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

set_time_limit(2400);
$php_file_name = 'export_hits';

$bg_tb_header = '#7eb48e';
$tb_color = '#e3e3e3';
$tb_color2 = '#d1e7db';
$titleBarW = '90%';

$filterStyleDisplay = 'none';
$theaction = '';
$fileExtention = "csv";
$titleLine = '';
$selecte_columns_str = '';
$item_ID = '';
$Expect = '';
$source = '';
$export_version = '';
$duplicate_log_title = 0;
$frm_sub_version = '';

$currentType = "Sample";
$SearchEngine = '';
$bait_as_name = '';
$msg_err = '';

$frm_apply_filter = '';
$frm_filter_Expect = '';
$frm_filter_Coverage = '';
$frm_filter_Peptide = '';
$frm_filter_Peptide_value = '';
$frm_filter_Fequency = '';
$frm_filter_Fequency_value = '';
$frm_NS_group_id = '';
$frm_min_XPRESS = '';
$frm_max_XPRESS = '';
$is_count_seq_len = 'Y';
$frm_start_with = '';
$frm_end_with = '';
$remove_pb_same_gene = 'Y';
$include_geneID = '';
$saint_record = array();

$saintName = '';
$saintDescription = '';
$has_control = '';

$nControl = 0;
$nCompressBaits = '2';
$nburn = 2000;
$niter = 5000;
$lowMode=0;
$minFold=1; 
$fthres = '0';
$fgroup = '0';
$var = '0';
$normalize = '1';
$saint_ID = '';
$disabled_sample_ids = '';
$control_arr = array();
$merge_proteinID = '';
$frm_is_collapse = '';
$saint_type = 'express';
$Is_geneLevel = 0;

//=======================================
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
include("analyst/comparison_common_functions.php");
require_once("msManager/is_dir_file.inc.php");
require_once("admin_office/update_protein_db/auto_update_protein_add_accession.inc.php");
require("analyst/export_lable_arrs.inc.php");

ini_set("memory_limit","-1");

$SAINT_info = check_SAINT();

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

//-------------------------------------------------------------------------------------------------------------
$SearchEngineConfig_arr = get_project_SearchEngine();
$SearchEngine_lable_arr = get_SearchEngine_lable_arr($SearchEngineConfig_arr);
//-------------------------------------------------------------------------------------------------------------

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);

if(strstr($SearchEngine, "TPP_")){
  $hits_table = "TppProtein";
  $hitType = 'TPP';
}elseif(strstr($SearchEngine, "GeneLevel_")){
  $hits_table = "Hits_GeneLevel";
  $hitType = 'geneLevel';
}else{
  $hits_table = "Hits";
  $hitType = 'normal';
}

$WHERE = SearchEngine_WHERE_OR($SearchEngine);

$AccessUserID = $_SESSION['USER']->ID;
$outDir = "../TMP/SAINT_".$AccessUserID."_report/";
if(!_is_dir($outDir)) _mkdir_path($outDir);
$bait_filename = $outDir."bait.dat";
$inter_filename = $outDir."inter.dat";
$prey_filename = $outDir."prey.dat";
$iRefIndex_inter_filename = $outDir."iRefIndex.dat";

$log_filename = $outDir."log.dat";

$zip_file_name = "SAINT_input_files.zip";
$zip_file_full_name = $outDir.$zip_file_name;  
$fileDelimit = "\t";

$w_new_line = "\r\n";
$Warning_line = "Warning:$w_new_line
following prey gene name or protein sequence$w_new_line
length cannot be found from ProHits in both$w_new_line
Inter.dat and Prey.dat files. If no prey gene$w_new_line
name found the protein ID will be used. If no$w_new_line
prey sequence found the average sequence length$w_new_line
will be used.$w_new_line";

$disabled_item_ids_arr = explode(",", $disabled_sample_ids);

