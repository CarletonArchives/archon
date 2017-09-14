<?php
require_once("PEAR.php");
function pear_isError($arg) {
   static $pear;
   if(!isset($pear))
      $pear = new PEAR();
   return $pear->isError($arg);
}
?>
