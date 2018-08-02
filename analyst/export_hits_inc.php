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
  $current_bait_id = '';  
  if(!$infile){
   exit;
  }
  $filename_in = $infile;
  if(!$handle_read = fopen($filename_in, "r")){
    echo "cannot open file $filename_in";
    exit;
  }
  
  if($theaction == 'generate_report' && ($php_file_name == "cytoscape_export" || ($php_file_name == 'export_hits' && isset($export_version) && !$export_version))){
    $outDir = "../TMP/".strtolower($type)."_report/";
    if(!_is_dir($outDir)) _mkdir_path($outDir);
    $filename_out = $outDir.$_SESSION['USER']->ID."_".strtolower($type).".".$fileExtention;    
    if(!$handle_write = fopen($filename_out, "w")){
      echo "cannot open file $filename_out";
      exit;
    }
    $filename_log = $outDir.$_SESSION['USER']->ID."_".strtolower($type)."_log.csv";
    if(!$log_handle_write = fopen($filename_log, "a")){
      echo "cannot open file $filename_log";
      exit;
    } 
  }
//==== export filter info ==============================================  
  if($theaction == "view_preview"){
    export_filter_info($handle_read);
  }else{
    export_filter_info($handle_read,$handle_write);
  }
//======================================================================


//======get OF info from OF_map and put them to array $OF_info_arr======
//########################################################################################### remove $USER->ID == '1'
  $OF_info_arr = array();
  if($USER->Type == 'Admin'){  //##########
    if(is_file($OF_map_file)){
      $OF_info_arr_tmp = file($OF_map_file);
      foreach($OF_info_arr_tmp as $OF_info_val_tmp){
        $tmp_OF_arr = explode('::', trim($OF_info_val_tmp));       
        if(count($tmp_OF_arr)==2 && $tmp_OF_arr[0] && $tmp_OF_arr[1]){
          $arr_format1 = explode(',',$tmp_OF_arr[1]);
          $arr_format2 = array();
          foreach($arr_format1 as $arr_format1_val){
            $tmp_arr = explode('=',$arr_format1_val);
            $arr_format2[$tmp_arr[0]] = $tmp_arr[1];
          }   
          $arr_format3 = array();
          foreach($OpenFreezer as $OF_key => $OF_val){
            $arr_format3[] = $OF_val.'==='.$arr_format2[$OF_key];
          }
          $OF_info_str = implode(',',$arr_format3);
          $OF_info_arr[$tmp_OF_arr[0]] = $OF_info_str;
        }
      }
    }
  }
/*echo "<pre>";
print_r($OF_info_arr);
echo "</pre>";
exit;*/
//======================================================================
  
  $uniqe_hit_gene_arr = array();
  $mapArr = array(); 
  $Protein_Length_index = '';
  
  $task_para = '';
  if(stristr($selecte_columns_str, '@Sample___SearchPar')){
    $selecte_columns_str = str_replace("@Sample___SearchPar", "", $selecte_columns_str);
    $task_para = "@Sample___SearchPar";
  }
  
  $formated_columns_str = format_key_map($selecte_columns_str);
  $columnsArr = explode("@", $formated_columns_str);
 
  $level4SelectedCols = '';
  $selectedItemArr = array();
  foreach($columnsArr as $columnsVal){
    if(preg_match("/^level4___(.+)/", $columnsVal, $matches)){
      if($level4SelectedCols) $level4SelectedCols .= ',';
      $level4SelectedCols .= $matches[1];
    }elseif(preg_match("/^(\w+)___(.+)/", $columnsVal, $matches)){
      if(!in_array($matches[1], $selectedItemArr)){
        array_push($selectedItemArr, $matches[1]);
      }
    }
  }
//-----------------------------------------------------------------------------------------------------------
  $level_arr = array('Bait' => '0', 'Experiment' => '1', 'Gel' => '2', 'Lane' => '3', 'Plate' => '4', 'Sample' => '5', 'PlateWell' => '6');
  $lowest_level_flag = 0;
  $lowest_level_item = '';
  if($level4SelectedCols){
    $mapArr['level4_title'] = $LableArr['level4'];
  }elseif(!in_array('level3', $selectedItemArr)){
    $lowest_level_item = 'Bait';
    foreach($selectedItemArr as $value){
      if($level_arr[$value] > $lowest_level_flag){
        $lowest_level_flag = $level_arr[$value];
        $lowest_level_item = ($value=='PlateWell')?'Sample':$value;
      }
    }
  }
  $lowest_level_item_unique_id_arr = array();
