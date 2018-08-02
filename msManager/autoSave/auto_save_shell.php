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

ini_set("memory_limit", "-1");
$error = '';
$report = '';

//error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(3600*48);  // it will execute for 48 hours

$midnight = 0; //set hour only
$earlymorning = 4; //set hour only
$field_spliter = ";;";
$logfile = '../../logs/parser.log';
$plate_ID_arr = array();
$frm_geneLevelHits = '';


if(count($_SERVER['argv']) == 4){

}else if(isset($_SERVER['argv']) and count($_SERVER['argv']) > 3 and $_SERVER['argv'][1] and $_SERVER['argv'][2]){
  $SID = $_SERVER['argv'][1];
  $table = $_SERVER['argv'][2];
  $task_ID = $_SERVER['argv'][3];
  $debug = $_SERVER['argv'][4];
  $CHECK_TARGETDB_BAND_ID = $_SERVER['argv'][5];
  $user_id = $_SERVER['argv'][6];
  $prohits_server_name = $_SERVER['argv'][7];
  $gpm_ip = $prohits_server_name;
}else{
  fatal_Error("Arguments error");
}

/*
//==============================================================================
if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}
echo "<pre>";
print_r($request_arr);
echo "</pre>";
//==============================================================================
*/
require("../../config/conf.inc.php");
include("../../common/mysqlDB_class.php");
include("../../common/common_fun.inc.php");
require("../../common/user_class.php");
require("../classes/saveConf_class.php"); 
require("../classes/xmlParser_class.php"); 
include("./auto_save_mascot_shell_fun.inc.php");
include("./auto_save_MSPLIT_shell_fun.inc.php");
include("./auto_save_gpm_shell_fun.inc.php");
include("./auto_save_tpp_shell_fun.inc.php");
include("./auto_save_sequest_shell_fun.inc.php");
include("./auto_save_gene_parse.inc.php");
require "../is_dir_file.inc.php";
require("../../analyst/common_functions.inc.php");
require_once("../common_functions.inc.php");
require("../../admin_office/update_protein_db/auto_update_protein_add_accession.inc.php");

$admin_email = ADMIN_EMAIL;
$searchEngine = '';
$tpp_in_prohits = false;

$phpself_dir = dirname($_SERVER['SCRIPT_FILENAME']); 
$analyst_dir = preg_replace("/msManager\/.*/", '', $phpself_dir) . "analyst";

$prohitsDB = new mysqlDB(PROHITS_DB);
$hitsDB = new mysqlDB(PROHITS_DB);
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);

$proteinDB = new mysqlDB('Prohits_proteins');

$managerDB = new mysqlDB(MANAGER_DB);

$tableSearchResults = $table . "SearchResults";
$tableSearchTasks = $table . "SearchTasks";
$tableTppResults = $table . "tppResults";

//-------------------- hits db permission check ------------------
$SQL = "select ID, Fname,Lname, Email from User where ID='$user_id'";
$USER = $prohitsDB->fetch($SQL);
if(!$USER){
 echo "user id is not correct";exit;
}
$SQL  = "select P.Insert, P.Modify, P.Delete from PagePermission P, Page G where P.PageID=G.ID and G.PageName='Auto Save' and UserID=$user_id";
$record = $prohitsDB->fetch($SQL);
$perm_modify = $record['Modify'];
$perm_delete = $record['Delete'];
$perm_insert = $record['Insert'];
if(!$record) {
  write_Log("User($user_id) has no parser permission" , $log_file='');
}

$SQL = "select  
        ID as TaskID, PlateID, SearchEngines, 
        Parameters, TaskName, 
        LCQfilter, DataFileFormat,ProjectID, 
        StartTime, Schedule, Status,UserID 
        from " . $tableSearchTasks . " 
        where ID='$task_ID'";
$task_arr = $managerDB->fetch($SQL);

if(!$task_arr){
  fatal_Error( "error: no record found for query '$SQL'",  __LINE__ );
}else{
  //print_r($task_arr);exit;
}
$ProjectID = $task_arr['ProjectID'];

$insert_only = 1;
$Pro_ID_names = get_user_permited_project_id_name($prohitsDB, $user_id, $insert_only);
$Pro_ID_dbName = array();
 
$Conf = new SaveConf($table, $managerDB->link);
$Conf->fetch_task($task_ID);
//------for TPP and geneLevel Value---------------------------------------------
$tmp_Tpp_Value_arr = explode(';',$Conf->Tpp_Value);
foreach($tmp_Tpp_Value_arr as $tmp_Tpp_Value_val){
  $tmp_Tpp_Value_arr2 = explode('=',$tmp_Tpp_Value_val);
  $$tmp_Tpp_Value_arr2[0] = $tmp_Tpp_Value_arr2[1];
}
//------------------------------------------------------------------------------
$frm_mascot_saveWell_str = $Conf->Mascot_SaveWell_str;
$frm_gpm_saveWell_str = $Conf->GPM_SaveWell_str;
$frm_MSPLIT_saveWell_str = '';
$frm_MSPLIT_DDA_saveWell_str = '';
$frm_sequest_saveWell_str = '';
$tmp_sequest_MSPLIT_str = $Conf->SEQUEST_SaveWell_str;
if(preg_match('/SEQUEST:([\d;,]+)/', $tmp_sequest_MSPLIT_str,$matches)){
  $frm_sequest_saveWell_str = $matches[1];
}
if(preg_match('/MSPLIT:([\d;,]+)/', $tmp_sequest_MSPLIT_str,$matches)){
  $frm_MSPLIT_saveWell_str = $matches[1];  
}
if(preg_match('/MSPLIT_DDA:([\d;,]+)/', $tmp_sequest_MSPLIT_str,$matches)){
  $frm_MSPLIT_DDA_saveWell_str = $matches[1];  
}

