<?php 
/***********************************************************************
    Prohits version 1.00
    Copyright (C) 2001, Mike Tyers, All Rights Reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
*************************************************************************/

$frm_myaction = '';

$TPP_User = '';
$Set_Date = '';
$frm_setName = '';
$set_arr = array();
$perm_modify = '';
$pars_file = '';
$default = 0;

$aa_array = array('A','C','D','E','F','G','H','I','K','L','M','N','P','Q','R','S','T','V','W','Y','n','c');
$xinter_pppfilter = '';
$xinter_ppiter = '';

$pep_icat = '';
$pep_icat = '';
$pep_noicat = '';
$pep_nglyc = '';
$pep_pI = '';
$pep_hydro ='';
$pep_accmass = '';
$pep_nontt = '';
$pep_maldi= '';
$pep_xclaster = '';
$pep_nclaster='';
$pep_usedecoy = '';
$pep_prot = '';
$pep_ngrps ='';
$pep_occ = '';
$pep_decoystr = '';

$xp_run = '';
$xp_mass = '';
$xp_fix = '';
$xp_heavy = '';
$xp_res1 = '';
$xp_res1md = '';
$xp_res2 = '';
$xp_res2md = '';
$xp_res3 = '';
$xp_res3md = '';
$as_run = '';
$as_static = '';
$as_labres1  = '';
$as_labres2 = '';
$as_labres3 = '';
$as_labres4 = '';
$as_labres5 = '';
$as_heavy = '';
$as_fixedscan = '';
$as_cidonly = '';
$as_area = '';
$as_zerobg = '';
$as_highbgok = '';
$as_mzpeak = '';

$as_res1 = '';
$as_res1mass = '';
$as_res2 = '';
$as_res2mass = '';
$as_res3 = '';
$as_res3mass = '';

$lb_run = '';
$lb_channel = '';
$frm_condition = '';

$perm_modify = false;
$perm_delete = false;
$perm_insert = false;
$parameter_file_folder = "../TMP/search_paramters";

require("../common/site_permission.inc.php"); 
require("../common/common_fun.inc.php");

$USER = $_SESSION['USER'];
$mainDB = new mysqlDB(PROHITS_DB);
$SQL  = "select P.Insert, P.Modify, P.Delete from PagePermission P, Page G where P.PageID=G.ID and G.PageName='Auto Search' and UserID=$USER->ID";
$record = $mainDB->fetch($SQL);
if(count($record)){
  $perm_modify = $record['Modify'];
  $perm_delete = $record['Delete'];
  $perm_insert = $record['Insert'];
}

if(!is_dir($parameter_file_folder)) mkdir($parameter_file_folder, 0755, TRUE);

if($frm_setName){
  $frm_setName = preg_replace("/[^A-Za-z0-9_]/", "", $frm_setName);
  $pars_file = $parameter_file_folder."/$frm_setName.tpp";
}
if(isset($frm_setName) and trim($frm_setName) and $frm_myaction == 'yes' and $perm_modify){
  $to_file = '';
  $fd_prt = @fopen($pars_file, "w");
  if(!$fd_prt) fatalError("Apache cannot write to file" . $pars_file . ". Please change the setting of Prohis server", __LINE__);
   
  reset ($_POST);
  while (list($key, $val) = each($_POST)) {
    if(preg_match("/^frm_/",$key) or $key == 'SID') continue;
      if(get_magic_quotes_gpc()) $$key = $val = stripslashes($val);
      $to_file .= "$key=$val\n";
  }
  $to_file .= "TPP_User=".$USER->Fname. " ". $USER->Lname;
  $to_file .= "\nSet_Date=". @date("F j, Y");
  fwrite($fd_prt, $to_file);
  fclose($fd_prt);
  if($lb_run){
    $condition_file = $parameter_file_folder."/condition_".$frm_setName.".xml";
    $fd_prt = @fopen($condition_file, "w");
    fwrite($fd_prt, $frm_condition);
    fclose($fd_prt);
  }
}

if($frm_myaction == 'yes' or !$frm_myaction){
  $frm_myaction = 'modifySet';
}


