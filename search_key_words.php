<?php
set_time_limit(0);
ini_set("memory_limit","-1");

//exit;

$parent = ".";
$mainArr = array();
array_push($mainArr, $parent);
$fileCount = 0;
$tmp_counter = 0;
$file_name_arr = array();

while(count($mainArr)){
  $parent = array_pop($mainArr);
  $curret_arr = scandir($parent);
  foreach($curret_arr as $curret_path){
    if($curret_path == "." || $curret_path == "..") continue;
    
    $full_path = $parent."/".$curret_path;   
    if(is_dir($full_path)){
      array_push($mainArr, $full_path);
      //echo " $full_path<br>";
    }else{
      if(!preg_match("/\.php$/i", trim($full_path))) continue;
      if(strstr($full_path,'BACKUP_')) continue;
      if(strstr($full_path,'conf.inc')) continue;
      if(strstr($full_path,'search_key_words.php')) continue;
      if(strstr($full_path,'common_fun.inc.php')) continue;
      
      
      /*if(!strstr($full_path,'.fasta')){
        continue;
      }else{
        echo "\$full_path=$full_path=========================================\n\n";
      }*/
      
      
      $fileCount++;
      $handle = @fopen("$full_path", "r");
      if($handle){
        $line_num = 0;
        $fund = 0;
        while(($buffer = fgets($handle)) !== false){
          $line_num++;
          $buffer = trim($buffer);
          if(preg_match("/auto_save_form.inc.php/i", $buffer)){
            $fund = 1;
            echo "$buffer----------$line_num-----\n";
          }
        }
        if($fund){
          echo ++$tmp_counter.". \$full_path=$full_path=========================================\n\n";
          $file_name_arr[] = $full_path;
        }
        fclose($handle);
      }
    }
  }
  if($fileCount > 5000){
    break;
  }
}
echo "\$tmp_counter=$tmp_counter<br>\n";
echo "\$fileCount=$fileCount<br>\n";
echo "Find in files\n";
echo "<pre>";
print_r($file_name_arr);
echo "</pre>";
echo "End_____________";
exit;
?>
