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

$theTaskID = 0;
$total_raw_file = 0;
$file_option_output = '';
$frm_PlateID = '';
$frm_setBy = '';
$myaction = '';
$theLastTaskID = '';
$theLastTaskStatus = '';
$theFirstTaskID = '';
$runningTaskID = '';

$frm_runTPP = '';
$frm_tppSetName='';
$frm_tppTaskName='';
$tppTaskID = 0;
$frm_tppStatus = '';
$frm_is_SWATH_file = '';
$frm_DIAUmpireSetName = '';
$frm_MSPLITSetName = '';

$warning_msg = '';
$file_names = array();
 
include("./ms_search_header.php");
include("./tppTask/tpp_task_shell_fun.inc.php");
require("./is_dir_file.inc.php");
 
/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$para_file_dir = "../TMP/Task_parameter_files";
if(!_is_dir($para_file_dir)){
  _mkdir_path($para_file_dir);
}
$para_file_name = $para_file_dir."/$theTaskID.txt";

$searchAll_parm_dir = "./autoSearch/";
$default_param_arr = get_comet_default_param($searchAll_parm_dir);

if($myaction == 'stoptask' and $theTaskID and $perm_delete){
  $SQL = "select ID, RunTPP, ProcessID from $tableSearchTasks  where ID='$theTaskID' and Status='Running'";
  $running_record = $managerDB->fetch($SQL);
  if($running_record){
    $SQL = "update $tableSearchTasks set Status='Stopped by ". $USER->Fname . " " . $USER->Lname . "' where ID='$theTaskID' and Status='Running'";
    if($managerDB->execute($SQL)){
      if($running_record['RunTPP']){
        updateTppTaskStatus($tableTppTasks, $running_record['RunTPP'],  $newStatus = 'Stopped');
      }
      $file = "http://" .$storage_ip . dirname($_SERVER['PHP_SELF']) . "/autoSearch/auto_search_table_shell.php?frm_theTaskID=".$theTaskID."&tableName=".$table."&kill=". $running_record['ProcessID']."&SID=".session_id();
      $handle = fopen($file, "r");
    }
  }
}
 
$SQL = "SELECT S.ID FROM $tableSearchTasks S, $table T where S.PlateID = T.ID and " . $where_project . " order by ID limit 1";
$fist_task_record = $managerDB->fetch($SQL);
if($fist_task_record){
  $theFirstTaskID = $fist_task_record['ID'];
}
$SQL = "SELECT S.ID, S.Status FROM $tableSearchTasks S,$table T where S.PlateID = T.ID and " . $where_project . " order by ID desc limit 1";
$last_task_record = $managerDB->fetch($SQL);
if($last_task_record){
  $theLastTaskID = $last_task_record['ID'];
  $theLastTaskStatus = $last_task_record['Status'];
}
 
if($theTaskID == 'last'){ 
 $theTaskID = $theLastTaskID;
}

