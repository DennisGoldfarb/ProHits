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
$filter_for = 'item_report';
$type = '';
$expect_exclusion_color="#93ffff";
$item_color="red";
$excludecolor = '#dbdbdb';//"#a7a7a7";
$bgcolordark = "#336fd0";
$bgcolor = "white";
$frm_selected_band = '';
$allBandsStr = '';
$start_point = 0;
$hits_colspan = 15;
$searchEngineField = '';

$sub = '';
$submitted = 0;
$whichitem ='';
$img_total = 0;
$is_reincluded = '';
$typeBioArr = array();
$typeExpArr = array();
$typeExpArr_tmp = array();
$hitType = 'normal';
//----
$typeFrequencyArr = '';
$frm_Expect_check = '';
$frm_Expect2_check = '';
$frm_Cov_check = '';
$frm_PT_check = '';
$frm_filter_Peptide = '';
//---Tpp
$frm_tppProbability = '';
$frm_tppCoverage = '';
$frm_tppUniquePep = '';
$frm_tppTotalPep = '';
//---TppP
$frm_tppProbability = '';
$frm_tppHyperscore = '';
$frm_tppIonPCT = '';
$frm_ExlCharge1 = '';
$frm_ExlCharge2 = '';
$frm_ExlCharge3 = '';
$exp_arr = array();
$item_hits_order_by = '';
$frm_selected_item_str = '';

$frm_filter_Fequency = 'Fequency';

$subQueryString = '';
$subWhere = '';
$subWhere2 = '';
$BaitID = '';

$fileDelimit = ",";
$isGelFree = 0;
$filerLable_css  = 'maintext';
$php_file_name = "item_report";
$score_lable = '';

$tr_bgcolor = '#e3e3e3';
$tr_title_bgcolor = 'white';
$edgeLine_color = "#b7c1c8";
$is_show_filter = '';
$filterStyleDisplay = '';
$this_projectID = '';
$DB_name = '';
$title_lable = '';
$OF_session_id = '';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php"); 
include("analyst/comparison_common_functions.php");
require_once("msManager/is_dir_file.inc.php");
require("analyst/status_fun_inc.php");
require("analyst/site_header.php");


if($this_projectID){
  $SQL = "SELECT `ID`,`Name`,`TaxID`, `DBname`,`Frequency`,`FilterSetID` FROM `Projects` WHERE `ID`='$this_projectID'";
  $tmp_arr = $PROHITSDB->fetch($SQL);
  if(!$tmp_arr) exit;
  $_SESSION["workingProjectID"] = $AccessProjectID = $tmp_arr['ID'];
  $_SESSION["workingProjectName"] = $AccessProjectName = $tmp_arr['Name'];
  $_SESSION["workingProjectTaxID"] = $AccessProjectTaxID = $tmp_arr['TaxID'];
  $_SESSION["workingFilterSetID"] = $AccessProjectSetID = $tmp_arr['FilterSetID'];
  $_SESSION["workingProjectFrequency"] = $AccessProjectFrequency = $tmp_arr['Frequency'];
  $_SESSION["workingDBname"] = $AccessDBname = $HITS_DB[strtolower($tmp_arr['DBname'])];
  $HITSDB = new mysqlDB($AccessDBname);
}

$DB_name = $HITSDB->selected_db_name;
$exist_Hits_tables_arr = exist_hits_table($DB_name);

require("export_lable_arrs.inc.php");
if(!$item_ID){
?>
  <script language=javascript>
    document.location.href='noaccess.html';
  </script>
<?php 
  exit;
}

if(isset($hitType)){
  if($hitType == 'geneLevel'){
    $Is_geneLevel = 1;
  }else{
    $Is_geneLevel = 0;
  }
}elseif(isset($Is_geneLevel)){
  if($Is_geneLevel){
    $hitType == 'geneLevel';
  }
}

//-------------------------------------------------------------------------------------------------------------
$SearchEngineConfig_arr = get_project_SearchEngine();
$SearchEngine_lable_arr = get_SearchEngine_lable_arr($SearchEngineConfig_arr);
//-------------------------------------------------------------------------------------------------------------
if(defined('OPENFREEZER_SEARCH') && isset($_SESSION["OF_session_id"])){
  $OF_session_id = $_SESSION["OF_session_id"];
}

if($type == 'Bait'){
  $SQL = "SELECT `ID`,`Vector` 
          FROM `Bait` WHERE ID='$item_ID'";
}elseif($type == 'Exp' || $type == 'Experiment'){          
  $SQL = "SELECT E.ID,B.Vector 
          FROM Experiment E LEFT JOIN Bait B ON(E.BaitID=B.ID) WHERE E.ID='$item_ID'";        
}elseif($type == 'Sample' || $type == 'Band'){
  $SQL = "SELECT BA.ID,B.Vector 
          FROM Band BA LEFT JOIN Bait B ON(BA.BaitID=B.ID) WHERE BA.ID='$item_ID'";
}          
$item_arr = $HITSDB->fetch($SQL);

$Vector_str = '';
if(isset($item_arr['Vector']) && $item_arr['Vector']){
  $Vector_str = $item_arr['Vector'];
}

if(!_is_dir("../TMP/bioGrid/")) _mkdir_path("../TMP/bioGrid/");
$tmp_file = "../TMP/bioGrid/". $USER->ID .".csv";
if(!$tmp_handle = fopen($tmp_file, 'w')){
  echo "Cannot open file ($tmp_file)";
}
fwrite($tmp_handle, "edge_info\r\n");

if($hitType == 'TPP'){
  $hits_colspan = 14;
}else{
  $hits_colspan = 15;
}

