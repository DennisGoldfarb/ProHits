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
ini_set('display_errors', 1);
set_time_limit(0);
ini_set("memory_limit","-1");

$frm_species = '';

include_once("../config/conf.inc.php");
include_once("../common/mysqlDB_class.php");
include_once("../common/user_class.php");
include_once("../common/common_fun.inc.php");
include_once("../analyst/common_functions.inc.php");
include_once("../msManager/is_dir_file.inc.php");

//include_once("functions.ini.php");
$remove_old = 1;
$frm_collapse_evidences = 0;

session_start();
if(!$_SESSION['USER']){
	echo "you didnot login";exit;
} 
$msg = '';
$err_msg = '';
$theAction = '';
if($_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach($request_arr as $key => $value) {
  $$key=$value;
}

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$iRefIndex_log = '../logs/iRefIndex.log';

$POST_MAX_SIZE = ini_get('post_max_size');
$UPLOAD_MAX_FILESIZE = ini_get('upload_max_filesize');
 
//permission check
//if(!is_writable($iRefIndex_log)) die("$iRefIndex_log is not writable");
$prohitsDB = new mysqlDB(PROHITS_DB);
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);
 
$PHP_SELF = $_SERVER['PHP_SELF'];

if($theAction == 'export'){
  //echo "<pre>$frm_gene_names";
  $frm_gene_names = str_replace(" ", ",", $frm_gene_names);
  $frm_gene_names = preg_replace("/[,]+/", ",", $frm_gene_names);
   
  $gene_arr = explode(",", $frm_gene_names);
//print_r($gene_arr);
  $gene_id_arr = array();
  foreach($gene_arr as $theGeneName){
    $SQL = "select EntrezGeneID from Protein_Class where GeneName='$theGeneName' and TaxID='$frm_taxID'";
 
    $results = mysqli_query($proteinDB->link, $SQL);
    if($row = mysqli_fetch_row($results)){
      $gene_id_arr[] = $row[0];
    }
  }
  if($gene_id_arr){
    //print_r($gene_id_arr);
    $gene_id_str = implode(",", $gene_id_arr);
    $SQL = "select ID, geneIDA, geneIDB, method, pmids, sourcedb from iRefIndex 
    where geneIDA is not null and geneIDB is not null and 
    (geneIDA in ($gene_id_str) or geneIDB in ($gene_id_str)) order by ID";
    //echo $SQL;exit;
    $results = mysqli_query($proteinDB->link, $SQL);
    $out_put_arr = array();
     
    while($row = mysqli_fetch_array($results)){
      if($row['sourcedb'] != 'MI:0463(biogrid)'
        and $row['sourcedb'] != 'MI:0465(dip)'
        and $row['sourcedb'] != 'MI:0469(intact)'
        and $row['sourcedb'] != 'MI:0471(mint)'
      ) continue;
      $key1 = $row['geneIDA']."_". $row['geneIDB'];
      $key2 = $row['geneIDB']."_". $row['geneIDA'];
      
      if($frm_collapse_evidences and array_key_exists($key1, $out_put_arr)){
        if(in_array($row['method']."_".$row['pmids'], $out_put_arr[$key1]['method_pmids'])){
          continue;
        }
        $out_put_arr[$key1]['method_pmids'][] = $row['method']."_".$row['pmids'];
        $out_put_arr[$key1]['count'] = $out_put_arr[$key1]['count'] + 1;
        $out_put_arr[$key1]['method'] .= "|".$row['method'];
        $out_put_arr[$key1]['pmids'] .= "|".$row['pmids'];
         
        $out_put_arr[$key1]['sourcedb'] .= "|". $row['sourcedb'];
      }else if($frm_collapse_evidences and array_key_exists($key2, $out_put_arr)){
        if(in_array($row['method']."_".$row['pmids'], $out_put_arr[$key2]['method_pmids'])){
          continue;
        }
        $out_put_arr[$key2]['method_pmids'][] = $row['method']."_".$row['pmids'];
        $out_put_arr[$key2]['count'] = $out_put_arr[$key2]['count'] + 1;
        $out_put_arr[$key2]['method'] .= "|".$row['method'];
        $out_put_arr[$key2]['pmids'] .= "|".$row['pmids'];
         
        $out_put_arr[$key2]['sourcedb'] .= "|". $row['sourcedb'];
      }else{
        if(!$frm_collapse_evidences){
          $key1 = $row['ID'];
        }
        $out_put_arr[$key1]['geneIDA'] = $row['geneIDA'];
        $out_put_arr[$key1]['geneIDB'] = $row['geneIDB'];
        
        $out_put_arr[$key1]['count'] = 1;
        $out_put_arr[$key1]['method'] = $row['method'];
        $out_put_arr[$key1]['pmids'] = $row['pmids'];
        $out_put_arr[$key1]['method_pmids'][] = $row['method']."_".$row['pmids'];
         
        $out_put_arr[$key1]['sourcedb'] = $row['sourcedb'];
      }
    }
    
    header("Content-Type: application/octet-stream");  //download-to-disk dialog
    header("Content-Disposition: attachment; filename=\"test.txt\"");
    header("Content-Transfer-Encoding: binary");
  
    $title_string = "geneIDA\tgeneIDB\tgeneA\tgeneB\tevidence#\tmethod\tpmids\tsourcedb\r\n";
    echo $title_string;
    $gene_name_arr = array();
    foreach($out_put_arr as $key=>$out_arr){
      if(!array_key_exists($out_arr['geneIDA'], $gene_name_arr)){
        $SQL = "select GeneName from Protein_Class where EntrezGeneID='".$out_arr['geneIDA']."'";
        $results = mysqli_query($proteinDB->link, $SQL);
        if($row = mysqli_fetch_row($results)){
          $gene_name_arr[$out_arr['geneIDA']] = $row[0];
        }else{
          $gene_name_arr[$out_arr['geneIDA']] = '';
        }
      }
      if(!array_key_exists($out_arr['geneIDB'], $gene_name_arr)){
        $SQL = "select GeneName from Protein_Class where EntrezGeneID='".$out_arr['geneIDB']."'";
        $results = mysqli_query($proteinDB->link, $SQL);
        if($row = mysqli_fetch_row($results)){
          $gene_name_arr[$out_arr['geneIDB']] = $row[0];
        }else{
          $gene_name_arr[$out_arr['geneIDB']] = '';
        }
      }
      
      echo "".$out_arr['geneIDA'];
      echo "\t".$out_arr['geneIDB'];
      echo "\t".$gene_name_arr[$out_arr['geneIDA']];
      echo "\t".$gene_name_arr[$out_arr['geneIDB']];
     
      echo "\t".$out_arr['count'];
      echo "\t".$out_arr['method'];
      echo "\t".$out_arr['pmids'];
     
      echo "\t".$out_arr['sourcedb'];
      echo "\r\n";
      ob_flush();
    }
    exit;
  }else{
    $err_msg = 'No record found';
  }
}
ob_end_flush();
?>
<theml>
<head>
<link rel='stylesheet' type='text/css' href='../analyst/site_style.css'>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
<script language="javascript">
function submitform(theFileID){
  var theForm = document.getElementById('uploadForm');
  var theFile = document.getElementById(theFileID);
  if(isEmptyStr(theFile.value)){
    alert("Please add file!");
    return false;
  }
  theForm.submit();
}
function exportform(theFileID){
  var theForm = document.getElementById('uploadForm');
  var theFile = document.getElementById(theFileID);
  if(isEmptyStr(theFile.value)){
    alert("Please type gene name(s)!");
    return false;
  }
  theForm.theAction.value = 'export';
  theForm.submit();
}
function show_div(sDiv, hDiv){
  var selected_obj = document.getElementById(sDiv);
  var selected_a_obj = document.getElementById(hDiv);
  _hide('display_msg');
  selected_obj.style.display = "block";
  selected_a_obj.style.display = "none";
   
}
function show_hide(theDiv){
  var selected_obj = document.getElementById(theDiv);
  _hide('display_msg');
  if(selected_obj.style.display == "block"){
    selected_obj.style.display = "none";
  }else{
    selected_obj.style.display = "block";
  }
}
function _hide(theDiv){
  var selected_obj = document.getElementById(theDiv);
  selected_obj.style.display = "none";
}
</script>
</head>
<basefont face='arial'>
<body >
<center>
<table width=90% border=0 cellspacing=0 cellpadding=0>
  <tr>
    <td>
    <span class=pop_header_text><b>iRefindex</b> database</span><br>
    <hr width=100% size=1 noshade>
    </td>
  </tr>
