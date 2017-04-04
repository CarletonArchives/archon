<?php

/*This file is a replacement for the original collection display for creators, which as of 12/20/2011 does not display creators
 * assicated at the content level via the authority file.  In addition, key term searches for subjects and creators do not give positive results 
 * for collection content associated with those subjects or creators. 
 * 
 * This tool determines if the user is looking at a creator or a subject authority record, queries the database for any collections 
 * and collection content that have the ID of the authority record in question attached to it.  
 * 
 * The colleciton and/or content information is pulled out, turned in to an array with the collection ID's as keys for the first
 * array level, and relivant content ID's as keys nested in the appropriate collection.  
 * 
 * The resulting array is used in the packages/creators/templates/[template name] folder to create the expanding/collapsing menues
 * in the authority record display. 
*/

if ($objCreator->ID)
{
//Calls up array with collection and collection content information    
    $CreatorID=$objCreator->ID;
    $ret=collectioncontentcreators($CreatorID);
}

elseif ($Type=='Subject')
{
    $SubjectID=$ID;
    $ret=collectioncontentSubjects($SubjectID);
}
                           

function collectioncontentcreators($CreatorID)
{
    echo $searchtype;
	$mysql_link = mysql_connect("localhost", "readuser", "readonly");
	if (!$mysql_link)
	{
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db("archon", $mysql_link) or die("Could not select database");

    $contentresult = mysql_query(
            "SELECT tblCollections_Content.*,
            tblCollections_Collections.Title AS collectiontitle,
            tblCollections_Collections.ID AS collectionID2,
            tblCollections_CollectionContentCreatorIndex.CollectionContentID
            from tblCollections_Content
            INNER JOIN tblCollections_Collections ON
            tblCollections_Collections.ID=tblCollections_Content.CollectionID
            INNER JOIN tblCollections_CollectionContentCreatorIndex ON
            tblCollections_CollectionContentCreatorIndex.CollectionContentID=tblCollections_Content.ID
            Where tblCollections_CollectionContentCreatorIndex.CreatorID= '".$CreatorID."'
            order by collectiontitle, Title
            ");
        
    $colllist = mysql_query(
            "SELECT tblCollections_Collections.ID as CollectionID,
             tblCollections_Collections.Title as collectiontitle
             FROM
             tblCollections_Collections
             INNER JOIN tblCollections_CollectionCreatorIndex
             ON
             tblCollections_CollectionCreatorIndex.CollectionID=tblCollections_Collections.ID
             WHERE tblCollections_CollectionCreatorIndex.CreatorID= '".$CreatorID."'
            ");

        if ($colllist)
        {
         while($row = mysql_fetch_object($colllist))
         {
             $collectionarray[$row->CollectionID][] = $row;
//             $collectionarray[$row->CollectionID]['CollectionID'] = $row->CollectionID;
         }
        }
    
	if($contentresult)
	{ 
            	$creatorcontent = array();
		while($row = mysql_fetch_object($contentresult))                    
                {
                    $creatorcontent[$row->CollectionID][$row->ID]=$row;
//                    $creatorcontent[$row->CollectionID]['CollectionID'] = $row->CollectionID;
                }
	}
   
       if ($creatorcontent and $collectionarray)
       {
              $creatorcontent=$creatorcontent + $collectionarray;
       }

       elseif ($creatorcontent and !$collectionarray)
       {
           $creatorcontent=$creatorcontent;
       }

       elseif (!$creatorcontent and $collectionarray)
       {
           $creatorcontent=$collectionarray;
       }

       return $creatorcontent;  
        
mysql_close($mysql_link);            
}

function collectioncontentSubjects($SubjectID)
{
    echo $searchtype;
	$mysql_link = mysql_connect("localhost", "readuser", "readonly");
	if (!$mysql_link)
	{
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db("archon", $mysql_link) or die("Could not select database");

    $contentresult = mysql_query(
            "SELECT tblCollections_Content.*,
            tblCollections_Collections.Title AS collectiontitle,
            tblCollections_Collections.ClassificationID,
            tblCollections_Collections.ID AS collectionID2,
            tblCollections_CollectionContentSubjectIndex.CollectionContentID
            from tblCollections_Content
            INNER JOIN tblCollections_Collections ON
            tblCollections_Collections.ID=tblCollections_Content.CollectionID
            INNER JOIN tblCollections_CollectionContentSubjectIndex ON
            tblCollections_CollectionContentSubjectIndex.CollectionContentID=tblCollections_Content.ID
            Where tblCollections_CollectionContentSubjectIndex.SubjectID= '".$SubjectID."'
            order by collectiontitle, Title
            ");
        
    $colllist = mysql_query(
            "SELECT tblCollections_Collections.ID as CollectionID,
             tblCollections_Collections.ClassificationID,
             tblCollections_Collections.Title as collectiontitle
             FROM
             tblCollections_Collections
             INNER JOIN tblCollections_CollectionSubjectIndex
             ON
             tblCollections_CollectionSubjectIndex.CollectionID=tblCollections_Collections.ID
             WHERE tblCollections_CollectionSubjectIndex.SubjectorID= '".$SubjectID."'
            ");

        if ($colllist)
        {
         while($row = mysql_fetch_object($colllist))
         {
             $collectionarray[$row->CollectionID][] = $row;
             $collectionarray[$row->CollectionID]['CollectionID'][]=$row->CollectionID;
             $collectionarray[$row->CollectionID]['ClassificationID'][]=$row->ClassificationID;
//             $collectionarray[$row->CollectionID]['CollectionID'] = $row->CollectionID;
         }
        }
    
	if($contentresult)
	{ 
            	$Subjectcontent = array();
		while($row = mysql_fetch_object($contentresult))                    
                {
                    $Subjectcontent[$row->CollectionID][$row->ID]=$row;
                    $Subjectcontent[$row->CollectionID]['CollectionID']=$row->CollectionID;
                    $Subjectcontent[$row->CollectionID]['ClassificationID']=$row->ClassificationID;
//                    $Subjectcontent[$row->CollectionID]['CollectionID'] = $row->CollectionID;
                }
	}
   
       if ($Subjectcontent and $collectionarray)
       {
              $Subjectcontent=$Subjectcontent + $collectionarray;
       }

       elseif ($Subjectcontent and !$collectionarray)
       {
           $Subjectcontent=$Subjectcontent;
       }

       elseif (!$Subjectcontent and $collectionarray)
       {
           $Subjectcontent=$collectionarray;
       }

       return $Subjectcontent;  
        
mysql_close($mysql_link);            
}


?>