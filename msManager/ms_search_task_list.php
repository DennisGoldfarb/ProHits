
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
$theaction = "";
$start_point = 0;
$order_by = ''; 
$runningTaskID = '';
$runningTaskPlateID = '';
$searched_id_str = '';
$title_lable = '';

$table = '';
$theaction = '';
$search_Whate = '';
$search_ID = '';
$search_Name = '';
$search_Project = '';

$frm_Projects = 'all';
$frm_Users = '';
$frm_list_by = 'task';
$is_SWATH_file = '';

$RESULTS_PER_PAGE = 50;
define ("MAX_PAGES", 20); //this is max page link to display 
include("./ms_search_header.php");
if(!$frm_Users) $frm_Users = $_SESSION["USER"]->ID;

/*echo "<pre>";
print_r($request_arr);
//print_r($_SESSION);
echo "</pre>";*/

if(!$order_by) $order_by = "ID desc";
if(!$start_point) $start_point = 0;

$pro_access_ID_Names = array();
$user_id_name_arr = array();
$specialTaskArr = array();
$pro_access_ID_str = '';

initial_arrays($frm_Projects);//create array $pro_access_ID_Names and $pro_access_ID_Names for project or task selections ----
if(!array_key_exists($frm_Users, $user_id_name_arr)) $frm_Users = "all";

$PAGE_COUNTER = new PageCounter();
$query_string = "table=$table&theaction=$theaction&search_Whate=$search_Whate&search_ID=$search_ID&search_Name=$search_Name&search_Project=$search_Project&frm_Projects=$frm_Projects&frm_Users=$frm_Users&frm_list_by=$frm_list_by";
$query_string .= "&order_by=".$order_by;

$runningRecord = array();
if($frm_list_by == 'task'){
  $runningRecord = array();
  $SQL = create_sql_str_for_task($runningRecord);
  if($runningRecord) $RESULTS_PER_PAGE = $RESULTS_PER_PAGE- count($runningRecord);
}else{
  $SQL = create_sql_str_for_folder();
}
$total_records = $managerDB->get_total($SQL);
$page_output = $PAGE_COUNTER->page_links($start_point, $total_records, $RESULTS_PER_PAGE, MAX_PAGES, str_replace(' ','%20',$query_string));

if($frm_list_by == 'task'){
  $SQL .= (strstr($order_by, 'ProhitsID'))?" order by T.$order_by":" order by S.$order_by";
}else{
  $SQL .= " order by $order_by";
}  
if($theaction !='search'){
  $SQL .= " Limit $start_point, ". $RESULTS_PER_PAGE;
}
$plate_records = $managerDB->fetchAll($SQL);

?>
<script language="javascript">
function sortList(order_by){
  var theForm = document.list_form;
  theForm.order_by.value = order_by;
  theForm.submit();
}

