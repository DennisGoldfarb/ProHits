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

error_reporting(E_ALL);
set_time_limit(0);
ini_set("memory_limit","500M");
ini_set("default_socket_timeout", "3600");

$enable_spleep = false;

chdir(dirname(__FILE__));
$configFile = "../../config/conf.inc.php";
$logfile = '../../logs/raw_back.log';
$only_run_on_shell = false;
$linked_file_arr = array();

require($configFile);
include('./shell_functions.inc.php');
include('../is_dir_file.inc.php');


if(count($_SERVER['argv']) > 1){
  if(isset($_SERVER['argv'][1])){
    $sleep_sec = $_SERVER['argv'][1];
    if(!is_numeric($sleep_sec)){
      echo "Usage: php raw_backup_shell.php spleepSeconds tableName\n";exit;
    }
    if($enable_spleep){
      sleep($sleep_sec);
    }
  }
  if(isset($_SERVER['argv'][2])){
    $tableName = $_SERVER['argv'][2];
  }
}
if(array_key_exists('REQUEST_METHOD', $_SERVER) and $only_run_on_shell){
  echo "this script cannot be run on web browser!"; exit;
}
$host = HOSTNAME;
$user = USERNAME;
$pswd = DBPASSWORD;
$msManager_link  = mysqli_connect($host, $user, $pswd, MANAGER_DB) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));
mysqli_query($msManager_link, "SET SESSION sql_mode = ''");

$HitDB_links = array();
foreach($HITS_DB as $key => $DBname){
  $HitDB_links[$key] = mysqli_connect($host, $user, $pswd, $DBname) or die("Unable to connect to mysql..." . mysqli_error($HitDB_links[$key]));
  mysqli_query($HitDB_links[$key], "SET SESSION sql_mode = ''");
}

if(isset($_SERVER['OS'])){
  $slash = "\\";
}else{
  $slash = "/";
}
 
$tableNameArr = get_db_tableNames($msManager_link, MANAGER_DB); //get a array of backup DB table names.
$proDBarr = get_projectDB();

if(!isset($BACKUP_SOURCE_FOLDERS)){
  $msg = "Array \$BACKUP_SOURCE_FOLDERS not exist in conf file.";
  fatalError($msg,  __LINE__, $logfile);
}
if(isset($tableName) and !in_array($tableName,$tableNameArr)){
  echo "$tableName doesn't exist.\nUsage: php raw_backup_shell.php spleepSeconds tableName\n";exit;
}


if(isset($tableName)){
  if(!isset($BACKUP_SOURCE_FOLDERS[$tableName])){
    $msg = "Warning: Please check conf file the $tableName is not set.";
    writeLog($msg, $logfile);
    exit;
  }
}
foreach($BACKUP_SOURCE_FOLDERS as $key => $value){
  global $logfile;
  if(isset($tableName) and $key != $tableName) continue;
  
  
	if($value['SOURCE']){
	  $msg = "process $key " . @date("Y-m-j G:i:s");
  	writeLog($msg, $logfile);
	}
  if(preg_match('/\W/', $key)){
    $msg = "Just 'A-Za-z0-9_' alowed for the table name -----$tableName";
    writeLog($msg, $logfile);
    continue;
  }
  
  if(!isset($value['SOURCE'])){
    $msg = "Warning: Please check conf file for $key in BACKUP_SOURCE_FOLDERS. Prohis couldn't find 'SOURCE' index";
    writeLog($msg, $logfile);
    continue;
  }else if($value['SOURCE']){
    $sourceFile = preg_replace('/\/$/', '', $value['SOURCE']);
    if(!_is_dir($sourceFile)){
      $msg = "Warning: No such source folder '$sourceFile' exists in the conf file for $key in BACKUP_SOURCE_FOLDERS";
      writeLog($msg, $logfile);
      continue;
    }
  }
  if(preg_match('/\/$/', STORAGE_FOLDER, $matchs)){
    $desFile = STORAGE_FOLDER . $key;
  }else{
    $desFile = STORAGE_FOLDER . "/" . $key;
  }
  echo $key."\n";
  
  if(in_array($key, $tableNameArr)){
    echo "$desFile\n";
     
    if(!_is_dir($desFile)){
      if(!mkdir($desFile, 0777)){
        echo "not folder---$key\n";
        $msg = "Warning: Cannot create directory $desFile in destination, please check if web user has permisson to create the folder.";
        echo $msg."\n";
        writeLog($msg, $logfile);
        continue;
      }else{
        chmod($desFile, 0777);
      }
    }
    if(!$value['SOURCE']){
	    //no backup needed
		  continue;
    }
    $defaultProjectID = $value['DEFAULT_PROJECT_ID'];
    $projectIDarr = array();
    $updatedDirArr = array();
    $currentTableName = $key;
    $rootFolderID = 0;
     
    process_dir($sourceFile, $desFile, $rootFolderID, $updatedDirArr);
    update_dir_size($updatedDirArr);
    //print_r($updatedDirArr);
    
  }else{
    $msg = "Warning: No such DB table $key exists in DB " . MANAGER_DB;
    writeLog($msg, $logfile);
  }
  $msg = "end of $key " . @date("Y-m-j G:i:s");
  writeLog($msg, $logfile);
}