if($is_show_filter == 'Y'){
  $is_show_filter_style = 'display: block';
  $is_show_filter_lable = '[Hide Filters]';
}else{  
  $is_show_filter_style = 'display: none';
  $is_show_filter_lable = '[Show Filters]';
}
//-----------------------------------------------
if($type == 'Bait' || $type == 'Experiment' || $type == 'Sample'){
  $passedTypeArr = array();
  if($type == 'Sample'){
    $group_table_name = 'BandGroup';
  }else{
    $group_table_name = $type.'Group';
  }
  $SQL = "SELECT N.ID,N.Initial 
          FROM $group_table_name G 
          LEFT JOIN NoteType N
          ON(N.ID=G.NoteTypeID)
          WHERE G.RecordID = '$item_ID'";
  if($tmp_sql_arr = $HITSDB->fetchAll($SQL)){
    foreach($tmp_sql_arr as $tmp_sql_val){
      $passedTypeArr[$tmp_sql_val['ID']] = $tmp_sql_val['Initial'];
    }
  }
}

if(!$item_hits_order_by){
  if($hitType == 'TPPpep'){
    $item_hits_order_by = 'PROBABILITY desc';
  }elseif($hitType == 'TPP'){
    $item_hits_order_by = 'TOTAL_NUMBER_PEPTIDES desc';
  }elseif($hitType == 'geneLevel'){
    $item_hits_order_by = 'SpectralCount desc';
  }else{
    $item_hits_order_by = 'Pep_num desc';
  }
}

if($hitType == 'TPPpep' && $theaction=='exclusion'){
  $start_point = 0;
}
$NSgeneIDarr = array();
$NSfilteIDarr = array();

$colorArr = array();
get_color_arr($colorArr);
$giArr = array();

$subDir = strtolower($type);
$outDir = "../TMP/".$subDir."_report/";

if(!_is_dir($outDir)) _mkdir_path($outDir);

$filename = $outDir.$_SESSION['USER']->ID."_".strtolower($type)."_map.csv";
if (!$handle = fopen($filename, 'w')){
  echo "Cannot open file ($filename)";
  exit;
}

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
//---------------------------------------------------------------------------------------
$ExpDetail_id_name_arr = get_expDetail_id_name_arr();
//-------------------------------------------------------------------------------------
create_filter_status_arrs($typeBioArr,$typeExpArr_tmp,$typeFrequencyArr);
if(!$theaction || !$submitted){
    $frm_Frequency = isset($typeFrequencyArr['Init'])?$typeFrequencyArr['Init']:0;
}else{
  if(!isset($frm_Frequency)){
    $frm_Frequency = 0;
  }
}
  
foreach($typeBioArr as $typeBioValue){
  $frmName = 'frm_' . $typeBioValue['Alias'];
  if(!$theaction || !$submitted){
    $$frmName = $typeBioValue['Init'];
  }else{
    if(!isset($$frmName)){
      $$frmName = "0";
    }
  }
}

$OP_arr = array();
$NStmpArr = array();
foreach($typeExpArr_tmp as $typeExpValue){
  if($typeExpValue['Alias'] == 'OP'){
    $OP_arr = $typeExpValue;
  }elseif($typeExpValue['Alias'] == 'NS'){
    $NStmpArr = $typeExpValue;
  }else{
    array_push($typeExpArr, $typeExpValue);
  }
}

if($NStmpArr) array_unshift($typeExpArr, $NStmpArr);
foreach($typeExpArr as $typeExpValue){
  $frmName = 'frm_' . $typeExpValue['Alias'];
  if(($hitType == 'normal'|| $hitType == 'geneLevel') && !$theaction || !$submitted){
    $$frmName = $typeExpValue['Init'];
  }else{
    if(!isset($$frmName)){
      $$frmName = "0";
    }
  }
}

if(!isset($frequencyLimit)){
  $frequencyLimit = $_SESSION["workingProjectFrequency"];
  if(!$frequencyLimit) $frequencyLimit = 101;
}else{
  if(($theaction != 'exclusion' || (isset($frm_Frequency) && !$frm_Frequency)) && $hitType != 'TPP'){
    $frequencyLimit = $_SESSION["workingProjectFrequency"];
  }
}

if($theaction != 'exclusion'){
  $frm_Expect_check = '';
  $frm_Expect2_check = '';
}

//processing move item
if($whichitem == 'last'){
  $item_ID = move_item($HITSDB, 'last',$type);
}else if($whichitem == 'first'){
  $item_ID = move_item($HITSDB, 'first',$type);
}else if($whichitem == 'next' and $item_ID){
  $item_ID = move_item($HITSDB, 'next', $type, $item_ID);
}else if($whichitem == 'previous' and $item_ID){
  $item_ID = move_item($HITSDB, 'previous', $type, $item_ID);
}

$level_1_arr = array();
$level_2_arr = array();
$BaitArr = array();

$task_tpptask_ids_arr = array();

get_item_general_info($type,$item_ID,$isGelFree);

$usersArr = get_users_ID_Name($HITSDB);
$baitStr = '';
$tmpBaitArr = array();
if($type == "Bait"){
  $baitStr = $item_ID;
}else{
  foreach($level_2_arr as $subArr){
    if(!in_array($subArr['BaitID'], $tmpBaitArr)){
      array_push($tmpBaitArr, $subArr['BaitID']);
    }
  }
  $baitStr = implode(",", $tmpBaitArr);
}
$tmp_band_arr = array();
foreach($level_2_arr as $subArr){
  if(!in_array($subArr['ID'], $tmp_band_arr)){
    array_push($tmp_band_arr, $subArr['ID']);
  }
}
$frm_selected_item_str = implode(",", $tmp_band_arr);
if(!$searchEngineField){
  $searchEngineField = get_default_searchEngine($frm_selected_item_str,$hitType);
}
$fequMaxScore = '';
$frequencyArr = array();