if($theTaskID or $myaction == 'previous' or $myaction == 'next'){
  if($theTaskID){
    if($myaction == 'previous'){ 
      $where = "S.ID < '$theTaskID'  and ". $where_project . " order by S.ID desc limit 1";;
    }else if($myaction == 'next'){
      $where = "S.ID > '$theTaskID'  and ". $where_project . " order by S.ID limit 1";
    }else{
      $where = "S.ID='$theTaskID'  and ". $where_project;
    }
  }else{
    fatalError("Missing task ID.", __LINE__);
  }
  $SQL = "SELECT 
          S.ID, 
          PlateID, 
          DataFileFormat, 
          SearchEngines, 
          Parameters,
          DIAUmpire_parameters, 
          TaskName, 
          LCQfilter, 
          Schedule, 
          StartTime, 
          AutoAddFile, 
          RunTPP, 
          Status, 
          ProcessID, 
          UserID
          FROM $tableSearchTasks S, $table T where S.PlateID = T.ID and $where";
  $task_record = $managerDB->fetch($SQL);

  if($task_record){
    $in_folderIDs = $task_record['PlateID'];
    $SQL = "select ID, FileName, ProhitsID, ProjectID, Date from $table T where " . $where_project . "  and ID in($in_folderIDs)";
  	$foldersRD = $managerDB->fetchAll($SQL);
    if(!$foldersRD) fatalError("no permission for the plate", __LINE__);
    
    $SQL = "SELECT WellID, SearchEngines FROM $tableSearchResults where TaskID='$theTaskID'";
    $file_records = $managerDB->fetchAll($SQL);
    $file_id_str = '';
    for($i = 0; $i < count($file_records); $i++){
      if($file_id_str) $file_id_str .= ",";
      $file_id_str .= $file_records[$i]['WellID'];
      $file_sarchEngines[$file_records[$i]['WellID']] = $file_records[$i]['SearchEngines'];
    }
   
    if($file_id_str){
      $SQL = "SELECT FileName, FolderID, ID from $table where ID in($file_id_str) order by FolderID, FileName";
      $file_names = $managerDB->fetchAll($SQL);
      $total_raw_file = count($file_names);
    }
    
		$theTaskID = $task_record['ID'];
		$frm_TaskName = $task_record['TaskName']; 
  	$frm_PlateIDs = $task_record['PlateID']; 
  	$frm_startTime = $task_record['StartTime']; 
  	$frm_status = $task_record['Status'];
		$frm_lcq_par_str = $task_record['LCQfilter'];
    
    if(preg_match("/DIAUmpire=(.+)$/", $task_record['SearchEngines'], $matches)){
      $frm_is_SWATH_file = 1;
      $frm_DIAUmpireSetName = $matches[1];
       
    }else if(preg_match("/MSPLIT=(.+)$/", $task_record['SearchEngines'], $matches)){
      $frm_is_SWATH_file = 1;
      $frm_MSPLITSetName = $matches[1];
       
      $warning_msg = " <a href=\"javascript:  MSPLIT_status('".$task_record['ID']."');\"><b>[MSPLIT STATUS]</b></a>";
    }
    if($frm_status == 'Running'){
      if($frm_is_SWATH_file and  $frm_MSPLITSetName){
      
      }else if(task_is_running($table,  $theTaskID)){
        $bgcolor = "lightgreen";
      }else{
        
        $bgcolor = "yellow";
        $frm_status  = 'Error';
        $warning_msg = " <font color=red><b> (The task was set to run. But it is not running. Stop the task or run it again.)</b></font>";
      }
    }
    
		if(strstr($frm_lcq_par_str, '=')){
			$para_arr = explode(";", $frm_lcq_par_str);
			foreach($para_arr as $pare){
			  $tmp_arr = explode('=',$pare); 
			  if(count($tmp_arr)>1) $$tmp_arr[0] = $tmp_arr[1];
			} 
			$frm_lcq_par_str = "-B$LCQ_MIN-T$LCQ_MAX -M$LCQ_GROUP - S$LCQ_INTER - G$LCQ_SCAN -I$LCQ_PEAKS -C$LCQ_CHARGE";
		}
		if($task_record['UserID']){
			$SQL = "select Fname, Lname from User where ID='".$task_record['UserID']."'";
			$user_rd = $PROHITSDB->fetch($SQL);
			if($user_rd)$frm_setBy = $user_rd['Fname'] . " " . $user_rd['Lname'];
		}
  }else{
    fatalError("The task doesn't exist.", __LINE__);
  }
}
if(!$theTaskID) header("location: ./ms_search_task.php?table=$table");
if($task_record['RunTPP']){
  $SQL = "SELECT `ID`,`SearchTaskID`,`Parameters`,`ParamSetName`,`TaskName`,`Status` FROM `$tableTppTasks` WHERE ID='".$task_record['RunTPP']."'";
  $tppTask_record = $managerDB->fetch($SQL);
  if($tppTask_record){
    $frm_runTPP = 'yes';
    $frm_tppSetName = $tppTask_record['ParamSetName'];
    $frm_tppTaskName = $tppTask_record['TaskName'];
    $frm_tppStatus = $tppTask_record['Status'];
    $tppTaskID = $task_record['RunTPP'];
    $tppParameters = $task_record['Parameters'];
  }
}

if($task_record and $myaction == 'runtask'){
  if($task_record['UserID'] == $USER->ID or $USER->Type == 'Admin'){
    send_task_to_shell($table, $perm_insert, $theTaskID );
    exit;
  }
}
 
