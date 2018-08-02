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

require("../common/site_permission.inc.php");
require("analyst/classes/bait_class.php");
require("analyst/classes/experiment_class.php");
require("analyst/classes/gel_class.php");
require("analyst/classes/lane_class.php");
require("analyst/classes/band_class.php");
require("analyst/classes/plate_class.php");
require("analyst/classes/plateWell_class.php");
require("analyst/classes/hits_class.php"); 
require("analyst/classes/peptide_class.php");
include("analyst/common_functions.inc.php");
require("common/common_fun.inc.php");

if(!$Band_ID){ 
  header ("Location: noaccess.html");
}
$colorArr = array('C5CBF7','0033cc','E6B751','AC9A72','F6B2A9','DF9DF7','884D9E','798AF9','687CFA','AE15E7',
                  'D5CCCD','586EFA','8ED0F5','69B0D8','4C90B7','54F4F6','82ACAD','909595','A0F4B8','7BBC8D',
                  '43CB69','E9E86F','A9A850','ffff99','99ffff','99cc00','999900','ffccff','006600','6666ff',
                  '663399','0000ff','cc3300','0099ff','9999ff','99ccff','996600','cc99ff','ff3300','ff66ff',
                  'ff00ff','99ccff','996600','00ff00','990000','993333','99cc33','9999ff','ccccff','9933cc',
                  'ffffcc','ccffff','ccff99','ccff33','99ffcc','99ff00','ff00ff','6633ff','6633ff','6600ff',
                  'ffffff','66ffcc','ffcccc','66cccc','ff99cc','6699cc','ff66cc','6666cc','ff33cc','6633cc',
                  'ffff66','66ff66','ffcc66','66cc66','ff9966','669966','ff6666','666666','ff3366','663366',
                  '99ff33','00ff33','99cc33','00cc33','999933','009933','996633','006633','993333','003333',
                  '99ffcc','00ffcc','99cccc','00cccc','9999cc','0099cc','9966cc','0066cc','9933cc',);
$giArr = array();
$colorIndex = 0;

$bgcolordark = '#999900';
$bgcolordarkHit = 'white';
$bgcolor = '#e2e083';
//$bgHitcolor="#e2e083";
$bgHitcolor="white";
$TB_HD_COLOR = $bgcolordark;

