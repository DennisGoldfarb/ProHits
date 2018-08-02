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

ini_set('display_errors', 1);
set_time_limit(0);
ini_set("memory_limit","-1");


if(isset($_SERVER['argv']) and count($_SERVER['argv']) > 1){
  $table         = $_SERVER['argv'][1];
  $TaskID         = $_SERVER['argv'][2];
  chdir(dirname(__FILE__));
}else{
   echo "usage: php ".__FILE__." tableName TaskID";
   exit;
}

//-----------------------------------------------------------------------------------
include_once("../../config/conf.inc.php");
include_once("../../common/mysqlDB_class.php");
include ( "../autoBackup/shell_functions.inc.php");

$managerDB = new mysqlDB(MANAGER_DB);
$msManager_link = $managerDB->link;

$tableSearchResults = $table."SearchResults";
$target_dir = "/mnt/thegpm/gpm/archive/$table/";
$shell_file = "../../TMP/copy_raw_to_gpm.sh";

//get all search results ----------------------------------------------------------
$SQL = "SELECT T.ID,
          T.FileName,
          T.FolderID,
          R.TaskID,
          R.SearchEngines
          FROM $table T, $tableSearchResults R where T.ID=R.WellID and R.TaskID='$TaskID'";
$result_records = $managerDB->fetchAll($SQL);
$to_file_str = "#!/bin/bash/\n\n";
foreach($result_records as $result_val){
  //if file type is not mzML or mzXML or mzML.gz or mzXML.gz change name 
  //if strstr("DIAUmpire=", $Search_Engine) copy mzXML not mzML file.
  $source = getFilePath($table, $result_val['ID']);  
  if($result_val['SearchEngines'] == 'DIAUmpire'){
    $fileName = $result_val['ID']."_".preg_replace("/RAW$|wiff$/i", "mzXML", $result_val['FileName']);
    $source =preg_replace("/RAW$|wiff$/i", "mzXML", $source);
  }else{
    $fileName = $result_val['ID']."_".preg_replace("/RAW$|wiff$/i", "mzML", $result_val['FileName']);
    $source =preg_replace("/RAW$|wiff$/i", "mzML", $source);
  }   
  if(!is_file($source)){
    if(is_file($source.".gz")){
      $to_file_str .= "unzip $source\n";
    }
  }
  if(is_file($source)){ 
    $target = $target_dir.$fileName;
    $to_file_str .= "cp $source $target\n";
  }
}
$to_file_str .= "file created: $shell_file\n";
//writh the $to_file_str to $shell_file
$fp = fopen($shell_file, 'w');
fwrite($fp, $to_file_str);
fclose($fp);
echo "file created: $shell_file";
?>



