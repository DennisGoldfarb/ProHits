<?php /***********************************************************************
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


$download_to = "../../TMP/Protein_Download/";
$download_old = $download_to ."Download_old/";

function _file_exist($fileName){
  $file = basename($fileName);
  $directory = dirname($fileName);     
  System("ls -l $directory > ./files.txt");
  $fd = fopen("./files.txt", "r");
  while(!feof($fd)){
    $buffer = fgets($fd, 1000);
    $buffer = trim($buffer);
    if(!$buffer) continue;
    if(preg_match("/ $file$/", $buffer)){
      fclose($fd);
      return true;
    }
  }
  fclose($fd);
  return false;
}

function update_conf_file($item, $status='No'){
  global $download_conf;
  $download_conf_backup = './download_backup.conf';
  
  if(!copy($download_conf, $download_conf_backup)){
    return;
  }
  $lines = file($download_conf);
  $confHandle = fopen($download_conf, 'w');
  foreach($lines as $value){
    if(strstr($value, $item)){
      fwrite($confHandle, $item."=$status\r\n");
    }else{
      fwrite($confHandle, $value);
    }
  }
  fclose($confHandle);
}

function _is_writable($file){
  if(is_file($file)){
    return is_writable($file);
  }else{
    return is_writable(dirname($file));
  }
}
?>