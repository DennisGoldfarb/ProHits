<?php
function runMSPLIT($msplit_file_arr, $theTask_arr, $searchEngines_parameter_arr){
  global $managerDB;
  global $msManager_link;
  global $task_infor;
  global $frm_theURL;
  
  global $theTask_arr;
  global $tableName;
  global $resultTable;
  global $searchEngine_arr;
  global $tmp_Engine;
  global $taskTable;
  global $search_logfile;
  global $gpm_ip;
  
  $MSPLIT_core_num = 8;
  
  
  $frm_variable_mod_str = '';
  $frm_fixed_mod_str = '';
    
  $MSPLIT_has_been_run = false;
  $search_engine = '';
  $MSPLIT_LIB = '';
  $searchEngines_parameter_arr['MSGFDB'] = array('other_param' => '', 'mods_str'=>'', 'database_name'=>'');
  $allDDAFileAdded = false;
 



  if(strpos($theTask_arr['DIAUmpire_parameters'], "allDDAFileAdded:Yes")!== false){
    $allDDAFileAdded = true;
  }
  
  if(!mysqli_ping($msManager_link)) {
    $managerDB = new mysqlDB(MANAGER_DB);
    $msManager_link  = $managerDB->link;
  }
  
  $engine_arr = explode(";", $theTask_arr['SearchEngines']);
  foreach($engine_arr as $en_str){
    $en_pair = explode("=", $en_str);
    if(count($en_pair) < 2) continue;
    if($en_pair[0] == 'MSPLIT_LIB'){
      $MSPLIT_LIB = $en_pair[1];
    }else if($en_pair[0] == 'Database'){
      $searchEngines_parameter_arr['MSGFDB']['database_name'] = $en_pair[1];
    }
  }
  
  $http_gpm_cgi_dir = "http://" . $gpm_ip . GPM_CGI_DIR;
  $tpp_formaction = $http_gpm_cgi_dir . "/Prohits_TPP.pl";
  $MSPLIT_in_prohits = false;

  $tmp_par_arr = explode("\n", $theTask_arr['Parameters']);
  
  foreach($tmp_par_arr as $par_str){
    $engine_par_arr = array();
    $the_p = explode("===", $par_str);
    
    if($the_p[0] == 'MSGFDB'){ 
      $search_engine = $the_p[0];
      $searchEngines_parameter_arr['MSGFDB'] = create_MSGFPL_parameter_arr($the_p[1]);
     
    }else if($the_p[0] == 'MSPLIT'){
       
      $searchEngines_parameter_arr['MSPLIT'] = str_replace("para_","", $the_p[1]);
    }else if($the_p[0] == 'frm_variable_mod_str'){
      $frm_variable_mod_str = str_replace(";;", ":::", $the_p[1]);
    }else if($the_p[0] == 'frm_fixed_mod_str'){
      $frm_fixed_mod_str = str_replace(";;", ":::", $the_p[1]);
    }
  }
  
  if($MSPLIT_LIB and !$searchEngines_parameter_arr['MSGFDB']['mods_str']){
    $multiple_select_str = "frm_variable_MODS|$frm_variable_mod_str&&frm_fixed_MODS|$frm_fixed_mod_str";
     
    $searchEngines_parameter_arr['MSGFDB']['mods_str'] =make_MSGFPL_mod_str($multiple_select_str, 'MSGFPL');
  }
  
  $allSwathFileAdded = '';
  if(strpos($theTask_arr['DIAUmpire_parameters'], "allSwathFileAdded")!== false){
    $allSwathFileAdded = 'Yes';
    $theTask_arr['DIAUmpire_parameters'] = preg_replace("/allSwathFileAdded:\w*;/", '', $theTask_arr['DIAUmpire_parameters']);
  }
  
  //print_r($msplit_file_arr);
  //print_r($theTask_arr);
  //print_r($searchEngines_parameter_arr); 
  //exit;
  
  $MSPLIT_in_prohits = is_in_local_server('MSPLIT');
  echo "MSPLIT in local=$MSPLIT_in_prohits\n";
 
  //run MSPLIT-DIV in XTandem server   
  //if($MSPLIT_in_prohits){
  $WellID = '';
  $raw_file_path = '';
  //$msplit_file_arr[] = array($tmp_WellID, $tmp_raw_file_path, $tmp_Engine);
  //no indevadio raw file needed.
  $task_infor = prepare_run_search_on_local($tableName, $WellID, $theTask_arr['ID'], $raw_file_path, 'MSPLIT', $msplit_file_arr);
  
  
  $msplit_arr = explode('dia_',  $searchEngines_parameter_arr['MSPLIT']);
  
  $msg = "Run MSPLIT on local server.";
  writeLog($msg,$search_logfile);
  $msg = print_r($searchEngines_parameter_arr, true);
  $msg = "searchEngines_parameter_arr\n".$msg;
  
  //writeLog($msg,$task_infor['tasklog']);
  
  $MSPLIT_param_arr = array();
  $NEW_MSPLIT_param_str = $msplit_arr[0];
  $NEW_MSPLIT_param_str = preg_replace("/;$/", "", $NEW_MSPLIT_param_str);
  $para_arr = explode(";", $NEW_MSPLIT_param_str);
  
   
  foreach($para_arr as $thePara_str){
    list($tmp_name, $tmp_value) = explode(":", $thePara_str);
    $MSPLIT_param_arr[$tmp_name] = $tmp_value;
  }
  //print "NEW MSPLIT param:$NEW_MSPLIT_param_str\n";
  $to_file = '';
  $dia_win_ms1_start = '';
  $dia_win_ms1_end = '';
  $dia_SWATH_window_setting = '';
   
  //make variable window file.
  $task_infor["scanNum"] = 0;
  if(is_file($task_infor["paramFilePath"])){ 
	$task_infor["scanNum"] = count(file($task_infor["paramFilePath"])) -1;
    print "\nvar wind file: ".$task_infor["paramFilePath"]."\n";
  }else if(count($msplit_arr) == 4){
    for( $i =1; $i< 4; $i++){
      $msplit_arr[$i] = trim(preg_replace("/;$/",'', trim($msplit_arr[$i])));
      list($tmp_name, $tmp_value) = explode(":", $msplit_arr[$i]);
      if($tmp_name == 'win_ms1_start'){
        $dia_win_ms1_start = $tmp_value;
      }else if($tmp_name == 'win_ms1_end'){
        $dia_win_ms1_end = $tmp_value;
      }else if($tmp_name == 'SWATH_window_setting'){
        $dia_SWATH_window_setting = $tmp_value;
      }
    }
    
	 
    if($dia_SWATH_window_setting){
      if(!$dia_win_ms1_start){
        $dia_win_ms1_start = '0';
      }
      if(!$dia_win_ms1_end){
        $dia_win_ms1_end = '1250';
      }
      print "window ms1 start: $dia_win_ms1_start\n";
      print "window ms1 end: $dia_win_ms1_end\n";
      print "window ms2: $dia_SWATH_window_setting\n";
      $to_file = "#Scan\twindowBegin\twindowEnd\n";
      $to_file .= "MS1\t".$dia_win_ms1_start."\t".$dia_win_ms1_end."\n";
      
       
      $dia_SWATH_window_setting = str_replace(" ", "\t", $dia_SWATH_window_setting);
      $tmp_v = explode(",", $dia_SWATH_window_setting);
      if(count($tmp_v) > 5){
        
        $i = 0;
        for( $i =0; $i< count($tmp_v); $i++){
          $to_file .= "MS2\t".$tmp_v[$i]."\n";
        }
        $fp = fopen($task_infor["paramFilePath"], 'w');
        if(!$fp){
          echo "can not open file to write: ".$task_infor["paramFilePath"]; exit;
        }
        fwrite($fp, $to_file);
        fclose($fp);
        print "var wind file: ".$task_infor["paramFilePath"]."\n";
       
        $task_infor["scanNum"] = $i+1;
      }
    }
  }

   
  $gpmDbFile = get_gpm_db_file_path($searchEngines_parameter_arr['MSGFDB']['database_name']);
  $MSGFDB_parameters_other = str_replace(" ", "&&", $searchEngines_parameter_arr['MSGFDB']['other_param']);
  $MSGFDB_parameters_mods =  str_replace(" ", "&&", $searchEngines_parameter_arr['MSGFDB']['mods_str']);
  global $prohits_root;
   
  $msplit_dia_path = dirname(dirname(MSPLIT_JAR_PATH));
  if($MSPLIT_LIB){
    $MSPLIT_LIB = $msplit_dia_path."/MSPLIT_LIBS/".$MSPLIT_LIB;
    writeLog("PreDefined LIB: $MSPLIT_LIB", $task_infor['statuslog']);
  }
  if(!_is_dir($msplit_dia_path) or !preg_match("/MSPLIT-DIA$/", $msplit_dia_path)){
     writeLog("ERROR:MSPLIT-DIA dir not found, Please check conf file." ,$task_infor['tasklog']);
     exit;
  }

  //print_r($task_infor);exit;
  //print_r($searchEngines_parameter_arr);
  //print_r($theTask_arr);
  
  if(!$MSPLIT_LIB){
    if(isset($task_infor['MSPLIT_FILE_arr']['DDA'])){
      echo "//1. MSPLIT_DDA search\n";
      writeLog("1. MSGFDB DDA search", $task_infor['statuslog']);
      DDA_MSGFDB_search($task_infor, $searchEngines_parameter_arr, $gpmDbFile);
      
    }
    if($allDDAFileAdded){
      echo "//2. Create_Spectral_Library from DDA results\n";
      writeLog("2. Create_Spectral_Library from DDA results", $task_infor['statuslog']);
      $MSPLIT_LIB = Create_Spectral_Library($task_infor, $MSPLIT_param_arr);
      
    }else{
      writeLog("Waiting for all DDA files to make spectral library.", $task_infor['statuslog']);
    }
  }
  
  if($MSPLIT_LIB){
    echo "//3. MSPLIT search\n";
    writeLog("3. MSPLIT DIA search", $task_infor['statuslog']);
    if(isset($task_infor['MSPLIT_FILE_arr']['DIA'])){
      MSPLIT_search($MSPLIT_LIB, $task_infor, $MSPLIT_param_arr, $gpmDbFile);
    }
    
    
    
    echo "//4. Make task output files\n";
    writeLog("4. Make task output files", $task_infor['statuslog']);
    make_task_outputs($task_infor);
  }
}
//****end*******************************************************************

