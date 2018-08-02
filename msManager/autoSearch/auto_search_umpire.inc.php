<?php
function runDIAUmpire($file_path, $WellID, $DIAUmpire_parameters, $converted_file, $theTaskID=''){
//function mzmlExistTPPserver($tableName,$rawFile_arr, $schTaskID='', $type=''){
  
  global $managerDB;
  global $msManager_link;
  global $task_infor;
  global $frm_theURL;
  
  global $theTask_arr;
  global $tableName;
  global $resultTable;
  global $searchEngine_arr;
  global $tmp_Engine;
  global $linked_raw_file_path;
  global $gpm_ip;
  global $tpp_ip;
  //if it will run DIAUmpireSE locally
   
  $use_memery_size = '30G';
  if(defined("JAR_MAX_MEMORY") and JAR_MAX_MEMORY){
    $use_memery_size = JAR_MAX_MEMORY;
  }
  $JAR_Command = get_jar_command($use_memery_size);
   
  
  
  
  $DIAUmpire_has_been_run = false;
  
  $http_gpm_cgi_dir = "http://" . $gpm_ip . GPM_CGI_DIR;
  $tpp_formaction = $http_gpm_cgi_dir . "/Prohits_TPP.pl";
  $DIAUmpire_in_prohits = false;
  
  $allSwathFileAdded = '';
  if(strpos($DIAUmpire_parameters, "allSwathFileAdded")!== false){
    $allSwathFileAdded = 'Yes';
    $DIAUmpire_parameters = preg_replace("/allSwathFileAdded:\w*;/", '', $DIAUmpire_parameters);
  }
  
  $DIAUmpire_in_prohits = is_in_local_server('DIAUmpire');
  
  
  //run DIA-Umpire in XTandem server
  if(!$DIAUmpire_in_prohits){
    $error_msg =  "error: please check DIAUMPIRE_BIN_PATH in Prohits onf file.";
    writeLog($error_msg);
    exit;
  }
  $task_infor = prepare_run_search_on_local($tableName, $WellID, $theTaskID, $file_path, 'DIAUmpireSE');
  $linked_raw_file_path = $task_infor['linked_raw_file_path'];
  print_r($task_infor);
  //exit;
   
  
  $mzDirPath = $task_infor['taskDir'];
  $tasklog = $task_infor['tasklog'];
  $taskComFile  = $task_infor['taskDir']."/task.commands";
  $file_base_path =  $task_infor['resultFilePath'];
  
  $error_msg = '';
  if(defined("PROTEOWIZARD_BIN_PATH") and _is_file(PROTEOWIZARD_BIN_PATH."/msconvert")){
    $msconvert = PROTEOWIZARD_BIN_PATH."/msconvert";
  }else if(defined("TPP_BIN_PATH") and _is_file(TPP_BIN_PATH."/msconvert")){
    $msconvert = TPP_BIN_PATH."/msconvert";
  }else{
    $error_msg =  "error: msconvert not fond. Please install proteowizard linux version in Prohits/EXT/pwiz-bin/. Then define 'PROTEOWIZARD_BIN_PATH' in Prohits conf file.";
    writeLog($error_msg);
    exit;
  }
  //it will check if the file has been run UmpireSE with the same parameters.
  $DIAUmpire_has_run = createDIAUmpireParamFile($task_infor['mzDirPath'], $DIAUmpire_parameters, $task_infor['paramFilePath']);
  echo "The DIA file has run:$DIAUmpire_has_run\n";
  
  
   
  $umpire_command = $JAR_Command . DIAUMPIRE_BIN_PATH . "/DIA_Umpire_SE.jar";
  $is_new_mgf = false;
  if(!$DIAUmpire_has_run){
    $is_new_mgf = true;
    print "run DIA-Umpire SE\n";
    $ok = make_SE_command_file($umpire_command, $msconvert, $task_infor);
    
    if($ok){
      run_search_on_local($task_infor['taskComFile'], $task_infor);
    }else{
      writeLog("DIAUmpire error: cannot create ".$task_infor['taskComFile']);
    }
  }else{
    writeLog("Previouse DIAUmpireSE results '$file_base_path' will be used.");
  }
  
  //echo "DIAUmpire SE done\n";exit;
  $outfile_base = preg_replace("/\.mzXML$/", "", $task_infor['linked_raw_file_path']);
  if(_is_file($outfile_base."_Q1.mzXML") and _is_file($outfile_base."_Q2.mzXML") and _is_file($outfile_base."_Q3.mzXML")){
    writeLog("DIAUmpire SE has been successfully run ".$task_infor['mzDirPath']);
    return true;
  }else{
    writeLog("DIAUmpire SE error : Please check log file for detail ".$task_infor['mzDirPath']."/task.log");
    return false;
  }
}
//-----------------------------------------------------------
function make_SE_command_file($umpire_command, $msconvert, $task_infor){
//----------------------------------------------------------
  $rt = false;
  $raw_file_tmp_path = '';
  $mzDirPath = $task_infor['mzDirPath']; //file ID added as prefix.
  $cmd_file = $task_infor['taskComFile'];
  $mzFileNameBase = basename($mzDirPath);
  
  $to_file = "#!/bin/bash\n\n";
  $to_file .= "echo `hostname`\n";
  $to_file .= "echo \"#. cd to working dir: $mzDirPath\"\n";
  $to_file .= 'cd '. escapeshellarg($mzDirPath)."\n";
  $to_file .= "ls -1 | grep -E -v 'diaumpire.se_params|commands' | xargs rm -fr\n";
  if($task_infor['link_from_tmp_dir']){
    $raw_file_tmp_path = $task_infor['link_from_tmp_dir'].$task_infor['prohits_mzML_fileName'];
    $to_file .="if [ ! -d '".$task_infor['link_from_tmp_dir']."' ]\n"; 
    $to_file .="then\n"; 
    $to_file .="\tmkdir -p -m 775 ".$task_infor['link_from_tmp_dir']."\n";
    $to_file .="else\n"; 
    $to_file .="\trm -rf ".$task_infor['link_from_tmp_dir']."*\n";
    $to_file .="fi\n";
    
    $to_file .="cp '". $task_infor['raw_file_path']."' '$raw_file_tmp_path'\n";
    $to_file .="ln -sf '$raw_file_tmp_path'". " '" .$task_infor['linked_raw_file_path']."'\n";     
  }
  $to_file .= $umpire_command . " '".$task_infor['linked_raw_file_path']."' '".$task_infor['paramFilePath']."'\n";
  $to_file .="if [ -f '".$mzFileNameBase."_Q1.mgf' ] && [ -f '".$mzFileNameBase."_Q2.mgf' ] && [ -f '".$mzFileNameBase."_Q3.mgf' ]\n";
  $to_file .="then\n"; 
  $to_file .="\t".$msconvert . " --mzXML --32 --mz32 '". $mzFileNameBase."'*.mgf -o '".$mzDirPath."' 2>&1\n";
  $to_file .="else\n"; 
  $to_file .="\techo 'DIAUmpire error: no all 3 mgf files are created'\n";
  $to_file .="\texit\n";
  $to_file .="fi\n";
  
   
  $to_file .="if [ -f '".$mzFileNameBase."_Q1.mzXML' ] && [ -f '".$mzFileNameBase."_Q2.mzXML' ] && [ -f '".$mzFileNameBase."_Q3.mzXML' ]\n";
  $to_file .="then\n"; 
  $to_file .="\techo " . "'DIAUmpire SE has been successfully run: $mzDirPath'\n";
  $to_file .="else\n"; 
  $to_file .="\techo 'mgf file cannot be converted to mzXML'\n";
  $to_file .="\texit\n";
  $to_file .="fi\n";
  if($raw_file_tmp_path){
    $to_file .="rm '".$task_infor['linked_raw_file_path']."'\n";
    $to_file .="touch '".$task_infor['linked_raw_file_path']."'\n";    
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
//-----------------------------------------------------------------
function createDIAUmpireParamFile($mzDirPath, $parameters, $paramFilePath){
//craete parameter file to run DIAUmpire locally.
//return true if SE has run before.
//-----------------------------------------------------------------
  if(defined("NUM_THREAD") and NUM_THREAD){
    $use_threads = NUM_THREAD;
  }else{
    $use_threads = 20;
  }
  $mzFileNameBase = basename($mzDirPath);
  $paramFilePath_tmp = $paramFilePath . "_tmp";
  if(!_is_file($paramFilePath)){
     $paramFilePath_tmp = $paramFilePath;
  }
  print "parameter file: $paramFilePath_tmp\n";
  $fp = fopen($paramFilePath_tmp, 'w');
  
  $to_file = "#No of threads\r\n";
  if($use_threads){
    $to_file .= "Thread = $use_threads\r\n";
  }else{
    $to_file .= "Thread = 10\r\n";
  }
  $to_file .= "AdjustFragIntensity = true\r\n";
  $to_file .= "BoostComplementaryIon = true\r\n";
  $to_file .= "ExportPrecursorPeak = false\r\n";
  $to_file .= "ExportFragmentPeak = false\r\n";

  
  
  $tmp_param_arr = explode(";", $parameters);
  foreach  ($tmp_param_arr as $thePara) {
    $thePara = trim($thePara);
    if(!$thePara) continue;
    
    list($tmp_name, $value) = explode(":", $thePara, 2);
    $tmp_name = preg_replace("/^dia_/", '', $tmp_name);
    $tmp_name =preg_replace("/^para_/", 'SE.', $tmp_name);
    $tmp_name = trim($tmp_name);
    if(!$tmp_name) continue;
    if($tmp_name == 'SWATH_window_setting'){
      $value = str_replace(" ", "\t", $value);
      $to_file .= "==window setting begin\r\n";
      $to_file .= $value. "\r\n";
      $to_file .= "==window setting end\r\n";
    }else{
      $to_file .= $tmp_name . " = " . $value ."\r\n";
    }
  }
  fwrite($fp, $to_file);
  fclose($fp);
   
  if($paramFilePath_tmp != $paramFilePath){
    $output = `diff $paramFilePath_tmp $paramFilePath`;
    
    if(!$output){
      print "It is the same parameter file\n";
      $file_base_path = $mzDirPath."/".$mzFileNameBase;
      if(_is_file( $file_base_path."_Q1.mgf") && _is_file( $file_base_path."_Q2.mgf") && _is_file( $file_base_path."_Q3.mgf")){ 
        print "Previous SE results will be used.\n";
        return 1;
      }
    }else{
      system("mv '$paramFilePath' '$paramFilePath"."_old'; mv '$paramFilePath_tmp' '$paramFilePath'");
      print "remove all old files\n";
      
    }
  }
  
  return 0;
}
//-----------------------------------------------------------------
function runDIAUmpire_Quant($umpireQUANT_ID, $frm_machine, $frm_SearchEngine, $frm_selected_list_str, $saint_option, $UmpireQuant_option, $control_id_str, $saint_bait_name_str='', $mapDIA_option, $SAINT_or_mapDIA, $REMOVE_SHARED_PEPTIDE_GENE='', $ParentQuantID=''){
// return $process_info=array('ProcessID'=>'','Status'=>'','msg'=>'')
// it will be called from /analyst/DIAUmpire_Quant_run.php
////$frm_selected_list_str= TaskID|RawFileID|sampleID, 
//-----------------------------------------------------------------
  $ProcessID='';
  $Status='Error';
  $msg = '';
  
  $searchTaskID_arr = array();
  global $logfile;
  global $search_task_arr;
  global $managerDB;
  global $msManager_link;
  
  global $tpp_formaction;
  global $tpp_in_prohits;
  global $is_SWATH_file;
  global $gpm_ip;
  
  $engine_str = '';
  $Dababase_name = '';
  $searchID_tppID_str = '';
  $taskResultDir_prt = '';
  $all_task_file_path = '';
  
  $is_SWATH_file = 1;
  $http_gpm_cgi_dir = "http://" . $gpm_ip . GPM_CGI_DIR;
  $tpp_formaction = $http_gpm_cgi_dir . "/Prohits_TPP.pl";
  
  
   
  flush();
  $fileDirName_str = '';
  $lower_searchEngine = strtolower($frm_SearchEngine);
  
  $SearchResultsTable = $frm_machine."SearchResults";
  $SearchTaskTable = $frm_machine."SearchTasks";
  $tppTaskTable = $frm_machine."tppTasks";
  
   
  $task_id_arr = explode(",", $frm_selected_list_str);
   
  $tmp_task_id = 0;
  $DIAUmpire_in_prohits = is_in_local_server('DIAUmpire');  
  $tpp_in_prohits = $DIAUmpire_in_prohits;
   
  if($ParentQuantID) {
    $GPM_datapath_prt = get_local_gpm_archive_path($frm_machine, $ParentQuantID, 'umpireQUANT');
    $rawDir_prt = $GPM_datapath_prt ."/". $frm_machine;
    $taskDir_prt = $rawDir_prt . "/DIAUmpire_Quant_Tasks/task". $ParentQuantID;
    $taskResultDir_prt = $taskDir_prt."/Results";
    echo "\nParent Task results dir = $taskResultDir_prt\n";
    
  }else{
    //only for new Quant task not re-sun Saint/mapDIA
    $msg = "1. Check mzXML in task folder";
    writeLog($msg, $logfile);
     
    foreach($task_id_arr as $value){
      list($taskID, $fileID, $sampleID) = explode("|", $value);
      //check if mzXML is in Umpire server.
      if($tmp_task_id != $taskID){
        $tmp_task_id = $taskID;
        $search_task_arr = get_search_task($taskID);
      }
      $SQL = "SELECT ID, FileName,FileType, FolderID,ConvertParameter,RAW_ID  from ".$frm_machine." where ID=$fileID" ;
      $rawFile_arr = $managerDB->fetch($SQL);
      
      //print_r($search_task_arr);
      //print_r($rawFile_arr);
      
      //this functoin is in tpp_task_shell_fun.inc.php
      mzmlExistTPPserver($frm_machine,$rawFile_arr, $taskID, $type='mzXML', 'DIAUmpireQuant');
      
      if(!mysqli_ping($msManager_link)) {
        $managerDB = new mysqlDB(MANAGER_DB);
        $msManager_link  = $managerDB->link;
        mysqli_query($msManager_link, "SET SESSION sql_mode = ''");
      }
      
      
      $fileID_arr[$taskID][] = $fileID;
      if(!in_array($taskID, $searchTaskID_arr)){
        $searchTaskID_arr[] = $taskID;
      }
    }
    
    $msg = "2. Get selected search results file locations.";
    writeLog($msg, $logfile);
    
    $searchID_tppID_str = '';
    $engine_str = '';
    $Dababase_name = '';
    if($frm_SearchEngine == 'iProphet'){
      $taskId_str = implode(",", $searchTaskID_arr);
      $SQL = "select SearchEngines from $SearchTaskTable where ID in($taskId_str)";
      
      $results = mysqli_query($managerDB->link, $SQL); 
      while($row = mysqli_fetch_row($results)){
        $tmp_engine_arr = explode(";", $row[0]);
        foreach($tmp_engine_arr as $value){
          $tmp_arr = explode("=", $value);
          if($tmp_arr[0] != 'Converter' and  $tmp_arr[0] != 'Database' and $tmp_arr[0] != 'DIAUmpire' and $tmp_arr[0] != 'MSPLIT'){
            if($engine_str) $engine_str .= ":";
            $engine_str .= strtolower($tmp_arr[0]);
          }else if($tmp_arr[0] == 'Database' and isset($tmp_arr[1])){
            $Dababase_name = trim($tmp_arr[1]);
          }
        }
      }
       
      $SQL = "select ID, SearchTaskID from $tppTaskTable where SearchTaskID in($taskId_str)";
      echo "$SQL\n";
      $results = mysqli_query($managerDB->link, $SQL); 
      while($row = mysqli_fetch_row($results)){
        if($searchID_tppID_str) $searchID_tppID_str .=':';
        $searchID_tppID_str .= $row[1] ."," . $row[0];
      }
    }
     
    
    foreach($fileID_arr as $taskID => $IDs){
      $id_str = implode(",", $IDs);
      $SQL = "select ID, FileName from $frm_machine where ID in($id_str)";
       
      $results = mysqli_query($managerDB->link, $SQL);
      while($row = mysqli_fetch_row($results)){
        $fileDirName_str .= $taskID."|".$row[0]."|". $row[1]."|$lower_searchEngine:";
        
        
      }
    }
  }
  $msg = "3. Submit reuqest form to DIA-Umpire server";
  writeLog($msg, $logfile); 
  
  //echo "$umpireQUANT_ID, $frm_machine, $frm_SearchEngine, $frm_selected_list_str, $saint_option";exit;
  
  
  //$searchID_tppID_str = TPPTaskID,SearchTaskID:..
  //$frm_selected_list_str= SearchTaskID|RawFileID|sampleID, ...
  //$fileDirName_str = SearchTaskID|rawFileID|rawFileName|searchEngine:..
  //$saint_bait_name_str = fileID|uerInputedName,..
  //$frm_machine =  the engine results will run Quant,
  //$engine_str = all engines from iProphet.
  
  
  
  $DIAUmpire_in_prohits = is_in_local_server('DIAUmpire');   
   
  //run DIA-Umpire Quant in XTandem server
  if($DIAUmpire_in_prohits){
    echo "\nRun DIA-Umpire Quant in the local computer.\n";
    
    //$GPM_datapath = dirname(GPM_CGI_PATH) . "/gpm/archive";
    $GPM_datapath = get_local_gpm_archive_path($frm_machine, $umpireQUANT_ID, 'umpireQUANT');
    
    $rawDir = $GPM_datapath ."/". $frm_machine;
    $taskDir = $rawDir . "/DIAUmpire_Quant_Tasks/task". $umpireQUANT_ID;
     
    if(!$engine_str){
      $engine_str = strtolower($frm_SearchEngine);
    }
    
    $task_infor = array(
      'taskDir' => $taskDir,
      'machine' => $frm_machine,
      'tppSchEngine'=> $frm_SearchEngine,
      'tppSchEngine_str' => $engine_str,
      'fileID' => $frm_selected_list_str,
      'rawDir' => $rawDir,
      'tasklog' => $taskDir."/task.log",
      'fileDirName_str' => $fileDirName_str,
      'umpireQuant_ID' => $umpireQUANT_ID,
      'searchID_tppID_str' => $searchID_tppID_str,
      'UmpireQuant_parameters' => $UmpireQuant_option,
      'SAINT_parameters' => $saint_option,
      'SAINT_bait_name_str' => $saint_bait_name_str,
      'SAINT_control_str' => $control_id_str,
      'SAINT_or_mapDIA' => $SAINT_or_mapDIA,
      'REMOVE_SHARED_PEPTIDE_GENE' => $REMOVE_SHARED_PEPTIDE_GENE,
      'mapDIA_parameters' => $mapDIA_option,
      'taskComFile'  => $taskDir."/task.commands",
      'protFilePath' => $taskDir."/combined.pep.inter.prot.xml",
      'QuantParamFile' => $taskDir."/diaumpire.quant_params",
      'Dababase_name' => $Dababase_name,
      'ParentQuantResultsDir' => $taskResultDir_prt
      
    );
    echo  "4. Create DIA-Umpire Quant parameter file.\n";
    print_r($task_infor);
    //exit; 
    
    if(!_is_dir( $taskDir)){
      umask(0002);
      mkdir("$taskDir",  0775, true);
    }
     
    $taskResultDir_prt = $taskDir_prt."/Results";
    if(!$ParentQuantID){
      $all_task_file_path = createDIAUmpireQuantParamFile($task_infor);  
    }      
    if($all_task_file_path or $ParentQuantID){
      createDIAUmpireQuantCommandFile ($task_infor, $all_task_file_path);
    }else{
      $msg = "<font color=#FF0000>Error</font>: mzXML file missing.";
      writeLog($msg, $logfile); 
      exit;
    }
     
    $QSUB_JOB_NAME = 'umpireQuant'. $task_infor['umpireQuant_ID'];
    
    //**********************************************************************************************************
    //($search_command, $task_infor, $QSUB_WORK_DIR, $QSUB_OUTPUT_DIR, $QSUB_JOB_NAME, $run_in_background)
    $ProcessID = run_search_on_local($task_infor['taskComFile'], $task_infor, $task_infor['taskDir'], $task_infor['taskDir'], $QSUB_JOB_NAME, true);
    //**********************************************************************************************************
    $msg =  "6. Run command file.\ntask ID=$ProcessID";
    writeLog($msg, $logfile); 
    return array('ProcessID'=>$ProcessID,'Status'=>'Running','msg'=>$msg);
  
  }else{
    $req = new HTTP_Request($tpp_formaction,array('timeout' => 18000,'readTimeout' => array(18000,0)));
    $req->setMethod(HTTP_REQUEST_METHOD_POST);
    $req->addHeader('Content-Type', 'multipart/form-data');
  
    $req->addPostData('tpp_myaction', 'runDIAUmpireQUANT');
    $req->addPostData('tpp_machine', $frm_machine);
    $req->addPostData('tpp_engine', $frm_SearchEngine);
    $req->addPostData('tpp_engine_str', $engine_str);
    $req->addPostData('tpp_fileID', $frm_selected_list_str);
    $req->addPostData('fileDirName_str', $fileDirName_str);
    $req->addPostData("umpireQuant_ID", $umpireQUANT_ID);
    $req->addPostData("umpireQuant_searchID_tppID_str", $searchID_tppID_str);
    
    $req->addPostData("UmpireQuant_parameters", $UmpireQuant_option);
    $req->addPostData("SAINT_parameters", $saint_option);
    $req->addPostData("SAINT_bait_name_str", $saint_bait_name_str);
    $req->addPostData("SAINT_control_str", $control_id_str);
    $req->addPostData("SAINT_or_mapDIA", $SAINT_or_mapDIA);
    $req->addPostData("mapDIA_parameters", $mapDIA_option);
    
    if (!PEAR::isError($req->sendRequest())) {
      $msg = "4. Return from DIA-Umpire server.";
      writeLog($msg, $logfile); 
      $response1 = $req->getResponseBody();
      echo "\n======response from $tpp_formaction========\n";
      echo $response1;
      echo "\n======end of url open========\n";
      
      flush();
      if(preg_match('/>>>TASK PID: (.+)<<</', $response1, $matches)){
        $ProcessID=$matches[1];
        $Status='Running';
        writeLog("DIA-Umpire Quant ID: $umpireQUANT_ID. ProcessID:$ProcessID", $logfile);
      }else{
        writeLog("Error: DIA-Umpire Quant ID: $umpireQUANT_ID. ". $response1, $logfile);
      }
    } else { 
      writeLog("Error: DIA-Umpire Quant ID: $umpireQUANT_ID. ". $req->getMessage(), $logfile);
    }
  }
  return array('ProcessID'=>$ProcessID,'Status'=>$Status,'msg'=>$msg);
}


function DIAUmpire_Quant_status($umpireQuant_ID, $ProcessID, $machine){
  global $PROHITSDB;
  global $umpireQuantResults_folder;
  global $logFile;
  global $PROHITS_IP;
  global $gpm_ip;
  $storage_ip = STORAGE_IP;
  if(STORAGE_IP=='localhost') $storage_ip = $PROHITS_IP;
  //$ProcessID='nnnn umpireQuant3';
  //$ProcessID='nnnn.xtandemserver.mshri.on.ca umpireQuant3
  //$ProcessID='nnnn';
  
  $status = '';
  echo "<pre>";
  
  $search_logfile = './DIAUmpire_Quant/'.$umpireQuant_ID.'_status.log';
   //if(is_file($search_logfile)){
  $url = "http://".$storage_ip . preg_replace('/msManager|analyst/','',dirname($_SERVER['PHP_SELF']))."logs/log_view.php?log_file=$search_logfile&contents=1";
   
  echo file_get_contents($url);
  /*
  if (is_numeric($ProcessID)){
     
    $url = "http://".$storage_ip . str_replace('msManager','',dirname($_SERVER['PHP_SELF']))."msManager/auto_run_shell.php";
    $url .="?psID=$ProcessID&php_script=auto_DIAUmpireQuant_shell.php";
    $return = file_get_contents($url);
     
    if(preg_match("/>>Yes<</", $return, $matches)){                                                                                                         
     //if(getPhpProcess_arr($tableName, $taskID, $theTask_arr['ProcessID'])){
     //if is local
      print "\n<h2>>>>Task is running<<<</h2>";
    }else{
       print "\n<h2>>>><font color=red>ERROR:</font> Task is not running. Please view search log for detail.<<<</h2>";
    }
    exit;
  }
  */
   
   
  $DIAUmpire_in_prohits = is_in_local_server('DIAUmpire');
   
  if($DIAUmpire_in_prohits){
    //$GPM_datapath = dirname(GPM_CGI_PATH) . "/gpm/archive";
    $GPM_datapath = get_local_gpm_archive_path($machine, $umpireQuant_ID, 'umpireQUANT');
    
    $rawDir = $GPM_datapath ."/". $machine;
    $taskDir = $rawDir . "/DIAUmpire_Quant_Tasks/task". $umpireQuant_ID;
    //***********************************************************************
    $is_running = is_ps_running($ProcessID, "task". $umpireQuant_ID.'/task.commands', $taskDir);
    //************************************************************************
    
    
    print "DIAUmpire-Quant task is in the local server.\n";
     
    if($is_running){
      print "\n>>>Task is running<<<";
      return 1;
    }else{
      print "\n>>>Task is not running <<<";
      if(_is_file("$taskDir/Results/ProtSummary.xls") or  _is_file("$taskDir/FragSummary.xls")){
        print "\n>>>Task has successfully run<<<";
        print "\n>>>RESULTS FOLDER: $taskDir<<<";
        $status = 'Finished';
      }else{
        print "\n>>>ERROR FILE: $taskDir<<<";
        $status = 'Error';
      }
    }
     
  }else{
    $http_gpm_cgi_dir = "http://" . $gpm_ip . GPM_CGI_DIR;
    print "DIAUmpire-Quant task is in the remote server ($http_gpm_cgi_dir).\n";
    $tpp_formaction = $http_gpm_cgi_dir . "/Prohits_TPP.pl";
    $req = new HTTP_Request($tpp_formaction,array('timeout' => 18000,'readTimeout' => array(18000,0)));
    $req->setMethod(HTTP_REQUEST_METHOD_POST);
    $req->addHeader('Content-Type', 'multipart/form-data');
    $req->addPostData('tpp_myaction', 'checkUmpireQuant');
    $req->addPostData('tpp_machine', $machine);
    $req->addPostData('umpireQuant_ID', $umpireQuant_ID);
    $req->addPostData('umpireQuant_PID', $ProcessID);
    if (!PEAR::isError($req->sendRequest())) {
      $response1 = $req->getResponseBody();
      
      echo "----------------------------------------------------------\n";
      echo $response1 . "\n";
      echo "----------------------------------------------------------\n";
      
      //parse the results to results table;
      if(preg_match('/>>>RESULTS FOLDER: (.+)<<</', $response1, $matchs)){
        $status = 'Finished';
        $downlad_dir = $matchs[1];
      }else if(preg_match('/>>>Task is running<<</', $response1, $matchs)){
      
      }else if(preg_match('/>>>ERROR FILE: (.+)<<</', $response1, $matchs)){
         $ERROR_FILE = $matchs[1];
         $status = 'Error';
         $downlad_dir = $matchs[1];
      }
    } else { 
      echo "<h1>ERROR</h1>:";
      echo $req->getMessage();
    }
  }
   
   
  if($status){
    
    if(!_is_dir( $umpireQuantResults_folder )){
      
      @mkdir($umpireQuantResults_folder, 0775, true);
      chmod($umpireQuantResults_folder, 0775);
    }else{
      //if(_is_file($zipped_Results_file)){
        //delete existing Results.zip
        $cmd = "rm -fr $umpireQuantResults_folder*";
        exec ($cmd, $output);
      //}
    }
    
    if($DIAUmpire_in_prohits){
      $tmp_psID = explode(" ",$ProcessID);
      $QSUB_task_ID =$tmp_psID[0];
      if(isset($tmp_psID[1])){
        $QSUB_task_NAME = $tmp_psID[1];
      }
      $STDINo = $QSUB_task_NAME.".o$QSUB_task_ID";
        
      
      $cmd = "cp -rf" .escapeshellarg($taskDir."/task.log"). " ". escapeshellarg($taskDir."/Results/");
      exec ($cmd, $output);
      $cmd = "cp -rf ". escapeshellarg($taskDir."/Results"). " ".  escapeshellarg($umpireQuantResults_folder . "Results");
      print "\n>>>COPY to STORAGE: $umpireQuantResults_folder<<<";
      exec ($cmd, $output);
      $cmd = "cat " .escapeshellarg($taskDir."/$STDINo"). ">>".escapeshellarg($umpireQuantResults_folder . "Results/task.log");
      exec ($cmd, $output); 
       
    }else if($downlad_dir){
      $zipped_Results_file = $umpireQuantResults_folder . "Results.zip";
      $download_to_log = $umpireQuantResults_folder."wget.log";
      $postData = "tpp_myaction=downloadUmpireQuant&taskDir=".$downlad_dir;
      $sysCall = 'wget';
      $sysCall .= " --post-data=\"$postData\"";
      $sysCall .= " --directory-prefix=\"$umpireQuantResults_folder\"";
      $sysCall .= " --output-document=\"$zipped_Results_file\"";
      $sysCall .= " ". $tpp_formaction;
      $sysCall .= ">> $download_to_log 2>&1";
      //echo $sysCall."\n";
      
      system($sysCall);
      if(!_is_file($zipped_Results_file)){
         echo "<h1>Error</h1>: cannot download results folder: $downlad_dir";
         $status = 'Error';
      }else{
        $cmd = "unzip -d $umpireQuantResults_folder "."$zipped_Results_file 2>&1";
        exec ($cmd, $output);
      }
    }
    
     
    if($status == 'Finished'){
      $file_modified = 0;
      echo "Start to add gene info to file ".@date("H:i:s")."<br>";                   
      if(is_dir($umpireQuantResults_folder."Results/mapDIA")){
        $saint_files[] = $umpireQuantResults_folder."Results/mapDIA/analysis_output.txt";
      }else{
        $saint_files[] = $umpireQuantResults_folder."Results/list_MS1.txt";
        $saint_files[] = $umpireQuantResults_folder."Results/list_MS2.txt";
        $saint_files[] = $umpireQuantResults_folder."Results/RESULT_MS1/unique_interactions";
        $saint_files[] = $umpireQuantResults_folder."Results/RESULT_MS2/unique_interactions";
      }
       
       
      foreach($saint_files as $saint_file_name){
        if(_is_file($saint_file_name)){
          echo "\nadd gene to file: $saint_file_name\n";
          add_gene_umpire_saint_results($saint_file_name);
          $file_modified = 1;
        }
      }      
      echo "\nEnd to add gene info to file ".@date("H:i:s")."<br>";
      if($file_modified){
        $cmd = "cd $umpireQuantResults_folder; zip -r Results.zip Results/* 2>&1";
        exec ($cmd, $output);
      }
      echo "<h2>DIA-Umpire Quant has been successfully run</h2>";
    }
  }
 
  if($status){
    $SQL = "update DIAUmpireQuant_log set Status = '". $status ."' where ID = '$umpireQuant_ID'";
    //echo $SQL;
    $PROHITSDB->update($SQL); //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    ?> 
      <input type=button name='Close' value=' Close ' onClick="window.opener.location.reload(true);window.close();" >
    <?php 
  }
  
}

function add_gene_umpire_saint_results($SAINT_File){
  global $proteinDB;
  global $PROHITSDB;
  //echo "++++$SAINT_File+++<br>";
  $handle_r = @fopen($SAINT_File, "r");
  $SAINT_File_tmp = $SAINT_File.'_tmp';
  $handle_w = @fopen($SAINT_File_tmp, "w");
  
  if($handle_r){
    $line_counter = 0;
    $title_str = '';
    
    $file_flag = '';
    $dir_name = dirname($SAINT_File);
    if(preg_match('/\/mapDIA$/',$dir_name)){
      $file_flag = 'mapDIA';
    }
     
    if($file_flag == 'mapDIA'){ //==============================================
      $added_col_arr = array('PreyGeneID','PreyGene');
    }else{
      $added_col_arr = array('PreyGeneID','AvgIntensity','ctrlIntensitySum','AvgctrlIntensity','FoldChange');
    }
    $proteinID_gene_arr = array();
    while(($buffer = fgets($handle_r, 4096)) !== false){
      $line_counter++;
      //$buffer = trim($buffer);
      if($line_counter == 1){
        $buffer = trim($buffer);
        $col_arr = explode("\t", $buffer);
        $real_added_col_arr =  array_diff($added_col_arr, $col_arr);
        
        $key_arr = $col_arr;
        $col_arr = array_merge($col_arr, $real_added_col_arr);
        $buffer = implode("\t", $col_arr);
        fwrite($handle_w, $buffer."\r\n");
        continue;
      }
      $line_w  = '';
      
      $buffer_tmp = str_replace("\r", "", $buffer);
      $buffer_tmp = str_replace("\n", "", $buffer_tmp);
      
      $tmp_arr = explode("\t", $buffer_tmp);
      $key_val_arr = array_combine($key_arr, $tmp_arr);
      
      if($file_flag == 'mapDIA'){ //=======================      
        $protein_line = $tmp_arr[0];
        $is_set = set_gene_ID_Name_from_protein_line($protein_line,$key_val_arr);
        if(!$is_set){
          $pieces = explode("|", $tmp_arr[0]);
          if(count($pieces) == 1){
            if($pieces[0]){
              $proteinID = $pieces[0];
            }else{
              $proteinID = 0;
            }
          }elseif(strlen($pieces[0]) > 3){
            $proteinID = $pieces[0];
          }else{
            $proteinID = $pieces[1];
          }
          //---------------------------------------------------------------------------------------        
          set_PreyGeneID_PreyGene($proteinID, $proteinID_gene_arr,$key_val_arr);
          //----------------------------------------------------------------------------------------
        }          
      }else{
        /*if($key_val_arr['Prey'] != $key_val_arr['PreyGene']){
          fwrite($handle_w, $buffer);
          continue;
        }*/
        $protein_line = $tmp_arr[1];
        $is_set = set_gene_ID_Name_from_protein_line($protein_line,$key_val_arr);
        
        if(!$is_set){
          $pieces = explode("|", $tmp_arr[1]);
          if(count($pieces) == 1){
            if($pieces[0]){
              $proteinID = $pieces[0];
            }else{
              $proteinID = 0;
            }
          }elseif(strlen($pieces[0]) > 3){
            $proteinID = $pieces[0];
          }else{
            $proteinID = $pieces[1];
          }
             
          if($proteinID){
  //--------------------------------------------------------------------------------------------- 
            set_PreyGeneID_PreyGene($proteinID, $proteinID_gene_arr,$key_val_arr);
  //---------------------------------------------------------------------------------------------
          }else{
            $key_val_arr['PreyGene'] = '';
            $key_val_arr['PreyGeneID'] = '';
          }
        } 
          
        $AvgIntensity = '';
        if(isset($key_val_arr['NumRep'])){
          if($key_val_arr['NumRep']){
            $AvgIntensity = round($key_val_arr['IntensitySum'] / $key_val_arr['NumRep'], 3);
          }
          $key_val_arr['AvgIntensity'] = $AvgIntensity;
        }elseif(!in_array("AvgIntensity", $real_added_col_arr)){
          $AvgIntensity = $key_val_arr['AvgIntensity'];
        }  
        
        $ctrlIntensity_arr = explode("|", $key_val_arr['ctrlIntensity']);
        $ctrlIntensitySum = 0;
        $ctrlIntensity_count = 0;
        foreach($ctrlIntensity_arr as $val){
          $ctrlIntensity_count++;
          if($val && $val != '.') $ctrlIntensitySum += $val;
        }
        $key_val_arr['ctrlIntensitySum'] = $ctrlIntensitySum;
  
        $AvgctrlIntensity = '';
        if($ctrlIntensity_count){
          $AvgctrlIntensity = round($ctrlIntensitySum /$ctrlIntensity_count, 3);
        }
        $key_val_arr['AvgctrlIntensity'] = $AvgctrlIntensity;
        
        if(in_array("FoldChange", $real_added_col_arr)){
          $FoldChange = $AvgIntensity / ($AvgctrlIntensity + 0.1);
          $key_val_arr['FoldChange'] = round($FoldChange,3);
        }
      }  
      $line_w = implode("\t", $key_val_arr);
      fwrite($handle_w, $line_w."\r\n");
    }
    fclose($handle_r);
    fclose($handle_w);
    rename($SAINT_File_tmp, $SAINT_File);
  }
}

function set_gene_ID_Name_from_protein_line($protein_line,&$key_val_arr){
  if(preg_match('/gn\|(.*)?:(.+)?\|$/',trim($protein_line),$matches)){
    if(isset($matches[2])){
      $key_val_arr['PreyGeneID'] = $matches[2];
    }
    if(isset($matches[1])){
      $key_val_arr['PreyGene'] = $matches[1];
    }
    return true;
  }else{
    return false;
  }
} 

function set_PreyGeneID_PreyGene($proteinID, &$proteinID_gene_arr,&$key_val_arr){
  global $proteinDB;
  if(!array_key_exists($proteinID, $proteinID_gene_arr)){
    $GeneID = get_protein_GeneID_for_DIAUmpire_Quant($proteinID);
    if($GeneID){
      if(is_numeric($GeneID)){
        $SQL = "select GeneName from Protein_Class where EntrezGeneID='$GeneID'";
      }else{
        $SQL = "select GeneName from Protein_ClassENS where ENSG='$GeneID'";
      }
      $row = $proteinDB->fetch($SQL);
      if($row){
        $key_val_arr['PreyGene'] = $row['GeneName'];
      }else{
        $key_val_arr['PreyGene'] = '';
      }  
      $key_val_arr['PreyGeneID'] = $GeneID;
    }else{
      $key_val_arr['PreyGene'] = $proteinID;
      $key_val_arr['PreyGeneID'] = '';
    }
    $ID_Name_arr['Name'] = $key_val_arr['PreyGene'];
    $ID_Name_arr['ID'] = $key_val_arr['PreyGeneID'];
    $proteinID_gene_arr[$proteinID] = $ID_Name_arr;
  }else{
    $key_val_arr['PreyGene'] = $proteinID_gene_arr[$proteinID]['Name'];
    $key_val_arr['PreyGeneID'] = $proteinID_gene_arr[$proteinID]['ID'];;
  }
}

function get_protein_GeneID_for_DIAUmpire_Quant($proteinKey){
  global $protein_id_sequence_arr;
  global $proteinDB;
  $SQL = '';
  $rt = '';
  if(!$proteinKey) return "";
 
  if(strpos($proteinKey, "DECOY") === 0) return "";
  $AccessionType = get_protein_ID_type($proteinKey);
//echo "\$AccessionType=$AccessionType<br>";   
  $AccessionType = strtoupper($AccessionType);
  if($AccessionType == 'ORF'){
    $SQL = "select EntrezGeneID from Protein_Class where LocusTag='$proteinKey'";
  }else if($AccessionType == 'GI'){
    $SQL = "select EntrezGeneID, GI, SequenceID from Protein_Accession where GI='".$proteinKey."'";
  }else if($AccessionType == 'ENS'){
    $SQL = "select ENSG, EntrezGeneID, SequenceID from Protein_AccessionENS where ENSP='".$proteinKey."'";
  }elseif($AccessionType == 'NCBIACC' or $AccessionType == 'UNIPROTKB'){ 
    $giSplitArr = explode('.',$proteinKey);
    $SQL = "select EntrezGeneID, SequenceID from Protein_Accession where Acc='".$giSplitArr[0]."'";
  }elseif($AccessionType == 'UNIPROT'){
    $SQL = "select EntrezGeneID, UniProtID, SequenceID from Protein_Accession where UniProtID='".$proteinKey."'";
  }elseif($AccessionType == 'IPI'){
    $tmp_key = preg_replace("/\..*$/", '', $proteinKey);
    $SQL = "select EntrezGeneID, SequenceID from Protein_AccessionIPI where IPI='".$tmp_key."'";
  }else{
    $SQL = "select EntrezGeneID, SequenceID from Protein_Accession where Acc='".$proteinKey."' order by ID desc";
  }
  if($SQL){
    $row = $proteinDB->fetch($SQL);
    if($row && $row['EntrezGeneID']){
      if($AccessionType == 'ENS'){
        $rt = ($row['EntrezGeneID'])? $row['EntrezGeneID']:$row['ENSG'];
      }else{
        $rt = $row['EntrezGeneID'];
      }
    }
    return $rt;
  }
}

//########################################################################
function linkDIAUmpireFiles($swath_file_name_base_in_TPP, $diaumpire_results_dir_path, $swath_search_dir_path){
//########################################################################
 global $task_infor;
 
 //$mzFileBase_path = $swath_search_dir_path."/".$swath_file_name_base_in_TPP;
 $mzFileBase_path = $diaumpire_results_dir_path."/".$swath_file_name_base_in_TPP;
 
 if(_is_file($mzFileBase_path."_Q1.mgf") && _is_file($mzFileBase_path."_Q2.mgf") && _is_file($mzFileBase_path."_Q3.mgf")){
   if(!_is_file( $mzFileBase_path."_Q3.mzXML")){
     if(defined("PROTEOWIZARD_BIN_PATH") and _is_file(PROTEOWIZARD_BIN_PATH."/msconvert")){
        $msconvert = PROTEOWIZARD_BIN_PATH."/msconvert";
     }else if(defined("TPP_BIN_PATH") and _is_file(TPP_BIN_PATH."/msconvert")){
        $msconvert = TPP_BIN_PATH."/msconvert";
     }else{
        $error_msg =  "error: msconvert not fond. Please install proteowizard linux version in Prohits/EXT/pwiz-bin/. Then define 'PROTEOWIZARD_BIN_PATH' in Prohits conf file.";
        writeLog($error_msg);
        exit;
     }
     
     $command =  $msconvert . " --mzXML --32 --mz32  '$diaumpire_results_dir_path/'*.mgf -o '$diaumpire_results_dir_path' 2>&1";
     run_search_on_local($command, $task_infor, $task_infor['taskDir'], $task_infor['taskDir']);
     if(!_is_file($mzFileBase_path."_Q3.mzXML")){
       writeLog("cannot convert mgf to mzXML file\n");
       exit;
     }
   } 
 }else{
   writeLog("error: cannot link files to $swath_search_dir_path");
   exit;
 }
 //$command = "ln -sf \"$diaumpire_results_dir_path/\"* \"$swath_search_dir_path/\"";
 //print "\n".$command."\n";
 $OK=link_file($diaumpire_results_dir_path, $swath_search_dir_path);
 if(!$OK){
    writeLog("cannot link dir $swath_search_dir_path\n");
 }
}

//########################################################################
//gether parameters for local DIA-Umpire Quant
function createDIAUmpireQuantParamFile ($task_infor){
//########################################################################
  $defaultPara = array(
  'Version' => 'version 2014.10',
  'InternalLibSearch' => 'true',
  'ExternalLibSearch' => 'false',
  'Thread' => '16', 
  'DecoyPrefix' => '', 
  'PeptideFDR' => '0.01', 
  'ProteinFDR' => '0.01', 
  'DataSetLevelPepFDR' => 'false',
  'UserMod' => '',
  'InternalLibID' => 'LibID',
  'ExternalLibPath' => '', 
  'ExternalLibDecoyTag' => 'DECOY',
  'ExtProbThreshold' => '0.99',

  
  'ProbThreshold' => '0.9',
  'FilterWeight' => 'GW',
  'MinWeight' => '0.9', 
  'TopNFrag' => '6',
  'TopNPep' => '6',
  'Freq' => '0.5');
  $taskDir = $task_infor['taskDir'];
  $QuantParamFile = $task_infor['QuantParamFile'];
  $UmpireQuant_parameters = $task_infor['UmpireQuant_parameters'];
  $fileDirName_str = $task_infor['fileDirName_str'];
  $protFilePath = $task_infor['protFilePath'];
  
  $SAINT_control_str = $task_infor['SAINT_control_str'];
  $SAINT_bait_name_str = $task_infor['SAINT_bait_name_str'];
  $tppSchEngine_str = $task_infor['tppSchEngine_str'];
  $SAINT_or_mapDIA = $task_infor['SAINT_or_mapDIA'];
  $searchID_tppID_str = $task_infor['searchID_tppID_str'];
  $rawDir = $task_infor['rawDir'];
   
   
  $engine_names = explode(':',$tppSchEngine_str);
   
  
  //print "$fileDirName_str=---=$SAINT_control_str=---=$SAINT_bait_name_str\n";
  
  $decoy_prefix = '';
  $fasta_file_path = '';
  $pepXML_file_path = '';
  $mzXML_file_path = '';
  $mzXML_file_str = '';
  $fileDirs = explode(":", $fileDirName_str);
  $taskID_tppIDs = explode(':',$searchID_tppID_str);  
  $tmp_file_basename = '';
  $tmp_file_dir_path = '';
  
  $all_file_dir_path = array();
  
  $SAINT_control = explode(',',$SAINT_control_str);
 
  //$SAINT_control_hash = map { $_ => 1 } @SAINT_control; //use $SAINT_control 
  $SAINT_baitName_hash = array();
  $tmp_id_bait_name = explode(',',$SAINT_bait_name_str);
  foreach ($tmp_id_bait_name as $theValue){
    list($tmp_id, $tmp_name) = explode('|',$theValue);
    $SAINT_baitName_hash[$tmp_id] = $tmp_name;
  }
  
  $to_file_BaitName_hash = array();
  $to_file_ControName_hash = array();
  $tppID_hash = array();
   
   
  foreach ($taskID_tppIDs as $theValue){
    if($theValue){
      list( $tmp_taskID, $tmp_tppID) = explode(',', $theValue);
      $tppID_hash[$tmp_taskID] = $tmp_tppID;
    }
  }
   
 
  $tmp_taskID = '';
  $tmp_fileID = '';
  $tmp_fileName = '';
  $tmp_file = '';
  $tmp_dir = '';
  $tmp_ext = '';
  $tmp_engine = '';
  
  if(isset($task_infor['Dababase_name']) and $task_infor['Dababase_name']){
    //$fasta_file_path = get_gpm_db_file_path($task_infor['Dababase_name']);
    //it will user following code to get 
  }
 
  foreach  ($fileDirs as $theDir) { 
    if(!trim($theDir))continue;
    
    list($tmp_taskID, $tmp_fileID, $tmp_fileName, $tmp_engine) = explode('|',$theDir);
    //get umpireSE task path.
    $tmp_GPM_datapath = get_local_gpm_archive_path($task_infor['machine'], $tmp_taskID);
    $tmp_rawDir = $tmp_GPM_datapath ."/". $task_infor['machine'];
    
    $tmp_file = pathinfo($tmp_fileName, PATHINFO_FILENAME);
    $tmp_file_basename = $tmp_fileID . "_" . $tmp_file;
    if($tmp_engine == 'iprophet'){
      $tmp_file_dir_path = $tmp_rawDir  . "/". $tmp_file_basename . "/";
    }else{
      $tmp_file_dir_path = $tmp_rawDir . "/task" . $tmp_taskID . "/". $tmp_file_basename ."_".$tmp_engine. "/";
    }
    $mzXML_file_path = $tmp_file_dir_path . $tmp_file_basename . ".mzXML";
    print $mzXML_file_path."\n"; 
    
    
    if(_is_file($mzXML_file_path)){
       
      if(in_array($tmp_fileID, $SAINT_control)) { 
        $to_file_ControName_hash[$tmp_fileID.'_'.$SAINT_baitName_hash[$tmp_fileID]] = $tmp_file_basename;
       
      }else{
        if(isset($to_file_BaitName_hash[$SAINT_baitName_hash[$tmp_fileID]])){
          $to_file_BaitName_hash[$SAINT_baitName_hash[$tmp_fileID]] .= "\t". $tmp_file_basename;
        }else{
          $to_file_BaitName_hash[$SAINT_baitName_hash[$tmp_fileID]] = $tmp_file_basename;
        }
      }
      $tmp_iprophet_pep_path;
      $added = 0;
       
      for ($i = 0; $i < count($engine_names); $i++){
        
        $tmp_file_dir_path = $tmp_rawDir . "/task" . $tmp_taskID . "/". $tmp_file_basename ."_".$engine_names[$i]. "/";
        if( _is_dir( $tmp_file_dir_path)){
          if($tmp_engine == 'iprophet'){
            if(!$added){
              $added = 1;
              $tmp_iprophet_pep_path = $tmp_rawDir . "/task" . $tmp_taskID . "/tpp". $tppID_hash[$tmp_taskID] ."/:". $tmp_file_basename;
              array_push($all_file_dir_path, $tmp_iprophet_pep_path);
            }
          }else{
             array_push($all_file_dir_path, $tmp_file_dir_path);
          }
          //get DECOY prefix and fasta path
          if(!$fasta_file_path){
            $pepXML_file_path = $tmp_file_dir_path. "interact-" . $tmp_file_basename . "_Q1.pep.xml";
            if(_is_file( $pepXML_file_path)){
              $fh = fopen ($pepXML_file_path, 'r');
              while (($buffer = fgets($fh, 4096)) !== false) {
                 $buffer = trim($buffer);
                if(!$decoy_prefix && preg_match("/<peptideprophet_summary .+ DECOY=([^ ]+)/", $buffer, $matches)){
                   $decoy_prefix = $matches[1];
                }
                if( preg_match("/<database_refresh_timestamp database=\"([^\"]*)\"/", $buffer, $matches )){
                   $fasta_file_path = $matches[1];
                   break;
                }
              }
              fclose ($fh);
            }
          }
        }
        $i++;
      }
      $mzXML_file_str .= $mzXML_file_path."\r\n";
    }else{
      writeLog("file missing: $mzXML_file_path", $task_infor['tasklog']);
    }
  }
  $para_str = '';
  $para_arr = explode(";", $UmpireQuant_parameters);
 
  foreach  ($para_arr as $thePara) {
    if(!trim($thePara))continue;
    //echo "\n".$thePara;
    list($tmp_n, $tmp_v) = explode(':', $thePara, 2);
    $defaultPara[$tmp_n] = $tmp_v;
  } 
  $to_file;
  
  print "$QuantParamFile\n";
  
  $fp = fopen($QuantParamFile, "w");
  $to_file = "#DIA-Umpire (".$defaultPara{'Version'}.")\r\n";
  $to_file .= "path = $taskDir/\r\n";
  $to_file .= "==File list begin\r\n";
  $to_file .= $mzXML_file_str;
  $to_file .= "==File list end\r\n";
  $to_file .= "Thread = " . $defaultPara{'Thread'}."\r\n";
  $to_file .= "InternalLibSearch = " . $defaultPara{'InternalLibSearch'}."\r\n";
  $to_file .= "ExternalLibSearch = " . $defaultPara{'ExternalLibSearch'}."\r\n";
  $to_file .= "\r\n";
  
  $to_file .= "#Fasta file path\r\n";
  $to_file .= "Fasta = $fasta_file_path\r\n";
  $to_file .= "\r\n";
  
  $to_file .= "#Combined prot.xml file\r\n";
  $to_file .= "Combined_Prot=$protFilePath\r\n";
  
  $to_file .= "#Decoy tag\r\n";
  $to_file .= "DecoyPrefix=$decoy_prefix\r\n";
  $to_file .= "\r\n";
  
  $to_file .= "#FDR threshold\r\n";
  $to_file .= "PeptideFDR = ". $defaultPara{'PeptideFDR'}."\r\n";
  $to_file .= "ProteinFDR = ". $defaultPara{'ProteinFDR'}."\r\n";
  $to_file .= "DataSetLevelPepFDR = ". $defaultPara{'DataSetLevelPepFDR'}."\r\n"; 
  $to_file .= "\r\n";
  
  $to_file .= "#UserMod path\r\n";
  $to_file .= "UserMod = ". $defaultPara{'UserMod'}."\r\n";
  $to_file .= "\r\n";
  
  $to_file .= "####Targeted re-extraction: internal library####\r\n";
  $to_file .= "InternalLibID = ". $defaultPara{'InternalLibID'}."\r\n";
  $to_file .= "\r\n";
  
  $to_file .= "####Targeted re-extraction: external library####\r\n";
  $to_file .= "ExternalLibPath = ". $defaultPara{'ExternalLibPath'}."\r\n";
  $to_file .= "ExternalLibDecoyTag = ". $defaultPara{'ExternalLibDecoyTag'}."\r\n";
  $to_file .= "ExtProbThreshold = ". $defaultPara{'ExtProbThreshold'}."\r\n";
  $to_file .= "\r\n";
  
  $to_file .= "####Targeted re-extraction ID filtering\r\n";
  $to_file .= "ProbThreshold = ". $defaultPara{'ProbThreshold'}."\r\n";
  $to_file .= "\r\n";
  
  $to_file .= "####Peptide filtering####\r\n"; 
  $to_file .= "FilterWeight = ". $defaultPara{'FilterWeight'}."\r\n";
  $to_file .= "MinWeight = ". $defaultPara{'MinWeight'}."\r\n"; 
  
  $to_file .= "####Peptide/Fragment selection for MS2-based quantitation####\r\n"; 
  $to_file .= "TopNFrag = ". $defaultPara{'TopNFrag'}."\r\n";
  $to_file .= "TopNPep = ". $defaultPara{'TopNPep'}."\r\n";
  $to_file .= "Freq = ". $defaultPara{'Freq'}."\r\n";
  
  if($SAINT_or_mapDIA == 'SAINT' && $SAINT_bait_name_str){
    $to_file .= "####Export SAINT input files####\r\n";
    $to_file .= "ExportSaintInput = true\r\n";
    $to_file .= "#Quantitation type (MS1, MS2, or BOTH)\r\n";
    $to_file .= "QuantitationType = BOTH\r\n";
    $to_file .= "#Assign file basename of baits/control samples, tab-delimited for multiple replicates\r\n";
    
    $bait_count = 1;
    $control_count = 1;
    foreach ($to_file_BaitName_hash as $baitName=>$fileNames){
      $to_file .= "BaitName_".$bait_count." = ".  $baitName ."\r\n";
      $to_file .= "BaitFile_".$bait_count." = ".  $fileNames ."\r\n";
      $bait_count++;
    }
    foreach($to_file_ControName_hash as $cbaitName => $cfileNames){
      $to_file .= "ControlName_".$control_count." = ".  $cbaitName ."\r\n";
      $to_file .= "ControlFile_".$control_count." = ".  $cfileNames ."\r\n";
      $control_count++;
    }
  }else{
    $to_file .= "ExportSaintInput = false\r\n";
  }
  
  fwrite($fp, $to_file); 
  fclose($fp);
  return $all_file_dir_path;
}






//########################################################################
//local DIA-Umpire Quant command file
function createDIAUmpireQuantCommandFile ($task_infor, $all_task_file_path){
//########################################################################
   
  $use_memery_size = '40G';
  if(defined("JAR_MAX_MEMORY") and JAR_MAX_MEMORY){
    $use_memery_size = JAR_MAX_MEMORY;
  }
  $JAR_Command = get_jar_command($use_memery_size);
  //in analyst dir
  $mapDIA_CRATE_DATA_FILE = dirname(__FILE__).'/create_DIAUmpire_data_unique_pep.php';
  
  echo "5. Create task command file\n";
  echo $task_infor['taskComFile']."\n";
  print "-- all searched file locations.\n";
  $fp = fopen($task_infor['taskComFile'], "w");
  
  fwrite($fp, "#!/bin/bash\n\n");
  fwrite($fp, "echo `hostname`\n");
  fwrite($fp, "echo \"###1. cd to working dir\"\n");
  
  $command = "cd '".$task_infor['taskDir']."'" ;
  fwrite($fp,  "$command \n"); 
  $command = "if [ ! -d ./Results ]\nthen\n\tmkdir ./Results\nfi\n";
  $command .= "if [ ! -d ./Results ]\nthen\n\techo \"Error: Cannot make Results folder\"\n\texit\nfi";
  fwrite($fp, "$command\n"); 
  
  if($task_infor['ParentQuantResultsDir']){
    $command = "if [ ! -f ".$task_infor['ParentQuantResultsDir']."/FragSummary.xls ]\nthen\n\techo 'Parent task folder has no FragSummary.xls'\n\texit\nfi";
    fwrite($fp, "$command \n"); 
    $command = "cp '". $task_infor['ParentQuantResultsDir']."/'*xls '" . $task_infor['taskDir']."/'" ;
    fwrite($fp, "$command\n"); 
    $command = "FragSummary_FILE=\"FragSummary.xls\"";
    fwrite($fp, "$command \n");
  }else{
    fwrite($fp, "echo \"###2.  run ProteinProphet for all interact files\"\n");  
    $all_task_file_path_str = '';
    if($task_infor['tppSchEngine'] == 'iProphet'){
      //my ($tmp_path, $tmp_file_basename, $pep_file_from, $pep_file_to);
      foreach ($all_task_file_path as $theFilePath){
        print $theFilePath."\n";
        if(strpos($theFilePath, ':') === false) continue;
        list($tmp_path, $tmp_file_basename) = explode(':', $theFilePath);
        $pep_file_from = "'". $tmp_path . "interact-".$tmp_file_basename."_Q'" . "*";
        $pep_file_to = dirname(dirname($tmp_path)). "/". $tmp_file_basename . "/";
        
         
        $command = "cd '".$pep_file_to."'" ;
        fwrite($fp,  "$command \n"); 
        $command = 'for theFile in `ls interact-* *_LCMSID.serFS *_LibID_* *_Q1.*FS *_Q2.*FS *_Q3.*FS *_masscaliRT.png`;do rm -f $theFile; done';
        
        fwrite($fp, "$command\n"); 
        $command = "cp -f $pep_file_from '$pep_file_to'";
        fwrite($fp, "$command\n"); 
        $all_task_file_path_str .= "'".$pep_file_to . "interact-".$tmp_file_basename."_Q'" . "* ";
      }
      $command = "cd '".$task_infor['taskDir']."'" ;
      fwrite($fp, "$command \n"); 
       
    }else{
     
      foreach ($all_task_file_path as $theFilePath){
        print $theFilePath."\n";
        $all_task_file_path_str .=  "'".$theFilePath . "interact-'*.pep.xml ";   
      }
    }
     
    $command = escapeshellarg(TPP_BIN_PATH.'/ProteinProphet') . " ". $all_task_file_path_str . escapeshellarg($task_infor['protFilePath']);
    if($task_infor['tppSchEngine'] == 'iProphet'){
     $command .= " IPROPHET";
    }
    $command .= " 2>&1";
    fwrite($fp, "echo \"$command\"\n$command \n"); 
    fwrite($fp, "echo \"###3. run DIA_Umpire_Quant\"\n");
    
    $command = $JAR_Command . escapeshellarg(DIAUMPIRE_BIN_PATH . "/DIA_Umpire_Quant.jar") ." ". escapeshellarg($task_infor['QuantParamFile']);
     
    fwrite($fp, "echo \"$command\"\n$command \n"); 
    
    $command = "FragSummary_FILE=`ls -t FragSummary_*.xls | head -1` \nif [ !-f \"\$FragSummary_FILE\" ] \nthen \n\techo 'Error: no DIA-Umpire-Quant result file created.' \n\texit \nelse \n\techo 'DIA-Umpire-Quant result files were created.' \nfi \n";
    fwrite($fp, "$command \n");
    
    $command = "for theFile in `ls *.xls diaumpire.quant_params`\ndo\n\tcp -f \$theFile Results/`echo \$theFile|sed \"s/_[0-9]\\+//g\"`\ndone";
    fwrite($fp, "$command \n"); 
     
  }
  
  if($task_infor['SAINT_or_mapDIA'] == 'mapDIA' && $task_infor['SAINT_bait_name_str']){
     $mapDIA_input = "mapDIA_input.txt";
     $mapDIA_data = "mapDIA_data.txt";
     $mapDIA_path = dirname(dirname(MAP_DIA_BIN_PATH));
     $CRATE_DATA_FILE_in_mapDIA = $mapDIA_path."/create_DIAUmpire_data_unique_pep.php";
     if(!_is_file($CRATE_DATA_FILE_in_mapDIA)){
       copy($mapDIA_CRATE_DATA_FILE, $CRATE_DATA_FILE_in_mapDIA);
     }
     $mapDIA_option = $task_infor['mapDIA_parameters'];
     $mapDIA_option = str_replace(",", "\n",$task_infor['mapDIA_parameters']);
     $fp_mapDIA = fopen($task_infor['taskDir']."/".$mapDIA_input, "w");
     fwrite($fp_mapDIA,  "### input file\nFILE=".$mapDIA_data."\n");
     fwrite($fp_mapDIA, $mapDIA_option); 
     fclose($fp_mapDIA);
     
      
     
     fwrite($fp, "echo \"###4. run mapDIA\"\n");
     $command = "if [ ! -d ./Results/mapDIA ]\nthen\n\tmkdir ./Results/mapDIA\nfi\n";
     fwrite($fp, "$command \n"); 
     $command = "php -f ".escapeshellarg($CRATE_DATA_FILE_in_mapDIA)." \$FragSummary_FILE ".escapeshellarg($task_infor['SAINT_bait_name_str'])." $mapDIA_data";
     if($task_infor['REMOVE_SHARED_PEPTIDE_GENE'] == 'true'){
       $command .= ' 1';
     }
     fwrite($fp, "echo \"$command\"\n$command \n");
     $command = escapeshellarg(MAP_DIA_BIN_PATH."/mapDIA") . " ". $mapDIA_input; 
     fwrite($fp, "echo \"$command\"\n$command \n"); 
     $command = "if [ -f analysis_output.txt ]\nthen\n\techo 'mapDIA result files created'\n\tcp -f protein_level.txt peptide_level.txt log2_data.txt fragment_selection.txt analysis_output.txt mapDIA*.txt ./Results/mapDIA/\nelse\n\techo 'no mapDIA result files were created.'\nfi";
         
     fwrite($fp, "$command \n"); 
    
     
  }else if($task_infor['SAINT_or_mapDIA'] == 'SAINT' && $task_infor['SAINT_bait_name_str']){
    
    fwrite($fp, "echo \"###4. run SAINT\"\n");
    $command = "if ls SAINT_Interaction_MS1* > /dev/null 2>&1\nthen\n\techo \"SAINT input files were created\"\nelse\n\techo \"Error: no SAINT input files were created\"\n\texit\nfi";
    fwrite($fp, "$command \n"); 
  
    $command = "for theFile in `ls SAINT_*.txt`\ndo\n\tcp -f \$theFile Results/`echo \$theFile|sed \"s/_[0-9]\+//g\"`\ndone";
    fwrite($fp, "$command \n"); 
    
    $tmp_saint_op = explode(',', $task_infor['SAINT_parameters']);
    $saint_para_hash = array();
    $saint_para_hash['saint_type'] = 'express';
    $saint_para_hash['nControl'] = '4';
    $saint_para_hash['nCompressBaits'] = '2';
    $saint_para_hash['nburn'] = '2000';
    $saint_para_hash['niter'] = '5000';
    $saint_para_hash['lowMode'] = '0';
    $saint_para_hash['minFold'] = '1'; 
    $saint_para_hash['fthres'] = '0';
    $saint_para_hash['fgroup'] = '0';
    $saint_para_hash['var'] ='0';
    $saint_para_hash['normalize'] ='1';
    $saint_para_hash['has_iRefIndex_file'] = '';
    
    
    foreach  ($tmp_saint_op as $thePare) {
      if(strpos($thePare, ":") === false)continue;
      list($tmp_name, $value) = explode(':', $thePare);
      $saint_para_hash[$tmp_name] = $value;
    }
     
    $tmp_com = ''; 
    
     
    if(_is_file(SAINT_SERVER_EXPRESS_PATH."/SAINTexpress-int")){
       
        fwrite($fp, "echo \"#4-1. run SAINT express\"\n");
        $command = escapeshellarg(SAINT_SERVER_EXPRESS_PATH."/SAINTexpress-int");
        
        if($saint_para_hash['nControl']){
          $command .= " -L".$saint_para_hash['nControl'];
        }
        if($saint_para_hash['nCompressBaits']){
          $command .= " -R".$saint_para_hash['nCompressBaits'];
        }
        $tmp_com = $command . " SAINT_Interaction_MS1_*.txt SAINT_Prey_*.txt SAINT_Bait_*.txt";
        if(_is_file("iRefIndex.dat")){
          $tmp_com .= " iRefIndex.dat";
        }
        fwrite($fp, "echo \"$tmp_com\"\n$tmp_com \n"); 
        $tmp_com = "if [ -f list.txt ]\nthen\n\tmv list.txt ./Results/list_MS1.txt\nfi";
        fwrite($fp, "$tmp_com \n");
        
        $tmp_com = $command . " SAINT_Interaction_MS2_*.txt SAINT_Prey_*.txt SAINT_Bait_*.txt";
         if(_is_file("iRefIndex.dat")){
          $tmp_com .= " iRefIndex.dat";
        }
        fwrite($fp, "echo \"$tmp_com\"\n$tmp_com \n"); 
        $tmp_com = "if [ -f list.txt ]\nthen\n\tmv list.txt ./Results/list_MS2.txt\nfi";
        fwrite($fp, "$tmp_com \n"); 
     
        
        fwrite($fp, "echo \"#4-2. run SAINT\"\n");
        $command_reform_MS1 = escapeshellarg(SAINT_SERVER_PATH."/saint-reformat"). " SAINT_Interaction_MS1_*.txt SAINT_Prey_*.txt SAINT_Bait_*.txt";
        $command_reform_MS2 = escapeshellarg(SAINT_SERVER_PATH."/saint-reformat"). " SAINT_Interaction_MS2_*.txt SAINT_Prey_*.txt SAINT_Bait_*.txt";
        $other_options;
        if($saint_para_hash['nControl']){
          $command_reform_MS1 .= " " . $saint_para_hash['nControl'];
          $command_reform_MS2 .= " " . $saint_para_hash['nControl'];
          $command = escapeshellarg(SAINT_SERVER_PATH. "/saint-int-ctrl");
          $other_options = " ". $saint_para_hash['lowMode']. " ". $saint_para_hash['minFold']." ". $saint_para_hash['normalize'];
        }else{
          ###############?????????????????????????????
          $command = escapeshellarg(SAINT_SERVER_PATH. "/saint-spc-noctrl"); 
          ###############????????????????????????????
          $other_options = " ".$saint_para_hash['fthres']." ". $saint_para_hash['fgroup']." ". $saint_para_hash['var']." ". $saint_para_hash['normalize'];
        }
        
        fwrite($fp, "echo \"$command_reform_MS1\" \n$command_reform_MS1\n");
        $command = "GSL_RNG_SEED=123 ". $command . " interaction.new prey.new bait.new ". $saint_para_hash['nburn'] . " ". $saint_para_hash['niter'] . $other_options;
        fwrite($fp, "echo \"$command\" \n$command \n"); 
        $tmp_com = "if [ -d RESULT ]\nthen\n\techo 'SAINT MS1 result files were created'\n\tmv *.new ./RESULT\n\tmv RESULT Results/RESULT_MS1\nelse\n\techo 'Error: No SAINT MS1 result files were created'\n\trm -f *.new\nfi\n";
        $tmp_com .= "rm -R LOG MAPPING MCMC";
        fwrite($fp, "$tmp_com\n");
        fwrite($fp, "echo \"$command_reform_MS2\" \n$command_reform_MS2\n");
        fwrite($fp, "echo \"$command\" \n$command \n"); 
        $tmp_com = "if [ -d RESULT ]\nthen\n\techo 'SAINT MS2 result files were created'\n\tmv *.new ./RESULT\n\tmv RESULT Results/RESULT_MS2\nelse\n\techo 'Error: No SAINT MS2 result files were created'\n\trm -f *.new\nfi";
        fwrite($fp, "$tmp_com\n");
      
      /*
      #/mnt/thegpm/Prohits_SAINT/saint_code/SAINT_v2.3.4/bin/saint-reformat SAINT_Interaction_MS1_*.txt SAINT_Prey_*.txt SAINT_Bait_*.txt 3
      #/mnt/thegpm/Prohits_SAINT/saint_code/SAINT_v2.3.4/bin/saint-reformat SAINT_Interaction_MS2_*.txt SAINT_Prey_*.txt SAINT_Bait_*.txt 3
      #GSL_RNG_SEED=123 /mnt/thegpm/Prohits_SAINT/saint_code/SAINT_v2.3.4/bin/saint-int-ctrl interaction.new prey.new bait.new 2000 5000 0 1 1
      #6 run SATINT Express
      #/mnt/thegpm/Prohits_SAINT/saint_code/SAINTexpress_v3.3__2014_Apr_23/bin/SAINTexpress-int  -L3 -R2 SAINT_Interaction_MS1_*.txt SAINT_Prey_*.txt SAINT_Bait_*.txt
      #/mnt/thegpm/Prohits_SAINT/saint_code/SAINTexpress_v3.3__2014_Apr_23/bin/SAINTexpress-int  -L3 -R2 SAINT_Interaction_MS2_*.txt SAINT_Prey_*.txt SAINT_Bait_*.txt
      */
    }else{
      fwrite($fp, "#SAINT is requested. But SAINT cannot be run. Please check SAINT setting.\n");
    }
  }else{
      fwrite($fp, "#SAINT is not requested.\n");
  }
  $command = "cp -f task.log ./Results/";
  fwrite($fp, "$command \n"); 
  fclose ($fp);
  
  
  
  
  
  system("chmod 775 ". $task_infor['taskComFile']);
  
}
if(!function_exists('writeLog')) { 
  function writeLog($msg, $log_file=''){
  //----------------------------------- 
    global $logfile; 
    global $debug;
    if(!$log_file and $logfile){
      $log_file = $logfile;
    }
    $log = fopen($log_file, 'a+');
    fwrite($log, "\n" . $msg);
    fclose($log);
  }
}
?>