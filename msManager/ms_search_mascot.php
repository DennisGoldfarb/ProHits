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
$msg = '';
$mascot_sessionID = '';
$SHOWHIDDENMODS = 0;



define ("MASCOT_FORM_DIR", "/mascot/"); 

set_include_path(get_include_path() . PATH_SEPARATOR . '../' );


include("./ms_permission.inc.php");
require("./common_functions.inc.php");
include("./autoSearch/auto_search_mascot.inc.php");
include ( "./is_dir_file.inc.php");

 
if(!MASCOT_IP){
  echo "<h2>Mascot is disabled in ProHits setting. </h2>";
  exit;
}

$USER = $_SESSION['USER'];

$dbstat_url = "http://" . MASCOT_IP . MASCOT_CGI_DIR ."/ProhitsMascotParser.pl?theaction=stat&dbName=";

if($frm_setName){
  $frm_setName = preg_replace("/ /", "_", trim($frm_setName));
  $frm_setName = preg_replace("/[^A-Za-z0-9_]/", "", $frm_setName);
}
if(isset($frm_setName) and trim($frm_setName) and $frm_myaction == 'yes' and $perm_modify){
  reset($_POST);
  $to_file = '';
  if(!array_key_exists('MODS', $_POST)) $_POST['MODS'] = 'y';
  if(!array_key_exists('IT_MODS', $_POST)) $_POST['IT_MODS'] = 'y';
  
  while (list($key, $val) = each($_POST)) {
    if(preg_match("/^frm_/",$key)) continue;
    
    if($key != 'SID' and $key != "MODS_hidden" and $key != "IT_MODS_hidden" and $key != "DB_hidden"){
      if(get_magic_quotes_gpc()) $$key = $val = stripslashes($val);
      if($key == 'MODS'){
          if($val){
            $tmp_arr = explode(";", $MODS_hidden);
            while(list($k, $v) = each($tmp_arr) ){
              if($v) $to_file .= "MODS=$v\n";
            }
          }else{
            $to_file .= "MODS=\n";
          }
      }else if($key == 'IT_MODS'){
          if($val){
            $tmp_arr = explode(";", $IT_MODS_hidden);
            while(list($k, $v) = each($tmp_arr) ){
              if($v) $to_file .= "IT_MODS=$v\n";
            }
          }else{
            $to_file .= "IT_MODS=\n";
          }
      }else if($key == 'DB'){
          if($val){
            $tmp_arr = explode(";", $DB_hidden);
            while(list($k, $v) = each($tmp_arr) ){
              if($v) $to_file .= "DB=$v\n";
            }
          }
      }else{
        $to_file .= "$key=$val\n";
      }
    }
  }//end while
  
  $frm_setID = search_para_add_modify('Mascot', $frm_setID, $frm_setName, $USER->ID, $frm_ProjectID, $to_file);
  if(!$frm_setID){
    $msg = "The name '$frm_setName' has been used. Please use other name";
    $frm_myaction = 'newSet';
  }
  $MODS = '';
  $IT_MODS = '';
  $IT_MODS_hidden = '';
  $MODS_hidden = '';
}

if($frm_myaction == 'yes' or !$frm_myaction){
  $frm_myaction = 'modifySet';
}
//get previous setting
$tmp_pro_str = ($USER->Type == 'Admin')?"": $pro_access_ID_str;
$set_arr = get_search_parameters('Mascot', 0, $tmp_pro_str);

if(!$frm_setID){
  if($set_arr){
    $frm_setID = $set_arr[0]['ID'];
  }
}

if($frm_setID and $frm_myaction != 'newSet'){
  $theSet_arr = get_search_parameters('Mascot', $frm_setID);
  $frm_setName = $theSet_arr['Name'];
  $frm_ProjectID = $theSet_arr['ProjectID'];
	$set_UserID = $theSet_arr['User'];
}
$MODS_arr = array();
$IT_MODS_arr = array();
$DB_arr = array();
$tmp_arr = array();