function make_task_outputs($task_infor){
  $taskDir = $task_infor['taskDir'];
  $task_output_bash = $taskDir."/taskOutput.bash";
  $ResultsDir = $taskDir."/Results";
  $DIA_resultsDir = $taskDir."/DIA_Results";
  $command_arr[] = 'rm -f '.$ResultsDir . '/*';
  $command_arr[] = 'printf "Q1\tQ3\tRT_detected\tprotein_name\tisotype\trelative_intensity\tstripped_sequence\tmodification_sequence\tprec_z\tfrg_type\tfrg_z\tfrg_nr\tiRT\tuniprot_id\tscore\tdecoy\tprec_y\tconfidence\tshared\tNmods\tnterm\tcterm\n" > '. $ResultsDir.'/MSPLIT_Results_for_Peakview.txt';
  $command_arr[] = 'awk FNR!=1 '.$DIA_resultsDir.'/*Peakview_assaylib.txt >> '.$ResultsDir.'/MSPLIT_Results_for_Peakview.txt';

	$command_arr[] = 'printf "PrecursorMz\tProductMz\tTr_recalibrated\ttransition_name\tCE\tLibraryIntensity\ttransition_group_id\tdecoy\tPeptideSequence\tProteinName\tAnnotation\tFullUniModPeptideName\tMissedCleavages\tReplicates\tNrModifications\tPrecursorCharge\tGroupLabel" > '.$ResultsDir.'/MSPLIT_Results_for_Openswath.txt';
	$command_arr[] = 'awk FNR!=1 '.$DIA_resultsDir.'/*Openswath_assaylib.txt >> '.$ResultsDir.'/MSPLIT_Results_for_Openswath.txt';

	$command_arr[] = 'printf "PrecursorMz\tProductMz\tTr_recalibrated\ttransition_name\tCE\tLibraryIntensity\ttransition_group_id\tdecoy\tPeptideSequence\tProteinName\tAnnotation\tFullUniModPeptideName" > '.$ResultsDir.'/MSPLIT_Results_for_Skyline.txt';
	$command_arr[] = 'awk FNR!=1 '.$DIA_resultsDir.'/*Skyline_assaylib.txt >> '.$ResultsDir.'/MSPLIT_Results_for_Skyline.txt';
  $OK = make_command_file($command_arr, $taskDir, $task_output_bash);
 
  if($OK){
  //****************************************************
    run_search_on_local($task_output_bash, $task_infor);
  //****************************************************
  }else{
     writeLog("Error: cannot make file $task_output_bash", $task_infor['statuslog']);
  }
}

