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
//Moscot---------------------------------------------------------
$frm_myaction = '';
$MODS = '';
$IT_MODS  = '';
$DB  = '';
$thePage = '';
$start_output = '';
$msg = '';
$ICAT = '';
$OVERVIEW = '';
$LCQ_PEAKS = '';
$op_value_arr = array();
$Mascot_User = '';
$Set_Date = '';
$set_arr = array();
$theSet_arr = array();
$set_UserID = '';
$frm_setName = '';
$frm_setID = 0;
$frm_ProjectID = 0;
$mascot_sessionID = '';
$SHOWHIDDENMODS = 0;
 
$logfile = '../logs/search.log';
//------------------------------------------------------
//GPM---------------------------------------------------
$ProhitsDate = '';
$GPM_User  = '';

$frm_form_obj_type_str = '';
$form_obj_type_arr = array();
$multiple_select_name_arr = array();
$frm_multiple_select_str = '';
$ProhitsUsekscore = '';
//-----------------------------------------------------------
//COMET------------------------------------------------------

$original_variable_mod_arr = array();
$frm_use_NL_ions = '';
$frm_enzyme = 1;
$frm_Semi_style_cleavage = '';
$frm_decoy = ''; 
$frm_Isotope_error = '';
$COMET_User  = '';
$frm_db = '';

$frm_Machine = '';
$frm_Description = '';
 
$is_default = 0;
$is_SWATH = 0;

//-------------------------------------------------------------
$MSGFDB_User  = '';
$frm_uniformAAProb = '';
$th_color = "#368981";
$general_title = "<span class=pop_header_text><font color='white'>Search Engine Parameter Set</font></span>";
$selected_SearchEngine = '';
$Element_name_arr = array();

include("./ms_permission.inc.php");
require("./common_functions.inc.php");
include("./autoSearch/auto_search_mascot.inc.php");
include ( "./is_dir_file.inc.php");
include("./autoSearch/msgfpl_parames.inc.php");
include("./autoSearch/msgfdb_parames.inc.php");
include("./classes/Ms_search_parameter_class.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

if(defined("MASCOT_IP") and MASCOT_IP){
  $search_Engine_arr = array('MASCOT','GPM','COMET','MSGFPL','MSGFDB','MSFRAGGER');
}else{
  $search_Engine_arr = array('GPM','COMET','MSGFPL','MSGFDB','MSFRAGGER');
}

$taskTables= $managerDB->list_tables();
if(!$frm_Machine){
  foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
    if(in_array($baseTable."SearchTasks", $taskTables)){
      $frm_Machine = $baseTable;
    }
  }
}

if(isset($Element_name_str)){
  $Element_name_arr = explode(",", $Element_name_str);
  $param_str = "database_name=;;multiple_select_str=;;";
  foreach($request_arr as $request_key => $request_val){
    if(preg_match("/frm_(.+)$/i", $request_key, $name_matchs)){
      $tmp_e_name = $name_matchs[1];
      if(in_array($tmp_e_name, $Element_name_arr)){
        $param_str .= $tmp_e_name."=".$request_val.";;";
      }
    }
  }
}
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
<link rel="stylesheet" type="text/css" href="./ms_style.css">
<script type="text/javascript" src="./ms.js"></script>
<script type="text/javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script> 
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>

<title>ms data management</title>
</head>
<body background=./images/site_bg.gif bgcolor=#d3d3d3">
<center>

<table bgcolor="white" width=850 cellpadding=0 cellspacing=0 border=0>
<tr>
<td class=tb width=1 colspan="3"><img src=./images/dot.gif width=1 height=1></td>
</tr>
<tr>
<td class=tb width=1><img src=./images/dot.gif width=1 height=1></td>
<td align=right width=100%><br>
<?php 
$USER = $_SESSION['USER'];
if($frm_setName){
  $frm_setName = preg_replace("/ /", "_", trim($frm_setName));
  $frm_setName = preg_replace("/[^A-Za-z0-9_]/", "", $frm_setName);
}
if(isset($frm_setName) and trim($frm_setName) and $USER->Type == 'Admin'){
  if($frm_myaction == 'yes'){
    if($SearchEngine == 'MASCOT'){//############
      reset($_POST);
      $to_file = '';
      while (list($key, $val) = each($_POST)) {
        if(preg_match("/^frm_/",$key)) continue;
        if(preg_match("/^table/",$key)) continue;
        if(preg_match("/^SearchEngine/",$key)) continue;
        if($key != 'SID'){
          if(get_magic_quotes_gpc()) $$key = $val = stripslashes($val);
          $to_file .= "$key=$val;;";
        }
      }//end while
      $to_file .= "SHOWALLMODS=;;";
      $to_file .= "MODS=;;";
      $to_file .= "IT_MODS=;;";
      $to_file .= "DB=;;";
      $frm_setID = search_para_add_modify($frm_Machine, $frm_setID, $frm_setName, $USER->ID, $to_file, $is_SWATH, $is_default, $frm_Machine, $frm_Description, $SearchEngine);
      if(!$frm_setID){
        $msg = "The name '$frm_setName' has been used. Please use other name";
        $frm_myaction = 'newSet';
      }
      $MODS = '';
      $IT_MODS = '';
      $IT_MODS_hidden = '';
      $MODS_hidden = '';
    }elseif($SearchEngine == 'GPM'){//#############
      $to_file = '';
      reset($_POST);
      $tmp_arr = explode(";", $frm_form_obj_type_str);
      foreach($tmp_arr as $tmp_str){
        $obj_arr = explode("=", $tmp_str);
        if(count($obj_arr) == 2 ){
          $form_obj_type_arr[$obj_arr[0]] = $obj_arr[1];
        }
      }
      $to_file .="frm_form_obj_type_str=$frm_form_obj_type_str;;";
//----------------------------------------------------------------------------
      $tmp_arr = explode("\n",$frm_multiple_select_str);
      $frm_multiple_select_str = "";
      foreach($tmp_arr as $key => $val){
        if(!trim($val)) continue;
        $tmp_arr2 = explode("=",$val);
        $frm_multiple_select_str .= trim($tmp_arr2[0])."=;;";
      }
      $to_file .= $frm_multiple_select_str;
//----------------------------------------------------------------------------
      while (list($key, $val) = each($_POST)) {
        if(preg_match("/^frm_/",$key)) continue;
        if(preg_match("/^table/",$key)) continue;
        if(preg_match("/^SearchEngine/",$key)) continue;
        if(isset($form_obj_type_arr[$key]) and $form_obj_type_arr[$key] == 'select_MULTIPLE'){
          continue;
        }
        //$val = str_replace("http://". $gpm_ip, '', addslashes($val));
        $val = str_replace("\r\n", ';;', $val);
        
        $to_file .= "$key=$val;;";
      }//end while
      $to_file .= "protein__taxon=;;";
      $to_file .= "residue__potential_modification_mass=;;";
      $to_file .= "residue__potential_modification_mass_select=;;";
      $to_file .= "residue__modification_mass_select=;;";
      $to_file .= "residue__modification_mass=;;";

      $to_file .= "refine=no;;";
      $to_file .= "refine__potential_N88terminus_modifications=+42.010565@[;;";
      $to_file .= "refine__maximum_valid_expectation_value=10;;";
      //$frm_setID = search_para_add_modify($table, $frm_setID, $frm_setName, $USER->ID, $frm_ProjectID, $to_file, $SearchEngine);
      $frm_setID = search_para_add_modify($frm_Machine, $frm_setID, $frm_setName, $USER->ID, $to_file, $is_SWATH, $is_default, $frm_Machine, $frm_Description, $SearchEngine);
      
      if(!$frm_setID){
        $msg = "The name '$frm_setName' has been used. Please use other name";
        $frm_myaction = 'newSet';
      }
    }elseif($SearchEngine == 'COMET' || $SearchEngine == 'MSGFPL' || $SearchEngine == 'MSGFDB' || $SearchEngine == 'MSFRAGGER'){
      //$frm_setID = search_para_add_modify($table, $frm_setID, $frm_setName, $USER->ID, $frm_ProjectID, $param_str, $SearchEngine);
      $frm_setID = search_para_add_modify($frm_Machine, $frm_setID, $frm_setName, $USER->ID, $param_str, $is_SWATH, $is_default, $frm_Machine, $frm_Description, $SearchEngine);
      
      if(!$frm_setID){
        $msg = "The name '$frm_setName' has been used. Please use other name";
        $frm_myaction = 'newSet';
      }
    }
  }elseif($frm_myaction == 'remove_parameter' && $frm_setID && $SearchEngine){
    remove_para($frm_setID,$SearchEngine);
  }
}

