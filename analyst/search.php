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
$searchThis = '';
$frm_addwildcard = '';

$limit = 3000;
$bait_search = true;
$hit_search = true;
$band_search = true;
$rawfile_search = true;

$bait_selected_bait_arr = array();
$hit_selected_bait_arr = array();
$TPP_hit_selected_bait_arr = array();
$hit_selected_band_arr = array();
$TPP_hit_selected_band_arr = array();

$band_selected_band_arr = array();
$raw_file_searched_arr = array();
$task_searched_arr = array();

$theaction = '';
$like_str = '';
$GeneID_str = '';
$s_wildcard = '';
$e_wildcard = '';
$geneID_str = '';
$SQL_bait = '';
$msg_h = '';
$add_process_img = 1;

require("../common/site_permission.inc.php");
include("analyst/common_functions.inc.php");
require("common/common_fun.inc.php");

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);
$managerDB = new mysqlDB(MANAGER_DB);

require("site_header.php");

$frm_search_OrAnd = 'OR';

$s_wildcard = '%';
$e_wildcard = '%';

//bait search---------------------------------------------------------
if($searchThis){
  $searchThis = trim($searchThis);
  $searchThis = preg_replace("/[ ]+/", " ", $searchThis);
  $searchThis = mysqli_escape_string($HITSDB->link, $searchThis);
  $like_str = '';
  
  $bait_selected_bait_str = '';
  $bait_selected_bait_num = 0;   
  $field_arr = array("GeneID","LocusTag","GeneName","BaitAcc","Tag","Mutation","Clone","Vector");
  
  $like_str = get_like_str($field_arr,$searchThis);

  if($like_str){
    $SQL = "SELECT ID FROM Bait WHERE ProjectID='$AccessProjectID' and (" . $like_str .")";
//echo $SQL;exit;
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
if($searchThis){
  $hit_selected_bait_str = '';
  $hits_selected_bait_num = 0;
   
  $geneID_str = '';
  $geneID_str_ens = '';
  $selected_bait_gene_str = '';
    //search all gene IDs
  $like_str = get_like_str(array("GeneName"),$searchThis);
  $SQL_ens = "SELECT ENSG FROM `Protein_ClassENS` WHERE $like_str";
  $results = mysqli_query($proteinDB->link, $SQL_ens);
  if(mysqli_num_rows($results) > $limit){
     $like_str_tmp = get_like_str(array("GeneName"),$searchThis, '=');
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
    $like_str_tmp = get_like_str(array("GeneName"),$searchThis, '=');
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
    $SQL = "SELECT B.ID, H.BandID, H.Expect, H.Expect2, H.Pep_num, H.SearchEngine  from Hits H, Bait B 
    WHERE B.ID=H.BaitID and B.ProjectID='$AccessProjectID' and H.GeneID in($selected_bait_gene_str) 
    ORDER BY B.ID, H.Pep_num desc";
    $TPP_SQL = "SELECT B.ID, H.BandID, H.PROBABILITY, H.TOTAL_NUMBER_PEPTIDES, H.SearchEngine  from TppProtein H, Bait B 
    WHERE B.ID=H.BaitID and B.ProjectID='$AccessProjectID' and H.GeneID in($selected_bait_gene_str) 
    Order by B.ID, H.TOTAL_NUMBER_PEPTIDES desc";
    
    $results = mysqli_query($HITSDB->link, $SQL);
    while($row = mysqli_fetch_row($results)){
      array_push($hit_selected_bait_arr, $row[0]);
      array_push($hit_selected_band_arr, $row[1]);
      if(!isset($hit_value_for_bait_arr[$row[0]])){
        $tmp_expect = ($row[2])?$row[2]:$row[3];
        $hit_value_for_bait_arr[$row[0]]= $row[5] ." " . $tmp_expect ." / ". $row[4];
      }
      if(!isset($hit_value_for_band_arr[$row[1]])){
        $hit_value_for_band_arr[$row[1]]= $row[5] ." " . $tmp_expect ." / ". $row[4];
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
  $hit_selected_bait_arr = array_unique($hit_selected_bait_arr);
  $hit_selected_band_arr = array_unique($hit_selected_band_arr);
  $TPP_hit_selected_bait_arr = array_unique($TPP_hit_selected_bait_arr);
  $TPP_hit_selected_band_arr = array_unique($TPP_hit_selected_band_arr);
    
  if(!$hit_selected_bait_arr){ 
    $hit_search = false;
  }
}

//search sample ----------------------------------------------------
$SQL = "SELECT `ID` FROM `Band` WHERE `ProjectID`='$AccessProjectID'";
if($searchThis){
  $like_str = get_like_str(array("Location"),$searchThis);
  $SQL .= " AND (". $like_str .")";
}

//echo "Band: $SQL<br>\n";
if($band_search){
  $results = mysqli_query($HITSDB->link, $SQL);
  while($row = mysqli_fetch_row($results)){
    array_push($band_selected_band_arr, $row[0]);
  }
}

$raw_file_num = 0;
$task_num = 0;
$msTable_arr = array();
$pro_access_ID_str = '';
if($searchThis){
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
  $like_str = get_like_str(array("FileName"),$searchThis);
  $SQL_where .= " (". $like_str .")";
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
}else{
  $rawfile_search = false;
}

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
  theForm.title_lable.value=theTitle;
  theForm.table.value=theTable;
  theForm.searched_id_str.value=document.getElementById(id_str).value;
  if(id_vl_str){
    theForm.searched_id_vl_str.value=document.getElementById(id_vl_str).value;
  }
  theForm.action = theURL;
  theForm.submit();
}
</script>
<form id="searched_form" name="searched_form" method='post' action="">
<input type=hidden name='title_lable' id='title_lable' value=''>
<input type=hidden name='searched_id_str' id='searched_id_str' value=''>
<input type=hidden name='searched_id_vl_str' id='searched_id_vl_str' value=''>
<input type=hidden name='theaction' id='theaction' value='search'>
<input type=hidden name='table' id='table' value=''>
 

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
  $tmp_str = '';
  $page_lable = urlencode("\"$searchThis\"". $tmp_str );
  ?>  <DIV class="st1">
      <table width=100% border=0 cellspacing="1" cellpadding="2">
        <tr><td colspan=2 bgcolor="#9c9c9c" height=30 align=center>
        <font color="#FFFFFF"><b>Your search results for following criteria:</b></font>
        </td>
        </tr>
        <tr>
        <td width=200  bgcolor="#eeeeee"><b>Word</b>:</td><td bgcolor="#eeeeee"><?php echo $searchThis;?></td>
        </tr>
      </table>
      </DIV>
      
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
            echo "<a class=button href=\"javascript: browse_for_detail('bait.php', 'Search Bait for $page_lable', 'bait_ids', '', '');\">[Browse]</a>";
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
          echo "<a class=button href=\"javascript: browse_for_detail('bait.php', 'Search hits for $page_lable', 'hit_ids', 'hit_ids_v', '');\">[Browse]</a>";
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
          echo "<a class=button href=\"javascript: browse_for_detail('band_show.php', 'Search hits for $page_lable', 'hit_Band_ids', 'hit_Band_ids_v','');\">[Browse]</a>";
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
          echo "<a class=button href=\"javascript: browse_for_detail('bait.php', 'Search TPP hits for $page_lable', 'TPP_ids', 'hit_TPP_ids_v', 'TPP');\">[Browse]</a>";
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
          echo "<a class=button href=\"javascript: browse_for_detail('band_show.php', 'Search sample for $page_lable', 'tpp_band_ids', 'tpp_band_ids_v', 'TPP');\">[Browse]</a>";
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
          echo "<a class=button href=\"javascript: browse_for_detail('band_show.php', 'Search sample for $page_lable', 'band_ids', '','');\">[Browse]</a>";
        }else{
          echo "&nbsp;";
        }
        ?>
        </td>
     </tr>     
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
          echo "<a class=button href=\"javascript: browse_for_detail('../msManager/ms_storage_fetch_raw.php', 'Search raw file for $page_lable', 'raw_ids', '','');\">[Browse]</a>";
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
       echo "<a class=button href=\"javascript: browse_for_detail('../msManager/ms_storage_fetch_raw.php', 'Search raw file for $page_lable', 'raw_ids$con', '','');\">[Browse]</a>";
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

<?php 
require("site_footer.php");
//------------------------------------------------
//return sql like str from fields and imploded str
function get_like_str($field_arr, $search_tmp_str, $OrAnd=''){
  global $s_wildcard, $e_wildcard, $frm_search_OrAnd;
  $s_wildcard = '%';
  $e_wildcard = '%';
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
?>
