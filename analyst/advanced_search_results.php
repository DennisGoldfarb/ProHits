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
$theaction = '';
$frm_search_str = '';
$frm_search_OrAnd = '';
$frm_addwildcard = '';
$frm_search_description = 0;
$frm_date_str = '';
$frm_expDetail_str ='';
$pro_access_ID_str = '';

$limit = 3000;
$bait_search = true;
$hit_search = true;
$band_search = true;
$gel_search = true;
$rawfile_search = true;
$task_search = true;

$bait_selected_bait_arr = array();
$hit_selected_bait_arr = array();
$TPP_hit_selected_bait_arr = array();
$hit_selected_band_arr = array();
$TPP_hit_selected_band_arr = array();

$band_selected_band_arr = array();
$gel_selected_gel_arr = array();
$raw_file_searched_arr = array();
$raw_file_searched_str = '';
$task_searched_arr = array();
$task_searched_str = '';

$expDetail_bait_arr = array();
$expDetail_Exp_arr = array();
$expDetail_Exp_str = '';

$theaction = '';
$like_str = '';
$GeneID_str = '';
$s_wildcard = '';
$e_wildcard = '';
$geneID_str = '';
$SQL_bait = '';
$date_from = '';
$date_to = '';
$msg_h = '';
$exp_option_mld = '';
$exp_option_num = 0;
$SQL_exp = '';
$SQL_exp_group = '';
$add_process_img = 1;
$frm_user_id = '';
$where_UserID_bait = '';
$where_UserID_hits = '';
$where_UserID_hits_TPP = '';
$where_UserID_gel = '';
$where_UserID_exp = '';
$where_UserID_task = '';
$where_UserID_band = '';

$used_group_arr = array();
$frm_search_hit_gene = '';

require("../common/site_permission.inc.php");
include("analyst/common_functions.inc.php");
require("common/common_fun.inc.php");

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);
$managerDB = new mysqlDB(MANAGER_DB);

require("site_header.php");

switch($frm_addwildcard){
  case 'both':
    $s_wildcard = '%';
    $e_wildcard = '%';
    break;
  case 'front':
    $s_wildcard = '%';
    break;
  case 'end':
    $e_wildcard = '%';
    break;
}
if($frm_date_str){
  $date_arr = explode(' To ', $frm_date_str);
  $date_from = $date_arr[0] . "-00";
  $date_to = $date_arr[1] . "-32";
}

if($frm_user_id){
  $where_UserID_bait = $where_UserID_band = " AND OwnerID='$frm_user_id' ";
  $where_UserID_hits = " AND H.OwnerID='$frm_user_id' ";
  $where_UserID_hits_TPP = " AND B.OwnerID='$frm_user_id' ";
  $where_UserID_gel = " AND G.OwnerID='$frm_user_id' ";
  $where_UserID_exp = " AND E.OwnerID='$frm_user_id' ";
  $where_UserID_task = " AND UserID='$frm_user_id' ";
} 

if($frm_expDetail_str){
  //$SQL_exp and $SQL_exp_group will be used in get_exp_bait_arr()
  preg_match_all("/[0-9]+[_]([0-9]+),,/", $frm_expDetail_str, $matches);
  if($matches){
    // $matches[1] is the selected option array.
    $exp_option_mld = implode(",",  $matches[1]);
    $exp_option_num = count($matches[1]);
    $tmp_arr = explode("====", $frm_expDetail_str);
    $and_or = '';
    if(count($tmp_arr)>1){
      $frm_expDetail_str = $tmp_arr[0];
      $and_or = $tmp_arr[1];
    }
    $SQL_exp = "SELECT E.BaitID, D.ExpID FROM ExpDetail D, Experiment E WHERE D.ExpID=E.ID AND OptionID in ($exp_option_mld)";
    if($and_or == 'AND' ){
      $SQL_exp_group = " GROUP by D.ExpID HAVING count(D.ExpID)=$exp_option_num";
    }
  }
  $expDetail_Exp_arr = get_exp_bait_arr("Band");
  if($expDetail_Exp_arr){
    $expDetail_Exp_str = implode(",", $expDetail_Exp_arr);
  }
}

//bait search---------------------------------------------------------
if($frm_search_str){
  $frm_search_str = trim($frm_search_str);
  $frm_search_str = preg_replace("/[ ]+/", " ", $frm_search_str);
  $frm_search_str = mysqli_escape_string($HITSDB->link, $frm_search_str);
  $like_str = '';
  
  $bait_selected_bait_str = '';
  $bait_selected_bait_num = 0;
   
  if($frm_search_description){
    $field_arr = array("GeneID","LocusTag","GeneName","BaitAcc","Tag","Mutation","Clone","Vector","Description");
  }else{
    $field_arr = array("GeneID","LocusTag","GeneName","BaitAcc","Tag","Mutation","Clone","Vector");
  }
  $like_str = get_like_str($field_arr,$frm_search_str);

  if($like_str){
    if($frm_group_id_list){
      $bait_id_str = get_group_item_id_in_str($frm_group_id_list,'Bait');
    }else{
      $bait_id_str = '';
    }  
    $SQL = "SELECT ID FROM Bait WHERE ProjectID='$AccessProjectID' $where_UserID_bait $bait_id_str AND (" . $like_str .")";
    $SQL .= get_and_date_str('DateTime');
    $bait_arr = $HITSDB->fetchAll($SQL);
    if(!$bait_arr){
      $bait_search = false;
    }else{
      foreach($bait_arr as $value){
        array_push($bait_selected_bait_arr, $value['ID']);
      }
    }
  }
}

