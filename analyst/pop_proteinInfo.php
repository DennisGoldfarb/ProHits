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

$GeneID = 0;
$LocusTag = '';
$GeneName = '';
$GI = '';
$TaxID = '';
$Description = '';
$BioFilter = '';
$Acc_Version = '';
$SequenceID = '';
$return_geneID = '';
$return_gene = '';
$return_orf = '';
$return_tax = '';    
$return_species = '';

$MW = '';
$seletFlag = 0;
$sqlTaxID = '';
$from = '';
$Alias = '';
$GeneAliase = '';
$pageName = '';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");

$GeneName_o = $GeneName;
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
if($theaction == 'get_sequence_detail'){
	get_sequence_MW_div($SequenceID,$GI,$selected_div,$tmp_index);
	exit;
}elseif($theaction == 'all_species'){
  $SQL = "SELECT `TaxID` FROM `Protein_Class` WHERE `GeneName`='$GeneName' GROUP BY TaxID";
  $Protein_Class_arr = $proteinDB->fetchAll($SQL);
  $child_tax_id_arr = array();
  foreach($Protein_Class_arr as $val){
    if(!in_array($val['TaxID'], $child_tax_id_arr)){
      array_push($child_tax_id_arr,$val['TaxID']);
    }
  }
  $SQL = "SELECT `TaxID` FROM `Protein_ClassENS` WHERE `GeneName`='$GeneName' GROUP BY TaxID";
  $Protein_Class_arr = $proteinDB->fetchAll($SQL);
  foreach($Protein_Class_arr as $val){
    if($val['TaxID'] and !in_array($val['TaxID'], $child_tax_id_arr)){
      array_push($child_tax_id_arr,$val['TaxID']);
    }
  }
  if($child_tax_id_arr){
    
    $tmp_tax_id_str = implode(",", $child_tax_id_arr);
    $SQL = "SELECT T.Tax_id, 
            N.name_txt 
            FROM NCBI_tax_nodes T 
            LEFT JOIN NCBI_tax_names N ON (T.Tax_id=N.TaxID) 
            WHERE T.Tax_id IN($tmp_tax_id_str)";
    $ProteinSpecies_arr = $proteinDB->fetchAll($SQL);
    display_child($GeneName);
    exit;
  }else{
    $meg = '';
  }
}

$LocusTag = trim($LocusTag);
$GeneName = trim($GeneName);
$taxIDFamilyArr = array();

if($TaxID){
  $sqlTaxID = "TaxID=$TaxID AND";  
}

$mainSQL = "SELECT EntrezGeneID, LocusTag, GeneName, GeneAliase, TaxID, Description, BioFilter, Status FROM Protein_Class ";
if($GeneID){
  $SQL = $mainSQL . "where EntrezGeneID=$GeneID";
  $seletFlag = 1;
}elseif($LocusTag){
  $SQL = $mainSQL . "where LocusTag='$LocusTag'";
  $seletFlag = 2;
}elseif($GeneName){
  $SQL = $mainSQL . "where $sqlTaxID GeneName='$GeneName'";
  $seletFlag = 3;
}

$k = 0;
$fundFlag = 0;
$proteinArr = array();

