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

$expect_exclusion_color="#93ffff";
$bait_color="red";
$excludecolor = "#a7a7a7";

$theaction = 'exclusion'; 
$submitted = 0;
$whichPlate ='';
$img_total = 0;
$sub = '';
//$frm_Frequency = '';
$is_reincluded = '';

$typeBioArr = array();
$typeExpArr = array();
$typeFrequencyArr = '';
$frm_Expect_check = '';
$frm_Expect2_check = '';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");
require("analyst/site_header.php");
  

if(!$Plate_ID ) {
?>
  <script language=javascript>
    document.location.href='noaccess.html';
  </script>
<?php 
  exit;
}
$colorArr = array('C5CBF7','A7B2F6','E6B751','AC9A72','F6B2A9','DF9DF7','884D9E','798AF9','687CFA','AE15E7',
                  'D5CCCD','586EFA','8ED0F5','69B0D8','4C90B7','54F4F6','82ACAD','909595','A0F4B8','7BBC8D',
                  '43CB69','E9E86F','A9A850','ffff99','99ffff','99cc00','999900','ffccff','006600','6666ff',
                  '663399','0000ff','cc3300','0099ff','9999ff','99ccff','996600','cc99ff','ff3300','ff66ff',
                  'ff00ff','99ccff','996600','00ff00','990000','993333','99cc33','9999ff','ccccff','9933cc',
                  'ffffcc','ccffff','ccff99','ccff33','99ffcc','99ff00','ff00ff','6633ff','6633ff','6600ff',
                  'ffffff','66ffcc','ffcccc','66cccc','ff99cc','6699cc','ff66cc','6666cc','ff33cc','6633cc',
                  'ffff66','66ff66','ffcc66','66cc66','ff9966','669966','ff6666','666666','ff3366','663366',
                  '99ff33','00ff33','99cc33','00cc33','999933','009933','996633','006633','993333','003333',
                  '99ffcc','00ffcc','99cccc','00cccc','9999cc','0099cc','9966cc','0066cc','9933cc','0033cc');
              
$giArr = array();
$colorIndex = 0;

$outDir = "../TMP/plate_report/";
if(!_is_dir($outDir)) _mkdir_path($outDir);

$filename = $outDir.$_SESSION['USER']->Username."_plate.csv";
if (!$handle = fopen($filename, 'w')){
  echo "Cannot open file ($filename)";
  exit;
}

$oldDBName = to_defaultDB($mainDB);
$SQL = "SELECT FilterNameID FROM Filter WHERE FilterSetID=" . $_SESSION['workingFilterSetID'] . " ORDER BY FilterNameID";
$filterIDArr=$mainDB->fetchAll($SQL);
foreach($filterIDArr as $Value) {
  $SQL = "SELECT ID, Name, Alias, Color, Type, Init FROM FilterName WHERE ID=" . $Value['FilterNameID'];
  $filterAttrArr=$mainDB->fetch($SQL);
  if($filterAttrArr['Type'] == 'Fre'){
    $filterAttrArr['Counter'] = 0;
    $typeFrequencyArr = $filterAttrArr;
    if(!$theaction || !$submitted){
      $frm_Frequency = $filterAttrArr['Init'];
    }else{
      if(!isset($frm_Frequency)){
        $frm_Frequency = 0;
      }
    }    
  }else{
    $filterAttrArr['Counter'] = 0;  
    if($filterAttrArr['Type'] == 'Bio'){
      array_push($typeBioArr, $filterAttrArr);
    }else if($filterAttrArr['Type'] == 'Exp'){
      array_push($typeExpArr, $filterAttrArr);
    }
    $frmName = 'frm_' . $filterAttrArr['Alias'];
    if(!$theaction || !$submitted){
      $$frmName = $filterAttrArr['Init'];
    }else{
      if(!isset($$frmName)){
        $$frmName = "0";
      }
    }  
  }  
}
back_to_oldDB($mainDB, $oldDBName);

if(!isset($frequencyLimit)){
  $frequencyLimit = $_SESSION["workingProjectFrequency"];
  if(!$frequencyLimit) $frequencyLimit = 101;
}else{
  if($theaction != 'exclusion' || (isset($frm_Frequency) && !$frm_Frequency)){
    $frequencyLimit = $_SESSION["workingProjectFrequency"];
  }
}
if($theaction != 'exclusion'){
  $frm_Expect_check = '';
  $frm_Expect2_check = '';
}

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);

