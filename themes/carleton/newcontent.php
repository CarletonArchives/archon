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
while(PEAR::isError($res)){
    if($res->getcode()==-19){
        $queries=array("ALTER TABLE `tblSubjects_Subjects` ADD `dateadded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;",
"ALTER TABLE `tblCollections_Content` ADD `dateadded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;",
"ALTER TABLE `tblAccessions_Accessions` ADD `dateadded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;",
"ALTER TABLE `tblCollections_Collections` ADD `dateadded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;",
"ALTER TABLE `tblDigitalLibrary_DigitalContent` ADD `dateadded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;",
"ALTER TABLE `tblDigitalLibrary_Files` ADD `dateadded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
	foreach($queries as $run){
            $res2=$_ARCHON->mdb2->query($run);
            if(PEAR::isError($res2)){
                if($res2->getcode()!=-5){
                    die('Adding dateadded column failed: '.$res2->getMessage().$res2->getcode());
                }
            }
        }
	$res=$_ARCHON->mdb2->query($query);
    }
    if(PEAR::isError($res)){
        die('Could not connect: ' . $res->getcode(). $res->getMessage());
    }
}

$newcontent=$res->fetchAll();

return $newcontent;

?>