</table>
<DIV ID='upload_desc' STYLE="Display:block; border: #a4a4a4 solid 1px; width: 90%">
<table width=100%>
<tr>
<td>
Description:<br>
  <font face='arial' size=2pt>
  <ul> 
   <li>The function adds/updates iRefindex table in Prohits protein database.<br><br> 
   <li>The iRefindex files can be downloaded from: <br>
   <a href=http://irefindex.org/wiki/index.php?title=README_MITAB2.6_for_iRefIndex target=new>http://irefindex.org</a><br>
   File location: http://irefindex.org/download/<br><br>   
   The BioGRID files can be downloaded from: <br>
   <a href=http://thebiogrid.org/download.php target=new>http://thebiogrid.org/download.php</a><br>
   File location: BioGRID Dataset Downloads/Current Release/BIOGRID-ORGANISM-X.X.XXX.mitab.zip<br><br>
   The IntAct] files can be downloaded from: <br>
   <a href=http://www.ebi.ac.uk/intact/downloads target=new>http://www.ebi.ac.uk/intact/downloads</a><br>
   File location: Downloads/FTP/intact.zip<br><br>
   
   <li>Click<a href="javascript: show_hide('upload_file_div')" class=button>[HERE]</a> to upload iRefindex file in Prohits server protein database.
  </ul>
  </font>
  <a href="javascript: show_hide('uploaded_list')" class=button>[Uploaded files]</a> 
  <a href="javascript: show_hide('export_div')" class=button>[Export]</a>
