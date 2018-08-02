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

require("classes/band_class.php");
require("classes/bait_class.php");
require("classes/experiment_class.php");

//-----band_color.inc.php 
$intensity_name = array();

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
  $ExpOwner = get_userName($HITSDB, $Exp->OwnerID);
}

if($Bait_ID){
  $Bait->fetch($Bait_ID);
}

if(!$Exp_ID){
  echo "<script language=javascript>document.location.href='noaccess.html';</script>";
  exit;
}

$Bands = new Band(); 
$Band_passed_ID = '';
$close_window = '';
if($theaction == "deleteband" AND $Band_ID AND $AUTH->Delete ){
//-------------------------------------------------------------
  if($AUTH->isOwner('Band', $Band_ID,$AccessUserID) ){
    $SQL = "SELECT ID FROM Hits WHERE BandID = '$Band_ID'";
    if(mysqli_num_rows(mysqli_query($HITSDB->link, $SQL))){
      return "Error: You can't delete the Band since it has hits.";
    }else{
      $SQL = "DELETE FROM Band WHERE ID = '$Band_ID'";
      $ret = $HITSDB->execute($SQL);
      if($ret){
        $Desc = "";
        $Log->insert($AccessUserID,'Band',$Band_ID,'delete',$Desc,$AccessProjectID);
      }
    }
  }
}else if($theaction == "updateband" and $AUTH->Modify){
  $theBand = new Band($Band_ID);
    if($AccessProjectID){
      $frm_Location = preg_replace("/[^A-Za-z0-9._-]/",'',$frm_Location);
      
      $error_msg = $theBand->update($Band_ID,$frm_BandMW,$frm_Intensity,$frm_Location,$frm_Modification,$frm_Description);
      if(!$msg){
        //add record into Log table
        $Desc = "BandCode=".$frm_Location.",MW=".$frm_BandMW.",Intesity=".$frm_Intensity.",Description=".$frm_Description;
        $Log->insert($AccessUserID,'Band',$Band_ID,'modify',$Desc,$AccessProjectID);
        //end of Log table
      }
    }else{
      $error_msg ="<font color=red> You have no permission to modify this band info!</font>";
    }
}else if($theaction == "insertband" and $AUTH->Insert){
  $newBand = new Band($Band_ID);
  if($AccessProjectID){
    $frm_Location = preg_replace("/[^A-Za-z0-9_-]/",'',$frm_Location);
    
    $newBand->insert($Exp_ID,$Lane_ID,$Bait_ID,$Bait->BaitMW,$frm_BandMW,$frm_Intensity,$frm_Location,$frm_Modification,$frm_Description, $AccessUserID,$Exp->ProjectID, $Plate_ID);
    $Band_passed_ID = $newBand->ID;
    $Desc = "ExpID=$Exp_ID,LaneID=$Lane_ID,BandCode=".$frm_Location.",MW=".$frm_BandMW.",Intesity=".$frm_Intensity.",Description=".$frm_Description;
    $Log->insert($AccessUserID,'Band',$newBand->ID,'insert',$Desc,$AccessProjectID);
    $close_window = 'close';
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
  if(confirm("Are you sure that you want to delete the Band?")){
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
</script>

<table border="0" cellpadding="0" cellspacing="0" width="95%" >  
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="center" colspan=2>
    <table align="center" cellspacing="0" cellpadding="0" border="0" width=740>
    <tr>
      <td colspan=3><br>
      
      </td>
    </tr>
    <tr>
       <td valign=top align=left>
       
        <table cellspacing="1" cellpadding="0" border="0" width=355>
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
       <td>&nbsp; &nbsp;</td>
       <td valign=top align=right>
        <table cellspacing="1" cellpadding="0" border="0" width=355>
        <tr>
            <td colspan="2" bgcolor="<?php echo $TB_HD_COLOR;?>" height="20">
          <div class=tableheader>&nbsp;<b>Gel ( <?php echo $Gel_ID;?> )</b></div></td>
        </tr>
        <?php if(!$Gel_ID){?>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
            <td width=40%><div class=maintext>&nbsp;<b>Gel Name:</td>
            <td width=60% rowspan=6 align=center>
            <div class=maintext><font face="Arial" size="5" color=#ffffff>Gel Free</font>&nbsp;&nbsp;
                <?php echo $Bait->GeneName;?></div>
            </td>
        </tr>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
            <td><div class=maintext>&nbsp;<b>Gel Image:</b></div></td>
        </tr>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
            <td><div class=maintext>&nbsp;<b>Method of Staining:</b></div></td>
        </tr>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
            <td><div class=maintext>&nbsp;<b>Gel Type:</b></div></td>
        </tr>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
            <td><div class=maintext>&nbsp;<b>Uploaded:</b></div></td>
        </tr>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
            <td><div class=maintext>&nbsp;<b>Uploaded by:</div></td>
        </tr>
        <?php }else{?>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
            <td width=40%><div class=maintext>&nbsp;<b>Gel Name:</td>
            <td width=60%><div class=maintext>&nbsp;<?php echo $Gel->Name;?></td>
        </tr>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
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
        </tr>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
            <td><div class=maintext>&nbsp;<b>Method of Staining:</b></div></td>
            <td><div class=maintext>&nbsp;<?php echo $Gel->Stain;?></div></td>
        </tr>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
            <td><div class=maintext>&nbsp;<b>Gel Type:</b></div></td>
            <td><div class=maintext>&nbsp;<?php echo $Gel->GelType;?></div></td>
        </tr>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
            <td><div class=maintext>&nbsp;<b>Uploaded:</b></div></td>
            <td><div class=maintext>&nbsp;<?php echo $Gel->DateTime;?></div></td>
        </tr>
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
            <td><div class=maintext>&nbsp;<b>Uploaded by:</div></td>
            <td><div class=maintext>&nbsp;<?php echo $GelOwner;?></div></td>
        </tr>
        <?php }?>
        </table>
       </td>
    </tr>
    <tr>
       <td colspan="3" align=center valign=top><img src="./images/arrow_down.gif" border=0></td>
    </tr>
<form name=action_form method=post action=<?php echo $_SERVER['PHP_SELF'];?>>
<input type=hidden name=theaction value='<?php echo $theaction;?>'>
<input type=hidden name=Lane_ID value="<?php echo $Lane_ID;?>">
<input type=hidden name=Exp_ID value="<?php echo $Exp_ID;?>">
<input type=hidden name=Gel_ID value="<?php echo $Gel_ID;?>">
<input type=hidden name=Bait_ID value="<?php echo $Bait_ID;?>">
<input type=hidden name=Band_passed_ID value="<?php echo $Band_passed_ID;?>">
<input type=hidden name=sub value=<?php echo $sub;?>>
<input type=hidden name=ProjectID value="<?php echo $ProjectID?>">    
<input type=hidden name=gelMode value="<?php echo $gelMode?>">
<input type=hidden name=addNewType value="<?php echo $addNewType;?>">
<input type=hidden name=selectedWellCode_str value=<?php echo $selectedWellCode_str;?>>
<input type=hidden name=Band_ID value=''>
<input type=hidden name=close_window value="<?php echo $close_window?>"> 
<input type=hidden name=DBname value=<?php echo $DBname;?>>
<?php 
$Bands = new Band();
$Bands->fetch_band_inOneExp($Exp_ID);
if(!$Bands->count && 0){
?> 
  <tr bgcolor="" align="center">
	  <td colspan="4" align="right">
	    <div class=maintext>
      If you want to put the samples to plate click this button&nbsp;&nbsp;
		  <input type="button" value=" Put Sample in Plate" onClick="javascript: goto_band();">
		  </div>
	  </td>
	</tr>
<?php }
if($AUTH->Insert){?>  
  <tr bgcolor="" align="center">
	  <td colspan="4" align="right">
	    <div class=maintext>
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
exit;
?>