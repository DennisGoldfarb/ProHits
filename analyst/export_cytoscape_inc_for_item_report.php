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

$SearchEngine = $hitType;
$frm_selected_item_str = $baitStr;
$currentType = 'Bait';

$orderbyTmpArr = explode(" ", $orderby);
$orderby = $orderbyTmpArr[0];
if($hitType == 'TPP'){
  $orderby = tpp_table_field_translate_for_hits($orderby);
}
$groupInfo = '';
$tmp_item_id = $item_ID;
if($type == "Sample"){
  $item_name = "Sample___Location";
  $item_id = "Sample___ID";
  $SQL = "SELECT S.ID,
                 S.Location AS Name, 
                 B.GeneID,
                 B.GeneName,
                 B.BaitAcc
                 FROM Band S 
                 LEFT JOIN Bait B
                 ON S.BaitID=B.ID 
                 WHERE S.ID='$tmp_item_id'";
}elseif($type == "Experiment"){
  $item_name = "Experiment___ExpName";
  $item_id = "Experiment___ExpID";
  $SQL = "SELECT E.ID,
                 E.Name, 
                 B.GeneID,
                 B.GeneName,
                 B.BaitAcc
                 FROM Experiment E 
                 LEFT JOIN Bait B
                 ON E.BaitID=B.ID 
                 WHERE E.ID='$tmp_item_id'";
}else{
  if($type != "Bait"){
    $tmp_item_id = $baitStr;
  }
  $item_name = "Bait___GeneName";
  $item_id = "Bait___BaitID";
  $SQL = "SELECT ID,
                 GeneID,
                 GeneName AS Name,
                 BaitAcc
                 FROM Bait
                 WHERE ID IN ($tmp_item_id)";
}
$itemLableInfo_arr = $HITSDB->fetchAll($SQL);
$itemLableInfo = '';
foreach($itemLableInfo_arr as $itemLableInfo_val){
  
  $tmp_str = $itemLableInfo_val['ID']." ".$itemLableInfo_val['Name']." ".$itemLableInfo_val['GeneID'];
  if($itemLableInfo) $itemLableInfo .= ',';
  $itemLableInfo .= $tmp_str;
}

if($hitType == 'normal'){
  $selecte_columns_str = "$item_name@Bait___GeneID@level3___GeneName@level3___GeneID@level3___HitGI@level3___Expect@level3___Pep_num@level3___Pep_num_uniqe@level3___Coverage@level3___Frequency@$item_id";
}elseif($hitType == 'TPP'){
  $selecte_columns_str = "$item_name@Bait___GeneID@level3___GeneName@level3___GeneID@level3___ProteinAcc@level3___PROBABILITY@level3___TOTAL_NUMBER_PEPTIDES@level3___UNIQUE_NUMBER_PEPTIDES@level3___PERCENT_COVERAGE@level3___Frequency@$item_id";    
}elseif($hitType == 'geneLevel'){
  $selecte_columns_str = "$item_name@Bait___GeneID@level3___GeneName@level3___GeneID@level3___Subsumed@level3___SpectralCount@level3___Unique@level3___Frequency@$item_id";
}


$mapfileDelimit = ",";
$fileExtention = "csv";
$theaction = "generate_report";
$level_header_arr = array();
if($hitType == 'normal'){
  $LableArr['level3'] = $hitsLableArr;
  $LableArr['level4'] = $PeptideLableArr;
  $level_header_arr['level3'] = 'Hit';
  $level_header_arr['level4'] = 'Peptide';
  $level_header_arr_R['Hit'] = 'level3';
  $level_header_arr_R['Peptide'] = 'level4';
}elseif($hitType == 'TPP'){
  $LableArr['level3'] = $TPPLableArr;
  $LableArr['level4'] = $TppPeptideGlableArr;
  $level_header_arr['level3'] = 'TPP Protein';
  $level_header_arr['level4'] = 'TPP Protein Group Peptide';
  $level_header_arr_R['TPP Protein'] = 'level3';
  $level_header_arr_R['TPP Protein Group Peptide'] = 'level4';
}elseif($hitType == 'TPPpep'){
  $LableArr['level3'] = $TppPeptideLableArr;
  $level_header_arr['level3'] = 'TPP Peptide';
  $level_header_arr_R['TPP Peptide'] = 'level3';
}elseif($hitType == 'geneLevel'){  
  $LableArr['level3'] = $geneLevel_hitsLableArr;
  $LableArr['level4'] = $geneLevel_PeptideLableArr;
  $level_header_arr['level3'] = 'Hit';
  $level_header_arr['level4'] = 'Peptide';
  $level_header_arr_R['Hit'] = 'level3';
  $level_header_arr_R['Peptide'] = 'level4';
}
if($type == 'Sample'){
  $index = 'Band';
}else{
  $index = $type;
}
if($hitType == 'TPP'){
  $powerArr['Expect'] = 1;
}else{
  $powerArr['Expect'] = 1/2;
}  
$powerArr['Expect2'] = 1/2;
$powerArr['Pep_num'] = 1/2;
$powerArr['Pep_num_uniqe'] = 1;
$powerArr['Coverage'] = 1;     
$powerArr['Fequency'] = 1;

if($orderby == 'Expect2'){
  $MAX = 'MIN';
}else{
  $MAX = 'MAX';
}

if($orderby == 'Fequency'){
  $powerColorIndex = 'Fequency';
}else{
  $powerColorIndex = $orderby;
}

if($orderby == 'Fequency'){
	$maxScore = $fequMaxScore;
}else{
  $maxScore = get_max_value($orderby);
}

$power = $powerArr[$powerColorIndex]; //1/2, 1/3, 1......
$biggestPowedSore = pow($maxScore,$power);
if($orderby != 'Expect2' && $biggestPowedSore <= 0) $biggestPowedSore = 1;
$frm_selected_item_str = $baitStr;
$SQL = "SELECT `GeneID` FROM `Bait` WHERE `ID` IN ($baitStr)";
$baitGeneIDarr_tmp = $HITSDB->fetchAll($SQL);
$baitGeneIDarr = array();
foreach($baitGeneIDarr_tmp as $tmpVal){
  array_push($baitGeneIDarr, $tmpVal['GeneID']);
}
$Delimit = ",";  
include("export_hits_inc.php");
?>