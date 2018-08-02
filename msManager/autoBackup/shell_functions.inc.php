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
function getRunningFtpLog($userName){
  $export_file_log = '';
  exec("ps axu | grep shell_ftp_upload.php", $output);
  //USER PID %CPU %MEM VSZ RSS TTY STAT START TIME COMMAND
  foreach($output as $tmp_str){
    $tmp_arr = preg_split('/[ ]+/', trim($tmp_str));
    //print_r($tmp_arr);exit;
    if(count($tmp_arr) > 13){
      if($tmp_arr[11] == 'shell_ftp_upload.php'){
        if( $userName == $tmp_arr[13]){
          $export_file_log = $tmp_arr[14];
        }
      }
    }
  }
  return $export_file_log;
}
function getPhpProcess_arr($tableName='', $frm_theTaskID=0, $PID = 0){
  $runningNum = 0;
  $phpProcess_arr = array();
  //get all php processes
  exec("ps axu | grep .php", $output);
  //USER PID %CPU %MEM VSZ RSS TTY STAT START TIME COMMAND
  //print_r($output);
  
  foreach($output as $tmp_str){
    $tmp_arr = preg_split('/[ ]+/', trim($tmp_str));
    //print_r($tmp_arr);
    if(count($tmp_arr) > 11){
      $tmp_command = $tmp_arr[10];
      if( preg_match('/php$/', $tmp_command)){
        $phpProcess_arr[$tmp_arr[1]] = array('user' => $tmp_arr[0],
                                              'pid' => $tmp_arr[1],
                                              '%cpu' => $tmp_arr[2],
                                              '%mem' => $tmp_arr[3],
                                              'vsz' => $tmp_arr[4],  //virtual size in kilobytes
                                              'stat'=> $tmp_arr[7], 
                                              'start'=>$tmp_arr[8], 
                                              'time' => $tmp_arr[9],
                                             'script' => $tmp_arr[11]);
        if(count($tmp_arr) > 13){
          $phpProcess_arr[$tmp_arr[1]]['Machine'] = $tmp_arr[12];
          $phpProcess_arr[$tmp_arr[1]]['TaskID'] = $tmp_arr[13];
          if($tableName and $frm_theTaskID){
            if(strpos($tmp_arr[11], 'auto_search_table_shell.php')){
              if($tmp_arr[12] == $tableName and $tmp_arr[13] == $frm_theTaskID){
                if($PID){
                  if($tmp_arr[1] == $PID){
                    return 1;
                  }
                }else{
                  $runningNum++;
                }
              }
            }
          }
          $i = 12;
          while(isset($tmp_arr[$i])){
            $tmp_arg = "arg" . ($i - 5);
            //$phpProcess_arr[$tmp_arr[1]][$tmp_arg] = $tmp_arr[$i];
            $i++;
          }
        }
      }
    }
  }
  if($tableName and $frm_theTaskID){
    return $runningNum;
  }else{
    return $phpProcess_arr;
  }
}//end of function
if (!function_exists('fatalError')) {
//----------------------------------------------
function fatalError($msg='', $line=0, $log_file=''){
//----------------------------------------------
  global $start_time;
  if(!$start_time)$start_time=@date("D M j G:i:s T Y"); 
  $msg  = "Fatal Error--<font color=\"#FF0000\">$msg</font>;<br>\n";
  $msg .= "Script Name: " . $_SERVER['PHP_SELF']. ";<br>\n";
  $msg .= "Start time: ". $start_time . ";";
  if($line){
    $msg .= " Line number: $line;";
  }
  if($log_file){
    writeLog($msg, $log_file);
  }else{
    writeLog($msg);
  }
  echo $msg."\n";
  exit;
}
}

if (!function_exists('writeLog')) {
//------------------------------------ 
function writeLog($msg, $log_file=''){
//----------------------------------- 
  global $logfile; 
  global $debug;
  if(!$log_file and $logfile){
    $log_file = $logfile;
  } 
  if(DEBUG_SEARCH or DEBUG_TPP or DEBUG_CONVERTER) echo $msg."\n";
  
  $log = fopen($log_file, 'a+');
  if(!$log){
    echo "can not open the log file to write: $log_file"; exit;
  }
  fwrite($log, "\n" . $msg);
  fclose($log);
}
}
if (!function_exists('is_process_running')) {
function is_process_running($PID){
   exec("ps $PID", $ProcessState);
   return(count($ProcessState) >= 2);
}
}
//===============================================
function check_permission($SID, $tableName){
//=============================================== 
  $host = HOSTNAME;
  $prohits_link = mysqli_connect($host, USERNAME, DBPASSWORD, PROHITS_DB) or die("Unable to connect to mysql..." . mysqli_error($prohits_link));
  $rt = false;
  $SQL = "SELECT UserID FROM Session WHERE SID = '$SID'";
   
  $result = mysqli_query($prohits_link, $SQL);
  if($row = mysqli_fetch_row($result) ){
    $User_id = $row[0];
    $SQL  = "select P.Insert, P.Modify, P.Delete from PagePermission P, Page G where P.PageID=G.ID and G.PageName='Auto Search' and UserID=$User_id";
    $results = mysqli_query($prohits_link, $SQL);
    if($row = mysqli_fetch_row($results)){
      $perm_modify = $row[1];
      $perm_delete = $row[2];
      $perm_insert = $row[0];
      $rt = $perm_insert;
    }
  }else{
    $msg = "session id is not in session table $SID. or the user has no permission to run the script";
    fatalError($msg);
  }
  return $rt;
}

function get_projectDB($prohits_link=''){ 
//--return a assoc array witch projectID as key and DBname as value.
  if(!$prohits_link){
    $host = PROHITS_SERVER_IP;
    if(STORAGE_IP == PROHITS_SERVER_IP){
      $host = HOSTNAME;
    }
    $prohits_link = mysqli_connect($host, USERNAME, DBPASSWORD, PROHITS_DB ) or die("Unable to connect to mysql..." . mysqli_error($prohits_link));
  }
  $proDBarr = array();
  $SQL = "SELECT ID, DBname FROM Projects ORDER BY ID";
  $result = mysqli_query($prohits_link, $SQL);
  while($row = mysqli_fetch_assoc($result)){
    $proDBarr[$row['ID']] = $row['DBname'];
  }
  return $proDBarr;
}

function checkLogSize($logfile, $maxsize=1000){
  if(!_is_file($logfile)) return;
  $fsize = _filesize($logfile)/1024;
  if($fsize > $maxsize){
    rename($logfile, $logfile."_".@date("Ymj"));
    if($fd = fopen($logfile, 'w')){
       fclose($fd);
       chmod($logfile, 0757);
     }
  }
}

