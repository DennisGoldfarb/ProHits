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

$ProjectName = '';
$Gel_ID = '';
$order_by = '';
$start_point = '';
$msg = '';
$theProjectName = '';
$frm_GeneID = '';
$frm_LocusTag = '';
$frm_GeneName  = '';
$table = '';
$frm_user_id = '';

$Bait_ID = '';
$frm_BaitMW = '';
$frm_TaxID = '';
$frm_Family  = '';
$frm_Tag = '';
$frm_Mutation = '';
$frm_Vector = '';
$frm_Clone = 'N/A';
$frm_Description = '';
$frm_BaitAcc = '';
$frm_AccType = 'REFSEQ';
$proteinTitle = '';
$bait_switch = 'new_bait';
$virtual_Tag = '';
$displayFormat = array();
$frm_displayFormatID = '';
$frm_group_id_list = '';

$title_lable = '';
$searched_bait_str = ''; 
$searched_id_str = '';
$searched_id_vl_str = '';
$bait_id_value_arr = array();
$bait_format_default_arr = array('Tag', 'BaitAcc', 'OwnerID');
$bait_lable_arr = array(
  'GeneID'=>"GeneID",
  'LocusTag'=>"LocusTag",
  'BaitAcc'=>"ProteinID",
  'AccType'=>"ProteinType",
  'Tag'=>"Tag",
  'Mutation'=>"Mutation",
  'Clone'=>"Clone",
  'Vector'=>"Vector",
  'OwnerID'=>"User"
  );
$toggle_group_status = '1';
$toggle_color_status = '1';
$frm_disID = '';
$note_action = '';
$OF_session_id = '';
//$frm_Reagent_ID = '';

