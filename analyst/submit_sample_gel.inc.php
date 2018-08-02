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

require("classes/gel_class.php");
require("classes/lane_class.php");
require("classes/band_class.php");
require("classes/plate_class.php");
require("classes/plateWell_class.php");
require("classes/bait_class.php");
require("classes/experiment_class.php");

//-----band_color.inc.php 
$intensity_name = array();
$sample_id_arr = array();

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
$Lane = new Lane();
$Exp = new Experiment();
$Bait = new Bait();
$Gel = new Gel();
$Plate = new Plate();
$PlateWells = new PlateWell();

$ExpOwner = '';
$GelOwner = '';
$Log = new Log();
if($Band_ID){
  $Band->fetch($Band_ID);
  $Lane_ID = $Band->LaneID;
  $Exp_ID = $Band->ExpID;
}
if($Lane_ID){
  $Lane->fetch($Lane_ID);
	$Exp_ID = $Lane->ExpID;
  $Gel_ID = $Lane->GelID;
}
if($Exp_ID){
  $Exp->fetch($Exp_ID);
  $Bait_ID = $Exp->BaitID;
  $ExpOwner = get_userName($HITSDB, $Exp->OwnerID);
}

if($Gel_ID){
 $Gel->fetch($Gel_ID);
 $GelOwner = get_userName($HITSDB, $Gel->OwnerID);
}
if($Bait_ID){
  $Bait->fetch($Bait_ID);
}

if(!$Exp_ID){
  echo "<script language=javascript>document.location.href='noaccess.html';</script>";
  exit;
}

//===if(!$Exp_ID){ header ("Location: noaccess.html"); exit;}
//echo "Bait=$Bait_ID; Exit=$Exp_ID; Lane=$Lane_ID; Gel=$Gel_ID; Band=$Band_ID"; //check passed value
 
//$Lanes = new Lane();
$Bands = new Band(); 

if($whichPlate == 'new'){
   $Plate_ID = '';
}else if($whichPlate == 'last' ){
  $Plate_ID = $Plate->get_one('last');
  $msg_plate = "last plate";
}else if($whichPlate == 'first'){
  $Plate_ID = $Plate->get_one('first');
  $msg_plate = "first availabel created plate";
}else if($whichPlate == 'next' and $Plate_ID){
  $Plate_ID = $Plate->get_one('next',$Plate_ID);
}else if($whichPlate == 'previous' and $Plate_ID){
  $Plate_ID = $Plate->get_one('previous', $Plate_ID);
}

