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
function searchSEQUEST($raw_file_path, $WellID, $parameter_arr, $theTaskID, $selected_data_format){
  global $raw_file_pattern;
  global $tableName, $resultTable, $searchEngine_arr;
  global $msManager_link;
	global $frm_theURL;
  
  echo "SEQUEST search for $raw_file_path\n";
	
  $http_SEQUEST_cgi_dir = "http://" . SEQUEST_IP . '/Prohits_SEQUEST';
  if($selected_data_format != 'mzXML' and $selected_data_format != 'mzXML.gz' and $selected_data_format != 'mzML.gz'){
    $msg = "Warning: the file($raw_file_path) cannot be searched, since file extention is not zipped dta folder.";
    writeLog($msg);
    return;
  }
  $formaction = $http_SEQUEST_cgi_dir . "/Prohits_SEQUEST.pl";
  $req = new HTTP_Request($formaction,array('timeout' => 18000,'readTimeout' => array(18000,0)));
  $req->setMethod(HTTP_REQUEST_METHOD_POST);
  $req->addHeader('Content-Type', 'multipart/form-data');
  #$req->addHeader('Content-Type', 'text/html');
 
	$req->addPostData('SEQUEST_myaction', 'search');
	$req->addPostData('SEQUEST_machine', $tableName);
	$req->addPostData('SEQUEST_fileID', $WellID);
	$req->addPostData('SEQUEST_taskID', $theTaskID);
  $req->addPostData('SEQUEST_parameter', $parameter_arr);
  $req->addPostData('SEQUEST_mzXML_file', $raw_file_path);
  $req->addPostData('SID', 'rawDataConverter');
  $req->addPostData('download_from', dirname($frm_theURL)."/download_raw_file.php");
  
  if(DEBUG_SEARCH){
  	echo " send to SEQUEST search form\n";
	}
  $result = $req->sendRequest();
  if (!PEAR::isError($result)) {
    $response1 = $req->getResponseBody();
    if(DEBUG_SEARCH) {
      print_r("\n********return from $formaction***********\n");
			print_r($response1);
			print_r("\n*********end of form response*************\n");
    }
    if(strstr($response1, 'Error')){
      //$msg = Warning: GPM failed to read spectrum file, it is an unsupported data file type '$raw_file_path'.";
      writeLog($response1);
      return false;
    }
    if(preg_match('/>>>(.+)<<</', $response1, $matchs)){
      $tmp_DataFile = mysqli_escape_string($msManager_link, $matchs[1]);
      $SQL = "update $resultTable set DataFiles='".$tmp_DataFile."', Date=now() where WellID='$WellID' and TaskID='$theTaskID' and SearchEngines='SEQUEST'";
      writeLog($SQL);
      mysqli_query($msManager_link, $SQL);
    }else{
      writeLog($response1 . "\nreturned from SEQUEST file='$raw_file_path', taskID='$theTaskID'");
    }
  } else { 
    print $result->getMessage();
   	fatalError($result->getMessage() . " in included function: auto_search_sequest.inc.php", __LINE__);
  }
  return true;
}
?>
