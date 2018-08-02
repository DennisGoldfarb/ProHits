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

require("classes/bait_class.php");
$Baits = new Bait();

?>
<script language="javascript">
function isNumber(str) {
  for(var position=0; position<str.length; position++){
        var chr = str.charAt(position)
        if ( ( (chr < "0") || (chr > "9") ) && chr != ".")
              return false;
  }
  return true;
}

function checkform(theForm){
  var switch_value = '';
  for(var i=0; i<theForm.bait_switch.length; i++){
    if(theForm.bait_switch[i].checked == true){
      switch_value = theForm.bait_switch[i].value;
    }
  }
  if(theForm.name == 'add_form'){
    theForm.theaction.value = 'insert';
  }else if(theForm.name == 'modify_form'){
    theForm.theaction.value = 'update';
  }    
  if(theForm.frm_Clone.value != "dummy"){
    var frm_GeneID = theForm.frm_GeneID.value;
    var frm_LocusTag = theForm.frm_LocusTag.value;
    var frm_GeneName = theForm.frm_GeneName.value;
    var frm_TaxID = theForm.frm_TaxID.value;
    var frm_BaitMW = theForm.frm_BaitMW.value;
    var frm_BaitAcc = theForm.frm_BaitAcc.value;
    var frm_AccType = theForm.frm_AccType.value;
    var frm_Clone = theForm.frm_Clone.value;
    var frm_Description = theForm.frm_Description.value;
    var selectedProjects = '<?php echo $AccessProjectID;?>';
    if(switch_value == 'new_bait'){  
      if((isEmptyStr(frm_LocusTag) && isEmptyStr(frm_GeneName)) || isEmptyStr(frm_BaitAcc) || isEmptyStr(frm_AccType) || isEmptyStr(frm_Description) || frm_TaxID == ''){
        alert("Bold field names are requiered to make the insert.");
        return false;
      }else if(!isEmptyStr(frm_GeneID) && !isNumber(frm_GeneID)){
        alert("frm_GeneID has to be a number!");
         return false;
      }else if(!isNumber(frm_BaitMW)){
         alert("Bait MW has to be a number!");
         return false;
      }else{
        theForm.submit();
      }  
    }else{
      if(isEmptyStr(frm_GeneName)){
        alert("Bold field names are required to make the insert.");
        return false;
      }else{
        theForm.submit();
      }  
    }  
  }else{
    theForm.submit();
  }  
}
function goexperiment(theForm,passAc,expid){
  theForm.theaction.value=passAc;
  theForm.Exp_ID.value = expid;
  theForm.addNewType.value = 'Exp';
  theForm.submit();
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
function subNextStep(Bait_ID,Exp_ID){
  var theForm = document.del_form;
  theForm.Bait_ID.value = Bait_ID;
  if(Exp_ID != ''){
    theForm.Exp_ID.value = Exp_ID;
    theForm.theaction.value = 'modify';
  }else{
    theForm.theaction.value = 'addnew';
  }  
  theForm.action = 'experiment.php';
  theForm.submit();
} 
</script>
<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>  
  <tr>
    <td align="center" colspan=2> 
<?php 
if(($theaction == "addnew" OR $theaction == "insert" )){// and $AUTH->Insert){
    
  $frm_LocusTag = trim($frm_LocusTag);
  $frm_GeneName = preg_replace("/[^A-Za-z0-9_.-]/",'',$frm_GeneName);
  //=======================================================================
  if($theaction == "insert"){
    $isInsertFlag = 0;
    if($sub == "2" || $sub == "4"){
      $isInsertFlag = 1;
    }else if(($frm_LocusTag Or $frm_GeneName) and $frm_Description){
      $isInsertFlag = 2;
    }else{
      echo "<center><font color='red' face='helvetica,arial,futura' size=2>";
      echo "Missing info. <b>Bold</b> field names are required to make the insert.";
      echo "</font></center>";
    }
    if($isInsertFlag){
      $Baits->insert(
         $frm_GeneID,
         $frm_LocusTag, 
         $frm_GeneName,
         $frm_BaitAcc,
         $frm_AccType, 
         $frm_TaxID, 
         $frm_BaitMW, 
         $frm_Family, 
         $frm_Tag, 
         $frm_Mutation,
         $frm_Clone, 
         $frm_Vector, 
         $frm_Description, 
         $AccessUserID,
         $AccessProjectID,
         $gelMode);
       echo "<center><font color='red' face='helvetica,arial,futura' size=2>";
       echo "Insert complete.";
       echo "</font></center>";
       
       //$Desc = "LocusTag=$frm_LocusTag,GeneName=$frm_GeneName,Clone=$frm_Clone";
       $Desc = "GeneID=$frm_GeneID,AccType=$frm_AccType,BaitMW=$frm_BaitMW,Clone=$frm_Clone"; 
      
      $Log->insert($AccessUserID,'Bait',$Baits->ID,'insert',$Desc,$AccessProjectID);
      $theaction = "modify";
      $Bait_ID = $Baits->ID;
    }
  }else{
    if($virtual_Tag){
      $frm_Tag = $virtual_Tag;
      $virtual_Tag = '';
    }
    
?>    <br>
      <table border="0" cellpadding="0" cellspacing="0" width="650">
      <form name='add_form' method=post action=<?php echo $PHP_SELF;?>>
      <input type=hidden name=theaction value="<?php echo $theaction;?>">
      <input type=hidden name=ProjectID value="<?php echo $ProjectID?>">    
      <input type=hidden name=gelMode value="<?php echo $gelMode?>">
      <input type=hidden name=addNewType value="<?php echo $addNewType;?>">    
      <input type=hidden name=Bait_ID value="<?php echo $Bait_ID;?>">
      <input type=hidden name=sub value=<?php echo $sub;?>>
      <input type=hidden name=Gel_ID value=<?php echo $Gel_ID;?>>
      <input type=hidden name=DBname value=<?php echo $DBname;?>>
      <input type=hidden name=virtual_Tag value=<?php echo $virtual_Tag;?>>
      <!--table border="0" cellpadding="0" cellspacing="1" width="500"-->      
        <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
           <td colspan="2" align="center" height="20">
<?php 
    if($sub == "2" || $sub == "4"){
      $baitTitlle = "New dummy bait";
    }else{
      $baitTitlle = "New bait";
    }
?>        
            <div class=tableheader>
            <?php echo $baitTitlle;?><input type='radio' id='switch_1' name='bait_switch' value='new_bait' <?php echo ($bait_switch=='new_bait')?'checked':''?> onclick="switch_bait_type(this)">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            No bait or a control<input type='radio' id='switch_2' name='bait_switch' value='no_bait' <?php echo ($bait_switch=='no_bait')?'checked':''?> onclick="switch_bait_type(this)">
            </div>
          </td>
        </tr>
  <?php  
    //---------------------
    include("bait.inc.php"); 
    //---------------------
  ?>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" align="center">
    <td colspan="2"><input type="button" value="Save" class=green_but onClick="javascript: checkform(add_form);"></td>
  </tr>      
  </table>
  </form>
  <br>
<?php 
  }//end of insert
}
if(($theaction == "modify" or $theaction == "update") and $Bait_ID ){
  
  $frm_LocusTag = trim($frm_LocusTag);
  $frm_GeneName = trim($frm_GeneName);  
  if($theaction == "update"){    
    $isUpdateFlag = 0;
    if($frm_Clone == "dummy"){
      $isUpdateFlag = 1;
    }else if(($frm_LocusTag Or $frm_GeneName) and $frm_Description){          
      $isUpdateFlag = 2;
    }else{
      echo "<center><font color='red' face='helvetica,arial,futura' size=2>";
      echo "Missing info. <b>Bold</b> field names are required to make the insert.";
      echo "</font></center><br>";
    }    
    
    if($isUpdateFlag){
      $Baits->update(
         $Bait_ID,
         $frm_GeneID,
         $frm_LocusTag, 
         $frm_GeneName,
         $frm_BaitAcc,
         $frm_AccType, 
         $frm_TaxID, 
         $frm_BaitMW, 
         $frm_Family, 
         $frm_Tag, 
         $frm_Mutation,
         $frm_Clone, 
         $frm_Vector, 
         $ProjectID,
         $frm_Description);
    } 
    if($isUpdateFlag){
      $Desc = "GeneID=$frm_GeneID,AccType=$frm_AccType,BaitMW=$frm_BaitMW,Clone=$frm_Clone"; 
      
      $Log->insert($AccessUserID,'Bait',$Bait_ID,'modify',$Desc,$ProjectID);
      echo "<center><font color='red' face='helvetica,arial,futura' size=2>";
      echo "Update complete.";
      echo "</font></center>";
    }  
    //end of Log table
    $theaction = "modify";   
  }
  echo "<br>";
  if($theaction == "modify"){
    $Baits->fetch($Bait_ID);
    $frm_GeneID = $Baits->GeneID;
    $frm_LocusTag = $Baits->LocusTag;
    $frm_GeneName = $Baits->GeneName;
    $frm_BaitAcc=$Baits->BaitAcc;
    $frm_AccType=$Baits->AccType;
    $frm_TaxID = $Baits->TaxID;
    $frm_BaitMW = $Baits->BaitMW;
    $frm_Family = $Baits->Family;
    $frm_Tag = $Baits->Tag;
    $frm_Mutation = $Baits->Mutation;
    $frm_Clone = $Baits->Clone;
    $frm_Vector = $Baits->Vector;    
    $frm_Description = $Baits->Description;
    $frm_OwnerID = $Baits->OwnerID;
    $frm_DateTime = $Baits->DateTime; 
    
    if($virtual_Tag){
      $frm_Tag = $virtual_Tag;
      $virtual_Tag = '';
    }
?>    
      <table border="0" cellpadding="0" cellspacing="0" width="650">
      <form name='modify_form' method=post action='<?php echo $PHP_SELF;?>'>
      <input type=hidden name=theaction value='<?php echo $theaction;?>'>
      <input type=hidden name=ProjectID value="<?php echo $ProjectID?>">    
      <input type=hidden name=gelMode value="<?php echo $gelMode?>">
      <input type=hidden name=addNewType value="<?php echo $addNewType;?>"> 
      <input type=hidden name=sub value=<?php echo $sub;?>>
      <input type=hidden name=DBname value=<?php echo $DBname;?>>
      <input type=hidden name=Gel_ID value='<?php echo $Gel_ID;?>'>
      <input type=hidden name=Exp_ID value=''>
      <input type=hidden name='bait_switch' value='<?php echo $bait_switch?>'>
      <input type=hidden name=virtual_Tag value=<?php echo $virtual_Tag;?>>
      <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
    <td colspan="2" align="center" height="20">
 <?php 
  if($sub == "2" || $sub == "4" || $frm_GeneName == "dummy"){
    $baitTitlle = "Modify Dummy";
  }else{
    $baitTitlle = "Modify";
  }
?>        
      <div class=tableheader><?php echo $baitTitlle;?></div>
    </td>
  </tr>
  
  <?php 
  //-----------------------------------
  include("bait.inc.php");
  //-----------------------------------
  ?>
   <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" align="center">
    <td colspan="2">
    <input type="button" value=" Close Window " class="green_but" onClick="javascript: window.close();">
  <?php if($AUTH->Modify ){ ?>
    <input type="button" value="Modify" class=green_but onClick="javascript: checkform(modify_form);">
  <?php }?> 
  &nbsp;
  <?php if($sub){
      $tmpAct = "addnew";
      $tmpExpID = '';
      if($Baits->GelFree == 1){
        $SQL ="SELECT `ID`FROM `Experiment` WHERE `BaitID` = '$Bait_ID'";
        $tmpExpArr = $HITSDB->fetch($SQL);
        if($tmpExpArr){
          $tmpAct = "modify";
          $tmpExpID = $tmpExpArr['ID'];
        }
      }  
  ?>
      <!--a href="experiment.php?sub=1&Bait_ID=<?php echo $Bait_ID;?>"> 
      <img src="./images/arrow_small.gif" border=0></a-->
         <input type="button" value=" Next " class=green_but onClick="javascript: goexperiment(modify_form,'<?php echo $tmpAct?>','<?php echo $tmpExpID?>');">
  <?php }?>
    </td>
  </tr>      
  </table><br>
  </form>
<?php 
  }
} //end if
?>
    </td>
  </tr>
</table>