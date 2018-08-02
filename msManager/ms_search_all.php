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
$frm_use_NL_ions = '';
$frm_enzyme = 1;
$frm_Isotope_error = '0';
$frm_Semi_style_cleavage = '0';
$decoy_search = '';
$isotope_error = '';
$frm_decoy = '';
$frm_GPM_refinement_check = 0;
$frm_msgfpl_FragmentMethodID = 0;
$frm_msgfpl_InstrumentID = 0;

$searchAll_User  = '';
$msg = '';
$frm_db = '';
$frm_CHARGE = '2+, 3+ and 4+';
$prohits_error_msg = '';
$ProhitsUsekscore = '';

define ("GPM_FORM_DIR", "/tandem/"); 

include("./ms_permission.inc.php");
require("./common_functions.inc.php");
include("./autoSearch/auto_search_mascot.inc.php");
require("./is_dir_file.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$searchAll_parm_dir = "./autoSearch/";
$mascot_modifications_file = $searchAll_parm_dir."mod_file"; 
 
if(_is_file($mascot_modifications_file)){
  $mascot_mod_array = read_mascot_mod_file($mascot_modifications_file);
}else{
  echo "<b>Error</b>: mod_file is missing in $searchAll_parm_dir. This file should be copied from Mascot. Please report to Prohits administrator";exit;
}
if(defined("MASCOT_IP") and MASCOT_IP){
  $mascot_default = get_mascot_default_param($searchAll_parm_dir, $refresh=1);
  $mascot_info['INSTRUMENT'] = explode(";;", $mascot_default['OPTIONS_INSTRUMENT']);
  $mascot_info['DB'] = explode(";;", $mascot_default['OPTIONS_DB']);
  $mascot_info['CLE'] = explode(";;", str_replace("-", "_", $mascot_default['OPTIONS_CLE']));
}
$default_param_arr = get_comet_default_param($searchAll_parm_dir);
  
if($frm_setID){
  $SQL = "SELECT `Name` FROM `SearchParameter` WHERE `ID`='$frm_setID'";
  if($tmp_set_arr = $managerDB->fetch($SQL)){
    $tmp_set_name_arr = explode('_',$tmp_set_arr['Name'],2);
    //$table = $tmp_set_name_arr[0];
  }
}

if(defined("MASCOT_IP") and MASCOT_IP){
  //remove enzymes that mascot doens't has
  $default_param_arr['comet_enzyme_info']['name'] = array_intersect($default_param_arr['comet_enzyme_info']['name'], $mascot_info['CLE']);
  if($frm_Semi_style_cleavage and $frm_enzyme){
    //check if mascot has the same enzyme
    $the_CLE = "semi".$default_param_arr['comet_enzyme_info']['name'][$frm_enzyme];
    if(!in_array($the_CLE, $mascot_info['CLE'])){
      $the_CLE = str_replace("-", "_", $the_CLE);
      if(!in_array($the_CLE, $mascot_info['CLE'])){
        $prohits_error_msg = "The enzyme $the_CLE is missing in Mascot";
      }
    }
  } 
}

if(!isset($default_param_arr['comet']['CHARGE'])) $default_param_arr['comet']['CHARGE'] = '1+, 2+ and 3+';
if(!isset($default_param_arr['comet']['INSTRUMENT'])) $default_param_arr['comet']['INSTRUMENT'] = '';

$selected_MODS_arr = array();

if(!$default_param_arr){
  echo "<b>Error</b>: comet.params.new is missing in $searchAll_parm_dir. Please report to Prohits administrator";exit;
}

if($frm_setName){
  $frm_setName = preg_replace("/ /", "_", trim($frm_setName));
  $frm_setName = preg_replace("/[^A-Za-z0-9_]/", "", $frm_setName);
}
if(isset($frm_setName) and trim($frm_setName) and $frm_myaction == 'yes' and $perm_modify and !$prohits_error_msg){
  $param_str  = "database_name=\n";
  $param_str .= "search_enzyme_number=".$_POST['frm_enzyme']."\n";
  $param_str .= "multiple_select_str=".$_POST['frm_multiple_select_str']."\n";
  $param_str .= "allowed_missed_cleavage=".$_POST['frm_mis_cleavage']."\n";
  $param_str .= "num_enzyme_termini=".$frm_Semi_style_cleavage."\n";
  $param_str .= "decoy_search=".$frm_decoy."\n";
  $param_str .= "mass_type_parent=".$_POST['frm_MonoAvg_par']."\n";
  $param_str .= "mass_type_fragment=".$_POST['frm_MonoAvg_frag']."\n";
  $param_str .= "peptide_mass_tolerance=".$_POST['frm_pep_mass_tol']."\n";
  $param_str .= "peptide_mass_units=".$_POST['frm_peptide_mass_unit']."\n";
  $param_str .= "fragment_bin_tol=".$_POST['frm_frag_ion_tol']."\n";
  //$param_str .= "fragment_bin_offset=".$_POST['frm_frag_ion_offset']."\n";
  $param_str .= "use_NL_ions=".$frm_use_NL_ions."\n";
  $param_str .= "isotope_error=".$frm_Isotope_error."\n";
  $param_str .= "CHARGE=".$_POST['frm_CHARGE']."\n";
  
  $param_str .= "ProhitsUsekscore=".$ProhitsUsekscore."\n";
  if(isset($_POST['frm_INSTRUMENT'])){
  	$param_str .= "INSTRUMENT=".$_POST['frm_INSTRUMENT']."\n";
  }

  $param_str .= "ProhitsUsekscore=".$ProhitsUsekscore."\n";
  $param_str .= "msgfpl_FragmentMethodID=".$frm_msgfpl_FragmentMethodID."\n";
  $param_str .= "msgfpl_InstrumentID=".$frm_msgfpl_InstrumentID."\n";
  
  
  $saved_setName = $table.'_'.$frm_setName;
   
  $frm_setID = search_para_add_modify('SearchAll', $frm_setID, $saved_setName, $USER->ID, $frm_ProjectID, $param_str);
  if(!$frm_setID){
    $msg = "The name '$frm_setName' has been used. Please use other name";
    $frm_myaction = 'newSet';
  }else{
?>
<script language=javascript>
    if(opener.document.form_task){
      opener.document.form_task.frm_SearchAllSetID_default.value = <?php echo $frm_setID?>;
      opener.refreshWin();
			theForm.submit();
      window.close();
    }
</script>
<?php 
  }
}
if($frm_myaction == 'yes' or !$frm_myaction){
  $frm_myaction = 'modifySet';
}

$tmp_pro_str = ($USER->Type == 'Admin')?"": $pro_access_ID_str;
$set_arr = get_search_parameters('searchAll', 0, $tmp_pro_str);

if($frm_setID and $frm_myaction != 'newSet'){
  $theSet_arr = get_search_parameters('searchAll', $frm_setID);
  $frm_setName = str_replace($table.'_',"",$theSet_arr['Name']);
  $frm_ProjectID = $theSet_arr['ProjectID'];
	$set_UserID = $theSet_arr['User'];
}
if($theSet_arr){
  $thePara_arr = explode("\n",$theSet_arr['Parameters']); 
  $searchAll_User = get_userName($theSet_arr['User']);
  $Set_Date = $theSet_arr['Date'];
  ///$frm_enzyme = $theSet_arr['enzyme'];
  $param_arr = get_comet_param($thePara_arr);
  
  $frm_enzyme = $param_arr['comet']['search_enzyme_number'];
  if(isset($param_arr)){
    $default_param_arr['comet'] = $param_arr['comet'];
    $tmp_multiple_select_arr = explode("&&", $param_arr['comet']['multiple_select_str']);
    if(isset($param_arr['comet']['msgfpl_InstrumentID'])){
      $frm_msgfpl_InstrumentID = $param_arr['comet']['msgfpl_InstrumentID'];
    }else{
      $frm_msgfpl_InstrumentID = 0;
    }
    if(isset($param_arr['comet']['msgfpl_FragmentMethodID'])){
      $frm_msgfpl_FragmentMethodID = $param_arr['comet']['msgfpl_FragmentMethodID'];
    }else{
      $frm_msgfpl_FragmentMethodID = 0;
    }
    foreach($tmp_multiple_select_arr as $tmp_multiple_select_val){
      $tmp_arr_1 = explode("|", $tmp_multiple_select_val);
      if(count($tmp_arr_1)==2){
        $selected_MODS_arr[$tmp_arr_1[0]] = explode(":::", $tmp_arr_1[1]);
      }
    }
  }
}

include("./ms_header_simple.php");

?>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script> 
<script language=javascript>
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
  var table = form.table.value;
  var save_type = form.save_type.value;
  var setName = form.frm_setName.value = trim(form.frm_setName.value);
  var table_up = table.toUpperCase();
  var setName_up = setName.toUpperCase();
  var frm_set = form.frm_set.value;  
  if(save_type != 'defaultSet'){
    var n = setName_up.search(table_up); 
    if(setName.match(/default/gi) && frm_set == 'default'){
      alert("The word 'default' is key. Please enter other words");
      return false;
    }else if(n >= 0){  
      alert("The word " + table + " is key. Please remove the word " + table);
      return false;
    }else if(setName.length > 30){
      alert("The set name length cannot be great than than 30 characters");
      return false;
    }else if(!onlyAlphaNumerics(form.frm_setName.value, 1)){
      alert("Only A-Z, a-z and 0-9 alowed");
      return false;
    }
    form.frm_setName.value = setName;
  }
//return false;  
  getMultipleSelectStr();
  form.submit();
}

