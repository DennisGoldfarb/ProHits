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

function create_groupArr_otherStrs(&$groupArr,&$frm_selected_item_str,&$frm_selected_group_str,&$no_groupped_str){
  global $frm_selected_list_str,$ungroupedItemColor,$contrlColor,$typeLable;
  $tmpGroupArr = explode(';', $frm_selected_list_str);
  $groupInexCounter = 0;
  foreach($tmpGroupArr as $tmpGroupValue){
    $tmpGroupArr2 = explode(':',$tmpGroupValue);
    if($tmpGroupArr2[0] != $ungroupedItemColor){
      if($tmpGroupArr2[0] == $contrlColor){
        $groupArr[$tmpGroupArr2[0]]['lable'] = 'Control Group';
      }else{
        $groupArr[$tmpGroupArr2[0]]['lable'] = 'Merged Group '.++$groupInexCounter;
      }
      $groupArr[$tmpGroupArr2[0]]['inStr'] = $tmpGroupArr2[1];
      if($frm_selected_item_str) $frm_selected_item_str .= ',';
      $frm_selected_item_str .= $tmpGroupArr2[1];
      if($frm_selected_group_str) $frm_selected_group_str .= ',';
      $frm_selected_group_str .= $tmpGroupArr2[0];
      $simpleGroupItemInfoStr = '';
      $group_item_info_arr = explode("##",get_group_item_info($tmpGroupArr2[1],$simpleGroupItemInfoStr));
      $itemInfo = '<b>'.$groupArr[$tmpGroupArr2[0]]['lable'].' ('.$typeLable.'):</b><br>'.$group_item_info_arr[0];
      $groupArr[$tmpGroupArr2[0]]['itemInfo'] = $itemInfo;
      $groupArr[$tmpGroupArr2[0]]['simpleInfo'] = $simpleGroupItemInfoStr;
      $groupArr[$tmpGroupArr2[0]]['geneID'] = $group_item_info_arr[1];
    }else{
      if($frm_selected_item_str) $frm_selected_item_str .= ',';
      $frm_selected_item_str .= $tmpGroupArr2[1];
      if($frm_selected_group_str) $frm_selected_group_str .= ',';
      $frm_selected_group_str .= $tmpGroupArr2[1];
      $no_groupped_str = $tmpGroupArr2[1];
    }
  } 
}
function get_group_item_info($inStr,&$simpleGroupItemInfoStr){
  global $HITSDB, $itemNoName,$currentType;
  if(!$inStr) return '';
	$itemsArr = array();
	get_elements_property($itemsArr,$inStr,'multiple');		
  $groupItemInfoStr = '';
  $geneID_str = "";
  if($currentType == 'Bait'){
    $groupItemInfoStr .= '<font color=green><b>BaitID&nbsp;&nbsp;GeneName</b></font>';
    foreach($itemsArr as $itemsValue){
  	  if($itemsValue['GeneName'] && $itemsValue['GeneName'] != "-"){
  	    $itemLable = $itemsValue['GeneName'];
  	  }elseif($itemsValue['LocusTag'] && $itemsValue['LocusTag'] != "-"){
  	    $itemLable = $itemsValue['LocusTag'];
  	  }else{
  	    $itemNoName++;
  	    $itemLable .= "&nbsp;&nbsp;_noName-".$itemNoName;
  	  }
      if($itemsValue['Tag'] && $itemsValue['Mutation']){
        $itemLable .= "(".$itemsValue['Tag'].";".$itemsValue['Mutation'].")";
      }elseif($itemsValue['Tag']){
        $itemLable .= "(".$itemsValue['Tag'].")";
      }elseif($itemsValue['Mutation']){
        $itemLable .= "(".$itemsValue['Mutation'].")";
      }
      if($groupItemInfoStr) $groupItemInfoStr .= '<br>';
      $groupItemInfoStr .= $itemsValue['ID'].'&nbsp;&nbsp;'.$itemLable;
      if($simpleGroupItemInfoStr) $simpleGroupItemInfoStr .= ',';
      $simpleGroupItemInfoStr .= $itemsValue['ID'].' '.$itemLable;
      if($geneID_str) $geneID_str .= "|";
      $geneID_str .= $itemsValue['GeneID'];
    }
  }elseif($currentType == 'Exp'){  
    $groupItemInfoStr .= '<font color=green><b>ExpID&nbsp;&nbsp;ExpName&nbsp;&nbsp;GeneName</b></font>';
    foreach($itemsArr as $itemsValue){
      $simpleItemLable = $itemLable = $itemsValue['Name'];
  	  if($itemsValue['GeneName'] && $itemsValue['GeneName'] != "-"){
  	    $itemLable .= '&nbsp;&nbsp;'.$itemsValue['GeneName'];
  	  }elseif(isset($itemsValue['LocusTag']) && $itemsValue['LocusTag'] && $itemsValue['LocusTag'] != "-"){
  	    $itemLable .= '&nbsp;&nbsp;'.$itemsValue['LocusTag'];
  	  }else{
  	    $itemNoName++;
  	    $itemLable .= "&nbsp;&nbsp;noName-".$itemNoName;
  	  }
      if($itemsValue['Tag'] && $itemsValue['Mutation']){
        $itemLable .= "(".$itemsValue['Tag'].";".$itemsValue['Mutation'].")";
      }elseif($itemsValue['Tag']){
        $itemLable .= "(".$itemsValue['Tag'].")";
      }elseif($itemsValue['Mutation']){
        $itemLable .= "(".$itemsValue['Mutation'].")";
      }
      if($groupItemInfoStr) $groupItemInfoStr .= '<br>';
      $groupItemInfoStr .= $itemsValue['ID'].'&nbsp;&nbsp;'.$itemLable;
      if($simpleGroupItemInfoStr) $simpleGroupItemInfoStr .= ',';
      $simpleGroupItemInfoStr .= $itemsValue['ID'].' '.$simpleItemLable;
      if($geneID_str) $geneID_str .= "|";
      $geneID_str .= $itemsValue['GeneID'];
    }
  }elseif($currentType == 'Band'){ 
    $hasGel = 0;
    foreach($itemsArr as $itemsValue){
      if($itemsValue['GelID']){
        $hasGel = 1;
        break;
      }  
    }
    if($hasGel){
      $groupItemInfoStr .= '<font color=green><b>SampleID&nbsp;&nbsp;SampleName&nbsp;&nbsp;GeneName&nbsp;&nbsp;GelID&nbsp;&nbsp;LaneNum</b></font>';
    }else{
      $groupItemInfoStr .= '<font color=green><b>SampleID&nbsp;&nbsp;SampleName&nbsp;&nbsp;GeneName</b></font>';
    }
    foreach($itemsArr as $itemsValue){
      $simpleItemLable = $itemLable = $itemsValue['Location'];
  	  if($itemsValue['GeneName'] && $itemsValue['GeneName'] != "-"){
  	    $itemLable .= '&nbsp;&nbsp;'.$itemsValue['GeneName'];
  	  }elseif(isset($itemsValue['LocusTag']) && $itemsValue['LocusTag'] && $itemsValue['LocusTag'] != "-"){
  	    $itemLable .= '&nbsp;&nbsp;'.$itemsValue['LocusTag'];
  	  }else{
  	    $itemNoName++;
  	    $itemLable .= "&nbsp;&nbsp;noName-".$itemNoName;
  	  }
      if($itemsValue['Tag'] && $itemsValue['Mutation']){
        $itemLable .= "(".$itemsValue['Tag'].";".$itemsValue['Mutation'].")";
      }elseif($itemsValue['Tag']){
        $itemLable .= "(".$itemsValue['Tag'].")";
      }elseif($itemsValue['Mutation']){
        $itemLable .= "(".$itemsValue['Mutation'].")";
      }
      if($hasGel){
        $itemLable .= '&nbsp;&nbsp;'.$itemsValue['GelID'].'&nbsp;&nbsp;'.$itemsValue['LaneNum'];
      }
      if($groupItemInfoStr) $groupItemInfoStr .= '<br>';
      $groupItemInfoStr .= $itemsValue['ID'].'&nbsp;&nbsp;'.$itemLable;
      if($simpleGroupItemInfoStr) $simpleGroupItemInfoStr .= ',';
      $simpleGroupItemInfoStr .= $itemsValue['ID'].' '.$simpleItemLable;
      if($geneID_str) $geneID_str .= "|";
      $geneID_str .= $itemsValue['GeneID'];
    }
  }  
  return $groupItemInfoStr."##".$geneID_str;
}

function get_group_item_id($group_color_num){
  global $groupArr;
  $tmp_arr = array();
  if(is_numeric($group_color_num)) return $tmp_arr;
  if(!isset($groupArr[$group_color_num])) return $tmp_arr;
  $tmp_arr = explode(",", $groupArr[$group_color_num]['inStr']);
  return $tmp_arr;
}

