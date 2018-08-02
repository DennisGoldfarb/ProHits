<?php 
/***********************************************************************
    Prohits version 1.00
    Copyright (C) 2001, Mike Tyers, All Rights Reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
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
require("analyst/classes/gel_class.php");
require("analyst/classes/lane_class.php");
require("analyst/classes/band_class.php");
require("analyst/classes/plate_class.php");
require("analyst/classes/plateWell_class.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");

$Log = new Log();
$Band = new Band($Band_ID);

$Bait = new Bait($Band->BaitID); $Bait_ID = $Bait->ID;
$Exp = new Experiment($Band->ExpID); $Exp_ID = $Exp->ID;
$Lane = new Lane($Band->LaneID); $Lane_ID = $Lane->ID;
$Gel = new Gel($Lane->GelID); $Gel_ID = $Gel->ID;
$Plate = new Plate();

$BandOwner = get_userName($PROHITSDB,$Band->OwnerID);

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
  //log table.
  $Desc = "PlateID=$Plate_ID,Band_ID=$Band_ID,WellCode=$frm_WellCode";
  $Log->insert($USER->id,'PlateWell',$PlateWell->ID,'insert',$Desc,$AccessProjectID);
  //end of Log table
  //$Band->is_in_plate($Band_ID);
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
if($theaction == 'updateplate' and $AUTH->Modify and $Plate_ID){
   $Plate->update($Plate_ID,$frm_Name,$frm_PlateNotes,$frm_DigestedBy, $frm_DigestStarted,$frm_DigestCompleted,$frm_Buffer,$frm_MSDate);
   //log table, recordID is 0 since ID can be multiple.
  $Desc = "PlateName=$frm_Name,DigBY=$frm_DigestedBy,DigStart=$frm_DigestStarted,DigComplted=$frm_DigestCompleted,Buffer=$frm_Buffer,MS=$frm_MSDate";
  $Log->insert($USER->id,'Plate',$Plate_ID,'modify',$Desc,$AccessProjectID);
  //end of Log table
}

//require("site_header.php");
$bgcolor = "#dadada";
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<LINK REL="SHORTCUT ICON" HREF="../images/porhits.ico">
<link rel="stylesheet" type="text/css" href="./site_style.css">
<title>Prohits</title>
<script language="Javascript" src="site_javascript.js"></script>
<!--script language="Javascript" src="site_no_right_click.inc.js"></script-->
</head><basefont face="arial">
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5
topMargin=5 rightMargin=5 marginheight="5" marginwidth="5">
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
      </div>
    </td>
  </tr>
  <tr>
    <td align="left"><br>
    &nbsp; <font color="navy" face="helvetica,arial,futura" size="3"><b>Band Location</b> 
    </font> 
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
    <td align="center" colspan=2><font color=red><b><?php echo $error_msg;?></b></font>
   
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
<?php  //include("site_footer.php");