$Notice_arr = array();
if($saint_ID){
  $SQL = "SELECT ID, `Name`,`UserID`, `Date` , `Description`, `Status` , `ProjectID`, `ParentSaintID`, `UserOptions`
  FROM SAINT_log WHERE  ID='$saint_ID' and UserID='$AccessUserID'";
  $saint_record = $PROHITSDB->fetch($SQL);
  if(!$saint_record){
    echo "The record doesn't exist: saint_ID=$saint_ID and UserID='$AccessUserID'";
    exit;
  }
}
if($theaction == "download_file"){
  if(_is_file($zip_file_full_name)){
    header("Cache-Control: public, must-revalidate");
    header("Content-Type: application/octet-stream");  //download-to-disk dialog
    header("Content-Disposition: attachment; filename=".basename($zip_file_full_name).";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: "._filesize($zip_file_full_name));
    ob_clean();
    readfile("$zip_file_full_name");
  }else{
    echo "$zip_file_full_name is not exist";
  }
  //if(_is_dir($outDir)) delete_directory($outDir);
  exit;
}elseif($theaction == "run_saint"){
  $other_option='';
  if($saint_type != 'express'){
    if(isset($SAINT_info['version']) and $SAINT_info['version']){
      $other_option =  "Version:".$SAINT_info['version'].",";
    }
  }else if(isset($SAINT_info['version_exp']) and $SAINT_info['version_exp']){
    $other_option =  "Version:".$SAINT_info['version_exp'].",";
  }
  
  $other_option .= "saint_type:$saint_type,";
  $other_option .= "nControl:$nControl,";
  $other_option .= "fthres:$fthres,";
  $other_option .= "fgroup:$fgroup,";
  $other_option .= "var:$var,";
   
  $other_option .= "nburn:$nburn,";
  $other_option .= "niter:$niter,";
  
  $other_option .= "lowMode:$lowMode,";
  $other_option .= "minFold:$minFold,";
  $other_option .= "normalize:$normalize,";
  $other_option .= "nCompressBaits:$nCompressBaits,";
   
  $saintName = mysqli_escape_string($PROHITSDB->link, $saintName);
  $saintDescription = mysqli_escape_string($PROHITSDB->link, $saintDescription);
  //check if the name has been used.
  $SQL = "SELECT ID FROM SAINT_log WHERE  Name='$saintName' and ProjectID='$AccessProjectID'";
  if($saint_ID and $saint_record['Status'] != 'Finished'){
    $SQL .= " and ID<>'$saint_ID'";
  }
  $msg_err = '';
  if($PROHITSDB->exist($SQL)){
    $msg_err = "The SAINT name '$saintName' has been used by other task.";
  }
   
   
  if($SAINT_info['error']) $msg_err = $SAINT_info['msg'];
  if(!$msg_err){
    if($saint_ID){
       if(strpos($saint_record['Name'], "(uploaded)")){
          if(!strpos($saintName, "(uploaded)")){
            $saintName .= " (uploaded)";
          }
       }
    }
     
    $SQL = "SAINT_log set 
      Name='$saintName', 
      Description='$saintDescription',
      UserID='$AccessUserID',
      ProjectID='$AccessProjectID',
      Status = '',
      Date=now(),
      UserOptions='$other_option'
      ";
    if($saint_ID){
      $saint_folder = STORAGE_FOLDER."Prohits_Data/SAINT_results/saint_$saint_ID/";
    }
    if($saint_ID and $saint_record['Status'] != 'Finished'){
      $SQL = "update " . $SQL . " where ID='$saint_ID'";
      $PROHITSDB->update($SQL);
    }else{
      $SQL = "insert into " . $SQL;
      if($saint_ID){
        $SQL .= ", ParentSaintID='$saint_ID'";
      }
      $new_saint_ID = $PROHITSDB->insert($SQL);
      $new_saint_folder = STORAGE_FOLDER."Prohits_Data/SAINT_results/saint_$new_saint_ID/";
      umask(0);
      if(!is_dir($new_saint_folder)){
        if (!mkdir($new_saint_folder,0775, true)) {
          die('Failed to create folders in Prohits storage.');
        }
      }
    }
    if(isset($new_saint_folder)){
      if(!$saint_ID){
        dir_copy($outDir,$new_saint_folder);
      }else{
        dir_copy($saint_folder,$new_saint_folder);
      }
    }else{
      $new_saint_folder = $saint_folder;
      $new_saint_ID = $saint_ID;
    }
    if(_filesize($new_saint_folder."inter.dat")){
      $com = "rm -rf $new_saint_folder"."RESULT*";
      shell_exec($com . " 2>&1");      
      if($new_saint_ID){
        $shell_log = $new_saint_folder."shell.log";
        $com = PHP_PATH ." ". dirname(__FILE__) ."/export_SAINT_shell.php ".$new_saint_ID. " " . $other_option;
        if(defined('DEBUG_SAINT') and DEBUG_SAINT){
          echo "Prohits SAINT stopped by administrator:<br> if you are Prohits admin copy following line and run it on the server shell for debug.<br>\n";
          echo "<font color=green>$com</font>";
          exit;
        }
        writeLog_a($com, '../logs/saint.log');
        $tmp_PID = shell_exec($com . " >> $shell_log 2>&1  & echo $!");
        $tmp_PID = trim($tmp_PID);
        $SQL = "update SAINT_log set ProcessID='".$tmp_PID."', Status='Running' where ID='".$new_saint_ID."'";
        $PROHITSDB->update($SQL);
         
        echo "<script language='javascript'>\n";
        echo "window.location='".$_SERVER['PHP_SELF']."?theaction=status&PID=".$tmp_PID."&saint_ID=".$new_saint_ID."';\n";
        echo "</script>\n";
        exit;
      }
    }else{
      echo "error: inter.dat file file size is 0.";exit;
    }
  }else{
    //run saint error
    $theaction='re_run_saint';
  }
}else if($theaction == "status"){
  check_saint_status($saint_ID, $PID);
  exit;
}elseif($theaction == "generate_SAINT_file"){
   ob_end_clean();
   ob_start();
   
?>
  <center>
  <div style='display:block' id='process'>
  Please be patient. Prohits is trying to get prey sequence length. It will speed up the process when searched fasta file has been uploaded to Prohits from admin office.<br>
  <img src='./images/process.gif' border=0>
  <?php
  echo str_repeat("\n", 256);
  ?>
  </div> 
  </center>
<?php 
  ob_flush();
  flush();

  $frm_start_with = format_pattern($frm_start_with,'start');
  $frm_end_with = format_pattern($frm_end_with,'end');
  $item_arr = array();
  $control_arr = array();
  $item_id_str_s = '';
   
  foreach($request_arr as $key => $value){
    if(strstr($key, 'TEXT_')){
      $tmp_id_arr = explode("_", $key);
      if(in_array($tmp_id_arr[1], $disabled_item_ids_arr)) continue;
      if(!array_key_exists($value, $item_arr)){
        $item_arr[$value] = $tmp_id_arr[1];
      }else{
        $item_arr[$value] .= ",".$tmp_id_arr[1];
      }
      if($item_id_str_s) $item_id_str_s .= ',';
      $item_id_str_s .= $tmp_id_arr[1];  
    }elseif(strstr($key, 'CHECK_')){
      $tmp_check_id_arr = explode("_", $key);
      if(in_array($tmp_check_id_arr[1], $disabled_item_ids_arr)) continue;
      array_push($control_arr, $tmp_check_id_arr[1]);
    }
  }
  
  $selected_item_id_str = '';
  if($item_id_str_s){
    if($currentType == 'Bait' || $currentType == 'Exp'){
      if($frm_is_collapse == 'sum' || $frm_is_collapse == 'average'){
        $selected_item_id_str = str_replace(",", "; ", $item_id_str_s);
      }else{
        $selected_item_id_str = get_selected_item_id_str();
      }
    }else{
      if($frm_is_collapse == 'no'){     
        $selected_item_id_str = str_replace(",", "; ", $item_id_str_s);
      }else{
        if($frm_is_collapse == 'sum' || $frm_is_collapse == 'average'){
          $selected_item_id_str = get_selected_item_id_str('Bait');
        }else{
          $selected_item_id_str = get_selected_item_id_str('Exp');
        }
      }  
    }
  }
  @dir_empty($outDir);
 
  if($Is_geneLevel){
    $is_count_seq_len = '';
    $merge_proteinID = '';
  } 
  ob_start();
?>

<div style='display:block' id='process2'>

<?php 
  include("./export_SAINT_file_inc.php");
?>
</div>
  <script language='javascript'>
  document.getElementById('process').style.display = 'none';
  document.getElementById('process2').style.display = 'none';
  </script>  
<?php 
  ob_get_contents();
  ob_flush();
  flush();
}
if($saint_ID and $theaction == "re_run_saint"){   
  $saintName = $saint_record['Name'];
  $saintDescription = $saint_record['Description'];
  $op_arr = explode(",", $saint_record['UserOptions']);
  foreach($op_arr as $set){
    $set = preg_replace("/\s/", "", $set);
    $set_arr = explode(":", $set);
    if(count($set_arr) == 2){
      $$set_arr[0] = $set_arr[1];
    }
  }
  $saint_folder = STORAGE_FOLDER."Prohits_Data/SAINT_results/saint_$saint_ID/";
  $bait_dat_file = $saint_folder."bait.dat";
  $sait_log_file = $saint_folder."log.dat";
  $sait_input_file_zip = $saint_folder."SAINT_input_files.zip";
}else if($theaction == 'generate_map_file'){
  $item_id_str = ''; 
  $item_id_arr_tmp = explode(";", $frm_selected_list_str);
  foreach($item_id_arr_tmp as $tmp_val){
    $item_id_arr_tmp2 = explode(":",$tmp_val);
    if($item_id_arr_tmp2 > 1){
      if($item_id_str) $item_id_str .= ",";
      $item_id_str .= $item_id_arr_tmp2[1];
    }  
  }
  if(!$item_id_str) exit;
  
  if($currentType == 'Bait'){
    $item_name = 'Bait';
    if($frm_is_collapse == 'sum' || $frm_is_collapse == 'average'){
      $SQL = "SELECT ID, 
              GeneName AS Name,
              GeneName AS Bait_name 
              FROM Bait 
              WHERE ID IN ($item_id_str)
              ORDER BY GeneName";
    }else{
      $SQL = "SELECT D.ID, 
              B.GeneName AS Name,
              B.GeneName AS Bait_name 
              FROM Band D 
              LEFT JOIN Bait B ON (B.ID=D.BaitID)
              WHERE  B.ID IN ($item_id_str)
              ORDER BY B.GeneName";
    }          
  }elseif($currentType == 'Exp'){
    $item_name = "Experiment";
    if($frm_is_collapse == 'sum' || $frm_is_collapse == 'average'){
      $SQL = "SELECT E.ID, 
              E.Name,
              B.GeneName AS Bait_name  
              FROM Experiment E
              LEFT JOIN Bait B ON (E.BaitID=B.ID)
              WHERE  E.ID IN ($item_id_str)
              ORDER BY E.Name";
    }else{
      $SQL = "SELECT D.ID, 
              E.Name,
              B.GeneName AS Bait_name  
              FROM Band D 
              LEFT JOIN Experiment E ON (D.ExpID=E.ID)
              LEFT JOIN Bait B ON (D.BaitID=B.ID)
              WHERE  E.ID IN ($item_id_str)
              ORDER BY E.Name";
    }          
  }elseif($currentType == 'Band'){
    $item_name = "Sample";
    if($frm_is_collapse == 'no'){
      $item_name = "Sample";
      $SQL = "SELECT D.ID,
              D.Location AS Name,
              B.GeneName AS Bait_name 
              FROM Band D
              LEFT JOIN Bait B ON (D.BaitID=B.ID) 
              WHERE D.ID IN ($item_id_str) ORDER BY D.Location";
    }elseif($frm_is_collapse == 'sum' || $frm_is_collapse == 'average'){
      $item_name = "Bait";
      $SQL = "SELECT B.ID, 
              B.GeneName AS Name,
              B.GeneName AS Bait_name 
              FROM Band D 
              LEFT JOIN Bait B ON (B.ID=D.BaitID)  
              WHERE D.ID IN ($item_id_str)
              GROUP BY B.ID
              ORDER BY GeneName";
    }else{
      $item_name = "Experiment";
      $SQL = "SELECT E.ID, 
              E.Name,
              B.GeneName AS Bait_name  
              FROM Band D
              LEFT JOIN Experiment E ON (D.ExpID=E.ID)
              LEFT JOIN Bait B ON (D.BaitID=B.ID)
              WHERE  D.ID IN ($item_id_str)
              GROUP BY E.ID
              ORDER BY E.Name";
    }          
  }
  $item_propty_arr = $HITSDB->fetchAll($SQL); 
  
  $not_empty_item_arr = array();     
  foreach($item_propty_arr as $item_propty_val){
    $has_band = 1;
    if($currentType == 'Band'){
      if($frm_is_collapse == 'no'){
        $SQL = "SELECT `ID` FROM $hits_table $WHERE `BandID` = '".$item_propty_val['ID']."' LIMIT 1";
      }elseif($frm_is_collapse == 'sum' || $frm_is_collapse == 'average'){
        $SQL = "SELECT `ID` FROM $hits_table $WHERE `BaitID` = '".$item_propty_val['ID']."' LIMIT 1";
      }else{
        $SQL = get_sqlStr_expID($item_propty_val['ID']);
        if(!$SQL) $has_band = 0;
      }
    }elseif($currentType == 'Bait'){
      if($frm_is_collapse == 'no'){
        $SQL = "SELECT `ID` FROM $hits_table $WHERE `BandID` = '".$item_propty_val['ID']."' LIMIT 1";
      }elseif($frm_is_collapse == 'sum' || $frm_is_collapse == 'average'){
        $SQL = "SELECT `ID` FROM $hits_table $WHERE `BaitID` = '".$item_propty_val['ID']."' LIMIT 1";
      }
    }elseif($currentType == 'Exp'){
      if($frm_is_collapse == 'no'){
        $SQL = "SELECT `ID` FROM $hits_table $WHERE `BandID` = '".$item_propty_val['ID']."' LIMIT 1";
      }elseif($frm_is_collapse == 'sum' || $frm_is_collapse == 'average'){
        $SQL = get_sqlStr_expID($item_propty_val['ID']);
        if(!$SQL) $has_band = 0;
      }
    }
    if($has_band){
      $tmp_arr = $HITSDB->fetchAll($SQL);
      if($tmp_arr) array_push($not_empty_item_arr, $item_propty_val['ID']);
    }  
  }
  if(!$item_propty_arr) exit;
} 