//----------------------------------------------------------------------------------------
function convertLargeRawFile($tableName, $ID, $file, $FileType, $desFile, $rawConvert_arr, $new_converted_file_arr = array(), $dont_create_tar = 1, $no_addtional_mgf = 0, $auotConvert=''){
//----------------------------------------------------------------------------------------
  
  global $logfile;
  global $frm_theURL;
  global $test;
  global $debug;
  $wiff_scan_file = '';
  $response1 = '';
  
  //check if mascot installed
  if($no_addtional_mgf or !defined("MASCOT_IP") or !MASCOT_IP){
    $no_addtional_mgf = 1;
  }
  
  
  $converted_file = array(); 
  if(defined('DEBUG_CONVERTER') and DEBUG_CONVERTER ){
    print "\nfileName: $file \nfileType: $FileType\nfileFullPath: $desFile\n";
  }
  //$converted_file['Name'] = preg_replace("/$FileType$/i","", $file) . $rawConvert_arr['Format'];
  //$converted_file['Path'] = preg_replace("/$FileType$/i","", $desFile) . $rawConvert_arr['Format'];
   
  if($new_converted_file_arr){
    $converted_file['Name'] =  $new_converted_file_arr['FileName'];
    $converted_file['Path'] =  $new_converted_file_arr['path'];
    $converted_file['Format'] = $rawConvert_arr['Format'];
    $converted_file['ID'] = $new_converted_file_arr['ID'];
    $converted_file['parameter'] = $rawConvert_arr['Parameter'];
  }else{
    $converted_file['Name'] = preg_replace("/$FileType(.gz)?$/i","", $file) . $rawConvert_arr['Format'];
    $converted_file['Path'] = preg_replace("/$FileType(.gz)?$/i","", $desFile) . $rawConvert_arr['Format'];
    $converted_file['Format'] = $rawConvert_arr['Format'];
    $converted_file['parameter'] = $rawConvert_arr['Parameter'];
  }
   
  if($rawConvert_arr['is_iProphet'] and !$converted_file['ID'] and ($FileType=='mgf' or $FileType=='mzML')){
    
    $converted_file['Name'] = $file_baseName."_I.".$converted_file['Format'];
    $converted_file['Path'] =  dirname($new_converted_file_arr['path'])."/". $converted_file['Name'];
  }
  
   
  
  $file_baseName = preg_replace('/\.\w+(.gz)?$/', "", $converted_file['Name']);
  //echo "$tableName, $ID, $file, $FileType, $desFile";exit;
  echo "\n";
  if($FileType == 'mgf' 
      or $FileType == 'mgf.gz' 
      or $FileType == 'mzML' 
      or $FileType == 'mzML.gz' 
      or $FileType == 'mzXML' 
      or $FileType == 'mzXML.gz'
      or (RAW_CONVERTER_SERVER_PATH and strpos(RAW_CONVERTER_SERVER_PATH, 'http://') === false)
      ){
    //run msconvert on local Prohits server.
    
    $tmp_FileType = preg_replace("/\.gz$/", "", $FileType);
    if($local_PWZ_path = get_local_PWIZ_path()){
      if($FileType == 'mgf.gz'){
        $new_raw_file_path = preg_replace("/\.gz$/", '', $desFile);
        if(!_is_file($new_raw_file_path)){
          $cmd = "gunzip -c ".escapeshellarg($desFile) .">". escapeshellarg($new_raw_file_path); 
          echo "$cmd\n";
          prohits_exec($cmd);
        }
        $desFile = $new_raw_file_path;
      }
      $desFile_name = preg_replace("/\.\w+(\.gz)?$/", '', $desFile);
      
      $tmp_arr = explode("|", $rawConvert_arr['Parameter']);
      $parameter = $tmp_arr[0];

      $need_convert_to = $rawConvert_arr['Format'];

      if($tmp_FileType != 'mgf' and $tmp_FileType != 'mzML' and $tmp_FileType != 'mzXML' and strpos(RAW_CONVERTER_SERVER_PATH, 'http://') === false){
        $cmd = RAW_CONVERTER_SERVER_PATH;
        if(defined("PREFERRED_FILE_TYPE") and PREFERRED_FILE_TYPE){
          $rawConvert_arr['Format'] = PREFERRED_FILE_TYPE;
          $converted_file['Format'] = PREFERRED_FILE_TYPE;
        }else{
          $rawConvert_arr['Format'] = 'mzML';
          $converted_file['Format'] = 'mzML';
        }
        $converted_file['Path'] = $desFile_name.".".$converted_file['Format'];
        $converted_file['Name'] = basename($converted_file['Path']);
        
      }else{
        $cmd = $local_PWZ_path . "msconvert";
        $parameter = '--32 --mz32 --inten32';
        $dont_create_tar = 1;
      }
      if($dont_create_tar){
        $parameter = preg_replace("/-g/", '', $parameter);
        $converted_file['Format'] = preg_replace("/\.gz$/", '', $converted_file['Format']);
        $converted_file['Name'] = preg_replace("/\.gz$/", '', $converted_file['Name']);
        $converted_file['Path'] = preg_replace("/\.gz$/", '', $converted_file['Path']);
        
      }else if(preg_match("/-g/", $parameter, $matches) and !preg_match("/\.gz$/",$converted_file['Name'] , $matches)){
        $converted_file['Format'] .= '.gz';
        $converted_file['Name'] .= '.gz';
        $converted_file['Path'] .= '.gz';
      }
      
      
      
      $cmd .= " ".escapeshellarg($desFile)." --". $rawConvert_arr['Format']. " -v $parameter --outdir " . escapeshellarg(dirname($desFile))." --outfile ". escapeshellarg($file_baseName);
      echo $cmd;
      system($cmd);
       
      if(($rawConvert_arr['Format'] == 'mzXML' or $rawConvert_arr['Format'] == 'mzML') and $FileType !='mgf' and $FileType !='mgf.gz' and !$no_addtional_mgf){
        
        $cmd = $local_PWZ_path . "msconvert ".escapeshellarg($converted_file['Path'])." --mgf -v --outdir " . escapeshellarg(dirname($desFile))." --outfile ". escapeshellarg($file_baseName);
        prohits_exec($cmd);
        $cmd = "perl \"".$local_PWZ_path."RawConverter.pl\" modifyMGF ".escapeshellarg("$desFile_name.mgf")."\n";
        prohits_exec($cmd);
         
        if($need_convert_to == 'mgf'){
          $converted_file['Name_2']= $converted_file['Name'];
          $converted_file['Path_2']= $converted_file['Path'];
          $converted_file['Format_2'] = $converted_file['Format'];
          $converted_file['Name'] = basename("$desFile_name.mgf");
          $converted_file['Path'] = "$desFile_name.mgf";
          $converted_file['Format'] = 'mgf';

        }else{
          $converted_file['Name_2']= basename("$desFile_name.mgf");
          $converted_file['Path_2']= "$desFile_name.mgf";
          $converted_file['Format_2'] = 'mgf'; 
        }
      }else if($rawConvert_arr['Format'] == 'mgf'){
        $new_mgf_path = preg_replace("/\.gz$/",'', $converted_file['Path']);
        if(preg_match("/-g/", $parameter, $matches)){
           $cmd = "gunzip -c ".escapeshellarg($converted_file['Path']) .">". escapeshellarg($new_mgf_path); 
           prohits_exec($cmd);
        }
        $cmd = "perl \"".$local_PWZ_path."RawConverter.pl\" modifyMGF ".escapeshellarg($new_mgf_path)."\n";
        prohits_exec($cmd);
      }
      if(_is_file($converted_file['Path'])){
        return $converted_file;
      }else{
        return array();
      }
    }
  }
   
  
  
  
  if(strtoupper($FileType) == 'WIFF'){
    $FileType = getFileExtension($desFile);
    if( $FileType == 'WIFF') {
      $wiff_scan_file = $desFile . ".SCAN";
    }else{
      $wiff_scan_file = $desFile . ".scan";
    }
    if(!_is_file($wiff_scan_file)){
      writeLog("The $wiff_scan_file doesn't exist. $file file cannot be converted.");
      return '';
    }
  } 
  if($auotConvert and defined('CONVERT_AUTOLINKED_FILE') and CONVERT_AUTOLINKED_FILE){
    $formaction = CONVERT_AUTOLINKED_FILE;
  }else{
    $formaction = RAW_CONVERTER_SERVER_PATH;
  }
  $formaction_dir = substr($formaction, 0, strrpos($formaction, "/")); 
  if($fd = &fopen($formaction, 'r')){
    fclose($fd);
  }else{
    writeLog("The $formaction doesn't work! Raw file cannot be converted.");
    return '';
  }
  
  if(!_is_file($desFile)){
    $msg = "converter:" .$desFile." is not file.";
    writeLog($msg, $logfile);
    return $converted_file;
  } 
  echo "$formaction\nconvert file from $file to ". $rawConvert_arr['Format']."\n";
   
  
  $req = new HTTP_Request($formaction, array('timeout' => 36000,'readTimeout' => array(36000,0)));
  $req->setMethod(HTTP_REQUEST_METHOD_GET);
  //$req->addHeader('Content-Type', 'text/html');
  $tmp_arr = explode("|", $rawConvert_arr['Parameter']);
  $frm_PROTEOWIZARD_par_str = $tmp_arr[0];
  
  
   
  
  if($dont_create_tar){
    $frm_PROTEOWIZARD_par_str = preg_replace("/-g/", '', $frm_PROTEOWIZARD_par_str);
  }
  
  $use_proteowizard = '';
  $OUTPUT_CONTENT_TYPE = '';
  $PRECISON = '';
  
  if(count($tmp_arr) == 2){
    $frm_SCIEX_par_str = $tmp_arr[1];
    $tmp_arr = explode(" ", $frm_SCIEX_par_str);
    if(count($tmp_arr) == 3){
      $use_proteowizard = $tmp_arr[0];
      $OUTPUT_CONTENT_TYPE = $tmp_arr[1];
      $PRECISON = $tmp_arr[2];
    }
  }
  if(isset($rawConvert_arr['is_iProphet'])){
    $req->addQueryString('is_iProphet', $rawConvert_arr['is_iProphet']);
  }
  if(isset($rawConvert_arr['is_SWATH_file']) and $rawConvert_arr['is_SWATH_file']){
    $req->addQueryString('is_SWATH_file', $rawConvert_arr['is_SWATH_file']);
  }
  
  if($no_addtional_mgf and $rawConvert_arr['Format'] != 'mgf'){
     
    $req->addQueryString('NOmgf', 'NOmgf');
  }
  $req->addQueryString('proteowizard_para_str', $frm_PROTEOWIZARD_par_str);
  if($rawConvert_arr['Format'] == 'mgf' and defined("PREFERRED_FILE_TYPE") and PREFERRED_FILE_TYPE){
    $rawConvert_arr['Format'] = PREFERRED_FILE_TYPE;
  }
  $req->addQueryString('format', $rawConvert_arr['Format']);
  $req->addQueryString('SID', 'rawDataConverter');
  $req->addQueryString('tableName', $tableName);
  $req->addQueryString('ID', $ID);
  if(strtoupper($FileType) == 'WIFF'){
    $req->addQueryString('use_proteowizard', $use_proteowizard);
    $req->addQueryString('OUTPUT_CONTENT_TYPE', $OUTPUT_CONTENT_TYPE);
    $req->addQueryString('PRECISON', $PRECISON);
    
    $req->addQueryString('file1',$desFile);
    $req->addQueryString('file2',$wiff_scan_file);
  }else{
    $req->addQueryString('file1', $desFile);
  }
  $req->addQueryString('download_from', dirname($frm_theURL)."/download_raw_file.php");
  
  $response = $req->sendRequest();
  if (!PEAR::isError($response)) {
    $response1 = $req->getResponseBody();
  } else { 
    //writeLog("the file $desFile can not be converted.", $logfile);
    writeLog($response->getMessage(). "\nPlease check if '$formaction' is working", $logfile);
    $converted_file = array();
    //return $converted_file;
  }
  
  if ($response1) {
    
     print_r("\n********return from $formaction***********\n");
     print_r($response1);
     print_r("\n*********end*****************\n");
      
      
      
    if(preg_match_all('/>>>(.+)<<</', $response1, $matches)){
      $copy_to_arr = array();
      $i = 1;
      foreach($matches[1] as $tmp_DataFile){
        $tmp_DataFile = $formaction_dir."/".$tmp_DataFile;
        if(preg_match('/\.(\w+(\.gz)*)$/', $tmp_DataFile, $matches)){
          $file_ext = $matches[1];
        }else{
          $file_ext = $converted_file['Format'];
        }
        $file_path_file_root = preg_replace('/\.\w+(\.gz)*$/', "", $converted_file['Path']);
        $format_needed = preg_replace('/\.gz$/', '', $converted_file['Format']);
        $copy_to = $file_path_file_root.".".$file_ext;
        if(preg_match("/$format_needed/i", $file_ext)){
          $converted_file['Path'] = $copy_to;
          $converted_file['Format'] = $file_ext;
          $converted_file['Name'] = preg_replace('/\.\w+(\.gz)*$/', "", $converted_file['Name']).".".$file_ext;
        }else{
          $i++;
          $converted_file['Path_'.$i] = $copy_to;
          $converted_file['Format_'.$i] = $file_ext;
          $converted_file['Name_'.$i] = preg_replace('/\.\w+(\.gz)*$/', "", $converted_file['Name']).".".$file_ext;
          
        }
        copy($tmp_DataFile, $copy_to);
        array_push($copy_to_arr, $copy_to);
        if(_filesize($copy_to) < 1){
          foreach($copy_to_arr as $copied){
            unlink($copied);
          }
          writeLog("$desFile can not be converted.\nConverter return 0 file size.", $logfile);
          return array();
        }else{
          chmod("$copy_to", 0666);
          echo "converted file ". $copy_to."\n";
        }
      }
      //print_r($converted_file);
    }else{
      if(preg_match("/(Error:.+)\W/", $response1, $matchs)){
        $msg = $matchs[1]. " from \n$formaction";
        if(preg_match("/(.+\n.+\n)Error:/", $response1, $matchs)){
          $msg .= "\n".$matchs[1];
        }
      }else{
        $msg = "Please check if '$formaction' is working.";
      }
      writeLog("the file $desFile can not be converted.\n$msg", $logfile);
      $converted_file = array();
    }
    unset($response1);
  } 
   
   
   
   
   
  return $converted_file;
}
//--------------------------------------
function getFileExtension($fileName=''){
//--------------------------------------
  $rt = '';
  if(!$fileName) return '';
  if(preg_match('/\.(\w+|\w+\.gz)$/i', $fileName, $matches)){
     $rt = $matches[1];
  }
  return $rt;
}

