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

$argv[1] = '';
$Plate_ID = 0;
$Band_ID = 0; 
$theaction = '';
$order_by = '';
$start_point = '';
$OwnerID = '';
//-plate_info.inc--------
$PlateName_str = '';
$msg_plate = '';
$in_Plate_ID = '';
$whichPlate = '';
//-plate_layout.inc------
$newplate = '';
$tab = '';
$selectedWellCode_arr = '';
//-plate_band_hits-----
$sub = '';
$noplate = '';
$bg_tb_header = 'black';
$hitType = 'normal';
$item_hits_order_by = '';
$frm_user_id = '';

define ("RESULTS_PER_PAGE", 25);
define ("MAX_PAGES", 15);

require("../common/site_permission.inc.php");
require("analyst/classes/bait_class.php");
require("analyst/classes/experiment_class.php");
require("analyst/classes/gel_class.php");
require("analyst/classes/lane_class.php");
require("analyst/classes/band_class.php");
require("analyst/classes/plate_class.php");
require("analyst/classes/plateWell_class.php");
require("analyst/classes/hits_class.php"); 
require("common/page_counter_class.php");
include("analyst/common_functions.inc.php");
include("common/common_fun.inc.php");
require_once("msManager/is_dir_file.inc.php");

$Log = new Log();

if(isset($_GET['noplate'])){
  require("analyst/site_simple_header.php");
}else{
  require("analyst/site_header.php");
}
 
if($Plate_ID){
   $Plate = new Plate($Plate_ID);
}else if($Band_ID){
   $thisPlateWell = new PlateWell();
	 $band_in_plates_arr = $thisPlateWell->band_in_plate($Band_ID);
	 if(count($band_in_plates_arr)){
	    $Plate_ID = $band_in_plates_arr[0];
	 		$Plate = new Plate($band_in_plates_arr[0]);
	 }else{
	   $Plate = new Plate();
	 }
}else{
   $Plate = new Plate();
}

if($theaction == 'updateplate' and $AUTH->Modify and $Plate_ID){
   //$thePlate = new Plate($Plate_ID);
   if($Plate->MSDate != $frm_MSDate){
      //if ms complited has been chagned, it should check bait Carry Over.
      //this include script will check if a hit is a bait in 'up-stream' on the same plate
      //the included file also will check other filters.
      include("checkCarryOver.inc.php");
   }
   $Plate->update($Plate_ID,$frm_Name,$frm_PlateNotes,$frm_DigestedBy, $frm_DigestStarted,$frm_DigestCompleted,$frm_Buffer,$frm_MSDate);
   $Desc = "DigestStarted=$frm_DigestStarted,DigestCompleted=$frm_DigestCompleted,Buffer=$frm_Buffer,MSDate=$frm_MSDate";
   $Log->insert($AccessUserID,'Plate',$Plate_ID,'modify',$Desc,$AccessProjectID);
   //$Plate = new Plate($Plate_ID);
	 $theaction = 'showone';
}

