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

//error_reporting(~E_NOTICE);
$thePage = '';
$theaction = '';
$open_dir_ID = 0;
$tableName = '';
$frm_date1 = '2009-01-01';
$frm_date2 = '';
$frm_name1 = '';
$frm_name2 = '';
$frm_or_and = 'OR';
$outRawFiles = array();
$title_lable = '';
$searched_raw_str = '';
$searched_id_str = '';

include("./ms_permission.inc.php");
require("classes/Storage_class.php");
require("../analyst/classes/dateSelector_class.php");
include ( "./is_dir_file.inc.php");

$USER = $_SESSION['USER'];
$DateSelector = new DateSelector();

if($USER->Type == 'Admin'){
  $where_project = 1;
}else{
  $where_project = "ProjectID in($pro_access_ID_str)";
}
if($searched_id_str) $searched_raw_str = $searched_id_str;
$projectName_arr = array();
$baitName_arr = array();
$tmp_sample_id_str = '';
$SQL = "select ID, Name, DBname from Projects order by ID";
$rds = $prohitsDB->fetchAll($SQL);
$tmp_db_obj_arr = array();
$tmp_db_name = '';
//create mysqlDB objects for all hit databases
for($i=0; $i < count($rds); $i++){
  $projectName_arr[$rds[$i]['ID']] = $rds[$i]['Name'];
  $tmp_db_name = $rds[$i]['DBname'];
  if(!isset($tmp_db_obj_arr[$tmp_db_name])){
    $tmp_db_obj_arr[$tmp_db_name] = new mysqlDB($HITS_DB[$tmp_db_name]);
  }
  $hitDB_obj_arr[$rds[$i]['ID']] = $tmp_db_obj_arr[$tmp_db_name];
}

if($theaction == 'fetch' or $theaction == 'search'){
  $table_arr = $managerDB->list_tables();
  $SQL_like = '(';
  $SQL_date = '';
  $tmp_name1 = mysqli_escape_string($managerDB->link, trim($frm_name1)); 
  $tmp_name2 = mysqli_escape_string($managerDB->link, trim($frm_name2));
  //echo "$frm_name1         $frm_name2       $frm_date1       $frm_date2       $tableName"; 
  $tmp_date1 = substr($frm_date1, 0,8) . "00";
  $tmp_date2 = substr($frm_date2, 0,8) . "32";
  $SQL = "SELECT `ID`, `FileName`, `FileType`, `FolderID`, `Date`, `User`, `ProhitsID`, `ProjectID`, `Size`";
  
  if($frm_name1) {
     $SQL_like .= "`FileName` LIKE ('%". $tmp_name1."%')";
  }
  if($frm_name2) {
     if(strlen($SQL_like) > 1) {
        $SQL_like .= " $frm_or_and ";
     }
     $SQL_like .= "`FileName` LIKE ('%".$tmp_name2."%')";
  }
  $SQL_like .= ')';
  $SQL_date = "  AND FileType <> 'sld' AND `Date` > '".$tmp_date1."' AND `Date` < '".$tmp_date2."' and ". $where_project ;
   
  
   
   
  if($tableName){
    $SQL_from = "From `".$tableName."` WHERE". $SQL_like . $SQL_date ." ORDER BY ID desc";
    $rd_count = $managerDB->fetch("Select count(ID) as num ".  $SQL_from);
    if($rd_count['num']+1 > 200){
      $outRawFiles[$tableName] = "too many files/folders match the criteria";
    }else{
      $SQL .= $SQL_from;
      $theRawFiles = $managerDB->fetchall($SQL);
       
      if($theRawFiles){
         $outRawFiles[$tableName] = $theRawFiles;
      }
    }
  }else{
    $searched_table_name_arr = array();
    $searched_table_str_arr = array();
    if($theaction == 'search'){
      $tmp_arr = explode(';;', $searched_raw_str);
      foreach($tmp_arr as $tmp_value){
        if($tmp_value){
          $tmp_table_arr = explode(":", $tmp_value);
          if(count($tmp_table_arr)==2){
            array_push($searched_table_name_arr, $tmp_table_arr[0]);
            $searched_table_str_arr[$tmp_table_arr[0]] = $tmp_table_arr[1];
          }
        }
      }
    }
    foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
      if(!in_array($baseTable, $table_arr)) continue; 
      if($theaction == 'search'){
        if(!in_array($baseTable, $searched_table_name_arr)) continue; 
        $SQL_tmp_from = "From `".$baseTable."` WHERE ID in(". $searched_table_str_arr[$baseTable].")";
      }else{
        $SQL_tmp_from = "From `".$baseTable."` WHERE". $SQL_like . $SQL_date;
      }
      $rd_count = $managerDB->fetch("Select count(ID) as num ".  $SQL_tmp_from);
      if($rd_count['num']+1 > 1000){
        $outRawFiles[$baseTable] = "too many files/folders match the criteria";
      }else{
        $SQL_tmp = $SQL . $SQL_tmp_from;
        $theRawFiles = $managerDB->fetchall($SQL_tmp);
        if($theRawFiles){
          $outRawFiles[$baseTable] = $theRawFiles;
        }
      }
    }
  }
}
//print_r($outRawFiles);
include("./ms_header.php");
?>

