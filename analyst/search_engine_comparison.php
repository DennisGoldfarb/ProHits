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
$sample_ID = 0;
//$order_by = 'Pep_num desc';

$order_by = 'Pep_num';
$sort_engine = '';
$gene_index = array();
$gene_names = array();
$engine_index = array();
$engine_list = array("Mascot", "GPM", "Sequest", "MSGF", "COMET", "iProphet");


$hits_arr = array(); 
//$hits_arr['Mascot']['gene1']['Pep_num'] = 22;
//$hits_arr['Mascot']['gene1']['Pep_num_uniqe'] = 12;
$sort_gene_index = array();
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

//-------------------------------------------------------------------------------------------------------------
$engine_list = get_project_SearchEngine('all');
$SearchEngine_lable_arr = get_SearchEngine_lable_arr($engine_list);
//-------------------------------------------------------------------------------------------------------------

$field_match_arr = array('ID'=>'ID', 'BaitID'=>'BaitID', 'GeneID'=>'GeneID', 'HitGI'=>'ProteinAcc', 'Pep_num'=>'TOTAL_NUMBER_PEPTIDES', 'Pep_num_uniqe'=>'UNIQUE_NUMBER_PEPTIDES', 'SearchEngine'=>'SearchEngine');
$field_match_arr_GeneLevel = array('ID'=>'ID', 'BaitID'=>'BaitID', 'GeneID'=>'GeneID', 'HitGI'=>'GeneName', 'Pep_num'=>'SpectralCount', 'Pep_num_uniqe'=>'`Unique`', 'SearchEngine'=>'SearchEngine');

$tmp_file_folder = "../TMP/". $USER->Username;
$tmp_file = $tmp_file_folder."/engine_conparison.csv";
if($theaction == 'download'){
  if(_is_file($tmp_file)){
    header("Content-Type: application/octet-stream");  //download-to-disk dialog
    header("Content-Disposition: attachment; filename=\"".basename($tmp_file)."\"");
    header("Content-Transfer-Encoding: binary");
    readfile("$tmp_file");
    exit;
  }else{
    echo "engine_conparison.csv is not a file";exit;
  }
}

if(!_is_dir($tmp_file_folder)) _mkdir_path($tmp_file_folder);

if(!$tmp_handle = fopen($tmp_file, 'w')){
  echo "Cannot open file ($tmp_file)";
}

$db_link = $HITSDB->link;
if(!$sample_ID){ 
  echo "no sample ID passed"; exit;
}

$SQL = "select ID, BaitID, GeneID, HitGI, Pep_num, Pep_num_uniqe, SearchEngine from Hits where
    BandID='$sample_ID' order by $order_by";
$results = mysqli_query($db_link, $SQL);

$tmp_engine = '';
$i = 0;
while($row = mysqli_fetch_row($results)){
  $tmp_engine = str_replace("Uploaded", '', $row[6]);
  $tmp_gene = ($row[2])? $row[2]:"Pro_".$row[3];
  if(isset($hits_arr[$tmp_engine][$tmp_gene]['Pep_num'])){
    $hits_arr[$tmp_engine][$tmp_gene]['Pep_num'] +=  $row[4];
  }else{
    $hits_arr[$tmp_engine][$tmp_gene]['Pep_num'] =  $row[4];
  } 
  if(isset($hits_arr[$tmp_engine][$tmp_gene]['Pep_num_uniqe'])){
    $hits_arr[$tmp_engine][$tmp_gene]['Pep_num_uniqe'] +=  $row[5];
  }else{
    $hits_arr[$tmp_engine][$tmp_gene]['Pep_num_uniqe'] =  $row[5];
  }
}

$SQL = "select ID, BaitID, GeneID, ProteinAcc, TOTAL_NUMBER_PEPTIDES, UNIQUE_NUMBER_PEPTIDES, SearchEngine from TppProtein where
    BandID='$sample_ID' order by " .$field_match_arr[$order_by];