if($frm_myaction == 'yes' or $frm_myaction == 'remove_parameter' or!$frm_myaction){
  $frm_myaction = 'modifySet';
}

//$tmp_pro_str = ($USER->Type == 'Admin')?"": $pro_access_ID_str;
$set_arr = get_search_parameters($frm_Machine);

if(!$frm_setID){
  if($set_arr){
    $frm_setID = $set_arr[0]['ID'];
  }else{
    $frm_myaction = 'newSet';
  }
}

$allPara_arr = array();
if($frm_setID and $frm_myaction != 'newSet'){
  $theSet_arr = get_search_parameters($frm_Machine, $frm_setID);
  
  $tmp_para_arr = explode("\n",$theSet_arr['Parameters']);
  foreach($tmp_para_arr as $tmp_para_val){
    if(!trim($tmp_para_val)) continue;
    $tmp_para_arr2 = explode("===",$tmp_para_val);
    if(count($tmp_para_arr2) == 2){
      $allPara_arr[$tmp_para_arr2[0]] = $tmp_para_arr2[1];
    }
  } 
  $frm_setName = $theSet_arr['Name'];
  $frm_ProjectID = $theSet_arr['ProjectID'];
	$set_UserID = $theSet_arr['User'];
  $Mascot_User = get_userName($theSet_arr['User']);
  $Set_Date = $theSet_arr['Date'];
  $frm_Description = $theSet_arr['Description'];
   
}

$MODS_arr = array();
$IT_MODS_arr = array();
$DB_arr = array();
$tmp_arr = array();
$SHOWHIDDENMODS = 0;

?>
<center>
<script language=javascript>
//=====DB AND MODS========================================
var MODS_arr = new Array();
<?php foreach($MODS_arr as $MODS_val){?>
MODS_arr.push('<?php echo $MODS_val;?>');
<?php }?>
var IT_MODS_arr = new Array();
<?php foreach($IT_MODS_arr as $IT_MODS_val){?>
IT_MODS_arr.push('<?php echo $IT_MODS_val;?>');
<?php }?>
var DB_arr = new Array();
<?php foreach($DB_arr as $DB_val){?>
DB_arr.push('<?php echo $DB_val;?>');
<?php }?>
//======================================================
function pass_vars(fromForm, toForm){
  toForm.frm_Machine.value = fromForm.frm_Machine.value;
  var setname = fromForm.frm_setName.value;
  if(isEmptyStr(setname)){
    alert("Please type the set name!");
    return false;
  }
  if(!onlyAlphaNumerics(setname, 2)){
    alert("Only _, A-Z, a-z and 0-9 alowed");
    return false;
  }
  toForm.frm_setName.value = fromForm.frm_setName.value;
  toForm.frm_setID.value = fromForm.frm_setID.value;
  toForm.frm_Description.value = fromForm.frm_Description.value;
  var from_frm_set = fromForm.frm_set;
  for(var i=0; i<from_frm_set.length; i++){
    if(from_frm_set[i].checked){
      toForm.frm_set.value = from_frm_set[i].value;
      break;
    }
  }
  return true;
}

function check_moscot_form(){
  var theForm = document.MASCOT_para_frm;
  var mainForm = document.main_Parameter_frm;
  
  if(!pass_vars(mainForm, theForm)) return;
  
  var Moscot_paras = ''
  for(var i=0; i<theForm.length; i++){
    if(!theForm[i].name) continue;
    if(theForm[i].name.match(/frm/g)) continue;
    if(theForm[i].name.match(/SID/g)) continue;
    //if(theForm[i].name.match(/DB/g)) continue;
    if(theForm[i].name.match(/MODS/g)) continue;
    //if(theForm[i].name.match(/ERRORTOLERANT/g)) continue;
    //if(theForm[i].name.match(/DECOY/g)) continue;  
    if(theForm[i].type == 'radio'){
      if(!theForm[i].checked) continue
    }
    if(theForm[i].type == 'checkbox'){
      if(!theForm[i].checked) continue
    }
    if(Moscot_paras) Moscot_paras += ";;";
    Moscot_paras += theForm[i].name+"="+theForm[i].value;
    //alert(theForm[i].name+"="+theForm[i].value);
  }
  if(theForm.USERNAME.value<"                   ") {
    alert("User name field is empty");
    theForm.USERNAME.select();
    return false;
  }
  if(theForm.USEREMAIL.type == 'text'){
    if(theForm.USEREMAIL.value <"                 " || 
       theForm.USEREMAIL.value.indexOf('@')  == -1  ||
       theForm.USEREMAIL.value.indexOf('@')  != theForm.USEREMAIL.value.lastIndexOf('@') ||
       theForm.USEREMAIL.value.indexOf('..') != -1  ||
       theForm.USEREMAIL.value.indexOf('.')  == 0   ||
       theForm.USEREMAIL.value.indexOf('@.') != -1  ||
       theForm.USEREMAIL.value.indexOf('@')  == 0   ||
       theForm.USEREMAIL.value.indexOf('.@') != -1) {
      alert("Please enter a valid email address");
      theForm.USEREMAIL.select();
      return false;
    }  
    if(theForm.USEREMAIL.value.indexOf('[')  != -1) {
      if(theForm.USEREMAIL.value.indexOf(']')  == -1  ||
        (theForm.USEREMAIL.value.indexOf(']') - theForm.USEREMAIL.value.indexOf('[')) < 8) {
        alert("Please enter a valid email address");
        theForm.USEREMAIL.select();
        return false;
      }
    } else {
      var lastPeriod =  theForm.USEREMAIL.value.lastIndexOf('.');
      if(theForm.USEREMAIL.value.charAt(lastPeriod+1) < "A"  ||
         theForm.USEREMAIL.value.charAt(lastPeriod+2) < "A"  ||
         theForm.USEREMAIL.value.charAt(lastPeriod+2) > "z"  ||
         theForm.USEREMAIL.value.charAt(lastPeriod+2) > "z") {
        alert("Please enter a valid email address");
        theForm.USEREMAIL.select();
        return false;
      }  
    }
  }
  theForm.frm_myaction.value = 'yes';
  theForm.submit();
}

