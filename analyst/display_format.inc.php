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


///////////////// processing ajax post ///////////////////
require_once("../common/site_permission.inc.php");
require_once("analyst/common_functions.inc.php");

if(isset($formatAction)){
	$the_format = array();
	if(isset($frm_formatID) and $frm_formatID){
  	$SQL = "select ID, Name, User, Format from ExportFormat where ID='".$frm_formatID."'";
  	$the_format = $HITSDB->fetch($SQL);
	}
	if($formatAction == 'add'){
		$SQL = "select ID from ExportFormat where Name='".$frm_new_format_name."' and Type='$Type' and ProjectID='".$AccessProjectID."'";
		if($HITSDB->exist($SQL)){
			echo "Error:The set name '$frm_new_format_name' exists. Please change the set name";exit;
		}else{
			$SQL = "insert into ExportFormat set 
			  `Name`='".$frm_new_format_name."',
			  `Type`='$Type',
			  `User`='".$USER->ID."',
			  `Date`=now(),
			  `ProjectID`='".$AccessProjectID."',
			  `Format`='".$frm_new_format."'";
			$rt = $HITSDB->insert($SQL);
			if(is_numeric($rt)){
				echo "Added:$rt,$frm_new_format_name,". $USER->Fname . " " . $USER->Lname;
			}else{
				echo "Error:$rt";
			}
		}
	}else if($formatAction == 'modifySet' and $the_format){
		if($the_format['User'] == $USER->ID or $USER->Type == 'Admin'){
			$SQL = "update ExportFormat set 
			  `Date`=now(),
			  `Format`='".$frm_new_format."' where ID='".$the_format['ID']."'";
			$rt = $HITSDB->update($SQL);
			if(is_numeric($rt)){
				echo "Modified:";
			}else{
				echo "Error:$rt";
			}
		}
	}else if($formatAction == 'changeSet'){
		if($the_format){
			 $set_user = $PROHITSDB->fetch("select Fname, Lname from User where ID='".$the_format['User']."'");
			 echo "ChangeSet:";
			 echo implode(":", $the_format);
			 if($set_user){
			 		echo ":".$set_user['Fname']." ". $set_user['Lname'];
			 }
			 if($the_format['User'] == $USER->ID or $USER->Type == 'Admin'){
			 		echo ":Modify";
			 }
		}
	}else if($formatAction == 'deleteSet' and $the_format){
		if($the_format['User'] == $USER->ID or $USER->Type == 'Admin'){
			$SQL = "delete from ExportFormat where ID='".$the_format['ID']."'";
			$HITSDB->delete("ExportFormat", $the_format['ID']);
			echo "Deleted:";
		}
	}else if(($formatAction == 'AddComparision' or $formatAction =='RemoveComparision') and $IDs and $Type){
		$add = ($formatAction == 'AddComparision')?'add':'remove';
		echo "Session:";
		edit_comparison_session($IDs, $Type, $add);
	}
	exit;
}
///////////////////////////////////////////////////////

$ajax_action = 'display_format.inc.php';
$selected_option_array = array(); 
$Formats = array();
$displayFormat = array();
$Type = '';