$HitGeneName = '';
?>
<html>
<head>
 <title>Prohits</title>
 <script language="Javascript" src="site_javascript.js"></script>
 <link rel="stylesheet" type="text/css" href="./site_style.css"> 
 </head>
 <script language='javascript'>
 function view_master_results(ResultFile,SearchEngine){
  if(ResultFile == ''){
    alert("No results file exists.");
    return false;
  }
  if(SearchEngine == "Mascot"){  
    file = "http://<?php echo MASCOT_IP;?>/mascot/cgi/master_results.pl?file=" + ResultFile;
  }else if(SearchEngine == "GPM"){  
    file = "http://<?php echo $gpm_ip;?>/thegpm-cgi/plist.pl?path=" + ResultFile;
  }else{
    return;
  }  
  popwin(file,800,600);
} 
 function view_image(Gel_ID){  
  file = 'gel_view.php?Gel_ID=' + Gel_ID;
  newwin = window.open(file,"gel_image",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=750,height=600');
  newwin.moveTo(10,10);
 } 
</script>
 <body>
 <center>
  <font color="navy" face="helvetica,arial,futura" size="3"><b>Sample Report</b></font>
 </center>
 <?php 
$Band = new Band($Band_ID);

if(!$Band->ID){
  //---error---
  exit;
}
$Bait = new Bait($Band->BaitID); $Bait_ID = $Bait->ID;
$Exp = new Experiment($Band->ExpID); $Exp_ID = $Exp->ID;
$Lane = new Lane($Band->LaneID); $Lane_ID = $Lane->ID;
//$Project = new Projects($Band->ProjectID);
$Gel = new Gel($Lane->GelID); $Gel_ID = $Gel->ID;
$BandOwner = get_userName($mainDB, $Band->OwnerID);
echo "<center>";
include("plate_band_info.inc.php");
echo "</center><br>";
echo "<center>";
echo "Shared spectrum peptides have the same color in Score fields and highest Score has bold number.<br>";
echo "</center>";
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
         Coverage, 
         RedundantGI,
         ResultFile,
         SearchDatabase,
         Datetime,
         SearchEngine
         FROM Hits WHERE BandID='$Band_ID'";

$sqlResult = mysqli_query($mainDB->link, $SQL);
$tmpResultFile = "########";
$startFlag = 0;
$peptideArr = array();
$IonFileArr = array();
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
      $HitCoverage,
      $HitRedundantGI,
      $HitResultFile,
      $HitSearchDatabase,
      $HitDateTime,
      $HitSearchEngine,
      )= mysqli_fetch_row($sqlResult)){
   
  if($HitGeneID){
    $SQL = "SELECT GeneName, BioFilter FROM Protein_Class WHERE  EntrezGeneID=$HitGeneID";
    $oldDBName = to_proteinDB($mainDB);
    $ProteinArr = $mainDB->fetch($SQL);
    back_to_oldDB($mainDB, $oldDBName);
    if(count($ProteinArr) && $ProteinArr['GeneName']){
      $HitGeneName = $ProteinArr['GeneName'];
    }
  }
  if($tmpResultFile != $HitResultFile){    
    if(!$startFlag){
      $startFlag++;
    }else{
      array_push($peptideArr, $IonFileArr);
      echo "</table>";
      $IonFileArr = array();
    }
    $tmpResultFile = $HitResultFile;
?>
<center>
<table border="0" cellpadding="1" cellspacing="1" width="700">
  <tr>
    <td align="right" colspan="0">&nbsp;<br>
  <?php if($tmpResultFile){?>    
      <a href="javascript:view_master_results('<?php echo $tmpResultFile;?>','<?php echo $HitSearchEngine;?>');">Click to view <b><?php echo $HitSearchEngine;?></b> search results(<?php echo $tmpResultFile;?>)</a>
  <?php }else{
    echo "<font color='red'>No results file</font>";
  }?>    
    </td>
  </tr>
  <tr>
    <td colspan=0 height=1 bgcolor="<?php echo $bgcolordark;?>"><img src="./images/pixel.gif"></td>
  </tr> 
<?php }?>
   
  <tr>
    <td align="center">
      <table border="0">
      <tr>
        <td bgcolor="<?php echo $bgHitcolor;?>" width="12%"><span class=middle>Protein ID</span></td>
        <td bgcolor="<?php echo $bgHitcolor;?>" width="20%"><span class=middle_bold><?php echo $HitGI;?></span></td>
        <td bgcolor="<?php echo $bgHitcolor;?>" width="12%"><span class=middle>Gene ID</span></td>
        <td bgcolor="<?php echo $bgHitcolor;?>" width="20%"><span class=middle_bold><?php echo $HitGeneID;?></span></td>
        <td bgcolor="<?php echo $bgHitcolor;?>" width="15%"><span class=middle>LucusTag</span></td>
        <td bgcolor="<?php echo $bgHitcolor;?>" width="20%"><span class=middle_bold><?php echo $HitLocusTag;?></span></td>
      </tr> 
      <tr>
        <td bgcolor="<?php echo $bgHitcolor;?>" width="13%"><span class=middle>Gene Name</span></td>
        <td bgcolor="<?php echo $bgHitcolor;?>" ><span class=middle_bold><?php echo $HitGeneName;?></span></td>
        <td bgcolor="<?php echo $bgHitcolor;?>"><span class=middle>MW kd</span></td>
        <td bgcolor="<?php echo $bgHitcolor;?>"><span class=middle_bold><?php echo $HitMW;?></span></td>
        <td bgcolor="<?php echo $bgHitcolor;?>"><span class=middle>Score</span></td>
        <td bgcolor="<?php echo $bgHitcolor;?>"><span class=middle_bold><?php echo $HitExpect;?></span></td>
      </tr> 
      <tr>
        <td bgcolor="<?php echo $bgHitcolor;?>" valign=top><span class=middle>Instrument</span></td>
        <td bgcolor="<?php echo $bgHitcolor;?>" valign=top><span class=middle_bold><?php echo $HitInstrument;?></span></td>
        <td  bgcolor="<?php echo $bgHitcolor;?>" valign=top><span class=middle>Redundant </span></td>
        <td bgcolor="<?php echo $bgHitcolor;?>">&nbsp;
       <?php 
        
       $GI_array = explode('gi|', str_replace(";","", $HitRedundantGI));
       for($i=0;$i<count($GI_array);$i++){
          if($GI_array[$i]){
            echo $GI_array[$i]."<br>";
          }  
       } 
       ?></td>
        <td bgcolor="<?php echo $bgHitcolor;?>" valign=top><span class=middle>Search Database</span></td>
        <td bgcolor="<?php echo $bgHitcolor;?>" valign=top><span class=middle_bold><?php echo $HitSearchDatabase;?></span></td>
      </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
<?php 
$Peptides = new Peptide();
$Peptides->fetchall($HitID);
?>
    <table border="0" cellpadding="1" cellspacing="1" width="740" align=center>  
    	<tr bgcolor="">
    	  <td width="5" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
        <div class=tableheader>PepID</div>
    	  </td>
        <td width="70" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
    	  <div class=tableheader>Score</div>
    	  </td>
    	  <td width="25" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
        <div class=tableheader>Charge</div> 
    	  </td>
    	  <td width="100" bgcolor="<?php echo $bgcolordark;?>" align=center> 
       <div class=tableheader>Mass (kDa)</div>
    	  </td>
    	  <td width="100" bgcolor="<?php echo $bgcolordark;?>" align=center> 
        <div class=tableheader>MZ</div>
    	  </td> 
        <td width="600" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
    	   <div class=tableheader>Sequence</div>
    	  </td>
    	</tr>
<?php 
    for($i=0; $i < $Peptides->count; $i++) {
      $add_color = '';
      $end_color = '';
      if($Peptides->Status[$i] == 'RB'){
        $add_color = '<font color=red><b>';
        $end_color = '</font></b>';
      }else if($Peptides->Status[$i] == 'R'){
        $add_color = '<font color=red>';
        $end_color = '</font>';
      }else if($Peptides->Status[$i] == 'B'){
        $add_color = '<font color=black><b>';
        $end_color = '</font></b>';
      }else {
        $add_color = '<font color=black>';
        $end_color = '</font>';
      }
      $Peptides->Sequence[$i] = $add_color . $Peptides->Sequence[$i] . $end_color;
      $IonFileValue = '';
      if($Peptides->IonFile[$i]){
        $IonFileValue = $Peptides->IonFile[$i];
      }
            
      if($IonFileValue){
        if(!isset($IonFileArr["$IonFileValue"])){
          $IonFileArr["$IonFileValue"] = array();
        }
        if(!isset($IonFileArr["$IonFileValue"]['peptidesID'])){
          $IonFileArr["$IonFileValue"]['peptidesID'] = array();
        }
        array_push($IonFileArr["$IonFileValue"]['peptidesID'], $Peptides->ID[$i]);
        if(isset($IonFileArr["$IonFileValue"]['maxscore'])){
          if($Peptides->Expect[$i] > $IonFileArr["$IonFileValue"]['maxscore']){
            $IonFileArr["$IonFileValue"]['maxscore'] = $Peptides->Expect[$i];
            $IonFileArr["$IonFileValue"]['maxspep'] = $Peptides->ID[$i];
          }  
        }else{
          $IonFileArr["$IonFileValue"]['maxscore'] = $Peptides->Expect[$i];
          $IonFileArr["$IonFileValue"]['maxspep'] = $Peptides->ID[$i];
        }
      }
?>
    	<tr bgcolor="<?php echo $bgcolor;?>">
    	  <td width="" align="center"><font face="arial" size="1">
    	      <?php echo '<b>'.$Peptides->ID[$i]."</b>";?>&nbsp;
    	    </div>
    	  </td>
        <td width="" align="center" class=peptide<?php echo $Peptides->ID[$i];?>><div class=maintext>
    	      <?php echo $Peptides->Expect[$i] ;?>&nbsp;
    	      </div>
    	  </td>
    	  <td width="" align="left"><div class=maintext>
    	      <?php echo $Peptides->Charge[$i];?>&nbsp;
    	      </div>
    	  </td>
    	  <td width="" align="center"><div class=maintext>
    	      <?php echo  $Peptides->MASS[$i];?>&nbsp;
    	      </div>
    	  </td>
        <td width="" align="center"><div class=maintext>
    	      <?php echo  $Peptides->MZ[$i];?>&nbsp;
    	      </div>
    	  </td>
        <td width="" align="left"><div class=maintext>
    	      &nbsp;<?php echo $Peptides->Sequence[$i]." <font size=1>(".$IonFileValue;?>)</font>&nbsp;
    	    </div>
    	  </td>
    	   
    	</tr>
  <?php }?>  
