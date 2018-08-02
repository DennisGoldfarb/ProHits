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

$Band_ID = '';
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");

if(!$Bait_ID){
  echo "no Bait ID.";
  exit;
}

$GelFreeColor = "#737373";
$unGelFreeColor = "#000000";
$GrowColor = "#d2691e";
$IpColor = "#ffa500";
$DigestColor = "#ffc0cb";
$RawFileColor = "#2080df";
$HasHitsColor = "#5b52ad";
$EmptyColor = "#d9e8f0";
$letterFlag = '';
$SampleMess = '';

$msManagerDB = new mysqlDB(MANAGER_DB, HOSTNAME, USERNAME, DBPASSWORD);
$SQL = "SELECT 
        `ID`,
        `GeneID`,
        `LocusTag`,
        `GeneName`,
        `BaitAcc`,
        `AccType`,
        `Description`,
        `OwnerID`,
        `GelFree`,
        `DateTime`FROM `Bait` 
        WHERE ID='$Bait_ID'
        AND ProjectID=$AccessProjectID";        
if(!$Bait = $HITSDB->fetch($SQL)){
  echo "Invalid Bait ID $Bait_ID";
  exit;
}
$userNameArr = get_users_ID_Name($HITSDB);
$createdBy = '';

if(isset($userNameArr[$Bait['OwnerID']])) $createdBy = $userNameArr[$Bait['OwnerID']];
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
   <LINK REL="SHORTCUT ICON" HREF="http://192.197.250.119/myicon.ico">
  <link rel="stylesheet" type="text/css" href="./site_style.css"> 
  <title>Prohits-Yeast</title>
</head>
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 topMargin=5 rightMargin=5 marginheight="5" marginwidth="5">

<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td align="left">
      <font color="navy" face="helvetica,arial,futura" size="3"><b>Experiment Progress Status</b></font>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor="#800040"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="left"><br>
      <table border=0 cellspacing="0" cellpadding="0"  width="100%">
        <tr>
          <td width=30%><div class=large>Bait ID:&nbsp;&nbsp;<b><?php echo $Bait_ID;?></b> &nbsp;</div></td>
          <td ><div class=large >Gene Name:&nbsp;<b><?php echo $Bait['GeneName'];?></b> &nbsp;</div></td>          
        </tr>
        <tr>
          <td><div class=large>Gene ID:&nbsp;<b><?php echo ($Bait['GeneID'])?$Bait['GeneID']:'';?></b> &nbsp;</div></td>
          <td ><div class=large>LocusTag:&nbsp;&nbsp;<b><?php echo $Bait['LocusTag'];?></b> &nbsp;</div></td>
        </tr>
        <tr>
          <td><div class=large width=35%>Protein ID:&nbsp;<b><?php echo $Bait['BaitAcc'];?></b> &nbsp;</div></td>
          <td><div class=large>Created by:&nbsp;<b><?php echo $createdBy;?></b> &nbsp;</div></td>
        </tr>
        <tr>
          <td colspan=3><div class=large>Created date:&nbsp;&nbsp;<b><?php echo $Bait['DateTime'];?></b> &nbsp;</div></td>
        </tr>               
        <tr>
          <td>Bait Description:</td>
          <td colspan=2><div class=maintext_extra><?php echo $Bait['Description'];?></div></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td align="center"><br>
      <table border=0 cellspacing="1" cellpadding="0" width="98%">
<?php 
$SQL ="SELECT `ID`,
        `Name`,
        `GrowProtocol`,
        `IpProtocol`, 
        `DigestProtocol`
        FROM `Experiment` 
        WHERE `BaitID` = '$Bait_ID'";
