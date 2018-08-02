<?php 
/***********************************************************************
 Copyright 2010 Gingras and Tyers labs, 
 Samuel Lunenfeld Research Institute, Mount Sinai Hospital.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*************************************************************************/

require_once("../common/site_permission.inc.php");
include("common_functions.inc.php");

include("./admin_header.php");
?>
<STYLE type="text/css">  
td { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
</STYLE>

<table border="0" cellpadding="0" cellspacing="0" width="95%" align=center>
  <tr>
    <td align="left">
		<br><font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="+2"><b>Admin Office Home</b></font> 
	  </td>
    <td align="right">
      &nbsp;
    </td>    
  </tr>
  <tr>
  	<td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
  	<td colspan=2>
    <br>
The Admin Office section provides interface to establish and modify settings within ProHits.  
If you are using Admin Office for the first time, configure your system in the following order.

<br><br>
<?php 
$is_v2 = false;
$prohitsDB = new mysqlDB(PROHITS_DB);
$SQL = "SHOW TABLES";
$results = mysqli_query($prohitsDB->link, $SQL);
while($row = mysqli_fetch_row($results)){
  if($row[0] == 'SAINT_log'){
    $is_v2 = true;
  }
}

if(!$is_v2){
  echo "<h2>Please Click to <a href=modify_Prohits_db.php target=new><font color=#FF0000 >modify ProHits database to V2.0.0</font></a></h2>";
}

?>

 <OL>   
 <li>
<font size="+1"><b>Backup Setup: </b></font><br>
Go to the "Backup Setup" page.  Link your mass spectrometer acquisition computer(s) to ProHits (indicate whether RAW data from the machine will be available for Auto Search).  If only using the ProHits "Lite" version, which uploads TPP results files without going through MS data management, the "Backup setup" step may be skipped.

</li>
<li>
<font size="+1"><b>Check installation:</b></font><br>
Click 'Installation Checklist' image on this page (bottom right).  This opens up a new page that lists each component of the ProHits system and configuration, and provides error messages if links are broken. 

</li>
<li>
<font size="+1"><b>Protein DB Update:</b></font><br>
ProHits maps all proteins to a single NCBI Gene entry by way of creating a ProHits Protein database.  Go to the Protein DB Update page, and follow the instructions to download the appropriate databases to ProHits. 

</li><li>
<font size="+1"><b>Filter Manager:</b></font><br>
Go to the "Filter Manager" page.  Create or modify Bio Filters, and maintain a gene list for each Bio Filter set.

</li>
<li>
<font size="+1"><b>Project Manager:</b></font><br>
Go to the "Project Manager" page. Create or modify Projects, and set species, lab, and filter set for the project.

</li>
<li>
<font size="+1"><b>User Manager:</b></font><br>
Go to the "User Manager page". Create and set user permissions.

</li></OL>

    </td>
  <tr>
  	<td colspan=2 align=center> 
    <table border=0>
      <tr>
        <td align="center">
    		<a href=./images/flowchart_storage.gif target=blank><img src=./images/flowchart_storage_small.gif border=0></a><br>
        <b>Flow Chart of Auto-Backup</b><br><br>
    	  </td>
        <td align="center">
         <a href=./images/flowchart_search.gif target=blank><img src=./images/flowchart_search_small.gif border=0></a><br>
        <b>Flow Chart of Auto-Search</b><br><br>
        </td>    
      </tr>
      <tr>
        <td align="center">
    		<a href=./images/flowchart_parser.gif target=blank><img src=./images/flowchart_parser_small.gif border=0></a><br>
        <b>Flow Chart of Hits Parser</b><br><br>
    	  </td>
        <td align="center">
        <a href=./check.php target=blank><img src=./images/installchecklist_small.gif border=0></a><br>
        <b>Installation Checklist</b><br><br>
        </td>    
      </tr>
   </table>
   </td>    
  </tr>
</table>
<?php 
include("./admin_footer.php");
?>