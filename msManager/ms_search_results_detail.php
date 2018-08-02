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
$frm_PlateID = '';
$taskIndex = '';
$task_arr = array();
$searchEngine_arr = array();
$plate_ID = 0;  
$frm_peptide_validation = '';
$frm_execute_time = 0;
$frm_other_value = '';
$frm_score = '';
$frm_status = '';
$frm_setdate = '';
$frm_setBy = '';
$frm_saveby = '';
$frm_peptide_min_score='-1';
$frm_requireBoldRed = 1;
$frm_sigthreshold = '0.05';
$frm_TPP_PARSE_MIN_PROBABILITY = 0.05;
$frm_geneLevelFDR = 0.01;
$frm_pepPROBABILITY = 0.85;
$frm_gpmeionxpect_dot = 0;
$frm_gpmexpect_dot = 0;
$frm_report = 0;
$frm_parser_type = '';
$has_parser_permit = false;
$parser_checkbox_arr = array();
$mascot_session_ID = '-1';
$_mudpit = 1;
$search_type = '';

$is_SWATH_file = '';
$SWATH_app = '';

//sequest---------
$sequest_rank = 2;

$order_icon = 'icon_order_down.gif';
$order_by = 'SearchEngines';  
$tppTaskID = 0;
$tppmsg = '';

$warning_msg = '';
$status_color = '';
$no_DECOY = '';
$DECOY_prefix = '';

$frm_geneLevelHits = '';
$parseOldGeneLevel = '0';

require("classes/Storage_class.php");
include_once("autoSearch/auto_search_mascot.inc.php");
include_once ( "./is_dir_file.inc.php");

if($_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}

/*echo "<pre>"; 
print_r($request_arr);
echo "</pre>";*/

if(isset($request_arr['view_file'])){
  $filePath=$request_arr['filePath'];
  header("Content-Type: application/octet-stream");  //download-to-disk dialog
  header("Content-Disposition: attachment; filename=\"".basename($filePath)."\"");
  header("Content-Transfer-Encoding: binary");
  header("Content-Length: "._filesize($filePath));
  readfile("$filePath");
  //_output($filePath);
  exit;
}

include("./ms_search_header.php");
$SID = session_id();

$comet_parm_dir = "./autoSearch/";
$default_comet_param_arr = get_comet_default_param($comet_parm_dir);
$demo_search_results = '';
if($_SERVER['HTTP_HOST'] == 'prohitsms.com'){
 $demo_search_results = 'demo_search_results.php';
}
   
if(!$frm_PlateID) fatalError("There is no table ID passed", __LINE__);
$perm_insert = 0;
$perm_modify = 0;
$perm_delete = 0;
$SQL  = "select P.Insert, P.Modify, P.Delete from PagePermission P, Page G where P.PageID=G.ID and G.PageName like 'Auto Save%' and UserID=$USER->ID";
$record = $prohitsDB->fetch($SQL);


if(count($record)){
  $perm_modify = $record['Modify'];
  $perm_delete = $record['Delete'];
  $perm_insert = $record['Insert'];
}  
//get tasks ----------------------------------------------------------------------
if(isset($iniTaskID) and $iniTaskID){
  $SQL = "select PlateID from ". $tableSearchTasks . " where ID='".$iniTaskID."'";
  $theTask = $managerDB->fetch($SQL);
  if($theTask) $frm_PlateID = $theTask['PlateID'];
}

$SQL = "select ID, 
               SearchEngines, 
               Parameters, 
               TaskName, 
               LCQfilter, 
               DataFileFormat,
               StartTime, 
               Schedule, 
               Status, 
               UserID, 
               ProjectID 
        from $tableSearchTasks
        where PlateID='$frm_PlateID' 
        and ".str_replace("T.", "",$where_project)."
        order by ID desc ";
              
$task_records = $managerDB->fetchAll($SQL);

/*echo "<pre>";
print_r($task_records);
echo "</pre>";*/


if(!$task_records) exit;  
if(isset($iniTaskID)){
  foreach($task_records as $key => $value){
    if($value['ID'] == $iniTaskID) $taskIndex = $key;
  }
}

if($taskIndex and $taskIndex < count($task_records)){
 $task_arr = $task_records[$taskIndex];
}else{
 $taskIndex = 0;
 $task_arr = $task_records[0];
}
if($task_arr){
  if(preg_match("/DIAUmpire=(.+)$/", $task_arr['SearchEngines'], $matches)){////=====================
    $is_SWATH_file = true;
    $SWATH_app = 'DIAUmpire';
  }else if(preg_match("/MSPLIT=(.+)$/", $task_arr['SearchEngines'], $matches)){
    $is_SWATH_file = true;
    $SWATH_app ='MSPLIT';
    $warning_msg = " <a href=\"javascript:  MSPLIT_status('".$task_arr['ID']."');\"><b>[MSPLIT STATUS]</b></a>";
  }
  if($task_arr['Status'] == 'Running'){
    if(task_is_running($table,  $task_arr['ID'])){
      $status_color = "lightgreen";
    }else{
      $status_color = "yellow";
      $task_arr['Status'] = 'Error';
      $warning_msg .= " (<b>The task was set to run. But it is not running. Click task detail to stop it or run it again.</b>)";
    }
  }
  if($task_arr['UserID']){
    $SQL = "select Fname, Lname from User where ID='".$task_arr['UserID']."'";
    $user_rd = $PROHITSDB->fetch($SQL);
    if($user_rd)$frm_setBy = $user_rd['Fname'] . " " . $user_rd['Lname']; 
  }
  $SQL = "select ID, FileName, ProhitsID, ProjectID, Date from $table T where " . $where_project . "  and ID in($frm_PlateID)";
  $foldersRD = $managerDB->fetchAll($SQL);
  if(!$foldersRD) fatalError("no permission for the plate", __LINE__);  
}else{
  exit;
}

$task_ID = $task_arr['ID'];

$searchEngine_arr = explode(";", $task_arr['SearchEngines']);
sort($searchEngine_arr); 

