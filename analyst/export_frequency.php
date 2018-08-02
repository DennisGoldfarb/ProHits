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

require("../common/site_permission.inc.php");
require("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

$frequency_dir_arr = array();
$Prohits_Data_dir = STORAGE_FOLDER . "Prohits_Data/";
$frequency_dir = $Prohits_Data_dir . "frequency";
$frequency_dir_arr['P'] = $frequency_dir;
$sub_frequency_dir = $Prohits_Data_dir . "subFrequency";
$frequency_dir_arr['G'] = $sub_frequency_dir;
$user_frequency_dir = $Prohits_Data_dir . "user_d_frequency/P_$AccessProjectID";
$frequency_dir_arr['U'] = $user_frequency_dir;

$frequency_report_dir = "../TMP/frequency_report";
if(!_is_dir($frequency_report_dir)){
  _mkdir_path($frequency_report_dir);
}

$tmp_frequency_name = explode(":", $frm_frequency_name);
$frequency_Dir = $frequency_dir_arr[$tmp_frequency_name[0]];
$filename = $frequency_Dir."/".$tmp_frequency_name[1];

$export_filename = $frequency_report_dir."/P".$AccessProjectID."_exported_".$tmp_frequency_name[1];
$frequencyArr = file($filename);

$fp = fopen($export_filename, 'w');
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
$is_start_point = 0;
$is_GeneLevel = 0;
if(stristr($frm_frequency_name, 'GeneLevel')){
  $is_GeneLevel = 1;
}
/*echo "<pre>";
print_r($frequencyArr);
echo "</pre>";*/
//exit;
foreach($frequencyArr as $frequencyAtr){
  $frequencyAtr = trim($frequencyAtr);
  if(!$frequencyAtr) continue;
  $tmpArr = explode(',',$frequencyAtr);
  if($tmpArr[0] == 'GeneID'){
    $is_start_point = 1;
  }elseif(!$is_start_point){
    continue;
  }  
  $line = '';
  if($tmpArr[0] == 'GeneID'){
    $line = trim($tmpArr[0]).",GeneName,".$tmpArr[1];
  }else{
    if($is_GeneLevel){
      $line = str_replace("|", ",", $tmpArr[0]).",".$tmpArr[1];;
    }else{
      if(is_numeric($tmpArr[0])){
        $tableName = 'Protein_Class';
        $geneID = "EntrezGeneID";
      }else{
        $tableName = 'Protein_ClassENS';
        $geneID = "ENSG";
      }
      $SQL = "SELECT `GeneName` FROM $tableName WHERE $geneID='".$tmpArr[0]."'";
      if($temGene = $proteinDB->fetch($SQL)){
        $GeneName = $temGene['GeneName'];
      }else{
        $GeneName = '';
      }
      $line = $tmpArr[0].",".$GeneName.",".$tmpArr[1];
    }
  }
  $line = trim($line);
  if(!$line) continue;
  fwrite($fp, $line."\r\n");
}
fclose($fp);
export_file($export_filename);
?>