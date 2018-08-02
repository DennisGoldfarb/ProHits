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


error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(0);  // it will execute for 24 hours

ini_set("memory_limit","-1");
error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(0);  // it will execute for 24 hours

$powerArr['Expect2'] = 1/2;

$itemlableMaxL = 0;
$Expect = 'Expect';
$MAX = 'MAX';
$frm_filter1 = '';
$orderby_peptide = '0';
$asc_desc = 'DESC';
$MAX_instance_num = 0;
$filter_instance = '0';
$filter_score = '';
$div_id_arr = array();
$Is_geneLevel = 0;

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
include("analyst/comparison_common_functions.php");
require_once("msManager/is_dir_file.inc.php");
ini_set("memory_limit","-1");

$PROTEINDB = new mysqlDB(PROHITS_PROTEINS_DB);
if(strstr($SearchEngine, 'TPP')){
  $SearchEngine = 'TPP';
}elseif(strstr($SearchEngine, 'SEQUEST')){
  $SearchEngine = 'SEQUEST';
}

if($SearchEngine != 'TPP'){
  $MAX_sore = 0;
}
$MIN_sore = 0;
if($theaction == "display_sequence"){
  echo "@@**@@".$div_id."@@**@@";
  get_Sequence_for_peptide($tmpProteinstr_uniq,$peptideStr,$div_id,$SearchEngine,$ModificationsStr);
  exit;
}

if($SearchEngine == 'Mascot'){
  $filter_score_lable = "Mascot Score < ";
  $orderby = 'Expect';
}elseif($SearchEngine == 'GPM'){
  $filter_score_lable = "GPM Expect > ";
  $orderby = 'Expect2';
}elseif($SearchEngine == 'SEQUEST'){
  $filter_score_lable = "SEQUEST Score < ";
  $orderby = 'Expect';
}elseif($Is_geneLevel){
  $filter_score_lable = "Spectral Count < ";
  $orderby = 'Spectral Count';  
}else{
  $filter_score_lable = "Init Prob < ";
  $orderby = 'Init_Prob';
}
$filter_total_Instance = "Total Instance < ";

$comparisonDir = "../TMP/comparison/P_$AccessProjectID/";
if(!_is_dir($comparisonDir)) _mkdir_path($comparisonDir);
$mapFileName = $AccessUserID."_peptide_compareson_map.txt";
$fileFullName = $comparisonDir.$mapFileName;


//echo "\$fileFullName=$fileFullName<br>";

if(!_is_file($fileFullName)){
  echo "File $fileFullName is not exist.";
  exit;
}
if(!$map_file_handle = fopen($fileFullName, "r")){
  echo "cannot open file: $fileFullName";
  exit;
}
$tableName = $AccessUserID."_peptide_compareson_table.csv";
$tableFullName = $comparisonDir.$tableName;

if($theaction == 'export'){
  export_file($tableFullName);
  exit;
} 
if(!$table_file_handle = fopen($tableFullName, "w")){
  echo "cannot open file: $tableFullName";
  exit;
}

$lineCounter = 0;
$itemlableMaxL = 0;
$itemLableArr = array();
$itemLableBgCorlorArr = array();
$hitsArr = array();
while(!feof($map_file_handle)){
  $lineCounter++;
  $buffer = fgets($map_file_handle);
  $buffer = trim($buffer);
  if($lineCounter == 1){
    $tmpArr = explode(',,',$buffer);
    $itemlableMaxL = $tmpArr[0];
    $currentType = $tmpArr[1];
  }elseif($lineCounter == 2){  
    $itemLableArr = explode(',,',$buffer);
  }elseif($lineCounter == 3){
    $itemLableBgCorlorArr = explode(',,',$buffer);
  }elseif($lineCounter == 4){ 
    $lableDetailArr = explode(',,',$buffer);
  }
  if($lineCounter == $lineNum){
    $tmpHitsGiArr = explode(':::',$buffer);
    $hitsArr = explode(',,',$tmpHitsGiArr[0]);
    $GIArr = explode(',,',$tmpHitsGiArr[1]);
    break;
  } 
}

if($SearchEngine == 'GPM'){
  $Expect = 'Expect2';
  $MAX = 'MIN';
}
$sqlInStr = '';
foreach($hitsArr as $hitsVal){
  $hits_ID = trim($hitsVal);
  if(!$hits_ID) continue;
  if($sqlInStr) $sqlInStr .= ',';
  $sqlInStr .= $hits_ID;
}
if($Is_geneLevel){
  $SQL = "SELECT $MAX(SpectralCount) as biggestNum
          FROM `Peptide_GeneLevel` 
          WHERE `HitID` IN($sqlInStr)";
}elseif($SearchEngine == 'TPP'){
  $SQL = "SELECT $MAX(INITIAL_PROBABILITY) as biggestNum
          FROM `TppPeptideGroup` 
          WHERE `ProteinID` IN($sqlInStr)";
}elseif($SearchEngine == 'SEQUEST'){
  $SQL = "SELECT $MAX($Expect) as biggestNum
          FROM `SequestPeptide` 
          WHERE `HitID` IN($sqlInStr)";
}else{
  $SQL = "SELECT $MAX($Expect) as biggestNum
          FROM `Peptide` 
          WHERE `HitID` IN($sqlInStr)";
}
$tmpSqlArr = $HITSDB->fetch($SQL);

