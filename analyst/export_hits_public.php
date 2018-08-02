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
set_time_limit(2400);
$log_dir = "../TMP/version_export";
$mapfileDelimit = ',';
//passed variables;
$frm_selected_list_str = '';
$SearchEngine = '';
$currentType = '';
$public = '';-
$filter_str = '';
$saint_ID = 0;

$frm_SearchEngine = '';

//filter
$frm_apply_filter = '';
$frm_filter_Expect = '';
$frm_filter_Coverage = '';
$frm_filter_Peptide = '';
$frm_filter_Peptide_value = '';
$frm_filter_Fequency = '';
$frm_filter_Fequency_value = '';
$frm_NS_group_id = '';
$frm_min_XPRESS = '';
$frm_max_XPRESS = '';

$frm_NS = '';
$frm_NS_group_id = '';
$frm_CO = '';
$frm_SO = '';
$frm_AW = '';
$frm_HS = '';
$frm_RP = '';
$frm_CP = '';
$frm_BT = '';
$frm_KT = '';
$frm_AT = '';
$frm_TE = '';
$frm_DB = '';
$frm_NP = '';
$frm_HT = '';
$frm_AL = '';

//============
$ant_ProhitsInstitute = '';
$ant_ProhitsAddress = '';
$ant_pubTitle = '';
$ant_pubJournal = '';
$ant_pubFirstAuthor = '';
$ant_pubAuthorList = '';
$ant_pubContactEmail = '';
$ant_isPublished = '';
$ant_pubYear = '';
$ant_pubmedID = '';
$ant_hostOrganism = '';
$ant_hostCellType = '';
$ant_interMethod = '';
$ant_protDB_str = '';
$ant_dbVersion = '';
$ant_isPublished = '';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
include("analyst/comparison_common_functions.php");
require_once("msManager/is_dir_file.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

if($type == 'Band'){
  $type = 'Sample';
}

if(isset($DIAUmpireQuant_ID)){
  $saint_ID = $DIAUmpireQuant_ID;
}

$export_dir_path = get_writable_dir_path(STORAGE_FOLDER."Prohits_Data/export");
if(!$export_dir_path){
  echo "Error: Permission denied to creade directory '$export_dir_path'. Please contact Prohits administrator";
  exit;
}
if($theaction == 'download_zip_file'){
  if(_is_file($zip_file_path)){
    header("Cache-Control: public, must-revalidate");
    //header("Pragma: hack");
    //header("Content-Type: text/xml"); 
    header("Content-Type: application/octet-stream");  //download-to-disk dialog
    header("Content-Disposition: attachment; filename=".basename($zip_file_path).";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: "._filesize($zip_file_path));
    $ret = readfile("$zip_file_path");
  }
  exit;
}

require("export_lable_arrs.inc.php");
include("./classes/psimi_maker_class.php"); 

$SearchEngineConfig_arr = get_project_SearchEngine();
  
if(!$saint_ID){
  if(!$frm_selected_list_str or !$SearchEngine or !$type) {
    echo "Error: no enough information passed";
    exit;
  }
  if($theaction == 'generate_map_file'){
    $subDir = strtolower($type);
    $outDir_map = "../TMP/".$subDir."_report/";
    if(!_is_dir($outDir_map)) _mkdir_path($outDir_map);
    include("./export_generate_map_file_inc.php");
  }
}
$unkown_db_bait_proteins_arr = array();
$unkown_db_prey_proteins_arr = array();

//echo "infile=$infile<br>";
?>
<html>
<head>
<title>Prohits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<STYLE type="text/css">
TD { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
</STYLE>
<!--script language="Javascript" src="site_no_right_click.inc.js"></script-->
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language="JavaScript">
function checkForm(theForm){
  var sel = theForm.ant_hostOrganism;
  theForm.ant_lableOrganism.value = sel[sel.selectedIndex].text;
  if(isEmptyStr(theForm.ant_ProhitsInstitute.value)){
    alert('Please input your institute name.');return;  
  }else if(isEmptyStr(theForm.ant_ProhitsAddress.value)){
    alert('Please input your institute address.');return; 
  }else if(isEmptyStr(theForm.ant_pubTitle.value)){
    alert('Please input the publication title.');return;   
  }else if(isEmptyStr(theForm.ant_pubJournal.value)){
    alert('Please input publication journal name.');return;   
  }else if(isEmptyStr(theForm.ant_pubFirstAuthor.value)){
    alert('Please input the first author.');return;   
  }else if(isEmptyStr(theForm.ant_pubAuthorList.value)){
    alert('Please input the author list.');return;   
  }else if(isEmptyStr(theForm.ant_pubContactEmail.value)){
    alert('Please input contact email.');return;   
  }
  if(theForm.ant_isPublished.checked){
    if(isEmptyStr(theForm.ant_pubYear.value)){
      alert('Please input published year.');return;
    }  
    if(isEmptyStr(theForm.ant_pubmedID.value)){
      alert('Please input PubMed number.');return;
    } 
  }
  var o_sel = theForm.ant_hostOrganism;
  if(o_sel[o_sel.selectedIndex].value==''){
    alert('Please select a host organism.');return;
  }
  var i_sel = theForm.ant_interMethod; 
  if(i_sel[i_sel.selectedIndex].value==''){
    alert('Please select a interaction detection method.');return;
  }
  var d_sel = theForm.ant_protDB_str; 
  if(d_sel[d_sel.selectedIndex].value==''){
    alert('Please select a database.');return;
  }
  theForm.theaction.value = "generate_zip_file";
  theForm.submit();
}
function resetForm(){
  var x=document.getElementById("pubYear");
  x.style.display = "none";
}
</script>
</head>
<body>
<center>
<FORM ACTION="<?php echo $_SERVER['PHP_SELF'];?>" ID="" NAME="generate_report_form" METHOD="POST">
<input TYPE="hidden" NAME="frm_selected_list_str" VALUE="<?php echo $frm_selected_list_str;?>">
<input TYPE="hidden" NAME="SearchEngine" VALUE="<?php echo $SearchEngine?>">
<input TYPE="hidden" NAME="type" VALUE="<?php echo $type?>">
<input TYPE="hidden" NAME="public" VALUE="<?php echo $public?>">
<input TYPE="hidden" NAME="infile" VALUE="<?php echo $infile?>">
<input TYPE="hidden" NAME="ant_lableOrganism" VALUE="">
<input TYPE="hidden" NAME="frm_apply_filter" VALUE="<?php echo $frm_apply_filter?>">
<input TYPE="hidden" NAME="frm_SearchEngine" VALUE="<?php echo $frm_SearchEngine?>">
<input TYPE="hidden" NAME="theaction" VALUE="">
<input TYPE="hidden" NAME="saint_ID" VALUE="<?php echo $saint_ID?>">
<?php 
if(isset($frm_apply_filter)){
  foreach($request_arr as $key=>$value){
    if(preg_match("/^frm_/", $key, $matches))
    echo "\n<input TYPE='hidden' NAME='$key' VALUE='$value'>";
  }
}
if($ant_isPublished){
  $ci_Display = "Display:block";
}else{             
  $ci_Display = "Display:none";
}    
?>
<table border=0 width=95% cellspacing="0" cellpadding=0 align=center>
  <tr>
    <td colspan='2' nowrap >&nbsp;&nbsp;</td>
  </tr>
  <tr>
    <td nowrap align="<?php echo $tmp_align?>" height='25'>
     <?php 
      $tmp_lable = "Export Bait-Hits Report"; 
      if($public == 'IntAct'){
        $tmp_lable = "Export interaction data in PSI-MI XML v2.5 format <br><img src='./images/imex_logo.jpg' border=0>&nbsp; &nbsp;  <img src='./images/intact-logo.png' alt='' border='0'>";
      }else if($public == 'BioGRID_Tab'){
        $tmp_lable = "Export interaction data in MITAB format <br><img src='./images/gridsmall.jpg' alt='' border='0'>";
      }
      ?>
      <span class=pop_header_text><?php echo $tmp_lable;?> </span>  <font size='3'>(<?php echo $AccessProjectName;?>)</font>
    </td>
  </tr>
  <tr>
    <td nowrap align=center height='1'><hr size=1>
    
    </td>
  </tr>
  <tr>
    <td bgcolor=#a9a9a9 align=center>
       <table cellspacing="1" cellpadding="2" border="0" with=100%>
          <tr bgcolor=white>
              <td rowspan="2" bgcolor=#6699cc valign=top><font color="#FFFFFF"><b>Your Institute</b></font></td>
              <td align=right><b>Name</b></td>
              <td><input id='test' type="text" name="ant_ProhitsInstitute" size="70" value='<?php echo $ant_ProhitsInstitute?>' maxlength="100"></td>
          </tr>
          <tr bgcolor=white>
              <td align=right><b>Address</b></td>
              <td><input type="text" name="ant_ProhitsAddress" size="70" value='<?php echo $ant_ProhitsAddress?>' maxlength="200"></td>
          </tr>
          <tr bgcolor=white>
              <td rowspan="6" bgcolor=#6699cc valign=top><font color="#FFFFFF"><b>Publication</b></font></td>
              <td align=right><b>Title</b></td>
              <td><input type="text" name="ant_pubTitle" size="70"  value='<?php echo $ant_pubTitle?>'  maxlength="300"></td>
          </tr>
          <tr bgcolor=white>
              <td align=right><b>Journal Name</b></td>
              <td><input type="text" name="ant_pubJournal" size="70"  value='<?php echo $ant_pubJournal?>' maxlength="300"></td>
          </tr>
          <tr bgcolor=white>
              <td align=right><b>First Author</b></td>
              <td><input type="text" name="ant_pubFirstAuthor" size="70"  value='<?php echo $ant_pubFirstAuthor?>' maxlength="300"></td>
          </tr>
          <tr bgcolor=white>
              <td align=right><b>Author List</b></td>
              <td><input type="text" name="ant_pubAuthorList" size="70"  value='<?php echo $ant_pubAuthorList?>' maxlength="300"></td>
          </tr>
          <tr bgcolor=white>
              <td align=right><b>Contact Email</b></td>
              <td><input type="text" name="ant_pubContactEmail" size="70"  value='<?php echo $ant_pubContactEmail?>' maxlength="300"></td>
          </tr>
          <tr bgcolor=white>
              <td align=right valign>Published</td>
              <td>
              <input id="c1" type="checkbox" name="ant_isPublished" value='y' <?php echo ($ant_isPublished)?'checked':'';?> onClick=showhide('pubYear','')>
              <DIV ID='pubYear' STYLE="<?php echo $ci_Display?>">
                  Published Year <input type="text" name="ant_pubYear" size="4"  value='<?php echo $ant_pubYear?>' maxlength="4">
                  PubMed # <input type="text" name="ant_pubmedID" size="10"  value='<?php echo $ant_pubmedID?>' maxlength="50">
              </DIV>
              </td>
          </tr>
           
          <tr bgcolor=white>
              <td rowspan="2" bgcolor=#6699cc valign=top><font color="#FFFFFF"><b>Experiment</b></font></td>
              <td align=right valign=top><b>Host Organism</b></td>
              <td>
                <select id="s1" name="ant_hostOrganism">
                  <option value="">--Choose a Organism--
                  <?php  
              			TaxID_list_($mainDB, $ant_hostOrganism);
                  ?>
                </select><br>
                NCBI taxid for the Host Organism in which the interaction took place.<br>
                <select id="s2" name="ant_hostCellType">
                  <option value="">--Choose a tissue or cell lines--
                  <script src="../common/javascript/psi_cellType.js"></script>
                </select><br>
                Details of tissue or cell lines may be added as option.
              </td>
          </tr>
          <tr bgcolor=white>
              <td align=right ><b>Interaction detection Method</b></td>
              <td>
                <select id="s3" name="ant_interMethod">
                  <script src="../common/javascript/psi_interactionDetection.js"></script>
                </select>
              </td>
          </tr>
          <tr bgcolor=white>
              <td valign=top bgcolor=#6699cc><font color="#FFFFFF"><b><b>Searched Database</b></b></font></td>
              <td align=right>Database Name</td>
              <td>
                <select id="s4" name="ant_protDB_str">
                  <script src="../common/javascript/psi_proteinDatabase.js"></script>
                </select>
                Database version: <input type="text" name="ant_dbVersion" size="5" value='<?php echo $ant_dbVersion?>' maxlength="10"> e.g., V_33 or V33
              </td>
          </tr>
        </table>
    </td>
  </tr>
<?php if($theaction == "generate_map_file"){
    if($public == 'IntAct'){
      $tmp_lable = "Generate PSI-MI XML File";
    }else if($public == 'BioGRID_Tab'){
      $tmp_lable = "Generate MITAB File";
    }

?> 
  <tr>
    <td align=center><br>
        <input type="reset" onClick="resetForm();"> &nbsp; &nbsp; <input type="button" value='<?php echo $tmp_lable;?>' onClick="checkForm(this.form)">  
    </td>
  </tr>
<?php }?>  
</table>
<?php 
if($theaction == 'generate_zip_file'){
$psi_proteinDB_arr = get_psi_proteinDB_arr();
?>
<script language='javascript'>
  var x=document.getElementById("s1");
  for(i=0;i<x.length;i++){
    if(x.options[i].value == '<?php echo $ant_hostOrganism?>'){
      x.options[i].selected = true;
      break;    
    }
  }
  var x=document.getElementById("s2");
  for(i=0;i<x.length;i++){
    if(x.options[i].value == '<?php echo $ant_hostCellType?>'){
      x.options[i].selected = true;
      break;    
    }
  }
  var x=document.getElementById("s3");
  for(i=0;i<x.length;i++){
    if(x.options[i].value == '<?php echo $ant_interMethod?>'){
      x.options[i].selected = true;
      break;    
    }
  }
  var x=document.getElementById("s4");
  for(i=0;i<x.length;i++){
    if(x.options[i].value == '<?php echo $ant_protDB_str?>'){
      x.options[i].selected = true;
      break;    
    }
  }
</script>
<?php 
  echo "<div ID='process' style='display:block'><img src='./images/process.gif' border=0></div>\n";
  ob_flush();
  flush();
  $proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
  $handle_write = '';
  $handle_read = '';
  $filename_out = '';
  $handle_log = '';
  
  $file_tmp = "P".$AccessProjectID."_".$public."_".@date('Y-m-d-His');
  $xml_file_path = $export_dir_path ."/". $file_tmp ."_mif253.xml";
//echo "\$xml_file_path=$xml_file_path<br>";
  $txt_file_path = $export_dir_path ."/". $file_tmp ."_mif253.txt";
  if(!_is_dir($log_dir)) _mkdir_path($log_dir, 0766, true);
  $log_file = $log_dir . "/".  $file_tmp."_log.csv";  
  
  $map_file = $infile;
//echo "\$map_file=$map_file<br>";  
//exit;  
  if(!$handle_read = fopen($map_file, "r")){
    echo "cannot open file $map_file to read.";
    exit;
  }
  export_filter_info($handle_read);
  
  //======================================================================================
  $selectedItemArr = array('Bait');
  //Protein Gene ID,Protein Gene Name,Protein Acc
  if($saint_ID){
    $bait_index_arr = array(0,1,2,3,4);
  }else{
    $bait_index_arr = array(2,4,3,0,1); //2=Bait_Gene_ID,4=Bait_Gene_Name,3=Bait_protein_ID,0=Bait_ID,1=Bait_Tax_ID
  }
  
  if($saint_ID){
    $hit_index_arr = array(0,1,2,3,4,5);
  }else{
    if(strstr($SearchEngine, 'TPP')){
      //$hit_index_arr = array(3,4,1,0,11); //3=Protein_Gene_ID,4=Protein_Gene_Name,1=Protein_ID,11=Unique_Number_Peptide
      $hit_index_arr = array(3,1,4,0,11); //3=Protein_Gene_ID,1=Protein_Gene_Name,4=Protein_ID,0==TppID,11=Unique_Number_Peptide
    }elseif(stristr($SearchEngine, 'GeneLevel')){
      $hit_index_arr = array(1,2,1,0,5,6); //1=Hit_GeneID,2=Hit_Gene_Name,4=Hit_Protein_ID,0=Hit_ID,5=Unique_Peptide_Number,6=Sub sumed
    }else{    
      $hit_index_arr = array(1,3,4,0,11,6); //1=Hit_GeneID,3=Hit_Gene_Name,4=Hit_Protein_ID,0=Hit_ID,11=Unique_Peptide_Number,6=Redundant_GI
    }
  }  
  $Bait_attr_arr = array();
  $Bait_Hits_edge_arr = array();
  $Hits_node_arr = array();
  $previewArr = array();
  if($handle_read){
    $interactorIndex = 0;
    $interactionIndex = 0;
    while (!feof($handle_read)){
      $buffer = fgets($handle_read);
      $buffer = trim($buffer);
//echo "<br>\$buffer=$buffer<br><br>";
      if(!$buffer) continue;
      if(preg_match("/^(\w+)::(.+)/i", $buffer, $matches)){
        if(!in_array($matches[1], $selectedItemArr)) continue;
        $tmpArr1 = explode($mapfileDelimit,$matches[2]);
/*echo "<pre>";        
print_r($tmpArr1);        
echo "<pre>";*/
        $valueArr = array();
        foreach($tmpArr1 as $tmpVal1){
          $tmpArr2 = explode('===',$tmpVal1);
          array_push($valueArr, $tmpArr2[1]);
        }
        $Bait_ID = $valueArr[$bait_index_arr[3]];
        $Bait_Gene_ID = $valueArr[$bait_index_arr[0]];
        $Bait_Tax_ID = $valueArr[$bait_index_arr[4]];
        if(is_numeric($Bait_Tax_ID)){
          $Bait_Tax_Name = get_Species_from_proteinDB($proteinDB, $Bait_Tax_ID);
        }else{
          $Bait_Tax_ID = 99999;
          $Bait_Tax_Name = 'no tax name';
        }        
        if(!$Bait_Gene_ID) continue;
        
        $Bait_Gene_Name = get_geneName($HITSDB, $Bait_Gene_ID);
        $Bait_Protein_ID = $valueArr[$bait_index_arr[2]];
        $Bait_Protein_ID = str_ireplace("IPI:", "", $Bait_Protein_ID);
        
        $protDB = get_protein_ID_type($Bait_Protein_ID);
        $tmp_protein_type = 'Bait';
        $proDB_short = get_short($protDB,$Bait_Protein_ID);       
        
        if($proDB_short == 'unkown db') continue;
        if(array_key_exists($Bait_ID, $Bait_attr_arr)) continue;
        
        $Bait_attr_arr[$Bait_ID] = array();
        array_push($Bait_attr_arr[$Bait_ID], ++$interactorIndex, $Bait_Gene_Name, $Bait_Gene_ID, $Bait_Protein_ID,$Bait_ID,$Bait_Tax_ID,$Bait_Tax_Name);
        $Bait_Hits_edge_arr[$Bait_ID] = array();
/*echo "<pre>";
print_r($Bait_attr_arr);      
echo "<pre>";*/
      }else{
        if(!$Bait_Gene_ID || $proDB_short == 'unkown db') continue;
        if(!$level3Arr = explode($mapfileDelimit,$buffer)) continue;                
        $Hit_Gene_ID = $level3Arr[$hit_index_arr[0]];
        $Hit_Gene_Name = $level3Arr[$hit_index_arr[1]];
        $Hit_Protein_ID = $level3Arr[$hit_index_arr[2]];
        
        $Hit_Protein_ID = str_ireplace("IPI:", "", $Hit_Protein_ID);
        
        $protDB = get_protein_ID_type($Hit_Protein_ID);
        $tmp_protein_type = 'Prey';
        $proDB_short = get_short($protDB,$Hit_Protein_ID);
        
        if($proDB_short == 'unkown db') continue;        
        
        $Hit_ID = $level3Arr[$hit_index_arr[3]];
        $Unique_Peptide_Number = $level3Arr[$hit_index_arr[4]];
        $Redundant_GI = '';
        if(!strstr($SearchEngine, 'TPP')) $Redundant_GI = $level3Arr[$hit_index_arr[5]];
        if($Hit_Protein_ID == $Bait_Protein_ID)  continue;
        if(!array_key_exists($Hit_Protein_ID, $Hits_node_arr)){
          $Hits_node_arr[$Hit_Protein_ID] = array();
          $Hits_Tax_ID = get_tax_id($Hit_Gene_ID);
          if(is_numeric($Hits_Tax_ID)){
            $Hits_Tax_Name = get_Species_from_proteinDB($proteinDB, $Hits_Tax_ID);
          }else{
            $Hits_Tax_ID = 99999;
            $Hits_Tax_Name = 'no tax name';
          }
          array_push($Hits_node_arr[$Hit_Protein_ID], ++$interactorIndex,$Hit_Gene_Name,$Hit_Gene_ID,'','',$Hits_Tax_ID,$Hits_Tax_Name);
          $tmp_interactorIndex = $interactorIndex;
        }else{
          $tmp_interactorIndex = $Hits_node_arr[$Hit_Protein_ID][0];
        }
        if(array_key_exists($Hit_Protein_ID, $Bait_Hits_edge_arr[$Bait_ID])){
          if($Bait_Hits_edge_arr[$Bait_ID][$Hit_Protein_ID][2] < $Unique_Peptide_Number){
            $Bait_Hits_edge_arr[$Bait_ID][$Hit_Protein_ID][2] = $Unique_Peptide_Number;
            $Bait_Hits_edge_arr[$Bait_ID][$Hit_Protein_ID][4] = $Redundant_GI;
          }
        }else{
          $Bait_Hits_edge_arr[$Bait_ID][$Hit_Protein_ID] = array();
          array_push($Bait_Hits_edge_arr[$Bait_ID][$Hit_Protein_ID], $tmp_interactorIndex, $Hit_ID,$Unique_Peptide_Number,$Hit_Gene_Name,$Redundant_GI,$Hit_Gene_ID,++$interactionIndex);
        }
      }
    }
  }
  fclose($handle_read);
  
//==================================================================  
  $is_no_hits = true;
  foreach($Bait_Hits_edge_arr as $Hits_arr){
    if($Hits_arr){
      $is_no_hits = false;
    }
  }
  
  if($is_no_hits){
    echo "<font color=red>No any record can be export.</font>";
  ?>
  <input type=button value=' Close ' onClick='javascript: window.close();' class=black_but>
  <script language='javascript'>
    document.getElementById('process').style.display = 'none';
  </script>
  
  <?php 
    exit;
  }
//======================================================================
  
  if(!$handle_log = fopen($log_file, "w")){
    echo "cannot open file $handle_version_log";
    exit;
  }
  if($public == 'IntAct'){
    $Exported_file_name = $xml_file_path;
  }else{
    $Exported_file_name = $txt_file_path;
  }  
  $log_title_line = "Exported file name: $Exported_file_name\r\n\r\n";
  fwrite($handle_log, $log_title_line);
  
  $log_line = "Arguments used:\r\n";
  foreach($request_arr as $key=>$value){
    $log_line .= "'$key=>$value';\r\n";
  }
  $log_line = str_replace("C_FFFFFF:", "", $log_line);
  $log_line = str_replace(",", ";", $log_line);
  $log_line .= "\r\n";
  fwrite($handle_log, $log_line);
  
  $log_title_line = "Exported by: ".$_SESSION['USER']->Fname." ".$_SESSION['USER']->Lname;
  fwrite($handle_log, $log_title_line."\r\n\r\n");
      
  $log_title_line = "Bait ID\tBait Gene ID\tBait Gene Name";
  fwrite($handle_log, $log_title_line."\r\n");
  
  if(!$handle_write = fopen($Exported_file_name, "w")){
    echo "cannot open file $filename_out to write.";
    exit;
  }  
  if($public == 'IntAct'){
    $ant_hostCellType = 'abc cell type';
    $psi = new psimi_maker(1);
    fwrite($handle_write, $psi->makeEntrySet());
    $source_array = array(
      'ProhitsVersion'=>'V1.0.1',
      'ProhitsReleaseDate'=>@date('Y-m-d'),
      'ProhitsLable'=>'Prohits',
      'ProhitsInstitute'=>$ant_ProhitsInstitute,
      'ProhitsAddress'=>$ant_ProhitsAddress,
      'ProhitsAdminEmail'=>$ant_pubContactEmail //======================================
    );
    fwrite($handle_write, $psi->makeSource($source_array));
    fwrite($handle_write, $psi->addExperiment());
    
    $interMethodLabel = '';
    $interMethodFullName = '';
    $interMethod_miID = '';
    if(preg_match("/(MI:\d+)?\s+(.+)?\((.+)?\)/i", $ant_interMethod, $matches)){
      $interMethod_miID = trim($matches[1]);
      $interMethodLabel = trim($matches[3]);
      $interMethodFullName = trim($matches[2]);
    }elseif(preg_match("/(MI:\d+)?\s+(.+)/i", $ant_interMethod, $matches)){
      $interMethod_miID = trim($matches[1]);
      $interMethodFullName = $interMethodLabel = trim($matches[2]);
    }
    
    $hostTaxId = $ant_hostOrganism;
    $hostOrganismLable = '';
    $hostOrganismFullName = '';   
    if(preg_match("/(.+)?\((.+)?\)/i", $ant_lableOrganism, $matches)){
      $hostOrganismLable = trim($matches[2]);
      $hostOrganismFullName = trim($matches[1]);
    }else{
      $hostOrganismLable = $hostOrganismFullName = $ant_lableOrganism;
    }    
    $exp_array = array(
      'ID'=>1,
      'pubTitle'=>$ant_pubTitle,
      'pubJournal'=>$ant_pubJournal,
      'pubmedID'=>$ant_pubmedID,
      'pubFirstAuthor'=>$ant_pubFirstAuthor,
      'pubAuthorList'=>$ant_pubAuthorList,
      'pubYear'=>$ant_pubYear,
      'pubContactEmail'=>$ant_pubContactEmail,
      'hostTaxId'=>$hostTaxId,
      'hostOrganismLable'=>$hostOrganismLable,
      'hostOrganismFullName'=>$hostOrganismFullName,
      'interMethodLabel'=>$interMethodLabel,
      'interMethodFullName'=>$interMethodFullName,
      'interMethod_miID'=>$interMethod_miID,
      'prohitsProjectID'=>$AccessProjectID
    );
    
    fwrite($handle_write, $psi->addExperimentDescription($exp_array));
    fwrite($handle_write, $psi->addExperiment(0));
    fwrite($handle_write, $psi->addInteractorList());
    foreach($Bait_attr_arr as $bait_id => $attr_arr){
      $protRole = "Bait";
      $protID = $attr_arr[3];
      $other_attr_arr = $attr_arr;
      
      $intActor_array = get_note_attr($protRole,$protID,$other_attr_arr);
      fwrite($handle_write, $psi->addInteractor($intActor_array));
      $log_line = $bait_id.",".$other_attr_arr[2].",".$other_attr_arr[1]."\r\n";
      fwrite($handle_log, $log_line);
    }
    foreach($Hits_node_arr as $hit_pid => $hits_attr_arr){
      $protRole = "";
      $protID = $hit_pid;
      $other_attr_arr = $hits_attr_arr;
      $intActor_array = get_note_attr($protRole,$protID,$other_attr_arr);
      fwrite($handle_write, $psi->addInteractor($intActor_array));
    }
    
    fwrite($handle_write, $psi->addInteractorList(0));
    fwrite($handle_write, $psi->addInteractionList());
    foreach($Bait_Hits_edge_arr as $bait_id => $hits_pid_arr){
      //$baitID = $bait_id;
      $baitIntActorIndex = $Bait_attr_arr[$bait_id][0];      
      foreach($hits_pid_arr as $hit_pid => $hits_attr_arr){


      
/*array_push($Bait_Hits_edge_arr[$Bait_ID][$Hit_Protein_ID], 
$tmp_interactorIndex, 0
$Hit_ID, 1
$Unique_Peptide_Number, 2
$Hit_Gene_Name, 3
$Redundant_GI, 4
$Hit_Gene_ID, 5
++$interactionIndex); 6 */    
      
      
      
        $IntActionIndex = $hits_attr_arr[6];
        $hitIntActorIndex = $hits_attr_arr[0];
        $prohitshitID = $hits_attr_arr[1];
        $bait_hit = $Bait_attr_arr[$bait_id][1].'_'.$bait_id.'-'.$hits_attr_arr[3];
        $intAct_array = array(
          'bait_hit'=>$bait_hit,
          'IntActionIndex'=>$IntActionIndex,
          'baitIntActorIndex'=>$baitIntActorIndex,
          'hitIntActorIndex'=>$hitIntActorIndex,
          'prohitshitID'=>$prohitshitID
        );
        fwrite($handle_write, $psi->addInteraction($intAct_array));
      }
    }
    fwrite($handle_write, $psi->addInteractionList(0));
    fwrite($handle_write, $psi->makeEntrySet(0));
    
  }elseif($public = 'BioGRID_Tab'){ 
  
    $line = "#####################################################################################################\r\n";
    fwrite($handle_write, $line);
    $line = "#Institute name:\t\t$ant_ProhitsInstitute\r\n";
    fwrite($handle_write, $line);
    $line = "#Institute address:\t\t$ant_ProhitsAddress\r\n#\r\n";
    fwrite($handle_write, $line);
    $line = "#*****Publication*****\r\n";
    fwrite($handle_write, $line);
    $line = "#Title:\t\t\t\t$ant_pubTitle\r\n";
    fwrite($handle_write, $line);
    $line = "#Journal name:\t\t\t$ant_pubJournal\r\n";
    fwrite($handle_write, $line);
    $line = "#First author:\t\t\t$ant_pubFirstAuthor\r\n";
    fwrite($handle_write, $line);
    $line = "#Author List:\t\t\t$ant_pubAuthorList\r\n";
    fwrite($handle_write, $line);
    $line = "#Contact email:\t\t\t$ant_pubContactEmail\r\n";
    fwrite($handle_write, $line);
    if($ant_isPublished){
      $line = "#Published year:\t\t$ant_pubYear\r\n";
      fwrite($handle_write, $line);
    }    
    $line = "#\r\n#*****Experiment*****\r\n"; 
    fwrite($handle_write, $line);   
    $Host_Organism_arr = get_TaxID_Name_Pair($HITSDB);
    if(array_key_exists($ant_hostOrganism, $Host_Organism_arr)){
      $Host_Organism = $Host_Organism_arr[$ant_hostOrganism]."($ant_hostOrganism)";
      $line = "#Host organism:\t\t\t$Host_Organism\r\n";
    }else{
      $line = "#Host organism:\t\t\t$Host_Organism\r\n";  
    }
    fwrite($handle_write, $line);
    if($ant_hostCellType){
      $line = "#Details of tissue or cell:\t$ant_hostCellType\r\n";
      fwrite($handle_write, $line);
    }
    $line = "#Interaction detection method:\t$ant_interMethod\r\n#\r\n";
    fwrite($handle_write, $line);
    $line = "#*****Searched database*****\r\n";
    fwrite($handle_write, $line);
    $line = "#Database name:\t\t\t$ant_protDB_str\r\n";
    fwrite($handle_write, $line);
    $line = "#Database version:\t\t$ant_dbVersion\r\n#\r\n";
    fwrite($handle_write, $line);
    $line = "#----------------------------------------------------------------------------------------------------\r\n#\r\n";
    fwrite($handle_write, $line);
    $line = "#Brief Description of the Columns:\r\n#\r\n"; 
    fwrite($handle_write, $line);
    $line = "#A.) ID interactor A\t\t\tUnique ID for Interacting Partner A\r\n"; 
    fwrite($handle_write, $line);
    $line = "#B.) ID interactor B\t\t\tUnique ID for Interacting Partner B\r\n";
    fwrite($handle_write, $line);
    $line = "#C.) Alt. ID interactor A\r\n";
    fwrite($handle_write, $line);
    $line = "#D.) Alt. ID interactor B\r\n";
    fwrite($handle_write, $line);
    $line = "#E.) Aliases interactor A\t\tList of common names for geneA, separated by '|'\r\n";
    fwrite($handle_write, $line);
    $line = "#F.) Aliases interactor B\t\tList of common names for geneB, separated by '|'\r\n";
    fwrite($handle_write, $line);
    $line = "#G.) Interaction detection methods\tSystem in which the interaction was shown\r\n";
    fwrite($handle_write, $line);
    $line = "#H.) Publication 1st author\t\tAuthor/s of the interaction\r\n";
    fwrite($handle_write, $line);
    $line = "#I.) Publication Identifier\t\tPubMed_ID of the paper, separated by ';'\r\n";
    fwrite($handle_write, $line);
    $line = "#J.) Taxid interactor A\r\n";
    fwrite($handle_write, $line);
    $line = "#K.) Taxid interactor B\r\n";
    fwrite($handle_write, $line);
    $line = "#L.) Interaction types\r\n";
    fwrite($handle_write, $line);
    $line = "#M.) Source databases\r\n";
    fwrite($handle_write, $line);
    $line = "#N.) Interaction identifier(s) \r\n";
    fwrite($handle_write, $line);
    $line = "#O.) Confidence score\r\n";
    fwrite($handle_write, $line); 
    $line = "#----------------------------------------------------------------------------------------------------\r\n#\r\n";
    fwrite($handle_write, $line);
    $line = "#File Version:\r\n";
    fwrite($handle_write, $line);
    $line = "#----------------------------------------------------------------------------------------------------\r\n#\r\n";
    fwrite($handle_write, $line);
    $line = "#####################################################################################################\r\n#\r\n";
    fwrite($handle_write, $line);
    $title_line = "#ID interactor A\tID interactor B\tAlt. ID interactor A\tAlt. ID interactor B\tAliases interactor A\tAliases interactor B\tInteraction detection methods\tPublication 1st author\tPublication Identifier\tTaxid interactor A\tTaxid interactor B\tInteraction types\tSource databases\tInteraction identifier(s)\tConfidence score\r\n";
    fwrite($handle_write, $title_line);
    
    foreach($Bait_Hits_edge_arr as $bait_id => $hits_pid_arr){
      $bait_protein_ID = $Bait_attr_arr[$bait_id][3];
      if(!$bait_protein_ID) $bait_protein_ID = '-';
      $tmp_protein_type = 'Bait';
      $bait_proDB_short = get_pro_ID_DB_short($bait_protein_ID);
      $bait_Gene_name = $Bait_attr_arr[$bait_id][1];
      $bait_Gene_ID = $Bait_attr_arr[$bait_id][2];
      
      if(!$bait_Gene_name) $bait_Gene_name = '-';
      if(!$bait_Gene_ID) $bait_Gene_ID = '-';
      
      $bait_tax_id = get_tax_id($Bait_attr_arr[$bait_id][2]);
      if($bait_tax_id != "-"){
        $bait_tax_name = get_TaxID_name($HITSDB, $bait_tax_id);
        $bait_tax_id = "taxid:".$bait_tax_id;
        $tmp_tax_arr = preg_split("/[()]/", $bait_tax_name);
        if(count($tmp_tax_arr) > 1){
          $bait_tax_id .= "(".trim($tmp_tax_arr[1]).")";
        }else{
          $bait_tax_id .= "(".trim($tmp_tax_arr[0]).")";
        }
      }      
      $log_line = $bait_id."\t".$Bait_attr_arr[$bait_id][2]."\t".$Bait_attr_arr[$bait_id][1]."\r\n";
      fwrite($handle_log, $log_line);
      if($bait_proDB_short) $bait_protein_ID = "$bait_proDB_short:$bait_protein_ID";      
      foreach($hits_pid_arr as $hit_pid => $hits_attr_arr){
        $hit_protein_ID = $hit_pid;
        $tmp_protein_type = 'Prey';
        $hit_proDB_short = get_pro_ID_DB_short($hit_protein_ID);
        if($hits_attr_arr[4]){
          $hit_Redundant_GI = preg_replace('/;\s*/', '|', $hits_attr_arr[4]);
          $hit_protein_ID = $hit_protein_ID;
        }
        
        
/*array_push($Bait_Hits_edge_arr[$Bait_ID][$Hit_Protein_ID], 
$tmp_interactorIndex, 0
$Hit_ID, 1
$Unique_Peptide_Number, 2
$Hit_Gene_Name, 3
$Redundant_GI, 4
$Hit_Gene_ID, 5
++$interactionIndex);*/ 
        
        
        $hit_Gene_name = $hits_attr_arr[3];
        $hit_Gene_ID = $hits_attr_arr[5];
        if(!$hit_Gene_name) $hit_Gene_name = '-';
        if(!$hit_Gene_ID) $hit_Gene_ID = '-';
        
        $hit_tax_id = get_tax_id($hits_attr_arr[5]);
        if($hit_tax_id != "-"){
          $hit_tax_name = get_TaxID_name($HITSDB, $hit_tax_id);
          $hit_tax_id = "taxid:".$hit_tax_id;
          $tmp_tax_arr = preg_split("/[()]/", $hit_tax_name);
          if(count($tmp_tax_arr) > 1){
            $hit_tax_id .= "(".trim($tmp_tax_arr[1]).")";
          }else{
            $hit_tax_id .= "(".trim($tmp_tax_arr[0]).")";
          }
        }
        $Interaction_types = "psi-mi:\"MI:0915\"(physical association)"; 
        if($ant_pubYear){
          $SOURCE = $ant_pubFirstAuthor."(".$ant_pubYear.")";
        }else{
          $SOURCE = $ant_pubFirstAuthor;
        }
        
        if($ant_pubmedID){
          $PUBMED_ID = "pubmed:".$ant_pubmedID;
        }else{
          $PUBMED_ID = "-";
        }
        
        $tmp_arr = explode("\t",$ant_interMethod);
        $tmp_arr2 = preg_split("/[()]/", $tmp_arr[1]);
        if(count($tmp_arr2) > 1){
          $tmp_method = "(".trim($tmp_arr2[1]).")";
        }else{
          $tmp_method = "(".trim($tmp_arr2[0]).")";
        }
        $ant_inter_method = "psi-mi:\"".trim($tmp_arr[0])."\"".$tmp_method; //."|psi-mi:\"MI:0943\"(detection by mass spectrometry)";
        
        if($hit_proDB_short) $hit_protein_ID = "$hit_proDB_short:$hit_protein_ID";
        $line = $bait_protein_ID."\t".$hit_protein_ID."\tentrez gene:".$bait_Gene_ID."\tentrez gene:".$hit_Gene_ID."\tentrez gene:".$bait_Gene_name."\tentrez gene:".$hit_Gene_name."\t".$ant_inter_method."\t".$SOURCE."\t".$PUBMED_ID."\t".$bait_tax_id."\t".$hit_tax_id."\t".$Interaction_types."\t-\t-\t-\r\n";
        fwrite($handle_write, $line);
      }
    }
  }
  if($handle_write) fclose($handle_write);
  //if($handle_log) fclose($handle_log);  
  
  $zip_file_path = $Exported_file_name.".zip";
  $zip_file_name = basename($Exported_file_name);
  $myshellcmd = "cd $export_dir_path; zip '$zip_file_name".".zip' '$zip_file_name';";
    
  $result = @exec($myshellcmd);
  if($result){
     $url = $zip_file_path;
     $size = _filesize($zip_file_path);
  }else{
    $err_msg = "ProHits can not create a zip file now. Please try it later.";
  }
  
  $Description = "Log file: $log_file";
  $SQL = "INSERT INTO `Log` SET
          `MyTable`='Export to IntAct',
          `MyAction`='Export', 
          `Description`='$Description', 
          `ProjectID`='$AccessProjectID'";
  $HITSDB->insert($SQL);
  if(count($unkown_db_prey_proteins_arr) || count($unkown_db_bait_proteins_arr)){
?>
<table border=1 width=95% cellspacing="0" cellpadding=0 align=center>
  <tr>
    <td align=left valign=bottom colspan="3">
      <font color="red">
        Some protein IDs cannot be fund PSI_MI database type. Please click [<a href='<?php echo $log_file?>' target=_new>Here</a>] for detail.
      </font>
    </td>
  </tr>
  <tr>
    <td align=left valign=bottom>Protein Type</td>
    <td align=left valign=bottom>Protein ID</td>
  </tr>
<?php 
  fwrite($handle_log, "\r\n");
  $log_line = "Some protein IDs cannot be fund PSI_MI database type blow.\r\n";
  fwrite($handle_log, $log_line);
  $log_line = "Protein Type\tProtein ID\r\n";
  fwrite($handle_log, $log_line);
  foreach($unkown_db_bait_proteins_arr as $value){
?>
  <tr>
    <td align=left valign=bottom>bait</td>
    <td align=left valign=bottom><?php echo $value;?></td>
  </tr>
<?php 
    $log_line = "bait\t$value\r\n";
    fwrite($handle_log, $log_line);
  }
  foreach($unkown_db_prey_proteins_arr as $value){
?>
  <tr>
    <td align=left valign=bottom>prey</td>
    <td align=left valign=bottom><?php echo $value;?></td>
  </tr>  
<?php 
    $log_line = "prey\t$value\r\n";
    fwrite($handle_log, $log_line);
  }
?>
</table>
<?php }?>
<table border=0>
  <tr>
    <td align=right valign=bottom><br>
      <b>File Name</b> 
    </td>
    <td align=left valign=bottom>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <?php echo basename($zip_file_path);?>
    </td>
  </tr>
  <tr>
    <td align=right valign=bottom>
      <b>File Size</b> 
    </td>
    <td align=left valign=bottom>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <?php echo round($size/1000);?> KB
    </td>
  </tr>
  <tr>
    <td align=right valign=middle>
      <b>Download</b> 
    </td>
    <td align=left valign=bottom>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <a  title='Download' href="<?php echo $_SERVER['PHP_SELF'];?>?theaction=download_zip_file&zip_file_path=<?php echo $zip_file_path?>"> <img src=./images/icon_download.gif border=0></a>
    </td>
  </tr>
</table>
<script language='javascript'>
  document.getElementById('process').style.display = 'none';
</script>
<?php 
}
?>
</form>
</center>
</body>
</html>
<?php 

function get_tax_id($geneID){
  global $proteinDB;
  $taxID = "-";
  $SQL = "SELECT `TaxID` FROM `Protein_Accession` WHERE `EntrezGeneID`='$geneID'"; 
  if($tmp_arr = $proteinDB->fetch($SQL)){
    $taxID = $tmp_arr['TaxID'];
  }
  return $taxID; 
}

function get_protein_acc($protID){
  global $proteinDB;
  if(is_numeric($protID)){
    $SQL = "SELECT `Acc_Version` FROM `Protein_Accession` WHERE `GI`='$protID'"; 
    if($tmp_arr = $proteinDB->fetch($SQL)){
      if($tmp_arr['Acc_Version']) return $tmp_arr['Acc_Version'];
    }else{
      return $protID;
    }
  }else{
    return $protID;
  }  
}

function get_note_attr($protRole,$protID,$other_attr_arr){
  global $ant_dbVersion,$proteinDB,$ant_protDB_str;
  global $psi_proteinDB_arr;
  global $unkown_db_bait_proteins_arr,$unkown_db_prey_proteins_arr;
  $baitID = '';
  if($protRole){
    $baitID = $other_attr_arr[4];
  }
  $secondProtID = '';
  $secondProtDB_short = '';
  $secondProDB_miID = '';
  $proDB_miID = '';
  $proDB_short = '';
  $is_continue = 1;
  $protDB = '';
  
  if(is_numeric($protID)){
    $SQL = "SELECT `Acc_Version` FROM `Protein_Accession` WHERE `GI`='$protID'"; 
    $tmp_arr = $proteinDB->fetch($SQL);       
    if($tmp_arr && get_protein_ID_type($tmp_arr['Acc_Version']) == 'REFSEQ'){
      $protDB = 'REFSEQ';
      $secondProtID = $protID;
      $protID = $tmp_arr['Acc_Version'];
      $secondProDB_miID = $psi_proteinDB_arr['GI']['MI'];
      $secondProtDB_short = $psi_proteinDB_arr['GI']['short'];
    }else{
      $proDB_miID = $psi_proteinDB_arr['GI']['MI'];
      $proDB_short = $psi_proteinDB_arr['GI']['short'];
      $is_continue = 0;
    }
  }else{
    $protDB = get_protein_ID_type($protID);
    if($protDB == 'UniProt') $protDB = 'uniprotkb';
  }
  if($is_continue){    
    if($protDB && isset($psi_proteinDB_arr[$protDB])){
      $proDB_miID = $psi_proteinDB_arr[$protDB]['MI'];
      $proDB_short = $psi_proteinDB_arr[$protDB]['short'];
    }else{
      $proDB_miID = '';
      $proDB_short = 'unkown db';
      if($protRole == 'Bait'){
        if(!in_array($protID, $unkown_db_bait_proteins_arr)){
          array_push($unkown_db_bait_proteins_arr, $protID);
        }
      }else{
        if(!in_array($protID, $unkown_db_prey_proteins_arr)){
          array_push($unkown_db_prey_proteins_arr, $protID);
        }
      }
      /*if(preg_match("/(MI:\d+)?\s+([\w \/-]+)?\(/i", $ant_protDB_str, $matches)){
        $proDB_miID = $matches[1];
        $proDB_short = $matches[2];
      }*/
    }
  }
  if($protDB && strstr($ant_protDB_str, "($protDB)")){
    $tmp_dbVersion = $ant_dbVersion;
  }else{
    $tmp_dbVersion = '';
  }    
  $intActor_array = array(
    'interactorIndex'=>$other_attr_arr[0],
    'baitID'=>$baitID,
    'protGeneName'=>$other_attr_arr[1],
    'dbVersion'=>$tmp_dbVersion,
    'protDB'=>$proDB_short,
    'proDB_miID'=>$proDB_miID,
    'protID'=>$protID,
    'secondProtDB'=>$secondProtDB_short,
    'secondProDB_miID'=>$secondProDB_miID,
    'secondProtID'=>$secondProtID,
    'protGeneID'=>$other_attr_arr[2],
    'taxID'=>$other_attr_arr[5],
    'taxName'=>$other_attr_arr[6]
  );
  return $intActor_array;
}

function get_pro_ID_DB_short(&$protID){
  global $proteinDB,$psi_proteinDB_arr;  
  if(is_numeric($protID)){
    $SQL = "SELECT `Acc_Version` FROM `Protein_Accession` WHERE `GI`='$protID'";
    $tmp_arr = $proteinDB->fetch($SQL);
    if($tmp_arr && $tmp_arr['Acc_Version']  == 'REFSEQ'){
      $protID = $tmp_arr['Acc_Version'];
      $protDB = 'REFSEQ';
      //$protDB = get_protein_ID_type($protID);
      $proDB_short = get_short($protDB,$protID);
    }else{
      $proDB_short = $psi_proteinDB_arr['GI']['short'];
    }
  }else{
    $protDB = get_protein_ID_type($protID);
    if($protDB == 'UniProt') $protDB = 'uniprotkb';
    $proDB_short = get_short($protDB,$protID);
  }
  return $proDB_short;
}
  
function get_short($protDB,$protID){
  global $psi_proteinDB_arr;
  global $unkown_db_bait_proteins_arr,$tmp_protein_type,$unkown_db_prey_proteins_arr;
  if($protDB && isset($psi_proteinDB_arr[$protDB])){
    $proDB_short = $psi_proteinDB_arr[$protDB]['short'];
  }else{
    //$proDB_short = get_general_proDB_short();
    $proDB_short = 'unkown db';
    if($tmp_protein_type == 'Bait'){
      if(!in_array($protID, $unkown_db_bait_proteins_arr)){
        array_push($unkown_db_bait_proteins_arr, $protID);
      }
    }elseif($tmp_protein_type == 'Prey'){
      if(!in_array($protID, $unkown_db_prey_proteins_arr)){
        array_push($unkown_db_prey_proteins_arr, $protID);
      }
    } 
  }
  return $proDB_short;
}  
    
function get_general_proDB_short(){
  global $ant_protDB_str;
  $proDB_short = '';
  if(preg_match("/(MI:\d+)?\s+([\w \/-]+)?\(/i", $ant_protDB_str, $matches)){
    $proDB_short = $matches[2];
  }
  return $proDB_short;
}   
?>
