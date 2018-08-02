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

$type_bgcolor = '#5c65af';
$pro_name_bgcolor = '#d6d6eb';
$bgcolor = '#f1f1f8';
$this_sign = '[+]';
$modal = '';
$new_protocol_id = '';
$selected_type_div_id = '';
$selected_prot_div_id = '';
$this_protocol_name_arr = array();//====for both project=======
$self_pro_arr = array();
$other_pro_arr = array();
$protocol_type_arr = array();
$selected_str = '';
$prot_type = '';
$outsite_script = 0;
$message = '';

set_time_limit(2400);
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

$protocol_full_name_arr = array('GrowProtocol' => 'Biological Material',
                                'IpProtocol' => 'Affinity Purification',
                                'DigestProtocol' => 'Peptide Preparation',
                                'PeptideFrag' => 'LC-MS');
                                
$self_pro_arr = array('GrowProtocol' => array(),
                                'IpProtocol' => array(),
                                'DigestProtocol' => array(),
                                'PeptideFrag' => array());
$experiment_pro_name_arr = array('GrowProtocol','IpProtocol','DigestProtocol','PeptideFrag');

if($this_sign == "[%2B]") $this_sign = '[+]';

$HITS_DB_obj_arr = array();              
foreach($HITS_DB as $DB_name_val){
  $HITS_DB_obj_arr[$DB_name_val] = new mysqlDB($DB_name_val);
}

$type_objID_arr = array();
$objID_type_arr = array();
$has_prot_projectID_arr = array();

$SQL = "SELECT `Type`,
          `ProjectID`
          FROM `Protocol`";
foreach($HITS_DB_obj_arr as $DB_obj){
  $results = mysqli_query($DB_obj->link, $SQL);
  while($row = mysqli_fetch_assoc($results)){
    if(!array_key_exists($row['Type'], $type_objID_arr)){
      $type_objID_arr[$row['Type']] = array();
      array_push($type_objID_arr[$row['Type']], $row['ProjectID']);
    }else{
      if(!in_array($row['ProjectID'],$type_objID_arr[$row['Type']])){
        array_push($type_objID_arr[$row['Type']], $row['ProjectID']);
      }
    }
    if(!in_array($row['ProjectID'],$has_prot_projectID_arr)){
      array_push($has_prot_projectID_arr, $row['ProjectID']);
    }
    if(!array_key_exists($row['ProjectID'], $objID_type_arr)){
      $objID_type_arr[$row['ProjectID']] = array();
      array_push($objID_type_arr[$row['ProjectID']], $row['Type']);
    }else{
      if(!in_array($row['Type'],$objID_type_arr[$row['ProjectID']])){
        array_push($objID_type_arr[$row['ProjectID']], $row['Type']);
      }
    }  
  }
}

$projectID_DBname_arr = get_projectID_DBname_pair($PROHITSDB);
$user_id_name_arr = get_users_ID_Name($PROHITSDB);
$project_id_name_arr = get_project_id_name_arr();

$SQL = "SELECT `ProjectID` FROM `ProPermission` WHERE `UserID`='$AccessUserID'";

$tmp_ProPermission_arr = $PROHITSDB->fetchAll($SQL);
$user_accessed_projects_arr = array();

foreach($tmp_ProPermission_arr as $tmp_ProPermission_val){
  array_push($user_accessed_projects_arr, $tmp_ProPermission_val['ProjectID']);
}

if($modal == "this_project"){
   
  if($theaction == 'update_single_detail'){
    $SQL = "UPDATE `Protocol` SET
                `Name`='".$frm_Name."',
                `Detail`='".mysqli_real_escape_string($HITSDB->link, $frm_Detail)."',
                `UserID`='$AccessUserID'
                WHERE `ID`=$frm_ID";
    $HITSDB->update($SQL);
    $ProtocolID = $frm_ID;
    $theaction = 'show_single_detail';
  }elseif($theaction == 'insert_single_detail'){
    $SQL = "SELECT `ID` 
            FROM `Protocol` 
            WHERE `Type`='$frm_Type' 
            AND`ProjectID`='$AccessProjectID' 
            AND (Name IS NULL OR Name='')"; 
    $tmp_arr = $HITSDB->fetchAll($SQL);
     
    if(count($tmp_arr) == 1){
      $SQL = "UPDATE `Protocol` SET
                `Name`='".$frm_Name."',
                `Detail`='".mysqli_real_escape_string($HITSDB->link, str_replace("#####", "&", $frm_Detail))."',
                `Date`='".@date("Y-m-d")."',
                `UserID`='$AccessUserID'
                WHERE ID='".$tmp_arr[0]['ID']."'";
      $HITSDB->update($SQL);
      $new_protocol_id = $tmp_arr[0]['ID'];          
    }else{
      $SQL = "INSERT INTO `Protocol` SET
                  `Name`='".$frm_Name."',
                  `Type`='$frm_Type',
                  `ProjectID`='$AccessProjectID',
                  `Detail`='".mysqli_real_escape_string($HITSDB->link, $frm_Detail)."',
                  `Date`='".@date("Y-m-d")."',
                  `UserID`='$AccessUserID'";
      
      $new_protocol_id = $HITSDB->insert($SQL);
    }  
     
    if(is_numeric($new_protocol_id)){
      $this_sign = '[-]';
      $theaction = 'show_type_detail';
    }
  }elseif($theaction == 'create_new_s_type'){
    $frm_s_protocol_type = 'SAM_'.$frm_s_protocol_type;
    $SQL = "INSERT INTO `Protocol` SET
                `Type`='$frm_s_protocol_type',
                `ProjectID`='$AccessProjectID'";
    $new_protocol_id = $HITSDB->insert($SQL);
    $theaction = '';
  }elseif($theaction == 'delete_single_detail'){
    $Protocol_has_used = 0;
    if(in_array($base_id, $experiment_pro_name_arr)){  
      $SQL = "SELECT $base_id 
              FROM Experiment 
              WHERE $base_id IS NOT NULL";
      $tmp_pro_arr = $HITSDB->fetchAll($SQL);
      foreach($tmp_pro_arr as $tmp_pro_val){
        $tmp_arr = explode(',',$tmp_pro_val);
        if($tmp_arr[0] == $ProtocolID){
          $Protocol_has_used = 1;
          break;
        }
      }
    }else{
      $SQL = "SELECT `ID` 
              FROM `BandGroup` 
              WHERE `Note`='$base_id'
              AND`NoteTypeID`='$ProtocolID'";
      $tmp_pro_arr = $HITSDB->fetch($SQL);
      if($tmp_pro_arr) $Protocol_has_used = 1;
    }
    
    if($Protocol_has_used){
      $message = $ProtocolID;
    }else{
      $SQL = "SELECT `ID` 
              FROM `Protocol` 
              WHERE `Type`='$base_id'
              AND `ProjectID`='$AccessProjectID'";
      $tmp_pro_arr2 = $HITSDB->fetchAll($SQL);
      if(count($tmp_pro_arr2) == 1){
        $SQL = "UPDATE `Protocol` SET
                `Name`=NULL,
                `Detail`=NULL,
                `Date`=NULL,
                `UserID`=NULL
                WHERE ID='$ProtocolID'";
        $HITSDB->update($SQL);
      }else{      
        $SQL = "DELETE FROM Protocol  
                WHERE ID = '$ProtocolID'";
        $db_ret = $HITSDB->execute($SQL);
      }
    }
    $theaction = 'show_type_detail';
  }
}

