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

$table = '';//#########################
$theaction = '';
$order_by = ''; 
$start_point = ''; 
$Gel_ID = ''; 
$Bait_ID = ''; 
$Exp_ID = ''; 
$Lane_ID = ''; 
$modify_intensity = '';
$searched_exp_str = ''; 
$searched_id_str = '';
$searched_id_vl_str = '';
$band_id_value_arr = array();
$title_lable = '';
$experiment_format_detault_arr = array('E.BaitID','EB.GeneName','EB.Tag','E.OwnerID', 'E.DateTime');
$experiment_lable_arr = array(
	'E.BaitID'=>"BaitID",
	'E.Name'=>"Exp. Name",
	'E.DateTime'=>"Date",
	'E.OwnerID'=>"User",
	'S.BaitID'=>"BaitID",
	'EB.GeneID'=>"BaitGeneID",
	'EB.GeneName'=>"BaitGene"
);

$frm_Bait_groups = '';
$frm_Experiment_groups = '';
$frm_user_id = '';
$frm_group_id_list = '';
$frm_group_id = '';
$toggle_group_status = '1';
$toggle_color_status = '1';


$bgcolor = "#e7e7cf";
$bg_tb_header = '#808040';
$group_lable_descipt_bgcolor = "#fffafa";


require("../common/site_permission.inc.php");
require("common/page_counter_class.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require("analyst/status_fun_inc.php");
require("analyst/site_header.php");

$toggle_arr = array();
$used_group_arr = array();
$all_group_recodes_str = '';

if($frm_group_id_list){
  $all_group_recodes_str = get_all_group_recordes_str($frm_group_id_list,'Experiment');
}

$DB_name = $HITSDB->selected_db_name;
$exist_Hits_tables_arr = exist_hits_table($DB_name);

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

//page counter start here----------
$SQL = "SELECT COUNT(ID) AS Total_records FROM Experiment 
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
  $row = mysqli_fetch_row(mysqli_query($mainDB->link, $SQL));
  $total_records = $row[0];
}else{
  $total_records = 0;
}
if($searched_id_str) $searched_exp_str = $searched_id_str;

$PAGE_COUNTER = new PageCounter('Exp_Status');
$caption = "Experiments";

if(!$order_by) $order_by = "E.ID desc";
$query_string = "order_by=".$order_by;
if($theaction) $query_string .= "&theaction=".$theaction;
 
$page_output = $PAGE_COUNTER->page_links($start_point, $total_records, RESULTS_PER_PAGE, MAX_PAGES,$query_string);

$users_ID_NameArr = get_users_ID_Name($HITSDB);
$session_comparsion_arr = get_comparison_session("Exp", 'array');


if($searched_id_vl_str and $theaction == 'search'){
  $tmp_arr = explode(":", $searched_id_vl_str);
  foreach($tmp_arr as $value){
    $tmp_arr2 = explode(",", $value);
    if(count($tmp_arr2)==2){
      $band_id_value_arr[$tmp_arr2[0]] = $tmp_arr2[1];
    }
  } 
}
?>
<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language="javascript">

function view_image(Gel_ID)  {
  file = 'gel_view.php?Gel_ID=' + Gel_ID;
  popwin(file,800,600);
}
function view_western_image(WesternGel,BatchCode) {  
  file = 'western_gel_view.php?WesternGel=' + WesternGel + '&BatchCode=' + BatchCode;
  popwin(file,800,600);
}
function pop_noteType(TypeID,kind,frm_Type){
  var file = 'pop_noteType.php?frm_ID=' + TypeID + '&kind=' + kind + '&frm_Type=' + frm_Type;
  popwin(file,650,300);
}

function sortList(order_by){
  var theForm = document.exp_form;
  theForm.order_by.value = order_by;
  set_group_id_list(theForm);
  theForm.submit();
}