function MSPLIT_search($MSPLIT_LIB, $task_infor, $MSPLIT_param_arr, $db){
  global $msManager_link;
  global $tableName;
  global $resultTable;
  //it will run seach $max_cmd at the same time. 
  //run all commands in the same cluster node. 
  //if no cluster $max_cmd shold set to 1. $max_comd will be igored.
  $max_cmd = 4;
  $JAR_Command = get_jar_command('5G');
  $CP_Command = get_jar_command('4G', '-cp');
  
  $search_command = $JAR_Command . MSPLIT_JAR_PATH;
  $search_command_cp = $CP_Command . MSPLIT_JAR_PATH;
  $var_wind_file = $task_infor['paramFilePath'];

   
  $theTaskID = $task_infor['taskID'];
  $fdr = 0.01;
  $par_tol = 2.5;
  $fr_tol = 50;
  $rt = 0;
  
  if(isset($MSPLIT_param_arr['FDR'])){
    $fdr = $MSPLIT_param_arr['FDR'];
  }
  if(isset($MSPLIT_param_arr['parent_mass_tolerance'])){
    $par_tol = $MSPLIT_param_arr['parent_mass_tolerance'];
  }
  if(isset($MSPLIT_param_arr['fragment_mass_tolerance'])){
    $fr_tol = $MSPLIT_param_arr['fragment_mass_tolerance'];
  }
  if(isset($MSPLIT_param_arr['rt'])){
    $rt = $MSPLIT_param_arr['rt'];
  }
  
  //print_r($task_infor);exit;
  //print_r($MSPLIT_param_arr);
  
  $cmd_num = 0;
  $all_cmd = array();
  $filtered_files_arr = array();
  $total_files = count($task_infor['MSPLIT_FILE_arr']['DIA']);
  $file_num = 0;
  foreach($task_infor['MSPLIT_FILE_arr']['DIA'] as $theDIA_arr){
    $cmd_num++;
    $file_num++;
    unset($command_arr);
    $command_arr = array();
    
    $has_results = false;
    $WellID = $theDIA_arr[0];
    $theDIA_file_path = $theDIA_arr[1];
     
    $file_NameBase = pathinfo($theDIA_file_path, PATHINFO_FILENAME);
    $file_NameBase = $WellID ."_" . $file_NameBase;
    
    $output_base = $task_infor['taskDir']."/DIA_Results/" . $file_NameBase;
    $MSPLIT_output = $output_base."_MSPLIT.txt";
    $MSPLIT_output_filtered = $output_base."_MSPLITfiltered.txt";
    $msplit_bash = $task_infor['taskDir']."/msplit_".$WellID.".bash";
    
    
    
    writeLog("Lib Search: $theDIA_file_path", $task_infor['statuslog']);
     
    //1. MSPLIT library search
    if(_is_file($var_wind_file)){
      //the version 07192015 works for variable window
      $jar_path = dirname(MSPLIT_JAR_PATH)."/MSPLIT-DIAv07192015.jar";
      if(!_is_file($jar_path)){
        $jar_path = MSPLIT_JAR_PATH;
      }
      
      $command_arr[] = $JAR_Command . $jar_path . " $par_tol $fr_tol ". $task_infor['scanNum']. " " . $theDIA_file_path . " " . $MSPLIT_LIB . " ". $MSPLIT_output ." " . $var_wind_file; 
    }else{
      $command_arr[] = $search_command . " $par_tol $fr_tol 0 " . $theDIA_file_path . " " . $MSPLIT_LIB . " ". $MSPLIT_output; 
    }
    //2. Filtering search results
    $cmd = $search_command_cp . " UI.SWATHFilter -r ". $MSPLIT_output . " -o " . $MSPLIT_output_filtered . " -fdr ". $fdr;
    if($rt){
      $cmd .= " -rt ". $rt;
    }
    $command_arr[] =  $cmd;
    
    //3. generate quantification 
    $command_arr[] =  $search_command_cp. " UI.SWATHQuant -PeakviewInput 1 -Baitname $file_NameBase -Expname $file_NameBase -d $db -l $MSPLIT_LIB -r $MSPLIT_output_filtered -o $output_base";
    $command_arr[] =  $search_command_cp. " UI.SWATHQuant -OpenswathInput 1 -Baitname $file_NameBase -Expname $file_NameBase -d $db -l $MSPLIT_LIB -r $MSPLIT_output_filtered -o $output_base";
    $command_arr[] =  $search_command_cp. " UI.SWATHQuant -SkylineInput 1 -Baitname $file_NameBase -Expname $file_NameBase -d $db -l $MSPLIT_LIB -r $MSPLIT_output_filtered -o $output_base";
    $command_arr[] =  $search_command_cp. " UI.SWATHQuant -SAINTInput 1 -Baitname $file_NameBase -Expname $file_NameBase -d $db -l $MSPLIT_LIB -r $MSPLIT_output_filtered -o $output_base";
    $OK = make_command_file($command_arr, $task_infor['taskDir'], $msplit_bash);

    if($OK){
      $filtered_files_arr[] = array('WellID'=>$WellID, 'filteredFile'=>$MSPLIT_output_filtered);
      $all_cmd[] = $msplit_bash;
      if(($cmd_num >= $max_cmd) or $file_num >= $total_files){
        $cmd_num = 0;
        //print_r($all_cmd);exit;
        //print_r($filtered_files_arr);
        $run_in_background = false;
        $run_same_node = true;
        //********************************************************************************************************************************
        run_search_on_local($all_cmd, $task_infor, $task_infor['taskDir'], $task_infor['taskDir'], '', $run_in_background, $run_same_node);
        //********************************************************************************************************************************
        foreach($filtered_files_arr as $filtered_file){
          $ID = $filtered_file['WellID'];
          $filteredFile = $filtered_file['filteredFile'];
          if(_is_file($filteredFile)){
            if(!mysqli_ping($msManager_link)) {
              $managerDB = new mysqlDB(MANAGER_DB);
              $msManager_link  = $managerDB->link;
              //mysqli_query($msManager_link, "SET SESSION sql_mode = ''");
            }
            $SQL = "update $resultTable set DataFiles='".$filteredFile."', Date=now() where WellID='$ID' and TaskID='$theTaskID' and SearchEngines='MSPLIT'";
            mysqli_query($msManager_link, $SQL);
            writeLog($SQL);
          }else{
            writeLog("No MSPLIT resluts created: $filteredFile", $task_infor['statuslog']);
          }
        }
        unset($all_cmd);
        $all_cmd = array();
        unset($filtered_files_arr);
        $filtered_files_arr = array();
      }
    }else{
       writeLog("Error: cannot make file $msplit_bash", $task_infor['tasklog']);
    }
  }
}

