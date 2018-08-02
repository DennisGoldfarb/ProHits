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

$frm_alias = '';
$frm_taxID = '';
$myaction = '';


require_once("../common/site_permission.inc.php");
include("admin_log_class.php");
include("common_functions.inc.php");
include("./admin_header.php");

$AdminLog = new AdminLog();

if($myaction == "add"){
  if(!$GeneID or !$Alias){
     echo "GeneID='$GeneID' Filter='$Alias'";
     echo "<font color=red>this record needs GeneID and Filter to insert. Please email this GeneID or Filter to frank at gliu@mshri.on.ca</font>";
     exit;
  }
  if($AUTH->Insert){
    $oldDBName = to_proteinDB($mainDB);  
    $SQL ="UPDATE Protein_Class SET 
          BioFilter = CONCAT_WS(',',BioFilter,'$Alias') where EntrezGeneID=$GeneID";
    $mainDB->execute($SQL);
    
    to_defaultDB($mainDB);
    $Desc = "EntrezGeneID=$GeneID,BioFilter=$Alias";       
    $AdminLog->insert($AccessUserID,'Protein_Class',$GeneID,'addFilter',$Desc);
  }  
  echo "<script language='javascript'>\n";
  echo "window.close();\n";
  echo "</script>\n";
  exit;
}
if($myaction == "delete"){
  if(!$GeneID or !$Alias){
     echo "GeneID='$GeneID' Filter='$Alias'";
     echo "<font color=red>this record needs GeneID and Filter to delete. Please email this GeneID or Filter to frank at gliu@mshri.on.ca</font>";
     exit;
  }
  if($AUTH->Delete){
    $oldDBName = to_proteinDB($mainDB);
    $SQL = "SELECT BioFilter FROM Protein_Class WHERE EntrezGeneID=$GeneID";
    $Protein_ClassArr = $mainDB->fetch($SQL);
    if(count($Protein_ClassArr)){
      $BioFilterArr = explode(",", $Protein_ClassArr['BioFilter']);
      $newBioFilterArr = array(); 
      foreach($BioFilterArr as $value){
        if($value != $Alias){
          array_push($newBioFilterArr, $value);
        }
      }
      $BioFilterStr = '';
      if($newBioFilterArr){
        $BioFilterStr = implode(",", $newBioFilterArr);
      }    
      $SQL ="UPDATE Protein_Class SET 
          BioFilter = '$BioFilterStr' where EntrezGeneID=$GeneID";       
      $mainDB->execute($SQL);
      
      to_defaultDB($mainDB);    
      $Desc = "EntrezGeneID=$GeneID,BioFilter=$Alias";       
      $AdminLog->insert($AccessUserID,'Protein_Class',$GeneID,'removeFilter',$Desc);
      
    }
  }  
  echo "<script language='javascript'>\n";
  echo "window.close();\n";
  echo "</script>\n";
  exit;
}

$SQL = "SELECT ID,
        Name,
        Alias,
        Type,
        KeyWord
        FROM FilterName
        WHERE Type = 'Bio' AND KeyWord IS NOT NULL AND KeyWord!=''
        GROUP BY Alias ORDER BY Name";
$FilterNameArr2 = $mainDB->fetchAll($SQL);

$SQL = "SELECT TaxID,
        Name
        FROM ProteinSpecies
        ORDER BY Name";
$TaxArr2 = $mainDB->fetchAll($SQL);

/*$SQL = "SELECT URL        
        FROM WebLink
        WHERE Name='NCBIUrl_locuslink'";
$WebLinkArr = $mainDB->fetch($SQL);*/

?>
<script language="javascript">
function search(){
  var theForm = document.create_bio_filter;
  if(theForm.frm_alias.value == ''){
    alert("Please select a Filter");
  }else if(theForm.frm_taxID.value == ''){
    alert("Please select a Species");
  }else{
    theForm.submit(); 
  }
}
function refresh(){
  var theForm = document.create_bio_filter;
  theForm.frm_alias.value = theForm.hi_alias.value;
  theForm.frm_taxID.value = theForm.hi_taxID.value;
  theForm.submit();
}
function openDelWin(obj){
  var theForm = document.create_bio_filter;
  var GeneID = obj.value;
  var Alias = theForm.hi_alias.value;
  //alert("GeneID" + GeneID + "Alias" + Alias);
  var file = '<?php echo $PHP_SELF;?>' + '?myaction=delete&GeneID='+GeneID+'&Alias='+Alias;
  newwin = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=620,height=600');
}
function openSaveWin(obj){
  var theForm = document.create_bio_filter;
  var GeneID = obj.value;
  var Alias = theForm.hi_alias.value;
  //alert("GeneID" + GeneID + "Alias" + Alias);
  var file = '<?php echo $PHP_SELF;?>' + '?myaction=add&GeneID='+GeneID+'&Alias='+Alias;
  newwin = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=620,height=600');
}
</script>
<table border=0 cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td align="left"><br>
    <font color="navy" face="helvetica,arial,futura" size="3">&nbsp;<b>Add Proteins to Bio Filters </b> 
    </font> 
    </td>  
  <tr>
    <td colspan=2 height=0 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
  <td align="center"><br>
