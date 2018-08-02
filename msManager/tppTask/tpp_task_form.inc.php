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

/****************************************************
rerun a tpp task
http://storageIP/Prohits/msManager/tppTask/tpp_task_shell.php?tableName=LCQ&tppTaskID=1
*****************************************************/
require_once("./common_functions.inc.php"); 
include("./tppTask/tpp_task_shell_fun.inc.php");

$error_tppmsg = '';
$color_tpp = '#357b73';
$frm_tppstatus = 'TPP task has not been set.';
$frm_tppsetdate = '';
$frm_tppsetby = '';
$parameter_file_folder = "../TMP/search_paramters";
$tppResults =array();
$reload = 0;
$msg = '';

if(!isset($frm_tppTaskID)) $frm_tppTaskID = '';
if(!isset($frm_tppTaskName)) $frm_tppTaskName = '';
if(!isset($frm_tppSetName)) $frm_tppSetName = '';
if(!isset($frm_tppSetID)) $frm_tppSetID = 0;

$tableTppTasks = $table . "tppTasks";
$tableTppResults = $table . "tppResults";

$task_ID = $task_arr['ID']; 
$run_the_tpp = false;
if($myaction == 'save_tpp_task' and $perm_insert  and $task_ID){
  if(savedTask($task_ID, $frm_tppTaskName)){
    $error_tppmsg = "The TPP name exists for the search task.";
    $myaction = 'new_ttp_task';
  }else{
    $tppTaskID = saveTppTask($task_arr, $frm_tppSetID, $frm_tppTaskName, $frm_tppID_str, $frm_merge_str);
		if($tppTaskID){
			$run_the_tpp = true;
		}
  }
}else if($myaction == 'save_repeat_tpp_task' and $perm_insert  and $tppTaskID){
   
	$error_tppmsg = updateTppTask($tppTaskID, $frm_tppID_str, $frm_merge_str);
   
  
	if(!$error_tppmsg){
		$run_the_tpp = true;
	}else{
		$myaction = 'repeat';
	}
}
if($run_the_tpp and $tppTaskID){
  ///////////tpp process ///////////////////////////////////////////
  	//$file = "http://" .$storage_ip . dirname($_SERVER['PHP_SELF']) . "/autoSearch/auto_search_table_shell.php?tableName=".$table."&kill=". $tmp_processID."&SID=".session_id();
   	//$handle = fopen($file, "r");
  //$file = dirname($_SERVER['HTTP_REFERER']);
  $file = "http://" .$storage_ip . dirname($_SERVER['PHP_SELF']);
  $file .= "/tppTask/tpp_task_shell.php?tableName=".$table."&tppTaskID=".$tppTaskID;
  $handle = fopen($file, "r");
  while (!feof($handle)) {
    $tppmsg .= fgets($handle, 4096);
  }
  fclose($handle);
  $url = $PHP_SELF."?table=".$table."&frm_PlateID=".$frm_PlateID."&iniTaskID=".$task_ID."&tppTaskID=".$tppTaskID;
  $url .= "&tppmsg=".urlencode($tppmsg);
?>
<script language=javascript>
document.location = '<?php echo $url;?>';
</script>
<?php 
 exit;
//////////////////////////////////////////////////////////////////
}


//JP 2017/05/30--------------------------------------------------------------------------------------------------------
if($myaction == 'delete_tpphits' and $frm_delete_well_id and $frm_delete_searchEngine and $perm_delete){
  $project_ID_DBname = get_projectID_DBname_pair($prohitsDB);
  remove_TppProtein_and_TppPeptide($table, $frm_delete_well_id, $tppTaskID, $frm_delete_searchEngine);
}


if($myaction == 'delete_selsected_hits' and $frm_delete_well_id and $perm_delete){
  $project_ID_DBname = get_projectID_DBname_pair($prohitsDB);
  $deleted_arr = explode(',',$frm_delete_well_id);  
  foreach($deleted_arr as $deleted_value){
    $tmp_arr = explode('@@',$deleted_value);
    $frm_delete_well_id = $tmp_arr[0];
    $frm_delete_searchEngine = $tmp_arr[1];
    if(count($tmp_arr) == 2){
      remove_hits_and_peptide($table, $frm_delete_well_id, $task_ID, $frm_delete_searchEngine);
    }elseif(count($tmp_arr) == 3){
      $tppTaskID = $tmp_arr[2];
      remove_TppProtein_and_TppPeptide($table, $frm_delete_well_id, $tppTaskID, $frm_delete_searchEngine);
    }
  }
}
//---------------------------------------------------------------------------------------------------------------------



