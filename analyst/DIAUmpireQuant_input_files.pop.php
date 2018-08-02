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

$type_bgcolor = '#808040';
$pro_name_bgcolor = '#d1d0be';
$general_title_bgcol = '#b1b09e';
$bgcolor = "#f1f1ed";
$theaction = '';
$action = '';
 
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

if($action == 'add'){
  edit_comparison_session($IDs, $ID_TYPE);
  //echo "Unclick to remove $ID_TYPE IDs from compare";
  exit;
}

$Log = new Log();
$is_error = 0;
if(!$DIAUmpireQuant_ID) exit;

$DIAUmpireQuant_folder = STORAGE_FOLDER."Prohits_Data/DIAUmpireQuant_results/task_$DIAUmpireQuant_ID/Results/";
$bait_dat_file = $DIAUmpireQuant_folder."SAINTBait.txt";
$DIAUmpireQuant_log_file = $DIAUmpireQuant_folder."log.dat";
$DIAUmpireQuant_input_file_zip = $DIAUmpireQuant_folder."DIAUmpireQuant_input_files.zip";
//$Log->insert($AccessUserID,'NoteType',$frm_ID,$action,$Desc,$AccessProjectID);
if($theaction == 'export'){
  if(!_is_file($DIAUmpireQuant_input_file_zip)){
     $myshellcmd = "cd $DIAUmpireQuant_folder; zip DIAUmpireQuant_input_files.zip bait.dat inter.dat prey.dat";
     if(_is_file($DIAUmpireQuant_folder."log.dat")){
       $myshellcmd .= " log.dat";
     }
     $result = @exec($myshellcmd);
     if(!$result){
       $err_msg = "Can not create a zip file now. Please try it later.";
     }
  }
   
  if(_is_file($DIAUmpireQuant_input_file_zip)){
    header("Cache-Control: public, must-revalidate");
    header("Content-Type: application/octet-stream");  //download-to-disk dialog
    header("Content-Disposition: attachment; filename=".basename($DIAUmpireQuant_input_file_zip).";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: "._filesize($DIAUmpireQuant_input_file_zip));
    ob_clean();
    readfile("$DIAUmpireQuant_input_file_zip");
  }
  exit();
}else if($theaction == 'save_des' and $AUTH->Insert){
  $SQL = "update DIAUmpireQuant_log set Description='$frm_description' WHERE  ProjectID=$AccessProjectID and ID='$DIAUmpireQuant_ID'";
  $PROHITSDB->update($SQL);
}

$prohitsManagerDB = new mysqlDB(MANAGER_DB);

$SQL = "SELECT `ID`, 
               `Name`, 
               `UserID`, 
               `Date`, 
               `Description`, 
               `Machine`, 
               `SearchEngine`, 
               `TaskIDandFileIDs`, 
               `Status`, 
               `ProjectID`, 
               `UserOptions`, 
               `ProcessID` 
        FROM `DIAUmpireQuant_log` 
        WHERE ProjectID=$AccessProjectID 
        AND ID='$DIAUmpireQuant_ID'";
$DIAUmpireQuant_record = $PROHITSDB->fetch($SQL);
if(!$DIAUmpireQuant_record) exit;
$Machine = $DIAUmpireQuant_record['Machine'];

$control_id_arr = array();
$baint_name_arr = array();

$tmp_UserOptions_arr = explode("\n",$DIAUmpireQuant_record['UserOptions']);

foreach($tmp_UserOptions_arr as $val){
  if(stristr($val, 'SAINT_control_id_str=')){
    $tmp_control_arr = explode('=',$val);
    $tmp_control_arr2 = explode(',',$tmp_control_arr[1]);
    foreach($tmp_control_arr2 as $tmp_control_val2){
      $control_id_arr[] = $tmp_control_val2;
    }
  }
  if(stristr($val, 'SAINT_baint_name_str')){
    $tmp_control_arr = explode('=',$val);
    $tmp_control_arr2 = explode(',',$tmp_control_arr[1]);
    foreach($tmp_control_arr2 as $tmp_control_val2){
      $tmp_control_arr3 = explode('|',$tmp_control_val2);
      $baint_name_arr[$tmp_control_arr3[0]] = $tmp_control_arr3[1];
    }
  }
}

$rawf_taskID_arr = array();
$raw_info_arr = array();

$TaskIDandFileIDs_arr = explode(',',$DIAUmpireQuant_record['TaskIDandFileIDs']);
foreach($TaskIDandFileIDs_arr as $TaskIDandFileIDs_val){
  $tmp_arr = explode('|',$TaskIDandFileIDs_val); 
  $rawf_taskID_arr[$tmp_arr[1]] = $tmp_arr[0];
}

