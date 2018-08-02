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

$sub = '';
$theaction = '';
$order_by = '';
$error_msg = '';
$frm_Name = '';
$img_msg = '';
$frm_Notes ='';
$Bait_ID = 0;
$Gel_ID = 0;
$Exp_ID = 0;
$frm_TaxID = '';
$frm_Date = '';
$DBname = '';
//-----for submit_gel.inc.php----------
$whichGel = '';
$theProjectName = '';
$start_point ='';
//--for gel.inc.php
$frm_Stain = '';
$frm_GelType ='';
$frm_Image ='';
$msg = '';
//-------------------------------------

//-----for submit_bait.inc.php---------
$frm_GeneID = '';
$frm_LocusTag = '';
$frm_GeneName  = '';
$frm_BaitMW = '';
$frm_Family  = '';
$frm_Tag = '';
$frm_Mutation = '';
$frm_Vector = '';
$frm_Clone = 'N/A';
$frm_Description = '';
$frm_BaitAcc = '';
$frm_AccType = '';
$bait_switch = 'new_bait';
$virtual_Tag = '';
$note_action = '';
$frm_disID = '';
//---------------------------------------
//----for submit_exp.inc.php-------------
$whichBait = '';
$frm_OwnerID = '';
$frm_PreySource = '';
$frm_GrowProtocol = '';
$frm_IpProtocol = '';
$frm_DigestProtocol = '';
$frm_PeptideFrag = '';
$GrowProtocolFlag = 0;
$IpProtocolFlag = 0;
$DigestProtocolFlag = 0;
$PeptideFragFlag = 0;
$change_protocol = '';
$Selected_option_str = '';
//-----------------------------------------

//----for submit_band.inc.php--------------
$Band_ID = 0;
$Lane_ID = 0; 
$whichPlate = ''; 
$band_counter = 0; 
$selectedWellCode_str = ''; 
$frm_WellCode = '';
$frm_LaneCode ='';
$frm_Intensity = '';
$frm_BandMW = '';
$frm_Location = '';
$frm_Modification = '';
$frm_LaneNum = '';
$frm_PlateNotes = '';
$frm_DigestedBy = '';
$frm_DigestStarted = '';
$frm_DigestCompleted = '';
$frm_Buffer ='';
$newIntensity ='';
$close_window = '';
$frm_swath = '';

$Plate_ID = ''; 
$band_ID_str  = '';
$WellCode_str = ''; 
$SESSION = '';
$in_Plate_ID = '';
$msg_plate = '';
$CurrPlate = '';
$DigestedBy = '';
$CurrPlate = '';
$PlateName_str = '';

//--plate_layout.inc.php
$tab = '';
$newplate ='';
$GroupID = '';
//-----------------------------------------

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require("analyst/status_fun_inc.php");

if(isset($DBname)){
  $HITSDB = new mysqlDB($HITS_DB[$DBname]);
  $mainDB = new mysqlDB($HITS_DB[$DBname]);
} 
if($gelMode == 0){
  $sub = '1';
}else{
  $sub = '3';
}
$Log = new Log();

$passed = '';
$typeName = $addNewType;
if($addNewType == 'Sample'){
  $typeName = 'Band';
  $passed = '_passed';
}
?>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="../analyst/site_style.css">
</head>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script> 
<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language='javascript'>
function passvalue(){
  var thisForm = document.forms[1];
  opener.document.editform.passed_<?php echo $typeName?>_ID.value = document.forms[1].<?php echo $typeName?><?php echo $passed?>_ID.value;
  if(thisForm.gelMode.value == '0'){
    opener.document.editform.Gel_ID.value = document.forms[1].Gel_ID.value;
  }
  opener.document.editform.submit();
  if(typeof thisForm.close_window != 'undefined'){
    if(document.forms[1].close_window.value == 'close') window.close();
  }  
}
function processAjaxReturn(rp){
  var ret_html_arr = rp.split("@@**@@");
  if(ret_html_arr.length == 2){
    var div_id = ret_html_arr[0];
    document.getElementById(div_id).innerHTML = ret_html_arr[1];
    return;
  }
}    
</script>

<body <?php echo ($theaction=='insert' || $theaction=='insertband')?'onload="passvalue()"':''?>>
<center>
<table cellspacing="0" cellpadding="0" border="0" align=center width=650>
<tr>
    <td><img src="./images/arrow_<?php echo ($addNewType=='Bait')?'red':'green'?>_bait.gif" border=0></td>   
    <td><img src="./images/arrow_<?php echo ($addNewType=='Exp')?'red':'green'?>_exp.gif" border=0></td>
<?php if(!$gelMode){?>
    <td><img src="./images/arrow_<?php echo ($addNewType=='Gel')?'red':'green'?>_gel.gif" border=0></td>
<?php }?>          
    <td><img src="./images/arrow_<?php echo ($addNewType=='Sample')?'red':'green'?>_band.gif" border=0></td>
</tr>
</table>
<form name='empty_form'></form>
<?php 
if($addNewType == 'Bait'){
  include("./submit_bait.inc.php");
}elseif($addNewType == 'Exp'){
  include("./submit_exp.inc.php");
}elseif($addNewType == 'Gel'){
  include("./submit_gel.inc.php");
}elseif($addNewType == 'Lane'){
  include("./submit_sample_gel.inc.php");
}elseif($addNewType == 'Sample'){
  if(!$gelMode){
    include("./submit_sample_gel.inc.php");
  }else{
    include("./submit_sample_gel_free.inc.php");
  }
}
?>
</body>
</html>