//******************************************************************
//convert linked files
//******************************************************************
if(defined("CONVERT_AUTOLINKED_FILE") and CONVERT_AUTOLINKED_FILE ){
  
  //get IP address-------------------------------
  exec("/sbin/ifconfig", $outs);
  $IP = '';
  $theScript_path = '';
  foreach($outs as $line){
   if(strpos($line, "inet")){
     if(preg_match("/inet addr:([0-9|.]+)/", $line, $matches) or preg_match("/inet ([0-9|.]+)/", $line, $matches)){
       $tmpIP = $matches[1];
       if($tmpIP != '127.0.0.1'){
        $IP = $tmpIP;
        break;
       }
     }
   }
  }
  if(!$IP){
    $msg = "Error convert linked file: Cannot parse the server IP address.";
    writeLog($msg, $logfile);
    exit;
  }
  if(preg_match("/(\/[^\/]+\/msManager\/autoBackup\/raw_backup_shell.php)/",  __FILE__, $matches)){
    $theScript_path = $matches[1];
  }else{
    $msg = "Error convert linked file: Cannot parse raw_backup_shell.php";
    writeLog($msg, $logfile);
    exit;
  }
  $theURL = "http://".$IP.$theScript_path;
  /*
  $linked_file_arr['LTQ_DEMO'][] = array('FileType'=>'RAW', 'ID'=>291, 'FileName'=>'198_RAF1_A01_12762.RAW', 'isSWATH'=>'SWATH');
  $linked_file_arr['LTQ_DEMO'][] = array('FileType'=>'WIFF', 'ID'=>292, 'FileName'=>'199_RAF1_pelletB_C.wiff', 'isSWATH'=>'');
  */
  //get autoConverter parameter sets ------------
  print_r($linked_file_arr);
  
  
  
  if($linked_file_arr){
    $results = mysqli_query($msManager_link,  "SELECT Parameters FROM `SearchParameter` WHERE TYPE = 'AutoConverter'");
    if($row = mysqli_fetch_row($results)){
      //auto_LTQ_DEMO:1;auto_LTQ_DEMO_DDA_ID:8;auto_LTQ_DEMO_SWATH_ID:74;auto_TEST_DDA_ID:8;auto_TEST_SWATH_ID:81
      $tmp_arr = explode(";", $row[0]);
      foreach($tmp_arr as $theTmp){
        list($theName, $v) = explode(":", $theTmp);
        $theName = str_replace('auto_', '', $theName);
        $table_para_set[$theName] = $v;
      }
    }
    
  }
  print_r($table_para_set);
  
  //process each tale -----------------------------
  foreach($linked_file_arr as $tableName=>$raw_files){
    if(!isset($table_para_set[$tableName]) or !$table_para_set[$tableName]) continue;
    $frm_format = 'mzXML';
    $frm_ID_DDA_str = '';
    $frm_ID_SWATH_str = '';
    foreach($raw_files as $theFile){
      if($theFile['isSWATH'] == 'SWATH' or preg_match("/SWATH/i", $theFile['FileName'], $matches)){
        if($frm_ID_SWATH_str) $frm_ID_SWATH_str .= ",";
        $frm_ID_SWATH_str .= $theFile['ID'];
      }else{
        if($frm_ID_DDA_str) $frm_ID_DDA_str .= ",";
        $frm_ID_DDA_str .= $theFile['ID'];
      }
    }
    //get parameters for each machine.
    if($frm_ID_DDA_str){
      $frm_PROTEOWIZARD_par_str = '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision';
      $frm_format = 'mzXML';
      if(isset($table_para_set[$tableName."_DDA_ID"])){
        $SQL = "SELECT Parameters FROM `SearchParameter` WHERE ID = '".$table_para_set[$tableName."_DDA_ID"]."'";
        $results = mysqli_query($msManager_link,  $SQL);
        if($row = mysqli_fetch_row($results)){
          $frm_PROTEOWIZARD_par_str = $row[0];
          
        } 
      }
      $frm_PROTEOWIZARD_par_str = escapeshellarg($frm_PROTEOWIZARD_par_str);
      $cmd = PHP_PATH." ./convert_raw_file.php $tableName $frm_ID_DDA_str $frm_format 0 0 $frm_PROTEOWIZARD_par_str ".$theURL;
      echo "$cmd\n";
      
      $tmp_PID =  system($cmd."> /dev/null & echo \$!");
     
      $msg = "PSID:$tmp_PID $tableName. Convert $frm_ID_DDA_str to $frm_format. Start at  " . @date("Y-m-j G:i:s");
      $msg .= "\nParameter: $frm_PROTEOWIZARD_par_str";
      writeLog($msg, $logfile);
    }
    if($frm_ID_SWATH_str){
      $frm_PROTEOWIZARD_par_str = '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -centroid /singleprecision';
      $frm_format = 'mzXML:SWATH';
      if(isset($table_para_set[$tableName."_SWATH_ID"])){
        $SQL = "SELECT Parameters FROM `SearchParameter` WHERE ID = '".$table_para_set[$tableName."_SWATH_ID"]."'";
        $results = mysqli_query($msManager_link,  $SQL);
        if($row = mysqli_fetch_row($results)){
          $frm_PROTEOWIZARD_par_str = $row[0];
        } 
      }
      $frm_PROTEOWIZARD_par_str = escapeshellarg($frm_PROTEOWIZARD_par_str);
      $cmd = PHP_PATH." ./convert_raw_file.php $tableName $frm_ID_SWATH_str $frm_format 0 0 $frm_PROTEOWIZARD_par_str ".$theURL;
      echo "$cmd\n";
      
      $tmp_PID =  system($cmd."> /dev/null & echo \$!");
      
      $msg = "PSID:$tmp_PID $tableName. Convert $frm_ID_DDA_str to $frm_format. Start at  " . @date("Y-m-j G:i:s");
      $msg .= "\nParameter: $frm_PROTEOWIZARD_par_str";
      writeLog($msg, $logfile);
    }
    //send to converter.
    
  }
}
//******************************************************************
exit;

