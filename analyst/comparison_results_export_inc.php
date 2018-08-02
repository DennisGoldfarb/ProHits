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

if(isset($bio_checked_arr)){
  $bio_checked_arr = $_SESSION["bio_checked_arr"];
}else{
   $bio_checked_arr = array();
}

if($bio_checked_arr && $theaction == "showImage"){
  include("filter_biogrid.inc.php");
  if(!_is_dir("../TMP/comparison/")) _mkdir_path("../TMP/comparison/");
  if(!_is_dir("../TMP/comparison/P_$AccessProjectID/")) _mkdir_path("../TMP/comparison/P_$AccessProjectID/");
  $subDir = "../TMP/comparison/P_$AccessProjectID/";
  $bio_grid_info_file_name = $subDir.$AccessUserID."_bio_grid_info.txt";
  $bio_grid_info_handle = fopen($bio_grid_info_file_name, "r");
  if(!$bio_grid_info_handle){
    echo "Cannot open file $reportFileName";
  }
  while(!feof($bio_grid_info_handle)) {
    $buffer = fgets($bio_grid_info_handle);
    $buffer = trim($buffer);
    $tem_arr = explode("=",$buffer);
    if($tem_arr[0] == 'baitGeneIDstr'){
      $baitGeneIDstr = $tem_arr[1];
    }elseif($tem_arr[0] == 'group_geneID_map_arr'){
      if(!trim($tem_arr[1])){
        $group_geneID_map_arr = array();
      }else{
        $tmp_arr_2 = explode('@@',$tem_arr[1]);
        foreach($tmp_arr_2 as $tmp_val_2){
          $tmp_arr_3 = explode(";;",$tmp_val_2);
          $group_geneID_map_arr[$tmp_arr_3[0]] = explode(",,",$tmp_arr_3[1]);
        }
      }  
    }elseif($tem_arr[0] == 'grid_bait_hits_arr'){
      if(!trim($tem_arr[1])){
        $grid_bait_hits_arr = array();
      }else{
        $tmp_arr_2 = explode('@@',$tem_arr[1]);
        foreach($tmp_arr_2 as $tmp_val_2){
          $grid_bait_hits_arr[$tmp_val_2] = array();
        }
      }  
    }elseif($tem_arr[0] == 'allBaitgeneIDarr'){
      if(!trim($tem_arr[1])){
        $allBaitgeneIDarr = array();
      }else{
        $tmp_arr_2 = explode('@@',$tem_arr[1]);
        foreach($tmp_arr_2 as $tmp_val_2){
          $tmp_arr_3 = explode(";;",$tmp_val_2);
          $allBaitgeneIDarr[$tmp_arr_3[0]] = $tmp_arr_3[1];
        }
      }  
    }elseif($tem_arr[0] == 'itemIdIndexArr'){
      if(!trim($tem_arr[1])){
        $itemIdIndexArr = array();
      }else{
        $itemIdIndexArr = explode(',',$tem_arr[1]);
      }        
    }
  }
  fclose($bio_grid_info_handle);
  
  get_grid_baitGene_to_hitsGene_arr($baitGeneIDstr,$grid_bait_hits_arr,$grid_hits_gene_arr);
  if(!$grid_hits_gene_arr) $no_grid_data = 1;
  $bio_checked_str = implode(",", $bio_checked_arr);
}

if($exportType == 'matrix'){
  if(isset($saint_ID) || isset($DIAUmpireQuant_ID)){
    $lableFormatArr = explode(",", $line_info_str);
    $P_index = array();
    if(isset($saint_ID)){
      $P_index['SPECSUM'] = 1;
    }elseif(isset($DIAUmpireQuant_ID)){
      $P_index['INTENSITYSUM'] = 1;
    }  
    for($i=0;$i<count($lableFormatArr);$i++){
      $P_index[$lableFormatArr[$i]] = $i+2;
    }    
  }else{
    if($Is_geneLevel){
      $lableFormatArr['SpectralCount'] = 'Spectral Count';
      $lableFormatArr['Unique'] = 'Unique Peptide Number';
      $P_index['SpectralCount'] = 1;
      $P_index['Unique'] = 2;
      $P_index['Fequency'] = 4;
    }else{        
      $lableFormatArr[$Expect] = $ExpectLable;
      $lableFormatArr['Pep_num'] = 'Total Peptide Number';
      $lableFormatArr['Pep_num_uniqe'] = 'Unique Peptide Number';
      $lableFormatArr['Coverage'] = 'Coverage';
      $P_index[$Expect] = 1;
      $P_index['Pep_num'] = 2;
      $P_index['Pep_num_uniqe'] = 3;
      $P_index['Coverage'] = 4;
      $P_index['Fequency'] = 5;
      $P_index['Shared_Fequency'] = 6;
    }
    $lableFormatArr['Fequency'] = 'Project Frequency';
    $lableFormatArr['Shared_Fequency'] = 'Shared Fequency';
  }
  $field_lable_key_arr = explode(",", $field_lable_key_str);
  $field_lable_val_arr = explode(",", $field_lable_val_str);
  $field_lable_arr = array_combine($field_lable_key_arr, $field_lable_val_arr);  
}

