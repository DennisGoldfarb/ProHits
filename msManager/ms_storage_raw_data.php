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
$year = '';
$month = '';
$day = '';
$order_by = 'ID desc';
$start_point = '';
$filePath = '';
$filePathID = '';
$open_dir_name = '';
$currentLable = '';

$tableName = '';
$crtPro = '';
$thePage = '';
$tmp_str = '';
$displayby = '';
//----for calendar.inc.php-----
$open_dir_ID = 0;
$info = '';
$frm_lcq_para_str = '-B300 -T4000 -I10 -S1 -G1 -M1.4';
$frm_format = '';
$frm_setBy = "";
$frm_date = '';

include ("./ms_permission.inc.php");
require ("msManager/classes/Storage_class.php");
include ("msManager/is_dir_file.inc.php");
$USER = $_SESSION['USER'];


/*
if($myaction == 'save_lcq_para' and ($USER->Type == 'MSTech' or $USER->Type == 'Admin')){
  $SQL = "INSERT INTO RawConvertParameter set 
    TableName='$tableName',
    Format='$frm_format',
    Parameter='$frm_lcq_para_str',
    UserName='".$USER->Fname." ".$USER->Lname."',
    DateTime=now()";
  $managerDB->insert($SQL);
}
$SQL = "SELECT Format, Parameter, UserName, DateTime FROM RawConvertParameter 
  where TableName='$tableName' order by ID desc limit 1";
$para_record = $managerDB->fetch($SQL);
if($para_record){
 
  $frm_format = $para_record['Format'];
  if($para_record['Parameter'])
  $frm_lcq_para_str = $para_record['Parameter'];
  $frm_setBy = $para_record['UserName'];
  $frm_date = $para_record['DateTime'];
}

//user type is 'user' only can access his own data.
*/
$ObjTable = new Storage($managerDB->link,$tableName);
 
$year_pass = $year;  //passed value from form or query string
$month_pass = $month; //they will be used in form "listform"
$day_pass = $day;

//default list all records of the user
if($crtPro){
  $where_project = "ProjectID='$crtPro'";
}else if($USER->Type == 'MSTech' or $USER->Type == 'Admin'){
  $where_project = 1;
}else{
  $where_project = "ProjectID in($pro_access_ID_str)";
}
 
 
//page counter start ===============================================================
define ("RESULTS_PER_PAGE", 25);
define ("MAX_PAGES", 10); //this is max page link to display
$total_records = $ObjTable->get_user_total($where_project,$year_pass,$month_pass,$day_pass);
$PAGE_COUNTER = new PageCounter();
 
$query_string = "year=$year_pass&month=$month_pass&day=$day_pass&crtPro=$crtPro&tableName=$tableName";
$caption = "";
if($order_by)  { 
$query_string .= "&order_by=".$order_by;
}

 
$page_output = $PAGE_COUNTER->page_links($start_point, $total_records, RESULTS_PER_PAGE, MAX_PAGES, str_replace(' ','%20',$query_string)); 
if(!$order_by) $order_by = "ID desc";
if(!$start_point) $start_point = 0;
$ObjTable->fetchall($order_by,$start_point,RESULTS_PER_PAGE, $where_project,$year_pass,$month_pass,$day_pass);
//end of page counter=================================================================
$convertConFlag = 1;
$convertPathFlag = 1;
$convertStr = '';
$old = ini_set('default_socket_timeout', 2);
if(defined('RAW_CONVERTER_SERVER_PATH') and RAW_CONVERTER_SERVER_PATH){
  if(!$fd= @fopen(RAW_CONVERTER_SERVER_PATH, 'r')){ 
     $convertPathFlag = 0;
  }else{
    fclose($fd);
		$convertStr = RAW_CONVERTER_SERVER_PATH;
  }  
}else{
  $convertConFlag = 0;
}
ini_set('default_socket_timeout', $old);
$set_auto_search = false;
if($managerDB->exist("show tables like '".$table."SearchResults'")){
	$set_auto_search = true;
}
include("./ms_header.php");
 