$frm_tpp_saveWell_str = $Conf->Tpp_SaveWell_str;
if(!$perm_insert or (!$frm_geneLevelHits and !$frm_mascot_saveWell_str and !$frm_gpm_saveWell_str and !$frm_tpp_saveWell_str and !$frm_sequest_saveWell_str and !$frm_MSPLIT_saveWell_str and !$frm_MSPLIT_DDA_saveWell_str)){
  write_Log( "no search result to be selected to parse.");
  exit;
}
    
//-------------------------end permission check--------------------
$tpp_in_prohits = true;
//print_r($Conf);

@ob_end_flush();
$Saved = 1;
$Mascot_search_results = array();
$GPM_search_results = array();
$SEQUEST_search_results = array();
$MSPLIT_search_results = array();
$MSPLIT_DDA_search_results = array();
$TPP_search_results = array();

$project_searchEngine_arr = array();

if($frm_mascot_saveWell_str){
  $frm_mascot_saveWell_str = str_replace(";",",", $frm_mascot_saveWell_str);
  $SQL = "select R.WellID, T.FileName,T.ProjectID, T.ProhitsID, R.DataFiles, R.SearchEngines, R.SavedBy from $table T, $tableSearchResults R where T.ID=R.WellID and T.ID in($frm_mascot_saveWell_str) and R.TaskID='$task_ID' and R.SearchEngines='Mascot' and (R.SavedBy is NULL or R.SavedBy=0)";
  $Mascot_search_results = $managerDB->fetchAll($SQL);
}

if($frm_gpm_saveWell_str){
  $frm_gpm_saveWell_str = str_replace(";", ",", $frm_gpm_saveWell_str);
  $SQL = "select R.WellID, T.FileName, T.ProjectID, T.ProhitsID, R.DataFiles, R.SearchEngines, R.SavedBy from $table T, $tableSearchResults R where T.ID=R.WellID and T.ID in($frm_gpm_saveWell_str) and R.TaskID='$task_ID' and R.SearchEngines='GPM' and (R.SavedBy is NULL or R.SavedBy=0)";
  $GPM_search_results = $managerDB->fetchAll($SQL);
}
if($frm_sequest_saveWell_str){
  $frm_sequest_saveWell_str = str_replace(";", ",", $frm_sequest_saveWell_str);
  $SQL = "select R.WellID, T.FileName, T.ProjectID, T.ProhitsID, R.DataFiles, R.SearchEngines, R.SavedBy from $table T, $tableSearchResults R where T.ID=R.WellID and T.ID in($frm_sequest_saveWell_str) and R.TaskID='$task_ID' and R.SearchEngines='SEQUEST' and (R.SavedBy is NULL or R.SavedBy=0)";
  $SEQUEST_search_results = $managerDB->fetchAll($SQL);
}
if($frm_MSPLIT_saveWell_str){
  $frm_MSPLIT_saveWell_str = str_replace(";",",", $frm_MSPLIT_saveWell_str);
  $SQL = "select R.WellID, T.FileName,T.ProjectID, T.ProhitsID, R.DataFiles, R.SearchEngines, R.SavedBy from $table T, $tableSearchResults R where T.ID=R.WellID and T.ID in($frm_MSPLIT_saveWell_str) and R.TaskID='$task_ID' and R.SearchEngines='MSPLIT' and (R.SavedBy is NULL or R.SavedBy=0)";
  $MSPLIT_search_results = $managerDB->fetchAll($SQL);
}
if($frm_MSPLIT_DDA_saveWell_str){
  $frm_MSPLIT_DDA_saveWell_str = str_replace(";",",", $frm_MSPLIT_DDA_saveWell_str);
  $SQL = "select R.WellID, T.FileName,T.ProjectID, T.ProhitsID, R.DataFiles, R.SearchEngines, R.SavedBy from $table T, $tableSearchResults R where T.ID=R.WellID and T.ID in($frm_MSPLIT_DDA_saveWell_str) and R.TaskID='$task_ID' and R.SearchEngines='MSPLIT_DDA' and (R.SavedBy is NULL or R.SavedBy=0)";
  $MSPLIT_DDA_search_results = $managerDB->fetchAll($SQL);
}
if($MSPLIT_search_results && $MSPLIT_DDA_search_results){
  $MSPLIT_search_results = array_merge($MSPLIT_search_results, $MSPLIT_DDA_search_results);
}elseif(!$MSPLIT_search_results && $MSPLIT_DDA_search_results){
  $MSPLIT_search_results = $MSPLIT_DDA_search_results;
}

$tpp_gpm_saveWell_str = '';
$tpp_mascot_saveWell_str = '';
$tpp_sequest_saveWell_str = '';
$tpp_MSPLIT_saveWell_str = '';
$tpp_COMET_saveWell_str = '';
$tpp_MSFragger_saveWell_str = '';
$tpp_MSGFPL_saveWell_str = '';
$tpp_iProphet_saveWell_str = '';
 
//GPM:36473;36474;36473,36474Mascot:36473;36474



