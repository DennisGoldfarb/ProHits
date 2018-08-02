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

require_once("../msManager/is_dir_file.inc.php");

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}
if(_is_file($file) and fnmatch("*.csv", $file)){
  header("Cache-Control: public, must-revalidate");
  //header("Pragma: hack");
  header("Content-Type: application/octet-stream");  //download-to-disk dialog
  header("Content-Disposition: attachment; filename=".basename($file).";" );
  header("Content-Transfer-Encoding: binary");
  header("Content-Length: "._filesize($file));
  readfile("$file");
  exit();
}
?>