if($ExperimentArr = $HITSDB->fetchAll($SQL)){
  foreach($ExperimentArr as $ExperimentValue){
    if($Band_ID){
      $SQL = "SELECT `ID`,`Location`,`RawFile` FROM `Band` WHERE `ID`='".$Band_ID."' AND ExpID='".$ExperimentValue['ID']."'";
      if(!$BandArr = $HITSDB->fetchAll($SQL)) continue;
    }else{
      $SQL = "SELECT `ID`,`Location`,`RawFile` FROM `Band` WHERE `ExpID`='".$ExperimentValue['ID']."'";
      $BandArr = $HITSDB->fetchAll($SQL);
    }
    $statusArr = get_Progress_status($ExperimentValue['ID'], "Experiment");
    //if($statusArr['num_hits']) $retArr['num_Hits'] += $statusArr['num_hits'];
    if($statusArr['num_hits']){
      $message = "Number of hits: " .$statusArr['num_hits'];
    }elseif($statusArr['num_files']){
      $message = "Number of raw files: " .$statusArr['num_files'];    
    }
?>
        <tr><td height=1 bgcolor="#408080"><img src="./images/pixel.gif"></td></tr>
        <tr><td height=30><div class=large>Experiment Name:&nbsp;&nbsp;<b><?php echo $ExperimentValue['Name'];?></b></div></td></tr>
<?php 
    if($Bait['GelFree']){
      $GelFreeColorlocal = $GelFreeColor;
      $gelFreeMess = '<font color=#008040><b>Gel free experiment.</font>';
    }else{
      $GelFreeColorlocal = $unGelFreeColor;
      $gelFreeMess = '<font color=#008040><b>Gel based experiment.</font>';
    }
    display_step(0,$GelFreeColorlocal,$gelFreeMess);

    $ProtocolMess = '';
    $bgColor = $EmptyColor;
    if($ExperimentValue['GrowProtocol']){
      $ProtocolMess = get_Protocol($ExperimentValue['GrowProtocol'],1);
      $bgColor = $GrowColor;
    }
    display_step(1,$bgColor,$ProtocolMess);
  
    $ProtocolMess = '';
    $bgColor = $EmptyColor;
    if($ExperimentValue['IpProtocol']){
      $ProtocolMess = get_Protocol($ExperimentValue['IpProtocol'],2);
      $bgColor = $IpColor;
    }
    display_step(2,$bgColor,$ProtocolMess);
  
    $ProtocolMess = '';
    $bgColor = $EmptyColor;
    if($ExperimentValue['DigestProtocol']){
      $ProtocolMess = get_Protocol($ExperimentValue['DigestProtocol'],3);
      $bgColor = $DigestColor;
    }
    display_step(3,$bgColor,$ProtocolMess);
    //echo $SQL;exit;
    if($BandArr){
      foreach($BandArr as $BandValue){
        $sample = $BandValue['Location'];
        
        $HitsParsed = 0;
        if($BandValue['RawFile']){
          $rawFileInfoArr = explode(";",$BandValue['RawFile']);
          foreach($rawFileInfoArr as $rawValue){
            $SampleMess = "<font color=#008040><b>".$sample.":</b></font>";
            $folder = '';
            $table = '';
            $file = ''; 
            $fileTableIDarr = explode(":",$rawValue);
            $table = '';
            $thetppResultsTable = '';
            if($fileTableIDarr[0] && isset($fileTableIDarr[1])){
              $thePos = strpos($fileTableIDarr[0], "tpp");
              $thetppResultsTable = $fileTableIDarr[0];
              if($thePos){
                $table = substr($fileTableIDarr[0], 0,$thePos);
              }else{
                $table = $fileTableIDarr[0];
              }
              $fileTableIDarr[1] = preg_replace("/[,]$/",'', $fileTableIDarr[1]);
               
              $SQL = "SELECT `ID`,`FileName`,`FolderID`,`Size` FROM ".$table." WHERE `ID` in(".$fileTableIDarr[1].")";
               
              if($mBaseArr1 = $msManagerDB->fetchAll($SQL)){
                $i = 0;
                if(count($mBaseArr1) > 1) {
                  $SampleMess_sub = " linked to a merged TPP results<br>";
                }
                $SampleMess_sub = '';
                foreach($mBaseArr1 as $mTableArr){
                  $file = $mTableArr['FileName'];
                  $SQL = "SELECT `FileName` FROM ".$table." WHERE `ID`='".$mTableArr['FolderID']."' AND `FileType`='dir'";
                  
                  $mDirArr = $msManagerDB->fetch($SQL);
                  $folder = $mDirArr['FileName'];
                  if($i>0) $SampleMess_sub .= "\n<br>";
                  $SampleMess_sub .= "&nbsp; &nbsp; $table <b>\\</b> ".$folder."<b> \\ ".$file."</b>";
                  $tmp_Size = '';
                  if( $mTableArr['Size']){
                    $tmp_Size = number_format(ceil( $mTableArr['Size']/1024));
                    $SampleMess_sub .= "&nbsp;(".$tmp_Size."KB)";
                  }
                  $i++;
                }
              }else{
                continue;
              }
              $theTableName = $fileTableIDarr[0];
              $theFileID = $fileTableIDarr[1];
               
              $HitsParsed = is_hits_parsed($theFileID, $theTableName);
              
              
              $SampleMess .= $SampleMess_sub;
              display_step(4,$RawFileColor,$SampleMess);
            }else{
              display_step(4,$EmptyColor,$SampleMess);
            }
          }  
        }else{
          display_step(4,$EmptyColor,$SampleMess);
        }
        
        
         
        $tmp_num_arr = get_hit_num($BandValue['ID']);
        $hitsMess = '';
        if($HitsParsed or $tmp_num_arr['hits'] or $tmp_num_arr['hitsTppProt']){
          $tppHits = ($tmp_num_arr['hitsTppProt'])?" <font color=black>TPP hits:</font>".$tmp_num_arr['hitsTppProt']:"";
          $hitsMess = "<font color=#008040><b># of Hits:</b></font>&nbsp;&nbsp;" . "<font color=red>".$tmp_num_arr['hits'].$tppHits."</fond>";
          //if(!count($HitsArr)) $letterFlag = 1;
        }
        if($hitsMess){
          $bgColor = $HasHitsColor;
        }else{
          $bgColor = $EmptyColor;
        }
        display_step(5,$bgColor,$hitsMess);
      }
    }else{
      display_step(4,$EmptyColor);
      display_step(5,$EmptyColor);
    }
  }
?>           
      </table>
    </td>
  </tr>
</table>
</body>
</html>
<?php 
}
function get_Protocol($ProtocolIDdate,$ProtocolType){
  global $HITSDB;
  if(!$ProtocolIDdate || !$ProtocolType){
    return "";
  }elseif($ProtocolType == 1){
    $labal = "Growing conditions/protocol:&nbsp;&nbsp&nbsp;&nbsp;";
  }elseif($ProtocolType == 2){
    $labal = "Ip conditions/protocol:&nbsp;&nbsp&nbsp;&nbsp;";
  }elseif($ProtocolType == 3){
    $labal = "Digest conditions/protocol:&nbsp;&nbsp;&nbsp;&nbsp;";
  }  
  $tmpProtocolArr = explode(",",$ProtocolIDdate);
  $SQL = "SELECT `Name` FROM `Protocol` WHERE `ID`='".$tmpProtocolArr[0]."'";
  $protocolName = '';
  if($protocolArr = $HITSDB->fetch($SQL)) $protocolName = $protocolArr['Name'];
  $createDate = "";
  if(isset($tmpProtocolArr[1])) $createDate = $tmpProtocolArr[1];
  return $message = "<font color=#008040><b>".$labal."</b></font>" . $protocolName . " (" . $createDate .")";
}

function display_step($repeatTime=0,$color,$message=''){
  global $letterFlag;
  $letter = "&nbsp;";
  if($letterFlag) $letter = "<font color=white>O</font>";
?>
        <tr>
          <td colspan=2 nowrap>
            <div class=maintext_extra><font face="Courier" size=+1><?php echo str_repeat("&nbsp;",$repeatTime);?><font style='background-color:<?php echo $color;?>'><?php echo $letter;?></font></font>&nbsp;&nbsp;<?php echo $message;?></div>          
          </td>          
        </tr>
<?php 
  $letterFlag = 0;
}
?>
