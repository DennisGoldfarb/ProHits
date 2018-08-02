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

$task_ID = '';
$th_color = "#368981";
//-----------------------------
$gpm_version = '';
$mascot_version = '';
$tpp_version = '';
$sequest_version = '';
$converter_version = '';
$comet_version = '';
$msgfpl_version = '';
$diaumpire_version = '';
$msumpire_version = '';
$msplit_version = '';
$view_file = '';
//----------------------------

require("../common/site_permission.inc.php");
require("./is_dir_file.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

if($view_file){
  header("Content-Type: application/octet-stream");  //download-to-disk dialog
  header("Content-Disposition: attachment; filename=\"".basename($filePath)."\"");
  header("Content-Transfer-Encoding: binary");
  header("Content-Length: "._filesize($filePath));
  readfile("$filePath");
  //_output($filePath);
  exit;
}
require("./common_functions.inc.php");
 
$managerDB = new mysqlDB(MANAGER_DB);
if(!$task_ID){
 //display parameters
 if($checkSearchEngine == 'COMET'){
    $comet_default_parm_dir = preg_replace("/\/$/", "", COMET_BIN_PATH); 
    if(!_is_file($comet_default_parm_dir. '/comet.params.new')){
      $comet_cmd = $comet_default_parm_dir . '/comet.exe';
      system("cd ". $comet_default_parm_dir. "; $comet_cmd -p");
    }
    if(!_is_file($comet_default_parm_dir. '/comet.params.new')){
      $comet_default_parm_dir = dirname(__FILE__)."/autoSearch";
    }
    echo "<pre>";
    echo file_get_contents($comet_default_parm_dir. '/comet.params.new');
 }else if($checkSearchEngine == 'MSGFPL'){
    $msgfpl_command = 'java -jar '. preg_replace("/\/$/", "", MSGFPL_BIN_PATH) . '/MSGFPlus.jar';
    echo "<pre>";
    system("$msgfpl_command 2>&1");
 }else if($checkSearchEngine == 'MSGFDB'){
    $msgfdb_command = 'java -jar '. preg_replace("/\/$/", "", MSGFDB_BIN_PATH) . '/MSGFDB.jar';
    echo "<pre>";
    system("$msgfdb_command 2>&1");
 }elseif($checkSearchEngine == 'MSFRAGGER'){
    $FRAGGER_default_parm_dir = preg_replace("/\/$/", "", MSFRAGGER_BIN_PATH); 
    if(_is_file($FRAGGER_default_parm_dir. '/fragger.params')){
      echo "<pre>";
      echo file_get_contents($FRAGGER_default_parm_dir. '/fragger.params');
    }
 }
 exit;
}
 
$SQL = "SELECT `ID`, `PlateID`, `DataFileFormat`, `SearchEngines`, `Parameters`, `DIAUmpire_parameters`, `TaskName`, `LCQfilter`, `Schedule`, `StartTime`, `RunTPP`, `Status`, `ProcessID`, `UserID`, `ProjectID`, `AutoAddFile`
from $tableSearchTasks
where ID='$task_ID'";
              
$task_record = $managerDB->fetch($SQL);
$show_converter = 1;

$para_file_dir = "../TMP/Task_parameter_files";
if(!_is_dir($para_file_dir)){
  _mkdir_path($para_file_dir);
}
$para_file_name = $para_file_dir."/$task_ID.txt";

?>
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
  <html>
  <head>
  	<title></title>
  </head>
  <link rel="stylesheet" type="text/css" href="./ms_style.css">
  <script src="../common/javascript/prohits.divDropDown.js" type="text/javascript"></script> 
  <script language="javascript">
  function toggle_detail(base_id){
    var selected_obj = document.getElementById(base_id);
    var selected_a_id = base_id + '_a';
    var selected_a_obj = document.getElementById(selected_a_id);
    //var inner_str = trimString(selected_a_obj.innerHTML);
    var inner_str = selected_a_obj.innerHTML;
    if(inner_str == '+'){
      selected_obj.style.display = "block";
      selected_a_obj.innerHTML = '-';
    }else{
      selected_obj.style.display = "none";
      selected_a_obj.innerHTML = '+';
    }
  }
   
  </script>
  
<DIV style="border: black 1px solid; background-color: white; padding: 20px 0px 20px 0px;">  
  <body background=./images/site_bg.gif bgcolor=#d3d3d3" >
  <center>    
  <table cellspacing="1" cellpadding="0" border="0" width="760" bgcolor="#969696">
    <tr> 
      <td>
      <span class="pop_header_text" style="color: white; padding: 10px 30px 0px 10px;border: red 0px solid;">
         Search Engine Parameters 
      </span>
      <a href="<?php echo $_SERVER['PHP_SELF']?>?view_file=y&filePath=<?php echo $para_file_name?>" class=button><img src='./images/icon_download.gif' border=0></a>&nbsp;&nbsp;&nbsp;&nbsp;
      <div style="float: right;padding: 0px 5px 0px 0px;font-family: Georgia, Serif;">
      [<a id="all_search_Engine_a" href="javascript: toggle_all('all_search_Engine_a')" title='all search engine detail'>+</a>]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </div>
      </td>
    </tr>
    <tr>
      <td height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
    </tr>
 
<?php 
  $search_Engine_arr = print_task_parameters($task_record);
?>
  <tr>
      <td align='center'>
    <input type="button" name="frm_lastTask" value=" Close " onClick="window.close()">
      </td>
  </tr>
 </table>
 <script language="javascript">
 toggle_all('all_search_Engine_a');
 </script>
  
  
  </center>
</DIV>
</body>
</html>
 
