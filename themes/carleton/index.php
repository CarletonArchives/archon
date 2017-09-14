<?php
/**
 * Main page for default template
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die(); if($_REQUEST['f'] == 'contact') { require("contact.inc.php"); return; }

isset($_ARCHON) or die();
echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

?>

<!--5 image slide show populated with random images.-->
<div id='carlSlideshow' class="flexslider bground">
<ul class="slides">
<?php //require_once("randomblobid.php");
$query = "Select
tblDigitalLibrary_Files.ID,
tblDigitalLibrary_DigitalContent.Title,
tblDigitalLibrary_Files.DigitalContentID
from tblDigitalLibrary_Files,
tblDigitalLibrary_DigitalContent
where tblDigitalLibrary_Files.FileTypeID = '2' and
tblDigitalLibrary_Files.DefaultAccessLevel = '2' AND 
tblDigitalLibrary_DigitalContent.Browsable = 1
and tblDigitalLibrary_Files.DigitalContentID = 	tblDigitalLibrary_DigitalContent.ID
ORDER BY RAND() LIMIT 5";
$result = $_ARCHON -> mdb2 -> query($query);
if (PEAR::isError($res)) {
    trigger_error($result->getMessage(), E_USER_ERROR);
}
$rows = $result -> fetchAll();
$result -> free();
foreach($rows as $objFile) {
    $objFile = New File($objFile[ID]);
    $objFile -> dbLoad();
    $objDigitalContent = New DigitalContent($objFile -> DigitalContentID);
    $objDigitalContent -> dbLoad();
    $url = $objFile -> getFileURL();
    $id = $objDigitalContent -> ID;
    //echo "<li><img src='$url' : '$id </li>";
    //echo "<li><img src='files/tree.jpg'</li>";
echo "<li> <a href='..?p=digitallibrary/digitalcontent&id=".$objDigitalContent->ID."' class='image'><img src='".$url."' height=250 align='middle'></a>";
echo "<div class='caption'>Collection: <a href='..?p=digitallibrary/digitalcontent&id=".$objDigitalContent->ID."'>".$objDigitalContent->toString()."</a></div></li>";    
    //echo "<br>'$url'<br>";
    
}
?>
</ul>
</div>

<?php
include_once 'newcontent.php';
?>
<div id='themeindex' class='bground'>
<dl>
  <dt class='index'>Newest Content Added</dt>
  <dd class='index'>
    <ul>
  <?php
  foreach($newcontent as $newcontentitem)
  {
      if ($newcontentitem[Collection]=='Yes')
      {
          echo "<br />";
          $objCollection=new Collection($newcontentitem[ID]);
          echo "<li>Collection: ".$objCollection->toString(LINK_EACH)."<br /></li>";
      }
      elseif ($newcontentitem[Collection]=='No')
      {
          echo "<br />";
          $objCollection=new Collection($newcontentitem[ID]);
          $objContent=new CollectionContent($newcontentitem[contentID]);
          echo "<li>".$objContent->toString(LINK_EACH, true, true, true, true, $_ARCHON->PublicInterface->Delimiter)."  Added to collection: ";
          echo $objCollection->toString(LINK_EACH);
          echo "</li>";

      }
  }
  ?></ul>
  </dd>
</div>

<div id='themeindex' class='bground'>
<dl>
  <dt class='index'>Getting records</dt>
  <dd class='index'>
    <ul>
      <li>Most of the records in the archives are not scanned, but you can make requests to view any originals copies you find in this database.  You can either email us about individual records or create a list of records using the Research List tool and make an appointment to see multiple records in our offices.</li>
    </ul>
  </dd>
  <dt class='index'>Scans</dt>
  <dd class='index'>
    <ul>
      <li>We have some scans of images or documents already available online, either in this database or an external database. If you do not find a scanned version of the item you are interested in, please contact us about the possibility of having a scan made and delivered to you.</li>
    </ul>
  </dd>
  
<script language="javascript">


function toggle2(showHideDiv, switchTextDiv) {
	var ele = document.getElementById(showHideDiv);
	var text = document.getElementById(switchTextDiv);
	if(ele.style.display == "block") {
    		ele.style.display = "none";
		
  	}
	else {
		ele.style.display = "block";
	
	}
}



</script>

 
  
  <dt class='index'>About us</dt>
    <dd class='index'>
        <ul>
            <li>
                <a id="myHeader1" href="javascript:toggle2('myContent1','myHeader1');" >Hours and Contact</a>
                
       <div id="myContent1" style="display: none">Monday - Friday 9 a.m. - 5 p.m.
and by appointment.
<p>Gould Library 164 | archives@carleton.edu | (507) 222-4270
        <p><a href ="./index.php?p=core/index&f=contact">(more...)</a>
</div>
            </li>
       
    <li>
                   <a id="myHeader1" href="javascript:toggle2('myContent2','myHeader1');" >Mission</a>
       <div id="myContent2" style="display: none">The primary purpose of the Carleton College Archives is to gather, preserve, and make available for institutional reference and public research use documentation and information pertaining to the work, history, and development of Carleton College, or about programs, policies, activities, events, persons, or groups associated with the College.
</div>
    </li>
        </dd>
       
        
  <dt class='index'>Narrow Your Search Results</dt>
  <dd class='index'>
    <ul>
      <li>Use a minus sign before a term you want to omit from your results.  (e.g. 'bass -fish' finds bass guitars but not bass fishing.)</li>
      <li>Browse by collection title, subject, name, or classification.</li>
    </ul>
  </dd>
</dl>
</div>
<link type="text/css" rel="stylesheet" href="/themes/carleton/js/FlexSlider-1.7/flexslider.css" />
<script type="text/javascript" src="/themes/carleton/js/FlexSlider-1.7/jquery.flexslider-min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  $(window).load(function() {
    $('#carlSlideshow').flexslider();
  });
});
</script>
