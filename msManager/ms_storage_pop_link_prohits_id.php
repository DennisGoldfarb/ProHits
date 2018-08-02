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

$raw_file_ID = 0; 
$frm_project_ID = 0;
$frm_bait_ID = 0;
$frm_exp_ID = 0;
$frm_gel_ID = 0;
$frm_lane_ID = 0;
$frm_sample_ID = 0;
$frm_confirmed = 0;
$frm_gel_mode = 1;
$gel_free_bait = false;

$oldProhitsID = '';
$saveBy = '';
$message = '';
$submit_disabled = '';

$tmp_db_name = '';
$addNewType = '';
$passed_Bait_ID = '';
$passed_Exp_ID = '';
$passed_Gel_ID = '';
$passed_Lane_ID = '';
$passed_Band_ID = '';
$message_flag = 0;
$tableName = '';
$tppR_table_name = '';

$menu_color = '#669999';
$msg = '';
$can_submitted = false;
$hitDB = '';
$permitted = '';
$Gel_ID = '';
$file_arr = array();
$analyst_project_insert = 0;

/////////for upload serach results //////////
$upload_search_results = '';
$frm_tppProt_xml = '';
$frm_tppPep_xml = '';
$error_msg = '';
$searchedDB = '';
$searchEngine = '';
$upload_to = "../TMP/uploaded_search_results/";
$logfile = '../logs/upload_search_results.log';
////////////////////////////////////////////
$link_type = '';

include("./ms_permission.inc.php");
require("./classes/Storage_class.php");
ini_set('memory_limit','2000M');

require_once("./common_functions.inc.php");   
require("./classes/saveConf_class.php"); 
include("./classes/xmlParser_class.php");
include("./autoSave/auto_save_tpp_shell_fun.inc.php");
include ( "./is_dir_file.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$tpp_task_arr = array();
if(isset($task_ID) && $task_ID && $tableName){
  $tppTaskTable = $tableName."tppTasks";
  $SQL = "SELECT `ID`,`SearchTaskID` FROM `$tppTaskTable` WHERE `SearchTaskID`='$task_ID'";
  $tmp_task_arr = $managerDB->fetchAll($SQL);
  foreach($tmp_task_arr as $tmp_task_val){
    if(!in_array($tmp_task_val['ID'], $tpp_task_arr)){
      array_push($tpp_task_arr, $tmp_task_val['ID']);
    }
  }
}

$SQL = "SELECT`ProjectID`,`UserID`,`Insert`,`Modify`,`Delete` FROM `ProPermission` WHERE `UserID`='".$_SESSION['USER']->ID."'";
$tmp_arr = $PROHITSDB->fetchAll($SQL);
$project_permission_arr = array();
foreach($tmp_arr as $tmp_val){
  $project_permission_arr[$tmp_val['ProjectID']] = $tmp_val;
}

if($perm_modify){
  $permitted = 'Y';
  $linkeder = "Linked By: ".$USER->Fname.'&nbsp;'.$USER->Lname;
}
if((!isset($tableName) or !isset($raw_file_ID) or !$raw_file_ID ) and !$upload_search_results){
  echo 'no raw file information passed'; exit;
}
if($passed_Bait_ID){
  $frm_bait_ID = $passed_Bait_ID;
}
if($passed_Exp_ID){
  $frm_exp_ID = $passed_Exp_ID;
}
if($passed_Gel_ID){
  $frm_gel_ID = $passed_Gel_ID;
}
if($passed_Lane_ID){
  $frm_lane_ID = $passed_Lane_ID;
}
if($passed_Band_ID){
  $frm_sample_ID = $passed_Band_ID;
}

if($upload_search_results){
  $error_msg = check_upload();
  if($error_msg)_error($error_msg);
  $proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);
  
  if($frm_sample_ID){
    $SQL = "select BaitID, ExpID, LaneID from Band where ID='$frm_sample_ID'";
    $band_record = $HITSDB->fetch($SQL);
    if($band_record){
      if(!$frm_bait_ID){
        $frm_bait_ID = $band_record['BaitID'];
        $frm_exp_ID = $band_record['ExpID'];
        $frm_lane_ID = $band_record['LaneID'];
        
        $SQL = "select GelFree from Bait where ID='$frm_bait_ID'";
        $bait_record = $HITSDB->fetch($SQL);
        if($bait_record){
          $frm_gel_mode = $bait_record['GelFree'];
          
          if(!$frm_gel_mode and $frm_lane_ID){
            $SQL = "select GelID from Lane where ID='$frm_lane_ID'";
            $gel_record = $HITSDB->fetch($SQL);
            if($gel_record) $frm_gel_ID = $gel_record['GelID'];
          }
        }
      }else if($frm_bait_ID != $band_record['BaitID']){
        $frm_bait_ID = 0;
        $frm_sample_ID = 0;
      }
    }else{
      $frm_bait_ID = 0;
      $frm_sample_ID = 0;
    }
  }
  $frm_project_ID = $AccessProjectID;
  $permitted = $AUTH->Insert;
  $hitDB = $HITSDB;
  $hitsDB = $HITSDB;
  if(!is_dir($upload_to)){
    if(!@mkdir($upload_to, 0755)){
      _error("Prohits TMP filder is no writable. Please contact Prohits Administrator");
    }
  }
  if($permitted and $theaction == 'saveTPP' and isset($_FILES['frm_tppProt_xml']) and $frm_sample_ID){
    $searchEngine = 'Uploaded';
    $message = '';
    $tmp_pepfilename = '';
    
    if(isset($_SERVER['CONTENT_LENGTH'])){
      $error_msg = check_upload('file_size');
    }
    if(!$error_msg and is_file($_FILES['frm_tppPep_xml']['tmp_name'])){
      $tmp_pepfilename = $_FILES['frm_tppPep_xml']['name'];
      $error_msg = check_file_type($_FILES['frm_tppPep_xml'], 'tppPep');
      if(!$error_msg){
        $error_msg = save_search_result_file($_FILES['frm_tppPep_xml'], $upload_to, $frm_sample_ID, 'tppPep');
        if(!$error_msg){
          $message .="<br>". $_FILES['frm_tppPep_xml']['name'];
        }
      }
    }
    if(!$error_msg and is_file($_FILES['frm_tppProt_xml']['tmp_name'])){
      $error_msg = check_file_type($_FILES['frm_tppProt_xml'], 'tppProt');
      if(!$error_msg){
        $error_msg = save_search_result_file($_FILES['frm_tppProt_xml'], $upload_to, $frm_sample_ID, 'tppProt', $tmp_pepfilename);
        if(!$error_msg){
          $message .="<br>". $_FILES['frm_tppProt_xml']['name'];
        }
      }
    }
    if($error_msg){
      $message = $error_msg;
    }else{
      $message = 'Following file(s) saved for sample: <br>'.$message;
    }
  }
  
}else{
  //--get project array------------------------------------------------
  $SQL = "select ID, Name, DBname from Projects order by ID";
  $rds = $prohitsDB->fetchAll($SQL);
  $project_arr = array();
  for($i=0; $i < count($rds); $i++){
    $project_arr[$rds[$i]['ID']]['Name'] = $rds[$i]['Name'];
    $project_arr[$rds[$i]['ID']]['DBname'] = $rds[$i]['DBname'];
  }  
  //--get user array---------------------------------------------------
  $SQL = "select ID, Fname, Lname from User";  
  $nameArr = $prohitsDB->fetchAll($SQL);
  $userFullName_arr = array();
  foreach($nameArr as $nameValue){
    $userFullName_arr[$nameValue['ID']] = $nameValue['Fname']." ".$nameValue['Lname'];
  }
  //get original info for tital----------------------------------------
  $fileObj_arr = array();
  $folderObj_arr = array();
  get_original_info($tableName,$raw_file_ID);  
  //---------------------------------------------------------------------
  $file_arr = array();
  get_file_array($tableName,$tppR_table_name,$raw_file_ID,$file_arr);
 
  $duplicate_prohits_rd = array();
  $oldProjectID = $file_arr['ProjectID'];
  $oldProhitsID = $file_arr['ProhitsID'];
  $oldUser = $file_arr['User'];
  $saveBy = $file_arr['SavedBy'];
  
  if(is_numeric($oldUser) && $oldUser>0 && $oldProhitsID){
    if(isset($userFullName_arr[$oldUser]) && $userFullName_arr[$oldUser]){
      $linkeder = "Linked By: ".$userFullName_arr[$oldUser];
    }else{
      $linkeder = "";
    }
  }elseif($oldProhitsID){
    $linkeder = "Auto linked";
  }else{
    $linkeder = '';
  }
  $submit_disabled = 0;
  $message = "";
}

