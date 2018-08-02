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

$pro_access_ID_str = '';
$pro_access_ID_Names = array();
$tableName = '';
$table = ''; 
//permission and include files --------------------------------------------------------
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("common/page_counter_class.php");

$managerDB = new mysqlDB(MANAGER_DB);
$prohitsDB = new mysqlDB(PROHITS_DB);

mysqli_query($managerDB->link, "SET SESSION sql_mode = ''");
mysqli_query($prohitsDB->link, "SET SESSION sql_mode = ''");

 
$taskTables= $managerDB->list_tables();
$USER = $_SESSION['USER']; 

$SQL  = "select P.Insert, P.Modify, P.Delete from PagePermission P, Page G where P.PageID=G.ID and G.PageName like 'Auto Search%' and UserID=$USER->ID";
$record = $prohitsDB->fetch($SQL);
$perm_modify = '';
$perm_delete = '';
$perm_insert = '';
if(count($record)){
  $perm_modify = $record['Modify'];
  $perm_delete = $record['Delete'];
  $perm_insert = $record['Insert'];
}

if($tableName and !$table) $table = $tableName;
$tableSearchResults = $table . "SearchResults";
$tableSearchTasks = $table . "SearchTasks";
$tableSaveConf = $table . "SaveConf";

$tableTppTasks = $table . "tppTasks";
$tableTppResults = $table . "tppResults";

if($USER->Type == 'Admin' or $USER->Type == 'MSTech'){
  $SQL = "SELECT P.ID, P.Name FROM Projects P order by P.ID"; 
}else{
  $SQL = "SELECT P.ID, P.Name FROM Projects P, ProPermission M where P.ID=M.ProjectID and M.UserID=$USER->ID order by P.ID"; 
}
//echo "$SQL<BR>";
$records = $prohitsDB->fetchAll($SQL);
foreach($records as $pID){
  if($pro_access_ID_str) $pro_access_ID_str .= ",";
  $pro_access_ID_str .= $pID['ID'];
  $pro_access_ID_Names[$pID['ID']] = $pID['Name'];
}
//   "RAW, dta, mgf, mzData, mzXML"
//   '/\.raw$|\.dta$|\.mgf$|\.XML$|\.XML$/i'
$raw_file_pattern = preg_replace("/msData|mzXML/", "XML", $RAW_FILES);
$raw_file_pattern = str_replace(" ", "", $RAW_FILES);
$raw_file_pattern = '/\.' . str_replace(",", '$|\.', $raw_file_pattern) . '$/i';
//end--------------------------------------------------------------------------------
?>