//===================================================================================

function process_dir($sourceDir, $desDir, $folderID, &$updatedDirArr){
  global $OLD_STORAGE_FOLDERS;
  global $logfile, $currentTableName, $slash, $projectIDarr, $defaultProjectID, $msManager_link;
  $copied_files = 0;
  $returnedValue = 0;
  
  
  $SQL = "SELECT ID, FileName, FileType, Size, ProjectID, ProhitsID  FROM $currentTableName WHERE FolderID='$folderID'"; 
  $msg = "";
  $result = mysqli_query($msManager_link, $SQL);
  $recordsArr = array();
  //$msg = "process folder: $sourceDir";
  //writeLog($msg, $logfile);
  while($row = mysqli_fetch_assoc($result)){
    $recordsArr[$row['FileName']] = $row;
  }
  if($handle = opendir($sourceDir)){
    if($folderID == 0){
      $projPlateIDpair = "0,0";
    }else{
      $projPlateIDpair = get_project_id($sourceDir, $folderID);
    }
    array_push($projectIDarr, $projPlateIDpair);
    while(false !== ($file = readdir($handle))){
      if($file != "." && $file != ".."){
        $isReNameSource = 0; 
        $sourceFile = $sourceDir . $slash . $file;
        $file = filter_filename($file, $sourceFile, $sourceDir); //clean up dir or file name of destination.
         
        $desFile = $desDir . $slash . $file;
        if(_is_dir($sourceFile)){ 
          if(isset($OLD_STORAGE_FOLDERS) && array_key_exists($file, $recordsArr) && $recordsArr[$file]['FileType'] == "dir"){
            //the folder is in old storage folder
            if(!_is_dir($desFile)){
              $rt = mkdir($desFile, 0777, true);
              chmod($desFile, 0777);
              if(!$rt){
                $msg = "Warning: 1-0-0 directory $desFile cannot be created.";
                writeLog($msg, $logfile);
              }
            }
          }
          if(_is_dir($desFile)){
            if(array_key_exists($file, $recordsArr) && $recordsArr[$file]['FileType'] == "dir"){      //1-1-1 call process_dir().
              
              $dirOldSize = $recordsArr[$file]['Size'];
              
              $dirNewSize = get_lunux_folder_size($sourceFile); 
               if($dirOldSize != $dirNewSize){
                  $tmpArr = array('desDir' => $desFile, 'fileName' => $file, 'folderID' => $folderID, 'sourceDir' => $sourceFile, 'updateSize' => 1);
                  $currentID = $recordsArr[$file]['ID'];
                  $returnedValue = $currentID;
                  $updatedDirArr[$currentID] = $tmpArr;
                  $nextLevelFolderID = $currentID;
                  $returnedID = process_dir($sourceFile, $desFile, $nextLevelFolderID, $updatedDirArr);
                  if($returnedID){
                    $updatedDirArr[$nextLevelFolderID]['updateSize'] *= $updatedDirArr[$returnedID]['updateSize'];
                  }  
               }else if(defined("DEBUG_BACKUP") and DEBUG_BACKUP){
                  echo "'$sourceFile' size is the same as in Prohits database\n";
               }
            }else{                                                                                    //1-0-1 do nothing, give message.
              $msg = "Warning: 1-0-1 directory $desFile exist in destination but no record in DB table $currentTableName";
              writeLog($msg, $logfile);
            }  
          }else{
            if(array_key_exists($file, $recordsArr) && $recordsArr[$file]['FileType'] == "dir"){      //1-1-0 do nothing, give message.
              $msg = "Warning: 1-1-0 no such directory $desFile exist in destination. but it is in database.";
              writeLog($msg, $logfile);
            }else{
              $returnedID = creat_dir($sourceFile, $desFile, $file, $folderID, $updatedDirArr);
              if($returnedID && $folderID){
                 $updatedDirArr[$folderID]['updateSize'] = $updatedDirArr[$folderID]['updateSize'] * $updatedDirArr[$returnedID]['updateSize'];
              }  
            }  
          }  
        }else if(_is_file($sourceFile)){
				   
          if(!array_key_exists($file, $recordsArr) || $recordsArr[$file]['FileType'] == "dir"){
            
            if(copy_file($sourceFile, $desFile, $file, $folderID)){
              $copied_files++;
            }
          }
        }
      }
    }//--end of while
    array_pop($projectIDarr);
  }
  if($copied_files){
    $msg = "copied $copied_files: $sourceDir";
    writeLog($msg, $logfile);  
  }
  
  return $returnedValue;
}

