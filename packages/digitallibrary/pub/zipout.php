<?php
/*
*	This is supposed to generate a zip file. I did totally steal zip.lib.php from the phpMyAdmin folder, and copied it here. It seemed simplest.
*	Add a link to download all the files and associated metadata by adding the following line to packages/digitallibrary/templates/CURRENTTEMPLATE/digitalcontent.inc.php
*   	echo("<a href ='?p=digitallibrary/zipout&id=".$objDigitalContent->ID."'>Download These Images and Metadata</a>");
*	Please put this file in packages/digitallibrary/pub 
*
*/
isset($_ARCHON) or die();
#BEGIN zip.lib.php
class ZipFile
{
    /**
     * Whether to echo zip as it's built or return as string from -> file
     *
     * @var  boolean $doWrite
     */
    var $doWrite = false;
    /**
     * Array to store compressed data
     *
     * @var  array $datasec
     */
    var $datasec = array();
    /**
     * Central directory
     *
     * @var  array $ctrl_dir
     */
    var $ctrl_dir = array();
    /**
     * End of central directory record
     *
     * @var  string $eof_ctrl_dir
     */
    var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
    /**
     * Last offset position
     *
     * @var  integer $old_offset
     */
    var $old_offset = 0;
    /**
     * Sets member variable this -> doWrite to true
     * - Should be called immediately after class instantiation
     * - If set to true, then ZIP archive are echo'ed to STDOUT as each
     *   file is added via this -> addfile(), and central directories are
     *   echoed to STDOUT on final call to this -> file().  Also,
     *   this -> file() returns an empty string so it is safe to issue a
     *   "echo $zipfile;" command
     *
     * @access public
     *
     * @return void
     */
    function setDoWrite()
    {
        $this->doWrite = true;
    } // end of the 'setDoWrite()' method
    /**
     * Converts an Unix timestamp to a four byte DOS date and time format (date
     * in high two bytes, time in low two bytes allowing magnitude comparison).
     *
     * @param integer $unixtime the current Unix timestamp
     *
     * @return integer the current date in a four byte DOS format
     *
     * @access private
     */
    function unix2DosTime($unixtime = 0)
    {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);
        if ($timearray['year'] < 1980) {
            $timearray['year'] = 1980;
            $timearray['mon'] = 1;
            $timearray['mday'] = 1;
            $timearray['hours'] = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        } // end if
        return (($timearray['year'] - 1980) << 25)
        | ($timearray['mon'] << 21)
        | ($timearray['mday'] << 16)
        | ($timearray['hours'] << 11)
        | ($timearray['minutes'] << 5)
        | ($timearray['seconds'] >> 1);
    } // end of the 'unix2DosTime()' method
    /**
     * Adds "file" to archive
     *
     * @param string  $data file contents
     * @param string  $name name of the file in the archive (may contains the path)
     * @param integer $time the current timestamp
     *
     * @access public
     *
     * @return void
     */
    function addFile($data, $name, $time = 0)
    {
        $name = str_replace('\\', '/', $name);
        $hexdtime = pack('V', $this->unix2DosTime($time));
        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x14\x00";            // ver needed to extract
        $fr .= "\x00\x00";            // gen purpose bit flag
        $fr .= "\x08\x00";            // compression method
        $fr .= $hexdtime;             // last mod time and date
        // "local file header" segment
        $unc_len = strlen($data);
        $crc = crc32($data);
        $zdata = gzcompress($data);
        $zdata = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
        $c_len = strlen($zdata);
        $fr .= pack('V', $crc);             // crc32
        $fr .= pack('V', $c_len);           // compressed filesize
        $fr .= pack('V', $unc_len);         // uncompressed filesize
        $fr .= pack('v', strlen($name));    // length of filename
        $fr .= pack('v', 0);                // extra field length
        $fr .= $name;
        // "file data" segment
        $fr .= $zdata;
        // echo this entry on the fly, ...
        if ($this->doWrite) {
            echo $fr;
        } else {                     // ... OR add this entry to array
            $this->datasec[] = $fr;
        }
        // now add to central directory record
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .= "\x00\x00";                // version made by
        $cdrec .= "\x14\x00";                // version needed to extract
        $cdrec .= "\x00\x00";                // gen purpose bit flag
        $cdrec .= "\x08\x00";                // compression method
        $cdrec .= $hexdtime;                 // last mod time & date
        $cdrec .= pack('V', $crc);           // crc32
        $cdrec .= pack('V', $c_len);         // compressed filesize
        $cdrec .= pack('V', $unc_len);       // uncompressed filesize
        $cdrec .= pack('v', strlen($name)); // length of filename
        $cdrec .= pack('v', 0);             // extra field length
        $cdrec .= pack('v', 0);             // file comment length
        $cdrec .= pack('v', 0);             // disk number start
        $cdrec .= pack('v', 0);             // internal file attributes
        $cdrec .= pack('V', 32);            // external file attributes
        // - 'archive' bit set
        $cdrec .= pack('V', $this->old_offset); // relative offset of local header
        $this->old_offset += strlen($fr);
        $cdrec .= $name;
        // optional extra field, file comment goes here
        // save to central directory
        $this->ctrl_dir[] = $cdrec;
    } // end of the 'addFile()' method
    /**
     * Echo central dir if ->doWrite==true, else build string to return
     *
     * @return string  if ->doWrite {empty string} else the ZIP file contents
     *
     * @access public
     */
    function file()
    {
        $ctrldir = implode('', $this->ctrl_dir);
        $header = $ctrldir .
            $this->eof_ctrl_dir .
            pack('v', sizeof($this->ctrl_dir)) . //total #of entries "on this disk"
            pack('v', sizeof($this->ctrl_dir)) . //total #of entries overall
            pack('V', strlen($ctrldir)) .          //size of central dir
            pack('V', $this->old_offset) .       //offset to start of central dir
            "\x00\x00";                            //.zip file comment length
        if ($this->doWrite) { // Send central directory & end ctrl dir to STDOUT
            echo $header;
            return "";            // Return empty string
        } else {                  // Return entire ZIP archive as string
            $data = implode('', $this->datasec);
            return $data . $header;
        }
    } // end of the 'file()' method
} // end of the 'PMA\libraries\ZipFile' class
#END zip.lib.php
#Set the headers first
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.$_REQUEST['id'].'.zip"');
#Grab ID and prepare query.
$collectionID=$_REQUEST['id'];
$query = "SELECT ID FROM `tblDigitalLibrary_Files` WHERE DigitalContentID=$collectionID AND Size < 20000000";
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
$zip = new ZipFile();
#Add metadata file
$zip->addFile($line,"metadata.csv");
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
	if($_ARCHON->config->CacheFiles && file_exists('packages/digitallibrary/files/' . $objFile->ID . '/' .$prefix. $objFile->Filename))
	{
		$fp = fopen('packages/digitallibrary/files/' . $objFile->ID . '/' .$prefix. $objFile->Filename,'rb');
		while(!feof($fp))
		{
			$line.=fread($fp, 1024*1024);
		}
		fclose($fp);
	}
	else
	{
		if(isset($objFile->DirectLink)){
			$fp=fopen(substr($objFile->DirectLink,strpos($objFile->DirectLink,'files')),'rb');
			if($fp){
				while(!feof($fp))
				{
					$line.=fread($fp,1024*1024);
				}
				fclose($fp);
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
	}
	#Add to zip
	$zip->addFile($line,$objFile->Filename);
}
#Output
$size=strlen($zip->file());
header("Content-Length: {$size}");
echo $zip->file();
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
	return array();
  }
  $res = $result -> fetchAll();
  $result -> free();
  return $res;
}
?>