if($theaction == 'savechange' and $frm_project_ID != '-1' and $permitted){
  if($oldProjectID != $frm_project_ID or ($frm_sample_ID !='-1' and $oldProhitsID != $frm_sample_ID)){
      $tmp_db_name = $project_arr[$frm_project_ID]['DBname'];
      $hitDB  = new mysqlDB($HITS_DB[$tmp_db_name]);
       
      $ret = update_file_table($tableName,$tppR_table_name,$raw_file_ID, $file_arr);
      if($ret){
        //update Band table
        $ret = update_band_table_add($tableName,$tppR_table_name,$raw_file_ID, $file_arr);
        if($oldProhitsID){
          //remove old link
          $tmp_hitDB = '';
          if($oldProjectID == $frm_project_ID){
            $tmp_hitDB = $hitDB;
          }else{
            $tmp_db_name = $project_arr[$oldProjectID]['DBname'];
            $tmp_hitDB  = new mysqlDB($HITS_DB[$tmp_db_name]);
          }
          update_band_table_remove($tableName,$tppR_table_name,$oldProhitsID,$raw_file_ID,$tmp_hitDB, $file_arr);
        }
      }
      closeWindow();
      exit;
  }else{
    $msg = "Nothing changed";
  }
}else if(($theaction == 'removelink' or $theaction == 'removelink_and_hits') and $oldProhitsID and $permitted){
  $tmp_db_name = $project_arr[$oldProjectID]['DBname'];
  $hitDB  = new mysqlDB($HITS_DB[$tmp_db_name]); 
  $project_ID_DBname = get_projectID_DBname_pair($hitDB);

  if($theaction == 'removelink_and_hits'){
    if($task_ID){
      remove_hits_and_peptide($tableName, $raw_file_ID, $task_ID);
    }  
    foreach($tpp_task_arr as $tpp_task_ID){
      remove_TppProtein_and_TppPeptide($tableName, $raw_file_ID, $tpp_task_ID);
    }
    $file_arr['this_passed_task_arr'] = array();  
  }
  if(!$file_arr['others_passed_task_arr'] && !$file_arr['this_passed_task_arr']){
    $ret = update_file_table_remove($tableName,$tppR_table_name,$raw_file_ID, $file_arr);
    if($ret){ 
      $ret_2 = update_band_table_remove($tableName,$tppR_table_name,$oldProhitsID,$raw_file_ID,$hitDB, $file_arr);
    }  
  }
  closeWindow();
  exit;
}else if($frm_sample_ID && $message_flag){
  if(!$frm_confirmed and !$upload_search_results){
    $tmp_db_name = $project_arr[$frm_project_ID]['DBname'];
    $hitDB  = new mysqlDB($HITS_DB[$tmp_db_name]);
    $SQL = "select RawFile from Band where ID='$frm_sample_ID'";
    $Band_info = $hitDB->fetch($SQL);
    if($Band_info and $Band_info['RawFile']){
      if(stristr($Band_info['RawFile'], 'tppResults')){//--LCQtppResults:1234
        $tmp_arr = explode(':',$Band_info['RawFile']);
        $message .= "<b>Warning:</b> The sample has linked to following merged raw files already.";
        $message .= " It cannot be linked to other raw file(s).</font>";
        $message .= "<br>Machine Name:<b>".$tmp_arr[0]."</b>";
        $message .= "<br>Storage ID: <b>".$tmp_arr[0]."</b>";
        $submit_disabled = 1;
      }else{
        $file_type_arr = array();
        $tmp_arr_1 = explode(';',$Band_info['RawFile']);
        foreach($tmp_arr_1 as $tmp_arr_1_value){
          $tmp_arr_2 = explode(':',$tmp_arr_1_value);
          if(!in_array($tmp_arr_2[0], $file_type_arr)){
            array_push($file_type_arr, $tmp_arr_2[0]);
          }
        }
        foreach($file_type_arr as $file_type_value){
          $SQL = "select ID, FileName, FolderID from $file_type_value where ProhitsID='$frm_sample_ID' and ProjectID='$frm_project_ID'";
          if($tmp_dup_arr = $managerDB->fetchAll($SQL)){
            $duplicate_prohits_rd[$file_type_value] = $tmp_dup_arr;
          }
        }
        $machineName_str = '';
        if($file_type_arr) $machineName_str = implode(",", $file_type_arr);
        if($duplicate_prohits_rd){
          if($tppR_table_name){
            $message .= "<b>Warning:</b>The sample has linked to following raw file(s) already.";
            $message .= " It cannot be linked to another merged raw file(s).</font>";
            foreach($duplicate_prohits_rd as $tmpKey => $tmpvalue){
              $message .= "<br>Machine Name: <b>".$tmpKey ."</b> ";
              foreach($tmpvalue as $rd){
                $message .= "<br>Storage ID: <b>".$rd['ID'] ."</b> ";
                $message .= "Name: <b>".$rd['FileName'] ."</b> ";
                $message .= "Storage Folder ID: <b>".$rd['FolderID'] ."</b>";
              }  
            }
            $submit_disabled = 1;
          }else{
            $message .= "<b>Warning:</b> The sample has linked to following raw file(s) already.Click";
            $message .= " \"Submit\" if you want to link this raw file as well. ";
						$message .= "If multiple raw files link one sample, those parsed hits will be merged in Prohits-Analyst.</font>";
            foreach($duplicate_prohits_rd as $tmpKey => $tmpvalue){
              $message .= "<br>Machine Name: <b>".$tmpKey ."</b> ";
              foreach($tmpvalue as $rd){
                $message .= "<br>Storage ID: <b>".$rd['ID'] ."</b> ";
                $message .= "Name: <b>".$rd['FileName'] ."</b> ";
                $message .= "Storage Folder ID: <b>".$rd['FolderID'] ."</b>";
              }
            }
          }
        }else{
          $message .= "Error: There is link in Band table and There is no link in $machineName_str table";
          $submit_disabled = 1;
        }
      }
    }
  }
}

if(!$frm_project_ID){
  if($oldProjectID){
    $frm_project_ID = $oldProjectID;
  }else{
    foreach($folderObj_arr as $folderObj1){
      if($folderObj1->ProjectID){
        $frm_project_ID = $folderObj1->ProjectID;
        break;
      }  
    }
  }
}

if($frm_project_ID > 0 and !$hitDB){
   $tmp_db_name = $project_arr[$frm_project_ID]['DBname'];
   $hitDB  = new mysqlDB($HITS_DB[$tmp_db_name]);
}

if(!$frm_bait_ID and $oldProhitsID and $hitDB){
  $SQL = "SELECT 
         B.ID, 
         B.ExpID,
         B.BaitID,
         B.ProjectID,
         BT.GelFree
         FROM Band B, Bait BT WHERE B.BaitID=BT.ID AND B.ID='".$oldProhitsID."'";
  $Sample_rd = $hitDB->fetch($SQL);
  if($Sample_rd){
    if($oldProjectID && $Sample_rd['ProjectID'] != $oldProjectID){
      //echo "Project are different between prohits DB and ms DB.";
    }
    $frm_bait_ID = $Sample_rd['BaitID'];
    $frm_exp_ID = $Sample_rd['ExpID'];
    $frm_gel_mode = $Sample_rd['GelFree'];
    $frm_sample_ID = $oldProhitsID;
  }
}
?>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="../analyst/site_style.css">
</head>
<style type="text/css">
.c { background-color:yellow; }
</style>

