<?php
/*
*	This is supposed to generate a zip file. It requires PHP's ZipArchive. You can check if this is enabled by looking at the box labeled "zip" in phpinfo.
*	Add a link to download all the files and associated metadata by adding the following line to packages/digitallibrary/templates/CURRENTTEMPLATE/digitalcontent.inc.php
*   	echo("<a href ='?p=digitallibrary/zipout&id=".$objDigitalContent->ID."'>Download These Images and Metadata</a>");
*	Please put this file in packages/digitallibrary/pub 
*
*/
isset($_ARCHON) or die();

#Grab ID and prepare query.
$collectionID=$_REQUEST['id'];
$query = "SELECT ID FROM `tblDigitalLibrary_Files` WHERE DigitalContentID=$collectionID";

#Grab content for metadata.csv file
$DigitalContent= New DigitalContent($collectionID);
if((!$DigitalContent->dbLoad()))
{
	header("Location: index.php?p=digitallibrary/digitalcontent&id=$collectionID");
	die();
}
#Load keys and vals from metadata into csv format
$keys='';
$vals='';
foreach($DigitalContent as $key=>$val){
	if($key!="ContentURL" && $key!="HyperlinkURL" && $val!='' && !empty($val) && $key!="ToStringFields"){
		$keys.="\"$key\",";
		$vals.="\"$val\",";
	}
}
$keys=substr($keys,0,-1)."\n";
$vals=substr($vals,0,-1);
$line=$keys.$vals;

#Load query results, and create the output file
$out=runQuery($query);
$zip = new ZipArchive;
$outfilename="files/temp";
while(file_exists($outfilename . ".zip")){
	$outfilename.="1";
}
if($zip->open($outfilename . ".zip",ZipArchive::CREATE)!==TRUE){
	$_ARCHON->declareError("Error: Could not compile zip file");
	die();
}
#Add metadata file
$zip->addFromString("metadata.csv",$line);

#Loop through results
foreach($out as $IDArray){

	#Load file data in
	$ID=$IDArray['ID'];
	$objFile = New File($ID);
	if((!$objFile->dbLoad(DIGITALLIBRARY_FILE_FULL)))
	{
		continue;
	}
	#Read through the file. This code is from getfile.php
	$line='';
	if(isset($objFile->DirectLink)){
		if(file_exists(substr($objFile->DirectLink,strpos($objFile->DirectLink,'files')))){
			$zip->addFile(substr($objFile->DirectLink,strpos($objFile->DirectLink,'files')),$objFile->Filename);
		}
		else
		{
			continue;
		}
	}
	else
	{
		continue;
	}
	#Add to zip
}

$zip->close();

/**
* Return file size (even for file > 2 Gb)
* For file size over PHP_INT_MAX (2 147 483 647), PHP filesize function loops from -PHP_INT_MAX to PHP_INT_MAX.
*
* @param string $path Path of the file
* @return mixed File size or false if error
*/
function realFileSize($path)
{
    if (!file_exists($path))
        return false;

    $size = filesize($path);
   
    if (!($file = fopen($path, 'rb')))
        return false;
   
    if ($size >= 0)
    {//Check if it really is a small file (< 2 GB)
        if (fseek($file, 0, SEEK_END) === 0)
        {//It really is a small file
            fclose($file);
            return $size;
        }
    }
   
    //Quickly jump the first 2 GB with fseek. After that fseek is not working on 32 bit php (it uses int internally)
    $size = PHP_INT_MAX - 1;
    if (fseek($file, PHP_INT_MAX - 1) !== 0)
    {
        fclose($file);
        return false;
    }
   
    $length = 1024 * 1024;
    while (!feof($file))
    {//Read the file until end
        $read = fread($file, $length);
        $size = bcadd($size, $length);
    }
    $size = bcsub($size, $length);
    $size = bcadd($size, strlen($read));
   
    fclose($file);
    return $size;
}



$size=realFileSize($outfilename.".zip");
if($size==false){
	$_ARCHON->declareError("Error: Could not read zip file");
	try{
		unlink($outfilename.".zip");
	}
	catch(Exception $e){
	}
	die();
}
#Set the headers first
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.$_REQUEST['id'].'.zip"');

header("Content-Length: {$size}");
#Output
@readfile($outfilename.".zip");
unlink($outfilename.".zip");

#runQuery is also stolen, but from indexutil.php
function runQuery($q) {
  global $_ARCHON;

  // Security Note:
  // It would be more secure to use the mdb2->prepare() function followed by
  // exec(), however with the checks already in place, it did not seem
  // completely necessary to be extra security conscience about this utility.
  $result = $_ARCHON -> mdb2 -> query($q);
  if(PEAR::isError($result)) {
    echo "ERROR: Unable to index ";
    echo ". There is a problem with the following query:<br><br>";
    echo "$q";
    exit(1);
  }
  if($result->numRows()==0){
	echo "There is nothing to report.";
	exit(1);
  }
  $res = $result -> fetchAll();

  $result -> free();

  return $res;
}












?>