<form name=create_bio_filter action="<?php echo $PHP_SELF;?>" method=post>
<input type="hidden" name="myaction">
<table border=0 width=750>
  <tr>
    <td width=33%><b>Select a Filter</b></td>
    <td><b>Select a Species</b></td>
  </tr>
  <tr>
    <td>
      <select name="frm_alias">
			<option selected value="">--Select a Filter--
    <?php foreach($FilterNameArr2 as $value){
        if($value['Alias'] != 'BT'){
    ?>
      <option value="<?php echo $value['Alias'];?>"<?php echo ($value['Alias']==$frm_alias)?" selected":"";?>><?php echo $value['Name'];?>
    <?php 
        }
      }
    ?>			
			</select> 		
    </td>
    <td>
      <select name="frm_taxID">
			<option selected value="">--Select a Species--
    <?php foreach($TaxArr2 as $value){?>
      <option value="<?php echo $value['TaxID'];?>"<?php echo ($value['TaxID']==$frm_taxID)?" selected":"";?>><?php echo $value['Name'];?>
    <?php }?>			
			</select> 
    </td>
    <td width=23%>      
      <input type="button" value="  Search  " onclick="search()">
    </td>
  </tr>
</table>
<?php 

$array1 = array();
$array2 = array();
$setTable = 'Protein_Class';
$setName = '';

$SQL = "SELECT
      Name,   
      KeyWord
      FROM FilterName
      WHERE Alias = '$frm_alias'"; 
//echo  $SQL;     
             
$KeyWordArr2 = $mainDB->fetchAll($SQL);
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



if($frm_taxID and count($array1)){
  $TaxID_Species_Arr = array();
  $taxIDstr = get_TaxID_tree_str($frm_taxID, $TaxID_Species_Arr);
  //echo $taxIDstr;exit;
  $oldDBName = to_proteinDB($mainDB);
  
  
  
  
  /*
  $SQL = "SELECT EntrezGeneID,  
          LocusTag, 
          GeneName, 
          Description,
          BioFilter,
          TaxID
          FROM Protein_Class 
          WHERE TaxID IN ($taxIDstr) 
          AND (";
  //echo $SQL;
  for($i=0; $i< count($array1); $i++){
    if($i > 0){
      $SQL .= " or ";
    }
    $SQL .= " Description REGEXP '^".$array1[$i]."[^A-Za-z0-9_]+' or 
              Description REGEXP '[^A-Za-z0-9_]+".$array1[$i]."[^A-Za-z0-9_]+' or
              Description REGEXP '[^A-Za-z0-9_]+".$array1[$i]."$'";
  }
  $SQL .=") order by GeneName";
  
  $i = 0;
  echo $SQL;
  $sqlResult = mysqli_query($mainDB->link, $SQL);
  $count = mysqli_num_rows($sqlResult);
  $GeneIdStr = '';
  while (list(
       $EntrezGeneID[$i], 
       $LocusTag[$i], 
       $GeneName[$i],
       $Description[$i],    
       $BioFilter[$i],
       $TaxID[$i])= mysqli_fetch_row($sqlResult)){
     if($i) $GeneIdStr .= "','";
     $GeneIdStr .= $EntrezGeneID[$i];
     $i++;
  }
  $GeneIdStr = "'" . $GeneIdStr . "'";
  */
  
  
  $SQL = "SELECT count(GeneID) as num FROM NCBI_gene2go";
  $gene2goArr = $mainDB->fetch($SQL);
  if(!$gene2goArr['num']){
    $NCBI_gene2go_is_empty = 1;
  }
  $SQL = "SELECT GeneID, GO_term, tax_id FROM NCBI_gene2go WHERE tax_id IN ($taxIDstr) and (";
  for($i=0; $i< count($array1); $i++){
    if($i > 0){
      $SQL .= " or ";
    }
    $SQL .= " GO_term REGEXP '^".$array1[$i]."[^A-Za-z0-9_]+' or 
              GO_term REGEXP '[^A-Za-z0-9_]+".$array1[$i]."[^A-Za-z0-9_]+' or
              GO_term REGEXP '[^A-Za-z0-9_]+".$array1[$i]."$'";
  }
  $SQL .=") ORDER BY GeneID";
  
    
  $NCBI_gene2goArr = $mainDB->fetchAll($SQL);
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
  
  ?>
  
  <H1>Bio-filter set: <?php echo $setName;?></h1>
  <h2>Search results from Protein_Class database</h2>
  <?php
  if(isset($NCBI_gene2go_is_empty)){
    echo "<font color=red><b>Warning</b>: Table 'NCBI_gene2go' is empty. Please use \"Protein DB Update\" function to add data to the table.</font><br>";
  }
  ?>
  1. keywords: 
  <?php 
  if(count($array2) > 5) echo "<br>"; 
  for($i=0; $i<count($array2); $i++){
    echo '"'.$array2[$i].'" ';
    if(!(($i+1) % 5)) echo "<br>";
  }
  if(count($array2) > 5 and ($i % 5)) echo "<br>";
  
  ?> <b>Total: <?php echo count($tmpGeneIDArr);?></b><br>
  2. Grayed record has been set to filter <?php echo $setName;?><br><br>
  
  <input type="hidden" name="hi_alias" value='<?php echo $frm_alias;?>'>
  <input type="hidden" name="hi_taxID" value='<?php echo $frm_taxID;?>'>
  <input type="button" value="Refresh this page" onclick="refresh()">
  <table><tr><td>&nbsp;</td></tr></table>
  <table border=1 width=850>
    <tr>
      <td nowrap width=15%><b>GeneID / GeneName</b></td>
      <td><b>Description</b></td>
      <td width=20%><b>Species</b></td>
      <td width=15%><b>option</b></td>
    </tr>
  <?php 
  $i=0;
  foreach ($tmpGeneIDArr as $theEntrezGeneID=>$GO_termStr) {
    
    $SQL = "SELECT EntrezGeneID,  
          LocusTag, 
          GeneName, 
          Description,
          BioFilter,
          TaxID
          FROM Protein_Class 
          WHERE EntrezGeneID='$theEntrezGeneID'"; 
    $theGeneArr = $mainDB->fetch($SQL);
    if($theGeneArr){
      $theLocusTag = $theGeneArr['LocusTag'];
      $theGeneName = $theGeneArr['GeneName'];
      $theBioFilter = $theGeneArr['BioFilter'];
      $theDescription = $theGeneArr['Description'];
      $theSpecies = $TaxID_Species_Arr[$theGeneArr['TaxID']];
    }else{
      $theLocusTag = '';
      $theGeneName = '';
      $theBioFilter = '';
      $theDescription = '';
      $theSpecies = '';
    }
    
     //do not show those record which is in $setTable nor is not ribosome
    if(strstr($theBioFilter, $frm_alias)){
    ?> 
      <tr bgcolor=#c0c0c0>
          <td><?php echo $theEntrezGeneID." / ".$theGeneName;?></td>
          <td><?php echo $theDescription.$GO_termStr;?></td>
          <td><?php echo $theSpecies;?></td>
          <td><b><?php echo ($i+1);?></b><br>
          <?php if($AUTH->Delete){?>
            <input type="checkbox" name="myCheck<?php echo $i;?>" value="<?php echo $theEntrezGeneID;?>" checked onclick="javascript: openDelWin(this)"><font color=red><?php echo $setName;?></font><br>          
          <?php }
            get_geneID_URL($theEntrezGeneID);
          ?>
          </td>
      </tr>
    <?php   
    }else{
    //only display the record which not in $setTable
    ?>
      <tr>
          <td><?php echo $theEntrezGeneID." / ".$theGeneName;?></td>
          <td><?php echo $theDescription.$GO_termStr;?></td>
          <td><?php echo $theSpecies;?></td>
          <td><b><?php echo ($i+1);?></b><br>
          <?php if($AUTH->Insert){?>
            <input type="checkbox" name="myCheck<?php echo $i;?>" value="<?php echo $theEntrezGeneID;?>" onclick="javascript: openSaveWin(this)"><font color=red><?php echo $setName;?></font><br>
          <?php }
            get_geneID_URL($theEntrezGeneID);
          ?>  
          </td>
        </tr>
    <?php     
    }//end if
    $i++;
  }//end for
}
?>
</table>
</form>
</td>
  </tr>
  </table>
