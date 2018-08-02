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

$hitsGeneIDarr = array();
$grid_baitGeneIDarr = array();
$grid_bait_hits_arr = array();
$grid_hits_gene_arr = array();
$group_geneID_map_arr = array();
$item_ID_name_map_arr = array();
$matchedHitGeneIDarr = array();

if($php_file_name != "comparison_results_image.php"){
  if(!is_dir("../TMP/comparison/")) mkdir("../TMP/comparison/");
  if(!is_dir("../TMP/comparison/P_$AccessProjectID/")) mkdir("../TMP/comparison/P_$AccessProjectID/");
  $subDir = "../TMP/comparison/P_$AccessProjectID/";
  $bio_grid_info_file_name = $subDir.$AccessUserID."_bio_grid_info.txt";
  if($php_file_name == "item_report"){
    if($type == 'Bait' || $type == 'Sample' || $type == 'Experiment'){
      array_push($grid_baitGeneIDarr, $level_1_arr['GeneID']);
      $grid_bait_hits_arr[$level_1_arr['GeneID']] =  array();
    }else{
      foreach($level_2_arr as $level_2_val){
        if(!in_array($level_2_val['GeneID'], $grid_baitGeneIDarr)){ 
          array_push($grid_baitGeneIDarr, $level_2_val['GeneID']);
          $grid_bait_hits_arr[$level_2_val['GeneID']] =  array();
        }  
      }
    }
  }elseif($php_file_name == "comparison_results_table"){
    foreach($groupArr as $groupKey => $groupVal){
      $tmpGroupArr = explode(',',$groupVal['inStr']);
      $tmpArr1 = array();
      foreach($tmpGroupArr as $tmpVal){
        array_push($tmpArr1, $allBaitgeneIDarr[$tmpVal]);
      }
      $group_geneID_map_arr[$groupKey] = $tmpArr1;
      $item_ID_name_map_arr[$groupKey] = str_replace(",", "|", $groupVal['simpleInfo']);
    }
        
    foreach($itemLableArr as $tmpKey => $tmpVal){
      if(is_numeric($tmpKey)){
        $item_ID_name_map_arr[$tmpKey] = $tmpKey." ".$tmpVal;
      }  
    }
    $grid_baitGeneIDarr = $allBaitgeneIDarr;
    foreach($grid_baitGeneIDarr as $grid_baitGeneIDval){
      $grid_bait_hits_arr[$grid_baitGeneIDval] = array();
    }
  }elseif($php_file_name == "SAINT_comparison_results_table"){
    
    $grid_baitGeneIDarr = $bait_gene_id_arr;
    foreach($grid_baitGeneIDarr as $grid_baitGeneIDval){
      $grid_bait_hits_arr[$grid_baitGeneIDval] = array();
    }
  }
  
  $baitGeneIDstr = implode("|", $grid_baitGeneIDarr);
  $bio_checked_arr = array();
  if(isset($frm_biogrid_pHTP) && $frm_biogrid_pHTP){
    array_push($bio_checked_arr, "PH");
  }else{
    $frm_biogrid_pHTP = 0;
  }
  if(isset($frm_biogrid_pNONHTP) && $frm_biogrid_pNONHTP){
    array_push($bio_checked_arr, "PN");
  }else{
    $frm_biogrid_pNONHTP = 0;
  }
  if(isset($frm_biogrid_gHTP) && $frm_biogrid_gHTP){
    array_push($bio_checked_arr, "GH");
  }else{
    $frm_biogrid_gHTP = 0;
  }
  if(isset($frm_biogrid_gNONHTP) && $frm_biogrid_gNONHTP){
    array_push($bio_checked_arr, "GN");
  }else{
    $frm_biogrid_gNONHTP = 0;
  }
  $bio_checked_str = '';
  $no_grid_data = 0;
  
  $_SESSION["bio_checked_arr"] = $bio_checked_arr;
  
  if($bio_checked_arr){
    if($php_file_name == "comparison_results_table" && $theaction == "showImage"){
      $bio_grid_info_handle = fopen($bio_grid_info_file_name, "w");
      if(!$bio_grid_info_handle){
        echo "Cannot open file $reportFileName";
      }
      $file_line = "baitGeneIDstr=$baitGeneIDstr\r\n";
      fwrite($bio_grid_info_handle, $file_line);
      
      $file_line = "group_geneID_map_arr=";
      $tmp_line = '';
      foreach($group_geneID_map_arr as $group_key => $group_val){
        $tmp_group_str = $group_key.';;'.implode(",,", $group_val);
        if($tmp_line) $tmp_line .= "@@";
        $tmp_line .= $tmp_group_str;
      }
      $file_line .= $tmp_line;
      fwrite($bio_grid_info_handle, $file_line."\r\n");
      
      $file_line = "grid_bait_hits_arr=";
      $tmp_line = '';
      foreach($grid_bait_hits_arr as $tmp_key => $tmp_val){
        if($tmp_line) $tmp_line .= "@@";
        $tmp_line .= $tmp_key;
      }
      $file_line .= $tmp_line;
      fwrite($bio_grid_info_handle, $file_line."\r\n");
      
      $file_line = "allBaitgeneIDarr=";
      $tmp_line = '';
      foreach($allBaitgeneIDarr as $tmp_key => $tmp_val){
        if($tmp_line) $tmp_line .= "@@";
        $tmp_line .= $tmp_key . ";;" . $tmp_val;
      }
      $file_line .= $tmp_line;
      fwrite($bio_grid_info_handle, $file_line."\r\n");
      $file_line = "itemIdIndexArr=";
      $tmp_line = implode(",", $itemIdIndexArr);
      $file_line .= $tmp_line;
      fwrite($bio_grid_info_handle, $file_line."\r\n");
      
      fclose($bio_grid_info_handle);
    }else{    
      get_grid_baitGene_to_hitsGene_arr($baitGeneIDstr,$grid_bait_hits_arr,$grid_hits_gene_arr);
      if(!$grid_hits_gene_arr) $no_grid_data = 1;
    }
    $bio_checked_str = implode(",", $bio_checked_arr); 
  }
?>
<input type=hidden name=bio_checked_str value='<?php echo $bio_checked_str?>'>
<?php 
  if($theaction != "popWindow"){  
?>
  <DIV ID="filter_biogride" style="display:block">
    <table border=0 cellspacing="1" cellpadding="1" width=100% bgcolor="white">
    <tr>
    	<td colspan=2><div class=maintext><b><img src='./images/icon_bioGrid.gif' border=0> BioGRID overlap</b></td>
      <td colspan=2><div class=maintext>
    <?php  
      if($php_file_name == "item_report" && $bio_checked_arr){
        $theFile = "./pop_unMatched_bioGrid.php?level1_matched_file=$tmp_file";
    ?>   
        [<a href="javascript: popwin('<?php echo $theFile?>',420,500)">BioGRID interactions not found here</a>]
    <?php }?>
      </td>
    </tr>
    <tr>
    	<td width='25%' bgcolor="<?php echo $tr_bgcolor;?>"><div class=<?php echo $filerLable_css?>>&nbsp;
      <input type=checkbox name='frm_biogrid_pHTP' value='1' <?php echo ($frm_biogrid_pHTP)?"checked":"";?>>
      Physical HTP <img src='./images/icon_pHTP.gif'></div></td>
    	<td width='25%' bgcolor="<?php echo $tr_bgcolor;?>"><div class=<?php echo $filerLable_css?>>&nbsp;
      <input type=checkbox name='frm_biogrid_pNONHTP' value='1' <?php echo ($frm_biogrid_pNONHTP)?"checked":"";?>>
      Physical NON-HTP <img src='./images/icon_pNONHTP.gif'></div></td>
    	<td width='25%' bgcolor="<?php echo $tr_bgcolor;?>"><div class=<?php echo $filerLable_css?>>&nbsp;
      <input type=checkbox name='frm_biogrid_gHTP' value='1' <?php echo ($frm_biogrid_gHTP)?"checked":"";?>>
      Genetic HTP <img src='./images/icon_gHTP.gif'></div></td>
    	<td width='25%' bgcolor="<?php echo $tr_bgcolor;?>"><div class=<?php echo $filerLable_css?>>&nbsp;
      <input type=checkbox name='frm_biogrid_gNONHTP' value='1' <?php echo ($frm_biogrid_gNONHTP)?"checked":"";?>>
      Genetic NON-HTP <img src='./images/icon_gNONHTP.gif'></div></td>
    </tr>
    </table>
  </DIV>
<?php 
  }
}