</table>    
    </td>
  </tr>  
<?php  
}
array_push($peptideArr, $IonFileArr);
echo "</table>";
 ?>
</table>
</center>
</body>
</html>
<style type="text/css">
<?php 
$count = 0;
$breakFlag = 0;
foreach($peptideArr as $firstlevelkey => $firstlevelvalue){
  foreach($firstlevelvalue as $secondlevelkey => $secondlevelvalue){
    if(count($secondlevelvalue['peptidesID']) > 1){
      foreach($secondlevelvalue['peptidesID'] as $thirdlevelvalue){
        echo ".peptide".$thirdlevelvalue."\n";
        echo "{ background-color: #".$colorArr[$colorIndex].";\n";
        if($thirdlevelvalue == $secondlevelvalue['maxspep']){
          echo "font-weight : bold;\n";
        }  
        echo "}\n";
        $count++;
        if($colorIndex >= 999){
          $breakFlag = 1;
          break;
        }
      }
      $colorIndex++;
      if($breakFlag) break;
    }
    if($breakFlag) break;
  }
  if($breakFlag) break;
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
    $rt .= "×10<sup>$tmp_int</sup>";
   
  }else if($decimals > 1){
    $tmpPow = $decimals - $tmp_int;
    $rt = sprintf("%0.1f", pow(10,$tmpPow));
    $rt .= "×10<sup>$tmp_int</sup>";
  }else{
    $rt = sprintf("%0.1f", $Value);
  }
  return $rt;
}
exit
//**************************************
?>