if($SCRIPT_NAME == "bait.php"){
  //$session_comparsion_arr = get_comparison_session("Bait", 'array');
  $Type = "Bait";
	$columns_option_array = array(
		'B.GeneID'=>"Bait GeneID",
		'B.LocusTag'=>"Bait LocusTag",
		'B.BaitAcc'=>"Bait Protien",
		'B.AccType'=>"Bait ProteinType",
		'B.Tag'=>"Bait Tag",
		'B.Mutation'=>"Bait Mutation",
		'B.Clone'=>"Bait Clone",
		'B.Vector'=>"Bait Vector",
		'B.OwnerID'=>"Bait User"
		);
		
		$selected_option_default_array = array(
			'ID'=>"BaitID",
			'GeneName'=>"BaitGeneName"
		);
		$SQL = "select ID, Name, User, Format from ExportFormat where Type='Bait' and ProjectID='".$AccessProjectID."' order by ID";
		$Formats = $HITSDB->fetchAll($SQL);
		if(!isset($frm_displayFormatID)){
			$frm_displayFormatID = '';
		}
		foreach($Formats as $tmp_format){
		  if(!$frm_displayFormatID){
				if($tmp_format['User'] == $USER->ID){
					$displayFormat = $tmp_format;
				}
			}else if($frm_displayFormatID == $tmp_format['ID']){
				$displayFormat = $tmp_format;
			}
		}
		
		if(!isset($displayFormat) and $Formats){
			$frm_displayFormatID = $Formats[0]['ID'];
			$displayFormat = $Formats[0];
		}else if($displayFormat){
			$frm_displayFormatID = $displayFormat['ID'];
		}
//----------------------------------------------------------------------------------------    
}else if($SCRIPT_NAME == "experiment_show.php"){
  //$session_comparsion_arr = get_comparison_session("Exp", 'array');
  $Type = "Exp";
	$columns_option_array = array(
		'E.BaitID'=>"Bait ID",
		'EB.GeneID'=>"Bait GeneID",
		'EB.GeneName'=>"Bait GeneName",
		'EB.Tag'=>"Bait Tag",
		'EB.Mutation'=>"Bait Mutation",
		'EB.Clone'=>"Bait Clone",
		'E.OwnerID'=>"Experiment User",
    'E.GrowProtocol'=>'Biological Material',
    'E.IpProtocol'=>'Affinity Purification',
    'E.DigestProtocol'=>'Peptide Preparation',
    'E.PeptideFrag'=>'LC-MS',
		'E.DateTime'=>"Experiment Date"
		);
	$selected_option_default_array = array(
			'ID'=>"Experiment ID",
			'Name'=>"Experiment Name"
		);
	$SQL = "select ID, Name, User, Format from ExportFormat where Type='Exp' and ProjectID='".$AccessProjectID."' order by ID";
  $Formats = $HITSDB->fetchAll($SQL);
  if(!isset($frm_displayFormatID)){
  	$frm_displayFormatID = '';
  }
  foreach($Formats as $tmp_format){
    if(!$frm_displayFormatID){
  		if($tmp_format['User'] == $USER->ID){
  			$displayFormat = $tmp_format;
  		}
  	}else if($frm_displayFormatID == $tmp_format['ID']){
  		$displayFormat = $tmp_format;
  	}
  }
  if(!isset($displayFormat) and $Formats){
  	$frm_displayFormatID = $Formats[0]['ID'];
  	$displayFormat = $Formats[0];
  }else if($displayFormat){
  	$frm_displayFormatID = $displayFormat['ID'];
  } 
//----------------------------------------------------------------------------------------    
}else if($SCRIPT_NAME == "band_show.php"){
  //$session_comparsion_arr = get_comparison_session("Sample", 'array');
  $Type = "Sample";
	$columns_option_array = array(
		'S.BaitID'=>"Bait ID",
		'SB.GeneID'=>"Bait GeneID",
		'SB.GeneName'=>"Bait GeneName",
		'SB.Tag'=>"Bait Tag",
		'SB.Mutation'=>"Bait Mutation",
		'SB.Clone'=>"Bait Clone",
		'S.OwnerID'=>"Sample User",
		'S.DateTime'=>"Sample Date"
		);
	$selected_option_default_array = array(
			'ID'=>"SampleID",
			'Location'=>"SampleName"
		);
	$SQL = "select ID, Name, User, Format from ExportFormat where Type='Sample' and ProjectID='".$AccessProjectID."' order by ID";
  $Formats = $HITSDB->fetchAll($SQL);
  if(!isset($frm_displayFormatID)){
  	$frm_displayFormatID = '';
  }
  foreach($Formats as $tmp_format){
    if(!$frm_displayFormatID){
  		if($tmp_format['User'] == $USER->ID){
  			$displayFormat = $tmp_format;
  		}
  	}else if($frm_displayFormatID == $tmp_format['ID']){
  		$displayFormat = $tmp_format;
  	}
  }
  if(!isset($displayFormat) and $Formats){
  	$frm_displayFormatID = $Formats[0]['ID'];
  	$displayFormat = $Formats[0];
  }else if($displayFormat){
  	$frm_displayFormatID = $displayFormat['ID'];
  }
}

$SQL = "SELECT `ID`,ParentID, `Name` FROM `ExpDetailName`";
$exp_option_array = array();
$exp_optionID_name_array = array();
$project_exp_ID_array = array();
$all_exp_details_arr = $PROHITSDB->fetchAll($SQL);
foreach($all_exp_details_arr as $tmp_arr){
	if($tmp_arr['ParentID'] == '0'){
		array_push($exp_option_array, $tmp_arr);
	}
	$exp_optionID_name_array[$tmp_arr['ID']] = $tmp_arr;
}

$SQL = "SELECT `SelectionID` from ExpDetailProject where ProjectID='".$AccessProjectID."'";
$exp_arr = $PROHITSDB->fetchAll($SQL);
foreach($exp_arr as $tmp_arr){
	array_push($project_exp_ID_array, $tmp_arr['SelectionID']);
}