?>
<script language='javascript'>
 function display_plate(Plate_ID,Hit_type){
  var theForm = document.action_form;
  theForm.theaction.value = 'showone';
  theForm.Plate_ID.value = Plate_ID;
  theForm.hitType.value = Hit_type;
  theForm.submit();
 }
 function view_image(Gel_ID){  
  file = 'gel_view.php?Gel_ID=' + Gel_ID;
  newwin = window.open(file,"gel_image",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=750,height=600');
  newwin.moveTo(10,10);
 }
 function print_view(Plate_ID){
  file = 'plate_view.php?Plate_ID=' + Plate_ID;
  newwin = window.open(file,"plate",'toolbar=1,location=0,directories=0,status=0,menubar=1,scrollbars=1,resizable=1,width=850,height=600');
  newwin.moveTo(10,10);
 }
 function show_all_peptides(Band_ID){
  file = 'show_all_peptides.php?Band_ID=' + Band_ID;
  newwin = window.open(file,"show_all_peptides",'toolbar=1,location=0,directories=0,status=0,menubar=1,scrollbars=1,resizable=1,width=850,height=600');
  newwin.moveTo(10,10);
 }
 function change_user(theForm){
  theForm.start_point.value = 0;
  theForm.theaction.value = '';
  theForm.submit();
}
function sortList(order_by){
  var theForm = document.action_form;
  theForm.order_by.value = order_by;
  theForm.submit();
}
function Exp_Status(temp_point){
  var theForm = document.action_form;
  theForm.start_point.value = temp_point;
  theForm.submit();
}
</script>
<?php if($sub){?>
<div style="width:650px;word-spacing:5px;padding-left:0px;padding-top:6px;white-space:nowrap;text-align:centre;border: red solid 0px;">
  <img src="./images/arrow_green_gel.gif" border=0>
  <img src="./images/arrow_green_bait.gif" border=0>
  <img src="./images/arrow_green_exp.gif" border=0>
  <img src="./images/arrow_green_band.gif" border=0>
  <img src="./images/arrow_red_well.gif" border=0>
</div>
<?php }?>
   <form name=action_form action=<?php echo $PHP_SELF;?> method=post>
   <input type=hidden name=Band_ID value=<?php echo $Band_ID;?>>
   <input type=hidden name=Plate_ID value='<?php echo $Plate_ID;?>'>
   <input type=hidden name=start_point value='<?php echo $start_point?>'>
   <input type=hidden name=theaction value=''>
   <input type=hidden name=whichPlate value=''>
   <input type=hidden name=frm_WellCode value=''>
   <input type=hidden name=sub value=<?php echo $sub;?>>
   <input type=hidden name=noplate value=<?php echo $noplate;?>>
   <input type=hidden name=hitType value='<?php echo $hitType?>'> 
   <input type=hidden name=order_by value='<?php echo $order_by?>'> 
<div style="width:95%;border: red solid 1px;border: black solid 0px;">
<?php if(!$noplate){?>
  <div style="width:100%;border: green solid 0px;text-align:left;">
    <div class=maintext>
      <img src="images/icon_but.gif"  height=15> Available Well 
      <img src="images/icon_curr_band.gif"  height=15> Current Band
      <img src="images/icon_first.gif" height=15> First Plate
      <img src="images/icon_previous.gif" height=15> Previous Plate
      <img src="images/icon_next.gif"  height=15> Next Plate
      <img src="images/icon_last.gif"  height=15> Last Plate
      <img src="images/icon_plate.gif"  height=17> Plate
      <img src="images/icon_plate_check.gif"  height=17> MS Completed Plate
      <img src="images/icon_report.gif"  height=17> Plate Report
    </div>
  </div> 
  <div style="width:100%;height:40px;border: green solid 0px;text-align:left;">
    <div style="float:left;padding-top:10px;white-space:nowrap;text-align:left;">
		  <font color="navy" face="helvetica,arial,futura" size="5"><b>Plates & Wells
    <?php 
      if($AccessProjectName){
        echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font>";
      }
    ?>
      </b> 
  	  </font>
    </div>
    <div style="float:left;padding-top:20px;white-space:nowrap;text-align:left;">  
    <?php if(!$theaction){
      $Plates = new Plate(); 
      $total = $Plates->get_total();    
    ?>
      &nbsp;&nbsp;<font face="helvetica,arial,futura" size="2"><b>User</b></font>
      <?php $users_list_arr = show_project_users_list();?>
      <select id="frm_user_id" name="frm_user_id" onchange="change_user(this.form)">
        <option value="">All Users		            
      <?php foreach($users_list_arr as $key => $val){?>              
        <option value="<?php echo $key?>"<?php echo ($frm_user_id==$key)?" selected":"";?>>(<?php echo $key?>)<?php echo $val?>			
      <?php }?>
      </select> 
    <?php }else{
        echo "&nbsp;";
      }?> 
	  </div>
    <?php if($theaction and $Plate_ID and $AUTH->Access){?>
    <div style="float:right;padding-top:20px;white-space:nowrap;text-align:left;"> 
       <a href="javascript: print_view('<?php echo $Plate_ID;?>');" class=button>[Print Preview]</a>&nbsp;
       <a href="<?php echo $PHP_SELF;?>" class=button>[Plate List]</a>
    </div>
    <?php }?>
  </div> 
<?php }else{?>	
  <div style="width:100%;height:40px;border: red solid 0px;text-align:left;">
    <div style="float:left;padding-top:10px;white-space:nowrap;text-align:left;">
		&nbsp; <font color="navy" face="helvetica,arial,futura" size="3"><b>Band
    <?php 
      if($AccessProjectName){
        echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project: $AccessProjectName)</font>";
      }
    ?>
    </b> 
		</font>
	  </div>
    <div style="float:right;padding-top:20px;white-space:nowrap;text-align:left;">    
     <a href="javascript: window.close();" class=button>[Close Window]</a>      
    </div>
  </div>
<?php }?>  
  <div style="width:100%;border: red solid 0px">
    <hr>
  </div>