$SHOWHIDDENMODS = 0;

if($theSet_arr){
  $thePara_arr = explode("\n",$theSet_arr['Parameters']);
  $Mascot_User = get_userName($theSet_arr['User']);
  $Set_Date = $theSet_arr['Date'];
   
  if(isset($IT_MODS)) $IT_MODS='';
  if(isset($MODS)) $MODS='';
  if(isset($DB)) $DB='';
  foreach($thePara_arr as $str_tmp){
    $str_tmp = trim($str_tmp);
    if($str_tmp){
      $tmp_arr = explode('=',$str_tmp);
      if($tmp_arr[0] == 'MODS' or $tmp_arr[0] == 'IT_MODS' or $tmp_arr[0] == 'DB'){
        if($tmp_arr[0] == 'MODS') array_push($MODS_arr, $tmp_arr[1]);
        if($tmp_arr[0] == 'IT_MODS') array_push($IT_MODS_arr, $tmp_arr[1]);
        if($tmp_arr[0] == 'DB') array_push($DB_arr, $tmp_arr[1]);
        $$tmp_arr[0] .= $tmp_arr[1] . ";";
      }else{
         $$tmp_arr[0] = $tmp_arr[1];
      }
    }
  }
}

$th_color = "#368981";
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
<link rel="stylesheet" type="text/css" href="./ms_style.css">
<script type="text/javascript" src="./ms.js"></script>
<script type="text/javascript" src="../common/javascript/site_javascript.js"></script>
<title>ms data management</title>
</head>
<body background=./images/site_bg.gif bgcolor=#d3d3d3 onload="ToggleMods();"><center>
<table width=95% cellpadding=0 cellspacing=0 border=0>
<tr>
<td class=tb colspan=3><img src=./images/dot.gif width=1 height=1></td>
</tr>
</table>
<!----- containt ------------------------------------------------->
<table bgcolor=#ffffff width=95% cellpadding=0 cellspacing=0 border=0><tr>
<td class=tb width=1><img src=./images/dot.gif width=1 height=1></td>
<td align=right width=100%>
<center>