$w_new_line = "";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Export hits SAINT</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
<script language="JavaScript" type="text/javascript">
<!--
function checkempty(theform){
  var frm_start_with = theform.frm_start_with.value;
  var frm_end_with = theform.frm_end_with.value;
  var has_control = false;
  frm_start_with = trim(frm_start_with);
  frm_end_with = trim(frm_end_with);
  if(frm_start_with != '' && !onlyAlphaNumerics(frm_start_with, 8)){
    alert("only characters '-_A-Za-z0-9,:|#' are allowed");
    return false; 
  }
  if(frm_end_with != '' && !onlyAlphaNumerics(frm_end_with, 8)){
    alert("only characters '-_A-Za-z0-9,:|#' are allowed");
    return false; 
  }
  for(i=0; i<theform.elements.length; i++){
    if(theform.elements[i].name.match(/TEXT/)){  
      if(trimString(theform.elements[i].value) == ''){
        alert("The name field cannot be emptly");
        return false; 
      }
    }else if(theform.elements[i].name.match(/CHECK_/)){ 
      if(theform.elements[i].checked){
        has_control = true;
      }
    }
  }
  var disabled_IDs = theform.disabled_IDs;
  var disabled_sample_ids = '';
  if(disabled_IDs.length == undefined){
    if(disabled_IDs.checked){
      alert("You disabled all samples");
      return;
    }
  }else{
    var counter = 0;
    for(i=0; i<disabled_IDs.length; i++){
      if(disabled_IDs[i].checked){
        counter++;
        if(disabled_sample_ids) disabled_sample_ids += ',';
        disabled_sample_ids += disabled_IDs[i].value;
      }
    }
    if(counter == disabled_IDs.length){
      alert("You disabled all samples");
      return;
    }
  }
  theform.disabled_sample_ids.value = disabled_sample_ids;
  if(!has_control){
    if(!confirm('You are attempting to generate SAINT files without controls. Are you sure that you want to do this?')){
      return false; 
    }
  }
  theform.theaction.value = "generate_SAINT_file"; 
  theform.submit();
}

