<?php

/*
*
* @original author Nat Wilson
*
This file is desiged to display one image that is associated with a collection on the collection record display
as a thumbnail.  
*I will put any setting or phrase that needs configuration in square brackets, i.e. [user name], replace the brackets and everything in it
*with the setting that is specific to your instance of Archon.
*
*The following lines will have to be changed depending on the institution's database and system configuration:
*
*$mysql_link = mysql_connect("localhost", "[database user name]", "[user password]");
*Note: "Localhost" should work for most instances.
*
*mysql_select_db("[database name]", $mysql_link) or die("Could not select database");
*
*It is also necessary to make a new limited database user for this function. In the case below, I made a user
*named "archon" with the following database privileges:
*
*Select

*Bug: Certrain special characters dont get passes well through the variables and come out as "?." This might be fixed if we
*decoded the characters as they come out in index.php

*/


/*  Connects to the database  */

/*$mysql_link = mysql_connect("localhost", "readuser", "readonly");
if (!$mysql_link)
{
die('Could not connect: ' . mysql_error());
}
mysql_select_db("archon", $mysql_link) or die("Could not select database");*/

/*  Looks for digital library files that are associated with the current collection  */

/*$result = mysql_query(
"Select
tblDigitalLibrary_Files.ID,
tblDigitalLibrary_DigitalContent.Title,
tblDigitalLibrary_DigitalContent.CollectionID,
tblDigitalLibrary_Files.FileTypeID,
tblDigitalLibrary_Files.DigitalContentID
from tblDigitalLibrary_Files,
tblDigitalLibrary_DigitalContent
where tblDigitalLibrary_Files.FileTypeID = '2' and
tblDigitalLibrary_Files.DefaultAccessLevel = '2'
and tblDigitalLibrary_Files.DigitalContentID = tblDigitalLibrary_DigitalContent.ID
and tblDigitalLibrary_DigitalContent.CollectionID =".$objCollection->ID."
ORDER BY RAND() LIMIT 1");

while($row = mysql_fetch_object($result))
{
$id=$row->ID;
$title=$row->Title;
$collection=$row->DigitalContentID;
}

mysql_close($mysql_link);*/

global $_ARCHON;

$query = "Select
tblDigitalLibrary_Files.ID,
tblDigitalLibrary_DigitalContent.Title,
tblDigitalLibrary_DigitalContent.CollectionID,
tblDigitalLibrary_Files.FileTypeID,
tblDigitalLibrary_Files.DigitalContentID
from tblDigitalLibrary_Files,
tblDigitalLibrary_DigitalContent
where tblDigitalLibrary_Files.FileTypeID IN (1,2,3,4) and
tblDigitalLibrary_Files.DefaultAccessLevel = '2'
and tblDigitalLibrary_Files.DigitalContentID = tblDigitalLibrary_DigitalContent.ID
and tblDigitalLibrary_DigitalContent.CollectionID =".$objCollection->ID."
ORDER BY RAND() LIMIT 1";

$result = $_ARCHON -> mdb2 -> query($query);
if (PEAR::isError($res)) {
    trigger_error($result->getMessage(), E_USER_ERROR);
}
$rows = $result -> fetchAll();
$result -> free();
if (!empty($rows[0])) {
    $id = $rows[0][ID];
    $title=$rows[0][Title];
    $collection=$rows[0][DigitalContentID];    
    
    $file = New File($id);
    $file -> dbLoad();
    $url = $file -> getFileURL();
/*  places the image in the page using the variables assigned above if that image is a jpg  */
if($_ARCHON->Security->userHasAdministrativeAccess())
{
    echo "<a href='..?p=digitallibrary/digitalcontent&id=".$collection."'><img src='$url' height=250></a>";
    echo "<br>Sample images from this set: <a href='..?p=digitallibrary/digitalcontent&id=".$collection."'>".$title."</a>";
}
    
}

?>