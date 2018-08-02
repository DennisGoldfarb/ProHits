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
error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(0);  // it will execute for 24 hours

define ("PROHITS_WEB", "http://prohits.mshri.on.ca/ProhitsPublished/GIPR/");

//ob_start();

$currentType = 'Bait';
$sort_by_item_name = '';
$orderby = '';
$asc_desc = 'DESC';
$filtrColorIniFlag = 0;
$frm_color_mode = 'property';
$filterStyleDisplay = 'none';
$subfilterStyleDisplay = 'none';
$frm_NS = '';
$filterd_prey_cells = '';
$filterd_prey_lines = '';
$prey_cells = '';
$frm_filter_Fequency_value = 0;
$selected_id_string = '';

$red = '#ff8080';
$blue = '#00bfff';
$green = '#92ef8f';

$frm_red = ''; 
$frm_green = ''; 
$frm_blue = '';

$applyFilters = 0;
$frm_apply_filter = 0;
$saint_ID = '';

$is_collapse = '';
$php_file_name = "SAINT_comparison_results_table";

$openF = '';

//-------------------------------------------
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
include("analyst/comparison_common_functions.php");
require_once("msManager/is_dir_file.inc.php");
ini_set("memory_limit","2000M");
require_once("common/HTTP/Request_Prohits.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);

$export_to_Prohits_permission = 0;
if(in_array($SCRIPT_NAME, $_SESSION['USER']->AccessPages_arr)){
  $export_to_Prohits_permission = 1;
}

if(STORAGE_IP == 'localhost'){
   $storage_url = "";
}else{
  $storage_url = "http://".STORAGE_IP;
}
$maxScore = 0;

$SAINT_dir = "../TMP/SAINT_comparison/P_$AccessProjectID/";
if(!_is_dir($SAINT_dir)) _mkdir_path($SAINT_dir);
$table_file = "_table.csv";
$full_table_file = $SAINT_dir.$AccessUserID.$table_file;

$export_file_dir = $SAINT_dir."U".$AccessUserID."/";
if(!_is_dir($export_file_dir)) _mkdir_path($export_file_dir);
$export_file = $export_file_dir."prey.csv";
$tmp_bait_file = $export_file_dir."bait.dat";
$otherInfo_file = $export_file_dir."otherInfo.dat";
$OF_map_file = $export_file_dir."OF_map.dat";


$other_handle = @fopen($otherInfo_file, "w");
if(!$other_handle){
  echo "Cannot open file $other_handle";
  exit;
}

$saint_folder = STORAGE_FOLDER."Prohits_Data/SAINT_results/saint_".$saint_ID."/";
$bait_file = $saint_folder."bait.dat";
$log_file = $saint_folder."log.dat";



//---------------------------------------------------------------------------------------------------------------
  $log_handle = @fopen($log_file, "r");
  if(!$log_handle){
    echo "Cannot open file $log_file";
    exit;
  }  
  while(($buffer = fgets($log_handle, 4096)) !== false){
//echo "$buffer<br>";
    $buffer = trim($buffer);
    if(preg_match("/^IS_COLLAPSE:(\w+)/i", $buffer, $matches)){
      $IS_COLLAPSE = $matches[1];
    }elseif(preg_match("/^ID_TYPE:(\w+)/i", $buffer, $matches)){
      $ID_TYPE = $matches[1];
    }elseif(preg_match("/^SELECTED_ID:(.+)/i", $buffer, $matches)){
      $SELECTED_ID = $matches[1];
    }elseif(preg_match("/^Project DBname:(.+)/i", $buffer, $matches)){
      $DBname = trim($matches[1]);
    }elseif(preg_match("/^Project name:(.+)/i", $buffer, $matches)){
      $Project_name = trim($matches[1]);
    }
  }
  
  if(!isset($DBname) || !$DBname){
    $SQL = "SELECT `DBname` FROM `Projects` WHERE `Name`='$Project_name'";
    $tmp_project_arr = $PROHITSDB->fetch($SQL);
    $DBname = $tmp_project_arr['DBname'];
  }
  
  fclose($log_handle);
  $bait_file_lable = "p_s_ID\tbaitName\tcontrol\tProjectID\tSampleID\tItemType\tDBname\tNameIDstr\tName\tID\tGeneID\tGeneName\tTaxID\tBaitAcc\tAccType\tVector\trealGeneName\r\n";
  $lines_export = file($bait_file);
  
  $Name_ID_arr = array();
  foreach($lines_export as $line_export){
    $wline = trim($line_export);
    $tmp_arr = explode("\t",$wline);
    list($projectID, $itemID) = explode("_",$tmp_arr[0]);
    $itemName = $tmp_arr[1];
    if(!array_key_exists($itemName, $Name_ID_arr)){
      $Name_ID_arr[$itemName] = $itemID;
    }else{
      $Name_ID_arr[$itemName] .= ",".$itemID;
    }
  }
  
  $bait_handle_export = @fopen($tmp_bait_file, "w");
  if(!$bait_handle_export){
    echo "Cannot open file $tmp_bait_file";
    exit;
  }
  fwrite($bait_handle_export, $bait_file_lable);
  
  $Vector_arr = array();
  foreach($lines_export as $line_export){
    $wline = trim($line_export);
    $tmp_arr = explode("\t",$wline);
    list($projectID, $itemID) = explode("_",$tmp_arr[0]);
    $itemName = $tmp_arr[1];
    $Name_ID_ID_str = '';
    if(array_key_exists($itemName, $Name_ID_arr)){
      $Name_ID_str = $Name_ID_arr[$itemName];
    }
    
    if($IS_COLLAPSE == 'no'){
      $item_TYPE = 'Band';
      $SQL = "SELECT B.ID,
                    B.GeneID,
                    B.GeneName,
                    B.TaxID,
                    B.BaitAcc,
                    B.AccType,
                    B.Vector
                    FROM Band S
                    LEFT JOIN Bait B 
                    ON S.BaitID=B.ID 
                    WHERE S.ID = '$itemID'
                    AND B.ProjectID = '$projectID'";
    }else{
      if($IS_COLLAPSE == 'Bait'){
        $item_TYPE = 'Bait'; 
        $SQL = "SELECT ID,
                      GeneID,
                      GeneName,
                      TaxID,
                      BaitAcc,
                      AccType,
                      Vector
                      FROM Bait
                      WHERE ID = '$itemID'
                      AND ProjectID = '$projectID'";
      }else{
        $item_TYPE = 'Exp'; 
          $SQL = "SELECT B.ID,
                    B.GeneID,
                    B.GeneName,
                    B.TaxID,
                    B.BaitAcc,
                    B.AccType,
                    B.Vector
                    FROM Experiment E
                    LEFT JOIN Bait B 
                    ON E.BaitID=B.ID 
                    WHERE E.ID = '$itemID'
                    AND B.ProjectID = '$projectID'";
      }  
    }
    $wline .= "\t".$projectID."\t".$itemID."\t".$item_TYPE."\t".$DBname."\t".$Name_ID_str; 
    
    $inf_arr = $HITSDB->fetch($SQL);
    if(!$inf_arr){
      $inf_arr = array('ID' => '',
                        'GeneID' => '',
                        'GeneName' => '',
                        'TaxID' => '',
                        'BaitAcc' => '',
                        'AccType' => '',
                        'Vector' => '');
    }
  
    if($inf_arr && $inf_arr['GeneID']){
      $Bait_geneID = $inf_arr['GeneID'];
    }else{
      $Bait_geneID = '';
    }
    $Bait_real_geneName = '';
    if($Bait_geneID && $Bait_geneID > 0){
      $SQL_tmp = "SELECT `GeneName` FROM `Protein_Class` WHERE `EntrezGeneID`='$Bait_geneID'";
      if($tmp_geneName_arr = $proteinDB->fetch($SQL_tmp)){
        if($tmp_geneName_arr['GeneName']) $Bait_real_geneName = $tmp_geneName_arr['GeneName'];
      }
    }
    $inf_line = implode("\t",$inf_arr);
    $tmp_tmp_arr = explode("(",$tmp_arr[1]);
    $Name = $tmp_tmp_arr[0];
    $wline .= "\t".$Name."\t".$inf_line."\t".$Bait_real_geneName."\r\n";

    fwrite($bait_handle_export, $wline);
    if(trim($inf_arr['Vector']) && !in_array($inf_arr['Vector'], $Vector_arr)){
      $Vector_arr[] = $inf_arr['Vector'];
    }
  }
  fclose($bait_handle_export);
  
  
  
  
//---------------------------------------------------------------------------------------------------------------
if($theaction == 'export_to_prohits_web'){
//##########################################################################################################
  if(is_file($OF_map_file)){
    unlink($OF_map_file);
  }//exit;  
  
  $Vector_str = '';
  if($Vector_arr){
    $Vector_str = implode(',',$Vector_arr);
  }
  //===================================================================
  if($Vector_str && $Username && $Password){
    get_Info_from_OpenFreezer($Vector_str,$Username,$Password);
  }
  //===================================================================
  create_file_for_item_insert_info($ID_TYPE,$IS_COLLAPSE,$SELECTED_ID);
  //===================================================================
//exit;
  
//##########################################################################################################
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_VERBOSE, 1);
  // true to return the transfer as a string of the return value
  // of 'curl_exec' instead of outputting it directly
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
  //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
  //curl_setopt($ch, CURLOPT_URL, 'http://192.197.250.119/ProhitsPublished/GIPR/receiver.php');
  curl_setopt($ch, CURLOPT_URL, PROHITS_WEB.'receiver.php');
  curl_setopt($ch, CURLOPT_POST, true);
  $post = array(
    'prey' => "@$export_file",
    'bait' => "@$tmp_bait_file",
    
    'log' => "@$log_file",
    'otherInfo' => "@$otherInfo_file",
    'theaction' => "file_from_prohits",
    'file_str' => "prey,bait,log,otherInfo",
    'saint_ID' => "$saint_ID"
  );
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  $response = curl_exec($ch);
  echo $response;
  exit;
}

if($theaction == 'export_table_file'){
  export_raw_file($full_table_file);
  exit;
}

if(!$saint_ID) exit;
?>
<center>
<div style='display:block' id='process'><img src='./images/process.gif' border=0></div>
</center>
<?php 
ob_flush();
flush();

$reportFileName = $SAINT_dir.$AccessUserID."_report.txt";

$reportFile_handle = fopen($reportFileName, "w");
if(!$reportFile_handle){
  echo "Cannot open file $reportFileName";
}
$map_file = "_PSI_MI_map.txt";
$full_map_file = $SAINT_dir.$AccessUserID.$map_file;
$html_file_name = $SAINT_dir.$AccessUserID."_report.html";
$png_file_name = $SAINT_dir.$AccessUserID."_report.png";

$filter_for = 'SAINT';

//================================================================================

if(!$is_uploaded){
  $log_handle = @fopen($log_file, "r");
  if(!$log_handle){
    echo "Cannot open file $log_file";
    exit;
  } 
  $SearchEngine = '';
  $item_type = '';
  while(!feof($log_handle)){
    $buffer = fgets($log_handle, 4096);
    $buffer = trim($buffer);
    if(!$buffer) continue;
    if(preg_match("/^SearchEngine:\s*(\w.+)/i", $buffer, $matches)){
      $SearchEngine = $matches[1];
    }elseif(preg_match("/^ID_TYPE:\s*(\w+)/i", $buffer, $matches)){
      $item_type = $matches[1];
      if($item_type == 'Band'){
        $item_type = 'Sample';
      }elseif($item_type == 'Exp'){
        $item_type = 'Experiment';
      }
    }elseif(preg_match("/^IS_COLLAPSE:\s*(\w+)/i", $buffer, $matches)){
      $is_collapse = $matches[1];
    }elseif(preg_match("/^SELECTED_ID:\s*(\S.+)/i", $buffer, $matches)){
      
      //$selected_id_string = $matches[1];
    }
  }  
  fclose($log_handle);
}

$SearchEngine_for_raw = $SearchEngine;
$SearchEngine = str_replace(" ", "_", $SearchEngine);
if(stristr($SearchEngine, 'GeneLevel')){
  $SearchEngine = str_replace("XTandem", "GPM", $SearchEngine);
  $hitType_for_g = 'GeneLevel';
}elseif(stristr($SearchEngine, 'TPP')){
  $hitType_for_g = 'TPP';
}elseif(stristr($SearchEngine, 'iProphet')){
  $hitType_for_g = 'TPP';
}else{
  $hitType_for_g = 'nomal';
}

$table_handle = fopen($full_table_file, "w");
if(!$table_handle){
  echo "Cannot open file $full_table_file";
  return;
}

$export_handle = fopen($export_file, "w");
if(!$export_handle){
  echo "Cannot open file $export_file";
  return;
}

$saint_file_info = array();
if($saint_ID){
  $SQL = "SELECT ID,
         `Name`,
         `UserID`, 
         `Date` , 
         `Description`, 
         `Status` , 
         `ProjectID`, 
         `UserOptions`
          FROM SAINT_log 
          WHERE ID=$saint_ID";
  $saint_file_info = $PROHITSDB->fetch($SQL);
}

if($saint_file_info){
  $info_line = "SAINT Name: ".$saint_file_info['Name']."\r\n";
  fwrite($table_handle, $info_line);
  fwrite($export_handle, $info_line);
  $Owner = get_userName($PROHITSDB, $saint_file_info['UserID']);
  $info_line = "SAINT Owner: ".$Owner."\r\n";
  fwrite($table_handle, $info_line);
  fwrite($export_handle, $info_line);
  $info_line = "SAINT Date: ".$saint_file_info['Date']."\r\n";
  fwrite($table_handle, $info_line);
  fwrite($export_handle, $info_line);
} 
$request_arr['SearchEngine'] = $SearchEngine;
$filter_export_arr = array();
$filter_export_arr_2 = array();
$filter_export_arr_3 = array();
get_filter_array_for_export($request_arr);
write_filter_info_map($table_handle, "SAINT"); 
write_filter_info_map($export_handle, "SAINT");
fwrite($table_handle, "\r\n");
fwrite($export_handle, "\r\n");

//===============================================================

$itemName_geneID_arr = array();
$bait_gene_id_arr = array();

//echo "\$saint_folder=$saint_folder<br>";exit;