?>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<script language="javascript">
function sort_it(sort_it){
  var theForm = document.listform;
  theForm.order_by.value=sort_it;
  theForm.submit();
}
function open_dir(theID, theProID){
  var theForm = document.listform;
  theForm.order_by.value='<?php echo $order_by;?>';
  theForm.start_point.value='<?php echo $start_point;?>';
  theForm.open_dir_ID.value=theID;
  theForm.open_dir_pro_ID.value = theProID;
  theForm.action = 'ms_storage_raw_data_plate.php';
  theForm.submit();
}
function select_user(){
  var theForm = document.listform;
  theForm.year.value='';
  theForm.month.value='';
  theForm.day.value='';
  theForm.submit();
}
function toggleBox(sel, szDivID) {
  var theForm = document.listform;
  var obj = document.getElementById(szDivID);
  if(sel.options[sel.selectedIndex].value == 'mgf'){
    if(<?php echo $convertConFlag?> == 0){
      alert("Constant 'RAW_CONVERTER_SERVER_PATH' is undefined!");
      theForm.frm_format.value = '';
      obj.style.display = "none";
    }else if(<?php echo $convertPathFlag?> == 0){
      alert("Path <?php echo $convertStr?> is not exist!");
      theForm.frm_format.value = '';
      obj.style.display = "none";
    }else{
      obj.style.display = "block";
    }  
  }else{
    obj.style.display = "none";
  }
}
function download(FileID){
  var file ='<?php echo "http://".$storage_ip.dirname($_SERVER['PHP_SELF'])."/autoBackup/download_raw_file.php?SID=". session_id(). "&tableName=$tableName";?>' + '&ID=' + FileID;
  popwin(file,520, 400)
}
function save_para_str(){
  var theForm = document.listform;
  var sel = theForm.frm_format;
  if(sel.options[sel.selectedIndex].value != 'mgf'){
  		theForm.frm_lcq_para_str.value = '';
  }
   
  theForm.myaction.value = 'save_lcq_para';
  theForm.start_point.value='<?php echo $start_point;?>';
  theForm.submit();
}
function pop_upload_form(){ 
  var file = 'http://<?php echo $storage_ip . dirname($_SERVER['PHP_SELF']);?>/autoBackup/ms_pop_upload_raw_files.php?tableName=<?php echo $tableName;?>&UserID=<?php echo $AccessUserID;?>&UserType=<?php echo $USER->Type?>&SID=<?php echo session_id();?>&folderLevel=1&status=firstTime';
  popwin(file,820,800)
}
function pop_backup_now(){ 
  var file ='<?php echo "http://";?>'+'<?php echo (defined('STORAGE_IP_OLD') and STORAGE_IP_OLD)?STORAGE_IP_OLD:$storage_ip;?>'+'<?php echo dirname($_SERVER['PHP_SELF'])."/autoBackup/pop_backup_now.php?SID=". session_id(). "&tableName=$tableName";?>';
  popwin(file,600,400)
}
function popXcalibur(theForm){
 var str = theForm.frm_lcq_para_str.value;
 file = './ms_search_xcalibur.php?frm_lcq_para_str=' + str + '&openerForm=listform';
 popwin(file, 730, 480); 
}
</script>

  <tr>
  <td bgcolor="#a4b0b7" valign="top" align="left" width="170">
   
   <?php include("./ms_storage_menu.inc.php");?>
   <?php include("./calendar/calendar.inc.php");?>
  
   <br><br>
  </td>
  <td width="928" align=center valign=top>
   <table border=0 width=97%>
   <form name=listform method=get action=<?php echo $PHP_SELF;?>>
   <input type=hidden name=myaction value=''>
   <input type=hidden name=year value='<?php echo $year_pass;?>'>
   <input type=hidden name=month value='<?php echo $month_pass;?>'>
   <input type=hidden name=day value='<?php echo $day_pass;?>'>
   <input type=hidden name=order_by value=''>
   <input type=hidden name=tableName value='<?php echo $tableName;?>'>
   
   <input type=hidden name=open_dir_ID value=''>
   <input type=hidden name=open_dir_pro_ID value=''>
   <input type=hidden name=start_point value=''>
   
    <tr><td align=center>
    <?php 
    $logo = strtoupper($tableName);
    if(!is_file("./images/msLogo/" . $logo . "_logo.gif")) $logo = "default";
    ?>
    <img src='./images/msLogo/<?php echo $logo."_logo.gif";?>' align=center>
     <font face="Arial" size="4" color="#660000"><b><?php echo $tableName;?> raw data</b></font>
     <hr width="100%" size="1" noshade>
    </td></tr>
    <tr><td bgcolor=#cfd7f1>&nbsp;
    <?php if(isset($BACKUP_SOURCE_FOLDERS[$tableName]) and $perm_insert){
      if(isset($BACKUP_SOURCE_FOLDERS[$tableName]['SOURCE']) and $BACKUP_SOURCE_FOLDERS[$tableName]['SOURCE']){
    ?>
     <input type=button value='Backup File Now' onClick='pop_backup_now()'>  
     <?php }?>
     <input type=button value='Upload Raw Files' onClick='pop_upload_form()'>
      <a href="javascript: popwin('../doc/management_help.html#Linking_upload',600,600);"><img src=./images/icon_help.gif border=0></a>
		<?php }?> 
		</td>
    </tr>
    <tr><td align=right>
    <?php echo $page_output;?>
    </td></tr>
    <tr><td align=right>
    <!--input type=button value='Upload Raw Files' onClick='pop_upload_form()'--> &nbsp;&nbsp;&nbsp;
    <b>Browse Project:</b> 
      <select name="crtPro">
        <option value="">All
      <?php 
      //$pro_access_ID_Names[$pID['ID']] = $pID['Name'];
      foreach($pro_access_ID_Names as  $key => $value){
         echo "    <option value='".$key."'";
         if($crtPro == $key) echo " selected";
         echo ">[ID:$key] ".$value."\n";
      }
      ?>
      </select>
      <input type=button value="Go" onClick="javascript: select_user();">
    </td></tr>
    <tr><td align=center>
       <table border=0 width=100% cellspacing=0 cellpadding=0>
       <tr><td bgcolor=#d2d2d2>
        <table border=0 width=100% cellspacing=1 cellpadding=2>
        <tr>
          <th width=7%><a href="javascript: sort_it('<?php echo ($order_by == "ID")? 'ID desc':'ID';?>');">ID</a>
            <?php 
            if($order_by == "ID") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "ID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </th>
          <th width=48%>[Project ID] <a href="javascript: sort_it('<?php echo ($order_by == "FileName")? 'FileName desc':'FileName';?>');"> Name</a>
            <?php 
            if($order_by == "FileName") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "FileName desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </th>
          <th width=5%><a href="javascript: sort_it('<?php echo ($order_by == "FileType")? 'FileType desc':'FileType';?>');">Type</a>
            <?php 
            if($order_by == "FileType") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "FileType desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </th>
           <th width=12%><a href="javascript: sort_it('<?php echo ($order_by == "Date")? 'Date desc':'Date';?>');">Date</a>
            <?php 
            if($order_by == "Date") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "Date desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </th>
          <th width=10%>&nbsp;Search<br>Task</th> 
          <th width=10%>&nbsp;Options</th>
        <?php 
	   
        //record list from msManager
        //$ObjTable_tmp = new $tableName();
				$search_detail_folder_url = "<a  title='task detail' href='./ms_search_results_detail.php?table=$table&";
        for($i=0; $i < $ObjTable->count; $i++){ 
          $has_sub = true;
          $bgcolor="#ffffff";
          echo  "\n<tr>";
					$tmp_task_str = '';
					
				  if($set_auto_search){
						if($ObjTable->FileType[$i] != 'dir'){
							$SQL = "SELECT DISTINCT TaskID as ID FROM ".$tableSearchResults."  WHERE WellID='".$ObjTable->ID[$i]."' order by TaskID desc";
							$tmp_folderID = $ObjTable->FolderID[$i];
						}else{
							$SQL = "select DISTINCT ID from ".$tableSearchTasks." where PlateID='".$ObjTable->ID[$i]."' order by ID desc";
							$tmp_folderID = $ObjTable->ID[$i];
						}
						$tmp_rds = $managerDB->fetchAll($SQL);
	          if(count($tmp_rds)){
	            foreach ($tmp_rds as $key => $value){
							  if($tmp_task_str) $tmp_task_str .= "<br>";
								$tmp_task_str .= $search_detail_folder_url."frm_PlateID=".$tmp_folderID."&iniTaskID=".$value['ID']."'>".$value['ID']."</a>";
	            }
						}
					}
					 
          $tmp_pro_name = ($ObjTable->ProjectID[$i])?$pro_access_ID_Names[$ObjTable->ProjectID[$i]]:'';
					if(!$tmp_pro_name) $tmp_pro_name = 'no project';
          echo "<td bgcolor=$bgcolor>".$ObjTable->ID[$i]."</td>
                <td bgcolor=$bgcolor><span style=\"background-color:#c0c0c0;\">[<a  title='$tmp_pro_name' href='#'>".$ObjTable->ProjectID[$i]."</a>]</span> ". $ObjTable->FileName[$i]."</td>
                <td bgcolor=$bgcolor>".$ObjTable->FileType[$i]."</td>
                <td bgcolor=$bgcolor>".$ObjTable->Date[$i]."</td>
                <td bgcolor=$bgcolor align=center>".$tmp_task_str."</td>
                <td bgcolor=$bgcolor align=center>";
          if($ObjTable->FileType[$i] != 'dir'){
             echo "<a  title='download' href=\"javascript: download('".$ObjTable->ID[$i]."');\">
              <img src='./images/icon_download.gif' border=0 alt=download></a>
             ";
          }else{
            echo "<a  title='open folder' href=\"javascript: open_dir('".$ObjTable->ID[$i]."','".$ObjTable->ProjectID[$i]."');\">";
            echo "<img src='./images/icon_dir.gif' border=0 alt='plate detail'></a> ";
            echo "<a  title='download' href=\"javascript: download('".$ObjTable->ID[$i]."');\">
              <img src='./images/icon_download.gif' border=0 alt='download plate'></a>";
          }
          echo "</td></tr>";
        }//end for
        echo "</table>";
        echo "</td></tr></table>";
     ?>
    </td></tr>
    </form>
   </table>
   <br>
  </td>
  </tr>
</table>
<?php 
include("./ms_footer.php");
?>