//get all tpp for the search task
$tppTasks = getTppTask($tableTppTasks, 0, $task_ID);
if($tppTasks and !$tppTaskID and $myaction != 'new_ttp_task') $tppTaskID = $tppTasks[0]['ID'];
if($tppTaskID){
  $tppResults = fetchAllTppResult($tableTppResults, $tppTaskID);
}

$tmp_pro_str = ($USER->Type == 'Admin')?"": $pro_access_ID_str;
$tppSet_arr = get_search_parameters('TPP', 0, $tmp_pro_str);
?>
<br>
<script language='javascript'>
var merge_count = 0;
function newTPP(isNew){
   var theForm = document.forms[0];
   if(isNew){ 
    theForm.myaction.value = "new_ttp_task";
   }else{
    theForm.myaction.value = "";
   }
   theForm.submit();
}
function changeTppID(theID, theaction){
   var theForm = document.forms[0];
   theForm.myaction.value = theaction
   theForm.tppTaskID.value = theID;
   theForm.submit();
}
function submitTPP(theForm){
  var str ='';
  sel = theForm.frm_tppboxes;
  for(var i=1;i<sel.length; i++){
    if(sel[i].checked){
      if(str) str +=',';
      str +=sel[i].value;
    }
  }
  if(str == '' && theForm.frm_merge_str.value == ''){
    if(!confirm("You haven't select any search results. Are you sure that you want to continue?")){
      return false;
    }
  }
	if(theForm.myaction.value == 'repeat'){
		theForm.myaction.value = "save_repeat_tpp_task";
	}else{
		if(theForm.frm_tppTaskName.value == ''){
	    alert("Please type TPP name.");
	    return false;
	  }else if(theForm.frm_tppSetID.options[theForm.frm_tppSetID.selectedIndex].value == ''){
	    alert('Please select a parameter set.');
	    return false;
	  }
		theForm.myaction.value = "save_tpp_task";
	}
  theForm.frm_tppID_str.value = str;
  theForm.submit();
  return true;
}
function editeTPPset(theForm){
 
  var opValue =theForm.frm_tppSetID.options[theForm.frm_tppSetID.selectedIndex].value;
  if(opValue){
    popwin('./ms_search_tpp.php?frm_setID='+opValue,840, 660);
  }else{
    alert("Please select a TPP parameter set.");
  }
}
function showTPPset(setID){
  popwin('./ms_search_tpp.php?frm_myaction=show&frm_setID='+setID,840, 660);
}
function mergeFiles(theForm){
  var str = '';
  var str_gpm = 'GPM';
  var str_mascot = 'Mascot';
  var num = 0;
  var str_fileID = '';
  var str_fileName = '';
  var engine = ''
  var box;
  var tds;
  var rsBody = document.getElementById('popTable');
  var trs =rsBody.getElementsByTagName('tr');
  for (var i = 0; i < trs.length; i++){
     box =trs[i].getElementsByTagName('input');
     if(box.length==1 && box[0].checked){
       tds = trs[i].getElementsByTagName('td');
       if(num > 0){
        str_fileID +="<br>";
        str_fileName += "<br>";
       }
       str_fileID +=tds[0].innerHTML;
       str_fileName +=tds[1].innerHTML;
       if(box[0].name == 'mergeboxes_GPM'){
          str_gpm += (str_gpm != 'GPM')? ',':"";
          str_gpm += box[0].value;
           
       }else if(box[0].name == 'mergeboxes_Mascot'){
          str_mascot += (str_mascot != 'Mascot')? ',':"";
          str_mascot += box[0].value;
        }
        num++;
     }
  }
  if(str_mascot != 'Mascot' && str_gpm != 'GPM'){
    alert('You only can select one type of search engine for a merging group!');
    return false;
  }else if(num < 2){
    alert('Please select more than one search results.');
    return false;
  }else{
    if(str_gpm != 'GPM') {
      str = str_gpm;
      engine = 'GPM';
    }else if(str_mascot != 'Mascot') {
      str = str_mascot;
      engine = 'Mascot';
    }
    if(!existMerging(theForm, str,'check')){
      theForm.frm_merge_str.value += (theForm.frm_merge_str.value)?(";" + str):str;
      uncheckboxes(theForm.mergeboxes_GPM);
      uncheckboxes(theForm.mergeboxes_Mascot);
			$('#tppMerge').slideUp(300);
      addTableRow( str_fileID, str_fileName, engine, str);
      merge_count++;
    }else{
      alert('The merging group has been set already');
      return false;
    }
  }
  return true;
}
function existMerging(theForm, str, removeIt){
  var merge_str =  theForm.frm_merge_str.value;
  var merge_arr = merge_str.split(";");
  if(str == '') return true;
  var new_str = '';
  for (var i = 0; i < merge_arr.length; i++){
    if(merge_arr[i] == str){
     if(removeIt !='remove'){
       return true;
     }
    }else{
      new_str += (new_str != '')? ';':"";
      new_str += merge_arr[i];
    }
  }
  if(removeIt =='remove'){
    theForm.frm_merge_str.value = new_str;
  }
  return false;
}
function uncheckboxes(field){
  if(field){
    for (var i = 0; i < field.length; i++){
      field[i].checked = false;
    }
  }
}
function checkboxes(field){
  if(field){
    for (var i = 0; i < field.length; i++){
      field[i].checked = true;
    }
  }
}
function checkAll(field, true_false){
  if(true_false){
      checkboxes(field);
  }else{
    uncheckboxes(field);
  }
}
function addTableRow(str_ID, str_Name, engine, box_value){
  var row = document.createElement("tr"); 
  row.id = "merge"+merge_count;
  row.appendChild(createCell(str_ID, '','','left'));
  row.appendChild(createCell(str_Name,'','','left'));
  row.appendChild(createCell('merged','','','center'));
  row.appendChild(createCell(engine,'','','center'));
  row.appendChild(createCell(box_value, 'checkbox','frm_merged_box', 'center'));
  document.getElementById("searchResultBody").appendChild(row);
}
function createCell(val, addcheckbox, checkboxName, alignTD){
  var cell = document.createElement('td');
  with(cell){
    style.background='white';
    style.textAlign=alignTD;
  }
  if(addcheckbox){
  	try{
  		obj = document.createElement("<input type='checkbox' value='"+ val +"' name='"+ checkboxName +"' checked onClick=\"remove_single_row('merge"+merge_count+"', this, this.form)\">");
  	}catch(err){
  		obj = document.createElement('input');
  		obj.setAttribute('type','checkbox');
  		obj.setAttribute('name',checkboxName);
  		obj.setAttribute('value',val);
  		obj.setAttribute('checked',true);
  		obj.setAttribute('onclick','javascript: remove_single_row("merge'+merge_count+'", this, this.form)');
       
  	}
    cell.appendChild(obj);
  }else{
    //var textNode = document.createTextNode(val);
    //cell.appendChild(textNode);<br>
    cell.innerHTML=val;
  }
   
  return cell;
}

