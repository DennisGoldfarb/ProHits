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
$frm_SearchEngine = '';
$frm_selected_element_str = '';
$frm_order_by = '';
$elementsPerPage = 500;
$currentType = 'Bait';
$itemType = 'Bait';
$frm_groups = 'Bait';
$currentPage = 1;
$action = '';
$offset = 0;
$tb_color = '#969696';
$SearchEngine = '';
$displaySearchEngine = 0;
$titleBarW = '90%';
$MascotHasHit = 0;
$GPMHasHit = 0;
$SEQUESTHasHit = 0;
$TPP_MascotHasHit = 0;
$TPP_GPMHasHit = 0;
$TPP_SEQUESTHasHit = 0;
$subAction = '';
$switch_SearchEngine = 0;
$IDs = '';
$firstDisplay = '';
$selected_group_id = '';
$frm_search_by = '';
$frm_user = '';
$frm_frequency_name = '';
$frm_frequency_description = '';
$old_base_name = '';

$bg_tb_header = '#7eb48e';
$tb_color = '#e3e3e3';
$tb_color2 = '#d1e7db';
$tb_color3 = '#e7e7cf';

$Is_geneLevel = 0;
$for_frequencyU = "Y";


require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");
ini_set("memory_limit","-1");
ini_set('max_execution_time', 0); 


/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/
//---------------------------------------------------------------------------------------
$SearchEngineConfig_arr = get_project_SearchEngine();
$hits_searchEngines = hits_searchEngines('get', $AccessProjectID,$HITSDB);

$filter_arr = array();
$filter_arr_U = array();
$exist_Hits_tables_arr = array();

$filter_arr_tmp0['else'] = array('Expect'=>'','Pep_num'=>'','Pep_num_uniqe'=>'');
$filter_arr_tmp0['Mascot'] = array('Expect'=>'','Pep_num'=>'','Pep_num_uniqe'=>'');
$filter_arr_tmp0['GPM'] = array('Expect2'=>'','Pep_num'=>'','Pep_num_uniqe'=>'');
$filter_arr_tmp0['tpp'] = array('PROBABILITY'=>'','TOTAL_NUMBER_PEPTIDES'=>'','UNIQUE_NUMBER_PEPTIDES'=>'');
$geneLevel_filter_arr = array('SpectralCount'=>'','Unique'=>'');

foreach($hits_searchEngines as $hits_searchEngine){
  if(strstr($hits_searchEngine, 'GeneLevel_')){
    $filter_arr[$hits_searchEngine] = $geneLevel_filter_arr;
  }elseif(stristr($hits_searchEngine, 'TPP_')){
    if(!array_key_exists('tpp', $filter_arr)){
      $filter_arr['tpp'] = $filter_arr_tmp0['tpp'];
    }  
  }else{
    //if($hits_searchEngine == 'MSGF') continue;
    if($hits_searchEngine == 'Mascot' || $hits_searchEngine == 'GPM'){
      $filter_arr[$hits_searchEngine] = $filter_arr_tmp0[$hits_searchEngine];
    }else{
      $filter_arr[$hits_searchEngine] = $filter_arr_tmp0['else'];
    }
  }
}

$filter_arr_U['Mascot'] = array('Expect'=>'','Expect2'=>'','Pep_num'=>'','Pep_num_uniqe'=>'');
$filter_arr_U['tpp'] = array('PROBABILITY'=>'','TOTAL_NUMBER_PEPTIDES'=>'','UNIQUE_NUMBER_PEPTIDES'=>'');
$filter_arr_U['geneLevel'] = array('SpectralCount'=>'','Unique'=>'');

$DB_name = $HITSDB->selected_db_name;
$exist_Hits_tables_arr = exist_hits_table($DB_name);
$filter_arr_for_status = $filter_arr;

$SearchEngine_lable_arr = get_SearchEngine_lable_arr($SearchEngineConfig_arr);

//=========================================================================================================

$filter_var_arr = array();
foreach($filter_arr as $filter_key => $filter_sub_arr){
  foreach($filter_sub_arr as $filter_sub_key => $filter_sub_val){
    $filter_var_arr[] = $filter_key.'00'.$filter_sub_key;
  }
}
foreach($filter_var_arr as $filter_var_val){
  $$filter_var_val = '';
}

$filter_lable_arr = array('Expect'=>'Score <',
                          'Pep_num'=>'Total peptide <',
                          'Pep_num_uniqe'=>'Unique peptide <',
                          'TOTAL_NUMBER_PEPTIDES'=>'Total peptide <',
                          'UNIQUE_NUMBER_PEPTIDES'=>'Unique peptide <',
                          'Expect2'=>'Expect >',
                          'PROBABILITY'=>'Probability <',
                          'SpectralCount'=>'Total peptide <',
                          'Unique'=>'Unique peptide <'
                          );
//============================================================================================================


$Prohits_Data_dir = STORAGE_FOLDER . "Prohits_Data/";
if(!_is_dir($Prohits_Data_dir)){
  _mkdir_path($Prohits_Data_dir);
}

$Frequency_log_dir = $Prohits_Data_dir."Frequency_log";
$Frequency_log_U_dir = $Prohits_Data_dir."Frequency_U_log";


if(!_is_dir($Frequency_log_dir)){
  _mkdir_path($Frequency_log_dir);
}
if(!_is_dir($Frequency_log_U_dir)){
  _mkdir_path($Frequency_log_U_dir);
}

$frequency_dir = $Prohits_Data_dir . "frequency";
$sub_frequency_dir = $Prohits_Data_dir . "subFrequency";
$user_frequency_dir = $Prohits_Data_dir . "user_d_frequency/P_$AccessProjectID";

$frequency_dir_arr['P'] = $frequency_dir;
$frequency_dir_arr['G'] = $sub_frequency_dir;
$frequency_dir_arr['U'] = $user_frequency_dir;

foreach($frequency_dir_arr as $tmpDir){
  if(!_is_dir($tmpDir)){
    _mkdir_path($tmpDir);
  }
}

if($firstDisplay == 'y'){
  if($theaction == 'modify_frequency'){
    $tmp_frequency_name_arr = explode(':',$frm_frequency_name);
    $old_base_name = $frm_frequency_name;
    $old_full_name = $frequency_dir_arr['U'].'/'.$tmp_frequency_name_arr[1];
    
    $u_f_handle = @fopen($old_full_name, "r");
    if(!$u_f_handle){
      echo "can not open file $old_full_name";
      exit;
    }
    while(!feof($u_f_handle)) {
      $buffer = fgets($u_f_handle, 4096);
      $buffer = trim($buffer);
      if(preg_match('/^Type:(.+)$/', $buffer, $matchse)){
        if(trim($matchse[1]) == 'Sample'){
          $item_Type = 'Band';
        }elseif(trim($matchse[1]) == 'Experiment'){
          $item_Type = 'Exp';
        }else{
          $item_Type = 'Bait';
        }  
      }elseif(preg_match('/^Item id list:(.+)$/', $buffer, $matchse)){
        edit_comparison_session(trim($matchse[1]), $item_Type, 'new');
      }elseif(preg_match('/^Description:(.+)$/', $buffer, $matchse)){
        $frm_frequency_description = $matchse[1];
        break;
      } 
    }
    fclose($u_f_handle);
  }else{
    //edit_comparison_session('', "Bait", 'new');
    $_SESSION['com_BaitIDs'] = '';    
  	$_SESSION['com_ExperimentIDs'] = '';
  	$_SESSION['com_SampleIDs'] = '';
  }

  $selected_id_str_normal = '';
  $selected_id_str_TPP = '';
  $session_Type = get_comparison_session_Type();
    
  if($session_Type == 'Bait'){
    $currentType = 'Bait';
    $clickedId = "tabOn1";
    $selected_id_str = get_comparison_session("Bait");
    $tmp_id = 'BaitID';
  }elseif($session_Type == 'Exp'){
    $currentType = 'Exp';
    $clickedId = "tabOn3";
    $selected_id_str = get_comparison_session("Exp");
    $tmp_id = 'ExpID';
  }elseif($session_Type == 'Sample'){
    $currentType = 'Band';
    $clickedId = "tabOn2";
    $selected_id_str = get_comparison_session("Sample");
    $tmp_id = 'BandID';
  }
  
  if($selected_id_str){
    if($session_Type == 'Exp'){
      $SQL = "SELECT 
              B.ExpID AS ID
              FROM Hits H, Band B
              WHERE B.ExpID IN ($selected_id_str) AND B.ID=H.BandID";
    }else{
      $SQL = "SELECT 
              $tmp_id AS ID
              FROM Hits 
              WHERE $tmp_id IN ($selected_id_str)";
    }          
    $tmpItemArr = $HITSDB->fetchAll($SQL);
    
    $tmpIDarr = array();
    foreach($tmpItemArr as $tmpItemVal){
      if(!in_array($tmpItemVal['ID'], $tmpIDarr)){
        array_push($tmpIDarr, $tmpItemVal['ID']);
        if($selected_id_str_normal) $selected_id_str_normal .= ",";
        $selected_id_str_normal .= $tmpItemVal['ID'].":C_FFFFFF:";
      }
    }  
    if($selected_id_str_normal) $SearchEngine = 'normal';
    $selected_id_str_normal = $tmp_id."@@".$selected_id_str_normal;   
        
    if($session_Type == 'Exp'){
      $SQL = "SELECT 
              B.ExpID AS ID
              FROM TppProtein H, Band B
              WHERE B.ExpID IN ($selected_id_str) AND B.ID=H.BandID";
    }else{
      $SQL = "SELECT 
              $tmp_id AS ID
              FROM TppProtein  
              WHERE $tmp_id IN ($selected_id_str) GROUP BY $tmp_id";
    }          
    $tmpItemArr = $HITSDB->fetchAll($SQL);
    
    $tmpTPPIDarr = array();
    foreach($tmpItemArr as $tmpItemVal){
      if(!in_array($tmpItemVal['ID'], $tmpTPPIDarr)){
        array_push($tmpTPPIDarr, $tmpItemVal['ID']);
        if($selected_id_str_TPP) $selected_id_str_TPP .= ",";
        $selected_id_str_TPP .= $tmpItemVal['ID'].":C_FFFFFF:";
      }
    }
  }
  if(!$SearchEngine) $SearchEngine = 'normal';
  $tmp_frequency_name_arr = explode('-', $frm_frequency_name);
  $frm_frequency_name = basename(end($tmp_frequency_name_arr),'.csv');
  
  
  if($Is_geneLevel){
    if(!$SearchEngine) $SearchEngine = 'MSPLIT';
  }else{
    if(!$SearchEngine) $SearchEngine = 'Mascot';
  }  
}

if($action == 'creatList'){
	create_source_element_list();
	exit;
}elseif($action == 'add_option'){
  edit_comparison_session($IDs, $currentType);
  exit;
}elseif($action == 'remove_option'){
  edit_comparison_session($IDs, $currentType, "remove");
  create_source_element_list();
	exit;
}

require("site_header.php");

$user_frequency_files = array();
if(!_is_dir($frequency_dir_arr['U'])){
  _mkdir_path($frequency_dir_arr['U']);
}

