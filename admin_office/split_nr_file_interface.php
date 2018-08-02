<?php 
/***********************************************************************
    Prohits version 1.00
    Copyright (C) 2001, Mike Tyers, All Rights Reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
*************************************************************************/

$theaction = '';
$singleFileID = '';
require_once("../common/site_permission.inc.php");
include("./admin_header.php");
$bgcolor = "#d0e4f8";
$bgcolormid = "#89baf5";
$bgcolordark = "#637eef";
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);

$SQL = "SELECT `ID` FROM `Fasta_file_tree`";  
$IDarr = $proteinDB->fetchAll($SQL);
$numFiles = count($IDarr);
$disableRadio = '';
if(!$numFiles){
  $disableRadio = 'disabled';
}
$actionURL = dirname($PHP_SELF) . "/nr_splitor/split_nr_file.php";
?>
<script language="javascript">
var newPop = '';
function popwin(theFile,w,h){
  if (!newPop.closed && newPop.location) {
    newPop.close();
  }
  newPop = window.open(theFile,"parawind",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=' + w + ',height=' + h);  
  newPop.focus();
}
function  Update_all_tables(){
   disable_fileName_list();
}
function  Update_fasta_file_gi_table_only(){
  disable_fileName_list();
}
function Create_or_update_all_files(){
  disable_fileName_list();
}
function Create_or_update_a_individual_file(){
  theForm = document.split_nr_form;
  theForm.singleFileID.disabled = false;
}
function disable_fileName_list(){
  theForm = document.split_nr_form;
  theForm.singleFileID.selectedIndex = '';
  theForm.singleFileID.disabled = true;
}
function submit_form(){
  theForm = document.split_nr_form;
  var oneChecked = 0;
  var thisaction = '';
  var file_id = '';
  for(var i=0; i<theForm.theaction.length; i++){
    if(theForm.theaction[i].checked == true){
      oneChecked = 1;
      var confirm_str = '';
      if(theForm.theaction[i].value == 'createTwotables'){
        confirm_str = "Are you sure you want to create or update both tables 'Fasta_file_tree' and 'Fasta_file_gi' in Protein database?";
        thisaction = 'createTwotables';
      }else if(theForm.theaction[i].value == 'createGiFiletableOnly'){
        confirm_str = "Are you sure you want to create or update table 'Fasta_file_gi' only in Protein database?";
        thisaction = 'createGiFiletableOnly';
      }else if(theForm.theaction[i].value == 'splitNRfile'){
        confirm_str = "Are you sure you want to create or update all fasta files from NR file?";
        thisaction = 'splitNRfile';
      }else if(theForm.theaction[i].value == 'createOneFile'){
        var x = theForm.singleFileID;
        if(x.value == ''){
          alert("Please select a name for creating or updating file.");
        }else{
          file_id = x.value;
          fileName = x.options[x.selectedIndex].text;
          confirm_str = "Are you sure you want to create or update " +fileName+ ".fasta files from NR file?";
          thisaction = 'createOneFile';
        }
      }
      if(confirm_str != '' && confirm(confirm_str)){
        var theFile = "<?php echo $actionURL?>" + "?theaction=" + thisaction + "&singleFileID=" + file_id;
        popwin(theFile,800,800);
        //theForm.submit();
      }
      break;      
    }
  }
  if(oneChecked == 0){
    alert("Please select a option to submit");
  }
}
</script>
<?php 
function file_list($focus_value){
  global $proteinDB; 
  $mainArr = array();  
  array_push($mainArr, '1');
  $itemCount = 0;
  $dot = ' . ';
  while(count($mainArr)&& $itemCount != 100){
    $itemCount++;
    $popItem = array_pop($mainArr);
    $SQL = "SELECT `ID`, `File_name`,`Level` FROM `Fasta_file_tree` WHERE `ID`='$popItem'";
    $fileIDarr = $proteinDB->fetch($SQL);
    if($fileIDarr['ID'] && $fileIDarr['ID'] !=1 ){
      ?>
        <option value="<?php echo $fileIDarr['ID'];?>"<?php echo ($fileIDarr['ID']==$focus_value)?" selected":"";?>><?php echo str_repeat($dot, ($fileIDarr['Level']-2)*5);?><?php echo $fileIDarr['File_name'];?><br>
      <?php 
    }
    $SQL = "SELECT `ID` FROM `Fasta_file_tree` WHERE `Parent_id`='$popItem' ORDER BY File_name DESC";  
    $childrinArr = $proteinDB->fetchAll($SQL);
    foreach($childrinArr as $value){
      array_push($mainArr, $value['ID']);
    }
  }  
}
?>
<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td align="left">
      &nbsp; <font color="<?php echo $bgcolordark;?>" face="helvetica,arial,futura" size="3"><b>Spliting Nr file</b></font>   
    </td>    
  </tr>
  <br>
  <tr>
    <td colspan=2 height=1 bgcolor="<?php echo $bgcolormid?>"><img src="./images/pixel.gif"></td>
  </tr>  
  <tr>    
  <td align="center" valign=top><br>
  <table border="0" cellpadding="0" cellspacing="0" width="350">
    <form name=split_nr_form method=post action='<?php echo $actionURL;?>'>
    <input type="hidden" name="theaction" size="55" value="">
    <tr bgcolor="<?php echo $bgcolordark;?>">
      <td align="left" colspan="4" height=20>
        <font color="white" face="helvetica,arial,futura" size="3">&nbsp;&nbsp;<b>Create or update DB</b></font>
      </td>
    </tr>
    <tr>
      <td colspan=4 height=1 bgcolor="<?php echo $bgcolormid?>"><img src="./images/pixel.gif"></td>
    </tr>   
    <tr bgcolor="<?php echo $bgcolor;?>">
      <td colspan="4" height=22>
        <div class=tableheader_blue height=18>&nbsp;&nbsp;<input type="radio" name="theaction" size="55" value="createTwotables" onclick="Update_all_tables();">
        &nbsp;&nbsp;<b>Update all tables</b></div>
        <div class=maintext height=18>&nbsp;&nbsp;&nbsp;(If a new tax name added this option must be selected.)</div>
      </td>
    </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
      <td colspan="4" height=22>
        <div class=tableheader_blue height=18>&nbsp;&nbsp;<input type="radio" name="theaction" size="55" value="createGiFiletableOnly" onclick="Update_fasta_file_gi_table_only();" <?php echo $disableRadio?>>
        &nbsp;&nbsp;<b>Update fasta_file_gi table only</b></div>
      </td>
    </tr>
    <tr>
      <td colspan=4 height=1 bgcolor="<?php echo $bgcolormid?>"><img src="./images/pixel.gif"></td>
    </tr> 
    <tr bgcolor="<?php echo $bgcolordark;?>">
      <td align="left" colspan="4" height=20>
        <font color="white" face="helvetica,arial,futura" size="3">&nbsp;&nbsp;<b>Create or update fasta files</b></font>   
      </td>
    </tr> 
    <tr>
      <td colspan=4 height=1 bgcolor="<?php echo $bgcolormid?>"><img src="./images/pixel.gif"></td>
    </tr> 
    <tr bgcolor="<?php echo $bgcolor;?>">
      <td colspan="4" height=22>
        <div class=tableheader_blue height=18>&nbsp;&nbsp;<input type="radio" name="theaction" size="55" value="splitNRfile" onclick="Create_or_update_all_files();" <?php echo $disableRadio?>>
        &nbsp;&nbsp;<b>Create or update all files</b></div>
      </td>
    </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
      <td colspan="4" height=22>
        <div class=tableheader_blue height=18>&nbsp;&nbsp;<input type="radio" name="theaction" size="55" value="createOneFile" onclick="Create_or_update_a_individual_file();" <?php echo $disableRadio?>>
        &nbsp;&nbsp;<b>  Create or update a individual file</b></div>
      </td>
    </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
      <td colspan="4" height=22>
        <div class=tableheader_blue height=18>&nbsp;&nbsp;
        <select name="singleFileID" size=<?php echo $numFiles?>" disabled> 
          <option value="">              
				 	<?php 
          file_list($singleFileID);
					?>
  		  </select>&nbsp;&nbsp;<br>&nbsp;   
      </td>
    </tr>
    <tr>
      <td colspan=4 height=1 bgcolor="<?php echo $bgcolormid?>"><img src="./images/pixel.gif"></td>
    </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
      <td colspan="4" height=32 valign=center align=center>
        <input type="button" name="theaction" size="55" value=" submit " onclick="submit_form();">
      </td>
    </tr>  
  </form>
  </table>
  </td>
  </tr>
</table><br>&nbsp;
<?php 
include("./admin_footer.php");
?> 