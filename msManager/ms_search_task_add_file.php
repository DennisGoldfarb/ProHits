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

$initExpandedNodes = ',333,';
 
$table = '';
$frm_PlateID = '';
$myaction = ''; 
//$menu_color = '#8080ff';

$start_point = 0;
$order_by = '';
$where_project = '';
$autoAdd = '';
$js_arr_str = '';

$selected_ids = '';
$tmp_open_folders = '';
$open_folders = '';
$frm_file_type = 'RW';

define ("RESULTS_PER_PAGE", 20);
define ("MAX_PAGES", 5); //this is max page link to display

include("./ms_permission.inc.php");
include("../common/tree.class.php");
$RAW_FILES_O = $RAW_FILES;
/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

//echo "\$RAW_FILES=$RAW_FILES<br>";

if($frm_file_type == 'RW'){
  $RAW_FILES = "RAW, Wiff";
}else{
  $RAW_FILES = $RAW_FILES_O;
}

$tree = new dhtmlgoodies_tree($initExpandedNodes);

if(!$table) fatalError("There is no table name passed", __LINE__);
//page counter start ----------------------------------------------------------------
if($USER->Type == 'MSTech' or $USER->Type == 'Admin'){
  $where_project = 1;
}else{
  $where_project = "ProjectID in($pro_access_ID_str)";
}

//page counter start -------------------------------------------------------------
$use_javascript = "changePage";
$PAGE_COUNTER = new PageCounter($use_javascript);
$caption = "Folder";
$query_string = "";
 
 
if($order_by) $query_string .= "&order_by=".$order_by;
$SQL = "select T.ID from $table T  where ".  $where_project . " and T.FileType='dir' and T.FolderID=0";
$total_records = $managerDB->get_total($SQL);
$page_output = $PAGE_COUNTER->page_links($start_point, $total_records, RESULTS_PER_PAGE, MAX_PAGES, str_replace(' ','%20',$query_string)); 

if(!$order_by) $order_by = "ID desc";
if(!$start_point) $start_point = 0;
//get table records --------------------------------------------------------------
$SQL = "select ID, FileName, FolderID, ProhitsID, ProjectID, Date 
        from $table
        where " . $where_project . "  and FolderID=0 and FileType='dir' 
        order by $order_by 
        Limit $start_point, ". RESULTS_PER_PAGE;
$root_records = $managerDB->fetchAll($SQL);
$search = array('/[\']/', '/["]/');
$replace = array('\\\'', '\\"');
$RAW_FILES = preg_replace("/\s+/", "", $RAW_FILES);
$in_type = "'". preg_replace("/,/", "','", $RAW_FILES) . "','dir'";

 
$selected_idArr = explode(",", $selected_ids);

for($i=0; $i < count($root_records); $i++){
  $tmp_open_folders = $root_records[$i]['ID'].",";
	$plate_tmp_name = str_replace("\\", "\\\\", $root_records[$i]['FileName']); 
	$plate_tmp_name = preg_replace($search, $replace ,$plate_tmp_name);
	$plate_tmp_name = "(".$root_records[$i]['ID'].") ".$plate_tmp_name;
	$tree->addToArray($root_records[$i]['ID'],"$plate_tmp_name",$root_records[$i]['FolderID']);
	addSubFolder($root_records[$i]['ID']);
}

function addSubFolder($folderID){
 global $in_type, $table, $managerDB, $tree, $selected_ids, $selected_idArr;
 global $tmp_open_folders;
 global $open_folders;
 $SQL = "select ID, FileName, FileType, FolderID, ProhitsID, ProjectID, Date, Size 
        from $table
        where FolderID=" . $folderID . " and FileType in(".$in_type.") 
        order by ID desc"; 
 //echo $SQL;exit;
 $node_records = $managerDB->fetchAll($SQL);
	for($k=0; $k < count($node_records); $k++){
	  $tmp_name = $node_records[$k]['FileName'];
		$checked = "";
		if($node_records[$k]['FileType'] !='dir'){
		  
			if(in_array($node_records[$k]['ID'], $selected_idArr)){
			  $open_folders .= $tmp_open_folders;
				$tmp_open_folders = "";
				$checked = " checked";
				$selected_ids = str_replace($node_records[$k]['ID'].",","", $selected_ids);
			}
	 		$tmp_img = './images/file.gif';
			$tmp_size = number_format(ceil($node_records[$k]['Size']/1024)) . "(KB)";
			$tmp_date = substr($node_records[$k]['Date'],0, 10);
			$tmp_name .= " <strong class=sizeText>". $tmp_size . " ". $tmp_date."</strong>";
	 	}else{
			$tmp_open_folders .= $node_records[$k]['ID'] .",";
		}
	  $tmp_img = ($node_records[$k]['FileType'] !='dir')? './images/file.gif':'';
	 	$tree->addToArray($node_records[$k]['ID'], $tmp_name, $node_records[$k]['FolderID'], $tmp_img, $checked);
	 	if($node_records[$k]['FileType'] =='dir'){
			addSubFolder($node_records[$k]['ID']);
		}
 	}
}
$tree->setExpandedNodes($open_folders);

