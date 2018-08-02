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
$gelFree = 0;
$hitType = 'normal';
$item_hits_order_by = '';

$hitType = 'normal';
$searchEngineField = '';
$Type = 'Sample';


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

//-------------------------------------------------------------------------------------------------------------
$SearchEngineConfig_arr = get_project_SearchEngine();
//-------------------------------------------------------------------------------------------------------------

$Log = new Log();
 
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

 
//require("site_header.php");
//$TB_CELL_COLOR = "#dadada";
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<LINK REL="SHORTCUT ICON" HREF="../images/porhits.ico">
<link rel="stylesheet" type="text/css" href="./site_style.css">
<title>Prohits</title>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
</head><basefont face="arial">
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5
topMargin=5 rightMargin=5 marginheight="5" marginwidth="5">

<script language='javascript'>
function view_master_results(ResultFile,searchEngineField){
    if(ResultFile == ''){
      alert("No results file exists.");
      return false;
    }
    if(searchEngineField == "Mascot"){  
		  var mascot_IP = '<?php echo MASCOT_IP?>';
      <?php if(defined('MASCOT_IP_OLD')){?>
        var mascot_IP_old = '<?php echo MASCOT_IP_OLD?>';
        if(ResultFile.search(/\w/) != -1){
          mascot_IP = mascot_IP_old;
        }
      <?php }?>  
		  <?php if(MASCOT_USER){?>
			var tmp_url = "http://"+mascot_IP+"<?php echo MASCOT_CGI_DIR;?>/login.pl";
			tmp_url += "?action=login&username=<?php echo MASCOT_USER;?>&password=<?php echo MASCOT_PASSWD;?>";
			tmp_url += "&display=nothing&savecookie=1&referer=master_results.pl?file=" + ResultFile;
 			<?php }else{?>
			var tmp_url = "http://"+mascot_IP+"<?php echo MASCOT_CGI_DIR;?>/master_results.pl?file=" + ResultFile;
 			<?php }?>
 			window.open(tmp_url,"mascot_win", "toolbar=1,menubar=1,scrollbars=1,resizable=1,width=800,height=800");
    }else if(searchEngineField == "GPM"){  
      var file = "http://<?php echo $gpm_ip;?>/thegpm-cgi/plist.pl?path=" + ResultFile;
			window.open(file,"gpm_win", "toolbar=1,menubar=1,scrollbars=1,resizable=1,width=800,height=800"); 
    }else{
      return;
    }
  }
 function display_plate(Plate_ID){
  var theForm = document.action_form;
  theForm.theaction.value = 'showone';
  theForm.Plate_ID.value = Plate_ID;
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
</script>
<?php 
//---------------------------------------------------------------------------------------------
if($sub){?>
<table cellspacing="0" cellpadding="0" border="0" align=center>
<tr>
    <td><img src="./images/arrow_green_gel.gif" border=0></td>
    <td><img src="./images/arrow_green_bait.gif" border=0></td>
    <td><img src="./images/arrow_green_exp.gif" border=0></td>
    <td><img src="./images/arrow_green_band.gif" border=0></td>
    <td><img src="./images/arrow_red_well.gif" border=0></td>
</tr>
</table>
<?php }?>
   <form name=action_form action=<?php echo $PHP_SELF;?> method=post>
   <input type=hidden name=Band_ID value=<?php echo $Band_ID;?>>
   <input type=hidden name=Plate_ID value='<?php echo $Plate_ID;?>'>
   <input type=hidden name=theaction value=''>
   <input type=hidden name=whichPlate value=''>
   <input type=hidden name=frm_WellCode value=''>
   <input type=hidden name=sub value=<?php echo $sub;?>>
   <input type=hidden name=noplate value=<?php echo $noplate;?>>
   <input type=hidden name=hitType value=<?php echo $hitType;?>> 
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<?php if(!$noplate){?>
 <tr>
  	<td colspan=2><div class=maintext>
      <img src="images/icon_but.gif"  height=15> Available Well 
      <img src="images/icon_curr_band.gif"  height=15> Current Band
      <img src="images/icon_plate.gif"  height=17> Plate
      <img src="images/icon_plate_check.gif"  height=17> MS Completed Plate
      <img src="images/icon_report.gif"  height=17> Plate Report
      </div><br>
    </td>
  </tr>
  <tr>
    <td align="left">
		&nbsp; <font color="navy" face="helvetica,arial,futura" size="3"><b>Plates & Wells
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
<?php }else{?>	
  <tr>
    <td align="left">
		&nbsp; <font color="navy" face="helvetica,arial,futura" size="3"><b>Band
    <?php 
      if($AccessProjectName){
        echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project: $AccessProjectName)</font>";
      }
    ?>
    </b> 
		</font> 
	  </td>
    <td align="right">     
     <a href="javascript: window.close();" class=button>[Close Window]</a>      
    </td>
  </tr>
<?php }?>  
  <tr>
  	<td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="center" colspan=2>