$SQL = "select ID, Name, DBname from Projects order by ID";
$rds = $prohitsDB->fetchAll($SQL);
$tmp_db_obj_arr = array();
$tmp_db_name = '';
//create mysqlDB objects for all hit databases
for($i=0; $i < count($rds); $i++){
  $projectName_arr[$rds[$i]['ID']] = $rds[$i]['Name'];
  $tmp_db_name = $rds[$i]['DBname'];
  
  if(!isset($tmp_db_obj_arr[$rds[$i]['DBname']])){
    $tmp_db_obj_arr[$tmp_db_name] = new mysqlDB($HITS_DB[$tmp_db_name]);
  }
  $hitDB_obj_arr[$rds[$i]['ID']] = $tmp_db_obj_arr[$tmp_db_name];
}
if(!$tppTaskID and $myaction != 'new_ttp_task'){
  $tableTppTasks = $table . "tppTasks";
  $SQL = "SELECT `ID` FROM $tableTppTasks WHERE `SearchTaskID` = '$iniTaskID' ORDER BY ID DESC LIMIT 1";
  $tppTask_arr = $managerDB->fetch($SQL);
  $tppTaskID = '';
  if($tppTask_arr && $tppTask_arr['ID'])  $tppTaskID = $tppTask_arr['ID'];
}
?>  
<script src="../common/javascript/prohits.divDropDown.js" type="text/javascript"></script> 
<script language="javascript">
var winW = 630, winH = 460;
if (parseInt(navigator.appVersion)>3) {
 if (navigator.appName=="Netscape") {
  winW = window.innerWidth;
  winH = window.innerHeight;
 }
 if (navigator.appName.indexOf("Microsoft")!=-1) {
  winW = document.body.offsetWidth;
 }
}

/*function checkall(field){
  for (var i = 1; i < field.length; i++){
    field[i].checked = field[0].checked;
  }
}*/

function selectTask(theIndex){
  var theForm = document.form_task;
  theForm.taskIndex.value = theIndex;
  theForm.tppTaskID.value = '';
  theForm.submit();
}
function sortList(orderby){
  var theForm = document.form_task;
  if(theForm.order_by.value == orderby){
    theForm.order_by.value = orderby + ' DESC';  
  }else{
    theForm.order_by.value = orderby;
  }
  theForm.submit();
}
function linkProhitsID(theID, tppR_table_name, task_ID, link_type){
  file = './ms_storage_pop_link_prohits_id.php?tableName=' + '<?php echo $table;?>' + '&raw_file_ID=' + theID  + '&tppR_table_name=' + tppR_table_name + '&task_ID=' + task_ID + '&link_type=' + link_type;
  popwin(file,600,450);
}
function MSPLIT_status(task_ID){
  file = './MSPLIT_status.php?tableName=' + '<?php echo $table;?>' + '&taskID=' + task_ID;
  popwin(file,700,500);
}
function addSearchResults(theID, searchEngine){
  file = './ms_storage_pop_add_search_results.php?tableName=' + '<?php echo $table;?>' + '&raw_file_ID=' + theID + '&SearchEngine=' + searchEngine + '&taskID=<?php echo $task_arr['ID'];?>';
  popwin(file,600,450);
}
function pop_mascot(mascot_ip, file){
 <?php if(MASCOT_USER){?>
  var tmp_url = "http://"+mascot_ip+"<?php echo MASCOT_CGI_DIR;?>/login.pl";
    tmp_url += "?action=login&username=<?php echo MASCOT_USER;?>&password=<?php echo MASCOT_PASSWD;?>";
    tmp_url += "&display=nothing&savecookie=1&referer=master_results_2.pl?file=" + file;
 <?php }else{?>
  var tmp_url = "http://"+mascot_ip+"<?php echo MASCOT_CGI_DIR;?>/master_results.pl?file=" + file;
 <?php }?>
 window.open(tmp_url,"mascot_win", "toolbar=1,menubar=1,scrollbars=1,resizable=1,width=800,height=800"); 
}
function open_dir(theID){
  var theForm = document.form_task;
  theForm.open_dir_ID.value=theID;
  theForm.order_by.value='';
  theForm.action = 'ms_storage_raw_data_plate.php';
  theForm.submit();
}
 
function download(FileID, taskID, searchType){
  var file ='<?php echo "http://".$storage_ip.dirname($_SERVER['PHP_SELF'])."/autoBackup/download_raw_file.php?SID=". $SID. "&tableName=$table";?>' + '&ID=' + FileID + '&taskID='+taskID+ '&searchType='+searchType;
  popwin(file,500,400)
}

function download_general(FileID, taskID, searchType,general){
  var file ='<?php echo "http://".$storage_ip.dirname($_SERVER['PHP_SELF'])."/autoBackup/download_raw_file.php?SID=". $SID. "&tableName=$table";?>' + '&ID=' + FileID + '&taskID='+taskID+ '&searchType='+searchType + '&general='+general;
  popwin(file,500,400)
}

function highlightTR_group(WellID, row_count, highlightColor, defaultColor){
  for(var i=0; i<row_count; i++){
    var row_id = WellID + '_' +i;
    var row_obj = document.getElementById(row_id);
    highlightTR(row_obj, 'click', highlightColor, defaultColor);
  }
}
function showhide(DivID, lableDivID){
  var obj = document.getElementById(DivID);
  if(lableDivID){
    var obj_a = document.getElementById(lableDivID);
  }
  if(obj.style.display == "none"){
    obj.style.display = "block";
    if(lableDivID){
      obj_a.innerHTML = "[&nbsp;Hide&nbsp;]";
    }
  }else{
    obj.style.display = "none";
    if(lableDivID){
      obj_a.innerHTML = "[&nbsp;Detail&nbsp;]";
    }
  }  
}

function toggle_tasks(){
  var single_obj = document.getElementById('single');
  var all_obj = document.getElementById('all');
  var lable_obj = document.getElementById('lable');
  if(lable_obj.innerHTML == '[+]'){
    all_obj.style.display = "block";
    single_obj.style.display = "none";
    lable_obj.innerHTML = '[-]';
  }else{
    all_obj.style.display = "none";
    single_obj.style.display = "block";
    lable_obj.innerHTML = '[+]';
  }  
}

function toggle_detail(base_id){
  var selected_obj = document.getElementById(base_id);
  var selected_a_id = base_id + '_a';
  var selected_a_obj = document.getElementById(selected_a_id);
  var inner_str = trimString(selected_a_obj.innerHTML);
  if(inner_str == '+'){
    selected_obj.style.display = "block";
    selected_a_obj.innerHTML = '-';
  }else{
    selected_obj.style.display = "none";
    selected_a_obj.innerHTML = '+';
  }
}
function pop_Search_Engine_Parameters(TaskID){
  file = './ms_pop_search_engine_parameters.php?task_ID=' + TaskID + '&theaction=pop_Search_Engine_Parameters&table=<?php echo $table?>&tableSearchTasks=<?php echo $tableSearchTasks?>&where_project=<?php echo $where_project?>';
//alert(file);  
  popwin(file,800,800);
}
</script>
<form action="<?php echo $PHP_SELF;?>" method="post" name="form_task" id="form_task">
    <input type="hidden" name="table" value="<?php echo $table;?>">
    <input type=hidden name=tableName value='<?php echo $table;?>'>
    <input type="hidden" name="iniTaskID" value="<?php echo $iniTaskID;?>"> 
    <input type=hidden name=open_dir_ID value=''>
    <input type="hidden" name="frm_PlateID" value="<?php echo $frm_PlateID;?>">
    <input type="hidden" name="taskIndex" value="<?php echo $taskIndex;?>"> 
    <input type="hidden" name="order_by" value="<?php echo $order_by;?>"> 
    <input type="hidden" name="tppTaskID" value="<?php echo $tppTaskID;?>">    
    <table border=0 width=97% cellspacing=5 cellpadding=0>
    <tr>
      <td align=center colspan=2><br>
     <font face="Arial" size="+1" color="<?php echo $menu_color;?>"><b><?php echo $table;?> Search Results</b></font>
     <hr width="100%" size="1" noshade>
      </td> 
    <tr>
      <td valign=top colspan=2>
         <table cellspacing="1" cellpadding="0" border="0" width=100%>
