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
$frm_SearchEngine = '';
$frm_machine = array();
$tb_color = '#969696';
$SearchEngine = '';
$titleBarW = '90%';
$theaction = '';
$frm_taskID = array();
$selected_id_str = '';
$selected_group_id = '';
$itemType = 'Band';
$frm_groups = 'Band';
$frm_note_id = '';
$saint_bait_name_str = '';
$contrl_id_str = '';
$all_project = 0;
$machine_index = 0;
$taskID_str = '';


require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");
ini_set("memory_limit","-1");

//echo "<pre>";
//print_r($request_arr);
//echo "\$frm_machine=";
//print_r($frm_machine);
//print_r($_SESSION);
//echo "</pre>";

//echo "\$machine_index=$machine_index<br>";
if(array_key_exists($machine_index, $frm_machine)){
  $frm_machine[$machine_index] = '';
}

/*echo "<pre>";
echo "\$frm_machine=";
print_r($frm_machine);
echo "</pre>";*/

if($theaction == "change_searchEnine" || $theaction == "change_machine") $frm_taskID = array();
$prohitsManagerDB = new mysqlDB(MANAGER_DB);
$mDBname = MANAGER_DB;

if($theaction == 'toggle_group'){
  toggle_group($frm_groups);
  exit;
}elseif($theaction == 'creatList'){
	create_source_element_list($taskID_str,$selectedList_str,$SearchEngine,$frm_note_id,$frm_groups);
	exit;
}

//-----------------------------------------------------------------------------------------------------------------------
if(!$all_project){
  $permitted_projectIDs_str = $AccessProjectID;  
}else{
  $SQL = "SELECT `ProjectID` FROM `ProPermission` WHERE `UserID`='$AccessUserID'";
  $tmp_p_arr = $PROHITSDB->fetch($SQL);
  $permitted_projectIDs_arr = $PROHITSDB->fetchAll($SQL);
  $permitted_projectIDs_str = '';
  foreach($permitted_projectIDs_arr as $permitted_projectIDs_val){
    if($permitted_projectIDs_str) $permitted_projectIDs_str .= ",";
    $permitted_projectIDs_str .= $permitted_projectIDs_val['ProjectID'];
  }
}
//echo "\$permitted_projectIDs_str=$permitted_projectIDs_str<br>";
//exit;
//---------------------------------------------------------------------------------------------------------------------

  
$SQL = "SHOW TABLES FROM $mDBname";
//echo $SQL."<br>";  
$result = mysqli_query($prohitsManagerDB->link, $SQL);

if(!$result){
   echo "DB Error, could not list tables\n";
   echo 'MySQL Error: ' . mysql_error();
   exit;
}
$SearchEngine_lable_arr = array('GPM'=>'XTandem','Mascot'=>'Mascot','COMET'=>'Comet','MSGFPL'=>'MSGFPL','iProphet'=>'iProphet');
$SearchEngine_arr_tree = array();

$task_prop_arr = array();
$machines_tasks_arr = array();
$has_default_project_arr = array();

if($result){
  while($row = mysqli_fetch_row($result)){
    if(preg_match('/(.+)SearchTasks$/', $row[0], $matches)){
      $task_ID_arr = array();
      $machine_name = $matches[1];   
      $SQL = "SELECT `ID`,`SearchEngines`,`TaskName`,`ProjectID` FROM ".$row[0]." WHERE Status='Finished' AND `ProjectID` IN($permitted_projectIDs_str)";
      $tmp_task_arr = $prohitsManagerDB->fetchAll($SQL);    
      if($tmp_task_arr){
        foreach($tmp_task_arr as $tmp_task_val){
          if(!strstr($tmp_task_val['SearchEngines'], 'DIAUmpire=')) continue;
          $tmp_arr = explode(";",$tmp_task_val['SearchEngines']);
          
          $db_name = '';
          //get db name first
          foreach($tmp_arr as $tmp_val){
            if(strpos($tmp_val, "Database=")===0){
              $db_name = str_replace("Database=",'', $tmp_val);
            }
          }     
          foreach($tmp_arr as $tmp_val){
            $tmp_arr2 = explode("=",$tmp_val);
            $SearchEngine_name = $tmp_arr2[0];
            if(!$db_name){
              if(count($tmp_arr2) >1){
                $db_name = $tmp_arr2[1];
              }
            }
            if(array_key_exists($SearchEngine_name, $SearchEngine_lable_arr)){
              if(!array_key_exists($SearchEngine_name, $SearchEngine_arr_tree)){
                $SearchEngine_arr_tree[$SearchEngine_name] = array();
              }
              if(!array_key_exists($machine_name, $SearchEngine_arr_tree[$SearchEngine_name])){
                $SearchEngine_arr_tree[$SearchEngine_name][$machine_name] = array();
              }            
              $SearchEngine_arr_tree[$SearchEngine_name][$machine_name][] = $tmp_task_val['ID'].'**'.$tmp_task_val['TaskName'].'**'.$db_name.'**'.$tmp_task_val['ProjectID'];
              if(!in_array($tmp_task_val['ID'], $task_ID_arr)){
                $task_ID_arr[] = $tmp_task_val['ID'];
                $task_prop_arr[$tmp_task_val['ID']] = $tmp_task_val['ID'].'**'.$tmp_task_val['TaskName'].'**'.$db_name.'**'.$tmp_task_val['ProjectID'];
              }
            }
          }
          $machines_tasks_arr[$machine_name] = $task_ID_arr;
          $machines_task_prop_arr[$machine_name] = $task_prop_arr;
        }
      }
    }
  }
}

foreach($machines_tasks_arr as $key => $val){
  $task_list_arr = array();
  $task_ID_str = implode(",", $val);
  $tmp_tpp_task_arr = array();
  if($task_ID_str){
    $SQL = "SELECT `ID`, `SearchTaskID`, `Status`, `UserID`, `ProjectID` FROM ".$key."tppTasks WHERE `SearchTaskID` IN ($task_ID_str)";
    $tmp_tpp_task_arr = $prohitsManagerDB->fetchAll($SQL);
    
    $task_tpp_ID_arr = array();
    $task_tpp_ID_str = '';
    foreach($tmp_tpp_task_arr as $tmp_tpp_task_val){
      $task_tpp_ID_arr[$tmp_tpp_task_val['ID']] = $tmp_tpp_task_val['SearchTaskID'];
      if($task_tpp_ID_str) $task_tpp_ID_str .= ',';
      $task_tpp_ID_str .= $tmp_tpp_task_val['ID'];
    }
    if($task_tpp_ID_str){
      $SQL = "SELECT `TppTaskID`, 
                     `SearchEngine`
              FROM ".$key."tppResults 
              WHERE `TppTaskID` IN($task_tpp_ID_str)
              AND `SearchEngine`='iProphet'
              AND `Date` IS NOT NULL
              GROUP BY `TppTaskID`";
      $tmp_tpp_resl_arr = $prohitsManagerDB->fetchAll($SQL);
      
      if($tmp_tpp_resl_arr){
        foreach($tmp_tpp_resl_arr as $tmp_tpp_resl_val){
          $tmp_task_ID = $task_tpp_ID_arr[$tmp_tpp_resl_val['TppTaskID']];
          $SearchEngine_arr_tree['iProphet'][$key][] = $machines_task_prop_arr[$key][$tmp_task_ID];
        }
      }
    }
  }
}