$Frequency_filters_file = $Frequency_log_dir."/P$AccessProjectID.log";
$Frequency_filters_U_file = $Frequency_log_U_dir."/P$AccessProjectID.log";
if($theaction == 'display_frequency' || $theaction == 'generate_frequency' || $theaction == "delete_frequency" || strstr($theaction,'update_frequency')){
  
  if($theaction != 'display_frequency'){
    if($theaction != 'generate_frequency' && $theaction != "delete_frequency"){  
      $filter_status_arr = get_new_filter_status();
    }  
    if(strstr($theaction,'update_frequency')){
    
      $filter_file_arr = array();
      if(is_file($Frequency_filters_file)){
        $filter_file_arr_tmp = file($Frequency_filters_file);
        $filter_file_arr_tmp = array_reverse($filter_file_arr_tmp);
        foreach($filter_file_arr_tmp as $filter_file_val_tmp){
          if(strpos($filter_file_val_tmp, "#####") === 0) break;
          $filter_file_arr[] = trim($filter_file_val_tmp);
        }
      }
      
      /*
      echo "<pre>@@@@@@@@***************************************************";
      echo "\$filter_status_arr<br>";
      print_r($filter_status_arr);
      echo "\$filter_file_arr<br>";
      print_r($filter_file_arr);
      echo "@@@@@@@@@********************************************************</pre>";
      */
      
      if($theaction == 'update_frequency_p'){
        $diff_arr = array_diff($filter_status_arr, $filter_file_arr);
        if($diff_arr){
          update_log_for_individual($diff_arr,$frm_frequency_name);
        }
      }elseif($theaction == 'update_frequency_all'){
        if(array_diff($filter_status_arr, $filter_file_arr) || array_diff($filter_file_arr,$filter_status_arr)){
          $fp = fopen($Frequency_filters_file, 'a');
          fwrite($fp, "#####".@date("Y-m-d H:i:s")."---Changed by $AccessUserName\r\n");
          foreach($filter_status_arr as $filter_status_val){
            fwrite($fp, $filter_status_val."\r\n");
          }  
          fclose($fp);
        }
      }
    }
    ?>
    <div style='display:block' id='process_2'>
      <img src='./images/process.gif' border=0>
    </div>
    <?php 
    ob_flush();
    flush();
    
    if($theaction == 'update_frequency_all'){
      updata_frequency();
    }elseif($theaction == 'update_frequency_projects'){
      updata_project_frequencys();
    }elseif($theaction == 'update_frequency_groups'){
      updata_group_frequencys();
    }elseif($theaction == 'update_frequency_p'){
      if(preg_match('/P:P\d+_(.+)/', $frm_frequency_name, $matches)){
        if(strstr($matches[1], 'tpp')){
          update_project_frequency($matches[1],'TppProtein');
        }elseif(strstr($matches[1], 'geneLevel')){
          //update_project_frequency($matches[1],'Hits_GeneLevel');
        }else{
          $tmp_ES_str = $matches[1];
          preg_match('/(\w+)_/', $tmp_ES_str, $matches_2);
          if(isset($matches_2[1])){
            update_project_frequency_SEs($matches_2[1]);
          }else{
            update_project_frequency_all();
          }
        }  
      }
    }elseif($theaction == 'update_frequency_g'){
      $g_name_arr = explode(':',$frm_frequency_name);
      update_single_group_frequeny($g_name_arr[1]);
    }elseif($theaction == 'generate_frequency' || $theaction == 'update_frequency_u'){    
      if($theaction == 'update_frequency_u'){
        delete_U_frequency($old_base_name);
      }
      $tmp_frequency_name_arr = explode('-',$frm_frequency_name);
      $frequency_fileName = $tmp_frequency_name_arr[2];     
    	$ret = generate_user_defined_frequency($frm_selected_list_str);      
      $frm_frequency_name = 'U:'.$frm_frequency_name.'.csv';
    }elseif($theaction == "delete_frequency"){
      delete_U_frequency($frm_frequency_name);
    }
  }
     
  $all_frequency_name_lable_arr = array();
  $all_frequency_info_arr = array();
  
  $note_type_id_arr = get_all_frequency_info();

  foreach($all_frequency_info_arr as $key => $val){
    uasort($all_frequency_info_arr[$key], "cmp_m_time_r");
  }

  $filter_arr_old = get_old_filter_status();
  
  foreach($filter_arr_old as $filter_arr_key => $filter_arr_val){
    $filter_single_arr = $filter_arr_val;
    foreach($filter_single_arr as $filter_single_key => $filter_single_val){
      $filter_arr[$filter_arr_key][$filter_single_key] = $filter_single_val;
    }
  }
?>
<html>  
<script language="JavaScript" type="text/javascript">
  function create_u_d_frequency(theForm){
    theForm.firstDisplay.value = 'y';
  } 
  function confirm_delete_uFrequency(file_name,u_frequency_name){
    var theForm = document.frm_frequency_sub;
    if(confirm("Are you sure that you want to delete the frequency '" + u_frequency_name + "'?")){
      theForm.theaction.value = "delete_frequency";
      theForm.frm_frequency_name.value = file_name;   
      theForm.submit();
    }
  } 
  function update_Pfrequency_single(theaction,frequency_name){
    var theForm = document.frm_frequency_table;
    theForm.theaction.value = theaction;
    theForm.frm_frequency_name.value=frequency_name;
    theForm.submit();  
  }
   function update_Pfrequency_all(theaction){
    var theForm = document.frm_frequency_table;
    theForm.theaction.value = theaction;
    theForm.submit();  
  }   
</script>  
<body>
<div style="width:95%;border: red solid 0px;">
  <div style="width:100%;height:40px;border: red solid 0px;text-align:left;padding-top:10px;">
    <br>&nbsp; <font color="navy" face="helvetica,arial,futura" size="4"><b>Frequencies</b></font>
<?php     
    if($AccessProjectName){
        echo "  <font color='red' face='helvetica,arial,futura' size='3'><b>(Project $AccessProjectID: $AccessProjectName)</b></font>";
    }
?> 
  </div>
  <div style="width:100%;border: red solid 0px">
    <hr size=1>
  </div>
  <div style="width:800px;border: blue solid 0px">
  <FORM ACTION="<?php echo $PHP_SELF;?>" NAME="frm_frequency_sub" METHOD="POST">
    <input type=hidden name=theaction value="">
    <input type=hidden name=firstDisplay value="">
    <input type=hidden name=frm_frequency_name value="">
  </FORM>
      
  <FORM ACTION="<?php echo $PHP_SELF;?>" NAME="frm_frequency_table" METHOD="POST">
    <input type=hidden name=theaction value="">
    <input type=hidden name=firstDisplay value="">
    <input type=hidden name=frm_frequency_name value="">    
    <div class=tableheader_black style="width:100%;border: blue solid 0px;text-align:right;">
      <a href="javascript: update_Pfrequency_all('update_frequency_all')"><b>[Updating project and group frequencies]</b></a>&nbsp;&nbsp;&nbsp;
      <a href="<?php echo $PHP_SELF;?>?firstDisplay=y"><b>[Creating user defined frequency]</b></a>
    </div>
    <hr size=1>
<?php 
  $bgcolordark = "#c5b781";
  $frequency_type = '';
 
  foreach($all_frequency_name_lable_arr as $frequency_key => $frequency_val){
    if($frequency_key == 'P'){
      $frequency_title = "Project frequencies";
      $update_type = 'update_frequency_projects';
      $update_lable = '[Updating project frequencies]';
    }elseif($frequency_key == 'U'){
      $frequency_title = "User defined frequencies";
      $update_type = '';
    }elseif($frequency_key == 'G'){
      $frequency_title = "Group frequencies  (all search engine hits)";
      $update_type = 'update_frequency_groups';
      $update_lable = '[Updating project groups]';
    }    
?>  
    <div class=tableheader_black style="float:left;width:50%;border: blue solid 0px;text-align:left;padding-top:10px;">
      <?php echo $frequency_title?>
    </div>
    <div class=tableheader_black style="float:right;width:38%;border: blue solid 0px;text-align:right;padding-top:10px;">
<?php   if($update_type and 0){?>
          <a href="<?php echo $PHP_SELF;?>?theaction=<?php echo $update_type?>"><?php echo $update_lable?></a>
<?php   }else{
      echo "&nbsp;&nbsp;";
    }
?>               
    </div>
    <table border="0" cellpadding="0" cellspacing="1" width="100%">
    <tr bgcolor="">    
      <td width="150" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
        <div class=tableheader>Frequency</div>
      </td>
<?php   if($frequency_key != "G"){?>
      <td width="100" bgcolor="<?php echo $bgcolordark;?>" align="center"><div class=tableheader>
        <div class=tableheader>Filters</div>
      </td>
<?php   }?>      
      <td width='' bgcolor="<?php echo $bgcolordark;?>" align=center nowrap><div class=tableheader>
        <div class=tableheader>Description</div>
      </td>
<?php   if($frequency_key == "U"){?>
      <td width="100" bgcolor="<?php echo $bgcolordark;?>" align="center"><div class=tableheader>
        <div class=tableheader>Created By</div>
      </td>
<?php   }?>
      <td width="150" bgcolor="<?php echo $bgcolordark;?>" align=center>
        <div class=tableheader>Created/updated time</div> 
      </td>
      <td width="100" bgcolor="<?php echo $bgcolordark;?>" align="center">
        <div class=tableheader>Options</div>
      </td>
    </tr>
<?php 
    $sub_frequency_tmp = $all_frequency_info_arr[$frequency_key];
    ksort($sub_frequency_tmp);

    $sub_frequency = array();
    $tmp_tmp_sub = array();
    foreach($sub_frequency_tmp as $sub_frequency_key => $sub_frequency_val){
      if(strstr($sub_frequency_key, 'GeneLevel_')){
        $tmp_tmp_sub[$sub_frequency_key] = $sub_frequency_val;
      }else{
        $sub_frequency[$sub_frequency_key] = $sub_frequency_val;
      }
    }
    foreach($tmp_tmp_sub as $tmp_tmp_sub_key => $tmp_tmp_sub_val){
      $sub_frequency[$tmp_tmp_sub_key] = $tmp_tmp_sub_val;
    }
    $engineKey_arr = array();
    foreach($sub_frequency as $sub_key => $sub_val){
      if(stristr($sub_key, 'TPP')){
        if(!in_array('TPP', $exist_Hits_tables_arr)){
          delete_unexist_frequency($sub_key);
          continue;
        }
      }elseif(stristr($sub_key, 'geneLevel')){
        if(!in_array('geneLevel', $exist_Hits_tables_arr)){
          delete_unexist_frequency($sub_key);
          continue;
        }
      }else{
        if(!array_key_exists('Hits', $exist_Hits_tables_arr)){
          delete_unexist_frequency($sub_key);
          continue;
        }
      }
    
      $bgcolor = $TB_CELL_COLOR;
      $tmp_sub_val = explode(')',$frequency_val[$sub_key]);
      if($frm_frequency_name == $sub_key) $bgcolor = "#e2e2e2";
      if($frequency_key == "U"){
        $owner_arr = explode('--',$sub_val['owner']);
        $owner_name = $owner_arr[0];
        $owner_id = $owner_arr[1];
      }
      if($theaction == 'update_frequency_projects' && $frequency_key == 'P' || $theaction == 'update_frequency_groups' && $frequency_key == 'G'){
        $bgcolor = "#e2e2e2";
      }elseif($theaction == 'update_frequency_all' && ($frequency_key == 'P' || $frequency_key == 'G')){
        $bgcolor = "#e2e2e2";
      }
?>    
    <tr bgcolor='<?php echo $bgcolor;?>' onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $bgcolor;?>');">
      <td width="" align="left" valign="top" nowrap style="padding: 0px 0px 0px 5px;">
        <div class=maintext>
        
        
<?php if($frequency_key == 'G'){
    if(isset($sub_val['Icon'])){
      $tmp_h = 'V';
      if($sub_val['Type'] == 'Export'){
    ?>
        <span class=tdback_star_image style="float:left;border:blue solid 0px;padding:4px 0px 0px 0px;height:17px;width:18px">  
          <?php echo $sub_val['Initial'];?>
        </span>
        <b><?php echo $tmp_sub_val[1]?>&nbsp;(V<?php echo $sub_val['Initial']?>)</b>&nbsp;
    <?php  
      }else{
        echo "<img src='./gel_images/".$sub_val['Icon']."' border=0>";
    ?>
        <b><?php echo $tmp_sub_val[1]?>&nbsp;(<?php echo $sub_val['Initial']?>)</b>&nbsp;
    <?php 
      }
    }
  }else{
?>        
        <b><?php echo $tmp_sub_val[1]?></b>&nbsp;
<?php }?>          
          
        </div>
      </td>
<?php     if($frequency_key != "G"){?>
      <td width="" align="left" nowrap>
        <div class=maintext>
<?php  
        if($frequency_key == "P"){
          foreach($filter_arr as $engineKey => $field_arr){
            $pattern = $engineKey."_frequency.csv";
            $pattern = "/".$pattern."/i";
            if(preg_match($pattern, $sub_key) && !in_array($engineKey, $engineKey_arr)){
              $engineKey_arr[] = $engineKey;
              foreach($field_arr as $field_key => $field_val){
                $frmName = $engineKey.'00'.$field_key;
                if($AccessUserType == 'Admin'){
                  echo $filter_lable_arr[$field_key].'&nbsp;&nbsp;';
                  $biggestNum = get_max_Num($engineKey,$field_key)."<br>";
                  $$frmName = $field_val;
                  create_filter_selections($field_key,$frmName,$biggestNum);
                  echo "<br>";
                }else{
    
                  echo $filter_lable_arr[$field_key].' '.$field_val.'<br>';
                  echo "<input type=hidden name='$frmName' value='$field_val'>";
                }
              }
            }            
          }
        }else{
          if(isset($sub_val['Filter_SQL'])){
            $tmp_sub_arr = explode('||',$sub_val['Filter_SQL']);
            if(count($tmp_sub_arr)>1){
              $tmp_arr1 = explode(',',$tmp_sub_arr[0]);
              $tmp_arr2 = explode(',',$tmp_sub_arr[1]);
              foreach($tmp_arr2 as $tmp_val){
                if(!in_array($tmp_val, $tmp_arr1)){
                  $tmp_arr1[] = $tmp_val;
                }
              }
              sort($tmp_arr1);
              $tmp_str = implode("<br>",$tmp_arr1);
              echo $tmp_str;
            }else{
              $tmp_arr = explode("&&",$sub_val['Filter_SQL']);
              if(count($tmp_arr)>1){
                $tmp_arr[1] = str_replace(",", "<br>", $tmp_arr[1]);
                echo $tmp_arr[1];
              }  
            }
          }
        }
?>
          &nbsp;
        </div>
      </td>
<?php     }?>      
      <td width="" align="left"><div class=maintext>&nbsp;
          <?php echo $sub_val['description']?>&nbsp;
        </div>
      </td>
<?php     if($frequency_key == "U"){?>
      <td width="" align="left"><div class=maintext>&nbsp;
          <?php echo $owner_name?>&nbsp;
        </div>
      </td>
<?php     }?>
      <td width="" align="center"><div class=maintext>&nbsp;
          <?php echo $sub_val['m_time']?>&nbsp;
        </div>
      </td>
      <td width="" align="center"><div class=maintext>&nbsp;
<?php     if($frequency_key != 'U'){
        if($AUTH->Access){
          if($frequency_key == 'P'){
            $pg = 'p';
          }else{
            $pg = 'g';
          }
    ?>
        <a  title='view <?php echo $tmp_sub_val[1]?>' href="javascript: popwin('mng_set_frequency.php?frm_frequency_name=<?php echo $sub_key?>', 800, 800);"><b><img border="0" src="images/icon_view.gif" alt="View"></b></a>&nbsp;
        <a  title='update <?php echo $tmp_sub_val[1]?>' href="javascript: update_Pfrequency_single('update_frequency_<?php echo $pg?>','<?php echo $sub_key?>')"><img border="1" src="images/icon_update.png" alt="Update"></a>&nbsp;
    <?php 
        }else{
          echo "<img src=\"images/icon_empty.gif\">";
        }
      }else{
        if($owner_id == $AccessUserID && $_SESSION['USER']->Type == 'Admin'){?>
        <a  title='view <?php echo $tmp_sub_val[1]?>' href="javascript: popwin('mng_set_frequency.php?frm_frequency_name=<?php echo $sub_key?>', 800, 800);"><b><img border="0" src="images/icon_view.gif" alt="View"></b></a>&nbsp;
        <a  title='update <?php echo $tmp_sub_val[1]?>' href="<?php echo $PHP_SELF;?>?firstDisplay=y&theaction=modify_frequency&frm_frequency_name=<?php echo $sub_key?>"><img border="1" src="images/icon_update.png" alt="Update"></a>&nbsp;
        <a  title='delete <?php echo $tmp_sub_val[1]?>' href="javascript: confirm_delete_uFrequency('<?php echo $sub_key?>','<?php echo $tmp_sub_val[1]?>')"><img border="0" src="images/icon_purge.gif" alt="Delete"></a>&nbsp;
<?php       }else{
          echo "<img src=\"images/icon_empty.gif\">";
          echo "<img src=\"images/icon_empty.gif\">";
          echo "<img src=\"images/icon_empty.gif\">";
        }
      }
?>
        </div>
      </td>
    </tr>
<?php   }?>     
    </table>
</Form>   
<?php  
  }
?>   
  </div>
</div>      
</body>
<script language="JavaScript" type="text/javascript"> 
  document.getElementById('process_2').style.display = 'none';
</script>    
  </html>
  <?php 
  require("site_footer.php");
	exit;
}
$elementID = '';
$allElementsIDstr = get_all_elements_for_this_project($elementID);
$radio_SearchEngine_arr = array();
if($allElementsIDstr){
  $radio_SearchEngine_arr = get_SearchEngine_type($allElementsIDstr,$elementID);
}else{
  //-this project have no any real element.
}