</td></tr>
</table>
</DIV>
<br>
<DIV ID='uploaded_list' STYLE="Display:none; border: #a4a4a4 solid 1px; width: 90%">
<table width=100%>
<tr>
<td><font face='arial' size=2pt>
Uploaded files:<br>
  <br><pre>
  <?php
   echo file_get_contents($iRefIndex_log);
  ?>
  </pre>
 </font>
</td></tr>
</table>
</DIV><br>
</center>
<div id=display_msg>
<?php 
flush();
if($theAction == 'uploaded_file'){
  if($_FILES['frm_iRefIndex_file']['name']){
    $the_file = $_FILES['frm_iRefIndex_file'];
    $myfile=$the_file['tmp_name'];
    $myfileName=$the_file['name'];
    
    $path_parts = pathinfo($myfileName);
    if($path_parts['extension'] == 'zip'){
      $sub_dir_name = $path_parts['filename'];
      $sub_dir = "../TMP/iRefIndex/".$sub_dir_name;
      if(!_is_dir($sub_dir)){
        _mkdir_path($sub_dir);
      }
      $com = "unzip $myfile -d $sub_dir";
      exec($com);
      $tmp_files_dir = scandir($sub_dir);
      foreach($tmp_files_dir as $tmp_val){
        if($tmp_val == '..' || $tmp_val == '.') continue;
        $files_dir[] = $sub_dir.'/'.$tmp_val;
      }
    }else{
      $files_dir[] = $myfile;
    }   
    //--------------------------------------------------------------------------------------------------------
    $permitted_taxid_arr = array();
    if($frm_species){
      $permitted_taxid_arr[] = $frm_species;
    }
    
    $col_name_arr = array();  
    $col_name_arr['iRefindex'] = "#uidA,uidB,altA,altB,aliasA,aliasB,method,author,pmids,taxa,taxb,interactionType,sourcedb,interactionIdentifier,confidence,expansion,biological_role_A,biological_role_B,experimental_role_A,experimental_role_B,interactor_type_A,interactor_type_B,xrefs_A,xrefs_B,xrefs_Interaction,Annotations_A,Annotations_B,Annotations_Interaction,Host_organism_taxid,parameters_Interaction,Creation_date,Update_date,Checksum_A,Checksum_B,Checksum_Interaction,Negative,OriginalReferenceA,OriginalReferenceB,FinalReferenceA,FinalReferenceB,MappingScoreA,MappingScoreB,irogida,irogidb,irigid,crogida,crogidb,crigid,icrogida,icrogidb,icrigid,imex_id,edgetype,numParticipants";      
    $col_name_arr['biogrid'] = "#ID Interactor A,ID Interactor B,Alt IDs Interactor A,Alt IDs Interactor B,Aliases Interactor A,Aliases Interactor B,Interaction Detection Method,Publication 1st Author,Publication Identifiers,Taxid Interactor A,Taxid Interactor B,Interaction Types,Source Database,Interaction Identifiers,Confidence Values";    
    $col_name_arr['intact'] = "#ID(s) interactor A,ID(s) interactor B,Alt. ID(s) interactor A,Alt. ID(s) interactor B,Alias(es) interactor A,Alias(es) interactor B,Interaction detection method(s),Publication 1st author(s),Publication Identifier(s),Taxid interactor A,Taxid interactor B,Interaction type(s),Source database(s),Interaction identifier(s),Confidence value(s),Expansion method(s),Biological role(s) interactor A,Biological role(s) interactor B,Experimental role(s) interactor A,Experimental role(s) interactor B,Type(s) interactor A,Type(s) interactor B,Xref(s) interactor A,Xref(s) interactor B,Interaction Xref(s),Annotation(s) interactor A,Annotation(s) interactor B,Interaction annotation(s),Host organism(s),Interaction parameter(s),Creation date,Update date,Checksum(s) interactor A,Checksum(s) interactor B,Interaction Checksum(s),Negative,Feature(s) interactor A,Feature(s) interactor B,Stoichiometry(s) interactor A,Stoichiometry(s) interactor B,Identification method participant A,Identification method participant B";
    //---------------------------------------------------------------------------------------
    $intact_brige_arr_R = array('#ID(s) interactor A' => '#uidA',  
                                'ID(s) interactor B' => 'uidB',
                                'Alt. ID(s) interactor A' => 'altA',  
                                'Alt. ID(s) interactor B' => 'altB', 
                                'Interaction detection method(s)' => 'method',
                                'Publication Identifier(s)' => 'pmids', 
                                'Taxid interactor A' => 'taxa', 
                                'Taxid interactor B' => 'taxb', 
                                'Interaction type(s)' => 'interactionType', 
                                'Source database(s)' => 'sourcedb', 
                                'Interaction identifier(s)' => 'interactionIdentifier', 
                                'Confidence value(s)' => 'confidence', 
                                'Expansion method(s)' => 'expansion',                         
                                'Biological role(s) interactor A' => 'biological_role_A', 
                                'Biological role(s) interactor B' => 'biological_role_B', 
                                'Experimental role(s) interactor A' => 'experimental_role_A', 
                                'Experimental role(s) interactor B' => 'experimental_role_B', 
                                'Type(s) interactor A' => 'interactor_type_A', 
                                'Type(s) interactor B' => 'interactor_type_B');
     $biogrid_brige_arr_R = array('#ID Interactor A' => '#uidA',                             
                                'ID Interactor B' => 'uidB',                       
                                'Alt IDs Interactor A' => 'altA',
                                'Alt IDs Interactor B' => 'altB', 
                                'Interaction Detection Method' => 'method',
                                'Publication Identifiers' => 'pmids',
                                'Taxid Interactor A' => 'taxa',
                                'Taxid Interactor B' => 'taxb',
                                'Interaction Types' => 'interactionType', 
                                'Source Database' => 'sourcedb', 
                                'Interaction Identifiers' => 'interactionIdentifier', 
                                'Confidence Values' => 'confidence');
    //-----------------------------------------------------------------------------------------
    
    $total_counter = 0;
    foreach($files_dir as $myfile){
      if($myfile == '..' || $myfile == '.') continue;
echo "<br>Process $myfile----------------------------<br>";
      $is_other = 0;
      if(!$fp = popen("cat $myfile", "r")) {
        echo ("file $myfileRealName is missing");
      }
  
      $SQL = "select * from iRefIndex limit 1";
      $results = mysqli_query($proteinDB->link, $SQL);
      $fieldCount = mysqli_field_count($proteinDB->link);
      for ($i=0; $i<$fieldCount; $i++) { 
        $finfo = mysqli_fetch_field_direct($results, $i);
        $DB_field_arr[] = $finfo->name;
      }
      
      $line_num = 0;
      $stop =0;
      $header_str = fgets($fp);
      //--------------------------------------------------------------------------------------------------------
      $file_type = '';
      $file_header_arr = explode("\t", trim($header_str));
      foreach($col_name_arr as $col_name_key => $col_name_val){
        $tmp_col_name = explode(",", trim($col_name_val));
        $diff = array_diff($tmp_col_name, $file_header_arr);
        if(!$diff){
          $file_type = $col_name_key;
          break;
        }        
      }
      //--------------------------------------------------------------------------------------------------------
      if($file_type == 'iRefindex'){
        //check_taxid($myfile,$myfileName);
        foreach($file_header_arr as $key => $header){
          if(in_array($header, $DB_field_arr)){
            $save_to_db_col_nums[] = $key;
          }
        }
      }elseif($file_type == 'biogrid'){
        foreach($file_header_arr as $key => $header){
          if(!array_key_exists($header, $biogrid_brige_arr_R)) continue;
          if(in_array($biogrid_brige_arr_R[$header], $DB_field_arr)){
            $save_to_db_col_nums[] = $key;
          }
        }
      }elseif($file_type == 'intact'){
        foreach($file_header_arr as $key => $header){
          if(!array_key_exists($header, $intact_brige_arr_R)) continue;
          if(in_array($intact_brige_arr_R[$header], $DB_field_arr)){
            $save_to_db_col_nums[] = $key;
          }
        }
      }
      $counter = 0;      
      while ($data = fgets($fp)) {
        $data = trim($data);
        $line_num++; 
        if($line_num%900 === 0){
          echo '.';
          if($line_num%4000 === 0)  echo "$line_num\n";
          flush();
        }
        if($file_type == 'iRefindex'){
          process_iRefindex_line($data);
        }elseif($file_type == 'biogrid'){
          process_biogrid_line($data);
        }elseif($file_type == 'intact'){
          process_intact_line($data);
        }
//if($line_num>50) exit;
      }
      echo "--$counter recoders added--<br>";
      $total_counter += $counter;
    }
    echo "<font color='red'>=====$total_counter recorders added for ".$myfileName."</font><br>";
  }
  if(!$err_msg){ 
    if($status_fd = fopen($iRefIndex_log, "a+")){
      $to_file = '\nuploaded file: '.$myfileName.'='.@date('Y-m-d H:i:s');
      $msg = $to_file;
      fwrite($status_fd, $to_file."\r\n");
      fclose($status_fd);
    }else{
      echo "could not open file $statusFile to write";
    }
  }
  
  if(isset($fp))  fclose($fp);
}
echo $msg;
echo "<font color=red>$err_msg</font>";
echo "</div><center>";