<?php //if($task_arr['ID']){?>
          <tr><td><b>Task ID</b>:</td>
          <td colspan=2>
          <a title='to task detail' href="ms_search_task_view.php?table=<?php echo $table;?>&theTaskID=<?php echo $task_arr['ID'];?>&tppTaskID=<?php echo $tppTaskID?>">
          <font color="#FF0000"><?php echo $task_arr['ID'];?></font>
          </a>
          </td>
          </tr>
<?php //}?>
          <tr>
            <td><b>Task Name</b>:</td>
            <td colspan=2><?php echo $task_arr['TaskName'];?>
            </td>
          </tr>
          <tr>
            <td><b>Task Project Name</b>:</td>
            <td colspan=2><?php echo $pro_access_ID_Names[$task_arr['ProjectID']];?> <a href="javascript:popwin('ms_search_pop_edit_project_id.php?table=<?php echo $table;?>&theTaskID=<?php echo $task_arr['ID'];?>',500,400, 'dbwin');"">[Edit]</a>
            </td>
          </tr>
          <tr>
            <td bgcolor=#d0d0d0><b>Folder ID</b></td>
            <td bgcolor=#d0d0d0><b>Folder Name</b></td>
            <td bgcolor=#d0d0d0><b>Folder Project</b></td>
          </tr>
<?php 
$dis_projectArr = array();
if($foldersRD){
  for($i = 0; $i < count($foldersRD); $i++){
    echo "<tr bgcolor=#deedf3>\n";
    echo "<td>".$foldersRD[$i]['ID'] . "</td>\n"; 
    $folder_tree = create_dir_tree($foldersRD[$i]['ID'],$table, true);
    echo "<td>".$folder_tree."</td>\n";
    $tmp_pro = ($foldersRD[$i]['ProjectID'])?$pro_access_ID_Names[$foldersRD[$i]['ProjectID']]:"&nbsp;";
    echo "<td>".$tmp_pro."</td>\n";  
    echo "</tr>\n";
  }
}
$styleColor = 'white';
if($task_arr['Status'] == 'Running'){
  $styleColor = 'lightgreen';
}else if($task_arr['Status'] == 'Error'){
  $styleColor = 'yellow';
}
?> 
        </table>
        <table cellspacing="1" cellpadding="0" border="0" width=100%>
        <tr><td colspan="2">
        <?php echo ($task_arr['StartTime'])?"<b>Start Time: </b><font color=red> ".$task_arr['StartTime']." </font>":"";?><br>
        </td></tr>
        <tr>
          <td>
          <?php echo ($frm_setBy)?"<b>Set By: </b><font color=red> ".$frm_setBy." </font>":"";?>
          </td>
          <td align=right>
            <a id="lable" href="javascript: toggle_tasks()" class="button">[+]</a>
          </td>
        </tr> 
        </table>
      </td>
    </tr>
    <tr>
      <td colspan=2>
        <table border=0 width=100% cellspacing=0 cellpadding=0>
        <tr>
        <td bgcolor=#d2d2d2>
        <DIV id="single" style="display:block;">
        <table width=100% cellspacing=1 cellpadding=3>
          <tr>
            <th>Task ID</th>
            <th>Task Name</th>
            <th>Search Engine</th>
            <th>Schedule</th>
            <th>Status</th>
          </tr>
          <?php   
            print_task_records($task_records,'single');
          ?>
        </table>
        </DIV> 
        <DIV id="all" style="display:none;">
        <table width=100% cellspacing=1 cellpadding=3>
          <tr>
            <th>Task ID</th>
            <th>Task Name</th>
            <th>Search Engine</th>
            <th>Schedule</th>
            <th>Status</th>
          </tr>
          <?php   
            print_task_records($task_records,'single');
            print_task_records($task_records);
          ?>
        </table>
        </DIV> 
        </td></tr></table>
        </td>
    </tr>
    <tr>
    <td colspan=2>
    <?php 
$SQL = "SELECT `SearchEngines` FROM $tableSearchTasks WHERE `ID`='".$task_arr['ID']."'";
$search_type_arr = $managerDB->fetch($SQL);
if(preg_match("/^iProphet/i", $search_type_arr['SearchEngines'])){
  $search_type = 'iProphet';
}

$SQL = "SELECT `ID`, `SearchTaskID`, `Status`, `UserID`, `ProjectID` FROM ".$table."tppTasks WHERE `SearchTaskID`='".$task_arr['ID']."'";
$tmp_tpp_task_arr = $managerDB->fetchALL($SQL);

$task_tpp_ID_str = '';
foreach($tmp_tpp_task_arr as $tmp_tpp_task_val){
  if($task_tpp_ID_str) $task_tpp_ID_str .= ',';
  $task_tpp_ID_str .= $tmp_tpp_task_val['ID'];
}

if($task_tpp_ID_str){
  $SQL = "SELECT `TppTaskID`, 
                 `SearchEngine`
          FROM ".$table."tppResults 
          WHERE `TppTaskID` IN($task_tpp_ID_str)
          AND `SearchEngine`='iProphet'
          GROUP BY `TppTaskID`";
  $tmp_tpp_resl_arr = $managerDB->fetchAll($SQL);
  if($tmp_tpp_resl_arr && $tmp_tpp_resl_arr[0]['SearchEngine']){
    $search_type = 'iProphet';
  }
}  
    //****************************************************
    include("./tppTask/tpp_task_form.inc.php");
    include("./autoSave/auto_save_form.inc.php");
    //****************************************************
    $tppTasks_id_str = implode(",", $tppTasks_id_arr);
    ?>
    </td>
    </tr>
    <tr>
      <td align=right colspan="2">
      <input type=button value='Reload' onClick='reloadMe()' align=left>&nbsp;&nbsp;&nbsp;
      <input type=button value='Delete Parsed Hits' onClick='remove_listed_hits()' align=left>
      </td> 
    <tr>
  </table>
  <input type="hidden" name="myaction" value="<?php echo $myaction;?>">
  <input type="hidden" name="tppTasks_id_str" value="<?php echo $tppTasks_id_str;?>">
