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
  $filerLable_css = "maintext_extra";
  $filter_add_checkbox = 0;
  $table_cellspacing = 1;
  $table_cellpadding = 0;
  $table_width = "100%";
  $cellpadding = "1";
  if(!isset($filterStyleDisplay)) $filterStyleDisplay = 'none';  
  if($filter_add_checkbox){
    $table_cellspacing = 0;
    $table_cellpadding = 0;  
  }
  if(!isset($Expect)){
    $Expect = 'Expect';
  }  
  if($filter_for == 'item_report'){
    $MAX = 'MAX';
    if($hitType == "TPP"){
      $SearchEngine = "TPP_";
      $SearchEngine = "TPP_$searchEngineField";
    }elseif($hitType == "normal" || $hitType == "geneLevel"){
      $SearchEngine = $searchEngineField;
      if($searchEngineField == 'GPM') $Expect = 'Expect2';
    }elseif($hitType == "TPPpep"){
      $SearchEngine = "TPPpep";
    }
  }
  
  $tr_bgcolor = '#e3e3e3';
  $tr_title_bgcolor = 'white';
  $edgeLine_color = "#b7c1c8";
  
  $SQL = "SELECT `FilterAlias` FROM `HitNote` GROUP BY `FilterAlias";
  $tmp_sql_arr = $HITSDB->fetchAll($SQL);
  $exists_exp_arr = array();
  foreach($tmp_sql_arr as $tmp_sql_val){
    array_push($exists_exp_arr, $tmp_sql_val['FilterAlias']);
  }
?>          
    <DIV ID="filter_area" style="display:<?php echo $filterStyleDisplay;?>">
      <table width=<?php echo $table_width?> bgcolor="#708090" cellspacing="0" cellpadding="0" border="0" >
      <tr><td>
        <table align="center" cellspacing="<?php echo $table_cellspacing?>" cellpadding="<?php echo $table_cellpadding?>" border="0" width=100%>
          <tr bgcolor="<?php echo $edgeLine_color?>">
            <td valign=bottom colspan=2>
            <table border=0 cellspacing="1" cellpadding="1" width=100% bgcolor="white">
            <tr bgcolor=<?php echo $tr_title_bgcolor;?>>
              <td colspan=4><div class=maintext><b>Experiment Filters</b></div></td>
            </tr>
            <tr bgcolor=<?php echo $tr_bgcolor;?>>