//hits search---------------------------------------------------------
$hit_value_for_bait_arr = array();
$hit_value_for_band_arr = array();
$tpp_hit_value_for_bait_arr = array();
$tpp_hit_value_for_band_arr = array();
if($frm_search_str){
  $hit_selected_bait_str = '';
  $hits_selected_bait_num = 0;
  
  //$where_UserID_hits_TPP
  $group_ids_for_hits = '';
  if($frm_group_id_list){
    $group_ids_for_hits = get_group_recordes_str_for_hits($frm_group_id_list);
  }
  if($frm_search_description){
    //search hits description
    if($frm_search_OrAnd){
      $like_str = get_like_str(array("HitName"),$frm_search_str);
      
      $SQL = "SELECT B.ID, H.BandID, H.Expect, H.Expect2, H.Pep_num from Hits H, Bait B 
      WHERE B.ID=H.BaitID and B.ProjectID='$AccessProjectID' $where_UserID_hits $group_ids_for_hits and (" . $like_str .") ";
      if($frm_date_str){
        $SQL .= get_and_date_str('H.DateTime');
      }
      $SQL .= " GROUP BY H.BandID ORDER BY B.ID, H.Pep_num desc";
      $TPP_like_str = get_like_str(array("ProteinDec"),$frm_search_str);
      
      $TPP_SQL = "SELECT B.ID, H.BandID, H.PROBABILITY, H.TOTAL_NUMBER_PEPTIDES from TppProtein H, Bait B 
      WHERE B.ID=H.BaitID and B.ProjectID='$AccessProjectID' $where_UserID_hits_TPP $group_ids_for_hits and (" . $TPP_like_str .") ";
      if($frm_date_str){
       $TPP_SQL .= get_and_date_str('B.DateTime');
      }
      $TPP_SQL .= " Order by B.ID, H.TOTAL_NUMBER_PEPTIDES desc";
    }else{    
      $SQL = "SELECT B.ID, H.BandID, H.Expect, H.Expect2, H.Pep_num from Hits H, Bait B 
      WHERE B.ID=H.BaitID and B.ProjectID='$AccessProjectID' $where_UserID_hits $group_ids_for_hits and H.HitName like '$frm_search_str' ";
      if($frm_date_str){
        $SQL .= get_and_date_str('H.DateTime');
      }
      $SQL .= " ORDER BY B.ID, H.Pep_num desc";
      $TPP_SQL = "SELECT B.ID, H.BandID, H.PROBABILITY, H.TOTAL_NUMBER_PEPTIDES from TppProtein H, Bait B 
      WHERE B.ID=H.BaitID and B.ProjectID='$AccessProjectID' $where_UserID_hits_TPP $group_ids_for_hits and H.ProteinDec like '$frm_search_str' ";
      if($frm_date_str){
       $TPP_SQL .= get_and_date_str('B.DateTime');
      }
      $TPP_SQL .= " Order by B.ID, H.TOTAL_NUMBER_PEPTIDES desc";
    }
    $results = mysqli_query($HITSDB->link, $SQL);
    while($row = mysqli_fetch_row($results)){
      array_push($hit_selected_bait_arr, $row[0]);
      array_push($hit_selected_band_arr, $row[1]);
      if(!isset($hit_value_for_bait_arr[$row[0]])){
        $tmp_expect = ($row[2])?$row[2]:$row[3];
        $hit_value_for_bait_arr[$row[0]]= $tmp_expect ." / ". $row[4];
      }
      if(!isset($hit_value_for_band_arr[$row[1]])){
        $hit_value_for_band_arr[$row[1]]= $tmp_expect ." / ". $row[4];
      }
    }
    $TPP_results = mysqli_query($HITSDB->link, $TPP_SQL);
    while($row = mysqli_fetch_row($TPP_results)){
      array_push($TPP_hit_selected_bait_arr, $row[0]);
      array_push($TPP_hit_selected_band_arr, $row[1]);
      if(!isset($tpp_hit_value_for_bait_arr[$row[0]])){
        $tpp_hit_value_for_bait_arr[$row[0]]= $row[2] ." / ". $row[3];
      }
      if(!isset($tpp_hit_value_for_band_arr[$row[1]])) {
        $tpp_hit_value_for_band_arr[$row[1]]= $row[2] ." / ". $row[3];
      }
    }
  }
   
  //not (exact phrase/all words and with two words)
  if($frm_search_OrAnd == 'OR' or !strpos($frm_search_str, ' ')){
    $geneID_str = '';
    $geneID_str_ens = '';
    $selected_bait_gene_str = '';
    //search all gene IDs
    $like_str = get_like_str(array("GeneName"),$frm_search_str);
    $SQL_ens = "SELECT ENSG FROM `Protein_ClassENS` WHERE $like_str";
    $results = mysqli_query($proteinDB->link, $SQL_ens);
    if(mysqli_num_rows($results) > $limit){
       $like_str_tmp = get_like_str(array("GeneName"),$frm_search_str, '=');
       $SQL_ens = "SELECT ENSG FROM `Protein_ClassENS` WHERE $like_str_tmp";
       $results = mysqli_query($proteinDB->link, $SQL_ens);
       //$msg_h = "More than $limit gene names are matched the search. The exact phrase search is used.";
    }
    while($row = mysqli_fetch_row($results)){
      $selected_bait_gene_str .= ",'".$row[0]."'";
    }
    $SQL_g = "SELECT EntrezGeneID FROM `Protein_Class` WHERE $like_str";
    //echo $SQL_g;
    $results = mysqli_query($proteinDB->link, $SQL_g);
    if(mysqli_num_rows($results) > $limit){
      $like_str_tmp = get_like_str(array("GeneName"),$frm_search_str, '=');
      $SQL_g = "SELECT EntrezGeneID FROM `Protein_Class` WHERE $like_str_tmp";
      $results = mysqli_query($proteinDB->link, $SQL_g);
      $msg_h = "<br>More than $limit gene names are matched. \"The exact phrase\" gene name search is used for hits search.";
    }
    while($row = mysqli_fetch_row($results)){
      $selected_bait_gene_str .= ",'".$row[0]."'";
    }
    //echo "H2: $SQL_g<br>\n";
    //from seached GeneIDs get bait IDs from hits results
    if($selected_bait_gene_str){
      $selected_bait_gene_str = substr($selected_bait_gene_str, 1);
      $SQL = "SELECT B.ID, H.BandID, H.Expect, H.Expect2, H.Pep_num, H.SearchEngine from Hits H, Bait B 
      WHERE B.ID=H.BaitID and B.ProjectID='$AccessProjectID' $where_UserID_hits $group_ids_for_hits and H.GeneID in($selected_bait_gene_str) ";
      if($frm_date_str) {
        $SQL .= get_and_date_str('H.DateTime');
      }
      $SQL .= " ORDER BY B.ID, H.Pep_num desc";
      $TPP_SQL = "SELECT B.ID, H.BandID, H.PROBABILITY, H.TOTAL_NUMBER_PEPTIDES, H.SearchEngine from TppProtein H, Bait B 
      WHERE B.ID=H.BaitID and B.ProjectID='$AccessProjectID' $where_UserID_hits_TPP $group_ids_for_hits and H.GeneID in($selected_bait_gene_str) ";
      if($frm_date_str) {
        $TPP_SQL .= get_and_date_str('B.DateTime');
      }
      $TPP_SQL .= " Order by B.ID, H.TOTAL_NUMBER_PEPTIDES desc";
      
      //echo "H3: $SQL<br>\n";
      //echo "H4: $TPP_SQL<br>\n";exit;
      $results = mysqli_query($HITSDB->link, $SQL);
      while($row = mysqli_fetch_row($results)){
        array_push($hit_selected_bait_arr, $row[0]);
        array_push($hit_selected_band_arr, $row[1]);
        if(!isset($hit_value_for_bait_arr[$row[0]])){
          $tmp_expect = ($row[2])?$row[2]:$row[3];
          $hit_value_for_bait_arr[$row[0]]= $row[5] ." " . $tmp_expect ." / ". $row[4];
        }
        if(!isset($hit_value_for_band_arr[$row[1]])){
          $hit_value_for_band_arr[$row[1]]= $row[5]." " . $tmp_expect ." / ". $row[4];
        }
      }
      $TPP_results = mysqli_query($HITSDB->link, $TPP_SQL);
      while($row = mysqli_fetch_row($TPP_results)){
        array_push($TPP_hit_selected_bait_arr, $row[0]);
        array_push($TPP_hit_selected_band_arr, $row[1]);
        if(!isset($tpp_hit_value_for_bait_arr[$row[0]])){
          $tpp_hit_value_for_bait_arr[$row[0]]= $row[4] ."TPP " . $row[2] ." / ". $row[3];
        }
        if(!isset($tpp_hit_value_for_band_arr[$row[1]])){
          $tpp_hit_value_for_band_arr[$row[1]]= $row[4] ."TPP " . $row[2] ." / ". $row[3];
        }
      }
    }
  }
  $hit_selected_bait_arr = array_unique($hit_selected_bait_arr);
  $hit_selected_band_arr = array_unique($hit_selected_band_arr);
  $TPP_hit_selected_bait_arr = array_unique($TPP_hit_selected_bait_arr);
  $TPP_hit_selected_band_arr = array_unique($TPP_hit_selected_band_arr);
    
  if(!$hit_selected_bait_arr){ 
    $hit_search = false;
  }
}
//add ExpDetail search in bait and hit search-----------------------
if($SQL_exp){
  if($frm_search_str){
    if($bait_selected_bait_arr){
      $bait_selected_bait_arr = get_exp_bait_arr("Bait", $bait_selected_bait_arr);
    }
    if($hit_selected_bait_arr){
       
       $hit_selected_bait_arr = get_exp_bait_arr("Hit", $hit_selected_bait_arr);
       $hit_selected_band_arr = get_exp_band_arr("Hit", $hit_selected_band_arr);

    }
    if($TPP_hit_selected_bait_arr){
       $TPP_hit_selected_bait_arr = array_unique($TPP_hit_selected_bait_arr);
       $TPP_hit_selected_bait_arr = get_exp_bait_arr("Hit", $TPP_hit_selected_bait_arr);
       $TPP_hit_selected_band_arr = get_exp_band_arr("Hit", $TPP_hit_selected_band_arr);
       
    }
  }else{
    $expDetail_bait_arr = get_exp_bait_arr("Bait");
    $bait_selected_bait_arr = $expDetail_bait_arr;
    $hit_selected_bait_arr = get_hit_bait_arr($expDetail_bait_arr);
    $TPP_hit_selected_bait_arr = get_hit_bait_arr($expDetail_bait_arr, 'TPP');
    
    $hit_selected_band_arr = get_exp_band_arr('Hit');
    $TPP_hit_selected_band_arr = get_exp_band_arr('TPP');
  }
}

