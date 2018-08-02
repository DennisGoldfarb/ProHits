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
$field_spliter = ';;';
$path = '';
require("../common/site_permission.inc.php");
require("../common/common_fun.inc.php");
require_once("./common_functions.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

if(!$path) exit;
//check if is MSPLIT in local

if(isset($TaskID)){
  echo "<pre>";
  echo file_get_contents($path);
  echo "</pre>";
  exit;
}

//is from analyst geneLevel result display
$managerDB = new mysqlDB(MANAGER_DB);
$SQL = "SELECT `pepXML_result`,
        `pepXML_original`
        FROM `GeneLevelParse` 
        WHERE `pepXML_original`='$path'
        AND `Machine`='$table'
        AND SearchEngine='$SearchEngine' 
        AND ProjectID=$AccessProjectID";     
$TMP_arr = $managerDB->fetch($SQL);

if(!$TMP_arr || !$TMP_arr['pepXML_result']){
  $msg = "The result is not exist.";
  echo "$msg<br>";
  exit;
}

$geneLevelResults = $TMP_arr['pepXML_result'];

$theFile = $geneLevelResults;

if(!is_file($theFile)){
  echo "The $theFile is not exist.";
  exit;
}

$fd = @fopen($theFile,"r");
if(!$fd){
  echo "The $theFile can not open.";
  //fatal_Error($msg);
  exit;
}

$peptide_display_index_arr = Array('Peptide','SpectralCount','IsUnique');
?>
<html>
<head>
 <title>Prohits</title>
 <link rel="stylesheet" type="text/css" href="./ms_style.css"> 
 <!--script language="Javascript" src="../commom/site_javascript.js"></script-->
 <script language="Javascript" src="./ms.js"></script>
 <!--script language="Javascript" src="site_no_right_click.inc.js"></script-->
 </head>
 <body>
<table border="0" cellpadding="1" cellspacing="1" width="100%">
  <tr>
    <td align="left" valign="center" colspan="4" bgcolor="">
      <span>
        <img border="0" src="./images/msplit.gif" alt="">
    	</span>
      <span style="COLOR: #006090;FONT-FAMILY: Arial, Tahoma, Helvetica, sans-serif;TEXT-DECORATION: none;FONT-SIZE: 20pt; padding: 0px 20px 0px 20px; font-weight : normal;border: 0px solid red;">
        Gene Level <?php echo ($SearchEngine=='GPM')?'XTandem':$SearchEngine?> Search Results
      </span><br>&nbsp<hr>
    </td>
  </tr>
<?php 
/*[Hit_11] => Array
        (
            [HitNumber] => Hit_11
            [Gene] => ACLY
            [GeneID] => 47
            [SpectralCount] => 153
            [Unique] => 24
            [Subsumed] => -
        )

//--------pep------------------------
[Hit_1144] => Array
        (
            [0] => Array
                (
                    [Peptide] => LGHPEALSAGTGSPQPPSFTYAQQR
                    [SpectralCount] => 7
                    [IsUnique] => 
                )

            [1] => Array
                (
                    [Peptide] => FSPGAPGGSGSQPNQK
                    [SpectralCount] => 2
                    [IsUnique] => 
                )

            [2] => Array
                (
                    [Peptide] => VNPFRPGDSEPPPAPGAQR
                    [SpectralCount] => 3
                    [IsUnique] => 
                )
        )*/
$peptide_start = false;
$hits_arr = array();
$peptides_arr = array();

while(!feof($fd)){    
  $buffer = trim(fgets($fd, 40960));
  if(preg_match("/^(Hit_[0-9]*)/", $buffer, $hit_matches)){
    $peptide_start = true;
    $tmp_array_hits = explode($field_spliter, $buffer);
    $hits_combine = array_combine($hits_key_arr, $tmp_array_hits);
    $hits_arr[$hits_combine['HitNumber']] = $hits_combine;
    $peptides_arr[$hits_combine['HitNumber']] = array();
    $buffer = trim(fgets($fd, 40960));//get next line
  }elseif(!trim($buffer)) {
    $peptide_start = false;
  }elseif(preg_match("/^HitNumber;;Gene;;GeneID;;SpectralCount;;Unique;;Subsumed/", $buffer)){
    $hits_key_arr = explode(";;",$buffer);
  }elseif(preg_match("/^Peptide;;SpectralCount;;IsUnique/", $buffer)){
    $peptide_key_arr = explode(";;",$buffer);
  }
  
  if($peptide_start and $buffer){
    $tmp_array_peptide = explode($field_spliter, $buffer);
    if(count($tmp_array_peptide) == 2){
      $tmp_array_peptide[] = '';
    }
    $peptide_combine = array_combine($peptide_key_arr, $tmp_array_peptide);
    $peptides_arr[$hits_combine['HitNumber']][] = $peptide_combine;
  }
}//======================end of file reading================================
fclose($fd);
?>
<tr>
    <td align="left" valign="center" colspan="4" bgcolor="">
    <table border="0" cellpadding="1" cellspacing="1" width="100%">
<?php 
foreach($hits_arr as $key => $val){
  $sigle_hit = $val;
  $hits_num = array_shift($sigle_hit);
  preg_match("/Hit_(\d+)/", $hits_num, $matches);
  $his_index = $matches[1];
  $hit_str = '';
  foreach($sigle_hit as $hit_key => $hit_val){
    if($hit_str) $hit_str .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $hit_str .= "<b>$hit_key:</b>&nbsp;&nbsp;".$hit_val;
  }
  $single_peptide = $peptides_arr[$hits_num];
?>
      <tr>
        <td colspan="" width="100" valign="top"><div class=maintext><b>&nbsp;<?php echo $his_index?>.</b>&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
        <td colspan="10"><div class=maintext><?php echo $hit_str?></div></td>
      </tr>
      <tr>
        <td colspan="" width="100" valign="top">&nbsp;</td>
<?php foreach($peptide_display_index_arr as $peptide_display_index_val){?>
        <td colspan=""><div class=maintext><b><?php echo $peptide_display_index_val?></b></div></td>
<?php }?>        
      </tr>
<?php foreach($single_peptide as $single_val){?>  
      <tr>
        <td colspan="" width="100" valign="top">&nbsp;</td>
<?php   foreach($peptide_display_index_arr as $peptide_display_index_val){?>
        <td colspan="" width="200"><div class=maintext><font color="#00773c"><?php echo $single_val[$peptide_display_index_val]?></font></div></td>
<?php   }?>        
      </tr>
<?php }?>
      <tr><td colspan="10">&nbsp;&nbsp;<hr size='1'></td></tr>
<?php 
  //echo "$hit_str<br>";
}
?>       
    </table>  
    </td>
  </tr>
</table>
<center>
<input type=button value=' Close ' onClick='javascript: window.close();' class=black_but>
</center>
</form>
</body>
</html>
