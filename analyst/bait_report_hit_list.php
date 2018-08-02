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
$tbl_geneID = '';
$tbl_orf = '';
$tbl_gene = '';
$tbl_score = '';
$tbl_frequency = '';
$tbl_rate ='';
$tbl_onePeptide ='';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php"); 
include("analyst/site_print_header.php");

if(!$Bait_ID){
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
?>
<table border="0" cellpadding="0" cellspacing="0" width="97%">
  <tr>
    <td align="left" colspan=6><br>
     <table border=0 cellspacing="0" cellpadding="2">
      <tr>
        <td valign=top>
          <table border=0 cellspacing="0" cellpadding="0">
          <tr>
            <td width=80><div class=large>Bait ID:</div></td>
            <td width=100><div class=large><b><?php echo $Bait_ID;?></b> &nbsp;</div></td>
            <td width=100><div class=large>Locus Tag:</div></td>
            <td width=100><div class=large>&nbsp;<b><?php echo $Bait['LocusTag'];?></b> &nbsp;</div></td>
            <td width=80><div class=large>Gene:</div></td></td>
            <td width=100><div class=large>&nbsp;<b><?php echo $Bait['GeneName'];?></b> &nbsp;</div></td></td>
           
          </tr>
          
          <tr>
            <td><div class=large>Clone:</div></td>
            <td><div class=large>&nbsp;<b><?php echo $Bait['Clone'];?></b> &nbsp;</div></td>
            <td><div class=large>Vector:</div></td>
            <td><div class=large>&nbsp;<b><?php echo $Bait['Vector'];?></b> &nbsp;</div></td>
            <td><div class=large>MW:</div></td>
            <td><div class=large>&nbsp;<b><?php echo $Bait['BaitMW'];?></b>kDa &nbsp;</div></td>
          </tr>
          <tr>
           <td colspan=2>Bait Description:</td>
           <td colspan=4><div class=maintext><?php echo trim($Bait['Description']);?></div></td>
          </tr>
          </table>
        </td>
      </tr>
     </table>
    </td>
  </tr>
<?php 
$SQL = "SELECT BaitGeneID FROM BaitToHits 
        WHERE ProjectID ='".$_SESSION["workingProjectID"]."'         
        GROUP BY BaitGeneID";     
$totalGenes = $mainDB->get_total($SQL);

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
    
    if($Bait['GeneID'] == $GeneID){
      array_push($tmpHitNotes, "BT");
    }
    
    $SQL = "SELECT Value, FilterAlias FROM ExpFilter WHERE ProjectID ='".$_SESSION["workingProjectID"]."' AND GeneID=$GeneID";
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
     
  if(!$rc_excluded){
    $tbl_geneID .= "<tr><td align=\"center\">".(($GeneID)?$GeneID:"&nbsp;")."</td></tr>\n";
    $tbl_orf .= "<tr><td align=\"center\">".(($LocusTag)?$LocusTag:"&nbsp;")."</td></tr>\n";
    $tbl_gene .= "<tr><td align=\"center\">".(($HitGeneName)?$HitGeneName:"&nbsp;")."</td></tr>\n";
	  $tbl_score .= "<tr><td align=\"center\">".(($Expect)?$Expect:"0")."</td></tr>\n";
	  $tbl_frequency .= "<tr><td align=\"center\">".(($HitFrequency)?$HitFrequency:"0")."</td></tr>\n";
	  if(!$HitFrequency){
	    $tmp_rate = 0;
	  }else{
	    $tmp_rate = floor($Expect/$HitFrequency);
	  }
	  $tbl_rate .= "<tr><td align=\"center\">$tmp_rate</td></tr>\n";
	 
	  if(in_array(ID_ONEPEPTIDE,$tmpHitNotes) ){
	    $tmp_onePeptide = "Y";
	  }else{
	    $tmp_onePeptide = "N";
	  }
	  $tbl_onePeptide .= "<tr><td align=\"center\">$tmp_onePeptide</td></tr>\n";
  }//end if re_excluded
} //end for
?>
  <tr>
    <td align="center"><b>Gene ID</b><br><br></td>
    <td align="center"><b>Locus Tag</b><br><br></td>
    <td align="center"><b>Gene Name</b><br><br></td>
    <td align="center"><b>Score</b><br><br></td>
    <td align="center"><b>Frequency</b><br><br></td>
    <td align="center"><b>Score/Frequency</b><br><br></td>
    <td align="center"><b>One Peptide</b><br><br></td>
  </tr>
  <tr>
    <td align="center"><table border=0><?php echo $tbl_geneID;?></table></td>
    <td align="center"><table border=0><?php echo $tbl_orf;?></table></td>
    <td align="center"><table border=0><?php echo $tbl_gene;?></table></td><br>
    <td align="center"><table border=0><?php echo $tbl_score;?></table></td>
    <td align="center"><table border=0><?php echo $tbl_frequency;?></table></td>
    <td align="center"><table border=0><?php echo $tbl_rate;?></table></td>
    <td align="center"><table border=0><?php echo $tbl_onePeptide;?></table></td>
  </tr>
</table><br><br>
<?php 
include("site_simple_footer.php");
?>