function get_elements_property(&$elementsPropertyArr,$inInfo,$multiple=''){
  global $HITSDB,$currentType,$frm_order_by;
  if($currentType == 'Bait'){
    $SQL = "SELECT `ID`,`GeneID`,`GeneName`,`LocusTag`,`Tag`,`Mutation`,BaitAcc";
    $FROM =" FROM `Bait`"; 
    $ORDER_BY = "ORDER BY ID DESC";
    $ID = 'ID';
  }elseif($currentType == 'Exp'){
    $SQL = "SELECT E.ID, E.BaitID, E.Name, B.GeneID, B.GeneName, B.LocusTag, B.Tag, B.Mutation, B.BaitAcc";
    $FROM ="  FROM Experiment E
              LEFT JOIN Bait B ON E.BaitID = B.ID";
    $ORDER_BY = "ORDER BY E.ID DESC";
    $ID = 'E.ID';
  }elseif($currentType == 'Band'){    
    $SQL = "SELECT D.ID, D.BaitID, D.Location, B.GeneID, B.GeneName, B.LocusTag, B.Tag, B.Mutation,B.BaitAcc, L.GelID, L.LaneNum";
    $FROM ="  FROM Band D
              LEFT JOIN Bait B ON D.BaitID = B.ID
              LEFT JOIN Lane L ON D.LaneID = L.ID";
    $ORDER_BY = "ORDER BY D.ID DESC";
    $ID = 'D.ID';
  }else{
    return;
  }
	if($multiple){
    $tmpIDarr = explode(',',$inInfo);
    foreach($tmpIDarr as $tmpID){
      if(!is_numeric($tmpID)){
        $group_item_id_arr = get_group_item_id($tmpID);        
        foreach($group_item_id_arr as $group_item_id_val){
          $WHERE = " WHERE $ID=$group_item_id_val ";
          $tmp_SQL = $SQL . $FROM.$WHERE.$ORDER_BY;
        	$elementsPropertyArr_tmp = $HITSDB->fetch($tmp_SQL);
          array_push($elementsPropertyArr,$elementsPropertyArr_tmp);
        }
      }else{
        $WHERE = " WHERE $ID=$tmpID ";
        $tmp_SQL = $SQL . $FROM.$WHERE.$ORDER_BY;
      	$elementsPropertyArr_tmp = $HITSDB->fetch($tmp_SQL);
        array_push($elementsPropertyArr,$elementsPropertyArr_tmp);
      }  
    }
	}else{
  	$WHERE = " WHERE $ID=$inInfo ";
    $SQL .= $FROM.$WHERE.$ORDER_BY;
		$elementsPropertyArr = $HITSDB->fetch($SQL);
	}
}

function create_item_lable_arr(&$itemLableArr,&$itemlableMaxL){
  global $groupArr,$no_groupped_str,$itemNoName,$lable_GeneName_ID_arr,$item_geneName_id_arr,$currentType;
  global $itemID_geneID_arr;
   
  foreach($groupArr as $groupKey => $groupValue){
    $itemID_geneID_arr[$groupKey] = $groupValue['geneID'];
    $itemLableArr[$groupKey] = $groupValue['lable'];
    $itemLableForLen = $groupValue['lable'];
    $tmpKey = str_replace(",", "|", $groupValue['simpleInfo']);
    $item_geneName_id_arr[$tmpKey] = $groupValue['geneID'];
    if(strlen($itemLableForLen) > $itemlableMaxL) $itemlableMaxL = strlen($itemLableForLen);
  }
  
	if($no_groupped_str){
    $itemsArr = array();
  	get_elements_property($itemsArr,$no_groupped_str,'multiple');
    
    foreach($itemsArr as $tmp_val){
      if(!$tmp_val['GeneID'] || $tmp_val['GeneID'] == '-1'){
        if(!$tmp_val['BaitAcc']){
          $itemID_geneID_arr[$tmp_val['ID']] = $tmp_val['GeneName'];
        }else{
          $itemID_geneID_arr[$tmp_val['ID']] = $tmp_val['BaitAcc'];
        }
      }else{
        $itemID_geneID_arr[$tmp_val['ID']] = $tmp_val['GeneID'];
      }  
    }
  	foreach($itemsArr as $itemsValue){
       $tag = '';
      if($itemsValue['Tag'] && $itemsValue['Mutation']){
        $tag = "(".$itemsValue['Tag'].";".$itemsValue['Mutation'].")";
      }elseif($itemsValue['Tag']){
        $tag = "(".$itemsValue['Tag'].")";
      }elseif($itemsValue['Mutation']){
        $tag = "(".$itemsValue['Mutation'].")";
      }
      
      if($currentType == 'Bait'){
    	  if($itemsValue['GeneName'] && $itemsValue['GeneName'] != "-"){
    	    $itemLable = $itemsValue['GeneName'].$tag;
    	  }elseif($itemsValue['LocusTag'] && $itemsValue['LocusTag'] != "-"){
    	    $itemLable = $itemsValue['LocusTag'].$tag;
    	  }else{
    	    $itemNoName++;
    	    $itemLable = "noName-".$itemNoName;
    	  }
      }elseif($currentType == 'Exp'){  
        if($itemsValue['Name']){
    	    $itemLable = $itemsValue['Name'].$tag;
    	  }else{
    	    $itemNoName++;
    	    $itemLable = "noName-".$itemNoName;
    	  }
      }elseif($currentType == 'Band'){ 
        if($itemsValue['Location']){
    	    $itemLable = $itemsValue['Location'].$tag;
    	  }else{
    	    $itemNoName++;
    	    $itemLable = "noName-".$itemNoName;
    	  }
      }
      $itemLableArr[$itemsValue['ID']] = $itemLable;
      if(!isset($lable_GeneName_ID_arr[$itemsValue['GeneID']])){
        $lable_GeneName_ID_arr[$itemsValue['GeneID']] = array();
        array_push($lable_GeneName_ID_arr[$itemsValue['GeneID']], $itemsValue['ID']." ".$itemLable);
        $item_geneName_id_arr[$itemsValue['ID']." ".$itemLable] = $itemsValue['GeneID'];
      }else{
        array_push($lable_GeneName_ID_arr[$itemsValue['GeneID']], $itemsValue['ID']." ".$itemLable);
      }
  		$itemLable = $itemsValue['ID'].'@'.$itemLable;
      $itemLableForLen = $itemLable;      
    	if(strlen($itemLableForLen) > $itemlableMaxL) $itemlableMaxL = strlen($itemLableForLen);
  	}  
  }
}

