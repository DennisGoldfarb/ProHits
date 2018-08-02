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
$typeBioArr = array();
$typeExpArr = array();
$typeFrequencyArr = '';

//-----------
$bgcolor = '';
$tbl_0 = '';
$tbl_1 = '';
$tbl_2 = '';
//-----------

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php"); 
include("analyst/site_print_header.php");

if(!$Plate_ID ) {
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
          Name, 
          PlateNotes, 
          OwnerID, 
          DateTime,
          ComplitDate,
          DigestedBy,
          DigestStarted,
          DigestCompleted,
          Buffer, 
          MSDate 
          FROM Plate where  ID='$Plate_ID'";
$Plate = $mainDB->fetch($SQL);
          
$SQL = "SELECT BaitGeneID FROM BaitToHits 
        WHERE ProjectID ='".$_SESSION["workingProjectID"]."'         
        GROUP BY BaitGeneID";
$totalGenes = $mainDB->get_total($SQL);
//--------------------------------------
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
         H.MW,
         H.Pep_num_uniqe, 
         H.RedundantGI, 
         H.ResultFile, 
         H.SearchDatabase, 
         H.DateTime,
         H.OwnerID, 
         H.SearchEngine,
         W.WellCode 
         FROM Hits H, PlateWell W 
         WHERE H.WellID=W.ID and W.PlateID='$Plate_ID' 
         and W.ProjectID=$AccessProjectID
         ORDER By W.WellCode";

$Hits = $mainDB->fetchAll($SQL);

?>
<table border="1" cellpadding="0" cellspacing="0" width="50%">
  <tr>
    <td align="left" colspan=3><br>
     <table border=0 cellspacing="0" cellpadding="2">
      <tr>
        <td valign=top>
          <table border=0 cellspacing="0" cellpadding="0">
          <tr>
            <td width=100><div class=large>Plate ID:</div></td>
            <td width=100><div class=large><b><?php echo $Plate_ID;?></b> &nbsp;</div></td>
            <td width=100><div class=large>Name:</div></td>
            <td width=100><div class=large>&nbsp;<b><?php echo $Plate['Name'];?></b> &nbsp;</div></td>
            <td width=200> &nbsp;</td>
            <td width=1>&nbsp;</td>
          </tr>
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
     </table>
    </td>
  </tr>
<?php 
$tmpBait['ID'] = '';
for($i=0; $i < count($Hits); $i++){
  $HitFrequency = '';
  $HitGeneName = "";
  $tmpHitNotes = array();  
  if($Hits[$i]['GeneID'])
  {
    $SQL = "SELECT GeneName, BioFilter FROM Protein_Class WHERE  EntrezGeneID='".$Hits[$i]['GeneID']."'";
    $ProteinArr = $proteinDB->fetch($SQL);
    $HitGeneName = "";
    if(count($ProteinArr) && $ProteinArr['GeneName']){
      $HitGeneName = $ProteinArr['GeneName'];
    }
    $SQL = "SELECT GeneName, BioFilter FROM Protein_Class WHERE  EntrezGeneID='".$Hits[$i]['GeneID']."'";
    $ProteinArr = $proteinDB->fetch($SQL);
    if(count($ProteinArr) && $ProteinArr['GeneName']){
      $HitGeneName = $ProteinArr['GeneName'];
    }
    if(count($ProteinArr) && $ProteinArr['BioFilter']){
      $tmpHitNotes = explode(",", $ProteinArr['BioFilter']);
    }
    
    $SQL = "SELECT Value, FilterAlias FROM ExpFilter WHERE ProjectID ='".$_SESSION["workingProjectID"]."' AND GeneID='".$Hits[$i]['GeneID']."'";
    $ExpFilterArr = $mainDB->fetchAll($SQL);
    if($ExpFilterArr){
      for($n=0; $n<count($ExpFilterArr); $n++){
        if($ExpFilterArr[$n]['FilterAlias'] == 'FQ'){
          if($ExpFilterArr[$n]['Value']  && $totalGenes){
            $HitFrequency = round(($ExpFilterArr[$n]['Value'] / $totalGenes)*100, 2);
          }  
        }else if($ExpFilterArr[$n]['FilterAlias']){
          array_push($tmpHitNotes, $ExpFilterArr[$n]['FilterAlias']);
        }
      }  
    }    
  }
  $SQL = "SELECT FilterAlias FROM HitNote WHERE HitID='".$Hits[$i]['ID']."'";
  $HitNoteArr = $mainDB->fetchAll($SQL);
  if($HitNoteArr){
    for($n=0; $n<count($HitNoteArr); $n++){
      if($HitNoteArr[$n]['FilterAlias']){
        array_push($tmpHitNotes, $HitNoteArr[$n]['FilterAlias']);
      }
    }
  }
  if(is_one_peptide($Hits[$i]['ID'],$HITSDB,$Hits[$i]['Pep_num_uniqe'])) array_push($tmpHitNotes, "OP");
  
  $SQL = "SELECT B.BandMW FROM Band B, PlateWell P WHERE B.ID=P.BandID AND P.ID='".$Hits[$i]['WellID']."'";
  $BandMWArr = $mainDB->fetch($SQL);
  $tmpNum = 0;
  if($BandMWArr && $BandMWArr['BandMW']){
    if($BandMWArr['BandMW'] > 0){
		  $tmpNum = abs(($BandMWArr['BandMW'] - $Hits[$i]['MW'])*100/$BandMWArr['BandMW']);
    }
		if($BandMWArr['BandMW'] < 25 or $BandMWArr['BandMW'] > 100){
			if($tmpNum > 50){
				array_push($tmpHitNotes, "AW");
			}
    }  
  }  
  if($Hits[$i]['BaitID'] != $tmpBait['ID']){
    $SQL = "SELECT 
      ID, 
      GeneID,
      LocusTag,
      GeneName,
      BaitMW       
      FROM Bait where  ID='".$Hits[$i]['BaitID']."'";
    $tmpBait = $mainDB->fetch($SQL);;
  }
  
  if($tmpBait['GeneID'] && $Hits[$i]['GeneID'] && ($tmpBait['GeneID'] == $Hits[$i]['GeneID'])){
    array_push($tmpHitNotes, "BT");
  }
  
  //--------------------------------------------------------------------------------------------------
  $rc_excluded = 0;
  if($theaction == 'exclusion' && !in_array(ID_REINCLUDE, $tmpHitNotes)){
	  if(($Hits[$i]['Expect'] && $Hits[$i]['Expect'] <= $frm_Expect) || ($frm_Frequency and $HitFrequency >= 3)){
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
  
  if(!$rc_excluded){
    $tbl_0 .= "<tr><td align=\"center\">".(($Hits[$i]['GeneID'])?$Hits[$i]['GeneID']:"&nbsp;")."</td></tr>\n";
    $tbl_1 .= "<tr><td align=\"center\">".(($Hits[$i]['LocusTag'])?$Hits[$i]['LocusTag']:"&nbsp;")."</td></tr>\n";
    $tbl_2 .= "<tr><td align=\"center\">".(($HitGeneName)?$HitGeneName:"&nbsp;")."</td></tr>\n";
  }//end if re_excluded
} //end for
?>
  <tr>
    <td align="center"><b>Gene ID</b><br><br></td>
    <td align="center"><b>LocusTag</b><br><br></td>
    <td align="center"><b>Gene Name</b><br><br></td>
  </tr>
  <tr>
    <td align="center"><table border=0><?php echo $tbl_0;?></table></td>
    <td align="center"><table border=0><?php echo $tbl_1;?></table></td>
    <td align="center"><table border=0><?php echo $tbl_2;?></table></td>
  </tr>
</table><br><br>
<?php 
include("site_simple_footer.php");

?>