if($frm_tpp_saveWell_str){
  if(preg_match('/Mascot:([\d;,]+)/', $frm_tpp_saveWell_str, $matches)){
    $tpp_mascot_saveWell_str = $matches[1];
  }
  if(preg_match('/GPM:([\d;,]+)/', $frm_tpp_saveWell_str, $matches)){
    $tpp_gpm_saveWell_str = $matches[1];
  }
  if(preg_match('/SEQUEST:([\d;,]+)/', $frm_tpp_saveWell_str, $matches)){
    $tpp_sequest_saveWell_str = $matches[1]; 
  }
  if(preg_match('/COMET:([\d;,]+)/', $frm_tpp_saveWell_str, $matches)){
    $tpp_COMET_saveWell_str = $matches[1];
  }
  if(preg_match('/MSFragger:([\d;,]+)/', $frm_tpp_saveWell_str, $matches)){
    $tpp_MSFragger_saveWell_str = $matches[1];
  }
  if(preg_match('/MSGFPL:([\d;,]+)/', $frm_tpp_saveWell_str, $matches)){
    $tpp_MSGFPL_saveWell_str = $matches[1];
  }
  if(preg_match('/iProphet:([\d;,]+)/', $frm_tpp_saveWell_str, $matches)){
    $tpp_iProphet_saveWell_str = $matches[1];
  }
}
if(!$frm_geneLevelHits and !$Mascot_search_results and !$GPM_search_results and !$MSPLIT_search_results and !$tpp_gpm_saveWell_str and !$tpp_mascot_saveWell_str and !$tpp_sequest_saveWell_str and !$tpp_COMET_saveWell_str and !$tpp_MSFragger_saveWell_str and !$tpp_MSGFPL_saveWell_str  and !$tpp_iProphet_saveWell_str){
  $error = "no search results found for set $tableSearchResults for Mascot: $frm_mascot_saveWell_str and GPM:$frm_gpm_saveWell_str";
  $Conf->setStatus('');
}



write_Log("\nTable:$table; Search task ID: ".$Conf->TaskID. " TPP task ID: " .$Conf->TppTaskID."; Start Time: ".@date("g:i a, j M. Y"). "; \nResults: Mascot-$frm_mascot_saveWell_str; GPM-$frm_gpm_saveWell_str; TPP-$frm_tpp_saveWell_str");
//==============================================================================================
//////////////////////////////////////////////following 2 lines should be removed/////////////////////////////////////////////////
//print_r($GPM_search_results);
//print_r($Mascot_search_results);
//$Mascot_search_results = array();
//$GPM_search_results = array(); 
//$Conf->TppTaskID = 0;///////////////////////////////////////////////////////////////////////////////////////
 ///////////////////////////////////////////////////////////////////////////////////////////////////