<?php   if($SearchEngine == 'TPPpep'){
      foreach($typeExpArr as $Value){
        $frmName = 'frm_' . $Value['Alias'];
        $tmp_icon_color = str_replace("#", "", $Value['Color']);
        if(!strstr($Value['Alias'], 'CH')){
          echo "<td>";
          echo "<div class=$filerLable_css>&nbsp";
          echo "&nbsp;".$Value['Name']." <b><</b> <input type=text name='$frmName' value='".$$frmName."' size=2>";
          echo ($Value['Alias']=='ION' || $Value['Alias']=='PBT')?'%':'';
          echo "&nbsp;<img src='./comparison_results_create_image.php?lableBgc=$tmp_icon_color&filter_for=$filter_for' border=0>";
          echo " </div>";
          echo "</td>";
        }elseif($Value['Alias'] == 'CH1'){
          echo "<td>";
          echo "<div class=$filerLable_css>&nbsp";
          echo "&nbsp;".$Value['Name']."<input type=checkbox name='$frmName' value='".substr($Value['Alias'], -1)."' size=2 ".(($$frmName)?'checked':'').">";
          echo "&nbsp;<img src='./comparison_results_create_image.php?lableBgc=$tmp_icon_color&filter_for=$filter_for' border=0>";
        }else{
          echo "&nbsp;".$Value['Name']."<input type=checkbox name='$frmName' value='".substr($Value['Alias'], -1)."' size=2 ".(($$frmName)?'checked':'').">";  
          echo "&nbsp;<img src='./comparison_results_create_image.php?lableBgc=$tmp_icon_color&filter_for=$filter_for' border=0>";              
        }
        if($Value['Alias'] == 'CH3'){
          echo " </div>";
          echo "</td>";
        }
        $subQueryString .= '&'.$frmName.'='.$$frmName;
        if($$frmName){
          if($subWhere2) $subWhere2 .= '#';
          if($Value['DBfieldName'] == 'TP.Ions'){
            $subWhere2 .= $Value['DBfieldName'].'='.$$frmName;
            continue; 
          }else{
            $subWhere2 .= $Value['DBfieldName'].'='.$Value['DBfieldName'].$Value['Operator2'].$$frmName;
          }
          if($subWhere) $subWhere .= ' AND ';
          $subWhere .= $Value['DBfieldName'].$Value['Operator'].$$frmName;
        } 
      }
      echo "</tr></table></td>";
      if($subWhere) $subWhere = ' AND ('.$subWhere.')';
    }else{
//====================================================================================================================    
      if($filter_for == 'SAINT' || $filter_for == 'DIAUmpire_Quant'){
        $tmp_counter = 0;
        if(strstr($SearchEngine,'TPP')){
          $freqency_lable = "Frequency";
          $freqency_file_name = "P:P".$AccessProjectID."_tpp_frequency.csv";
        }elseif($SearchEngine){
          $freqency_lable = "Frequency";
          $freqency_file_name = "P:P".$AccessProjectID."_".$SearchEngine."_frequency.csv";
        }else{
          $freqency_lable = "Frequency";
          $freqency_file_name = "P:P".$AccessProjectID."_frequency.csv";
        }
        $freqency_file_name = str_replace('XTandem', 'GPM', $freqency_file_name);
        $freqency_file_name = str_replace(' ', '_', $freqency_file_name);
        $sort_list_count = count($sort_list_arr);
        foreach($sort_list_arr as $sort_list_val){
          if($sort_list_val == 'PROJECT_FREQUENCY') continue;
          $field_lable = $field_lable_arr[$sort_list_val];
          $col_name = $field_lable_arr[$sort_list_val];
    ?>
          <td align="" colspan='' width="50%">
            <div class=<?php echo $filerLable_css?>>&nbsp;
            
            <?php 
          echo $field_lable;
          if($col_name == 'BFDR'){
            echo " > ";
          }else{
            echo " < ";
          }
          echo create_filter_list($field_lable,$col_name);
            ?>
            </div>
				</td>
    <?php   
          if($tmp_counter%2) echo "</tr><tr bgcolor=$tr_bgcolor>";
          $tmp_counter++;
        }
?>      
         <td align="" colspan=''><div class=<?php echo $filerLable_css?>>&nbsp;
              <input type=checkbox name='frm_NS' value='1' <?php echo ($frm_NS)?"checked":"";?>>
              Background Set&nbsp;&nbsp;
              <select name='frm_NS_group_id'>
              <option value='' selected>background list</option>
<?php   
        foreach($NSarr as $NSvalue){
          echo "<option value='".$NSvalue['ID']."' ".(($frm_NS_group_id==$NSvalue['ID'])?'selected':'').">".$NSvalue['Name']."<br>";
        }
?>
            </select>
            </div>
				</td>
<?php       if($sort_list_count == $tmp_counter+1){?>
          <tr bgcolor=<?php echo $tr_bgcolor?>>
<?php       }?>
          <td align="" colspan="2"><div class=<?php echo $filerLable_css?>>&nbsp;
       <?php 
        if($typeFrequencyArr){?>   
          <a href="javascript: popwin('mng_set_frequency.php?frm_frequency_name=<?php echo $freqency_file_name?>', 800, 800);"><?php echo $freqency_lable?></a>&nbsp;>&nbsp;
            <?php 
                create_filter_list('Fequency','frm_filter_Fequency_value');
            ?>
                %</div>
            <?php 
        }
        ?>   
          </td>
        </tr>                      
<?php 
      }else{
        if((isset($hitType) && $hitType == 'geneLevel') || isset($Is_geneLevel) && $Is_geneLevel){          
          $ExpectFilterLable = 'Spectral Count';
          $ExpectAlia = 'SpectralCount';
          //$ExpectItemName = 'SpectralCount';
          $ExpectItemName = 'frm_filter_Expect';
?>
              <td align="" width=50% colspan=2>
              <div class=<?php echo $filerLable_css?>>&nbsp;&nbsp;
        <?php if($filter_for == 'item_report'){?>
            <input type=checkbox name='frm_Expect_check' value='1' <?php echo ($theaction and $frm_Expect_check)?"checked":"";?>>
        <?php }?>
          <?php echo $ExpectFilterLable?>&nbsp;&nbsp;<b><</b>&nbsp;&nbsp;
  <?php 
          create_filter_list($ExpectAlia,$ExpectItemName);
  ?>
              </div>
  				    </td>
  						<td align="" colspan=2>
                <div class=<?php echo $filerLable_css?>>
                  &nbsp;&nbsp;
                </div>
              </td>
            </tr>                 
            <tr bgcolor=<?php echo $tr_bgcolor;?>>
  						<td align="" colspan=2><div class=<?php echo $filerLable_css?>>&nbsp;&nbsp;
        <?php if($filter_for == 'item_report'){?>    
          <input type=checkbox name='frm_PT_check' value='1' <?php echo ($theaction and $frm_PT_check)?"checked":"";?>>
        <?php }?>     
              Unique Group Peptide&nbsp;&nbsp;<b><</b>&nbsp;&nbsp;
                <?php 
                $Pep_numAlia = 'Unique';
                //$Pep_numItemName = 'Unique';
                $Pep_numItemName = 'frm_filter_Peptide_value';
                create_filter_list($Pep_numAlia,$Pep_numItemName);
            ?>            
  						</td>
        <?php 
        }else{
          $g_sin = "<";
          $ExpectAlia = $Expect;
          $ExpectItemName = 'frm_filter_Expect';            
          if(strstr($SearchEngine, 'iProphet')){
            $ExpectFilterLable = 'iProphet Probability';
          }elseif(strstr($SearchEngine, 'TPP_')){
            $ExpectFilterLable = 'TPP Probability';
          }else{
            if($SearchEngine == 'GPM'){
              $Score_lable = ' Expect';
              $SearchEngine_f = 'XTandem';
              $g_sin = ">";
            }else{
              $Score_lable = ' Score';
              $SearchEngine_f = $SearchEngine;
            }
            $ExpectFilterLable = $SearchEngine_f . $Score_lable;
          }
?>
                <td align="" width=50% colspan=2>
                <div class=<?php echo $filerLable_css?>>&nbsp;&nbsp;
        <?php if($filter_for == 'item_report'){?>
            <input type=checkbox name='frm_Expect_check' value='1' <?php echo ($theaction and $frm_Expect_check)?"checked":"";?>>
        <?php }?>           
        <?php echo $ExpectFilterLable?>&nbsp;&nbsp;<b><?php echo $g_sin?></b>&nbsp;&nbsp;
<?php 
          create_filter_list($ExpectAlia,$ExpectItemName);
?>
            </div>
				  </td>
<?php 
          $CoverageAlia = 'Coverage';
          $CoverageItemName = 'frm_filter_Coverage';
?>
  						<td align="" colspan=2><div class=<?php echo $filerLable_css?>>&nbsp;&nbsp;
        <?php if($filter_for == 'item_report'){?>    
          <input type=checkbox name='frm_Cov_check' value='1' <?php echo ($theaction and $frm_Cov_check)?"checked":"";?>>
        <?php }?>   
          Coverage&nbsp;&nbsp;<b><</b>&nbsp;&nbsp;
        <?php create_filter_list($CoverageAlia,$CoverageItemName);?>
                %</div> 
              </td>
            </tr>                 
            <tr bgcolor=<?php echo $tr_bgcolor;?>>
  						<td align="" colspan=2><div class=<?php echo $filerLable_css?>>&nbsp;&nbsp;
        <?php if($filter_for == 'item_report'){?>    
          <input type=checkbox name='frm_PT_check' value='1' <?php echo ($theaction and $frm_PT_check)?"checked":"";?>>
        <?php }?>     
              Peptide&nbsp;&nbsp;
          <select name="frm_filter_Peptide" size=1>
            <option value=''>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <option value='Pep_num' <?php echo ($frm_filter_Peptide=='Pep_num')?'selected':''?>>Total Peptide
            <option value='Pep_num_uniqe' <?php echo ($frm_filter_Peptide=='Pep_num_uniqe')?'selected':''?>>Unique Peptide
          </select>&nbsp;&nbsp;<b><</b>&nbsp;&nbsp;
          <?php 
          $Pep_numAlia = 'Pep_num';
          $Pep_numItemName = 'frm_filter_Peptide_value';
          create_filter_list($Pep_numAlia,$Pep_numItemName);
          ?>            
  			</td>
<?php       }?>  
        <td align="" colspan=2><div class=<?php echo $filerLable_css?>>&nbsp;&nbsp;
     <?php 
        if($typeFrequencyArr){
          if($hitType == 'TPP'){
            $Project_f_type = 'TPP';
            $frequency_file_type = 'tpp';
            
            
            
          }elseif($hitType == 'geneLevel'){
            if($filter_for == 'item_report'){
              $Project_f_type = 'GeneLevel_'.$SearchEngine;
              $frequency_file_type = 'GeneLevel_'.$SearchEngine;
            }else{
              $Project_f_type = $SearchEngine;
              $frequency_file_type = $SearchEngine;
            }
          }else{
            if($SearchEngine == 'GPM'){
              $Project_f_type = 'XTandem';
            }elseif($SearchEngine == 'MSPLIT'){  
              $Project_f_type = 'Gene Level';
            }else{  
              $Project_f_type = $SearchEngine;
            } 
            $frequency_file_type = $SearchEngine; 
          }                                  
          if($filter_for == 'item_report'){?>   
            <input type=checkbox name='frm_Frequency' value='1' <?php echo ($frm_Frequency)?"checked":"";?>>
         <?php }?>   
        <a href="javascript: pop_Frequency_set();">Frequency</a>&nbsp;&nbsp;        
          <select id="frm_filter_Fequency" name="frm_filter_Fequency" size=1>
            <option value=''>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <option value='Fequency' <?php echo ($frm_filter_Fequency=='Fequency')?'selected':''?>>(P) <?php echo $Project_f_type?> 
          <?php 
          $id_frequencyFileName_arr = array();
          if($filter_for == 'item_report'){
            if(($hitType == 'TPP' || $hitType == 'normal' || $hitType == 'geneLevel') && $type != 'Plate' && $type != 'Gel'){
              $id_frequencyFileName_arr = get_itemID_frequencyFileName_arr($type,$hitType,'itemID');
            }
            if(array_key_exists($item_ID, $id_frequencyFileName_arr)){
              $U_frequencyFileName_str = $id_frequencyFileName_arr[$item_ID];
              $U_frequencyFileName_arr = explode(",", $U_frequencyFileName_str);
              foreach($U_frequencyFileName_arr as $U_frequencyFileName){
                //if($hitType == 'TPP')
                $option_value = "U:".$U_frequencyFileName;
                
                $tmp_arr = explode('-',$U_frequencyFileName);
                if($hitType == 'TPP'){
                  if(strpos(trim($tmp_arr[2]), "TPP") !== 0) continue;
                  $option_lable = "(U) ".$tmp_arr[2];
                }elseif($hitType == 'geneLevel'){
                  if(strpos(trim($tmp_arr[2]), "geneLevel") !== 0) continue;
                  $option_lable = "(U) ".$tmp_arr[2];
                }elseif($hitType == 'normal'){
                  if(strpos(trim($tmp_arr[2]), "geneLevel") === 0 || strpos(trim($tmp_arr[2]), "TPP") === 0) continue;
                  $option_lable = "(U) ".$tmp_arr[2];
                }else{
                  continue;
                }
           ?>
             <option value='<?php echo $option_value?>' <?php echo ($frm_filter_Fequency==$option_value)?'selected':''?>><?php echo $option_lable?> 
           <?php 
              }
            }
          }else{
            if(isset($optionArr_for_user_d_frequency)){
              foreach($optionArr_for_user_d_frequency as $option_value => $option_lable){
              ?>
                 <option value='<?php echo $option_value?>' <?php echo ($frm_filter_Fequency==$option_value)?'selected':''?>><?php echo $option_lable?>
               <?php 
              }
            }  
          }
          if(isset($passedTypeArr)){
            foreach($passedTypeArr as $key => $value){
              if(!$key) continue;
              if(is_numeric($value)){
                $sub_name = "VS".$value;
              }else{
                $sub_name = $value;
              }
              echo "<option value=\"$key\" ".(($frm_filter_Fequency==$key)?'selected':'').">(G) $sub_name";
            }
          }
          ?>           
          </select>&nbsp;&nbsp;<b>></b>&nbsp;&nbsp;
          <input type=hidden id='frequency_file_type' value='<?php echo $frequency_file_type?>'>
          <input type=hidden id='related_project_id' value='<?php echo $AccessProjectID?>'> 
          <?php 
          if($filter_for == 'item_report'){
              $tmp_icon_color = str_replace("#", "", $typeFrequencyArr['Color']);
              create_filter_list('Fequency','frequencyLimit');
          ?>
              % <img src='./comparison_results_create_image.php?lableBgc=<?php echo $tmp_icon_color?>&filter_for=<?php echo $filter_for?>' border=0></div>  
          <?php     
          }else{   
              create_filter_list('Fequency','frm_filter_Fequency_value');
          ?>
              %</div>
          <?php 
          }
        }
      ?>   
        </td>
      </tr>
      <?php 
      if($typeExpArr){
        $tmpSpan = 4;
        $trhead = "<tr bgcolor='$tr_bgcolor'>";
        $trend = "</tr>";
        if($typeExpArr[0]['Alias'] == 'NS' && strstr($SearchEngine, 'TPP_')){
          $tmpSpan = 2;
        }elseif($typeExpArr[0]['Alias'] != 'NS' && !strstr($SearchEngine, 'TPP_')){
          $trhead = "";
          $trend = "";
        }
        
        echo $trhead;
        if($typeExpArr[0]['Alias'] == 'NS'){
          if($filter_for == 'item_report'){
?> 
        <td colspan=<?php echo $tmpSpan?>><div class=<?php echo $filerLable_css?>>&nbsp;
          <input type=checkbox name='frm_NS' value='1' <?php echo (($theaction and $frm_NS==1))?"checked":"";?>>
<?php    
            if(!isset($frm_NS_group_id) || !$frm_NS_group_id){
              $frm_NS_group_id = '';
            }
            get_NS_geneID($NSfilteIDarr,$frm_NS_group_id);
            $SQL = "SELECT `ID`,`Name` FROM `ExpBackGroundSet` WHERE `ProjectID`='$AccessProjectID'";
            $NSarr = $HITSDB->fetchAll($SQL);
?>
            <select name='frm_NS_group_id' class=maintext>
              <option value='' selected>background list</option>
<?php   
            $tmp_icon_color = str_replace("#", "", $typeExpArr[0]['Color']);
            foreach($NSarr as $NSvalue){
              echo "<option value='".$NSvalue['ID']."' ".(($frm_NS_group_id==$NSvalue['ID'])?'selected':'').">".$NSvalue['Name']."<br>";
            }
?>
            </select>
              <a href="javascript: pop_exp_filter_set('<?php echo $typeExpArr[0]['ID']?>');"><img src=./images/icon_view.gif border=0></a>                               .
              <img src='./comparison_results_create_image.php?lableBgc=<?php echo $tmp_icon_color?>&filter_for=<?php echo $filter_for?>' border=0> 
            </div>
          </td>
<?php   
          }else{
?>
          <td align="" colspan=<?php echo $tmpSpan?>><div class=<?php echo $filerLable_css?>>&nbsp;
            <input type=checkbox name='frm_NS' value='1' <?php echo ($frm_NS)?"checked":"";?>>
            Background Set&nbsp;&nbsp;
            <select name='frm_NS_group_id'>
            <option value='' selected>background list</option>
<?php   
            foreach($NSarr as $NSvalue){
              echo "<option value='".$NSvalue['ID']."' ".(($frm_NS_group_id==$NSvalue['ID'])?'selected':'').">".$NSvalue['Name']."<br>";
            }
?>
          </select>
          </div>
		  </td>
<?php    
          }
        }
        if(strstr($SearchEngine, "TPP_")){
?>  
          <td align="" colspan=<?php echo $tmpSpan?>><div class=<?php echo $filerLable_css?>>&nbsp;&nbsp;
        <?php if($filter_for != 'item_report'){?>
            min XPRESS Ratio:&nbsp;
            <input type="text" name="frm_min_XPRESS" size="4" maxlength=10 value="<?php echo $frm_min_XPRESS;?>">
            &nbsp;&nbsp;&nbsp;max XPRESS Ratio:&nbsp;
            <input type="text" name="frm_max_XPRESS" size="4" maxlength=10 value="<?php echo $frm_max_XPRESS;?>">
        <?php }?>     
           </div>
		  </td>
        </tr>     
<?php 
        }
        echo $trend;          
        $filterCount = 0;
        foreach($typeExpArr as $Value){
          if($Value['Alias'] == 'NS') continue;
          if(strstr($SearchEngine, 'TPP_')){
            if($Value['Alias'] == 'AW') array_push($exists_exp_arr, $Value['Alias']);
            if(in_array($Value['Alias'], $exists_exp_arr)) continue;
          }
          $frmName = 'frm_' . $Value['Alias'];
          $tmp_icon_color = str_replace("#", "", $Value['Color']);
?>              
        <td width='25%' bgcolor="<?php echo $tr_bgcolor;?>" nowrap>
          <div class=<?php echo $filerLable_css?>>&nbsp;
          <input type=checkbox name='<?php echo $frmName?>' value='1' <?php echo ($$frmName)?"checked":"";?>>                  
          <a href="javascript: pop_filter_set('<?php echo $Value['ID']?>');"><?php echo $Value['Name']?>
<?php               if($filter_for == 'item_report'){  ?>
              <img src='./comparison_results_create_image.php?lableBgc=<?php echo $tmp_icon_color?>&filter_for=<?php echo $filter_for?>' border=0>
<?php               }?> 
          </a>                  
          </div>
          </td>
<?php 
          $filterCount++;
          if(!($num = $filterCount % 4)){
            echo "</tr><tr>";
          }
        }  
        if(isset($num) && $num && $num < 4){
          for($i=$num; $i<4; $i++){
            echo "<td bgcolor='$tr_bgcolor' width='25%'>&nbsp;</td>";
          } 
        }
      }
?>                
        </tr> 
<?php 
    }        
?>
        </table>
        </td>
      </tr>          
      <tr bgcolor="<?php echo $edgeLine_color?>">
        <td valign=bottom colspan=2>
          <table border=0 cellspacing="1" cellpadding="1" width=100% bgcolor="#f5f8fa">
              <tr bgcolor=<?php echo $tr_title_bgcolor;?>>
                <td colspan=4><div class=maintext><b>Bio Filters</b></div></td>
              </tr>             
              <tr>
<?php 
        $filterCount = 0;              
        foreach($typeBioArr as $Value) {
        //if($Value['Alias'] == 'BT') continue;
          $frmName = 'frm_' . $Value['Alias'];
          $tmp_icon_color = str_replace("#", "", $Value['Color']);
?>              
                <td width='25%' bgcolor="<?php echo $tr_bgcolor;?>" nowrap><div class=<?php echo $filerLable_css?>>&nbsp;
                  <input type=checkbox name='<?php echo $frmName?>' value='1' <?php echo ($$frmName)?"checked":"";?>>                  
                  <a href="javascript: pop_filter_set('<?php echo $Value['ID']?>');"><?php echo $Value['Name']?>
<?php 
            if($filter_for == 'item_report'){
?>
              <img src='./comparison_results_create_image.php?lableBgc=<?php echo $tmp_icon_color?>&filter_for=<?php echo $filter_for?>' border=0>
<?php 
            }
?>    
                  </a>                  
                  </div>
                </td>
<?php 
          $filterCount++;
          if(!($num = $filterCount % 4)){
            echo "</tr><tr>";
          }  
        }
        if(isset($num) && $num < 4){
          for($i=$num; $i<4; $i++){
            echo "<td bgcolor='$tr_bgcolor'>&nbsp;</td>";
          } 
        }
?>                
            </tr>  
          </table>
        </td>
      </tr>
      <tr bgcolor="<?php echo $edgeLine_color?>">
        <td valign=bottom colspan=2>
<?php 
        if(isset($php_file_name) && $php_file_name != "comparison_results_image.php"){
          if(!isset($is_uploaded) || !$is_uploaded){
            include("./filter_biogrid.inc.php");
          }
        }  
?>
      </td></tr>
<?php 
      }
      if(!$filter_add_checkbox && isset($frm_color_mode) && $frm_color_mode == 'shared'){
?>  
      <tr bgcolor="<?php echo $edgeLine_color;?>">
        <td colspan='2'>
        <table align="center" bgcolor='' cellspacing="1" cellpadding="1" border="0" width=100%>
        <tr bgcolor="<?php echo $tr_bgcolor;?>">
        <td width=25% align="right"><div class=<?php echo $filerLable_css?>>&nbsp;&nbsp;<b><?php echo ($php_file_name == "SAINT_comparison_results_table")?'Prey':'Hit'?> found in</b> all <?php echo $typeLable;?>s&nbsp;&nbsp;&nbsp;</div></td>
        <td width=8% align=center bgcolor='<?php echo $red;?>'><input type="checkbox" name="frm_red" value="y" <?php echo ($frm_red)?'checked':'';?>></td>
<?php 
        if($php_file_name == "SAINT_comparison_results_table" || !in_array($contrlColor, $itemIdIndexArr)){
?>
        <td width=25% align="right"><div class=<?php echo $filerLable_css?>>&nbsp; &nbsp; more than one <?php echo $typeLable;?>s&nbsp;&nbsp;&nbsp;</div></td>
        <td width=8% align=center bgcolor='<?php echo $green;?>'><input type="checkbox" name="frm_green" value="y" <?php echo ($frm_green)?'checked':'';?>></td>
<?php 
        }
?>      
        <td width=15% align=right nowrap><div class=<?php echo $filerLable_css?>>&nbsp; &nbsp; one <?php echo $typeLable;?>&nbsp;&nbsp;</div></td>
        <td width=8% align=center bgcolor='<?php echo $blue;?>'><input type="checkbox" name="frm_blue" value="y" <?php echo ($frm_blue)?'checked':'';?>></td>
        <td >&nbsp;&nbsp;</td>
        </tr>
        </table>
        </td>
      </tr> 
<?php 
      }
?>              
    </table>
  </td></tr>
  </table>  
</DIV>

    
   
      
