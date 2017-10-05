<?php
/**
 * DigitalContent template
 *
 * The variable:
 *
 *  $objDigitalContent
 *
 * is an instance of a DigitalContent object, with its properties
 * already loaded when this template is referenced.
 *
 * Refer to the DigitalContent class definition in packages/digitallibrary/lib/digitallibrary.inc.php
 * for available properties and methods.
 *
 * The Archon API is also available through the variable:
 *
 *  $_ARCHON
 *
 * Refer to the Archon class definition in packages/core/lib/archon.inc.php
 * for available properties and methods.
 *
 * @package Archon
 * @author Chris Rishel, Chris Prom, Caitlin Donahue
 */
isset($_ARCHON) or die();

echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");
//Create an array that stores the file name and url of all inages in diglib
$image_array = array();
if(!empty($objDigitalContent->Files))
{
    foreach($objDigitalContent -> Files as $objFile){
    $PreviewAccess = $objFile->verifyLoadPermissions(DIGITALLIBRARY_FILE_PREVIEWSHORT);

    $FullAccess = $objFile->verifyLoadPermissions(DIGITALLIBRARY_FILE_FULL);
    if($PreviewAccess){
        if($objFile->FileType->MediaType->MediaType == 'Image')
         {
            $temparray = array($objFile -> Filename, $objFile -> getFileURL());
            $image_array[] = $temparray;
         }
         }
    } 
}

//define functions for new media handlers
function pdfViewer($file,$url) {

    ?>
    <div style="width:100%!important; height:400px!important; margin:auto; overflow:hidden;">
    <iframe src="https://docs.google.com/gview?url=<?php echo $url; ?>&embedded=true" style="width:100%; height:100%;" frameborder="1"></iframe>
    
<!--    <a href = "http://docs.google.com/gview?url=<?php // echo $url; ?>&embedded=true"> View on Google Docs </a>    -->
    </div>
    <?php

}

//using HTML5 for the video and audio player. Won't work with some older browsers.
function videoViewer($file, $url){
    echo "<video src='".$url."' controls width=100%>
        Your browser does not support the video element.
        </video>";
}

function audioViewer($file, $url){
    echo "<audio controls style = 'width: 100%;'>
        <source src='".$url."'>
        Your browser does not support the audio element.
        </audio>";
}

function imageViewer($file,$url, $index) {
    global $image_array;
    //make image link to javascript. needs index and image array as variables
    echo("<a href ='javascript:lightbox(" . $index .", ".json_encode($image_array).");'>");
    echo("<div class='digcontenttitlebox mdround' src = " . $url . ">");
    echo("<img class='digcontentfile' src='".$url."' title='Click to open slideshow'>");
    echo("</div></a>");
}