if(!$error){
  $real_exe_time = @date("g:i a, j M. Y"); 
  echo "<pre>";
  $engine_hits_parsed = 0; 
  //***********************************************************************************************
  //1. SEQUEST :: well by well to process. if well id provide it well check if march the one in targetDB
  for($i = 0; $i < count($SEQUEST_search_results); $i++){
    $engine_hits_parsed = 1; 
    if($SEQUEST_search_results[$i]['SavedBy']) continue;
    $storage_well_ID = $SEQUEST_search_results[$i]['WellID'];
    $target_band_ID = $SEQUEST_search_results[$i]['ProhitsID'];
    $prohits_id_ok = false;
    if(!check_hitsDB($SEQUEST_search_results[$i]['ProjectID'])) continue;
    
    update_hits_searchEngines($SEQUEST_search_results[$i]['ProjectID'], $hitsDB, 'SEQUEST');
    
    if($SEQUEST_search_results[$i]['DataFiles'] and $SEQUEST_search_results[$i]['DataFiles'] != 'rawFileError'){
      $SQL = "SELECT ID, InPlate from Band where ID='".$SEQUEST_search_results[$i]['ProhitsID']."'";
      $prohits_id_ok = $hitsDB->fetch($SQL);
      if($prohits_id_ok){
        if($prohits_id_ok['InPlate']){
           if(!in_array($prohits_id_ok['InPlate'], $plate_ID_arr)){
            array_push($plate_ID_arr, $prohits_id_ok['InPlate']);
           }
         }
         //save this file
        //**********************************
        //this function is in the top included file (auto_save_one_well_fun.inc.php)
        $rt = false;
        $data_folder = STORAGE_FOLDER."Prohits_Data/sequest_search_results";
        
        $rt = save_sequest_results($SEQUEST_search_results[$i]['DataFiles'],$storage_well_ID, $target_band_ID, $Conf, $field_spliter, $Conf->SEQUEST_Value);                
        if($rt){
          $tmp_file = mysqli_escape_string($managerDB->link, $SEQUEST_search_results[$i]['DataFiles']);
          $SQL = "UPDATE $tableSearchResults SET SavedBy='".$user_id."' WHERE DataFiles='".$tmp_file."'";
          //echo $SQL;
          $managerDB->execute($SQL);
        }
        //**********************************
      }else{
         $report .= "\n".$SEQUEST_search_results[$i]['FileName']." doesn't link to a sample in $hitsDB->selected_db_name.";
      }
    }else{ //not searched
      $report .= "\n".$SEQUEST_search_results[$i]['FileName']." has no SEQUEST search results.";
    }
  }//end for gpm
  
  //***********************************************************************************************
  //2. GPM :: well by well to process. if well id provide it well check if march the one in targetDB
  for($i = 0; $i < count($GPM_search_results); $i++){
    $engine_hits_parsed = 1; 
    if($GPM_search_results[$i]['SavedBy']) continue;
    $storage_well_ID = $GPM_search_results[$i]['WellID'];
    $target_band_ID = $GPM_search_results[$i]['ProhitsID'];
    $prohits_id_ok = false;
    if(!check_hitsDB($GPM_search_results[$i]['ProjectID'])) continue;
    
    update_hits_searchEngines($GPM_search_results[$i]['ProjectID'], $hitsDB, 'GPM');
    
    if($GPM_search_results[$i]['DataFiles'] and $GPM_search_results[$i]['DataFiles'] != 'rawFileError'){
      $SQL = "SELECT ID, InPlate from Band where ID='".$GPM_search_results[$i]['ProhitsID']."'";
      $prohits_id_ok = $hitsDB->fetch($SQL);
      
      if($prohits_id_ok){
        if($prohits_id_ok['InPlate']){
           if(!in_array($prohits_id_ok['InPlate'], $plate_ID_arr)){
            array_push($plate_ID_arr, $prohits_id_ok['InPlate']);
           }
         }
        //save this file
        //**********************************
        //this function is in the top included file (auto_save_one_well_fun.inc.php)
        $rt = false;
        $rt = save_gpm_results($GPM_search_results[$i]['DataFiles'],$storage_well_ID, $target_band_ID, $Conf, $field_spliter);                
        if($rt){
          $tmp_file = mysqli_escape_string($managerDB->link, $GPM_search_results[$i]['DataFiles']);
          $SQL = "UPDATE $tableSearchResults SET SavedBy='".$user_id."' WHERE DataFiles='".$tmp_file."'";
          //echo $SQL;
          $managerDB->execute($SQL);
        }
        //**********************************
      }else{
         $report .= "\n".$GPM_search_results[$i]['FileName']." doesn't link to a sample in $hitsDB->selected_db_name.";
      }
    }else{ //not searched
      $report .= "\n".$GPM_search_results[$i]['FileName']." has no GPM search results.";
    }
  }//end for gpm
   
  
  
  //***************************************************************************************************
  //3. Mascot :: well by well to process. if prohits id is provided it well check if march the one in HitsDB
  //print_r($Mascot_search_results);exit;
  for($i = 0; $i < count($Mascot_search_results); $i++){
    $engine_hits_parsed = 1; 
    if($Mascot_search_results[$i]['SavedBy']) continue;
    $storage_well_ID = $Mascot_search_results[$i]['WellID'];
    $target_band_ID = $Mascot_search_results[$i]['ProhitsID'];
    $prohits_id_ok = false;
    if(!check_hitsDB($Mascot_search_results[$i]['ProjectID'])) continue;
    
    update_hits_searchEngines($Mascot_search_results[$i]['ProjectID'], $hitsDB, 'Mascot');
    
    if($Mascot_search_results[$i]['DataFiles'] and $Mascot_search_results[$i]['DataFiles'] != 'rawFileError'){
      $SQL = "SELECT ID, InPlate from Band where ID='".$Mascot_search_results[$i]['ProhitsID']."'";
      $prohits_id_ok = $hitsDB->fetch($SQL);
      if($prohits_id_ok){
         if($prohits_id_ok['InPlate']){
           if(!in_array($prohits_id_ok['InPlate'], $plate_ID_arr)){
            array_push($plate_ID_arr, $prohits_id_ok['InPlate']);
           }
         }
         //save this file
        //**********************************
        //this function is in the top included file (auto_save_one_well_fun.inc.php)
        $rt = false;
        //echo $storage_well_ID;exit;
        $rt = save_mascot_results($Mascot_search_results[$i]['DataFiles'],$storage_well_ID, $target_band_ID, $Conf, $field_spliter);                
        if($rt){
          $tmp_file = mysqli_real_escape_string($managerDB->link, $Mascot_search_results[$i]['DataFiles']);
          $SQL = "UPDATE $tableSearchResults SET SavedBy='".$user_id."' WHERE DataFiles='".$tmp_file."'";
          $managerDB->execute($SQL);
          
        }
        //**********************************
      }else{
         $report .= "\n".$Mascot_search_results[$i]['FileName']." doesn't link to a sample in $hitsDB->selected_db_name.";
      }
    }else{ //not searched
      $report .= "\n".$Mascot_search_results[$i]['FileName']." has no mascot search results.";
    }
  }//end for mascot  
  
  //***************************************************************************************************
  //4. MSPLIT :: well by well to process. if prohits id is provided it well check if march the one in HitsDB 
  
  for($i = 0; $i < count($MSPLIT_search_results); $i++){
    $engine_hits_parsed = 1; 
    if($MSPLIT_search_results[$i]['SavedBy']) continue;
    $storage_well_ID = $MSPLIT_search_results[$i]['WellID'];
    $target_band_ID = $MSPLIT_search_results[$i]['ProhitsID'];
    $prohits_id_ok = false;
    if(!check_hitsDB($MSPLIT_search_results[$i]['ProjectID'])) continue;
    
    
    $SearchEngines_to_log = "GeneLevel_".$MSPLIT_search_results[$i]['SearchEngines'];
    update_hits_searchEngines($MSPLIT_search_results[$i]['ProjectID'], $hitsDB, $SearchEngines_to_log);
    
    $fasta_db = '';
    $tmp_arr = explode(';',$task_arr['SearchEngines']);
    foreach($tmp_arr as $tmp_val){
      if(strstr($tmp_val, 'Database=')){
        $tmp_tem_arr = explode('=',$tmp_val);
        $fasta_db = $tmp_tem_arr[1];
      }
    }     
    
    $fasta_dir = "../../TMP/parser/".$table;
    if(!_is_dir($fasta_dir)){
      _mkdir_path($fasta_dir);
    }    
    
    $local_fasta_path = $fasta_dir."/$fasta_db.fasta";
    //$local_map_path = preg_replace('/.fasta$/', '.map', $local_fasta_path);
    
    $local_map_path = $fasta_dir."/$fasta_db.map";
    $gpm_fasta_path = get_gpm_db_file_path($fasta_db);    
     
    if(_is_file($gpm_fasta_path)){
      $local_fasta_path = $gpm_fasta_path;
    }else{
      $http_gpm_cgi_dir = "http://" . $gpm_ip . GPM_CGI_DIR;
      $tpp_formaction = $http_gpm_cgi_dir . "/Prohits_TPP.pl";
      $postData = "tpp_myaction=downloadDB&fileName=" . $fasta_db;
      echo $tpp_formaction."?".$postData;
      if(!copy($tpp_formaction."?".$postData, $local_fasta_path)){
        $msg = "failed to copy to $local_fasta_path...\n";
        write_Log($msg);
      }
    }  
    if($MSPLIT_search_results[$i]['DataFiles'] and $MSPLIT_search_results[$i]['DataFiles'] != 'rawFileError'){
      if($MSPLIT_search_results[$i]['SearchEngines']){
        $searchEngine = $MSPLIT_search_results[$i]['SearchEngines'];
      }else{
        echo "no searchEngine";
        exit;
      }      
      $SQL = "SELECT ID, InPlate from Band where ID='".$MSPLIT_search_results[$i]['ProhitsID']."'";
      $prohits_id_ok = $hitsDB->fetch($SQL);
      if($prohits_id_ok){
        if($prohits_id_ok['InPlate']){
          if(!in_array($prohits_id_ok['InPlate'], $plate_ID_arr)){
            array_push($plate_ID_arr, $prohits_id_ok['InPlate']);
          }
        }
        $fasta_geneMap_arr = fasta_map_file($local_fasta_path);                
        $thePepXML_file = $MSPLIT_search_results[$i]['DataFiles'];        
        //$to_file = $fasta_dir.'/'.basename($thePepXML_file);
         
        $to_file = $thePepXML_file;
         
        $geneLevelResults_dir = STORAGE_FOLDER."Prohits_Data/gene_parse/$table/task".$Conf->TaskID."/Results";
        if(!_is_dir($geneLevelResults_dir)){
          _mkdir_path($geneLevelResults_dir);
        }  
        $fastaFilePath = $fasta_geneMap_arr['local_fasta_path'];
        $protein_gene_map_path = $fasta_geneMap_arr['local_map_path'];
        $geneLevelResults = gene_parse($to_file, $fastaFilePath, $protein_gene_map_path, $searchEngine,$frm_geneLevelFDR,$frm_pepPROBABILITY);
        $dis_file = $geneLevelResults_dir."/".basename($geneLevelResults);

        if(!copy($geneLevelResults, $dis_file)){
          $msg = "failed to copy $dis_file...\n";
          write_Log($msg);
        }
               
        $msg = "GeneLevel hits parsed: $to_file ".@date("Y-m-d H:i:s"); 
        write_Log($msg);
        $rt = false;
        $rt = save_MSPLIT_results($geneLevelResults, $target_band_ID, $field_spliter=';;', $isUploaded=0, $searchEngine, $thePepXML_file);         
        
        unlink($geneLevelResults);
        
        if($rt){
          $tmp_file = mysqli_real_escape_string($managerDB->link, $MSPLIT_search_results[$i]['DataFiles']);
          $SQL = "UPDATE $tableSearchResults SET SavedBy='".$user_id."' WHERE DataFiles='".$tmp_file."'";
          $managerDB->execute($SQL);
          //======================================================================
          //--INSERT TO GeneLevelParse--MSPLIT geneLevel--------------------------
          $SQL = "INSERT INTO `GeneLevelParse` SET
                 `Machine`='$table', 
                 `TaskID`='".$Conf->TaskID."', 
                 `TppID`='".$Conf->TppTaskID."', 
                 `pepXML_original`='".$thePepXML_file."',
                 `pepXML_result` = '$dis_file',
                 `ProhitsID`='".$MSPLIT_search_results[$i]['ProhitsID']."', 
                 `SearchEngine`='$searchEngine', 
                 `ProjectID`='".$MSPLIT_search_results[$i]['ProjectID']."',
                 `FastaFile`='".$fasta_geneMap_arr['local_fasta_path']."', 
                 `Parsed`=1";
          $insertID = $managerDB->insert($SQL);
          //======================================================================          
        }
      }else{
         $report .= "\n".$MSPLIT_search_results[$i]['FileName']." doesn't link to a sample in $hitsDB->selected_db_name.";
      }
    }else{ //not searched
      $report .= "\n".$MSPLIT_search_results[$i]['FileName']." has no MSPLIT search results.";
    }
  }//end for MSPLIT
  
  
   
  //****************************************************************************************************
  //6. TPP :: well by well to process. if prohits id is provided it will check the id first.
  echo "start TPP ------\n";
  $tpp_hits_parsed = 0;
  if($Conf->TppTaskID and 
    ($tpp_gpm_saveWell_str or 
     $tpp_mascot_saveWell_str or 
     $tpp_sequest_saveWell_str or 
     $tpp_COMET_saveWell_str or 
     $tpp_MSFragger_saveWell_str or 
     $tpp_MSGFPL_saveWell_str or 
     $tpp_iProphet_saveWell_str
   )){
    $tpp_hits_parsed = 1;
    $gpm_id_arr = explode(";", $tpp_gpm_saveWell_str);
    $mascot_id_arr = explode(";", $tpp_mascot_saveWell_str);
    $sequest_id_arr = explode(";", $tpp_sequest_saveWell_str);
    $COMET_id_arr = explode(";", $tpp_COMET_saveWell_str);    
    $MSFragger_id_arr = explode(";", $tpp_MSFragger_saveWell_str);    
    $MSGFPL_id_arr = explode(";", $tpp_MSGFPL_saveWell_str);
    $iProphet_id_arr = explode(";", $tpp_iProphet_saveWell_str);
    
     
    foreach($gpm_id_arr as $tmp_id){
      if(trim($tmp_id)){
        $searchEngine = 'GPM';
        $rt = save_tpp_results($table, $tmp_id, 'GPM', $Conf, $field_spliter);
      }
    }
    
    foreach($mascot_id_arr as $tmp_id){
      if(trim($tmp_id)){
        $searchEngine = 'Mascot';
        $rt = save_tpp_results($table, $tmp_id, 'Mascot', $Conf, $field_spliter);
      }
    }
    foreach($sequest_id_arr as $tmp_id){
      if(trim($tmp_id)){
        $searchEngine = 'SEQUEST';
        $rt = save_tpp_results($table, $tmp_id, 'SEQUEST', $Conf, $field_spliter);
      }
    }    
    foreach($COMET_id_arr as $tmp_id){
      if(trim($tmp_id)){
        $searchEngine = 'COMET';
        $rt = save_tpp_results($table, $tmp_id, 'COMET', $Conf, $field_spliter);
      }
    }
    
    foreach($MSFragger_id_arr as $tmp_id){
      if(trim($tmp_id)){
        $searchEngine = 'MSFragger';
        $rt = save_tpp_results($table, $tmp_id, 'MSFragger', $Conf, $field_spliter);
      }
    }
    foreach($MSGFPL_id_arr as $tmp_id){
      if(trim($tmp_id)){
        $searchEngine = 'MSGFPL';
        $rt = save_tpp_results($table, $tmp_id, 'MSGFPL', $Conf, $field_spliter);
      }
    } 
    foreach($iProphet_id_arr as $tmp_id){
      if(trim($tmp_id)){
        $searchEngine = 'iProphet';
        $rt = save_tpp_results($table, $tmp_id, 'iProphet', $Conf, $field_spliter);
      }
    }
  }
  
  
   
//-----------PARSE GENELEVEL------------------------------------------------------------------------------------------
  //Parsed='0' going to parse
  //parsed='1' parsed
  //parsed='2' waiting.
  $gene_level_where_str = '';
   
  if($frm_geneLevelHits){
    if($tpp_hits_parsed){
      $gene_level_where_str = "`Parsed`='0'";
    }else if(!$engine_hits_parsed){
      $gene_level_where_str = "(`Parsed`='0' or `Parsed`='2')";
    }
    
    
    if($gene_level_where_str){
        echo "start TPP gene level------<br>\n";
        $SQL = "SELECT `ID`, 
                       `Machine`, 
                       `TaskID`, 
                       `TppID`, 
                       `pepXML_original`, 
                       `pepXML`, 
                       `ProhitsID`, 
                       `SearchEngine`, 
                       `ProjectID`, 
                       `isUploaded`, 
                       `FastaFile`, 
                       `Parsed` 
               FROM `GeneLevelParse` 
               WHERE `Machine`='$table'
               AND `TaskID`='".$Conf->TaskID."' 
               AND `TppID`='".$Conf->TppTaskID."'
               AND $gene_level_where_str";    
         
        $geneNotParsed_arr = $managerDB->fetchAll($SQL);
         
        $fasta_dir = "../../TMP/parser";
        //print_r($geneNotParsed_arr); exit;
        foreach($geneNotParsed_arr as $val){
          
          $theProjectID = $val['ProjectID'];
          if(!check_hitsDB($theProjectID)) continue;
      
          $theID = $val['ID'];
          $theTaskID = $val['TaskID'];
          $theTppID = $val['TppID'];
          $thePepXML_file = $val['pepXML'];
          $fastaFilePath = $val['FastaFile'];
          $fasta_basename = basename($fastaFilePath);
          $map_basename = preg_replace('/.fasta$/', '.map', $fasta_basename);
          $protein_gene_map_path = $fasta_dir."/".$map_basename; 
          
          //$protein_gene_map_path = preg_replace('/.fasta$/', '.map', $fastaFilePath);
          
          $theProhitsID = $val['ProhitsID'];
          $isUploaded = $val['isUploaded'];
          $searchEngine = $val['SearchEngine'];
          $pepXML_original = $val['pepXML_original'];
          
          if(!_is_file($thePepXML_file)){
            $tmp_XML_file = preg_replace("/[0-9]+_task".$theTaskID."_tpp".$theTppID."_/", "", $thePepXML_file);
            if(!_is_file($tmp_XML_file)){
              $tmp_XML_file = getTppXML($pepXML_original, $val['Machine']);
            }
            if(rename($tmp_XML_file, $thePepXML_file)){
              write_Log("couldn't change file name: $thePepXML_file");
              continue;
            }
          }
          echo "make geneLeve results file\n";      
          
          $geneLevelResults = gene_parse($thePepXML_file, $fastaFilePath, $protein_gene_map_path,'TPP',$frm_geneLevelFDR, $frm_pepPROBABILITY);
          
          //echo $geneLevelResults;exit;
          //check database connectoin--------------------
          if(!mysqli_ping($prohitsDB->link)){
            $prohitsDB = new mysqlDB(PROHITS_DB);
          }
          if(!mysqli_ping($hitsDB->link)){
            $hitsDB = new mysqlDB(PROHITS_DB);
            if(!check_hitsDB($theProjectID)) continue;
          }
          if(!mysqli_ping($proteinDB->link)){
            $proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);
          }
          if(!mysqli_ping($managerDB->link)){
            $managerDB = new mysqlDB(MANAGER_DB);
          }
          //----------------------------------------------
          
          $geneLevelResults_dir = STORAGE_FOLDER."Prohits_Data/gene_parse/$table/task".$Conf->TaskID."/tpp".$Conf->TppTaskID;
          if(!_is_dir($geneLevelResults_dir)){
            _mkdir_path($geneLevelResults_dir);
          }
          $dis_file = $geneLevelResults_dir."/".basename($geneLevelResults);
          if(!copy($geneLevelResults, $dis_file)){
            $msg = "failed to copy $dis_file...\n";
            write_Log($msg);
          }else{
            $msg = "GeneLevel hits parsed: $thePepXML_file ".@date("Y-m-d H:i:s"); 
            write_Log($msg);
          }
          $rt_gn = save_MSPLIT_results($geneLevelResults, $theProhitsID,  $field_spliter=';;', $isUploaded=0, $searchEngine, $pepXML_original);
          
          unlink($geneLevelResults);
          
          if(!$rt_gn){
            $msg = "cannot create gene level result file.";
            echo "$msg<br>";
            write_Log($msg);
          }else{
            $msg = "GeneLevel hits parsed: $thePepXML_file ".@date("Y-m-d H:i:s"); 
            write_Log($msg);
          //----INSERT TO GeneLevelParse--MSPLIT geneLevel--------------------      
            $SQL = "UPDATE `GeneLevelParse` SET 
                    `Parsed`='1', 
                    `pepXML_result` = '$dis_file'
                    WHERE `ID`='$theID'";
            $managerDB->update($SQL);
            //------------------------------------------------------------------
            if($theProjectID && $searchEngine){
              $searchEngine_GeneLevel = 'GeneLevel_'.$searchEngine;
              update_hits_searchEngines($theProjectID, $hitsDB, $searchEngine_GeneLevel);
            }
          }
          
        }
    }
  }  
  
  //**************************
  // 7. set processing status.
  $real_end_time = @date("g:i a, j M. Y");
  $Conf->setStatus('Completed');
   
  if($plate_ID_arr){
    foreach($plate_ID_arr as $Plate_ID){
      add_plate_carry_over($hitsDB, $Plate_ID);
      $SQL = "update Plate set MSDate=now() where ID='$Plate_ID' and (MSDate is null or MSDate='')";
      $hitsDB->update($SQL);
    }
  }
}//end or no error
write_Log("Table:$table; Task ID: ". $Conf->TaskID. "; End Time: ".@date("Y-m-d G:i:s"));