?>
 
<form id=uploadForm name=uploadForm method=post action=<?php echo $PHP_SELF;?> enctype="multipart/form-data">
<input type=hidden name=theAction value='uploaded_file'>
<center>
<DIV ID='upload_file_div' STYLE="Display:none; border: #a4a4a4 solid 1px; width: 90%">
<table width=100%>
  <tr>
  	<td><b>Species:</b>
    <select name="frm_species">
    <option value='9606' selected>Homo sapiens (9606)
    <option value='562'>Escherichia coli (562)
    <option value='4932'>Saccharomyces cerevisiae (4932)
    <option value='6239'>Caenorhabditis elegans (6239)  
    <option value='7227'>Drosophila melanogaster (7227)
    <option value='10090'>Mus musculus (10090)
    <option value='559292'>Saccharomyces cerevisiae S288c (559292)
    <option value='10116'>Rattus norvegicus (Norway rat) (10116)
    </select>
    </td>
  </tr>
  <tr bgcolor=white>
  	<!--td bgcolor=#a4d5c2><b><font face="Arial" size=2pt> &nbsp; IRefIndex file (MITAB2.6) for iRefIndex :  </font></b></td-->
  	<td><input type=file size=45 name=frm_iRefIndex_file id=fasta_file></td>
  	<td>&nbsp;</td>
  </tr>
 <tr>
   <td colspan=3 ><center><div class=maintext>Upload max file size:&nbsp;<font color='red'><?php echo $UPLOAD_MAX_FILESIZE?></font>&nbsp;&nbsp;Post max size:&nbsp;<font color='red'><?php echo $POST_MAX_SIZE?></font></div></center></td>
 </tr>
