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

$php_file_name = "comparison_results_image.php";
require("../common/site_permission.inc.php");
ini_set("memory_limit","-1");

if(!$infileName){
  echo "no input file $infileName.";
  exit;
}

require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

include("analyst/comparison_results_export_inc.php");
//exit;
if(_is_file($filename_out)){
  export_raw_file($filename_out);
}
?>