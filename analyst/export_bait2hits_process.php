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
require_once("msManager/is_dir_file.inc.php");

$PROTEINDB = new mysqlDB(PROHITS_PROTEINS_DB);
//----make dir-------------------------------
$tmpDir = "../TMP/bait2hits/";
if(!_is_dir($tmpDir)) _mkdir_path($tmpDir);
$tmpDir .= 'U_' . $_SESSION['USER']->ID;
if(!is_dir($tmpDir)) mkdir($tmpDir);

chdir($tmpDir);
empty_dir(".");//exit;

//--creat file name for normal file--------------
$baitIDarr = explode(',', $frm_selected_bait_str);

if(count($baitIDarr) == 1){
  $inBaitIDstr = $baitIDarr[0];
}elseif(count($baitIDarr) > 1){
  $inBaitIDstr = $baitIDarr[0].','.end($baitIDarr);
}else{
  echo "No bait IDs";
  exit;
}
$SQL = "SELECT `ID`,`GeneID`,`GeneName`, `LocusTag` FROM `Bait` WHERE `ID` IN ($inBaitIDstr)";
$fileNamePartsArr = $HITSDB->fetchAll($SQL);

if(count($fileNamePartsArr) == 1){
  $fileFullName = $fileNamePartsArr[0]['ID'].'_'.$fileNamePartsArr[0]['GeneName'].'.csv';
}else{
  $fileFullName = $fileNamePartsArr[0]['ID'].'_'.$fileNamePartsArr[0]['GeneName']."-".$fileNamePartsArr[1]['ID'].'_'.$fileNamePartsArr[1]['GeneName'].'.csv';
}
$fileFullName = preg_replace('/\s/', '', $fileFullName);

$baitLableArr = array();
$fileNameArr = array();
$hitsNameArr = array();
$baitNameArr = array();
$hitGeneNameFlag = 0;
$hitYorfNameFlag = 0;
$noIDnum = 0;
$typeOutputStr = "";
$baitNoName = 0;
$TypeArr = array();
$tmpTypeArr = explode(',,',$typeStr);
foreach($tmpTypeArr as $value){
  $tmpArr1 = explode(';;',$value);
  $tmpArr1[1] = str_add_double_quote($tmpArr1[1]);
  $TypeArr[$tmpArr1[0]] = $tmpArr1[1];
}