function get_hits_in_single_bait_or_group($j,&$hitsArrs,$isImage=''){
  global $sqlOrderby,$Expect,$SearchEngine,$itemIdIndexArr,$groupArr,$HITSDB,$itemID,$DESC;
  global $frm_BT,$applyFilters;
  global $Is_geneLevel;
  global $all_gi_Acc_Version_arr;
  global $proteinDB;
    
  $filter_bait_is_checked = 0;
  if($applyFilters && isset($frm_BT) && $frm_BT) $filter_bait_is_checked = 1;
  //if(!$Is_geneLevel){
    $tmpField = hits_table_field_translate_for_tpp($sqlOrderby);
    if(!strstr($SearchEngine, 'TPP_')){
      $tmpSqlOrderby = $sqlOrderby;
    }else{
      $tmpSqlOrderby = $tmpField[0];
    }
    if($isImage){
      if(!strstr($SearchEngine, 'TPP_')){
        $subSelect = "$sqlOrderby";
      }else{
        $subSelect = $tmpField[1];
      }  
    }else{
      if(!strstr($SearchEngine, 'TPP_')){
        $subSelect = "Pep_num,Pep_num_uniqe,Coverage,$Expect";
      }else{
        $subSelect = "TOTAL_NUMBER_PEPTIDES as Pep_num,UNIQUE_NUMBER_PEPTIDES as Pep_num_uniqe,PERCENT_COVERAGE as Coverage,PROBABILITY as Expect";
      }  
    }  
  if($Is_geneLevel){
    $SQL = "SELECT `ID`, 
            `WellID`, 
            `BaitID`, 
            `BandID`,
            `GeneID`, 
            `GeneName`,
            `SpectralCount`,
            `Unique`,
            `Subsumed`, 
            `Redundant`,
            `SearchEngine` ";
    $HitsTable = 'Hits_GeneLevel';
  }else{
    if(!strstr($SearchEngine, 'TPP_')){
      $HitGI = 'HitGI';
      $SearchEngine_inTable = 'SearchEngine';
      $RedundantGI = 'RedundantGI';
      $hitMW = 'MW';
      $HitsTable = 'Hits';
    }else{
      $HitGI = 'ProteinAcc as HitGI';
      $SearchEngine_inTable = 'AccType '; //---not real
      $RedundantGI = 'INDISTINGUISHABLE_PROTEIN';
      $hitMW = 'PCT_SPECTRUM_IDS as MW'; //---not real
      $HitsTable = 'TppProtein';
    }
    $SQL = "SELECT ID,
                  WellID,
                  GeneID,
                  LocusTag,
                  $HitGI,
                  $subSelect,
                  $RedundantGI as RedundantGI,
                  $hitMW,             
                  $SearchEngine_inTable ";
    if(strstr($SearchEngine, 'TPP_')){
    $SQL .= ",XPRESSRATIO_MEAN,
            XPRESSRATIO_STANDARD_DEV,
            XPRESSRATIO_NUM_PEPTIDES";
    }
  }               
  $SQL .= " FROM $HitsTable";
  $WHERE = SearchEngine_WHERE_OR_($SearchEngine);
  
  /*if(strstr($SearchEngine, 'Mascot')){      
    $WHERE = " WHERE (SearchEngine='Mascot' OR SearchEngine='MascotUploaded') AND ";
  }elseif(strstr($SearchEngine, 'COMET')){      
    $WHERE = " WHERE (SearchEngine='COMET' OR SearchEngine='COMETUploaded') AND ";  
  }elseif(strstr($SearchEngine, 'iProphet')){      
    $WHERE = " WHERE (SearchEngine='iProphet' OR SearchEngine='iProphetUploaded') AND ";  
  }elseif(strstr($SearchEngine, 'GPM')){
    $WHERE = " WHERE (SearchEngine='GPM' OR SearchEngine='GPMUploaded') AND ";
  }elseif(strstr($SearchEngine, 'SEQUEST')){
    $WHERE = " WHERE (SearchEngine='SEQUEST' OR SearchEngine='SEQUESTUploaded') AND ";  
  }elseif(strstr($SearchEngine, 'Other')){
    $WHERE = " WHERE SearchEngine!='Mascot' AND SearchEngine!='MascotUploaded' AND SearchEngine!='COMET' AND SearchEngine!='COMETUploaded' AND SearchEngine!='iProphet' AND SearchEngine!='iProphetUploaded' AND SearchEngine!='GPM' AND SearchEngine!='GPMUploaded' AND SearchEngine!='SEQUEST' AND SearchEngine!='SEQUESTUploaded' AND ";
  }*/
  
  if($itemID == 'ExpID'){
    $itemID_tmp = 'BandILD';
  }else{
    $itemID_tmp = $itemID;
  }
  
  $is_group = 0;
  $hit_bait_gene_arr = array();
	$tmpPropertyArr = array();
  if(strstr($itemIdIndexArr[$j], 'C_')){
    $inGroup = $groupArr[$itemIdIndexArr[$j]]['inStr'];
    if($itemID == 'ExpID'){
      $itemID_tmp = 'BandID';
      $SQL_tmp = "SELECT `ID` FROM `Band` WHERE `ExpID` IN ($inGroup)";
      $tmpIDarr = $HITSDB->fetchAll($SQL_tmp);
      
      $inGroup_tmp = '';
      foreach($tmpIDarr as $tmpIDval){
        if($inGroup_tmp) $inGroup_tmp .= ',';
        $inGroup_tmp .= $tmpIDval['ID'];
      }
    }else{
      $itemID_tmp = $itemID;
      $inGroup_tmp = $inGroup;
    }
    $WHERE .= " $itemID_tmp IN($inGroup_tmp)";
		if($filter_bait_is_checked){
      get_elements_property($tmpPropertyArr,$itemIdIndexArr[$j],'multiple');
			$tmpGeneIDarr = '';
			foreach($tmpPropertyArr as $tmpPropertyVal){
				if($tmpGeneIDarr) $tmpGeneIDarr .= ",";
				$tmpGeneIDarr .= "'".$tmpPropertyVal['GeneID']."'";
			}
			if($tmpGeneIDarr){
				$SQL .= $WHERE.subWhere('1',$tmpGeneIDarr);
			}else{
				$SQL .= $WHERE.subWhere();
			}
      
		}else{
			get_same_gene_hit_bait_array($hit_bait_gene_arr,$inGroup,$HitsTable,'multiple');
			$SQL .= $WHERE.subWhere();
		}
    $is_group = 1;
  }else{
    if($itemID == 'ExpID'){
      $itemID_tmp = 'BandID';
      $SQL_tmp = "SELECT `ID` FROM `Band` WHERE `ExpID`='".$itemIdIndexArr[$j]."'";
      $tmpIDarr = $HITSDB->fetchAll($SQL_tmp);
      $inGroup_tmp = '';
      foreach($tmpIDarr as $tmpIDval){
        if($inGroup_tmp) $inGroup_tmp .= ',';
        $inGroup_tmp .= $tmpIDval['ID'];
      }
      $WHERE .= " $itemID_tmp IN ($inGroup_tmp)";
    }else{
      $itemID_tmp = $itemID;
      $WHERE .= " $itemID_tmp='".$itemIdIndexArr[$j]."'";
    }
        
    if($filter_bait_is_checked){
      get_elements_property($tmpPropertyArr,$itemIdIndexArr[$j]);
      $tmpBaitGeneID = $tmpPropertyArr['GeneID'];
			if($tmpBaitGeneID){
				$SQL .= $WHERE.subWhere('1',$tmpBaitGeneID);
			}else{
				 $SQL .= $WHERE.subWhere();
			}
    }else{
      get_same_gene_hit_bait_array($hit_bait_gene_arr,$itemIdIndexArr[$j],$HitsTable);
      $SQL .= $WHERE.subWhere();
    }    
  }          
  $SQL .= " ORDER BY `".$tmpSqlOrderby."` ".$DESC;
  $hitsArrs_tmp = $HITSDB->fetchAll($SQL);
/*echo "<pre>";
print_r($hitsArrs_tmp);
echo "</pre>"; 
exit;*/
  if($Is_geneLevel){
    $hitsArrs_tmp_geneLevel = array();
    for($i=0; $i<count($hitsArrs_tmp); $i++){
      if($filter_bait_is_checked){
        $hitsArrs_tmp[$i]['isBait'] = 0;
      }else{
        if(in_array($hitsArrs_tmp[$i]['ID'], $hit_bait_gene_arr)){
          $hitsArrs_tmp[$i]['isBait'] = 1;
        }else{
          $hitsArrs_tmp[$i]['isBait'] = 0;
        }
      }
      $geneID_arr = explode(",", $hitsArrs_tmp[$i]['GeneID']);
      $geneName_arr = explode(",", $hitsArrs_tmp[$i]['GeneName']);    
      if(count($geneID_arr) > 1){
        for($j=0; $j<count($geneID_arr); $j++){
          $tmp_arr = $hitsArrs_tmp[$i];
          //$tmp_arr['GeneID'] = $hitsArrs_tmp[$i]['GeneID'];
          $tmp_arr['GeneID'] = trim($geneID_arr[$j]);
          $tmp_arr['GeneName'] = trim($geneName_arr[$j]);
          $tmp_arr['Dup'] = $hitsArrs_tmp[$i]['GeneID'];
          $hitsArrs_tmp_geneLevel[] = $tmp_arr;
        }
      }else{
        $hitsArrs_tmp_geneLevel[] = $hitsArrs_tmp[$i];
      }
    }
    $hitsArrs = $hitsArrs_tmp_geneLevel;
  }else{  
    $gi_array = array();
    for($i=0; $i<count($hitsArrs_tmp); $i++){
      if($filter_bait_is_checked){
        $hitsArrs_tmp[$i]['isBait'] = 0;
      }else{
        if(in_array($hitsArrs_tmp[$i]['ID'], $hit_bait_gene_arr)){
          $hitsArrs_tmp[$i]['isBait'] = 1;
        }else{
          $hitsArrs_tmp[$i]['isBait'] = 0;
        }
      }
      $hitsArrs_tmp[$i]['HitGI_old'] = $hitsArrs_tmp[$i]['HitGI'];
      
      if(is_numeric($hitsArrs_tmp[$i]['HitGI']) && !array_key_exists($hitsArrs_tmp[$i]['HitGI'], $all_gi_Acc_Version_arr)){
        $gi_array[] = $hitsArrs_tmp[$i]['HitGI'];
      }
      $tmp_RedundantGI = trim($hitsArrs_tmp[$i]['RedundantGI']);
      if($tmp_RedundantGI){
        $tmp_RedundantGI = str_ireplace("gi|", "", $tmp_RedundantGI);
        $tmp_re_arr = explode(";", $tmp_RedundantGI);
        foreach($tmp_re_arr as $tmp_re_val){
          $tmp_re_val = trim($tmp_re_val);
          if(is_numeric($tmp_re_val) && !array_key_exists($tmp_re_val, $all_gi_Acc_Version_arr)){
            $gi_array[] = $tmp_re_val;
          }
        }
      }
    }
    if($gi_array){
      $gi_str = implode(',',$gi_array);
      
      if($gi_str){
        $SQL = "SELECT `GI`,`Acc_Version` FROM `Protein_Accession` WHERE `GI` IN ($gi_str)";
        $tmp_gi_acc_arr = $proteinDB->fetchAll($SQL);
        foreach($tmp_gi_acc_arr as $tmp_gi_acc_val){
          $all_gi_Acc_Version_arr[$tmp_gi_acc_val['GI']] = $tmp_gi_acc_val['Acc_Version'];
        }
      }    
      for($i=0; $i<count($hitsArrs_tmp); $i++){
        if(array_key_exists($hitsArrs_tmp[$i]['HitGI'], $all_gi_Acc_Version_arr)){
          $hitsArrs_tmp[$i]['HitGI'] = $all_gi_Acc_Version_arr[$hitsArrs_tmp[$i]['HitGI']];
        }
        $tmp_RedundantGI = trim($hitsArrs_tmp[$i]['RedundantGI']);
        if($tmp_RedundantGI){
          $tmp_RedundantGI = str_ireplace("gi|", "", $tmp_RedundantGI);
          $tmp_re_arr = explode(";", $tmp_RedundantGI);
          $tmp_re_str = '';
          foreach($tmp_re_arr as $tmp_re_val){
            if($tmp_re_str) $tmp_re_str .= '; ';
            $tmp_re_val = trim($tmp_re_val);
            if(array_key_exists($tmp_re_val, $all_gi_Acc_Version_arr)){
              $tmp_re_str .= $all_gi_Acc_Version_arr[$tmp_re_val];
            }else{
              $tmp_re_str .= $tmp_re_val;
            }
          }
          $hitsArrs_tmp[$i]['RedundantGI'] = $tmp_re_str;
//echo $hitsArrs_tmp[$i]['RedundantGI']."<br>";
        }
      }
    }
    $hitsArrs = $hitsArrs_tmp;
  }
}