if($seletFlag){
  $proteinArr = $proteinDB->fetchAll($SQL);
  if(count($proteinArr)){
    $fundFlag = 1;
  }else if($seletFlag == 3){
    $SQL = $mainSQL . "where $sqlTaxID (GeneAliase LIKE '%|$GeneName%' OR GeneAliase LIKE '%|$GeneName|%' OR GeneAliase LIKE '%$GeneName|%')";
    $proteinArr = $proteinDB->fetchAll($SQL);    
    if(count($proteinArr)){  
      for(; $k<count($proteinArr); $k++){                 
        $tmpArr = preg_split('/\|/', $proteinArr[$k]['GeneAliase']);
        for($m=0; $m<count($tmpArr); $m++){
          $tmpArr[$m] = strtoupper(trim($tmpArr[$m]));
        }
        $GeneName = strtoupper(trim($GeneName));
        if(in_array($GeneName, $tmpArr)){
          $fundFlag = 1;         
          break;    
        }
      }
    }else{
      $SQL = "SELECT T.Tax_id, 
              N.name_txt 
              FROM NCBI_tax_nodes T 
              LEFT JOIN NCBI_tax_names N ON (T.Tax_id=N.TaxID) 
              WHERE T.Parent_tax_id='$TaxID'";
      $ProteinSpecies_arr = $proteinDB->fetchAll($SQL);
      $Tax_id_str = '';
      foreach($ProteinSpecies_arr as $tmp_val){
        if($Tax_id_str) $Tax_id_str .= ',';
        $Tax_id_str .= $tmp_val['Tax_id'];
      }
      if($Tax_id_str){
        $SQL = "SELECT TaxID FROM Protein_Class where GeneName='$GeneName' AND TaxID IN($Tax_id_str)";
      }
      $tmp_tax_id_arr = $proteinDB->fetchAll($SQL);
      $child_tax_id_arr = array();
      foreach($tmp_tax_id_arr as $tmp_tax_id_val){
        array_push($child_tax_id_arr, $tmp_tax_id_val['TaxID']);
      }      
      if($ProteinSpecies_arr && $child_tax_id_arr){
        display_child($GeneName);          
        exit;
      }    
    }
  }  
}

$Acc_counter = 0;

