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

 
function get_gpm_db_arr($hide_db_arr=array()){
  global $gpm_ip;
  $gpm_dbs = array();
  
  if(defined("MASCOT_IP") and MASCOT_IP){
    $mascot_default = get_mascot_default_param("./autoSearch/", 1);
    if(!isset($mascot_default['OPTIONS_DB'])){
      echo "Error: please check Mascot setting in Prohits conf file";exit;
    }
    $mascot_info['DB'] = explode(";;", $mascot_default['OPTIONS_DB']);
  }
  if(!$gpm_ip){
    $gpm_ip = $_SERVER['SERVER_NAME'];
  }
  
  $sp_file = "http://".$gpm_ip."/tandem/species.js";
  
  if(GPM_CGI_PATH){
    $sp_file = dirname(GPM_CGI_PATH)."//tandem/species.js";
  }
   
  $lines = file($sp_file);
  
  foreach($lines as $line){
    $line = trim($line);
    if(strpos($line, "//") === 0) continue;
    $line = str_replace("\\", '', $line);
    if(preg_match_all("/<option .*value=[\"']?([^\"'>]*)[\"']?[ ]?[>]([^<]*)/i", $line, $matches)){
       
      $db_name = $matches[1][0];
      $db_label = $matches[2][0];
      if(!$db_name or in_array($db_label, $hide_db_arr)) continue;
      if(defined("MASCOT_IP") and MASCOT_IP){
        if(in_array($db_name, $mascot_info['DB'])){
          $gpm_dbs['name'][] = $db_name;
          $gpm_dbs['label'][] = $db_label;
        }
      }else if($db_name){
        $gpm_dbs['name'][] = $db_name;
        $gpm_dbs['label'][] = $db_label;
      }
    }
  }
  if(!isset($gpm_dbs['name'])){
      echo "<font color=red>Error:</font> Mascot databases should have the same db names and fasta files in $sp_file.
      <br>Please read instruction \"Add databases to theGPM\" from Prohits/install/GPM/install_TPP_GPM.html";
      exit;
  }
  return $gpm_dbs;
}

function get_amino_mass_arr($mono_or_ave = 'mono'){
  $Residue_mono = array();
  $Residue_mono["A"] = 71.03711;  $Residue_ave["A"] = 71.0788;
  $Residue_mono['B'] = 114.53493; $Residue_ave['B'] = 114.59625;
  $Residue_mono['C'] = 103.00919; $Residue_ave['C'] = 103.1388;
  $Residue_mono['D'] = 115.02694; $Residue_ave['D'] = 115.0886;
  $Residue_mono['E'] = 129.04259; $Residue_ave['E'] = 129.1155;
  $Residue_mono['F'] = 147.06841; $Residue_ave['F'] = 147.1766;
  $Residue_mono['G'] =  57.02146; $Residue_ave['G'] =  57.0520;
  $Residue_mono['H'] = 137.05891; $Residue_ave['H'] = 137.1412;
  $Residue_mono['I'] = 113.08406; $Residue_ave['I'] = 113.1595;
  $Residue_mono['J'] =   0.0;     $Residue_ave['J'] =    0.0;
  $Residue_mono['K'] = 128.09496; $Residue_ave['K'] = 128.1742;
  $Residue_mono['L'] = 113.08406; $Residue_ave['L'] = 113.1595;
  $Residue_mono['M'] = 131.04049; $Residue_ave['M'] = 131.1925;
  $Residue_mono['N'] = 114.04293; $Residue_ave['N'] = 114.1039;
  $Residue_mono['O'] =   0.0    ; $Residue_ave['O'] = 0.0;
  $Residue_mono['P'] =  97.05276; $Residue_ave['P'] =  97.1167;
  $Residue_mono['Q'] = 128.05858; $Residue_ave['Q'] = 128.1308;
  $Residue_mono['R'] = 156.10111; $Residue_ave['R'] = 156.1876;
  $Residue_mono['S'] =  87.03203; $Residue_ave['S'] =  87.0782;
  $Residue_mono['T'] = 101.04768; $Residue_ave['T'] = 101.1051;
  $Residue_mono['U'] =   0.0    ; $Residue_ave['U'] = 0.0;
  $Residue_mono['V'] =  99.06841; $Residue_ave['V'] =  99.1326;
  $Residue_mono['W'] = 186.07931; $Residue_ave['W'] = 186.2133;
  $Residue_mono['X'] = 111.0    ; $Residue_ave['X'] = 111.0;
  $Residue_mono['Y'] = 163.06333; $Residue_ave['Y'] = 163.1760;
  $Residue_mono['Z'] = 128.55059; $Residue_ave['Z'] = 128.62315;
  if($mono_or_ave == 'mono'){
    return $Residue_mono;
  }else{
    return $Residue_ave;
  }
}
if(!function_exists('get_userName')){
function get_userName($userID = 0){
  global $prohitsDB;
  if(!$prohitsDB or !$userID) return '';
  $SQL = "select Fname, Lname from User WHERE ID='$userID'";
  $nameArr = $prohitsDB->fetch($SQL);
  $userFullName = ''; 
  if($nameArr){
    $userFullName = $nameArr['Fname']." ".$nameArr['Lname'];
  }
  return $userFullName;
}
}
function get_search_parameters($type='', $ID=0 , $order='', $Machine='', $is_SWATH=0){
  global $managerDB;
  global $USER;
  $para_arr = array();
  if(!$managerDB or !$type) return $para_arr;
  $SQL = "SELECT `ID`, `Name`, `Type`, `User`, `Date`, `ProjectID`, `Parameters`, `SWATH`, `Default`, `Machine`, `Description` FROM `SearchParameter` 
          Where Type='".$type."'";
  if($ID){
    $SQL .= " and ID='".$ID."'";
    return $managerDB->fetch($SQL);
  }else{
    if($Machine){
      $SQL .= " and Machine='".$Machine."'";
    }
    if($is_SWATH){
      if($is_SWATH != 'All'){
        $SQL .= " and SWATH=1";
      }
    }else{
      $SQL .= " and ( SWATH=0 or SWATH is NULL)";
    }
    if($type == 'PARSER'){
       $SQL .= " and User = '".$USER->ID."'";
    }
  }
  $SQL .= " order by ID";
 
  return $managerDB->fetchAll($SQL);
}

function search_para_set_name_exist($type='', $Name = ''){
  global $managerDB;
  if(!$managerDB or !$type) return false;
  if(preg_match("/(.+;;).+$/", $Name, $matches)){
    $SQL = "SELECT `ID`, `Name` FROM `SearchParameter` Where Type='".$type."' and Name like '".mysqli_real_escape_string($managerDB->link, $matches[1])."%'";
  }else{
    $SQL = "SELECT `ID`, `Name` FROM `SearchParameter` Where Type='".$type."' and Name='".mysqli_real_escape_string($managerDB->link, $Name)."'";
  }
  $tmp_arr = $managerDB->fetchAll($SQL);
  foreach($tmp_arr as $tmp_val){
    if($tmp_val['Name'] == $Name) return true;
  }
  return false;
}

 

function search_para_add_modify($type, $ID, $Name, $UserID, $parameter, $is_SWATH=0, $is_default=0, $machine='', $description='', $SearchEngine=''){
  global $managerDB;
  global $is_defult_set;

  if($is_default){
    //remove default
    $SQL = "UPDATE `SearchParameter` SET `Default`=0 WHERE Type='$type' ";
    if($type == 'TPP'){
      if($is_SWATH){
        $SQL .= "and SWATH=1";
      }else{
        $SQL .= "and ( SWATH=0 or SWATH is NULL)";
      }
    }else if($type == 'Converter'){
      if($is_SWATH){
        $SQL .= "and SWATH=1";
      }else{
        $SQL .= "and ( SWATH=0 or SWATH is NULL)";
      }
      $SQL .= " and Machine='$machine'";
    }
    $managerDB->update($SQL);
  }
  
  if($SearchEngine){
    $SearchEngine = strtoupper($SearchEngine); 
    if($ID){
      $SQL = "SELECT `Parameters` FROM `SearchParameter` WHERE `ID`='$ID'";
      $tmp_para_arr = $managerDB->fetch($SQL);
      $tmp_para_arr2 = explode("\n",$tmp_para_arr['Parameters']);
      $SearchEngine_exist = 0;
      foreach($tmp_para_arr2 as $key => $tmp_para_val2){
        $tmp_para_arr3 = explode("===",$tmp_para_val2);
        if($tmp_para_arr3[0] == $SearchEngine){
          $tmp_para_arr2[$key] = $SearchEngine."===".$parameter;
          $SearchEngine_exist = 1;
          break;
        }
      }
      if(!$SearchEngine_exist){
        $tmp_para_arr2[] = $SearchEngine."===".$parameter;
      }
      $parameter = implode("\n", $tmp_para_arr2);  
    }else{
      $parameter = "\n".$SearchEngine."===".$parameter;
    }
  }  
  



  $sql_value = "`SearchParameter` set "; 
  if(!$ID){
    $sql_value .= " `Name`='".addslashes($Name)."',";
	  $sql_value .= " `Type`='".$type."', ";
	  $sql_value .= " `User`='".$UserID."',";
  }
  $sql_value .= " `SWATH`='".$is_SWATH."',";
  $sql_value .= " `Default`='".$is_default."',";
  $sql_value .= " `Machine`='".$machine."',";
  $sql_value .= " `Description`='".mysqli_real_escape_string($managerDB->link, $description)."',";
  $sql_value .= " `Date`=now(),";
  $sql_value .= " `Parameters`='".mysqli_real_escape_string($managerDB->link, $parameter)."'";
  if($ID){
    $SQL = "Update ".$sql_value." where ID='".$ID."'";
    $managerDB->update($SQL);
  }else{
    if(!search_para_set_name_exist($type, $Name)){
      $SQL = "insert into ".$sql_value;
      $ID = $managerDB->insert($SQL);
    }
  } 
  return $ID;
}

function task_is_running($tableName='', $taskID='', $isTPP=''){
  global $storage_ip;
  $rt = false;
  if(!$tableName) return $rt;
  
  if(function_exists('getPhpProcess_arr')){
    return getPhpProcess_arr($tableName, $taskID);
    exit;
  }
  if($storage_ip == 'localhost' or !$storage_ip){
    $storage_ip = $_SERVER['SERVER_NAME'];
  }
  $queryString = "tableName=$tableName";
  if($taskID){
    $queryString .= "&taskID=$taskID";
  }
  if($isTPP){
    $queryString .= "&isTPP=Yes";
  }
  $file = "http://" .$storage_ip . dirname($_SERVER['PHP_SELF']) . "/auto_run_shell.php?$queryString";
  $lines = file($file);
  foreach($lines as $line){
    if(preg_match("/>>Yes<</", $line, $matches)){
      $rt = true;
    }
  }
  return $rt;
}
function send_task_to_shell($table, $perm_insert, $theTaskID ){
  global $storage_ip;
  $msg = '';
  if($perm_insert and $theTaskID){
    //process search now. 
    $file = "http://" .$storage_ip . dirname($_SERVER['PHP_SELF']) . "/autoSearch/auto_search_table_shell.php?tableName=".$table."&SID=".session_id();
    
    if($theTaskID){
      $file .= "&frm_theTaskID=$theTaskID";
    }
 
    $handle = fopen($file, "r");
    while (!feof($handle)) {
      $msg .= fgets($handle, 4096);
    }
    fclose($handle);
  }
  return $msg;
}
/**********************************
 check storage computer connection
***********************************/
function check_stoage_computer(){
  global $process_storage_folder_url;
  $error = '';
  if(STORAGE_IP == PROHITS_SERVER_IP){
    if(!_is_dir(STORAGE_FOLDER)){
      $error = "<b>Error</b>: Prohits cannot connect to storage folder.";
    }
  }else{
    check_prohits_web_root();
    $storage_folder = STORAGE_FOLDER;
    $storage_folder = add_folder_backslash($storage_folder);
    $action = "&action=isDir";
    $path = $storage_folder;
    $timeout = 3;
    $old = ini_set('default_socket_timeout', $timeout);
    if($fd = @fopen($process_storage_folder_url, 'r')){
      fclose($fd);
      $storage_folder_arr = file($process_storage_folder_url.$path.$action);
      if(!$storage_folder_arr[0]){
        $error .= "The Storage folder is not writable";
      }else if($storage_folder_arr[0] == '2'){
        $error .= "The Storage folder doesn't exist.";
      }
    }else{
      $error = "<b>Error</b>: Prohits cannot connect to storage computer.";
    }
    ini_set('default_socket_timeout', $old);
  }
  return $error;
}

/*******************************************
check if the download package setup
$DOWNLOAD_PACKAGE_FOLDER is defined in conf file
*******************************************/
function _check_download_package_folder(){
  global $PHP_SELF;
  global $DOWNLOAD_PACKAGE_FOLDER;
  global $process_storage_folder_url;
  $rt = array('activated'=>'', 'error'=>'', 'path'=>'');
  if( $_SERVER['HTTP_HOST'] == STORAGE_IP){
    if(is_writable($DOWNLOAD_PACKAGE_FOLDER['SOURCE'])){
      $rt['activated']= true;
      $rt['path']= $DOWNLOAD_PACKAGE_FOLDER;
    }
    return $rt;
  }
  check_prohits_web_root();
  if(isset($DOWNLOAD_PACKAGE_FOLDER['SOURCE']) and $DOWNLOAD_PACKAGE_FOLDER['SOURCE']){
    $path = add_folder_backslash($DOWNLOAD_PACKAGE_FOLDER['SOURCE']);
    $action = '&action=isEmpty_or_unexist';
    $tmp_arr = file($process_storage_folder_url.$path.$action);
    if($tmp_arr[0] === '0'){
      $action = '&action=isWritable';
      $tmp_arr = file($process_storage_folder_url.$path.$action);
      if(!$tmp_arr[0] or $tmp_arr[0] == '2'){
        $rt['error'] .= "The folder is not writable($path)";
      }else{
        $rt['path'] = $path;
        $rt['activated'] = true;
      }
    }else if($tmp_arr[0] == '2'){
      $rt['error'] .= "Prohits lost connection with DOWNLOAD_PACKAGE folder data folder. <br>The source directory is empty.";
    }else if($tmp_arr[0] == '1'){
      $rt['error'] .= "Prohits lost connection with DOWNLOAD_PACKAGE folder data folder. <br>The source directory doesn't exist.";
    }else if(strpos($tmp_arr[1], 'Maximum execution time')){
      $rt['error'] .= "Prohits lost connection with DOWNLOAD_PACKAGE folder data folder. <br>The source directory should be mounted after umounted.";
    }
  }
  return $rt;
}

/*******************************************
check user raw file export tmp folder
return folder path
*******************************************/
function _check_user_raw_export_folder($userName){
  $user_raw_export_dir = '';
  if($user_tmp_dir = check_user_tmp_folder($userName)){
     $user_raw_export_dir = $user_tmp_dir ."raw_export";
     if(!_is_dir($user_raw_export_dir)){
        if(!mkdir ($user_raw_export_dir, 0777, true)){
          echo "Apache user cannot create tmp folder ".$user_raw_export_dir . ". Please contact Prohits admin.";exit;
        }
     }
  }
  if(!is_writable($user_raw_export_dir)){
    echo "Apache user cannot write file to tmp folder ".$user_raw_export_dir . ". Please contact Prohits admin.";exit;
  }
  return $user_raw_export_dir;
}
/*******************************************
check user prohits tmp folder 
return folder path
********************************************/
function _check_user_tmp_folder($userName){
  $user_tmp_dir = '';
  $prohits_root = pathinfo(__FILE__,PATHINFO_DIRNAME);
  $prohits_root = str_replace("msManager","",$prohits_root);
  $user_tmp_dir = add_folder_backslash($prohits_root) . "TMP/$userName/";
  if(!_is_dir($user_tmp_dir )){
    if(!mkdir ( $user_tmp_dir, 0777, true)){
      echo "Apache user cannot create tmp folder ". $user_tmp_dir . ". Please contact Prohits admin.";exit;
    }
  }
  return $user_tmp_dir;
}
/*******************************************
 check backup machine setup and connections
********************************************/
function check_backup($baseTable, $var_arr){
  global $process_storage_folder_url;
  
  global $prohits_root;
  global $prohits_web_root;
  check_prohits_web_root();
  
  if(defined('STORAGE_IP_OLD') and STORAGE_IP_OLD){
    //the old still using for auto-backup.
    //only for mshri.on.ca
    $process_storage_folder_url = 'http://'.STORAGE_IP_OLD .'/Prohits/msManager/process_storage_folder.php?path=';
  }
  
  $logo_dir_web_path = $prohits_web_root."/msManager/images/msLogo/";
  $rt = array('msg'=>'', 'error'=>'');
  if(!$var_arr['SOURCE']){
    $no_backup_set = 'Y';
    $rt['msg']="No backup setup.\n";
  }else{
    $var_arr['SOURCE'] = add_folder_backslash($var_arr['SOURCE']);
    $path = $var_arr['SOURCE'];
    $action = '&action=isEmpty_or_unexist';
    $tmp_arr = file($process_storage_folder_url.$path.$action);
     
    if($tmp_arr[0] === '0'){
      //it is ok
    }else if($tmp_arr[0] == '2'){
      $rt['error'] .= "Prohits lost connection with $baseTable data folder. <br>The source directory is empty.";
    }else if($tmp_arr[0] == '1'){
      $rt['error'] .= "Prohits lost connection with $baseTable data folder. <br>The source directory doesn't exist.";
    }else if(strpos($tmp_arr[1], 'Maximum execution time')){
      $rt['error'] .= "Prohits lost connection with $baseTable data folder. <br>The source directory should be mounted after umounted.";
    }
  }
  if(preg_match("/\/$/", STORAGE_FOLDER, $matches)){
    $path = STORAGE_FOLDER.$baseTable."/";
  }else{
    $path = STORAGE_FOLDER."/".$baseTable."/";
  }
  $action = '&action=isWritable';
  if(defined('STORAGE_IP_OLD') and STORAGE_IP_OLD){
     //the old still using for auto-backup. don't check if wriable.
     //it is only for mshril.on.ca
  }else{
    $tmp_arr = file($process_storage_folder_url.$path.$action);
    if(!$tmp_arr[0] or $tmp_arr[0] == '2'){
      $rt['error'] .= "The backup destination folder ($baseTable) is not writable($path)";
    }
  }
  return $rt;
}
function add_folder_backslash($folder){
  if(!preg_match("/^\//",$folder)){
    $folder = "/" . $folder;
  }
  if(!preg_match("/\/$/",$folder)){
    $folder .= "/";
  }
  return $folder;
}
function check_prohits_web_root(){
  global $PHP_SELF;
  global $process_storage_folder_url;
  global $prohits_web_root;
  global $prohits_root;
  global $storage_ip;
  global $PROHITS_IP;

  if(!$prohits_root){
    $prohits_root = str_replace("msManager","",dirname(__FILE__));
  }
  if(!$storage_ip){
    $storage_ip = STORAGE_IP;
  }
  if(!$prohits_web_root){
    
    $this_web_dir = pathinfo($PHP_SELF,PATHINFO_DIRNAME);
    $prohits_web_root = dirname($this_web_dir);
    $prohits_web_root = str_replace("/msManager","",$prohits_web_root);
  }
  if(!$process_storage_folder_url){
    $process_storage_folder_url = "http://".$storage_ip.$prohits_web_root."/msManager/process_storage_folder.php?path=";
  }
}