//$AccessProjectID = 5;
foreach($SearchEngine_arr_tree as $Engine => $machines_arr){
  $Engine_no_AccessProjectID = 1;
  foreach($machines_arr as $machine => $tasks_arr){
    $no_AccessProjectID_arr = array();
    $new_task_arr = array();
    foreach($tasks_arr as $task){
      $task_atr = explode('**',$task);
      $tmp_projectID = end($task_atr);
      if($tmp_projectID == $AccessProjectID){
        $new_task_arr[] = $task;
        if($Engine_no_AccessProjectID){
          $Engine_no_AccessProjectID = 0;
        }  
      }else{
        $no_AccessProjectID_arr[] = $task;
      }
    }
    $new_task_arr = array_merge($new_task_arr, $no_AccessProjectID_arr);
    $SearchEngine_arr_tree[$Engine][$machine] = $new_task_arr;
  }
  if($Engine_no_AccessProjectID){
    foreach($machines_arr as $machine => $tasks_arr){
      $SearchEngine_arr_tree[$Engine][$machine] = array();
    }
  }
}

//echo "<pre>";
//print_r($SearchEngine_arr_tree);
//echo "\$machines_tasks_arr=";
//print_r($machines_tasks_arr);
//echo "\$machines_task_prop_arr=";
//print_r($machines_task_prop_arr);
//echo "</pre>";
//exit;

$no_task_flag = 1;
if($frm_SearchEngine && $frm_machine && isset($SearchEngine_arr_tree[$frm_SearchEngine]) && $SearchEngine_arr_tree[$frm_SearchEngine]){
  $machine_arr = $SearchEngine_arr_tree[$frm_SearchEngine];
  $no_task_flag = 0; 
}elseif(isset($SearchEngine_arr_tree['GPM']) && $SearchEngine_arr_tree['GPM']){
  $frm_SearchEngine = 'GPM';
  $machine_arr = $SearchEngine_arr_tree['GPM'];
  $no_task_flag = 0;
}else{
  $tmp_arr = each($SearchEngine_arr_tree);
  $frm_SearchEngine = $tmp_arr['key'];
  $machine_arr = $tmp_arr['val'];
  $no_task_flag = 0;
}

require("site_header.php");

$bg_tb_header = '#7eb48e';
$tb_color = '#e3e3e3';
$tb_color2 = '#d1e7db';
$tb_color3 = '#e7e7cf';
                                 
$machine_arr_tmp = array();                                                                         
foreach($machine_arr as $machine_key => $machine_val){
  $machine_arr_tmp[] = $machine_key;
} 

