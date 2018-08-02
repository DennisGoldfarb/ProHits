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
$filter_for = 'comparison';
$frm_SearchEngine = '';
$frm_selected_element_str = '';
$frm_order_by = '';
$elementsPerPage = 500;
$currentType = 'Bait';
$itemType = 'Bait';
$frm_groups = 'Bait';
$currentPage = 1;
$action = '';
$offset = 0;
$tb_color = '#969696';
$SearchEngine = '';
$displaySearchEngine = 0;
$titleBarW = '90%';
$MascotHasHit = 0;
$GPMHasHit = 0;
$SEQUESTHasHit = 0;
$TPP_MascotHasHit = 0;
$TPP_COMETHasHit = 0;
$TPP_iProphetHasHit = 0;

$TPP_GPMHasHit = 0;
$TPP_SEQUESTHasHit = 0;
$subAction = '';
$switch_SearchEngine = 0;
$IDs = '';
$firstDisplay = '';
$selected_group_id = '';
$frm_search_by = '';
$frm_user = '';

$frm_filter_Expect = 0;
$frm_filter_Probability = 0;
$frm_filter_Coverage = 0;
$frm_filter_Peptide = '';
$frm_filter_Peptide_value = 0;
$frm_filter_Fequency = 'Fequency';
$frm_filter_Fequency_value = 0;
$frm_apply_filter = '';
$frm_min_XPRESS = '';
$frm_max_XPRESS = '';
$frm_selected_item_str = '';

$Is_geneLevel = 0;
$se_has_hits = false;

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");
ini_set("memory_limit","-1");

$SearchEngineConfig_arr = get_project_SearchEngine();
$hits_searchEngines = hits_searchEngines('get', $AccessProjectID,$HITSDB);

if($hits_searchEngines){
  if($Is_geneLevel){
    if(!$SearchEngine){
      foreach($hits_searchEngines as $val){
        if(strstr($val, 'GeneLevel')){
          $SearchEngine = $val;
          break;
        }
      }
    }
  }else{
    if(!$SearchEngine) $SearchEngine = $hits_searchEngines[0];
  }
}

if($action == 'creatList'){
	create_source_element_list();
	exit;
}elseif($action == 'add_option'){
  edit_comparison_session($IDs, $currentType);
  exit;
}elseif($action == 'remove_option'){
  edit_comparison_session($IDs, $currentType, "remove");
  create_source_element_list();
	exit;
}

$DB_name = $HITSDB->selected_db_name;
$exist_Hits_tables_arr = exist_hits_table($DB_name);
$radio_SearchEngine_arr_tmp = array();
$SearchEngineProperty_arr = get_SearchEngineProperty_arr($SearchEngineConfig_arr);
$SearchEngine_for_js_arr = get_SearchEngine_for_js_arr($SearchEngineConfig_arr);
$SearchEngine_lable_arr = get_SearchEngine_lable_arr($SearchEngineConfig_arr);

if($firstDisplay == 'y'){  
  $session_Type = get_comparison_session_Type();
  if($session_Type == 'Bait'){
    $currentType = 'Bait';
    $clickedId = "tabOn1";
    $selected_id_str = get_comparison_session("Bait");
    $tmp_id = 'BaitID';
  }elseif($session_Type == 'Exp'){
    $currentType = 'Exp';
    $clickedId = "tabOn3";
    $selected_id_str = get_comparison_session("Exp");
    $tmp_id = 'ExpID';
  }elseif($session_Type == 'Sample'){
    $currentType = 'Band';
    $clickedId = "tabOn2";
    $selected_id_str = get_comparison_session("Sample");
    $tmp_id = 'BandID';
  }
  $SearchEngine = fill_SearchEngineProperty_arr($selected_id_str,$SearchEngine,$session_Type);
  $elementID = '';
  $allElementsIDstr = get_all_elements_for_this_project($elementID);
  
  $radio_SearchEngine_arr = array();
  if($allElementsIDstr){   
    $radio_SearchEngine_arr = get_has_hits_SearchEngine($hits_searchEngines);
  }
}

require("site_header.php");

