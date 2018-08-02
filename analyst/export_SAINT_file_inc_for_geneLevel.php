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
  $tmp_arr = explode(":", $frm_selected_list_str);
  $selected_band_arr = explode(",", $tmp_arr[1]);

  $applyFilters = $frm_apply_filter;
  if($SearchEngine == 'GPM'){
    $Expect = 'Expect2';
  }else{
    $Expect = 'Expect';
  }

  if(strstr($SearchEngine, 'TPP_')){
    $hitType = 'TPP';
    $ht = "T.";
  }elseif(strstr($SearchEngine, 'GeneLevel_')){
    $hitType = 'geneLevel';
    $ht = "G.";
  }else{
    $hitType = 'normal';
    $ht = "H.";
  }  
//----filter prepare----------------------
  $typeBioArr = array();
  $typeExpArr_tmp = array();
  $typeExpArr = array();
  $typeFrequencyArr = array();
  create_filter_status_arrs($typeBioArr,$typeExpArr_tmp,$typeFrequencyArr,'comparison');
  
  foreach($typeBioArr as $typeBioValue){
    $frmName = 'frm_' . $typeBioValue['Alias'];
    if(!isset($$frmName)){
      $$frmName = "0";
    }
  }
  $NStmpArr = array();
  foreach($typeExpArr_tmp as $typeExpValue){
    if($typeExpValue['Alias'] == 'OP'){
      continue;
    }elseif($typeExpValue['Alias'] == 'NS'){
      $NStmpArr = $typeExpValue;
    }else{
      array_push($typeExpArr, $typeExpValue);
    }
  }
  if($NStmpArr) array_unshift($typeExpArr, $NStmpArr);
  foreach($typeExpArr as $typeExpValue){
    if($typeExpValue['Alias'] == 'OP') continue;
    $frmName = 'frm_' . $typeExpValue['Alias'];
    if($theaction == 'generate_report'){
      $$frmName = $typeExpValue['Init'];
    }else{
      if(!isset($$frmName)){
        $$frmName = "0";
      }
    }
  }
  
  $keep_bait = isset($frm_BT) && !$frm_BT;
  $is_filter_bait = isset($frm_BT) && $frm_BT;
    
