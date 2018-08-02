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
set_time_limit(2400);
ob_start();
$php_file_name = 'export_hits';

$bg_tb_header = '#7eb48e';
$tb_color = '#e3e3e3';
$tb_color2 = '#d1e7db';
$titleBarW = '90%';

$filterStyleDisplay = 'none';
$theaction = '';
$fileExtention = "csv";
$titleLine = '';
$selecte_columns_str = '';
$item_ID = '';
$Expect = '';
$source = '';
$export_version = '';
$duplicate_log_title = 0;
$frm_sub_version = '';
//==for filter===========================
$frm_apply_filter = '';
$frm_filter_Expect = '';
$frm_filter_Coverage = '';
$frm_filter_Peptide = '';
$frm_filter_Peptide_value = '';
$frm_filter_Fequency = '';
$frm_filter_Fequency_value = '';
$frm_NS_group_id = '';
$frm_min_XPRESS = '';
$frm_max_XPRESS = '';

$isGelFree = 1;
$infile = '';
$mapfileDelimit = ',';
$SearchEngine = '';
$Vector_str = '';
//=======================================
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
@require_once("common/HTTP/Request_Prohits.php");
include("analyst/common_functions.inc.php");
include("analyst/comparison_common_functions.php");
require_once("msManager/is_dir_file.inc.php");
ini_set("memory_limit","-1");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

if(isset($Is_geneLevel)){
  $SearchEngineConfig_arr = get_project_SearchEngine_GL();
}else{
  $SearchEngineConfig_arr = get_project_SearchEngine();
}
$SearchEngine_lable_arr = get_SearchEngine_lable_arr($SearchEngineConfig_arr);


if($theaction == 'update_modifications' && $SearchEngine){
  update_modifications($SearchEngine);
  display_modifications();
  exit;
}

require("export_lable_arrs.inc.php");

if($fileExtention == "txt"){
  $Delimit = "\t";
}else{
  $Delimit = ",";
}
$level_header_arr = array();

if($hitType == 'normal'){
  $LableArr['level3'] = $hitsLableArr;
  if($SearchEngine == 'SEQUEST'){
    $LableArr['level4'] = $SequestPeptideLableArr;
  }else{
    $LableArr['level4'] = $PeptideLableArr;
  }  
  $level_header_arr['level3'] = 'Hit';
  $level_header_arr['level4'] = 'Peptide';
  $level_header_arr_R['Hit'] = 'level3';
  $level_header_arr_R['Peptide'] = 'level4';
}elseif($hitType == 'geneLevel'){  
  $LableArr['level3'] = $geneLevel_hitsLableArr;
  $LableArr['level4'] = $geneLevel_PeptideLableArr;
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
}
if($type == 'Sample' || $type == 'Band'){
  $type = 'Sample';
  $index = 'Band';
}else{
  $index = $type;
}

$subDir = strtolower($type);
$outDir_map = "../TMP/".$subDir."_report/";
if(!_is_dir($outDir_map)) _mkdir_path($outDir_map);
$OF_map_file = $outDir_map.$_SESSION['USER']->ID."_OF_map.csv";

//echo "\$OF_map_file=$OF_map_file<br>";
//echo $USER->Type."<br>";

if($USER->Type == 'Admin' && $theaction == 'create_OFmapFile' && $Vector_str){
  get_Info_from_OpenFreezer($Vector_str,$Username,$Password);
  exit;
}

//------------------------------------------------------------------------
if($hitType != 'geneLevel'){
  $LableArr['level3']['Protein_Length'] = "Hit Protein Length";
}

//########################################################################## remove $USER->ID == '17'
if($USER->Type == 'Admin'){
  $LableArr['OpenFreezer'] = $OpenFreezer;
}
//##########################################################################

//------------------------------------------------------------------------
if($item_ID){
  $header_SQL = $header_SQL_str_arr[$index] ." FROM $index WHERE ID=$item_ID";
}else{
  $header_SQL = '';
}
if($theaction == 'save_format'){
  $saved_Type = $hitType;
  if(!strstr($selecte_columns_str, '@level3___') && !strstr($selecte_columns_str, '@level4___')){
    $saved_Type = '';
  }
  $SQL = "INSERT INTO ExportFormat SET
          Name = '$format_name',
          Type = '$saved_Type',
          User = '$AccessUserID',
          Date = '".@date('Y-m-d')."',
          ProjectID = '$AccessProjectID',
          Format = '$selecte_columns_str'";
  $pre_format = $PROHITSDB->insert($SQL);
}elseif($theaction == 'modify_format'){
  $saved_Type = $hitType;
  if(!strstr($selecte_columns_str, '@level3___') && !strstr($selecte_columns_str, '@level4___')){
    $saved_Type = '';
  }
  $SQL = "UPDATE ExportFormat SET
          Format = '$selecte_columns_str',
          Type = '$saved_Type'
          WHERE ID='$pre_format'";
  $PROHITSDB->execute($SQL);
}elseif($theaction == 'remove_format'){
   $SQL = "DELETE FROM ExportFormat 
          WHERE ID='$pre_format'";
  $PROHITSDB->execute($SQL);
  $selecte_columns_str = '';        
}elseif($theaction == 'generate_report' || $theaction == 'view_preview'){
  $handle_version_log = '';
  if($source == 'comparison' && $export_version){
    $outDir = "../TMP/version_export/";
    $tmp_sub_version = '';
    if($frm_sub_version) $tmp_sub_version = "_".$frm_sub_version;
    if(!_is_dir($outDir)) _mkdir_path($outDir);
    $filename_out = $outDir."P".$AccessProjectID."_VS".$export_version.$tmp_sub_version."_".@date('Y-m-d').".csv";
    if(!$handle_write = fopen($filename_out, "w")){
      echo "cannot open file $filename_out";
      exit;
    }
    $version_log_file = $outDir."P".$AccessProjectID."_VS".$export_version.$tmp_sub_version."_".@date('Y-m-d')."_log.csv";
    if(!$handle_version_log = fopen($version_log_file, "w")){
      echo "cannot open file $handle_version_log";
      exit;
    }
    $version_log_title_line = "Bait ID,Bait Gene ID,Bait Gene Name,Exported by: ".$_SESSION['USER']->Fname." ".$_SESSION['USER']->Lname;
    fwrite($handle_version_log, $version_log_title_line."\r\n");
    $filename_log = $outDir."P".$AccessProjectID."_VS".$export_version."_duplicate_hits_log.csv";
    if(!$log_handle_write = fopen($filename_log, "a")){
      echo "cannot open file $filename_log";
      exit;
    }
  }  
  
  include("./export_hits_inc.php");
  
  if($theaction == 'generate_report'){
    if($task_para){
      $files_dir = dirname($filename_out);
      $Search_parameters_file = $files_dir.'/'.$_SESSION['USER']->ID."_parameters.csv";
      $file1 = basename($filename_out);
      $file2 = basename($Search_parameters_file);
      $filename_zip = "export.zip";
      $cmd = "cd $files_dir; zip $filename_zip $file1 $file2";
      $result = @exec($cmd);
      if(!$result){
        echo  "Can not create a zip file now in $saint_folder.";
        exit;
      }
      $filename_out = $files_dir.'/'.$filename_zip;
    }
    if(_is_file($filename_out)){
      header("Cache-Control: public, must-revalidate");
      header("Content-Type: application/octet-stream");  //download-to-disk dialog
      header("Content-Disposition: attachment; filename=".basename($filename_out).";" );
      header("Content-Transfer-Encoding: binary");
      header("Content-Length: "._filesize($filename_out));
      ob_clean();
      readfile("$filename_out");
      unlink($filename_out);
    }else{
      echo "$filename_out is not exist.";
    }
  }else{
    echo "<pre>";
    foreach($previewArr as $previewVal){
      echo $previewVal;
    }
    echo "</pre>";
  }
  exit;
  
}elseif($theaction == 'generate_map_file'){
?>
  <center>
  <div style='display:block' id='process'><img src='./images/process.gif' border=0></div> 
  </center>
<?php
  ob_flush();
  flush();
//########################################################################################### remove $USER->ID == '17' 
  include("./export_generate_map_file_inc.php");
//###########################################################################################
?>
<script language='javascript'>
document.getElementById('process').style.display = 'none';
</script>  
<?php  
}
//exit;
$default_pre_defined_formar_arr['Type'] = 'default';