<?php 
//get all search results ----------------------------------------------------------
$SQL = "SELECT T.FileName,
          T.ProhitsID,
          T.ProjectID,
          T.FolderID,
          T.User,
          T.Size,
          R.WellID, 
          R.TaskID, 
          R.DataFiles, 
          R.SearchEngines, 
          R.Date, 
          R.SavedBy
          FROM $table T, $tableSearchResults R where T.ID=R.WellID and TaskID='". $task_arr['ID']."' order by $order_by, WellID";
$result_records = $managerDB->fetchAll($SQL);

/*echo "<pre>";
print_r($result_records);
echo "</pre>";*/

if($search_type != 'iProphet'){
  $wellID_arr = array();
  foreach($result_records as $tmp_val){
    if(!array_key_exists($tmp_val['WellID'], $wellID_arr)){
      $wellID_arr[$tmp_val['WellID']] = 1;
    }else{
      $wellID_arr[$tmp_val['WellID']]++;
    }
  }
  foreach($wellID_arr as $wellID_val){
    if($wellID_val > 1){
      $search_type = 'iProphet';
      break;
    }
  }
}  

//get search engines from search results.
$SQL = "SELECT SearchEngines FROM $tableSearchResults where TaskID='". $task_arr['ID']."' group by SearchEngines order by SearchEngines";
$search_search_rds = $managerDB->fetchAll($SQL);

if($search_type == 'iProphet'){
  $tmp_result_records = array();
  foreach($result_records as $result_vals){
    if(!array_key_exists($result_vals['WellID'], $tmp_result_records)){
      $tmp_result_records[$result_vals['WellID']] = $result_vals;
      $tmp_result_records[$result_vals['WellID']]['Engines_File'] = array();
    }
    $tmp_Engines_File = $result_vals['SearchEngines']."===".$result_vals['DataFiles']."===".$result_vals['SavedBy']."===".$result_vals['Date'];
    array_push($tmp_result_records[$result_vals['WellID']]['Engines_File'], $tmp_Engines_File);
  }
  $result_records = array();
  foreach($tmp_result_records as $tmp_result_vals){
    array_push($result_records, $tmp_result_vals);
  }
}
$order_icon = (strstr($order_by, "DESC"))?'icon_order_up.gif':'icon_order_down.gif';
$DataFiles_dir = dirname($result_records[0]['DataFiles']);
?>
  <table border=0 width=96% cellspacing=0 cellpadding=0>
<?php 
if($result_records[0]['SearchEngines'] == 'MSPLIT' && $result_records[0]['DataFiles'] && $result_records[0]['DataFiles'] !='rawFileError'){
    $DataFiles_dir = dirname(dirname($result_records[0]['DataFiles']))."/Results";
    
    $Openswath = $DataFiles_dir.'/MSPLIT_Results_for_Openswath.txt';
    $Peakview = $DataFiles_dir.'/MSPLIT_Results_for_Peakview.txt';
    $Skyline = $DataFiles_dir.'/MSPLIT_Results_for_Skyline.txt';
    $file_downlad_url = "./autoBackup/download_raw_file.php?SID=".$SID."&tableName=".$table."&clicked=y&filePath=";
?>  
    <tr>
    <td>
      <a href="<?php echo $file_downlad_url.$Openswath?>" class=button>Openswath&nbsp;&nbsp;<img src='./images/icon_download.gif' border=0></a>&nbsp;&nbsp;&nbsp;&nbsp;
      <a href="<?php echo $file_downlad_url.$Peakview?>" class=button>Peakview&nbsp;&nbsp;<img src='./images/icon_download.gif' border=0></a>&nbsp;&nbsp;&nbsp;&nbsp;
      <a href="<?php echo $file_downlad_url.$Skyline?>" class=button>Skyline&nbsp;&nbsp;<img src='./images/icon_download.gif' border=0></a>&nbsp;&nbsp;&nbsp;&nbsp;
    </td>
    </tr>
<?php 
}
?>    
    <tr>
    <td bgcolor=#d2d2d2>
      <table id=searchResult width=100% cellspacing=1 cellpadding=3 border=0>
      <tbody id="searchResultBody">    
      <tr>
        <th><a href="javascript: sortList('WellID')">File ID</a><?php echo (strstr($order_by, 'WellID'))?" <img src='images/$order_icon'>":"";?></th>
        <th><a href="javascript: sortList('FileName')">[Folder ID] / File Name</a><?php echo (strstr($order_by, 'FileName'))?" <img src='images/$order_icon'>":"";?></th>
        <th><a href="javascript: sortList('Size')">Size(KB)</a><?php echo (strstr($order_by, 'Size'))?" <img src='images/$order_icon'>":"";?></th>
        
        
        
        <td align=center bgcolor="#8999e4">
        <a href="javascript: sortList('SearchEngines')"><b>Search Results</b></a>
        <?php echo (strstr($order_by, 'SearchEngines'))?" <img src='images/$order_icon'>":"";?>
<?php  
        if($search_type == 'iProphet'){
          echo "<input value ='' type=hidden name=frm_wells_iProphet>";
          echo "<input value ='' type=hidden name=mergeboxes_iProphet>";
          echo "<input value ='' type=hidden name=frm_tpp_iProphet>";
        }
        $searchEngines = array();
        foreach($search_search_rds as $engine_arr){
          array_push($searchEngines, $engine_arr['SearchEngines']);
          if($engine_arr['SearchEngines'] == 'Mascot' or $engine_arr['SearchEngines'] == 'GPM' or $engine_arr['SearchEngines'] == 'SEQUEST' or $engine_arr['SearchEngines'] == 'COMET' or $engine_arr['SearchEngines'] == 'MSGFPL' or $engine_arr['SearchEngines'] == 'MSPLIT' or $engine_arr['SearchEngines'] == 'MSPLIT_DDA' or $engine_arr['SearchEngines'] == 'MSFragger'){
            echo "<input value ='' type=hidden name=frm_wells_".$engine_arr['SearchEngines'].">";
            echo "<input value ='' type=hidden name=mergeboxes_".$engine_arr['SearchEngines'].">";
            echo "<input value ='' type=hidden name=frm_tpp_".$engine_arr['SearchEngines'].">";
          }
        }
        if($SWATH_app != 'DIAUmpire' and $has_parser_permit and ($frm_parser_type == 'search' or $frm_parser_type == 'both')){
          echo "<br>select all<input type=checkbox name=search_check_all onClick=\"javascript: select_all(this, 'search');\">";
        }       
        if($SWATH_app != 'DIAUmpire' and $USER->Type == 'Admin'){
          echo "&nbsp;<a title='remove all parsed hits' href=\"javascript: removehits('All', 'All')\"><img src=./images/icon_delete.gif border=0 alt='delete all hits'></a>";
        }