function check_search_engine_url($serchEngine, $in_local=''){
  $rt = '';
  $timeout = 2;
  global $gpm_version;
  global $mascot_version;
  global $tpp_version;
  global $philosopher_version;
  global $sequest_version;
  global $converter_version;
  global $comet_version;
  global $msgfpl_version;
  global $diaumpire_version;
  global $msumpire_version;
  global $msplit_version;
  global $msfragger_version;
  
  global $gpm_in_prohits;
  global $tpp_in_prohits;
  global $comet_in_prohits;
  global $msgfpl_in_prohits;
  global $msplit_in_prohits;
  global $msfragger_in_prohits;
  global $diaumpire_in_prohits;
  global $gpm_ip;
  
  
  $old = ini_set('default_socket_timeout', $timeout);
  if($serchEngine == 'mascot'){
    if(!MASCOT_IP){
      $rt =  'Mascot IP is empty';
    }else{
      $url = "http://".MASCOT_IP.add_folder_backslash(MASCOT_CGI_DIR)."/ProhitsMascotParser.pl";
      if(!$fd=@fopen($url, 'r')){
        $rt = 'Prohits can not connection Mascot: '.MASCOT_IP;
      }else{
        while (! feof ($fd)) {
          $the_line= trim(fgets($fd));
          if(strpos($the_line, "Version") === 0){
            $mascot_version = $the_line;
            break;
          }
        }
        fclose($fd);
      }
    }
  }else if($serchEngine == 'gpm'){
    $output = array();
    if($gpm_in_prohits){
      $cmd = preg_replace("/\/$/", "", TPP_BIN_PATH)."/tandem";
      exec("$cmd 2>&1", $output);
    }else{
      $url = "http://".$gpm_ip.add_folder_backslash(GPM_CGI_DIR)."Prohits_TPP.pl";
      //echo $url;
      $output = @file($url);
    }
    //print_r($output);exit;
    if($output){
      foreach($output as $line){
        $line = trim($line);
        if(strpos($line, "X! TANDEM") === 0){
          $gpm_version = $line;
          break;
        }
      }
    }
    if(!$gpm_version){
      $rt = 'Prohits can not connect GPM: '.$gpm_ip;
    } 
  }else if($serchEngine == 'sequest'){
     
    if(!SEQUEST_IP){
      $rt =  'SEQUEST IP is empty';
    }else{
      $url = "http://" . SEQUEST_IP . "/Prohits_SEQUEST";
      if(!$fd=@fopen($url."/Prohits_SEQUEST.pl", 'r')){
        $rt = 'Prohits can not connect SEQUEST: '.SEQUEST_IP;
      }else{
        $line = '';
        while (! feof ($fd)) {
          $line .= trim(fgets($fd));
        }
				if(preg_match("/(VERSION:.+)/", $line, $matches)){
          if(count($matches)>1){
            $sequest_version = $matches[1];
          }
        } 
        if(!preg_match("/ALL COMMANDS ARE WORKING/i",$line, $matches)){
          $rt = "Please following Prohits-sequest installation instruction to correct the setting.";
        }
        fclose($fd);
      }
    } 
  }else if($serchEngine == 'philosopher'){ 
    $output = array();
    $cmd = preg_replace("/\/$/", "", PHILOSOPHER_BIN_PATH)."/philosopher";
    @exec("$cmd 2>&1", $output);
    
    if($output){
      foreach($output as $line){
        $line = trim($line);
        if(strpos($line, "version:") === 0){
          $philosopher_version = $line;
          break;
        }
      }
    }
    if(!$philosopher_version){
       $rt = 'Prohits can not connect $philosopher_version: '.PHILOSOPHER_BIN_PATH;
    }
   
  }else if($serchEngine == 'tpp'){ 
     
    $output = array();
    if(!$in_local){
      $in_local = is_in_local_server('TPP');
    }
    if($in_local){
      $cmd = preg_replace("/\/$/", "", TPP_BIN_PATH)."/xinteract";
      @exec("$cmd 2>&1", $output);
      
    }else{
      $url = "http://".$gpm_ip.add_folder_backslash(GPM_CGI_DIR);
      $url .= "Prohits_TPP.pl?tpp_myaction=test";
      $output = @file($url);
      if(!$output){
        $rt = "Prohits_TPP.pl is missing";
      }
    }
    $tpp_set = 0;
    if($output){
      
      foreach($output as $line){
        $line = trim($line);
        if(!$tpp_version){
          if(preg_match("/\(TPP(.+)\)/",$line, $matches)){
            if(count($matches)>1){
              $tpp_version = $matches[1];
            }
          }
        }
        if(strpos($line, "usage: xinteract") === 0){
          $tpp_set = 1;
          break;
        }
      }
    }
    if(!$tpp_set){
      $rt = 'Prohits can not connect TPP: '.$gpm_ip;
    }
  }else if($serchEngine == 'comet'){ 
    $output = array();
    $cmd = preg_replace("/\/$/", "", COMET_BIN_PATH)."/comet.exe";
    @exec("$cmd 2>&1", $output);
    $tpp_set = 0;
    if($output){
      foreach($output as $line){
        $line = trim($line);
        if(strpos($line, "Comet version") === 0){
          $comet_version = $line;
          break;
        }
      }
    }
    if(!$comet_version){
      $rt = 'Prohits can not connect COMET: '.$gpm_ip;
    }
  }else if($serchEngine == 'msfragger'){
      
      $msfragger_version = 'version: 20170103';
      
      $output = array();
      if(!$in_local){
        $rt = 'Prohits can not connect MSFragger: '.$gpm_ip;
        return $rt;
      }
      $cmd = "java -jar ".preg_replace("/\/$/", "", MSFRAGGER_BIN_PATH)."/MSFragger.jar";
      
      @exec("$cmd 2>&1", $output);
      $tpp_set = 0;
       
      if($output){
        foreach($output as $line){
          $line = trim($line);
          if(strpos($line, "version") === 0){
            $msfragger_version = $line;
            break;
          }else if(strpos($line, "Error") === 0){
            $rt = $line;
            $msfragger_version = '';
          }
        }
      }
      if(!$msfragger_version){
       $rt = 'Prohits can not connect MSFragger';
      }
      
  }else if($serchEngine == 'msgfpl'){ 
       
      $output = array();
      if(!$in_local){
        $in_local = is_in_local_server('MSGFPL');
      }
      if($in_local){
         
        $cmd = "java -jar ". preg_replace("/\/$/", "", MSGFPL_BIN_PATH) . '/MSGFPlus.jar';
        @exec("$cmd 2>&1", $output); 
        
        
      }else{
        $url = "http://".$gpm_ip.add_folder_backslash(GPM_CGI_DIR);
        $url .= "Prohits_TPP.pl?tpp_myaction=testMSGFPL";
        $output = @file($url); 
        if(!$output){
          $rt = "Prohits_TPP.pl is missing";
        }
      }
      $tpp_set = 0;
      if($output){
        foreach($output as $line){
          $line = trim($line);
          if(strpos($line, "MS-GF+") === 0){
            $msgfpl_version = str_replace("MS-GF+", "", $line);
            break;
          }
        }
      }
      
      if(!$msgfpl_version){
        $rt = 'MS-GF+ can not connect TPP: '.$gpm_ip;
      }
  }else if($serchEngine == "msumpire"){ 
    if(!$gpm_ip){
      $rt =  'MS-Umpire is empty';
    }else{
      $output = array();
      
      if($in_local){
        $cmd = "java -jar ". preg_replace("/\/$/", "", MSUMPIRE_BIN_PATH) . '/MSUmpire.jar';
        @exec("$cmd 2>&1", $output); 
         exit;
      }else{
        $url = "http://".$gpm_ip.add_folder_backslash(GPM_CGI_DIR);
        $url .= "Prohits_TPP.pl?tpp_myaction=testMSUmpire";
        $output = @file($url); 
        if(!$output){
          $rt = "Prohits_TPP.pl is missing";
        }
      }
      $tpp_set = 0;
      if($output){
        foreach($output as $line){
          $line = trim($line);
          if(strpos($line, "MS-Umpire") === 0){
            if(preg_match("/\((version:.+)\)/",$line, $matches)){
              if(count($matches)>1){
                $msumpire_version = $matches[1];
              }
            }
            break;
          }
        }
      }
       
      if(!$msumpire_version){
        $rt = 'MS-Umpire not in : '.$gpm_ip;
      }
    }
  }else if($serchEngine == "diaumpire"){
    $output = array();
    if($in_local){
      $cmd = "java -jar ". preg_replace("/\/$/", "", DIAUMPIRE_BIN_PATH) . '/DIA_Umpire_SE.jar';
      @exec("$cmd 2>&1", $output); 
    }else{
      $url = "http://".$gpm_ip.add_folder_backslash(GPM_CGI_DIR);
      $url .= "Prohits_TPP.pl?tpp_myaction=testDIAUmpire";
      $output = @file($url); 
      if(!$output){
        $rt = "Prohits_TPP.pl is missing";
      }
    }
    $tpp_set = 0;
    if($output){
      foreach($output as $line){
        $line = trim($line);
        if(strpos($line, "DIA-Umpire") === 0){
          if(preg_match("/\((version:.+)\)/",$line, $matches)){
            if(count($matches)>1){
              $diaumpire_version = $matches[1];
            }
          }
          break;
        }
      }
    }
     
    if(!$diaumpire_version){
      $rt = 'DIA-Umpire not in : '.$gpm_ip;
    }
   
  }else if($serchEngine == 'converter'){
    if(!RAW_CONVERTER_SERVER_PATH){
      $rt =  'Converter path is empty';
    }else{
      if(strpos(RAW_CONVERTER_SERVER_PATH, 'http://')!== false){ 
        $url = RAW_CONVERTER_SERVER_PATH;
        if(!$fd=@fopen($url, 'r')){
          $rt = 'Prohits can not connect Raw file converter: '.RAW_CONVERTER_SERVER_PATH;
        }else{
          $has_sciex_converter = 0;
          while (! feof ($fd)) {
            $the_line= trim(fgets($fd));
            if(strpos($the_line, "ProteoWizard release") === 0){
              $converter_version = $the_line;
            }else if(preg_match("/Copyright:(.+SCIEX)/", $the_line, $matches)){
              $converter_version .= " and SCIEX converter: ".$matches[1];
              $has_sciex_converter = 1;
            }else{
              if($has_sciex_converter) $converter_version .= $the_line;
            }
          }
          fclose($fd);
        }
  	  }
    }
  }else if($serchEngine == 'msplit'){
      $output = array();
      if(!$in_local){
        $in_local = is_in_local_server("MSPLIT"); 
      }
      if($in_local){
        $cmd = "java -jar ". MSPLIT_JAR_PATH;
        @exec("$cmd 2>&1", $output); 
        
        if($output){
          if(!preg_match("/Exception/", $output[0], $matches)){
            $rt = "MSPLIT doesn't work";
            $output = '';
          }
        }
         
      }else{
        $url = "http://".$gpm_ip.add_folder_backslash(GPM_CGI_DIR);
        $url .= "Prohits_TPP.pl?tpp_myaction=testMSPLIT";
        $output = @file($url); 
        if(!$output){
          $rt = "Prohits_TPP.pl is missing";
        }
      }
      if($output){
         $msplit_version = " version 1.0";
         return $rt;
      } 
  }
  
  ini_set('default_socket_timeout', $old);
  return $rt;
}
function get_dir_tree_line($dirID,&$dirTreelineArr,$tableName){
  global $managerDB;
  $currentDirObj = new Storage($managerDB->link,$tableName);
  $currentDirObj->fetch($dirID);
  array_push($dirTreelineArr, $currentDirObj);
  if($currentDirObj->FolderID){
    get_dir_tree_line($currentDirObj->FolderID,$dirTreelineArr,$tableName);
  }
}
function create_dir_tree($dirID,$tableName, $clickable=true){
  $rt = '';
  $dirTreelineArr = array();
  get_dir_tree_line($dirID,$dirTreelineArr,$tableName);  
  $levelCount = 0;
  $rt .= "<div id='dir_tree'>\r\n";
  $rt .= "<ul id='dir_tree_topNodes'>\r\n";
  for($i=count($dirTreelineArr)-1;$i>=0;$i--){   
    if($dirTreelineArr[$i]->ID == $dirID){
      $imageFile1 = "minus.gif";
      $folderImage = "folder_open.gif";
      $folderName = "<span class=dir_tree_text_lite>".$dirTreelineArr[$i]->FileName."</span>";
    }else{
      $imageFile1 = "plus.gif";
      $folderImage = "folder_close.gif";
      $folderName = $dirTreelineArr[$i]->FileName;
    }
    $dateArr = explode(' ',$dirTreelineArr[$i]->Date);
    $date  = $dateArr[0];
    $rt .= (($levelCount)?"<ul>\r\n":'')."<li>";
		if($clickable){
			$rt .= "<a href=\"javascript: open_dir('".$dirTreelineArr[$i]->ID."');\"><img src='images/$imageFile1' border=0></a><img src='images/$folderImage'>&nbsp;<a href=\"javascript: open_dir('".$dirTreelineArr[$i]->ID."');\">".$folderName."&nbsp;<strong class=dir_tree_text>".ceil($dirTreelineArr[$i]->Size/1024)."(MB)"."&nbsp;&nbsp;".$date."</strong></a></li>\r\n";
    }else{
			$rt .= "<img src='images/$imageFile1' border=0><img src='images/$folderImage'>&nbsp;".$folderName."&nbsp;<strong class=dir_tree_text>".ceil($dirTreelineArr[$i]->Size/1024)."(MB)"."</strong></li>\r\n";
		}
		$levelCount++;
  }
  while($levelCount){
    $rt .= "</ul>\r\n";
    $levelCount--;
  }
	return $rt."</div>";
}
function read_mascot_mod_file($mascot_modifications_file){
  $rt = array();
  if(!_is_file($mascot_modifications_file)){
    return array();
  }
  $lines = file($mascot_modifications_file);
   
  for($i = 0; $i< count($lines); $i++){
    $buffer = trim($lines[$i]);
    if(!$buffer) continue;
    if(strpos($buffer, "Title:" ) === 0){
      //if(trim($lines[$i+1])=='Hidden') continue;
      $mod_name = str_replace("Title:","",  $buffer);
      $rt[$mod_name] = array();
      do{
        $i++;
        $buffer = trim($lines[$i]);
        if(!$buffer or trim($buffer)=='Hidden') continue;
        $tmp_arr = preg_split("/[\s:]+/", $buffer);
        if(count($tmp_arr)==4){
          array_push($rt[$mod_name], array("location"=>$tmp_arr[0],"R"=>$tmp_arr[1], "mono"=>$tmp_arr[2], "avg"=>$tmp_arr[3]));
        }else if(count($tmp_arr)==3){
          array_push($rt[$mod_name], array("location"=>$tmp_arr[0],"R"=>'', "mono"=>$tmp_arr[1], "avg"=>$tmp_arr[2]));
       
        }else{
          $buffer = '';
        }
      }while($buffer and $buffer != "*");
    }
  }
  //ksort($rt);
  return $rt;
}
function get_mascot_form__(){
  $rt = array();
  $mascot_sessionID = Mascot_session();
  if($mascot_sessionID === true or $mascot_sessionID === false or !$mascot_sessionID){
    return $rt;
  }
  $timeout = 15;
  $old = ini_set('default_socket_timeout', $timeout);
  $url_tmp = "http://" . MASCOT_IP . MASCOT_CGI_DIR ."/search_form.pl?sessionID=$mascot_sessionID&SEARCH=MIS";
  $fd = @fopen($url_tmp, "r");
  ini_set('default_socket_timeout', $old);
  if(!$fd) fatalError("Cannot open http://" . MASCOT_IP . MASCOT_CGI_DIR ."/search_form.pl. \nThis function needs local Mascot. If the local Mascot is running, please check MASCOT_IP in ../config/conf.inc.php", __LINE__);
  $start_output = 0;
  $remove_this_line = 0;
  $tmp_name = 'tmp';
  $DB_start = 0;
  $INSTRUMENT_start = 0;
  $i = 0;
  while($buffer = fgets($fd, 4075)){
    if( trim($buffer) == '<SCRIPT LANGUAGE="JavaScript">' or strstr($buffer, 'TYPE="hidden"')){
      $start_output = 1;
    }else if(strstr($buffer, '</SCRIPT>')){
      $start_output = 0;
    }else if(trim($buffer) == '</FORM>'){
      $start_output = 0;
      break;
    }
    if($start_output and preg_match("/Name=\"DB\"/i", $buffer, $matches)){
        $DB_start = 1;
        $i = 0;
    }else if($DB_start and preg_match("/\<\/SELECT\>/i", $buffer, $matches)){
      $DB_start = 0;
    }
    if($start_output and preg_match("/NAME=\"INSTRUMENT\"/i", $buffer, $matches)){
       $INSTRUMENT_start = 1;
       $i = 0;
    }else if($INSTRUMENT_start and preg_match("/\<\/SELECT\>/i", $buffer, $matches)){
      $INSTRUMENT_start = 0;
    }
    if($DB_start){
      if(preg_match("/<OPTION>(.+)<\/OPTION>/", $buffer, $matches)){
        $rt["DB"][$i++] = $matches[1];
      }
    }else if($INSTRUMENT_start){
      if(preg_match("/<OPTION>(.+)<\/OPTION>/", $buffer, $matches)){
        $rt["INSTRUMENT"][$i++] = $matches[1];
      }
    }  
  }
  return $rt;
}
//----------------------------------------------------------------------------------------------
function create_mascot_parameter_arr($searchAll_parameter, $taskID=0, $default_comet_param_arr){
//----------------------------------------------------------------------------------------------
  global $msManager_link;
  global $prohits_error_msg;
  $prohits_error_msg = '';
  $rt = array();
  $mascot_default_parm_dir = dirname(__FILE__);
  $tmp_para_arr = get_mascot_default_param($mascot_default_parm_dir);
  $mascot_CLE_arr = explode(";;", $tmp_para_arr['OPTIONS_CLE']);
  $tmp_arr = explode(";;", $searchAll_parameter);
  foreach($tmp_arr as $value){
    if(trim($value)){
      $tmp = explode("=", $value, 2);
      $search_all_arr[$tmp[0]] = $tmp[1];
    }
  }
  $the_CLE =  $default_comet_param_arr['comet_enzyme_info']['name'][$search_all_arr['search_enzyme_number']];
  if($search_all_arr['num_enzyme_termini'] == 1){
    $the_CLE = 'semi'.$the_CLE;
  }
  if(!in_array($the_CLE, $mascot_CLE_arr)){
    $the_CLE = str_replace("_", "-", $the_CLE);
    if(!in_array($the_CLE, $mascot_CLE_arr)){
      $prohits_error_msg = "The enzyme $the_CLE is missing in Mascot";
      return $rt;
    }
  }
  $mass_units = array('Da', 'mmu', 'ppm');
  $mass_type = array('Average', 'Monoisotopic');
  $tmp_para_arr['USERNAME'] = 'prohits';
  $tmp_para_arr['USEREMAIL'] = 'prohits@prohits';
  $tmp_para_arr['COM'] = "Prohits search task id=$taskID";
  $tmp_para_arr['DB'] = $search_all_arr['database_name'];
  $tmp_para_arr['CLE'] = $the_CLE;
  
  $tmp_para_arr['PFA'] = $search_all_arr['allowed_missed_cleavage'];
  $tmp_para_arr['TAXONOMY'] = 'All entries';
  $tmp_para_arr['TOL'] = $search_all_arr['peptide_mass_tolerance'];
  $tmp_para_arr['TOLU'] = $mass_units[$search_all_arr['peptide_mass_units']];
  $tmp_para_arr['PEP_ISOTOPE_ERROR'] = $search_all_arr['isotope_error']; 
  
  $tmp_para_arr['ITOL'] = $search_all_arr['fragment_bin_tol'];
  $tmp_para_arr['ITOLU'] = 'Da';
  $tmp_para_arr['CHARGE'] = preg_replace("/\(.+\)$/", '', $search_all_arr['CHARGE']);
  $tmp_para_arr['MASS'] = $mass_type[$search_all_arr['mass_type_parent']];
  $tmp_para_arr['FORMAT'] = 'Mascot generic';
  
  $tmp_para_arr['PRECURSOR'] = '';
  $tmp_para_arr['INSTRUMENT'] = $search_all_arr['INSTRUMENT'];
  $tmp_para_arr['DECOY'] = $search_all_arr['decoy_search'];
  $tmp_para_arr['REPORT'] = 'AUTO';
  foreach($tmp_para_arr as $key=>$value){
    if($key and $key != 'MODS' and $key != 'IT_MODS' and $key != 'FILE' and strpos($key,'OPTIONS')!==0)
    $rt[] = "$key=$value";
  }
   
  if(isset($search_all_arr['multiple_select_str'])){
    $mod_arr = make_mod_array($search_all_arr['multiple_select_str'], "Mascot");
     
    if($mod_arr['fixed']){
      foreach($mod_arr['fixed'] as $mod){
        $rt[] = "MODS=".$mod;
      }
    }
    if($mod_arr['variable']){
      foreach($mod_arr['variable'] as $mod){
        $rt[] = "IT_MODS=".$mod;
      }
    }
  }
  return $rt;
}
//-------------------------------------------
function get_mascot_default_param($mascot_parm_dir, $refresh=0){
//-------------------------------------------
  $rt = array();
   
  $handle = '';
  $file = $mascot_parm_dir."/mascot.params.new";
  if(!$refresh and _is_file($file)){
    $lines = file($mascot_parm_dir."/mascot.params.new");
    foreach($lines as $v){
      $tmp = explode("=", trim($v), 2);
      $rt[$tmp[0]] = $tmp[1];
    }
  }else{
    $mascot_sessionID = Mascot_session();
    if($mascot_sessionID === true){
      $mascot_sessionID = '';
    }else if($mascot_sessionID === false or !$mascot_sessionID){
    	return $rt;
    }
    $timeout = 15;
    $old = ini_set('default_socket_timeout', $timeout);
    $url_tmp = "http://" . MASCOT_IP . MASCOT_CGI_DIR ."/search_form.pl?sessionID=$mascot_sessionID&SEARCH=MIS";
    //following line for test
    //$url_tmp = "http://www.matrixscience.com/cgi/search_form.pl?SEARCH=MIS";
    $HTML_form = file_get_contents($url_tmp);
    ini_set('default_socket_timeout', $old);
    
    if(!$HTML_form) return $rt;
    
    $form_arr = form_to_data($HTML_form);
    //print_r($form_arr);exit;
    
    
    $to_file = '';
    if(isset($form_arr['data'])){
      foreach($form_arr['data'] as $k=>$a){
        if(!trim($k) or
           $k == 'remove_MODS' or
           $k == 'add_MODS' or
           $k == 'remove_IT_MODS' or 
           $k == 'add_IT_MODS' or
           $k == 'MASTER_MODS') continue;
        $theValue = '';
        foreach($a as $kk=>$vv){
          if($theValue) $theValue .=";";
          $theValue .= $kk;
          
        }
        $str = $k."=".$theValue;
        $to_file .= $k."=".$theValue."\n";
        $rt[$k] = $theValue;
      }
    }
    if(isset($form_arr['data_possible']['DB'])){
       $option_str = implode(";;", $form_arr['data_possible']['DB']);
       $to_file .= "OPTIONS_DB=$option_str\n";
       $rt['OPTIONS_DB'] = $option_str;
    }
    if(isset($form_arr['data_possible']['CLE'])){
       $option_str = implode(";;", $form_arr['data_possible']['CLE']);
       $to_file .= "OPTIONS_CLE=$option_str\n";
       $rt['OPTIONS_CLE'] = $option_str;
    }
    if(isset($form_arr['data_possible']['INSTRUMENT'])){
       $option_str = implode(";;", $form_arr['data_possible']['INSTRUMENT']);
       $to_file .= "OPTIONS_INSTRUMENT=$option_str\n";
       $rt['OPTIONS_INSTRUMENT'] = $option_str;
    }
    if (_is_writable($mascot_parm_dir)) {
      $handle = fopen($file, 'w');
      if($handle){
        fwrite($handle, $to_file);
        fclose($handle);
      }
    }
  }
   
  return $rt;
}
function make_MSGFPL_mod_str($multiple_select_str){
  $mod_arr = make_mod_array($multiple_select_str, 'MSGFPL');
  $rt = '';
  foreach($mod_arr as $fix_var_type => $thisTypeMods){
    if($fix_var_type == 'fixed' or $fix_var_type == 'variable'){
      $fix_opt = 'opt';
      if($fix_var_type == 'fixed') $fix_opt = 'fix';
      foreach($thisTypeMods as $R=>$value){
        if( $R != 'name'){
          $rt .= $value.",".$R.",".$fix_opt.",any".",".$thisTypeMods['name'][$R].";;";
        }
      }
    }else if($fix_var_type == 'fixedCterm' and $thisTypeMods > 0){
      $rt .= $thisTypeMods.",*,fix,C-Term,".$mod_arr['name']['fixedCterm']."::";
    }else if($fix_var_type == 'fixedNterm' and $thisTypeMods > 0){
      $rt .= $thisTypeMods.",*,fix,N-Term,".$mod_arr['name']['fixedNterm']."::";
    }else if($fix_var_type == 'variableCterm' and $thisTypeMods > 0){
      $rt .= $thisTypeMods.",*,opt,C-Term,".$mod_arr['name']['variableCterm']."::";
    }else if($fix_var_type == 'variableNterm' and $thisTypeMods > 0){
      $rt .= $thisTypeMods.",*,opt,N-Term,".$mod_arr['name']['variableNterm']."::";
    }else if($fix_var_type == 'add_Cterm_protein' and $thisTypeMods > 0){
      $rt .= $thisTypeMods.",*,fix,Prot-C-term,".$mod_arr['name']['add_Cterm_protein']."::";
    }else if($fix_var_type == 'add_Nterm_protein' and $thisTypeMods > 0){
      $rt .= $thisTypeMods.",*,fix,Prot-N-term,".$mod_arr['name']['add_Nterm_protein']."::";
    }
  }
  return $rt;
}
function create_MSGFPL_parameter_arr($searchAll_parameter, $taskID=0){
  include("./msgfpl_parames.inc.php");
  global $prohits_error_msg;
  $rt = array();
  
  $prohits_error_msg = '';
  //$comet_default_parm_dir = dirname(__FILE__)."/autoSearch";
  $comet_default_param_arr = get_comet_default_param();
  $tmp_arr = explode(";;", $searchAll_parameter);
  foreach($tmp_arr as $value){
    if(trim($value)){
      $tmp = explode("=", $value, 2);
      $search_all_arr[$tmp[0]] = $tmp[1];
      if($tmp[0] != 'multiple_select_str' 
        //and $tmp[0] != 'CHARGE' 
        and $tmp[0] != 'INSTRUMENT' 
        and $tmp[0] != 'fragment_bin_tol'
        and $tmp[0] != 'ProhitsUsekscore'){
        $tmp_para_arr[$tmp[0]] = trim($tmp[1]);
      }
    }
  }
  //print_r($tmp_arr);
  //print_r($tmp_para_arr);
  $rt['mods_str'] = make_MSGFPL_mod_str($search_all_arr['multiple_select_str'], 'MSGFPL');
  $rt['database_name'] = $tmp_para_arr['database_name'];
  if($tmp_para_arr['peptide_mass_units'] == 2){
    $theUnit = 'ppm';
  }else{
    $theUnit = 'Da';
  }
  
  if(isset($tmp_para_arr['peptide_mass_tolerance_start']) and isset($tmp_para_arr['peptide_mass_tolerance_end'])){
    $tmp_para_arr['peptide_mass_tolerance'] = $tmp_para_arr['peptide_mass_tolerance_start'].$theUnit.",".$tmp_para_arr['peptide_mass_tolerance_end'];
  }
  $rt['other_param'] = ' -t '. $tmp_para_arr['peptide_mass_tolerance'].$theUnit;
  
  if(isset($tmp_para_arr['isotope_error_start']) and isset($tmp_para_arr['isotope_error_end'])){
    $rt['other_param'] .= ' -ti '.$tmp_para_arr['isotope_error_start'].','.$tmp_para_arr['isotope_error_end'];
  }else if(isset($tmp_para_arr['isotope_error'])){
     $rt['other_param'] .= ' -ti -1,3';
  }else{
     //$rt['other_param'] .= ' -ti 0,1';
  }
  //msgf has to use reversed fasta db to search for TPP, don't need to decoy search.
  if(isset($tmp_para_arr['decoy_search']) and $tmp_para_arr['decoy_search']){
    $rt['other_param'] .= ' -tda 1';
  }
  if(isset($tmp_para_arr['c13']) and $tmp_para_arr['c13']){
    $rt['other_param'] .= ' -c13 '.$tmp_para_arr['c13'];;
  }
   
  if(isset($tmp_para_arr['msgfdb_InstrumentID'])){
    $rt['other_param'] .= ' -inst '. $tmp_para_arr['msgfdb_InstrumentID'];
  }else{
    $rt['other_param'] .= ' -inst '. $tmp_para_arr['msgfpl_InstrumentID'];
  }
  
  if(isset($tmp_para_arr['msgfdb_FragmentMethodID'])){
    $rt['other_param'] .= ' -m ' . $tmp_para_arr['msgfdb_FragmentMethodID'];
  }else{
    $rt['other_param'] .= ' -m ' . $tmp_para_arr['msgfpl_FragmentMethodID'];
  }
  if(isset($tmp_para_arr['enzyme_number'])){
    //is MS-GF+
    $rt['other_param'] .= ' -e ' . $tmp_para_arr['enzyme_number'];
  }else{
    $the_enzym_name = $comet_default_param_arr['comet_enzyme_info']['name'][$tmp_para_arr['search_enzyme_number']];
    foreach($default_MSGFPL_param_arr['MSGFPL_enzyme_info'] as $index=>$name){
      if($name == $the_enzym_name){
        $rt['other_param'] .= ' -e ' . $index;
        break;
      }
    }
  }
  if(isset($tmp_para_arr['num_enzyme_termini']) and $tmp_para_arr['num_enzyme_termini']){
    //num_enzyme_termini(Semi-style cleavage). if = 1 or 0. -ntt default:2
    if($taskID){ 
      //it is MSGFPL
      $rt['other_param'] .= ' -ntt '.$tmp_para_arr['num_enzyme_termini'];
    }else{
      //MSGFDB
      $rt['other_param'] .= ' -nnet '.$tmp_para_arr['num_enzyme_termini'];
    }
  }
  
  if(isset($search_all_arr['CHARGE'])){ 
    if(preg_match("/^([0-9]).+ and ([0-9])/", $search_all_arr['CHARGE'], $matches)){
       
      if(count($matches) > 2){
        $rt['other_param'] .= ' -minCharge '. $matches[1];
        $rt['other_param'] .= ' -maxCharge '. $matches[2];
      }
    }
  }
  
  if(isset($tmp_para_arr['minPepLength']) and $tmp_para_arr['minPepLength']){
    $rt['other_param'] .= ' -minLength '. $tmp_para_arr['minPepLength'];
  }
  if(isset($tmp_para_arr['maxPepLength']) and $tmp_para_arr['maxPepLength']){
    $rt['other_param'] .= ' -maxLength '. $tmp_para_arr['maxPepLength'];
  }
  if(isset($tmp_para_arr['minPrecursorCharge']) and $tmp_para_arr['minPrecursorCharge']){
    $rt['other_param'] .= ' -minCharge '. $tmp_para_arr['minPrecursorCharge'];
  }
  if(isset($tmp_para_arr['maxPrecursorCharge']) and $tmp_para_arr['maxPrecursorCharge']){
    $rt['other_param'] .= ' -maxCharge '. $tmp_para_arr['maxPrecursorCharge'];
  } 
  if(isset($tmp_para_arr['numMatchesPerSpec']) and $tmp_para_arr['numMatchesPerSpec']){
    $rt['other_param'] .= ' -n '. $tmp_para_arr['numMatchesPerSpec'];
  }
  if(isset($tmp_para_arr['uniformAAProb']) and $tmp_para_arr['uniformAAProb']){
    $rt['other_param'] .= ' -uniformAAProb '. $tmp_para_arr['uniformAAProb'];
  }
  return $rt;
}
function create_MSFRAGGER_parameter_arr($searchAll_parameter, $taskID=0, $default_msfragger_param_arr=array()){
  global $prohits_error_msg;
  $prohits_error_msg = '';
  $clear_mz_range__from = '0.0';
  $clear_mz_range__to = '0.0';
  
  $rt = array();
  if($default_msfragger_param_arr){
    $tmp_para_arr = $default_msfragger_param_arr;
  }else{
    $tmp_para_arr = get_msfragger_default_param();
  }
  if(!$tmp_para_arr){
    fatalError("cannot get default MSFragger parameters");
  }
  $tmp_arr = explode(";;", $searchAll_parameter);
  
  foreach($tmp_arr as $value){
    if(trim($value)){
      $tmp = explode("=", $value, 2);
      $search_all_arr[$tmp[0]] = $tmp[1];
      if(isset($tmp_para_arr[$tmp[0]])){
        $tmp_para_arr[$tmp[0]] = $tmp[1];
        
      }else if($tmp[0] == 'search_enzyme_name_str'){
        list($tmp_para_arr['search_enzyme_name'], $tmp_para_arr['search_enzyme_cutafter'], $tmp_para_arr['search_enzyme_butnotafter']) = explode(":", $tmp[1]);
      }
    }
  }
  if($search_all_arr['clear_mz_range__from']){
    $clear_mz_range__from = $search_all_arr['clear_mz_range__from'];
  }
  if($search_all_arr['clear_mz_range__to']){
    $clear_mz_range__to = $search_all_arr['clear_mz_range__to'];
  }
  $tmp_para_arr['clear_mz_range'] = $clear_mz_range__from . " " . $clear_mz_range__to;
  $mod_arr = make_mod_array($search_all_arr['multiple_select_str'], 'COMET');
  
  $tmp_para_arr['add_Cterm_peptide'] = $mod_arr['add_Cterm_peptide'];
  $tmp_para_arr['add_Nterm_peptide'] = $mod_arr['add_Nterm_peptide'];
  $tmp_para_arr['add_Cterm_protein'] = $mod_arr['add_Cterm_protein'];
  $tmp_para_arr['add_Nterm_protein'] = $mod_arr['add_Nterm_protein'];
  foreach($tmp_para_arr as $key=>$value){
    if(preg_match("/^add_([A-Z])_/", $key, $matches)){
      if(isset($mod_arr['fixed'][$matches[1]])){
        $tmp_para_arr[$key] = $mod_arr['fixed'][$matches[1]];
      }else{
        $tmp_para_arr[$key] = '0.0000';
      }
    }else if(preg_match("/^variable_mod_0[0-9]/", $key, $matches)){
      $tmp_para_arr[$key] = "";
    }
  }
  if(isset($mod_arr['variable'])){
    $i = 1;
    foreach($mod_arr['variable'] as $key => $value){
      $tmp_para_arr['variable_mod_0'.$i] = "$value $key";
      $i++;
    }
  }
  return $tmp_para_arr;
}
function create_comet_parameter_arr($searchAll_parameter, $taskID=0, $default_comet_param_arr=array()){
  //fragment_bin_tol will use the default if no fragment_bin_offset value
  global $prohits_error_msg;
  
   
  
  $prohits_error_msg = '';
  $rt = array();
  if($default_comet_param_arr){
    $tmp_para_arr = $default_comet_param_arr;
  }else{
    $tmp_para_arr = get_comet_default_param();
  }
  $tmp_arr = explode(";;", $searchAll_parameter);
  $fragment_bin_tol = '';
  $add_bin_tol = false;
  foreach($tmp_arr as $value){
    if(trim($value)){
      $tmp = explode("=", $value, 2);
      $search_all_arr[$tmp[0]] = $tmp[1];
      if($tmp[0] == 'fragment_bin_offset'){
        $tmp_para_arr['comet'][$tmp[0]] = $tmp[1];
        $add_bin_tol = true;
      }else if($tmp[0] == 'fragment_bin_tol'){
        $fragment_bin_tol = $tmp[1];
      }else if($tmp[0] != 'multiple_select_str' 
        and $tmp[0] != 'CHARGE' 
        and $tmp[0] != 'INSTRUMENT' 
        //and $tmp[0] != 'fragment_bin_tol'
        and $tmp[0] != 'ProhitsUsekscore'
        and $tmp[0] != 'sgfpl_InstrumentID'
        and $tmp[0] != 'msgfpl_FragmentMethodID'
        ){
        $tmp_para_arr['comet'][$tmp[0]] = $tmp[1];
      }
    }
  }
  if($add_bin_tol and $fragment_bin_tol){
    $tmp_para_arr['comet']['fragment_bin_tol'] = $fragment_bin_tol;
  }
  
  
  if(!$tmp_para_arr['comet']['num_enzyme_termini']){
    $tmp_para_arr['comet']['num_enzyme_termini'] = 2;
  }
  $tmp_para_arr['comet']['output_pepxmlfile'] = 1;
  if(!isset($search_all_arr['decoy_search']) or !$search_all_arr['decoy_search']){
     $tmp_para_arr['comet']['decoy_search'] = 0;
  }
  if(!isset($search_all_arr['use_NL_ions']) or !$search_all_arr['use_NL_ions']){
     $tmp_para_arr['comet']['use_NL_ions'] = 0;
  }
  
  $CHARGE = '1 3';
   
  if(isset($search_all_arr['CHARGE'])){ 
    if(preg_match("/^([0-9]).+ and ([0-9])/", $search_all_arr['CHARGE'], $matches)){
      if(count($matches) > 2){
        $CHARGE =  $matches[1] ." ". $matches[2];
      }
    }
  }
   
  $tmp_para_arr['comet']['precursor_charge'] = $CHARGE;
  //handle modifications
   
  $mod_arr = make_mod_array($search_all_arr['multiple_select_str'], 'COMET');
   
  
  //$tmp_para_arr['comet']['variable_C_terminus'] = $mod_arr['variable_C_terminus'];
  //$tmp_para_arr['comet']['variable_N_terminus'] = $mod_arr['variable_N_terminus'];
  $tmp_para_arr['comet']['add_Cterm_peptide'] = $mod_arr['add_Cterm_peptide'];
  $tmp_para_arr['comet']['add_Nterm_peptide'] = $mod_arr['add_Nterm_peptide'];
  $tmp_para_arr['comet']['add_Cterm_protein'] = $mod_arr['add_Cterm_protein'];
  $tmp_para_arr['comet']['add_Nterm_protein'] = $mod_arr['add_Nterm_protein'];
  //reset all aa mod
  foreach($tmp_para_arr['comet'] as $key=>$value){
    if(preg_match("/^add_([A-Z])_/", $key, $matches)){
      if(isset($mod_arr['fixed'][$matches[1]])){
        $tmp_para_arr['comet'][$key] = $mod_arr['fixed'][$matches[1]];
      }else{
        $tmp_para_arr['comet'][$key] = '0.0000';
      }
    }else if(preg_match("/^variable_mod0[0-9]/", $key, $matches)){
      $tmp_para_arr['comet'][$key] = '0.0 X 0 3 -1 0';
    }
  }
  if(isset($mod_arr['variable'])){
    $i = 1;
    foreach($mod_arr['variable'] as $key => $value){
      $tmp_para_arr['comet']['variable_mod0'.$i] = "$value $key 0 3 -1 0 0";
      $i++;
    }
  }
  $rt =  $tmp_para_arr['comet'];
  $rt['COMET_ENZYME_INFO'] = $tmp_para_arr['comet_enzyme_info_lines'];
  return $rt;
}
function create_gpm_parameter_arr($searchAll_parameter, $taskID=0, $default_comet_param_arr){
  //in autoSearch dir
  global $prohits_error_msg;
  $prohits_error_msg = '';
  $rt = array();
  $mass_units = array('Daltons', 'mmu', 'ppm');
  $mass_type = array('average', 'monoisotopic');
  
  $gpm_default_parm_dir = dirname(__FILE__)."/autoSearch";
   
  $tmp_para_arr = get_gpm_default_param($gpm_default_parm_dir);
  if(!$tmp_para_arr){
    echo "error: cannot get gpm default parmeter file";
  }
   
  $tmp_arr = explode(";;", $searchAll_parameter);
  foreach($tmp_arr as $value){
    if(trim($value)){
      $tmp = explode("=", $value, 2);
      $search_all_arr[$tmp[0]] = $tmp[1];
    }
  }
  //print_r($search_all_arr);
  
  
   
  $tmp_para_arr['protein__taxon'] = $search_all_arr['database_name'];
  $tmp_para_arr['scoring__maximum_missed_cleavage_sites'] = $search_all_arr['allowed_missed_cleavage'];
  if($search_all_arr['num_enzyme_termini'] == 1){
    $cleavage_semi = 'yes';
  }else{
    $cleavage_semi = 'no';
  }
  $tmp_para_arr['protein__cleavage_semi'] = $cleavage_semi;
  if($search_all_arr['decoy_search']){
    $include_reverse = 'yes';
  }else{
    $include_reverse = '';
  }
  $tmp_para_arr['gpmdb__add'] = 'no';
  $tmp_para_arr['gpmdb__anonymous'] = 'no';
  $tmp_para_arr['scoring__include_reverse'] = $include_reverse;
  $tmp_para_arr['spectrum__fragment_mass_type'] = $mass_type[$search_all_arr['mass_type_parent']];
  
  $tmp_para_arr['spectrum__parent_monoisotopic_mass_error_plus'] = $search_all_arr['peptide_mass_tolerance'];
  $tmp_para_arr['spectrum__parent_monoisotopic_mass_error_minus'] = $search_all_arr['peptide_mass_tolerance'];
  $tmp_para_arr['spectrum__parent_monoisotopic_mass_error_units'] = $mass_units[$search_all_arr['peptide_mass_units']];
  
  $tmp_para_arr['spectrum__fragment_monoisotopic_mass_error'] = $search_all_arr['fragment_bin_tol'];
  $tmp_para_arr['spectrum__fragment_monoisotopic_mass_error_units'] = 'Daltons';
  if($search_all_arr['isotope_error']){
    $isotope_error = 'yes';
  }else{
    $isotope_error = 'no';
  }
  $tmp_para_arr['spectrum__parent_monoisotopic_mass_isotope_error'] = $isotope_error;
  if(preg_match("/([0-9])[+\) ]?$/", $search_all_arr['CHARGE'], $matches)){
    $CHARGE = $matches[1];
  }else{
    $CHARGE = 6;
  }
  $tmp_para_arr['spectrum__maximum_parent_charge'] = $CHARGE;
  $the_CLE_other =  $default_comet_param_arr['comet_enzyme_info']['other'][$search_all_arr['search_enzyme_number']];
  $tmp_para_arr['protein__cleavage_site_select'] = make_gpm_cleavage_value($the_CLE_other);
  $tmp_para_arr['refine__potential_N88terminus_modifications'] = '';
  
  $mod_arr = make_mod_array($search_all_arr['multiple_select_str'], 'GPM');
  
  $tmp_para_arr['residue__modification_mass_select'] = $mod_arr['fixed'];
  $tmp_para_arr['residue__potential_modification_mass_select'] = $mod_arr['variable'];
  
  $tmp_para_arr['protein__N88terminal_residue_modification_mass'] = $mod_arr['fixed_ProteinNterm'];
  $tmp_para_arr['protein__C88terminal_residue_modification_mass'] = $mod_arr['fixed_ProteinCterm'];
  
  $tmp_para_arr['refine__potential_C88terminus_modifications'] = $mod_arr['redefined_Cterm'];
  $tmp_para_arr['refine__potential_N88terminus_modifications'] = $mod_arr['redefined_Nterm'];
  $tmp_para_arr['refine__potential_modification_mass_select'] = $mod_arr['redefined'];
  if(!$mod_arr['redefined']){
    $tmp_para_arr['refine'] = 'no';
    $tmp_para_arr['refine__unanticipated_cleavage'] = 'no';
    //$tmp_para_arr['refine__spectrum_synthesis'] = 'no';
  }
  foreach($tmp_para_arr as $key=>$value){
    $rt[] = "$key=$value";
  }
  if(isset($search_all_arr['ProhitsUsekscore']) and $search_all_arr['ProhitsUsekscore'] == 'yes'){
    $rt[] = "ProhitsUsekscore=yes";
  }
  return $rt;
}
//-----------------------------------------------------
function make_mod_array($multiple_select_str, $engine){
// for all search engine modifications.
// need convert mascot $multiple_select_str to GPM and Comet
//-----------------------------------------------------
  //$multiple_select_str = 'frm_variable_MODS|Cation:Na (C-term):::Phospho (ST):::Dehydrated (N-term C):::Oxidation (M)&&frm_fixed_MODS|Biotin (N-term):::Phospho (ST):::Acetyl (Protein N-term):::Carbamidomethyl (C)&&frm_refinement_MODS|Acetyl (Protein N-term):::Amidated (C-term):::Phospho (ST):::Phospho (Y)';
  //echo $multiple_select_str;
  $rt = array(
    'fixed'=>'', 
    'variable'=>'', 
    'redefined'=>''
  );
  $rt_gpm = array(
  'fixed'=>'', 
  'variable'=>'', 
  'redefined'=>'',
  'redefined_Cterm'=>'',
  'redefined_Nterm'=>'',
  'variable_ProteinCterm'=>'', 
  'variable_ProteinNterm'=>'',
  'fixed_ProteinCterm'=>'', 
  'fixed_ProteinNterm'=>''
  );
  $rt_comet = array(
    'fixed'=>array(), 
    'variable'=>array(), 
    //'variable_C_terminus'=> '0.0',
    //'variable_N_terminus'=> '0.0',
    'add_Cterm_peptide'=> '0.0',
    'add_Nterm_peptide'=> '0.0',
    'add_Cterm_protein'=> '0.0',
    'add_Nterm_protein'=> '0.0'
  );
  
  
  $tmp_arr = explode("&&", $multiple_select_str);
  foreach($tmp_arr as $tmp_val){
    if(!trim($tmp_val)) continue;
    $tmp_arr_1 = explode("|", $tmp_val);
    if(count($tmp_arr_1)==2){
      if($tmp_arr_1[0] == 'frm_fixed_MODS'){
         $index = 'fixed';
      }else if($tmp_arr_1[0] == 'frm_variable_MODS'){
         $index = 'variable';
      }else if($tmp_arr_1[0] == 'frm_refinement_MODS'){
         $index = 'redefined';
      }else{
        continue;
      }
      $all_mods = explode(":::", $tmp_arr_1[1]);
      $tmp_arr = array();
      foreach($all_mods as $mod){
        if(!trim($mod)) continue;
        $tmp_arr[] = $mod;
      }
      if($tmp_arr)
      $rt[$index] = $tmp_arr;
    }
  }

  if($engine == 'Mascot') return $rt;
  $mascot_mod_array = read_mascot_mod_file(dirname(__FILE__)."/autoSearch/mod_file" );
  //Residues:  Nterm: Cterm: NeutralLoss: PepNeutralLoss: ProteinNterm: ResiduesNterm: ResiduesCterm: ResiduesProteinNterm
  //GPM mods---------------
  //M1@X1,M2@X2,..., Mn@Xn  = Residues
  //M1@[,M2@[,..., Mn@[     = N-term
  //M1@],M2@],..., Mn@]     = C-term;
  //17.00305                = protein C-terminal
  //1.00794                 = protein N-terminal
  //there are 3 type not handled NeutralLoss, ResiduesNterm/ResiduesCterm and ResiduesProteinNterm/ResiduesProteinCterm
  $Residue_mono = get_amino_mass_arr();
  
  if($engine == 'GPM' or $engine == 'COMET' or $engine == 'MSGFPL'){
    foreach($rt as $fix_var_type => $thisTypeMods){
      //fixed; variable; redefined
       
      $mod_str = '';
      $aa_arr = array();
      if(!is_array($thisTypeMods)) continue;
      foreach($thisTypeMods as $mod_name){
        
        if(isset($mascot_mod_array[$mod_name])){
          $R_str = '';
          foreach($mascot_mod_array[$mod_name] as $theArr){
            
            if($theArr['location'] == 'Residues'){
              
              $diff = $theArr['mono'] - $Residue_mono[$theArr['R']];
              if($mod_str) $mod_str .= ":";
              $mod_str .= $diff.'@'.$theArr['R'];
              if($fix_var_type == 'fixed'){
                $aa_arr[$theArr['R']] = $diff;
                if($engine == 'MSGFPL'){
                  $aa_arr['name'][$theArr['R']] = $mod_name;
                }
              }else{
                $R_str .=$theArr['R'];
              }
            }else if($theArr['location'] == 'Nterm'){
              if($fix_var_type == 'redefined'){
                $rt_gpm['redefined_Nterm'] = $theArr['mono'].'@[';
                
              }else{
                if($mod_str) $mod_str .= ":";
                $mod_str .= $theArr['mono'].'@[';
                $rt_comet[$fix_var_type."Nterm"] = $theArr['mono'];
                if($engine == 'MSGFPL'){
                  $rt_comet['name'][$fix_var_type."Nterm"] = $mod_name;
                }
              }
            }else if($theArr['location'] == 'Cterm'){
              if($fix_var_type == 'redefined'){
                $rt_gpm['redefined_Cterm'] = $theArr['mono'].'@]';
              }else{
                if($mod_str) $mod_str .= ":";
                $mod_str .= $theArr['mono'].'@]';
                $rt_comet[$fix_var_type."Cterm"] = $theArr['mono'];
                if($engine == 'MSGFPL'){
                  $rt_comet['name'][$fix_var_type."Cterm"] = $mod_name;
                }
                
              }
            }else if($theArr['location'] == 'ProteinNterm'){
              if($fix_var_type == 'variable' or $fix_var_type == 'redefined'){
                //not in use
                $rt_gpm['variable_ProteinNterm'] = $theArr['mono'].'@[';
              }else{
                $rt_comet['add_Nterm_protein'] = $theArr['mono'];
                $rt_gpm['fixed_ProteinNterm'] = $theArr['mono'].'@[';
                if($engine == 'MSGFPL'){
                  $rt_comet['name']['add_Nterm_protein'] = $mod_name;
                }
              }
            }else if($theArr['location'] == 'ProteinCterm'){
              if($fix_var_type == 'variable' or $fix_var_type == 'redefined'){
                //not in use
                $rt_gpm['variable_ProteinCterm'] = $theArr['mono'].'@]';
              }else{
                $rt_comet['add_Cterm_protein'] = $theArr['mono'];
                $rt_gpm['fixed_ProteinCterm'] = $theArr['mono'].'@]';
                if($engine == 'MSGFPL'){
                  $rt_comet['name']['add_Cterm_protein'] = $mod_name;
                }
              }
            }
          }
          if($R_str) {
            $aa_arr[$R_str] = $diff;
            if($engine == 'MSGFPL'){
              $aa_arr['name'][$R_str] = $mod_name;
            }
          }
        }
      }
      
      $rt_gpm[$fix_var_type] = $mod_str;
      if(isset($rt_comet['fixedCterm'])) $rt_comet['add_Cterm_peptide'] = $rt_comet['fixedCterm'];
      if(isset($rt_comet['fixedNterm'])) $rt_comet['add_Nterm_peptide'] = $rt_comet['fixedNterm'];
      //if(isset($rt_comet['variableCterm'])) $rt_comet['variable_C_terminus'] = $rt_comet['variableCterm'];
      //if(isset($rt_comet['variableNterm'])) $rt_comet['variable_N_terminus'] = $rt_comet['variableNterm'];
      $rt_comet[$fix_var_type] = $aa_arr;
    }
    
    if($engine == 'GPM'){
      return $rt_gpm;
    }else if($engine == 'COMET' or $engine == 'MSGFPL' ){
      return $rt_comet;
    }
  }
  
  
  return $rt;
}
//-------------------------------------------
function make_gpm_cleavage_value($value_arr){
// parse from comet comet_enzyme_info array
//-------------------------------------------
  $rt = '';
  list($termianl, $amino, $except_amino) = $value_arr;
  $part1 = "[$amino]";
  if($except_amino == '-') {
    $part2 = '[X]';
  }else{
    $part2 = '{'.$except_amino.'}';
  }
  if($termianl){
    $rt = $part1 .'|'. $part2;
  }else{
    $rt = $part2 .'|'. $part1;
  }
  return $rt;
}
//-------------------------------------------
function get_gpm_default_param($gpm_parm_dir, $refresh=0){
//-------------------------------------------
  global $gpm_ip;
  $rt = array();
  $handle = '';
  $file = $gpm_parm_dir."/gpm.params.new";
  if(!$refresh and is_file($file)){
    $lines = file($gpm_parm_dir."/gpm.params.new");
    foreach($lines as $v){
      $tmp = explode("=", trim($v), 2);
      $rt[$tmp[0]] = $tmp[1];
    }
  }else{
     
    $timeout = 15;
    $old = ini_set('default_socket_timeout', $timeout);
    $url_tmp = "http://" . $gpm_ip . "/tandem/" . "thegpm_tandem_a.html";
    $HTML_form = file_get_contents($url_tmp);
    ini_set('default_socket_timeout', $old);
    
     
    if(!$HTML_form) return $rt;
    
    $form_arr = form_to_data($HTML_form);
     
    $to_file = '';
    if(isset($form_arr['data'])){
      foreach($form_arr['data'] as $k=>$a){
        if(!trim($k) or
           $k == 'Button2' or
           $k == 'spectrum, path' or
           $k == 'submit') continue;
        $theValue = '';
        foreach($a as $kk=>$vv){
          if($theValue) $theValue .=";";
          $theValue .= $kk;
        }
        $k = preg_replace("/[, ]/", "_", $k);
        $k = str_replace('+', '99', $k);
        $k = str_replace('-', '88', $k);
        if($k == 'scoring__a_ions' or $k == 'soring__c_ions' or $k == 'soring__x_ions' or $k == 'scoring__z_ions' ){
          $theValue = '';
        }
        
        $str = $k."=".$theValue;
        $to_file .= $k."=".$theValue."\n";
        $rt[$k] = $theValue;
      }
    }
    /*
    if(isset($form_arr['data_possible']['DB'])){
       $option_str = implode(";;", $form_arr['data_possible']['DB']);
       $to_file .= "OPTIONS_DB=$option_str\n";
       $rt['OPTIONS_DB'] = $option_str;
    }
    */
    if (_is_writable($gpm_parm_dir)) {
       
      $handle = fopen($file, 'w');
      if($handle){
        fwrite($handle, $to_file);
        fclose($handle);
      }
    }else{
      echo "error: cannot write to $gpm_parm_dir";
    }
  }
  return $rt;
}
//----------------------------------------------------
function get_msfragger_default_param(){
//----------------------------------------------------
  //from EXT/
  $MSFRAGGER_default_parm_dir = preg_replace("/\/$/", "", MSFRAGGER_BIN_PATH);
  if(_is_file($MSFRAGGER_default_parm_dir."/fragger.params")){
    $lines = file($MSFRAGGER_default_parm_dir."/fragger.params");
  }
  $bridger_arr = get_comet_param($lines);
  return $bridger_arr['comet'];
}
//----------------------------------------------------
function get_comet_default_param(){
//----------------------------------------------------
  //from EXT/
  $comet_default_parm_dir = preg_replace("/\/$/", "", COMET_BIN_PATH);
  if(!_is_file($comet_default_parm_dir. '/comet.params.new')){
     $comet_cmd = $comet_default_parm_dir . '/comet.exe';
     system("cd ". $comet_default_parm_dir. "; $comet_cmd -p");
  }
  if(_is_file($comet_default_parm_dir."/comet.params.new")){
    $lines = file($comet_default_parm_dir."/comet.params.new");
  }else{
    $comet_default_parm_dir = dirname(__FILE__)."/autoSearch";
    $lines = file($comet_default_parm_dir."/comet.params.new");
  }
  return get_comet_param($lines);
}
//----------------------------------------------------
function get_MSGFPL_param($lines){
//----------------------------------------------------
  $rt = array();
  if(is_array($lines)) {
    $is_default_file = 1;
  }else{
		echo "ERROR:  <br>";
	  echo "Please make sure that MS-GF+ has been installed in GPM server.";
		echo "<br>Read the installation instruction in Prohits/install/Prohits_COMET/ for detail.";exit;
  }
  foreach($lines as $buffer){
    $buffer = trim($buffer);
    if(!$buffer || preg_match("/^#/", $buffer ) ) continue;
    $tmp_arr= preg_split("/=/", $buffer, 2);
    $tmp_name = trim($tmp_arr[0]);
    $rt[$tmp_name]= (count($tmp_arr)>1)? trim($tmp_arr[1]):'';
     
  }
  return $rt;
}
 
