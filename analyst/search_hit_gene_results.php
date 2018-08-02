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
$add_process_img = 1;
$order_by = '';
$hits_index = '';
$is_desc = '';
$opened_str = '';

require("../common/site_permission.inc.php");
include("analyst/common_functions.inc.php");
require("common/common_fun.inc.php");
require_once("msManager/is_dir_file.inc.php");

if($theaction == "export_search_result"){
  export_file($theFile);
  exit;
}
ini_set("memory_limit","118M");
$project_info_arr = explode(",", $project_info);
$_SESSION["workingProjectID"] = $AccessProjectID = $project_info_arr[0];
$_SESSION["workingProjectName"] = $AccessProjectName = $project_info_arr[1];
$_SESSION["workingProjectTaxID"] = $AccessProjectTaxID = $project_info_arr[2]; 
$_SESSION["workingFilterSetID"] = $AccessProjectSetID = $project_info_arr[3];
$_SESSION["workingProjectFrequency"] = $AccessProjectFrequency = $project_info_arr[4];
$_SESSION["workingDBname"] = $AccessDBname = $project_info_arr[5];
$HITSDB = new mysqlDB($project_info_arr[5]);

$frm_taxID = '';
$bgcolor = $TB_CELL_COLOR;
$bgcolordark = "#c5b781";

require("site_header.php");

$outDir = "../TMP/hit_search_export/";
if(!_is_dir($outDir)) _mkdir_path($outDir);

$SQL = "SELECT `ID`,
               `Name`,
               `TaxID`,
               `DBname`
               FROM `Projects`";
$Projects_arr = $PROHITSDB->fetchAll($SQL);
$Projects_ID_name_arr = array(); 
$Projects_ID_DB_arr = array();
$Projects_ID_taxID_arr = array();
foreach($Projects_arr as $Projects_val){
  $Projects_ID_name_arr[$Projects_val['ID']] = $Projects_val['Name'];
  $Projects_ID_taxID_arr[$Projects_val['ID']] = $Projects_val['TaxID'];
  $Projects_ID_DB_arr[$Projects_val['ID']] = $Projects_val['DBname'];
}
$frequency_base_arr = array();
$gene_ID_name_arr = array(); 
$gene_ID_taxID_arr = array();
$project_isGel_arr = array();

$project_frequency_base_arr = array();

$filename_out = $outDir.$AccessUserID."_hit_search_result.csv";


if($theaction == "create_basic_file"){
  if($frm_addwildcard == 'end'){
    $title_lable = $frm_search_str."*";
  }else{
    $title_lable = $frm_search_str;
  }
  $handle_write = fopen($filename_out, "w");
  create_basic_file();
  fclose($handle_write);
  echo "<script language=javascript>document.location.href='$PHP_SELF?project_info=$project_info&title_lable=$title_lable&projects=$selected_str';</script>"; 
  exit;
}

$report_sorting_arr = array();
$filename_in = $outDir.$AccessUserID."_hit_search_result.csv";
$file_lines = file($filename_in);
if($file_lines === false){
  exit;
}

$handle_write = fopen($filename_in, "w");
foreach($file_lines as $file_line){
  $file_line_tmp = trim($file_line);
  if(!$file_line_tmp) continue;
  if(strstr($file_line_tmp, 'Search hits')) continue;
  if(strstr($file_line_tmp, 'Projects:')) continue;
  if(strstr($file_line_tmp, 'Hit Gene Name (Gene ID),')) continue;

  $tmp_arr = explode(",", $file_line_tmp);
  if(!array_key_exists($tmp_arr[0], $report_sorting_arr)){
    $report_sorting_arr[$tmp_arr[0]] = array();
  }
  if(!array_key_exists($tmp_arr[1], $report_sorting_arr[$tmp_arr[0]])){
    $report_sorting_arr[$tmp_arr[0]][$tmp_arr[1]] = array();
  }
  array_push($report_sorting_arr[$tmp_arr[0]][$tmp_arr[1]], $tmp_arr);
}