<body>
<script type="text/javascript" src="./ms.js"></script>
<script language="javascript"> 
function addSample(url){
  thispop = window.open(url,"changesample",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=' + 1000 + ',height=' + 600);
}
function changeProject(theForm){
  obj = theForm.frm_project_ID;
  if(obj.options[obj.selectedIndex].value == '-1'){
    alert('Please select a Project!');
    return false;
  }
  //theForm.frm_bait_ID.selectedIndex = '-1';
  <?php if($permitted){?>
  theForm.submit();
  <?php }?>
}
function changeBait(theItem){
  theForm = document.editform;
  obj = theForm.frm_bait_ID;
  if(obj.options[obj.selectedIndex].value == '-1'){
    alert('Please select a Bait!');
    return false;
  }
  <?php if($permitted){?>
  if(theItem.name == 'frm_sample_ID'){
     theForm.message_flag.value = 1;
  }
  theForm.submit();
  <?php }?>
}
function checkForm(theForm){
  pobj = theForm.frm_project_ID;
  bobj = theForm.frm_bait_ID;
  sobj = theForm.frm_sample_ID;
  
  if(bobj.options[bobj.selectedIndex].value == '-1'){
    alert('Please select a bait!');
    return false;
  }else if(sobj.options[sobj.selectedIndex].value == '-1'){
    alert('Please select a sample!');
    return false;
  }
  <?php if($oldProhitsID){?>
  if('<?php echo $oldProhitsID;?>' != sobj.options[sobj.selectedIndex].value){
    if(!confirm("The selected sample is different from origianl one.\n Are you sure that you wnat to save the change?")){
      return false;
    }
  }
  <?php 
  }
  $tmp_str = ($upload_search_results)?"saveTPP":"savechange";
  ?>
  theForm.theaction.value = '<?php echo $tmp_str?>';
  if(theForm.theaction.value == 'saveTPP' && isEmptyStr(theForm.frm_tppProt_xml.value)){
    alert("Please add protein prophet file!");
    return false;
  }else if(theForm.theaction.value == 'saveTPP' && isEmptyStr(theForm.frm_tppPep_xml.value)){
    if(!confirm("Are you sure that you don't want to add a peptide prophet file?")){
      return false;
    }
  }
  theForm.submit();
}
function removeLink(theForm){
  if(!confirm("Are you sure that you want to remvoe the link?")){
      return false;
  }
  theForm.theaction.value = 'removelink';
  theForm.submit();
}

function removeLink_Hits(theForm,tmp_link_lable){
  if(!confirm("Are you sure that you want to " + tmp_link_lable + "?")){
      return false;
  }
  theForm.theaction.value = 'removelink_and_hits';
  theForm.submit();
}
 
function change_gel_mode(mode){
  var theForm = document.editform;
  if(mode == theForm.pre_gel_mode.value) return;
   
  theForm.submit();
}
function pop_add_new(itemType){
  theForm = document.editform;
  var ProjectID = theForm.frm_project_ID.value;
  for(var i=0; i<theForm.frm_gel_mode.length; i++){
    if(theForm.frm_gel_mode[i].checked == true){
      var gelMode = theForm.frm_gel_mode[i].value;
      break;
    }
  }
  var querryStr = "?DBname=" + theForm.tmp_db_name.value;
  if(itemType == "Bait"){
    querryStr += "&theaction=addnew";
  }else if(itemType == "Exp"){
    var Bait_ID = theForm.frm_bait_ID.value;
    querryStr += "&theaction=addnew&Bait_ID=" + Bait_ID;
  }else if(itemType == "Gel"){
    var Bait_ID = theForm.frm_bait_ID.value;
    var Exp_ID = theForm.frm_exp_ID.value;
    querryStr += "&theaction=addnew&Bait_ID=" + Bait_ID + "&Exp_ID=" + Exp_ID;
  }else if(itemType == "Lane"){
    itemType = "Sample";
    var Bait_ID = theForm.frm_bait_ID.value;
    var Exp_ID = theForm.frm_exp_ID.value;
    if(gelMode == '0'){
      querryStr += "&theaction=addnew";
      var Gel_ID = theForm.frm_gel_ID.value;
    }else{
      querryStr += "&theaction=addnew";
      var Gel_ID = '';
    }
    querryStr += "&Bait_ID=" + Bait_ID + "&Gel_ID=" + Gel_ID + "&Exp_ID=" + Exp_ID; 
  }else if(itemType == "Sample"){
    var Bait_ID = theForm.frm_bait_ID.value;
    var Exp_ID = theForm.frm_exp_ID.value;
    if(gelMode == '0'){
      querryStr += "&theaction=addnew";
      var Gel_ID = theForm.frm_gel_ID.value;
      var Lane_ID = theForm.frm_lane_ID.value;
    }else{
      querryStr += "&theaction=addnew";
      var Gel_ID = '';
      var Lane_ID = '';
    }
    querryStr += "&Bait_ID=" + Bait_ID + "&Gel_ID=" + Gel_ID + "&Exp_ID=" + Exp_ID  + "&Lane_ID=" + Lane_ID;;
  } 
  querryStr += "&change_project=" + ProjectID + "&ProjectID=" + ProjectID + "&addNewType=" + itemType + "&gelMode=" + gelMode;
  //alert(querryStr);return;
  file = '../analyst/submit.php' + querryStr;
  newpopwin(file,800,550);
}
</script>
<form name=editform method=post action=<?php echo $PHP_SELF;?> enctype="multipart/form-data">
<input type=hidden name=raw_file_ID value='<?php echo $raw_file_ID;?>'>
<input type=hidden name=tableName value='<?php echo $tableName;?>'>
<input type=hidden name=tppR_table_name value='<?php echo $tppR_table_name;?>'>
<input type=hidden name=theaction value=''>
<input type=hidden name=addNewType value=''>
<input type=hidden name=Gel_ID value='<?php echo $Gel_ID?>'>
<input type=hidden name=change_project value=''>
<input type=hidden name=passed_Bait_ID value=''>
<input type=hidden name=passed_Exp_ID value=''>
<input type=hidden name=passed_Gel_ID value=''>
<input type=hidden name=passed_Lane_ID value=''>
<input type=hidden name=passed_Band_ID value=''>
<input type=hidden name=message_flag value=''>
<input type=hidden name=tmp_db_name value='<?php echo $tmp_db_name?>'>
<input type=hidden name=upload_search_results value='<?php echo $upload_search_results?>'>
<input type=hidden name=task_ID value='<?php echo $task_ID?>'>

<table border=0 width=90% cellspacing="5" align=center>
<tr>
  <td align=center colspan=2>
  <font face="Arial" size="+2" color="#660000"><b><?php echo ($upload_search_results)?"Upload Search Results":"Link Raw file to Prohits Sample";?></b></font><br>
  <font color=red><?php echo $msg;?></font>
  <hr width="100%" size="1" noshade>
  </td>
</tr>
<?php if(!$upload_search_results){?>
    <tr>
      <td bgcolor="<?php echo $menu_color;?>" colspan=2><font face="Arial" size="3" color="#ffffff"><b>Raw file information</b></td>
    </tr>
    <tr>
      <td colspan=2>
    <?php  
      $projectName = ''; 
      echo "<font face=Arial size=2 color=#008000>\r\n";
      echo "Machine Name: &nbsp;&nbsp;<font color=black>$tableName</font><br>\r\n";
      if($tppR_table_name){
        echo "Merged files IDs: <font color=black><b>$raw_file_ID</b></font><br>\r\n";
      } 
      foreach($folderObj_arr as $folderObj2){
        foreach($fileObj_arr[$folderObj2->ID] as $fileObj2){ 
          echo "Raw File&nbsp;&nbsp;<font color=black>".$folderObj2->FileName."</font>";
          echo "&nbsp;<b>/</b>&nbsp;<font color=#000000>".$fileObj2->FileName."</font><br>\r\n";
        }
        if(!$projectName && $folderObj2->ProjectID &&$project_arr[$folderObj2->ProjectID]['Name']){
          $projectName = $project_arr[$folderObj2->ProjectID]['Name'];
        }
      }
      echo "Folder Project: <font color=#ff0000>$projectName</font><br>\r\n";
      echo "</font>\r\n";
      ?>  
    </td></tr>
    <tr>
      <td bgcolor="<?php echo $menu_color;?>"><font face="Arial" size="3" color="#ffffff"><b>Link to Experiment Sample</b></font>&nbsp;&nbsp;&nbsp;<?php echo ($linkeder)?"<font face=Arial size=2 color=#660000>($linkeder)</font>":""?></td>
    </tr>
<?php }else{?>
  <tr>
    <td bgcolor="<?php echo $menu_color;?>"><font face="Arial" size="3" color="#ffffff"><b>Select Sample</b></font></td>
  </tr>
<?php }?>
<tr>  
  <td>
    <table border=0  width="100%">
      <tr>
        <td colspan=3 align=center>
  <?php if(!$oldProhitsID){?>       
          <input type=radio name='frm_gel_mode' value='0' <?php echo ($frm_gel_mode=='0')?"checked":""?> onclick="change_gel_mode(this.value)">Gel&nbsp;&nbsp;&nbsp;
          <input type=radio name='frm_gel_mode' value='1' <?php echo ($frm_gel_mode=='1')?"checked":""?> onclick="change_gel_mode(this.value)">Gel Free
          <input type=hidden name=pre_gel_mode value='<?php echo $frm_gel_mode?>'>
  <?php }?>     
        </td>
      </tr>
      <tr>
      <td width="20%" nowrap>
      <b><font face="Arial" size=2>Project Name:</font></b>
      </td>
      <td colspan=2>
 
<?php if($upload_search_results){
    echo $AccessProjectName;
    echo "<input type=hidden name=frm_project_ID value='$frm_project_ID'>\n";
  }else{
    if($oldProhitsID){
      echo "&nbsp;&nbsp;".$project_arr[$frm_project_ID]['Name'];
    }elseif(!$permitted){
      echo "&nbsp;&nbsp;".$project_arr[$frm_project_ID]['Name']."<br></td>";
      echo "<tr><td colspan=3 align=center>You don't have permission to set a link.<br> If you have to set a link please contact Prohits administrator.</td></tr>"; 
      echo "<tr><td colspan=3 align=center><input type='button' value='Close' onClick='window.close()';></td></tr>";
      exit;
    }else{
      if(isset($project_permission_arr[$frm_project_ID]['Insert']) && $project_permission_arr[$frm_project_ID]['Insert']){
        $analyst_project_insert = $project_permission_arr[$frm_project_ID]['Insert'];
      }  
    ?>
      <select name="frm_project_ID"  onChange="changeProject(this.form);">
      <option value='-1'>-- select project --
      <?php 
      foreach($pro_access_ID_Names as $tmp_pro_ID=>$tmp_pro_name){
        if(!array_key_exists($tmp_pro_ID, $project_permission_arr)) continue;
        if(!$project_permission_arr[$tmp_pro_ID]['Insert']) continue;
        $selected = ($tmp_pro_ID == $frm_project_ID)? " selected": "";
        echo "  <option value='$tmp_pro_ID'$selected>($tmp_pro_ID) $tmp_pro_name\n"; 
      }
      ?>
      </select>
  <?php }
  }
  ?>      
      </td>
      </tr>
    <?php 
    if($frm_project_ID > 0){
      $SQL = "select ID, GeneName, GelFree from Bait where ProjectID='$frm_project_ID' and GelFree='$frm_gel_mode' order by ID desc";
      $Bait_rds = $hitDB->fetchAll($SQL);
    ?>
      <tr>
        <td nowrap>
        <b><font face="Arial" size=2>Bait: </font></b>
        </td>
    <?php 
      $bait_selected = false;
      $exp_selected = false;
      $gel_selected = false;
      $lane_selected = false;
      $Bait_GeneName = '';
      if($oldProhitsID || !$permitted){
        if($frm_bait_ID) $bait_selected = true;
        foreach($Bait_rds as $Bait_value){
          if($Bait_value['ID'] == $frm_bait_ID){
            $Bait_GeneName = $Bait_value['GeneName'];
          }
        }
    ?>
          <td colspan=2>&nbsp;&nbsp;(<?php echo $frm_bait_ID?>)&nbsp;&nbsp;<?php echo $Bait_GeneName?></td>
    <?php }else{?>            
        <td>
        <select name="frm_bait_ID" onChange="changeBait(this);">
        <option value='-1'>--select bait--
        <?php 
        for($i = 0; $i < count($Bait_rds); $i++){
          if($frm_bait_ID == $Bait_rds[$i]['ID']){
            $selected = " selected";
            $bait_selected = true;
            $gel_free_bait = $Bait_rds[$i]['GelFree'];
          }else{
            $selected = '';
          }
          echo "<option value='".$Bait_rds[$i]['ID']."'$selected>(".$Bait_rds[$i]['ID'].") ".$Bait_rds[$i]['GeneName']. "\n";
        }
        if(!$bait_selected){
          $frm_sample_ID = '';
        }
        ?>
        </select>
        </td>
        <td width="30%"><a href="javascript: pop_add_new('Bait');" class=button><?php echo ($analyst_project_insert)?"[new]":"&nbsp;"?></a></td>
    <?php }?>      
      </tr>      
      <?php 
      if($frm_bait_ID > 0 and $bait_selected){ 
        //get all bait in the project
        $SQL = "select ID, Name from Experiment where BaitID='$frm_bait_ID' order by ID desc";
        $Exp_rds = $hitDB->fetchAll($SQL);
      ?>
        <tr>
        <td nowrap>
        <b><font face="Arial" size=2>Experiment: </font></b>
        </td>
      <?php if($oldProhitsID || !$permitted){
          $Exp_name = '';
          if($frm_exp_ID)$exp_selected = true;
          foreach($Exp_rds as $Exp_value){
            if($frm_exp_ID == $Exp_value['ID']) $Exp_name = $Exp_value['Name'];
          }
      ?>
        <td colspan=2>&nbsp;&nbsp;(<?php echo $frm_exp_ID?>)&nbsp;&nbsp;<?php echo $Exp_name?></td>
      <?php }else{?>        
        <td>
      <?php   if(count($Exp_rds) == 1){
            echo "&nbsp;&nbsp;".$Exp_rds[0]['Name'];
            echo "\n<input type=hidden name=frm_exp_ID value='".$Exp_rds[0]['ID']."'>";
            $frm_exp_ID = $Exp_rds[0]['ID'];
            $exp_selected = true;
          }else if(count($Exp_rds) > 1){
        ?>
          <select name="frm_exp_ID" onChange="changeBait(this);">
          <option value='-1'>--select experiment--
          <?php 
            for($i = 0; $i < count($Exp_rds); $i++){
              if($frm_exp_ID == $Exp_rds[$i]['ID']){
                $selected = " selected";
                $exp_selected = true;
              }else{
                $selected = "";
              }
               
              echo "<option value='".$Exp_rds[$i]['ID']."'$selected>(".$Exp_rds[$i]['ID'].") ".$Exp_rds[$i]['Name']. "\n";
            }
          ?>
          </select> 
          <?php 
          }else{
            echo "&nbsp;&nbsp;no experiment";
          }
        ?>
          </td>
          <!--td><input type="button" value="Add New Experiment" onClick="pop_add_new('Exp');"></td-->
          <td><a href="javascript: pop_add_new('Exp');" class=button><?php echo ($analyst_project_insert)?"[new]":"&nbsp;"?></a></td>
      <?php }?>      
        </tr>
      <?php if($frm_exp_ID > 0 and $exp_selected){
      ?>
      <tr>
        <td nowrap><b><font face="Arial" size=2>Gel: </font></b></td>
        <?php 
          if($frm_gel_mode){
            echo "<td colspan=2>";
            echo "&nbsp;&nbsp;Gel Free";
            echo "</td></tr>";
          }else{    
            if($oldProhitsID || !$permitted){
              $SQL = "SELECT
                     B.LaneID,
                     L.LaneNum,
                     L.LaneCode,
                     L.GelID,
                     G.Name     
                     FROM Band B, Lane L, Gel G
                     WHERE L.ID = B.LaneID and L.GelID = G.ID
                     AND B.ProjectID='$frm_project_ID' AND B.ID='$frm_sample_ID'";
              if($Gel_rds = $hitDB->fetch($SQL)){
                if($Gel_rds['GelID']){
                  $frm_gel_ID = $Gel_rds['GelID'];
                  $gel_selected = true;
                  $frm_lane_ID = $Gel_rds['LaneID'];
                  $lane_selected = true;
                }
                echo "<td colspan=2>";  
                echo "&nbsp;&nbsp;(".$Gel_rds['GelID'].")&nbsp;&nbsp;".$Gel_rds['Name']."&nbsp;&nbsp;&nbsp;<b>Lane:</b>&nbsp;&nbsp;".$Gel_rds['LaneCode']."&nbsp;&nbsp;<B>Lane No.</B>:&nbsp;".$Gel_rds['LaneNum'];
                echo "</td>";
              }else{
                echo "db error";
                //exit;
              }  
            }else{
              $gel_id_str = '';
              $SQL = "SELECT 
                     L.GelID, 
                     G.Name
                     FROM Band B, Lane L 
                     LEFT JOIN Gel G ON L.GelID=G.ID
                     WHERE L.ProjectID='$frm_project_ID' 
                     AND B.ExpID='$frm_exp_ID' 
                     AND B.LaneID=L.ID
                     GROUP BY L.GelID ORDER BY G.Name DESC";
              if($Gel_rds = $hitDB->fetchAll($SQL)){
                foreach($Gel_rds as $Gel_value){
                  if($Gel_value['GelID']){
                    if($gel_id_str) $gel_id_str .= ',';
                    $gel_id_str .= $Gel_value['GelID'];
                  } 
                }
              }
              if($gel_id_str){
                $SQL = "SELECT `ID`,`Name` FROM `Gel` WHERE `ProjectID`='$frm_project_ID' AND `ID` NOT IN($gel_id_str) ORDER BY ID DESC";
              }else{
                $SQL = "SELECT `ID`,`Name` FROM `Gel` WHERE `ProjectID`='$frm_project_ID' ORDER BY ID DESC";
              }
              $Gel_rds_2 = $hitDB->fetchAll($SQL);
            ?>
            <td>
            <?php if($Gel_rds || $Gel_rds_2){?>
              <select name="frm_gel_ID" onChange="changeBait(this);">
              <option value='-1'>--select gel--
              <?php foreach($Gel_rds as $Gel_value){
                  if($frm_gel_ID == $Gel_value['GelID']){
                    $selected = " selected";
                    $gel_selected = true;
                  }else{
                    $selected = "";
                  }
              ?>
                <option value='<?php echo $Gel_value['GelID']?>' <?php echo $selected?> class='c'>(<?php echo $Gel_value['GelID']?>)<?php echo $Gel_value['Name']?>
              <?php }?> 
              <?php foreach($Gel_rds_2 as $Gel_value){
                  if($frm_gel_ID == $Gel_value['ID']){
                    $selected = " selected";
                    $gel_selected = true;
                  }else{
                    $selected = "";
                  }
              ?>
                <option value='<?php echo $Gel_value['ID']?>' <?php echo $selected?>>(<?php echo $Gel_value['ID']?>)<?php echo $Gel_value['Name']?>
              <?php }?> 
              </select>
            <?php }else{
                echo "&nbsp;&nbsp;no gel";
              }?>  
            </td>
            <td width="40%"><a href="javascript: pop_add_new('Gel');" class=button><?php echo ($analyst_project_insert)?"[new]":"&nbsp;"?></a></td>
            </td>
          <?php }?>
            </tr>
          <?php if($frm_gel_ID > 0 and $gel_selected && !$oldProhitsID){?>
            <tr>
              <td nowrap><b><font face="Arial" size=2>Lane: </font></b></td>
              <td>
            <?php if(!$oldProhitsID and $permitted){
                $SQL = "SELECT
                     ID,
                     LaneNum,
                     LaneCode  
                     FROM Lane
                     WHERE GelID='$frm_gel_ID' 
                     AND ExpID='$frm_exp_ID'
                     AND ProjectID='$frm_project_ID'";
                if($Lane_rds = $hitDB->fetchAll($SQL)){
             ?>
                <select name="frm_lane_ID" onChange="changeBait(this);">
                  <option value='-1'>--select lane--
             <?php    foreach($Lane_rds as $Lane_value){
                    if($frm_lane_ID == $Lane_value['ID']){
                      $selected = " selected";
                      $lane_selected = true;
                    }else{
                      $selected = "";
                    }                
               ?>
                  <option value='<?php echo $Lane_value['ID']?>' <?php echo $selected?>>(<?php echo $Lane_value['LaneNum']?>)<?php echo $Lane_value['LaneCode']?>(<?php echo $Lane_value['ID']?>)
                <?php }?> 
                </select>
              <?php }else{
                  echo "&nbsp;&nbsp;no lane";
                }?>
              </td>
              <td width="40%"><a href="javascript: pop_add_new('Lane');" class=button><?php echo ($analyst_project_insert)?"[new]":"&nbsp;"?></a></td>
            </tr>  
            <?php }?> 
          <?php }?>
        <?php }?> 
      <?php }
        if($exp_selected and $frm_exp_ID && ($frm_gel_mode || ($gel_selected && $frm_gel_ID && $lane_selected && $frm_lane_ID))){
          if($frm_gel_mode){
            $SQL = "select ID, Location, RawFile from Band where ExpID='$frm_exp_ID' order by ID";
          }else{
            $SQL = "SELECT
                   B.ID,
                   B.Location,
                   B.RawFile,
                   P.PlateID
                   FROM Band B LEFT JOIN PlateWell P ON B.ID=P.BandID
                   WHERE B.LaneID='$frm_lane_ID'
                   AND B.ProjectID=$frm_project_ID";
          }
          $Sample_rds = $hitDB->fetchAll($SQL);
      ?>
          <tr>
          <td nowrap><b><font face="Arial" size=2>Sample: </font></b></td>
        <?php if($oldProhitsID || !$permitted){ 
            $Sample_name = '';
            foreach($Sample_rds as $Sample_value){
              if($Sample_value['ID'] == $oldProhitsID){
                $Sample_name = $Sample_value['Location'];
              }
            }
        ?>    
          <td colspan=2>&nbsp;&nbsp;(<?php echo $frm_sample_ID?>)&nbsp;&nbsp;<?php echo $Sample_name?></td>
        <?php }else{?>                  
          <td>
        <?php  
          if(count($Sample_rds) > 0){
        ?>
          <select name="frm_sample_ID" onChange="changeBait(this);">
          <option value='-1'>--select sample--
          <?php 
            for($i = 0; $i < count($Sample_rds); $i++){
              $can_submitted = true;
              if($frm_sample_ID == $Sample_rds[$i]['ID']){
                $selected = " selected";
              }else{
                $selected = "";
              }
              $style = '';
              if($Sample_rds[$i]['RawFile']){
                $style = "class=c";
              }
              $plateLable = '';
              if(!$frm_gel_mode) $plateLable = "(Plate_".$Sample_rds[$i]['PlateID'].")";;
              echo "<option value='".$Sample_rds[$i]['ID']."'$selected $style>(".$Sample_rds[$i]['ID'].") ".$plateLable.$Sample_rds[$i]['Location']. "\n";
            }
          ?>
          </select> 
        <?php 
          }else{
            echo "&nbsp;&nbsp;No sample";
          }
        ?>
          </td>
          <td><a href="javascript: pop_add_new('Sample');" class=button><?php echo ($analyst_project_insert)?"[new]":"&nbsp;"?></a></td>
      <?php }?>         
        </tr>  
        <?php 
          $url = ''; 
          if($gel_free_bait){
            $url = "../analyst/plate_free.php?theaction=addnew&sub=3&change_project=$frm_project_ID&Exp_ID=$frm_exp_ID&Bait_ID=$frm_bait_ID";
           }else{
            $url = "../analyst/gel.php?change_project=$frm_project_ID&sub=1";
          }
          //echo "<br>Notice: Don't logout pop window. You can close it!";
        }
      }
    }
    
    if($upload_search_results and $permitted and $frm_sample_ID){
      if($theaction != 'saveTPP' and uploaded_before($frm_sample_ID, 'tppProt')){
          $error_msg = 'The sample has linked with a uploaded TPP results. If you want to upload a different TPP file you have to delete the previous one';
          $message = $error_msg;
      }else{
    ?>
    
    <tr><td colspan=3 bgcolor="<?php echo $menu_color;?>"><font face="Arial"  size=3 color="#ffffff"><b>Browse TPP Files</b></font></td>
    </tr>
    <tr>
      <td nowrap><b><font face="Arial" size=2>TPP ProteinProphet:  </font></b></td>
      <td colspan=2><input type=file size=45 name=frm_tppProt_xml></td>
    </tr>
    <tr>
      <td nowrap><b><font face="Arial" size=2>TPP PeptideProphet: </font></b></td>
      <td colspan=2><input type=file size=45 name=frm_tppPep_xml></td>
    </tr>
    <?php 
      }
    }
    ?>
    </table>
  </td>
</tr>
<?php 
if($saveBy){
  $task_str = '';
  if(is_array($saveBy)){
    foreach($saveBy as $arr){
      $task_str .= ' TaskID: '. $arr['TaskID'].' WellID: '.$arr['WellID'];
    }
  }else{
    $task_str = "Task: $saveBy";
  }
?>
<tr><td>Hits have been parsed from <?php echo $task_str?></td></tr>
<?php }?>
<?php 
if($message){
  echo "<tr>";
  echo "<td colspan=2>";
  echo "<font color=green>";
  echo $message;
  echo "</td>";
  echo "</tr>";
}
?>
<tr>
 <td align=center>
  <?php if($permitted){
      if($can_submitted && !$submit_disabled){  
  ?>
      <input type=button value='Submit' onClick='checkForm(this.form)'>
  <?php   }
      if(!$saveBy && $oldProhitsID && array_key_exists($oldProjectID,$pro_access_ID_Names)){?>
      <input type=button value='Remove Link' onClick='removeLink(this.form)'>
    <?php }elseif($link_type == 'icon_link_y.gif' and in_array($USER->ID, $file_arr['userID_arr'])){
        $tmp_link_lable = strtolower($file_arr['link_lable']);
    ?>
    <?php //}elseif($link_type == 'icon_link_y.gif' and (in_array($USER->ID, $file_arr['userID_arr']) or $USER->Type == 'Admin')){?>
    <?php //}elseif(in_array($USER->ID, $file_arr['userID_arr']) or $USER->Type == 'Admin'){?>
      <input type=button value='<?php echo $file_arr['link_lable']?>' onClick="removeLink_Hits(this.form,'<?php echo $tmp_link_lable?>')">
    <?php }?>
  <?php }else{
      //echo "You don't have permission to change the link.<br> If you have to change the link please contact Prohits administrator."; 
    }?>
  <input type="button" value='Close' onClick="window.close()";>
 </td>
</tr>
<?php 
if($file_arr['remove info'] && $file_arr['User'] > 0){
  $sub_remove_info = " will be removed if you click on button '".$file_arr['link_lable']."'";
  $remove_info = "Hits had been parsed from ".substr(trim($file_arr['remove info']), 0, -1);   
  if($file_arr['link_lable'] == 'Remove Linking and Passed Hits'){
    $remove_info .= " and this link ";
  }
  $remove_info .= $sub_remove_info;
  echo "<tr><td><font color='red'>$remove_info</font></td></tr>";
}
?>
</table>
</form>
</body>
</html>
<?php 
function get_original_info($tableName,$raw_file_ID){
  global $fileObj_arr, $folderObj_arr, $managerDB;
  $raw_file_ID_arr = explode(',', $raw_file_ID);
  foreach($raw_file_ID_arr as $file_id){
    $fileObj =  new Storage($managerDB->link,$tableName);
    $folderObj =  new Storage($managerDB->link,$tableName);
    $fileObj->fetch($file_id);
    if($fileObj->FolderID){
      $folderObj->fetch($fileObj->FolderID);
    }else{
      echo "could not find (id: $raw_file_ID) this file in any folder";exit;
    }
    if(!array_key_exists($folderObj->ID, $folderObj_arr)){
      $folderObj_arr[$folderObj->ID] = $folderObj;
      $fileObj_arr[$folderObj->ID] = array();
    }
    array_push($fileObj_arr[$folderObj->ID],$fileObj);
  }
}

function get_file_array($tableName,$tppR_table_name,$raw_file_ID,&$returned_arr){
  //get the raw file record and not parsed group ID string.
  global $managerDB;
  global $theaction;
  global $task_ID;
  global $tpp_task_arr;
  
  $userID_arr = array();
  $task_arr = array();
  $others_passed_task_arr = array();
  $this_passed_task_arr = array();
  if($tppR_table_name){
    $tableName .= $tppR_table_name;
    $SQL = "select TppTaskID, ProjectID, ProhitsID,User,SavedBy,SearchEngine from $tableName where WellID='$raw_file_ID'";
    if($returned_arr = $managerDB->fetch($SQL)){
      $returned_arr['remove info'] = '';
      $SQL = "select WellID, SavedBy, TppTaskID, SearchEngine from $tableName where WellID='$raw_file_ID' and SavedBy>0";
      $tmp_arr = $managerDB->fetchAll($SQL);
      foreach($tmp_arr as $row){
        if($row['SearchEngine'] == 'GPM') $row['SearchEngine'] = 'XTandem';
        if(in_array($row['TppTaskID'], $tpp_task_arr)){
          if(!in_array($row['SavedBy'], $userID_arr)){
            array_push($userID_arr, $row['SavedBy']);
          }
          array_push($this_passed_task_arr, $row['TppTaskID']);
          $returned_arr['remove info'] .= "TppTask:".$row['TppTaskID'].$row['SearchEngine'].", ";     
        }else{
          array_push($others_passed_task_arr, $row['TppTaskID']);
        }
        $returned_arr['SavedBy'] .= "TppTask:".$row['TppTaskID'].$row['SearchEngine'].", ";
      }
    }
  }else{
    $SQL = "select ID, ProjectID, ProhitsID, User, FileName, FolderID from $tableName where ID='$raw_file_ID'";
    //---not linked-----
    if($returned_arr = $managerDB->fetch($SQL)){
    $returned_arr['remove info'] = '';
      $returned_arr['SavedBy'] = '';
      $tmp_table = $tableName . "SearchResults";
      $SQL = "select WellID, SavedBy, TaskID, SearchEngines from $tmp_table where WellID='$raw_file_ID' and SavedBy>0";
      //---hits had passed-------
      $tmp_arr = $managerDB->fetchAll($SQL);
      foreach($tmp_arr as $row){
        if($row['SearchEngines'] == 'GPM') $row['SearchEngines'] = 'XTandem';
        if($row['TaskID'] == $task_ID){
          if(!in_array($row['SavedBy'], $userID_arr)){
            array_push($userID_arr, $row['SavedBy']);
          }
          array_push($this_passed_task_arr, $row['TaskID']);
          $returned_arr['remove info'] .= $row['TaskID'].$row['SearchEngines'].", ";     
        }else{
          array_push($others_passed_task_arr, $row['TaskID']);
        }
        $returned_arr['SavedBy'] .= $row['TaskID'].$row['SearchEngines'].", ";
      }
      $tmp_table = $tableName . "tppResults";
      $SQL = "select WellID, SavedBy, TppTaskID, SearchEngine from $tmp_table where WellID='$raw_file_ID' and SavedBy>0";
      $tmp_arr = $managerDB->fetchAll($SQL);
      foreach($tmp_arr as $row){
        if($row['SearchEngine'] == 'GPM') $row['SearchEngine'] = 'XTandem';
        if(in_array($row['TppTaskID'], $tpp_task_arr)){
          if(!in_array($row['SavedBy'], $userID_arr)){
            array_push($userID_arr, $row['SavedBy']);
          }
          array_push($this_passed_task_arr, $row['TppTaskID']);
          $returned_arr['remove info'] .= "TppTask:".$row['TppTaskID'].$row['SearchEngine'].", ";     
        }else{
          array_push($others_passed_task_arr, $row['TppTaskID']);
        }
        $returned_arr['SavedBy'] .= "TppTask:".$row['TppTaskID'].$row['SearchEngine'].", ";
      }
      $returned_arr['no_savedBy_group_id_str'] = $raw_file_ID; //--- not linked and hits not passed.
    }
  }
  
  $returned_arr['this_passed_task_arr'] = $this_passed_task_arr;
  $returned_arr['others_passed_task_arr'] = $others_passed_task_arr;
  if(!$returned_arr['ProhitsID']){
    $returned_arr['link_lable'] = 'Make linking';
  }elseif($this_passed_task_arr && $others_passed_task_arr){
    $returned_arr['link_lable'] = 'Remove Passed Hits';
  }elseif($this_passed_task_arr && !$others_passed_task_arr){
    $returned_arr['link_lable'] = 'Remove Linking and Passed Hits';
  }elseif(!$this_passed_task_arr && $others_passed_task_arr){
    $returned_arr['link_lable'] = 'Display Linking Info';
  }elseif(!$this_passed_task_arr && !$others_passed_task_arr){
    $returned_arr['link_lable'] = 'Remove Linking';
  }  
  $returned_arr['userID_arr'] = $userID_arr;
}

function update_file_table($tableName,$tppR_table_name,$raw_file_ID, $raw_arr){
  global $managerDB,$frm_project_ID,$frm_sample_ID,$oldProjectID,$oldProhitsID,$oldUser,$USER;
  global $hitDB;
   
  $whereID = '';
  if($tppR_table_name){
    $tableName .= $tppR_table_name;
    $whereID = "where WellID='$raw_file_ID'";
  }else{
    $raw_file_ID = $raw_arr['no_savedBy_group_id_str'];
    $whereID = "where ID='$raw_file_ID'";
  }
  $SQL = "update $tableName set ProjectID='$frm_project_ID', ProhitsID='$frm_sample_ID', User='".$USER->ID."' $whereID";
  if($ret = $managerDB->execute($SQL)){
    $msg = "old ProID $oldProjectID; new ProID=$frm_project_ID; old prohits ID=$oldProhitsID; new prohits ID=$frm_sample_ID; old User=$oldUser; new User=".$USER->ID;
    $SQL = "insert into Log set UserID='".$USER->ID."', MyTable='$tableName', RecordID='$raw_file_ID', Myaction='modify', Description='$msg', ProjectID='$frm_project_ID'";
    $hitDB->insert($SQL);
  }
  return $ret;
}





function update_file_table_remove($tableName,$tppR_table_name,$raw_file_ID, $raw_arr){
  global $managerDB,$USER,$oldProjectID,$oldProhitsID,$oldUser, $frm_project_ID;
  global $hitDB,$theaction;
  
  /*echo "\$tableName=$tableName<br>";
  echo "\$tppR_table_name=$tppR_table_name<br>";
  echo "\$raw_file_ID=$raw_file_ID<br>";
  echo "<pre>";
  print_r($raw_arr);
  echo "</pre>";
  exit;*/
  
  $whereID = '';
  $group_id_str = '';
  //if($raw_arr['SavedBy'] && $theaction != 'removelink_and_hits') return 0;
  if($tppR_table_name){
    $raw_file_ID_str = get_child_id($raw_file_ID, $tableName);
    $tableName .= $tppR_table_name;
    $whereID = "where WellID IN ($raw_file_ID_str)";
  }else{
    $raw_file_ID = $raw_arr['no_savedBy_group_id_str'];
    if(!$raw_file_ID) return 0;
    $raw_file_ID_str = get_child_id($raw_file_ID, $tableName);
    $whereID = "where ID IN ($raw_file_ID_str)";
  }
  $SQL = "update $tableName set ProhitsID='', User='' $whereID";
  
  if($ret = $managerDB->execute($SQL)){
    $msg = "old ProID=$oldProjectID; old prohits ID=$oldProhitsID; old User=$oldUser; group_id_str: $raw_file_ID_str";
    $SQL = "insert into Log set UserID='".$USER->ID."', MyTable='$tableName', RecordID='$raw_file_ID', Myaction='linkRemoved', Description='$msg', ProjectID='$frm_project_ID'";
    $hitDB->insert($SQL);
  }  
  return $ret;
}




function get_child_id($raw_file_ID, $tableName){
  global $managerDB;
  $SQL = "SELECT `ID` FROM $tableName WHERE `RAW_ID` IN ($raw_file_ID)";
  $tmp_arr = $managerDB->fetchAll($SQL);
  $ret_id_str = '';
  foreach($tmp_arr as $tmp_val){
    $ret_id_str .= $tmp_val['ID'].',';
  }
  if($ret_id_str){
    $ret_id_str .= $raw_file_ID;
  }else{
    $ret_id_str = $raw_file_ID;
  } 
  //echo "\$ret_id_str=$ret_id_str<br>";
  //exit; 
  return $ret_id_str;
} 

 

function update_band_table_add($tableName,$tppR_table_name,$raw_file_ID, $raw_arr){
  global $hitDB,$frm_sample_ID,$frm_project_ID,$USER;
  if($tppR_table_name){
    $tableName .= $tppR_table_name;
  }else{
    $raw_file_ID = $raw_arr['no_savedBy_group_id_str'];
  }
  $RawFile_value = "$tableName:".$raw_file_ID;
  $SQL = "select RawFile from Band where ID='$frm_sample_ID'";
  $band_rd = $hitDB->fetch($SQL);
  if($band_rd['RawFile']){
    $SQL = "update Band set RawFile='".$band_rd['RawFile'].";$RawFile_value' where ID='$frm_sample_ID'";
  }else{
    $SQL = "update Band set RawFile='$RawFile_value' where ID='$frm_sample_ID'";
  }
  if($ret = $hitDB->execute($SQL)){
    $msg = "add link=$tableName:$raw_file_ID";
    $SQL = "insert into Log set UserID='".$USER->ID."', MyTable='Band', RecordID='$frm_sample_ID', Myaction='linkAdd', Description='$msg', ProjectID='$frm_project_ID'";
    $hitDB->insert($SQL);
  }
  return $ret;
}
 
 
 
function update_band_table_remove($tableName,$tppR_table_name,$oldProhitsID,$raw_file_ID,$tmp_hitDB, $raw_arr){
  global $oldProjectID,$USER;
  $RawFile_value = '';
  $SQL = "select RawFile from Band where ID='$oldProhitsID'";
  $oldBand_rd = $tmp_hitDB->fetch($SQL);
  $tmp_rawFile = '';
  if(isset($oldBand_rd['RawFile'])){
    if($tppR_table_name){
      $tableName .= $tppR_table_name;
      $raw_file_ID_str = get_child_id($raw_file_ID, $tableName);//------------------------------
      $removed_rawID_arr = explode(',', $raw_file_ID_str);
      
      $tmp_rawfile_arr = explode(';', $oldBand_rd['RawFile']);
      $new_rawfile_str = '';
      $pattern = '/$tableName:(.+)/';
      $new_m_arr = array();
      foreach($tmp_rawfile_arr as $tmp_rawfile_val){
        if(preg_match($pattern, $tmp_rawfile_val, $matches)){
          $tmp_arr = explode(',', $matches[1]);
          $new_m_arr = array_merge($new_m_arr, $tmp_arr);
          continue;
        }
        if($new_rawfile_str) $new_rawfile_str .= ';';
        $new_rawfile_str .= $tmp_rawfile_val;
      }
      $new_m_arr = array_unique($new_m_arr);
      $new_m_arr = array_diff($new_m_arr, $removed_rawID_arr);
      $new_m_str = '';
      if($new_m_arr){
        $new_m_str = implode(',', $new_m_arr);
        $new_m_str = $tableName.":".$new_m_str;
      }
      if($new_rawfile_str && $new_m_str){
        $tmp_rawFile = $new_rawfile_str.";".$new_m_str;
      }elseif($new_m_str){
        $tmp_rawFile = $new_m_str;
      }elseif($new_rawfile_str){
        $tmp_rawFile = $new_rawfile_str;
      }else{
        $tmp_rawFile = '';
      }
    }else{
      $raw_file_ID = $raw_arr['no_savedBy_group_id_str'];
      $raw_file_ID_str = get_child_id($raw_file_ID, $tableName);//------------------------------
      if($raw_file_ID_str){
        $raw_file_ID_arr = explode(',',$raw_file_ID_str);
        $tmp_rawFile = $oldBand_rd['RawFile'];
echo "\$tmp_rawFile=$tmp_rawFile<br>";
        foreach($raw_file_ID_arr as $raw_file_ID){
          $RawFile_value = "$tableName:$raw_file_ID";
          $tmp_rawFile = preg_replace("/;$RawFile_value|$RawFile_value/", "", $tmp_rawFile);
        }
        $tmp_rawFile = preg_replace("/^;|;$/", "", $tmp_rawFile);
      }
    }
echo "\$tmp_rawFile=$tmp_rawFile<br>"; 
    $SQL = "update Band set RawFile='$tmp_rawFile' where ID='$oldProhitsID'";
    if($ret = $tmp_hitDB->update($SQL)){    
      $msg = "removed link=$raw_file_ID";
      $SQL = "insert into Log set UserID='".$USER->ID."', MyTable='Band', RecordID='$oldProhitsID', Myaction='linkRemoved', Description='$msg', ProjectID='$oldProjectID'";
      $tmp_hitDB->insert($SQL);
    }  
  }
  return $ret;
}

function closeWindow(){
?>
<html>
<body>
<script type="text/javascript">
window.opener.location.reload();
window.close();
</script>
</body>
</html>
<?php 
exit;
}

function _error($message){
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Upload Notice:</title>
</head>
<body>
<center>
  <table border=0 width=99% cellspacing="1">
    <tr>
      <td align=center>
        <font face="Arial" size="+2" color="#660000"><b>Upload Notice</b></font><br>
      <hr width="100%" size="1" noshade>
      </td>
    </tr>
    <tr>
      <td width=30% valign=top bgcolor=white>
        <font face="Arial" size="2" color="red"><?php echo $message?></font>
      </td>
    </tr>
  </table> 
</center>   
</body>
</html>
<?php 
  exit;
}

function check_upload($file_size=''){
  $msg = '';
  $POST_MAX_SIZE = ini_get('post_max_size');
  $UPLOAD_MAX_FILESIZE = ini_get('upload_max_filesize');
  $FILE_UPLOADS = ini_get('file_uploads');
  if($file_size){
    if(isset($_SERVER['CONTENT_LENGTH'])){
      $mul_P = substr($POST_MAX_SIZE, -1);
      $mul_P= ($mul_P == 'M' ? 1048576 : ($mul_P == 'K' ? 1024 : ($mul_P == 'G' ? 1073741824 : 1)));
      $mul_F = substr($UPLOAD_MAX_FILESIZE, -1);
      $mul_F= ($mul_F == 'M' ? 1048576 : ($mul_F == 'K' ? 1024 : ($mul_F == 'G' ? 1073741824 : 1)));
      if (($_SERVER['CONTENT_LENGTH'] > $mul_P*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) or
         ($_SERVER['CONTENT_LENGTH'] > $mul_F*(int)$UPLOAD_MAX_FILESIZE && $UPLOAD_MAX_FILESIZE))
      {
        $msg = "The file you uploaded has exceeded the server limit post_max_size: 
           $POST_MAX_SIZE or upload_max_filesize $UPLOAD_MAX_FILESIZE. 
           Please contact Prohits administrator to change the setting.";
      }
    }
  }else{
    if(!$FILE_UPLOADS){
      $msg = "The Apache setting: '<b>file_uploads</b>' is off. Please contact Prohits administrator to change the setting.";
    }
  }
  return $msg;
}
function check_file_type($file, $tppType){
  $rt = '';
  //<program_details analysis="proteinprophet" time="Tue Jun 17 19:01:40 200" version=" Insilicos_LabKey_C++ (TPP v3.4 SQUALL rev.0, Build 200711291721)">
  //<peptideprophet_summary version="PeptideProphet v3.0 April 1, 2004 (TPP v3.4 SQUALL rev.0, Build 200711291721)" author="AKeller@ISB" min_prob="0.05" options=" MINPROB=0.05 EXTRAITRS=20 NONTT " est_tot_num_correct="0.0">
  //<peptideprophet_summary version="PeptideProphet v3.0 April 1, 2004 (TPP v3.4 SQUALL rev.0, Build 200711291721)" author="AKeller@ISB" min_prob="0.05" options=" MINPROB=0.05 EXTRAITRS=20 NONTT " est_tot_num_correct="7.2">
  if(!is_file($file['tmp_name'])) return "Cannot open file: ".$file['name'];
  if ($fp = @fopen($file['tmp_name'], "r")) {
    $i=0;
    while ($data = fgets($fp, 4096)) {
      if($tppType == 'tppProt'){
        if(strpos($data, '<program_details analysis="proteinprophet"') === 0){
           
          break;
        }
      }
      if($tppType == 'tppPep'){
        if(strpos($data, '<peptideprophet_summary version="PeptideProphet') === 0){
          
          break;
        }
      }
      $i++;
      if($i>20){
        $rt = "The uploaded $tppType file is not correct format.";
        break;
      }
    }
  }
  return $rt;
}
function save_search_result_file($file_arr, $upload_to, $frm_sample_ID, $Type, $pepFileName=''){
  global $hitDB;
  global $AccessUserID;
  global $AccessProjectID;
  $error_msg = '';
  $ok = true;
  $uploaded_file_name = $file_arr['name'];
  $uploaded_file_type = $file_arr['type'];
  $uploaded_file_size = $file_arr['size'];
  $uploaded_file_name = preg_replace ( '/[^-+\w+\.]/', '', $uploaded_file_name );
 
  if(uploaded_before($frm_sample_ID, $Type)){
    return $uploaded_file_name. " has been uploaded";
  }
  if(is_file($upload_to.$uploaded_file_name)){
    $uploaded_file_name = $frm_sample_ID."_".$uploaded_file_name;
  }
  $tmpFileFullName = $upload_to . $uploaded_file_name;
  if(move_uploaded_file($file_arr['tmp_name'], $tmpFileFullName)){
    if($Type == 'tppPep'){
      //the function is in auto_save_tpp_shell_fun.inc.php
      $ok = parse_peptideProphet($frm_sample_ID, $tmpFileFullName, "uploaded:".$uploaded_file_name);
    }else if($Type == 'tppProt'){
      if($pepFileName) $pepFileName = 'uploaded:'.$pepFileName;
      $ok = parse_proteinProphet($frm_sample_ID, $tmpFileFullName, $pepFileName, "uploaded:".$uploaded_file_name);
    }
    if(!$ok){
      $error_msg = "There is error when parsing file " .$uploaded_file_name. ". read log file for detail.";
      write_Log($error_msg);
    }
    if(!$error_msg){
      $SQL = "insert into UploadSearchResults set 
              BandID='$frm_sample_ID',
              File='$uploaded_file_name',
              UploadedBy='$AccessUserID',
              Date=now(),
              SearchEngine='$Type'";
      $hitDB->insert($SQL);
      $Log = new Log($hitDB->link);
      $Desc = "uploaded file:$uploaded_file_name"; 
      $Log->insert($AccessUserID,'UploadSearchResults',$frm_sample_ID,'insert',$Desc,$AccessProjectID);
    }
  }
  return $error_msg;
}
function uploaded_before($frm_sample_ID, $Type){
  global $hitDB;
  $SQL = "select ID from UploadSearchResults where BandID='$frm_sample_ID' and SearchEngine='$Type'";
  return $hitDB->exist($SQL);
}
?>