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

$Band_ID = 0;
$newplate = '';
$tab = '';

require("../common/site_permission.inc.php");
require("analyst/classes/bait_class.php");
require("analyst/classes/gel_class.php");
require("analyst/classes/lane_class.php");
require("analyst/classes/band_class.php");
require("analyst/classes/plate_class.php");
require("analyst/classes/plateWell_class.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");

include("analyst/site_simple_header.php");

$Band = new Band($Band_ID);
$Bait = new Bait($Band->BaitID);
$Lane = new Lane($Band->LaneID);
$Gel = new Gel($Lane->GelID);
$Plate = new Plate($Plate_ID);
$BandOwner = get_userName($mainDB, $Band->OwnerID);

?>
<script language='javascript'>
 function view_image(Gel_ID){
  file = 'gel_view.php?Gel_ID=' + Gel_ID;
  newwin = window.open(file,"gel_image",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=750,height=600');
  newwin.moveTo(10,10);
 }
</script>
  <center>
  <table border="0" cellpadding="0" cellspacing="0" width="750">
      <tr>
        <td height=20 align=center>&nbsp;<b><font face="Arial" size="3">Plate Information</font></b>
       <hr>
       </td>
      </tr>
      <tr>
        <td>
          <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%" bgcolor="white">
            <tr>
              <td align="center" valign="top" width="300">
                <table border="0" cellpadding="0" cellspacing="1" width=100%>
                  <tr>
                    <td nowrap width=30%><div class=maintext>&nbsp;<b>Plate ID</b>:</div></td>
                    <td width=70%><div class=maintext>&nbsp;<?php echo ($Plate_ID)?$Plate->ID:"<font color=green>New Plate</font>";?></div></td>
                  </tr>
                  <tr>
                    <td nowrap width=45%><div class=maintext>&nbsp;<b>Plate Name</b>:</div></td>
                    <td width=45%><div class=maintext>&nbsp;<?php echo ($Plate_ID)?$Plate->Name:"<input type=text name=frm_Name size=15>";?></div></td>
                  </tr>
                  <tr>
                    <td><div class=maintext>&nbsp;<b>Created By</b>:</div></td>
                    <td><div class=maintext>
                    &nbsp;<?php 
                      if($Plate_ID){
                        $PlateOwner = get_userName($mainDB, $Plate->OwnerID);
                        echo $PlateOwner;
                      }else{
                        //new plate Owner is current user
                        echo $USER->Fname. " ". $USER->Lname;
                      }
                     ?>
                    </div></td>
                  </tr>
                  <tr>
                    <td><div class=maintext>&nbsp;<b>Created On</b>:</div></td>
                    <td><div class=maintext>
                     <?php echo $Plate->DateTime;?></div></td>
                  </tr>
                   <tr>
                    <td><div class=maintext>&nbsp;<b>Project</b>:</div></td>
                    <td><div class=maintext>
                     <?php echo $AccessProjectID . " ($AccessProjectName)";?></div></td>
                  </tr>
                  
                  <tr>
                    <td colspan="2"><div class=maintext>&nbsp;<b>Plate Notes</b>:</div></td>
                  </tr>
                  <tr>
                    <td colspan="2"><div class=maintext>&nbsp;<?php echo htmlspecialchars($Plate->PlateNotes);?></div></td>
                  </tr>
                  <tr>
                    <td colspan="2"><div class=maintext>&nbsp;<b>Raw file folder Name</b>:</div></td>
                  </tr>
                  <tr>
                    <td colspan="2"><br><font color=red><b><?php echo str_replace('-','', substr($Plate->DateTime,0, 10)). "_" . $Plate->Name . "_A" . $Plate->ID . "_P" . $_SESSION["workingProjectID"];?></b></font></td>
                  </tr>
                </table>
              </td>
              <td width="550">
                  <?php 
                  /***********************************************************************
                    get this plate wells
                  ************************************************************************/
                  $A2H_array = array("A","B","C","D","E","F","G","H");
                  if($Plate_ID and !$newplate){
                    //get all wells in this plate
                    $thePlateWells = new PlateWell();
                    $thePlateWells->fetchall_this_plate($Plate_ID);
                    //put records in well array. e.g. $wll_array["1A"] = 3 (Band_ID);
                    //Wellcode value are A01 to A12, B01 to B12, C01 to 12C ...
                    for($i=0; $i< $thePlateWells->count; $i++){
                      if(strlen($thePlateWells->WellCode[$i])!=3){
                        $tmpCode = $thePlateWells->WellCode[$i];
                        $thePlateWells->WellCode[$i] = $tmpCode{0} . "0" . $tmpCode{1};
                      }
                      $well_array[$thePlateWells->WellCode[$i]] = $thePlateWells->BandID[$i];
                      //$group_array[$thePlateWells->WellCode[$i]] = $thePlateWells->GroupID[$i];
                    }
                  }
                  ?>
                 <table bgcolor="#797979" border="0" cellpadding="0" cellspacing="0" width="100%">
                  <tr>
                    <td align="center" bgcolor=white><div class=maintext><b>&nbsp;Plate Layout</b></div></td>
                  </tr>
                  <tr>
                    <td align="center" valign="top">
                      <TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 width=100%>
                        <TR>
                          <TD>
                            <IMG SRC="images/conner1_plate.jpg" WIDTH=23 HEIGHT=20></TD>
                          <td background="images/top_plate.jpg">&nbsp;</TD>
                          <TD>
                            <IMG SRC="images/conner2_plate.jpg" WIDTH=23 HEIGHT=20></TD>
                        </TR>
                        <TR>
                          <TD background="images/left_plate.jpg">&nbsp;</TD>
                          <TD>
                            <table border="0" cellpadding="0" cellspacing="1">
                                <tr height="18" bgcolor="#797979">
                                <td height="18" width=30 align=center><div class=tableheader>&nbsp;</div></td>
                                <td height="18" width=45 align=center><div class=tableheader>1</div></td>
                                <td height="18" width=45 align=center><div class=tableheader>2</div></td> 
                                <td height="18" width=45 align=center><div class=tableheader>3</div></td> 
                                <td height="18" width=45 align=center><div class=tableheader>4</div></td> 
                                <td height="18" width=45 align=center><div class=tableheader>5</div></td> 
                                <td height="18" width=45 align=center><div class=tableheader>6</div></td> 
                                <td height="18" width=45 align=center><div class=tableheader>7</div></td> 
                                <td height="18" width=45 align=center><div class=tableheader>8</div></td> 
                                <td height="18" width=45 align=center><div class=tableheader>9</div></td> 
                                <td height="18" width=45 align=center><div class=tableheader>10</div></td> 
                                <td height="18" width=45 align=center><div class=tableheader>11</div></td> 
                                <td height="18" width=45 align=center><div class=tableheader>12</div></td> 
                                </tr>
                                <?php  
                                //create all rows of a plate layout 
                                //first for A->H second for 1->12
                                $tabs = "\n                             ";               
                                for($row=0; $row < count($A2H_array); $row++){
                                   echo $tabs;
                                   echo "<tr height='18' bgcolor=white>";
                                   echo $tabs;
                                   echo "<td height='18' align=center bgcolor='#797979'><div class=tableheader>".$A2H_array[$row]."</div></td>";
                                   for($col=1; $col <= 12; $col++){
                                     if($col < 10) $col = "0".$col;
                                     $theKey = $A2H_array[$row].$col;
                                     //echo $well_array[4A];exit;
                                     if(isset($well_array[$theKey])){
                                         echo "<td  height='18' align=center><div class=maintext>";
                                         echo "$well_array[$theKey]</div></td>"; 
                                     }else{
                                        //available well
                                        echo $tabs;
                                        echo "<td bgcolor='#bebebe' height='18'><div class=maintext>&nbsp;</div></td>\n";
                                     }
                                   }
                                    
                                   echo $tab."</tr>";
                                }
                                ?>
                                 
                              </table>
              
                          </TD>
                          <td background="images/right_plate.jpg">&nbsp;</TD>
                        </TR>
                        <TR>
                          <TD>
                            <IMG SRC="images/conner3_plate.jpg" WIDTH=23 HEIGHT=21></TD>
                             
                          <td background="images/bottom_plate.jpg">&nbsp;</td>
                          <TD>
                            <IMG SRC="images/conner4_plate.jpg" WIDTH=23 HEIGHT=21></TD>
                        </TR>
                      </TABLE>
                    </td>
                  </tr>
                </table><br>
                <?php //----------------------end of plate layout ----------------?>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <?php 
    $Bands = new Band();
    $Bands->fetchall_list_one_plate($Plate_ID);
    ?>
    <b><font face="Arial" size="3">Bands In This Plate</font></b>
    <table border="0" cellpadding="0" cellspacing="1" width=750>
      <tr bgcolor=#475598 height=25>
       <td align=center onwrap><div class=tableheader>Raw file Name</div></td>
       <td align=center onwrap><div class=tableheader>User Name</div></td>
       <td align=center onwrap><div class=tableheader>Well ID</div></td> 
       <td align=center onwrap><div class=tableheader>Gel Image</div></td> 
       <td align=center onwrap><div class=tableheader>Band Code</div></td> 
       <td align=center onwrap><div class=tableheader>Observed MW</div></td> 
       <td align=center onwrap><div class=tableheader>Species</div></td> 
       <td align=center onwrap><div class=tableheader>Gel Line</div></td> 
       <td align=center onwrap><div class=tableheader>Modification</div></td>
      </tr>
    <?php 
     $theGel = new Gel();
     for($i = 0; $i < $Bands->count; $i++){
      $theUser = get_userName($mainDB, $Bands->OwnerID[$i]); 
      $theGel->fetch($Bands->LaneGelID[$i]);
      if($theGel->Image){
       $url = "<a href=\"javascript:view_image('".$theGel->ID."');\">[".$theGel->Image."]</a>";
      }else{
        $url = "&nbsp;";
      }
      ?>
      <tr bgcolor=<?php echo (($i%2) != 0 )?'#bccae2':'#ffffff';?>>
       <td align=center onwrap><div class=maintext><font color=red><?php echo $Bands->WellCode[$i]."_".$Bands->ID[$i];?></font></div></td>
       <td align=center onwrap><div class=maintext><?php echo $theUser;?></div></td> 
       <td align=center onwrap><div class=maintext><?php echo $Bands->WellID[$i];?></div></td>
       <td align=center onwrap><div class=maintext><?php echo $url;?></div></td> 
       <td align=center onwrap><div class=maintext><?php echo $Bands->Location[$i];?></div></td>
       <td align=center onwrap><div class=maintext><?php echo $Bands->BandMW[$i];?></div></td> 
       <td align=center onwrap><div class=maintext><?php echo $Bands->LaneSpecies[$i];?></div></td> 
       <td align=center onwrap><div class=maintext><?php echo $Bands->LaneNum[$i];?></div></td> 
       <td align=center onwrap><div class=maintext><?php echo ($Bands->Modification[$i])?$Bands->Modification[$i]:"&nbsp;";?></div></td>
        
      </tr>
     <?php }?>
    </table><br>
    </center>
  <?php include("site_simple_footer.php");?>