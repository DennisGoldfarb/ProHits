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

if( !$Plate_ID and !$whichPlate){
  $Plate_ID = $Plate->get_one('last');
}
if($Plate_ID and (!isset($CurrPlate_ID) or $Plate_ID != $CurrPlate_ID)){
  $CurrPlate_ID = $Plate_ID;  
}else if($whichPlate == "new" or !isset($CurrPlate_ID)){
  $CurrPlate_ID = ''; 
}else{
  $Plate_ID = $CurrPlate_ID;
}
if($Plate_ID){
 $Plate->fetch($Plate_ID);
}
if(!$whichPlate){
  $whichPlate = 'new';
}else{
  $Plate->removeEnpty($AccessUserID); 
}
if(!isset($message)){
  $message = '';
}
?>
<input type=hidden name=Plate_ID value=<?php echo $Plate_ID;?>>
<input type=hidden name=CurrPlate_ID value=<?php echo $CurrPlate_ID;?>>
<input type=hidden name=whichPlate value=''>
<input type=hidden name=frm_WellCode value=''>
<input type=hidden name=PlateName_str value=<?php echo $PlateName_str;?>> 


<script language='javascript'>
function change_plate(whichPlate){
  var theForm = document.action_form; 
  
  if(theForm.selectedWellCode_str.value.length > 0){
    alert("All selected wells have to be saved or removed before change plate.");
     
  }else{
    //Plate_ID = 0 means new plate
    if(whichPlate == 'new'){
      theForm.Plate_ID.value = '';
      theForm.whichPlate.value = 'new';
    } else if(whichPlate == 'last') {
      theForm.Plate_ID.value = '';
      theForm.whichPlate.value = 'last';
    } else if(whichPlate == 'first') {
      theForm.Plate_ID.value = '';
      theForm.whichPlate.value = 'first';
    } else if(whichPlate == 'next') {
      theForm.whichPlate.value = 'next';
    } else if(whichPlate == 'previous') {
      theForm.whichPlate.value = 'previous';
    }
    theForm.submit();
  }
}
function select_well(theWell){ 
   var in_plate_id = <?php echo ($in_Plate_ID)?$in_Plate_ID:0;?>;
   var theForm = document.action_form;
   var theName = theForm.frm_Name.value;
   if(in_plate_id != <?php echo ($Plate_ID)?$Plate_ID:'0';?> && in_plate_id != 0){
     alert("The sample was set to plate <?php echo $in_Plate_ID;?> already");
   }else if(isEmptyStr(theForm.Plate_ID.value) && (isEmptyStr(theForm.frm_Name.value)) ){
      alert('New plate name is required!'); 
   }else{
     if(isEmptyStr(theForm.Plate_ID.value)){
        theName =  trimString(theName);
        if(theName.match(/[^a-zA-Z0-9]/)){
          alert("Just enter charactors 'A-z', 'a-z' and '0-9' for plate's name");
        }else{
          theForm.theaction.value = "selectWell";
          theForm.frm_WellCode.value = theWell;  
                
          theForm.submit();
        }    
     }else{
        theForm.theaction.value = "selectWell";
        theForm.frm_WellCode.value = theWell;        
        theForm.submit();
     } 
   }
 }
 function remove_well(theWell){
   var theForm = document.action_form;
   theForm.frm_WellCode.value = theWell;
   theForm.theaction.value = "removeWell";
   if(confirm("Are you sure that you want to remove the well?")){
    theForm.submit();
   }
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
function show_band_info(Band_ID){
  var theForm = document.action_form;
  if(typeof(theForm.start_point) != 'undefined'){
    theForm.start_point.value = 0;
  }  
  theForm.Band_ID.value = Band_ID; 
  theForm.theaction.value = 'showone';
  theForm.submit();
}
 
function getToday(Field){
  <?php  $today =@time()+5*60;?>
  var Today = '<?php echo @date("Y-m-d H:i:s",$today);?>';
  Field.value = Today;
}
function modifyPlate(){
  document.action_form.theaction.value = 'modifyplate';
  document.action_form.submit();
}
function updatePlate(){
  document.action_form.theaction.value = 'updateplate';
  document.action_form.submit();

}
function trimString (str) {
  var str = this != window? this : str;
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
</script>
      <br>
      <table border="0" cellpadding="1" cellspacing="0" width="740" bgcolor="<?php echo $TB_HD_COLOR;?>">
      <tr >
        <td colspan=2 bgcolor="white" align="center" height=18><font color='red' face='helvetica,arial,futura' size=3>&nbsp;<?php echo $message;?></td>
      </tr>
      <tr>
        <td align="left" height=18><div class=tableheader>&nbsp;<b>Plate</b>
        <?php 
        
        echo ($msg_plate)?" (".$msg_plate.")":'';
        if($Plate_ID){
          echo " ( $Plate_ID )";
        }
        ?>
        </div></td>
        <td align="right" height=18>
         <?php          
         echo( $theaction !='modifyplate' and $AUTH->Modify and $Plate_ID and ($SCRIPT_NAME != 'band.php' and $SCRIPT_NAME != 'pop_plate.php' and $SCRIPT_NAME != 'submit.php' ))?"<a href=\"javascript: modifyPlate();\" class=button><font color=white>[Modify Plate]</font></a>":"";
         ?>
        </td>
      </tr>
      <tr>
        <td colspan=2>
          <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%" bgcolor="white">
            <tr>
              <td bgcolor="<?php echo $TB_CELL_GRAY;?>" align="center" valign="top" width="250">
                <table border="0" cellpadding="0" cellspacing="1" width="">
                  <tr>
                    <td nowrap width=50%><div class=maintext>&nbsp;<b>Plate Name</b>:</div></td>
                    <td width=50%><div class=maintext>
                      <?php 
											
                      if(!$Plate_ID){
                        if($message){
                          echo "<input type=text name=frm_Name value='".$frm_Name."' size=17>";
                        }else{
                          echo "<input type=text name=frm_Name value='".$Plate->Name."' size=17>";
                        }  
                      }else if($theaction == 'modifyplate'){
                        echo $Plate->Name;
                        echo "<input type=hidden name=frm_Name value='".$Plate->Name."'>";
                      }else{
                        echo $Plate->Name;
                        echo "<input type=hidden name=frm_Name value=''>";
                      }  
                      ?></div></td>
                  </tr>
                  <tr>
                    <td><div class=maintext>&nbsp;<b>Created By</b>:</div></td>
                    <td><div class=maintext>
                     <?php 
                      if($Plate_ID){                      
                        $PlateOwner = get_userName($mainDB, $Plate->OwnerID);
                        echo $PlateOwner;
                      }else{
                        //new plate is created by current user
                        echo $_SESSION["USER"]->Fname. " ". $_SESSION["USER"]->Lname;
                      }
                     ?>
                    </div></td>
                  </tr>
                  <tr>
                    <td><div class=maintext>&nbsp;<b>Created</b>:</div></td>
                    <td><div class=maintext>
                     <?php echo ($Plate_ID)?"$Plate->DateTime":@date('Y-m-d');?></div></td>
                  </tr>
                  <tr>
                    <td><div class=maintext>&nbsp;<b>Digested By</b>:</div></td>
                    <td><div class=maintext>
                    <?php 
                      if(!$Plate_ID or $theaction == 'modifyplate'){
                        if($Plate->DigestedBy){
                          $DigestedBy =  $Plate->DigestedBy;
                        }else if($theaction == 'modifyplate'){
                          $DigestedBy = $_SESSION["USER"]->Fname. " ". $_SESSION["USER"]->Lname;
                        }
                        echo "<input type=text name=frm_DigestedBy value='".$DigestedBy."' size=17>";
                      } else{
                        echo $Plate->DigestedBy;
                      }
                      ?></div></td>
                  </tr>
                  <tr>
                    <td><div class=maintext>&nbsp;<b>Resusp. Buffer</b>:</div></td>
                    <td><div class=maintext valign=top>
                    <?php 
                      if(!$Plate_ID or $theaction == 'modifyplate'){
                        echo "<input type=text name=frm_Buffer value='".$Plate->Buffer."' size=17 amxlength=50>";
                      } else{
                        echo $Plate->Buffer;
                      }
                      ?></div></td>
                  </tr>
                  <tr>
                    <td><div class=maintext>&nbsp;<b>Digest Started</b>:</div></td>
                    <td nowrap><div class=maintext>
                      <?php 
                       if($Plate_ID and $theaction !='modifyplate'){
                         echo $Plate->DigestStarted;
                       }else{
                          echo "<input type=text name=frm_DigestStarted size=17 value='".$Plate->DigestStarted."' maxlength=50>";
                          echo "<a href=\"javascript: getToday(document.action_form.frm_DigestStarted);\" class=button><font size=1>now</font></a>";
                       }
                      ?></div></td>
                  </tr>
                  <tr>
                    <td><div class=maintext>&nbsp;<b>Digest Completed</b>:</div></td>
                    <td><div class=maintext>
                       <?php 
                       if($Plate_ID and $theaction !='modifyplate'){
                         echo $Plate->DigestCompleted;
                       }else{
                          echo "<input type=text name=frm_DigestCompleted size=17 value='". $Plate->DigestCompleted. "' maxlength=50>";
                          echo "<a href=\"javascript: getToday(document.action_form.frm_DigestCompleted);\" class=button><font size=1>now</font></a>";
                       }
                       ?></div></td>
                  </tr>
                  <tr>
                    <td nowrap width=30%><div class=maintext>&nbsp;<b>MS Completed</b>:</div></td>
                    <td width=70%><div class=maintext>
                     <?php  
                       if($Plate_ID and $theaction !='modifyplate'){
                         echo "<font color=red>".$Plate->MSDate."</font>";
                       }else if($theaction == 'modifyplate'){
                          echo "<input type=text name=frm_MSDate size=17 value='". $Plate->MSDate. "' maxlength=50>";
                          echo "<a href=\"javascript: getToday(document.action_form.frm_MSDate);\" class=button><font size=1>now</font></a>";
                       }
                       ?></div></td>
                  </tr>
                  <tr>
                    <td colspan="2"><div class=maintext>&nbsp;<b>Plate Notes</b>:</div></td>
                  </tr>
                  <tr>
                    <td colspan="2"><textarea name="frm_PlateNotes" cols="30" rows="3"><?php echo $Plate->PlateNotes;?></textarea></td>
                  </tr>
             <?php if($Plate_ID and $theaction=='modifyplate'){?>
                  <tr>
                    <td colspan="2" align=center>&nbsp;<input type="button" value='Update' class=black_but onClick="javascript: updatePlate();"></td>
                  </tr>
             <?php }?>
                </table>
              </td>
              <td width="" align=center>
                <?php include("plate_layout.inc.php");?>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  