$SQL_1 = "SELECT `ID`,
              `Name`,
              `Type`
              FROM `Protocol` 
              WHERE `ProjectID`='$AccessProjectID' 
              ORDER BY `Type`,`ID` DESC";
$results_1 = mysqli_query($HITSDB->link, $SQL_1);
while($row = mysqli_fetch_assoc($results_1)){
  if(!array_key_exists($row['Type'], $protocol_full_name_arr)){
    $protocol_full_name_arr[$row['Type']] = $row['Type'];
  }
}                                
foreach($protocol_full_name_arr as $key => $value){
  array_push($protocol_type_arr, $key);
}

if($theaction == "export_protocols"){
  if($selected_str && $selected_type_str){
    $SQL = "SELECT `ID`,
                  `Name`,
                  `Type`,
                  `ProjectID`,
                  `Detail`,
                  `Date`,
                  `UserID` 
                  FROM `Protocol` 
                  WHERE `Type` IN ($selected_type_str)
                  AND `ProjectID` IN ($selected_str)
                  ORDER BY `Type`,`ProjectID`";
    $all_pro_arr = array();        
    get_protocols_arr_for_both_db($SQL,$all_pro_arr);
    
    $outDir = "../TMP/protocol_export/";
    if(!_is_dir($outDir)) _mkdir_path($outDir);
    $filename_out = $outDir.$AccessUserID."_protocol.csv";

    $handle_write = fopen($filename_out, "w");
    $filedNameStr = "\"ID\",\"Name\",\"Type\",\"Project Name\",\"Detail\",\"Creation Date\",\"Creater\"";
    fwrite($handle_write, $filedNameStr."\r\n");
    foreach($all_pro_arr as $key => $value){
      $tmp_arr = $value;
      foreach($tmp_arr as $tmp_val){
        $ID = $tmp_val['ID'];
        $Name = $tmp_val['Name'];
        $Type = format_pro_type_name($protocol_full_name_arr[$tmp_val['Type']]);
        if(array_key_exists($tmp_val['ProjectID'], $project_id_name_arr)){
          $ProjectName = $project_id_name_arr[$tmp_val['ProjectID']];
        }else{
          $ProjectName = '';
        }
        $Detail = $tmp_val['Detail'];
        $CreateDate = $tmp_val['Date'];
        if(array_key_exists($tmp_val['UserID'], $user_id_name_arr)){
          $Creater = $user_id_name_arr[$tmp_val['UserID']];
        }else{
          $Creater = '';
        }
        $line = "\"".$ID."\",\"".$Name."\",\"".$Type."\",\"".$ProjectName."\",\"".$Detail."\",\"".$CreateDate."\",\"".$Creater."\"";
        $line = str_replace("\r", "", $line);
        $line = str_replace("\n", "", $line);
        fwrite($handle_write, $line."\r\n");
      }
    }
    fclose($handle_write);

    if(_is_file($filename_out)){
      header("Cache-Control: public, must-revalidate");
      header("Content-Type: application/octet-stream");  //download-to-disk dialog
      header("Content-Disposition: attachment; filename=".basename($filename_out).";" );
      header("Content-Transfer-Encoding: binary");
      header("Content-Length: "._filesize($filename_out));
      readfile("$filename_out");
    }else{
      echo "Canont export file";
    }
    exit();
  }
}

if($theaction == 'show_single_detail'){
  $DB_obj = $HITS_DB_obj_arr[$DB_name];
  $tmp_arr = explode('_',$base_id);  //******************************
  $ProjectID = $tmp_arr[0];
  $ProtocolID = $tmp_arr[1];
  $SQL = "SELECT `ID`,
              `Name`,
              `Type`,
              `ProjectID`,
              `Detail`,
              `Date`,
              `UserID` 
              FROM `Protocol` 
              WHERE `ID`='$ProtocolID'";
  $results = mysqli_query($DB_obj->link, $SQL);
  $row = mysqli_fetch_assoc($results);
  if($row){
    echo "@@**@@".$base_id."@@**@@";
    print_single_detail($row,$theaction);
  }
  exit;
}

if($modal == "this_project"){
  $used_prot_id_arr = array();
  $SQL = "SELECT `GrowProtocol`,
            `IpProtocol`,
            `DigestProtocol`,
            `PeptideFrag` 
            FROM `Experiment`
            WHERE `ProjectID`='$AccessProjectID'";
  $used_prot_atr = $HITSDB->fetchAll($SQL);
  foreach($used_prot_atr as $used_prot_arr){
    foreach($used_prot_arr as $used_prot_val){
      if($used_prot_val){
        $tmpArr = explode(",",$used_prot_val);
        if(!in_array($tmpArr[0], $used_prot_id_arr)){
          array_push($used_prot_id_arr, $tmpArr[0]);
        }
      }
    }
  }
//------------------------------------------------
  $SQL = "SELECT B.NoteTypeID 
         FROM BandGroup B
         LEFT JOIN Protocol P 
         ON (B.NoteTypeID=P.ID)
         WHERE B.Note LIKE 'SAM%'
         AND ProjectID='$AccessProjectID'
         GROUP BY B.NoteTypeID";
  $used_S_prot_atr = $HITSDB->fetchAll($SQL);
  foreach($used_S_prot_atr as $used_S_prot_val){
    if(!in_array($used_S_prot_val['NoteTypeID'], $used_prot_id_arr)){
      array_push($used_prot_id_arr, $used_S_prot_val['NoteTypeID']);
    }
  } 
//------------------------------------------------  
}elseif($modal == "other_projects"){
  if($selected_str && $prot_type){
    $SQL_2 = "SELECT `ID`,
                  `Name`,
                  `Type`,
                  `ProjectID`,
                  `Detail`,
                  `Date`,
                  `UserID` 
                  FROM `Protocol` 
                  WHERE `Type`='$prot_type'
                  AND `ProjectID` IN ($selected_str) 
                  ORDER BY `Type`,`ProjectID`";
    get_protocols_arr_for_both_db($SQL_2,$other_pro_arr);
  }
}
$SQL_1 = "SELECT `ID`,
              `Name`,
              `Type`,
              `ProjectID`,
              `Detail`,
              `Date`,
              `UserID` 
              FROM `Protocol` 
              WHERE `ProjectID`='$AccessProjectID' 
              ORDER BY `Type`,`ID` DESC";
$results_1 = mysqli_query($HITSDB->link, $SQL_1);
while($row = mysqli_fetch_assoc($results_1)){
  if(!in_array($row['Name'], $this_protocol_name_arr)){
    array_push($this_protocol_name_arr, $row['Name']);
  }
  $row['DB_name'] = $HITSDB->selected_db_name;
  if(!array_key_exists($row['Type'], $self_pro_arr)){
    $self_pro_arr[$row['Type']] = array();
  }
  array_push($self_pro_arr[$row['Type']], $row);
}