$selected_project_arr = explode(",", $projects);
$selected_project_str = '';
$selected_project_str_for_file = '';
foreach($selected_project_arr as $selected_project){
  if($selected_project_str) $selected_project_str .= "<br>";
  $selected_project_str .= $Projects_ID_name_arr[$selected_project]."($selected_project) ";
  $selected_project_str_for_file .= $Projects_ID_name_arr[$selected_project]."($selected_project); ";
} 
$title_lable_for_display = "<font color='#000080' size='2'>Search Hits: <font color='#ff0000' size='2'>".$title_lable."</font><br>Projects: </font><br>".$selected_project_str;
$title_lable_for_file = "Search hits: '".str_replace(",", ";",$title_lable )."'\r\nProjects: ".$selected_project_str_for_file."\r\n";
?>
<script language="JavaScript" type="text/javascript">
function toggle_detail(base_id){
  var theForm = document.search_hits_form;
  var id_1 = base_id + "_1";
  var id_2 = base_id + "_2";
  var id_3 = base_id + ",";
  var base_id_obj = document.getElementById(base_id);
  if(base_id_obj.innerHTML == '[+]'){
    document.getElementById(id_1).style.display = "none";
    document.getElementById(id_2).style.display = "block";
    base_id_obj.innerHTML = '[-]';
    theForm.opened_str.value += id_3;
  }else{
    document.getElementById(id_1).style.display = "block";
    document.getElementById(id_2).style.display = "none";
    base_id_obj.innerHTML = '[+]';
    theForm.opened_str.value = theForm.opened_str.value.replace(id_3,"");
  }
}
function sortList(order_by){
  var theForm = document.search_hits_form;
  theForm.order_by.value = order_by;
  theForm.submit();
}
</script>
<form name="search_hits_form" action="<?php echo $PHP_SELF;?>" method="post">
<input type="hidden" name="opened_str" value="<?php echo $opened_str?>">
<input type="hidden" name="order_by" value="">
<input type="hidden" name="title_lable" value="<?php echo $title_lable?>">
<input type="hidden" name="project_info" value="<?php echo $project_info?>">
<input type="hidden" name="projects" value="<?php echo $projects?>">
<table border="0" cellpadding="0" cellspacing="0" width="95%"> 
  <tr>
    <td align="left"><br>
		&nbsp; <font color="navy" face="helvetica,arial,futura" size="5"><b>Advanced Search
    <?php 
      if($AccessProjectName){
        echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font>";
      }
    ?>
    </b> 
		</font> 
	  </td>
    <td align="right">
     &nbsp;
    </td>
  </tr>
  <tr>
  	<td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td colspan=2 align="center"><br>
    <div style="width:1300px;border: red solid 0px;">
    <table border="0" cellpadding="0" cellspacing="1" width="1300">
      <tr bgcolor="">
        <td height="25" bgcolor="" align="left" colspan="6">
          <div class=maintext>
            <?php echo $title_lable_for_display?>
          </div>
        </td>
        <td height="25" bgcolor="" align="right" valign="bottom" colspan="3">
          <div class=maintext>
            <a href="<?php echo $PHP_SELF;?>?theaction=export_search_result&theFile=<?php echo $filename_out?>"  title='export search result'>[Export search result]</a>
          </div>
        </td>
      </tr>
      <tr bgcolor="">
        <td width="200" height="20" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader>Hit Gene Name (Gene ID)</div>
        </td>
        <td width="250" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader>Project</div>
        </td>
        <td width="150" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader>
            <!--a href="<?php echo $PHP_SELF;?>?order_by=<?php echo ($order_by == "Bait_ID")?'Bait_ID.desc':'Bait_ID';?>"-->
            <a href="javascript: sortList('<?php echo ($order_by == "Bait_ID")?'Bait_ID.desc':'Bait_ID';?>');">
              Bait Name (Bait ID)
            </a>
            <?php 
              if($order_by == "Bait_ID") echo "<img src='images/icon_order_up.gif'>";
              if($order_by == "Bait_ID.desc") echo "<img src='images/icon_order_down.gif'>";
            ?>
          </div>
        </td>
        <td width="270" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader>Sample Name (Sample ID)</div>
        </td>
        <td width="150" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader>
            <!--a href="<?php echo $PHP_SELF;?>?order_by=<?php echo ($order_by == "Search_Engine")?'Search_Engine.desc':'Search_Engine';?>"-->
            <a href="javascript: sortList('<?php echo ($order_by == "Search_Engine")?'Search_Engine.desc':'Search_Engine';?>');">
              Search Engine
            </a>
            <?php 
              if($order_by == "Search_Engine") echo "<img src='images/icon_order_up.gif'>";
              if($order_by == "Search_Engine.desc") echo "<img src='images/icon_order_down.gif'>";
            ?>
          </div>
        </td>
        <td width="100" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader>Frequency</div> 
        </td>
        <td width="60" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader>
            <!--a href="<?php echo $PHP_SELF;?>?order_by=<?php echo ($order_by == "Max_Peptide")?'Max_Peptide.desc':'Max_Peptide';?>"-->
            <a href="javascript: sortList('<?php echo ($order_by == "Max_Peptide")?'Max_Peptide.desc':'Max_Peptide';?>');">
              Max. Peptide
            </a>
            <?php 
              if($order_by == "Max_Peptide") echo "<img src='images/icon_order_up.gif'>";
              if($order_by == "Max_Peptide.desc") echo "<img src='images/icon_order_down.gif'>";
            ?>
          </div>
        </td>
        <td width="60" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader>
            <!--a href="<?php echo $PHP_SELF;?>?order_by=<?php echo ($order_by == "Unique_Peptide")?'Unique_Peptide.desc':'Unique_Peptide';?>"-->
            <a href="javascript: sortList('<?php echo ($order_by == "Unique_Peptide")?'Unique_Peptide.desc':'Unique_Peptide';?>');">
              Unique Peptide
            </a>
            <?php 
              if($order_by == "Unique_Peptide") echo "<img src='images/icon_order_up.gif'>";
              if($order_by == "Unique_Peptide.desc") echo "<img src='images/icon_order_down.gif'>";
            ?>
          </div>
        </td>
        <td width="60" bgcolor="<?php echo $bgcolordark;?>" align="center">
          <div class=tableheader>Options</div>
        </td>
      </tr>
  <?php 
    fwrite($handle_write, $title_lable_for_file."\r\n");
    $filedNameStr = "Hit Gene Name (Gene ID),Project,Bait ID (Bait Name),Sample ID (Sameple Name),Search Engine,Frequency,Max. Peptide,Unique Peptide";
    fwrite($handle_write, $filedNameStr."\r\n");
    $display_counter = 0; 
    $opened_arr = explode(",", $opened_str);
    foreach($report_sorting_arr as $key => $report_val){
      $hits_arr = $report_val;
      if(in_array($key, $opened_arr)){
        $gene_id_sign = "[-]";
        $display_1 = "display: none";
        $display_2 = "display: block";
      }else{
        $gene_id_sign = "[+]";
        $display_1 = "display: block";
        $display_2 = "display: none";
      }

   ?>
      <tr bgcolor='<?php echo $bgcolor;?>'>
        <td width="200" bgcolor="#9797cc" valign=top>
          &nbsp;<a href="javascript: toggle_detail('<?php echo $key?>')" class=Button>
            <span id="<?php echo $key?>" style="color:white;"><?php echo $gene_id_sign?></span>
            </a>
          &nbsp;<span class=tableheader><?php echo $key?></span>&nbsp;
        </td>
        <td width="1100" colspan="8">
          <div id="<?php echo $key."_1"?>" STYLE="width:100%;<?php echo $display_1?>;border: #9797cc solid 1px">
          <table border="0" cellpadding="0" cellspacing="0" width="100%">
   <?php 
            print_one_hit_gene($key,$hits_arr,'1');
   ?>
          </table>
          </div>
          <div id="<?php echo $key."_2"?>" STYLE="width:100%;<?php echo $display_2?>;border: #9797cc solid 1px">
          <table border="0" cellpadding="0" cellspacing="0" width="100%">
   <?php 
            print_one_hit_gene($key,$hits_arr);
   ?>
          </table>
          </div>
        </td>
      </tr>
   <?php 
    }
   ?>
    </table>
    </div>
    </td>
  </tr>
