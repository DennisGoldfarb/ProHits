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
set_time_limit(2);

include ( "./autoBackup/shell_functions.inc.php");
include ( "./is_dir_file.inc.php");

$path = '';
$action = ''; 
$newDir = '';
$oldDir = '';

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach($request_arr as $key => $value) {
  $$key=$value;
}
if(!$path){
  echo "no path passed";exit;
}
if(!$action or $action == "list"){
  echo "check\n";
  echo list_dir($path);
}elseif($action == "create"){
  echo "create\n";
  echo create_dir($path, $newDir);
}elseif($action == "modify"){
  echo "modify\n";
  echo remove_dir($path, $oldDir)."\n";
  echo create_dir($path, $newDir);
}elseif($action == "remove"){
  echo remove_dir($path, $oldDir);
}elseif($action == "isDir"){
  echo isDir($path);
}elseif($action == "isEmpty_or_unexist"){
  echo isEmpty_or_unexist($path); 
}elseif($action == "isWritable"){
  echo is__writable($path);
}

/**************************
the dir should be readable
**************************/
function isDir($path){
  $rt = 0;
  if(_is_dir($path) and is_readable($path)){
    $rt = 1;
  }else{
    $rt = 0;
  }
  return $rt;
}
/*************************
print list of files
**************************/
function list_dir($path){
  $rt = '';
  if(_is_dir($path)){
    if($handle = opendir($path)){
      while($file = readdir($handle)){
        $rt .= "$path$file\n";
      }
    }
  }
  return $rt;
}

/***********************************
return 1 for success or error message
************************************/
function create_dir($path, $newDir){
  $rt = '';
  if(_is_dir($path) && $newDir){
    $newFullDir = $path.$newDir;
    $dirArr = array();
    if($handle = opendir($path)){
      while($file = readdir($handle)){
        $existDir = $path.$file;
        array_push($dirArr, $existDir);
      }
      if(in_array($newFullDir, $dirArr)){
        if(!is_emtpy_dir($newFullDir)){
          $rt = "backup destination folder $newDir already exist and is not empty.\n";
        }else{
          $rt = 1;
        }  
      }else{
        if(mkdir($newFullDir)){
          chmod($newFullDir, 0757); 
          $rt = 1;
        }else{
          $rt = "fail to created backup destination folder $newFullDir\n";
        }
      }
    }else{
      $rt = "path $path is not exist.\n";
    }
  }else{
    $rt = "path $path is not exist or new directory name is empty.\n";
  }
  return $rt;
}
/*********************************
return: 0-exist but not writable
        1-writable, 
        2-doesn't exist
**********************************/
function is__writable($path){
  $rt = '';
  if(file_exists($path)){
    if(is_writable($path) and _is_dir($path)){
      $rt = 1;
    }else{
      $rt = "0";
    }
  }else{
    $rt = 2;
  }
  return $rt;
}

/*************************************
only reove empty dir
return: 1-success
        or error message
**************************************/
function remove_dir($path, $oldDir){
  $rt = '';
  $oldFullDir = $path.$oldDir;
  if(_is_dir($path)){
    if(_is_dir($oldFullDir)){
      if(is_emtpy_dir($oldFullDir)){
        if($ret = rmdir($oldFullDir)){
          $rt = 1;
        }else{
          $rt =  "fail to remove backup destination folder $oldFullDir\n";
        } 
      }else{
        $rt = "backup destination folder $oldFullDir is not empty and can not be removed.\n";
      }
    }else{
      //$rt = "backup destination folder $oldFullDir is not exist.\n";
      $rt = 1;
    }
  }else{
    //$rt = "path $path is not exist.\n";
    $rt = 1;
  }
  return $rt;
}

/*********************************
inner function
**********************************/
function is_emtpy_dir($dirname){
  $rt=true; 
  $handle = opendir($dirname);
  while(($name = readdir($handle)) !== false){
    if($name!= "." && $name !=".."){ 
      $rt=false; 
      break;
    }
  }
  closedir($handle);
	return $rt; 
}

/**********************************
return: 0-not empty dir
        1-doesn't exist
        2-exist but empty
***********************************/
function isEmpty_or_unexist($path){
  $rt = '';
  if(!_is_dir($path)){
    $rt = 1;
  }else{
    if(is_emtpy_dir($path)){
      $rt = 2;
    }else{
      $rt = 0;
    }
  }
  return $rt;
}
?>