//----------------------------------------------------
function get_comet_param($lines){
//----------------------------------------------------
  $rt = array();
  if(is_array($lines)) {
    $is_default_file = 1;
  }else{
		echo "ERROR:  <br>";
	  echo "Please make sure that COMET has been installed in GPM server.";
		echo "<br>Read the installation instruction in Prohits/install/Prohits_COMET/ for detail.";exit;
  }
  
  $enzyme_started = 0;
  $MOD_started = 0;
  $sequest_started = 0;
  $enzyme_info_started = 0;
  //$rt['comet']['CHARGE'] = '';
  $rt['comet_enzyme_info_lines'] = '';
echo "";
  
  $is_header = 1;
  $rt['comet']['HEADER'] = '';
  foreach($lines as $buffer){
    
    if($is_header and preg_match("/^#/", $buffer )){
      $rt['comet']['HEADER'] .=$buffer;
      continue;
    }else{
      $is_header = 0;
    }
    $buffer = trim($buffer);
    if(!$buffer || preg_match("/^#/", $buffer ) ) continue;
    if(strpos($buffer, "[COMET_ENZYME_INFO]") === 0){
       $enzyme_info_started = 1;
       continue;
    }
    if($enzyme_info_started){
        $rt['comet_enzyme_info_lines'] .= $buffer."\n";
        $tmp_arr= preg_split("/\s+/", $buffer, 5);
        $this_index = 0;
        if(count($tmp_arr)>1){
          $this_index = (int)$tmp_arr[0];
        }else{
          continue;
        }
        $rt['comet_enzyme_info']['name'][$this_index]= $tmp_arr[1];
        $rt['comet_enzyme_info']['other'][$this_index]= array($tmp_arr[2], $tmp_arr[3], $tmp_arr[4]);
    }else{
      $buffer = preg_replace("/#.*$/", "", $buffer);
      
      $tmp_arr= preg_split("/=/", $buffer, 3);
      
      $tmp_name = trim($tmp_arr[0]);
      $rt['comet'][$tmp_name]= (count($tmp_arr)>1)? trim($tmp_arr[1]):'';
    }
  }
  return $rt;
}

