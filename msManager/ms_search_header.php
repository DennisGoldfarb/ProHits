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

$tableName = '';
$thePage = '';
$table = '';
$tableSearchResults = '';
$tableSearchTasks = '';
$menu_color = '#7e7e7e';
$where_project = '';
$runningTaskID = '';
$runningTaskPlateIDs = array();
$runningTaskIDs = array();

include("./ms_permission.inc.php");
require("./common_functions.inc.php");
 
if(!$table) fatalError("There is no table name passed", __LINE__);

//page counter start ----------------------------------------------------------------
if($USER->Type == 'MSTech' or $USER->Type == 'Admin'){
  $where_project = 1;
}else{
  $where_project = "T.ProjectID in($pro_access_ID_str)";
}
if(!$table) fatalError("There is no table name passed", __LINE__);
$logo = $table;
if(!is_file("./images/msLogo/" . $table . "_logo.gif")) $logo = "default";
if($tableSearchTasks){
  $SQL = "SELECT ID, PlateID FROM $tableSearchTasks where Status='Running' order by ID desc";
  $running_task_records = $managerDB->fetchAll($SQL);
  if($running_task_records){
    foreach($running_task_records as $tmp_arr) {
      array_push($runningTaskIDs, $tmp_arr['ID']);
      array_push($runningTaskPlateIDs, $tmp_arr['PlateID']);
    }
  }
}

include("./ms_header.php");

?>
<script language='javascript'>
function browsTask(theForm, goTo){
  if(goTo == 'previous'){
     theForm.myaction.value = 'previous';
  }else if(goTo == 'next'){
     theForm.myaction.value = 'next';
  }else if(goTo == 'new'){
    <?php if($runningTaskIDs and $USER->Type != 'Admin'){
      echo "if(!confirm(\"There is a running task. Only one running task is allowed.\\nAre you sure that you want to stop the running task and create a new task\")){
        return;
      }\n";
    }
    ?>
    document.location = 'ms_search_task.php?table=<?php echo $table;?>&myaction=new&frm_PlateID=&theTaskID=';
    return;
  }
  theForm.submit();
}
</script>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
  <td bgcolor="<?php echo $menu_color;?>" valign="top" align="" width="175">
    <br>
    <table width=96% bgcolor="#ffffff" align=center><tr><td align=center>
    <img src='./images/msLogo/<?php echo $logo."_logo.gif";?>'>
    </td></tr></table>
   <br><br>
   &nbsp;<a href=./ms_search_task_list.php?table=<?php echo $table;?> class=left_menu> <?php echo $table;?> Search Tasks</a> 
   <a href="javascript: popwin('../doc/management_help.html#Task_view',782,600,'help');"><img src=./images/icon_help.gif border=0></a>
   <hr width="100%" size="1" noshade color=white>
   <br>
   <?php  if($perm_insert){?>
        &nbsp;
        <a href="ms_search_task_new.php?table=<?php echo $table;?>&myaction=new&frm_PlateID=&theTaskID=" class=left_menu><?php echo $table;?> New Task</a>
        <a href="javascript: popwin('../doc/management_help.html#Manually_initiate',780,600, 'help');"><img src=./images/icon_help.gif border=0></a>
          
        <hr width="100%" size="1" noshade color=white>
  <?php }?>
   <br>
  &nbsp;<a href=./ms_search_taskfinder.php?table=<?php echo $table;?> class=left_menu><?php echo $table;?> Task Finder</a>&nbsp; &nbsp;
    
   <hr width="100%" size="1" noshade color=white>
   <br><br><br>
   
   
    &nbsp;<a href="javascript:popwin('http://<?php echo $storage_ip . dirname($_SERVER['PHP_SELF']);?>/auto_run_shell.php', 700,300 )"; class=left_menu>Running Task Status</a> &nbsp; &nbsp;
    
   <hr width="100%" size="1" noshade color=white>
   <br>
   &nbsp;<a href="javascript:popwin('http://<?php echo $storage_ip . str_replace('msManager','',dirname($_SERVER['PHP_SELF']));?>logs/log_view.php?log_file=search.log', 750,500 )"; class=left_menu>View Search Log</a> &nbsp; &nbsp;
    
   <hr width="100%" size="1" noshade color=white>
   <br> 
   &nbsp;<a href="javascript:popwin('http://<?php echo $storage_ip . str_replace('msManager','',dirname($_SERVER['PHP_SELF']));?>logs/log_view.php?log_file=./TPP/tpps.log', 750,500 )"; class=left_menu>View TPP Log</a> &nbsp; &nbsp;
   
    
   <hr width="100%" size="1" noshade color=white>
   <br>
   &nbsp;<a href="javascript:popwin('../logs/log_view.php?log_file=./parser.log',750,500 )"; class=left_menu>View Parser Log</a> &nbsp; &nbsp;
   
   <hr width="100%" size="1" noshade color=white>
   
  </td>
  <td width="923" align=center valign=top >
