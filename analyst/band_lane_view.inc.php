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


if($Lane_ID ){
  $Lane = new Lane($Lane_ID);
  $frm_LaneCode = $Lane->LaneCode;  
  $frm_LaneNum = $Lane->LaneNum;
  $frm_Notes = $Lane->Notes;
}

if($Lane_ID and $theaction != "modifylane") {
  $plate_arr = $Lane->lane_in_plates($Lane_ID);
   
?>
    <table border="0" cellpadding="0" cellspacing="1" width="740">
    <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
    <td   align="left" height=18>
      <div class=tableheader><b>&nbsp;Gel Lane (<?php echo $Lane_ID;?>)</b></div>
    </td>
     
    <td   align="left" height=18> <div class=tableheader>&nbsp;In Plate:
    <?php 
    for($i = 0; $i < count($plate_arr); $i++){
      echo "<a href=\"javascript: change_plate_ID('".$plate_arr[$i]."');\">".$plate_arr[$i]."</a> ";
    }
    ?>
    </div>
    </td>
    <td align=right>
     <?php   if($AUTH->Modify and ($Lane->OwnerID == $AccessUserID or $SuperUsers)) {?>
      <a href="band.php?theaction=modifylane&Lane_ID=<?php echo $Lane->ID;?><?php echo "&sub=$sub&Bait_ID=$Bait_ID&Gel_ID=$Gel_ID&Exp_ID=$Exp_ID";?>">
      <img border="0" src="images/icon_view.gif" alt="Modify Gel Lane"></a>&nbsp;
       <?php }?>&nbsp;
    </td>
  </tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
    <td align="right"  width=25%>
      <div class=maintext><b>Lane Code</b>:&nbsp;</div>
    </td>
    <td  width=75% colspan=2><div class=maintext>&nbsp;&nbsp;<?php echo $frm_LaneCode;?></div></td>
  </tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
    <td align="right" nowrap>
      <div class=maintext><b>Lane Number</b>:</font>&nbsp;</div>
    </td>
    <td colspan=2><div class=maintext>&nbsp;&nbsp; <?php echo $frm_LaneNum;?></div></td>
  </tr>
  
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
    <td align="right" valign=top>
      <div class=maintext>Lane Notes:</font>&nbsp;</div>
    </td> 
    <td colspan=2><div class=maintext>&nbsp;&nbsp;<?php 
      $tmpStr = nl2br(htmlspecialchars($frm_Notes));
     echo $tmpStr;
    ?>
    </div>
    </td>
  </tr>
  </table>

<?php 
}else{
  $SQL = "SELECT `ID`,`LaneNum`,`ExpID` FROM `Lane` WHERE `GelID`='$Gel_ID' AND `ProjectID`='$AccessProjectID'";
  $usedLaneArr = array();
  if($temLaneArr = $HITSDB->fetchAll($SQL)){
    foreach($temLaneArr as $temLaneVal){
      $temArr['ID'] = $temLaneVal['ID'];
      $temArr['ExpID'] = $temLaneVal['ExpID'];
      $usedLaneArr[$temLaneVal['LaneNum']] = $temArr;
    }  
  }
?>
 <table border="0" cellpadding="0" cellspacing="1" width="740">
   <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
    <td colspan="2" align="left" height="20">
     <div class= tableheader>&nbsp;<b>Gel Lane<?php echo ($Lane->ID)?"( $Lane->ID )":"";?></b></div>
    </td>
  </tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" >
    <td align="right" width=25%>
     <div class=maintext><b>Lane Code</b>:&nbsp;</div>
    </td>
    <td width=75%>&nbsp;&nbsp;<input type="text" name="frm_LaneCode" size="24" maxlength=25 value="<?php echo $frm_LaneCode;?>"></td>
  </tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
    <td align="right" nowrap>
      <div class=maintext><b>Lane Number</b>:&nbsp;</div>
    </td>
    <td>&nbsp;&nbsp;<select name=frm_LaneNum onchange="check_is_used_lane(this.form)">
       <option value='none'>--select Lane num.--
      <?php 
      for($i=1; $i<=20;$i++){
        $selected = '';
        if($frm_LaneNum == $i) $selected = 'selected';
        if(array_key_exists($i, $usedLaneArr)){
          if($usedLaneArr[$i]['ExpID'] == $Exp_ID){
            $opColor = 'opColor2';
            //$optionID = $usedLaneArr[$i]['ID'];
            $optionID = 'exp';
          }else{
            $opColor = 'opColor1';
            $optionID = 'used';
          }  
      ?>    
        <option value='<?php echo $i?>' id='<?php echo $optionID?>' class="<?php echo $opColor?>" <?php echo $selected?>><?php echo $i?>
      <?php }else{?>
        <option value='<?php echo $i?>' <?php echo $selected?>><?php echo $i?>
      <?php   
        }
      }  
      ?>
      </select>
    </td>
  </tr>
  </tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
    <td align="right" valign=top><div class=maintext>Lane Notes:&nbsp;</div>
    </td> 
    <td>&nbsp;&nbsp;<textarea name=frm_Notes cols=40 rows=3><?php echo $frm_Notes;?></textarea>
    </td>
  </tr>
  <?php  if($theaction == "modifylane"){ ?>
     <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
    <td colspan=2 align=center>
    <input type="button" value="Update" onclick="javascript: modify_lane(this.form);" class="green_but">
    </td>
  </tr>
   <?php }?>
  </table>
<script language='javascript'>
function  check_is_used_lane(theForm){
  var LaneNum = theForm.frm_LaneNum;
  if(LaneNum.options[LaneNum.selectedIndex].id != ''){
    if('<?php echo $SCRIPT_NAME?>'=='band.php'){ 
      alert('this lane number has been used.');
      theForm.frm_LaneNum.value='none'
    }else if('<?php echo $SCRIPT_NAME?>'=='submit.php'){
      if(LaneNum.options[LaneNum.selectedIndex].id == 'used'){
        alert('this lane number has been used by other experiment.');
      }else{
        alert('this lane number has been used by same experiment.');
      }
      theForm.frm_LaneNum.value='none'  
    }  
  }
}
</script>
<style type="text/css">
    .opColor1 { background-color:yellow; }
    .opColor2 { background-color:#f9a76a; }
  </style> 
<?php 
}
?>