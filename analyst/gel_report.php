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

$frm_Frequence = '';
$frm_Expect = '';
$expect_exclusion_color="#93ffff";
$bait_color="red";
$excludecolor = "#a7a7a7";
$is_reincluded = '';
$frm_Expect_check = '';
$frm_Expect2_check = '';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php"); 
require("analyst/site_header.php");

if(!$Gel_ID){
?>
  <script language=javascript>
    document.location.href='noaccess.html';
  </script>
<?php 
  exit;
}

$typeBioArr = array();
$typeExpArr = array();
$typeFrequencyArr = '';

$oldDBName = to_defaultDB($mainDB);
$SQL = "SELECT FilterNameID FROM Filter WHERE FilterSetID=" . $_SESSION['workingFilterSetID'] . " ORDER BY FilterNameID";
$filterIDArr=$mainDB->fetchAll($SQL);
foreach($filterIDArr as $Value) {
  $SQL = "SELECT ID, Name, Alias, Color, Type, Init FROM FilterName WHERE ID=" . $Value['FilterNameID'];
  $filterAttrArr=$mainDB->fetch($SQL);
  if($filterAttrArr['Type'] == 'Fre'){
    $filterAttrArr['Counter'] = 0;
    $typeFrequencyArr = $filterAttrArr;
    if($theaction != 'exclusion'){
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
$SQL = "SELECT 
          ID, 
          Name, 
          Image, 
          Stain, 
          Notes,
		  		GelType, 
          OwnerID, 
          ProjectID, 
          DateTime
          FROM Gel where  ID='$Gel_ID'";
       //echo $SQL; exit;   
list(
  $GelID,
  $GelName,
  $GelImage,
  $GelStain,
  $GelNotes,
  $GelGelType,
  $GelOwnerID,
  $GelProjectID,
  $GelDateTime) = mysqli_fetch_array(mysqli_query($mainDB->link, $SQL));

//--------- color ---------------------
$bgcolordark = "#94c1c9";
$bgcolor = "white";

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

$outDir = "../TMP/gel_report/";
if(!_is_dir($outDir)) _mkdir_path($outDir);
$filename = $outDir.$_SESSION['USER']->Username."_gel.csv";
if (!$handle = fopen($filename, 'w')){
  echo "Cannot open file ($filename) to write. Please make apache user write permission for the folder.";
  exit;
}

?>
<script language='javascript'>
 function applyExclusion(){
  document.plate_form.theaction.value = 'exclusion';
  document.plate_form.action = '<?php echo $PHP_SELF;?>';
  document.plate_form.target = "_self";
  document.plate_form.submit(); }
 
 function hitedit(Hit_ID){
  file = 'hit_editor.php?Hit_ID=' + Hit_ID;
  newwin = window.open(file,"Hit",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=480,height=400');
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
  if(typeof(theForm.frm_Frequency) != 'undefined'){
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
  }  
  theForm.theaction.value = 'exclusion';
  theForm.action = '<?php echo $_SERVER['PHP_SELF'];?>';
  theForm.target = "_self";
  theForm.submit();
 }
 function NoExclusion(){
  theForm = document.plate_form;
  theForm.theaction.value = '';
  theForm.action = '<?php echo $_SERVER['PHP_SELF'];?>';
  theForm.target = "_self";
  theForm.submit();
 }
</script> 

   <form name=plate_form action=<?php echo $PHP_SELF;?> method=post>   
   <input type=hidden name=Gel_ID value='<?php echo $Gel_ID;?>'>
   <input type=hidden name=theaction value='<?php echo $theaction;?>'>
      
<table border="0" cellpadding="0" cellspacing="0" width="97%">
  <tr>
    <td colspan=2><div class=maintext>
      <img src="images/icon_carryover_color.gif"> Exclude Color
      <!--img src="images/icon_itisbait_color.gif"> Bait Color-->
      <img src="images/icon_Mascot.gif"> Mascot
      <img src="images/icon_GPM.gif"> GPM
      <img src="images/icon_notes.gif"> Hit Notes
      <!--img src="images/icon_first.gif" width="31" height="17" border="0"> Move Plate-->
      </div><br>
    </td>
  </tr>
  <tr>
    <td align="left">
    <font color="navy" face="helvetica,arial,futura" size="3"><b>Gel Reported Hits
    <?php 
    if($AccessProjectName){
      echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project: $AccessProjectName)</font>";
    }
    ?>
    </b> 
    </font> 
    </td>
    <td align="right"> 
     <a href="<?php echo $filename;?>" class=button target=_blank>[Export Gel Report]</a> 
     &nbsp;
     <a href="./gel.php" class=button>[Back to Gel List]</a> 
    
    </td>
  </tr>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="center" colspan=2><br>
      <table border=0 cellspacing="0" cellpadding="2" width="740">
      <tr>
        <td valign=top align=center>
        <img src="./gel_images/<?php echo $GelImage;?>" border=0>
        </td>  
      </tr>
      </table>
    </td>
  </tr>
  <tr>     
    <td align="center" colspan=2><br>
      <table border=0 cellspacing="0" cellpadding="2" width="97%">
      <tr>
        <td valign=top>
          <table border=0 cellspacing="0" cellpadding="0" >
          <tr>
            <td width=100><div class=large>Gel ID:</div></td>
            <td width=100><div class=large><b><?php echo $Gel_ID;?></b> &nbsp;</div></td>
            <td width=100><div class=large>Name:</div></td>
            <td width=100><div class=large><b><?php echo $GelName;?></b> &nbsp;</div></td>            
            <td width=100>&nbsp;</td>
            <td width=100>&nbsp;</td>
            <td width=100>&nbsp;</td>
          </tr>
<?php 
$GelOwner = get_userName($mainDB, $GelOwnerID);
$GelName = str_replace(",", ";", $GelName);
$GelGelType = str_replace(",", ";", $GelGelType);
$GelOwner = str_replace(",", ";", $GelOwner);

fwrite($handle, "Gel ID: ".$Gel_ID.",Name: ".$GelName);
fwrite($handle, ",Gel Type: ".$GelGelType.",Uploaded By :".$GelOwner.",Created On :$GelDateTime\n\n");
$filedNameStr = "Well,Gene,Hit ID,Hit GI,Hit LocusTag,";
if($frequencyLimit < 101){
  $filedNameStr .= "Frequency,";
}
$filedNameStr .= "Band MW,Hit MW,Hit Description,Score,SearchDatabase,SearchDate,Filters, SearchResultFile\n\n";
fwrite($handle, $filedNameStr);
?>
          <tr>
            <td><div class=large>Gel Type:</div></td>
            <td><div class=large>&nbsp;<b><?php echo $GelGelType;?></b> &nbsp;</div></td>
            <td><div class=large>Uploaded By:</div></td>
            <td><div class=large>&nbsp;<b><?php echo $GelOwner;?>&nbsp;</div></td>            
            <td><div class=large>Created On:</div></td>
            <td><div class=large>&nbsp;<b><?php echo $GelDateTime;?></b> &nbsp;</div></td> 
            <td width=1>&nbsp;</td>           
          </tr>
          </table><br>
        </td>
      </tr>
      <tr>
        <td valign=top>
          <table border=0 cellspacing="4" cellpadding="0" width=650>  
          <tr>
                <?php if($typeFrequencyArr && $frequencyLimit <= 100){?>
                <td bgcolor=<?php echo $typeFrequencyArr['Color']?>><div class=maintext nowrap>&nbsp;
                <input type=checkbox name='frm_Frequency' value='1' <?php echo ($frm_Frequency)?"checked":"";?>>
                <a href="javascript: pop_Frequency_set();"><?php echo $typeFrequencyArr['Name']?></a>>
                <input type=text name='frequencyLimit' value="<?php echo $frequencyLimit?>" size=2>%
                 </div>
                </td>
                <?php }?>
                <td bgcolor=<?php echo $expect_exclusion_color;?>><div class=maintext nowrap>&nbsp;
                <input type=checkbox name='frm_Expect_check' value='1' <?php echo ($frm_Expect_check)?"checked":"";?>>
                    Score < 
    	          <select name='frm_Expect' class=maintext>
    	            <option value='-2' selected>0</option>
                 <?php 
    	            if($theaction != 'exclusion' or !$frm_Expect) $frm_Expect = '-1';
                  $theValue = 20;
                  if( $frm_Expect == -1) $frm_Expect = DEFAULT_EXPECT_EXCLUSION;
                  while($theValue <= 510){
                    if($theValue == $frm_Expect){
                      echo "<option value='$theValue' selected>$theValue</option>\n";
                    }else{
                      echo "<option value='$theValue'>$theValue</option>\n";
                    }
                    $theValue = $theValue+20;
                  }
                 ?>
                 </select>
                </div></td>
                <td bgcolor=<?php echo $expect_exclusion_color;?>><div class=maintext nowrap>&nbsp;
                <input type=checkbox name='frm_Expect2_check' value='1' <?php echo ($frm_Expect2_check)?"checked":"";?>>
                    Expect > 
    	          <select name='frm_Expect2' class=maintext>
    	            <option value='1' selected>1</option>
                 <?php 
    	            if($theaction != 'exclusion' or !$frm_Expect2) $frm_Expect2 = '1';
                  $theValue = -1;
                 // if( $frm_Expect == -1) $frm_Expect = DEFAULT_EXPECT_EXCLUSION;
                  while($theValue >= -300){
                    if($theValue == $frm_Expect2){
                      echo "<option value='$theValue' selected>$theValue</option>\n";
                    }else{
                      echo "<option value='$theValue'>$theValue</option>\n";
                    }
                    $theValue = $theValue-5;
                  }
                 ?>
                 </select>
                </div></td>
            <td colspan=6>
                <input type=button value='No Exclusion' class=black_but onClick='javascript: NoExclusion();'>
                <input type=button value='Apply Exclusion' class=black_but onClick='javascript: applyExclusion();'>
            </td>          
          </tr>
          </table>        
        </td>
      </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td align="center" colspan=2>
      <table border="0" cellpadding="0" cellspacing="1" width="97%">
      <tr bgcolor="">
        <td width="5" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
        <div class=tableheader>Well</div></td>
        <td width="5" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
        <div class=tableheader>Gene</div></td>
        <td width="50" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
        <div class=tableheader>Hit ID</div></td>
        <td width="50" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
        <div class=tableheader>Hit GI</div></td>
        <td width="50" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
        <div class=tableheader>Hit LocusTag</div></td>
        <?php if($frequencyLimit < 101){?>
        <td width="25" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
        <div class=tableheader>Frequency</div></td>
        <?php }?>
        <td width="50" bgcolor="<?php echo $bgcolordark;?>" align=center> 
        <div class=tableheader>Hit MW<BR>kDa</div></td> 
        <td width="600" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
         <div class=tableheader>Hit Description</div></td>
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
         <div class=tableheader>Score</div></td>
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
         <div class=tableheader>Expect</div></td>
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
         <div class=tableheader># Uniqe<BR>Peptide</div></td> 
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center">
          <div class=tableheader>Links</div></td>
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center">
          <div class=tableheader>&nbsp;</div></td>
        <td width="80" bgcolor="<?php echo $bgcolordark;?>" align="center">
          <div class=tableheader>Option</div></td>
      </tr>
<?php 
//-------------------------------------------------
$URL = getURL();
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);

$SQL = "SELECT BaitGeneID FROM BaitToHits 
        WHERE ProjectID ='".$_SESSION["workingProjectID"]."'         
        GROUP BY BaitGeneID";
$totalGenes = $mainDB->get_total($SQL);
$SQL = "select L.ID,L.LaneNum,L.LaneCode, L.ExpID, E.BaitID, E.Name, E.TaxID, E.ProjectID, B.GeneName 
        from Lane L, Experiment E, Bait B 
        where L.GelID=$Gel_ID and E.ID=L.ExpID and B.ID=E.BaitID ORDER BY LaneNum";
$laneResult = mysqli_query($mainDB->link, $SQL);
$laneCount = mysqli_num_rows($laneResult);

$counter2 = 0;

while(list(
     $LaneID, $LaneLaneNum, $LaneLaneCode, $LaneExpID,
     $LaneBaitID, $LaneExpName, $LaneExpTaxID, 
     $LaneProjectID, $LaneBaitGeneName )= mysqli_fetch_row($laneResult)){
 
  $SQL = "SELECT 
         ID,          
         Location               
         FROM Band 
         where LaneID='$LaneID' order by Location";   
  //echo $SQL;exit;
  $bandResult = mysqli_query($mainDB->link, $SQL);
  $bandCount = mysqli_num_rows($bandResult);
   
  
 
  if($bandCount){
  fwrite($handle, "Lane Number: ".$LaneLaneNum.",Lane Code: ".$LaneLaneCode.",Exp Number:$LaneExpID,Exp Name: $LaneExpName,Exp TaxID: $LaneExpTaxID,Project ID:$LaneProjectID,Bait Gene:$LaneBaitGeneName,Bait ID: $LaneBaitID\n\n"); 
  ?>
  <tr bgcolor="">
    <td colspan=14><div class=maintext_color><hr>
    <?php echo "Lane Number: <b>".$LaneLaneNum."</b>               
            &nbsp;Lane Code: <b>".$LaneLaneCode."</b><br><BR>";              
    ?></div>
    </td>
   </tr> 
  <?php 
  }
  
  while (list(
         $BandID,        
         $BandLocation)= mysqli_fetch_row($bandResult)){
                  
    $Band_ID = $BandID;
    //--------------------------------------------------------------------
    $SQL = "SELECT 
         ID, 
         WellID, 
         BaitID, 
         BandID, 
         Instrument,
         GeneID, 
         LocusTag, 
         HitGI, 
         HitName, 
         Expect,
         Expect2,
         MW,
         Pep_num_uniqe,
         RedundantGI, 
         ResultFile,
         SearchDatabase, 
         DateTime,
         OwnerID, 
	 	     SearchEngine         
         FROM Hits WHERE BandID='$Band_ID' ORDER BY ID";
    
    $HitResult = mysqli_query($mainDB->link, $SQL);
    $HitCount = mysqli_num_rows($HitResult);
    //echo $HitCount;
    $tmpBait['ID'] = '';
    $tmpBait['GeneID'] = ''; 
    $tmpBand['ID'] = '';
    while (list(
         $HitID, 
         $HitWellID, 
         $HitBaitID, 
         $HitBandID, 
         $HitInstrument,
         $HitGeneID,
         $HitLocusTag, 
         $HitHitGI, 
         $HitHitName, 
         $HitExpect,
         $HitExpect2,
         $HitMW,
         $HitPepNumUniqe, 
         $HitRedundantGI, 
         $HitResultFile, 
         $HitSearchDatabase, 
         $HitDateTime,
         $HitOwnerID,
	       $HitSearchEngine)= mysqli_fetch_row($HitResult)){
 
      $tmpHitNotes = array();
      $HitGeneName = '';
     
      $HitFrequency = 0;    
      $tmpbgcolor = $bgcolor;
      $tmptextfont = "maintext";
      
      $tmpbgcolor = $bgcolor;
      $tmptextfont = "maintext";
      if(is_one_peptide($HitID,$HITSDB,$HitPepNumUniqe)) array_push($tmpHitNotes, "OP");
    
      if(isset($tmpBait['ID']) && $HitBaitID != $tmpBait['ID']){
        $SQL = "SELECT 
          ID, 
          GeneID,
          LocusTag,
          GeneName,
          BaitMW       
          FROM Bait where  ID='$HitBaitID'";
        $tmpBait = $mainDB->fetch($SQL);
      }      
    
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
        
        if(isset($tmpBait['GeneID']) && $tmpBait['GeneID'] && $HitGeneID && ($tmpBait['GeneID'] == $HitGeneID)){
          array_push($tmpHitNotes, "BT");
        }      
        
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
      
      $SQL = "SELECT B.BandMW FROM Band B, PlateWell P WHERE B.ID=P.BandID AND P.ID=$HitWellID";
      $BandMWArr = $mainDB->fetch($SQL);
      $tmpNum = 0;
      $bandMW = '';
      if($BandMWArr && $BandMWArr['BandMW']){
        if($BandMWArr['BandMW'] > 0){
          $bandMW = $BandMWArr['BandMW'];
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
      }
      $rc_excluded = 0;
      
	    if($theaction == 'exclusion' && (($frm_Expect_check && $HitExpect && $HitExpect <= $frm_Expect) || ($frm_Expect2_check && $HitExpect2 && $HitExpect2 >= $frm_Expect2) || (isset($frm_Frequency) && $frm_Frequency and $HitFrequency >= $frequencyLimit))){
      //if($theaction == 'exclusion' && ($HitFrequency <= $frm_Frequence and ($HitExpect2 < $frm_Expect2  or $HitExpect >= $frm_Expect))){
        $rc_excluded = 1;
      }
      
      if(!in_array(ID_REINCLUDE,$tmpHitNotes)){
        $isTrue = 0;    
      	foreach($typeBioArr as $Value){     
      		if(in_array($Value['Alias'] ,$tmpHitNotes) && $Value['Alias'] != "HP"){
          //if(in_array($Value['Alias'] ,$tmpHitNotes)){
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
     	    if($HitFrequency >=$frequencyLimit || ($HitExpect and $HitExpect <= DEFAULT_EXPECT_EXCLUSION )){
    		    $isTrue = 1;
    	    }
        }
        if($isTrue){
          $tmpbgcolor = $excludecolor; 
    	    $tmptextfont = "excludetext";
        }
      }
      
      if(!$rc_excluded){//=====
       
        if($HitHitGI && ($HitGeneID || $HitGeneName)){
          if(!isset($giArr[$HitHitGI])){
            $giArr[$HitHitGI] = 1;
          }else{  
            $giArr[$HitHitGI]++;
          }
        }
        
        if($HitFrequency){
          $HitFrequencyPercent = $HitFrequency."%";
        }else{
          $HitFrequencyPercent = "0%";
        }
        
        $Description = str_replace(",", ";", $HitHitName);
        $Description = str_replace("\n", "", $Description);
        $HitGeneName = str_replace(",", ";", $HitGeneName);
        $HitLocusTag = str_replace(",", ";", $HitLocusTag);
        $HitExpect = str_replace(",", ";", $HitExpect);
        $HitSearchDatabase = str_replace(",", ";", $HitSearchDatabase);
        $HitHitGI = str_replace(",", ";", $HitHitGI);
        $HitHitGI = trim(preg_replace("/sp\||gi\||\|/", "",$HitHitGI));
        fwrite($handle, $BandLocation.",".$HitGeneName.",".$HitID.",".$HitHitGI.",".$HitLocusTag.",");
        if($frequencyLimit < 101){
          fwrite($handle, $HitFrequencyPercent.",");
        }
        fwrite($handle,$bandMW.",". $HitMW.",".$Description.",".$HitExpect.",".$HitSearchDatabase.",".$HitDateTime.",");
        $filterString = ''; 
    ?>
      <tr bgcolor="<?php echo $tmpbgcolor;?>">
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
          <?php echo $BandLocation;?>&nbsp;
        </div>
        </td>
        <td width="" align="center" class='gi<?php echo $HitHitGI;?>'><div class=maintext>
            <?php echo  $HitGeneID."&nbsp;/&nbsp;".$HitGeneName ;?>&nbsp;
            </div>
        </td>
        <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
            <?php echo  $HitID;?>&nbsp;
            </div>
        </td>
        <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
            <?php echo  $HitHitGI;?>&nbsp;
            </div>
        </td>
        <td width="" align="center" bgcolor='<?php echo $tmpbgcolor;?>'><div class=maintext>
            <?php echo  $HitLocusTag;?>&nbsp;
            </div>
        </td>
        <?php if($frequencyLimit < 101){?>
        <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
            <?php echo $HitFrequencyPercent;?>&nbsp;
            </div>
        </td>
        <?php }?>
        <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
            <?php echo  $HitMW;?>&nbsp;
            </div>
        </td>
        <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
            <?php echo $HitHitName;?>&nbsp;
          </div>
        </td>
        <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
            <?php  echo $HitExpect;            
            ?>&nbsp;
          </div>
        </td>
        <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
            <?php  echo $HitExpect2;            
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
          $urlGI = $HitHitGI;
          echo get_URL_str($HitHitGI, $HitGeneID, $HitLocusTag);
        ?>
        </td>
     
    <td>
      <table border=0 cellpadding="1" cellspacing="1"><tr>
    <?php 
        $filterString = '';
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
        if($typeFrequencyArr && $HitFrequency >=$frequencyLimit and !$is_reincluded){
          $typeFrequencyArr['Counter']++;
          echo "<td bgcolor='" . $typeFrequencyArr['Color'] . "' nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
          ($filterString)? $filterString.=";frequence>=$frequencyLimit and frequence<$frm_Frequence" : $filterString.="frequence>=$frequencyLimit and frequence<$frm_Frequence";
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
        //echo ID_MANUALEXCLUSION."*****";
            echo "<td bgcolor=black><font face='Arial' color=yellow size=-1><b>X</b></font></td>";
            ($filterString)? $filterString.=";manualexclusion" : $filterString.="manualexclusion";          
        }
        fwrite($handle, $filterString.",$HitResultFile\n");
        $counter2++;     
    ?>      
      </tr>
      </table>
    </td> 
      <td width="" align="left" nowrap><div class=maintext>&nbsp; &nbsp; &nbsp;
        <?php if($HitSearchEngine=='Mascot' or $HitSearchEngine=='GPM'){?>
          <a href="javascript:view_peptides(<?php echo $HitID;?>);"><img border="0" src="./images/icon_<?php echo $HitSearchEngine;?>.gif" alt="Peptides"></a>
          <a href="javascript:view_master_results('<?php echo  $HitResultFile;?>','<?php echo  $HitSearchEngine;?>');"><img border="0" src="./images/icon_<?php echo $HitSearchEngine;?>2.gif" alt="Peptides"></a>
          <?php }?>
          <a href="javascript: add_notes('<?php echo $HitID;?>');"><img src="./images/icon_notes.gif" border=0 alt="Hit Notes"></a>
          <?php    
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
      }
    } //end for
  }  
  fwrite($handle, "\n");   
}
//echo $counter2;
arsort($giArr); 
$colorIndex = 0;

?>
    </table>     
    </td>
  </tr>
</table>
</form>
<?php 
require("site_footer.php");
?>
<style type="text/css">
<?php 
foreach($giArr as $key => $value){
  if($value > 1){  
    echo ".gi".$key."\n";
    echo "{ background-color: #".$colorArr[$colorIndex]."; }\n";
    $colorIndex++;
    if($colorIndex >= 20){
      break;
    }
  }  
}
?>
</style>
<?php 
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
//**************************************
?>
