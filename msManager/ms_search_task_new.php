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

$error_msg = '';
$theTaskID = '';
$frm_TaskName = '';
$frm_status = '';
$frm_startTime = '';
$task_has_results = '';
$frm_is_SWATH_file = '';
$frm_swath_app = '';
$user_task_record = array();
$import_color = '#c5bde1';
$frm_import_task_ID = '';

$frm_i_Mascot = '';
$frm_i_GPM = '';
$frm_i_COMET = '';
$frm_i_MSFragger = '';
$frm_i_MSGFPL = '';
$frm_i_MSGFDB = '';
$frm_i_SEQUEST = '';
$frm_db = '';
$frm_enzyme = '';

$task_has_results = '';

$gpm_dbs = array();
$searchAll_parm_dir = "./autoSearch/";

$frm_SearchAllSetID = '';
$frm_SearchAllSetName = '';
$enPara_name_arr = array();

$frm_DIAUmpireSetID = '';
$frm_DIAUmpireSetName = '';
$DIAUmpire_name_arr = array();
$DIAUmpire_set_arr = array();

$frm_MSPLITSetID = '';
$frm_MSPLITSetName = '';
$frm_use_msplit_DDA_lib = '';
$MSPLIT_name_arr = array();
$frm_MSPLIT_lib = '';
$MSPLIT_set_arr = array();

$frm_ConverterSetID = '';
$frm_ConverterSetName = '';
$Converter_name_arr = array();
$frm_PROTEOWIZARD_par_str = '';
$converter_set_arr = array();

$file_id_str = '';
$myaction = '';
$frm_PROTEOWIZARD_par_str = '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g';
$warning_msg = '';
$unsearched_file_exists = false;

$frm_runTPP = '';
$frm_tppSetID = '';
$frm_tppSetName = '';
$tppTaskID = '';
$frm_tppTaskName = '';
$TPP_name_arr = array();
$tpp_set_arr = array();

$frm_file_id_str = '';
$total_raw_file = '0';
$frm_DataFileFormat = '';
$RAW_FILES ='';
$id_have_results = array();
$foldersRD = array();


$frm_allDDAFileAdded = '';
$file_option_output_arr = array();

$task_record = '';
$unsearched_file_exists = false;
$hide_db_arr = array();

$frm_fixed_mod_str = '';
$frm_variable_mod_str = '';
$frm_all_mod_str = '';

$error_msg = '';

$running_records = '';

$id_no_results = array();
$id_have_results = array();

$is_defult_set = '';   
  
include("./ms_search_header.php");
 
include("msManager/tppTask/tpp_task_shell_fun.inc.php");
include("msManager/autoSearch/auto_search_mascot.inc.php");
include("msManager/is_dir_file.inc.php");

$gpm_in_prohits = is_in_local_server('GPM');
$tpp_in_prohits = is_in_local_server('TPP');
$comet_in_prohits = is_in_local_server('COMET');
$msgfpl_in_prohits = is_in_local_server('MSGFPL');
$diaumpire_in_prohits = is_in_local_server('DIAUmpire');
$msplit_in_prohits = is_in_local_server('MSPLIT');
$msfragger_in_prohits = is_in_local_server('MSFragger');

/*
echo "<pre>";
print_r($request_arr);
echo "</pre>";
*/

$used_sets_arr = array('SearchEngines'=>array(),'Converter'=>array(), 'Database'=>array(), 'DIAUmpire'=>array(),'MSPLIT'=>array(),'MSPLIT_LIB'=>array(), 'TPP'=>array());
if(!$theTaskID){
  //get user previous parameter sets
  $used_sets_arr = get_used_para_names($USER->ID, $tableSearchTasks, $tableTppTasks);
}

if($theTaskID or $frm_import_task_ID){
    if($theTaskID){
      $where = "ID='$theTaskID'  and ". $where_project;
    }else{
      $where = "ID='$frm_import_task_ID'  and ". $where_project;
    }
    $SQL = "SELECT 
          ID, 
          PlateID,
          DataFileFormat, 
          SearchEngines, 
          Parameters, 
          DIAUmpire_parameters,
          TaskName, 
          LCQfilter, 
          Schedule, 
          StartTime,
          RunTPP, 
          Status, 
          UserID,
          ProjectID,
          AutoAddFile
          FROM $tableSearchTasks T where  $where";
  
  $task_record = $managerDB->fetch($SQL);
  
  if($task_record){
    $in_folderIDs = $task_record['PlateID'];
    $frm_PROTEOWIZARD_par_str = $task_record['LCQfilter'];
    
    $SQL = "select ID, FileName, ProhitsID, ProjectID, Date from $table T where " . $where_project . "  and ID in($in_folderIDs)";
    $foldersRD = $managerDB->fetchAll($SQL);
    if(!$foldersRD) fatalError("no permission for the plate", __LINE__);
    //only get raw files with theTaskID
    $SQL = "SELECT WellID, DataFiles, SearchEngines FROM $tableSearchResults where TaskID='$theTaskID'";
    $file_records = $managerDB->fetchAll($SQL);
    //$file_id_str = '';
    $tmp_wellID_arr = array();
     
    foreach($file_records as $file_records_val){
      if($file_records_val['SearchEngines']== 'MSPLIT_DDA'){
        if($myaction != 'save' or !$perm_insert){
          $theFieldName = $file_records_val['WellID']. "_is_DDA";
           
          $$theFieldName = 1;
        }
      }
      if(!array_key_exists($file_records_val['WellID'], $tmp_wellID_arr)){
        $tmp_wellID_arr[$file_records_val['WellID']] = trim($file_records_val['DataFiles']);
      }else{
        if(!$tmp_wellID_arr[$file_records_val['WellID']]){
          $tmp_wellID_arr[$file_records_val['WellID']] = trim($file_records_val['DataFiles']);
        }
      }
    }
    foreach($tmp_wellID_arr as $tmp_wellID_key => $tmp_wellID_val){
      if($file_id_str) $file_id_str .= ",";
      $file_id_str .= $tmp_wellID_key;
      if($tmp_wellID_val){
        $task_has_results = true;
        array_push($id_have_results,$tmp_wellID_key);
      }else{
        array_push($id_no_results,$tmp_wellID_key);
      } 
    }
    if($file_id_str){
      
      $SQL = "SELECT FileName, FolderID, ID from $table where ID in($file_id_str) order by FolderID, FileName";
      $file_option_output_arr = $file_names = $managerDB->fetchAll($SQL);      
      $total_raw_file = count($file_option_output_arr);
    }    
    if($task_record['RunTPP']){
      $SQL = "SELECT `SearchTaskID`,`ParamSetName`,`TaskName`,`Status` FROM `$tableTppTasks` WHERE ID='".$task_record['RunTPP']."'";
      $tppTask_record = $managerDB->fetch($SQL);
      if($tppTask_record){
        $frm_runTPP = 'yes';
         
        $frm_tppSetName = $tppTask_record['ParamSetName'];
         
        if($theTaskID){
          $frm_tppTaskName = $tppTask_record['TaskName'];
          $frm_tppStatus = $tppTask_record['Status'];
          $tppTaskID = $task_record['RunTPP'];
        }
      }
    }
  }else{
    $myaction = 'addnew';
  }  
  $Engines_num = 0;
  $engine_arr = explode(";",$task_record['SearchEngines']);  
  if(preg_match('/^iProphet/', $task_record['SearchEngines'], $matches)){
    
    $frm_Run_iProphet = "y";
    $iProphet_item = array_shift($engine_arr);
    $tmp_set = preg_split("/[=:]+/", $iProphet_item);
    $frm_db = $tmp_set[2];
    if($tmp_set[0] == "iProphet"){
      $frm_SearchAllSetName = $tmp_set[1];
      if(strstr($frm_SearchAllSetName, 'default')){
        $frm_set_type = "default";
      }else{
        $frm_set_type = "customized";
      }
    }
    $iProphet_item = array_shift($engine_arr);
    $tmp_search_engines = explode(":", $iProphet_item);
    foreach($tmp_search_engines as $tmp_search_engines_name){
      if($tmp_search_engines_name == "Mascot"){
        $frm_i_Mascot = "Mascot";
        $Engines_num++;
      }else if($tmp_search_engines_name == "SEQUEST"){
        $frm_i_SEQUEST = "SEQUEST";
        $Engines_num++;
      }else if($tmp_search_engines_name == "GPM"){
        $frm_i_GPM = "GPM";
        $Engines_num++;
      }else if($tmp_search_engines_name == "COMET"){
        $frm_i_COMET = "COMET";
        $Engines_num++;
      }else if($tmp_search_engines_name == "MSFragger"){
        $frm_i_MSFragger = "MSFragger";
        $Engines_num++;
      }else if($tmp_search_engines_name == "MSGFPL"){
        $frm_i_MSGFPL = "MSGFPL";
        $Engines_num++;
      }else if($tmp_search_engines_name == "MSGFDB"){
        $frm_i_MSGFPL = "MSGFDB";
        $Engines_num++;
      }
    }
  }else{
    $frm_Run_iProphet = "";
  }
   
  foreach($engine_arr as $key => $tmp_en){
    $tmp_set = explode("=", $tmp_en);
    if($frm_Run_iProphet){
      if($tmp_set[0] == "Converter" and count($tmp_set)> 1){
        $frm_ConverterSetName = $tmp_set[1];
      }
    }else{
      if($tmp_set[0] == "Mascot"){
        $frm_i_Mascot = "Mascot";
        if(count($tmp_set)> 1){
          $frm_SearchAllSetName = $tmp_set[1];
        }
        $Engines_num++;
      }else if($tmp_set[0] == "SEQUEST"){
        $frm_i_SEQUEST = "SEQUEST";
        if(count($tmp_set)> 1){
          $frm_SearchAllSetName = $tmp_set[1];
        }
        $Engines_num++;
      }else if($tmp_set[0] == "GPM"){
        $frm_i_GPM = "GPM";
        if(count($tmp_set)> 1){
          $frm_SearchAllSetName = $tmp_set[1];
        }
        $Engines_num++;
      }else if($tmp_set[0] == "COMET"){
        $frm_i_COMET = "COMET";
        if(count($tmp_set)> 1){
          $frm_SearchAllSetName = $tmp_set[1];
        }
        $Engines_num++;
      }else if($tmp_set[0] == "MSFragger"){
        $frm_i_MSFragger = "MSFragger";
        if(count($tmp_set)> 1){
          $frm_SearchAllSetName = $tmp_set[1];
        }
        $Engines_num++;
      }else if($tmp_set[0] == "MSGFPL"){
        $frm_i_MSGFPL = "MSGFPL";
        if(count($tmp_set)> 1){
          $frm_SearchAllSetName = $tmp_set[1];
        }
        $Engines_num++;
      }else if($tmp_set[0] == "MSGFDB"){
        $frm_i_MSGFDB = "MSGFDB";
        if(count($tmp_set)> 1){
          $frm_SearchAllSetName = $tmp_set[1];
        }
        $Engines_num++;
      }else if($tmp_set[0] == "Converter" and count($tmp_set)> 1){
        $frm_ConverterSetName = $tmp_set[1];
      }else if($tmp_set[0] == "Database"){
        $frm_db = $tmp_set[1];
      }else if($tmp_set[0] == "DIAUmpire" and count($tmp_set)> 1){
        $frm_is_SWATH_file = 1;
        $frm_DIAUmpireSetName = $tmp_set[1];
        $frm_swath_app ='MSUmpire';
      }else if($tmp_set[0] == "MSPLIT" and count($tmp_set)> 1){
        $frm_is_SWATH_file = 1;
        $frm_MSPLITSetName = $tmp_set[1];
        $frm_swath_app ='MSPLIT';
        $task_has_results = true;
      }else if($tmp_set[0] == "MSPLIT_LIB" and count($tmp_set)> 1){
        $frm_MSPLIT_lib = $tmp_set[1];;
        $frm_use_msplit_DDA_lib = '1';
      }
    }
  }
  
  if($theTaskID){
    $frm_TaskName = $task_record['TaskName']; 
    $frm_PlateIDs = $task_record['PlateID']; 
    $frm_startTime = $task_record['StartTime'];
    $frm_DataFileFormat = $task_record['DataFileFormat']; 
  }
  if($myaction != 'save' && $myaction != 'refresh'){
    $task_para_arr = explode("\n", $task_record['Parameters']);
    foreach($task_para_arr as $para_line){
      $line_var_arr = explode("===", trim($para_line));
      if(count($line_var_arr) > 1){
       
        $$line_var_arr[0] = $line_var_arr[1];
      }
    }
    
    $frm_status = $task_record['Status'];
    $frm_PROTEOWIZARD_par_str = $task_record['LCQfilter'];
     
    if(strpos($task_record['DIAUmpire_parameters'], "allDDAFileAdded")!== false){
      $frm_allDDAFileAdded = 'Yes';
    }
     
  }
  if($frm_status == 'Running'){
    if($frm_swath_app == 'MSPLIT'){
    
    }else if(task_is_running($table,  $theTaskID)){
      $bgcolor = "lightgreen";
    }else{
      $bgcolor = "yellow";
      $frm_status  = 'Error';
      $warning_msg = " <font color=red><b> (The task was set to run. But it is not running. Stop the task or run it again.)</b></font>";
    }
  }
}


