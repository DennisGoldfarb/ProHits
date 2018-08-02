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

/*************************************************************************************
description:
this is a pop window page from /msManager/ms_results_detail.php.
The open window action is in ./ato_save_from.inc.php.
A other_value variable will be passed to the script. If the variable contains no value.
the default value will be used.
***************************************************************************************/
$other_value = '';
$frm_is_modified_peptide = '';

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}

if($other_value){
  $tmp_arr = explode(";", $other_value);
  
  for($i=0;$i<count($tmp_arr);$i++){
    $name_value = explode(":", $tmp_arr[$i]);
    $tmp_name = "frm_" . $name_value[0];
    $$tmp_name = $name_value[1];
  }
}
?>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1252">
<title>ms data management -- Peptide Filter Conf</title>
<link rel="stylesheet" type="text/css" href="./ms_style.css">
</head>
<body bgcolor="#deefef">
<script language='javascript'>

function applyDefault(){
  setDefault();
  setTimeout("passValueToOpenner()", 1300);
}
function passValueToOpenner(){
   var value_str = "";
   value_str += "peptide_min_size:"+document.forms[0].frm_peptide_min_size.value;
   value_str += ";matched_ion_percentage:"+document.forms[0].frm_matched_ion_percentage.value;
   value_str += ";peptide_min_charge:"+document.forms[0].frm_peptide_min_charge.value;
   value_str += ";matched_ions_group_size:"+document.forms[0].frm_matched_ions_group_size.value;
   value_str += ";matched_ions_num:" + document.forms[0].frm_matched_ions_num.value;
   if(document.forms[0].frm_is_modified_peptide.checked != true){
     document.forms[0].frm_is_modified_peptide.value = "0";
   }
   value_str += ";is_modified_peptide:"+document.forms[0].frm_is_modified_peptide.value;
   opener.document.forms[0].frm_other_value.value = value_str;
   window.close();
}
function setDefault(){
  document.forms[0].frm_peptide_min_size.value = "0";
  document.forms[0].frm_matched_ion_percentage.value = "0";
  document.forms[0].frm_peptide_min_charge.value = "2";
  document.forms[0].frm_matched_ions_group_size.value = "6";
  document.forms[0].frm_matched_ions_num.value = "4";
  document.forms[0].frm_is_modified_peptide.checked = false;
}
</script>
<form method=post name=peptide_filter>
<b><a name='pepFilter'>Auto-Save in hits database</a></b><br><br>
<font size="2">
Since peptide validation is a tedious ordeal, we have been developing a function called 
Peptide Filter to automatically validate mass spectra and save peptide information in 
ProHits. Prior to this, we had to manually open a Mascot spectrum, 
study the b- and y-ion profile and decide whether or not a peptide is present, 
and manually save the information accordingly. 

The new function examines every Mascot spectrum and 'decides' whether the data represents 
a true peptide or a false positive. The user can decide how stringently the filter should 
work (see below). It is important to realize that this is a peptide filter and NOT a protein 
filter, thus peptides from abundant proteins (such as heat shocks or baits) will be saved to 
ProHits. These background proteins can later be filtered out using the protein filter. 
The sole function of Peptide Filter is to validate whether a peptide is present (or not) 
using user defined criteria.

<ol>
<li>
Only a user who has permission can save searched results into ProHits database.
</li>
<li>
User can select search time (now, midnight, early morning).
</li>
<li>
The program can work on multiple plates simultaneously.
</li>
<li>
After a plate has been completely saved, a report will be sent to the user. 
</li>
<li>
The program will save data to corresponding database (yeast or mammalian) based on plate name. 
</li>
<li>
Sample IDs (i.e. well number) must match raw data file name. If a mistake is detected (i.e. Sample ID does not match the raw data file name), the results will not be saved, and a warning will be added to the report e-mail. The user must then ask the administrator to change the data file name and re-save the data.
</li>
<li>
After a Sample result has been saved, the box next to the sample name will be checked. After an entire pleat has been saved, the user can still delete saved hits from the database.
</li>
<li>
The user can define the following Peptide Filter criteria:
    <ol>
	<li type="a">
Save all hits that score greater than user defined value. If user select 'save all hits', all hits will be saved without applying Peptide Filter.
        </li>
    	<li type="a">
If the peptide validation box is checked, a hit that scores less than user selected will be sent to Peptide Filter to check if the hit contains any true peptide.
       </li>
       <li type="a">
User defined criteria for Peptide Filter.
       </li>
	<li type="a">
Minimum peptide size in amino acid residues
<input type="text" name="frm_peptide_min_size" value="<?php echo $frm_peptide_min_size;?>" size="2" maxlength="2">(default = <b>0</b>).
       </li>
	<li type="a">
Minimum matched ion percentage
<input type="text" name="frm_matched_ion_percentage" value="<?php echo $frm_matched_ion_percentage;?>" size="2" maxlength="2">%(default = <b>0%</b>).
        </li>
	<li type="a">
Minimum peptide charge >=+
<input type="text" name="frm_peptide_min_charge" value="<?php echo $frm_peptide_min_charge;?>" size="1" maxlength="2">(default >= <b>2+</b>)
        </li>
	<li type="a">Ion species (default settings):<br>
Minimum matched ions
<input type="text" name="frm_matched_ions_num" value="<?php echo $frm_matched_ions_num;?>" size="1" maxlength="2">
within a sequence of 
<input type="text" name="frm_matched_ions_group_size" value="<?php echo $frm_matched_ions_group_size;?>" size="1" maxlength="2">
residues. <br>
    ( default.<br>
     For 2+ peptides, there must be a minimum of either [<b>4</b>] b-ions or [<b>4</b>] y-ions within a sequence of [<b>6</b>] residues<br> 
     For 3+ peptides, there must be a minimum of either [4] b-ions or [4] y-ions within a sequence of [6] residues AND a minimum of either [4] b++-ions or [4] y++-ions within a sequence of [6] residues 
    )
    </li>
	<li type="a">
Has to be modified peptide.
<input type="checkbox" name="frm_is_modified_peptide" value="1" <?php echo ($frm_is_modified_peptide == "1")?"checked":"";?>>(default = <b>unchecked</b> )
        </li>
    </ol>
</li>
</ol>
<center>
<?php 
if(!$other_value){
  echo "<script language='javascript'>setDefault();</script>";
  echo "<font color=red><b>default value</b></font><br>";
}else{
  echo "<font color=green><b>previous setting</b></font><br>";
}
?>
<input type=button value="Apply Default value" OnClick="applyDefault()">
<input type=button value="Save Change" OnClick="passValueToOpenner()">
<input type=reset>

</center>
</font>

</form>
</body>

</html>