function checkform(){
  var theForm = document.list_form;
  theForm.order_by.value = "";
  theForm.submit();
} 
</script>
<form name="list_form" method=post action="<?php echo $PHP_SELF;?>">  
  <input type=hidden name=theaction value="<?php echo $theaction?>">
  <input type=hidden name=table value='<?php echo $table;?>'>
  <input type=hidden name=order_by value='<?php echo $order_by;?>'>
  <input type=hidden name=search_Whate value='<?php echo $search_Whate;?>'>
  <input type=hidden name=search_ID value='<?php echo $search_ID;?>'>
  <input type=hidden name=search_Name value='<?php echo $search_Name;?>'>
  <input type=hidden name=search_Project value='<?php echo $search_Project;?>'>
  <input type=hidden name=searched_id_str value='<?php echo $searched_id_str;?>'>
  <input type=hidden name=title_lable value='<?php echo $title_lable;?>'>

  <table border=0 width=97% cellspacing=5 cellpadding=1>
    <tr>
      <td align=center><br>
        <font face="Arial" size="+1" color="<?php echo $menu_color;?>"><b><?php echo $table . " Search Tasks ";?></font><br><?php echo ($title_lable)?urldecode($title_lable):""?></b>
        <hr width="100%" size="1" noshade>
      </td>
    </tr>
    <?php if($theaction != "search"){?>
    <tr>
      <td align=center>     
      <DIV STYLE="border: #b1b1b1 solid 1px; background-color:#e1e1e1;">
      <table border=0 width=100%>
        <tr>
        <td align=left><b>List by:</b></td>
        <td align=left colspan="2">
          <b>Task</b>
          <input type="radio" name="frm_list_by" value="task" <?php echo ($frm_list_by=='task')?'checked':''?> onclick="checkform()">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
          <b>Folder</b>
          <input type="radio" name="frm_list_by" value="folder" <?php echo ($frm_list_by=='folder')?'checked':''?> onclick="checkform()">
        </td>
        </tr>      
        <tr>
        <td width=100><b>Project:</b></td>
        <td align=left colspan="2">
          <select name="frm_Projects" id="frm_Projects" size=1 onChange="checkform()"">
            <option value='all'>All&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;               
      <?php 
            foreach($pro_access_ID_Names as $key => $val){
              echo  "<option  value='".$key."'";
              echo ($key==$frm_Projects)?" selected":"";   
              echo ">"."(".$key.") ".$val."\n";
            } 
      ?>
          </select>
        </td>
        </tr>
        <tr>
        <td width=100><b>User:</b></td>
        <td align=left>
          <select name="frm_Users" id="frm_Users" size=1 onChange="checkform()">
            <option value='all'>All&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;               
      <?php 
            foreach($user_id_name_arr as $key => $val){
              echo  "<option  value='".$key."'";
              echo ($key==$frm_Users)?" selected":"";   
              echo ">"."(".$key.") ".$val."\n";
            } 
      ?>
          </select>
        </td>
        <td align=center width=150>
        </td>
        </tr>  
      </table>
      </DIV>
      </td>
    </tr>
    <?php }?>    
    <?php if($theaction != 'search'){?>
    <!--tr>
      <td align=right><a href=./ms_search_results.php?table=<?php echo $table;?> class=button>[List Search Results by Folder]</a></td>
    </tr-->
    <tr>
      <td align=right><?php echo $page_output;?></td>
    </tr>
    <?php }?>
    <tr><td bgcolor=#d2d2d2>
      <table border=0 width=100% cellspacing=1 cellpadding=2>
        <tr bgcolor="#b7b7b7">
      <?php if($frm_list_by == 'task'){?>        
          <td width=8%  align=center>
          <a href="javascript: sortList('<?php echo ($order_by == "ID")? 'ID%20desc':'ID';?>');"><b><font color="#000000">Task ID</font></b></a>
            <?php 
            if($order_by == "ID") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "ID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </td>
          <td width=8% align=center>
          <a href="javascript: sortList('<?php echo ($order_by == "ProjectID")? 'ProjectID%20desc':'ProjectID';?>');"><b><font color="#000000">Project</font></b></a>
            <?php 
            if($order_by == "ProjectID") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "ProjectID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </td>
          <td width=30% align=center>
          <a href="javascript: sortList('<?php echo ($order_by == "TaskName")? 'TaskName%20desc':'TaskName';?>');"><b><font color="#000000">Task Name</font></b></a>
            <?php 
            if($order_by == "TaskName") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "TaskName desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </td>
          
          <td width=10% align=center>
          <a href="javascript: sortList('<?php echo ($order_by == "Status")? 'Status%20desc':'Status';?>');"><b><font color="#000000">Status</font></b></a>
           <?php 
            if($order_by == "Status") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "Status desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </td>
          <td width=6% align=center><b><font color="#000000">Task Detail</font></b>
          </td>
          <td width=5% align=center><b>Result Detail</b></td>
      <?php }else{?>
          <td width=8%  align=center>
          <a href="javascript: sortList('<?php echo ($order_by == "ID")? 'ID%20desc':'ID';?>');"><b><font color="#000000">Folder ID</font></b></a>
            <?php 
            if($order_by == "ID") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "ID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </td>
          <td width=8% align=center>
          <a href="javascript: sortList('<?php echo ($order_by == "ProjectID")? 'ProjectID%20desc':'ProjectID';?>');"><b><font color="#000000">Project</font></b></a>
            <?php 
            if($order_by == "ProjectID") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "ProjectID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </td>
          <td width=30% align=center>
          <a href="javascript: sortList('<?php echo ($order_by == "FileName")? 'FileName%20desc':'FileName';?>');"><b><font color="#000000">Folder Name</font></b></a>
            <?php 
            if($order_by == "FileName") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "FileName desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </td>
          
          <td width=10% align=center>
          <a href="javascript: sortList('<?php echo ($order_by == "Date")? 'Date%20desc':'Date';?>');"><b><font color="#000000">Date</font></b></a>
           <?php 
            if($order_by == "Date") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "Date desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </td>
          <td width=6% align=center><b><font color="#000000">Task ID</font></b></td>
          <td width=5% align=center><b>Detail</b></td>
    
      <?php }?>      
        </tr>
      <?php if($frm_list_by == 'task'){
          if($runningRecord) array_splice($plate_records, 0, 0, $runningRecord);
          
          for($i=0; $i < count($plate_records); $i++){
           $is_SWATH_file = 0;
           $frm_is_SWATH_file = 0; 
           $warning_msg = '';
           $status_lable = '';
           if(preg_match("/DIAUmpire=(.+)$/", $plate_records[$i]['SearchEngines'], $matches)){
              $frm_is_SWATH_file = 1;
            }else if(preg_match("/MSPLIT=(.+)$/", $plate_records[$i]['SearchEngines'], $matches)){
              $frm_is_SWATH_file = 1;
            }
            //echo "\$frm_is_SWATH_file = $frm_is_SWATH_file<br>";
            if(in_array($plate_records[$i]['ID'], $runningTaskIDs)){
              //if(preg_match("/MSPLIT=(.+)$/", $plate_records[$i]['SearchEngines'], $matches)){
              //    //$is_SWATH_file = 1;
              ////    $bgcolor = "lightgreen";
              //    $status_lable = $plate_records[$i]['Status'];
             // }else 
              if(task_is_running($table,  $plate_records[$i]['ID'])){
                $bgcolor = "lightgreen";
              }else{
                $bgcolor = "yellow";
                $status_lable = 'Error';
                $warning_msg = "<br><b><font color=red>The task was set to run. But it is not running. Click task detail to stop it or run it again.</font></b>";
              }
            }else if($plate_records[$i]['Status'] == 'Waiting'){
              $bgcolor = "#c5d7f5";
              $status_lable = 'In task queue';
            }else{
              $bgcolor = "#ffffff";
              $status_lable = $plate_records[$i]['Status'];
            }
            echo  "<tr>";
            echo "<td bgcolor=$bgcolor>".$plate_records[$i]['ID']."</td>
                  <td bgcolor=$bgcolor>".$plate_records[$i]['ProjectID']."</td>
                  <td bgcolor=$bgcolor>".
                  (($frm_is_SWATH_file)?'<b>'.$plate_records[$i]['TaskName'].'<b>':$plate_records[$i]['TaskName'])
                  .$warning_msg."</td>
                  <td bgcolor=$bgcolor>".$status_lable."</td>";
           ?>
            <td bgcolor=<?php echo $bgcolor;?> align=center>       
            <a href="ms_search_task_view.php?table=<?php echo $table;?>&theTaskID=<?php echo $plate_records[$i]['ID'];?>">
            <img border="0" src="images/icon_view.gif" alt="Task detail"></a>&nbsp;
            </td> 
            <td bgcolor=<?php echo $bgcolor;?> align=center>       
            <a href="ms_search_results_detail.php?table=<?php echo $table;?>&frm_PlateID=<?php echo $plate_records[$i]['PlateID'];?>&iniTaskID=<?php echo $plate_records[$i]['ID'];?>">
            <img src=./images/icon_checked.gif border=0 alt=Result detail></a>&nbsp;
            </td>
            </tr>               
          <?php 
          }
        }else{      
          $tmpTaskIDarr = array();
          for($i=0; $i < count($plate_records); $i++){
            $SQL = "select ID from $tableSearchTasks where PlateID='".$plate_records[$i]['ID']."'";
            if($pro_access_ID_str){
              if($frm_Projects != "all"){
                $SQL .=  " AND ProjectID='$frm_Projects'";
              }elseif($frm_Users != "all"){
                $SQL .=  " AND UserID='$frm_Users' AND ProjectID IN($pro_access_ID_str)";
              }else{
                $SQL .=  " AND ProjectID IN($pro_access_ID_str)";
              }
            }else{
              $SQL .=  " AND 0";
            }
            $managerDB->check_connection();
            $tmp_rds = $managerDB->fetchAll($SQL);
            if(count($tmp_rds)){
              $tmp_task_str = '';
              foreach ($tmp_rds as $key => $value){
                $tmp_task_str .= ($tmp_task_str)?"<br>".$value['ID']:$value['ID'];
              }
              $bgcolor=(in_array($plate_records[$i]['ID'], $runningTaskPlateIDs))?"yellow":"#ffffff";
              echo  "<tr>";
              echo "<td bgcolor=$bgcolor>".$plate_records[$i]['ID']."</td>
                    <td bgcolor=$bgcolor>".$plate_records[$i]['ProjectID']."</td>
                    <td bgcolor=$bgcolor>".$plate_records[$i]['FileName']."</td>
                    <td bgcolor=$bgcolor>".$plate_records[$i]['Date']."</td>
                    <td bgcolor=$bgcolor>".$tmp_task_str."</td>
                    <td bgcolor=$bgcolor><a href=./ms_search_results_detail.php?table=$table&frm_PlateID=" . $plate_records[$i]['ID']. "><img src=./images/icon_checked.gif border=0 alt=detail></a></td>
                   ";
              echo "</tr>\n";
            }
            if(count($specialTaskArr)){
              print_multiple_folders($plate_records[$i]);
            }  
          }
        }  
         ?>
       </table>
       </form>
    </td>
    </tr>
   </table> 