</table><br>
    <input type=button value='Submit' onClick="submitform('fasta_file')">
    <input type="button" value='Close' onClick="window.close()";>
    <br>
</DIV>

<DIV ID='export_div' STYLE="Display:none; border: #a4a4a4 solid 1px; width: 90%">
<table width=100% bgcolor=#dedede>
  <tr>
  	<td><font face="Arial">
  Create a custom interaction dataset for all of the interactions found in the iRefIndex. <br>
  NOTE: to include more than one gene you must seperate each one by a space or comma.
    </td>
  </tr>
  <tr>
  	<td ><b>Gene identifier(s) :</td>
  </tr>
  <tr>
  	<td><input type="text" name="frm_gene_names" size="100" maxlength="350" id=gene_name></td>
  </tr>
  <tr>
  	<td><b>Organism</b>
    <select name="frm_taxID">
    <option value=9606>Homo sapiens
    </select>
    </td>
  </tr>
  <tr>
  	<td><b>Collapse evidences</b>
    <input type="checkbox" name="frm_collapse_evidences" value="1" checked>
    </td>
  </tr>
</table><br>
    <input type=button value='Submit' onClick="exportform('gene_name')">
    <input type="button" value='Close' onClick="window.close()";>
    <br>
</DIV>
</form>
</body>
</html>
<?php 

