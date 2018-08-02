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
$sub = '';
$submitted = 0;
$whichBait ='';
$img_total = 0;
$frm_Frequency = '';
$is_reincluded = '';
$typeBioArr = array();
$typeExpArr = array();
$typeFrequencyArr = '';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php"); 
include("analyst/site_print_header.php");

if(!$Bait_ID) {
?>
  <script language=javascript>
    document.location.href='noaccess.html';
  </script>
<?php 
  exit;
}
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);

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


$SQL = "SELECT 
  ID, 
  GeneID,
  LocusTag,
  GeneName, 
  BaitMW, 
  Clone, 
  Vector, 
  Description
  FROM Bait where ID='$Bait_ID'AND ProjectID=$AccessProjectID";  
$Bait = $mainDB->fetch($SQL);
 //[Bait_ID] => 2139
 //   [Exp_ID] => 2721
 //   [Band_ID] => 25739
 
 
 
$SQL = "SELECT 
  ID, 
  Name, 
  OwnerID, 
  DateTime 
  FROM Experiment
  WHERE ID = '$Exp_ID' and ProjectID=$AccessProjectID";     	
  
$Exps = $mainDB->fetch($SQL);

$SQL = "SELECT 
  `Location`,
  `BandMW`,
  `OwnerID`,
  `DateTime`
  FROM `Band` WHERE `ID`='$Band_ID'";
$SampleInfo = $mainDB->fetch($SQL);

$bgcolordark = "#94c1c9";
$bgcolor = "white";
//set default exclusion

$usersArr = get_users_ID_Name($mainDB);
?>


<table border="0" cellpadding="0" cellspacing="0" width="97%">
   <tr>
    <td align="left">
		<font color="navy" face="helvetica,arial,futura" size="3"><b>Sample Report</b> 
		</font> 
	</td>
    <td align="right"> &nbsp;
     </td>
  </tr>
  <tr>
  	<td align="left" colspan=2>
      <table border=0 cellspacing="0" cellpadding="2">
      <tr>
      	<td valign=top>
          <table border=0 cellspacing="0" cellpadding="0">
          <tr>
          	<td width=80><div class=large>Bait ID:</div></td>
            <td width=100><div class=large><b><?php echo $Bait_ID;?></b> &nbsp;</div></td>
            <td width=100><div class=large>Gene ID:</div></td>
            <td width=100><div class=large>&nbsp;<b><?php echo $Bait['GeneID'];?></b> &nbsp;</div></td>
            <td width=100><div class=large>Locus Tag:</div></td>
            <td width=100><div class=large>&nbsp;<b><?php echo $Bait['LocusTag'];?></b> &nbsp;</div></td>
            <td width=80><div class=large>Gene:</div></td></td>
            <td width=100><div class=large>&nbsp;<b><?php echo $Bait['GeneName'];?></b> &nbsp;</div></td>
            <td>&nbsp;</td>
          </tr>
          <tr>
          	<td><div class=large>Clone:</div></td>
            <td><div class=large>&nbsp;<b><?php echo $Bait['Clone'];?></b> &nbsp;</div></td>
            <td><div class=large>Vector:</div></td>
            <td><div class=large>&nbsp;<b><?php echo $Bait['Vector'];?></b> &nbsp;</div></td>
            <td><div class=large>MW:</div></td>
            <td><div class=large>&nbsp;<b><?php echo $Bait['BaitMW'];?></b>kDa &nbsp;</div></td>
            <td><div class=large>&nbsp;&nbsp;</div></td>
            <td><div class=large>&nbsp;&nbsp;</div></td>
          	<td>&nbsp;</td>
          </tr>
          </table>
        </td>
     </tr>
     <tr>
    <td align="left" colspan=10><br>    
      <table border=0 cellspacing="0" cellpadding="0" width=100%>     
        <tr>
           <td valign=top>
            <table border=0 cellspacing="0" cellpadding="2" width=100%>
               <tr>
                 <td colspan=10><div class=maintext><b>Experiment</b></div></td>
               </tr>
  	        <?php   	        
	          $theUser = get_userName($mainDB, $SampleInfo['OwnerID']);
            $SQL = "SELECT C.ID, 
              C.`Condition`  
              FROM `Condition` C , ExpCondition E 
              WHERE C.ID = E.ConditionID and E.ExpID=".$Exps['ID']." ORDER BY C.ID";
            $ExpCDT = $mainDB->fetchAll($SQL);
            $exConditions = '';
            for($k=0;$k<count($ExpCDT);$k++){
              if($exConditions){
                $exConditions .= ";".$ExpCDT[$k]['Condition'];
              }else{
                $exConditions .= $ExpCDT[$k]['Condition'];
              } 
            }             
  	        ?>
                <tr>
      	          <td><div class=maintext>Sample ID:</div></td>
                  <td><div class=maintext><b><?php echo $Band_ID;?></b></div></td>
      	          <td><div class=maintext>Sample Name:</div></td>
                  <td><div class=maintext><b><?php echo $SampleInfo['Location'];?></b></div></td>
                  <td><div class=maintext>Band Observed MW:</div></td>
                  <td><div class=maintext><b><?php echo ($SampleInfo['BandMW'] == '0.000' || !$SampleInfo['BandMW'])?str_repeat('&nbsp;', 20):$SampleInfo['BandMW'].'kDa';?></b></div></td>
                  <td><div class=maintext>Submitted by:</div></td>
                  <td><div class=maintext><b><?php echo $theUser;?></div></td>
      	          
      	        </tr>
                <tr>
      	          <td><div class=maintext>Exp ID:</div></td>
                  <td><div class=maintext><b><?php echo $Exps['ID'];?></b></div></td>
                  <td><div class=maintext>Date:</div></td>
                  <td><div class=maintext><b><?php echo $SampleInfo['DateTime'];?></div></td>                
      	          <td><div class=maintext>Condition:</div></td>
                  <td colspan=5><div class=maintext><b><?php echo $exConditions;?></b></div></td>
      	        </tr>
  	        <?php 
              $SampleInfo['Name'] = str_replace(",", ";", $SampleInfo['Location']);
              $exConditions = str_replace(",", ";", $exConditions);
              $theUser = str_replace(",", ";", $theUser);
            ?>
             </table>
            </td>
        </tr>        
      </table>
    </td><br>&nbsp;
  </tr>
     </table>
	  </td>
  </tr>
  <tr>
    <td align="center" colspan=2><br>&nbsp;
  <table border="0" cellpadding="0" cellspacing="1" width="100%">
	<tr bgcolor="">
	  <td width="5" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader>ID</div></td>
          <td width="50" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
          <div class=tableheader>GI</div></td>
          <td width="50" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
          <div class=tableheader>Gene</div></td>
          <td width="50" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
          <div class=tableheader>LocusTag</div></td>
          <td width="25" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader>Frequency</div></td>
          <td width="25" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader>Redundant</div></td>
          <td width="50" bgcolor="<?php echo $bgcolordark;?>" align=center> 
          <div class=tableheader>MW<BR>kDa</div></td> 
          <td width="600" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
           <div class=tableheader>Description</div></td>
          <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
           <div class=tableheader>Score</div></td>
	</tr>