<?php 
include("./ms_search_footer.php");
function create_sql_str_for_task(&$runningRecord){
  global $table, $theaction, $search_Whate, $search_ID, $search_Name, $search_Project, $tableSearchTasks, $where_project;
  global $prohitsDB, $managerDB;
  global $searched_id_str;
  global $pro_access_ID_str,$frm_Projects,$frm_Users,$frm_list_by; 
  $SQL = "select 
            S.ID,
            S.PlateID,
            S.TaskName,
            S.SearchEngines,
            S.Status,
            T.ProjectID,
            T.FileName,
            T.ProhitsID,
            S.ProcessID 
            from $tableSearchTasks S, $table T 
            where S.PlateID = T.ID ";             

  if($theaction == 'search' and $search_Whate){
    $plateIDstr = '';
    if($search_Whate == 'plate' && $search_Name){
      $sqlPlate = "SELECT `ID` FROM `Plate` WHERE `Name` LIKE '%$search_Name%'";
      $plateArr = $prohitsDB->fetchAll($sqlPlate);
      foreach($plateArr as $value){
        if($plateIDstr) $plateIDstr .= ',';
        $plateIDstr .= $value['ID'];
      }
    }
    if($plateIDstr && $search_ID){
      $plateIDstr .= ',' . $search_ID;
    }elseif(!$plateIDstr && $search_ID){  
      $plateIDstr = $search_ID;
    }
    
    if($search_Project == -5){
      $SQL .= "and $where_project ";
    }else{
      $SQL .= "and S.ProjectID='$search_Project' ";
    }
    
    if($search_Whate == 'task'){
      $likeStr = ($search_Name)?"S.TaskName LIKE'%$search_Name%'":"0";
      $SQL .= "and (S.ID='$search_ID' or $likeStr)";
    }elseif($search_Whate == 'folder'){
      $likeStr = ($search_Name)?"T.FileName LIKE'%$search_Name%'":"0";
      $SQL .= "and (S.PlateID='$search_ID' or $likeStr)";
    }elseif($search_Whate == 'plate'){
      $inStr = ($plateIDstr)?"T.ProhitsID IN ($plateIDstr)":"0";
      $SQL .= "and " . $inStr;
    }else{
      exit;
    }
  }else{
    if($searched_id_str){
      $SQL .= "and S.ID in($searched_id_str) ";
    }
    $SQL .= "and $where_project";
  }
    
  $runningSQL = $SQL . " AND S.Status = 'Running' order by ID desc";
  $runningRecord = $managerDB->fetchAll($runningSQL);
//echo "$runningSQL";
//exit;  
/*echo "<pre>";
print_r($runningRecord);  
echo "</pre>";*/  
  
  if($runningRecord){
    $SQL .=  " AND S.Status != 'Running'";
  }
  if($pro_access_ID_str){
    if(isset($frm_list_by) && $frm_list_by == "task"){
      if($frm_Projects == "all" && $frm_Users == "all"){
        $SQL .=  " AND S.ProjectID IN($pro_access_ID_str)";
      }elseif($frm_Projects == "all"){
        $SQL .=  " AND S.UserID='$frm_Users' AND S.ProjectID IN($pro_access_ID_str)";
      }elseif($frm_Users == "all"){
        $SQL .=  " AND S.ProjectID='$frm_Projects'";
      }else{
        $SQL .=  " AND S.UserID='$frm_Users' AND S.ProjectID='$frm_Projects'";
      }
    }
  }else{
    $SQL .=  " AND 0";
  }
  return $SQL;  
} 