$maxScore = $tmpSqlArr['biggestNum'];

$subWhere = '';
if($frm_filter1){
  if($SearchEngine == 'TPP'){
    $subWhere = " AND IS_CONTRIBUTING_EVIDENCE='Y' ";
  }elseif($SearchEngine == 'Mascot'){
    $subWhere = " AND Status='RB'";
  }  
}

$itemNameArr = array();
$peptideIndexArr = array();
$peptideIndexArr2 = array();
$modificationsIndexArr = array();
$selectElementArr = array();
if($orderby_peptide != 'Sequence' && $orderby_peptide != 'Modifications'){
  add_leaf($orderby_peptide);
}

for($k=0; $k<count($hitsArr);$k++){
  $emptyArr = array();
  if(!$hitsArr[$k]){
    $itemNameArr[$k] = $emptyArr;
    continue;
  }
  $selectElementArr[$k] = $itemLableArr[$k];
  if($orderby_peptide != 'Sequence' && $orderby_peptide != 'Modifications' && $orderby_peptide == $k) continue;
  add_leaf($k);
}
if($orderby_peptide == 'Modifications'){
  $peptideIndexArr = array();
  if($asc_desc == 'DESC'){
    arsort($modificationsIndexArr);
  }else{
    asort($modificationsIndexArr);
  }
  foreach($modificationsIndexArr as $key => $val){
    array_push($peptideIndexArr, $key);
  }
}elseif($orderby_peptide == 'Sequence'){
  if($asc_desc == 'DESC'){
    rsort($peptideIndexArr);
  }else{
    sort($peptideIndexArr);
  }  
}else{
  if($asc_desc == 'ASC'){
    $peptideIndexArr = $peptideIndexArr2;
  }
}
fclose($map_file_handle);
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>ProHits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<link rel="stylesheet" type="text/css" href="./tool_tip_style.css">
<link rel="stylesheet" type="text/css" href="./colorPicker_style.css">
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script src="../common/javascript/jquery.hoverIntent.js" type="text/javascript"></script>
<style type="text/css">
TD {
  font-family : Arial, Helvetica, sans-serif;
  FONT-SIZE: 7pt;
}
.m_bg{
  background-color: #cc0066; 
  color: white;
}
.m_bg_2{
  background-color: #9e9e9e; 
  color: white;
  font-weight : bold;
}
</style>
</head>
<META content="MSHTML 6.00.2900.3199" name=GENERATOR></head>
<basefont face="arial">
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 
topMargin=5 rightMargin=5 marginheight="5" marginwidth="5">
<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language="Javascript">
function sort_peptide(theForm){
  if(theForm.orderby_peptide.value == '-1'){
    alert("Please select the 'Peptide Sequence', 'Peptide Modifications' or a <?php echo (($currentType=='Band')?'Sample':$currentType)?> for sorting.");
    return false;
  }else{
    theForm.theaction.value = '';
    theForm.submit();
  }  
}
function export_file(){
  theForm = document.getElementById('peptide_form');
  theForm.theaction.value = 'export';
  theForm.submit();
}
function export_all(protein_id){
  theForm = document.peptide_form;
  theForm.action = "./export_hits.php";
  theForm.peptide_report_protein_id.value = protein_id;
  theForm.theaction.value = "generate_map_file";
  theForm.submit();
}

