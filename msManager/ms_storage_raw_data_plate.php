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

$year = '';
$month = '';
$day = '';
$order_by = 'FileType, ID';
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
$modified_by = '';
$hitDB_obj_arr = array();
$frm_format = '';
$frm_PROTEOWIZARD_par_str = '--64 --mz64 --inten64 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /doubleprecision';
$frm_SCIEX_par_str = ' -proteinpilot /doubleprecision';
$frm_NOmgf = '';


$disable_merge_file = 1;

$theaction = '';
//----for calendar.inc.php-----
$open_dir_ID = 0;
$info = '';

$dirTreelineArr = array();
$childArr = array();
$frm_file_type = 'RW';

include("./ms_permission.inc.php");
require("classes/Storage_class.php");
include("./common_functions.inc.php");
include ( "./is_dir_file.inc.php");

 
/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

if($frm_file_type == 'RW'){
  $RAW_FILES = "RAW, Wiff, dir";
}else{
  $RAW_FILES = '';
}

//user type is 'user' only can access his own data.
$ObjTable = new Storage($managerDB->link,$tableName);
$SQL = "SELECT WellID FROM ".$tableName."SearchResults LIMIT 1";
$is_auto = 1;
if(!mysqli_query($managerDB->link, $SQL)){
  $is_auto = 0;
}

if($crtPro){
  $where_project = "ProjectID='$crtPro'";
}else if($USER->Type == 'MSTech' or $USER->Type == 'Admin'){
  $where_project = 1;
}else{
  $where_project = "ProjectID in($pro_access_ID_str)";
}

$year_pass = $year;  //passed value from form or query string
$month_pass = $month; //they will be used in form "listform"
$day_pass = $day;

$ObjTable_tmp =  new Storage($managerDB->link,$tableName);
if(!isset($order_by)){
  $ObjTable_tmp->fetch($open_dir_ID);
  $orderBy = ($ObjTable_tmp->ProhitsID)?'FileName':'Date';
}else{
  $orderBy = $order_by;
}
$convertConFlag = 1;
$convertPathFlag = 1;
$convertStr = '';
$old = ini_set('default_socket_timeout', 1);

if(defined('RAW_CONVERTER_SERVER_PATH')){
  if(($rh = @fopen(RAW_CONVERTER_SERVER_PATH, 'rb')) === FALSE){ 
     $convertPathFlag = 0;
     $convertStr = RAW_CONVERTER_SERVER_PATH;
  }else{
    fclose($rh);
  }  
}else{
  $convertConFlag = 0;
}
$old = ini_set('default_socket_timeout', $old);
 
//no page to display 
$where_project = '';
$ObjTable->fetchall($orderBy,0,0, $where_project,'','','',$open_dir_ID );

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
  if(!isset($tmp_db_obj_arr[$rds[$i]['DBname']])){
    $tmp_db_obj_arr[$tmp_db_name] = new mysqlDB($HITS_DB[$tmp_db_name]);
  }
  $hitDB_obj_arr[$rds[$i]['ID']] = $tmp_db_obj_arr[$tmp_db_name];
}

$set_auto_search = false;
if($managerDB->exist("show tables like '".$table."SearchResults'")){
	$set_auto_search = true;
}
//-------------------------------------------------------------------------
$merged_file_id_arr = array();

$SQL = "SELECT `MergedID`,`MergedType`,`ID_str` FROM `MergedFiles` WHERE `TableName`='$tableName'";
if($results_obj = mysqli_query($managerDB->link, $SQL)){
  while($row = mysqli_fetch_array($results_obj)){
    $merged_file_id_arr[$row['MergedID']] = $row['ID_str'];
  }
}
/*print_r($merged_file_id_arr);exit;*/
$file_id_name_arr = array();
for($i=0; $i<$ObjTable->count; $i++){
  if($ObjTable->ID[$i]) $file_id_name_arr[$ObjTable->ID[$i]] = $ObjTable->FileName[$i];
}
/*echo "<pre>";
print_r($file_id_name_arr);
echo "</pre>";exit;*/
 
//-------------------------------------------------------------------------
include("./ms_header.php");


