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

$Gel_ID = '';

require("../common/site_permission.inc.php");
require("analyst/classes/gel_class.php");
require("analyst/classes/plate_class.php");
require("analyst/classes/lane_class.php");
require("analyst/classes/bait_class.php");
require("analyst/classes/experiment_class.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");

require("analyst/site_header.php");

//if( !$Exp_ID) header ("Location: noaccess.html");
$Exp = new Experiment($HITSDB->link, $Exp_ID);
 
$Lanes = new Lane();
//get lane list of the experiment
$Lanes->fetchall($Exp_ID,'');
if($Lanes->count == 0 ){
  echo 
  "<script language='javascript'>
    window.location='./band.php?theaction=viewband&Gel_ID=$Gel_ID&sub=$sub&Exp_ID=$Exp_ID';
  </script>";
  exit;
}

$Bait_ID = $Exp->BaitID;
if($Exp->BaitID){
   $Bait = new Bait($Exp->BaitID);
}

$ExpOwner = get_userName($mainDB, $Exp->OwnerID); 

?>
<script language="javascript">
function back_to_view(theForm){
   theForm.theaction.value = "viewband";
   theForm.submit();
}
function view_image(Gel_ID){  
  file = 'gel_view.php?Gel_ID=' + Gel_ID;
  newwin = window.open(file,"gel_image",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=750,height=600');
  newwin.moveTo(10,10);
}
</script>
<?php if($sub){?>
<table cellspacing="1" cellpadding="0" border="0" align=center>
<tr>
    <td><a href='./gel.php?sub=<?php echo $sub;?>'><img src="./images/arrow_green_gel.gif" border=0></a></td>    
    <td><a href='./bait.php?sub=<?php echo "$sub&Gel_ID=$Gel_ID";?>'><img src="./images/arrow_green_bait.gif" border=0></td>
    <td><a href='./experiment.php?sub=<?php echo "$sub&Gel_ID=$Gel_ID&Bait_ID=$Bait_ID";?>'><img src="./images/arrow_green_exp.gif" border=0></td>   
    <td><img src="./images/arrow_red_band.gif" border=0></td>   
    <td><img src="./images/arrow_green_well.gif" border=0></td>
</tr>
</table>
<?php }?>
<table border="0" cellpadding="0" cellspacing="0" width="90%" >
  <tr>
  	<td colspan=2><div class=maintext>
      <img src="images/icon_purge.gif"> Delete 
      <img src="images/icon_tree.gif"> Next Level
      <img src="images/icon_view.gif"> Modify 
      <img src="images/arrow_small.gif"> Next
      </div>
    </td>
  </tr>
  <tr>
    <td align="left">
    &nbsp; <font color="#006699" face="helvetica,arial,futura" size="3"><b>Gel Lane
    <?php 
    if($AccessProjectName){
      echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font>";
    }
    ?>
    </b> 
    </font> 
  </td>
    <td align="right">
<?php if($AUTH->Insert){?>
     <a href="band.php?theaction=addnew<?php echo "&sub=$sub&Gel_ID=$Gel_ID&Bait_ID=$Bait_ID&Exp_ID=$Exp_ID";?>" class=button>[Add New]</a>&nbsp;
<?php }?>
      <a href="experiment.php?theaction=viewall<?php echo "&sub=$sub&Gel_ID=$Gel_ID&Bait_ID=$Bait_ID";?>" class=button>[Back to Experiment]</a>&nbsp;
    </td>
  </tr>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td colspan=2><br><font szie=3>  
    </td>
  </tr>
  <tr>
    <td align="center" colspan=2>
<?php 
// start to display Experiment and Gel information ------------------------------------------
?>
    <table align="center" cellspacing="0" cellpadding="0" border="0" width=700>
    <tr>
       <td valign=top align=center>
        <table cellspacing="1" cellpadding="0" border="0" width=400>
          <tr>
              <td colspan="2" bgcolor="<?php echo $TB_HD_COLOR;?>" height="20">
            <div class=tableheader><b>&nbsp; Experiment ( <?php echo $Exp_ID;?> )</b></div></td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td width=40%><div class=maintext><b>&nbsp;Gene ID</b>:</div></td>
              <td width=60%><div class=maintext>&nbsp;<?php echo $Bait->GeneID;?></div></td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td width=40%><div class=maintext><b>&nbsp;Locus Tag </b>:</div></td>
              <td width=60%><div class=maintext>&nbsp;<?php echo $Bait->LocusTag;?></div></td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td><div class=maintext><b>&nbsp;Gene Name:</b></td>
              <td><div class=maintext>&nbsp;<?php echo $Bait->GeneName;?></div></td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td><div class=maintext><b>&nbsp;Clone Number:</b></div></td>
              <td><div class=maintext>&nbsp;<?php echo $Bait->Clone;?></div></td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td><div class=maintext>&nbsp;<b>Exp. Name:</b></div></td>
              <td><div class=maintext>&nbsp;<?php echo $Exp->Name;?></div></td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td><div class=maintext>&nbsp;<b>Created by:</div></td>
              <td><div class=maintext>&nbsp;<?php echo $ExpOwner.' ' . $Exp->DateTime;?></div></td>
          </tr>
        </table>
           </td>
    </tr>
    <tr>
       <td align=center valign=top><img src="./images/arrow_down.gif" border=0></td>
    </tr>
    <tr>
       <td align=center>
<?php 
// end of displaying Experiment and Gel information -----------------------------------------------
//Gel Lane information in the experiment 
  
?>
  <form name=lane_form method=post action=<?php echo $PHP_SELF;?>>
    <input type=hidden name=theaction value='viewband'>    
    <input type=hidden name=Exp_ID value="<?php echo $Exp_ID;?>">
    <input type=hidden name=Gel_ID value=''>
    <input type=hidden name=sub value=<?php echo $sub;?>>
     <table border="0" cellpadding="0" cellspacing="1" width="100%">
    <tr bgcolor="">
    <td width="" height="20" width=5% bgcolor="<?php echo $TB_HD_COLOR;?>" align=center>
      <a href="lane.php?theaction=viewall&Exp_ID=<?php echo $Exp_ID;?>&order_by=ID<?php echo ($sub)?"&sub=$sub":"";?>">
      <div class=tableheader>
    ID</div></a>
    </td>
    <td width="" height="20" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center>
      <a href="lane.php?theaction=viewall&Exp_ID=<?php echo $Exp_ID;?>&order_by=LaneCode<?php echo ($sub)?"&sub=$sub":"";?>">
        <div class=tableheader>
    Lane Code</div></a>
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center>
        <div class=tableheader>
    Lane Number</div></a>
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center>
      <a href="lane.php?theaction=viewall&Exp_ID=<?php echo $Exp_ID;?>&order_by=GelID<?php echo ($sub)?"&sub=$sub":"";?>">
        <div class=tableheader>
    Gel Name</div></a>
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center>
        <div class=tableheader>
    Gel Image</div> 
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align="center" align=center>
      <a href="lane.php?theaction=viewall&Exp_ID=<?php echo $Exp_ID;?>&order_by=DateTime<?php echo ($sub)?"&sub=$sub":"";?>">
      <div class=tableheader>Created</div></a>
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align="center" align=center>
      <div class=tableheader>In Plate</div> 
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align="center">
      <div class=tableheader>Options</div>
    </td>
  </tr>
<?php 

for($i=0; $i < $Lanes->count; $i++) {
    $Gel = new Gel();
    $Gel->fetch($Lanes->GelID[$i]);
    $Plates = new Plate();
    $Plates->get_plates_in_lane($Lanes->ID[$i]);
    
?>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php echo $Lanes->ID[$i];?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp; 
        <?php echo $Lanes->LaneCode[$i];?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php echo $Lanes->LaneNum[$i];?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php echo $Gel->Name;?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp;
      <?php 
      if($Gel->Image){
          echo "[<a href=\"javascript: view_image('" . $Gel->ID. "');\">";
          echo $Gel->Image;
          echo "</a>]";
       } 
      ?>
      </div>
    </td>
    <td width="" align="center"><div class=maintext>
        <?php echo $Lanes->DateTime[$i];?>&nbsp;
        </div>
    </td>
    <td width="" align="left"><div class=maintext>
        <?php  for($k=0; $k< $Plates->count; $k++){
              if($k) echo "<br>";
              echo $Plates->Name[$k]."(".$Plates->ID[$k].")";
           }
        ?>&nbsp;
        </div>
    </td>
    <td width="" align="center">
      <div class=maintext>
       <a href="band.php?theaction=viewband&Gel_ID=<?php echo $Gel->ID;?>&Lane_ID=<?php echo $Lanes->ID[$i];?><?php echo "&sub=$sub&Exp_ID=$Exp_ID";?>">
   <?php if(!$sub){?>     
        <img border="0" src="images/icon_tree.gif" alt="experiments">
   <?php }else{?>
        <img src='images/arrow_small.gif' border=0>
   <?php }?>     
       </a>&nbsp;
      </div>
    </td>
  </tr>
  
<?php 
} //end for
?>

      </table>

     </form>
       </td>
      </tr>
     </table>
    </td>
  </tr>
</table>

<?php 

require("site_footer.php");

?>