//----------------------------------------------------------------------
function checkFileFormat($dir_path = '', $ID, $file = '', $engine = ''){
//----------------------------------------------------------------------
  //RAW or WIFF file will find/convert same parameter file 
  //       mgf for Mascot mzXML for GPM and SEQUST
  //other file: DTA/MGF/PKL is OK for Mascot
  //            DTA/MGF/PKL/MZXML is OK for GPM
  //            GPM/MZXML is OK for SEQUEST
  //$replace_existing
  //0 = Don't convert if same parameter file exists
  //1 = force to replace previous converted file
  //2 = Make new
  //3 = replace previous converted (base name) file, if not the same parameter file (iProphet, SWATH);
  global $tableName;
  global $theTask_arr;
  global $frm_theURL;
  global $debug;
  global $rawConvert_arr;
  global $is_SWATH_file;
  global $SWATH_app;
  global $search_logfile;
  //print_r($theTask_arr);exit;
  $msg = '';
  $new_file = array();
  $converted_file = array();
  $is_iProphet = false;
  $replace_existing = 3; //replace previous converted
  
  if(strpos($theTask_arr['SearchEngines'], 'iProphet')===0){
    $is_iProphet = true;
    $replace_existing = 3;
  }
  if(!_is_file($dir_path . $file)){
    $msg = "Warning: the file dosen't exist: " .$dir_path . $file;
    writeLog($msg);
    return $new_file;
  }else if(!$dir_path or !$file or !$engine ){
    $msg = "warning: no enough info passed to checkFileFormat ". $file;
    writeLog($msg);
    return $new_file;
  }  
  $type = getFileExtension($file);
  $upperType = strtoupper($type);  
  if($theTask_arr['LCQfilter'] and strstr($theTask_arr['LCQfilter'], '-M')){
    $theTask_arr['LCQfilter'] = '';
  }
  $frm_lcq_par_str = (strstr($theTask_arr['LCQfilter'], '--inten'))?$theTask_arr['LCQfilter']:'--filter "peakPicking true 2" --filter "msLevel 2" -g';
  $rawConvert_arr['Parameter'] = trim($frm_lcq_par_str);
  
  
  if($is_SWATH_file){
    if($upperType != 'MZXML' and $upperType != 'MZXML.GZ' and $upperType != 'MZML' and $upperType != 'MZML.GZ' and $upperType != 'RAW' and $upperType != 'WIFF'){
      $msg = "warning: only mzML, mzXML, RAW, and WIFF file can be run DIA-Umpire. ($file)";
      writeLog($msg);
      return $new_file;
    }
    $replace_existing = 3;
    $rawConvert_arr['Format'] = 'mzXML';
    $rawConvert_arr['is_SWATH_file'] = true;
    if($engine == 'MSPLIT_DDA'){
      $rawConvert_arr['Parameter'] = str_replace("centroid", "proteinpilot", $rawConvert_arr['Parameter']);
      $rawConvert_arr['is_SWATH_file'] = false;
    }else{
      $rawConvert_arr['Parameter'] = str_replace("proteinpilot", "centroid", $rawConvert_arr['Parameter']);
    }
    
    if($upperType == 'MZXML' or $upperType == 'MZXML.GZ'){
      $new_file['fileName'] = $file;
      $new_file['type'] = $type;
      $msg = "File existed: ". $file;
      writeLog($msg,$search_logfile);
      return $new_file;
    }
  }else if($engine == 'Mascot'){
    $rawConvert_arr['Format'] = 'mgf';
    if($upperType == 'MGF' or $upperType == 'MGF.GZ'){
      $new_file['fileName'] = $file;
      $new_file['type'] = $type;
      if(!$is_iProphet){
        return $new_file;
      }
    }
  }else{
    if(defined("PREFERRED_FILE_TYPE") and PREFERRED_FILE_TYPE){
      $rawConvert_arr['Format'] = PREFERRED_FILE_TYPE;
    }else{
      $rawConvert_arr['Format'] = 'mzML';
    }
    $upper_preferred_type = strtoupper($rawConvert_arr['Format']);
    
    if($upperType == $upper_preferred_type or $upperType == $upper_preferred_type.'GZ'){
      $new_file['fileName'] = $file;
      $new_file['type'] = $type;
      return $new_file;
    }
  }
  $rawConvert_arr['is_iProphet'] = $is_iProphet;
  
  $new_converted_file_arr = get_new_converted_file_array($theTask_arr['UserID'], $replace_existing, $dir_path . $file, $ID, $rawConvert_arr);
  
  //print_r($rawConvert_arr);
  //print_r($new_converted_file_arr); exit;
  
  if($new_converted_file_arr['status'] == 'existed' and _filesize($new_converted_file_arr['path']) > 5){
    $new_file['fileName'] = $new_converted_file_arr['FileName'];
    $new_file['type'] = $new_converted_file_arr['FileType'];
    $msg = "File existed: ". basename($new_converted_file_arr['path']);
    writeLog($msg,$search_logfile);
    return $new_file;
  }else{
    if(!RAW_CONVERTER_SERVER_PATH){
      $new_file['fileName'] = $file;
      $new_file['type'] = $type;
      return $new_file;
    }
    $file_path = $dir_path.$file; 
    if($new_converted_file_arr['status'] == 'other_format' and _filesize($new_converted_file_arr['path']) > 5){
      //print_r($new_converted_file_arr);
      
      $file_path = $new_converted_file_arr['path'];
      $type = $new_converted_file_arr['FileType'];
      $file = $new_converted_file_arr['FileName'];
      
      $new_converted_file_arr = array();
      $file_root = preg_replace("/\.\w+(\.gz)?$/i", "", $file_path);
      $tmp_file_path = $file_root.".".$rawConvert_arr['Format'];
  
      $new_converted_file_arr['ID'] = '';
      $new_converted_file_arr['path'] = $tmp_file_path;
      $new_converted_file_arr['User_ID'] = $theTask_arr['UserID'];
      $new_converted_file_arr['status'] = 'new';
      $new_converted_file_arr['FileName'] = basename($tmp_file_path);
    }
    
    echo "\n\n$tableName, $ID, $file, $type, $dir_path.$file,";
    
    //------------------------------------------------------------------------------------------------------------------------------
    $converted_file = convertLargeRawFile($tableName, $ID, $file, $type, $dir_path.$file, $rawConvert_arr, $new_converted_file_arr);
    //------------------------------------------------------------------------------------------------------------------------------
    
    if($converted_file){
      $msg = "Created file:" .$converted_file['Name'] ;
      writeLog($msg);
      writeLog($msg,$search_logfile);
      $new_file['fileName'] = $converted_file['Name'];
      $new_file['type']     = $converted_file['Format'];
    }else{
      $msg = "Warning: can not convert ".$file." to ". $rawConvert_arr['Format']. " in $dir_path";
      writeLog($msg);
      writeLog($msg,$search_logfile);
      $new_file = array();
    }
  }
   
  if($converted_file){
    saveConvertedFile2db($converted_file, '', $ID);
  }
  if(!$new_file){
    $msg = "warning: " .$file . " file type willn't be supported by ".$engine;
    writeLog($msg);
    writeLog($msg,$search_logfile);
  }
  
  //if(($engine == 'SEQUEST' or $engine == 'GPM' or $engine == 'COMET' or $engine == 'MSGFPL') and $new_file['type'] == 'mgf' ){
  //  $new_file = mgf2mzXML($new_file, $dir_path);
  //}
  return $new_file;
}
//---------------------------------------
function mgf2mzXML($new_file, $dir_path){
//---------------------------------------
  $tmp_new_file = array();
  $theFileNameBase = preg_replace("/[.]".$new_file['type']."$/i","", $new_file['fileName']);
  if(_is_file($dir_path.$theFileNameBase.".mzXML.gz")){
    $tmp_new_file['fileName'] = $theFileNameBase.".mzXML.gz";
    $tmp_new_file['type'] = "mzXML.gz";
  }else if(_is_file($dir_path.$theFileNameBase.".mzXML")){
    $tmp_new_file['fileName'] = $theFileNameBase.".mzXML";
    $tmp_new_file['type'] = "mzXML";
  }else{
    //need convert mgf to mzXML file
  }
  return $tmp_new_file;
}
/*
//-----------------------------------------------
function createDTA_gz_file($new_file, $dir_path){
//-----------------------------------------------

//convert fileName.dta folder then ziped to 
//fileName.dta.gz file for Sequest
  global $prohits_root;
  $removeMZXML = 0;
  $removeDir = 0; 
  $theFileNameBase = preg_replace("/[.]".$new_file['type']."$/i","", $new_file['fileName']);
  $tar_file_name = $theFileNameBase.".dta.tar.gz";
  $tmp_new_file = array();
  if(!_is_file($dir_path.$tar_file_name)){
    if(_is_file($dir_path.$new_file['fileName'])){
      if($new_file['type'] == 'mzXML' or $new_file['type'] == 'mzXML.gz'){
        $tar_file = mzXML2dtaTar($new_file['type'], $theFileNameBase, $dir_path);
        if($tar_file){
          $tmp_new_file['fileName'] = $tar_file;
          $tmp_new_file['type'] = "dta.tar.gz";
        }
      }else if(_is_file($dir_path.$theFileNameBase.".mzXML.gz")){
      //mgf file or other file.
      //mgf to dta file converter here. or send RAW file to converter
      //then converter mzXML.gz to dta file.
        $tar_file = mzXML2dtaTar("mzXML.gz", $theFileNameBase, $dir_path);
        if($tar_file){
          $tmp_new_file['fileName'] = $tar_file;
          $tmp_new_file['type'] = "dta.tar.gz";
        }
      }
    }
  }else{
    $tmp_new_file['fileName'] = $tar_file_name;
    $tmp_new_file['type'] = "dta.tar.gz";
  }
  if(!$tmp_new_file){
      writeLog("The ".$tar_file_name." cannot be created.");
  }
  return $tmp_new_file;

}
//----------------------------------------------------------
function mzXML2dtaTar($fileType, $fileNameBase, $dir_path){
  //use MzXML2Search from common/TPP, it can be other script.
  global $prohits_root;
  $removeMZXML = 0;
  $removeDir = 0;
  $rt = '';
  $output = array();
  if(_is_file($dir_path.$fileNameBase.".dta.tar.gz")){
    $rt = $dir_path.$fileNameBase.".dta.tar.gz";
  }else if($fileType == 'mzXML' or $fileType == 'mzXML.gz'){
    if($fileType == 'mzXML.gz' and !_is_file($dir_path.$fileNameBase.".mzXML")){
      $cmd = "gzip -dc ".$dir_path.$fileNameBase.".mzXML.gz" . " > ". $dir_path.$fileNameBase.".mzXML";
      prohits_exec($cmd);
      $removeMZXML = 1;
    }
    if(_is_file($dir_path.$fileNameBase.".mzXML")){
       $cmd =  $prohits_root."common/TPP/MzXML2Search -dta ". $dir_path.$fileNameBase.".mzXML";
       prohits_exec($cmd);
       if(!_is_dir($dir_path.$fileNameBase)){
        $msg = "the '$cmd' fail."; 
        writeLog($msg);
       }else{
          $removeDir = 1;
          $cmd = "cd $dir_path; tar -zcf ".$fileNameBase.".dta.tar.gz ". $fileNameBase;
          prohits_exec($cmd);
          if(_is_file($dir_path.$fileNameBase.".dta.tar.gz")){
            $rt = $dir_path.$fileNameBase.".dta.tar.gz";
          }else{
            writeLog("Cannot create ".$dir_path.$fileNameBase.".dta.tar.gz" );
          }
       }
    }
    if($removeMZXML) unlink($dir_path.$fileNameBase.".mzXML");
    if($removeDir) system("rm -rf ".$dir_path.$fileNameBase);
  }
  return $rt;
}
*/
//--------------------------
function prohits_exec($cmd){
//--------------------------
  exec("$cmd 2>&1", $output);
  if(DEBUG_SEARCH or DEBUG_CONVERTER){
    echo "\n$cmd\n";
  }
  if(isset($output[0])){
    if(preg_match("/not found|No such file or directory/", $output[0], $matches)){
     writeLog($output[0]);
    }
  }
}
//----------------------------------------------------------------------------------------
function get_new_converted_file_array($User_ID, $replace_existing, $raw_path='', $rawFile_ID, $rawConvert=array()){
//$replace_existing:
//0 = Don't convert if same parameter file exists
//1 = force to replace previous converted file
//2 = Make new
//3 = replace previous converted (base name) file, if not the same parameter file (iProphet, SWATH);
//----------------------------------------------------------------------------------------
  //always return array with status./existed/replaceIt/new/
  global $tableName;
  global $msManager_link;
  global $rawConvert_arr;
  
   
   
  if($rawConvert) $rawConvert_arr = $rawConvert;
  //iProphet file has to be base name file. if the parameter is not the same it will be replaced.
  if($replace_existing == 3){
     
    $old_file_path = get_baseName_converted_file_path($rawFile_ID, $rawConvert_arr['Format'], $tableName, $msManager_link, $rawConvert_arr['Parameter']);
    if($old_file_path){
     return $old_file_path;
    }
  }else if(!$replace_existing){
    //return last same parameter file.
    $old_file_path = get_existed_converted_file_path($rawFile_ID, $rawConvert_arr['Format'], $tableName, $msManager_link, $rawConvert_arr['Parameter']);
    if($old_file_path){
      $old_file_path['status'] = 'existed';
      return $old_file_path;
    }
  }else if($replace_existing == 1){
    //return owner's previous file
    $old_file_path = get_existed_converted_file_path($rawFile_ID, $rawConvert_arr['Format'], $tableName, $msManager_link, '', $User_ID );
     
    if($old_file_path and ($old_file_path['User']== $User_ID or !$old_file_path['User'])){
      $old_file_path['status'] = 'replaceIt';
      return $old_file_path;
    }
  }
  
  
  
  
  //no existing found, create new anyway or $replace_existing == 2
  if(!$raw_path){
    $raw_path =  getFilePath($tableName, $rawFile_ID);
  }
  
  $file_root = preg_replace("/\.\w+(\.gz)?$/i", "", $raw_path);
  $tmp_file_path = $file_root.".".$rawConvert_arr['Format'];
  
  if($replace_existing != 3){
    if(_is_file($tmp_file_path) or _is_file($tmp_file_path.".gz")){
      $find_file = 1;
      while($find_file){
        $tmp_file_path = $file_root.".prh$find_file.".$rawConvert_arr['Format'];
        $find_file++;
        if(!_is_file($tmp_file_path) and !_is_file($tmp_file_path.".gz")){
          $find_file = 0;
        }
      }
    }
  }
  
  $old_file_path['ID'] = '';
  $old_file_path['path'] = $tmp_file_path;
  $old_file_path['User_ID'] = $User_ID;
  $old_file_path['status'] = 'new';
  $old_file_path['FileName'] = basename($tmp_file_path);
   
  return $old_file_path;
}
//-------------------------------------------------------------------------------------------------------------
function get_existed_converted_file_path($ID, $fileType, $tableName, $msManager_link='', $parameter='', $User_ID=''){
//-------------------------------------------------------------------------------------------------------------
  //if !$parameter: return last record.
  //if $parameter: return same parameter last record 
  //and if $User_ID: return user's record or return last record if the last has no User.
  $search = array('/[ ]*--\S*64/', '/[ ]*--\S*32/', '/[ ]*\/doubleprecision/', '/[ ]*\/singleprecision/');
  $replace = array('', '', '','');
  $parameter = preg_replace($search, $replace, $parameter);
  
  $rt = array();
  $SQL = "select ID, FileName, FileType, FolderID, User, ConvertParameter from $tableName where RAW_ID='".$ID."' order by ID desc";
  
  //echo "\n$fileType=$SQL\n";exit; 
  if(!$msManager_link) global $msManager_link;
  $results = mysqli_query($msManager_link, $SQL);
  
  while($row = mysqli_fetch_array($results)){
    
    if(strtoupper($row['FileType'])== strtoupper($fileType) or strtoupper($row['FileType'])== strtoupper($fileType.".gz")){
      $row['ConvertParameter'] = preg_replace($search, $replace, $row['ConvertParameter']);
      if(!$parameter or (trim($row['ConvertParameter']) == trim($parameter))){
        $row['path'] = getFilePath($tableName, $row['ID'], 'full', $msManager_link);
        return $row; 
      }
    }
  }
  
  if(!$rt){
    $SQL = "select RAW_ID from $tableName where ID='".$ID."'";
   
    if(!$msManager_link) global $msManager_link;
    $results = mysqli_query($msManager_link, $SQL);
    $row = mysqli_fetch_array($results);
    if(isset($row[0]) and $row[0]){
      $SQL = "select ID, FileName, FileType, FolderID, User, ConvertParameter from $tableName where RAW_ID='".$row[0]."' or ID='".$row[0]."' order by ID desc";
       
      $results = mysqli_query($msManager_link, $SQL);
    }else{
      $SQL = "select ID, FileName, FileType, FolderID, User, ConvertParameter from $tableName where ID='".$ID."'";
      $results = mysqli_query($msManager_link, $SQL);
      //echo $SQL;exit;
    }
    while($row = mysqli_fetch_array($results)){
      if(strtoupper($row['FileType'])== strtoupper($fileType) or strtoupper($row['FileType'])== strtoupper($fileType.".gz")){
        $row['ConvertParameter'] = preg_replace($search, $replace, $row['ConvertParameter']);
        if(!$parameter or !$row['ConvertParameter'] or ($parameter and trim($row['ConvertParameter']) == trim($parameter))){
          $row['path'] = getFilePath($tableName, $row['ID'], 'full', $msManager_link);
          return $row;
        }
      }
    }
  }
  return $rt;
}
//----------------------------------------------------------------------------------------------------
function get_baseName_converted_file_path($ID, $fileType, $tableName, $msManager_link='', $parameter){
// this function is used for iProphet converted file.
//----------------------------------------------------------------------------------------------------
  $rt = array();
  $file_baseName = '';
  $file_RAW_ID = $ID;
  if(!$msManager_link) global $msManager_link;
  $SQL = "select FileName, RAW_ID from $tableName where ID='".$ID."'";
  //echo "$SQL\n";
  
  $results = mysqli_query($msManager_link, $SQL);
  $find_raw_id = "RAW_ID='$file_RAW_ID'";
  if($row = mysqli_fetch_array($results)){
    $file_baseName = preg_replace('/\.\w+(\.gz)*$/', "", $row['FileName']);
    if($row['RAW_ID']){
      $file_RAW_ID = $row['RAW_ID'];
      $find_raw_id = "RAW_ID in ($ID, $file_RAW_ID)";
    }
  }
   
  $SQL = "select ID, FileName, FileType, FolderID, User, ConvertParameter, RAW_ID from $tableName where $find_raw_id order by ID desc";
  //echo "$SQL\n";
  $results = mysqli_query($msManager_link, $SQL);
  while($row = mysqli_fetch_array($results)){
    
      if(preg_replace('/(_I)*\.\w+(\.gz)*$/', "", $row['FileName']) == $file_baseName){
        $rt['path'] = getFilePath($tableName, $row['ID'], 'full', $msManager_link);
        $rt['User_ID'] = $row['User'];
        $rt['ID'] = $row['ID'];
        $rt['RAW_ID'] = $row['RAW_ID'];
        $rt['FileName'] = $row['FileName'];
        $rt['FileType'] = $row['FileType'];
        $rt['ConvertParameter'] = $row['ConvertParameter'];
        
        if(strtoupper($row['FileType'])== strtoupper($fileType) or strtoupper($row['FileType'])== strtoupper($fileType.".gz")){
          if($row['ConvertParameter'] == $parameter){
            $rt['status'] = 'existed';
          }else{
            $rt['status'] = 'replaceIt';
          }
          return $rt;
        }else if($row['ConvertParameter'] == $parameter and preg_match("/mzML|mzXML/", $row['FileType'], $matches)){
          $rt['status'] = 'other_format';
        }
      }
    
  }
   
  return $rt;
}
//-----------------------------------------------------------------
function saveConvertedFile2db($converted_file, $rawFileRow, $ID=0){
//-----------------------------------------------------------------
  global $tableName;
  global $msManager_link;
  global $rawConvert_arr;
  global $proDBarr;
  global $HitDB_links;
  global $HITS_DB;
  
  if(!$rawFileRow and $ID){
    $SQL = "select ID, FileName, FileType, FolderID, User, ProhitsID, ProjectID from $tableName where ID='".$ID."'";
    if(!$rawFileRow = mysqli_fetch_assoc(mysqli_query($msManager_link, $SQL))){
     return;
    }
  }
  $converted_all[] = array('Path'=>$converted_file['Path'], 'Name'=>$converted_file['Name'], 'Format'=>$converted_file['Format']);
  if(isset($converted_file['Path_2'])){
    $converted_all[] = array('Path'=>$converted_file['Path_2'], 'Name'=>$converted_file['Name_2'], 'Format'=>$converted_file['Format_2']);
  }
  if(isset($converted_file['Path_3'])){
    $converted_all[] = array('Path'=>$converted_file['Path_3'], 'Name'=>$converted_file['Name_3'], 'Format'=>$converted_file['Format_3']);
  }
  //print_r($converted_file);
  //print_r($converted_all);exit;
   
  foreach($converted_all as $converted_one){
  
    $size = _filesize($converted_one['Path']);
    $timeInt = _filemtime($converted_one['Path']);
    $date = @date("Y-m-d G:i:s",$timeInt);
    $SQL = "set FileName='".$converted_one['Name']."', 
            FileType='".$converted_one['Format']."', 
            FolderID='".$rawFileRow['FolderID']."', 
            Date='$date', 
            User='".$rawFileRow['User']."',
            ProhitsID='".$rawFileRow['ProhitsID']."', 
            ProjectID='".$rawFileRow['ProjectID']."', 
            Size='$size',
            ConvertParameter='". $converted_file['parameter']."', 
            RAW_ID='".$rawFileRow['ID']."'";
            
    $tmpSQL = "select ID from $tableName 
            where FileName='".$converted_one['Name']."' and  
            FileType='".$converted_one['Format']."' and 
            FolderID='".$rawFileRow['FolderID']."'";
     
    if($row = mysqli_fetch_row(mysqli_query($msManager_link, $tmpSQL))){
      $ID = $row[0];
      $SQL = "update $tableName  set Size='$size', Date='$date',
              ConvertParameter='". $converted_file['parameter']."', 
              RAW_ID='".$rawFileRow['ID']."' where ID='$ID'";
     
      mysqli_query($msManager_link, $SQL);
    }else{
      $SQL = "insert into $tableName ".$SQL;
      mysqli_query($msManager_link, $SQL);
      $ID = mysqli_insert_id($msManager_link);
      if(!$ID){
        echo mysqli_error($msManager_link);
      }
      
      if($rawFileRow['ProhitsID'] and $rawFileRow['ProjectID'] and $ID){
        //update band in hitsDB
        if($proDBarr and $HitDB_links){
          $dbName = $proDBarr[$rawFileRow['ProjectID']];
          $DBlink = $HitDB_links[$dbName];
        }else{
          $prohitsDB = new mysqlDB(PROHITS_DB);
          $SQL = "SELECT ID, DBname FROM Projects where ID='".$rawFileRow['ProjectID']."'";
          $theProject = $prohitsDB->fetch($SQL);
          $prohitsDB->change_db($HITS_DB[$theProject['DBname']]);
          $DBlink = $prohitsDB->link;
        }
        $SQL = "select ID, RawFile from Band where ID='".$rawFileRow['ProhitsID']."' and ProjectID='".$rawFileRow['ProjectID']."'"; 
        $result = mysqli_query($DBlink, $SQL);
        if($row = mysqli_fetch_row($result)){
          $tmp_rawFile = $row[1];
          //updata Analyst/Band table
          $pt = "$tableName:$ID";
          $SQL = "update Band set RawFile=";
          if($tmp_rawFile){
            $SQL .= "'$tmp_rawFile;$pt' where ID='".$rawFileRow['ProhitsID']."'";
          }else{
            $SQL .= "'$pt' where ID='".$rawFileRow['ProhitsID']."'";
          }
          mysqli_query($DBlink, $SQL);
        }
      }
      
    }
    
  }
}
//------------------------------
function getRawFileDir($dirID){
//------------------------------
  global $tableName;
  $path = getFilePath($tableName, $dirID);
  if($path) $path = end_with_slash($path);
  return $path;
}
//---------------------------------------------------------
function getFileDirPath($tableName, $fileID='', $folderID=''){
//---------------------------------------------------------
  global $folder_path_arr;
  global $OLD_STORAGE_FOLDERS;
  if(!$fileID) return '';
  //if(isset($folder_path_arr[$folderID])) return $folder_path_arr[$folderID];
  if(isset($folder_path_arr[$folderID]) and !isset($OLD_STORAGE_FOLDERS)) return $folder_path_arr[$folderID];
  $path = dirname(getFilePath($tableName, $fileID))."/";
  if($folderID){
    $folder_path_arr[$folderID] = $path;
  }
  return $path;
}
//----------------------------------------------------------------
function getFilePath($tableName, $ID, $type='full', $msdb_link=0){
//----------------------------------------------------------------
  global $msManager_link;
  global $OLD_STORAGE_FOLDERS;
  if(!$msdb_link)  $msdb_link = $msManager_link;
  $path = '';
  while($ID){
    $SQL = "select ID, FileName, FolderID from $tableName where ID='$ID'";
    if($row = mysqli_fetch_assoc(mysqli_query($msdb_link, $SQL))){
      $ID = $row['FolderID'];
      $path = "/" . $row['FileName'] . $path;
    }else{
      $ID = 0;
    }
  }
  if($type == 'full'){
    $dir_header = end_with_slash(STORAGE_FOLDER);
    $full_path = $dir_header . $tableName . $path;
    if(!file_exists($full_path) and isset($OLD_STORAGE_FOLDERS)){
      foreach($OLD_STORAGE_FOLDERS as $TMP_STORAGE_FOLDER){
        $dir_header = end_with_slash($TMP_STORAGE_FOLDER);
        $full_path = $dir_header . $tableName . $path;
        if(file_exists($full_path)){
          break;
        }
      }
    }
    $path = $full_path;
  }
  return $path;
}
//-------------------------------
function end_with_slash($dir=''){
//-------------------------------
  if($dir){
    if(!preg_match("/\/$/", $dir, $matches)){
      $dir .= "/";
    }
  }
  return $dir;
}
/*******************************************
check if the download package setup
$DOWNLOAD_PACKAGE_FOLDER is defined in conf file
*******************************************/
function check_download_package_folder(){
  global $PHP_SELF;
  global $DOWNLOAD_SHARED_FOLDER;
  global $process_storage_folder_url;
  global $storage_ip;
  $rt = array('activated'=>'', 'error'=>'', 'path'=>'');
   
  if( $_SERVER['HTTP_HOST'] == $storage_ip){
    if(is_writable($DOWNLOAD_SHARED_FOLDER['SOURCE'])){
      $rt['activated']= true;
      $rt['path']= $DOWNLOAD_SHARED_FOLDER;
    }
     
    return $rt;
  }
  check_prohits_web_root();
  if(isset($DOWNLOAD_SHARED_FOLDER['SOURCE']) and $DOWNLOAD_SHARED_FOLDER['SOURCE']){
    $path = add_folder_backslash($DOWNLOAD_SHARED_FOLDER['SOURCE']);
    $action = '&action=isEmpty_or_unexist';
    $url = $process_storage_folder_url.$path.$action;
     
    $tmp_arr = file($url);
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
function check_user_raw_export_folder($userName){
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
function check_user_tmp_folder($userName){
  $user_tmp_dir = '';
  $prohits_root = pathinfo(__FILE__,PATHINFO_DIRNAME);
  $prohits_root = str_replace("msManager","",dirname($prohits_root));
  $user_tmp_dir = end_with_slash($prohits_root) . "TMP/$userName/";
  if(!_is_dir($user_tmp_dir )){
    if(!mkdir ( $user_tmp_dir, 0777, true)){
      echo "Apache user cannot create tmp folder ". $user_tmp_dir . ". Please contact Prohits admin.";exit;
    }
  }
  return $user_tmp_dir;
}
/******************************************
download search results file from remote computer
*******************************************/
function download_search_result($searchEngine, $tableName, $taskID, $ID, $download_file_to='', $server_name=''){
  global $gpm_ip;
  global  $msManager_link;
  if($server_name){
    $PROHITS_IP = $server_name;
  }else{
    $PROHITS_IP = $_SERVER['SERVER_NAME'];
  }
  //$gpm_ip = GPM_IP;
  if($gpm_ip=='localhost') $gpm_ip = $PROHITS_IP;


  $newName = '';
  $download_this_file = '';
  if(strpos($searchEngine, "TPP") === 0 ){
     
    list($TPP_file, $TPP_searchEngine) = explode("_", $searchEngine);
    $SQL = "Select WellID, TppTaskID, SearchEngine, pepXML, protXML, ProhitsID, ProjectID from " . $tableName."tppResults Where 
    WellID='".$ID."' and 
    TppTaskID='".$taskID."' and 
    SearchEngine='".$TPP_searchEngine."'";
     
    $record= mysqli_fetch_assoc(mysqli_query($msManager_link, $SQL));
    //echo "$SQL\n";
    if($TPP_file == 'TPPprot'){
      $download_this_file = $record['protXML'];
    }else{
      $download_this_file = $record['pepXML'];
    }
    $url = "http://".$gpm_ip.GPM_CGI_DIR."/Prohits_TPP.pl";
    $postData = "tpp_myaction=downloadTppXML&fileName=".$download_this_file;
    
    $newName = basename($download_this_file);  
       
  }else{
    $SQL = "SELECT  FileName, DataFiles FROM $tableName T, $tableName"."SearchResults S where S.WellID=T.ID and WellID=$ID and TaskID='". $taskID."' and SearchEngines='".$searchEngine."'";
    $record= mysqli_fetch_assoc(mysqli_query($msManager_link, $SQL));
    $download_this_file = $record['DataFiles'];
    
    if($searchEngine=='Mascot'){
      $mascot_IP = MASCOT_IP;
      if(defined('MASCOT_IP_OLD') and preg_match("/^\w/", $record['DataFiles'], $matches)){
        $mascot_IP = MASCOT_IP_OLD;
      }
      $dat_file_name = substr($record['FileName'], 0, strrpos($record['FileName'], '.')); 
      $url = "http://".$mascot_IP.MASCOT_CGI_DIR."/ProhitsMascotParser.pl";
      $postData = "theaction=download&file=".$record['DataFiles']."&newName=".$dat_file_name.".dat";
      $newName = $dat_file_name.".dat";
      
    }else if($searchEngine=='GPM'){
      $url = "http://".$gpm_ip.GPM_CGI_DIR."/Prohits_TPP.pl";
      $postData = "tpp_myaction=downloadTppXML&fileName=..".$record['DataFiles'];
      $newName = basename($record['DataFiles']);
    }else if($searchEngine=='SEQUEST'){
    
      $url = "http://".SEQUEST_IP.SEQUEST_CGI_DIR."/Prohits_SEQUEST.pl";
      $postData = "SEQUEST_myaction=download&type=out&dir=".$record['DataFiles'];
      $newName = basename($record['DataFiles']);
    }else{
      $newName = basename($record['DataFiles']);
    }
  }
  if($download_file_to){ 
    $cmd = '';
    if(!_is_file("$download_file_to/$newName")){
      if(is_in_local_server('TPP') and $searchEngine !='Mascot'){
        if(_is_file($download_this_file)){
          $cmd = "ln -s '$download_this_file' '$download_file_to/$newName'";
        }else{
          echo "the file doesn't exist: $download_this_file.\n";
          return '';
        }
      }else{
      //echo "=$download_file_to/$newName=";exit;
        $cmd = "wget --directory-prefix=\"$download_file_to\"  --post-data=\"$postData\"";
        if($newName) $cmd .= " --output-document=\"$download_file_to/$newName\"";
        $cmd .= " ". $url;
      }
      $cmd .= " >> ". $download_file_to."/wget.log 2>&1";
       
      echo $cmd."<br>\n";
      exec($cmd, $outputs);
      //print_r($outputs);
      if(_is_file("$download_file_to/$newName") and filesize("$download_file_to/$newName") < 1000){
        system("cat \"$download_file_to/$newName\" > ".$download_file_to."/wget.log");
        return '';
      }
    } 
    return $newName;
  }else{
    return $url . "?". $postData;
  }
}
/************************************
return dir file as array tree
*************************************/
function getFileListFromDir($dir,  $extension='', $tree=false, $prefix='') {

  $files = array();
  if ($handle = @opendir($dir)) {
    while (false !== ($file = readdir($handle))) {
      if ($file != "." && $file != "..") {
          if(is_dir($dir.'/'.$file)) {
              if(!$tree) continue;
              $dir2 = $dir.'/'.$file;
              $files[] = array ('dirName'=>$file, 'fileList'=>getFileListFromDir($dir2, $extension, $tree, $prefix));
          }else {
            if($prefix){
              if(strpos($file, $prefix) === 0){
                $files[] = $file;
              }
            }if($extension){
              if(pathinfo($file, PATHINFO_EXTENSION) == $extension){
                 $files[] = $file;
              }
            }else{
              $files[] = $file;
            }
         }
      }
    }
    closedir($handle);
  }

  return $files;
}
/*******************************
ftp/sftp connection check
********************************/
function check_connection ($username = '', $password ='', $ip ='', $type=''){
  global $sftp;
  global $ftp_conn_id;
  if( $type == 'sftp'){
    if($sftp = new Net_SFTP($ip,22,5)){
      if (@$sftp->login($username, $password)) {
        return true;
      }
    }
  }else if( $type == 'ftp'){
    if($ftp_conn_id = ftp_connect($ip)){
      if (ftp_login($ftp_conn_id, $username, $password)) {
        ftp_pasv($ftp_conn_id, true);
        return true;
      }
    }
  }
  return false;
}
/*******************************
sftp connection login.
********************************/
function sftp_connection ($username = '', $password ='', $ip =''){
  $sftp = new Net_SFTP($ip,22,5);
  if ($sftp->login($username, $password)) {
     return $sftp;
  }
  return false;
}
/*******************************
sftp connection login.
********************************/
function ftp_connection ($username = '', $password ='', $ip =''){
  $rt =  false;
  if($ftp_conn_id = ftp_connect($ip)){
    if (@ftp_login($ftp_conn_id, $username, $password)) {
      $rt = $ftp_conn_id;
    }
  }
  return $rt;
}
function is_64bit() {
  $int = "9223372036854775807";
  $int = intval($int);
  if ($int == 9223372036854775807) {
    /* 64bit */
    return 1;
  }
  elseif ($int == 2147483647) {
    /* 32bit */
    return 0;
  }
  else {
    /* error */
    return 2;
  } 
}
//*******************************
function get_search_task($theTaskID){
//********************************
  global $msManager_link;
  global $tableName;
  $taskTable = $tableName . "SearchTasks";
  $SQL = "SELECT ID, PlateID, DataFileFormat, SearchEngines, Parameters, TaskName, LCQfilter, Schedule, StartTime, 
        AutoAddFile, RunTPP, Status, ProcessID, UserID, ProjectID
        FROM ". $taskTable." where ";
  if($theTaskID){
    $SQL .= "ID='$theTaskID'";
  }else{
    $SQL .="Status='Running'  or Status='Waiting' order by ID";
  }
  
  $result = mysqli_query($msManager_link, $SQL);
  $theTask_arr = mysqli_fetch_array($result);
  return $theTask_arr;
}
function get_local_PWIZ_path(){
  $prohits_path = str_replace("msManager/autoBackup/shell_functions.inc.php", "", __FILE__);
  
  $pwiz_path = $prohits_path."EXT/pwiz-bin/";

  $cmd = $pwiz_path."msconvert";
  exec("$cmd 2>&1", $output);
  foreach($output as $line){
    if(strpos($line, 'Usage:') !== false){
      return $pwiz_path;
    }
  }
  return false;
}
?>