<script language="javascript">
function checkform(theForm){
  if(isEmptyStr(theForm.frm_name1.value) && isEmptyStr(theForm.frm_name2.value)){
    alert("Please enter raw file name in 'File Name' box");
    return false;
  }
  var sleY1 = theForm.frm_datefrom_Year;
  var sleM1 = theForm.frm_datefrom_Month;
  theForm.frm_date1.value = sleY1.options[sleY1.selectedIndex].value + "-" + check_month(sleM1.options[sleM1.selectedIndex].value) + "-01";
  var sleY2 = theForm.frm_dateto_Year;
  var sleM2 = theForm.frm_dateto_Month;
  theForm.frm_date2.value = sleY2.options[sleY2.selectedIndex].value + "-" + check_month(sleM2.options[sleM2.selectedIndex].value) + "-01";
  if(theForm.frm_date1.value > theForm.frm_date2.value){
    alert("Please select the date 'From' less or equal the date 'TO'!");
  }
  theForm.theaction.value = 'fetch';
  theForm.submit();
}
function check_month(str){
  if(str.length == 1){
    str = "0" + str;
  }
  return str;
}
function open_dir(theID, theTableName){
  var theForm = document.sForm;
  theForm.action = 'ms_storage_raw_data_plate.php';
  theForm.tableName.value = theTableName;
  theForm.submit();
}
function download(FileID, theTableName){
  var file ='<?php echo "http://".STORAGE_IP.dirname($_SERVER['PHP_SELF'])."/autoBackup/download_raw_file.php?SID=". session_id(). "&tableName=";?>'+theTableName + '&ID=' + FileID;
  popwin(file,520, 400)
}
function open_dir(theID, theTableName){
  var theForm = document.sForm; 
  theForm.open_dir_ID.value=theID;
  theForm.tableName.value = theTableName;
  theForm.action = 'ms_storage_raw_data_plate.php';
  theForm.submit();
}
function linkProhitsID(theID,theTableName){
  file = './ms_storage_pop_link_prohits_id.php?tableName=' + theTableName + '&raw_file_ID=' + theID;
  popwin(file,600,450);
}
</script>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
  <td bgcolor="#a4b0b7" valign="top" align="left" width="175">
   <?php include("./ms_storage_menu.inc.php");?>
   <br><br>
  </td>
  <td width="928" align=center valign=top>
  <?php 
  if($tableName){
      $logo = strtoupper($tableName);
      if(!is_file("./images/msLogo/" . $logo . "_logo.gif")) $logo = "default";
      echo "<img src='./images/msLogo/".$logo."_logo.gif' align=center>\n";
      echo "<font face='Arial' size='4' color='#660000'><b>$tableName</b></font>\n";
    }
    ?><br><br>
   <table border=0 width=97% bgcolor="#ccccc" cellspacing="1" >
   <form name=sForm method=post action=<?php echo $PHP_SELF;?>>
   <input type=hidden name=theaction value=''>
   <input type=hidden name=frm_date1 value=''>
   <input type=hidden name=frm_date2 value=''>
   <input type=hidden name=open_dir_ID value=''>
   
   
    <tr><td align=center>
    <font face="Arial" size="4" color="#000000"><b><?php echo ($title_lable)?urldecode($title_lable):"Fetch Raw File";?></b></font>
		</td>
    </tr>
    <tr bgcolor=white>
      <td align=center>
      <table border=0 width=95% cellpadding="3">
        <tr>
        	<td><b>Machine Name</b>:</td>
        	<td>
              <select name="tableName">
        				<option value="">All
                <?php 
                foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
                  $selected = ($tableName == $baseTable)? 'selected':'';
                  if(!in_array($baseTable, $table_arr)) continue; 
                  echo "<option value='$baseTable' $selected>$baseTable\n";
                }
                ?>
              </select>
          </td>
        </tr>
        <tr>
        	<td><b>File Name</b>:</td>
        	<td><input type="text" name="frm_name1" size="19" maxlength="20" value='<?php echo $frm_name1;?>'> &nbsp; 
            <select name="frm_or_and">
			      <option value="OR" <?php echo ($frm_or_and == 'OR')?'selected':'';?>>OR
			      <option value="AND" <?php echo ($frm_or_and == 'AND')?'selected':'';?>>AND
            </select>&nbsp; 
           <input type="text" name="frm_name2" size="19" maxlength="20" value='<?php echo $frm_name2;?>'> </td>
        </tr>
				
        <tr>
        	<td><b>Date From</b>: </td>
        	<td><?php echo $DateSelector->setDate('frm_datefrom_', $frm_date1, false);?> &nbsp; <b>TO</b> &nbsp; <?php echo $DateSelector->setDate('frm_dateto_', $frm_date2, false);?></td>
        </tr>
        <tr align=right>
        	<td colspan=2><input type="button" name="Fetch" value="Fetch" onClick='checkform(this.form)'></td>
        </tr>
      </table>
 <?php if($outRawFiles){?>
      <table border=0 width=100% cellspacing=0 cellpadding=0>
       <tr><td bgcolor=#d2d2d2>
        <table border=0 width=100% cellspacing=1 cellpadding=0>
        <tr>
          <th width=7%>ID</a>
          </th>
          <th width=30%>Folder/File Name</a>
          </th>
          <th width=12%>Size(KB)</a>
          </th>
          <th width=30%>Project<br>Bait<br>Sample</a>
          </th>
          <th width=25%>Date</a>
          </th>
          <th width=20%>Search<br>Task</th>
          <th width=20%>Options</th>
        </tr>
        <?php 
      foreach($outRawFiles as $baseTable => $raw_files){
        if(!$tableName){
          echo "<tr><td colspan=6><b><font size='3'>$baseTable</font></b></td></tr>\n";
        }
        if(!is_array($raw_files)){
          echo "<tr><td colspan=6 bgcolor=white><b>$raw_files</b></td></tr>\n";
          continue;
        }
				
				if(!$tableName) {
				  $table = $baseTable;
					$tableSearchResults = $table."SearchResults";
					$tableSearchTasks = $table."SearchTasks";
				}
				$set_auto_search = false;
				if($managerDB->exist("show tables like '".$table."SearchResults'")){
					$set_auto_search = true;
				}
				
				$search_detail_folder_url = "<a  title='task detail' href='./ms_search_results_detail.php?";
        for($i=0; $i < count($raw_files); $i++){ 
          $has_sub = true;
          $bgcolor="#ffffff";
          $tmp_Size = '';
          if($raw_files[$i]['Size'] and $raw_files[$i]['FileType'] != 'dir'){
            $tmp_Size = number_format(ceil($raw_files[$i]['Size']/1024));
          }
          echo "<tr>
                <td bgcolor=$bgcolor>".$raw_files[$i]['ID']."</td>
                <td bgcolor=$bgcolor>".$raw_files[$i]['FileName']."</td>
                <td bgcolor=$bgcolor>".$tmp_Size."</td>
                <td bgcolor=$bgcolor align=center>\n";
          if($raw_files[$i]['FileType'] != 'dir'){
            if(is_numeric($raw_files[$i]['User']) && $raw_files[$i]['User'] >0 && $raw_files[$i]['ProhitsID']){
              $tmp_icon = "icon_link_y.gif";
							$tmp_title = "<b>Manual-linked</b><br>";
            }elseif($raw_files[$i]['ProhitsID']){
              $tmp_icon = "icon_link_g.gif";
							$tmp_title = "<b>Auto-linked</b><br>";
            }else{
               $tmp_icon = "icon_link.gif";
							 $tmp_title = '<b>Unlinked</b><br>Click the icon to link the file to sample';
            }
            
            if($raw_files[$i]['ProhitsID'] and $raw_files[$i]['ProjectID']){
              $SQL = "select D.ID, D.BaitID,D.Location, B.GeneName from Band D, Bait B where D.BaitID=B.ID and D.ID='".$raw_files[$i]['ProhitsID']."'";
              $tmp_sample_rd = $hitDB_obj_arr[$raw_files[$i]['ProjectID']]->fetch($SQL);
              if(isset($projectName_arr[$raw_files[$i]['ProjectID']])){
                 
								$tmp_title .= "Project: " .$projectName_arr[$raw_files[$i]['ProjectID']];
                if($tmp_sample_rd){
                  
									$tmp_title .= "<br>Bait: (".$tmp_sample_rd['BaitID'].")".$tmp_sample_rd['GeneName'];
                  $tmp_title .= "<br>Sample: ".$tmp_sample_rd['Location'];
                }else{
									$tmp_title .= "<br><font color=red>broken link:sample ID ". $raw_files[$i]['ProhitsID']."</font>";
                }
              }else{
								 $tmp_title .= "<br><font color=red>broken link:project ID ". $raw_files[$i]['ProjectID']."</font>";
              }
            }
						echo "<a class='title' title='$tmp_title' href=\"javascript: linkProhitsID('".$raw_files[$i]['ID']."','$baseTable');\"><img src=./images/$tmp_icon border=0></a>\n";
            
          }else if($raw_files[$i]['ProjectID']){
					  echo "<a class='title' title='Project: ".$projectName_arr[$raw_files[$i]['ProjectID']]."'>PID:".$raw_files[$i]['ProjectID']."</a>\n";
            //echo $projectName_arr[$raw_files[$i]['ProjectID']];
          }
          echo "</td>"; 
          echo "<td bgcolor=$bgcolor>".$raw_files[$i]['Date']."</td>\n";
					
					
					
					
					$tmp_task_str = '';
					$tmp_folderID = 0;
					
					
					if($set_auto_search){
						if($raw_files[$i]['FileType'] != 'dir'){
							$SQL = "SELECT DISTINCT TaskID as ID FROM ".$tableSearchResults."  WHERE WellID='".$raw_files[$i]['ID']."' order by TaskID desc";
							$tmp_folderID = $raw_files[$i]['FolderID'];
						}else{
							$SQL = "select DISTINCT ID from ".$tableSearchTasks." where PlateID='".$raw_files[$i]['ID']."' order by ID desc";
							$tmp_folderID = $raw_files[$i]['ID'];
						}
						$tmp_rds = $managerDB->fetchAll($SQL);
		         if(count($tmp_rds)){
		           foreach ($tmp_rds as $key => $value){
							  if($tmp_task_str) $tmp_task_str .= "<br>";
								$tmp_task_str .= $search_detail_folder_url."table=$table&frm_PlateID=".$tmp_folderID."&iniTaskID=".$value['ID']."'>".$value['ID']."</a>";
		           }
						}
					}
					echo "<td bgcolor=$bgcolor align=center>".$tmp_task_str."</td>\n";
					
					
					
					
					
					
          echo "<td bgcolor=$bgcolor align=center>";
          if($raw_files[$i]['FileType'] != 'dir'){
             echo "<a  title='parent folder' href=\"javascript: open_dir('".$raw_files[$i]['FolderID']."','".$baseTable."');\"><img src='./images/icon_dir_up.gif' border=0 alt='open up folder'></a>\n";
             echo "<a  title='download' href=\"javascript: download('".$raw_files[$i]['ID']."','$baseTable');\"><img src='./images/icon_download.gif' border=0 alt=download></a>\n";
          }else{
              echo "<a  title='open folder'  href=\"javascript: open_dir('".$raw_files[$i]['ID']."','".$baseTable."');\"><img src='./images/icon_dir.gif' border=0 alt='open folder'></a>\n";
          }
          echo "</td></tr>";
        }//end for
      }
      echo "</table>";
      echo "</td></tr></table>";
  }
      ?>
      </td>
   </tr>
   </table>
   </form> 
  </td>
  </tr>
</table>
<?php 
include("./ms_footer.php");
?>