function download_file(theform){
  theform.theaction.value = "download_file"; 
  theform.submit();
}  
function run_SAINT(theform){
  var num = parseInt(theform.has_control.value);
   
  if(num > 0){
    var num2 = parseInt(theform.nControl.value);
    if(num < num2){ 
      alert("The compressed control number cannot be greater than the control simples you have selected!");
      return false;
    }else if(num2.value < 1){
      alert("Please input the compressed control number!");
      return false;
    }
  }
  if(isEmptyStr(theform.saintName.value) || isEmptyStr(theform.saintDescription.value)){
    alert("SAINT log Name and Description are required!");
    return false;
  }
  theform.theaction.value = "run_saint";
  
  theform.submit();
}
function toggle_itme_type_name(theform){
  var disabled_IDs = theform.disabled_IDs;
  var disabled_sample_ids = '';
  theform.theaction.value = 'generate_map_file';
  if(disabled_IDs.length == undefined){
    if(disabled_IDs.checked){
      disabled_sample_ids = disabled_IDs.value;
    }  
  }else{
    for(i=0; i<disabled_IDs.length; i++){
      if(disabled_IDs[i].checked){
        if(disabled_sample_ids) disabled_sample_ids += ',';
        disabled_sample_ids += disabled_IDs[i].value;
      }
    }
  }
  theform.disabled_sample_ids.value = disabled_sample_ids;
  theform.submit();
}
function change_tr_bg_color(checkbox_id,cellBgcolor){
  var checkbox_obj = document.getElementById(checkbox_id);
  var td_obj_id = 't_' + checkbox_id;
  var td_obj = document.getElementById(td_obj_id);
  if(checkbox_obj.checked){
    td_obj.style.backgroundColor = '#c0c0c0';
  }else{
    td_obj.style.backgroundColor = cellBgcolor;
  }
} 
function checkControl(obj, sampleID){
  var text_obj = document.getElementById('TEXT_'+ sampleID);
  if(obj.checked){
    text_obj.value = sampleID + '_' + text_obj.value;
  }else{
    text_obj.value = text_obj.value.replace(sampleID + '_', '');
  }
}
//-->
</script>
</head>
<body>
<FORM ACTION="<?php echo $_SERVER['PHP_SELF'];?>" ID="" NAME="generate_SAINT_form" METHOD="POST">
<input type="hidden" name="SearchEngine" value="<?php echo $SearchEngine;?>">
<input type="hidden" name="disabled_sample_ids" value="">
<input type="hidden" name="saint_ID" value="<?php echo $saint_ID;?>">
<input type="hidden" name="item_name" value="<?php echo $item_name;?>">
<input type="hidden" name="Is_geneLevel" value="<?php echo $Is_geneLevel;?>">
<?php 
if($theaction != "generate_map_file"){  
  echo "<input type='hidden' name='theaction' value='$theaction'>";
}
?>
<table border=0 width=95% cellspacing="1" cellpadding=1 align=center>
  <tr>
    <td align=left nowrap><span class=pop_header_text>Generate SAINT Report</span></td>
  </tr>  
  <tr>
    <td height='1'><hr size=1>