</table>
</form>
<script language='javascript'>
document.getElementById('process').style.display = 'none';
</script>
<?php 
fclose($handle_write);
//echo "disply end: ".date("H:i:s")."<br>";
function create_basic_file(){
  global $frm_search_str;
  global $frm_addwildcard;
  global $selected_str;
  global $Projects_ID_name_arr; 
  global $Projects_ID_DB_arr;
  global $Projects_ID_taxID_arr;
  global $HITS_DB;
  global $AccessUserID;
  global $frequency_base_arr;
  global $handle_write;
  global $gene_ID_name_arr;  
  global $gene_ID_taxID_arr;
  global $project_isGel_arr;
  global $is_frequency_base_on_sample;

  $proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);
  $gene_name_arr = explode(",", $frm_search_str);
  $projects_arr = explode(',',$selected_str);
  $tax_ID_str = '';
  $DB_ProjectIDs_arr = array(); 
  foreach($projects_arr as $projects_id){
    $tmp_db_name = $Projects_ID_DB_arr[$projects_id];
    
    if(!array_key_exists($tmp_db_name, $DB_ProjectIDs_arr)){
      $DB_ProjectIDs_arr[$tmp_db_name] = $projects_id;
    }else{
      $DB_ProjectIDs_arr[$tmp_db_name] .= ','.$projects_id;
    }
    if(!isset($Projects_ID_taxID_arr[$projects_id]) || !$Projects_ID_taxID_arr[$projects_id]){
      $tax_ID_str = '';
      //break;
    }
    if($tax_ID_str) $tax_ID_str .= ',';
    $tax_ID_str .= $Projects_ID_taxID_arr[$projects_id]; 
  }
  if($tax_ID_str and 0){
    $sub_sql = " TaxID IN ($tax_ID_str) AND ";
  }else{
    $sub_sql = "";
  }
  if($frm_addwildcard){
    $sub_sql_2 = "";
    for($i=0;$i<count($gene_name_arr);$i++){
      if($sub_sql_2) $sub_sql_2 .= " OR ";
      $sub_sql_2 .= "GeneName LIKE '".$gene_name_arr[$i]."%'";
    }
    if($sub_sql_2){
      $sub_sql_2 = "(".$sub_sql_2.")";
      $sub_sql .= $sub_sql_2;
    }
    if($sub_sql) 
    $SQL = "SELECT `EntrezGeneID`, 
                   `GeneName`,
                   `TaxID` 
                   FROM `Protein_Class` 
                   WHERE $sub_sql";
                   
    $SQL2 = "SELECT `ENSG` AS EntrezGeneID, 
                   `GeneName`,
                   `TaxID` 
                   FROM `Protein_ClassENS` 
                   WHERE $sub_sql";
                   
  }else{
    $gene_name_str = str_replace(",", "','", $frm_search_str);
    $gene_name_str = "'".$gene_name_str."'";
    $SQL = "SELECT `EntrezGeneID`, 
                   `GeneName`,
                   `TaxID` 
                   FROM `Protein_Class` 
                   WHERE $sub_sql GeneName IN ($gene_name_str)";
                   
    $SQL2 = "SELECT `ENSG` AS EntrezGeneID, 
                   `GeneName`,
                   `TaxID` 
                   FROM `Protein_ClassENS` 
                   WHERE $sub_sql GeneName IN ($gene_name_str)";               
                   
  }
  $Protein_Class_arr = $proteinDB->fetchAll($SQL);
  $Protein_ClassENS_arr = $proteinDB->fetchAll($SQL2);
  if(!$Protein_Class_arr && !$Protein_ClassENS_arr){
    echo "no results<br>";
    exit;
  }
  $gene_ID_str = '';
  foreach($Protein_Class_arr as $Protein_Class_val){
    if($Protein_Class_val['EntrezGeneID'] <= 0) continue;
    if($gene_ID_str) $gene_ID_str .= "','";
    $gene_ID_str .= $Protein_Class_val['EntrezGeneID'];
    $gene_ID_name_arr[$Protein_Class_val['EntrezGeneID']] = $Protein_Class_val['GeneName'];
    $gene_ID_taxID_arr[$Protein_Class_val['EntrezGeneID']] = $Protein_Class_val['TaxID'];
  }  
  foreach($Protein_ClassENS_arr as $Protein_Class_val){
    if(!$Protein_Class_val['EntrezGeneID']) continue;
    if($gene_ID_str) $gene_ID_str .= "','";
    $gene_ID_str .= $Protein_Class_val['EntrezGeneID'];
    $gene_ID_name_arr[$Protein_Class_val['EntrezGeneID']] = $Protein_Class_val['GeneName'];
    $gene_ID_taxID_arr[$Protein_Class_val['EntrezGeneID']] = $Protein_Class_val['TaxID'];
  }
  if($gene_ID_str) $gene_ID_str = "'".$gene_ID_str."'";  
  $HITS_DB_obj_arr = array();              
  foreach($HITS_DB as $DB_key => $DB_name_val){
    $HITS_DB_obj_arr[$DB_key] = new mysqlDB($DB_name_val);
  } 
  $report_arr = array();
  $table_name_arr = array(array('Hits','Pep_num','Pep_num_uniqe'),array('TppProtein','TOTAL_NUMBER_PEPTIDES','UNIQUE_NUMBER_PEPTIDES'));     

  foreach($HITS_DB_obj_arr as $DB_obj_key => $DB_obj){
    if(!array_key_exists($DB_obj_key, $DB_ProjectIDs_arr)) continue;
    $projects_str = $DB_ProjectIDs_arr[$DB_obj_key];
//-------------------------------------------------------- 
    $tmp_projects_arr = explode(',',$projects_str);
    foreach($tmp_projects_arr as $tmp_project){
      if($is_frequency_base_on_sample){
        $project_isGel_arr[$tmp_project] = 0;
      }else{
        $SQL = "SELECT `GelFree` 
                FROM `Bait` 
                WHERE `GelFree`='0'
                AND ProjectID='".$tmp_project."'
                LIMIT 1";
        $results = mysqli_query($DB_obj->link, $SQL);
        if($row = mysqli_fetch_assoc($results)){
          $project_isGel_arr[$tmp_project] = 1;
        }else{
          $project_isGel_arr[$tmp_project] = 0;
        }
      }  
    }
//--------------------------------------------------------- 
    foreach($table_name_arr as $table_property){
      $Hits_table = $table_property[0];
      $Pep_num = $table_property[1];
      $Pep_num_uniqe = $table_property[2];
      $SQL = "SELECT H.ID,
                     H.BaitID,
                     H.BandID,
                     H.GeneID,
                     H.$Pep_num AS Pep_num,
                     H.$Pep_num_uniqe as Pep_num_uniqe,
                     H.SearchEngine,
                     B.ProjectID,
                     B.GeneName,
                     B.GeneID AS BaitGeneID,
                     S.Location
              FROM $Hits_table H
              LEFT JOIN Bait B ON (B.ID=H.BaitID)
              LEFT JOIN Band S ON (S.ID=H.BandID)
              WHERE H.GeneID IN ($gene_ID_str)
              AND B.ProjectID IN ($projects_str)";
      $results = mysqli_query($DB_obj->link, $SQL);
      while($row = mysqli_fetch_assoc($results)){
        if($Hits_table == 'TppProtein'){
          $row['is_Tpp'] = "TPP";
        }else{
          if($row['SearchEngine']){
            $row['is_Tpp'] = str_replace("Uploaded", "", $row['SearchEngine']);
          }else{
            $row['is_Tpp'] = '';
          }  
        }
        $row['db_name'] = $DB_obj->selected_db_name;
        if(!array_key_exists($row['GeneID'], $report_arr)){
          $report_arr[$row['GeneID']] = array();
        }
        array_push($report_arr[$row['GeneID']], $row);        
        $fre_base_index = $row['ProjectID'].'_'.$row['is_Tpp'];
        if(!array_key_exists($fre_base_index, $frequency_base_arr)){
          $fre_data = get_base_num_for_frequency($DB_obj->link,$row['ProjectID'],$Hits_table,$row['is_Tpp']);
          $frequency_base_arr[$fre_base_index] = $fre_data;
        }
      }
    }
  }     
  foreach($report_arr as $report_key => $report_val){
    usort($report_arr[$report_key],"cmp_Pep_num");
  }
  uasort($report_arr, "cmp_Pep_num_project");  
  
  $filedNameStr = "Hit Gene Name (Gene ID),Project,Bait Name (Bait ID),Sameple Name (Sample ID),Search Engine,Frequency,Max. Peptide,Unique Peptide";
  fwrite($handle_write, $filedNameStr."\r\n");
  $display_counter = 0; 
  foreach($report_arr as $key => $report_val){
    $hits_arr_tmp = $report_val;
    $hits_arr = array();
    foreach($hits_arr_tmp as $hits_val_tmp){
      if(!array_key_exists($hits_val_tmp['ProjectID'], $hits_arr)){
        $hits_arr[$hits_val_tmp['ProjectID']] = array();
      }
      array_push($hits_arr[$hits_val_tmp['ProjectID']], $hits_val_tmp);
    }
    uasort($hits_arr, "cmp_Pep_num_project");
    write_one_hit_gene($key,$hits_arr);
  } 
}