//---------------------------------
function form_to_data($HTML_form) {
//---------------------------------
   // extract from first <form> block
   if (preg_match("/<form.+?<\/form[^>]*>/ims", $HTML_form, $uu)) {
      $form = $uu[0];
      
      // fetch METHOD=
      if (preg_match("/<form[^>]+method=[\"']?(\w+)/ims", $form, $uu)) {
         $method = strtoupper($uu[1]);
      }
      else {
         $method = "GET";
      }
      // and URL=
      if (preg_match("/<form[^>]+?action=[\"']?([^\"'>\s]+)/ims", $form, $uu)) {
         $url = $uu[1];
      }
      else {
         $url = $browser->url;
      }

      // and type= if any
      if (preg_match("/<form[^>]+enctype=[\"']?([^\"'>\s]+)/ims", $form, $uu)) {
         $ct = $uu[1];
      }
      else {
         $ct = "application/x-www-form-urlencoded";
      }
      
      // getall fields
      $d = array();
      $d_possible = array();
      preg_match_all("/  (<input[^>]+>)  |  <select[^>]+>(.+?)<\/select  |  <textarea[^>]+>([^<]+)  /xims", $form, $matches);
 
//print_r($matches);exit;
      foreach ($matches[0] as $i=>$_full) {
        
         // general fields
         $name = "";
         $value = "";
         $type = "";
         $desc = "";
         if (preg_match("/<[^>]+name=\"(.+?)\"[^>]*>/ims", $_full, $uu)) {
            $name = $uu[1];
         }
         if (preg_match("/<[^>]+value=\"([^\"]*)/ims", $_full, $uu)) {
            $value = $uu[1];
         }
         if (preg_match("/<[^>]+type=\"(.+?)\"[^>]*>/ims", $_full, $uu)) {
          
            $type = strtolower($uu[1]);
         }
         if (preg_match("/<label[^>]+for=\"$name\"[^>]*>(.+?)<\/label>/ims", $_full, $uu)) {
            $desc = strip_tags($uu[1]);
         }
         $selected = preg_match("/<[^>]+\s(selected|checked)[=>\s]/ims", $_full, $uu);

         // input
         if (strlen($matches[1][$i])) {
            if (($type == "checkbox") and !$selected) {
              $value = '';
            }
            if (($type != "radio") or ($selected)) {
               $d[$name][$value] = "$desc";
            }
         }
         // select
         elseif (strlen($matches[2][$i])) {
            preg_match_all("/<option(?: [^>]+value=[\"']?([^\"'>]*))?[^>]*>([^<]*)/xims", $_full, $uu);
            foreach ($uu[1] as $n=>$value) {
               // either from value= or plain text following opening <option> tag
               $desc = $uu[2][$n];
               if (!$value) {
                  $value = $desc;
               }
               // only add the allowed ones
               if ($selected = preg_match("/<[^>]+\s(selected|checked)[=>\s]/ims", $uu[0][$n])) { 
                  $d[$name][$value] = "";
               }

               // add possible values + desc              
               $d_possible[$name][$value] = trim($desc);
            }
            if(!isset($d[$name])) $d[$name]['']='';
            continue; // but skip base
         }
         // textarea
         elseif (strlen($matches[3][$i])) {
            $value = $matches[3][$i];
            $d[$name][$value] = "$desc";
         }

         else {
            // ..
         }
         
         // add always
         $d_possible[$name][$value] = "$desc";
        
      }
   }
   
  
//print_r($d_possible);exit;

   // multiple return values
   return array(
      "method" => $method,
      "url" => $url,
      "enctype" => $type,
      "data" => $d,
      "data_possible" => $d_possible,
   );
   
}
function remove_hits_and_peptide($table,$frm_delete_well_id, $task_ID='', $frm_delete_searchEngine=''){
  global $managerDB;
  global $USER;
  global $HITS_DB;
  global $project_ID_DBname;  
  $tableSearchResults = $table.'SearchResults';
  
  if($frm_delete_well_id == 'All' and $frm_delete_searchEngine == 'All' and $USER->Type != 'Admin'){
    echo "only admin has permision to remove all hits.";
    return false;
  }
   
  $SQL = "select WellID, SearchEngines, SavedBy, DataFiles from $tableSearchResults";
  $where = '';
  if($frm_delete_well_id != 'All'){
    if(!$where){
       $where = " where";
    }else{
       $where .= " and";
    }
    $where .= " WellID='$frm_delete_well_id'";
  }
  if($USER->Type != 'Admin'){
    if(!$where){
       $where = " where";
    }else{
       $where .= " and";
    }
    $where .= " SavedBy='$USER->ID'";
  }
  if($task_ID){
    if(!$where){
       $where = " where";
    }else{
       $where .= " and";
    }
    $where .= " TaskID='$task_ID'";
  } 
  if($frm_delete_searchEngine and $frm_delete_searchEngine != 'All'){
    if(!$where){
       $where = " where";
    }else{
       $where .= " and";
    }
    $where .= " SearchEngines='$frm_delete_searchEngine'";
  }
  $SQL .= $where;    
  $the_records = $managerDB->fetchAll($SQL);
  
  if(!$the_records) return false;
     
  $SQL ="UPDATE $tableSearchResults SET SavedBy=NULL";
  if($frm_delete_well_id != 'All'){
    $SQL .= " WHERE WellID='$frm_delete_well_id'";
  }else{
    $SQL .= " WHERE 1 ";
  }
  if($USER->Type != 'Admin'){
    $SQL .= " and SavedBy='$USER->ID'";
  }
  if($task_ID){
    $SQL .= " and TaskID='$task_ID'";
  } 
  if($frm_delete_searchEngine and $frm_delete_searchEngine !='All'){
    $SQL .= " and SearchEngines='$frm_delete_searchEngine'";
  }
    
  $managerDB->update($SQL);
  $objDB = array();
  foreach($the_records as $the_record){
    $well_id = $the_record['WellID'];
    $SQL = "select ProjectID from $table where ID='$well_id'";
    $project_record = $managerDB->fetch($SQL);
    $hits_project_ID = $project_record['ProjectID'];
    if(!$hits_project_ID or !$the_record['SavedBy']){
      continue;
    }
    $DB_Name = $project_ID_DBname[$hits_project_ID];
    if(!isset($objDB[$DB_Name])){
      $objDB[$DB_Name] = new mysqlDB($HITS_DB[$DB_Name]);
    }
    $tmp_file = mysqli_real_escape_string($managerDB->link, substr($the_record['DataFiles'], 0, 255));    
    if($the_record['SearchEngines'] == 'MSPLIT' || $the_record['SearchEngines'] == 'MSPLIT_DDA'){
      $SQL = "select ID from Hits_GeneLevel where ResultFile='".$tmp_file."'";
    }else{
      $SQL = "select ID from Hits where ResultFile='".$tmp_file."'";
    }
    $new_hits = $objDB[$DB_Name]->fetchAll($SQL);    
    for($i = 0; $i < count($new_hits); $i++){
      $hit_ID = $new_hits[$i]['ID'];
      if($the_record['SearchEngines'] == 'MSPLIT' || $the_record['SearchEngines'] == 'MSPLIT_DDA'){
        $objDB[$DB_Name]->execute("delete from Hits_GeneLevel where ID='$hit_ID'");
        $objDB[$DB_Name]->execute("delete from Peptide_GeneLevel where HitID='$hit_ID'");
        $SQL = "DELETE FROM `GeneLevelParse` WHERE `pepXML_original`='$tmp_file' and `Machine`='$table'";
        $managerDB-> execute($SQL);
        //$objDB[$DB_Name]->execute("delete from HitNote where HitID='$hit_ID'");
      }else{
        $objDB[$DB_Name]->execute("delete from Hits where ID='$hit_ID'");
        if($the_record['DataFiles'] == 'SEQUEST'){
          $objDB[$DB_Name]->execute("delete from SequestPeptide where HitID='$hit_ID'");
        }else{
          $objDB[$DB_Name]->execute("delete from Peptide where HitID='$hit_ID'");
        }
        $objDB[$DB_Name]->execute("delete from HitNote where HitID='$hit_ID'");
      }
    }
  }   
  return true;
}