<?php if($theaction == "generate_SAINT_file" or $theaction == 're_run_saint'){
    $saint_form_div = 'block';
    if($theaction == "generate_SAINT_file"){
        $saint_form_div = 'none';
?>
    <DIV STYLE="border: #a0a7c5 solid 1px">
    <table border=0 width=100% cellspacing="1" cellpadding=1 align=center>
      <tr  height="25">    
      <td>
        The following four files have been created: bait.dat, inter.dat, prey.dat and log.dat. They were zipped in the file <?php echo $zip_file_name?>.Please click the 'Download' button to get the zipped file. 
        <br><br>
      </td>
      </tr>
      <tr bgcolor="#d2d2d2">
        <td>
        
        <input type=Radio name='run_saint' value=0 onClick="showhideDiv('download_it', 'send_to_saint' )" checked>Download SAINT Compatible Files<br>
        <input type=Radio name='run_saint' value=1 onClick="showhideDiv('send_to_saint', 'download_it')">Run SAINT Directly 
        
        </td>
      </tr>
      
    </table>
    </DIV>
    
    <br>
      <center>
      <DIV ID=download_it STYLE="width: 100%;margin-left: auto;margin-right: auto;">
       <input type=button name='sort' value=' Download ' onClick="download_file(this.form)" > 
      </DIV>
      </center>
    <?php 
    }else{
      display_saint_infor($saint_record);
    }   
    
    if(!$has_control) {
      $has_control = count($control_arr);
      if($saint_type != 'saint_no_control' and $nControl){
       $has_control = $nControl;
      }
    }
    ?>
    <DIV ID=send_to_saint STYLE="display: <?php echo $saint_form_div;?>;">
    
    <table border=0 width=100% cellspacing="1" cellpadding=1 align=center bgcolor=#345ecb>
      <tr>
          <td colspan='4'> 
          <font color="#FFFFFF">
          <b><font size=+1>SAINT express</font></b>(<?php echo $SAINT_info['version_exp'];?>)<input type=radio value='express' name=saint_type<?php echo ($saint_type=='express')?" checked":"";?> >&nbsp; &nbsp; &nbsp; 
          <?php if($has_control){?>
          <b><font size=+1>SAINT</font></b>(<?php echo $SAINT_info['version'];?>)<input type=radio value='saint_control' name=saint_type<?php echo ($saint_type=='saint_control')?" checked":"";?> > &nbsp; &nbsp; &nbsp;
          <?php }else{?>
          <b><font size=+1>SAINT without control</font></b>(<?php echo $SAINT_info['version'];?>)<input type=radio value='saint_no_control' name=saint_type<?php echo ($saint_type=='saint_no_control')?" checked":"";?> ><br>
          <?php }
          echo "<font color=\"#FF0000\">$msg_err</font>";
          ?>
          </font>
          </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top width=30%><b>Use SAINT with controls</b></td>
          <td colspan=3 width=80%>
          
          <?php 
          
          if($has_control){
            if($has_control > 4) {
              $nControl = 4;
            }else{
              $nControl = $has_control;
            }
          ?>
           
            <font color="#008000">You have selected <?php echo $has_control;?> control sample(s) in previous step.</font><br>
            How many compressed controls:
            <input type=text size="2" maxlength="2" value='<?php echo $nControl;?>' name=nControl>
          <?php 
          }else{
          ?>
          <font color="#008000">You are going to run SAINT without controls</font>.<br>
           
          frequency threshold for preys above which probability is set to 0 in all IPs:
          <input type=text size="3" maxlength="3" value='<?php echo $fthres;?>' name=fthres><br>
          frequency boundary dividing high and low frequency groups:
          <input type=text size="3" maxlength="3" value='<?php echo $fgroup;?>' name=fgroup><br>
          binary [0/1] indicating whether variance of count data distributions should be modeled or not:
          <input type=text size="3" maxlength="3" value='<?php echo $var;?>' name=var>
          
          <?php }?>
          <input type="hidden" name="has_control" value="<?php echo $has_control;?>"> 
          </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top width=30%><b>Compress baits</b></td>
          <td colspan=3> <input type=text size=5 value='<?php echo $nCompressBaits;?>' name=nCompressBaits>
           replicates in each interaction with the highest counts is involved in the computation of the scores
          </td>
      </tr> 
      <tr bgcolor=white>
          <td align=right ><b>Burn-in period</b></td>
          <td>nburn:<input type=text size=5 value='<?php echo $nburn;?>' name=nburn> </td>
          <td align=right ><b>Iterations</b></td>
          <td>niter:<input type=text size=5 value='<?php echo $niter;?>' name=niter></td>
      </tr>
      <tr bgcolor=white>
          <td align=right ><b>exclude extremely high counts</b></td>
          <td>lowMode:<input type=text size=5 value='<?php echo $lowMode;?>' name=lowMode> </td><br>
          <td align=right ><b>forcing separation  </b></td>
          <td>minFold:<input type=text size=5 value='<?php echo $minFold;?>' name=minFold></td>
      </tr>
      <tr bgcolor=white>
          <td align=right colspan=3><b>divide spectral counts by the total spectral counts of each IP</b></td>
          <td>normalize:<input type=text size=5 value='<?php echo $normalize;?>' name=normalize></td>
      </tr>
      
      
      <tr>
          <td colspan='4'> <font color="#FFFFFF"><b>SAINT Log Information</b></font></td>
      </tr>
      <tr bgcolor=white>
          <td align=right ><b>Name</b></td>
          <td colspan=3><input type=text size=60 value='<?php echo $saintName;?>' name=saintName> </td>
      </tr>
      <tr bgcolor=white>
          <td align=right ><b>Description</b></td>
          <td colspan=3><textarea cols="60" rows="4" name="saintDescription"><?php echo $saintDescription;?></textarea></td>
      </tr>
      <tr>
        <td colspan=4' align=center bgcolor=#ffffff><input type=button name='RunSAINT' value=' Run SAINT ' onClick="run_SAINT(this.form)" ></td>
      </tr>
    </table>
    </DIV>    
  </td>
  </tr>
  <tr><td>&nbsp;</td>
  </tr>
<?php }else{?>
   <br>
    &nbsp;Instructions<a id='instruction_a' href="javascript: toggle_group_description('instruction')" class=Button>[+]</a>
<DIV id='instruction' STYLE="display: none; border: #a0a7c5 solid 1px; font-family: ARIAL; font-size: 10pt">
<ul>
<li>ProHits retrieves the "Sample ID" and "Sample Name" for each of the selected samples (these cannot be modified).
<li>Initially, the "SAINT Bait Name" is a copy of the "Sample Name"; however the information can be modified. The
"SAINT bait name" is the name you want to give to the bait. SAINT will group by default all identical bait names.
If you do not want this grouping, ensure that the "SAINT Bait Name" is different. For example: "bait_drug" as opposed
to "bait_nodrug".<br>
<li>The "Control" column shows a clickable box; use this box to indicate which of the samples should be used as negative
controls (optimally, you should have between 4-10 controls).<br>
<li>Press the "Export" button. ProHits will use this information to create 3 different files (bait.dat, prey.dat, inter.dat)
that are needed for running SAINT. These can be downloaded in a folder on your computer.<br>
<li>Possible error messages:<br>
a) ProHits will attempt to retrieve the official Gene Symbol for each prey. If it cannot retrieve the symbol, it will list
the GI number instead. An error message will list the affected GI numbers. This does not affected the performance of SAINT.<br>
b) ProHits will also attempt retrieving the protein sequence length, either from its internal database or from the internet
(NCBI). Failures will be listed. Note that SAINT will not be able to run unless a protein length value is provided for each bait.
You can manually fix the problem in the prey .dat file. Note, however, that if you use Excel to fix the issue, you will need to
convert the PC or Mac excel (*.txt) file to a Unix-compatible file with the *.dat extension.
</ul>
</DIV> 
    </td>
  </tr>
  <tr>
    <td>
    <div>
    <ol>
    <li>
   Sequences matching the reversed databases should be removed from the prey list prior to running SAINT. 
   ProHits can automatically remove them if you specify their identifier (tag).<br>
     Indicate the prefix (starts by)   <input type="text" name="frm_start_with" value="<?php echo (isset($frm_start_with))?$frm_start_with:''?>"> 
     or suffix (ends by)<input type="text" name="frm_end_with" value="<?php echo (isset($frm_end_with))?$frm_end_with:''?>"> for matches to the reversed databases 
    <font size="-1" color="#008000">(separate by "|" if there are more than one, e.g "rm|99999")</font>.
    <li>
    <input type="checkbox" name="remove_pb_same_gene" value="Y" <?php echo ($remove_pb_same_gene=='Y')?'checked':''?>>
    Check the box to remove the prey if  its gene ID or protein id is the same as its bait.
    <li> 
   In its normal mode, SAINT uses prey protein sequence length for modeling. 
   ProHits will attempt to automatically retrieve this information.  
   If the sequence length for an unknown protein cannot be calculated, ProHits will instead report the average of all prey sequence length (a warning message will be provided). 
    <li>
    Note that the sequence length is an optional parameter for SAINT. Uncheck the following box if you do not want to use the sequence length as part of the SAINT model.    
    <br>
    <input type="checkbox" name="is_count_seq_len" value="Y" <?php echo ($is_count_seq_len=='Y')?'checked':''?>>include sequence length
    <li>
    <input type="checkbox" name="include_geneID" value="Y" <?php echo ($include_geneID=='Y')?'checked':''?>>include gene ID.
    <li>
    <input type="checkbox" name="merge_proteinID" value="Y" checked>collapse to gene ID.
    </ol>
    </div> 
    </td>
  </tr>
  
<?php }?>    
  <tr>
  <td>
    <DIV STYLE="border: #a0a7c5 solid 1px">
    <table border=0 width=100% cellspacing="1" cellpadding=1 align=center>
<?php if($theaction != "generate_SAINT_file" and $theaction != "run_saint" and $theaction != "re_run_saint"){?>
      <tr  bgcolor=#6699cc height="25">
      <td align=center nowrap><font color="#FFFFFF"><b><?php echo ($frm_is_collapse == 'no' && ($currentType == 'Bait' || $currentType == 'Exp'))?'Sample':$item_name?> ID</b></font></td>
      <td align=center><font color="#FFFFFF"><b><?php echo $item_name?> Name</b></font></td>
      <td align=center>
        <font color="#FFFFFF"><b>Saint Bait Name</b></font>
    <?php if($item_name != 'Bait'){?>
        <br><input id="bait_as_name" type="checkbox" name="bait_as_name" value='y' <?php echo ($bait_as_name?'checked':'')?> onClick="toggle_itme_type_name(this.form)">
        select "Bait Name" as "SAINT Bait Name"
    <?php }?>    
      </td>
      <td align=center nowrap><font color="#FFFFFF"><b>Is control</font></td>
      <td align=center nowrap><font color="#FFFFFF"><b>Remove</font></td>
      </tr>
<?php }elseif($Notice_arr){?>
      <tr  bgcolor="#f8f8fc" height="25">
      <td align=left colspan="4"><?php echo $Warning_line?></td>
      </tr>
      <tr  bgcolor=#6699cc height="25">
      <td align=center><font color="#FFFFFF"><b>Prey Name</b></font></td>
      <td align=center><font color="#FFFFFF"><b>Protein ID</b></font></td>
      <td align=center><font color="#FFFFFF"><b>Sequence len</b></font></td>
      <td align=center><font color="red"><b>Notes</b></font></td>
      </tr>
<?php }?>