//--$hit_bait_gene_arr--return hits has same geneid with baits or band
function get_same_gene_hit_bait_array(&$hit_bait_gene_arr,$inInfo,$HitsTable,$multiple=''){
  global $HITSDB,$currentType;
  $elementsPropertyArr = array();  
  get_elements_property($elementsPropertyArr,$inInfo,$multiple);
  if($currentType == 'Bait'){
		$itemID = "BaitID";
  }elseif($currentType == 'Exp'){
    $tmp_PropertyArr = $elementsPropertyArr;    
    $elementsPropertyArr = array();
    if($multiple){
      foreach($tmp_PropertyArr as $tmpVal){
        $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID`='".$tmpVal['ID']."'";
        if($tmp_E_arr = $HITSDB->fetchAll($SQL)){
          foreach($tmp_E_arr as $tmp_E_val){
            $tmp_s_arr['ID'] = $tmp_E_val['ID'];
            $tmp_s_arr['GeneID'] = $tmpVal['GeneID'];
            array_push($elementsPropertyArr, $tmp_s_arr);
          }
          
        }
      }
    }else{
      $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID`='".$tmp_PropertyArr['ID']."'";
        if($tmp_E_arr = $HITSDB->fetchAll($SQL)){
          foreach($tmp_E_arr as $tmp_E_val){
            $tmp_s_arr['ID'] = $tmp_E_val['ID'];
            $tmp_s_arr['GeneID'] = $tmp_PropertyArr['GeneID'];
            array_push($elementsPropertyArr, $tmp_s_arr);
          }
        }
    }
    $itemID = "BandID";
    $multiple = "multiple";    
	}elseif($currentType == 'Band'){
		$itemID = "BandID";
	}	   
  
	if(!$multiple){
    $SQL = "SELECT ID                
            FROM $HitsTable
            WHERE $itemID = '".$elementsPropertyArr['ID']."'
            AND GeneID = '".$elementsPropertyArr['GeneID']."'";
    $tmpHitIDs = $HITSDB->fetchAll($SQL);
    foreach($tmpHitIDs as $tmpHitIDsvalue){
      if(!in_array($tmpHitIDsvalue['ID'], $hit_bait_gene_arr)){
        array_push($hit_bait_gene_arr, $tmpHitIDsvalue['ID']);
      }
    }
	}else{
    foreach($elementsPropertyArr as $value){
      $SQL = "SELECT ID                
              FROM $HitsTable
              WHERE $itemID = '".$value['ID']."'
              AND GeneID = '".$value['GeneID']."'";
      $tmpHitIDs = $HITSDB->fetchAll($SQL);
      foreach($tmpHitIDs as $tmpHitIDsvalue){
        if(!in_array($tmpHitIDsvalue['ID'], $hit_bait_gene_arr)){
          array_push($hit_bait_gene_arr, $tmpHitIDsvalue['ID']);
        }
      }  
    }
  } 
}

function subWhere($is_filter_bait='',$baitGeneID=''){
  global $frm_filter_Expect, $frm_filter_Coverage, $frm_filter_Peptide,$frm_filter_Peptide_value,$Expect,$applyFilters,$SearchEngine;
  global $frm_min_XPRESS,$frm_max_XPRESS,$php_file_name;
  global $Is_geneLevel,$hisType;
  
  if(!$applyFilters) return '';
  $ht = '';  
  if($php_file_name != "comparison_results_table" && $php_file_name != "comparison_results_image.php"){
    if(strstr($SearchEngine, 'TPP_')){
      $ht = "T.";
    }else{
      $ht = "H.";
    }
  }    
  $subWhereStr = '';
  
  /*echo "\$Is_geneLevel=$Is_geneLevel<br>";
  echo "\$hisType=$hisType<br>";
  echo "\$frm_filter_Expect=$frm_filter_Expect<br>";
  echo "\$frm_filter_Coverage=$frm_filter_Coverage<br>";
  echo "\$frm_filter_Peptide=$frm_filter_Peptide<br>";
  echo "\$frm_filter_Peptide_value=$frm_filter_Peptide_value<br>";
  echo "\$Expect=$Expect<br>";
  echo "\$applyFilters=$applyFilters<br>";
  echo "\$SearchEngine=$SearchEngine<br>";*/
  
  if($frm_filter_Peptide == 'Pep_num' && $frm_filter_Peptide_value > 0){
    if(!strstr($SearchEngine, 'TPP_')){
      $tem_Pep_num = $ht.'Pep_num';
    }else{
      $tem_Pep_num = $ht.'TOTAL_NUMBER_PEPTIDES';
    }    
    $subWhereStr .= " AND $tem_Pep_num>=$frm_filter_Peptide_value ";
  }
  if($frm_filter_Peptide_value > 0 && $frm_filter_Peptide != 'Pep_num'){
    if(isset($Is_geneLevel) && $Is_geneLevel || isset($hisType) && $hisType=='geneLevel'){
      $tem_Pep_num_uniqe = $ht.'`Unique`';
      $subWhereStr .= " AND $tem_Pep_num_uniqe>=$frm_filter_Peptide_value ";
    }elseif($frm_filter_Peptide == 'Pep_num_uniqe'){
      if(!strstr($SearchEngine, 'TPP_')){
        $tem_Pep_num_uniqe = $ht.'Pep_num_uniqe';
      }else{
        $tem_Pep_num_uniqe = $ht.'UNIQUE_NUMBER_PEPTIDES';
      }
      $subWhereStr .= " AND $tem_Pep_num_uniqe>=$frm_filter_Peptide_value ";
    }
  }
  if($Expect == 'Expect' && $frm_filter_Expect > 0){
    if(isset($Is_geneLevel) && $Is_geneLevel || isset($hisType) && $hisType=='geneLevel'){
      $tem_Expect = $ht.'SpectralCount';
    }elseif(!strstr($SearchEngine, 'TPP_')){
      $tem_Expect = $ht.'Expect';
    }else{
      $tem_Expect = $ht.'PROBABILITY';
    }
    $subWhereStr .= " AND $tem_Expect>=$frm_filter_Expect ";
  }elseif($Expect == 'Expect2' && $frm_filter_Expect < 0){
    $subWhereStr .= " AND ".$ht.$Expect."<$frm_filter_Expect ";
  }
  if($frm_filter_Coverage > 0){
    if(!strstr($SearchEngine, 'TPP_')){
      $tem_Coverage = $ht.'Coverage';
    }else{
      $tem_Coverage = $ht.'PERCENT_COVERAGE';
    }
    $subWhereStr .= " AND $tem_Coverage>=$frm_filter_Coverage ";
  }
  if($is_filter_bait && $baitGeneID){
    $q_GeneID = $ht.'GeneID';
		if(strstr($baitGeneID, ",")){
			$subWhereStr .= " AND $q_GeneID NOT IN ($baitGeneID) ";
		}else{
      if(!strstr($baitGeneID, "'")) $baitGeneID = "'".$baitGeneID."'";
    	$subWhereStr .= " AND $q_GeneID != $baitGeneID ";
		}
  }
  if(strstr($SearchEngine, 'TPP_') && ($frm_min_XPRESS || $frm_max_XPRESS)){
    $q_XPRESSRATIO_MEAN = $ht.'XPRESSRATIO_MEAN';
    if($frm_min_XPRESS && $frm_max_XPRESS){
      $subWhereStr .= " AND $q_XPRESSRATIO_MEAN >= $frm_min_XPRESS AND $q_XPRESSRATIO_MEAN <= $frm_max_XPRESS ";
    }elseif($frm_min_XPRESS){
      $subWhereStr .= " AND $q_XPRESSRATIO_MEAN >= $frm_min_XPRESS ";
    }elseif($frm_max_XPRESS){
      $subWhereStr .= " AND $q_XPRESSRATIO_MEAN <= $frm_max_XPRESS ";
    }
  }
  return $subWhereStr;
}

function filter_Fequency(&$hitsArrValue,$index){
  global $frm_filter_Fequency,$frm_filter_Fequency_value,$subFeqsValueArr, $FeqValueArr,$applyFilters,$allBaitgeneIDarr;
  global $A,$itemIdIndexArr;
  if(!$applyFilters) return false;
  if($A){
    if($allBaitgeneIDarr[$itemIdIndexArr[$index]] == $hitsArrValue['GeneID']) return false;
  }
  if($frm_filter_Fequency == 'Fequency' && isset($FeqValueArr[$hitsArrValue['GeneID']]) && $FeqValueArr[$hitsArrValue['GeneID']] >= $frm_filter_Fequency_value){
    return true;
  }elseif(is_numeric($frm_filter_Fequency) && isset($subFeqsValueArr[$frm_filter_Fequency][$hitsArrValue['GeneID']]) && $subFeqsValueArr[$frm_filter_Fequency][$hitsArrValue['GeneID']] >= $frm_filter_Fequency_value){
    return true;
  }
  return false;
}

function create_itemTree_hitsIndex_hitsPropty_Arrs($j,&$firstHitsArr,&$contrlArr,&$hitsGeneIdIndexArr,&$hitsGeneIdIndexArr2,&$hitsNameArr,&$itemNameArr,$is_image=''){
	global $hasGeneID,$hasProteinID,$hasLocusTag,$itemIdIndexArr,$theaction,$frm_color_mode,$contrlColor;
  global $sqlOrderby,$sort_by_item_id,$Expect,$asc_desc,$orderby,$SearchEngine,$A,$FeqFiltedGeneIdArr;
  global $Is_geneLevel,$hisType;
  
  $hitsArrs = array();
	get_hits_in_single_bait_or_group($j,$hitsArrs,$is_image);

  $tmpHitsArr = array();
  if(($orderby == 'Expect2' && $asc_desc == 'DESC') || ($orderby != 'Expect2' && $asc_desc == 'ASC')){
     $tmpReverseArr = array();
  }     
  foreach($hitsArrs as $hitsArrValue){
    if($A){
      if(in_array($hitsArrValue['GeneID'], $FeqFiltedGeneIdArr) && !$hitsArrValue['isBait']) continue;
    }else{
      if(in_array($hitsArrValue['GeneID'], $FeqFiltedGeneIdArr)) continue;
    }  
		if(!$hasGeneID) $hasGeneID = 1;
		if(!$hasProteinID) $hasProteinID = 1;
    if(!$hasLocusTag && isset($hitsArrValue['LocusTag']) && $hitsArrValue['LocusTag'] && $hitsArrValue['LocusTag'] != '-'){
      if(get_protein_ID_type($hitsArrValue['LocusTag']) == 'ORF') $hasLocusTag = 1;
    }
  	if($hitsArrValue['GeneID']){
      if(!array_key_exists($hitsArrValue['GeneID'], $tmpHitsArr)){
        if($sort_by_item_id == $itemIdIndexArr[$j]){
         	array_push($firstHitsArr, $hitsArrValue['GeneID']);
       	}
        if($frm_color_mode == 'shared' && $itemIdIndexArr[$j] == $contrlColor){
          array_push($contrlArr, $hitsArrValue['GeneID']);
        } 
  			if($is_image){
  				$tmpHitValue = $hitsArrValue['HitGI'].":".$hitsArrValue[$sqlOrderby].":".$hitsArrValue['isBait'].":".$hitsArrValue['ID'].":".$hitsArrValue['MW'].":".$hitsArrValue['WellID']; 
  			}else{
          $TPPextraInfo = '';
          if(strstr($SearchEngine, 'TPP_')){
            $TPPextraInfo = "##XPRESSRATIO_MEAN=".$hitsArrValue["XPRESSRATIO_MEAN"]."##XPRESSRATIO_STANDARD_DEV=".$hitsArrValue["XPRESSRATIO_STANDARD_DEV"]."##XPRESSRATIO_NUM_PEPTIDES=".$hitsArrValue["XPRESSRATIO_NUM_PEPTIDES"];
          }
          if((isset($Is_geneLevel) && $Is_geneLevel) || isset($hisType) && $hisType == 'geneLevel'){
            $Dup_str = "";
            if(isset($hitsArrValue['Dup']) && $hitsArrValue['Dup']){
              $Dup_str = "##Dup=".$hitsArrValue['Dup'];
            }   
            $tmpHitValue = "GeneID=".$hitsArrValue['GeneID']."##SpectralCount=".$hitsArrValue['SpectralCount']."##Unique=".$hitsArrValue['Unique']."##Subsumed=".$hitsArrValue['Subsumed']."##ID=".$hitsArrValue['ID']."##WellID=".$hitsArrValue['WellID']."##RedundantGI=".$hitsArrValue['Redundant'].$TPPextraInfo."##isBait=".$hitsArrValue['isBait'].$Dup_str;
//echo "$tmpHitValue<br>";
          }else{
            $tmpHitValue = "HitGI=".$hitsArrValue['HitGI']."##Expect=".$hitsArrValue[$Expect]."##Pep_num=".$hitsArrValue['Pep_num']."##Pep_num_uniqe=".$hitsArrValue['Pep_num_uniqe']."##Coverage=".$hitsArrValue['Coverage']."##ID=".$hitsArrValue['ID']."##MW=".$hitsArrValue['MW']."##WellID=".$hitsArrValue['WellID']."##RedundantGI=".$hitsArrValue['RedundantGI'].$TPPextraInfo."##isBait=".$hitsArrValue['isBait'];
          }
        }
  			$tmpHitsArr[$hitsArrValue['GeneID']] = $tmpHitValue;
       	if(!in_array($hitsArrValue['GeneID'], $hitsGeneIdIndexArr)){
          if(($orderby == 'Expect2' && $asc_desc == 'DESC') || ($orderby != 'Expect2' && $asc_desc == 'ASC')){
            array_push($tmpReverseArr, $hitsArrValue['GeneID']);
          }
   				array_push($hitsGeneIdIndexArr, $hitsArrValue['GeneID']);
          if($theaction != "popWindow"){
            creat_hitsName_property($hitsNameArr,$hitsArrValue,'GeneID');//=======================
          }  
       	}else{
          if($theaction != "popWindow"){
            improve_hitsName_property($hitsNameArr,$hitsArrValue,'GeneID');
          }  
  		  }
      }  
   	}elseif($hitsArrValue['HitGI']){// && $hitsArrValue['HitGI'] != 'none'){
      $tempHitGI = $hitsArrValue['HitGI']."_GI";
      if(!array_key_exists($tempHitGI, $tmpHitsArr)){ 
        if($sort_by_item_id == $itemIdIndexArr[$j]){
          array_push($firstHitsArr, $tempHitGI);
        }
        if($frm_color_mode == 'shared' && $itemIdIndexArr[$j] == $contrlColor){
          array_push($contrlArr, $tempHitGI);
        }
  			if($is_image){
          $tmpHitValue = $hitsArrValue['HitGI'].":".$hitsArrValue[$sqlOrderby].":".$hitsArrValue['isBait'].":".$hitsArrValue['ID'].":".$hitsArrValue['MW'].":".$hitsArrValue['WellID'];
       	}else{
          $TPPextraInfo = '';
          if(strstr($SearchEngine, 'TPP_')){
            $TPPextraInfo = "##XPRESSRATIO_MEAN=".$hitsArrValue["XPRESSRATIO_MEAN"]."##XPRESSRATIO_STANDARD_DEV=".$hitsArrValue["XPRESSRATIO_STANDARD_DEV"]."##XPRESSRATIO_NUM_PEPTIDES=".$hitsArrValue["XPRESSRATIO_NUM_PEPTIDES"];
          }
         	$tmpHitValue = "HitGI=".$hitsArrValue['HitGI']."##Expect=".$hitsArrValue[$Expect]."##Pep_num=".$hitsArrValue['Pep_num']."##Pep_num_uniqe=".$hitsArrValue['Pep_num_uniqe']."##Coverage=".$hitsArrValue['Coverage']."##ID=".$hitsArrValue['ID']."##MW=".$hitsArrValue['MW']."##WellID=".$hitsArrValue['WellID'].$TPPextraInfo."##isBait=".$hitsArrValue['isBait'];
        }
  			$tmpHitsArr[$tempHitGI] = $tmpHitValue;
        if(!in_array($tempHitGI, $hitsGeneIdIndexArr)){
          if(($orderby == 'Expect2' && $asc_desc == 'DESC') || ($orderby != 'Expect2' && $asc_desc == 'ASC')){
            array_push($tmpReverseArr, $tempHitGI);
          }
          array_push($hitsGeneIdIndexArr, $tempHitGI);
          if($theaction != "popWindow"){
            creat_hitsName_property($hitsNameArr,$hitsArrValue,'HitGI');//=======================
          }  
       	}else{
          if($theaction != "popWindow"){
   				  improve_hitsName_property($hitsNameArr,$hitsArrValue,'HitGI');
          }  
  		  }
      }
    }
  }  
  if(($orderby == 'Expect2' && $asc_desc == 'DESC') || ($orderby != 'Expect2' && $asc_desc == 'ASC')){
    $tmpReverseArr = array_reverse($tmpReverseArr);
    foreach($tmpReverseArr as $value){
      array_push($hitsGeneIdIndexArr2, $value);
    }  
  }  
  $itemNameArr[$itemIdIndexArr[$j]] = $tmpHitsArr;
}

function creat_hitsName_property(&$hitsNameArr,&$hitsArrValue,$Gene_HitGi){
  global $PROTEINDB;
  global $Is_geneLevel,$hisType;
  if($Gene_HitGi == 'GeneID'){
    $inDex = $hitsArrValue['GeneID'];    
    if(get_Gene_ID_type($inDex) == 'ENS'){
      $Protein_Class = 'Protein_ClassENS';
      $EntrezGeneID = 'ENSG';
      $BioFilter = '';
    }else{
      $Protein_Class = 'Protein_Class';
      $EntrezGeneID = 'EntrezGeneID';
      $BioFilter = ',BioFilter';
    }
    $SQL = "SELECT `GeneName` $BioFilter FROM $Protein_Class WHERE $EntrezGeneID='".$hitsArrValue['GeneID']."'";
    $GeneNameArr = $PROTEINDB->fetch($SQL);
  }else{
    if(isset($hitsArrValue['HitGI'])){
      $inDex = $hitsArrValue['HitGI']."_GI";
      $GeneNameArr['GeneName'] = $hitsArrValue['HitGI'];
    }else{
      $GeneNameArr['GeneName'] = '';
    }
    $GeneNameArr['BioFilter'] = '';
  }
  $tmpStr = '';
  if(isset($hitsArrValue['GeneName'])){
    $GeneName = $hitsArrValue['GeneName'];
	}elseif($GeneNameArr){
  	$GeneName = $GeneNameArr['GeneName'];
	}else{
  	$GeneName = '';
	} 
  if(isset($GeneNameArr['BioFilter'])){
    $tmpStr = $GeneNameArr['BioFilter'];
  }  
  if(isset($hitsArrValue['LocusTag']) && $hitsArrValue['LocusTag'] == '-') $hitsArrValue['LocusTag'] = '';
  if((isset($Is_geneLevel) && $Is_geneLevel) || (isset($hisType) && $hisType == 'geneLevel')){
    $hitsNameArr[$inDex]['name'] = $GeneName.",,";
  }else{
    $hitsNameArr[$inDex]['name'] = $GeneName.','.$hitsArrValue['HitGI'].','.$hitsArrValue['LocusTag'].','.$hitsArrValue['HitGI_old'];
  }
  $hitsNameArr[$inDex]['filter'] = $tmpStr;
  $hitsNameArr[$inDex]['counter'] = 1;
  $hitsNameArr[$inDex]['ctr'] = 0;
  $hitsNameArr[$inDex]['isBait'] = $hitsArrValue['isBait'];
}
//-------?????????????????????????????????????????????????????????????????????????????????????
function improve_hitsName_property(&$hitsNameArr,&$hitsArrValue,$Gene_HitGi){
  if($Gene_HitGi == 'GeneID'){
    $inDex = $hitsArrValue['GeneID'];
  }else{
    $inDex = $hitsArrValue['HitGI']."_GI";
  }
  if(!isset($hitsNameArr[$inDex]['counter'])){
    $hitsNameArr[$inDex]['counter'] = 1;
  }else{
    $hitsNameArr[$inDex]['counter']++;
  }
  if((!isset($hitsNameArr[$inDex]['isBait']) || !$hitsNameArr[$inDex]['isBait']) && $hitsArrValue['isBait']){
    $hitsNameArr[$inDex]['isBait'] = $hitsArrValue['isBait'];
  }
}
//---------?????????????????????????????????????????????????????????????????????????????????

function format_image(){
  global $cellH, $cellW, $overallWidth, $overallHeight,$totalitems,$totalHits,$fontSize,$labalH,$fontH,$itemlableMaxL,$noLableHeight;
	$maxCellH = 5;
  $maxCellW = 30;
  $minCellH = 1;
  $minCellW = 1;
  if($overallWidth/$totalitems > $maxCellW){
    $cellW = $maxCellW;
  }elseif($overallWidth/$totalitems < $minCellW){
    $cellW = $minCellW;
  }else{
    $cellW =  round(($overallWidth/$totalitems)-0.5);
  }
  $overallWidth = $cellW * $totalitems;
  if($totalHits == 0){
    $cellH = 0;
  }elseif($overallHeight/$totalHits > $maxCellH){
    $cellH = $maxCellH;
  }elseif($overallHeight/$totalHits < $minCellH){
    $cellH = $minCellH;
  }else{
    $cellH = round(($overallHeight/$totalHits)-0.5);
  }
  
  $font1Heighth = imagefontheight(1);
  $font1Width = imagefontwidth(1);
  $font2Heighth = imagefontheight(2);
  $font2Width = imagefontwidth(2);
  $font4Heighth = imagefontheight(4);
  $font4Width = imagefontwidth(4);
  
  if($cellW > $font4Heighth+2){
    $fontSize = 4;
    $labalH = $font4Width*$itemlableMaxL + 3;
    $fontH = $font4Heighth;
  }elseif($cellW > $font2Heighth+2){
    $fontSize = 2;
    $fontH = $font2Heighth;
    $labalH = $font2Width*$itemlableMaxL + 3;
  }elseif($cellW > $font1Heighth+2){
    $fontSize = 1;
    $fontH = $font1Heighth;
    $labalH = $font1Width*$itemlableMaxL + 3;
  }else{
    $labalH = 0;
  }
  $overallHeight = $cellH * $totalHits + $labalH;
  $noLableHeight = $cellH * $totalHits;
  
  if($overallHeight > 5000){
    $cellW = 1;
    $overallWidth = $cellW * $totalitems;
  }
}

function reportFile_title_info(&$groupArr,&$itemLableArr,$itemlableMaxL,$totalitems){
  global $reportFile_handle,$frm_color_mode,$lable_GeneName_ID_arr,$apply_bioGrid;
  global $itemID_geneID_arr;
  $groupInfo = '';
  $colorMode = "colorMode;;".$frm_color_mode."\r\n";
  fwrite($reportFile_handle, $colorMode);
  fwrite($reportFile_handle, "totalitems;;$totalitems\r\n");
  $itemLableInfo = '';
  foreach($itemLableArr as $key => $value){
    if(strstr($key, 'C_')) continue;
    if(array_key_exists($key, $itemID_geneID_arr)){
      $item_geneid = $itemID_geneID_arr[$key];
    }else{
      $item_geneid = '';
    }
    if($itemLableInfo) $itemLableInfo .= ',';
    $itemLableInfo .= $key.' '.$value.' '.$item_geneid;
  }  
  $itemLableInfo = "itemLableInfo;;".$itemLableInfo."\r\n";
  fwrite($reportFile_handle, $itemLableInfo);
  $du_NameGeneID = '';
  $baitGeneIDstr = '';

  foreach($lable_GeneName_ID_arr as $key => $value){
    if($baitGeneIDstr) $baitGeneIDstr .= ",";
    $baitGeneIDstr .= $key;
    
    if(count($value) <= 1) continue;
    if($du_NameGeneID) $du_NameGeneID .= ',';
    $du_NameGeneID .= $key . "@";
    $tmpNameStr = '';
    foreach($value as $tmpName){
      if($tmpNameStr) $tmpNameStr .= ':';
      $tmpNameStr .= $tmpName;
    }
    $du_NameGeneID .= $tmpNameStr;
  }
  $baitGeneIDstr = "baitGeneIDstr;;".$baitGeneIDstr."\r\n";
  fwrite($reportFile_handle, $baitGeneIDstr);
  $du_NameGeneID = "du_NameGeneID;;".$du_NameGeneID."\r\n";
  fwrite($reportFile_handle, $du_NameGeneID);
  if($apply_bioGrid){
    fwrite($reportFile_handle, "bioGrid_overlap;;yes\r\n");
  }else{
    fwrite($reportFile_handle, "bioGrid_overlap;;no\r\n");
  }  
  //-----------------------------------------------------------------------
  foreach($groupArr as $key => $value){
    if($groupInfo) $groupInfo .= '@@';
    $groupInfo .= $key.'##';
    $groupInfo .= $value['lable'].'##';
    $tmp_arr =  explode(",", $value['simpleInfo']);
    $tmp_groupInfo = '';
    foreach($tmp_arr as $tmp_val){
      if($tmp_groupInfo) $tmp_groupInfo .= ',';
      $tmp_groupInfo .= $tmp_val;
      $tmp_arr2 = explode(" ", $tmp_val);
      $tmp_arr2[0] = trim($tmp_arr2[0]);
      if(array_key_exists($tmp_arr2[0], $itemID_geneID_arr)){
        $item_geneid = $itemID_geneID_arr[$tmp_arr2[0]];
      }else{
        $item_geneid = '';
      }
      $tmp_groupInfo .= " ".$item_geneid;
    }
    $groupInfo .= $tmp_groupInfo;
  }
  $groupInfo = "groupInfo;;".$groupInfo."\r\n";
  fwrite($reportFile_handle, $groupInfo);
  $itemlableMaxL = "itemlableMaxL;;".$itemlableMaxL."\r\n";
  fwrite($reportFile_handle, $itemlableMaxL);
}

function get_subFequency(&$subFeqIndexArr, &$subFeqsValueArr,&$FeqFiltedGeneIdArr,&$passedTypeArr, $typeNum=''){
  global $AccessProjectID, $asc_desc,$theation,$applyFilters,$frm_filter_Fequency,$frm_filter_Fequency_value;
  global $SearchEngine;
  $updatedFlag = 0;
  $maxScore = 0;
	if(!$passedTypeArr) return 0;
  
  //echo "\$frm_filter_Fequency=$frm_filter_Fequency<br>";
  //echo "\$typeNum=$typeNum<br>";
	
  $subDir = STORAGE_FOLDER."Prohits_Data/subFrequency/";
  foreach($passedTypeArr as $typeKey => $typeValue){
    if(!$typeKey) continue;
    if(strstr($SearchEngine, 'TPP_')){
      $subFileName = $subDir."Pro".$AccessProjectID."_Type".$typeKey."_TPP.csv";
    }else{
      $subFileName = $subDir."Pro".$AccessProjectID."_Type".$typeKey.".csv";
    } 
    
       
    if(!is_file($subFileName) && !$updatedFlag){
       //updata_frequency();
			 //$updatedFlag = 1;
       return;
    }
    //if(is_file($subFileName)){
      $lines = file($subFileName);
      array_shift($lines);
      $subFeqValueArr = array();
      foreach($lines as $lineValue){  
        list($GeneID, $Freqency) = explode(',', $lineValue);
        $GeneID = trim($GeneID);
        $Freqency = trim($Freqency);
        $subFeqValueArr[$GeneID] = $Freqency;
        if($applyFilters && $typeKey == $frm_filter_Fequency && $frm_filter_Fequency_value){
          if($Freqency >= $frm_filter_Fequency_value){
            array_push($FeqFiltedGeneIdArr, trim($GeneID));
          }
        }
      //}
    }
    if($typeKey == $typeNum){
      if($asc_desc == 'DESC'){
        arsort($subFeqValueArr);
      }else{
        asort($subFeqValueArr);
      }
      foreach($subFeqValueArr as $subFeqKey => $subFeqValue){
        if($maxScore < $subFeqValue){
          $maxScore = $subFeqValue;
        }
        array_push($subFeqIndexArr, $subFeqKey);
      }  
    }
		$subFeqsValueArr[$typeKey] = $subFeqValueArr;
  }
  return $maxScore;
}

function get_userFequency(&$userFeqIndexArr,&$userFeqsValueArr,&$FeqFiltedGeneIdArr,&$optionArr_for_user_d_frequency,$u_file_name=''){
  global $AccessProjectID, $asc_desc,$theation,$applyFilters,$frm_filter_Fequency,$frm_filter_Fequency_value;
  global $SearchEngine;
  $maxScore = 0;
	if(!$optionArr_for_user_d_frequency) return 0;
  $user_d_frequency_dir = STORAGE_FOLDER . "Prohits_Data/user_d_frequency/";
  $frequency_dir = $user_d_frequency_dir . "P_$AccessProjectID/";
  
  foreach($optionArr_for_user_d_frequency as $u_file_name => $u_file_lable){
    $tmp_arr = explode(':', $u_file_name);
    $userFileName = $frequency_dir.$tmp_arr[1];
    $lines = generate_frequency_arr($userFileName);
    $userFeqValueArr = array();
    foreach($lines as $GeneID => $Freqency){
      $userFeqValueArr[$GeneID] = $Freqency;
      if($applyFilters && $u_file_name == $frm_filter_Fequency && $frm_filter_Fequency_value){
        if($Freqency >= $frm_filter_Fequency_value){
          array_push($FeqFiltedGeneIdArr, trim($GeneID));
        }
      }
    }
    if($u_file_name == $u_file_name){
      if($asc_desc == 'DESC'){
        arsort($userFeqValueArr);
      }else{
        asort($userFeqValueArr);
      }
      foreach($userFeqValueArr as $userFeqKey => $userFeqValue){
        if($maxScore < $userFeqValue){
          $maxScore = $userFeqValue;
        }
        array_push($userFeqIndexArr, $userFeqKey);
      }  
    }
		$userFeqsValueArr[$u_file_name] = $userFeqValueArr;
  }
  return $maxScore;
}

function create_colorArr_set(&$colorArrSets,$color=''){
  $colorArrSets['red'] = array("#ffd2d2","#ff9797","#ff6666","#ff3c3c","#fd0000","#d70000","#a80000","#840000","#590000","#2b0000");
  $colorArrSets['blue'] = array("#aaaaff","#7171ff","#5b5bff","#3737ff","#1717ff","#0000f0","#0000ce","#0000b9","#00009b","#000080");
  $colorArrSets['oliver'] = array("#d2d2a6","#c5c58b","#bbbb77","#a7a754","#9a9a4e","#8c8c46","#808040","#6f6f37","#656532","#58582c");
  //$colorArrSets['green'] = array("#88ff88","#5bff5b","#00dd00","#00b700","#00a400","#009500","#008000","#006c00","#005f00","#005500");
  $colorArrSets['purple'] = array("#e7ceff","#cd9bff","#c184ff","#a74fff","#9d3cff","#7d00fb","#6700ce","#5400a8","#3c0077","#290053");
  $colorArrSets['sienna'] = array("#ffdece","#ffb591","#ff732f","#ff6a22","#ff5706","#dd4800","#aa3700","#7d2800","#531b00","#2f1700");
  //$colorArrSets['green'] = array("#f4ffea","#d2ffd2","#b3ffb3","#88ff88","#5bff5b","#00dd00","#00b700","#00a400","#009500","#008000");  
  $colorArrSets['green'] = array("#d2ffd2","#b3ffb3","#92ef8f","#52e980","#00dd00","#00b700","#00a400","#009500","#008000","#006c00");
  if($color){
    $colorArrSets = $colorArrSets['green'];
  }
}
function get_colorArrSets($powerColorIndex, &$colorArrSet, &$im, $shared=''){
  //global $colorArrSets;
  global $sortColorArr_for_saint;
  $colorArrSets = array();
  create_colorArr_set($colorArrSets);
	$colorArrSet = array();
  $sortColorArr['Expect'] = 'red';
	$sortColorArr['Expect2'] = 'red';
	$sortColorArr['Pep_num'] = 'blue';
	$sortColorArr['Pep_num_uniqe'] = 'purple';
	$sortColorArr['Coverage'] = 'oliver';     
	$sortColorArr['Fequency'] = 'green';
  $sortColorArr['Peptide'] = 'oliver';
  $sortColorArr['Peptide'] = 'oliver';
  
  $sortColorArr['SpectralCount'] = 'red';
	$sortColorArr['Unique'] = 'purple';
  
  $sortColorArr['SAINTSCORE'] = 'oliver';         //SpecSum
	$sortColorArr['SPECSUM'] = 'red';         //SpecSum
  $sortColorArr['INTENSITYSUM'] = 'red';
  $sortColorArr['AVGP'] = 'blue';       //AvgP
  $sortColorArr['MAXSPEC'] = 'purple';       //maxP
  $sortColorArr['INTENSITY'] = 'purple';       //maxP
  $sortColorArr['MAXP'] = 'purple';
  $sortColorArr['NUMREPLICATES'] = 'oliver';
  $sortColorArr['NUMREP'] = 'oliver';
	$sortColorArr['PROJECT_FREQUENCY'] = 'green';      //Frequence
  $sortColorArr['BFDR'] = 'blue';      // BFDR
  
	if($shared){
    $colorSet = 'green';
  }else{
	  $colorSet = $sortColorArr[$powerColorIndex]; //red, blue...
  }
	if(!$im){
		$colorArrSet = $colorArrSets[$colorSet];
	}else{
    foreach($colorArrSets[$colorSet] as $value){
		  $tmpColor = switch_color_format($im,$value);      
		  array_push($colorArrSet, $tmpColor);
		}
	}
}

function switch_color_format($image,$colorValue){
  $colorString = '';
  if(substr($colorValue, 0, 1) == '#'){
    $colorString = substr($colorValue, 1, 6); 
  }else{
    $colorString = $colorValue;
  }
  $r = substr($colorString, 0, 2);
  $g = substr($colorString, 2, 2);
  $b = substr($colorString, 4, 2); 
  $r = hexdec("0x{$r}");
  $g = hexdec("0x{$g}");
  $b = hexdec("0x{$b}"); 
  return $color = ImageColorAllocate($image, $r, $g, $b);
}

function print_color_bar(&$colorArrSet){
	global $biggestPowedSore,$power,$powerColorIndex,$maxScore,$theaction,$orderby;
  global $Total_Spec_index;
  global $maxScore_original;
  $SCRIPT_NAME = basename($_SERVER["PHP_SELF"]);
  
  $gap = round($maxScore_original/10);
   
	$colorBarTotalW = 250;
	$colorCellW = 23;
	$colorCellH = 40;
  $maxScoreLable = $maxScore;
  if($orderby == "Expect2"){
    if($maxScore < 0){
      $maxScoreLable = $maxScore;
      $maxScore = -1 * $maxScore;
    }else{
      $maxScoreLable = -1 * $maxScore;
    }
  }
//echo "\$maxScore=$maxScore<br>";  	
//echo "\$maxScoreLable=$maxScoreLable<br>";
	$colorRange = count($colorArrSet);
	$colorBarTotalRealW = $colorBarTotalW*0.98;
	if($colorBarTotalRealW/($colorRange+1) > $colorCellW){
		$colorCellW = round($colorBarTotalRealW/($colorRange+1)-0.5);
		$colorBarTotalRealW = $colorCellW * ($colorRange+1);
		$colorBarTotalW = $colorBarTotalRealW + 10;
	}else{
		$colorBarTotalRealW = $colorCellW * ($colorRange+1);
		$colorBarTotalW = $colorBarTotalRealW + 10;
	}
  $aa = '';
	get_colorArrSets($powerColorIndex, $colorArrSet,$aa);
?>			
    <td width=<?php echo $colorBarTotalW;?> colspan=1>
      <table align="" bgcolor='' cellspacing="0" cellpadding="0" border="0" width=<?php echo $colorBarTotalRealW;?>>
        <tr height=40>
    <?php 
      $Key = 0;
      foreach($colorArrSet as $colorCell){
				if($theaction == "showNormal"){
		?>        
          <td valign=top width=<?php echo $colorCellW;?> class=s21 bgcolor='<?php echo $colorCell;?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
      <?php }else{
      ?>
					<td valign=top width=<?php echo $colorCellW;?> class=s21 ><img src='./comparison_results_create_image.php?imageW=<?php echo $colorCellW;?>&imageH=<?php echo $colorCellH;?>&colorkey=<?php echo $Key;?>&powerColorIndex=<?php echo $powerColorIndex;?>&Total_Spec_index=<?php echo $Total_Spec_index?>' border=0></td>	
			<?php 
          $Key++;
				}
			}
		?> 
          <td valign=top width=<?php echo $colorCellW;?> class=s21>&nbsp;
    <?php if($colorArrSet && $SCRIPT_NAME == "comparison_results_table.php"){?>
          <select ID="user_maxScore" name="user_maxScore">
            <option value='<?php echo $maxScore?>' selected><?php echo ($orderby == "Expect2")?-1*$maxScore:$maxScore?>
        <?php for($z=0; $z<10; $z++){
            $Score_num = round($maxScore_original - $z*$gap);
            if($Score_num < 0) break;
        ?>
            <option value='<?php echo $Score_num?>'><?php echo ($orderby == "Expect2")?-1*$Score_num:$Score_num?>
        <?php }?>
        	</select >
    <?php }?>
          </td>         
        </tr>
        <tr>
          <?php 
            if(abs($maxScoreLable) <= 5){
              $tmpNum = 2;
            }elseif(abs($maxScoreLable) <= 10){
              $tmpNum = 1;
            }else{
              $tmpNum = 0;
            }
            foreach($colorArrSet as $olorKey => $colorCell){
              $colorNumber = round(pow(($biggestPowedSore*$olorKey/$colorRange),1/$power),$tmpNum);
              if($orderby == "Expect2") $colorNumber = -1 * $colorNumber
          ?>     
          <td valign=top width='<?php echo $colorCellW;?>'><?php echo $colorNumber?></td>
          <?php }?>
          <td valign=top width='200' nowrap><?php echo round($maxScoreLable,$tmpNum)?><?print_colorbar_lable($orderby)?></td>          
        </tr>        
      </table>
    </td>
<?php 
}

function print_colorbar_lable($orderby){
  global $passedTypeArr,$SearchEngine,$frm_color_mode;
  global $field_lable_arr;
  global $Is_geneLevel;
  $color = 'black';
  if($frm_color_mode == "shared"){
    if(isset($col_name_arr)){
      echo "% &nbsp;&nbsp;<font color=$color><b>Shared Preys</b>";
    }else{
      echo "% &nbsp;&nbsp;<font color=$color><b>Shared Hits</b>";
    }  
  }elseif(strstr($SearchEngine, 'TPP_') && $orderby == 'Expect'){
    echo " &nbsp;&nbsp;<font color=$color><b>TPP Probability</b>";  
  }elseif($orderby == 'Expect'){
    if($SearchEngine == "SEQUEST"){
      echo " &nbsp;&nbsp;<font color=$color><b>SEQUEST Score</b>";
    }else{
      echo " &nbsp;&nbsp;<font color=$color><b>Mascot Score</b>";
    }  
  }elseif($orderby == 'Spectral Count'){
    echo " &nbsp;&nbsp;<font color=$color><b>Spectral Count</b>";
  }elseif($orderby == 'Expect2'){
    echo " &nbsp;&nbsp;<font color=$color><b>GPM Expect</b>";
  }elseif($SearchEngine == 'SEQUEST' && $orderby == 'Expect'){
    echo " &nbsp;&nbsp;<font color=$color><b>SEQUEST Score</b>";    
  }elseif($orderby == 'Pep_num'){
    echo " &nbsp;&nbsp;<font color=$color><b>Total Peptide Number</b>";
  }elseif($orderby == 'Pep_num_uniqe'){
    echo " &nbsp;&nbsp;<font color=$color><b>Uniqe Peptide Number</b>";
  }elseif($orderby == 'Coverage'){
    echo "% &nbsp;&nbsp;<font color=$color><b>Coverage</b>";
  }elseif($orderby == 'Fequency'){
    echo "% &nbsp;&nbsp;<font color=$color><b>Project Frequency</b>";
  }elseif(isset($passedTypeArr) && in_array($orderby, $passedTypeArr)){
    echo "% &nbsp;&nbsp;<font color=$color><b>$orderby Frequency</b>";
  }elseif($orderby == 'Init_Prob'){
    echo "% &nbsp;&nbsp;<font color=$color><b>Initial Probability</b>";
  }elseif($orderby == 'Protein_Expect'){
    if(strstr($SearchEngine, 'Mascot')){
      $tmpLable = 'Peptide Score';
    }elseif(strstr($SearchEngine, 'GPM')){
      $tmpLable = 'Peptide Expect';
    }elseif(strstr($SearchEngine, 'SEQUEST')){
      $tmpLable = 'SEQUEST Score';
    }
    echo "&nbsp;&nbsp;<font color=$color><b>$tmpLable</b>";
  }else{
    if(isset($field_lable_arr)){
      if($orderby == 'PROJECT_FREQUENCY'){
        echo "% &nbsp;&nbsp;<font color=$color><b>".$field_lable_arr[$orderby]."</b>";
      }else{
        echo " &nbsp;&nbsp;<font color=$color><b>".$field_lable_arr[$orderby]."</b>";
      }  
    }
  }
} 

function color_num($score,&$colorIndex){
  global $biggestPowedSore,$power,$colorArrSet;
  $colorRange = count($colorArrSet);
  $powedSore = pow($score,$power);
  $tmp_diff = $biggestPowedSore - 0.5;
  if($tmp_diff <= 0) $tmp_diff = $biggestPowedSore;
  if($tmp_diff == 0) $tmp_diff = 1;
  $colorIndex = round($colorRange * $powedSore / $tmp_diff);
  $colorIndex = intval($colorIndex);                      
  if($colorIndex >= 10) $colorIndex = 9;
  if($colorIndex <= 0) $colorIndex = 0;
  return $colorArrSet[$colorIndex];
} 
//function get_all_baits_geneID_str($inStr){
function get_all_item_geneID_arr($inStr){
  global $HITSDB,$currentType;
  $itemIDstr = '';
  $geneIDarr = array();
  $tmpArr_1 = explode(";",$inStr);
  foreach($tmpArr_1 as $tmpVal_1){
    $tmpArr_2 = explode(":",$tmpVal_1);
    if($itemIDstr) $itemIDstr .= ",";
    $itemIDstr .= $tmpArr_2[1];
  }
  if(!$itemIDstr) return '';
  if($currentType == 'Bait'){
    $SQL = "SELECT ID, GeneID FROM Bait WHERE ID IN($itemIDstr)";
  }elseif($currentType == 'Exp'){
    $SQL = "SELECT E.ID, B.GeneID FROM Experiment E LEFT JOIN Bait B ON B.ID=E.BaitID WHERE E.ID IN($itemIDstr)";
  }elseif($currentType == 'Band'){
    $SQL = "SELECT D.ID, B.GeneID FROM Band D LEFT JOIN Bait B ON B.ID=D.BaitID WHERE D.ID IN($itemIDstr)";
  }else{
    return '';
  }
  $itemArr = $HITSDB->fetchAll($SQL);
  foreach($itemArr as $itemVal){
    if($itemVal['GeneID'] < 0){
      $geneIDarr[$itemVal['ID']] = 0;
    }else{  
      $geneIDarr[$itemVal['ID']] = $itemVal['GeneID'];
    }  
  }
  return $geneIDarr;
}

function get_fequency(&$FeqIndexArr, &$FeqValueArr,&$FeqFiltedGeneIdArr,$frequencyFileName){
  global $HITSDB, $asc_desc, $AccessProjectID,$allBaitgeneIDarr,$A,$applyFilters;
  global $frm_filter_Fequency,$frm_filter_Fequency_value;
	$maxScore = 0;
  $temFeq = array();
  get_frequency_arr($temFeq,$frequencyFileName);
  if($asc_desc =='ASC'){
    asort($temFeq);
  }
  foreach($temFeq as $tmpKey => $temFeqValue){//--$tmpKey=GeneID --$temFeqValue=Frequency value
    if($applyFilters && $frm_filter_Fequency == 'Fequency' && $temFeqValue >= $frm_filter_Fequency_value){
      array_push($FeqFiltedGeneIdArr, $tmpKey);
    }
    array_push($FeqIndexArr, $tmpKey);
		$fequency = $temFeqValue;
    $FeqValueArr[$tmpKey] = $fequency;
		if($maxScore < $fequency){
      $maxScore = $fequency;
    }  
  }
  return $maxScore;
}

function SearchEngine_WHERE_OR_($SearchEngine){
  global $SearchEngineConfig_arr;
  $SearchEngine = str_replace("TPP_", "", $SearchEngine);          
  $WHERE = "";        
  foreach($SearchEngineConfig_arr as $val){
    if(strstr($SearchEngine, $val)){
      $uploaded_engine = $val.'Uploaded';        
      $WHERE = " WHERE (SearchEngine='$val' OR SearchEngine='$uploaded_engine') AND ";
    }      
  }
  if(!$WHERE) $WHERE = " WHERE ";
  return $WHERE;
}
?>