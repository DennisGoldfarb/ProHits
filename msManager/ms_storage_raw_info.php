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
$myaction = '';
$open_dir_ID = 0;

$frm_name1 = '';
$frm_name2 = '';
$frm_or_and = 'OR';
$outRawFiles = array();
//=================================================
$php_file = "ms_storage_raw_info";
$display_style = "table";
$interval = "monthly";
$tableName = '';
$frm_date1 = @date('Y-m-d');
$frm_date2 = '';
$show_all = '';
$show_single = "single";
$file_size = 'size';
$num_file = 'num_files';
$table_style = 'table';
$bar_style = 'bar';
$pare_style = '';
$line_style = '';
$size_unit = 'GB';
$popwin_w = 730;
$popwin_h = 700;

$sort_by = "machine";

include("./ms_permission.inc.php");
require("classes/Storage_class.php");
require("../analyst/classes/dateSelector_class.php");
include ( "./is_dir_file.inc.php");
include ( "./autoBackup/shell_functions.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$table_arr = $managerDB->list_tables();

if($theaction == "export_status_file"){
  $all_files_arr = array();
  if(!$tableName){
    foreach($BACKUP_SOURCE_FOLDERS as $tableName => $tableInf){
      if(!in_array($tableName,$table_arr)) continue;
      get_all_files_arr($frm_date1,$frm_date2,$tableName);
    }
  }else{
    get_all_files_arr($frm_date1,$frm_date2,$tableName);
  }
  
  $statistics_file_dir = "../TMP/Raw_file_statistics";
  if(!_is_dir($statistics_file_dir)){
    _mkdir_path($statistics_file_dir);
  }
  $statistics_file_name = $statistics_file_dir."/file_statistics.csv";
  $fp = fopen($statistics_file_name, 'w');
  
  $title_line = "Instrument,MS ID,FolderPath,File Name,File Size,Date,Search Task,Project,Collaborator,Collaborator Institute\n";
  fwrite($fp, $title_line);
  $line_index_arr = array('Machine','ID','FolderPath', 'FileName','Size','Date','TaskID','ProjectID','cooperName','Institute');
    
  foreach($all_files_arr as $file_val){
    $file_line = '';
    foreach($line_index_arr as $line_index){
      $file_line .= $file_val[$line_index].',';
    }
    fwrite($fp, $file_line."\n");
  }
  fclose($fp); 
  //exit;
  
  header("Content-Type: application/octet-stream");  //download-to-disk dialog
  header("Content-Disposition: attachment; filename=\"".basename($statistics_file_name)."\"");
  header("Content-Transfer-Encoding: binary");
  header("Content-Length: "._filesize($statistics_file_name));
  readfile("$statistics_file_name");
  exit;

}elseif($theaction == "change_log"){
  log_Html();
  exit;
}

include("./ms_header.php");
$DateSelector = new DateSelector();

?>
<script language="javascript">
var xmlHttp;
function createXMLHttpRequest(){
  if(window.ActiveXObject){
      xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
  }else if(window.XMLHttpRequest){
      xmlHttp = new XMLHttpRequest();
  }
}

function initial(){
  var theForm = document.sForm;
  theForm.theaction.value = 'change_log';
  startRequest_1();
}

function display_result(theForm){
  if(!validate()) return false;
  theForm.theaction.value = 'display_results';
  startRequest_1();
}

function validate(){
  theForm = document.sForm;
  if(theForm.theaction.value == 'export_status_file'){
  
  }else{
    if(theForm.tableName.value == ''){
      if(theForm.show_all.checked == false && theForm.show_single.checked == false){
        alert("Please select 'Merge all Machines' or 'Separated Machines' to submit");
        return false;
      }
    }
  }  
  var file_type = document.getElementById("file_type").value;
  if(file_type == ''){
    alert("Please select a file type or select All types for all files");
    return false;
  }
  var from_Month = theForm.frm_datefrom_Month.value;
  if(from_Month.length == 1){
    from_Month = "0" + from_Month;
  }
  var to_Month = theForm.frm_dateto_Month.value;
  if(to_Month.length == 1){
    to_Month = "0" + to_Month;
  }
  var frm_date1 = theForm.frm_datefrom_Year.value + "-" + from_Month + "-" + "00";
  var frm_date2 = theForm.frm_dateto_Year.value + "-" + to_Month + "-" + "32";
  if(frm_date1 > frm_date2){
    alert("Error. Select date again");
    return false;
  }
  theForm.frm_date1.value = frm_date1;
  theForm.frm_date2.value = frm_date2;
  if(theForm.file_size.checked == false && theForm.num_file.checked == false){
    alert("Please select 'File Size' or 'Number of Files' to submit");
    return false;
  }
  if(theForm.table_style.checked == false && theForm.bar_style.checked == false && theForm.line_style.checked == false){
    alert("Please select 'Table' or 'Bar' or 'Line' to submit");
    return false;
  }
  return true;
}

function startRequest_1(){
  var theForm = document.sForm;
  show_hide_sub();
  var all_single_div = document.getElementById('all_single_div');
  if(theForm.tableName.value == ""){
    all_single_div.style.display = "block";
    theForm.show_single.checked = true;
  }else{
    all_single_div.style.display = "none";
    theForm.show_all.checked = false;
    theForm.show_single.checked = false;
  }
  createXMLHttpRequest();
  xmlHttp.onreadystatechange = handleStateChange_1;
  var queryString = createQueryString();
  xmlHttp.open("POST", "<?php echo $PHP_SELF;?>", true);
  xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded;");
  xmlHttp.send(queryString);
}
function handleStateChange_1(){
  if(xmlHttp.readyState == 4){
    if(xmlHttp.status == 200){
      var theForm = document.sForm;
      if(theForm.theaction.value == 'change_log'){
        clean_up_child_nodes("logo_div");
        clean_up_child_nodes("file_type_div");
        var ret_html = xmlHttp.responseText;
  			var ret_html_arr = ret_html.split("@@**@@");
        document.getElementById("logo_div").innerHTML = ret_html_arr[0];
        document.getElementById("file_type_div").innerHTML = ret_html_arr[1];
      } 
    }
  }
}
function createQueryString(){
  var theForm = document.sForm;
  if(theForm.theaction.value == 'change_log'){
    var queryString = "theaction="+theForm.theaction.value+"&tableName="+theForm.tableName.value;
  }else{
    var tableName = theForm.tableName.value;
    var file_type = document.getElementById("file_type").value;
    var theaction = theForm.theaction.value;
    var frm_date1 = theForm.frm_date1.value;
    var frm_date2 = theForm.frm_date2.value;
    var interval = '';
    for(var i=0; i<theForm.interval.length; i++){
      if(theForm.interval[i].checked == true){
        interval = theForm.interval[i].value;
        break;
      }
    }
    var size_unit = '&size_unit=';
    for(var i=0; i<theForm.size_unit.length; i++){
      if(theForm.size_unit[i].checked == true){
        var tmp_size_unit = theForm.size_unit[i].value;
        size_unit = '&size_unit=' + tmp_size_unit;
        break;
      }
    }
    var show_all = "&show_all=";
    var show_single = "&show_single=";
    var pare_style = '&pare_style=';
    if(theForm.tableName.value == ""){
      if(theForm.show_all.checked == true){
        show_all = "&show_all=" + theForm.show_all.value;
      }
      if(theForm.show_single.checked == true){
        show_single = "&show_single=" + theForm.show_single.value;
      }
    }
    var file_size = '&file_size=';
    if(theForm.file_size.checked == true){
      file_size = '&file_size=' +  theForm.file_size.value;
    }
    var num_file = '&num_file=';
    if(theForm.num_file.checked == true){
      num_file = '&num_file=' +  theForm.num_file.value;
    }
    var table_style = '&table_style=';
    if(theForm.table_style.checked == true){
      table_style = '&table_style=' +  theForm.table_style.value;
    }
    var bar_style = '&bar_style=';
    if(theForm.bar_style.checked == true){
      bar_style = '&bar_style=' +  theForm.bar_style.value;
    }
    var line_style = '&line_style=';
    if(theForm.line_style.checked == true){
      line_style = '&line_style=' +  theForm.line_style.value;
    }
    var sub_queryString = show_all + show_single + file_size + num_file + table_style + bar_style + line_style + pare_style + size_unit;
    var queryString = "tableName="+tableName+"&file_type="+file_type+"&theaction="+theaction+"&frm_date1="+frm_date1+"&frm_date2="+frm_date2+"&interval="+interval+sub_queryString;
  }
	return queryString;
}
function clean_up_child_nodes(itemID){
  var parentItem = document.getElementById(itemID);
  if(parentItem.hasChildNodes()){
    while(parentItem.childNodes.length > 0) {
      parentItem.removeChild(parentItem.childNodes[0]);
    }
  }  
}
function show_hide_sub(){
  var theForm = document.sForm;
  var pare_style_div = document.getElementById('pare_style_div');
  if(theForm.tableName.value == ""){
    if(theForm.show_all.checked == true){
      pare_style_div.style.display = "block";
    }else{
      pare_style_div.style.display = "none";
    }
  }else{
    pare_style_div.style.display = "block";
  }  
}
function display_result_win(){
  var theForm = document.sForm;
  if(!validate()) return;
  theForm.theaction.value = 'display_results';
  var queryString = createQueryString();
  var file = "./ms_storage_raw_info_pop.php?" + queryString + "&tableWidth=" + <?php echo $popwin_w?>;
  popwin(file,<?php echo $popwin_w?>,<?php echo $popwin_h?>);
}
function on_off_size_unit(){
  var theForm = document.sForm;
  if(theForm.file_size.checked == true){
    for(var i=0;i<theForm.size_unit.length; i++){
      if(theForm.size_unit[i].value == 'GB'){
        theForm.size_unit[i].checked = true;
      }else{
        theForm.size_unit[i].checked = false;
      }
    }
  }else{
    for(var i=0;i<theForm.size_unit.length; i++){
      theForm.size_unit[i].checked = false;
    }
  }
}
function switch_mode(this_obj){
  var theForm = document.sForm;
  var show_status = document.getElementById('show_status');
  var show_export = document.getElementById('show_export');
  var all_single_div = document.getElementById('all_single_div');
  var lable = document.getElementById('lable');
//alert(this_obj.value);
  var Mode = theForm.Mode.value;
  if(Mode == "To export files page"){
    show_status.style.display = "none";
    show_export.style.display = "block";
    all_single_div.style.display = "none";
    lable.innerHTML = "Raw File Status export";
    theForm.Mode.value = "To show files status page";
  }else{
    show_status.style.display = "block";
    show_export.style.display = "none";
    all_single_div.style.display = "block";
    lable.innerHTML = "Raw File Statistics";
    theForm.Mode.value = "To export files page";  
  }
}
function export_status_file(){
  var theForm = document.sForm;
  theForm.theaction.value = 'export_status_file';
  //var file_type = document.getElementById("file_type").value;
  theForm.file_type_new.value = document.getElementById("file_type").value;
  validate()
  theForm.submit();
}
</script>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
  <td bgcolor="#a4b0b7" valign="top" align="left" width="175">
   <?php include("./ms_storage_menu.inc.php");?>
   <br><br>
  </td>
  <td width="928" align=center valign=top>
    <DIV id='logo_div'></DIV><br><br>
    <table border=0 width=97% bgcolor="#ccccc" cellspacing="1" >
      <form name=sForm method=post action=<?php echo $PHP_SELF;?>>
      <input type=hidden name=myaction value=''>
      <input type=hidden name=frm_date1 value=''>
      <input type=hidden name=frm_date2 value=''>
      <input type=hidden name=open_dir_ID value=''>
      <input type=hidden name=theaction value=''>
      <tr>
        <td align=center>
          <font face="Arial" size="4" color="#000000"><b id='lable'>Raw File Statistics</b></font>
        </td>
      </tr>
      <tr bgcolor=white>
        <td align=center>
          <table border=0 width=100% cellpadding="1">
            <tr>
        	    <td width="150"><b>Machine Name</b>:</td>
        	    <td>
                <select name="tableName" onchange="initial();">
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
              <td>
              <DIV id='all_single_div' style="display:none">
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
              <td>
                <input type="checkbox" name="show_all" value="all" <?php echo ($show_all=="all")?"checked":""?> onclick="show_hide_sub();">
                Merge all Machines&nbsp;
                <input type="checkbox" name="show_single" value="single" <?php echo ($show_single=="single")?"checked":""?>onclick="show_hide_sub();">
                Separated Machines
              </td>
              </tr>
              </table>
              </DIV>  
              </td>              
            </tr>
            <tr>
            	<td><b>File Type</b>:</td>
            	<td colspan=2>
                <DIV id="file_type_div" style="display:block"></DIV>
              </td>
            </tr>
            <tr>
            	<td><b>Date From</b>: </td>
            	<td colspan=2><?php echo $DateSelector->setDate('frm_datefrom_', $frm_date1, false);?> &nbsp; <b>TO</b> &nbsp; <?php echo $DateSelector->setDate('frm_dateto_', $frm_date2, false);?></td>
            </tr>
                        
            <tr>
              <td colspan=3>              
<DIV id="show_status" style="display:block"> 
          <table border=0 width=100% cellpadding="0">
            <tr>
            	<td width="150"><b>Time Unit</b>: </td>
            	<td colspan=2>
                <input type="radio" name="interval" value="monthly" <?php echo ($interval=="monthly")?"checked":""?>>&nbsp;Month&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="interval" value="yearly" <?php echo ($interval=="yearly")?"checked":""?>>&nbsp;Year</td>
            </tr>
            <tr>
            	<td valign=top><b>Display Contents</b>: </td>
            	<td colspan=2>
                <input type="checkbox" name="num_file" value="num_files" <?php echo ($num_file=="num_files")?"checked":""?>>&nbsp;Number of Files&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
               
                <input type="checkbox" name="file_size" value="size" <?php echo ($file_size=="size")?"checked":""?> onclick="on_off_size_unit()">&nbsp;File Size&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="size_unit" value="GB" <?php echo ($size_unit=="GB")?"checked":""?>>&nbsp;GB&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="size_unit" value="MB" <?php echo ($size_unit=="MB")?"checked":""?>>&nbsp;MB
              </td>
            </tr>
            <tr>
            	<td><b>Display Style</b>: </td>
            	<td colspan=2><input type="checkbox" name="table_style" value="table" <?php echo ($table_style=="table")?"checked":""?>>&nbsp;Table&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="checkbox" name="bar_style" value="bar" <?php echo ($bar_style=="bar")?"checked":""?>>&nbsp;Bar&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="checkbox" name="line_style" value="line" <?php echo ($line_style=="line")?"checked":""?>>&nbsp;Line&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
             <DIV id='pare_style_div' style="display:none">   
                <!--input type="checkbox" name="pare_style" value="pare" <?php echo ($pare_style=="pare")?"checked":""?>-->
             </DIV>   
              </td>
            </tr>
            <tr align=right>
            	<td colspan=3>
                <!--input type="button" name="Fetch" value="Show" onClick='display_result(this.form)'-->
                <input type="button" name="Fetch" value="Show" onClick="javascript: display_result_win();">
              </td>
            </tr>
          </table>
</DIV>
<DIV id="show_export" style="display:none"> 
           <table border=0 width=100% cellpadding="0">
            <tr>
            	<td valign=top width="150"><b>Filter</b>: </td>
            	<td colspan=2>
                <input type="text" name="prefix" value="" size=30> <br>Enter file name prefixe. Separated by ',' for more than one prefix.
              </td>
            </tr>    
            <!--tr>
            	<td valign=top width="150"><b>File size unit</b>: </td>
            	<td colspan=2>
                <input type="radio" name="size_unit_ext" value="GB" <?php echo ($size_unit=="GB")?"checked":""?>>&nbsp;GB&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="size_unit_ext" value="MB" <?php echo ($size_unit=="MB")?"checked":""?>>&nbsp;MB
              </td>
            </tr-->
            <tr>
            	<td width="150"><b>Sort by</b>: </td>
            	<td colspan=2>
                <input type="radio" name="sort_by" value="machine" <?php echo ($sort_by=="machine")?"checked":""?>>&nbsp;Machine&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="radio" name="sort_by" value="project" <?php echo ($sort_by=="project")?"checked":""?>>&nbsp;Project</td>
            </tr>
            <tr align=right>
            	<td colspan=3>
                <input type="hidden" name="file_type_new" value="">
                <input type="button" name="report_file" value="export" onClick="javascript: export_status_file();">
              </td>
            </tr>
          </table>
</DIV>                     
            
            </td>
            </tr>
<?php if($USER->Type == 'Admin'){?>            
            <tr align=right>
            	<td colspan=3><hr>
                <input type="button" name="Mode" value="To export files page" onClick="javascript: switch_mode(this);">
              </td>
            </tr>
<?php }?>           
          </table>
        </td>
      </tr>
      </form>
    </table>
  </td>
  </tr>
</table>
<?php 
include("./ms_footer.php");
function log_Html(){
  global $tableName,$BACKUP_SOURCE_FOLDERS,$table_arr,$managerDB,$RAW_FILES;
  if($tableName){
    $logo = strtoupper($tableName);
    if(!is_file("./images/msLogo/" . $logo . "_logo.gif")) $logo = "default";
    echo "<table><tr><td>\n";
    echo "<img src='./images/msLogo/".$logo."_logo.gif' align=center>\n";
    echo "<font face='Arial' size='4' color='#660000'><b>$tableName</b></font>\n";
    echo "</td></tr></table>";
  }
  $file_type_arr = array();
//-------------------------------------------------------
  //echo "\$RAW_FILES=$RAW_FILES<br>";
  $RAW_FILES_arr = explode(',',$RAW_FILES);
  foreach($RAW_FILES_arr as $RAW_FILES_val){
    $file_type_arr[] = trim($RAW_FILES_val);
  }
//------------------------------------------------------------
  /*if(!$tableName){
    foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
      if(!in_array($baseTable, $table_arr)) continue;
      get_file_types($baseTable,$file_type_arr);
    }
  }else{
    if(in_array($tableName, $table_arr)){
      get_file_types($tableName,$file_type_arr);
    }  
  }*/  
  echo "@@**@@";
  echo "<table border=0 width=100% cellspacing=0 cellpadding=0><tr><td>\n";
  echo "<select id='file_type' name='file_type'>\n";
//  echo "<option value=''>-- select a file type --\n";
  $formal_type_arr = array();
  if($RAW_FILES){
    $formal_type_arr = explode(',',$RAW_FILES);
    for($i=0; $i<count($formal_type_arr); $i++){
      $formal_type_arr[$i] = trim($formal_type_arr[$i]);
    }
  }
  $options_arr = array();
  if($file_type_arr){
    foreach($file_type_arr as $typeVal){
      if(!$typeVal) continue;
      if(!in_array($typeVal, $formal_type_arr)) continue;
      array_push($options_arr, "<option value='$typeVal'>$typeVal\n");
    }
    if($options_arr){
      echo "<option value='all'>All types\n";
      foreach($options_arr as $value){
        echo $value;
      }
    }  
  }
  echo "</select>\n";
  echo "</td></tr></table>";
}

function get_file_types($baseTable,&$file_type_arr){
  global $managerDB;     
  $SQL = "SELECT `FileType` FROM $baseTable GROUP BY `FileType`";
  $tmpArr = $managerDB->fetchAll($SQL);
  foreach($tmpArr as $value){
    if(!in_array($value['FileType'], $file_type_arr)){
      array_push($file_type_arr, $value['FileType']);
    }
  }
}

function get_all_files_arr($frm_date1,$frm_date2,$tableName){
  global $managerDB;
  global $all_files_arr;
  global $prefix;
  global $sort_by;
  global $file_type_new;
  global $size_unit_ext;
  global $table_arr;
  global $PROHITSDB;
  global $msManager_link;
  global $HITS_DB;
  
  
  $msManager_link = $managerDB->link;
  $SQL = "SELECT `ID`, `Name`,`DBname` FROM `Projects`";
  $project_tmp_arr = $PROHITSDB->fetchAll($SQL);
  $project_ID_name_arr = array();
  $project_ID_DB_arr = array();
  foreach($project_tmp_arr as $project_tmp_val){
    $project_ID_name_arr[$project_tmp_val['ID']] = $project_tmp_val['Name'];
    $project_ID_DB_arr[$project_tmp_val['ID']] = $project_tmp_val['DBname'];;
  }

  $projectNAME_dbLink_arr = array();
  foreach($HITS_DB as $db_key => $db_name){
    $projectNAME_dbLink_arr[$db_key] = new mysqlDB($db_name);
  }
/*echo "<pre>";
print_r($project_ID_name_arr);
print_r($project_ID_DB_arr); 
print_r($projectNAME_dbLink_arr);
echo "</pre>";*/ 
//exit;
   
  $prefix_arr = explode(',',$prefix);
  for($i=0; $i<count($prefix_arr); $i++){
    $prefix_arr[$i] = trim($prefix_arr[$i]);
  }
  $prefix = implode(',',$prefix_arr);
  
  $prefix = str_replace(",", "|^", $prefix);
  $SQL = "SELECT `ID`,                
                 `FileName`,
                 `FolderID`,
                 `Size`, 
                 `Date`,
                 `ProhitsID`,
                 `ProjectID`
                 FROM $tableName 
                 WHERE `Date`>'$frm_date1'
                 AND `Date`<'$frm_date2'";
  if($file_type_new and $file_type_new !== 'all'){
     $SQL .= " AND FileType='$file_type_new'"; 
  }
  $SQL .= " ORDER BY `ProjectID`,`FileName`";
//echo "$SQL<br>";
  $tmpArr = $managerDB->fetchAll($SQL);
 
  $results_table = $tableName."SearchResults";  
  foreach($tmpArr as $tmpVal){
    
    if($prefix && preg_match("/^$prefix/i", $tmpVal['FileName'])) continue;
    $tmpVal['Machine'] = $tableName;
//---------------------------------------------------------------
    $ProjectID = $tmpVal['ProjectID'];
    $BandID = $tmpVal['ProhitsID'];
    $tmpVal['cooperName'] = '';
    $tmpVal['Institute'] = '';
//---------------------------------------------------------------
    if(isset($project_ID_name_arr[$tmpVal['ProjectID']])){
      $tmpVal['ProjectID'] = $project_ID_name_arr[$tmpVal['ProjectID']]." (".$tmpVal['ProjectID'].")";
    }else{
      $tmpVal['ProjectID'] = '';
    }  
    $tmpVal['FolderPath'] = getFilePath($tableName, $tmpVal['FolderID']);
     
    if(in_array($results_table, $table_arr)){
      $SQL = "SELECT `TaskID` 
              FROM $results_table 
              WHERE WellID='".$tmpVal['ID']."'
              LIMIT 1";
      $tmp_task_arr = $managerDB->fetch($SQL);
      if($tmp_task_arr && $tmp_task_arr['TaskID']){
        $tmpVal['TaskID'] = $tmp_task_arr['TaskID'];
      }else{
        $tmpVal['TaskID'] = '';
      }
    }else{
      $tmpVal['TaskID'] = '';
    }
//-----------------------------------------------
    if($BandID && $ProjectID){
      $dbName = $project_ID_DB_arr[$ProjectID];    
      $selectdeHitsDB = $projectNAME_dbLink_arr[$dbName];
      /*
      $SQL = "SELECT B.ID, B.RawFile, E.CollaboratorID 
            FROM Band B 
            LEFT JOIN Experiment E 
            ON B.ExpID = E.ID 
            WHERE B.ID = $BandID
            AND B.ProjectID = $ProjectID";     
      $dbName = $project_ID_DB_arr[$ProjectID];    
      $selectdeHitsDB = $projectNAME_dbLink_arr[$dbName];
      */
      //-----------------------------------------------------------
      $SQL = "SELECT ExpID 
            FROM Band 
            WHERE ID = $BandID
            AND ProjectID = $ProjectID";
      $Exp_arr = $selectdeHitsDB->fetch($SQL);
      if($Exp_arr){
        $expID = $Exp_arr['ExpID'];
        $SQL = "SELECT `CollaboratorID` FROM `Experiment` WHERE `ID` = '$expID'";
        $cooperID_arr = $selectdeHitsDB->fetch($SQL);
      }
      //---------------------------------------------------
      $cooperID = $cooperID_arr['CollaboratorID'];
      if($cooperID){
        $SQL = "SELECT `FirstName`, `LastName`, `Institute` 
                FROM `Collaborator` 
                WHERE `ID` = $cooperID";
        $cooper = $PROHITSDB->fetch($SQL);
        if($cooper){
          $tmpVal['cooperName'] = $cooper['FirstName']." ".$cooper['LastName'];
          $tmpVal['Institute'] = $cooper['Institute'];
        }
      }
    }          
//------------------------------------------------    
    $all_files_arr[] = $tmpVal;
  }
  if($sort_by == 'project'){
    usort($all_files_arr, "cmp_projects");
  }
}

function cmp_projects($a, $b){
  if($a['ProjectID'] == $b['ProjectID']) {
    return 0;
  }
  return ($a['ProjectID'] < $b['ProjectID']) ? -1 : 1;
}
?>