if($theaction == "deleteband" AND $Band_ID AND $AUTH->Delete ) {
  
  if($AUTH->isOwner('Band', $Band_ID,$AccessUserID)){
     $error_msg = $Bands->delete($Band_ID);
     //echo $error_msg;
     $theaction = "viewband";     
     if(strstr($error_msg,"Band/sample has been deleted") ){
      $Desc = "";
      $Log->insert($AccessUserID,'Band',$Band_ID,'delete',$Desc,$AccessProjectID);
      //$Band_ID = '-1';
      //echo "\$Band_ID=$Band_ID";
     }
     if(strstr($error_msg,"Well has been deleted") ){
      $Desc = "BandID=$Band_ID";
      $Log->insert($AccessUserID,'PlateWell',$Band_ID,'delete',$Desc,$AccessProjectID);
     }
     if(strstr($error_msg,"Lane has been deleted")){
      $Desc = "";
      $Log->insert($AccessUserID,'Lane',$Lane_ID,'delete',$Desc,$AccessProjectID);
      $Lane_ID = 0;
      $Lane = new Lane();
      //header ("Location: ./experiment.php?theaction=viewall&Bait_ID=$Bait_ID&sub=$sub");
     }
     if(strstr($error_msg,"Plate has been deleted") ){
       $Desc = "";
       $Log->insert($AccessUserID,'Plate',$Plate_ID,'delete',$Desc,$AccessProjectID); 
       $Plate_ID = 0;
       $Plate_ID = $Plate->get_one('last');
       if(!$Plate_ID){
         $whichPlate = 'new';
       }
     }
  }
}else if($theaction == "updateband" and $AUTH->Modify){
  $theBand = new Band($Band_ID);
    if($AccessProjectID){
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
}else if($theaction == "updatelane" and $AUTH->Modify){
  $error_msg = $Lane->update( $Lane_ID, $Lane->GelID, $frm_LaneNum, $frm_LaneCode, $frm_Notes);
  if(!$error_msg){
     $Desc = "LaneCode=$frm_LaneCode,LaneNum=$frm_LaneNum";
     $Log->insert($AccessUserID,'Lane',$Lane_ID,'modify',$Desc,$AccessProjectID);
     $theaction = "viewband";
  }else{
    $theaction = "modifylane";  
    $frm_LaneCode = '';
  }
}
$message ='';
if($frm_WellCode and $AUTH->Modify){
   if($theaction == "selectWell"){
     if( $selectedWellCode_str){
       $selectedWellCode_str .= ",$frm_WellCode";
      }else{
        $selectedWellCode_str = "$frm_WellCode";
      }
      
      if(!$Plate_ID and $frm_Name){
        $PlateNameExist = 0;
        $DBindexArr = get_allProjectsDB_index($HITSDB);
        $oldDBName =$HITSDB->selected_db_name;
        for($i=0; $i<count($DBindexArr); $i++){
          $HITSDB->change_db($HITS_DB[$DBindexArr[$i]]);
          $SQL = "SELECT ID FROM Plate WHERE Name='$frm_Name'";
          $PlateArr = $HITSDB->fetch($SQL);
          if(count($PlateArr)){
            $PlateNameExist = 1;
            break;
          }
        }
        back_to_oldDB($HITSDB, $oldDBName);
        if(!$PlateNameExist){
        //echo "insert in db";
          //after insert the new plate it will be fetch to $Plate object
         $error_msg = $Plate->insert(
                    $frm_Name,
                    $frm_PlateNotes,
                    $AccessUserID,
                    $frm_DigestedBy,
                    $frm_DigestStarted,
                    $frm_DigestCompleted,
                    $frm_Buffer);
          //add record into Log table
          //$Desc = "PlateName=$frm_Name";
          $Desc = "Name=$frm_Name,DigestedBy=$frm_DigestedBy,DigestStarted=$frm_DigestStarted,DigestCompleted=$frm_DigestCompleted,Buffer=$frm_Buffer";
          $Log->insert($AccessUserID,'Plate',$Plate->ID,'insert',$Desc,$AccessProjectID);
          //end of Log table
          $Plate_ID = $Plate->ID;        
          $whichPlate = '';
        }else{
          $selectedWellCode_str = '';
          $theaction = "viewband";
          $Plate_ID = '';
          $whichPlate = 'new';
          $message = "The plate name has been used, please try another name";                   
        }  
      }
   }else if($theaction == "removeWell"){
      $search = array("/^$frm_WellCode,/", "/,$frm_WellCode/","/$frm_WellCode/");
      $replace = array("","","");
      $selectedWellCode_str = preg_replace($search, $replace, $selectedWellCode_str);
      $band_counter--;
      if(strlen($selectedWellCode_str)===0 and $Plate->is_empty_plate($Plate_ID) ){
         $Plate_ID = $Plate->get_one('last');
         $msg_plate = "last plate"; 
      }
   }
   //$PlateWells->insert($Plate_ID, '', $frm_WellCode, $AccessUserID, $Project->ID){
     //log table.
   //$Desc = "PlateID=$Plate_ID,Band_ID=$Band_ID,WellCode=$frm_WellCode";
   //$Log->insert($AccessUserID,'PlateWell',$PlateWell->ID,'insert',$Desc,$AccessProjectID);
   //end of Log table
}

$band_counter = ($band_counter)?$band_counter:0;
$selectedWellCode_arr = array();
if($selectedWellCode_str){
    $selectedWellCode_arr = explode(",", $selectedWellCode_str);
    sort($selectedWellCode_arr );
    $band_counter = count($selectedWellCode_arr); 
    for($i=0; $i < count($selectedWellCode_arr); $i++) {
      $location_name[$i] = "frm_Location_".$selectedWellCode_arr[$i];
      $intensity_name[$i] = "frm_Intensity_".$selectedWellCode_arr[$i];
      $bandMW_name[$i] = "frm_BandMW_".$selectedWellCode_arr[$i];
      $bandModification_name[$i] = "frm_Modification_".$selectedWellCode_arr[$i];
      $bandDescription_name[$i] = "frm_Description_".$selectedWellCode_arr[$i];
      if(!isset($$location_name[$i])){
        $$location_name[$i] = "";
        $$intensity_name[$i] = "";
        $$bandMW_name[$i] = "";
        $$bandModification_name[$i] = "";
        $$bandDescription_name[$i] = "";
      }
   }
}


?>
<script language="javascript">
function uncheckradio(theradio){
  for (var i=0; i < theradio.length; i++) {
    theradio[i].checked = false;
  }
}
function list_new_band(theForm){
  theForm.action = "band_show.php";
  theForm.theaction.value = "new";
  theForm.submit();
}
function add_one_band(){
    //for add one more band
  document.band_form.theaction.value = "addnewband";
  document.band_form.submit();
}
function add_more_band(){
  var new_counter = <?php echo $band_counter;?> + 3;
  document.add_form.band_counter.value = new_counter;
  document.add_form.theaction.value = "addnew";
  document.add_form.submit();
}
function confirm_delete_band(Band_ID){
  if(confirm("Are you sure that you want to delete the Band?")){
    document.action_form.Band_ID.value = Band_ID;
    document.action_form.theaction.value = "deleteband";    
    document.action_form.submit();
  }
}
function modify_lane(theForm){
  var laneCode = theForm.frm_LaneCode.value;
  var numSelected = theForm.frm_LaneNum.options[theForm.frm_LaneNum.selectedIndex].value != "none";
  var laneNum = theForm.frm_LaneNum.value;
    if( isEmptyStr(laneCode) || !numSelected){
    alert("Bold field names are required!");
    return false;
  }
  theForm.theaction.value = 'updatelane';
  theForm.submit();
}
function back_to_view(theForm){
   theForm.theaction.value = "viewband";
   theForm.submit();
}
function change_plate_ID(plate_id){
   theForm = document.action_form;
   theForm.theaction.value = "viewband";
   theForm.Plate_ID.value = plate_id;
   theForm.submit();
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
function view_image(Gel_ID)  {  
  file = 'gel_view.php?Gel_ID=' + Gel_ID;
  newwin = window.open(file,"gel_image",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=750,height=600');
  newwin.moveTo(10,10);
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
        <?php if($Gel->GelType == "dummy" || !$Gel_ID){?>
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
    <tr>
       <td colspan="3" align=center>

<?php 

// end of display Experiment and Gel information -----------------------------------------------
$Band_passed_ID = '';
$close_window = '';
if($theaction == "insert" and $AUTH->Modify) {
    //echo " I am here"; exit;
    //add new gel lane 
    $newBand = new Band();
    $newLane = new Lane();
    
    $error_msg = "";
    if($frm_LaneCode and $frm_LaneNum and $Gel_ID ){
      $error_msg = $newLane->insert($Gel_ID, $Exp_ID, $frm_LaneNum, $frm_LaneCode, $frm_Notes, $AccessUserID, $Exp->ProjectID);
      if(!$error_msg){
        $Lane_ID = $newLane->ID;
        //echo "\$Lane_ID=$Lane_ID";exit;
        //add record into Log table
        $Desc = "GelID=$Gel_ID,ExpID=$Exp_ID,LaneCode=$frm_LaneCode,LaneNum=$frm_LaneNum";
        $Log->insert($AccessUserID,'Lane',$newLane->ID,'insert',$Desc,$AccessProjectID);
      }
    }
    if(!$error_msg){ 
      //add bands
      for($i = 0; $i < $band_counter; $i++){        
        $newLocation = $$location_name[$i];
        if(isset($$intensity_name[$i])){
          $newIntensity = $$intensity_name[$i];
        }
        $newBandMW = $$bandMW_name[$i];
        $newModification = $$bandModification_name[$i];
        $newDescription = $$bandDescription_name[$i]; 
         
        //echo $$newLocation."-".$$newIntensity."-".$$newBandMW."\n";
        if(($newLocation and $newIntensity and $newBandMW and $Gel_ID) || (!$Gel_ID and $newLocation) ){       
          if( !$PlateWells->is_exsist('', $selectedWellCode_arr[$i], $Plate_ID) ){           
            $newBand->insert($Exp_ID,$Lane_ID,$Bait_ID,$Bait->BaitMW,$newBandMW,$newIntensity,$newLocation,$newModification,$newDescription,$AccessUserID,$Exp->ProjectID, $Plate_ID);
            $Band_passed_ID = $newBand->ID;
            $Desc = "ExpID=$Exp_ID,LaneID=$Lane_ID,BandCode=".$newLocation.",MW=".$newBandMW.",Intesity=".$newIntensity.",Description=".$newDescription;
            $Log->insert($AccessUserID,'Band',$newBand->ID,'insert',$Desc,$AccessProjectID);
            
            $PlateWells->insert($Plate_ID, $newBand->ID, $selectedWellCode_arr[$i], $AccessUserID,$Exp->ProjectID);
            $Desc = "PlateID=$Plate_ID,BandID=".$newBand->ID.",WellCode=".$selectedWellCode_arr[$i];
            $Log->insert($AccessUserID,'PlateWell',$PlateWells->ID,'insert',$Desc,$AccessProjectID);
          }
        }
      }
      $selectedWellCode_str = "";
      $selectedWellCode_arr = array();
      $band_counter = 0;
      echo "<center><font color='green' face='helvetica,arial,futura' size=2>";
      echo "Insert complete.";
      echo "</font></center>";
      $close_window = 'close';
      //after insert change the action
      $theaction = "viewband";
      //if($SCRIPT_NAME == 'submit.php'){
        //$Lane_ID = 
        //echo "\$Lane_ID=$Lane_ID";exit;
    }else{
      echo "<div class=maintext><font color=red>".$error_msg."</font></div>";
      //echo "<a href=\"javascript: window.history.back();\" class=button>[ Click here to go back ]</a>";
    }
} else {
   if($theaction == "insert") {
         echo "<div class=maintext><font color=red>Error: Missing info. <b>Bold</b> field names are requiered to make the insert.</div>";
    }
}
//echo $error_msg;
?>
<form name=action_form method=post action=<?php echo $_SERVER['PHP_SELF'];?>>
<input type=hidden name=theaction value='<?php echo $theaction;?>'>
<input type=hidden name=Lane_ID value="<?php echo $Lane_ID;?>">
<input type=hidden name=Exp_ID value="<?php echo $Exp_ID;?>">
<input type=hidden name=Gel_ID value="<?php echo $Gel_ID;?>">
<input type=hidden name=Bait_ID value="<?php echo $Bait_ID;?>">
<input type=hidden name=Band_ID value="<?php echo $Band_ID;?>">
<input type=hidden name=Band_passed_ID value="<?php echo $Band_passed_ID;?>">
<input type=hidden name=sub value=<?php echo $sub;?>>
<input type=hidden name=ProjectID value="<?php echo $ProjectID?>">    
<input type=hidden name=gelMode value="<?php echo $gelMode?>">
<input type=hidden name=addNewType value="<?php echo $addNewType;?>"> 
<input type=hidden name=selectedWellCode_str value=<?php echo $selectedWellCode_str;?>>
<input type=hidden name=close_window value="<?php echo $close_window?>">
<input type=hidden name=DBname value=<?php echo $DBname;?>>
<?php 
//-------------------------------------------------------------------------
//get band list of the gel lane
$Bands = new Band();
$PlateWell_bd = new PlateWell();
  
//if it is gel free $Lane_ID='' it will fetch experiment
if($Gel_ID){
  $PlateWell_bd->fetch_wells_in_lane_or_exp($Lane_ID,0);
}else{
  $PlateWell_bd->fetch_wells_in_lane_or_exp(0,$Exp_ID);
}
$addcomma = "";
$plate_ID_arr = array();
if($PlateWell_bd->count){
  if(!$Plate_ID and !$whichPlate) $Plate_ID = $PlateWell_bd->PlateID[0];
  
  for($i = 0; $i < $PlateWell_bd->count; $i++){
    if($band_ID_str) $addcomma = ",";
    if( $Plate_ID == $PlateWell_bd->PlateID[$i]){
        $band_ID_str .= $addcomma.$PlateWell_bd->BandID[$i];        
        $WellCode_str .= $addcomma.$PlateWell_bd->WellCode[$i]; 
        if(strlen($PlateWell_bd->WellCode[$i]) < 3){
          $tmpCode = $PlateWell_bd->WellCode[$i];
          $PlateWell_bd->WellCode[$i] = $tmpCode{0} . "0" . $tmpCode{1};
        }       
        array_push($selectedWellCode_arr,$PlateWell_bd->WellCode[$i]);
    }
  }
  $plate_ID_arr = array_filter(array_unique($PlateWell_bd->PlateID));
  $Bands->fetchAll_id_str($band_ID_str);  
} 

if($Gel_ID){
   include("band_lane_view.inc.php");
}
include("plate_info.inc.php");
if($Bands->count or $band_counter){
   include("band_list.inc.php"); 
}

//-----------------------------------------------------------------------------
?>
     
</form>
       </td>
      </tr>
     </table>
    </td>
  </tr>
</table><br>
<script language='javascript'>
function checkform(theForm){
 // var theForm = document.action_form;
 //alert(theForm.name) ;
  var i;
  var Location_name;
  var Intensity_name;
  var MW_name;
<?php 
if($Gel_ID and !$Lane_ID){?>
  //check Lane info
  var numSelected = theForm.frm_LaneNum.options[theForm.frm_LaneNum.selectedIndex].value != "none";
  var laneCode = theForm.frm_LaneCode.value;
  var laneNum = theForm.frm_LaneNum.value;
  if( isEmptyStr(laneCode) || laneNum == '' || !numSelected){
    alert("Bold field names are requiered to make the insert in gel lane.");
    return false;
  }
  if('<?php echo $SCRIPT_NAME?>' == 'submit.php'){
    theForm.addNewType.value = 'Lane';
  }
<?php 
}
//make javascript for addnew 
if($Gel_ID){ //no gel free
    for($i=0; $i < $band_counter; $i++) { 
      echo "if(\n";
    
      echo "    ( isEmptyStr(theForm.$location_name[$i].value)\n";  
      echo "    && !radio_checked(theForm.$intensity_name[$i])\n";  
      echo "    && isEmptyStr(theForm.$bandMW_name[$i].value) )  || \n";
      echo "    ( !isEmptyStr(theForm.$location_name[$i].value)  \n";
      echo "    && radio_checked(theForm.$intensity_name[$i])  \n";
      echo "    && !isEmptyStr(theForm.$bandMW_name[$i].value) ) \n";
    
      echo "   ) {\n";
      echo "       //do nothing\n";
      echo "} else {\n";
            $disNum = $Bands->count + $i + 1;
      echo "  alert('Band ".$disNum." has to have band code, intensity and observed MW!');\n";
      echo "  return false;\n";
      echo "}\n";
      echo "if(!isEmptyStr(theForm.$location_name[$i].value) && !isNumber(theForm.$bandMW_name[$i].value) ){\n";
      echo "   alert(\"Observed MW has to be a number in Band ".$disNum."!\");\n";
      echo "  return false;\n";
      echo "}\n"; 
    }
}//end if
if($band_counter > 0){
echo "if(\n";
for($i=0;$i< $band_counter; $i++){
    if($i) echo "  && ";
    echo "isEmptyStr(theForm.$location_name[$i].value)\n";
}
echo "){\n";   
echo "    alert(\"You haven't input any band information yet!\");\n";
echo "} else {\n";
echo "    document.action_form.theaction.value = \"insert\";\n";
echo "    document.action_form.submit();\n";
echo "}\n";
}
echo "}\n";
echo "</script>\n";
exit;
?>