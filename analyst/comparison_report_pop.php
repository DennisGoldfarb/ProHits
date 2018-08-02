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

set_time_limit(3600*0.5);

$frm_order_by = '';
$frm_red = '';
$frm_green  = '';
$frm_blue = '';
$spliter = ',,';
$giInfoArr = array();
$frm_Display_Mascot_hits = '';
$frm_Display_GPM_hits = '';
//---jp-------------------------
$is_reincluded = '';
$typeBioArr = array();
$typeExpArr = array();
$typeFrequencyArr = '';
$expect_exclusion_color="#93ffff";
$bait_color="red";
$excludecolor = "#a7a7a7";
$frm_Expect_check = '';
$frm_Expect2_check = '';
$AccTableW = '390';
$groupedBandIDstr = '';
$displayType = '';
$frm_selected_exp_str2 = '';
$frm_selected_bait_str = '';
$GIstr = '';
$filteGI = 0;
$controlBandColor = "#f9eec1";
$out_Sgroups_Name_str = '';

//-------------------------------
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

if(isset($start)){
  $frm_Display_Mascot_hits = 'y';
  $frm_Display_GPM_hits = 'y';
  $frm_red = 'y'; 
  $frm_green = 'y'; 
  $frm_blue = 'y';
}
$tmpCounter = 0;

if( getenv("HTTP_CLIENT_IP")){
 $ip = getenv('HTTP_CLIENT_IP');
}else
if ( getenv("HTTP_X_FORWARDED_FOR")) {
 $ip = getenv('HTTP_X_FORWARDED_FOR');
}else{
 $ip = getenv('REMOTE_ADDR');
}

$outDir = "../TMP/comparison/";
if(!_is_dir($outDir)) _mkdir_path($outDir);
$file = $outDir.$ip.".txt";
$fd = fopen($file,'w');

//------------------------------------------------------
$newGIarr = array();
if($displayType){
  $filteGI++;
  $newGIarr = explode(',', $GIstr);
}
if($filteGI < 2){
  $GIstr = '';
}
if($filteGI == 1){
  $frm_red = 'y'; 
  $frm_green = 'y'; 
  $frm_blue = 'y';
}
//------------------------------------------------------

$SelectedBaits = array();
if($frm_selected_bait_str){
  $SQL = "SELECT `ID`,`GeneID`,`GeneName`, `BaitAcc`,`TaxID`,`Clone` FROM `Bait` 
          WHERE `ProjectID`='$AccessProjectID' AND ID IN($frm_selected_bait_str) ";
  if($frm_order_by == 'ID' or $frm_order_by == 'GeneName' or $frm_order_by == 'BaitAcc'){
  $SQL .= "ORDER BY $frm_order_by";
  }else{
    $SQL .= "ORDER BY ID DESC";
  }       
  //        ORDER BY $frm_order_by";
  $SelectedBaits = $HITSDB->fetchAll($SQL);
}
$frm_exp_arr = explode(',', $frm_selected_exp_str);
$frm_bait_arr = explode(',', $frm_selected_bait_str);
$bandGroupArr = array();
if($displayType == ''){
  $frm_selected_exp_str2 = $frm_selected_exp_str;
  $frm_selected_bait_str2 = $frm_selected_bait_str;
}else{
  $tmpArr1 = explode(',', $groupedBandIDstr);
  foreach($tmpArr1 as $value){
    $tmpArr2 = explode("_", $value);
    $bandGroupArr[$tmpArr2[1]] = $tmpArr2[0];
  }
}
//following arrays are need to contain all report information
$UniHitGIs = array(); //
$BaitExps = array();  //$BaitExp[$tmpBaitID][0-11111] =  array($tmpExpID,$tmpExpName, $tmpDateTime, $tmpUserName);
$ExpBands = array();  //$ExpBands[$tmpExpID][0-1111] = array($tmpBandID, $tmpBandLocation, $Modidfycation);
$BandHits = array();  //$BandHits[$tmpGI][$tmpBaitID][$tmpExpID][$tmpBandID][$tmpSearchEngine] = array ($tmpResultFile, $tmpExpect);
$ExpBandCount = array();   //$ExpBandCount[$tmpExpID]
$BaitBandCount = array(); //$BaitBandCount[$tmpBaitID]

$bgColor_0 = '#e6e6e6';
$bgColor_1 = '#cfcfcf';
$bg_tb = 'white';
$red = '#ff0000';
$blue = '#00bfff';
$green = '#92ef8f';