<script language=javascript>
//--------------------------------------------
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
function selected_dbs(){
  var theForm = document.mainSearch;
  if(DB_arr.length){
    for(var i=0; i<DB_arr.length; i++){
      for (j = 0; j < theForm.DB.length; j++){
        if(theForm.DB.options[j].text == DB_arr[i]){
          theForm.DB.options[j].selected = true;
        }
      }
    }
  }
}
function addOptionMods(){
  var theForm = document.mainSearch;
  if(MODS_arr.length){
    theForm.MODS.remove(0);
    for(var i=0; i<MODS_arr.length; i++){
      theForm.MODS.options[i] = new Option(MODS_arr[i]);
    }
  }
  if(IT_MODS_arr.length){
    theForm.IT_MODS.remove(0);
    for(var i=0; i<IT_MODS_arr.length; i++){
      theForm.IT_MODS.options[i] = new Option(IT_MODS_arr[i]);
    }
  }
}
function refreshMods(chk) {
  selectedMods = new Array(1);
  var index = 0; 
  if (theForm.MODS.options[0].text != "--- none selected ---") {
    for (i = 0; i < theForm.MODS.length; i++){
      selectedMods[index] = theForm.MODS.options[i].text;
      index++;
    }
  }
  if (theForm.IT_MODS.options[0].text != "--- none selected ---") {
    for (i = 0; i < theForm.IT_MODS.length; i++){
      selectedMods[index] = theForm.IT_MODS.options[i].text;
      index++;
    }
  }
  for (i = theForm.MASTER_MODS.options.length; i >= 0; i--){
    theForm.MASTER_MODS.remove(i);
  }
  if (chk.checked) {
    for (i = 0; i < allMods.length; i++){
      var str = allMods[i];
      str = str.replace(/&gt;/g, ">");
      str = str.replace(/&lt;/g, "<");
      var found = 0;
      if (index > 0) {
        for (j = 0; j < index; j++){
          if (selectedMods[j] == str) {
            found = 1;
            selectedMods[j] = "";
            break;
          }
        }
      }
      if (found == 0) {
        theForm.MASTER_MODS.options[theForm.MASTER_MODS.options.length] = new Option(str);
      }
    }
  } else {
    for (i = 0; i < mods.length; i++){
      var str = mods[i];
      str = str.replace(/&gt;/g, ">");
      str = str.replace(/&lt;/g, "<");
      var found = 0;
      if (index > 0) {
        for (j = 0; j < index; j++){
          if (selectedMods[j] == str) {
            found = 1;
            selectedMods[j] = "";
            break;
          }
        }
      }
      if (found == 0) {
        theForm.MASTER_MODS.options[theForm.MASTER_MODS.options.length] = new Option(str);
      }
    }
  }
  if (index > 0) {
    for (j = 0; j < index; j++){
      if (selectedMods[j] != "") {
        if (chk.checked) {
          alert("Modification " + selectedMods[j] + " is not in the current list of modifications");
        } else {
          // need to check full list before issuing warning
          for (i = 0; i < allMods.length; i++){
            var str = allMods[i];
            str = str.replace(/&gt;/g, ">");
            str = str.replace(/&lt;/g, "<");
            var found = 0;
            if (selectedMods[j] == str) {
              found = 1;
              selectedMods[j] = "";
              break;
            }
          }
          if (found == 0) {
            alert("Modification " + selectedMods[j] + " is not in the current list of modifications");
          }
        }
      }
    }
  }
  return true;
}
function checkForm(form){
  MODS_string(form.MODS, form.IT_MODS);
  DB_string(form.DB);
  if(form.DB_hidden.value == ''){
    alert("Please select database.");
    form.DB.select();
    return false;
  }
  if (!verify_FORMAT(form)){
    return false;
  }
  if(form.frm_setName.value<"                   ") {
    alert("Parameter name field is empty");
    return false;
  }
  if(form.USERNAME.value<"                   ") {
    alert("User name field is empty");
    form.USERNAME.select();
    return false;
  }
  if(form.USEREMAIL.type == 'text'){
    if(form.USEREMAIL.value <"                 " || 
       form.USEREMAIL.value.indexOf('@')  == -1  ||
       form.USEREMAIL.value.indexOf('@')  != form.USEREMAIL.value.lastIndexOf('@') ||
       form.USEREMAIL.value.indexOf('..') != -1  ||
       form.USEREMAIL.value.indexOf('.')  == 0   ||
       form.USEREMAIL.value.indexOf('@.') != -1  ||
       form.USEREMAIL.value.indexOf('@')  == 0   ||
       form.USEREMAIL.value.indexOf('.@') != -1) {
      alert("Please enter a valid email address");
      form.USEREMAIL.select();
      return false;
    }  
    if(form.USEREMAIL.value.indexOf('[')  != -1) {
      if(form.USEREMAIL.value.indexOf(']')  == -1  ||
        (form.USEREMAIL.value.indexOf(']') - form.USEREMAIL.value.indexOf('[')) < 8) {
        alert("Please enter a valid email address");
        form.USEREMAIL.select();
        return false;
      }
    } else {
      var lastPeriod =  form.USEREMAIL.value.lastIndexOf('.');
      if(form.USEREMAIL.value.charAt(lastPeriod+1) < "A"  ||
         form.USEREMAIL.value.charAt(lastPeriod+2) < "A"  ||
         form.USEREMAIL.value.charAt(lastPeriod+2) > "z"  ||
         form.USEREMAIL.value.charAt(lastPeriod+2) > "z") {
        alert("Please enter a valid email address");
        form.USEREMAIL.select();
        return false;
      }  
    }
  }
  var cookieName="userName";
  var cookieValue=form.USERNAME.value;
  register(cookieName,cookieValue);
  var cookieName="userEmail";
  var cookieValue=form.USEREMAIL.value;
  register(cookieName,cookieValue);
  form.frm_myaction.value = 'yes';
  form.submit();
}
function DB_string(this_one){
  var str_db = '';
  for (i=0; i<this_one.length; i++){
    if(this_one.options[i].selected){
      if(str_db != '') str_db += ";";
        str_db += this_one.options[i].text;
    }
  }
  this_one.form.DB_hidden.value = str_db;
  return true; 
}
function MODS_string(this_one,that_one){
  var str_mods = '';
  var str_it_mods = '';
  
  if(document.forms["mainSearch"].SHOWHIDDENMODS==undefined){
    for (i=0; i<this_one.length; i++){
      if (this_one.options[i].selected && that_one.options[i].selected){
        that_one.options[i].selected = false;
      }
      if(this_one.options[i].selected){
        if(str_mods != '') str_mods += ";";
        str_mods += this_one.options[i].text;
      }
      if(that_one.options[i].selected){
        if(str_it_mods != '') str_it_mods += ";";
        str_it_mods += that_one.options[i].text;
      }
    }
  }else{
    for (i=0; i<this_one.length; i++){
      if (this_one.options[i].text == "--- none selected ---"){
        break;
      }else{
        if(str_mods != '') str_mods += ";";
        str_mods += this_one.options[i].text;
      }
    }
    for (i=0; i<that_one.length; i++){
      if (that_one.options[i].text == "--- none selected ---"){
        break;
      }else{
        if(str_it_mods != '') str_it_mods += ";";
        str_it_mods += that_one.options[i].text;
      }
    }
  }  
  this_one.form.MODS_hidden.value = str_mods;
  this_one.form.IT_MODS_hidden.value = str_it_mods;
  return true;
}