//------------------------------------------------------------------------------------------------------------  
  $previewArr = array();
  $protein_ID_arr = array();
  
  $Modifications_Col = '';
  $pattern_m = '';
  
  if(isset($modification_str) && $modification_str){
    if($modification_str == 'ALL'){
      $Modifications_Col = " AND Modifications IS NOT NULL AND Modifications != ''";
    }else{
      if($SearchEngine == 'GPM' || $SearchEngine == 'SEQUEST'){
        $modification_str = str_replace("+", '\+' ,$modification_str);
        $modification_str = str_replace(",", "|" ,$modification_str);
        $modification_str = str_replace(" ", "\s*\[[\d,]+\]\s*" ,$modification_str);
        $pattern_m = "/".$modification_str."/";
      }elseif($SearchEngine == 'Mascot'){
        $modification_str = str_replace("(", "\(",$modification_str);
        $modification_str = str_replace(")", "\)",$modification_str);
        $modification_str = str_replace(",", "(,[A-Z]\d+)+|\d ",$modification_str);
        $pattern_m = "/\d ".$modification_str."(,[A-Z]\d+)+/";
      }
    }
  }
  
/*echo "<pre>";
echo "\$columnsArr=<br>";
print_r($columnsArr); 
print_r($selectedItemArr);
echo "</pre>";*/  
  
  if($handle_read){
    while (!feof($handle_read)){
      $level4SelectedCols_tmp = $level4SelectedCols;
      $buffer = fgets($handle_read);
      $buffer = trim($buffer);      
      if(!$buffer) continue;
      if(isset($handle_version_log) && $handle_version_log && preg_match("/^Bait::(.+)/", $buffer,$matches)){
//      Bait ID===3345,Bait Tax ID===4932,Bait Gene ID===852943,Bait Acc===6321489,Bait Gene Name===FMP48-TAP (TAP;test mutation),Bait Acc Type===GI,Bait Locus Tag===YGR052W,Bait MW===42.820,Bait Clone===9GS4  C-2,Bait Vector===,Bait Description===Fmp48p,Is Gel Free===Y
        $tmpArr = explode(',',$matches[1]);
        $key_value_arr = array();
        foreach($tmpArr as $tmpVal){
          $tmpArr2 = explode("===",$tmpVal);
          $key_value_arr[$tmpArr2[0]] = $tmpArr2[1];
        }
        $version_log_line = $key_value_arr['Bait ID'].",".$key_value_arr['Bait Gene ID'].",".$key_value_arr['Bait Gene Name'];
        fwrite($handle_version_log, $version_log_line."\r\n");
      }
      if(preg_match("/^(level3)::(.+)/i", $buffer, $matches)){
        $tmpArr1 = explode($mapfileDelimit,$matches[2]);

        if(is_numeric($Protein_Length_index)) $tmpArr1[$Protein_Length_index] = "Hit Protein Length";

        $titleKey = $matches[1].'_title';
        $mapArr[$titleKey] = $tmpArr1;        
      }elseif(preg_match("/^(\w+)::(.+)/i", $buffer, $matches)){
        if(!in_array($matches[1], $selectedItemArr)) continue;
        $tmpArr1 = explode($mapfileDelimit,$matches[2]);
        $titleArr = array();
        $valueArr = array();
        $BaitVector = '';
        foreach($tmpArr1 as $tmpVal1){
          $tmpArr2 = explode('===',$tmpVal1);
          array_push($titleArr, $tmpArr2[0]);
          array_push($valueArr, $tmpArr2[1]);
          if($tmpArr2[0] =='Bait Vector'){
            $BaitVector = $tmpArr2[1];
          }
        }
        $titleKey = $matches[1].'_title';
        $valueKey = $matches[1];
        $mapArr[$titleKey] = $titleArr;
        $mapArr[$valueKey] = $valueArr;
        
//=====get vID from Bait and create OF array here=====================================
        if($USER->Type == 'Admin' && $matches[1] == 'Bait'){
//echo "\$BaitVector=$BaitVector<br>";
          if($BaitVector && isset($OF_info_arr[$BaitVector])){
            $tmpArr1 = explode($mapfileDelimit,$OF_info_arr[$BaitVector]);
          }else{
            $empty_OF_inf = "Vector ID===,Cell line ID===,Insert ID===,Insert Acc===,Insert Protein Fasta===,Entrez Gene ID===,Gene Name===,Species===";
            $tmpArr1 = explode($mapfileDelimit,$empty_OF_inf);
          }
          $titleArr = array();
          $valueArr = array();
          foreach($tmpArr1 as $tmpVal1){
            $tmpArr2 = explode('===',$tmpVal1);
            array_push($titleArr, $tmpArr2[0]);
            array_push($valueArr, $tmpArr2[1]);
          }
          $titleKey = 'OpenFreezer_title';
          $valueKey = 'OpenFreezer';
          $mapArr[$titleKey] = $titleArr;
          $mapArr[$valueKey] = $valueArr;
        }
//=====================================================================================         
      }else{
        if(!isset($titleLine) || !$titleLine){
          $titleLine = '';
          foreach($columnsArr as $columnsVal){
            $tmpArr = explode('___',$columnsVal);
            $titleKey = $tmpArr[0].'_title';
            if($titleLine) $titleLine .= $Delimit;
            $titleLine .= $mapArr[$titleKey][$tmpArr[1]];
          }
          $titleLine .= "\r\n";          
          if($theaction == 'generate_report'){          
            fwrite($handle_write, $titleLine);
          }else{
            array_push($previewArr, $titleLine);
          }
        }
        if(!$level3Arr = explode($mapfileDelimit,$buffer)) continue;       
        if(is_numeric($Protein_Length_index)){
          $protein_ID = $level3Arr[4];
          if(!array_key_exists($protein_ID, $protein_ID_arr)){
            $Sequence = GetSequence($HITSDB, $protein_ID,'','',0);
            //$Sequence = GetSequence($HITSDB, $protein_ID);
            $Protein_Length = strlen(trim($Sequence));
            $protein_ID_arr[$protein_ID] = $Protein_Length;
          }else{
            $Protein_Length = $protein_ID_arr[$protein_ID];
          }
          $level3Arr[$Protein_Length_index] = ($Protein_Length)?$Protein_Length:'';
        }
        $mapArr['level3'] = $level3Arr;
        if($level4SelectedCols_tmp){
          if($hitType == 'normal'){
            if($SearchEngine == 'SEQUEST'){
              $tableName = 'SequestPeptide';
            }else{
              $tableName = 'Peptide';
            }  
            $hitID = 'HitID';
          }elseif($hitType == 'geneLevel'){
            $tableName = 'Peptide_GeneLevel';
            $hitID = 'HitID';
          }else{
            $tableName = 'TppPeptideGroup';
            $hitID = 'ProteinID';
            $level4SelectedCols_tmp = str_replace("Modifications", "Sequence AS Modifications", $level4SelectedCols_tmp);
          }
          $SQL = "SELECT $level4SelectedCols_tmp 
                  FROM $tableName
                  WHERE $hitID='".$mapArr['level3'][0]."'$Modifications_Col";             
                  
          $tmpLevel4Arr = $HITSDB->fetchAll($SQL);
          $level_flag_counter = 0;
          if($tmpLevel4Arr){
            for($k=0; $k<count($tmpLevel4Arr); $k++){
              if(($SearchEngine == 'Mascot' || $SearchEngine == 'GPM' || $SearchEngine == 'SEQUEST') && $pattern_m){
                if(!$tmpLevel4Arr[$k]['Modifications'] || !preg_match($pattern_m, trim($tmpLevel4Arr[$k]['Modifications']))) continue;
              }elseif($SearchEngine != 'Mascot' && $SearchEngine != 'GPM' && $SearchEngine != 'SEQUEST'){
                if($modification_str){
                  $modification_arr = explode(",", $modification_str);
                  $tmp_str = '';
                  foreach($modification_arr as $val){
                    if(strpos($tmpLevel4Arr[$k]['Modifications'], $val) !== FALSE){
                      if($tmp_str) $tmp_str .= ';';
                      $tmp_str .= $val;
                    }
                  }
                  if(!$tmp_str && $frm_modification_type != 'ALL_ALL') continue;
                  $tmpLevel4Arr[$k]['Modifications'] = $tmp_str;
                }elseif(isset($tmpLevel4Arr[$k]['Modifications'])){
                  $tmp_Modifications = $tmpLevel4Arr[$k]['Modifications'];                
                  $tmpLevel4Arr[$k]['Modifications'] = '';  
                  if(preg_match_all('/(\w\[\d+\])/',$tmp_Modifications,$matches)){
                    foreach($matches[1] as $matches_val){
                      if($tmpLevel4Arr[$k]['Modifications']) $tmpLevel4Arr[$k]['Modifications'] .= ";";
                      $tmpLevel4Arr[$k]['Modifications'] .= $matches_val;
                    }
                  }
                }
              }
              $mapArr['level4'] = str_replace(",", ";", $tmpLevel4Arr[$k]);
              $mapArr['level4'] = str_replace("\n", "", $mapArr['level4']);
              $mapArr['level4'] = str_replace("\r", "", $mapArr['level4']);
              write_file_line();
              $level_flag_counter++;
            }
          }else{
            if(!$modification_str) write_file_line();
          }        
        }else{
          $level_flag_counter = 0;
          write_file_line();
        }
        if(count($previewArr) > 20) break;
      }
    }
  }
?>