<?php 

if($theaction != "generate_SAINT_file" and $theaction != "run_saint" and $theaction != "re_run_saint"){
    foreach($item_propty_arr as $val){
      if(in_array($val['ID'], $not_empty_item_arr)){
        $tr_bgcolor = "#f8f8fc";
        $tmp_name = 'CHECK_'.$val['ID'];
        $checked = '';
        if(isset($$tmp_name)){
          $checked = 'checked';
          $val['Bait_name'] = $val['ID'] . "_" . $val['Bait_name'];
          $val['Name'] = $val['ID']. "_" . $val['Name'];
        }
        $disabled_checked = '';
        if(in_array($val['ID'], $disabled_item_ids_arr)){
          $disabled_checked = 'checked';
          $tr_bgcolor = '#c0c0c0';
        }
  ?>  
    <tr id='t_<?php echo $val['ID']?>' bgcolor='<?php echo $tr_bgcolor?>'>
      <td align=left nowrap><div class=maintext>&nbsp;&nbsp;<?php echo $val['ID']?>&nbsp;&nbsp;</div></td>
      <td align=left nowrap><div class=maintext>&nbsp;&nbsp;<?php echo $val['Name']?>&nbsp;&nbsp;</div></td>
      <td align=><div class=maintext><input ID="TEXT_<?php echo $val['ID']?>" type="text" name="TEXT_<?php echo $val['ID']?>" size="30"  value='<?php echo ($bait_as_name)?$val['Bait_name']:$val['Name']?>' maxlength="300"></div></td>
      <td align=center><input type="checkbox" name="CHECK_<?php echo $val['ID']?>" value="<?php echo $val['ID']?>" <?php echo $checked?> onClick="checkControl(this, '<?php echo $val['ID']?>')"></td>
      <td align=center><input id=<?php echo $val['ID']?> type="checkbox" name="disabled_IDs" value="<?php echo $val['ID']?>" <?php echo $disabled_checked?> onClick="change_tr_bg_color('<?php echo $val['ID']?>','#f8f8fc')"></td>
    </tr>
  <?php 
      }
    }
  ?>
    <tr bgcolor="#f8f8fc" colspan="4">
      <td colspan='4'align=center><input type=button name='sort' value=' Generate SAINT Compatible Files ' onClick="checkempty(this.form)"></td>
    </tr>
<?php }else{?>
  <?php foreach($Notice_arr as $key => $val){?>  
    <tr bgcolor="#f8f8fc">
      <td align=left nowrap><div class=maintext>&nbsp;&nbsp;<?php echo $val['Prey name']?>&nbsp;&nbsp;</div></td>
      <td align=left nowrap><div class=maintext>&nbsp;&nbsp;<?php echo $val['ProteinID']?>&nbsp;&nbsp;</div></td>
      <td align=left nowrap><div class=maintext>&nbsp;&nbsp;<?php echo $val['S len']?>&nbsp;&nbsp;</div></td>
      <td align=left nowrap><div class=maintext>&nbsp;&nbsp;<?php echo $key." ".$val['Notice']?>&nbsp;&nbsp;</div></td>
    </tr>
  <?php }?>
<?php }?>    
    </table>
    </DIV>
  </td>
  </tr>
</table>

<?php 
if($theaction == "generate_map_file"){  
  create_hidden_fields($request_arr);
}  
?>
</FORM>
</body>
</html>
<?php 
flush();
function create_hidden_fields($request_arr){
  foreach($request_arr as $key => $val){
    //if($key == 'bait_as_name') continue;
    if($key == "theaction"){
      echo "<INPUT TYPE=\"hidden\" NAME=\"$key\" VALUE=\"\">\n";
    }elseif(strstr($key, 'TEXT_') || strstr($key, 'CHECK_')){
      continue;
    }elseif($key == "frm_start_with" || $key == "frm_end_with"){
      continue;
    }elseif($key == "remove_pb_same_gene" || $key == "is_count_seq_len" || $key == "include_geneID"){
      continue;
    }elseif($key == "bait_as_name" || $key == "disabled_IDs" || $key == "disabled_sample_ids"){
      continue; 
    }else{
      echo "<INPUT TYPE=\"hidden\" NAME=\"$key\" VALUE=\"$val\">\n";
    }
  }
}

function delete_directory($dirname){
   if(is_dir($dirname)){
     $dir_handle = opendir($dirname);
   }else{
    echo "$dirname";
   }     
   if(!$dir_handle){
      return false;
   }   
   while($file = readdir($dir_handle)){
      if($file != "." && $file != ".."){
         if(!is_dir($dirname."/".$file)){
            unlink($dirname."/".$file);
         }else{
            delete_directory($dirname.'/'.$file);
         }        
      }
   }
   closedir($dir_handle);
   rmdir($dirname);
   return true;
}
function get_EntrezGeneID_info($gene_id,$proteinDB){        
  $SQL = "SELECT `EntrezGeneID`,
                 `GeneName`,
                 `BioFilter` 
                 FROM `Protein_Class` 
                 WHERE `EntrezGeneID`='$gene_id'";
  $EntrezGene_resul_arr = $proteinDB->fetch($SQL);
  return $EntrezGene_resul_arr;
}

function get_sequence_len($Sequence,$ass_id){
  global $proteinDB;
  global $protein_id_sequence_arr;  
  global $ass_id_not_in_ass_table_arr; //******************************
  $seq_len = '';
  if($Sequence){ 
    $seq_len = strlen(trim($Sequence));
  }else{
    if(isset($protein_id_sequence_arr[$ass_id])){
    
      $seq_len = strlen(trim($protein_id_sequence_arr[$ass_id]));
    
    }else{
      array_push($ass_id_not_in_ass_table_arr, $ass_id); //************************************
      $pro_arr = get_protein_info($ass_id, '', $proteinDB);
      flush();
      if(isset($pro_arr['sequence']) && $pro_arr['sequence']){
        $sequence = $pro_arr['sequence'];
        $seq_len = strlen(trim($sequence));
      }
    }
  }
  return $seq_len;
}