function remove_single_row(rowID, box_obj, theForm) {
  if(confirm("Are you sure that you want to remove the merging group?")){
  	var tableBody = document.getElementById("searchResultBody");
  	var rowNote = document.getElementById(rowID);
  	tableBody.removeChild(rowNote);
  } 
  existMerging(theForm, box_obj.value, 'remove');
  return false;
}

</script>
<input type="hidden" name="frm_tppID_str" value=''>
<input type="hidden" name="frm_merge_str" value=''>
<font color="#FF0000"><?php echo $tppmsg;?></font>
<table border="0" bgcolor='<?php echo $color_tpp;?>' width=100% cellpadding="0" cellspacing="0" height=25>
  <tr>
    <td colspan="3" nowrap>
      <b><font size="2" color="white"> Set Search Results to Run TPP </font></b>
		 
    </td>
    <td bgcolor="white" align="right" width=90%>
    <a href="javascript: popwin('../doc/management_help.html#Analyze_results',782,600,'help');"><img src=./images/icon_help.gif border=0></a>
    <?php if(!$is_SWATH_file && $perm_insert && (!isset($search_type) || (isset($search_type) && $search_type != 'iProphet'))){?>
      <a href="javascript:newTPP(true)" class='button' title='new TPP task'>[&nbsp;New&nbsp;]</a>
    <?php }?>
    </td>
  </tr>
</table>
<DIV style="border: 1px solid <?php echo $color_tpp;?>;">
<table cellpadding="1" cellspacing="1" bgcolor='#bcbcbc' width=100%>
<tr>
	<td bgcolor="#bcbcbc" align=center><b>TPP ID</b></td>
	<td bgcolor="#bcbcbc" align=center><b>TPP Name</b></td>
	<td bgcolor="#bcbcbc" align=center><b>Parameter Set</b></td>
	<td bgcolor="#bcbcbc" align=center><b>Status</b></td>
	<td bgcolor="#bcbcbc" align=center><b>Set By</b></td>
</tr>
<?php 
 
$GPM_datapath = get_local_gpm_archive_path($table, $task_ID);
$log_file = $GPM_datapath."/" . $table . "/task". $task_ID. "/TPPtask.log";
$tpp_log_url = "../logs/log_view.php?log_file=$log_file&display=all";
  
