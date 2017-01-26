<?php
/**
 * Main page for default template
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();
echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

?>

<div id='themeindex' class='bground'>
<dl>
  <dt class='index'>General Contact</dt>
  <dd class='index'>
Carleton College Archives<br/>
<a href ="http://apps.carleton.edu/map/library/floors/1/">Gould Library, room 164</a><br/>
One North College Street<br/>
Northfield MN 55057
<p><a href ="http://apps.carleton.edu/map/library/floors/1/">Map</a>
<p>archives@carleton.edu<br/>
Phone: (507) 222-4270<br/>
Fax: (507) 222-4087</dd>
  
  <dt class='index'>Hours</dt>
  <dd class='index'>Monday - Friday 9 a.m. - 5 p.m.<br/>
and by appointment.
  </dd>
  
    <dt class='index'>Staff</dt>
  <dd class='index'>
<p>Eric S. Hillemann - Senior Associate in Archives<br/>
Office: Gould Library 162<br/>
Phone: (507) 222-5983<br/>
Email: ehillema@carleton.edu
<p>Tom Lamb - Head of Special Collections and Archives<br/>
Office: Gould Library 166<br/>
Phone: (507) 222-7015<br/>
Email: tlamb@carleton.edu
<p>Nat Wilson - Digital Archivist<br/>
Office: Gould Library 161<br/>
Phone: (507) 222-4265<br/>
Email: nwilson@carleton.edu<br/>


  </dd>
  

</dl>
</div>