$keys_arr = array_keys($rawf_taskID_arr);
$raw_ID_str = implode(',',$keys_arr);
$task_ID_str = implode(',',array_unique($rawf_taskID_arr));

$ProhitsID_str = '';

if($raw_ID_str){
  $SQL = "SELECT `ID`,`FileName`,`ProhitsID` FROM $Machine WHERE ID IN ($raw_ID_str)";
  $tmp_arr = $prohitsManagerDB->fetchAll($SQL);
  foreach($tmp_arr as $tmp_val){
    $raw_info_arr[$tmp_val['ID']]['FileName'] = $tmp_val['FileName'];
    $raw_info_arr[$tmp_val['ID']]['ProhitsID'] = $tmp_val['ProhitsID'];
    if($ProhitsID_str) $ProhitsID_str .= ',';
    $ProhitsID_str .= $tmp_val['ProhitsID'];
    if(array_key_exists($tmp_val['ID'], $baint_name_arr)){
      $raw_info_arr[$tmp_val['ID']]['Bait_name'] = $baint_name_arr[$tmp_val['ID']];
    }else{
      $raw_info_arr[$tmp_val['ID']]['Bait_name'] = '';
    }
    if(in_array(intval($tmp_val['ID']), $control_id_arr)){
      $raw_info_arr[$tmp_val['ID']]['Control'] = 'C';
    }else{
      $raw_info_arr[$tmp_val['ID']]['Control'] = 'T';
    }
  }
}
$task_ID_Name_arr = array();
if($task_ID_str){
  $SQL = "SELECT `ID`,`TaskName` FROM ".$Machine."SearchTasks WHERE ID IN ($task_ID_str)";
  $tmp_arr = $prohitsManagerDB->fetchAll($SQL);
  foreach($tmp_arr as $tmp_val){
    $task_ID_Name_arr[$tmp_val['ID']] = $tmp_val['TaskName'];
  }
}
foreach($rawf_taskID_arr as $key => $val){
  if(isset($task_ID_Name_arr[$val])){
    $raw_info_arr[$key]['TaskName'] = $task_ID_Name_arr[$val];
    $raw_info_arr[$key]['TaskID'] = $val;
  }
}
?>
<html>
<head>
<title>Prohits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<STYLE type="text/css">
TD { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
</STYLE>

<script language="Javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language="JavaScript" type="text/javascript">
<!--
function modify_detail(base_id,DB_name){
  var selected_obj = document.getElementById(base_id);
  var selected_a_id = base_id + '_a';
  var selected_a_obj = document.getElementById(selected_a_id);
  queryString = "DB_name=" + DB_name + "&base_id=" + base_id + "&theaction=modify_single_detail";
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
  selected_obj.style.display = "block";
  selected_a_obj.innerHTML = '[-]';
}
function submit_form(thisAction){
  var theForm = document.del_form;
  theForm.theaction.value = thisAction;
  theForm.submit(); 
}
function add_or_remove_IDs(IDs,ID_TYPE){
  alert("Sample IDs " + IDs + " have been added to comparison");
  var action = 'add';
  queryString = "IDs=" + IDs + "&ID_TYPE=" + ID_TYPE + "&action=" + action;
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}
function processAjaxReturn(ret_html){
  document.getElementById("aa").innerHTML = ret_html;
}
//-->
</script>
</head>
<body>
  <form name="del_form" method=post action="<?php echo $PHP_SELF;?>">
  <input type=hidden name=DIAUmpireQuant_ID value="<?php echo $DIAUmpireQuant_ID;?>">
  <input type=hidden name=theaction value="">  
  <table border=0 width=95% cellspacing="1" cellpadding=1 bgcolor='' align=center> 
    <tr bgcolor="white">
            <td colspan='2' align="" ><span class=pop_header_text>DIAUmpireQuant input files</span>&nbsp; &nbsp; &nbsp; &nbsp;
            <!--a href="javascript: submit_form('export');"  title='download DIAUmpireQuant input files'><img src="../msManager/images/icon_download.gif" alt='download' border=0></a-->
            <hr size=1>
            </td>
          </tr>
    <tr>
      <td bgcolor="" width=100%>
        <table align=center border=0 width=100% cellspacing="1" cellpadding=1 bgcolor='#a0a7c5'>
          
          <!--tr bgcolor="white">
            <td colspan='2' nowrap align=center height='1'><hr size=1></td>
          </tr--> 
          <tr bgcolor="white">
            <td width=25% nowrap><b>DIAUmpireQuant Name</b>:</td>
            <td><?php echo $DIAUmpireQuant_record['Name'];?></td>
          </tr>
          <tr bgcolor="white">
            <td><b>Machine Name</b>:</td>
            <td><?php echo $DIAUmpireQuant_record['Machine'];?></td>
          </tr>
          <tr bgcolor="white">
            <td><b>SearchEngine</b>:</td>
            <td><?php echo $DIAUmpireQuant_record['SearchEngine'];?></td>
          </tr>
          <tr bgcolor="white">
            <td><b>ProjectID</b>:</td>
            <td><?php echo $AccessProjectName;?></td>
          </tr>          
          <tr bgcolor="white">
            <td><b>User</b>:</td>
            <td><?php echo get_userName($PROHITSDB, $DIAUmpireQuant_record['UserID']);?></td>
          </tr>
          <tr bgcolor="white">
            <td><b>Status</b>:</td>
            <td><?php  echo $DIAUmpireQuant_record['Status'];?></td>
          </tr>
          <tr bgcolor="white">
            <td><b>DIAUmpireQuant options</b>:</td>
            <td>
            <?php 
            $UserOptions = preg_replace('/,+/', "<br>", nl2br($DIAUmpireQuant_record['UserOptions']));
            echo str_replace(";","<br>", nl2br($UserOptions));
            ?>
            </td>
          </tr> 
          <tr bgcolor="white">
            <td valign=top><b>Date</b>:</td>
            <td><?php echo $DIAUmpireQuant_record['Date'];?></td>
          </tr>
          <tr bgcolor="white">
            <td valign=top><b>Description</b>:</td>
            <td><?php  
            if($theaction == 'modify_des' and $AUTH->Insert){
               echo "<textarea name='frm_description' cols='50' rows='5'>".$DIAUmpireQuant_record['Description']."</textarea>";
               echo "[<a href=\"javascript: submit_form('save_des')\" class=button>SAVE</a>]<br><br>";
            }else{
              echo nl2br($DIAUmpireQuant_record['Description']);
              if($AUTH->Insert){
                echo "<a href=\"javascript: submit_form('modify_des')\"  title='modify description'>
                <img src=\"./images/icon_view.gif\" border=0></a>";
              }
            }
            ?>
            </td>
          </tr>
          <tr bgcolor="white">
            <td colspan=3>
              <table width=100% bgcolor="black" cellspacing="1" cellpadding="1">
              <?php 
              if(_is_file($DIAUmpireQuant_log_file)){
                $DIAUmpireQuant_log_lines = file($DIAUmpireQuant_log_file);
                $DIAUmpireQuant_option_start = 0;
                foreach($DIAUmpireQuant_log_lines as $log_line){
                  $log_line = trim($log_line);
                  $log_line = str_replace(",", ", ", $log_line);
                  if($log_line == "<OTHER_OPTIONS>") {$DIAUmpireQuant_option_start = 1; continue;}
                  if($log_line == "</OTHER_OPTIONS>"){$DIAUmpireQuant_option_start = 0; break;}
                  if($DIAUmpireQuant_option_start){
                    $line_arr = explode(":", $log_line);
                    echo "<tr bgcolor='#cdcfad'>";
                    echo "<td>".$line_arr[0]."</td><td colspan=2>".$line_arr[1]."</td>";
                    echo "</tr>";
                  }
                }
              }
//echo "$ProhitsID_str<br>";
              ?>
              <tr bgcolor="white" height=30>
              	<td colspan=6 align="right">
                <a href="javascript:add_or_remove_IDs('<?php echo $ProhitsID_str?>','Sample')">add Sample ID to comparison</a></td>
              </tr>
              <tr bgcolor="#808080">
              	<td>Raw file Name (Raw file ID)</td>
                <td>Task Name (Task ID)</td>
              	<td>DIAUmpireQuant bait name</td>
                <td>Sample ID</td>
              	<td>Control</td>
              </tr>
              <?php foreach($raw_info_arr as $raw_info_key => $raw_info_val){?>
                <tr bgcolor="#ffffff">
                	<td><?php echo $raw_info_val['FileName'];?> (<?php echo $raw_info_key;?>)</td>
                  <td><?php echo $raw_info_val['TaskName'];?> (<?php echo $raw_info_val['TaskID'];?>)</td>
                	<td><?php echo $raw_info_val['Bait_name'];?></td>
                	<td><?php echo $raw_info_val['ProhitsID'];?></td>
                  <td><?php echo $raw_info_val['Control'];?></td>
                </tr>
                <?php 
                }
              ?>
              </table>            
            </td>
          </tr>
        </table> <br>
      </td> 
    </tr> 
  </table>
  </form>
</body>
</html>
 