function get_all_SAINT_protein_info($item_id_str_s){
  ob_end_flush();
  global $HITSDB,$proteinDB,$protein_property_arr,$protein_seq_len_arr,$no_gene_id_arr;
  global $ProteinAcc,$hitTable,$WHERE,$is_count_seq_len,$frm_start_with,$frm_end_with;
  global $protein_id_sequence_arr;
  
  $no_geneID_arr = array();  

  $SQL = "SELECT `GeneID`,
          $ProteinAcc AS ProteinAcc 
          FROM $hitTable 
          $WHERE BandID IN ($item_id_str_s)
          GROUP BY $ProteinAcc";
  $Gene_id_arr = $HITSDB->fetchAll($SQL);  
  $EntrezGene_arr = array();
  $ENSG_arr = array();

  foreach($Gene_id_arr as $value){
    if(!$value['ProteinAcc']) continue;
    $tmp_ProteinAcc = parse_protein_Acc($value['ProteinAcc']);
    if($frm_start_with && preg_match("/$frm_start_with/", $tmp_ProteinAcc)) continue;
    if($frm_end_with && preg_match("/$frm_end_with/", $tmp_ProteinAcc)) continue;
    $tmp_GeneID = trim($value['GeneID']);      
    if(!$tmp_GeneID){    
      $tmp_GeneID = get_protein_GeneID($tmp_ProteinAcc,'', $proteinDB);
      if(!$tmp_GeneID){
        if(!in_array($tmp_ProteinAcc, $no_gene_id_arr)){
          array_push($no_gene_id_arr,$tmp_ProteinAcc);
        }
        $protein_type = get_protein_ID_type($tmp_ProteinAcc);
        if($protein_type == "ENS"){
          $ENSG_arr[$tmp_ProteinAcc] = $tmp_GeneID;
        }else{
          $EntrezGene_arr[$tmp_ProteinAcc] = $tmp_GeneID;
        }
      }else{
        if(is_numeric($tmp_GeneID)){
          $EntrezGene_arr[$tmp_ProteinAcc] = $tmp_GeneID;
        }else{
          $ENSG_arr[$tmp_ProteinAcc] = $tmp_GeneID;
        }
      }  
    }else{
      if(is_numeric($tmp_GeneID)){
        $EntrezGene_arr[$tmp_ProteinAcc] = $tmp_GeneID;
      }else{
        $ENSG_arr[$tmp_ProteinAcc] = $tmp_GeneID;
      }
    }   
  }
  $AccessionType = '';
  flush();
  foreach($EntrezGene_arr as $ass_id => $gene_id){
    if(!array_key_exists($ass_id, $protein_property_arr)){
      if($gene_id){
        $EntrezGene_resul_arr = get_EntrezGeneID_info($gene_id,$proteinDB);
        if($EntrezGene_resul_arr){
          $protein_property_arr[$ass_id] = $EntrezGene_resul_arr['EntrezGeneID']."@@".$EntrezGene_resul_arr['GeneName']."@@".$EntrezGene_resul_arr['BioFilter'];
        }else{
 //if has gene id but no gene id info---------------------------------------------------------------
          if(in_array($ass_id, $no_gene_id_arr)){
            $protein_property_arr[$ass_id] = $ass_id."@@".$ass_id."@@";
          }else{
            $gene_id_tmp = get_protein_GeneID($ass_id,'', $proteinDB);
            if($gene_id_tmp){
              $EntrezGene_resul_arr = get_EntrezGeneID_info($gene_id,$proteinDB);
              if($EntrezGene_resul_arr){
                $protein_property_arr[$ass_id] = $EntrezGene_resul_arr['EntrezGeneID']."@@".$EntrezGene_resul_arr['GeneName']."@@".$EntrezGene_resul_arr['BioFilter'];
              }else{
                $protein_property_arr[$ass_id] = $ass_id."@@".$ass_id."@@";
                if(!in_array($ass_id, $no_gene_id_arr)){
                  array_push($no_gene_id_arr,$ass_id);
                }
              }
            }
          }
//----------------------------------------------------------------------------------------------------------
        }
      }else{
        $protein_property_arr[$ass_id] = $ass_id."@@".$ass_id."@@";
        if(!in_array($ass_id, $no_gene_id_arr)){
          array_push($no_gene_id_arr,$ass_id);
        }
      }
      if($is_count_seq_len){
        $AccessionType = '';
        $protein_inf = get_protin_info($ass_id, $AccessionType, $proteinDB);
        $Sequence = $protein_inf['Sequence'];
        $protein_seq_len_arr[$ass_id] = get_sequence_len($Sequence,$ass_id);
      }          
    }  
  }
  flush();
  
  foreach($ENSG_arr as $ass_id => $gene_id){
    if(!array_key_exists($ass_id, $protein_property_arr)){
      if($gene_id){
        $SQL = "SELECT `ENSG`,
                       `GeneName` 
                       FROM `Protein_ClassENS` 
                       WHERE `ENSG`='$gene_id'";
        $EntrezGene_resul_arr = $proteinDB->fetch($SQL);
        if($EntrezGene_resul_arr){
          $protein_property_arr[$ass_id] = $EntrezGene_resul_arr['ENSG']."@@".$EntrezGene_resul_arr['GeneName']."@@";
        }else{
          $protein_property_arr[$ass_id] = $ass_id."@@".$ass_id."@@";
        }  
      }else{
        $protein_property_arr[$ass_id] = $ass_id."@@".$ass_id."@@";
      }
      if($is_count_seq_len){
        $AccessionType = '';
        $protein_inf = get_protin_info($ass_id, $AccessionType, $proteinDB);
        $Sequence = $protein_inf['Sequence'];
        $protein_seq_len_arr[$ass_id] = get_sequence_len($Sequence,$ass_id);
      }  
    }
  }
  flush();
   $aver_len = 0;
  if($is_count_seq_len){
    $tmp_total = count($protein_seq_len_arr);
    if($tmp_total){
      $aver_len = round(array_sum($protein_seq_len_arr) / $tmp_total);
    }
  }else{
    $aver_len = 0;
  }
  return $aver_len;  
}

function get_all_SAINT_protein_info_for_geneLevel($item_id_str_s){
  global $HITSDB,$proteinDB,$protein_property_arr;
  global $hitTable,$WHERE;
  if(!$item_id_str_s) return 1000;
  $SQL = "SELECT `GeneID`
          FROM $hitTable 
          $WHERE BandID IN ($item_id_str_s)
          GROUP BY `GeneID`";
//echo "$SQL<br>";
  $Gene_id_arr = $HITSDB->fetchAll($SQL);
  if($Gene_id_arr){
    $Gene_id_arr_tmp = array();
    foreach($Gene_id_arr as $Gene_id){
      if(is_numeric($Gene_id['GeneID'])){
        $Gene_id_arr_tmp[] = $Gene_id['GeneID'];
      }  
    }
    $Gene_id_arr_tmp = array_unique($Gene_id_arr_tmp);
    $Gene_id_str = implode(',',$Gene_id_arr_tmp);
    if($Gene_id_str){  
      $SQL = "SELECT `EntrezGeneID`,`BioFilter` FROM `Protein_Class` WHERE `EntrezGeneID` IN ($Gene_id_str)";
      $resul_arr = $proteinDB->fetchAll($SQL);
      foreach($resul_arr as $resul_val){
        $protein_property_arr[$resul_val['EntrezGeneID']] = "@@@@".$resul_val['BioFilter'];
      }
    }
  }  
  return 1000;  
}

function format_pattern($pattern, $pos){
  $tmp_arr = explode("|", $pattern);
  $pattern = '';
  foreach($tmp_arr as $tmp_val){
    if($pattern) $pattern .= "|";
    if($tmp_val){
      if($pos == 'start'){
        $pattern .= '^'.$tmp_val;
      }else{
        $pattern .= $tmp_val.'$';
      }
    }  
  }
  return $pattern;
}
 
