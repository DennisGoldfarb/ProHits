<?php
set_time_limit(0);
ob_end_flush();
$dir= "/home/frank/";
$check = 1;
//echo "this file should be modified to use";
//exit;


echo "<pre>";flush();
remove_broken_links($dir);
if($check) echo "it is test only file will not be deleted.\n";
 
function remove_broken_links($dir, $max_level='', $curr_level=-1){
  //usage: remove_broken_links($dir, $max_level);
  //$dir = remove the broken likes in the folder
  //$max_level = process sub folder levels. default = all subfolders.
  $curr_level++;
  $dir = preg_replace("/\/$/", '', $dir);
  if($max_level !=='' and $max_level < $curr_level ) return;
  $dir_arr = @scandir($dir);
  if(!$dir_arr) return;
  foreach($dir_arr as $entry) { 
    if(strpos($entry, '.')===0) continue;
    $path = $dir . DIRECTORY_SEPARATOR . $entry;
    if (is_link($path) && !file_exists($path)) {
      echo "broken link: $path\n";
      flush();
	  if(!$check) @unlink($path);
    }else if(is_dir($path)){
      echo "go: $path\n";flush();
      remove_broken_links($path, $max_level, $curr_level);
    }
  }
  
}

?>