$proteinAccArr = array();
if($fundFlag){
  for($k=0; $k<count($proteinArr); $k++){ 
    if(!preg_match("/^Replaced:/", $proteinArr[$k]['Status'], $matches)){
      break;
    }
  }
  $GeneID = $proteinArr[$k]['EntrezGeneID'];
  if(trim($proteinArr[$k]['LocusTag'] == "-")){
    $LocusTag = '';
  }else{
    $LocusTag = $proteinArr[$k]['LocusTag'];
  }
  $GeneName = $proteinArr[$k]['GeneName'];
  $GeneAliase = $proteinArr[$k]['GeneAliase'];  
  $Description = $proteinArr[$k]['Description'];
  $BioFilter = $proteinArr[$k]['BioFilter'];
  $TaxID = $proteinArr[$k]['TaxID'];
  $SQL = "SELECT  GI, Acc_Version, UniProtID, Description, SequenceID FROM Protein_Accession where EntrezGeneID='".$GeneID."'";
  $proteinAccArr2 = $proteinDB->fetchAll($SQL);
  
  
  if($proteinAccArr2){  
    $proteinAccArr2 = multi_sort($proteinAccArr2, $sortKey='Acc_Version');
    $tmp_arr = array();
    $findFlag2 = 0;
    $acc_name_arr = array();
     
    foreach($proteinAccArr2 as $proteinAccVal2){
      if(preg_match('/^[NXZAY]P_/', $proteinAccVal2['Acc_Version'])){
        $tmp_acc_arr = explode('.',$proteinAccVal2['Acc_Version']);
        if(!in_array($tmp_acc_arr[0], $acc_name_arr)){
          array_push($acc_name_arr, $tmp_acc_arr[0]);
        }else{
          continue;
        }
        $tmp_inner_arr['Acc_Version'] = $proteinAccVal2['Acc_Version'];
        $tmp_inner_arr['Description'] = $proteinAccVal2['Description'];
        $tmp_inner_arr['SequenceID'] = $proteinAccVal2['SequenceID'];
        $tmp_inner_arr['GI'] = $proteinAccVal2['GI'];
        array_push($proteinAccArr, $tmp_inner_arr);
      }elseif(!$findFlag2){
        if($proteinAccVal2['UniProtID']){
          $tmp_arr['Acc_Version'] = $proteinAccVal2['Acc_Version'];
          $tmp_arr['Description'] = $proteinAccVal2['Description'];
          $tmp_arr['SequenceID'] = $proteinAccVal2['SequenceID'];
          $tmp_arr['GI'] = $proteinAccVal2['GI'];
          $findFlag2 = 1;
        }  
      }
    }
    if(!count($proteinAccArr)){
      array_push($proteinAccArr, $tmp_arr);
    }
    $Acc_counter = count($proteinAccArr);
    if($Acc_counter == 1){
      $tmp_inner_arr = array();
      get_sequence_MW($proteinAccArr[0]['SequenceID'],$proteinAccArr[0]['GI'],$tmp_inner_arr);
      $proteinAccArr[0]['Sequence'] = $tmp_inner_arr['Sequence'];
      $proteinAccArr[0]['MW'] = $tmp_inner_arr['MW'];
      if($tmp_inner_arr['Description']){
        $proteinAccArr[0]['Description'] = $tmp_inner_arr['Description'];
      }
    }
  }else{    
    $MW = '';
    $fundFlag = 'helf';
  }  
}else{
  $GeneID = '';
  $LocusTag = '';
  //$GeneName = ''; 
  $GI = '';
  //$TaxID = '';
  $MW = '';
  $Description = '';
}
if($pageName){
  $formIndex = 0;
}else{
  $formIndex = 1;
}
$new_species = get_Species_from_proteinDB($proteinDB, $TaxID);
$tmp_str = "<table border='1' cellpadding='0' cellspacing='1' width='500'><tr bgcolor='#c5b781'><td colspan='2' align='center' height=20><div class=tableheader>New Selection</div></td></tr><tr bgcolor='#d2dcff'><td align='right'><div class=maintext>Name:&nbsp;</div></td><td><div class=maintext><input type='text' name='frm_Name' size='25' maxlength=15 value=''></div></td></tr></table>";
html_header();
?>
<!--script language="Javascript" src="site_no_right_click.inc.js"></script-->
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script> 
<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language='javascript'>
function passvalue(){
  var theForm = document.protein_info_frm;
  var Acc_counter = theForm.Acc_counter.value;
  var acc_index = 0;
  var checked_flag = 0;
  if(Acc_counter > 1){  
    var rad_obj = theForm.acc_rad;
    for(var i=0; i<rad_obj.length; i++){
      if(rad_obj[i].checked == true){
        acc_index = i;
        checked_flag = 1;
        break;
      } 
    }
    if(checked_flag == 0){
      alert("You must chose a Accession to pass data.");
      return;
    }
  }
  var gi_div_id = 'gi_' + acc_index;
  var gi_val = document.getElementById(gi_div_id).innerHTML;
  var Acc_div_id = 'Acc_' + acc_index;
  var Acc_val = document.getElementById(Acc_div_id).innerHTML;
  var des_div_id = 'des_' + acc_index;
  var des_val =  document.getElementById(des_div_id).innerHTML;
  if(des_val == '') des_val = "<?php echo $Description;?>";
  var MW_div_id = 'MW_' + acc_index;
  var MW_val =  document.getElementById(MW_div_id).innerHTML;
  MW_val = MW_val.replace(/KDa/i,'');
  MW_val = trim(MW_val);
  
 <?php if($return_geneID){?>
    opener.document.forms[<?php echo $formIndex;?>].<?php echo $return_geneID;?>.value = "<?php echo $GeneID;?>";
    opener.document.forms[<?php echo $formIndex;?>].<?php echo $return_orf;?>.value = "<?php echo $LocusTag;?>";    
	  opener.document.forms[<?php echo $formIndex;?>].<?php echo $return_gene;?>.value = "<?php echo $GeneName;?>";
    opener.document.forms[<?php echo $formIndex;?>].<?php echo $return_tax;?>.value = "<?php echo $TaxID;?>";    
	  opener.document.forms[<?php echo $formIndex;?>].<?php echo $return_species;?>.value = "<?php echo $new_species;?>";
    
    if(opener.document.forms[<?php echo $formIndex;?>].add_filter != undefined){
      opener.document.forms[<?php echo $formIndex;?>].add_filter.disabled = false;
    } 
    pass_species('<?php echo $formIndex;?>','<?php echo $new_species;?>','<?php echo $TaxID;?>'); 
 <?php }else{?>
    opener.document.forms[<?php echo $formIndex;?>].frm_GeneID.value = "<?php echo $GeneID;?>";
	  opener.document.forms[<?php echo $formIndex;?>].frm_LocusTag.value = "<?php echo $LocusTag;?>";
	  opener.document.forms[<?php echo $formIndex;?>].frm_GeneName.value = "<?php echo $GeneName;?>";
    pass_species('<?php echo $formIndex;?>','<?php echo $new_species;?>','<?php echo $TaxID;?>'); 
    <?php if(!$pageName){?>
      opener.document.forms[<?php echo $formIndex;?>].frm_BaitAcc.value = Acc_val;
      opener.document.forms[<?php echo $formIndex;?>].frm_BaitMW.value = MW_val;	  
  	  opener.document.forms[<?php echo $formIndex;?>].frm_Description.value = des_val;
    <?php }else{?>
      opener.document.forms[<?php echo $formIndex;?>].frm_GeneID.readOnly = true;
      opener.document.forms[<?php echo $formIndex;?>].frm_LocusTag.readOnly = true;
	    opener.document.forms[<?php echo $formIndex;?>].frm_GeneName.readOnly = true;
      opener.document.forms[<?php echo $formIndex;?>].add_filter.disabled = false;
    <?php }?>
 <?php }?>
 window.close();
}