//search sample ----------------------------------------------------
if($frm_group_id_list){
  $band_id_str = get_group_item_id_in_str($frm_group_id_list,'Band');
}else{
  $band_id_str = '';
}  

$SQL = "SELECT `ID` FROM `Band` WHERE `ProjectID`='$AccessProjectID' $where_UserID_band $band_id_str ";
if($frm_search_str){
  $like_str = get_like_str(array("Location"),$frm_search_str);
  $SQL .= " AND (". $like_str .")";
}
if($SQL_exp and $SQL_exp_group){
  if($expDetail_Exp_str){
    $SQL .= " AND ExpID in ($expDetail_Exp_str)";
  }else{
     $band_search = false;
  }
}
if($date_from and $date_to){
  $SQL .= " AND (DateTime > '".$date_from."' AND DateTime < '".$date_to."')";
}
//echo "Band: $SQL<br>\n";
if($band_search){
  $results = mysqli_query($HITSDB->link, $SQL);
  while($row = mysqli_fetch_row($results)){
    array_push($band_selected_band_arr, $row[0]);
  }
}

//search gel ---------------------------------------------------------
$SQL = "SELECT distinct G.ID FROM Gel G, Lane L WHERE G.ID=L.GelID AND G.ProjectID='$AccessProjectID' $where_UserID_gel";
if($frm_search_str){
  $like_str = get_like_str(array("Name", "Image", "LaneCode"),$frm_search_str);
  $SQL .= " AND (". $like_str .")";
}
if($SQL_exp and $SQL_exp_group){
  if($expDetail_Exp_str){
    $SQL .= " AND ExpID in ($expDetail_Exp_str)";
  }else{
     $gel_search = false;
  }
}
if($date_from and $date_to){
  $SQL .= " AND (G.DateTime > '".$date_from."' AND G.DateTime < '".$date_to."')";
}
//echo "Gel: $SQL<br>\n";
if($gel_search){
  $results = mysqli_query($HITSDB->link, $SQL);
  while($row = mysqli_fetch_row($results)){
    array_push($gel_selected_gel_arr, $row[0]);
  }
}