if(!empty($objDigitalContent->Files))
{

   $firstFile = true;
   //Set the index to -1, will set first image as 0, and count up. used for lightbox functionality.
   $index = -1;
   echo("<div id='digcontentwrapper'><div id='digcontentfiles' class='mdround'>\n");
   echo("Material presented here may only be samples from a larger set. Contact the <a href ='./index.php?p=core/index&f=contact'>Carleton Archives</a> to inquire about additional items.");
   echo("<br/><br/><a href ='?p=digitallibrary/zipout&id=".$objDigitalContent->ID."'>Download These Files and Metadata</a><br/><br/>");
   foreach($objDigitalContent -> Files as $objFile)
   {
       
      if(!$firstFile)
      {
         echo("<hr/>");
      }
      
      $firstFile = false;

      $PreviewAccess = $objFile->verifyLoadPermissions(DIGITALLIBRARY_FILE_PREVIEWSHORT);

      $FullAccess = $objFile->verifyLoadPermissions(DIGITALLIBRARY_FILE_FULL);

      echo("<div class='digcontenttitlebox mdround'>");

      if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ) && $objFile->DefaultAccessLevel != DIGITALLIBRARY_ACCESSLEVEL_FULL)
      {
         if($objFile->DefaultAccessLevel == DIGITALLIBRARY_ACCESSLEVEL_PREVIEWONLY)
         {
            echo("<span class='bold'>NOTE</span>: The public cannot download this file.<br/>");
         }
         elseif($objFile->DefaultAccessLevel == DIGITALLIBRARY_ACCESSLEVEL_NONE)
         {
            echo("<span class='bold'>NOTE</span>: The public cannot view previews of or download this file.<br/>");
         }
      }

      if(!$PreviewAccess)
      {
         echo("<span class='digcontentfiletitle'>No preview for this item is publicly available. Contact the archives for information about accessing this item.<br/><br/>");
         echo("{$objFile->getString('Title')} (" . ($objFile->FileType ? $objFile->FileType->getString('FileType') : '') . ", " . formatsize($objFile->Size) . ")</span><br/>");
      }
      else
      {
         $url = $objFile -> getFileURL();

         if($objFile->FileType->MediaType->MediaType == 'Document')
         {
           // $onclick = ($_ARCHON->config->GACode && $_ARCHON->config->GADigContentPrefix) ? "onclick='javascript: pageTracker._trackPageview(\"{$_ARCHON->config->GADigContentPrefix}/pdf/DigitalContentID={$objDigitalContent->ID}/fileID={$objFile->ID}\");'": "";
//            Commented out original PDF display and added new pdfViewer 
            $curURL = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
            $docURL = $url;
            if (substr_compare($url, "index.php?",0,count("index.php?")) == 0){
              $docURL = substr($curURL,0,strpos($curURL,"?p=digitallibrary")) . $url;
            }
            pdfViewer($objFile -> Filename, $docURL);
         }
         elseif($objFile->FileType->MediaType->MediaType == 'Image')
         {
            $index += 1;
            //$onclick = ($_ARCHON->config->GACode && $_ARCHON->config->GADigContentPrefix) ? "onclick='javascript: pageTracker._trackPageview(\"{$_ARCHON->config->GADigContentPrefix}/image/DigitalContentID={$objDigitalContent->ID}/fileID={$objFile->ID}\");'": "";
            imageViewer($objFile -> Filename, $url, $index);
         }
         elseif($objFile->FileType->MediaType->MediaType == 'Audio') {
             //mediaViewer($objFile -> Filename, $url, true);
             audioViewer($objFile -> Filename, $url);
         }
         elseif($objFile->FileType->MediaType->MediaType == 'Video' || $objFile->FileType->MediaType->MediaType == 'Other') {
             //mediaViewer($objFile -> Filename, $url, false);
             videoViewer($objFile -> Filename, $url);
         }         
         elseif($objFile->Filename)
         {
            preg_match("/.+?\/(.+)/u", $objFile->FileType->getString('ContentType'), $matches);
            $contenttype = $matches[1] ? $matches[1] : 'file';
            $onclick = ($_ARCHON->config->GACode && $_ARCHON->config->GADigContentPrefix) ? "onclick='javascript: pageTracker._trackPageview(\"{$_ARCHON->config->GADigContentPrefix}/{$contenttype}/DigitalContentID={$objDigitalContent->ID}/fileID={$objFile->ID}\");'": "";
            echo("<script type='text/javascript'>embedFile($objFile->ID, '" . encode($objFile->FileType->MediaType->MediaType, ENCODE_JAVASCRIPT) . "', 'long');</script><br/>");
         }

echo("<span class='digcontentfiletitle'>". $objFile->getString('Title') . " (" . $objFile->FileType->getString('FileType') . ", " . formatsize($objFile->Size) . ")<br/>");


         if($FullAccess)
         {
             if($objFile->FileType->MediaType->MediaType == 'Document')
             {
              echo("<a href = 'http://docs.google.com/gview?url=" .$url."&embedded=true'> View in Reader </a><br/><br/>");
             }
            echo("<a href='$url' download='" . $objFile->FileType->getString('Title') . "' title='" . $objFile->FileType->getString('Title') . "'>Download File</a><br/>");
         }
         else
         {
            echo("<br/>Download of the full file is not publicly available.  Contact the archives for information about accessing this item.<br/>");
         }

         echo("</span>");
      }


//      echo ("<br/><span class='digcontentrequest'><a href='?p=digitallibrary/request&amp;id=" . $_REQUEST['id'] . "&amp;fileid=" . $objFile->ID. "&amp;referer=" . urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . "'>Request quality copy</a></span>");

      echo("<br/><br/></div>");

   }
   echo ("</div>\n");
}



echo("<div id='digcontentmetadata' class='mdround'>\n");

if($_ARCHON->Security->verifyPermissions(MODULE_DIGITALLIBRARY, READ) && !$objDigitalContent->Browsable)
{
   echo("<span class='bold'>NOTE</span>: This metadata is <span class='bold'>NOT</span> searchable by the public.<br/>");
}

if($objDigitalContent->ContentURL && empty($objDigitalContent->Files))
{
    if($_ARCHON->Security->userHasAdministrativeAccess())
    {
   ?>&nbsp;  <!--forces IE to behave -->

<!--This display is for digital library records with no files attached but instead links to other resources.  .-->
<div class='digcontentlabel'>Content URL:</div>
<div class='digcontentdata' style='font-weight:bold'><?php echo("<a href='{$objDigitalContent->getString('ContentURL')}'>{$objDigitalContent->getString('ContentURL')}</a>"); ?></div>
   <?php
}
}

