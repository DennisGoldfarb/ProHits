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

set_time_limit(3600*2);

$menu_color = '#669999';
$frm_project_id = '';
$frm_m_name = '';
$frm_file_id_str = '';
$Band_ID = '';

define ("RESULTS_PER_PAGE", 20);
define ("MAX_PAGES", 5); //this is max page link to display

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require("msManager/classes/Storage_class.php");
require_once("analyst/status_fun_inc.php");

$managerDB = new mysqlDB(MANAGER_DB);

if(isset($theaction) && $theaction == "link_raw_file" && $frm_m_name && $frm_file_id_str){
  $group_ID_str = _get_not_saved_file_group_IDs($frm_m_name, $frm_file_id_str);
  $SQL = "UPDATE $frm_m_name SET
          ProhitsID='$Band_ID',
          User='$AccessUserID',
          ProjectID='$frm_project_id'
          WHERE ID='$group_ID_str'";
  $ret_val = $managerDB->update($SQL);
  
  if($ret_val){
    $tmp_str = str_replace(",", ";$frm_m_name:", $group_ID_str);
    $RawFile_value = "$frm_m_name:$tmp_str";
    $SQL = "UPDATE Band SET
            RawFile='$RawFile_value'
            WHERE ID='$Band_ID'";
    $HITSDB->update($SQL);
    
    if($itemType == "Bait"){
      $tmp_head = 'B';
    }elseif($itemType == "Experiment"){
      $tmp_head = 'E';
    }else{
      $tmp_head = 'S';
    }
    $base_id = $tmp_head.$item_ID;
    echo "@@**@@".$base_id."@@**@@";
    get_status($item_ID, $itemType);
    exit;
  }  
}
if(!$Band_ID) exit;

$SQL = "SELECT B.ID,       
       B.ExpID,
       B.LaneID,
       B.BaitID,
       B.Location,
       B.OwnerID,
       B.ProjectID,
       BT.GeneName,
       BT.LocusTag,
       BT.Tag,
       BT.GelFree, 
       E.Name as ExpName,
       L.LaneNum,
       L.LaneCode,
       L.GelID
       FROM Band B 
       LEFT JOIN Bait BT ON (B.BaitID=BT.ID) 
       LEFT JOIN Experiment E ON (B.ExpID=E.ID)
       LEFT JOIN Lane L ON (B.LaneID=L.ID)
       WHERE B.ID = '$Band_ID'";
$all_Info_arr = $HITSDB->fetch($SQL);

$all_Info_arr['GelName'] = '';
if(!$all_Info_arr['GelFree'] && $all_Info_arr['GelID']){
  $SQL = "SELECT `Name` FROM `Gel` WHERE `ID`='".$all_Info_arr['GelID']."'";
  $gel_Info_arr = $HITSDB->fetch($SQL);
  if($gel_Info_arr){
    $all_Info_arr['GelName'] = $gel_Info_arr['Name'];
  }
}
$project_id_name_arr = get_project_id_name_arr();
$user_permi_projects_id_name_arr = get_user_permited_project_id_name($PROHITSDB, $AccessUserID);
if(!$frm_project_id){
  $frm_project_id = $all_Info_arr['ProjectID'];
}
?>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="./site_style.css">
</head>
<style type="text/css">
.c { background-color:yellow; }
</style>
<body>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script type="text/javascript" src="../common/site_ajax.js"></script>

<script language="javascript"> 
var peptedeW = '';
function get_raw_file(theForm){
  if(theForm.frm_project_id.value == ''){
    //alert("Please select a project");
    //return;
  }else if(theForm.frm_m_name.value == ''){
    alert("Please select a machine");
    return;
  }
  var table = theForm.frm_m_name.value;
  var ProjectID = theForm.frm_project_id.value;
  if(!peptedeW.closed && peptedeW.location) {
    peptedeW.close();
  }
  var file = "./pop_raw_file_info.php?table=" + table + "&pro_access_ID_str=" + ProjectID;
  peptedeW = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=560,height=600');
  peptedeW.moveTo(1500,0);
  peptedeW.focus();
}

