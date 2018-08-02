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

$sub = '';
$theaction = '';
$frm_date1 = '2000-01-01';
$frm_date2 = '';
$frm_user_id = '';
$frm_groups = 'Bait';
$group_type = 'Bait';
$frm_search_hit_gene = '';
$frm_TaxID = '';
$frm_search_str = '';

require("../common/site_permission.inc.php");
include("analyst/common_functions.inc.php");
require("common/common_fun.inc.php");
require("analyst/classes/dateSelector_class.php");
$DateSelector = new DateSelector();

if($theaction == "change_group_type"){
  change_group_type($group_type);
  exit;
}
require("site_header.php");

if($frm_search_hit_gene){
  $user_accessed_projects_arr = array();
  $SQL = "SELECT `ProjectID` 
          FROM `ProPermission` 
          WHERE `UserID`='$AccessUserID' 
          ORDER BY ProjectID";
  $tmp_ProPermission_arr = $PROHITSDB->fetchAll($SQL);
  $user_accessed_projects_arr = array();
  foreach($tmp_ProPermission_arr as $tmp_ProPermission_val){
    array_push($user_accessed_projects_arr, $tmp_ProPermission_val['ProjectID']);
  }
  $project_id_name_arr = get_project_id_name_arr();
  $selected_arr = array();
  array_push($selected_arr, $AccessProjectID);
}

?>
<STYLE type="text/css">  
td { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
.st1 {
  display: block;
  border: black solid 1px;
  width: 700px;
  color: black;
  background-color: white;
}
#gdir {
	width:500px;
	height:480px;
	overflow:auto;
	border: black solid 1px;
	font-family: tahoma;
	font-size:12px;
	position:relative;
	background-color : #ffffff;
}
</STYLE>
<script type="text/javascript"> 
function check_search(){
  var theForm = document.getElementById('adForm');
  if(isEmptyStr(theForm.frm_expDetail_str.value) && isEmptyStr(theForm.frm_search_str.value)){
    alert('Please enter value in Word(s) box or select experiment information.');
    return false;
  }
  if(!onlyAlphaNumerics(theForm.frm_search_str.value,5) && !isEmptyStr(theForm.frm_search_str.value)){
    alert("Only these characters A-Z, a-z, 0-9, +, -, _,  and space are valid.");
    return false;
  }
  theForm.submit();
  //return true;
}
function removeDate(){
  var theForm = document.getElementById('adForm');
  theForm.frm_date1.value = '';
  theForm.frm_date2.value = '';
  var obj = document.getElementById('frm_date_str');
  obj.value = '';
  hideTip('date_div');
}
function removeExpDetail(){
  document.getElementById('frm_expDetail_str').value = '';
  document.getElementById('frm_expDetail_dis').value = '';
}
function passDate(){
  var theForm = document.getElementById('adForm');
  var dateObj = document.getElementById('frm_date_str');
  var sleY1 = theForm.frm_datefrom_Year;
  var sleM1 = theForm.frm_datefrom_Month;
  theForm.frm_date1.value = sleY1.options[sleY1.selectedIndex].value + "-" + check_month(sleM1.options[sleM1.selectedIndex].value);
  var sleY2 = theForm.frm_dateto_Year;
  var sleM2 = theForm.frm_dateto_Month;
  theForm.frm_date2.value = sleY2.options[sleY2.selectedIndex].value + "-" + check_month(sleM2.options[sleM2.selectedIndex].value);
  dateObj.value = theForm.frm_date1.value + " To " + theForm.frm_date2.value;
  hideTip('date_div');
}
function check_month(str){
  if(str.length == 1){
    str = "0" + str;
  }
  return str;
}

function toggle_group_for_search(theForm){
  var groups = theForm.frm_groups;
  for(var i=0; i<groups.length; i++){
    var group_obj = document.getElementById(groups[i].value);
    if(groups[i].checked == true){
      group_obj.style.display = "block";
    }else{
      group_obj.style.display = "none";
    }
  }
}
function change_group_type(theForm){
  var groups = theForm.frm_groups;
  var group_type = '';
  for(var i=0; i<groups.length; i++){
    if(groups[i].checked == true){
      group_type = groups[i].value;
      break;
    }
  }
  queryString = "theaction=change_group_type&group_type=" + group_type;
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}
function processAjaxReturn(ret_html){
  document.getElementById("group_block").innerHTML = ret_html;
}

function is_search_hit_gene(theForm){
  if(theForm.frm_search_hit_gene.checked == true){
    theForm.frm_search_hit_gene.value = 'Y';
  }else{
    theForm.frm_search_hit_gene.value = '';
  }
  theForm.action = "<?php echo $PHP_SELF?>";
  theForm.submit();
}