if(!$theTaskID){
   //get all user tasks
  $SQL = "SELECT ID, SearchEngines, TaskName FROM $tableSearchTasks where UserID='".$USER->ID."' order by ID desc";
  $user_task_record = $managerDB->fetchAll($SQL);
} 

$tmp_processID = '';
$SQL = "select ID, ProcessID from $tableSearchTasks  where Status='Running' and UserID='".$USER->ID."'";
$running_records = $managerDB->fetchAll($SQL, 'col');
if($running_records and $theTaskID){
  if($tmp_arr = array_keys($running_records['ID'], $theTaskID)){
    $tmp_processID = $running_records['ProcessID'][$tmp_arr[0]];
    if(count($running_records['ID']) == 1) $running_records = array();
  }
}
 
$frm_file_id_str = preg_replace("/^\W+|\W+$/", "", $frm_file_id_str);

$umpire_running_file_ID_arr = array();
$umpire_running_tasks_arr = array();
if($frm_file_id_str and $myaction != 'modify'){
  //get running umpire file ids
  $SQL = "select ID, SearchEngines from $tableSearchTasks where Status='Running' or Status='Waiting'";
  $running_tasks = $managerDB->fetchAll($SQL);
  if($running_tasks){
    foreach($running_tasks as $theRunningTask){
      if(preg_match("/DIAUmpire(=|;)/",  $theRunningTask['SearchEngines'], $matches)){
        //echo "==".$theRunningTask['SearchEngines']."==<br>";
        $SQL = "select DISTINCT WellID from $tableSearchResults where WellID in(".$frm_file_id_str.") and TaskID=".$theRunningTask['ID'] ." order by WellID";
        $se_results = $managerDB->fetchAll($SQL);
        if($se_results){
          $umpire_running_tasks_arr[] = $theRunningTask['ID'];
          foreach($se_results as $theSE){
            $umpire_running_file_ID_arr[] = $theSE['WellID'];
          }
        }
      }
      
    }
  }
   

  $SQL = "select ID, FileName, FolderID, Date from $table T where ID in(".$frm_file_id_str.") order by FolderID, FileName";
  $file_option_output_arr = $managerDB->fetchAll($SQL);
  $folder_IdArr = array();
  for($i = 0; $i < count($file_option_output_arr); $i++){
    if(!in_array($file_option_output_arr[$i]['FolderID'], $folder_IdArr)) array_push($folder_IdArr, $file_option_output_arr[$i]['FolderID']);
  }
  sort($folder_IdArr);
  $in_folderIDs = implode(",", $folder_IdArr);
  $SQL = "select ID, FileName, ProhitsID, ProjectID, Date from $table T where ID in($in_folderIDs)";
  $foldersRD = $managerDB->fetchAll($SQL);
  foreach($foldersRD as $tmp_folder){
    if($frm_ProjectID = $tmp_folder['ProjectID'])break;
  }
  if($in_folderIDs){  
    //---------------------------------------
    if($myaction == 'save' and $perm_insert){
    //---------------------------------------
      //save task
      if($tmp_processID){
        //kill the running task if executed by apache user. it can not be killed if it is executed by cronjob.
        $file = "http://" .$storage_ip . dirname($_SERVER['PHP_SELF']) . "/autoSearch/auto_search_table_shell.php?tableName=".$table."&kill=". $tmp_processID."&SID=".session_id();
        $handle = fopen($file, "r");
      }
      
      $status = 'Running';
      if($USER->Type != 'Admin' and $running_records){
        $status = 'Waiting';
      }
      
      $tmp_Parameters = '';
      $tmp_Engines = '';
      $database_str = '';
      
      if($task_record){
        if($perm_modify){
          $SQL = "$tableSearchTasks SET 
            PlateID='$in_folderIDs',
            Status='$status', 
            ProjectID='$frm_ProjectID'";
            //UserID='". $USER->ID."'";
            
          if($frm_is_SWATH_file){
            $tmp_para = preg_replace("/allDDAFileAdded:\w*;/", '', $task_record['DIAUmpire_parameters']);
            if($frm_allDDAFileAdded){
              $tmp_para .= "allDDAFileAdded:Yes;";
            }
            $SQL .= ", DIAUmpire_parameters='$tmp_para'";
          }
          
          $SQL = "UPDATE " . $SQL ." where ID='$theTaskID'";
          
          $managerDB->update($SQL);
          //remove empty results
          if($frm_swath_app !='MSPLIT'){
            $SQL = "SELECT `WellID`, `SearchEngines`  from " . $tableSearchResults . " where TaskID='$theTaskID'  and DataFiles=''";
            $all_empty = $managerDB->fetchAll($SQL);
            $theEmpty_ID_engine_sgtr = '';
            foreach($all_empty as $theRD){
              if($theEmpty_ID_engine_sgtr) $theEmpty_ID_engine_sgtr .= ",";
              $theEmpty_ID_engine_sgtr .= $theRD['WellID'].":".$theRD['SearchEngines'];
            }
            $theEmpty_ID_engine_sgtr .= ' ';
            $SQL = "delete from " . $tableSearchResults . " where TaskID='$theTaskID'  and DataFiles=''";

            $managerDB->execute($SQL); 
            removeEmptyTppResults($theTaskID, $theEmpty_ID_engine_sgtr);
          }
        }
      }else{        
        if($frm_SearchAllSetID){
          $SearchAllSet_arr = get_search_parameters($table, $frm_SearchAllSetID);
          $tmp_para_arr = explode("\n",$SearchAllSet_arr['Parameters']);
          foreach($tmp_para_arr as $tmp_para_val){
            if(!trim($tmp_para_val)) continue;
            $tmp_para_arr2 = explode("===",$tmp_para_val);
            if(count($tmp_para_arr2) == 2){
              $allPara_arr[$tmp_para_arr2[0]] = $tmp_para_arr2[1];
            }
          }
        }
      
          
        $Engines_num = 0;
        $database_str = ";Database=$frm_db";
        //add db and modifications to params  
        if($frm_i_Mascot){
          if(isset($allPara_arr['MASCOT'])){
            $Engines_num++;
            $allPara_arr['MASCOT'] = str_replace(";;", ";", $allPara_arr['MASCOT']);
            $allPara_arr['MASCOT'] = preg_replace("/;DB=[^;]*/", ";DB=$frm_db", $allPara_arr['MASCOT']);
            //add modifications
            //echo $allPara_arr['MASCOT'];
            $allPara_arr['MASCOT'] = add_modifications("Mascot", $frm_fixed_mod_str, $frm_variable_mod_str, $allPara_arr['MASCOT']);
            $tmp_Engines = $frm_i_Mascot;
            $tmp_Parameters = $frm_i_Mascot . "===";
            $tmp_Parameters .=  $allPara_arr['MASCOT'];
          }
        }
        if($frm_i_GPM){
          $Engines_num++;
          if(isset($allPara_arr['GPM'])){
            if($tmp_Engines) $tmp_Engines .=";";
            $tmp_Engines .= $frm_i_GPM;
            if($tmp_Parameters) $tmp_Parameters .="\n";
            $tmp_Parameters .= $frm_i_GPM . "===";
             
            $allPara_arr['GPM'] = substr($allPara_arr['GPM'], strpos($allPara_arr['GPM'], ";;")+2);
            $allPara_arr['GPM'] = preg_replace("/protein__taxon=[^;]*/", "protein__taxon=$frm_db", $allPara_arr['GPM']);
            $allPara_arr['GPM'] = str_replace(";;", ";", $allPara_arr['GPM']);
            $allPara_arr['GPM'] = add_modifications("GPM", $frm_fixed_mod_str, $frm_variable_mod_str, $allPara_arr['GPM']);
            $tmp_Parameters .= $allPara_arr['GPM'];
          }
        }
        if($frm_i_SEQUEST){
          $Engines_num++;
          if(isset($allPara_arr['SEQUEST'])){
            if($tmp_Engines) $tmp_Engines .=";";
            $tmp_Engines .= $frm_i_SEQUEST;
            if($tmp_Parameters) $tmp_Parameters .="\n";
            $tmp_Parameters .= $frm_i_SEQUEST . "===";
            $tmp_Parameters .= $allPara_arr['SEQUEST'];
          }
        }
        if($frm_i_COMET){
          $Engines_num++;
          
          if(isset($allPara_arr['COMET'])){
            if($tmp_Engines) $tmp_Engines .=";";
            $tmp_Engines .= $frm_i_COMET;
            if($tmp_Parameters) $tmp_Parameters .="\n";
            $tmp_Parameters .= $frm_i_COMET . "===";
            $allPara_arr['COMET'] = preg_replace("/database_name=[^;]*/", "database_name=$frm_db", $allPara_arr['COMET']);
            $allPara_arr['COMET'] = add_modifications("COMET", $frm_fixed_mod_str, $frm_variable_mod_str, $allPara_arr['COMET']);
            $tmp_Parameters .= $allPara_arr['COMET'];
          }
        }
        if($frm_i_MSFragger){
          $Engines_num++;
          
          if(isset($allPara_arr['MSFRAGGER'])){
            if($tmp_Engines) $tmp_Engines .=";";
            $tmp_Engines .= $frm_i_MSFragger;
            if($tmp_Parameters) $tmp_Parameters .="\n";
            $tmp_Parameters .= $frm_i_MSFragger . "===";
            $allPara_arr['MSFRAGGER'] = preg_replace("/database_name=[^;]*/", "database_name=$frm_db", $allPara_arr['MSFRAGGER']);
            $allPara_arr['MSFRAGGER'] = add_modifications("MSFragger", $frm_fixed_mod_str, $frm_variable_mod_str, $allPara_arr['MSFRAGGER']);
            $tmp_Parameters .= $allPara_arr['MSFRAGGER'];
          }
        }
        if($frm_i_MSGFPL){
          $Engines_num++;
          if(isset($allPara_arr['MSGFPL'])){
            if($tmp_Engines) $tmp_Engines .=";";
            $tmp_Engines .= $frm_i_MSGFPL;
            if($tmp_Parameters) $tmp_Parameters .="\n";
            $tmp_Parameters .= $frm_i_MSGFPL . "===";
            $allPara_arr['MSGFPL'] = preg_replace("/database_name=[^;]*/", "database_name=$frm_db", $allPara_arr['MSGFPL']);
            $allPara_arr['MSGFPL'] = add_modifications("MSGFPL", $frm_fixed_mod_str, $frm_variable_mod_str, $allPara_arr['MSGFPL']);
            $tmp_Parameters .= $allPara_arr['MSGFPL'];
          }
        }
        if($frm_SearchAllSetID){
          $tmp_Engines .= "=".$SearchAllSet_arr['Name'];
        }
        if($frm_is_SWATH_file and $frm_swath_app == 'MSPLIT'){
          $tmp_Engines = '';
          $tmp_Parameters = '';
          //if($frm_use_msplit_DDA_lib){
            $tmp_Engines = "MSPLIT_LIB=" . $frm_MSPLIT_lib;
          //}
          if(isset($allPara_arr['MSGFDB']) and $frm_i_MSGFDB){
             if($tmp_Engines) $tmp_Engines .= ";";
             $tmp_Engines .= $frm_i_MSGFDB."=".$SearchAllSet_arr['Name'];
             $tmp_Parameters = $frm_i_MSGFDB . "===";
             $allPara_arr['MSGFDB'] = preg_replace("/database_name=[^;]*/", "database_name=$frm_db", $allPara_arr['MSGFDB']);
             $allPara_arr['MSGFDB'] = add_modifications("MSGFPL", $frm_fixed_mod_str, $frm_variable_mod_str, $allPara_arr['MSGFDB']);
             $tmp_Parameters .= $allPara_arr['MSGFDB'];
          }
           
        }
        
        if($frm_ConverterSetID){
          $theConverterSet_arr = get_search_parameters('Converter', $frm_ConverterSetID);
          $frm_PROTEOWIZARD_par_str = $theConverterSet_arr['Parameters'];
          $tmp_name = preg_replace("/;;.+$/", '', $theConverterSet_arr['Name']);
          $tmp_Engines .= ";Converter=". $tmp_name;
        }
        
        $tmp_DIAparameters = '';
        if($frm_is_SWATH_file){
          if($frm_swath_app == 'MSUmpire'){
            if($frm_DIAUmpireSetID){
              $theDIAUmpireSet_arr = get_search_parameters('DIAUmpire',  $frm_DIAUmpireSetID);
              $tmp_Engines .= ";DIAUmpire=". $theDIAUmpireSet_arr['Name'];
              $tmp_DIAparameters = $theDIAUmpireSet_arr['Parameters'];
            }
          }else{
            if($frm_MSPLITSetID or $theTaskID){
              //$theMSGFDBSet_arr = get_search_parameters('MSGFDB',  $frm_MSGFDBSetID);
              $theMSPLITSet_arr = get_search_parameters('MSPLIT',  $frm_MSPLITSetID);
              $tmp_Engines .= ";MSPLIT=". $theMSPLITSet_arr['Name'];
              $tmp_Parameters .= "\nMSPLIT===". preg_replace("/\n|\r\n/", ",", $theMSPLITSet_arr['Parameters']);
            }
          }
          if($frm_allDDAFileAdded){
            $tmp_DIAparameters .= "allDDAFileAdded:Yes;";
          }
        }
        
        $tmp_Parameters .= "\nfrm_fixed_mod_str===$frm_fixed_mod_str";
        $tmp_Parameters .= "\nfrm_variable_mod_str===$frm_variable_mod_str";
        
        $frm_TaskName = mysqli_escape_string($managerDB->link, $frm_TaskName);
        $SQL = "$tableSearchTasks SET 
            PlateID='$in_folderIDs',
            Status='$status', 
            ProjectID='$frm_ProjectID', 
            UserID='". $USER->ID."'";
        $SQL .= ",
          SearchEngines='$tmp_Engines$database_str', 
          Parameters='$tmp_Parameters', 
          TaskName='$frm_TaskName', 
          LCQfilter='$frm_PROTEOWIZARD_par_str'";
        if($frm_is_SWATH_file){
           $SQL .=",
           DIAUmpire_parameters='$tmp_DIAparameters'";
        }
        $SQL ="INSERT INTO ". $SQL;
        
        $theTaskID = $managerDB->insert($SQL);
      }
      if($theTaskID){
        $comet_tppID_str = '';
        $MSFragger_tppID_str = '';
        $msgfpl_tppID_str = '';
        $tmp_ID = '';
        $frm_tppID_str = '';
        for($i = 0; $i < count($file_option_output_arr); $i++){
          $tmp_id = $file_option_output_arr[$i]['ID'];
          $tmp_name = $file_option_output_arr[$i]['FileName'];
          
          $iProphet_tpp_flag = 0;
          
          $SQL ="INSERT INTO $tableSearchResults SET 
            WellID='$tmp_id', 
            TaskID='$theTaskID', 
            SearchEngines=";
          if($frm_is_SWATH_file and $frm_swath_app == 'MSPLIT'){
            $field_name = $file_option_output_arr[$i]['ID']."_is_DDA";
            //$is_DDA = (isset($$field_name) and !$frm_use_msplit_DDA_lib)?"_DDA":"";
            $is_DDA = (isset($$field_name) and $$field_name)?"_DDA":"";
            $managerDB->insert($SQL . "'MSPLIT".$is_DDA."'");
            continue;
          }
          if($frm_i_Mascot){
            $tmp_rt = $managerDB->insert($SQL . "'Mascot'");
            if($tmp_rt === 0){
              $iProphet_tpp_flag = 1;
              if($frm_tppID_str) $frm_tppID_str .= ',';
              $frm_tppID_str .='Mascot'.$tmp_id;
            }
          }
          if($frm_i_GPM){
            $tmp_rt = $managerDB->insert($SQL . "'GPM'");
            if($tmp_rt === 0){
              $iProphet_tpp_flag = 1;
              if($frm_tppID_str) $frm_tppID_str .= ',';
              $frm_tppID_str .='GPM'.$tmp_id;
            }
          }
          if($frm_i_SEQUEST){
            $tmp_rt = $managerDB->insert($SQL . "'SEQUEST'");
            if($tmp_rt === 0){
              $iProphet_tpp_flag = 1;
              if($frm_tppID_str) $frm_tppID_str .= ',';
              $frm_tppID_str .='SEQUEST'.$tmp_id;
            }
          }
          if($frm_i_COMET){
            $tmp_rt = $managerDB->insert($SQL . "'COMET'");
            if($tmp_rt === 0){
              $iProphet_tpp_flag = 1;
              if($frm_tppID_str) $frm_tppID_str .= ',';
              $frm_tppID_str .='COMET'.$tmp_id;
              $comet_tppID_str .= ',COMET'.$tmp_id;
            }
          }
          if($frm_i_MSFragger){
            $tmp_rt = $managerDB->insert($SQL . "'MSFragger'");
            if($tmp_rt === 0){
              $iProphet_tpp_flag = 1;
              if($frm_tppID_str) $frm_tppID_str .= ',';
              $frm_tppID_str .='MSFragger'.$tmp_id;
              $MSFragger_tppID_str .= ',MSFragger'.$tmp_id;
            }
          }
          if($frm_i_MSGFPL){
            $tmp_rt = $managerDB->insert($SQL . "'MSGFPL'");
            if($tmp_rt === 0){
              $iProphet_tpp_flag = 1;
              if($frm_tppID_str) $frm_tppID_str .= ',';
              $frm_tppID_str .='MSGFPL'.$tmp_id;
              $msgfpl_tppID_str .= ',MSGFPL'.$tmp_id;
            }
          }
           
          if($Engines_num > 1 && $iProphet_tpp_flag){
            if($frm_tppID_str) $frm_tppID_str .= ',';
            $frm_tppID_str .='iProphet'.$tmp_id;
          }
          $total_raw_file++;
        }
        
        $SQL = "select T.FolderID from $table T, $tableSearchResults R where T.ID=R.WellID and R.TaskID='".$theTaskID."'  group by T.FolderID order by T.FolderID";
         
        $folder_rds = $managerDB->fetchAll($SQL);
        $in_folderIDs = '';
        foreach($folder_rds as $tmp_arr){
          $in_folderIDs .=$tmp_arr['FolderID'].",";
        }
        $in_folderIDs = preg_replace("/\W+$/", "", $in_folderIDs);
        
        $SQL = "SELECT ID, RunTPP, Status, ProjectID FROM $tableSearchTasks where  ID='$theTaskID'";
        $task_tmp_rd = $managerDB->fetch($SQL);        
        $tppTaskID = $task_tmp_rd['RunTPP'];
         
        if($frm_runTPP or $tppTaskID or ($frm_is_SWATH_file and $frm_swath_app == 'MSUmpire')){
          $tppTaskID = saveTppTask($task_tmp_rd, $frm_tppSetID, $frm_tppTaskName, $frm_tppID_str, '', $tppTaskID);          
        }
        
        if($tppTaskID != $task_tmp_rd['RunTPP']){
          $SQL = "UPDATE ". $tableSearchTasks ." SET PlateID='". $in_folderIDs ."', RunTPP='".$tppTaskID."'";
          $SQL .= " where ID='$theTaskID'";
          $managerDB->update($SQL);
        }
        $msg = ''; 
        if($myaction == 'save' and $status == 'Running'){
          $msg = send_task_to_shell($table, $perm_insert, $theTaskID );
        }else{
          $msg = "This task has been set to task queue";
        }
        echo "<script language='javascript'>\n";
        echo "window.location='./ms_search_task_view.php?table=$table&theTaskID=$theTaskID&msg=".urlencode($msg)."';\n";
        echo "</script>\n";
        exit;
      }
    }
  }
}

 
//get databases.-------------------------------------------------------------------------- 
$SQL = "select Parameters from SearchParameter where Type='Database'";
$Paras_arr = $managerDB->fetch($SQL);
if($Paras_arr){
  $hide_db_arr = explode("\n", $Paras_arr['Parameters']);
}
$gpm_dbs = get_gpm_db_arr($hide_db_arr);
 
 
//get default mods------------------------------------------------------------------------
if($myaction == 'refresh' or $myaction == 'modify' ){
  $default_fixed_mod_arr = explode(";;", $frm_fixed_mod_str);
  $default_variable_mod_arr = explode(";;", $frm_variable_mod_str);
  $default_other_mod_arr = explode(";;", $frm_all_mod_str);
}else{
  $SQL = "select ID, Name, User, ProjectID, Parameters from SearchParameter where Type='Modifications' and User='0'";
  $mod_param_rd = $managerDB->fetch($SQL);
  $SQL = "select ID, Name, User, ProjectID, Parameters from SearchParameter where Type='Modifications' and User='".$USER->ID."'";
  $user_mod_param_rd = $managerDB->fetch($SQL);
  
  $default_fixed_mod_arr = array();
  $default_variable_mod_arr = array();
  $default_other_mod_arr = array();
  if(!$mod_param_rd){
    $error_msg = "Error: Prohits admin has not set default modifications.";
  }else{
    $thePara = $mod_param_rd['Parameters'];
    $lines = explode("\n", $thePara);
    foreach($lines as $line){
      if(strpos($line, "Fixed=") === 0){
        $line = str_replace("Fixed=", '', $line);
        $default_fixed_mod_arr = explode(";;", $line);
      }else if(strpos($line, "Variable=") === 0){
        $line = str_replace("Variable=", '', $line);
        $default_variable_mod_arr = explode(";;", $line);
      }else if(strpos($line, "Other=") === 0){
        $line = str_replace("Other=", '', $line);
        $default_other_mod_arr = explode(";;", $line);
      }
    }
  }
  if($user_mod_param_rd){
    $user_mod_arr = explode(";;", $user_mod_param_rd['Parameters']);
    $default_other_mod_arr = array_merge($default_other_mod_arr, $user_mod_arr);
  }
  if($frm_import_task_ID){
    $default_other_mod_arr = array_merge($default_other_mod_arr, $default_fixed_mod_arr, $default_variable_mod_arr);
    $default_fixed_mod_arr = explode(";;", $frm_fixed_mod_str);
    $default_variable_mod_arr = explode(";;", $frm_variable_mod_str);
    
    $default_other_mod_arr = array_diff($default_other_mod_arr, $default_fixed_mod_arr, $default_variable_mod_arr);
  }
}

