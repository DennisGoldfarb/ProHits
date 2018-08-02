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

$frm_removed_id = '';
$download_file = '';
$frm_decoy = '';
$num_files = 4;

include_once("../config/conf.inc.php");
include_once("../common/mysqlDB_class.php");
include_once("../common/user_class.php");
include_once("../common/common_fun.inc.php");
include_once("../analyst/common_functions.inc.php");

$remove_old = 1;
$frm_collapse_evidences = 0;

session_start();
if(!isset($_SESSION['USER']) || !$_SESSION['USER']){
	echo "you did not login";exit;
} 
$msg = '';
$err_msg = '';
$theAction = '';
if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}

/*echo "<pre>";
print_r($request_arr);
print_r($_FILES);
echo "</pre>";*/

$AccessUserID = $_SESSION['USER']->ID;

$POST_MAX_SIZE = ini_get('post_max_size');
$UPLOAD_MAX_FILESIZE = ini_get('upload_max_filesize');
$prohitsDB = new mysqlDB(PROHITS_DB);
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);


$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"]; 
$PHP_SELF = $_SERVER['PHP_SELF'];
$tmp_arr = explode("/",$PHP_SELF);
 
if(defined('TPP_BIN_PATH') and is_dir(TPP_BIN_PATH)){
  $decoyFASTA = TPP_BIN_PATH."/decoyFASTA";
}else{
  $decoyFASTA = $DOCUMENT_ROOT."/".$tmp_arr[1]."/EXT/TPP/bin/decoyFASTA";
}

$fasta_dir = "../TMP/modfied_fasta_files/";
$fasta_tmp_dir = "../TMP/modfied_fasta_files/tmp/";
if(!is_dir($fasta_dir)) mkdir($fasta_dir);
if(!is_dir($fasta_tmp_dir)) mkdir($fasta_tmp_dir);

$merged_file = $fasta_tmp_dir.$AccessUserID."merged_step1.fasta";
$merged_file2 = $fasta_tmp_dir.$AccessUserID."merged_step2.fasta";

if($theAction == 'download'){
  export_raw_file($download_file);
  exit;
}elseif($theAction == 'uploaded_file'){
  if($_FILES[0]['tmp_name']){
    $file_name_str = '';
    foreach($_FILES as $file_inf){
      if($file_name_str) $file_name_str .= " ";
      if($file_inf['tmp_name'] && is_file($file_inf['tmp_name'])){
        $file_name_str .= $file_inf['tmp_name'];
        exec("echo \"\" >> ". $file_inf['tmp_name']);
      }
    }
    $com = "cat $file_name_str >> $merged_file";
    
    $last_line = system($com, $retval);

    if(!is_file($merged_file)){
      echo ("file $merged_file is missing");
      exit; 
    }    
     
    if($frm_decoy){
      if(is_file($decoyFASTA)){
        $com = "$decoyFASTA -t DECOY $merged_file $merged_file2";
        $last_line = system($com, $retval);
        if(is_file($merged_file2)){
          rename($merged_file2, $merged_file);
        }
      }else{
        echo "$decoyFASTA is not a file";
        exit;
      }
    }
        
    if(!$fp = popen("cat $merged_file", "r")) {
       echo ("file $merged_file is missing");
    }
    $line_num = 0;
    $stop =0;
    
    $tmp_file_namr_arr = explode('.',$_FILES[0]['name']);
    $fasta_file = $fasta_dir.$tmp_file_namr_arr[0].'_'.@date("Ymd").'.fasta';       
    if(!$fp_w = fopen($fasta_file, "w")) {
       echo("file $fasta_file is missing");
    }
    echo "<DIV id='process_file'>";    
    while($data = fgets($fp)){
      $data = trim($data);
      $line_num++; 
      if($line_num%900 === 0){
        echo '.';
        if($line_num%4000 === 0)  echo "$line_num\n";
        flush();
        ob_flush();
      }
          
      if(preg_match('/^>DECOY.+/', $data, $matches)){
        fwrite($fp_w,$data."\r\n");
      }elseif(preg_match('/^>(.+)/', $data, $matches)){
        $protein_id_arr = array();
        $type = '';
        $protein_str = '';
        $tmp_arr = explode(" ",  $matches[1]);
        $protein_str = $tmp_arr[0];
        $tmp_split_arr = preg_split("/gn\|.+?:/", $protein_str);
        $acc_geneID_arr = array('EntrezGeneID'=>'','Acc_Version'=>'');
        if(preg_match("/^gi\|(\d+)\|\w{1,3}\|/", $tmp_split_arr[0], $matches2)){
          $protein_str = preg_replace("/^gi\|\d+\|\w{1,3}\|/", '', $protein_str);
          if(count($tmp_split_arr) == 1){
            $acc_geneID_arr = replease_gi_with_Acc_Version($matches2[1]);
          }
        }elseif(preg_match("/^gi\|(\d+)\|$/", trim($tmp_split_arr[0]), $matches2)){
          $acc_geneID_arr = replease_gi_with_Acc_Version($matches2[1]);
          $acc_v = $acc_geneID_arr['Acc_Version'];
          if($acc_v){
            $protein_str = preg_replace("/^gi\|(\d+)/", $acc_v, $protein_str);
          }
        }else{        
          $protein_str = preg_replace("/^gi\|\d+\|/", '', $protein_str);
          if(count($tmp_split_arr) == 1){
            $protein_info_arr = explode("|", $protein_str);
            for($i=0; $i<count($protein_info_arr); $i++){
              if(strlen($protein_info_arr[$i]) > 3){
                $protein_id_arr[] = $protein_info_arr[$i];
              }
            }                         
            $gene_id = '';       
            foreach($protein_id_arr as $protein_id_val){
              $gene_info = get_protein_GeneID_in_local($protein_id_val, $type, $proteinDB);
              $gene_id = $gene_info['GeneID'];
              if($gene_id){
                $acc_geneID_arr['EntrezGeneID'] = $gene_id;
                break;
              }
            }        
          }
        }
        if(count($tmp_split_arr) == 1){
          $gene_id = $acc_geneID_arr['EntrezGeneID'];
          if($gene_id){
            $gene_name = get_Gene_Name($gene_id, $proteinDB);
            $new_gene_str = "gn|".$gene_name.':'.$gene_id."|";
            $pos = strrpos($protein_str, "|");
            if($pos === false){
              $protein_str .= "|";
            }
            $protein_str .= $new_gene_str;
          }
        }  
        $tmp_arr[0] = $protein_str;                
        $new_line = '>'.implode(" ",$tmp_arr);
        fwrite($fp_w,$new_line."\r\n");
      }else{
        fwrite($fp_w,$data."\r\n");
      }
      //if($line_num > 100) break;
    }
    echo "</DIV>";
    if(isset($fp)){
      fclose($fp);
      unlink($merged_file);
    }  
    if(isset($fp_w))  fclose($fp_w);
    ob_flush(); 
  }
}

