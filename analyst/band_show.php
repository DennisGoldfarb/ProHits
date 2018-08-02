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

define ("RESULTS_PER_PAGE", 100);
define ("MAX_PAGES", 12);

$table = '';
$theaction = '';
$order_by = ''; 
$start_point = ''; 
$Gel_ID = ''; 
$Bait_ID = ''; 
$Exp_ID = ''; 
$Lane_ID = ''; 
$modify_intensity = '';
$searched_sample_str = ''; 
$searched_id_str = '';
$searched_id_vl_str = '';
$band_id_value_arr = array();
$title_lable = '';
$sample_format_detault_arr = array('S.BaitID','SB.GeneName', 'SB.Tag', 'S.OwnerID', 'S.DateTime');
$sample_lable_arr = array(
	'S.BaitID'=>"BaitID",
	'S.LocusTag'=>"LocusTag",
	'S.DateTime'=>"Date",
	'S.OwnerID'=>"User",
	'S.BaitID'=>"BaitID",
	'SB.GeneID'=>"BaitGeneID",
	'SB.GeneName'=>"BaitGene"
);

$frm_Bait_groups = '';
$frm_Experiment_groups = '';
$frm_Band_groups = '';
$frm_Band_z_groups = '';
$frm_group_id = '';
$frm_user_id = '';
$frm_group_id_list = '';
$toggle_group_status = '1';
$toggle_color_status = '1';

$bg_tb_header = '#71a695';
$group_lable_descipt_bgcolor = "#fffafa";