function check_taxid($myfile,$myfileName){
  global $proteinDB;
  global $is_other;
  if(!$myfileName){
    echo "No uploaded file";
    exit;
  }
  $tmp_arr = explode('.',$myfileName);  
  if($tmp_arr[0] == 'All'){
    echo "Process $myfileName<br>";
    $SQL_del = "TRUNCATE TABLE iRefIndex";
    mysqli_query($proteinDB->link, $SQL_del);
  }elseif(is_numeric($tmp_arr[0])){
    echo "Process $myfileName<br>";
    $taxid = $tmp_arr[0];
    $taxid_family_arr = get_TaxID_family_from_proteinDB($proteinDB, $taxid);
    $taxid_family_str = implode(',',$taxid_family_arr);
    $SQL_del = "delete from iRefIndex where taxa in($taxid_family_str) or taxb in($taxid_family_str) or geneIDA=0 or geneIDB=0 or geneIDA IS NULL or geneIDB IS NULL";
    mysqli_query($proteinDB->link, $SQL_del);
  }elseif($tmp_arr[0] == 'other'){
    $is_other = 1;
  }else{
    echo "$myfileName is not a mitab file";
    exit;
  }
}

function process_iRefindex_line($data){ 
  global $file_header_arr;
  global $save_to_db_col_nums;
  global $proteinDB;
  global $is_other;
  global $biogrid_brige_arr_R;
  global $counter;
  global $permitted_taxid_arr;
       
  $tmp_arr = explode("\t", $data);
//$conbine_arr = array_combine($file_header_arr, $tmp_arr);
/*echo "<pre>";  
print_r($conbine_arr);  
echo "</pre>";*/
   
  $SQL = "";
  $Acc_A_arr = array();
  $Acc_A_geneID_arr = array();
  $Acc_B_arr = array();
  $Acc_B_geneID_arr = array();
  $Source_Database = '';
  $pmids = '';
  $taxid_A = '';
  $taxid_B = '';
   
  foreach($tmp_arr as $col_num => $value){
    if($file_header_arr[$col_num] == 'taxa'){
      if(preg_match("/taxid:(\d+)/i", $value, $matches)){
        $value = $matches[1];
      }else{
        $value = 0;
      }
      $taxid_A = $value;
    }elseif($file_header_arr[$col_num] == 'taxb'){
      if(preg_match("/taxid:(\d+)/i", $value, $matches)){
        $value = $matches[1];
      }else{
        $value = 0;
      }
      $taxid_B = $value;
    }else if($file_header_arr[$col_num] == 'altA'){
      $t_arr = explode("|", $value);
      foreach($t_arr as $tmp_value){
        $tmp_arr = explode(":", $tmp_value, 2);
        if($tmp_arr[0] == 'refseq'){
          $Acc_A_arr[] = $tmp_arr[1];
        }else if($tmp_arr[0] == 'entrezgene/locuslink'){
          $Acc_A_geneID_arr[] = $tmp_arr[1];
        }
      }
    }else if($file_header_arr[$col_num] == 'altB'){
      $t_arr = explode("|", $value);
      foreach($t_arr as $tmp_value){
        $tmp_arr = explode(":", $tmp_value, 2);
        if($tmp_arr[0] == 'refseq'){
          $Acc_B_arr[] = $tmp_arr[1];
        }else if($tmp_arr[0] == 'entrezgene/locuslink'){
          $Acc_B_geneID_arr[] = $tmp_arr[1];
        }
      }
    }elseif($file_header_arr[$col_num] == 'method' || $file_header_arr[$col_num] == 'sourcedb'){
      $tem_val = str_replace('psi-mi:"', '', $value);
      $value = str_replace('"', '', $tem_val);
      if($file_header_arr[$col_num] == 'sourcedb'){
        $Source_Database = $value;
      }
      if($file_header_arr[$col_num] == 'method'){
        $method = $value;
      }      
    }elseif($file_header_arr[$col_num] == 'pmids'){
      $pmids = $value;
    }
    
    if(in_array($col_num, $save_to_db_col_nums)){
      if($SQL) $SQL .= ", ";
      $SQL .= " `$file_header_arr[$col_num]`='".mysqli_real_escape_string($proteinDB->link, $value)."'";
    }
  }  
  if($permitted_taxid_arr && !in_array($taxid_A,$permitted_taxid_arr) && !in_array($taxid_B,$permitted_taxid_arr)){
    return false;
  }
  if(!$Acc_A_geneID_arr && isset($Acc_A_arr[count($Acc_A_arr)-1]) && $Acc_A_arr[count($Acc_A_arr)-1]){
    $SQL_for_geneID_A = "SELECT `EntrezGeneID` FROM `Protein_Accession` WHERE `Acc`='".$Acc_A_arr[count($Acc_A_arr)-1]."'";
    $Acc_A_Q_arr = $proteinDB->fetch($SQL_for_geneID_A);
    if($Acc_A_Q_arr && $Acc_A_Q_arr['EntrezGeneID']){
      $Acc_A_geneID_arr[] = $Acc_A_Q_arr['EntrezGeneID'];
    }
  }
  if(!$Acc_B_geneID_arr && isset($Acc_B_arr[count($Acc_B_arr)-1]) && $Acc_B_arr[count($Acc_B_arr)-1]){
    $SQL_for_geneID_B = "SELECT `EntrezGeneID` FROM `Protein_Accession` WHERE `Acc`='".$Acc_B_arr[count($Acc_B_arr)-1]."'";
    $Acc_B_Q_arr = $proteinDB->fetch($SQL_for_geneID_B);
    if($Acc_B_Q_arr && $Acc_B_Q_arr['EntrezGeneID']){
      $Acc_B_geneID_arr[] = $Acc_B_Q_arr['EntrezGeneID'];
    }
  }
  
  if(!$Acc_A_geneID_arr or !$Acc_B_geneID_arr) return false;
  if($Acc_A_geneID_arr){
    $geneIDA_str = " geneIDA=".$Acc_A_geneID_arr[count($Acc_A_geneID_arr)-1];
    $SQL .= ",".$geneIDA_str;
  }
  if($Acc_B_geneID_arr){
    $geneIDB_str = " geneIDB=".$Acc_B_geneID_arr[count($Acc_B_geneID_arr)-1];
    $SQL .= ",".$geneIDB_str;
  }
  
  $select_SQL = "SELECT `ID` 
                FROM `iRefIndex` 
                WHERE $geneIDA_str 
                AND $geneIDB_str
                AND sourcedb='$Source_Database'
                AND pmids='$pmids'
                AND method='$method'";
  $tmp_sql_arr = $proteinDB->fetch($select_SQL);
  if(!$tmp_sql_arr){
    $SQL = "insert into iRefIndex set" . $SQL;
    mysqli_query($proteinDB->link, $SQL);
    $counter++;
    //if($counter>10) exit;
  }
}