if(!$is_uploaded){
  $bait_file = $saint_folder. "bait.dat";
  $bait_handle = @fopen($bait_file, "r");
  if(!$bait_handle){
    echo "Cannot open file $bait_file";
    exit;
  }
  $item_ID_str = '';
  $itemID_itemName_arr = array();
  $itemName_itemID_arr = array();
  
  while(!feof($bait_handle)){
    $buffer = fgets($bait_handle, 4096);
    $buffer = trim($buffer);
    if(!$buffer) continue;
    $tmp_arr = explode("\t", $buffer);
    
    $item_name = $tmp_arr[1];
    $tmp_arr_2 = explode("_", $tmp_arr[0]);
    $item_ID = $tmp_arr_2[1];
    
    $itemID_itemName_arr[$item_ID] = $item_name;
    
    if(!array_key_exists($item_name, $itemName_itemID_arr)){
      $itemName_itemID_arr[$item_name] = array();
    }
    array_push($itemName_itemID_arr[$item_name], $item_ID);
    if($item_ID_str) $item_ID_str .= ',';
    $item_ID_str .= $item_ID;
  }
  
  fclose($bait_handle);
  
  if($item_ID_str){
    if($is_collapse == 'no' || !$is_collapse){
      $SQL = "SELECT S.ID,
              B.ID AS BaitID,
              B.GeneID,
              B.GeneName,
              B.BaitAcc,
              B.TaxID,
              S.Location 
              FROM Band S
              LEFT JOIN Bait B 
              ON(S.BaitID=B.ID) 
              WHERE S.ID IN($item_ID_str)";
      $selected_id_string = $item_ID_str;
    }elseif($is_collapse == 'Experiment'){
      $SQL = "SELECT E.ID,
              B.ID AS BaitID,
              B.GeneID,
              B.GeneName,
              B.BaitAcc,
              B.TaxID,
              E.Name AS Location
              FROM Experiment E
              LEFT JOIN Bait B 
              ON(E.BaitID=B.ID) 
              WHERE E.ID IN($item_ID_str)";
              
      $tmp_SQL = "SELECT `ID` FROM `Band` WHERE `ExpID` IN ($item_ID_str)";        
      $tmp_SQL_arr = $HITSDB->fetchAll($tmp_SQL);
      $selected_id_arr = array();
      foreach($tmp_SQL_arr as $tmp_SQL_val){
        $selected_id_arr[] = $tmp_SQL_val['ID'];
      }
      $selected_id_string = implode(',',$selected_id_arr);
              
    }else if($is_collapse == 'Bait'){
      $SQL = "SELECT ID,
              ID AS BaitID,
              GeneID,
              GeneName,
              BaitAcc,
              TaxID,
              GeneName AS Location 
              FROM Bait
              WHERE ID IN($item_ID_str)";
              
      $tmp_SQL = "SELECT `ID` FROM `Band` WHERE `BaitID` IN ($item_ID_str)";        
      $tmp_SQL_arr = $HITSDB->fetchAll($tmp_SQL);
      $selected_id_arr = array();
      foreach($tmp_SQL_arr as $tmp_SQL_val){
        $selected_id_arr[] = $tmp_SQL_val['ID'];
      }
      $selected_id_string = implode(',',$selected_id_arr); 
    }         
    $tmp_item_arr = $HITSDB->fetchAll($SQL);
  }   
  
  $itemID_property_arr = array();
  foreach($tmp_item_arr as $tmp_item_val){
    $itemID_property_arr[$tmp_item_val['ID']] = $tmp_item_val;
  }
  
  $itemName_property_arr = array();

  foreach($itemName_itemID_arr as $key => $val){
    $tmp_item_ID_arr = $val;
    $tmp_acc = '';
    $flag = 0;    
    foreach($tmp_item_ID_arr as $tmp_item_ID_val){
      if(!$tmp_acc){
        $tmp_acc = $itemID_property_arr[$tmp_item_ID_val]['BaitAcc']; //--or ['GeneID']
      }else{
        if($tmp_acc != $itemID_property_arr[$tmp_item_ID_val]['BaitAcc']){ //--or ['GeneID']
          $tmp_acc_2 = $itemID_property_arr[$tmp_item_ID_val]['BaitAcc'];
          $flag = 1;
          break;
        }
      }  
    }
    if($flag){
      //echo "Sample name $key hes diffent proteins $tmp_acc and $tmp_acc_2"; //--or GeneID
      //exit;
    }
    if($tmp_item_ID_arr){
      $itemName_property_arr[$key] = $itemID_property_arr[$tmp_item_ID_arr[0]];
    }  
  }  
  
  $allBaitgeneID_str = '';
  $tmpBaitGeneIDarr = array();
  $du_Name_in_aGeneID_arr = array();
  
  $itemID_geneID_arr = array();  
  
  foreach($tmp_item_arr as $tmp_item_val){
    if(!in_array($tmp_item_val['GeneID'], $tmpBaitGeneIDarr)){
      array_push($tmpBaitGeneIDarr, $tmp_item_val['GeneID']);
      $du_Name_in_aGeneID_arr[$tmp_item_val['GeneID']] = array();
    }
    $itemID_geneID_arr[$tmp_item_val['ID']] = $tmp_item_val['GeneID'];
    array_push($du_Name_in_aGeneID_arr[$tmp_item_val['GeneID']], $itemID_itemName_arr[$tmp_item_val['ID']]);  
  }
 
  foreach($itemID_itemName_arr as $key => $val){
    if(!array_key_exists($val, $itemName_geneID_arr)){
      $itemName_geneID_arr[$val] = $itemID_geneID_arr[$key];
      array_push($bait_gene_id_arr, $itemID_geneID_arr[$key]);
    }
  }  
  $allBaitgeneID_str =  implode(",", $tmpBaitGeneIDarr);
  
  $du_Name_in_aGeneID_str_arr = array();
  foreach($du_Name_in_aGeneID_arr as $tmpKey => $tmpVal){
    if(count($tmpVal) > 1){
      $tmpNames = implode(":", $tmpVal);
      $tmpNames = $tmpKey."@".$tmpNames;
      array_push($du_Name_in_aGeneID_str_arr, $tmpNames);
    }
  }
  $du_Name_in_aGeneID_str = implode(",", $du_Name_in_aGeneID_str_arr);
}
$filter_export_arr = array();
$filter_export_arr_2 = array();
$filter_export_arr_3 = array();

get_filter_array_for_export($request_arr);
if(!$is_uploaded){
  $filter_export_arr['Item Type'] = $item_type;
  $filter_export_arr['SearchEngine'] = $SearchEngine;
}  
write_filter_info_map($reportFile_handle);

$apply_bioGrid = 0;
if(isset($frm_biogrid_pHTP) && $frm_biogrid_pHTP){
  $apply_bioGrid = 1;
}elseif(isset($frm_biogrid_pNONHTP) && $frm_biogrid_pNONHTP){
  $apply_bioGrid = 1;
}elseif(isset($frm_biogrid_gHTP) && $frm_biogrid_gHTP){
  $apply_bioGrid = 1;
}elseif(isset($frm_biogrid_gNONHTP) && $frm_biogrid_gNONHTP){
  $apply_bioGrid = 1;
}

$filterd_prey_arr = array();
if($filterd_prey_cells){
  $filterd_prey_arr = explode(",",$filterd_prey_cells);
}
$filterd_prey_lines_arr = array();
if($filterd_prey_lines){
  $filterd_prey_lines_arr = explode(",",$filterd_prey_lines);
}

if($currentType == 'Bait'){
  $typeLable = 'Bait';
  $singleTypeLable = 'Bait';
}elseif($currentType == 'Exp'){
  $typeLable = 'Experiment';
  $singleTypeLable = 'ExpID&nbsp;&nbsp;ExpName';
}else{
  $typeLable = 'Sample';
  $singleTypeLable = 'SampleID&nbsp;&nbsp;SampleName';
}

if(!_is_dir("../TMP/bioGrid/")) _mkdir_path("../TMP/bioGrid/");
$tmp_file = "../TMP/bioGrid/". $USER->ID .".csv";

if(!$matchGred_handle = fopen($tmp_file, 'w')){
  echo "Cannot open file ($tmp_file)";
  exit;
}
fwrite($matchGred_handle,"edge_info\r\n");
if(!isset($frm_NS_group_id) || !$frm_NS_group_id){
  $frm_NS_group_id = '';
}
$SQL = "SELECT `ID`,`Name` FROM `ExpBackGroundSet` WHERE `ProjectID`='$AccessProjectID'";
$NSarr = $HITSDB->fetchAll($SQL);

$typeBioArr = array();
$typeExpArr_tmp = array();
$typeExpArr = array();
$typeFrequencyArr = array();
create_filter_status_arrs($typeBioArr,$typeExpArr_tmp,$typeFrequencyArr,'comparison');
$filterArgumentsStr = '';

foreach($typeBioArr as $typeBioValue){
  $frmName = 'frm_' . $typeBioValue['Alias'];
  if($theaction == 'generate_report'){
    $$frmName = $typeBioValue['Init'];
  }else{
    if(!isset($$frmName)){
      $$frmName = "0";
    }
  }
  $filterArgumentsStr .= '@@'.$frmName.'='.$$frmName;
}

$NSfilteIDarr = array();
if(isset($frm_NS) && $frm_NS && $frm_NS_group_id){
  get_NS_geneID($NSfilteIDarr,$frm_NS_group_id);
}

if($filtrColorIniFlag){
  $frm_red = 'y'; 
  $frm_green = 'y'; 
  $frm_blue = 'y';
}

if(strstr($SearchEngine, 'TPP') ){
  $frequencyFileName = 'tpp_frequency.csv';
}elseif($SearchEngine){  
  $frequencyFileName = $SearchEngine.'_frequency.csv';
}else{
  $frequencyFileName = 'frequency.csv';
}

$geneID_frequency_arr = array();
$fequMaxScore = get_frequency_arr($geneID_frequency_arr,$frequencyFileName);

//------------------------------------------------------------------------------
$saint_result_file = '';
if(_is_file($saint_folder . "RESULT/unique_interactions")){
  $saint_result_file = $saint_folder . "RESULT/unique_interactions";
  $tmp_result_file = 'unique_interactions';
}else if(_is_file($saint_folder . "RESULT/list.txt")){
  $saint_result_file = $saint_folder . "RESULT/list.txt";
  $tmp_result_file = 'list.txt';
}

$file_arr = array();
$bait_lable_arr = array();
$bait_gene_name_arr = array();
$table_array = array();
if(!$saint_result_file){
  $SQL = "update SAINT_log set Status='Error' where ID='$saint_ID'";
  $PROHITSDB->update($SQL);
  echo "<font color=#FF0000>There is no SAINT result file. Please click the SAINT log for the detail. If it was connection error you can re-run the SAINT task.</font>";
  exit;
}

$lines = file($saint_result_file);

if(count($lines) ==1){
  //handle one line file.
  $file_arr = explode("\r",$lines[0]);
}else{
  $RESULT_handle = fopen($saint_result_file, "r");
  if(!$RESULT_handle){
    echo "Cannot open file $saint_result_file";
    exit;
  }
  while(!feof($RESULT_handle)){
    $buffer = fgets($RESULT_handle);
    //$buffer = trim($buffer);
    if(!$buffer) continue;
    array_push($file_arr, $buffer);
  }
  fclose($RESULT_handle);
}

$buffer = array_shift($file_arr);
$col_name_arr_tmp = explode("\t",$buffer);

$col_name_arr = array();
$field_lable_arr = array();

//$col_name_match_arr = array('NumRep'=>'NumReplicates');
$col_name_match_arr = array();

for($i=0;$i<count($col_name_arr_tmp);$i++){
  if(array_key_exists($col_name_arr_tmp[$i], $col_name_match_arr)){
    $col_name_arr_tmp[$i] = $col_name_match_arr[$col_name_arr_tmp[$i]];
  }
  $col_name_arr_tmp[$i] = trim($col_name_arr_tmp[$i]);
  $col_name_arr[$i] = strtoupper($col_name_arr_tmp[$i]);
  $field_lable_arr[$col_name_arr[$i]] = $col_name_arr_tmp[$i];
}

$col_name_arr_key = $col_name_arr;

$extra_name_arr = array('maxSpec','PreyGeneID','PreyGeneName','Project_Frequency','Shared_Frequency','BioGrid','High_confidence');
for($i=0;$i<count($extra_name_arr);$i++){
  $capital = strtoupper($extra_name_arr[$i]);
  array_push($col_name_arr, $capital);
  $field_lable_arr[$capital] = $extra_name_arr[$i];
}

$sort_list_arr = array('SAINTSCORE','SPECSUM','AVGP','MAXSPEC','MAXP','NUMREPLICATES','NUMREP','PROJECT_FREQUENCY','BFDR');
$display_list_arr = array('BAIT','PREY','PREYGENE','SAINTSCORE','FOLDCHANGE','SPECSUM','AVGP','MAXSPEC','MAXP','TOPOMAXP','NUMREPLICATES','NUMREP','PROJECT_FREQUENCY','BFDR');
$line_info_arr = array('MAXSPEC','AVGP','MAXP','TOPOMAXP','NUMREPLICATES','NUMREP','PROJECT_FREQUENCY','SHARED_FREQUENCY','SAINTSCORE', 'BFDR','HIGH_CONFIDENCE');
$sort_list_arr = array_intersect($sort_list_arr, $col_name_arr);
$display_list_arr = array_intersect($display_list_arr, $col_name_arr);
$line_info_arr = array_intersect($line_info_arr, $col_name_arr);
$line_info_str = implode(",", $line_info_arr);

$field_lable_key_str = '';
$field_lable_val_str = '';
foreach($field_lable_arr as $key => $val){
  if($field_lable_key_str){
    $field_lable_key_str .= ',';
    $field_lable_val_str .= ',';
  }  
  $field_lable_key_str .= $key;
  $field_lable_val_str .= $val;
}

$sortColorArr_for_saint = array();
$sortColorArr_for_saint['SAINTSCORE'] = 'oliver';         //SpecSum
$sortColorArr_for_saint['SPECSUM'] = 'red';         //SpecSum
$sortColorArr_for_saint['AVGP'] = 'blue';       //AvgP
$sortColorArr_for_saint['MAXSPEC'] = 'purple';       //maxP
$sortColorArr_for_saint['MAXP'] = 'purple';
$sortColorArr_for_saint['NUMREPLICATES'] = 'oliver';
$sortColorArr_for_saint['NUMREP'] = 'oliver';
$sortColorArr_for_saint['PROJECT_FREQUENCY'] = 'green';      //Frequence

if(!$orderby){
  $orderby = current($sort_list_arr);
}
$export_talble_index_arr = $col_name_arr;

$M_SpecSum = 0;
$M_maxSpec = 0;

$tem_c = 0;

$tmp_prey_gene_ID_arr = array();

$prey_index_arr = array();
$prey_propty_arr = array();
//--------------------------------------------------------------------------
//echo @date("H:i:s")."<br>";
if(!stristr($SearchEngine, 'GeneLevel')){
  $gi_arr_tmp = array();
  foreach($file_arr as $file_val){
    $data_arr_tmp = explode("\t",$file_val);  
    if($data_arr_tmp[1] && is_numeric($data_arr_tmp[1])){
      if(!array_key_exists($data_arr_tmp[1], $gi_arr_tmp)){
        $gi_arr_tmp[$data_arr_tmp[1]] = 1;
      }
    }
  }
  $gi_str = '';
  foreach($gi_arr_tmp as $gi_key => $gi_val){
    if($gi_str) $gi_str .= ',';
    $gi_str .= $gi_key;
  }
  $gi_acc_arr = replease_gi_with_Acc_Version($gi_str);
  for($i=0; $i<count($file_arr); $i++){
    $data_arr_tmp = explode("\t",$file_arr[$i]);
    if(array_key_exists($data_arr_tmp[1], $gi_acc_arr)){
      $data_arr_tmp[1] = $gi_acc_arr[$data_arr_tmp[1]]['Acc_Version'];
    }
    $file_arr[$i] = implode("\t",$data_arr_tmp);
  }
}
//echo @date("H:i:s")."<br>";
//--------------------------------------------------------------------------

