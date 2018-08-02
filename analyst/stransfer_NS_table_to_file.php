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
require_once("msManager/is_dir_file.inc.php");

$SQL = "SELECT `ProjectID` FROM `ExpFilter` WHERE `FilterAlias`='NS' GROUP BY `ProjectID`";
if(!$temDBArr = $HITSDB->fetchAll($SQL)){
  echo "db problem1";
  exit;
}
$NS_Dir = "../TMP/Non_Specific/";
if(!_is_dir($NS_Dir)) _mkdir_path($NS_Dir);
$NS_upload_dir = $NS_Dir."NS_upload/";
if(!_is_dir($NS_upload_dir)) _mkdir_path($NS_upload_dir);
$NS_data_dir = $NS_Dir."NS_data/";
if(!_is_dir($NS_data_dir)) _mkdir_path($NS_data_dir);

foreach($temDBArr as $temDBvalue){
  $SQL = "SELECT `GeneID` FROM `ExpFilter` WHERE `FilterAlias`='NS' AND `ProjectID`='".$temDBvalue['ProjectID']."'";
  if(!$temDBArr2 = $HITSDB->fetchAll($SQL)){
    echo "db problem1";
    exit;
  }
  $tmpGeneIDarr = array();
  foreach($temDBArr2 as $temDBvalue2){
    if(in_array($temDBvalue2['GeneID'], $tmpGeneIDarr)) continue;
    array_push($tmpGeneIDarr, $temDBvalue2['GeneID']);
  }
  $geneIDstr = implode(",", $tmpGeneIDarr);
  $groupName = 'Background1';
  $SQL = "INSERT INTO `ExpBackGroundSet` SET 
            `Name`='$groupName',
            `ProjectID`='".$temDBvalue['ProjectID']."',
            `UserID`='$AccessUserID',
            `Date`='".@date("Y-m-d")."'";
  if(!$groupID = $HITSDB->insert($SQL)){
    echo "db insert problem";
    exit;
  }
  $fileName = "P".$AccessProjectID."_G".$groupID."_".$groupName.".txt";
  $SQL = "UPDATE `ExpBackGroundSet` SET
         `FileName`='$fileName'
         WHERE ID= '$groupID'";
  if(!$ret = $HITSDB->execute($SQL)){
    echo "db update problem";
    exit;
  }
  $dataFileFullName = $NS_data_dir.$fileName;
  if(!$NS_data_handle = fopen($dataFileFullName, "w")){
    echo "Cannot open file $new_full_file_name";
    exit;
  }
  fwrite($NS_data_handle, $geneIDstr);
}
echo "ok";
?>