function process_biogrid_line($data){ 
  global $file_header_arr;
  global $proteinDB;
  global $biogrid_brige_arr_R;
  global $permitted_taxid_arr;
  global $DB_field_arr;
  global $counter;
       
  $tmp_arr = explode("\t", $data);
  $conbine_arr = array_combine($file_header_arr, $tmp_arr);

  $SQL = "";
  $geneID_A = '';
  $geneID_B = '';
  $Source_Database = '';
  $taxid_A = '';
  $taxid_B = '';
  $pmids = '';
  $method = '';  
  
  foreach($conbine_arr as $col_name => $value){
    if($col_name == 'Taxid Interactor A'){
      if(preg_match("/taxid:(\d+)/i", $value, $matches)){
        $value = $matches[1];
      }else{
        $value = 0;
      }
      $taxid_A = $value;
    }elseif($col_name == 'Taxid Interactor B'){
      if(preg_match("/taxid:(\d+)/i", $value, $matches)){
        $value = $matches[1];
      }else{
        $value = 0;
      }
      $taxid_B = $value;
    }elseif($col_name == '#ID Interactor A'){
      $t_arr = explode(":", $value);
      if(count($t_arr) == 2){
        if($t_arr[1]){
          $geneID_A = $t_arr[1];
        }else{
          return false;
        }
      }else{
        return false;
      }
    }elseif($col_name == 'ID Interactor B'){
      $t_arr = explode(":", $value);
      if(count($t_arr) == 2){
        if($t_arr[1]){
          $geneID_B = $t_arr[1];
        }else{
          return false;
        }
      }else{
        return false;
      }
    }elseif($col_name == 'Interaction Detection Method' || $col_name == 'Source Database'){
      $tem_val = str_replace('psi-mi:"', '', $value);
      $value = str_replace('"', '', $tem_val);
      if($col_name == 'Source Database'){
        $Source_Database = $value;
      }
      if($col_name == 'Interaction Detection Method'){
        $method = $value;
      }
    }elseif($col_name == 'Publication Identifiers'){
      $pmids = $value;
    }
        
    if(isset($biogrid_brige_arr_R[$col_name]) && in_array($biogrid_brige_arr_R[$col_name], $DB_field_arr)){
      if($SQL) $SQL .= ", ";
      $SQL .= " `$biogrid_brige_arr_R[$col_name]`='".mysqli_real_escape_string($proteinDB->link, $value)."'";
    }
  }
   
  if($permitted_taxid_arr && !in_array($taxid_A,$permitted_taxid_arr) && !in_array($taxid_B,$permitted_taxid_arr)){
    return false;
  }
    
  $geneIDA_str = " geneIDA='$geneID_A'";
  $SQL .= ",".$geneIDA_str;
  $geneIDB_str = " geneIDB='$geneID_B'";
  $SQL .= ",".$geneIDB_str;
  
  if(trim($geneIDA_str) && trim($geneIDB_str)){
    $select_SQL = "SELECT `ID` 
                  FROM `iRefIndex` 
                  WHERE $geneIDA_str 
                  AND $geneIDB_str 
                  AND sourcedb='$Source_Database'
                  AND method='$method'
                  AND pmids='$pmids'";
    $tmp_sql_arr = $proteinDB->fetch($select_SQL);
    if(!$tmp_sql_arr){
      $SQL = "insert into iRefIndex set" . $SQL;
      mysqli_query($proteinDB->link, $SQL);
      $counter++; 
      //if($counter > 10) exit; 
    }
  }
}

