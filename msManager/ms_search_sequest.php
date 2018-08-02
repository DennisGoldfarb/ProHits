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
$ProhitsDate = '';
$set_arr = array();
$theSet_arr = array();
$frm_setName = '';
$frm_setID = 0;
$set_UserID = '';
$frm_ProjectID = 0;
$original_variable_mod_arr = array();

$SEQUEST_User  = '';
$msg = '';


//define ("GPM_FORM_DIR", "/tandem/"); 

include("./ms_permission.inc.php");
require("./common_functions.inc.php");
require("./is_dir_file.inc.php");

//$sequest_parm_dir = add_folder_backslash(STORAGE_FOLDER)."Prohits_Data/SequestParams/";
$sequest_parm_dir = "./autoSearch/";
$enzyme_modifications_file = $sequest_parm_dir."sequest_enzyme_modifications.txt";
$default_parms_file = $sequest_parm_dir."sequest.params.new";

$default_param_arr = get_default_param($default_parms_file);
$default_enzyme_mod_arr = get_default_param($enzyme_modifications_file);
if(isset($default_param_arr['sequest']['diff_search_options']['value'])){
  $original_variable_mod_arr = explode(" ", $default_param_arr['sequest']['diff_search_options']['value']);
}

if($frm_setName){
  $frm_setName = preg_replace("/ /", "_", trim($frm_setName));
  $frm_setName = preg_replace("/[^A-Za-z0-9_]/", "", $frm_setName);
}
if(isset($frm_setName) and trim($frm_setName) and $frm_myaction == 'yes' and $perm_modify){
  $to_file = '';
  reset ($_POST);
  if(!isset($_POST['frm_ion0'])) $_POST['frm_ion0'] = '0';
  if(!isset($_POST['frm_ion1'])) $_POST['frm_ion1'] = '0';
  if(!isset($_POST['frm_ion2'])) $_POST['frm_ion2'] = '0';
  
  //echo $frm_form_obj_type_str;
  if(isset($default_param_arr['sequest']['database_name'])){
    $default_param_arr['sequest']['database_name']['value'] = $_POST['frm_db'];
  }
  if(isset($default_param_arr['sequest']['first_database_name'])){
    $default_param_arr['sequest']['first_database_name']['value'] = $_POST['frm_db'];
  }
  if(isset($default_param_arr['sequest']['enzyme_info'])){
    $tmp_enzym_arr = explode(",", $_POST['frm_enzyme'], 2);
    $tmp_enzym_str = $tmp_enzym_arr[0].",". $_POST['frm_cleaves_at'].",".  $tmp_enzym_arr[1];
    $tmp_enzym_str = str_replace(",", " ", $tmp_enzym_str);
    $default_param_arr['sequest']['enzyme_info']['value'] = $tmp_enzym_str;
  }else if(isset($default_param_arr['sequest']['enzyme_number'])){
    if(!isset($default_param_arr['sequest_enzyme_info'])){
      echo "Error: [SEQUEST_ENZYME_INFO] is missing in sequest default file ($default_parms_file)";exit;
    }
    $found_enzyme = 0;
    foreach($default_param_arr['sequest_enzyme_info'] as $key=>$tmp_arr){
      $value = preg_replace("/;.+/", '', $tmp_arr['value']);
      $value = preg_replace("/[ ]+|\t+/", ",", trim($value));
      if($value == $_POST['frm_enzyme']){
        $default_param_arr['sequest']['enzyme_number']['value'] = $key;
        $found_enzyme = 1;
        break;
      }
    }
    if(!$found_enzyme){
      $default_param_arr['sequest']['enzyme_number']['value'] = 1;
      $default_param_arr['sequest_enzyme_info'][1]['value'] = $_POST['frm_enzyme'];
    }
  }
  $default_param_arr['sequest']['max_num_internal_cleavage_sites']['value'] = $_POST['frm_mis_cleavage'];
  if(isset($default_param_arr['sequest']['term_diff_search_options'])){
    if(!is_numeric($_POST['frm_c_term'])){
      $_POST['frm_c_term'] = '0.0000';
    }
    if(!is_numeric($_POST['frm_n_term'])){
      $_POST['frm_n_term'] = '0.0000';
    }
    $default_param_arr['sequest']['term_diff_search_options']['value'] =  $_POST['frm_c_term'] . " ". $_POST['frm_n_term'];
  }
  if(isset($default_param_arr['sequest']['peptide_mass_units'])){
    $default_param_arr['sequest']['peptide_mass_units']['value'] = $_POST['frm_peptide_mass_unit'];
  }
  $default_param_arr['sequest']['mass_type_parent']['value'] = $_POST['frm_MonoAvg_par'];
  $default_param_arr['sequest']['mass_type_fragment']['value'] = $_POST['frm_MonoAvg_frag'];
  
  $default_param_arr['sequest']['peptide_mass_tolerance']['value'] = $_POST['frm_pep_mass_tol'];
  
  $default_param_arr['sequest']['fragment_ion_tolerance']['value'] = $_POST['frm_frag_ion_tol'];
  $default_param_arr['sequest']['ion_series']['value'] = 
                                                $_POST['frm_ion0']. " ".
                                                $_POST['frm_ion1']. " ".
                                                $_POST['frm_ion2']. " ".
                                                $_POST['frm_ion3']. " ".
                                                $_POST['frm_ion4']. " ".
                                                $_POST['frm_ion5']. " ".
                                                $_POST['frm_ion6']. " ".
                                                $_POST['frm_ion7']. " ".
                                                $_POST['frm_ion8']. " ".
                                                $_POST['frm_ion9']. " ".
                                                $_POST['frm_ion10']. " ".
                                                $_POST['frm_ion11']. " ";
                                                
                                                
  $to_file_frm_variable_MODS = '';
  $num_variable_MODS = 0;
  $tmp_mod_arr = explode("\n", $_POST['frm_multiple_select_str'], 2);
  foreach($tmp_mod_arr as $value){
    $value = preg_replace("/ |\t/", '', $value);
    $tmp_arr = explode("=", $value);
    $all_mod_arr = explode(":",$tmp_arr[1]);
     
    foreach($all_mod_arr as $tmp_value){
      $tmp_value = trim($tmp_value);
      if(!$tmp_value)continue; 
      $one_mod_arr = explode(",",$tmp_value);
      if($tmp_arr[0] == 'frm_variable_MODS'){
        $num_variable_MODS++;
        if($to_file_frm_variable_MODS) $to_file_frm_variable_MODS .= " ";
        $to_file_frm_variable_MODS .= $one_mod_arr[2]." ".$one_mod_arr[1];
      }else if($tmp_arr[0] == 'frm_fixed_MODS'){
        $chars_arr = preg_split('//', strtoupper($one_mod_arr[1]));
        foreach($chars_arr as $theChar){
          if(!$theChar)continue;
          $found_char = 0;
          foreach($default_param_arr['sequest'] as $key=>$value){
            if(preg_match("/^add_".$theChar."_/", $key, $matches)){
              $default_param_arr['sequest'][$key]['value'] = $one_mod_arr[2];
              $found_char = 1;
            }
          }
          //if(!$found_char){
          //  $default_param_arr['sequest'][$key]['value'] = '0.0000';
          //}
        }
      }
    }
  }
  if($num_variable_MODS < 6 ) 
    $to_file_frm_variable_MODS .= str_repeat(" 0.0000 X", 6-$num_variable_MODS);
  $default_param_arr['sequest']['diff_search_options']['value'] = trim($to_file_frm_variable_MODS);
  if(!is_numeric($_POST['frm_c_fix_term'])){
    $_POST['frm_c_fix_term'] = '0.0000';
  }
  if(!is_numeric($_POST['frm_n_fix_term'])){
    $_POST['frm_n_fix_term'] = '0.0000';
  }
  if(isset($default_param_arr['sequest']['add_Cterm_peptide'])){
    $default_param_arr['sequest']['add_Cterm_peptide']['value'] = $_POST['frm_c_fix_term'];
    $default_param_arr['sequest']['add_Nterm_peptide']['value'] = $_POST['frm_n_fix_term'];
  }else if(isset($default_param_arr['sequest']['add_C_terminus']['value'])){
    $default_param_arr['sequest']['add_C_terminus']['value'] = $_POST['frm_c_fix_term'];
    $default_param_arr['sequest']['add_N_terminus']['value'] = $_POST['frm_n_fix_term'];
  }
  
  $to_file = "[SEQUEST]\n";
  $add_started = 0;
  foreach($default_param_arr['sequest'] as $key=>$var_arr){
    $tmp_to_file = $key . " = " . $var_arr['value'];
    if($default_param_arr['sequest'][$key]['desc']){
      $num = 50-strlen($tmp_to_file);
      if($num > 0) $tmp_to_file .= str_repeat(" ", $num);
      $tmp_to_file .= ";".$default_param_arr['sequest'][$key]['desc'];
    }
    if(!$add_started and preg_match("/^add_/", $key)){
      $add_started = 1;
      $tmp_to_file = "\n". $tmp_to_file;
    }
    $to_file .= $tmp_to_file. "\n";
  }
  if(isset($default_param_arr['sequest_enzyme_info'])){
    $to_file .= "\n[SEQUEST_ENZYME_INFO]\n";
    
    foreach($default_param_arr['sequest_enzyme_info'] as $key=>$tmp_arr){
      $key_spaces = ($key < 10)?"  ":" "; 
      $str_arr = explode(",", $tmp_arr['value']);
      if(count($str_arr) == 4){
        $to_file .= $key.".".$key_spaces. $str_arr[0]. str_repeat(" ", 23-strlen($str_arr[0]));
        $to_file .= $str_arr[1]. str_repeat(" ", 7-strlen($str_arr[1]));
        $to_file .= $str_arr[2]. str_repeat(" ", 12-strlen($str_arr[2]));
        $to_file .= $str_arr[3]."\n";
      }
    }
  }
  //print "<pre>$to_file</pre>";
  $frm_setID = search_para_add_modify('SEQUEST', $frm_setID, $frm_setName, $USER->ID, $frm_ProjectID, $to_file);
  if(!$frm_setID){
    $msg = "The name '$frm_setName' has been used. Please use other name";
    $frm_myaction = 'newSet';
  }
}
if($frm_myaction == 'yes' or !$frm_myaction){
  $frm_myaction = 'modifySet';
}
$tmp_pro_str = ($USER->Type == 'Admin')?"": $pro_access_ID_str;

