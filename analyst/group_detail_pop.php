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
$type_bgcolor = '#808040';
$pro_name_bgcolor = '#d1d0be';
$general_title_bgcol = '#b1b09e';
$bgcolor = "#f1f1ed";
$error_msg = '';
$is_error = 0;
$this_sign = '[+]';
$modal = '';
$new_protocol_id = '';
$selected_type_div_id = '';
$selected_prot_div_id = '';
$old_Initial = '';
$frm_passed_Icon = '';
$toggle_new = '';

$self_pro_arr = array();
$other_pro_arr = array();
$group_type_arr = array();
$selected_str = '';
$prot_type = '';
$icon_folder = "./gel_images";
$frm_Icon = '';
$display_new = 0;
$outsite_script = 0;
                                
$group_name_lable_arr = array('Bait' => 'Bait Groups',
                              'Experiment' => 'Experiment Groups',
                              'Band' => 'Sample Groups',
                              'Export' => 'Export Versions');
                                
$self_pro_arr = array('Bait' => array(),
                      'Experiment' => array(),
                      'Band' => array(),
                      'Export' => array());
$this_group_name_arr = array('Bait' => array(),
                      'Experiment' => array(),
                      'Band' => array(),
                      'Export' => array());                      
$this_group_init_arr = array('Bait' => array(),
                      'Experiment' => array(),
                      'Band' => array(),
                      'Export' => array()); 
                                                    
foreach($group_name_lable_arr as $key => $value){
  array_push($group_type_arr, $key);
}

set_time_limit(2400);
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

$Log = new Log();

if($this_sign == "[%2B]") $this_sign = '[+]';

$HITS_DB_obj_arr = array();              
foreach($HITS_DB as $DB_name_val){
  $HITS_DB_obj_arr[$DB_name_val] = new mysqlDB($DB_name_val);
}

$type_objID_arr = array();
$objID_type_arr = array();
$has_group_projectID_arr = array();

