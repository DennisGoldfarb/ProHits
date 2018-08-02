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

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

//------------------------------------------------------------------------------
if($action == 'add'){
  edit_comparison_session($IDs, $ID_TYPE);
  //echo "Unclick to remove $ID_TYPE IDs from compare";
  exit;
}elseif($action == 'remove'){
  edit_comparison_session($IDs, $ID_TYPE, "remove");
  //echo "Click to add $ID_TYPE IDs to compare";
	exit;
}
//------------------------------------------------------------------------------

$Log = new Log();
$is_error = 0;
if(!$saint_ID) exit;

$saint_folder = STORAGE_FOLDER."Prohits_Data/SAINT_results/saint_$saint_ID/";
$bait_dat_file = $saint_folder."bait.dat";
$sait_log_file = $saint_folder."log.dat";
$sait_input_file_zip = $saint_folder."SAINT_input_files.zip";
//$Log->insert($AccessUserID,'NoteType',$frm_ID,$action,$Desc,$AccessProjectID);

if($theaction == 'export_BaitInput'){
  $SAINT_dir = "../TMP/SAINT_comparison/P_$AccessProjectID/";
  $export_file_dir = "../TMP/SAINT_comparison/P_$AccessProjectID/U".$AccessUserID."/";
  $tmp_bait_file = $export_file_dir."bait.dat";
//echo "$tmp_bait_file<br>";exit;

  if(_is_file($tmp_bait_file)){
    header("Cache-Control: public, must-revalidate");
    header("Content-Type: application/octet-stream");  //download-to-disk dialog
    header("Content-Disposition: attachment; filename=".basename($tmp_bait_file).";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: "._filesize($tmp_bait_file));
    ob_clean();
    readfile("$tmp_bait_file");
  }
  exit();
}elseif($theaction == 'export'){
  if(!_is_file($sait_input_file_zip)){
     $myshellcmd = "cd $saint_folder; zip SAINT_input_files.zip bait.dat inter.dat prey.dat";
     if(_is_file($saint_folder."log.dat")){
       $myshellcmd .= " log.dat";
     }
     $result = @exec($myshellcmd);
     if(!$result){
       $err_msg = "Can not create a zip file now. Please try it later.";
     }
  }
   
  if(_is_file($sait_input_file_zip)){
    header("Cache-Control: public, must-revalidate");
    header("Content-Type: application/octet-stream");  //download-to-disk dialog
    header("Content-Disposition: attachment; filename=".basename($sait_input_file_zip).";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: "._filesize($sait_input_file_zip));
    ob_clean();
    readfile("$sait_input_file_zip");
  }
  exit();
}else if($theaction == 'save_des' and $AUTH->Insert){
  $SQL = "update SAINT_log set Description='$frm_description' WHERE  ProjectID=$AccessProjectID and ID='$saint_ID'";
  $PROHITSDB->update($SQL);
}
$SQL = "SELECT ID, `Name`,`UserID`, `Date` , `Description`, `Status` , `ProjectID`, `ParentSaintID`, `UserOptions`
  FROM SAINT_log WHERE  ProjectID=$AccessProjectID and ID='$saint_ID'";
$saint_record = $PROHITSDB->fetch($SQL);
if(!$saint_record) exit;