$set_arr = get_search_parameters('SEQUEST', 0, $tmp_pro_str);

if($frm_setID and $frm_myaction != 'newSet'){
  $theSet_arr = get_search_parameters('SEQUEST', $frm_setID);
  $frm_setName = $theSet_arr['Name'];
  $frm_ProjectID = $theSet_arr['ProjectID'];
	$set_UserID = $theSet_arr['User'];
}
if($theSet_arr){
  $thePara_arr = explode("\n",$theSet_arr['Parameters']);
  $SEQUEST_User = get_userName($theSet_arr['User']);
  $Set_Date = $theSet_arr['Date'];
  $param_arr = get_default_param($theSet_arr['Parameters']);
  if(isset($param_arr['sequest'])){
    $default_param_arr['sequest'] = $param_arr['sequest'];
  }
}

include("./ms_header_simple.php");

$sequest_fasta_url = "http://".SEQUEST_IP . "/Prohits_SEQUEST/Prohits_SEQUEST.pl?SEQUEST_myaction=listFasta";
//echo $sequest_fasta_url;exit;
$sequest_fasta_files = file_get_contents($sequest_fasta_url);
if(!$sequest_fasta_files) fatalError("Cannot open '$sequest_fasta_url'.\nIf the theSEQUEST is running, please check SEQUEST_IP in ../config/conf.inc.php.", __LINE__);
 
