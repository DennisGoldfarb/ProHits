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
$theID = '';
$theID_for_user = '';
$frm_userID = '';
$frm_myaction = '';
$frm_fixed_select_str = '';
$fixed_arr = array();

$frm_variable_select_str = '';
$variable_arr = array();

$frm_other_select_str = '';
$other_arr = array();

$frm_user_select_str = '';
$user_select_arr = array();
$tmp_user_select_str = '';

$theDefaultID = '';
 
include("./ms_permission.inc.php");
require("./common_functions.inc.php");
require("./is_dir_file.inc.php");

/*
echo "<pre>";
print_r($request_arr);
echo "</pre>";
*/

//record in SearchParameter Type=Modification. 
//User 0 for all user
//record with User ID only for the user.
//$theID and $theID_for_user are record ids.

$SQL = "SELECT `ID`, `Fname`,  `Lname` FROM `User` order by Fname";
$user_arr = $PROHITSDB->fetchAll($SQL);


$mod_parm_dir = "./autoSearch/";
$mascot_mod_array = read_mascot_mod_file($mod_parm_dir."mod_file");

if($frm_userID and $frm_myaction != 'saveDefault'){
  $SQL = "select ID, Name, User, ProjectID, Parameters from SearchParameter where Type='Modifications' and User='$frm_userID'";
  $user_Paras_arr = $managerDB->fetch($SQL);
  if($user_Paras_arr){
    $theID_for_user = $user_Paras_arr['ID'];
    $tmp_user_select_str = $user_Paras_arr['Parameters'];
  }
}

if($USER->Type == 'Admin'){
  if($frm_myaction == 'saveDefault'){
    
    $frm_userID = '';
    $thePara = "Fixed=".$frm_fixed_select_str."\n";
    $thePara .= "Variable=".$frm_variable_select_str."\n";
    $thePara .= "Other=".$frm_other_select_str;
      
    $sql_str = "SearchParameter set Name='default modifications', Type='Modifications', User='0', Parameters='$thePara', Date=now()";
    if($theID){
      $SQL = "update ". $sql_str . " where ID=$theID";
    }else{
      $SQL = "insert into ". $sql_str;
    } 
    $managerDB->insert($SQL);
  }else if($frm_myaction == 'saveUser' and $frm_userID){
    $tmp_user_select_str = $frm_user_select_str;
    $sql_str = "SearchParameter set Name='user modifications', Type='Modifications', User='$frm_userID', Parameters='$frm_user_select_str', Date=now()";
    if($theID_for_user){
      if(trim($frm_user_select_str)){
        $SQL = "update ". $sql_str . " where ID=$theID_for_user";
      }else{
        $SQL = "delete from SearchParameter where ID=$theID_for_user";
      }
    }else{
      $SQL = "insert into ". $sql_str;
    } 
    $managerDB->insert($SQL);
  }
}
if($tmp_user_select_str){
  $user_select_arr = explode(";;", $tmp_user_select_str);
}
$SQL = "select ID, Name, User, ProjectID, Parameters from SearchParameter where Type='Modifications' and User='0'";
$default_Paras_arr = $managerDB->fetch($SQL);
if($default_Paras_arr){
  $theID = $default_Paras_arr['ID'];
  $thePara = $default_Paras_arr['Parameters'];
  $lines = explode("\n", $thePara);
  foreach($lines as $line){
    if(strpos($line, "Fixed=") === 0){
      $line = str_replace("Fixed=", '', $line);
      $fixed_arr = explode(";;", $line);
    }else if(strpos($line, "Variable=") === 0){
      $line = str_replace("Variable=", '', $line);
      $variable_arr = explode(";;", $line);
    }else if(strpos($line, "Other=") === 0){
      $line = str_replace("Other=", '', $line);
      $other_arr = explode(";;", $line);
    }
  }
}
$default_arr = array_merge($fixed_arr, $variable_arr, $other_arr, $user_select_arr);
//print_r($default_arr);


include("./ms_header_simple.php");
 