?>
<theml>
<head>
<link rel='stylesheet' type='text/css' href='../analyst/site_style.css'>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
<script language="javascript">
function submitform(){
  var theForm = document.getElementById('uploadForm');
  var theFile = document.getElementById('0');
  if(isEmptyStr(theFile.value)){
    alert("Please add first file!");
    return false;
  }
  var proecss_icon = document.getElementById('process');
  proecss_icon.style.display = 'block';
  theForm.submit();
}
function download_uploaded_file(theFile){
  var theForm = document.getElementById('uploadForm');
  theForm.theAction.value = 'download';
  theForm.download_file.value = theFile;
  theForm.submit();
}
function hide_file_process(){
  var process_obj = document.getElementById('process_file');
  process_obj.style.display = 'none';
}
</script>
</head>
<basefont face='arial'>
<body onload="hide_file_process()">
<center>
<div style='display:none' id='process'>
  <img src='../analyst/images/process.gif' border=0>
</div>
<?
ob_flush();
flush();
?>
<form id=uploadForm name=uploadForm method=post action=<?=$PHP_SELF;?> enctype="multipart/form-data">
<input type=hidden name=theAction value='uploaded_file'>
<input type=hidden name=download_file value=''>
<table width=90% border=0 cellspacing=0 cellpadding=0>
<?if(!$theAction){?>
  <tr height=50>
    <td colspan=2>
    <span class=pop_header_text><b>Add gene and decoy sequence to fasta file </b></span><br>
    <hr width=100% size=1 noshade>
    </td>
  </tr>
  <tr height=''>
    <td colspan=2>
      Instructions <a id='instruction_a' href="javascript: toggle_group_description('instruction')" class=Button>[+]</a>
    </td>
  </tr> 
  <tr>
    <td colspan=2>
    <DIV id='instruction'>
    <ul>
    <li> <font color="#008000">The fasta file should by upload to Prohits protein database before run this script</font>.<br>
    Addmin Office --> Protein DB Update --> [Upload fasta file]
    <li> Upload fasta files. 
    <li> Concatenate files to one file. 
    <li> Add decoy sequences in concatenated file (optional). 
    <li> Add gene ID and gene name in concatenated file.
    <li> Download concatenated file.    
    </ul>
    </DIV>
    </td>
  </tr>  
<?for($i=0; $i<$num_files; $i++){?>
  <tr bgcolor=white height=25>
  	<td width='30%' bgcolor='' nowrap>
    <font face="Arial" size=2pt><b>Fasta file <?=($i)?$i.'</b> (option)':''?>:</font>
    </td>
    <td width='' bgcolor=''>
    <input type=file size=45 name='<?=$i?>' id='<?=$i?>'>
    </td>
  </tr>
<?}?>
  <tr  height=38>
    <td colspan=2 >
    <font color="#008000">Concatenate files to one file</font><br>
    <input type='checkbox' name='frm_decoy'  value='Y'">
    Add decoy sequences
    </td>
  </tr>
  <tr height=38>
    <td colspan=2>
    <input type=button value='Submit' onClick="submitform()">
    <input type="reset" value="Reset">
    <input type="button" value='Close' onClick="window.close()";>
    </td>
  </tr>
<?}elseif(is_file($fasta_file)){?>
  <tr>
    <td nowrap colspan=2 align=center>
      <a class=sTitle title='Download fasta file' href="javascript: download_uploaded_file('<?=$fasta_file;?>')"> <img src=../admin_office/images/Download-lg.png border=0></a>
      <?=basename($fasta_file);?>
      <input type="button" value='Close' onClick="window.close()";>
    </td>
  </tr> 
<?}?>  
</table>
</form>
</center>   
</body>
</html>
<?
/*function  replease_gi_with_Acc_Version($gi){
  global $proteinDB;
  $SQL = "SELECT `EntrezGeneID`,`Acc_Version` FROM `Protein_Accession` WHERE `GI`='$gi'";
  $tmp_arr = $proteinDB->fetch($SQL);
  if($tmp_arr){
    return $tmp_arr;
  }else{
    return array('EntrezGeneID'=>'','Acc_Version'=>'');
  }
}*/  
?>