$tppTasks_id_arr = array();

foreach($tppTasks as $tppRow){
  if(!in_array($tppRow['ID'], $tppTasks_id_arr)){
    array_push($tppTasks_id_arr, $tppRow['ID']);
  }
  $bgcolor = 'white';
  if($tppRow['ID'] == $tppTaskID) $bgcolor = '#dfdfdf';
  $the_setID = 0;
  foreach($tppSet_arr as $tmpSet){
    if($tppRow['ParamSetName'] == $tmpSet['Name']){
      $the_setID = $tmpSet['ID'];
      break;
    }
  }
	$repeat_str = '';
  if($tppRow['Status'] == 'Running'){
    if(!task_is_running($table, $tppRow['ID'], 'Yes')){
      $tppRow['Status'] = 'Stopped';
    }
  }
	if(($tppRow['Status'] == 'Finished' or $tppRow['Status'] == 'Stopped') and ($tppRow['UserID']==$USER->ID or $USER->Type=='Admin')){
		$repeat_str = " <a class='button' title='add files to run the TPP again' href=\"javascript: changeTppID('".$tppRow['ID']."', 'repeat')\"><img src='./images/icon_repeat.gif' border=0></a>";
	}
  $log_TPP_ID = $tppRow['ID'];
  if($SWATH_app == 'DIAUmpire'){
    $log_TPP_ID = '';
  }
  $ParamSetName = str_replace("__DEFAULT",'',  $tppRow['ParamSetName']);
?>
<tr>
	<td bgcolor='<?php echo $bgcolor;?>' align=center >
  <a href="javascript: changeTppID('<?php echo $tppRow['ID'];?>', 'changeTPP')"><?php echo $tppRow['ID'];?></a> 
  
  <a href='<?php echo $tpp_log_url."&tpp_TPPtaskID=".$log_TPP_ID;?>' class='button' title='tpp log detail' target=new>[&nbsp;log&nbsp;]</a>
  </td>
	<td bgcolor='<?php echo $bgcolor;?>' align=center><?php echo $tppRow['TaskName'];?></td>
	<td bgcolor='<?php echo $bgcolor;?>' align=center>
    <a  title="<?php echo pop_tppTask_parameters_div($tppRow)?>"><?php echo $ParamSetName;?>&nbsp;&nbsp;<img border="0" src="images/icon_view.gif" alt="Task detail"></a>
  </td>
	<td bgcolor='<?php echo $bgcolor;?>' align=center><?php echo $tppRow['Status'].$repeat_str;?></td>
	<td bgcolor='<?php echo $bgcolor;?>' align=center><?php echo getUserName($tppRow['UserID']);?></td>
</tr>
<?php 
}
if($myaction == 'new_ttp_task'){
?>
<tr >
	<td bgcolor=white>&nbsp;</td>
	<td bgcolor="#ded398" align=center><input type=text name=frm_tppTaskName value='<?php echo $frm_tppTaskName;?>'></td>
	<td bgcolor="#ded398" align=center>
  <select name=frm_tppSetID>
   <option value=''>-- &nbsp; --
<?php   
   foreach($tppSet_arr as $tmpSet){
      $selected = ($tmpSet['ID'] == $frm_tppSetID)?" selected":"";
      echo "<option value='" . $tmpSet['ID'] . "'$selected>".$tmpSet['Name']."\n";
   }
?>
  </select>
  <input type=button value='Edit' onClick=editeTPPset(this.form)>
  </td>
	<td bgcolor=white>&nbsp;</td>
	<td bgcolor=white>&nbsp;</td>
</tr> 
<?php 
}
if($myaction == 'new_ttp_task' or $myaction == 'repeat'){
?>
<tr bgcolor="white">
<?php if(!isset($search_type) || (isset($search_type) && $search_type != 'iProphet')){?>
 <td colspan=5 align=center height=30>
 Merges together search result files --- <a href="javascript:DropDown($('#tppMerge'))" class='button' title='click to select files'><b>[Select Files]</b></a>
 </td>
<?php }?>
</tr>
<tr bgcolor="white">
 <td colspan=5 align=center>
 <?php if($perm_insert){?>
 
  <input type="button" name="frm_runTPP" value="Run TPP" onClick=submitTPP(this.form);>
  <input type="reset">
  <input type="button" value="Cancel" onClick=newTPP(false)>
  <?php echo "<br><font color=\"#FF0000\">".$error_tppmsg."</font>";?>
 <?php }?>
 </td>
</tr>
<?php 
}?>
</table>
</DIV>