function get_grid_baitGene_to_hitsGene_arr($baitGeneIDstr,&$grid_bait_hits_arr,&$grid_hits_gene_arr){
  global $bio_checked_arr,$group_geneID_map_arr;  
  $gride_reponse_arr = array();
  $tmp_arr = explode("|", $baitGeneIDstr);
  $baitGeneIDstr = '';
  foreach($tmp_arr as $tmp_val){
    if($tmp_val <= 0)  continue;
    if($baitGeneIDstr) $baitGeneIDstr .= "|";
    $baitGeneIDstr .= $tmp_val;
  }
  get_bioGride_response($baitGeneIDstr, $gride_reponse_arr);
/*echo "<pre>";  
print_r($gride_reponse_arr);  
echo "</pre>"; 
*/
  if(!$gride_reponse_arr){
    echo "cannot get bioGrid file from " . BIOGRID_URL;
  }else{
    foreach($gride_reponse_arr as $buffer){
      $buffer = trim($buffer);
      if(!$buffer) continue;
      $tmpGeneArr = explode("\t",$buffer);
      if(count($tmpGeneArr) != 4){
        continue;
      }
      if($tmpGeneArr[1] == $tmpGeneArr[0]) continue; 
      $gridType = substr($tmpGeneArr[2], 0, 1).substr($tmpGeneArr[3], 0, 1);
      if($bio_checked_arr){
        if(!in_array($gridType, $bio_checked_arr)) continue;
      }
      if(array_key_exists($tmpGeneArr[0], $grid_bait_hits_arr)){
        if(!isset($grid_bait_hits_arr[$tmpGeneArr[0]][$tmpGeneArr[1]])){
          $grid_bait_hits_arr[$tmpGeneArr[0]][$tmpGeneArr[1]] = array();
          array_push($grid_bait_hits_arr[$tmpGeneArr[0]][$tmpGeneArr[1]], $gridType);
        }else{
          if(!in_array($gridType, $grid_bait_hits_arr[$tmpGeneArr[0]][$tmpGeneArr[1]])){
            array_push($grid_bait_hits_arr[$tmpGeneArr[0]][$tmpGeneArr[1]], $gridType);
          }  
        }
        if(!in_array($tmpGeneArr[1], $grid_hits_gene_arr)){
          array_push($grid_hits_gene_arr, $tmpGeneArr[1]);
        }
      }
      if(array_key_exists($tmpGeneArr[1], $grid_bait_hits_arr)){
        if(!isset($grid_bait_hits_arr[$tmpGeneArr[1]][$tmpGeneArr[0]])){
          $grid_bait_hits_arr[$tmpGeneArr[1]][$tmpGeneArr[0]] = array();
          array_push($grid_bait_hits_arr[$tmpGeneArr[1]][$tmpGeneArr[0]], $gridType);
        }else{
          if(!in_array($gridType, $grid_bait_hits_arr[$tmpGeneArr[1]][$tmpGeneArr[0]])){
            array_push($grid_bait_hits_arr[$tmpGeneArr[1]][$tmpGeneArr[0]], $gridType);
          }
        }
        if(!in_array($tmpGeneArr[0], $grid_hits_gene_arr)){
          array_push($grid_hits_gene_arr, $tmpGeneArr[0]);
        }
      }
    }
  }
  if($group_geneID_map_arr){
    foreach($group_geneID_map_arr as $groupKey => $baitGeneArr){
      $tmpGroupArr = array();
      foreach($baitGeneArr as $baitGeneID){
        $tmpGridBaitArr = $grid_bait_hits_arr[$baitGeneID];        
        foreach($tmpGridBaitArr as $tmpGridhitKey => $tmpGridhitArr){
          if(!array_key_exists($tmpGridhitKey, $tmpGroupArr)){
            $tmpGroupArr[$tmpGridhitKey] = $tmpGridhitArr;
          }else{
            foreach($tmpGridhitArr as $gridHitType){
              if(!in_array($gridHitType,$tmpGroupArr[$tmpGridhitKey])){
                array_push($tmpGroupArr[$tmpGridhitKey], $gridHitType);
              }
            }
          }
        }
      }
      $grid_bait_hits_arr[$groupKey] = $tmpGroupArr;
    }
  }
}
?>