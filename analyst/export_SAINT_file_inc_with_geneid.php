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

  $applyFilters = $frm_apply_filter;
  if($SearchEngine == 'GPM'){
    $Expect = 'Expect2';
  }else{
    $Expect = 'Expect';
  }
  if($hitType == 'TPP'){
    $ht = "T.";
  }else{
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
  if(!$prey_handle = fopen($prey_filename, 'w')){
    echo "Cannot open file ($prey_filename)";
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
  
  //= export filter info =======
 
  $filter_export_arr = array();
  $filter_export_arr_2 = array();
  $filter_export_arr_3 = array();
  get_filter_array_for_export($request_arr);
  
  fwrite($log_handle, $star_line);
  write_filter_info_map($log_handle, "SAINT");
    
  $log_line = "\r\nFile: Bait.dat\r\n$dech_line";
  fwrite($log_handle, $log_line);
  $bait_title_str = "Column 1:\tSample ID (IP ID)\r\nColumn 2:\tSaint Bait Name (Bait GeneName). Could be changed by user\r\nColumn 3:\tControl/Test\r\n";
  fwrite($log_handle, $bait_title_str);
  $log_line = "\r\nFile: Inter.dat\r\n$dech_line";
  fwrite($log_handle, $log_line);
  $inter_title_str = "Column 1:\tSample ID (IP ID)\r\nColumn 2:\tSaint Bait Name (Bait GeneName). Could be changed by user\r\nColumn 3:\tPrey Gene Name\r\nColumn 4:\tPrey Peptide number\r\n";
  fwrite($log_handle, $inter_title_str);
  $log_line = "\r\nFile: Prey.dat\r\n$dech_line";
  fwrite($log_handle, $log_line);
  $prey_title_str = "Column 1:\tPrey Gene Name\r\nColumn 2:\tPrey Protein Sequence length\r\n\r\n";
  fwrite($log_handle, $prey_title_str);  
  
  $proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);
  
  //------ get filtered gene id array from frequency and Ns -------------------------------
  
  if($hitType == 'TPP'){
    $frequencyFileName = 'tpp_frequency.csv';
    $hitTable = 'TppProtein';
    $ProteinAcc = 'ProteinAcc';
  }elseif($hitType == 'normal'){
    $frequencyFileName = 'frequency.csv';
    $hitTable = 'Hits';
    $ProteinAcc = 'HitGI';
  }else{
    $frequencyFileName = '';
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
  
  //-------------------------------------------------------------------------------------------
  
  $sample_id_name_arr = array();
  $sample_id_str = '';  
  
  foreach($item_arr as $key => $val){
    if($sample_id_str) $sample_id_str .= ',';
    $sample_id_str .= $val;
    $tmp_sample_id_arr = explode(",", $val);
    foreach($tmp_sample_id_arr as $val2){
      $sample_id_name_arr[$val2] = $key;
    }
  }
  
  $protein_property_arr = array();
  $protein_seq_len_arr = array();
  $no_gene_id_arr = array();
  
  $uniqe_inter_arr = array(); 
  $uniqe_prey_arr = array();
  
  $eva_len = get_all_SAINT_protein_info($sample_id_str);
  
  $item_arr = array();
  foreach($sample_id_name_arr as $id_key => $item_id_value){
    $SQL = "SELECT BA.ID,B.GeneID,B.GeneName,B.GelFree 
            FROM Band BA LEFT JOIN Bait B ON(BA.BaitID=B.ID) WHERE BA.ID='$id_key'";
    $item_arr_single = $HITSDB->fetch($SQL);
    array_push($item_arr, $item_arr_single);
  }
  foreach($item_arr as $item_val){
    //--get genename filter from protein db--
    $sample_ID = $item_val['ID'];
    $baitGeneID = $item_val['GeneID'];

    $saint_bait_name = $sample_id_name_arr[$sample_ID];
    if(in_array($sample_ID, $control_arr)){
      $control_test = 'C';
    }else{
      $control_test = 'T';
    }
    $line = "$sample_ID$fileDelimit$baitGeneID|$saint_bait_name$fileDelimit$control_test\r\n";

    fwrite($bait_handle, $line); 
    
    $item_hits_order_by = 'ID';
    $subWhere = ''; 
    if($frm_apply_filter){
      $subWhere .= subWhere($is_filter_bait,$baitGeneID);
    }
    get_hits_result($sample_ID, $hits_result, $SearchEngine, $hitType);
    while($hitsValue = mysqli_fetch_assoc($hits_result)){
      $hitsValue[$ProteinAcc] = parse_protein_Acc($hitsValue[$ProteinAcc]);
      if($frm_start_with && preg_match("/$frm_start_with/", $hitsValue[$ProteinAcc])) continue;
      if($frm_end_with && preg_match("/$frm_end_with/", $hitsValue[$ProteinAcc])) continue;
      if($frm_apply_filter){        
        if($keep_bait){
          if(in_array($hitsValue['GeneID'], $filted_gene_id_arr) && $hitsValue['GeneID'] != $item_val['GeneID']) continue;
        }else{
          if(in_array($hitsValue['GeneID'], $filted_gene_id_arr)) continue;
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
        $hitsValue['GeneName'] = $tmpArr[1];
        $hitsValue['Filters'] = $tmpArr[2];
      }else{       
        $hitsValue['GeneName'] = '';
        $hitsValue['Filters'] = '';
      }
      if($hitType == 'normal'){
        $Pep_num = $hitsValue['Pep_num'];
      }else{
        $Pep_num = $hitsValue['TOTAL_NUMBER_PEPTIDES'];
      }

      $inter_line = "$sample_ID$fileDelimit$baitGeneID|$saint_bait_name$fileDelimit".$hitsValue['GeneID']."|".$hitsValue['GeneName'];
 
      if($is_count_seq_len){
        if(!$protein_seq_len_arr[$hitsValue[$ProteinAcc]]){
          $prey_line = $hitsValue['GeneID']."|".$hitsValue['GeneName'].$fileDelimit.$eva_len;
        }else{  
          $prey_line = $hitsValue['GeneID']."|".$hitsValue['GeneName'].$fileDelimit.$protein_seq_len_arr[$hitsValue[$ProteinAcc]];
        }  
      }else{
        $prey_line = $hitsValue['GeneID']."|".$hitsValue['GeneName'].$fileDelimit.'1';
      }  
      
      if(!array_key_exists($hitsValue['GeneName'], $uniqe_prey_arr)){
        $uniqe_prey_arr[$hitsValue['GeneName']] = $prey_line;
      }  
      
      if(!array_key_exists($inter_line, $uniqe_inter_arr)){
        $uniqe_inter_arr[$inter_line] = $Pep_num;
      }else{
        if(!$uniqe_inter_arr[$inter_line] || $Pep_num > $uniqe_inter_arr[$inter_line]){
          $uniqe_inter_arr[$inter_line] = $Pep_num;
          continue;
        }
      }
      
      $line_log_sub = '';
      if(in_array($hitsValue[$ProteinAcc] ,$no_gene_id_arr)){
        $line_log_sub = "No gene name ";
      }
      
      if($is_count_seq_len && $is_count_seq_len && !$protein_seq_len_arr[$hitsValue[$ProteinAcc]]){
        if($line_log_sub) $line_log_sub .= "and ";
        $line_log_sub .= "average seq length $eva_len;";
      }
      
      if($line_log_sub && !array_key_exists($hitsValue[$ProteinAcc], $Notice_arr)){        
        $tmp_pro_arr['Prey name'] = $hitsValue['GeneName'];
        if($is_count_seq_len){
          $tmp_pro_arr['S len'] = ($protein_seq_len_arr[$hitsValue[$ProteinAcc]])?$protein_seq_len_arr[$hitsValue[$ProteinAcc]]:$eva_len;
        }else{
          $tmp_pro_arr['S len'] = 1;
        }
        $tmp_pro_arr['Notice'] = $line_log_sub;
        $Notice_arr[$hitsValue[$ProteinAcc]] = $tmp_pro_arr;
      }
    }
  }
  
  foreach($uniqe_inter_arr as $uniqe_inter_key => $uniqe_inter_val){
    $inter_line = $uniqe_inter_key.$fileDelimit.$uniqe_inter_val."\r\n";
    fwrite($inter_handle,$inter_line);
  }

  foreach($uniqe_prey_arr as $uniqe_prey_key => $uniqe_prey_val){
    $prey_line = $uniqe_prey_val."\r\n";
    fwrite($prey_handle,$prey_line);
  } 
   
  if($Notice_arr){
    fwrite($log_handle, $star_line);
    fwrite($log_handle, $Warning_line);
    fwrite($log_handle, $star_line."\r\n");
    $line_log = "Prey name\tSquence len\tNotes\r\n";
    fwrite($log_handle, $line_log);
    foreach($Notice_arr as $Notice_val){
      $line_log = $Notice_val['Prey name']."\t".
                  $Notice_val['S len']."\t".
                  $Notice_val['Notice']."\r\n";
      fwrite($log_handle, $line_log);
    }
  }
  $myshellcmd = "cd $outDir; zip $zip_file_name *;";
  $result = @exec($myshellcmd);
  if(!$result){
    $err_msg = "Can not create a zip file now. Please try it later.";
    exit;
  }
?>