function create_sql_str_for_folder(){
  global $table, $tableSearchTasks;
  global $prohitsDB, $managerDB;
  global $pro_access_ID_str,$frm_Projects,$frm_Users,$frm_list_by;
  global $specialTaskArr;
  
  $SQL = "SELECT `ID`,`PlateID` FROM ".$tableSearchTasks;
  if($pro_access_ID_str){
    if($frm_Projects == "all" && $frm_Users == "all"){
      $SQL .=  " WHERE ProjectID IN($pro_access_ID_str)";
    }elseif($frm_Projects == "all"){
      $SQL .=  " WHERE UserID='$frm_Users' AND ProjectID IN($pro_access_ID_str)";
    }elseif($frm_Users == "all"){
      $SQL .=  " WHERE ProjectID='$frm_Projects'";
    }else{
      $SQL .=  " WHERE UserID='$frm_Users' AND ProjectID='$frm_Projects'";
    }
  }else{
    $SQL .=  " WHERE 0";
  }
  $plateIDarr = $managerDB->fetchAll($SQL);
  $plateIDstr = '';
  foreach($plateIDarr as $tmpValue){
    if(strstr($tmpValue['PlateID'], ',')){
      $tmpArr = explode(',',$tmpValue['PlateID']);
      $specialTaskArr[$tmpValue['ID']] = $tmpArr;
    }
    if($plateIDstr) $plateIDstr .= ',';
    $plateIDstr .= $tmpValue['PlateID'];
  }
  $SQL = "select ID, FileName, ProhitsID, ProjectID, Date 
          from $table WHERE";
  if($plateIDstr){        
    $SQL .=  " ID IN($plateIDstr)";
  }else{
    $SQL .=  " 0";
  }
  return $SQL;
} 
 