if(preg_match('/^U:(.+)$/', $frm_filter_Fequency, $matches)){
  $user_frequencyFileName = $matches[1];
  $frequencyArr = get_user_frequency_arr($user_frequencyFileName);
}elseif(is_numeric($frm_filter_Fequency)){
  if($hitType == 'geneLevel'){
    $sub_frequencyFileName = 'Type'.$frm_filter_Fequency.'_geneLevel.csv';
  }elseif($hitType == 'TPP'){
    $sub_frequencyFileName = 'Type'.$frm_filter_Fequency.'_TPP.csv';
  }else{
    $sub_frequencyFileName = 'Type'.$frm_filter_Fequency.'.csv';
  } 
  $frequencyArr = get_sub_frequency_arr($sub_frequencyFileName,$frequencyLimit);
}else{
  if($hitType == 'geneLevel'){
    $frequencyFileName = "GeneLevel_".$searchEngineField.'_frequency.csv';
  }elseif($hitType == 'TPP'){
    $frequencyFileName = 'tpp_frequency.csv';
  }elseif($hitType == 'normal'){
    $frequencyFileName = $searchEngineField.'_frequency.csv';
    //$frequencyFileName = 'frequency.csv';
  }else{
    $frequencyFileName = '';
  }  
  $fequMaxScore = get_frequency_arr($frequencyArr,$frequencyFileName);
}

?>