function display_saint_infor($saint_record){
  global $PROHITSDB, $sait_log_file, $bait_dat_file;
  if(!$saint_record) return;
  ?>
  <table align=center border=0 width=100% cellspacing=0 cellpadding=0>
    <tr>
      <td width=100><b>SAINT ID</b>:</td>
      <td><?php echo $saint_record['ID'];?></td>
    </tr>
    <tr>
      <td><b>SAINT Name</b>:</td>
      <td><?php echo $saint_record['Name'];?></td>
    </tr>
    <tr>
      <td><b>User</b>:</td>
      <td><?php echo get_userName($PROHITSDB, $saint_record['UserID']);?></td>
    </tr>
    <tr>
      <td valign=top><b>Status</b>:</td>
      <td>
      <?php  
      echo $saint_record['Status'];
      echo "<font color='#FF0000'>";
      if($saint_record['Status'] == 'Finished'){
        echo "<br>Since the task is finished, It will create <b>new</b> SAINT task using the same input files.";
      }else{
        echo "<br>Since the task has no results, It will re-run the task.";
      }
      echo "You can modify the SAINT parameters from the form below.";
      ?>
      </font>
      </td>
    </tr>
    <tr>
      <td><b>SAINT options</b>:</td>
      <td><?php  echo $saint_record['UserOptions'];?></td>
    </tr> 
    <tr>
      <td><b>Date</b>:</td>
      <td><?php  echo $saint_record['Date'];?></td>
    </tr>
    <tr>
      <td valign=top><b>Description</b>:</td>
      <td><?php  echo nl2br($saint_record['Description']); ?>
      </td>
    </tr>
     
    <tr bgcolor="#777777">
      <td colspan=2><font color="#FFFFFF">Bait.dat file <a ID=saint_infor_a href="javascript: toggle_group_description('saint_infor','saint_infor_a');" class=button>[+]</a></font></td>
    </tr>
    <tr>
      <td colspan=2>
        <DIV ID=saint_infor STYLE="display: none">
        <table width=100% bgcolor="#777777" cellspacing="1" cellpadding="0">
        <?php 
        $saint_log_lines = file($sait_log_file);
        $saint_option_start = 0;
        foreach($saint_log_lines as $log_line){
          $log_line = trim($log_line);
          $log_line = str_replace(",", ", ", $log_line);
          if($log_line == "<OTHER_OPTIONS>") {$saint_option_start = 1; continue;}
          if($log_line == "</OTHER_OPTIONS>"){$saint_option_start = 0; break;}
          if($saint_option_start){
            $line_arr = explode(":", $log_line);
            echo "<tr bgcolor='#cdcfad'>";
            echo "<td>".$line_arr[0]."</td><td colspan=2>".$line_arr[1]."</td>";
            echo "</tr>";
          }
        }
        ?>
        <tr bgcolor="white">
        	<td colspan=3>&nbsp;</td>
        </tr>
        <tr bgcolor="#777777">
        	<td><font color="#FFFFFF">Sample ID</font></td>
        	<td><font color="#FFFFFF">SAINT bait name</font></td>
        	<td><font color="#FFFFFF">Control</font></td>
        </tr>
        <?php 
        $bait_lines = file($bait_dat_file);
        foreach($bait_lines as $line){
          $line = trim($line);
          $rd_arr = explode("\t", $line);
          if(count($rd_arr)==3){
        ?>
        <tr bgcolor='#cdcfad'">
        	<td><?php echo $rd_arr[0];?></td>
        	<td><?php echo $rd_arr[1];?></td>
        	<td><?php echo $rd_arr[2];?></td>
        </tr>
        <?php 
          }
        }
        ?>
        </table>  
        </DIV>          
      </td>
    </tr>
  </table>
<?php 
}
function get_selected_item_id_str($item_type=''){ // $item_type cannot be Band!!!
  global $item_id_str_s,$HITSDB,$currentType,$frm_selected_list_str;
  if($currentType == 'Bait' || $currentType == 'Exp'){     
    if($currentType == 'Bait'){
      $itemID = 'BaitID';
    }elseif($currentType == 'Exp'){
      $itemID = 'ExpID';
    }
    $SQL = "SELECT ID,
                   $itemID AS itemID
                   FROM Band 
                   WHERE ID IN($item_id_str_s)";
                   
  }else{
    $tmp_arr = explode(":", $frm_selected_list_str);
    $selected_band_arr = explode(",", $tmp_arr[1]);
    if($item_type == 'Bait'){
      $SQL = "SELECT ID,
                     BaitID AS itemID
                     FROM Band 
                     WHERE BaitID IN($item_id_str_s)";
    }else{
      $SQL = "SELECT ID,
                     ExpID AS itemID
                     FROM Band 
                     WHERE ExpID IN($item_id_str_s)";
    }
  }                 
  $Band_sql_arr = $HITSDB->fetchAll($SQL);
  $itemID_bainID_arr = array();
  foreach($Band_sql_arr as $Band_sql_val){
    if($item_type){
      if(!in_array($Band_sql_val['ID'], $selected_band_arr)) continue;
    }
    $tmp_item_id = trim($Band_sql_val['itemID']);
    if(!array_key_exists($tmp_item_id, $itemID_bainID_arr)){        
      $itemID_bainID_arr[$tmp_item_id] = array();
    }      
    array_push($itemID_bainID_arr[$tmp_item_id], $Band_sql_val['ID']);
  }
  $selected_item_id_str = '';
    
  foreach($itemID_bainID_arr as $itemID_bainID_key => $itemID_bainID_val){
    if($selected_item_id_str) $selected_item_id_str .= '|';
    $bainID_arr = $itemID_bainID_val;
    $band_str = '';
    foreach($bainID_arr as $bainID_val){
      if($band_str) $band_str .= ";";
      $band_str .= $bainID_val;
    }
    if($band_str) $band_str = '(' . $band_str . ')';
    if($currentType == 'Band'){
      $selected_item_id_str .= $band_str . $itemID_bainID_key;
    }else{
      $selected_item_id_str .= $itemID_bainID_key . $band_str;
    }
  }  
  return $selected_item_id_str;
}

function get_sqlStr_expID($exp_ID){
  global $HITSDB,$hits_table,$WHERE;
  $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID`='".$exp_ID."'";
  $tmp_tmp_arr = $HITSDB->fetchAll($SQL);
  $tmp_band_id_str = '';
  foreach($tmp_tmp_arr as $tmp_tmp_val){
    if($tmp_band_id_str) $tmp_band_id_str .= ',';
    $tmp_band_id_str .= $tmp_tmp_val['ID'];
  }
  if($tmp_band_id_str){
    $SQL = "SELECT `ID` FROM $hits_table $WHERE `BandID` IN ($tmp_band_id_str) LIMIT 1";  
  }else{
    $SQL = 0;
  }
  return $SQL;
}
?>

