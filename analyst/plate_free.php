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

$Bait_ID = 0;
$Exp_ID = 0;
$Band_ID = 0;
$Plate_ID = ''; 
$Lane_ID = 0; 
$whichPlate = ''; 
$band_counter = 0; 
$selectedWellCode_str = ''; 

$frm_WellCode = '';
$frm_LaneCode ='';
$frm_Intensity = '';
$frm_BandMW = '';
$frm_Location = '';
$frm_swath = '';
$frm_Modification = '';
$frm_Description = '';
$frm_LaneNum = '';
$frm_Notes = '';
$msg = '';
$pro_type_counter = '';
$error_msg = '';
$sample_id_arr = array();
 
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
require("analyst/classes/band_class.php");
require("analyst/classes/bait_class.php");
require("analyst/classes/experiment_class.php");
require("analyst/status_fun_inc.php");

//-----band_color.inc.php 
$intensity_name = array();
require("analyst/site_header.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

//theaction = "addnew" -- form to add new gel lane and bands
//theaction = "insert" -- insert into gel lane and band table, change theaction to "viewband"
//theaction = "viewband" -- display gel lane with bands
//theaction = "addnewband" -- display gel lane with bands, add on line band form 
//theaction = "insetnewband" -- insert the new band into band table
//theaction = "deleteband" -- delete one band then change theaction to "viewband"
//theaction = "modifyband" -- display band form.
//theaction = "updateband" -- update bane then change theaction to "viewband"
//theaction = "modifylane" -- display form to modify gel lane
//theaction = "updatelane" -- update gel lane change thecation to "viewband"
//theaction = "deletelane" -- delete the gel lane if it hasn't set to plate well then change theaction to "andnew"


//$TB_CELL_COLOR =  "#e3e3e3";
//$TB_CELL_COLOR = "yellow";
//=======================================
$Band = new Band();
$Exp = new Experiment();
$Bait = new Bait();

$ExpOwner = '';
$Log = new Log();

if($Band_ID){
  $Band->fetch($Band_ID);
  $Exp_ID = $Band->ExpID;
}

if($Exp_ID){
  $Exp->fetch($Exp_ID);
  $Bait_ID = $Exp->BaitID;
  $ExpOwner = get_userName($mainDB, $Exp->OwnerID);
}

if($Bait_ID){
  $Bait->fetch($Bait_ID);
}

if(!$Exp_ID){
  echo "<script language=javascript>document.location.href='noaccess.html';</script>";
  exit;
}

$Bands = new Band();
if($theaction == "unlink_sample_protocol" AND $Band_ID AND $linked_pro_id AND $AUTH->Delete ){
  $SQL = "DELETE FROM BandGroup 
          WHERE ID='$linked_pro_id'";
  if($HITSDB->execute($SQL)){
    $Desc = "Sample protocol ID='$linked_pro_id'";
    $Log->insert($AccessUserID,'BandGroup',$linked_pro_id,'delete',$Desc,$AccessProjectID);
  }
  $theaction = "modifyband";
}elseif($theaction == "deleteband" AND $Band_ID AND $AUTH->Delete ){
//-------------------------------------------------------------
  if($AUTH->isOwner('Band', $Band_ID,$AccessUserID) ){
    $SQL = "SELECT ID FROM Hits WHERE BandID = '$Band_ID'";
    if(mysqli_num_rows(mysqli_query($mainDB->link, $SQL))){
      return "Error: You can't delete the Band since it has hits.";
    }else{
      $SQL = "DELETE FROM Band WHERE ID = '$Band_ID'";
      $ret = $mainDB->execute($SQL);
      if($ret){
        $Desc = "";
        $Log->insert($AccessUserID,'Band',$Band_ID,'delete',$Desc,$AccessProjectID);
        $SQL = "DELETE FROM BandGroup 
                WHERE RecordID='$Band_ID'";
        if($HITSDB->execute($SQL)){
          $Desc = "Sample ID='$Band_ID'";
          $Log->insert($AccessUserID,'BandGroup',$Band_ID,'delete',$Desc,$AccessProjectID);
        }
        
      }
      //$deleted_user_pro_id = $Band_ID;
      //$theaction = 'delete_sample_protocol';
    }
  }
}else if($theaction == "updateband" and $AUTH->Modify){
  $theBand = new Band($Band_ID);
    if($AccessProjectID){
      $frm_Location = preg_replace("/[^A-Za-z0-9._-]/",'',$frm_Location);
       
      $error_msg = $theBand->update($Band_ID,$frm_BandMW,$frm_Intensity,$frm_Location,$frm_Modification,$frm_Description, $frm_swath);
      if(!$msg){
        //add record into Log table
        $Desc = "BandCode=".$frm_Location.",MW=".$frm_BandMW.",Intesity=".$frm_Intensity.",Description=".$frm_Description;
        $Log->insert($AccessUserID,'Band',$Band_ID,'modify',$Desc,$AccessProjectID);
        //end of Log table
      }
      $item_ID = $Band_ID;
      $theaction = 'add_change_protocols';
    }else{
      $error_msg ="<font color=red> You have no permission to modify this band info!</font>";
    }
}else if($theaction == "insertband" and $AUTH->Insert){
  $newBand = new Band($Band_ID);
  if($AccessProjectID){
    //$frm_Location = preg_replace("/[^A-Za-z0-9_-]/",'',$frm_Location);
    $frm_Location = mysqli_real_escape_string($mainDB->link, trim($frm_Location));
    $newBand->insert($Exp_ID,$Lane_ID,$Bait_ID,$Bait->BaitMW,$frm_BandMW,$frm_Intensity,$frm_Location,$frm_Modification,$frm_Description,$AccessUserID,$Exp->ProjectID, $Plate_ID, $frm_swath);
    $Desc = "ExpID=$Exp_ID,LaneID=$Lane_ID,BandCode=".$frm_Location.",MW=".$frm_BandMW.",Intesity=".$frm_Intensity.",Description=".$frm_Description;
    $Log->insert($AccessUserID,'Band',$newBand->ID,'insert',$Desc,$AccessProjectID);
    $item_ID = $newBand->ID;
    $theaction = 'add_change_protocols';
  } 
}
if($theaction == 'delete_sample_protocol'){
  $labal = 'Band';
  if($deleted_user_pro_id){
    $SQL = "DELETE FROM BandGroup 
            WHERE ID='$deleted_user_pro_id'";
    if($HITSDB->execute($SQL)){
      $Desc = "Sample protocol ID='$deleted_user_pro_id'";
      $Log->insert($AccessUserID,$labal.'Group',$item_ID,'delete',$Desc,$AccessProjectID);
    }
  }
  $theaction = '';
}elseif($theaction == 'add_change_protocols'){
  $labal = 'Band';  
  for($i=1; $i<=$pro_type_counter; $i++){
    $v_name = 'frm_ProType_'.$i;    
    $tmp_pro_arr = explode("___", $$v_name, 2);
    $protocol_id = $tmp_pro_arr[0];
    $protocol_type = $tmp_pro_arr[1];
    if(!$protocol_id){
      $SQL = "DELETE FROM BandGroup WHERE Note='$protocol_type' AND RecordID='$item_ID'";
      if($HITSDB->execute($SQL)){
        $Desc = "Sample protocol Note='$protocol_type'";
        $Log->insert($AccessUserID,$labal.'Group',$item_ID,'delete',$Desc,$AccessProjectID);
      }
     continue;
    }
    $SQL = "SELECT `ID`,
            `NoteTypeID`,
            `UserID`
            FROM `BandGroup` 
            WHERE `RecordID`='$item_ID'
            AND `Note`='$protocol_type'";
    $tmp_pro_arr = $HITSDB->fetch($SQL);    
    if(!$tmp_pro_arr){
      $SQL = "INSERT INTO BandGroup SET
              `RecordID`='$item_ID',
              `Note`='$protocol_type',
              `NoteTypeID`='$protocol_id',
              `UserID`='$AccessUserID',
              `DateTime`=now()";
      if($ret_id = $HITSDB->insert($SQL)){
        $Desc = "NoteTypeID = $protocol_id(Sample protocol ID)";
        $Log->insert($AccessUserID,$labal.'Group',$ret_id,'insert',$Desc,$AccessProjectID);
      }    
    }elseif($tmp_pro_arr && $tmp_pro_arr['UserID'] == $AccessUserID){
      if(!$protocol_id ){
        $SQL = "DELETE FROM BandGroup WHERE ID='".$tmp_pro_arr['ID']."'";
        if($HITSDB->execute($SQL)){
          $Desc = "Sample protocol";
          $Log->insert($AccessUserID,$labal.'Group',$tmp_pro_arr['ID'],'delete',$Desc,$AccessProjectID);
        }
      }elseif($protocol_id == $tmp_pro_arr['NoteTypeID']){
        continue;
      }else{
        $SQL = "UPDATE BandGroup SET
                `NoteTypeID`='$protocol_id'
                WHERE ID='".$tmp_pro_arr['ID']."'";
        if($HITSDB->execute($SQL)){        
          $Desc = "Sample protocol";
          $Log->insert($AccessUserID,$labal.'Group',$tmp_pro_arr['ID'],'update',$Desc,$AccessProjectID);
        }
      }
    }
  }  
}
?>
<script language="javascript">
function goto_band(){
  theForm = document.action_form;
  theForm.action = "band.php"
  theForm.theaction.value = "new";
  theForm.submit();
}
function add_band(){
  theForm = document.action_form;
  theForm.theaction.value = "addband";
  theForm.submit();
}
function uncheckradio(theradio){
  for (var i=0; i < theradio.length; i++) {
    theradio[i].checked = false;
  }
}
function add_one_band(){
    //for add one more band
  document.band_form.theaction.value = "addnewband";
  document.band_form.submit();
}
function confirm_delete_band(Band_ID){
  if(confirm("Are you sure that you want to delete the sample?")){
    document.action_form.Band_ID.value = Band_ID;
    document.action_form.theaction.value = "deleteband";    
    document.action_form.submit();
  }
}

function radio_checked(radio_array){
  var is_checked = false;
  for(var i=0; i < radio_array.length; i++){
    if(radio_array[i].checked == true){
      is_checked = true;
    }
  }
  return is_checked;
}
function isNumber(str) {
  for(var position=0; position<str.length; position++){
  var chr = str.charAt(position)
        if ( ( (chr < "0") || (chr > "9") ) && chr != ".")
              return false;
  };      
  return true;
}
function isEmptyStr(str){
    var str = this != window? this : str;
  var temstr =  str.replace(/^\s+/g, '').replace(/\s+$/g, '');
  if(temstr == 0 || temstr == ''){
     return true;
  } else {
    return false;
  }
}
function trimString (str) {
  var str = this != window? this : str;
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}

function processAjaxReturn(rp){
  var ret_html_arr = rp.split("@@**@@");
  if(ret_html_arr.length == 2){
    var div_id = trimString(ret_html_arr[0]);
    document.getElementById(div_id).innerHTML = ret_html_arr[1];
    return;
  }
}   
</script>
<?php if($sub){?>
<table cellspacing="1" cellpadding="0" border="0" align=center>
<tr>
<td>
<?php if($sub != 3){?>
    <img src="./images/arrow_green_gel.gif" border=0>
<?php }?>
    <img src="./images/arrow_green_bait.gif" border=0>
    <img src="./images/arrow_green_exp.gif" border=0>
    <img src="./images/arrow_red_band.gif" border=0>
<?php if($sub != 3){?>    
    <img src="./images/arrow_green_well.gif" border=0>
<?php }?> 
</td>   
</tr>
</table>
<?php }?>
<table border="0" cellpadding="0" cellspacing="0" width="95%" >
  <tr>
    <td colspan=2><div class=maintext>
      <img src="images/icon_purge.gif"> Delete 
      <img src="images/icon_tree.gif"> Next Level
      <img src="images/icon_view.gif"> Modify 
      </div>
    </td>
  </tr>
  <tr>
    <td align="left">
    &nbsp; <font color="#006699" face="helvetica,arial,futura" size="3"><b><?php echo ($Gel_ID)?"Band":"Sample";?>
    <?php 
    if($AccessProjectName){
      echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project&nbsp;$AccessProjectID: $AccessProjectName)</font>";
    }
    if($sub && !$Gel_ID){
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color='blue' face='helvetica,arial,futura' size='3'>(Submit Gel Free Sample)</font>";
    }
    ?>
    </b>
    </font> 
  </td>
    <td align="right">
    <a href="experiment.php?theaction=viewall<?php echo ($sub)?"&sub=$sub":"";?><?php echo "&Gel_ID=$Gel_ID&Bait_ID=$Bait_ID";?>" class=button>[Back to Experiment]</a>&nbsp;
    </td>
  </tr>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="center" colspan=2>
    <table align="center" cellspacing="0" cellpadding="0" border="0" width=900>
    <tr>
      <td colspan=3><br>
      
      </td>
    </tr>
    <tr>
       <td valign=top align=left colspan="3">
        <table cellspacing="1" cellpadding="0" border="0" width=100%>
          <tr>
              <td colspan="2" bgcolor="<?php echo $TB_HD_COLOR;?>" height="20">
            <div class=tableheader><b>&nbsp;Bait ID (<?php echo $Bait_ID;?>)&nbsp;&nbsp; Experiment ( <?php echo $Exp_ID;?> )</b></div></td>
          </tr>
          <?php if($Bait->Clone == "dummy"){?>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td width=40%><div class=maintext>&nbsp;</div></td>
              <td width=60% rowspan=4 align=center>
                <div class=maintext><font face="Arial" size="5" color=#ffffff>No Bait</font>&nbsp;&nbsp;
                <?php echo $Bait->GeneName;?></div>
              </td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td><div class=maintext><b>&nbsp;</td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td><div class=maintext><b>&nbsp;</div></td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td><div class=maintext><b>&nbsp;</div></td>
          </tr>
          <?php }else{?>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td><div class=maintext><b>&nbsp;Gene ID:</b></td>
              <td><div class=maintext>&nbsp;<?php echo $Bait->GeneID;?></div></td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td width=40%><div class=maintext><b>&nbsp;LocusTag/GeneName</b>:</div></td>
              <td width=60%><div class=maintext>&nbsp;<?php echo $Bait->LocusTag."  <b>/</b> ".$Bait->GeneName;?></div></td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td><div class=maintext><b>&nbsp;Bait MW:</b></td>
              <td><div class=maintext>&nbsp;<?php echo ($Bait->BaitMW != 0)?$Bait->BaitMW." kDa":"";?></div></td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td><div class=maintext><b>&nbsp;Clone Number:</b></div></td>
              <td><div class=maintext>&nbsp;<?php echo $Bait->Clone;?></div></td>
          </tr>
          <?php }?>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td><div class=maintext>&nbsp;<b>Exp. Name:</b></div></td>
              <td><div class=maintext>&nbsp;<?php echo $Exp->Name;?></div></td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
              <td><div class=maintext>&nbsp;<b>Created by:</div></td>
              <td><div class=maintext>&nbsp;<?php echo $ExpOwner .'   ' . $Exp->DateTime;?></div></td>
          </tr>
        </table>
      </td>
    </tr>
     