$SQL = "SELECT `Type`,
          `ProjectID`
          FROM `NoteType`";
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
    if(!in_array($row['ProjectID'],$has_group_projectID_arr)){
      array_push($has_group_projectID_arr, $row['ProjectID']);
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
$is_error = 0;
if($modal == "this_project"){  
  if($theaction == 'insert_single_detail' || $theaction == 'update_single_detail' ){
    $action = '';
    $icon_img_name = '';
    
    $frm_Initial = strtoupper(trim($frm_Initial));
    //$frm_Name = preg_replace("/[^A-Za-z0-9  _-]/",'',$frm_Name);
    
    if($frm_passed_icon){
      $icon_img_name = $frm_passed_icon;
    }elseif(!$upload_image || $selected_type_div_id == 'Export'){
      $icon_img_name = '';    
    }else{
      $icon_ret = check_icon($frm_Icon);
      if($icon_ret[0]){
        $icon_img_name = $icon_ret[1];
      }else{
        $is_error = 1;
        $error_msg = $icon_ret[1];
      }
    }
    
    if($is_error && $theaction == 'insert_single_detail'){      
      $toggle_new = 'Y';
      header ("Location: ".$_SERVER['PHP_SELF']."?selected_type_div_id=$selected_type_div_id&selected_prot_div_id=$base_id&modal=this_project&error_msg=$error_msg&toggle_new=$toggle_new");
      exit;
    }elseif($is_error && $theaction == 'update_single_detail'){ 
      $action = 'update';
      $new_protocol_id = $frm_ID;
      if($frm_ID){
        $refresh = 1;
      }
      if(is_numeric($frm_ID)){
        $this_sign = '[+]';
        $display_new = 3;
        $theaction = '';
      } 
      $is_error = 0; 
    }elseif(!$is_error){
      if($theaction == 'insert_single_detail'){
        $SQL ="INSERT INTO NoteType SET 
            Name='".$frm_Name."',  
            Type='".$frm_Type."',
            Description='".mysqli_real_escape_string($HITSDB->link, $frm_Description)."',
            Icon='".$icon_img_name."',
            ProjectID='".$AccessProjectID."',
            UserID='".$USER->ID."',
            Initial='".$frm_Initial."'";
        if($frm_ID = $HITSDB->insert($SQL)){
          $action = 'insert';
        }  
      }elseif($theaction == 'update_single_detail'){
        $SQL = "UPDATE NoteType SET 
                Name='".$frm_Name."', 
                Description='".mysqli_real_escape_string($HITSDB->link, $frm_Description)."',";
        if($upload_image){        
          $SQL .= "Icon='".$icon_img_name."',";
        }  
        $SQL .= "UserID='".$USER->ID."',
            Initial='".$frm_Initial."'
            WHERE ID='".$frm_ID."'";
        if($frm_ID_tmp = $HITSDB->update($SQL)){
          $action = 'update';
        }
      }
      $new_protocol_id = $frm_ID;
      if($frm_ID){
        $Desc = "Name=$frm_Name,Description=$frm_Description,Date=".@date("Y-m-d");
        $Log->insert($AccessUserID,'NoteType',$frm_ID,$action,$Desc,$AccessProjectID);
        $refresh = 1;
      }
      if(is_numeric($frm_ID)){
        $this_sign = '[+]';
        $display_new = 3;
        $theaction = '';
      }    
    }
  }elseif($theaction == 'lock_export'){
    
    $SQL = "UPDATE NoteType SET
            Icon='locked'
            WHERE ID='".$frm_ID."' and UserID='".$USER->ID. "'";
    if($frm_ID_tmp = $HITSDB->update($SQL)){
      $action = 'update';
    }
  }elseif($theaction == 'unlock_export'){
    
    $SQL = "UPDATE NoteType SET
            Icon=''
            WHERE ID='".$frm_ID."' and UserID='".$USER->ID. "'";
    if($frm_ID_tmp = $HITSDB->update($SQL)){
      $action = 'update';
    }
  }elseif($theaction == 'delete_single_detail'){
    $SQL = "DELETE FROM NoteType  
            WHERE ID = '$ProtocolID'";
    $db_ret = $HITSDB->execute($SQL);;
    //$theaction = 'show_type_detail';
  }
}

if($theaction == 'show_single_detail'){
  $DB_obj = $HITS_DB_obj_arr[$DB_name];
  $tmp_arr = explode('_',$base_id);
  $ProjectID = $tmp_arr[0];
  $ProtocolID = $tmp_arr[1];
  $SQL = "SELECT `ID`,
                `Name`,
                `Type`,
                `Description`,
                `Icon`,
                `ProjectID`,
                `Initial`,
                `UserID` 
                FROM `NoteType` 
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
  $used_group_id_arr = array();
  //---------------------------------------------------
  foreach($group_name_lable_arr as $tmp_key => $tmp_val){
    $table_name = $tmp_key."Group";
    if($tmp_key == 'Export') continue;
    $SQL = "SELECT `NoteTypeID` FROM $table_name GROUP BY `NoteTypeID`";
    if($tmp_arr = $HITSDB->fetchAll($SQL)){
      foreach($tmp_arr as $tmp_val){
        if(!in_array($tmp_val['NoteTypeID'], $used_group_id_arr)){
          array_push($used_group_id_arr, $tmp_val['NoteTypeID']);
        }
      }
    }  
  }
  
  $SQL_1 = "SELECT `ID`,
                `Name`,
                `Type`,
                `Description`,
                `Icon`,
                `ProjectID`,
                `Initial`,
                `UserID` 
                FROM `NoteType` 
                WHERE `ProjectID`='$AccessProjectID' 
                ORDER BY `Type`,`ID` DESC";
  $results_1 = mysqli_query($HITSDB->link, $SQL_1);
  while($row = mysqli_fetch_assoc($results_1)){
    $row['DB_name'] = $HITSDB->selected_db_name;
    if(array_key_exists($row['Type'], $self_pro_arr)){
      array_push($self_pro_arr[$row['Type']], $row);
      array_push($this_group_name_arr[$row['Type']], $row['Name']);
      array_push($this_group_init_arr[$row['Type']], $row['Initial']);
    }
  }
}elseif($modal == "other_projects"){
  if($selected_str && $prot_type){
    $SQL_2 = "SELECT `ID`,
                  `Name`,
                  `Type`,
                  `Description`,
                  `Icon`,
                  `ProjectID`,
                  `Initial`,
                  `UserID` 
                  FROM `NoteType`  
                  WHERE `Type`='$prot_type'
                  AND `ProjectID` IN ($selected_str) 
                  ORDER BY `Type`,`ProjectID`";
    get_protocols_arr_for_both_db($SQL_2,$other_pro_arr);
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
    print_type_detail($type_arr,$this_sign,$modal,1);
  }elseif($modal == 'other_projects'){
    //$type_arr = $other_pro_arr[$base_id];
    //print_type_detail($type_arr,$this_sign,$modal,1);
  }  
  exit;
}  

?>

<html>
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
var init_arr_Bait = new Array();
var init_arr_Experiment = new Array();
var init_arr_Band = new Array();
<?php foreach($this_group_init_arr as $key => $init_val){
    $tmp_arr = $init_val;
    foreach($tmp_arr as $tmp_val){
      if($key == 'Bait'){?>
        init_arr_Bait.push("<?php echo $tmp_val?>"); 
<?php     }elseif($key == 'Experiment'){?>
        init_arr_Experiment.push("<?php echo $tmp_val?>");
<?php     }elseif($key == 'Band'){?>
        init_arr_Band.push("<?php echo $tmp_val?>");
<?php     }
    }  
  }
?>

var group_arr_Bait = new Array();
var group_arr_Experiment = new Array();
var group_arr_Band = new Array();
var group_arr_Export = new Array();
<?php foreach($this_group_name_arr as $key => $group_val){
    $tmp_arr = $group_val;
    foreach($tmp_arr as $tmp_val){
      if($key == 'Bait'){?>
        group_arr_Bait.push("<?php echo $tmp_val?>"); 
<?php     }elseif($key == 'Experiment'){?>
        group_arr_Experiment.push("<?php echo $tmp_val?>");
<?php     }elseif($key == 'Band'){?>
        group_arr_Band.push("<?php echo $tmp_val?>");
<?php     }elseif($key == 'Export'){?>
        group_arr_Export.push("<?php echo $tmp_val?>");
<?php     }
    }  
  }
?>
var group_type_arr = new Array();
<?php foreach($group_type_arr as $protocol_type_name){?>
    group_type_arr.push("<?php echo $protocol_type_name?>"); 
<?php }?>

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
  for(var j=0; j<group_name_arr.length; j++){
    if(group_name_arr[j] == P_name){
      group_name_arr[j] = '';
      break;
    }  
  }
} 
function remove_p_init(P_init){        
  for(var j=0; j<init_arr.length; j++){
    if(init_arr[j] == P_init){
      init_arr[j] = '';
      break;
    }  
  }
}