function cmp_gene_name($a, $b){
  global $gene_ID_name_arr;
  if($gene_ID_name_arr[$a] > $gene_ID_name_arr[$b]){
    return 1;
  }else{
    return -1;
  }
}

function cmp_Pep_num($a, $b){
  if($a["Pep_num"] < $b["Pep_num"]){
    return 1;
  }else{
    return -1;
  }
}

function cmp_Pep_num_project($a, $b){
  if($a[0]["Pep_num"] < $b[0]["Pep_num"]){
    return 1;
  }else{
    return -1;
  }
}

function cmp_hits_property($a, $b){
  global $hits_index;
  global $is_desc;
  if($a[$hits_index] < $b[$hits_index]){
    if($is_desc){
      return 1;
    }else{
      return -1;
    }  
  }else{
    if($is_desc){
      return -1;
    }else{
      return 1;
    } 
  }
}

function print_one_hit_gene($key,$hits_arr_group,$closed=0){
  global $handle_write;
  global $handle_write_s;
  global $display_counter;
  global $bgcolor;
  global $TB_CELL_COLOR;
  global $Projects_ID_DB_arr;
  global $order_by;
  global $hits_index;
  global $is_desc;
  global $title_lable;
  
  $project_id_flag = '';   
  $project_ids_arr =array();
  
  foreach($hits_arr_group as $project_name => $hits_val_group){
    $hits_arr = $hits_val_group;
    $name_index_arr = array('Bait_ID'=>2,'Max_Peptide'=>6,'Unique_Peptide'=>7,'Search_Engine'=>4);
    if($order_by){
      $tmp_arr = explode(".", $order_by);
      if(count($tmp_arr) == 2){
        $is_desc = 1;
      }else{
        $is_desc = 0;
      }
      $hits_index = $name_index_arr[$tmp_arr[0]];
      usort($hits_arr,"cmp_hits_property");
    }  
    
    $hits_counter = count($hits_arr);
    $display_counter++;
    if($display_counter%2){
      $bgcolor = "#e4e4f1";
    }else{
      $bgcolor = $TB_CELL_COLOR;
    }
    $project_id = 0;
    if(preg_match('/\((\d+)\)/', $project_name, $matches)){
      $project_id = $matches[1];
    }
    if(!$project_id){
      echo "\$project_id=$project_id<br>";
      exit;
    }
    
    $searchE_frequencys_arr = array();      
    foreach($hits_arr as $hits_val){
      if($closed){
        if(!in_array($project_name, $project_ids_arr)){
          array_push($project_ids_arr, $project_name);
        }else{
          continue;
        }
        $hits_counter = 1;
      }
      if($hits_val[4] == 'TPP'){
        $hitType = 'TPP';
      }else{
        $hitType = 'normal';
      }
      $Band_ID = 0;
      if(preg_match('/\((\d+)\)/', $hits_val[3], $matches)){
        $Band_ID = $matches[1];
      }
    ?>
    <tr bgcolor='<?php echo $bgcolor;?>' height='20'>
    <?php if($project_id_flag != $project_name){
         $project_id_flag = $project_name;             
    ?>
        <td width="250" bgcolor='' rowspan="<?php echo $hits_counter?>" align="left" valign="top" >
          <div class=maintext>&nbsp;
            <?php echo $project_name?>
          </div>
        </td>
    <?php }?> 
      <td width="150" align="left" valign="top">
        <div class=maintext>&nbsp;
          <?php echo $hits_val[2]?>
        </div>
      </td>
      <td width="270" align="left" valign="top">
        <div class=maintext>&nbsp;
          <?php echo $hits_val[3]?>
        </div>
      </td>
      <td width="150" align="left" valign="top">
        <div class=maintext>&nbsp;
          <?php echo $hits_val[4]?>
        </div>
      </td>
      <td width="100" align="left">
        <div class=maintext>&nbsp;
          <?php 
          if(!in_array($hits_val[4], $searchE_frequencys_arr)){
            array_push($searchE_frequencys_arr, $hits_val[4]);
            echo $hits_val[5];
          }else{
            echo "&nbsp;";
          }          
          ?>
        </div>
      </td>
      <td width="60" align="left">
        <div class=maintext>&nbsp;
          <?php echo $hits_val[6];?>     
        </div>
      </td>
      <td width="60" align="left">
        <div class=maintext>&nbsp;
          <?php echo $hits_val[7];?>     
        </div>
      </td>
      <td width="60" align="left">
        <div class=maintext>&nbsp;
        <a  title="sample report" href="./item_report.php?type=Sample&item_ID=<?php echo $Band_ID?>&noteTypeID_str=&hitType=<?php echo $hitType;?>&DB_name=<?php echo $Projects_ID_DB_arr[$project_id]?>&this_projectID=<?php echo $project_id?>&title_lable=<?php echo $title_lable?>" style='text-decoration:none'>
        <img src="./images/icon_report.gif" border=0 alt="Sample Report"></a>   
        </div>
      </td>
    </tr> 
    <?php 
      $line = $key.','.$project_name.','.$hits_val[2].','.$hits_val[3].','.$hits_val[4].','.$hits_val[5].','.$hits_val[6].','.$hits_val[7];
      if(!$closed){        
        fwrite($handle_write, $line."\r\n");
      }  
    }
  }
}