//$converter_set_arr = display_options($frm_ConverterSetID,$frm_ConverterSetName,$Converter_name_arr,'Converter');
//$tpp_set_arr = display_options($frm_tppSetID,$frm_tppSetName,$TPP_name_arr,'TPP');
if((!$frm_tppSetName || !$frm_tppTaskName) && ($tppTaskID || $frm_import_task_ID)){
  $tableTppTasks = $table . "tppTasks";
  $SQL = "SELECT `ParamSetName`,`TaskName` FROM $tableTppTasks WHERE `ID`='$tppTaskID' || `ID`='$frm_import_task_ID'";
  $tppTaskID_arr = $managerDB->fetch($SQL);
  if($tppTaskID_arr){
    if(!$frm_tppSetName) $frm_tppSetName = $tppTaskID_arr['ParamSetName'];
    if(!$frm_tppTaskName) $frm_tppTaskName = $tppTaskID_arr['TaskName'];
  }
}

?>
<style type="text/css">
.en{background-color:#4b9cd8; margin: 0 0;  padding: 4px 8px;}
.sel_wth{width:240px;}
.mod{background-color:#50c5a5; margin: 0 0; padding: 8px 20px; width: 80%;}
 
.img_box {
    border: 1px solid <?php echo $menu_color;?>;
    border-radius: 0.5em;
    display: block;
    float: left;
    height: auto;
    margin: 4px 4px 4px 4px;
    position: relative;
    width: 98%;
    text-align: center;
    padding: 0px 0px
}
.coWhite { background-color: #fff;}
.coEn{ background-color: #dfe2f7;} 
.coUmpire{ background-color: #dfe2f7;}
.coData{background-color: #dfe2f7;}

 
</style>
<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language="javascript">
var tips = new Array();
var frm_fixed_mod_obj = {};
var frm_variable_mod_obj = {};
var frm_all_mod_obj = {};

var div_umpire_para_obj = {};
var not_swath_pipeline_obj = {};
var umpire_pipeline_obj = {};
var msplit_pipeline_obj = {};
var swath_radio_obj = {};
var div_msplit_para_obj = {};
var is_DDA_obj = {};
var div_add_more_swath_file_obj = {};
//var frm_use_msplit_DDA_lib_obj = {};
var param_set_area_obj = {};
var tpp_area_obj = {};
var div_add_more_swath_file_obj = {}
var s_obj = new Array();
 
var div_msgfdb_obj = {};
function make_all_ojbs(){
  frm_fixed_mod_obj = document.getElementById('frm_fixed_mod');
  frm_variable_mod_obj = document.getElementById('frm_variable_mod');
  frm_all_mod_obj = document.getElementById('frm_all_mod');
  
  div_umpire_para_obj = document.getElementById('div_umpire_para');
  not_swath_pipeline_obj = document.getElementById('not_swath_pipeline');
  umpire_pipeline_obj = document.getElementById('umpire_pipeline');
  msplit_pipeline_obj = document.getElementById('msplit_pipeline');
  swath_radio_obj = document.getElementById('swath_radio');
  div_msplit_para_obj = document.getElementById('div_msplit_para');
  //is_DDA_obj = document.getElementsByClassName('is_DDA');
  is_DDA_obj = $(".is_DDA");
  div_add_more_swath_file_obj = document.getElementById('div_add_more_swath_file');
  //frm_use_msplit_DDA_lib_obj = document.getElementById('frm_use_msplit_DDA_lib');
  div_add_more_swath_file_obj = document.getElementById('div_add_more_swath_file');
  param_set_area_obj = document.getElementById('param_set_area');
  tpp_area_obj = document.getElementById('tpp_area');  
  s_obj[0] = document.getElementById('div_Mascot');
  s_obj[1] = document.getElementById('div_GPM');
  s_obj[2] = document.getElementById('div_COMET');
  s_obj[3] = document.getElementById('div_MSGFPL');
  s_obj[4] = document.getElementById('div_MSFragger');
  div_msgfdb_obj = document.getElementById('div_msgfdb');
  lib_obj = document.getElementById('frm_MSPLIT_lib'); 
}

function popAddFile(){
  var theForm = document.form_task; 
  var selObj = theForm.frm_WellID;
  theForm.selected_ids.value = getFileIDstr(selObj);
  var old_action = theForm.action;
  theForm.action = './ms_search_task_add_file.php';
  newPop =  window.open('', 'formpopup', 'width=620,height=750,resizeable,scrollbars');
  newPop.focus();
  theForm.target = 'formpopup';
  theForm.submit();
  
  theForm.target = '_self';
  theForm.action = old_action;
   
  //var file = "./ms_search_task_add_file.php?table=<?php echo $table;?>&selected_ids=" + str;
  //popwin(file, 620, 750);
}
function getFileIDstr(obj){
  var file_id_arr = obj;
  var str = '';
  if(typeof file_id_arr != 'undefined' && typeof file_id_arr.length != 'undefined'){
    for(var i=1; i<file_id_arr.length; i++){
      if(file_id_arr[i].value == '') continue;
      //if(str != '') str += ',';
      str += file_id_arr[i].value + ',';
    }
  }
  return str;
}
function importTask(theID,tppTaskID){
  var theUrl = "<?php echo $PHP_SELF;?>?table=<?php echo $table;?>&myaction=new&frm_import_task_ID="+theID+"&tppTaskID="+tppTaskID;
  document.location = theUrl;
}
function refreshWin(){
  var theForm = document.form_task;
  var selObj = theForm.frm_WellID; 
  if(theForm.frm_file_id_str.value == ''){
    theForm.frm_file_id_str.value = getFileIDstr(selObj);
  }
  make_mod_strings(theForm);
  theForm.submit();
}
function make_mod_strings(theForm){
  theForm.frm_fixed_mod_str.value = all_option_to_str(frm_fixed_mod_obj);
  theForm.frm_variable_mod_str.value = all_option_to_str(frm_variable_mod_obj);
  theForm.frm_all_mod_str.value = all_option_to_str(frm_all_mod_obj);
}
function deleteRow(file_id){
   
  try{
    var id_arr = file_id.split("_");
    var folder_id_arr = new Array();
    var table = document.getElementById('dataTable');    
    var rowCount = table.rows.length;
    var delete_index = 0;
    for(var i=1; i<rowCount; i++){
      var row = table.rows[i];
      var delet_id = row.cells[2].childNodes[0].value;
      var folder_id = row.cells[3].childNodes[0].value;      
      if(delet_id == id_arr[1]) {
       delete_index = i;
      }else{
        folder_id_arr.push(folder_id);
      }
    }
    if(delete_index != 0){
      table.deleteRow(delete_index);
    }
    
    var energy = folder_id_arr.join();
    find_flag = 0;
    for(var j=0; j<folder_id_arr.length; j++){
      if(folder_id_arr[j] == id_arr[0]){
        find_flag = 1;
        break;
      }
    }
    if(find_flag == 0){
      var folder_table = document.getElementById('folderIdTable');
     
      var rowCount = folder_table.rows.length;
      for(var k=0; k<=rowCount; k++){
        var row = folder_table.rows[k];
        if(row.cells.length < 4) continue;
        var delet_id = row.cells[0].childNodes[0].value;
        if(delet_id == id_arr[0]){       
          folder_table.deleteRow(k);
          break;
        }
      }
    }
  }
  catch(e){
    alert(e);
  }
}

function is_swath_file(theForm){
  //refreshWin();
  //return;
  var runTPP = theForm.frm_runTPP;
  if(theForm.frm_is_SWATH_file.checked){
    not_swath_pipeline_obj.style.display = "none";
     
    umpire_pipeline_obj.style.display = "block";
    swath_radio_obj.style.display = "inline";
    div_umpire_para_obj.style.display = "block";
     
    runTPP.checked = true;
    <?php if(!$theTaskID){?>
    runTPP.disabled = true;
     <?php }?>
     
    change_swath_app(theForm.frm_swath_app);
  }else{
    
    not_swath_pipeline_obj.style.display = "block";
    umpire_pipeline_obj.style.display = "none";
    swath_radio_obj.style.display = "none";
    div_umpire_para_obj.style.display = "none";
    msplit_pipeline_obj.style.display = "none";
    div_msplit_para_obj.style.display = "none";
    <?php if(!$theTaskID){?>
    runTPP.disabled = false;
    <?php }?>
     
    theForm.frm_i_MSGFDB.checked = false;
    //if($.isArray(is_DDA_obj)){
   
      show_hide_all(is_DDA_obj, "none");
    //}
     
    show_hide_search_engines(theForm, '');
    
    showAllDivs(theForm);
  }
   
}
function change_swath_app(sel){
  //if($.isArray(is_DDA_obj)){
    show_hide_all(is_DDA_obj, "none");
  //}
  if(sel.options[sel.selectedIndex].value == 'MSUmpire'){
    //frm_use_msplit_DDA_lib_obj.checked = false;
    msplit_pipeline_obj.style.display = "none";
    umpire_pipeline_obj.style.display = "block";
    div_msplit_para_obj.style.display = "none";
    div_add_more_swath_file_obj.style.display = "none";
    div_umpire_para_obj.style.display = "block";
    tpp_area_obj.style.display = "block";
    sel.form.frm_runTPP.checked = true;
    show_hide_search_engines(sel.form, '');
    param_set_area_obj.style.display = 'inline';
    sel.form.frm_i_MSGFDB.checked = false;
  }else{
    msplit_pipeline_obj.style.display = "block";
    umpire_pipeline_obj.style.display = "none";
    div_msplit_para_obj.style.display = "block"; 
    div_umpire_para_obj.style.display = "none";
    tpp_area_obj.style.display = "none"; 
    sel.form.frm_runTPP.checked = false;
    //if($.isArray(is_DDA_obj)){
      show_hide_all(is_DDA_obj, "inline");
    //}
    show_hide_search_engines(sel.form, 'MSPLIT');
    checkMsplitLib();  
  }
  
}
function show_hide_all(the_obj, show_hide){
  for(i = 0; i< the_obj.length;i++){
    if(!$.isEmptyObject(the_obj[i])){
      the_obj[i].style.display = show_hide;
    }
  }
}
function show_hide_search_engines(theFom, searchType){
  if(searchType == 'MSPLIT'){
    show_hide_all(s_obj, 'none');
    div_msgfdb_obj.style.display = 'inline';
  }else{
    show_hide_all(s_obj, 'inline');
    div_msgfdb_obj.style.display = 'none';
  }
}


function checkMsplitLib(){ 
  //if(frm_use_msplit_DDA_lib_obj.checked){
  if(lib_obj.options[lib_obj.selectedIndex].value !=''){
    div_add_more_swath_file_obj.style.display = "none";
    show_hide_all(is_DDA_obj, "inline");
    param_set_area_obj.style.display = 'none';
    div_msgfdb_obj.style.display = 'none';
    frm_use_msplit_DDA_lib_obj.form.frm_i_MSGFDB.checked = false;
    div_add_more_swath_file_obj.style.display = "block";
  }else{
    div_add_more_swath_file_obj.style.display = "block";
    show_hide_all(is_DDA_obj, "inline");
    param_set_area_obj.style.display = 'inline';
    div_msgfdb_obj.style.display = 'inline'; 
    div_add_more_swath_file_obj.style.display = "block";
     
  }
}
function showAllDivs(theForm){
  tpp_area_obj.style.display = "block";  
  //frm_use_msplit_DDA_lib_obj.checked = false;
  div_msgfdb_obj.style.display = 'none';  
  param_set_area_obj.style.display = 'inline';
  show_hide_all(is_DDA_obj, "none");
  div_add_more_swath_file_obj.style.display = "none";
}



function editMSPLITset(sel){
  var opValue =sel.options[sel.selectedIndex].value;
  if(opValue){
    popwin('./ms_search_msplit.php?frm_setID='+opValue,740, 760);
  }else{
    alert("Please select a MSPLIT parameter set.");
  }
}
function viewMSPLIT_lib(theForm){
  var opValue =theForm.frm_MSPLIT_lib.options[theForm.frm_MSPLIT_lib.selectedIndex].value;
  popwin('./ms_search_msplit_lib.php?frm_MSPLIT_lib='+opValue,800,700);
}
function viewParameterSet(sel){
  var theValue = sel.options[sel.selectedIndex].value;
  if(!theValue){
    alert("Please select a parameter set.");
    return;
  }
  var theFile = './ms_search_parameter.php?frm_Machine=<?php echo $table;?>&frm_setID=' + theValue;
  popwin(theFile,870,840)
}
function editDIAUmpireset(sel){
  var opValue =sel.options[sel.selectedIndex].value;
  if(opValue){
    popwin('./ms_search_diaumpire.php?frm_setID='+opValue,750, 660);
  }else{
    alert("Please select a DIA-Umpire parameter set.");
  }
}
function editMSPLITset(sel){
  var opValue =sel.options[sel.selectedIndex].value;
  if(opValue){
    popwin('./ms_search_msplit.php?frm_setID='+opValue,740, 660);
  }else{
    alert("Please select a MSPLIT parameter set.");
  }
}
function editConverterset(theForm){
  var opValue =theForm.frm_ConverterSetID.options[theForm.frm_ConverterSetID.selectedIndex].value;
  if(opValue){
    popwin('./ms_search_proteowizard.php?frm_Machine=<?php echo $table;?>&frm_setID='+opValue,740, 700);
  }else{
    alert("Please select a raw convertter parameter set.");
  }
}
function editTPPset(theForm){
  var opValue =theForm.frm_tppSetID.options[theForm.frm_tppSetID.selectedIndex].value;
  if(opValue){
    popwin('./ms_search_philosopher.php?frm_setID='+opValue,650, 510);
  }else{
    alert("Please select a TPP parameter set.");
  }
}
function getFileIDstr(obj){
  var file_id_arr = obj;
  var str = '';
  if(typeof file_id_arr != 'undefined' && typeof file_id_arr.length != 'undefined'){
    for(var i=1; i<file_id_arr.length; i++){
      if(file_id_arr[i].value == '') continue;
      str += file_id_arr[i].value + ',';
    }
  }
  return str;
}
function submitModifyTask(theForm){
  var selObj = theForm.frm_WellID; 
  theForm.frm_file_id_str.value = getFileIDstr(selObj);
  if(theForm.frm_file_id_str.value == ''){
    alert("Please add data files!");
    return false;
  }  
  theForm.myaction.value = 'save';
  refreshWin();
}
function checkForm(theForm){
  var selected_engines = [];
  var engine_checked = false;
  var engine_checked_count = 0;
  var frm_SearchAllSetID_obj = theForm.frm_SearchAllSetID;
  var frm_db_obj = theForm.frm_db;
  var db_value = frm_db_obj.options[frm_db_obj.selectedIndex].value;
  var para_set_value = frm_SearchAllSetID_obj.options[frm_SearchAllSetID_obj.selectedIndex].value;
  var SWATH_app = '';
  
  if(theForm.frm_is_SWATH_file.checked){
    var sel = theForm.frm_swath_app;
    SWATH_app = sel.options[sel.selectedIndex].value;
  }  
  
  if(isEmptyStr(theForm.frm_TaskName.value)){
    alert("Please type a task name.");
    theForm.frm_TaskName.focus();
    return false;
  }else if(!db_value){
    alert("Please select Database.");
    return false;
  }
  <?php 
  if(defined("MASCOT_IP") and MASCOT_IP){
    echo "if(theForm.frm_i_Mascot.checked){
      selected_engines[engine_checked_count] = 'MASCOT';
      engine_checked_count += 1;
    }
    ";
  }
  ?>
  if(theForm.frm_i_GPM.checked){
      selected_engines[engine_checked_count] = 'GPM';
      engine_checked_count += 1;
  }
  if(theForm.frm_i_COMET.checked){
      selected_engines[engine_checked_count] = 'COMET';
      engine_checked_count += 1;
  }
  <?php 
  if($msfragger_in_prohits){?>
  if(theForm.frm_i_MSFragger.checked){
      selected_engines[engine_checked_count] = 'MSFRAGGER';
      engine_checked_count += 1;
  }
  <?php }?>
  
  if(theForm.frm_i_MSGFPL.checked){
      selected_engines[engine_checked_count] = 'MSGFPL';
      engine_checked_count += 1;
  }
  if(theForm.frm_i_MSGFDB.checked){
      selected_engines[engine_checked_count] = 'MSGFDB';
      engine_checked_count += 1;
  }
  <?php 
  if(defined("SEQUEST_IP") and SEQUEST_IP){
    echo "if(theForm.frm_i_SEQUEST.checked){
      selected_engines[engine_checked_count] = 'SEQUEST';
      engine_checked_count += 1;
    }
    ";
  }
  ?>    
  var checked_dda = false;
  if(SWATH_app){
    if(SWATH_app == 'MSPLIT'){
      for (var e=0; e < theForm.length; e++) {
        if(theForm.elements[e].name.match(/_is_DDA$/)){
          if(theForm.elements[e].checked){
            checked_dda = true;
          }
        }
      }
      
      if(lib_obj.options[lib_obj.selectedIndex].value !=''){
        if(checked_dda){
          alert("Please remove DDA file, no DDA file needed for predefined spectral library!");
          return false;
        }
      }else{
        if(!checked_dda){
          alert("Please add DDA file!");
          return false;
        }
        if(!theForm.frm_i_MSGFDB.checked){
           alert("Please select MSGFDB search engines!");
           return false;
        }else{
          //only one engine needed.
          selected_engines = ['MSGFDB'];
        }
      }
      
      //if(checked_dda){
      //  //dda files always needs search engine.
      //  if(!theForm.frm_i_MSGFDB.checked){
       //    alert("Please select MSGFDB search engines!");
      //     return false;
      //  }else{
      //    //only one engine needed.
      ////    selected_engines = ['MSGFDB'];
      //  }
     // }
      
      //if(theForm.frm_use_msplit_DDA_lib.checked){
      //if(0){
      //  var sel_lib = theForm.frm_MSPLIT_lib;
        //no search engine needed. if no dda files
        //if(!checked_dda){
        //  engine_checked_count = 1;
        //  if(!theForm.frm_allDDAFileAdded.checked){
        //    alert("Because you didn't add any DDA files, it will search from library only. Please check 'All files are added'!");
        //    return false;
        //  }
        //}
      //  if(sel_lib.options[sel_lib.selectedIndex].value == ''){
      //    alert("Please select a MSPLIT library!");
     //     return false;
     //   }
     // }else if(!checked_dda){
     //   alert("Please select DDA files!");
     //   return false;
     // }
      if(theForm.frm_MSPLITSetID.options[theForm.frm_MSPLITSetID.selectedIndex].value == ''){
        alert("Please select a MSPLIT parameter set!");
        return false;
      }     
    }else{
      //DIA-Umpire
      if(engine_checked_count < 1){
        alert("Please select search engines!");
        return false;
      }
      if(theForm.frm_DIAUmpireSetID.options[theForm.frm_DIAUmpireSetID.selectedIndex].value == ''){
        alert("Please select DIA-Umpire parameter set!");
        return false;
      }
    }
  }
  if(!SWATH_app || SWATH_app == 'MSUmpire' || checked_dda){
     
    if(engine_checked_count < 1){
      alert("Please select search engines!");
      return false;
    }else if(!para_set_value){
      alert("Please select Search Engine Parameter Set!");
      return false;
    }else{
      if(!parameter_set_match_selected_engines(theForm, selected_engines)){
        return false;
      }
    }
  }
   
  if(!SWATH_app || SWATH_app == 'MSUmpire'){
    if(theForm.frm_runTPP.checked){
      if(isEmptyStr(theForm.frm_tppTaskName.value)){
        alert("Please type a TPP name!");
        return false;
      }
      if(theForm.frm_tppSetID.options[theForm.frm_tppSetID.selectedIndex].value == ''){
        alert('Please select a TPP parameter set.');
        return false;
      }
    }
  }
  make_mod_strings(theForm);
  if(isEmptyStr(theForm.frm_fixed_mod_str.value) && isEmptyStr(theForm.frm_variable_mod_str.value)){
    if(!confirm("Do you want to submit the task without Fixed and Variable modifications?")){
      return false;
    }
  }
  
  
  var sel_pwzd = theForm.frm_ConverterSetID;
  if(sel_pwzd.options[sel_pwzd.selectedIndex].value == ''){
    alert("Please select a Proteowizard Parameter set!");
    return false;
  }
        
        
  var selObj = theForm.frm_WellID; 
  theForm.frm_file_id_str.value = getFileIDstr(selObj);
  if(theForm.frm_file_id_str.value == ''){
    alert("Please add data files!");
    return false;
  }
  theForm.myaction.value = 'save';
  refreshWin();
}
function parameter_set_match_selected_engines(theForm, selected_engines){
  var frm_SearchAllSetID_obj = theForm.frm_SearchAllSetID;
  var frm_engine_index_obj = theForm.frm_engine_index;
  var para_set_index = frm_SearchAllSetID_obj.selectedIndex;
  for(var i=0; i<frm_engine_index_obj.length; i++){
    if(i == para_set_index){
      var para_ens = frm_engine_index_obj[i].value.split(",");
      for(var j=0; j<selected_engines.length; j++){
        if($.inArray(selected_engines[j], para_ens) == -1){
          alert("Selected Parameter Set is only for " + frm_engine_index_obj[i].value+ "!");
          return false;
        }
      }
      break;
    }
  }
  return true;
}
function setTextToTitle(idName, sel){
  var theTitle = document.getElementById(idName);
  var theID = sel.options[sel.selectedIndex].value;
  if(theID){
    var theKey = idName + "_" + theID;
    theTitle.title= tips[idName][theKey];
  }else{
    theTitle.title= '';
  }
}
function get_default_tips(theform){
  setTextToTitle('enPar', theform.frm_SearchAllSetID);
  setTextToTitle('tppPar', theform.frm_tppSetID);
  setTextToTitle('umPar', theform.frm_DIAUmpireSetID);
  setTextToTitle('splitPar', theform.frm_MSPLITSetID);
  setTextToTitle('wizard', theform.frm_ConverterSetID);
}
</script>

<form action="<?php echo $PHP_SELF;?>" method="post" name="form_task" id="form_task">
<input type="hidden" name="table" value="<?php echo $table;?>">
<input type="hidden" name="myaction" value="refresh">
<input type="hidden" name="frm_PlateIDs" value="">
<input type="hidden" name="frm_file_id_str" value="">
<input type="hidden" name="theTaskID" value="<?php echo $theTaskID;?>">
<input type="hidden" name="frm_fixed_mod_str" value="">
<input type="hidden" name="frm_variable_mod_str" value="">
<input type="hidden" name="frm_all_mod_str" value="">
<input type="hidden" name="selected_ids" value="">
 
<table cellspacing="1" cellpadding="1" border="0" width=95%>
  <tr>
    <td align=center>
     <font face="Arial" size="+1" color="#000000"><b><?php echo $table;?> Search Task</b></font>
     <hr width="100%" size="1" noshade>
    </td>
  </tr>
  <tr>
      <td valign=top>
        <?php  echo "<font color=red>$error_msg</font>";?>
        <table id="folderIdTable" cellspacing="1" cellpadding="2" border="0" width=100%>
          <?php if($theTaskID){?>
          <tr><td><b>Task ID</b>:</td><td colspan=2><font color="#FF0000"><?php echo $theTaskID;?></font></td></tr>
          <?php }?>
          <tr>
            <td width=80><b>Task Name</b>:</td>
            <td align=left>
              <input type="text" name="frm_TaskName" value="<?php echo $frm_TaskName;?>" size="50" maxlength="100"<?php echo ($theTaskID)?" disabled":"";?>>
            </td>
            <td width=400>
            &nbsp;
             <?php 
              if(!$theTaskID){
                echo "<a onclick=\"showTip(event,'import_div')\" title='<b>Import</b>;;import previous task parameters.'>
                <img src=./images/import.gif border=0 valign=top></a>
                $frm_import_task_ID";
              }
              ?>
<DIV ID='import_div' STYLE="display: none; position: absolute;  overflow:auto; z-index:15; width: 420px; height: 400px;">
              <table align="center" cellspacing="1" cellpadding="3" border="0" width=100% bgcolor="#979797">
                <tr bgcolor="black" height=25>
                <td valign="bottem" colspan=2>&nbsp;&nbsp;&nbsp;
                <font color="white" face="helvetica,arial,futura" size="2"><b>Select task</b></font>
                </td>
                <td><input type=button VALUE="Close" onclick="javascript: hideTip('import_div');"></td>
                </tr>
                <tr>
                  <td>Task ID</td><td>Task Name</td><td>Import</td>
                </tr>
                <?php 
                
                foreach($user_task_record as $tmpTask){
                  $tableTppTasks = $table . "tppTasks";
                  $SQL = "SELECT `ID` FROM $tableTppTasks WHERE `SearchTaskID` = '".$tmpTask['ID']."' ORDER BY ID DESC LIMIT 1";
                  $tppTask_arr = $managerDB->fetch($SQL);
                  $tppTaskID = '';
                  if($tppTask_arr && $tppTask_arr['ID']){
                    $tppTaskID = $tppTask_arr['ID'];
                  }
                echo "
                <tr>
                  <td bgcolor='$import_color'>".$tmpTask['ID']."</td><td bgcolor='$import_color'>".$tmpTask['TaskName']."</td><td align=center bgcolor='$import_color'><a href=\"javascript:importTask('".$tmpTask['ID']."','$tppTaskID')\"><img src='images/icon_import.gif' border=0></a></td>
                </tr>
                ";
                }?>
                <tr height=35><td align="center" colspan=3><input type=button name='hide_div' VALUE=" Close " onclick="javascript: hideTip('import_div');"></td></tr>
              </table> 
</DIV>
            </td> 
          </tr>
          <?php 
          if($theTaskID){
          ?>
           <tr>
            <td bgcolor=#d0d0d0><input type='hidden' name='folder_ID' value=''><b>Folder ID</b></td>
            <td bgcolor=#d0d0d0><b>Folder Name</b></td>
            <td bgcolor=#d0d0d0><b>Project</b></td>
          </tr>
          <?php 
          }
          $dis_projectArr = array();
//echo count($foldersRD); 
          if($foldersRD and $theTaskID){
            for($i = 0; $i < count($foldersRD); $i++){
              echo "<tr bgcolor=#deedf3 id=".$foldersRD[$i]['ID'].">\n";
              echo "<td><input type='hidden' name='folder_ID' value='".$foldersRD[$i]['ID']."'>".$foldersRD[$i]['ID'] . "</td>\n"; 
              echo "<td>".$foldersRD[$i]['FileName'] . "</td>\n";
              $tmp_pro = ($foldersRD[$i]['ProjectID'])?$pro_access_ID_Names[$foldersRD[$i]['ProjectID']]:"&nbsp;";
              echo "<td>".$tmp_pro."</td>\n"; 
              echo "<td><input type='hidden' name='folder_ID' value='".$foldersRD[$i]['ID']."'></td>\n";  
              echo "</tr>\n";
            }
          }
          $styleColor = 'white';
          if($frm_status == 'Running'){
            $styleColor = 'lightgreen';
          }else if($frm_status == 'Error'){
            $styleColor = 'yellow';
          }
          
          ?>
        </table>
      <?php 
      $styleColor = 'white';
      if($frm_status == 'Running'){
        $styleColor = 'lightgreen';
      }else if($frm_status == 'Error'){
        $styleColor = 'yellow';
      }
      if($theTaskID) echo "<br><b>Status</b>: <font style='background-color: $styleColor;'>$frm_status $warning_msg</font>";
      if($theTaskID) echo "<br><b>Start Time: </b><font color=red> $frm_startTime </font>";
      if($theTaskID) echo "<br><font color=#008000>Search parameters can not be changed post-search initiation.</font>";
      ?>
      </td>
    </tr>
    <tr>
      <td valign=top bgcolor=#989898 width=100%>
        <div style="background-color: #515151; height:25px">&nbsp; &nbsp;
          <input type="checkbox" name="frm_is_SWATH_file" value="y" <?php echo ($frm_is_SWATH_file)?' checked':'';?> <?php echo ($theTaskID)?" disabled":"";?> onclick="refreshWin()">
          <b><font color="#FFFFFF">Is DIA file</font></b>&nbsp;&nbsp;&nbsp;&nbsp;
          <span id="swath_radio" style="display:none"><b><font color="#FFFFFF">Run</font> </b>
          <select name="frm_swath_app"  onChange="change_swath_app(this)"<?php echo ($theTaskID)?" disabled":"";?>>
          <OPTION value='MSUmpire'<?php echo ($frm_swath_app=='MSUmpire')?" selected":"";?>>DIA-Umpire</OPTION>
          <OPTION value='MSPLIT'<?php echo ($frm_swath_app=='MSPLIT')?" selected":"";?>>MSPLIT-DIA</OPTION>
          </select>                   
          </span>
        </div>
        <div class="img_box coWhite"><br>
          <div id="not_swath_pipeline" style="display:block"><img src=images/iprophet_pipe_line.gif></div>
          <div id="umpire_pipeline" style="display:none"><img src=images/search_umpire_pipe_line.gif>
          <a href="javascript: popwin('./images/umpire_pipeline.png',820,700);"><img border="0" alt="Task detail" src="images/icon_view.gif"></a>
          </div> 
          <div id="msplit_pipeline" style="display:none"><img src=images/search_msplit_pipe_line.gif></div>
          <br>
        </div> 
        <div class="img_box coEn"> 
        
        <table cellspacing="2" cellpadding="2" border="0" width=100% align=center>
          <tr>
            <td colspan=2>
              <b><font color="#515151">Search Engine Parameters</font></b>
             <hr width="100%" size="1" noshade>
            </td>
          </tr> 
          <tr>
            <td align="right" nowrap bgcolor="#50c5a5" width=120>
              <b>Parameter Set</b>
            </td>
            <td align="left">
              
              <span ID='param_set_area'>
              <select name="frm_SearchAllSetID" <?php echo ($theTaskID)?" disabled":"";?> onChange="setTextToTitle('enPar', this)">

                <option value=''>-- --
                <?php 
                $enPara_name_arr = $used_sets_arr['SearchEngines'];
                $enPara_set_arr = display_options($frm_SearchAllSetID,$frm_SearchAllSetName,$enPara_name_arr, $table);
                
                ?>
              </select>
                <input type=hidden name=frm_engine_index value=''>
<script language="javascript">
tips['enPar'] = new Array();
<?php
  foreach($enPara_set_arr as $tmpPara){
    echo "tips['enPar']['enPar_".$tmpPara['ID']."'] = '".makeTip_str($tmpPara['Description'])."';\n";
  }
?>
</script>        
                <?php 
                
                foreach($enPara_set_arr as $thePara){
                  $tmp_en_str = '';
                  $tmp_para_arr = explode("\n",$thePara['Parameters']);
                  foreach($tmp_para_arr as $tmp_para_val){
                    if(!trim($tmp_para_val)) continue;
                    $tmp_para_arr2 = explode("===",$tmp_para_val);
                    if(count($tmp_para_arr2) == 2){
                      if($tmp_en_str) $tmp_en_str.=',';
                      $tmp_en_str .= $tmp_para_arr2[0];
                    }
                  }
                  echo "<input type=hidden name=frm_engine_index value='$tmp_en_str'>\n";
                  
                }
                ?> 
              &nbsp;&nbsp;
              <input type="button" name="frm_edit_SearchAll" value="View" onClick="viewParameterSet(this.form.frm_SearchAllSetID)"> 
              <a title="" ID='enPar'  href="#"><img border="0" src="images/icon_tip.gif"  align=bottom></a>
              <?php
                if(!$enPara_set_arr){
                    echo "<font color='#FF0000'>Please make parameter set</font>";
                }
              ?>
                   
             </span> 
            </td>
          </tr>
          <tr>
            <td align="right" nowrap bgcolor="#50c5a5" width=120>
              <b>Search engines</b>
            </td>
            <td align="left">
             
             <?php if(defined("MASCOT_IP") and MASCOT_IP){?>
              <span class='en' id='div_Mascot'><input type="checkbox" name="frm_i_Mascot" value="Mascot"<?php echo ($frm_i_Mascot)?' checked':'';?><?php echo ($theTaskID)?" disabled":"";?>>&nbsp;Mascot&nbsp;</span>
             <?php }
               if($gpm_in_prohits){?>
              <span class='en' id='div_GPM'><input type="checkbox" name="frm_i_GPM" value="GPM" <?php echo ($frm_i_GPM)?' checked':'';?><?php echo ($theTaskID)?" disabled":"";?>>&nbsp;XTandem&nbsp;</span>
             <?php }?>
             <?php if($comet_in_prohits){?>
              <span class='en' id='div_COMET'><input type="checkbox" name="frm_i_COMET" value="COMET" <?php echo ($frm_i_COMET)?' checked':'';?><?php echo ($theTaskID)?" disabled":"";?>>&nbsp;Comet&nbsp;</span>
             <?php }?>
             <?php if($msfragger_in_prohits){?>
              <span class='en' id='div_MSFragger'><input type="checkbox" name="frm_i_MSFragger" value="MSFragger" <?php echo ($frm_i_MSFragger)?' checked':'';?><?php echo ($theTaskID)?" disabled":"";?>>&nbsp;MSFragger&nbsp;</span>
             <?php }?>
             
             <?php if($msgfpl_in_prohits){?>
              <span class='en' id='div_MSGFPL'><input type="checkbox" name="frm_i_MSGFPL" value="MSGFPL" <?php echo ($frm_i_MSGFPL)?' checked':'';?><?php echo ($theTaskID)?" disabled":"";?>>&nbsp;MS-GF+&nbsp;</span>
             <?php }?>
             <?php if($msgfpl_in_prohits){?>
              <span class='en' id='div_msgfdb' style="display:none"><input type="checkbox" name="frm_i_MSGFDB" value="MSGFDB" <?php echo ($frm_i_MSGFDB)?' checked':'';?><?php echo ($theTaskID)?" disabled":"";?>>&nbsp;MS-GFDB&nbsp;</span>
              
             <?php }?>
             
            </td>
          </tr>
          <tr>
              <td nowrap bgcolor="#50c5a5" align=right><b>Database</b>
              
              </td>
              <td align="left" width=90%>
              <select name=frm_db <?php echo ($theTaskID)?" disabled":"";?>>
                <option value=''>-- --
          <?php 
              for($i=0; $i < count($gpm_dbs['name']); $i++){
                $selected = '';
                $db_name = $gpm_dbs['name'][$i];
                $db_label = $gpm_dbs['label'][$i];
                if($db_name == $frm_db){
                  $selected = " selected";
                }
                echo "<option value='$db_name'$selected>$db_label\n"; 
              }
          ?>
              </select>
              <?php if($USER->Type == 'Admin'){?> 
               &nbsp;&nbsp; &nbsp; &nbsp; 
               <input type=button value='View' onClick="popwin('pop_dbs_view.php',680,600, 'dbwin')">
               <?php }?>
              </td>
          </tr>
           
          <tr>
              <td bgcolor="#50c5a5" align=right valign=top><b>Fixed<br>Modifications</b></td>
              <td rowspan="2"> 
              <div class='mod'>
              <table cellspacing="2" cellpadding="2" border="0" bgcolor="#e0e0e0" width=100% >
                <tr>
                    <td>&nbsp; &nbsp; selected modifications</td><td>&nbsp;</td><td>&nbsp; &nbsp; available modifications</td>
                </tr>
                <tr>
                    <td align=center>
                    <select class='sel_wth' ID="frm_fixed_mod" name="frm_fixed_mod" size="4" >
                    <?php 
                      foreach($default_fixed_mod_arr as $name){
                        echo "<option value='$name'>$name\n";
                      }
                      ?>
                    </select>
                    </td>
                    <td align=center>
                      <?php if(!$theTaskID){?>
                      <input type=button value='<<' onClick="add_option_to_selected('frm_all_mod', 'frm_fixed_mod')"><br>
                      <input type=button value='>>' onClick="add_option_to_selected('frm_fixed_mod', 'frm_all_mod')">   
                      <?php }?>
                    </td>
                    <td rowspan="2" align=center>
                    <select class='sel_wth' ID="frm_all_mod" NAME="frm_all_mod" SIZE=8 onChange="">
                      <?php 
                      foreach($default_other_mod_arr as $name){
                        echo "<option value='$name'>$name\n";
                      }
                      ?>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td align=center>
                    <select class='sel_wth' ID="frm_variable_mod" name="frm_variable_mod" size="4">
                    <?php 
                      foreach($default_variable_mod_arr as $name){
                        echo "<option value='$name'>$name\n";
                      }
                      ?>
                    </select>
                    </td>
                    <td align=center>
                      <?php if(!$theTaskID){?>
                      <input type=button value='<<' onClick="add_option_to_selected('frm_all_mod', 'frm_variable_mod')"><br>
                      <input type=button value='>>' onClick="add_option_to_selected('frm_variable_mod', 'frm_all_mod')">  
                      <?php }?> 
                   </td>
                </tr>
                 
               </table>
               <?php 
               if($USER->Type == 'Admin'){?> 
               Set default modifications 
               <a class="" href="javascript: popwin('pop_mods.php',700,650);">
                    <img border="0" alt="Task detail" src="images/icon_view.gif">
               </a>
               <?php }?>
               </div>
              
              </td>
          </tr>
          <tr>
              <td bgcolor="#50c5a5" align=right valign=top><b>Variable<br>Modifications</b></td>
          </tr>
        </table>
        </div>
        <?php 
        $memory_msg = '';
        exec("cat /proc/meminfo", $outputs);
        $total_mem = round(intval(preg_replace("/\D+/", "", $outputs[0]))/1024/1024);
        
        if($total_mem < 5){
          $memory_msg = "<font color=red>Prohits server memory is $total_mem GB (< 5 GB). DIAUmpire may not run for large file.</font><br>"; 
        }
        
       
        ?>
        <div class="img_box coUmpire" id="div_umpire_para" style="display:none">
        <table cellspacing="2" cellpadding="0" border="0" width=100% align=center>
          <tr>
            <td colspan="2"><b><font color="#515151">DIA-Umpire Parameter set</font></b>
            <hr width="100%" size="1" noshade>  
            </td> 
          </tr>
          <tr>
              <td colspan="2">
             Parameters required for SWATH raw file to generate pseudo ms/ms. 
              </td>
          </tr>
          <tr>
              <td colspan="2">&nbsp;
              <select name=frm_DIAUmpireSetID<?php echo ($task_has_results)?" disabled":"";?>  onChange="setTextToTitle('umPar', this)">
              <option value=''>-- &nbsp; --
              <?php 
              $DIAUmpire_name_arr = $used_sets_arr['DIAUmpire'];
              $DIAUmpire_set_arr = display_options($frm_DIAUmpireSetID,$frm_DIAUmpireSetName,$DIAUmpire_name_arr,'DIAUmpire'); 
              ?>
              </select>
              <input type=button value='View' onClick=editDIAUmpireset(this.form.frm_DIAUmpireSetID)>
<script language="javascript">
tips['umPar'] = new Array();
<?php
  foreach($DIAUmpire_set_arr as $tmpPara){
    echo "tips['umPar']['umPar_".$tmpPara['ID']."'] = '".makeTip_str($tmpPara['Description'])."';\n";
  }
?>
</script>     <a title="" ID='umPar'  href="#"><img border="0" src="images/icon_tip.gif"  align=bottom></a>     
              <?php  echo $memory_msg;?>              
          </td>
          </tr> 
        </table>
        </div>
        <div class="img_box coUmpire" id="div_msplit_para" style="display:none">
        <table cellspacing="2" cellpadding="0" border="0" width=100% align=center>
          <tr>
              <td colspan="2" ><b><font color="#515151">MSPLIT-DIA Parameter set</font></b>
               <hr width="100%" size="1" noshade>
              </td>
          </tr>
          <tr>
              <td>Parameters required for MSPLIT-DIA.</td>
             <td>
             Search Spectral Library <!--input type="hidden" ID="frm_use_msplit_DDA_lib" name="frm_use_msplit_DDA_lib" value="0" <?php echo ($frm_use_msplit_DDA_lib)?' checked':'';?><?php echo ($theTaskID)?" disabled":"";?> onclick="checkMsplitLib();" -->&nbsp;
             </td>
          </tr>
          <tr>
            <td>&nbsp;
              <select name=frm_MSPLITSetID<?php echo ($theTaskID)?" disabled":"";?> onChange="setTextToTitle('splitPar', this)">
              <option value=''>-- &nbsp; --
              <?php 
              $MSPLIT_name_arr = $used_sets_arr['MSPLIT'];
              $MSPLIT_set_arr = display_options($frm_MSPLITSetID,$frm_MSPLITSetName,$MSPLIT_name_arr,'MSPLIT'); 
              
              ?>
              </select>
              <input type=button value='View' onClick=editMSPLITset(this.form.frm_MSPLITSetID)> 
<script language="javascript">
tips['splitPar'] = new Array();
<?php
  foreach($MSPLIT_set_arr as $tmpPara){
    echo "tips['splitPar']['splitPar_".$tmpPara['ID']."'] = '".makeTip_str($tmpPara['Description'])."';\n";
  }
?>
</script>   <a title="" ID='splitPar'  href="#"><img border="0" src="images/icon_tip.gif"  align=bottom></a>
            </td>
            <td> 
              <select ID="frm_MSPLIT_lib" name=frm_MSPLIT_lib<?php echo ($theTaskID)?" disabled":"";?> onchange="checkMsplitLib();">
              <option value=''>-- Create lib from DDA files --
              <?php 
               display_MSPLIT_lib_options($frm_MSPLIT_lib);
              ?>
              </select>
              <input type=button value='View' onClick=viewMSPLIT_lib(this.form)> 
              <?php 
              if(!$MSPLIT_set_arr){
                echo '<font color=red>Please create MSPLIT parameter set</font>';
              }
              ?>
            </td>
          </tr> 
        </table>
        </div>
        <div class="img_box coUmpire" id="div_converter" style="width: 40%;height: 113px"">
        <table cellspacing="2" cellpadding="0" border="0" width=100% align=center>
        <tr>
          <td><b><font color="#515151">Proteowizard Parameter set</font></b>&nbsp; 
          <hr width="100%" size="1" noshade>
          </td>
        </tr>
        <tr>
            <td>
            Parameters required for converting raw/wiff files<br>
            <select name="frm_ConverterSetID"<?php echo ($theTaskID)?" disabled":"";?> onChange="setTextToTitle('wizard', this)">
              <option value=''>-- &nbsp; --
              <?php 
              $Converter_name_arr = $used_sets_arr['Converter'];
              $converter_set_arr = display_options($frm_ConverterSetID,$frm_ConverterSetName,$Converter_name_arr,'Converter', $frm_is_SWATH_file, $table);
              ?>
            </select>
             <input type="button" name="frm_edit_PROTEOWIZARD" value="View" onClick=editConverterset(this.form)>
<script language="javascript">
tips['wizard'] = new Array();
<?php
  foreach($converter_set_arr as $tmpPara){
    echo "tips['wizard']['wizard_".$tmpPara['ID']."'] = '".makeTip_str($tmpPara['Description'])."';\n";
  }
?>
</script>
              <a title="" ID='wizard'  href="#"><img border="0" src="images/icon_tip.gif"  align=bottom></a>  
            </td>
        </tr>
         
        </table>
         <?php 
        if(!$converter_set_arr){
          echo "<font color=red>Please create Proteowizard parameter set for $table</font>\n";
        }
        ?>
        </div>
        
        
        <div class="img_box coUmpire" id="tpp_area" style="width: 57%; height: 113px">
         
        <table cellspacing="2" cellpadding="0" border="0" width=100% align=center>
        <tr>
              <td colspan="2"><b><font color="#515151">Run TPP</font></b>
              <input type='checkbox' name='frm_runTPP'<?php echo ($frm_runTPP)?' checked':'';?><?php echo ($theTaskID)?" disabled":"";?> value='yes'>
              <hr width="100%" size="1" noshade>
              </td>
          </tr>
           
          <tr>
              <td colspan="2">
             TPP can be run after the search task is finished in search results page.
              </td>
          </tr>
          <tr>
              <td>
                <b>TPP Name</b>
              </td>
              <td>
                <input type=text name=frm_tppTaskName value='<?php echo $frm_tppTaskName;?>' size="15" maxlength="30"<?php echo ($theTaskID)?" disabled":"";?>>
              </td>
          <tr>
              <td>      
                <b>TPP Parameter set</b>
              </td>
              <td>
              <select id='tppSetID' name=frm_tppSetID <?php echo ($theTaskID)?" disabled":"";?> onChange="setTextToTitle('tppPar', this)">
              <option value=''>-- &nbsp; --
              <?php 
              $TPP_name_arr = $used_sets_arr['TPP'];
              $tpp_set_arr = display_options($frm_tppSetID,$frm_tppSetName,$TPP_name_arr,'TPP', $frm_is_SWATH_file);
              ?>
              </select>
              <input type=button value='View' onClick=editTPPset(this.form)>
<script language="javascript">
tips['tppPar'] = new Array();
<?php
  foreach($tpp_set_arr as $tmpPara){
    echo "tips['tppPar']['tppPar_".$tmpPara['ID']."'] = '".makeTip_str($tmpPara['Description'])."';\n";
  }
?>
</script>      
              <a title="" ID='tppPar'  href="#"><img border="0" src="images/icon_tip.gif"  align=bottom></a>     
              <?php 
          if(!$tpp_set_arr){
                echo '<br><font color=red>Please create TPP parameter set for swath file</font>';
          }
          ?>        
             </td>
          </tr> 
            
        </table>
        
        </div>        
        <div class="img_box coData">
        <table cellspacing="2" cellpadding="0" border="0" width=100% align=center>
          <tr>
            <td>
            <b><font color="#515151">Data Files</font> (total: <?php echo $total_raw_file?>)</b> 
            <hr width="100%" size="1" noshade>
           
            <div id="div_add_more_swath_file" style="display:none">
             <b>All DDA files are added. Run all steps of the process pipeline. </b>
             <?php
             $disabled_DDA = '';
             if($task_record and strpos($task_record['DIAUmpire_parameters'], "allDDAFileAdded:Yes")!== false){
               $disabled_DDA = ' disabled';
               $frm_allDDAFileAdded = 'Yes';
               echo '<input type="hidden" name="frm_allDDAFileAdded" value="Yes">';
             }else if($frm_MSPLIT_lib){
               $disabled_DDA = ' disabled';
             }
             ?>
             
             <input type="checkbox" name="frm_allDDAFileAdded" value="Yes"<?php echo ($frm_allDDAFileAdded)?" checked":"";?><?php echo $disabled_DDA;?>><br>
             MSPLIT needs all DDA files to create spectral library. 
             If the box is checked, spectral library will be created and locked. 
            </div>
            </td>
          </tr> 
          <tr>
            <td>
            <TABLE id="dataTable" width="100%" border="0">
              <TR>
                <TD><font color="">
                <?php 
                  if($umpire_running_file_ID_arr){
                    echo "<font color=red>files (ID: ". implode(",", $umpire_running_file_ID_arr). ") in task ". implode(",", $umpire_running_tasks_arr). " 
                    are running/waiting. They cannot be selected</font>.<br>";
                  }
                  ?>
                [Folder ID] / File Name </font></TD>
                <TD>&nbsp;</TD>
                <TD><input type='hidden' name='frm_WellID' value=''></TD>
                <TD><input type='hidden' name='frm_FolderID' value=''></TD>
              </TR>
              
              <?php 
              if($task_record){
                $fileID_inDB = explode(",", $file_id_str);
              }
              foreach($file_option_output_arr as $file_val){
                  //$umpire_running_file_ID_arr = array();
                  //$umpire_running_tasks_arr = array();
                  if(in_array($file_val['ID'], $umpire_running_file_ID_arr)) continue;
                  $comb_id = $file_val['FolderID'].'_'.$file_val['ID'];
              ?>
              <TR>
                <TD width="30%" nowrap>
                  
                  <?php echo "[".$file_val['FolderID']."] / ".$file_val['FileName']." (".$file_val['ID'].")"?>
                </TD>
                
                <TD id="<?php echo $file_val['ID']?>" width="20%">&nbsp; &nbsp; &nbsp; 
                 <div class='is_DDA' style="display:<?php echo ($frm_is_SWATH_file and $frm_swath_app == 'MSPLIT')? "inline":"none";?>">
                 <?php 
                 $thefieldName = $file_val['ID']."_is_DDA";
                  
                 //if($theTaskID) $dda_check_disabled = ' disabled';
                 $dda_checked = '';
                 $box_disabled = '';
                 if(isset($$thefieldName) and $$thefieldName) $dda_checked = ' checked';
                 if($disabled_DDA or ($task_record and in_array($file_val['ID'], $fileID_inDB))) $box_disabled = ' disabled';
                 echo "<font color=#FFFFFF>is DDA</font><input type='checkbox' name='".$thefieldName."' value='1'". $dda_checked."$box_disabled>\n";
                 if($box_disabled){
                   $v = '';
                   if($dda_checked) $v = 1;
                   echo "<input type='hidden' name='".$thefieldName."' value='$v'>\n";
                 }
                 echo "</div>&nbsp;&nbsp;&nbsp;\n";
                  if(!in_array($file_val['ID'],$id_have_results) and !($theTaskID and $frm_swath_app == 'MSPLIT' and in_array($file_val['ID'], $fileID_inDB))){
                    $unsearched_file_exists = true;
                ?>
                    <a title='remove file' onclick="deleteRow('<?php echo $comb_id?>')"><img border='0' src='./images/icon_delete.gif'></a>              
                <?php }?>
                </TD>
                <TD><input type='hidden' name='frm_WellID' value='<?php echo $file_val['ID']?>'></TD>
                <TD><input type='hidden' name='frm_FolderID' value='<?php echo $file_val['FolderID']?>'></TD>
              </TR>
              <?php }?>
            </TABLE>   
            </td>
          </tr>  
          <tr>
          <td>
        <br>
        <?php 
        if($unsearched_file_exists && $theTaskID && $frm_swath_app != 'MSPLIT'){
          echo "<font color=red>Files with <img border='0' src='images/icon_delete.gif'> couldn't be searched in previous run. Please see the search log for detail. 
          Click 'Remove File' to delete them from the task. Otherwise they will be tried again.</font>";
        }
        ?>
        <br>
        <input type="button" name="frm_addFile" value="Add Files" onClick="popAddFile()">
        </td></tr>
        </table>
        </div>
        
    </td>
  </tr>
  <tr>
    <td align=center height=38>
<?php if(($perm_insert and !$theTaskID) or ($perm_modify and $theTaskID and !$task_has_results)){?>
<input type="button" name="frm_save" value="Run Task" onClick="checkForm(this.form)">
<?php }else if($perm_modify and $theTaskID and $task_has_results){?>
<input type="button" name="frm_modify" value="Run Task" onClick="submitModifyTask(this.form)">
<input type="button" name="frm_back" value="Cancel" onClick="document.location='./ms_search_task_view.php?theTaskID=<?php echo $theTaskID?>&table=<?php echo $table;?>'">
<?php }?>

    </td>
  </tr>
<script langguage="javascript">
make_all_ojbs();
is_swath_file(document.form_task);
get_default_tips(document.form_task);
</script>
    </form>   
</table>       
<?php
include("./ms_search_footer.php");


function display_MSPLIT_lib_options($frm_MSPLIT_lib){
  $selected = '';
  global $msplit_in_prohits;
  global $Prohits_path;
  
  if($msplit_in_prohits){
    $thefile = $Prohits_path . "/EXT/MSPLIT-DIA/msplit_lib.conf";
    if(!_is_file($thefile)){
      echo "Error: file missing: $thefile.";
    }
  }else{
    echo "error: Please check Prohits conf file to make sure that the variable 'MSPLIT_JAR_PATH' is correct"; 
    return;
  }
  
  $lines = file($thefile);
  foreach($lines as $line){
    $line = trim($line);
    if(preg_match("/^Name:(.+)$/", $line, $matches)){
      $name = trim($matches[1]);
      if($frm_MSPLIT_lib and $frm_MSPLIT_lib == $name){
        $selected = " selected";
      }else{
        $selected = '';
      }
      echo "<option value='$name'$selected>$name\n";
    }
  }
}


function display_options($ID,$Name,$Name_arr,$type, $is_SWATH='', $Machine=''){
  //$Name_arr the user previous used sets.
  global $pro_access_ID_str,$table;
  $option_array = array();
  $default_option = array();
  
  $default_ID_arr = array();
  $default_ID = '0';
  
	$set_arr = get_search_parameters($type, 0, '', $Machine, $is_SWATH);
  if($set_arr){
    $list_arr_default = array();
    $list_arr = array();
    $list_MSname_arr = array();
    foreach($set_arr as $tmpSet){
      if($tmpSet['Default']){
        $list_arr_default[$tmpSet['Name']] = $tmpSet['ID'];
      }else{
        $list_arr[$tmpSet['Name']] = $tmpSet['ID'];
      }
    }
    $list_arr = array_merge($list_arr_default, $list_arr);
    //display used set names first.
    foreach($Name_arr as $name_val){
      if(array_key_exists($name_val, $list_arr)){
        $style_str = '';
        $selected = ($list_arr[$name_val] == $ID or $name_val == $Name)?" selected":"";
        if(isset($list_arr_default[$name_val])){
          $style_str = " style='background-color: yellow;' ";
        }
        $default_option[] = "<option value='" . $list_arr[$name_val] . "'$selected$style_str>(".$list_arr[$name_val].")&nbsp;".$name_val."\n";
      }
    }     
    //display not used set names.
    foreach($list_arr as $tmp_name => $tmp_ID){     
      if(in_array($tmp_name, $Name_arr)) continue;      
      $style_str = '';
      $selected = ($tmp_ID == $ID or $tmp_name == $Name)?" selected":"";
      if(isset($list_arr_default[$tmp_name])){
        $style_str = " style='background-color: yellow;' ";
      }
      $default_option[] = "<option value='" . $tmp_ID . "'$selected$style_str>(".$tmp_ID.")&nbsp;".$tmp_name."\n";
    }
    $option_array = array_merge($default_option, $option_array);   
    foreach($option_array as $option_value){
      echo $option_value;
    }
  }
  return $set_arr;
}

function add_modifications($seachEngine, $frm_fixed_mod_str, $frm_variable_mod_str, $param_str){
  $frm_fixed_mod_str = trim($frm_fixed_mod_str);
  $frm_variable_mod_str = trim($frm_variable_mod_str);
  if($seachEngine == 'Mascot'){
    if($frm_fixed_mod_str){
      $tmp_MOD_str = '';
      $mods_arr = explode(";;", $frm_fixed_mod_str);
      foreach($mods_arr as $theMod){
        $theMod = trim($theMod);
        if($theMod){
          if($tmp_MOD_str) $tmp_MOD_str .= ";";
          $tmp_MOD_str .= "MODS=$theMod";
        }
      }
      $param_str = preg_replace("/;MODS=[^;]*/", ";$tmp_MOD_str", $param_str);
    }
    if($frm_variable_mod_str){
      $tmp_IT_MOD_str = '';
      $mods_arr = explode(";;", $frm_variable_mod_str);
      foreach($mods_arr as $theMod){
        $theMod = trim($theMod);
        if($theMod){
          if($tmp_IT_MOD_str) $tmp_IT_MOD_str .= ";";
          $tmp_IT_MOD_str .= "IT_MODS=$theMod";
        }
      }
      $param_str = preg_replace("/;IT_MODS=[^;]*/", ";$tmp_IT_MOD_str", $param_str);
    }
  }else{
    $tmp_mod_str = '';
    if($frm_fixed_mod_str){
      $frm_fixed_mod_str = str_replace(";;", ":::", $frm_fixed_mod_str);
    }
    if($frm_variable_mod_str){
      $frm_variable_mod_str = str_replace(";;", ":::", $frm_variable_mod_str);
    }
    
    $tmp_mod_str = "frm_variable_MODS|$frm_variable_mod_str&&frm_fixed_MODS|$frm_fixed_mod_str";
    if($seachEngine == 'GPM'){
      $tmp_gpm_mod_arr = make_mod_array($tmp_mod_str, 'GPM');
       if(isset($tmp_gpm_mod_arr['fixed']) and $tmp_gpm_mod_arr['fixed']){
          $param_str = preg_replace("/residue__modification_mass_select=/", "residue__modification_mass_select=".$tmp_gpm_mod_arr['fixed'], $param_str);
       }
       if(isset($tmp_gpm_mod_arr['variable']) and $tmp_gpm_mod_arr['variable']){
          $param_str = preg_replace("/residue__potential_modification_mass_select=/", "residue__potential_modification_mass_select=".$tmp_gpm_mod_arr['variable'], $param_str);
       }
    }else{
      $tmp_mod_str = "multiple_select_str=".$tmp_mod_str;
      $param_str = preg_replace("/multiple_select_str=/", "$tmp_mod_str", $param_str);
    }
  }
  return $param_str;
}

function fomat_proteowizard_name($tmp_arr){
  if(count($tmp_arr) > 1){
    preg_match("/(.+?)(__DEFAULT)$/", $tmp_arr[1],$matches);
    if(isset($matches[2]) && $matches[2] == '__DEFAULT'){
      $frm_setName = $tmp_arr[0].$matches[2];
      $frm_MSname = $matches[1];
    }else{
      $frm_setName = $tmp_arr[0];
      $frm_MSname = $tmp_arr[1];
    }
    $name_str = $frm_setName."@@".$frm_MSname;
  }else{
    $name_str = $tmp_arr[0]."@@";
  }
  return $name_str;
}                       
?>