//get previous setting
if ($handle = opendir($parameter_file_folder)) {
  while (false !== ($file = readdir($handle))) {
    if (preg_match("/[.]tpp$/", $file, $matchs)) {
      $file = str_replace(".tpp","", $file);
      $set_arr[] = $file;
    }
  }
  closedir($handle);
  if($set_arr)  natcasesort($set_arr);
}
if(!$frm_setName){
  if($set_arr){
    $frm_setName = $set_arr[0];
    $pars_file = $parameter_file_folder."/$frm_setName.tpp";
  }
}
 
if($frm_myaction == 'newSet'){
  $condition_file = $parameter_file_folder."/condition_libra.xml";
  $frm_condition = file_get_contents($condition_file);
  $default = 1;
}else if(isset($pars_file) and  is_file($pars_file) ){
  $fd_prt = @fopen($pars_file, "r");
  if(isset($IT_MODS)) $IT_MODS='';
  if(isset($MODS)) $MODS='';
  while($fd_prt and !feof($fd_prt)){
    $str_tmp = trim(fgets($fd_prt,4076));
    if($str_tmp){
      $tmp_arr = explode('=',$str_tmp,2);
      if(count($tmp_arr) == 2) $$tmp_arr[0] = $tmp_arr[1];
    }
  } 
  fclose($fd_prt);
  $condition_file = $parameter_file_folder."/condition_".$frm_setName.".xml";
  if(!$frm_condition = file_get_contents($condition_file)){
    $condition_file = $parameter_file_folder."/condition_libra.xml";
    $frm_condition = file_get_contents($condition_file);
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
function showConditionFile(theForm){
  obj = document.getElementById("conFile");
  if(theForm.frm_modCon.checked){
    obj.style.display="block";
    if(pre_condition == ''){
      pre_conditon = theForm.frm_condition.value;
    }
  }else{ 
    theForm.frm_condition.value = pre_conditon;
    obj.style.display="none";
  }
} 
function checkForm(theForm){
  if(theForm.xinter_pppfilter.value != ''){
    if(!IsNumeric(theForm.xinter_pppfilter.value)){
      alert('Please enter a numeric (float) value for PeptideProphet ');
      return false;
    }
  }
  if(theForm.xinter_ppiter.value != ''){
    if(!IsNumeric(theForm.xinter_ppiter.value)){
      alert('Please enter a numeric (integer) value for Number of Extra Iterations');
      return false;
    }
  }
  if(theForm.xp_run.checked && !IsNumeric(theForm.xp_mass.value)){
    alert("Please enter a numeric (float) value for XPRESS mass tolerance");
    return false;
  }
  
  
  
  if(theForm.pep_usedecoy.checked && theForm.pep_decoystr.value==''){
    theForm.pep_decoystr.focus();
    alert("enter a value for Decoy protein string");
    return false; 
  }else if(optionSelected(theForm.xp_res1) && !IsNumeric(theForm.xp_res1md.value)){
    alert("Please enter a numeric (float) value for Labeled Residue 1 Mass Difference in XPRESS");
    return false;
  }else if(optionSelected(theForm.xp_res2) && !IsNumeric(theForm.xp_res2md.value)){
    alert("Please enter a numeric (float) value for Labeled Residue 2 Mass Difference in XPRESS");
    return false;
  }else if(optionSelected(theForm.xp_res3) && !IsNumeric(theForm.xp_res3md.value)){
    alert("Please enter a numeric (float) value for Labeled Residue 3 Mass Difference in XPRESS");
    return false;
  }else if(theForm.as_run.checked && !optionSelected(theForm.as_labres1)){
    alert("Please enter a value for (first) Labeled Residue in ASAPRatio");
    return false;
  }
  
  if(theForm.as_area.value != ''){
    if(!IsNumeric(theForm.as_area.value)){
      alert("Please enter a numeric (float) value for Area Flag for ASAPRatio display");
      return false;
    }
  }
  if(theForm.as_mzpeak.value != ''){
    if(!IsNumeric(theForm.as_mzpeak.value)){
      alert("Please enter a numeric (float) value for m/z range to include in summation of peak");
      return false;
    }
  }
  if(optionSelected(theForm.as_res1) && !IsNumeric(theForm.as_res1mass.value)){
    alert("Please enter a numeric (float) value for Labeled Residue 1 Mass in ASAPRatio");
    return false;
  }else if(optionSelected(theForm.as_res2) && !IsNumeric(theForm.as_res2mass.value)){
    alert("Please enter a numeric (float) value for Labeled Residue 2 Mass in ASAPRatio");
    return false;
  }else if(optionSelected(theForm.as_res3) && !IsNumeric(theForm.as_res3mass.value)){
    alert("Please enter a numeric (float) value for Labeled Residue 3 Mass in ASAPRatio");
    return false;
  } 
  theForm.frm_myaction.value = 'yes';
  theForm.submit();
}
function optionSelected(sel){
  var val = sel.options[sel.selectedIndex].value;
  if(val !='--' && val !=''){
    return true;
  }else{
    return false;
  }
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
<br>
<form name=listform method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=frm_myaction value='yes'> 
<table border="0" cellpadding="0" cellspacing="2" width=90%>
  <tr>
   <td><img src='./images/tpp.gif' border=0></td>
   <td><b><font color='red' face='helvetica,arial,futura' size='3'>TPP Parameters</font></b><br>
     Create or modify TPP parameter set <a onClick="newpopwin('./tpp_help.html',600,700)" class=button><img src='./images/help2.gif' border=0></a>
  </td>
 </tr>
 <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
 </tr>
 <tr>
   <td colspan=2>
    <?php 
    if($TPP_User){
      echo "Set by: <b>".$TPP_User ."</b> &nbsp;&nbsp;&nbsp;  Set Date: <b>" .$Set_Date ."</b>";
    }
    ?>
   </td>
 </tr>
 <tr>
  <td colspan=2>
  
  
<table cellspacing=0 border=0>
<tr>
<td class=TPPentryhead>&nbsp;Filter Options&nbsp;&nbsp;&nbsp;</td>
</tr>
</table>
<div id=pep class=TPPentry>
Filter out results below this PeptideProphet probability: <input type="text" name="xinter_pppfilter" value="<?php echo ($xinter_pppfilter and !$default)?$xinter_pppfilter:'0.05';?>" size="5" maxlength="5"><br>
Number of extra iterations to be computed by PeptideProphet after convergence is detected: <input type="text" name="xinter_ppiter" value="<?php echo ($xinter_ppiter and !$default)?$xinter_ppiter:'20';?>" size="5" maxlength="3"></div>
<br>
<table cellspacing=0>
<tr>
<td id=pep_head class=TPPentryhead>&nbsp;PeptideProphet Options&nbsp;&nbsp;&nbsp;</td>
 
</table>

<div id=pep class=TPPentry>

<input type="checkbox" name="pep_run" value="true" checked disabled>RUN PeptideProphet &nbsp; &nbsp;<a onClick="newpopwin('http://tools.proteomecenter.org/wiki/index.php?title=Software:PeptideProphet',800,500)"><img src='images/help2.gif' border=0></a><br>
<input type="checkbox" name="pep_icat" value="i"<?php echo ($pep_icat and !$default)?' checked':'';?>>Use icat information<br>
<input type="checkbox" name="pep_noicat" value="f"<?php echo ($pep_noicat and !$default)?' checked':'';?>>Do not use icat information<br>
<input type="checkbox" name="pep_nglyc" value="g"<?php echo ($pep_nglyc and !$default)?' checked':'';?>>Use N-glyc motif information<br>
<input type="checkbox" name="pep_pI" value="I"<?php echo ($pep_pI and !$default)?' checked':'';?>>Use pI information<br>
<input type="checkbox" name="pep_hydro" value="R"<?php echo ($pep_hydro and !$default)?' checked':'';?>>Use Hydrophobicity / RT information<br>
<input type="checkbox" name="pep_accmass" value="A"<?php echo ($pep_accmass and !$default)?' checked':'';?>>Use accurate mass binning<br>
<input type="checkbox" name="pep_nontt" value="N"<?php echo ($pep_nontt or $default)?' checked':'';?>>Do not use the NTT model<br>
<input type="checkbox" name="pep_maldi" value="m"<?php echo ($pep_maldi and !$default)?' checked':'';?>>MALDI data<br>
<input type="checkbox" name="pep_xclaster" value="x"<?php echo ($pep_xclaster and !$default)?' checked':'';?>>Exclude all entries with asterisked score values<br>
<input type="checkbox" name="pep_nclaster" value="l"<?php echo ($pep_nclaster and !$default)?' checked':'';?>>Leave alone all entries with asterisked score values<br>
<input type="checkbox" name="pep_usedecoy" value="-d"<?php echo ($pep_usedecoy and !$default)?' checked':'';?>>Use decoy hits to pin down the negative distribution.
Decoy Protein names begin with: <input type="text" name="pep_decoystr"  size="15" maxlength="50" value='<?php echo ($pep_decoystr  and !$default)?$pep_decoystr:'';?>'>(whitespace not allowed)<br>
<br>
<input type="checkbox" name="pep_prot" value="true" checked disabled>Run ProteinProphet afterwards &nbsp; &nbsp;<a onClick="newpopwin('http://tools.proteomecenter.org/wiki/index.php?title=Software:ProteinProphet',800,500)"><img src='images/help2.gif' border=0></a><br>
<input type="checkbox" name="pep_ngrps" value="u"<?php echo ($pep_ngrps and !$default)?' checked':'';?>>Do not assemble protein groups in ProteinProphet analysis<br>
<input type="checkbox" name="pep_occ" value="s"<?php echo ($pep_occ and !$default)?' checked':'';?>>Do not use Occam&#39;s Razor in ProteinProphet analysis to derive the simplest protein list to explain observed peptides
</div><br>
<table cellspacing=0>
<tr>
<td id=xpress_head class=TPPentryhead>&nbsp;XPRESS Options&nbsp;&nbsp;&nbsp;</td><td>&nbsp; &nbsp;<a onClick="newpopwin('http://tools.proteomecenter.org/wiki/index.php?title=Software:XPRESS',800,500)"><img src='images/help2.gif' border=0></a></td></tr>
</table>
<div id=xpress class=TPPentry>
<input type="checkbox" name="xp_run" value="-X"<?php echo ($xp_run and !$default)?' checked':'';?>>RUN XPRESS<br>
Change XPRESS mass tolerance: <input type="text" name="xp_mass" value="<?php echo ($xp_mass and !$default)?$xp_mass:'1.0';?>" size="10" maxlength="10" ><br>
<input type="radio" name="xp_fix" value="-L"<?php echo ($xp_fix == "-L"  and !$default)?' checked':'';?>>For ratio, set/fix light to 1, vary heavy<br>	
<input type="radio" name="xp_fix" value="-H"<?php echo ($xp_fix == "-H" and !$default)?' checked':'';?>>For ratio, set/fix heavy to 1, vary light
<br><br>
<input type="checkbox" name="xp_heavy" value="-b"<?php echo ($xp_heavy  and !$default)?' checked':'';?>>Heavy labeled peptide elutes before light labeled partner<br>Change XPRESS residue mass difference: 
<select name="xp_res1" >
<option value="--">--</option>
<?php 

foreach($aa_array as $value){
  $selected = '';
  if($value == $xp_res1  and !$default) $selected = ' selected';
  echo "<option value='$value'$selected>$value</option>\n";
}
?>
</select>
<input type="text" name="xp_res1md" value="<?php echo ($xp_res1md and !$default)?$xp_res1md:'9.0';?>" size="10" maxlength="10"><br>
Change XPRESS residue mass difference: <select name="xp_res2" >
<option value="--">--</option>
<?php 
foreach($aa_array as $value){
	$selected = '';
  if($value == $xp_res2 and !$default) $selected = ' selected';
  echo "<option value='$value'$selected>$value</option>\n";
}
?>
</select><input type="text" name="xp_res2md" value="<?php echo ($xp_res2md and !$default)?$xp_res2md:'9.0';?>" size="10" maxlength="10"><br>
Change XPRESS residue mass difference: <select name="xp_res3" >
<option value="--">--</option>
<?php 
foreach($aa_array as $value){
	$selected = '';
  if($value == $xp_res3 and !$default) $selected = ' selected';
  echo "<option value='$value'$selected>$value</option>\n";
}
?>
</select><input type="text" name="xp_res3md" value="<?php echo ($xp_res3md and !$default)?$xp_res3md:'9.0';?>" size="10" maxlength="10"></div><br>
<table cellspacing=0>
<tr>
<td id=asap_head class=TPPentryhead>&nbsp;ASAPRatio Options&nbsp;&nbsp;&nbsp;</td><td> &nbsp; &nbsp;&nbsp; &nbsp;<a onClick="newpopwin('http://tools.proteomecenter.org/wiki/index.php?title=Software:ASAPRatio',800,500)"><img src='images/help2.gif' border=0></a></td></tr>
</table>
<div id=asap class=TPPentry>
	<input type="checkbox" name="as_run" value="-A"<?php echo ($as_run and !$default)?' checked':'';?>>RUN ASAPRatio<br>
	<input type="checkbox" name="as_static" value="-S"<?php echo ($as_static and !$default)?' checked':'';?>>Static modification quantification (i.e. each run is either all light or all heavy)<br><br>
Change labeled residues to <select name="as_labres1" >
<option value="--">--</option>
<?php 
foreach($aa_array as $value){
	$selected = '';
  if($default) $as_labres1 = 'C';
  if($value == $as_labres1) $selected = ' selected';
  echo "<option value='$value'$selected>$value</option>\n";
}
?>
</select>

<select name="as_labres2" >
<option value="--">--</option>
<?php 
foreach($aa_array as $value){
	$selected = '';
  if($value == $as_labres2 and !$default) $selected = ' selected';
  echo "<option value='$value'$selected>$value</option>\n";
}
?>
</select><select name="as_labres3" >
<option value="--">--</option>
<?php 
foreach($aa_array as $value){
	$selected = '';
  if($value == $as_labres3 and !$default) $selected = ' selected';
  echo "<option value='$value'$selected>$value</option>\n";
}
?>
</select><select name="as_labres4" >
<option value="--">--</option>
<?php 
foreach($aa_array as $value){
	$selected = '';
  if($value == $as_labres4 and !$default) $selected = ' selected';
  echo "<option value='$value'$selected>$value</option>\n";
}
?>
</select><select name="as_labres5" >
<option value="--">--</option>
<?php 
foreach($aa_array as $value){
	$selected = '';
  if($value == $as_labres5 and !$default) $selected = ' selected';
  echo "<option value='$value'$selected>$value</option>\n";
}
?>
</select><br>
<input type="checkbox" name="as_heavy" value="-b"<?php echo ($as_heavy and !$default)?' checked':'';?>>Heavy labeled peptide elutes before light labeled partner<br>
<input type="checkbox" name="as_fixedscan" value="-F"<?php echo ($as_fixedscan and !$default)?' checked':'';?>>Use fixed scan range for Light and Heavy<br>
<input type="checkbox" name="as_cidonly" value="-C"<?php echo ($as_cidonly and !$default)?' checked':'';?>>Quantitate only the charge state where the CID was made<br>
Set areaFlag to <input type="text" name="as_area"  size="10" maxlength="10" value='<?php echo ($as_area and !$default)?$as_area:'';?>'> (ratio display option) <br>
<br>
<input type="checkbox" name="as_zerobg" value="-Z"<?php echo ($as_zerobg and !$default)?' checked':'';?>>Zero out all background<br>
<input type="checkbox" name="as_highbgok" value="-B"<?php echo ($as_highbgok and !$default)?' checked':'';?>>Quantitate despite high background<br>
m/z range to include in summation of peak:<input type="text" name="as_mzpeak" value="<?php echo (!$default)?$as_mzpeak:'0.5';?>" size="10" maxlength="10"><br>
<br>
Specified label mass 1: <select name="as_res1" >
<option value="--">--</option>
<?php 
foreach($aa_array as $value){
	$selected = '';
  if($value == $as_res1 and !$default) $selected = ' selected';
  echo "<option value='$value'$selected>$value</option>\n";
}
?>
</select><input type="text" name="as_res1mass"  size="10" maxlength="10" value='<?php echo ($as_res1mass and !$default)?$as_res1mass:"";?>'> *<br>
Specified label mass 2: <select name="as_res2" >
<option value="--">--</option>
<?php 
foreach($aa_array as $value){
	$selected = '';
  if($value == $as_res2 and !$default) $selected = ' selected';
  echo "<option value='$value'$selected>$value</option>\n";
}
?>
</select><input type="text" name="as_res2mass"  size="10" maxlength="10" value='<?php echo ($as_res2mass and !$default)?$as_res2mass:"";?>'> *  only relevant for static modification quantification<br>
Specified label mass 3: <select name="as_res3" >
<option value="--">--</option>
<?php 
foreach($aa_array as $value){
	$selected = '';
  if($value == $as_res3 and !$default) $selected = ' selected';
  echo "<option value='$value'$selected>$value</option>\n";
}
?>
</select><input type="text" name="as_res3mass"  size="10" maxlength="10" value='<?php echo ($as_res3mass and !$default)?$as_res3mass:"";?>'> *</div><br>
<table cellspacing=0>
<tr>
<td id=libra_head class=TPPentryhead>&nbsp;Libra Quantification Options&nbsp;&nbsp;&nbsp;</td><td>&nbsp; &nbsp;&nbsp; &nbsp;<a onClick="newpopwin('http://tools.proteomecenter.org/wiki/index.php?title=Software:Libra',800,500)"><img src='images/help2.gif' border=0></a></td></tr>
</table>

<div id=libra class=TPPentry>
<input type="checkbox" name="lb_run" value="-L"<?php echo ($lb_run and !$default)?' checked':'';?>>RUN Libra<br>
Condition File: <input type="text" name="lb_condition" value="condition_<?php echo ($frm_setName and !$default)?"$frm_setName":"default";?>.xml" size="20" maxlength="100" readonly> 
Modify condition file <input type="checkbox" name="frm_modCon" value="true" onClick="showConditionFile(this.form)">
<br>
  <div id=conFile class=TPPentry style="display: none";>
  <textarea cols="70" rows="13" name="frm_condition"><?php echo $frm_condition;?></textarea><br>
  Modify the contents or Click <a href=http://db.systemsbiology.net/webapps/conditionFileApp/ target=new>HERE</a> to generate condition file then paste in the text box.
  </div>
Normalization channel: <select name="lb_channel" >
<?php 
for($i = 1; $i < 5; $i++){
  $selected = '';
  if($lb_channel == $i and !$default) $selected = ' selected';
  echo "<option value='$i'$selected>$i</option>\n";
}
?>
</select> (for protein level quantification)</div><br>
</td>
</tr>
<tr>
   <td colspan=2><br><b><font color='red' face='helvetica,arial,futura' size='3'>Parameter Set</font></b>
   <?php if($perm_modify){?>&nbsp; &nbsp;
   New Set<input type=radio value=newSet name=frm_set onClick="isNewSet(this.form, true)" <?php echo ($frm_myaction == 'newSet')?'checked':'';?>>
   Modify Set<input type=radio value=modifySet name=frm_set onClick="isNewSet(this.form, false)" <?php echo ($frm_myaction == 'modifySet')?'checked':'';?>>
   <?php 
   }else{
     echo " &nbsp; You have no permission to change the setting.";
   }
   ?> 
  </td>
 </tr>
<tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
 </tr>
 <tr>
   <td colspan=2 align=center>
    <?php if($TPP_User and $frm_myaction != 'newSet'){?>
        Set by: <b><?php echo $TPP_User;?></b>&nbsp; &nbsp; &nbsp;  Set date:<b><?php echo $Set_Date;?></b> &nbsp; &nbsp; &nbsp; &nbsp; 
    <?php 
   }
   if($frm_myaction == 'newSet'){
       echo 'New set name: <input type=text size=10 name=frm_setName>';
   }else{
        
       if($set_arr){
          
         echo "Set name: <select name=frm_setName onChange=\"isNewSet(this.form, false)\">\n";
         for($i = 0; $i < count($set_arr); $i++){
           $selected = ($set_arr[$i] == $frm_setName)?" selected":"";
           echo "<option value='" . $set_arr[$i] . "'$selected>$set_arr[$i]\n";
         }
         echo "</select>\n";
       }else{
         echo "<font color=\"#FF0000\">Please create new parameter set.</font>";
       }
    } 
    ?>
   </td>
 </tr>
 
 <tr>
  <td colspan=2>
   <br><center>
   <?php 
   if($perm_modify and ($frm_myaction == 'newSet' or $frm_setName) and $frm_myaction != 'show'){
    echo "&nbsp; &nbsp; &nbsp;  <input type=button value='Save' onClick=\"checkForm(this.form)\">\n";
   }
   ?>
   <input type=reset value='Reset' name=reset>
   <input type="button" value="Close" onClick="window.close()">
   </center>
  </td>
  </tr>
</tr>
</table>
</form>
<?php 
include("./ms_footer_simple.php");
?>