function pass_species(formIndex,new_species,TaxID){
    
  <?php if($return_geneID){?>
    opener.document.forms[formIndex].<?php echo $return_species;?>.value = new_species;
    var sel = opener.document.forms[formIndex].<?php echo $return_tax;?>;
  <?php }else{?>
    opener.document.forms[formIndex].new_species.value = new_species;
    var sel = opener.document.forms[formIndex].frm_TaxID;
  <?php }?>  
    var sel_len = sel.length;
    var find_flag = false;
    for(var i=0; i<sel.length; i++){
      if(sel.options[i].value == TaxID){
        sel.options[i].selected = true;          
        find_flag = true;
        break;
      }  
    }
    if(!find_flag){
      var elOptNew = opener.document.createElement('option');
      elOptNew.text = new_species;
      elOptNew.value = TaxID;
      var elOptOld = sel.options[sel.selectedIndex];  
      try{
        sel.add(elOptNew, elOptOld); //standards compliant; doesn't work in IE
      }
      catch(ex){
        sel.add(elOptNew, sel.selectedIndex); //IE only
      }
      sel.options[sel.selectedIndex-1].selected = true;
   }
}
    

function get_sequence_detail(SequenceID,tmp_index,GI){
  var theForm = document.protein_info_frm;
  var selected_div = "sequence_mw_div_" + tmp_index; 
  var queryString = "SequenceID="+SequenceID+"&tmp_index="+tmp_index+"&selected_div="+selected_div+"&GI="+GI+"&theaction=get_sequence_detail";
  
  var selected_obj = document.getElementById(selected_div);
  if(selected_obj.innerHTML == ''){
    ajaxPost("<?php echo $PHP_SELF;?>", queryString);
  }
  var Acc_counter = theForm.Acc_counter.value;
  for(var i=0; i<Acc_counter; i++){
    if(i == tmp_index){
      selected_obj.style.display = "block";
    }
  }
}

function processAjaxReturn(rp){
  var ret_html_arr = rp.split("@@**@@");
  if(ret_html_arr.length == 3){
    var selected_div_id = ret_html_arr[1];
    document.getElementById(selected_div_id).innerHTML = ret_html_arr[2];
  }
}
</script>
<?php 

