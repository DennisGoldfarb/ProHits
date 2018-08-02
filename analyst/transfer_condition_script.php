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

//192.197.250.119', 'std', 'stdpass

require("../common/site_permission.inc.php");

$mammalianDB = new mysqlDB("Prohits_mml");

//=======ExpDetailName==========================================
$SQL = "SELECT `ConditionID` 
        FROM `ExpCondition` 
        WHERE ConditionID != 1
        GROUP BY `ConditionID`";
$mml_name_arr = $mammalianDB->fetchAll($SQL);
$pro_name_arr = $PROHITSDB->fetchAll($SQL);

insert_to_ExpDetailName($mml_name_arr,$mammalianDB,'32');
insert_to_ExpDetailName($pro_name_arr,$PROHITSDB,'32');

insert_SelectionID('32','Growth condition');

//====== ExpDetailProject========================================
$SQL = "SELECT 
        E.ProjectID 
        FROM ExpCondition C 
        LEFT JOIN Experiment E ON (C.ExpID=E.ID)
        WHERE ConditionID != 1
        GROUP BY E.ProjectID ";
$mml_project_arr = $mammalianDB->fetchAll($SQL);
$pro_project_arr = $PROHITSDB->fetchAll($SQL);

insert_to_ExpDetailProject($mml_project_arr,'32');
insert_to_ExpDetailProject($pro_project_arr,'32');

//=====ExpDetail==============================================
$SQL = "SELECT 
        C.ExpID,
        C.ConditionID,
        E.OwnerID,
        E.ProjectID 
        FROM ExpCondition C 
        LEFT JOIN Experiment E ON (C.ExpID=E.ID)
        WHERE ConditionID != 1
        ORDER BY E.ProjectID, C.ConditionID";
$pro_tmp_arr = $PROHITSDB->fetchAll($SQL);
$mml_tmp_arr = $mammalianDB->fetchAll($SQL);

get_detail_array($mml_tmp_arr);
get_detail_array($pro_tmp_arr);

insert_to_expDetail($mml_tmp_arr,$mammalianDB,'32');
insert_to_expDetail($pro_tmp_arr,$PROHITSDB,'32');


function insert_to_ExpDetailName(&$mml_name_arr,$DB,$SelectionID){
  global $PROHITSDB;
  foreach($mml_name_arr as $mml_name_val){
    if(!$mml_name_val['ConditionID']) continue;
    $SQL = "SELECT `ID`,
            `Condition`,
            `UserID` 
            FROM `Condition` 
            WHERE `ID`='".$mml_name_val['ConditionID']."'";
    $tmpArr = $DB->fetch($SQL);
    
    $SQL = "INSERT INTO ExpDetailName SET
              ID='".$tmpArr['ID']."',
              ParentID='$SelectionID',
              Name='".$tmpArr['Condition']."',
              UserID='".$tmpArr['UserID']."',
              DT='".@date('Y-m-j')."'";
    $PROHITSDB->insert($SQL);          
  }
}

function insert_SelectionID($SelectionID,$name){
  global $AccessUserID,$PROHITSDB;
  $SQL = "INSERT INTO ExpDetailName SET
              ID='".$SelectionID."',
              ParentID='0',
              Name='".$name."',
              UserID='".$AccessUserID."',
              DT='".@date('Y-m-j')."'";
  $PROHITSDB->insert($SQL);
}

function insert_to_ExpDetailProject(&$mml_project_arr,$selection_id){
  global $PROHITSDB,$AccessUserID;
  foreach($mml_project_arr as $mml_project_val){
    if(!$mml_project_val['ProjectID']) continue;
    $SQL = "INSERT INTO ExpDetailProject SET
            SelectionID ='$selection_id',
            ProjectID ='".$mml_project_val['ProjectID']."',
            UserID='$AccessUserID'";
    $PROHITSDB->insert($SQL);
  }
}

function get_detail_array(&$mml_tmp_arr){
  $m_exp_id_arr = array();
  for($i=0; $i<count($mml_tmp_arr); $i++){
    if(!$mml_tmp_arr[$i]['ProjectID']) $mml_tmp_arr[$i]['ProjectID'] = 0;
    if(!array_key_exists($mml_tmp_arr[$i]['ExpID'], $m_exp_id_arr)){
      $m_exp_id_arr[$mml_tmp_arr[$i]['ExpID']] = 1;
      $mml_tmp_arr[$i]['IndexNum'] = 1;
    }else{
      $m_exp_id_arr[$mml_tmp_arr[$i]['ExpID']]++;
      $mml_tmp_arr[$i]['IndexNum'] = $m_exp_id_arr[$mml_tmp_arr[$i]['ExpID']];
    }
  }
}

function insert_to_expDetail(&$mml_tmp_arr,$DB,$SelectionID){
  foreach($mml_tmp_arr as $mml_tmp_val){
    $SQL = "INSERT INTO ExpDetail SET
            ExpID ='".$mml_tmp_val['ExpID']."',
            SelectionID='$SelectionID',
            OptionID='".$mml_tmp_val['ConditionID']."', 
            IndexNum='".$mml_tmp_val['IndexNum']."',
            UserID='".$mml_tmp_val['OwnerID']."',
            DT='".@date('Y-m-j')."'";
    $new_selection_id = $DB->insert($SQL);
  }
}
?>