?>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language="javascript"> 
function backpage(theForm){
  theForm.action = 'ms_storage_raw_data.php';
  theForm.submit();
}
function download(FileID){
  var file ='<?php echo "http://".$storage_ip.dirname($_SERVER['PHP_SELF'])."/autoBackup/download_raw_file.php?SID=". session_id(). "&tableName=$tableName";?>' + '&ID=' + FileID;
  popwin(file,500,400)
}
function editProhitsID(){
  file = './ms_storage_pop_edit_prohits_id.php?tableName=' + '<?php echo $tableName;?>' + '&open_dir_ID=' + '<?php echo $open_dir_ID;?>';
  popwin(file,550,420);
}
function linkProhitsID(theID,tppR_table_name){
  file = './ms_storage_pop_link_prohits_id.php?tableName=' + '<?php echo $tableName;?>' + '&raw_file_ID=' + theID  + '&tppR_table_name=' + tppR_table_name;
  popwin(file,600,450,newPop);
}
function refreshWin(order){
  var theForm = document.listform;
  if(order){
    theForm.order_by.value = order;
  }
  theForm.submit();
}
function popProteowizard(theForm){
 var str = theForm.frm_PROTEOWIZARD_par_str.value;
 file = './ms_search_proteowizard.php?storagePop=1&frm_PROTEOWIZARD_par_str=' + str;
 popwin(file, 730, 660); 
}
function open_dir(theID, User, dirName){
  var theForm = document.listform;
  theForm.order_by.value='<?php echo $order_by;?>';
  theForm.start_point.value='<?php echo $start_point;?>';
  theForm.open_dir_ID.value=theID;
  theForm.action = 'ms_storage_raw_data_plate.php';
  theForm.submit();
}
function toggleBox(sel, szDivID) {
  <?php
  if($disable_merge_file) echo "return;";
  ?>
  var theForm = document.listform;
  var obj = document.getElementById(szDivID);
  var sel_val = sel.options[sel.selectedIndex].value;
  if(sel_val && sel_val == 'mgf'){
    if(<?php echo $convertConFlag?> == 0){
      alert("Constant 'RAW_CONVERTER_SERVER_PATH' is undefind!");
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
function show_hide_merging_div(event,div_id){
  var theForm = document.listform;
  if(theForm.merge_files.checked == true){
    showTip(event,div_id);
  }else{
    hideTip(div_id);
  }
}
function cancel_merging(div_id){
  var theForm = document.listform;
  if(theForm.merge_files.checked == true){
    theForm.merge_files.checked = false;
    hideTip(div_id);
  }
}

function change_file_type(){
  var theForm = document.listform;
  theForm.submit();
}

<?php if(isset($BACKUP_SOURCE_FOLDERS[$tableName]) and ($USER->Type == 'MSTech' or $USER->Type == 'Admin' or $perm_insert)){?>
function convert(){
  var queryString = '';
  var theForm = document.listform;
  var obj = theForm.frm_IDs;
  theForm.frm_ID_str.value = '';
  var counter = 0;
  if(typeof(obj) == 'object'){
    for (var e=0; e < obj.length; e++) {
      if(obj[e].checked){
        theForm.frm_ID_str.value +=obj[e].value + ",";
        counter++;
      }
    }
  }
  var is_replace = 2; 
  for (var i=0; i < theForm.frm_replace_existing.length; i++) {
    if (theForm.frm_replace_existing[i].checked){
      is_replace = theForm.frm_replace_existing[i].value;
    }
  }
   
  queryString +='<?php echo "SID=". session_id(). "&tableName=$tableName";?>';
  queryString +='&frm_ID_str=' + theForm.frm_ID_str.value;
  queryString +='&frm_format=' + theForm.frm_format.options[theForm.frm_format.selectedIndex].value;
  queryString +='&frm_replace_existing=' + is_replace;
  if(theForm.frm_NOmgf.checked){
    queryString +='&frm_NOmgf=' + theForm.frm_NOmgf.value;
  }
  queryString +='&frm_PROTEOWIZARD_par_str=' + theForm.frm_PROTEOWIZARD_par_str.value;
  if(theForm.merge_files.checked == true){
    if(!onlyAlphaNumerics(theForm.frm_merged_file_name.value, 4)){
      alert('Please type Only [+-_A-Za-z0-9] characters.');
  		return false;
  	}else{
      if(counter < 2){
        alert('Please select more than one files to merge');
  		  return false;
      }
    }
    queryString +='&frm_merged_file_name=' + theForm.frm_merged_file_name.value;
  }else{
    queryString +='&frm_merged_file_name=';
  }
   
  if(theForm.frm_ID_str.value != ''){
    var file ='<?php echo "http://".$storage_ip.dirname($_SERVER['PHP_SELF'])."/autoBackup/convert_raw_file.php?";?>' + queryString;
    popwin(file,500,400);
    hideTip('merge_file_div');
  }else{
    alert("you didn't select any raw files!");
  }
}
<?php }?>
</script>

  <tr>
  <td bgcolor="#a4b0b7" valign="top" align="left" width="175">
   <?php include("./ms_storage_menu.inc.php");?>
   <br><br>
  </td>
  <td width="928" align=center valign=top>
   <table border=0 width=97%>
   <form name=listform method=get action=<?php echo $PHP_SELF;?>>
   <input type=hidden name=year value='<?php echo $year_pass;?>'>
   <input type=hidden name=month value='<?php echo $month_pass;?>'>
   <input type=hidden name=day value='<?php echo $day_pass;?>'>
   <input type=hidden name=order_by value='<?php echo $order_by;?>'>
   <input type=hidden name=tableName value='<?php echo $tableName;?>'>
   <input type=hidden name=start_point value='<?php echo $start_point;?>'>
   <input type=hidden name=crtPro value='<?php echo $crtPro;?>'>
   <input type=hidden name=open_dir_ID value='<?php echo $open_dir_ID;?>'>
   <input type=hidden name=frm_ID_str>
   <input type="hidden" name=frm_IDs value="">
   
    <tr><td align=center>
    <?php 
    $logo = strtoupper($tableName);
    if(!is_file("./images/msLogo/" . $logo . "_logo.gif")) $logo = "default";
    ?>
    <img src='./images/msLogo/<?php echo $logo."_logo.gif";?>' align=center>
     <font face="Arial" size="+2" color="#660000"><b><?php echo $tableName;?> raw data</b></font>
     <hr width="100%" size="1" noshade>
    </td></tr>
    <tr><td>
    download <img src='images/icon_download.gif' border=0>
    <a href="javascript: popwin('../doc/ftp_transfers.html',702,600);"><img src=./images/icon_help.gif border=0></a>
    &nbsp; &nbsp;auto-link <img src='images/icon_link_g.gif' border=0> &nbsp;
    manual link<img src='images/icon_link_y.gif' border=0> &nbsp;
    no link<img src='images/icon_link.gif' border=0>
    <a href="javascript: popwin('../doc/management_help.html#Linking1',702,600);"><img src=./images/icon_help.gif border=0></a>
    </td></tr>
    <tr><td>
    <font face="Arial Black" size="3" color="#008000">
    <?php 
    $modified_by = '';
    $ObjTable_tmp =  new Storage($managerDB->link,$tableName);
    $ObjTable_tmp->fetch($open_dir_ID);
    if(is_numeric($ObjTable_tmp->User)){
      $SQL = "select Fname, Lname from User where ID='".$ObjTable_tmp->User."'";
      $record = $prohitsDB->fetch($SQL);
      if(count($record)){
        $modified_by = $record['Fname'] . " " . $record['Lname'];
      }
    }
    $tmp_pro_name = ($ObjTable_tmp->ProjectID)?$pro_access_ID_Names[$ObjTable_tmp->ProjectID]:'';
    echo "Plate/Folder Name: <font color=black>".$ObjTable_tmp->FileName."</font>
          <br>Folder Storage ID: <font color=#000000>". $ObjTable_tmp->ID . "</font>
          <br>Prohits Analyst Project ID: <font color=#000000>". $ObjTable_tmp->ProjectID;
     echo ($USER->Type == 'MSTech' or $USER->Type == 'Admin')? " <font size=2><a href=\"javascript: editProhitsID();\">[Edit]</a></font>":"";
     if($modified_by) echo " <font face=\"Arial\">modified by $modified_by</font>";
     echo "</font>
          <br>Project Name: <font color=#000000>". $tmp_pro_name . "</font>
          <br> Created on: <font color=#000000>". $ObjTable_tmp->Date."</font>
          <br> Total files: <font color=#000000>". $ObjTable->count."</font>";
          $extrPra = "&submitFolder=single&parProjectID=".$ObjTable_tmp->ProjectID."&parProhitsID=".$ObjTable_tmp->ProhitsID."&parFolderID=".$ObjTable_tmp->ID."&parFolderName=".$ObjTable_tmp->FileName;
    ?>
    </font>
    </td></tr>
    <?php if(($USER->Type == 'MSTech' or $USER->Type == 'Admin' or $perm_insert) and trim(@constant("RAW_CONVERTER_SERVER_PATH"))){?>
    <tr>
    <td>
      <span style="padding : 2px 8px;background-color:#656565;"><b><font color="#FFFFFF">Converter Parameters:</font></b></span>
      <!--a href="javascript:showhide('convertDiv', 'convertDiv_a')" id=convertDiv_a class='button' title='click to set parameters'>[&nbsp;Detail&nbsp;]</a-->
     
      <DIV ID=convertDiv style="border: 2px solid #656565; display: block">
      <table border=0 width=100% cellspacing="1" cellpadding="1">
      <tr bgcolor=#e0e0e0>
        <td>
        <table border=0 width=100% cellspacing="1" cellpadding="1">
        <tr bgcolor=#e0e0e0>
      	<td nowrap>
         Convert Selected file to:
         <select name=frm_format onChange="toggleBox(this,'div_m')">
         <option value='mzXML'<?php echo ($frm_format=='mzXML')?" selected":"";?>> mzXML
         <option value='mgf'<?php echo ($frm_format=='mgf')?" selected":"";?>> MGF
         <option value='mzML'<?php echo ($frm_format=='mzML')?" selected":"";?>> mzML
         <option value='mz5'<?php echo ($frm_format=='mz5')?" selected":"";?>> mz5
         </select>&nbsp;&nbsp;&nbsp;
         
         </td>
         <td align=left nowrap>
         <DIV ID='div_m' style="display:<?php echo ($frm_format=='mgf')?"block":"none"?>">   
				 <input type=checkbox name='merge_files' onClick="show_hide_merging_div(event,'merge_file_div')" title='Merging files'>
         Merge files
         </DIV>
          
         <input type="checkbox" name="frm_NOmgf" value="NOmgf" checked> Don't create mgf file if convert to mzML or mzXML.
      
         </td>
         <td width=80% align=left nowrap >
      	 <DIV ID='merge_file_div' STYLE="position: absolute; 
                              display: none;
                              border: black solid 1px;
                              width: 180px";>
          <table align="center" border="0" width=100% bgcolor="#e6e6cc">
            <tr bgcolor="#c1c184" height=25><td align=center><b>Type merged file name</b></td></tr>            
            <tr><td align=center><input type=text NAME="frm_merged_file_name" size="22" maxlength="30"></td></tr>
            <tr height=35><td align="center">
            <input type=button name='hide_div' VALUE=" Convert " onClick='convert()';">
            <input type=button name='hide_div' VALUE=" Cancel " onclick="javascript: cancel_merging('merge_file_div');">
            </td></tr>
          </table>   
         </DIV>&nbsp;&nbsp; &nbsp;
        </td>
        </tr>
        </table> 
        </td>
      </tr>
      <tr bgcolor="#cbcbcb">
      	<td align=left colspan=3>
          Don't convert if same parameter file exists:<input type=radio name='frm_replace_existing' value=0 checked>&nbsp; &nbsp;
				  Replace previous converted file:<input type=radio name='frm_replace_existing' value=1>&nbsp; &nbsp; 
          Make new:<input type=radio name='frm_replace_existing' value=2>
        </td>         
      </tr>
      <tr bgcolor=#e0e0e0>  
        <td colspan=3>
          Conversion Parameters: <input type="button" name="frm_edit_lcq" value="Edit" onClick="popProteowizard(this.form)"><br>
         <input size="90" name="frm_PROTEOWIZARD_par_str" value='<?php echo $frm_PROTEOWIZARD_par_str;?>'>
         
        </td>
      </tr>
      <tr bgcolor=#e0e0e0>
      	<td colspan=3 align="center"><input type=button value='Convert' onClick='convert()'>
         <a href="javascript: popwin('../doc/management_help.html#Data',702,600);"><img src=./images/icon_help.gif border=0></a>
        </td>         
      </tr>
     </table>
     </DIV>
     </td>
     </tr>
    <?php }?>
    <tr><td align=right>
    <?php if(isset($BACKUP_SOURCE_FOLDERS[$tableName]) and ($USER->Type == 'MSTech' or $USER->Type == 'Admin')){?>
     <input type=button value='Upload Raw Files' onClick='pop_upload_form()'> &nbsp;&nbsp;&nbsp; 
    <?php }?>
     <input type=button value="Back" onClick="backpage(this.form);">
    </td>
    </tr>
    <tr>
      <td>
      <table border=0 width=100% cellspacing=0 cellpadding=0>
        <tr>
        <td><?php echo create_dir_tree($open_dir_ID,$tableName);?></td>
        <td align=right valign=bottom><b>Display:</b>&nbsp;&nbsp;
          Raw & Wiff<input type=radio name='frm_file_type' value='RW' <?php echo ($frm_file_type=='RW'?'checked':'')?> onClick="change_file_type()">&nbsp;&nbsp; 
          All<input type=radio name='frm_file_type' value='All' <?php echo ($frm_file_type=='All'?'checked':'')?> onClick="change_file_type()">
        </td>
        </tr>
      </table>
      </td>
    </tr>
    <tr><td align=center>
       <table border=0 width=100% cellspacing=0 cellpadding=0>
       <tr><td bgcolor=#d2d2d2>
        <table border=0 width=100% cellspacing=1 cellpadding=2>
        <tr>
          <th width=7%><a href="javascript: refreshWin('<?php echo ($order_by == "ID")? 'ID desc':'ID';?>');">ID</a>
            <?php 
            if($order_by == "ID") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "ID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </th>
          <th width=30%><a href="javascript: refreshWin('<?php echo ($order_by == "FileName")? 'FileName desc':'FileName';?>');">File Name</a>
            <?php 
            if($order_by == "FileName") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "FileName desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </th>
          <th width=12%><a href="javascript: refreshWin('<?php echo ($order_by == "Size")? 'Size desc':'Size';?>');">Size<br>(Mb)</a>
            <?php 
            if($order_by == "Size") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "Size desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </th>
          </th>
          <th width=30%>Project<br>Bait<br>sample
          </th>
          <th width=12%><a href="javascript: refreshWin('<?php echo ($order_by == "Date")? 'Date desc':'Date';?>');">Date</a>
            <?php 
            if($order_by == "Date") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "Date desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </th>
					<th width=10%>Search<br>Task</th>
          <th width=10%>Download</th>
          <th width=10%>Convert</th>  
        <?php 
        //record list from msManager
        $tmp_sample_rd = array();
				$search_detail_folder_url = "<a  title='task detail' href='./ms_search_results_detail.php?table=$table&";
        
        for($i=0; $i < $ObjTable->count; $i++){ 
          $tmp_Size = '';
					$tmp_title = '';
          if($ObjTable->Size[$i]){
            if($ObjTable->FileType[$i] == 'dir'){
              $mg = $ObjTable->Size[$i]/1024;
            }else{
              $mg = $ObjTable->Size[$i]/1024/1024;
            }
            $tmp_Size = number_format(ceil($mg));
          }
          $bgcolor="#ffffff";
          echo  "\n<tr bgcolor=\"#ffffff\" onmousedown=\"highlightTR(this, 'click', '#CCFFCC', '#ffffff')\">";
          $ext = preg_replace("/^.+\./", "",$ObjTable->FileName[$i]);
          if(!preg_match("/RAW/i", $ext)){
            $ObjTable->FileName[$i] = preg_replace("/\.$ext$/", ".<b>$ext</b>",$ObjTable->FileName[$i]);
          }
          echo "<td >".$ObjTable->ID[$i]."</td>";
          if(array_key_exists($ObjTable->ID[$i], $merged_file_id_arr)){
            $merged_title = get_merged_file_detail($merged_file_id_arr[$ObjTable->ID[$i]]);
            echo "<td ><a  title='$merged_title'>".$ObjTable->FileName[$i]."</a></td>";
          }else if($ObjTable->RAW_ID[$i]){
            $tmp_title = "Converted from file ID: ".$ObjTable->RAW_ID[$i]."<br>;;";
            $tmp_title .= "Parameter:".$ObjTable->ConvertParameter[$i];
            echo "<td ><font color=#808080><a  title='$tmp_title'>".$ObjTable->FileName[$i]."</a></font></td>";
          }else{
            echo "<td >".$ObjTable->FileName[$i]."</td>";
          }
          
          echo "<td  align=right>".$tmp_Size."</td>
                <td  align=center>";
          if($ObjTable->FileType[$i] != 'dir' && $is_auto){
            if(is_numeric($ObjTable->User[$i]) && $ObjTable->User[$i]>0 && $ObjTable->ProhitsID[$i]){
              $tmp_icon = "icon_link_y.gif";
							$tmp_title = "Manual-linked<br>;;";
            }elseif($ObjTable->ProhitsID[$i]){
              $tmp_icon = "icon_link_g.gif";
							$tmp_title = "Auto-linked<br>;;";
            }else{
              $tmp_icon = "icon_link.gif";
							$tmp_title = 'Unlinked<br>;;Click the icon to link the file with sample';
            }
						if($ObjTable->ProhitsID[$i] and $ObjTable->ProjectID[$i]){
              $SQL = "select D.ID, D.BaitID,D.Location, B.GeneName from Band D, Bait B where D.BaitID=B.ID and D.ID='".$ObjTable->ProhitsID[$i]."'";
              $tmp_sample_rd = $hitDB_obj_arr[$ObjTable->ProjectID[$i]]->fetch($SQL);
              if(isset($projectName_arr[$ObjTable->ProjectID[$i]])){
                $tmp_title .= "Project: " . $projectName_arr[$ObjTable->ProjectID[$i]];
                if($tmp_sample_rd){
                  $tmp_title .= "<br>Bait: (".$tmp_sample_rd['BaitID'].")".$tmp_sample_rd['GeneName'];
                  $tmp_title .= "<br>Sample: ".$tmp_sample_rd['Location'];
                }else{
                  $tmp_title .= "<br><font color=red>broken link:sample ID ". $ObjTable->ProhitsID[$i]."</font>";
                }
              }else{
                 $tmp_title .= "<br><font color=red>broken link:project ID ". $ObjTable->ProjectID[$i]."</font>";
              }
            } 
            if($ext != 'scan' and $ext != 'SCAN' ){
              echo "<a  href=\"javascript: linkProhitsID('".$ObjTable->ID[$i]."','');\" title='$tmp_title'><img src=./images/$tmp_icon border=0></a>\n";
            }
            echo "</td>";
          }
          echo "<td>".$ObjTable->Date[$i]."</td>\n";
					$tmp_task_str = '';
					$tmp_folderID = 0;
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
					echo "<td  align=center>".$tmp_task_str."</td>\n";
					
          echo "<td  align=center>";
          $downloadFileName = $ObjTable_tmp->FileName."/".$ObjTable->FileName[$i];
          //$downloadFileName = escapeStr($downloadFileName);
          echo "<a  title='download' href=\"javascript: download('".$ObjTable->ID[$i]."');\">
                <img src='./images/icon_download.gif' border=0 alt=download></a>
               ";
          if($ObjTable->FileType[$i] == 'dir'){
            if($tableName != 'ReflexIV'){
              echo "<a href=\"javascript: open_dir('".$ObjTable->ID[$i]."');\">";
              echo "<img src='./images/icon_dir.gif' border=0 alt='plate detail'></a> ";
            }
          }
          echo "</td>\n";
          echo "<td  align=center>";
          if(strtoupper($ObjTable->FileType[$i]) == 'RAW' or strtoupper($ObjTable->FileType[$i]) == 'WIFF'){
            echo "<input type=checkbox name=frm_IDs value='".$ObjTable->ID[$i]."'>";
          }else{
            echo "&nbsp;";
          }
          echo "</td></tr>\n";
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
<script language="javascript">
function pop_upload_form(){ 
  var file = "http://<?php echo $storage_ip . dirname($_SERVER['PHP_SELF']);?>/autoBackup/ms_pop_upload_raw_files.php?tableName=<?php echo $tableName;?>&SID=<?php echo session_id();?>&status=first<?php echo $extrPra?>&UserID=<?php echo $AccessUserID;?>&UserType=<?php echo $USER->Type?>&folderLevel=2";
  window.open(file,'','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=700,height=500');
}
</script>
<?php 
include("./ms_footer.php");

function escapeStr($inStr){
  $inStr = preg_replace('/<.+?>/', '', $inStr);
  return addslashes($inStr);
}
function get_merged_file_detail($id_str){
  global $file_id_name_arr;
  $tmp_arr = explode(",",$id_str);
  $tmp_title = "<b>Merged files</b>;;";
  foreach($tmp_arr as $tmp_key){
    if(array_key_exists($tmp_key, $file_id_name_arr)){
      $tmp_file_name = $file_id_name_arr[$tmp_key];
      $tmp_title .= $tmp_file_name."<br>";
    }
  }
  return $tmp_title;
}            
?>