function remove_TppProtein_and_TppPeptide($table, $frm_delete_well_id, $tppTaskID='', $frm_delete_searchEngine=''){
  global $managerDB;
  global $USER;
  global $HITS_DB;
  global $project_ID_DBname;
  $tableTppResults = $table.'tppResults';  
  $objDB = array();
  if($frm_delete_well_id == 'All' and $USER->Type == 'Admin'){
    $SQL = "select T.ProjectID, R.SavedBy, R.pepXML, R.protXML, R.SearchEngine  from $table T, $tableTppResults R where 
            T.ID=R.WellID and R.TppTaskID='$tppTaskID'";
  }else{
    $SQL = "select T.ProjectID, R.SavedBy, R.pepXML, R.protXML, R.SearchEngine from $table T, $tableTppResults R where 
            T.ID=R.WellID and R.WellID='$frm_delete_well_id'";
    if($USER->Type != 'Admin'){
      $SQL .= " and R.SavedBy='$USER->ID'";
    }
    if($tppTaskID){
      $SQL .= " and R.TppTaskID='$tppTaskID'";
    } 
    if($frm_delete_searchEngine){
      $SQL .= " and R.SearchEngine='$frm_delete_searchEngine'";
    }
  }
   
  $the_records = $managerDB->fetchAll($SQL);
  if(!$the_records) return false;
  if(!$the_records || !$the_records[0]['ProjectID']) return false;
  $hits_project_ID = $the_records[0]['ProjectID'];
  if(!$hits_project_ID){
    echo "no project ID";
    return false;
  }
  
  $SQL ="UPDATE $tableTppResults SET SavedBy='0' WHERE 1";
  
//2017-06-22 modified by JP---------------------------------------------  
  if($USER->Type != 'Admin'){
    $SQL .= " and SavedBy='$USER->ID'";
  }
  if($tppTaskID){
    $SQL .= " and TppTaskID='$tppTaskID'";
  }
  if($frm_delete_well_id != 'All'){
    $SQL .= " and WellID='$frm_delete_well_id'";
    if($frm_delete_searchEngine){
      $SQL .= " and SearchEngine='$frm_delete_searchEngine'";
    }
  }else{
    if($frm_delete_searchEngine == 'iProphet'){
      $SQL .= " and SearchEngine='iProphet'";
    }else{
      $SQL .= " and SearchEngine!='iProphet'";
    }
  }
//---------------------------------------------------------------------  
  
  $managerDB->update($SQL);
   
  foreach($the_records as $the_record){
    $hits_project_ID = $the_record['ProjectID'];
    if(!$hits_project_ID or !$the_record['SavedBy']){
      continue;
    }
//2017-06-22 modified by JP---------------------------------------------   
    if($frm_delete_well_id == 'All'){
      if($frm_delete_searchEngine == 'iProphet'){
        if($the_record['SearchEngine'] != 'iProphet') continue;
      }else{
        if($the_record['SearchEngine'] == 'iProphet') continue;
      }
    }
//---------------------------------------------------------------------- 
   
    $DB_Name = $project_ID_DBname[$hits_project_ID];
    if(!isset($objDB[$DB_Name])){
      $objDB[$DB_Name] = new mysqlDB($HITS_DB[$DB_Name]);
    }
    $SQL = "select ID from TppProtein where XmlFile='".$the_record['protXML']."'";
    $new_hits = $objDB[$DB_Name]->fetchAll($SQL);
    for($i = 0; $i < count($new_hits); $i++){
      $hit_ID = $new_hits[$i]['ID'];
      $objDB[$DB_Name]->execute("delete from TppPeptideGroup where ProteinID='$hit_ID'");
    }
    $objDB[$DB_Name]->execute("delete from TppProtein where XmlFile='".$the_record['protXML']."'");
    $objDB[$DB_Name]->execute("delete from TppPeptide where XmlFile='".$the_record['pepXML']."'");
    //------for geneLevel----------------------------------------------------------------------------------------------------------------
    $tmp_geneLevel_arr = $objDB[$DB_Name]->fetchAll("SELECT `ID` FROM `Hits_GeneLevel` WHERE `ResultFile`='".$the_record['pepXML']."'");
    $geneLevel_hits_arr = array();
    foreach($tmp_geneLevel_arr as $geneLevel_hitsID){
      $geneLevel_hits_arr[] = $geneLevel_hitsID['ID'];
    }
    if($geneLevel_hits_arr){
      $geneLevel_hits_str = implode(',',$geneLevel_hits_arr);
      $objDB[$DB_Name]->execute("delete from `Hits_GeneLevel` where `ID` in ($geneLevel_hits_str)");
      $objDB[$DB_Name]->execute("delete from `Peptide_GeneLevel` where `HitID` in ($geneLevel_hits_str)");
    }
    $SQL = "DELETE FROM `GeneLevelParse` WHERE `Machine`='$table' AND `pepXML_original`='".$the_record['pepXML']."'";
    $managerDB->execute($SQL);
  }
  return true;
}   




function unzip_gz($filepath){
  $unzipped_file_path = preg_replace("/[.]gz$/", "", $filepath);
  if(!_is_file($unzipped_file_path)){
    $cmd = "gzip -N -d -f -S \".gz\" -c " . escapeshellarg($filepath) ." > " . escapeshellarg($unzipped_file_path);
    $rt = system($cmd . " 2>&1");
    if(!_is_file($unzipped_file_path)){
      fatalError( "gzip returned an error. $cmd");
    }
  }
  return $unzipped_file_path;
}
function cleanFileName($str) {
  $str  = preg_replace("/[^.a-zA-Z0-9_-]/", "", $str);
  return $str;
}
function get_runing_host($QSUB_bin_path, $QSUB_task_ID, $status_log){
  //for cluster only.
  $host_name = '';
  $the_qsub_user = '';
  $got_the_task = 0;
  $sleep_time = 5;
  $total_sleep_time = 0;
  $max_sleep_time = 300;
  if(defined("QSUB_USER") and QSUB_USER){
    if(preg_match("/([^ ]*)$/", QSUB_USER, $matches)){
      $the_qsub_user = " -u ". $matches[1];
    } 
  }
  $command = "'$QSUB_bin_path/qstat' -cb$the_qsub_user 2>&1";
  sleep($sleep_time);
  $total_sleep_time += $sleep_time;
  while (!$host_name and $total_sleep_time < $max_sleep_time){
    sleep($sleep_time);
    $total_sleep_time += $sleep_time;
    $output = array();
    exec($command, $output);
    //print_r($output);
    foreach($output as $theLine){
      $cols = preg_split("/\s+/", trim($theLine));
      //print_r($cols);
      if(count($cols) > 4){
        if($cols[0] == $QSUB_task_ID){
          $got_the_task = 1;
          if($cols[4] == 'r'){
            if(preg_match("/@([^.]+)/", $cols[7], $matches)){
              $host_name = $matches[1];
              break;
            }
          }
        }
      }
    }
    if(!$got_the_task){
      writeLog("ERROR: QSUB ID $QSUB_task_ID not in task list.", $status_log);
      return '';
    }
  }
  if(!$host_name){
    if($got_the_task and $total_sleep_time > $max_sleep_time){
      $the_qsub_user = '';
      writeLog("ERROR: cluster is busy. Please run the task later.", $status_log);
    }else{
      writeLog("ERROR: didn't find host name for QSUB ID $QSUB_task_ID.", $status_log);
    }
  }else{
    echo "run all task in the same host: $host_name\n";
  }
  return $host_name;
}

function check_TORQUE_task($taskDir, $QSUB_bin_PATH, $QSUB_task_ID,  $tasklog){
//for the local search only. loop check until finish.
  $qsub_timeout_hrs = 10;
  if(defined("QSUB_TIMEOUT_HRS")) $qsub_timeout_hrs = QSUB_TIMEOUT_HRS;
  $sleep_sec = 0;
  
  echo $QSUB_task_ID;
  $QSUB_task_ID = trim($QSUB_task_ID);
  $output = '';
  $finished = 0;
  $task_ID_NUM = '';
  if(!$QSUB_task_ID){
    return 'no Torque id passed!';
  }
   
  $command = "'$QSUB_bin_PATH/qstat' -j $QSUB_task_ID 2>&1";
  //if(defined("QSUB_USER") and QSUB_USER){
    //$command = QSUB_USER. " $command";
  //}
  print "\n";
  print "check: qstat\n";
  print "$command\n";
  
  while (!$finished){
    print ".";
    $output = array();
    exec($command, $output);
    
    if (strpos($output[0], "jobs do not exist")) {
      $finished = 1;
      
      
      $q_out_file = $taskDir."/STDIN.o".$QSUB_task_ID;  
      $check_out_file = 1;
      echo "$q_out_file\n";
      //print "\nqsub ID:$QSUB_task_ID:outfile:$q_out_file\n";exit;
      while ($check_out_file > 0){
        if(_is_file($q_out_file)){
          print "qsub output: $q_out_file\n";
          system("cat \"$q_out_file\" >>  \"$tasklog\"");
          echo file_get_contents( "$q_out_file");
		      sleep(1);
          //unlink("$q_out_file");
          return;
        }else{
          sleep(2);
          print "*";
          $check_out_file++;
          if($check_out_file > 120){
            return 'error';
          }
        }
      }
    }
    sleep(20); 
    $sleep_sec += 20;
    if($sleep_sec > 3600*$qsub_timeout_hrs){
       
    	$msg = "\nThe cmd is running more than $qsub_timeout_hrs. qsub '$QSUB_task_ID' should be killed.\n";
      writeLog($msg,$tasklog);
      print $msg;
    	return 'error';
    }
  }
  return 'error';
}

function is_ps_running($PID, $script, $theTaskDir) {
   
  $rt = '';
  $command = '';
  $output = '';
  $QSUB_task_ID = '';
  $QSUB_task_NAME = 'STDIN';
   
  if (is_numeric($PID)){
    $PID = trim($PID);
    $command = "ps -f $PID | grep ' $PID '";
    if($script){
      $command .= " | grep $script";
    }
    $output = `$command`;
    if($output){
      $rt = 1;
    }
  }else{
    //putenv ("SGE_ROOT=/opt/gridengine");
    //putenv ("SGE_QMASTER_PORT=536");
    //putenv ("SGE_EXECD_PORT=537");
    
    print "\nIt is SGE task.\n";
    $tmp_psID = explode(" ", $PID);
    $QSUB_task_ID =$tmp_psID[0];
    if(isset($tmp_psID[1])){
      $QSUB_task_NAME = $tmp_psID[1];
    }
    $STDINo = $QSUB_task_NAME.".o$QSUB_task_ID";
    $command = QSUB_BIN_PATH."qstat -j $QSUB_task_ID 2>&1";
    
    /*
    $com = "echo '/home/slave/TEST_qsub/testCluster.bash' | sudo -u slave '/opt/gridengine/bin/linux-x64/qsub' -wd '/home/slave/TEST_qsub' -o 'localhost:/home/slave/TEST_qsub' -j y 2>&1";
    
    exec($com." 2>&1", $output);
    echo $com;
    print_r($output);
    exit;
    */
    
    $output = array();
    echo $command;
    exec($command, $output);
    print_r($output);
    if (strpos($output[0], "jobs do not exist")) {
      $cwd = getcwd();
      $finished = 1;
      chdir($theTaskDir);
      $command = "cat $STDINo >> task.log";
      `$command`;
      chdir($cwd);
    }else{
      //it should check the job is hunging. the hunging job should be deleted.
      $rt = 1;
    }
  }
  return $rt;
}