$typeStr = '';
foreach($baitIDarr as $value){
  $typeStr .= ','.$TypeArr[$value];
  
  $SQL = "SELECT `GeneID`,`GeneName`, `LocusTag` FROM `Bait` WHERE `ID`='$value'";
  $baitArr = $HITSDB->fetch($SQL);
  if(count($baitArr)){
    if($baitArr['GeneName'] && $baitArr['GeneName'] != "-"){
      $baitLable = $baitArr['GeneName'];
    }elseif($baitArr['LocusTag'] && $baitArr['LocusTag'] != "-"){
      $baitLable = $baitArr['LocusTag'];
    }else{
      $baitNoName++;
      $baitLable = "noName_".$baitNoName;
    }
    $fileName = str_add_double_quote($value.'_'.$baitLable);
    $baitLableArr[$value] = str_add_double_quote($baitLable);
    array_push($fileNameArr, $fileName);
    $SQL = "SELECT `GeneID`,
                   `LocusTag`,
                   `HitGI`,
                   `Pep_num`,
                   `Pep_num_uniqe`,
                   `Coverage`,
                   `Expect`,
                   `Expect2`
                   FROM `Hits` 
                   WHERE `BaitID`='$value' order by ID";
    $hitsArrs = $HITSDB->fetchAll($SQL);
    $tmpHitsArr = array();
    $hitsGeneIDinAbaitArr = array();
    $hitsPIDinAbaitArr = array();
    for($i=0; $i<count($hitsArrs); $i++){
      if($hitsArrs[$i]['LocusTag'] && $hitsArrs[$i]['LocusTag'] != "-"){
        if(!$hitYorfNameFlag) $hitYorfNameFlag = 1;
      }else{
        $hitsArrs[$i]['LocusTag'] = '';
      }
      if(!$hitsArrs[$i]['Expect']) $hitsArrs[$i]['Expect'] = $hitsArrs[$i]['Expect2'];
      if($hitsArrs[$i]['GeneID']){
        if(!array_key_exists($hitsArrs[$i]['GeneID'], $hitsNameArr)){
          $SQL = "SELECT `GeneName` FROM `Protein_Class` WHERE `EntrezGeneID`='".$hitsArrs[$i]['GeneID']."'";
          if($GeneNameArr = $PROTEINDB->fetch($SQL)){
            $GeneName = str_add_double_quote($GeneNameArr['GeneName']);
            if(!$hitGeneNameFlag) $hitGeneNameFlag = 1;
          }else{
            $GeneName = '';
          }
          if((!$hitsArrs[$i]['LocusTag'] || $hitsArrs[$i]['LocusTag'] == "-") && !$GeneName){
            $hitsNameArr[$hitsArrs[$i]['GeneID']] = ",".str_add_double_quote($hitsArrs[$i]['GeneID']);
          }else{
            $hitsNameArr[$hitsArrs[$i]['GeneID']] = str_add_double_quote($hitsArrs[$i]['LocusTag']).",".$GeneName;
          }  
        }
        if(isset($Pid)){
          $tmpHitValue = $hitsArrs[$i]['HitGI'].":".$hitsArrs[$i]['Expect']."(".$hitsArrs[$i]['Pep_num']."-".$hitsArrs[$i]['Pep_num_uniqe']."-".$hitsArrs[$i]['Coverage'].")";
        }else{
          $tmpHitValue = $hitsArrs[$i]['Expect']."(".$hitsArrs[$i]['Pep_num']."-".$hitsArrs[$i]['Pep_num_uniqe']."-".$hitsArrs[$i]['Coverage'].")";        
        }
        if(!array_key_exists($hitsArrs[$i]['GeneID'], $tmpHitsArr)){
          $tmpHitsArr[$hitsArrs[$i]['GeneID']] = array();
          array_push($tmpHitsArr[$hitsArrs[$i]['GeneID']], $tmpHitValue);
        }else{
          if(!in_array($tmpHitValue, $tmpHitsArr[$hitsArrs[$i]['GeneID']])){
            array_push($tmpHitsArr[$hitsArrs[$i]['GeneID']], $tmpHitValue);
          }  
        }
      }elseif($hitsArrs[$i]['HitGI']){
        $tempHitGI = $hitsArrs[$i]['HitGI']."_GI";
        if(!array_key_exists($tempHitGI, $hitsNameArr)){
          $SQL = "SELECT `GeneName` FROM `Protein_Class` WHERE `LocusTag`='".$hitsArrs[$i]['LocusTag']."'";
          if($GeneNameArr = $PROTEINDB->fetch($SQL) && $GeneNameArr['GeneName'] != "-"){
            $GeneName = str_add_double_quote($GeneNameArr['GeneName']);
            if(!$hitGeneNameFlag) $hitGeneNameFlag = 1;
          }else{
            $GeneName = '';
          }
          if((!$hitsArrs[$i]['LocusTag'] || $hitsArrs[$i]['LocusTag'] == "-") && !$GeneName){
            $hitsNameArr[$tempHitGI] = ",".str_add_double_quote($hitsArrs[$i]['HitGI']);
          }else{
            $hitsNameArr[$tempHitGI] = str_add_double_quote($hitsArrs[$i]['LocusTag']).",".$GeneName;
          }
        }
        if(isset($Pid)){
          $tmpHitValue = $hitsArrs[$i]['HitGI'].":".$hitsArrs[$i]['Expect']."(".$hitsArrs[$i]['Pep_num']."-".$hitsArrs[$i]['Pep_num_uniqe']."-".$hitsArrs[$i]['Coverage'].")";      
        }else{
          $tmpHitValue = $hitsArrs[$i]['Expect']."(".$hitsArrs[$i]['Pep_num']."-".$hitsArrs[$i]['Pep_num_uniqe']."-".$hitsArrs[$i]['Coverage'].")";              
        }
        if(!array_key_exists($tempHitGI, $tmpHitsArr)){
          $tmpHitsArr[$tempHitGI] = array();
          array_push($tmpHitsArr[$tempHitGI], $tmpHitValue);
        }else{
          if(!in_array($tmpHitValue, $tmpHitsArr[$tempHitGI])){
            array_push($tmpHitsArr[$tempHitGI], $tmpHitValue);
          }  
        }
      }else{
        $noIDnum++;
        $noID = "noID_".$noIDnum;
        $GeneName = '';
        $hitsNameArr[$noID] = str_add_double_quote($hitsArrs[$i]['LocusTag']).",".$GeneName;
        if(isset($Pid)){
          $tmpHitValue = "noPid:".$hitsArrs[$i]['Expect']."(".$hitsArrs[$i]['Pep_num']."-".$hitsArrs[$i]['Pep_num_uniqe']."-".$hitsArrs[$i]['Coverage'].")";      
        }else{
          $tmpHitValue = $hitsArrs[$i]['Expect']."(".$hitsArrs[$i]['Pep_num']."-".$hitsArrs[$i]['Pep_num_uniqe']."-".$hitsArrs[$i]['Coverage'].")";      
        }
        array_push($tmpHitsArr[$noID], $tmpHitValue);
      }
    }
    $baitNameArr[$value] = $tmpHitsArr;
  }
}

