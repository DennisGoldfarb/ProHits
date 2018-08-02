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

require_once("../common/site_permission.inc.php");
require_once("analyst/common_functions.inc.php");

if(!isset($item_ID) || !$item_ID || !isset($itemType) || !$itemType) return;

$SQL = "SELECT `ID`,`Name` FROM `ExpDetailName`";
$tmpExpDetail_arr = $PROHITSDB->fetchAll($SQL);
$ExpDetail_id_name_arr = array();
foreach($tmpExpDetail_arr as $tmpExpDetail_val){
  $ExpDetail_id_name_arr[$tmpExpDetail_val['ID']] = $tmpExpDetail_val['Name'];
}
$detail_div_id = "B".$item_ID."_a";
//-------------------------------------------------------------------------  
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
if($itemType == "Bait"){
  $SQL = "SELECT `ID`, `GelFree` 
          FROM `Bait` 
          WHERE ID='$item_ID'";
  $SQL2 ="SELECT `ID`,
          `Name`,
          `GrowProtocol`,
          `IpProtocol`, 
          `DigestProtocol`,
          `Notes`
          FROM `Experiment` 
          WHERE `BaitID`='$item_ID' order by ID";        
}else{
  $SQL = "SELECT B.ID, B.GelFree 
          FROM Band N 
          LEFT JOIN Bait B ON N.BaitID=B.ID 
          WHERE N.ID='$item_ID'";
  $SQL2 ="SELECT E.ID,
          E.Name,
          E.GrowProtocol,
          E.IpProtocol, 
          E.DigestProtocol,
          E.Notes
          FROM Band B LEFT JOIN Experiment E ON E.ID=B.ExpID 
          WHERE B.ID='$item_ID'";
}
$itemInfoArr = $HITSDB->fetch($SQL);
$Bait_ID = $itemInfoArr['ID'];
$GelFree =$itemInfoArr['GelFree'];
if($ExperimentArr = $HITSDB->fetchAll($SQL2)){
  $detailStr_all = $detail_div_id."@@**@@";;
  foreach($ExperimentArr as $ExperimentValue){
    $SQL = "SELECT 
            `SelectionID`,
            `OptionID` 
            FROM `ExpDetail` 
            WHERE `ExpID`='".$ExperimentValue['ID']."'
            ORDER BY `IndexNum`";
    $expConditionArr = $HITSDB->fetchAll($SQL);
    $expConditionStr = '';
    foreach($expConditionArr as $expConditionVal){
      $tmpSelection = $ExpDetail_id_name_arr[$expConditionVal['SelectionID']];
      $tmpOption = $ExpDetail_id_name_arr[$expConditionVal['OptionID']];
      if($expConditionStr) $expConditionStr .= ",";
      $expConditionStr .= $tmpSelection.":".$tmpOption;
    }
     
    if($expConditionStr) $expConditionStr = "(".$expConditionStr.")";
    $detailStr = '';
		$sub_sql = '';
	  if($itemType == "Bait"){
		  $typeID = $ExperimentValue['ID'];
  		$typeID_name = 'ExpID';
      $sub_sql = " AND BaitID='$item_ID'";
		}else{
		  $typeID = $item_ID;
  		$typeID_name = 'ID';
		}
    $SQL = "SELECT
           `ID`,
           `Location`,
           `RawFile` 
           FROM `Band` 
           WHERE $typeID_name='".$typeID."' $sub_sql";
    $BandArr = $HITSDB->fetchAll($SQL);
			
    if($GelFree){
      $GelFreeColorlocal = $GelFreeColor;
      $gelFreeMess = '<font color=#008040>'.$ExperimentValue['Name'].$expConditionStr.'</font>';
    }else{
      $GelFreeColorlocal = $unGelFreeColor;
      $gelFreeMess = '<font color=#008040>'.$ExperimentValue['Name'].$expConditionStr.'</font>';
    }
    $detailStr .= "<table border=0><tr><td><pre>\r\n";
    $detailStr .= display_step(0,$GelFreeColorlocal,$gelFreeMess);
      
    $ProtocolMess = '';
    $bgColor = $EmptyColor;
    if($ExperimentValue['GrowProtocol']){
      $ProtocolMess = get_Protocol($ExperimentValue['GrowProtocol'],1);
      $bgColor = $GrowColor;
    }    
    $detailStr .= display_step(1,$bgColor,$ProtocolMess);
      
    $ProtocolMess = '';
    $bgColor = $EmptyColor;
    if($ExperimentValue['IpProtocol']){
      $ProtocolMess = get_Protocol($ExperimentValue['IpProtocol'],2);
      $bgColor = $IpColor;
    }
    $detailStr .= display_step(2,$bgColor,$ProtocolMess);
      
    $ProtocolMess = '';
    $bgColor = $EmptyColor;
    if($ExperimentValue['DigestProtocol']){
      $ProtocolMess = get_Protocol($ExperimentValue['DigestProtocol'],3);
      $bgColor = $DigestColor;
    }
    $detailStr .= display_step(3,$bgColor,$ProtocolMess);
    
    $HasHitsColor = "#5b52ad";
    $msManagerDB = new mysqlDB(MANAGER_DB);
    if($BandArr){
		  foreach($BandArr as $BandValue){
        $BandHits_passed = 0;
     	  $sample = $BandValue['Location'];
     	  if($BandValue['RawFile']){
       	  $rawFileInfoArr = explode(";",$BandValue['RawFile']);//rawfiles belong to a single band--
				  $SampleMess = '';
          foreach($rawFileInfoArr as $rawValue){
         	  $fileTableIDarr = explode(":",$rawValue);
         	  $thetppResultsTable = '';
         	  if(count($fileTableIDarr) == 2){
           	  $table = '';
           	  $file = ''; 
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
                $subFlag = '';
  							if(count($mBaseArr1) > 1){
  								if($SampleMess) $SampleMess .= "\n".str_repeat("&nbsp;",6);
  								$SampleMess .= "Merged Files:";
                  $subFlag = '-';
  							}            
               	foreach($mBaseArr1 as $mTableArr){
                  $raw_path = get_rawfile_path($mTableArr['FolderID'],$table,$msManagerDB);
                  $file = "<a class='title' title='$raw_path'>".$subFlag.$mTableArr['FileName']."</a>";
                 	//$file = $subFlag.$mTableArr['FileName'];
                 	if($SampleMess) $SampleMess .= "\n<font face='Courier' size=1>".str_repeat("&nbsp;",6)."</font>";
                 	$SampleMess .= $file;
                 	$tmp_Size = '';
                 	if( $mTableArr['Size']){
                   	$tmp_Size = number_format(ceil( $mTableArr['Size']/1024));
                   	$SampleMess .= "&nbsp;(".$tmp_Size."KB)";
                 	}
                  $pop_file_name = "./pop_rawFile_detail.php?tableName=".$table."&raw_file_ID=".$mTableArr['ID']."&Band_ID=".$BandValue['ID']."&GelFree=$GelFree";
                  $SampleMess .= "&nbsp;<a  title='raw file detail' href=\"javascript: popwin('$pop_file_name',550,380)\">[detail]</a>";
               	}
             	}
         	  }
            if(count($fileTableIDarr) != 2) continue;
            $theTableName = $fileTableIDarr[0];
           	$theFileID = $fileTableIDarr[1];
            $is_hits_parsed = is_hits_parsed($theFileID, $theTableName);
            $BandHits_passed += $is_hits_parsed;
       	  }
          if($SampleMess){
  				  $detailStr .= display_step(4,$RawFileColor,$SampleMess);
          }else{
            $detailStr .= display_step(4,$EmptyColor);
          }    
       	}else{
          $detailStr .= display_step(4,$EmptyColor);
        }
      
     	  $tmp_num_arr = get_hit_num($BandValue['ID']);
     	  $hitsMess = '';
       	if($tmp_num_arr['hits'] or $tmp_num_arr['hitsTppProt']){
         	$tppHits = ($tmp_num_arr['hitsTppProt'])?" <font color=#008040>TPP hits:</font><font color=red>".$tmp_num_arr['hitsTppProt']:"</font>";
         	$hitsMess = "<font color=#008040># of Hits:</font>&nbsp;&nbsp;" . "<font color=red>".$tmp_num_arr['hits'].$tppHits."</font>";
        }elseif($BandHits_passed){
  				$hitsMess = "<font color=#008040># of Hits:</font>&nbsp;&nbsp;" . "<font color=red>0</font>";
  			}
       	if($hitsMess){
         	$bgColor = $HasHitsColor;
       	}else{
         	$bgColor = $EmptyColor;
       	}
       	$detailStr .= display_step(5,$bgColor,$hitsMess);
     	}
   	}else{
     	$detailStr .= display_step(4,$EmptyColor);
     	$detailStr .= display_step(5,$EmptyColor);
   	}
    $detailStr .= "</font></pre>";
    if($ExperimentValue['Notes']){
      $detailStr .= display_note($ExperimentValue['Notes']);
    }  
    $detailStr .= "</td></tr></table>\r\n";
    $detailStr_all .= $detailStr;      
  }
  echo $detailStr_all;
}

?>