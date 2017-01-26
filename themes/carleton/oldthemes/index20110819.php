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

<div id='themeindex' class='bground'>
<dl>
<dt class='index'>
<?php include("randomblobid.php");
echo "<a href='..?p=digitallibrary/digitalcontent&id=".$collection.
"'><img src='..?p=digitallibrary/getfile&id=".$id."&preview=long' height=250></a>";
echo "<br>Learn more about this collection: <a href='..?p=digitallibrary/digitalcontent&id=".$collection."'>"
.$title."</a>";
?>
</dt>
</dl>
</div>

<div id='themeindex' class='bground'>
<dl>
  <dt class='index'>Mission</dt>
  <dd class='index'>
    <ul>
      <li>The primary purpose of the Carleton College Archives is to gather, preserve, and make available for institutional reference and public research use documentation and information pertaining to the work, history, and development of Carleton College, or about programs, policies, activities, events, persons, or groups associated with the College.</li>
    </ul>
  </dd>
  <dt class='index'>Contact</dt>
  <dd class='index'>
    <ul>
      <li>The Archives is located on the Carleton College Campus in the Laurence McKinley Gould Library, Level 1, (entrance: Room 164). We are open when staff is present, which is generally Monday - Friday 1 p.m. to 4 p.m. and by appointment, except college holidays. Researchers are best advised to make an appointment in advance of a visit.  For more information on our holdings and using the archives feel free to contact us at archives@carleton.edu or 507-222-4270. </br> <a href ="http://archon.its.carleton.edu/index.php?p=core/index&f=contact">(more...)</a>
</li>
    </ul>
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