<?php 
if(!$gelFree){
if(!$theaction){//list plate ---------------------
  $Plates = new Plate();
  $total = $Plates->get_total();
  if(!$order_by) $order_by = "P.ID desc";
  if(!$start_point) $start_point = 0;
  $Plates->fetchall_list($order_by,$start_point, RESULTS_PER_PAGE);
   
  //page counter start here
  $PAGE_COUNTER = new PageCounter();
  $query_string = "";
  $caption = "Plates";
  if($order_by){
    $query_string .= "&order_by=".$order_by;
  }
  $page_output = $PAGE_COUNTER->page_links($start_point, $total, RESULTS_PER_PAGE, MAX_PAGES,$query_string); 
?> 
  <table border="1" cellpadding="0" cellspacing="1" width="100%">
  <tr><td colspan=6 align=right><?php echo $page_output;?></td></tr>
	<tr bgcolor="">
	  <td width="60" height="25" bgcolor="<?php echo $bg_tb_header;?>" align=center>
	    <a href="<?php echo $PHP_SELF;?>?theaction=<?php echo $theaction;?><?php echo ($sub)?"&sub=$sub":"";?>">
	    <div class=tableheader>
		ID</div></a>
	  </td>
	  <td width="140" height="25" bgcolor="<?php echo $bg_tb_header;?>" align=center>
	    <a href="<?php echo $PHP_SELF;?>?theaction=<?php echo $theaction;?>&order_by=P.Name<?php echo ($sub)?"&sub=$sub":"";?>">
        <div class=tableheader>
		Plate Name</div></a>
	  </td>
	  <td width="150" bgcolor="<?php echo $bg_tb_header;?>" align=center><div class=tableheader>
		Created By</div>
	  </td>
	  <td width="150" bgcolor="<?php echo $bg_tb_header;?>" align="center" align=center>
	    <a href="<?php echo $PHP_SELF;?>?theaction=<?php echo $theaction;?>&order_by=P.DateTime<?php echo ($sub)?"&sub=$sub":"";?>">
	    <div class=tableheader>
		Created On</div></a>
	  </td>
    <td width="100" bgcolor="<?php echo $bg_tb_header;?>" align="center" align=center>
	    <a href="<?php echo $PHP_SELF;?>?theaction=<?php echo $theaction;?>&order_by=P.MSDate<?php echo ($sub)?"&sub=$sub":"";?>">
	    <div class=tableheader>
		 MS Completed</div></a>
	  </td>
	  </td>
	  <td width="70" bgcolor="<?php echo $bg_tb_header;?>" align="center">
	    <div class=tableheader>Options</div>
	  </td>
	</tr>
<?php 
//print_r($_SESSION['USER']->UsersArr);exit;
//echo $Plates->count;exit;
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
	  <td width="" align="left"><div class=maintext>&nbsp; &nbsp;
	      <?php echo $Plates->ID[$i];?>&nbsp;
	    </div>
	  </td>
	  <td width="" align="center"><div class=maintext>
	      <?php echo $Plates->Name[$i];?>&nbsp;
	    </div>
	  </td>
	  <td width="" align="center"><div class=maintext>
	      <?php echo $theOwner;?>&nbsp;
	      </div>
	  </td>
	  <td width="" align="center"><div class=maintext>
	      <?php echo  $Plates->DateTime[$i] ;?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo  $Plates->MSDate[$i] ;?>&nbsp;
	      </div>
	  </td>
	  <td width="" align="left"><div class=maintext>&nbsp; &nbsp; &nbsp;
    <a href="javascript:display_plate('<?php echo $Plates->ID[$i];?>');">
    <?php if($Plates->MSDate[$i]){?> 
       <img border="0" src="./images/icon_plate_check.gif" alt="MS completed"></a>
       <a href="./plate_report.php?Plate_ID=<?php echo $Plates->ID[$i];?>" class=button><img src="./images/icon_report.gif" border=0 alt="Plate Report"></a>
    <?php }else{?>
       <img border="0" src="./images/icon_plate.gif" alt="bands in plate"></a>
    <?php }?>   
    
    </div>
	  </td>
	</tr>
  
<?php 
} //end for
?>
   </table>
<?php 
} //end of list plate -------------
}
if(($theaction == 'showone' or $theaction=='modifyplate')){
  //get all info about this band
    //display one plate only
    echo "<br>";
   if(!$gelFree){ 
    include("plate_info.inc.php");    
   } 
   $Band = new Band($Band_ID);
	 if($Band->ID){
     $Location = $Band->Location; 
     $Bait = new Bait($Band->BaitID); $Bait_ID = $Bait->ID;
     $Exp = new Experiment('',$Band->ExpID); $Exp_ID = $Exp->ID;
     $Lane = new Lane($Band->LaneID); $Lane_ID = $Lane->ID;
     //$Project = new Projects($Band->ProjectID);
     $Gel = new Gel($Lane->GelID); $Gel_ID = $Gel->ID;
     $BandOwner = get_userName($mainDB, $Band->OwnerID);
     include("plate_band_info.inc.php");
?> 
  <!--table border="0" cellpadding="0" cellspacing="1" width=740"><tr><td align="right">&nbsp;<br>          
     <a href="javascript:show_all_peptides('<?php echo $Band->ID;?>');">Sample Report (peptide detail)</a>
  </td></tr></table--> 
<?php           
	   include("plate_band_hits.inc.php");
   }
}//end of show one plate ------------
?>
    </td>
  </tr>
</table>
</form>
</body>
</html>