function creat_dir($sourceDir, $desDir, $file, $folderID, &$newDirArr){
  global $logfile, $currentTableName, $slash, $projectIDarr, $defaultProjectID;
  $copied_files = 0;
  $returnedValue = 0; 
  $msg = "process new folder: $sourceDir";
  //==writeLog($msg, $logfile); 
  if(mkdir($desDir, 0777)){
    chmod($desDir, 0777);
    $projPlateIDpair = get_project_id($sourceDir, $folderID, 'new_folder');
     
    array_push($projectIDarr, $projPlateIDpair);
    $timeInt = _filemtime($sourceDir); 
    $ID = save_to_db($file, $desDir, $folderID, $timeInt, $sourceDir);
    if($ID){
      $tmpArr = array('desDir' => $desDir, 'fileName' => $file, 'folderID' => $folderID, 'sourceDir' => $sourceDir, 'updateSize' => 1);
      $newDirArr[$ID] = $tmpArr;
      $returnedValue = $ID;
      $nextfolderID = $ID;
      if($handle = opendir($sourceDir)){     
        while(false !== ($file = readdir($handle))){
          if($file != "." && $file != ".."){
            $isReNameSource = 0;
            $sourceFile = $sourceDir . $slash . $file;
            
            
            $file = filter_filename($file, $sourceFile, $sourceDir); //clean up file name of destination.
                      
            $desFile = $desDir . $slash . $file;
            
            if(_is_dir($sourceFile)){
              $ID = creat_dir($sourceFile, $desFile, $file, $nextfolderID, $newDirArr);
              if($ID && $nextfolderID){
                $newDirArr[$nextfolderID]['updateSize'] = $newDirArr[$nextfolderID]['updateSize'] * $newDirArr[$ID]['updateSize'];
              }  
            }else if(_is_file($sourceFile)){
              
              if(copy_file($sourceFile, $desFile, $file, $nextfolderID)){
                $copied_files++;
              }
            }
          }
        }//--end of while------
      }else{
        $msg = "Warning: fail to open source directory $sourceDir";
        writeLog($msg, $logfile);
      }
    }  
    array_pop($projectIDarr);
  }else{
    $msg = "Warning: fail to create directory $desDir";
    writeLog($msg, $logfile);
  }
  $msg = "copy $copied_files: $sourceDir";
  writeLog($msg, $logfile); 
  return $returnedValue;
}