?>
<link rel="stylesheet" type="text/css" href="./colorPicker_style.css">
<STYLE type="text/css">
.sss { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt; white-space: nowrap}
.sss2 { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt; font-weight : bold; white-space: nowrap}
.sss3 {	HEIGHT: 339px }
TD { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
</STYLE>
<SCRIPT language=Javascript  src="colorPicker.js"></SCRIPT>
<script language="JavaScript" type="text/javascript">
$(document).ready(function(){
  document.getElementById('process').style.display = 'none';
});
function inset_new_option(parent, newOption,nextOption){
	try {
    parent.add(newOption, nextOption); // standards compliant; doesn't work in IE
  }
  catch(ex) {
    parent.add(newOption, nextOption.index); // IE only
  }
}

function append_new_option(parent, newOption){
	try {
    parent.add(newOption, null); // standards compliant; doesn't work in IE
  }
  catch(ex) {
    parent.add(newOption); // IE only
  }
}

function add_option_to_selected(){
  var sourceList = document.getElementById('frm_sourceList');
  var selectedList = document.getElementById('frm_selected_list');
  var theForm = document.form_comparison;
	var selectedCounter = 0;
	var currentIndex = 0;
  var is_alerted = false;
  var file_is_alerted = false;
  for(var i=sourceList.length-1; i>0; i--){
//alert(sourceList.options[i].value);
    var tmp_var = sourceList.options[i].value.split("**");
//alert(sourceList.options[i].value); 
    if(sourceList.options[i].selected && tmp_var.length == 2){ 
      sourceList.options[i].selected = false;
      if(!is_alerted && tmp_var[1] == 1){
        is_alerted = true;
        alert("The options with color Olive have no TPP results.")
        continue;
      }else{
        file_is_alerted = true;
        alert("The options with color Orange is being searched on other task"+ tmp_var[1] +".");
        continue;
      }    
    }
    if(sourceList.options[i].selected){
      if(sourceList.options[i].value == '') continue;
			selectedCounter++;
      var optionNew = document.createElement('option');
    	optionNew.id = sourceList.options[i].id;
      optionNew.text = sourceList.options[i].text;
      optionNew.value = sourceList.options[i].value;
			sourceList.remove(i);
  		if(selectedCounter == 1){
  			append_new_option(selectedList, optionNew);
  			currentIndex = selectedList.length-1;
  		}else{
  			inset_new_option(selectedList, optionNew, selectedList.options[currentIndex]);
  		}
		}	
  }
}

function remove_option_from_selected(){
  var selectedList = document.getElementById('frm_selected_list');
  var sourceList = document.getElementById('frm_sourceList');
	var theForm = document.form_comparison;
  var selectedCounter = 0;
	var currentIndex = 0;	
  for(var i=selectedList.length-1; i>0; i--){
    if(selectedList.options[i].selected){
      if(selectedList.options[i].id == '') continue;
      selectedCounter++;
      var optionNew = document.createElement('option');
    	optionNew.id = selectedList.options[i].id;
      optionNew.text = selectedList.options[i].text;
      optionNew.value = selectedList.options[i].value;
      selectedList.remove(i);
      if(selectedCounter == 1){
  			append_new_option(sourceList, optionNew);
  			currentIndex = sourceList.length-1;
  		}else{
  			inset_new_option(sourceList, optionNew, sourceList.options[currentIndex]);
  		}
    }
  }
}

function send_files(){
  var theForm = document.form_comparison;
  selectedList = document.getElementById('frm_selected_list');
  var selectedList_str = '';
  var selectedBand_str = '';
  var rawID_arr = [];
  var du_rawID_str = '';
  var tmp_projectID_arr = [];
  for(var i=0; i<selectedList.length; i++){
    if(selectedList.options[i].id == '') continue;
    if(selectedList_str) selectedList_str += ',';
    if(selectedBand_str) selectedBand_str += ',';
//alert(selectedList.options[i].value);
    var tmp_arr = selectedList.options[i].value.split('|');    
    tmp_projectID_arr.push(tmp_arr[3]);
    //selectedList_str += tmp_arr[1] + '|' + selectedList.options[i].id + '|' + tmp_arr[0];
    selectedList_str += tmp_arr[0] + '|' + tmp_arr[2] + '|' + selectedList.options[i].id + '|' + tmp_arr[1];
    
    var flag = 0;
    for(var k=0; k<rawID_arr.length; k++){
      if(rawID_arr[k] == selectedList.options[i].id){
        flag = 1;
        break;
      }
    }
    if(flag == 1){
      if(du_rawID_str) du_rawID_str += ',';
      du_rawID_str += selectedList.options[i].id;
    }else{
      rawID_arr.push(selectedList.options[i].id);
    }   
    selectedBand_str += tmp_arr[1];
  }
  var has_current_project = 0;
  for(var k=0; k<tmp_projectID_arr.length; k++){
    if(tmp_projectID_arr[k] == <?php echo $AccessProjectID?>){
      has_current_project = 1;
      break;
    }
  }
  if(!has_current_project){
    alert("Please select a current project's raw file!");
    return;
  }  
  if(du_rawID_str){
    alert('Please remove the duplicated raw files!');
    return;
  }
  
  if(!selectedList_str){
    alert("Please select files.");
    return;
  }
  theForm.frm_selected_list_str.value = selectedList_str;
  theForm.frm_selected_sample_str.value = selectedBand_str;
    
  var frm_SearchEngine = theForm.frm_SearchEngine.value;  
  var p_str = "frm_selected_list_str="+selectedList_str+"&frm_selected_sample_str="+selectedBand_str+"&frm_SearchEngine="+frm_SearchEngine;
  //file = 'DIAUmpire_Quant_run_prepare.php?'+p_str;
  file = 'loading.html';
  newWin = window.open(file,"subWin",'toolbar=1,location=0,directories=0,status=0,menubar=1,scrollbars=1,resizable=1,width=800,height=800');
  newWin.focus();
  theForm.target = 'subWin';
  theForm.action = "DIAUmpire_Quant_run_prepare_dev.php";
  theForm.submit();
}

var DB_id = '';

function startRequest(is_machine){
  var theForm = document.form_comparison;
  var SearchEngine = '<?php echo $frm_SearchEngine?>';  
  //----------------------------------------------------
  var frm_note_id = '';
  if(typeof theForm.frm_note_id != "undefined"){
    frm_note_id = theForm.frm_note_id.value;
  }
  //-----------------------------------------------------
  var frm_groups = '';
  var groups = '';
  if(typeof theForm.frm_groups != "undefined"){
    if(typeof theForm.frm_groups.length == "undefined"){
      frm_groups = theForm.frm_groups.value;
    }else{
      groups = theForm.frm_groups;
      for(var j=0; j<groups.length; j++){
        if(groups[j].checked){
          frm_groups = groups[j].value;
        }
      }
    }
  }
  //---------------------------------------------------------
  
  var taskID_arr = theForm.frm_taskID;
  
  var taskID_str = '';
  if(typeof theForm.frm_taskID != "undefined"){
    if(typeof taskID_arr.length == "undefined"){
      if(taskID_arr.checked){
        taskID_str = taskID_arr.value;
      }  
    }else{
      for(var i=0; i<taskID_arr.length; i++){
        if(taskID_arr[i].checked){
          if(!DB_id){
            DB_id = taskID_arr[i].id;
          }else{
            if(DB_id != taskID_arr[i].id){
              alert("The task DB '"+taskID_arr[i].id+"' is not the same as selected task DB '"+DB_id+"'");
              taskID_arr[i].checked = false;
              continue;
            }  
          }
          if(taskID_str) taskID_str += ',';
          taskID_str += taskID_arr[i].value;
        }  
      }
    }
  }  
  if(!taskID_str) DB_id = '';
  if(is_machine == 'yes'){
    var selectedList_str = document.getElementById('selectedList_str').value;
  }else{
    var selectedList = document.getElementById('frm_selected_list');
    var selectedList_str = '';
    for(var i=0; i<selectedList.length; i++){
        if(selectedList.options[i].id == '') continue;
        if(selectedList_str) selectedList_str += ',';
        selectedList_str += selectedList.options[i].id + '|' + selectedList.options[i].value;
    }
  }
  var permitted_projectIDs_str = theForm.permitted_projectIDs_str.value;
  //alert(selectedList_str);  
  var queryString = "theaction=creatList&taskID_str=" + taskID_str + "&selectedList_str=" + selectedList_str + "&SearchEngine=" + SearchEngine + "&frm_note_id=" + frm_note_id + "&frm_groups=" + frm_groups + "&permitted_projectIDs_str=" + permitted_projectIDs_str; 
  //alert(queryString);//return;
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function clean_selected_list(){
  var parentItem = document.getElementById('frm_selected_list');
  if(parentItem.hasChildNodes()){
    for(var i=parentItem.length-1; i>0; i--){
      parentItem.remove(i);
    }
  }  
}

function processAjaxReturn(ret_html){
//alert(ret_html)
  document.getElementById('process').style.display = 'none';
  if(ret_html == '') return;
  var ret_html_arr = ret_html.split("@@**@@");
  //alert(ret_html_arr.length);
  if(ret_html_arr.length == 2){
    document.getElementById("results").innerHTML = ret_html_arr[0];
    document.getElementById("results2").innerHTML = ret_html_arr[1];
  }else{
    document.getElementById("note_group").innerHTML = ret_html_arr;
    startRequest();
  }
}

function switch_SearchEngine(){
  var theForm = document.form_comparison;
  theForm.theaction.value = "change_searchEnine";
  //DB = '';
  theForm.action = '<?php echo $PHP_SELF;?>';
  theForm.target = "_self";
  theForm.submit();
}

function switch_machine(machine_index){
  var theForm = document.form_comparison;
  theForm.theaction.value = "change_machine";
  theForm.machine_index.value = machine_index;
  var taskID_arr = theForm.frm_taskID;
  var taskID_str = '';
  
  if(typeof theForm.frm_taskID != "undefined"){
    if(typeof taskID_arr.length == "undefined"){
      if(taskID_arr.checked){
        taskID_str = taskID_arr.value;
      }  
    }else{
      for(var i=0; i<taskID_arr.length; i++){
        if(taskID_arr[i].checked){
          if(taskID_str) taskID_str += ',';
          taskID_str += taskID_arr[i].value;
        }  
      }
    }
    theForm.taskID_str.value = taskID_str;
  }
  var selectedList = document.getElementById('frm_selected_list');
  var selectedList_str = '';
  for(var i=0; i<selectedList.length; i++){
      if(selectedList.options[i].id == '') continue;
      if(selectedList_str) selectedList_str += ',';
      selectedList_str += selectedList.options[i].id + '|' + selectedList.options[i].value;
  }
  theForm.frm_selected_list_str.value = selectedList_str;
  theForm.selectedList_str.value = selectedList_str;
//alert(theForm.selectedList_str.value);
  theForm.action = '<?php echo $PHP_SELF;?>';
  theForm.target = "_self";
  theForm.submit();
}

function toggle_group(theForm){
  var groups = theForm.frm_groups;
  var frm_groups = '';
  var groups = theForm.frm_groups;
  for(var j=0; j<groups.length; j++){
    if(groups[j].checked){
      frm_groups = groups[j].value;
    }
  }
  var queryString = "theaction=toggle_group&frm_groups=" + frm_groups + "&permitted_projectIDs_str=<?php echo $permitted_projectIDs_str?>";
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function toggle_project(theForm){
  if(theForm.all_project[1].checked){
    document.location.href='<?php echo $PHP_SELF;?>?all_project=1';
  }else{
    document.location.href='<?php echo $PHP_SELF;?>?all_project=0';
  }
}
</script>
<style>
.intTr1{
	background: <?php echo $tb_color;?>;
}
.intTr2{
	background: <?php echo $tb_color2;?>;
}
.intTr3{
	background: <?php echo $tb_color3;?>;
}
.tabOn1{
  background: <?php echo $tb_color;?>;
  font-weight: bold;
  font-size: 13px;
}
.tabOn1 a{
  color: black;
  text-decoration: none; 
  border-bottom: none;
}
.tabOn2{
  background: <?php echo $tb_color2;?>;
  font-weight: bold;
  font-size: 13px;
}
.tabOn2 a{
  color: black;
  text-decoration: none; 
  border-bottom: none;
}
.tabOn3{
  background: <?php echo $tb_color3;?>;
  font-weight: bold;
  font-size: 13px;
}
.tabOn3 a{
  color: black;
  text-decoration: none; 
  border-bottom: none;
}
.tab{
  background: #708090;
  font-weight: bold;
  font-size: 13px;
}
.tab a{
  color: #ffffff;
  text-decoration: none; 
  border-bottom: none;
}
</style> 
<FORM ACTION="<?php echo $_SERVER['PHP_SELF'];?>" ID="form_comparison" NAME="form_comparison" METHOD="POST">
<INPUT TYPE="hidden" NAME="theaction" VALUE="">
<INPUT TYPE="hidden" NAME="frm_selected_list_str" VALUE="">
<INPUT TYPE="hidden" NAME="frm_selected_sample_str" VALUE="">
<INPUT TYPE="hidden" NAME="machine_index" VALUE="0">
<INPUT TYPE="hidden" NAME="permitted_projectIDs_str" VALUE="<?php echo $permitted_projectIDs_str?>">
<INPUT TYPE="hidden" NAME="taskID_str" VALUE="">
<INPUT TYPE="hidden" ID="selectedList_str" NAME="selectedList_str" VALUE="<?php echo $selectedList_str?>">
<div id='tmp'></div>

<table border="0" cellpadding="0" cellspacing="1" width="90%">
  <tr>
    <td align="left" colspan=10 >
    <br>&nbsp; <font color="navy" face="helvetica,arial,futura" size="4"><b><?php //=$tmp_lable;?></b></font>
<?php     
    if($AccessProjectName){
        echo "  <font color='red' face='helvetica,arial,futura' size='3'><b>(Project $AccessProjectID: $AccessProjectName)</b></font>";
    }
?>     
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
<?php 
if($no_task_flag){?>  
  <tr>
    <td align="center">
    <div style="font-weight: bold;font-size: 13px; margin-top:20px;">
      No DIA-Umpire task in this project or a DIA-Umpire task is running in Data Management.
    </div> 
    </td>
  </tr>
</table>
</body>
</html>
<?php 
require("site_footer.php");
exit;
}?>
  <tr>
    <td>
    &nbsp; instructions <a id='instruction_a' href="javascript: toggle_group_description('instruction')" class=Button>[+]</a>
    <DIV id='instruction' STYLE="display: none">
      <ul>
<li>Only the DIA-Umpire tasks with this project can be selected from the list. You can change a DIA-Umpire task's project from Data Management.
<li>The searched database should be the same, if raw files are selected from multiple DIA-Umpire tasks,
<li>If a raw file has been searched in multiple DIA-Umpire tasks, It only can be selected from one task.

     </ul>
    </DIV>
    </td>
  </tr>
  <tr>
    <td>
    <!--div style="border:#708090 1px solid;width: 75%"-->
    <table border="0" width="75%" cellspacing="1" cellpadding=0 align=center BGCOLOR="#236c7c">
    <tr BGCOLOR="#5c8ca3">
      <td colspan=10>
        <table border=0 width=100% cellspacing="1" cellpadding=0>
          <tr height="30">
            <td bgcolor="#004040">
               &nbsp;&nbsp;
               <font color="#FFFFFF">Current project</font><input type=radio name='all_project' value='0' <?php echo ($all_project==0)?'checked':''?> onclick="toggle_project(this.form)"  title=''> &nbsp;&nbsp;              
               <font color="#FFFFFF">All projects</font><input type=radio name='all_project' value='1' <?php echo ($all_project==1)?'checked':''?> onclick="toggle_project(this.form)"  title=''> 
            </td>
            <td align="left" width='' nowrap>&nbsp;&nbsp;
              <font size="2">
<?php 
          foreach($SearchEngine_arr_tree as $key => $val){
            if($key){
              //if(!isset($SearchEngine_arr_tree[$key][$selected_machine]) || !count($SearchEngine_arr_tree[$key][$selected_machine])) continue;
              $SearchEngine_lable = $SearchEngine_lable_arr[$key];
?>    
              <?php echo $SearchEngine_lable?><input type=radio name='frm_SearchEngine' value='<?php echo $key?>' <?php echo ($frm_SearchEngine == $key)?'checked':''?> onclick="switch_SearchEngine()">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php           }?> 
<?php         }?>    
              </font>
            </td>
            <td align="right" width='' nowrap>&nbsp;&nbsp;
              <input name=button onclick="javascript: send_files()" type="button" value=" Process files ">&nbsp;&nbsp;
            </td>  
          </tr>
        </table>
      </td>
    </tr>
    
    <tr id=intTr class=intTr1>
      <td width="350" align=center valign=top>
        <table border=0 width=100% cellspacing="0" cellpadding=0>
        <tr>
          <td align=center colspan=2 nowrap>
            <div style="border:#708090 0px solid;height:20px;font-size: 120%;padding: 5px 0px 5px 5px;">Searched tasks</div>
            <div style="border:#708090 0px solid;height:20px;font-size: 100%;padding: 0px 0px 0px 5px;">Tasks name&nbsp;[Tasks ID]</div> 
          </td>
        </tr>
        <tr>
          <td width="" align=center valign=top colspan=2>
          <div style="border:#708090 1px solid;height:318px;padding: 0px 0px 5px 5px;margin:0px 10px 2px 10px;overflow:scroll">
          <table border=0 width=100% cellspacing="0" cellpadding=0>
 <?php 
          $used_machine_arr = array();
          for($i=0;$i<count($machine_arr_tmp);$i++){
          ?>
            <tr>
            <td align="left" width='20%' nowrap>
            &nbsp;&nbsp;Machine&nbsp;&nbsp;
            <select name="frm_machine[]" onchange="switch_machine(<?php echo $i+1?>)">
              <option value="">
        <?php 
            $selected_index = 0;
            for($j=0;$j<count($machine_arr_tmp);$j++){
              if(in_array($machine_arr_tmp[$j], $used_machine_arr)) continue;
              $selected = '';
              if(isset($frm_machine[$i]) && $machine_arr_tmp[$j]==$frm_machine[$i]){
                $used_machine_arr[] = $machine_arr_tmp[$j];
                $selected_index = $j;
                $selected = 'selected';
              }
            ?>
              <option value="<?php echo $machine_arr_tmp[$j]?>" <?php echo $selected?>><?php echo $machine_arr_tmp[$j]?>
            <?php 
            }?>
            </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </td>
            </tr>
        <?php 
            if($machine_index == $i) break;
            if(!isset($frm_machine[$i]) || !$frm_machine[$i]) break;
            $machine_task_list = $machine_arr[$machine_arr_tmp[$selected_index]];
            print_task_list($machine_task_list,$machine_arr_tmp[$selected_index]);
          }
        ?>
          </table>
          </div>
          </td>
        </tr>
<?php 
//$GROUP_ARR = have_group();
//if($GROUP_ARR){
  if(!$all_project && $GROUP_ARR = have_group()){
    $frm_groups = $GROUP_ARR['Group'];
?>              
        <tr id=intTr class=intTr1>
          <td width="100%" align=center valign=top>
            <div style="border:#708090 1px solid;padding: 0px 0px 0px 0px;margin:0px 10px 10px 10px;">
            <table border=0 width=90% cellspacing="0" cellpadding=0>
            <tr>
              <td align=left height="20">&nbsp;Group type:</td>
            </tr>
            <tr>
              <td height="30" align=left valign=top nowrap>
    <?php foreach($GROUP_ARR as $KEY => $VAL){
        if(!$VAL) continue;
        if($KEY == 'Group') continue;
    ?>              
                      <input type="radio" name="frm_groups" value="<?php echo $KEY?>" onClick="toggle_group(this.form)" <?php echo (($frm_groups==$KEY)?'checked':'')?>><?php echo $KEY?>&nbsp;
    <?php }?>              
              </td>
            </tr>
            <tr><td height="20" align=left>&nbsp;Show group:</td>
            </tr>
            <tr>
              <td height="20" align=left valign=top>
                <table border=0 cellspacing="0" cellpadding=0>
                <tr>
                  <td valign=top>
                  <div id="note_group" style="border:#708090 0px solid;padding: 0px 0px 0px 0px;margin:0px 0px 0px 0px;">
                  <?php echo toggle_group($frm_groups);?>
                  </div><br>&nbsp;
                  </td>
                </tr>
                </table>
              </td>
            </tr>
            </table>
            </div>
          </td>
        </tr>
<?php }else{?>
        <tr>
          <td align=left height="20" width="380">&nbsp;<br><br></td>
        </tr>
<?php }?>   
        </table>      
      </td>      
      <td width=1 BGCOLOR="#ffffff"><img src='./images/pixel.gif' border=0></td>
      <td width="400" align=center valign=top>
      <div style="border:#708090 0px solid;height:20px;font-size: 120%;padding: 5px 0px 5px 5px;">Raw files</div>
      <div style="border:#708090 0px solid;height:20px;font-size: 100%;padding: 0px 0px 0px 5px;">Raw file name&nbsp;[Raw file ID,&nbsp;Sample ID,&nbsp;Tasks ID,&nbsp;Machine]</div> 
      <div id="results" style="border:#708090 0px solid;padding: 0px 10px 0px 10px;">
      <select ID="frm_sourceList" name="frm_sourceList" size=20 multiple>
        <option value=''><?php echo str_repeat("&nbsp;", 90);?>
      </select>
      </div>    
      </td>
      <td width=1 BGCOLOR="#ffffff"><img src='./images/pixel.gif' border=0></td>
      <td width="60" valign=center align=center><br>
      <div style='display:block' id='process'><img src='./images/process.gif' border=0></div>
      <br>
      <font size="2" face="Arial">
      <input type=button value='&nbsp;> >&nbsp;' onClick="add_option_to_selected()">
      <br><br>
      <input type=button value='&nbsp;< <&nbsp;' onClick="remove_option_from_selected()">
      </font> 
      </td>
      <td width=1 BGCOLOR="#ffffff"><img src='./images/pixel.gif' border=0></td>
      <td width="400" align=center valign=top>
        <table border=0>
          <tr>
            <td align=center>
            <div style="border:#708090 0px solid;height:20px;font-size: 120%;padding: 2px 0px 5px 5px;">Selected raw files</div> 
            <div style="border:#708090 0px solid;height:20px;font-size: 100%;padding: 0px 0px 0px 5px;">Raw file name&nbsp;[Raw file ID,&nbsp;Sample ID,&nbsp;Tasks ID,&nbsp;Machine]</div>             
  			    <div id="results2" style="border:#708090 0px solid;padding: 0px 7px 0px 7px;">
            <select id="frm_selected_list" name="frm_selected_list" size=20 multiple>
              <option value=''><?php echo str_repeat("&nbsp;", 90);?>
            </select>
            </div>
            <td>
            <td align=left style="border:#708090 0px solid;padding: 0px 11px 0px 0px;">
              <a href="javascript: moveOptionsUp('frm_selected_list',1);" title='up' class=button>
                <img border="0" src="images/icon_up.gif">
              </a><br>
              <a href="javascript: moveOptionsDown('frm_selected_list',1);" title='down' class=button>
                <img border="0" src="images/icon_down.gif">
              </a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    </table>    
    <!--/div-->
    </td>
    </tr>
    </table>
    </td>
  </tr>
</table>   
</FORM>
</body>
<?php 
if($theaction == "change_machine"){
?>
<SCRIPT language="Javascript">
  startRequest('yes');
</SCRIPT>
<?php 
}
?>
</html>
<?php 
 
require("site_footer.php");

function create_source_element_list($machine_taskID_str,$selectedList_str,$SearchEngine,$frm_note_id,$frm_groups){
  global $frm_machine;
  global $AccessProjectID;
  global $prohitsManagerDB;
  global $frm_SearchEngine;
  global $HITSDB;
  global $PROHITSDB;
  global $permitted_projectIDs_str;
  global $all_project;  
  $machine_tasks_arr_tmp = explode(",", $machine_taskID_str);
  $machine_tasks_arr = array();
//echo "\$permitted_projectIDs_str=$permitted_projectIDs_str<br>";
/*echo "<pre>";
print_r($machine_tasks_arr_tmp);  
echo "</pre>";*/  
  foreach($machine_tasks_arr_tmp as $machine_task_arrs_val){
    if(!trim($machine_task_arrs_val)) continue;
    $pair = explode("||", $machine_task_arrs_val);
    if(!array_key_exists($pair[0], $machine_tasks_arr)){
      $machine_tasks_arr[$pair[0]] = array();
    }
    $machine_tasks_arr[$pair[0]][] = $pair[1];
  }
  
/*echo "<pre>";  
print_r($machine_tasks_arr);  
echo "</pre>";*/  
    
  $all_file_task_arr = array();
  foreach($machine_tasks_arr as $machine => $tasks){
    $file_task_arr = array();
    $SQL = "SELECT `ID`,`TaskIDandFileIDs` FROM `DIAUmpireQuant_log` WHERE `Status`='Running' AND `Machine`='$machine'";
    $tmp_running_task_arr = $PROHITSDB->fetchAll($SQL);
    foreach($tmp_running_task_arr as $tmp_val){
      $tmp_arr = explode(',',$tmp_val['TaskIDandFileIDs']);
      foreach($tmp_arr as $tmp_val2){
        $tmp_arr2 = explode('|',$tmp_val2);
        if(!array_key_exists($tmp_arr2[1], $file_task_arr)){
          $file_task_arr[$tmp_arr2[1]] = array();
        }
        if(!in_array($tmp_arr2[0], $file_task_arr[$tmp_arr2[1]])){
          $file_task_arr[$tmp_arr2[1]][] = $tmp_val['ID'];
        }
      }
    }
    $all_file_task_arr[$machine] = $file_task_arr;
  }  
  
/*echo "<pre>";
print_r($tmp_running_task_arr);
print_r($file_task_arr);  
echo "</pre>";*/
  $tmp_itemID_arr = array();
  if(!$all_project){
    if($frm_note_id && $frm_groups){  
      $group_table = $frm_groups."Group";
      $SQL = "SELECT `RecordID` FROM $group_table WHERE `NoteTypeID`='$frm_note_id'";
      $tmp_Note_arr = $HITSDB->fetchAll($SQL);
      
      foreach($tmp_Note_arr as $tmp_Note_val){
        $tmp_itemID_arr[] = $tmp_Note_val['RecordID'];
      }
      if($tmp_itemID_arr && $frm_groups != 'Band'){
        if($frm_groups == 'Experiment') $frm_groups = "Exp";
        $item_ID = $frm_groups.'ID';
        $tmp_itemID_str = implode(",", $tmp_itemID_arr);
        $SQL = "SELECT `ID` FROM `Band` WHERE $item_ID IN ($tmp_itemID_str)"; 
        $tmp_arr = $HITSDB->fetchAll($SQL);
        $tmp_itemID_arr = array();
        foreach($tmp_arr as $tmp_val){
          $tmp_itemID_arr[] = $tmp_val['ID'];
        }
      }
    }
  }
 
  $selectedList_arr = array();
/*echo "<pre>";
print_r($machine_tasks_arr);
echo "</pre>";*/
//echo "\$selectedList_str=$selectedList_str<br>";
  //--------------------------------------------------------------------------------------------------      
  if($selectedList_str){
    $selectedID_arr = explode(",", $selectedList_str);
    foreach($selectedID_arr as $selectedID_val){
      $IDs_arr = explode("|", $selectedID_val);
      $sele_ID = $IDs_arr[0];
      $machine = $IDs_arr[1];
      $sele_taskID = $IDs_arr[3];
      //if(!in_array($machine, $machine_tasks_arr)) continue;
      if(!array_key_exists($machine, $machine_tasks_arr)) continue;
      $taskID_arr = $machine_tasks_arr[$machine];
      if(!in_array($sele_taskID, $taskID_arr)) continue;
      $SQL = "SELECT FileName,ProhitsID,ProjectID
              FROM $machine
              WHERE ID = $sele_ID";
      $selectedList_arr_tmp = $prohitsManagerDB->fetch($SQL);
      $selectedList_arr[$machine.'|'.$sele_ID.'|'.$sele_taskID] = $selectedList_arr_tmp;
    }
  }
//------------------------------------------------------------------------------------
  $all_source_arr = array();
  
/*echo "<pre>";
print_r($selectedList_arr);
echo "</pre>";*/ 
  if(isset($machine_tasks_arr) && $machine_tasks_arr){ 
    foreach($machine_tasks_arr as $machine => $taskID_arr){
      $taskID_str = implode(',',$taskID_arr);
      if($taskID_str){
        $result_table_name = $machine."SearchResults";      
        
        $SQL = "SELECT M.ID,M.FileName, M.ProhitsID, M.ProjectID,S.TaskID FROM $machine M
                LEFT JOIN  $result_table_name S
                ON (M.ID = S.WellID)
                WHERE M.ProjectID IN ($permitted_projectIDs_str)
                AND S.TaskID IN ($taskID_str) ";        
                
        if($SearchEngine != 'iProphet'){
          $SQL .= " AND S.SearchEngines = '$SearchEngine' ";
        }     
        $SQL .= "AND S.DataFiles !='' 
                AND M.ProhitsID IS NOT NULL 
                AND M.ProhitsID != 0";
        if($SearchEngine == 'iProphet'){
          $SQL .= " GROUP BY M.ID";
        }    
        $source_arr = $prohitsManagerDB->fetchAll($SQL);
  //echo "$SQL<br>";
  /*echo "<pre>";
  print_r($source_arr);
  //print_r($all_source_arr);
  echo "</pre>";*/      
        
        
        foreach($source_arr as $key => $source_val){
          $SQL = "SELECT `ID` FROM ".$machine."tppTasks WHERE `SearchTaskID`='".$source_val['TaskID']."'";
          $tmp_tpp_id_arr = $prohitsManagerDB->fetch($SQL);
          if(!$tmp_tpp_id_arr){
            $source_arr[$key]['tpp_task_id'] = '';
            $source_arr[$key]['has_tpp_result'] = 0;
          }else{
            $SQL = "SELECT `pepXML`, 
                           `protXML` 
                   FROM ".$machine."tppResults 
                   WHERE `WellID`='".$source_val['ID']."' 
                   AND `TppTaskID`='".$tmp_tpp_id_arr['ID']."' 
                   AND `SearchEngine`='$SearchEngine'";
            $tmp_tpp_resultFile_arr = $prohitsManagerDB->fetch($SQL);
            if($tmp_tpp_resultFile_arr){
              if($tmp_tpp_resultFile_arr['pepXML'] && $tmp_tpp_resultFile_arr['pepXML'] != 'NoPepXML' && $tmp_tpp_resultFile_arr['protXML'] && $tmp_tpp_resultFile_arr['protXML'] != 'NoProtXML'){
                $source_arr[$key]['has_tpp_result'] = 1;
              }else{
                $source_arr[$key]['has_tpp_result'] = 0;
              }
            }else{
              $source_arr[$key]['has_tpp_result'] = 0;
            }
            $source_arr[$key]['tpp_task_id'] = $tmp_tpp_id_arr['ID'];
          }
        }
        $all_source_arr[$machine] = $source_arr;
  /*echo "<pre>";
  //print_r($source_arr);
  print_r($all_source_arr);
  echo "</pre>";*/
      }
    }
  }
?>
  <select ID="frm_sourceList" name="frm_sourceList" size=20 multiple>
    <option value=''><?php echo str_repeat("&nbsp;", 90);?>
<?php 
  foreach($all_source_arr as $machine => $source_arr){
    foreach($source_arr as $source_val){
      if($frm_note_id && !in_array($source_val['ProhitsID'], $tmp_itemID_arr)) continue;
      $key = $machine.'|'.$source_val['ID'].'|'.$source_val['TaskID'];
      if(array_key_exists($key, $selectedList_arr)) continue;
      if(array_key_exists($source_val['ID'], $file_task_arr)){
        $ran_task = implode(',',$file_task_arr[$source_val['ID']]);
        $message = "This file is being searched on other task $ran_task.";
        $style = 'style="background-color:#ffb08a;"';
        $label = "**$ran_task";
        $tips = " title='".$message."'";
      }elseif(!$source_val['has_tpp_result']){
        $style = 'style="background-color:#bcbc7a;"';
        $label = "**1";
        //$label = "label='1'";
        $tips = " title='This file has no TPP results.'";
      }else{
        $style = '';
        //$label = "label='0'";
        $label = "";
        $tips = '';
      }
?>
      <option id='<?php echo $source_val['ID']?>' value='<?php echo $machine?>|<?php echo $source_val['ProhitsID']?>|<?php echo $source_val['TaskID']?>|<?php echo $source_val['ProjectID']?><?php echo $label?>' <?php echo $style?> <?php echo $tips?>><?php echo $source_val['FileName']?> [<?php echo $source_val['ID']?>] [<?php echo $source_val['ProhitsID']?>] [<?php echo $source_val['TaskID']?>][<?php echo $machine?>]
<?php 
    }
  }
?>
	</select>@@**@@
  <select ID="frm_selected_list" name="frm_selected_list" size=20 multiple>
    <option value=''><?php echo str_repeat("&nbsp;", 90);?>
<?php foreach($selectedList_arr as $key => $selectedList_val){
    if($frm_note_id && !in_array($selectedList_val['ProhitsID'], $tmp_itemID_arr)) continue;
    $tmp_ID_taskID_arr = explode("|", $key);
?>
    <option id='<?php echo $tmp_ID_taskID_arr[1]?>' value='<?php echo $tmp_ID_taskID_arr[0]?>|<?php echo $selectedList_val['ProhitsID']?>|<?php echo $tmp_ID_taskID_arr[2]?>|<?php echo $selectedList_val['ProjectID']?>'><?php echo $selectedList_val['FileName']?> [<?php echo $tmp_ID_taskID_arr[1]?>] [<?php echo $selectedList_val['ProhitsID']?>] [<?php echo $tmp_ID_taskID_arr[2]?>] [<?php echo $tmp_ID_taskID_arr[0]?>]
<?php }?>
	</select>
	<?php 
}

function toggle_group($frm_groups){
  global $HITSDB;
  global $AccessProjectID;
  global $frm_note_id;
  global $permitted_projectIDs_str;  
  
  //$SQL = "SELECT `ID` FROM `Bait` WHERE `ProjectID`= '$AccessProjectID'";
  $SQL = "SELECT `ID` FROM `Bait` WHERE `ProjectID` IN ($permitted_projectIDs_str)";
  $tmp_Bait_id_arr = $HITSDB->fetchAll($SQL);
  $item_id_str = '';
  foreach($tmp_Bait_id_arr as $tmp_Bait_id_val){
    if($item_id_str) $item_id_str .= ',';
    $item_id_str .= $tmp_Bait_id_val['ID'];
  }
  
  //if(!$item_id_str) exit;
  if($frm_groups != 'Bait'){
    $SQL = "SELECT `ID` FROM $frm_groups WHERE `BaitID` IN ($item_id_str)";
    $tmp_id_arr = $HITSDB->fetchAll($SQL);
    $item_id_str = '';
    foreach($tmp_id_arr as $tmp_id_val){
      if($item_id_str) $item_id_str .= ',';
      $item_id_str .= $tmp_id_val['ID'];
    }
  }
  $table_name = $frm_groups."Group";
  $SQL = "SELECT `NoteTypeID` FROM $table_name WHERE RecordID IN($item_id_str) GROUP BY`NoteTypeID`";
  
  $tmp_type_id_arr = $HITSDB->fetchAll($SQL);
  $Note_ID_str = '';
  foreach($tmp_type_id_arr as $tmp_type_id_val){
    if($Note_ID_str) $Note_ID_str .= ',';
    $Note_ID_str .= $tmp_type_id_val['NoteTypeID'];
  }
  if($Note_ID_str) {
  $SQL = "SELECT `ID`,`Name`,`Initial`,`Type`,`ProjectID` FROM `NoteType` WHERE ID IN ($Note_ID_str)";
  $Note_arr = $HITSDB->fetchAll($SQL);
?>
                <select name="frm_note_id" onchange="startRequest()">
                  <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                <?php foreach($Note_arr as $Note_val){
                    $VS = '';
                    if(is_numeric($Note_val['Initial'])) $VS = 'VS';
                    echo "<option value='".$Note_val['ID']."'".(($frm_note_id==$Note_val['ID'])?'selected':'').">".$Note_val['Name']." ($VS".$Note_val['Initial'].")</option>\r\n";
                  }
                ?>
                </select>
<?php 
  }
}

function have_group(){
  global $HITSDB;
  global $AccessProjectID;
  global $permitted_projectIDs_str;
  
  $has_flag = 0;
  $SQL = "SELECT `ID` FROM `Bait` WHERE `ProjectID` IN ($permitted_projectIDs_str)";
  $tmp_Bait_id_arr = $HITSDB->fetchAll($SQL);
  $item_id_str = '';
  foreach($tmp_Bait_id_arr as $tmp_Bait_id_val){
    if($item_id_str) $item_id_str .= ',';
    $item_id_str .= $tmp_Bait_id_val['ID'];
  }
  
  if(!$item_id_str) return $has_flag;
  $group_arr = array('Bait'=>'','Experiment'=>'','Band'=>'');
  foreach($group_arr as $key => $val){
    if($key != 'Bait'){
      $SQL = "SELECT `ID` FROM $key WHERE `BaitID` IN ($item_id_str)";
      $tmp_id_arr = $HITSDB->fetchAll($SQL);
      $item_id_str = '';
      foreach($tmp_id_arr as $tmp_id_val){
        if($item_id_str) $item_id_str .= ',';
        $item_id_str .= $tmp_id_val['ID'];
      }
    }
    $group_arr[$key] = $item_id_str;
  }  
  $group_arr_ok = array('Bait'=>'','Experiment'=>'','Band'=>'');
  foreach($group_arr as $key2 => $val2){
    if($val2){
      $table_name = $key2."Group";
      $SQL = "SELECT `NoteTypeID` FROM $table_name WHERE RecordID IN($val2) AND NoteTypeID !=0 GROUP BY`NoteTypeID` LIMIT 1";      
      $tmp_type_id_arr = $HITSDB->fetch($SQL);
      //if($tmp_type_id_arr){
      if($tmp_type_id_arr && $tmp_type_id_arr['NoteTypeID']){
        $group_arr_ok[$key2] = 1;
        if(!$has_flag){
          $has_flag = 1;
        }  
      }else{
        $group_arr_ok[$key2] = 0;
      }
    }else{
      $group_arr_ok[$key2] = 0;
    }
  }
  if($group_arr_ok['Band']){
    $group_arr_ok['Group'] = 'Band';
  }elseif($group_arr_ok['Bait']){
    $group_arr_ok['Group'] = 'Bait';
  }elseif($group_arr_ok['Experiment']){
    $group_arr_ok['Group'] = 'Experiment';
  }
  //echo "\$has_flag=$has_flag<br>";
  if($has_flag){
    return $group_arr_ok;
  }else{
    return $has_flag;
  }  
}


function print_task_list($task_list_arr,$frm_machine){
  global $prohitsManagerDB;
  global $frm_SearchEngine;
  global $AccessProjectID;
  global $permitted_projectIDs_str;
  global $frm_taskID;
  global $taskID_str;
  
  $taskID_arr = array();
  if($taskID_str){
    $taskID_arr = explode(',',$taskID_str);
  }
    
  foreach($task_list_arr as $task_list_val){
    $id_name_arr = explode("**",$task_list_val);
    $result_table_name = $frm_machine."SearchResults";
    if($frm_SearchEngine == 'iProphet'){
      $SQL = "SELECT `WellID` 
              FROM $result_table_name 
              WHERE `TaskID`='".$id_name_arr[0]."'
              AND `DataFiles` != ''
              GROUP BY `WellID`";
    }else{
      $SQL = "SELECT `WellID` 
              FROM $result_table_name 
              WHERE `TaskID`='".$id_name_arr[0]."'
              AND `DataFiles` != ''
              AND `SearchEngines`='$frm_SearchEngine'";
    }          
    $tmp_result_arr = $prohitsManagerDB->fetchAll($SQL);
    $rawID_str = '';
    foreach($tmp_result_arr as $tmp_result_val){
      if($rawID_str) $rawID_str .= ",";
      $rawID_str .= $tmp_result_val['WellID'];
    }
    if(!$rawID_str) continue;
    $SQL = "SELECT ID 
            FROM $frm_machine 
            WHERE ProjectID IN($permitted_projectIDs_str)
            AND ID IN ($rawID_str)
            AND ProhitsID IS NOT NULL 
            AND ProhitsID != 0";
    $tmp_rawID_arr = $prohitsManagerDB->fetchAll($SQL);
    if(!$tmp_rawID_arr) continue;
?>
    <tr>
      <td colspan="2" align=left nowrap>
        <input type=checkbox name='frm_taskID' value='<?php echo $frm_machine."||".$id_name_arr[0]?>' id='<?php echo $id_name_arr[2]?>' <?php echo (in_array($frm_machine."||".$id_name_arr[0], $taskID_arr))?'checked':''?> onclick="startRequest()"  title='DB: <?php echo $id_name_arr[2]?>'>&nbsp;<?php echo $id_name_arr[1]?>&nbsp;&nbsp;(<?php echo $id_name_arr[0]?>)
      </td>
    </tr>
<?php }
}
?>

