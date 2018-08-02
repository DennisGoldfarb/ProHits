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

/*************************************************************
Description:
  1. It is a included file for auto_search_table_shell.php.
  2. It handles Mascot seach for a raw file.
  3. It will use pear/HTTP_Request. Since currently the pachage 
     doen't surpport mulitple selection one file has to be modified
     please read ./readme.txt.

**************************************************************/
function searchMascot($raw_file_path, $WellID, $parameter_arr, $LCQfilter_str, $theTaskID, $selected_data_format, $mgf_file_num=0){
  global $raw_file_pattern;
  global $tableName, $resultTable, $searchEngine_arr;
  global $msManager_link;
  global $debug;
  global $is_SWATH_file;
  global $mascot_dat_file_str;
  
 
  $mascot_session_ID = '';
  $mascot_session_ID = Mascot_session();
  if($mascot_session_ID === true){
     $mascot_session_ID = '';
  }else if($mascot_session_ID === false){
    writeLog("Cannot connect http://" . MASCOT_IP . MASCOT_CGI_DIR ."/login.pl. Please check the Mascot setting in ../config/conf.inc.php");
    return 0;
  }else if(!$mascot_session_ID){
    writeLog("Cannot login http://" . MASCOT_IP . MASCOT_CGI_DIR ."/login.pl. Please check the MASCOT_USER account in ../config/conf.inc.php");
    return 0;
  }
  echo "mascot search for $raw_file_path\n";
  $http_mascot_cgi_dir = "http://" . MASCOT_IP . MASCOT_CGI_DIR;
  
  
  $raw_file_type = ''; 
  $que_arr = array();
  if(!preg_match($raw_file_pattern, $raw_file_path, $tmp)){
    $msg = "Warning: the file($raw_file_path) cannot be searched, since file extention is no one of raw file in config/conf.inc.php file";
    //echo $msg;
    writeLog($msg);
    return 0;
  }else if(preg_match("/\.gz$/", $selected_data_format, $matches)){
    $new_raw_file_path = preg_replace("/\.gz$/", '', $raw_file_path);
    if(!_is_file($new_raw_file_path)){
      $cmd = "gunzip -c ".escapeshellarg($raw_file_path) .">". escapeshellarg($new_raw_file_path);
      prohits_exec($cmd);
    }
    $raw_file_path = $new_raw_file_path;
    $selected_data_format = preg_replace("/\.gz$/", '', $selected_data_format);
     
  }
  $raw_file_type = strtoupper($tmp[0]);
  
  $formaction = $http_mascot_cgi_dir . "/nph-mascot.exe?1";
  //$formaction = "http://xtandemserver.mshri.on.ca/thegpm-cgi/check_post_get.pl";
  //echo "\n".$formaction;exit;
   
  
  $req = new HTTP_Request($formaction, array('timeout' => 1000,'readTimeout' => array(1000,0)));
   
  $req->setMethod(HTTP_REQUEST_METHOD_POST);
  $req->addHeader('Content-Type', 'multipart/form-data');
  
  if($mascot_session_ID){
    $req->addCookie('MASCOT_SESSION', $mascot_session_ID);
  }
  echo $raw_file_path;
  $result = $req->addFile("FILE", $raw_file_path);
  //--------------------------
  $data['FILE'] = "@$raw_file_path"; 
  //--------------------------
  if (PEAR::isError($result)) {
    fatalError($result->getMessage() . " in included function: auto_search_mascot.inc.php", __LINE__);
  }
  
  //RAW, dta, mgf, mzData, mzXML";
  if($que_arr)  $req->addPostData('QUE_Prohits', $que_arr);
  //get mascot parameters from parameter set.
  $the_parm_format = '';
  $MODS = array();
  $IT_MODS = array();
  $DB = array();
  
  //-----------
  $mod_str = '';
  //-----------
  for($i = 0; $i < count($parameter_arr); $i++){
    $tmp_arr = explode("=", $parameter_arr[$i]);
    if(count($tmp_arr) == 2){
      if(($raw_file_type == '.RAW' and $tmp_arr[0] == 'INTERMEDIATE') or $tmp_arr[0] == 'FORMAT' or $tmp_arr[0] == 'FILE') continue;
      //if($tmp_arr[0] == 'MODS' or $tmp_arr[0] == 'IT_MODS' or $tmp_arr[0] == 'DB'){
      if($tmp_arr[0] == 'MODS' or $tmp_arr[0] == 'IT_MODS'){
        array_push($$tmp_arr[0], $tmp_arr[1]);
        $mod_str .= $tmp_arr[1];
      }else{
        $req->addPostData($tmp_arr[0], $tmp_arr[1]);
        //-----------------------------------------
        $data[$tmp_arr[0]] = $tmp_arr[1];
        //-----------------------------------------
      }
    }
  }
  
  
  if($MODS) $req->addPostData('MODS_Prohits', $MODS);
  if($IT_MODS) $req->addPostData('IT_MODS_Prohits', $IT_MODS);
  
  
  //$req->addPostData('DB_Prohits', $DB);
  
  
  if(strtoupper($selected_data_format) == 'MGF' or strtoupper($selected_data_format) == 'RAW'){
    $req->addPostData('FORMAT', 'Mascot generic');
    $data['FORMAT'] = 'Mascot generic';
  }else{
     if($raw_file_type == '.DTA'){// and strtoupper($selected_data_format) == 'DTA'){
        $req->addPostData('FORMAT', 'Sequest (.DTA)');
    }else if($raw_file_type == '.XML' and strtoupper($selected_data_format) == 'MZDATA'){
      $req->addPostData('FORMAT', 'mzData (.XML)');
    }else if($raw_file_type == '.XML' and strtoupper($selected_data_format) == 'BRUKER'){
      $req->addPostData('FORMAT', 'Bruker (.XML)');
    }else if($raw_file_type == '.PKL'){// and strtoupper($selected_data_format) == 'PKL'){
      $req->addPostData('FORMAT', 'Micromass (.PKL)');
    }else if($raw_file_type == '.ASC'){// and strtoupper($selected_data_format) == 'PKL'){
      $req->addPostData('FORMAT', 'Finnigan (.ASC)');
    }else if($raw_file_type == '.PKS'){// and strtoupper($selected_data_format) == 'PKL'){
      $req->addPostData('FORMAT', 'PerSeptive (.PKS)');
    }else{
      $msg = "Warning: Prohits cannot handle the file($raw_file_path) for Mascot auto-search!";
      //echo $msg ."\n";
      writeLog($msg);
      return false;
    }
  }
  echo "\nsend to mascot search form\n";
   
  //*************************
  $response = $req->sendRequest();
  
  //*****************************
  if (!PEAR::isError($response)) {
    $SQL = '';
    $response1 = $req->getResponseBody();
    
    echo "\n==========return from $formaction==========\n";
    print_r($response1);
    echo "\n==========end==========\n";
    
    if(preg_match('/<A HREF="\.\.\/cgi\/master_results.*\.pl\?file=(.+)">/', $response1, $matchs)){
     $tmp_DataFiles = $matchs[1];
     
     if($is_SWATH_file){
       if($mgf_file_num < 3){
        $mascot_dat_file_str .= $tmp_DataFiles.";";
        $tmp_DataFiles = '';
       }else if($mgf_file_num == 3){
         $tmp_DataFiles = $mascot_dat_file_str . $tmp_DataFiles.";";
       }
     }
     
     if($tmp_DataFiles){
      $SQL = "update $resultTable set DataFiles='$tmp_DataFiles', Date=now() where WellID='$WellID' and TaskID='$theTaskID' and SearchEngines='Mascot'";
      writeLog($SQL);
     }
    }else{
     $tmp_DataFiles = "rawFileError";
     writeLog($response1);
     
    }
    unset($response1);
    if($SQL){
      check_manager_db_connection();
      mysqli_query($msManager_link, $SQL);
    }else{
      return false;
    }
  }else { 
   echo "error".__LINE__;
   fatalError($response->getMessage() .$req->getResponseBody(). " in included function: auto_search_mascot.inc.php", __LINE__);
  }
  //print $response1; exit;
  return true;
}
//------------------------------------------------------
function Mascot_session($action='login', $sessionID=''){
//$mascot_sessionID = Mascot_session(); //login
//Mascot_session('logout', $sessionID);//logout the sess
//$mascot_session_ID = ture; security is disabled
//$mascot_session_ID = false; mascot URL error
//$mascot_session_ID is string ; loged in
//$mascot_session_ID = ; user account is not correct
//------------------------------------------------------
  $mascot_session_ID = '';
  $cookie_tmp_dir = dirname(dirname(dirname(__FILE__)))."/TMP/mascot_cookie/";
  if(!_is_dir($cookie_tmp_dir)) mkdir($cookie_tmp_dir);
  $cookie_file = $cookie_tmp_dir."cookie.txt";
  $out_file = $cookie_tmp_dir."out.txt";
  if($action == 'login'){
    if(!_is_dir($cookie_tmp_dir)){
      mkdir("$cookie_tmp_dir", 0755);
    }
    $com = "cd $cookie_tmp_dir; rm -f *; wget --timeout=10 --tries=3 --save-cookies=cookie.txt";
    $com .= " -o log.txt -O out.txt \"http://".MASCOT_IP. MASCOT_CGI_DIR."/login.pl";
    $com .= "?action=login&username=".MASCOT_USER."&password=".MASCOT_PASSWD."\"";
     
    
    $last_line = system($com);
    if(!($last_line === false) and _is_file($cookie_file)){
       $lines = file($cookie_file);
        
       preg_match("/MASCOT_SESSION\t(.+)/", $lines[count($lines)-1], $matches);
       if(isset($matches[1])){
           $mascot_session_ID = $matches[1];
       }else{
           $out_file_str = file_get_contents($out_file);
          
          preg_match("/Mascot security is not enabled/", $out_file_str, $matches);
          if($matches){
            $mascot_session_ID = true;
          }else{
             
            write_Log(implode("\n", $lines));
            $mascot_session_ID = false;
          }
       }
    }
  }else if($action == 'logout' and  $sessionID){
    $com = "cd $cookie_tmp_dir; wget --cookies=on --keep-session-cookies --save-cookies=cookie.txt";
    $com .= " -o log.txt -O out.txt \"http://".MASCOT_IP. MASCOT_CGI_DIR."/login.pl";
    $com .= "?action=logout&sessionID=$sessionID\"";
    system($com);
  }
  return $mascot_session_ID;
}

?>