function Create_Spectral_Library($task_infor, $MSPLIT_param_arr){
  if(isset($MSPLIT_param_arr['FDR'])){
    $FDR = $MSPLIT_param_arr['FDR'];
  }else{
    $FDR = '0.01';
  }
  if(isset($MSPLIT_param_arr['[decoy_fragment_mass_tolerane'])){
    $decoy_tol = $MSPLIT_param_arr['[decoy_fragment_mass_tolerane'];
  }else{
    $decoy_tol = '0.05';
  }
  $DDA_dir = $task_infor['taskDir']."/DDA/";
  $DDA_comlete = $task_infor['taskDir']."/DDA_complete/";
  $MSGFDB_merged_file = $task_infor['taskDir']."/MSGFDB_results.txt";
  $Spectral_library = $task_infor['taskDir']."/Spectral_library.mgf";
  $Spectral_library_log = $task_infor['taskDir']."/Spectral_library_log.txt";
  $Spectral_library_decoy = $task_infor['taskDir']."/Spectral_library_wDecoy.mgf";
  
  $makeLib_bash = $task_infor['taskDir']."/make_lib.bash";
  
  if(_is_file($Spectral_library_decoy) and filesize($Spectral_library_decoy) > 200){
    writeLog("Existing LIB: $Spectral_library_decoy", $task_infor['statuslog']);
    return $Spectral_library_decoy;
  }
  $CP_Command = get_jar_command('8G', '-cp');
  $search_command = $CP_Command . MSPLIT_JAR_PATH;
  //merged result file
  $command_arr[] = $search_command." org.Spectrums.TDAStat MSGFDB-Dir $DDA_comlete $MSGFDB_merged_file $FDR";
  //CreateSpectralLibrary
  $command_arr[] = $search_command." UI.CreateSpectralLibrary $DDA_dir $MSGFDB_merged_file $Spectral_library $Spectral_library_log";
  //Creating decoy spectral library
  $command_arr[] = $search_command." org.Spectrums.DecoySpectrumGenerator $Spectral_library $Spectral_library_decoy $decoy_tol";
  $OK = make_command_file($command_arr, $task_infor['taskDir'], $makeLib_bash);
  if($OK){
    //***********************************************************
    run_search_on_local($makeLib_bash, $task_infor);
    //***********************************************************
  }else{
    $Spectral_library_decoy = '';
    writeLog("Error: cannot make file $makeLib_bash", $task_infor['statuslog']);
  }
  if($Spectral_library_decoy and !_is_file($Spectral_library_decoy)){
    $Spectral_library_decoy = '';
    writeLog("Error: cannot create $Spectral_library_decoy", $task_infor['statuslog']);
  }else{
    writeLog("Created: $Spectral_library_decoy", $task_infor['statuslog']);
  }
  return $Spectral_library_decoy;
}
function DDA_MSGFDB_search($task_infor, $searchEngines_parameter_arr, $gpmDbFile){
  global $msManager_link;
  global $tableName;
  global $resultTable;
  
  //it will run seach $max_cmd at the same time. 
  //run all commands in the same cluster node. 
  //if no cluster $max_cmd shold set to 1. $max_comd will be igored.
  $max_cmd = 8;
  $JAR_Command = get_jar_command('5G');
  
  
  $cmd_num = 0;
  $all_cmd = array();
  $data_files_arr = array();
  $total_files = count($task_infor['MSPLIT_FILE_arr']['DDA']);
  $file_num = 0;
  
  
  $MC_DDA_Results_dir = dirname($task_infor['taskDir'])."/DDA_MSGFDB_Results";
  
  $Task_DDA_Results_dir = $task_infor['taskDir']."/DDA_Results";
  $Task_DDA_complete_dir = $task_infor['taskDir']."/DDA_complete";
  
  $mods_file = make_MSGFDB_mod_file($task_infor['taskDir'], $searchEngines_parameter_arr['MSGFDB']['mods_str']);
  
  $use_threads = 4;
 
  
  $theTaskID = $task_infor['taskID'];
  foreach($task_infor['MSPLIT_FILE_arr']['DDA'] as $theDDA_arr){
    //check DDA_Results/*_MSGFDB.txt.temp.tsv
    //and ../taskDir/DDA_Results/*_MSGFDB.txt.temp.tsv\
    
    $file_num++;
    
    $has_results = false;
    $WellID = $theDDA_arr[0];
    $theDDA_file_path = $theDDA_arr[1];
     
    $file_NameBase = pathinfo($theDDA_file_path, PATHINFO_FILENAME);
    $theMSGFDB = $file_NameBase."_MSGFDB.txt";
    
    $MC_tmp_DataFile = "$MC_DDA_Results_dir/$theMSGFDB";
    $tmp_DataFile = "$Task_DDA_Results_dir/$theMSGFDB";
    
    writeLog("MSGFDB: $theDDA_file_path", $task_infor['statuslog']);
    //echo $MC_tmp_DataFile;exit;
    
    if(_is_file("$MC_tmp_DataFile")){
      $cmd = "cp '$MC_tmp_DataFile'* ". "'$Task_DDA_Results_dir/'";
      system("$cmd 2>&1");
      writeLog("####". $cmd."\n###start time:". @date("Y-m-d H:i:s"), $task_infor['taskComFile']);
      if(!mysqli_ping($msManager_link)) {
        $managerDB = new mysqlDB(MANAGER_DB);
        $msManager_link  = $managerDB->link;
        mysqli_query($msManager_link, "SET SESSION sql_mode = ''");
      }
      $SQL = "update $resultTable set DataFiles='".$tmp_DataFile."', Date=now() where WellID='$WellID' and TaskID='$theTaskID' and SearchEngines='MSPLIT_DDA'";
      mysqli_query($msManager_link, $SQL);
      $cmd = "cp '$tmp_DataFile".".temp.tsv' ". "'$Task_DDA_complete_dir/'";
      system("$cmd 2>&1");
      writeLog("####". $cmd."\n###start time:". @date("Y-m-d H:i:s"), $task_infor['taskComFile']);
      writeLog($SQL);
    }else{
      $cmd_num++;
      $data_files_arr[] = array('WellID'=>$WellID, 'dataFile'=>$tmp_DataFile);
      //run MSGFDB search
      $MSGFDB_para = $searchEngines_parameter_arr['MSGFDB']['other_param'] . " -d ". $gpmDbFile;
      $cmd = "rm '$Task_DDA_Results_dir/$file_NameBase'*";
      exec("$cmd 2>&1", $output);
      writeLog("####". $cmd."\n###start time:". @date("Y-m-d H:i:s"), $task_infor['taskComFile']);
      $search_command = $JAR_Command . MSGFDB_BIN_PATH ."/MSGFDB.jar -s '".$theDDA_file_path."' -o ". "'$Task_DDA_Results_dir/$theMSGFDB' " .$MSGFDB_para . " -thread $use_threads";
      
      if(_is_file($mods_file)){
        $search_command .=  " -mod $mods_file";
      }
      $all_cmd[] = $search_command;
    }
    //print_r($all_cmd);exit;
    if($cmd_num and ($cmd_num >= $max_cmd or $file_num >= $total_files)){
      $run_in_background = false;
      $run_same_node = true;
      //-----------------------------------------------
      run_search_on_local($all_cmd, $task_infor, $task_infor['taskDir'], $task_infor['taskDir'], '', $run_in_background, $run_same_node);
      //-----------------------------------------------
      foreach($data_files_arr as $dataFile_arr){
        $WellID = $dataFile_arr['WellID'];
        $tmp_DataFile = $dataFile_arr['dataFile'];
        if(_is_file($tmp_DataFile)){
          //copy file to 
          $cmd = "cp '$tmp_DataFile'* ". "'$MC_DDA_Results_dir/'";
          system("$cmd 2>&1");
          writeLog("####". $cmd."\n###start time:". @date("Y-m-d H:i:s"), $task_infor['taskComFile']);
          $cmd = "cp '$tmp_DataFile".".temp.tsv' ". "'$Task_DDA_complete_dir/'";
          system("$cmd 2>&1");
          writeLog("####". $cmd."\n###start time:". @date("Y-m-d H:i:s"), $task_infor['taskComFile']);
          if(!mysqli_ping($msManager_link)) {
            $managerDB = new mysqlDB(MANAGER_DB);
            $msManager_link  = $managerDB->link;
          }
          $SQL = "update $resultTable set DataFiles='".$tmp_DataFile."', Date=now() where WellID='$WellID' and TaskID='$theTaskID' and SearchEngines='MSPLIT_DDA'";
          mysqli_query($msManager_link, $SQL);
          writeLog($SQL);
       }else{
         writeLog("no MSGFDB results for $tmp_DataFile", $task_infor['tasklog']);
       }
      }
      $cmd_num = 0;
      unset($all_cmd);
      $all_cmd = array();
      unset($data_files_arr);
      $data_files_arr = array();
    }
  }
}