<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language='javascript'>
function print_view(theTarget){
  theForm = document.item_form;  
  theForm.theaction.value = '<?php echo $theaction;?>';
  theForm.action = theTarget
  theForm.target = "_blank";
  theForm.submit();
}
function trimString(str) {
  var str = this != window? this : str;
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
function is_numberic(str){
  str = trimString(str);
  if(/^\d*\.?\d+$/.test(str)){
    return true;
  }else{
    return false;
  }
}
function applyExclusion(){
  theForm = document.item_form;
  validate_tpp();
  if(typeof(theForm.frm_Frequency) != 'undefined'){
    if(theForm.frm_Frequency.checked == true){
      var frequency = theForm.frequencyLimit.value;
      if(!is_numberic(frequency)){
        alert("Please enter numbers or uncheck check box on frequency field.");
        return;
      }else{
        if(frequency > 100 || frequency < 0){
          alert("frequence value should be great than 0 and less than 100");
          return;
        }
      }
    }else{
      if(theForm.hitType.value != 'TPP'){
        theForm.frequencyLimit.value = '';
      }  
    } 
  }  
  theForm.theaction.value = 'exclusion';
  theForm.action = '<?php echo $_SERVER['PHP_SELF'];?>';
  theForm.target = "_self";
  theForm.is_show_filter.value = 'Y';
  theForm.submit();
}
function NoExclusion(){
  theForm = document.item_form;
  if('<?php echo $hitType?>' != 'normal' && !validate_tpp() && '<?php echo $hitType?>' != 'geneLevel') return;
  theForm.theaction.value = '';
  theForm.action = '<?php echo $_SERVER['PHP_SELF'];?>';
  theForm.target = "_self";
  theForm.is_show_filter.value = 'Y';
  theForm.submit();
}
function validate_tpp(){
  theForm = document.item_form;
  if('<?php echo $hitType?>' == 'TPP'){
    return true;
  }else if('<?php echo $hitType?>' == 'TPPpep'){
    var PBT = theForm.frm_PBT.value;
    var HSR = theForm.frm_HSR.value;
    var ION = theForm.frm_ION.value;
    if(PBT != '' && !is_number(PBT)) return false;
    if(HSR != '' && !is_number(HSR)) return false;
    if(ION != '' && !is_number(ION)) return false;
    return true;
  }
}
function is_number(in_str){
  if(/^\d*\.?\d*$/.test(in_str)){
    return true;
  }else{
    alert('Interge and fload allowed.');
    return false;  
  }
}
function pop_exp_filter_set(filter_ID){
  if(filter_ID == '12'){
    var theForm =  document.item_form 
    var NS_group_id = theForm.frm_NS_group_id;
    var frm_NS_group_id = '';
    for(var i=0; i<NS_group_id.length; i++){
      if(NS_group_id[i].selected == true){
        var frm_NS_group_id = NS_group_id[i].value;
        break;
      }
    }
    file = 'mng_set_non_specific.php?filterID=' + filter_ID + '&frm_NS_group_id=' + frm_NS_group_id;
  }else{
    file = 'mng_set.php?filterID=' + filter_ID;
  }  
  window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=600,height=620');
}
 
function move_item(whichitem){
  var theForm =  document.item_form
  if(whichitem == 'last') { 
    theForm.whichitem.value = 'last';
  } else if(whichitem == 'first') {
    theForm.whichitem.value = 'first';
  } else if(whichitem == 'next') {
    theForm.whichitem.value = 'next';
  } else if(whichitem == 'previous') {
    theForm.whichitem.value = 'previous';
  }
  theForm.action = '<?php echo $_SERVER['PHP_SELF'];?>';
  theForm.target = "_self";
  theForm.theaction.value = 'exclusion';
  theForm.submitted.value = 0;
  theForm.submit();
}
function updateFrequency(){
  if(!confirm("Are you sure that you want to update frequency?")){
    return false;
  }
  if(typeof(newWin2) == 'object'){
      newWin2.close();
      theForm.target='_parent';
  }
  file = "./mng_set_frequency.php?theaction=update_only";
  nWin = window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=300');
  nWin.moveTo(4,0);
} 
function show_all_peptides(Band_ID){
  file = 'show_all_peptides.php?Band_ID=' + Band_ID;
  newwin = window.open(file,"show_all_peptides",'toolbar=1,location=0,directories=0,status=0,menubar=1,scrollbars=1,resizable=1,width=850,height=600');
  newwin.moveTo(10,10);
}
function popTest(){
  file = "bait_report_gif.inc.php";
  nWin = window.open(file,"image",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=800');
  nWin.moveTo(4,0);
}
function confirm_cyto_div(){
  var theForm =  document.item_form;
	theForm.target = "_blank";
  theForm.action = "./cytoscape_export.php";
  hide_confirm_div('cyto_confirm_div');
  theForm.submit();
}
function hide_confirm_div(tipDiv){
	var obj = document.getElementById(tipDiv);
  obj.style.display="none";
}
function processAjaxReturn(rp){
  var ret_html_arr = rp.split("@@**@@");
  if(ret_html_arr.length == 2){
    var div_id = trimString(ret_html_arr[0]);
    document.getElementById(div_id).innerHTML = ret_html_arr[1];
    return;
  }
}
function toggle_filter(is_show){
  var detail_obj = document.getElementById('filter_detail_div');
  var button_obj = document.getElementById('filter_button_div');
  var toggle_obj = document.getElementById('filte_toggle_a');
  if(detail_obj.style.display == "none"){
    detail_obj.style.display = "block";
    button_obj.style.display = "block";
    toggle_obj.innerHTML = '[Hide Filters]';
  }else{
    detail_obj.style.display = "none";
    button_obj.style.display = "none";
    toggle_obj.innerHTML = '[Show Filters]';
  }
}
function popwin_export(file){
  var theForm =  document.item_form;
  var SearchEngine = '<?php echo $searchEngineField?>';
  //var SearchEngine = theForm.SearchEngine.value;
  file += "&SearchEngine=" + SearchEngine + "&Is_geneLevel=" + <?php echo $Is_geneLevel?> ;
  popwin(file,'650','800');
}     
</script>
   <form name=item_form action=<?php echo $_SERVER['PHP_SELF'];?> method=post>  
   <input type=hidden name=item_ID value='<?php echo $item_ID;?>'>
   <input type=hidden name=theaction value='<?php echo $theaction;?>'>
   <input type=hidden name=sub value=<?php echo $sub;?>>
   <input type=hidden name=submitted value='1'>
   <input type=hidden name=whichitem value=''>
   <input type=hidden name=type value='<?php echo $type?>'>
   <input type=hidden name=hitType value='<?php echo $hitType?>'> 
   <input type=hidden name=item_hits_order_by value='<?php echo $item_hits_order_by?>'>
   <input type=hidden name=start_point value='<?php echo $start_point?>'>
   <input type=hidden name=infile value='<?php echo $filename?>'>
   <input type=hidden name=sendBy value='item_repot'>
   <input type=hidden name=orderby value='<?php echo $item_hits_order_by?>'>
   <input type=hidden name=baitStr value='<?php echo $baitStr?>'>
   <input type=hidden name=fequMaxScore value='<?php echo $fequMaxScore?>'>
   <input type=hidden name=level1_matched_file value='<?php echo $tmp_file?>'>
   <input type=hidden name=searchEngineField value='<?php echo $searchEngineField?>'>
   <input type=hidden name=is_show_filter value='<?php echo $is_show_filter?>'>
   <input type=hidden name=noteTypeID_str value='<?php echo (isset($noteTypeID_str))?$noteTypeID_str:''?>'>
   <input type=hidden name=this_projectID value='<?php echo $this_projectID?>'>
   <input type=hidden name=DB_name value='<?php echo $DB_name?>'>
   <input type=hidden name=title_lable value='<?php echo $title_lable?>'>
   
<table border=0 cellpadding="0" cellspacing="0" width="97%">
  <tr>
    <td colspan=2><div class=maintext>
      <img src="images/icon_carryover_color.gif"> Exclusion Color &nbsp;&nbsp;
      <img src="images/icon_picture.gif"> Gel image &nbsp;&nbsp;
      <img src="images/icon_Mascot.gif"> Mascot &nbsp;&nbsp;
      <img src="images/icon_GPM.gif"> GPM &nbsp;&nbsp;
      <img src="images/icon_notes.gif"> Hit Notes &nbsp;&nbsp;
    	<img src='./images/icon_coip_green.gif'> Yes &nbsp;&nbsp;
    	<img src='./images/icon_coip_red.gif'> No &nbsp;&nbsp;
    	<img src='./images/icon_coip_yellow.gif'> Possible &nbsp;&nbsp;
    	<img src='./images/icon_coip_blue.gif'> In Progress
      </div><BR>
    </td>
  </tr>
  <tr>
    <td align="left">
    <font color="navy" face="helvetica,arial,futura" size="3"><b><?php echo (($title_lable)?"Search hits: ".$title_lable:$type." Report Hits")?>
<?php 
if($AccessProjectName){
  echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font>";
}
if($type == 'Bait'){    
  $preFile = 'bait.php';
}elseif($type == 'Plate'){
  $preFile = 'plate_show.php';
}elseif($type == 'Gel'){  
  $preFile = 'gel.php';
}elseif($type == 'Sample'){  
  $preFile = 'band_show.php';
}elseif($type == 'Experiment'){
  $preFile = 'experiment_show.php';
}
$theFile = "./export_hits.php?type=$type&hitType=$hitType&infile=$filename&mapfileDelimit=$fileDelimit&isGelFree=$isGelFree&item_ID=$item_ID&source=item_report&Vector_str=$Vector_str"; 
?>
    </b> 
    </font>&nbsp;&nbsp;&nbsp;&nbsp; 
    
<?php 
if($hitType != "TPPpep"){
?>    
        <a href="javascript: href_show_hand();" onclick="showTip(event,'cyto_confirm_div')" class=button>[<img src=./images/icon_cytoscape.gif border=0>Cytoscape]</a> &nbsp;
        <DIV ID='cyto_confirm_div' STYLE="position: absolute; 
                              display: none;
                              border: black solid 1px;
                              width: 200px;
                              height: 120px;
                              color: black";
                              z-index:100;>
          <table align="center" cellspacing="0" cellpadding="0" border="0" width=100%>
            <tr><td valign="bottem">&nbsp;&nbsp;&nbsp;&nbsp;<font color="green" face="helvetica,arial,futura" size="2"><b>Select node lable:</b></font><hr size=1></td></tr>
            <tr><td>&nbsp;&nbsp;&nbsp;<input type=radio NAME="node_lable" VALUE="short" checked>&nbsp;<font color="black" face="helvetica,arial,futura" size="2">Gene name</font></td></tr>
            <tr><td>&nbsp;&nbsp;&nbsp;<input type=radio NAME="node_lable" VALUE="long">&nbsp;<font color="black" face="helvetica,arial,futura" size="2">Gene name and Gene ID<br>&nbsp;</font></td></tr>
            <tr><td align="center"><input type=button name='confirm_div' VALUE=" Confirm " onclick="javascript: confirm_cyto_div();">&nbsp;&nbsp;
            <input type=button name='hide_div' VALUE=" Cancel " onclick="javascript: hide_confirm_div('cyto_confirm_div');">
            </td></tr>
          </table>   
        </DIV>
        <!--a href="javascript: cytoscape()" class=button>[<img src=./images/icon_cytoscape.gif border=0>Cytoscape]</a-->
<?php 
}
?> 
    </td>
    <td align="right">
      <a href="javascript: popwin_export('<?php echo str_replace("\\","/",$theFile);?>')" class=button>[Export <?php echo $type?> Report]</a> 
      &nbsp;<a href="./<?php echo  $preFile; echo ($sub)?"?sub=$sub":"";?>" class=button>[Back to <?php echo $type?> List]</a>   
    </td>   
  </tr>
  <tr>
    <td colspan=2 height=0 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
<?php 
if($type == 'Gel'){
?>
  <tr>
    <td align='center' colspan=2>
      <img src="./gel_images/<?php echo $level_1_arr['Image'];?>" border=0>
    </td>
  </tr>
<?php 
}
?>
  <tr>
    <td align="">
      <table border=0 cellspacing="6" cellpadding="0" width=100%>
<?php 
if($type == 'Bait' || $type == 'Sample'  || $type == 'Experiment'){
?>
      <tr>
        <td align="<?php echo ($type=='Gel')?'center':'left'?>">
          <table border=0 cellspacing="0" cellpadding="0" width=100%>  
            <tr>
              <td>
                <DIV STYLE="display: block; border: #a4a4a4 solid 1px; width: 100%">
                <?php 
                  $bait_info_arr = bait_info($level_1_arr['BaitID'],$tr_bgcolor,$tr_title_bgcolor,'item_report');
                ?>
                </DIV>
              </td>
            </tr>
          </table>
        </td>
      </tr>    
<?php 
  $BaitDescription = $level_1_arr['Description'] = str_replace(",", ";", $level_1_arr['Description']);
  $BaitDescription = $level_1_arr['Description'] = str_replace("\n", "", $level_1_arr['Description']);
}elseif($type == 'Plate'){
?>        
      <tr>
        <td align="<?php echo ($type=='Gel')?'center':'left'?>">
          <table border=0 cellspacing="0" cellpadding="0" width=100%>  
            <tr>
              <td>
                <DIV STYLE="display: block; border: #a4a4a4 solid 1px; width: 100%">
                <?php 
                  plate_info($item_ID,$tr_bgcolor,$tr_title_bgcolor,'item_report');
                ?>
                </DIV>
              </td>
            </tr>
          </table>
        </td>
      </tr>    
<?php 
}elseif($type == 'Gel'){
?>
      <tr>
        <td align="<?php echo ($type=='Gel')?'center':'left'?>">
          <table border=0 cellspacing="0" cellpadding="0" width=100%>  
            <tr>
              <td>
                <DIV STYLE="display: block; border: #a4a4a4 solid 1px; width: 100%">
                <?php 
                  gel_info($item_ID,$tr_bgcolor,$tr_title_bgcolor,'item_report');
                ?>
                </DIV>
              </td>
            </tr>
          </table>
        </td>
      </tr>    
<?php 
}
  
if($type == 'Bait' || $type == 'Sample' || $type == 'Experiment'){
  $arg_exp_id = '';
  $arg_type = '';
  if($type == 'Bait'){
    $arg_exp_id = '';
    foreach($exp_arr as $exp_val){
      if($exp_val['ID']){
        $arg_exp_id = $exp_val['ID'];
        $arg_type = 'Bait';
        break;
      }
    }
  }elseif($type == 'Experiment'){  
    $arg_exp_id = $exp_arr[0]['ID'];
    $arg_type = 'Experiment';
  }elseif($type == 'Sample'){
    $arg_exp_id = $level_2_arr[0]['ExpID'];
    $arg_type = 'Experiment';
  }  
?>
        <tr>
          <td align='left'>
            <table border=0 cellspacing="0" cellpadding="0" width=100%>  
              <tr>
                <td>
                  <DIV STYLE="display: block; border: #a4a4a4 solid 1px; width: 100%">
                  <?php 
                    Exp_info($arg_exp_id,$arg_type,$tr_bgcolor,$tr_title_bgcolor,'item_report');
                  ?>
                  </DIV>
                </td>
              </tr>
            </table>
          </td>
        </tr>
<?php 
  if($type == 'Sample'){
    $arg_exp_id = $level_2_arr[0]['ID'];
    $arg_type = 'Band';
?>    
        <tr>
          <td align='left'>
            <table border=0 cellspacing="0" cellpadding="0" width=100%>  
              <tr>
                <td>
                  <DIV STYLE="display: block; border: #a4a4a4 solid 1px; width: 100%">
                  <?php 
                    band_info($arg_exp_id,$arg_type,$tr_bgcolor,$tr_title_bgcolor,'item_report');
                  ?>
                  </DIV>
                </td>
              </tr>
            </table>
          </td>
        </tr> 
<?php     
  }
}
?>    
        <tr>
          <td class=large>   
            <a id=filte_toggle_a href="javascript: toggle_filter()" class=button><?php echo $is_show_filter_lable?></a>
          </td>
        </tr>
        <tr>
          <td align="left" valign=bottom>
            <DIV ID='filter_detail_div' STYLE="<?php echo $is_show_filter_style?>">
              <table border=0 cellspacing="0" cellpadding="0" width=100%>  
                <tr>
                  <td>
                <?php                 
                  include("filter_interface.php");
                ?>
                  </td>
                </tr>
              </table>
            </DIV>
          </td>
        </tr>
      </table>
    </td>
    
    <td align=center valign=top width="40%">
    <div id="chart_div" style="width: 500px; height: 200px;  border: #708090 0px solid; z-index:-1"></div>
    </td>
  </tr>
  <tr>
    <td>
      <DIV ID='filter_button_div' STYLE="<?php echo $is_show_filter_style?>">&nbsp;
        <input type=button value='No Exclusion' class=black_but onClick='javascript: NoExclusion();'>
        <input type=button value='Apply Exclusion' class=black_but onClick='javascript: applyExclusion();'>
      </DIV>
    </td>
    <td align="right">
      <a href='./user_defined_frequency.php?theaction=display_frequency' class=button>[ Update Frequency ]</a>
        <!--input type=button value='Update Frequency' onClick='javascript: updateFrequency();'-->&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php 
if($type == 'Sample' && 0){
?>
      <a href="javascript:show_all_peptides('<?php echo $item_ID;?>');">Sample Report (peptide detail)</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php 
}
?>
    </td>        
  </tr> 
  <tr>
    <td colspan=2><br>
      <table border=0 cellpadding="0" cellspacing="1" width="100%">
<?php 

print_table_head();

//= export filter info =========================================================
if(!isset($request_arr['searchEngineField'])) $request_arr['searchEngineField'] = $searchEngineField;
$filter_export_arr = array();
$filter_export_arr_2 = array();
$filter_export_arr_3 = array();

get_filter_array_for_export($request_arr);
write_filter_info_map($handle);
//==============================================================================

$fileLevel_1_str = '';
foreach($level1_lable_array as $tmpKey => $tmpLable){
  if($fileLevel_1_str) $fileLevel_1_str .= $fileDelimit;
  if($tmpKey == 'GelFree'){
    if($level_1_arr[$tmpKey] == '1'){
      $level_1_arr[$tmpKey] = 'Y';
    }else{
      $level_1_arr[$tmpKey] = 'N';
    }
  }
  if(isset($level_1_arr[$tmpKey])){
    $singefileLevel_1_str = str_replace(",", ";", $level_1_arr[$tmpKey]);
    $singefileLevel_1_str = str_replace("\n", "", $singefileLevel_1_str);
  }  
  $fileLevel_1_str .= $tmpLable.'==='.$singefileLevel_1_str;
}
$fileLevel_1_str = $level1_header.'::'.$fileLevel_1_str."\r\n";


fwrite($handle, $fileLevel_1_str);

$level3_lable_array_new = array();
if($searchEngineField == 'SEQUEST'){
  foreach($level3_lable_array as $tmpKey2 => $tmpVal2){
    if($tmpKey2 == 'Expect'){
      $level3_lable_array_new[$tmpKey2] = 'Hit Sequest Score';
    }elseif($tmpKey2 == 'Expect2'){
      continue;
    }else{
      $level3_lable_array_new[$tmpKey2] = $tmpVal2;
    }  
  }
}else{
  $level3_lable_array_new = $level3_lable_array;
}
  
$filedNameStr = implode($fileDelimit, $level3_lable_array_new);
$filedNameStr = "level3::".$filedNameStr."\r\n";

fwrite($handle, $filedNameStr);

$tmpCounter = 0;
$img_total = 0;

$fileColorIndexArr = array();
$optionBgColor = '';
$fileColorCounter = 0;
$displayBandFlag = 0;
$BaitInfo = array();
$BaitGeneInfo = array();
$sub_grid_bait_hits_arr = array();
if($hitType != 'TPPpep' && ($type == 'Bait' || $type == 'Experiment' || $type == 'Sample')){
  if(array_key_exists($level_1_arr['GeneID'], $grid_bait_hits_arr)){
    $sub_grid_bait_hits_arr = $grid_bait_hits_arr[$level_1_arr['GeneID']];
    $BaitGeneName = $level_1_arr['GeneName'];
    $BaitID = $level_1_arr['BaitID'];
    $BaitGeneInfo[$level_1_arr['GeneID']] = $BaitID." ".$BaitGeneName;
    $BaitInfoKey = $BaitID." ".str_replace(",", ";", $BaitGeneName);
    $BaitInfoKey = trim($BaitInfoKey);
    $BaitInfo[$BaitInfoKey] = $level_1_arr['GeneID']; 

  }
//will be remove--get_hits_geneID_arr($level_1_arr['BaitID'], $hitsGeneIDarr, $hitType);
}
$note_exist_arr = array();
get_exist_note_hitsID_arr($level_2_arr,$note_exist_arr);

$matched_hits_geneID_arr = array();
$matched_hits_node_array = array();
$grid_eage_arr = array();
$EdgeArr_matched = array();

foreach($level_2_arr as $arr2_value){
  if(!$frm_selected_band || $frm_selected_band == $arr2_value['ID'] || $frm_selected_band == 'all_bands'){
    if($type != 'Bait' && $type != 'Sample' && $type != 'Experiment'){
      $sub_grid_bait_hits_arr = array();
      if(isset($grid_bait_hits_arr) && array_key_exists($arr2_value['GeneID'], $grid_bait_hits_arr)){
        $sub_grid_bait_hits_arr = $grid_bait_hits_arr[$arr2_value['GeneID']];
        $BaitGeneName = $arr2_value['GeneName'];
        $BaitID = $arr2_value['BaitID'];
        $tmpStr = $BaitID." ".$BaitGeneName;
        if(!array_key_exists($arr2_value['GeneID'], $BaitGeneInfo)){
          $BaitGeneInfo[$arr2_value['GeneID']] = $tmpStr;
          $BaitInfoKey = $BaitID." ".str_replace(",", ";", $BaitGeneName);
          $BaitInfoKey = trim($BaitInfoKey);
          $BaitInfo[$BaitInfoKey] = $arr2_value['GeneID'];
        }else{
          if(!strstr($BaitGeneInfo[$arr2_value['GeneID']], $tmpStr)){
            $BaitGeneInfo[$arr2_value['GeneID']] .= "#".$BaitID." ".$BaitGeneName;
            $BaitInfoKey = $BaitID." ".str_replace(",", ";", $BaitGeneName);
            $BaitInfoKey = trim($BaitInfoKey);
            $BaitInfo[$BaitInfoKey] = $arr2_value['GeneID'];
          }  
        }  
      }
    }   
    $hits_result = '';    
    get_hits_result($arr2_value['ID'], $hits_result, $searchEngineField,$hitType);    
    if(!$num_rows = mysqli_num_rows($hits_result)) continue;
    $img_total += $num_rows;
    if($type == 'Plate' || $type == 'Gel'){
      get_bait_arr($BaitArr,$arr2_value['BaitID']);
    }
    $subHeadFlag = 0;
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    include("item_report_hits.inc.php");
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
    if($frm_selected_band == $arr2_value['ID']){
      break;
    }  
  }  
}

if($hitType != 'TPPpep' && $bio_checked_arr && !$no_grid_data){
  $no_matched_hits_node = array();
  foreach($EdgeArr_matched as $EdgeKey => $EdgeValue){
    $fileLine = "1,".$EdgeKey.",".$EdgeValue."\r\n";
    fwrite($tmp_handle, $fileLine);
  }
  
  $no_matched_hits_geneID_arr = array_diff($grid_hits_gene_arr, $matched_hits_geneID_arr);
  if($no_matched_hits_geneID_arr){
    $no_matched_hits_geneID_str = implode(",", $no_matched_hits_geneID_arr);
    $SQL = "SELECT `EntrezGeneID`,`GeneName` FROM `Protein_Class` WHERE `EntrezGeneID` IN ($no_matched_hits_geneID_str)";
    if($tmpGeneArr = $proteinDB->fetchAll($SQL)){
      $no_matched_hits_node = array();
      foreach($tmpGeneArr as $geneInfo){
        $no_matched_hits_node[$geneInfo['EntrezGeneID']] = $geneInfo['GeneName'];
        foreach($grid_bait_hits_arr as $tmpKey => $tmpVal){
          if(array_key_exists($geneInfo['EntrezGeneID'], $tmpVal)){
            $gridTyp = implode(":", $tmpVal[$geneInfo['EntrezGeneID']]);
            $tmpArr = explode("#",$BaitGeneInfo[$tmpKey]);
            foreach($tmpArr as $tmpVal){
              $fileLine = "0,".str_replace(",", ";", $tmpVal)."??".str_replace(",", ";", $geneInfo['GeneName']).",".$gridTyp.",".$geneInfo['EntrezGeneID']."\r\n";
              fwrite($tmp_handle, $fileLine);
            }  
          }
        }  
      }
    }
  }
}
if($BaitInfo){
  ksort($BaitInfo);
  fwrite($tmp_handle, "bait_info\r\n");
  foreach($BaitInfo as $key => $BaitVal){
    $fileLine = $key.",".$BaitVal."\r\n";
    fwrite($tmp_handle, $fileLine);
  }
}

if($hitType == 'TPPpep' && !$theaction){
  $tmpWhereArr = explode('#',$subWhere2);
  foreach($tmpWhereArr as $tmpWhereval){
    $indiWhereArr = explode('=', $tmpWhereval);
    if(count($indiWhereArr) == 2){
      set_tppPep_counter($indiWhereArr); 
    }  
  }
}

$currentSetArr = array();
foreach($typeExpArr as $Value){
  if($hitType == 'TPP' && in_array($Value['Alias'], $exists_exp_arr)) continue;
  array_push($currentSetArr, $Value);
}
if($hitType != 'TPPpep'){  
  if($typeFrequencyArr && $frequencyLimit < 101){
    array_push($currentSetArr, $typeFrequencyArr);
  }
  foreach($typeBioArr as $Value){
    array_push($currentSetArr, $Value);
  }
}
arsort($giArr);
 
$colorIndex = 0;
?>
   </table>
    </td>
  </tr>
</table>
</form>
<?php 
if(isset($bait_info_arr) && $bait_info_arr) OF_block($bait_info_arr);

$_SESSION["currentSetArr"] = $currentSetArr;
require("site_footer.php");
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
}

function hideSameGene(){ 
	var obj = document.getElementById("pop_same_gene");
  obj.style.display="none";
  obj.style.position="";
  obj.style.border="";
  obj.style.backgroundColor="";
}
</script>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<!--script type="text/javascript" src="../common/javascript/google/jsapi.js"></script-->
<script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(drawChart);
    function drawChart(){
      var data = google.visualization.arrayToDataTable([
<?php 
        $filter_color_str='';
        echo "[''";
        foreach($currentSetArr as $val){
          echo ",'".$val['Name']."'";
          if($filter_color_str) $filter_color_str .= ",";
          $filter_color_str .= "'".$val['Color']."'";
        }
        echo "],";
        $filter_color_str = "[".$filter_color_str."]";
        echo "[''";
        foreach($currentSetArr as $val){
          echo ",".$val['Counter']."";
        }
        echo "]";
?>     
      ]);

      var options = {
        //title: 'Totol hits: <?php echo $img_total?>',
        colors: <?php echo $filter_color_str?>,
        //width:400, height:150,
        vAxis: {title: 'Number of hits: <?php echo $img_total?>'},
        hAxis: {title: "Filter cagegory (see legend)",
                gridlines: {color: '#ffffff', count: 0}
        },
        legend: {position: 'bottom'},
        bar: {groupWidth: '90%'}
      };

      var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
      chart.draw(data, options);
    }
</script>
<?php 
//--functions----------------------------------------------------------------------
function move_item($DB, $whichitem, $type, $type_ID=0){
  global $AccessProjectID;
  $re = $type_ID;
  $ID = "ID";
  if($type == 'Plate'){
    $type = "PlateWell";
    $ID = "PlateID";
  }
  $SQL = "SELECT $ID FROM $type";     
  $Where = " WHERE ProjectID=$AccessProjectID";     
  $SQL .= $Where;
  if($whichitem == 'last'){
    $SQL .= " GROUP BY $ID order by $ID desc limit 1";
  }elseif($whichitem == 'first'){
    $SQL .= " GROUP BY $ID order by $ID limit 1";
  }elseif($whichitem == 'next' and $type_ID){
    $SQL .= " and  $ID >  $type_ID GROUP BY $ID order by $ID limit 1";
  }elseif($whichitem == 'previous' and $type_ID){
    $SQL .= " and  $ID < $type_ID GROUP BY $ID order by $ID desc limit 1";
  }
  //echo $SQL."<br>";
  $row = mysqli_fetch_array(mysqli_query($DB->link, $SQL));
  if($row[0]) $re = $row[0];
  return $re;
}
function set_tppPep_counter($indiWhereArr){
  global $HITSDB,$frm_selected_band,$allBandsStr,$typeExpArr;
  $total = 0;  
  if($frm_selected_band == 'all_bands'){
    if($allBandsStr){
      $subWhereStr = " TP.BandID IN($allBandsStr)";
    }
  }elseif($frm_selected_band){
    $subWhereStr = " TP.BandID=$frm_selected_band ";
  }
  if($indiWhereArr[0] != 'TP.Ions'){  
    $SQL = "SELECT COUNT(".$indiWhereArr[0].") 
            FROM TppPeptide TP 
            WHERE $subWhereStr AND ".$indiWhereArr[1];
    $result = mysqli_query($HITSDB->link, $SQL);
    $row = mysqli_fetch_row($result);
    $total = $row[0];
  }else{
    $SQL = "SELECT TP.Ions 
            FROM TppPeptide TP 
            WHERE $subWhereStr";
    $result = mysqli_query($HITSDB->link, $SQL);
    while($row = mysqli_fetch_row($result)){
      $tmpIonsArr = explode('/',$row[0]);
      $hitsIons = round($tmpIonsArr[0]/$tmpIonsArr[1]*100,2);
      if($hitsIons < $indiWhereArr[1]) $total++;
    }
  }
  for($i=0; $i<count($typeExpArr); $i++){
    if($typeExpArr[$i]['DBfieldName'] == $indiWhereArr[0]){
      $typeExpArr[$i]['Counter'] = $total;
      break;
    }
  }
}
function print_move_icon(){
?>                
                  <a href="javascript: move_item('first');">
                  <img src="./images/icon_first.gif" border=0 valign="bottom"></a>&nbsp;
                  <a href="javascript: move_item('previous');">
                  <img src="./images/icon_previous.gif" border=0 valign="bottom"></a>&nbsp;
                  <a href="javascript: move_item('next');">
                  <img src="./images/icon_next.gif" border=0 valign="bottom"></a>&nbsp;
                  <a href="javascript: move_item('last');">
                  <img src="./images/icon_last.gif" border=0 valign="bottom"></a>&nbsp;
<?php 
}
function convert_Redundant($Redundant,$gi_acc_arr){
  $tmp_red_str = str_ireplace("gi|", "", $Redundant);
  $tmp_red_arr = explode(";",$tmp_red_str);
  $red_str = '';
  foreach($tmp_red_arr as $tmp_red_val){
    $tmp_red_val = trim($tmp_red_val);
    if($red_str) $red_str .= '; ';
    if(array_key_exists($tmp_red_val, $gi_acc_arr)){
      $red_str .= $gi_acc_arr[$tmp_red_val]['Acc_V'];
    }else{
      $red_str .= $tmp_red_val;
    }
  }
  return $red_str;          
}      



?>