$bg_tb_header = '#7eb48e';
$tb_color = '#e3e3e3';
$tb_color2 = '#d1e7db';
$tb_color3 = '#e7e7cf';
?>
<link rel="stylesheet" type="text/css" href="./colorPicker_style.css">
<STYLE type="text/css">
.sss { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt; white-space: nowrap}
.sss2 { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt; font-weight : bold; white-space: nowrap}
.sss3 {	HEIGHT: 339px }
TD { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
</STYLE>
<SCRIPT language=Javascript  src="colorPicker.js"></SCRIPT>
<SCRIPT language=JavaScript> 
$(document).ready(function () {
  startRequest('start','');
	initial_color_cube();
});
var cp2 = new ColorPicker(); 
cp2.writeDiv();
</SCRIPT>
<script language="JavaScript" type="text/javascript">
<!--
var x = 0;
var current_group_color;
var radioName = 'abc';
var contrlColor = 'C_FFFF00';
var NnMergedColor = 'C_FFFFFF';

function addTableRow(colorNumSelected,lable){
  var colorNum = 'C_' + colorNumSelected.substring(1);
  var radioArr = document.getElementById('form_comparison');
	for(var i=0; i<radioArr.length; i++){
		if(radioArr[i].name == radioName && radioArr[i].value == colorNum){
			alert('This color has been selected. Please check another coler');
      return;
		}	
	}
	var color = colorNum;
  if(lable == ''){
    current_group_color = colorNum;
  	var text = ''; // 'Group' + (x+1);
    x++;
  }else{
    var text = lable;
  }  
  var row = document.createElement("tr");
	row.id = color;
  var cell = createCellWithRadio(radioName,color);
  row.appendChild(cell);
  cell = createCellWithBgcolor(color, text);
  row.appendChild(cell);
  document.getElementById("colorBarBody").appendChild(row);
}
function createCellWithBgcolor(color,text) {
  var cell = document.createElement('td');
	cell.className = color;
  if(text !== ''){
  	var textNode = document.createTextNode(text);
    cell.appendChild(textNode);
  }
  return cell;
}
function createCellWithRadio(radioName,value){
	var radioArr = document.getElementById('form_comparison');
	for(var i=0; i<radioArr.length; i++){
		if(radioArr[i].name == radioName){
			radioArr[i].checked = false;
		}	
	}
  var cell = document.createElement('td');
	try{
		rdo = document.createElement('<input type="radio" value="'+ value +'" name="'+ radioName +'" checked onclick="get_current_color()"/>');
	}catch(err){
		rdo = document.createElement('input');
		rdo.setAttribute('type','radio');
		rdo.setAttribute('name',radioName);
		rdo.setAttribute('value',value);
		rdo.setAttribute('checked',true);
		rdo.setAttribute('onclick','javascript: get_current_color()');
	}
	cell.appendChild(rdo);
  return cell;
}

function remove_single_row(rowID) {
	var tableBody = document.getElementById("colorBarBody");
	var rowNote = document.getElementById(rowID);
	tableBody.removeChild(rowNote);
}
function get_current_color(){
	var radioArr = document.getElementById('form_comparison');
	for(var i=0; i<radioArr.length; i++){
		if(radioArr[i].name == radioName && radioArr[i].checked == true){
			current_group_color = radioArr[i].value;
			break;
		}	
	}
}
function remove_color_bars(){
  var selectedList = document.getElementById('frm_selected_list');
  var listColorArr = new Array();
  var colorFlag = '';
  for(var j=0; j<selectedList.length; j++){
	  if(colorFlag != selectedList.options[j].value){
      colorFlag = selectedList.options[j].value
      listColorArr.push(colorFlag);
    }
  }
  var radioArr = document.getElementById('form_comparison');
  var checkedItem = 0;
  for(var i=radioArr.length-1; i>=0; i--){
		if(radioArr[i].name == radioName){
      if(radioArr[i].value == contrlColor) continue;
      if(radioArr[i].value == NnMergedColor){
        if(checkedItem == 1){
          radioArr[i].checked = true;
          current_group_color = radioArr[i].value;
        }  
        continue;
      }  
      var colorUsed = 0;
      for(var j=0; j<listColorArr.length; j++){
			  if(listColorArr[j] == radioArr[i].value){
          colorUsed = 1;
          break;
        } 
      }
      if(colorUsed == 0){
        if(radioArr[i].checked == true){
          checkedItem = 1;
        }  
        remove_single_row(radioArr[i].value);
      }  
		}	
	}
}

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
  var currentType = theForm.currentType.value;
	var selectedCounter = 0;
	var currentIndex = 0;
  var this_time_selected_option_str = '';
  
  for(var i=sourceList.length-1; i>0; i--){
    if(sourceList.options[i].selected){
      if(sourceList.options[i].id == '') continue;
      if(this_time_selected_option_str) this_time_selected_option_str += ",";
      this_time_selected_option_str += sourceList.options[i].id
			selectedCounter++;
      var optionNew = document.createElement('option');
    	optionNew.id = sourceList.options[i].id;
      optionNew.text = sourceList.options[i].text;
      optionNew.value = current_group_color;
    	optionNew.className = current_group_color;
			sourceList.remove(i);
			if(optionNew.value == NnMergedColor){
				if(selectedCounter == 1){

					append_new_option(selectedList, optionNew);
					currentIndex = selectedList.length-1;
				}else{
					inset_new_option(selectedList, optionNew, selectedList.options[currentIndex]);
				}		
			}else{
				var newGroup = 1;
	      for(var j=selectedList.length-1; j>0; j--){
	        if(optionNew.value == selectedList.options[j].value){
						if(selectedCounter == 1){
							if(j == selectedList.length-1){
								append_new_option(selectedList, optionNew);
							}else{
								inset_new_option(selectedList, optionNew, selectedList.options[j+1]);
							}				
							currentIndex = j + 1;
						}else{
							inset_new_option(selectedList, optionNew, selectedList.options[currentIndex]);
						}
						newGroup = 0;
	          break;
	        }
	      }
				if(newGroup == 1){
          if(current_group_color == contrlColor){
            if(selectedList.length > 1){
              inset_new_option(selectedList, optionNew, selectedList.options[1]);
            }else{
              append_new_option(selectedList, optionNew);
            }
						currentIndex = 1;
          }else	if(selectedList.options[selectedList.length-1].value == NnMergedColor){
						for(var j=0; j<selectedList.length; j++){
							if(selectedList.options[j].value == NnMergedColor) break;
						}
						inset_new_option(selectedList, optionNew, selectedList.options[j]);
						currentIndex = j;
					}else{
						append_new_option(selectedList, optionNew);
						currentIndex = selectedList.length-1;
            //alert(this_time_selected_option_str);
					}	
	      }       
	    }
		}	
  }  
  if(this_time_selected_option_str == '') return;
  queryString = "IDs=" + this_time_selected_option_str + "&Add=add" + "&action=add_option" + "&currentType=" + currentType  + "&Is_geneLevel=" + <?php echo $Is_geneLevel?>;
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function remove_option_from_selected(){
  var selectedList = document.getElementById('frm_selected_list');
	var theForm = document.form_comparison;
	var currentType = theForm.currentType.value;
  var this_time_selected_option_str = '';
  	
  for(var i=selectedList.length-1; i>0; i--){
    if(selectedList.options[i].id == '') continue;
    if(selectedList.options[i].selected){
      if(this_time_selected_option_str) this_time_selected_option_str += ",";
      this_time_selected_option_str += selectedList.options[i].id;
      selectedList.remove(i);
    }
  }
  if(this_time_selected_option_str == '') return;
  var queryString = createQueryString('remove_selected_item','');
  queryString += "&IDs=" + this_time_selected_option_str + "&Add=" + "&action=remove_option"  + "&Is_geneLevel=" + <?php echo $Is_geneLevel?>;
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function trimString(str) {
  var str = this != window? this : str;
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
//-->
</script>

<script type="text/javascript">
var sourceTitleTxt;

function initial_color_cube(){
  var theForm = document.form_comparison;
  var initialArr = new Array('#FFFF00-Control','#FFFFFF-Unmerge');
  for(var i=0; i<initialArr.length; i++){
    var tmpArr = initialArr[i].split('-');
    addTableRow(tmpArr[0],tmpArr[1]);
    if(tmpArr[1] == 'Unmerge'){
      current_group_color = 'C_FFFFFF';
    }
  }
}

function createQueryString(theaction,pageNum){
  var selObj = document.getElementById("frm_selected_list");
	var selected_id_str = '';  
	var frm_order_by;
	var currentPage;
  var theForm = document.form_comparison;
  var offset;
  var currentType;
  var displaySearchEngine = theForm.displaySearchEngine.value;
  var SearchEngineConfig_str = theForm.SearchEngineConfig_str.value;
  
  var frm_user = "&frm_user=";
  var frm_search_by = "&frm_search_by=";
  var firstDisplay = "&firstDisplay=";
  var SearchEngine = '';
  var subAction = '';
  var switch_SearchEngine = "&switch_SearchEngine=0";
  var per_selected_id_str = "";
  if(displaySearchEngine == '1'){
    for(var k=0; k<theForm.frm_SearchEngine.length; k++){
      if(theForm.frm_SearchEngine[k].checked == true){
        SearchEngine = theForm.frm_SearchEngine[k].value;
        break;
      }  
    }
  }else{
    SearchEngine = theForm.SearchEngine.value;
  }
	if(theaction == 'start'){
    SearchEngine = '<?php echo $SearchEngine?>';
    firstDisplay = "&firstDisplay=<?php echo $firstDisplay;?>";
    if('<?php echo $currentType?>' == 'Bait'){
  		currentType = 'Bait';
  		frm_order_by = 'ID';
    }else if('<?php echo $currentType?>' == 'Exp'){  
      currentType = 'Exp';
  		frm_order_by = 'E.BaitID';
    }else{
      currentType = 'Band';
      frm_order_by = 'D.BaitID';
    }
    var tmp_selected_id_str = '';
<?php //---------------------------------------------------------------------------------------------
	foreach($SearchEngine_for_js_arr as $key => $val){?>
    if(SearchEngine == '<?php echo $key?>') selected_id_str = theForm.<?php echo $val?>.value;
<?php }//------------------------------------------------------------------------------------------------
?>    
    currentPage = 1;
    subAction = "list_is_empty";
    switch_bgclor('<?php echo $clickedId?>');
  }else if(theaction == 'switch' || theaction == 'switch_SearchEngine'){
    if(theaction == 'switch_SearchEngine') switch_SearchEngine = "&switch_SearchEngine=1";
    currentType = pageNum;    
    if(currentType == 'Bait'){ 
      frm_order_by = 'ID';
      tmp_id = 'BaitID';
    }else if(currentType == 'Exp'){
  		frm_order_by = 'E.BaitID';
      tmp_id = 'ExpID';
    }else{
      frm_order_by = 'D.BaitID';
      tmp_id = 'BandID';
    }
    
    for(var i=1; i<selObj.length; i++){
      if(selected_id_str != "") selected_id_str += ',';
	    selected_id_str += selObj.options[i].id+':'+selObj.options[i].value+':'+selObj.options[i].className;
    }
    if(theaction == 'switch_SearchEngine'){
<?php //-------------------------------------------------------------------------------------------------------------------
  foreach($SearchEngine_for_js_arr as $key => $val){?>
    if(theForm.SearchEngine.value == '<?php echo $key?>') tmp_selected_id_str = theForm.<?php echo $val?>.value;
<?php }?>     
<?php foreach($SearchEngine_for_js_arr as $key => $val){?>
    if(theForm.SearchEngine_before.value == '<?php echo $key?>') theForm.<?php echo $val?>.value = tmp_id+"@@"+selected_id_str;
<?php }//-----------------------------------------------------------------------------------------------------------------------
?>     
      per_selected_id_str = selected_id_str;
      selected_id_str = tmp_selected_id_str;
    }else{
      var before_type = "switch_from_" + theForm.currentType.value + "@@";
      selected_id_str = before_type + selected_id_str;
    }    
    if(selected_id_str != ""){
      subAction = "list_sub_bands";
    }
    clean_selected_list();
		currentPage = 1;
	}else{
    var currentType = theForm.currentType.value;
    if(currentType == 'Bait'){ 
      frm_order_by = 'ID';
      tmp_id = 'BaitID';
    }else if(currentType == 'Exp'){
  		frm_order_by = 'E.BaitID';
      tmp_id = 'ExpID';
    }else{
      frm_order_by = 'D.BaitID';
      tmp_id = 'BandID';
    }
    subAction = "normal";
	  for(var i=1; i<selObj.length; i++){
	    if(selected_id_str != "") selected_id_str += ',';
			selected_id_str += selObj.options[i].id;
	  }
    selected_id_str = tmp_id+"@@"+selected_id_str;
		var order_by = document.getElementById("frm_order_by");
		for(var i=0; i<order_by.length; i++){
			if(order_by.options[i].selected == true && order_by.options[i].value !== ''){
				frm_order_by = order_by.options[i].value
	    }
	  }	
		if(pageNum != ''){
			currentPage = pageNum;
		}else{	
			currentPage = document.getElementById("currentPage").value;
		}
    offset = theForm.offset.value;
	}
  
  var group_str = '';
  var user_search = '';
  if(theaction == 'changeOrderby' || theaction == 'changePage' || theaction == 'remove_selected_item'){
    if(theaction == 'changeOrderby'){
      currentPage = 1;
    }
    document.getElementById("currentPage").value = currentPage;
    
    var itemType = theForm.itemType.value;
    if(itemType == 'Bait'){
      var selected_group_id = theForm.Bait_order_by.value;
    }else if(itemType == 'Experiment'){
      var selected_group_id = theForm.Experiment_order_by.value;
    }else if(itemType == 'Band'){
      var selected_group_id = theForm.Band_order_by.value;
    }    
    group_str = "&itemType=" + itemType + "&selected_group_id=" + selected_group_id;
    if(currentType != 'Bait'){
      var frm_groups = ''
      var groups_obj = theForm.frm_groups;
      for(var i=0; i<groups_obj.length; i++){
        if(groups_obj[i].checked){
          frm_groups = groups_obj[i].value;
          break;
        }
      }
      group_str += "&frm_groups=" + frm_groups;
    }
    user_search = "&frm_user=" + theForm.frm_user.value + "&frm_search_by=" + encodeURIComponent(theForm.frm_search_by.value);
  }    
  per_selected_id_str = "&per_selected_id_str=" + per_selected_id_str;  
  var queryString = "selected_id_str=" + selected_id_str + "&Is_geneLevel=" + <?php echo $Is_geneLevel?> + "&SearchEngineConfig_str=" + SearchEngineConfig_str + "&frm_order_by=" + frm_order_by + "&currentType=" + currentType + "&currentPage=" + currentPage + "&offset=" + offset + "&SearchEngine=" + SearchEngine + "&action=creatList&subAction=" + subAction + switch_SearchEngine + group_str + user_search + per_selected_id_str + firstDisplay;
  return queryString;
}


function startRequest(theaction,pageNum){
  var queryString = createQueryString(theaction,pageNum);
  document.getElementById('process').style.display = 'block';
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function switch_type(itemtype, clickedId){
  var ret = switch_bgclor(clickedId);
  if(ret == true){
    startRequest('switch',itemtype);
  }
}

function switch_SearchEngine(){
  var theForm = document.form_comparison;
  theForm.SearchEngine_before.value = theForm.SearchEngine.value;
  for(var k=0; k<theForm.frm_SearchEngine.length; k++){
    if(theForm.frm_SearchEngine[k].checked == true){
      if(theForm.SearchEngine.value == theForm.frm_SearchEngine[k].value) return; 
      theForm.SearchEngine.value = theForm.frm_SearchEngine[k].value;
      break;
    }  
  }
  theForm.frm_apply_filter.checked = false;
  var itemtype = theForm.currentType.value;
  clean_up_child_nodes('targetTitleType');
  clean_up_child_nodes('targetTitle');
  clean_up_child_nodes("results");
  startRequest('switch_SearchEngine',itemtype);
}

function clean_selected_list(){
  var parentItem = document.getElementById('frm_selected_list');
  if(parentItem.hasChildNodes()){
    for(var i=parentItem.length-1; i>0; i--){
      parentItem.remove(i);
    }
  }  
}

function clean_up_child_nodes(itemID){
  var parentItem = document.getElementById(itemID);
  if(parentItem.hasChildNodes()){
    while(parentItem.childNodes.length > 0) {
      parentItem.removeChild(parentItem.childNodes[0]);
    }
  }  
}

function processAjaxReturn(ret_html){
  document.getElementById('process').style.display = 'none';
  if(ret_html == '') return;
  var ret_html_arr = ret_html.split("@@**@@");
  document.getElementById("tmp").innerHTML = ret_html_arr[0];
  if(ret_html_arr.length >=2 && trimString(ret_html_arr[1]) == 'source_target'){
    clean_up_child_nodes("results");  
    var sub_action = trimString(ret_html_arr[4]);
    if(sub_action == "normal"){
      document.getElementById("results").innerHTML = ret_html_arr[2];
    }else{
    	document.getElementById("results").innerHTML = ret_html_arr[2];
    	clean_up_child_nodes("results2");
    	document.getElementById("results2").innerHTML = ret_html_arr[3];
      if(ret_html_arr[6] == 1){
        document.getElementById("filter_area_out").innerHTML = ret_html_arr[5];
      }  
    }	
    add_target_title();
  }  
}

function add_target_title(){
  var theForm = document.form_comparison; 
  var sourceTitle = document.getElementById('sourceTitle');
  var sourceTitleTxt = sourceTitle.firstChild.nodeValue;
  var targetTitle = document.getElementById('targetTitle');
  var currentType = theForm.currentType.value;
  if(currentType == 'Bait'){
    currentType = 'Baits';
  }else if(currentType == 'Exp'){  
    currentType = 'Experiments';
  }else if(currentType == 'Band'){
    currentType = 'Samples';
  }
  if(targetTitle.hasChildNodes()) {
    targetTitle.removeChild(targetTitle.childNodes[0]);
  }
  var textNode = document.createTextNode(sourceTitleTxt);
  targetTitle.appendChild(textNode);
  var targetTitleType = document.getElementById('targetTitleType');
  if(targetTitleType.hasChildNodes()) {
    targetTitleType.removeChild(targetTitleType.childNodes[0]);
  }
  var titleTypeTxt = 'Selected ' + currentType;
  var textNode2 = document.createTextNode(titleTypeTxt);
  targetTitleType.appendChild(textNode2);
}

var Bait_note_init_arr = new Array();
var Experiment_note_init_arr = new Array();
var Band_note_init_arr = new Array();
<?php 
$note_init_arr = get_noteType_ini_by_type();
foreach($note_init_arr as $key => $val){
  if($key == 'Export') continue;
  foreach($val as $val_2){
?>
  <?php echo $key?>_note_init_arr.push('<?php echo $val_2?>');
<?php 
  }
}
?>
var rpt_win_num = 0;
function generate_report(){
  var theForm = document.form_comparison;  
  var selectedList = document.getElementById('frm_selected_list');
  var currentType = theForm.currentType.value;
  var hasTage = theForm.hasTage.value;
  var typeName = '';
  var tmp_new_arr = new Array();
  if(currentType == 'Bait'){
    typeName = 'baits';
    tmp_new_arr = Bait_note_init_arr;
  }else if(currentType == 'Exp'){
    typeName = 'Experiments';
    tmp_new_arr = Experiment_note_init_arr;
  }else if(currentType == 'Band'){ 
    typeName = 'Samples';
    tmp_new_arr = Band_note_init_arr;
  }
  if(selectedList.length <= 1){
    alert("Please select baits or " + typeName + " for report");
    return false;
  }
  var colorVar = '';
  var idStr = '';
  var groupStr = '';
  var listStr = '';
  var typeStr = '';
 
  var typeFlag = true;
  var IniNameArr = new Array();
  var IniNameCountArr = new Array();
  var selectedListCount = selectedList.length - 1;
  
  for(var j=1; j<selectedList.length; j++){
    if(selectedList.options[j].value != colorVar){
      if(colorVar != '' && idStr != ''){
        groupStr = colorVar + ':' + idStr;
        if(listStr != '') listStr += ';';
        listStr += groupStr;
      }  
      colorVar = selectedList.options[j].value;
      idStr = '';
    }
    if(idStr != '') idStr += ',';
    idStr += selectedList.options[j].id;
    
    if(hasTage == 1 && typeFlag){
      var tmp_text = selectedList.options[j].text;
      var tag = tmp_text.match(/\[[A-Z]{1,2}\]/g);
      if(tag == null){
        typeFlag = false;
      }     
      if(typeFlag){
        for(var k=0; k<tag.length; k++){
          IniNameArr.push(tag[k]);
        }
      }
    }
  }
  
  IniNameArr.sort();
  var counter = 0;
  var tmp_tag = IniNameArr[0];

  for(var i=0; i<=IniNameArr.length; i++){
    if(IniNameArr[i] == tmp_tag){
      counter++;
    }else{
      if(counter == selectedListCount){
        var last_index = tmp_tag.length-1;
        tmp_tag = tmp_tag.substring(1,last_index);
        var flag = true;
        for(var v=0; v<tmp_new_arr.length; v++){
          if(tmp_new_arr[v] == tmp_tag){
            flag = false;
            break;
          }
        }
        if(flag){
          typeStr = '';
          break;
        }
        if(typeStr) typeStr += ",";
        typeStr += tmp_tag;
      }
      tmp_tag = IniNameArr[i];
      counter = 1;
    }
  }
  if(colorVar != '' && idStr != ''){
    groupStr = colorVar + ':' + idStr;
    if(listStr != '') listStr += ';';
    listStr += groupStr;
  }
  theForm.frm_selected_list_str.value = listStr;
 
  theForm.typeStr.value = typeStr;
  theForm.action = 'comparison_results_table.php';
  theForm.source.value = 'comparison';
  file = 'loading.html';
  rpt_win_num = rpt_win_num + 1;
  var subWin = "subWin" + rpt_win_num;
  newWin = window.open(file,subWin,'toolbar=1,location=0,directories=0,status=0,menubar=1,scrollbars=1,resizable=1,width=1100,height=800');
  newWin.focus();
  theForm.theaction.value = "generate_report";
  theForm.target = subWin;
  theForm.submit();
}

var currentTabId = "tabOn1";

function onOff(obj, colorName){
   if(obj.id == currentTabId){
    return false;
   }
   obj.className = colorName;
}

function switch_bgclor(clickedId){
	if(clickedId != currentTabId){
		var currentObj = document.getElementById(currentTabId);
		var clickedObj = document.getElementById(clickedId);
		currentObj.className = 'tab';
		clickedObj.className = clickedId;
		currentTabId = clickedId;
		var witchone = clickedId.substr(5,1);
		var trObj = document.getElementById('intTr');
		if(witchone == '1'){
			trObj.className = 'intTr1';
		}else if(witchone == '2'){
			trObj.className = 'intTr2';
		}else if(witchone == '3'){
			trObj.className = 'intTr3';
		}
    return true;
	}
  return false;
}

function toggle_group(theForm){
  var groups = theForm.frm_groups;
  for(var i=0; i<groups.length; i++){
    var group_obj = document.getElementById(groups[i].value);
    if(groups[i].checked == true){
      group_obj.style.display = "block";
      theForm.itemType.value = groups[i].value;
    }else{
      group_obj.style.display = "none";
    }
  }
}
function showhide_filter(DivID){
  var theForm = document.getElementById('form_comparison');
  var obj = document.getElementById(DivID);  
  if(theForm.frm_apply_filter.checked){
    obj.style.display = "block";
  }else{
    obj.style.display = "none";
  }
}
function swith_page(){
  var theForm = document.getElementById('form_comparison');
  if(theForm.Is_geneLevel.checked){
    var Is_geneLevel = 1;
  }else{
    var Is_geneLevel = 0;
  }
  window.location.assign("<?php echo $PHP_SELF;?>?firstDisplay=y&Is_geneLevel="+Is_geneLevel);
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
 
<FORM ACTION="/Prohits/analyst/comparison_results_table.php" ID="form_comparison" NAME="form_comparison" METHOD="POST">
<INPUT TYPE="hidden" NAME="theaction" VALUE="">
<INPUT TYPE="hidden" NAME="source" VALUE="">
<INPUT TYPE="hidden" NAME="frm_selected_list_str" VALUE="">
<INPUT TYPE="hidden" NAME="typeStr" VALUE="">
<INPUT TYPE="hidden" NAME="start" VALUE="">
<INPUT TYPE="hidden" name=color2>
<INPUT TYPE="hidden" NAME="displaySearchEngine" VALUE="<?php echo $displaySearchEngine?>">
<INPUT TYPE="hidden" NAME="SearchEngine" VALUE="<?php echo $SearchEngine?>">
<INPUT TYPE="hidden" NAME="filtrColorIniFlag" VALUE="1">
<INPUT TYPE="hidden" NAME="SearchEngine_before" VALUE="">
<INPUT TYPE="hidden" NAME="firstDisplay" VALUE="">
<INPUT TYPE="hidden" NAME="SearchEngine_before" VALUE="">
<INPUT TYPE="hidden" NAME="firstDisplay" VALUE="">
<INPUT TYPE="hidden" NAME="SearchEngineConfig_str" VALUE="">
<?php 
//--------------------------------------------------------------------------------------------------
print_SearchEngine_hedden_tag();
//--------------------------------------------------------------------------------------------------

?>
<div id='tmp'></div>
<table border="0" cellpadding="0" cellspacing="1" width="90%">
  <tr>
    <td align="left">
    <br>&nbsp; <font color="navy" face="helvetica,arial,futura" size="4"><b>Comparison</b></font>
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
  <tr>
    <td><br>
    &nbsp; instructions <a id='instruction_a' href="javascript: toggle_group_description('instruction')" class=Button>[+]</a>
    <DIV id='instruction' STYLE="display: none">
    <ul>
<li>To use the Comparison tool, simply select any set of Baits or Samples, and load into the Selected Baits or Selected Samples window by clicking the >> button. 

<li>Multiple samples may be assigned to a single bait.  Selecting a bait merges all samples with the same bait ID. 

<li>In a bait comparison (consisting of multiple samples from a single bait), peptides for each protein hit are not added together. Rather, ProHits simply displays the highest number of peptides identified for each hit in a single analysis. 

<li>Data may be merged to create control groups, or any user-defined group of baits or samples.   Click to select a merged group color, then load baits or samples into the Selected Baits/Samples window 

<li>When all Baits, Samples and/or groups are loaded and ready for comparison, press the [Generate Report] button. 

<li>There are two types of color codes in the results page. The hit property color code indicates hit property values by color gradient.  For example, the default hit property is "total peptides" (blue); the darker the shade, the higher the number of peptides observed. The shared hits color code is used to indicate hits in common between samples. 

     </ul>
    </DIV> 
    </td>
  </tr>
  
  <tr>
    <td align=center >
    <table width=908  cellspacing="0" cellpadding=1 border="0">
<?php 

if(array_key_exists('Hits_GeneLevel', $exist_Hits_tables_arr)){?>  
    <tr>
        <td>
        <input type="checkbox" name="Is_geneLevel" value="1" <?php echo ($Is_geneLevel)?'checked':'';?> onclick="swith_page();">&nbsp;&nbsp;Gene Level
        </td>
    </tr>
<?php }?>    
    <tr>
      <td>
    <DIV style="border:#708090 1px solid;">
    <table border="0" width="100%" bgcolor="#708090" height="50" cellspacing="0" cellpadding=0>
    <tr>
      <td colspan=7>
        <table border=0 width=100% cellspacing="0" cellpadding=0>
          <tr>
            <td class=tabOn1 id=tabOn1 nowrap height=30 onmouseover="onOff(this, 'tabOn1')" onmouseout="onOff(this, 'tab')">
             &nbsp; &nbsp;<a href="javascript: switch_type('Bait', 'tabOn1');"><font size="2"><b>Bait List</b></font>&nbsp; &nbsp;
            </td>
            <td BGCOLOR="#708090">&nbsp;</td>
            <td class=tab id=tabOn3 nowrap height=30 onmouseover="onOff(this, 'tabOn3')" onmouseout="onOff(this, 'tab')">
             &nbsp; &nbsp; <a href="javascript: switch_type('Exp', 'tabOn3');">Experiment List</a> &nbsp; &nbsp;
            </td>
            <td BGCOLOR="#708090">&nbsp;</td>
            <td class=tab id=tabOn2 nowrap height=30 onmouseover="onOff(this, 'tabOn2')" onmouseout="onOff(this, 'tab')">
             &nbsp; &nbsp; <a href="javascript: switch_type('Band', 'tabOn2');">Sample List</a> &nbsp; &nbsp;
            </td>
            
        <?php if($displaySearchEngine || 1){
            $titleBarW ='20%';
        ?>
            <td align="center" width=60% nowrap>
              <font size="2">
           <?php 
          $tmp_counter = 0;
          $tmp_type_lable = '';
          foreach($radio_SearchEngine_arr as $key => $val){
            if($Is_geneLevel){
              if(!strstr($key, 'GeneLevel_')) continue;
            }else{
              if(strstr($key, 'GeneLevel_')) continue;
            }
            if($val){
              $tmp_type_lable = $SearchEngine_lable_arr[$key];
              $tmp_counter++;
            }  
          }
          if($tmp_counter == 1){
            echo $tmp_type_lable;
          }else{
            foreach($radio_SearchEngine_arr as $key => $val){
              if($Is_geneLevel){
                if(!strstr($key, 'GeneLevel_')) continue;
              }else{
                if(strstr($key, 'GeneLevel_')) continue;
              }
              if(!isset($SearchEngine_lable_arr[$key])) continue;
              if($val){
              ?>
                &nbsp;&nbsp;&nbsp;<?php echo $SearchEngine_lable_arr[$key]?><input readonly type=radio name='frm_SearchEngine' value='<?php echo $key?>' <?php echo ($SearchEngine == $key)?'checked':''?> onclick="switch_SearchEngine()">&nbsp;&nbsp;&nbsp;
            <?php }?>  
          <?php }?>
        <?php }?>    
              </font>
            </td>
        <?php }?>
           
            <td width=<?php echo $titleBarW;?> BGCOLOR="#708090" align=right>
             <input type=button name='go' value='Generate Report' onClick="generate_report()">&nbsp;&nbsp;             
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr id=intTr class=intTr1>
      <td width="400" align=center valign=top><br>
      <div id="results"></div>    
      </td>
      <td width=1 BGCOLOR="#ffffff"><img src='./images/pixel.gif' border=0></td>
      <td width="60" valign=center align=center>
      <div style='display:block' id='process'><img src='./images/process.gif' border=0></div>
      <br>
      <font size="2" face="Arial">
      <input type=button value='&nbsp;&nbsp;> >&nbsp;&nbsp;' onClick="add_option_to_selected()">
      <br><br>
      <input type=button value='&nbsp;&nbsp;< <&nbsp;&nbsp;' onClick="remove_option_from_selected()">
      </font> 
      </td>
      <td width=1 BGCOLOR="#ffffff"><img src='./images/pixel.gif' border=0></td>
      <td width="400" align=center valign=top><br>
      <div id='targetTitleType' class=sss2></div> 
      <div id='targetTitle' class=sss></div>
      <table border=0><tr><td align=right>
			<div id="results2"></div>
      <td>
      <td align=left>
        <a href="javascript: moveOptionsUp('frm_selected_list');" title='up' class=button>
          <img border="0" src="images/icon_up.gif">
        </a><br>
        <a href="javascript: moveOptionsDown('frm_selected_list');" title='down' class=button>
          <img border="0" src="images/icon_down.gif">
        </a>
      </td>
      </tr>
      </table>
      
      <font size="2">
      Hit property color code <input type=radio name='frm_color_mode' value='property' checked><br>
      Shared hits color code <input type=radio name='frm_color_mode' value='shared'> <br><br>
      </font>
     
      <input type="checkbox" name="frm_apply_filter" value="y" <?php echo ($frm_apply_filter=='y')?'checked':'';?> onclick="showhide_filter('filter_area');">
      Apply Filters
      </td>
      <td width=1 BGCOLOR="#ffffff"><img src='./images/pixel.gif' border=0></td>
      <td width="150" align=center valign=top><br>
      <b>Merge groups</b><br>
       new group color <A id=pick2  onclick="cp2.select(document.form_comparison.color2,'pick2');return false;"  href="#"  name=pick2 nowrap><img src='./images/color.gif' border=0 align=center></A>
			<br>&nbsp;<br>
      <div class=sss3>
      <table id="colorBarTable" width="100" border="0">
			  <tbody id="colorBarBody">					
			  </tbody>
			</table>
      </div>
      <font size="2">
      <a href="javascript:remove_color_bars();">Remove</a><br>Unused colors
      </font>
      </td>
    </tr>
    </table>
    </DIV>
    </td>
    </tr>
    
    <tr BGCOLOR="#ffffff"><td>&nbsp;</td></tr>
    <tr BGCOLOR="#ffffff">
    <td align='center'>
    <!--DIV id="filter_area" STYLE="display: none;"></DIV-->
    <DIV id="filter_area_out"></DIV>
    </td>
    </tr>
    
    </table>
    </td>
  </tr>
</table>   
</FORM>
</body>
</html>
<?php 

require("site_footer.php");

function create_source_element_list(){
  global $HITSDB,$AccessProjectID,$currentType,$itemType,$elementsPerPage,$frm_order_by,$selected_id_str,$currentPage,$frm_groups;
  global $bg_tb_header,$bg_tb,$offset,$SearchEngine,$subAction,$switch_SearchEngine,$per_selected_id_str,$selected_group_id;
  global $filter_for,$Expect,$theaction;
  global $frm_search_by,$PROHITSDB,$frm_user;
  //================================================================================
  global $frm_filter_Expect;
  global $frm_filter_Probability;
  global $frm_filter_Coverage;
  global $frm_filter_Peptide;
  global $frm_filter_Peptide_value;
  global $frm_filter_Fequency;
  global $frm_filter_Fequency_value;
  global $frm_apply_filter;
  global $frm_min_XPRESS;
  global $frm_max_XPRESS;

  global $PROBABILITY;
  global $TOTAL_NUMBER_PEPTIDES;
  global $UNIQUE_NUMBER_PEPTIDES;
  global $PERCENT_COVERAGE;
  
  global $theaction;
  global $sqlOrderby;
  global $orderby;
  global $sort_by_item_id;
  global $asc_desc;
  global $maxScore;
  global $is_all_tag;
  global $itemType;
  global $frm_groups;
  //global $subAction;
  global $switch_SearchEngine;
  global $IDs;
  global $firstDisplay;
  global $selected_group_id;
  global $SearchEngine_lable_arr;
  global $frm_selected_item_str;
  global $Is_geneLevel; 
 
  $source = 'comparison';
  $update_filter_box = 0;  

  if($selected_id_str){
    $tmp_selected_id_arr = explode("@@",$selected_id_str);
    $tmp_selected_id_arr2 = explode(",",$tmp_selected_id_arr[1]);
  
    $frm_selected_item_str = '';
    foreach($tmp_selected_id_arr2 as $tmp_selected_id_val2){
      if(!$tmp_selected_id_val2) continue;
      if($frm_selected_item_str) $frm_selected_item_str .= ',';
      $tmp_selected_id_arr3 = explode(":",$tmp_selected_id_val2);
      $frm_selected_item_str .= $tmp_selected_id_arr3[0];
    }
  }
  
  if($firstDisplay == 'y' || $switch_SearchEngine == 1) $update_filter_box = 1;

  if(!isset($frm_NS_group_id) || !$frm_NS_group_id){
    $frm_NS_group_id = '';
  }
  $NSfilteIDarr = array();
  get_NS_geneID($NSfilteIDarr,$frm_NS_group_id);

  $SQL = "SELECT `ID`,`Name` FROM `ExpBackGroundSet` WHERE `ProjectID`='$AccessProjectID'";
  $NSarr = $HITSDB->fetchAll($SQL);
  
  $proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
  
  $filerLable_css = "maintext_extra";
  
  if(strstr($SearchEngine, 'TPP_')){
    $hitType = 'TPP';
  }elseif($Is_geneLevel){
    $hitType = "geneLevel";
  }else{
    $hitType = 'normal';
  }  
  $typeBioArr = array();
  $typeExpArr_tmp = array();
  $typeExpArr = array();
  $typeFrequencyArr = array();
  create_filter_status_arrs($typeBioArr,$typeExpArr_tmp,$typeFrequencyArr,'comparison');

  $filterArgumentsStr = '';
  foreach($typeBioArr as $typeBioValue){
    $frmName = 'frm_' . $typeBioValue['Alias'];
    if($theaction == 'generate_report' || 1){
      $$frmName = $typeBioValue['Init'];
    }else{
      if(!isset($$frmName)){
        $$frmName = "0";
      }
    }
    $filterArgumentsStr .= '@@'.$frmName.'='.$$frmName;
  }
  $NStmpArr = array();
  foreach($typeExpArr_tmp as $typeExpValue){
    if($typeExpValue['Alias'] == 'OP'){
      continue;
    }elseif($typeExpValue['Alias'] == 'NS'){
      $NStmpArr = $typeExpValue;
    }else{
      array_push($typeExpArr, $typeExpValue);
    }
  }
  if($NStmpArr) array_unshift($typeExpArr, $NStmpArr);
  foreach($typeExpArr as $typeExpValue){
    if($typeExpValue['Alias'] == 'OP') continue;
    $frmName = 'frm_' . $typeExpValue['Alias'];
    if($theaction == 'generate_report' || 1){
      $$frmName = $typeExpValue['Init'];
    }else{
      if(!isset($$frmName)){
        $$frmName = "0";
      }
    }
    $filterArgumentsStr .= '@@'.$frmName.'='.$$frmName;
  }  
  if($SearchEngine == 'GPM'){
    $Expect = 'Expect2';
    if(!$orderby || $orderby == 'Expect'){
      $orderby = 'Expect2';
    }
    if($source == 'comparison'){
      $asc_desc = 'ASC';
    }
  }else{
    $Expect = 'Expect';
    if(!$orderby){
      $orderby = 'Expect';
    }
  }
  
  $A = isset($frm_BT) && !$frm_BT;
  
  $frm_min = ''; 
  $frm_max = '';
  $frm_tage_max = '';
  $frm_tage_min ='';
  $hasGel = 0;
  $jointed = 0;
  $isJointedPage = 0;  
  if($currentType == 'Bait'){
    $sourceType = 'Baits';
    $itemType = 'Bait';
  }elseif($currentType == 'Exp'){
    $sourceType = 'Experiments';
  }elseif($currentType == 'Band'){
    $sourceType = 'Samples';
  }
  $sele_optionStr = '';
  
  $has_notes_itemID_arr = array();
  
  $bait_group_icon_arr = get_project_noteType_arr($HITSDB);
  
  $item_group_icon_arr = array('Bait'=>array(),'Experiment'=>array(),'Band'=>array());
  foreach($bait_group_icon_arr as $key => $bait_group_icon_val){
    if(!$bait_group_icon_val['Type']) continue;
    if($bait_group_icon_val['Type'] == 'Export'){
      $item_group_icon_arr['Band'][$key] = $bait_group_icon_val;
    }else{  
      $item_group_icon_arr[$bait_group_icon_val['Type']][$key] = $bait_group_icon_val;
    }  
  }

  $tmp_arr = explode(",",$per_selected_id_str);
  $per_selected_id_arr = array();
  foreach($tmp_arr as $tmp_val){
    $tmp_arr_2 = explode(":",$tmp_val);
    $per_selected_id_arr[] = $tmp_arr_2[0];
  }  

  $hasTage = get_tages($has_notes_itemID_arr,$item_group_icon_arr[$itemType],$itemType,$currentType);
  $selected_id_arr = array();
  $selected_id_arr_tmp = array();
  $opPropertyArr = array();
  $tmptmpOpArr = explode('@@',$selected_id_str);
  
  
  if($tmptmpOpArr[0]){
    $before_type = $tmptmpOpArr[0];
  }else{
    $before_type = "BaitID";
  }
  $selected_id_str_new = '';
 
  if(has_itemIDstr_in_session()){ 
    $tem_ssesion_arr = array();
    if($before_type == 'switch_from_Bait' || $before_type == 'BaitID'){
      $tem_ssesion_arr = get_comparison_session('Bait',1);
      $tmpID = "BaitID";
    }elseif($before_type == 'switch_from_Exp' || $before_type == 'ExpID'){
      $tem_ssesion_arr = get_comparison_session('Exp',1);
      if($tem_ssesion_arr){
        $exp_selected_id_str = implode(",", $tem_ssesion_arr);
        $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID` IN ($exp_selected_id_str)";
        $tmpExpIdArr = $HITSDB->fetchAll($SQL);
        $tem_ssesion_arr = array();
        foreach($tmpExpIdArr as $tmpExpIdVal){
          array_push($tem_ssesion_arr, $tmpExpIdVal['ID']);
        }
      }
      $tmpID = "BandID";
    }elseif($before_type == 'switch_from_Band' || $before_type == 'BandID'){
      $tem_ssesion_arr = get_comparison_session('Sample',1);
      $tmpID = "BandID";
    }
    $selected_id_arr = array_unique($tem_ssesion_arr);
    rsort($selected_id_arr);  
    $selected_id_str = implode(",", $selected_id_arr);
    if($selected_id_str){
      if(strstr($SearchEngine, 'TPP_')){
        $SQL_table = " TppProtein ";
      }elseif($Is_geneLevel){
        $SQL_table = " Hits_GeneLevel ";
      }else{
        $SQL_table = " Hits ";
      }   

      $WHERE = SearchEngine_WHERE_OR($SearchEngine);

      if($currentType == 'Band'){    
        foreach($selected_id_arr as $selected_id){
          if(!$selected_id) continue;
          $SQL = "SELECT 
                  BandID AS ID,
                  BaitID 
                  FROM $SQL_table 
                  $WHERE
                  $tmpID='$selected_id'  
                  GROUP BY BandID";
          $tmpSubArr = $HITSDB->fetchAll($SQL);
          for($i=0; $i<count($tmpSubArr); $i++){
            $SQL = "SELECT D.ID, D.BaitID, D.Location, B.GeneName, L.GelID, L.LaneNum";
            $FROM ="  FROM Band D
                      LEFT JOIN Bait B ON D.BaitID = B.ID
                      LEFT JOIN Lane L ON D.LaneID = L.ID
                      WHERE D.ID='".$tmpSubArr[$i]['ID']."'";
            $SQL .= $FROM;
            if($tmp_tmpSubArr = $HITSDB->fetch($SQL)){
              $tmpSubArr[$i]['Location'] = $tmp_tmpSubArr['Location'];
              $tmpSubArr[$i]['GeneName'] = $tmp_tmpSubArr['GeneName'];
              $tmpSubArr[$i]['GelID'] = $tmp_tmpSubArr['GelID'];
              $tmpSubArr[$i]['LaneNum'] = $tmp_tmpSubArr['LaneNum'];
            }
          }
		      foreach($tmpSubArr as $elementsValue){
            $initial_str = '';
            if(isset($has_notes_itemID_arr[$elementsValue['ID']])){
              foreach($has_notes_itemID_arr[$elementsValue['ID']] as $tmpTypeID){
                $VS = '';
                $tmp_version_num = $bait_group_icon_arr[$tmpTypeID]['Initial'];
                if(is_numeric($tmp_version_num))  $VS = 'VS';
                $initial_str .= "[".$VS.$tmp_version_num."]";
              }
            }
    				$gellStr = '';
    	  		if($elementsValue['GelID']){
    	  			$gellStr = $elementsValue['GelID']."&nbsp; &nbsp;".$elementsValue['LaneNum']."&nbsp; &nbsp;";
    	  		}
            if($before_type == 'switch_type' || $before_type == 'BaitID'){
              $PropertyArr_index = $elementsValue['BaitID'];
            }else{
              $PropertyArr_index = $elementsValue['ID'];
            }
            $sele_option = "<option id='".$elementsValue['ID']."' value='C_FFFFFF' class='C_FFFFFF'>";            
            //$sele_option = "<option id='".$elementsValue['ID']."' value='".$opPropertyArr[$PropertyArr_index][1]."' class='".$opPropertyArr[$PropertyArr_index][2]."'>";          
    	      $sele_optionStr .= $sele_option.$elementsValue['BaitID']."&nbsp; &nbsp;".$elementsValue['GeneName']."&nbsp; &nbsp;".$elementsValue['ID']."&nbsp; &nbsp;".$elementsValue['Location']."&nbsp; &nbsp;".$gellStr.$initial_str."\n";
    	  	  if($selected_id_str_new) $selected_id_str_new .= ",";
    			  $selected_id_str_new .= $elementsValue['ID'];
          }
        }
      }elseif($currentType == 'Exp'){
        if($selected_id_arr){         
          $selected_id_str = implode(",", $selected_id_arr);
          $SQL = "SELECT 
                  BandID
                  FROM $SQL_table
                  $WHERE 
                  $tmpID IN ($selected_id_str)  
                  GROUP BY BandID";
                  
          $tmpBandIDArr = $HITSDB->fetchAll($SQL);
          $tmp_band_id_str = '';
          foreach($tmpBandIDArr as $temVal){
            if($tmp_band_id_str) $tmp_band_id_str .= ",";
            $tmp_band_id_str .= $temVal['BandID'];
          }
          if($tmp_band_id_str){
            $SQL = "SELECT 
                    B.ExpID AS ID,
                    E.Name,
                    BA.ID AS BaitID,
                    BA.GeneName
                    FROM Band B
                    LEFT JOIN Experiment E ON (B.ExpID=E.ID)
                    LEFT JOIN Bait BA ON (B.BaitID = BA.ID)
                    WHERE B.ID IN ($tmp_band_id_str)
                    GROUP BY B.ExpID
                    ORDER BY B.ExpID DESC";
            $Exp_info_arr = $tmpBandIDArr = $HITSDB->fetchAll($SQL);
      		  foreach($Exp_info_arr as $elementsValue){
              $initial_str = '';
              if(isset($has_notes_itemID_arr[$elementsValue['ID']])){
                foreach($has_notes_itemID_arr[$elementsValue['ID']] as $tmpTypeID){
                  $VS = '';
                  $tmp_version_num = $bait_group_icon_arr[$tmpTypeID]['Initial'];
                  if(is_numeric($tmp_version_num))  $VS = 'VS';
                  $initial_str .= "[".$VS.$tmp_version_num."]";
                }
              }          
              $sele_option = "<option id='".$elementsValue['ID']."' value='C_FFFFFF' class='C_FFFFFF'>";
      	      $sele_optionStr .= $sele_option.$elementsValue['BaitID']."&nbsp; &nbsp;".$elementsValue['GeneName']."&nbsp; &nbsp;".$elementsValue['ID']."&nbsp; &nbsp;".$elementsValue['Name']."&nbsp; &nbsp;".$initial_str."\n";
      	  	  if($selected_id_str_new) $selected_id_str_new .= ",";
      			  $selected_id_str_new .= $elementsValue['ID'];
            }
          }
        }     
      }elseif($currentType == 'Bait'){
        $tmp_baitID_arr = array(); 
        $tmpSubArr = array();   
        foreach($selected_id_arr as $selected_id){
          if(!$selected_id) continue;
          $SQL = "SELECT 
                  BaitID
                  FROM $SQL_table 
                  $WHERE
                  $tmpID='$selected_id'  
                  GROUP BY BaitID";
          $tmpSubArr_tmp = $HITSDB->fetchAll($SQL);
          $tmp_flag = 0;
          for($i=0; $i<count($tmpSubArr_tmp); $i++){
            if(!in_array($tmpSubArr_tmp[$i]['BaitID'],$tmp_baitID_arr)){
              array_push($tmp_baitID_arr, $tmpSubArr_tmp[$i]['BaitID']);
              $SQL = "SELECT 
                      `ID` AS BaitID,
                      `GeneName`, 
                      `BaitAcc`, 
                      `Tag`, 
                      `Mutation` 
                      FROM `Bait` 
                      WHERE `ID`='".$tmpSubArr_tmp[$i]['BaitID']."'";
              if($tmp_tmpSubArr = $HITSDB->fetch($SQL)){
                $tmpSubArr_tmp[$i]['GeneName'] = $tmp_tmpSubArr['GeneName'];
                $tmpSubArr_tmp[$i]['BaitAcc'] = $tmp_tmpSubArr['BaitAcc'];
                $tmpSubArr_tmp[$i]['Tag'] = $tmp_tmpSubArr['Tag'];
                $tmpSubArr_tmp[$i]['Mutation'] = $tmp_tmpSubArr['Mutation'];
                array_push($tmpSubArr,$tmpSubArr_tmp[$i]);
              }
            }else{
              continue;
            }    
          }
        }  
  
  		  if($tmpSubArr){
          foreach($tmpSubArr as $elementsValue){
            if($selected_id_str_new) $selected_id_str_new .= ",";
            $selected_id_str_new .= $elementsValue['BaitID'];
            $initial_str = '';
            if(isset($has_notes_itemID_arr[$elementsValue['BaitID']])){
              foreach($has_notes_itemID_arr[$elementsValue['BaitID']] as $tmpTypeID){  
                $VS = '';
                $tmp_version_num = $bait_group_icon_arr[$tmpTypeID]['Initial'];
                if(is_numeric($tmp_version_num))  $VS = 'VS';
                $initial_str .= "[".$VS.$tmp_version_num."]";
              }
            }        
            $baitTag = '';
            if($elementsValue['Tag'] && $elementsValue['Mutation']){
              $baitTag = "(".$elementsValue['Tag'].";".$elementsValue['Mutation'].")";
            }elseif($elementsValue['Tag']){
              $baitTag = "(".$elementsValue['Tag'].")";
            }elseif($elementsValue['Mutation']){
              $baitTag = "(".$elementsValue['Mutation'].")";
            }
            
            $sele_option = "<option id='".$elementsValue['BaitID']."' value='C_FFFFFF' class='C_FFFFFF'>";
            //$sele_option = "<option id='".$elementsValue['BaitID']."' value='".$elementsValue['c1']."' class='".$elementsValue['c2']."'>";
            $sele_optionStr .= $sele_option.$elementsValue['BaitID']."&nbsp; &nbsp;".escapeSpace($elementsValue['GeneName']).$baitTag."&nbsp; &nbsp;".$elementsValue['BaitAcc']."&nbsp; &nbsp;".$initial_str."\n";
          }
        }
      }
    }
  }

  if($firstDisplay){
    $frm_user = $_SESSION['USER']->ID;
  }

  $tmpElementIdArr = array();
  $tmpElementIdStr = get_real_elements_for_this_project($tmpElementIdArr,'',$SearchEngine);
  
  if($firstDisplay && ($tmpElementIdStr == 'no_hits' || $tmpElementIdStr == 'no_item')){
    $frm_user = '';
    $tmpElementIdStr = get_real_elements_for_this_project($tmpElementIdArr,'',$SearchEngine);
  }
  if($tmpElementIdStr == 'no_hits' || $tmpElementIdStr == 'no_item'){
    $tmpElementIdStr = '';
  }
  
  $group_type_id_arr = array();
  $frm_search_by = trim($frm_search_by);
  
  if($tmpElementIdStr){
    if($currentType == 'Band'){
      $group_type_id_arr_tmp = array('Bait'=>'BaitID','Experiment'=>'ExpID','Band'=>'');
      foreach($group_type_id_arr_tmp as $key => $val){
        if($tmpElementIdStr){
          if($key == 'Band'){
            $group_type_id_arr_tmp[$key] = $tmpElementIdStr;
          }else{
            $SQL = "SELECT $val FROM `Band` WHERE `ID` IN($tmpElementIdStr) GROUP BY $val";
            if($tmpExpIDarr = $HITSDB->fetchAll($SQL)){
              $tmp_id_arr = array();
              foreach($tmpExpIDarr as $tmpExpIDval){
                array_push($tmp_id_arr, $tmpExpIDval[$val]);
              }
              $tmp_bait_id_str = implode(",", $tmp_id_arr);
            }else{
              $tmp_bait_id_str = '';
            }
            $group_type_id_arr_tmp[$key] = $tmp_bait_id_str;
          }
        } 
      }
    }elseif($currentType == 'Exp'){
      if($tmpElementIdStr){
        $group_type_id_arr_tmp = array('Bait'=>'','Experiment'=>$tmpElementIdStr,'Band'=>'');
        $SQL = "SELECT BaitID FROM Experiment WHERE `ID` IN($tmpElementIdStr) GROUP BY BaitID";
        if($tmpExpIDarr = $HITSDB->fetchAll($SQL)){
          $tmp_id_arr = array();
          foreach($tmpExpIDarr as $tmpExpIDval){
            array_push($tmp_id_arr, $tmpExpIDval['BaitID']);
          }
          $tmp_bait_id_str = implode(",", $tmp_id_arr);
        }else{
          $tmp_bait_id_str = '';
        }
        $group_type_id_arr_tmp['Bait'] = $tmp_bait_id_str;
      }   
    }elseif($currentType == 'Bait'){
      if($tmpElementIdStr){   
        $group_type_id_arr_tmp = array('Bait'=>$tmpElementIdStr,'Experiment'=>'','Band'=>'');
      }
    }
    if($frm_search_by && $tmpElementIdStr){
      $tmpElementIdStr = search_item_name($frm_search_by,$currentType,$tmpElementIdStr);
      $tmpElementIdArr = explode(",", $tmpElementIdStr);
    }
    foreach($group_type_id_arr_tmp as $key2 => $val2){
      $group_type_id_arr[$key2] = array();
      if(($val2) && ($val2 != 'BaitID' && $val2 != 'ExpID' && $val2 != 'BandID')){
        $table_name = $key2."Group";
        $SQL = "SELECT `NoteTypeID` FROM $table_name WHERE RecordID IN($val2) GROUP BY`NoteTypeID`";
        $tmp_type_id_arr = $HITSDB->fetchAll($SQL);
        foreach($tmp_type_id_arr as $tmp_type_id_val){
          array_push($group_type_id_arr[$key2], $tmp_type_id_val['NoteTypeID']);
        }
      }  
    }  
  }else{
    if($frm_user){
      $user_full_name = get_userName($HITSDB, $frm_user);
      echo "No Hits for user $user_full_name within project $AccessProjectID";
    }else{
      echo "No Hits within project $AccessProjectID";
    }
  }
  
  $elementsArr = array();
  $max_mim_arr = array();
  $tage_max_mim_arr = array();
  $tmpOrderbyTageArr = array();
  if($selected_group_id){
    $tmpOrderbyTageArr = array();
    $table_name = $itemType."Group";    
    $SQL = "SELECT RecordID FROM $table_name WHERE `NoteTypeID`=$selected_group_id";
    $tmpArr = $HITSDB->fetchAll($SQL);
    $tmpStr = array_to_delimited_str($tmpArr, 'RecordID');
    if($tmpStr){    
      if($currentType == 'Band'){
        if($itemType == 'Bait'){
          $SQL = "SELECT `ID`, Location AS Name FROM `Band` WHERE `BaitID` IN($tmpStr) AND `ProjectID`='$AccessProjectID'";
        }elseif($itemType == 'Experiment'){
          $SQL = "SELECT `ID`, Location AS Name FROM `Band` WHERE `ExpID` IN($tmpStr) AND `ProjectID`='$AccessProjectID'";
        }elseif($itemType == 'Band'){
          $SQL = "SELECT `ID`, Location AS Name FROM `Band` WHERE `ID` IN($tmpStr) AND `ProjectID`='$AccessProjectID'";
        }
      }elseif($currentType == 'Exp'){
        if($itemType == 'Bait'){
          $SQL = "SELECT `ID`, Name FROM `Experiment` WHERE `BaitID` IN($tmpStr) AND `ProjectID`='$AccessProjectID'";
        }elseif($itemType == 'Experiment'){
          $SQL = "SELECT `ID`, Name FROM `Experiment` WHERE `ID` IN($tmpStr) AND `ProjectID`='$AccessProjectID'";
        }  
      }elseif($currentType == 'Bait'){
        $SQL = "SELECT `ID`, GeneName AS Name FROM `Bait` WHERE `ID` IN($tmpStr) AND `ProjectID`='$AccessProjectID'";
      }
      if($tmpArr = $HITSDB->fetchAll($SQL)){
        if($frm_search_by){
          foreach($tmpArr as $tmp_val){
            $pos = strpos(strtoupper($tmp_val['Name']), strtoupper($frm_search_by));
            if($pos !== false){
              array_push($tmpOrderbyTageArr, $tmp_val['ID']);
            }
          }
        }else{  
          array_to_array($tmpArr, 'ID', $tmpOrderbyTageArr);
        }  
      }
    }
    $OrderbyTageArr = array_intersect($tmpOrderbyTageArr, $tmpElementIdArr);
    $OrderbyTageStr = implode(",", $OrderbyTageArr);
    $startPoint = ($currentPage - 1) * $elementsPerPage;
    $max_mim_arr = get_source_elements_arr($elementsArr,$OrderbyTageStr,$startPoint,$elementsPerPage);
    $totalElements = count($OrderbyTageArr);
  }else{
    $startPoint = ($currentPage - 1) * $elementsPerPage;
    $max_mim_arr = get_source_elements_arr($elementsArr,$tmpElementIdStr,$startPoint,$elementsPerPage);
    $totalElements = count($tmpElementIdArr);
  }
  if($selected_id_str_new){
    $selectedElementArr = explode(',',$selected_id_str_new);
  }else{
    $selectedElementArr = array();
  }
  $optionStr = '';
   
  foreach($elementsArr as $elementsValue){
    if(in_array($elementsValue['ID'], $selectedElementArr)) continue;
    $initial_str = '';
    if(isset($has_notes_itemID_arr[$elementsValue['ID']])){
      foreach($has_notes_itemID_arr[$elementsValue['ID']] as $tmpTypeID){
        $VS = '';
        $tmp_version_num = $bait_group_icon_arr[$tmpTypeID]['Initial'];
        if(is_numeric($tmp_version_num))  $VS = 'VS';
        $initial_str .= "[".$VS.$tmp_version_num."]";
      }
    }
    $baitTag = '';
    if($elementsValue['Tag'] && $elementsValue['Mutation']){
      $baitTag = "(".$elementsValue['Tag'].";".$elementsValue['Mutation'].")";
    }elseif($elementsValue['Tag']){
      $baitTag = "(".$elementsValue['Tag'].")";
    }elseif($elementsValue['Mutation']){
      $baitTag = "(".$elementsValue['Mutation'].")";
    }
  	if($currentType == 'Bait'){
  	  $optionStr .= "<option id='".$elementsValue['ID']."'>".$elementsValue['ID']."&nbsp; &nbsp;".escapeSpace($elementsValue['GeneName']).$baitTag."&nbsp; &nbsp;".$elementsValue['BaitAcc']."&nbsp; &nbsp;".$initial_str."</option>\n";
  	}elseif($currentType == 'Exp'){
      $optionStr .= "<option id='".$elementsValue['ID']."'>".$elementsValue['BaitID']."&nbsp; &nbsp;".$elementsValue['GeneName'].$baitTag."&nbsp; &nbsp;".$elementsValue['ID']."&nbsp; &nbsp;".$elementsValue['Name']."&nbsp; &nbsp".$initial_str."</option>\n";
    }elseif($currentType == 'Band'){
  		$gellStr = '';
  		if($elementsValue['GelID']){
  			if(!$hasGel) $hasGel = 1;
  			$gellStr = $elementsValue['GelID']."&nbsp; &nbsp;".$elementsValue['LaneNum']."&nbsp; &nbsp;";
  		}
      $optionStr .= "<option id='".$elementsValue['ID']."'>".$elementsValue['BaitID']."&nbsp; &nbsp;".$elementsValue['GeneName'].$baitTag."&nbsp; &nbsp;".$elementsValue['ID']."&nbsp; &nbsp;".$elementsValue['Location']."&nbsp; &nbsp;".$gellStr.$initial_str."</option>\n";
    }	
  }
  
  ($hasTage)?$tagLable='':$tagLable='';
  if($currentType == 'Bait'){
		$sourceTitle = "BaitID GeneName(Tag) ProteinID $tagLable";
  }elseif($currentType == 'Exp'){
    $sourceTitle = "BaitID GeneName(Tag) ExpID ExpName $tagLable";
  }elseif($currentType == 'Band'){
		if($hasGel){
			$sourceTitle = "BaitID GeneName(Tag) SampleID SampleName GellID LaneNum $tagLable";
		}else{
			$sourceTitle = "BaitID GeneName(Tag) SampleID SampleName $tagLable";
		}
	}
  $frm_min = $max_mim_arr['min']; 
  $frm_max = $max_mim_arr['max'];
  if($jointed){
    $frm_tage_min = $tage_max_mim_arr['min'];
    $frm_tage_max = $tage_max_mim_arr['max'];
  }
  ?>
  @@**@@source_target@@**@@<td width="33%" BGCOLOR="<?php echo $bg_tb;?>" align=center>
  <div class=sss2><?php echo $sourceType;?></div> 
  <div id='sourceTitle' class=sss><?php echo $sourceTitle;?></div>
    <select ID="frm_sourceList" name="frm_sourceList" size="20" multiple>
      <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
  		<?php echo $optionStr;?>
  	</select ><br><br>
     
    <input type='hidden' id='sourceTitleTxt' name='sourceTitleTxt' value='<?php echo $sourceTitle;?>'>
  	<input type='hidden' id='frm_max' name='frm_max' value='<?php echo $frm_max;?>'>
  	<input type='hidden' id='frm_min' name='frm_min' value='<?php echo $frm_min;?>'>
    <input type='hidden' id='frm_tage_max' name='frm_tage_max' value='<?php echo $frm_tage_max;?>'>
  	<input type='hidden' id='frm_tage_min' name='frm_tage_min' value='<?php echo $frm_tage_min;?>'>
    <input type='hidden' id='currentPage' name='currentPage' value='<?php echo $currentPage;?>'>
    <input type='hidden' id='currentType' name='currentType' value='<?php echo $currentType;?>'>
    <input type='hidden' id='hasGel' name='hasGel' value='<?php echo $hasGel;?>'>
    <input type='hidden' id='hasTage' name='hasTage' value='<?php echo $hasTage;?>'>
    <input type='hidden' id='isJointedPage' name='isJointedPage' value='<?php echo $isJointedPage;?>'>
    <input type='hidden' id='offset' name='offset' value='<?php echo $offset;?>'>
    <input type='hidden' id='offset' name='itemType' value='<?php echo $itemType;?>'>
    <?php 
      $pageLable = create_page_lable($totalElements);
      echo $pageLable;
    ?>
  <center>
  <table border=0 cellspacing="2" cellpadding=2 width="320">
  <?php if($currentType == 'Band'){
      $search_by_lable = "Search <br>&nbsp;sample name";
      $item_table = "Band";
    }elseif($currentType == 'Exp'){
      $search_by_lable = "Search <br>&nbsp;experiment name";
      $item_table = "Experiment";
    }elseif($currentType == 'Bait'){
      $search_by_lable = "Search <br>&nbsp;gene name";
      $item_table = "Bait";
    }
  ?>
    <tr>
      <td align=left>User:</td>
      <td width="75%">
        <select name="frm_user">
          <option value=''>All users
        <?php 
          $SQL = "SELECT 
                  OwnerID
                  FROM $item_table
                  WHERE ProjectID='$AccessProjectID'
                  Group by OwnerID";
          $OwnerID_obj = $HITSDB->fetchAll($SQL);
          $users_id_str = array_to_delimited_str($OwnerID_obj,'OwnerID');
          if($users_id_str){
            $SQL = "SELECT `ID`,`Fname`,`Lname` FROM `User` WHERE `ID` IN($users_id_str) ORDER BY `Fname`";
            $users_id_name_arr = $PROHITSDB->fetchAll($SQL);
            foreach($users_id_name_arr as $users_val){
              if(!$users_val['ID'] || (!$users_val['Fname'] && !$users_val['Lname'])) continue;
              echo "<option value='".$users_val['ID']."'".(($frm_user==$users_val['ID'])?' selected':'').">".$users_val['Fname']." ".$users_val['Lname']."</option>\r\n";
            }
          }  
        ?>
        </select>
      </td>
    </tr>  
    <tr>
      <td align=left>Search:</td>
      <td>
        <input type="text" name="frm_search_by" value="<?php echo $frm_search_by?>">
      </td>
    </tr>  
  <?php if($currentType == 'Band' || $currentType == 'Exp'){?>  
    <tr>
      <td align=left>Group type:</td>
      <td>
    <?php if($currentType == 'Band'){?> 
          <input type="radio" name="frm_groups" value="Bait" onClick="toggle_group(this.form)" <?php echo (($frm_groups=='Bait')?'checked':'')?>>Bait&nbsp;
        	<input type="radio" name="frm_groups" value="Experiment" onClick="toggle_group(this.form)" <?php echo (($frm_groups=='Experiment')?'checked':'')?>>Experiment&nbsp;
        	<input type="radio" name="frm_groups" value="Band" onClick="toggle_group(this.form)" <?php echo (($frm_groups=='Band')?'checked':'')?>>Sample&nbsp;
    <?php }elseif($currentType == 'Exp'){?>
          <input type="radio" name="frm_groups" value="Bait" onClick="toggle_group(this.form)" <?php echo (($frm_groups=='Bait')?'checked':'')?>>Bait&nbsp;
        	<input type="radio" name="frm_groups" value="Experiment" onClick="toggle_group(this.form)" <?php echo (($frm_groups=='Experiment')?'checked':'')?>>Experiment&nbsp;
    <?php }?>     
       </td>
    </tr>
  <?php }?>
    <tr><td rowspan=1>Show group:</td>
    <td>
  <?php foreach($item_group_icon_arr as $item_key => $item_val){
      //if($item_key == 'Export') continue;
      $group_arr = $item_val;
      $selection_name = $item_key."_order_by";
  ?>
    
    <div id='<?php echo $item_key?>' STYLE="<?php echo ($item_key==$itemType)?'display: block':'display: none'?>">
    <table border=0 cellspacing="0" cellpadding=0>
    <tr>
      <td>
      <select name="<?php echo $selection_name?>">
      <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
    <?php foreach($group_arr as $group_key => $group_val){
        if(!array_key_exists($item_key, $group_type_id_arr)) continue;
        if(!in_array($group_key, $group_type_id_arr[$item_key])) continue;
        $VS = '';
        if(is_numeric($group_val['Initial'])) $VS = 'VS';
        echo "<option value='$group_key'".(($selected_group_id==$group_key)?'selected':'').">".$group_val['Name']." ($VS".$group_val['Initial'].")</option>\r\n";
      }
    ?>
      </select>
      </td>
    </tr>
    </table>
    </div>
  <?php }?>
    </td>
    </tr>
    <tr><td align=left>   
    Sort by:
    </td>
    <td>
    <select id="frm_order_by" name="frm_order_by">
      <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
  <?php if($currentType == 'Bait'){?>
      <option value='ID' <?php echo ($frm_order_by=='ID')?'selected':''?>>BaitID</option>
      <option value='GeneName' <?php echo ($frm_order_by=='GeneName')?'selected':''?>>Gene Name</option>
      <option value='BaitAcc' <?php echo ($frm_order_by=='BaitAcc')?'selected':''?>>Protein ID</option>
  <?php }elseif($currentType == 'Exp'){?>
      <option value='E.BaitID' <?php echo ($frm_order_by=='E.BaitID')?'selected':''?>>BaitID</option>
      <option value='B.GeneName' <?php echo ($frm_order_by=='B.GeneName')?'selected':''?>>Gene Name</option>
      <option value='E.ID' <?php echo ($frm_order_by=='E.ID')?'selected':''?>>Exp ID</option>    
      <option value='E.Name' <?php echo ($frm_order_by=='E.Name')?'selected':''?>>Exp Name</option>
  <?php }elseif($currentType == 'Band'){?>
      <option value='D.BaitID' <?php echo ($frm_order_by=='D.BaitID')?'selected':''?>>Bait ID</option>
      <option value='D.ID' <?php echo ($frm_order_by=='D.ID')?'selected':''?>>Sample ID</option>
      <option value='D.Location' <?php echo ($frm_order_by=='D.Location')?'selected':''?>>Sample Name</option>
      <option value='B.GeneName' <?php echo ($frm_order_by=='B.GeneName')?'selected':''?>>Gene Name</option>
      <?php if($hasGel){?> 
        <option value='L.GelID' <?php echo ($frm_order_by=='L.GelID')?'selected':''?>>Gel ID</option>
      <?php }?>
  <?php }?>
    </select>
    </td>
    </tr>
    <?php if($currentType == 'Bait'){
        $sort_lable = 'Sort bait list';
      }elseif($currentType == 'Exp'){
        $sort_lable = 'Sort experiment list';
      }elseif($currentType == 'Band'){
        $sort_lable = 'Sort sample list';
      }
    ?>
    <tr>
      <td colspan=2 align=center>
      <input type=button name='sort' value=' Go ' onClick="startRequest('changeOrderby','')">
      </td>
    </tr>
    </table>
    </center>
    <br><br>
	@@**@@<select id="frm_selected_list" name="frm_selected_list" size=20 multiple>
         <option id='' value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
		<?php echo $sele_optionStr;?>		  
  </select><br><br><center>&nbsp;&nbsp;
  Sort by:
  <select id="frm_sel_order_by" name="frm_sel_order_by" onChange="sort_selected_list('<?php echo $currentType?>')">
      <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
  <?php if($currentType == 'Bait'){?>
      <option value='0'>BaitID</option>
      <option value='1'>Gene Name</option>
      <option value='2'>Protein ID</option>
  <?php }elseif($currentType == 'Exp'){?>
      <option value='0'>BaitID</option>
      <option value='1'>Gene Name</option>
      <option value='2'>Exp ID</option>    
      <option value='3'>Exp Name</option>
  <?php }elseif($currentType == 'Band'){?>
      <option value='0'>Bait ID</option>
      <option value='1'>Gene Name</option>
      <option value='2'>Sample ID</option>
      <option value='3'>Sample Name</option>
  <?php }?>
    </select></center><br>@@**@@<?php echo ($subAction)?$subAction:"list_sub_bands";?>@@**@@<?php include("filter_interface.php");?>@@**@@<?php echo $update_filter_box?>
	<?php 
}

function time_interval($start_time,$note){
  $end_time = time();
  $time_interval = $end_time - $start_time;
  echo "<br>$time_interval--------$note-------------".@date("H:i:s")."------<br>";
  flush();
  return $end_time;
}
?>