if(!$fundFlag){ 
  echo "<div class=maintext><font color=red><b>Protein is not found in protein database. <br> 
    Check to make sure you entered a accurate Gene ID or Locus Tag or Gene Name and choose 
    a correct Genus Species!</b></font></div> ";
}elseif($fundFlag == 'helf'){    
  echo "<div class=maintext><font color=red><b>Protein id found in Protein database. <br> 
    But sequence is not found!</b></font></div> ";
    $fundFlag = '';  
}else{
  //$taxIDFamilyArr = get_TaxID_family_from_proteinDB($proteinDB, $_SESSION["workingProjectTaxID"]);
  $is_in_species_family = is_in_species_family($proteinDB, $TaxID, $_SESSION["workingProjectTaxID"]); 
  if(!$is_in_species_family){
    $selectedSpeciesName = get_Species_from_proteinDB($proteinDB, $TaxID);
    $SpeciesName = get_Species_from_proteinDB($proteinDB, $_SESSION["workingProjectTaxID"]);
    echo "<div class=maintext><font color=red><b>The species $selectedSpeciesName which you selected is not in <br> 
    species $SpeciesName family.</b></font></div> "; 
  }elseif($is_in_species_family == "table_not_exist"){
    echo "<div class=maintext><font color=red><b><BR>&nbsp;TABLES 'NCBI_tax_nodesa' is not exists</b></font></div> "; 
  }
}    
?>
<form name="protein_info_frm">
<input type="hidden" name='Acc_counter' value='<?php echo $Acc_counter?>'>
<input type="hidden" name='return_geneID' value='<?php echo $return_geneID?>'>
<input type="hidden" name='return_gene' value='<?php echo $return_gene?>'>
<input type="hidden" name='return_orf' value='<?php echo $return_orf?>'>
<input type="hidden" name='pageName' value='<?php echo $pageName?>'>
<center>
<table border="0" cellpadding="0" cellspacing="1" width="98%">
<?php if($GeneName_o){?>
  <tr bgcolor="white">
	  <td colspan="2" align="right" height=20>
     <a href='<?php echo $PHP_SELF;?>?GeneName=<?php echo $GeneName_o?>&TaxID=<?php echo $TaxID?>&return_geneID=<?php echo $return_geneID?>&return_gene=<?php echo $return_gene?>&return_orf=<?php echo $return_orf?>&return_tax=<?php echo $return_tax?>&return_species=<?php echo $return_species?>&pageName=<?php echo $pageName?>&theaction=all_species' class=button>[Search All Species]</a>
	  </td>
	</tr>
<?php }?>  
	<tr bgcolor="#a48b59">
	  <td colspan="2" align="center" height=20>
	   <div class=tableheader>Protein Information</div>
	  </td>
	</tr>
  <tr bgcolor="#e9e1c9">
	  <td align="right" width="20%">
	   <div class=maintext><b>Gene ID:</b>&nbsp;</div>
	  </td>
	  <td width="80%"><div class=maintext>&nbsp;<?php echo $GeneID;?></div></td>
	</tr>
  <tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>Gene Name</b>:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;<?php echo ($fundFlag)?$GeneName:'';?></div></td>
	</tr>
	<tr bgcolor="#e9e1c9">
	  <td align="right" width="20%">
	   <div class=maintext><b>Gene Alias:</b>&nbsp;</div>
	  </td>
<?php if($GeneAliase == "-" || $GeneAliase == "|" || !$GeneAliase){
    $GeneAliase = $LocusTag;
  }else{
    $GeneAliase = str_replace("|", "<br>&nbsp;&nbsp;", $GeneAliase);
    if($LocusTag && stristr($GeneAliase, $LocusTag) === FALSE){
      $GeneAliase .= "<br>&nbsp;&nbsp;".$LocusTag;
    }
  }      
?>
	  <td width="80%"><div class=maintext>&nbsp;<?php echo $GeneAliase;?></div></td>
	</tr>
  <tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>Species</b>:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;<?php  if($TaxID && $fundFlag) echo get_Species_from_proteinDB($proteinDB, $TaxID);?></div></td>
	</tr>
<?php if($Alias){?>    
	<tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>BioFilter</b>:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;<?php echo $Alias;?></div></td>
	</tr>
<?php }?>  
	<tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>Description</b>:&nbsp;</div>
	  </td>
