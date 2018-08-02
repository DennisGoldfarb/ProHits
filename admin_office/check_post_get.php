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

error_reporting(E_ALL);
/*
echo '<pre> $_SERVER<br>';
print_r($_SERVER);
echo '<pre> $_ENV<br>';
print_r($_ENV);
*/
echo "<p><pre>";
if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
  echo 'Form actoin = Post $_POST<br>';
}else if( $_SERVER['REQUEST_METHOD'] == "GET"){
  echo 'Form action = Get <br>$_GET<br>';
  $request_arr = $_GET;
}else{
  echo 'Form action = PUT <br>$_PUT<br>';
  $request_arr = $_PUT;
}
echo "<p>";
print_r($request_arr);
echo "uploaded files<br>";
print_r($_FILES);
?>