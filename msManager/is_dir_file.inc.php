<?php
function dir_or_file($path){
  if(is_dir($path)){
    return 'dir';
  }else if(is_file($path)){
    return 'file';
  }else{// if(file_exists($path)){
    $output = array(); 
    $last_line = @exec("stat -c %F $path 2>/dev/null" , $output);
    
    if(preg_match("/[ ]*([a-zA-Z]+)$/", $last_line, $matches)){
      if(isset($matches[1])){
        if($matches[1] == 'file'){
          return 'file';
        }else if($matches[1] == 'directory'){
          return 'dir';
        }
      }
    }
  }
  return false;
}
function _is_dir($path){
  if(dir_or_file($path) == 'dir'){
    return true;
  }else{
    return false;
  }
}
function _is_file($path){
  if(dir_or_file($path) == 'file'){
    return true;
  }else{
    return false;
  }
}

function _filesize($path){
  if(is_file($path)){
    return filesize($path);
  }elseif(is_dir($path)){
    return get_lunux_folder_size($path);
  }else{// if(file_exists($path)){
    //-c = format
    //%s file size in bit
    //%F fileType
    $last_line = @exec("stat -c %s,%F $path 2>/dev/null");
    if($last_line){
      list($size, $fileType) = explode(",", $last_line, 2);
      if(trim($fileType) !=  "directory"){
        return $size;
      }else{
        return get_lunux_folder_size($path);
      }
    }  
  }
  return false;
}

function _filemtime($path){
  if(is_dir($path) || is_file($path)){
    return filemtime($path);
  }elseif(file_exists($path)){
    $last_line = @exec("stat -c %Y $path");
    if($last_line){
      return $last_line;
    }else{
      return false;
    }
  }
  return false;
}
function _mkdir_path($dir_path, $umask='0002'){
  if(!_is_dir($dir_path)){
    umask($umask);
    if (!mkdir(trim($dir_path), 0777, true)) {
      return false;
    }
  }
  /*
  $tmp_dir = "";
  $dirs = explode('/', trim($dir_path));
  if(!$dirs[0]) {
    $tmp_dir = "/";
  }
  foreach($dirs as $dir){
    $dir = trim($dir);
    if($dir){
      $tmp_dir .= $dir.'/';
      if($dir == '..' or $dir == '.'){ 
        continue;
      }
      if(!_is_dir($tmp_dir)){
        if(!mkdir($tmp_dir, 0755)){
          return false;
        }
      }
    }
  }
  */
  return true;
}
function _is_writable($path){
   if (_is_dir($path)){
      $path = add_end_slash($path);
      return _is_writable($path.uniqid(mt_rand()).'.tmp');
   }else if(_is_file($path) or preg_match("/\.tmp$/", $path, $matches)){
      if (!($f = @fopen($path, 'a+'))) return false;
      fclose($f);
      if(preg_match("/\.tmp$/", $path, $matches)){
        unlink($path);
      }
      return true;
    }else{
      return 0; // Or return error - invalid path...
    }
}

function get_lunux_folder_size($dir){
  $rt = 0;
  if(file_exists($dir)){
    $last_line = @exec("/usr/bin/du -sk \"$dir\"");
    $res = explode("\t", $last_line);
    $rt = $res[0];
  }
  return $rt;
}
//-------------------------------
function add_end_slash($dir=''){
//-------------------------------
  if($dir){
    if(!preg_match("/\/$/", $dir, $matches)){
      $dir .= "/";
    }
  }
  return $dir;
}
?>