function update_dir_size(&$DirArr){
  global $logfile, $currentTableName, $msManager_link;
  foreach($DirArr as $value){
    if($value['updateSize']){
      //$Size = get_lunux_folder_size($DirArr[$i]['desDir']);
      $Size = get_lunux_folder_size($value['sourceDir']);
      
      $SQL = "UPDATE $currentTableName SET Size='$Size'
              WHERE FolderID='".$value['folderID']."'
              AND FileName='".mysqli_escape_string($msManager_link, $value['fileName'])."'";
      
      $ret = mysqli_query($msManager_link, $SQL); 
      if(!$ret){
        $msg = "fail to update directory size for " .$value['desDir'];
        writeLog($msg, $logfile);
      }
    } 
  }
}

function copy_file($sourceFile, $desFile, $file, $folderID){
  global $logfile, $currentTableName,  $updatedDirArr;
  global $BACKUP_SOURCE_FOLDERS;
  if(isset($BACKUP_SOURCE_FOLDERS[$currentTableName]['FILE_PREFIX_FILTER'])){
    $filter_arr = $BACKUP_SOURCE_FOLDERS[$currentTableName]['FILE_PREFIX_FILTER'];
    foreach($filter_arr as $prefix){
      if(strpos(strtoupper($file), strtoupper($prefix)) === 0){
       $msg = "filtered file:$sourceFile";
       writeLog($msg, $logfile);
       return 0;
      }
    }
  }
  $rt = 0;
  $timeInt = _filemtime($sourceFile);
  if((@time() - $timeInt)/3600 > FILE_COPY_DELAY_HOURS){                //delay to copy file three hours.
		echo "copy file $sourceFile, $desFile\n";
    
		$scrFile = escapeshellarg($sourceFile);
    $trgFile = escapeshellarg($desFile);
    $cmd = '\cp -f '."$scrFile $trgFile";
    for($i = 0; $i<2; $i++){
      prohits_exec($cmd);
      if(_is_file($desFile)){
        if(_filesize($sourceFile) == _filesize($desFile)){
          save_to_db($file, $desFile, $folderID,$timeInt);
          return 1;
        }
      }
      sleep(1);
    }
    
    if(_is_file($desFile)){
      prohits_exec("rm -f $trgFile");
    }
    $msg = "Warning: failed to copy file $sourceFile";
	  echo "$msg\n";
    writeLog($msg, $logfile);
  
  }else{
    if(isset($updatedDirArr[$folderID])){
      $updatedDirArr[$folderID]['updateSize'] = 0;
    }
  }  
  return $rt;
}