?> 
        </td> 
        <td align=center bgcolor=#8999e4><b>TPP</b>
        <?php 
        if($has_parser_permit and ($frm_parser_type == 'TPP' or $frm_parser_type == 'both')){
          echo "<br>select all<input type=checkbox name=tpp_check_all onClick=\"javascript: select_all(this, 'tpp');\">";
        }
        if($USER->Type == 'Admin'){
           echo "&nbsp;<a title='remove all parsed hits' href=\"javascript: removeTPPhits('$tppTaskID', 'All', 'tpp')\"><img src=./images/icon_delete.gif border=0 alt='delete all hits'></a>";
        }
        echo "<input type=hidden name=frm_tppboxes value=''>\n";
        ?>
        </td>
        
        <?php if($search_type == 'iProphet'){?>
        <td td align=center bgcolor=#8999e4>
          <b>iProphet</b>
        <?php 
          if($has_parser_permit and ($frm_parser_type == 'TPP' or $frm_parser_type == 'both')){
            echo "<br>select all<input type=checkbox name=iProphet_check_all onClick=\"javascript: select_all(this, 'iProphet');\">";
          }
          if($USER->Type == 'Admin'){
             echo "&nbsp;<a title='remove all parsed hits' href=\"javascript: removeTPPhits('$tppTaskID', 'All', 'iProphet')\"><img src=./images/icon_delete.gif border=0 alt='delete all hits'></a>";
          }
          ?>
        </td>
      </tr>
       <?php }
$all_rows = '';
$fileNameLables = array();
 
for($i = 0; $i < count($result_records); $i++){
  $linked_arr = array('ProhitsID'=>$result_records[$i]['ProhitsID'], 'ProjectID'=>$result_records[$i]['ProjectID']);
  
  $savedBy = '';
  $tmp_Size = '';
  if($result_records[$i]['SavedBy']) $savedBy = $result_records[$i]['SavedBy'];
  if(is_numeric($result_records[$i]['User']) && $result_records[$i]['User']>0 && $result_records[$i]['ProhitsID']){
    $tmp_icon = "icon_link_y.gif";
    $tmp_title = "<b>Manual-linked</b><br>;;";
  }elseif($result_records[$i]['ProhitsID']){
    $tmp_icon = "icon_link_g.gif";
    $tmp_title = "<b>Auto-linked</b><br>;;";
  }else{
    $tmp_icon = "icon_link.gif";
    $tmp_title = '<b>Unlinked</b><br>;;Click the icon to link the file with sample';
  }  
  if($result_records[$i]['ProhitsID'] and $result_records[$i]['ProjectID']){
    $SQL = "select D.ID, D.BaitID,D.Location, B.GeneName from Band D, Bait B where D.BaitID=B.ID and D.ID='".$result_records[$i]['ProhitsID']."'";
    $tmp_sample_rd = $hitDB_obj_arr[$result_records[$i]['ProjectID']]->fetch($SQL);
    if(isset($projectName_arr[$result_records[$i]['ProjectID']])){
      $tmp_title .= "Project: " . $projectName_arr[$result_records[$i]['ProjectID']];
      if($tmp_sample_rd){
        $tmp_title .= "<br>Bait: (".$tmp_sample_rd['BaitID'].")".$tmp_sample_rd['GeneName'];
        $tmp_title .= "<br>Sample: ".$tmp_sample_rd['Location'];
      }else{
        $tmp_title .= "<br><font color=red>broken link:sample ID ". $result_records[$i]['ProhitsID']."</font>";
      }
    }else{
       $tmp_title .= "<br><font color=red>broken link:project ID ". $result_records[$i]['ProjectID']."</font>";
    }
  }
  if($result_records[$i]['Size']){
    $tmp_Size = number_format(ceil($result_records[$i]['Size']/1024));
  }
  
  $fileNameLables[$result_records[$i]['WellID']] =  "[".$result_records[$i]['FolderID']."] / ".$result_records[$i]['FileName'];
   
  if($search_type == 'iProphet' ){
    $row_count = 0;
    print_iProphet($result_records[$i]);
  }else{
     
    $row_line = "
      <tr bgcolor=\"#ffffff\" onmousedown=\"highlightTR(this, 'click', '#CCFFCC', '#ffffff')\">
        <td valign=top>".$result_records[$i]['WellID']."</td>
        <td valign=top>".$fileNameLables[$result_records[$i]['WellID']]."</td>
        <td valign=top align=right>".$tmp_Size;
    echo $row_line;
    $tmp_engine = $result_records[$i]['SearchEngines'];
    if($result_records[$i]['DataFiles'] and $result_records[$i]['DataFiles'] !='rawFileError'){
      $all_rows = $all_rows . $row_line ."</td>
      <td align=center>".$tmp_engine."</td>
      <td align=center>
      <input type=checkbox name=mergeboxes_".$tmp_engine." value='".$result_records[$i]['WellID']."'>
      </td>
    </tr>\n";
    }
?> 
        <a title='<?php echo $tmp_title;?>' href="javascript: linkProhitsID('<?php echo $result_records[$i]['WellID'];?>','','<?php echo $task_arr['ID']?>','<?php echo $tmp_icon?>');" ><img src='./images/<?php echo $tmp_icon;?>' border=0></a>&nbsp; </td>
      <td align=left valign=top><?php echo getResultURL($result_records[$i]);?></td>
      <td>
      <?php 

      $tpp_link_str = '';
      if($tppTaskID and $myaction != 'new_ttp_task'){
      
        $tpp_link_str = getTppResultLink($result_records[$i]['WellID'], $tmp_engine);
      }
      if($tpp_link_str){
        echo $tpp_link_str;
      }else{
        if(($myaction == 'new_ttp_task' or $myaction == 'repeat')
          and $result_records[$i]['DataFiles'] 
          and $result_records[$i]['DataFiles'] !='rawFileError'){
          echo "<input type=checkbox name=frm_tppboxes value='".$tmp_engine.$result_records[$i]['WellID']."'>\n";
        }
      }
      if(strpos($tpp_link_str, 'NoProtXML') and $myaction == 'repeat'and $result_records[$i]['DataFiles'] and $result_records[$i]['DataFiles'] !='rawFileError'){
         echo "<input type=checkbox name=frm_tppboxes value='".$tmp_engine.$result_records[$i]['WellID']."'>\n";
      }
?>
      </td>
    </tr>
<?php 
  }
}

