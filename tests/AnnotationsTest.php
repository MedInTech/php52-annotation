<?php

/** @noinspection PhpUnusedPrivateFieldInspection */
/** @noinspection PhpUnusedPrivateMethodInspection */

use PHPUnit\Framework\TestCase;

class AnnotationsTest extends TestCase
{

  public function testEmpty()
  {
    $doc = <<<DOC
/**
 *
 */
DOC;

    $list = MedInTech_Util_Annotations::parseDocComment($doc);
    $this->assertEquals(array(), $list);
  }
  public function testSimple()
  {
    $doc = <<<DOC
/**
 * @param 
 * @argNone @arg1 1 @arg2 1 2
 * @dup dup1 Description
 * @dup dup2
 */
DOC;

    $list = MedInTech_Util_Annotations::parseDocComment($doc);
    $this->assertEquals(array(
      'param' => true,
      'argNone' => true,
      'arg1' => 1,
      'arg2' => '1 2',
      'dup' => array(
        'dup1 Description',
        'dup2',
      ),
    ), $list);

  }
  public function testAtSignInValue()
  {
    $doc = <<<DOC
/**
 * @atTest v@lue
 */
DOC;

    $list = MedInTech_Util_Annotations::parseDocComment($doc);
    $this->assertEquals(array(
      'atTest' => 'v@lue',
    ), $list);
  }
  public function testNullValue()
  {
    $doc = <<<DOC
/**
 * @key null
 */
DOC;

    $list = MedInTech_Util_Annotations::parseDocComment($doc);
    $this->assertEquals(array(
      'key' => null,
    ), $list);
  }
  public function testJsonValue()
  {
    $doc = <<<DOC
/**
 * @json {"key": 15}
 */
DOC;

    $list = MedInTech_Util_Annotations::parseDocComment($doc);
    $this->assertEquals(array(
      'json' => array('key' => 15),
    ), $list);
  }

  public function testMultiline()
  {
    $doc = <<<DOC
/**
 * @json {
 *   "key": 15,
     "answer": 42
 * }
 */ 
DOC;

    $list = MedInTech_Util_Annotations::parseDocComment($doc);
    $this->assertEquals(array(
      'json' => array('key' => 15, 'answer' => 42),
    ), $list);

  }

  public function _testSome()
  {
    $doc = <<<DOC
/**
 * @encapse 1 "no @annotations here"
 */
DOC;

    $list = MedInTech_Util_Annotations::parseDocComment($doc);
    $this->assertEquals(array(
      'encapse' => "1 'no @annotations here'",
    ), $list);
  }

  /**
   * @dataProvider getDocs
   *
   * @param $doc
   * @param $expected
   */
  public function testAll($doc, $expected)
  {
    $list = MedInTech_Util_Annotations::parseDocComment($doc);
    $this->assertEquals($expected, $list);
  }

  public function testClassFull()
  {
    $className = 'FullAnnotationsClass_b86b10e21760c22a13e1ac346cbe725b';
    $list = MedInTech_Util_Annotations::fromClassFull($className);
    $this->assertNotEmpty($list);
    $this->assertEquals($className, $list['class']['Class']['name']);
    $this->assertEquals('private', $list['properties']['privateField']['Property']['name']);
    $this->assertEquals('pubsf', $list['methods']['pubsf']['Method']['name']);
  }

  public function getDocs()
  {
    return array(
      array("/**\n * @a1 \n */", array('a1' => true)),
      array("/**\n * @a1 re \n * \n */", array('a1' => 're')),
      array("/**\n * @a1 @a2 \n */", array('a1' => true, 'a2' => true)),
      array("/**\n * @a1 \n * @a2 \n */", array('a1' => true, 'a2' => true)),
      array("/** @var int */", array('var' => 'int')),
    );
  }
}

/**
 * @Class {"name": "FullAnnotationsClass_b86b10e21760c22a13e1ac346cbe725b"}
 */
class FullAnnotationsClass_b86b10e21760c22a13e1ac346cbe725b
{
  /**
   * @var boolean
   * @Property {"name": "private"}
   */
  private $privateField;
  /**
   * @var boolean
   * @Property {"name": "public"}
   */
  public $publicField;

  /**
   * @var string
   * @Property {"name": "static"}
   */
  public static $staticField;

  /**
   * @Method {"name": "pubf"}
   */
  public function pubf() { }

  /**
   * @Method {"name": "pubsf"}
   */
  public static function pubsf() { }

  /**
   * @Method {"name": "prisf"}
   */
  private static function prisf() { }
}