function lock_export(theForm, YesNo){
  if(YesNo == 'Yes'){
    theForm.theaction.value = "lock_export";
    if(confirm("If you lock the export version, you cannot change anything for the export version until it is unlocked.\n Are you sure that you want to lock it?")){
      theForm.submit();
    }
  }else{
    theForm.theaction.value = "unlock_export";
    theForm.submit();
  }
}
function add_new(theForm){
  theForm.frm_Name.value = trimString(theForm.frm_Name.value);
  var base_id = theForm.base_id.value;
  var item_Type = theForm.frm_Type.value;
  var p_name = theForm.frm_Name.value;
  var p_init = theForm.frm_Initial.value.toUpperCase();
  var old_init = theForm.old_Initial.value.toUpperCase();
  if(!onlyAlphaNumerics(p_name, 7)){
    alert("Only characters \"%+-_A-Za-z0-9\(\)\.:\" and spaces are allowed.");
    return;
  }
  if(theForm.theaction.value == "update_single_detail"){
    var lable_id = base_id + "_b";
    var modified_obj = document.getElementById(lable_id);
    //var lable = trimString(modified_obj.innerHTML.replace(/&nbsp;/, ""));
    var tmp_arr = modified_obj.innerHTML.split(";");
    var tmp_len = tmp_arr.length;
    var lable = trimString(tmp_arr[tmp_len-1]);
    if(theForm.frm_Icon.value == ''){
      theForm.upload_image.value = 0;
    }
  }else if(theForm.theaction.value == 'insert_single_detail'){
    if(theForm.frm_passed_icon.value == '' && theForm.frm_Icon.value == '' && item_Type != 'Export'){
      alert("Please upload a icom file");
      return;
    }
    if(theForm.frm_Icon.value != ''){
      theForm.frm_passed_icon.value = '';
    }
  } 
  var flag = 0;
  //if(theForm.theaction.value == 'insert_single_detail' || (theForm.theaction.value == "update_single_detail" && lable != p_name)){
  if(theForm.theaction.value == 'insert_single_detail'){
	if(item_Type == 'Bait' && group_arr_Bait.in_array(p_name)){
      flag = 1;
    }else if(item_Type == 'Experiment' && group_arr_Experiment.in_array(p_name)){
      flag = 1;
    }else if(item_Type == 'Band' && group_arr_Band.in_array(p_name)){
      flag = 1;
    }else if(item_Type == 'Export' && group_arr_Export.in_array(p_name)){
      flag = 1;
    }
    if(flag == 1){  
      alert("The name " + p_name + " has been used.");
      return;
    }
  }
    
  if(theForm.theaction.value == 'insert_single_detail' || (theForm.theaction.value == "update_single_detail" && old_init != p_init)){
    if(item_Type == 'Bait' && init_arr_Bait.in_array(p_init)){
      flag = 1;
    }else if(item_Type == 'Experiment' && init_arr_Experiment.in_array(p_init)){
      flag = 1;
    }else if(item_Type == 'Band' && init_arr_Band.in_array(p_init)){
      flag = 1;
    }
    if(flag == 1){  
      alert("The initial " + p_init + " has been used.");
      return;
    }
  }
  theForm.submit();
}