$comparisonDir = "../TMP/comparison/";
if(!_is_dir($comparisonDir)) _mkdir_path($comparisonDir);
$subDir = "../TMP/comparison/P_$AccessProjectID/";
if(!_is_dir($subDir)) _mkdir_path($subDir);

$filename_in = $infileName;
if($exportType == 'matrix'){
  $filename_out = $subDir.$AccessUserID."_comparison_matrix.csv";
}else{
  $filename_out = $subDir.$AccessUserID."_comparison_table.csv";
}
$handle_write = fopen($filename_out, "w");
$handle_read = fopen($filename_in, "r");

$saint_file_info = array();
if(isset($saint_ID)){
  $SQL = "SELECT ID,
         `Name`,
         `UserID`, 
         `Date` , 
         `Description`, 
         `Status` , 
         `ProjectID`, 
         `UserOptions`
          FROM SAINT_log 
          WHERE ID=$saint_ID";
  
}elseif(isset($DIAUmpireQuant_ID)){
  $SQL ="SELECT `ID`, 
                `Name`, 
                `UserID`, 
                `Date`, 
                `Machine`, 
                `SearchEngine`, 
                `TaskIDandFileIDs`, 
                `Status`, 
                `ProjectID` 
          FROM `DIAUmpireQuant_log` 
          WHERE ID='$DIAUmpireQuant_ID'";
}
$saint_file_info = $PROHITSDB->fetch($SQL);

if($saint_file_info){
  if(isset($saint_ID)){
    $Name_title = "SAINT Name: ";
    $Owner_title = "SAINT Owner: ";
    $Date_title = "SAINT Date: ";
  }elseif(isset($DIAUmpireQuant_ID)){
    $Name_title = "DIAUmpireQuant Name: ";
    $Owner_title = "DIAUmpireQuant Owner: ";
    $Date_title = "DIAUmpireQuant Date: ";
  }
  $info_line = $Name_title.$saint_file_info['Name']."\r\n";
  fwrite($handle_write, $info_line);
  $Owner = get_userName($PROHITSDB, $saint_file_info['UserID']);
  $info_line = $Owner_title.$Owner."\r\n";
  fwrite($handle_write, $info_line);
  $info_line = $Date_title.$saint_file_info['Date']."\r\n";
  fwrite($handle_write, $info_line);
} 

export_filter_info($handle_read,$handle_write);

$groupNames = '';
$emptyCell = "                ";
$colorMode = '';
$groupSubLineArr = array();
$groupLableLineStr = '';
$groupLableLineArr = array();
$itemLableInfo = '';
$totalitems = 0;

$ProteinDB = new mysqlDB(PROHITS_PROTEINS_DB);

