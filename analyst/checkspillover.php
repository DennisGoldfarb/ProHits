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

$spillover = '';
require("../common/site_permission.inc.php");

require("analyst/classes/gel_class.php"); 
require("analyst/classes/bait_class.php");
require("analyst/classes/lane_class.php");
require("analyst/classes/hits_class.php");
require("analyst/classes/plate_class.php");
require("analyst/classes/band_class.php");
require("analyst/classes/hitNote_class.php");

require("common/page_counter_class.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
define ("NEARBY", 3);

//require("site_header.php");
$Log = new Log();
$HitNote = new HitNote();
$Hits = new Hits();
$Band = new Band();
$Plate = new Plate();
$Lane = new Lane();
$Gel = new Gel($Gel_ID);
$Gel_Lanes = new Lane();
$Gel_Lanes->get_gel_lanes($Gel_ID);
//print_r($Gel_Lanes);

$bgcolor = "#e9e1c9";
$bgcolordark = "#c5b781";
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="./site_style.css"> 
<title>Prohits</title>
<script language='javascript'>
function view_image(Gel_ID){
  file = 'gel_view.php?Gel_ID=' + Gel_ID;
  nw = window.open(file,"gel_image",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=750,height=600');
  nw.moveTo(400,400);
}
</script>
</head><basefont face="arial">
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff>
<table border="0" cellpadding="0" cellspacing="0" width="95%" align=center>
  <tr>
  <td align="left">
    &nbsp; <font color="#006699" face="helvetica,arial,futura" size="3"><b>Sample Spill Over</b> 
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
    <td align="center" colspan=2><br>
     <table width=100% border=0>
         <tr>
          <tr>
          <td><div class=maintext><b>Gel ID:</b></div></td>
          <td><div class=maintext><?php echo $Gel_ID;?></div></td>
        </tr>
          <td><div class=maintext><b>Gel Name:</b></div></td>
          <td><div class=maintext><?php echo $Gel->Name;?></div></td>
        <tr>
          <td><div class=maintext><b>Uploaded By:</b></div></td>
          <td><div class=maintext><?php  echo get_userName($mainDB, $Gel->OwnerID);?></div></td>
        </tr>
        <tr>
          <td><div class=maintext><b>Uploaded On:</b></div></td>
          <td><div class=maintext><?php echo $Gel->DateTime;?></div></td>
        </tr>
        <tr>
          <td><div class=maintext><b>Gel Image:</b></div></td>
          <td><div class=maintext><a href="javascript: view_image(<?php echo $Gel_ID;?>);"><?php echo $Gel->Image;?></a></div></td>
        </tr>
  <?php 
  $lane_str = '';
     $tmp_Gel = new Gel();
    //it will return num of lanes in diff plates
    if($tmp_Gel->get_plate_ids($Gel_ID)){
      $tmp_Plate = new Plate();
    ?>
      <tr bgcolor="<?php echo $bgcolordark;?>">
        <td align="center"><div class=tableheader height=18>Gel Lane</td>
        <td align="center"><div class=tableheader height=18>In Plate</td>
      </tr><?php  
      for($i = 0; $i<$tmp_Gel->count;$i++){ 
        $lane_str .= $tmp_Gel->Lane_num[$i].", ";
        if($tmp_Gel->Plate_ID[$i] != $tmp_Gel->Plate_ID[$i+1]){ //if its the end of the plate id
          $tmp_Plate->fetch($tmp_Gel->Plate_ID[$i]);
    ?><tr bgcolor="<?php echo $bgcolordark;?>">
          <td><font color=white face=Arial size=2>&nbsp; <?php  echo $lane_str;?></font></td>
          <td><font color=white face=Arial size=2>&nbsp; <?php  
              echo "Plate ID:<b> $tmp_Plate->ID </b> Plate Name: $tmp_Plate->Name ";
              if($tmp_Plate->MSDate){
                echo "<img src='./images/icon_plate_check.gif' border=0>";
              }else{
                echo "<img src='./images/icon_plate.gif' border=0>";
              }?></font></td>
        </tr>
       <?php  $lane_str ='';
        }//end if
       }//end for 
    }//end if ?>
    </table>
<?php 
for($i=0; $i < $Gel_Lanes->count; $i++){
  $curr_laneNum = $Gel_Lanes->LaneNum[$i];
  $curr_laneID = $Gel_Lanes->ID[$i];
  $curr_laneBaitLocusTag = $Gel_Lanes->BaitLocusTag[$i];
  //get lane string 1,2,3,5
  $laneNum_str = "0";
  
  for($k=0; $k < $Gel_Lanes->count; $k++){
    if(($Gel_Lanes->LaneNum[$k] >= ($curr_laneNum - NEARBY)) 
        and ($Gel_Lanes->LaneNum[$k] <= ($curr_laneNum + NEARBY)) and $curr_laneNum !=$Gel_Lanes->LaneNum[$k]){
      $laneNum_str .=",".$Gel_Lanes->ID[$k];
    }
  }//end for
  //  echo $laneNum_str;exit;
  if(strstr($laneNum_str,",")){
    if($curr_laneBaitLocusTag && $laneNum_str){
      $Hits->get_spillover_hits($curr_laneBaitLocusTag,$laneNum_str); 
      if($Hits->count){
        $spillover = 'Yes';
        //found spillover and create report.
        echo "<table border=0 width=100%>
              <tr>
                <td colspan=3><div class=maintext><font color=red>Bait <b>$curr_laneBaitLocusTag</b> in Lane <b>$curr_laneNum</b> spill over to :</font></div></td>
              <tr bgcolor='$bgcolordark'>
                <td><div class=tableheader>Lane Number</div></td>
                <td><div class=tableheader>Band Code</div></td>
                <td colspan=3 align=center><div class=tableheader>In Plate</div></td>
              </tr>";
        for($j=0; $j<$Hits->count;$j++){
          //if the band is bait, do not check the band,since two same bait lanes can be in same gel.
          //if a band in well is bait do not check carryover, Since tow well bands cut from same gel lane.
          $tmpBait = new Bait($Hits->BaitID[$j]);
          if( $Hits->LocusTag[$j] != $tmpBait->LocusTag ){
            $Band->fetch($Hits->BandID[$j]);
            $Plate->fetch($Hits->PlateID[$j]);
            $Lane->fetch($Hits->LaneID[$j]);
            echo "<tr bgcolor='$bgcolor'>
                    <td align=center><div class=maintext>".$Lane->LaneNum."</div></td>
                    <td align=center><div class=maintext>".$Hits->BandLocation[$j]."</div></td>
                    <td align=center><div class=maintext> Plate ID: ".$Hits->PlateID[$j]. "</div></td>
                    <td  align=center><div class=maintext>Plate Name: ".$Plate->Name."</div></td>
                    <td align=center><div class=maintext>Well Code: ".$Hits->WellCode[$j]."</div></td>
                  </tr>";
            
            $HitNote->insert($Hits->ID[$j],2,'auto detected', 0);
          }//end if
        }//end for
        echo "</table>";
      }
    }  
  }
}//end for
if(!$spillover) echo "<div class=maintext><b>No spillover found in the gel</b></div>";
?>
  <td>
</tr>
</table>
<form>
 <center><input type=button value='Close Window' onClick="javascript: window.close();"></center>
</form>
</body>
</html>