<?php 
if($Alias){
  $array1 = array();
  $array2 = array();
  $setTable = 'Protein_Class';
  $setName = '';
  
  $SQL = "SELECT
        Name,   
        KeyWord
        FROM FilterName
        WHERE Alias = '$Alias'";             
  $KeyWordArr2 = $PROHITSDB->fetchAll($SQL);
  for($i=0; $i<count($KeyWordArr2); $i++){
    if($KeyWordArr2[$i]['KeyWord']){
      $tmpArr = explode(";", $KeyWordArr2[$i]['KeyWord']);
      $array1 = array_merge($array1, $tmpArr);
    }
  }
  for($i=0; $i<count($array1); $i++){
    $array1[$i] = trim($array1[$i]);
  }
  
  $array1 = array_unique($array1);
  $patterns = array();
  foreach($array1 as $value){
    array_push($patterns, "/(^)($value)/i");
    array_push($patterns, "/(\W+)($value)(\W+)/i");
    array_push($patterns, "/(\W+)($value)($)/i");
    array_push($array2, "<b>$value</b>");
  }
  $replace = '\1<b>\2</b>\3';
  if(count($KeyWordArr2)){
    $setName = $KeyWordArr2[0]['Name'];
  }
  //----------------------------------------------------------
  $SQL = "SELECT GeneID, GO_term FROM NCBI_gene2go WHERE GeneID='$GeneID'";
  $NCBI_gene2goArr = $proteinDB->fetchAll($SQL);
  $NCBI_gene2goArrCount = count($NCBI_gene2goArr);
  
  $tmpGeneIDArr = array();
  $singleGeneID = '';
  $GO_termStr = '';
  $uniqeArr = array();
  for($i=0; $i<$NCBI_gene2goArrCount; $i++){
    if($NCBI_gene2goArr[$i]['GeneID'] != $singleGeneID){    
      if($singleGeneID){
        $tmpGeneIDArr[$singleGeneID] = $GO_termStr;
      }
      $singleGeneID = $NCBI_gene2goArr[$i]['GeneID'];
      $GO_termStr = "<BR><font color='green'><b>Go Annotations:</b></font>";
      $uniqeArr = array();
    }
    if(!in_array($NCBI_gene2goArr[$i]['GO_term'], $uniqeArr)){
      array_push($uniqeArr, $NCBI_gene2goArr[$i]['GO_term']);
	      
      $NCBI_gene2goArr[$i]['GO_term'] = preg_replace($patterns, $replace, $NCBI_gene2goArr[$i]['GO_term']);
	   
      $GO_termStr .= "<BR>- ".$NCBI_gene2goArr[$i]['GO_term'];
    }  
  }
  $Description = preg_replace($patterns, $replace, $Description);
  $Description = $Description.$GO_termStr;
}    
?>    
	  <td><div class=maintext>&nbsp;<?php echo $Description;?></div></td>
	</tr>
<?php 
if($Acc_counter > 1){?>
  <tr bgcolor="#caa75e">
	  <td colspan="2" align="left" height=20>
	   <div class=tableheader>&nbsp;&nbsp;Select accession to pass value</div>
	  </td>
	</tr>
<?php 
}

foreach($proteinAccArr as $key => $val){?>
<?php if($Acc_counter > 1){?>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
<?php }?>  
  <tr bgcolor="#e9e1c9">
	  <td align="right" nowrap><div class=maintext>
<?php if($Acc_counter > 1){?>
    <input type='radio' name='acc_rad' onclick="get_sequence_detail('<?php echo $val['SequenceID'];?>','<?php echo $key?>','<?php echo $val['GI']?>')">
<?php }?>      
	    <b>Accession</b>:&nbsp;</div>
	  </td>
	  <td><div id='Acc_<?php echo $key?>'  class=maintext><?php echo $val['Acc_Version'];?></div></td>
	</tr>
  <tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>GI Number</b>:&nbsp;</div>
	  </td>
	  <td><div id='gi_<?php echo $key?>' class=maintext><?php echo $val['GI'];?></div></td>
	</tr>
  
<?php if($Acc_counter == 1){?>  
  <tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>Acc Description</b>:&nbsp;</div>
	  </td>
	  <td><div id='des_<?php echo $key?>' class=maintext><?php echo $val['Description'];?></div></td>
	</tr>
  <tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>MW</b>:&nbsp;</div>
	  </td>
	  <td><div id='MW_<?php echo $key?>' class=maintext><?php echo $val['MW'];?> KDa</div></td>
	</tr>	
	<tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>Sequence</b>:&nbsp;</div>
	  </td>
	  <td valign=top><div class=maintext>
  	<?php  
    if($val['Sequence']){
      echo $val['Sequence']; 
    }else{
      echo $val['SequenceID'];  
    } 
  	?></div></td>
	</tr>
<?php }else{?>
  <tr>
    <td align=center colspan=2>
    <DIV id="sequence_mw_div_<?php echo $key?>" STYLE="display: none;"></DIV>
    </td>
  </tr>
<?php 
  }
}
?>   
	<tr bgcolor="#e9e1c9" align="center">
	  <td colspan="2">
