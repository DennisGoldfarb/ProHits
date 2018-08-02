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

  if($Bait_ID) $BaitID = $Bait_ID;
  if(!isset($sub_grid_bait_hits_arr)) $sub_grid_bait_hits_arr = array();
  if(!$hitType) $hitType = 'normal';
  
  $tpp_cgi = "http://" .$tpp_ip. TPP_CGI_DIR;
//--------------------------------------------------------------------------
  $gi_acc_arr = array();
  if($hitType != 'TPPpep' && $hitType != 'geneLevel'){
    $gi_acc_arr = get_gi_acc_arr($hits_result,$hitType,$proteinDB);
  }
//--------------------------------------------------------------------------
  mysqli_data_seek($hits_result, 0); 
  $geneID_Name_array = array();
  while($hitsValue_tmp = mysqli_fetch_assoc($hits_result)){
    $geneID_arr_tmp = explode(',',$hitsValue_tmp['GeneID']);
    foreach($geneID_arr_tmp as $geneID_arr_tmp_val){
      $geneID_arr_tmp_val = trim($geneID_arr_tmp_val);
      if(!$geneID_arr_tmp_val) continue;
      if(array_key_exists($geneID_arr_tmp_val, $geneID_Name_array)) continue;
      
      $geneType = get_Gene_ID_type($geneID_arr_tmp_val);          
      if($geneType == 'ENS'){
        $SQL = "SELECT GeneName FROM Protein_ClassENS WHERE  ENSG='".$geneID_arr_tmp_val."'";
      }else{
        $SQL = "SELECT GeneName FROM Protein_Class WHERE  EntrezGeneID='".$geneID_arr_tmp_val."'";
      }
      $tmp_gene_arr = $proteinDB->fetch($SQL);
      if($tmp_gene_arr){
        $geneID_Name_array[$geneID_arr_tmp_val] = $tmp_gene_arr['GeneName'];
      }else{
        $geneID_Name_array[$geneID_arr_tmp_val] = '';
      }
    }   
    for($k=0; $k<count($geneID_arr_tmp);$k++){
      $tmpHitNotes = array();
      $HitGeneName = '';
      $LocusTag = '';
      $hitsValue = $hitsValue_tmp;
      $hitsValue['GeneID'] = trim($geneID_arr_tmp[$k]);
      $Redundant_str = '';
      if(count($geneID_arr_tmp) > 1){
        foreach($geneID_arr_tmp as $geneID_val_tmp){
          if(trim($geneID_val_tmp) == $hitsValue['GeneID']) continue;
          if($Redundant_str) $Redundant_str .= ",";
          $Redundant_str .= $geneID_val_tmp;
        }
      }
      $hitsValue['Redundant'] = $Redundant_str;  
      get_expFilter_arr($hitsValue);//=====changed=====02/01/2010======     
      if(array_key_exists($hitsValue['GeneID'], $geneID_Name_array)){
        $hitsValue['GeneName'] = $geneID_Name_array[$hitsValue['GeneID']];
      }else{
        $hitsValue['GeneName'] = '';
      }    
      if($hitType == 'TPPpep'){
        $tmpIonsArr = explode('/',$hitsValue['Ions']);
        $hitsIons = round($tmpIonsArr[0]/$tmpIonsArr[1]*100,2);
        if(isset($frm_PBT) && $frm_PBT !='' && $hitsValue['Probability'] < $frm_PBT) array_push($tmpHitNotes, 'PBT');
        if(isset($frm_HSR) && $frm_HSR !='' && $hitsValue['Score1'] < $frm_HSR) array_push($tmpHitNotes, 'HSR');
        if(isset($frm_ION) && $frm_ION !='' && $hitsIons < $frm_ION) array_push($tmpHitNotes, 'ION');
        if(isset($frm_CH1) && $frm_CH1 !='' && $hitsValue['Charge'] == $frm_CH1) array_push($tmpHitNotes, 'CH1');
        if(isset($frm_CH2) && $frm_CH2 !='' && $hitsValue['Charge'] == $frm_CH2) array_push($tmpHitNotes, 'CH2');
        if(isset($frm_CH3) && $frm_CH3 !='' && $hitsValue['Charge'] == $frm_CH3) array_push($tmpHitNotes, 'CH3');
      }
      //---------------------------------------------------------------------
      $HitFrequency = 0;
      if($hitType != 'TPPpep' && array_key_exists(trim($hitsValue['GeneID']), $frequencyArr) && trim($hitsValue['GeneID'])){
        $HitFrequency = $frequencyArr[trim($hitsValue['GeneID'])];
      }
      $tmpbgcolor = $bgcolor;
      $tmptextfont = "maintext";
      $rc_excluded = 0;
  
    	if(((!isset($frm_BT) || !$frm_BT) and trim($hitsValue['GeneID']) and $BaitArr['GeneID'] == trim($hitsValue['GeneID'])) && $hitType != 'TPPpep'){
    	  $rc_excluded = 0;
    	}else if($theaction == 'exclusion' && !in_array(ID_REINCLUDE, $tmpHitNotes)){
        if($hitType == 'TPP'){
          $tmp_peptide = 0;
          if($frm_PT_check){
            if($frm_filter_Peptide == 'Pep_num' && $hitsValue['TOTAL_NUMBER_PEPTIDES'] <= $frm_filter_Peptide_value){
              $tmp_peptide = 1;
            }elseif($frm_filter_Peptide == 'Pep_num_uniqe' && $hitsValue['UNIQUE_NUMBER_PEPTIDES'] <= $frm_filter_Peptide_value){
              $tmp_peptide = 1;
            }
          }
          if((isset($frm_Expect_check) && $frm_Expect_check && $hitsValue['PROBABILITY'] < $frm_filter_Expect) ||
            (isset($frm_Cov_check) && $frm_Cov_check && $hitsValue['PERCENT_COVERAGE'] < $frm_filter_Coverage) || 
            $tmp_peptide ||
            (isset($frm_Frequency) && $frm_Frequency && $HitFrequency > $frequencyLimit)){
            $rc_excluded=1;
          }
        }elseif($hitType == 'geneLevel'){
          $tmp_peptide = 0;
          if($frm_PT_check){
            if($hitsValue['Unique'] <= $frm_filter_Peptide_value){
              $tmp_peptide = 1;
            }
          }
          if((isset($frm_Expect_check) && $frm_Expect_check && $hitsValue['SpectralCount'] < $frm_filter_Expect) || $tmp_peptide ||
            (isset($frm_Frequency) && $frm_Frequency && $HitFrequency > $frequencyLimit)){
            $rc_excluded=1;
          }
        }elseif($hitType == 'TPPpep'){
          if((isset($frm_PBT) && $frm_PBT !='' && $hitsValue['Probability'] < $frm_PBT) ||
            (isset($frm_HSR) && $frm_HSR !='' && $hitsValue['Score1'] < $frm_HSR) ||
            (isset($frm_ION) && $frm_ION !='' && $hitsIons < $frm_ION) ||
            (isset($frm_CH1) && $frm_CH1 !='' && $hitsValue['Charge'] == $frm_CH1) ||
            (isset($frm_CH2) && $frm_CH2 !='' && $hitsValue['Charge'] == $frm_CH2) ||
            (isset($frm_CH3) && $frm_CH3 !='' && $hitsValue['Charge'] == $frm_CH3)){
            $rc_excluded=1;
          }
        }else{
          $tmp_peptide = 0;
          if($frm_PT_check){
            if($frm_filter_Peptide == 'Pep_num' && $hitsValue['Pep_num'] <= $frm_filter_Peptide_value){
              $tmp_peptide = 1;
            }elseif($frm_filter_Peptide == 'Pep_num_uniqe' && $hitsValue['Pep_num_uniqe'] <= $frm_filter_Peptide_value){
              $tmp_peptide = 1;
            }
          }
      	  if(($searchEngineField == 'Mascot' && $frm_Expect_check && $hitsValue['Expect'] && $hitsValue['Expect'] <= $frm_filter_Expect) ||
            ($searchEngineField == 'SEQUEST' && $frm_Expect_check && $hitsValue['Expect'] && $hitsValue['Expect'] <= $frm_filter_Expect) ||
            ($searchEngineField == 'GPM' && $frm_Expect_check && $hitsValue['Expect2'] && $hitsValue['Expect2'] >= $frm_filter_Expect) ||
            ($frm_Cov_check && $hitsValue['Coverage'] && $hitsValue['Coverage'] <= $frm_filter_Coverage) || $tmp_peptide ||
            (isset($frm_Frequency) && $frm_Frequency && $HitFrequency > $frequencyLimit)){
            $rc_excluded=1;
          }
        }
        if(count($tmpHitNotes) && !$rc_excluded){
          foreach($typeBioArr as $Value) {
         		$frmName = 'frm_' . $Value['Alias'];
        		if($$frmName and in_array($Value['Alias'] ,$tmpHitNotes)){
        			$rc_excluded=1;
        			break;
        		}
        	}
    		  if(!$rc_excluded && $hitType == 'normal'){
            foreach($typeExpArr as $Value) {
         		  $frmName = 'frm_' . $Value['Alias'];
        		  if($$frmName and in_array($Value['Alias'] ,$tmpHitNotes)){
        			  $rc_excluded=1;
        			  break;
              }
        		}
    	    }
          if(!$rc_excluded && in_array(ID_MANUALEXCLUSION, $tmpHitNotes)) $rc_excluded=1; //-----???????
        }
      }
  
      if(!$rc_excluded and $theaction != 'exclusion' && !in_array(ID_REINCLUDE,$tmpHitNotes)){
        $isTrue = 0;
      	foreach($typeBioArr as $Value){
      		//if(in_array($Value['Alias'] ,$tmpHitNotes) && $Value['Alias'] != "HP"){
          if(in_array($Value['Alias'], $tmpHitNotes)){
      			$isTrue = 1;
      			break;
      		}
      	}
        if(!$isTrue){
          foreach($typeExpArr as $Value) {
        		if(in_array($Value['Alias'], $tmpHitNotes)){
        			$isTrue = 1;
        			break;
        		}
        	}
        }
        if(!$isTrue){
          if($hitType == 'normal'){
       	    if($HitFrequency > $frequencyLimit || ($hitsValue['Expect'] and $hitsValue['Expect'] <= DEFAULT_EXPECT_EXCLUSION )){
      		    $isTrue = 1;
      	    }
          }elseif($hitType == 'TPP' || $hitType == 'geneLevel'){
            if($HitFrequency > $frequencyLimit){
              $isTrue = 1;
            }
          }
        }
        if($isTrue){
          $tmpbgcolor = $excludecolor;
    	    $tmptextfont = "excludetext";
        }
      }
      if(!$rc_excluded){
        if(!$subHeadFlag){
          print_table_sub_head($arr2_value);
          $subHeadFlag = 1;
        }
        $tmpCounter++;                      
        if($hitType == 'TPP'){
          if(array_key_exists($hitsValue['ProteinAcc'], $gi_acc_arr)){
            $tmp_gi = $hitsValue['ProteinAcc'];
            $hitsValue['ProteinAcc'] = $gi_acc_arr[$tmp_gi]['Acc_V'];
            $hitsValue['Acc'] = $gi_acc_arr[$tmp_gi]['Acc'];
          }else{
            $hitsValue['Acc'] = $hitsValue['ProteinAcc'];
          }
          if(isset($giArr) && $hitsValue['ProteinAcc'] && (trim($hitsValue['GeneID']) || $hitsValue['GeneName'])){
            $hits_atr_str = $BaitID.",".$arr2_value['ID'].",".$arr2_value['Location'].",".$hitsValue['ProteinAcc'].",".$hitsValue['PROBABILITY'].",".$hitsValue['UNIQUE_NUMBER_PEPTIDES'];
            $tmpPkey = trim($hitsValue['GeneID']).",".$hitsValue['GeneName'];
            if(!array_key_exists($tmpPkey, $giArr)){
              $giArr[$tmpPkey] = array();
              array_push($giArr[$tmpPkey], $hits_atr_str);
            }else{
              array_push($giArr[$tmpPkey], $hits_atr_str);
            }
          }
          if($HitFrequency){
            $HitFrequencyPercent = $HitFrequency."%";
          }else{
            $HitFrequencyPercent = "0%";
          }
          $Description = $hitsValue['ProteinDec'] = str_replace(",", ";", $hitsValue['ProteinDec']);
          $Description = $hitsValue['ProteinDec'] =str_replace("\n", "", $Description);
          $hitsValue['GeneName'] = str_replace(",", ";", $hitsValue['GeneName']);
          $hitsValue['LocusTag'] = str_replace(",", ";", $hitsValue['LocusTag']);
          $hitsValue['ProteinAcc'] = str_replace(",", ";", $hitsValue['ProteinAcc']);
          $hitsValue['Frequency'] = $HitFrequencyPercent;
          $filterString = '';
          str_replace("gi","<br>gi", $hitsValue['INDISTINGUISHABLE_PROTEIN']);
      ?>
        <tr  bgcolor='<?php echo $tmpbgcolor;?>' onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $tmpbgcolor;?>');">
          <td align="center" bgcolor=<?php 
          if(trim($hitsValue['GeneID']) && ($BaitArr['GeneID'] == trim($hitsValue['GeneID']))){
        	  echo "'$item_color'";
            for($m=0; $m<count($typeBioArr); $m++){
              if($typeBioArr[$m]['Alias'] == ID_BAIT){
          				$typeBioArr[$m]['Counter']++;
          		}
            }
        	}else{
        	  echo "'$tmpbgcolor'";
          }
          if(isset($hitsGeneIDarr)){
            array_push($hitsGeneIDarr, trim($hitsValue['GeneID']));
          }
      	 ?>
          ><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['ID'];?>&nbsp;
            </div>
          </td>
          <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
              <?php echo  $hitsValue['ProteinAcc'];?>&nbsp;
              </div>
          </td>
          <td align="center" <?php echo  ($hitsValue['GeneID'] || $hitsValue['GeneName'])?"class='gi".$hitsValue['GeneID']."'" : "";?>><a href="javascript: href_show_hand();" onmouseover="showSameGene(event,'<?php echo $hitsValue['GeneID']?>');" onmouseout="hideSameGene('<?php echo $hitsValue['GeneID']?>');" class=button><div class=maintext>
              <?php 
              if($hitsValue['GeneID'] || $hitsValue['GeneName']){
                
                echo  $hitsValue['GeneID']." / ".$hitsValue['GeneName'];
              }
              ?>&nbsp;
              </div></a>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php 
              echo $hitsValue['PROBABILITY'];
              ?>&nbsp;
            </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['PCT_SPECTRUM_IDS'];?>&nbsp;
              </div>
          </td>
          <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
              <?php echo $HitFrequencyPercent;?>&nbsp;
              </div>
          </td>
          <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
              <?php 
              $Redundant_str = convert_Redundant($hitsValue['INDISTINGUISHABLE_PROTEIN'],$gi_acc_arr);
              echo $Redundant_str;              
              //echo str_replace("gi","<br>gi", $hitsValue['INDISTINGUISHABLE_PROTEIN']);              
              ?>&nbsp;
              </div>
          </td>
          <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['ProteinDec'];?>&nbsp;
            </div>
          </td>
          <td width="" align="right" ><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['TOTAL_NUMBER_PEPTIDES'];?>&nbsp;</div>
          </td>
          <td width="" align="right" ><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['UNIQUE_NUMBER_PEPTIDES'];?>&nbsp;</div>
          </td>
          <td width="" align="right" ><div class=<?php echo $tmptextfont;?>>
              <?php echo (($Coverage=$hitsValue['PERCENT_COVERAGE'])>0)?"$Coverage%":"";?>&nbsp;</div>
          </td>
          <td width="" align="center" nowrap><div class=<?php echo $tmptextfont;?>>
          <?php 
          $urlLocusTag = $hitsValue['LocusTag'];
          $urlGeneID = $hitsValue['GeneID'];
          $urlGI = $hitsValue['ProteinAcc'];
          echo get_URL_str($hitsValue['ProteinAcc'], $hitsValue['GeneID'], $hitsValue['LocusTag']);
          ?>
          </div>
          </td>
          <td>
            <table border=0 cellpadding="1" cellspacing="1"><tr>
          <?php 
  
          $counter = count($typeBioArr);
          for($m=0; $m<$counter; $m++){
            if(($typeBioArr[$m]['Alias'] != ID_BAIT) && in_array($typeBioArr[$m]['Alias'] ,$tmpHitNotes)){
        			$tmp_color = $typeBioArr[$m]['Color'];
        			echo "<td bgcolor=$tmp_color nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
              ($filterString)? $filterString.=";".$typeBioArr[$m]['Name'] : $filterString.=$typeBioArr[$m]['Name'];
        			$typeBioArr[$m]['Counter']++;
        		}
          }
  
          $counter = count($typeExpArr);
          for($m=0; $m<$counter; $m++){
            if(($typeExpArr[$m]['Alias'] != ID_BAIT) && in_array($typeExpArr[$m]['Alias'] ,$tmpHitNotes)){
        			$tmp_color = $typeExpArr[$m]['Color'];
        			echo "<td bgcolor=$tmp_color nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
              ($filterString)? $filterString.=";".$typeExpArr[$m]['Name'] : $filterString.=$typeExpArr[$m]['Name'];
        			$typeExpArr[$m]['Counter']++;
        		}
          }
          if($typeFrequencyArr && $HitFrequency > $frequencyLimit and !$is_reincluded){
            $typeFrequencyArr['Counter']++;
             echo "<td bgcolor='" . $typeFrequencyArr['Color'] . "' nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
             ($filterString)? $filterString.=";frequency>$frequencyLimit%" : $filterString.="frequence>$frequencyLimit%";
          }
          if(in_array(ID_REINCLUDE,$tmpHitNotes)) {  //reinclude
              echo "<td bgcolor=#660000><font face='Arial' color=white size=-1><b>R</b></font></td>";
              ($filterString)? $filterString.=";reinclude" : $filterString.="reinclude";
          }
          if(in_array(ID_MANUALEXCLUSION,$tmpHitNotes)) {  //manual exclude
              echo "<td bgcolor=black><font face='Arial' color=yellow size=-1><b>X</b></font></td>";
              ($filterString)? $filterString.=";manualexclusion" : $filterString.="manualexclusion";
          }
          if($hitsValue['GeneID'] && array_key_exists($hitsValue['GeneID'], $sub_grid_bait_hits_arr)){
            if(!in_array($hitsValue['GeneID'],$matched_hits_geneID_arr)){
              $matched_hits_geneID_arr[$hitsValue['GeneName']] = $hitsValue['GeneID'];
            }
            $typeStr = '';
            echo get_bioGrid_icon($sub_grid_bait_hits_arr[$hitsValue['GeneID']],$typeStr);
            $tmpEageKey = $BaitID." ".str_replace(",", ";",$BaitGeneName)."??".str_replace(",", ";",$hitsValue['GeneName']);
            if(!array_key_exists($tmpEageKey, $EdgeArr_matched)){
              $EdgeArr_matched[$tmpEageKey] = $typeStr.",".$hitsValue['GeneID'];
            }
          }else{
            echo "<td ><div class=maintext>&nbsp; &nbsp;</div></td>";
          }
          $hitsValue['Filters'] = $filterString;
          ?>
            </tr></table>
          </td>
          <td width="" align="left" nowrap><div class=maintext>&nbsp; &nbsp; &nbsp;
          <?php if(!$BaitArr['GelFree']){?>
            <a href="javascript: view_gel(<?php echo $arr2_value['ID'];?>);"><img border=0 src="./images/icon_picture.gif" alt='gel image'></a>
          <?php }?>
            <a href="javascript:view_peptides_tpp(<?php echo  $hitsValue['ID'];?><?php echo (($SCRIPT_NAME == "item_report.php")?"":",'new'")?>);"><img border="0" src="./images/icon_P.gif" alt="Peptides"></a>
          <?php  
            if(strstr($hitsValue['SearchEngine'], 'Uploaded')){
              $tmp_file_name = $hitsValue['XmlFile'];
              $theFile = "./ProhitsTPP_protHTML.php?userID=$AccessUserID&File=$tmp_file_name";
          ?>
            <a href="javascript:popwin('<?php echo str_replace("\\","/",$theFile)?>',800,800,'new')"><img border="0" src="./images/icon_tpp_uploaded.gif"></a>
          <?php }else{
              if($_SERVER['HTTP_HOST'] == 'prohitsms.com'){
                  $demo_search_results = 'demo_search_results.php';
                  echo "<a href='../msManager/demo_search_results.php' target=_new><img border='0' src='./images/icon_tpp.gif'></a>\n";
              }else{
          
          ?>
            <a href="<?php echo $tpp_cgi?>/protxml2html.pl?min_prob=<?php echo TPP_DISPLAY_MIN_PROBABILITY;?>&xmlfile=<?php echo $hitsValue['XmlFile']?>" target=new><img border="0" src="./images/icon_tpp.gif"></a>
          <?php   }
            }?>
          </div>
          </td>
        </tr>
      <?php }elseif($hitType == 'normal'){
          //if(array_key_exists($hitsValue['HitGI'], $gi_acc_arr)) $hitsValue['HitGI'] = $gi_acc_arr[$hitsValue['HitGI']];
          
          if(array_key_exists($hitsValue['HitGI'], $gi_acc_arr)){
            $tmp_gi = $hitsValue['HitGI'];
            $hitsValue['HitGI'] = $gi_acc_arr[$tmp_gi]['Acc_V'];
            $hitsValue['Acc'] = $gi_acc_arr[$tmp_gi]['Acc'];
          }else{
            $hitsValue['Acc'] = $hitsValue['HitGI'];
          }
          
          if(isset($giArr) && $hitsValue['HitGI'] && ($hitsValue['GeneID'] || $hitsValue['GeneName'])){
            $hits_atr_str = $BaitID.",".$arr2_value['ID'].",".$arr2_value['Location'].",".$hitsValue['HitGI'].",".$hitsValue['Expect'].",".$hitsValue['Pep_num_uniqe'];
            $tmpPkey = $hitsValue['GeneID'].",".$hitsValue['GeneName'];
            if(!array_key_exists($tmpPkey, $giArr)){
              $giArr[$tmpPkey] = array();
              array_push($giArr[$tmpPkey], $hits_atr_str);
            }else{
              array_push($giArr[$tmpPkey], $hits_atr_str);
            }
          }
  
          if($HitFrequency){
            $HitFrequencyPercent = $HitFrequency."%";
          }else{
            $HitFrequencyPercent = "0%";
          }
  
          $Description = $hitsValue['HitName'] = str_replace(",", ";", $hitsValue['HitName']);
          $Description = $hitsValue['HitName'] = str_replace("\n", "", $Description);
          $hitsValue['GeneName'] = str_replace(",", ";", $hitsValue['GeneName']);
          $hitsValue['LocusTag'] = str_replace(",", ";", $hitsValue['LocusTag']);
          $hitsValue['SearchDatabase'] = str_replace(",", ";", $hitsValue['SearchDatabase']);
          $hitsValue['Expect'] = str_replace(",", ";", $hitsValue['Expect']);
          $hitsValue['HitGI'] = str_replace(",", ";", $hitsValue['HitGI']);
          $hitsValue['Frequency'] = $HitFrequencyPercent;
          $filterString = '';
      ?>
        <tr  bgcolor='<?php echo $tmpbgcolor;?>' onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $tmpbgcolor;?>');">
          <td width="" align="center" bgcolor=<?php 
          if($hitsValue['GeneID'] && ($BaitArr['GeneID'] == $hitsValue['GeneID'])){
        	  echo "'$item_color'";
            for($m=0; $m<count($typeBioArr); $m++){
              if($typeBioArr[$m]['Alias'] == ID_BAIT){
          				$typeBioArr[$m]['Counter']++;
          		}
            }
        	}else{
        	  echo "'$tmpbgcolor'";
          }
          //if($SCRIPT_NAME != "pop_plate_show.php"){
          if(isset($hitsGeneIDarr)){
            array_push($hitsGeneIDarr, $hitsValue['GeneID']);
          }
      	 ?>
         ><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['ID'];?>&nbsp;
            </div>
          </td>
          <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
              <?php echo  $hitsValue['HitGI'];?>&nbsp;
              </div>
          </td>
          <td width="" align="center" <?php echo  ($hitsValue['GeneID'] || $hitsValue['GeneName'])?"class='gi".$hitsValue['GeneID']."'" : "";?>> <a href="javascript: href_show_hand();" onmouseover="showSameGene(event,'<?php echo $hitsValue['GeneID']?>');" onmouseout="hideSameGene();"  class=button><div class=maintext>
  
              <?php 
              if($hitsValue['GeneID'] || $hitsValue['GeneName']){
                echo  $hitsValue['GeneID']." / ".$hitsValue['GeneName'];
              }
              ?>&nbsp;
              </div></a>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <b>
              <?php 
              echo $hitsValue['Expect'];
              ?>&nbsp;
              </b>
            </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <b>
              <?php echo $hitsValue['Expect2'];?>&nbsp;
              </b></div>
          </td>
          <!--td width="" align="center"><div class=maintext>
              <?php echo  $hitsValue['LocusTag'];?>&nbsp;
              </div>
          </td-->
  
          <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
              <?php if($frequencyArr){
                  echo $HitFrequencyPercent."&nbsp;";
                }else{
                  echo "&nbsp;&nbsp;";
                }
              ?>
              </div>
          </td>
          <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
              <?php 
              //echo str_replace("gi","<br>gi", $hitsValue['RedundantGI']);
              $Redundant_str = convert_Redundant($hitsValue['RedundantGI'],$gi_acc_arr);
              echo $Redundant_str;
              ?>&nbsp;
              </div>
          </td>
          <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
              <?php echo  $hitsValue['MW'];?>&nbsp;
              </div>
          </td>
          <td width="" align="left"><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['HitName'];?>&nbsp;
            </div>
          </td>
  
          <td width="" align="right" ><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Pep_num'];?>&nbsp;</div>
          </td>
          <td width="" align="right" ><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Pep_num_uniqe'];?>&nbsp;</div>
          </td>
          <td width="" align="right" ><div class=<?php echo $tmptextfont;?>>
              <?php echo (($Coverage=$hitsValue['Coverage'])>0)?"$Coverage%":"";?>&nbsp;</div>
          </td>
          <td width="" align="center" nowrap><div class=<?php echo $tmptextfont;?>>
          <?php 
          $urlLocusTag = $hitsValue['LocusTag'];
          $urlGeneID = $hitsValue['GeneID'];
          $urlGI = $hitsValue['HitGI'];
          echo get_URL_str($hitsValue['HitGI'], $hitsValue['GeneID'], $hitsValue['LocusTag']);
          ?>
          </div>
          </td>
          <td>
            <table border=0 cellpadding="1" cellspacing="1"><tr>
          <?php 
  
          $counter = count($typeBioArr);
          for($m=0; $m<$counter; $m++){
            if(($typeBioArr[$m]['Alias'] != ID_BAIT) && in_array($typeBioArr[$m]['Alias'] ,$tmpHitNotes)){
        			$tmp_color = $typeBioArr[$m]['Color'];
        			echo "<td bgcolor=$tmp_color nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
              ($filterString)? $filterString.=";".$typeBioArr[$m]['Name'] : $filterString.=$typeBioArr[$m]['Name'];
        			$typeBioArr[$m]['Counter']++;
        		}
          }
  
          $counter = count($typeExpArr);
          for($m=0; $m<$counter; $m++){
            if(($typeExpArr[$m]['Alias'] != ID_BAIT) && in_array($typeExpArr[$m]['Alias'] ,$tmpHitNotes)){
        			$tmp_color = $typeExpArr[$m]['Color'];
        			echo "<td bgcolor=$tmp_color nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
              ($filterString)? $filterString.=";".$typeExpArr[$m]['Name'] : $filterString.=$typeExpArr[$m]['Name'];
        			$typeExpArr[$m]['Counter']++;
        		}
          }
          if($typeFrequencyArr && $HitFrequency>$frequencyLimit and !$is_reincluded){
            $typeFrequencyArr['Counter']++;
             echo "<td bgcolor='" . $typeFrequencyArr['Color'] . "' nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
             ($filterString)? $filterString.=";frequence>=$frequencyLimit%" : $filterString.="frequence>=$frequencyLimit%";
          }
          if(($hitsValue['Expect'] and $hitsValue['Expect'] <= DEFAULT_EXPECT_EXCLUSION) and !$theaction and !$is_reincluded){
             echo "<td bgcolor='$expect_exclusion_color' nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
             ($filterString)? $filterString.=";expect<=".DEFAULT_EXPECT_EXCLUSION : $filterString.="expect<=".DEFAULT_EXPECT_EXCLUSION;
          }
          if(in_array(ID_REINCLUDE,$tmpHitNotes)) {  //reinclude
              echo "<td bgcolor=#660000><font face='Arial' color=white size=-1><b>R</b></font></td>";
              ($filterString)? $filterString.=";reinclude" : $filterString.="reinclude";
          }
          if(in_array(ID_MANUALEXCLUSION,$tmpHitNotes)) {  //manual exclude
              echo "<td bgcolor=black><font face='Arial' color=yellow size=-1><b>X</b></font></td>";
              ($filterString)? $filterString.=";manualexclusion" : $filterString.="manualexclusion";
          }
          if($hitsValue['GeneID'] && array_key_exists($hitsValue['GeneID'], $sub_grid_bait_hits_arr)){
            if(!in_array($hitsValue['GeneID'],$matched_hits_geneID_arr)){
              $matched_hits_geneID_arr[$hitsValue['GeneName']] = $hitsValue['GeneID'];
            }
            $typeStr = '';
            echo get_bioGrid_icon($sub_grid_bait_hits_arr[$hitsValue['GeneID']],$typeStr);
            $tmpEageKey = $BaitID." ".str_replace(",", ";",$BaitGeneName)."??".str_replace(",", ";",$hitsValue['GeneName']);
            if(!array_key_exists($tmpEageKey, $EdgeArr_matched)){
              $EdgeArr_matched[$tmpEageKey] = $typeStr.",".$hitsValue['GeneID'];
            }
          }else{
            echo "<td ><div class=maintext>&nbsp;&nbsp;</div></td>";
          }
          $hitsValue['Filters'] = $filterString;
          ?>
            </tr></table>
          </td>
          <td width="" align="left" nowrap><div class=maintext>&nbsp; &nbsp; &nbsp;
          <?php if(!$BaitArr['GelFree']){?>
            <a href="javascript: view_gel(<?php echo $arr2_value['ID'];?>);"><img border=0 src="./images/icon_picture.gif" alt='gel image'></a>
          <?php }?>
          <?php if(strstr($hitsValue['SearchEngine'], 'Mascot') or strstr($hitsValue['SearchEngine'],'GPM')){?>
            <a href="javascript:view_peptides(<?php echo  $hitsValue['ID'];?><?php echo (($SCRIPT_NAME == "item_report.php")?"":",'new'")?>);"><img border="0" src="./images/icon_P.gif" alt="Peptides"></a>
          <?php }elseif(strstr($hitsValue['SearchEngine'],'MSPLIT')){
                if($hitType == 'geneLevel'){
            ?>
              <a href="javascript:view_peptides_geneLevel(<?php echo  $hitsValue['ID'];?><?php echo (($SCRIPT_NAME == "item_report.php")?"":",'new'")?>);"><img border="0" src="./images/icon_P.gif" alt="Peptides"></a>          
            <?php   }else{?>
              <a href="javascript:view_peptides(<?php echo  $hitsValue['ID'];?><?php echo (($SCRIPT_NAME == "item_report.php")?"":",'new'")?>);"><img border="0" src="./images/icon_P.gif" alt="Peptides"></a>
            <?php 
                }
            }elseif(strstr($hitsValue['SearchEngine'],'SEQUEST')){?>
            <a href="javascript:view_peptides_SEQUEST(<?php echo  $hitsValue['ID'];?><?php echo (($SCRIPT_NAME == "item_report.php")?"":",'new'")?>);"><img border="0" src="./images/icon_P.gif" alt="Peptides"></a>
          <?php }?>      
          <?php if(strstr($hitsValue['SearchEngine'], 'Uploaded')){
              if(strstr($hitsValue['SearchEngine'], 'Mascot')){
                $tmp_file_name = $hitsValue['ResultFile'];
                $theFile = "./ProhitsMascotParserHTML.php?userID=$AccessUserID&File=$tmp_file_name";
          ?>
            <a href="javascript:popwin('<?php echo str_replace("\\","/",$theFile)?>',800,800,'new')"><img border="0" src="./images/icon_<?php echo $hitsValue['SearchEngine'];?>2.gif" alt="Peptides"></a>
          <?php 
              }elseif(strstr($hitsValue['SearchEngine'], 'GPM')){
                $tmp_dir = dirname($hitsValue['ResultFile']);
                $tme_name = basename($hitsValue['ResultFile'], ".xml").".txt";
                $tmp_file_name = $tmp_dir.'/'.$tme_name;
                //$tmp_file_name = str_replace(".xml", ".txt", $hitsValue['ResultFile']);
                $theFile = "./ProhitsGPM_ParserHTML.php?userID=$AccessUserID&File=$tmp_file_name";
                str_replace("%body%", "black", "<body text='%body%'>");
          ?>
            <a href="javascript:popwin('<?php echo str_replace("\\","/",$theFile)?>',800,800,'new')"><img border="0" src="./images/icon_<?php echo $hitsValue['SearchEngine'];?>2.gif" alt="Peptides"></a>
          <?php 
              }
            }else{
              if(stristr($hitsValue['SearchEngine'], 'MSPLIT')){
                $theFile = "../msManager/ms_search_MSPLIT_results_view.php?path=".$hitsValue['ResultFile']."&BandID=".$hitsValue['BandID']."&SearchEngine=".$hitsValue['SearchEngine']."&table=".$hitsValue['Instrument'];
          ?>
                <a href="javascript:popwin('<?php echo str_replace("\\","/",$theFile)?>',800,800,'new')"><img border="0" src="./images/icon_geneLevel.gif" alt="Peptides"></a>
          <?php     
              }elseif($_SERVER['HTTP_HOST'] == 'prohitsms.com'){
                $demo_search_results = 'demo_search_results.php';
                echo "<a href='../msManager/demo_search_results.php' target=_new><img border='0' src='./images/icon_".$hitsValue['SearchEngine']."2.gif'></a>\n";
              }else{
          ?>
              <a href="javascript:view_master_results('<?php echo  str_replace("\\",'/', $hitsValue['ResultFile']);?>','<?php echo  $hitsValue['SearchEngine'];?>');"><img border="0" src="./images/icon_<?php echo $hitsValue['SearchEngine'];?>2.gif" alt="Peptides"></a>
          <?php 
              }
            }
            $dark = '';
            if(in_array($hitsValue['ID'], $note_exist_arr)) $dark = "_dark";
          ?>
            <a href="javascript: add_notes('<?php echo $hitsValue['ID'];?>');"><img src="./images/icon_notes<?php echo $dark?>.gif" border=0 alt="Hit Notes"></a>
          <?php 
          $coip_color_and_ID = array('color'=>'', 'ID'=> '');
          $coip_color_and_ID = get_coip_color($HITSDB, $BaitArr['GeneID'], $hitsValue['GeneID']);
          if($coip_color_and_ID && $coip_color_and_ID['ID'] && $coip_color_and_ID['color']){
            echo "<a href='./coip.php?theaction=modify&Coip_ID=".$coip_color_and_ID['ID'] . "' target=new>";
            echo "<img src=\"./images/icon_coip_".$coip_color_and_ID['color'].".gif\" border=0 alt='co-ip detail'>";
            echo "</a>";
          }
          ?>
          </div>
          </td>
        </tr>
      <?php }elseif($hitType == 'TPPpep'){
          if(isset($giArr) && $hitsValue['Protein'] && ($hitsValue['GeneID'] || $hitsValue['GeneName'])){
            if(!isset($giArr[$hitsValue['Protein']])){
              $giArr[$hitsValue['Protein']] = 1;
            }else{
              $giArr[$hitsValue['Protein']]++;
            }
          }
  
          if($HitFrequency){
            $HitFrequencyPercent = $HitFrequency."%";
          }else{
            $HitFrequencyPercent = "0%";
          }
      ?>
        <tr  bgcolor='<?php echo $tmpbgcolor;?>' onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $tmpbgcolor;?>');">
          <td width="" align="center" bgcolor=<?php 
          if($hitsValue['GeneID'] && ($BaitArr['GeneID'] == $hitsValue['GeneID'])){
        	  echo "'$item_color'";
            for($m=0; $m<count($typeBioArr); $m++){
              if($typeBioArr[$m]['Alias'] == ID_BAIT){
          				$typeBioArr[$m]['Counter']++;
          		}
            }
        	}else{
        	  echo "'$tmpbgcolor'";
          }
      	 ?>
         ><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['ID'];?>&nbsp;
            </div>
          </td>
          <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
              <?php echo  $hitsValue['Protein'];?>&nbsp;
              </div>
          </td>
          <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
              <?php echo  $hitsValue['Probability'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php 
              echo $hitsValue['Score1'];
              ?>&nbsp;
            </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Score2'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Score3'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Score4'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Score5'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Ions'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Sequence'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Charge'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo round($hitsValue['Calc_mass'],2);?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo round($hitsValue['Massdiff'],2);?>&nbsp;
             </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Mised_cleavages'];?>&nbsp;
              </div>
          </td>
          <?php 
          $counter = count($typeExpArr);
          for($m=0; $m<$counter; $m++){
            if(($typeExpArr[$m]['Alias'] != ID_BAIT) && in_array($typeExpArr[$m]['Alias'] ,$tmpHitNotes)){
        			$typeExpArr[$m]['Counter']++;
        		}
          }
          if(array_key_exists($hitsValue['XmlFile'], $fileColorIndexArr)){
            $optionBgColor = $colorArr[$fileColorIndexArr[$hitsValue['XmlFile']]];
          }else{
            $fileColorIndexArr[$hitsValue['XmlFile']] = $fileColorCounter;
            $optionBgColor = $colorArr[$fileColorIndexArr[$hitsValue['XmlFile']]];
            $fileColorCounter++;
          }
          //echo $hitsValue['SearchEngine'];
          ?>
          <td width="" align="center" bgcolor='<?php echo $optionBgColor;?>' nowrap><div class=maintext>        
          <?php if(strstr($hitsValue['SearchEngine'], 'Uploaded')){
              $tmp_file_name = $hitsValue['XmlFile']; 
              $theFile = "./ProhitsTPP_pepHTML.php?userID=$AccessUserID&File=$tmp_file_name";
          ?>
            <a href="javascript:popwin('<?php echo str_replace("\\","/",$theFile)?>',800,800,'new')"><img border="0" src="./images/icon_tpp_uploaded.gif"></a>
          <?php }else{
              if(strpos($hitsValue['XmlFile'], 'uploaded:') === false){
                if($_SERVER['HTTP_HOST'] == 'prohitsms.com'){
                  $demo_search_results = 'demo_search_results.php';
                  echo "<a href='../msManager/demo_search_results.php' target=_new><img border='0' src='./images/icon_tpp.gif'></a>\n";
                }else{
              ?>
                <a href="<?php echo $tpp_cgi?>/PepXMLViewer.cgi?FmPprobability=<?php echo TPP_DISPLAY_MIN_PROBABILITY;?>&xmlFileName=<?php echo $hitsValue['XmlFile']?>" target=new><img border="0" src="./images/icon_tpp.gif"></a>
          <?php     }
              }
            }?>          
          </div>
          </td>
        </tr>
   <?php 
        }elseif($hitType == 'geneLevel'){
          if($HitFrequency){
            $HitFrequencyPercent = $HitFrequency."%";
          }else{
            $HitFrequencyPercent = "0%";
          }
          
          $hitsValue['GeneName'] = str_replace(",", ";", $hitsValue['GeneName']);
          $hitsValue['SearchDatabase'] = str_replace(",", ";", $hitsValue['SearchDatabase']);
          $hitsValue['Frequency'] = $HitFrequencyPercent;
          $filterString = '';
          //for($i=0; $i<count($geneID_arr); $i++){
      ?>
          <tr  bgcolor='<?php echo $tmpbgcolor;?>' onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $tmpbgcolor;?>');">
            <td width="" align="center" bgcolor=<?php 
            if($hitsValue['GeneID'] && ($BaitArr['GeneID'] == $hitsValue['GeneID'])){
          	  echo "'$item_color'";
              for($m=0; $m<count($typeBioArr); $m++){
                if($typeBioArr[$m]['Alias'] == ID_BAIT){
            				$typeBioArr[$m]['Counter']++;
            		}
              }
          	}else{
          	  echo "'$tmpbgcolor'";
            }
            if(isset($hitsGeneIDarr)){
              //array_push($hitsGeneIDarr, $geneID_arr[$i]);
            }
        	 ?>
             ><div class=<?php echo $tmptextfont;?>>
                <?php echo $hitsValue['ID'];?>&nbsp;
              </div>
            </td>
            <td width="" align="center" <?php echo  ($hitsValue['GeneID'] || $hitsValue['GeneName'])?"class='gi".$hitsValue['GeneID']."'" : "";?>> <a href="javascript: href_show_hand();" onmouseover="showSameGene(event,'<?php echo $hitsValue['GeneID']?>');" onmouseout="hideSameGene();"  class=button>
              <div class=maintext>
                <?php 
                if($hitsValue['GeneID'] || $hitsValue['GeneName']){
                  echo  $hitsValue['GeneID']." / ".$hitsValue['GeneName'];
                }
                ?>&nbsp;
              </div></a>
            </td>
            <td width="" align="left">
              <div class=<?php echo $tmptextfont;?>>
                <?php echo $hitsValue['Redundant'];?>&nbsp;
              </div>
            </td>
            <td width="" align="right" nowrap>
              <div class=<?php echo $tmptextfont;?>>
                <b>&nbsp;&nbsp;<?php echo $hitsValue['SpectralCount'];?>&nbsp;&nbsp;&nbsp;</b>
              </div>
            </td>
            <td width="" align="right" ><div class=<?php echo $tmptextfont;?>>
               &nbsp;&nbsp;<?php echo $hitsValue['Unique'];?>&nbsp;&nbsp;&nbsp;</div>
            </td>
            <td width="" align="right" nowrap>
              <div class=<?php echo $tmptextfont;?>>
                &nbsp;&nbsp;<?php echo $hitsValue['Subsumed'];?>&nbsp;&nbsp;&nbsp;
              </div>
            </td> 
            <td width="" align="left">
              <div class=<?php echo $tmptextfont;?>>
                <?php if($frequencyArr){
                    echo $HitFrequencyPercent."&nbsp;";
                  }else{
                    echo "&nbsp;&nbsp;";
                  }
                ?>
              </div>
            </td>
            <td width="" align="center" nowrap><div class=<?php echo $tmptextfont;?>>
            <?php 
            $urlGeneID = $hitsValue['GeneID'];
            echo get_URL_str('', $hitsValue['GeneID'], '');
            ?>
            </div>
            </td>
            <td>
              <table border=0 cellpadding="1" cellspacing="1"><tr>
            <?php 
    
            $counter = count($typeBioArr);
            for($m=0; $m<$counter; $m++){
              if(($typeBioArr[$m]['Alias'] != ID_BAIT) && in_array($typeBioArr[$m]['Alias'] ,$tmpHitNotes)){
          			$tmp_color = $typeBioArr[$m]['Color'];
          			echo "<td bgcolor=$tmp_color nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
                ($filterString)? $filterString.=";".$typeBioArr[$m]['Name'] : $filterString.=$typeBioArr[$m]['Name'];
          			$typeBioArr[$m]['Counter']++;
          		}
            }
    
            $counter = count($typeExpArr);
            for($m=0; $m<$counter; $m++){
              if(($typeExpArr[$m]['Alias'] != ID_BAIT) && in_array($typeExpArr[$m]['Alias'] ,$tmpHitNotes)){
          			$tmp_color = $typeExpArr[$m]['Color'];
          			echo "<td bgcolor=$tmp_color nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
                ($filterString)? $filterString.=";".$typeExpArr[$m]['Name'] : $filterString.=$typeExpArr[$m]['Name'];
          			$typeExpArr[$m]['Counter']++;
          		}
            }
            if($typeFrequencyArr && $HitFrequency>$frequencyLimit and !$is_reincluded){
              $typeFrequencyArr['Counter']++;
               echo "<td bgcolor='" . $typeFrequencyArr['Color'] . "' nowrap><div class=maintext>&nbsp; &nbsp;</div></td>";
               ($filterString)? $filterString.=";frequence>=$frequencyLimit%" : $filterString.="frequence>=$frequencyLimit%";
            }
            if(in_array(ID_REINCLUDE,$tmpHitNotes)) {  //reinclude
                echo "<td bgcolor=#660000><font face='Arial' color=white size=-1><b>R</b></font></td>";
                ($filterString)? $filterString.=";reinclude" : $filterString.="reinclude";
            }
            if(in_array(ID_MANUALEXCLUSION,$tmpHitNotes)) {  //manual exclude
                echo "<td bgcolor=black><font face='Arial' color=yellow size=-1><b>X</b></font></td>";
                ($filterString)? $filterString.=";manualexclusion" : $filterString.="manualexclusion";
            }
            if($hitsValue['GeneID'] && array_key_exists($hitsValue['GeneID'], $sub_grid_bait_hits_arr)){
              if(!in_array($hitsValue['GeneID'],$matched_hits_geneID_arr)){
                $matched_hits_geneID_arr[$hitsValue['GeneName']] = $hitsValue['GeneID'];
              }
              $typeStr = '';
              echo get_bioGrid_icon($sub_grid_bait_hits_arr[$hitsValue['GeneID']],$typeStr);
              $tmpEageKey = $BaitID." ".str_replace(",", ";",$BaitGeneName)."??".str_replace(",", ";",$hitsValue['GeneName']);
              if(!array_key_exists($tmpEageKey, $EdgeArr_matched)){
                $EdgeArr_matched[$tmpEageKey] = $typeStr.",".$hitsValue['GeneID'];
              }
            }else{
              echo "<td ><div class=maintext>&nbsp;&nbsp;</div></td>";
            }
            $hitsValue['Filters'] = $filterString;
            ?>
              </tr></table>
            </td>
            <td width="" align="left" nowrap><div class=maintext>&nbsp; &nbsp; &nbsp;
            <?php if(!$BaitArr['GelFree']){?>
              <a href="javascript: view_gel(<?php echo $arr2_value['ID'];?>);"><img border=0 src="./images/icon_picture.gif" alt='gel image'></a>
            <?php }?>          
              <a href="javascript:view_peptides_geneLevel(<?php echo  $hitsValue['ID'];?><?php echo (($SCRIPT_NAME == "item_report.php")?"":",'new'")?>);"><img border="0" src="./images/icon_P.gif" alt="Peptides"></a>          
        
            <?php $theFile = "../msManager/ms_search_MSPLIT_results_view.php?path=".$hitsValue['ResultFile']."&BandID=".$hitsValue['BandID']."&SearchEngine=".$hitsValue['SearchEngine']."&table=".$hitsValue['Instrument'];?>
              <a href="javascript:popwin('<?php echo str_replace("\\","/",$theFile)?>',800,800,'new')"><img border="0" src="./images/icon_geneLevel.gif" alt="Peptides"></a>
            <?php   
              $dark = '';
              if(in_array($hitsValue['ID'], $note_exist_arr)) $dark = "_dark";
            ?>
              <a href="javascript: add_notes_geneLevel('<?php echo $hitsValue['ID'];?>');"><img src="./images/icon_notes<?php echo $dark?>.gif" border=0 alt="Hit Notes"></a>
            <?php 
            $coip_color_and_ID = array('color'=>'', 'ID'=> '');
            $coip_color_and_ID = get_coip_color($HITSDB, $BaitArr['GeneID'], $hitsValue['GeneID']);
            if($coip_color_and_ID && $coip_color_and_ID['ID'] && $coip_color_and_ID['color']){
              echo "<a href='./coip.php?theaction=modify&Coip_ID=".$coip_color_and_ID['ID'] . "' target=new>";
              echo "<img src=\"./images/icon_coip_".$coip_color_and_ID['color'].".gif\" border=0 alt='co-ip detail'>";
              echo "</a>";
            }
            ?>
            </div>
            </td>
          </tr>
        <?php //}?>
      <?php }elseif($hitType == 'TPPpep'){
          if(isset($giArr) && $hitsValue['Protein'] && ($hitsValue['GeneID'] || $hitsValue['GeneName'])){
            if(!isset($giArr[$hitsValue['Protein']])){
              $giArr[$hitsValue['Protein']] = 1;
            }else{
              $giArr[$hitsValue['Protein']]++;
            }
          }
  
          if($HitFrequency){
            $HitFrequencyPercent = $HitFrequency."%";
          }else{
            $HitFrequencyPercent = "0%";
          }
      ?>
        <tr  bgcolor='<?php echo $tmpbgcolor;?>' onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $tmpbgcolor;?>');">
          <td width="" align="center" bgcolor=<?php 
          if($hitsValue['GeneID'] && ($BaitArr['GeneID'] == $hitsValue['GeneID'])){
        	  echo "'$item_color'";
            for($m=0; $m<count($typeBioArr); $m++){
              if($typeBioArr[$m]['Alias'] == ID_BAIT){
          				$typeBioArr[$m]['Counter']++;
          		}
            }
        	}else{
        	  echo "'$tmpbgcolor'";
          }
      	 ?>
         ><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['ID'];?>&nbsp;
            </div>
          </td>
          <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
              <?php echo  $hitsValue['Protein'];?>&nbsp;
              </div>
          </td>
          <td width="" align="center"><div class=<?php echo $tmptextfont;?>>
              <?php echo  $hitsValue['Probability'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php 
              echo $hitsValue['Score1'];
              ?>&nbsp;
            </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Score2'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Score3'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Score4'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Score5'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Ions'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Sequence'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Charge'];?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo round($hitsValue['Calc_mass'],2);?>&nbsp;
              </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo round($hitsValue['Massdiff'],2);?>&nbsp;
             </div>
          </td>
          <td width="" align="right" nowrap><div class=<?php echo $tmptextfont;?>>
              <?php echo $hitsValue['Mised_cleavages'];?>&nbsp;
              </div>
          </td>
          <?php 
          $counter = count($typeExpArr);
          for($m=0; $m<$counter; $m++){
            if(($typeExpArr[$m]['Alias'] != ID_BAIT) && in_array($typeExpArr[$m]['Alias'] ,$tmpHitNotes)){
        			$typeExpArr[$m]['Counter']++;
        		}
          }
          if(array_key_exists($hitsValue['XmlFile'], $fileColorIndexArr)){
            $optionBgColor = $colorArr[$fileColorIndexArr[$hitsValue['XmlFile']]];
          }else{
            $fileColorIndexArr[$hitsValue['XmlFile']] = $fileColorCounter;
            $optionBgColor = $colorArr[$fileColorIndexArr[$hitsValue['XmlFile']]];
            $fileColorCounter++;
          }
          //echo $hitsValue['SearchEngine'];
          ?>
          <td width="" align="center" bgcolor='<?php echo $optionBgColor;?>' nowrap><div class=maintext>        
          <?php if(strstr($hitsValue['SearchEngine'], 'Uploaded')){
              $tmp_file_name = $hitsValue['XmlFile']; 
              $theFile = "./ProhitsTPP_pepHTML.php?userID=$AccessUserID&File=$tmp_file_name";
          ?>
            <a href="javascript:popwin('<?php echo str_replace("\\","/",$theFile)?>',800,800,'new')"><img border="0" src="./images/icon_tpp_uploaded.gif"></a>
          <?php }else{
              if(strpos($hitsValue['XmlFile'], 'uploaded:') === false){
                if($_SERVER['HTTP_HOST'] == 'prohitsms.com'){
                  $demo_search_results = 'demo_search_results.php';
                  echo "<a href='../msManager/demo_search_results.php' target=_new><img border='0' src='./images/icon_tpp.gif'></a>\n";
                }else{
              ?>
                <a href="<?php echo $tpp_cgi?>/PepXMLViewer.cgi?FmPprobability=<?php echo TPP_DISPLAY_MIN_PROBABILITY;?>&xmlFileName=<?php echo $hitsValue['XmlFile']?>" target=new><img border="0" src="./images/icon_tpp.gif"></a>
          <?php     }
              }
            }?>          
          </div>
          </td>
        </tr> 
   <?php    
        }
        $fileLevel_3_str = '';
        if(isset($fileDelimit)){
          foreach($level3_lable_array as $tmpKey => $tmpLable){
            if(!array_key_exists($tmpKey, $hitsValue)) continue;
            
            
            if($tmpKey == 'SearchEngine'){
              if($hitType == 'TPP'){
                $tmp_Engine =  $hitsValue[$tmpKey];
              }else{
                $tmp_Engine =  str_replace("Uploaded", "", $hitsValue[$tmpKey]);
              }
              if($hitType == 'TPP'){
                $tmp_Engine = 'TPP_'.$tmp_Engine;
              }elseif($hitType == 'geneLevel'){
                $tmp_Engine = 'GeneLevel_'.$tmp_Engine;
              }
              $hitsValue[$tmpKey] = $SearchEngine_lable_arr[$tmp_Engine];
            }
              
            $hitsValue[$tmpKey] = str_replace(",", ";", $hitsValue[$tmpKey]);
            $hitsValue[$tmpKey] = str_replace("\n", "", $hitsValue[$tmpKey]);
            $fileLevel_3_str .= $hitsValue[$tmpKey].$fileDelimit;
          }
//echo "$fileLevel_3_str<br>";
          $fileLevel_3_str .= "\r\n";
          fwrite($handle, $fileLevel_3_str);
        }
      }//end if re_excluded
    }
  } 
?>
