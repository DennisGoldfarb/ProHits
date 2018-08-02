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
/*
it is included in export_hits.php export_hits_public.php
*/

  $task_tpptask_ids_arr = array();

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
  $item_id_arr = array();
  $tmpArr1 = explode(';',$frm_selected_list_str);
  foreach($tmpArr1 as $tmpVal1){
    $tmpArr2 = explode(':',$tmpVal1);
    $tmpArr3 = explode(',', $tmpArr2[1]);
    foreach($tmpArr3 as $tmpVal3){
      array_push($item_id_arr, $tmpVal3);
    }
  }
  
  $infile = $filename = $outDir_map.$_SESSION['USER']->ID."_".strtolower($type)."_map.csv";
  $Search_parameters_file = $outDir_map.$_SESSION['USER']->ID."_parameters.csv";
  
  $mapfileDelimit = ",";
  
  //echo "\$filename=$filename";
  //exit;
  
  if(!$handle = fopen($filename, 'w')){
    echo "Cannot open file ($filename)";
    exit;
  }  
  $filter_export_arr = array();
  $filter_export_arr_2 = array();
  $filter_export_arr_3 = array();
  get_filter_array_for_export($request_arr);
  write_filter_info_map($handle);
  $proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);
  $fileDelimit = ",";
  
  $filedNameStr = implode($fileDelimit, $level3_lable_array);
  $filedNameStr = "level3::".$filedNameStr."\r\n";
  fwrite($handle, $filedNameStr);
  if($hitType == 'TPP'){
    $frequencyFileName = 'tpp_frequency.csv';
    $hitTable = 'TppProtein';
    $GeneName_for_hitQ = '';
  }elseif($hitType == 'normal'){
    $frequencyFileName = $SearchEngine.'_frequency.csv';
    $hitTable = 'Hits';
    $GeneName_for_hitQ = '';
  }elseif($hitType == 'geneLevel'){
    $frequencyFileName = 'geneLevel_frequency.csv';
    $hitTable = 'Hits_GeneLevel';
    $GeneName_for_hitQ = " ,`GeneName` ";  
  }else{
    $frequencyFileName = '';
  }
  $frequencyArr = array();
  get_frequency_arr($frequencyArr,$frequencyFileName);
  $filted_gene_id_arr = array();
  if($frm_apply_filter && $frm_filter_Fequency && $frm_filter_Fequency_value){
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
  $item_ids_str =  implode(",", $item_id_arr);
  $item_arr = array();
  foreach($item_id_arr as $item_id_value){
    if($currentType == 'Bait'){
      $SQL = "SELECT `ID`,`GeneID`,`GeneName`,`GelFree`,`Vector` 
              FROM `Bait` WHERE ID='$item_id_value'";
    }elseif($currentType == 'Exp'){          
      $SQL = "SELECT E.ID,B.GeneID,B.GeneName,B.GelFree,B.Vector 
              FROM Experiment E LEFT JOIN Bait B ON(E.BaitID=B.ID) WHERE E.ID='$item_id_value'";        
    }elseif($currentType == 'Band'){
      $SQL = "SELECT BA.ID,B.GeneID,B.GeneName,B.GelFree,B.Vector 
              FROM Band BA LEFT JOIN Bait B ON(BA.BaitID=B.ID) WHERE BA.ID='$item_id_value'";
    }          
    $item_arr_single = $HITSDB->fetch($SQL);
    array_push($item_arr, $item_arr_single);
  }
  $Vector_str = '';
  foreach($item_arr as $item_val){
    if(!trim($item_val['Vector'])) continue;
    if($Vector_str) $Vector_str .= ",";
    $Vector_str .= $item_val['Vector'];
  } 

//echo "\$Vector_str=$Vector_str@@@@@@@@@@@@@<br>";exit;
//######### test as normal function not ajx function remove this section ####################
  //$Vector_str = "v1169,v2,v84936";
  //if($USER->Type == 'Admin' && $Vector_str){
    //get_Info_from_OpenFreezer($Vector_str,$Username='Chrisdgo',$Password='donghyan');
  //}
//########################################################################################### 
//exit;  
  $H_T_item_field = $currentType.'ID';
  foreach($item_arr as $item_val){
    //--get genename filter from protein db
    $item_ID = $item_val['ID'];
    $baitGeneID = $item_val['GeneID'];
    if($currentType == 'Exp'){
      $SQL = "SELECT ID 
              FROM Band 
              WHERE ExpID='$item_ID'";
      $Band_id_arr = $HITSDB->fetchAll($SQL);
      $Band_id_str = '';
      foreach($Band_id_arr as $Band_id_val){
        if($Band_id_str) $Band_id_str .= ',';
        $Band_id_str .= $Band_id_val['ID'];
      }
      if($Band_id_str){
        $SQL = "SELECT `GeneID` $GeneName_for_hitQ 
              FROM $hitTable 
              WHERE BandID IN ($Band_id_str)
              GROUP BY `GeneID`";
      }
    }else{
      $SQL = "SELECT `GeneID` $GeneName_for_hitQ
              FROM $hitTable 
              WHERE $H_T_item_field='$item_ID'
              GROUP BY `GeneID`";
    }       
    $Gene_id_arr = $HITSDB->fetchAll($SQL);
    if($hitType == 'geneLevel'){
      foreach($Gene_id_arr as $Gene_id_val){
        $tmp_GnenID_arr = explode(',',$Gene_id_val['GeneID']);
        $tmp_GnenName_arr = explode(',',$Gene_id_val['GeneName']);
        for($a=0; $a<count($tmp_GnenID_arr); $a++){
          $protein_property_str = trim($tmp_GnenName_arr[$a])."@@";
          $protein_property_arr[trim($tmp_GnenID_arr[$a])] = $protein_property_str;
        }
      }
    }else{
      $EntrezGene_arr = array();
      $ENSG_arr = array(); 
      foreach($Gene_id_arr as $value){
        if(!$value['GeneID']) continue;
        if(is_numeric($value['GeneID'])){
          array_push($EntrezGene_arr, $value['GeneID']);
        }else{
          array_push($ENSG_arr, $value['GeneID']);
        } 
      }
      $protein_property_arr = array();
      if(count($EntrezGene_arr)){
        $tmpStr = implode("','", $EntrezGene_arr);
        $EntrezGene_str = "'".$tmpStr."'";
        $SQL = "SELECT `EntrezGeneID`,
                       `GeneName`,
                       `BioFilter` 
                       FROM `Protein_Class` 
                       WHERE `EntrezGeneID` IN ($EntrezGene_str)";
        $EntrezGene_resul_arr = $proteinDB->fetchAll($SQL);
        foreach($EntrezGene_resul_arr as $EntrezGene_resul_val){
          $protein_property_arr[$EntrezGene_resul_val['EntrezGeneID']] = $EntrezGene_resul_val['GeneName']."@@".$EntrezGene_resul_val['BioFilter'];
        }
      }
      if(count($ENSG_arr)){
        $tmpStr = implode("','", $ENSG_arr);
        $ENSG_str = "'".$tmpStr."'";
        $SQL = "SELECT `ENSG`,
                       `GeneName` 
                       FROM `Protein_ClassENS` 
                       WHERE `ENSG` IN ($ENSG_str)";
        $EntrezGene_resul_arr = $proteinDB->fetchAll($SQL);
        foreach($EntrezGene_resul_arr as $EntrezGene_resul_val){
          $protein_property_arr[$EntrezGene_resul_val['ENSG']] = $EntrezGene_resul_val['GeneName']."@@";
        }
      }
    }   
    //--level_1-------------------------------------------------------
    $isGelFree = $item_val['GelFree'];    
    $level1_header = 'Bait';
    $level_1_arr = array();
    $level_2_arr = array();
    $BaitArr = array();
    $exp_arr = array();
    get_item_general_info($type,$item_ID,$isGelFree);
    $fileLevel_1_str = '';    

    foreach($level1_lable_array as $tmpKey => $tmpLable){
      if($fileLevel_1_str) $fileLevel_1_str .= $fileDelimit;
      if($tmpKey == 'GelFree'){
        if($level_1_arr[$tmpKey] == '1'){
          $level_1_arr[$tmpKey] = 'Y';
        }else{
          $level_1_arr[$tmpKey] = 'N';
        }
      }
      $singefileLevel_1_str = str_replace(",", ";", $level_1_arr[$tmpKey]);
      $singefileLevel_1_str = str_replace("\n", "", $singefileLevel_1_str);
      $fileLevel_1_str .= $tmpLable.'==='.$singefileLevel_1_str;
    }
    $fileLevel_1_str = $level1_header.'::'.$fileLevel_1_str."\r\n";
    fwrite($handle, $fileLevel_1_str);
    
    //--level_2------------------------------------------------------------
    foreach($level_2_arr as $arr2_value){
      //--level_3----------------------------------------------------------
      $item_hits_order_by = 'ID';
      $subWhere = $start_point = $frm_selected_band = '';
      if($frm_apply_filter){
        $subWhere .= subWhere($is_filter_bait,$baitGeneID);
      }   
      
      get_hits_result($arr2_value['ID'], $hits_result, $SearchEngine, $hitType);


      if(!$num_rows = mysqli_num_rows($hits_result)) continue;
      foreach($level2_lable_array as $tmpKey => $tmpLable){

        if($tmpKey == $level1_header) continue;
        $fileLevel_2_str = '';
        foreach($tmpLable as $lableKey => $lableVel){
          if($fileLevel_2_str) $fileLevel_2_str .= $fileDelimit;
          if(array_key_exists($lableKey, $arr2_value)){
            $single_str = str_replace(",", ";", $arr2_value[$lableKey]);
            $single_str = str_replace("\n", "", $single_str);
            $fileLevel_2_str .= $lableVel.'==='.$single_str;
          }else{
            $fileLevel_2_str .= $lableVel.'===';
          }  
        }
        $fileLevel_2_str = $tmpKey.'::'.$fileLevel_2_str."\r\n";
        fwrite($handle, $fileLevel_2_str);  
      }
     
      $gi_acc_arr = array();
      if($hitType == 'TPP' || $hitType == 'normal'){
        $gi_acc_arr = get_gi_acc_arr($hits_result,$hitType,$proteinDB);
      }    
    
      mysqli_data_seek($hits_result, 0);
      while($hitsValue_tmp = mysqli_fetch_assoc($hits_result)){
        $tmp_d_geneID_arr = explode(',',$hitsValue_tmp['GeneID']);
        if($hitType == 'geneLevel'){
          $tmp_d_geneName_arr = explode(',',$hitsValue_tmp['GeneName']);
        }  
        for($b=0; $b<count($tmp_d_geneID_arr); $b++){
          $hitsValue = $hitsValue_tmp;
      
          $hitsValue['GeneID'] = $tmp_d_geneID_arr[$b];
          if($hitType == 'geneLevel'){
            $hitsValue['GeneName'] = $tmp_d_geneName_arr[$b];
          }
          if(isset($SearchEngine_lable_arr[$SearchEngine])){
            $hitsValue['SearchEngine'] = $SearchEngine_lable_arr[$SearchEngine];
          }
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
          if(array_key_exists($hitsValue['GeneID'], $protein_property_arr)){
            $tmpArr = explode("@@",$protein_property_arr[$hitsValue['GeneID']]);
            if($frm_apply_filter){
              if($tmpArr[1]){
                $filterFlag = 0;
                $bioFilterArr = explode(",",$tmpArr[1]);
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
            $hitsValue['GeneName'] = $tmpArr[0];
            $hitsValue['Filters'] = $tmpArr[1];
          }else{
            $hitsValue['GeneName'] = '';
            $hitsValue['Filters'] = '';
          }
          if(array_key_exists($hitsValue['GeneID'], $frequencyArr)){
            $hitsValue['Frequency'] = $frequencyArr[$hitsValue['GeneID']]."%";
          }else{
            $hitsValue['Frequency'] = '';
          }
       
          if($hitType == 'TPP' || $hitType == 'normal'){
            if($hitType == 'TPP'){
              $gi = 'ProteinAcc';
              $redundantGI = 'INDISTINGUISHABLE_PROTEIN';              
            }elseif($hitType == 'normal'){
              $gi = 'HitGI';
              $redundantGI = 'RedundantGI';
            }
            if(array_key_exists($hitsValue[$gi], $gi_acc_arr)){
              $tmp_gi = $hitsValue[$gi];
              $hitsValue[$gi] = $gi_acc_arr[$tmp_gi]['Acc_V'];
              $hitsValue['Acc'] = $gi_acc_arr[$tmp_gi]['Acc'];
            }else{
              $hitsValue['Acc'] = $hitsValue[$gi];
            }
            if(trim($hitsValue[$redundantGI])){
              $redundantGI_str = $hitsValue[$redundantGI];
              $redundantGI_str = str_ireplace("gi|", "", $redundantGI_str);
              //$redundantGI_str = str_ireplace("|", "", $redundantGI_str);   
              $tmp_re_arr = explode(";", $redundantGI_str);
              $redundantAC_str = '';
              foreach($tmp_re_arr as $tmp_re_val){
                $tmp_gi_key = trim($tmp_re_val);
                if(!$tmp_gi_key) continue;
                if($redundantAC_str) $redundantAC_str .= "; ";
                if(array_key_exists($tmp_gi_key, $gi_acc_arr)){
                  $redundantAC_str .= $gi_acc_arr[$tmp_gi_key]['Acc_V'];
                }else{
                  $redundantAC_str .= $tmp_gi_key;
                }
              }
              $hitsValue[$redundantGI] = $redundantAC_str;
            }
          }
             
          $fileLevel_3_str = '';
          foreach($level3_lable_array as $tmpKey => $tmpLable){
            if(!array_key_exists($tmpKey, $hitsValue)) continue;
            $hitsValue[$tmpKey] = str_replace(",", ";", $hitsValue[$tmpKey]);
            $hitsValue[$tmpKey] = str_replace("\n", "", $hitsValue[$tmpKey]);
            $hitsValue[$tmpKey] = str_replace("\r", "", $hitsValue[$tmpKey]);
            $fileLevel_3_str .= $hitsValue[$tmpKey].$fileDelimit;
          }
          fwrite($handle, $fileLevel_3_str."\r\n");
        }
      }
    }
    
/*echo "<pre>";    
print_r($level_2_arr);    
echo "</pre>";      
exit;*/
  }
  fclose($handle);
  if(!$para_handle = fopen($Search_parameters_file, 'w')){
    echo "Cannot open file ($Search_parameters_file)";
    exit;
  }
  fwrite($para_handle, "Raw file ID,Task ID,Parameters,Convert Parameters,Search Engines,TppTask ID,Tpp Parameters\r\n");  
  foreach($task_tpptask_ids_arr as $task_tpptask_ids_sub_arr){
    $para_line = '';
    foreach($task_tpptask_ids_sub_arr as $tmp_val){
      $tmp_str = str_replace(",", ":", $tmp_val);
      $tmp_str = str_replace("\n", "|", $tmp_str);
      if($para_line) $para_line .= ',';
      $para_line .= $tmp_str;
    }
    fwrite($para_handle, $para_line."\r\n");
  }
  fclose($para_handle);
?>