<?php 
include("./admin_footer.php");
function get_TaxID_tree_str($root=0, &$TaxID_Species_Arr){
  if(!is_numeric($root)){
    return '';
  }
  $DB = $mainDB = new mysqlDB(PROHITS_DB);
  $taxIDstr = '';
  $SQL = "SELECT TaxID, Name FROM ProteinSpecies WHERE TaxID='".$root."'";
  if($rootPairArr = $DB->fetch($SQL)){
    $SpeciesArr['TaxID'] = $rootPairArr['TaxID'];
    $SpeciesArr['Name'] = $rootPairArr['Name'];
    $mainArr = array();  
    array_push($mainArr, $SpeciesArr);
    while(count($mainArr)){
      $popItem = array_pop($mainArr);
      $TaxID_Species_Arr[$popItem['TaxID']] = $popItem['Name'];
      if($taxIDstr) $taxIDstr .= ",";
      $taxIDstr .= $popItem['TaxID'];
      $SQL = "SELECT TaxID, Name FROM ProteinSpecies WHERE ParentTaxID=".$popItem['TaxID']." ORDER BY Name DESC";  
      $SpeciesArr2 = $mainDB->fetchAll($SQL);
      for($i=0; $i<count($SpeciesArr2); $i++){
        array_push($mainArr, $SpeciesArr2[$i]);
      }
    }
    return $taxIDstr;
  }else{
    return $root;
  }
}
function get_geneID_URL($geneID){
  if($geneID){
    $url = "http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?db=gene&cmd=Retrieve&dopt=Graphics&list_uids=$geneID";
    echo $rt = "[<a href=$url target=new class=button>NcbiGene</a>]";
  }  
}  
?>