?>
<script src="../common/javascript/prohits.divDropDown.js" type="text/javascript"></script>
<script type="text/javascript">
var sel_disFormatID;
var objMsg;
var sel_from;
var sel_to;
var sel_formatID;
var text_fromatName;
var objText;
function init_objs(){
	sel_disFormatID = document.getElementById("frm_displayFormatID");
	objMsg       = document.getElementById("format_msg");
	sel_from     = document.getElementById("frm_unselected_columns");
	sel_to       = document.getElementById("frm_selected_columns");
	sel_formatID = document.getElementById("frm_formatID");
	text_fromatName = document.getElementById("frm_new_format_name");
	objText 		 = document.getElementById("frm_new_format_name");
}
function save_format(theaction){
  var queryString = '';
	if(!check_formate_form(theaction)){
		return;
	}
	if(theaction == 'add'){
	  queryString = "Type=<?php echo $Type;?>";
		queryString += "&frm_new_format_name=" + escape(objText.value);
	}else{
	 	var formatID = sel_formatID.options[sel_formatID.selectedIndex].value;
		queryString = "frm_formatID="+formatID;
	}
	queryString += "&formatAction="+theaction;
	var selected_str = '';
	for (var i=0; i<sel_to.options.length; i++) {
	  if(selected_str.length > 0){
			selected_str +=",";
		}
		selected_str += sel_to.options[i].value;
	}
	if(selected_str.length > 0){
		selected_str = escape(selected_str);
		queryString += "&frm_new_format="+selected_str;
	}
	if(queryString.length > 0){
		ajaxPost('<?php echo $ajax_action;?>', queryString);
	}
}
function processAjaxReturn(rp){
  rp = trimString(rp);
  var ret_html_arr = rp.split("@@**@@");
  if(ret_html_arr.length == 2){
    var div_id = trimString(ret_html_arr[0]);
    document.getElementById(div_id).innerHTML = ret_html_arr[1];
    return;
  } 
  if(rp.match(/^Error:/)){
		alert(rp.replace("Error:","") );
	}else if(rp.match(/^Added:/)){
		var tmp = rp.replace("Added:","");
		var text_value = tmp.split(",");
		addToEnd(sel_formatID, text_value[1], text_value[0]);
		addToEnd(sel_disFormatID, text_value[1], text_value[0]);
		sel_formatID.options[sel_formatID.options.length - 1].selected = true;
		showhideDiv('newLable', 'NewFormat');
		objMsg.innerHTML = "added by: " + text_value[2];
		text_fromatName.value = '';
		addDeleteModify(objMsg);
	}else if(rp.match(/^ChangeSet:/)){
		var tmp = rp.replace("ChangeSet:","");
		var set_arr = tmp.split(":");
		if(set_arr.length > 4){
			removeAll(sel_to, sel_from);
			objMsg.innerHTML = "Added by:<br>" + set_arr[4];
			if(set_arr.length == 6){
				addDeleteModify(objMsg);
			} 
			var cols = set_arr[3].split(",");
			for (var i=0; i<cols.length; i++) {
				var o = getOption(sel_from, cols[i]);
				o.selected=true;
				moveOption(sel_from, sel_to);
			}
		}
	}else if(rp.match(/^Deleted:/)){
	}else if(rp.match(/^Modified:/)){
	 	objMsg.innerHTML = "The set was updated.<br><br>" + objMsg.innerHTML;
	}else if(rp.match(/^Session:/)){
		//do nothing
	}else{
		alert("debug: '"+rp);
	}
}
function addDeleteModify(objMsg){
	objMsg.innerHTML += "<br><br><a href=\"javascript:save_format('modifySet')\" class=button>[save]</a>";
	objMsg.innerHTML += "<a href=\"javascript:delete_format()\" class=button>[delete]</a>";
}