<?php 
if(!$theaction){//list plate ---------------------
  if(!$order_by) $order_by = "P.ID desc";
  if(!$start_point) $start_point = 0;
  $Plates->fetchall_list($order_by,$start_point, RESULTS_PER_PAGE);
   
  //page counter start here
  $PAGE_COUNTER = new PageCounter('Exp_Status');
  $query_string = "";
  $caption = "Plates";
  if($order_by){
    $query_string .= "&order_by=".$order_by;
  }
  $page_output = $PAGE_COUNTER->page_links($start_point, $total, RESULTS_PER_PAGE, MAX_PAGES,$query_string); 
?>   
	<div style="float:left;width:100%;border: yellow solid 0px">
  <table border="0" cellpadding="0" cellspacing="1" width="720">
  <tr><td colspan=6 align=right><?php echo $page_output;?></td></tr>
	<tr bgcolor="">  
	  <td width="60" height="25" bgcolor="<?php echo $bg_tb_header;?>" align=center>
	    <a href="javascript: sortList('<?php echo ($order_by == "ID")? 'ID%20desc':'ID';?>');">
        <div class=tableheader>ID</div>
      </a>
      <?php 
        if($order_by == "ID") echo "<img src='images/icon_order_up.gif'>";
        if($order_by == "ID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
      ?>
	  </td>
	  <td width="140" height="25" bgcolor="<?php echo $bg_tb_header;?>" align=center>
      <a href="javascript: sortList('<?php echo ($order_by == "P.Name")? 'P.Name%20desc':'P.Name';?>');">
        <div class=tableheader>Plate Name</div>
      </a>
      <?php 
        if($order_by == "P.Name") echo "<img src='images/icon_order_up.gif'>";
        if($order_by == "P.Name desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
      ?>
	  </td>
	  <td width="150" bgcolor="<?php echo $bg_tb_header;?>" align=center><div class=tableheader>
		    Created By</div>
	  </td>
	  <td width="150" bgcolor="<?php echo $bg_tb_header;?>" align="center" align=center>
      <a href="javascript: sortList('<?php echo ($order_by == "P.DateTime")? 'P.DateTime%20desc':'P.DateTime';?>');">
	      <div class=tableheader>Created On</div>
      </a>
      <?php 
        if($order_by == "P.DateTime") echo "<img src='images/icon_order_up.gif'>";
        if($order_by == "P.DateTime desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
      ?>
	  </td>
    <td width="100" bgcolor="<?php echo $bg_tb_header;?>" align="center" align=center>
      <a href="javascript: sortList('<?php echo ($order_by == "P.MSDate")? 'P.MSDate%20desc':'P.MSDate';?>');">
	      <div class=tableheader>MS Completed</div>
      </a>
      <?php 
        if($order_by == "P.MSDate") echo "<img src='images/icon_order_up.gif'>";
        if($order_by == "P.MSDate desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
      ?>
	  </td>
	  </td>
	  <td width="70" bgcolor="<?php echo $bg_tb_header;?>" align="center">
	    <div class=tableheader>Options</div>
	  </td>
	</tr>
<?php 
  for($i=0; $i < $Plates->count; $i++) {
    $theOwner = '';
    if($Plates->OwnerID[$i]){
      $OwnerID = $Plates->OwnerID[$i];
      if(isset($_SESSION['USER']->UsersArr[$OwnerID])){    
        $theOwner = $_SESSION['USER']->UsersArr[$OwnerID];
      }  
    } 
?>
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td width="" align="left">
      <div class=maintext>&nbsp; &nbsp;
	      <?php echo $Plates->ID[$i];?>&nbsp;
	    </div>
	  </td>
	  <td width="" align="center">
      <div class=maintext>
	      <?php echo $Plates->Name[$i];?>&nbsp;
	    </div>
	  </td>
	  <td width="" align="center">
      <div class=maintext>
	      <?php echo $theOwner;?>&nbsp;
	      </div>
	  </td>
	  <td width="" align="center">
      <div class=maintext>
	      <?php echo  $Plates->DateTime[$i] ;?>&nbsp;
	    </div>
	  </td>
    <td width="" align="center">
      <div class=maintext>
	      <?php echo  $Plates->MSDate[$i] ;?>&nbsp;
	    </div>
	  </td>
	  <td width="" align="left">
      <div class=maintext>&nbsp; &nbsp; &nbsp;
    <?php if($Plates->MSDate[$i] && $hitType = get_hit_type($Plates->ID[$i],'Plate')){?> 
         <a href="javascript:display_plate('<?php echo  $Plates->ID[$i];?>','<?php echo $hitType;?>');">
         <img border="0" src="./images/icon_plate_check.gif" alt="MS completed"></a>
         <a href="./item_report.php?type=Plate&hitType=normal&item_ID=<?php echo  $Plates->ID[$i];?>&hitType=<?php echo $hitType;?>" class=button>
         <img src="./images/icon_report.gif" border=0 alt="Plate Report">
         </a>     
    <?php }else{?>
          <a href="javascript:display_plate('<?php echo  $Plates->ID[$i];?>','<?php echo $hitType;?>');">
          <img border="0" src="./images/icon_plate.gif" alt="bands in plate"></a>
    <?php }?> 
      </div>
	  </td>
	</tr>
<?php 
  } //end for
?>
   </table>
  </div>  
<?php 
} //end of list plate -------------
?>
</div>
<?php 
if(($theaction == 'showone' or $theaction=='modifyplate')and $Plate_ID){
   echo "<br><b>Notice:</b>
    In order for Prohits to link raw files automatically, 
    please print plate preview to get raw file name formats.<br><br>";
    
   include("plate_info.inc.php");
   $Band = new Band($Band_ID);
	 if($Band->ID){
     $Location = $Band->Location;
     $Bait = new Bait($Band->BaitID); $Bait_ID = $Bait->ID;
     $Exp = new Experiment($Band->ExpID); $Exp_ID = $Exp->ID;
     $Lane = new Lane($Band->LaneID); $Lane_ID = $Lane->ID;
     //$Project = new Projects($Band->ProjectID);
     $Gel = new Gel($Lane->GelID); $Gel_ID = $Gel->ID;
     $BandOwner = get_userName($mainDB, $Band->OwnerID);
     include("plate_band_info.inc.php");
	   include("plate_band_hits.inc.php");
   }
}//end of show one plate ------------
?> 
</form>
<?php 
require("site_footer.php");
?>

