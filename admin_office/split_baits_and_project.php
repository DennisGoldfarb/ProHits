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

//only work for 'Admin' with page permission
exit;
$theaction = '';
$frm_project_ID = 0;
$hits_db_name = '';
$bait_results = '';
$request_arr = array();
$frm_selected_bait_str = '';
$frm_order_by = 'ID DESC';
$baitIDarr_have_hits = array();
$baitNotesArr = array(); 
$bait_group_icon_arr = array();
$Selectedbaits = array();
$frm_selected_bait_str = '';
$selected_bait_results = array();

$msg = '';
$error_msg = '';
$frm_type = 'Bait';
$frm_is_all_baits = 0;
$frm_select_group = '';

$menu_color = '#669999';
$msg = '';
$can_submitted = false;
$hitDB = '';
$permitted = '';  

set_time_limit ( 2400 ) ; 

require("../common/site_permission.inc.php");
/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
$frm_project_ID = '130';
$frm_to_project_ID = '103';
$theaction = 'process';

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
$host = HOSTNAME;
$user = USERNAME;
$pswd = DBPASSWORD;
$prohits_link  = mysqli_connect("$host", $user, $pswd, PROHITS_DB) or die("Unable to connect to mysql..." . mysqli_error($prohits_link));
$msManager_link  = mysqli_connect("$host", $user, $pswd, MANAGER_DB) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));
 
/*if($USER->Type != 'Admin'){
  echo "Only Prohits admin has permission to access this page";exit;
}*/

$SQL = "SELECT ID, Name, DBname FROM Projects order by ID";
$results = mysqli_query($prohits_link, $SQL);
while($row = mysqli_fetch_assoc($results)){
  $pro_access_ID_Names[$row['ID']] = $row['Name'];
  $pro_access_ID_database_Names[$row['ID']] = $row['DBname'];
  if($frm_project_ID and $frm_project_ID == $row['ID']){
    $hits_db_name = $row['DBname'];
  }
}

$log_dir = '../logs/';
$log_path = $log_dir.'split_bait_project_'. $USER->Username. @date("Y-m-d").".txt";