function check_GPM_form(theForm){
  var mainForm = document.main_Parameter_frm;
  if(!pass_vars(mainForm, theForm)) return;
  theForm.submit();
}

function check_form(theForm){
  var mainForm = document.main_Parameter_frm;
  if(!pass_vars(mainForm, theForm)) return;
  theForm.submit();
}

function remove_parameters(SearchE){
  if(SearchE == 'MASCOT'){
    theForm = document.MASCOT_para_frm;
  }else if(SearchE == 'GPM'){
    theForm = document.listform;
  }else if(SearchE == 'COMET'){
    theForm = document.COMET_para_frm;
  }else if(SearchE == 'MSGFPL'){
    theForm = document.MSGFPL_para_frm;
  }else if(SearchE == 'MSGFDB'){
    theForm = document.MS_GFDB_para_frm;
  }else if(SearchE == 'MSFRAGGER'){
    theForm = document.MSFRAGGER_para_frm;
  }
  if(!confirm("Are you sure you want to remove "+theForm.SearchEngine.value+" parameters")){
    return;
  }
  var mainForm = document.main_Parameter_frm;
  pass_vars(mainForm, theForm);
  theForm.frm_myaction.value = 'remove_parameter';
  theForm.submit();
}

function isNewSet(newSet){
  theForm = document.main_Parameter_frm;
  if(newSet){
    theForm.frm_myaction.value = 'newSet';
  }else{
    theForm.frm_myaction.value = 'modifySet';
  }
  theForm.submit();
}

function chang_machine(){
  theForm = document.main_Parameter_frm;
  theForm.frm_setName.value = '';
  theForm.frm_setID.value = '';
  theForm.submit();
}

function toggle_all(all_search_engine){
  var all_obj = document.getElementById(all_search_engine);
  var all_obj_inner_str = all_obj.innerHTML;
  if(all_obj_inner_str == '+'){ 
  <?php foreach($search_Engine_arr as $val){?> 
      var selected_obj = document.getElementById('<?php echo $val?>');
      var selected_a_id = '<?php echo $val?>' + '_a';
      var selected_a_obj = document.getElementById(selected_a_id);
      selected_obj.style.display = "block";
      selected_a_obj.innerHTML = '-';
  <?php }?>
    all_obj.innerHTML = '-'; 
  }else{
   <?php foreach($search_Engine_arr as $val){?> 
      var selected_obj = document.getElementById('<?php echo $val?>');
      var selected_a_id = '<?php echo $val?>' + '_a';
      var selected_a_obj = document.getElementById(selected_a_id);
      selected_obj.style.display = "none";
      selected_a_obj.innerHTML = '+';
  <?php }?>
    all_obj.innerHTML = '+'; 
  }
}
  
function toggle_detail(base_id){
  var selected_obj = document.getElementById(base_id);
  var selected_a_id = base_id + '_a';
  var selected_a_obj = document.getElementById(selected_a_id);
  var inner_str = trimString(selected_a_obj.innerHTML);  
  if(inner_str == '+'){
    selected_obj.style.display = "block";
    selected_a_obj.innerHTML = '-';
  }else{
    selected_obj.style.display = "none";
    selected_a_obj.innerHTML = '+';
  }
} 
</script>
<!---------main_Parameter_frm-------------------------------------------------------------------------------------------->
<table border="0" cellpadding="0" cellspacing="1" bgcolor="#949494">
<tr>
<td colspan=4 width=100%>
<div class="title-bar">
  <?php echo $general_title;?>
  <div style="float: right;padding: 0px 5px 0px 0px; border: #708090 0px solid;font-family: Georgia, Serif;">
    [<a id="all_search_Engine_a" href="javascript: toggle_all('all_search_Engine_a')" class="tipButton" title='all search engine parameters'>+</a>]
   </div>
</div>
</td>
</tr>
<form name=main_Parameter_frm method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=frm_myaction value=''>
<tr>
<td colspan=4 width=100%>
<div class="act_box">
<table border="0" cellpadding="0" cellspacing="2" width=100%> 
 <tr>
   <td colspan=2 width=50%>
 <?php if($USER->Type == 'Admin' && $set_arr){?>
   New Set<input type=radio value=newSet name=frm_set onClick="isNewSet(true)" <?php echo ($frm_myaction == 'newSet')?'checked':'';?>>
   Modify Set<input type=radio value=modifySet name=frm_set onClick="isNewSet(false)" <?php echo ($frm_myaction == 'modifySet')?'checked':'';?>>
   
 <?php }else{?>
     <input type=hidden value=newSet name=frm_set>
  
 <?php } ?>
    </td>
   <td colspan=2 width=50%>
 <?php
   if($Mascot_User and $frm_myaction != 'newSet'){
       echo "Set by: <b>".$Mascot_User."</b>&nbsp; &nbsp; Set date:<b>".$Set_Date."</b>\n"; 
   }
   if($msg){
    echo "<br><font color='red'>$msg</font>";
   }
   ?>
  </td>  
 </tr>

 <tr>
    <td colspan=4 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
 </tr>
 <tr>
   <td align=left><b>Set Name</b>: </td>
   <td>
 <?php 
   if($frm_myaction == 'newSet'){
       echo '<input type=text size=40 maxlength=100 name=frm_setName>';
       echo "<input type=hidden name=frm_setID value=''>\n";
   }else{
     if($set_arr){
       echo "<input type=hidden name=frm_setName value='$frm_setName'>\n";
       echo "<select id=frm_setID name=frm_setID onChange=\"isNewSet(false)\">\n";
       echo "<option value=''>\n";
       foreach($set_arr as $tmpSet){
         $selected = ($tmpSet['ID'] == $frm_setID)?" selected":"";
         echo "<option value='" . $tmpSet['ID'] . "'$selected>(".$tmpSet['ID'].") ".$tmpSet['Name']."\n";
       }
       echo "</select>\n";
       
     }else if($perm_insert){
       echo "<font color=\"#FF0000\">Please create new parameter set.</font>";
     }
   }
   ?>
   </td>
   <td align=right><b>for machine</b>: </td>
   <td align=left>&nbsp;&nbsp;
   <select name=frm_Machine onChange="chang_machine()">
 <?php foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
		  if(in_array($baseTable."SearchTasks", $taskTables)){
 ?>   
     <option value='<?php echo $baseTable?>' <?php echo (($baseTable == $frm_Machine)?" selected":"")?>><?php echo $baseTable?>
 <?php   }
   }?>
   </select>
   </td>
 </tr>