function link_file(theForm){
  if(theForm.file_full_name.value == ""){
    alert('Please pass a file name!');
    return false;
  }
  var Band_ID = theForm.Band_ID.value;
  var Table_FileID = theForm.RawFile.value;
  var frm_file_id_str = theForm.frm_file_id_str.value;
  var frm_m_name = theForm.frm_m_name.value;
  var frm_project_id = theForm.frm_project_id.value;
  var itemType = theForm.itemType.value;
  var item_ID = theForm.item_ID.value;
  var theaction = 'link_raw_file';
  queryString = "&Band_ID="+Band_ID+"&Table_FileID="+Table_FileID+"&frm_project_id="+frm_project_id+"&item_ID="+item_ID+"&itemType="+itemType+"&frm_file_id_str="+frm_file_id_str+"&frm_m_name="+frm_m_name+"&theaction="+theaction;
//alert(queryString);   
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function processAjaxReturn(rp){
  var ret_html_arr = rp.split("@@**@@");
  if(ret_html_arr.length == 3){
    var div_id = ret_html_arr[1];
    var div_id_a = div_id + "_a";
    //window.opener.document.getElementById(div_id).innerHTML = ret_html_arr[2];
    window.opener.document.getElementById(div_id_a).style.display = "none";
    window.opener.document.getElementById(div_id_a).innerHTML = '';
    window.opener. status_detail('<?php echo $item_ID?>','<?php echo $itemType?>');
    window.close();
    return;
  }
}

function clean_file_name(theForm){
  theForm.file_full_name.value = '';
}
</script>
<table border=0 width=90% cellspacing="1" align=center>
  <tr>
    <td align=center colspan=2>
    <font face="Arial" size="+2" color="#660000"><b>Link Prohits sample to raw file</b></font><br>
    <hr width="100%" size="1" noshade>
    </td>
  </tr>
  <tr>
    <td bgcolor="<?php echo $menu_color;?>" colspan=2>      
     <font face="Arial" size="3" color="#ffffff"><b>Sample information</b></font>
    </td>
  </tr>
  <tr>
    <td width=30%><font face=Arial size=2 color=#008000><b>Project Name:</b></font></td>
    <td><font face=Arial size=2 color=black>(<?php echo $all_Info_arr['ProjectID']?>) <?php echo $project_id_name_arr[$all_Info_arr['ProjectID']]?></font></td>
  </tr>
  <tr>  
    <td><font face=Arial size=2 color=#008000><b>Bait: </b></font></td>
    <td><font face=Arial size=2 color=black><?php echo ($all_Info_arr['BaitID'])?"(".$all_Info_arr['BaitID'].")":'';?> <?php echo $all_Info_arr['GeneName']?></font></td>
  </tr>
  <tr>  
    <td><font face=Arial size=2 color=#008000><b>Experiment:</b></font></td>
    <td><font face=Arial size=2 color=black><?php echo ($all_Info_arr['ExpID'])?"(".$all_Info_arr['ExpID'].")":""?> <?php echo ($all_Info_arr['ExpName'])?$all_Info_arr['ExpName']:"";?></font></td>
  </tr>
  <tr>  
    <td><font face=Arial size=2 color=#008000><b>Gel:</b></font></td>  
    <td>
    <font face=Arial size=2 color=black>
<?php if($all_Info_arr['GelFree']){
    echo "Gel free";
  }else{
    if($all_Info_arr['GelID']){
      echo '('.$all_Info_arr['GelID'].') '.$all_Info_arr['GelName'];
    }  
  }?>
    </font>
    </td>
  </tr>
<?php if(!$all_Info_arr['GelFree']){?>
  <tr>  
    <td><font face=Arial size=2 color=#008000><b>Lane (No.):</b></font></td>
    <td><font face=Arial size=2 color=black><?php echo $all_Info_arr['LaneCode']?> <?php echo ($all_Info_arr['LaneNum'])?"(".$all_Info_arr['LaneNum'].")":""?></font></td>
  </tr>
<?php }?>  
  <tr>  
    <td><font face=Arial size=2 color=#008000><b>Sample:</b></font></td>
    <td><font face=Arial size=2 color=black>(<?php echo $all_Info_arr['ID']?>) <?php echo $all_Info_arr['Location']?></font></td>
  </tr>
  <tr>
    <td bgcolor="<?php echo $menu_color;?>" colspan=2>      
     <font face="Arial" size="3" color="#ffffff"><b>Sample information</b></font>
    </td>
  </tr>
  <FORM NAME='link_file_frm' ACTION='<?php echo $_SERVER['PHP_SELF'];?>' METHOD='POST'>
  <input type='hidden' name='Band_ID' value='<?php echo $Band_ID?>'>
  <input type='hidden' name='frm_file_id_str' value='<?php echo $frm_file_id_str?>'>
  <input type='hidden' name='itemType' value='<?php echo $itemType?>'>
  <input type='hidden' name='item_ID' value='<?php echo $item_ID?>'>
  <tr>
    <td colspan=2>
    <DIV STYLE="display: block;border: #a0a7c5 solid 1px">
    <table border=0 width=100% cellspacing="1" align=center>
      <input type='hidden' ID="frm_project_id" name="frm_project_id" value='<?php echo $frm_project_id?>'>
    <tr>  
      <td width=30%><font face=Arial size=2 color=#008000><b>Machine Name:</b></font></td>
      <td>
        <font face=Arial size=2 color=black>
        <select ID="frm_m_name" name="frm_m_name" onchange="javascript: clean_file_name(this.form)">
          <option value=''>
          <?php foreach($BACKUP_SOURCE_FOLDERS as $key => $value){?>
          <option value='<?php echo $key?>' <?php echo (($key==$frm_m_name)?'selected':'')?>><?php echo $key?>
          <?php }?>
      	</select>
        </font>
      </td>
    </tr>
    <tr>      
      <td align=center colspan=2 >
        <font face=Arial size=2 color=black>
        <input type="button" value=" Get raw file info. " onClick="javascript: get_raw_file(this.form);">
        </font>
      </td>
    </tr>
    </table>
    </DIV>
   </td>
  </tr>
<?php 
if($frm_file_id_str && $frm_m_name){
  $path_arr = get_path_arr($frm_file_id_str,$frm_m_name);  
  if($path_arr){
    $file_full_name = '';
    $cou_2 = 0;
    while($path_arr){
      if($file_full_name) $file_full_name .= "/";
      $tmp_atr = array_pop($path_arr);
      $file_full_name .= $tmp_atr['FileName'];
      if($cou_2++ > 10) break;
    }
  }
  $notes = '';
  $SQL = "SELECT `User`, 
          `ProhitsID`,
          `ProjectID` 
          FROM $frm_m_name 
          WHERE `ID`='$frm_file_id_str'";
  $tmp_array = $managerDB->fetch($SQL);
  if($tmp_array){
    if($tmp_array['User'] && $tmp_array['ProhitsID'] && $tmp_array['ProjectID']){
      $projectID_DBname_pair_arr = get_projectID_DBname_pair($PROHITSDB, $tmp_array['ProjectID']);
      $current_DB = new mysqlDB($projectID_DBname_pair_arr[$tmp_array['ProjectID']]);
      $SQL_2 = "SELECT `ID`,
              `Location`,
              `ProjectID` 
              FROM `Band` 
              WHERE `ID`='".$tmp_array['ProhitsID']."'";
      $tmp_array2 = $current_DB->fetch($SQL_2);
      $notes = "Raw file $file_full_name had linked by (".$tmp_array2['ID'].") ".$tmp_array2['Location'];
    }
  }
  $tmp_raw_file = $frm_m_name.":".$frm_file_id_str;
  ?>
  <tr>      
    <td align=center colspan=2 >
      <DIV id="f_name_id" STYLE="display: block;border: #a0a7c5 solid 1px">
        <table border=0 width=100% cellspacing="2" align=center>
          <tr>  
            <td nowrap><font face=Arial size=2 color=#008000><b>raw file:</b></font></td>
            <td>
              <font face=Arial size=2 color=black>
              <input type="text" name="file_full_name" size="65" value="<?php echo $file_full_name?>" readonly>
              <input type="hidden" name="RawFile" value="<?php echo $tmp_raw_file?>">
              </font>
            </td>
          </tr>
  <?php if($notes){?>
          <tr>  
            <td nowrap><font face=Arial size=2 color=#008000><b>Notes:</b></font></td>
            <td>
              <font face=Arial size=2 color=black>
              <?php echo $notes?>
              </font>
            </td>
          </tr>
          <tr>      
            <td align=center colspan=2>
              <font face=Arial size=2 color=black>
              <input type="button" value="     Close     " onClick="javascript: window.close();">
              </font>
            </td>
          </tr>
  <?php }else{?>       
          <tr>      
            <td align=center colspan=2>
              <font face=Arial size=2 color=black>
              <input type="button" value="     Link file     " onClick="javascript: link_file(this.form);">
              </font>
            </td>
          </tr>
  <?php }?>        
          
        </table> 
      </DIV> 
    </td>
  </tr> 
<?php 
}else{
?>
            <input type="hidden" name="file_full_name" value="" readonly>
<?php 
}
?>
  </FORM>   
</table>
</body>
</html>
<?php 
function get_path_arr($ID,$table){
  global $managerDB;  
  $file_info_arr = array();  
  $item_ID = $ID;
  $cou = 0;
  while($item_ID){
    $cou++;
    $SQL = "SELECT `ID`,`FileName`,`FileType`,`FolderID` FROM $table WHERE ID='$item_ID'";
    $tmp_file_info_arr =$managerDB->fetch($SQL);
    array_push($file_info_arr, $tmp_file_info_arr);
    if($tmp_file_info_arr['FolderID']){
      $item_ID = $tmp_file_info_arr['FolderID'];
    }else{
      break;
    }
    if($cou>10) break; 
  }
  return $file_info_arr;
}


/*function _get_not_saved_file_group_IDs($tableName, $raw_ID){
  global $managerDB;
  $SQL = "select ID, FileName from $tableName where RAW_ID='". $raw_ID ."' and (ProhitsID=0 or ProhitsID is null)";
  $tmp_arr = $managerDB->fetchAll($SQL);
echo "<per>";  
print_r($tmp_arr);  
echo "</per>";  
  
  //$id_str = $raw_file_ID; 
  $id_str = $raw_ID;
  foreach($tmp_arr as $row){
    if($id_str) $id_str .= ",";
    $id_str .= $row['ID'];
  }
   
  $tableName .= "SearchResults";
  $SQL = "select WellID, SavedBy, TaskID from $tableName where WellID in ($id_str) and SavedBy>0";
  $tmp_arr = $managerDB->fetchAll($SQL);
  $saved_ID_arr = array();
  foreach($tmp_arr as $row){
    if(!in_array($row['WellID'], $saved_ID_arr)){
      array_push($saved_ID_arr, $row['WellID']);
    }
  }
  $tableName .= "tppResults";
  $SQL = "select WellID, SavedBy, TppTaskID from $tableName where WellID in ($id_str) and SavedBy>0";
  foreach($tmp_arr as $row){
    if(!in_array($row['WellID'], $saved_ID_arr)){
      array_push($saved_ID_arr, $row['WellID']);
    }
  }
  if($saved_ID_arr){
    $ID_arr = explode(",",$id_str);
    $new_ID_arr = array_diff($ID_arr, $saved_ID_arr);
    $id_str = implode(",", $new_ID_arr);
  } 
  return $id_str;
}*/

function _get_not_saved_file_group_IDs($tableName, $raw_ID){
  global $managerDB;
  $SQL = "select ID, FileName from $tableName where ID='". $raw_ID ."' and (ProhitsID=0 or ProhitsID is null)";
  $tmp_arr = $managerDB->fetch($SQL);
  if(!count($tmp_arr)) return '';
  $Results_table = $tableName."SearchResults";
  $SQL = "select WellID, SavedBy, TaskID from $Results_table where WellID='$raw_ID' and SavedBy>0";
  $tmp_arr = $managerDB->fetchAll($SQL);
  if(count($tmp_arr)) return '';
  $tpp_Results_table = $tableName."tppResults";
  $SQL = "select WellID, SavedBy, TppTaskID from $tpp_Results_table where WellID='$raw_ID' and SavedBy>0";
  $tmp_arr = $managerDB->fetchAll($SQL);
  if(count($tmp_arr)) return '';
  return $raw_ID;
}

 
?>