function getMultipleSelectStr(){
  theForm = document.listform;
  all_str = '';
  all_str += 'frm_variable_MODS|' + catMultipleSelect(theForm.frm_variable_MODS)+"&&";
  all_str += 'frm_fixed_MODS|' + catMultipleSelect(theForm.frm_fixed_MODS)+"&&";
  all_str += 'frm_refinement_MODS|' + catMultipleSelect(theForm.frm_refinement_MODS);
  theForm.frm_multiple_select_str.value = all_str;
}
function catMultipleSelect(this_obj){
  var str = '';
  var num = 0;
  for (i=0; i<this_obj.length; i++){
    if(this_obj.options[i].selected){
      if(str) str += ":::";
      str += this_obj.options[i].value;
      num++;
    }  
  }
  return str;
}

function verify_MODS(this_one,that_one){
  for(i=0; i<this_one.length; i++){
    if(this_one.options[i].selected && that_one.options[i].selected){
      that_one.options[i].selected = false;
    }
  }
  return true;
}
function toggle_refinement(theForm){
  var sel_obj = theForm.frm_refinement_MODS;
  var div_obj = document.getElementById('refine_div');
   
  if(theForm.frm_GPM_refinement_check.checked){
    div_obj.style.display = "block";
    
  }else{
    div_obj.style.display = "none";
    for(i=0; i<sel_obj.length; i++){
      sel_obj.options[i].selected = false;
    }
  }
}
</script>
<form name=listform method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=frm_myaction value='yes'>
<input type=hidden name=save_type value='<?php echo $frm_myaction?>'>
<input type=hidden name=frm_multiple_select_str value=''>
<input type=hidden name=table value='<?php echo $table?>'>
<table border="0" cellpadding="0" cellspacing="2">
  <tr>
   <td><img src='./images/prohits_logo_s.gif' border=0></td>
   <td><b><font color="red" face='helvetica,arial,futura' size='3'><?php echo $table?> iProphet Search Engine Parameters</font></b><br>
     Create or modify search parameter set
  </td>
 </tr>
 <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
 </tr>
 <tr>
  <td colspan=2 valign=top><font color="#FF0000"><?php echo $prohits_error_msg;?></font>
  <TABLE width=650 cellspacing="1" cellpadding="1">
   <TR>
    <TD colspan=2 bgcolor=white>
      <b>Enzyme</b> 
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#b7ae8e width=30%><font color="#FFFFFF"><b>Enzyme</b>:</font>&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> 
    <select name=frm_enzyme>
    <?php 
    foreach($default_param_arr['comet_enzyme_info']['name'] as $index=>$tmp_ens){
      $selected='';
      if($frm_enzyme == $index) $selected = ' selected';
      echo "<option value='$index'$selected>$tmp_ens\n";
    }
    ?>
    </select>
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#b7ae8e><font color="#FFFFFF"><b>Max missed cleavage:</b></font>&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> 
    <SELECT name=frm_mis_cleavage>
      <OPTION VALUE=0<?php echo ($default_param_arr['comet']['allowed_missed_cleavage']['value']=='0')?" selected":"";?>>0
      <OPTION VALUE=1<?php echo ($default_param_arr['comet']['allowed_missed_cleavage']['value']=='1')?" selected":"";?>>1
      <OPTION VALUE=2<?php echo ($default_param_arr['comet']['allowed_missed_cleavage']['value']=='2')?" selected":"";?>>2
      <OPTION VALUE=3<?php echo ($default_param_arr['comet']['allowed_missed_cleavage']['value']=='3')?" selected":"";?>>3
      <OPTION VALUE=4<?php echo ($default_param_arr['comet']['allowed_missed_cleavage']['value']=='4')?" selected":"";?>>4
      <OPTION VALUE=5<?php echo ($default_param_arr['comet']['allowed_missed_cleavage']['value']=='5')?" selected":"";?>>5
    </SELECT>
    &nbsp; &nbsp; &nbsp; <b>Semi-style cleavage</b> 
    <INPUT TYPE=checkbox NAME="frm_Semi_style_cleavage" VALUE="1"<?php echo ($default_param_arr['comet']['num_enzyme_termini']==1)?" CHECKED":"";?>>&nbsp;&nbsp;&nbsp;&nbsp; 
	   
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#b7ae8e><font color="#FFFFFF"><b>Decoy:</b></font>&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> 
    <INPUT TYPE=checkbox NAME="frm_decoy" VALUE="1" <?php echo ($default_param_arr['comet']['decoy_search'])?" CHECKED":"";?>>&nbsp;&nbsp;&nbsp;&nbsp; 
	   
    </TD>
   </TR>
   <TR>
    <TD colspan=2 bgcolor=white>
      <b>Advanced options</b> 
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#b7ae8e><font color="#FFFFFF"><b>Parent Mass Type:</font></b>&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> 
    <INPUT TYPE=radio NAME="frm_MonoAvg_par" VALUE="1"<?php echo ($default_param_arr['comet']['mass_type_parent'])?" checked":"";?>>Monoisotopic
		<INPUT TYPE=radio NAME="frm_MonoAvg_par" VALUE="0"<?php echo (!$default_param_arr['comet']['mass_type_parent'])?" checked":"";?>>Average 
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#b7ae8e><font color="#FFFFFF"><b>Fragment Mass Type:</b></font>&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> 
    <INPUT TYPE=radio NAME="frm_MonoAvg_frag" VALUE="1"<?php echo ($default_param_arr['comet']['mass_type_fragment'])?" checked":"";?>>Monoisotopic
		<INPUT TYPE=radio NAME="frm_MonoAvg_frag" VALUE="0"<?php echo (!$default_param_arr['comet']['mass_type_fragment'])?" checked":"";?>>Average 
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#b7ae8e><font color="#FFFFFF"><b>Peptide Mass Tolerance:</b></font>&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> &nbsp; 
    <INPUT NAME="frm_pep_mass_tol" VALUE="<?php echo $default_param_arr['comet']['peptide_mass_tolerance'];?>" SIZE=6>&nbsp;
    <?php 
      $selected_num = '0';
      if(isset($default_param_arr['comet']['peptide_mass_units'])){
        $selected_num = $default_param_arr['comet']['peptide_mass_units'];
      }
    ?>
    <SELECT name=frm_peptide_mass_unit>
      <OPTION VALUE=0<?php echo ($selected_num=='0')?" selected":"";?>>amu
      <!--OPTION VALUE=1<?php echo ($selected_num=='1')?" selected":"";?>>mmu-->
      <OPTION VALUE=2<?php echo ($selected_num=='2')?" selected":"";?>>ppm
    </SELECT>
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#b7ae8e><font color="#FFFFFF"><b> Fragment Ion Tolerance:</b></font>&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> &nbsp; 
    <INPUT NAME="frm_frag_ion_tol" VALUE="<?php echo $default_param_arr['comet']['fragment_bin_tol'];?>" SIZE=6>&nbsp;amu  &nbsp; &nbsp; &nbsp;
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor="#b7ae8e"><font color="#FFFFFF"><b>Neutral Losses (H2O/NH3):</b></font>&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> &nbsp; 
     <INPUT TYPE=checkbox NAME="frm_use_NL_ions" VALUE="1"<?php echo ($default_param_arr['comet']['use_NL_ions'])?" CHECKED":"";?>>&nbsp;&nbsp;&nbsp;&nbsp; 
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor="#b7ae8e"><font color="#FFFFFF"><b>Isotope error:</b></font>&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> &nbsp; 
     <SELECT name=frm_Isotope_error>
        <option<?php echo ($default_param_arr['comet']['isotope_error']=='0')?" selected":"";?>>0</option>
        <option<?php echo ($default_param_arr['comet']['isotope_error']=='1')?" selected":"";?>>1</option>
        <option<?php echo ($default_param_arr['comet']['isotope_error']=='2')?" selected":"";?>>2</option>
     </SELECT>
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#b7ae8e valign=top><font color="#FFFFFF"><b>Peptide charge:</b></font>&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4>
      <SELECT name=frm_CHARGE>
        <option <?php echo ($default_param_arr['comet']['CHARGE']=='1+, 2+ and 3+')?" selected":"";?>>1+, 2+ and 3+</option>
        <option <?php echo ($default_param_arr['comet']['CHARGE']=='2+ and 3+')?" selected":"";?>>2+ and 3+</option>
        <option <?php echo ($default_param_arr['comet']['CHARGE']=='2+, 3+ and 4+')?" selected":"";?>>2+, 3+ and 4+</option>
      </SELECT>
      <font size="-2">if spectrum has no charge.</font> 
    </TD>
   </TR>
   <TR>
    <TD colspan=2 bgcolor=white>
      <b>Modifications</b> 
    </TD>
   </TR><br>
   <TR>
    <TD align=center bgcolor=#b7ae8e valign=top colspan=2>
      <table border=0 width=95% cellpadding="3" cellpadding="3">
        <tr align=center>
          <td bgcolor=#e4e4e4><b>Fixed modifications:</b><br>
    <SELECT NAME="frm_fixed_MODS" MULTIPLE SIZE=5 onChange="verify_MODS(this.form.frm_fixed_MODS,this.form.frm_variable_MODS)">
    <?php 
    foreach($mascot_mod_array as $name=>$value){
      
      if(isset($selected_MODS_arr['frm_fixed_MODS']) && in_array($name, $selected_MODS_arr['frm_fixed_MODS'])){
        $fixed_selected = "selected";
      }else{
        $fixed_selected = "";
      }
      echo "<option value='$name' $fixed_selected>$name\n";
    }
    ?>
    </SELECT>
          </td>
          <td bgcolor=#e4e4e4><b>Variable modifications:</b><br>
    <SELECT NAME="frm_variable_MODS" MULTIPLE SIZE=5 onChange="verify_MODS(this.form.frm_variable_MODS, this.form.frm_fixed_MODS)">
    <?php 
    foreach($mascot_mod_array as $name=>$value){
      if(isset($selected_MODS_arr['frm_variable_MODS']) && in_array($name, $selected_MODS_arr['frm_variable_MODS'])){
        $variable_selected = "selected";
      }else{
        $variable_selected = "";
      }
      echo "<option value='$name' $variable_selected>$name\n";
    }
    ?>
    </SELECT>
          </td>
        </tr>  
      </table><br> 
    </TD>
   </TR>
    
   <TR>
    <TD colspan=2 bgcolor=white>
      <b>Specific Search engines</b> 
    </TD>
   </TR>
   <TR>
    <TD align=right bgcolor=#b7ae8e valign=top><font color="#FFFFFF"><b>Mascot:</b></font>&nbsp; &nbsp; 
    </TD>
    <TD bgcolor=#e4e4e4> &nbsp; 
    Instrument
     <SELECT NAME="frm_INSTRUMENT">
     <?php 
    if(isset($mascot_info['INSTRUMENT'])){
      foreach($mascot_info['INSTRUMENT'] as $value){
        if($default_param_arr['comet']['INSTRUMENT'] == $value){
          $INSTRUMENT_selected = "selected";
        }else{
          $INSTRUMENT_selected = "";
        }
        echo "<option value='$value' $INSTRUMENT_selected>$value</option>\n";
      }
    }
    ?>
    </SELECT>
    </TD>
   </TR>
   
   <TR>
    <TD align=right bgcolor=#b7ae8e valign=top rowspan="2"><font color="#FFFFFF"><b>MS-GF+:</b></font>&nbsp; &nbsp;</TD>
    <TD bgcolor=#e4e4e4> &nbsp; 
    FragmentMethod
    <SELECT name=frm_msgfpl_FragmentMethodID>
        <option<?php echo ($frm_msgfpl_FragmentMethodID=='0')?" selected":"";?> value='0'>in the spectrum or CID if no info (Default)</option>
        <option<?php echo ($frm_msgfpl_FragmentMethodID=='1')?" selected":"";?> value='1'>CID</option>
        <option<?php echo ($frm_msgfpl_FragmentMethodID=='2')?" selected":"";?> value='2'>ETD</option>
        <option<?php echo ($frm_msgfpl_FragmentMethodID=='3')?" selected":"";?> value='3'>HCD</option>
    </SELECT>
   </TR>
   <TR> 
    <TD bgcolor=#e4e4e4> &nbsp; 
    Instrument
    <SELECT name=frm_msgfpl_InstrumentID>
        <option<?php echo ($frm_msgfpl_InstrumentID=='0')?" selected":"";?> value='0'>Low-res LCQ/LTQ (Default)</option>
        <option<?php echo ($frm_msgfpl_InstrumentID=='1')?" selected":"";?> value='1'>High-res LTQ</option>
        <option<?php echo ($frm_msgfpl_InstrumentID=='2')?" selected":"";?> value='2'>TOF</option>
        <option<?php echo ($frm_msgfpl_InstrumentID=='3')?" selected":"";?> value='3'>Q-Exactive</option>
    </SELECT>
    </TD>
   </TR>
   
   
   <TR>
    <TD align=right bgcolor=#b7ae8e valign=top rowspan="2"><font color="#FFFFFF"><b>X!Tandem:</b></font>&nbsp; &nbsp;</TD>
    <TD bgcolor=#e4e4e4><input type=checkbox name='ProhitsUsekscore' value='yes' <?php echo (isset($default_param_arr['comet']['ProhitsUsekscore']) && $default_param_arr['comet']['ProhitsUsekscore'])?" CHECKED":"";?>>Use  k-score plug-in module</TD>
   </TR>
   <TR> 
    <TD bgcolor=#e4e4e4>
    <INPUT TYPE=checkbox NAME="frm_GPM_refinement_check" VALUE="1"<?php echo ((isset($selected_MODS_arr['frm_refinement_MODS']) && trim($selected_MODS_arr['frm_refinement_MODS'][0]))?" CHECKED":"");?> onclick="toggle_refinement(this.form)">
    <font size="-1">For X!Tandem search, check this box if you want to use both <b>refinement AND variable</b> modifications.</font>
    <div id="refine_div" style="display: <?php echo ((isset($selected_MODS_arr['frm_refinement_MODS']) && trim($selected_MODS_arr['frm_refinement_MODS'][0]))?'block':'none')?>;width:100%;border: red solid 0;padding:0px 0px 0px 0px; margin: 0px 0px;">
    <SELECT NAME="frm_refinement_MODS" MULTIPLE SIZE=5>
     <?php 
     foreach($mascot_mod_array as $name=>$value){
      if(isset($selected_MODS_arr['frm_refinement_MODS']) && in_array($name, $selected_MODS_arr['frm_refinement_MODS'])){
        $refinement_selected = "selected";
      }else{
        $refinement_selected = "";
      }
      echo "<option value='$name' $refinement_selected>$name\n";
    }
     ?>
    </SELECT>
    </div>
    </TD>
   </TR>
   <tr>
   <td colspan=2><br><b><font color='red' face='helvetica,arial,futura' size='3'>Parameter Set</font></b>
   <?php 
   if($frm_myaction != 'defaultSet'){
     if($perm_modify){?>&nbsp; &nbsp;
     New Set<input type=radio value=newSet name=frm_set onClick="isNewSet(true)" <?php echo ($frm_myaction == 'newSet')?'checked':'';?>>
     Modify Set<input type=radio value=modifySet name=frm_set onClick="isNewSet(false)" <?php echo ($frm_myaction == 'modifySet')?'checked':'';?>>
     <?php 
     }else{
       echo " &nbsp; You have no permission to change the setting.";
     }
   }else{
     echo "<input type='hidden' value='default' name='frm_set'>"; 
   }
   if($frm_myaction != 'newSet' && $frm_setID){
   //if($searchAll_User and $frm_myaction != 'newSet'){
       echo "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
       Set by: <b>".$searchAll_User."</b>&nbsp; &nbsp; Set date:<b>".$Set_Date."</b>\n"; 
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
   }elseif($frm_myaction != 'defaultSet'){
     if($set_arr){
       $pattern = "/^".$table."_/";
       echo "<input type=hidden name=frm_setName value='$frm_setName'>\n";
       echo "<b>Set name</b>: <select name=frm_setID onChange=\"isNewSet(false)\">\n";
       echo "<option value=''>-select--";
       foreach($set_arr as $tmpSet){
         if(!preg_match($pattern, $tmpSet['Name'], $matches)) continue;
         
         $tmp_set_Name = str_replace($table.'_',"",$tmpSet['Name']);
          
         $selected = ($tmpSet['ID'] == $frm_setID)?" selected":"";
         echo "<option value='" . $tmpSet['ID'] . "'$selected>".$tmpSet['Name']."\n";
       }
       echo "</select>\n";
       
     }else if($perm_insert){
       echo "<font color=\"#FF0000\">Please create new parameter set.</font>";
     }
   }else{
     echo "<input type=hidden name=frm_setName value='default'>\n"; 
   }
   
   echo " &nbsp; &nbsp; <b>for Project</b>: <select name=frm_ProjectID>\n";
   echo "<option value=''>none\n";
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
   if($perm_modify and ((($USER->Type == 'Admin' or $USER->ID == $set_UserID) and $set_UserID) or $frm_myaction == 'newSet' or $frm_myaction == 'defaultSet')){
      if($frm_myaction != 'defaultSet'){
        $save_value = 'Save';
      }else{
        $save_value = "save as $table default";
      }
      echo "&nbsp; &nbsp; &nbsp;  <input type=button name='save_set' value='$save_value' onClick=\"checkForm(this.form)\">\n";
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
<?php
include("./ms_footer_simple.php");

?>