if(!$Is_geneLevel){
  $tmp_radio_SearchEngine_arr = $radio_SearchEngine_arr;
  $radio_SearchEngine_arr = array();
  $find_flag = 0;
  $find_TPP_flag = 0;
  foreach($tmp_radio_SearchEngine_arr as $key => $val){
    if(strstr($key, 'TPP_')){
      if(!$find_TPP_flag && $val){
        $radio_SearchEngine_arr[$key] = $val;
        $find_TPP_flag = 1;
      }
    }else{
      if(!$find_flag && $val){
        $radio_SearchEngine_arr[$key] = $val;
        $find_flag = 1;
      }
    }
  }
}

//=========================================================================================================
$radio_SearchEngine_arr = array();
$radio_SearchEngine_arr_tmp = get_has_hits_SearchEngine($hits_searchEngines);

$tmp_tmp_arr = array();
foreach($radio_SearchEngine_arr_tmp as $key => $val){
  if(strstr($key, 'GeneLevel_')){
    $radio_SearchEngine_arr['GeneLevel'] = $val;
  }elseif(strstr($key, 'TPP_')){
    $radio_SearchEngine_arr['TPP'] = $val;
  }elseif(!array_key_exists('Normal', $radio_SearchEngine_arr)){
    $radio_SearchEngine_arr['Normal'] = $val;
  }
}