function delete_format(){
	if(confirm("Are you sure that you want to delete the pre-defined set?")){
		 var formatID = sel_formatID.options[sel_formatID.selectedIndex].value;
		 var del_index = sel_formatID.selectedIndex;
		 sel_formatID.remove(del_index);
		 sel_disFormatID.remove(del_index);
		 if(sel_formatID.options.length > 0){
		 		sel_formatID.options[0].selected = true;
				changeFormatSet(sel_formatID);
		 }
		 ajaxPost('<?php echo $ajax_action;?>', "formatAction=deleteSet&frm_formatID="+formatID);
	}
}
function check_formate_form(theaction){
	var rt = true;
	if(theaction == 'add'){
		if(isEmptyStr(objText.value)){
			alert("Please type a format name!");
			rt = false;
		}else{
		  if(!onlyAlphaNumerics(objText.value, 2)){
				alert("Only 'A-Za-z0-9_' allowed for a set name.");
				rt = false;
			}
		}
	}
	if(rt && sel_to.options.length < 1){
		alert("Please select columns.");
		rt = false;
	}
	 
	return rt;
}
function changeFormatSet(sel){
  if(sel.options.length <1) return;
  var formatID = sel.options[sel.selectedIndex].value;
	for(var i=0; i<sel_formatID.options.length; i++){
		if(sel_formatID.options[i].value == formatID){
			sel_formatID.options[i].selected = true;
		}
	}
	if(formatID){
		loadFormatSet(formatID);
	}
	showhideDiv('newLable', 'NewFormat');
}
function loadFormatSet(formatID){
  ajaxPost('<?php echo $ajax_action;?>', "formatAction=changeSet&frm_formatID="+formatID);
}
function formatDetail(){ 
	init_objs();
	changeFormatSet(sel_disFormatID);
	DropDown($('#tppMerge'));
}
function RefreshPage(theForm){
  theForm.theaction.value = 'viewall';
  theForm.submit();
}
</script>
&nbsp; 
<font face="helvetica,arial,futura" size="2"><b>Column Display Set</b> 
<select name="frm_displayFormatID" ID="frm_displayFormatID" onChange="RefreshPage(this.form)"></font>
<?php 
foreach($Formats as $tmp_format){
  $is_selected = '';
  if(isset($frm_displayFormatID) and $frm_displayFormatID == $tmp_format['ID']){
		$is_selected = " selected";
	}
	echo "<option value='".$tmp_format['ID']."' $is_selected>".$tmp_format['Name']."\n";
}
?>
</select> 
<a href="javascript:formatDetail()" title='edit display set' ><img src="./images/icon_view.gif" border="0"></a>
<center>
<!----- pop merging table --------->
    <DIV id='tppMerge' style="LEFT: 300px; TOP: 50px; display: none; POSITION: absolute;  width:620; Height:360;BACKGROUND-color:#ded398; border: black solid 1px;">
    <br>
    <b><font size="+1">Select Columns To Display</font></b>
    <DIV style="width:600px;
  	height:270px;
  	overflow:auto;
  	border: black solid 1px;
  	font-family: tahoma;
  	font-size:12px;
  	position:relative;
  	background-color:#e0e0e0;">
		<br>
		select the columns you want to display from bait and experiment information. Following information will be in front of columns: 
		<?php 
		foreach($selected_option_default_array as $key=>$value){
				echo $value.", ";
		}
		?>
    <br><br>
		
    <table BORDER=0 width=97% cellspacing=2 cellpadding=1>
      <tbody id="popTable">
    <tr>
      <td bgcolor="#bcbcbc" align=center><font size=2>Undisplayed Columns</font></td>
			<td>&nbsp;</td>
      <td bgcolor="#bcbcbc" align=center><font size=2>Columns to Display</font></td>
			<td bgcolor="#bcbcbc" align=center><font size=2>Pre-Defined</font></td>
		</tr>
		<tr>
			<td bgcolor="#bcbcbc" align=center>
			<select name="frm_unselected_columns" size="10" ID="frm_unselected_columns">
			<?php 
			foreach($columns_option_array as $key=>$value){
					echo "<option value='".$key."'>".$value."\n";
		  }
			foreach($exp_option_array as $value){
				if(in_array($value['ID'], $project_exp_ID_array)){
					echo "<option value='E.".$value['ID']."'>Exp.".$value['Name']."\n";
				}
			}
			?>
			</select>
			</td>
			<td align=center><input type="button" name="frm_add" value=" >> " onClick="moveOption(this.form.frm_unselected_columns, this.form.frm_selected_columns)";>
			    <br><br>
			    <input type="button" name="frm_remove" value=" << " onClick="moveOption(this.form.frm_selected_columns, this.form.frm_unselected_columns)";>
			</td>
      <td bgcolor="#bcbcbc" align=center> 
			<select name="frm_selected_columns" size="10" ID="frm_selected_columns">
			</select>
			</td>
			<td valign=top bgcolor="#bcbcbc"><br>
			<select name="frm_formatID" ID="frm_formatID" onChange="changeFormatSet(this)";>
			<?php 
			foreach($Formats as $tmp_format){
				echo "<option value='".$tmp_format['ID']."'>".$tmp_format['Name']."\n";
			}
			?>
			</select><br>
			<DIV ID="newLable" style="display:block; font-size:12px">
				<DIV ID="format_msg" style="display:block; font-size:12px"></DIV>
				<a href="javascript:showhideDiv('NewFormat', 'newLable')" class=button>[new]</a>
			</DIV>
			<br>
			<DIV ID="NewFormat" style="display:none; font-size:12px">
			new set name:<br>
			<input type="text" name="frm_new_format_name" size="20" maxlength="20" ID="frm_new_format_name">
			<br>
			<a href="javascript:save_format('add')" class=button>[save]</a>
			<a href="javascript:showhideDiv('newLable', 'NewFormat')" class=button>[reset]</a>
			</DIV>
			
			</td>
		</tr>
      </tbody>
    </table>
    </DIV>
		<br>
    <input type=button value='Close' onClick="$('#tppMerge').slideUp(200); ">
    </DIV>
 <!----- end of the pop merging table ---->
</center>