$raw_file_num = 0;
$task_num = 0;
$msTable_arr = array();
if($frm_search_str and !$SQL_exp){
  //get access id string
  if($USER->Type == 'Admin'){
    $sql_p = "SELECT P.ID, P.Name FROM Projects P order by P.ID"; 
  }else{
    $sql_p = "SELECT P.ID, P.Name FROM Projects P, ProPermission M where P.ID=M.ProjectID and M.UserID=$USER->ID order by P.ID"; 
  }
  $results = mysqli_query($PROHITSDB->link, $sql_p);
  while($row = mysqli_fetch_row($results)){
    if($pro_access_ID_str) $pro_access_ID_str .= ",";
    $pro_access_ID_str .= $row[0];
  }
  //get all ms tables
  $SQL = "SHOW TABLES";
  $results = mysqli_query($managerDB->link, $SQL);
  while($row = mysqli_fetch_row($results)){
    array_push($msTable_arr, $row[0]);
  }
  //search raw file ---------------------------------------------
  $tableName = '';
  $SQL_from = '';
  $SQL_where = ' WHERE';
  $like_str = get_like_str(array("FileName"),$frm_search_str);
  $SQL_where .= " (". $like_str .")";
  $SQL_where .= get_and_date_str('Date');
  $SQL_where .= " AND ProjectID in($pro_access_ID_str)";
  foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
    if(!in_array($baseTable, $msTable_arr)) continue;
    $raw_file_searched_arr[$baseTable] = array();
    $SQL = "SELECT ID From `".$baseTable."`". $SQL_where;
    $results = mysqli_query($managerDB->link, $SQL);
    while($row = mysqli_fetch_row($results)){
      array_push($raw_file_searched_arr[$baseTable], $row[0]);
      $raw_file_num++;
      
    }
    //echo "RawFile : $SQL<br>\n";
    //print_r($raw_file_searched_arr[$baseTable]);
  }
  if(!$raw_file_num) $rawfile_search = false;
  
  //Search Tasks --------------------------------------------------
  //$task_search = true;
  $like_str = get_like_str(array("TaskName"),$frm_search_str);
  foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
    
    $task_searched_arr[$baseTable] = array();
    $tableSearchTasks = $baseTable."SearchTasks";
    if(!in_array($tableSearchTasks, $msTable_arr)) continue;
    $SQL = "SELECT ID from $tableSearchTasks where "; 
    $SQL .= " (". $like_str .")";
    $SQL .= get_and_date_str('StartTime');
    $SQL .= " AND ProjectID in($pro_access_ID_str) $where_UserID_task";    
    $results = mysqli_query($managerDB->link, $SQL);
    while($row = mysqli_fetch_row($results)){
      if($row[0] > 0){
        array_push( $task_searched_arr[$baseTable], $row[0]);
        $raw_file_num++;
      }
    }
    //echo "Task : $SQL<br>\n";
    //print_r($task_searched_arr[$baseTable]);
  }
  
}else{
  $rawfile_search = false;
  $task_search = false;
}

