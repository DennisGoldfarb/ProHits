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

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
$is_reincluded = '';
$bgcolordark = "#94c1c9";
$bgcolor = "white";
$subHeadFlag = 1;
$type = 'Sample';
$BaitArr['GeneID'] = $Bait->GeneID;
$BaitArr['GelFree'] = $Bait->GelFree;
$frequencyLimit = $_SESSION["workingProjectFrequency"];
$handle = '';

$expect_exclusion_color="#93ffff";
$item_color="red";
$excludecolor = '#dbdbdb';//"#a7a7a7";
$is_reincluded = '';
$tmpCounter = 0;
$totalGenes = get_total_genes();
$workingFilterSetID = $_SESSION['workingFilterSetID'];
$NSfilteIDarr = array();
$typeBioArr = array();
$typeExpArr = array();
$typeFrequencyArr = array();

$URL = getURL();
$subHeadFlag = 1;

if(!$item_hits_order_by){
  if($hitType == 'TPPpep'){
    $item_hits_order_by = 'PROBABILITY desc';
  }elseif($hitType == 'TPP'){
    $item_hits_order_by = 'TOTAL_NUMBER_PEPTIDES desc';
  }else{
    $item_hits_order_by = 'Pep_num desc';
  }
}
$frm_selected_item_str = $Band_ID;
if(!isset($searchEngineField) || !$searchEngineField){
  $searchEngineField = get_default_searchEngine($frm_selected_item_str,$hitType);
}
$frequencyArr = array();
if($hitType == 'TPP'){
  $frequencyFileName = 'tpp_frequency.csv';
}elseif($hitType == 'normal'){
  $frequencyFileName = $searchEngineField.'_frequency.csv';
}else{
  $frequencyFileName = '';
}
$fequMaxScore = get_frequency_arr($frequencyArr,$frequencyFileName);
$giArr = array();
?>
<script language='javascript'>
 function hitedit(Hit_ID){
  file = 'hit_editor.php?Hit_ID=' + Hit_ID;
  newwin = window.open(file,"Hit",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=480,height=400');
 }
</script>
<br>
<div style="width:95%;border: red solid 1px;border: black solid 0px;">
<table border="0" cellpadding="0" cellspacing="1" width="100%">
<?php 
  $SearchEngineConfig_arr = get_project_SearchEngine();
  print_table_head();
  $hits_result = '';
  $optionBgColor = '';
  $fileColorCounter = 0;
  if(!isset($start_point) || !$start_point) $start_point = 0;
  $colorArr = array();
  get_color_arr($colorArr);
  $fileColorIndexArr = array();
  get_hits_result($Band_ID, $hits_result, $searchEngineField, $hitType);
  $arr2_value['ID'] = $Band_ID;
  $arr2_value['Location'] = $Location;
  $note_exist_arr = array();
  $tmp_level_2_arr[0]['ID'] = $Band_ID;
  get_exist_note_hitsID_arr($tmp_level_2_arr,$note_exist_arr);
  include("item_report_hits.inc.php");
?>
</table>
</div>
<input type=hidden name=item_hits_order_by value=''>
<input type=hidden name=start_point value='<?php echo $start_point?>'>
<input type=hidden name=searchEngineField value='<?php echo $searchEngineField?>'>
<br>
<?php  
arsort($giArr);
?>
<style type="text/css">
<?php 
foreach($giArr as $key => $value){
  if(count($value) > 1){
    $tmpArr = explode(",",$key);  
    echo ".gi".$tmpArr[0]."\n";
    echo "{ font-weight : bold; COLOR: #525252;}\n";
  }  
}
?>
</style>
<DIV id="pop_same_gene" class=maintext></DIV>
<script language=javascript>

function showSameGene(evt,proteinID){
  var protein_info =  new Array();
  var protein_index = new Array();
<?php 
$index_num = 0;
foreach($giArr as $giKey => $giVal){
  if(count($giVal) <= 1) continue;
  $tmpArr = explode(",",$giKey);
  $tmpStr = $giKey."@";
  $tmpStr2 = '';
  foreach($giVal as $giVal2){
    if($tmpStr2) $tmpStr2 .= ":";
    $tmpStr2 .= $giVal2;
  }
  $tmpStr .= $tmpStr2;
?>
  protein_index[<?php echo $index_num?>] = "<?php echo $tmpArr[0]?>"; 
  protein_info[<?php echo $tmpArr[0]?>] = "<?php echo $tmpStr?>"; 
<?php 
  $index_num++;
}
$scoreLable = "Score";
if($hitType == 'TPP') $scoreLable = "Probability";
?>
  var flag = 0;
  for(var i=0; i<protein_index.length; i++){
    if(proteinID == protein_index[i]){
      flag = 1;
      break;
    }
  }
  if(flag == 0) return;
  protein_atr_str = protein_info[proteinID];
	var obj = document.getElementById("pop_same_gene");
  var TDS = "<td><div class=maintext>"
  var TdE = "</div></td>";
  
  var tmpArr = protein_atr_str.split("@");
  var tmpArr2 = tmpArr[0].split(",");
  var titleStr = "Same Gene Found in this page<br><b>Gene ID</b>:&nbsp;"+tmpArr2[0]+"&nbsp;&nbsp;<b>Gene Name</b>:&nbsp;"+tmpArr2[1]+"\n";
  var htmlStr = "<div class=tableheader_title>"+titleStr+"</div>\n";
  var tableStr = "<table border=0 cellpadding='1' cellspacing='1' width='100%'>\n";
  htmlStr += tableStr;
  fieldNameStr = "<tr bgcolor='#a85400'><td><div class=tableheader_light>Bait ID</div></td><td><div class=tableheader_light>Sample ID</div></td><td><div class=tableheader_light>Sample Name</div></td><td><div class=tableheader_light>Protein ID</div></td><td><div class=tableheader_light><?php echo $scoreLable?></div></td><td><div class=tableheader_light>Unique Peptide</div></td></tr>\n";
  htmlStr += fieldNameStr;
  tmpArr2 = tmpArr[1].split(":");
  var tr_line = "";
  for(var m=0; m<tmpArr2.length; m++){
    var line_info = tmpArr2[m].split(",");
    tr_line += "<tr>";
    for(var n=0; n<line_info.length; n++){
      tr_line += "<td bgcolor='#f7e0c1'><div class=maintext>"+line_info[n]+"</div></td>";
    }
    tr_line += "</tr>\n";
  }
  tr_line += "</table>\n";
  htmlStr += tr_line;
  obj.innerHTML = htmlStr;
  
  var xl = 10;
  var yl = 20;
  if(isNav){
    obj.style.left = evt.pageX + xl + "px";
  	obj.style.top = evt.pageY + yl + "px";
  }else{
    obj.style.left = window.event.clientX  + document.body.scrollLeft + xl + "px";
  	obj.style.top = window.event.clientY + document.body.scrollTop+ yl + "px";
  }
  obj.style.display="block";
  obj.style.position="absolute";
  obj.style.border="black solid 1px";
  obj.style.backgroundColor  = "white";
  obj.style.width= "300px";
}

function hideSameGene(){ 
	var obj = document.getElementById("pop_same_gene");
  obj.style.display="none";
  obj.style.position="";
  obj.style.border="";
  obj.style.backgroundColor="";
}
</script>