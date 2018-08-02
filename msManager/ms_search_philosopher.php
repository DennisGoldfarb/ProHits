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

$this_User = '';
$Set_Date = '';
$frm_setName = '';
$frm_setID = 0;
$set_UserID = '';
$frm_machine = '';
$frm_Description = '';
$msg = '';
$is_default = 0;
$is_SWATH = 0;


$set_arr = array();
$perm_modify = '';
$pars_file = '';
$default = 0;
$philosopher_cmd = 1;
$is_defult_set = '';


//--expectscore  only for XTandem;

//'output' and 'threads' '-h' and 'help' should be removed
$check_options['plsp_peptideprophet'] = "/^accmass$|^clevel \d$|^combine$|^decoy \w+$|^decoyprobs$|^exclude$|^expectscore$|^forcedistr$|^glyc$|^icat$|^instrwarn$|^leave$|^maldi$|^masswidth \d+([.]\d+)?$|^minpeplen \d+$|^minpintt \d+$|^minpiprob \d+([.]\d+)?$|^minprob[ ]\d+([.]\d+)?$|^minrtntt \d+$|^minrtprob \d+([.]\d+)?$|^neggamma$|^noicat$|^nomass$|^nonmc$|^nonparam$|^nontt$|^optimizefval$|^phospho$|^pi$|^ppm$|^rt$|^zero|^output \w+/";


$check_options['plsp_iprophet'] = "/^decoy \w+$|^length$|^minProb \d+([.]\d+)?$|^nofpkm$|^nonrs$|^nonse$|^nonsi$|^nonsm$|^nonsp$|^nonss$|^sharpnse$|^threads \d+/";
$check_options['plsp_proteinprophet'] = "/^accuracy$|^allpeps$|^confem$|^delude$|^excludezeros$|^fpkm$|^glyc$|^icat$|^instances$|^iprophet$|^logprobs$|^maxppmdiff \d+$|^minindep \d+$|^minprob \d+([.]\d+)?$|^mufactor \d+$|^nogroupwts$|^nonsp$|^nooccam$|^noprotlen$|^normprotlen$|^output \w+$|^protmw$|^softoccam$|^unmapped|^output \w+/";
 
$default_1 = $plsp_peptideprophet = '--minprob 0.05 --ppm --decoy DECOY --decoyprobs --nonparam';
$default_2 = $plsp_iprophet = '--nonsp --nonrs --nonsi --nonsm --nonse';
$default_3 = $plsp_proteinprophet = '--maxppmdiff 30';
 


$prohits_err_msg = '';
$perm_modify = false;
$perm_delete = false;
$perm_insert = false;

$parameter_file_folder = "./autoSearch/search_parameters";

include("./ms_permission.inc.php");
 
require("./common_functions.inc.php");
include ( "./is_dir_file.inc.php");
include ( "./tppTask/tpp_task_shell_fun.inc.php");
$help_file = PHILOSOPHER_BIN_PATH."/help.txt";


//echo "<pre>";print_r($request_arr);echo "</pre>";

$USER = $_SESSION['USER'];
if($USER->Type != 'Admin'){
  $perm_modify = false;
  $perm_delete = false;
  $perm_insert = false;
}else{
  $perm_modify = true;
  $perm_delete = true;
  $perm_insert = true;
}

if($frm_setName){
  $frm_setName = preg_replace("/ /", "_", trim($frm_setName));
  $frm_setName = preg_replace("/[^A-Za-z0-9_]/", "", $frm_setName);
}
if(isset($frm_setName) and trim($frm_setName) and $frm_myaction == 'yes' and $perm_modify){
  
  $plsp_peptideprophet = check_options($check_options['plsp_peptideprophet'], $plsp_peptideprophet, 'peptideprophet');
  $plsp_iprophet = check_options($check_options['plsp_iprophet'], $plsp_iprophet, 'iprophet');
  $plsp_proteinprophet = check_options($check_options['plsp_proteinprophet'], $plsp_proteinprophet, 'proteinprophet');
  if(!$prohits_err_msg){
    $para_str = "plsp_peptideprophet:$plsp_peptideprophet\n";
    $para_str .= "plsp_iprophet:$plsp_iprophet\n";
    $para_str .= "plsp_proteinprophet:$plsp_proteinprophet";
    $frm_setID = search_para_add_modify('TPP', $frm_setID, $frm_setName, $USER->ID, $para_str, $is_SWATH, $is_default, $frm_machine, $frm_Description);
    
    if(!$frm_setID){
      $prohits_err_msg = "The name '$frm_setName' has been used. Please use other name";
      $frm_myaction = 'newSet';
    } 
  }
}
if($frm_myaction == 'yes' or !$frm_myaction){
  $frm_myaction = 'modifySet';
}
$set_arr = get_search_parameters('TPP', 0 , '', '', 'All');
$set_arr = put_default_first($set_arr);
 