function toggle_detail(tmpProteinstr_uniq, peptideStr, div_id_s, ModificationsStr){
  var div_obj = document.getElementById(div_id_s);
  var innerHTML = trim(div_obj.innerHTML)
  if(innerHTML != '') return;
  queryString = "tmpProteinstr_uniq=" + tmpProteinstr_uniq + "&peptideStr=" + peptideStr + "&div_id=" + div_id_s + "&ModificationsStr=" + ModificationsStr + "&SearchEngine=<?php echo $SearchEngine?>" + "&theaction=display_sequence";
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function processAjaxReturn(rp){
  var ret_html_arr = rp.split("@@**@@");
  if(ret_html_arr.length == 3){
    var obj_id = trimString(ret_html_arr[1]);
    document.getElementById(obj_id).innerHTML = ret_html_arr[2];
    return;
  }
}

function hideTips_for_peptide(tipDiv_head){
  for(var i=0; i<div_id_arr.length; i++){
    var Div_id = tipDiv_head + div_id_arr[i];
    hideTip(Div_id);
  }
}

document.onclick=check;
var div_id = '';
var tmp_flag = 0;
function check(e){
  if(tmp_flag == 1){
    tmp_flag = 0;
    return;
  }
  for(var i=0; i<div_id_arr.length; i++){
    var div_id_tmp = "line_" + div_id_arr[i];
    var obj_tmp = document.getElementById(div_id_tmp);
    if(obj_tmp.style.display == 'block'){
      div_id = div_id_tmp;
      break;
    }
  }
  if(div_id != ''){
    var target = (e && e.target) || (event && event.srcElement);
    var obj = document.getElementById(div_id);
    checkParent(target)?obj.style.display='none':null;
  }  
}
function checkParent(t){
  while(t.parentNode){
    if(t==document.getElementById(div_id)){
      return false
    }
    t=t.parentNode
  }
  return true
}
</script>
<DIV ID='hit_detail_div' STYLE="position: absolute; 
                          display: none;
                          border: black solid 1px;
                          width: 200px";>
  <table align="center" cellspacing="0" cellpadding="1" border="0" width=100% bgcolor="#e6e6cc">
    <tr bgcolor="#c1c184" height=20>
      <td valign="bottem">
        <font color="white" face="helvetica,arial,futura" size="2"><b>Hit details</b></font>
      </td>
    </tr>
    <tr><td id='hit_detail_td'></td></tr>
  </table>   
</DIV> 
<center>

<form name='peptide_form' id='peptide_form' action='<?php echo $PHP_SELF;?>' method='post'>
<?php foreach($request_arr as $key => $value){
    if($key == "frm_filter1") continue;
?>
  <input type=hidden name='<?php echo $key?>' value='<?php echo $value?>'>
<?php }?>
  <input type='hidden' name='peptide_report_protein_id' value='<?php echo $peptide_report_protein_id;?>'>

<table align="" bgcolor='' cellspacing="1" cellpadding="0" border="0" width=750>
  <tr>
    <td colspan="3" align="center">
      <font face="Arial" size=+2><b>Peptide Comparison</b>
      </font>
    </td>
  </tr>
  <?php 
    $filte1_lable = '';
    if($SearchEngine == 'TPP'){
      $filte1_lable = 'Remove none contributing evidence';
    }elseif($SearchEngine == 'Mascot'){
      $filte1_lable = 'Remove none red bold';
    }
  ?>
  <tr>
    <td colspan="3" >
      <table align="" bgcolor='' cellspacing="1" cellpadding="2" border="0" width=100%>
        <tr>
          <td nowrap bgcolor="#b7c1c8" width=70% colspan="4">&nbsp;&nbsp;
      			<font size="2" face="Arial"><b>Sort by:</b></font>&nbsp;&nbsp;
            <select name="orderby_peptide" size=1>
              <option value='-1'>
              <option value='Sequence' <?php echo (($orderby_peptide=='Sequence')?'selected':'')?>>Peptide Sequence<br>
              <option value='Modifications' <?php echo (($orderby_peptide=='Modifications')?'selected':'')?>>Peptide Modifications<br>
              <option value='-1'>
          <?php foreach($selectElementArr as $key => $value){?>
              <option value='<?php echo $key?>' <?php echo (($orderby_peptide=="$key")?'selected':'')?>><?php echo $value?><br>  
          <?php }?>
            </select>&nbsp;&nbsp;&nbsp;&nbsp;
            <font size=2>Descending</font><input type=radio name=asc_desc value='DESC' <?php echo (isset($asc_desc) && $asc_desc=='DESC')?'checked':''?>>&nbsp;&nbsp;&nbsp;
            <font size=2>Ascending</font><input type=radio name=asc_desc value='ASC' <?php echo (isset($asc_desc) && $asc_desc=='ASC')?'checked':''?>>&nbsp;
          </td>
        </tr>
        <tr>
          <td nowrap bgcolor="#b7c1c8" width=7%>&nbsp;&nbsp;
      			<font size="2" face="Arial"><b>Filter:</b></font>&nbsp;&nbsp;
          </td>  
          <td nowrap bgcolor="#b7c1c8" width=31%><font size=2 color=black><?php echo $filter_score_lable?></font>
            <select name="filter_score" size=1>
            <?php filter_option_list('filter_score',$filter_score,$MAX_sore);?>
            </select>&nbsp;&nbsp;&nbsp;&nbsp;
          </td>  
          <td nowrap bgcolor="#b7c1c8" width=31%><font size=2 color=black><?php echo $filter_total_Instance?></font>
            <select name="filter_instance" size=1>
            <?php filter_option_list('filter_instance',$filter_instance,$MAX_instance_num);?>
            </select>&nbsp;&nbsp;&nbsp;&nbsp;
          </td>
          <?php if($SearchEngine != 'GPM' && !$Is_geneLevel){?>
          <td bgcolor="#b7c1c8" nowrap>
            <font size=2 color=black><?php echo $filte1_lable?></font>&nbsp;&nbsp;
            <input type=checkbox name=frm_filter1 value='Y' <?php echo ($frm_filter1=='Y')?"checked":""?>>
          </td>  
          <?php }?>
        </tr>
      </table>
    </td>
  </tr>
	<tr>
  <?php 
    $power = 1;
    $biggestPowedSore = pow($maxScore,$power);
    $powerColorIndex = 'Peptide';
    $theaction = "showNormal";
    $colorArrSet = '';
    $passedTypeArr = array();
    $frm_color_mode = '';
    if($SearchEngine != 'TPP'){
      $orderby_peptide = 'Protein_Expect';
    }else{
      $orderby_peptide = 'Init_Prob';
    }
    get_colorArrSets($powerColorIndex, $colorArrSet,$aa);
		print_color_bar($colorArrSet);
    $GeneLable = '';
    if($GeneID){
      $GeneID = urldecode($GeneID);
      $GeneLable = "Gene ID: $GeneID &nbsp;&nbsp;&nbsp;";
    }  
    if($GeneName){
      $GeneName = urldecode($GeneName);
      $GeneLable .= "Gene Name: ".$GeneName."&nbsp;";
    }  
  ?>
    <td valign="BOTTOM" colspan=1 width=60%>
      <table bgcolor='' cellspacing="3" cellpadding="0" border="0" width=100%>
      <tr>
      <td align="right"><input type=button name=sort_submit value="     GO     " onclick="sort_peptide(this.form);">&nbsp;&nbsp;&nbsp;</td>		
      </tr>
      <tr>
      <td align="right" valign="BOTTOM" nowrap><font size=2><b><?php echo $GeneLable?></b></font></td>
      </tr>
      <tr>
      <td align="right" valign="BOTTOM">
      <font size=2>
      <a href="javascript: export_file();">[Export (matrix)]</a>&nbsp;&nbsp;
 <?php if(!$Is_geneLevel){?> 
      <a href="javascript: export_all()";>[Export (select)]</a>
 <?php }?>
      </font></td>  
      </tr>
      </table>
    <td>	
  </tr>
</table>
<table align="center" bgcolor="" cellspacing="0" cellpadding="0" border="0" width=750>
  <tr>
<?php 

fwrite($table_file_handle, "peptide report\r\n");
fwrite($table_file_handle, "Type: $currentType\r\n");
fwrite($table_file_handle, "Gene ID: $GeneID\r\n");
fwrite($table_file_handle, "Gene Name: $GeneName\r\n");
fwrite($table_file_handle, "SearchEngine: $SearchEngine\r\n");
fwrite($table_file_handle, "Project name: $AccessProjectName\r\n");
fwrite($table_file_handle, "Created by: $AccessUserName\r\n");
fwrite($table_file_handle, "Creation date: ".@date("Y-m-d")."\r\n\r\n");

$start_J_index = 0;
$end_J_index = count($itemLableArr);
$title_line = '';
foreach($itemLableArr as $value){
  $title_line .= $value.",";
}
$title_line .= "Peptide Sequence,Peptide Modifications,Protein\r\n";
fwrite($table_file_handle, $title_line);

for($j=$start_J_index; $j<$end_J_index; $j++){
  if($itemLableBgCorlorArr[$j] != '000000'){
  $tmp_arr = explode(":</b><br>",$lableDetailArr[$j],2);
  $lableDetail = $tmp_arr[0]."</b>;;".$tmp_arr[1];
?>  
  <td colspan="" class=s20 align=center bgcolor=<?php echo $itemLableBgCorlorArr[$j]?>>
  <a  href="#" title="<?php echo $lableDetail;?>">
    <img src='./comparison_results_create_image.php?strMaxL=<?php echo $itemlableMaxL;?>&displayedStr=<?php echo $itemLableArr[$j];?>&lableBgc=<?php echo $itemLableBgCorlorArr[$j];?>&fontSize=2' border=0></font>
  </a>
  
<?php }else{?>  
  <td colspan="" class=s20 align=center bgcolor=<?php echo $itemLableBgCorlorArr[$j]?>>
    <img src='./comparison_results_create_image.php?strMaxL=<?php echo $itemlableMaxL;?>&displayedStr=<?php echo $itemLableArr[$j];?>&lableBgc=<?php echo $itemLableBgCorlorArr[$j];?>&fontSize=2' border=0></font>
<?php }?> 
  </td>
<?php 
}
?> 
  <td bgcolor="#aeaeae" nowrap align=center class=s20><font size=2><b>Peptide Sequence</b></font></td>
  <td bgcolor="#aeaeae" nowrap align=center class=s21><font size=2><b>Peptide <br>&nbsp;Modifications&nbsp;</b></font></td>
  <td bgcolor="#aeaeae" nowrap align=center class=s21><font size=2><b>Protein</b></font></td> 
 </tr>
<?php 
$start_I_index = 0;
$end_I_index = count($peptideIndexArr);
for($i=$start_I_index; $i<$end_I_index; $i++){
  $filter_flag = 1;
  for($j=$start_J_index; $j<$end_J_index; $j++){
    $tmpPepArr = $itemNameArr[$j];
    if(array_key_exists($peptideIndexArr[$i], $tmpPepArr)){
      $tmpCounter = 0;
      $displayedScore = '';
      $Total_instance = '';
      foreach($tmpPepArr[$peptideIndexArr[$i]] as $tmpKey => $tmpVal){
        $tmpCounter++;
        if($tmpCounter < 3) continue;
        if($tmpCounter == 3) $displayedScore = $tmpVal;
        if($tmpKey == "Total instance"){
          $Total_instance = $tmpVal;
        }
      }
      if((($SearchEngine == 'GPM' && ($displayedScore <= $filter_score || $filter_score === '')) || ($SearchEngine != 'GPM' && $displayedScore >= $filter_score)) && $Total_instance >= $filter_instance){
        $filter_flag = 0;
        break;
      }  
    }  
  }
  if($filter_flag) continue;
?>
  <tr bgcolor="#ececec" onmousedown="highlightTR(this, 'click', '#CCFFCC', '#ececec')";>
<?php 
  $tmpProteinArr = array();
  $content_line = '';
  for($j=$start_J_index; $j<$end_J_index; $j++){
    $tmpPepArr = $itemNameArr[$j];
    if(array_key_exists($peptideIndexArr[$i], $tmpPepArr)){
      if($itemLableBgCorlorArr[$j] != '000000'){
        $popWstr = $lableDetailArr[$j].'<br>---------------------';
      }else{
        $popWstr = '<font color=green><b>BaitID  GeneName</b></font><br>'.$lableDetailArr[$j].'<br>---------------------';
      }
      $popWstr .= "<br>Protein ID: ".$GIArr[$j];  
      $tmpCounter = 0;
      $displayedScore = '';
      $numOfClass='s14';
      $font_color = "black";
      $Total_instance = '';
      foreach($tmpPepArr[$peptideIndexArr[$i]] as $tmpKey => $tmpVal){
        $tmpCounter++;
        if($tmpCounter < 3) continue;
        if($tmpCounter == 3) $displayedScore = $tmpVal;
        $popWstr .= '<br>';
        $popWstr .= $tmpKey.': '.$tmpVal;
        if($tmpKey == "Total instance"){
          $Total_instance = $tmpVal;
        }
        if($SearchEngine == 'TPP'){
          if($tmpKey == 'Evidence' && $tmpVal == 'Y'){
            $numOfClass='s13_1';
            $font_color = "#df0070";
          }  
        }elseif($SearchEngine == 'Mascot'){
          if($tmpKey == 'Status'){
            if($tmpVal == 'RB'){
              $numOfClass='s13_2';
              $font_color = "#df0070";
            }elseif($tmpVal == 'R'){
              $numOfClass='s13_1';
              $font_color = "#df0070";
            }elseif($tmpVal == 'B'){
              $numOfClass='s13_3';
              $font_color = "black";
            }
          }
        }
      }    
      $cellBgcolor = color_num($displayedScore, $colorIndex);
      array_push($tmpProteinArr, $GIArr[$j]);
      $popWstr = '<b>Peptide details</b>;;'.$popWstr;
      $content_line .= trim($displayedScore).",";
      if((($SearchEngine == 'GPM' && ($displayedScore <= $filter_score || $filter_score === '')) || ($SearchEngine != 'GPM' && $displayedScore >= $filter_score)) && $Total_instance >= $filter_instance){
        ?>
        <td class=<?php echo $numOfClass;?> align=center bgcolor='<?php echo $cellBgcolor;?>'>
        <a  href="#" title="<?php echo $popWstr;?>">
        <font color='<?php echo $font_color?>'>
        <?php echo trim($displayedScore);?>
        </font>&nbsp;<font size="1">(<?php echo $Total_instance;?>)</font>
        </a>
        </td>
      <?php 
      }else{
      ?>
        <td align=center class=s15>&nbsp;</td>
      <?php 
      }
    }else{
      $content_line .= ',';
      ?>
        <td align=center class=s15>&nbsp;</td>
      <?php 
    }
  }
  $tmp_peptide_arr = explode("&&**&&", $peptideIndexArr[$i]);
  if($SearchEngine == 'TPP'){
    $peptideStr = preg_replace('/(\[\d+?\])/', '<font color=red>\\1</font>', $tmp_peptide_arr[0]);
  }else{
    $peptideStr = $tmp_peptide_arr[0];
  }
  $peptideStr = preg_replace('/\*/', '', $peptideStr);
  $ModificationsStr = (isset($tmp_peptide_arr[1]))?$tmp_peptide_arr[1]:'';
  
  $tmpProteinArr_uniq = array_unique($tmpProteinArr);
  $proteinIDstr_uniq = implode(",", $tmpProteinArr_uniq);
  if(count($tmpProteinArr_uniq) == 1){
    $proteinIDstr = $tmpProteinArr_uniq[0].";"; 
  }else{  
    $proteinIDstr = implode(";", $tmpProteinArr); 
    $proteinIDstr .= ";";
  }   
  $search = array(",", "<font color=red>", "</font>","&nbsp;");
  $replace = array(";", "", "", "");
  $content_line .= str_replace($search,$replace,$peptideStr).",".str_replace($search,$replace,$ModificationsStr).",".$proteinIDstr."\r\n";
  fwrite($table_file_handle, $content_line);
  $tmpProteinstr_uniq = implode(",", $tmpProteinArr_uniq);
  array_push($div_id_arr, $i); 
  $div_id = "line_".$i;
?>
  <td align='' class=s15_2 nowrap>
  <?php echo $peptideStr?>
  </td>
  <td align='' class=s15_2 nowrap><font color="#ff0000"><?php echo $ModificationsStr?></font></td>
  <td align=left class=s15>
<?php if(!$Is_geneLevel){?>  
  <a href="javascript: href_show_hand();" onclick="hideTips_for_peptide('line_'); toggle_detail('<?php echo $tmpProteinstr_uniq;?>','<?php echo $peptideStr;?>','<?php echo $div_id;?>','<?php echo $ModificationsStr?>'); showTip_right(event,'<?php echo $div_id;?>'); tmp_flag=1;" >
  <img border="0" src="images/desciption_pop.gif" alt="Description">
  </a>
  <DIV ID='<?php echo $div_id;?>' STYLE="position: absolute;display: none;border: black solid 1px;width: 400px; padding:0px 10px 0px 10px";>
  </DIV>
  <?php echo $proteinIDstr_uniq?>
<?php }else{?>
    <img border="0" src="images/icon_empty.gif" alt="Description">&nbsp;&nbsp;
<?php }?>  
  </td>
  </tr>
<?php 
}
?> 
</table>
</form>
</BODY>
</html>
<script language="javascript">
var div_id_arr = new Array();
<?php foreach($div_id_arr as $div_id_value){?>
    div_id_arr.push("<?php echo $div_id_value?>"); 
<?php }?>
</script>
<?php 
function add_leaf($index){
  global $SearchEngine, $subWhere, $Expect, $HITSDB, $itemNameArr,$peptideIndexArr,$hitsArr,$peptideIndexArr2,$asc_desc;
  global $modificationsIndexArr,$MAX_instance_num,$MAX_sore,$MIN_sore;
  global $Is_geneLevel;
  $hitID = $hitsArr[$index];
  $evidence_num_arr = array();   
  if($SearchEngine == 'TPP'){
    $SQL = "SELECT `ProteinID`,
            `Sequence`,
            `INITIAL_PROBABILITY` as `Init Prob`,
            `NSP_ADJUSTED_PROBABILITY` as `Nsp Adj Prob`,
            `WEIGHT` as `Weight`,
            `N_SIBLING_PEPTIDES` as `NSP`,
            `N_INSTANCES` as `Total instance`,
            `IS_CONTRIBUTING_EVIDENCE` as `Evidence`,
            `CALC_MASS` as `Mass`,
             `CHARGE` as `Charge`
            FROM `TppPeptideGroup` 
            WHERE `ProteinID`='$hitID' $subWhere
            ORDER BY `INITIAL_PROBABILITY` DESC";
  }elseif($SearchEngine == 'SEQUEST'){
    $SQL = "SELECT `HitID`,
            `Sequence`,
             Expect as `SEQUEST Score`,
            `Charge`,
            `MZ`,
            `MASS` as Mass,
            `Location`,
            TIC,
            `Modifications`,
            Ppm
            FROM `SequestPeptide` WHERE `HitID`='$hitID' $subWhere
            ORDER BY $Expect DESC";
  }elseif($Is_geneLevel){
    $SQL = "SELECT `HitID`,
            `Sequence`,
            `SpectralCount` AS `Spectral Count`,
            `IsUnique`,
            `Miss`,
            `Location`,
            `Modifications`
            FROM `Peptide_GeneLevel` 
            WHERE `HitID`='$hitID' $subWhere
            ORDER BY SpectralCount DESC";
  }else{
    $SQL = "SELECT `HitID`,
            `Sequence`,
            $Expect as ".(($SearchEngine == 'Mascot')?"`Mascot Score`":"`GPM Expect`").",
            `Charge`,
            `MZ`,
            `MASS` as Mass,
            `Location`,
            `Intensity_log` as `Intensity log`,
            `Modifications`,
            `Status`
            FROM `Peptide` WHERE `HitID`='$hitID' $subWhere
            ORDER BY $Expect DESC";
  }
  if($tmpPeptideArr = $HITSDB->fetchAll($SQL)){
 
    for($i=0; $i<count($tmpPeptideArr); $i++){
      if($SearchEngine != 'TPP'){
        if($tmpPeptideArr[$i]['Modifications']){
          $tmpPeptideArr[$i]['Sequence'] .= "&&**&&" . $tmpPeptideArr[$i]['Modifications'];
        }
      }else{
        if(preg_match_all('/([A-Z]\[\d+\])/',$tmpPeptideArr[$i]['Sequence'],$matches)){
          $tmpPeptideArr[$i]['Modifications'] = implode(",", $matches[1]);
          $tmpPeptideArr[$i]['Sequence'] .= "&&**&&" . $tmpPeptideArr[$i]['Modifications'];
        }else{
          $tmpPeptideArr[$i]['Modifications'] = '';
        }
      }
      $modificationsIndexArr[$tmpPeptideArr[$i]['Sequence']] = $tmpPeptideArr[$i]['Modifications'];
    }
         
    $peptideArr = array();
    $tmpReverseArr = array();
    $evidence_num_arr = array();    
    foreach($tmpPeptideArr as $tmpPeptideVal){
      if($tmpPeptideVal['Sequence']){
        if(!in_array($tmpPeptideVal['Sequence'], $peptideIndexArr)){
          array_push($peptideIndexArr, $tmpPeptideVal['Sequence']);
          array_push($tmpReverseArr, $tmpPeptideVal['Sequence']);
        }
      }
      if($SearchEngine != 'TPP'){
        if(!array_key_exists($tmpPeptideVal['Sequence'], $evidence_num_arr)){
          $evidence_num_arr[$tmpPeptideVal['Sequence']] = 1;
        }else{
          $evidence_num_arr[$tmpPeptideVal['Sequence']]++;
        }
      }
       
      if(!array_key_exists($tmpPeptideVal['Sequence'], $peptideArr)){
        $peptideArr[$tmpPeptideVal['Sequence']] = $tmpPeptideVal;
      }else{
        if($SearchEngine == 'TPP'){
          $peptideArr[$tmpPeptideVal['Sequence']]['Total instance'] += $tmpPeptideVal['Total instance'];
        }  
      }
    }
    if($asc_desc == 'ASC'){
      $tmpReverseArr = array_reverse($tmpReverseArr);
      foreach($tmpReverseArr as $value){
        array_push($peptideIndexArr2, $value);
      }  
    }       
    foreach($peptideArr as $peptideKey => $peptideVal){
      if($SearchEngine != 'TPP'){
        $peptideArr[$peptideKey]['Total instance'] = $evidence_num_arr[$peptideKey];
        if($SearchEngine == 'Mascot'){
          if($peptideArr[$peptideKey]['Mascot Score'] > $MAX_sore) $MAX_sore = $peptideArr[$peptideKey]['Mascot Score'];
        }elseif($SearchEngine == 'SEQUEST'){
          if($peptideArr[$peptideKey]['SEQUEST Score'] > $MAX_sore) $MAX_sore = $peptideArr[$peptideKey]['SEQUEST Score'];
        }elseif($Is_geneLevel){
          if($peptideArr[$peptideKey]['Spectral Count'] > $MAX_sore) $MAX_sore = $peptideArr[$peptideKey]['Spectral Count'];
        }else{
          $tmp_sore = -1*$peptideArr[$peptideKey]['GPM Expect'];
          if($tmp_sore > $MAX_sore) $MAX_sore = $tmp_sore;
          if($tmp_sore < $MIN_sore) $MIN_sore = $tmp_sore;
        }
      }
      if($peptideArr[$peptideKey]['Total instance'] > $MAX_instance_num) $MAX_instance_num = $peptideArr[$peptideKey]['Total instance'];
    }
    $itemNameArr[$index] = $peptideArr;
  }else{  
    $itemNameArr[$index] = $tmpPeptideArr;
  }
}

function filter_option_list($frmName,$frmValue,$biggestNum=''){
  global $SearchEngine,$MIN_sore;
  $sign = '1';
  if($SearchEngine == 'TPP' && $frmName != "filter_instance"){ 
    $biggestNum = 1;
  }
  if($SearchEngine == 'GPM' && $frmName != "filter_instance"){
    $sign = -1;
  }
  $num_range = $biggestNum - $MIN_sore;
  if($num_range >= 1000){
    $numLen = 10;
  }elseif($num_range < 1000 && $num_range >= 100){
    $numLen = 5;
  }elseif($num_range <100 && $num_range >= 50){
    $numLen = 2;
  }elseif($num_range <50 && $num_range >= 10){
    $numLen = 1;
  }elseif($num_range <10 && $num_range > 1){
    if($frmName == "filter_instance"){
      $numLen = 1;
    }else{
      $numLen = 0.1;
    }  
  }elseif($num_range <=1 && $num_range >= 0.1){
    if($frmName == "filter_instance"){
      $numLen = 1;
    }else{
      $numLen = 0.01;
    }  
  }
  if($SearchEngine == 'GPM' && $frmName != "filter_instance"){
    $numbers = $MIN_sore;
    $blank_option = '';
  }else{
    $numbers = 0; 
    $blank_option = 0;
  }  
  echo "<option value=\"$blank_option\">\r\n";
  while($numbers < $biggestNum){
    $numbers += $numLen;
    $number_val = round($sign * $numbers,3);
    if($number_val == '-0') $number_val = 0;
    echo "<option value=\"$number_val\" ".((strcmp($frmValue,$number_val))?'':'selected').">".$number_val."\r\n";
    
  }
}

function get_Sequence_for_peptide($tmpProteinstr_uniq,$peptideStr,$div_id,$SearchEngine,$ModificationsStr=''){
  global $PROTEINDB;  
  $protein_id_sequence_arr = array();
  $Sequence_arr = array();
  $tmpProteinArr_uniq = explode(",", $tmpProteinstr_uniq);
  $position_arr = array();
  if($SearchEngine == "TPP"){
    $tmp_arr = preg_split('/<font color=red>\[\d+\]<\/font>/', $peptideStr);
    $total_len = 0;
    for($i=0; $i<count($tmp_arr)-1; $i++){
      $total_len += strlen($tmp_arr[$i]);
      array_push($position_arr, $total_len);
    }
    $peptideStr = preg_replace('/<font color=red>\[\d+\]<\/font>/', '', $peptideStr);
  }else{
    if($ModificationsStr){
      if($SearchEngine == "Mascot"){
        $tmp_arr =  explode(";", $ModificationsStr);
        foreach($tmp_arr as $tmp_val){
          $tmp_arr_2 = explode(",", $tmp_val);
          for($y=1;$y<count($tmp_arr_2);$y++){
            $subject = $tmp_arr_2[$y];
            $pattern = '/^[A-Z](\d+)/';
            if(preg_match($pattern, $subject, $matches)){
              array_push($position_arr, $matches[1]);
            }
          }
        }
      }elseif($SearchEngine == "GPM" || $SearchEngine == "SEQUEST"){
        $tmp_arr =  explode(",", $ModificationsStr);
        foreach($tmp_arr as $tmp_val){
          $subject = $tmp_val;
          $pattern = '/^[A-Z][ ]\[(\d+)\]/';
          if(preg_match($pattern, $subject, $matches)){
            array_push($position_arr, $matches[1]);
          }
        }
      }
    }
  }  
  foreach($tmpProteinArr_uniq as $val){
    if(!array_key_exists($val, $protein_id_sequence_arr)){
      $AccessionType = '';
      $protin_info_arr = get_protin_info($val,$AccessionType, $PROTEINDB);
      if($protin_info_arr['Sequence']){
        $protein_id_sequence_arr[$val] = $protin_info_arr['Sequence'];
      }else{
        $protin_info_arr = get_protein_from_url($val);
        if(isset($protin_info_arr['sequence']) && $protin_info_arr['sequence']){
          $protein_id_sequence_arr[$val] = $protin_info_arr['sequence'];
        }else{
          continue;
        }
      }
    }
    if(!array_key_exists($protein_id_sequence_arr[$val], $Sequence_arr)){
      $Sequence_arr[$protein_id_sequence_arr[$val]] = $val;
    }else{
      $Sequence_arr[$protein_id_sequence_arr[$val]] .= ", $val";
    }
  }  
  
  $popSequence_str = "<FONT FACE='Courier New,Courier,monospace'>";
  foreach($Sequence_arr as $tmp_key => $tmp_val){
    $popSequence_str .= "<div class='m_bg_2'>Protein ID: ".$tmp_val."</div>";
    $Sequence_str = $tmp_key;
    $lower_peptideStr = strtolower($peptideStr);
    $Sequence_str = str_replace($peptideStr, $lower_peptideStr, $Sequence_str);
    if(($SearchEngine == "GPM" || $SearchEngine == "SEQUEST") && $position_arr){
      $Sequence_gpm_arr = str_split($Sequence_str);
      $tmp_gpm_counter = 0;
      foreach($Sequence_gpm_arr as $Sequence_gpm_val){
        $tmp_gpm_counter++;
        if(ord($Sequence_gpm_val) >= 97 && ord($Sequence_gpm_val) <= 122) break;
      }
      $start_position = $tmp_gpm_counter - 1;
      for($z=0; $z<count($position_arr); $z++){
        $position_arr[$z] = $position_arr[$z] - $start_position;
      }
    }
    $Sequence_str = wordwrap($Sequence_str, 10, " ", true);
    $Sequence_str = chunk_split($Sequence_str, 55, "<BR>");
    //$patterns = array ('/([A-Z])(\d?[ ]?(<BR>)?[\d|a-z])/','/([a-z|\d][ ]?(<BR>)?\d?)([A-Z])/');
    $patterns = array ('/([A-Z])([ ]?(<BR>)?[a-z])/','/([a-z][ ]?(<BR>)?)([A-Z])/');
    $replace = array ('\1<FONT COLOR=RED>\2', '\1</FONT>\3');
    $Sequence_str = preg_replace($patterns, $replace, $Sequence_str);
    $Sequence_split_arr = str_split($Sequence_str);
    $Sequence_str = '';
    $tmp_counter = 0;
    foreach($Sequence_split_arr as $Sequence_split_val){
      if(ord($Sequence_split_val) >= 97 && ord($Sequence_split_val) <= 122){
        $tmp_counter++;
        if(in_array($tmp_counter, $position_arr)) {
          $Sequence_str .= "<span class='m_bg'>".$Sequence_split_val."</span>";
        }else{
          $Sequence_str .= $Sequence_split_val;
        }
      }else{
        $Sequence_str .= $Sequence_split_val;
      }
    }
    $Sequence_str = strtoupper($Sequence_str);     
    $popSequence_str .= $Sequence_str."<BR>";
  }  
  $popSequence_str .= "</FONT>";
?> 
    <table align="center" cellspacing="0" cellpadding="0" border="0" width=100% bgcolor="#ffffff">
      <tr>
        <td valign="bottem" align="left">&nbsp;<font color="#818181" face="helvetica,arial,futura" size="2">&nbsp;&nbsp;&nbsp;</font>
        <td valign="bottem" align="right">
          <a href="javascript: hideTip('<?php echo $div_id;?>');"><img border="0" src="images/icon_remove.gif" alt="Close"></a>&nbsp;&nbsp;
        </td>
      </tr>
      <tr ><td colspan="2">
      <div class=maintext>
        <?php echo $popSequence_str;?>
      </div>
      </td></tr>
    </table>
<?php               
} 
?>