function make_MSGFDB_mod_file($taskDir, $mods_str){
  $mods_file = "$taskDir/mods.txt";
  if($mods_str){
    $mods_str = str_replace(";;", "\n", $mods_str);
    $fp = fopen($mods_file, "w");
    fwrite($fp, $mods_str);
    fclose($fp);
  }
  return $mods_file;
}
function check_MSPLIT_isRunning($tableName, $taskID, $processID, $msManager_link, $task_Status=''){
  global $gpm_ip;
  //needs to modify for MSPLIT running in localhost.
  $taskTable = $tableName . "SearchTasks";
  $resultTable = $tableName . "SearchResults"; 
  $lines = array();
  $rt = true;
  $status = '';
  $MSPLIT_in_prohits = is_in_local_server('MSPLIT');
  $results_folder_path = '';
  
  
  if($MSPLIT_in_prohits){
    echo "<pre>";
    echo "MSPLIT is running on local server\n";
    print "TaskID: $taskID\n";
    print "Task PID: $processID\n";
    $GPM_datapath = get_local_gpm_archive_path($tableName, $taskID);
    $gpm_machine_dir = $GPM_datapath ."/". $tableName;
    $taskDir     = $gpm_machine_dir."/task". $taskID;
    $tasklog     = $taskDir."/task.log";
    $results_folder_path = "$taskDir/Results";
    echo "Task folder: $taskDir\n";
    
    $msplitLog_file = $taskDir."/msplit.log";
    if(_is_file($msplitLog_file)){
      print "Returned from MSPLIT log file\n";
      print "------------------------------\n";
      echo file_get_contents($msplitLog_file);
    }
  
    $rt = is_ps_running($processID, 'msplit_dia.bash', $taskDir);
   
    if($rt){
      print "\n>>>Task is running<<<";
      return 1;
    }else{
      print "\n>>>Task is not running<<<";
      
      if(_is_file("$taskDir/Results/MSPLIT_Results_for_Peakview.txt")){
        print "\n>>>Task has successfully run<<<";
        print "\n>>>RESULTS FOLDER: $taskDir/Results<<<\n";
        $status = 'Finished';
        $dh = opendir ("$taskDir/Results/");
        while ($file = readdir($dh) ) {
          if(preg_match('/MSPLITfiltered\.txt$/', $file, $matches) 
            or preg_match('/MSGFDBresults\.txt$/', $file, $matches)
            or preg_match('/^MSPLIT_Results_/', $file, $matches)){
            $lines[] = $file;
            echo "$file\n";
          }
        }
        closedir($dh);
      }else{
        print "\n>>>ERROR: RESULTS FOLDER $taskDir<<<";
        print "\nno MSPLIT_Results_for_Peakview.txt file created";
        $status = 'Error';
      }
    }
    echo "</pre>";
    
  }else{
    $http_gpm_cgi_dir = "http://" . $gpm_ip. GPM_CGI_DIR;
    $tpp_formaction = $http_gpm_cgi_dir . "/Prohits_TPP.pl";
  
  //echo $tpp_formaction; //to be removed
  //echo "<br>tpp_myaction==checkMSPLIT"; //to be removed
  
  
    $req = new HTTP_Request($tpp_formaction,array('timeout' => 18000,'readTimeout' => array(18000,0)));
    $req->setMethod(HTTP_REQUEST_METHOD_POST);
    $req->addHeader('Content-Type', 'multipart/form-data');
    $req->addPostData('tpp_myaction', 'checkMSPLIT');
    $req->addPostData('tpp_machine', $tableName);
    $req->addPostData('tpp_taskID', $taskID);
    $req->addPostData('MSPLIT_PID', $processID);
    if(!PEAR::isError($req->sendRequest())){
      $response1 = $req->getResponseBody();
      echo "<pre>";
      echo $response1 . "\n";
     
      if(preg_match('/>>>RESULTS FOLDER: (.+)<<</', $response1, $matchs)){
         $results_folder_path =  $matchs[1];
         $status = 'Finished';
      }else if(preg_match('/>>>ERROR: RESULTS FOLDER (.+)<<</', $response1, $matchs)){
         $results_folder_path =  $matchs[1];
         $status = 'Error';
      }else{
        //is running
        return $rt;
      }
      $lines = explode("\n", $response1);
    }else{ 
      echo $req->getMessage();
      $rt = false;
    }
    /*
    $MSPLIT_Results_folder = STORAGE_FOLDER."Prohits_Data/MSPLIT_results/$tableName/task".$taskID;
    if(!_is_dir($MSPLIT_Results_folder)){
      @mkdir($MSPLIT_Results_folder, 0700, true);
    }
    $download_to_log = $MSPLIT_Results_folder."/wget.log";
    */
  }
  
  if($lines){
    $Peakview_file = '';
    $Openswath_file = '';
    $Skyline_file = '';
    
    $fileID_arr = array();
    $results_file_arr = array();
    
    foreach ($lines as $line){
      if($status == 'Finished'){
        if($line == 'MSPLIT_Results_for_Peakview.txt'){
          $Peakview_file = $line;
        }else if($line == 'MSPLIT_Results_for_Openswath.txt'){
          $Openswath_file = $line;
        }else if($line == 'MSPLIT_Results_for_Skyline.txt'){
          $Skyline_file = $line;
        }else if(preg_match("/^([0-9]+).*MSPLITfiltered\.txt$/", $line, $matches)){
          $fileID_arr[] = $matches[1];
          $results_file_arr[] = $line;
        }else if(preg_match("/^([0-9]+).*MSGFDBresults\.txt$/", $line, $matches)){
          $fileID_arr[] = $matches[1];
          $results_file_arr[] = $line;
        } 
      }
    }
    if($task_Status != 'Finished'){
      for($i = 0; $i< count($fileID_arr); $i++){
        $full_fileName = $results_folder_path."/".$results_file_arr[$i];
        $SQL = "update $resultTable set DataFiles='$full_fileName', Date=now() where WellID='".$fileID_arr[$i]."' and TaskID='$taskID' and (SearchEngines='MSPLIT' or SearchEngines='MSPLIT_DDA')";
        //echo "$SQL\n";
        writeLog($SQL);
        mysqli_query($msManager_link, $SQL);
      }
      $SQL = "UPDATE $taskTable SET Status='$status' WHERE ID='$taskID'";
      //echo $SQL;
      mysqli_query($msManager_link, $SQL);
      writeLog($SQL);
    }
    if($status == 'Error'){
      writeLog($response1);
      echo "<h2>MSPLIT task Error. Please View Sarch task log for detail.</h2>";
    }else{
      echo "<h2>MSPLIT task has been successfully run</h2>";
    }
    writeLog("$tableName task ID:$taskID Status='$status'".@date("Y-m-d G:i:s"));
    $rt = false;
  }
  return $rt;
}
?>