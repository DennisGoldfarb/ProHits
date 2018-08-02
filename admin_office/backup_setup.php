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

$theaction = '';
$oldName = '';
$backupSourceFolder = '';
$Project = '';
$sourceFolderInfo = '';
$disFolerInfo = '';
$backupFolderInfo = '';
$warnMess = '';
$mTablesInfoArr = array();
$currentTableInfo = array();
$basetableName = '';
$img_msg = '';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//$other_DB_tables_name_arr = array('LOG','RAWCONVERTPARAMETER','SEARCHPARAMETER','MERGEDFILES','TEST');
$other_DB_tables_name_arr = array('LOG','RAWCONVERTPARAMETER','SEARCHPARAMETER','MERGEDFILES');
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$Warning = '';

$ADDRESS = '';
$RAW_DATA_FOLDER = '';
$SHARED_TO_USER = '';
$SHARED_TO_USER_PASSWD = '';
$WINDOWS_ACTIVE_DIRECTORY = '';
$backupSourceFolder_root = '/mnt/';

require("../common/site_permission.inc.php");
include("./admin_log_class.php");
include("common_functions.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$configFile = "../config/conf.inc.php";
$configFileBackup = "../config/conf.inc_backup.php";
$logfile = '../logs/raw_back.log';
$script_path = dirname(dirname($PHP_SELF))."/msManager/process_storage_folder.php";
$logoDir = "../msManager/images/msLogo/";
$defaultImageName = $logoDir."default_logo.gif";
if(preg_match('/\/$/', STORAGE_FOLDER)){
  $STORAGE_FOLDER = STORAGE_FOLDER;
}else{
  $STORAGE_FOLDER = STORAGE_FOLDER . "/";
}
$no_computer_attr_flag = 0;

$SOURCE_COMPUTER = array(
              'ADDRESS'                 =>$ADDRESS,
              'RAW_DATA_FOLDER'         =>$RAW_DATA_FOLDER,
              'SHARED_TO_USER'          =>$SHARED_TO_USER,
              'SHARED_TO_USER_PASSWD'   =>$SHARED_TO_USER_PASSWD,
              'WINDOWS_ACTIVE_DIRECTORY'=>$WINDOWS_ACTIVE_DIRECTORY
              );
$SOURCE_COMPUTER_lable = array(
              'ADDRESS'                 =>"Computer Address",
              'RAW_DATA_FOLDER'         =>"Shared raw data folder name",
              'SHARED_TO_USER'          =>"Shared to user",
              'SHARED_TO_USER_PASSWD'   =>"Shared to user password",
              'WINDOWS_ACTIVE_DIRECTORY'=>"Windows Active Directory"
              ); 
$SOURCE_COMPUTER_comment = array(
              'ADDRESS'                 =>"Full DNS address or IP address.",
              'RAW_DATA_FOLDER'         =>"(e.g. shared folder C:\Data, type Data)",
              'SHARED_TO_USER'          =>"",
              'SHARED_TO_USER_PASSWD'   =>"",
              'WINDOWS_ACTIVE_DIRECTORY'=>"Keep it empty if the user is in the mass spec acquisition computer."
              );                            
$SOURCE_COMPUTER_empty = array(
              'ADDRESS'                 =>'',
              'RAW_DATA_FOLDER'         =>'',
              'SHARED_TO_USER'          =>'',
              'SHARED_TO_USER_PASSWD'   =>'',
              'WINDOWS_ACTIVE_DIRECTORY'=>''
              );
$storageFolders = get_all_storage_dir($script_path);

$Results = "SearchResults";
$Tasks = "SearchTasks";
$Conf = "SaveConf";
$tppResults = "tppResults";
$tppTasks = "tppTasks";

$SearchResults = strtoupper($Results);
$SearchTasks = strtoupper($Tasks);
$SaveConf = strtoupper($Conf);
$TPPresults = strtoupper($tppResults);
$TPPTasks = strtoupper($tppTasks);

$bgcolor = "#bbd7ce";
$bgcolordark = "#94b4aa"; 

$prohitsManagerDB = new mysqlDB(MANAGER_DB);
$original_tb_name = check_conditions_for_creat_tables($prohitsManagerDB);

//echo $original_tb_name;exit;

$AdminLog = new AdminLog();
$projectIdNameArr = get_project_ID_Name($prohitsManagerDB);

include("./admin_header.php");

$mTablesNameArr = get_mdb_table_names();

if($theaction == "delete"){
  if(!$oldName){
    $Warning = "<font color='red' size=1>Table name is empty!</font>";
  }else{  
    $ret = process_outsite_dir($STORAGE_FOLDER, STORAGE_IP, $script_path, "remove", "", $oldName);//--remove destination folder.
    //$ret[0] = 1;
    if($ret[0] == '1'){    
      $dropInfor = drop_tables($oldName, "dropAll");      
      $backup_db_tables_info = get_backup_db_tables_info($dropInfor,1);
      $warnMess = $backup_db_tables_info[1];
      /*$tmp_flag_1 = 1;
      $tmpStr = '';
      foreach($dropInfor as $dropKey => $dropVal){
        if($dropVal){
          $tmpStr .= $dropKey . " is deleted; ";
        }else{
          $tmpStr .= $dropKey . " cannot be deleted; ";
          $tmp_flag_1 = 0;
        }
      }*/
      remove_backup_item($oldName);//--clean up backup item.
      $Warning = "<font color='red' size=2>".$warnMess."</font>";
    }else{
      $Warning = "<font color='red' size=2>".$ret[0]."</font>";
    }  
  }
  $theaction = "viewall";
  $DBaction = '';
}

if(strstr($original_tb_name, 'Warning:')){
  $tmp_arr = explode(":",$original_tb_name);
  $Warning = trim($tmp_arr[1]);
  $theaction == "viewall";
}

$is_BackupArrExist = 0;
if(isset($BACKUP_SOURCE_FOLDERS)){
  $is_BackupArrExist = 1;
}else{
	$BACKUP_SOURCE_FOLDERS = array();
}

$listTableWidth = "95%";
$addNeTableWidth = 600;
if($theaction == "viewall" OR !$theaction){
  $titleTableWidth = $listTableWidth;
}else{
  $titleTableWidth = $addNeTableWidth;
}
$tdHeight = 20;

?>
<script language="Javascript" src="../analyst/site_no_right_click.inc.js"></script>
<SCRIPT language=JavaScript>
<!--
var tableNameArr = new Array();
var linkedSourceFolderArr = new Array();
var existSourceFolderArr = new Array();

function trimString(str){
  var str = this != window? this : str;
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}

function tableName_not_been_used(inStr){
  var tableName = inStr.toUpperCase();
  for(var i=0; i<tableNameArr.length; i++){
    if(tableNameArr[i] == tableName){
      alert("Mass Spec Machine Name " + inStr + " has been used by others");
      return false;
    }
  }
  return true;
}

function sourceFolder_exist(sourceFolder){
  for(var i=0; i<existSourceFolderArr.length; i++){
    if(existSourceFolderArr[i] == sourceFolder){
      return true;
    }
  }
  alert("Backup Source Folder " + sourceFolder + " doesn't exist");
  return false;
}

function sourceFolder_not_been_linked(sourceFolder){
  for(var i=0; i<linkedSourceFolderArr.length; i++){
    if(linkedSourceFolderArr[i] == sourceFolder){
      alert("Backup Source Folder " + sourceFolder + " has been linked by others");
      return true;
    }
  }
  return false;
}

function confirm_addnew(theForm){
  var inStr = trimString(theForm.oldName.value);
  inStr = inStr.toUpperCase();
  theForm.backupSourceFolder.value = theForm.backupSourceFolder.value + inStr;
  var source_folder = theForm.backupSourceFolder.value;
  var sourceFolder = trimString(theForm.backupSourceFolder.value);
  var m_str = sourceFolder.match(/^\/mnt\/\w+/);
  var ret = tableName_not_been_used(inStr);
  if(ret == false){
    return false;
  }
  $tmp_sf = theForm.noSourceFolder.checked;
  if(inStr == "" || /\W/.test(inStr)){
    alert("Only A-Za-z0-9 is allowed.");
    return false;
  }else if($tmp_sf == false && m_str == null){
    alert("Enter backup source folder path starting with <?php echo $backupSourceFolder_root;?> or select \"don't run backup and upload only!\"");
    return false;
  }else if(theForm.noSourceFolder.checked == true){
    theForm.backupSourceFolder.value = '';
    //alert("Enter backup source folder name or check checkbox for upload only.");
    //return false;
  }  
  var message = "";
  if(theForm.addNewAautoSearch.checked == true){
    message = "If click OK button a set of tables\n"; 
    message += inStr + ", ";
    message += inStr + "<?php echo $Results;?>,\n";
    message += inStr + "<?php echo $Tasks;?>, ";
    message += inStr + "<?php echo $Conf;?>,\n";
    message += inStr + "<?php echo $tppResults;?> and ";
    message += inStr + "<?php echo $tppTasks;?>\n";
    message += "will be create in Prohits_manager Database \nfor auto search "
    message += "and a link to file source " + source_folder + " will be created.";
  }else{
    message = "If click OK button a table called " + inStr + " will be created in Prohits_manager Database\n";
  }  
  
  
  if(confirm(message)){;
    theForm.oldName.value = inStr;
    theForm.theaction.value = "create";
    if(theForm.noSourceFolder.checked && theForm.addNewAautoSearch.checked){
      theForm.notes_info_flag.value = 8;
    }else if(!theForm.noSourceFolder.checked && theForm.addNewAautoSearch.checked){
      theForm.notes_info_flag.value = 7;
    }else if(!theForm.noSourceFolder.checked && !theForm.addNewAautoSearch.checked){
      theForm.notes_info_flag.value = 6;
    }
    theForm.submit();
  }
}

function show_list(){
  var theForm = document.Backup_Setup;
  theForm.theaction.value = "viewall";
  theForm.submit();
}

function update(){
  var theForm = document.Backup_Setup;
  if(typeof theForm.basetableName != "undefined" && typeof theForm.backupSourceFolder != "undefined"){
    var inStr = trimString(theForm.basetableName.value);
    theForm.backupSourceFolder.value = "<?php echo $backupSourceFolder_root?>" + inStr;
    var oldBaseTableName = trimString(theForm.oldBaseTableName.value);
    if(inStr != oldBaseTableName){
      var ret = tableName_not_been_used(inStr);
      if(ret == false){
        return false;
      }
    }
    $tmp_sf = theForm.noSourceFolder.checked;
    var sourceFolder = trimString(theForm.backupSourceFolder.value);
    var m_str = sourceFolder.match(/^\/mnt\/\w+/);
    var oldSourceFolder = trimString(theForm.oldBackupSourceFolder.value);
    if(inStr == "" || /\W/.test(inStr)){
      alert("Only A-Za-z0-9 is allowed.");
      return false;
    }else if($tmp_sf == false && m_str == null){
      alert("Enter backup source folder path starting with <?php echo $backupSourceFolder_root;?> or select \"don't run backup and upload only!\"")
      return false;
    }else if(theForm.noSourceFolder.checked == true){
      theForm.backupSourceFolder.value = '';
      //alert("Enter backup source folder name or check checkbox for upload only.");
      //return false;
    }
  }
  //alert(theForm.noSourceFolder.checked);
  
  if(theForm.noSourceFolder.checked && theForm.autoSearch.checked){
    theForm.notes_info_flag.value = 8;
  }else if(!theForm.noSourceFolder.checked && theForm.autoSearch.checked){
    theForm.notes_info_flag.value = 7;
  }else if(!theForm.noSourceFolder.checked && !theForm.autoSearch.checked){
    theForm.notes_info_flag.value = 6;
  }
  //alert(theForm.notes_info_flag.value);
  theForm.theaction.value = "update";
  theForm.submit();
}

function cleanup_sourceFolder(){
  var theForm = document.Backup_Setup;
  //alert(theForm.noSourceFolder.checked);
  if(theForm.noSourceFolder.checked == true){
    if(typeof(theForm.backupSourceFolder)!="undefined"){
      theForm.backupSourceFolder.value = "";
    }  
    <?php Toggle_computer_arr(1);?>
  }else{
    if(typeof(theForm.backupSourceFolder)!="undefined"){
      theForm.backupSourceFolder.value = "<?php echo $backupSourceFolder_root;?>";
    }  
    <?php Toggle_computer_arr();?>
  }
}
function checkImage(theaction){
  var theForm = document.Backup_Setup;
  if(theForm.frm_Image.value ==''){
    alert("please add an image");
  }else{
    if(theaction == 'addNew'){
      confirm_addnew(theForm);
    }else if(theaction == 'update'){
      update(); 
    }
  }
}
-->
</SCRIPT>
<br>
<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td align="left">
		&nbsp; <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="4"><b>Backup Setup</b></font> 
	  </td>
    <td align="right">
      <a href="<?php echo $PHP_SELF;?>?theaction=addnew" class=button>[Add New]</a>&nbsp;
      <a href="<?php echo $PHP_SELF;?>?theaction=viewall" class=button>[Mass Spec Machine List]</a>&nbsp;
    </td>    
  </tr>
  <tr>
  	<td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
  <td align="center" colspan=2 valign=top>
    <table cellspacing="2" cellpadding="2" border="0" align=center>
    <tr>
        <td align=center><img src='./images/computer1.gif' border=0><br>
        Mass spectrometer acquisition computer
        </td>
        <td><img src='./images/computer3.gif' border=0>
        </td>
        <td align=center><img src='./images/computer2.gif' border=0><br>
        Prohits Storage computer
        </td>
        <td><img src='./images/plus.gif' border=0></td>
        <td align=center><img src='./images/database.gif' border=0><br>
        Prohits storage database
        </td>
    </tr>
    </table>
  </td>
  </tr>
  <tr>
    <td colspan=5><div class=maintext>ProHits MS data management is organized in an instrument-specific manner.  Each of the instruments in the facility is given a user-defined <font size="+1"><b>Mass spectrometer Name</b></font>.  This list of names serves as the Base Table, and as a part of the path of the Backup Destination Folder.  ProHits creates a set of database tables under each of the mass spectrometer names.  These tables contain information such as search results, search tasks, TPP results, TPP tasks and configuration files.
<br><br>
To add a new instrument, select [Add New], set a Mass Spectrometer Name, and enter required information.  
Note that if you do not check the "Auto Search" checkbox, only the Mass Spectrometer Name table is created; 
if "Auto Search" is selected, all other tables will be automatically created.
<br><br>
Once data is entered in any of the tables, it is impossible to modify or delete the information (except for the Default Project association, which can be changed at any time).
<br><br>
<font size="+1"><b>Backup Source Folder</b></font>: Source data is acquired to a data folder on the acquisition computer connected to the mass spectrometer.  
The folder should be mounted in the ProHits storage computer directory <strong><?php echo $backupSourceFolder_root;?></strong>. Please follow the instuction from "Prohits/install/MSconnection/MountMSComuters.doc" to mount mass spectrometer computer.
If this folder does not exist, an error message will be displayed.  Clicking the button under the "Backup Source Folder" only allows manual file uploading, without automated backup of MS data. 
<br><br>
<font size="+1"><b>Backup Destination Folder</b></font>: This folder will be automatically created from the Storage Root Directory and Mass Spectrometer Name.  Make sure that at least 1 TB of storage space is allocated (see Installation instructions). If there is any problem with creating this folder an error message will be given.
<br><br>
<font size="+1"><b>Auto Search</b></font>: Checking this box results in the creation of SearchResults, SearchTasks and SaveCont tables. To disable the tables, uncheck the box. If one of the three tables contains data, the checkbox will be disabled and no modification of the tables will be allowed.
</div></td>
   </tr>
  
  <tr>
    <td align="center" colspan=2>&nbsp;<br>
      <table border="0" cellpadding="0" cellspacing="1" width="<?php echo $titleTableWidth;?>">
        <tr bgcolor="">      
          <td width="200" align="left" valign="top" nowrap>
            <font face="Arial" size="2" color="green"><b>Storage IP:</b></font>
          </td>
          <td width="" align="left" valign="top">
            <font face="Arial" size="2" ><b><?php echo STORAGE_IP;?></b></font>
          </td>
        </tr>
        <tr bgcolor="">      
          <td width="" align="left" valign="top">
            <font face="Arial" size="2" color="green"><b>Storage Root directory: </b></font>
          </td>
          <td width="" align="left" valign="top">
            <font face="Arial" size="2" ><b><?php echo STORAGE_FOLDER;?></b></font>
          </td>
        </tr>
        <tr bgcolor="">      
          <td width="" align="left" valign="top">
            <font face="Arial" size="2" color="green"><b>Prohits Server IP: </b></font>
          </td>
          <td width="" align="left" valign="top">
            <font face="Arial" size="2" ><b><?php echo PROHITS_SERVER_IP;?></b></font>
          </td>
        </tr>
        <tr bgcolor="">      
          <td width="" align="left" valign="top">
            <font face="Arial" size="2" color="green"><b>Database Server IP: </b></font>
          </td>
          <td width="" align="left" valign="top">
            <font face="Arial" size="2" ><b><?php echo HOSTNAME;?></b></font>
          </td>
        </tr>
        <tr bgcolor="">      
          <td width="" align="left" valign="top">
            <font face="Arial" size="2" color="green"><b>Database Name: </b></font>
          </td>
          <td width="" align="left" valign="top">
            <font face="Arial" size="2" ><b><?php echo MANAGER_DB;?></b></font>
            
          </td>
        </tr>
        <tr bgcolor="">      
          <td width="" align="left" valign="top">
            <font face="Arial" size="2" color="green"><b>Test Computer connction: </b></font>
          </td>
          <td width="" align="left" valign="top">
            <font face="Arial" size="2" ><b>
            Run follwing command from root user. It will read conf.inic.php file and 
            automatically mount mss spec computers. Fix errors if there are any.
            </b></font>
            <br>
 	          >sudo <?php echo PHP_PATH . " " . realpath("../");?>/msManager/auto_run_shell.php connect
          </td>
        </tr>        
     <?php if($Warning){?> 
        <tr bgcolor="">      
          <td width="" align="left" valign="top">
            <font face="Arial" size="2" color="red"><b>Note:</b></font>
          </td>
          <td width="" align="left" valign="top">
            <font face="Arial" size="2" color="red"><b><?php echo $Warning;?></b></font>
          </td>
        </tr>
     <?php }?> 
      </table>
    <td>
  </tr>  
  <tr>    
    <td align="center" colspan=2 valign=top>
<?php 
if($theaction == "viewall" OR !$theaction){
  $mTablesInfoArr = get_mdb_tables_info();
?>
      <table border="0" cellpadding="0" cellspacing="1" width="<?php echo $listTableWidth;?>">
        <form name="Backup_Setup" method=post action="<?php echo $PHP_SELF;?>">  
          <input type=hidden name=theaction value=delete>
        <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
          <td width="10%" rowspan='2' colspan='2' align=center><div class=tableheader>Name</td>
          <td width="" colspan='6' align=center height=<?php echo $tdHeight;?>><div class=tableheader>Database Tables</td>
          <td width="12%" rowspan='2' align=center><div class=tableheader>Backup Source Folder</td>
          <td width="12%" rowspan='2' align=center><div class=tableheader>Backup Destination Folder</td>
          <td width="15%" rowspan='2' align=center><div class=tableheader>Default Project</td>
          <td width="5%" rowspan='2' align=center><div class=tableheader>Auto Search</td>   
          <td width="5%"rowspan='2' align="center"><div class=tableheader>Modify</td>    
        </tr>
        <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
          <td width="8%" align=center height=<?php echo $tdHeight;?>><div class=tableheader>Base</td>
          <td width="9%" align=center><div class=tableheader>Results</td>
          <td width="9%" align=center><div class=tableheader>Tasks</td>
          <td width="8%" align=center><div class=tableheader>Conf</td>
          <td width="8%" align=center><div class=tableheader>TPP Results</td>
          <td width="8%" align=center><div class=tableheader>TPP Tasks</td>    
        </tr>
<?php 
  foreach($mTablesInfoArr as $key => $value){
    $tmpBackupSourceFolder = ''; 
    $projectName = '';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%  
    if(!isset($BACKUP_SOURCE_FOLDERS[$key]) && !isset($BACKUP_SOURCE_FOLDERS[$mTablesNameArr[$key]])) continue;
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%    
    if(isset($BACKUP_SOURCE_FOLDERS[$key])){    
      if($BACKUP_SOURCE_FOLDERS[$key]['SOURCE']){
        //echo $BACKUP_SOURCE_FOLDERS[$key]['SOURCE'];
        $isSourceDir = process_outsite_dir($BACKUP_SOURCE_FOLDERS[$key]['SOURCE'], STORAGE_IP, $script_path, "isDir");
        if(isset($isSourceDir[0]) && trim($isSourceDir[0])){
          $tmpBackupSourceFolder = $BACKUP_SOURCE_FOLDERS[$key]['SOURCE'];
        }else{
          $tmpBackupSourceFolder = "<font color=red>Source directory ".$BACKUP_SOURCE_FOLDERS[$key]['SOURCE']." doesn't exist</font>";
        }
      }
      if($BACKUP_SOURCE_FOLDERS[$key]['DEFAULT_PROJECT_ID']){
        if(isset($projectIdNameArr[$BACKUP_SOURCE_FOLDERS[$key]['DEFAULT_PROJECT_ID']])){        
          $projectName = $projectIdNameArr[$BACKUP_SOURCE_FOLDERS[$key]['DEFAULT_PROJECT_ID']];
        }else{
          $projectName = "<font color=red>Project ID ".$BACKUP_SOURCE_FOLDERS[$key]['DEFAULT_PROJECT_ID']." is an invalid project ID</font>";
        }  
      }
    }
    
    $tmpDestinationFolder = '';
    $tmpStorageFolder = $STORAGE_FOLDER . $key;
    if(in_array($tmpStorageFolder, $storageFolders)){
      $tmpDestinationFolder = $tmpStorageFolder;
    }
    
    $imageFullName = $logoDir.$key."_logo.gif";
    if(!is_file($imageFullName)){
      $imageFullName = $defaultImageName;
    }
    //$is_empty_or_unexist_folder = trim(is_empty_or_unexist_folder($tmpDestinationFolder));
    $is_empty_or_unexist_folder = 1;
    $is_empty_base_table = is_table_empty($key);
    $can_be_delet = $is_empty_or_unexist_folder && $is_empty_base_table;
    if($can_be_delet){
      $DBaction = "dropAll";
    }else{
      $DBaction = "";
    }
?>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">      
          <td width="" align="left"><div class=maintext>&nbsp;<b>
              <?php echo $mTablesNameArr[strtoupper($key)];?></b></div>
          </td>
          <td><img src=<?php echo $imageFullName;?>>
          </td>
          <td width=""><div class=maintext>&nbsp;
            <?php echo ($value['BASE']['Exist'])?$mTablesNameArr[$value['BASE']['Name']]:'<font color=red>not exist(error)</font>';?>
          </div></td>
          <td width=""  ><div class=maintext>&nbsp;
            <?php echo ($value[$SearchResults]['Exist'])?$mTablesNameArr[$value[$SearchResults]['Name']]:'';?>
          </div></td>
          <td width="" ><div class=maintext>&nbsp;
            <?php echo ($value[$SearchTasks]['Exist'])?$mTablesNameArr[$value[$SearchTasks]['Name']]:'';?>
          </div></td>
          <td width=""><div class=maintext>&nbsp;
            <?php echo ($value[$SaveConf]['Exist'])?$mTablesNameArr[$value[$SaveConf]['Name']]:'';?>
          </div></td>
          <td width=""><div class=maintext>&nbsp;
            <?php echo ($value[$TPPresults]['Exist'])?$mTablesNameArr[$value[$TPPresults]['Name']]:'';?>
          </div></td>
          <td width=""><div class=maintext>&nbsp;
            <?php echo ($value[$TPPTasks]['Exist'])?$mTablesNameArr[$value[$TPPTasks]['Name']]:'';?>
          </div></td>
          <td width=""" ><div class=maintext>&nbsp;
            <?php echo $tmpBackupSourceFolder?>
          </div></td>
          <td width="" ><div class=maintext>&nbsp;
            <?php echo $tmpDestinationFolder?>
          </div></td>
          <td width="" ><div class=maintext>&nbsp;
            <?php echo $projectName?>
          </div></td>
          <td width="" align="center"><div class=maintext>&nbsp;
            <?php echo ($value['BASE']['Exist'] && $value[$SearchResults]['Exist'] && $value[$SearchTasks]['Exist'] && $value[$SaveConf]['Exist'])?"<img src='./images/check_yes.gif' alt='' width='14' height='16' border='0'>":'';?>
          </div></td>
          <td width="" align="left" nowrap><div class=maintext>&nbsp;&nbsp;
            <a href="<?php echo $PHP_SELF;?>?theaction=modify&oldName=<?php echo $key;?>">
            <img border="0" src="./images/icon_view.gif" alt="Detail"></a>
         <?php if($can_be_delet){?>   
            <a href="<?php echo $PHP_SELF;?>?theaction=delete&oldName=<?php echo $mTablesNameArr[strtoupper($key)];?>&DBaction=<?php echo $DBaction?>&allEmpty=1&othersEmpty=1">
            <img border="0" src="images/icon_purge.gif" alt="Delete" class=button>&nbsp;&nbsp;</a>
         <?php }?>   
          </div></td>          
        </tr>
<?php }?>    
      </form>
      </table><br>&nbsp;
<?php 
}else if($theaction == "addnew" OR $theaction == "create" ){
  if($theaction == "create"){
    $oldName = strtoupper(trim($oldName));
    $backupSourceFolder = format_source_folder(trim($backupSourceFolder));
    //$sourceFolderStatus = check_sourcr_folder($backupSourceFolder, $script_path);
    $destinationFolder = $STORAGE_FOLDER . $oldName;
    //$is_empty_or_unexist_folder = trim(is_empty_or_unexist_folder($destinationFolder));
    $is_empty_or_unexist_folder = 1;
    //if($oldName && !array_key_exists($oldName, $mTablesNameArr) && ($backupSourceFolder == '' || $sourceFolderStatus > 0) && $is_empty_or_unexist_folder == '1'){
    if($oldName && !array_key_exists($oldName, $mTablesNameArr) && $is_empty_or_unexist_folder == '1'){
    //if($oldName && !array_key_exists($oldName, $mTablesNameArr) && $is_empty_or_unexist_folder == '1'){      
      $img_msg = upload_logo($oldName);
      if($img_msg != 1){
        $theaction = "addnew";
      }else{
        $img_msg = '';
        if(!isset($BACKUP_SOURCE_FOLDERS[$oldName])){
          $newBackupItemStatus = create_new_backup_item($oldName, $backupSourceFolder, $Project, $SOURCE_COMPUTER, $is_BackupArrExist);
          if($newBackupItemStatus == 1 && !$backupSourceFolder){
            $newBackupItemStatus = 0;
          }
        }else{
          $newBackupItemStatus = 5;
        }
        if($newBackupItemStatus == 0){
          $sourceFolderInfo = "Make backup and search without backup source folder.";
        }elseif($newBackupItemStatus == 3){
          $sourceFolderInfo = "<font color=red>For some reason the link to Source folder $backupSourceFolder cannot be created.</font>";    
        }elseif($newBackupItemStatus == 5){
          $sourceFolderInfo .= "<font color=red>confige file error.";
        }
      
        if($newBackupItemStatus < 2){
          //--create storage dir----------------------------------------------------------
          $retArr = process_outsite_dir($STORAGE_FOLDER, STORAGE_IP, $script_path, "create", $oldName);
          
          if(trim($retArr[1]) == "1"){
            $destinationFolder = $STORAGE_FOLDER . $oldName;
            //----create DB tables--------------------------------------------------------
            $DBaction = "";
            if(isset($addNewAautoSearch)){
              $DBaction = "createAll";
            }else{
              $DBaction = "createBase";
            }
            $DBcreateStatusArr = creat_backup_db_tables($oldName,$original_tb_name,$DBaction);
            $backup_db_tables_info = get_backup_db_tables_info($DBcreateStatusArr);            
            if(!$backup_db_tables_info[0]){
              @unlink($new_pic_name);
              remove_backup_item($oldName);//--clean up backup item.
              process_outsite_dir($STORAGE_FOLDER, STORAGE_IP, $script_path, "remove", "", $oldName);//--remove destination folder.
              drop_tables($oldName, $DBaction);//--drop DB tables.
              $theaction = "addnew";
            }else{
              $theaction = "modify";
            }
            $warnMess = $backup_db_tables_info[1];
          }else{
            print_r($retArr);
            remove_backup_item($oldName);//--clean up backup item.
            $warnMess = $retArr[1];
            $theaction = "addnew";
          }
        }else{
          @unlink($new_pic_name);
          $warnMess = $sourceFolderInfo;
          $theaction = "addnew";
        }
      }
    }else{
      $warnMess = '';
      if(!$oldName){
        $warnMess = '<font color=red>Please enter Mass Spec Machine name</font>';
      }elseif(array_key_exists(strtoupper($oldName), $mTablesNameArr)){
        $warnMess = '<font color=red>The Mass Spec Machine name you entered has existed in prohits_manager DB</font>';
     
      }elseif($is_empty_or_unexist_folder == "0"){
        $warnMess = "<font color=red>Destination folder $destinationFolder exists and is not empty!</font>";
      }
      $theaction = "addnew";
    }
  }
  if($theaction == "addnew"){
    validate_javaScripts();
?>   
      <form name="Backup_Setup" method=post action=<?php echo $_SERVER['PHP_SELF'];?> enctype="multipart/form-data">
      <input type=hidden name=theaction value="">
      <input type=hidden name="notes_info_flag" value="">
      <table border="0" cellpadding="0" cellspacing="1" width="<?php echo $addNeTableWidth;?>">
      <?php if($warnMess){?>
        <tr bgcolor="">
		      <td colspan="3" align="center" height=25><div class=maintext><?php echo $warnMess;?></div>
		      </td>
	      </tr>
      <?php }?>  
        <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
		      <td colspan="3" align="center" height=25><div class=tableheader>Backup Setup</div>
		      </td>
	      </tr>
		    <tr>          
	        <td colspan="2" bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top align=right width=37% nowrap>
          <div class=maintext><b>Name</b>:&nbsp;</div>                   
	        </td>
          <td bgcolor="<?php echo $TB_CELL_COLOR;?>" >
            <input type="text" name="oldName" size="30" value="<?php echo $oldName;?>"><br><div class=maintext>&nbsp;Only A-Za-z0-9 is allowed</div>                
	        </td>
        </tr>
        <tr>          
	        <td colspan="2" bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top align=right nowrap>
          <div class=maintext><b>&nbsp;Don't run backup and upload raw only<br>Don't connect mass spectrometer computer.</b>:&nbsp;</div>                   
	        </td>
          <td bgcolor="<?php echo $TB_CELL_COLOR;?>" >
            <input type="hidden" name="backupSourceFolder" size="30" value="<?php echo $backupSourceFolder_root;?>">
            <input type="checkbox" name="noSourceFolder" size="30" value="Y" <?php echo (isset($noSourceFolder))?"checked":"";?> onClick="cleanup_sourceFolder();">                
	        </td>
        </tr>
        <tr>          
	        <td colspan="2" bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top align=right nowrap>
          <div class=maintext><b>Default Project</b>:&nbsp;</div>                   
	        </td>
          <td bgcolor="<?php echo $TB_CELL_COLOR;?>">
            <select name="Project">
              <option  value=''>----Select a Project----                 
    				 	<?php foreach($projectIdNameArr as $key => $value){?>
                <option  value='<?php echo $key;?>' <?php echo ($key == $Project)?"selected":""?>><?php echo $value?>(<?php echo $key?>)
              <?php }?>  
    				</select>                
	        </td>
        </tr>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
		      <td colspan="2" align=right height=<?php echo $tdHeight;?>>
		      <div class=maintext><b>Auto Search</b>:&nbsp;</div>
		      </td>
          <td height=<?php echo $tdHeight;?>>
  		      <input type="checkbox" name="addNewAautoSearch" size="30" value="" <?php echo (isset($addNewAautoSearch))?"checked":"";?>>
		      </td>
	      </tr>  
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
          <td align=right height=<?php echo $tdHeight;?> valign=top align=right width=40%>
		        <div class=maintext><b>Mass spectrometer logo</b>:&nbsp;</div>
          </td>
          <td colspan="3" align="center" height=<?php echo $tdHeight;?> valign=top><div class=maintext>
          <?php if($img_msg) echo $img_msg ."<br>"?>
          &nbsp;&nbsp;<input type='file' name='frm_Image' size='32'>
          &nbsp;&nbsp;<input type="button" value="Add Logo" onClick="javascript:checkImage('addNew');">
          <br>&nbsp; Please only upload GIF formatted and size less than 134X134 pixels image.</div>
          </td>      
        </tr>
        <?php 
          print_mass_computer_attr($SOURCE_COMPUTER);
        ?>      
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
		      <td colspan="3" align="center" height=<?php echo $tdHeight;?>>
		      <input type='button' name='frm_submit' value=' Submit ' onClick="confirm_addnew(this.form)">
		      </td>
	     </tr>        
      </table>
  </form>
<?php 
  }
}
if($theaction == "modify" || $theaction == "update"){
  $allEmpty = 1;
  $allExist = 1;
  $othersEmpty = 1;
  $othersExist = 1;
  $baseEmpty = 1;
  $baseExist = 1;
  $otherTaleNamesE = '';
  $otherTaleNamesN = '';
  $linkExist = '';
  
  if($theaction == "update"){
    $warnMess = '';
    $oldName = trim($oldName);
    $backupSourceFolder = format_source_folder(trim($backupSourceFolder));
    $mTablesNameArr = get_mdb_table_names();
    /*if(!array_key_exists($oldName, $mTablesNameArr)){
      $oldName = $_SESSION["msBaseTableName"];
      $theaction = "modify";
    }else{
      
    }*/
    $oldName = $mTablesNameArr[strtoupper($oldName)];
    
    //$sourceFolderStatus = check_sourcr_folder($backupSourceFolder, $script_path,$oldName);
    //if($backupSourceFolder == '' || $sourceFolderStatus > 0){
    //if($backupSourceFolder == ''){
      $DBaction = '';
      $createInfo = array();
      $dropInfor = array();
      $mTablesInfoArr = get_mdb_tables_info();
      $currentTableInfo = get_statues($oldName);
      $linkExist = isset($BACKUP_SOURCE_FOLDERS[$oldName]);
      
      $img_msg = upload_logo($basetableName, $oldName);
      if($img_msg == 1){
        $img_msg ='';
      //---process tables-------------------------------------------
        if(isset($basetableName) && strcasecmp($basetableName, $oldName) && $allEmpty){
          $basetableName = strtoupper(trim($basetableName));
          $destinationFolder = $STORAGE_FOLDER . $basetableName;
          //$is_empty_or_unexist_folder = trim(is_empty_or_unexist_folder($destinationFolder));
          $is_empty_or_unexist_folder = 1;
          if($is_empty_or_unexist_folder == '1'){
          
            if(!array_key_exists(strtoupper($basetableName), $mTablesNameArr)){   
              $DBaction = "dropAll";
              $dropInfor = drop_tables($oldName, $DBaction); //--drop all table '$oldName'
              if(isset($autoSearch)){
                $DBaction = "createAll"; //--create all tables '$basetableName'
              }else{
                $DBaction = "createBase"; //--create base table '$basetableName'
              }
              $createInfo = creat_backup_db_tables($basetableName,$original_tb_name,$DBaction);
              
              if($linkExist){
                if(isset($noSourceFolder)){
                  $backupSourceFolder = '';
                }else{
                  //$backupSourceFolder = $backupSourceFolder_root . $oldName ."/";
                  $backupSourceFolder = "dont_change_source";
                }
      					change_backup_item($backupSourceFolder,$Project,$oldName, $SOURCE_COMPUTER, $basetableName);
              }else{           
                if(isset($noSourceFolder)){
                  $backupSourceFolder = '';
                }else{
                  $backupSourceFolder = $backupSourceFolder_root . $basetableName ."/";
                }
    						create_new_backup_item($basetableName, $backupSourceFolder, $Project, $SOURCE_COMPUTER, $is_BackupArrExist);
      				}
              $oldDirName = $STORAGE_FOLDER . $oldName;
              $newDirName = $STORAGE_FOLDER . $basetableName;
              $retArr = process_outsite_dir($STORAGE_FOLDER, STORAGE_IP, $script_path, "modify", $basetableName, $oldName);
              //print_r($retArr);exit;
              if(trim($retArr[1]) == "1"){
                //echo "old directory $oldDirName removed\n";
              }else{
                if($backupFolderInfo) $backupFolderInfo .= "<br>";
                $backupFolderInfo .= $retArr[1];
                //echo $retArr[1]."\n";
              }  
              if(trim($retArr[2]) == "1"){
                //echo "new directory $newDirName created\n";
              }else{
                if($backupFolderInfo) $backupFolderInfo .= "<br>";
                $backupFolderInfo .= $retArr[2];
                //echo $retArr[2]."\n";
              }
              $destinationFolder = $newDirName;
              $oldName = $basetableName;
              $_SESSION["msBaseTableName"] = $basetableName;
              $backup_db_tables_info = get_backup_db_tables_info($createInfo);
              $warnMess = $backup_db_tables_info[1];
            }else{
              $warnMess = "<font color=red>The Mass Spec Machine name you entered has been used in prohits_manager DB</font>";
            }
          }else{
            $warnMess = "<font color=red>Destination folder $destinationFolder exists and is not empty!</font>";
          }            
        }else{        
          if(isset($autoSearch)){
            $DBaction = "createOthers";
            $createInfo = creat_backup_db_tables($oldName,$original_tb_name,$DBaction); //--create all other tables
            $backup_db_tables_info = get_backup_db_tables_info($createInfo);
            $warnMess = $backup_db_tables_info[1];
          }elseif($othersEmpty){
            $DBaction = "dropOthers";
            $dropInfor = drop_tables($oldName, $DBaction);  //--drop all other tables.
            $backup_db_tables_info = get_backup_db_tables_info($dropInfor,1);
            $warnMess = $backup_db_tables_info[1];
          }
          if(isset($noSourceFolder)){
            $backupSourceFolder = '';
          }else{
            $backupSourceFolder = $backupSourceFolder_root . $oldName ."/";
          }
          if($linkExist){
            if(!is_same_link($backupSourceFolder,$Project,$oldName,$SOURCE_COMPUTER)){
              change_backup_item($backupSourceFolder,$Project,$oldName,$SOURCE_COMPUTER);
            }  
          }else{
      			create_new_backup_item($oldName, $backupSourceFolder, $Project, $SOURCE_COMPUTER, $is_BackupArrExist);
          }  
        }
      }
    $theaction = "modify";  
  }
  //---------------------------------------------------
  if($theaction == "modify"){
    //------------------------------------------
    $mTablesNameArr = get_mdb_table_names();
    $mTablesInfoArr = get_mdb_tables_info();
    $currentTableInfo = get_statues($oldName);
		$linkExist = isset($BACKUP_SOURCE_FOLDERS[$oldName]);
    
//-jp 2010/8/19--------------------------------------------------------------------   
    /*if(!$linkExist){
      echo "no BACKUP_SOURCE_FOLDERS [$oldName] exist";
      exit;
    }*/
//---------------------------------------------------------------------------------
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    if(isset($BACKUP_SOURCE_FOLDERS[$oldName])){
      if(!isset($BACKUP_SOURCE_FOLDERS[$oldName]['SOURCE_COMPUTER'])){
        $BACKUP_SOURCE_FOLDERS[$oldName]['SOURCE_COMPUTER'] = $SOURCE_COMPUTER_empty;
      }  
    }elseif(isset($BACKUP_SOURCE_FOLDERS[$mTablesNameArr[$oldName]])){
      if(!isset($BACKUP_SOURCE_FOLDERS[$mTablesNameArr[$oldName]]['SOURCE_COMPUTER'])){
        $BACKUP_SOURCE_FOLDERS[$mTablesNameArr[$oldName]]['SOURCE_COMPUTER'] = $SOURCE_COMPUTER_empty;
      }  
    }
    
    if(isset($BACKUP_SOURCE_FOLDERS[$oldName])){
      $SOURCE_COMPUTER = $BACKUP_SOURCE_FOLDERS[$oldName]['SOURCE_COMPUTER'];
    }elseif(isset($BACKUP_SOURCE_FOLDERS[$mTablesNameArr[$oldName]])){
      $SOURCE_COMPUTER = $BACKUP_SOURCE_FOLDERS[$mTablesNameArr[$oldName]]['SOURCE_COMPUTER'];
    } 
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $storageFolders = get_all_storage_dir($script_path);
    $destinationFolder = '';
    $tmpStorageFolder2 = $STORAGE_FOLDER . $mTablesNameArr[strtoupper($oldName)];
    if(in_array($tmpStorageFolder2, $storageFolders)){
      $destinationFolder = $tmpStorageFolder2;
    }else{
      $destinationFolder = "<font color=red>Destination folder $tmpStorageFolder2 doesn't exist</font>";
    }
  }

  if($warnMess && $backupFolderInfo) $warnMess .= "<br>" . $backupFolderInfo;
  if(!$warnMess && $backupFolderInfo) $warnMess = $backupFolderInfo;
  
  $oldNameLabal = (isset($mTablesNameArr[$oldName]))?$mTablesNameArr[$oldName]:$oldName;
  validate_javaScripts();
  $no_computer_attr_flag = 0;
  
  if(isset($noSourceFolder) || (isset($BACKUP_SOURCE_FOLDERS[$oldName]) && !$BACKUP_SOURCE_FOLDERS[$oldName]['SOURCE'])){
    $no_computer_attr_flag = 1;
  }
?>
      <form name="Backup_Setup" method=post action=<?php echo $_SERVER['PHP_SELF'];?> enctype="multipart/form-data">
      <input type=hidden name=theaction value="">
      <input type=hidden name="oldName" value="<?php echo $oldName?>">
      <input type=hidden name="allEmpty" value="<?php echo $allEmpty?>">
      <input type=hidden name="othersEmpty" value="<?php echo $othersEmpty?>">
      <input type=hidden name="notes_info_flag" value="">
      <table border="0" cellpadding="1" cellspacing="1" width="<?php echo $addNeTableWidth;?>">
      <?php if($warnMess){?>
        <tr bgcolor="">
		      <td colspan="4" align="left" height=25><div class=maintext><font color=red><?php echo $warnMess;?></font></div>
		      </td>
	      </tr>
      <?php }?>  
        <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
		      <td colspan="4" align="center" height=25><div class=tableheader>Modify</div>
		      </td>
	      </tr>
		    <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" >          
	        <td valign=top align=right width=35% colspan="2">
            <div class=maintext><b>Mass Spec Machine Name</b>:&nbsp;</div></td>
          <td align='' height=<?php echo $tdHeight;?> colspan="2">
            <div class=maintext>&nbsp;<?php echo ($allEmpty)?"<input type='text' name='basetableName' size='20' value='$oldNameLabal'><input type=hidden name=oldBaseTableName value='$oldNameLabal'>":"<font color=green>".$oldNameLabal."<font>";?></div></td>
        </tr>
<?php 
      $i=0;      
      foreach($currentTableInfo as $value){
        $restTableName = (isset($mTablesNameArr[$value['Name']]))?$mTablesNameArr[$value['Name']]:$value['Labal'];
        if($i == 0){
          $i++;
?>        
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" >
          <td valign=top align=right width=15% rowspan='6'>
            <div class=maintext><b>Database Tables&nbsp;</b>:&nbsp;</div></td>
          <td valign=top align=right width=25% height=<?php echo $tdHeight;?>>
            <div class=maintext><?php echo $restTableName?>:&nbsp;</div></td>
          <td valign=top colspan="2">
            <div class=maintext>&nbsp;<?php echo ($value['Exist'])?"exist":"<font color=red>not exist(error)</font>";?>&nbsp;<?php echo ($value['Exist'] && $value['Empty'])?"(empty)":"";?></div></td> 
        </tr>      
      <?php }else{?>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" width=20%>
          <td align=right height=<?php echo $tdHeight;?>><div class=maintext><?php echo $restTableName;?>:&nbsp;</div></td>  
          <td><div class=maintext>&nbsp;<?php echo ($value['Exist'])?"exist":"not exist";?>&nbsp;<?php echo ($value['Exist'] && $value['Empty'])?"(empty)":"";?></div></td>
        </tr>
      <?php }
      }
      $backupSourceFolder = ''; 
      $backupSourceFolder_error = "";
      $Project = '';
      $ProjectID_error = '';
      if($linkExist){
        $backupSourceFolder = $BACKUP_SOURCE_FOLDERS[$oldName]['SOURCE'];
        
        /*if($backupSourceFolder){
          //$SourceFolderStatus = check_sourcr_folder($backupSourceFolder, $script_path,$oldName);
          if($SourceFolderStatus == -1){
            $backupSourceFolder_error = "<font color=red>$backupSourceFolder has been used by others</font>";
          }elseif($SourceFolderStatus == -2){
            $backupSourceFolder_error = "<font color=red>$backupSourceFolder dosen't exist</font>"; 
          }
        }*/
        if($BACKUP_SOURCE_FOLDERS[$oldName]['DEFAULT_PROJECT_ID']){
          $Project = $BACKUP_SOURCE_FOLDERS[$oldName]['DEFAULT_PROJECT_ID'];
          if(!isset($projectIdNameArr[$Project])){
            $ProjectID_error = "<font color=red>The project ID $Project doesn't exist in prohits system</font>";
    			}
        }
      }
      ?>
      <?php 
      //if($allEmpty){
      ?>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" colspan="2">
		      <td align=right height=<?php echo $tdHeight;?> valign=top align=right width=40% colspan="2">
		        <div class=maintext><b>Don't run backup and upload raw file only:&nbsp;<br>Don't connect mass spectrometer computer:&nbsp;</div>
          </td>
          <td colspan="2"><div class=maintext>&nbsp;
            <?php //if($allEmpty){
                if($backupSourceFolder_error) echo $backupSourceFolder_error . "<br>";
                if($no_computer_attr_flag){
                  $tmp_checkek = "checked";
                  $backupSourceFolder_ext = '';
                }else{
                  $tmp_checkek = "";
                  $backupSourceFolder_ext = ($backupSourceFolder)?$backupSourceFolder:"/mnt/";;
                }
                if(isset($noSourceFolder)){
                  $tmp_checkek = "checked";
                }
            ?>
            <?php if($allEmpty){?>
              <input type='hidden' name='backupSourceFolder' size='25' value='<?php echo $backupSourceFolder_ext;?>'>
              <input type=hidden name="oldBackupSourceFolder" value='<?php echo $backupSourceFolder_ext?>'>
            <?php }?> 
              <input type="checkbox" name="noSourceFolder" size="30" value="Y" <?php echo $tmp_checkek;?> onClick="cleanup_sourceFolder();">                
	        <?php //}?>
          </td> 
	      </tr>
      <?php //}?>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" colspan="2">
		      <td align=right height=<?php echo $tdHeight;?> valign=top align=right width=40% colspan="2">
		        <div class=maintext><b>Backup Destination Folder</b>:&nbsp;</div>
          </td>
          <td colspan="2"><div class=maintext>&nbsp;<?php echo $destinationFolder;?>/</div></td> 
	      </tr>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">          
	        <td valign=top height=<?php echo $tdHeight;?> align=right width=40% colspan="2">
          <div class=maintext><b>Default Project</b>:&nbsp;</div>                   
	        </td>
          <td width="" valign="top" colspan="2"><div class=maintext>&nbsp;
          <?php if($ProjectID_error) echo $ProjectID_error;?>
            <select name="Project">
              <option  value=''>----Select a Project----                 
    				 	<?php foreach($projectIdNameArr as $key => $value){?>
                <option  value='<?php echo $key;?>' <?php echo ($key == $Project)?"selected":""?>><?php echo $value?>(<?php echo $key?>)
              <?php }?>  
    				</select>
          </td>
        </tr>    
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
		      <td align=right height=<?php echo $tdHeight;?> valign=top align=right width=40% colspan="2">
		      <div class=maintext><b>Auto Search</b>:&nbsp;</div>
		      </td>
          <td width="" valign="top" colspan="2"><div class=maintext>&nbsp;     
            <input type='checkbox' name='autoSearch' value="y" <?php echo ($allExist)?"checked":"";?> <?php echo ($othersEmpty)?"":"disabled";?>>
          </td>
	      </tr>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
          <td align=right height=<?php echo $tdHeight;?> valign=top align=right width=40% >
		        <div class=maintext><b>Mass spectrometer logo</b>:&nbsp;</div>
          </td>
          <td colspan="4" align="center" height=<?php echo $tdHeight;?>><div class=maintext>
          <?php 
          $imageFullName = $logoDir.strtoupper($oldNameLabal)."_logo.gif";
          $defaultImageName = $logoDir."default_logo.gif";
          if(!is_file($imageFullName)){
            $imageFullName = $defaultImageName;
          }  
          echo "<b>This Mass spec Machine logo</b>  <img src='" . $imageFullName . "' border=0 align=middle>";
           
          echo "<br>";
          if($img_msg) echo $img_msg ."<br>";
          ?>
          &nbsp;&nbsp;<input type='file' name='frm_Image' size='32'>
          &nbsp;&nbsp;<input type="button" value="Change Logo" onClick="javascript:checkImage('update');">
          <br>&nbsp; Please only upload GIF formatted and size less than 134X134 pixels image.</div>
          </td>      
        </tr>
        <?php 
          print_mass_computer_attr($SOURCE_COMPUTER);
        ?> 	
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
          <td height=<?php echo $tdHeight;?> align=center colspan="4">
        <?php //if($baseExist && ($othersEmpty || !$linkExist)){?>
		        <input type='button' name='frm_submit' value=' Submit ' onClick="update();">
            <input type='reset' name='frm_reset' value=' Reset '>
        <?php //}?>
            <input type='button' name='frm_back' value=' back ' onClick="show_list();">
          </td> 
        </tr>
      </table>
  </form>
<?php 
}
?>  
  </td>
  </tr>
</table>
<?php 
include("./admin_footer.php");
function drop_tables($currentTableName, $DBaction){
  global $allEmpty,$othersEmpty;
  global $prohitsManagerDB, $mTablesNameArr;
  global $SearchResults, $SearchTasks, $SaveConf,$TPPresults,$TPPTasks;
//--------------------------------------------------------------------  
  //echo "\$allEmpty=$allEmpty";
  //echo "\$othersEmpty=$othersEmpty";  
//----------------------------------------------------------------  
  $executeStatutes = array();
  $tablesNameArr = get_mdb_table_names();  
  $backuptableName = strtoupper($currentTableName);
  
  if($DBaction == "dropAll" && $allEmpty){
    $baseName_real = (isset($tablesNameArr[$backuptableName]))?$tablesNameArr[$backuptableName]:'';        
    if($baseName_real && is_table_empty($baseName_real)){
      $SQL = "DROP TABLE IF EXISTS `$baseName_real`";
      $ret = $prohitsManagerDB->execute($SQL);
      $executeStatutes[$baseName_real] = $ret;
    }    
  }  
  
  if(($DBaction == "dropAll" || $DBaction == "dropOthers") && $othersEmpty){
    $resulttableName = $backuptableName . $SearchResults;   
    $resulttableName_real = (isset($tablesNameArr[$resulttableName]))?$tablesNameArr[$resulttableName]:'';
    if($resulttableName_real && is_table_empty($resulttableName_real)){
      $SQL = "DROP TABLE IF EXISTS `$resulttableName_real`";
      $ret = $prohitsManagerDB->execute($SQL);
      $executeStatutes[$resulttableName_real] = $ret;
    }  
    
    $tasktableName = $backuptableName . $SearchTasks;
    $tasktableName_real = (isset($tablesNameArr[$tasktableName]))?$tablesNameArr[$tasktableName]:'';
    
    if($tasktableName_real && is_table_empty($tasktableName_real)){
      $SQL = "DROP TABLE IF EXISTS `$tasktableName_real`";
      $ret = $prohitsManagerDB->execute($SQL);
      $executeStatutes[$tasktableName_real] = $ret;
    }      
    
    $conftableName = $backuptableName . $SaveConf;
    $conftableName_real = (isset($tablesNameArr[$conftableName]))?$tablesNameArr[$conftableName]:'';
    if($conftableName_real && is_table_empty($tablesNameArr[$conftableName])){
      $SQL = "DROP TABLE IF EXISTS `$conftableName_real`";
      $ret = $prohitsManagerDB->execute($SQL);
      $executeStatutes[$conftableName_real] = $ret;
    }
    
    $tppResultstableName = $backuptableName . $TPPresults;
    $tppResultstableName_real = (isset($tablesNameArr[$tppResultstableName]))?$tablesNameArr[$tppResultstableName]:'';
    if($tppResultstableName_real && is_table_empty($tablesNameArr[$tppResultstableName])){
      $SQL = "DROP TABLE IF EXISTS `$tppResultstableName_real`";
      $ret = $prohitsManagerDB->execute($SQL);
      $executeStatutes[$tppResultstableName_real] = $ret;
    }
    
    $tppTaskstableName = $backuptableName . $TPPTasks;
    $tppTaskstableName_real = (isset($tablesNameArr[$tppTaskstableName]))?$tablesNameArr[$tppTaskstableName]:'';
    if($tppTaskstableName_real && is_table_empty($tablesNameArr[$tppTaskstableName])){
      $SQL = "DROP TABLE IF EXISTS `$tppTaskstableName_real`";
      $ret = $prohitsManagerDB->execute($SQL);
      $executeStatutes[$tppTaskstableName_real] = $ret;
    }            
  }
  return $executeStatutes;
}

function creat_backup_db_tables($currentTableName,$original_tb_name,$DBaction){
  global $Results, $Tasks, $Conf,$tppResults, $tppTasks;
  global $prohitsManagerDB;
  $tables_name_arr = get_current_DB_tables_name_arr($prohitsManagerDB);
  $executeStatutes = array();
  
  $backuptableName = $currentTableName;
  $backuptableName_o = $original_tb_name;
  
  if($DBaction == "createAll" || $DBaction == "createBase"){
    if(!in_array($backuptableName, $tables_name_arr)){
      $ret = clone_db_table_structure($prohitsManagerDB,$backuptableName_o,$backuptableName);
    }else{
      $ret = 2;
    }  
    $executeStatutes[$backuptableName] = $ret;
  }

  if($DBaction == "createAll" || $DBaction == "createOthers"){
    $resulttableName = $currentTableName . $Results;
    $resulttableName_o = $original_tb_name . $Results;
    if(!in_array($resulttableName, $tables_name_arr)){
      $ret = clone_db_table_structure($prohitsManagerDB,$resulttableName_o,$resulttableName);
    }else{
      $ret = 2;
    }
    $executeStatutes[$resulttableName] = $ret;
    
    $tasktableName = $currentTableName . $Tasks;
    $tasktableName_o = $original_tb_name . $Tasks;
    if(!in_array($tasktableName, $tables_name_arr)){
      $ret = clone_db_table_structure($prohitsManagerDB,$tasktableName_o,$tasktableName);
    }else{
      $ret = 2;
    }
    $executeStatutes[$tasktableName] = $ret;    
    
    $conftableName = $currentTableName . $Conf;
    $conftableName_o = $original_tb_name . $Conf;
    if(!in_array($conftableName, $tables_name_arr)){
      $ret = clone_db_table_structure($prohitsManagerDB,$conftableName_o,$conftableName);
    }else{
      $ret = 2;
    }
    $executeStatutes[$conftableName] = $ret;
   
    $tppResultstableName = $currentTableName . $tppResults;
    $tppResultstableName_o = $original_tb_name . $tppResults;
    if(!in_array($tppResultstableName, $tables_name_arr)){
      $ret = clone_db_table_structure($prohitsManagerDB,$tppResultstableName_o,$tppResultstableName);
    }else{
      $ret = 2;
    }
    $executeStatutes[$tppResultstableName] = $ret;
    
    $tppTaskstableName = $currentTableName . $tppTasks;
    $tppTaskstableName_o = $original_tb_name . $tppTasks;
    if(!in_array($tppTaskstableName, $tables_name_arr)){
      $ret = clone_db_table_structure($prohitsManagerDB,$tppTaskstableName_o,$tppTaskstableName);
    }else{
      $ret = 2;
    }
    $executeStatutes[$tppTaskstableName] = $ret;
   }
   return $executeStatutes;  
}
//---------------------------------------------------------
function get_mdb_tables_info(){//return a array of all tables name in prohits_mamager db.
  global $SearchResults, $SearchTasks, $SaveConf, $TPPresults, $TPPTasks;
  global $prohitsManagerDB,$other_DB_tables_name_arr;
  $mDBname = MANAGER_DB;
  
  $SQL = "SHOW TABLES FROM $mDBname";
  $result = mysqli_query($prohitsManagerDB->link, $SQL);
  if(!$result){
     echo "DB Error, could not list tables\n";
     echo 'MySQL Error: ' . mysqli_error();
     exit;
  }
  $mTablesNameArr = array();
  $mTablesInfoArr = array();
  while($row = mysqli_fetch_row($result)){
    $rawKey = strtoupper($row[0]);
    if(in_array(strtoupper($rawKey), $other_DB_tables_name_arr)) continue;
    if(!preg_match("/$SearchResults$/i", $row[0]) && !preg_match("/$SearchTasks$/i", $row[0]) && !preg_match("/$SaveConf$/i", $row[0]) && !preg_match("/$TPPresults$/i", $row[0]) && !preg_match("/$TPPTasks$/i", $row[0])){
      $mTablesInfoArr[$rawKey]['BASE']['Name'] = $rawKey;
      $mTablesInfoArr[$rawKey]['BASE']['Exist'] = 1;
    }else{
      $mTablesNameArr[$rawKey] = 0;
    }  
  }  
  foreach($mTablesInfoArr as $key => $value){
    $rTableName = $key . $SearchResults;
    $tTableName = $key . $SearchTasks;
    $cTableName = $key . $SaveConf;
    $tpprTableName  = $key . $TPPresults;
    $tpptTableName  = $key . $TPPTasks;
    $mTablesInfoArr[$key][$SearchResults]['Name'] = $rTableName;
    if(array_key_exists(strtoupper($rTableName), $mTablesNameArr)){
      $mTablesInfoArr[$key][$SearchResults]['Exist'] = 1;
      $mTablesNameArr[$rTableName] = 1;
    }else{
      $mTablesInfoArr[$key][$SearchResults]['Exist'] = 0;
    }
    $mTablesInfoArr[$key][$SearchTasks]['Name'] = $tTableName;
    if(array_key_exists(strtoupper($tTableName), $mTablesNameArr)){
      $mTablesInfoArr[$key][$SearchTasks]['Exist'] = 1;
      $mTablesNameArr[$tTableName] = 1;
    }else{
      $mTablesInfoArr[$key][$SearchTasks]['Exist'] = 0;
    }
    $mTablesInfoArr[$key][$SaveConf]['Name'] = $cTableName;
    if(array_key_exists(strtoupper($cTableName), $mTablesNameArr)){
      $mTablesInfoArr[$key][$SaveConf]['Exist'] = 1;
      $mTablesNameArr[$cTableName] = 1;
    }else{
      $mTablesInfoArr[$key][$SaveConf]['Exist'] = 0;
    }
    $mTablesInfoArr[$key][$TPPresults]['Name'] = $tpprTableName;
    if(array_key_exists(strtoupper($tpprTableName), $mTablesNameArr)){
      $mTablesInfoArr[$key][$TPPresults]['Exist'] = 1;
      $mTablesNameArr[$tpprTableName] = 1;
    }else{
      $mTablesInfoArr[$key][$TPPresults]['Exist'] = 0;
    }
    $mTablesInfoArr[$key][$TPPTasks]['Name'] = $tpptTableName;
    if(array_key_exists(strtoupper($tpptTableName), $mTablesNameArr)){
      $mTablesInfoArr[$key][$TPPTasks]['Exist'] = 1;
      $mTablesNameArr[$tpptTableName] = 1;
    }else{
      $mTablesInfoArr[$key][$TPPTasks]['Exist'] = 0;
    }
  }
  //---------------------------------------------------------------------
  $pattern = "/(.+)($SearchResults|$SearchTasks|$SaveConf|$TPPresults|$TPPTasks)$/i";  
  foreach($mTablesNameArr as $key => $value){
    if(!$value){
      if(preg_match($pattern, $key, $matches)){
        $matchedStr = strtoupper($matches[1]);
        if(!array_key_exists($matchedStr, $mTablesInfoArr)){
          $mTablesInfoArr[$matchedStr]['BASE']['Name'] = $matches[1];
          $mTablesInfoArr[$matchedStr]['BASE']['Exist'] = 0;
          $othersName = $matchedStr . $SearchResults;
          if(isset($mTablesNameArr[$othersName])){
            $mTablesInfoArr[$matchedStr][$SearchResults]['Name'] = $othersName;
            $mTablesInfoArr[$matchedStr][$SearchResults]['Exist'] = 1;
            $mTablesNameArr[$othersName] = 1;
          }else{
            $mTablesInfoArr[$matchedStr][$SearchResults]['Name'] = $othersName;
            $mTablesInfoArr[$matchedStr][$SearchResults]['Exist'] = 0;
          }
          $othersName = $matchedStr . $SearchTasks;
          if(isset($mTablesNameArr[$othersName])){
            $mTablesInfoArr[$matchedStr][$SearchTasks]['Name'] = $othersName;
            $mTablesInfoArr[$matchedStr][$SearchTasks]['Exist'] = 1;
            $mTablesNameArr[$othersName] = 1;
          }else{
            $mTablesInfoArr[$matchedStr][$SearchTasks]['Name'] = $othersName;
            $mTablesInfoArr[$matchedStr][$SearchTasks]['Exist'] = 0;
          }
          $othersName = $matchedStr . $SaveConf;
          if(isset($mTablesNameArr[$othersName])){
            $mTablesInfoArr[$matchedStr][$SaveConf]['Name'] = $othersName;
            $mTablesInfoArr[$matchedStr][$SaveConf]['Exist'] = 1;
            $mTablesNameArr[$othersName] = 1;
          }else{
            $mTablesInfoArr[$matchedStr][$SaveConf]['Name'] = $othersName;
            $mTablesInfoArr[$matchedStr][$SaveConf]['Exist'] = 0;
          }
          $othersName = $matchedStr . $TPPresults;
          if(isset($mTablesNameArr[$othersName])){
            $mTablesInfoArr[$matchedStr][$TPPresults]['Name'] = $othersName;
            $mTablesInfoArr[$matchedStr][$TPPresults]['Exist'] = 1;
            $mTablesNameArr[$othersName] = 1;
          }else{
            $mTablesInfoArr[$matchedStr][$TPPresults]['Name'] = $othersName;
            $mTablesInfoArr[$matchedStr][$TPPresults]['Exist'] = 0;
          }
          $othersName = $matchedStr . $TPPTasks;
          if(isset($mTablesNameArr[$othersName])){
            $mTablesInfoArr[$matchedStr][$TPPTasks]['Name'] = $othersName;
            $mTablesInfoArr[$matchedStr][$TPPTasks]['Exist'] = 1;
            $mTablesNameArr[$othersName] = 1;
          }else{
            $mTablesInfoArr[$matchedStr][$TPPTasks]['Name'] = $othersName;
            $mTablesInfoArr[$matchedStr][$TPPTasks]['Exist'] = 0;
          }
        }
      }
    }
  }
  //--------------------------------------------------------------------
  ksort($mTablesInfoArr);
  return $mTablesInfoArr;
}

function get_statues($tableName){
  global $mTablesInfoArr, $BACKUP_SOURCE_FOLDERS;
  global $allEmpty,$allExist,$othersEmpty,$othersExist,$baseEmpty,$baseExist,$allExist,$otherTaleNamesE,$otherTaleNamesN;
  global $Results,$Tasks,$Conf,$tppResults, $tppTasks;
  global $mTablesNameArr;
  $allEmpty = 1;
  $allExist = 1;
  $othersEmpty = 1;
  $othersExist = 1;
  $baseEmpty = 1;
  $baseExist = 1;
  $otherTaleNamesE = '';
  $otherTaleNamesN = '';

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%    
  //$currentTableInfo = $mTablesInfoArr[$tableName];
  $currentTableInfo = (isset($mTablesInfoArr[$tableName]))?$mTablesInfoArr[$tableName]:$mTablesInfoArr[strtoupper($tableName)];
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%  
  $baseName = $mTablesNameArr[$currentTableInfo['BASE']['Name']];

  $k = 0;
  foreach($currentTableInfo as $key => $value){
    if($value['Exist']){
      
      if(is_table_empty($value['Name'])){
        $currentTableInfo[$key]['Empty'] = 1;
      }else{
        $currentTableInfo[$key]['Empty'] = 0;
      }  
    }else{
      $currentTableInfo[$key]['Empty'] = 1;
    }
    
    if(strcasecmp($key, $Results) == 0){
      $currentTableInfo[$key]['Labal'] = $baseName . $Results;
    }elseif(strcasecmp($key, $Tasks) == 0){
      $currentTableInfo[$key]['Labal'] = $baseName . $Tasks;
    }elseif(strcasecmp($key, $Conf) == 0){
      $currentTableInfo[$key]['Labal'] = $baseName . $Conf;
    }elseif(strcasecmp($key, $tppResults) == 0){
      $currentTableInfo[$key]['Labal'] = $baseName . $tppResults;
    }elseif(strcasecmp($key, $tppTasks) == 0){
      $currentTableInfo[$key]['Labal'] = $baseName . $tppTasks;
    }else{
      $currentTableInfo[$key]['Labal'] = $baseName;
    }  
    $allEmpty = $allEmpty * $currentTableInfo[$key]['Empty'];
    $allExist = $allExist * $currentTableInfo[$key]['Exist'];
    if($k){
      $othersEmpty = $othersEmpty * $currentTableInfo[$key]['Empty'];
      $othersExist = $othersExist * $currentTableInfo[$key]['Exist'];
      if($currentTableInfo[$key]['Exist']){
        if($otherTaleNamesE) $otherTaleNamesE .= "<br>&nbsp;";
        $otherTaleNamesE .= $key;
      }else{
        if($otherTaleNamesN) $otherTaleNamesN .= "<br>&nbsp;";
        $otherTaleNamesN .= $key;
      }
    }else{
      $baseEmpty = $baseEmpty * $currentTableInfo[$key]['Empty'];
      $baseExist = $baseExist * $currentTableInfo[$key]['Exist'];
    }
    $k++;
  }
  return $currentTableInfo;
}

function get_mdb_table_names(){//return a array of all tables name in prohits_mamager db.
  global $prohitsManagerDB;
  $mDBname = MANAGER_DB;
  $SQL = "SHOW TABLES FROM $mDBname";
  $result = mysqli_query($prohitsManagerDB->link, $SQL);
  if(!$result){
     echo "DB Error, could not list tables\n";
     echo 'MySQL Error: ' . mysqli_error();
     exit;
  }
  $mTablesNameArr = array();
  while($row = mysqli_fetch_row($result)){
    $mTablesNameArr[strtoupper($row[0])] = $row[0];
  } 
  return $mTablesNameArr;
}

function is_table_empty($baseTableName){//if LCQ table is empty the base name LCQ can be changed.
  global $prohitsManagerDB, $mTablesNameArr;
  $baseTableName = strtoupper($baseTableName);
  $isEmpty = 1;
  if(isset($mTablesNameArr[$baseTableName])){
    $tableName = $mTablesNameArr[$baseTableName];
    $mDBname = MANAGER_DB;        
    $SQL = "SELECT * FROM `$tableName` LIMIT 1";
    $baseTableArr = $prohitsManagerDB->fetch($SQL);
    
    if($baseTableArr){
      $isEmpty = 0;
    }
  }  
  return $isEmpty;
} 

function create_new_backup_item($tableName, $backupSourceFolder, $Project, $SOURCE_COMPUTER, $is_BackupArrExist){
  global $script_path;
  global $configFile, $configFileBackup;
  if(!copy($configFile, $configFileBackup)){
    return 3;
  }
  global $BACKUP_SOURCE_FOLDERS;
  
  $tmp_cou = 0;
  
  if(!$SOURCE_COMPUTER['ADDRESS']){
    $SOURCE_COMPUTER_str = '';
  }else{
    $tmp_len = count($SOURCE_COMPUTER);
    $SOURCE_COMPUTER_str = ",'SOURCE_COMPUTER'=>array(";
    foreach($SOURCE_COMPUTER as $key => $val){
      if($tmp_cou == $tmp_len-1){
        $SOURCE_COMPUTER_str .= "'$key'=>'$val'";
      }else{
        $SOURCE_COMPUTER_str .= "'$key'=>'$val',";
      }
      $tmp_cou++;  
    }
    $SOURCE_COMPUTER_str .= ")";
  }
  
  $insertStr = "\$BACKUP_SOURCE_FOLDERS['$tableName'] = array('SOURCE'=>'$backupSourceFolder', 'DEFAULT_PROJECT_ID'=>'$Project'".$SOURCE_COMPUTER_str.");\r\n";
    
  $lines = file($configFile);
  $confHandle = fopen($configFile, 'w');    
  if($is_BackupArrExist){
    for($i=count($lines)-1; $i>=0; $i--){
      if(preg_match('/^\$BACKUP_SOURCE_FOLDERS\[/', $lines[$i])){
        array_splice($lines, $i+1, 0, $insertStr);
        break;
      }
    }
    foreach($lines as $value){
      fwrite($confHandle, $value);
    }
  }else{
    for($i=count($lines)-1; $i>=0; $i--){
      if(preg_match('/(\?\>)/', $lines[$i], $matches)){
        $tmpArr = explode('?>', $lines[$i]);
        if($tmpArr[0]){
          $tmpArr[0] .= "\r\n";
          array_splice($lines, $i, 0, $tmpArr[0]);
          $i++;
        }
        array_splice($lines, $i, 1, $insertStr);
        $i++;
        array_splice($lines, $i, 0, "?>\r\n");
        if(isset($tmpArr[1]) && $tmpArr[1]){
          $i++;
          $tmpArr[1] .= "\r\n";
          array_splice($lines, $i, 0, $tmpArr[1]);
        }
        break;
      }
    }
    foreach($lines as $value){
      fwrite($confHandle, $value);
    }
  }
  fclose($confHandle);
	$BACKUP_SOURCE_FOLDERS[$tableName] = array('SOURCE'=>$backupSourceFolder, 'DEFAULT_PROJECT_ID'=>$Project,'SOURCE_COMPUTER'=>$SOURCE_COMPUTER);
  return 1;
}

function change_backup_item($sourceFolder, $projectID, $oldName, $SOURCE_COMPUTER, $basetableName=''){
  if(!$oldName){
    return 0;
  }
  global $script_path;
  //$sourceFolderStatus = check_sourcr_folder($sourceFolder, $script_path);
  global $configFile, $configFileBackup;
  if(!copy($configFile, $configFileBackup)){
   echo "failed to backup $configFile\n";
   exit;
  }
	global $BACKUP_SOURCE_FOLDERS;
  $insteadFlag = 0;
  if($basetableName){
    $index = $basetableName;
  }else{
    $index = $oldName;
  }
  
  $tmp_cou = 0;
  if(!$SOURCE_COMPUTER['ADDRESS']){
    $SOURCE_COMPUTER_str = '';
  }else{
    $tmp_len = count($SOURCE_COMPUTER);
    $SOURCE_COMPUTER_str = ",'SOURCE_COMPUTER'=>array(";
    foreach($SOURCE_COMPUTER as $key => $val){
      if($tmp_cou == $tmp_len-1){
        $SOURCE_COMPUTER_str .= "'$key'=>'$val'";
      }else{
        $SOURCE_COMPUTER_str .= "'$key'=>'$val',";
      }
      $tmp_cou++;  
    }
    $SOURCE_COMPUTER_str .= ")";
  }
   
  $lines = file($configFile);
  $confHandle = fopen($configFile, 'w');
  foreach($lines as $value){ 
    $pattern = "\$BACKUP_SOURCE_FOLDERS['$oldName']";
    if(strstr($value, $pattern)){
      if($sourceFolder == "dont_change_source"){
        if(preg_match("/'SOURCE'=>'(.+)?',/", $value, $matches)){
          $sourceFolder = $matches[1];        
        }
      }
      $insteadStr = "\$BACKUP_SOURCE_FOLDERS['$index'] = array('SOURCE'=>'$sourceFolder', 'DEFAULT_PROJECT_ID'=>'$projectID'".$SOURCE_COMPUTER_str.");\r\n";
      fwrite($confHandle, $insteadStr); 
      $insteadFlag = 1;
			unset($BACKUP_SOURCE_FOLDERS[$oldName]);
			$BACKUP_SOURCE_FOLDERS[$index] = array('SOURCE'=>$sourceFolder, 'DEFAULT_PROJECT_ID'=>$projectID, 'SOURCE_COMPUTER'=>$SOURCE_COMPUTER);
    }else{
      fwrite($confHandle, $value);
    }
  }
  return $insteadFlag;  
}

function remove_backup_item($oldName){
  if(!$oldName){
    return 0;
  }
  global $configFile, $configFileBackup;
  if(!copy($configFile, $configFileBackup)){
   echo "failed to backup $configFile\n";
   exit;
  }
	global $BACKUP_SOURCE_FOLDERS;
  $lines = file($configFile);
  $confHandle = fopen($configFile, 'w');
  foreach($lines as $value){ 
    $pattern = "\$BACKUP_SOURCE_FOLDERS['$oldName']";
    if(strstr($value, $pattern)){
			unset($BACKUP_SOURCE_FOLDERS[$oldName]);
    }else{
      fwrite($confHandle, $value);
    }
  }
}

/*function check_storage_dir($path, $storage_ip, $script_path){
  $folder_arr = array();
  $url = "http://$storage_ip/$script_path?path=$path";
  $folder_arr = file($url);
  return $folder_arr;
}*/

function process_outsite_dir($path, $storage_ip, $script_path, $action='', $newDir='',$oldDir=''){
  $folder_arr = array();
  $url = "http://$storage_ip"."$script_path?path=$path&action=$action&newDir=$newDir&oldDir=$oldDir";
   
  $folder_arr = file($url);
  return $folder_arr;
  //actions and returns ----
  //'isDir'--isDir()--one cell 1 for $path is dir or 0 for $path is not dir.--first 4 argue
  //''--check_dir()--a array of dirs belong to $path (one level)--first 3 argu.
  //'create'--create_dir()--one cell 1 for success or error message------first 4 argus and $newDir.
  //'remove' --remove_dir()--one cell 1 for success or error message----first 4 argus and $oldDir.
  //'modify'--remove_dir(),create_dir()-- all argus.
}
function get_all_storage_dir($script_path){//---from storage root-------------
  global $STORAGE_FOLDER;
  $storageFolders_tmp = process_outsite_dir($STORAGE_FOLDER, STORAGE_IP, $script_path);
  $storageFolders = array();
  foreach($storageFolders_tmp as $value){
    array_push($storageFolders, trim($value));
  }
  return $storageFolders;
}
function is_same_source_folder($backupSourceFolder){
  global $BACKUP_SOURCE_FOLDERS;
  $isSame = 0;
  foreach($BACKUP_SOURCE_FOLDERS as $value){
    if($value['SOURCE'] == $backupSourceFolder){
      $isSame =1;
      break;
    }
  }
  return $isSame;
}
function validate_javaScripts(){
  global $BACKUP_SOURCE_FOLDERS;
  global $currentTableInfo;
  $DBtableNamesArr = get_mdb_table_names();
  echo "<SCRIPT language=JavaScript>\n";
  $i = 0;
  foreach($DBtableNamesArr as $key => $value){
    echo "tableNameArr[$i] = '$key';\n";
    $i++;
  }
  $i = 0;
  foreach($BACKUP_SOURCE_FOLDERS as $value){
    if($value['SOURCE']){
      echo "linkedSourceFolderArr[$i] = '".$value['SOURCE']."';\n";
      $i++;
    }  
  }
  echo "</SCRIPT>\n";
} 
function is_same_link($backupSourceFolder,$Project,$oldName,$SOURCE_COMPUTER){
  global $BACKUP_SOURCE_FOLDERS;
  if(!isset($BACKUP_SOURCE_FOLDERS[$oldName]['SOURCE_COMPUTER'])) return 0;
  $diff_arr = array_diff_assoc($BACKUP_SOURCE_FOLDERS[$oldName]['SOURCE_COMPUTER'],$SOURCE_COMPUTER);
  if(array_key_exists($oldName, $BACKUP_SOURCE_FOLDERS) && $BACKUP_SOURCE_FOLDERS[$oldName]['SOURCE'] == $backupSourceFolder && $BACKUP_SOURCE_FOLDERS[$oldName]['DEFAULT_PROJECT_ID'] == $Project && !count($diff_arr)){
    return 1;
  }else{
    return 0;
  }
}
function check_sourcr_folder($folderName, $script_path, $arrkey=''){//--$arrkey used by modify---
  //called by action 'create'(first 3 argus) or 'update(all argus)'. 
  //--return 1 found $folderName is exist and not used by others.
  //--return 2 $folderName == '';
  //--return -2 $folderName is not exist.
  //--return -1 $folderName is exist but used by others.
  if(!$folderName) return 2;
  global $STORAGE_FOLDER,$theaction;
  global $BACKUP_SOURCE_FOLDERS;
  //$folderName .= '/';
  $isDir = process_outsite_dir($folderName, STORAGE_IP, $script_path, "isDir");
  if(trim($isDir[0])){
    foreach($BACKUP_SOURCE_FOLDERS as $key => $value){
      if($key == $arrkey) continue;
      if($value['SOURCE'] == $folderName){
        return -1;//--source folder used by others------
      }
    }
    return 1;
  }else{
    return -2;//--source folder not exist------
  }
}
function format_source_folder($sourceFolder){
  if(!trim($sourceFolder)) return '';
  if(!preg_match('/\/$/', $sourceFolder)) $sourceFolder .= '/';
  return $sourceFolder;
}
function is_empty_or_unexist_folder($folderName){
  global $script_path;
  $isEmpty_or_unexist = process_outsite_dir($folderName, STORAGE_IP, $script_path, "isEmpty_or_unexist");
  return trim($isEmpty_or_unexist[0]);
}

function upload_logo($currentName, $previousName=''){
  global $_FILES,$logoDir;
  if(!$currentName) $currentName = $previousName;
  $uploaded_file_name = $currentName . "_logo.gif";
  $new_pic_name = $logoDir . $uploaded_file_name;
  if(!$fileAtrArr = @getimagesize($_FILES['frm_Image']['tmp_name'])){
    return 1;
  }elseif($fileAtrArr[2] != 1){
    return $img_msg = "<font color=#FF0000>image ".$_FILES['frm_Image']['name']." is not a GIF file.</font>";
  }elseif($fileAtrArr[0] > 134 || $fileAtrArr[1] >134){
    return $img_msg = "<font color=#FF0000>image ".$_FILES['frm_Image']['name']." bigger than 134X134. Please upload a small one.</font>";
  }elseif(is_file($new_pic_name) && !$previousName){
    return $img_msg = "<font color=#FF0000>The file $uploaded_file_name has been exists.</font>";
  }
  $previousFileFullName = $logoDir . $previousName . "_logo.gif";
  $tmpFileName = $logoDir . $previousName . "_logo_tmp.gif";
  if(is_file($previousFileFullName)){
    rename($previousFileFullName, $tmpFileName);
  }  
  if(!move_uploaded_file($_FILES['frm_Image']['tmp_name'], $new_pic_name)){
    if(is_file($tmpFileName)) rename($tmpFileName,$previousFileFullName);
    return $img_msg = "<font color=#FF0000>Possible file upload attack! Please try again</font>";
  }else{
    if(is_file($tmpFileName)) unlink($tmpFileName);
    return 1;
  }
}

function get_backup_db_tables_info($DBcreateStatusArr,$drop=0){
  global $noSourceFolder,$autoSearch,$addNewAautoSearch,$notes_info_flag;
  
  $notes_info_1 = "This machine has been set up for raw file back up only.";
  $notes_info_2 = "This machine has been set up for raw file back up and auto-search.";
  $notes_info_3 = "This machine has been set up for auto-search only.";
  
  $notes_info_arr = array('6' => $notes_info_1,'7' => $notes_info_2,'8' => $notes_info_3);
  $notes_info = '';
  $ok_str = '';
  $ok_num = 0;
  $not_ok_num = 0;
  $delete_all_flag = 0;
  $total_cou = count($DBcreateStatusArr);
  if(($total_cou == 1 || $total_cou == 6) && $drop) $delete_all_flag = 1;
  
  foreach($DBcreateStatusArr as $value){
    if($value == 1){
      $ok_num++;
    }elseif($value == 2){
    }else{
      $not_ok_num++;      
    }
  }
  $ok_flag = 1;
  $o_count = 0;
  $other_count = 0;
  $n_count = 0;
  foreach($DBcreateStatusArr as $key => $value){
    if($value == 1){
      if($ok_str){
        if($o_count == $ok_num-1){
          $ok_str .= " and ";
        }else{
          $ok_str .= ", ";
        }  
      }  
      $ok_str .= $key;
      $o_count++;
    }elseif($value == 2){
    }else{
      if($not_ok_str){
        if($n_count == $not_ok_num-1){
          $not_ok_str .= " and ";
        }else{
          $not_ok_str .= ", ";
        }  
      }  
      $not_ok_str .= $key;
      $n_count++;
      $ok_flag = 0;
    }
  }
  $DBstatusArr[0] = $ok_flag;
  $Table = "Table ";
  if($ok_flag){
    if($ok_str){
      if($o_count > 1) $Table = "Tables ";
      $status = " have been created.";
      if($drop) $status = " have been dropped.";
      $ok_str = $Table.$ok_str.$status;
    }  
    $table_status_t = $ok_str;
  }else{
    if($not_ok_str){
      if($n_count > 1) $Table = "Tables ";
      $status = " can not be created.";
      if($drop) $status = " can not be dropped. <br>Contact your DB administrator to drop them manually.";
      $not_ok_str = $Table.$not_ok_str.$status;
    }  
    $table_status_t = $not_ok_str;
  }
  if(!$table_status_t){
    $DBstatusArr[1] = '';
  }else{
    $table_status = '('.$table_status_t.')';
    /*if(isset($noSourceFolder) && $noSourceFolder){  
      $notes_info = $notes_info_3;
    }else{
      if((isset($autoSearch) && $autoSearch) || isset($addNewAautoSearch) && $addNewAautoSearch){
        $notes_info = $notes_info_2;
      }else{
        $notes_info = $notes_info_1;
      }
    }*/
    if(isset($notes_info_flag) && isset($notes_info_arr[$notes_info_flag])){
      $notes_info = $notes_info_arr[$notes_info_flag];
    }  
    if($delete_all_flag){
      $DBstatusArr[1] = $table_status;
    }else{
      $DBstatusArr[1] = $notes_info."<br>".$table_status;
    }
  }  
  return $DBstatusArr;
}

function print_mass_computer_attr($SOURCE_COMPUTER){
  global $TB_CELL_COLOR,$SOURCE_COMPUTER_lable,$SOURCE_COMPUTER_comment,$no_computer_attr_flag,$tmp_checkek;
//print_r($SOURCE_COMPUTER);  
   
  $i = 0;
  foreach($SOURCE_COMPUTER as $tmpKey => $tmpVal){
    $read_only = '';
    if(isset($tmp_checkek) && $tmp_checkek){
      $read_only = "readOnly";
      $tmpVal = '';
    }
    $comment_str = '';
    if($SOURCE_COMPUTER_comment[$tmpKey]){
      $comment_str = "<div class=maintext>".$SOURCE_COMPUTER_comment[$tmpKey]."</div>";
    }
    if($i == 0){
    $i++;  
  ?>
          <tr>
          <td bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top align=right width=15% rowspan='5'>
              <div class=maintext><b>Mass spectrometer acquisition computer&nbsp;</b>:&nbsp;<br><br> 
              Create user account which will be used in ProHits scripts in the computer. 
              Then share raw file folder (e.g. C:\Data) security permissions (read) with the user. 
              All files and subfolders in the raw file folder will be saved in ProhitsStorage.
              </div></td>          
          <td bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top align=right width=37% nowrap>
            <div class=maintext><b><?php echo $SOURCE_COMPUTER_lable[$tmpKey];?></b>:&nbsp;</div>                   
          </td>
          <td bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top>
            <input type="text" name="<?php echo $tmpKey;?>" size="30" value="<?php echo $tmpVal;?>" <?php echo $read_only;?>>
            <?php echo $comment_str?>              
          </td>
          </tr> 
  <?php }else{?>
          <tr>          
          <td bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top align=right width=37% nowrap>
            <div class=maintext><b><?php echo $SOURCE_COMPUTER_lable[$tmpKey];?></b>:&nbsp;</div>                   
          </td>
          <td bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top>
            <input type="text" name="<?php echo $tmpKey;?>" size="30" value="<?php echo $tmpVal;?>" <?php echo $read_only;?>>
          <?php echo $comment_str?>                      
          </td>
          </tr>
  <?php }
  }
}
function Toggle_computer_arr($is_greeied = 0){
  global $SOURCE_COMPUTER;
  foreach($SOURCE_COMPUTER as $key => $val){
    if($is_greeied){
      echo "theForm.$key.value = '';\n";
      echo "theForm.$key.readOnly = true;\n";
    }else{
      echo "theForm.$key.readOnly = false;\n";
    }
  }
}
?>