function write_one_hit_gene($key,$hits_arr_group,$closed=0){
  global $handle_write;
  global $gene_ID_name_arr;
  global $Projects_ID_name_arr;
  global $projects_frequency_arr;
  global $frequency_base_arr;
  global $project_isGel_arr;
  
  $project_id_flag = 0; 
  $project_ids_arr =array();
 
  foreach($hits_arr_group as $project_id => $hits_val_group){  
    $hits_arr = $hits_val_group;
    $hits_counter = count($hits_arr);
    $project_name = $Projects_ID_name_arr[$project_id];

    $frequency_catched_arr = array();
    $is_Gel = $project_isGel_arr[$project_id];
    if($is_Gel){
      $tmp_ID = 'BaitGeneID';
    }else{
      $tmp_ID = 'BandID';
    }
    foreach($hits_arr as $hits_val){
      if(!$hits_val[$tmp_ID]) continue;
      $tmp_index = $hits_val['is_Tpp'];
      if(!array_key_exists($tmp_index, $frequency_catched_arr)){
        $frequency_catched_arr[$tmp_index] = array();
        array_push($frequency_catched_arr[$tmp_index], $hits_val[$tmp_ID]);
      }else{
        if(!in_array($hits_val[$tmp_ID], $frequency_catched_arr[$tmp_index])){
          array_push($frequency_catched_arr[$tmp_index], $hits_val[$tmp_ID]);
        }  
      }
    }   
    foreach($hits_arr as $hits_val){
      if(!$hits_val[$tmp_ID]) continue;
      if($closed){
        if(!in_array($project_id, $project_ids_arr)){
          array_push($project_ids_arr, $project_id);
        }else{
          continue;
        }
        $hits_counter = 1;
      }
      $frequency_index = $project_id.'_'.$hits_val['is_Tpp'];
      if(array_key_exists($frequency_index, $frequency_base_arr)){
        $frequency_base = $frequency_base_arr[$frequency_index];
        $frequency_catched = count($frequency_catched_arr[$hits_val['is_Tpp']]);
        if($frequency_base){
          $frequency_display_str = round(($frequency_catched/$frequency_base)*100, 2).'% ('.$frequency_catched.' / '.$frequency_base.')';
        }else{
          $frequency_display_str = '--%('.$frequency_catched.' / '.$frequency_base.')';
        }
      }else{
        $frequency_display_str = '--%';
      }
      if($hits_val['is_Tpp'] == 'TPP'){
        $is_Tpp = $hitType = 'TPP';
      }else{
        $hitType = 'normal';
        $is_Tpp = $hits_val['is_Tpp'];
      }
      $Bait_lable =  $hits_val['GeneName']." (".$hits_val['BaitID'].")";
      //$Bait_lable =  $hits_val['GeneName']." (".$hits_val['BaitID'].")(".$hits_val['BaitGeneID'].")";
      $Sample_lable = $hits_val['Location']." (".$hits_val['BandID'].")";
      $line = $gene_ID_name_arr[$key].' ('.$key.'),'.$project_name." (".$project_id.'),'.$Bait_lable.','.$Sample_lable.','.$is_Tpp.','.$frequency_display_str.','.$hits_val['Pep_num'].','.$hits_val['Pep_num_uniqe'];    
      fwrite($handle_write, $line."\r\n");
    }
  }
}