$lable_exp = $frm_expDetail_dis;
$tmp_str = ($lable_exp)?"<br>".$lable_exp:"";
$page_lable = urlencode("\"$frm_search_str\"". $tmp_str );
//---display search results --------------------------------------
?>
<STYLE type="text/css">  
td { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
.st1 {
  display: block;
  border: black solid 1px;
  width:552;
  color: black;
  background-color: white;
}
</STYLE>
<script type="text/javascript"> 
function browse_for_detail(theURL, theTitle, id_str, id_vl_str, theTable){
  var theForm = document.getElementById('searched_form');
  theForm.title_lable.value = theTitle + theForm.title_lable.value;
  theForm.table.value=theTable;
  theForm.searched_id_str.value=document.getElementById(id_str).value;
  if(theURL == 'band_show.php'){
    theForm.frm_Band_groups.value = 'Band';
  }  
  if(id_vl_str){
    theForm.searched_id_vl_str.value=document.getElementById(id_vl_str).value;
  }
  theForm.action = theURL;
  theForm.submit();
}
</script>
<form id="searched_form" name="searched_form" method='post' action="">
<input type=hidden name='searched_id_str' id='searched_id_str' value=''>
<input type=hidden name='searched_id_vl_str' id='searched_id_vl_str' value=''>
<input type=hidden name='theaction' id='theaction' value='search'>
<input type=hidden name='table' id='table' value=''>
<input type=hidden name='title_lable' id='title_lable' value='<?php echo $page_lable;?>'>
<input type=hidden name='frm_Band_groups' id='frm_Band_groups' value=''>

<table border="0" cellpadding="0" cellspacing="0" width="95%"> 
  <tr>
    <td align="left"><br>
		&nbsp; <font color="navy" face="helvetica,arial,futura" size="5"><b>Search Results
    <?php 
      if($AccessProjectName){
        echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project: $AccessProjectName)</font>";
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
    <td align="center" colspan=2 bgcolor=''><br>
    
<?php  
$lable_wildcard = 'No';
$lable_OrAnd = 'the exact phrase';
$label_des = ($frm_search_description)?'Yes':'No';
//$lable_exp = ($frm_expDetail_str)?$frm_expDetail_str:"&nbsp;";
//$RemoveChars  = array('/@@/', '/[0-9]+_[0-9]+,,/', '/;;/');
//$ReplaceWith = array('<br>', '', ': ');

 
switch($frm_addwildcard){
  case 'both':
    $lable_wildcard = 'front and end';
    break;
  case 'front':
    $lable_wildcard = 'at the front';
    break;
  case 'end':
    $lable_wildcard = 'at the end';
    break;
}
switch($frm_search_OrAnd){
  case 'OR':
    $lable_OrAnd = 'at least one of the words';
    break;
  case 'AND':
    $lable_OrAnd = 'all words';
    break;
}
  ?>  <DIV class="st1">
      <table width=100% border=0 cellspacing="1" cellpadding="2">
        <tr><td colspan=2 bgcolor="#9c9c9c" height=30 align=center>
        <font color="#FFFFFF"><b>Your search results for following criteria:</b></font>
        </td>
        </tr>
        <tr>
        <td width=200  bgcolor="#eeeeee"><b>Word(s) or value(s)</b>:</td><td bgcolor="#eeeeee"><?php echo $frm_search_str;?></td>
        </tr><tr>
        <td  bgcolor="#eeeeee"><b>Add wildcard</b>:</td><td bgcolor="#eeeeee"><?php echo $lable_wildcard;?></td>
        </tr><tr>
        <td  bgcolor="#eeeeee"><b>Find</b>:</td><td bgcolor="#eeeeee"><?php echo $lable_OrAnd;?></td>
        </tr><tr>
        <td  bgcolor="#eeeeee"><b>Include description</b>:</td><td bgcolor="#eeeeee"><?php echo $label_des;?></td>
        </tr>
        <tr>
        <td valign=top bgcolor="#eeeeee"><b>Experiment detail</b>:</td><td bgcolor="#eeeeee"><?php echo $lable_exp;?></td>
        </tr>
        <tr>
        <td bgcolor="#eeeeee"><b>Date</b>:</td><td bgcolor="#eeeeee"><?php echo ($frm_date_str)?$frm_date_str:"&nbsp;";?></td>
        </tr>
      </table>
      </div>
      <?php echo $msg_h;?>
      <br>
      <DIV class="st1">
     <table width=100% border=0 cellspacing="1" cellpadding="2">
      <tr bgcolor="#888888" align=center>
      <td bgcolor="#9c9c9c"><font color="#FFFFFF"><b>Record Type</b></font></td>
      <td bgcolor="#9c9c9c"><font color="#FFFFFF"><b>Match(es)</b></font></td>
      <td bgcolor="#9c9c9c"><font color="#FFFFFF"><b>Browse for Detail</b></font></td>
      </tr>
      <tr> 
        <td width=200 bgcolor="#cccccc"> Bait:</td>
        <td width=200 align=center bgcolor="#eeeeee">
        <?php 
        //---- display bait -----------------
         $num = count($bait_selected_bait_arr);
         echo $num . "";
         ?>
        </td>
        <td align=center  bgcolor="#eeeeee">
        <?php 
         if($num){
          $bait_selected_bait_str = implode(",", $bait_selected_bait_arr);
          echo "<input type=hidden name='bait_ids' id='bait_ids' value='$bait_selected_bait_str'>\n";
          echo "<a class=button href=\"javascript: browse_for_detail('bait.php', 'Search Bait ', 'bait_ids', '', '');\">[Browse]</a>";
        }else{
          echo "&nbsp;";
        }
        ?>
        </td>
      </tr>
      <tr>
        <td bgcolor="#cccccc"> Hit (Report by Bait): </td>
        <td align=center bgcolor="#eeeeee">
        <?php 
         // -- display hits ----------------
         $num = count($hit_selected_bait_arr);
         echo $num . "";
         ?>
        </td>
        <td align=center bgcolor="#eeeeee">
        <?php 
         if($num){
          $hit_selected_bait_str = implode(",", $hit_selected_bait_arr);
          $hit_value_for_bait_str = '';
          foreach($hit_value_for_bait_arr as $key=>$value){
            $hit_value_for_bait_str .= $key . ",".$value . ":";
          }
          echo "<input type=hidden name='hit_ids_v' id='hit_ids_v' value='$hit_value_for_bait_str'>\n";
          echo "<input type=hidden name='hit_ids' id='hit_ids' value='$hit_selected_bait_str'>\n";
          echo "<a class=button href=\"javascript: browse_for_detail('bait.php', 'Search hits ', 'hit_ids', 'hit_ids_v', '');\">[Browse]</a>";
        }else{
          echo "&nbsp;";
        }
        ?>
        </td>
     </tr>
     <tr>
        <td bgcolor="#cccccc"> Hit (Report by Sample): </td>
        <td align=center bgcolor="#eeeeee">
        <?php 
         // -- display hits ----------------
         $num = count($hit_selected_band_arr);
         echo $num . "";
         ?>
        </td>
        <td align=center bgcolor="#eeeeee">
        <?php 
         if($num){
          $hit_selected_band_str = implode(",", $hit_selected_band_arr);
          $hit_value_for_band_str = '';
          foreach($hit_value_for_band_arr as $key=>$value){
            $hit_value_for_band_str .= $key .",".$value . ":";
          }
          echo "<input type=hidden name='hit_Band_ids_v' id='hit_Band_ids_v' value='$hit_value_for_band_str'>\n";
          echo "<input type=hidden name='hit_Band_ids' id='hit_Band_ids' value='$hit_selected_band_str'>\n";
          echo "<a class=button href=\"javascript: browse_for_detail('band_show.php', 'Search hits ', 'hit_Band_ids', 'hit_Band_ids_v','');\">[Browse]</a>";
       }else{
          echo "&nbsp;";
        }
        ?>
        </td>
     </tr>
     <tr>
        <td bgcolor="#cccccc"> TPP Hit (Report by Bait): </td>
        <td align=center bgcolor="#eeeeee">
        <?php 
         // -- display TPP hits ----------------
         $num = count($TPP_hit_selected_bait_arr);
         echo $num . "";
         ?>
        </td>
        <td align=center bgcolor="#eeeeee">
        <?php 
         if($num){
          $TPP_hit_selected_bait_str = implode(",", $TPP_hit_selected_bait_arr);
          $tpp_value_str = '';
          foreach($tpp_hit_value_for_bait_arr as $key=>$value){
            $tpp_value_str .= $key. ",".$value . ":";
          }
          echo "<input type=hidden name='hit_TPP_ids_v' id='hit_TPP_ids_v' value='$tpp_value_str'>\n";
          echo "<input type=hidden name='TPP_ids' id='TPP_ids' value='$TPP_hit_selected_bait_str'>\n";
          echo "<a class=button href=\"javascript: browse_for_detail('bait.php', 'Search TPP hits ', 'TPP_ids', 'hit_TPP_ids_v', 'TPP');\">[Browse]</a>";
        }else{
          echo "&nbsp;";
        }
        ?>
        </td>
     </tr>
     <tr>
        <td bgcolor="#cccccc"> TPP Hit (Report by Sample): </td>
        <td align=center bgcolor="#eeeeee">
        <?php 
         // -- display TPP hits ----------------
         $num = count($TPP_hit_selected_band_arr);
         echo $num . "";
         ?>
        </td>
        <td align=center bgcolor="#eeeeee">
        <?php 
         if($num){
          $TPP_hit_selected_band_str = implode(",", $TPP_hit_selected_band_arr);
          $tpp_value_str = '';
          foreach($tpp_hit_value_for_band_arr as $key=>$value){
            $tpp_value_str .= $key. ",".$value . ":";
          }
          echo "<input type=hidden name='tpp_band_ids_v' id='tpp_band_ids_v' value='$tpp_value_str'>\n";
          echo "<input type=hidden name='tpp_band_ids' id='tpp_band_ids' value='$TPP_hit_selected_band_str'>\n";
          echo "<a class=button href=\"javascript: browse_for_detail('band_show.php', 'Search sample ', 'tpp_band_ids', 'tpp_band_ids_v', 'TPP');\">[Browse]</a>";
      }else{
          echo "&nbsp;";
        }
        ?>
        </td>
     </tr>
     <tr>
        <td bgcolor="#cccccc"> Sample: </td>
        <td align=center bgcolor="#eeeeee">
        <?php 
         $num = count($band_selected_band_arr);
         echo $num . "";
         ?>
        </td>
        <td align=center bgcolor="#eeeeee">
        <?php 
         if($num){
          $band_selected_band_str = implode(",", $band_selected_band_arr);
          echo "<input type=hidden name='band_ids' id='band_ids' value='$band_selected_band_str'>\n";
          echo "<a class=button href=\"javascript: browse_for_detail('band_show.php', 'Search sample ', 'band_ids', '','');\">[Browse]</a>";
       }else{
          echo "&nbsp;";
        }
        ?>
        </td>
     </tr>
     <?php 
     // --- display gel----------------------
     if($gel_selected_gel_arr){
     ?>
     <tr>
        <td bgcolor="#cccccc"> Gel: </td>
        <td align=center bgcolor="#eeeeee">
        <?php 
         
         $num = count($gel_selected_gel_arr);
         echo $num . "";
         ?>
        </td>
        <td align=center bgcolor="#eeeeee">
        <?php 
         if($num){
          $gel_selected_gel_str = implode(",", $gel_selected_gel_arr);
          echo "<input type=hidden name='gel_ids' id='gel_ids' value='$gel_selected_gel_str'>\n";
          echo "<a class=button href=\"javascript: browse_for_detail('gel.php', 'Search gel ', 'gel_ids', '','');\">[Browse]</a>";
       }else{
          echo "&nbsp;";
        }
        ?>
        </td>
     </tr>
     <?php }?> 
     <tr>
        <?php 
        //---- display raw files ------------
        $num = 0;
        $total_imploded_str = '';
        $dis_arr = array();
        foreach($raw_file_searched_arr as $theTable => $theValue){
          if($theValue){
            $tmp_num = count($theValue);
            $num += $tmp_num;
            $the_imploded = $theTable . ":" . implode(",",$theValue);
            $total_imploded_str .= $the_imploded.";;";
            array_push($dis_arr, array("$theTable", $tmp_num, $theTable . ":" . implode(",",$theValue)));
          }
        }
        $num_tables = count($dis_arr);
        ?>
        <td rowspan="<?php echo $num_tables+1;?>" valign=top bgcolor="#cccccc"> Raw File / Folder: </td>
        <td align=center bgcolor="#eeeeee"><?php echo "<font color='#FF0000'>total: $num</font>";?></td>
        <td align=center bgcolor="#eeeeee">
        <?php 
         if($num_tables){
          echo "<input type=hidden name='raw_ids' id='raw_ids' value='$total_imploded_str'>\n";
          echo "<a class=button href=\"javascript: browse_for_detail('../msManager/ms_storage_fetch_raw.php', 'Search raw file ', 'raw_ids', '','');\">[Browse]</a>";
        }else{
          echo "&nbsp;";
        }
        ?>
        </td>
     </tr>
     <?php 
     $con= 0;
     foreach($dis_arr as $tmp_dis_arr){
      $con++;
      echo "<tr>
              <td align=center bgcolor=\"#eeeeee\">" . $tmp_dis_arr[0] . ": " . $tmp_dis_arr[1]. "</td>
              <td align=center bgcolor=\"#eeeeee\">\n";
       echo "<input type=hidden name='raw_ids$con' id='raw_ids$con' value='".$tmp_dis_arr[2]."'>\n";
       echo "<a class=button href=\"javascript: browse_for_detail('../msManager/ms_storage_fetch_raw.php', 'Search raw file ', 'raw_ids$con', '','');\">[Browse]</a>";
       echo "</td>
           </tr>\n";
     }
     ?>
     <tr>
        <?php 
        //---- display search tasks -------
        $num = 0;
        $dis_arr = array();
        foreach($task_searched_arr as $theTable => $theValue){
          if($theValue){
            $tmp_num = count($theValue);
            $num += $tmp_num;
            $the_imploded = $theTable . ":" . implode(",",$theValue);
            array_push($dis_arr, array("$theTable", $tmp_num, implode(",",$theValue)));
          }
        }
        $num_tables = count($dis_arr);
        ?>
        <td rowspan="<?php echo $num_tables+1;?>" valign=top  bgcolor="#cccccc"> Auto-search Task: </td>
        <td align=center bgcolor="#eeeeee"><?php echo "<font color='#ff0000'>total: $num</font>";?></td>
        <td align=right bgcolor="#eeeeee">&nbsp;</td>
     </tr>
     <?php 
     $con= 0;
     foreach($dis_arr as $tmp_dis_arr){
      $con++;
      echo "<tr>
              <td align=center bgcolor=\"#eeeeee\">" . $tmp_dis_arr[0] . ": " . $tmp_dis_arr[1]. "</td>
              <td align=center bgcolor=\"#eeeeee\">\n";
      echo "<input type=hidden name='task_ids$con' id='task_ids$con' value='".$tmp_dis_arr[2]."'>\n";
      echo "<a class=button href=\"javascript: browse_for_detail('../msManager/ms_search_task_list.php', 'Find tasks ', 'task_ids$con', '','".$tmp_dis_arr[0]."');\">[Browse]</a>";
      echo "</td>
           </tr>\n";
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
require("site_footer.php");

//-------------------------------------------------------
//from band and exp array to get those shared band array
function get_exp_band_arr($type, $band_arr = array()){
  global $HITSDB;
  global $AccessProjectID;
  global $expDetail_Exp_str;
  $rt_arr = array();
  if(!$expDetail_Exp_str) return $rt_arr;
  $band_arr = array_unique($band_arr);
  if($band_arr){
    $band_str = implode(",",  $band_arr);
    $SQL = "select distinct ID from Band where ExpID in ($expDetail_Exp_str) and ID in($band_str)";
  }else if($type == 'TPP'){
    $SQL = "select distinct B.ID from Band B, TppProtein T where B.ID=T.BandID and B.ExpID in ($expDetail_Exp_str)";
  }else{
    $SQL = "select distinct B.ID from Band B, Hits H where B.ID=H.BandID and B.ExpID in ($expDetail_Exp_str)";
  }
  //echo $SQL;
  $results = mysqli_query($HITSDB->link, $SQL);
  while($row = mysqli_fetch_row($results)){
    array_push($rt_arr, $row[0]);
  }
  return $rt_arr;
}
//-------------------------------------------------------
//from bait array to get those baits have the pointed exp 
//$SQL_exp and $SQL_exp_group
//if no bait array passed it will get all baits have the exp
function get_exp_bait_arr($type, $selected_bait_arr = array()){
  global $HITSDB;
  global $AccessProjectID;
  global $SQL_exp, $SQL_exp_group,$where_UserID_exp;
  $rt_arr = array();
  $SQL = '';
  if($selected_bait_arr){
    //bait and hit
    $bait_str = implode(",",  $selected_bait_arr);
    $SQL = $SQL_exp ." AND E.BaitID in ($bait_str)" . $SQL_exp_group;
  }else if($type == 'Bait' or $type == 'Band'){
    $SQL_exp .= get_and_date_str('E.DateTime');
    $SQL = $SQL_exp ." AND E.ProjectID='$AccessProjectID' $where_UserID_exp " . $SQL_exp_group;
  }else{
    return $rt_arr;
  }
  //echo "get_exp_bait: $SQL <br>\n";
  $results = mysqli_query($HITSDB->link, $SQL);
  while($row = mysqli_fetch_row($results)){
    if($type == 'Band'){
      //get ExpID array
      array_push($rt_arr, $row[1]);
    }else{
      //get BaitID array
      array_push($rt_arr, $row[0]);
    }
  }
  $rt_arr = array_unique($rt_arr);
  return $rt_arr;
}

//-------------------------------------------------
//from bait arr get those baits have hits.
function get_hit_bait_arr($expDetail_bait_arr, $type=''){
  global $HITSDB;
  $rt_arr = array();
  if(!$expDetail_bait_arr){
    return $rt_arr;
  }
  $bait_e_str = implode(",", $expDetail_bait_arr);
  if($type='TPP'){
    $SQL = "SELECT Distinct BaitID FROM Hits WHERE BaitID in ($bait_e_str)";
  }else{
    $SQL = "SELECT Distinct BaitID FROM TppProtein WHERE BaitID in ($bait_e_str)";
  }
  //echo "H6: $SQL<br>\n";
  $results = mysqli_query($HITSDB->link, $SQL);
  while($row = mysqli_fetch_row($results)){
    array_push($rt_arr, $row[0]);
  }
  return $rt_arr;
}
//------------------------------------------------
//return sql like str from fields and imploded str
function get_like_str($field_arr, $search_tmp_str, $OrAnd=''){
  global $s_wildcard, $e_wildcard, $frm_search_OrAnd;
  $rt = '';
  if(!$OrAnd){
   $OrAnd = $frm_search_OrAnd;
  }else if($OrAnd == "="){
    $OrAnd = '';
  }
  foreach($field_arr as $field){
    $tmp_str = '';
    if($rt) $rt .= " OR ";
    if($OrAnd){
      $tmp_str =  "`".$field."`" . " LIKE '".$s_wildcard;
      $tmp_str .= str_replace(" ", $e_wildcard . "'  ".$OrAnd." `".$field."` LIKE '".$s_wildcard, $search_tmp_str).$e_wildcard."'";
      if($OrAnd == 'AND'){
        $tmp_str = "($tmp_str)";
      }
      $rt .= $tmp_str;
    }else{
      $search_tmp_str = str_replace("@@", " ", $search_tmp_str);
      $rt .=  "`".$field."`" . "='".$search_tmp_str."'";
    }
  }
  return $rt;
}
//------------------------------------------------
function get_and_date_str($date){
  global $date_from, $date_to;
  $rt = '';
  if($date_from and $date_to){
    $rt = " AND ($date>'".$date_from."' AND $date<'".$date_to."')";
  }
  return $rt;
}

function get_group_item_id_in_str($frm_group_id_list,$item_type){
  if(!$frm_group_id_list || !$item_type) return '';
  $item_id_str = get_all_group_recordes_str($frm_group_id_list,$item_type);
  if($item_id_str){
    return " AND ID IN($item_id_str) ";
  }else{
    return " AND 0 ";
  }
}

function get_group_recordes_str_for_hits($frm_group_id_list){
  global $HITSDB;
  $tmp_arr = explode("_", $frm_group_id_list);
  $item_type = $tmp_arr[0];
  $table_name = $tmp_arr[0]."Group";
  if(count($tmp_arr) == 3){
    $NoteTypeID = $tmp_arr[2];
  }else{
    $NoteTypeID = $tmp_arr[1];
  } 
  $SQL = "SELECT RecordID FROM $table_name WHERE NoteTypeID='$NoteTypeID'";
  if($RecordID_sql_arr = $HITSDB->fetchAll($SQL)){
    $record_ids_str = array_to_delimited_str($RecordID_sql_arr, 'RecordID');
    if($item_type == 'Experiment'){
      $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID` IN ($record_ids_str)";
      if($RecordID_sql_arr = $HITSDB->fetchAll($SQL)){
        $record_ids_str = array_to_delimited_str($RecordID_sql_arr, 'ID');
      }
    }
  }
  if($record_ids_str){
    if($item_type == 'Experiment' || $item_type == 'Band'){
      return " AND H.BandID IN ($record_ids_str) ";
    }elseif($item_type == 'Bait'){
      return " AND H.BaitID IN ($record_ids_str) ";
    }
  }
  return " AND 0 ";  
}  
?>
