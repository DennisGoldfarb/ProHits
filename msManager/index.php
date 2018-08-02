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
$thePage = "home";

include("./ms_header.php");
?> 
<table border=0 width=85% cellpadding="0" cellspacing="0">
  <tr>
    <td align=center>
    <br>
    <font face="Arial" size="+1" color="#28496a"><b>Overview</b></font>
    <hr width="100%" size="1" noshade>
    </td>
  </tr> 
  <tr>
   <td>
    <img src="images/cdc4_4nb3_1.jpg" alt="" border="0" align="right">
    <!--img style='border-width:1;border-color:#000000;border-style:solid;' src="images/ribbon.jpg" alt="" width="400" height="300" border="0" align="right"-->
     
<b><font size="+1">Welcome to ProHits</font></b><br>
ProHits is an open source software tool designed to help scientists manage, search and analyze mass spectrometry data.   

<br><br>
<b><font size="+1">MS Data Management</font></b> allows you to store raw mass spectrometry data from multiple instruments, and to initiate database searches using the commercial Mascot search engine (licence from Matrix Science is necessary) and/or the free Open Source search engine X!Tandem.  Search results can be further analyzed using the TransProteomic Pipeline (TPP, an Open Source software suite), and viewed directly within the MS Data Management module.  Alternatively, search engine (and/or TPP) results can be transferred (parsed) into a bait-centric relational database, the <b><font size="+1">Analyst</font></b> module.
<br><br>
The <b><font size="+1">Storage</font></b> section allows you to monitor the transfer of data from each of the acquisition computers to the ProHits backup system.  It also allows you to search, browse and download files, convert RAW files to other formats, and manually upload RAW data.  
<br><br>
The <b><font size="+1">Auto Search</font></b> section allows you to perform database searching on specified files using user-defined search engines and parameters, to explore the results, and to transfer search results to the Analyst module.  It also allows for database searches to be pre-scheduled for data files that will be acquired at a later time.
 

    <br><BR>
   </td>
  </tr>
   
</table>
<?php 
include("./ms_footer.php");
?>