function isNewSet(newSet){
  theForm = document.mainSearch;
  if(newSet){
    theForm.frm_myaction.value = 'newSet';
  }else{
    theForm.frm_myaction.value = 'modifySet';
  }
  theForm.submit();
}
function getdbstat(){
  theForm = document.mainSearch;
  dbsel = theForm.DB;
  dbName = dbsel.options[dbsel.selectedIndex].text;
  thewin = window.open('<?php echo $dbstat_url;?>'+dbName,"stat",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=650,height=500');  
  thewin.focus();
}
function ToggleMods(){
  if(typeof(body_onload) == 'function'){
    body_onload();
    return;
  }
  var theForm = document.forms["mainSearch"];
  selected_dbs();
  if(theForm.SHOWHIDDENMODS != undefined){
  <?php if($SHOWHIDDENMODS){?>
    theForm.SHOWHIDDENMODS.checked = true;
  <?php }else{?>
    theForm.SHOWHIDDENMODS.checked = false;
  <?php }?>    
    addOptionMods();
    refreshMods(theForm.SHOWHIDDENMODS);
  } 
}
function reset_form(){
  var theForm = document.forms["mainSearch"];
  theForm.reset();
  if(theForm.SHOWHIDDENMODS != undefined){  
    refreshMods(theForm.SHOWHIDDENMODS);
  }  
}

</script>
<form name=mainSearch method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=frm_myaction value='yes'> 
<input type=hidden name=MODS_hidden value=''>
<input type=hidden name=IT_MODS_hidden value=''>
<input type=hidden name=DB_hidden value=''>
<br>
<table border="0" cellpadding="0" cellspacing="2">
  <tr>
   <td><img src='./images/mascot.gif' border=0></td>
   <td><b><font color='red' face='helvetica,arial,futura' size='3'>Mascot Parameters</font></b><br>
     Create or modify Mascot search parameter set
  </td>
 </tr>
 <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
 </tr>
 <tr>
  <td colspan=2>
      
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
//$url_tmp = "http://www.matrixscience.com/cgi/search_form.pl?SEARCH=MIS";
//$url_tmp = "http://10.197.104.12/RawConverter/MatrixScienceMascot.html";
$fd = @fopen($url_tmp, "r");


ini_set('default_socket_timeout', $old);
if(!$fd) fatalError("Cannot open http://" . MASCOT_IP . MASCOT_CGI_DIR ."/search_form.pl. \nThis function needs local Mascot. If the local Mascot is running, please check MASCOT_IP in ../config/conf.inc.php", __LINE__);
$start_output = 0;
$remove_this_line = 0;
$tmp_name = 'tmp';
$DB_start = 0;

while($buffer = fgets($fd, 4075)){
  if( trim($buffer) == '<SCRIPT LANGUAGE="JavaScript">' or strstr($buffer, 'TYPE="hidden"') or trim($buffer) == '<script type="text/javascript">'){
    $start_output = 1;
  }else if(strstr($buffer, '</SCRIPT>')){
    echo $buffer;
    $start_output = 0;
  }else if(trim($buffer) == '</FORM>'){
    $start_output = 0;
    //break;
  }else if(strstr($buffer, 'Data file') ){
     //do not need upload file so move to next 6 lines version 2.3
     for($i = 0; $i < 6; $i++){
      $buffer = fgets($fd, 4075);
     }
     $buffer = str_replace('<TD ALIGN="RIGHT" NOWRAP>', '',$buffer);
  }else if(strstr($buffer, 'Data input') ){
     //do not need upload file so move to next 6 lines version 2.5
     for($i = 0; $i < 12; $i++){
      $buffer = fgets($fd, 4075);
      $buffer = "</td><td COLSPAN=3>&nbsp;</td></tr><tr><td colspan=4></td>";
     }
  }else if($start_output and strstr($buffer, 'img')){
    echo "<td colspan=4>&nbsp;</td>";
    continue;
  }
  
  if(strstr($buffer, 'TYPE="submit"') or strstr($buffer, ' VALUE="Reset')){
  //if(strstr($buffer, 'TYPE="submit"')){
    echo '</TD>';
    continue;
  }
  if($start_output and !$remove_this_line){
    //the line will be print out
    $buffer = str_replace('<A HREF="../', '<A target=_black HREF="http://'. MASCOT_IP. MASCOT_FORM_DIR, $buffer);
    $buffer = str_replace('<B>', '', $buffer);
    $buffer = str_replace('</B>', '', $buffer);
    $buffer = str_replace(' SELECTED', '', $buffer);
    if(preg_match('/NAME="([A-Z,a-z,0-9,_]+)"/', $buffer,$tmp_arr)){
      //get all field names
      $tmp_name = $tmp_arr[1];
    }
    if(isset($$tmp_name)){
      if(strstr($buffer, 'TYPE="text"')){
        $buffer = preg_replace('/VALUE="(|[A-Z,a-z,0-9,_,\.]+)"/', 'value="'.$$tmp_name.'"', $buffer);
      }else if(strstr($buffer,'TYPE="checkbox"')){
        $name_value = 'NAME="'. $tmp_name. '" VALUE="'. $$tmp_name .'"';
        $buffer = str_replace($name_value, $name_value. ' checked', $buffer);
      }else if(strstr($buffer, 'TYPE="radio"')){
        $buffer = str_replace(' CHECKED', '', $buffer);
        $name_value = 'VALUE="'. $$tmp_name. '" NAME="'. $tmp_name .'"';
        $buffer = str_replace($name_value, $name_value. ' checked', $buffer);
      }
    }
    //processing selection
    if(isset($$tmp_name)) {
      $op_value = $$tmp_name;
  	  if($tmp_name == 'MODS' or $tmp_name == 'IT_MODS'){
  	    $op_value_arr = explode(";", $$tmp_name);
  	  }
    }else{
      $op_value = 'not selected';
    }
	  if(strstr($buffer,'<OPTION>')){
       
      if(trim($buffer) == '<OPTION>'. $op_value.'</OPTION>'){
        $buffer = str_replace('<OPTION>', '<OPTION selected>', $buffer);
	    }else if(is_array($op_value_arr)){
	     //for MODS and IT_MODS
			  $buffer = str_replace("&gt;", ">", $buffer);
        $buffer = str_replace("&lt;", "<", $buffer);
	      if( in_array(preg_replace('/<OPTION>|<\/OPTION>/', '',trim($buffer)), $op_value_arr)){
		      $buffer = str_replace('<OPTION>', '<OPTION selected>', $buffer);
		    }
	    }
    }
    if(preg_match("/Name=\"DB\"/i", $buffer, $matches)){
      $DB_start = 1;
    }else  if($DB_start and preg_match("/\<\/SELECT\>/i", $buffer, $matches)){
      $buffer = preg_replace("/\>\</", "><a href='javascript: getdbstat()'>[db information]</a><", $buffer);
      $DB_start = 0;
    }
    echo $buffer;
  }
  $remove_this_line = 0;
} 
?>
  </td>
 </tr> 
 <tr>
   <td colspan=2><br><b><font color='red' face='helvetica,arial,futura' size='3'>Parameter Set</font></b>
   <?php if($perm_modify){?>&nbsp; &nbsp;
   New Set<input type=radio value=newSet name=frm_set onClick="isNewSet(true)" <?php echo ($frm_myaction == 'newSet')?'checked':'';?>>
   Modify Set<input type=radio value=modifySet name=frm_set onClick="isNewSet(false)" <?php echo ($frm_myaction == 'modifySet')?'checked':'';?>>
   <?php 
   }else{
     echo " &nbsp; You have no permission to change the setting.";
   }
   if($Mascot_User and $frm_myaction != 'newSet'){
       echo "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
       Set by: <b>".$Mascot_User."</b>&nbsp; &nbsp; Set date:<b>".$Set_Date."</b>\n"; 
   }
   if($msg){
    echo "<br><font color='red'>$msg</font>";
   }
   ?> 
  </td>
 </tr>
 <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
 </tr>
 <tr>
   <td colspan=2 align=center>
   <?php 
   if($frm_myaction == 'newSet'){
       echo '<b>New set name</b>: <input type=text size=10 name=frm_setName>';
   }else{
     if($set_arr){
       echo "<input type=hidden name=frm_setName value='$frm_setName'>\n";
       echo "<b>Set name</b>: <select name=frm_setID onChange=\"isNewSet(false)\">\n";
       foreach($set_arr as $tmpSet){
         $selected = ($tmpSet['ID'] == $frm_setID)?" selected":"";
         echo "<option value='" . $tmpSet['ID'] . "'$selected>".$tmpSet['Name']."\n";
       }
       echo "</select>\n";
       
     }else if($perm_insert){
       echo "<font color=\"#FF0000\">Please create new parameter set.</font>";
     }
   }
   echo " &nbsp; &nbsp; <b>for Project</b>: <select name=frm_ProjectID>\n";
   foreach($pro_access_ID_Names as $key => $value){
    $selected = ($key == $frm_ProjectID)?" selected":"";
    echo "<option value='" . $key . "'$selected>".$value."\n";
   }
   echo "</select>\n"; 
    ?>
   </td>
 </tr>
 <tr>
  <td colspan=2>
   <br><center>
   <?php 
   if($perm_modify and 
	 		(
			 (
					($USER->Type == 'Admin' or $USER->ID == $set_UserID) 
					and $set_UserID
				) 
				or $frm_myaction == 'newSet'
			) 
		){
    echo "&nbsp; &nbsp; &nbsp;  <input type=button value='Save' onClick=\"checkForm(this.form)\">\n";
   }
   ?>
   <input type=reset value='Reset' onclick="reset_form()">
   <input type="button" value="Close" onClick="window.close()">
   </center>
  </td>
  </tr>
</table>
</form> 
<?php 
include("./ms_footer_simple.php");
?>
