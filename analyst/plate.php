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

$Plate_ID = '';
$whichPlate = '';
$PlateName_str = '';
$newplate = '';
$error_msg = '';
$msg_plate = '';
$selectedWellCode_arr = '';
$tab = '';

require("../common/site_permission.inc.php");
require("analyst/classes/bait_class.php");
require("analyst/classes/experiment_class.php");
require("common/project_class.php");
require("analyst/classes/gel_class.php");
require("analyst/classes/lane_class.php");
require("analyst/classes/band_class.php");
require("analyst/classes/plate_class.php");
require("analyst/classes/plateWell_class.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");

//get all info about this band
$Log = new Log();
$Band = new Band($Band_ID);
$Bait = new Bait($Band->BaitID); $Bait_ID = $Bait->ID;
$Exp = new Experiment($Band->ExpID); $Exp_ID = $Exp->ID;
$Lane = new Lane($Band->LaneID); $Lane_ID = $Lane->ID;
$Project = new Projects($Band->ProjectID);
$Gel = new Gel($Lane->GelID); $Gel_ID = $Gel->ID;
$Plate = new Plate();

$BandOwner = new User('',$Band->OwnerID);

//if pass a Band_ID but whichplate it check if the band in a plate
if($Band->InPlate){
  //get the plate id 
  $thePlateWell = new PlateWell();
  $in_Plate_ID = $thePlateWell->get_plate_id($Band_ID);
  if(!$Plate_ID and !$whichPlate) $Plate_ID = $in_Plate_ID;
}else{ $in_Plate_ID = 0;}

//---- insert new plate record ----
if($theaction == 'insert' and $AUTH->modify){
  if(!$Plate_ID and $frm_Name){
    //after insert the new plate it will be fetch to $Plate object
    $Plate->insert($frm_Name,$frm_PlateNotes,$USER->id,
                  $frm_DigestedBy,
                  $frm_DigestStarted,
                  $frm_DigestCompleted,
                  $frm_ResuspensionBuff);
    //add record into Log table
    $Desc = "PlateName=$frm_Name";
    $Log->insert($USER->id,'Plate',$Plate->ID,'insert',$Desc,$AccessProjectID);
    //end of Log table
    $Plate_ID = $Plate->ID;    
    $whichPlate = '';
  }
  //insert into PlateWell table
  $PlateWell = new PlateWell();
  $PlateWell->insert($Plate_ID, $Band_ID, $frm_WellCode, $Band->OwnerID, $Band->ProjectID);
 
  $Desc = "PlateID=$Plate_ID,Band_ID=$Band_ID,WellCode=$frm_WellCode";
  $Log->insert($USER->id,'PlateWell',$PlateWell->ID,'insert',$Desc,$AccessProjectID);
}
// ---- end of insert record ----
//remove the band from plate
if($theaction == 'remove' and $AUTH->modify){
  $PlateWell = new PlateWell();
  $error_msg = $PlateWell->remove($Band_ID);
  if(!$error_msg) $in_Plate_ID=0; //refresh band information
  if(!$error_msg){
    //log table, recordID is 0 since ID can be multiple.
    $Desc = "BandID=$Band_ID";
    $Log->insert($USER->id,'PlateWell',0,'delete',$Desc,$AccessProjectID);
    //end of Log table
  }
  //$Band->not_in_plate($Band_ID);
  if($Plate->is_empty_plate($Plate_ID)){
    //the plate is deleted
    $Plate_ID = '';
  }
}
if($theaction == 'updateplate' and $AUTH->modify and $Plate_ID){
   $Plate->update($Plate_ID,$frm_Name,$frm_PlateNotes,$frm_DigestedBy, $frm_DigestStarted,$frm_DigestCompleted,$frm_Buffer,$frm_MSDate);
   //log table, recordID is 0 since ID can be multiple.
  $Desc = "PlateName=$frm_Name,DigBY=$frm_DigestedBy,DigStart=$frm_DigestStarted,DigComplted=$frm_DigestCompleted,Buffer=$frm_Buffer,MS=$frm_MSDate";
  $Log->insert($USER->id,'Plate',$Plate_ID,'modify',$Desc,$AccessProjectID);
  //end of Log table
}

require("site_header.php");
$bgcolor = "#dadada";
?>
<script language="javascript">
 function view_image(Gel_ID){
  file = 'gel_view.php?Gel_ID=' + Gel_ID;
  newwin = window.open(file,"gel_image",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=750,height=600');
  //newwin.moveTo(10,10);
 }
 
 function remove_from_plate(){
   var theForm = document.action_form;
   theForm.theaction.value = 'remove';
   theForm.submit();
 }
</script> 
<?php if($sub){?>
<table cellspacing="1" cellpadding="0" border="0" align=center>
<tr>
    <?php if($Gel_ID){?>
    <td><img src="./images/arrow_green_gel.gif" border=0></td>
    <?php }
    if($Bait_ID){?>
    <td><img src="./images/arrow_green_bait.gif" border=0></td>
    <?php }?>
    <td><img src="./images/arrow_green_exp.gif" border=0></td>
    <td><img src="./images/arrow_green_band.gif" border=0></td>
    <td><img src="./images/arrow_red_well.gif" border=0></td>
</tr>
</table>
<?php }?>
<table border="0" cellpadding="0" cellspacing="0" width="95%">
 <tr>
  	<td colspan=2><div class=maintext>
      <img src="images/icon_but.gif"  height=15> Available Well 
      <img src="images/icon_curr_band.gif"  height=15> Current Band
      <img src="images/icon_first.gif" height=15> First Plate
      <img src="images/icon_previous.gif" height=15> Previous Plate
      <img src="images/icon_next.gif"  height=15> Next Plate
      <img src="images/icon_last.gif"  height=15> Last Plate
      </div>
    </td>
  </tr>
  <tr>
    <td align="left"><br>
    &nbsp; <font color="navy" face="helvetica,arial,futura" size="3"><b>Band (Add Band to Plate)</b> 
    </font> 
  </td>
    <td align="right"><br>
      <a href="band_show.php?theaction=viewall<?php echo ($sub)?"&sub=1&Gel_ID=$Gel_ID&Bait_ID=$Bait_ID&Exp_ID=$Exp_ID&Lane_ID=$Lane_ID":"";?>" class=button>
    [All Submitted Bands]</a>&nbsp;
      <a href="band_show.php?theaction=new<?php echo ($sub)?"&sub=1&Gel_ID=$Gel_ID&Bait_ID=$Bait_ID&Exp_ID=$Exp_ID&Lane_ID=$Lane_ID":"";?>" class=button>
    [New Submitted Bands]</a>&nbsp;
    </td>
  </tr>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr> 
   <form name=action_form action=<?php echo $PHP_SELF;?> method=post>
   <input type=hidden name=Band_ID value=<?php echo $Band_ID;?>>
   <input type=hidden name=Plate_ID value=<?php echo $Plate_ID;?>>
   <input type=hidden name=theaction value=''>
   <input type=hidden name=whichPlate value=''>
   <input type=hidden name=frm_WellCode value=''>
   <input type=hidden name=sub value=<?php echo $sub;?>> 
    <td align="center" colspan=2><font color=red><b><?php echo $error_msg;?></b></font><br>
   
    <!-- display this plate information---->
    <?php include("plate_info.inc.php");?>
	   <!-- display this band information----->
    <?php include("plate_band_info.inc.php");?>
		
       </td>
      </form>
      </tr>
     </table><br> 
    </td>
  </tr>
</table>
<?php  include("site_footer.php");
