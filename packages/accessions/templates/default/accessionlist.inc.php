<?php
/**
 * Accession List template
 *
 * The variable:
 *
 *  $objAccession
 *
 * is an instance of a Accession object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the Accession class definition in lib/Accession.inc.php
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
 * @author Robert Andrews, Chris Rishel
 */

isset($_ARCHON) or die();

echo("<div class='listitem'>");
if($objAccession->ClassificationID)
{
    $objAccession->Classification = New Classification($objAccession->ClassificationID);
    //$objAccession->Classification->dbLoad(true);
    echo($_ARCHON->Error);
    echo($objAccession->Classification->toString(LINK_NONE, true, false, true, false) . '/');
}
echo($objAccession->toString(LINK_TOTAL, true) . "</div>\n");

?>