if($myaction != 'new_ttp_task' and $tppResults){
//if($myaction != 'new_ttp_task' and $tppResults && $search_type != 'iProphet'){
          foreach($tppResults as $row){
            $nameLable = '';
            $idLable = '';
            $tmp_arr = explode(",",$row['WellID']);
            foreach($tmp_arr as $id){
              if(trim($id)){
                $idLable .= ($idLable)? "<br>".$id:$id;
                if(isset($fileNameLables[$id])){
                  $nameLable .= ($nameLable)? "<br>".$fileNameLables[$id]:$fileNameLables[$id];
                }  
              }
            }
            //echo $row['ProhitsID'];
            if($row['ProhitsID']){
              $tmp_icon = "icon_link_y.gif";
            }else{
              //$tmp_icon = "icon_link.gif alt=add the link";
              $tmp_icon = "icon_link.gif";
            }
            echo "
            <tr>
            <td bgcolor=white>".$idLable."</td>
            <td bgcolor=white>".$nameLable."</td>            
            <td bgcolor=white align=center>merged&nbsp;&nbsp;<a href=\"javascript: linkProhitsID('".$row['WellID']."','tppResults','".$task_arr['ID']."','$tmp_icon');\"><img src='./images/$tmp_icon' border=0></a>&nbsp; &nbsp;</td>
            <td bgcolor=white align=center>".$row['SearchEngine']."</td>
            <td bgcolor=white align=center>". getTppResultLink($row['WellID'], $row['SearchEngine'])."</td>\n";
            if($search_type == 'iProphet'){
              echo "<td bgcolor=white align=center>&nbsp;</td>\n";
            }
            echo " </tr>\n";
          }
}
?>
      </tbody>
     </table>
   </td></tr>
   </table>
<script language='javascript'>
  function select_all(thisCheckbox, boxType){
    var theForm = thisCheckbox.form;
    var true_false = thisCheckbox.checked;
  <?php foreach($parser_checkbox_arr as $key=>$value){
      if(strpos($key, '_iProphet')){
        echo "if(boxType=='iProphet') checkAll(theForm.$key, true_false);\n";
      }elseif(strpos($key, '_tpp')){
        echo "if(boxType=='tpp') checkAll(theForm.$key, true_false);\n";
      }else{
        echo "if(boxType=='search') checkAll(theForm.$key, true_false);\n";
      }
    }
  ?>
  }
</script>
   <!----- pop merging table --------->
    <DIV id='tppMerge' style="LEFT: 200px; display: none; POSITION: absolute; TOP: 20px; WIDTH:500; Height:400;BACKGROUND-color:#ded398; border: black solid 1px;">
    <br>
    <b><font size="+1">Select Search Results to Merge</font></b>
    <DIV style="width:480px;
    height:300px;
    overflow:auto;
    border: black solid 1px;
    font-family: tahoma;
    font-size:12px;
    position:relative;
    background-color:#e0e0e0;">
    <table BORDER=0 width=100% cellspacing=1 cellpadding=1>
      <tbody id="popTable">
    <tr>
      <td bgcolor="#bcbcbc">File ID</td>
      <td bgcolor="#bcbcbc">[Folder ID]/File Name</td>
      <td bgcolor="#bcbcbc">Size(KB)</td>
      <td bgcolor="#bcbcbc">Search Results</td>
      <td bgcolor="#bcbcbc">Merge</td>
    </tr> 
    <?php 
    if($all_rows){
      echo $all_rows;
    }else{
      echo "<td colspan=5><font color=\"#FF0000\">no search results</font></td>\n";
    }
    ?>
      </tbody>
    </table>
    </DIV>
    <input type=button value='Merge' onClick="mergeFiles(this.form)";>
    <input type=button value='Close' onClick="$('#tppMerge').slideUp(150);">
    <br><br>
    </DIV>
    <!----- end of the pop merging table ---->
    </form>