function modify_detail(base_id,DB_name){
  var selected_obj = document.getElementById(base_id);
  var selected_a_id = base_id + '_a';
  var selected_a_obj = document.getElementById(selected_a_id);
  queryString = "DB_name=" + DB_name + "&base_id=" + base_id + "&theaction=modify_single_detail";
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
function close_other_divs(base_id,close_all){
  for(var i=0; i<group_type_arr.length; i++){
    if(group_type_arr[i] != base_id || close_all == 'Y'){
      var other_base_id = group_type_arr[i];
      var other_lable_id = other_base_id + '_a';
      var other_add_new_id = other_base_id + '_add_new';
      var other_frm_id = other_base_id + '_frm';
      var other_lable_obj = document.getElementById(other_lable_id);
      var other_base_obj = document.getElementById(other_base_id);
      var other_add_new_obj = document.getElementById(other_add_new_id);
      var other_frm_obj = document.getElementById(other_frm_id); 
      var error_msg_id = group_type_arr[i] + '_error_msg';
      var error_msg_obj = document.getElementById(error_msg_id);
      if(error_msg_obj != null){
        error_msg_obj.innerHTML='';
      }
      var other_sign = '[+]';
      other_sign = other_sign.replace('+', '%2B');
      other_lable_obj.innerHTML = '[+]';
      other_base_obj.style.display = "none";
      other_add_new_obj.style.display = "none";
      other_frm_obj.reset(); 
    }
  }
  if(close_all == 'Y'){
    toggle_add_new(base_id);
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
  var error_msg_obj =  document.getElementById('error_msg');
  if(error_msg_obj != null){
    error_msg_obj.innerHTML='';
  }
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
  theForm.frm_Description.value = '';
  new_obj.style.display = "block"; 
  if(!peptedeW.closed && peptedeW.location) {
    peptedeW.close();
  }
  close_other_divs(prot_type,'Y');
  var file = "<?php echo $PHP_SELF;?>?modal=other_projects&prot_type=" + prot_type;
  peptedeW = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=620,height=600');
  peptedeW.moveTo(1500,0);
  peptedeW.focus();
}

function pop_export_win(){
  if(!peptedeW.closed && peptedeW.location) {
    peptedeW.close();
  }
  var file = "<?php echo $PHP_SELF;?>?modal=&theaction=pop_export_win";
  peptedeW = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=620,height=400');
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
  
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function pass_protocol_data(obj_frm,name_id,detail_id,ini_id,icon_id){
  var name_obj = document.getElementById(name_id);
  var detail_obj = document.getElementById(detail_id);
  var ini_obj = document.getElementById(ini_id);
  var icon_obj = document.getElementById(icon_id);
  if(obj_frm == 'Bait'){
    opener.document.Bait_frm.frm_Name.value = name_obj.innerHTML;
  	opener.document.Bait_frm.frm_Description.value = detail_obj.innerHTML;
    opener.document.Bait_frm.frm_Initial.value = ini_obj.innerHTML;
    opener.document.Bait_frm.frm_passed_icon.value = icon_obj.innerHTML;    
  }else if(obj_frm == 'Experiment'){
    opener.document.Experiment_frm.frm_Name.value = name_obj.innerHTML;
  	opener.document.Experiment_frm.frm_Description.value = detail_obj.innerHTML;
    opener.document.Experiment_frm.frm_Initial.value = ini_obj.innerHTML;
    opener.document.Experiment_frm.frm_passed_icon.value = icon_obj.innerHTML;
  }else if(obj_frm == 'Band'){
    opener.document.Band_frm.frm_Name.value = name_obj.innerHTML;
  	opener.document.Band_frm.frm_Description.value = detail_obj.innerHTML;
    opener.document.Band_frm.frm_Initial.value = ini_obj.innerHTML;
    opener.document.Band_frm.frm_passed_icon.value = icon_obj.innerHTML;
  }else if(obj_frm == 'Export'){
    opener.document.Export_frm.frm_Name.value = name_obj.innerHTML;
  	opener.document.Export_frm.frm_Description.value = detail_obj.innerHTML;
  }  
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
  theForm.theaction.value = 'export_protocols'
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
<body <?php echo (($toggle_new)?"onload=\"toggle_add_new('$selected_type_div_id')\"":"");?>>
<?php 
if($modal == "this_project"){
  $general_title = "<span class=pop_header_text>Groups</span>  <font face='arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font> ";
  //$general_title = "Groups for project ".$AccessProjectName;
  $tmp_align = 'left';
}elseif($modal == "other_projects"){
  $general_title = "<span class=pop_header_text>Groups from other projects</span> <font face='helvetica,arial,futura' size='2'>   (".$group_name_lable_arr[$prot_type].")</font>"; 
  //$general_title = "Groups from other projects (".$group_name_lable_arr[$prot_type].")";
  $type_bgcolor = '';
  $tmp_align = 'left';
}else{
  $general_title = "Export Groups";
  $tmp_align = 'left';
}
?>
  <div style="width:600px;border: #a0a7c5 solid 1px;padding:0px 0px 10px 0px;overflow:auto;">
  <center>  
<?php if($modal == "this_project"){?> 
     <div style="width:90%;border: blue solid 0px;"> 
      <div style="width:100%;border: green solid 0px;height:35px;top:10px;position:relative;">
        <div style="float:left;width:70%;border: red solid 0px;text-align:<?php echo $tmp_align?>;">
          <?php echo $general_title?>
          &nbsp; &nbsp; &nbsp;
          <a href="javascript: popwin('../doc/Analyst_help.php#faq41', 800, 600, 'help');">
            <img src='./images/icon_HELP.gif' border=0 >
          </a>
        </div>     
        <div style="float:right;display:none;width:26%;border: red solid 0px;text-align:right;">
          <a id='lable_1'href="<?php echo $PHP_SELF;?>?modal=this_project&this_sign=<?php echo (($this_sign=='[+]')?'[-]':'[%2B]')?>"  title='all protocol detail in project <?php echo $AccessProjectName?>'>              
          <?php echo $this_sign?>
          </a>
        </div>      
      </div>
      <div style="width:100%;border: red solid 0px;padding:10px 0px 0px 0px;">
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
        <table border=0 width=99% cellspacing="10" cellpadding=0>
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
        <table border=0 width=99% cellspacing="10" cellpadding=0>
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
  global $user_accessed_projects_arr,$group_type_arr,$theaction,$project_id_name_arr,$AccessProjectID,$modal;
  global $type_objID_arr,$has_group_projectID_arr,$selected_str,$objID_type_arr;
  global $group_name_lable_arr;
  $selected_arr = array();
  $type_list_arr = array();
  if($prot_type && isset($type_objID_arr[$prot_type])){
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
      <td width="400" align=center valign=top>
        <div class=maintext_bold>Projects</div>
        <div class=maintext>
        <select ID="frm_sourceList" name="frm_sourceList" size=5 multiple>
          <?php foreach($user_accessed_projects_arr as $accessed_projects_id){
              if($modal == "other_projects" && $accessed_projects_id == $AccessProjectID) continue;
              if($prot_type){
                if(!in_array($accessed_projects_id, $exist_protocols_array)) continue;
              }else{
                if(!in_array($accessed_projects_id, $has_group_projectID_arr)) continue;
                if(in_array($accessed_projects_id, $selected_arr)) continue;
              }  
          ?>
          <option value='<?php echo $accessed_projects_id?>'><?php echo $project_id_name_arr[$accessed_projects_id]?>(<?php echo $accessed_projects_id?>)
          <?php }?>
      	</select>
        </div>   
      </td>
      <td width="60" valign=center align=center><br>
        <font size="2" face="Arial">
        <input type=button value='&nbsp;> >&nbsp;' onClick="moveOption(this.form.frm_sourceList, this.form.frm_selected_list); <?php echo ((!$prot_type)?"create_protocols_type_selection(this.form)":'')?>">
        <br><br>
        <input type=button value='&nbsp;< <&nbsp;' onClick="moveOption(this.form.frm_selected_list, this.form.frm_sourceList); <?php echo ((!$prot_type)?"create_protocols_type_selection(this.form)":'')?>">
        </font> 
      </td>
      <td width="400" align=center valign=top>
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
          ?>
          <option value='<?php echo $type_list_val?>'><?php echo $group_name_lable_arr[$type_list_val]?>
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
  global $group_name_lable_arr,$analyst_this_page_permission_arr,$USER,$error_msg,$is_error,$lable_font_color;
  if($this_sign == "[-]"){
    $style = "display: block";
  }else{
    $style = "display: none";
  }
?>          
      <div style="width:100%;border: green solid 0px;">
    <?php foreach($self_pro_arr as $self_pro_key => $self_pro_atr){
        $type_div_id = $self_pro_key;
        $add_new_div_id = $type_div_id."_add_new";
        $type_lable_id = $type_div_id."_a"; 
        
        if($selected_type_div_id == $self_pro_key && !$is_error){
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
        $form_id = $type_div_id.'_frm';
        $font_color = 'white';
        $font_size = '2';
        if($type_div_id == 'Export'){
          $type_bgcolor = 'white';
          $font_color = $lable_font_color;
          $font_size = '3';
        }
    ?>
        <div style="width:100%;height:23px;position:relative;border: white solid 0px;background-color:<?php echo $type_bgcolor?>;padding:5px 0px 0px 0px;margin-top:1px;">
          <div style="float:left;width:85%;text-align:left;font-size:small;font-family:Arial;border: red solid 0px;padding:0px 0px 0px 5px;">          
            <font color=<?php echo $font_color?>><b><?php echo $group_name_lable_arr[$type_div_id]?></b></font>&nbsp;&nbsp;
            <?php if($analyst_this_page_permission_arr['Insert']){?>
              <a href="javascript: toggle_add_new('<?php echo $type_div_id?>')"  title='add new'><font color=<?php echo $font_color?>>[add new]</font></a>&nbsp;&nbsp;
              <a href="javascript: pop_other_project_win('<?php echo $type_div_id?>','<?php echo $add_new_div_id?>','<?php echo $form_id?>')"  title='pop other project'><font color=<?php echo $font_color?>>[import from other projects]</font></a>
            <?php }?>
          </div>
          <div style="float:right;width:5%;text-align:right;border: red solid 0px;padding:0px 5px 0px 0px;">
            <a id='<?php echo $type_lable_id?>' href="javascript: toggle_all_detail_this('<?php echo $type_lable_id?>','<?php echo $type_div_id?>','show_type_detail','<?php echo $modal?>')"  title='protocol detail'>
              <?php echo $this_sign_tmp?>
            </a>
          </div>
        </div>
          <?php if($type_div_id == 'Export'){?>
        <div style="width:100%;border: red solid 0px">
          <hr size=1>
        </div>
          <?php }?>
        <div id="<?php echo $add_new_div_id?>" STYLE="display: none;border: black solid 1px;">
          <?php print_single_detail($row,'add_new_single');?>
        </div>
        <div id='<?php echo $type_div_id?>' STYLE="<?php echo $style_tmp?>">
          <?php if($this_sign_tmp == "[-]"){
             print_type_detail($self_pro_atr,$this_sign,$modal);
           }
          ?>
        </div>
    <?php }?> 
      </div>
<?php 
}

function print_type_detail($self_pro_atr,$this_sign,$modal,$level=''){
  global $used_group_id_arr,$pro_name_bgcolor,$theaction,$new_protocol_id,$selected_prot_div_id,$display_new,$PHP_SELF,$analyst_this_page_permission_arr,$USER;
  global $AccessUserID,$error_msg,$is_error,$outsite_script;
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
?>
      <div style="width:100%;border: green solid 0px;">
      <?php foreach($self_pro_atr as $self_pro_val){
           
          if($level || $display_new){
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
          <div style="float:left;width:75%;text-align:left;font-size:small;font-family:Arial;color:black;border: red solid 0px;padding:2px 0px 0px 0px;">          
            
      <?php   if($modal == 'other_projects'){?>
			<div id="<?php echo $div_id_b?>" class=maintext_bold style="float:left;">
              <a href="javascript: pass_protocol_data('<?php echo $self_pro_val['Type'];?>','<?php echo $base_div_id."_1"?>','<?php echo $base_div_id."_2"?>','<?php echo $base_div_id.'_3'?>','<?php echo $base_div_id.'_4'?>')"  title='pass_data'>
                <img border="0" src="images/Icons-mini-arrow_left.gif">&nbsp;
              </a> 
			</div>
      <?php   }?>
            
      <?php   if($self_pro_val['Type'] == 'Export'){
             $locked_label = '';
             if($self_pro_val['Icon'] == 'locked'){
              $locked_label = " &nbsp; &nbsp; &nbsp; &nbsp; <font color='#FF0000'>(locked)</font>";
             }
      ?>
            <div id="<?php echo $div_id_b?>"class=maintext_bold  style="float:left;border: black solid 0px">
              <div style="float:left;width:13px;height:15px;border: red solid 0px;background-image:url('./gel_images/icon_star.gif');padding:2px 0px 0px 6px;">
                <?php echo $self_pro_val['Initial']?>
              </div>
              <div style="float:left;padding:5px 0px 0px 0px;border: red solid 0px;">
                &nbsp;&nbsp;<?php echo $self_pro_val['Name'].$locked_label ?>
              </div>
            </div>
      <?php   }else{?>
            <div id="<?php echo $div_id_b?>" class=maintext_bold  style="float:left;padding:5px 0px 0px 0px;border: red solid 0px;">
                &nbsp;<img border="0" src="gel_images/<?php echo $self_pro_val['Icon']?>" alt="<?php echo $self_pro_val['Name']?>">&nbsp;&nbsp;<?php echo $self_pro_val['Name']?> 
            </div>
      <?php   }?> 
          </div>
          <div style="float:right;width:20%;text-align:right;border: red solid 0px;padding:2px 5px 0px 0px;">
      <?php   if($modal == 'this_project' && $analyst_this_page_permission_arr['Modify'] && $AccessUserID == $self_pro_val['UserID']){?>
              <a href="<?php echo $_SERVER['PHP_SELF'];?>?selected_type_div_id=<?php echo $self_pro_val['Type'];?>&selected_prot_div_id=<?php echo $base_div_id?>&modal=this_project"  title='modify detail'>
              <img border="0" src="images/icon_view.gif" alt="Modify">
              </a>
      <?php     if(!in_array($self_pro_val['ID'], $used_group_id_arr) && $analyst_this_page_permission_arr['Delete']){?>  
              <a href="<?php echo $PHP_SELF;?>?modal=this_project&base_id=<?php echo $self_pro_val['Type'];?>&ProtocolID=<?php echo $ProtocolID?>&selected_type_div_id=<?php echo $self_pro_val['Type'];?>&theaction=delete_single_detail"  title='delete detail'>
              <img border="0" src="images/icon_purge.gif" alt="Delete">
              </a>
      <?php     }else{?>
              <img src="images/icon_empty.gif">&nbsp;
      <?php     }?>              
      <?php   }else{?>
              <img src="images/icon_empty.gif">&nbsp;
              <img src="images/icon_empty.gif">&nbsp;
      <?php   }?>    
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
  global $project_id_name_arr,$user_id_name_arr,$group_name_lable_arr,$bgcolor,$selected_prot_div_id,$error_msg,$selected_type_div_id;
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
  <FORM ID='<?php echo $row['Type']?>_frm' NAME='<?php echo $row['Type']?>_frm' ACTION='<?php echo $_SERVER['PHP_SELF'];?>' METHOD='POST' enctype="multipart/form-data">
  <input type='hidden' name='theaction' value='insert_single_detail'>
  <input type='hidden' name='base_id' value='<?php echo $row['Type'];?>'>
  <input type='hidden' name='frm_Type' value='<?php echo $row['Type']?>'>
  <input type='hidden' name='selected_type_div_id' value='<?php echo $row['Type']?>'>
  <input type='hidden' name='frm_ID' value=''>
  <input type='hidden' name='frm_ProjectID' value='<?php echo $row['ProjectID']?>'>
  <input type='hidden' name='frm_Date' value='<?php echo @date("Y-m-d")?>'>
  <input type='hidden' name='DB_name' value='<?php echo $row['DB_name']?>'>
  <input type='hidden' name='modal' value='this_project'>
<?php }elseif($theaction == 'modify_single_detail'){
    $base_id = $row['ProjectID']."_".$row['ID'];
?>
  <FORM ACTION='<?php echo $_SERVER['PHP_SELF'];?>' METHOD='POST' enctype="multipart/form-data">
  <input type='hidden' name='theaction' value='update_single_detail'>
  <input type='hidden' name='base_id' value='<?php echo $base_id;?>'>
  <input type='hidden' name='frm_Type' value='<?php echo $row['Type']?>'>
  <input type='hidden' name='selected_type_div_id' value='<?php echo $row['Type']?>'>
  <input type='hidden' name='frm_ID' value='<?php echo $row['ID']?>'>
  <input type='hidden' name='frm_ProjectID' value='<?php echo $row['ProjectID']?>'>
  <input type='hidden' name='frm_Date' value='<?php echo @date("Y-m-d")?>'>
  <input type='hidden' name='DB_name' value='<?php echo $row['DB_name']?>'>
  <input type='hidden' name='modal' value='this_project'>
<?php }?>
  <input type='hidden' name='upload_image' value='1'>
  <input type='hidden' name='frm_passed_icon' value=''>
  <input type='hidden' name='selected_prot_div_id' value='<?php echo $selected_prot_div_id?>'>
  <?php if($theaction != 'add_new_single'){?>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext><?php echo str_repeat("&nbsp;", 30);?>ID:&nbsp;&nbsp;</div>
	  </td>
	  <td align="left" colspan='2'>    
      <div class=maintext><?php echo $row['ID'];?>&nbsp;&nbsp;</div>
    </td>
	</tr>
  <?php }?>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>&nbsp;&nbsp;Name:&nbsp;</div>
	  </td>
	  <td align="left" bgcolor="<?php echo $bgcolor;?>" colspan='2'>
    <?php if($theaction == 'add_new_single'){?>  
        <div class=maintext><input type="text" name="frm_Name" size="40" maxlength=39 value=""></div>
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <div class=maintext><input type="text" name="frm_Name" size="40" maxlength=39 value="<?php echo $row['Name'];?>"></div>
    <?php }else{?>
        <div id='<?php echo $base_div_id.'_1'?>' class=maintext><?php echo  $row['Name'];?></div>
    <?php }?>
    </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>Type:&nbsp;</div>
	  </td>
	  <td nowrap align="left" colspan='2'>
      <div class=maintext><?php echo ($row['Type']=='Band')?'Sample':$row['Type'];?></div>
    </td>
	</tr>
  <?php if($theaction != 'add_new_single'){?>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>&nbsp;&nbsp;Project:&nbsp;</div>
	  </td>
	  <td nowrap align="left" colspan='2'>
      <div class=maintext><?php echo $project_id_name_arr[$row['ProjectID']];?></div>
    </td>
	</tr>
  <?php }?>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' valign=top>
	    <div class=maintext>Description:&nbsp;</div>
	  </td>
	  <td valign=top align="left" colspan='2'>
    <?php if($theaction == 'add_new_single'){?>  
        <div class=maintext><textarea name=frm_Description cols=70 rows=6></textarea></div>
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <div class=maintext><textarea name=frm_Description cols=70 rows=6><?php echo $row['Description']?></textarea></div>
    <?php }else{?>
        <div id='<?php echo $base_div_id.'_2'?>' class=maintext><?php echo  $row['Description'];?></div>
    <?php }?>
	  </td>
	</tr>
  <?php if($theaction != 'add_new_single'){?>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>Create by:&nbsp;</div>
	  </td>
	  <td nowrap align="left" colspan='2'>
      <div class=maintext><?php echo $user_name;?></div>
    </td>
	</tr>
  <?php }?>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>Abbreviation:&nbsp;</div>
	  </td>
	  <td nowrap align="left" colspan='2'>
<?php if($row['Type'] == 'Export'){
    if($theaction == 'add_new_single'){
      $row['Initial'] = get_last_v_num();
    }
    $tmp_Initial = "VS".$row['Initial'];
?>    
     <div id='<?php echo $base_div_id.'_3'?>' class=maintext><?php echo $tmp_Initial;?></div>
     <input type=hidden name=frm_Initial value='<?php echo $row['Initial']?>'>
     <input type="hidden" name="old_Initial" value="">
<?php         
  }else{
    if($theaction == 'add_new_single'){?>
      <div class=maintext><input type="text" name="frm_Initial" size="2" maxlength=2 value=""> Max. 2 letters</div>
      <input type="hidden" name="old_Initial" value="">
  <?php }elseif($theaction == 'modify_single_detail'){?>
      <div class=maintext><input type="text" name="frm_Initial" size="2" maxlength=2 value="<?php echo $row['Initial'];?>"> Max. 2 letters</div>
      <input type="hidden" name="old_Initial" value="<?php echo $row['Initial'];?>">
  <?php }else{?>
      <div id='<?php echo $base_div_id.'_3'?>' class=maintext><?php echo  $row['Initial'];?></div>
  <?php }
  }
  ?>         
	  </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" width='20%' nowrap>
	    <div class=maintext>Icon:&nbsp;</div>
	  </td>
    <td nowrap align="left" colspan='2'>
<?php if($row['Type'] == 'Export'){
?>
      <div style="float:left;width:13px;height:15px;border: red solid 0px;background-image:url('./gel_images/icon_star.gif');padding:2px 0px 0px 6px;font-size:x-small;font-weight:bold;">
        <?php echo $row['Initial']?>
      </div>
      <input type='hidden' name='frm_Icon' size='32'>
<?php }else{
    if($theaction == 'add_new_single' || $theaction == 'modify_single_detail'){?>
    <?php if($error_msg && $selected_type_div_id == $row['Type']){?>       
      <div id='<?php echo $row['Type']?>_error_msg'class=maintext><font color=#FF0000><?php echo $error_msg;?></font></div>
    <?php }?>
      <div class=maintext>   
      <input type='file' name='frm_Icon' size='32'>&nbsp;&nbsp;
      <br>Please only upload GIF,PNG or JPEG formatted and size 17x17 pixels image.<br>
      Click <a href='./download_icon_file.php'  title='download icon'>[here]</a> to download a photoshop template icon.
      </div>
  <?php }else{?>
      <div class=maintext id='<?php echo $base_div_id.'_4'?>'><?php echo $row['Icon']?></div>
  <?php }?>
<?php }?>
    </td>
  </tr>
  <?php if($theaction){?>
  <tr bgcolor="<?php echo $bgcolor;?>">	  
	  <td valign=top colspan=3 align=center>
    <?php if($theaction == 'add_new_single'){?>  
        <input type="button" value="Save" onClick="javascript: add_new(this.form);">&nbsp;
        <input type="reset" value="Reset">&nbsp;
        <input type="button" value="Close" onClick="javascript: close_add_new('<?php echo $row['Type']."_add_new"?>');">
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <?php if($row['Type'] == 'Export'){
            if($row['Icon'] == 'locked'){
              echo "<input type=\"button\" value=\"Unlock the expport version\" onClick=\"javascript: lock_export(this.form, 'No');\">\n";
            }else{
              echo "<input type=\"button\" value=\"Lock the expport version\" onClick=\"javascript: lock_export(this.form, 'Yes');\">\n";
            } 
        }
        if($row['Type'] == 'Export' and $row['Icon'] == 'locked'){
          //don't show save button
        }else{
        ?>
        <input type="button" value="Save" onClick="javascript: add_new(this.form);">
        <?php }?>
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

function create_types_div($group_type_arr){
?>
    <tr><td colspan=3><hr size=1></td></tr>
    <tr>
      <td width="400" align=center valign=top>
        <div class=maintext_bold>Protocols type</div>
        <div class=maintext>
        <select ID="frm_source_type" name="frm_source_type" size=4 multiple>
          <?php foreach($group_type_arr as $protocol_type){
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
    $results_1 = mysqli_query($DB_obj->link, $SQL_2);
    while($row = mysqli_fetch_assoc($results_1)){
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
function check_icon($frm_Icon){
  global $_FILES, $icon_folder, $frm_Name;
  $ret_arr = array('0','');
  $tmp_file_name = $_FILES['frm_Icon']['name'];
  $uploaded_file_type = $_FILES['frm_Icon']['type'];
  if(!strstr($uploaded_file_type,"jpeg") && !strstr($uploaded_file_type,"gif") && !strstr($uploaded_file_type,"png")){
    $ret_arr[1] = "The file type should be 'gif', 'png' or 'jpeg'";
    return $ret_arr;
  }
  
  if($frm_Icon){
    $uploaded_file_name = $frm_Icon;
  }else{
    $uploaded_file_name = "icon_".preg_replace('/[^A-Za-z0-9]/', '', $frm_Name). ".gif";
    $tmp_upload_file_name = basename($uploaded_file_name, ".gif");
  }
  
  $new_pic_name = $icon_folder ."/" . $uploaded_file_name;
  $tmp_name = $icon_folder ."/" . $tmp_upload_file_name;
  
  $tmp_counter = 0;
  while(file_exists($new_pic_name)){
    $tmp_counter++;
    $new_pic_name = $tmp_name."_".$tmp_counter.".gif";
    if($tmp_counter > 100) break;
  }
  
  $uploaded_file_name = basename($new_pic_name);
  
  if(!$fileAtrArr = getimagesize($_FILES['frm_Icon']['tmp_name'])){
    $ret_arr[1] = "Possible file upload attack! Please try again";
    return $ret_arr;
  }elseif($fileAtrArr[0] > 17 || $fileAtrArr[1] >17){
    $ret_arr[1] = "The image size is bigger than 17x17 pixels";
    return $ret_arr;
  }
  if(!move_uploaded_file($_FILES['frm_Icon']['tmp_name'], $new_pic_name)){
    if(_is_file($tmpFileName)) rename($tmpFileName,$previousFileFullName);
    $ret_arr[1] = "<font color=#FF0000>Possible file upload attack! Please try again";
    return $ret_arr;
  }else{
    if(_is_file($new_pic_name)){
      $ret_arr[0] = 1;
      $ret_arr[1] = $uploaded_file_name;
      return $ret_arr;
    }else{
      $ret_arr[1] = "Possible file upload attack! Please try again</font>";
      return $ret_arr;
    }
  }
}
function check_name_initial($frm_Name, $frm_Initial){
  global $HITSDB, $AccessProjectID;
  $SQL = "select ID from NoteType where Initial='$frm_Initial' and Name='$frm_Name' and ProjectID='$AccessProjectID'";
  if($HITSDB->fetch($SQL)){
    return false;
  }else{
    return true;
  }
}
function get_last_v_num(){
  global $AccessProjectID,$HITSDB;
  $version_num = 0;  
  $SQL = "SELECT Initial FROM NoteType WHERE Type='Export' AND ProjectID='$AccessProjectID'";
  $NoteInitArr = $HITSDB->fetchAll($SQL);
  foreach($NoteInitArr as $value){
    if(!is_numeric($value['Initial'])) continue;
    if($value['Initial'] > $version_num) $version_num = $value['Initial'];
  }
  return $frm_Initial = ++$version_num;
} 
?>           