function Exp_Status(temp_point){
  var theForm = document.exp_form;
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
//==================================================================  
function change_groups(theForm){
  theForm.start_point.value = 0;
  set_group_id_list(theForm);
  theForm.title_lable.value = '';
  theForm.submit();
}
//==================================================================
</script>
<?php if($sub){?>
<div style="width:650px;word-spacing:5px;padding-left:0px;padding-top:6px;white-space:nowrap;text-align:centre;">
  <?php if($Gel_ID){?>
    <a href='./gel.php?sub=<?php echo $sub;?>'><img src="./images/arrow_green_gel.gif" border=0></a>
  <?php }
    if($Bait_ID){?>
    <a href='./bait.php?sub=<?php echo $sub."&Gel_ID=$Gel_ID";?>'><img src="./images/arrow_green_bait.gif" border=0></a>
  <?php }?>
    <a href='./experiment.php?Gel_ID=<?php echo "$Gel_ID&Bait_ID=$Bait_ID&sub=$sub";?>'><img src="./images/arrow_green_exp.gif" border=0></a>
    <a href='./band.php?Gel_ID=<?php echo "$Gel_ID&Bait_ID=$Bait_ID&Exp_ID=$Exp_ID";?>&theaction=viewband&Lane_ID=<?php echo $Lane_ID;?>&sub=<?php echo $sub;?>'><img src="./images/arrow_green_band.gif" border=0></a>
    <img src="./images/arrow_red_well.gif" border=0>
</div>
<?php }?>
<form name=exp_form action=<?php echo $PHP_SELF;?> method=post>
<input type=hidden name=theaction value='<?php echo $theaction;?>'>
<input type=hidden name=order_by value='<?php echo $order_by?>'>
<input type=hidden name=sub value=<?php echo $sub;?>>
<input type=hidden name=Gel_ID value=<?php echo $Gel_ID;?>>
<input type=hidden name=Bait_ID value=<?php echo $Bait_ID;?>>
<input type=hidden name=Exp_ID value=<?php echo $Exp_ID;?>>
<input type=hidden name=Lane_ID value=<?php echo $Lane_ID;?>>
<input type=hidden name=searched_id_vl_str  value="<?php echo $searched_id_vl_str;?>">
<input type=hidden name=searched_exp_str value='<?php echo $searched_exp_str;?>'>
<input type=hidden name=table  value="<?php echo $table;?>">
<input type=hidden name=title_lable value='<?php echo $title_lable;?>'>
<input type=hidden name=frm_group_id_list  value='<?php echo $frm_group_id_list;?>'>
<input type=hidden name=start_point  value='<?php echo $start_point?>'>
<div style="width:95%;border: red solid 0px;">
  <div style="width:100%;border: black solid 0px;text-align:left;">
    <div class=maintext>
      <img src="images/icon_picture.gif"> Gel Image 
      <img src="images/icon_plate.gif"> Experiment in Plate 
      <img src="images/arrow_small.gif"> Add to Plate 
      <img src="images/icon_report.gif"> Report 
    </div>
  </div> 
  <div style="width:100%;height:40px;border: red solid 0px;text-align:left;">
    <div style="float:left;padding-top:10px;white-space:nowrap;text-align:left;">
		  <font color="navy" face="helvetica,arial,futura" size="5"><b><?php echo ($title_lable)?urldecode($title_lable):"Experiments";?> 
    <?php 
      if($AccessProjectName){
        echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font>";
      }
      ?>
    </b></font> 
		<?php 
			//-----------------------------------------------------------------------------
			if($theaction == 'view' or  $theaction == 'viewall' or$theaction == 'search' or !$theaction) include("./display_format.inc.php");
			//-----------------------------------------------------------------------------
		?>
    </div>
    <div style="float:left;padding-top:20px;white-space:nowrap;text-align:left;">
    <font face="helvetica,arial,futura" size="2"><b>User</b></font>
    <?php $users_list_arr = show_project_users_list();?>
    <select id="frm_user_id" name="frm_user_id" onchange="change_user(this.form)">
      <option value="">All Users		            
    <?php foreach($users_list_arr as $key => $val){?>              
      <option value="<?php echo $key?>"<?php echo ($frm_user_id==$key)?" selected":"";?>><?php echo $val?>			
    <?php }?>
    </select> 
    </div>
  </div> 
  <div style="width:100%;border: red solid 0px">
    <hr>
  </div>
<?php 
  //$group_id_arr = explode(",", $frm_group_id_list);
  if(!$start_point) $start_point = 0;
  $SQL = "SELECT E.ID,
                E.BaitID,
                E.Name,
                E.OwnerID,
                E.GrowProtocol,
                E.IpProtocol,
                E.DigestProtocol,
                E.PeptideFrag,
                E.PreySource,
                E.Notes,
                E.DateTime,
                E.WesternGel,
      					EB.GeneID,
                EB.Tag,
                EB.GelFree,
                EB.GeneName,
      					EB.Tag,
      					EB.Mutation,
                EB.Clone,
      					EB.Vector 
                FROM Experiment E left join Bait EB on (EB.ID = E.BaitID) 
                WHERE E.ProjectID='$AccessProjectID'";
  if($theaction == 'search' and $searched_exp_str){
    $SQL .= " And E.ID in($searched_exp_str) 
    ORDER BY $order_by";
    $page_output = '';
  }else{
    if($frm_user_id){
      $SQL .= " AND E.OwnerID = $frm_user_id ";
    }
    if($all_group_recodes_str){
      $SQL .= " AND E.ID IN($all_group_recodes_str) ";
    }elseif(!$all_group_recodes_str && $frm_group_id_list){
      $SQL = '';
    }
    if($SQL){
      $SQL .= "ORDER BY $order_by LIMIT $start_point,".RESULTS_PER_PAGE;
    }
  }
    
  $Experiments = array();
  if($SQL){
    $Experiments = $HITSDB->fetchAll($SQL);
  }  
  $tmpExpsIDarr = array();
  foreach($Experiments as $tmpValue){
    if($tmpValue['GelFree']){
      if(!in_array($tmpValue['ID'], $tmpExpsIDarr)){
        array_push($tmpExpsIDarr, $tmpValue['ID']);
      }
    }
  }  
  $tmpExpsIDstr = implode(",", $tmpExpsIDarr);  
  $idedExpsArr = get_idedItemsArr($tmpExpsIDstr,'Experiment');
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
		<div id="divCoord" style="float:right;border: black solid 0px;padding-top:10px;"><?php echo $page_output;?></div>
	</div>
	<?php 
	//get bait and exp column names----------------------------
	$exp_format_arr = array();
	$exp_con_format_str = '';
	$colums_number = 0;
	if($displayFormat){
		$tmp_arr = explode(",", $displayFormat['Format']);
		foreach($tmp_arr as $value){
      $tmp_arr2 = explode(".",$value);
      if(count($tmp_arr2) == 2 AND is_numeric($tmp_arr2[1])){
        if($exp_con_format_str) $exp_con_format_str .=",";
				$exp_con_format_str .= $tmp_arr2[1];
				$colums_number++;
				if(isset($exp_optionID_name_array) and isset($exp_optionID_name_array[$tmp_arr2[1]])){
					array_push($exp_format_arr, $exp_optionID_name_array[$tmp_arr2[1]]);
				}
      }else{
        array_push($exp_format_arr, $value);
				$colums_number++;
      }
    }
  }  
	if(!$colums_number){
		$exp_format_arr = $experiment_format_detault_arr;
	}
	//----------------------------------------------------------
	?>
  <div style="float:left;width:100%;border: yellow solid 0px">
	<table border="0" width=100% cellpadding="1" cellspacing="1">
	<tr bgcolor="">
	  <td width="6%" height="25" bgcolor="<?php echo $bg_tb_header;?>" align=center><div class=tableheader>
		  
      <a href="javascript: sortList('<?php echo ($order_by == "E.ID")? 'E.ID%20desc':'E.ID';?>');">Exp. ID</a>&nbsp;
		<?php if($order_by == "E.ID") echo "<img src='images/icon_order_up.gif'>";
			if($order_by == "E.ID desc") echo "<img src='images/icon_order_down.gif'>";
		?></div>   
	  </td>
    <td width="12%" height="25" bgcolor="<?php echo $bg_tb_header;?>" align=center><div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "E.Name")? 'E.Name%20desc':'E.Name';?>');">Exp. Name</a>&nbsp;
	    <?php if($order_by == "E.Name") echo "<img src='images/icon_order_up.gif'>";
			if($order_by == "E.Name desc" ) echo "<img src='images/icon_order_down.gif'>";
		  ?></div> 
    </td>
		
		<?php 
		foreach($exp_format_arr as $value){
		  echo "\n<td width='' bgcolor='".$bg_tb_header."' align=center><div class=tableheader>";
		  if(!is_array($value)){
		  	$tmp_sort = ($order_by == $value)? $value."%20desc":$value;
	      echo "<a href=\"javascript: sortList('".$tmp_sort."');\">";
				echo (isset($experiment_lable_arr[$value]))?$experiment_lable_arr[$value]:$value;
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
      </div>
	  </td>
    <?php if($band_id_value_arr and $theaction == 'search'){?>
      <td width="10%" bgcolor="<?php echo $bg_tb_header;?>" align="center">
        <div class=tableheader>Score or Probability /<br> # Peptide</div>
      </td>
    <?php }?>
	  <td width="10%" bgcolor="<?php echo $bg_tb_header;?>" align=center>
	    <div class=tableheader>Options</div>
	  </td>
	</tr>
<?php 

$noExpArr = array();

foreach($Experiments as $ExperimentValue){
  $ownerName = '';
	if(isset($users_ID_NameArr[$ExperimentValue['OwnerID']])){
		$ownerName = $users_ID_NameArr[$ExperimentValue['OwnerID']];
	}
  $Bait_ID = $ExperimentValue['BaitID'];
  $Exp_ID = $ExperimentValue['ID'];
  $gelFree = $ExperimentValue['GelFree'];
  
	$experiment_checked = (in_array($Exp_ID, $session_comparsion_arr))? " checked": "";
	$experiment_checkbox_disabled = (!has_hits($Exp_ID, 'Experiment'))? " disabled":"";
  
?>  
	<tr bgcolor="<?php echo $bgcolor;?>" onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $bgcolor;?>')";>
	  <td width="" ><div class=maintext>
		 <input type="checkbox" name="frm_check<?php echo $ExperimentValue['ID'];?>" value="1" onClick="add_compare(this, '<?php echo $ExperimentValue['ID'];?>', 'Exp')"<?php echo $experiment_checked . $experiment_checkbox_disabled;?>>
		<?php echo $ExperimentValue['ID'];?>&nbsp;</div>
		</td>    
    <td width="" align="left" <?php echo ($ExperimentValue['Name'])?"class='".$ExperimentValue['Name']."'" : "";?>><div class=maintext>&nbsp;
    <?php echo $ExperimentValue['Name'];?>&nbsp;</div></td>
		<?php 
			foreach($exp_format_arr as $value){
			  $value = preg_replace("/^(E|EB)\./", "", $value);
			  $tmp_bgcolor = '';
			  $tmp_display = '';
				if(!is_array($value)){
					//$tmp_arr = $ExperimentValue[$value];
					if($value == 'OwnerID'){
						$tmp_display = $ownerName;
					}else if($value=='DateTime'){
					  $tmp_display = substr($ExperimentValue['DateTime'],0,10);
					}else{
						$tmp_display = $ExperimentValue[$value];
					}
				}else{
					  if($tmp_display) $tmp_display .='<br>';
      			$SQL = "SELECT OptionID FROM ExpDetail WHERE ExpID='".$ExperimentValue['ID']."' and SelectionID='".$value['ID']."'";
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
      <DIV id="R_<?php echo $ExperimentValue['ID']?>">
<?php 
        $statusArr = get_status($Exp_ID,"Experiment",$toggle_arr);
        $dark = '';
        if($noteTypeID_str = $statusArr['has_note']) $dark = "_dark";
?>  
      </DIV>
    </td>
<?php 
  if(!in_array($Exp_ID, $idedExpsArr) && $statusArr['total_hits']) array_push($noExpArr, $ExperimentValue['Name']);
  if($band_id_value_arr and $theaction == 'search'){?>
        <td width="" align="left"><div class=maintext>
          <?php echo $band_id_value_arr[$ExperimentValue['ID']];?>
          </div>
        </td>
 <?php }?>
     <td><div class=maintext>&nbsp;
<?php 
  if($AUTH->Access){
?>    
      <a  title='experiment detail' href="experiment.php?theaction=modify&Bait_ID=<?php echo $ExperimentValue['BaitID']?>&Exp_ID=<?php echo $ExperimentValue['ID']?>">
      <img border="0" src="images/icon_view.gif" alt="Modify"></a>&nbsp;
<?php }else{
    echo "\n<img src=\"images/icon_empty.gif\">&nbsp;";
  }
   
  //if($hitType = get_hit_type($Exp_ID,'Experiment')){
  if($statusArr['has_report']){
    $hitType = $statusArr['hitType'];
    if($table == 'TPP') $hitType = 'TPP';
?>  
    <a  title="experiment report" href="./item_report.php?type=Experiment&item_ID=<?php echo $Exp_ID?>&noteTypeID_str=<?php echo $noteTypeID_str?>&hitType=<?php echo $hitType;?>&isGelFree=<?php echo $gelFree;?>" style='text-decoration:none'>
    <img src="./images/icon_report.gif" border=0 alt="Experiment Report"></a>
<?php   if($statusArr['num_hitsGeneLevel']){?>
        <a  title="gene level experiment report" href="./item_report.php?type=Experiment&item_ID=<?php echo $Exp_ID?>&noteTypeID_str=<?php echo $noteTypeID_str?>&hitType=geneLevel&isGelFree=<?php echo $gelFree;?>" style='text-decoration:none'>
           <img src="./images/icon_report_uploaded.gif" border=0 alt="Experiment Report">
        </a>
<?php    }
  }else{
    echo "<img src=\"images/icon_empty.gif\" width=17>&nbsp;";
  }
  echo "<a  title='add notes for the experiment' href=\"javascript: popwin('./pop_note.php?item_ID=$Exp_ID&item_type=Experiment', 650,500);\"><img src='./images/icon_notes$dark.gif' border='0' alt='Experiment note' align='bottom'></a>\n";                  
 
  if($ExperimentValue['WesternGel']){
      $WesternGelArr = explode(",",$ExperimentValue['WesternGel']);
      foreach($WesternGelArr as $value){
        echo "<a href=\"javascript: view_western_image('".$value."','".$ExperimentValue['Name']."');\">";
        echo "<img src='./images/icon_picture.gif' border=0 alt='view image'>";
        echo "</a>&nbsp;";
      }  
    }else{
       echo "\n<img src='./images/icon_empty.gif'>&nbsp;";
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
foreach($noExpArr as $value){
  echo ".".$value."\n";
  echo "{ background-color: ".$NoBaitFoundColor."; }\n";
}
?>
</style>
<script language="javascript"> 
var group_item_id_arr = new Array();
<?php 
foreach($Experiments as $Experiments_id){
?>
  group_item_id_arr.push("<?php echo $Experiments_id['ID']?>");   
<?php 
}
foreach($used_group_arr as $used_group_val){
?>
  var obj_id = "frm_"+"<?php echo $used_group_val?>"+"_groups";
  var group_obj = document.getElementById(obj_id);
  display_group(group_obj);
<?php 
}
?>
</script>