function search_hit_gene(theForm){
  var selected_list = theForm.frm_selected_list;
	var selected_str = '';
  var search_gene_str = theForm.frm_search_str.value;
  if(!onlyAlphaNumerics(search_gene_str,12) && !isEmptyStr(search_gene_str)){
    alert("Only these characters ,; A-Z; a-z; 0-9; +; -; _  and space are valid.");
    return false;
  }
	for (var i=0; i<selected_list.length; i++) {
	  if(selected_str.length > 0){
			selected_str +=",";
		}
		selected_str += selected_list.options[i].value;
	}
	if(selected_str.length == 0){
    alert('Please select project(s).')
    return false;
  }
  var addwildcard = theForm.frm_addwildcard
  for(var i=0; i<addwildcard.length; i++){
    if(addwildcard[i].checked && addwildcard[i].value == 'end'){
      search_gene_arr = search_gene_str.split(",");
      for(var j=0; j<search_gene_arr.length; j++){
        if(search_gene_arr[j].length < 3){
          alert("Please enter at least 3 characters.");
          return false;
        }
      }
    }
  }    
  theForm.selected_str.value = selected_str;
  theForm.theaction.value = 'create_basic_file';
  theForm.action = 'search_hit_gene_results.php';  
  theForm.submit();
}
</script>
<?php 
?>
<form id="adForm" name="qadForm" method='post' action="advanced_search_results.php">
<input type=hidden name=frm_date1 value=''>
<input type=hidden name=frm_date2 value=''>
<input type=hidden id=frm_expDetail_str name=frm_expDetail_str value=''>
<input type=hidden name=theaction value=''>
<table border="0" cellpadding="0" cellspacing="0" width="95%"> 
  <tr>
    <td align="left"><br>
		&nbsp; <font color="navy" face="helvetica,arial,futura" size="5"><b>Advanced Search
    <?php 
      if($AccessProjectName){
        echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font>";
      }
    ?>
    </b> 
		</font> 
	</td>
    <td align="right">
     &nbsp;
    </td>
  </tr> 
  <tr>
  	<td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
   <td><br>
    &nbsp; instructions <a id='instruction_a' href="javascript: toggle_group_description('instruction')" class=Button>[+]</a><br>
    <DIV id='instruction' STYLE="display: none">
    <ul>