?>
<STYLE type="text/css">
.sss { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt; white-space: nowrap}
.sss2 { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt; font-weight : bold; white-space: nowrap}
.sss3 {	HEIGHT: 339px }
TD { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
</STYLE>
<SCRIPT language=JavaScript> 

$(document).ready(function () {
  startRequest('start','');
});
</SCRIPT>
<script language="JavaScript" type="text/javascript">
<!--

var used_frequency_file_names = new Array();
<?php 
  $user_frequency_files = scandir($frequency_dir_arr['U']);
  foreach($user_frequency_files as $frequency_files_name){
    if($frequency_files_name == '.' || $frequency_files_name == '..') continue;
    $tmp_frequency_files_name =  explode("-", $frequency_files_name);
    if($theaction == 'modify_frequency' && basename($tmp_frequency_files_name[2], ".csv") == $frm_frequency_name) continue;
?>
    used_frequency_file_names.push("<?php echo basename($tmp_frequency_files_name[2], ".csv")?>");
<?php }?>

var x = 0;
var current_group_color;
var radioName = 'abc';
var contrlColor = 'C_FFFF00';
var NnMergedColor = 'C_FFFFFF';

function createCellWithBgcolor(color,text) {
  var cell = document.createElement('td');
	cell.className = color;
  if(text !== ''){
  	var textNode = document.createTextNode(text);
    cell.appendChild(textNode);
  }
  return cell;
}
function createCellWithRadio(radioName,value){
	var radioArr = document.getElementById('form_frequency');
	for(var i=0; i<radioArr.length; i++){
		if(radioArr[i].name == radioName){
			radioArr[i].checked = false;
		}	
	}
  var cell = document.createElement('td');
	try{
		rdo = document.createElement('<input type="radio" value="'+ value +'" name="'+ radioName +'" checked onclick="get_current_color()"/>');
	}catch(err){
		rdo = document.createElement('input');
		rdo.setAttribute('type','radio');
		rdo.setAttribute('name',radioName);
		rdo.setAttribute('value',value);
		rdo.setAttribute('checked',true);
		rdo.setAttribute('onclick','javascript: get_current_color()');
	}
	cell.appendChild(rdo);
  return cell;
}

function remove_single_row(rowID) {
	var tableBody = document.getElementById("colorBarBody");
	var rowNote = document.getElementById(rowID);
	tableBody.removeChild(rowNote);
}
function get_current_color(){
	var radioArr = document.getElementById('form_frequency');
	for(var i=0; i<radioArr.length; i++){
		if(radioArr[i].name == radioName && radioArr[i].checked == true){
			current_group_color = radioArr[i].value;
			break;
		}	
	}
}
function remove_color_bars(){
  var selectedList = document.getElementById('frm_selected_list');
  var listColorArr = new Array();
  var colorFlag = '';
  for(var j=0; j<selectedList.length; j++){
	  if(colorFlag != selectedList.options[j].value){
      colorFlag = selectedList.options[j].value
      listColorArr.push(colorFlag);
    }
  }
  var radioArr = document.getElementById('form_frequency');
  var checkedItem = 0;
  for(var i=radioArr.length-1; i>=0; i--){
		if(radioArr[i].name == radioName){
      if(radioArr[i].value == contrlColor) continue;
      if(radioArr[i].value == NnMergedColor){
        if(checkedItem == 1){
          radioArr[i].checked = true;
          current_group_color = radioArr[i].value;
        }  
        continue;
      }  
      var colorUsed = 0;
      for(var j=0; j<listColorArr.length; j++){
			  if(listColorArr[j] == radioArr[i].value){
          colorUsed = 1;
          break;
        } 
      }
      if(colorUsed == 0){
        if(radioArr[i].checked == true){
          checkedItem = 1;
        }  
        remove_single_row(radioArr[i].value);
      }  
		}	
	}
}

function inset_new_option(parent, newOption,nextOption){
	try {
    parent.add(newOption, nextOption); // standards compliant; doesn't work in IE
  }
  catch(ex) {
    parent.add(newOption, nextOption.index); // IE only
  }
}

function append_new_option(parent, newOption){
	try {
    parent.add(newOption, null); // standards compliant; doesn't work in IE
  }
  catch(ex) {
    parent.add(newOption); // IE only
  }
}

function add_option_to_selected(){
  var sourceList = document.getElementById('frm_sourceList');
  var selectedList = document.getElementById('frm_selected_list');
  var theForm = document.form_frequency;
  var currentType = theForm.currentType.value;
	var selectedCounter = 0;
	var currentIndex = 0;
  var this_time_selected_option_str = '';
  for(var i=sourceList.length-1; i>0; i--){
    if(sourceList.options[i].selected){
      if(sourceList.options[i].id == '') continue;
      if(this_time_selected_option_str) this_time_selected_option_str += ",";
      this_time_selected_option_str += sourceList.options[i].id
			selectedCounter++;
      var optionNew = document.createElement('option');
    	optionNew.id = sourceList.options[i].id;
      optionNew.text = sourceList.options[i].text;
      optionNew.value = current_group_color;
    	optionNew.className = current_group_color;
			sourceList.remove(i);
			if(optionNew.value == NnMergedColor){
				if(selectedCounter == 1){
					append_new_option(selectedList, optionNew);
					currentIndex = selectedList.length-1;
				}else{
					inset_new_option(selectedList, optionNew, selectedList.options[currentIndex]);
				}		
			}else{
				var newGroup = 1;
	      for(var j=selectedList.length-1; j>0; j--){
	        if(optionNew.value == selectedList.options[j].value){
						if(selectedCounter == 1){
							if(j == selectedList.length-1){
								append_new_option(selectedList, optionNew);
							}else{
								inset_new_option(selectedList, optionNew, selectedList.options[j+1]);
							}				
							currentIndex = j + 1;
						}else{
							inset_new_option(selectedList, optionNew, selectedList.options[currentIndex]);
						}
						newGroup = 0;
	          break;
	        }
	      }
				if(newGroup == 1){
          if(current_group_color == contrlColor){
            if(selectedList.length > 1){
              inset_new_option(selectedList, optionNew, selectedList.options[1]);
            }else{
              append_new_option(selectedList, optionNew);
            }
						currentIndex = 1;
          }else	if(selectedList.options[selectedList.length-1].value == NnMergedColor){
						for(var j=0; j<selectedList.length; j++){
							if(selectedList.options[j].value == NnMergedColor) break;
						}
						inset_new_option(selectedList, optionNew, selectedList.options[j]);
						currentIndex = j;
					}else{
						append_new_option(selectedList, optionNew);
						currentIndex = selectedList.length-1;
					}	
	      }       
	    }
		}	
  }
  if(this_time_selected_option_str == '') return;
  queryString = "IDs=" + this_time_selected_option_str + "&Add=add" + "&action=add_option" + "&currentType=" + currentType  + "&Is_geneLevel=" + <?php echo $Is_geneLevel?>;
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function remove_option_from_selected(){
  var selectedList = document.getElementById('frm_selected_list');
	var theForm = document.form_frequency;
	var currentType = theForm.currentType.value;
  var this_time_selected_option_str = '';
  	
  for(var i=selectedList.length-1; i>0; i--){
    if(selectedList.options[i].id == '') continue;
    if(selectedList.options[i].selected){
      if(this_time_selected_option_str) this_time_selected_option_str += ",";
      this_time_selected_option_str += selectedList.options[i].id;
      selectedList.remove(i);
    }
  }
  if(this_time_selected_option_str == '') return;
  var queryString = createQueryString('remove_selected_item','');
  queryString += "&IDs=" + this_time_selected_option_str + "&Add=" + "&action=remove_option"  + "&Is_geneLevel=" + <?php echo $Is_geneLevel?>;
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function trimString(str) {
  var str = this != window? this : str;
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
//-->
</script>

<script type="text/javascript">
var sourceTitleTxt;

function createQueryString(theaction,pageNum){
  var selObj = document.getElementById("frm_selected_list");
	var selected_id_str = '';  
	var frm_order_by;
	var currentPage;
  var theForm = document.form_frequency;
  var offset;
  var currentType;
  var displaySearchEngine = theForm.displaySearchEngine.value;
  var frm_user = "&frm_user=";
  var frm_search_by = "&frm_search_by=";
  
  var SearchEngine = '';
  var subAction = '';
  var switch_SearchEngine = "&switch_SearchEngine=0";
  var per_selected_id_str = "";
  if(displaySearchEngine == '1'){
    for(var k=0; k<theForm.frm_SearchEngine.length; k++){
      if(theForm.frm_SearchEngine[k].checked == true){
        SearchEngine = theForm.frm_SearchEngine[k].value;
        break;
      }  
    }
  }else{
    SearchEngine = theForm.SearchEngine.value;
  }  
	if(theaction == 'start'){
    SearchEngine = '<?php echo $SearchEngine?>';
    if('<?php echo $currentType?>' == 'Bait'){
  		currentType = 'Bait';
  		frm_order_by = 'ID';
    }else if('<?php echo $currentType?>' == 'Exp'){  
      currentType = 'Exp';
  		frm_order_by = 'E.BaitID';
    }else{
      currentType = 'Band';
      frm_order_by = 'D.BaitID';
    }
    var tmp_selected_id_str = '';
    if(SearchEngine == 'normal'){
      selected_id_str = theForm.selected_id_str_normal.value;
    }else if(SearchEngine == 'TPP'){
      selected_id_str = theForm.selected_id_str_TPP.value;
    }
    currentPage = 1;
    subAction = "list_is_empty";
    switch_bgclor('<?php echo $clickedId?>');
  }else if(theaction == 'switch' || theaction == 'switch_SearchEngine'){
    if(theaction == 'switch_SearchEngine') switch_SearchEngine = "&switch_SearchEngine=1";
    currentType = pageNum;    
    if(currentType == 'Bait'){ 
      frm_order_by = 'ID';
      tmp_id = 'BaitID';
    }else if(currentType == 'Exp'){
  		frm_order_by = 'E.BaitID';
      tmp_id = 'ExpID';
    }else{
      frm_order_by = 'D.BaitID';
      tmp_id = 'BandID';
    }
    
    for(var i=1; i<selObj.length; i++){
      if(selected_id_str != "") selected_id_str += ',';
	    selected_id_str += selObj.options[i].id+':'+selObj.options[i].value+':'+selObj.options[i].className;
    }
    
    if(theaction == 'switch_SearchEngine'){
      if(theForm.SearchEngine.value == 'normal'){
        tmp_selected_id_str = theForm.selected_id_str_normal.value;
      }else if(theForm.SearchEngine.value == 'TPP'){
        tmp_selected_id_str = theForm.selected_id_str_TPP.value;
      }
      if(theForm.SearchEngine_before.value == 'normal'){
        theForm.selected_id_str_normal.value = tmp_id+"@@"+selected_id_str;
      }else if(theForm.SearchEngine_before.value == 'TPP'){
        theForm.selected_id_str_TPP.value = tmp_id+"@@"+selected_id_str;
      }
      per_selected_id_str = selected_id_str;
      selected_id_str = tmp_selected_id_str;      
    }else{
      var before_type = "switch_from_" + theForm.currentType.value + "@@";
      selected_id_str = before_type + selected_id_str;
    }    
    if(selected_id_str != ""){
      subAction = "list_sub_bands";
    }
    clean_selected_list();
		currentPage = 1;
    
	}else{
    var currentType = theForm.currentType.value;
    if(currentType == 'Bait'){ 
      frm_order_by = 'ID';
      tmp_id = 'BaitID';
    }else if(currentType == 'Exp'){
  		frm_order_by = 'E.BaitID';
      tmp_id = 'ExpID';
    }else{
      frm_order_by = 'D.BaitID';
      tmp_id = 'BandID';
    }
    subAction = "normal";
	  for(var i=1; i<selObj.length; i++){
	    if(selected_id_str != "") selected_id_str += ',';
			selected_id_str += selObj.options[i].id;
	  }
    selected_id_str = tmp_id+"@@"+selected_id_str;
		var order_by = document.getElementById("frm_order_by");
		for(var i=0; i<order_by.length; i++){
			if(order_by.options[i].selected == true && order_by.options[i].value !== ''){
				frm_order_by = order_by.options[i].value
	    }
	  }	
		if(pageNum != ''){
			currentPage = pageNum;
		}else{	
			currentPage = document.getElementById("currentPage").value;
		}
    offset = theForm.offset.value;
	}
  
  var group_str = '';
  if(theaction == 'changeOrderby' || theaction == 'changePage' || theaction == 'remove_selected_item'){
    if(theaction == 'changeOrderby'){
      currentPage = 1;
    }
    document.getElementById("currentPage").value = currentPage;
    
    var itemType = theForm.itemType.value;
    if(itemType == 'Bait'){
      var selected_group_id = theForm.Bait_order_by.value;
    }else if(itemType == 'Experiment'){
      var selected_group_id = theForm.Experiment_order_by.value;
    }else if(itemType == 'Band'){
      var selected_group_id = theForm.Band_order_by.value;
    }    
    group_str = "&itemType=" + itemType + "&selected_group_id=" + selected_group_id;
    if(currentType != 'Bait'){
      var frm_groups = ''
      var groups_obj = theForm.frm_groups;
      for(var i=0; i<groups_obj.length; i++){
        if(groups_obj[i].checked){
          frm_groups = groups_obj[i].value;
          break;
        }
      }
      group_str += "&frm_groups=" + frm_groups;
    }
    var user_search = "&frm_user=" + theForm.frm_user.value + "&frm_search_by=" + encodeURIComponent(theForm.frm_search_by.value);
  }    
  per_selected_id_str = "&per_selected_id_str=" + per_selected_id_str;  
  //var queryString = "selected_id_str=" + selected_id_str + "&frm_order_by=" + frm_order_by + "&currentType=" + currentType + "&currentPage=" + currentPage + "&offset=" + offset + "&SearchEngine=" + SearchEngine + "&action=creatList&subAction=" + subAction + switch_SearchEngine + group_str + user_search + per_selected_id_str;
  var queryString = "selected_id_str=" + selected_id_str + "&Is_geneLevel=" + <?php echo $Is_geneLevel?> + "&frm_order_by=" + frm_order_by + "&currentType=" + currentType + "&currentPage=" + currentPage + "&offset=" + offset + "&SearchEngine=" + SearchEngine + "&action=creatList&subAction=" + subAction + switch_SearchEngine + group_str + user_search + per_selected_id_str;
  return queryString;
}

function startRequest(theaction,pageNum){
  var queryString = createQueryString(theaction,pageNum);
  //document.getElementById('process').style.display = 'block';
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function switch_type(itemtype, clickedId){
  var ret = switch_bgclor(clickedId);
  if(ret == true){
    startRequest('switch',itemtype);
  }
}

function switch_SearchEngine(){
  var theForm = document.form_frequency;
  theForm.SearchEngine_before.value = theForm.SearchEngine.value;
  for(var k=0; k<theForm.frm_SearchEngine.length; k++){
    if(theForm.frm_SearchEngine[k].checked == true){
      if(theForm.SearchEngine.value == theForm.frm_SearchEngine[k].value) return; 
      theForm.SearchEngine.value = theForm.frm_SearchEngine[k].value;
      break;
    }  
  }
  var itemtype = theForm.currentType.value;
  clean_up_child_nodes('targetTitleType');
  clean_up_child_nodes('targetTitle');
  clean_up_child_nodes("results");
  startRequest('switch_SearchEngine',itemtype);
}


function clean_selected_list(){
  var parentItem = document.getElementById('frm_selected_list');
  if(parentItem.hasChildNodes()){
    for(var i=parentItem.length-1; i>0; i--){
      parentItem.remove(i);
    }
  }  
}

function clean_up_child_nodes(itemID){
  var parentItem = document.getElementById(itemID);
  if(parentItem.hasChildNodes()){
    while(parentItem.childNodes.length > 0) {
      parentItem.removeChild(parentItem.childNodes[0]);
    }
  }  
}

function processAjaxReturn(ret_html){
//alert(ret_html);
  document.getElementById('process').style.display = 'none';
  if(ret_html == '') return;
  var ret_html_arr = ret_html.split("@@**@@");
  document.getElementById("tmp").innerHTML = ret_html_arr[0];
  if(ret_html_arr.length >=2 && trimString(ret_html_arr[1]) == 'source_target'){
    clean_up_child_nodes("results");  
    var sub_action = trimString(ret_html_arr[4]);
    if(sub_action == "normal"){
      document.getElementById("results").innerHTML = ret_html_arr[2];
    }else{
    	document.getElementById("results").innerHTML = ret_html_arr[2];
    	clean_up_child_nodes("results2");
    	document.getElementById("results2").innerHTML = ret_html_arr[3];
    }
    document.getElementById("filters").innerHTML = ret_html_arr[4];	
    add_target_title();
  }  
}

function add_target_title(){
  var theForm = document.form_frequency; 
  var sourceTitle = document.getElementById('sourceTitle');
  var sourceTitleTxt = sourceTitle.firstChild.nodeValue;
  var targetTitle = document.getElementById('targetTitle');
  var currentType = theForm.currentType.value;
  if(currentType == 'Bait'){
    currentType = 'Baits';
  }else if(currentType == 'Exp'){  
    currentType = 'Experiments';
  }else if(currentType == 'Band'){
    currentType = 'Samples';
  }
  if(targetTitle.hasChildNodes()) {
    targetTitle.removeChild(targetTitle.childNodes[0]);
  }
  var textNode = document.createTextNode(sourceTitleTxt);
  targetTitle.appendChild(textNode);
  var targetTitleType = document.getElementById('targetTitleType');
  if(targetTitleType.hasChildNodes()) {
    targetTitleType.removeChild(targetTitleType.childNodes[0]);
  }
  var titleTypeTxt = 'Selected ' + currentType;
  var textNode2 = document.createTextNode(titleTypeTxt);
  targetTitleType.appendChild(textNode2);
}

var Bait_note_init_arr = new Array();
var Experiment_note_init_arr = new Array();
var Band_note_init_arr = new Array();
<?php 
$note_init_arr = get_noteType_ini_by_type();
foreach($note_init_arr as $key => $val){
  if($key == 'Export') continue;
  foreach($val as $val_2){
?>
  <?php echo $key?>_note_init_arr.push('<?php echo $val_2?>');
<?php 
  }
}
?>
function generate_frequency(){
  var theForm = document.form_frequency;
  var selectedList = document.getElementById('frm_selected_list');
  var currentType = theForm.currentType.value;
  var hasTage = theForm.hasTage.value;
  var SearchEngine = theForm.SearchEngine.value;
  var typeName = '';
  var tmp_new_arr = new Array();
  if(SearchEngine.match(/TPP/)){
    theForm.frm_frequency_name.value = 'TPP_' + theForm.frm_frequency_name.value;
  }
<?php if(array_key_exists('Hits_GeneLevel', $exist_Hits_tables_arr)){?>
    if(theForm.Is_geneLevel.checked){
      theForm.frm_frequency_name.value = 'geneLevel_' + theForm.frm_frequency_name.value;
    }
<?php }?>  
  var frm_frequency_name = theForm.frm_frequency_name.value;  
  if(!onlyAlphaNumerics(frm_frequency_name, 2)){
    alert("Only characters \"_A-Za-z0-9\" are allowed for frequency name.");
    return;
  }
  
  for(var i=0; i<used_frequency_file_names.length; i++){
    if(used_frequency_file_names[i] == frm_frequency_name){
      alert("The name " + frm_frequency_name + " has been used.");
      return;
    }
  }
  
  var owner_id = <?php echo $AccessUserID?>;
  
  if(currentType == 'Bait'){
    typeName = 'baits';
    tmp_new_arr = Bait_note_init_arr;
    theForm.frm_frequency_name.value = 'B-' + owner_id + '-' + frm_frequency_name;
  }else if(currentType == 'Exp'){
    typeName = 'Experiments';
    tmp_new_arr = Experiment_note_init_arr;
    theForm.frm_frequency_name.value = 'E-' + owner_id + '-' + frm_frequency_name;
  }else if(currentType == 'Band'){ 
    typeName = 'Samples';
    tmp_new_arr = Band_note_init_arr;
    theForm.frm_frequency_name.value = 'S-' + owner_id + '-' + frm_frequency_name;
  }
  
  if(selectedList.length <= 1){
    alert("Please select baits or " + typeName + " for report");
    return false;
  }
  var colorVar = '';
  var idStr = '';
  var groupStr = '';
  var listStr = '';
  var typeStr = '';
 
  var typeFlag = true;
  var IniNameArr = new Array();
  var IniNameCountArr = new Array();
  var selectedListCount = selectedList.length - 1;
  
  for(var j=1; j<selectedList.length; j++){
    if(selectedList.options[j].value != colorVar){
      if(colorVar != '' && idStr != ''){
        groupStr = colorVar + ':' + idStr;
        if(listStr != '') listStr += ';';
        listStr += groupStr;
      }  
      colorVar = selectedList.options[j].value;
      idStr = '';
    }
    if(idStr != '') idStr += ',';
    idStr += selectedList.options[j].id;
    
    
    if(hasTage == 1 && typeFlag){
      var tmp_text = selectedList.options[j].text;
      var tag = tmp_text.match(/\[[A-Z]{1,2}\]/g);
      if(tag == null){
        typeFlag = false;
      }     
      if(typeFlag){
        for(var k=0; k<tag.length; k++){
          IniNameArr.push(tag[k]);
        }
      }
    }
  }
  IniNameArr.sort();
  var counter = 0;
  var tmp_tag = IniNameArr[0];

  for(var i=0; i<=IniNameArr.length; i++){
    if(IniNameArr[i] == tmp_tag){
      counter++;
    }else{
      if(counter == selectedListCount){
        var last_index = tmp_tag.length-1;
        tmp_tag = tmp_tag.substring(1,last_index);
        var flag = true;
        for(var v=0; v<tmp_new_arr.length; v++){
          if(tmp_new_arr[v] == tmp_tag){
            flag = false;
            break;
          }
        }
        if(flag){
          typeStr = '';
          break;
        }
        if(typeStr) typeStr += ",";
        typeStr += tmp_tag;
      }
      tmp_tag = IniNameArr[i];
      counter = 1;
    }
  } 
  if(colorVar != '' && idStr != ''){
    groupStr = colorVar + ':' + idStr;
    if(listStr != '') listStr += ';';
    listStr += groupStr;
  }
  theForm.frm_selected_list_str.value = listStr;
  theForm.typeStr.value = typeStr;  
//alert(theForm.theaction.value);
//alert(theForm.frm_frequency_name.value);
//return;
  if(theForm.theaction.value == 'modify_frequency'){
    theForm.theaction.value = "update_frequency_u";
  }else{
    theForm.theaction.value = "generate_frequency";
  }
  theForm.submit();
}

var currentTabId = "tabOn1";

function onOff(obj, colorName){
   if(obj.id == currentTabId){
    return false;
   }
   obj.className = colorName;
}

function switch_bgclor(clickedId){
	if(clickedId != currentTabId){
		var currentObj = document.getElementById(currentTabId);
		var clickedObj = document.getElementById(clickedId);
		currentObj.className = 'tab';
		clickedObj.className = clickedId;
		currentTabId = clickedId;
		var witchone = clickedId.substr(5,1);
		var trObj = document.getElementById('intTr');
		if(witchone == '1'){
			trObj.className = 'intTr1';
		}else if(witchone == '2'){
			trObj.className = 'intTr2';
		}else if(witchone == '3'){
			trObj.className = 'intTr3';
		}
    return true;
	}
  return false;
}

function toggle_group(theForm){
  var groups = theForm.frm_groups;
  for(var i=0; i<groups.length; i++){
    var group_obj = document.getElementById(groups[i].value);
    if(groups[i].checked == true){
      group_obj.style.display = "block";
      theForm.itemType.value = groups[i].value;
    }else{
      group_obj.style.display = "none";
    }
  }
}

function swith_page(){
  var theForm = document.getElementById('form_frequency');
  if(theForm.Is_geneLevel.checked){
    var Is_geneLevel = 1;
  }else{
    var Is_geneLevel = 0;
  }
  window.location.assign("<?php echo $PHP_SELF;?>?firstDisplay=y&Is_geneLevel="+Is_geneLevel);
}

</script>
<style>
.intTr1{
	background: <?php echo $tb_color;?>;
}
.intTr2{
	background: <?php echo $tb_color2;?>;
}
.intTr3{
	background: <?php echo $tb_color3;?>;
}


.tabOn1{
  background: <?php echo $tb_color;?>;
  font-weight: bold;
  font-size: 13px;
}
.tabOn1 a{
  color: black;
  text-decoration: none; 
  border-bottom: none;
}
.tabOn2{
  background: <?php echo $tb_color2;?>;
  font-weight: bold;
  font-size: 13px;
}
.tabOn2 a{
  color: black;
  text-decoration: none; 
  border-bottom: none;
}
.tabOn3{
  background: <?php echo $tb_color3;?>;
  font-weight: bold;
  font-size: 13px;
}
.tabOn3 a{
  color: black;
  text-decoration: none; 
  border-bottom: none;
}
.tab{
  background: #708090;
  font-weight: bold;
  font-size: 13px;
}
.tab a{
  color: #ffffff;
  text-decoration: none; 
  border-bottom: none;
}
</style>
 
<FORM ACTION="<?php echo $_SERVER['PHP_SELF'];?>" ID="form_frequency" NAME="form_frequency" METHOD="POST">
<INPUT TYPE="hidden" NAME="theaction" VALUE="<?php echo $theaction?>">
<INPUT TYPE="hidden" NAME="source" VALUE="">
<INPUT TYPE="hidden" NAME="frm_selected_list_str" VALUE="">
<INPUT TYPE="hidden" NAME="typeStr" VALUE="">
<INPUT TYPE="hidden" NAME="start" VALUE="">
<INPUT TYPE="hidden" name=color2>
<INPUT TYPE="hidden" NAME="displaySearchEngine" VALUE="<?php echo $displaySearchEngine?>">
<INPUT TYPE="hidden" NAME="SearchEngine" VALUE="<?php echo $SearchEngine?>">
<INPUT TYPE="hidden" NAME="filtrColorIniFlag" VALUE="1">
<INPUT TYPE="hidden" NAME="selected_id_str_normal" VALUE="<?php echo $selected_id_str_normal?>">
<INPUT TYPE="hidden" NAME="selected_id_str_TPP" VALUE="<?php echo $selected_id_str_TPP?>">
<INPUT TYPE="hidden" NAME="SearchEngine_before" VALUE="">
<INPUT TYPE="hidden" NAME="old_base_name" VALUE="<?php echo $old_base_name?>">

<div id='tmp'></div>
<table border="0" cellpadding="0" cellspacing="1" width="90%">
  <tr>
    <td align="left">
    <br>&nbsp; <font color="navy" face="helvetica,arial,futura" size="4"><b>Create user defined frequency</b></font>
<?php     
    if($AccessProjectName){
        echo "  <font color='red' face='helvetica,arial,futura' size='3'><b>(Project $AccessProjectID: $AccessProjectName)</b></font>";
    }
?>     
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td><br>
    &nbsp; instructions <a id='instruction_a' href="javascript: toggle_group_description('instruction')" class=Button>[+]</a>
    <DIV id='instruction' STYLE="display: none">
    <ul>
      <li> 
      <li> 
      <li>
    </ul>
    </DIV> 
    </td>
  </tr>
  
  <tr>
    <td align=center >
    <table width=908 bgcolor="#708090" cellspacing="0" cellpadding=1 border="0">
  <tr bgcolor="white" height=25>
    <td align=right><a title='go back to frequency list' href="<?php echo $PHP_SELF;?>?&theaction=display_frequency" class=Button>[ Frequency list ]</a>&nbsp;</td>
  </tr>
<?php if(array_key_exists("Hits_GeneLevel", $exist_Hits_tables_arr)){?>
  <tr>
        <td>
        <input type="checkbox" name="Is_geneLevel" value="1" <?php echo ($Is_geneLevel)?'checked':'';?> onclick="swith_page();">&nbsp;&nbsp;Gene Level
        </td>
  </tr> 
<?php }?>   
    <tr><td>
    <table border="0" width="100%" height="50" cellspacing="0" cellpadding=0 >
    <tr>
      <td colspan=7>
        <table border=0 width=100% cellspacing="0" cellpadding=0>
          <tr>
            <td class=tabOn1 id=tabOn1 nowrap height=30 onmouseover="onOff(this, 'tabOn1')" onmouseout="onOff(this, 'tab')">
             &nbsp; &nbsp;<a href="javascript: switch_type('Bait', 'tabOn1');"><font size="2"><b>Bait List</b></font>&nbsp; &nbsp;
            </td>
            <td BGCOLOR="#708090">&nbsp;</td>
            <td class=tab id=tabOn3 nowrap height=30 onmouseover="onOff(this, 'tabOn3')" onmouseout="onOff(this, 'tab')">
             &nbsp; &nbsp; <a href="javascript: switch_type('Exp', 'tabOn3');">Experiment List</a> &nbsp; &nbsp;
            </td>
            <td BGCOLOR="#708090">&nbsp;</td>
            <td class=tab id=tabOn2 nowrap height=30 onmouseover="onOff(this, 'tabOn2')" onmouseout="onOff(this, 'tab')">
             &nbsp; &nbsp; <a href="javascript: switch_type('Band', 'tabOn2');">Sample List</a> &nbsp; &nbsp;
            </td>
            
        <?php if($displaySearchEngine || 1){
            $titleBarW ='20%';
        ?>
            <td align="center" width=60% nowrap>
              <font size="2">
           <?php 
          $tmp_counter = 0;
          $tmp_type_lable = '';
          foreach($radio_SearchEngine_arr as $key => $val){
            if($val){
              $tmp_type_lable = $key;
              $tmp_counter++;
            }  
          }
          if($tmp_counter == 1){
            echo $tmp_type_lable;
          }else{
            $normal_tpp_arr = array();
            foreach($radio_SearchEngine_arr as $key => $val){
              if($Is_geneLevel){
                if(!strstr($key, 'GeneLevel')) continue; 
                $E_lable = $key;
                $selected_key = 'GeneLevel';
              }else{
                if(strstr($key, 'GeneLevel')) continue;
                $E_lable = $key;
                $selected_key = 'Normal';
              }             
              if($val){?>    
                <?php echo $E_lable?><input readonly type=radio name='frm_SearchEngine' value='<?php echo $key?>' <?php echo ($selected_key == $key)?'checked':''?> onclick="switch_SearchEngine()">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <?php }?>
          <?php }?>
        <?php }?>    
              </font>
            </td>
       <?php }?>   
            <td width=<?php echo $titleBarW;?> BGCOLOR="#708090" align=right valign=center>
             <input type=button name='go' value='Generate Frequency' onClick="generate_frequency()">&nbsp;&nbsp;             
            </td>
          </tr>
        </table>
      </td>
    </tr>
     <tr id=intTr class=intTr1>
      <td width="400" align=center valign=top><br>
      <div id="results"></div>    
      </td>
      <td width=1 BGCOLOR="#ffffff"><img src='./images/pixel.gif' border=0></td>
      <td width="60" valign=center align=center>
      <div style='display:block' id='process'><img src='./images/process.gif' border=0></div>
      <br>
      <font size="2" face="Arial">
      <input type=button value='&nbsp;&nbsp;> >&nbsp;&nbsp;' onClick="add_option_to_selected()">
      <br><br>
      <input type=button value='&nbsp;&nbsp;< <&nbsp;&nbsp;' onClick="remove_option_from_selected()">
      </font> 
      </td>
      <td width=1 BGCOLOR="#ffffff"><img src='./images/pixel.gif' border=0></td>
      <td width="400" align=center valign=top><br>
      <div id='targetTitleType' class=sss2></div> 
      <div id='targetTitle' class=sss></div>
      <table border=0>
        <tr>
          <td align=center>
    			  <div id="results2"></div>
          <td>
          <td align=left>
            <a href="javascript: moveOptionsUp('frm_selected_list');" title='up' class=button>
              <img border="0" src="images/icon_up.gif">
            </a><br>
            <a href="javascript: moveOptionsDown('frm_selected_list');" title='down' class=button>
              <img border="0" src="images/icon_down.gif">
            </a>
          </td>
        </tr>
        <tr>
          <td align=center>
            <table border="0"  height="50" cellspacing="0" cellpadding=0 width="300">
              <tr>
                <td align=left>
                  User defined frequency name:
                </td>
              </tr>
              <tr>
                <td align=left>
                  <INPUT TYPE="text" NAME="frm_frequency_name" size=30 VALUE="<?php echo $frm_frequency_name?>">
                </td>
              </tr>
              <tr>
                <td align=left>
                  Description:
                </td>
              </tr>
              <tr>
                <td align=left>
                  <textarea cols="40" rows="5" name="frm_frequency_description"><?php echo $frm_frequency_description?></textarea>
                </td>
              </tr>
            </table>
            <div id="filters">
            </div>
          </td>
        </tr>   
      </table>
      </td>
    </tr>
    </table>
    </td></tr></table>
    </td>
  </tr>
</table>   
</FORM>
</body>
</html>
<?php 
 
require("site_footer.php");

function create_source_element_list(){
  global $HITSDB,$AccessProjectID,$currentType,$itemType,$elementsPerPage,$frm_order_by,$selected_id_str,$currentPage,$frm_groups;
  global $bg_tb_header,$bg_tb,$offset,$SearchEngine,$subAction,$switch_SearchEngine,$per_selected_id_str,$selected_group_id;
  global $frm_search_by,$PROHITSDB,$frm_user;
  global $Is_geneLevel,$filter_arr_U, $filter_lable_arr;
  
  $frm_min = ''; 
  $frm_max = '';
  $frm_tage_max = '';
  $frm_tage_min ='';
  $hasGel = 0;
  $jointed = 0;
  $isJointedPage = 0;
  if($currentType == 'Bait'){
    $sourceType = 'Baits';
    $itemType = 'Bait';
  }elseif($currentType == 'Exp'){
    $sourceType = 'Experiments';
  }elseif($currentType == 'Band'){
    $sourceType = 'Samples';
  }
  $sele_optionStr = '';
  
  $has_notes_itemID_arr = array();
  
  $bait_group_icon_arr = get_project_noteType_arr($HITSDB);
  
  $item_group_icon_arr = array('Bait'=>array(),'Experiment'=>array(),'Band'=>array());
  foreach($bait_group_icon_arr as $key => $bait_group_icon_val){
    if(!$bait_group_icon_val['Type']) continue;
    if($bait_group_icon_val['Type'] == 'Export'){
      $item_group_icon_arr['Band'][$key] = $bait_group_icon_val;
    }else{  
      $item_group_icon_arr[$bait_group_icon_val['Type']][$key] = $bait_group_icon_val;
    }  
  }

  $hasTage = get_tages($has_notes_itemID_arr,$item_group_icon_arr[$itemType],$itemType,$currentType);
 
  $selected_id_arr = array();
  $selected_id_arr_tmp = array();
  $opPropertyArr = array();
  $tmptmpOpArr = explode('@@',$selected_id_str);
  $before_type = $tmptmpOpArr[0];
  $selected_id_str_new = '';
 
  if(has_itemIDstr_in_session()){ 
    $tem_ssesion_arr = array();
    if($before_type == 'switch_from_Bait' || $before_type == 'BaitID'){
      $tem_ssesion_arr = get_comparison_session('Bait',1);
      $tmpID = "BaitID";
    }elseif($before_type == 'switch_from_Exp' || $before_type == 'ExpID'){
      $tem_ssesion_arr = get_comparison_session('Exp',1);
      if($tem_ssesion_arr){
        $exp_selected_id_str = implode(",", $tem_ssesion_arr);
        $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID` IN ($exp_selected_id_str)";
        $tmpExpIdArr = $HITSDB->fetchAll($SQL);
        $tem_ssesion_arr = array();
        foreach($tmpExpIdArr as $tmpExpIdVal){
          array_push($tem_ssesion_arr, $tmpExpIdVal['ID']);
        }
      }
      $tmpID = "BandID";
    }elseif($before_type == 'switch_from_Band' || $before_type == 'BandID'){
      $tem_ssesion_arr = get_comparison_session('Sample',1);
      $tmpID = "BandID";
    }
    $selected_id_arr = $tem_ssesion_arr;
    rsort($selected_id_arr);
    $selected_id_str = implode(",", $selected_id_arr);
    if($selected_id_str){
      if($Is_geneLevel){
        $SQL_table = " Hits_GeneLevel ";
      }elseif(strstr($SearchEngine, 'TPP')){
        $SQL_table = " TppProtein ";
      }else{
        $SQL_table = " Hits ";
      }
         
      if($currentType == 'Band'){    
        foreach($selected_id_arr as $selected_id){
          if(!$selected_id) continue;
          $SQL = "SELECT 
                  BandID AS ID,
                  BaitID 
                  FROM $SQL_table 
                  WHERE $tmpID='$selected_id'  
                  GROUP BY BandID";
          $tmpSubArr = $HITSDB->fetchAll($SQL);
          for($i=0; $i<count($tmpSubArr); $i++){
            $SQL = "SELECT D.ID, D.BaitID, D.Location, B.GeneName, L.GelID, L.LaneNum";
            $FROM ="  FROM Band D
                      LEFT JOIN Bait B ON D.BaitID = B.ID
                      LEFT JOIN Lane L ON D.LaneID = L.ID
                      WHERE D.ID='".$tmpSubArr[$i]['ID']."'";
            $SQL .= $FROM;
            if($tmp_tmpSubArr = $HITSDB->fetch($SQL)){
              $tmpSubArr[$i]['Location'] = $tmp_tmpSubArr['Location'];
              $tmpSubArr[$i]['GeneName'] = $tmp_tmpSubArr['GeneName'];
              $tmpSubArr[$i]['GelID'] = $tmp_tmpSubArr['GelID'];
              $tmpSubArr[$i]['LaneNum'] = $tmp_tmpSubArr['LaneNum'];
            }
          }
		      foreach($tmpSubArr as $elementsValue){
            $initial_str = '';
            if(isset($has_notes_itemID_arr[$elementsValue['ID']])){
              foreach($has_notes_itemID_arr[$elementsValue['ID']] as $tmpTypeID){
                $VS = '';
                $tmp_version_num = $bait_group_icon_arr[$tmpTypeID]['Initial'];
                if(is_numeric($tmp_version_num))  $VS = 'VS';
                $initial_str .= "[".$VS.$tmp_version_num."]";
              }
            }
    				$gellStr = '';
    	  		if($elementsValue['GelID']){
    	  			$gellStr = $elementsValue['GelID']."&nbsp; &nbsp;".$elementsValue['LaneNum']."&nbsp; &nbsp;";
    	  		}
            if($before_type == 'switch_type' || $before_type == 'BaitID'){
              $PropertyArr_index = $elementsValue['BaitID'];
            }else{
              $PropertyArr_index = $elementsValue['ID'];
            }
            $sele_option = "<option id='".$elementsValue['ID']."' value='C_FFFFFF' class='C_FFFFFF'>";            
            //$sele_option = "<option id='".$elementsValue['ID']."' value='".$opPropertyArr[$PropertyArr_index][1]."' class='".$opPropertyArr[$PropertyArr_index][2]."'>";          
    	      $sele_optionStr .= $sele_option.$elementsValue['BaitID']."&nbsp; &nbsp;".$elementsValue['GeneName']."&nbsp; &nbsp;".$elementsValue['ID']."&nbsp; &nbsp;".$elementsValue['Location']."&nbsp; &nbsp;".$gellStr.$initial_str."\n";
    	  	  if($selected_id_str_new) $selected_id_str_new .= ",";
    			  $selected_id_str_new .= $elementsValue['ID'];
          }
        }
      }elseif($currentType == 'Exp'){
        if($selected_id_arr){         
          $selected_id_str = implode(",", $selected_id_arr);
          $SQL = "SELECT 
                  BandID
                  FROM $SQL_table 
                  WHERE $tmpID IN ($selected_id_str)  
                  GROUP BY BandID";
                  
          $tmpBandIDArr = $HITSDB->fetchAll($SQL);
          $tmp_band_id_str = '';
          foreach($tmpBandIDArr as $temVal){
            if($tmp_band_id_str) $tmp_band_id_str .= ",";
            $tmp_band_id_str .= $temVal['BandID'];
          }
          if($tmp_band_id_str){
            $SQL = "SELECT 
                    B.ExpID AS ID,
                    E.Name,
                    BA.ID AS BaitID,
                    BA.GeneName
                    FROM Band B
                    LEFT JOIN Experiment E ON (B.ExpID=E.ID)
                    LEFT JOIN Bait BA ON (B.BaitID = BA.ID)
                    WHERE B.ID IN ($tmp_band_id_str)
                    GROUP BY B.ExpID
                    ORDER BY B.ExpID DESC";
            $Exp_info_arr = $tmpBandIDArr = $HITSDB->fetchAll($SQL);
      		  foreach($Exp_info_arr as $elementsValue){
              $initial_str = '';
              if(isset($has_notes_itemID_arr[$elementsValue['ID']])){
                foreach($has_notes_itemID_arr[$elementsValue['ID']] as $tmpTypeID){
                  $VS = '';
                  $tmp_version_num = $bait_group_icon_arr[$tmpTypeID]['Initial'];
                  if(is_numeric($tmp_version_num))  $VS = 'VS';
                  $initial_str .= "[".$VS.$tmp_version_num."]";
                }
              }          
              $sele_option = "<option id='".$elementsValue['ID']."' value='C_FFFFFF' class='C_FFFFFF'>";
      	      $sele_optionStr .= $sele_option.$elementsValue['BaitID']."&nbsp; &nbsp;".$elementsValue['GeneName']."&nbsp; &nbsp;".$elementsValue['ID']."&nbsp; &nbsp;".$elementsValue['Name']."&nbsp; &nbsp;".$initial_str."\n";
      	  	  if($selected_id_str_new) $selected_id_str_new .= ",";
      			  $selected_id_str_new .= $elementsValue['ID'];
            }
          }
        }     
      }elseif($currentType == 'Bait'){
        $tmp_baitID_arr = array(); 
        $tmpSubArr = array();   
        foreach($selected_id_arr as $selected_id){
          if(!$selected_id) continue;
          $SQL = "SELECT 
                  BaitID
                  FROM $SQL_table 
                  WHERE $tmpID='$selected_id'  
                  GROUP BY BaitID";
          $tmpSubArr_tmp = $HITSDB->fetchAll($SQL);
          $tmp_flag = 0;
          for($i=0; $i<count($tmpSubArr_tmp); $i++){
            if(!in_array($tmpSubArr_tmp[$i]['BaitID'],$tmp_baitID_arr)){
              array_push($tmp_baitID_arr, $tmpSubArr_tmp[$i]['BaitID']);
              $SQL = "SELECT 
                      `ID` AS BaitID,
                      `GeneName`, 
                      `BaitAcc`, 
                      `Tag`, 
                      `Mutation` 
                      FROM `Bait` 
                      WHERE `ID`='".$tmpSubArr_tmp[$i]['BaitID']."'";
              if($tmp_tmpSubArr = $HITSDB->fetch($SQL)){
                $tmpSubArr_tmp[$i]['GeneName'] = $tmp_tmpSubArr['GeneName'];
                $tmpSubArr_tmp[$i]['BaitAcc'] = $tmp_tmpSubArr['BaitAcc'];
                $tmpSubArr_tmp[$i]['Tag'] = $tmp_tmpSubArr['Tag'];
                $tmpSubArr_tmp[$i]['Mutation'] = $tmp_tmpSubArr['Mutation'];
                array_push($tmpSubArr,$tmpSubArr_tmp[$i]);
              }
            }else{
              continue;
            }    
          }
        }  
  
  		  if($tmpSubArr){
          foreach($tmpSubArr as $elementsValue){
            if($selected_id_str_new) $selected_id_str_new .= ",";
            $selected_id_str_new .= $elementsValue['BaitID'];
            $initial_str = '';
            if(isset($has_notes_itemID_arr[$elementsValue['BaitID']])){
              foreach($has_notes_itemID_arr[$elementsValue['BaitID']] as $tmpTypeID){  
                $VS = '';
                $tmp_version_num = $bait_group_icon_arr[$tmpTypeID]['Initial'];
                if(is_numeric($tmp_version_num))  $VS = 'VS';
                $initial_str .= "[".$VS.$tmp_version_num."]";
              }
            }        
            $baitTag = '';
            if($elementsValue['Tag'] && $elementsValue['Mutation']){
              $baitTag = "(".$elementsValue['Tag'].";".$elementsValue['Mutation'].")";
            }elseif($elementsValue['Tag']){
              $baitTag = "(".$elementsValue['Tag'].")";
            }elseif($elementsValue['Mutation']){
              $baitTag = "(".$elementsValue['Mutation'].")";
            }
            
            $sele_option = "<option id='".$elementsValue['BaitID']."' value='C_FFFFFF' class='C_FFFFFF'>";
            //$sele_option = "<option id='".$elementsValue['BaitID']."' value='".$elementsValue['c1']."' class='".$elementsValue['c2']."'>";
            $sele_optionStr .= $sele_option.$elementsValue['BaitID']."&nbsp; &nbsp;".escapeSpace($elementsValue['GeneName']).$baitTag."&nbsp; &nbsp;".$elementsValue['BaitAcc']."&nbsp; &nbsp;".$initial_str."\n";
          }
        }
      }
    }
  }

  $tmpElementIdArr = array();  
  $tmpElementIdStr = get_real_elements_for_this_project($tmpElementIdArr,'',$SearchEngine);
  $group_type_id_arr = array();
  $frm_search_by = trim($frm_search_by); 
  if($tmpElementIdStr && $tmpElementIdStr != 'no_item' && $tmpElementIdStr != 'no_hits'){
    if($currentType == 'Band'){
      $group_type_id_arr_tmp = array('Bait'=>'BaitID','Experiment'=>'ExpID','Band'=>'');
      foreach($group_type_id_arr_tmp as $key => $val){
        if($tmpElementIdStr){
          if($key == 'Band'){
            $group_type_id_arr_tmp[$key] = $tmpElementIdStr;
          }else{
            $SQL = "SELECT $val FROM `Band` WHERE `ID` IN($tmpElementIdStr) GROUP BY $val";
            if($tmpExpIDarr = $HITSDB->fetchAll($SQL)){
              $tmp_id_arr = array();
              foreach($tmpExpIDarr as $tmpExpIDval){
                array_push($tmp_id_arr, $tmpExpIDval[$val]);
              }
              $tmp_bait_id_str = implode(",", $tmp_id_arr);
            }else{
              $tmp_bait_id_str = '';
            }
            $group_type_id_arr_tmp[$key] = $tmp_bait_id_str;
          }
        } 
      }
    }elseif($currentType == 'Exp'){
      if($tmpElementIdStr){
        $group_type_id_arr_tmp = array('Bait'=>'','Experiment'=>$tmpElementIdStr,'Band'=>'');
        $SQL = "SELECT BaitID FROM Experiment WHERE `ID` IN($tmpElementIdStr) GROUP BY BaitID";
        if($tmpExpIDarr = $HITSDB->fetchAll($SQL)){
          $tmp_id_arr = array();
          foreach($tmpExpIDarr as $tmpExpIDval){
            array_push($tmp_id_arr, $tmpExpIDval['BaitID']);
          }
          $tmp_bait_id_str = implode(",", $tmp_id_arr);
        }else{
          $tmp_bait_id_str = '';
        }
        $group_type_id_arr_tmp['Bait'] = $tmp_bait_id_str;
      }   
    }elseif($currentType == 'Bait'){
      if($tmpElementIdStr){   
        $group_type_id_arr_tmp = array('Bait'=>$tmpElementIdStr,'Experiment'=>'','Band'=>'');
      }
    }
  //---------------------------------------------------------------------------------
    if($frm_search_by && $tmpElementIdStr){
      $tmpElementIdStr = search_item_name($frm_search_by,$currentType,$tmpElementIdStr);
      $tmpElementIdArr = explode(",", $tmpElementIdStr);
    }
  //----------------------------------------------------------------------------------
    foreach($group_type_id_arr_tmp as $key2 => $val2){
      $group_type_id_arr[$key2] = array();
      if(($val2) && ($val2 != 'BaitID' && $val2 != 'ExpID' && $val2 != 'BandID' && $val2 != 'no_item')){
        $table_name = $key2."Group";
        $SQL = "SELECT `NoteTypeID` FROM $table_name WHERE RecordID IN($val2) GROUP BY`NoteTypeID`";
        $tmp_type_id_arr = $HITSDB->fetchAll($SQL);
        foreach($tmp_type_id_arr as $tmp_type_id_val){
          array_push($group_type_id_arr[$key2], $tmp_type_id_val['NoteTypeID']);
        }
      }  
    }  
  }else{
    echo "No Hits within project $AccessProjectID";
    exit;
  }
  
  $elementsArr = array();
  $max_mim_arr = array();
  $tage_max_mim_arr = array();
  $tmpOrderbyTageArr = array();
  if($selected_group_id){
    $tmpOrderbyTageArr = array();
    $table_name = $itemType."Group";    
    $SQL = "SELECT RecordID FROM $table_name WHERE `NoteTypeID`=$selected_group_id";
    $tmpArr = $HITSDB->fetchAll($SQL);
    $tmpStr = array_to_delimited_str($tmpArr, 'RecordID');
    if($tmpStr){    
      if($currentType == 'Band'){
        if($itemType == 'Bait'){
          $SQL = "SELECT `ID`, Location AS Name FROM `Band` WHERE `BaitID` IN($tmpStr) AND `ProjectID`='$AccessProjectID'";
        }elseif($itemType == 'Experiment'){
          $SQL = "SELECT `ID`, Location AS Name FROM `Band` WHERE `ExpID` IN($tmpStr) AND `ProjectID`='$AccessProjectID'";
        }elseif($itemType == 'Band'){
          $SQL = "SELECT `ID`, Location AS Name FROM `Band` WHERE `ID` IN($tmpStr) AND `ProjectID`='$AccessProjectID'";
        }
      }elseif($currentType == 'Exp'){
        if($itemType == 'Bait'){
          $SQL = "SELECT `ID`, Name FROM `Experiment` WHERE `BaitID` IN($tmpStr) AND `ProjectID`='$AccessProjectID'";
        }elseif($itemType == 'Experiment'){
          $SQL = "SELECT `ID`, Name FROM `Experiment` WHERE `ID` IN($tmpStr) AND `ProjectID`='$AccessProjectID'";
        }  
      }elseif($currentType == 'Bait'){
        $SQL = "SELECT `ID`, GeneName AS Name FROM `Bait` WHERE `ID` IN($tmpStr) AND `ProjectID`='$AccessProjectID'";
      }
      if($tmpArr = $HITSDB->fetchAll($SQL)){
        if($frm_search_by){
          foreach($tmpArr as $tmp_val){
            $pos = strpos(strtoupper($tmp_val['Name']), strtoupper($frm_search_by));
            if($pos !== false){
              array_push($tmpOrderbyTageArr, $tmp_val['ID']);
            }
          }
        }else{  
          array_to_array($tmpArr, 'ID', $tmpOrderbyTageArr);
        }  
      }
    }
    $OrderbyTageArr = array_intersect($tmpOrderbyTageArr, $tmpElementIdArr);
    $OrderbyTageStr = implode(",", $OrderbyTageArr);
    $startPoint = ($currentPage - 1) * $elementsPerPage;
    $max_mim_arr = get_source_elements_arr($elementsArr,$OrderbyTageStr,$startPoint,$elementsPerPage);
    $totalElements = count($OrderbyTageArr);
  }else{
    $startPoint = ($currentPage - 1) * $elementsPerPage;
    $max_mim_arr = get_source_elements_arr($elementsArr,$tmpElementIdStr,$startPoint,$elementsPerPage);
    $totalElements = count($tmpElementIdArr);
  }
    
  if($selected_id_str_new){
    $selectedElementArr = explode(',',$selected_id_str_new);
  }else{
    $selectedElementArr = array();
  }
  $optionStr = '';
   
  foreach($elementsArr as $elementsValue){
    if(in_array($elementsValue['ID'], $selectedElementArr)) continue;
    $initial_str = '';
    if(isset($has_notes_itemID_arr[$elementsValue['ID']])){
      foreach($has_notes_itemID_arr[$elementsValue['ID']] as $tmpTypeID){
        $VS = '';
        $tmp_version_num = $bait_group_icon_arr[$tmpTypeID]['Initial'];
        if(is_numeric($tmp_version_num))  $VS = 'VS';
        $initial_str .= "[".$VS.$tmp_version_num."]";
      }
    }
    $baitTag = '';
    if($elementsValue['Tag'] && $elementsValue['Mutation']){
      $baitTag = "(".$elementsValue['Tag'].";".$elementsValue['Mutation'].")";
    }elseif($elementsValue['Tag']){
      $baitTag = "(".$elementsValue['Tag'].")";
    }elseif($elementsValue['Mutation']){
      $baitTag = "(".$elementsValue['Mutation'].")";
    }
  	if($currentType == 'Bait'){
  	  $optionStr .= "<option id='".$elementsValue['ID']."'>".$elementsValue['ID']."&nbsp; &nbsp;".escapeSpace($elementsValue['GeneName']).$baitTag."&nbsp; &nbsp;".$elementsValue['BaitAcc']."&nbsp; &nbsp;".$initial_str."\n";
  	}elseif($currentType == 'Exp'){
      $optionStr .= "<option id='".$elementsValue['ID']."'>".$elementsValue['BaitID']."&nbsp; &nbsp;".$elementsValue['GeneName'].$baitTag."&nbsp; &nbsp;".$elementsValue['ID']."&nbsp; &nbsp;".$elementsValue['Name']."&nbsp; &nbsp".$initial_str."\n";
    }elseif($currentType == 'Band'){
  		$gellStr = '';
  		if($elementsValue['GelID']){
  			if(!$hasGel) $hasGel = 1;
  			$gellStr = $elementsValue['GelID']."&nbsp; &nbsp;".$elementsValue['LaneNum']."&nbsp; &nbsp;";
  		}
      $optionStr .= "<option id='".$elementsValue['ID']."'>".$elementsValue['BaitID']."&nbsp; &nbsp;".$elementsValue['GeneName'].$baitTag."&nbsp; &nbsp;".$elementsValue['ID']."&nbsp; &nbsp;".$elementsValue['Location']."&nbsp; &nbsp;".$gellStr.$initial_str."\n";
    }	
  }
  ($hasTage)?$tagLable='':$tagLable='';
  if($currentType == 'Bait'){
		$sourceTitle = "BaitID GeneName(Tag) ProteinID $tagLable";
  }elseif($currentType == 'Exp'){
    $sourceTitle = "BaitID GeneName(Tag) ExpID ExpName $tagLable";
  }elseif($currentType == 'Band'){
		if($hasGel){
			$sourceTitle = "BaitID GeneName(Tag) SampleID SampleName GellID LaneNum $tagLable";
		}else{
			$sourceTitle = "BaitID GeneName(Tag) SampleID SampleName $tagLable";
		}
	}
  $frm_min = $max_mim_arr['min']; 
  $frm_max = $max_mim_arr['max'];
  if($jointed){
    $frm_tage_min = $tage_max_mim_arr['min'];
    $frm_tage_max = $tage_max_mim_arr['max'];
  }
  if($Is_geneLevel){
    $hits_table = "Hits_GeneLevel";
  }elseif(strstr($SearchEngine, 'TPP')){
    $hits_table = "TppProtein";
  }else{
    $hits_table = "Hits";
  }
  
  
  
  ?>
  @@**@@source_target@@**@@<td width="33%" BGCOLOR="<?php echo $bg_tb;?>" align=center>
  <div class=sss2><?php echo $sourceType;?></div> 
  <div id='sourceTitle' class=sss><?php echo $sourceTitle;?></div>
    <select ID="frm_sourceList" name="frm_sourceList" size=20 multiple>
      <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
  		<?php echo $optionStr;?>
  	</select ><br><br>
    <input type='hidden' id='sourceTitleTxt' name='sourceTitleTxt' value='<?php echo $sourceTitle;?>'>
  	<input type='hidden' id='frm_max' name='frm_max' value='<?php echo $frm_max;?>'>
  	<input type='hidden' id='frm_min' name='frm_min' value='<?php echo $frm_min;?>'>
    <input type='hidden' id='frm_tage_max' name='frm_tage_max' value='<?php echo $frm_tage_max;?>'>
  	<input type='hidden' id='frm_tage_min' name='frm_tage_min' value='<?php echo $frm_tage_min;?>'>
    <input type='hidden' id='currentPage' name='currentPage' value='<?php echo $currentPage;?>'>
    <input type='hidden' id='currentType' name='currentType' value='<?php echo $currentType;?>'>
    <input type='hidden' id='hasGel' name='hasGel' value='<?php echo $hasGel;?>'>
    <input type='hidden' id='hasTage' name='hasTage' value='<?php echo $hasTage;?>'>
    <input type='hidden' id='isJointedPage' name='isJointedPage' value='<?php echo $isJointedPage;?>'>
    <input type='hidden' id='offset' name='offset' value='<?php echo $offset;?>'>
    <input type='hidden' id='offset' name='itemType' value='<?php echo $itemType;?>'>
    <input type='hidden' id='offset' name='hits_table' value='<?php echo $hits_table;?>'>
    <?php 
      $pageLable = create_page_lable($totalElements);
      echo $pageLable;
    ?>
  <center>
  <table border=0 cellspacing="2" cellpadding=2 width="320">
  <?php if($currentType == 'Band'){
      $search_by_lable = "Search <br>&nbsp;sample name";
      $item_table = "Band";
    }elseif($currentType == 'Exp'){
      $search_by_lable = "Search <br>&nbsp;experiment name";
      $item_table = "Experiment";
    }elseif($currentType == 'Bait'){
      $search_by_lable = "Search <br>&nbsp;gene name";
      $item_table = "Bait";
    }
  ?>
    <tr>
      <td align=left>User:</td>
      <td width="75%">
        <select name="frm_user">
          <option value=''>All users
        <?php 
          $SQL = "SELECT 
                  OwnerID
                  FROM $item_table
                  WHERE ProjectID='$AccessProjectID'
                  Group by OwnerID";
          $OwnerID_obj = $HITSDB->fetchAll($SQL);
          $users_id_str = array_to_delimited_str($OwnerID_obj,'OwnerID');
          if($users_id_str){
            $SQL = "SELECT `ID`,`Fname`,`Lname` FROM `User` WHERE `ID` IN($users_id_str) ORDER BY `Fname`";
            $users_id_name_arr = $PROHITSDB->fetchAll($SQL);
            foreach($users_id_name_arr as $users_val){
              if(!$users_val['ID'] || (!$users_val['Fname'] && !$users_val['Lname'])) continue;
              echo "<option value='".$users_val['ID']."'".(($frm_user==$users_val['ID'])?' selected':'').">".$users_val['Fname']." ".$users_val['Lname']."</option>\r\n";
            }
          }  
        ?>
        </select>
      </td>
    </tr>  
    <tr>
      <td align=left>Search:</td>
      <td>
        <input type="text" name="frm_search_by" value="<?php echo $frm_search_by?>">
      </td>
    </tr>  
  <?php if($currentType == 'Band' || $currentType == 'Exp'){?>  
    <tr>
      <td align=left>Group type:</td>
      <td>
    <?php if($currentType == 'Band'){?> 
          <input type="radio" name="frm_groups" value="Bait" onClick="toggle_group(this.form)" <?php echo (($frm_groups=='Bait')?'checked':'')?>>Bait&nbsp;
        	<input type="radio" name="frm_groups" value="Experiment" onClick="toggle_group(this.form)" <?php echo (($frm_groups=='Experiment')?'checked':'')?>>Experiment&nbsp;
        	<input type="radio" name="frm_groups" value="Band" onClick="toggle_group(this.form)" <?php echo (($frm_groups=='Band')?'checked':'')?>>Sample&nbsp;
    <?php }elseif($currentType == 'Exp'){?>
          <input type="radio" name="frm_groups" value="Bait" onClick="toggle_group(this.form)" <?php echo (($frm_groups=='Bait')?'checked':'')?>>Bait&nbsp;
        	<input type="radio" name="frm_groups" value="Experiment" onClick="toggle_group(this.form)" <?php echo (($frm_groups=='Experiment')?'checked':'')?>>Experiment&nbsp;
    <?php }?>     
       </td>
    </tr>
  <?php }?>
    <tr><td rowspan=1>Show group:</td>
    <td>
  <?php foreach($item_group_icon_arr as $item_key => $item_val){
      //if($item_key == 'Export') continue;
      $group_arr = $item_val;
      $selection_name = $item_key."_order_by";
  ?>
    
    <div id='<?php echo $item_key?>' STYLE="<?php echo ($item_key==$itemType)?'display: block':'display: none'?>">
    <table border=0 cellspacing="0" cellpadding=0>
    <tr>
      <td>
      <select name="<?php echo $selection_name?>">
      <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
    <?php foreach($group_arr as $group_key => $group_val){
        if(!array_key_exists($item_key, $group_type_id_arr)) continue;
        if(!in_array($group_key, $group_type_id_arr[$item_key])) continue;
        $VS = '';
        if(is_numeric($group_val['Initial'])) $VS = 'VS';
        echo "<option value='$group_key'".(($selected_group_id==$group_key)?'selected':'').">".$group_val['Name']." ($VS".$group_val['Initial'].")</option>\r\n";
      }
    ?>
      </select>
      </td>
    </tr>
    </table>
    </div>
  <?php }?>
    </td>
    </tr>
    <tr><td align=left>   
    Sort by:
    </td>
    <td>
    <select id="frm_order_by" name="frm_order_by">
      <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
  <?php if($currentType == 'Bait'){?>
      <option value='ID' <?php echo ($frm_order_by=='ID')?'selected':''?>>BaitID</option>
      <option value='GeneName' <?php echo ($frm_order_by=='GeneName')?'selected':''?>>Gene Name</option>
      <option value='BaitAcc' <?php echo ($frm_order_by=='BaitAcc')?'selected':''?>>Protein ID</option>
  <?php }elseif($currentType == 'Exp'){?>
      <option value='E.BaitID' <?php echo ($frm_order_by=='E.BaitID')?'selected':''?>>BaitID</option>
      <option value='B.GeneName' <?php echo ($frm_order_by=='B.GeneName')?'selected':''?>>Gene Name</option>
      <option value='E.ID' <?php echo ($frm_order_by=='E.ID')?'selected':''?>>Exp ID</option>    
      <option value='E.Name' <?php echo ($frm_order_by=='E.Name')?'selected':''?>>Exp Name</option>
  <?php }elseif($currentType == 'Band'){?>
      <option value='D.BaitID' <?php echo ($frm_order_by=='D.BaitID')?'selected':''?>>Bait ID</option>
      <option value='D.ID' <?php echo ($frm_order_by=='D.ID')?'selected':''?>>Sample ID</option>
      <option value='D.Location' <?php echo ($frm_order_by=='D.Location')?'selected':''?>>Sample Name</option>
      <option value='B.GeneName' <?php echo ($frm_order_by=='B.GeneName')?'selected':''?>>Gene Name</option>
      <?php if($hasGel){?> 
        <option value='L.GelID' <?php echo ($frm_order_by=='L.GelID')?'selected':''?>>Gel ID</option>
      <?php }?>
  <?php }?>
    </select>
    </td>
    </tr>
    <?php if($currentType == 'Bait'){
        $sort_lable = 'Sort bait list';
      }elseif($currentType == 'Exp'){
        $sort_lable = 'Sort experiment list';
      }elseif($currentType == 'Band'){
        $sort_lable = 'Sort sample list';
      }
    ?>
    <tr>
      <td colspan=2 align=center>
      <input type=button name='sort' value=' Go ' onClick="startRequest('changeOrderby','')">
      </td>
    </tr>
    </table>
    </center>
    <br><br>
	@@**@@<select id="frm_selected_list" name="frm_selected_list" size=20 multiple>
         <option id='' value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
		<?php echo $sele_optionStr;?>		  
  </select><br><br><br>@@**@@<table border="0"  height="20" cellspacing="0" cellpadding=0 width="300">
      <tr><td>
      <?php 
        if(strstr($SearchEngine, 'TPP')){
          $engineKey = "tpp";
        }elseif($Is_geneLevel){
          $engineKey = "geneLevel";
        }else{
          $engineKey = "Mascot";
        }
        $inde_filter_arr = $filter_arr_U[$engineKey];
        foreach($inde_filter_arr as $field_key => $field_val){
          if($field_key == 'Expect2') $engineKey = 'GPM';
          $frmName = $engineKey.'_'.$field_key;
          echo $filter_lable_arr[$field_key].'&nbsp;&nbsp;';
          $biggestNum = get_max_Num($engineKey,$field_key)."<br>";
          create_filter_selections($field_key,$frmName,$biggestNum);
          echo "<br>";
        }
      ?>
      </td>
      </tr>
    </table>@@**@@<?php echo ($subAction)?$subAction:"list_sub_bands";?>
	<?php 
}

//-return real elements(bait, experiment or band) (GPM, Mascot,orOther) (owner or all owner)for this project.
function cmp_m_time_r($a, $b){
  //global $orderby;
  if($a['m_time'] < $b['m_time']){
    return 1;
  }
  return -1;
}

function delete_U_frequency($frm_frequency_name){
  global $frequency_dir_arr; 
  $tmp_f_name_arr = explode(":", $frm_frequency_name);
  $deleted_frequency_file_name = $frequency_dir_arr[$tmp_f_name_arr[0]]."/".$tmp_f_name_arr[1];
  if(is_file($deleted_frequency_file_name)){
    if(unlink($deleted_frequency_file_name)){
      $frm_frequency_name = '';
    }else{
      echo "Cannot delete frequency file: $deleted_frequency_file_name<br>";
    }
  }  
}

function get_max_Num($SearchEngine,$filter_Field){

   global $HITSDB;
//echo "\$SearchEngine=$SearchEngine<br>";
  if($filter_Field == 'PROBABILITY'){
    return 1;
  }else {
    return 1000;
  }
  /*
  global $HITSDB;
//echo "\$SearchEngine=$SearchEngine<br>";
  if($SearchEngine == 'tpp'){
    $HitsTable = 'TppProtein';
    $WHERE = "";
  }elseif($SearchEngine == 'geneLevel'){
    $HitsTable = 'Hits_GeneLevel';
    $WHERE = "";
  }else{  
    $HitsTable = 'Hits';
    $WHERE = " WHERE SearchEngine='$SearchEngine'";
  }
  if($SearchEngine == 'GPM' && $filter_Field == 'Expect2'){
    $MAX = 'MIN';
  }else{
    $MAX = 'MAX';
  }
	$SQL = "SELECT $MAX(`".$filter_Field."`) as biggestNum FROM $HitsTable $WHERE";
//echo "<br>\$SQL=$SQL<br>";
	$hitsArrTmp2 = $HITSDB->fetch($SQL);
	$maxScore = $hitsArrTmp2['biggestNum'];
  if($maxScore < 0) $maxScore = -1 * $maxScore;  
  return $maxScore;
  */
}

function create_filter_selections($listName,$frmName,$biggestNum){
  global $$frmName,$orderby,$SearchEngine;  
  global $M_SpecSum,$M_maxSpec;
  global $M_INTENSITYSUM,$M_INTENSITY;
  global $frm_selected_item_str;  
 
  $kk = 1;   
  $sign = '';
  if($listName == 'Expect2'){
    $sign = '-';
  }
  if($biggestNum >=1000){
    $numLen = 10;
  }elseif($biggestNum <1000 && $biggestNum >=100){
    $numLen = 5;
  }elseif($biggestNum <100 && $biggestNum >=50){
    $numLen = 2;
  }elseif($biggestNum <50 && $biggestNum >=10){
    $numLen = 1;
  }elseif($biggestNum <10 && $biggestNum >1){
    $numLen = 0.1;  
  }elseif($biggestNum <=1 && $biggestNum >=0.1){
    $numLen = 0.01;
  }
  echo "<select name=\"$frmName\" size=1>\r\n";
  echo "<option value='0' selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  $ss = 1;
  if($frmName == 'NumRep'){
    $ss = '2';
  }
  $numbers = 0;
  while($numbers < $biggestNum){
    //if($listName == 'Pep_num' || $listName == 'Expect' || $listName='Unique peptide'){
    if($listName == 'SpectralCount' || $listName == 'Unique' || $listName=='Pep_num' || $listName == 'Pep_num_uniqe'|| $listName == 'Expect'|| $listName == 'TOTAL_NUMBER_PEPTIDES'|| $listName == 'UNIQUE_NUMBER_PEPTIDES'){
      if($numbers < 50){
        $numbers++;
      }elseif($numbers >= 50 && $numbers < 100){
        $numbers += 5;
      }else{
        $numbers += 10;
      }
      if($numbers >= 10) $kk = round($numbers/$numLen) + 1;
    }elseif($listName == 'Expect2'){
      if($numbers < 20){
        $numbers = $ss++;
      }elseif($numbers >= 20 && $numbers < 100){
        $numbers += 5;
      }else{
        $numbers += 10;
      }
 
    }elseif($listName == 'PROBABILITY'){
      $numbers += $numLen;
    }
    echo "<option value=\"$sign$numbers\" ".(($$frmName==$sign.$numbers)?'selected':'').">$sign$numbers\r\n";
  } 
  echo '</select>';
}


//------------------------------------------------------------------------------------
function get_old_filter_status(){
  global $Prohits_Data_dir;
  global $AccessProjectID;
  global $Frequency_filters_file;
  
  $filter_arr = array();
  if(is_file($Frequency_filters_file)){
    $filter_file_arr = file($Frequency_filters_file);
    $filter_file_arr = array_reverse($filter_file_arr);
    foreach($filter_file_arr as $filter_file_val){
      if(strpos($filter_file_val, "#####") === 0) break;
      $filter_str_tmp = trim($filter_file_val);
      $tmp_arr = explode("::", $filter_str_tmp);
      $SearchEngine = $tmp_arr[0];
      if(trim($tmp_arr[1])){
        $pieces = explode(",", $tmp_arr[1]);
        foreach($pieces as $piece){
          $tmp_arr2 =preg_split("/[><]+/", $piece);
          if(!trim($tmp_arr2[1])) continue;
          $filter_arr[$SearchEngine][$tmp_arr2[0]] = $tmp_arr2[1];
        }
      }
    }
  }
  return $filter_arr;
}


function get_new_filter_status(){
  global $request_arr;
  global $filter_arr;
  $tmp_line_arr = array();
  $tmp_line_sub_arr = array();  
  foreach($request_arr as $request_key => $request_val){
    if($request_key == 'theaction') $theaction = $request_val;
    $tmp_key_arr = explode("00", $request_key);
    if(!strstr($tmp_key_arr[0], 'GPM') && !strstr($tmp_key_arr[0], 'Mascot') && !strstr($tmp_key_arr[0], 'tpp') && !stristr($tmp_key_arr[0], 'GeneLevel')) continue;    
   
    if(!array_key_exists($tmp_key_arr[0], $tmp_line_arr)){
      $tmp_line_arr[$tmp_key_arr[0]] = $tmp_key_arr[0]."::";
      $tmp_line_sub_arr[$tmp_key_arr[0]] = '';
    }
    
    if($tmp_key_arr[1] == 'Expect2'){
      if($request_val){
        if($tmp_line_sub_arr[$tmp_key_arr[0]]) $tmp_line_sub_arr[$tmp_key_arr[0]] .= ",";
        //$tmp_line_sub_arr[$tmp_key_arr[0]] .= $tmp_key_arr[1].">".$request_val;
        $tmp_line_sub_arr[$tmp_key_arr[0]] .= $tmp_key_arr[1]."<".$request_val;
      }  
    }else{
      if($request_val){
        if($tmp_line_sub_arr[$tmp_key_arr[0]]) $tmp_line_sub_arr[$tmp_key_arr[0]] .= ",";
        //$tmp_line_sub_arr[$tmp_key_arr[0]] .= $tmp_key_arr[1]."<".$request_val;
        $tmp_line_sub_arr[$tmp_key_arr[0]] .= $tmp_key_arr[1].">".$request_val;
      }  
    }
  }
  foreach($tmp_line_sub_arr as $tmp_line_sub_key => $tmp_line_sub_val){
     $tmp_line_arr[$tmp_line_sub_key] .= $tmp_line_sub_val;
  }  
  foreach($filter_arr as $key => $val){
    if(!array_key_exists($key, $tmp_line_arr)){
      $tmp_line_arr[$key] = $key."::";
    }
  }
  return $tmp_line_arr;
}

function get_new_filter_status_U(){
  global $request_arr;  
  $tmp_line_arr = array();
  $tmp_line_sub_arr = array();
  foreach($request_arr as $request_key => $request_val){
    if($request_key == 'theaction') $theaction = $request_val;
    $tmp_key_arr = explode("_", $request_key,2);
    if(!strstr($tmp_key_arr[0], 'GPM') && !strstr($tmp_key_arr[0], 'Mascot') && !strstr($tmp_key_arr[0], 'tpp') && !stristr($tmp_key_arr[0], 'geneLevel')) continue;
    if(!array_key_exists($tmp_key_arr[0], $tmp_line_arr)){
      $tmp_line_arr[$tmp_key_arr[0]] = $tmp_key_arr[0]."::";
      $tmp_line_sub_arr[$tmp_key_arr[0]] = '';
    }
    
    if($tmp_key_arr[1] == 'Expect2'){
      if($request_val){
        if($tmp_line_sub_arr[$tmp_key_arr[0]]) $tmp_line_sub_arr[$tmp_key_arr[0]] .= ",";
        $tmp_line_sub_arr[$tmp_key_arr[0]] .= $tmp_key_arr[1]."<".$request_val;
      }  
    }else{
      if($request_val){
        if($tmp_line_sub_arr[$tmp_key_arr[0]]) $tmp_line_sub_arr[$tmp_key_arr[0]] .= ",";
        $tmp_line_sub_arr[$tmp_key_arr[0]] .= $tmp_key_arr[1].">".$request_val;
      }  
    }
  }
  foreach($tmp_line_sub_arr as $tmp_line_sub_key => $tmp_line_sub_val){
     $tmp_line_arr[$tmp_line_sub_key] .= $tmp_line_sub_val;
  }
  if(isset($tmp_line_arr['GPM'])){
    if(preg_match("/.+?(Pep_num.+)/i",$tmp_line_arr['GPM'],$matches)){
      $tmp_arr = explode("::",$tmp_line_arr['Mascot']);
      if(trim($tmp_arr[1])){
        $tmp_line_arr['Mascot'] .= ",".$matches[1];
      }else{
        $tmp_line_arr['Mascot'] .= $matches[1];
      }
    }
  }
  return $tmp_line_arr;
} 

function update_log_for_individual($diff_arr,$frm_frequency_name){
  global $filter_status_arr,$filter_file_arr;
  global $Frequency_filters_file;
  global $AccessUserName;
  preg_match("/^P:P\d+_(.+)_frequency\.csv$/i",$frm_frequency_name,$matches);
  $changed_searchE = $matches[1];
  if(array_key_exists($changed_searchE, $diff_arr)){
    if(!$filter_file_arr){
      $filter_file_arr = $filter_status_arr;
    }else{
      $changed_str = $filter_status_arr[$changed_searchE];
      for($i=0; $i<count($filter_file_arr); $i++){
        $tmp_arr = explode("::", $filter_file_arr[$i]);
        if($tmp_arr[0] == $changed_searchE){
          $filter_file_arr[$i] = $changed_str;
          break;
        }
      }
    }
    
  }
  $fp = fopen($Frequency_filters_file, 'a');
  fwrite($fp, "#####".@date("Y-m-d H:i:s")."---Changed by $AccessUserName\r\n");
  foreach($filter_file_arr as $filter_file_val){
    fwrite($fp, $filter_file_val."\r\n");
  }  
  fclose($fp);
}

function delete_unexist_frequency($file_name){
  global $frequency_dir;
  global $sub_frequency_dir;
  global $user_frequency_dir;
  
  $tmp_arr = explode(':',$file_name);
  if($tmp_arr[0] == 'P'){
    $file_full_name = $frequency_dir."/".$tmp_arr[1];
  }elseif($tmp_arr[0] == 'G'){
    $file_full_name = $sub_frequency_dir."/".$tmp_arr[1];
  }elseif($tmp_arr[0] == 'U'){
    $file_full_name = $user_frequency_dir."/".$tmp_arr[1];
  }
  unlink($file_full_name);
}
?>