foreach($file_arr as $buffer){
  if(!$buffer) continue;
  $buffer = str_replace("-nan", "0", $buffer);
  $data_arr_tmp = explode("\t",$buffer);
  
  for($i=0;$i<count($data_arr_tmp);$i++){
    $data_arr[$i] = trim($data_arr_tmp[$i]);
  }   
  $tmpArr = array_combine($col_name_arr_key,$data_arr);
  if(!in_array($tmpArr['BAIT'], $bait_lable_arr)){
    $bait_lable_arr[$tmpArr['BAIT']] = $tmpArr['BAIT'];
  }
  if(!array_key_exists($tmpArr['BAIT'], $table_array)){
    $table_array[$tmpArr['BAIT']] = array();
    //echo $tmpArr['BAIT']."<br>";
  }
  if(!in_array($tmpArr['BAIT'], $bait_gene_name_arr)){
    array_push($bait_gene_name_arr, $tmpArr['BAIT']);
  }
  
  $prey_gene_name_id_arr = preg_split("/[\(\)]+/", $tmpArr['PREYGENE']);  
  $gene_id = '';
  if(stristr($SearchEngine, 'GeneLevel')){
    $gene_id = $tmpArr['PREY'];
  }else{
    if(count($prey_gene_name_id_arr) > 1){
      $gene_id = $prey_gene_name_id_arr[1];
    }else{
      if(array_key_exists($tmpArr['PREY'], $tmp_prey_gene_ID_arr)){
        $gene_id = $tmp_prey_gene_ID_arr[$tmpArr['PREY']];
      }else{
        if(is_numeric($tmpArr['PREY'])){
          $gene_id = get_protein_GeneID($tmpArr['PREY'], 'GI', $proteinDB);
        }else{
          $gene_id = get_protein_GeneID($tmpArr['PREY'], '', $proteinDB);
        }
        $tmp_prey_gene_ID_arr[$tmpArr['PREY']] = $gene_id;
      }
    }
  }
    
  $gene_name = $prey_gene_name_id_arr[0];
  $spec_arr = explode("|",$tmpArr['SPEC']);
  
  rsort($spec_arr);
  $tmpArr['MAXSPEC'] = $spec_arr[0];
  
  $tmpArr['PREYGENEID'] = $gene_id;
  $tmpArr['PREYGENENAME'] = $gene_name; 
  
  if($M_SpecSum < $tmpArr['SPECSUM']) $M_SpecSum = $tmpArr['SPECSUM'];
  if($M_maxSpec < $tmpArr['MAXSPEC']) $M_maxSpec = $tmpArr['MAXSPEC'];
  
  if($orderby != 'PROJECT_FREQUENCY'){
    if($maxScore < $tmpArr[$orderby]){
      $maxScore = $tmpArr[$orderby];
    }
  }
 
  $tmpArr['BIOGRID'] = '';  
  $tmpArr['SHARED_FREQUENCY'] = '';
  $tmpArr['HIGH_CONFIDENCE'] = 1;

  //=====filter NS gene here======
  if($frm_apply_filter && in_array($gene_id, $NSfilteIDarr)) $tmpArr['HIGH_CONFIDENCE'] = 0;//continue; 
  //==============================
 
  $prey_frequency = '';
  if(isset($geneID_frequency_arr[$gene_id])) $prey_frequency = $geneID_frequency_arr[$gene_id];
  //===filter frequency here =======
  if($frm_filter_Fequency_value == 101) $frm_filter_Fequency_value = 0;
  if($frm_apply_filter && $frm_filter_Fequency_value && $frm_filter_Fequency_value < $prey_frequency) $tmpArr['HIGH_CONFIDENCE'] = 0;//continue;
  //================================
  $tmpArr['PROJECT_FREQUENCY'] = $prey_frequency;
  //======filter Experiment Filters=====================
  if($frm_apply_filter){
    $filter_flag = 0;
    foreach($sort_list_arr as $sort_list_val){
      if($sort_list_val == 'PROJECT_FREQUENCY') continue;
      $col_name = $field_lable_arr[$sort_list_val];
      if(isset($$col_name)){
        if($col_name == 'BFDR'){
          if($$col_name && $tmpArr[$sort_list_val] > $$col_name){       
            $filter_flag = 1;
            break;
          }
        }else{
          if($$col_name && ($tmpArr[$sort_list_val]) < $$col_name){    
            $filter_flag = 1;
            break;
          }
        }
      }
    }
    if($filter_flag) $tmpArr['HIGH_CONFIDENCE'] = 0;//continue;
  }    
  if($frm_BT && $itemName_geneID_arr[$tmpArr['BAIT']] == $tmpArr['PREYGENEID']){
    $tmpArr['HIGH_CONFIDENCE'] = 0;//continue;
  }
  
  if(!array_key_exists($tmpArr['PREY'], $prey_index_arr)){
    $prey_propty_arr[$tmpArr['PREY']]['gene_name'] = $tmpArr['PREYGENENAME'];
    $prey_propty_arr[$tmpArr['PREY']]['gene_id'] = $tmpArr['PREYGENEID'];
    $prey_propty_arr[$tmpArr['PREY']]['frequency'] = $tmpArr['PROJECT_FREQUENCY'];
    $prey_propty_arr[$tmpArr['PREY']]['count'] = 1;
    $filter_flag = 0;
    if($applyFilters){ 
      $bioFilterArr = array();
      $prey_gene_id = $tmpArr['PREY'];    
      $SQL = "SELECT BioFilter FROM Protein_Class WHERE EntrezGeneID='$prey_gene_id'";
      if($BioFilterArr = $proteinDB->fetch($SQL)){
        if($BioFilterArr['BioFilter']){
          $bioFilterArr = explode(",",$BioFilterArr['BioFilter']);
        }
      }
      foreach($typeBioArr as $Value){
     		$frmName = 'frm_' . $Value['Alias'];
    		if($$frmName and in_array($Value['Alias'] ,$bioFilterArr)){          
    			$filter_flag = 1;
          break;
    		}
    	}
      if($filter_flag && !in_array($prey_index, $filtered_preyID_arr)){
        array_push($filtered_preyID_arr, $prey_index);
      }
    } 
    if($filter_flag){
      $prey_index_arr[$tmpArr['PREY']] = 0;// continue;
      $tmpArr['HIGH_CONFIDENCE'] = 0;
    }else{
      $prey_index_arr[$tmpArr['PREY']] = 1;
    }
    if(!$tmpArr['HIGH_CONFIDENCE']){
      $prey_propty_arr[$tmpArr['PREY']]['filter'] = 1;
    }else{
      $prey_propty_arr[$tmpArr['PREY']]['filter'] = 0;
    }  
  }else{
    $prey_propty_arr[$tmpArr['PREY']]['count']++;
    if($tmpArr['HIGH_CONFIDENCE']) $tmpArr['HIGH_CONFIDENCE'] = $prey_index_arr[$tmpArr['PREY']];
    if(!$tmpArr['HIGH_CONFIDENCE']){
      $prey_propty_arr[$tmpArr['PREY']]['filter']++;
    }
  }
  $table_array[$tmpArr['BAIT']][$tmpArr['PREY']] = $tmpArr;
}
 
if($orderby == 'PROJECT_FREQUENCY'){
  $maxScore = 100;
}

if(!$is_uploaded){
  if($bait_gene_id_arr){
    $allBaitgeneID_str = implode(",", $bait_gene_id_arr);
  }
}
$sorted_table_array = array();
if($table_array && $sort_by_item_name){
  $sorted_table_array[$sort_by_item_name] = $table_array[$sort_by_item_name];
  foreach($table_array as $key => $val){
    if($key == $sort_by_item_name) continue;
    $sorted_table_array[$key] = $val;
  }
}else{
  $sorted_table_array = $table_array;
  $sort_by_item_name = array_shift(array_keys($table_array));
}
unset($table_array);

foreach($sorted_table_array as $key => $val){
  if($asc_desc == 'DESC'){
    uasort($sorted_table_array[$key], "cmp_prey_val_r");
  }else{
    uasort($sorted_table_array[$key], "cmp_prey_val");
  }  
}

$itemlableMaxL = '';
$lableBgc = '000000';
foreach($bait_lable_arr as $val){
  $itemLableL = strlen($val);
  if($itemlableMaxL < $itemLableL) $itemlableMaxL = $itemLableL;
}

if($maxScore < 1) $maxScore = 1;
$biggestPowedSore = $maxScore;
$power = 1;
$aa = '';
$powerColorIndex = $orderby;
get_colorArrSets($powerColorIndex,$colorArrSet,$aa);

$html_handle = fopen($html_file_name, "w");

$html_str = "<html>\r\n<head>\r\n<meta http-equiv='content-type' content='text/html;charset=iso-8859-1'>\r\n<title>SAINT comparison</title>\r\n";
fwrite($html_handle, $html_str);
echo "$html_str";

$tool_tip_style_name = "./tool_tip_style.css";
$html_str = file_get_contents($tool_tip_style_name);
fwrite($html_handle, "<style type='text/css'>\r\n");
fwrite($html_handle, $html_str);
fwrite($html_handle, "</style>\r\n");

$site_javascript_name = "../common/javascript/site_javascript.js";
$html_str = file_get_contents($site_javascript_name);
fwrite($html_handle, "<script language='javascript'>\r\n");
fwrite($html_handle, $html_str);
fwrite($html_handle, "</script>\r\n");
?>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<link rel="stylesheet" type="text/css" href="./tool_tip_style.css">
<link rel="stylesheet" type="text/css" href="./colorPicker_style.css">
<script src="../common/javascript/site_javascript.js" type="text/javascript"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script src="../common/javascript/jquery.hoverIntent.js" type="text/javascript"></script>
<script type="text/javascript" src="../common/site_ajax.js"></script>

<script language='javascript'>

function get_sel_prey_cells(){
  theForm = document.form_SAINT_comparison;
  var sel_prey_cells = '';
  if(theForm.prey_cells != undefined){
    prey_cells = theForm.prey_cells;
    if(prey_cells.length == undefined){
      if(prey_cells.checked){
        sel_prey_cells = prey_cells.value;
      }  
    }else{
      for(i=0; i<prey_cells.length; i++){
        if(prey_cells[i].checked){
          if(sel_prey_cells) sel_prey_cells += ',';
          sel_prey_cells += prey_cells[i].value;
        }
      }
    }
  }
  return sel_prey_cells;
}

function get_sel_prey_lines(){
  theForm = document.form_SAINT_comparison;
  var sel_prey_lines = '';
  if(theForm.prey_lines != undefined){
    prey_lines = theForm.prey_lines;
    if(prey_lines.length == undefined){
      if(prey_lines.checked){
        sel_prey_lines = prey_lines.value;
      }  
    }else{
      for(i=0; i<prey_lines.length; i++){
        if(prey_lines[i].checked){
          if(sel_prey_lines) sel_prey_lines += ',';
          sel_prey_lines += prey_lines[i].value;
        }
      }
    }
  }
  return sel_prey_lines;
}

function is_selected_prey_cells_changed(){
  theForm = document.form_SAINT_comparison;
  var last_sel_prey_cells = theForm.filterd_prey_cells.value;
  var last_sel_prey_cells_arr = last_sel_prey_cells.split(',');
  var sel_prey_cells = get_sel_prey_cells();
  var sel_prey_cells_arr = sel_prey_cells.split(',');
  if(last_sel_prey_cells_arr.length == sel_prey_cells_arr.length){
    for(var i=0; i<last_sel_prey_cells_arr.length; i++){
      var match_flag = 0;
      for(var j=0; j<sel_prey_cells_arr.length; j++){
        if(last_sel_prey_cells_arr[i] == sel_prey_cells_arr[j]){
          match_flag = 1;
          break;
        }
      }
      if(match_flag == 0) return true;
    }
    return false;
  }else{
    return true;
  }  
}

function sort_page(){
  theForm = document.form_SAINT_comparison;
  theForm.filtrColorIniFlag.value = '0';
  
  theForm.filterd_prey_cells.value = get_sel_prey_cells();
  theForm.filterd_prey_lines.value = get_sel_prey_lines();
  
  if(typeof(newWin2) == 'object'){
    newWin2.close();
    theForm.target='_parent';
  }
  theForm.target=  '_self';
  theForm.theaction.value = '';
  submit_form();
}

function showhide(DivID){
  var theForm = document.form_SAINT_comparison;
  if(typeof(newWin2) == 'object'){
      newWin2.close();
      theForm.target='_parent';
  }
  var obj = document.getElementById(DivID);
  var obj_a = document.getElementById(DivID + "_a");
  var sub_filter_area = document.getElementById('sub_filter_area');
  tmp_flag = 0;
  for(var i=0; i<theForm.frm_color_mode.length; i++){
    if(theForm.frm_color_mode[i].checked == true && theForm.frm_color_mode[i].value == 'shared'){
      tmp_flag = 1;
      break;
    }
  }  
  if(obj.style.display == "none"){
    sub_filter_area.style.display = "none";
    obj.style.display = "block";
    obj_a.innerHTML = "<font size='2' face='Arial'>[&nbsp;Click to remove filters&nbsp;]</font>";
    theForm.applyFilters.value = '1';
    theForm.frm_apply_filter.value = '1';
  }else{
    if(tmp_flag == 1){
      sub_filter_area.style.display = "block";
    }else{ 
      sub_filter_area.style.display = "none";
    } 
    obj.style.display = "none";
    obj_a.innerHTML = "<font size='2' face='Arial'>[&nbsp;Click to apply filters&nbsp;]</font>";
    theForm.applyFilters.value = '0';
    theForm.frm_apply_filter.value = '0';
  }
} 

function change_color_code(objColor){
  theForm = document.form_SAINT_comparison;
  if(theForm.color_mode.value == objColor.value) return;
  if(typeof(newWin2) == 'object'){
      newWin2.close();
      theForm.target='_parent';
  }
  theForm.filtrColorIniFlag.value = '0';
  if(objColor.value == 'shared'){
     theForm.filtrColorIniFlag.value = '1';
  }
  submit_form();
} 
function submit_form(){ 
  theForm = document.form_SAINT_comparison; 
  var obj = document.getElementById('filter_area');
  theForm.filterStyleDisplay.value = obj.style.display;
  var tmp_flag = 0;
  for(var i=0; i<theForm.frm_color_mode.length; i++){
    if(theForm.frm_color_mode[i].checked == true && theForm.frm_color_mode[i].value == 'shared'){
      tmp_flag = 1;
      break;
    }
  }
  if(obj.style.display == 'none' && tmp_flag == 1){
    theForm.subfilterStyleDisplay.value = 'block';
  }else{
    theForm.subfilterStyleDisplay.value = 'none';
  }
  theForm.action = '<?php echo $PHP_SELF;?>';
  theForm.submit();
}

function to_prohits_web_confirm_div(){
  if(!('<?php echo $saint_v?>'.match(/exp/g))){
    alert("Currently Prohits-web just support SAINT version exp3.");
    return;
  }
  var theForm = document.getElementById('form_SAINT_comparison');
  var Username = '';
  var Password = ''; 
  
  if(theForm.openF.checked){
    var Username = theForm.Username.value;
    var Password = theForm.Password.value;
    if(trimString(Username) == '' || trimString(Password) == ''){
      alert('Please enter Username or Password.');
      return;
    }
  }
  var queryString = "theaction=export_to_prohits_web&saint_ID=<?php echo $saint_ID?>&Username="+Username+"&Password="+Password;
  //alert(queryString);
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
  var prohits_web_confirm_div = document.getElementById('prohits_web_confirm_div');
  hideTip('prohits_web_confirm_div');
}