<form name=action_form method=post action=<?php echo $_SERVER['PHP_SELF'];?>>
<input type=hidden name=theaction value='<?php echo $theaction;?>'>
<input type=hidden name=Lane_ID value="<?php echo $Lane_ID;?>">
<input type=hidden name=Exp_ID value="<?php echo $Exp_ID;?>">
<input type=hidden name=Gel_ID value="<?php echo $Gel_ID;?>">
<input type=hidden name=Bait_ID value="<?php echo $Bait_ID;?>">
<input type=hidden name=sub value=<?php echo $sub;?>>
<input type=hidden name=selectedWellCode_str value=<?php echo $selectedWellCode_str;?>>
<input type=hidden name=Band_ID value=''> 
<?php 
$Bands = new Band();
$Bands->fetch_band_inOneExp($Exp_ID);
if(!$Bands->count && 0){
?> 
  <tr bgcolor="" align="center">
	  <td colspan="4" align="right">
	    <div class=maintext><br>
      If you want to put the samples to plate click this button&nbsp;&nbsp;
		  <input type="button" value=" Put Sample in Plate" onClick="javascript: goto_band();">
		  </div>
	  </td>
	</tr>
<?php }
if($AUTH->Insert){?>  
  <tr bgcolor="" align="center">
	  <td colspan="4" align="right">
	    <div class=maintext><br>
		  <input type="button" value=" Add New Sample " onClick="javascript: add_band();">
		  </div>
	  </td>
	</tr>
  <?php 
  }
    $IdGeneName = $Bait->ID.$Bait->GeneName;
  ?>
  <tr>
       <td colspan="3" align=center>
<?php 
// end of display Experiment and Gel information -----------------------------------------------
//-------------------------------------------------------------------------
//get band list 

//if($Bands->count or $band_counter){
   include("plate_free_band_list.inc.php"); 
//}
//-----------------------------------------------------------------------------
?>
      </td>
      </tr>
      </form>
     </table>
    </td>
  </tr>
</table><br>
<?php 
require("site_footer.php");
exit;
?>