//----------------end of the script --------------------------------

//------------------------------------------------------------------
function send_mail($to, $msg,  $subject='', $from='', $replayTo=''){
//------------------------------------------------------------------
  if(!$to or !$msg){
    echo 'need $to or $msg to send a email!';
    exit;
  }
  mail($to, $subject, $msg, "From: $from\r\n"."Reply-To: $replayTo\r\n");
}
//-------------------------------------
function check_hitsDB($the_project_ID){
//-------------------------------------
  global $Pro_ID_names;
  global $Pro_ID_dbName;
  global $hitsDB;
  global $managerDB;
  global $prohitsDB;
  global $HITS_DB;
  global $table;
  
  $rt = true;
   
  if($the_project_ID){
    if(isset($Pro_ID_names[$the_project_ID]) and !isset($Pro_ID_dbName[$the_project_ID])){
      $Pro_ID_dbName = get_projectID_DBname_pair($prohitsDB, $the_project_ID);
    }
    if(!isset($Pro_ID_dbName[$the_project_ID])){
       $rt = false;
    }else if(isset($HITS_DB) and $Pro_ID_dbName[$the_project_ID] != $hitsDB->selected_db_name){
      $hitsDB = new mysqlDB($HITS_DB[$Pro_ID_dbName[$the_project_ID]]);
    }
  }else{ 
    $rt = false;
  }
  if(!$rt){
    $msg =  "Warning: project " . $Pro_ID_names[$the_project_ID] . " hits cannot be parsed. the user may have no insert permition for the project\n";
    write_Log($msg);
  }else{
    //echo $Pro_ID_names[$the_project_ID]."\n";
  }
  return $rt;
}