if($exportType == 'matrix'){
  if($report_style == "multiple"){
    if(isset($saint_ID)){
      if(in_array("SAINTSCORE", $lableFormatArr)){
        $explaneStr = "PID:TSp(MSp-ASa-MSa-NRe-F%-SF%-SC)   Protein ID:Total Spec(Max Spec-Avg SAINT-Max SAINT-Number of repeats-Frequency-Shared Frequency-Saint Score),,,,,";    
      }else{
        $explaneStr = "PID:TSp(MSp-ASa-MSa-NRe-F%-SF%)   Protein ID:Total Spec(Max Spec-Avg SAINT-Max SAINT-Number of repeats-Frequency-Shared Frequency),,,,,";    
      }
    }elseif(isset($DIAUmpireQuant_ID)){  
      $explaneStr = "PID:TInt(Int-ASa-MSa-NRe-F%-SF%-HC)   Protein ID:Total Intensity(Intensity-Avg SAINT-Max SAINT-Number of repeats-Frequency-Shared Frequency-High_Cofidence),,,,,";          
    }elseif($Is_geneLevel){
      $explaneStr = "PID:SC(PU-S-F%-SF%)   Protein ID:SpectralCount(Unique Peptide Numbe-Subsumed-Frequency-Shared Frequency),,,,,";
    }else{
      $explaneStr = "PID:SC(PT-PU-C%-F%-SF%)   Protein ID:Score(Total Peptide Number-Unique Peptide Numbe-Coverage-Frequency-Shared Frequency),,,,,";
    }
    fwrite($handle_write, $explaneStr."\r\n\r\n");
  }  
}
$BaitIndexArr = array();
$exportArr = array();
$baitGeneIDarr = array();
$bioGrid_overlap = '';
if($handle_read){
  while(!feof($handle_read)){
    $buffer = fgets($handle_read);
    $buffer = trim($buffer);
    if(preg_match('/^bioGrid_only:/', $buffer, $matches)){
      if($exportType == 'table'){
        break;
      }else{
        fwrite($handle_write, $buffer."\r\n");
      }   
    }elseif(preg_match('/^bioGrid_overlap;;(.*)/', $buffer, $matches)){
      $bioGrid_overlap = $matches[1];
    }elseif(preg_match('/^colorMode;;(.*)/', $buffer, $matches)){
      $colorMode = $matches[1];
    }elseif(preg_match('/^totalitems;;(.*)/', $buffer, $matches)){
      $totalitems = $matches[1];
    }elseif(preg_match('/^itemLableInfo;;(.*)/', $buffer, $matches)){
      $itemLableInfo = $matches[1];
    }elseif(preg_match('/^baitGeneIDstr;;(.*)/', $buffer, $matches)){
      $baitGeneIDarr = explode(",", $matches[1]);      
    }elseif(preg_match('/^du_NameGeneID;;(.*)/', $buffer, $matches)){
      //---------
    }elseif(preg_match('/^groupInfo;;(.*)/', $buffer, $matches)){
      $groupInfo = $matches[1];
      if(!$matches[1]){
        if($exportType == 'matrix'){
          if($report_style == "multiple"){
            $lableLineStr = ',,,'.$itemLableInfo."\r\n";
          }else{  
            $lableLineStr = ',,,,'.$itemLableInfo."\r\n";
          }
        }       
      }else{
        $tmpArr1 = explode('@@',$matches[1]);
        $tmpLength = 0;
        foreach($tmpArr1 as $value1){
          $tmpArr2 = explode('##',$value1);
          if($groupLableLineStr) $groupLableLineStr .= ',';
          $groupLableLineStr .= $tmpArr2[1];
          $tmpArr3 = explode(',', $tmpArr2[2]);
          if(count($tmpArr3) > $tmpLength) $tmpLength = count($tmpArr3);
          array_push($groupSubLineArr, $tmpArr3);
        }
        for($j=0; $j<$tmpLength; $j++){
          $tmpStr = '';
          for($i=0; $i<count($groupSubLineArr); $i++){
            if($i) $tmpStr .= ',';
            if(isset($groupSubLineArr[$i][$j])){
              $tmpStr .= $groupSubLineArr[$i][$j];
            }else{
              $tmpStr .= '';
            }
          }
          array_push($groupLableLineArr, $tmpStr);
        }
        if($exportType == 'matrix'){
          if($report_style == "multiple"){
            $lableLineStr = ',,,'.$groupLableLineStr.','.$itemLableInfo."\r\n";
          }else{
            $lableLineStr = ',,,,'.$groupLableLineStr.','.$itemLableInfo."\r\n";
          }
        }   
      }
      if($exportType == 'matrix'){
        fwrite($handle_write, $lableLineStr);
      }   
      $BaitGeneNameArr = array();
      $BaitIdArr = array();
      $BaitGineidArr = array();
      foreach($groupLableLineArr as $value){
        if($exportType == 'matrix'){
          if($report_style == "multiple"){
            $tmpStr2 = ',,,'.$value."\r\n";
          }else{
            $tmpStr2 = ',,,,'.$value."\r\n";
          }  
          fwrite($handle_write, $tmpStr2);
        }  
        $tmpArr = explode(',',$value);
        for($i=0; $i<count($tmpArr); $i++){
          if($tmpArr[$i]){
            if($exportType == 'table' || $exportType == 'matrix'){
              $tmpArr2 = explode(" ", $tmpArr[$i],3);
              $baitName = $tmpArr2[1];
              $baitID = $tmpArr2[0];
              $baitGeneID = (isset($tmpArr2[2]))?$tmpArr2[2]:'';
            }else{
              $baitName = $tmpArr[$i];
            }
            if(isset($BaitGeneNameArr[$i])){
              if($exportType == 'table' || $exportType == 'matrix'){
                $BaitIdArr[$i] .= "|";
                $BaitIdArr[$i] .= $baitID;
                $BaitGineidArr[$i] .= "|";
                $BaitGineidArr[$i] .=$baitGeneID;
              }
              $BaitGeneNameArr[$i] .= "|";
              $BaitGeneNameArr[$i] .= $baitName;
            }else{
              if($exportType == 'table' || $exportType == 'matrix'){
                $BaitIdArr[$i] = $baitID;
                $BaitGineidArr[$i] =$baitGeneID;
              }
              $BaitGeneNameArr[$i] = $baitName;
            }
          }
        }
      }  
      if($exportType == 'table' || $exportType == 'matrix'){
        for($i=0; $i<count($BaitGeneNameArr); $i++){
          $BaitGeneNameArr[$i] = $BaitIdArr[$i].",".$BaitGeneNameArr[$i].",".$BaitGineidArr[$i];
        }
      }
      $tmpArr = explode(',',$itemLableInfo);      
      foreach($tmpArr as $tmpVal){
        if(!preg_match('/^Control Group|^Merged Group/', $tmpVal)){
          if($exportType == 'table' || $exportType == 'matrix'){
            $tmpArr2 = explode(" ", $tmpVal,3);
            if(count($tmpArr2) == 3){
              $baitName = $tmpArr2[0].",".$tmpArr2[1].",".$tmpArr2[2];
            }elseif(count($tmpArr2) == 2){
              if(isset($saint_ID)){
                $baitName = $tmpArr2[0].",".$tmpArr2[1];
              }else{
                $baitName = $tmpArr2[0].",".$tmpArr2[1].",";
              }
            }else{
              $baitName = $tmpArr2[0];
            }  
          }else{
            $baitName = $tmpVal;
          }  
          array_push($BaitGeneNameArr, $baitName);
        }  
      } 
      if($exportType == 'matrix'){
        $bioGrid_title = '';
        if($bioGrid_overlap == "yes") $bioGrid_title = "[bioGrid]";
        if($report_style == "multiple"){
          if(isset($saint_ID)){
            if(in_array("SAINTSCORE", $lableFormatArr)){
              $tmpStr2  = "\r\n"."Gene ID,Gene Name,LocusTag".str_repeat(",PID:TSp(MSp-ASa-MSa-NRe-F%-SF%-SC) $bioGrid_title", $totalitems);
            }else{
              $tmpStr2  = "\r\n"."Gene ID,Gene Name,LocusTag".str_repeat(",PID:TSp(MSp-ASa-MSa-NRe-F%-SF%) $bioGrid_title", $totalitems);
            }
          }elseif(isset($DIAUmpireQuant_ID)){  
            $tmpStr2 = "\r\n"."Gene ID,Gene Name,LocusTag".str_repeat("PID:TInt(Int-ASa-MSa-NRe-F%-SF%-HC) $bioGrid_title", $totalitems);
          }elseif($Is_geneLevel){
            $tmpStr2  = "\r\n"."Gene ID,Gene Name,LocusTag".str_repeat(",PID:SC(PU-S-F%-SF%) $bioGrid_title", $totalitems);
            //$explaneStr = "PID:SC(PU-S-F%-SF%)   Protein ID:SpectralCount(Unique Peptide Numbe-Subsumed-Frequency-Shared Frequency),,,,,";
          }else{
            $tmpStr2  = "\r\n"."Gene ID,Gene Name,LocusTag".str_repeat(",PID:SC(PT-PU-C%-F%-SF%) $bioGrid_title", $totalitems);
          }  
        }else{
          $tmpStr2  = "\r\n"."Gene ID,Gene Name,LocusTag,Protein ID".str_repeat(','.$field_lable_arr[$orderby]." ".$bioGrid_title, $totalitems);
        }    
        fwrite($handle_write, $tmpStr2."\r\n");
      }else{
        $bioGrid_title = '';
        if($bioGrid_overlap == "yes") $bioGrid_title = ",BioGrid";
        
        foreach($BaitGeneNameArr as $key => $value){
          $exportArr[$key] = array();
          $BaitIndexArr[$value] = $key;
        }
        $IdNameLable = "Bait Gene Name,Bait Gene ID";
        if($exportType == 'table'){
          if(isset($saint_ID)){
            $IdNameLable = "Saint Bait Name,Bait Gene ID";
          }elseif($currentType == 'Bait'){
            $IdNameLable = "Bait ID,Bait Gene Name,Bait Gene ID";
          }elseif($currentType == 'Exp'){ 
            $IdNameLable = "Experiment ID,Bait Gene Name,Bait Gene ID"; 
          }else{
            $IdNameLable = "Sample ID,Bait Gene Name,Bait Gene ID";
          }
        }
        if($hitType == 'TPP'){
          $titleStr = "$IdNameLable,Protein Gene Name,Protein Gene ID,Protein ID,Protein Probability,Peptide Number,Unique Peptide Number,Coverage,Frequency,Shared Frequency".$bioGrid_title;
        }else{
          $Hit_Score = "Hit Score";
          if($hitType == 'GPM') $Hit_Score = "GPM Expect";
          if(isset($saint_ID)){
            $titleStr = "$IdNameLable,Prey Gene Name,Prey Gene ID,Prey Protein ID,Total Spec,Max Spec,Avg SAINT,Max SAINT,Number of Repeats,Frequency,Shared Frequency".$bioGrid_title;
          }else{
            if($Is_geneLevel){
              $titleStr = "$IdNameLable,Hit Gene Name,Hit Gene ID,Hit Protein ID,Spectral Count,Unique Peptide Number,Subsumed,Frequency,Shared Frequency".$bioGrid_title;            
            }else{
              $titleStr = "$IdNameLable,Hit Gene Name,Hit Gene ID,Hit Protein ID,$Hit_Score,Peptide Number,Unique Peptide Number,Coverage,Frequency,Shared Frequency".$bioGrid_title;
            }
          }
        }
        fwrite($handle_write, $titleStr."\r\n");
      }       
    }elseif(preg_match('/^itemlableMaxL;;/', $buffer)){ 
      continue;
    }else{
      if(!$buffer) continue;          
      if($colorMode == 'shared'){
        $tmpArr = explode('@',$buffer);
        $tmpStr2 = $tmpArr[0];
      }else{
        $tmpStr2 = $buffer;
      }            
      if($exportType == 'matrix'){
        $tmpArr2 = explode(",",$tmpStr2);
        $hitGeneID = $tmpArr2[0];
        if($tmpArr2[2] == "-") $tmpArr2[2] = '';
        $tmpDataStr = '';
        for($i=0;$i<3;$i++){
          if($report_style != "multiple"){
            $tmpDataStr .= $tmpArr2[$i].',';
          }else{
            if($i == 2){
              $tmpDataStr .= $tmpArr2[$i];
            }else{
              $tmpDataStr .= $tmpArr2[$i].',';
            }
          }     
        }
         
        $first_pid_find = 0;
        $tmpDataStr_sub = '';
        for($i=3;$i<count($tmpArr2);$i++){
          $bioGrid_str = '';
          if($bio_checked_arr && $theaction == "showImage"){
            $index = $i - 3;
            $itemIdIndex = $itemIdIndexArr[$index];
            $hitsGeneIdIndex = $hitGeneID;
            $bioGrid_str = get_bioGrid_typeStr($itemIdIndex,$hitGeneID);
          }       
          if($tmpArr2[$i]){
            if($report_style != "multiple"){              
              $tmp_arr = explode(")",$tmpArr2[$i]);
              if(count($tmp_arr) == 2){
                $newStr = $tmp_arr[0];
                $bio_gred_lable = $tmp_arr[1];
              }else{
                if(preg_match('/^\[/', $tmp_arr[0])){
                  $newStr = '';
                  $bio_gred_lable = $tmp_arr[0];
                }else{
                  $newStr = $tmp_arr[0];
                  $bio_gred_lable = '';
                }
              }         
              preg_match("/^(.*):(.*)\((.+)/", $newStr, $tmp_newArr);
              $newArr = array();
              if(count($tmp_newArr) > 3){
                array_push($newArr, $tmp_newArr[1], $tmp_newArr[2]);
                $tmp_newArr2 = explode("-",$tmp_newArr[3]);
                $newArr = array_merge($newArr, $tmp_newArr2);
              }           
              if(!$first_pid_find){
                $first_pid_find = 1;
                if($newArr){
                  $tmpDataStr .= $newArr[0];
                }else{
                  $tmpDataStr .= '';
                }  
              }
              $property = '';
              if(isset($newArr[$P_index[$orderby]])) $property = $newArr[$P_index[$orderby]];              
              $property = str_replace("+", "-",$property);
              $tmpDataStr_sub .= ",".$property.$bio_gred_lable.$bioGrid_str;
            }else{
              $tmpDataStr_sub .= ",".$tmpArr2[$i].$bioGrid_str;
            }  
          }else{
            $tmpDataStr_sub .= ",".$bioGrid_str;
          }
          $tmpStr2 = $tmpDataStr.$tmpDataStr_sub;
        }        
        fwrite($handle_write, $tmpStr2."\r\n");
      }else{      
        $tmpArr = explode(",",$tmpStr2);
        $hitGeneName = '';
        if($tmpArr[1]){
          $hitGeneName = $tmpArr[1];
        }elseif($tmpArr[2]){
          $hitGeneName = $tmpArr[2];
        }elseif($tmpArr[0]){
          $hitGeneName = $tmpArr[0];
        }
        $hitGeneID = $tmpArr[0];
        foreach($tmpArr as $key => $value){
          if(preg_match('/^\[/', $value)) continue;
          if($key <= 2) continue;
          $index = $key - 3;
          if($value){
            $tmpVal = $value;
            $tmpAttArr = explode('(',$tmpVal,2);
            $tmpVal = str_replace(":", ",", $tmpAttArr[0]);
            $old = array("-",")");
            $new   = array(",", ",");
            $tmpVal .= ",".str_replace($old, $new, $tmpAttArr[1]);
            $tmpVal = str_replace("+++", ":", $tmpVal);
            //-----------------------------------------------------------------
            $bioGrid_str = '';
            if($bio_checked_arr && $theaction == "showImage"){
              $itemIdIndex = $itemIdIndexArr[$index];
              $hitsGeneIdIndex = $hitGeneID;
              $bioGrid_str = get_bioGrid_typeStr($itemIdIndex,$hitGeneID);
            }
            //-----------------------------------------------------------------
            $tmp_arr = explode("|", $BaitGeneNameArr[$index]);
            $Bait_gene_id = '';
            foreach($tmp_arr as $tmp_val){
              if(preg_match('/.+[ ](.+)$/', $tmp_val,$matches)){
                if($Bait_gene_id) $Bait_gene_id .= "|";
                $Bait_gene_id .= $matches[1];
              } 
            }
            if($exportType == 'table'){
              $tmpVal = $BaitGeneNameArr[$index].",".$hitGeneName.",".$hitGeneID.",".$tmpVal.$bioGrid_str;
            }else{
              $tmpVal = $BaitGeneNameArr[$index].",".(($Bait_gene_id)?$Bait_gene_id:$BaitGeneNameArr[$index]).",".$hitGeneName.",".$hitGeneID.",".$tmpVal.$bioGrid_str;
            }   
            fwrite($handle_write, $tmpVal."\r\n");
          }
        }
      } 
    }
  }
  fclose($handle_read);
  fclose($handle_write);
  
}
function get_bioGrid_typeStr($itemIdIndex,$hitsGeneIdIndex){
  global $grid_bait_hits_arr,$bio_checked_arr,$allBaitgeneIDarr; 
  $bioGrid_typeStr = '';
  if($bio_checked_arr){
    if(is_numeric($itemIdIndex)){
      $gridIndex = $allBaitgeneIDarr[$itemIdIndex];
    }else{
      $gridIndex = $itemIdIndex;
    }    
    if(array_key_exists($gridIndex, $grid_bait_hits_arr)){
      $gridHitsArr = $grid_bait_hits_arr[$gridIndex];
      if(array_key_exists($hitsGeneIdIndex, $gridHitsArr)){
        $tmp_type_arr = $gridHitsArr[$hitsGeneIdIndex];
        foreach($tmp_type_arr as $tmp_type_val){
          $bioGrid_typeStr .= "[".$tmp_type_val."]";
        }
      }
    }  
  }
  return $bioGrid_typeStr;
}
?>