function get_local_gpm_archive_path($tableName, $theTaskID, $isUmpireQUANT = ''){
  if(defined('SEARCH_ARCHIVE')){
    if(!_is_dir(SEARCH_ARCHIVE)){
      mkdir ( SEARCH_ARCHIVE, 0777, true);
    }
  }
  if(defined('SEARCH_ARCHIVE') and _is_writable(SEARCH_ARCHIVE)){
    $GPM_datapath = preg_replace("/\/$/",'', SEARCH_ARCHIVE);
  }else{
    //default archive path in /thegpm/gpm/archive/
    $GPM_datapath = dirname(GPM_CGI_PATH) . "/gpm/archive";
  }
  if(defined('SEARCH_ARCHIVE_OLD') and _is_writable(SEARCH_ARCHIVE_OLD)){
    //find the old task in old archive
    $theGPM_path = preg_replace("/\/$/",'', SEARCH_ARCHIVE_OLD);
    $dirs = scandir($theGPM_path);
    foreach($dirs as $theDir){
      if($theDir == $tableName){
        if($isUmpireQUANT){
          $tmp_taskDir = $theGPM_path."/". $tableName."/DIAUmpire_Quant_Tasks/task". $theTaskID;
        }else{
          $tmp_gpm_machine_dir = $theGPM_path."/". $tableName;
          $tmp_taskDir = $tmp_gpm_machine_dir."/task". $theTaskID;
        }
        if(_is_dir($tmp_taskDir)){
          $GPM_datapath = $theGPM_path;
          break;
        }
      }
    }
  }
  return $GPM_datapath;
}
function prepare_run_search_on_local($tableName, $WellID, $theTaskID, $raw_file_path, $Engine, $msplit_file_arr = array()){
//for the local search only.
 
    global $is_SWATH_file;
    global $theTask_arr;
    $run_times = 1;
    $prohits_mzML_file_dir ='';
    $prohits_mzML_fileName = '';
    $prohits_mzML_file_type ='';
    $prohits_mzML_fileNameBase = '';
    $linked_raw_file_path = '';
    
    $diaumpire_results_dir_path = '';
    $swath_file_name_base_in_TPP = '';
    $swath_search_dir_name = '';
    $link_from_tmp_dir = '';  //for DiaUmpireSE mzXML file
    $mzDirPath = '';  
    $gpmDbFile = '';
    $MSPLIT_FILE_arr = array();
    if(!defined("GPM_CGI_PATH") or !is_dir(GPM_CGI_PATH) ){
      fatalError("please set GPM in the local server, then define full GPM_CGI_PATH in the conf file.");
    }
    $gpm_cgi_path = preg_replace("/\/$/", "", GPM_CGI_PATH);
    
    $GPM_datapath = get_local_gpm_archive_path($tableName, $theTaskID);
     
    
    $gpm_machine_dir = $GPM_datapath ."/". $tableName;
    $taskDir     = $gpm_machine_dir."/task". $theTaskID;
    $tasklog     = $taskDir."/task.log";
    $statuslog     = $taskDir."/status.log";
    $STDIN_DIR   = $taskDir;
    $taskComFile   = $taskDir."/task.commands";
    $paramFile = '';
    $resultFile_suffix = '';
    if($Engine == 'GPM'){
      $paramFile = 'tandemparam.xml';
      $resultFile_suffix = "_gpm.xml";
    }else if($Engine == 'COMET'){
      $paramFile = 'comet.params';
      $resultFile_suffix = "_comet.pep.xml";
    }else if($Engine == 'MSFragger'){
      $paramFile = 'msfragger.params';
      $resultFile_suffix = ".msfragger.pep.xml";
    }else if($Engine == 'MSGFPL'){
      $paramFile = 'msgfpl_mods.txt';
      $resultFile_suffix = "_msgfpl.pep.xml";
      
    }else if($Engine == 'SEQUEST'){
      $paramFile = '';
    }else if($Engine == 'TPP'){
      $tasklog     = $taskDir."/TPPtask.log";
      $taskComFile   = $taskDir."/TPPtask.commands";
      $gpmDbFile = get_gpm_db_file_path('', $theTask_arr['SearchEngines']);
    }else if($Engine == 'MSPLIT'){
      $paramFile   = "var_wind.txt";
    }
    
    umask(0);
    $paramFilePath = $taskDir."/". $paramFile;
    $xtandem_cmd = preg_replace("/\/$/", "", TPP_BIN_PATH)."/tandem";
    
    if(!_is_dir($taskDir)){
      if(!mkdir($taskDir, 0775, true)){
        fatalError( "cannot make folder $taskDir.");
      }
    }else{
      system("chmod -R 775 $taskDir >/dev/null 2>&1"); 
    }
    
    
    if($raw_file_path){
      if(preg_match("/[.]gz$/", $raw_file_path, $matches)){
        $raw_file_path = unzip_gz($raw_file_path);
      }
      list($prohits_mzML_file_dir, $prohits_mzML_fileName, $prohits_mzML_file_type, $prohits_mzML_fileNameBase) = array_values(pathinfo($raw_file_path));
       
      
      //****************************
      if($Engine == 'DIAUmpireSE' or $Engine == 'DIAUmpireQuant'){
      //****************************
         //it should be mzXML file
         echo "$prohits_mzML_file_dir, $prohits_mzML_fileName, $prohits_mzML_file_type, $prohits_mzML_fileNameBase\n";
      
         $mzDirPath = $gpm_machine_dir."/".$WellID ."_". $prohits_mzML_fileNameBase;
         $mzFilePath = $mzDirPath . "/" . $WellID ."_". $prohits_mzML_fileName;
         $paramFilePath = $mzDirPath."/". "diaumpire.se_params";
         $tasklog    = $mzDirPath."/task.log";
         $taskComFile  = $mzDirPath."/task.commands";
         if(!_is_dir($mzDirPath)){
           mkdir($mzDirPath, 0775, true);
         }else{
           system("chmod -R 775 $mzDirPath >/dev/null 2>&1"); 
         }
         //$taskDir  = $mzDirPath;
         $linked_raw_file_path = $mzDirPath."/".$WellID."_".$prohits_mzML_fileName;
         //echo $linked_raw_file_path;
      }else if($is_SWATH_file){
        //for DIAUmpire Q1-Q3 mzXML file search.
        $lowerEngine = strtolower($Engine);
        $run_times = 3;
        $diaumpire_results_dir_path = $gpm_machine_dir."/".$WellID ."_". $prohits_mzML_fileNameBase;
        $swath_file_name_base_in_TPP = $WellID."_".$prohits_mzML_fileNameBase;
        $swath_search_dir_name = $swath_file_name_base_in_TPP ."_".$lowerEngine;
        //each searh has it own folder.
        $taskDir = $taskDir ."/". $swath_search_dir_name;
        if(!_is_dir($diaumpire_results_dir_path)){
          writeLog("error: diaumpire results dir '$diaumpire_results_dir_path' doesn't exsist.");
          exit;
        }
        if(!is_dir($taskDir)){
          mkdir("$taskDir", 0775, true);
        }else{
          system("chmod -R 775 $taskDir >/dev/null 2>&1"); 
        }
        //don't need to link raw file. it shold link diaumpireSE results folder.
        $linked_raw_file_path = '';
        linkDIAUmpireFiles($swath_file_name_base_in_TPP, $diaumpire_results_dir_path, $taskDir);
      }else{
        //for other search
        if(defined("LINK_MZML_TO_TASK_DIR") and !LINK_MZML_TO_TASK_DIR){
          $linked_raw_file_path = $raw_file_path;
        }else{
          $linked_raw_file_path = $taskDir."/".$WellID."_".$prohits_mzML_fileName;
        }
      }
      echo "\n".$linked_raw_file_path;
      //exit;
      if($linked_raw_file_path){
        if($Engine == 'DIAUmpireSE'){
          //it will be linked by command file.
          if(defined("PROHITS_TMP") and PROHITS_TMP){
            $link_from_tmp_dir = preg_replace("/\/$/", "", PROHITS_TMP)."/Prohits_tmp/";
          }else{
            $link_from_tmp_dir = $raw_file_path;
          }
        }else if(!_is_file($linked_raw_file_path)) {
          //all serching and Umpire Quant will be linked.
          //$com = "ln -s ". escapeshellarg($raw_file_path) ." ". escapeshellarg($linked_raw_file_path);
          //echo "\n$com\n";
          //exec("$com 2>&1", $output);
          $OK= link_file($raw_file_path, $linked_raw_file_path);
          //if(isset($output[0])){
          if(!$OK){
            //link error
            //if(preg_match("/Permission denied|failed to create symbolic link/", $output[0], $matches)){
            //  echo $output[0];
            //}
            //try to copy.
            $com = "cp ". escapeshellarg($raw_file_path) ." ". escapeshellarg($linked_raw_file_path);
            exec("$com 2>&1", $output);
          }
        }
      }
      echo "$prohits_mzML_file_dir, $prohits_mzML_fileName, $prohits_mzML_file_type, $prohits_mzML_fileNameBase\n";
      
    }else if($Engine == 'MSPLIT'){
      $DDA_dir = $taskDir."/DDA";
      $DIA_dir = $taskDir."/DIA";
      $MC_DDA_Results_dir = $gpm_machine_dir . "/DDA_MSGFDB_Results";
      
      if(!is_dir($MC_DDA_Results_dir)){
        mkdir("$MC_DDA_Results_dir", 0775, true);
      }else{
        system("chmod -R 775 $MC_DDA_Results_dir  >/dev/null 2>&1"); 
      }
      
      if(!is_dir($DDA_dir)){
        mkdir("$DDA_dir", 0777, true);
      }else{
        system("chmod -R 775 $DDA_dir  >/dev/null 2>&1"); 
      }
      if(!is_dir($DIA_dir)){
        mkdir("$DIA_dir", 0775, true);
      }else{
        system("chmod -R 775 $DIA_dir >/dev/null 2>&1"); 
      }
      
      if(!is_dir($taskDir."/Results")){
        mkdir($taskDir."/Results", 0777, true);
      }else{
        system("chmod -R 775 ".$taskDir."/Results >/dev/null 2>&1"); 
      }
      
      if(!is_dir($DDA_dir."_complete")){
        mkdir($DDA_dir."_complete", 0777, true);
      }else{
        system("chmod -R 775 ".$DDA_dir."_complete >/dev/null 2>&1"); 
      }
      if(!is_dir($DDA_dir."_Results")){
        mkdir($DDA_dir."_Results", 0777, true);
      }else{
        system("chmod -R 775 ".$DDA_dir."_Results >/dev/null 2>&1"); 
      }
      if(!is_dir($DIA_dir."_Results")){
        mkdir($DIA_dir."_Results", 0777, true);
      }else{
        system("chmod -R 775 ".$DIA_dir."_Results >/dev/null 2>&1"); 
      }
      echo "DDA_dir=$DDA_dir\nDIA_dir=$DIA_dir\n\nLink raw files:\n";
      //print_r($msplit_file_arr);exit;
      
      
      foreach($msplit_file_arr as $file_arr){
        $tmp_WellID = $file_arr[0];
        $tmp_raw_file_path = $file_arr[1];
        $tmp_file_type = $file_arr[2];
        if(preg_match("/[.]gz$/", $tmp_raw_file_path, $matches)){
          $tmp_raw_file_path = unzip_gz($tmp_raw_file_path);
        }
        
        list($prohits_mzML_file_dir, $prohits_mzML_fileName, $prohits_mzML_file_type, $prohits_mzML_fileNameBase) = array_values(pathinfo($tmp_raw_file_path));
        
        if($tmp_file_type == 'MSPLIT'){
          if(isset($file_arr[3]) and $file_arr[3]){
            $linked_raw_file_path = $DIA_dir."_complete/".$tmp_WellID."_".$prohits_mzML_fileName;
          }else{
            $linked_raw_file_path = $DIA_dir."/".$tmp_WellID."_".$prohits_mzML_fileName;
          }
          $MSPLIT_FILE_arr['DIA'][] = array($tmp_WellID, $tmp_raw_file_path);
        }else if($tmp_file_type == 'MSPLIT_DDA'){
          if(isset($file_arr[3]) and $file_arr[3]){
            
            $linked_raw_file_path = $DDA_dir."_complete/".$tmp_WellID."_".$prohits_mzML_fileName;
          }else{
            $linked_raw_file_path = $DDA_dir."/".$tmp_WellID."_".$prohits_mzML_fileName;
          }
          $MSPLIT_FILE_arr['DDA'][] = array($tmp_WellID, $linked_raw_file_path);
        }
        
        echo "$linked_raw_file_path\n";;
       
        if($tmp_file_type == 'MSPLIT_DDA' and $linked_raw_file_path and !_is_file($linked_raw_file_path) ){
          //$com = "ln -s ". escapeshellarg($tmp_raw_file_path) ." ". escapeshellarg($linked_raw_file_path);
          //$com = "cp ". escapeshellarg($tmp_raw_file_path) ." ". escapeshellarg($linked_raw_file_path);
          //exec("$com 2>&1", $output);
          $OK = link_file($tmp_raw_file_path, $linked_raw_file_path);
          
          chmod("$tmp_raw_file_path", 0775); 
          //echo "$com\n";
          
          if(!$OK){
            //link error //try to copy.
            $com = "cp ". escapeshellarg($tmp_raw_file_path) ." ". escapeshellarg($linked_raw_file_path);
            exec("$com 2>&1", $output);
            chmod("$linked_raw_file_path", 0775); 
          }
          
        }
      }
      $linked_raw_file_path = '';
      $prohits_mzML_file_dir = '';
      $prohits_mzML_fileName = '';
      $prohits_mzML_file_type = '';
      $prohits_mzML_fileNameBase = '';
      
    }
    if(defined("LINK_MZML_TO_TASK_DIR") and !LINK_MZML_TO_TASK_DIR){
      $resultFilePath = $taskDir."/".$prohits_mzML_fileNameBase . $resultFile_suffix;
    }else{
      $resultFilePath = $taskDir."/".$WellID."_".$prohits_mzML_fileNameBase . $resultFile_suffix;
    }
    return array('taskDir'=>$taskDir,  
                  'taskID'=>$theTaskID, 
                'STDIN_DIR' => $STDIN_DIR,
                   'tasklog'=>$tasklog, 
                 'statuslog'=>$statuslog, 
               'taskComFile'=>$taskComFile, 
             'paramFilePath'=>$paramFilePath, 
             'raw_file_path'=>$raw_file_path, 
                 'mzDirPath'=>$mzDirPath,
         'link_from_tmp_dir'=>$link_from_tmp_dir, 
      'linked_raw_file_path'=>$linked_raw_file_path,
            'resultFilePath'=>$resultFilePath,
     'prohits_mzML_file_dir'=>$prohits_mzML_file_dir,
     'prohits_mzML_fileName'=>$prohits_mzML_fileName,
    'prohits_mzML_file_type'=>$prohits_mzML_file_type,
 'prohits_mzML_fileNameBase'=>$prohits_mzML_fileNameBase,
              'gpm_cgi_path'=>$gpm_cgi_path,
            'GPM_fasta_path'=> dirname(GPM_CGI_PATH) . "/fasta",
                'fasta_file'=>$gpmDbFile,
                'run_times' =>$run_times,
   'diaumpire_results_dir_path' => $diaumpire_results_dir_path,
  'swath_file_name_base_in_TPP' => $swath_file_name_base_in_TPP,
        'swath_search_dir_name' => $swath_search_dir_name,
              'MSPLIT_FILE_arr' => $MSPLIT_FILE_arr
  );
}
function get_gpm_db_file_path($db_name, $task_SearchEngline=''){
//for the local search only.
  $gpm_tax_file = dirname(GPM_CGI_PATH) . "/tandem/taxonomy.xml";
  $flag = 0;
  if(!$db_name and $task_SearchEngline){
    $engine_arr = explode(";", $task_SearchEngline);
    foreach($engine_arr as $en_str){
      $en_pair = explode("=", $en_str);
      if(count($en_pair) < 2) continue;
      if($en_pair[0] == 'Database'){
        $db_name = $en_pair[1];
      }
    }
    if(!$db_name){
       fatalError( "Error: cannot get fasta file path.");
    }
  }
  $handle = @fopen("$gpm_tax_file", "r");
  if(!$handle){
    fatalError( "Fail to open gpm taxonomy.xml '$gpm_tax_file' file");
  }
  $flag = 0;
  while (($buffer = fgets($handle, 4096)) !== false) {
    if($flag and preg_match('/<file .+ URL="(.+)"/', $buffer, $matches)){
		  return $matches[1];
	  }
    if(preg_match("/<taxon label=\"(.+)\">/", $buffer, $matches)){
      if($matches[1] == $db_name) $flag = 1;
    }
  }
  fclose($handle);
  return 0;
   
}
function run_search_on_local($search_command, $task_infor, $QSUB_WORK_DIR='', $QSUB_OUTPUT_DIR='', $QSUB_JOB_NAME='', $run_in_background = false, $run_same_node = false){
  //only run in backg
  //$run_same_node (parallel commands): run all commands in the same cluster node, if cluster is set.

  $QSUB_task_ID_arr = array();
  $QSUB_bin_path = '';
  $rt_task_ID = 0;
  $node_name = '';
  //$task_infor['taskDir'] = '/mnt/prohits/Prohits_storage/Search_archive/LTQ_DEMO/71_Swath_EIF4aJune7_Biorep2';
  if(is_array($search_command)){
    $all_command = $search_command;
  }else{
    $all_command[] = $search_command;
  }
  if(!$QSUB_WORK_DIR){
    $QSUB_WORK_DIR = $task_infor['gpm_cgi_path'];
  }
  if(!$QSUB_OUTPUT_DIR){
    if($task_infor['STDIN_DIR']){
      $QSUB_OUTPUT_DIR = $task_infor['STDIN_DIR'];   
    }else{
      $QSUB_OUTPUT_DIR = $task_infor['taskDir']; 
    }
  }
  echo "------run search on local--------------------\n";
   
  foreach($all_command as $theCommand){
    if(is_file($theCommand) and !is_executable($theCommand)){
      $theCommand = "sh $theCommand";
    }
    
    $rt_task_ID = 0;
    if(defined("QSUB_BIN_PATH") and is_dir(QSUB_BIN_PATH)){
       $QSUB_bin_path = preg_replace("/\/$/", "",QSUB_BIN_PATH);
       $the_qsub_user = '';
       if(defined("QSUB_USER") and QSUB_USER){
        $the_qsub_user = QSUB_USER;
       }
       //$QSUB_task_ID_file = $QSUB_OUTPUT_DIR."/qsub_task_id.out";
       $tmp_job_name = '';
       if($QSUB_JOB_NAME){
        $tmp_job_name = " -N $QSUB_JOB_NAME";
       }
       $tmp_run_node = '';
       if($run_same_node and $node_name){
         $tmp_run_node = " -l h=$node_name";
       }
       
       //$theCommand = "sleep 30"; //for test only 
       $command = "echo ". escapeshellarg($theCommand)." | $the_qsub_user \"".$QSUB_bin_path."/qsub\"$tmp_run_node$tmp_job_name -wd '$QSUB_WORK_DIR' -o 'localhost:$QSUB_OUTPUT_DIR' -j y 2>&1";
       writeLog("####". $command."\n###start time:". @date("Y-m-d H:i:s"), $task_infor['taskComFile']);
       writeLog($command."\n\n", $task_infor['tasklog']);
       
       
       
       $QSUB_task_ID =  system($command . " 2>&1");
       if(preg_match("/Your job (\d+)/", $QSUB_task_ID, $matches)){
          $rt_task_ID = $matches[1];
       } 
       writeLog($QSUB_task_ID, $task_infor['tasklog']);
       writeLog("## $QSUB_task_ID", $task_infor['taskComFile']);
       if($run_in_background and $rt_task_ID){
         if($QSUB_JOB_NAME){
           $rt_task_ID .= " ".$QSUB_JOB_NAME;
         }else{
           $rt_task_ID .= " STDIN";
         }
       }else if($rt_task_ID){
         if(count($all_command) > 1 and $run_same_node and !$node_name){
           $node_name = get_runing_host($QSUB_bin_path, $rt_task_ID, $task_infor['statuslog']);
           if(!$node_name){
             return;
           }
         }
         $QSUB_task_ID_arr[] = $rt_task_ID;
         writeLog("QSUB ID:$rt_task_ID", $task_infor['statuslog']);
      }
    }else{
      $command = "cd ".$QSUB_WORK_DIR." && ". $theCommand;
      echo $command;
      writeLog("####". $command."\n###start time:". @date("Y-m-d H:i:s"), $task_infor['taskComFile']);
      writeLog($command, $task_infor['tasklog']);
      writeLog("####". $command. "\n##start time:". @date("Y-m-d H:i:s"), $task_infor['tasklog']);
      if($run_in_background){
        $rt_task_ID =  system($command." >> ".escapeshellarg($task_infor['tasklog'])." 2>&1 & echo \$!");
      }else{
        system($command . " 2>&1 | tee -a ".escapeshellarg($task_infor['tasklog']));
      }
    }
  }
  
  if($QSUB_task_ID_arr){
    //only run this part not run_in_background
    foreach($QSUB_task_ID_arr as $QSUB_task_ID){
      echo "qsub task ID: $QSUB_task_ID\n";
      sleep(15);
      check_TORQUE_task($QSUB_OUTPUT_DIR, $QSUB_bin_path, $QSUB_task_ID,  $task_infor['tasklog']);
    }
  }
  return $rt_task_ID;
}