if($objDigitalContent->Title)
{
   ?>

<div class='digcontentlabel'>Title:</div>
<div class='digcontentdata'><?php echo($objDigitalContent->toString()); ?></div>
   <?php
}


if($objDigitalContent->Date)
{
   ?>
<div class='digcontentlabel'>Date:</div>
<div class='digcontentdata'><?php echo($objDigitalContent->getString('Date')); ?></div>
   <?php
}


if($objDigitalContent->Scope)
{
   ?>
<div class='digcontentlabel'>Description:</div>
<div class='digcontentdata'><?php echo($objDigitalContent->getString('Scope')); ?></div>

   <?php
}

if($objDigitalContent->PhysicalDescription)
{
   ?>
<div class='digcontentlabel'>Phys. Desc:</div>
<div class='digcontentdata'><?php echo($objDigitalContent->getString('PhysicalDescription')); ?></div>
   <?php
}

if($objDigitalContent->Identifier)
{
   ?>
<div class='digcontentlabel'>ID:</div>
<div class='digcontentdata'><?php echo($objDigitalContent->getString('Identifier')); ?></div>
   <?php
}

if($objDigitalContent->Collection->Repository)
{
   ?>
<div class='digcontentlabel'>Repository:</div>
<div class='digcontentdata'><?php echo($objDigitalContent->Collection->Repository); ?></div>
   <?php
}


if($objDigitalContent->Collection)
{
   ?>

<div class='digcontentlabel'>Found in:</div>
<div class='digcontentdata'><?php echo($objDigitalContent->Collection->toString(LINK_TOTAL));
      if($objDigitalContent->CollectionContent)
      {
         echo($_ARCHON->PublicInterface->Delimiter . $objDigitalContent->CollectionContent->toString(LINK_EACH, true, true, true, true, $_ARCHON->PublicInterface->Delimiter));
      }
      ?>
</div>
   <?php
}

if($objDigitalContent->Creators && defined('PACKAGE_CREATORS'))
{
   ?>
<div class='digcontentlabel'>Creators:</div>
<div class='digcontentdata'><?php echo($_ARCHON->createStringFromCreatorArray($objDigitalContent->Creators, '<br/>', LINK_TOTAL)); ?></div>
   <?php
}

if($objDigitalContent->Subjects && defined('PACKAGE_SUBJECTS'))
{
   ?>
<div class='digcontentlabel'>Subjects:</div>
<div class='digcontentdata'><?php echo($_ARCHON->createStringFromSubjectArray($objDigitalContent->Subjects, '<br/>', LINK_TOTAL)); ?></div>
   <?php
}

if($objDigitalContent->Publisher)
{
   ?>
<div class='digcontentlabel'>Publisher:</div>
<div class='digcontentdata'><?php echo($objDigitalContent->getString('Publisher')); ?></div>
   <?php
}

if($objDigitalContent->Contributor)
{
   ?>
<div class='digcontentlabel'>Contributor:</div>
<div class='digcontentdata'><?php echo($objDigitalContent->getString('Contributor')); ?></div>
   <?php
}

if($objDigitalContent->RightsStatement)
{
   ?>
<div class='digcontentlabel'>Rights:</div>
<div class='digcontentdata'><?php echo($objDigitalContent->getString('RightsStatement')); ?></div>
   <?php
}


if($objDigitalContent->Languages)
{
   ?>
<div class='digcontentlabel'>Languages:</div>
<div class='digcontentdata'><?php echo($_ARCHON->createStringFromLanguageArray($objDigitalContent->Languages, '&nbsp;', LINK_TOTAL)); ?></div>
   <?php
}
else
{
   ?>
<!--No languages are listed for this digital content.-->
   <?php
}

if($objDigitalContent->ContentURL && !empty($objDigitalContent->Files))
{
    if($_ARCHON->Security->userHasAdministrativeAccess())
    {
   ?>&nbsp;  <!--forces IE to behave -->

<!--This display is for a digital library record that has files attached. -->
<div class='digcontentlabel'>Content URL:</div>
<div class='digcontentdata' style='font-weight:bold'><?php echo("<a href='{$objDigitalContent->getString('ContentURL')}'>{$objDigitalContent->getString('ContentURL')}</a>"); ?></div>
   <?php
}
}

echo('</div>');

if(!empty($objDigitalContent->Files))
{
   echo('</div>');
}
