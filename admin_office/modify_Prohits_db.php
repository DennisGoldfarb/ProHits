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

$Prohits_version = "V2.0.0";
$update_file_name = "update_prohitsdb_to_v2.0.0.sql";
$output_str = '';

require_once("../common/site_permission.inc.php");
include("common_functions.inc.php");
$update_file_name = "update_prohitsdb_to_v2.0.0.sql";

$is_v2 = false;
$mangerDB = new mysqlDB(MANAGER_DB);
$SQL = "SHOW TABLES";
$results = mysqli_query($mangerDB->link, $SQL);
while($row = mysqli_fetch_row($results)){
  if($row[0] == 'SAINT_log'){
    $is_v2 = true;
  }
}
if(!$is_v2){
  $sql_file = "../install/DB/".$update_file_name;
  $com = "mysql --user=".USERNAME." --password=".DBPASSWORD." < $sql_file";
   
  exec("$com 2>&1", $output);
  $output_str = implode("<br>", $output);
  echo $output_str;
  if(strpos( "$output_str", "You have an error")){
	echo "<h2>To fix above ERROR, Log on shell run mysql_upgrade then refresh this page: <br>#mysql_upgrade -u root -p</h2>";
	exit;
  }
  
  $ms_updated = false;
  $base_name = check_conditions_for_creat_tables($mangerDB);
   
  $save_conf_name = $base_name . "SaveConf";
  $SQL = "DESC $save_conf_name";
  $results = mysqli_query($mangerDB->link, $SQL);
  while($row = mysqli_fetch_row($results)){
    if($row[0] == 'SEQUEST_SaveWell_str'){
      $ms_updated = true;
    }
  }
  if(!$ms_updated){
    $ms_table_arr = get_current_DB_tables_name_arr($mangerDB);
    foreach($BACKUP_SOURCE_FOLDERS as $tmp_base=> $value){
      $tmp_saveConf_name = $tmp_base. "SaveConf";
      if(in_array($tmp_saveConf_name, $ms_table_arr)){
        $SQL = "ALTER TABLE `".$tmp_saveConf_name."` ADD `SEQUEST_SaveWell_str` text";
        mysqli_query($mangerDB->link, $SQL);
        $SQL = "ALTER TABLE `".$tmp_saveConf_name."` ADD `SEQUEST_Value` varchar(200) default NULL";
        mysqli_query($mangerDB->link, $SQL);
      }
    }
  }
}
if(!$output_str){
	echo "<h2>Databases have been updateed to version $Prohits_version";
}else{
  echo "<br>$com";
}

?>
