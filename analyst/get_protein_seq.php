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
require("../config/conf.inc.php");
include("../common/mysqlDB_class2.php");
require("../common/common_fun.inc.php");

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}

if(!isset($protin_key) || !$protin_key){
  echo '';
  exit;
}
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
$AccessionType = '';
$protin_info = get_protin_info($protin_key, $AccessionType, $proteinDB);

if(!isset($protin_info['Sequence']) || !$protin_info['Sequence']){
  $protin_info = get_protein_from_url($protin_key);
  if(isset($protin_info['sequence']) && $protin_info['sequence']){
    echo $protin_info['sequence'];
  }else{
    echo '';
  }
}else{
  echo $protin_info['Sequence'];
}
?>