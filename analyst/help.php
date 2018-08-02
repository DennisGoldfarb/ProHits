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

require("../config/conf.inc.php");
require("../common/mysqlDB_class2.php");
require("./site_print_header.php");


$prohitsDB = new mysqlDB(PROHITS_DB);
$SQL = " select Name, Alias, Color, `Type`, Description, KeyWord from FilterName order by `Type`";
$filters = $prohitsDB->fetchAll($SQL);
?>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><font face="'MS Sans Serif',Geneva,sans-serif" size="4" color="#008080">
    <br><b><div align="center">Description of pages and security</div></b>
    </font></td>
  </tr>
  <tr height="1">
    <td bgcolor="#006699" height="1">
       <img src="./images/pixel.gif" width="1" height="1" border="0"></td>
  </tr>
</table> 
<table>
 <tr>
  <td><div class=maintext>
    <ul>
     <li><b><a name='Security'><font size="+1">Security</font></a></b><a name='home.php'>&nbsp;</a>
      <br>There are three levels of authority: User Type, Project and Web Page. 
      <ol>
      <li><b>User type</b>:	There are three user types in Prohits (User, lab Supervisor and MS Specialist). Some functions in Prohits msManager can only be run as MS Specialist. The Prohits administrator should be the MS Specialist. 
      <li><b>Project</b>:	All Prohits data has be categorized by projects. A user can access, modify and insert a record in a project depends on the user permission for the project. 
      a project depends on the user permission for the project. 
      <li><b>Web Page</b> Some web pages only can be used by selected users (e.g. Admin office, Auto-search and Auto-Parse).
      </ol>  
        <img src="./images/projectPlan.gif" border=0>
     </li>
     <li><b><a name='Structure'><font size="+1">Data Structure</font></a></b><br>
     <img src='./images/data_structure.gif' border=0><br>
     <li><b><a name='Structure'><font size="+1">Data Pipeline</font></a></b><br>
     <img src='./images/pipeline.gif' border=1><br>
     <li><b><a name='Icons'><font size="+1">Icons</font></b><br>
     <img src="images/icon_carryover_color.gif"> Exclusion Color &nbsp;&nbsp;
      <img src="images/icon_picture.gif"> Gel image &nbsp;&nbsp;
      <img src="images/icon_Mascot.gif"> Mascot Peptide &nbsp;&nbsp;
      <img src="images/icon_GPM.gif"> GPM Peptides &nbsp;&nbsp;
      <img src="images/icon_notes.gif"> Notes &nbsp;&nbsp;
    	<img src='./images/icon_coip_green.gif'> COIP Yes &nbsp;&nbsp;
    	<img src='./images/icon_coip_red.gif'> COIP No &nbsp;&nbsp;
    	<img src='./images/icon_coip_yellow.gif'> COIP Possible &nbsp;&nbsp;
    	<img src='./images/icon_coip_blue.gif'> COIP In Progress
      
      <br>
      <img src="images/icon_plate.gif"  border="0"> Plate &nbsp;&nbsp;
      <img src="images/icon_plate_check.gif"  border="0"> Hits Plate &nbsp;&nbsp;
      <img src="images/icon_tree.gif"  border="0"> Next data level &nbsp;&nbsp;
      <img src="images/icon_picture.gif"  border="0"> Gel image &nbsp;&nbsp;
      <img src="images/icon_purge.gif"  border="0"> Delete &nbsp;&nbsp;
      <img src="images/icon_skull.gif"  border="0"> Failed Bait &nbsp;&nbsp;
      <img src="images/icon_Mascot2.gif"  border="0"> Mascot Results &nbsp;&nbsp;
      <img src="images/icon_GPM2.gif"  border="0"> GPM Results &nbsp;&nbsp;
      
      <br>
      <img src="images/icon_link.gif"  border="0"> No raw file linked &nbsp;&nbsp;
      <img src="images/icon_link_g.gif"  border="0"> Raw file auto-linked &nbsp;&nbsp;
      <img src="images/icon_link_y.gif"  border="0"> Raw file manually linked &nbsp;&nbsp;
      <img src="images/icon_dir_open.gif"  border="0"> Raw file folder &nbsp;&nbsp;
      <img src="images/icon_download.gif"  border="0"> Download raw file &nbsp;&nbsp;
      <img src="images/arrow_small.gif"  border="0"> Next step of submitting sample &nbsp;&nbsp;
      
     <br><br>
     <li><b><a name='submit_sample.php'><font size="+1">Submitting Samples</font></a></b><a name='submit_sample.php'>&nbsp;</a>
     <br><b>Gel based sample</b>:<br>
     <ol>
     <li>Click 'Submit Sample' at the left menu. 
     <li>Click 'Gel' arrow in the submit steps. 
     <li>Select a gel from the gel list options, or 'Add New' gel before going to next step. 
     <li>Select or create a gel based bait for your samples. 
     <li>Default is 'Add New' experiment in the experiment step. Click 'Experiment List' and select a existing experiment if you don't want to create a new experiment. One bait can have multiple experiments and one experiment can have multiple gel lanes.
     <li>Default is 'Add New' gel lane in the plate step. Click 'Back to Lane' to get a gel lane list under the selected experiment. If you want put a sample in an existing gel lane, then select a gel lane. 
     <li>7.	Default is the last plate which has been created. You can select an old plate. You can click 'New Plate' if you want to put bands of the lane in a new plate. Click plate well locations where you put bands for the gel lane. 
     <li>Click 'Save' after completing the sample detail form for each sample. 
     </ol>
     
     <br><b>Gel free sample</b>:<br>
     <ol>
     <li>Click 'Submit Sample' at the left menu.
     <li>Click 'Start from Bait' arrow in the submit steps.
     <li>Select or create a gel free bait for your samples.
     <li>Default is 'Add New' experiment in the experiment step if the bait has no experiment. Otherwise it will be 'Modify' existing experiment form. One bait can only have one experiment. Click the next step arrow icon if you don't want change anything. 
     <li>Click 'Add New Sample' button and the default sample code will be formed by bait id and bait gene name. You can change the sample code but only a-z, A-Z, - and _ characters are allowed. 
     </ol>
     
     <br><br>
     <li><b><a name='Bait.php'><font size="+1">Report by Bait</font></a></b><a name='bait.php'>&nbsp;</a>
     <ol>
     <li>Click 'Report by Bait' at the left menu. A bait list will display bait ID, bait gene name and experiment status. Bait list is sorted in descending order with 100 baits per page. 
     <li>Status color bar will indicate experiments in the bait. One color bar is for one experiment. If there is more than one experiment for a bait (gel based), multiple color bars will show in the status fields. The last two colors indicate if a raw file has been created and hits have been parse. The last color with a 0 means search results have been parsed but there are no hits. No hits can be explained by raw file error or no protein found or proteins (scores, coverage..) met user-defined criteria. Click the color bar and a pop-up window will display a detailed experiment status with raw file location, raw file size and number of hits. Scrolling over the color bar will show a hovering box that displays the last step of the experiment's progress. 
     <li>The 'Next Level' icon is only shown when a bait has an experiment attached. Click the 'Next Level' icon and you will get the experiment list page. 
     <li>If you have project modification permission, you can modify a bait by clicking 'Modify' icon. 
     <li>You can delete a bait if you created the bait and the bait has no experiment attached
     <li>Click the'Bait notes' icon to add bait notes for the bait. There are two types of note: Discussion=0; Experiment fail=1. Bait notes only can be deleted by the note creator. Discussion notes cannot be used for a filtration. However, an Experiment failed note will be displayed in the bait report list with a skull icon in status color bar. 
     <li>If a bait has hits, a 'Report' icon will show in 'Options' field. Clicking the 'Report' icon will take you to the 'Bait Reported Hits' page which displays hits detail for the bait. 
     <li>There are two types of filter sets. The filter sets are defined in 'Admin Office' for the working project.
      <br><b>Bio-filter</b>: a hit gene name falls in one of the bio-filter categories the hit record will be grayed. Click the 'Apply Exclusion' button and the grayed records will be removed from the report page, when the Bio-filter check box is checked. 
      <br><b>Exp-filter</b>: Carry over and Spill Over are applied only by gel based sample. Please read filter description for details. 
     <li>Identical hits have the same color in Gene fields. 
     <li>Click the Hit note icon and a pop-up window will allow you to add hit notes. 

     </ol>
    <li><b><a name='Band'><font size="+1">Report by Sample</font></a></b><a name='band.php'>&nbsp;</a>
    <ol>
     <li>Click 'Processed Sample' at the left menu and a sample list will display sample ID, sample name and experiment status. Sample list is sorted in descending order with 100 baits per page. 
     <li>If a sample is gel based, a Plate icon and gel image icon will appear in the 'Options' field. 
     <li>Click the plate icon. It will show you the sample location in a plate at the pop-window. 
     <li>Clicking the gel image icon will show the gel image in the pop-window. 
     <li>If the sample has hits, a report icon will show in the 'Options' field. Click the icon and you will get the hit list in a pop-window. 

     </ol>
     <li><b><a name='Plate_show.php'><font size="+1">Report by Plate</font></a></b><a name='plate.php'>&nbsp;</a><a name='plate_show.php'>&nbsp;</a>
      <ol>
       <li>Only gel based samples are in plates. 
       <li>Click 'Report by Plate' at the left menu. A plate list will be displayed with sample ID descending sorted. 
       <li>If the plate samples have hits the 'MS Completed' plate icon and 'Report' icon will appear in the 'Options' field. 
       <li>Click the plate icon. It will show the sample location in the plate and sample information. If the sample has hits, the hit list will be at the bottom of the page. 
       <li>If you have 'Modify' permission for the project, you can click [Modify Plate] to add a plate or change 'Digest Started', 'Digest Completed' and 'MS Completed' value. 
       <li>Click 'Report' icon at the plate list, you will get all hits in the plate with filtration functions. 

      </ol>
     <li><b><a name='Gel.php'><font size="+1">Report by Gel</font></a></b><a name='gel.php'>&nbsp;</a>
     <ol>
      <li>Click 'Report by Gel'. A gel list page will be shown with gel ID descending sorted. 
      <li>Click the table header to sort gel list by different fields. 
      <li>'Options' field shows 'Modify' icon, 'Gel image' icon and 'Gel Report' icon. 
      <li>Clicking the 'Modify' icon will show the gel detail with gel lane number and plate information the gel has related. If you have 'Modify' permission you can replace the gel image and add notes to the gel. 
      <li>Click the gel lane number. It will show you samples (bands) of the gel lane in plate locations. 
      <li>Click the 'Check SpillOver' button. A pop-up window will show you if there is any possible spillover in the gel if hits have already parsed to the Prohits Analyst database. Please read the 'SpillOver' description for detail. 
      <li>Please don't upload large images. The uploaded gel image should be less than 3 MG in size. 

     </ol>
   
     <li><b><a name='Comparsion'><font size="+1">Comparison</font></a></b><a name='comparison.php'>&nbsp;</a>
     <ol>
      <li>The Comparison tool can compare different hit level. Bait to Bait, Experiment to Experiment and Sample to Sample. 
      <li><b>Step 1: </b>Select baits
        <ol type="a">
          <li>List only baits they have hits.<br>
          <li>Hold the Ctrl key to select multiple Baits
          <li>Sort by bait gene name, bait ID or bait protein ID to brows baits. 
        </ol>
      <li><b>Step 2: </b>Select experiments
        <ol type="a">
        <li>If each bait has only one experiment it will skip step 2.
        <li>Select at lease one experiment from each bait.
       </ol>
      <li><b>Step 3 : </b>Generate report
        <ol type="a">
         <li>Mascot hit is a circle background with number of unique peptides. 
         <li>GPM hit is a square background with number of unique peptides.
         <li>Color code:
             <br>Red - hit found in all samples.
             <br>Green - hit found in more than one sample.
             <br>Blue - hit found in only one sample.
        <li>Each hit can be clickable to allow user adding hit notes. 
        <li>Click the Go button if you want the report to be refreshed. 
        <li>Protein ID can be any type of protein ID from search results. e.g. GI, yeast OrfName, ENS. 
        <li>If 'Display Mascot hits' check box is unchecked, all Mascot hits will be removed after the page is refreshed. 
        <li>If 'Display GPM hits' check box is unchecked, all GPM hits will be removed after the page is refreshed. 
        <li>You can check/un-check filter, change frequency and score(mascot)/expect(GPM) to view different results. 
        <li>Links should be auto-added based on hits protein key type.
          <br>BioGrade -- yeast ORFName can be found from hit protein key.
          <br>SGD -- yeast ORFName can be found from hit protein key
          <br>NCBI -- protein key is GI number.
          <br>NcbiGene -- hit gene name can be found.
          <br>Ensembl -- Protein key is ENS key.
       <li>Click 'Print Report' a graphic report will display in a pop-window. 
       <li>Click 'Export Report' to download a CSV file. 
       <li>Click 'Export Osprey' to download a CSV file which supports the Osprey application. 
      </ol>
    <li><b>Step 4 : </b>Group Comparison
      <ol type="a">
        <li>User can group sample(s) when user wants to compare their hits among groups.  It will display if different groups have shared hits, especially useful when you have negative and positive controls.
        <li>A drop down list is in each sample for group selection. User can have more than one sample in each group. Each group can have more than one sample. 
        <li>There is a control group (CTL). If CTL group is selected, other group will only compare to the control group. Otherwise groups will compare to each other.
        <li>If a sample is not in any group, the sample will be absent in the comparison page 
        <li>Control group will have yellow background.
        <li>Bio-filters and Exp-filters still applies in the group comparison (e.g. filter one peptide and low score hits).
      </ol>
     </ol>
    <br>
    <li><b><a name='Filter'><font size="+1">Filter Description</font></a></b><br>
    The color beside the filter name is used in the hit report. If a hit has the color in a hit record it means the protein is in the filter. The hit will be grayed out in the hit report. The hit will be removed if the filter check box is checked when you click the 'Apply Exclusion' button. 
      <ol>
      <?php 
      if($filters){
        foreach($filters as $tmpfilter){
         if($tmpfilter['Type'] == 'Non' or !$tmpfilter['Type']){
            $tmpfilter['Type'] = 'Manual';
         }
         echo "<li><b>".$tmpfilter['Name'].":</b> (".$tmpfilter['Type'].")<span style='background-color: ".$tmpfilter['Color']."'> &nbsp; </span>\n";
         if($tmpfilter['Description'])
         echo  "<br>".$tmpfilter['Description']."<br>". $tmpfilter['KeyWord']."\n";
        }
      }
      ?>
      </ol>
    </ul>
    
    </div>
  </td>
</table>
</BODY>
   