if($theaction == 'process' and $frm_project_ID){
  mysqli_select_db($prohits_link, $HITS_DB[$hits_db_name]);
  $HITSDB->change_db($HITS_DB[$hits_db_name]);
echo $HITSDB->selected_db_name."<br>";
  
$Sample_ID_str = "5350,5351,5548,5549,5362,5363,5530,5531,5374,5375,5536,5537,5376,5377,5544,5545,5370,5371,5540,5541,5352,5353,5528,5529,5364,5365,5552,5553,5378,5379,5526,5527,5372,5382,5532,5533,5360,5361,5546,5547,5346,5347,5354,5355,5542,5543";

//$Sample_ID_str = "5368,5369,5550,5551";
//$Sample_ID_str = "5348,5349,5538,5539";


//$Exp_ID_str = "2789,2850,2779,2844,2780,2849,2786,2839,2792,2843,2793,2847,2790,2845,2781,2838,2787,2851,2794,2837,2791,2841,2785,2848,2778,2783,2846";
//$Bait_ID_str = "970,717,547,544,514,505,503,502,493,458,456,454,450,184";
 
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  $SQL = "SELECT `ID`,`ExpID`,`BaitID` FROM `Band` WHERE `ID` IN ($Sample_ID_str)";
  $Band_SQL_arr = $HITSDB->fetchAll($SQL);

 
  
  $Bait_ID_arr = array();
  $Exp_ID_arr = array();
  $Band_ID_arr = array();
  $Band_BaitExpID_arr = array();//---use to update Band table.
  $Bait_Exp_ID_arr = array();     //----use to create new Bait and Exp.
  $Bait_Sample_ID_arr = array();  //----use to update hits table
  
  
  
  
echo "<pre>";
print_r($Band_SQL_arr); 
echo "</pre>";
  
  
  foreach($Band_SQL_arr as $Band_SQL_val){
    if(!in_array($Band_SQL_val['BaitID'], $Bait_ID_arr)){
      $Bait_ID_arr[] = $Band_SQL_val['BaitID'];
    }
    if(!in_array($Band_SQL_val['ExpID'], $Exp_ID_arr)){
      $Exp_ID_arr[] = $Band_SQL_val['ExpID'];
    }
    if(!in_array($Band_SQL_val['ID'], $Band_ID_arr)){
      $Band_ID_arr[] = $Band_SQL_val['ID'];
    }
    $Band_BaitExpID_arr[$Band_SQL_val['ID']] = $Band_SQL_val['BaitID'].','.$Band_SQL_val['ExpID'];
//-------------------------------------------------------------------------------------------------


    if(!array_key_exists($Band_SQL_val['BaitID'], $Bait_Exp_ID_arr)){
      $Bait_Exp_ID_arr[$Band_SQL_val['BaitID']] = array();
    }
    
    
    
    if(!in_array($Band_SQL_val['ExpID'], $Bait_Exp_ID_arr[$Band_SQL_val['BaitID']])){
      $Bait_Exp_ID_arr[$Band_SQL_val['BaitID']][] = $Band_SQL_val['ExpID'];
    }
    
    
//-------------------------------------------------------------------------------------------------    
    if(!array_key_exists($Band_SQL_val['BaitID'], $Bait_Sample_ID_arr)){
      $Bait_Sample_ID_arr[$Band_SQL_val['BaitID']] = array();
    }
    $Bait_Sample_ID_arr[$Band_SQL_val['BaitID']][] = $Band_SQL_val['ID'];
 } 
 
echo "<pre>";
echo "Bait_ID_arr=<br>";
print_r($Bait_ID_arr);
echo "Exp_ID_arr=<br>";  
print_r($Exp_ID_arr);
echo "Band_ID_arr=<br>";
print_r($Band_ID_arr); 
echo "Band_BaitExpID_arr=<br>"; 
print_r($Band_BaitExpID_arr);
echo "Bait_Exp_ID_arr=<br>"; 
print_r($Bait_Exp_ID_arr);
echo "Bait_Sample_ID_arr=<br>"; 
print_r($Bait_Sample_ID_arr);   
echo "</pre>";

//exit;

    $to_file = "\n\n".@date("Y-m-d H:i:s")."=====================================================================";
    $to_file .= "\n\nMove Baits from Project: <b>($frm_project_ID)" . $pro_access_ID_Names[$frm_project_ID] . "</b>
     to Project: <b>($frm_to_project_ID)". $pro_access_ID_Names[$frm_to_project_ID]."</b>
     Selected Bait IDs: <b>$frm_selected_bait_str</b>";

//exit;
    $BaitID_old_new_arr = array();
    $ExpID_old_new_arr = array();
    
    foreach($Bait_Exp_ID_arr as $Bait_Exp_ID_key => $Bait_Exp_ID_val){
      $SQL = "SELECT `ID`, `GeneID`, `LocusTag`, `GeneName`, `BaitAcc`, `AccType`, `TaxID`, `BaitMW`, `Family`, `Tag`, `Mutation`, `Clone`, `Vector`, `Description`, `OwnerID`, `ProjectID`, `DateTime`, `GelFree` 
              FROM `Bait` 
              WHERE `ID`='$Bait_Exp_ID_key'";
      $Bait_SQL_arr = $HITSDB->fetch($SQL);
/*echo "<pre>";
print_r($Bait_SQL_arr); 
echo "</pre>";*/
      $insert_str = '';       
      if($Bait_SQL_arr){
        foreach($Bait_SQL_arr as $key => $val){
          if($insert_str) $insert_str .= ",";
          if($key == 'ID') continue;
          if($key == 'ProjectID') $val = "$frm_to_project_ID";
          $insert_str .= "$key='".mysqli_real_escape_string($HITSDB->link,$val)."'";
        }
        $insert_SQL = "INSERT INTO `Bait` SET " . $insert_str;
        
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        $newBaitID = $HITSDB->insert($insert_SQL);//^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
echo "\$newBaitID=$newBaitID<br>";
        $BaitID_old_new_arr[$Bait_SQL_arr['ID']] = $newBaitID;
        $to_file .= "\n$SQL";
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
      }
      
      $Exp_arr = $Bait_Exp_ID_val;
      foreach($Exp_arr as $Exp_val){
        $SQL = "SELECT `ID`, `BaitID`, `Name`, `TaxID`, `OwnerID`, `ProjectID`, `GrowProtocol`, `IpProtocol`, `DigestProtocol`, `PeptideFrag`, `PreySource`, `Notes`, `WesternGel`, `DateTime`, `CollaboratorID` 
                FROM `Experiment` 
                WHERE `ID`='$Exp_val'";
        $Exp_SQL_arr = $HITSDB->fetch($SQL);
        $Exp_insert_str = '';
        if($Exp_SQL_arr){
          foreach($Exp_SQL_arr as $Exp_key => $Exp_val){
            if($Exp_insert_str) $Exp_insert_str .= ",";
            if($Exp_key == 'ID') continue;
            if($Exp_key == 'ProjectID') $Exp_val = "$frm_to_project_ID";
            if($Exp_key == 'BaitID') $Exp_val = $newBaitID;
            $Exp_insert_str .= "$Exp_key='".mysqli_real_escape_string($HITSDB->link,$Exp_val)."'";
          }
          
          $insert_SQL = "INSERT INTO `Experiment` SET " . $Exp_insert_str;
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
          $newExpID = $HITSDB->insert($insert_SQL);
          $ExpID_old_new_arr[$Exp_SQL_arr['ID']] = $newExpID;
echo "\$newExpID=$newExpID<br>";
          $to_file .= "\n$SQL";
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        }
      }
    }
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!delete
//$BaitID_old_new_arr[18] = 32;
//$ExpID_old_new_arr[28] = 50;
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

  $frm_selected_bait_str = implode(',',$Bait_ID_arr);
  $frm_selected_Exp_str = implode(',',$Exp_ID_arr);
  $frm_selected_band_str = implode(',',$Band_ID_arr);
  
  if(!$frm_to_project_ID){
     $error_msg = "Where do you want to move baits?";
  }else if(!$frm_selected_bait_str){
    $error_msg = "Please select baits?";
  }else{    
//=========Process Experimental Editor==================================================================================================
    $to_file .= "\n\nProcess Experimental Editor-------------------";
    $SQL = "SELECT N.ID,
                   N.ParentID, 
                   N.Name,
                   N.UserID,
                   P.ProjectID 
            FROM ExpDetailName N
            LEFT JOIN ExpDetailProject P
            ON (P.SelectionID=N.ID)
            WHERE `ProjectID`='$frm_project_ID'";
    $tmp_Parent_arr = $HITSDB->fetchAll($SQL);

    $from_arr = array();
    foreach($tmp_Parent_arr as $tmp_Parent_val){
      $from_arr[$tmp_Parent_val['Name']] = $tmp_Parent_val;
    }
    $SQL = "SELECT N.ID,
                   N.ParentID, 
                   N.Name,
                   N.UserID,
                   P.ProjectID 
            FROM ExpDetailName N
            LEFT JOIN ExpDetailProject P
            ON (P.SelectionID=N.ID)
            WHERE `ProjectID`='$frm_to_project_ID'";
    $tmp_Parent_to_arr = $HITSDB->fetchAll($SQL);

    $to_arr = array();
    foreach($tmp_Parent_to_arr as $tmp_Parent_to_val){
      $to_arr[$tmp_Parent_to_val['Name']] = $tmp_Parent_to_val;
    }
    $old_new_selectionID_arr = array();//======================================================================!!!!!!!!!!!!!
    foreach($from_arr as $key => $val){
      if(array_key_exists($key, $to_arr)){
        $old_new_selectionID_arr[$val['ID']] = $to_arr[$key]['ID'];
      }else{
        $SQL = "INSERT INTO `ExpDetailName` 
                SET ParentID=0, 
                Name='".$val['Name']."', 
                UserID='".$val['UserID']."', 
                DT='".@date('Y-m-j')."'";
        $ParID = $HITSDB->insert($SQL);
        $to_file .= "\n$SQL";
        if($ParID){
          $old_new_selectionID_arr[$val['ID']] = $ParID;
          $SQL = "INSERT INTO `ExpDetailProject`
                  SET SelectionID=$ParID,
                  ProjectID='$frm_to_project_ID', 
                  UserID='".$val['UserID']."', 
                  DT='".@date('Y-m-j')."'";
          $HITSDB->insert($SQL);
          $to_file .= "\n$SQL";
        }
      }
    }
    $selectionID_str = '';
    foreach($old_new_selectionID_arr as $key => $val){
      if($selectionID_str) $selectionID_str .= ',';
      $selectionID_str .= $key;
    }

    $old_new_optionID_arr = array();   //=============================================================!!!!!!!!!!!!!!!!!!!!!!!!!
    if($selectionID_str){
      $SQL = "SELECT `ID`, `ParentID`, `Name`, `UserID`, `DT` FROM `ExpDetailName` WHERE `ParentID` IN ($selectionID_str)";
      $tmp_ExpDetailName_arr = $HITSDB->fetchAll($SQL);
      foreach($tmp_ExpDetailName_arr as $val){
        $new_parent_id = $old_new_selectionID_arr[$val['ParentID']];
        $new_Name = $val['Name'];
        $SQL = "SELECT `ID` 
                FROM `ExpDetailName` 
                WHERE `ParentID`= '$new_parent_id'
                AND `Name`='$new_Name'";
        $tmp_arr = $HITSDB->fetch($SQL);
        if($tmp_arr){
          $old_new_optionID_arr[$val['ID']] = $tmp_arr['ID'];
        }else{
          $SQL = "INSERT INTO `ExpDetailName` 
                  SET ParentID=$new_parent_id, 
                  Name='".$val['Name']."', 
                  UserID='".$val['UserID']."', 
                  DT='".@date('Y-m-j')."'";
          $new_option_id = $HITSDB->insert($SQL);
          $to_file .= "\n$SQL";
          if($new_option_id){
            $old_new_optionID_arr[$val['ID']] = $new_option_id;
          }
        }
      }
    }

    if($frm_selected_Exp_str){
      $SQL = "SELECT `ExpID`, `SelectionID`, `OptionID`, `IndexNum`, `UserID`, `DT` FROM `ExpDetail` WHERE `ExpID` IN ($frm_selected_Exp_str)";
      $tmp_ExpDetail_arr = $HITSDB->fetchAll($SQL);
      
      foreach($tmp_ExpDetail_arr as $tmp_ExpDetail_val){
        if(!array_key_exists($tmp_ExpDetail_val['ExpID'], $ExpID_old_new_arr)) continue;
        if(array_key_exists($tmp_ExpDetail_val['SelectionID'], $old_new_selectionID_arr) && array_key_exists($tmp_ExpDetail_val['OptionID'], $old_new_optionID_arr)){
          $new_ExpID = $ExpID_old_new_arr[$tmp_ExpDetail_val['ExpID']];
//echo $tmp_ExpDetail_val['ExpID']."<br>";
          $new_SelectionID = $old_new_selectionID_arr[$tmp_ExpDetail_val['SelectionID']];
          $new_OptionID = $old_new_optionID_arr[$tmp_ExpDetail_val['OptionID']];
          $SQL = "INSERT INTO `ExpDetail` SET `ExpID`='$new_ExpID',
                                         `SelectionID`='$new_SelectionID',
                                         `OptionID`='$new_OptionID',
                                         `DT`='".@date('Y-m-j')."'";
          $HITSDB->insert($SQL);
          $to_file .= "\n$SQL";        
        } 
      }
    }     
echo "<pre>";
echo "BaitID_old_new_arr=<br>";
print_r($BaitID_old_new_arr);
echo "ExpID_old_new_arr=<br>";
print_r($ExpID_old_new_arr);
      
//=========process NoteType and itemGroups =============================================================================================
    $to_file .= "\n\nprocess NoteType and itemGroups------------------------";
    $SQL = "SELECT `ID`, `Name`, `Type`, `Description`, `Icon`, `ProjectID`, `UserID`, `Initial` FROM `NoteType` WHERE `ProjectID`='$frm_project_ID'";
    $NoteType_rs = mysqli_query($prohits_link, $SQL);
    $NoteType_map_arr = array();
    while($NoteType_row = mysqli_fetch_assoc($NoteType_rs)){ 
//print_r($NoteType_row);             
      $SQL = "select ID from `NoteType` 
              where ProjectID='$frm_to_project_ID' 
              and Type='".$NoteType_row['Type']."' 
              and Name='".mysqli_real_escape_string($prohits_link, $NoteType_row['Name'])."'
              and Initial='".$NoteType_row['Initial']."'"; 
      $tmp_rs = mysqli_query($prohits_link, $SQL);
      if($new_row = mysqli_fetch_assoc($tmp_rs)){
        $NoteType_map_arr[$NoteType_row['ID']] = $new_row['ID'];
      }else{
        $NoteType_map_arr[$NoteType_row['ID']] = $NoteType_row;
      }
    }
//print_r($NoteType_map_arr);

    //---BaitGroup------------
    $SQL = "SELECT `ID`, `RecordID`, `Note`, `NoteTypeID`, `UserID`, `DateTime` FROM `BaitGroup` WHERE RecordID in($frm_selected_bait_str)";
    update_itemGroup_to_new_project($NoteType_map_arr,'BaitGroup',$SQL);
    
    //---ExperimentGroup------
    /*$SQL = "select ID from Experiment where BaitID in ($frm_selected_bait_str)";
    $exp_rs = mysqli_query($prohits_link, $SQL);
    $Exp_str = '';
    while($exp_row = mysqli_fetch_assoc($exp_rs)){
      if($Exp_str) $Exp_str .= ',';
      $Exp_str .= $exp_row['ID'];
    }*/
    $SQL = "SELECT `ID`, `RecordID`, `Note`, `NoteTypeID`, `UserID`, `DateTime` FROM `ExperimentGroup` WHERE RecordID in($frm_selected_Exp_str)";
    update_itemGroup_to_new_project($NoteType_map_arr,'ExperimentGroup',$SQL);
    
    //---BandGroup------------
    /*$SQL = "select ID from Band where BaitID in ($frm_selected_bait_str)";
    $band_rs = mysqli_query($prohits_link, $SQL);
    $Band_str = '';
    while($band_row = mysqli_fetch_assoc($band_rs)){
      if($Band_str) $Band_str .= ',';
      $Band_str .= $band_row['ID'];
    }*/
    $SQL = "SELECT `ID`, `RecordID`, `Note`, `NoteTypeID`, `UserID`, `DateTime` FROM BandGroup WHERE Note NOT LIKE 'SAM%' AND RecordID in($frm_selected_band_str)";
    update_itemGroup_to_new_project($NoteType_map_arr,'BandGroup',$SQL);
    
//======Process Exp and Sample Protocol===============================================================================
//------process Protocol table and update NoteTypeID for sample protocol in BandGroup table---------------------------
    $to_file .= "\n\nProcess Exp and Sample Protocol-----------------------";
    //copy sampel protocals
    $SQL = "select ID,Name,Type,Detail,UserID from Protocol where ProjectID='$frm_project_ID'";
    $protocol_rs = mysqli_query($prohits_link, $SQL);
    
    $protocol_old2new = array();
    while($protocol_row = mysqli_fetch_assoc($protocol_rs)){
      $old_protocol_ID = $protocol_row['ID']; 
      
      $SQL = "select ID from Protocol 
              where ProjectID='$frm_to_project_ID' 
              and Type='".$protocol_row['Type']."' 
              and Name='".mysqli_real_escape_string($prohits_link, $protocol_row['Name'])."'";
      
      $tmp_rs = mysqli_query($prohits_link, $SQL);
      if($new_row = mysqli_fetch_assoc($tmp_rs)){
        $new_protocol_ID = $new_row['ID'];
      }else{
        $SQL = "insert into Protocol set 
            Name='".mysqli_real_escape_string($prohits_link, $protocol_row['Name'])."',
            Type='".mysqli_real_escape_string($prohits_link, $protocol_row['Type'])."',
            ProjectID='".$frm_to_project_ID."',
            Detail='".mysqli_real_escape_string($prohits_link, $protocol_row['Detail'])."',
            Date=now(),
            UserID='".$protocol_row['UserID']."'";
        mysqli_query($prohits_link, $SQL);
        $to_file .= "\n$SQL";
        $new_protocol_ID = mysqli_insert_id($prohits_link);
      }
      
      if(preg_match("/^SAM/", $protocol_row['Type'], $matches)){
        $SQL = "select ID from Band where ID in ($frm_selected_band_str)";
        $band_rs = mysqli_query($prohits_link, $SQL);
        while($band_row = mysqli_fetch_assoc($band_rs)){
          $SQL = "update BandGroup set NoteTypeID='".$new_protocol_ID."' where NoteTypeID='".$old_protocol_ID."' AND RecordID='".$band_row['ID']."' AND Note LIKE 'SAM%'";
          mysqli_query($prohits_link, $SQL);
          $to_file .= "\n$SQL";
        }
      }else{
        $protocol_old2new[$old_protocol_ID] = $new_protocol_ID;
      }
    }
echo "<pre>";
echo "protocol_old2new<br>";      
print_r($protocol_old2new);      
echo "</pre>";
//==============Chang old ProtocolID to new ProtocolID ==================================================================================    
    $SQL = "select ID, GrowProtocol, IpProtocol, DigestProtocol, PeptideFrag from Experiment where ID in ($frm_selected_Exp_str)";
    $exp_rs = mysqli_query($prohits_link, $SQL);
    while($exp_row = mysqli_fetch_array($exp_rs)){
      $g_str = '';
      $i_str = '';
      $d_str = '';
      $p_str = '';

      if(!isset($ExpID_old_new_arr[$exp_row['ID']])) continue;    
      $new_Exp_ID = $ExpID_old_new_arr[$exp_row['ID']];
      
      $SQL = "update Experiment set ";
      if($exp_row['GrowProtocol']){
        $tmp_arr = explode(",", $exp_row['GrowProtocol']);
        if(count($tmp_arr)==2){
          if(isset($protocol_old2new[$tmp_arr[0]])){
            $g_str = $protocol_old2new[$tmp_arr[0]].",".$tmp_arr[1];
          }  
        }
      }
      if($exp_row['IpProtocol']){
        $tmp_arr = explode(",", $exp_row['IpProtocol']);
        if(count($tmp_arr)==2){
          if(isset($protocol_old2new[$tmp_arr[0]])){
            $i_str = $protocol_old2new[$tmp_arr[0]].",".$tmp_arr[1];
          }  
        }
      }
      if($exp_row['DigestProtocol']){
        $tmp_arr = explode(",", $exp_row['DigestProtocol']);
        if(count($tmp_arr)==2){
          if(isset($protocol_old2new[$tmp_arr[0]])){
            $d_str = $protocol_old2new[$tmp_arr[0]].",".$tmp_arr[1];
          }  
        }
      }
      if($exp_row['PeptideFrag']){
        $tmp_arr = explode(",", $exp_row['PeptideFrag']);
        if(count($tmp_arr)==2){
          if(isset($protocol_old2new[$tmp_arr[0]])){
            $p_str = $protocol_old2new[$tmp_arr[0]].",".$tmp_arr[1];
          }  
        }
      }
      $SQL .= "GrowProtocol='$g_str', IpProtocol='$i_str', DigestProtocol='$d_str', PeptideFrag='$p_str' where ID='".$new_Exp_ID."'";
      mysqli_query($prohits_link, $SQL);
      $to_file .= "\n$SQL";
    }
    
//change linked file-----------------------------------------------------------
    $to_file .= "\n\nProcess Raw files link-------------------------------";
    $SQL = "select ID, RawFile from Band where ID in ($frm_selected_band_str)";
    $band_rs = mysqli_query($prohits_link, $SQL);
    $managerDB = new mysqlDB(MANAGER_DB);
    
    while($band_row = mysqli_fetch_array($band_rs)){
      if($band_row['RawFile']){
        $raw_arr = explode(";", $band_row['RawFile']);
        foreach($raw_arr as $raw_file){
          $tmp_arr = explode(":", $raw_file);
          if(count($tmp_arr) == 2){
            $raw_file_ID = trim($tmp_arr[1]);
            $machine = trim($tmp_arr[0]);
            if(!$raw_file_ID or !$machine) continue;
            $SQL = "show tables like '".$machine."'";
            if(!mysqli_fetch_row(mysqli_query($msManager_link, $SQL))) continue;
            $SQL = "update $machine set ProjectID='$frm_to_project_ID' where ID='$raw_file_ID'";
            mysqli_query($msManager_link, $SQL);
            $to_file .= "\n$SQL";
            $SQL = "update $machine set ProjectID='$frm_to_project_ID' where RAW_ID='$raw_file_ID'";
            mysqli_query($msManager_link, $SQL);
            $to_file .= "\n$SQL";
          }
        }
      }
    }
//exit;    

    foreach($Band_BaitExpID_arr as $key => $val){
      $tmp_arr = explode(',',$val);
      if(!isset($BaitID_old_new_arr[$tmp_arr[0]])) continue;
      $BaitID = $BaitID_old_new_arr[$tmp_arr[0]];
      if(!isset($ExpID_old_new_arr[$tmp_arr[1]])) continue;
      $ExpID = $ExpID_old_new_arr[$tmp_arr[1]];
      $BandID = $key;
      $SQL = "update Band set 
            ProjectID='$frm_to_project_ID',
            BaitID='$BaitID',
            ExpID='$ExpID' 
            where ID='$BandID'";
      $to_file .= "\n$SQL";
      mysqli_query($prohits_link, $SQL);
    }
    
    foreach($Bait_Sample_ID_arr as $key => $val){
    
      $BaitID = $BaitID_old_new_arr[$key];
      $Band_str = implode(',',$val);
      $SQL = "update Hits set
            BaitID='$BaitID'
            where BandID IN ($Band_str)";
      $to_file .= "\n$SQL";
      mysqli_query($prohits_link, $SQL);
      
      $SQL = "update TppProtein set
            BaitID='$BaitID'
            where BandID IN ($Band_str)";
      $to_file .= "\n$SQL";
      mysqli_query($prohits_link, $SQL);
      
      $SQL = "update Hits_GeneLevel set
            BaitID='$BaitID'
            where BandID IN ($Band_str)";
      $to_file .= "\n$SQL";
      mysqli_query($prohits_link, $SQL);
    } 
       
    $fd = fopen($log_path, 'a+');
    fwrite($fd, "$to_file");
    fclose($fd);
    echo "<br>Log file path: $log_path";
  }
}