if(!$frm_setID){
  if($set_arr){
    $frm_setID = $set_arr[0]['ID'];
  }
}
if($frm_myaction == 'newSet'){
  $plsp_peptideprophet = $default_1;
  $plsp_iprophet = $default_2;
  $plsp_proteinprophet = $default_3;
  $frm_Description = '';
  $is_default = 0;
  $is_SWATH = 0;
}else if($frm_setID){ 
  $theSet_arr = get_search_parameters('TPP', $frm_setID);
   
  $frm_setName = $theSet_arr['Name'];
  $this_User = get_userName($theSet_arr['User']);
  $Set_Date = $theSet_arr['Date'];
  $set_UserID =  $theSet_arr['User'];
  $frm_machine = $theSet_arr['Machine'];
  $frm_Description = $theSet_arr['Description'];
  $is_default = $theSet_arr['Default'];
  $is_SWATH = $theSet_arr['SWATH'];
  
  if(strpos($theSet_arr['Parameters'], 'frm_')===0){
    $tmp_arr = createParameterString($theSet_arr['Parameters'], 'iProphet');
    $plsp_iprophet = $tmp_arr['peptideprophet'];
    
    $tmp_arr = createParameterString($theSet_arr['Parameters']);
    $plsp_peptideprophet = $tmp_arr['peptideprophet'];
    $plsp_proteinprophet = $tmp_arr['proteinprophet'];
  }else if(strpos($theSet_arr['Parameters'], 'plsp_')===0){
    $tmp_arr = explode("\n", $theSet_arr['Parameters']);
    foreach($tmp_arr as $line){
      if(!trim($line)) continue;
      $tmp_pare = explode(":", trim($line));
      if(count($tmp_pare)<1) continue;
      $$tmp_pare[0] = $tmp_pare[1];
    }
  }
  
}




include("./ms_header_simple.php");
?>
<SCRIPT LANGUAGE=JavaScript>
var pre_condition = '';
function isNewSet(theForm, newSet){
  if(newSet){
    theForm.frm_myaction.value = 'newSet';
  }else{
    theForm.frm_myaction.value = 'modifySet';
  }
  theForm.submit();
}
function checkForm(theForm){
  if(theForm.plsp_peptideprophet.value == ''){
     alert('Please enter peptide prophet options.');
     return false;
  }  
  if(theForm.frm_setName.value == ''){
    alert('Please enter the new set name.');
    return false;
  }
  theForm.frm_myaction.value = 'yes';
  theForm.submit();
}
 
function IsNumeric(sText){
  var ValidChars = "0123456789.";
  var IsNumber=true;
  var Char;
  if (sText.length == 0) return false;

  for (i = 0; i < sText.length && IsNumber == true; i++) { 
    Char = sText.charAt(i); 
    if (ValidChars.indexOf(Char) == -1) {
      IsNumber = false;
    }
 }
 return IsNumber;
}
 
</SCRIPT>
<form name=listform method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=frm_myaction value='yes'>
<table border="0" cellpadding="0" cellspacing="2" width=100% bgcolor=#5d5d5d>
<tr>
<td align=center>
<div class='divBoxPop'>

<table border="0" cellpadding="0" cellspacing="1" width=98%>
  <tr>
   <td><img src='./images/philosopher.gif' border=0></td>
   <td><span class="pop_header_text coHeader">Philosopher Parameters</span><br>
     Create or modify Philosopher parameter set <a onClick="newpopwin('../logs/log_view.php?display=all&log_file=<?php echo $help_file;?>',600,700)" class=button><img src='./images/help2.gif' border=0></a>
  </td>
 </tr>
 <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
 </tr>
 <tr>
    <td colspan=2 height=1> <font color="red"><?php echo $prohits_err_msg;?></font></td>
 </tr>
 <TR>
    <TD align=right valign=top bgcolor=#c7aa8d width=25%><font color="#FFFFFF"><b>Peptide prophet</b></font>&nbsp &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4>
    <?php 
    
    $plsp_peptideprophet = str_replace('"' , "&quot;", $plsp_peptideprophet);
    ?>
    <textarea cols="70" rows="3" name="plsp_peptideprophet"><?php echo $plsp_peptideprophet;?></textarea>
   
    </TD>
 </TR>
 <TR>
    <TD align=right valign=top bgcolor=#c7aa8d width=25%><font color="#FFFFFF"><b>iProphet</b></font>&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4>
    <textarea cols="70" rows="3" name="plsp_iprophet"><?php echo $plsp_iprophet;?></textarea>
    </TD>
 </TR>
 <TR>
    <TD align=right valign=top bgcolor=#c7aa8d width=25%><font color="#FFFFFF"><b>Protein Prophet</b></font>&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4>
    <textarea cols="70" rows="3" name="plsp_proteinprophet"><?php echo $plsp_proteinprophet;?></textarea>
    </TD>
 </TR>