//-------------if move plate ------------------------------
if($whichPlate == 'last'){
  $Plate_ID = move_plate($mainDB, 'last');
}else if($whichPlate == 'first'){
  $Plate_ID = move_plate($mainDB, 'first'); 
}else if($whichPlate == 'next' and $Plate_ID){
  $Plate_ID = move_plate($mainDB, 'next',$Plate_ID);
}else if($whichPlate == 'previous' and $Plate_ID){
  $Plate_ID = move_plate($mainDB, 'previous', $Plate_ID);
}
//---------------------------------------------------------

$URL = getURL();

$SQL = "SELECT 
    ID, 
    Name,
    DigestedBy,
    Buffer, 
    MSDate 
    FROM Plate where  ID='$Plate_ID'";
$Plate = $mainDB->fetch($SQL);

//--------- color ---------------------
$bgcolordark = "#94c1c9";
$bgcolor = "white";
 
?>
<script language='javascript'> 
 function print_view(theTarget){
   document.plate_form.theaction.value = '<?php echo $theaction;?>';
   document.plate_form.action = theTarget
   document.plate_form.target = "_blank";
   document.plate_form.submit();
 } 
 function trimString(str) {
    var str = this != window? this : str;
    return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
 }
 function is_numberic(str){
   str = trimString(str);
   if(/^\d*\.?\d+$/.test(str)){
     return true;
   }else{
     return false;
   }
 }
 function applyExclusion(){
  theForm = document.plate_form;
<?php if($typeFrequencyArr){?>
  if(theForm.frm_Frequency.checked == true){
    var frequency = theForm.frequencyLimit.value;
    if(!is_numberic(frequency)){
      alert("Please enter numbers or uncheck check box on frequency field.");
      return;
    }else{
      if(frequency > 100 || frequency < 0){
        alert("frequence value should be great than 0 and less than 100");
        return;
      }
    }
  }else{
    theForm.frequencyLimit.value = '';
  }
<?php }?> 
  theForm.action = '';
  theForm.theaction.value = 'exclusion';
  theForm.action = '<?php echo $PHP_SELF;?>';
  theForm.target = "_self";
  theForm.submit();
 }
 function NoExclusion(){
  theForm = document.plate_form;
  theForm.theaction.value = '';
  theForm.action = '<?php echo $PHP_SELF;?>';
  theForm.target = "_self";
  theForm.submit();
 }
 
 function hitedit(Hit_ID){
  file = 'hit_editor.php?Hit_ID=' + Hit_ID;
  newwin = window.open(file,"Hit",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=480,height=400');
  
 }
 function change_plate(whichPlate){
  var theForm = document.plate_form;
  if(whichPlate == 'last') {
    theForm.whichPlate.value = 'last';
  } else if(whichPlate == 'first') {
    theForm.whichPlate.value = 'first';
  } else if(whichPlate == 'next') {
    theForm.whichPlate.value = 'next';
  } else if(whichPlate == 'previous') {
    theForm.whichPlate.value = 'previous';
  }
  theForm.theaction.value = 'exclusion';
  document.plate_form.action = '<?php echo $PHP_SELF;?>';
  theForm.submitted.value = 0;
  theForm.submit();
 }
 function pop_filter_set(filter_ID){   
   file = 'mng_set.php?filterID=' + filter_ID;
   window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=500,height=620');
 }
 function pop_Frequency_set(){   
   file = 'mng_set_frequency.php';
   window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=500,height=620');
 }
 
</script>
 
   <form name=plate_form action=<?php echo $PHP_SELF;?> method=post>
   <input type=hidden name=Plate_ID value='<?php echo $Plate['ID'];?>'>
   <input type=hidden name=theaction value='<?php echo $theaction;?>'>
   <input type=hidden name=submitted value='1'>
   <input type=hidden name=whichPlate value=''>

<table border="0" cellpadding="0" cellspacing="0" width="97%">
  <tr>
    <td colspan=2><div class=maintext>
      <img src="images/icon_carryover_color.gif"> Exclude Color
      <!--img src="images/icon_itisbait_color.gif"> Bait Color-->
      <img src="images/icon_Mascot.gif"> Mascot
      <img src="images/icon_GPM.gif"> GPM
      <img src="images/icon_notes.gif"> Hit Notes
      <img src="images/icon_first.gif" width="31" height="17" border="0"> Move Plate
      </div><BR>
    </td>
  </tr>
  <tr>
    <td align="left">
      <font color="navy" face="helvetica,arial,futura" size="3"><b>Plate Reported Hits
<?php 
if($AccessProjectName){
  echo "  <BR><font color='red' face='helvetica,arial,futura' size='3'>(Project: $AccessProjectName)</font>";
}
?>
      </b> 
      </font> 
    </td>
    <td align="right">
     <a href="./export_peptides.php?infileName=<?php echo $filename;?>&table=plate" class=button>[Export Peptides]</a> 
     <a href="<?php echo $filename;?>" class=button target=_blank>[Export Plate Report]</a>   
     <a href="javascript: print_view('plate_report_hit_list.php');" class=button>[Export Hit List]</a> 
     <a href="./plate_show.php" class=button>[Back to Plate List]</a> 
    </td>
  </tr>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="left" colspan=2><br>
      <table border=0 cellspacing="0" cellpadding="2" width="97%">
      <tr>
        <td valign=top>
          <table border=0 cellspacing="0" cellpadding="0">
          <tr>
            <td width=100><div class=large>Plate ID:</div></td>
            <td width=100><div class=large><b><?php echo $Plate['ID'];?></b> &nbsp;</div></td>
            <td width=100><div class=large>Name:</div></td>
            <td width=100><div class=large>&nbsp;<b><?php echo $Plate['Name'];?></b> &nbsp;</div></td>
            <td width=200><div class=large>
              <a href="javascript:change_plate('first');">
               <img src="images/icon_first.gif" width="31" height="18" border="0" alt='move to first'></a>&nbsp;
               <a href="javascript:change_plate('previous');">
               <img src="images/icon_previous.gif" width="30" height="18" border="0" alt='move to provious'></a>&nbsp;
               <a href="javascript:change_plate('next');">
               <img src="images/icon_next.gif" width="30" height="18" border="0" alt='move to next'></a>&nbsp;
                <a href="javascript:change_plate('last');">
               <img src="images/icon_last.gif" width="30" height="18" border="0" alt='move to last'></a>&nbsp; 
            </div></td>
            <td width=1><div class=large>&nbsp;</div></td>
          </tr>
<?php 
$Plate['Name'] = str_replace(",", ";", $Plate['Name']);
$Plate['DigestedBy'] = str_replace(",", ";", $Plate['DigestedBy']);
$Plate['Buffer'] = str_replace(",", ";", $Plate['Buffer']);
fwrite($handle, "Plate ID: ".$Plate['ID'].",Name: ".$Plate['Name']);
fwrite($handle, ",MS Complit Date: ".$Plate['MSDate'].",Digested By: ".$Plate['DigestedBy'].",Resusp. Buffer: ".$Plate['Buffer']."\n\n");
?>
          <tr>
            <td><div class=large>MS Complit Date:</div></td>
            <td><div class=large>&nbsp;<b><?php echo $Plate['MSDate'];?></b> &nbsp;</div></td>
            <td><div class=large>Digested By:</div></td>
            <td><div class=large>&nbsp;<b><?php echo $Plate['DigestedBy'];?></b> &nbsp;</div></td>
            <td><div class=large>Resusp. Buffer:</div></td>
            <td><div class=large>&nbsp;<b><?php echo $Plate['Buffer'];?></b> &nbsp;</div></td>
          </tr>
          </table>
        </td>
     </tr>
<?php 
include("filterSelection.inc.php");
?>
     
      </table>
    </td>
  </tr>
  <tr>
      <td colspan=2>&nbsp;
          <input type=button value='No Exclusion' class=black_but onClick='javascript: NoExclusion();'>
          <input type=button value='Apply Exclusion' class=black_but onClick='javascript: applyExclusion();'>
      </td>          
  </tr>
  <tr>
    <td align="center" colspan=2><br>
  <table border="0" cellpadding="0" cellspacing="1" width="100%">
  <tr bgcolor="">
    <td width="5" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
    <div class=tableheader>Well</div></td>
    <td width="50" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
    <div class=tableheader>Protein</div></td>
    <td width="50" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
    <div class=tableheader>Gene</div></td>
    <!--td width="50" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
    <div class=tableheader>LocusTag</div></td-->
    <?php if($frequencyLimit < 101){?>
    <td width="25" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
    <div class=tableheader>Frequency</div></td>
    <?php }?>
    <td width="25" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
    <div class=tableheader>Redundant</div></td>
    <td width="50" bgcolor="<?php echo $bgcolordark;?>" align=center> 
    <div class=tableheader>MW<BR>kDa</div></td> 
    <td width="600" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
     <div class=tableheader>Description</div></td>
    <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
     <div class=tableheader>Score</div></td>
    <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
     <div class=tableheader>Expect</div></td>
    <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
      <div class=tableheader># Uniqe<BR>Peptide</div></td> 
    <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center">
      <div class=tableheader>Links</div></td>
    <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center">
      <div class=tableheader>Filter</div></td>
    <td width="80" bgcolor="<?php echo $bgcolordark;?>" align="center">
      <div class=tableheader>Option</div></td>
  </tr>
<?php 
$filedNameStr = "HitID,Well,GI,GeneID,GeneName,LocusTag,";
if($frequencyLimit < 101){
  $filedNameStr .= "Frequency,";
}
$filedNameStr .= "Redundant,MW,Description,Score,Search Database,Search Date,Filters\n\n";
fwrite($handle, $filedNameStr);
$tmpCounter = 0;
//-------1/9-----------------------------------
$SQL = "SELECT BaitGeneID FROM BaitToHits 
        WHERE ProjectID ='".$_SESSION["workingProjectID"]."'         
        GROUP BY BaitGeneID";
$totalGenes = $mainDB->get_total($SQL);
//------------------------------------------
$tmpBait['ID'] = ''; 
$tmpBand['ID'] = ''; 

$SQL = "SELECT 
     H.ID, 
     H.WellID, 
     H.BaitID, 
     H.BandID, 
     H.Instrument, 
     H.GeneID,
     H.LocusTag, 
     H.HitGI, 
     H.HitName, 
     H.Expect,
     H.Expect2,
     H.MW,
     H.Pep_num_uniqe, 
     H.RedundantGI,
     H.ResultFile, 
     H.SearchDatabase,
     H.DateTime,
     H.OwnerID,
     H.SearchEngine,
     W.WellCode          
     FROM Hits H, PlateWell W ";
$SQL .= " WHERE H.WellID=W.ID and PlateID='".$Plate['ID']."' ";    
$SQL .= " and W.ProjectID=$AccessProjectID";    
$SQL .= ' ORDER By W.WellCode';

$sqlResult = mysqli_query($mainDB->link, $SQL);
$img_total = mysqli_num_rows($sqlResult);

while (list(
  $HitID, 
  $HitWellID, 
  $HitBaitID, 
  $HitBandID, 
  $HitInstrument,
  $HitGeneID,
  $HitLocusTag, 
  $HitGI, 
  $HitName, 
  $HitExpect,
  $HitExpect2,
  $HitMW,
  $HitPepNumUniqe, 
  $HitRedundantGI,
  $HitResultFile, 
  $HitSearchDatabase,
  $HitDateTime,
  $HitOwnerID,
  $HitSearchEngine,
  $HitWellCode  
  )= mysqli_fetch_row($sqlResult)){
   
  $tmpHitNotes = array();
  $HitGeneName = '';
  //--------------1/9-----------------------
  $HitFrequency = 0;
  if($HitGeneID)
  {
    $SQL = "SELECT GeneName, BioFilter FROM Protein_Class WHERE  EntrezGeneID=$HitGeneID";
    $ProteinArr = $proteinDB->fetch($SQL);
    if(count($ProteinArr) && $ProteinArr['GeneName']){
      $HitGeneName = $ProteinArr['GeneName'];
    }
    if(count($ProteinArr) && $ProteinArr['BioFilter']){
      $tmpHitNotes = explode(",", $ProteinArr['BioFilter']);
    }  
  //---add 1/9--------------------------------------------------------------------
    $SQL = "SELECT Value, FilterAlias FROM ExpFilter WHERE ProjectID ='".$_SESSION["workingProjectID"]."' AND GeneID=$HitGeneID";
    $ExpFilterArr = $mainDB->fetchAll($SQL);
    if($ExpFilterArr){
      for($n=0; $n<count($ExpFilterArr); $n++){
        if($ExpFilterArr[$n]['FilterAlias'] == 'FQ'){ 
          if($ExpFilterArr[$n]['Value'] && $totalGenes){
            $HitFrequency = round(($ExpFilterArr[$n]['Value'] / $totalGenes) * 100, 2);
          }  
        }else if($ExpFilterArr[$n]['FilterAlias']){
          array_push($tmpHitNotes, $ExpFilterArr[$n]['FilterAlias']);
        }
      }  
    }
  }
   
  $SQL = "SELECT FilterAlias FROM HitNote WHERE HitID=$HitID";
  $HitNoteArr = $mainDB->fetchAll($SQL);
  if($HitNoteArr){
    for($n=0; $n<count($HitNoteArr); $n++){
      if($HitNoteArr[$n]['FilterAlias']){
        array_push($tmpHitNotes, $HitNoteArr[$n]['FilterAlias']);
      }
    }
  }
  
  //---add 1/9-------------------------------------------------------------------
  if(is_one_peptide($HitID,$HITSDB,$HitPepNumUniqe)) array_push($tmpHitNotes, "OP");
  
  $SQL = "SELECT B.BandMW FROM Band B, PlateWell P WHERE B.ID=P.BandID AND P.ID=$HitWellID";
  $BandMWArr = $mainDB->fetch($SQL);
  $tmpNum = 0;
  if($BandMWArr && $BandMWArr['BandMW']){
    if($BandMWArr['BandMW'] > 0){
		  $tmpNum = abs(($BandMWArr['BandMW'] - $HitMW)*100/$BandMWArr['BandMW']);
    }
		if($BandMWArr['BandMW'] < 25 or $BandMWArr['BandMW'] > 100){
			if($tmpNum > 50){
				array_push($tmpHitNotes, "AW");
			}
    }else{
      if($tmpNum > 30 ){
				array_push($tmpHitNotes, "AW");
			}
    }   
  }//from checkCarryOver.inc.php=====================
  //---------------------------------------------------------------------------------
  
  $tmpbgcolor = $bgcolor;
  $tmptextfont = "maintext";
   
  if($HitBaitID != $tmpBait['ID']){
    $SQL = "SELECT 
      ID, 
      GeneID,
      LocusTag,
      GeneName,
      BaitMW       
      FROM Bait where  ID='$HitBaitID'";
    $tmpBait = $mainDB->fetch($SQL);;
  }
  
  if($tmpBait['GeneID'] && $HitGeneID && ($tmpBait['GeneID'] == $HitGeneID)){
    array_push($tmpHitNotes, "BT");
  }
    
  $rc_excluded = 0;
	if(!isset($frm_BT) || !$frm_BT and $tmpBait['GeneID'] == $HitGeneID){
		$rc_excluded = 0; 
	}else if($theaction == 'exclusion' && !in_array(ID_REINCLUDE, $tmpHitNotes)){
	  if(($frm_Expect_check && $HitExpect && $HitExpect <= $frm_Expect) || ($frm_Expect2_check && $HitExpect2 && $HitExpect2 >= $frm_Expect2) || (isset($frm_Frequency) && $frm_Frequency and $HitFrequency >= $frequencyLimit)){
      $rc_excluded=1;
    }
    if(count($tmpHitNotes) && !$rc_excluded){
      foreach($typeBioArr as $Value) {
     		$frmName = 'frm_' . $Value['Alias'];
    		if($$frmName and in_array($Value['Alias'] ,$tmpHitNotes)){
    			$rc_excluded=1; 
    			break;
    		}	
    	}	  	
		  if(!$rc_excluded){
        foreach($typeExpArr as $Value) {
     		  $frmName = 'frm_' . $Value['Alias'];
    		  if($$frmName and in_array($Value['Alias'] ,$tmpHitNotes)){
    			  $rc_excluded=1; 
    			  break;
          }  
    		}	
	    }	   
      if(in_array(ID_MANUALEXCLUSION, $tmpHitNotes) && !$rc_excluded) $rc_excluded=1; 
    }
  }  
     
  if(!$rc_excluded and $theaction != 'exclusion' && !in_array(ID_REINCLUDE,$tmpHitNotes)){
    $isTrue = 0;    
  	foreach($typeBioArr as $Value){     
  		//if(in_array($Value['Alias'] ,$tmpHitNotes) && $Value['Alias'] != "HP"){
      if(in_array($Value['Alias'] ,$tmpHitNotes)){
  			$isTrue = 1; 
  			break;
  		}	
  	}
    if(!$isTrue){
      foreach($typeExpArr as $Value) {
    		if(in_array($Value['Alias'], $tmpHitNotes)){
    			$isTrue = 1; 
    			break;
    		}	
    	}		
    }	 
    if(!$isTrue){
 	    if($HitFrequency >= $frequencyLimit || ($HitExpect and $HitExpect <= DEFAULT_EXPECT_EXCLUSION )){
		    $isTrue = 1;
	    }
    }
    if($isTrue){
      $tmpbgcolor = $excludecolor; 
	    $tmptextfont = "excludetext";
    }
  }
   
  if(!$rc_excluded){
    $tmpCounter++;
    if($HitBandID != $tmpBand['ID']) {
      $SQL = "SELECT 
        ID,
        BandMW,
        Location
        FROM Band WHERE ID='$HitBandID' and ProjectID=$AccessProjectID";
      $tmpBand = $mainDB->fetch($SQL);           
    ?>
     <tr bgcolor="">
      <td colspan=14><div class=maintext_color><hr>
      <?php 
      echo "Band ID: <b>".$tmpBand['ID']."</b>   
              &nbsp; Observed MW: <b>".$tmpBand['BandMW']."</b> kDa   
              &nbsp; Band Code: <b>".$tmpBand['Location']."</b><br>
              Bait ID: <b>".$tmpBait['ID']."</b> 
              &nbsp: Bait Gene: <b>".$tmpBait['GeneID']."/".$tmpBait['GeneName']."</b>
              &nbsp: Bait LocusTag: <b>".$tmpBait['LocusTag']."</b>              
              &nbsp: Bait MW: <b>".$tmpBait['BaitMW']."</b>";
      ?></div>
      </td>
     </tr>
      <?php 
      $tmpBait['GeneName'] = str_replace(",", ";", $tmpBait['GeneName']);
      $tmpBait['LocusTag'] = str_replace(",", ";", $tmpBait['LocusTag']);
      
      fwrite($handle, "\n\nBand ID: ".$tmpBand['ID'].",Observed MW: ".$tmpBand['BandMW'].",Band Code: ".$tmpBand['Location']."\n");
      fwrite($handle, "Bait ID: ".$tmpBait['ID'].",Bait Gene: ".$tmpBait['GeneID'].",Bait LocusTag: ".$tmpBait['LocusTag'].",Bait MW: ".$tmpBait['BaitMW']."\n"); 
    }
    if($HitFrequency){
      $HitFrequencyPercent = $HitFrequency."%";
    }else{
      $HitFrequencyPercent = "0%";
    }
    $fileRedundantGI = str_replace("gi","<br>gi", $HitRedundantGI);
    $Description = str_replace(",", ";", $HitName);
    $Description = str_replace("\n", "", $Description);
    $HitWellCode = str_replace(",", ";", $HitWellCode);
    $HitGeneName = str_replace(",", ";", $HitGeneName);
    $HitLocusTag = str_replace(",", ";", $HitLocusTag);
    $HitExpect = str_replace(",", ";", $HitExpect);
    $HitSearchDatabase = str_replace(",", ";", $HitSearchDatabase);
    $HitGI = str_replace(",", ";", $HitGI);
    
    fwrite($handle, $HitID.",".$HitWellCode.",".$HitGI.",".$HitGeneID.",".$HitGeneName.",".$HitLocusTag.",");
    if($frequencyLimit < 101){
      fwrite($handle, $HitFrequencyPercent.",");
    }
    fwrite($handle, $HitRedundantGI.",".$HitMW.",".$Description.",".$HitExpect.",".$HitSearchDatabase.",".$HitDateTime.",");
    $filterString = '';
        
?>
  <tr  bgcolor='<?php echo $tmpbgcolor;?>' onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $tmpbgcolor;?>');">
    <td width="" align="center" bgcolor=<?php 
    if($HitGeneID && ($tmpBait['GeneID'] == $HitGeneID)){
  	  echo "'$bait_color'";      
      $counter = count($typeBioArr);
      for($m=0; $m<$counter; $m++){
        if($typeBioArr[$m]['Alias'] == ID_BAIT){ 
    			$typeBioArr[$m]['Counter']++;
          break;
    		}
      }
  	}else{
  	  echo "'$tmpbgcolor'";
  	}
	 ?>><div class=<?php echo $tmptextfont;?>>
        <?php echo $HitWellCode;?>&nbsp;
      </div>
    </td>
    <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
        <?php echo  $HitGI ;?>&nbsp;
        </div>
    </td>
    <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
        <?php 
        if($HitGeneID || $HitGeneName){
          echo  $HitGeneID." / ".$HitGeneName;
        }
        ?>&nbsp;        
        </div>
    </td>
    <!--td width="" align="center"><div class=maintext>
        <?php echo  $HitLocusTag ;?>&nbsp;
        </div>
    </td-->
    <?php if($frequencyLimit < 101){?>
    <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
        <?php echo ($HitFrequency)?$HitFrequency."%":'0%';?>&nbsp;
        </div>
    </td>
    <?php }?>
    <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
        <?php echo str_replace("gi","<br>gi", $HitRedundantGI);?>&nbsp;
        </div>
    </td>
    <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
        <?php echo  $HitMW ;?>&nbsp;
        </div>
    </td>
    
    <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
        <?php echo $HitName;?>&nbsp;
      </div>
    </td>
    <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
        <?php  echo $HitExpect;
        //echo expectFormat($Hits->Expect[$i]);
        ?>&nbsp;
      </div>
    </td>
    <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
        <?php  echo $HitExpect2;
        //echo expectFormat($Hits->Expect[$i]);
        ?>&nbsp;
      </div>
    </td>
    <td width="" align="right" ><div class=<?php echo $tmptextfont;?>>
        <?php echo $HitPepNumUniqe;?>&nbsp;</div>
    </td>
    <td width="" align="center" nowrap><div class=<?php echo $tmptextfont;?>>
    <?php 
    $urlLocusTag = $HitLocusTag;
    $urlGeneID = $HitGeneID;
    $urlGI = $HitGI;
     
    echo get_URL_str($HitGI, $HitGeneID, $HitLocusTag);
    ?>
    </td>
    <td>
      <table border=0 cellpadding="1" cellspacing="1"><tr>
    <?php 
    $counter = count($typeBioArr);    
    for($m=0; $m<$counter; $m++){
      if(($typeBioArr[$m]['Alias'] != ID_BAIT) && in_array($typeBioArr[$m]['Alias'] ,$tmpHitNotes)){		
  			$tmp_color = $typeBioArr[$m]['Color']; 
  			echo "<td bgcolor=$tmp_color nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
        ($filterString)? $filterString.=";".$typeBioArr[$m]['Name'] : $filterString.=$typeBioArr[$m]['Name'];
  			$typeBioArr[$m]['Counter']++;   
  		}
    }
    
    $counter = count($typeExpArr);    
    for($m=0; $m<$counter; $m++){
      if(($typeExpArr[$m]['Alias'] != ID_BAIT) && in_array($typeExpArr[$m]['Alias'] ,$tmpHitNotes)){		
  			$tmp_color = $typeExpArr[$m]['Color']; 
  			echo "<td bgcolor=$tmp_color nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
        ($filterString)? $filterString.=";".$typeExpArr[$m]['Name'] : $filterString.=$typeExpArr[$m]['Name'];
  			$typeExpArr[$m]['Counter']++;
  		}
    }
	  
    if($typeFrequencyArr && $HitFrequency >= $frequencyLimit and !$is_reincluded){
      $typeFrequencyArr['Counter']++;
       echo "<td bgcolor='" . $typeFrequencyArr['Color'] . "' nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
       ($filterString)? $filterString.=";frequence>=$frequencyLimit" : $filterString.="frequence>=$frequencyLimit";
    }
    if(($HitExpect and $HitExpect <= DEFAULT_EXPECT_EXCLUSION) and !$theaction and !$is_reincluded){
       echo "<td bgcolor='$expect_exclusion_color' nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
       ($filterString)? $filterString.=";expect<=".DEFAULT_EXPECT_EXCLUSION : $filterString.="expect<=".DEFAULT_EXPECT_EXCLUSION;
    }	  
    if(in_array(ID_REINCLUDE,$tmpHitNotes)) {  //reinclude
        echo "<td bgcolor=#660000><font face='Arial' color=white size=-1><b>R</b></font></td>";
        ($filterString)? $filterString.=";reinclude" : $filterString.="reinclude";
    }
    if(in_array(ID_MANUALEXCLUSION,$tmpHitNotes)) {  //manual exclude
        echo "<td bgcolor=black><font face='Arial' color=yellow size=-1><b>X</b></font></td>";
        ($filterString)? $filterString.=";manualexclusion" : $filterString.="manualexclusion"; 
    } 
     fwrite($handle, $filterString."\n");    
    ?>
      </tr></table>
    </td>
    <td width="" align="left" nowrap><div class=maintext>&nbsp;
     <?php if($HitSearchEngine=='Mascot' or $HitSearchEngine=='GPM'){?>
      <a href="javascript:view_peptides(<?php echo $HitID;?>);"><img border="0" src="./images/icon_<?php echo $HitSearchEngine;?>.gif" alt="Peptides"></a>
      <a href="javascript:view_master_results('<?php echo  $HitResultFile;?>','<?php echo  $HitSearchEngine;?>');"><img border="0" src="./images/icon_<?php echo $HitSearchEngine;?>2.gif" alt="Peptides"></a>
      <?php }?>
      <a href="javascript: add_notes('<?php echo $HitID;?>');"><img src="./images/icon_notes.gif" border=0 alt="Hit Notes"></a> 
      <?php if($AUTH->Modify and ($HitOwnerID == $AccessUserID || $SuperUsers) and 0){?>
      <a href="javascript: hitedit('<?php echo $HitID;?>');"><img src="./images/icon_view.gif" border=0 alt="modify hit"></a>
      <?php }
      $coip_color_and_ID = array('color'=>'', 'ID'=> '');
      $coip_color_and_ID = get_coip_color($mainDB, $tmpBait['GeneID'], $HitGeneID);
      if($coip_color_and_ID && $coip_color_and_ID['ID'] && $coip_color_and_ID['color']){
        echo "<a href='./coip.php?theaction=modify&Coip_ID=".$coip_color_and_ID['ID'] . "' target=new>";
        echo "<img src=\"./images/icon_coip_".$coip_color_and_ID['color'].".gif\" border=0 alt='co-ip detail'>";
        echo "</a>";
      }
      ?>
    </div>
    </td>
  </tr>
<?php 
   }//end if re_excluded
} //end for
//echo $tmpCounter;

$currentSetArr = array();
foreach($typeBioArr as $Value){
  array_push($currentSetArr, $Value);
}
foreach($typeExpArr as $Value){
  array_push($currentSetArr, $Value);
}
if($typeFrequencyArr && $frequencyLimit < 101){
  array_push($currentSetArr, $typeFrequencyArr);
}
?>
   </table>
     
    </td>
  </tr>
</table>
</form>
<?php 
if($currentSetArr){
  $_SESSION["currentSetArr"] = $currentSetArr;
?>
    <script language=javascript>
    document['reportgif'].src = 'bait_report_gif.inc.php?total=<?php echo $img_total?>';
    </script>
<?php 
}  
require("site_footer.php");
//**************************************
// this function will return 10 base power 
// string value for displaying by passing
// a float value.
function expectFormat($Value){
  $rt='';
  if($Value == 0) return "0";
  $decimals = log10($Value);
  $tmp_int = intval( $decimals );
  if($decimals < 0){
    $tmpPow = $decimals + abs($tmp_int) + 1;
    $rt = sprintf("%0.1f", pow(10,$tmpPow));
    $tmp_int = $tmp_int -1;
    $rt .= "?0<sup>$tmp_int</sup>";
  }else if($decimals > 1){
    $tmpPow = $decimals - $tmp_int;
    $rt = sprintf("%0.1f", pow(10,$tmpPow));
    $rt .= "?0<sup>$tmp_int</sup>";
  }else{
    $rt = sprintf("%0.1f", $Value);
  }
  return $rt;
}

function move_plate($DB, $whichPlate,$Plate_ID = 0){  
   $re = $Plate_ID;
   if($whichPlate == 'last'){
     $SQL = "select PlateID from PlateWell group by PlateID order by PlateID desc limit 1";
   }elseif($whichPlate == 'first'){
     $SQL = "select PlateID from PlateWell where PlateID > 0 group by PlateID order by PlateID limit 1";
   }elseif($whichPlate == 'next' and $Plate_ID){
     $SQL = "select PlateID from PlateWell WHERE PlateID > $Plate_ID group by PlateID order by PlateID limit 1";
   }elseif($whichPlate == 'previous' and $Plate_ID){
     $SQL = "select PlateID from PlateWell WHERE PlateID < $Plate_ID group by PlateID order by PlateID desc limit 1";
   }
   //echo $SQL;
   $row = mysqli_fetch_array(mysqli_query($DB->link, $SQL));
   if($row[0]) $re = $row[0];
   return $re;
}
?>