function get_base_num_for_frequency($DB_obj_link,$projectID,$tableName,$SearchEngine){
  global $project_isGel_arr;
  if($SearchEngine != 'TPP'){
    if($SearchEngine){
      $WHRER = " WHERE SearchEngine='".$SearchEngine."' OR SearchEngine='".$SearchEngine."Uploaded' ";
    }else{
      $WHRER = " WHERE SearchEngine='' OR SearchEngine IS NULL ";
    }
  }else{
    $WHRER = '';
  }
  $is_Gel = $project_isGel_arr[$projectID];
  if($is_Gel){
    $tmp_item_ID = 'BaitID';
    $item_table_name = 'Bait';
    $counted_ID = 'GeneID';
    $sub_where = " AND GeneID != '0' AND GeneID IS NOT NULL AND GeneID!='' ";
  }else{
    $tmp_item_ID = 'BandID';
    $item_table_name = 'Band';
    $counted_ID = 'ID';
    $sub_where = '';
  }  
  $SQL = "SELECT $tmp_item_ID 
          FROM $tableName 
          $WHRER
          GROUP BY $tmp_item_ID";
  if($results = mysqli_query($DB_obj_link, $SQL)){
    $item_ID_str = '';
    while($row = mysqli_fetch_assoc($results)){
      if($item_ID_str) $item_ID_str .= ',';
      $item_ID_str .= $row[$tmp_item_ID];
    }
    if($item_ID_str){
      $SQL = "SELECT COUNT(DISTINCT($counted_ID)) as value
              FROM $item_table_name 
              WHERE `ProjectID`='$projectID'
              $sub_where 
              AND ID IN($item_ID_str)";
      if($results = mysqli_query($DB_obj_link, $SQL)){
        if($row = mysqli_fetch_assoc($results)){
          $total_number = $row['value'];
          return $total_number;
        }
      }  
    }
  }
  return '';
}
?>