require("../common/site_permission.inc.php");
require("common/page_counter_class.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require("analyst/status_fun_inc.php");
require("analyst/site_header.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$toggle_arr = array();
$used_group_arr = array();
$machine_name_icon_arr = array();
$all_group_recodes_str = '';

if($frm_group_id_list){
  $all_group_recodes_str = get_all_group_recordes_str($frm_group_id_list,'Band');
//echo "$all_group_recodes_str=$all_group_recodes_str<br>";
}
$DB_name = $HITSDB->selected_db_name;
$exist_Hits_tables_arr = exist_hits_table($DB_name);
//=============================================================================
$group_id_arr = explode(",", $frm_group_id_list);
foreach($group_id_arr as $group_id_val){
  if(strstr($group_id_val, 'Bait')){
    if(!in_array('Bait', $toggle_arr)){
      array_push($toggle_arr, 'Bait');
    }
  }elseif(strstr($group_id_val, 'Experiment')){
    if(!in_array('Experiment', $toggle_arr)){
      array_push($toggle_arr, 'Experiment');
    }
  }elseif(strstr($group_id_val, 'Band_z')){
    if(!in_array('Band_z', $toggle_arr)){
      array_push($toggle_arr, 'Band_z');
    }
  }elseif(strstr($group_id_val, 'Band')){
    if(!in_array('Band', $toggle_arr)){
      array_push($toggle_arr, 'Band');
    }
  }
} 

if($frm_Bait_groups){
  if(!in_array('Bait', $toggle_arr)){
    array_push($toggle_arr, 'Bait');
  }  
}
if($frm_Experiment_groups){
  if(!in_array('Experiment', $toggle_arr)){
    array_push($toggle_arr, 'Experiment');
  }  
}
if($frm_Band_groups){
  if(!in_array('Band', $toggle_arr)){
    array_push($toggle_arr, 'Band');
  }  
}
if($frm_Band_z_groups){
  if(!in_array('Band_z', $toggle_arr)){
    array_push($toggle_arr, 'Band_z');
  }  
}
//page counter start here----------
$SQL = "SELECT COUNT(ID) AS Total_records FROM Band 
        WHERE ProjectID='$AccessProjectID' ";
if($frm_user_id){
  $SQL2 = $SQL . " AND OwnerID = $frm_user_id ";
  $tmp_arr_m = $HITSDB->fetch($SQL2);
  if($tmp_arr_m['Total_records']){
    $SQL = $SQL2;
  }else{
    if(isset($first_show)){
      $frm_user_id = '';
    }else{
      $SQL = $SQL2;
    }
  }
}
if($all_group_recodes_str){
  $SQL .= " AND ID IN($all_group_recodes_str) ";
}elseif(!$all_group_recodes_str && $frm_group_id_list){
  $SQL = '';
}

if($SQL){
  $row = mysqli_fetch_row(mysqli_query($HITSDB->link, $SQL));
  $total_records = $row[0];
}else{
  $total_records = 0;
}

if($searched_id_str) $searched_sample_str = $searched_id_str;

$PAGE_COUNTER = new PageCounter('Exp_Status');
$caption = "Bands";
if(!$order_by) $order_by = "S.ID desc";
$query_string = "order_by=".$order_by;
if($theaction) $query_string .= "&theaction=".$theaction;
 
$page_output = $PAGE_COUNTER->page_links($start_point, $total_records, RESULTS_PER_PAGE, MAX_PAGES,$query_string); 

$users_ID_NameArr = get_users_ID_Name($HITSDB);
$session_comparsion_arr = get_comparison_session("Sample", 'array');
$bgcolor = "#d6e3e4";

if($searched_id_vl_str and $theaction == 'search'){

  $searchE_arr = array();
  $searchE_type_counter = 0;
  $tmp_arr = explode(":", $searched_id_vl_str);
  foreach($tmp_arr as $value){
    $tmp_arr2 = explode(",", $value);
    if(count($tmp_arr2)==2){
      $tmp_arr3 = explode(" ", $tmp_arr2[1]);
      if(!array_key_exists($tmp_arr3[0], $searchE_arr)){
        $searchE_arr[$tmp_arr3[0]] = ++$searchE_type_counter;
      }
      $band_id_value_arr[$tmp_arr2[0]] = $searchE_arr[$tmp_arr3[0]].','.$tmp_arr2[1];
    }
  } 
}
?>
<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language="javascript">
function view_image(Gel_ID)  {
  file = 'gel_view.php?Gel_ID=' + Gel_ID;
  newwin = window.open(file,"gel_image",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=750,height=600');
  newwin.moveTo(10,10);
}

function pop_noteType(TypeID,kind,frm_Type){
  var file = 'pop_noteType.php?frm_ID=' + TypeID + '&kind=' + kind + '&frm_Type=' + frm_Type;
  popwin(file,650,300);
}

function sortList(order_by){
  var theForm = document.band_form;
  theForm.order_by.value = order_by;
  set_group_id_list(theForm);
  theForm.submit();
}

function Exp_Status(temp_point){
  var theForm = document.band_form;
  theForm.start_point.value = temp_point;
  //set_group_id_list(theForm);
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
//==========================================================
function change_groups(theForm){
  /*theForm.frm_Bait_groups.checked = false;
  theForm.frm_Experiment_groups.checked = false;
  theForm.frm_Band_groups.checked = false;
  theForm.frm_Band_z_groups.checked = false;*/
  theForm.start_point.value = 0;
  set_group_id_list(theForm);
//theForm.theaction.value = 'viewall';
  theForm.title_lable.value = '';
  theForm.submit();
}
//============================================================  
function export_search_result(){
  var theForm = document.band_form;
  theForm.action = 'export_bait_to_hits.php';
  theForm.submit();
}
function download_search_result(){
  var theForm = document.band_form;
  theForm.action = 'download_search_result.php';
  theForm.submit();
}
 
</script>
<?php if($sub){?>
<div style="width:650px;word-spacing:5px;padding-left:0px;padding-top:6px;white-space:nowrap;text-align:centre;">
  <?php if($Gel_ID){?>
      <a href='./gel.php?sub=<?php echo $sub;?>'><img src="./images/arrow_green_gel.gif" border=0></a>
  <?php }
    if($Bait_ID){?>
      <a href='./bait.php?sub=<?php echo $sub."&Gel_ID=$Gel_ID";?>'><img src="./images/arrow_green_bait.gif" border=0>
  <?php }?>
      <a href='./experiment.php?Gel_ID=<?php echo "$Gel_ID&Bait_ID=$Bait_ID&sub=$sub";?>'><img src="./images/arrow_green_exp.gif" border=0></a>
      <a href='./band.php?Gel_ID=<?php echo "$Gel_ID&Bait_ID=$Bait_ID&Exp_ID=$Exp_ID";?>&theaction=viewband&Lane_ID=<?php echo $Lane_ID;?>&sub=<?php echo $sub;?>'><img src="./images/arrow_green_band.gif" border=0></a>
      <img src="./images/arrow_red_well.gif" border=0>
</div>
<?php }?>
<form name=band_form id='band_form' action=<?php echo $PHP_SELF;?> method=post>
<input type=hidden name=theaction value='<?php echo $theaction;?>'>
<input type=hidden name=order_by value='<?php echo $order_by?>'>
<input type=hidden name=sub value=<?php echo $sub;?>>
<input type=hidden name=Gel_ID value=<?php echo $Gel_ID;?>>
<input type=hidden name=Bait_ID value=<?php echo $Bait_ID;?>>
<input type=hidden name=Exp_ID value=<?php echo $Exp_ID;?>>
<input type=hidden name=Lane_ID value=<?php echo $Lane_ID;?>>
<input type=hidden name=searched_id_vl_str  value="<?php echo $searched_id_vl_str;?>">
<input type=hidden name=searched_sample_str value='<?php echo $searched_sample_str;?>'>
<input type=hidden name=table  value="<?php echo $table;?>">
<input type=hidden name=title_lable value='<?php echo $title_lable;?>'>
<input type=hidden name=frm_group_id_list  value='<?php echo $frm_group_id_list;?>'>
<input type=hidden name=start_point  value='<?php echo $start_point?>'>
<input type=hidden name=searched_id_str  value='<?php echo $searched_id_str?>'>
<input type=hidden name=item_type value='Band'>
<input type=hidden name=Add value='new'>
<input type=hidden name=firstDisplay value='y'>

<div style="width:95%;border: red solid 0px;">
  <div style="width:100%;border: black solid 0px;text-align:left;">
    <img src="images/icon_picture.gif"> Gel Image 
    <img src="images/icon_plate.gif"> Band in Plate 
    <img src="images/arrow_small.gif"> Add to Plate 
    <img src="images/icon_report.gif"> Report 
  </div> 
  <div style="width:100%;height:40px;border: red solid 0px;text-align:left;">
    <!--div style="float:left;border: blue solid 1px;text-align:left;height:100%"-->
    <div style="float:left;padding-top:10px;white-space:nowrap;text-align:left;">

		<font color="navy" face="helvetica,arial,futura" size="5"><b><?php echo ($title_lable)?urldecode($title_lable):"Samples";?> 
    <?php 
    if($AccessProjectName){
      echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font>";
    }
    ?>
    </b>
    </font>
		<?php 
			//-----------------------------------------------------------------------------
		if($theaction == 'viewall' or  $theaction == 'search' or !$theaction) include("./display_format.inc.php");
			//-----------------------------------------------------------------------------
    ?>  
    </div>
    <div style="float:left;padding-top:20px;white-space:nowrap;text-align:left;">
    <?php  
		if($theaction == "viewall"){?>
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
  <?php if($title_lable){?>
    <div style="float:right;padding-top:20px;white-space:nowrap;text-align:left;">
      <a href="javascript: export_search_result()" class=button>[Export]</a>&nbsp;
    </div>   
  <?php }?>
  </div>
 <div style="width:100%;border: red solid 0px">
  <hr>
 </div>   
<?php 
  //if(!$order_by) $order_by = "S.ID desc";  
  if(!$start_point) $start_point = 0;
  if($theaction == 'search' and $searched_sample_str){
    $SQL = "SELECT 
          S.ID,
          S.ExpID, 
          S.LaneID,
          S.BaitID,
          S.Location,
          S.OwnerID,
          S.DateTime,
          S.InPlate,
          SB.ID AS BaitID,
					SB.GeneID,
          SB.Tag,
          SB.GelFree,
          SB.GeneName,
					SB.Tag,
					SB.Mutation,
          SB.Clone,
					SB.Vector 
          FROM Band S left join Bait SB on (SB.ID = S.BaitID) 
          WHERE S.ProjectID='$AccessProjectID'
          And S.ID in($searched_sample_str) 
          ORDER BY $order_by";
    $page_output = '';
  }else{
    $SQL = "SELECT 
          S.ID,
          S.ExpID, 
          S.LaneID,
          S.BaitID,
          S.Location,
          S.OwnerID,
          S.DateTime,
          S.InPlate,
          SB.ID AS BaitID,
					SB.GeneID,
          SB.Tag,
          SB.GelFree,
          SB.GeneName,
					SB.Tag,
					SB.Mutation,
          SB.Clone,
					SB.Vector 
          FROM Band S left join Bait SB on (SB.ID = S.BaitID) 
          WHERE S.ProjectID='$AccessProjectID'";
    if($frm_user_id){
      $SQL .= " AND S.OwnerID = $frm_user_id ";
    }else{
      $frm_user_id = '';
    }    
    if($all_group_recodes_str){
      $SQL .= " AND S.ID IN($all_group_recodes_str) ";
    }elseif(!$all_group_recodes_str && $frm_group_id_list){
      $SQL = '';
    }
    if($SQL){
      $SQL .= "ORDER BY $order_by LIMIT $start_point,".RESULTS_PER_PAGE;
    }        
  }  
  $Bands = array();
  if($SQL){
    $Bands = $HITSDB->fetchAll($SQL);
  }
  $tmpBandsIDarr = array();
  foreach($Bands as $tmpValue){
    if($tmpValue['GelFree']){
      if(!in_array($tmpValue['ID'], $tmpBandsIDarr)){
        array_push($tmpBandsIDarr, $tmpValue['ID']);
      }
    }
  } 
  $tmpBandsIDstr = implode(",", $tmpBandsIDarr);
  $idedBandsArr = get_idedItemsArr($tmpBandsIDstr,'Band');
?> 
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
	$sample_format_arr = array();
	$exp_format_arr = array();
	$exp_format_str = '';
	$colums_number = 0;
  
	if($displayFormat){
		$tmp_arr = explode(",", $displayFormat['Format']);
		foreach($tmp_arr as $value){
			if(preg_match("/^([SE]|SB)\.(.+)/", $value, $matches)){
				if(count($matches) == 3){
					if($matches[1] == 'E'){
					  if($exp_format_str) $exp_format_str .=",";
						$exp_format_str .= $matches[2];
						$colums_number++;
						if(isset($exp_optionID_name_array) and isset($exp_optionID_name_array[$matches[2]])){
							array_push($sample_format_arr, $exp_optionID_name_array[$matches[2]]);
						}
					}else{
						array_push($sample_format_arr, $value);
						$colums_number++;
					}
				}
			}
		}
	}
	if(!$colums_number){
		$sample_format_arr = $sample_format_detault_arr;
	}
  
	//----------------------------------------------------------
	?>
  <input type="hidden" name="displayFormat_str" value="<?php echo $displayFormat['Format']?>">
  <div style="float:left;width:100%;border: yellow solid 0px">
	<table border="0" width=100% cellpadding="1" cellspacing="1">
	<tr bgcolor="">
	  <td width="6%" height="25" bgcolor="<?php echo $bg_tb_header;?>" align=center><div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "S.ID")? 'S.ID%20desc':'S.ID';?>');">Sample ID</a>&nbsp;
		<?php if($order_by == "S.ID") echo "<img src='images/icon_order_up.gif'>";
			if($order_by == "S.ID desc") echo "<img src='images/icon_order_down.gif'>";
		?></div>   
	  </td>
    <td width="12%" height="25" bgcolor="<?php echo $bg_tb_header;?>" align=center><div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "S.Location")? 'S.Location%20desc':'S.Location';?>');">Sample Name</a>&nbsp;
	    <?php if($order_by == "S.Location") echo "<img src='images/icon_order_up.gif'>";
			if($order_by == "S.Location desc" ) echo "<img src='images/icon_order_down.gif'>";
		  ?></div> 
    </td>
		<?php 
		foreach($sample_format_arr as $value){
		  echo "\n<td width='' bgcolor='".$bg_tb_header."' align=center><div class=tableheader>";
		  if(!is_array($value)){
		  	$tmp_sort = ($order_by == $value)? $value."%20desc":$value;
	      echo "<a href=\"javascript: sortList('".$tmp_sort."');\">";
				echo (isset($sample_lable_arr[$value]))?$sample_lable_arr[$value]:$value;
				echo "</a>&nbsp;\n";
	    	if($order_by == $value) echo "<img src='images/icon_order_up.gif' border=0>";
				if($order_by == "$value desc") echo "<img src='images/icon_order_down.gif' border=0>";
			}else{
				echo $value['Name'];
			}
	    echo "</div></td>\n";
		}
		?>
    <td width="30%" bgcolor="<?php echo $bg_tb_header;?>" align=center>
      <div class=tableheader>Exp. Status</div>
      <div class=tableheader>
        Show groups: <input type="checkbox" id="frm_Bait_groups" name="frm_Bait_groups" value="Bait" <?php echo ($frm_Bait_groups)?'checked':''?> onClick="toggle_group(this)">Bait&nbsp;
        	<input type="checkbox" id="frm_Experiment_groups" name="frm_Experiment_groups" value="Experiment" <?php echo ($frm_Experiment_groups)?'checked':''?> onClick="toggle_group(this)">Experiment&nbsp;
        	<input type="checkbox" id="frm_Band_groups" name="frm_Band_groups" value="Band" <?php echo ($frm_Band_groups)?'checked':''?> onClick="toggle_group(this)" >Sample&nbsp;
          <input type="checkbox" id="frm_Band_z_groups" name="frm_Band_z_groups" value="Band_z" <?php echo ($frm_Band_z_groups)?'checked':''?> onClick="toggle_group(this)">Version&nbsp;
      </div>
	  </td>
  <?php if($band_id_value_arr and $theaction == 'search'){?>
    <td width="10%" bgcolor="<?php echo $bg_tb_header;?>" align="center"  colspan="<?php echo $searchE_type_counter?>">
      <div class=tableheader>Score or Probability /<br> # Peptide</div>
    </td>
  <?php }?>
	  <td width="10%" bgcolor="<?php echo $bg_tb_header;?>" align=center>
	    <div class=tableheader>Options</div>
	  </td>
	</tr>
<?php 
$noBandArr = array();
foreach($Bands as $BandValue){
  $ownerName = '';
	if(isset($users_ID_NameArr[$BandValue['OwnerID']])){
			$ownerName = $users_ID_NameArr[$BandValue['OwnerID']];
	}
  $SQL = "SELECT GelID FROM Lane WHERE ID='".$BandValue['LaneID']."'";
  $LaneArr = $HITSDB->fetch($SQL);
  $Gel_ID = ($LaneArr)?$LaneArr['GelID']:'';
  $Bait_ID = $BandValue['BaitID'];
  $Exp_ID = $BandValue['ExpID'];
  $Lane_ID = $BandValue['LaneID'];
  $Band_ID = $BandValue['ID'];
  $gelFree = $BandValue['GelFree'];
	$sample_checked = (in_array($Band_ID, $session_comparsion_arr))? " checked": "";
	$sample_checkbox_disabled = (!has_hits($Band_ID, 'Sample'))? " disabled":"";
?>  
	<tr bgcolor="<?php echo $bgcolor;?>" onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $bgcolor;?>')";>
	  <td width="" ><div class=maintext>
		 <input type="checkbox" name="frm_check<?php echo $BandValue['ID'];?>" value="1" onClick="add_compare(this, '<?php echo $BandValue['ID'];?>', 'Sample')"<?php echo $sample_checked . $sample_checkbox_disabled;?>>
		<?php echo $BandValue['ID'];?>&nbsp;</div>
		</td>    
    <td width="" align="left" <?php echo ($BandValue['Location'])?"class='".$BandValue['Location']."'" : "";?>><div class=maintext>&nbsp;
    <?php echo $BandValue['Location'];?>&nbsp;</div></td>
		<?php 
			foreach($sample_format_arr as $value){
			  $value = preg_replace("/^(S|SB)\./", "", $value);
				 
			  $tmp_bgcolor = '';
			  $tmp_display = '';
				if(!is_array($value)){
					//$tmp_arr = $BandValue[$value];
					if($value == 'OwnerID'){
						$tmp_display = $ownerName;
					}else if($value=='DateTime'){
					  $tmp_display = substr($BandValue['DateTime'],0,10);
					}else{
						$tmp_display = $BandValue[$value];
					}
				}else{
					  if($tmp_display) $tmp_display .='<br>';
      			$SQL = "SELECT OptionID FROM ExpDetail WHERE ExpID='".$BandValue['ExpID']."' and SelectionID='".$value['ID']."'";
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
					 
					$tmp_bgcolor = '#b6c5e0';
				}
				echo "\n<td width='' bgcolor='$tmp_bgcolor' valign=top align=\"left\"><div class=maintext>&nbsp;".$tmp_display."</div></td>\n";
			}
		?>
	  <td width="" nowrap>
      <DIV id="R_<?php echo $BandValue['ID']?>">
<?php 
        $statusArr = get_status($BandValue['ID'],"Band",$toggle_arr);
        $dark = '';
        if($noteTypeID_str = $statusArr['has_note']) $dark = "_dark";
?>  
      </DIV>
    </td>
<?php 
  if(!in_array($Band_ID, $idedBandsArr) && $statusArr['total_hits']) array_push($noBandArr, $BandValue['Location']);
 
  if($band_id_value_arr and $theaction == 'search'){
    $E_tmp_arr = explode(",", $band_id_value_arr[$BandValue['ID']]);
    for($z=1;$z<=$searchE_type_counter;$z++){
      if($z == $E_tmp_arr[0]){?>
        <td align="left" nowrap><div class=maintext><?php echo $E_tmp_arr[1];?></div></td>
<?php     }else{?>
        <td align="left"><div class=maintext>&nbsp;</div></td>
<?php     }
    }   
  } 
 ?>
     <td><div class=maintext>&nbsp;
<?php 
  if($AUTH->Access){
    if($BandValue['GelFree']){
      $tmp_url = "plate_free.php?";
    }else{
      $tmp_url = "band.php?";
    }
?>    
      <a  title='sample detail' href="<?php echo $tmp_url?>theaction=modifyband&Band_ID=<?php echo $BandValue['ID']?><?php echo  ($sub)?"&sub=$sub&Gel_ID=$Gel_ID":"";?>">
      <img border="0" src="images/icon_view.gif" alt="Modify"></a>&nbsp;
<?php }else{
    echo "\n<img src=\"images/icon_empty.gif\">&nbsp;";
  }
  
  if($statusArr['has_report']){
    $hitType = $statusArr['hitType'];
    if($table == 'TPP') $hitType = 'TPP';
    if($gelFree){
      if($statusArr['num_hits'] || $statusArr['num_hitsTppProt']){
?>  
        <a  title="sample report" href="./item_report.php?type=Sample&item_ID=<?php echo $Band_ID?>&noteTypeID_str=<?php echo $noteTypeID_str?>&hitType=<?php echo $hitType;?>" style='text-decoration:none'>
          <img src="./images/icon_report.gif" border=0 alt="Sample Report">
        </a>             
<?php     }
      if($statusArr['num_hitsGeneLevel']){?>
        <a  title="gene level sample report" href="./item_report.php?type=Sample&item_ID=<?php echo $Band_ID?>&noteTypeID_str=<?php echo $noteTypeID_str?>&hitType=geneLevel" style='text-decoration:none'>
          <img src="./images/icon_report_uploaded.gif" border=0 alt="Sample Report">
        </a>
<?php     }   
    }else{
      if($statusArr['num_hits'] || $statusArr['num_hitsTppProt']){
?> 
        <a  title="sample report" href="javascript: popwin('pop_plate_show.php?Gel_ID=<?php echo $Gel_ID?>&Bait_ID=<?php echo $Bait_ID?>&Exp_ID=<?php echo $Exp_ID?>&Lane_ID=<?php echo $Lane_ID?>&Band_ID=<?php echo $Band_ID?>&gelFree=<?php echo $gelFree?>&hitType=<?php echo $hitType;?>&theaction=showone',850,600)" style='text-decoration:none'>
          <img src="./images/icon_report.gif" border=0 alt="Sample Report">
        </a> 
<?php     }
      if($statusArr['num_hitsGeneLevel']){?>
        <a  title="gene level sample report" href="./item_report.php?type=Sample&item_ID=<?php echo $Band_ID?>&noteTypeID_str=<?php echo $noteTypeID_str?>&hitType=geneLevel" style='text-decoration:none'>
           <img src="./images/icon_report_uploaded.gif" border=0 alt="Sample Report">
        </a>
<?php     }
    }
  }else{
    echo "<img src=\"images/icon_empty.gif\" width=17>&nbsp;";
  }
  if(!$BandValue['GelFree']){    
?>	
    <a  title="sample location" href="javascript: popwin('pop_plate.php?Band_ID=<?php echo $Band_ID?>&theaction=showone',750,530)" style='text-decoration:none'>	
    <img border="0" src="./images/icon_plate.gif" alt="band in plate"></a>
<?php }
  echo "<a  title='add notes for the sample' href=\"javascript: popwin('./pop_note.php?item_ID=$Band_ID&item_type=Band', 650,500);\"><img src='./images/icon_notes$dark.gif' border='0' alt='Sample note' align='bottom'></a>\n";                  
  if($Gel_ID){
    echo "<a  title='gel information' href=\"javascript: popwin('./gel_view.php?Gel_ID=$Gel_ID',600,600);\"><img src='./images/icon_picture.gif' border=0></a>";
  }
?>
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
</div>
 </form>
<?php 
require("site_footer.php");
?>
<style type="text/css">
<?php 
foreach($noBandArr as $value){
  echo ".".$value."\n";
  echo "{ background-color: ".$NoBaitFoundColor."; }\n";
}
?>
</style>
<script language="javascript"> 
var group_item_id_arr = new Array();
<?php 
foreach($Bands as $Bands_id){
?>
  group_item_id_arr.push("<?php echo $Bands_id['ID']?>");   
<?php 
}
foreach($used_group_arr as $used_group_val){
  if($used_group_val == 'SAM') continue;
?>
  var obj_id = "frm_"+"<?php echo $used_group_val?>"+"_groups";
  var group_obj = document.getElementById(obj_id);
  display_group(group_obj);
<?php 
}
?>
</script>