function insert_to_NoteType_table($NoteType_row,$frm_to_project_ID){
  global $prohits_link; 
  global $to_file;       
  $SQL = "insert into NoteType set 
          Name='".mysqli_real_escape_string($prohits_link, $NoteType_row['Name'])."',
          Type='".mysqli_real_escape_string($prohits_link, $NoteType_row['Type'])."',
          Description='".mysqli_real_escape_string($prohits_link, $NoteType_row['Description'])."',
          Icon='". $NoteType_row['Icon']."',
          ProjectID='".$frm_to_project_ID."',
          UserID='".$NoteType_row['UserID']."',
          Initial='".$NoteType_row['Initial']."'";
  mysqli_query($prohits_link, $SQL);
  $to_file .= "\n$SQL";
  return $new_NoteType_ID = mysqli_insert_id($prohits_link);
}
  
function update_itemGroup_to_new_project($NoteType_map_arr,$group_name,$SQL){    
  global $prohits_link;
  global $frm_to_project_ID;
  global $to_file;
  global $BaitID_old_new_arr;
  global $ExpID_old_new_arr;
  
  $BaitGroup_rs = mysqli_query($prohits_link, $SQL);   
  while($BaitGroup_row = mysqli_fetch_assoc($BaitGroup_rs)){
  
    $old_NoteType_ID = $BaitGroup_row['NoteTypeID'];
    $old_Record_ID = $BaitGroup_row['RecordID'];
    $index_ID = $BaitGroup_row['ID'];
    if(array_key_exists($old_NoteType_ID, $NoteType_map_arr)){
      if(is_array($NoteType_map_arr[$old_NoteType_ID])){
        $new_NoteType_ID = insert_to_NoteType_table($NoteType_map_arr[$old_NoteType_ID],$frm_to_project_ID);
        $NoteType_map_arr[$old_NoteType_ID] = $new_NoteType_ID;
      }else{
        $new_NoteType_ID = $NoteType_map_arr[$old_NoteType_ID];
      }
      if($group_name == 'BaitGroup' || $group_name == 'ExperimentGroup'){
        if($group_name == 'BaitGroup'){
          if(isset($BaitID_old_new_arr[$old_Record_ID])){
            $new_Record_ID = $BaitID_old_new_arr[$old_Record_ID];
          }else{
            continue;
          }
        }elseif($group_name == 'ExperimentGroup'){
          if(isset($ExpID_old_new_arr[$old_Record_ID])){
            $new_Record_ID = $ExpID_old_new_arr[$old_Record_ID];
          }else{
            continue;
          }
        }
        $SQL = "INSERT INTO $group_name SET 
                RecordID='".$new_Record_ID."',
                NoteTypeID='".$new_NoteType_ID."',
                Note='".mysqli_real_escape_string($prohits_link, $BaitGroup_row['Note'])."',
                UserID='".$BaitGroup_row['UserID']."',
                DateTime='".$BaitGroup_row['DateTime']."'";
      }else{   
        $SQL = "UPDATE $group_name SET NoteTypeID='".$new_NoteType_ID."' WHERE ID='".$index_ID."'";
      }
      mysqli_query($prohits_link, $SQL);
      $to_file .= "\n$SQL";
    }
  } 
}  
?>