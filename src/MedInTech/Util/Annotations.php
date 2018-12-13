<?php

class MedInTech_Util_Annotations implements ArrayAccess
{
  private $list = array();
  private function __construct() { }

  const REG_PATTERN = '/\s+@(?=(.*)\s*(?:\s@|$))/Usu';

  /**
   * @param Reflector|ReflectionClass|ReflectionMethod|ReflectionProperty $reflector
   * @return MedInTech_Util_Annotations
   */
  public static function from(Reflector $reflector)
  {
    $isClass    = $reflector instanceof ReflectionClass;
    $isMethod   = $reflector instanceof ReflectionMethod;
    $isProperty = $reflector instanceof ReflectionProperty;
    if (!$isClass && !$isMethod && !$isProperty) {
      throw new InvalidArgumentException('$reflector must be ReflectionClass|ReflectionMethod|ReflectionProperty');
    }
    $doc        = $reflector->getDocComment();
    $self       = new self;
    $self->list = self::parseDocComment($doc);
    return $self;
  }

  public static function fromClass(ReflectionClass $class) { return self::from($class); }
  public static function fromMethod(ReflectionMethod $method) { return self::from($method); }
  public static function fromProperty(ReflectionProperty $property) { return self::from($property); }

  /**
   * @param $className
   *
   * @return array [class => [], properties => [], methods => []]
   * @throws ReflectionException
   */
  public static function fromClassFull($className)
  {
    $meta       = new ReflectionClass($className);
    $properties = $meta->getProperties();
    $methods    = $meta->getMethods();

    $propAnnotations = array();
    foreach ($properties as $property) {
      $propAnnotations[$property->name] = self::from($property);
    }

    $methodAnnotations = array();
    foreach ($methods as $method) {
      $methodAnnotations[$method->name] = self::from($method);
    }

    return array(
      'class'      => self::fromClass($meta),
      'properties' => $propAnnotations,
      'methods'    => $methodAnnotations,
    );
  }

  public static function parseDocComment($doc)
  {
    $parameters = array();
    preg_match_all(self::REG_PATTERN, $doc, $matches);
    foreach ($matches[1] as $rawParameter) {
      $rawParameter = trim(preg_replace('/^\s*\*(?:\/?$)?/m', "\n", $rawParameter));
      if (preg_match("/^(\S+)\s+(.+)$/is", $rawParameter, $match)) {
        $parsedValue = self::parseValue($match[2]);
        if (isset($parameters[$match[1]])) {
          $parameters[$match[1]] = array_merge((array)$parameters[$match[1]], (array)$parsedValue);
        } else {
          $parameters[$match[1]] = $parsedValue;
        }
      } else if (preg_match("/^\S+$/", $rawParameter, $match)) {
        $parameters[$rawParameter] = true;
      } else {
        $parameters[$rawParameter] = null;
      }
    }
    return $parameters;
  }

  private static function parseValue($originalValue)
  {
    if ($originalValue && $originalValue !== 'null') {
      if (($json = json_decode(strtr($originalValue, array("\r\n" => "\n", "\n" => ' ')), true)) === null) {
        $value = $originalValue;
      } else {
        $value = $json;
      }
    } else {
      $value = null;
    }
    return $value;
  }
  public function get($key) { return $this->has($key) ? $this->list[$key] : null; }
  public function has($key) { return array_key_exists($key, $this->list); }
  // ArrayAccess
  public function offsetExists($offset) { return $this->has($offset); }
  public function offsetGet($offset) { return $this->get($offset); }
  public function offsetSet($offset, $value) { $this->list[$offset] = $value; }
  public function offsetUnset($offset) { unset($this->list[$offset]); }
}