function download_raw(){
  var theWin = '';
  if (!theWin.closed && theWin.location) {
      theWin.close();
  }
  var theForm = document.getElementById('form_SAINT_comparison');
  theForm.setAttribute("action", "<?php echo $storage_url.dirname(dirname($_SERVER['PHP_SELF']))."/msManager/autoBackup/download_group_raw_file.php";?>");
  //theForm.setAttribute("action", "http://192.197.250.100/testphp/check_post_get.php"); 
  theForm.SID.value = "<?php echo session_id()?>";
  theForm.ID_string.value = '<?php echo $selected_id_string?>';
  theForm.SearchEngine.value = '<?php echo $SearchEngine_for_raw?>';
  theForm.hitType.value = '<?php echo $SearchEngine_for_raw?>';
  theForm.item_type.value = 'Sample';
  theWin = window.open("test.html","downloadW",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=' + 500 + ',height=' + 300);
  theForm.setAttribute("target", "downloadW");
  theWin.focus();
  theForm.submit();
}

function processAjaxReturn(ret_html){
//alert(ret_html)
//return;
  if(ret_html.match(/exists/)){
    var res = ret_html.split("_"); 
    alert("The file(SAINT ID: "+res[2]+") is exists in Prohits Web. You could not upload it again");
    return;
  }else if(ret_html.match(/successfully/)){ 
    var prohits_web_form = document.getElementById('export_prohits_web_form');
    prohits_web_form.target = 'view';  
    newwin = window.open('', 'view','toolbar=1,location=1,directories=1,status=1,menubar=1,scrollbars=1,resizable=1,width=1200,height=800');
    prohits_web_form.submit();
  }else{
    alert("file transfer problem");
    return;
  }
}

function confirmed_div(){
  var theForm = document.form_SAINT_comparison;
  theForm.action = "./comparison_results_export.php";
  theForm.infileName.value = '<?php echo $reportFileName;?>';
  theForm.exportType.value = 'matrix';
  hideTip('matrix_confirm_div');
  theForm.submit();
  theForm.action = '<?php echo $PHP_SELF;?>';
}

function confirm_cyto_div(){
  var theForm = document.form_SAINT_comparison;
  var cyto_qurryStr = theForm.cyto_qurryStr.value;
  var lable = '';
  if(theForm.node_lable[0].checked == true){
    lable = "&node_lable="+ theForm.node_lable[0].value;
  }else if(theForm.node_lable[1].checked == true){
    lable = "&node_lable="+ theForm.node_lable[1].value;
  }else{
    return;
  }
  
  var allBaitgeneID_str =  "&allBaitgeneID_str="+theForm.allBaitgeneID_str.value+"&saint_ID=<?php echo $saint_ID?>"; 
  hideTip('cyto_confirm_div');
  var file = "./cytoscape_export.php?"+cyto_qurryStr+lable+allBaitgeneID_str;
	window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=1200,height=820');
}

function export_table_file(){
  var theForm = document.form_SAINT_comparison;
  theForm.full_table_file.value = "<?php echo $full_table_file?>";
  theForm.theaction.value = "export_table_file";
  if(is_selected_prey_cells_changed()){
    alert("Please click go button first then click [Export (table)].");
  }else{
    theForm.submit();
  }
}

function export_psi_mi_file(){
  var theForm = document.form_SAINT_comparison;
  var file = theForm.SAINT_PSI_MI_URL.value;
  if(is_selected_prey_cells_changed()){
    alert("Please click go button first then click [Export to PSI-MI].");
  }else{
    window.open(file,"_self",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=1200,height=820');
  }  
}

function updateFrequency(){
  if(!confirm("Are you sure that you want to update frequency?")){
    return false;
  }
  theForm = document.form_comparison;
  if(typeof(newWin2) == 'object'){
      newWin2.close();
      theForm.target='_parent';
  }
  file = "./mng_set_frequency.php?theaction=update_only";
  nWin = window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=300');
  nWin.moveTo(4,0);
}

function highlight_tr(tr_id){
  tr_obj = document.getElementById(tr_id);
  if(isIE){
    if(tr_obj.style.backgroundColor == '#ccffcc'){
      tr_obj.style.backgroundColor = '#ececec';
    }else{
      tr_obj.style.backgroundColor = '#ccffcc';
    }
  }else{  
    if(tr_obj.style.backgroundColor == 'rgb(204, 255, 204)'){
      tr_obj.style.backgroundColor = 'rgb(236, 236, 236)';
    }else{
      tr_obj.style.backgroundColor = 'rgb(204, 255, 204)';
    }
  }  
}
 
function change_td_bg_color(checkbox_id,cellBgcolor_backup){
  var checkbox_obj = document.getElementById(checkbox_id);
  var td_obj_id = 't_' + checkbox_id;
  var td_obj = document.getElementById(td_obj_id);
  if(checkbox_obj.checked){
    td_obj.style.backgroundColor = '#c0c0c0';
  }else{
    td_obj.style.backgroundColor = cellBgcolor_backup;
    var tmp_arr = checkbox_id.split('_');
    var t_index = tmp_arr.length - 1;
    var prey_line_id = tmp_arr[t_index];
    var prey_line_obj = document.getElementById(prey_line_id);
    var prey_tr_id = '_' + prey_line_id;
    var prey_tr_obj = document.getElementById(prey_tr_id);
    if(prey_line_obj.checked){
      prey_line_obj.checked = false;
      prey_tr_obj.style.backgroundColor = '#ececec';;
    }
  }
}

function change_tr_bg_color(cell_id_str, prey_index){
  var tr_id = '_' + prey_index;
  var tr_obj = document.getElementById(tr_id);
  var prey_checkbox_obj = document.getElementById(prey_index);
  
  if(prey_checkbox_obj.checked){
    tr_obj.style.backgroundColor = '#c0c0c0';
  }else{
    tr_obj.style.backgroundColor = '#ececec';
    //tr_obj.style.backgroundColor = '#CCFFCC';
  }
  var cell_id_arr = cell_id_str.split('|');
  for(var i=0; i<cell_id_arr.length; i++){
    var tmp_id_color_arr = cell_id_arr[i].split('!');
    var checkbox_id = tmp_id_color_arr[0]
    var cellBgcolor = tmp_id_color_arr[1];
    var checkbox_obj = document.getElementById(checkbox_id);
    var td_obj_id = 't_' + checkbox_id;
    var td_obj = document.getElementById(td_obj_id);
    if(prey_checkbox_obj.checked){
      checkbox_obj.checked = true;
      td_obj.style.backgroundColor = '#c0c0c0';
    }else{
      checkbox_obj.checked =false;
      td_obj.style.backgroundColor = cellBgcolor;
    }
  }
  return;
}

function comfirm_showTip(event,block_div,lable){
  if(is_selected_prey_cells_changed()){
    alert("Please click go button first then click " + lable + ".");
  }else{
    showTip(event,block_div);
  }  
}
function pop_sent_win(){
  theFileName = '<?php echo $html_file_name?>';
  file = "./pop_send_report_to_public.php?html_report_url=" + theFileName;
  nWin = window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=670,height=370');
  nWin.moveTo(4,0);
}
      
</script>
<?php 
$html_str = "<META content='MSHTML 6.00.2900.3199' name=GENERATOR>\r\n</head>\r\n<basefont face='arial'>\r\n";
fwrite($html_handle, $html_str);
echo $html_str;
$html_str = "<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 topMargin=5 rightMargin=5 marginheight=5 marginwidth=5>\r\n<center>\r\n";
fwrite($html_handle, $html_str);
echo $html_str;
?>
<FORM id="form_SAINT_comparison" ACTION="<?php echo $PHP_SELF;?>" NAME="form_SAINT_comparison" METHOD="POST">
<INPUT TYPE="hidden" NAME="saint_ID" VALUE="<?php echo $saint_ID;?>">
<INPUT TYPE="hidden" NAME="filterStyleDisplay" VALUE="<?php echo $filterStyleDisplay;?>">
<INPUT TYPE="hidden" NAME="subfilterStyleDisplay" VALUE="<?php echo $subfilterStyleDisplay;?>">
<INPUT TYPE="hidden" NAME="color_mode" VALUE="<?php echo $frm_color_mode;?>">
<INPUT TYPE="hidden" NAME="filtrColorIniFlag" VALUE="<?php echo $filtrColorIniFlag;?>">
<INPUT TYPE="hidden" NAME="filterd_prey_cells" VALUE="<?php echo $filterd_prey_cells;?>">
<INPUT TYPE="hidden" NAME="filterd_prey_lines" VALUE="<?php echo $filterd_prey_lines;?>">
<INPUT TYPE="hidden" NAME="applyFilters" VALUE="<?php echo $applyFilters;?>">
<INPUT TYPE="hidden" NAME="frm_apply_filter" VALUE="<?php echo $applyFilters;?>">
<INPUT TYPE="hidden" NAME="exportType" VALUE="">
<INPUT TYPE="hidden" NAME="infileName" VALUE="">
<INPUT TYPE="hidden" NAME="allBaitgeneID_str" VALUE="<?php echo (isset($allBaitgeneID_str))?$allBaitgeneID_str:''?>">
<INPUT TYPE="hidden" NAME="currentType" VALUE="SAINT">
<INPUT TYPE="hidden" NAME="SearchEngine" VALUE="">
<INPUT TYPE="hidden" NAME="hitType" VALUE="<?php echo $SearchEngine?>">
<INPUT TYPE="hidden" NAME="is_uploaded" VALUE="<?php echo $is_uploaded?>">
<!--INPUT TYPE="hidden" NAME="tmp_result_file" VALUE="<?php echo $tmp_result_file?>"-->
<INPUT TYPE="hidden" NAME="line_info_str" VALUE="<?php echo $line_info_str?>">
<INPUT TYPE="hidden" NAME="field_lable_key_str" VALUE="<?php echo $field_lable_key_str?>">
<INPUT TYPE="hidden" NAME="field_lable_val_str" VALUE="<?php echo $field_lable_val_str?>">
<INPUT TYPE="hidden" NAME="theaction" VALUE="">
<INPUT TYPE="hidden" NAME="SID" VALUE="">
<INPUT TYPE="hidden" NAME="ID_string" VALUE="">
<INPUT TYPE="hidden" NAME="item_type" VALUE="<?php echo $item_type?>">
<INPUT TYPE="hidden" NAME="ProjectID" VALUE="<?php echo $AccessProjectID?>">
<INPUT TYPE="hidden" NAME="saint_v" VALUE="<?php echo $saint_v?>">

<?php 
$html_str = "<b><font face='Arial' size='+3'>Saint Comparison</font></b>\r\n";
fwrite($html_handle, $html_str."<br><br>");
echo $html_str;

$html_str = "<table align='center' bgcolor='' cellspacing='0' cellpadding='0' border='0' width=700>\r\n";
fwrite($html_handle, $html_str);
echo $html_str;

$html_str = "<tr>\r\n<td colspan='2' nowrap>\r\n<table align='center' bgcolor='' cellspacing='1' cellpadding='3' border='0' width=700>
      <tr bgcolor='#b7c1c8' height=28>
        <td width='15%' align='right' nowrap>
          <font size='2' face='Arial'><b>Color code</b></font>&nbsp;&nbsp;
        </td>
        <td >&nbsp;&nbsp;
          <font size='2'>
          Hit property color code <input type=radio name='frm_color_mode' value='property' ".(($frm_color_mode == 'property')?'checked':'')." onClick='change_color_code(this);'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          Shared hits color code <input type=radio name='frm_color_mode' value='shared' ". (($frm_color_mode == 'shared')?'checked':'')." onClick='change_color_code(this);'>
          </font>
        </td>
      </tr>";
//fwrite($html_handle, $html_str);
echo $html_str;

$item_list = '';
$select_item = '';
foreach($bait_lable_arr as $itemID => $lable){
  if($sort_by_item_name==$itemID) $select_item = $lable;
  if($item_list) $item_list .= "<br>";
  $item_list .= $lable;
}

$order_by_lable = $field_lable_arr[$orderby];
$sort_by_item_name_lable = $sort_by_item_name;

$html_str = "<tr bgcolor='#b7c1c8'>
              <td nowrap align='left'>
              <DIV style='display:block;
                          float:left;
                          width:240px;
                          height:25px; 
                          position: relative;
                          font-size: 15px;
                          padding:5px 0px 0px 10px; 
	                        border:#708090 0px solid;'>
                          <b>Sort by</b>: &nbsp;&nbsp;&nbsp;&nbsp;$order_by_lable&nbsp;&nbsp;&nbsp;$typeLable ID:
              </DIV>
              <DIV id='one' style='display:block;
                            float:left;
                            width:200px;
                            height:25px; 
                            position: relative;
                            font-size: 15px;
                            padding:5px 0px 0px 5px; 
  	                        border:#708090 0px solid;'>
                            <a href=\"javascript: href_show_hand();\" onclick=\"toggle_item('one')\">
                            $select_item
                            </a>
              </DIV>
              <DIV id='more' style='display:none;
                            float:left;
                            width:200px;
                            position: relative;
                            font-size: 15px;
                            padding:5px 0px 0px 5px; 
  	                        border:#708090 0px solid;'>
                            <a href=\"javascript: href_show_hand();\" onclick=\"toggle_item('more')\">
                            $item_list
                            </a>
              </DIV>
            </td>
          </tr>
<script language='javascript'>           
function toggle_item(div_id){
  var one_obj = document.getElementById('one');
  var more_obj = document.getElementById('more');
  if(div_id == 'one'){
    one_obj.style.display = 'none';
    more_obj.style.display = 'block';
  }else{
    one_obj.style.display = 'block';
    more_obj.style.display = 'none';
  }
} 
</script>         
";        
          
                      
fwrite($html_handle, $html_str);
?>   
        
      <tr bgcolor="#b7c1c8">
        <td nowrap align="right">
    			<font size="2" face="Arial"><b>Sort by</b></font>&nbsp;&nbsp;
           </td>
           <td nowrap >&nbsp;&nbsp;
           <select name="orderby" size=1>
            <option value=''>&nbsp; &nbsp; &nbsp;
          <?php foreach($col_name_arr as $col_name_key){
              if(!in_array($col_name_key, $sort_list_arr)) continue;
          ?>
            <option value='<?php echo $col_name_key?>' <?php echo ($orderby==$col_name_key)?'selected':''?>><?php echo $field_lable_arr[$col_name_key]?>
          <?php }?>
          </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <font size="2" face="Arial"><?php echo $typeLable?> Name&nbsp;
          <select name="sort_by_item_name">
            <?php foreach($bait_gene_name_arr as $name){?>
              <option value='<?php echo $name?>' <?php echo ($sort_by_item_name==$name)?'selected':''?>><?php echo $name?>
            <?php }?>
          </select>&nbsp;&nbsp;&nbsp;&nbsp;
          Descending<input type=radio name=asc_desc value='DESC' <?php echo (isset($asc_desc) && $asc_desc=='DESC')?'checked':''?>>&nbsp;&nbsp;&nbsp;
          Ascending<input type=radio name=asc_desc value='ASC' <?php echo (isset($asc_desc) && $asc_desc=='ASC')?'checked':''?>>&nbsp;
        </td>
      </tr>      
<?php       
$html_str = "    </table> 
    </td>
	</tr>";
fwrite($html_handle, $html_str);
echo $html_str; 

?>  
  
  <tr>
		<td colspan='2' nowrap>
      <a id="filter_area_a" href="javascript: href_show_hand();" onclick="showhide('filter_area')"><font size="2" face="Arial">[&nbsp;<?php echo ($filterStyleDisplay=='none')?'Click to apply filters':'Click to remove filters'?>&nbsp;]</font></a></td>
	</tr>
  <tr bgcolor="#b7c1c8">
    <td colspan='2'>
    <DIV ID="sub_filter_area" style="display:<?php echo $subfilterStyleDisplay;?>">
    <table align="center" bgcolor='' cellspacing="3" cellpadding="3" border="0" width=100%>
    <tr bgcolor="#b7c1c8">
    <font size="2" face="Arial">
    <td width=25% align="right" nowrap>&nbsp;&nbsp;<font size="2" face="Arial"><b>Prey found in</b> all <?php echo $typeLable;?>s</font>&nbsp;&nbsp;&nbsp;</td>
    <td width=8% align=center bgcolor='<?php echo $red;?>'>&nbsp;&nbsp;</td>
    <td width=25% align="right" nowrap>&nbsp; &nbsp;<font size="2" face="Arial"> more than one <?php echo $typeLable;?>s</font>&nbsp;&nbsp;&nbsp;</td>
    <td width=8% align=center bgcolor='<?php echo $green;?>'>&nbsp;&nbsp;</td>
    <td width=15% align=right nowrap>&nbsp; &nbsp;<font size="2" face="Arial"> one <?php echo $typeLable;?></font>&nbsp;&nbsp;</td>
    <td width=8% align=center bgcolor='<?php echo $blue;?>'></td>
    <td >&nbsp;&nbsp;</td>
    </tr>
    </table>
    </DIV>
    </td>
  </tr> 
  <tr>
    <td colspan='2'>    
 <?php   
include("filter_interface.php");

/*echo "<pre>";
print_r($sorted_table_array);
echo "</pre>";*/

$total_baits = count($sorted_table_array);
$prey_index_arr_sorted = array();

foreach($sorted_table_array as $key => $val){
  if(isset($itemName_geneID_arr[$key])){
    $key_geneID = $itemName_geneID_arr[$key];
  }else{
    $key_geneID = 0;
  }
  
  $prey_arr = $val;
  foreach($prey_arr as $prey_val){
    if(!array_key_exists($prey_val['PREY'], $prey_index_arr_sorted) && $sorted_table_array[$key][$prey_val['PREY']]['HIGH_CONFIDENCE']){
      $prey_index_arr_sorted[$prey_val['PREY']] = $prey_index_arr[$prey_val['PREY']];
      $prey_propty_arr[$prey_val['PREY']]['sharedFreqStr'] = round(($prey_propty_arr[$prey_val['PREY']]['count'] - $prey_propty_arr[$prey_val['PREY']]['filter']) * 100 / $total_baits,2);
    }
    $sorted_table_array[$key][$prey_val['PREY']]['SHARED_FREQUENCY'] = round($prey_propty_arr[$prey_val['PREY']]['count'] * 100 / $total_baits,2);
    if($key_geneID && isset($grid_bait_hits_arr[$key_geneID][$prey_propty_arr[$prey_val['PREY']]['gene_id']])){
      $sorted_table_array[$key][$prey_val['PREY']]['BIOGRID'] = implode("|", $grid_bait_hits_arr[$key_geneID][$prey_propty_arr[$prey_val['PREY']]['gene_id']]);
    }
  }
}
 ?>  
		</td>&nbsp;<br>&nbsp;
  </tr>
  <tr>
		<td colspan=3>
		<table align="" bgcolor='' cellspacing="0" cellpadding="0" border="0" width=100%>
		<tr>
  <?php if($frm_color_mode != 'shared'){
		  print_color_bar($colorArrSet);
    }else{
      print_shared_color_bar();
    }
  ?>  
	  <!--td align="right"><input type=button value='Update Frequency' onClick='javascript: updateFrequency();'>&nbsp;&nbsp;&nbsp;</td-->	
	  <td align="right"><input type=button name=sort_submit value="     GO     " onclick="sort_page();">&nbsp;&nbsp;&nbsp;</td>		
  </tr>
	</table>
	</td>
	</tr>
<?php 
$html_str = "</table>";
fwrite($html_handle, $html_str);
echo $html_str;

  if(!$sorted_table_array) exit;
  if(!isset($bio_checked_str)) $bio_checked_str = '';
  
  $qurryStr = "infileName=$reportFileName&exportType=graph&orderby=$orderby&power=$power&biggestPowedSore=$biggestPowedSore&powerColorIndex=$powerColorIndex&hitType=$hitType_for_g&bio_checked_str=$bio_checked_str&level1_matched_file=$tmp_file";
  
  if(!$is_uploaded){
    $SAINT_PSI_MI_URL = "./export_hits_public.php?infile=$full_map_file&theaction=generate_map_file&public=IntAct&SearchEngine=$SearchEngine&type=$item_type&saint_ID=$saint_ID";
  }
  $SAINT_table_file_URL = "./SAINT_comparison_results_table.php?full_table_file=$full_table_file&theaction=export_table_file";
?>
<INPUT TYPE="hidden" NAME="cyto_qurryStr" VALUE="<?php echo $qurryStr?>">
<table align="center" bgcolor="" cellspacing="0" cellpadding="3" border="0" width=780>
  <tr>
    <td align=right><font size=2>
    <a href="javascript: href_show_hand();" onclick="comfirm_showTip(event,'cyto_confirm_div','[Cytoscape]')">[<img src=./images/icon_cytoscape.gif border=0>Cytoscape]</a> &nbsp;
    <DIV ID='cyto_confirm_div' STYLE="position: absolute; 
                          display: none;
                          border: black solid 1px;
                          width: 200px";>
      <table align="center" cellspacing="0" cellpadding="1" border="0" width=100% bgcolor="#e6e6cc">
        <tr bgcolor="#c1c184" height=25><td valign="bottem">&nbsp;&nbsp;&nbsp;<font color="white" face="helvetica,arial,futura" size="2"><b>Select node lable:</b></font></td></tr>
        <tr bgcolor="#e6e6cc"><td>&nbsp;&nbsp;&nbsp;<input type=radio NAME="node_lable" VALUE="short" checked>&nbsp;<font color="black" face="helvetica,arial,futura" size="2">Gene name</font></td></tr>
        <tr bgcolor="#e6e6cc"><td>&nbsp;&nbsp;&nbsp;<input type=radio NAME="node_lable" VALUE="long">&nbsp;<font color="black" face="helvetica,arial,futura" size="2">Gene name and Gene ID</font></td></tr>
        <tr bgcolor="#e6e6cc"><td align="center" height=35><input type=button name='cyto_confirm_div' VALUE=" Confirm " onclick="javascript: confirm_cyto_div();">&nbsp;&nbsp;
        <input type=button name='hide_div' VALUE=" Cancel " onclick="javascript: hideTip('cyto_confirm_div');">
        </td>
        </tr>
      </table>   
    </DIV>
    [<a href="javascript: export_table_file();">Export (table)</a>] &nbsp;
    <INPUT TYPE="hidden" NAME="SAINT_table_file_URL" VALUE="<?php echo $SAINT_table_file_URL?>">
    <INPUT TYPE="hidden" NAME="full_table_file" VALUE="">     
    [<a href="javascript: href_show_hand();" onclick="comfirm_showTip(event,'matrix_confirm_div','[Export (matrix)]')">Export (matrix)</a>] &nbsp;
<?php if(!$is_uploaded){?> 
    [<a href="javascript: export_psi_mi_file();">Export to PSI-MI</a>] &nbsp;
    <INPUT TYPE="hidden" NAME="SAINT_PSI_MI_URL" VALUE="<?php echo $SAINT_PSI_MI_URL?>">
<?php }?>    
    <DIV ID='matrix_confirm_div' STYLE="position: absolute; 
                          display: none;
                          border: black solid 1px;
                          width: 200px";>
      <table align="center" cellspacing="0" cellpadding="1" border="0" width=100% bgcolor="#e6e6cc">
        <tr bgcolor="#c1c184" height=25><td valign="bottem">&nbsp;&nbsp;&nbsp;<font color="white" face="helvetica,arial,futura" size="2"><b>Select hit property:</b></font></td></tr>
        <tr><td>&nbsp;&nbsp;&nbsp;<input type=radio NAME="report_style" VALUE="multiple" checked>&nbsp;<font color="black" face="helvetica,arial,futura" size="2">All hit properties</font></td></tr>
  <?php 
    if(in_array($orderby, $sort_list_arr)){?>    
        <tr><td>&nbsp;&nbsp;&nbsp;<input type=radio NAME="report_style" VALUE="<?php echo $orderby?>">&nbsp;<font color="black" face="helvetica,arial,futura" size="2">Only <?php echo $field_lable_arr[$orderby]?></font></td></tr>
  <?php }?>    
        <tr height=35><td align="center"><input type=button name='matrix_confirm' VALUE=" Confirm " onclick="javascript: confirmed_div();">&nbsp;&nbsp;
        <input type=button name='hide_div' VALUE=" Cancel " onclick="javascript: hideTip('matrix_confirm_div');">
        </td></tr>
      </table>   
    </DIV>
    [<a href="javascript: pop_sent_win()";>Send by email</a>] &nbsp;
  <?php if(($USER->Type == 'Admin' || $export_to_Prohits_permission) && defined('PROHITS_WEB') && !$is_uploaded){?>    
    [<a href="javascript: href_show_hand();" onclick="comfirm_showTip(event,'prohits_web_confirm_div','[Export (Prohits web)]')">Export (Prohits web)</a>] &nbsp;
    <DIV ID='prohits_web_confirm_div' STYLE="position: absolute; 
                          display: none;
                          border: black solid 1px;
                          width: 250px";>
      <table align="center" cellspacing="0" cellpadding="1" border="0" width=100% bgcolor="#e6e6cc">
        <tr bgcolor="#c1c184" height=25><td valign="bottem">&nbsp;&nbsp;&nbsp;<font color="white" face="helvetica,arial,futura" size="2"><b>Export to Prohits web</b></font></td></tr>
        <tr><td>&nbsp;&nbsp;&nbsp;<font color="black" face="helvetica,arial,futura" size="2">Add Vector Info. from OpenFreezer?</font><input type=checkbox NAME="openF" VALUE='y'>&nbsp;</td></tr>        
        <tr><td>&nbsp;&nbsp;&nbsp;<font color="black" face="helvetica,arial,futura" size="2">Username</font>:&nbsp;&nbsp;<input type=text NAME="Username" VALUE=""></td></tr>
        <tr><td>&nbsp;&nbsp;&nbsp;<font color="black" face="helvetica,arial,futura" size="2">Password</font>:&nbsp;&nbsp;&nbsp;<input type=password NAME="Password" VALUE=""></td></tr>  
     
        <tr height=35><td align="center"><input type=button name='matrix_confirm' VALUE=" Confirm " onclick="javascript: to_prohits_web_confirm_div();">&nbsp;&nbsp;
        <input type=button name='hide_div' VALUE=" Cancel " onclick="javascript: hideTip('prohits_web_confirm_div');">
        </td></tr>
      </table>   
    </DIV>
  <?php }?>
    [<a href="javascript: href_show_hand();" onclick="download_raw()">Export (raw files)</a>] &nbsp;
 
    </td>
  </tr>
</table>
<?php 

$html_str = "
<DIV ID='hit_detail_div' STYLE='position: absolute; display: none;border: black solid 1px;width: 200px';>
  <table align='center' cellspacing='0' cellpadding='1' border='0' width=100% bgcolor='#e6e6cc'>
    <tr bgcolor='#c1c184' height=20>
      <td valign='bottem'>
        <font color='white' face='helvetica,arial,futura' size='2'><b><div ID='title_div'>Prey details</div></b></font>
      </td>
    </tr>
    <tr><td id='hit_detail_td'></td></tr>
  </table>   
</DIV>";
fwrite($html_handle, $html_str."<br>&nbsp;&nbsp;");
echo $html_str; 

$html_str = "\r\n<table align='center' bgcolor='' cellspacing='0' cellpadding='0' border='0' width=750>
  <tr>\t\n";
fwrite($html_handle, $html_str);
echo $html_str;

  foreach($sorted_table_array as $key => $val){
    $itemLable = $bait_lable_arr[$key];
$html_str = "<td colspan='' class='s20' align='center' bgcolor='$lableBgc' rowspan='2'>";
fwrite($html_handle, $html_str);
echo $html_str;

$html_str = "<img src='./comparison_results_create_image.php?strMaxL=$itemlableMaxL&displayedStr=$itemLable&lableBgc=$lableBgc&Total_Spec_index=$Total_Spec_index&fontSize=2' border=0>";
echo $html_str;

$html_str = "<font color='white'>".$itemLable."</font>";
fwrite($html_handle, $html_str);

$html_str = "</td>\r\n";
fwrite($html_handle, $html_str);
echo $html_str;
  }
$html_str = "<td bgcolor='#aeaeae' colspan=4 rowspan=1 align=center><font size=3><b>Prey</b></font></td>
 </tr>
 <tr>
    <td class=s19  align=center>Gene Name</td>
    <td class=s19  align=center>Protein ID</td>
 </tr>";  
fwrite($html_handle, $html_str."\r\n");

if(stristr($SearchEngine, 'GeneLevel')){
  $PREY_lable = 'Gene ID';
}else{
  $PREY_lable = 'Protein ID';
}

$html_str = "<td bgcolor='#aeaeae' colspan=4 rowspan=1 align=center><font size=3><b>Prey</b></font></td>
 </tr>
 <tr>
    <td class=s19  align=center>Gene Name</td>
    <td class=s19  align=center>$PREY_lable</td>
    <td class=s19  align=center>Remove</td>
 </tr>";
echo $html_str;     

  $TB_CELL_COLOR = '#ff7575';
  $totalitems = count($bait_gene_name_arr);
  $colorMode = "colorMode;;".$frm_color_mode."\r\n";
  fwrite($reportFile_handle, $colorMode);
  fwrite($reportFile_handle, "totalitems;;$totalitems\r\n");
  $itemLableInfo = '';

  foreach($sorted_table_array as $key => $val){
    if($itemLableInfo) $itemLableInfo .= ',';
    $tmp_gene_id = (isset($itemName_geneID_arr[$key]))?$itemName_geneID_arr[$key]:'';
    $itemLableInfo .= $key." ".$tmp_gene_id;
  }
  
  fwrite($reportFile_handle, "itemLableInfo;;$itemLableInfo\r\n");
  if(!$is_uploaded){  
    fwrite($reportFile_handle, "baitGeneIDstr;;$allBaitgeneID_str\r\n");
    fwrite($reportFile_handle, "du_NameGeneID;;$du_Name_in_aGeneID_str\r\n");
  }  
  if($apply_bioGrid){
    fwrite($reportFile_handle, "bioGrid_overlap;;yes\r\n");
  }else{
    fwrite($reportFile_handle, "bioGrid_overlap;;no\r\n");
  }  
  fwrite($reportFile_handle, "groupInfo;;\r\n");
  fwrite($reportFile_handle, "itemlableMaxL;;$itemlableMaxL\r\n"); 
  
  $i = 0;
  
  $preysGeneIDarr = array();
  $filtered_preyID_arr = array();
  $sharedColorSet = array();
  if($frm_color_mode == 'shared'){
    create_colorArr_set($sharedColorSet,'green');
  }
      
  foreach($prey_index_arr_sorted as $prey_index => $prey_val){
    if(!$prey_val) continue;
    if($prey_propty_arr[$prey_index]['count'] == $prey_propty_arr[$prey_index]['filter']) continue;    
    $tr_id = '_'.$prey_index;


$html_str = "<tr id='$tr_id' bgcolor='#ececec' onmousedown=\"highlight_tr('<?php echo $tr_id;?>')\">";
$line_check_box_checked = '';
if(in_array($prey_index, $filterd_prey_lines_arr)){
  $line_check_box_checked = 'checked';
}else{
  fwrite($html_handle, $html_str);
}
echo $html_str;
    $freqStr = "<br>Project Frequency: ".$prey_propty_arr[$prey_index]['frequency']."%";
    $sharedFreqStr = "Shared Prey Frequency: " . $prey_propty_arr[$prey_index]['sharedFreqStr']."%";
    $prey_ProteinID = $prey_index;
    $prey_GeneID = $prey_propty_arr[$prey_index]['gene_id'];
    $prey_GeneName = $prey_propty_arr[$prey_index]['gene_name'];
    
    $lineLable = $prey_GeneID.','.$prey_GeneName.',,';
    
    if($frm_color_mode == 'shared'){
      $cellBgcolor = classify_filter($prey_index);
      if(!$cellBgcolor && !in_array($prey_index, $filtered_preyID_arr)){
        array_push($filtered_preyID_arr, $prey_index);
      }
      if(!$cellBgcolor) continue;
    }
        
    $lineInfo = '';;
    $j_counter = 0;
    $empty_counter = 0;
    $cell_id_str = '';
    $real_cell_counter = 0;
    
		foreach($sorted_table_array as $bait_index => $tmp_prey){
      $bioGrid_typeStr = '';
      $j_counter++;
      if(isset($sorted_table_array[$bait_index][$prey_index]) && $sorted_table_array[$bait_index][$prey_index]['HIGH_CONFIDENCE']){
        $upStr = '';
        foreach($display_list_arr as $display_list_index){
          $upStr .= $field_lable_arr[$display_list_index].": ".$sorted_table_array[$bait_index][$prey_index][$display_list_index].(($display_list_index=='PROJECT_FREQUENCY')?'%':'')."<br>";
        }
        $upStr .= $sharedFreqStr;  
        $hitSore = $sorted_table_array[$bait_index][$prey_index][$orderby];
        $scoreLable = $field_lable_arr[$orderby];
        if($frm_color_mode != 'shared'){
          $hitSoreForColor = $hitSore;
          $cellBgcolor = color_num($hitSoreForColor, $colorIndex);
          $cellBgcolor_backup = $cellBgcolor;
          ($colorIndex >= 7)?$numOfClass='s13':$numOfClass='s14';
          $font_color = 'white';
        }else{  
          $numOfClass = 's14';
          $font_color = 'white';
        }
        $check_box_id = $bait_index."_".$prey_index;         
        $detail_div_id = "d_".$check_box_id;
        $td_id = 't_'.$bait_index."_".$prey_index;        
        
        if($cell_id_str) $cell_id_str .= "|";
        $cell_id_str .= $check_box_id."!".$cellBgcolor;        
        
        $check_box_checked = '';
        $filted_cell = 0;
        if(in_array($check_box_id, $filterd_prey_arr) || in_array($prey_index, $filterd_prey_lines_arr)){
          $check_box_checked = 'checked';
          $cellBgcolor = '#c0c0c0';
          $filted_cell = 1;
        }

$html_str = "<td id='$td_id' class='$numOfClass' align='left' bgcolor='$cellBgcolor'>";
echo $html_str;

if(!$line_check_box_checked){
  if($check_box_checked) $cellBgcolor = '#ececec';
  $html_str = "<td id='$td_id' class='$numOfClass' align='center' bgcolor='$cellBgcolor'>";
  fwrite($html_handle, $html_str);
}
     ?> 
        <a  title='check the box then click GO button to remove this cell'>
        <input id='<?php echo $check_box_id?>' type=checkbox name='prey_cells' value='<?php echo $check_box_id?>' <?php echo $check_box_checked?> onClick="change_td_bg_color('<?php echo $check_box_id?>','<?php echo $cellBgcolor_backup;?>');">
        </a>
     <?php 
$html_str = "<a style='text-decoration:none' href=\"javascript: href_show_hand();\" onmouseover=\"show_hit_detail(event,'$detail_div_id','Prey details');\" onmouseout=\"hideTip('hit_detail_div');\">
        <DIV ID='$detail_div_id' STYLE='display: none';>$upStr</DIV>
        <font color='$font_color'>".(($hitSore=trim($hitSore))?$hitSore:"&nbsp;")."</font>
        </a>";
if(!$line_check_box_checked && !$check_box_checked){        
  fwrite($html_handle, $html_str);
}elseif($check_box_checked){
  fwrite($html_handle, "&nbsp;&nbsp;");
}
echo $html_str;

        if(!$is_uploaded){
          print_bioGrid_icon($itemName_geneID_arr[$bait_index],$prey_GeneID,$prey_GeneName,$bait_index,'1');
          if($bioGrid_typeStr) $bioGrid_typeStr = "[".$bioGrid_typeStr."]";
        }
        
$html_str = "</td>"; 
if(!$line_check_box_checked && !$check_box_checked){        
  fwrite($html_handle, $bioGrid_typeStr.$html_str);
}elseif($check_box_checked){
  fwrite($html_handle, "&nbsp;&nbsp;");
}
echo $html_str;        

        $total_Spec = $sorted_table_array[$bait_index][$prey_index]['SPECSUM'];
        if($filted_cell){
          if($bioGrid_typeStr) $bioGrid_typeStr = "[".$bioGrid_typeStr."]";
          $lineInfo .= $bioGrid_typeStr;
        }else{
          $tmp_line_info_str = '';
          foreach($line_info_arr as $line_info_val){
            
            if($tmp_line_info_str) $tmp_line_info_str .= '-';
            $tmp_line_info_str .= $sorted_table_array[$bait_index][$prey_index][$line_info_val];
          }
          $lineInfo .= str_replace(":", "+++", $prey_index).':'.$total_Spec.'('.$tmp_line_info_str.')'.$bioGrid_typeStr;
          $real_cell_counter++;
        }
                
      }else{
      
$html_str = "<td align=center class=s15>&nbsp;";
if(!$line_check_box_checked){        
  fwrite($html_handle, $html_str);
}
echo $html_str;


        if(!$is_uploaded){
            print_bioGrid_icon($itemName_geneID_arr[$bait_index],$prey_GeneID,$prey_GeneName,$bait_index,'0');
            if($bioGrid_typeStr) $bioGrid_typeStr = "[".$bioGrid_typeStr."]";
        }
$html_str = "</td>"; 
if(!$line_check_box_checked){        
  fwrite($html_handle, $bioGrid_typeStr.$html_str);
}
echo $html_str;         
        $lineInfo .= $bioGrid_typeStr;
      }
      if($j_counter != $totalitems) $lineInfo .= ',';
    }    
    
    $lineLable = $prey_GeneID.','.$prey_GeneName.',';
    if($frm_color_mode == 'shared'){
      $lineInfo = $lineLable.','.$lineInfo.'@'.$cellBgcolor."\r\n";
    }else{
      $lineInfo = $lineLable.','.$lineInfo."\r\n";
    } 
     
    if(!in_array($prey_index, $filterd_prey_lines_arr) && $real_cell_counter){
      fwrite($reportFile_handle, $lineInfo);
    }
      
    if(!($GeneIdURL = get_URL_str('',$prey_GeneID,'',$prey_GeneName,'comparison'))){
      $GeneIdURL = "&nbsp;";
      if($prey_GeneID) $GeneIdURL = $prey_GeneName;
    }
    
    if(stristr($SearchEngine, 'GeneLevel')){
  		$ProteinIdURL = "<a href=".$URLS['ENTREZ_GENE'].$prey_GeneID." target=_blank>$prey_GeneID</a>";
  	}else{
        if(!($ProteinIdURL = get_URL_str($prey_ProteinID,'','','','comparison'))){
          $ProteinIdURL = "&nbsp;";
          if($prey_ProteinID) $ProteinIdURL = $prey_ProteinID;
        } 
  	}    
    
    /*if(!($ProteinIdURL = get_URL_str($prey_ProteinID,'','','','comparison'))){
      $ProteinIdURL = "&nbsp;";
      if($prey_ProteinID) $ProteinIdURL = $prey_ProteinID;
    }*/    
    
$html_str = "<td id='gene$tr_id' class=s16 align=center>".str_replace("<br>", "&nbsp;&nbsp;&nbsp;", $GeneIdURL)."</td>
      <td id='protein$tr_id' align=center class=s17 bgcolor=".(($frm_color_mode == 'shared')?$cellBgcolor:'#d6d6d6')." align=center>
			$ProteinIdURL
			</td>"; 
if(!$line_check_box_checked){        
  fwrite($html_handle, $html_str);
}  
echo $html_str;
    ?>    
      <td class=s16  align=center>
        <input id='<?php echo $prey_index?>' type=checkbox name='prey_lines' value='<?php echo $prey_index?>' <?php echo $line_check_box_checked?> onClick="change_tr_bg_color('<?php echo $cell_id_str;?>','<?php echo $prey_index;?>');">
      </td>
<?php 
$html_str = "  </tr>";
if(!$line_check_box_checked){        
  fwrite($html_handle, $html_str);
}  
echo $html_str;

    array_push($preysGeneIDarr, $prey_GeneID);
  }
      
  if(isset($bio_checked_arr) && $bio_checked_arr && $applyFilters && !$no_grid_data){
    $no_matched_gene_array = array_diff($grid_hits_gene_arr, $preysGeneIDarr);
    if($no_matched_gene_array){
      $no_matched_gene_IDstr = implode(",", $no_matched_gene_array);    
      $SQL = "SELECT `EntrezGeneID`,
              `LocusTag`,
              `GeneName` 
              FROM `Protein_Class` 
              WHERE `EntrezGeneID` IN ($no_matched_gene_IDstr) 
              ORDER BY `GeneName`";
      $tmp_Protein_Class_arr = $proteinDB->fetchAll($SQL);
      $noMatchedHitGeneArr = array();
      if($tmp_Protein_Class_arr){
        $lineInfo = "bioGrid_only:\r\n";
        fwrite($reportFile_handle, $lineInfo);
      }
      foreach($tmp_Protein_Class_arr as $no_matched_gene_info){
        $counter = 0;
        $lineInfo = '';
        $noMatchedHitGeneArr[$no_matched_gene_info['GeneName']] = $no_matched_gene_info['EntrezGeneID'];
        $i++;
        
$html_str = "<tr id='$i' bgcolor='#ececec' onmousedown=\"highlightTR(this, 'click', '#CCFFCC', '#ececec', '<?php echo $frm_color_mode?>')\";>";
echo $html_str;
fwrite($html_handle, $html_str);

        $j_counter = 0;
        $tmpIndex2 = '';        
        foreach($sorted_table_array as $bait_index => $tmp_prey){
          if($counter){
  				  $lineInfo .= ',';
  				}
          $counter++;
$html_str = "<td align=center class=s15>&nbsp;";
echo $html_str;
fwrite($html_handle, $html_str);          
          print_bioGrid_icon_noMatch($itemName_geneID_arr[$bait_index],$no_matched_gene_info,$bait_index,$j_counter,$tmpIndex2);
          if($bioGrid_typeStr) $bioGrid_typeStr = "[".$bioGrid_typeStr."]";

$html_str = "&nbsp;</td>";
echo $html_str;
fwrite($html_handle, $bioGrid_typeStr.$html_str);
          $lineInfo .= $bioGrid_typeStr;
        }
        $tempHitGeneID = $no_matched_gene_info['EntrezGeneID'];
        $tmpGeneName = $no_matched_gene_info['GeneName'];
        $tmpLocusTag = $no_matched_gene_info['LocusTag'];
        if(!($GeneIdURL = get_URL_str('',$tempHitGeneID,'',$tmpGeneName,'comparison'))){
          $GeneIdURL = "&nbsp;";
          if($tempHitGeneID) $GeneIdURL = $tempHitGeneID;
        }               
        if(!($orfURL = get_URL_str('','',$tmpLocusTag,'','comparison'))) $orfURL = "&nbsp;";
        
$html_str = "<td id='gene$i' class=s16 align=center>".str_replace("<br>", "&nbsp;&nbsp;&nbsp;", $GeneIdURL)."</td>";
echo $html_str;
fwrite($html_handle, $html_str);
        
$html_str = "<td id='protein$i' align=center class=s16 bgcolor=#d6d6d6>&nbsp;</td>";
echo $html_str;
fwrite($html_handle, $html_str);          
      ?>
          <td id='' align=center class=s16 bgcolor=#d6d6d6>&nbsp;</td>
      <?php 
$html_str = "</tr> ";
echo $html_str;
fwrite($html_handle, $html_str); 
    
        $lineLable = $tempHitGeneID.','.$tmpGeneName.','.(($tmpLocusTag=='-')?'':$tmpLocusTag);
        if($frm_color_mode == 'shared'){
          $lineInfo = $lineLable.','.$lineInfo.'@'.$cellBgcolor."\r\n";
        }else{
          $lineInfo = $lineLable.','.$lineInfo."\r\n";
        }
        if($theaction != "popWindow") fwrite($reportFile_handle, $lineInfo);
      }
    }
  }
  fwrite($matchGred_handle, "bait_info\r\n");
  if(isset($item_geneName_id_arr)){
    foreach($item_geneName_id_arr as $key => $value){
      fwrite($matchGred_handle, $key.",".$value."\r\n");
    }
  }
$html_str = "  <tr height='1'>
   <td bgcolor='#4d4d4d' height='1' colspan='1000'><img src='images/pixel.gif' width='1' height='1' border='0'></td>
 </tr>";
echo $html_str;
fwrite($html_handle, $html_str);
$html_str = "<tr>
   <td align='center' colspan='1000'>
    <font face='Arial' size=1 color=black>Copyright &copy; 2010 <a href='http://gingraslab.lunenfeld.ca' class='button' target='blank'><font size='1'>Gingras</font></a> and <a href='http://tyerslab.bio.ed.ac.uk/lisa/index.php' class='button' target='blank'><font size=1>Tyers</font></a> labs, Samuel Lunenfeld Research Institute, Mount Sinai Hospital.</font>
   </td>
 </tr>";
echo $html_str;
fwrite($html_handle, $html_str);

$html_str = "</table>
  </FORM>
</BODY>
</HTML>";
echo $html_str;
fwrite($html_handle, $html_str);
?>
<script language='javascript'>
document.getElementById('process').style.display = 'none';
</script>
<?php if(defined('PROHITS_WEB')){?>
<form id='export_prohits_web_form' name='export_prohits_web_form' action='<?php echo PROHITS_WEB?>login_data.php' method='post'>
  <input type='hidden' name='export_file' value='<?php echo $export_file?>'>
  <input type='hidden' name='saint_ID' value='<?php echo $saint_ID?>'>
  <input type='hidden' name='theaction' value='file_from_prohits'>
</form>
<?php 
}
create_PSI_MI_map_file();

function create_PSI_MI_map_file(){
  global $sorted_table_array;
  global $sort_list_arr;
  global $filtered_preyID_arr;
  global $itemName_property_arr;
  global $full_map_file;
  global $table_handle;
  global $export_handle;
  global $export_talble_index_arr;
  global $field_lable_arr;
  global $filterd_prey_arr;
  global $filterd_prey_lines_arr;
  global $MaxP_index;
   
  $map_handle = fopen($full_map_file, "w");  
  if(!$map_handle){
    echo "Cannot open file $full_map_file";
    return;
  }
  $table_line = '';
  foreach($export_talble_index_arr as $index){
    if($table_line) $table_line .= ',';
    $table_line .= $field_lable_arr[$index];
  }
  fwrite($table_handle, $table_line."\r\n");
  fwrite($export_handle, $table_line."\r\n");
  $counter = 0;
  
  $index_1 = array_shift($sort_list_arr);
  $index_2 = array_shift($sort_list_arr);
  foreach($sorted_table_array as $item_name => $prey_arrs){  
    $Bait_Gene_ID = $itemName_property_arr[$item_name]['GeneID'];
    $Bait_Acc = $itemName_property_arr[$item_name]['BaitAcc'];
    $Bait_ID = $itemName_property_arr[$item_name]['ID'];
    $Bait_Tax_ID = $itemName_property_arr[$item_name]['TaxID'];
    
    $item_line = "Bait::Bait Gene ID===".$Bait_Gene_ID.",Bait Gene Name===".$item_name.",Bait Acc===".$Bait_Acc.",Bait ID===".$Bait_ID.",Bait Tax ID===".$Bait_Tax_ID."\r\n";
    fwrite($map_handle, $item_line);
    
    foreach($prey_arrs as $prey_key => $prey_arr_tmp){
      $prey_arr = $prey_arr_tmp;
      if(in_array($prey_arr['PREY'], $filtered_preyID_arr)) $prey_arr['HIGH_CONFIDENCE'] = 0;
      if(in_array($prey_key, $filterd_prey_lines_arr)) continue;
      $cell = $item_name . "_" .$prey_key;
      if(in_array($cell, $filterd_prey_arr)) continue;
      if($prey_arr['HIGH_CONFIDENCE']){
        $priy_line = $prey_arr['PREYGENEID'].','.$prey_arr['PREYGENENAME'].','.$prey_arr['PREY'].','.($counter++).','.$prey_arr[$index_1].','.$prey_arr[$index_2]."\r\n";
        fwrite($map_handle, $priy_line);
      }  
      $table_line = '';
      foreach($export_talble_index_arr as $index){
        if($table_line) $table_line .= ',';
        $table_line .= $prey_arr[$index];
      }
      if($prey_arr['HIGH_CONFIDENCE']){
        fwrite($table_handle, $table_line."\r\n");
      }
      fwrite($export_handle, $table_line."\r\n");  
    }
  }
}

function cmp_prey_val($a, $b){
  global $orderby;
  if($a[$orderby] > $b[$orderby]){
    return 1;
  }elseif($a[$orderby] == $b[$orderby]){
    if($a['PREYGENENAME'] < $b['PREYGENENAME']){
      return 1;
    }  
  }
  return -1;
}

function cmp_prey_val_r($a, $b){
  global $orderby;
  if($a[$orderby] < $b[$orderby]){
    return 1;
  }elseif($a[$orderby] == $b[$orderby]){
    if($a['PREYGENENAME'] > $b['PREYGENENAME']){
      return 1;
    }  
  }
  return -1;
}

function print_bioGrid_icon($bait_gene_id,$prey_gene_id,$prey_gene_name,$item_name,$matched){
  global $grid_bait_hits_arr,$bio_checked_arr,$applyFilters,$bait_gene_id_arr,$matchGred_handle;
  global $item_ID_name_map_arr,$hitsNameArr,$matchedHitGeneIDarr,$j_counter,$bioGrid_typeStr;
  $gridImage = '';
  $bioGrid_typeStr = '';
  if($bio_checked_arr && $applyFilters){
    $gridIndex = $bait_gene_id;
    $gridHitsArr = array();
    if(array_key_exists($gridIndex, $grid_bait_hits_arr)){
      $gridHitsArr = $grid_bait_hits_arr[$gridIndex];
    }
    if(array_key_exists($prey_gene_id, $gridHitsArr)){
      $gridImage = get_bioGrid_icon($gridHitsArr[$prey_gene_id],$bioGrid_typeStr,'s');      
      $geneName = $prey_gene_name;
      $matchedLine = $matched.",".str_replace(",", ";",$item_name)."??".str_replace(",", ";",$geneName).",".$bioGrid_typeStr.",".$prey_gene_id."\r\n";      
      if($matchGred_handle) fwrite($matchGred_handle,$matchedLine);
      if($matched){
        if(!in_array($prey_gene_id, $matchedHitGeneIDarr)){
          $matchedHitGeneIDarr[$geneName] = $prey_gene_id;
        }
      } 
      echo $gridImage;
    }
  }
  if(!$matched && $gridImage) $j_counter++;
  if($bioGrid_typeStr) $bioGrid_typeStr = str_replace(":", ";", $bioGrid_typeStr);
}

function print_bioGrid_icon_noMatch($bait_gene_id,$no_matched_gene_info,$item_name,&$j_counter,&$tmpIndex2){
  global $bio_checked_arr, $allBaitgeneIDarr, $grid_bait_hits_arr,$matchGred_handle;
  global $item_ID_name_map_arr,$bioGrid_typeStr;    
  $gridImage = '';
  $bioGrid_typeStr = '';
  if($bio_checked_arr){
    $gridIndex = $bait_gene_id;
    $gridHitsArr = $grid_bait_hits_arr[$gridIndex];
    if(array_key_exists($no_matched_gene_info['EntrezGeneID'], $gridHitsArr)){
      $gridImage = get_bioGrid_icon($gridHitsArr[$no_matched_gene_info['EntrezGeneID']],$bioGrid_typeStr,'s');
      $geneName = $no_matched_gene_info['GeneName'];
      $matchedLine = "0,".str_replace(",", ";",$item_name)."??".str_replace(",", ";",$geneName).",".$bioGrid_typeStr.",".$no_matched_gene_info['EntrezGeneID']."\r\n";      
      if($matchGred_handle) fwrite($matchGred_handle,$matchedLine);
      echo $gridImage;
      $j_counter++;
      $tmpIndex2 = $bait_gene_id;
    }
  }
  if($bioGrid_typeStr) $bioGrid_typeStr = str_replace(":", ";", $bioGrid_typeStr);
}

function print_shared_color_bar(){
	global $theaction,$orderby;
  $powerColorIndex = 'Fequency';
  $colorArrSet = array();
  $aa = '';
  get_colorArrSets(1, $colorArrSet,$aa,'shared');
  
	$colorBarTotalW = 250;
	$colorCellW = 23;
	$colorCellH = 40;
  $maxScoreLable = 100;
?>			
    <td width=<?php echo $colorBarTotalW;?> colspan=2>
      <table align="" bgcolor='' cellspacing="0" cellpadding="0" border="0" width=78%>
        <tr height=40>
    <?php 
      $Key = 0;
      foreach($colorArrSet as $colorCell){
				if($theaction == "showNormal"){
		?>        
          <td valign=top width=<?php echo $colorCellW;?> class=s21 bgcolor='<?php echo $colorCell;?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
      <?php }else{?>
					<td valign=top width=<?php echo $colorCellW;?> class=s21 ><img src='./comparison_results_create_image.php?imageW=<?php echo $colorCellW;?>&imageH=<?php echo $colorCellH;?>&colorkey=<?php echo $Key;?>&powerColorIndex=<?php echo $powerColorIndex;?>' border=0></td>	
			<?php 
          $Key++;
				}
			}
		?> 
          <td valign=top width=<?php echo $colorCellW;?> class=s21>&nbsp;</td>         
        </tr>
        <tr>
          <?php 
          $i = 0;
          foreach($colorArrSet as $olorKey => $colorCell){              
          ?>     
          <td valign=top width=<?php echo $colorCellW;?> Valign="top"><?php echo 10*$i++?></td>
          <?php }?>
          <td valign=topwidth=<?php echo $colorCellW;?> Valign="top" nowrap><?php echo round($maxScoreLable)?><?print_colorbar_lable($orderby)?></td>          
        </tr>        
      </table>
    </td>
<?php 
}
function classify_filter($prey_index){
  global $red,$blue,$sharedColorSet,$totalitems;
  global $frm_red,$frm_green,$frm_blue,$applyFilters;
  global $prey_propty_arr;  
  if(!$applyFilters){
    $frm_red = 'y';
    $frm_green = 'y';
    $frm_blue = 'y';
  }
  if($prey_propty_arr[$prey_index]['count'] == $totalitems){
    if(!$frm_red) return '';
    $cellBgcolor = $red;
  }elseif($prey_propty_arr[$prey_index]['count'] == 1){
    if(!$frm_blue) return '';
    $cellBgcolor = $blue;
  }else{
    if(!$frm_green) return '';
    $colorIndex = floor($prey_propty_arr[$prey_index]['count']*10/$totalitems);
    $cellBgcolor = $sharedColorSet[$colorIndex];
  }
  return $cellBgcolor;
}



//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
function create_file_for_item_insert_info($ID_TYPE,$IS_COLLAPSE,$SELECTED_ID){
  if($ID_TYPE == 'Sample'){
    $Sample_ID_str = '';
    if($IS_COLLAPSE == 'no'){
      $Sample_ID_str = str_replace(";", ",", $SELECTED_ID);
    }elseif($IS_COLLAPSE == 'Experiment'){
//SELECTED_ID:(201)23|(212;213)28
      if(preg_match_all("/\(([\d;]+)\)/i", $SELECTED_ID, $matches)){
        $SampleID_arr = $matches[1];
        $Sample_ID_str_str = '';
        foreach($SampleID_arr as $SampleID_val){
          if($Sample_ID_str) $Sample_ID_str .= ",";
          $tmp_str = str_replace(";", ",", $SampleID_val);
          $Sample_ID_str .= $tmp_str;
        }
      }
    }elseif($IS_COLLAPSE == 'Bait'){
//echo "SELECTED_ID:(201)16|(212;213)18<br>";
      if(preg_match_all("/\(([\d;]+)\)/i", $SELECTED_ID, $matches)){
        $SampleID_arr = $matches[1];
        $Sample_ID_str_str = '';
        foreach($SampleID_arr as $SampleID_val){
          if($Sample_ID_str) $Sample_ID_str .= ",";
          $tmp_str = str_replace(";", ",", $SampleID_val);
          $Sample_ID_str .= $tmp_str;
        }
      }
    }
    if($Sample_ID_str){
      $itemID_arr = Sample_sql_info($Sample_ID_str);
      if($itemID_arr['BaitID_str']){
        $Bait_ID_str = $itemID_arr['BaitID_str'];
        Bait_sql_info($Bait_ID_str);
      }     
      if($itemID_arr['ExpID_str']){
        $Exp_ID_str = $itemID_arr['ExpID_str'];
        Exp_sql_info($Exp_ID_str);
      }
    }
  }elseif($ID_TYPE == 'Experiment'){
    if($IS_COLLAPSE == 'Experiment'){
      $Exp_ID_str = str_replace(";", ",", $SELECTED_ID);
    }elseif($IS_COLLAPSE == 'no'){
//25(200)|23(201)|27(202;30)|29(32)|28(212;213)
      $Exp_ID_str = '';
      $Exp_ID_arr = '';
      $pieces1 = explode("|", $SELECTED_ID);
      foreach($pieces1 as $pieces1_val){
        $pieces2 = explode("(", $pieces1_val);
        $Exp_ID_arr[] = $pieces2[0];
      }
      $Exp_ID_str = implode(',',$Exp_ID_arr);
    }
    
    if($Exp_ID_str){
      $itemID_arr = Exp_sql_info($Exp_ID_str);
      if($itemID_arr['BaitID_str']){
        $Bait_ID_str = $itemID_arr['BaitID_str'];
        Bait_sql_info($Bait_ID_str);
      }     
      if($itemID_arr['BandID_str']){
        $Band_ID_str = $itemID_arr['BandID_str'];
        Sample_sql_info($Band_ID_str);
      }
    }
  }elseif($ID_TYPE == 'Bait'){  
    if($IS_COLLAPSE == 'Bait'){
      $Bait_ID_str = str_replace(";", ",", $SELECTED_ID);
    }elseif($IS_COLLAPSE == 'no'){
//17(200)|16(201)|18(202;30;212;213)|19(32) 
      $Bait_ID_str = '';
      $Bait_ID_arr = '';
      $pieces1 = explode("|", $SELECTED_ID);
      foreach($pieces1 as $pieces1_val){
        $pieces2 = explode("(", $pieces1_val);
        $Bait_ID_arr[] = $pieces2[0];
      }
      $Bait_ID_str = implode(',',$Bait_ID_arr);
    }
    
    if($Bait_ID_str){
      $itemID_arr = Bait_sql_info($Bait_ID_str);
      if($itemID_arr['ExpID_str']){
        $Exp_ID_str = $itemID_arr['ExpID_str'];
        Exp_sql_info($Exp_ID_str);
      }    
      if($itemID_arr['BandID_str']){
        $Band_ID_str = $itemID_arr['BandID_str'];
        Sample_sql_info($Band_ID_str);
      }
    }
  }
}  


function Sample_sql_info($Sample_ID_str){
  global $HITSDB;
  global $DBname;
  global $other_handle;
  $DBname = trim($DBname);
//---Band------------------------------------------------      
  $SQL = "SELECT `ID`, `ExpID`,`BaitID`,`Location`,`ProjectID`, `RawFile` FROM `Band` WHERE `ID` IN ($Sample_ID_str)";
  $tmp_Sample_arr = $HITSDB->fetchAll($SQL);
  
  $sampleID_rawFile_arr = get_rawFile_info($tmp_Sample_arr); 
  
  $BaitID_arr = array();
  $ExpID_arr = array();
  foreach($tmp_Sample_arr as $tmp_Sample_value){
    if(!in_array($tmp_Sample_value['BaitID'],$BaitID_arr)){
      $BaitID_arr[] = $tmp_Sample_value['BaitID'];
    }
    if(!in_array($tmp_Sample_value['ExpID'],$ExpID_arr)){
      $ExpID_arr[] = $tmp_Sample_value['ExpID'];
    }
    $Sample_arr = $tmp_Sample_value;
    if($Sample_arr){
//-----------------------------------------------------------------------
      $Delete_sql = "DELETE FROM `Band_P` WHERE ID='".$Sample_arr['ID']."' AND DBname='$DBname'";
      fwrite($other_handle,$Delete_sql."\r\n");
//----------------------------------------------------------------------- 
      $rawFile = $sampleID_rawFile_arr[$Sample_arr['ID']];   
      $Sample_sql = "INSERT INTO Band_P SET ";
      foreach($Sample_arr as $key => $Sample_val){
        $Sample_sql .= "$key='".mysqli_real_escape_string($HITSDB->link,$Sample_val)."',";
      }
      $Sample_sql .= "RawFile_name='$rawFile',DBname='$DBname'";
      fwrite($other_handle,$Sample_sql."\r\n");
    }
  }
//--BandGroup-----------------------------------------------      
  $SQL = "SELECT `RecordID`, `Note`, `NoteTypeID` FROM `BandGroup` WHERE `RecordID` IN ($Sample_ID_str)"; 
  $tmp_group_arr = $HITSDB->fetchAll($SQL);
  $NoteTypeID_arr = array();
  $BandProtocol_arr = array();
  foreach($tmp_group_arr as $tmp_group_value){
    $group_arr = $tmp_group_value;
    if($group_arr){
      if(preg_match("/^SAM_/", $tmp_group_value['Note'])){
        if(!in_array($tmp_group_value['NoteTypeID'],$BandProtocol_arr)){
          $BandProtocol_arr[] = $tmp_group_value['NoteTypeID'];
        }
//-----------------------------------------------------------------------
        $Delete_sql = "DELETE FROM `BandProtocol_P` WHERE RecordID='".$group_arr['RecordID']."' AND DBname='$DBname'";
        fwrite($other_handle,$Delete_sql."\r\n");        
//-----------------------------------------------------------------------
        $group_sql = "INSERT INTO BandProtocol_P SET ";        
      }else{
        if(!in_array($tmp_group_value['NoteTypeID'],$NoteTypeID_arr)){
          $NoteTypeID_arr[] = $tmp_group_value['NoteTypeID'];
        }
//-----------------------------------------------------------------------
        $Delete_sql = "DELETE FROM `BandGroup_P` WHERE RecordID='".$group_arr['RecordID']."' AND DBname='$DBname'";
        fwrite($other_handle,$Delete_sql."\r\n");        
//-----------------------------------------------------------------------
        $group_sql = "INSERT INTO BandGroup_P SET ";
      }
      foreach($group_arr as $key => $group_val){
        $group_sql .= "$key='".mysqli_real_escape_string($HITSDB->link,$group_val)."',";
      }
      $group_sql .= "DBname='$DBname'";
      fwrite($other_handle,$group_sql."\r\n");
    }
  }
//--NoteType-------------------------------------------------------------
  $NoteTypeID_str = implode(',',$NoteTypeID_arr); 
  if($NoteTypeID_str){
    $SQL = "SELECT `ID`, `Name`, `Type`, `Description`, `Icon`, `ProjectID`, `UserID`, `Initial` 
            FROM `NoteType` WHERE `ID` IN ($NoteTypeID_str)";
    $tmp_Note_arr = $HITSDB->fetchAll($SQL);
    foreach($tmp_Note_arr as $tmp_Note_value){
      $Note_arr = $tmp_Note_value;
      $Note_sql = "INSERT INTO NoteType_P SET ";
      foreach($Note_arr as $key => $Note_val){
        $Note_sql .= "$key='".mysqli_real_escape_string($HITSDB->link,$Note_val)."',";
      }
      $Note_sql .= "DBname='$DBname'";
      fwrite($other_handle,$Note_sql."\r\n");
    }
  }
//--BandProtocol---------------------------------------------------------
  $BandProtocol_str = implode(',',$BandProtocol_arr); 
  if($BandProtocol_str){
    $SQL = "SELECT `ID`, `Name`, `Type`, `ProjectID`, `Detail`, `Date`, `UserID` 
            FROM `Protocol` WHERE `ID`IN ($BandProtocol_str)";
    $tmp_Protocol_arr = $HITSDB->fetchAll($SQL);
    foreach($tmp_Protocol_arr as $tmp_Protocol_value){
      $Protocol_arr = $tmp_Protocol_value;
      $Protocol_sql = "INSERT INTO `Protocol_P` SET ";
      foreach($Protocol_arr as $key => $Protocol_val){
        $Protocol_sql .= "$key='".mysqli_real_escape_string($HITSDB->link,$Protocol_val)."',";
      }
      $Protocol_sql .= "DBname='$DBname'";
      fwrite($other_handle,$Protocol_sql."\r\n");
    }
  }
  $item_arr['BaitID_str'] = implode(',',$BaitID_arr);
  $item_arr['ExpID_str'] = implode(',',$ExpID_arr);
  return $item_arr;
}

function Bait_sql_info($Bait_ID_str){
  global $HITSDB;
  global $DBname;
  global $other_handle;
  global $OF_map_file;
  
//-------------------------------------------------------
  $OF_map_arr = array();
  if(is_file($OF_map_file)){
    $OF_map_info = file($OF_map_file);
    foreach($OF_map_info as $OF_map_val){
      $tmp_arr = explode("::",trim($OF_map_val));
      $tmp_arr2 = explode(",",$tmp_arr[1]);
      $property_arr = array();
      foreach($tmp_arr2 as $tmp_val2){
        $tmp_arr3 = explode("=",$tmp_val2);
        $property_arr[$tmp_arr3[0]] = $tmp_arr3[1];
      }
      $OF_map_arr[$tmp_arr[0]] = $property_arr;
    }
  }
  /*echo "<pre>";
  print_r($OF_map_arr);
  echo "</pre>";*/
//---------------------------------------------------------
  $DBname = trim($DBname);
//---Bait_P------------------------------------------------      
  $SQL = "SELECT `ID`, `GeneID`, `LocusTag`, `GeneName`, 
                        `BaitAcc`, `AccType`, `TaxID`, `BaitMW`, `Family`, `Tag`, 
                        `Mutation`, `Clone`, `Vector`, `Description`, `OwnerID`, 
                        `ProjectID`, `DateTime`, `GelFree` 
                FROM `Bait` WHERE `ID` IN ($Bait_ID_str)";
  $tmp_Bait_arr = $HITSDB->fetchAll($SQL);
  $BandID_arr = array();
  $ExpID_arr = array();
  
  foreach($tmp_Bait_arr as $tmp_Bait_value){
    $Bait_arr = $tmp_Bait_value;
    if($Bait_arr){
//-----------------------------------------------------------------------
    $Delete_sql = "DELETE FROM `Bait_P` WHERE ID='".$Bait_arr['ID']."' AND DBname='$DBname'";
    fwrite($other_handle,$Delete_sql."\r\n");
//-----------------------------------------------------------------------    
      $Bait_sql = "INSERT INTO Bait_P SET ";
      foreach($Bait_arr as $key => $Bait_val){
        $Bait_sql .= "$key='".mysqli_real_escape_string($HITSDB->link,$Bait_val)."',";
      }
      if(array_key_exists($tmp_Bait_value['Vector'], $OF_map_arr)){
        $Map_arr = $OF_map_arr[$tmp_Bait_value['Vector']];
        foreach($Map_arr as $Map_key => $Map_val){
          $tmp_Map_key = "O_".$Map_key;
          $Bait_sql .= "$tmp_Map_key='".mysqli_real_escape_string($HITSDB->link,$Map_val)."',";
        }
      }
      $Bait_sql .= "DBname='$DBname'";
      fwrite($other_handle,$Bait_sql."\r\n");
    }
  }
//--BaitGroup-----------------------------------------------      
  $SQL = "SELECT `RecordID`, `Note`, `NoteTypeID` FROM `BaitGroup` WHERE `RecordID` IN ($Bait_ID_str)"; 
  $tmp_group_arr = $HITSDB->fetchAll($SQL);
  $NoteTypeID_arr = array();
  foreach($tmp_group_arr as $tmp_group_value){
    $group_arr = $tmp_group_value;
    if($group_arr){
      if(!in_array($tmp_group_value['NoteTypeID'],$NoteTypeID_arr)){
        $NoteTypeID_arr[] = $tmp_group_value['NoteTypeID'];
      }
//-----------------------------------------------------------------------
      $Delete_sql = "DELETE FROM `BaitGroup_P` WHERE RecordID='".$group_arr['RecordID']."' AND DBname='$DBname'";
      fwrite($other_handle,$Delete_sql."\r\n");        
//-----------------------------------------------------------------------
      $group_sql = "INSERT INTO BaitGroup_P SET ";
      foreach($group_arr as $key => $group_val){
        $group_sql .= "$key='".mysqli_real_escape_string($HITSDB->link,$group_val)."',";
      }
      $group_sql .= "DBname='$DBname'";
      fwrite($other_handle,$group_sql."\r\n");
    }
  }
//--NoteType-------------------------------------------------------------
  $NoteTypeID_str = implode(',',$NoteTypeID_arr); 
  if($NoteTypeID_str){
    $SQL = "SELECT `ID`, `Name`, `Type`, `Description`, `Icon`, `ProjectID`, `UserID`, `Initial` 
            FROM `NoteType` WHERE `ID` IN ($NoteTypeID_str)";
    $tmp_Note_arr = $HITSDB->fetchAll($SQL);
    foreach($tmp_Note_arr as $tmp_Note_value){
      $Note_arr = $tmp_Note_value;
      $Note_sql = "INSERT INTO NoteType_P SET ";
      foreach($Note_arr as $key => $Note_val){
        $Note_sql .= "$key='".mysqli_real_escape_string($HITSDB->link,$Note_val)."',";
      }
      $Note_sql .= "DBname='$DBname'";
      fwrite($other_handle,$Note_sql."\r\n");
    }
  }
  $SQL = "SELECT `ID`,`ExpID` FROM `Band` WHERE `BaitID` IN ($Bait_ID_str)";
  $tmp_itemID_arr = $HITSDB->fetchAll($SQL);  
  $BandID_arr = array();
  $ExpID_arr = array();
  foreach($tmp_itemID_arr as $tmp_itemID_val){
    if(!in_array($tmp_itemID_val['ID'],$BandID_arr)){
      $BandID_arr[] = $tmp_itemID_val['ID'];
    }
    if(!in_array($tmp_itemID_val['ExpID'],$ExpID_arr)){
      $ExpID_arr[] = $tmp_itemID_val['ExpID'];
    }
  }
  $itmeID_arr['BandID_str'] = implode(',',$BandID_arr);
  $itmeID_arr['ExpID_str'] = implode(',',$ExpID_arr);
  return $itmeID_arr;
}          

function Exp_sql_info($Exp_ID_str){
  global $HITSDB;
  global $DBname;
  global $other_handle;
  $DBname = trim($DBname);
//---Experiment-----------------------------------------------      
  $SQL = "SELECT `ID`, 
                  `BaitID`, 
                  `Name`, 
                  `TaxID`, 
                  `OwnerID`, 
                  `ProjectID`, 
                  `GrowProtocol`, 
                  `IpProtocol`, 
                  `DigestProtocol`, 
                  `PeptideFrag`, 
                  `PreySource`, 
                  `Notes`, 
                  `CollaboratorID` 
          FROM `Experiment` 
          WHERE `ID` IN ($Exp_ID_str)";
  $tmp_Exp_arr = $HITSDB->fetchAll($SQL);
  $BaitID_arr = array();
  $BandID_arr = array();
  $ExpProtocol_arr = array();
  $Protocol_name_arr = array('GrowProtocol','IpProtocol','DigestProtocol','PeptideFrag');
  
  foreach($tmp_Exp_arr as $tmp_Exp_value){
    if(!in_array($tmp_Exp_value['BaitID'],$BaitID_arr)){
      $BaitID_arr[] = $tmp_Exp_value['BaitID'];
    }
    $Exp_arr = $tmp_Exp_value;
    if($Exp_arr){
      foreach($Protocol_name_arr as $Protocol_name){
        if($Exp_arr[$Protocol_name]){
          $tmp_arr = explode(',',$Exp_arr[$Protocol_name]);
          if($tmp_arr[0]){
            if(!in_array($tmp_arr[0],$ExpProtocol_arr)){
              $ExpProtocol_arr[] = $tmp_arr[0];
            }
          }
        }
      }
//-----------------------------------------------------------------------
      $Delete_sql = "DELETE FROM `Experiment_P` WHERE ID='".$Exp_arr['ID']."' AND DBname='$DBname'";
      fwrite($other_handle,$Delete_sql."\r\n");
//-----------------------------------------------------------------------
      $Exp_sql = "INSERT INTO Experiment_P SET ";
      foreach($Exp_arr as $key => $Exp_val){
        $Exp_sql .= "$key='".mysqli_real_escape_string($HITSDB->link,$Exp_val)."',";
      }
      $Exp_sql .= "DBname='$DBname'";
      fwrite($other_handle,$Exp_sql."\r\n");
    }
  }
//--ExperimentGroup-----------------------------------------------      
  $SQL = "SELECT `RecordID`, `Note`, `NoteTypeID` FROM `ExperimentGroup` WHERE `RecordID` IN ($Exp_ID_str)"; 
  $tmp_group_arr = $HITSDB->fetchAll($SQL);
  $NoteTypeID_arr = array();
  foreach($tmp_group_arr as $tmp_group_value){
    $group_arr = $tmp_group_value;
    if($group_arr){
      if(!in_array($tmp_group_value['NoteTypeID'],$NoteTypeID_arr)){
        $NoteTypeID_arr[] = $tmp_group_value['NoteTypeID'];
      }
//-----------------------------------------------------------------------
      $Delete_sql = "DELETE FROM `ExperimentGroup_P` WHERE RecordID='".$group_arr['RecordID']."' AND DBname='$DBname'";
      fwrite($other_handle,$Delete_sql."\r\n");        
//-----------------------------------------------------------------------      
      $group_sql = "INSERT INTO ExperimentGroup_P SET ";
      foreach($group_arr as $key => $group_val){
        $group_sql .= "$key='".mysqli_real_escape_string($HITSDB->link,$group_val)."',";
      }
      $group_sql .= "DBname='$DBname'";
      fwrite($other_handle,$group_sql."\r\n");
    }
  } 
//--NoteType-------------------------------------------------------------
  $NoteTypeID_str = implode(',',$NoteTypeID_arr);
  if($NoteTypeID_str){
    $SQL = "SELECT `ID`, `Name`, `Type`, `Description`, `Icon`, `ProjectID`, `UserID`, `Initial` 
            FROM `NoteType` WHERE `ID` IN ($NoteTypeID_str)";
    $tmp_Note_arr = $HITSDB->fetchAll($SQL);
    foreach($tmp_Note_arr as $tmp_Note_value){
      $Note_arr = $tmp_Note_value;
      $Note_sql = "INSERT INTO NoteType_P SET ";
      foreach($Note_arr as $key => $Note_val){
        $Note_sql .= "$key='".mysqli_real_escape_string($HITSDB->link,$Note_val)."',";
      }
      $Note_sql .= "DBname='$DBname'";
      fwrite($other_handle,$Note_sql."\r\n");
    }
  }  
//--ExperimentProtocol---------------------------------------------------------
  $ExpProtocol_str = implode(',',$ExpProtocol_arr);
  if($ExpProtocol_str){
    $SQL = "SELECT `ID`, `Name`, `Type`, `ProjectID`, `Detail`, `Date`, `UserID` 
            FROM `Protocol` WHERE `ID`IN ($ExpProtocol_str)";
    $tmp_Protocol_arr = $HITSDB->fetchAll($SQL);

    foreach($tmp_Protocol_arr as $tmp_Protocol_value){
      $Protocol_arr = $tmp_Protocol_value;
      $Protocol_sql = "INSERT INTO `Protocol_P` SET ";
      foreach($Protocol_arr as $key => $Protocol_val){
        $Protocol_sql .= "$key='".mysqli_real_escape_string($HITSDB->link,$Protocol_val)."',";
      }
      $Protocol_sql .= "DBname='$DBname'";
      fwrite($other_handle,$Protocol_sql."\r\n");
    }
  }
  $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID` IN ($Exp_ID_str)";
  $tmp_itemID_arr = $HITSDB->fetchAll($SQL);
  $BandID_arr = array();
  foreach($tmp_itemID_arr as $tmp_itemID_val){
    $BandID_arr[] = $tmp_itemID_val['ID'];
  }
  $item_arr['BaitID_str'] = implode(',',$BaitID_arr);
  $item_arr['BandID_str'] = implode(',',$BandID_arr);
  return $item_arr;
}
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
function get_rawFile_info($BandArr){
  $id_fileName_arr = array();
  $msManagerDB = new mysqlDB(MANAGER_DB);
  $sampleID_rawFile_arr = array();
  foreach($BandArr as $BandValue){
 	  if(!time($BandValue['RawFile'])) continue;
 	  $rawFileInfoArr = explode(";",$BandValue['RawFile']);//rawfiles belong to a single band--
    $rawFileNameArr = array();
    foreach($rawFileInfoArr as $rawValue){
   	  $fileTableIDarr = explode(":",$rawValue);
      if(count($fileTableIDarr) != 2) continue;      
      if(preg_match("/^(\w+)tppResults$/", $fileTableIDarr[0], $matches)){
        $fileTableIDarr[0] = $matches[1];
      }
      $theTableName = $fileTableIDarr[0];
     	$fileTableIDarr[1] = preg_replace("/[,]$/",'', $fileTableIDarr[1]);
      if(!$fileTableIDarr[1]) continue;
     	$SQL = "SELECT `ID`,`FileName`,`FolderID`,`Size` FROM ".$theTableName." WHERE `ID` in(".$fileTableIDarr[1].")";
     	$mBaseArr1 = $msManagerDB->fetchAll($SQL);
      $MFileName_arr = array();                                     
     	foreach($mBaseArr1 as $mTableArr){
        $MFileName_arr[] = $rawValue."=".$mTableArr['FileName'];
      }
      $MFileName_str = implode(';;',$MFileName_arr);
      $rawFileNameArr[] = $MFileName_str;
    }
    $sampleID_rawFile_arr[$BandValue['ID']] = implode(';;',$rawFileNameArr);
  }
  return $sampleID_rawFile_arr;
}




?>