<li>The Advanced Search function allows you to search for keywords (or combinations of keywords), and retrieve entries across the following categories: <b><em>Baits, Hits, Samples, Gels, Raw Files</em></b> and <b><em>Auto Search</em></b>.  
<li>Keywords (e.g. Gene Names, Protein descriptions, or File Names) can be searched with or without wildcards, placed at the front and/or end of the query (simply select the appropriate button below the query text window).  When entering several keywords, the user has the option to select "at least one of the words" , "all words" (irrespective of order), or "the exact phrase" options.  To search in the Protein Description field, you must select the "include description" button (in the Find section).  Importantly, the protein description is considered to be a single phrase: to search an individual word inside the field, specify "wildcards front and end".  Note that such searches may be considerably slower.  
<li>The Experimental Detail section allows you to search in Experimental Details via the use of project-specific controlled vocabulary.  Press the [select] button to browse through all available Experimental Details categories and values for the project, and [pass] the selections to the Advanced Search page.  The results may similarly be restricted by date. 
<li>If data is entered in both the Keyword and Experimental Detail fields, the default operation is "and".  
    </ul>
    </DIV> 
    </td>
  </tr>
  <tr>
    <td align="center">
    <DIV class="st1">  
    <table border="0" cellpadding="3" cellspacing="1" width=100% >
      <tr>
        <td bgcolor="#a9bbe7" colspan="2">
          <input type="checkbox" name="frm_search_hit_gene" value="Y" <?php echo (($frm_search_hit_gene)?'checked':'');?> onClick="is_search_hit_gene(this.form)">&nbsp;&nbsp;Search hits only from multiple projects&nbsp;
        </td>
      </tr>
      <tr>
        <td align="right" bgcolor="#cdcdcd" >
        <?php if(!$frm_search_hit_gene){        
            echo "Word(s) or value(s) to query:&nbsp;";
          }else{
            echo "Hit's gene name(s) to query:&nbsp;<br><font color=#008000>separate by ',' for more than one gene names</font>.";
          }
        ?>  
        </td>
        <td bgcolor="#eeeeee">
          <input type="text" name="frm_search_str" size="60" value="<?php echo $frm_search_str?>">
        </td>
      </tr>
      <tr>
          <td align="right" bgcolor="#cdcdcd" >
              Add wildcard:&nbsp;
          </td>
          <td bgcolor="#eeeeee">
              at the end <input type="radio" name="frm_addwildcard" value="end" <?php echo ((!$frm_search_hit_gene)?'checked':'');?>>&nbsp; &nbsp;
          <?php if(!$frm_search_hit_gene){?>  
              at the front<input type="radio" name="frm_addwildcard" value="front">&nbsp; &nbsp;
              front and end<input type="radio" name="frm_addwildcard" value="both">&nbsp; &nbsp;
          <?php }?>
              no wildcard<input type="radio" name="frm_addwildcard" value="" <?php echo (($frm_search_hit_gene)?'checked':'');?>>&nbsp; &nbsp;
          </td>
      </tr>
      <tr><td colspan="2" bgcolor="#eeeeee">&nbsp;</td>
      </tr>
  <?php if($frm_search_hit_gene){?>
      <input type='hidden' name='selected_str' value=''>
      <input type='hidden' name='project_info' value='<?php echo $AccessProjectID.','.$AccessProjectName.','.$AccessProjectTaxID.','.$AccessProjectSetID.','.$AccessProjectFrequency.','.$AccessDBname?>'>
      <tr>
        <td colspan="2" bgcolor="#eeeeee">
        <div STYLE="width:100%;display: block;border: #a0a7c5 solid 0px;background-color:#a0a7c5;">
        <table border="0" cellpadding="3" cellspacing="1" width=100% >
        <tr>
          <td width="46%" align=center valign=top bgcolor="#eeeeee">
            <div>Projects</div>
            <div class=maintext>
            <select ID="frm_sourceList" name="frm_sourceList" size=10 multiple>
              <?php foreach($user_accessed_projects_arr as $accessed_projects_id){
                 if($accessed_projects_id == $AccessProjectID) continue;
              ?>
              <option value='<?php echo $accessed_projects_id?>'><?php echo $project_id_name_arr[$accessed_projects_id]?>(<?php echo $accessed_projects_id?>)
              <?php }?>
          	</select>
            </div>   
          </td>
          <td width="8%" valign=center align=center bgcolor="#eeeeee"><br>
            <font size="2" face="Arial">
            <input type=button value='&nbsp;> >&nbsp;' onClick="moveOption(this.form.frm_sourceList, this.form.frm_selected_list);">
            <br><br>
            <input type=button value='&nbsp;< <&nbsp;' onClick="moveOption(this.form.frm_selected_list, this.form.frm_sourceList);">
            </font> 
          </td>
          <td width="46%" align=center valign=top bgcolor="#eeeeee">
            <div>Selected objects</div>
            <select id="frm_selected_list" name="frm_selected_list" size=10 multiple>
            <?php foreach($selected_arr as $selected_id){?>
              <option value='<?php echo $selected_id?>'><?php echo $project_id_name_arr[$selected_id]?>(<?php echo $selected_id?>)
            <?php }?>
            </select>
          </td>
        </tr>
        </table>
        </div><br>
        </td>   
      </tr>  
  <?php }else{?>      
      <tr>
          <td align="right" valign="top" bgcolor="#cdcdcd" >
              Find:&nbsp;
          </td>
          <td bgcolor="#eeeeee">
              <input type="radio" id="search_option_1" name="frm_search_OrAnd" value="OR" checked="checked">at least one of the words (separated by a space character)<br>
              <input type="radio" id="search_option_2" name="frm_search_OrAnd" value="AND">all words (separated by a space character)<br>
              <input type="radio" id="search_option_3" name="frm_search_OrAnd" value=""> the exact phrase<br>
              <input type="checkbox" id="search_description" name="frm_search_description" value="1">include description<br>
              
          </td>
      </tr>
      <tr><td colspan="2" bgcolor="#eeeeee">&nbsp;</td></tr>
      <tr>
          <td align="right" valign="top" bgcolor="#cdcdcd" >
              Experiment Detail:&nbsp;
          </td>
          <td bgcolor="#eeeeee">
              <textarea cols="50" rows="4" name="frm_expDetail_dis" id="frm_expDetail_dis" readonly></textarea>
              <a href="javascript:popwin('experiment_detail_pop.php?for_search=1',800,500);" class=button>[select]</a>&nbsp;&nbsp;
              <a href="javascript:removeExpDetail();" class=button>[remove]</a></td>
      </tr>
      <tr>
          <td align="right" valign="top" bgcolor="#cdcdcd">
              Date:&nbsp;
          </td>
          <td bgcolor="#eeeeee">
              <input type="text" id="frm_date_str" name="frm_date_str" size="40" value="" readonly>
              <a href="javascript: href_show_hand();" onclick="showTip(event,'date_div')" class=button>[select]</a>&nbsp;&nbsp;
              <a href="javascript: removeDate()" class=button>[remove]</a>
              <DIV ID='date_div' STYLE="position: absolute; 
                          display: none;
                          border: black solid 1px;
                          width: 200px";>
                <table align="center" cellspacing="0" cellpadding="0" border="0" width=100% bgcolor="#e1e3e3">
                  <tr BGCOLOR='#a0a7c5' height=20>
                    <td valign="bottem" colspan=2>
                      <table border=0 width=100%><tr>
                        <td><font color="white" face="helvetica,arial,futura"><b>Select Date:</b></font></td>
                        <td align=right><a href="javascript: hideTip('date_div');"><img src='images/icon_delete_option.gif' border=0></a></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr height=25>
                    <td>&nbsp; &nbsp;From:</td><td><?php echo $DateSelector->setDate('frm_datefrom_', $frm_date1, false);?></td>
                  </tr>
                  <tr height=25>
                    <td>&nbsp; &nbsp;To:</td><td><?php echo $DateSelector->setDate('frm_dateto_', $frm_date2, false);?></td>
                  </tr>
                  <tr><td align="center" height=35 colspan=2>
                  <input type=button name='show_div' VALUE="Select" onclick="javascript: passDate();">&nbsp;&nbsp;
                  </td>
                  </tr>
                </table>   
              </DIV>
          </td>
      </tr>
      <tr>
          <td align="right" valign="top" bgcolor="#cdcdcd" >
              User:&nbsp;
          </td>
          <td bgcolor="#eeeeee">
            <?php $users_list_arr = show_project_users_list();?>
            <select id="frm_user_id" name="frm_user_id" onchange="change_user(this.form)">
              <option value="">All Users		            
            <?php foreach($users_list_arr as $key => $val){?>              
              <option value="<?php echo $key?>"<?php echo ($frm_user_id==$key)?" selected":"";?>><?php echo $val?>			
            <?php }?>
            </select> 
          </td>
      </tr>
      <tr>
          <td align="right" valign="top" bgcolor="#cdcdcd" >
              Group type:&nbsp;
          </td>
          <td bgcolor="#eeeeee">
            <input type="radio" name="frm_groups" value="Bait" onClick="change_group_type(this.form)" <?php echo (($frm_groups=='Bait')?'checked':'')?>>Bait&nbsp;
          	<input type="radio" name="frm_groups" value="Experiment" onClick="change_group_type(this.form)" <?php echo (($frm_groups=='Experiment')?'checked':'')?>>Experiment&nbsp;
          	<input type="radio" name="frm_groups" value="Band" onClick="change_group_type(this.form)" <?php echo (($frm_groups=='Band')?'checked':'')?>>Sample&nbsp;
          </td>
      </tr>
      <tr><td align="right" valign="top" bgcolor="#cdcdcd" >Group:</td>
      <td bgcolor="#eeeeee">
        <DIV id="group_block"><?php change_group_type($group_type);?></DIV>
      </td>
      </tr>
  <?php }?>
    </table>
    </Div>&nbsp;<br>
    <!--input type="submit" name="submit_search" value="Search"-->
    <input type="button" value="Search" onclick="javascript: <?php echo (($frm_search_hit_gene)?'search_hit_gene(this.form)':'check_search()')?>">
    </td>
  </tr>