?>
<script language=javascript>
var selected_var_mod_ok = true;
var max_var_in_defult_file = 0;
function isNewSet(newSet){
  theForm = document.listform;
  if(newSet){
    theForm.frm_myaction.value = 'newSet';
  }else{
    theForm.frm_myaction.value = 'modifySet';
  }
  theForm.submit();
}
function checkForm(form){
  if(form.frm_setName.value<"                   ") {
    alert("set name field is empty");
    return false;
  }
  
<?php 
  if(!isset($default_param_arr['sequest']['peptide_mass_units'])){
?>
  var sel = form.frm_peptide_mass_unit;
  if(sel.options[sel.selectedIndex].value != '0'){
     alert ("The default file has no 'peptide_mass_units'. You have to select 'amu'.");
     return;
  }
<?php }?>
  getMultipleSelectStr();
  if(selected_var_mod_ok){
    form.submit();
  }else{
    alert('The default parameter file sets maximum variable modifications are '+max_var_in_defult_file +".");
  }
  
}
function getMultipleSelectStr(){
  theForm = document.listform;
  all_str = '';
  all_str += 'frm_variable_MODS=' + catMultipleSelect(theForm.frm_variable_MODS)+"\n";
  all_str += 'frm_fixed_MODS=' + catMultipleSelect(theForm.frm_fixed_MODS);
  theForm.frm_multiple_select_str.value = all_str;
}
function catMultipleSelect(this_obj){
  
  var str = '';
  var num = 0;
  for (i=0; i<this_obj.length; i++){
    if(this_obj.options[i].selected){
      str += this_obj.options[i].value + ":";
      num++;
    }  
  }
  if(this_obj.name == 'frm_variable_MODS'){
    max_var_in_defult_file = max_variable_in_default();
    if(num > max_var_in_defult_file){
      selected_var_mod_ok = false;
    }
  }
  return str;
}
</script>
<form name=listform method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=frm_myaction value='yes'>
<input type=hidden name=frm_multiple_select_str value=''>
<table border="0" cellpadding="0" cellspacing="2">
  <tr>
   <td><img src='./images/sequest.gif' border=0></td>
   <td><b><font color='red' face='helvetica,arial,futura' size='3'>SEQUEST Parameters</font></b><br>
     Create or modify SEQUEST search parameter set
  </td>
 </tr>
 <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
 </tr>
 <tr>
  <td colspan=2 valign=top>
  <TABLE width=650>
   <TR>
    <TD colspan=2 bgcolor=white>
      <b>Database & Enzyme</b> 
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#d3d3eb width=30%><b>Database</b>:&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> 
    <select name=frm_db>
    <script language=javascript>
    <?php echo $sequest_fasta_files;?>
    </script>
    </select>
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#d3d3eb><b>Enzyme</b>:&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> 
    <select name=frm_enzyme>
    <?php 
    $frm_cleaves_at = 1;
    $frm_enzyme = '';
    if(isset($default_param_arr['sequest']['enzyme_info'])){
      $tmm_enz_arr = explode(" ", $default_param_arr['sequest']['enzyme_info']['value'], 3);
      $frm_enzyme = $tmm_enz_arr[0].",". preg_replace("/ /", ",", $tmm_enz_arr[2]);
      $frm_cleaves_at = $tmm_enz_arr[1];
    }else if(isset($default_param_arr['sequest_enzyme_info']) and isset($default_param_arr['sequest']['enzyme_number']['value'])){
       
      $the_index = (int)$default_param_arr['sequest']['enzyme_number']['value'];
      $frm_enzyme = $default_param_arr['sequest_enzyme_info'][$the_index]['value'];
    }
    foreach($default_enzyme_mod_arr['enzyme'] as $tmp_ens){
      $selected='';
      $tmp_ens_name = preg_replace("/,.+/",'', $tmp_ens);
      if($frm_enzyme == $tmp_ens) $selected = ' selected';
      echo "<option value='$tmp_ens'$selected>$tmp_ens_name\n";
    }
    ?>
    </select>
     <b>Cleaves At:</b> 
    <select name="frm_cleaves_at">
      <option value="1"<?php echo ($frm_cleaves_at == 1)?" selected":"";?>>Both Ends
      <option value="2"<?php echo ($frm_cleaves_at == 2)?" selected":"";?>>Either Ends
      <option value="3"<?php echo ($frm_cleaves_at == 3)?" selected":"";?>>Nterm Only
      <option value="4"<?php echo ($frm_cleaves_at == 4)?" selected":"";?>>Cterm Only
    </select>  
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#d3d3eb><b>Max missed cleavage:</b>:&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> 
    <SELECT name=frm_mis_cleavage>
      <OPTION VALUE=0<?php echo ($default_param_arr['sequest']['max_num_internal_cleavage_sites']['value']=='0')?" selected":"";?>>0
      <OPTION VALUE=1<?php echo ($default_param_arr['sequest']['max_num_internal_cleavage_sites']['value']=='1')?" selected":"";?>>1
      <OPTION VALUE=2<?php echo ($default_param_arr['sequest']['max_num_internal_cleavage_sites']['value']=='2')?" selected":"";?>>2
      <OPTION VALUE=1<?php echo ($default_param_arr['sequest']['max_num_internal_cleavage_sites']['value']=='3')?" selected":"";?>>3
      <OPTION VALUE=2<?php echo ($default_param_arr['sequest']['max_num_internal_cleavage_sites']['value']=='4')?" selected":"";?>>4
      <OPTION VALUE=1<?php echo ($default_param_arr['sequest']['max_num_internal_cleavage_sites']['value']=='5')?" selected":"";?>>5
    </SELECT>
    </TD>
   </TR>
   
   <TR>
    <TD colspan=2 bgcolor=white>
      <b>Advanced options</b> 
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#d3d3eb><b>Parent Mass Type:</b>:&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> 
    <INPUT TYPE=radio NAME="frm_MonoAvg_par" VALUE="1"<?php echo ($default_param_arr['sequest']['mass_type_parent']['value'])?" checked":"";?>>Mono
		<INPUT TYPE=radio NAME="frm_MonoAvg_par" VALUE="0"<?php echo (!$default_param_arr['sequest']['mass_type_parent']['value'])?" checked":"";?>>Avg
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#d3d3eb><b>Fragment Mass Type:</b>:&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> 
    <INPUT TYPE=radio NAME="frm_MonoAvg_frag" VALUE="1"<?php echo ($default_param_arr['sequest']['mass_type_fragment']['value'])?" checked":"";?>>Mono
		<INPUT TYPE=radio NAME="frm_MonoAvg_frag" VALUE="0"<?php echo (!$default_param_arr['sequest']['mass_type_fragment']['value'])?" checked":"";?>>Avg
    </TD>
   </TR>
    
   <TR>
    <TD align=right bgcolor=#d3d3eb><b>Peptide Mass Tolerance:</b>:&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> &nbsp; 
    <INPUT NAME="frm_pep_mass_tol" VALUE="<?php echo $default_param_arr['sequest']['peptide_mass_tolerance']['value'];?>" SIZE=6>&nbsp;
    <?php 
      $selected_num = '0';
      if(isset($default_param_arr['sequest']['peptide_mass_units'])){
        $selected_num = $default_param_arr['sequest']['peptide_mass_units']['value'];
      }
    ?>
    <SELECT name=frm_peptide_mass_unit>
      <OPTION VALUE=0<?php echo ($selected_num=='0')?" selected":"";?>>amu
      <OPTION VALUE=1<?php echo ($selected_num=='1')?" selected":"";?>>mmu
      <OPTION VALUE=2<?php echo ($selected_num=='2')?" selected":"";?>>ppm
    </SELECT>
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#d3d3eb><b> Fragment Ion Tolerance:</b>:&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> &nbsp; 
    <INPUT NAME="frm_frag_ion_tol" VALUE="<?php echo $default_param_arr['sequest']['fragment_ion_tolerance']['value'];?>" SIZE=6>&nbsp;amu
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#d3d3eb><b>Neutral Losses (H2O/NH3):</b>:&nbsp; &nbsp; 
    </TD>
    <?php 
    $ion_arr = explode(" ", $default_param_arr['sequest']['ion_series']['value']);
    for($i=0; $i < 12;$i++){
      $ion_name = 'frm_ion'.$i;
      if(isset($ion_arr[$i])){
        $$ion_name = $ion_arr[$i];
      }else{
        if($i>2){
          $$ion_name = '0.0';
        }else{
          $$ion_name = '';
        }
      }
    }
    ?>
    <TD bgcolor=#e4e4e4> &nbsp; 
     a: <INPUT TYPE=checkbox NAME="frm_ion0" VALUE="1"<?php echo ($frm_ion0)?" CHECKED":"";?>>&nbsp;&nbsp;&nbsp;&nbsp; 
	   b: <INPUT TYPE=checkbox NAME="frm_ion1" VALUE="1"<?php echo ($frm_ion1)?" CHECKED":"";?>>&nbsp;&nbsp;&nbsp;&nbsp;
	   y: <INPUT TYPE=checkbox NAME="frm_ion2" VALUE="1"<?php echo ($frm_ion2)?" CHECKED":"";?>>&nbsp;&nbsp;&nbsp;&nbsp
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#d3d3eb valign=top><b>Ion Series Weightings:</b>:&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4>
     &nbsp; 
     a: <INPUT NAME="frm_ion3" SIZE=3 VALUE="<?php echo $frm_ion3;?>">&nbsp;&nbsp;
     b: <INPUT NAME="frm_ion4" SIZE=3 VALUE="<?php echo $frm_ion4;?>">&nbsp;&nbsp;
     c: <INPUT NAME="frm_ion5" SIZE=3 VALUE="<?php echo $frm_ion5;?>">&nbsp;&nbsp;<br>
      &nbsp; 
     d: <INPUT NAME="frm_ion6" SIZE=3 VALUE="<?php echo $frm_ion6;?>">&nbsp;&nbsp;
     v: <INPUT NAME="frm_ion7" SIZE=3 VALUE="<?php echo $frm_ion7;?>">&nbsp;&nbsp;
     w: <INPUT NAME="frm_ion8" SIZE=3 VALUE="<?php echo $frm_ion8;?>">&nbsp;&nbsp;<br>
      &nbsp; 
     x: <INPUT NAME="frm_ion9" SIZE=3 VALUE="<?php echo $frm_ion9;?>">&nbsp;&nbsp;
     y: <INPUT NAME="frm_ion10" SIZE=3 VALUE="<?php echo $frm_ion10;?>">&nbsp;&nbsp;
     z: <INPUT NAME="frm_ion11" SIZE=3 VALUE="<?php echo $frm_ion11;?>">&nbsp;&nbsp;<br>
    </TD>
   </TR>
   <TR>
    <TD colspan=2 bgcolor=white>
      <b>Modifications</b> 
    </TD>
   </TR><br>
   <TR>
    <TD align=right bgcolor=#d3d3eb valign=top><b>Fixed modifications:</b>:&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> &nbsp; 
    <SELECT NAME="frm_fixed_MODS" MULTIPLE SIZE=5>
    <?php 
    $fixed_mod_arr = array();
    
    foreach($default_param_arr['sequest'] as $key=>$tmp_arr){
      if(preg_match("/add_([A-Z])_/", $key, $matches)){
        if($tmp_arr['value'] > 0){
          $fixed_mod_arr[$matches[1]] = $tmp_arr['value'];
        }
      }
    }
    
    
    $mod_value_group = array();
    
    for($i=0; $i < count($default_enzyme_mod_arr['MOD']); $i++){
      $theMod = preg_replace("/ |\t/", "", $default_enzyme_mod_arr['MOD'][$i]);
      $theMod_arr = explode(",", $theMod);
      if(in_array($theMod_arr[2], $fixed_mod_arr)){
        if(!isset($mod_value_group[$theMod_arr[2]])) $mod_value_group[$theMod_arr[2]] = array();
        array_push($mod_value_group[$theMod_arr[2]], $theMod_arr[1]);
      }
    }
    foreach($mod_value_group as $V => $L_arr){
      //if(count($L_arr) == 1) continue;
      $mod_value_group[$V] = checkFixedMod_group($V, $L_arr, $fixed_mod_arr);
    }
    for($i=0; $i < count($default_enzyme_mod_arr['MOD']); $i++){
      $theMod = preg_replace("/ |\t/", "", $default_enzyme_mod_arr['MOD'][$i]);
      $theMod_arr = explode(",", $theMod);
      $selected = '';
      if(count($theMod_arr)>2){
        if(isset($mod_value_group[$theMod_arr[2]]) and in_array($theMod_arr[1],$mod_value_group[$theMod_arr[2]])){
          $selected = ' selected';
        }
      }
      echo "<OPTION value='".$default_enzyme_mod_arr['MOD'][$i]."'$selected>".$default_enzyme_mod_arr['MOD'][$i]."\n";
    }
    ?>
    </SELECT>
    <br><br>
     C-terminal change: 
     <?php 
     if(isset($default_param_arr['sequest']['add_Cterm_peptide'])){
       $c_value = $default_param_arr['sequest']['add_Cterm_peptide']['value'];
       $n_value = $default_param_arr['sequest']['add_Nterm_peptide']['value'];
     }else if(isset($default_param_arr['sequest']['add_C_terminus'])){
      $c_value = $default_param_arr['sequest']['add_C_terminus']['value'];
      $n_value = $default_param_arr['sequest']['add_N_terminus']['value'];
     }else{
      $c_value = '0.0000';
      $n_value = '0.0000';
     }
     ?>
   +<INPUT TYPE=text NAME="frm_c_fix_term" VALUE="<?php echo $c_value;?>" Size=10>
   <br>
    N-terminal change: 
    +<INPUT TYPE=text NAME="frm_n_fix_term" VALUE="<?php echo $n_value;?>" Size=10>
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#d3d3eb valign=top><b>Variable modifications:</b>:&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> &nbsp; 
    <SELECT NAME="frm_variable_MODS" MULTIPLE SIZE=5>
    <?php 
    
     
    $mod_v_arr = explode(" ", $default_param_arr['sequest']['diff_search_options']['value']);
    for($i=0; $i < count($default_enzyme_mod_arr['MOD']); $i++){
      $selected = '';
      $theMod = preg_replace("/ |\t/", "", $default_enzyme_mod_arr['MOD'][$i]);
      $theMod_arr = explode(",", $theMod);
      //print_r($theMod_arr);exit;
      foreach($mod_v_arr as $key=>$value){
        if(is_numeric($value) and isset($mod_v_arr[$key + 1]) and count($theMod_arr)>2 ){
          if($value == $theMod_arr[2] and $mod_v_arr[$key + 1] == $theMod_arr[1]){
            $selected = " selected";
          }
        }
      } 
      echo "<OPTION value='".$default_enzyme_mod_arr['MOD'][$i]."'$selected>".$default_enzyme_mod_arr['MOD'][$i]."\n";
    }
    ?>
    </SELECT>
    <br><br>
     C-terminal change:
     <?php 
     $frm_c_term = '0.0000';
     $frm_n_term = '0.0000';
     if(isset($default_param_arr['sequest']['term_diff_search_options'])){
      $tmp_term_arr = explode(' ', $default_param_arr['sequest']['term_diff_search_options']['value']);
      if(count($tmp_term_arr) == 2){
        $frm_c_term = trim($tmp_term_arr[0]);
        $frm_n_term = trim($tmp_term_arr[1]);
      }
     }
     ?>
   +<INPUT TYPE=text NAME="frm_c_term" VALUE="<?php echo $frm_c_term;?>" Size=10>
   <br>
    N-terminal change: 
    +<INPUT TYPE=text NAME="frm_n_term" VALUE="<?php echo $frm_n_term;?>" Size=10>
    </TD>
   </TR>
   
   <tr>
   <td colspan=2><br><b><font color='red' face='helvetica,arial,futura' size='3'>Parameter Set</font></b>
   <?php if($perm_modify){?>&nbsp; &nbsp;
   New Set<input type=radio value=newSet name=frm_set onClick="isNewSet(true)" <?php echo ($frm_myaction == 'newSet')?'checked':'';?>>
   Modify Set<input type=radio value=modifySet name=frm_set onClick="isNewSet(false)" <?php echo ($frm_myaction == 'modifySet')?'checked':'';?>>
   <?php 
   }else{
     echo " &nbsp; You have no permission to change the setting.";
   }
   if($SEQUEST_User and $frm_myaction != 'newSet'){
       echo "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
       Set by: <b>".$SEQUEST_User."</b>&nbsp; &nbsp; Set date:<b>".$Set_Date."</b>\n"; 
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
       echo 'New set name: <input type=text size=10 name=frm_setName>';
   }else{
     if($set_arr){
      echo "<input type=hidden name=frm_setName value='$frm_setName'>\n";
       echo "<b>Set name</b>: <select name=frm_setID onChange=\"isNewSet(false)\">\n";
        echo "<option value=''>-select--";
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
    
   <input type=reset value='Reset' name=reset>
   <input type="button" value="Close" onClick="window.close()">
   </center>
  </td>
  </tr>
   </TABLE>
  </td>
 </tr>
</table>
</form>
<script langage='javascript'>
var theForm = document.listform;
var db_obj = theForm.frm_db;
function setValue(){
  unsetDefaultSelected(db_obj);
  <?php 
  if(isset($default_param_arr['sequest']['database_name'])){
    $db_name = $default_param_arr['sequest']['database_name']['value'];
  }else if(isset($default_param_arr['sequest']['first_database_name'])){
    $db_name = $default_param_arr['sequest']['first_database_name']['value'];
  }
  ?>
  setSelecedValue(db_obj, '<?php echo $db_name;?>');
}
function unsetDefaultSelected(this_obj){
  for (i=0; i < this_obj.length; i++) {
     this_obj[i].selected = false;
  }
}
function setSelecedValue(this_obj, v){
  for (i=0; i<this_obj.length; i++){
    if(this_obj.options[i].value == v){
      this_obj.options[i].selected = true;
      return;
    }
  }
}
function max_variable_in_default(){
<?php 
  echo "return ".count($original_variable_mod_arr)/2 . "\n" ;
?>
}
setValue();
</script>
<?php
include("./ms_footer_simple.php");
//--------------------------------
function get_default_param($file){
//--------------------------------
  $is_default_file = 0;
  if(preg_match("/^\[SEQUEST\]/", $file, $matches)){
    $file_arr = explode("\n", $file);
  }else if(_is_file($file)) {
    $file_arr = file($file);
    $is_default_file = 1;
  }else{
		echo "ERROR: <br>$file doens't exist. <br>";
	  if(strpos($file, 'sequest.params')){
    	echo "Please paste parameter file from SEQUEST server to $file.";
	  }else{
			echo "Please paste 'sequest_enzyme_modifications.txt' file from Prohits/install/Prohits_SEQUEST/ to $file";
		}
		echo "<br>Read the installation instruction in Prohits/install/Prohits_SEQUEST/ for detail.";exit;
  }
  $enzyme_started = 0;
  $MOD_started = 0;
  $sequest_started = 0;
  $enzyme_info_started = 0;
  
  foreach($file_arr as $buffer){
      $buffer = trim($buffer);
      if(!$buffer || preg_match("/^#/", $buffer ) ) continue;
      if(strpos($buffer, "[ENZYME]" ) === 0){
         $enzyme_started = 1;
         $rt['enzyme'] = array();
         continue;
      }else if(strpos($buffer, "[MODIFICATIONS]") === 0){
         $enzyme_started = 0;
         $MOD_started = 1;
         $rt['MOD'] = array();
         continue;
      }else if(strpos($buffer, "[SEQUEST]") === 0){
         $MOD_started = 0;
         $sequest_started = 1;
         $rt['sequest'] = array();
         continue;
      }else if(strpos($buffer, "[SEQUEST_ENZYME_INFO]") === 0){
         $MOD_started = 0;
         $sequest_started = 0;
         $enzyme_info_started = 1;
         continue;
      }
      if($enzyme_started){
         $buffer = preg_replace("/[ ]+|\t+/", '', $buffer);
         array_push($rt['enzyme'], $buffer);
      }else if($MOD_started){
        //$buffer = preg_replace("/ |\t/", '', $buffer);
        array_push($rt['MOD'], $buffer);
      }else if($sequest_started){
          $tmp_arr= preg_split("/=|;/", $buffer, 3);
          $tmp_name = trim($tmp_arr[0]);
          $rt['sequest'][$tmp_name]['desc'] = (count($tmp_arr)==3)? $tmp_arr[2]:'';
          if($is_default_file and preg_match("/^add_/", $tmp_arr[0]) ){
            $rt['sequest'][$tmp_name]['value'] = '0.0000';
          }else if(count($tmp_arr)>1){
            if($tmp_name == 'second_database_name') $tmp_arr[1] = '';
            $rt['sequest'][$tmp_name]['value']= trim($tmp_arr[1]);
          }
      }else if($enzyme_info_started){
          $tmp_arr= preg_split("/[.]|;/", $buffer, 3);
          $this_index = 0;
          if(count($tmp_arr)>1){
            $this_index = (int)$tmp_arr[0];
          }else{
            continue;
          }
          $rt['sequest_enzyme_info'][$this_index]['value']= preg_replace("/[ ]+|\t+/", ',', trim($tmp_arr[1]));
          $rt['sequest_enzyme_info'][$this_index]['desc'] = (count($tmp_arr)==3)? $tmp_arr[2]:'';
      }
  }
  return $rt;
}
//-------------------------------------------------------
function checkFixedMod_group($V, $L_arr, $fixed_mod_arr){
//-------------------------------------------------------
  $rt = array();
  $fix_letter_arr = array();
  foreach($fixed_mod_arr as $letter => $tmp_value){
    if($V == $tmp_value){
      array_push($fix_letter_arr, $letter);
    }
  }
  $total_fix_L = count($fix_letter_arr);
  foreach($L_arr as $Ls){
    $char_buff = preg_split('//', $Ls);
    
    $this_found = 0;
    foreach($char_buff as $L){
      if(!$L)continue;
      if(in_array($L, $fix_letter_arr)){
        $this_found++;
      }else{
        $this_found = 0; break;
      }
    }
    if($this_found == $total_fix_L){
      return array($Ls);
    }else if($this_found){
      array_push($rt, $Ls);
    }
  }
  return $rt;
}
?>