if(count($SelectedBaits) > 1){
  $comparisionType = 'Bait';
}else if(count($frm_exp_arr) > 1){
  $comparisionType = 'Experiment';
}else{
  $comparisionType = 'Sample';
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
    if(!$theaction){
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
    if(!$theaction){
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
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>ProHits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<script language="Javascript" src="site_javascript.js"></script>
<script language='javascript'>
function goBaitReport(Bait_ID,GI){
  file = 'bait_report.php?Bait_ID=' + Bait_ID + '&GI=' + GI;
  opener.document.location = file;
}
function popImage(){
  file = './comparison_report_pop_gif.php';
  nWin = window.open(file,"image",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=800');
  nWin.moveTo(4,0);
}
function goGIDetail(GI){
  theForm = document.form_comparison;
  theForm.GI.value = GI;
  theForm.action = 'comparison_gi_detail_pop.php';
  theForm.target = '_blank';
  theForm.submit();
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
  theForm = document.form_comparison;
  //alert(theForm.groupedBandIDstr.value);
  if(theForm.displayType.value == ''){
    change_display_type();
  }
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

function change_display_type(){
  theForm = document.form_comparison;
  var x = theForm.elements;
  var baitIDarr = new Array();
  var expIDarr = new Array();
  var bandIDstr = "";
  var flag = 0;
  for(var i=0; i<x.length; i++){
    if(/^groupSelect_/.test(x[i].name)){
      if(x[i].value != ""){
        var tmpArr = x[i].id.split("_");
        //-----
        var hasBait = 0;
        for(var j=0; j<baitIDarr.length; j++){
          if(baitIDarr[j] == tmpArr[0]){
            hasBait = 1;
            break;
          }  
        }
        if(hasBait == 0){
          baitIDarr.push(tmpArr[0]);
        }
        //-----
        var hasExp = 0;
        for(var j=0; j<expIDarr.length; j++){
          if(expIDarr[j] == tmpArr[1]){
            hasExp = 1;
            break;
          }  
        }
        if(hasExp == 0){
          expIDarr.push(tmpArr[1]);
        }
        //-----
        if(bandIDstr != "") bandIDstr += ","
        bandIDstr += x[i].value + "_" + tmpArr[2];
        if(x[i].value == 'CTL' && flag == 0){
          theForm.displayType.value = "Control";
          flag = 1;
        }else if(flag == 0){
          theForm.displayType.value = 'Group';
        }
      }
    }
  }
  if(bandIDstr != ''){
    theForm.frm_selected_bait_str.value =  baitIDarr.join(",");
    theForm.frm_selected_exp_str.value = expIDarr.join(",");
    theForm.groupedBandIDstr.value = bandIDstr;
  } 
}

function NoExclusion(){
  theForm = document.form_comparison;
  theForm.theaction.value = '';
  theForm.action = '<?php echo $_SERVER['PHP_SELF'];?>';
  theForm.target = "_self";
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
function to_default(){
  window.location="<?php echo $_SERVER['PHP_SELF'];?>?theaction=report&frm_selected_bait_str=<?php echo $frm_selected_bait_str2;?>&frm_selected_exp_str=<?php echo $frm_selected_exp_str2;?>&displayType=&frm_order_by=ID DESC&start";
}
</script>
</head>
<STYLE TYPE="text/css">
<!--
TD {
  font-family : Arial, Helvetica, sans-serif;
  FONT-SIZE: 10pt;
}
A.button:link
{
        COLOR: #000099;
        FONT-FAMILY: verdana,sans-serif;
        FONT-SIZE: 10pt;
        TEXT-DECORATION: none;
        MARGIN:0 0 0pt 0pt;

}
A.button:visited
{
        COLOR: #000099;
        FONT-FAMILY: verdana,sans-serif;
        TEXT-DECORATION: none;
        FONT-SIZE: 10pt;
        MARGIN:0 0 0pt 0pt;

}
A.button:hover
{
        COLOR: #860223;
        FONT-FAMILY: verdana,sans-serif;
        FONT-SIZE: 10pt;
        TEXT-DECORATION: none;
    MARGIN:0 0 0pt 0pt;
}
-->
</STYLE>

<basefont face="arial">
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 
topMargin=5 rightMargin=5 marginheight="5" marginwidth="5">
<center>
<p></p>
<!-- bait, experiment, bands info table-->
<b><font size="+3"><?php echo $comparisionType;?> Comparison Report</font></b><br><br>
<table border="0" cellpadding="1" cellspacing="1" width="600" bgcolor="white">
<?php 

//bait loop<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
$giCount = 0;
$total_bands = 0;
//--jp--------------------------------------------
$SQL = "SELECT BaitGeneID FROM BaitToHits 
        WHERE ProjectID ='".$_SESSION["workingProjectID"]."'         
        GROUP BY BaitGeneID";
$totalGenes = $mainDB->get_total($SQL);
//------------------------------------------------
for($i=0; $i<count($SelectedBaits); $i++){
   $tmpBaitID = $SelectedBaits[$i]['ID'];
   
   echo "<tr>\n";
   echo "<td width='30%' BGCOLOR='$bg_tb' valign=top><font size=2 face=Courier>\n";
   $GIarr = getGI($mainDB, $SelectedBaits[$i]['GeneID']);
   echo "<b>Bait &nbsp;&nbsp;ID</b>: ". $SelectedBaits[$i]['ID'];
   echo "\n<br><b>Gene name: </b>". $SelectedBaits[$i]['GeneName'];
   echo "\n<br><b>GI number: </b>" . ((count($GIarr))?$GIarr[0]['GI']:'');
   echo "\n<br><b>Clone &nbsp; &nbsp;: </b>". $SelectedBaits[$i]['Clone'];    
   echo "</font>";
   echo "</td>\n";
   echo "<td BGCOLOR='$bg_tb' valign=top><font size=2>\n";
   
   //experiment loop<<<<<<<<<<<<<<<<<<<<<<<<<<<<
   $SQL = "SELECT 
       ID, 
       Name,
       OwnerID,
       DateTime
       FROM Experiment
       WHERE BaitID = '". $SelectedBaits[$i]['ID']."' ORDER BY DateTime";
   $Exps = $HITSDB->fetchAll($SQL);
   
   echo "<table width=350 border=0 cellpadding='0' cellspacing='0' >\n";
   $expCount = 0;
   $userFullNameArr = get_users_ID_Name($HITSDB);
   
   for($k=0;$k < count($Exps); $k++){
     //only display selected experiments
     if(in_array($Exps[$k]['ID'], $frm_exp_arr)){
        $theUser = isset($userFullNameArr[$Exps[$k]['OwnerID']])?$userFullNameArr[$Exps[$k]['OwnerID']]:'';
        $tmpExpID = $Exps[$k]['ID'];
        //-------------------------------------------------------------------------------------
        $BaitExps[$SelectedBaits[$i]['ID']][$expCount++] = array($Exps[$k]['ID'], $Exps[$k]['Name']);
        //-------------------------------------------------------------------------------------
        echo "<td bgcolor='#eeeeee' colspan=2><font size='2'>\n";
        echo "".$Exps[$k]['Name'] . " Created by ". $theUser. "<br>"; 
        echo "</font></td></tr>\n";
        
        //sample loop<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        $results = mysqli_query($HITSDB->link, "select ID,Location,Modification from Band where ExpID='".$Exps[$k]['ID']."' order by ID");
        if(!mysqli_num_rows($results)){//--after testing remove if block. leave  else block.
          echo "<tr>
                 <td colspan=2><font size='2'>the experiment has no sample</font></td>
                </tr>";
        }else{
          echo "<tr>
                <td width=50%><font size='2'><b>sample ID/Location</b></font></td>
                <td width=50%><font size='2'><b>Modification</b></font></td>
              </tr>\n";
        }
        $bandCount = 0;
        if(!isset($BaitBandCount[$tmpBaitID])){
          $BaitBandCount[$tmpBaitID] = 0;
        }
       
        if(!isset($ExpBandCount[$tmpExpID])){
          $ExpBandCount[$tmpExpID] = 0;
        }
        while($row = mysqli_fetch_array($results)){
        
        if($displayType == "" || array_key_exists($row['ID'], $bandGroupArr)){ 
          //-----------------------------------------------------------------------------------------
          $ExpBands[$Exps[$k]['ID']][$bandCount++] = array($row['ID'],$row['Location'],$row['Modification']);
          $BaitBandCount[$tmpBaitID]++;
          $ExpBandCount[$tmpExpID]++;
          //-----------------------------------------------------------------------------------------
          //get hits for the band
          $SQL = "select 
          ID,
          WellID,
          BaitID,
          BandID,
          GeneID,
          LocusTag, 
          HitGI,
          AccType, 
          Expect,
          Expect2,
          MW,
          Pep_num_uniqe, 
          HitName,
          ResultFile, 
          SearchEngine 
          from Hits where BaitID='$tmpBaitID' AND BandID='".$row['ID']."'
          AND (SearchEngine = 'Mascot' OR SearchEngine = 'GPM')  
          order by Expect";
         
          $gi_results = mysqli_query($HITSDB->link, $SQL);
          //$tmpMascotArr = array();
          //$tmpGPMArr = array();
          while($gi_row=mysqli_fetch_array($gi_results)){            
            $tmpGI = trim(preg_replace("/sp\||\#|gi\||\|/", "",$gi_row['HitGI']));
            //---------------------------------------------------
            if($filteGI){
              if(!in_array($tmpGI, $newGIarr)) continue;
            }
            //---------------------------------------------------
            $tmpID = $gi_row['ID'];
            //$tmpGI = $gi_row['HitGI'];
            $tmpWellID = $gi_row['WellID'];
            $tmpBaitID_gi = $gi_row['BaitID'];
            $tmpBandID = $gi_row['BandID'];
            $tmpGeneID = $gi_row['GeneID'];
            $tmpLocusTag = $gi_row['LocusTag'];
            $tmpAccType = $gi_row['AccType'];
            $tmpExpect = $gi_row['Expect'];
            $tmpExpect2 = $gi_row['Expect2'];
            $tmpPepNumUniqe = $gi_row['Pep_num_uniqe'];
            $tmpMW = $gi_row['MW'];
            $tmpHitName = $gi_row['HitName'];
            $tmpResultFile = $gi_row['ResultFile'];
            $tmpSearchEngine = strtoupper($gi_row['SearchEngine']);
            
            
            //-----------------------------------------------------------------------------------
            $LocusTag = '';
            $BaitGeneID = $SelectedBaits[$i]['GeneID'];
            $HitGeneName = '';
            $tmpHitNotes = array();
            if($tmpGeneID){
              $SQL = "SELECT LocusTag, GeneName, BioFilter FROM Protein_Class WHERE  EntrezGeneID=$tmpGeneID";
              $ProteinArr = $proteinDB->fetch($SQL);
              if(count($ProteinArr) && $ProteinArr['LocusTag'] && $ProteinArr['LocusTag'] != "-"){
                $LocusTag = $ProteinArr['LocusTag'];
              }
              if(count($ProteinArr) && $ProteinArr['GeneName']){
                $HitGeneName = $ProteinArr['GeneName'];
              }
              //---Process Bio Filters--------------------------------------------------    
              if(count($ProteinArr) && $ProteinArr['BioFilter']){
                $tmpHitNotes = explode(",", $ProteinArr['BioFilter']);
              }
              //----Process 'BT'-------------------------------------------
              if($BaitGeneID && $tmpGeneID && ($BaitGeneID == $tmpGeneID)){
                array_push($tmpHitNotes, 'BT');
              }
              //---add 1/9-Process FQ and NS-------------------------------------------------------------------
              $SQL = "SELECT Value, FilterAlias FROM ExpFilter WHERE ProjectID ='".$_SESSION["workingProjectID"]."' AND GeneID=$tmpGeneID";
              $ExpFilterArr = $mainDB->fetchAll($SQL);
              if($ExpFilterArr){
                for($n=0; $n<count($ExpFilterArr); $n++){
                  if($ExpFilterArr[$n]['FilterAlias'] == 'FQ'){
                    if($ExpFilterArr[$n]['Value'] && $totalGenes){
                      $HitFrequency = round(($ExpFilterArr[$n]['Value'] / $totalGenes)*100, 2);
                    }  
                  }else if($ExpFilterArr[$n]['FilterAlias'] == 'NS'){
                    array_push($tmpHitNotes, $ExpFilterArr[$n]['FilterAlias']);
                  }
                }  
              }    
            }
            if($tmpID){
            //------Process CO, ME, RI, SO,  ----------------------------------------
              $SQL = "SELECT FilterAlias FROM HitNote WHERE HitID='$tmpID'";
              $HitNoteArr = $mainDB->fetchAll($SQL);
              if($HitNoteArr){
                for($n=0; $n<count($HitNoteArr); $n++){
                  if($HitNoteArr[$n]['FilterAlias']){
                    array_push($tmpHitNotes, $HitNoteArr[$n]['FilterAlias']);
                  }
                }
              }
              //-----------Process OP--------------------------------------------------------------------------
              if(is_one_peptide($tmpID,$HITSDB,$tmpPepNumUniqe)) array_push($tmpHitNotes, "OP");
            }
            $SQL = "SELECT B.BandMW FROM Band B, PlateWell P WHERE B.ID=P.BandID AND P.ID=$tmpWellID";
            $BandMWArr = $mainDB->fetch($SQL);
            $tmpNum = 0;
            if($BandMWArr && $BandMWArr['BandMW']){
              if($BandMWArr['BandMW'] > 0){
          		  $tmpNum = abs(($BandMWArr['BandMW'] - $tmpMW)*100/$BandMWArr['BandMW']);
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
            
            $rc_excluded = 0;
          	if((!isset($frm_BT) || !$frm_BT) && $BaitGeneID && $BaitGeneID == $tmpGeneID){    
          	  $rc_excluded = 0;	 
          	}else if($theaction == 'exclusion' && !in_array(ID_REINCLUDE, $tmpHitNotes)){
          	  if(($frm_Expect_check && $tmpExpect && $tmpExpect <= $frm_Expect) || ($frm_Expect2_check && $tmpExpect2 && $tmpExpect2 >= $frm_Expect2) || (isset($frm_Frequency) && $frm_Frequency and $HitFrequency >= $frequencyLimit)){
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
            if(!$rc_excluded){//=================
              $tmpCounter++;
              //---------------------------------------------------------------------------------------------------------
              if(!array_key_exists($tmpGI, $giInfoArr)){
                $giInfoArr[$tmpGI]['AccType'] = $tmpAccType; 
                $giInfoArr[$tmpGI]['GeneID'] = $tmpGeneID;
                $giInfoArr[$tmpGI]['LocusTag'] = $tmpLocusTag;
              }
              if(!isset($giInfoArr[$tmpGI]['engine'])){
                $giInfoArr[$tmpGI]['engine'] = $tmpSearchEngine;
              }else{
                if(!stristr($giInfoArr[$tmpGI]['engine'], $tmpSearchEngine)){
                  if($giInfoArr[$tmpGI]['engine']) $giInfoArr[$tmpGI]['engine'] .= ",";
                  $giInfoArr[$tmpGI]['engine'] .= $tmpSearchEngine;
                }
              }
              $BandHits[$tmpGI][$tmpBaitID_gi][$tmpExpID][$tmpBandID][$tmpSearchEngine][$tmpResultFile] = array($tmpID,$tmpPepNumUniqe);
              if(!in_array($tmpGI, $UniHitGIs)){
                $UniHitGIs[$giCount] = $tmpGI;
                $UniHitGeneAccessions[$giCount] = array($HitGeneName, '', $tmpHitName,$tmpGeneID);
                $giCount++;
              }
            }
          }
          echo "<tr>
          <td><font size='2'>".$row['ID']. " / ". $row['Location'] ."</font></td>
          <td><font size='2'>".$row['Modification']."</font></td>
          </tr>";
        }//end of bands
        }
     }//end if
   }//end exp loop
   echo "</table>\n";
   echo "</td>\n";
   echo "</tr>\n";
   echo "<tr><td colspan=2><hr size=1 color=></td></tr>\n";
}//end bait for loop

?>
</table>

<FORM ACTION="<?php echo $PHP_SELF;?>" NAME="form_comparison" METHOD="POST">
<INPUT TYPE="hidden" NAME="frm_selected_bait_str" VALUE="<?php echo $frm_selected_bait_str?>">
<INPUT TYPE="hidden" NAME="frm_selected_exp_str" VALUE="<?php echo $frm_selected_exp_str;?>">
<INPUT TYPE="hidden" NAME="frm_selected_bait_str2" VALUE="<?php echo $frm_selected_bait_str2?>">
<INPUT TYPE="hidden" NAME="frm_selected_exp_str2" VALUE="<?php echo $frm_selected_exp_str2;?>">
<INPUT TYPE="hidden" NAME="GI" VALUE="">
<INPUT type=hidden name=theaction value='<?php echo $theaction;?>'>
<INPUT TYPE="hidden" NAME="frm_order_by" VALUE="<?php echo $frm_order_by?>">
<INPUT TYPE="hidden" NAME="groupedBandIDstr" VALUE="<?php echo $groupedBandIDstr;?>">
<INPUT TYPE="hidden" NAME="displayType" VALUE="<?php echo $displayType;?>">
<INPUT TYPE="hidden" NAME="filteGI" VALUE="<?php echo $filteGI;?>">

<table border=0 cellspacing="1" cellpadding="0">
<?php 
include("filterSelection.inc.php");
?>
 <!--tr>
      <td colspan=2>&nbsp;
          <input type=button value='No Exclusion' class=black_but onClick='javascript: NoExclusion();'>
          <input type=button value='Apply Exclusion' class=black_but onClick='javascript: applyExclusion();'>
      </td>          
  </tr--> 
</table><br>
<table border=0 cellspacing="0" cellpadding="2">
<tr>
  <td>Mascot Hit with # of unique peptides</td>
  <td align=center><img src=./images/icon_circle_red.gif borde=0></td>
  <td>&nbsp; &nbsp; GPM Hit with # of unique peptides</td>
  <td align=center><img src=./images/icon_square_red.gif borde=0></td>
  <td>&nbsp;</td>
  <td colspan=2 align=right>&nbsp;&nbsp;</td>
</tr>
<tr height=8>
<?php if($displayType == 'Control'){?>
  <td>Hits found in both control group and any other groups</td>
  <td width=40 align=center bgcolor='<?php echo $red;?>'><input type="checkbox" name="frm_red" value="y" <?php echo ($frm_red)?'checked':'';?>></td>
  <td nowrap colspan=3>&nbsp; &nbsp; Hits found in control group or any other groups</td>
  <td width=40 align=center bgcolor='<?php echo $blue;?>'><input type="checkbox" name="frm_blue" value="y" <?php echo ($frm_blue)?'checked':'';?>></td>
<?php }else{?>  
  <td>Hit found in all <?php echo ($displayType)?$displayType:$comparisionType;?>s</td>
  <td width=40 align=center bgcolor='<?php echo $red;?>'><input type="checkbox" name="frm_red" value="y" <?php echo ($frm_red)?'checked':'';?>></td>
  <td>&nbsp; &nbsp; more than one <?php echo ($displayType)?$displayType:$comparisionType;?></td>
  <td width=40 align=center bgcolor='<?php echo $green;?>'><input type="checkbox" name="frm_green" value="y" <?php echo ($frm_green)?'checked':'';?>></td>
  <td nowrap>&nbsp; &nbsp; one <?php echo ($displayType)?$displayType:$comparisionType;?></td>
  <td width=40 align=center bgcolor='<?php echo $blue;?>'><input type="checkbox" name="frm_blue" value="y" <?php echo ($frm_blue)?'checked':'';?>></td>
<?php }?>  
  <td rowspan=2 width=100 align=right valign=bottom>
    <input type="submit" name="sub" value="    Go    " onClick='javascript: applyExclusion();'>
    <input type="button" name="go_default" value=" Default " onClick='javascript: to_default();'>
  <td>
</tr>
<tr height=8>
  <td>Display Mascot hits </td>
  <td width=40 align=center><input type="checkbox" name="frm_Display_Mascot_hits" value="y" <?php echo ($frm_Display_Mascot_hits)?'checked':'';?>></td>
  <td>&nbsp; &nbsp; Display GPM hits</td>
  <td width=50 align=center ><input type="checkbox" name="frm_Display_GPM_hits" value="y" <?php echo ($frm_Display_GPM_hits)?'checked':'';?>></td>
  <td>&nbsp; &nbsp;</td>
  <td width=30 align=center ><input type="button" name="go_default" value=" Help " onClick="javascript: popwin('./help.php#comparison.php', 770, 600);"></td>
</tr>
<tr><td colspan=7 align=right>
[<a href='javascript: popImage();'>Print Report</a>] &nbsp;
[<a href="./export_comparison.php?infileName=<?php echo $file;?>&spliter=<?php echo $spliter;?>">Export Report</a>] &nbsp;
[<a href="./export_osprey.php?infileName=<?php echo $file;?>&spliter=<?php echo $spliter;?>">Export Osprey</a>]
</td></tr>
</table>

<!-- hits list table -->
<table align="center" bgcolor='#acacac' cellspacing="1" cellpadding="0" border="0" width=90%>
<tr>
    <?php 
    $titlesStr = '';
    for($i=0; $i < count($SelectedBaits); $i++){
      $total_bands += $BaitBandCount[$tmpBaitID];
      $num = $i%2;
      $str = "bgColor_$num";
      $tmpBaitColor = $$str;
      $tmpBaitID = $SelectedBaits[$i]['ID'];
      $tmpGeneName = $SelectedBaits[$i]['GeneName'];
      if($BaitBandCount[$tmpBaitID] > 0){
    ?>
    <td colspan="<?php echo $BaitBandCount[$tmpBaitID];?>" bgcolor='<?php echo $tmpBaitColor;?>' align=center><font color=#008000><b>Bait ID:<?php echo $tmpBaitID;?>(<?php echo $tmpGeneName;?>)</b></font></td>
  <?php   }
    }//end loop?>
    <td bgcolor=#ffffff colspan=3 rowspan=2 align=center><b>Hits</b></td>
</tr>
<tr>
    <?php 
    if($total_bands){
      $td_width = 70/$total_bands;
    }  
    $allBaitStr = '';
    for($i=0; $i < count($SelectedBaits); $i++){
        $num = $i%2;
        $str = "bgColor_$num";
        $tmpExpColor = $$str;
      //-----------------------------------------------  
      $tmpBaitID = $SelectedBaits[$i]['ID'];
      $tmpGeneName = $SelectedBaits[$i]['GeneName'];
      $tmpBandInaBait = $BaitBandCount[$tmpBaitID];
      $BaitStr = "Bait ID: $tmpBaitID ($tmpGeneName)$tmpBandInaBait";
      //------------------------------------------------
      $tmpExpCount = count($BaitExps[$tmpBaitID]);
      $expStr = '';
      for($n = 0; $n < $tmpExpCount; $n++){
        if($comparisionType == 'Experiment'){
          $num = $n%2;
          $str = "bgColor_$num";
          $tmpExpColor = $$str;
        }
        //------------------------------------------------
        $tmpExpID = $BaitExps[$tmpBaitID][$n][0];
        $tmpExpName = $BaitExps[$tmpBaitID][$n][1];
        $tmpBandInaExp = $ExpBandCount[$tmpExpID];
        $tmpExpStr = $tmpExpName.",,".$tmpBandInaExp;
        if($expStr) $expStr .= ";;";
        $expStr .= $tmpExpStr;
        //------------------------------------------------
        if($ExpBandCount[$tmpExpID]> 0){
    ?>
          <td colspan="<?php echo $ExpBandCount[$tmpExpID];?>" bgcolor='<?php echo $tmpExpColor;?>' align=center><?php echo $BaitExps[$tmpBaitID][$n][1];?></td>
  <?php     }
      }
      $oneBait = $BaitStr."%%".$expStr;
      if($allBaitStr) $allBaitStr .= "##";
      $allBaitStr .= $oneBait;
    }
    ?>
</tr>
<tr>
    <?php 
    $out_Total_samples_str = 0;
    $out_Sample_Names_str = '';
    $groupSelectArr = array();
    for($i=0; $i < count($SelectedBaits); $i++){
      $tmpBaitID = $SelectedBaits[$i]['ID'];
      $tmpExpCount = count($BaitExps[$tmpBaitID]);
      for($n = 0; $n < $tmpExpCount; $n++){
        $tmpExpID = $BaitExps[$tmpBaitID][$n][0];
        for($m = 0; $m < $ExpBandCount[$tmpExpID]; $m++){
          $tmpBandID = $ExpBands[$tmpExpID][$m][0];
          $tmpBandLocation = $ExpBands[$tmpExpID][$m][1];
          $tmpBandModification = $ExpBands[$tmpExpID][$m][2];
      	  $out_Total_samples_str++;
          if($out_Sample_Names_str) $out_Sample_Names_str .= $spliter;
      	  $out_Sample_Names_str .= $tmpBandLocation;
          $tmpSelectArr = array();
          $tmpSelectArr[0] = $tmpBaitID;
          $tmpSelectArr[1] = $tmpExpID;
          $tmpSelectArr[2] = $tmpBandID;
          array_push($groupSelectArr, $tmpSelectArr);
    ?>
         <td align=center bgcolor=#000000 width='<?php echo $td_width;?>%'><font color=white size=2><b><?php echo $tmpBandLocation;?></b></font></td>
    <?php   
        } //end band loop
      }//end exp loop
    }//end bait loop
    
    ?>
    <td align=center bgcolor=#000000 width=10%><font color=white size=2><b>Gene Name</b></font></td>
    <td align=center bgcolor=#000000 width=10%><font color=white size=2><b>Protein ID</b></font></td>
    <td align=center bgcolor=#000000 width=10%><font color=white size=2><b>Links</b></font></td>
</tr>
<tr>
  <?php foreach($groupSelectArr as $key => $value){?>
    
    <?php if($displayType == ''){?> 
      <td align=center bgcolor=#000000 width='<?php echo $td_width;?>%'>
        <select name=groupSelect_<?php echo $key+1?> id="<?php echo $value[0]?>_<?php echo $value[1]?>_<?php echo $value[2]?>">
          <?php group_select_box($out_Total_samples_str);?>
        </select>
      </td>  
    <?php }else{
          if($out_Sgroups_Name_str) $out_Sgroups_Name_str .= $spliter;
          $out_Sgroups_Name_str .= $bandGroupArr[$value[2]];
    ?>
      <td align=center width='<?php echo $td_width;?>%'>
          <?php echo $bandGroupArr[$value[2]];?>  
      </td>
    <?php }?>      
    </td>
  <?php }?>
    <td align=center bgcolor=#000000 width=10%><font color=white size=2><b>&nbsp;&nbsp;</b></font></td>
    <td align=center bgcolor=#000000 width=10%><font color=white size=2><b>&nbsp;&nbsp;</b></font></td>
    <td align=center bgcolor=#000000 width=10%><font color=white size=2><b>&nbsp;&nbsp;</b></font></td>
</tr>
<?php 
  $MascotDisplay = 0;
  $GPMDiaplay = 0;
  //hit table start here 
  $out_gi_list_str = '';
  $out_Total_GIs_str = 0;
  $sample_counter = 0;
  $out_Background_str = '';
  for($gi_count = 0; $gi_count < count($UniHitGIs); $gi_count++){
    $tmpGI = $UniHitGIs[$gi_count];
    $thisEnginesArr = explode(",",$giInfoArr[$tmpGI]['engine']);
    if(!$frm_Display_Mascot_hits && !$frm_Display_GPM_hits){
      continue;
    }elseif($frm_Display_Mascot_hits && !$frm_Display_GPM_hits){
      if(count($thisEnginesArr) == 1 && !strcasecmp($thisEnginesArr[0], 'GPM')) continue;
    }elseif(!$frm_Display_Mascot_hits && $frm_Display_GPM_hits){
      if(count($thisEnginesArr) == 1 && !strcasecmp($thisEnginesArr[0], 'Mascot')) continue;
    }

    $maxNumHits = 0;
    //find out ball color for the hits
    if($displayType != ''){
      if($displayType == 'Group'){
        $groupArr = array();
        foreach($bandGroupArr as $value){
          if(!array_key_exists($value, $groupArr)){
            $groupArr[$value] = 0;
          }  
        }  
        foreach($BaitExps as $baitKey => $baitValue){
          $tmpBaitID = $baitKey;
          foreach($baitValue as $expValul){
            $tmpExpID = $expValul[0];
            foreach($ExpBands[$tmpExpID] as $bandValue){
              $tmpBandID = $bandValue[0];
              if(isset($BandHits[$tmpGI][$tmpBaitID][$tmpExpID][$tmpBandID]) && count($BandHits[$tmpGI][$tmpBaitID][$tmpExpID][$tmpBandID]) > 0){
                $groupArr[$bandGroupArr[$tmpBandID]] = 1;
              }
            }
          }
        }
        $tmpGroupCounter = 0;
        foreach($groupArr as $value){
          if($value == 1) $tmpGroupCounter++;
        }
        if($tmpGroupCounter == count($groupArr)){
          $ball_color = 'red';
        }else if($tmpGroupCounter >= 2){
          $ball_color = 'green';
        }else{
          $ball_color = 'blue';
        }
      }else if($displayType == 'Control'){
        $controlFlag = 0;
        $otherFlag = 0;
        foreach($BaitExps as $baitKey => $baitValue){
          $tmpBaitID = $baitKey;
          foreach($baitValue as $expValul){
            $tmpExpID = $expValul[0];
            foreach($ExpBands[$tmpExpID] as $bandValue){
              $tmpBandID = $bandValue[0];
              if(isset($BandHits[$tmpGI][$tmpBaitID][$tmpExpID][$tmpBandID]) && count($BandHits[$tmpGI][$tmpBaitID][$tmpExpID][$tmpBandID]) > 0){
                if($bandGroupArr[$tmpBandID] == 'CTL') $controlFlag = 1;
                if($bandGroupArr[$tmpBandID] != 'CTL') $otherFlag = 1;
                if($controlFlag && $otherFlag) break;
              }
            }
          }
        }
        $bothFlag = $controlFlag * $otherFlag;
        if($bothFlag){
          $ball_color = 'red';
        }else{
          $ball_color = 'blue';
        }
      }  
    }else if($comparisionType == 'Bait'){
       $band_num = 0;
       for($i=0; $i < count($SelectedBaits); $i++){
         $tmpBaitID = $SelectedBaits[$i]['ID'];
         if(isset($BandHits[$tmpGI][$tmpBaitID]) && count($BandHits[$tmpGI][$tmpBaitID]) > 0){
           $band_num++;
         }
       }
       if(count($SelectedBaits) <= $band_num){
         $ball_color = 'red';
       }else if($band_num > 1){
         $ball_color = 'green';
       }else{
         $ball_color = 'blue';
       }
    }else  if($comparisionType == 'Experiment'){ 
       //only has one bait
       $band_num = 0;
       $tmpBaitID = $SelectedBaits[0]['ID'];
       $tmp_exp_count = count($BaitExps[$tmpBaitID]);
       for($i=0; $i < $tmp_exp_count; $i++){
         $tmpExpID = $BaitExps[$tmpBaitID][$i][0];
         if(isset($BandHits[$tmpGI][$tmpBaitID][$tmpExpID]) && count($BandHits[$tmpGI][$tmpBaitID][$tmpExpID]) > 0){
           $band_num++;
         }
       }
       if($tmp_exp_count <= $band_num){
         $ball_color = 'red';
       }else if($band_num > 1){
         $ball_color = 'green';
       }else{
         $ball_color = 'blue';
       }
    }else if($comparisionType == 'Sample'){
     //only has one bait and one experiment
       $band_num = 0;
       $tmpBaitID = $SelectedBaits[0]['ID'];
       $tmpExpID = $BaitExps[$tmpBaitID][0][0];
       $tmp_band_count = $ExpBandCount[$tmpExpID];
       for($i=0; $i < $tmp_band_count; $i++){
         $tmpBandID = $ExpBands[$tmpExpID][$i][0];
         if(isset($BandHits[$tmpGI][$tmpBaitID][$tmpExpID][$tmpBandID]) && count($BandHits[$tmpGI][$tmpBaitID][$tmpExpID][$tmpBandID]) > 0){
           $band_num++;
         }
       }
       if($tmp_band_count <= $band_num){
         $ball_color = 'red';
       }else if($band_num > 1){
         $ball_color = 'green';
       }else{
         $ball_color = 'blue';
       }
    }
    if(!$frm_red and $ball_color == 'red') continue;
    if(!$frm_green and $ball_color == 'green') continue;
    if(!$frm_blue and $ball_color == 'blue') continue;
    
    $out_gi_list_str .= $ball_color.$spliter;
    ?>
<tr>
 <?php  
    //bait loop
    for($i=0; $i < count($SelectedBaits); $i++){
      if($comparisionType == 'Bait'){
        $num = $i%2;
        $str = "bgColor_$num";
        $tmpBandColor = $$str;
      }
      $tmpBaitID = $SelectedBaits[$i]['ID'];
      $tmpExpCount = count($BaitExps[$tmpBaitID]);
      //exp loop
      for($n = 0; $n < $tmpExpCount; $n++){
        if($comparisionType == 'Experiment'){
          $num = $n%2;
          $str = "bgColor_$num";
          $tmpBandColor = $$str;
        }
        $tmpExpID = $BaitExps[$tmpBaitID][$n][0];
        //band loop
	
        for($m = 0; $m < $ExpBandCount[$tmpExpID]; $m++){
	        $cell_has_hit = '';
          $numOfHits = 0;
          if($comparisionType == 'Sample'){
            $num = $m%2;
            $str = "bgColor_$num";
            $tmpBandColor = $$str;
          }
          $tmpBandID = $ExpBands[$tmpExpID][$m][0];
          if($displayType == 'Control'){
            if($bandGroupArr[$tmpBandID] == 'CTL'){
              $oldBandColor = $tmpBandColor;
              $tmpBandColor = $controlBandColor;
            }  
          }
          $tmpBandLocation = $ExpBands[$tmpExpID][$m][1];
          $tmpBandModification = $ExpBands[$tmpExpID][$m][2];
          $MascotExpectArr = array();
          $GPMExpectArr = array();
          if(isset($BandHits[$tmpGI][$tmpBaitID][$tmpExpID][$tmpBandID]['MASCOT']) && $BandHits[$tmpGI][$tmpBaitID][$tmpExpID][$tmpBandID]['MASCOT'] && $frm_Display_Mascot_hits){
            $MascotExpectArr = $BandHits[$tmpGI][$tmpBaitID][$tmpExpID][$tmpBandID]['MASCOT'];
          }
          if(isset($BandHits[$tmpGI][$tmpBaitID][$tmpExpID][$tmpBandID]['GPM']) && $BandHits[$tmpGI][$tmpBaitID][$tmpExpID][$tmpBandID]['GPM'] && $frm_Display_GPM_hits){
            $GPMExpectArr = $BandHits[$tmpGI][$tmpBaitID][$tmpExpID][$tmpBandID]['GPM'];
          }
          $hitInBand = count($MascotExpectArr) + count($GPMExpectArr);
          $imgTableW = (17 + 4) * $hitInBand;
          echo "<td bgcolor='$tmpBandColor' align=center>\n";
          echo "<table border=0 cellspacing=2 cellpadding=0 width=$imgTableW><tr>\n";
          if($MascotExpectArr){
            $mTmpStr = '';
            //echo "<td><font size=1>&nbsp;</font></td>";
            foreach($MascotExpectArr as $key => $value){
              $thecolor = 'icon_circle_' . $ball_color;
            ?>
                  <td align=center width="17" height="17" background="./images/<?php echo $thecolor?>.gif">
                  <a href="javascript:add_notes('<?php echo $value[0]?>');" class=button><?php echo ($value[1])?$value[1]:"&nbsp;&nbsp;"?></a></td>
            <?php 
              if(!$value[1]) $value[1] = 0;
              if($mTmpStr != '') $mTmpStr .= ';';
              $mTmpStr .= $value[1];
              $numOfHits++;
              $numOfPep = format_pep_num($value[1]);
            }
            if($mTmpStr != '') $cell_has_hit .= 'm:' . $mTmpStr;
          }
          if($GPMExpectArr){
            $gTmpStr = '';
            foreach($GPMExpectArr as $key => $value){
              $thecolor = 'icon_square_' . $ball_color;
            ?>
                  <td align=center width="17" height="17" background="./images/<?php echo $thecolor?>.gif">
                  <a href="javascript:add_notes('<?php echo $value[0]?>');" class=button><?php echo ($value[1])?$value[1]:"&nbsp;&nbsp;"?></a></td>
            <?php 
              if(!$value[1]) $value[1] = 0;
              if($gTmpStr != '') $gTmpStr .= ';';
              $gTmpStr .= $value[1];
              $numOfHits++;
              $numOfPep = format_pep_num($value[1]);
            }
            if($gTmpStr != ''){
              if($cell_has_hit != ''){
                $cell_has_hit .= '@g:' . $gTmpStr;
              }else{
                $cell_has_hit .= 'g:' . $gTmpStr;
              }
            } 
          }
          if($cell_has_hit) $cell_has_hit = "h#" . $cell_has_hit;
          echo "</tr></table>";
          if(!count($MascotExpectArr) && !count($GPMExpectArr)){
            echo "&nbsp; &nbsp; &nbsp;";
          }
          echo "</td>\n";
          
      	  $sample_counter++;
      	  if($sample_counter <= $out_Total_samples_str){
            if($out_Background_str) $out_Background_str .= $spliter;
            if($tmpBandColor == $controlBandColor){
              $out_Background_str .= ($oldBandColor == $bgColor_0)?'light_yellow':'dark_yellow';
            }else{
      	      $out_Background_str .= ($tmpBandColor == $bgColor_0)?'light':'dark';
            }  
      	  }
      	  $out_gi_list_str .= "$cell_has_hit".$spliter;
          
          if($numOfHits > $maxNumHits) $maxNumHits = $numOfHits;
          if($displayType == 'Control'){
            if($bandGroupArr[$tmpBandID] == 'CTL'){
              $tmpBandColor = $oldBandColor;
            }  
          }
        } //end band loop
      }//end exp loop
    }//end bait loop
    $out_Total_GIs_str++;
    $out_gi_list_str .= $UniHitGeneAccessions[$gi_count][0] . $spliter;
    $out_gi_list_str .= $tmpGI . $spliter .$maxNumHits . "\r\n";
    $urlGeneID = $UniHitGeneAccessions[$gi_count][3];
    echo  "<td bgcolor=#ffffff nowrap>&nbsp;";
    echo $UniHitGeneAccessions[$gi_count][0];
    echo "</td>\n";
    if($filteGI < 2){
      if($GIstr) $GIstr .= ',';
      $GIstr .= $tmpGI;
    }  
    ?>
    <td bgcolor='<?php echo $$ball_color;?>' nowrap onmouseover="showTip(event,'Q<?php echo $tmpGI;?>','<?php echo $AccTableW;?>');" onmouseout="hideTip('Q<?php echo $tmpGI;?>');">&nbsp;
      <?php echo $tmpGI;?>
    </td>
   <?php 
    echo "<td bgcolor=#ffffff nowrap>";
    echo get_URL_str($tmpGI, $urlGeneID, $giInfoArr[$tmpGI]['LocusTag']);
    echo "</td>\n"; 
   ?>
</tr>
<?php }
fwrite($fd, "Total samples:$out_Total_samples_str\r\n");
fwrite($fd, "Total GIs:$out_Total_GIs_str\r\n");
fwrite($fd, "Sample Names:$out_Sample_Names_str\r\n");
if($out_Sgroups_Name_str){
  fwrite($fd, "Group Names:$out_Sgroups_Name_str\r\n");
}
fwrite($fd, "Background:$out_Background_str\r\n");
fwrite($fd, "titals:$allBaitStr\r\n");
fwrite($fd, "GI list:\r\n");
fwrite($fd, $out_gi_list_str);
fclose($fd);

?>
</table>
<INPUT TYPE="hidden" NAME="GIstr" VALUE="<?php echo $GIstr?>">
</form>
<a href='javascript: window.close();' class=button>[Close Window]</a>
</center>
<?php 
//create DIV for all GIs
for($gi_count = 0; $gi_count < count($UniHitGIs); $gi_count++){
  $UniHitGeneAccessions[$gi_count][2] = str_replace(';',";<br>",$UniHitGeneAccessions[$gi_count][2]);
?>
<DIV CLASS='tooltip' ID='Q<?php echo $UniHitGIs[$gi_count];?>'><pre>
<TABLE BORDER=0 CELLPADDING=1 CELLSPACING=0 BGCOLOR="#ff9900" width=<?php echo $AccTableW;?>>
  <TR><TD>
    <table cellspacing='1' cellpadding='0' border='0' width=100% BGCOLOR="#e2dd9c">
      <tr>
        <td colspan='4'><div class=middle><?php echo $UniHitGeneAccessions[$gi_count][2];?></div></td>
      </tr> 
   </table>
  </TD></TR>
</TABLE>
</pre></DIV>
<?php 
}//end for

function format_pep_num($pepNum){ 
  if(strlen($pepNum) == 1){
    $numOfPep = "&nbsp;" . $pepNum . "&nbsp;";
  }else{
    $numOfPep = $pepNum;
  }
  return $numOfPep;
}
function group_select_box($groupNum=10){
?>
    <option value="">&nbsp;</option>
    <option value="CTL">CTL</option>
<?php for($i=1; $i<=$groupNum; $i++){?>
    <option value="G<?php echo $i?>">G<?php echo $i?></option>
<?php 
  }
}
?>

</BODY>
</HTML>