function update_hits_searchEngines($project, $hitsDB, $searchEngine){
  global $project_searchEngine_arr;
  $project_searchEngine = $project."_".$searchEngine;
  if(!in_array($project_searchEngine, $project_searchEngine_arr)){
    hits_searchEngines('update', $project, $hitsDB, $searchEngine);
    $project_searchEngine_arr[] = $project_searchEngine;
  }
}

function create_fasta_map_file($thePepXML_file){

  global $task_arr;
  $fasta_path = get_gpm_db_file_path('', $task_arr['SearchEngines']);
  if(!$fasta_path){
    $cmd = "grep $thePepXML_file -e 'search_database local_path'";
    exec($cmd, $output);
    foreach($output as $line){
      if(preg_match("/local_path=\"([^\"]+)/", $line, $matches)){
        $fasta_path = $matches[1];
        break;
      }
    }
  }
  if(!$fasta_path){
    $msg = "failed to make fasta map file. no fasta path found.\n";
    write_Log($msg);
    return 0;
  }
  $local_fasta_path = $fasta_path;
  $local_map_path = str_replace(".fasta", ".map", $local_fasta_path);
  
  $ret_arr['local_fasta_path'] = $local_fasta_path;
  $ret_arr['local_map_path'] = $local_map_path;  
  if(is_file($local_fasta_path) && is_file($local_map_path)){
    return $ret_arr;
  }
  if(!is_file($local_fasta_path)){  
    $tpp_in_prohits = is_in_local_server('TPP');
    if($tpp_in_prohits){
      $local_fasta_path = $fasta_path;
    }else{
      $http_gpm_cgi_dir = "http://" . $gpm_host . GPM_CGI_DIR;
      $tpp_formaction = $http_gpm_cgi_dir . "/Prohits_TPP.pl";
      if(!$fasta_path) return '';
      
      $postData = "tpp_myaction=downloadTppXML&fileName=" . $fasta_path;
    
      if(!copy($tpp_formaction."?".$postData, $local_fasta_path)){
        $msg = "failed to copy to $local_fasta_path...\n";
        write_Log($msg);
      }
    }
  }
   
  $ret_arr = fasta_map_file($local_fasta_path);
  
  return $ret_arr;
}