//------open files-------------------------------------------------------  
  if(!$bait_handle = fopen($bait_filename, 'w')){
    echo "Cannot open file ($bait_filename)";
    exit;
  }
  if(!$inter_handle = fopen($inter_filename, 'w')){
    echo "Cannot open file ($inter_filename)";
    exit;
  }
  $inter_filename_2 = $outDir."inter_2.dat";
  if(!$inter_handle_2 = fopen($inter_filename_2, 'w')){
    echo "Cannot open file ($inter_filename_2)";
    exit;
  }  
  
  if(!$prey_handle = fopen($prey_filename, 'w')){
    echo "Cannot open file ($prey_filename)";
    exit;
  }
  
  if(!$iRefIndex_inter_handle = fopen($iRefIndex_inter_filename, 'w')){
    echo "Cannot open file ($iRefIndex_inter_filename)";
    exit;
  }
  
  if(!$log_handle = fopen($log_filename, 'w')){
    echo "Cannot open file ($log_filename)";
    exit;
  }
  $filter_start = 0;
  $apply_ns = 0;
  $star_line = "**********************************************\r\n";
  $dech_line = "----------------------------------------------\r\n";
  
  $filter_export_arr = array();
  $filter_export_arr_2 = array();
  $filter_export_arr_3 = array();
  get_filter_array_for_export($request_arr);
  
  fwrite($log_handle, $star_line);
  write_filter_info_map($log_handle, "SAINT");  
  //write user options to log.
  $other_options = "<OTHER_OPTIONS>\r\n";
  $control_IDs = '';
  
  foreach($request_arr as $key=>$value){
    if($key == 'frm_selected_list_str'){
      $value = preg_replace("/^.+:/", "", $value);
      $other_options .= "SELECTED_ID:$selected_item_id_str\r\n";
    }else if($key == 'currentType'){
      if($value == 'Band'){
        $tmp_type = 'Sample';
      }elseif($value == 'Exp'){
        $tmp_type = 'Experiment';
      }else{
        $tmp_type = $value;
      }
      $other_options .= "ID_TYPE:$tmp_type\r\n";
    }else if($key == 'frm_start_with' AND $value){
      $other_options .= "REMOVE_PREY_PREFIX:$value\r\n";
    }else if($key == 'frm_end_with' AND $value){
      $other_options .= "REMOVE_PREY_SUFFIX:$value\r\n";
    }else if($key == 'remove_pb_same_gene' AND $value){
      $other_options .= "REMOVE_PREY_SAME_AS_BAIT:$value\r\n";
    }else if($key == 'is_count_seq_len' AND $value){
      $other_options .= "INCLUDE_PREY_LENGTH:$value\r\n";
    }else if($key == 'include_geneID' AND $value){
      $other_options .= "INCLUDE_GENE_ID:$value\r\n";
    }
  }

  if($control_arr){
    $control_IDs = implode(",", $control_arr);
    $other_options .= "CONTROL_ID:$control_IDs\r\n";
  }
  
  if($frm_is_collapse == 'no'){
    $other_options .= "IS_COLLAPSE:no\r\n";
  }else{
    $other_options .= "IS_COLLAPSE:$item_name\r\n";
  }  
  
  $other_options .= "</OTHER_OPTIONS>\r\n";
  fwrite($log_handle, $other_options);
    
  $log_line = "\r\nFile: Bait.dat\r\n$dech_line";
  fwrite($log_handle, $log_line);
  $bait_title_str = "Column 1:\tProjectID_SampleID: IP ID\r\nColumn 2:\tSaint Bait Name/Bait GeneName(geneID). Could be changed by user\r\nColumn 3:\tControl/Test\r\n";
  fwrite($log_handle, $bait_title_str);
  $log_line = "\r\nFile: Inter.dat\r\n$dech_line";
  fwrite($log_handle, $log_line);
  $inter_title_str = "Column 1:\tProjectID_SampleID: IP ID\r\nColumn 2:\tSaint Bait Name/Bait GeneName(geneID). Could be changed by user\r\nColumn 3:\tPrey ProteinID\r\nColumn 4:\tPrey Peptide Number\r\n";
  fwrite($log_handle, $inter_title_str);
  
  $log_line = "\r\nFile: Prey.dat\r\n$dech_line";
  fwrite($log_handle, $log_line);
  $prey_title_str = "Column 1:\tPrey ProteinID\r\nColumn 2:\tPrey Protein Sequence Length\r\nColumn 3:\tPrey Gene Name(geneID)\r\n\r\n";
  fwrite($log_handle, $prey_title_str); 
  
  $log_line = "\r\nFile: iRefIndex.dat\r\n$dech_line";
  fwrite($log_handle, $log_line);
  $prey_title_str = "Column 1:\tiRefIndexID\r\nColumn 2:\tpreyProteinA preyProteinB\r\nColumn 3:\tpreyGeneA preyGeneB\r\nColumn 4:\tMethod|Source\r\n";
  fwrite($log_handle, $prey_title_str);
  
  
  //------ get filtered gene id array from frequency and Ns -------------------------------\
  $frequencyFileName = '';
  if(strstr($SearchEngine, 'TPP_')){
    $frequencyFileName = 'tpp_frequency.csv';
    $hitTable = 'TppProtein';
    $ProteinAcc = 'ProteinAcc';
  }elseif(strstr($SearchEngine, 'GeneLevel_')){
    $frequencyFileName = 'geneLevel_frequency.csv';
    $hitTable = 'Hits_GeneLevel';
    $ProteinAcc = 'GeneID';
  }else{
    $frequencyFileName = $SearchEngine.'_frequency.csv';
    $hitTable = 'Hits';
    $ProteinAcc = 'HitGI';
  }
  
  $frequencyArr = array();
  get_frequency_arr($frequencyArr,$frequencyFileName);
  $filted_gene_id_arr = array();
  if($frm_apply_filter && $frm_filter_Fequency == 'Fequency' && $frm_filter_Fequency_value){
    foreach($frequencyArr as $freKey => $freVal){
      if($freVal <= $frm_filter_Fequency_value) continue;
      array_push($filted_gene_id_arr, $freKey);
    }
  }
  $NSfilteIDarr = array();
  if($frm_NS_group_id){
    get_NS_geneID($NSfilteIDarr,$frm_NS_group_id);
  }
  $filted_gene_id_arr = array_merge($filted_gene_id_arr,$NSfilteIDarr);
  $filted_gene_id_arr = array_unique($filted_gene_id_arr);
  $item_id_name_arr = array();
  $item_id_str_s = ''; 
  
  foreach($item_arr as $key => $val){
    if($item_id_str_s) $item_id_str_s .= ',';
    $item_id_str_s .= $val;
    $tmp_item_id_arr = explode(",", $val);
    foreach($tmp_item_id_arr as $val2){
      $item_id_name_arr[$val2] = preg_replace('/\s+/', '_', $key);
    }
  }
  $protein_property_arr = array();
  $protein_seq_len_arr = array();
  $no_gene_id_arr = array();
  
  $uniqe_inter_arr = array();
  $uniqe_inter_upep_arr = array(); 
  $uniqe_prey_arr = array();
  $uniqe_prey_geneID_arr = array();
  $Pep_num_times_counter_arr = array();
  $Pep_uni_num_times_counter_arr = array();

  $item_arr = array();
  foreach($item_id_name_arr as $id_key => $item_id_value){
    if($currentType == 'Bait' || $currentType == 'Exp'){
      if($frm_is_collapse == 'no'){
        $SQL = "SELECT BA.ID,
                       BA.ID as item_ID, 
                       B.GeneID,
                       B.GeneName,
                       B.GelFree 
                FROM Band BA LEFT JOIN Bait B ON(BA.BaitID=B.ID) WHERE BA.ID='$id_key'";
        $item_arr_single = $HITSDB->fetch($SQL);
        array_push($item_arr, $item_arr_single);           
      }else{
        if($currentType == 'Bait'){
          $SQL = "SELECT BA.ID,
                         BA.BaitID as item_ID,
                         B.GeneID,
                         B.GeneName,
                         B.GelFree 
                  FROM Band BA LEFT JOIN Bait B ON(BA.BaitID=B.ID) WHERE BA.BaitID='$id_key'";
        }else{
          $SQL = "SELECT BA.ID,
                         BA.ExpID as item_ID,
                         B.GeneID,
                         B.GeneName,
                         B.GelFree 
                  FROM Band BA LEFT JOIN Bait B ON(BA.BaitID=B.ID) WHERE BA.ExpID='$id_key'";
        }
        $item_arr_single = $HITSDB->fetchAll($SQL);
        if(!count($item_arr)){
          $item_arr = $item_arr_single;
        }else{
          $item_arr = array_merge($item_arr,$item_arr_single);
        }
      }
    }elseif($currentType == 'Band'){
      if($frm_is_collapse == 'no'){
        $SQL = "SELECT BA.ID,
                       BA.ID as item_ID, 
                       B.GeneID,
                       B.GeneName,
                       B.GelFree 
                FROM Band BA LEFT JOIN Bait B ON(BA.BaitID=B.ID) WHERE BA.ID='$id_key'";
        $item_arr_single = $HITSDB->fetch($SQL);
        array_push($item_arr, $item_arr_single);
      }elseif($frm_is_collapse == 'sum' || $frm_is_collapse == 'average'){
        $SQL = "SELECT BA.ID,
                         BA.BaitID as item_ID,
                         B.GeneID,
                         B.GeneName,
                         B.GelFree 
                  FROM Band BA LEFT JOIN Bait B ON(BA.BaitID=B.ID) WHERE BA.BaitID='$id_key'";
        $item_arr_single = $HITSDB->fetchAll($SQL);
        if(!count($item_arr)){
          $item_arr = $item_arr_single;
        }else{
          $item_arr = array_merge($item_arr,$item_arr_single);
        }         
      }else{
        $SQL = "SELECT BA.ID,
                       BA.ExpID as item_ID,
                       B.GeneID,
                       B.GeneName,
                       B.GelFree 
                FROM Band BA LEFT JOIN Bait B ON(BA.BaitID=B.ID) WHERE BA.ExpID='$id_key'";
        $item_arr_single = $HITSDB->fetchAll($SQL);
        if(!count($item_arr)){
          $item_arr = $item_arr_single;
        }else{
          $item_arr = array_merge($item_arr,$item_arr_single);
        }
      }
    }
  } 
  
  $item_tmp_ID_box = '';  
  $all_sample_id_str = '';
  foreach($item_arr as $item_val){
    if($all_sample_id_str) $all_sample_id_str .= ",";
    $all_sample_id_str .= $item_val['ID'];
  }
  
  $protein_id_sequence_arr = array();
  $ass_id_not_in_ass_table_arr = array();  //************************* 
  
  
  if($hitType == 'geneLevel'){
    //$aver_len = get_all_SAINT_protein_info_for_geneLevel($all_sample_id_str);
    $aver_len = 1000;
  }else{
    $aver_len = get_all_SAINT_protein_info($all_sample_id_str);
  }
 
  foreach($item_arr as $item_val){
    //--get genename filter from protein db--
    $sample_ID = $item_val['ID'];
    $baitGeneID = $item_val['GeneID'];
    $item_tmp_ID = $item_val['item_ID'];
    
    if($currentType == 'Band' && $frm_is_collapse != 'no'){
      if(!in_array($sample_ID, $selected_band_arr)) continue;
    }    
    $saint_bait_name = $item_id_name_arr[$item_tmp_ID];
    if(in_array($item_tmp_ID, $control_arr)){
      $control_text = 'C';
    }else{
      $control_text = 'T';
    }
        
    $bait_GeneID_lable = "";
    if($include_geneID) $bait_GeneID_lable = ($baitGeneID)?"(".$baitGeneID.")":"";
    
    if($item_tmp_ID_box != $item_tmp_ID){
      $item_tmp_ID_box = $item_tmp_ID;
      if($item_tmp_ID_box){
        $line = $AccessProjectID."_$item_tmp_ID$fileDelimit$saint_bait_name$bait_GeneID_lable$fileDelimit$control_text\r\n";
        fwrite($bait_handle, $line);
      }   
    }
    
    $item_hits_order_by = 'ID';    
    $subWhere = '';
    if($frm_apply_filter){
      $subWhere .= subWhere($is_filter_bait,$baitGeneID);
    }
    
    get_hits_result($sample_ID, $hits_result, $SearchEngine, $hitType);
    $bait_property = $AccessProjectID."_".$item_tmp_ID.$fileDelimit.$saint_bait_name.$bait_GeneID_lable.$fileDelimit;
    while($hitsValue = mysqli_fetch_assoc($hits_result)){
      $geneID_tmp_arr = explode(',',$hitsValue['GeneID']);
      $geneName_tmp_arr = explode(',',$hitsValue['GeneName']);
      $gene_tmp_arr = array_combine($geneID_tmp_arr, $geneName_tmp_arr);

      foreach($gene_tmp_arr as $gene_ID => $gene_Name){
        $hitsValue['GeneID'] = $gene_ID;
        $hitsValue['GeneName'] = $gene_Name;      
        if($remove_pb_same_gene && $hitsValue['GeneID'] == $item_val['GeneID']) continue;
        if($frm_apply_filter){
          if($keep_bait){
            if(in_array($hitsValue['GeneID'], $filted_gene_id_arr) && $hitsValue['GeneID'] != $item_val['GeneID']) continue;
          }else{
            if(in_array($hitsValue['GeneID'], $filted_gene_id_arr) || $hitsValue['GeneID'] == $item_val['GeneID']) continue;
          }
          $expFilterArr = array();
          get_exp_filter_arr($expFilterArr,$hitsValue);
          if($expFilterArr){
            $filterFlag = 0;
            if($keep_bait){
              foreach($typeExpArr as $Value){
              	$frmName = 'frm_' . $Value['Alias'];
             		if($$frmName && in_array($Value['Alias'] ,$expFilterArr) && $hitsValue['GeneID'] != $item_val['GeneID']){
                  $filterFlag = 1;
                  break;
                }
             	}
            }else{
              foreach($typeExpArr as $Value){
              	$frmName = 'frm_' . $Value['Alias'];
             		if($$frmName && in_array($Value['Alias'] ,$expFilterArr)){
                  $filterFlag = 1;
                  break;
                }
             	}
            }  
            if($filterFlag) continue;
          }  
        }  
        $tmpStr = '';
        if(array_key_exists($hitsValue[$ProteinAcc], $protein_property_arr)){
          $tmpArr = explode("@@",$protein_property_arr[$hitsValue[$ProteinAcc]]);
          if($frm_apply_filter){
            if($tmpArr[2]){
              $filterFlag = 0;
              $bioFilterArr = explode(",",$tmpArr[2]);
              if($keep_bait){
                foreach($typeBioArr as $Value){
                	$frmName = 'frm_' . $Value['Alias'];
               		if($$frmName && in_array($Value['Alias'] ,$bioFilterArr) && $hitsValue['GeneID'] != $item_val['GeneID']){
                    $filterFlag = 1;
                    break;
                  }
               	}
              }else{
                foreach($typeBioArr as $Value){
                	$frmName = 'frm_' . $Value['Alias'];
               		if($$frmName && in_array($Value['Alias'] ,$bioFilterArr)){
                    $filterFlag = 1;
                    break;
                  }
               	}
              }  
              if($filterFlag) continue;
            }  
          }
          $hitsValue['Filters'] = $tmpArr[2];
        }else{
          $hitsValue['Filters'] = '';
        }
        
        if(strstr($SearchEngine, 'TPP_')){
          $Pep_num = $hitsValue['TOTAL_NUMBER_PEPTIDES'];
          $Pep_num_uniqe = $hitsValue['UNIQUE_NUMBER_PEPTIDES'];
        }elseif(strstr($SearchEngine, 'GeneLevel_')){ 
          $Pep_num = $hitsValue['SpectralCount'];
          $Pep_num_uniqe = $hitsValue['Unique'];
        }else{
          $Pep_num = $hitsValue['Pep_num'];
          $Pep_num_uniqe = $hitsValue['Pep_num_uniqe'];
        }
        if(!$Pep_num) $Pep_num = 0;
        if(!$Pep_num_uniqe) $Pep_num_uniqe = 0;
          
        $hit_GeneID_lable = '';
        if($include_geneID) $hit_GeneID_lable = ($hitsValue['GeneID'])?"(".$hitsValue['GeneID'].")":"";            
        $inter_line = $bait_property.$hitsValue['GeneID'];            
        $prey_line =  $hitsValue[$ProteinAcc].$fileDelimit.'1000'.$fileDelimit.$hitsValue['GeneName'].$hit_GeneID_lable;
 
        $uniqe_prey_index = $hitsValue[$ProteinAcc];
        if(!array_key_exists($uniqe_prey_index, $uniqe_prey_arr)){
          $uniqe_prey_arr[$uniqe_prey_index] = $prey_line;
        }
        
        if(!array_key_exists($inter_line, $uniqe_inter_arr)){
          $uniqe_inter_arr[$inter_line] = $Pep_num;
        }else{        
          if($frm_is_collapse == 'no'){
            if(!$uniqe_inter_arr[$inter_line] || $Pep_num > $uniqe_inter_arr[$inter_line]){
              $uniqe_inter_arr[$inter_line] = $Pep_num;//----------------------------------
            }
          }else{
            $uniqe_inter_arr[$inter_line] += $Pep_num;
               
            if($frm_is_collapse == 'average' || $frm_is_collapse == 'e_average'){
              if(!array_key_exists($inter_line, $Pep_num_times_counter_arr)){
                $Pep_num_times_counter_arr[$inter_line] = 2;
              }else{
                $Pep_num_times_counter_arr[$inter_line]++;
              }
            }
          }
        }
        
        if(!array_key_exists($inter_line, $uniqe_inter_upep_arr)){
          $uniqe_inter_upep_arr[$inter_line] = $Pep_num_uniqe;
        }else{        
          if($frm_is_collapse == 'no'){
            if(!$uniqe_inter_upep_arr[$inter_line] || $Pep_num_uniqe > $uniqe_inter_upep_arr[$inter_line]){
              $uniqe_inter_upep_arr[$inter_line] = $Pep_num_uniqe;//----------------------------------
            }
          }else{
            $uniqe_inter_upep_arr[$inter_line] += $Pep_num_uniqe;
            if($frm_is_collapse == 'average' || $frm_is_collapse == 'e_average'){
              if(!array_key_exists($inter_line, $Pep_uni_num_times_counter_arr)){
                $Pep_uni_num_times_counter_arr[$inter_line] = 2;
              }else{
                $Pep_uni_num_times_counter_arr[$inter_line]++;
              }
            }
          }
          continue;
        } 
            
        $line_log_sub = '';
        if(in_array($hitsValue[$ProteinAcc] ,$no_gene_id_arr)){
          $line_log_sub = "No gene name ";
        }
        if(0 && $is_count_seq_len && (!isset($protein_seq_len_arr[$hitsValue[$ProteinAcc]]) || !$protein_seq_len_arr[$hitsValue[$ProteinAcc]])){
          if($line_log_sub) $line_log_sub .= "and ";
          $line_log_sub .= "average seq length $aver_len";
        }
        if($line_log_sub && !array_key_exists($hitsValue[$ProteinAcc], $Notice_arr)){
          $tmp_pro_arr['Prey name'] = $hitsValue['GeneName'];        
          $tmp_pro_arr['ProteinID'] = $hitsValue[$ProteinAcc];
          if($is_count_seq_len){
            $tmp_pro_arr['S len'] = (isset($protein_seq_len_arr[$hitsValue[$ProteinAcc]]) && $protein_seq_len_arr[$hitsValue[$ProteinAcc]])?$protein_seq_len_arr[$hitsValue[$ProteinAcc]]:$aver_len;
          }else{
            $tmp_pro_arr['S len'] = 1;
          }
          $tmp_pro_arr['Notice'] = $line_log_sub;
          $Notice_arr[$hitsValue[$ProteinAcc]] = $tmp_pro_arr;
        }
      }
    }
  }
    
  foreach($uniqe_inter_arr as $uniqe_inter_key => $uniqe_inter_val){
    if(($frm_is_collapse == 'average' || $frm_is_collapse == 'e_average') && array_key_exists($uniqe_inter_key, $Pep_num_times_counter_arr)){
      $tmp_pep_num = ceil($uniqe_inter_val / $Pep_num_times_counter_arr[$uniqe_inter_key]);
    }else{
      $tmp_pep_num = $uniqe_inter_val;
    }
    
    if(($frm_is_collapse == 'average' || $frm_is_collapse == 'e_average') && array_key_exists($uniqe_inter_key, $Pep_uni_num_times_counter_arr)){
      $tmp_pep_num_uni = ceil($uniqe_inter_upep_arr[$uniqe_inter_key] / $Pep_uni_num_times_counter_arr[$uniqe_inter_key]);
    }else{
      $tmp_pep_num_uni = $uniqe_inter_upep_arr[$uniqe_inter_key];
    }
    
    $inter_line = $uniqe_inter_key.$fileDelimit.$tmp_pep_num.$fileDelimit.$tmp_pep_num_uni."\r\n";
    $inter_line_2 = $uniqe_inter_key.$fileDelimit.$tmp_pep_num."\r\n";
    fwrite($inter_handle,$inter_line);
    fwrite($inter_handle_2,$inter_line_2);
  }
  
  foreach($uniqe_prey_arr as $uniqe_prey_key => $uniqe_prey_val){
    $prey_line = $uniqe_prey_val."\r\n";
    fwrite($prey_handle,$prey_line);
  }
  fclose($prey_handle);
  
  $to_file = "iRefIndexID\tpreyProteinA preyProteinB\tpreyGeneA preyGeneB\tMethod|Source\n";
  fwrite($iRefIndex_inter_handle, $to_file);
          
  $iRefIndex_AB_keys = array();
  foreach($uniqe_prey_geneID_arr as $tmp_geneID=>$tmp_Acc){
    
    $SQL = "select ID, geneIDA, geneIDB, method, sourcedb from iRefIndex 
            where geneIDA='$tmp_geneID' and geneIDB is not null";
    //echo "$SQL\n";
    $results = mysqli_query($proteinDB->link, $SQL);
    while($row = mysqli_fetch_row($results)){
      if($row[4] != 'MI:0463(biogrid)'
        and $row[4] != 'MI:0465(dip)'
        and $row[4] != 'MI:0469(intact)'
        and $row[4] != 'MI:0471(mint)'
      ) continue;
      $key1 = $row[1]." ". $row[2];
      $key2 = $row[2]." ". $row[1];
      if(!array_key_exists($key1, $iRefIndex_AB_keys) and !array_key_exists($key2, $iRefIndex_AB_keys)){
        $iRefIndex_AB_keys[$key1] = 1;
        if(isset($uniqe_prey_geneID_arr[$row[2]])){
          $to_file = $row[0]."\t". $uniqe_prey_geneID_arr[$row[1]]." ". $uniqe_prey_geneID_arr[$row[2]]."\t" . 
          $row[1]." ". $row[2]."\t".$row[3]."|".$row[4]."\n";
          fwrite($iRefIndex_inter_handle, $to_file);
        }
      }
    }
    $SQL = "select ID, geneIDA, geneIDB, method, sourcedb from iRefIndex 
            where geneIDB='$tmp_geneID' and geneIDA is not null";
    //echo "$SQL\n";
    $results = mysqli_query($proteinDB->link, $SQL);
    while($row = mysqli_fetch_row($results)){
      if($row[4] != 'MI:0463(biogrid)'
        and $row[4] != 'MI:0465(dip)'
        and $row[4] != 'MI:0469(intact)'
        and $row[4] != 'MI:0471(mint)'
      ) continue; 
      $key1 = $row[1]." ". $row[2];
      $key2 = $row[2]." ". $row[1];
      if(!array_key_exists($key1, $iRefIndex_AB_keys) and !array_key_exists($key2, $iRefIndex_AB_keys)){
        $iRefIndex_AB_keys[$key1] = 1;
        if(isset($uniqe_prey_geneID_arr[$row[1]])){
          $to_file = $row[0]."\t". $uniqe_prey_geneID_arr[$row[1]]." ". $uniqe_prey_geneID_arr[$row[2]]."\t" . 
          $row[1]." ". $row[2]."\t".$row[3]."|".$row[4]."\n";
          fwrite($iRefIndex_inter_handle, $to_file);
        }
      }
    }
  }
  fclose($iRefIndex_inter_handle);
  
  if($Notice_arr){
    fwrite($log_handle, $star_line);
    fwrite($log_handle, $Warning_line);
    fwrite($log_handle, $star_line."\r\n");
    $line_log = "Prey name\tProtein ID\tSquence len\tNotes\r\n";
    fwrite($log_handle, $line_log);
    foreach($Notice_arr as $Notice_val){
      $line_log = $Notice_val['Prey name']."\t".
                  $Notice_val['ProteinID']."\t".
                  $Notice_val['S len']."\t".
                  $Notice_val['Notice']."\r\n";
      fwrite($log_handle, $line_log);
    }
  }
  fclose($log_handle);
  //echo "create zip file.\n<br>";
  $myshellcmd = "cd $outDir; zip $zip_file_name *;";
  $result = @exec($myshellcmd);
  if(!$result){
    $err_msg = "Can not create a zip file now. Please try it later.";
    exit;
  } 
?>