$fileNameStr = implode(',', $fileNameArr);
$handle_write = fopen($fileFullName, "w");
$titleStr = "Generated date: ".$today = @date("Y-F-j")."      Project Name: ".$AccessProjectName;
fwrite($handle_write, $titleStr."\r\n\r\n");

$titleStr = ",,". $fileNameStr ."\r\n";
fwrite($handle_write, $titleStr);

$baitLableStr = implode(",", $baitLableArr);
$titleStr = ",,". $baitLableStr ."\r\n";
fwrite($handle_write, $titleStr);

$titleStr = ",". $typeStr ."\r\n\r\n";
fwrite($handle_write, $titleStr);

$titleStr = '';
if($hitYorfNameFlag){
  $titleStr = "HITS";
}  
if($hitGeneNameFlag){
  $titleStr .= ",GENE";
}else{
  $titleStr .= ",";
}

foreach($baitNameArr as $baitID => $hitskeys){
  if(isset($Pid)){
    $titleStr .= ",PID:SC(PT-PU-C%)";
  }else{
    $titleStr .= ",SC(PT-PU-C%)";
  }  
}
fwrite($handle_write, $titleStr."\r\n\r\n");
foreach($hitsNameArr as $key => $GeneName){
  $strLine = $GeneName;
  foreach($baitNameArr as $baitID => $hitskeys){
    if(array_key_exists($key, $hitskeys)){
      $tempStrLine = implode(";", $hitskeys[$key]);
      $strLine .= ",".str_add_double_quote($tempStrLine);
    }else{
      $strLine .= ",";
    }
  }
  fwrite($handle_write, $strLine."\r\n");
}

fclose($handle_write);

dl_file($fileFullName);

function dl_file($file){
  //First, see if the file exists
  if (!_is_file($file)) { die("<b>404 File not found!</b>"); }
  //Gather relevent info about file
  $len = _filesize($file);
  $filename = basename($file);
  $file_extension = strtolower(substr(strrchr($filename,"."),1));
  //This will set the Content-Type to the appropriate setting for the file
  switch( $file_extension ) {
    case "pdf": $ctype="application/pdf"; break;
    case "exe": $ctype="application/octet-stream"; break;
    case "zip": $ctype="application/zip"; break;
    case "doc": $ctype="application/msword"; break;
    case "xls": $ctype="application/vnd.ms-excel"; break;
    case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
    case "gif": $ctype="image/gif"; break;
    case "png": $ctype="image/png"; break;
    case "jpeg":
    case "jpg": $ctype="image/jpg"; break;
    case "mp3": $ctype="audio/mpeg"; break;
    case "wav": $ctype="audio/x-wav"; break;
    case "mpeg":
    case "mpg":
    case "mpe": $ctype="video/mpeg"; break;
    case "mov": $ctype="video/quicktime"; break;
    case "avi": $ctype="video/x-msvideo"; break;
    //The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
    case "php":
    case "htm":
    case "html":
    case "txt": die("<b>Cannot be used for ". $file_extension ." files!</b>"); break;
    default: $ctype="application/force-download";
  }

  //Begin writing headers
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: public"); 
  header("Content-Description: File Transfer");
  
  //Use the switch-generated Content-Type
  header("Content-Type: $ctype");

  //Force the download
  $header="Content-Disposition: attachment; filename=".$filename.";";
  header($header);
  header("Content-Transfer-Encoding: binary");
  header("Content-Length: ".$len);
  @readfile($file);
  exit;
}

function str_add_double_quote($inStr){
  $inStr = str_replace('"','""', $inStr);
  $inStr = '"'.$inStr.'"';
  return $inStr;
}

function empty_dir($path){
  if(!is_dir($path)) return;
  if ($d=opendir($path)) {
    while ($f=readdir($d)) {
       if ($f=='.' || $f=='..')  continue;
       $f=$path.'/'.$f;
       if (is_file($f) and preg_match("/zip|csv/", $f))  unlink($f);
    }
    closedir($d);
  }
}
?>