<?php if($fundFlag && !$from){?>
		<input type="button" value="Pass Value" onclick="javascript: passvalue();" class=black_but>&nbsp;
<?php }?>
		<input type="button" value=" Close " onclick="javascript: window.close();" class=black_but></td>
	</tr>
</table>
</center>
</form>
<?php 
html_footer();
//--------------------------
// pass Sequence to calculate MS
// function return MW
//--------------------------
function calcProteinMass($sequence){
  # amino acid monoisotopic residue molecular weights
  if(!$sequence)return 0;
  $masses = array(
  	'A' => 71.079,
		'B' => 0,
		'C' => 103.145,
		'D' => 115.089, 
		'E' => 129.116,
		'F' => 147.177,
		'G' => 57.052,
		'H' => 137.141,
		'I' => 113.160,
		'J' => 0,
		'K' => 128.17,
		'L' => 113.160,
		'M' => 131.199,
		'N' => 114.104,
		'O' => 0,
		'P' => 97.117,
		'Q' => 128.131,
		'R' => 156.188,
		'S' => 87.078,
		'T' => 101.105,
		'U' => 0,
		'V' => 99.133,
		'W' => 186.213,
		'X' => 0,
		'Y' => 163.176,
		'Z' => 0
  );
	$sequence = strtoupper($sequence);
  $chars = preg_split('//', $sequence, -1, PREG_SPLIT_NO_EMPTY);
  $tempMass = 0;
  for($i=0; $i<count($chars); $i++){
     $tempMass = $tempMass + $masses[$chars[$i]];
  }  
  return  round(($tempMass + 18)/1000, 2);
}

function get_sequence_MW($SequenceID,$GI,&$AccArr){
  $AccArr['Sequence'] = '';
  $AccArr['MW'] = '';
  $AccArr['Description'] = '';
  if(!$SequenceID && !$GI){
    return;
  }elseif(!$SequenceID){
    $ret_arr = get_seqence_from_NCBI($GI);
    if(!$ret_arr){
      return;
    }else{
      $tmp_sequence = trim($ret_arr['sequence']);
      $AccArr['Description'] = $ret_arr['description'];
    }
  }else{
    global $proteinDB;
    $SQL="SELECT Description FROM Protein_Accession WHERE GI='$GI'";
    $desArr = $proteinDB->fetch($SQL);
    if($desArr) $AccArr['Description'] = $desArr['Description'];
    $SQL="SELECT Sequence FROM Protein_Sequence WHERE ID='$SequenceID'";
    $sequenceArr = $proteinDB->fetch($SQL);
    if(count($sequenceArr)){    
      $tmp_sequence = trim($sequenceArr['Sequence']);
    }
  }    
  if($tmp_sequence){
    $AccArr['MW'] = calcProteinMass($tmp_sequence);
    $Sequence_str = '';
    $chars = preg_split('//',$tmp_sequence, -1, PREG_SPLIT_NO_EMPTY);
    for($i=0;$i<count($chars);$i++){
		  if($i%50 == 0 and $i!=0) $Sequence_str .= "<br>";
			$Sequence_str .= $chars[$i];
		}
    $AccArr['Sequence'] = $Sequence_str;
  }  
}
function multi_sort($array, $akey){  
  function compare($a, $b){
     global $sortKey;
     return 0 - strcmp($a[$sortKey], $b[$sortKey]);
  } 
  usort($array, "compare");
  return $array;
}