// fileName, filePath, folder ID, fileModifyTime
function save_to_db($file, $desFile, $folderID,$timeInt=0,$sourceFile=''){
  global $logfile, $currentTableName, $projectIDarr, $msManager_link, $proDBarr, $HitDB_links;
  global $linked_file_arr;
  $FileName = $file;
  $FileType = '';
  $PlateID = 0;
   
  $tmpArr = explode(",", end($projectIDarr));
  $ProjectID = $tmpArr[0];
  $ProhitsID = 0;
  $tmp_rawFile = '';
  $tmp_Analysis = '';
  $DBlink = ''; 
  if(_is_dir($desFile)){
    $FileType = 'dir';
    $ProhitsID = $tmpArr[1];
    $Size = '';
    //$Size = _filesize($sourceFile);
  }else if(_is_file($desFile)){
    $PlateID = $tmpArr[1];
    if(!$PlateID){
      //manually added plate id
      $SQL = "select ProhitsID from $currentTableName where ID='$folderID'";
      $results = mysqli_query($msManager_link, $SQL);
      if($row = mysqli_fetch_row($results)){
        $PlateID = $row[0];
      }
    }
    if(preg_match('/\.(\w+)$/', $file, $matches)){
      $FileType = $matches[1];
    }
    
    if($FileType != 'scan' and $FileType != 'SCAN' and isset($proDBarr[$ProjectID])){
      $tmpLocation = '';
      $tmpGelFree = false;
      $SQL = '';
      $tmpName_arr = explode("_", str_replace(".$FileType","", $FileName), 2);
      if($PlateID){
        //get gel band file $ProhitsID//raw file is formded by will code and band ID
        $tmpLocation = $tmpName_arr[0];
        $SQL = "select B.ID, B.Location, B.RawFile, B.Analysis from Band B, PlateWell W where B.ID=W.BandID and W.PlateID='$PlateID' and W.WellCode='$tmpLocation' and B.ProjectID='$ProjectID'";
        if(isset($tmpArr[1])){
          $tmpProhitsID = $tmpName_arr[1];
          $SQL .= " and B.ID='$tmpProhitsID'";
        }
      }else if(is_numeric($tmpName_arr[0]) and count($tmpName_arr)>1){
        //get gel free file $ProhitsID.
        $tmpGelFree = true;
        $tmpBandID = $tmpName_arr[0];
        $tmpLocation = str_replace(".$FileType","", $tmpName_arr[1]);
        $SQL = "select ID, Location, RawFile, Analysis from Band where ID='$tmpBandID' and ProjectID='$ProjectID'"; 
        //$SQL .= "and Location like '$tmpLocation%'";
      }
      if($SQL){
       
        $dbName = $proDBarr[$ProjectID];
        $DBlink = $HitDB_links[$dbName];
        $result = mysqli_query($DBlink, $SQL);
        if($row = mysqli_fetch_row($result)){
          $ProhitsID = $row[0];
          $theLocation = $row[1];
          $tmp_rawFile = $row[2];
          $tmp_Analysis = $row[3];
          
          if($tmpGelFree and strpos($tmpLocation, substr($theLocation, 0,4)) !== 0){
            $ProhitsID = '';
            $theLocation = '';
            $tmp_rawFile = '';
            $tmp_Analysis = '';
          }
        }
        
      
      }
    }
    $Size = _filesize($desFile);
  }
  clearstatcache();
  $User = "";
  if($timeInt){
    $Date = @date("Y-m-d G:i:s",$timeInt);
  }else{
    $Date = @date("Y-m-d G:i:s");
  }
  
  $SQL = "INSERT INTO $currentTableName SET
        FileName='".mysqli_escape_string($msManager_link, $FileName)."',
        FileType='$FileType',
        Size='$Size',
        FolderID='$folderID',
        Date='$Date',
        User='$User',
        ProhitsID = '$ProhitsID',
        ProjectID='$ProjectID'";
  //echo "$SQL\n";
  $ret = mysqli_query($msManager_link, $SQL);
  if($ret){
    $ID = mysqli_insert_id($msManager_link);
    //updata Analyst/Band table
    if($ProhitsID and $DBlink){
      $pt = "$currentTableName:$ID";
      $SQL = "update Band set RawFile=";
      if($tmp_rawFile){
        $SQL .= "'$tmp_rawFile;$pt' where ID='$ProhitsID'";
      }else{
        $SQL .= "'$pt' where ID='$ProhitsID'";
      }
      mysqli_query($DBlink, $SQL);
    }
    $thisType = strtoupper($FileType);
    if($ProhitsID and $ProjectID and ($thisType == 'RAW' or $thisType == 'WIFF')){
      $theType = strtoupper($FileType);
      $linked_file_arr[$currentTableName][] = array('FileType'=>$thisType, 'ID'=>$ID, 'FileName'=>$FileName, 'isSWATH'=>$tmp_Analysis);
    } 
    return $ID;
  }else{
    $msg = "Warning: failed to insert record for $desFile to DB table $currentTableName.";
    writeLog($msg, $logfile);
    return 0;
  }
}
//--return a formated file name. only remove space from a file name.
function filter_filename($filename, $sourcefullName, $sourceDir){
  global $logfile;
  if(_is_file($sourcefullName) and preg_match("/[\s']/", $filename, $matches)){
    $new_filename = preg_replace("/[\s']/", "", $filename);
    $sourcefullNameNew = $sourceDir . "/" . $new_filename;
    if(_is_file($sourcefullNameNew)){
       $new_filename = preg_replace("/[\s']/", "_", $filename);
       $sourcefullNameNew = $sourceDir . "/" . $new_filename;
       if(!_is_file($sourcefullNameNew)){
         $filename = $new_filename;
       }
    }else{
      $filename = $new_filename;
    }
  }
  return $filename;
}