function process_intact_line($data){ 
  global $file_header_arr;
  global $proteinDB;
  global $intact_brige_arr_R;
  global $permitted_taxid_arr;
  global $DB_field_arr;
  global $counter;
       
  $tmp_arr = explode("\t", $data);
  $conbine_arr = array_combine($file_header_arr, $tmp_arr);
  
  $SQL = "";
  $geneID_A = '';
  $geneID_B = '';
  $Source_Database = '';
  $taxid_A = '';
  $taxid_B = '';
  $pmids = '';
  $method = '';   
  
  foreach($conbine_arr as $col_name => $value){
    if($col_name == 'Taxid interactor A'){
      if(preg_match("/taxid:(\d+)/i", $value, $matches)){
        $value = $matches[1];
      }else{
        $value = 0;
      }
      $taxid_A = $value;
    }elseif($col_name == 'Taxid interactor B'){
      if(preg_match("/taxid:(\d+)/i", $value, $matches)){
        $value = $matches[1];
      }else{
        $value = 0;
      }
      $taxid_B = $value;
    }elseif($col_name == '#ID(s) interactor A'){
      $t_arr = explode(":", $value);
      if(count($t_arr) == 2){
        $gene_info = get_protein_GeneID_in_local($t_arr[1], '', $proteinDB);
        $geneID_A = $gene_info['GeneID'];
        if(!$geneID_A) return false;
      }else{
        return false;
      }
    }elseif($col_name == 'ID(s) interactor B'){
      $t_arr = explode(":", $value);
      if(count($t_arr) == 2){
        $gene_info = get_protein_GeneID_in_local($t_arr[1], '', $proteinDB);
        $geneID_B = $gene_info['GeneID'];
        if(!$geneID_B) return false;
      }else{
        return false;
      }
    }elseif($col_name == 'Interaction detection method(s)' || $col_name == 'Source database(s)'){
      $tem_val = str_replace('psi-mi:"', '', $value);
      $value = str_replace('"', '', $tem_val);
      if($col_name == 'Source database(s)'){
        $Source_Database = $value;
      }
      if($col_name == 'Interaction detection method(s)'){
        $method = $value;
      }
    }elseif($col_name == 'Publication Identifier(s)'){
      $pmids = $value;
    }
    
    if(isset($intact_brige_arr_R[$col_name]) && in_array($intact_brige_arr_R[$col_name], $DB_field_arr)){
      if($SQL) $SQL .= ", ";
      $SQL .= " `$intact_brige_arr_R[$col_name]`='".mysqli_real_escape_string($proteinDB->link, $value)."'";
    }
  }

  if($permitted_taxid_arr && !in_array($taxid_A,$permitted_taxid_arr) && !in_array($taxid_B,$permitted_taxid_arr)){
    return false;
  }
  
  $geneIDA_str = " geneIDA='$geneID_A'";
  $SQL .= ",".$geneIDA_str;
  $geneIDB_str = " geneIDB='$geneID_B'";
  $SQL .= ",".$geneIDB_str;
  
  if(trim($geneIDA_str) && trim($geneIDB_str)){
    $select_SQL = "SELECT `ID` 
                   FROM `iRefIndex`
                   WHERE $geneIDA_str 
                   AND $geneIDB_str 
                   AND sourcedb='$Source_Database'
                   AND method='$method'
                   AND pmids='$pmids'";
    $tmp_sql_arr = $proteinDB->fetch($select_SQL);
    if(!$tmp_sql_arr){
      $SQL = "insert into iRefIndex set" . $SQL;
      mysqli_query($proteinDB->link, $SQL);
      $counter++;
      //if($counter > 10) exit;
    }
  }
}
?>
