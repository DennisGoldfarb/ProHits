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

require("../common/site_permission.inc.php"); 
$check_ip = false;

$download_cgi = "http://".STORAGE_IP."/msManager/download_backup_raw_file.php?SID=". session_id()."&prohits_host=".$_SERVER['HTTP_HOST']."&prohits_db=".PROHITS_DB."&";
if($check_ip){
  $SLRI_IPs = array("192.197.250", 
                    "192.197.251", 
                    "192.168.1", 
                    "206.248.102", 
                    "206.204.105",
                    "10.197.",
                    "38.");
        
  $err_msg = "<font color=red>This file only can be downloaded with in SLRI.<br>
       If your are using proxy to connect \"proxy.library.utoronto.ca port 8080\",
       you should change the setting.</font>";

  $inside_IP = false;
  $ip = getenv('REMOTE_ADDR');
  for($i = 0; $i < count($SLRI_IPs); $i++){
    if(strpos($ip, $SLRI_IPs[$i]) === 0 ){
      $inside_IP = true;
      break;
    }
  }
  if(!$inside_IP){
    echo $err_msg;
      exit;
   
  }
}

$query_str = ""; 
if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  if(strlen($query_str) > 0){
    $query_str .= "&";
  }
  $query_str .= "$key=$value";
} 
$query_str = $download_cgi . $query_str; 
header("location: $query_str");
?>