?>
<style type="text/css">
.en{background-color:#4b9cd8; margin: 0 0;  padding: 4px 8px;}
.sel_wth{width:240px;}
.mod{background-color:#50c5a5; margin: 0 0; padding: 8px 20px; width: 80%;}
}
</style>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script> 
<script language=javascript>
function changeUser(sel){
  var theForm = sel.form;
  theForm.submit();
} 
function saveDefault(theForm){
  var sel_fixed = document.getElementById('frm_fixed_MODS');
  var sel_variable = document.getElementById('frm_variable_MODS');
  var sel_other = document.getElementById('frm_other_MODS');
  if(sel_other.length < 1){
    alert("'User can select modifications' cannot be empty.");
    return;
  }
  theForm.frm_other_select_str.value = all_option_to_str(sel_other);
  theForm.frm_fixed_select_str.value = all_option_to_str(sel_fixed);
  theForm.frm_variable_select_str.value = all_option_to_str(sel_variable);
  theForm.frm_myaction.value = 'saveDefault';
  theForm.submit();
}
function saveUser(theForm){
  var sel_user = document.getElementById('frm_user_MODS');
  theForm.frm_user_select_str.value = all_option_to_str(sel_user);
  theForm.frm_myaction.value = 'saveUser';
  theForm.submit();
}


 
</script>

<form name=modForm method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=frm_myaction value=''>
<input type=hidden name=frm_fixed_select_str value=''>
<input type=hidden name=frm_variable_select_str value=''>
<input type=hidden name=frm_other_select_str value=''>
<input type=hidden name=frm_user_select_str value=''>
<input type=hidden name=theID value='<?php echo $theID;?>'>

<table border="0" cellpadding="0" cellspacing="2" width=95%>
  <tr>
   
   <td clspan=2><span class="pop_header_text">Set Default Modifications
    
    </span>
    <br>
    <hr width="100%" size="1" noshade>
    The modification list is from file "/Prohits/autoSearch/mod_file" which originated from Uniprot and was converted to the Mascot style. 
    It can be manually modified.
   </td>
   </tr>
  
   <TR>
    <TD align=center bgcolor=#50c5a5 colspan=2><br>
      <table cellspacing="2" cellpadding="2" border="0" width=95%>
      <tr bgcolor="#dfe2f7" >
          <td rowspan="4" bgcolor="#ffffff">
          <b>All modifications:</b><br>
           <SELECT ID=frm_all_MODS NAME="frm_all_MODS" MULTIPLE SIZE=25>
          <?php 
          foreach($mascot_mod_array as $name=>$value){
            if(in_array($name, $default_arr)) {
             
              continue;
            }
            echo "<option value='$name'>$name</option>\n";
          }
          ?>
          </SELECT>
          </td>
          <td ><input type=button value='>>' onClick="add_option_to_selected('frm_all_MODS', 'frm_fixed_MODS')"><br>
              <input type=button value='<<' onClick="add_option_to_selected('frm_fixed_MODS', 'frm_all_MODS')"> </td>
          <td><b>Default fixed modifications</b>
          <SELECT class='sel_wth' ID=frm_fixed_MODS NAME="frm_fixed_MODS" MULTIPLE SIZE=3>
          <?php 
          foreach($fixed_arr as $value){
            echo "<option value='$value'>$value\n";
          }
          ?>
          </SELECT>
          </td>
          <td rowspan="3"><input type=button value='Save' onClick="saveDefault(this.form)"></td>
      </tr>
      <tr bgcolor="#dfe2f7" >
          <td ><input type=button value='>>' onClick="add_option_to_selected('frm_all_MODS', 'frm_variable_MODS')"><br>
              <input type=button value='<<' onClick="add_option_to_selected('frm_variable_MODS', 'frm_all_MODS')"> </td>
          <td><b>Default variable modifications</b>
          <SELECT class='sel_wth' ID=frm_variable_MODS NAME="frm_variable_MODS" MULTIPLE SIZE=3">
          <?php 
          foreach($variable_arr as $value){
            echo "<option value='$value'>$value\n";
          }
          ?>
          </SELECT>
          </td>
      </tr>
      <tr bgcolor="#dfe2f7" >
          <td ><input type=button value='>>' onClick="add_option_to_selected('frm_all_MODS', 'frm_other_MODS')"><br>
              <input type=button value='<<' onClick="add_option_to_selected('frm_other_MODS', 'frm_all_MODS')"> </td>
          <td><b>User selectable modifications</b>
          <SELECT class='sel_wth' ID=frm_other_MODS NAME="frm_other_MODS" MULTIPLE SIZE=9">
          <?php 
          foreach($other_arr as $value){
            echo "<option value='$value'>$value\n";
          }
          ?>
          </SELECT>
          </td>
      </tr>
      <tr bgcolor="#ababab" >
          <td ><input type=button value='>>' onClick="add_option_to_selected('frm_all_MODS', 'frm_user_MODS')"><br>
              <input type=button value='<<' onClick="add_option_to_selected('frm_user_MODS', 'frm_all_MODS')"> </td>
          <td><b>Modifications only for user</b>
          <SELECT NAME="frm_userID" onChange="changeUser(this)">
          <option value=''>-- --
          <?php 
          foreach($user_arr as $theUser){
            $is_selected = "";
            if($frm_userID and $frm_userID == $theUser['ID']){
              $is_selected = " selected";
            }
            echo "<option value=".$theUser['ID']."$is_selected>".$theUser['Fname']." ". $theUser['Lname']."\n";
          }
          ?>
          </SELECT>
          
          <SELECT class='sel_wth' ID=frm_user_MODS NAME="frm_user_MODS" MULTIPLE SIZE=3>
          <?php 
          foreach($user_select_arr as $value){
            echo "<option value='$value'>$value\n";
          }
          ?>
          </SELECT>
          
          </td>
          <td><input type=button value='Save' onClick="saveUser(this.form)"></td>
           
      </tr>
      </table>
      <br> 
    </TD> 
   <tr>
    
</table>
<input type="button" onclick="window.close()" value=" Close " name="frm_Task">   
</form>
<?php
include("./ms_footer_simple.php");

?>