<?php    
function getResultURL($record){
  global $perm_modify;
  global $task_arr;
  global $Pro_ID_names;
  global $demo_search_results;
  global $PROHITS_IP;
  global $is_SWATH_file;
  global $table; 
  global $gpm_ip;
  global $tpp_ip; 
  
  $rt = '';
  
  if($record['DataFiles'] and $record['DataFiles'] !='rawFileError'){
     $dat_file_name = substr($record['FileName'], 0, strrpos($record['FileName'], '.')); 
     $DataFiles_arr = explode(";", $record['DataFiles']);

     $arr_count = 0; 
     foreach($DataFiles_arr as $DataFiles_val){
       if(!trim($DataFiles_val)) continue;
       $arr_count++;
     }      
     foreach($DataFiles_arr as $DataFiles_full_name){
       $a_hd = ''; 
       $a_download_hd = '';
       if(!$DataFiles_full_name) continue;
       if($record['SearchEngines'] == "Mascot"){
           $mascot_IP = MASCOT_IP;
           if(defined('MASCOT_IP_OLD') and preg_match("/^\w/", $DataFiles_full_name, $matches)){
              $mascot_IP = MASCOT_IP_OLD;
           }
           $a_hd = "<a title='open Mascot' href=\"javascript: pop_mascot('$mascot_IP','".$DataFiles_full_name."');\">";
           $a_download_hd = " <a title='download dat file' href=\"javascript: download(".$record['WellID'].", ".$record['TaskID'].", '". $record['SearchEngines']."' );\">";
       }else if($record['SearchEngines'] == "GPM"){
           $a_hd = "<a title='open GPM' href='" . GPM_CGI_DIR . "/plist.pl?path=" . $DataFiles_full_name . "' target=_blank>";
           $a_download_hd = " <a title='download xml file' href=\"javascript: download(".$record['WellID'].", ".$record['TaskID'].", '". $record['SearchEngines']."' );\">";
       }else if($record['SearchEngines'] == "SEQUEST"){
           $a_hd = "<a title='open SEQUEST' href=\"http://" . SEQUEST_IP . SEQUEST_CGI_DIR . "/Prohits_SEQUEST_parser.pl?dir=" . $DataFiles_full_name . "\" target=_blank>";
           $a_download_hd = " <a title='download out files' href=\"javascript: download(".$record['WellID'].", ".$record['TaskID'].", '". $record['SearchEngines']."' );\">";
       }else if($record['SearchEngines'] == "COMET"){
           $a_hd = "<a title='open PepTPP' href='" . TPP_CGI_DIR."/PepXMLViewer.cgi?xmlFileName=".$DataFiles_full_name."' target=_blank>";
             
           $a_download_hd = " <a title='download out files' href=\"javascript: download(".$record['WellID'].", ".$record['TaskID'].", '". $record['SearchEngines']."' );\">";
       }else if($record['SearchEngines'] == "MSFragger"){
           $a_hd = "<a title='open PepTPP' href='" . TPP_CGI_DIR."/PepXMLViewer.cgi?xmlFileName=".$DataFiles_full_name."' target=_blank>";
             
           $a_download_hd = " <a  title='download out files' href=\"javascript: download(".$record['WellID'].", ".$record['TaskID'].", '". $record['SearchEngines']."' );\">";
       
       }else if($record['SearchEngines'] == "MSGFPL"){
           $a_hd = "<a  title='open PepTPP' href='" . TPP_CGI_DIR."/PepXMLViewer.cgi?xmlFileName=".$DataFiles_full_name."' target=_blank>";
             
           $a_download_hd = " <a  title='download out files' href=\"javascript: download(".$record['WellID'].", ".$record['TaskID'].", '". $record['SearchEngines']."' );\">";
       }else if($record['SearchEngines'] == "MSPLIT" or $record['SearchEngines'] == "MSPLIT_DDA"){
           $theFile = "./ms_search_MSPLIT_results_view.php?table=$table&WellID=".$record['WellID']."&TaskID=".$record['TaskID']."&path=".$record['DataFiles'];
           $a_hd = "<a  title='open MSPLIT' href=\"javascript:popwin('$theFile',800,800,'new')\">";
           $a_download_hd = " <a  title='download out file' href=\"javascript: download(".$record['WellID'].", ".$record['TaskID'].", '". $record['SearchEngines']."' );\">";
       }
       if($demo_search_results){
         //disable the link for prohistms.com
         $a_hd = "<a  title='open search results' href='$demo_search_results' target=_blank>";
         $a_download_hd = " <a  title='download search result file' href='$demo_search_results' target=_blank>";
         $a_end = "</a>";
       }
       $a_end = "</a>";
       $search_lable = $record['SearchEngines'];
       if($search_lable == 'GPM') $search_lable='XTandem';
       $rt .= "&nbsp;".$a_hd . $search_lable . $a_end;
       $rt .= $a_download_hd."<img src='./images/icon_download.gif' border=0></a>";
       $rt .= "<br>";
     }
     if($record['ProhitsID'] and isset($Pro_ID_names[$record['ProjectID']]) and $arr_count == 1){
       $rt .= add_check_box($record['SavedBy'], $record['WellID'], $record['SearchEngines']);
     } 
  }else{
    //$tmp_sng = $searchEngines[$searchEngine_count];
    $rt = "&nbsp;".(($record['SearchEngines']=='GPM')?'XTandem':$record['SearchEngines']);
    if($perm_modify and $task_arr['Status'] == 'Finished'){
       //$rt .= "<a href=\"javascript: addSearchResults('".$record['WellID']."','".$record['SearchEngines']."');\"><img src=./images/icon_add.gif alt='add ".$record['SearchEngines']." search results' border=0></a>";
    } 
  }
  return $rt;
}

function add_check_box($savedBy, $well_ID, $searchEngine){ 
   
   global $USER,  $perm_delete;
   global $has_parser_permit;
   global $frm_parser_type;
   global $parser_checkbox_arr;
   global $is_SWATH_file;
   global $tableSearchResults;
   global $managerDB;
   global $task_ID;
   $rt = '';
   //return $rt;
   if($searchEngine != "Mascot" and $searchEngine !="GPM" and $searchEngine !="SEQUEST"  and strpos($searchEngine , "MSPLIT") !==0) return $rt;
   if($savedBy){
      $rt = "&nbsp;<a  title='parsed'><img src='images/icon_checked2.gif' border=0></a>";
   }else if($has_parser_permit and ($frm_parser_type == 'search' or $frm_parser_type == 'both')){
      $SQL = "SELECT `SavedBy`,`TaskID` FROM $tableSearchResults WHERE `WellID`='$well_ID' AND `SearchEngines`='$searchEngine' AND `TaskID`!='$task_ID' ORDER BY `SavedBy` DESC";
      $tmp_saveby_arr = $managerDB->fetch($SQL);
      if($tmp_saveby_arr && $tmp_saveby_arr['SavedBy']){
        $rt = "&nbsp; &nbsp;<a  title='Hits have been parsed to the linked sample from task: ".$tmp_saveby_arr['TaskID']." WellID: ".$well_ID."'><img src='images/icon_checkbox_disabled.gif'></a>";
      }else{
        $rt =  "&nbsp;<input type=checkbox value='$well_ID' name='frm_wells_$searchEngine'>";
        if(!isset($parser_checkbox_arr["frm_wells_$searchEngine"])){
          $parser_checkbox_arr["frm_wells_$searchEngine"] = 1;
        }else{
          $parser_checkbox_arr["frm_wells_$searchEngine"]++;
        }
      }
   }
   //echo $searchEngine;
   if($savedBy and $perm_delete and ($savedBy==$USER->ID or $USER->Type=='Admin')){
     //-17 means that the results has been manually parsed by user '17'.
     $div_ID = $well_ID."@@".$searchEngine;
     //$rt .= "&nbsp;<a id='$div_ID'  title='to-be-deleted' href=\"javascript: removehits('".$well_ID."', '".$searchEngine."')\"><img src='./images/icon_delete.gif' border='0' alt='delete hits'></a>";
     $rt .= "&nbsp;<a id='$div_ID' title='to-be-deleted' href=\"javascript: removehits('".$well_ID."', '".$searchEngine."')\"><img src='./images/icon_delete.gif' border='0' alt='delete hits'></a>";   
   }
   return $rt;
}

function print_Engines_File_parts($single_record,$Engines_File_str){
  global $tppTaskID;
  global $myaction;
   
  $tmp_Engines_File_arr =  explode("===",$Engines_File_str);
  $single_record['SearchEngines'] = $tmp_engine = $tmp_Engines_File_arr[0];
  $single_record['DataFiles'] = $tmp_Engines_File_arr[1];
  $single_record['SavedBy'] = $tmp_Engines_File_arr[2];
  $single_record['Date'] = $tmp_Engines_File_arr[3];
?>        
  <td align=left valign=top><?php echo getResultURL($single_record);?></td>
  <td> 
  <?php 
  print_tpp($single_record,$tmp_engine);
  ?>  
  </td>
  <?php 
}