$results = mysqli_query($db_link, $SQL);

$tmp_engine = '';
$i = 0;
while($row = mysqli_fetch_row($results)){
  $tmp_engine = str_replace("Uploaded", '', $row[6]);
  $tmp_engine = "TPP_".$tmp_engine;
  $tmp_gene = ($row[2])? $row[2]:"Pro_".$row[3];
  if(isset($hits_arr[$tmp_engine][$tmp_gene]['Pep_num'])){
    $hits_arr[$tmp_engine][$tmp_gene]['Pep_num'] +=  $row[4];
  }else{
    $hits_arr[$tmp_engine][$tmp_gene]['Pep_num'] =  $row[4];
  } 
  if(isset($hits_arr[$tmp_engine][$tmp_gene]['Pep_num_uniqe'])){
    $hits_arr[$tmp_engine][$tmp_gene]['Pep_num_uniqe'] +=  $row[5];
  }else{
    $hits_arr[$tmp_engine][$tmp_gene]['Pep_num_uniqe'] =  $row[5];
  }
}
//-------------------------------------------------------------------------------------------------------------------------------------
$SQL = "SELECT `ID`, `BaitID`, `GeneID`, `GeneName`, `SpectralCount`, `Unique`, `SearchEngine` FROM `Hits_GeneLevel` WHERE `BandID`='$sample_ID' order by " .$field_match_arr_GeneLevel[$order_by];
//echo "$SQL<br>";
$results = mysqli_query($db_link, $SQL);

$tmp_engine = '';
$i = 0;
while($row = mysqli_fetch_row($results)){
  $tmp_engine = str_replace("Uploaded", '', $row[6]);
  $tmp_engine = "GeneLevel_".$tmp_engine;
  $tmp_gene_tmp = ($row[2])? $row[2]:"";
  $tmp_gene_arr = explode(',',$tmp_gene_tmp);
  
  for($i=0; $i<count($tmp_gene_arr);$i++){
    $tmp_gene = trim($tmp_gene_arr[$i]);
    if(isset($hits_arr[$tmp_engine][$tmp_gene]['Pep_num'])){
      $hits_arr[$tmp_engine][$tmp_gene]['Pep_num'] +=  $row[4];
    }else{
      $hits_arr[$tmp_engine][$tmp_gene]['Pep_num'] =  $row[4];
    } 
    if(isset($hits_arr[$tmp_engine][$tmp_gene]['Pep_num_uniqe'])){
      $hits_arr[$tmp_engine][$tmp_gene]['Pep_num_uniqe'] +=  $row[5];
    }else{
      $hits_arr[$tmp_engine][$tmp_gene]['Pep_num_uniqe'] =  $row[5];
    }
  }
}
//------------------------------------------------------------------------------------------------------------------------------------------
foreach($engine_list as $eng){
  if(isset($hits_arr[$eng])){
    if(!$sort_engine){
      $sort_engine = $eng;
    }
    $engine_index[] = $eng;
  }
}

foreach($engine_list as $eng){
  $tmp_eng = "TPP_".$eng;
  if(isset($hits_arr[$tmp_eng])){
    if(!$sort_engine){
      $sort_engine = $tmp_eng;
    }
    $engine_index[] = $tmp_eng;
  }
}
//---------------------------------------------
foreach($engine_list as $eng){
  $tmp_eng = "GeneLevel_".$eng;
  if(isset($hits_arr[$tmp_eng])){
    if(!$sort_engine){
      $sort_engine = $tmp_eng;
    }
    $engine_index[] = $tmp_eng;
  }
}
//------------------------------------------