$SearchEngine = '';
$saint_log_lines = '';
if(_is_file($sait_log_file)){
  $saint_log_lines = file($sait_log_file);
  foreach($saint_log_lines as $tmp_log_val){
    if(strstr($tmp_log_val, 'SearchEngine:')){
      $tmp_arr = explode(":",$tmp_log_val);
      $SearchEngine = trim($tmp_arr[1]);
      break;
    }
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
  alert("IDs have been added to comparison");
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
  <input type=hidden name=saint_ID value="<?php echo $saint_ID;?>">
  <input type=hidden name=theaction value="">  
  <table border=0 width=100% cellspacing="1" cellpadding=0 bgcolor='#a0a7c5'>    
    <tr>
      <td bgcolor="white" width=100%>
        <table align=center border=0 width=95% cellspacing="0" cellpadding=0>
          <tr>
            <td colspan='2' align="" ><span class=pop_header_text>SAINT input files</span>&nbsp; &nbsp; &nbsp; &nbsp;
            <a href="javascript: submit_form('export');"  title='download SAINT input files'><img src="../msManager/images/icon_download.gif" alt='download'></a>
            <a href="javascript: submit_form('export_BaitInput');"  title='download SAINT Bait input file'><img src="../msManager/images/icon_download.gif" alt='download'></a>
            </td>
          </tr>
          <tr>
            <td colspan='2' nowrap align=center height='1'><hr size=1></td>
          </tr> 
          <tr>
            <td><b>SAINT Name</b>:</td>
            <td><?php echo $saint_record['Name'];?></td>
          </tr>
          <tr>
            <td><b>User</b>:</td>
            <td><?php echo get_userName($PROHITSDB, $saint_record['UserID']);?></td>
          </tr>
          <tr>
            <td><b>Status</b>:</td>
            <td><?php  echo $saint_record['Status'];?></td>
          </tr>
          <tr>
            <td><b>SearchEngine</b>:</td>
            <td><?php echo $SearchEngine;?></td>
          </tr>
          <tr>
            <td><b>SAINT options</b>:</td>
            <td><?php  echo $saint_record['UserOptions'];?></td>
          </tr> 
          <tr>
            <td valign=top><b>Date</b>:</td>
            <td><?php  echo $saint_record['Date'];?>
             <?php 
            if($saint_record['ParentSaintID']){
              echo "<br><font color=#800000>Used the same input files from task ID: ".$saint_record['ParentSaintID'];
              echo "</font>";
            }
            ?>
            </td>
          </tr>
          <tr>
            <td valign=top><b>Description</b>:</td>
            <td><?php  
            if($theaction == 'modify_des' and $AUTH->Insert){
               echo "<textarea name='frm_description' cols='50' rows='5'>".$saint_record['Description']."</textarea>";
               echo "[<a href=\"javascript: submit_form('save_des')\" class=button>SAVE</a>]<br><br>";
            }else{
              echo nl2br($saint_record['Description']);
              if($AUTH->Insert){
                echo "<a href=\"javascript: submit_form('modify_des')\"  title='modify description'>
                <img src=\"./images/icon_view.gif\" border=0></a>";
              }
            }
            ?>
            </td>
          </tr>
          <tr>
            <td colspan=2>
              <table width=100% bgcolor="black" cellspacing="1" cellpadding="1">
              <tr>
              	<td colspan=3><font color="#FFFFFF"><b>Bait.dat file</b></font></td>
              </tr>
              <?php 
              if($saint_log_lines){              
                $saint_option_start = 0;
                $saint_option_arr = array();
                foreach($saint_log_lines as $log_line){
                  $log_line = trim($log_line);
                  $log_line = str_replace(",", ", ", $log_line);
                  if($log_line == "<OTHER_OPTIONS>") {$saint_option_start = 1; continue;}
                  if($log_line == "</OTHER_OPTIONS>"){$saint_option_start = 0; break;}
                  if($saint_option_start){
                    $line_arr = explode(":", $log_line);
                    $saint_option_arr[$line_arr[0]] = $line_arr[1];
                  }
                }
                $tmp_IDs = $saint_option_arr['SELECTED_ID']; 
                $ID_TYPE = $saint_option_arr['ID_TYPE'];
                $IDs = '';
                if($ID_TYPE == 'Sample'){
                  //$tmp_IDs = 10; 11; 8; 9; 12
                  $tmp_IDs_arr = explode(";",$tmp_IDs);
                  foreach($tmp_IDs_arr as $tmp_IDs_val){
                    if($IDs) $IDs .= ',';
                    $IDs .= trim($tmp_IDs_val);
                  }
                }else{
                  //$tmp_IDs = 6(8;9)|7(10;11)|8(12;13)
                  $tmp_IDs_arr = explode("|",$tmp_IDs);
                  foreach($tmp_IDs_arr as $tmp_IDs_val){
                    $tmp_arr = explode("(",$tmp_IDs_val);
                    if($IDs) $IDs .= ',';
                    $IDs .= trim($tmp_arr[0]);
                  }
                }
                foreach($saint_option_arr as $key => $saint_option){
                  if($key == 'SELECTED_ID') $key .= " [<a href=\"javascript:add_or_remove_IDs('$IDs','$ID_TYPE')\">add to comparison</a>]";
                  echo "<tr bgcolor='#cdcfad'>";
                  echo "<td>$key</td><td colspan=2>".$saint_option."</td>";
                  echo "</tr>";
                }
              }
              ?>
              <tr bgcolor="white">
              	<td colspan=3>&nbsp;</td>
              </tr>
              <tr>
              	<td colspan=3><font color="#FFFFFF"><b>Filters applied</b></font></td>
              </tr>
              <?php 
              if($saint_log_lines){              
                $saint_filter_start = 0;
                $saint_filter_arr = array();
                foreach($saint_log_lines as $log_line){
                  $log_line = trim($log_line);
                  if(strstr($log_line, "<Filters>")){$saint_filter_start = 1;continue;}
                  if(strstr($log_line, "</Filters>")){$saint_filter_start = 0; break;}
                  if($saint_filter_start){
                    $saint_filter_arr[] = trim($log_line);
                  }
                }
                foreach($saint_filter_arr as $key => $saint_filter){
                  if(strstr($saint_filter, "Coverage") || strstr($saint_filter, "Fequency")) $saint_filter .= "%";
                  echo "<tr bgcolor='#cdcfad'>";
                  echo "<td colspan='3'>$saint_filter</td>";
                  echo "</tr>";
                }
              }
              ?>
              
              <tr bgcolor="white">
              	<td colspan=3>&nbsp;</td>
              </tr>
              <tr bgcolor="#808080">
              	<td>Sample ID</td>
              	<td>SAINT bait name</td>
              	<td>Control</td>
              </tr>
              <?php 
              $bait_lines = array();
              if(_is_file($bait_dat_file)){
                $bait_lines = file($bait_dat_file);
              }
              foreach($bait_lines as $line){
                $line = trim($line);
                $rd_arr = explode("\t", $line);
                if(count($rd_arr)==3){
              ?>
              <tr bgcolor="#ffffff">
              	<td><?php echo $rd_arr[0];?></td>
              	<td><?php echo $rd_arr[1];?></td>
              	<td><?php echo $rd_arr[2];?></td>
              </tr>
              <?php 
                }
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
 