function get_project_id($sourceDir,$folderID = 0, $new_folder = false ){ //return a string contain projectID and folder ID 
  global $defaultProjectID, $proDBarr, $HitDB_links;
  global $logfile;
  global $currentTableName, $msManager_link;
  $plateID = 0;
  $projectID = 0;
  /* 
  if($folderID){
    $SQL = "SELECT ProjectID, ProhitsID  FROM $currentTableName WHERE ID='$folderID'"; 
    $result = mysqli_query($msManager_link, $SQL); 
    if($row = mysqli_fetch_assoc($result)){
      return $row['ProjectID'] . "," . $row['ProhitsID']; 
    }
  }
  */
  if($new_folder and $dirBase = basename($sourceDir)){
     
    if(preg_match('/([A-Za-z0-9\-]+)_A(\d+)_P(\d+)$|_P(\d+)$/', $dirBase, $matches2)){ //20051203_YDP00123_A234_P23
    
      if(isset($matches2[4])){
        $plateName = '';
        $plateID = '0';
        $projectID = $matches2[4];
      }else{
        $plateName = $matches2[1];
        $plateID = $matches2[2];
        $projectID = $matches2[3];
      }
      if(array_key_exists($projectID, $proDBarr)){
        $dbName = $proDBarr[$projectID];
        $DBlink = $HitDB_links[$dbName];
        if($DBlink and $plateName){
          $SQL = "SELECT ProjectID FROM Plate WHERE ID='$plateID' AND Name='$plateName' AND ProjectID='$projectID'";
          $result = mysqli_query($DBlink, $SQL); 
          if(!($row = mysqli_fetch_assoc($result))){
            $msg = "plateID , plate name or projectID is mismatched ----- $sourceDir.";
            $plateID = 0;
            writeLog($msg, $logfile);
          }
        }
      }else{
        $msg = "project $projectID doesn't exist ----- $sourceDir.";
        writeLog($msg, $logfile);
      }
    }else{
      //$platName = "";
      //$plateID = 0;
      //$projectID = $defaultProjectID;
      //$msg = "No ProjectID, PlateID and Plate name associated with this plate -- $sourceDir. Add default ProjectID";
      //writeLog($msg, $logfile);
    }
    
  }
  if(!$projectID and $folderID){
    $SQL = "SELECT ProjectID, ProhitsID  FROM $currentTableName WHERE ID='$folderID'"; 
    $result = mysqli_query($msManager_link, $SQL); 
    if($row = mysqli_fetch_assoc($result)){
      return $row['ProjectID'] . "," . $row['ProhitsID']; 
    }
  }
  if(!$projectID){
    $platName = "";
    $plateID = 0;
    $projectID = $defaultProjectID;
  }
  
  return $projectID . "," . $plateID; 
}



