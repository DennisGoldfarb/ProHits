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
ob_start();
$orderby = "Pep_num";
$sendBy = '';
$allBaitgeneID_str = '';

$php_file_name = "cytoscape_export";

require("../common/site_permission.inc.php");
require_once("common/common_fun.inc.php");
require("analyst/common_functions.inc.php"); 
include("analyst/comparison_common_functions.php");
include("analyst/classes/cytoscape_default_page.php");
require_once("msManager/is_dir_file.inc.php");

$PROTEINDB = new mysqlDB(PROHITS_PROTEINS_DB);
if($sendBy == "item_repot"){
  require("analyst/export_lable_arrs.inc.php");  
  include("analyst/export_cytoscape_inc_for_item_report.php");
}else{
  include("analyst/comparison_results_export_inc.php");
}
$infileName = $filename_out;

if(!$infileName){
  echo "no input file $infileName.";
  exit;
}
$webstartDir = "../TMP/webstart/";
if(!_is_dir($webstartDir)) _mkdir_path($webstartDir);
$Expect = "Expect";
if($hitType == "GPM"){
  $Expect = "Expect2";
}
 
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);

$fieldIndexArr = array();
$fieldIndexArr[$Expect] = 0;
$fieldIndexArr['Pep_num'] = 1;
$fieldIndexArr['Pep_num_uniqe'] = 2;
$fieldIndexArr['Coverage'] = 3;
$fieldIndexArr['Fequency'] = 4;
if(!isset($fieldIndexArr[$orderby])){
  $orderby = 'Fequency'; 
}
$sortByIndex = $fieldIndexArr[$orderby];
$colorArrSet = array();

$filename_in = $infileName;

$lines = file($filename_in);
$start_flag = 0;
$exist_bait_geneID_name_arr = array();
$upload_pattern = '/^SAINT\s*NAME:.+?\(uploaded\)$/i';
$pattern = '/(.+)?\s(\S+)$/';
$is_upload = 0;
foreach($lines as $line){
//echo "$line<br>";
  $line = trim($line);
  if(preg_match($upload_pattern, $line)){
    $is_upload = 1;
  }
  if(strstr($line, ',Bait Gene ID') && !$start_flag){
    $start_flag = 1;
    continue;
  }
  if($start_flag){
    $pieces = explode(",", $line);
    $pieces[0] = trim($pieces[0]);
    if($is_upload){
      if(!array_key_exists($pieces[0], $exist_bait_geneID_name_arr)){
        $exist_bait_geneID_name_arr[$pieces[0]] = array($pieces[0]);
      }
    }else{
      $tmp_tmp_arr = explode("|", $pieces[0]);
      foreach($tmp_tmp_arr as $tmp_tmp_val){
        if(preg_match($pattern, $tmp_tmp_val, $matches)){
          if(!array_key_exists($matches[2], $exist_bait_geneID_name_arr)){
            $exist_bait_geneID_name_arr[$matches[2]] = array();
          }
          if(!in_array($matches[1], $exist_bait_geneID_name_arr[$matches[2]])){
            array_push($exist_bait_geneID_name_arr[$matches[2]], $matches[1]);
          }
        }
      }
    }  
  }
}

if(!$handle_read = fopen($filename_in, "r")){
  echo "Cannot open file ($filename_in)";
  exit;
}

while(!feof($handle_read)){
  $buffer = fgets($handle_read);
  $buffer = trim($buffer);
  if(!$buffer) break;
}
if(!$matchGred_handle = fopen($level1_matched_file, 'r')){
  echo "Cannot open file ($level1_matched_file)";
  exit;
}

$filename_out = $webstartDir.$AccessUserID."_cytoscape.xgmml";

if(!$handle_write = fopen($filename_out, "w")){
  echo "Cannot open file ($filename_out)";
  exit;
}

$cytoscape = new cytoscape_export($PROTEINDB);
$cytoscape->get_prohits_data($handle_read,$sendBy,$baitGeneIDarr,$allBaitgeneID_str,$exist_bait_geneID_name_arr);
$cytoscape->get_biogrid_data($matchGred_handle,$bio_checked_str);
$cytoscape->Layout_design();
$cytoscape->graph($handle_write);

fclose($handle_read);
fclose($handle_write);
fclose($matchGred_handle);
?>