$protocol_type_num_array = array();

foreach($protocol_type_arr as $key => $val){
  if(!array_key_exists($val, $protocol_type_num_array)){
    $protocol_type_num_array[$val] = 'protocol_'.++$key;
  }  
}

if($theaction == 'show_this'){
  echo "@@**@@".$base_id."@@**@@";
  if($modal == 'this_project'){
    print_this_detail($self_pro_arr,$this_sign,$modal);
  }elseif($modal == 'other_projects'){
    if(array_key_exists($base_id, $other_pro_arr)){
      $type_arr = $other_pro_arr[$base_id];
      print_type_detail($type_arr,$this_sign,$modal);
    }  
  }  
  exit;
}elseif($theaction == 'show_type_detail'){
  echo "@@**@@".$base_id."@@**@@";
  if($modal == 'this_project'){
    $type_arr = $self_pro_arr[$base_id];
    print_type_detail($type_arr,$this_sign,$modal,1,$message);
  }elseif($modal == 'other_projects'){
    //$type_arr = $other_pro_arr[$base_id];
    //print_type_detail($type_arr,$this_sign,$modal,1);
  }  
  exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Prohits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<STYLE type="text/css">
TD { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
A {TEXT-DECORATION: none;}
</STYLE>
<!--script language="Javascript" src="site_no_right_click.inc.js"></script-->
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language="JavaScript" type="text/javascript">
<!--
var protocol_name_arr = new Array();
<?php foreach($this_protocol_name_arr as $protocol_name){?>
    protocol_name_arr.push("<?php echo $protocol_name?>"); 
<?php }?>
var protocol_type_arr = new Array();
var protocol_type_number_arr = new Array();
<?php foreach($protocol_type_num_array as $protocol_type_key => $protocol_type_val){?>
    protocol_type_arr.push("<?php echo $protocol_type_key?>"); 
    protocol_type_number_arr.push("<?php echo $protocol_type_val?>"); 
<?php }
if(isset($frm_s_protocol_type) && $frm_s_protocol_type){
?>
  protocol_type_arr.push("<?php echo $frm_s_protocol_type?>");
<?php 
}
?>

var peptedeW = '';
Array.prototype.in_array = function(p_val) {
	for(var i = 0, l = this.length; i < l; i++) {
		if(this[i] == p_val) {
			return true;
		}
	}
	return false;
}
function remove_p_name(P_name){        
  for(var j=0; j<protocol_name_arr.length; j++){
    if(protocol_name_arr[j] == P_name){
      protocol_name_arr[j] = '';
      break;
    }  
  }
}
function add_new_s_protocol_type(theForm){
  theForm.frm_s_protocol_type.value = trimString(theForm.frm_s_protocol_type.value);
  var p_name = theForm.frm_s_protocol_type.value;
  if(p_name.length > 16){
    alert("The length of the type could not greater than 16.");
    return;
  }
  if(!onlyAlphaNumerics(p_name, 3)){
    alert("Only characters \"%,+,-,_,A-Z,a-z,0-9\" and spaces are allowed.");
    return;
  }
  theForm.frm_s_protocol_type.value = p_name;
  var tmp_p_name = 'SAM_' + p_name;
  if(protocol_type_arr.in_array(tmp_p_name)){
    alert("The name " + p_name + " has been used.");
    return;
  }
  var type_obj = document.getElementById('protocol_type_add_new');
  type_obj.style.display = "none";
  theForm.submit();
}

function open_s_type_block(obj_id){
  var type_obj = document.getElementById(obj_id);
  type_obj.style.display = "block";
}

function add_new(theForm){
  theForm.frm_Name.value = trimString(theForm.frm_Name.value);
  var base_id = theForm.base_id.value;
  var p_name = theForm.frm_Name.value;
  if(!onlyAlphaNumerics(p_name, 3)){
    alert("Only characters \"%,+,\-,_,A-Z,a-z,0-9\" and spaces are allowed.");
    return;
  }
  if(theForm.theaction.value == "update_single_detail"){
    var lable_id = base_id + "_b";
    var modified_obj = document.getElementById(lable_id);
    var lable = trimString(modified_obj.innerHTML.replace(/&nbsp;/ig, ""));
    if(lable != p_name){
      if(protocol_name_arr.in_array(p_name)){
        alert("The name " + p_name + " has been used.");
        return;
      }else{
        modified_obj.innerHTML = '&nbsp;' + p_name;
        remove_p_name(lable);
        protocol_name_arr.push(p_name);
      } 
    }
  }else{
    if(protocol_name_arr.in_array(p_name)){
      alert("The name " + p_name + " has been used.");
      return;
    }
    var type_id = theForm.frm_Type.value;
    var type_id_a = type_id + '_a';
    var type_obj = document.getElementById(type_id);
    var type_obj_a = document.getElementById(type_id_a);
    type_obj.style.display = "block";
    type_obj_a.innerHTML = '[-]';
    protocol_name_arr.push(p_name);  
  }
  var theaction = theForm.theaction.value;
  var modal = theForm.modal.value;
  var frm_ID= theForm.frm_ID.value;
  var frm_Name = theForm.frm_Name.value;
  var frm_Type = theForm.frm_Type.value;
  var frm_Detail = theForm.frm_Detail.value;
  frm_Detail = frm_Detail.replace(/&/, "#####");
  var DB_name = theForm.DB_name.value;
  frm_Name = frm_Name.replace(/\+/g, "%2B");
  queryString = "base_id=" + base_id + "&theaction="+ theaction + "&modal=" + modal + "&frm_ID=" + frm_ID + "&frm_Name=" + frm_Name + "&frm_Type=" + frm_Type + "&frm_Detail=" + frm_Detail + "&DB_name=" + DB_name;
  queryString = queryString.replace(/\+/g, "%2B");
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
  theForm.reset();
  
}

function delete_single_detail(base_id,ProtocolID,P_name){
  queryString = "modal=this_project&base_id=" + base_id + "&ProtocolID=" + ProtocolID + "&theaction=delete_single_detail";
  queryString = queryString.replace(/\+/g, "%2B");
  P_name = trimString(P_name);
  remove_p_name(P_name);
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function modify_detail(base_id,DB_name){
  var selected_obj = document.getElementById(base_id);
  var selected_a_id = base_id + '_a';
  var selected_a_obj = document.getElementById(selected_a_id);
  queryString = "DB_name=" + DB_name + "&base_id=" + base_id + "&theaction=modify_single_detail";
  queryString = queryString.replace(/\+/g, "%2B");
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
  selected_obj.style.display = "block";
  selected_a_obj.innerHTML = '[-]';
}

function toggle_detail(base_id,DB_name){
  var selected_obj = document.getElementById(base_id);
  var selected_a_id = base_id + '_a';
  var selected_a_obj = document.getElementById(selected_a_id);
  if(selected_obj.style.display == "none"){
    var inner_str = trimString(selected_obj.innerHTML);
    queryString = "DB_name=" + DB_name + "&base_id=" + base_id + "&theaction=show_single_detail";
    queryString = queryString.replace(/\+/g, "%2B");    
    ajaxPost("<?php echo $PHP_SELF;?>", queryString);
    selected_obj.style.display = "block";
    selected_a_obj.innerHTML = '[-]';
    selected_a_obj.title = 'close details';
  }else{
    selected_obj.style.display = "none";
    selected_a_obj.innerHTML = '[+]';
  }
  close_pop_win();
}
function processAjaxReturn(rp){
  var ret_html_arr = rp.split("@@**@@");
  if(ret_html_arr.length == 3){
    var obj_id = trimString(ret_html_arr[1]);
//alert(obj_id);
    document.getElementById(obj_id).innerHTML = ret_html_arr[2];
    return;
  }
}
function toggle_all_detail_this(lable_id,base_id,theaction,modal){
  var theForm = document.protocols_edit_frm;
  var lable_obj = document.getElementById(lable_id);
  var base_obj = document.getElementById(base_id);
  var sign = trimString(lable_obj.innerHTML);
  if(sign == '[+]'){
    var this_sign = '[-]';
    lable_obj.innerHTML = '[-]';
    close_other_divs(base_id);  
  }else{
    var this_sign = '[+]';
    this_sign = this_sign.replace('+', '%2B');
    lable_obj.innerHTML = '[+]';
  }
  queryString = "base_id=" + base_id + "&theaction="+ theaction + "&this_sign=" + this_sign + "&modal=" + modal;
  queryString = queryString.replace(/\+/g, "%2B");  
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
  if(theaction == "show_type_detail"){
    if(base_obj.style.display == "none"){
      base_obj.style.display = "block";
    }else{
      base_obj.style.display = "none";
    }
  }
  close_pop_win();
}

function close_other_divs(base_id){
  for(var i=0; i<protocol_type_arr.length; i++){
    if(protocol_type_arr[i] != base_id){
      var other_base_id = protocol_type_arr[i];
      var other_lable_id = other_base_id + '_a';
      var other_add_new_id = other_base_id + '_add_new';
      var other_frm_id = protocol_type_number_arr[i] + '_frm';
      var other_lable_obj = document.getElementById(other_lable_id);
      var other_base_obj = document.getElementById(other_base_id);
      var other_add_new_obj = document.getElementById(other_add_new_id);
      var other_frm_obj = document.getElementById(other_frm_id);
      var other_sign = '[+]';
      other_sign = other_sign.replace('+', '%2B');
      other_lable_obj.innerHTML = '[+]';
      other_base_obj.style.display = "none";
      other_add_new_obj.style.display = "none";
      other_frm_obj.reset(); 
    }
  }
}    
function toggle_add_new(base_id){
  var add_new_id = base_id + "_add_new";
  var new_obj = document.getElementById(add_new_id);
  new_obj.style.display = "block";
  close_other_divs(base_id);
  close_pop_win();
}
function close_add_new(add_new_id){
  var new_obj = document.getElementById(add_new_id);
  new_obj.style.display = "none";
  if(!peptedeW.closed && peptedeW.location) {
    peptedeW.close();
  }
}
function close_modify(modify_id){
  var modified_obj = document.getElementById(modify_id);
  modify_id_a = modify_id + "_a";
  var modified_a_obj = document.getElementById(modify_id_a);
  modified_obj.style.display = "none";
  modified_a_obj.innerHTML = '[+]';
}

function clean_up_child_nodes(itemID){
  var parentItem = document.getElementById(itemID);
  if(parentItem.hasChildNodes()){
    while(parentItem.childNodes.length > 0) {
      parentItem.removeChild(parentItem.childNodes[0]);
    }
  }  
}

function pop_other_project_win(prot_type,add_new_id,form_id){
  var new_obj = document.getElementById(add_new_id);
  var theForm = document.getElementById(form_id);
  theForm.frm_Name.value = '';
  theForm.frm_Detail.value = '';
  new_obj.style.display = "block"; 
  if(!peptedeW.closed && peptedeW.location) {
    peptedeW.close();
  }
  close_other_divs(prot_type);
  prot_type = prot_type.replace(/\+/g, "%2B");
  var file = "<?php echo $PHP_SELF;?>?modal=other_projects&prot_type=" + prot_type;
  peptedeW = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=720,height=600');
  peptedeW.moveTo(1500,0);
  peptedeW.focus();
}

function pop_export_win(){
  if(!peptedeW.closed && peptedeW.location) {
    peptedeW.close();
  }
  var file = "<?php echo $PHP_SELF;?>?modal=&theaction=pop_export_win";
  peptedeW = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=720,height=400');
  peptedeW.moveTo(2500,0);
  peptedeW.focus();
}

function close_pop_win(){
  if(!peptedeW.closed && peptedeW.location) {
    peptedeW.close();
  }
}

function show_selected(theForm){
  var selected_list = theForm.frm_selected_list;
	var selected_str = '';
	for (var i=0; i<selected_list.length; i++) {
	  if(selected_str.length > 0){
			selected_str +=",";
		}
		selected_str += selected_list.options[i].value;
	}
	if(selected_str.length > 0){
		theForm.selected_str.value = selected_str;
	}else{
    theForm.selected_str.value = '';
  }
  var theaction = 'show_this';
  var modal = "other_projects";
  var prot_type = theForm.prot_type.value;
  var base_id = theForm.prot_type.value;
  var this_sign = '[-]'
  queryString = "selected_str=" + selected_str + "&theaction="+ theaction + "&this_sign=" + this_sign + "&modal=" + modal + "&base_id=" + base_id + "&prot_type=" + prot_type;
queryString = queryString.replace(/\+/g, "%2B");  
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function pass_protocol_data(obj_frm,name_id,detail_id){
  var name_obj = document.getElementById(name_id);
  var detail_obj = document.getElementById(detail_id);
<?php foreach($self_pro_arr as $key => $val){
  $form_name = $protocol_type_num_array[$key];
?>
  if(obj_frm == '<?php echo $form_name?>'){
    opener.document.<?php echo $form_name?>_frm.frm_Name.value = name_obj.innerHTML;
  	//opener.document.<?php echo $form_name?>_frm.frm_Detail.value = detail_obj.innerHTML;
    opener.document.<?php echo $form_name?>_frm.frm_Detail.value = $("<div/>").html(detail_obj.innerHTML).text(); 
  }
<?php }?>
}

function export_protocols(theForm){
  var selected_list = theForm.frm_selected_list;
	var selected_str = '';
	for (var i=0; i<selected_list.length; i++) {
	  if(selected_str.length > 0){
			selected_str +=",";
		}
		selected_str += selected_list.options[i].value;
	}
	if(selected_str.length == 0){
    alert('Please select project(s).')
    return false;
  }  
  theForm.selected_str.value = selected_str;
  var selected_type = theForm.frm_selected_type;
	var selected_type_str = '';
	for (var i=0; i<selected_type.length; i++) {
	  if(selected_type_str.length > 0){
			selected_type_str +="','";
		}
		selected_type_str += selected_type.options[i].value;
	}
  
	if(selected_type_str.length == 0){
    alert('Please select protocol type(s).')
    return false;
  }else{
    selected_type_str = "'" + selected_type_str + "'";
  }  
  theForm.selected_type_str.value = selected_type_str;
  theForm.theaction.value = 'export_protocols';  
  theForm.submit();
}

function create_protocols_type_selection(theForm){
  var selected_list = theForm.frm_selected_list;
	var selected_str = '';
	for (var i=0; i<selected_list.length; i++) {
	  if(selected_str.length > 0){
			selected_str +=",";
		}
		selected_str += selected_list.options[i].value;
	}
  theForm.theaction.value = 'pop_export_win';
	theForm.selected_str.value = selected_str;
  theForm.submit();
}

//-->
</script>
</head>
<!--body onload="initial_interface();"-->
<body>
<?php 
if($modal == "this_project"){
  $general_title = "<span class=pop_header_text>Protocols</span> <font color='navy' face='helvetica,arial,futura' size='2'> (Project $AccessProjectID: $AccessProjectName)</font>";
  $tmp_align = 'left';
}elseif($modal == "other_projects"){
  $general_title = "<font color='navy' face='helvetica,arial,futura' size='4'>Protocols from other projects</font> <font color='navy' face='helvetica,arial,futura' size='2'>   (".format_pro_type_name($protocol_full_name_arr[$prot_type]).")</font>";
  $tmp_align = 'left';
}else{
  $general_title = "<font color='navy' face='helvetica,arial,futura' size='4'>Export protocols</font>";
  $tmp_align = 'left';
}
?>
  <div style="width:700px;border: #a0a7c5 solid 1px;padding:0px 0px 10px 0px;overflow:auto;">
  <center>     
<?php if($modal == "this_project"){?>
    <div style="width:90%;border: blue solid 0px;"> 
      <div style="width:100%;border: green solid 0px;height:35px;top:10px;position:relative;">
        <div style="float:left;width:70%;border: red solid 0px;text-align:<?php echo $tmp_align?>;">
          <?php echo $general_title?>
        </div> 
        <div style="float:right;width:26%;border: red solid 0px;text-align:right;">
          <a href="javascript: popwin('../doc/Analyst_help.php#faq38', 800, 600, 'help');">
            <img src='./images/icon_HELP.gif' border=0 >
          </a>
          <a href="javascript: pop_export_win()"  title='export protocols'>[Export protocols]</a>
        </div>      
      </div>
      <div style="width:100%;border: red solid 0px">
        <hr size=1>
      </div>
      <div id='this_project' style="width:100%;border: #a0a7c5 solid 0px;">
        <?php print_this_detail($self_pro_arr,$this_sign,'this_project')?>
      </div>
    </div>    
<?php }elseif($modal == "other_projects"){?>
    <div style="width:95%;border: blue solid 0px;">
      <div style="width:100%;border: green solid 0px;height:35px;top:10px;position:relative;">
        &nbsp;<?php echo $general_title?>
      </div>
      <div style="width:100%;border: red solid 0px">
        <hr size='1' color="#a0a7c5">
      </div> 
      <div STYLE="width:100%;display: block;border: #a0a7c5 solid 1px">
      <table border=0 width=100% cellspacing="10" cellpadding=0>
        <FORM NAME='selection_frm' ACTION='<?php echo $_SERVER['PHP_SELF'];?>' METHOD='POST'>
        <input type='hidden' name='theaction' value=''>
        <input type='hidden' name='selected_str' value=''>
        <input type='hidden' name='selected_type_str' value=''>
        <input type='hidden' name='modal' value=''>
        <input type='hidden' name='prot_type' value='<?php echo $prot_type?>'>
        <?php print_selections($prot_type)?>
        <tr>
        <td align=center></td><td align=center></td>
        <td align=center>
        <input type="button" value="  Submit  " onClick="javascript: show_selected(this.form);">
        </td>
        </tr>       
        </FORM>
      </table>            
      </div>
      <div id='<?php echo $prot_type?>' STYLE="display: block;border: red solid 0px">
      </div>
    </div>        
<?php }elseif($theaction == "pop_export_win"){?> 
    <div style="width:95%;border: blue solid 0px;">
      <div style="width:100%;border: green solid 0px;height:35px;top:10px;position:relative;">
        &nbsp;<?php echo $general_title?>
      </div>
      <div style="width:100%;border: red solid 0px">
        <hr size='1' color="#a0a7c5">
      </div> 
      <div STYLE="width:100%;display: block;border: #a0a7c5 solid 1px">
        <table border=0 width=100% cellspacing="10" cellpadding=0>
          <FORM NAME='selection_frm' ACTION='<?php echo $_SERVER['PHP_SELF'];?>' METHOD='POST'>
          <input type='hidden' name='theaction' value='<?php echo $theaction?>'>
          <input type='hidden' name='selected_str' value=''>
          <input type='hidden' name='selected_type_str' value=''>
          <input type='hidden' name='modal' value=''>
          <input type='hidden' name='prot_type' value='<?php echo $prot_type?>'>
          <?php print_selections()?>
          <tr>
          <td align=center></td><td align=center></td>
          <td align=center>
          <input type="button" value="  Export  " onClick="javascript: export_protocols(this.form);">  
          </td>
          </tr>
          </FORM>
        </table>            
      </div>
    </div>
<?php }?>
  </center>    
  </div>  
</body>
</html>
<?php 
function print_selections($prot_type=''){
  global $user_accessed_projects_arr,$protocol_type_arr,$theaction,$project_id_name_arr,$AccessProjectID,$modal;
  global $type_objID_arr,$has_prot_projectID_arr,$selected_str,$objID_type_arr;
  global $protocol_full_name_arr;
  $selected_arr = array();
  $type_list_arr = array();
  if($prot_type and $type_objID_arr){
    $exist_protocols_array = $type_objID_arr[$prot_type];
  }else{
    if(trim($selected_str)){
      $selected_arr = explode(',',$selected_str);
      $type_list_arr = $objID_type_arr[$selected_arr[0]];
      if(count($selected_arr) > 1){
        for($i=1; $i<count($selected_arr); $i++){
          $type_list_arr = array_merge($type_list_arr, $objID_type_arr[$selected_arr[$i]]);
        }
        $type_list_arr = array_unique($type_list_arr);
      }
    }
  }
   
?>             
    <tr>
      <td width="46%" align=center valign=top>
        <div class=maintext_bold>Projects</div>
        <div class=maintext>
        <select ID="frm_sourceList" name="frm_sourceList" size=5 multiple>
          <?php foreach($user_accessed_projects_arr as $accessed_projects_id){
              if($modal == "other_projects" && $accessed_projects_id == $AccessProjectID) continue;
              if($prot_type){
                
                if(!in_array($accessed_projects_id, $exist_protocols_array)) continue;
              }else{
                if(!in_array($accessed_projects_id, $has_prot_projectID_arr)) continue;
                if(in_array($accessed_projects_id, $selected_arr)) continue;
              }  
          ?>
          <option value='<?php echo $accessed_projects_id?>'><?php echo $project_id_name_arr[$accessed_projects_id]?>(<?php echo $accessed_projects_id?>)
          <?php }?>
      	</select>
        
        </div>   
      </td>
      <td width="8%" valign=center align=center><br>
        <font size="2" face="Arial">
        <input type=button value='&nbsp;> >&nbsp;' onClick="moveOption(this.form.frm_sourceList, this.form.frm_selected_list); <?php echo ((!$prot_type)?"create_protocols_type_selection(this.form)":'')?>">
        <br><br>
        <input type=button value='&nbsp;< <&nbsp;' onClick="moveOption(this.form.frm_selected_list, this.form.frm_sourceList); <?php echo ((!$prot_type)?"create_protocols_type_selection(this.form)":'')?>">
        </font> 
      </td>
      <td width="46%" align=center valign=top>
        <div class=maintext_bold>Selected objects</div>
        <select id="frm_selected_list" name="frm_selected_list" size=5 multiple>
        <?php foreach($selected_arr as $selected_id){?>
          <option value='<?php echo $selected_id?>'><?php echo $project_id_name_arr[$selected_id]?>(<?php echo $selected_id?>)
        <?php }?>
        </select>
      </td>
    </tr>
  <?php if($theaction == "pop_export_win" && $type_list_arr){?>
    <tr><td colspan=3><hr size=1></td></tr>
    <tr>
      <td width="400" align=center valign=top>
        <div class=maintext_bold>Protocols type</div>
        <div class=maintext>
        <select ID="frm_source_type" name="frm_source_type" size=4 multiple>
          <?php foreach($type_list_arr as $type_list_val){
            if(!isset($protocol_full_name_arr[$type_list_val])) continue;
          ?>
          <option value='<?php echo $type_list_val?>'><?php echo format_pro_type_name($protocol_full_name_arr[$type_list_val])?>
          <?php }?>
      	</select>
        </div>   
      </td>
      <td width="60" valign=center align=center><br>
        <font size="2" face="Arial">
        <input type=button value='&nbsp;> >&nbsp;' onClick="moveOption(this.form.frm_source_type, this.form.frm_selected_type)">
        <br><br>
        <input type=button value='&nbsp;< <&nbsp;' onClick="moveOption(this.form.frm_selected_type, this.form.frm_source_type)">
        </font> 
      </td>
      <td width="400" align=center valign=top>
        <div class=maintext_bold>Selected Protocols type</div>
        <select id="frm_selected_type" name="frm_selected_type" size=4 multiple>
        </select>
      </td>
    </tr>
    <tr><td colspan=3><hr size=1></td></tr>
  <?php }?>  
<?php 
}

function print_this_detail($self_pro_arr,$this_sign,$modal){
  global $type_bgcolor,$pro_name_bgcolor,$selected_type_div_id,$projectID_DBname_arr,$AccessProjectID,$HITS_DB;
  global $protocol_full_name_arr,$analyst_this_page_permission_arr,$experiment_pro_name_arr;
  global $protocol_type_num_array;
  if($this_sign == "[-]"){
    $style = "display: block";
  }else{
    $style = "display: none";
  }
  $sample_pro_start = 1;
?>          
      <div style="width:100%;border: green solid 0px;">
        <div style="width:100%;text-align:left;font-size:small;font-family:Arial;font-weight:bold;color:black;border: green solid 0px;text-align:left">
          Experiment Protocol
        </div>
    <?php foreach($self_pro_arr as $self_pro_key => $self_pro_atr){    
        if(!in_array($self_pro_key, $experiment_pro_name_arr) && $sample_pro_start){
          $sample_pro_start = 0;
          print_creating_pro_type_block();
        }
        $type_div_id = $self_pro_key;
        $add_new_div_id = $type_div_id."_add_new";
        $type_lable_id = $type_div_id."_a";
        if($selected_type_div_id == $self_pro_key){
          $this_sign_tmp = "[-]";
          $style_tmp = "display: block";
        }else{
          $this_sign_tmp = $this_sign;
          $style_tmp = $style;
        }
        if($self_pro_atr){
          $row = $self_pro_atr[0];
        }else{
          $row['Type'] = $self_pro_key;
          $row['ProjectID'] = $AccessProjectID;
          $row['DB_name'] = $HITS_DB[$projectID_DBname_arr[$AccessProjectID]];
        }
        $form_id = $protocol_type_num_array[$type_div_id].'_frm';
        
    ?>
        <div style="width:100%;height:23px;position:relative;border: white solid 0px;background-color:<?php echo $type_bgcolor?>;padding:5px 0px 0px 0px;margin-top:1px;">
          <div style="float:left;width:85%;text-align:left;font-size:small;font-family:Arial;color:white;border: red solid 0px;padding:0px 0px 0px 5px;">          
            <b><?php echo format_pro_type_name($protocol_full_name_arr[$type_div_id])?></b>&nbsp;&nbsp;
            <?php if($analyst_this_page_permission_arr['Insert']){?>                        
              <a href="javascript: toggle_add_new('<?php echo $type_div_id?>')"  title='add new'><font color=white>[add new]</font></a>&nbsp;&nbsp;
              <a href="javascript: pop_other_project_win('<?php echo $type_div_id?>','<?php echo $add_new_div_id?>','<?php echo $form_id?>')"  title='pop other project'><font color=white>[import from other projects]</font></a>
            <?php }?>
          </div>
          <div style="float:right;width:5%;text-align:right;border: red solid 0px;padding:0px 5px 0px 0px;">
              <a id='<?php echo $type_lable_id?>' href="javascript: toggle_all_detail_this('<?php echo $type_lable_id?>','<?php echo $type_div_id?>','show_type_detail','<?php echo $modal?>')"  title='protocol detail'>
                <?php echo $this_sign_tmp?>
              </a>
          </div>
        </div>
        <div id="<?php echo $add_new_div_id?>" STYLE="display: none;border: black solid 1px;">
          <?php print_single_detail($row,'add_new_single');?>
        </div>
        <div id='<?php echo $type_div_id?>' STYLE="<?php echo $style_tmp?>">
        <?php if($this_sign_tmp == "[-]"){
            print_type_detail($self_pro_atr,$this_sign,$modal);
          }
        ?>    
        </div>
    <?php }
      if($sample_pro_start){
        print_creating_pro_type_block();
      }
    ?>
      </div>
<?php 
}

function print_creating_pro_type_block(){
  global $_SERVER,$analyst_this_page_permission_arr;
  global $USER;
?>     
  <div style="width:100%;height:20px;position:relative;border: white solid 0px;padding:5px 0px 0px 0px;margin-top:1px;">
    <div style="float:left;width:39%;text-align:left;font-size:small;font-family:Arial;font-weight:bold;color:black;border: red solid 0px;padding:5px 0px 0px 0px;">          
      Sample Protocol
    </div>
    <div style="float:right;width:59%;text-align:right;border: red solid 0px;padding:0px 5px 0px 0px;">
<?php if($analyst_this_page_permission_arr['Insert'] and $USER->Type == 'Admin' ){?> 
      <a href="javascript: open_s_type_block('protocol_type_add_new');"  title='create new sample protocol type'>
        [create new sample protocol type]
      </a>
<?php }?>
    </div>
  </div>
  <div id="protocol_type_add_new" STYLE="display: none;border: black solid 1px;">
    <table cellspacing='1' cellpadding='1' border='0' align=center width='99%'>
      <FORM ID='protocol_type_frm' NAME='create_pro_type_frm' ACTION='<?php echo $_SERVER['PHP_SELF'];?>' METHOD='POST'>
      <input type='hidden' name='theaction' value='create_new_s_type'>
      <input type='hidden' name='modal' value='this_project'>
      <tr bgcolor="#f1f1f8">
    	  <td align="right" width='20%' nowrap>
    	    <div class=maintext>&nbsp;&nbsp;Protocol Type:&nbsp;</div>
    	  </td>
       <td align="left" bgcolor="#f1f1f8">
          <div class=maintext><input type="text" name="frm_s_protocol_type" size="40" maxlength=55 value=""></div>
        </td>
     </tr>
      <tr bgcolor="#f1f1f8">	  
       <td valign=top colspan=2 align=center>
          <input type="button" value="Save" onClick="javascript: add_new_s_protocol_type(this.form);">&nbsp;
          <input type="reset" value="Reset">&nbsp;
          <input type="button" value="Close" onClick="javascript: close_add_new('protocol_type_add_new');">
      	</td>
     </tr>
      </FORM>
    </table>
  </div>
<?php 
} 

function print_type_detail($self_pro_atr,$this_sign,$modal,$level='',$message=''){
  global $used_prot_id_arr,$pro_name_bgcolor,$theaction,$new_protocol_id,$selected_prot_div_id,$analyst_this_page_permission_arr,$outsite_script;
  global $protocol_type_num_array,$AccessUserID;
  if($this_sign == "[-]"){
    $style = "display: block";
  }else{
    $style = "display: none";
  }
  $selected_prot_id = '';
  if($selected_prot_div_id){
    $tmp_arr = explode('_',$selected_prot_div_id);
    if(count($tmp_arr) == 2) $selected_prot_id = $tmp_arr[1];
  }
  if(!isset($self_pro_atr[0]['Name']) || !$self_pro_atr[0]['Name']) return;
?>  
    <div style="width:100%;border: green solid 0px;">
      <?php foreach($self_pro_atr as $self_pro_val){
          if($level){
            if($self_pro_val['ID'] == $new_protocol_id){
              $this_sign = "[-]";
              $style = "display: block";
            }else{
              $this_sign = "[+]";
              $style = "display: none";
            }  
          }
          
          if($self_pro_val['ID'] == $selected_prot_id){
            $this_sign_tmp = "[-]";
            $style_tmp = "display: block";
            if($outsite_script){
              $theaction_tmp = '';
            }else{
              $theaction_tmp = 'modify_single_detail';
            }
          }else{
            $this_sign_tmp = $this_sign;
            $style_tmp = $style;
            $theaction_tmp = $theaction;
          }          
          $ProtocolID = $self_pro_val['ID'];
          $base_div_id = $self_pro_val['ProjectID'].'_'.$ProtocolID;
          $div_id_a = $base_div_id.'_a';
          $div_id_b = $base_div_id.'_b';
          $DB_name = $self_pro_val['DB_name'];
      ?> 
        <div style="width:100%;height:26px;position:relative;border: white solid 0px;background-color:<?php echo $pro_name_bgcolor?>;margin-top:1px;">
          <div style="float:left;width:75%;text-align:left;font-size:small;font-family:Arial;color:black;border: red solid 0px;padding:5px 0px 0px 5px;">          
            <div id="<?php echo $div_id_b?>"class=maintext_bold>
        <?php if($modal == 'other_projects'){?>
              <a href="javascript: pass_protocol_data('<?php echo $protocol_type_num_array[$self_pro_val['Type']];?>','<?php echo $base_div_id."_1"?>','<?php echo $base_div_id."_2"?>')"  title='pass_data'>
                <img border="0" src="images/Icons-mini-arrow_left.gif">&nbsp;
              </a>  
        <?php }?>
                &nbsp;<?php echo $self_pro_val['Name']?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo (($message == $self_pro_val['ID'])?'This protocol has been used. You cloud not delete it':'')?>
               
            </div>
          </div>  
          <div style="float:right;width:20%;text-align:right;border: red solid 0px;padding:2px 5px 0px 0px;">
        <?php if($analyst_this_page_permission_arr['Modify'] && $modal == 'this_project' && !in_array($self_pro_val['ID'], $used_prot_id_arr) && $AccessUserID == $self_pro_val['UserID']){?>
              <a href="<?php echo $_SERVER['PHP_SELF'];?>?selected_type_div_id=<?php echo urlencode($self_pro_val['Type']);?>&selected_prot_div_id=<?php echo urlencode($base_div_id);?>&modal=this_project"  title='modify detail'>
              <img border="0" src="images/icon_view.gif" alt="Modify">
              </a>
          <?php if($analyst_this_page_permission_arr['Delete']){?>
              <a href="javascript: delete_single_detail('<?php echo $self_pro_val['Type'];?>','<?php echo $ProtocolID?>','<?php echo $self_pro_val['Name'];?>')"  title='delete detail'>
              <img border="0" src="images/icon_purge.gif" alt="Delete">
              </a>
          <?php }else{?>
               <img src="images/icon_empty.gif">&nbsp;
          <?php }?>    
        <?php }else{?>
              <img src="images/icon_empty.gif">&nbsp;
        <?php }?>    
              <a id='<?php echo $div_id_a?>'href="javascript: toggle_detail('<?php echo $base_div_id?>','<?php echo $DB_name?>')"  title='protocol detail'>
              <?php echo $this_sign_tmp?>
              </a> 
          </div>     
        </div>
        <div id="<?php echo $base_div_id;?>" STYLE="<?php echo $style_tmp;?>; border: black solid 1px">
       <?php if($this_sign_tmp == "[-]"){
            print_single_detail($self_pro_val,$theaction_tmp);
         }
       ?>
        </div>
      <?php }?>
      </div>
<?php 
}

function print_single_detail($row='',$theaction=''){
  global $project_id_name_arr,$user_id_name_arr,$protocol_full_name_arr;
  global $protocol_type_num_array;  
  $bgcolor = '#f1f1f8';
  $user_name = '';
  $base_div_id = '';
  if($theaction != 'add_new_single'){
    if(array_key_exists($row['UserID'], $user_id_name_arr)) {
      $user_name = $user_id_name_arr[$row['UserID']];
    }
    $base_div_id = $row['ProjectID'].'_'.$row['ID'];
  }     
?>
<table cellspacing='1' cellpadding='1' border='0' align=center width='99%'>
<?php if($theaction == 'add_new_single'){?>
  <FORM ID='<?php echo $protocol_type_num_array[$row['Type']]?>_frm' NAME='<?php echo $protocol_type_num_array[$row['Type']]?>_frm' ACTION='<?php echo $_SERVER['PHP_SELF'];?>' METHOD='POST'>
  <input type='hidden' name='theaction' value='insert_single_detail'>
  <input type='hidden' name='base_id' value='<?php echo $row['Type'];?>'>
  <input type='hidden' name='frm_Type' value='<?php echo $row['Type']?>'>
  <input type='hidden' name='frm_ID' value=''>
  <input type='hidden' name='frm_ProjectID' value='<?php echo $row['ProjectID']?>'>
  <input type='hidden' name='frm_Date' value='<?php echo @date("Y-m-d")?>'>
  <input type='hidden' name='DB_name' value='<?php echo $row['DB_name']?>'>
  <input type='hidden' name='modal' value='this_project'>
<?php }elseif($theaction == 'modify_single_detail'){
    $base_id = $row['ProjectID']."_".$row['ID'];
?>
  <FORM ACTION='<?php echo $_SERVER['PHP_SELF'];?>' METHOD='POST'>
  <input type='hidden' name='theaction' value='update_single_detail'>
  <input type='hidden' name='base_id' value='<?php echo $base_id;?>'>
  <input type='hidden' name='frm_Type' value='<?php echo $row['Type']?>'>
  <input type='hidden' name='frm_ID' value='<?php echo $row['ID']?>'>
  <input type='hidden' name='frm_ProjectID' value='<?php echo $row['ProjectID']?>'>
  <input type='hidden' name='frm_Date' value='<?php echo @date("Y-m-d")?>'>
  <input type='hidden' name='DB_name' value='<?php echo $row['DB_name']?>'>
  <input type='hidden' name='modal' value='this_project'>
<?php }?>
  <?php if($theaction != 'add_new_single'){?>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext><?php echo str_repeat("&nbsp;", 30);?>Protocol ID:&nbsp;&nbsp;</div>
	  </td>
	  <td align="left">    
      <div class=maintext><?php echo $row['ID'];?></div>
    </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" width='20%' nowrap>
	    <div class=maintext>Protocol Type:&nbsp;&nbsp;</div>
	  </td>
    <td align="left">
      <div class=maintext><?php echo format_pro_type_name($protocol_full_name_arr[$row['Type']]);?></div>
    </td>
  </tr>
  <?php }?>
  <?php if($theaction != 'add_new_single'){?>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>Project:&nbsp;&nbsp;</div>
	  </td>
	  <td nowrap align="left">
      <div class=maintext><?php echo $project_id_name_arr[$row['ProjectID']];?></div>
    </td>
	</tr>
  <?php }?>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>&nbsp;&nbsp;Protocol Name:&nbsp;&nbsp;</div>
	  </td>
	  <td align="left" bgcolor="<?php echo $bgcolor;?>">
    <?php if($theaction == 'add_new_single'){?>  
        <div class=maintext><input type="text" name="frm_Name" size="40" maxlength=55 value=""></div>
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <div class=maintext><input type="text" name="frm_Name" size="40" maxlength=55 value="<?php echo $row['Name'];?>"></div>
    <?php }else{?>
        <div id='<?php echo $base_div_id.'_1'?>' class=maintext><?php echo  $row['Name'];?></div>
    <?php }?>
    </td>
	</tr>
  <?php if($theaction != 'add_new_single'){?>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>Created by:&nbsp;</div>
	  </td>
	  <td nowrap align="left">
      <div class=maintext><?php echo $user_name;?></div>
    </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>Creation date:&nbsp;&nbsp;</div>
	  </td>
	  <td nowrap align="left">
      <div class=maintext><?php echo $row['Date'];?></div>
	  </td>
	</tr>
  <?php }?>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' valign=top>
	    <div class=maintext>Protocol Detail:&nbsp;&nbsp;</div>
	  </td>
	  <td valign=top align="left">
    <?php if($theaction == 'add_new_single'){?>  
        <div class=maintext><textarea name=frm_Detail cols=70 rows=6></textarea></div>
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <div class=maintext><textarea name=frm_Detail cols=70 rows=6><?php echo $row['Detail']?></textarea></div>
    <?php }else{?>
        <div id='<?php echo $base_div_id.'_2'?>' class=maintext><?php echo  htmlentities($row['Detail']);?></div>
    <?php }?>
	  </td>
	</tr>
  <?php if($theaction){?>
  <tr bgcolor="<?php echo $bgcolor;?>">	  
	  <td valign=top colspan=2 align=center>
    <?php if($theaction == 'add_new_single'){?>  
        <input type="button" value="Save" onClick="javascript: add_new(this.form);">&nbsp;
        <input type="reset" value="Reset">&nbsp;
        <input type="button" value="Close" onClick="javascript: close_add_new('<?php echo $row['Type']."_add_new"?>');">
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <input type="button" value="Save" onClick="javascript: add_new(this.form);">
        <input type="reset" value="Reset">&nbsp;
        <input type="button" value="Close" onClick="javascript: close_modify('<?php echo $base_id?>');">
    <?php }?>
	  </td>
	</tr>
 <?php }?> 
<?php if($theaction == 'add_new_single' || $theaction == 'modify_single_detail'){?>
</FORM>
<?php }?>
</table>
<?php 
}
function create_types_div($protocol_type_arr){
?>
    <tr><td colspan=3><hr size=1></td></tr>
    <tr>
      <td width="400" align=center valign=top>
        <div class=maintext_bold>Protocols type</div>
        <div class=maintext>
        <select ID="frm_source_type" name="frm_source_type" size=4 multiple>
          <?php foreach($protocol_type_arr as $protocol_type){
          ?>
          <option value='<?php echo $protocol_type?>'><?php echo $protocol_type?>
          <?php }?>
      	</select>
        </div>   
      </td>
      <td width="60" valign=center align=center><br>
        <font size="2" face="Arial">
        <input type=button value='&nbsp;> >&nbsp;' onClick="moveOption(this.form.frm_source_type, this.form.frm_selected_type)">
        <br><br>
        <input type=button value='&nbsp;< <&nbsp;' onClick="moveOption(this.form.frm_selected_type, this.form.frm_source_type)">
        </font> 
      </td>
      <td width="400" align=center valign=top>
        <div class=maintext_bold>Selected Protocols type</div>
        <select id="frm_selected_type" name="frm_selected_type" size=4 multiple>
        </select>
      </td>
    </tr>
    <tr><td colspan=3><hr size=1></td></tr>
<?php 
}

function get_protocols_arr_for_both_db($SQL_2,&$other_pro_arr){
  global $HITS_DB_obj_arr;                  
  foreach($HITS_DB_obj_arr as $DB_obj){
    @mysqli_free_result($results_1);
    $results_1 = mysqli_query($DB_obj->link, stripslashes($SQL_2));
    while($row = mysqli_fetch_array($results_1)){
      $row['DB_name'] = $DB_obj->selected_db_name;
      if(array_key_exists($row['Type'], $other_pro_arr)){
        array_push($other_pro_arr[$row['Type']], $row);
      }else{
        $other_pro_arr[$row['Type']] = array();
        array_push($other_pro_arr[$row['Type']], $row);
      }  
    }
    @mysqli_free_result($results_1);
  }
}
?>           