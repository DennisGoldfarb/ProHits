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


require("../common/site_permission.inc.php");
require("analyst/classes/dateSelector_class.php");
//$TB_HD_COLOR = '#637eef';
$TB_CELL_COLOR = '#d2dcff';
$TB_HD_COLOR = '#3471cb';
$DateSelector = new DateSelector();
$lastmonth =@mktime(0, 0, 0,@date("m")-1,@date("d"), @date("Y"));
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
  <title>Experiment Report</title>
<link rel="stylesheet" type="text/css" href="./site_style.css"> 
</head>
<script language="javascript">
function display_CSV_file(){
  var theForm = document.progress_report;
  var optionChecked = false;
  for(var i=0; i<theForm.display_option.length; i++){
    if(theForm.display_option[i].checked == true){
      if(theForm.display_option[i].value == "timeRange"){
        var from_time = Date.UTC(theForm.from_Year.value, theForm.from_Month.value, theForm.from_Day.value);
        var to_time = Date.UTC(theForm.to_Year.value, theForm.to_Month.value, theForm.to_Day.value);
        if(from_time < to_time){
          alert("The 'from time' must be great than 'to time'.");
          return false;
        }
      }
      optionChecked = true;    
    }
  }
  if(optionChecked == false){
    alert("Chose one of options");
    return false;
  }
  theForm.submit();
}
</script> 
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 topMargin=5 rightMargin=5 marginheight="0" marginwidth="0">
<form name='progress_report' method=post action='export_experiment_progress.php'>
<table border="0" cellpadding="1" cellspacing="1" align=center width="95%">
  <tr bgcolor="#000000" height=20>
    <td colspan="2" align="center">
    <div class=tableheader><font size=3>Experiment Progress Status</font>
    </div>
    </td>
  </tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR?>">
	  <td colspan="" align="" valign="top" height=35>
      <div class=maintext><font size=2>&nbsp;&nbsp;Select a criterion to export CSV file.</font></div>
	  </td>
	</tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR?>">
	  <td colspan="" valign="top" height=100>
      <b><div class=maintext>&nbsp;&nbsp;<font size=3 color=<?php echo $TB_HD_COLOR?>>Export Experiment Progress</font></div></b>
      <div class=maintext>&nbsp;&nbsp;<font size=2><input type="radio" name='display_option' value='all' checked> All</font></div>
      <div class=maintext>&nbsp;&nbsp;<font size=2><input type="radio" name='display_option' value='hits'> Hits have been parsed</font></div>
      <div class=maintext>&nbsp;&nbsp;<font size=2><input type="radio" name='display_option' value='digest'> Sample has been digested but has no raw file linked</font></div>
      <div class=maintext>&nbsp;&nbsp;<font size=2><input type="radio" name='display_option' value='timeRange'> date range of baits</font></div>
	    <div class=maintext>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font size=2>From&nbsp;<?php echo $DateSelector->setDate('from_');?>
      &nbsp;To&nbsp;<?php echo $DateSelector->setDate('to_', $lastmonth);?></font></div>
    </td>
	</tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR?>">
	  <td colspan="" align="center" height=30>
      <input type="button" value="Export" onclick="display_CSV_file();">
      &nbsp;<input type="reset" value="Reset">
      &nbsp;<input type="button" value="Close" onclick="window.close();">
	   <!--b><a href="./export_experiment_progress.php" class=button>[Export Experiment Progress]</a></b-->
	  </td>
	</tr>
</table>
</form>
</body>
</html>