<tr>
   <td valign=top><b>Description: </b> 
   <td colspan=3> 
   <textarea cols='60' rows='3' name='frm_Description'><?php echo $frm_Description;?></textarea>
   </td>
</tr>
</form> 
</table>
</div>
</td>
</tr>
<?php 
//MASCOT %%%%%%%%%%%%%%
if(MASCOT_IP){
//only if mascot has set in conf file.
$thePara_arr = array();
$has_MASCOT_db = 0;
if(isset($allPara_arr['MASCOT'])){
  $has_MASCOT_db = 1;
  $thePara_arr = explode(";;",$allPara_arr['MASCOT']);
  foreach($thePara_arr as $str_tmp){
    $str_tmp = trim($str_tmp);
    if($str_tmp){
      $tmp_arr = explode('=',$str_tmp);
      $$tmp_arr[0] = $tmp_arr[1];
    }
  }
}
$test_file = "http://" . MASCOT_IP . MASCOT_CGI_DIR."/../help/search_field_help.html";
?>    
  <tr>
    <td colspan=4>
<?php 
$MASCOT_title_arr = array('SearchEngine' => 'MASCOT','aliase' => 'Mascot','logo' => './images/mascot.gif');
print_title($MASCOT_title_arr,$test_file,$has_MASCOT_db);
?> 
    </td>
  </tr>
  <tr>
    <td colspan=4>
<DIV id="MASCOT" class="contents" style="display:<?php echo (($selected_SearchEngine == 'MASCOT')?'block':'none')?>">
   <table border="0" cellpadding="0" cellspacing="0" width=100%>
    <form name=MASCOT_para_frm method=post action=<?php echo $PHP_SELF;?>>    
    <input type=hidden name=frm_myaction value='yes'>
    <input type=hidden name=frm_Machine value=''>
    <input type=hidden name=frm_setName value=''>
    <input type=hidden name=frm_set value=''>
    <input type=hidden name=frm_setID value=''>
    <input type=hidden name=frm_Description value=''>
    <input type=hidden name=SearchEngine value='MASCOT'>
    <input type=hidden name=frm_ProjectID value='0'>
   
    <tr>
     <td align="right" nowrap colspan=4>
    <?php if($USER->Type == 'Admin'){?>
       <input type=button value='Save' onClick="check_moscot_form(this.form)">
    <?php }?>
     &nbsp;&nbsp;</td>
    </tr>
    <tr>
      <td colspan=4 align="center">
<?php 
$mascot_sessionID = Mascot_session();
if($mascot_sessionID === true){
  $mascot_sessionID = '';
  //security is disabled
  if(MASCOT_USER){
		fatalError("Mascot security is not enabled. Please define(\"MASCOT_USER\", \"\") in ../config/conf.inc.php");
	}
}else if($mascot_sessionID === false){
	fatalError("Cannot connect http://" . MASCOT_IP . MASCOT_CGI_DIR ."/login.pl. Please check the Mascot setting in ../config/conf.inc.php");
}else if(!$mascot_sessionID){
	fatalError("Cannot login http://" . MASCOT_IP . MASCOT_CGI_DIR ."/login.pl. Please check the MASCOT_USER account in ../config/conf.inc.php");
}
$timeout = 15;
$old = ini_set('default_socket_timeout', $timeout);

$url_tmp = "http://" . MASCOT_IP . MASCOT_CGI_DIR ."/search_form.pl?sessionID=$mascot_sessionID&SEARCH=MIS";
//$url_tmp = "http://www.matrixscience.com/cgi/search_form.pl?FORMVER=2&SEARCH=MIS";
//$url_tmp = "http://10.197.104.12/RawConverter/MatrixScienceMascot.html";
$fd = @fopen($url_tmp, "r");

ini_set('default_socket_timeout', $old);
if(!$fd) fatalError("Cannot open http://" . MASCOT_IP . MASCOT_CGI_DIR ."/search_form.pl. \nThis function needs local Mascot. If the local Mascot is running, please check MASCOT_IP in ../config/conf.inc.php", __LINE__);

$start_taxonomy = 0;
$start_instrument = 0;

$SELECT_TAXONOMY_arr = array();
$SELECT_INSTRUMENT_arr = array();

while($buffer = fgets($fd, 4075)){
  if(!$SELECT_TAXONOMY_arr and strpos(strtoupper($buffer), 'NAME="TAXONOMY"')){
    $start_taxonomy = 1;
  }else  if($start_taxonomy){
    if(strpos(strtoupper($buffer), '/SELECT')){
      $start_taxonomy = 0;
    }else{
      $SELECT_TAXONOMY_arr[] = $buffer;
    }
  }else if(!$SELECT_INSTRUMENT_arr and strpos(strtoupper($buffer), 'NAME="INSTRUMENT"')){
    $start_instrument = 1;
  }else if($start_instrument){
    if(strpos(strtoupper($buffer), '/SELECT')){
      $start_instrument = 0;
    }else{
      $SELECT_INSTRUMENT_arr[] = $buffer;
    }
  } 
}   
    include("./Mascot_search_form.inc.php");
?>
    </td>
    </tr>
    </form>
    </table>
    </DIV>
   </td>
 </tr>
 
<?php 
}
//-end Moscot-------------------------------------------------------------------------------------------------------- ?>

<?php //-start GPM--------------------------------------------------------------------------------------------------------- ?>
<?php  
$thePara_arr = array();
$has_GPM_db = 0;
if(isset($allPara_arr['GPM'])){
  $has_GPM_db = 1;
  $thePara_arr = explode(";;",$allPara_arr['GPM']);
}
?>
<br>  
  <tr>
    <td colspan=4>
<?php 
$GPM_title_arr = array('SearchEngine' => 'GPM','aliase' => 'XTandem','logo' => './images/gpm.gif');
print_title($GPM_title_arr,'',$has_GPM_db);
?> 
    
    </td>
  </tr>
  <tr> 
  <td colspan=4>
