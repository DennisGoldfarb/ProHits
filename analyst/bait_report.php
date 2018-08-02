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
$is_reincluded = '';
$typeBioArr = array();
$typeExpArr = array();
$typeFrequencyArr = '';
$frm_Expect_check = '';
$frm_Expect2_check = '';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php"); 
require("analyst/site_header.php");
require_once("msManager/is_dir_file.inc.php");

if(!$Bait_ID) {
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

$outDir = "../TMP/bait_report/";
if(!_is_dir($outDir)) _mkdir_path($outDir);

$filename = $outDir.$_SESSION['USER']->Username."_bait.csv";
if (!$handle = fopen($filename, 'w')){
  echo "Cannot open file ($filename)";
  exit;
}
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
$oldDBName = to_defaultDB($mainDB);
$SQL = "SELECT FilterNameID FROM Filter WHERE FilterSetID='" . $_SESSION['workingFilterSetID'] . "' ORDER BY FilterNameID";
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

//processing move bait
if($whichBait == 'last'){
  $Bait_ID = move_bait($mainDB, 'last');
}else if($whichBait == 'first'){
  $Bait_ID = move_bait($mainDB, 'first');
}else if($whichBait == 'next' and $Bait_ID){
  $Bait_ID = move_bait($mainDB, 'next', $Bait_ID);
}else if($whichBait == 'previous' and $Bait_ID){
  $Bait_ID = move_bait($mainDB, 'previous', $Bait_ID);
}

$SQL = "SELECT 
  ID, 
  GeneID,
  LocusTag,
  GeneName, 
  BaitAcc,
  BaitMW, 
  Clone, 
  Vector, 
  Description,
  GelFree 
  FROM Bait where ID='$Bait_ID'AND ProjectID=$AccessProjectID";  
$Bait = $mainDB->fetch($SQL);
 
$SQL = "SELECT 
  ID, 
  Name, 
  OwnerID,
  PreySource, 
  DateTime 
  FROM Experiment
  WHERE BaitID = '$Bait_ID' and ProjectID=$AccessProjectID";    	
  
$Exps = $mainDB->fetchAll($SQL);

$bgcolordark = "#94c1c9";
$bgcolor = "white";
//set default exclusion

$usersArr = get_users_ID_Name($mainDB);

?>
<script language='javascript'>
function print_view(theTarget){
  theForm = document.Bait_form;  
  theForm.theaction.value = '<?php echo $theaction;?>';
  theForm.action = theTarget
  theForm.target = "_blank";
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
  theForm = document.Bait_form;
  if(typeof(theForm.frm_Frequency) != 'undefined'){
    if(theForm.frm_Frequency.checked == true){
      var frequency = theForm.frequencyLimit.value;
      if(!is_numberic(frequency)){
        alert("Please enter numbers or uncheck checkbox on frequency field.");
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
  theForm = document.Bait_form;
  theForm.theaction.value = '';
  theForm.action = '<?php echo $_SERVER['PHP_SELF'];?>';
  theForm.target = "_self";
  theForm.submit();
}
function pop_filter_set(filter_ID){   
  file = 'mng_set.php?filterID=' + filter_ID;
  window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=500,height=620');
}
function pop_exp_filter_set(filter_ID){
  if(filter_ID == '12'){ 
    file = 'mng_set_non_specific.php?filterID=' + filter_ID;
  }else{
    file = 'mng_set.php?filterID=' + filter_ID;
  }  
  window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=500,height=620');
}
function pop_Frequency_set(){   
  file = 'mng_set_frequency.php';
  window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=500,height=620');
}
 
function move_bait(whichBait){
  var theForm =  document.Bait_form
  if(whichBait == 'last') { 
    theForm.whichBait.value = 'last';
  } else if(whichBait == 'first') {
    theForm.whichBait.value = 'first';
  } else if(whichBait == 'next') {
    theForm.whichBait.value = 'next';
  } else if(whichBait == 'previous') {
    theForm.whichBait.value = 'previous';
  }
  theForm.action = '<?php echo $_SERVER['PHP_SELF'];?>';
  theForm.target = "_self";
  theForm.theaction.value = 'exclusion';
  theForm.submitted.value = 0;
  theForm.submit();
}
function updateFrequency(){
  if(typeof(newWin2) == 'object'){
      newWin2.close();
      theForm.target='_parent';
  }
  file = "./mng_set_frequency.php?theaction=update_only";
  nWin = window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=300');
  nWin.moveTo(4,0);
} 
</script>
   <form name=Bait_form action=<?php echo $_SERVER['PHP_SELF'];?> method=post>  
   <input type=hidden name=Bait_ID value='<?php echo $Bait_ID;?>'>
   <input type=hidden name=theaction value='<?php echo $theaction;?>'>
   <input type=hidden name=sub value=<?php echo $sub;?>>
   <input type=hidden name=submitted value='1'>
   <input type=hidden name=whichBait value=''>

<table border=0 cellpadding="0" cellspacing="0" width="97%">
  <tr>
    <td colspan=2><div class=maintext>
      <img src="images/icon_carryover_color.gif"> Exclusion Color &nbsp;&nbsp;
      <img src="images/icon_picture.gif"> Gel image &nbsp;&nbsp;
      <img src="images/icon_Mascot.gif"> Mascot &nbsp;&nbsp;
      <img src="images/icon_GPM.gif"> GPM &nbsp;&nbsp;
      <img src="images/icon_notes.gif"> Hit Notes &nbsp;&nbsp;
    	<img src='./images/icon_coip_green.gif'> Yes &nbsp;&nbsp;
    	<img src='./images/icon_coip_red.gif'> No &nbsp;&nbsp;
    	<img src='./images/icon_coip_yellow.gif'> Possible &nbsp;&nbsp;
    	<img src='./images/icon_coip_blue.gif'> In Progress
      </div><BR>
    </td>
  </tr>
  <tr>
    <td align="left">
    <font color="navy" face="helvetica,arial,futura" size="3"><b>Bait Reported Hits
    <?php 
    if($AccessProjectName){
      echo "  <br><font color='red' face='helvetica,arial,futura' size='3'>(Project: $AccessProjectName)</font>";
    }
    ?>
    </b> 
    </font> 
    </td>
    <td align="right">
      <a href="./export_peptides.php?infileName=<?php echo $filename;?>&table=bait" class=button>[Export Peptides]</a>
      <a href="export_file.php?file=<?php echo $filename;?>" class=button>[Export bait Report]</a>           
      <!--a href="javascript: print_view('bait_report_hit_list.php?frequencyLimit=<?php echo $frequencyLimit;?>');" class=button>[Export hit list]</a--> 
      <a href="javascript: print_view('bait_report_print_view.php?frequencyLimit=<?php echo $frequencyLimit;?>');" class=button>[Print Preview]</a> 
      <a href="./bait.php<?php echo ($sub)?"?sub=$sub":"";?>" class=button>[Back to Bait List]</a>
    </td>
  </tr>
  
  <tr>
    <td colspan=2 height=0 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="left" colspan=2><br>    
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
                <td width=100><div class=large>&nbsp;<b><?php echo $Bait['GeneName'];?></b> &nbsp;</div></td></td>
                <td width=200>
                  <a href="javascript: move_bait('first');">
                  <img src="./images/icon_first.gif" border=0 valign="bottom"></a>&nbsp;
                  <a href="javascript: move_bait('previous');">
                  <img src="./images/icon_previous.gif" border=0 valign="bottom"></a>&nbsp;
                  <a href="javascript: move_bait('next');">
                  <img src="./images/icon_next.gif" border=0 valign="bottom"></a>&nbsp;
                  <a href="javascript: move_bait('last');">
                  <img src="./images/icon_last.gif" border=0 valign="bottom"></a>&nbsp;</td>
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
                <td>
                <?php 
                echo get_URL_str($Bait['BaitAcc'], $Bait['GeneID'], $Bait['LocusTag']);
                ?>                 
                </td>
              </tr>
              <tr>
               <td colspan=2>Bait Description:</td>
               <td colspan=5><div class=maintext><?php echo $Bait['Description'];?></div></td>
              </tr>
            </table>
          </td>
        </tr>
       </table>
    </td>
  </tr>
<?php 
$BaitDescription = str_replace(",", ";", $Bait['Description']);
$BaitDescription = str_replace("\n", "", $BaitDescription);
$Bait['LocusTag'] = str_replace(",", ";", $Bait['LocusTag']);
$Bait['GeneName'] = str_replace(",", ";", $Bait['GeneName']);
$Bait['Clone'] = str_replace(",", ";", $Bait['Clone']);
$Bait['Vector'] = str_replace(",", ";", $Bait['Vector']);

fwrite($handle, "Bait ID: ".$Bait_ID.",Gene ID: ".$Bait['GeneID'].",Locus Tag: ".$Bait['LocusTag'].",Gene: ".$Bait['GeneName'].",Clone: ".$Bait['Clone']);
fwrite($handle, ",Vector: ".$Bait['Vector'].",MW: ".$Bait['BaitMW'].",Bait Description: $BaitDescription\n\n");
?>            
  <tr>
    <td align="left" colspan=2><br>    
      <table border=0 cellspacing="0" cellpadding="2" width=97%>     
        <tr>
          <td >
            <table border=0>
  	          <tr>
  	            <td valign=top>
  		            <table border=0>
  	                <tr>
  	                  <td colspan=6><div class=maintext><b>Experiments</b></div></td>
  	                </tr>
          	        <tr>
          	          <td><div class=maintext>Exp ID</div></td>
          	          <td><div class=maintext>Name/Batch Name</div></td>
                      <td><div class=maintext>Gels Link</div></td>
          	          <td><div class=maintext>Condition</div></td>
          	          <td><div class=maintext>Input by</div></td>
          	          <td><div class=maintext>Date</div></td>
          	        </tr>
  	        <?php 
            //Experiments info-------------------------------------------
            fwrite($handle, "Experiments\n");
            fwrite($handle, "Exp ID,Name/Batch Name,Condition,Inputed by,Date,Prey Source\n"); 
  	        for($i=0; $i<count($Exps); $i++){
  	          $theUser = get_userName($mainDB, $Exps[$i]['OwnerID']);
              $SQL = "SELECT C.ID, 
                C.`Condition`  
                FROM `Condition` C , ExpCondition E 
                WHERE C.ID = E.ConditionID and E.ExpID=".$Exps[$i]['ID']." ORDER BY C.ID";
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
          	          <td><div class=maintext><?php echo $Exps[$i]['ID'];?></div></td>
          	          <td><div class=maintext><?php echo $Exps[$i]['Name'];?></div></td>
                      <td>
                      <?php if(!$Bait['GelFree']){?>
                      <div class=maintext><a href="./search.php?searchThis=''&ListType=Experiment&linkID=<?php echo $Exps[$i]['ID'];?>&linkName=<?php echo $Exps[$i]['Name'];?>" class=button target=new><img src="./images/icon_report.gif" border=0 alt="Plate Report"></a></div>
                      <?php }?> 
                      </td>            
          	          <td><div class=maintext><?php echo $exConditions;?></div></td>
          	          <td><div class=maintext><?php echo $theUser;?></div></td>
          	          <td><div class=maintext><?php echo $Exps[$i]['DateTime'];?></div></td>
          	        </tr>
  	        <?php 
              $Exps[$i]['Name'] = str_replace(",", ";", $Exps[$i]['Name']);
              $exConditions = str_replace(",", ";", $exConditions);
              $theUser = str_replace(",", ";", $theUser);
              $Exps[$i]['PreySource'] = str_replace(",", ";", $Exps[$i]['PreySource']);
              fwrite($handle, $Exps[$i]['ID'].",".$Exps[$i]['Name'].",".$exConditions.",".$theUser.",".$Exps[$i]['DateTime'].",".$Exps[$i]['PreySource']."\n");          
            }
            fwrite($handle, "\n"); 
            ?>
  	              </table>
                </td>  	            
  	          </tr>
  	        </table>
          </td>
        </tr>        
      </table>
    </td>
  </tr>
<?php 
include("filterSelection.inc.php");
?>
  <tr>
      <td colspan=2>&nbsp;
          <input type=button value='No Exclusion' class=black_but onClick='javascript: NoExclusion();'>
          <input type=button value='Apply Exclusion' class=black_but onClick='javascript: applyExclusion();'>
          <input type=button value='Update Frequency' onClick='javascript: updateFrequency();'>
      </td>          
  </tr>  
  <tr>
    <td align="center" colspan=2><br>
      <table border=0 cellpadding="0" cellspacing="1" width="100%">
        <tr bgcolor="">
          <td width="5" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader>ID</div></td>
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
           <div class=tableheader># Peptide</div></td>
          <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
           <div class=tableheader># Unique<BR>Peptide</div></td>
          <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
           <div class=tableheader>Sequence<br>Coverage</div></td>
          <td width="75" bgcolor="<?php echo $bgcolordark;?>" align="center">
            <div class=tableheader>Links</div></td>
          <td width="" bgcolor="<?php echo $bgcolordark;?>" align="center">
            <div class=tableheader>Filter</div></td>
          <td width="75" bgcolor="<?php echo $bgcolordark;?>" align="center">
            <div class=tableheader>Option</div></td>
        </tr>
<?php 
$filedNameStr = "Hit ID,Hit GI,GeneID,GeneName,LocusTag,";
if($frequencyLimit < 101){
  $filedNameStr .= "Frequency,";
}
$filedNameStr .= "Redundant,Hit MW,Hit Description,Score,Search Database,Search Date,Filters\n\n";
fwrite($handle, $filedNameStr);
$tmpCounter = 0;
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
         H.Expect2,
         H.MW,
         H.Pep_num,
         H.Pep_num_uniqe,
         H.Coverage,
         H.RedundantGI,
         H.ResultFile,
         H.SearchDatabase,
         H.DateTime, 
         H.SearchEngine,
         B.ExpID         
         FROM Hits H, Band B
         WHERE H.BandID = B.ID
         and H.BaitID='$Bait_ID' 
         and B.ProjectID=$AccessProjectID 
         ORDER BY B.ExpID, H.ID";
//echo $SQL;exit;
                       
$sqlResult = mysqli_query($mainDB->link, $SQL);
$img_total = mysqli_num_rows($sqlResult);  // for create image
while(list(
  $ID,
  $WellID, 
  $BaitID, 
  $BandID,
  $GeneID, 
  $LocusTag, 
  $HitGI, 
  $HitName, 
  $Expect,
  $Expect2,
  $MW,
  $PepNum,
  $PepNumUniqe,
  $Coverage,
  $RedundantGI,
  $ResultFile,
  $SearchDatabase,
  $DateTime,
  $SearchEngine,
  $ExpID)= mysqli_fetch_row($sqlResult) ){
  
  $tmpHitNotes = array();
  $HitGeneName = '';
  $HitFrequency = 0;
  
  if($GeneID)
  {
    $SQL = "SELECT LocusTag, GeneName, BioFilter FROM Protein_Class WHERE  EntrezGeneID=$GeneID";
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
    if($Bait['GeneID'] && $GeneID && ($Bait['GeneID'] == $GeneID)){
      array_push($tmpHitNotes, 'BT');
    }
    //---add 1/9-Process FQ and NS-------------------------------------------------------------------
    $SQL = "SELECT Value, FilterAlias FROM ExpFilter WHERE ProjectID ='".$_SESSION["workingProjectID"]."' AND GeneID=$GeneID";
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
  if($ID){
  //------Process CO, ME, RI, SO,  ----------------------------------------
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
//-----------Process OP--------------------------------------------------------------------------
  if(is_one_peptide($ID,$HITSDB,$PepNumUniqe)) array_push($tmpHitNotes, "OP");
    
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
    }else{
      if($tmpNum > 30 ){
				array_push($tmpHitNotes, "AW");
			}
    } 
  }//from checkCarryOver.inc.php=====================
  //---------------------------------------------------------------------------------
   
  $tmpbgcolor = $bgcolor;
  $tmptextfont = "maintext";  
  
	if($ExpID != $tmpExp['ID']){
    $SQL = "SELECT 
      ID,  
      Name 
      FROM Experiment where ID='$ExpID'";
    $tmpExp = $mainDB->fetch($SQL);
    
?>
        <tr bgcolor="">
          <td colspan=14><div class=maintext_color><hr>
<?php 
    echo "Experiment ID: <b>".$tmpExp['ID']."</b> &nbsp; Name/Batch Name: <b>".$tmpExp['Name']."</b>";
?>
            </div>
          </td>
        </tr>
    <?php 
    $tmpExp['Name'] = str_replace(",", ";", $tmpExp['Name']);
    fwrite($handle, "\nExperiment ID: ".$tmpExp['ID'].",Name/Batch Name: ".$tmpExp['Name']."\n\n");
  }
  $rc_excluded = 0;
	if((!isset($frm_BT) || !$frm_BT) and $Bait['GeneID'] == $GeneID){
	  $rc_excluded = 0;	 
	}else if($theaction == 'exclusion' && !in_array(ID_REINCLUDE, $tmpHitNotes)){
	  if(($frm_Expect_check && $Expect && $Expect <= $frm_Expect) || ($frm_Expect2_check && $Expect2 && $Expect2 >= $frm_Expect2) || (isset($frm_Frequency) && $frm_Frequency && $HitFrequency >= $frequencyLimit)){
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
  		//if(in_array($Value['Alias'] ,$tmpHitNotes) && $Value['Alias'] != "HP"){
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

  if(!$rc_excluded){//============================================================
    $tmpCounter++;
        
    if($HitGI && ($GeneID || $HitGeneName)){
      if(!isset($giArr[$HitGI])){
        $giArr[$HitGI] = 1;
      }else{  
        $giArr[$HitGI]++;
      }
    }
    
    if($HitFrequency){
      $HitFrequencyPercent = $HitFrequency."%";
    }else{
      $HitFrequencyPercent = "0%";
    }
      
    $Description = str_replace(",", ";", $HitName);
    $Description = str_replace("\n", "", $Description);
    
    $HitGeneName = str_replace(",", ";", $HitGeneName);
    $LocusTag = str_replace(",", ";", $LocusTag);
    $SearchDatabase = str_replace(",", ";", $SearchDatabase);
    $Expect = str_replace(",", ";", $Expect);
    $HitGI = str_replace(",", ";", $HitGI);
    
    fwrite($handle, $ID.",".$HitGI.",".$GeneID.",".$HitGeneName.",".$LocusTag.",");
    if($frequencyLimit < 101){
      fwrite($handle, $HitFrequencyPercent.",");
    }
    fwrite($handle, $RedundantGI.",".$MW.",".$Description.",".$Expect.",".$SearchDatabase.",".$DateTime.",");
    $filterString = '';
    str_replace("gi","<br>gi", $RedundantGI);
?>
  <tr  bgcolor='<?php echo $tmpbgcolor;?>' onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $tmpbgcolor;?>');">
    <td width="" align="center" bgcolor=<?php 
    if($GeneID && ($Bait['GeneID'] == $GeneID)){
  	  echo "'$bait_color'";
      for($m=0; $m<count($typeBioArr); $m++){
        if($typeBioArr[$m]['Alias'] == ID_BAIT){ 
    				$typeBioArr[$m]['Counter']++;
    		}
      }
  	}else{
  	  echo "'$tmpbgcolor'";  	
    }
	 ?>
   ><div class=<?php echo $tmptextfont;?>>
        <?php echo $ID;?>&nbsp;
      </div>
    </td>
    <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
        <?php echo  $HitGI;?>&nbsp;
        </div>
    </td>
    <td width="" align="center" <?php echo ($GeneID || $HitGeneName)?"class='gi".$HitGI."'" : "";?>><div class=maintext>    
        <?php 
        if($GeneID || $HitGeneName){
          echo  $GeneID." / ".$HitGeneName;
        }
        ?>&nbsp;
        </div>
    </td>
    <!--td width="" align="center"><div class=maintext>
        <?php echo  $LocusTag;?>&nbsp;
        </div>
    </td-->
    <?php if($frequencyLimit < 101){?>
    <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
        <?php echo $HitFrequencyPercent;?>&nbsp;
        </div>
    </td>
    <?php }?>
    <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
        <?php echo str_replace("gi","<br>gi", $RedundantGI);?>&nbsp;
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
    <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
        <?php echo $Expect2;?>&nbsp;</div>
    </td>
    <td width="" align="right" ><div class=<?php echo $tmptextfont;?>>
        <?php echo $PepNum;?>&nbsp;</div>
    </td>
    <td width="" align="right" ><div class=<?php echo $tmptextfont;?>>
        <?php echo $PepNumUniqe;?>&nbsp;</div>
    </td>
    <td width="" align="right" ><div class=<?php echo $tmptextfont;?>>
        <?php echo ($Coverage>0)?"$Coverage%":"";?>&nbsp;</div>
    </td>
    <td width="" align="center" nowrap><div class=<?php echo $tmptextfont;?>>
    <?php 
    $urlLocusTag = $LocusTag;
    $urlGeneID = $GeneID;
    $urlGI = $HitGI;
    echo get_URL_str($HitGI, $GeneID, $LocusTag);
    ?>
    </div>
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
    if($typeFrequencyArr && $HitFrequency >=$frequencyLimit and !$is_reincluded){
      $typeFrequencyArr['Counter']++;
       echo "<td bgcolor='" . $typeFrequencyArr['Color'] . "' nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
       ($filterString)? $filterString.=";frequence>=$frequencyLimit%" : $filterString.="frequence>=$frequencyLimit%";
    }
    if(($Expect and $Expect <= DEFAULT_EXPECT_EXCLUSION) and !$theaction and !$is_reincluded){
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
    <td width="" align="left" nowrap><div class=maintext>&nbsp; &nbsp; &nbsp;
    <?php if(!$Bait['GelFree']){?>
      <a href="javascript: view_gel(<?php echo $BandID;?>);"><img border=0 src="./images/icon_picture.gif" alt='gel image'></a>
    <?php }?>
      <?php if($SearchEngine=='Mascot' or $SearchEngine=='GPM'){?>
      <a href="javascript:view_peptides(<?php echo  $ID;?>);"><img border="0" src="./images/icon_<?php echo $SearchEngine;?>.gif" alt="Peptides"></a>
      <a href="javascript:view_master_results('<?php echo  $ResultFile;?>','<?php echo  $SearchEngine;?>');"><img border="0" src="./images/icon_<?php echo $SearchEngine;?>2.gif" alt="Peptides"></a>
      <?php }?>
      <a href="javascript: add_notes('<?php echo $ID;?>');"><img src="./images/icon_notes.gif" border=0 alt="Hit Notes"></a>
    <?php    
    $coip_color_and_ID = array('color'=>'', 'ID'=> '');
    $coip_color_and_ID = get_coip_color($mainDB, $Bait['GeneID'], $GeneID);
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
}//end for
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

arsort($giArr); 
$colorIndex = 0;
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

function move_bait($DB, $whichBait, $Bait_ID=0){
  global $AccessProjectID;
  $re = $Bait_ID;
  $SQL = "SELECT ID FROM Bait";     
  $Where = " WHERE  ProjectID=$AccessProjectID";     
  $SQL .= $Where;
  if($whichBait == 'last'){
    $SQL .= " order by ID desc limit 1";
  }elseif($whichBait == 'first'){
    $SQL .= " order by ID limit 1";
  }elseif($whichBait == 'next' and $Bait_ID){
    $SQL .= " and  ID > $Bait_ID  order by ID limit 1";
  }elseif($whichBait == 'previous' and $Bait_ID){
    $SQL .= " and  ID < $Bait_ID  order by ID desc limit 1";
  }
  //echo $SQL;
  $row = mysqli_fetch_array(mysqli_query($DB->link, $SQL));
  if($row[0]) $re = $row[0];
  return $re;
}
?>