?>
<style type="text/css">
.c { background-color:#e2e2e2; }
.td_st1{
  display: block;
  border: red solid 0px;
  padding: 0px 5px 0px 5px;
  margin: 0px 0px 0px 0px;
}
</style>
<script language="javascript">
function modifyTask(theForm){ 
  submitForm(theForm, 'modify', 'ms_search_task_new.php');
}
function newTask(theForm){
  theForm.theTaskID.value = '';
  submitForm(theForm, 'new', 'ms_search_task_new.php');
}
function stopTask(theForm){
  if(confirm("Are you sure you want to stop the running task?")){
    submitForm(theForm, 'stoptask', '');
  }
}
function runTask(theForm){
  if(confirm("Are you sure that you want to run the task again?")){
    submitForm(theForm, 'runtask', '');
  }
}
function MSPLIT_status(task_ID){
  file = './MSPLIT_status.php?tableName=' + '<?php echo $table;?>' + '&taskID=' + task_ID;
  popwin(file,700,500);
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

</script>
    <form action="<?php echo $PHP_SELF;?>" method="post" name="form_task" id="form_task">
    <input type="hidden" name="table" value="<?php echo $table;?>">
    <input type="hidden" name="myaction" value="refresh">
    <input type="hidden" name="theTaskID" value="<?php echo $theTaskID;?>">
    <input type="hidden" name="tppTaskID" value="<?php echo $tppTaskID;?>">
    <table cellspacing="5" cellpadding="1" border="0" width=95%>
    <tr><td align=center colspan=2><br>
     <font face="Arial" size="+1" color="<?php echo $menu_color;?>"><b><?php echo $table;?> Search Task</b></font>
     <?php 
     if(isset($msg)) echo "<br><b>$msg</b><br>";
     ?>
     <hr width="100%" size="1" noshade>
    </td>
    </tr>
    <tr>
        <td valign=top colspan=2>
					<table cellspacing="1" cellpadding="0" border="0" width=100%>
						<?php if($theTaskID){?>
            <tr><td><b>Task ID</b>:</td>
            <td colspan=2>
            <a title='to results detail' href="ms_search_results_detail.php?table=<?php echo $table;?>&frm_PlateID=<?php echo $frm_PlateIDs?>&iniTaskID=<?php echo $theTaskID;?>">
            <font color="#FF0000"><?php echo $theTaskID;?></font>
            </a>
            </td>
            </tr>
        		<?php }?>
						<tr>
							<td><b>Task Name</b>:</td>
							<td colspan=2><?php echo $frm_TaskName;?>
							</td>
						</tr>
						 <tr>
							<td bgcolor=#d0d0d0><b>Folder ID</b></td>
							<td bgcolor=#d0d0d0><b>Folder Name</b></td>
							<td bgcolor=#d0d0d0><b>Project</b></td>
						</tr>
						<?php 
						$dis_projectArr = array();
						if($foldersRD){
					    for($i = 0; $i < count($foldersRD); $i++){
								echo "<tr bgcolor=#deedf3>\n";
								echo "<td>".$foldersRD[$i]['ID'] . "</td>\n"; 
						    echo "<td>".$foldersRD[$i]['FileName'] . "</td>\n";
								$tmp_pro = ($foldersRD[$i]['ProjectID'])?$pro_access_ID_Names[$foldersRD[$i]['ProjectID']]:"&nbsp;";
								echo "<td>".$tmp_pro."</td>\n";  
								echo "</tr>\n";
						  }
						}
            $styleColor = 'white';
            if($frm_status == 'Running'){
              $styleColor = 'lightgreen';
            }else if($frm_status == 'Error'){
              $styleColor = 'yellow';
            }
            ?>
					</table>
        <?php echo ($frm_status)?"<br><b>Status</b>: <font style='background-color: $styleColor;'>$frm_status</font> $warning_msg":"";?><br>
				<?php echo ($frm_startTime)?"<b>Start Time: </b><font color=red> $frm_startTime </font>":"";?><br>
				<?php echo ($frm_setBy)?"<b>Set By: </b><font color=red> $frm_setBy </font>":"";?>
        </td>
    </tr>
    <tr>
        <td valign=top bgcolor=#cccccc width=55%>
        <div class='td_st1'>
        <table cellspacing="1" cellpadding="0" border="0" width=100%>
          <tr>
              <td colspan="4">
              <div style="float: left; padding: 0px 0px 0px 5px;color: #FFFFFF; border: blue 0px solid">
                <b><font size='3'>Search Engine Parameters</font></b>
              </div>
              <div style="float: right;padding: 0px 5px 0px 0px; border: #708090 0px solid;font-family: Georgia, Serif;">
              [<a id="all_search_Engine_a" href="javascript: toggle_all('all_search_Engine_a')" title='all search engine detail'>+</a>]
              </div><br>
              <hr width="100%" size="1" noshade color=#888888 height=1 align=left>
              </td>
          </tr> 
          <?php 
          print_task_parameters($task_record);
          if($frm_is_SWATH_file){?>
          <tr>
              <td colspan="4" bgcolor=black><b><font color="#ffffff">&nbsp; Is SWATH File</font></b></td>
          </tr>
          <tr>
              <td colspan="4">
              <?php echo str_replace(";","<br>", $task_record['SearchEngines']);?>
							</td>
          </tr>
          <?php }
          
          if($frm_runTPP){?>
          <tr>
              <td colspan="4"><br><b><font color="#FFFFFF">Run TPP</font></b><br>
              <hr width="100%" size="1" noshade color=#888888 align=left></td>
          </tr>
          <tr>
              <td colspan="4">
              <b>TPP Name: </b> <?php echo $frm_tppSetName;?><br>
              <b>TPP Parameter Set: </b><?php echo $frm_tppSetName;?>&nbsp;&nbsp;<a title="<?php echo pop_tppTask_parameters_div($tppTask_record)?>"><img border="0" src="images/icon_view.gif" alt="Task detail"></a><br>
              <b>TPP Status: </b> <?php echo $frm_tppStatus;?>
							</td>
          </tr>
          <?php }?>
					<tr>
              <td colspan="4"><br><b><font color="#FFFFFF">Proteowizard Parameters</font></b><br>
              <hr width="100%" size="1" noshade color=#888888 align=left></td>
          </tr>
					<tr>
              <td colspan="4">
							<font color="#FFFFFF">The parameters will apply when converting RAW file to mgf/mzXML file.</font><br>
							<?php echo $frm_lcq_par_str;?>
							 </td>
          </tr>
          <tr>
              <td colspan="4"><br><b><font color="#FFFFFF">Search Schedule</font></b><br>
              <hr width="100%" size="1" noshade color=#888888 align=left></td>
          </tr>
          <tr>
              <td valign=top><b>Start</b></td>
              <?php 
              $tmp_lable = strpos($task_record['Schedule'],';')?' hours':'';
              ?>
              <td colspan=3><font color='#FF0000'><?php echo ($task_record['Schedule']=='Waiting')?" in task queue":$task_record['Schedule'].$tmp_lable;?></font><br>
							<font color=white>Only <?php echo $frm_setBy;?> and Admin can modify the task.</font>
							</td>
               
          </tr>
          </table>
          </div>
        </td>
    </tr>
    <tr>
      <td align=top bgcolor=#cccccc valign=top>
      <div class='td_st1'>
        <table cellspacing="1" cellpadding="0" border="0" width=100%>
          <tr>
            <td colspan="4"><b><font color="#FFFFFF">Data File List</font> (total: <?php echo $total_raw_file?>)</b><br>
            <hr width="100%" size="1" noshade color=#888888 height=1 align=left></td>
          </tr> 
          <tr>
            <td>
              <b>Automatically add </b><font color="#FF0000"><?php echo $task_record['DataFileFormat'];?></font>
              <b>files from the Folder:</b> <font color="#FF0000"><?php echo ($task_record['AutoAddFile'])?$task_record['AutoAddFile']:"No";?></font>
              <?php 
              if($frm_is_SWATH_file){
                $all_file_added = 'No';
                if(strpos($task_record['DIAUmpire_parameters'],"allSwathFileAdded:Yes") !== false){ 
                  $all_file_added = 'Yes';
                }
                echo "<br><b>All files are added. Run all steps of the process pipeline</b>: <font color=red>$all_file_added</font>";
              }
              ?>
            </td>
          </tr>
          <tr>
            <td colspan="4">
            <font color="#FFFFFF">[Folder ID] / File Name </font><br>
            </td>
          </tr>
          <?php foreach($file_names as $file_vals){
              $is_DDA = '';
              if($file_sarchEngines[$file_vals['ID']] == 'MSPLIT_DDA'){
                $is_DDA = '&nbsp; &nbsp; &nbsp;<font color=red>is DDA</font>';
              }
          ?>
          <tr>
            <td>[<?php echo $file_vals['FolderID']?>] / <?php echo $file_vals['FileName'].$is_DDA; ?></td>
          </tr>
          <?php }?> 
      </div>
      </td>
    </tr>
    <tr>
        <td colspan=2 align=center><br>
        <?php if($theTaskID > $theFirstTaskID) {?>
        <input type="button" name="frm_lastTask" value="Previous Task" onClick="browsTask(this.form, 'previous')">
        <?php }
        if($theTaskID < $theLastTaskID) {?>
        <input type="button" name="frm_lastTask" value="Next Task" onClick="browsTask(this.form, 'next')">
        <?php }
        if($perm_modify and ($task_record['UserID'] == $USER->ID or $USER->Type == 'Admin')){?>
           <input type="button" name="frm_lastTask" value="Modify Task" onClick="modifyTask(this.form)">
           <?php if($task_record['Status'] == 'Running'){?>
              <input type="button" name="frm_stop" value="Stop Task" onClick="stopTask(this.form)">
           <?php }
             
        }?> 
        </td>
    </tr>    
    </table>
    </form>
<?php 
include("./ms_search_footer.php");
?>