function initial_arrays($project_ID){
  global $pro_access_ID_Names,$user_id_name_arr,$tableSearchTasks;
  global $PROHITSDB,$managerDB;
  global $pro_access_ID_str;
  $SQL = "SELECT `ProjectID` FROM $tableSearchTasks GROUP BY `ProjectID`";
  $projectsForTasks = $managerDB->fetchAll($SQL);
  $page_project_IDs = array();
  for($i = 0; $i<count($projectsForTasks); $i++){
    array_push($page_project_IDs, $projectsForTasks[$i]['ProjectID']);
  }
  $Projects_obj = new Projects(); 
  $Projects_obj->getAccessProjects($_SESSION["USER"]->ID);
  $pro_access_ID_str = '';
  for($i= 0;$i < $Projects_obj->count; $i++){
    if(!in_array($Projects_obj->ID[$i], $page_project_IDs)) continue;
    if($pro_access_ID_str) $pro_access_ID_str .= ',';
    $pro_access_ID_str .= $Projects_obj->ID[$i];
    $pro_access_ID_Names[$Projects_obj->ID[$i]] = $Projects_obj->Name[$i];
  }
  if($project_ID != 'all') $pro_access_ID_str = $project_ID;
  if($pro_access_ID_str){
    $SQL = "SELECT `UserID` 
            FROM $tableSearchTasks 
            WHERE ProjectID IN ($pro_access_ID_str)
            GROUP BY `UserID`";
    $usersForTasks = $managerDB->fetchAll($SQL);
    $usersidForTasks = array();
    foreach($usersForTasks as $value){
      array_push($usersidForTasks, $value['UserID']);
    }
    $SQL = "SELECT `ID`,
            `Fname`,
            `Lname` 
            FROM `User`";
    $user_spl_arr = $PROHITSDB->fetchAll($SQL);
    foreach($user_spl_arr as $value){
      if(!in_array($value['ID'], $usersidForTasks)) continue;
      $user_id_name_arr[$value['ID']] = $value['Fname']." ".$value['Lname'];
    }
  }
}

