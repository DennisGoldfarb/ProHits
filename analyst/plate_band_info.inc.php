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

?>
<table cellspacing="1" cellpadding="0" border="0" width=740>
  <tr align=centerd>
      <td colspan=4><div class=maintext><font size="4">&nbsp;</font></div></td>
  </tr>
  <tr>
      <td colspan="4" bgcolor="<?php echo $TB_HD_COLOR;?>" height="20">
      <div class=tableheader>&nbsp;<b>Band(Sample) Information ( <?php echo $Band_ID;?> )</b></div></td>
  </tr>
<?php if(!$Gel->ID){?>
  <tr bgcolor="<?php echo $TB_CELL_GRAY;?>" align=center>
      <td colspan=4><div class=maintext><font size="5" color="#ffffff">Gel Free</font></div></td>
  </tr>
<?php }else{?>
  <tr bgcolor="<?php echo $TB_CELL_GRAY;?>">
      <td><div class=maintext>&nbsp;<b>Gel Image:</b></div></td>
      <td><div class=maintext>&nbsp;
    <?php 
    if($Gel->Image){
      echo "[<a href=\"javascript: view_image('" . $Gel->ID. "');\">";
      echo $Gel->Image;
      echo "</a>]";
    } 
    ?>
    </div></td>
      <td><div class=maintext>&nbsp;<b>Lane Number:</b></div></td>
      <td><div class=maintext>&nbsp;<?php echo $Lane->LaneNum;?></div></td>
  </tr>
  <tr bgcolor="<?php echo $TB_CELL_GRAY;?>">
      <td width=><div class=maintext>&nbsp;<b>Gel Name:</div></td>
      <td width=><div class=maintext>&nbsp;<?php echo $Gel->Name;?></div></td>
      <td><div class=maintext>&nbsp;<b>Lane Code:</b></div></td>
      <td><div class=maintext>&nbsp;<font color=red><?php echo $Lane->LaneCode;?></font></div></td>
  </tr>
<?php }?>
  <tr bgcolor="<?php echo $TB_CELL_GRAY;?>">
      <td width=25%><div class=maintext>&nbsp;<b>Bait LocusTag:</td>
      <td width=25%><div class=maintext>&nbsp;<?php echo ($Bait->LocusTag)?$Bait->LocusTag:"";?></td>
	    <td><div class=maintext>&nbsp;<b>Sample Name:</b></div></td>
     <td><div class=maintext>&nbsp;<font color=red><?php echo $Band->Location;?></font></div></td> 
  </tr>
  <tr bgcolor="<?php echo $TB_CELL_GRAY;?>">
      <td width=25%><div class=maintext>&nbsp;<b>Bait Gene Name:</td>
      <td width=25><div class=maintext>&nbsp;<?php echo $Bait->GeneName;?></td>
      <td><div class=maintext>&nbsp;<b>Band Observed MW:</b></div></td>
      <td><div class=maintext>&nbsp;<?php echo ($Band->BandMW==0.000 || !$Band->BandMW)?'':$Band->BandMW.'kDa';?></div></td>
  </tr>
  <tr bgcolor="<?php echo $TB_CELL_GRAY;?>">
      <td><div class=maintext>&nbsp;<b>Experiment:</b></div></td>
      <td><div class=maintext>&nbsp;<?php echo $Exp->Name;?></div></td>
      <td><div class=maintext>&nbsp;<b>Submitted by:</div></td>
      <td><div class=maintext>&nbsp;<?php echo $BandOwner;?></div></td>
  </tr>
  <tr bgcolor="<?php echo $TB_CELL_GRAY;?>">
      <td><div class=maintext>&nbsp;<b>Genus Species(Exp.):</b></div></td>
      <td><div class=maintext>&nbsp;<?php echo get_TaxID_name($mainDB, $Exp->TaxID, $HITS_DB["prohits"]);?></div></td>
	   <td><div class=maintext>&nbsp;<b>Submitted Date:</div></td>
     <td><div class=maintext>&nbsp;<?php echo $Band->DateTime;?></div></td>
  </tr>
<?php if($Gel->ID){
  $SQL = "SELECT WellCode FROM PlateWell WHERE BandID='".$Band->ID."'";
  $WellArr = $mainDB->fetch($SQL);
?>  
  <tr bgcolor="<?php echo $TB_CELL_GRAY;?>">
      <td><div class=maintext>&nbsp;<b>Well Code:</b></div></td>
      <td><div class=maintext>&nbsp;<?php echo $WellArr['WellCode'];?></div></td>
	   <td><div class=maintext>&nbsp;</div></td>
     <td><div class=maintext>&nbsp;</div></td>
  </tr>
<?php }?> 
</table>  