</TABLE>
</div>
 
<div class='divBoxPop'> 
<table border="0" cellpadding="1" cellspacing="2" width=98%>
<TR>
   <td colspan=2><b><font color='red' face='helvetica,arial,futura' size='3'>Parameter Set</font></b>
   <?php if($perm_modify){?>&nbsp; &nbsp;
   New Set<input type=radio value=newSet name=frm_set onClick="isNewSet(this.form, true)" <?php echo ($frm_myaction == 'newSet')?'checked':'';?>>
   Modify Set<input type=radio value=modifySet name=frm_set onClick="isNewSet(this.form, false)" <?php echo ($frm_myaction == 'modifySet')?'checked':'';?>>
   <?php 
   }else{
     echo " &nbsp; You have no permission to change the setting.";
   }
   if($this_User and $frm_myaction != 'newSet'){
       echo "<br> 
       Set by: <b>".$this_User."</b>&nbsp; &nbsp; Set date:<b>".$Set_Date."</b>\n"; 
   }
   echo "<br><font color=#008000>Only Prohits administrator can change the setting</font>.<br>";
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
   <td><b>Set name</b></td>
<?php 
if($frm_myaction == 'newSet'){
   echo "
   <td> <input type=text size=20 name=frm_setName></td>";
    
}else{
   if($set_arr){
      echo "
      <td><input type=hidden name=frm_setName value='$frm_setName'>
      <select name=frm_setID onChange=\"isNewSet(this.form, false)\">\n";
      foreach($set_arr as $tmpSet){
        $selected = ($tmpSet['ID'] == $frm_setID)?" selected":"";
        $style_str = '';
        if($tmpSet['SWATH']){
          $style_str = " style='background-color: #CCCCCC;' ";
          if($tmpSet['Default']){
            $style_str = " style='background-color: #D6AD03;' ";
          }
        }else{
          if($tmpSet['Default']){
            $style_str = " style='background-color: yellow;' ";
          }
        }
        echo "<option value='" . $tmpSet['ID'] . "'$selected $style_str>".$tmpSet['Name']."\n";
      }
      echo "</select>
      </td>";
   }else if($perm_insert){
       echo "<td></td><font color=\"#FF0000\">Please create new parameter set.</font></td>";
     }
   }
?>
</tr>  
<tr>
   <td valign=top><b>Description</b></td> 
   <td>
   <textarea cols='60' rows='3' name='frm_Description'><?php echo $frm_Description;?></textarea>
   </td>
</tr>
<!--tr>
   <td><b>Machine</b></td> 
   <td>
   <select name=frm_machine>
<?php
   foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
    $selected = ($baseTable == $frm_machine)?" selected":"";
    echo "<option value='" . $baseTable . "'$selected>".$baseTable."\n";
   }
  
?>
   </select>
 </tr-->
 <tr>
   <td>
    &nbsp;
   </td>
   <td>
    Is default <input type="checkbox" name="is_default" value="1"<?php echo ($is_default)?" checked":"";?>> 
    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
    Is for SWATH file  <input type="checkbox" name="is_SWATH" value="1"<?php echo ($is_SWATH)?" checked":"";?>> 
   </td>
</tr>
  
 
<tr>
  <td colspan=2>
   <br><center>
   <?php 
	 if($perm_modify and $frm_myaction != 'show' and (($USER->Type == 'Admin' or $USER->ID == $set_UserID) or $frm_myaction == 'newSet'	)){
    echo "&nbsp; &nbsp; &nbsp;  <input type=button value='Save' onClick=\"checkForm(this.form)\">\n";
   }
   ?>
   <input type=reset value='Reset' name=reset>
   <input type="button" value="Close" onClick="window.close()">
   </center>
  </td>
</tr>
</table>
</div>
</td>
</tr>
</table>
</form>
<?php 
include("./ms_footer_simple.php");
function check_options($parttern, $frm_option_str, $field){
  global $prohits_err_msg; 
  $frm_option_str = preg_replace("/[ ]+/", " ", $frm_option_str);
  $frm_option_str = preg_replace("/--output \w+|--help|-h|--threads \d+/", "", $frm_option_str);
  $op_arr = preg_split("/--|-/", $frm_option_str);
  //echo $frm_option_str;
  //print_r($op_arr);exit;
  foreach($op_arr as $op){
    if(!$op)continue;
    if(!preg_match($parttern, trim($op), $matches)){
      $prohits_err_msg .= "The '$op' is not correct in '$field' field. ";
    } 
  }
  return $frm_option_str;
}
?>