<DIV id="GPM" class="contents" style="display:<?php echo (($selected_SearchEngine == 'GPM')?'block':'none')?>">
    <table border="0" cellpadding="0" cellspacing="0" width=100%>
    <form name=listform method=post action=<?php echo $PHP_SELF;?>>
    <input type=hidden name=frm_multiple_select_str value=''>
    <input type=hidden name=frm_myaction value='yes'>
    <input type=hidden name=frm_Machine value=''>
    <input type=hidden name=frm_setName value=''>
    <input type=hidden name=frm_set value=''>
    <input type=hidden name=frm_setID value=''>
    <input type=hidden name=frm_Description value=''>
    <input type=hidden name=SearchEngine value='GPM'>
    <input type=hidden name=frm_ProjectID value='0'>
  <tr>
    <td align="right" nowrap colspan=4>
      <?php if($USER->Type == 'Admin'){?>
         <input type=button value='Save' onClick="check_GPM_form(this.form)">
      <?php }?>
    </td>
  </tr>
  <tr>
   <td colspan=2>
   <font color="#008000">Database and modifications will be set from search task.</font>
   <TABLE WIDTH="100%" BORDER="0"><TR><TD><OL><li>   
<?php 
$multiple_select_name_arr = array();
$gpm_in_prohits = is_in_local_server('GPM');
if($gpm_in_prohits){
  $file = dirname(GPM_CGI_PATH)."/tandem/thegpm_tandem_a.html";
}
$gpm_form_dir = dirname(GPM_CGI_PATH)."/tandem/";
$fd = @fopen($file, "r");

if(!$fd) fatalError("Cannot open ". $file . "'.\nplease check ".GPM_CGI_PATH." in ../config/conf.inc.php", __LINE__);
$start_output = 0;
$remove_this_line = 1;
$tmp_name = 'tmp';
$support_raw = false;
$support_raw_checked = false;
if(stristr($RAW_FILES, 'RAW')) $support_raw = true;

$disabled_arr = array();

$modification_area_start = 0;
$gpmdb_area_start = 0;
$gpmdb_area_end = 0;