function get_sequence_MW_div($SequenceID,$GI,$selected_div,$tmp_index){
  $AccArr = array();
  get_sequence_MW($SequenceID,$GI,$AccArr);
  echo "@@**@@".$selected_div."@@**@@";
?>
  <table border=0 width=100% cellspacing="1" cellpadding=0>
    <tr bgcolor="#e9e1c9">
     <td align="right" nowrap width="20%">
       <div class=maintext><b>Acc Description</b>:&nbsp;</div>
     </td>
     <td><div id='des_<?php echo $tmp_index?>'  class=maintext><?php echo $AccArr['Description']?></div></td>
	  </tr>
    <tr bgcolor="#e9e1c9">
     <td align="right" nowrap width="20%">
       <div class=maintext><b>MW</b>:&nbsp;</div>
     </td>
     <td><div id='MW_<?php echo $tmp_index?>'  class=maintext><?php echo $AccArr['MW']?> KDa</div></td>
	  </tr>	
	  <tr bgcolor="#e9e1c9">
	    <td align="right" nowrap>
	      <div class=maintext><b>Sequence</b>:&nbsp;</div>
	    </td>
	    <td valign=top><div id='sequence_detail_div' class=maintext><?php echo $AccArr['Sequence']?></div></td>
	  </tr>
  </table>
<?php  
} 
//end of function
function html_header(){
echo "<html>\r\n";
echo "<head>\r\n";
echo "  <meta http-equiv='content-type' content='text/html;charset=iso-8859-1'>\r\n";
echo "<title>Prohits</title>\r\n";
echo "<link rel='stylesheet' type='text/css' href='./site_style.css'>\r\n";
echo "</head>\r\n";
echo "<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 topMargin=5 rightMargin=5 marginheight=0 marginwidth=0>\r\n";
}
function html_footer(){
echo "</body>\r\n";
echo "</html>\r\n";
}

function display_child($GeneName){
  global $ProteinSpecies_arr,$child_tax_id_arr,$return_geneID,$return_gene,$return_orf,$pageName,$return_tax,$return_species;        
  html_header();
  ?>
  <form name="protein_info_frm">
  <input type="hidden" name='GeneName' value='<?php echo $GeneName?>'>
  <input type="hidden" name='return_geneID' value='<?php echo $return_geneID?>'>
  <input type="hidden" name='return_gene' value='<?php echo $return_gene?>'>
  <input type="hidden" name='return_orf' value='<?php echo $return_orf?>'>
  <input type="hidden" name='return_tax' value='<?php echo $return_tax?>'>
  <input type="hidden" name='return_species' value='<?php echo $return_species?>'>
  <input type="hidden" name='pageName' value='<?php echo $pageName?>'>
  
  <center>
  <table border="0" cellpadding="0" cellspacing="1" width="98%">
  	<tr bgcolor="#a48b59">
  	  <td colspan="4" align="center" height=20>
  	   <div class=tableheader>Species Information</div>
  	  </td>
  	</tr>
    <tr bgcolor="#e9e1c9">
  	  <td align="center" width="20%">
  	   <div class=maintext><b>Tax ID:</b>&nbsp;</div>
  	  </td>
  	  <td align="center">
        <div class=maintext>&nbsp;<b>Species</b>&nbsp;</div>
      </td>
      <td align="center">
        <div class=maintext>&nbsp;<b>Gene Name</b>&nbsp;</div>
      </td>
      <td width="10%" align="center">
        <div class=maintext>&nbsp;</div>
      </td>
  	</tr>
  <?php       
  foreach($ProteinSpecies_arr as $tmp_val){
    if(in_array($tmp_val['Tax_id'], $child_tax_id_arr)){
  ?>  
    <tr bgcolor="#e9e1c9">
  	  <td align="left" width="20%">
  	   <div class=maintext>&nbsp;&nbsp;<?php echo $tmp_val['Tax_id']?></div>
  	  </td>
  	  <td align="left">
        <div class=maintext>&nbsp;&nbsp;<?php echo $tmp_val['name_txt']?></div>
      </td>
      <td align="left">
        <div class=maintext>&nbsp;&nbsp;<?php echo $GeneName?></div>
      </td>
      <td width="10%" align="center">
        <input type='radio' name='TaxID' value='<?php echo $tmp_val['Tax_id']?>' onclick="this.form.submit()">
      </td>
  	</tr>      
  <?php 
    }        
  }
?>
  </table>
  </center>
<?php 
  html_footer();
}
?>