<?php 
$SQL = "SELECT BaitGeneID FROM BaitToHits 
        WHERE ProjectID ='".$_SESSION["workingProjectID"]."'         
        GROUP BY BaitGeneID";     
$totalGenes = $mainDB->get_total($SQL);

$tmpExp['ID'] = 0;
$SQL = "SELECT 
         H.ID,
         H.WellID, 
         H.BaitID, 
         H.BandID,
         H.GeneID, 
         H.LocusTag, 
         H.HitGI, 
         H.HitName, 
         H.Expect,
         H.MW,
         H.Pep_num_uniqe,
         H.RedundantGI,
         H.SearchEngine,
         B.ExpID
         FROM Hits H, Band B
         WHERE H.BandID = B.ID
         and H.BaitID='$Bait_ID' 
         and B.ProjectID=$AccessProjectID 
         ORDER BY B.ExpID, H.ID";
         
$sqlResult = mysqli_query($mainDB->link, $SQL);
$img_total = mysqli_num_rows($sqlResult);  // for create image

while (list(
  $ID,
  $WellID, 
  $BaitID, 
  $BandID,
  $GeneID, 
  $LocusTag, 
  $HitGI, 
  $HitName, 
  $Expect,
  $MW,
  $Pep_num_uniqe,
  $RedundantGI,
  $SearchEngine,
  $ExpID)= mysqli_fetch_row($sqlResult) ){
  $tmpHitNotes = array();
  $HitGeneName = '';
  $HitFrequency = 0;
  if($GeneID || $LocusTag)
  {
    $SQL = "SELECT GeneName, BioFilter FROM Protein_Class WHERE  EntrezGeneID=$GeneID";
    $ProteinArr = $proteinDB->fetch($SQL);
    if(count($ProteinArr) && $ProteinArr['GeneName']){
      $HitGeneName = $ProteinArr['GeneName'];
    }
    if(count($ProteinArr) && $ProteinArr['BioFilter']){
      $tmpHitNotes = explode(",", $ProteinArr['BioFilter']);
    }
    
    $SQL = "SELECT Value, FilterAlias FROM ExpFilter WHERE ProjectID ='".$_SESSION["workingProjectID"]."' AND GeneID=$GeneID";
    $ExpFilterArr = $mainDB->fetchAll($SQL);
    if($ExpFilterArr){
      for($n=0; $n<count($ExpFilterArr); $n++){
        if($ExpFilterArr[$n]['FilterAlias'] == 'FQ'){
          if($ExpFilterArr[$n]['Value']){
            $HitFrequency = round(($ExpFilterArr[$n]['Value'] / $totalGenes)*100, 2);
          }  
        }else if($ExpFilterArr[$n]['FilterAlias']){
          array_push($tmpHitNotes, $ExpFilterArr[$n]['FilterAlias']);
        }
      }  
    }
    $SQL = "SELECT FilterAlias FROM HitNote WHERE HitID=$ID";
    $HitNoteArr = $mainDB->fetchAll($SQL);
    if($HitNoteArr){
      for($n=0; $n<count($HitNoteArr); $n++){
        if($HitNoteArr[$n]['FilterAlias']){
          array_push($tmpHitNotes, $HitNoteArr[$n]['FilterAlias']);
        }
      }
    }
  }
  
  if(is_one_peptide($ID,$HITSDB,$Pep_num_uniqe)) array_push($tmpHitNotes, "OP");
  
  $SQL = "SELECT B.BandMW FROM Band B, PlateWell P WHERE B.ID=P.BandID AND P.ID=$WellID";
  $BandMWArr = $mainDB->fetch($SQL);
  $tmpNum = 0;
  if($BandMWArr && $BandMWArr['BandMW']){
    if($BandMWArr['BandMW'] > 0){
		  $tmpNum = abs(($BandMWArr['BandMW'] - $MW)*100/$BandMWArr['BandMW']);
    }
		if($BandMWArr['BandMW'] < 25 or $BandMWArr['BandMW'] > 100){
			if($tmpNum > 50){
				array_push($tmpHitNotes, "AW");
			}
    }  
  }
   
  $tmpbgcolor = $bgcolor;
  $tmptextfont = "maintext";  
  
 
  $rc_excluded = 0;
	if(!$frm_BT and $Bait['GeneID'] == $GeneID){    
	  $rc_excluded = 0;	 
	}else if($theaction == 'exclusion' && !in_array(ID_REINCLUDE, $tmpHitNotes)){
	  if(($Expect && $Expect <= $frm_Expect) || ($frm_Frequency and $HitFrequency >= $frequencyLimit)){
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
      if(!$rc_excluded && in_array(ID_MANUALEXCLUSION, $tmpHitNotes)) $rc_excluded=1; 
    }
  }  
     
  if(!$rc_excluded and $theaction != 'exclusion' && !in_array(ID_REINCLUDE,$tmpHitNotes)){
    $isTrue = 0;    
  	foreach($typeBioArr as $Value){ 
      if(in_array($Value['Alias'], $tmpHitNotes)){
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
 	    if($HitFrequency >=$frequencyLimit || ($Expect and $Expect <= DEFAULT_EXPECT_EXCLUSION )){
		    $isTrue = 1;
	    }
    }
    if($isTrue){
      $tmpbgcolor = $excludecolor; 
	    $tmptextfont = "excludetext";
    }
  }
    if(!$rc_excluded){
?>
	<tr bgcolor="<?php echo $tmpbgcolor;?>">
	  <td width="" align="center" bgcolor=<?php 
    if($Bait['GeneID'] == $GeneID){
  	  echo "'$bait_color'";
      for($m=0; $m<count($typeBioArr); $m++){
        if($typeBioArr[$m]['Alias'] == ID_BAIT){ 
    				$typeBioArr[$m]['Counter']++;
    		}
      }
  	}else{
  	  echo "'$tmpbgcolor'";
  	}
	 ?>><div class=<?php echo $tmptextfont;?>>
        <?php echo $ID;?>&nbsp;
      </div>
    </td>
    <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
        <?php echo  $HitGI;?>&nbsp;
        </div>
    </td>
    <td width="" align="center"><div class=maintext>
        <?php 
        if($GeneID || $HitGeneName){
          echo  $GeneID." / ".$HitGeneName;
        }
        ?>&nbsp;
        </div>
    </td>
    <td width="" align="center"><div class=maintext>
        <?php echo  $LocusTag;?>&nbsp;
        </div>
    </td>
    <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
        <?php echo ($HitFrequency)?$HitFrequency."%":'0%';?>&nbsp;
        </div>
    </td>
    <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
        <?php echo str_replace(";","<br>", $RedundantGI);?>&nbsp;
        </div>
    </td>
    <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
        <?php echo  $MW;?>&nbsp;
        </div>
    </td>
    
    <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
        <?php echo $HitName;?>&nbsp;
      </div>
    </td>
    <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
        <?php  
        echo $Expect;
        ?>&nbsp;
      </div>
    </td>
	</tr>
   
<?php 
   }//end if re_excluded
  
} //end for
?>
   </table>
     
    </td>
  </tr>
</table>
</form>
<br>
<?php 
include("site_simple_footer.php");
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