<?php
/**
 * Item template for finding aid output
 *
 * The variable:
 *
 *  $objContent
 *
 * is an instance of a CollectionContent object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the CollectionContent class definition in lib/collection.inc.php
 * for available properties and methods.
 *
 * The Archon API is also available through the variable:
 *
 *  $_ARCHON
 *
 * Refer to the Archon class definition in lib/archon.inc.php
 * for available properties and methods.
 *
 * @package Archon
 * @author Chris Rishel
 * TODO: Add setting to control hidden userfields.
 * TODO: LINK_TOTAL vs. LINK_NONE
 * TODO: Deal with DigitalContentLink in collections/lib/collection.inc.php
 */
isset($_ARCHON) or die();


if($enabled)
{
    if ($Content["DigitalContentLink"]) {
        ?>
        <dt class='faitem'><a name="id<?php echo($Content['ID']); ?>"></a><?php echo("<a href = " . $Content['DigitalContentLink'].">" . $Content['String'] . "</a>"); ?></dt>
        <?php
    }
    else {
        ?>
        <dt class='faitem'><a name="id<?php echo($Content['ID']); ?>"></a><?php echo($Content['String']); ?></dt>
         <?php
    } 
   if($Content['Description'])
   {
      echo("<dd class='faitemcontent'>" . $Content['Description'] . "</dd>\n");
   }

   if($Content['UserFields'])
   {
      $strUserFields = '';
      $last = count($Content['UserFields']);
      $count = 1;

      natcasesort($Content['UserFields']);
      foreach($Content['UserFields'] as $ID => $String)
      {
         if (preg_match("/Previous Code|Additional Location Information|Confidential Note|UnitID|IndexField/", $String))
         {
            if($_ARCHON->Security->userHasAdministrativeAccess())
            {
               $strUserFields .= $String;
               $strUserFields .= "</dd>\n<dd class='faitemcontent'>\n";
            }
         }
         else
         {
            $strUserFields .= $String;
            if($count != $last)
            {
               $strUserFields .= "</dd>\n<dd class='faitemcontent'>\n";
            }
            $count++;
         }
      }

      echo("<dd class='faitemcontent'>" . $strUserFields . "</dd>\n");
   }

   if(!empty($Content['Subjects']))
   {
      echo("<dd class='faitemcontent'><dl><dt>Subject/Index Terms:</dt><dd>\n");
      echo($_ARCHON->createStringFromSubjectArray($Content['Subjects'], "</dd>\n<dd>\n", LINK_TOTAL));
      echo("</dd></dl></dd>\n");
   }

   if(!empty($Content['Creators']))
   {
      echo("<dd class='faitemcontent'><dl><dt>Creators:</dt><dd>\n");
      echo($_ARCHON->createStringFromCreatorArray($Content['Creators'], "</dd>\n<dd>\n", LINK_TOTAL));
      echo("</dd></dl></dd>\n");
   }

   if(!empty($Content['Content']))
   {
      echo("<dd><dl class='faitem'>#CONTENT#</dl></dd>");
   }


}


?>