function print_iProphet($result_record){
  global $row_count;
  global $fileNameLables;
  global $tmp_Size;
  global $tmp_title;
  global $tmp_icon;
  global $task_arr;
  
  $rowspan = count($result_record['Engines_File']);
  $WellID = $result_record['WellID'];
  $row_id = $result_record['WellID'].'_'.$row_count++;
  $row_line = "
      <tr id=$row_id bgcolor=\"#ffffff\" onmousedown=\"highlightTR_group('$WellID', '$rowspan', '#CCFFCC', '#ffffff')\">
        <td rowspan='$rowspan' valign=top>".$result_record['WellID']."</td>
        <td rowspan='$rowspan' valign=top>".$fileNameLables[$result_record['WellID']]."</td>
        <td align=right rowspan='$rowspan' valign=top>".$tmp_Size;
  echo $row_line;
  ?>
        <a class='title_head' title='<?php echo $tmp_title;?>' href="javascript: linkProhitsID('<?php echo $result_record['WellID'];?>','','<?php echo $task_arr['ID']?>','<?php echo $tmp_icon?>');" ><img src='./images/<?php echo $tmp_icon;?>' border=0></a>&nbsp; </td>
<?php 
  $Engines_File_arr = $result_record['Engines_File'];
  $Engines_File_str = array_shift($Engines_File_arr);
  print_Engines_File_parts($result_record,$Engines_File_str);
?>
        <td td align=center rowspan='<?php echo $rowspan?>'>
<?php 

        print_tpp($result_record,"iProphet");
?>      
        </td>
      </tr>
<?php 
  foreach($Engines_File_arr as $Engines_File_vale){
    $Engines_File_str = $Engines_File_vale;
    $row_id = $result_record['WellID'].'_'.$row_count++;
    echo "<tr id=$row_id bgcolor=\"#ffffff\" onmousedown=\"highlightTR_group('$WellID', '$rowspan', '#CCFFCC', '#ffffff')\">";
    print_Engines_File_parts($result_record,$Engines_File_str);
    echo "</tr>";
  }
}

function print_tpp($single_record,$tmp_engine){
  global $tppTaskID;
  global $myaction;       
  $tpp_link_str = '';
  $added_checkbox = 0;
   
  
  if($tppTaskID and $myaction != 'new_ttp_task'){
    $tpp_link_str = getTppResultLink($single_record['WellID'], $tmp_engine);
  }
  if($tpp_link_str){
    echo $tpp_link_str;
  }else{
    if(($myaction == 'new_ttp_task' or $myaction == 'repeat')
      and $single_record['DataFiles'] 
      and $single_record['DataFiles'] !='rawFileError'){
      echo "<input type=checkbox name=frm_tppboxes value='".$tmp_engine.$single_record['WellID']."'>\n";
      
      $added_checkbox = 1;
    }
  }
  if(!$added_checkbox){
  	if((strpos($tpp_link_str, 'NoProtXML') or $tmp_engine == 'iProphet') and $myaction == 'repeat'and $single_record['DataFiles'] and $single_record['DataFiles'] !='rawFileError'){
        
        echo "<input type=checkbox name=frm_tppboxes value='".$tmp_engine.$single_record['WellID']."'>\n";
  	}
  }
}

function print_task_records($task_records,$print_style=''){
  global $taskIndex;
  global $default_comet_param_arr;
  global $task_arr;
  global $status_color;
  global $table;
  global $warning_msg;
  
  $is_MSPLIT = false;
  for($i = 0; $i < count($task_records); $i++){
    $frm_is_SWATH_file = 0;
    if(preg_match("/DIAUmpire=(.+)$/", $task_records[$i]['SearchEngines'], $matches)){
      $frm_is_SWATH_file = 1;
      $frm_DIAUmpireSetName = $matches[1];
    }else if(preg_match("/MSPLIT=(.+)$/", $task_records[$i]['SearchEngines'], $matches)){
      $frm_is_SWATH_file = 1;
      $frm_MSPLITSetName = $matches[1];
      $is_MSPLIT = true;
    }
  
    if($print_style == 'single'){
      if($i != $taskIndex) continue;
    }else{
      if($i == $taskIndex) continue;
    }
    $div_str = '';
  ?>
  <tr<?php echo ($taskIndex != $i)?" bgcolor=#ffffff":"";?>>
    <td align=center>
  <?php if($print_style == 'single'){?>
    <?php echo $task_records[$i]['ID'];?>
    </td>    
  <?php }else{?>
    <a href="javascript: selectTask(<?php echo $i;?>);"><?php echo $task_records[$i]['ID'];?></a></td>
    </td>
  <?php }?>
    <td>
  <?php if($frm_is_SWATH_file){?>
    <b><?php echo $task_records[$i]['TaskName'];?></b>
  <?php }else{?>
    <?php echo $task_records[$i]['TaskName'];?>
  <?php }?>
    </td>
    <td>
    <span style="float: left;padding: 0px 0px 0px 5px;border: blue 0px solid;">
    <?php echo str_replace(";","<br>", $task_records[$i]['SearchEngines']);?>
    </span>
    <span style="float: left;padding: 20px 20px 0px 20px;border: blue 0px solid;background-position: center">
    <a class="" href="javascript: pop_Search_Engine_Parameters('<?php echo $task_records[$i]['ID'];?>');"><img border="0" src="images/icon_view.gif" alt="Task detail"></a>&nbsp;</a>
    </span>
    </td>
    <?php $tmp_lable = strpos($task_records[$i]['Schedule'],';')?' hours':'';?>
    <td><?php echo $task_records[$i]['Schedule'].$tmp_lable;?></td>
    <?php 
    $tmp_bgcolor = '';

    if($task_records[$i]['Status'] == 'Running'){
      //$status_color;
      if($task_records[$i]['ID'] == $task_arr['ID']){
        $tmp_bgcolor = $status_color;
        $task_records[$i]['Status'] = $task_arr['Status'];
      }else{
        if(task_is_running($table, $task_records[$i]['ID'])){
           $tmp_bgcolor = "lightgreen";
        }else{
           $tmp_bgcolor = "yellow";
           $task_records[$i]['Status'] = 'Error';
        }
      }
    }
    $status_lable = $task_records[$i]['Status'];
    if($task_records[$i]['Status'] == 'Waiting'){
      $status_lable = 'In task queue';
    }
    $Status_tmp = $task_records[$i]['Status'];
    ?>
    <td
    <?php echo ($Status_tmp == 'Running' or $Status_tmp == 'Error')?" bgcolor=$tmp_bgcolor":"";?>><?php echo $status_lable;?>
    <?php echo (($i == $taskIndex && $Status_tmp != 'Finished') or $is_MSPLIT)?"<br>$warning_msg":"";?>    
    </td>
  </tr>
<?php 
  }
} 
include("./ms_search_footer.php");