function print_multiple_folders(&$plate_records){
  global $specialTaskArr,$managerDB,$tmpTaskIDarr,$table,$tableSearchTasks,$runningTaskPlateIDs;
  global $pro_access_ID_str,$frm_Projects,$frm_Users,$frm_list_by;
  
  foreach($specialTaskArr as $tmpKey => $value1){
    if(!in_array($tmpKey, $tmpTaskIDarr) && in_array($plate_records['ID'], $value1)){
      array_push($tmpTaskIDarr, $tmpKey);
      $tmpIDstr = implode(",", $value1);
      $SQL = "select ID, FileName, ProhitsID, ProjectID, Date 
              from $table 
              where ID IN($tmpIDstr)";
      $multipleFoldArr = $managerDB->fetchAll($SQL);
      $SQL = "select ID from $tableSearchTasks where PlateID='".$tmpIDstr."'";
      if($pro_access_ID_str){
        if($frm_Projects != "all"){
          $SQL .=  " AND ProjectID='$frm_Projects'";
        }elseif($frm_Users != "all"){
          $SQL .=  " AND UserID='$frm_Users' AND ProjectID IN($pro_access_ID_str)";
        }else{
          $SQL .=  " AND ProjectID IN($pro_access_ID_str)";
        }
      }else{
        $SQL .=  " AND 0";
      }
      $managerDB->check_connection();
      $tmp_rds = $managerDB->fetchAll($SQL);
      $tmp_task_str = '';
      foreach ($tmp_rds as $key => $value){
        $tmp_task_str .= ($tmp_task_str)?"<br>".$value['ID']:$value['ID'];
      }
      echo  "<tr>";
      $tmpIDStr = '';
      $tmpProhitsIDStr = '';
      $tmpProjectIDStr = '';
      $tmpFileNameStr = '';
      $tmpDateStr = '';
      foreach($multipleFoldArr as $FoldArrvalue){
        if($tmpIDStr) $tmpIDStr .= '<br>';
        if($tmpProhitsIDStr) $tmpProhitsIDStr .= '<br>';
        if($tmpProjectIDStr) $tmpProjectIDStr .= '<br>';
        if($tmpFileNameStr) $tmpFileNameStr .= '<br>';
        if($tmpDateStr) $tmpDateStr .= '<br>';
        $tmpIDStr .= $FoldArrvalue['ID'];
        $tmpProhitsIDStr .= $FoldArrvalue['ProhitsID'];
        $tmpProjectIDStr .= $FoldArrvalue['ProjectID'];
        $tmpFileNameStr .= $FoldArrvalue['FileName'];
        $tmpDateStr .= $FoldArrvalue['Date'];
      }
      $bgcolorTmp=(in_array($tmpIDstr, $runningTaskPlateIDs))?"yellow":"#ffffff";
      echo "<td bgcolor=$bgcolorTmp>".$tmpIDStr."</td>
        <td bgcolor=$bgcolorTmp>".$tmpProjectIDStr."</td>
        <td bgcolor=$bgcolorTmp>".$tmpFileNameStr."</td>
        <td bgcolor=$bgcolorTmp>".$tmpDateStr."</td>
        <td bgcolor=$bgcolorTmp>".$tmp_task_str."</td>
        <td bgcolor=$bgcolorTmp><a href=./ms_search_results_detail.php?table=$table&frm_PlateID=" . $tmpIDstr. "><img src=./images/icon_checked.gif border=0 alt=detail></a></td>
       ";
      echo "</tr>\n";
      break;
    }
  }
}             
?>