require("../common/site_permission.inc.php");
require("analyst/classes/bait_class.php");
require("analyst/classes/hits_class.php");
require("common/page_counter_class.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
require("analyst/status_fun_inc.php");

define ("RESULTS_PER_PAGE", 50);
define ("MAX_PAGES", 15); // this value will set how many page links to display. If there are more the 

if($theaction == 'modify_note'){
  modify_note_block($frm_disID,$item_type);
  exit;
}

require("site_header.php");

if(isset($_SESSION["OF_session_id"]) && $_SESSION["OF_session_id"]){
  $OF_session_id = $_SESSION["OF_session_id"];
}

$DB_name = $HITSDB->selected_db_name;
$exist_Hits_tables_arr = exist_hits_table($DB_name);


$Log = new Log();
$Baits = new Bait(0, $HITSDB->link);
if($theaction == "delete" && $Bait_ID AND $AUTH->Delete ) {
  if($Baits->isOwner($Bait_ID,$AccessUserID)){
    $msg = $Baits->delete($Bait_ID);
    if(!$msg){
     $Desc = "";
     $Log->insert($AccessUserID,'Bait',$Bait_ID,'delete',$Desc,$AccessProjectID);
    }
    $theaction = "viewall";
  }
}
if($searched_id_str) $searched_bait_str = $searched_id_str;


if($searched_id_vl_str and $theaction == 'search'){
  $searchE_arr = array();
  $searchE_type_counter = 0;
  $tmp_arr = explode(":", $searched_id_vl_str);
  foreach($tmp_arr as $value){
    $tmp_arr2 = explode(",", $value);
    if(count($tmp_arr2)==2){
      $tmp_arr3 = explode(" ", $tmp_arr2[1]);
      if(!in_array($tmp_arr3[0], $searchE_arr)){
        array_push($searchE_arr, $tmp_arr3[0]);
        $searchE_type_counter++;
      }
      $bait_id_value_arr[$tmp_arr2[0]] = $searchE_type_counter.','.$tmp_arr2[1];
    }
  }
}

?>
<style type="text/css">
a.title { cursor: pointer; cursor: hand; }
</style>
<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language="javascript">


function add_baitnotes(bait_ID){
  var file = 'pop_bait_note.php?Bait_ID=' + bait_ID;
  popwin(file,650,500);
}
function pop_noteType(TypeID,kind){
  var file = 'pop_noteType.php?frm_ID=' + TypeID + '&kind=' + kind;
  popwin(file,650,300);
}
function isNumber(str) {
  for(var position=0; position<str.length; position++){
        var chr = str.charAt(position)
        if ( ( (chr < "0") || (chr > "9") ) && chr != ".")
              return false;
  }
  return true;
}
function confirm_delete(Bait_ID){
  if(confirm("Are you sure that you want to delete the Bait?")){
    document.bait_form.Bait_ID.value = Bait_ID;
    document.bait_form.theaction.value = "delete";
    document.bait_form.submit();
  }
}

function confirm_delete_note(note_ID){
  if(confirm("Are you sure that you want to delete the note?")){
    document.bait_form.frm_disID.value = note_ID;
    document.bait_form.note_action.value = "delete";
    document.bait_form.submit();
  }
}

function checkform(formType){
  var theForm = document.bait_form;
  var switch_value = '';
  for(var i=0; i<theForm.bait_switch.length; i++){
    if(theForm.bait_switch[i].checked == true){
      switch_value = theForm.bait_switch[i].value;
    }
  }
  
  if(document.getElementById('note_header').innerHTML == 'Modify Bait Note'){
    theForm.note_action.value = 'update';
  }else{
    theForm.note_action.value = 'insert';
  }
  
  if(formType == 'add_form'){
    theForm.theaction.value = 'insert';
  }else if(formType == 'modify_form'){
    theForm.theaction.value = 'update';
  }  
  if(theForm.frm_Clone.value != "dummy"){
    var frm_GeneID = theForm.frm_GeneID.value;
    var frm_LocusTag = theForm.frm_LocusTag.value;
    var frm_GeneName = theForm.frm_GeneName.value;
    var frm_TaxID = theForm.frm_TaxID.value;
    var frm_BaitMW = theForm.frm_BaitMW.value;
    var frm_BaitAcc = theForm.frm_BaitAcc.value;
    var frm_AccType = theForm.frm_AccType.value;
    var frm_Clone = theForm.frm_Clone.value;
    var frm_Description = theForm.frm_Description.value;
    var selectedProjects = '<?php echo $AccessProjectID;?>';
    if(switch_value == 'new_bait'){  
      if((isEmptyStr(frm_LocusTag) && isEmptyStr(frm_GeneName)) || isEmptyStr(frm_BaitAcc) || isEmptyStr(frm_AccType) || isEmptyStr(frm_Description) || frm_TaxID == ''){
        alert("Bold field names are requiered to make the insert.");
        return false;
      }else if(!isEmptyStr(frm_GeneID) && !isNumber(frm_GeneID)){
        alert("frm_GeneID has to be a number!");
         return false;
      }else if(!isNumber(frm_BaitMW)){
         alert("Bait MW has to be a number!");
         return false;
      }else{
        theForm.submit();
      }  
    }else{
      if(isEmptyStr(frm_GeneName)){
        alert("Bold field names are required to make the insert.");
        return false;
      }else{
        theForm.submit();
      }  
    }  
  }else{
    theForm.submit();
  }  
}
function goexperiment(passAc,expid, baitid){
  var theForm = document.bait_form;
  theForm.theaction.value=passAc;
  theForm.Exp_ID.value = expid;
  theForm.Bait_ID.value = baitid;
  theForm.action="experiment.php";
  theForm.submit();
}

function subNextStep(Bait_ID,Exp_ID){
  var theForm = document.bait_form;
  theForm.Bait_ID.value = Bait_ID;
  if(Exp_ID != ''){
    theForm.Exp_ID.value = Exp_ID;
    theForm.theaction.value = 'viewall';
  }else{
    theForm.theaction.value = 'addnew';
  }
  theForm.order_by.value = '';  
  theForm.action = 'experiment.php';
  theForm.submit();
}

function sortList(order_by){
  var theForm = document.bait_form;
  theForm.order_by.value = order_by;
  set_group_id_list(theForm);
  theForm.submit();
}

function Exp_Status(temp_point){
  var theForm = document.bait_form;;
  theForm.start_point.value = temp_point;
  theForm.submit();
}

function display_group(the_obj){
  the_obj.checked = true;
  for(var j=0; j<group_item_id_arr.length; j++){
    var tmp_id = the_obj.value + "_" + group_item_id_arr[j];
    var group_obj = document.getElementById(tmp_id);
    if(group_obj != null){ 
      group_obj.style.display = "block";
    }  
  }  
}
  
function change_groups(theForm){
  theForm.start_point.value = 0;
  set_group_id_list(theForm);
  theForm.theaction.value = 'viewall';
  theForm.title_lable.value = '';
  theForm.start_point.value = 0;
  theForm.submit();
}

function export_search_result(){
  var theForm = document.bait_form;
  theForm.action = 'export_bait_to_hits.php';
  theForm.submit();
}
function download_search_result(){
  var theForm = document.bait_form;
  theForm.action = 'download_search_result.php';
  theForm.submit();
}
function reset_form(){
  theForm = document.bait_form;
  theForm.frm_GeneID.readOnly = false;
  theForm.reset();
}
</script>
<form name=bait_form action=<?php echo $PHP_SELF;?> method=post>
<input type=hidden name=theaction value="<?php echo $theaction?>">  
<input type=hidden name=sub value=<?php echo $sub;?>>
<input type=hidden name=Gel_ID value=<?php echo $Gel_ID;?>>
<input type=hidden name=order_by value='<?php echo $order_by;?>'>
<input type=hidden name=Exp_ID value=''>
<input type=hidden name='bait_switch' value='<?php echo $bait_switch?>'>
<input type=hidden name=virtual_Tag value=<?php echo $virtual_Tag;?>>
<input type=hidden name=searched_bait_str  value="<?php echo $searched_bait_str;?>">
<input type=hidden name=searched_id_vl_str  value="<?php echo $searched_id_vl_str;?>">
<input type=hidden name=hit_Band_ids_v  value="<?php echo (isset($hit_Band_ids_v)?$hit_Band_ids_v:'');?>">
<input type=hidden name=tpp_band_ids_v  value="<?php echo (isset($tpp_band_ids_v)?$tpp_band_ids_v:'');?>">
<input type=hidden name=table  value="<?php echo $table;?>">
<input type=hidden name=title_lable  value='<?php echo $title_lable;?>'>
<input type=hidden name=frm_group_id_list  value='<?php echo $frm_group_id_list;?>'>
<input type=hidden name=start_point  value='<?php echo $start_point?>'>
<input type=hidden name=searched_id_str  value='<?php echo $searched_id_str?>'>
<input type=hidden name=item_type value='Bait'>
<input type=hidden name=Add value='new'>
<input type=hidden name=firstDisplay value='y'>
<?php 
if($sub){
?>
<div style="width:650px;word-spacing:5px;padding-left:0px;padding-top:6px;white-space:nowrap;text-align:centre;">
<?php if($sub != 3){?>
    <img src="./images/arrow_green_gel.gif" border=0>
<?php }?>      
    <img src="./images/arrow_red_bait.gif" border=0>   
    <img src="./images/arrow_green_exp.gif" border=0>
    <img src="./images/arrow_green_band.gif" border=0>
<?php if($sub != 3){?>    
    <img src="./images/arrow_green_well.gif" border=0>
<?php }?>    
</div>
<?php 
}
$used_group_arr = array();
$all_group_recodes_str = '';
if($frm_group_id_list){
  $all_group_recodes_str = get_all_group_recordes_str($frm_group_id_list,'Bait');
}
$group_id_arr = explode(",", $frm_group_id_list);
$theBait = new Bait(0, $HITSDB->link);
if($sub){
  if($sub == "2" || $sub == "4"){
    if($Gel_ID){
      $total_records = $theBait->get_total(1, 2);
    }else{
      $total_records = $theBait->get_total(1, 1);
    }  
  }else{
    if($Gel_ID){
      $total_records = $theBait->get_total(0, 2);
    }else{
      $total_records = $theBait->get_total(0, 1);
    }  
  }
}else{
  $total_records = $theBait->get_total();
}
?>
<div style="width:95%;border: red solid 0px;">
  <div style="width:100%;border: black solid 0px;text-align:left;">
    <div class=maintext>
      <img src="images/icon_purge.gif"> Delete 
      <img src="images/icon_tree.gif"> Next Level
      <img src="images/icon_view.gif"> Modify 
      <img src="images/arrow_small.gif"> Next
      <img src="images/icon_report.gif"> Bait Report
      <img src="./images/icon_notes.gif" border=0 alt="Hit Notes"> Bait Notes
    </div>
  </div> 
  <div style="width:100%;height:35px;border: red solid 0px;text-align:left;">
    <div style="float:left;padding-top:10px;white-space:nowrap;text-align:left;border: blue solid 0px;">
      <font color="navy" face="helvetica,arial,futura" size="5"><b><?php echo ($title_lable)?urldecode($title_lable):"Baits";?>
      <?php        
      if($AccessProjectName){
        echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font>";
      }
      if($sub && !$Gel_ID){
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color='blue' face='helvetica,arial,futura' size='3'>(Submit Gel Free Sample)</font>";
      }elseif($sub && $Gel_ID){
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color='green' face='helvetica,arial,futura' size='3'>(Submit Gel Sample)</font>";
      }
      echo "</b></font>\n";
      //-----------------------------------------------------------------------------
      if($theaction == 'viewall' OR $theaction == 'search' or !$theaction) include("./display_format.inc.php");
      //-----------------------------------------------------------------------------
      ?>   
    </div>
    <div style="float:left;padding-top:18px;white-space:nowrap;text-align:left;border: blue solid 0px;">
    <?php if($theaction == "viewall"){?>
        &nbsp;&nbsp;&nbsp;
      <font face="helvetica,arial,futura" size="2"><b>User</b></font>
      <?php $users_list_arr = show_project_users_list();?>
      <select id="frm_user_id" name="frm_user_id" onchange="change_user(this.form)">
        <option value="">All Users		            
      <?php foreach($users_list_arr as $key => $val){?>              
        <option value="<?php echo $key?>"<?php echo ($frm_user_id==$key)?" selected":"";?>><?php echo $val?>			
      <?php }?>
      </select> 
    <?php }else{
        echo "&nbsp;";
      }?>
     </div>
     <div style="float:right;padding-top:20px;white-space:nowrap;text-align:left;border: blue solid 0px;">       
<?php if($AUTH->Insert && $sub){?>
      <a href="bait.php?theaction=addnew<?php echo ($sub)?"&sub=$sub&Gel_ID=$Gel_ID":"";?>" class=button>[Add New Bait]</a>&nbsp;
<?php }?>
<?php if($title_lable){?>
      <!--a href="export_bait_to_hits.php?searched_id_str=<?php echo $searched_id_str?>&item_type=Bait&Add=new&firstDisplay=y" class=button>[Export]</a-->
      <a href="javascript: export_search_result()" class=button>[Export]</a>&nbsp;
<?php }?>
      <!--a href="bait.php?theaction=viewall<?php echo ($sub)?"&sub=$sub&Gel_ID=$Gel_ID":"";?>" class=button>[Bait List]</a-->&nbsp;
    </div>
  </div>
  <hr color="#000000" size=1>
<?php if($sub == "2" || $sub == "4") {?>  
  <div style="width:100%;border: red solid 0px">
    Please select a dummy Bait or create a new dummy Bait to submit.
  </div>
<?php }elseif($sub == 3 && !$AUTH->Insert){?>
  <div style="width:100%;border: red solid 0px">
    You don't have the permission to add a new Bait.
  </div>  
<?php }?>
<?php 
if($theaction == "viewall" OR $theaction == 'search' OR !$theaction){  
  //get bait id array which contains all bait have been ided
  $users_ID_NameArr = get_users_ID_Name($HITSDB);
  $Hits = new Hits();  
  $session_comparsion_arr = get_comparison_session("Bait", 'array');
  //page counter start here---------------------------------------------------------
  $PAGE_COUNTER = new PageCounter('Exp_Status');
  $query_string = "sub=$sub&Gel_ID=$Gel_ID&frm_displayFormatID=$frm_displayFormatID";
  $caption = "Baits";
  if($order_by)  $query_string .= "&order_by=".$order_by;
  $page_output = $PAGE_COUNTER->page_links($start_point, $total_records, RESULTS_PER_PAGE, MAX_PAGES,str_replace(' ','%20',$query_string)); 
//end of page counter-----------------------------------------------------------------
  if($order_by == "") $order_by = "ID desc";
  if(!$start_point) $start_point = 0;
  if($sub){
    if($sub == "2" || $sub == "4"){
      if($Gel_ID){
        $Baits->fetchall($order_by, $start_point, RESULTS_PER_PAGE, 1, 2);
      }else{
        $Baits->fetchall($order_by, $start_point, RESULTS_PER_PAGE, 1, 1);
      }  
    }else{
      if($Gel_ID){
        $Baits->fetchall($order_by, $start_point, RESULTS_PER_PAGE, 0, 2);
      }else{
        $Baits->fetchall($order_by, $start_point, RESULTS_PER_PAGE, 0, 1);
      }  
    }
  }else if($theaction == 'search'){
    if($searched_bait_str){
      
      $Baits->fetchall_ids($order_by, $searched_bait_str, 'in');
    }else{
      $Baits->count = 0;
    }
    $page_output = '';
  }else{
    $Baits->fetchall($order_by, $start_point, RESULTS_PER_PAGE);
  }  
  $idedBaitsArr = get_idedItemsArr($Baits->bait_str,'Bait');
  if($msg){
      echo "<center><font color='red' face='helvetica,arial,futura' size=3>";
      echo $msg;
      echo "</font></center>";
  }
  for($i=0; $i < $Baits->count; $i++){
    if($proteinTitle = $Baits->AccType[$i]){
      break;
    }
  }
?> 
  <input type="hidden" name="displayFormat_str" value="<?php echo (isset($displayFormat['Format'])?$displayFormat['Format']:'')?>">
  <div style="float:left;width:100%;white-space:nowrap;text-align:left;border: black solid 0px">
    <table border="0" width=100% cellpadding="1" cellspacing="1">
    	<tr>
    	  <td width="20%" valign="top">
          <?php color_keys_for_experiment();?>
        </td>
        <td valign="top">
          <?php print_group_bar();?>
        </td>
      </tr>
    </table>  
  </div>
  <div style="float:left;width:100%;white-space:nowrap;text-align:left;height:35;margin-top:10px;border: red solid 0px;">
    <div style="float:left;border: black solid 0px"><img src='./images/select-to-compare.gif' border=0></div>
  <?php if($theaction == 'search'){?>
    <div style="float:right;padding-top:20px;white-space:nowrap;text-align:left;">
      <a href="javascript: download_search_result()" class=button>[Download this table]</a>&nbsp;
    </div> 
  <?php }else{?>   
    <div id="divCoord" style="float:right;border: black solid 0px;padding-top:10px;"><?php echo $page_output;?></div>
  <?php }?>
  </div>
  <?php 
  //get bait and exp column names----------------------------
  $bait_format_arr = array();
  $exp_format_arr = array();
  $exp_format_str = '';
  $colums_number = 0;
  if($displayFormat){
    $tmp_arr = explode(",", $displayFormat['Format']);
    foreach($tmp_arr as $value){
      if(preg_match("/^([BE])\.(.+)/", $value, $matches)){
        if(count($matches) == 3){
          if($matches[1] == 'B'){
            array_push($bait_format_arr, $matches[2]);
            $colums_number++;
          }else{
            if($exp_format_str) $exp_format_str .=",";
            $exp_format_str .= $matches[2];
            $colums_number++;
            if(isset($exp_optionID_name_array) and isset($exp_optionID_name_array[$matches[2]])){
              array_push($bait_format_arr, $exp_optionID_name_array[$matches[2]]);
            }
          }
        }
      }
    }
  }
  if(!$colums_number){
    $bait_format_arr = $bait_format_default_arr;
  }
  ?>
  <div style="float:left;width:100%;border: yellow solid 0px">
    <table border='0' width=100% cellpadding="1" cellspacing="1">
    <tr bgcolor="" height="25">
      <td width="5%" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center><div class=tableheader>
        <a href="javascript: sortList('<?php echo ($order_by == "ID")? 'ID%20desc':'ID';?>');">
      Bait ID</a>
      <?php if($order_by == "ID") echo "<img src='images/icon_order_up.gif'>";
        if($order_by == "ID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
      ?></div>
      </td>
      <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center> <div class=tableheader>
        <a href="javascript: sortList('<?php echo ($order_by == "GeneName")? 'GeneName%20desc':'GeneName';?>');">
        GeneName</a>
      <?php if($order_by == "GeneName") echo "<img src='images/icon_order_up.gif'>";
        if($order_by == "GeneName desc") echo "<img src='images/icon_order_down.gif'>";
      ?></div>
      </td>
    <?php 
    foreach($bait_format_arr as $value){
      echo "\n<td width='' bgcolor='".$TB_HD_COLOR."' align=center><div class=tableheader>";
      if(!is_array($value)){
        $tmp_sort = ($order_by == $value)? $value."%20desc":$value;
        echo "<a href=\"javascript: sortList('".$tmp_sort."');\">";
        echo (isset($bait_lable_arr[$value]))?$bait_lable_arr[$value]:$value;
        echo "</a>\n";
        if($order_by == $value) echo "<img src='images/icon_order_up.gif'>";
        if($order_by == "$value desc") echo "<img src='images/icon_order_down.gif'>";
      }else{
        echo $value['Name'];
      }
      echo "</div></td>\n";
    }
    ?>
      <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align="center" align=center>
        <table width="100%" border=0>
          <tr>
          <td align="center"><div class=tableheader>Status</div></td>
          </tr>
        </table>
      </td>
    <?php 
    if($bait_id_value_arr and $theaction == 'search'){?>
      <td width="124" bgcolor="<?php echo $TB_HD_COLOR;?>" align="center" colspan="<?php echo $searchE_type_counter?>">
        <div class=tableheader>Score or Probability /<br> # Peptide</div>
      </td>
    <?php }?>
      <td width="124" bgcolor="<?php echo $TB_HD_COLOR;?>" align="center">
        <div class=tableheader>Options</div>
      </td>
    </tr>
  <?php 
    $noBaitArr = array();
    if(!$Baits->count){
      echo "<tr>
        <td colspan=6><font color='#FF0000'><b>No Record found</b></font>
        </td>
      </tr>";
      
    }
    for($i=0; $i < $Baits->count; $i++) {
      $ownerName = '';
      if(isset($users_ID_NameArr[$Baits->OwnerID[$i]])){
         $ownerName = $users_ID_NameArr[$Baits->OwnerID[$i]];
       }
      $bait_checked = (in_array($Baits->ID[$i], $session_comparsion_arr))? " checked": "";
      $bait_checkbox_disabled = (!has_hits($Baits->ID[$i], 'Bait'))? " disabled":"";
  ?>
    <tr bgcolor="<?php echo  $TB_CELL_COLOR;?>" onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $TB_CELL_COLOR;?>')"; >
      <td width="" align="left" nowrap>
        <div class=maintext>&nbsp;
          <input type="checkbox" name="frm_check<?php echo $Baits->ID[$i];?>" value="1" onClick="add_compare(this, '<?php echo $Baits->ID[$i];?>', 'Bait')"<?php echo $bait_checked . $bait_checkbox_disabled;?>>
          <?php echo $Baits->ID[$i];?>&nbsp;
        </div>
      </td>
      <td width="" align="left" <?php echo ($Baits->ID[$i])?"class='bait".$Baits->ID[$i]."'" : "";?>><div class=maintext>&nbsp;
          <?php echo $Baits->GeneName[$i];?>&nbsp;
        </div>
      </td>
  <?php 
      foreach($bait_format_arr as $value){
        $tmp_bgcolor = '';
        $tmp_display = '';
        if(!is_array($value)){
          $tmp_arr = $Baits->$value;
          if($value == 'BaitAcc'){
            if(is_numeric($tmp_arr[$i])){
              $Acc_V_arr = replease_gi_with_Acc_Version($tmp_arr[$i]);
              $tmp_display = $Acc_V_arr['Acc_Version'];
            }else{
              $tmp_display = $tmp_arr[$i];
            }    
          }elseif($value == 'OwnerID'){
            $tmp_display = $ownerName;
          }else{
            $tmp_display = $tmp_arr[$i];
          }
        }else{
          $SQL ="SELECT `ID` FROM `Experiment` WHERE `BaitID`='".$Baits->ID[$i]."' order by ID"; 
          $ExpArr = $HITSDB->fetchAll($SQL);
          foreach($ExpArr as $ExpValue){
            if($tmp_display) $tmp_display .='<br>';
            $SQL = "SELECT OptionID FROM ExpDetail WHERE ExpID='".$ExpValue['ID']."' and SelectionID='".$value['ID']."'";
             
            $tmpOption = $HITSDB->fetch($SQL);
            if($tmpOption){
              if(isset($exp_optionID_name_array[$tmpOption['OptionID']])){
              $tmp_display .= $exp_optionID_name_array[$tmpOption['OptionID']]['Name'];
              }else{
                $tmp_display .='&nbsp;';
              }
            }else{
              $tmp_display .='&nbsp;';
            }
          }
          $tmp_bgcolor = '#b6c5e0';
        }
        echo "\n<td width='' bgcolor='$tmp_bgcolor' valign=top align=\"left\"><div class=maintext>&nbsp;".$tmp_display."</div></td>\n";
      }
  ?>
      <td width="" align="left">
        <DIV id="R_<?php echo $Baits->ID[$i]?>">
          <?php 
          $statusArr = get_status($Baits->ID[$i],"Bait");
/*echo "<pre>";
print_r($statusArr);
echo "<pre>";*/
          
          $dark = '';
          if($noteTypeID_str = $statusArr['has_note']) $dark = "_dark";
          ?>
        </DIV>  
      </td>
      <?php 
        if(!in_array($Baits->ID[$i], $idedBaitsArr) && $statusArr['total_hits']) array_push($noBaitArr, $Baits->ID[$i]);
        if($bait_id_value_arr and $theaction == 'search'){
          $E_tmp_arr = explode(",", $bait_id_value_arr[$Baits->ID[$i]]);
          for($z=1;$z<=$searchE_type_counter;$z++){
            if($z == $E_tmp_arr[0]){?>
              <td align="left"><div class=maintext><?php echo $E_tmp_arr[1];?></div></td>
      <?php     }else{?>
              <td align="left"><div class=maintext>&nbsp;</div></td>
      <?php     }
          }   
        }
      ?>
      <td width="" align="left" nowrap><div class=maintext>&nbsp;
    <?php if($AUTH->Delete and $Baits->OwnerID[$i] == $AccessUserID and !$statusArr['num_Exp']){
    ?>
            <a  title='delete bait' href="javascript:confirm_delete(<?php echo $Baits->ID[$i];?>);">
            <img border="0" src="images/icon_purge.gif" alt="Delete"></a>&nbsp;
    <?php }else{
        echo "<img src=\"images/icon_empty.gif\">&nbsp;";
      }
      if($statusArr['num_Exp'] && !$sub){
    ?>   <a  title='experiment detail' href="experiment.php?theaction=viewall&Bait_ID=<?php echo $Baits->ID[$i];?><?php echo ($sub)?"&sub=$sub&Gel_ID=$Gel_ID":"";?>">
          <img border="0" src="images/icon_tree.gif" alt="experiments"></a>&nbsp;
    <?php }elseif(!$sub){
        echo "\n<img src=\"images/icon_empty.gif\">&nbsp;";
      }
      if($AUTH->Access){
      ?>
          <a  title='bait detail' href="bait.php?theaction=modify&Bait_ID=<?php echo  $Baits->ID[$i];?><?php echo  ($sub)?"&sub=$sub&Gel_ID=$Gel_ID":"";?><?php echo (($Baits->GeneID[$i]=='-1')?"&bait_switch=no_bait":"&bait_switch=new_bait");?>">
          <img border="0" src="images/icon_view.gif" alt="Modify"></a>&nbsp;
    <?php }else{
        echo "\n<img src=\"images/icon_empty.gif\">&nbsp;";
      }
      if($statusArr['has_report']){
        if($statusArr['hitType']){
          $theType = '';
          if($table == 'TPP'){
            $theType = 'TPP';
          }else{
            $theType = $statusArr['hitType'];
          }
        }  
    ?>  
        <a href="item_report.php?type=Bait&item_ID=<?php echo  $Baits->ID[$i];?>&hitType=<?php echo $theType;?>&isGelFree=<?php echo  $Baits->GelFree[$i];?>&noteTypeID_str=<?php echo $noteTypeID_str?>&theaction=<?php echo  ($sub)?"&sub=$sub":"";?>"  title='hits detail'>
           <img src="./images/icon_report.gif" border=0 alt="Bait Report">
        </a>
        <?php if($statusArr['num_hitsGeneLevel']){?>
        <a href="item_report.php?type=Bait&item_ID=<?php echo  $Baits->ID[$i];?>&hitType=geneLevel&isGelFree=<?php echo  $Baits->GelFree[$i];?>&noteTypeID_str=<?php echo $noteTypeID_str?>&theaction=<?php echo  ($sub)?"&sub=$sub":"";?>"  title='gene level hits detail'>            
           <img src="./images/icon_report_uploaded.gif" border=0 alt="Bait Report">
        </a>
        <?php }?>                              
    <?php }else{
        echo "\n<img src=\"images/icon_empty.gif\" width=17>&nbsp;\n";
      }
    ?>
        <a  title='add notes for the bait' href="javascript: add_notes_dev('<?php echo $Baits->ID[$i];?>','Bait');"><img src="./images/icon_notes<?php echo $dark?>.gif" border=0 alt="Bait Notes"></a>  
  <?php  
      if($sub){
        $Exp_ID = '';
        $SQL = "SELECT ID FROM Experiment where BaitID = '".$Baits->ID[$i]."'";        
        $expArr = $HITSDB->fetchAll($SQL);
        if($expArr && $expArr[0]['ID']){
          $Exp_ID = $expArr[0]['ID'];
        }
      ?>
        <a href="javascript:subNextStep('<?php echo $Baits->ID[$i];?>','<?php echo $Exp_ID;?>');">
         <img border="0" src="./images/arrow_small.gif" alt="Submit"></a>&nbsp;
    <?php }?> 
      </div>
      </td>
    </tr>
    <?php 
    } //end for
    ?>
    </table>
  </div>
  <div style="width:100%;text-align:right;border: black solid 0px">
    <?php echo $page_output;?>
  </div>
  <input type=hidden name=Bait_ID value="">
  </form>
<?php 
}elseif(($theaction == "addnew" OR $theaction == "insert" ) and $AUTH->Insert) {
  $frm_LocusTag = trim($frm_LocusTag);
  $frm_GeneName = preg_replace("/[^A-Za-z0-9_.-]/",'',$frm_GeneName);
  //$frm_GeneName = trim($frm_GeneName);
  if($theaction == "insert"){
    $isInsertFlag = 0;
    if($sub == "2" || $sub == "4"){
      $isInsertFlag = 1;
    }else if(($frm_LocusTag Or $frm_GeneName) and $frm_Description){
      $isInsertFlag = 2;
    }else{
      echo "<center><font color='red' face='helvetica,arial,futura' size=2>";
      echo "Missing info. <b>Bold</b> field names are required to make the insert.";
      echo "</font></center>";
    }
    $isGelFree = 1;
    if($Gel_ID) $isGelFree = 0;
    if($isInsertFlag){    
      $Baits->insert(
         $frm_GeneID,
         $frm_LocusTag, 
         $frm_GeneName,
         $frm_BaitAcc,
         $frm_AccType, 
         $frm_TaxID, 
         $frm_BaitMW, 
         $frm_Family, 
         $frm_Tag, 
         $frm_Mutation,
         $frm_Clone, 
         $frm_Vector, 
         $frm_Description, 
         $AccessUserID,
         $AccessProjectID,
         $isGelFree);         
       echo "<center><font color='red' face='helvetica,arial,futura' size=2>";
       echo "Insert complete.";
       echo "</font></center>";
       
       //$Desc = "LocusTag=$frm_LocusTag,GeneName=$frm_GeneName,Clone=$frm_Clone";
       $Desc = "GeneID=$frm_GeneID,AccType=$frm_AccType,BaitMW=$frm_BaitMW,Clone=$frm_Clone"; 
      
      $Log->insert($AccessUserID,'Bait',$Baits->ID,'insert',$Desc,$AccessProjectID);
      $theaction = "modify";
      $Bait_ID = $Baits->ID;
      add_species($frm_TaxID,$new_species);
    }
  }else{
    if($virtual_Tag){
      $frm_Tag = $virtual_Tag;
      $virtual_Tag = '';
    }
    $baitTitlle = "New gene for IP experiment";
?>    <br>
      <table border="0" cellpadding="0" cellspacing="0" width="700">
    
      <!--table border="0" cellpadding="0" cellspacing="0" width="500"-->      
        <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
           <td colspan="2" align="center" valign="top" height="20">
            <div class=tableheader>
            <?php echo $baitTitlle;?><input type='radio' id='switch_1' name='bait_switch' value='new_bait' <?php echo ($bait_switch=='new_bait')?'checked':''?> onclick="switch_bait_type(this)">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            No gene (control) or non IP experiment<input type='radio' id='switch_2' name='bait_switch' value='no_bait' <?php echo ($bait_switch=='no_bait')?'checked':''?> onclick="switch_bait_type(this)">
            </div>
          </td>
        </tr>
  <?php  
  //---------------------
  include("bait.inc.php"); 
  //---------------------
  ?>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" align="center">
    <td colspan="2">
      <input type="button" value="Save" class=green_but onClick="javascript: checkform('add_form');">&nbsp;&nbsp;&nbsp;
      <input type="button" value="Reset" class=green_but onClick="javascript: reset_form()">
    </td>
  </tr>      
  </table> 
  <br>
<?php 
  }//end of insert
}

if(($theaction == "modify" or $theaction == "update") and $Bait_ID ){
  $frm_LocusTag = trim($frm_LocusTag);
  $frm_GeneName = trim($frm_GeneName);  
  if($theaction == "update"){    
    $isUpdateFlag = 0;
    if($frm_Clone == "dummy"){
      $isUpdateFlag = 1;
    }else if(($frm_LocusTag Or $frm_GeneName) and $frm_Description){          
      $isUpdateFlag = 2;
    }else{
      echo "<center><font color='red' face='helvetica,arial,futura' size=2>";
      echo "Missing info. <b>Bold</b> field names are required to make the insert.";
      echo "</font></center><br>";
    }    
    
    if($isUpdateFlag){
      $Baits->update(
         $Bait_ID,
         $frm_GeneID,
         $frm_LocusTag, 
         $frm_GeneName,
         $frm_BaitAcc,
         $frm_AccType, 
         $frm_TaxID, 
         $frm_BaitMW, 
         $frm_Family, 
         $frm_Tag, 
         $frm_Mutation,
         $frm_Clone, 
         $frm_Vector, 
         $AccessProjectID,
         $frm_Description);
    }       
  //add record into Log table
    if($isUpdateFlag){
      //$Desc = "LocusTag=$frm_LocusTag,GeneName=$frm_GeneName,Clone=$frm_Clone";
      $Desc = "GeneID=$frm_GeneID,AccType=$frm_AccType,BaitMW=$frm_BaitMW,Clone=$frm_Clone"; 
      
      $Log->insert($AccessUserID,'Bait',$Bait_ID,'modify',$Desc,$AccessProjectID);
      echo "<center><font color='red' face='helvetica,arial,futura' size=2>";
      echo "Update complete.";
      echo "</font></center>";
    }
    add_species($frm_TaxID,$new_species);  
    //end of Log table
    $theaction = "modify";   
  }
  echo "<br>";
  if($theaction == "modify"){
    $Baits->fetch($Bait_ID);
    $frm_GeneID = $Baits->GeneID;
    $frm_LocusTag = $Baits->LocusTag;
    $frm_GeneName = $Baits->GeneName;
    $frm_BaitAcc=$Baits->BaitAcc;
    $frm_AccType=$Baits->AccType;
    $frm_TaxID = $Baits->TaxID;
    $frm_BaitMW = $Baits->BaitMW;
    $frm_Family = $Baits->Family;
    $frm_Tag = $Baits->Tag;
    $frm_Mutation = $Baits->Mutation;
    $frm_Clone = $Baits->Clone;
    $frm_Vector = $Baits->Vector;    
    $frm_Description = $Baits->Description;
    $frm_OwnerID = $Baits->OwnerID;
    $frm_DateTime = $Baits->DateTime;
    if($virtual_Tag){
      $frm_Tag = $virtual_Tag;
      $virtual_Tag = '';
    }
?>    
      <table border="0" cellpadding="0" cellspacing="0" width="700">
      <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
    <td colspan="2" align="center" height="20">
<?php 
  if($sub == "2" || $sub == "4" || $frm_GeneName == "dummy"){
    $baitTitlle = "Modify Dummy";
  }else{
    $baitTitlle = "Modify";
  }
?>        
      <div class=tableheader><?php echo $baitTitlle;?></div>
    </td>
  </tr>
  
  <?php 
  //-----------------------------------
  include("bait.inc.php");
  //-----------------------------------
  ?>
   <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" align="center">
    <td colspan="2">
  <?php if($AUTH->Modify ){ ?>
    <input type="button" value="Modify" class=green_but onClick="javascript: checkform('modify_form');">&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="button" value="Reset" class=green_but onClick="javascript: reset_form()">
  <?php }?> 
  &nbsp;&nbsp;&nbsp;
  <?php 
    $SQL ="SELECT `ID`FROM `Experiment` WHERE `BaitID` = '$Bait_ID'";
    $tmpExpArr = $HITSDB->fetch($SQL);
    if($sub){
      $tmpAct = "addnew";
      $tmpExpID = '';
      if($Baits->GelFree == 1){
        if($tmpExpArr){
          $tmpAct = "modify";
          $tmpExpID = $tmpExpArr['ID'];
        }
      }  
  ?>
        <input type="button" value=" Next " class=green_but onClick="javascript: goexperiment('<?php echo $tmpAct?>','<?php echo $tmpExpID?>', '<?php echo $Bait_ID?>');">&nbsp;&nbsp;
  <?php }else{
      if($tmpExpArr){
    ?>   <a  title='experiment detail' href="experiment.php?theaction=viewall&Bait_ID=<?php echo $Bait_ID;?><?php echo ($sub)?"&sub=$sub&Gel_ID=$Gel_ID":"";?>">
          <img border="0" src="images/icon_tree.gif" alt="experiments"></a>&nbsp;
    <?php }else{
        echo "\n<img src=\"images/icon_empty.gif\">&nbsp;";
      }
    }?>
    </td>
  </tr>      
  </table><br> 
<?php 
  }
} //end if
?>
</div>
</form>
<?php if(defined('OPENFREEZER_SEARCH')){?>
<form id='OP_form' name='OP_form' action='<?php echo OPENFREEZER_SEARCH?>' method='post'>
  <input type='hidden' name='GeneID' value=''>
  <input type='hidden' name='GeneName' value=''>
  <input type='hidden' name='LocusTag' value=''>
  <input type='hidden' name='TaxID' value=''>
  <input type='hidden' name='Vector' value=''>
  <input type='hidden' name='OF_session_id' value=''>
  <input type='hidden' name='CellLine' value=''>
  <input type='hidden' name='theaction' value=''>
</form>
<script language="javascript">  
function send_req_to_OF(theForm){
//alert(theForm.OF_session_id.value);
  var of_Form = document.getElementById('OP_form');
  var GeneName = of_Form.GeneName.value = theForm.frm_GeneName.value;
  var GeneID = of_Form.GeneID.value = theForm.frm_GeneID.value;
  var TaxID = of_Form.TaxID.value = theForm.frm_TaxID.value;
  var OF_session_id = of_Form.OF_session_id.value = theForm.OF_session_id.value;
  var Vector = of_Form.Vector.value = theForm.frm_Vector.value;
  var CellLine = of_Form.CellLine.value = theForm.frm_CellLine.value;
  //var Reagent_ID = of_Form.Reagent_ID.value = theForm.frm_Reagent_ID.value;
  of_Form.target = 'view';  
  /*if(TaxID == ""){
    alert('Please Choose a species.');
  }else*/ 
  if(!isNumber(GeneID)){
    alert('Please type only numbers in GineID field.');  
  }else if(isEmptyStr(GeneName) && isEmptyStr(GeneID) && isEmptyStr(Vector)){
    alert('Please type Gene ID or Locus Tag or Gene Name or Vector.');
  }else{
    newwin = window.open('', 'view','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=1200,height=800');
    //newwin.moveTo(1,1);
    of_Form.submit();   
  }
}
</script>
<?php 
}
require("site_footer.php");
if(isset($noBaitArr)){
?>
<style type="text/css">
<?php 
foreach($noBaitArr as $value){
    echo ".bait".$value."\n";
    echo "{ background-color: ".$NoBaitFoundColor."; }\n";
}
?>
</style>
<?php 
}
?> 