function fasta_map_file($fasta_path){
  //it will make map file when the fata file is newer.
  global $table;
  $proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
  $filter_prefix_arr = array('^>DECOY','^>REV');
  $prefix_patten = implode('|',$filter_prefix_arr);
//echo "\$fasta_path=$fasta_path<br>";   
  $fasta_fd = fopen($fasta_path,"r");
  if(!$fasta_fd){
    $msg = "The fasta file $fasta_path can not open.";
    write_Log($msg);
    return false;
  }
  $map_dir = "../../TMP/parser";
  
  $fasta_base_name = basename($fasta_path);
  $map_base_name = str_replace(".fasta", ".map", $fasta_base_name);
  $local_map_path = $map_dir."/".$map_base_name;
//echo "\$local_map_path=$local_map_path<br>";  
  if(_is_file($local_map_path)){
    $fasta_path_Mtime = filemtime($fasta_path);
    $local_file_Mtime = filemtime($local_map_path);
    $time_diff = $local_file_Mtime - $fasta_path_Mtime;
    if($time_diff > 0){
      $ret_arr['local_fasta_path'] = $fasta_path;
      $ret_arr['local_map_path'] = $local_map_path;
//echo "do not create new map file<br>";
      return $ret_arr;
    }
  }
//echo "create new map file<br>"; 
  $map_fd = fopen($local_map_path,"w");
  if(!$map_fd){
    $msg = "The map file $local_map_path can not open.";
    write_Log($msg);
    return false;
  }
  
  $line_num = 0;
  $unique_protein_arr = array();
  while($data = fgets($fasta_fd)){
    $data = trim($data);

    $gene_info = array('GeneID'=>'','GeneName'=>'','ProteinID'=>'','ProteinType'=>'');
    $has_gene = 0;
    if(preg_match('/^[^>]/', $data, $matches)){
      continue;
    }elseif(preg_match('/^>DECOY/', $data, $matches)){
      continue;
    }elseif(preg_match('/\|gn\|(.*?):(.+?)\|*\s/', $data, $matches)){
      $gene_info['GeneID'] = $matches[2];
      $gene_name = get_Gene_Name($gene_info['GeneID'], $proteinDB);
      $gene_info['GeneName'] = $gene_name;
      $tmp_arr = preg_split("/\|gn\|.*?:/", $data);
      $tmp_ProteinID_str = str_replace(">", "", $tmp_arr[0]);
      $protein_id_arr = explode("|", $tmp_ProteinID_str);
      $has_gene = 1;
    }elseif(preg_match('/^>(.+)/', $data, $matches)){
      $tmp_arr = explode(" ",  $matches[1]);
      $protein_id_arr = explode("|", $tmp_arr[0]);
    }
    foreach($protein_id_arr as $protein_id_val){     
      if(strlen($protein_id_val) > 3){
        $tmp_inner_arr = explode(':',$protein_id_val);
        if(count($tmp_inner_arr) == 2){
          $ProteinID = $tmp_inner_arr[1];
        }else{
          $ProteinID = $protein_id_val;
        }
        break;
      }
    } 
    
    if(!array_key_exists($ProteinID, $unique_protein_arr)){
      $unique_protein_arr[$ProteinID] = '';
    }else{
      continue;
    }    
    $line_num++;   
    if($has_gene){
      $gene_info['ProteinID'] = $ProteinID;
      $gene_info['ProteinType'] = get_protein_ID_type($ProteinID);
    }else{
      $gene_info = get_protein_and_gene_info($ProteinID);
    }     
    if($gene_info['GeneID']){
      $map_line = $gene_info['GeneID'].','.$gene_info['GeneName'].','.$gene_info['ProteinID'].','.$gene_info['ProteinType'];
    }else{
      $map_line = $gene_info['ProteinID'].','.$gene_info['ProteinID'].','.$gene_info['ProteinID'].','.$gene_info['ProteinType'];
    }
    fwrite($map_fd,$map_line."\r\n");
    //if($line_num >1000) break;
  }

  $ret_arr['local_fasta_path'] = $fasta_path;
  $ret_arr['local_map_path'] = $local_map_path;
  return $ret_arr;
}

