<?php

isset($_ARCHON) or die();


$mysql_link = mysql_connect("localhost", "readuser", "readonly");
if (!$mysql_link)
{
        die('Could not connect: ' . mysql_error());
}
mysql_select_db($_ARCHON->db->DatabaseName, $mysql_link) or die("Could not select database");


$result = mysql_query('SELECT tblCollections_Collections.ID,
"NULL" as contentID,
tblCollections_Collections.Title,tblCollections_Collections.dateadded,
"Yes" AS Collection 
FROM tblCollections_Collections
WHERE tblCollections_Collections.dateadded != 0
and tblCollections_Collections.Enabled != 0
UNION
SELECT tblCollections_Content.CollectionID,
tblCollections_Content.ID as contentID,
tblCollections_Content.Title,
tblCollections_Content.dateadded,"No" FROM tblCollections_Content
JOIN tblCollections_Collections
on tblCollections_Content.CollectionID = tblCollections_Collections.ID
WHERE tblCollections_Content.dateadded != 0
and tblCollections_Content.Enabled != 0
and tblCollections_Collections.Enabled != 0
ORDER BY dateadded DESC LIMIT 5');

while($row = mysql_fetch_array($result))
{
    $newcontent[]=$row;
}

return $newcontent;

?>