</table>
</form>
<?php 
require("site_footer.php");

function change_group_type($group_type){
  global $frm_group_id_list,$HITSDB,$AccessProjectID;
  $table_name = $group_type."Group";
  $SQL = "SELECT `NoteTypeID` FROM $table_name GROUP BY `NoteTypeID`";
  $tmp_NoteTypeID_arr = $HITSDB->fetchAll($SQL);
  $tmp_NoteTypeID_str = array_to_delimited_str($tmp_NoteTypeID_arr, 'NoteTypeID');
  $group_arr = array();
  if($tmp_NoteTypeID_str){
    $SQL = "SELECT `ID`,`Name`,`Initial` FROM `NoteType` WHERE `ProjectID`='$AccessProjectID' AND `ID` IN($tmp_NoteTypeID_str)";
    $group_arr = $tmp_NoteTypeID_arr = $HITSDB->fetchAll($SQL);
  }
?>
  <select name="frm_group_id_list">
  <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
<?php foreach($group_arr as $group_val){
    $VS = '';
    if(is_numeric($group_val['Initial'])) $VS = 'VS';
    echo "<option value='".$group_type."_".$group_val['ID']."'".(($frm_group_id_list==$group_val['ID'])?'selected':'').">".$group_val['Name']." ($VS".$group_val['Initial'].")</option>\r\n";
  }
?>
  </select>
<?php 
}
?>

