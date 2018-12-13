Install:

 composer require arth/php52-annotation --no-dev

Usage:

 $annotations = MedInTech_Util_Annotations::fromClassFull($className);
 echo json_encode($annotations);

 {"class": [], "properties": [], "methods": []}


Hint:
  It's designed to be php5.2 compatible, but unit tests assumed to run on recent php versions.
  Purpose is to have zero-level dependency on legacy php features.