function make_command_file($command_arr, $workDir, $cmd_file){
  $rt = false;
  $to_file = "#!/bin/bash\n\n";
  
  $to_file .= "export PATH=\$PATH:/bin\n";
  $to_file .= "echo PATH is \$PATH\n";

  $to_file .= "echo `hostname`\n";
  $to_file .= "echo \"#. cd to working dir: $workDir\"\n";
  
  $to_file .= 'cd '. escapeshellarg($workDir)."\n";
  
  foreach($command_arr as $theCmd){
    $tmp_str = escapeshellarg("RUN: $theCmd");
    $to_file .= "\necho $tmp_str\n";
    $to_file .= $theCmd ." 2>&1\n";
  }
  $to_file .= "#end";
  $fp = fopen($cmd_file, "w");
  $rt = fwrite($fp, $to_file);
  fclose($fp);
  
  if($rt){
    $rt = chmod($cmd_file, 0775);
  }
  return $rt;
}

function downloadMascotDat($ID, $datFile, $shFileNameBase, $tppMascotCgi, $tppWorkDir) {
   
  $download_from = $tppMascotCgi . "/ProhitsMascotParser.pl";
  $tmpDatFile = $shFileNameBase;
  if($ID){
    $tmpDatFile = $tppWorkDir. "/". $ID."_".$shFileNameBase."_mascot.dat";
  }
  $postData = "theaction=download&file=" . $datFile;
  
  wget_download($download_from, $postData , $tmpDatFile, $tppWorkDir);
  return $tmpDatFile;
}
function checkMascotDB($newDatFile,$GPM_fasta_path, $tppMascotCgi) {
  $handle = @fopen($newDatFile, "r");
  if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
      if (preg_match("/^fastafile=(.+)/i", $buffer, $matches) ) {
        $mascotDbFile= $matches[1];
      }
      if (preg_match("/^release=(.+)/i", $buffer, $matches) ) {
        $mascotDbRelease= $matches[1];
        break;
      }
    }
    fclose($handle);
  }else{
    echo "Fail to open Mascot '$newDatFile' file";
    return 0;
  }
  $gpmDbFile = $GPM_fasta_path . "/". $mascotDbRelease;
   
  if(!_is_file( $gpmDbFile)){
    $postData = "theaction=download&file=" . $mascotDbFile;
    $download_from = $tppMascotCgi . "/ProhitsMascotParser.pl";
    wget_download($download_from, $postData, $gpmDbFile, $task_infor['GPM_fasta_path']  );
  }
  return $gpmDbFile;
}

if(!function_exists('wget_download')){
function wget_download($download_from, $postData='' , $out_file_name='', $out_to_dir='', $wget_log='') {
  $sysCall = 'wget';
  if(!$wget_log){
    if($out_to_dir){
      $sysCall = "cd ". $out_to_dir ."; " . $sysCall;
      $wget_log = $out_to_dir."/wget.log";
    }
  }
  if(system("which wget > /dev/null" )){
    print "wget command doesn't exist in the server:"; exit;
  }
  
  $sysCall .= " --post-data=". escapeshellarg($postData);
  if($out_to_dir){
    $sysCall .= " --directory-prefix=".escapeshellarg($out_to_dir);
  }
  if($out_file_name){
    $sysCall .= " --output-document=". escapeshellarg($out_file_name);
  }else{
    //$sysCall .= " --trust-server-names";
  }
  $sysCall .= " ". $download_from;
   
  print "$sysCall\n";
  if($wget_log){
    $sysCall = $sysCall . " 2>&1 | tee -a \"".$wget_log."\"";
  }
  system($sysCall);
}
}
 
function get_prohits_root_dir($is_shell_path = ''){
  //return with end /
  $Prohits_root_dir = '';
  if($is_shell_path){
    $Prohits_root_dir = preg_replace("/msManager\/.+$/", "",__FILE__);
  }else{
    if(PROHITS_SERVER_IP == SEQUEST_IP){
       $Prohits_root_dir = preg_replace("/msManager\/.+$/", "", $_SERVER['PHP_SELF']);
    }
  }
  return $Prohits_root_dir;
}
function is_in_local_server($search_engine){
  $rt = false;
  if($search_engine == 'GPM' or $search_engine == 'TPP'){
    if( defined("TPP_BIN_PATH") and TPP_BIN_PATH){
      $rt = true;
    }
  }else if($search_engine == 'COMET'){
    if( defined("COMET_BIN_PATH") and COMET_BIN_PATH){
      $rt = true;
    }
  }else if($search_engine == 'MSFragger'){
    if( defined("MSFRAGGER_BIN_PATH") and MSFRAGGER_BIN_PATH){
      if(_is_file(preg_replace("/\/$/", "", MSFRAGGER_BIN_PATH)."/MSFragger.jar")){
        $rt = true;
      }
    }
  }else if($search_engine == 'MSGFPL'){
    if(defined("MSGFPL_BIN_PATH") and MSGFPL_BIN_PATH){
      $rt = true;
    }
  }else if($search_engine == 'MSGFDB'){
    if(defined("MSGFDB_BIN_PATH") and MSGFDB_BIN_PATH){
      $rt = true;
    }
  }else if($search_engine == 'MSUmpire'){
    if(defined("MSUMPIRE_BIN_PATH") and MSUMPIRE_BIN_PATH){
      $rt = true;
    }
  }else if($search_engine == 'MSPLIT'){
    if(defined("MSPLIT_JAR_PATH") and MSPLIT_JAR_PATH){
      $rt = true;
    }
  }else if($search_engine == 'DIAUmpire'){
    if(defined("DIAUMPIRE_BIN_PATH") and DIAUMPIRE_BIN_PATH){
      $rt = true;
    }
  }else if($search_engine == 'SAINT'){
    //if return true the ("SAINT_SERVER_WEB_PATH") not going to be used
    if(defined("SAINT_SERVER_PATH") and SAINT_SERVER_PATH
    and defined('SAINT_SERVER_EXPRESS_PATH') and SAINT_SERVER_EXPRESS_PATH){
      $rt = true;
    }
  }
 
  return $rt;
}
function is_prohits_ip($ip, $prohits_ip = ''){
  global $PROHITS_IP;
  global $PROHITS_NAME;
  
  if(!$prohits_ip){
    $prohits_ip = $PROHITS_IP;
  }
  if($ip == $prohits_ip or $ip == $PROHITS_NAME or $ip == 'localhost'){
    return true;
  }else{
    return false;
  }
}

function print_task_parameters($task_record){
  global $managerDB;
  global $prohitsDB;
  global $default_param_arr;
  global $show_converter;
  global $frm_setID;
  global $selected_SearchEngine;
  global $SCRIPT_NAME;
  global $task_ID;
  global $para_version_arr;
  global $para_file_name;
  
  $fp = fopen($para_file_name, 'w');
  if($SCRIPT_NAME == 'ms_pop_search_engine_parameters.php'){
    $bgcolor_tr = '';
    $bgcolor_td = '';
  }else{
    $bgcolor_tr = "#8f8765";
    $bgcolor_td = "#dadada";
  }
  $search_Engine_arr = array();
  
  $set_arr_tmp = explode("\n", $task_record['Parameters']);
  $set_arr = array();
  $tmp_fixed_mod_str = '';
  $tmp_variable_mod_str = '';
  foreach($set_arr_tmp as $set_arr_val){
    if(preg_match("/^frm_fixed_mod_str===(.*)/", $set_arr_val,$matches)){
      $tmp_fixed_mod_str = str_replace(";;", ":", $matches[1]);
    }elseif(preg_match("/^frm_variable_mod_str===(.*)/", $set_arr_val,$matches)){
      $tmp_variable_mod_str = str_replace(";;", ":", $matches[1]);
    }else{
      $set_arr[] = $set_arr_val;
    }
  }
  $mod_str = "Modifications===Fixed Modifications=$tmp_fixed_mod_str;;Variable Modifications=$tmp_variable_mod_str";
  
  $set_arr[] = $mod_str;
      
  if(isset($task_record['DIAUmpire_parameters']) && $task_record['DIAUmpire_parameters']){
    $task_record['DIAUmpire_parameters'] = str_replace(":", "=", $task_record['DIAUmpire_parameters']);
    $set_arr[] = 'DIAUmpire==='.$task_record['DIAUmpire_parameters'];  
  }
  if(isset($task_record['LCQfilter']) && $task_record['LCQfilter'] && $show_converter){
    $set_arr[] = 'Converter==='.$task_record['LCQfilter'];
  }
       
  for($i = 0; $i < count($set_arr); $i++){
    $para_arr = array();
    $engine_arr = explode('===', $set_arr[$i]);
    if(count($engine_arr)<2) continue;
    if(strtoupper($engine_arr[0]) == 'SEQUEST' || strtoupper($engine_arr[0]) == 'SEARCHALL' || strtoupper($engine_arr[0]) == 'COMET' || strtoupper($engine_arr[0]) == 'MSGFPL' || strtoupper($engine_arr[0]) == 'MSGFDB' || strtoupper($engine_arr[0]) == 'MSPLIT'){
      if($engine_arr[0] == 'MSPLIT'){
        $engine_arr[1] = str_replace("para_", "", $engine_arr[1]);
        $engine_arr[1] = str_replace(":", "=", $engine_arr[1]);
        $para_arr = explode(";", $engine_arr[1]);
      }else{
        $para_arr = explode(";;", $engine_arr[1]);
      }  
    }else{
      //$para_arr = explode(";", $engine_arr[1]);
      if($frm_setID){
        $para_arr = preg_split("/;;/", $engine_arr[1]);
      }else{
        $para_arr = explode(";", $engine_arr[1]);
      }  
    }
    
//echo $engine_arr[0]."====================================================================<br>";
    $Parameters_ARR = array();
    $IT_MODS='';
    $MODS='';
    $para_str = '';
    $para_file_str = '';
    $seq_started = 0;
    $seq_enzyme_started = 0;
    $seq_enzyme_arr = array();
    $seq_fixed_mod = '';
   
    $engine_up = strtoupper($engine_arr[0]);

    if($engine_up == 'GPM'){
      $engine_up = "XTandem";
    }
    $para_file_str .= $engine_up."\n";
    if($engine_up != 'MODIFICATIONS' && $engine_up != 'MSGFDB'){
      if(isset($para_version_arr[strtoupper($engine_arr[0])])){
        $para_file_str .= 'Version: '.$para_version_arr[strtoupper($engine_arr[0])]."\n";
      }  
    }  
    foreach($para_arr as $pare){
      if(trim($pare) == '[SEQUEST_ENZYME_INFO]'){
        $seq_enzyme_started = 1;
      }
      if(trim($pare) == '[SEQUEST]'){
        $seq_started = 1;
      }
      if($seq_enzyme_started){
        $tmp_arr = explode('.',$pare);
        if(count($tmp_arr)>1 and is_numeric($tmp_arr[0])) {
          $seq_enzyme_arr[trim($tmp_arr[0])] = $tmp_arr[1];
        }
      }else{
        $tmp_arr = explode('=',$pare);
        if(count($tmp_arr)>1) {
          if($tmp_arr[0] == 'MODS' or $tmp_arr[0] == 'IT_MODS'){
            // $MODS = "Oxidation(M);Phopho(ST);"
            $$tmp_arr[0] .= $tmp_arr[1] . ":";
          }else{
            $tmp_name = trim($tmp_arr[0]);
            if($seq_started){
              $tmp_arr[1] = trim(preg_replace("/;.+$/",'',  $tmp_arr[1]));
              if(preg_match("/^add_/", trim($tmp_arr[0])) and floatval($tmp_arr[1])>0){
                $seq_fixed_mod .= "\n$tmp_name=". $tmp_arr[1];
              }
            }
            $$tmp_name = $tmp_arr[1];
            $Parameters_ARR[$tmp_name] = $$tmp_name; 
          }
        }elseif($engine_arr[0] == 'Converter'){
          $para_str .= $tmp_arr[0];
          $para_file_str .= $tmp_arr[0];
        }
      }
    }
    
    $search_Engine_arr[] = strtoupper($engine_arr[0]);
    if(strtoupper($engine_arr[0]) == 'MASCOT'){
      $para_str .= "Fixed Mod: <font color='#FF0000'>$MODS </font><br>
                  Variable Mod: <font color='#FF0000'> $IT_MODS</font><br>
                  ";
      $para_file_str .= "Fixed Mod: $MODS \nVariable Mod: $IT_MODS\n";            
    }
    if(strtoupper($engine_arr[0]) == 'GPM'){
      $unit_f = $spectrum__fragment_monoisotopic_mass_error_units;
      if(strlen($unit_f)>3){
        $unit_f = substr($unit_f, 0, 2);
      }
      $unit_p = $spectrum__parent_monoisotopic_mass_error_units;
      if(strlen($unit_p)>3){
        $unit_p = substr($unit_p, 0, 2);
      }
    }
    if(strtoupper($engine_arr[0]) == 'SEQUEST'){
      $tmp_db = '';
      if(isset($database_name)){
        $tmp_db = $database_name;
      }else if(isset($first_database_name)){
        $tmp_db = $first_database_name;
      }
      if(isset($peptide_mass_units)){
        if($peptide_mass_units == '1'){
          $peptide_mass_units = 'amu';
        }else if($peptide_mass_units == '2'){
          $peptide_mass_units = 'ppm';
        }else{
          $peptide_mass_units = 'amu';
        }
      }else{
        $peptide_mass_units = 'amu';
      }
      if(!isset($enzyme_info) and isset($enzyme_number)){
        $enzyme_info = $seq_enzyme_arr[trim($enzyme_number)];
      }
    }
//-----------------------------------------------------------------------------------------------------------
    if(strtoupper($engine_arr[0]) == 'SEARCHALL'){
      //============================================================================================
      $search_Engine = "<b>".$engine_arr[0]."</b>:<br>";
      $tmp_arr1 = explode(";",$task_record['SearchEngines']);
      $sub_engine = str_replace(":", "<br>", $tmp_arr1[1]);
      $search_Engine .= $sub_engine;              
      $enzyme_info = '';
              
      $para_str = '';
      $multiple_select_str = '';
      foreach($para_arr as $para_val){
        if(!trim($para_val)) continue;
        $tmp_arr = explode("=", $para_val);
        if(strstr($para_val, 'multiple_select_str')){
          $multiple_select_str = $tmp_arr[1];
        }else{
          if($tmp_arr[0] == 'search_enzyme_number'){
            $tmp_arr[1] = $default_param_arr['comet_enzyme_info']['name'][$tmp_arr[1]];
          }elseif($tmp_arr[0] == 'mass_type_parent'){
            $tmp_arr[1] = ($tmp_arr[1])?'Monoisotopic':'Average';
          }elseif($tmp_arr[0] == 'mass_type_fragment'){
            $tmp_arr[1] = ($tmp_arr[1])?'Monoisotopic':'Average';
          }elseif($tmp_arr[0] == 'peptide_mass_units'){
            $tmp_arr[1] = ($tmp_arr[1])?'ppm':'amu';
          }elseif($tmp_arr[0] == 'num_enzyme_termini'){
            $tmp_arr[1] = ($tmp_arr[1])?'semi-digested':'fully digested';
          }elseif($tmp_arr[0] == 'decoy_search'){
            $tmp_arr[1] = ($tmp_arr[1])?'concatenated search':'no';
          }elseif($tmp_arr[0] == 'use_NL_ions'){
            $tmp_arr[1] = ($tmp_arr[1])?'yes':'no';
          }elseif($tmp_arr[0] == 'isotope_error'){
            $tmp_arr[1] = ($tmp_arr[1])?'no':'off';
          }elseif($tmp_arr[0] == 'msgfpl_FragmentMethodID'){
            if(!$tmp_arr[1]){
              $tmp_arr[1] = 'in the spectrum or CID if no info';
            }elseif($tmp_arr[1] == 1){
              $tmp_arr[1] = 'CID';
            }elseif($tmp_arr[1] == 2){
              $tmp_arr[1] = 'ETD';
            }elseif($tmp_arr[1] == 3){
              $tmp_arr[1] = 'HCD';
            }
          }elseif($tmp_arr[0] == 'msgfpl_InstrumentID'){ 
            if(!$tmp_arr[1]){
              $tmp_arr[1] = 'Low-res LCQ/LTQ';
            }elseif($tmp_arr[1] == 1){
              $tmp_arr[1] = 'High-res LTQ';
            }elseif($tmp_arr[1] == 2){
              $tmp_arr[1] = 'TOF';
            }elseif($tmp_arr[1] == 3){
              $tmp_arr[1] = 'Q-Exactive';
            }
          }
          $tmp_arr[0] = str_replace("_", " ", $tmp_arr[0]);
          $para_str .= ucfirst($tmp_arr[0]).": <font color=red>".$tmp_arr[1]."</font><br>";
        }
      }      
                      
      $tmp_M_arr = explode('&&',$multiple_select_str);
      $variable_MODS = '';
      $fixed_MODS = '';
      $frm_refinement_MODS = '';
      foreach($tmp_M_arr as $tmp_M_val){
        $tmp_M_arr2 = explode('|',$tmp_M_val);
        if(!$tmp_M_arr2[1]) continue;
        $MODS_C = str_replace(":::", "&nbsp;", $tmp_M_arr2[1]);
        $MODS_C = "<font color='#FF0000'>$MODS_C</font><br>";
        if($tmp_M_arr2[0] == 'frm_variable_MODS'){
          $variable_MODS = "Variable MOD:&nbsp;$MODS_C";
        }elseif($tmp_M_arr2[0] == 'frm_fixed_MODS'){
          $fixed_MODS = "Fixed MOD:&nbsp;$MODS_C";
        }elseif($tmp_M_arr2[0] == 'frm_refinement_MODS'){
          $frm_refinement_MODS = "Refinement MOD:&nbsp;$MODS_C";
        }
      }
      if($fixed_MODS)  $para_str .= $fixed_MODS;          
      if($variable_MODS)  $para_str .= $variable_MODS;            
      if($frm_refinement_MODS)  $para_str .= $frm_refinement_MODS;
    }
//-----------------------------------------------------------------------------------------------------------         
    
    if(strtoupper($engine_arr[0]) == 'COMET'){    
        if($search_enzyme_number){
          $Enzyme = $default_param_arr['comet_enzyme_info']['name'][$search_enzyme_number];
        }else{
          $Enzyme = '';
        }
        
        $varable_Mod = '';
        $fixed_Mod = '';
        $tmp_Mod_arr = explode('&&',$multiple_select_str);
        foreach($tmp_Mod_arr as $tmp_Mod_val){
          $tmp_Mod_arr_2 = explode('|',$tmp_Mod_val);
          if($tmp_Mod_arr_2[0] == 'frm_variable_MODS'){
            $varable_Mod = str_replace("...", "<br>", $tmp_Mod_arr_2[1]);
          }elseif($tmp_Mod_arr_2[0] == 'frm_fixed_MODS'){
            $fixed_Mod = str_replace(":::", "<br>", $tmp_Mod_arr_2[1]);
          }
        }
    }
    
    if(strtoupper($engine_arr[0]) == 'MSGFPL'){    
        if($enzyme_number){
          $Enzyme = $default_param_arr['comet_enzyme_info']['name'][$enzyme_number];
        }else{
          $Enzyme = '';
        }
        
        $varable_Mod = '';
        $fixed_Mod = '';
        $tmp_Mod_arr = explode('&&',$multiple_select_str);
        foreach($tmp_Mod_arr as $tmp_Mod_val){
          $tmp_Mod_arr_2 = explode('|',$tmp_Mod_val);
          if($tmp_Mod_arr_2[0] == 'frm_variable_MODS'){
            $varable_Mod = str_replace("...", "<br>", $tmp_Mod_arr_2[1]);
          }elseif($tmp_Mod_arr_2[0] == 'frm_fixed_MODS'){
            $fixed_Mod = str_replace(":::", "<br>", $tmp_Mod_arr_2[1]);
          }
        }
    }
    if(strtoupper($engine_arr[0]) != 'MASCOT'){
      //$para_str = '';
    }  
    if(strtoupper($engine_arr[0]) != 'SEARCHALL'){
      foreach($Parameters_ARR as $tmp_key => $tmp_val){
        $para_str .= format_output($tmp_key,$tmp_val);
        $para_file_str .= format_file_output($tmp_key,$tmp_val,$engine_arr[0]);
      }
      fwrite($fp, $para_file_str."\n\n");
    }

    $search_Engine = strtoupper($engine_arr[0]);
    $img_file_name = str_replace("_", "", $search_Engine);
    $img_file_name = strtolower($img_file_name);
    
    if($img_file_name == 'searchall'){
      $img_file_name = 'modifications';
    }
    
    $search_Engine_label = "<b>".(($engine_arr[0]=='GPM')?"XTandem":$engine_arr[0])."</b>";
    if($engine_arr[0] == 'Converter'){
      $img_file_name = 'proteowizard';
    }  
?>
    <tr bgcolor="<?php echo $bgcolor_tr?>" height=25>
      <td colspan=4 valign=center >
<?php if($SCRIPT_NAME == 'ms_pop_search_engine_parameters.php'){?>
        <div class="title">
          <div style="float: left;padding: 0px 0px 0px 2px;border:red 0px solid; width:100">
            <img src='./images/<?php echo $img_file_name?>.<?php echo (($img_file_name=='msgfdb' or $img_file_name == 'proteowizard')?'png':'gif')?>' border=0>
          </div>
    <div style="float: left;padding: 0px 0px 0px 20px;border:red 0px solid;">
      <b><font color='red' face='helvetica,arial,futura' size='3'><?php echo $search_Engine_label?></font></b><br>
    </div>
          <div style="float: right;padding: 0px 0px 0px 0px;font-family: Georgia, Serif; border:red 0px solid; min-width: 200px;">
            <span style="float: right;padding: 10px 5px 0px 0px;font-family: Georgia, Serif; border:red 0px solid;">
            [<a id="<?php echo $search_Engine?>_a" href="javascript: toggle_detail('<?php echo $search_Engine?>')" title='Mascot parameters'><?php echo (($selected_SearchEngine == 'MASCOT')?'-':'+')?></a>]
            </span>
          </div>
        </div>
<?php }else{?>
        <div style="float: left;padding: 0px 0px 0px 5px;color: white;">
          <?php echo $search_Engine_label;?>
        </div>
        <div style="float: right;padding: 0px 5px 0px 0px;font-family: Georgia, Serif;">
        [<a id="<?php echo $search_Engine;?>_a" href="javascript: toggle_detail('<?php echo $search_Engine;?>')" title='<?php echo $search_Engine;?> detail'><?php echo ((strtoupper($selected_SearchEngine)==strtoupper($search_Engine))?'-':'+')?></a>]
        </div>
<?php }?>
      </td>
    </tr>
    <tr>
      <td colspan=4 bgcolor="<?php echo $bgcolor_td?>">
<?php if($SCRIPT_NAME == 'ms_pop_search_engine_parameters.php'){?>
        <DIV id="<?php echo $search_Engine;?>" class="contents" style="display:<?php echo (($selected_SearchEngine == '<?php echo $search_Engine;?>')?'block':'none')?>">
<?php }else{?> 
        <div id="<?php echo $search_Engine;?>"style="display:<?php echo ((strtoupper($selected_SearchEngine)==strtoupper($search_Engine))?'block':'none')?>;padding: 5px 0px 5px 20px;border:#708090 1px solid;">
<?php }?>          
          <font color="#000000"><?php echo $para_str;?></font>
        <div>
        
      </td>
    </tr>
    <?php 
  }
?>
  <script language="javascript">
  function toggle_all(all_search_engine){
    var all_obj = document.getElementById(all_search_engine);
    var all_obj_inner_str = all_obj.innerHTML;
    if(all_obj_inner_str == '+'){ 
    <?php foreach($search_Engine_arr as $val){?> 
        var selected_obj = document.getElementById('<?php echo $val?>');
        var selected_a_id = '<?php echo $val?>' + '_a';
        var selected_a_obj = document.getElementById(selected_a_id);
        selected_obj.style.display = "block";
        selected_a_obj.innerHTML = '-';
    <?php }?>
      all_obj.innerHTML = '-'; 
    }else{
     <?php foreach($search_Engine_arr as $val){?> 
        var selected_obj = document.getElementById('<?php echo $val?>');
        var selected_a_id = '<?php echo $val?>' + '_a';
        var selected_a_obj = document.getElementById(selected_a_id);
        selected_obj.style.display = "none";
        selected_a_obj.innerHTML = '+';
    <?php }?>
      all_obj.innerHTML = '+'; 
    }
  }
  </script>
<?php 
  fclose($fp);
  return $search_Engine_arr;
}

