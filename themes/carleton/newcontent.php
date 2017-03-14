<?php

isset($_ARCHON) or die();

$query='SELECT tblCollections_Collections.ID,
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
ORDER BY dateadded DESC LIMIT 5';
$res=$_ARCHON->mdb2->query($query);

if(PEAR::isError($res)){
    die('Could not connect: ' . mysql_error());
}

$newcontent=$res->fetchAll();

return $newcontent;

?>