while($buffer = fgets($fd)){
  if($support_raw){
    if(strstr($buffer, 'name="lcq_para_str"'))$support_raw_checked = true;
  }
  if($remove_this_line and trim($buffer) == '<b>taxon</b><br>'){
    $remove_this_line = 0;
  }else if(!$remove_this_line and strstr($buffer, '</form>')){
    break;
  } 
  if(!$remove_this_line){
    $buffer = str_replace('<input type="submit" name="submit"','<input type="button" name="disabled"', $buffer);
    if(preg_match('/<script src="[0-9|a-z|A-Z]/', $buffer) ){
      $buffer = preg_replace('/<script src="/', '<script src="/tandem/methods/', $buffer);
    }
    $buffer = preg_replace('/src="\//', "src=\"http://". $gpm_ip. "/", $buffer);
    $buffer = str_replace('value="/tandem/methods/', 'value="http://' . $gpm_ip. '/tandem/methods/', $buffer);
    //remove modifications
    if(strpos($buffer, '4. protein modifications')){
      $modification_area_start = 1;
    }else if(strpos($buffer, '6. protein cleavage specification')){
      $modification_area_start = 0;
    }
    if($modification_area_start) continue;
    if(strpos($buffer, '<b>gpmdb</b>')){
      $gpmdb_area_start = 1;
    }else if($gpmdb_area_start and !$gpmdb_area_end and strpos($buffer, '</ol>')){
      
      $gpmdb_area_start = 0;
      $gpmdb_area_end = 1;
      continue;
    }
    if($gpmdb_area_start) continue;
    if(strstr($buffer, 'name="') ){
      $buffer = change_form_obj_name($buffer); 
    }
   
    if(preg_match('/name="(refine\w*)"/i', $buffer, $matches)){
      if($matches[1] != 'refine__spectrum_synthesis'){
        $buffer = preg_replace("/(name=\"refine.*\")/i", "$1 disabled ", $buffer);
        if(!in_array($matches[1], $disabled_arr)) $disabled_arr[] = $matches[1];
      }
    }elseif(preg_match('/name="(gpmdb__\w*?)"/i', $buffer, $matches)){
      $buffer = preg_replace('/(name=\"gpmdb__\w*?\")/i', "$1 disabled ", $buffer);
      if(!in_array($matches[1], $disabled_arr)) $disabled_arr[] = $matches[1];
    }elseif(preg_match('/name=\"(.*modification.*?)\"/i', $buffer, $matches)){
      $buffer = preg_replace("/(name=\".*modification.*?\")/i", "$1 disabled ", $buffer);
      if(!in_array($matches[1], $disabled_arr)) $disabled_arr[] = $matches[1];
    }elseif(preg_match('/name=\"(protein__taxon\d*?)\"/i', $buffer, $matches)){  
      $buffer = preg_replace("/(name=\"protein__taxon\d*?\")/i", "$1 disabled ", $buffer);
      if(!in_array($matches[1], $disabled_arr)) $disabled_arr[] = $matches[1];
    }elseif(0 and preg_match("/name=\"(scoring__include_reverse)\"/i", $buffer, $matches)){  
      $buffer = preg_replace("/(name=\"scoring__include_reverse?\")/i", "$1 disabled ", $buffer);
      if(!in_array($matches[1], $disabled_arr)) $disabled_arr[] = $matches[1];
    }elseif(0 and preg_match("/name=\"(protein__modified_residue_mass_file)\"/i", $buffer, $matches)){  
      $buffer = preg_replace("/(name=\"protein__modified_residue_mass_file?\")/i", "$1 disabled ", $buffer);
      if(!in_array($matches[1], $disabled_arr)) $disabled_arr[] = $matches[1];
    }elseif(preg_match("/name=\"(output__maximum_valid_expectation_value)?\"/i", $buffer, $matches)){   
      $buffer = preg_replace("/(name=\"output__maximum_valid_expectation_value?\")/i", "$1 disabled ", $buffer);
      if(!in_array($matches[1], $disabled_arr)) $disabled_arr[] = $matches[1];
    }
    $buffer = preg_replace("/(name=\"disabled\")/i", "$1 disabled ", $buffer);
    //name="protein__taxon"
    //name="protein__taxon1"
    //name="scoring__include_reverse"
    //name="protein__modified_residue_mass_file" 
    //name="output__maximum_valid_expectation_value"
    echo $buffer;
  }
  $buffer='';
}
$disabled_str = implode(",", $disabled_arr);
fclose($fd);
?>
<input type=hidden name=frm_disabled_str value='<?php echo $disabled_str?>'>
<script language='javascript'>
<?php 
if($frm_myaction == 'newSet'){
  $ProhitsUsekscore = 'yes';
}else{
  $ProhitsUsekscore = '';
}
echo "
function setValue(){
  theForm = document.listform;
  ";
if($theSet_arr){
  if($frm_form_obj_type_str){
    $tmp_arr = explode(";", $frm_form_obj_type_str);
    foreach($tmp_arr as $tmp_str){
      $obj_arr = explode("=", $tmp_str);
      if(count($obj_arr) == 2 and strtolower($obj_arr[1]) == "checkbox"){
         echo "\n  unsetDefaultceckbox(theForm.".$obj_arr[0].");";
      }
    }
  }
  $form_obj_type_arr = array();
  foreach($thePara_arr as $str_tmp){
    $str_tmp = trim($str_tmp);
    $tmp_arr = explode('=',$str_tmp);
    if(!$str_tmp or count($tmp_arr) <1 ) continue;
    if(preg_match('/^frm_form_obj_type_str=;(.+)/',$str_tmp, $matchs)){
      $tmp_obj_arr = explode(";", $matchs[1]);
      foreach($tmp_obj_arr as $tmp_str){
        $obj_arr = explode("=", $tmp_str);
        if(count($obj_arr) == 2 ){
          $form_obj_type_arr[$obj_arr[0]] = $obj_arr[1]; 
          if(strstr($obj_arr[1], 'select')){
            
            echo "\n  unsetDefaultSelected(theForm.".$obj_arr[0].");";
          }
        }
      }
    }else{
      if(isset($form_obj_type_arr[$tmp_arr[0]])){
        if(strstr($form_obj_type_arr[$tmp_arr[0]],'select') and strstr($tmp_arr[1], ":")){
          if(strstr($tmp_arr[1], $gpm_ip)){
            $tmp_mtp_arr[0] = $tmp_arr[1];
          }else{
            $tmp_mtp_arr = explode(":", $tmp_arr[1]);
          }
          for($i = 0; $i < count($tmp_mtp_arr) -1; $i++){
            echo "\n  setFormObjValue(theForm.".$tmp_arr[0].", 'select', '" . $tmp_mtp_arr[$i] . "');";
          }
        }else{
          if($tmp_arr[0] == 'lpdp') $tmp_arr[1] = 'http://' . $gpm_ip . $tmp_arr[1];
          echo "\n  setFormObjValue(theForm.".$tmp_arr[0].", '". $form_obj_type_arr[$tmp_arr[0]] ."', '" . $tmp_arr[1] . "');";
        }
      }else{
        $$tmp_arr[0] = $tmp_arr[1];
      }
    }
  }
}
echo "\n}\n";
?>
 </script>
  <a href='https://proteomics.fhcrc.org/CPAS/Project/Published%20Experiments/Tandem%20Pluggable%20Scoring/begin.view?' target=_black>
  <img src='./images/help.gif' border=0></a>
   <b>Use  k-score plug-in module</b><input type=checkbox name='ProhitsUsekscore' value=yes<?php echo ($ProhitsUsekscore)?' checked':'';?>>
  <input type=hidden name='frm_form_obj_type_str' value='<?php echo $frm_form_obj_type_str;?>'>
    </td>
  </tr>
  </form> 
  </table>
</DIV>
  </td>
  </tr> 
<script language='javascript'>
function getMultipleSelectStr(){
  theForm = document.listform;
  all_str = '';
<?php 
if($multiple_select_name_arr){
  foreach($multiple_select_name_arr as $value){
    echo "\n  all_str += '$value=' + catMultipleSelect(theForm.". $value . ')+"\n";';
  }
}
?>
  theForm.frm_multiple_select_str.value = all_str;
}
function catMultipleSelect(this_obj){
  if (this_obj === undefined) {
    return;
  }
  var str = '';
  for (i=0; i<this_obj.length; i++){
    if(this_obj.options[i].selected){
      str += this_obj.options[i].value + ":";
    }  
  }
  return str;
}
function unsetDefaultSelected(this_obj){
  if (this_obj === undefined) {
    return;
  }
  for (i=0; i < this_obj.length; i++) {
     this_obj[i].selected = false;
  }
}
function unsetDefaultceckbox(this_obj){
  if (this_obj === undefined) {
    return;
  }
  this_obj.checked = false;
}
function setFormObjValue(this_obj,theType, v){
  if (this_obj === undefined) {
    return;
  }
  if(theType == 'text' || theType == 'textarea'){
    this_obj.value = v;
  }else if(theType == 'select'){
    for (i=0; i<this_obj.length; i++){
      if(this_obj.options[i].value == v){
        this_obj.options[i].selected = true;
        return;
      }
    }
  }else if(theType == 'radio'){
    for (i=0; i < this_obj.length; i++) {
      if (this_obj[i].value == v){
         this_obj[i].checked = true;
      }
    }
  }else if(theType == 'checkbox'){
    this_obj.checked = true
  }
}
setValue();
</script>
<?php //-end GPM-------------------------------------------------------------------------------------------------------- ?>

<?php //-start Comet======================================================================================================= 
//$comet_in_prohits = is_in_local_server('COMET');
$comet_in_prohits = 1;
if($comet_in_prohits){
  $test_file = "./ms_pop_search_engine_parameters.php?checkSearchEngine=COMET";
}else{
  $test_file = "http://".$gpm_ip.add_folder_backslash(GPM_CGI_DIR)."Prohits_TPP.pl?tpp_myaction=testCOMET";
}
//---------------------------------------------------------------------------------------------------
 
$comet_parm_dir = "./autoSearch/";
$mascot_modifications_file = $comet_parm_dir."mod_file";
//echo "\$mascot_modifications_file=$mascot_modifications_file<br>";
$mascot_mod_array = read_mascot_mod_file($mascot_modifications_file); 
$default_comet_param_arr['comet']['CHARGE']= '';
$all_comet_param_arr = get_comet_default_param();
$default_comet_param_arr =$all_comet_param_arr['comet'];

if(!$default_comet_param_arr){
  echo "<b>Error</b>: cannot connect Comet server. Please report to Prohits administrator";exit;
}
$selected_MODS_arr = array();
//----------------------------------------------------------------------------------------------------
$thePara_arr = array();
$has_COMET_db = 0;

$file = "./autoSearch/search_parameters/COMET.html";
$td_label_color = '#8dc0a5';
$td_base_color = '#e4e4e4';

$COMET_html = new Ms_search_parameter($default_comet_param_arr,$file);

if(isset($allPara_arr['COMET'])){
  $has_COMET_db = 1;
  $thePara_arr = explode(";;",$allPara_arr['COMET']);
  $param_arr = get_comet_param($thePara_arr);
  $COMET_Element_arr = $param_arr['comet'];

  if(isset($param_arr['comet']['multiple_select_str'])){
    //$default_comet_param_arr['comet'] = $param_arr['comet'];    
    $tmp_multiple_select_arr = explode("&&", $param_arr['comet']['multiple_select_str']);
    
    foreach($tmp_multiple_select_arr as $tmp_multiple_select_val){
      $tmp_arr_1 = explode("|", $tmp_multiple_select_val);
      if(count($tmp_arr_1)==2){
        $selected_MODS_arr[$tmp_arr_1[0]] = explode("===", $tmp_arr_1[1]);
      }
    }
  }
}else{
   $COMET_Element_arr = array();
}

$tmp_db_arr = file("http://".$gpm_ip."/tandem/species.js");
if(!$tmp_db_arr) fatalError("Cannot open '$sequest_fasta_url'.\nIf the theSEQUEST is running, please check SEQUEST_IP in ../config/conf.inc.php.", __LINE__);

?>
  <tr>
    <td colspan=4>
<?php 
$COMET_title_arr = array('SearchEngine' => 'COMET','aliase' => 'Comet','logo' => './images/comet.gif');
print_title($COMET_title_arr,$test_file,$has_COMET_db);
?>         
    </td>
  </tr>
 <tr>
    <td colspan=4>
<DIV id="COMET" class="contents"  style="display:<?php echo (($selected_SearchEngine == 'COMET')?'block':'none')?>">
<?php 
//===============================================================================================================
/*echo "<pre>";
print_r($COMET_Element_arr);
echo "</pre>";*/
$COMET_html->set_elements($COMET_Element_arr);
$COMET_html->display_form($td_label_color,$td_base_color);
//===============================================================================================================
?>
</DIV>
    </td>
  </tr>
<?php //-end Comet-------------------------------------------------------------------------------------------------------- ?>

<?php //-start MSGFPL---------------------------------------------------------------------------------------------------------

if($gpm_in_prohits){
  $test_file = "./ms_pop_search_engine_parameters.php?checkSearchEngine=MSGFPL";
}else{
  $test_file = "http://".$gpm_ip.add_folder_backslash(GPM_CGI_DIR)."Prohits_TPP.pl?tpp_myaction=testMSGFPL";
}

$file = "./autoSearch/search_parameters/MSGFPL.html";
$td_label_color = '#8e98b3';
$td_base_color = '#e4e4e4';

$MSGFPL_html = new Ms_search_parameter($default_MSGFPL_param_arr,$file);

$thePara_arr = array();
$has_MSGFPL_db = 0; 
if(isset($allPara_arr['MSGFPL'])){
  $has_MSGFPL_db = 1;
  $thePara_arr = explode(";;",$allPara_arr['MSGFPL']);
  $param_arr = get_MSGFPL_param($thePara_arr);
  
  $MSGFPL_element_arr = $param_arr;
  $tmp_multiple_select_arr = explode("&&", $param_arr['multiple_select_str']);
  foreach($tmp_multiple_select_arr as $tmp_multiple_select_val){
    $tmp_arr_1 = explode("|", $tmp_multiple_select_val);
    if(count($tmp_arr_1)==2){
      $selected_MODS_arr[$tmp_arr_1[0]] = explode(":::", $tmp_arr_1[1]);
    }
  }
}else{
  $MSGFPL_element_arr = array();
}
$url = "http://".$gpm_ip."/tandem/species.js";
$tmp_db_arr = file($url);
if(!$tmp_db_arr) fatalError("Cannot open '$sequest_fasta_url'.\nIf the theSEQUEST is running, please check SEQUEST_IP in ../config/conf.inc.php.", __LINE__);

?>
  <tr>
    <td colspan=4>
<?php 
$MSGFPL_title_arr = array('SearchEngine' => 'MSGFPL','aliase' => 'MSGFPL','logo' => './images/msgfpl.gif');
print_title($MSGFPL_title_arr,$test_file,$has_MSGFPL_db);
?> 
    </td>
  </tr>
  <tr>
    <td colspan=2>
<DIV id="MSGFPL" class="contents" style="display:<?php echo (($selected_SearchEngine == 'MSGFPL')?'block':'none')?>">
<?php 
//========================================================================================================================
$MSGFPL_html->set_elements($MSGFPL_element_arr);
$MSGFPL_html->display_form($td_label_color,$td_base_color);
//=========================================================================================================================
?>
</DIV>
    </td>
  </tr>
<?php //-end MSGFPL-------------------------------------------------------------------------------------------------------- ?>

<?php //-start MS_GFDB-----------------------------------------------------------------------------------------------------
if($gpm_in_prohits){
  $test_file = "./ms_pop_search_engine_parameters.php?checkSearchEngine=MSGFDB";
}else{
  $test_file = "http://".$gpm_ip.add_folder_backslash(GPM_CGI_DIR)."Prohits_TPP.pl?tpp_myaction=testMSGFDB";
}
$thePara_arr = array();
$has_MSGFDB_db = 0;

$file = "./autoSearch/search_parameters/MS-GFDB.html";
$td_label_color = '#808040';
$td_base_color = '#e4e4e4';

$MSGFDB_html = new Ms_search_parameter($default_MSGFDB_param_arr,$file);

if(isset($allPara_arr['MSGFDB'])){
  $has_MSGFDB_db = 1;
  $thePara_arr = explode(";;",$allPara_arr['MSGFDB']);
  $param_arr = get_MSGFPL_param($thePara_arr);
  $MSGFDB_element_arr = $param_arr;
}else{
  $MSGFDB_element_arr = array();
}
$url = "http://".$gpm_ip."/tandem/species.js";
$tmp_db_arr = file($url);
if(!$tmp_db_arr) fatalError("Cannot open '$sequest_fasta_url'.\nIf the theSEQUEST is running, please check SEQUEST_IP in ../config/conf.inc.php.", __LINE__);
$MS_GFDB_bg_color = "#808040";

?>
  <tr>
    <td colspan=4>
<?php 
$MSGFDB_title_arr = array('SearchEngine' => 'MSGFDB','aliase' => 'MS-GFDB','logo' => './images/msgfdb.png');
print_title($MSGFDB_title_arr,$test_file,$has_MSGFDB_db);
?>  
    </td>
  </tr>
  <tr>
    <td colspan=2 align=center>
<DIV id="MSGFDB" class="contents" style="display:<?php echo (($selected_SearchEngine == 'MSGFDB')?'block':'none')?>">
<?php 
//========================================================================================================================
$MSGFDB_html->set_elements($MSGFDB_element_arr);
$MSGFDB_html->display_form($td_label_color,$td_base_color);
//========================================================================================================================
?>
</DIV>
<?php //-end MS-GFDB-------------------------------------------------------------------------------------------------------- ?>

<?php //-start MSFRAGGER-----------------------------------------------------------------------------------------------------"" 
if(defined('MSFRAGGER_BIN_PATH')){
  $test_file = "./ms_pop_search_engine_parameters.php?checkSearchEngine=MSFRAGGER";
  $thePara_arr = array();
  $has_MSFRAGGER_db = 0;
  
  $default_MSFRAGGER_param_arr = array();
  $MSFRAGGER_default_parm_dir = preg_replace("/\/$/", "", MSFRAGGER_BIN_PATH);
  if(_is_file($MSFRAGGER_default_parm_dir."/fragger.params")){
    $lines = file($MSFRAGGER_default_parm_dir."/fragger.params");
    $bridger_arr = get_comet_param($lines);
    $default_MSFRAGGER_param_arr = $bridger_arr['comet'];
  }
  
  $file = "./autoSearch/search_parameters/MSFRAGGER.html";
  $td_label_color = '#b97373'; 
  $td_base_color = '#e4e4e4';
  
  $MSFRAGGER_html = new Ms_search_parameter($default_MSFRAGGER_param_arr,$file);
  
  if(isset($allPara_arr['MSFRAGGER'])){
   $has_MSFRAGGER_db = 1;
   $thePara_arr = explode(";;",$allPara_arr['MSFRAGGER']);
   $param_arr = get_MSGFPL_param($thePara_arr);
   $MSFRAGGER_element_arr = $param_arr;
  }else{
   $MSFRAGGER_element_arr = array();
  }
  $url = "http://".$gpm_ip."/tandem/species.js";
  $tmp_db_arr = file($url);
  if(!$tmp_db_arr) fatalError("Cannot open '$sequest_fasta_url'.\nIf the theSEQUEST is running, please check SEQUEST_IP in ../config/conf.inc.php.", __LINE__);
  $MS_GFDB_bg_color = "#808040";
  
  ?>
    <tr>
      <td colspan=4>
  <?php 
  $MSFRAGGER_title_arr = array('SearchEngine' => 'MSFRAGGER','aliase' => 'MSFragger','logo' => './images/msfragger.gif');
  print_title($MSFRAGGER_title_arr,$test_file,$has_MSFRAGGER_db);
  ?>  
      </td>
    </tr>
    <tr>
      <td colspan=2 align=center>
  <DIV id="MSFRAGGER" class="contents" style="display:<?php echo (($selected_SearchEngine == 'MSFRAGGER')?'block':'none')?>">
  <?php 
  //========================================================================================================================
  $MSFRAGGER_html->set_elements($MSFRAGGER_element_arr);
  $MSFRAGGER_html->display_form($td_label_color,$td_base_color);
  //========================================================================================================================
  ?>
  </DIV>
<?php 
}//-end MSFRAGGER-------------------------------------------------------------------------------------------------------- ?>

</td>
</tr>
<tr height=10><td></td></tr>
</table>
<table>
<tr height=20><td></td></tr>
</table>
</td>
<td class=tb width=1><img src=./images/dot.gif width=1 height=1></td>
</tr>
</table>
<table width=850 cellpadding=0 cellspacing=0 border=0>
<tr bgcolor=<?php echo $th_color;?>>
<td class=tb width=1><img src=./images/dot.gif width=1 height=1></td>
<td width=100%> &nbsp; <span class=date><?php echo @date("F j, Y")?> </span></td>
<td class=tb width=1><img src=./images/dot.gif width=1 height=1></td>
</tr>
<tr>
<td class=tb colspan=3><img src=./images/dot.gif width=1 height=1></td>
</tr>
</table>
</center>
</body>
</html>
<?php 
////////////////////////////////////////
function change_form_obj_name($buffer){
////////////////////////////////////////
  global $frm_form_obj_type_str;
  global $multiple_select_name_arr;
  $rt = $buffer;
  if(preg_match_all('/<[^<^>]+>/', $rt, $matchs)){
    foreach($matchs[0] as $tmp_obj){
      if(preg_match('/name="([^"]+)"/', $tmp_obj, $machs_obj)){
        $old_name = $machs_obj[1];
        $new_name = preg_replace('/[, ]/', '_', $old_name);
        $new_name = str_replace('+', '99', $new_name);
        $new_name = str_replace('-', '88', $new_name);
        $new_tmp_obj = str_replace($old_name, $new_name, $tmp_obj);
        $rt = str_replace($tmp_obj, $new_tmp_obj, $rt);
        if(strstr($tmp_obj, 'input')){
          if(preg_match('/type="([a-zA-Z]+)"/', $tmp_obj, $matchs)){
            $frm_form_obj_type_str .= ";$new_name=". $matchs[1];
          }else{
            $frm_form_obj_type_str .= ";$new_name=text";
          }
        }else if(stristr($tmp_obj, '<select')){
          $frm_form_obj_type_str .= ";$new_name=select";
          if(stristr($tmp_obj, 'MULTIPLE')){
            array_push($multiple_select_name_arr, $new_name);
            $frm_form_obj_type_str .= "_MULTIPLE";
          }
        }else if(stristr($tmp_obj, '<textarea')){
          $frm_form_obj_type_str .= ";$new_name=textarea";
        }
      }
    }
  }
  return $rt;
}

function remove_para($ID,$SearchEngine){
  global $managerDB;
  $SearchEngine = strtoupper($SearchEngine);
  $SQL = "SELECT `Parameters` FROM `SearchParameter` WHERE `ID`='$ID'";
  $tmp_para_arr = $managerDB->fetch($SQL);  
  $tmp_para_arr2 = explode("\n",$tmp_para_arr['Parameters']);
  $para_str = '';
  foreach($tmp_para_arr2 as $key => $tmp_para_val2){
    if(!trim($tmp_para_val2)) continue;
    $tmp_para_arr3 = explode("===",$tmp_para_val2);
    if($tmp_para_arr3[0] == $SearchEngine) continue;
    if($para_str) $para_str .= "\n";
    $para_str .= $tmp_para_val2;
  }
  $SQL = "UPDATE `SearchParameter` SET  
          `Parameters`='".mysqli_real_escape_string($managerDB->link, $para_str)."'
          where ID='".$ID."'";
  $managerDB->update($SQL);
  return $ID;
}


function print_title($title_arr,$test_file='',$has_DB_data){
  global $selected_SearchEngine;
  global $USER;
  $SearchEngine = $title_arr['SearchEngine'];
?>   
  <div class="title">
      <div style="float: left;padding: 0px 0px 0px 2px;border:red 0px solid; width: 80px">
      <img src='<?php echo $title_arr['logo']?>' border=0>
      </div>
      <div style="float: left;padding: 0px 0px 0px 39px;border:red 0px solid;">
      <b><font color='red' face='helvetica,arial,futura' size='3'><?php echo $title_arr['aliase']?> Parameters</font></b><br>
      Create or modify <?php echo $title_arr['aliase']?> search parameter set
<?php if($test_file){?>
      <a onClick="newpopwin('<?php echo $test_file;?>',600,700)" class=button><img src='./images/help2.gif' border=0></a>
<?php }?>
      </div>
      
      <div style="float: right;padding: 0px 0px 0px 0px;font-family: Georgia, Serif; border:red 0px solid; min-width: 200px;">
        <span style="float: left;padding: 10px 5px 0px 0px;font-family: Georgia, Serif; border:red 0px solid;">
<?php   
      if($has_DB_data){
        if($USER->Type == 'Admin'){
?>
        <a href="javascript: remove_parameters('<?php echo $SearchEngine?>');" class=tipButton title='remove <?php echo $title_arr['aliase']?>  parameters'>
        <img src='./images/check_no.gif' border=1>
        </a>
<?php       }
      }else{
        echo "<font color='red'>not saved</font>";
      }
?>
        </span>
        <span style="float: right;padding: 10px 5px 0px 0px;font-family: Georgia, Serif; border:red 0px solid;">
        [<a id="<?php echo $SearchEngine?>_a" href="javascript: toggle_detail('<?php echo $SearchEngine?>')" class=tipButton title='<?php echo $title_arr['aliase']?>  parameters'><?php echo (($selected_SearchEngine == $SearchEngine)?'-':'+')?></a>]
        </span>
      </div>
  </div>  
<?php 
}
?>