function format_output($key,$val){
  return ucfirst($key).": ".$val."<br>";
}

function format_file_output($key,$val,$engine){
  if($engine == 'GPM'){
    $key = str_replace("__", ", ", $key);
    $key = str_replace("_", " ", $key); 
  }
  return ucfirst($key).": ".$val."<br>";
}
function makeTip_str($str){
  
  $patterns = array("/\"/", "/\'/", "/>/", "/</", "/\r/", "/\n/");
  $replace = array("&quot;", "&rsquo;", "&gt;", "&lt;", "", "<br>");
  $str =  preg_replace($patterns, $replace, $str);
  return $str;
}
function pop_tppTask_parameters_div($tppRow){
  $tpp_para_str = "TPP ".$tppRow['TaskName']."(".$tppRow['ID'].")"." Parameters;;";
  $tmp_para_arr = explode("\n",$tppRow['Parameters']);
  foreach($tmp_para_arr as $tmp_para_val){
    if(!$tmp_para_val) continue;
    $tmp_arr = preg_split("/[:]/", $tmp_para_val);
    if(isset($tmp_arr[1]) and trim($tmp_arr[1])){
      $tmp_arr[0] = str_replace("frm_", "", $tmp_arr[0]);
      $tmp_arr[1] = preg_replace(array("/\"/", "/\'/", "/>/", "/</"), array("&quot;", "&rsquo;", "&gt;", "&lt;"), $tmp_arr[1]);
      
      $tpp_para_str .= format_output($tmp_arr[0],$tmp_arr[1]);
    }
  }
  return $tpp_para_str;
}
//used in auto_run_shell.php and MSPLIT_status.php
function get_jar_command($use_memery_size = '', $jar='-jar'){
  if(!$use_memery_size){
    $use_memery_size = '3G';
  }else{
    exec("cat /proc/meminfo", $outputs);
    $total_mem = round(intval(preg_replace("/\D+/", "", $outputs[0]))/1024/1024);
    if($total_mem < intval($use_memery_size)){
      $use_memery_size = $total_mem."G";
    }
    if(intval($use_memery_size) < 5) {
      $use_memery_size = intval($use_memery_size) + 1;
      $use_memery_size .="G";
    }
  }
  if(!strpos($use_memery_size, "G")){
    $use_memery_size = $use_memery_size .="G";
  }
  $JAR_Command = "java -Xmx".$use_memery_size ." $jar ";
  return $JAR_Command;
}
//-----------------------------------------------------------------------
function check_geneMapping_file($fasta_file_basename='', $db_basename=''){
  
  $mapping_parent = dirname(PROTEOWIZARD_BIN_PATH);
  $mapping_dir = $mapping_parent."/geneMapping";
  if(!_is_dir($mapping_dir)){
    _mkdir_path($mapping_dir);
  }
  $mapping_log_file = $mapping_dir."/geneMapping.log";
  $mapping_log_arr = array();
  if(_is_file($mapping_log_file)){
    $mapping_log_arr_tmp = file($mapping_log_file);
    foreach($mapping_log_arr_tmp as $mapping_log_val_tmp){
      list($name,$date) = explode("\t", trim($mapping_log_val_tmp));
      $mapping_log_arr[$name] = $date;
    }
  }
  
  $fasta_location_file = $mapping_parent."/thegpm/tandem/taxonomy.xml";
  $handle = fopen($fasta_location_file, "r");
  if($handle){
    $db_name = '';
    $fasta_file_name = '';
    $fund_flag = 0;
    while(($buffer = fgets($handle, 4096)) !== false){
      if(preg_match('/^\<taxon label=\"(.+?)\"/', trim($buffer), $matches)){
        $db_name_m = $matches[1];
        if($db_basename == $db_name_m){
          $fund_flag = 1;
        }
      }elseif(preg_match('/^\<file format=\"peptide\"\s*URL=\"(.+?)\"/', trim($buffer), $matches)){
        $fasta_file_name = $matches[1];
        if($fund_flag) break;
        if($fasta_file_basename && strstr($fasta_file_name, $fasta_file_basename)) break;
      }
    }
  }else{
    echo "no $fasta_location_file";
    exit;
  } 
  
  fclose($handle);  
  $proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);
  
  $modified_date = @date("Y-m-d", filemtime($fasta_file_name));
  $fasta_file_basename = basename($fasta_file_name);
  if(array_key_exists($fasta_file_basename, $mapping_log_arr) && $modified_date <= $mapping_log_arr[$fasta_file_basename]){
    echo "<br>The fasta file named $fasta_file_basename has not updeted.<br><br>";
    return 0;
  }
  echo "<br><br>Process file: $fasta_file_basename======================================<br><br>";  
  if(!$handle_read = popen("cat $fasta_file_name", "r")) {
     echo ("file $fasta_file_name is missing");
  }
  $mapping_file_basename = preg_replace('/\..+$/', '.map', $fasta_file_basename);

  $mapping_file_name = $mapping_dir.'/'.$mapping_file_basename;
  $handle_write = fopen($mapping_file_name, "w");
  $line_num = 0;
  while($data = fgets($handle_read)){
    $data = trim($data);
    $line_num++; 
    if($line_num%900 === 0){
      echo '.';
      if($line_num%4000 === 0)  echo "$line_num\n";
      flush();
      ob_flush();
    }
    if(preg_match('/^>DECOY.+/', $data, $matches)){
      continue;
    }elseif(preg_match('/^>(.+)/', $data, $matches)){    
      $has_GeneID = 0;
      $GeneID = 0;
      $tmp_arr = explode(" ",  $matches[1]);
      $tmp_arr2 = explode("|gn|", $tmp_arr[0]);
      if(count($tmp_arr2) == 2){
        $has_GeneID = 1;
        if(preg_match('/:(.+)\|$/', trim($tmp_arr2[1]), $matches)){
          $GeneID = $matches[1];
        }
      }
      $protein_str = $tmp_arr2[0];      
      $protein_info_arr = explode("|", $protein_str);
      $pos = strpos($protein_info_arr[0], ":");
      $protein_id_arr = array();
      $type = '';
      if($pos === false){
        for($i=0; $i<count($protein_info_arr); $i++){
          if(strlen($protein_info_arr[$i]) > 3){
            $protein_id_arr[] = $protein_info_arr[$i];
            //break;
          }
        } 
      }else{    
        foreach($protein_info_arr as $protein_info_val){
          $protein_info_tmp = explode(":", $protein_info_val);
          $protein_info_tmp2 = explode(";", $protein_info_tmp[1]);
          foreach($protein_info_tmp2 as $protein_info_tmp2_v){
            if($protein_info_tmp2_v){
              $protein_id_arr[] = $protein_info_tmp2_v;
            }
          }  
        }
        $type = 'IPI';
      }
      
      if($has_GeneID){
        if($GeneID){
          $proteinID = $protein_id_arr[0];
          $line = $proteinID."\t".$GeneID."\n";
          fwrite($handle_write,$line);
        }
      }else{    
        foreach($protein_id_arr as $protein_id_val){
          $gene_info = get_protein_GeneID_in_local($protein_id_val, $type, $proteinDB);
          $GeneID = $gene_info['GeneID'];
          if($GeneID){
            $line = $protein_id_val."\t".$GeneID."\n";
            fwrite($handle_write,$line);
            //$gene_name = get_Gene_Name($gene_id, $proteinDB);
            break;
          }
        }
        if(!$GeneID && 0){
          foreach($protein_id_arr as $protein_id_val){
            $GeneID = get_protein_GeneID($protein_id_val, $type, $proteinDB);
            if($GeneID){
              $line = $protein_id_val."\t".$GeneID."\n";
              fwrite($handle_write,$line);
              //$gene_name = get_Gene_Name($gene_id, $proteinDB);
              break;
            }
          }
        }
      }
      //if($line_num >50) break;
    }
  }
  if(isset($handle_read)){
    pclose($handle_read);
  } 
  if(isset($handle_write)){
    fclose($handle_write);
  }
  $mapping_date = @date("Y-m-d", filemtime($mapping_file_name));
  $mapping_log_arr[$fasta_file_basename] = $mapping_date;
  
  $handle_log = fopen($mapping_log_file, "w");
  foreach($mapping_log_arr as $mapping_log_key => $mapping_log_val){
    $log_line = $mapping_log_key."\t".$mapping_log_val."\n";
    fwrite($handle_log,$log_line);
  }
  fclose($handle_log);
}
function is_dir_empty($dir) {
  if (!is_readable($dir)) return NULL; 
  return (count(scandir($dir)) == 2);
}

function link_file($target_path, $linker_path){
  /*full path file link or full path of folder all file links. 
  it will try to make relative link
  */ 
  if(is_dir($target_path)){
    if(is_dir_empty($target_path)) {
      return false;
    }else{
      $target_path = rtrim($target_path, '\/') . '/';
      $linker_path = rtrim($linker_path, '\/') . '/';
      $linker_dir = $linker_path;
    } 
  }else if(!is_file($target_path)){
    return false;
  }else{
    $linker_dir = dirname($linker_path);
  }
  $linker = explode('/', $linker_path);
  $target = explode('/', $target_path);
  $relPath  = $target;
  foreach($linker as $depth => $dir) {
    // find first non-matching dir
    if($dir === $target[$depth]) {
        // ignore this directory
       array_shift($relPath);
    } else {
      if($depth == 1){
        $relPath[0] = '/' . $relPath[0];
        break;
      }
      // get number of remaining dirs to $linker
      $remaining = count($linker) - $depth;
      if($remaining > 1) {
        // add traversals up to first matching dir
        $padLength = (count($relPath) + $remaining - 1) * -1;
        $relPath = array_pad($relPath, $padLength, '..');
        break;
      }else{
        $relPath[0] = './' . $relPath[0];
      }
    }
  }
  $new_target_path =  implode('/', $relPath);
  if(!is_dir($linker_dir)){
    mkdir($linker_dir, 0775, true);
  }
  if(is_dir($target_path)){
    $cmd = "cd ".escapeshellarg($linker_dir)."; ln -sf ".escapeshellarg($new_target_path)."* ".escapeshellarg($linker_path);
    system($cmd);
    $rt =  !is_dir_empty($linker_path);
  }else{
    $cmd = "cd ".escapeshellarg($linker_dir)."; ln -sf ".escapeshellarg($new_target_path)." ".escapeshellarg($linker_path);
    system($cmd);
    $rt =  is_file($linker_path);
  }
  if(function_exists('writeLog')){
    writeLog($cmd);
  }else{
    echo "$cmd\n"; 
  }
  return $rt;
}
function remove_broken_links($dir, $max_level='', $curr_level=-1){
  //usage: remove_broken_links($dir, $max_level);
  //$dir = remove the broken likes in the folder
  //$max_level = process sub folder levels. default = all subfolders.
  $curr_level++;
  $dir = preg_replace("/\/$/", '', $dir);
  if($max_level !=='' and $max_level < $curr_level ) return;
  $dir_arr = @scandir($dir);
  if(!$dir_arr) return;
  foreach($dir_arr as $entry) { 
    if(strpos($entry, '.')===0) continue;
    $path = $dir . DIRECTORY_SEPARATOR . $entry;
    if (is_link($path) && !file_exists($path)) {
      echo "deleted: $path\n";
      flush();
      @unlink($path);
    }else if(is_dir($path)){
      echo "go: $path\n";flush();
      remove_broken_links($path, $max_level, $curr_level);
    }
  }
}
function put_default_first($set_arr){
  $catched_key = '';
  foreach($set_arr as $key => $val){
    //if(!is_array($val)) return $set_arr;
    if($val['Default']){
      $catched_key = $key;
      break;
    }
  }
  if($catched_key){
    $tmp_part_arr = array_splice($set_arr, $catched_key, 1);
    array_unshift($set_arr, $tmp_part_arr[0]);
  }
  return $set_arr;
}
function get_msplit_lib_file_path($dir=''){
  if(!$dir){
    global $Prohits_path;
    if(defined('MSPLIT_JAR_PATH') and MSPLIT_JAR_PATH){
      $dir = MSPLIT_JAR_PATH;
    }else{
      $dir = $Prohits_path . "/EXT/MSPLIT-DIA";
    }
  }
  $conf_file = $dir."/msplit_lib.conf";
  $new_dir = dirname($dir);
  if(is_file($conf_file)){
    return $conf_file;
  }else if(is_dir($new_dir)){
    return get_msplit_lib_file_path($new_dir);
  }else{
    return '';
  }
}




function get_used_para_names($USER_ID, $tableSearchTasks, $tableTppTasks){
  $rt = array('SearchEngines'=>array(),'Converter'=>array(), 'Database'=>array(), 'DIAUmpire'=>array(),'MSPLIT'=>array(),'MSPLIT_LIB'=>array(), 'TPP'=>array());
  global $managerDB;
  $SQL = "SELECT SearchEngines FROM $tableSearchTasks where UserID=$USER_ID order by StartTime desc";
  $rds = $managerDB->fetchAll($SQL);
  foreach($rds as $rd){
    $engine_arr = explode(";",$rd['SearchEngines']);  
    foreach($engine_arr as $tmp_en){
      $tmp_set = explode("=", $tmp_en);
      if($tmp_set[0] == "Mascot" or $tmp_set[0] == "SEQUEST" or 
         $tmp_set[0] == "GPM" or $tmp_set[0] == "COMET" or 
         $tmp_set[0] == "MSFragger" or $tmp_set[0] == "MSGFPL" or
         $tmp_set[0] == "MSGFDB"
      ){
        if(count($tmp_set)> 1){
          $rt['SearchEngines'][] = $tmp_set[1];
        }
      }else if($tmp_set[0] == "Converter" and count($tmp_set)> 1){
        $rt['Converter'][] = $tmp_set[1];
      }else if($tmp_set[0] == "Database" and count($tmp_set)> 1){
        $rt['Database'][] = $tmp_set[1];
      }else if($tmp_set[0] == "DIAUmpire" and count($tmp_set)> 1){
        $rt['DIAUmpire'][] = $tmp_set[1];
      }else if($tmp_set[0] == "MSPLIT" and count($tmp_set)> 1){
        $rt['MSPLIT'][] = $tmp_set[1];
      }else if($tmp_set[0] == "MSPLIT_LIB" and count($tmp_set)> 1){
        $rt['MSPLIT_LIB'][] = $tmp_set[1];
      }
    }
  }
  $SQL = "SELECT ParamSetName FROM $tableTppTasks where UserID=$USER_ID order by StartTime desc";
  $rds = $managerDB->fetchAll($SQL);
  foreach($rds as $rd){
    $rt['TPP'][] = $rd['ParamSetName'];
  }
  $rt['SearchEngines'] = array_unique($rt['SearchEngines']);
  $rt['Converter'] = array_unique($rt['Converter']);
  $rt['Database'] = array_unique($rt['Database']);
  $rt['DIAUmpire'] = array_unique($rt['DIAUmpire']);
  $rt['MSPLIT'] = array_unique($rt['MSPLIT']);
  $rt['MSPLIT_LIB'] = array_unique($rt['MSPLIT_LIB']);
  $rt['TPP'] = array_unique($rt['TPP']);
  return $rt;
}
?>
