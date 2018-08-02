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
$myaction = '';
$frm_db = '';
$frm_hide='';
$setID = '';
$hide_db_arr = array();
$db_str = '';
$download_url = '';
 
include("./ms_permission.inc.php");
require("./common_functions.inc.php");
include("./autoSearch/auto_search_mascot.inc.php");
require("./is_dir_file.inc.php");


if($myaction == 'downloadDB'){
  $filePath = get_gpm_db_file_path($fileName);
  header("Content-Type: application/octet-stream");  //download-to-disk dialog
  header("Content-Disposition: attachment; filename=\"".basename($filePath)."\"");
  header("Content-Transfer-Encoding: binary");
  header("Content-Length: "._filesize($filePath));
  readfile("$filePath");
  exit();
}
$gpm_dbs = get_gpm_db_arr();
//print_r($gpm_dbs);
//if(defined("MASCOT_IP") and MASCOT_IP){
//  $download_url = "http://" . MASCOT_IP . MASCOT_CGI_DIR ."/ProhitsMascotParser.pl?theaction=stat&dbName=";
//}else{
  $download_url = "$PHP_SELF?myaction=downloadDB&fileName=";
//}
include("./ms_header_simple.php");
$gpm_dbs_descs = get_gpm_db_desc();
//print_r( $gpm_dbs_descs);
?>
 

<form name=listform method=post action=<?php echo $PHP_SELF;?>>
 
<table border="0" cellpadding="0" cellspacing="2" width=95%>
  <tr>
   
   <td><span class="pop_header_text">List of databases 
    </span> 
    <?php if($USER->Type == 'Admin'){?>
    display or hide databases
    <a href="pop_dbs.php">
      <img border="0" alt="Task detail" src="images/icon_view.gif">
    </a>
    
    <?php }?>
     <hr width="100%" size="1" noshade>
     <li>If a database cannot be seen from a new task, the database has been hidden by ProHits admin. A hidden database cannot be used for a new task, but old tasks will still be able to used the database.
     <li>The database information is manually added by Prohits administrator ("./pop_dbs_info.txt").
    </td>
   </tr>
  
   <TR>
    <TD align=center bgcolor=#50c5a5>&nbsp;</TD> 
   </TR>
   <TR>
    <TD bgcolor=#ffffff><pre>
<?php 
for($i = 0; $i < count($gpm_dbs['name']); $i++){
  echo "\n<b>". $gpm_dbs['label'][$i] ."</b> ". get_downlod_str($gpm_dbs['name'][$i])."\n";
  if(isset($gpm_dbs_descs[$gpm_dbs['label'][$i]])){
    echo $gpm_dbs_descs[$gpm_dbs['label'][$i]];
  }
}

?>
       
 
</pre>
    </TD>
   <tr>
</table>
<input type="button" onclick="window.close()" value=" Close " name="frm_save">   
</form>
<?php
include("./ms_footer_simple.php");
function get_downlod_str($db_name){
  $rt = '';
  global $download_url;
  $rt = "<A HREF='$download_url". $db_name."'>Download</A>\n";
  return $rt;
}
function get_gpm_db_desc(){
  $rt = array();
  $dbs_info_file = "./pop_dbs_info.txt";
  if(_is_file($dbs_info_file)){
    $lines = file($dbs_info_file);
    $db_name = '';
    foreach($lines as $line){
      $line = trim($line);
      if(!$line) continue;
      if(strpos($line, '#') === 0){
        $db_name = preg_replace("/[#]+/", '', $line);
        if($db_name){
          $rt[$db_name] = '';
        }
        continue;
      }
      if($db_name){
        $rt[$db_name] .= "$line\n";
      }
    }
  }
  return $rt;
}
?>