$tmp_gene_index = array();
foreach($hits_arr as $tmp_eng=>$gene_arr){
  if(!$sort_engine)$sort_engine = $tmp_eng;
  if($sort_engine == $tmp_eng){
    foreach($gene_arr as $tmp_gene=>$num_arr){
      $gene_index[] = $tmp_gene;
    }
  }else{
    foreach($gene_arr as $tmp_gene=>$num_arr){
      if(!in_array($tmp_gene, $tmp_gene_index)){
        $tmp_gene_index[] = $tmp_gene;
      }
    }
  }
}

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
foreach($tmp_gene_index as $tmp_gene){
  $tmp_gene = trim($tmp_gene);
  if(!in_array($tmp_gene, $gene_index)){
    $gene_index[] = $tmp_gene;
  }
}

foreach($gene_index as $tmp_gene){
  if(strpos($tmp_gene, "Pro_") === 0){
    $tmp_gene = str_replace("Pro_", "", $tmp_gene);
    $gene_names[$tmp_gene] = '';
  }else{
    $gene_names[$tmp_gene] = '';
    $SQL = "SELECT `GeneName` FROM `Protein_Class` WHERE `EntrezGeneID`='$tmp_gene'";
    $tmp_record = $proteinDB->fetch($SQL);
    if($tmp_record){
      $gene_names[$tmp_gene] = $tmp_record['GeneName'];
    }
  }
}
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="./site_style.css">
	<title>Prohits</title>
</head>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script language='javascript'>
function download_file(){
  var theform = document.item_form;
  theform.theaction.value = 'download';
  theform.submit();
}
</script>
<body>
<form name=item_form action=<?php echo $_SERVER['PHP_SELF'];?> method=post>  
<input type=hidden name=sample_ID value='<?php echo $sample_ID;?>'>
<input type=hidden name=theaction value=''>
[<A href="javascript: download_file();" class=button>Export (CSV)</A>]<br><br>
<table border=0 cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td>
    <table border=1 cellpadding="0" cellspacing="0" width="95%">
    <?php echo "<tr>";
      $to_file = '';
      foreach($engine_index as $tmp_engine){
        echo "<th width=''>".$SearchEngine_lable_arr[$tmp_engine]."<br><font size=-2>(total pep)</font></th>";
        echo "<th width=''>".$SearchEngine_lable_arr[$tmp_engine]."<br><font size=-2>(unique pep)</font></th>";
        $to_file .= $tmp_engine."(total pep),";
        $to_file .= $tmp_engine."(unique pep),";
      }
      
      echo "<th width='15%'><b>Gene ID</b></th><th width='15%'><b>Gene Name</b></th></tr>\n";
      $to_file .= "Gene ID,Gene Name";
      fwrite($tmp_handle, "$to_file\r\n");
      foreach($gene_index as $tmp_gene){
        $to_file = '';
        echo "<tr>";
        foreach($engine_index as $tmp_engine){
          echo "<td>";
          if(isset($hits_arr[$tmp_engine][$tmp_gene])){
            echo $hits_arr[$tmp_engine][$tmp_gene]['Pep_num'];
            echo "</td><td>".$hits_arr[$tmp_engine][$tmp_gene]['Pep_num_uniqe'];
            $to_file .= $hits_arr[$tmp_engine][$tmp_gene]['Pep_num'].",".$hits_arr[$tmp_engine][$tmp_gene]['Pep_num_uniqe'];
             
          }else{
            echo "&nbsp;</td><td>&nbsp;";
            $to_file .= ",";
          }
          $to_file .= ",";
          echo "</td>";
        }
        
        if(strpos($tmp_gene, "Pro_") === 0){
          $tmp_gene = str_replace("Pro_", "", $tmp_gene);
        }
        echo "<td>$tmp_gene</td>";
        echo "<td>".$gene_names[$tmp_gene]."&nbsp;</td>\n";
        echo "</tr>\n";
        $to_file .= "$tmp_gene,".$gene_names[$tmp_gene];
        fwrite($tmp_handle, "$to_file\r\n");
      }
      fclose($tmp_handle);
    ?>
      
    </table>
    </td>
  </tr>
  <tr>
</table>
</form>
</body>
</html>   