?>
<html>
<head>
	<title>select raw files</title>
  <link rel="stylesheet" type="text/css" href="./ms_style.css">
	<STYLE type=text/css>
	#gdir {
	width:500px;
	height:540px;
	overflow:auto;
	border: #000000 solid 1px;
  
	font-family: tahoma;
	font-size:12px;
	position:relative;
	background-color : #ffffff;
  padding: 5px 0px 0px 5px; 
  margin: 0px 0px 0px 0px;
	}
	.sizeText {
	  color: black;
		background-color: #d1d1d1;
		font-family: arial;
		font-size:10px;
	}
	
	</STYLE>
	<?php 
	$tree->printJavascript();
	?>
</head>
<body bgcolor="#ffffff">

<center>
<script language='javascript'>
function validateForm(theForm){
  var checkboxes = theForm.rawbox;
	var ids = "";
  if(checkboxes.length == undefined){
    if(checkboxes.checked == true){
      ids = checkboxes.value;
    }
  }else{  
    for(var no=0;no<checkboxes.length;no++){
  		if(checkboxes[no].checked){
  			ids = ids + checkboxes[no].value + ',';
  		}
  	}
  	ids += theForm.selected_ids.value;
  }    
	if(ids == ''){
	  alert("You haven't selected any raw fies!");
		return false;
	}else{
	  if(opener.document.form_task){
      opener.document.form_task.frm_file_id_str.value = ids;
      //opener.document.form_task.myaction.value = theForm.opener_myaction.value;
      //alert(ids);return
      opener.refreshWin();
			theForm.submit();
      window.close();
    }
	}
}
function clearAll(obj){
	obj.form.selected_ids.value = '';
	var checkboxes = obj.form.rawbox;
	for(var no=0;no<checkboxes.length;no++){
		checkboxes[no].checked = false;
	}	 
}
function changePage(startPoint){
	var theForm = document.selform;
	var checkboxes = theForm.rawbox;
	var ids = '';
	for(var no=0;no<checkboxes.length;no++){
		if(checkboxes[no].checked){
			ids = ids + checkboxes[no].value + ',';
		}
	}
	theForm.selected_ids.value += ids;
	theForm.myaction.value = 'changePage';
	theForm.start_point.value = startPoint;
	theForm.submit();	 
}
function sortFolder(order_by){
	var theForm = document.selform;
	if(order_by == 'ID desc'){
		theForm.order_by.value = 'ID';
	}else{
		theForm.order_by.value = 'ID desc';
	}
	changePage(0);
}
function closeWin(){
	window.close();
}
function change_file_type(){
  changePage(<?php echo $start_point?>);;
}
</script>
 
<form name=selform action="<?php echo $PHP_SELF;?>" method="post">
<input type="hidden" name=selected_ids value='<?php echo $selected_ids;?>'>
<input type="hidden" name="table" value="<?php echo $table;?>">
<input type="hidden" name="myaction" value="getFiles">
<input type="hidden" name="order_by" value="<?php echo $order_by;?>">
<input type="hidden" name="start_point" value="">
<input type="hidden" name="opener_myaction" value="<?php echo $opener_myaction;?>">

<div style="border: black solid 1px;width:560px; background-color:#3788b9; border-radius: 0.6em;">
<table align=center border=0 cellpadding="2" cellspacing="5">
	<tr>
		<td>
    <span class="pop_header_text" style="color:#ffffff">Select <?php echo $table;?> Raw files</span>
    <br>
    <hr width="100%" size="1" noshade style="color:#ffffff;">
		<?php 
		 
		echo "<font face='Arial' size='-1' color=>$RAW_FILES files can be selected. A SWATH file will be converted to mzXML. Other type of raw file will be converted to mgf file for Mascot, mzML for XTandem.</font><br>";
		echo $page_output;
		?>
    </td>
  </tr>
   
  <tr>
		<td align=center>
		<table>
     <tr><td>
    <b>Display:</b>&nbsp;&nbsp;&nbsp;
      Raw & Wiff<input type=radio name='frm_file_type' value='RW' <?php echo ($frm_file_type=='RW')?'checked':''?> onClick="change_file_type()">&nbsp;
      All<input type=radio name='frm_file_type' value='All' <?php echo ($frm_file_type=='All')?'checked':''?> onClick="change_file_type()">
     </td>
     <td>
    Sort Folder&nbsp;&nbsp; 
			<a href="javascript: sortFolder('<?php echo ($order_by)?$order_by:'';?>')">
			<img src="images/icon_order_<?php echo ($order_by=='ID desc')?'down2':'up2';?>.gif" border=0>
			</a> &nbsp; &nbsp; 
     </td>
     <tr>
     <td colspan=2> 
		<?php 
		echo "\n<DIV id=gdir>\n"; 
		echo "<IMG src='./images/icon_harddrive.gif'> $table\n";
		$tree->drawTree();
		echo "</DIV>\n";
		?>
		</td>
		</tr>
		<tr>
		<td align=right colspan=2> 
		  
			<input type=button onClick=clearAll(this) value='Clear All'> &nbsp; &nbsp;
			<input type=button onClick=closeWin() value='Cancel'> &nbsp; &nbsp;
			<input type=button value='Submit' onClick="validateForm(this.form)">
		</td>
		</tr>
	  </table>
	  </td>
 </tr>
</table>
</div>
</form>
</center>
</body>
</html>
	
	