function get_protein_and_gene_info($protein_id){
  global $proteinDB;
  global $gene_info;
  $proteinType = get_protein_ID_type($protein_id);
  
  $gene_info['ProteinID'] = $protein_id;
  $gene_info['ProteinType'] = $proteinType;
  $gene_info['GeneID'] = '';
  $gene_info['GeneName'] = '';  
  
  $table_field = array();
  $protein_info_arr = array();
  $gene_id = '';
  if($proteinType != 'NCBIAcc'){
    $table_field = _get_acc_table_fields($proteinType, $protein_id);
    if($table_field['geneID_field'] == 'ENSG'){
      $table_field['geneID_field'] = 'EntrezGeneID';
    }
    if($table_field['match_field'] != 'Acc' && $table_field['match_field'] != 'UniProtID'){
      $order_by_str = " order by SequenceID desc limit 1";
    }else{
      $order_by_str = " order by Acc_Version desc limit 1";
    }
    $SQL = "SELECT ".$table_field['id_field'].", Description, ".$table_field['geneID_field'].$table_field['other_fields']." from ".$table_field['acc_tableName']." 
            WHERE ".$table_field['match_field']."='".$protein_id."' $order_by_str"; //========
    $protein_info_arr = $proteinDB->fetch($SQL);
    if(!$gene_id && $protein_info_arr && $protein_info_arr[$table_field['geneID_field']]){
      $gene_id = $protein_info_arr[$table_field['geneID_field']];
    }
    if($table_field['match_field'] == 'Acc' && strstr($protein_id, '-') && !$protein_info_arr){
      $tmp_Acc_arr = explode('-',$protein_id);
      if(count($tmp_Acc_arr) == 2){
        $tmp_protein_id = $tmp_Acc_arr[0];
        $SQL = "SELECT ".$table_field['geneID_field']." 
                FROM ".$table_field['acc_tableName']." 
                WHERE ".$table_field['match_field']."='$tmp_protein_id' 
                AND ".$table_field['geneID_field']."!= 0
                AND ".$table_field['geneID_field']." IS NOT NULL";
        $protein_geneid_arr_tmp = $proteinDB->fetch($SQL);
        if(isset($protein_geneid_arr_tmp[$table_field['geneID_field']])){
          $gene_id = $protein_geneid_arr_tmp[$table_field['geneID_field']]; 
        }
      }
    }
    if(!$gene_info['GeneID'] && $gene_id){
      $gene_info['GeneID'] = $gene_id;
    }
    if(!$gene_info['GeneID'] && $gene_id){
      $gene_info['GeneID'] = $gene_id;
    }
    if($gene_info['GeneID']){
      $gene_name = get_Gene_Name($gene_info['GeneID'], $proteinDB);
      $gene_info['GeneName'] = $gene_name;
    }
  }
  return $gene_info;
}
?>