$formatSetArr[0] = $default_pre_defined_formar_arr;
$defaulSetName = $default_pre_defined_formar_arr['Name'];
$SQL = "SELECT 
        `ID`,
        `Name`,
        `User`, 
        `Format`,
        `Type`
        FROM `ExportFormat` 
        WHERE `ProjectID` ='$AccessProjectID'";
if($tmpSetArr = $PROHITSDB->fetchAll($SQL)){
  foreach($tmpSetArr as $tmpSetVal){
    if(!strstr($tmpSetVal['Format'], '@level3___') && !strstr($tmpSetVal['Format'], '@level4___')){
      $tmpSetVal['Type'] = '';
    }
    array_push($formatSetArr, $tmpSetVal);
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Using responseText with innerHTML</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<STYLE type="text/css">
.sss { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt; white-space: nowrap; text-align: left}
TD { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}

option.grey {background-color:#cbcbcb}

</STYLE>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script type="text/javascript" src="../common/site_ajax.js"></script>
<!--script src="./site_no_right_click.inc.js" type="text/javascript"></script-->

<script language="JavaScript" type="text/javascript">
<!--
var format_sets = new Array();
var opened_heats  = new Array();
var all_heats = new Array();
var opened_heat_arr = new Array();
var proj_list = new Array();
var proj_id_list = new Array();
var proj_class_list = new Array();
var owner_list = new Array();
var owner_id_list = new Array();
var owner_class_list = new Array();
 
var mod = 'save_box';
var selected_value = '0';
<?php
$k = 0;
$m = 0;

foreach($formatSetArr as $value){
    $headerArr = array();
    $tmpArr = explode('@',$value['Format']);
    foreach($tmpArr as $tmpVal){
      $tmpArr2 = explode('___',$tmpVal);
      
      if(strstr($tmpArr2[0], 'level')){
        $tmpArr2[0] = $level_header_arr[$tmpArr2[0]];
      }   
      if(!in_array($tmpArr2[0], $headerArr)){
        array_push($headerArr, $tmpArr2[0]);
      }
    }
    $headerStr = implode(",", $headerArr);
    
    $color_class = '';
    if($value['Type'] && $value['Type'] != 'default' && $hitType != $value['Type']){
      $color_class = 'grey';
    }
?>
    opened_heats[<?php echo $value['ID'];?>] = "<?php echo $headerStr;?>";
    format_sets[<?php echo $value['ID'];?>] = "<?php echo $value['Format'];?>";
    proj_list[<?php echo $k?>] = "<?php echo $value['Name'];?><?php echo (($value['Type']=='default'||$value['Type']=='normal'||!$value['Type'])?'':'('.$value['Type'].')');?>";
    proj_id_list[<?php echo $k?>] = "<?php echo $value['ID'];?>";
    proj_class_list[<?php echo $k?>] = "<?php echo $color_class;?>";
<?php   if($value['User'] == $AccessUserID){?>
      owner_list[<?php echo $m?>] = "<?php echo $value['Name'];?><?php echo (($value['Type']=='default'||$value['Type']=='normal'||!$value['Type'])?'':'('.$value['Type'].')');?>";
      owner_id_list[<?php echo $m?>] = "<?php echo $value['ID'];?>";
      owner_class_list[<?php echo $m?>] = "<?php echo $color_class;?>";
<?php     
      $m++;
    }
    $k++;
    if(!$selecte_columns_str){
      if($value['Name'] == 'default'){
        $selecte_columns_str = $value['Format'];
        $pre_format = $value['ID'];
      }
    }
  }
  $kk = 0;
  foreach($LableArr as $key =>$value){
    if($key == 'Band') continue;
    if($isGelFree && in_array($key, $gelFrrItemsArr_not)) continue;
    if(strstr($key, 'level')){
      $tmpHeader = $level_header_arr[$key];
    }else{
      $tmpHeader = $key;
    }   
?>
    all_heats[<?php echo $kk?>] = "<?php echo $tmpHeader?>";
<?php   
    $kk++;
  }
?>

function createCellWithText(className,text) {
  var cell = document.createElement('td');
	cell.className = className;
  if(text !== ''){
  	var textNode = document.createTextNode(text);
    cell.appendChild(textNode);
  }
  return cell;
}

function addTableRow(this_obj,text,className){
	var theForm = this_obj.form;
	var rowID = this_obj.value;
	if(this_obj.checked == false){
		remove_single_row(rowID);
		var tmpArr = theForm.selecte_columns_str.value.split("@");
		var tmpStr = '';
		for(var i=0; i<tmpArr.length; i++){
			if(tmpArr[i] == rowID) continue;
			if(tmpStr != '') tmpStr += '@';
			tmpStr += tmpArr[i];
		}
		theForm.selecte_columns_str.value = tmpStr;
	}else{
	  var row = document.createElement("tr");
		row.id = rowID;
	  cell = createCellWithText(className, text);
	  row.appendChild(cell);
	  document.getElementById("colorBarBody").appendChild(row);
		if(theForm.selecte_columns_str.value != '') theForm.selecte_columns_str.value += "@"
		theForm.selecte_columns_str.value += rowID;
	}
  if(mod == 'save_box' && theForm.pre_format.value != ''){
    var tmp_arr = theForm.pre_format;
    for(var i=0; i<tmp_arr.length; i++){
      if(tmp_arr.options[i].value == ''){
        tmp_arr.options[i].selected = true;
        break;
      }
    }
  }	
}

function remove_single_row(rowID) {
	var tableBody = document.getElementById("colorBarBody");
	var rowNote = document.getElementById(rowID);
	tableBody.removeChild(rowNote);
}

function clean_up_child_nodes(itemID){
  var parentItem = document.getElementById(itemID);
  if(parentItem.hasChildNodes()){
    while(parentItem.childNodes.length > 0) {
      parentItem.removeChild(parentItem.childNodes[0]);
    }
  }  
}

function removeAllOptions(selectbox){
  var i;
  for(i=selectbox.options.length-1;i>=0;i--){
    selectbox.remove(i);
  }
}

function addOption(selectbox,text,value,className,selected){
  var optn = document.createElement("OPTION");
  optn.text = text;
  optn.value = value;
  optn.className = className;
  optn.selected = selected;  
  selectbox.options.add(optn);
}

function initial_interface(){
  var selected_obj = document.getElementById('previou_format');
  var colour = selected_obj.options[selected_obj.selectedIndex].className;
  if(colour == 'grey'){
    alert("The option you selected is not matching with current hits type.");
    selected_obj.selectedIndex = 1;
  }

  var theForm = document.generate_report_form;
  var set_index = theForm.pre_format.value;
  if(set_index=='')return; 
  var checkbox_arr = theForm.option_items;
  var defaul_cols = format_sets[set_index];
  theForm.selecte_columns_str.value = format_sets[set_index];
  var defaul_col_arr = defaul_cols.split('@');
  clean_up_child_nodes("colorBarBody");
  for(var i=0; i<checkbox_arr.length; i++){
    checkbox_arr[i].checked = false;
  }    
  for(var j=0; j<defaul_col_arr.length; j++){
    for(var i=0; i<checkbox_arr.length; i++){
      if(defaul_col_arr[j] == checkbox_arr[i].value){ 
        checkbox_arr[i].checked = true;
        var row = document.createElement("tr");
		    row.id = defaul_col_arr[j];
        cell = createCellWithText('sss', checkbox_arr[i].id);
        row.appendChild(cell);
	      document.getElementById("colorBarBody").appendChild(row);
        break;
      }
    }  
  }
  
	//process headers
  for(var i=0; i<all_heats.length; i++){
    var selected_index = theForm.pre_format.value;
    var opened_heat_arr = opened_heats[selected_index].split(',');
    var matched = 0;
    for(var k=0; k<opened_heat_arr.length; k++){
      if(opened_heat_arr[k]==all_heats[i]){      
        matched = 1;
        break;
      }
    }
    if(matched==1){
      showhide(all_heats[i],'none');
    }else{
      showhide(all_heats[i],'block');
    } 
  }
	var flag = '0';
  for(var i=0; i<owner_id_list.length; i++){
    if(owner_id_list[i] == set_index){
      flag = '1';
      break;
    }  
  }
  showhide_modify_delete(flag);
  theForm.fileExtention.value = "csv";
}

function change_set(){ 
  initial_interface();
}

function showhide_modify_delete(flag){
  var obj_save_inner = document.getElementById('save_inner_box_t');
  var obj_save_t = document.getElementById('save_box_t');
  if(obj_save_t.style.display == 'block'){
    if(flag == '1'){
      obj_save_inner.style.display = "block";
    }else{
      obj_save_inner.style.display = "none";
    }
  }  
}

function showhide(DivID,flag){  
  var obj = document.getElementById(DivID);
  var obj_a = document.getElementById(DivID + "_a");
  if(flag != '') obj.style.display = flag;
  if(obj.style.display == "none"){
    obj.style.display = "block";
    obj_a.innerHTML = "<font size='2' face='Arial'><img src='images/minus.gif' border=0>&nbsp;" + DivID + ":</font>";
  }else{
    obj.style.display = "none";
    obj_a.innerHTML = "<font size='2' face='Arial'><img src='images/plus.gif' border=0>&nbsp;" + DivID + ":</font>";
  }
}

function generate_report(){
  theForm = document.generate_report_form;
  if(theForm.selecte_columns_str.value == ''){
    alert('Please select columns for export');
    return;
  }
  var SearchEngine = theForm.SearchEngine.value;
  var frm_modification = theForm.frm_modification;
  var modification_str = '';
  var m_obj = document.getElementById('Peptide Modifications');
  theForm.frm_modification_type.value = '';
  if(m_obj != undefined){
    if(m_obj.checked){
      for(var j=0; j<frm_modification.length; j++){
        if(frm_modification.options[j].selected == true){
          if(frm_modification.options[j].value == ''){
            modification_str = '';
            break;
          }else if(frm_modification.options[j].value == 'ALL'){
            modification_str = frm_modification.options[j].value;
            break;
          }else{
            if(modification_str != '') modification_str += ',';
            modification_str += frm_modification.options[j].value;
          }
        }
      }
      if(SearchEngine != "Mascot" && SearchEngine != "GPM" && SearchEngine != "SEQUEST" && (modification_str == "ALL" || modification_str == '')){
        if(modification_str == "ALL"){
          theForm.frm_modification_type.value = modification_str;
        }else{
          theForm.frm_modification_type.value = "ALL_ALL"; 
        }
        modification_str = '';
        for(var j=2; j<frm_modification.length; j++){
          if(modification_str != '') modification_str += ',';
          modification_str += frm_modification.options[j].value;
        }
      }
    }
  }
  theForm.modification_str.value = modification_str;
  theForm.theaction.value = 'generate_report';
  theForm.submit();
}

function pop_preview(){
  var theForm = document.generate_report_form;
  if(theForm.selecte_columns_str.value == ''){
    alert('Please select columns for export');
    return;
  }
  var selecte_columns_str = theForm.selecte_columns_str.value;
  var type = theForm.type.value;
  var hitType = theForm.hitType.value;
  var infile = theForm.infile.value;
  var theaction = 'view_preview';
  var mapfileDelimit = theForm.mapfileDelimit.value;
  var fileExtention = theForm.fileExtention.value;
  var SearchEngine = theForm.SearchEngine.value;
  var frm_modification = theForm.frm_modification;
  var modification_str = '';
  theForm.frm_modification_type.value = '';
  var m_obj = document.getElementById('Peptide Modifications');
  if(m_obj != undefined){
    if(m_obj.checked){
      for(var j=0; j<frm_modification.length; j++){
        if(frm_modification.options[j].selected == true){
          if(frm_modification.options[j].value == ''){
            modification_str = '';
            break;
          }else if(frm_modification.options[j].value == 'ALL'){
            modification_str = frm_modification.options[j].value;
            break;
          }else{
            if(modification_str != '') modification_str += ',';
            modification_str += frm_modification.options[j].value;
          }
        }
      }
      if(SearchEngine != "Mascot" && SearchEngine != "GPM"  && SearchEngine != "SEQUEST"&& (modification_str == "ALL" || modification_str == '')){
        if(modification_str == "ALL"){
          theForm.frm_modification_type.value = modification_str;
        }else{
          theForm.frm_modification_type.value = "ALL_ALL"; 
        }      
        modification_str = '';
        for(var j=2; j<frm_modification.length; j++){
          if(modification_str != '') modification_str += ',';
          modification_str += frm_modification.options[j].value;
        }
      }
    }
  }
  theForm.modification_str.value = modification_str;
  modification_str = encodeURIComponent(modification_str); 
  frm_modification_type = theForm.frm_modification_type.value;
  var sub_querryStr = "&SearchEngine="+SearchEngine+"&modification_str="+modification_str+"&frm_modification_type="+frm_modification_type;
  var querryStr = "selecte_columns_str="+selecte_columns_str+"&type="+type+"&hitType="+hitType+"&infile="+infile+"&theaction="+theaction+"&mapfileDelimit="+mapfileDelimit+"&fileExtention="+fileExtention+sub_querryStr;
  file = "/Prohits/analyst/export_hits.php?" + querryStr;
  popwin(file,800,400,'second_pop');
}

function check_options(){
  var theForm = document.generate_report_form;
  var optionArr = theForm.option_items
  var title_str = '';
  for(var i=0; i<optionArr.length; i++){
    if(optionArr[i].checked == true){
      if(title_str != '') title_str += "@";
      title_str += optionArr[i].value;
    }
  }
  if(title_str == ''){
    return false;
  }else{
    return title_str;
  }  
}

function save_format_set(action){
  var theForm = document.generate_report_form;
  if(action == 'Save'){
    if(theForm.selecte_columns_str.value == ''){
      alert("Please select a column.");
      return false;
    }else if(theForm.format_name.value == ''){
      alert('Giva a name for the set selected columns');
      return false;
    }else if(theForm.format_name.value == '<?php echo $defaulSetName;?>'){
      alert("Name '<?php echo $defaulSetName;?>' is reserved name. Please enter other name.");
      return false;
    }else{
			var match_flag = 0;
			for(var i=0; i<proj_list.length; i++){
        var res = proj_list[i].split("("); 
				if(res[0] == theForm.format_name.value){
					match_flag = 1;
					break;
				}
			}
			if(match_flag == 1){
				alert("The name had been used by others.");
				return false;
			}  
      theForm.theaction.value = 'save_format';
    }
  }else if(action == 'Modify'){
    if(theForm.pre_format.value == ''){
      alert('Select a Format to be modified');
      return false;
    }else{
      theForm.theaction.value = 'modify_format';
    }
  }else if(action == 'Remove'){
    if(theForm.pre_format.value == ''){
      alert('Select a Format to be remove');
      return false;
    }else{
      if(confirm("remove selected set?")){
        theForm.theaction.value = 'remove_format';
      }else{
        return false;
      }  
    }
  }
  theForm.submit();    
}

function showhide_sets_box(div_id,flag){
	var theForm = document.generate_report_form;
  var obj_t = document.getElementById('save_box_t');
  var obj = document.getElementById(div_id);
	var bar_obj = document.getElementById('button_bar');
	var tital_obj = document.getElementById('columns_tital');
  var selected_obj = document.getElementById('previou_format');
  var all_box_obj = document.getElementById('all_box');
  if(flag == "1"){
		selected_value = theForm.pre_format.value;
    all_box_obj.style.display = "none";
		bar_obj.style.display = "none";
    obj.style.display = "block";
		
		if(div_id == 'modify_box' || div_id == 'delete_box'){
			mod = div_id;
      if(div_id == 'modify_box'){
  			tital_obj.innerHTML = "<font size='2' face='Arial' color='#800040'><br><b>Modify Pre-defined export format set</b><br>&nbsp;</font>";
  		}else{
  			tital_obj.innerHTML = "<font size='2' face='Arial' color=red><br><b>Delete Pre-defined export format set</b><br>&nbsp;</font>";
			} 
      removeAllOptions(selected_obj);
      if(selected_value == ''){
        addOption(selected_obj,'-----------------------','','',true);
      }else{
        addOption(selected_obj,'-----------------------','','','');
      }  
      for(var i=0; i < owner_list.length; ++i){
        if(selected_value == owner_id_list[i]){
          addOption(selected_obj, owner_list[i],owner_id_list[i],owner_class_list[i],true);
        }else{
          addOption(selected_obj, owner_list[i],owner_id_list[i],owner_class_list[i],false);
        }  
      }
    }
  }else{
		all_box_obj.style.display = "block";
		bar_obj.style.display = "block";
		obj.style.display = "none";
		mod = 'save_box'; 
		tital_obj.innerHTML = "<font size='2' face='Arial'><br><b>Please select columns to be included in the export file</b><br>&nbsp;</font>";
		removeAllOptions(selected_obj);
		if(selected_value == ''){
			addOption(selected_obj,'-----------------------','','',true);
		}else{
			addOption(selected_obj,'-----------------------','','','');
		}  
		for(var i=0; i < proj_list.length;++i){

			if(selected_value == proj_id_list[i]){
				addOption(selected_obj, proj_list[i],proj_id_list[i],proj_class_list[i],true);
			}else{
				addOption(selected_obj, proj_list[i],proj_id_list[i],proj_class_list[i],false);
			}  
		}
		initial_interface();
  }
}

function update_modifications(){
  queryString = "theaction=update_modifications&SearchEngine=<?php echo $SearchEngine?>&Is_geneLevel=<?php echo $Is_geneLevel?>";
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
  document.getElementById('D_mod_id').innerHTML = "<img src='./images/process.gif' border=0>";
}

function create_OFmapFile(Vector_str){
  var theForm = document.generate_report_form;
  var Username = theForm.Username.value;
  var Password = theForm.Password.value;
  if(!Username.trim() || !Password.trim()){
    alert("Please enter Username and Password");
    return;
  }
  var type = theForm.type.value;
  queryString = "theaction=create_OFmapFile&Vector_str=" + Vector_str + "&Username=" + Username + "&Password=" + Password  + "&type=" + type;
 
//alert(queryString);
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function processAjaxReturn(rp){
  var tmp_arr = rp.split("::");
  if(tmp_arr[0].trim() == 'OF_Info'){
  //if(tmp_arr[0].trim() == 'OF_line'){
    if(tmp_arr[1].trim() == 'OK'){
      document.getElementById('OF_login').style.display = "none";
      document.getElementById('OF_option').style.display = "block";
      document.getElementById('OpenFreezer').style.display = "block";
      document.getElementById('OpenFreezer_a').innerHTML = "<img src='images/minus.gif' border='0'><font size='2' face='Arial'>&nbsp;OpenFreezer</font>";
    }else{
      document.getElementById('error_mes').innerHTML = "<font color=red>&nbsp;" + tmp_arr[1] + "</font>";
    }
  }else{
    document.getElementById('D_mod_id').innerHTML = rp;
  }
  return;
}

function toggle_detail(evt,this_obj, DivID){
  var obj = document.getElementById(DivID);
  if(this_obj.checked){
    var xl = 10;
    var yl = 20;
    if(isNav){
      obj.style.left = evt.pageX + xl + "px";
    	obj.style.top = evt.pageY + yl + "px";
    }else{
      obj.style.left = window.event.clientX  + document.body.scrollLeft + xl + "px";
    	obj.style.top = window.event.clientY + document.body.scrollTop+ yl + "px";
    }
    obj.style.display = "block";
  }else{  
    obj.style.display = "none";
  }
}

function hideTip_m(tipDiv){
  var obj = document.getElementById(tipDiv);
  obj.style.display="none";
}
//-->
</script>
<style>
.intTr1{
	background: <?php echo $tb_color;?>;
}
</style>
</head>
<?php 
if($header_SQL){
  $headerInfoArr = $HITSDB->fetchAll($header_SQL);
}else{
  $headerInfoArr = array();
}
?>
<body onload="initial_interface();">
<FORM ACTION="<?php echo $_SERVER['PHP_SELF'];?>" ID="" NAME="generate_report_form" METHOD="POST">
<input TYPE="hidden" NAME="selecte_columns_str" VALUE="<?php echo $selecte_columns_str;?>">
<input TYPE="hidden" NAME="type" VALUE="<?php echo $type?>">
<input TYPE="hidden" NAME="hitType" VALUE="<?php echo $hitType?>">
<input TYPE="hidden" NAME="infile" VALUE="<?php echo $infile?>">
<input TYPE="hidden" NAME="theaction" VALUE="">
<input TYPE="hidden" NAME="mapfileDelimit" VALUE="<?php echo $mapfileDelimit?>">
<input TYPE="hidden" NAME="isGelFree" VALUE="<?php echo $isGelFree?>">
<input TYPE="hidden" NAME="item_ID" VALUE="<?php echo $item_ID?>">
<input TYPE="hidden" NAME="source" VALUE="<?php echo $source?>">
<input TYPE="hidden" NAME="export_version" VALUE="<?php echo $export_version?>">
<input TYPE="hidden" NAME="frm_sub_version" VALUE="<?php echo $frm_sub_version?>">
<input TYPE="hidden" NAME="modification_str" VALUE="">
<input TYPE="hidden" NAME="SearchEngine" VALUE="<?php echo $SearchEngine?>">
<input TYPE="hidden" NAME="frm_modification_type" VALUE="">
<input TYPE="hidden" NAME="Is_geneLevel" VALUE="<?php echo $Is_geneLevel?>">
<table border="0" cellpadding="0" cellspacing="1"  width=100%>
  <tr>
    <td align="left" bgcolor='white'>
    <br><span class=pop_header_text>Export <?php echo $type?> Report</span>
<?php     
    if($AccessProjectName){
        echo "  <font color='#008040' face='helvetica,arial,futura' size='3'><b>(Project: $AccessProjectName)</b></font>";
    }
?>     
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor='white'><hr></td>
  </tr>
<?php if($theaction != 'generate_map_file' && isset($source) && $source == "item_report"){?>  
  <tr><td><table border=0 width=100% cellspacing="1" cellpadding=0>
<?php 
    echo "<pre>";
    echo "<tr>";
    foreach($headerInfoArr[0] as $key => $value){
      if($type == 'Sample'){
        $index = 'Band';
      }else{
        $index = $type;
      }  
      echo"<td align=left bgcolor='white'><b>".$LableArr[$index][$key]."</b></td>\n";
    }
    echo "</tr>";
    foreach($headerInfoArr as $singleRecord){
      echo "<tr>";
      foreach($singleRecord as $key => $value){
        if($type == 'Bait' && $key == 'GelFree'){
          if($value){
            $itemVal = 'Y';
          }else{
            $itemVal = 'N';
          }
        }else{
          $itemVal = ($value)?$value:'&nbsp;';
        }  
        echo"<td BGCOLOR='white'>$itemVal</td>\n";
      }
      echo "</tr>";
    }
    echo "</pre>";
?> 
  </table></td></tr>
<?php }
?>    
  <tr>
    <td colspan=7>
    <table border=0 width=100% cellspacing="1" cellpadding=0 bgcolor='#a0a7c5'>
      <tr>
        <td width="" valign=top align=center BGCOLOR='#a0a7c5' height='25' colspan='2' >
        <DIV ID="button_bar" style="display:block">
        <table border=0 width=100% cellspacing="0" cellpadding=0>
        <tr> 
        <td width='' height='25' BGCOLOR='#a0a7c5' colspan=1 align=left>&nbsp;&nbsp;<font color=white>Export rows as</font>
        <select name="fileExtention">
			    <option value="csv" <?php echo ($fileExtention == "csv")?'selected':''?>>CSV
          <option value="txt" <?php echo ($fileExtention == "txt")?'selected':''?>>TSV			
			  </select> 
        <input type=button name='Preview' value='Preview' onClick="pop_preview()">&nbsp;&nbsp;&nbsp; 
        <input type=button name='go' value='Generate Report' onClick="generate_report()">             
        </td>
        </tr>
        </table>
        </DIV>
        </td>  
      </tr>
      <tr id=intTr class=intTr1>
        <td width="" valign=top align=center bgcolor="#f8f8fc">
          <table border=0 width=98% cellspacing="1" cellpadding=0>
          <tr>
            <td id = 'columns_tital' colspan='2' nowrap align=center><br><b>Please select columns to be included in the export file</b><br>&nbsp;</td>
          </tr>
      <?php         
        foreach($LableArr as $headerKey => $headerSelections){
          if($headerKey == 'Band') continue;
          if(strstr($headerKey, 'level')){
            $headerLable = $level_header_arr[$headerKey];
          }else{
            if($isGelFree && !in_array($headerKey, $gelFrrItemsArr)) continue;
            $headerLable = $headerKey;
          }
          //echo "\$headerLable=$headerLable<br>";
      ?>      
      
      <?php   if($headerLable == "OpenFreezer"){?>
          <tr>
            <td nowrap bgcolor="#cfcfe7" align=left>                                          
            <DIV ID="OF_login" style="display:block">
              <table cellspacing="0" cellpadding="0" border="0" width="98%">
                <tr>
                  <td colspan='2' nowrap bgcolor="#cfcfe7" align=left>
                <?php if($Vector_str){?>
                    &nbsp;<b>OpenFreezer Login</b>
                    &nbsp;Username:<input type='text' size='10' name='Username'>
                    &nbsp;Password:<input type='password' size='10' name='Password'>
                    &nbsp;<input type='button' value='Log in' onclick="create_OFmapFile('<?php echo $Vector_str?>')">
                    <div id="error_mes"></div>
                <?php }else{?>
                    &nbsp;No Vector IDs or Cell_line IDs could be passed to OpenFreezer  
                <?php }?>
                  </td>
                </tr>
              </table>
            </DIV>
            </td>
          </tr>
          <tr><td colspan='4' nowrap bgcolor="white" align=left>                                          
          <DIV ID="OF_option" style="display:none">
          <table cellspacing="0" cellpadding="0" border="0" width="100%">          
      <?php   }?> 
          <tr>
          <td colspan='2' nowrap bgcolor="#cfcfe7" align=left>
              <a id='<?php echo $headerLable;?>_a' onclick="showhide('<?php echo $headerLable;?>','')"><font size="2" face="Arial"><?php echo ($filterStyleDisplay=='none')?"<img src='images/plus.gif' border=0>&nbsp;".$headerLable.":":"<img src='images/minus.gif' border=0>&nbsp;".$headerLable.":"?></font></a>
          </td>
          </tr>
          <tr>
            <td colspan='2' width=98%>
              <DIV ID="<?php echo $headerLable;?>" style="display:<?php echo $filterStyleDisplay;?>">
                <table align="center" bgcolor='' cellspacing="1" cellpadding="1" border="0" width=100%>
            <?php 
              $tmpCounter = 1;
              foreach($headerSelections as $fieldName => $fieldLable){
                if($tmpCounter%2) echo "<tr >";
                $index = $tmpCounter - 1;
                $option_items_value = $headerKey.'___'.$fieldName;
            ?>
                <td align=left width='50%' nowrap >
            <?php if($fieldLable == 'Peptide Modifications'){?>
                  <input type='checkbox' id='<?php echo $fieldLable;?>' name='option_items' value='<?php echo $option_items_value?>' onClick="addTableRow(this,'<?php echo $fieldLable;?>','sss');toggle_detail(event,this, 'D_mod_id')">
                  <font size="2" face="Arial"><?php echo $fieldLable;?></font>
                  <DIV id='D_mod_id' STYLE="position: absolute;display: none;border: black solid 1px;width: 300px; padding:0px 0px 0px 0px; background-color:#ffffff">
                  <?php display_modifications();?>
                  </DIV>
            <?php }else{?>
                  <input type='checkbox' id='<?php echo $fieldLable;?>' name='option_items' value='<?php echo $option_items_value?>' onClick="addTableRow(this,'<?php echo $fieldLable;?>','sss');">
                  <font size="2" face="Arial"><?php echo $fieldLable;?></font>
            <?php }?>      
                  
                </td>
            <?php 
                if((++$tmpCounter)%2) echo "</tr>";
              }
            ?>           
                </table>
              </DIV>
            </td>
          </tr>
          <?php if($headerLable == "OpenFreezer"){?>
          </table>
          </DIV>
          </td>
          </tr>
          <?php }?>
      <?php }?>
          </table>     
        </td> 
        <td width="30%" align=center valign=top bgcolor="#f8f8fc">
          <table align="center" bgcolor='' cellspacing="1" cellpadding="3" border="0" width=100%>
          <tr><td nowrap><br><b>Pre-defined export format <?php echo (($hitType=='normal')?'':'('.$hitType.')')?></b></td></tr>
					<tr>
            <td colspan='2' align=center width=98%>
              <DIV ID="save_box" style="display:none;border:black 1px solid;">
                <table align="center" bgcolor='' cellspacing="1" cellpadding="3" border="0" width=100%>
								<tr><td align="center">
                <table width=20>
								<tr><td >Column Set Name:</td></tr>
                <tr><td align=center nowrap width=100%><input type='text' name='format_name' value='' size='20'></td></tr>
                <tr><td align=center>
                  <input type=button value=' Save ' onclick="save_format_set('Save')">&nbsp;&nbsp;
                  <input type=button value=' Cancel ' onclick="showhide_sets_box('save_box','2')">  
                  <!--a href="javascript: showhide_sets_box('save_box','2')" class=button>Cancel</a-->
                </td></tr>
                </table></td></tr>
								</table>
              </DIV>
            </td>
          </tr>
					<tr>
            <td colspan='2' align=center width=98%>
              <DIV ID="modify_box" style="display:none;border:#800040 1px solid;">
                <table align="center" bgcolor='' cellspacing="1" cellpadding="3" border="0" width=100%>
								<tr>
                  <td align="center">
                    <table width=100%>
								     <tr>
                      <td >Select a set you want to modified. Check or uncheck columns on columns pat then click 'Save' button</td>
                     </tr>
                     <tr>
                       <td align=center>
                         <input type=button value=' Save ' onclick="save_format_set('Modify')">&nbsp;&nbsp;
                         <input type=button value=' Cancel ' onclick="showhide_sets_box('modify_box','2')">     
                         <!--a href="javascript: showhide_sets_box('modify_box','2')" class=button>Cancel</a-->
                       </td>
                     </tr>
                    </table>
                  </td>
                </tr>
								</table>
              </DIV>
            </td>
          </tr>
          <tr>
            <td colspan='2' align=center width=98%>
              <DIV ID="delete_box" style="display:none;border:red 1px solid;">
                <table align="center" bgcolor='' cellspacing="1" cellpadding="3" border="0" width=100%>
								<tr><td align="center">
                <table width=100%>
								<tr><td >Select a set you want to removed then click 'Remove' button</td></tr>
                <tr><td align=center>
                  <input type=button value=' Remove ' onclick="save_format_set('Remove')">&nbsp;&nbsp;
                  <input type=button value=' Cancel ' onclick="showhide_sets_box('delete_box','2')">
                  <!--a href="javascript: showhide_sets_box('delete_box','2')" class=button>Cancel</a-->
                </td></tr>
                </table></td></tr>
								</table>
              </DIV>
            </td>
          </tr>
          <tr>
          <td align=center>
          <table align="center" bgcolor='' cellspacing="1" cellpadding="3" border="0" width=100%>
          <tr>
            <td align=center colspan=2>
              <select id="previou_format" name="pre_format" onchange="change_set(this)">
        			  <option value="">---------------------------
              <?php foreach($formatSetArr as $setVal){
                  $color_class = "";
                  if($setVal['Type'] && $setVal['Type'] != 'default' && $hitType != $setVal['Type']){
                    $color_class = "class='grey'";
                  }
              ?>
                <option value="<?php echo $setVal['ID']?>" <?php echo ($pre_format==$setVal['ID'])?'selected':''?> <?php echo $color_class?>><?php echo $setVal['Name']?><?php echo ((!$setVal['Type']||$setVal['Type']=='normal'||$setVal['Type']=='default')?'':'('.$setVal['Type'].')')?>
              <?php }?>	
        			</select> 	  
            </td>
          </tr>
      <?php if($_SESSION['USER']->Type == 'Admin'){?>
          <tr>
            <td>
            <DIV ID="all_box" style="display:block">
            <table align="left" bgcolor='' cellspacing="0" cellpadding="0" border="0" width=100%>
          	<tr>
              <td width='' valign="top" nowrap>
                <DIV ID="save_box_t" style="display:block">
  							<table align="left" bgcolor='' cellspacing="0" cellpadding="0" border="0" width=100%>
            		<tr><td align="right"><a href="javascript: showhide_sets_box('save_box','1')" class=button>[new]</a>&nbsp;</td></tr>
  							</table>
  							</DIV>              
              </td>
              <td width='67%' valign="top" nowrap>
              <DIV ID="save_inner_box_t" style="display:block">
                <table align="left" bgcolor='' cellspacing="0" cellpadding="0" border="0" width=100%>
                <tr>
                  <td align="left">
                  <a href="javascript: showhide_sets_box('modify_box','1')" class=button>[edit]</a>
                  <a href="javascript: showhide_sets_box('delete_box','1')" class=button>[delete]</a>
                  </td>
                </tr>
  							</table>
              </DIV>&nbsp;
              </td>
            <tr> 
            </table>
            </DIV>
            </td>  
          </tr>
      <?php }?>    	
          <tr><td colspan=2><hr></td></tr>      
          <tr>
          <td align=center colspan=2>
          <b>Selected columns</b><br><br>
          <div class=sss3>
          <table id="colorBarTable" width="100" border="0">
    			  <tbody id="colorBarBody">					
    			  </tbody>
    			</table>
          </div>
          </td>
          </tr>
          </table>
        </td>
      </tr>
      </table>
    </td>
  </tr>
</table>
</FORM>
</body>
</html>
<?php 
function display_modifications(){
  global $theaction,$AccessProjectID,$SearchEngine;
  $modificationDir = STORAGE_FOLDER."Prohits_Data/modification_list/P_$AccessProjectID";
  $file_full_name = $modificationDir.'/'.$SearchEngine.'.txt';
  if(!_is_file($file_full_name)){
    update_modifications($SearchEngine);
  }
  $list_str = '';
  $list_arr = array();
  if(_is_file($file_full_name)){  
    $list_str = file_get_contents($file_full_name);
    $list_str = trim($list_str);
    $list_arr = explode(";;",$list_str);
  }  
?>
  <table bgcolor='' cellspacing="0" cellpadding="5" border="0" width=100%>
  
    <tr height=25 bgcolor="#d7d7d7">
      <td align="left">   
        <b>Modification list</b>&nbsp;&nbsp;<a href="javascript: update_modifications()">[update]</a>
      </td>
      <td valign="bottem" align="right">
        <a href="javascript: hideTip_m('D_mod_id');"><img border="0" src="images/icon_remove.gif" alt="Close"></a>&nbsp;
      </td>
    </tr>
    <tr>
      <td colspan=2>
        <select id="frm_modification" name="frm_modification" size=15 multiple>
        <option value="" selected>All peptides
  <?php if($list_str){?>       
        <option value="ALL">Only modified peptides
    <?php foreach($list_arr as $val){
        if(!$val) continue;
    ?>
        <option value="<?php echo $val?>"><?php echo $val?>
    <?php }?>
  <?php }?>
        </select>
      </td>
    </tr>
    <tr><td>&nbsp;</td></tr> 
  </table>                
  <?php 
}

function update_modifications($SearchEngine){
  global $HITSDB,$AccessProjectID;
  $Modification_arr = array();
  
  if(preg_match('/^TPP_(.+)$/', $SearchEngine, $matches_main)){
    $SearchE_name = $matches_main[1];
    if($SearchE_name){
      $SQL = "SELECT G.Sequence,P.SearchEngine FROM TppPeptideGroup G
              LEFT JOIN TppProtein P ON(G.ProteinID=P.ID)
              LEFT JOIN Bait B ON(P.BaitID=B.ID)
              WHERE B.ProjectID='$AccessProjectID'
              AND P.SearchEngine='$SearchE_name'
              GROUP BY G.Sequence";
           
      $tmp_s_arr = $HITSDB->fetchAll($SQL);
      foreach($tmp_s_arr as $tmp_s_val){
        if(preg_match_all('/([A-Z]\[\d+\])/',$tmp_s_val['Sequence'],$matches)){
          foreach($matches[1] as $val){
            if(!in_array($val, $Modification_arr)){
              array_push($Modification_arr, $val);
            }
          }
        }
      }
    }
  }elseif(preg_match('/^GeneLevel_(.+)$/', $SearchEngine, $matches_main)){
    $SearchE_name = $matches_main[1];
    $SearchE_name_upload = $matches_main[1].'Uploaded';
    $SQL = "SELECT P.Modifications,H.SearchEngine FROM Peptide_GeneLevel P 
            LEFT JOIN Hits_GeneLevel H ON(P.HitID=H.ID) 
            LEFT JOIN Bait B ON(H.BaitID=B.ID)
            WHERE B.ProjectID='$AccessProjectID'
            AND (H.SearchEngine='$SearchE_name' OR H.SearchEngine='$SearchE_name_upload')
            GROUP BY P.Modifications";
    $tmp_s_arr = $HITSDB->fetchAll($SQL);            
    foreach($tmp_s_arr as $tmp_s_val){
      if(preg_match_all('/([A-Z])[ ]?\[\d+\][ ]?(-?\d+\.\d+)/',$tmp_s_val['Modifications'],$matches)){
        for($i=0; $i<count($matches[1]); $i++){
          $m_val = $matches[1][$i].' '.$matches[2][$i];
          if(!in_array($m_val, $Modification_arr)){
            array_push($Modification_arr, $m_val);
          }
        }
      }elseif(preg_match_all('/[A-Za-z]+[ ]?\([A-Z]+\)/',$tmp_s_val['Modifications'],$matches)){
        foreach($matches[0] as $val){
          if(!in_array($val, $Modification_arr)){
            array_push($Modification_arr, $val);
          }
        }
      }
    }  
  }else{
    $SearchE_name = $SearchEngine;
    $SearchE_name_upload = $SearchEngine.'Uploaded';
  
      
    $SQL = "SELECT P.Modifications,H.SearchEngine FROM Peptide P 
            LEFT JOIN Hits H ON(P.HitID=H.ID) 
            LEFT JOIN Bait B ON(H.BaitID=B.ID)
            WHERE B.ProjectID='$AccessProjectID'
            AND (H.SearchEngine='$SearchE_name' OR H.SearchEngine='$SearchE_name_upload') 
            GROUP BY P.Modifications";
    $tmp_s_arr = $HITSDB->fetchAll($SQL);
    foreach($tmp_s_arr as $tmp_s_val){
      
      if(preg_match_all('/([A-Z])[ ]?\[\d+\][ ]?(-?\d+\.\d+)/',$tmp_s_val['Modifications'],$matches)){
        for($i=0; $i<count($matches[1]); $i++){
          $m_val = $matches[1][$i].' '.$matches[2][$i];
          if(!in_array($m_val, $Modification_arr)){
            array_push($Modification_arr, $m_val);
          }
        }
      }elseif(preg_match_all('/[A-Za-z]+[ ]?\([A-Z]+\)/',$tmp_s_val['Modifications'],$matches)){
        foreach($matches[0] as $val){
          if(!in_array($val, $Modification_arr)){
            array_push($Modification_arr, $val);
          }
        }
      }
    }  
    $SQL = "SELECT P.Modifications, H.SearchEngine FROM SequestPeptide P 
            LEFT JOIN Hits H ON(P.HitID=H.ID) 
            LEFT JOIN Bait B ON(H.BaitID=B.ID)
            WHERE B.ProjectID='$AccessProjectID' 
            GROUP BY P.Modifications";
    $tmp_s_arr = $HITSDB->fetchAll($SQL);
    foreach($tmp_s_arr as $tmp_s_val){
      if(preg_match_all('/([A-Z])[ ]?\[\d+\][ ]?(-?\+?\d+\.\d+)/',$tmp_s_val['Modifications'],$matches)){
        for($i=0; $i<count($matches[1]); $i++){
          $m_val = $matches[1][$i].' '.$matches[2][$i];
          if(!in_array($m_val, $Modification_arr)){
            array_push($Modification_arr, $m_val);
          }
        }
      }
    }
  }   
  $modificationDir = STORAGE_FOLDER."Prohits_Data/modification_list/P_$AccessProjectID";
  if(!_is_dir($modificationDir)) _mkdir_path($modificationDir);
  $file_full_name = $modificationDir.'/'.$SearchEngine.'.txt';
  $contents = implode(";;", $Modification_arr);
  file_put_contents($file_full_name, $contents);
}