function get_db_tableNames($dbLink, $dbname){  //--return a array contain all table names in DB backup.
  $tableNameArr = array();
  $sql = "SHOW TABLES FROM $dbname";
  $result = mysqli_query($dbLink, $sql);
  if($result){
    while($row = mysqli_fetch_row($result)){
      array_push($tableNameArr, $row[0]);
    }
    mysqli_free_result($result);
  }
  return $tableNameArr;
}

function get_convert_parameter($tableName){
  global $logfile;
  global $msManager_link;
  $para_arr = array();
  $SQL = "SELECT `ID` , `Format` , `Parameter` 
          FROM `RawConvertParameter` WHERE TableName='$tableName' order by ID desc limit 1";
  $result = mysqli_query($msManager_link, $SQL);
  if($row = mysqli_fetch_assoc($result)){
    if($row['Format']){
      if (defined('RAW_CONVERTER_SERVER_PATH')) {
          if (($rh = @fopen(RAW_CONVERTER_SERVER_PATH, 'rb')) === FALSE) { 
            $msg = "Error: cannot connnect 'RAW_CONVERTER_SERVER_PATH' ".RAW_CONVERTER_SERVER_PATH . ". Raw file will not be converted";
            writeLog($msg, $logfile);
          }else{
            fclose($rh);
            $para_arr['ID'] = $row['ID'];
            $para_arr['Format'] = $row['Format'];
            $para_arr['Parameter'] = $row['Parameter'];
          }
      }else{
        $msg = "Error: cannot convert file for $tableName raw files since 'RAW_CONVERTER_SERVER_PATH' is not defined in conf file.";
        writeLog($msg, $logfile);
      }
    }
  }
  return $para_arr;
}
?>