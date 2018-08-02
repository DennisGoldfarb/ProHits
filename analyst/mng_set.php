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

$theaction ='';
$order_by = 'GeneName';
$msg = '';
$frm_GeneID = '';
$frm_LocusTag = '';
$frm_GeneName = '';
$frm_TaxID = '';
$Cyt_GeneID = '';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

if(!$frm_TaxID){
  $frm_TaxID = $_SESSION["workingProjectTaxID"];
}
if($theaction=='export'){
  export_file($file_name);
}

$permission = get_mermission($PROHITSDB, $SCRIPT_NAME, $_SESSION['USER']->ID);
//------------------action processing --------------------------------------------
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
$Log = new Log();
if($theaction == "insert" and $permission['Insert']){
  $SQL = "UPDATE Protein_Class SET BioFilter=CONCAT_WS(',', BioFilter, ',$Alias') WHERE EntrezGeneID=$frm_GeneID";
  $proteinDB->execute($SQL);
  $Desc = "Add BioFilter $Alias";
  $Log->insert($AccessUserID,'Protein_Class',$frm_GeneID,'add',$Desc,$AccessProjectID);    
  $msg =  "Add BioFilter $Alias successfully for GeneID $frm_GeneID";
  add_species($frm_TaxID,$new_species);    
  //$theaction = 'added';
}
if($theaction == "delete" and $Cyt_GeneID AND $permission['Delete']) {
  $SQL = "SELECT BioFilter FROM Protein_Class WHERE EntrezGeneID=$Cyt_GeneID";
  $proteinClassArr = $proteinDB->fetch($SQL);  
  $pattern = "/(,$Alias)|($Alias,)|($Alias)/";
  //echo $pattern;
  $newFilters = preg_replace($pattern, "", $proteinClassArr['BioFilter']);
  //echo $newFilters;
  $SQL = "UPDATE Protein_Class SET BioFilter='$newFilters' WHERE EntrezGeneID=$Cyt_GeneID";
  //echo $SQL;exit;
  $proteinDB->execute($SQL); 
  $Desc = "Move BioFilter $Alias";
  $Log->insert($AccessUserID,'Protein_Class',$Cyt_GeneID,'move',$Desc,$AccessProjectID);  
  $msg = "Move BioFilter $Alias successfully for GeneID<b>$Cyt_GeneID</b>.";
}

$URL = getURL();
$SQL = "SELECT Name, Alias, Description,KeyWord FROM FilterName WHERE ID=$filterID";

$filterArr = $PROHITSDB->fetch($SQL);

//--get set values---------
$setsArr = get_enumORset($proteinDB, "BioFilter","Protein_Class");
if(in_array($filterArr['Alias'], $setsArr)){
  $title = $filterArr['Name']."(".$filterArr['Alias'].") Proteins";
}else{  
  $title = $filterArr['Name']." (".$filterArr['Alias'].")";
}  
$filterArr['Description'] = str_replace ("\r\n", "<br><br>", $filterArr['Description']);

$bgcolor = "#e1e1e1";
$bgcolordark = "#8a8a8a";
echo "
<html>
<head>
  <meta http-equiv=\"content-type\" content=\"text/html;charset=iso-8859-1\">
<link rel=\"stylesheet\" type=\"text/css\" href=\"./site_style.css\"> 
<title>Prohits</title>
</head><basefont face=\"arial\">
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff>
";
?>
<table border=0 width=100% cellspacing="1" cellpadding=0 bgcolor='#a0a7c5' width=100%><tr><td valign=top align=center bgcolor="white" width=100%>
<table border="0" cellpadding="0" cellspacing="0" width="95%">
 <tr> 
  <td><br><span class=pop_header_text><?php echo $title;?></span></td>
 </tr>
 <tr>
   <td nowrap align=center height='1'><hr size=1></td>
</tr> 
 <tr>
   <td>
   <?php  
   echo $filterArr['Description'];
   if($filterArr['KeyWord']){
    echo "<br><strong>Key Words</strong>: <font color=green size=-1>".$filterArr['KeyWord']."</font>";
   }
   ?>
   </td>
 </tr>
<?php  
if(in_array($filterArr['Alias'], $setsArr)){
  $TaxIDsArr = get_TaxID_family_from_proteinDB($proteinDB, $_SESSION["workingProjectTaxID"]);
  $TaxIDsStr = implode("','", $TaxIDsArr);
  if($TaxIDsStr != ''){
    $TaxIDsStr = "'" . $TaxIDsStr . "'";
    $SQL = "SELECT EntrezGeneID, LocusTag, GeneName, GeneAliase, TaxID FROM Protein_Class  WHERE  BioFilter LIKE '%".$filterArr['Alias']."%'";
    $SQL2 = "SELECT ENSG  as EntrezGeneID,`GeneName`, TaxID FROM `Protein_ClassENS` 
      WHERE TaxID IN(".$TaxIDsStr.") AND BioFilter LIKE '%".$filterArr['Alias']."%'";
    $SQL .= " ORDER BY $order_by";
    if(strstr($order_by, 'EntrezGeneID')){
      $ENSorder_by = str_replace('EntrezGeneID', 'ENSG', $order_by);
      $SQL2 .= " ORDER BY $ENSorder_by";
    }else{
      $SQL2 .= " ORDER BY $order_by";
    }  
  }else{
    $SQL = "SELECT EntrezGeneID, LocusTag, GeneName, GeneAliase, TaxID FROM Protein_Class WHERE BioFilter LIKE '%".$filterArr['Alias']."'";
    $SQL2 = "SELECT ENSG as EntrezGeneID, GeneName, TaxID FROM Protein_ClassENS WHERE BioFilter LIKE '%".$filterArr['Alias']."'";
    if($order_by){
      $SQL .= " ORDER BY $order_by";
      if(strstr($order_by, 'EntrezGeneID')){
        $ENSorder_by = str_replace('EntrezGeneID', 'ENSG', $order_by);
        $SQL2 .= " ORDER BY $ENSorder_by";
      }else{
        $SQL2 .= " ORDER BY $order_by";
      }
    }  
  }
  $genePropertyArr = $proteinDB->fetchAll($SQL);
  
  $mng_set_dir = "../TMP/mng_set";
  if(!_is_dir($mng_set_dir)) _mkdir_path($mng_set_dir);
  $file_full_name = $mng_set_dir."/".$AccessProjectID."_".$filterArr['Alias']."_mng_set.csv";
  if($fp = fopen($file_full_name, 'w')){
    fwrite($fp, "Gene ID,Gene Name,Gene Aliase\r\n");
    foreach($genePropertyArr as $val){
      $tmp_line = $val['EntrezGeneID'].",".$val['GeneName'].",".(($val['GeneAliase']=='-')?'':$val['GeneAliase'])."\r\n";
      fwrite($fp, $tmp_line);
    }
    fclose($fp);
  }
  $ENSgenePropertyArr = array();  
?>
<script language="javascript">
function confirm_delete(Cyt_GeneID){
	if(confirm("Are you sure that you want to delete " + Cyt_GeneID + "?")){
		document.del_form.Cyt_GeneID.value = Cyt_GeneID;
    document.del_form.theaction.value = 'delete';
		document.del_form.submit();
	}
}
function checkform(theForm){
	var GeneID = theForm.frm_GeneID.value;  	
	if(GeneID == '' || trimString(GeneID) == 0){
	  alert("GeneID is required to add filter.");
	} else {
	  theForm.theaction.value = "insert";    
		theForm.submit();
	}
}
function getProteinInfo(theForm){
  var LocusTag=theForm.frm_LocusTag.value;
  var GeneID=theForm.frm_GeneID.value;
  var TaxID = theForm.frm_TaxID.value; 
  var GeneName=theForm.frm_GeneName.value;
  var Alias=theForm.Alias.value;
  
  var file = 'pop_proteinInfo.php?GeneID=' + GeneID + '&LocusTag=' + LocusTag + '&TaxID=' + TaxID + '&GeneName=' + GeneName + '&pageName=mng_set' + "&Alias=" + Alias;
  if(TaxID == ""){
    alert('Please Choose a TaxID.');
  }else if(!isNumber(GeneID)){
    alert('Please type only numbers in GineID field.');  
  }else if(isEmptyStr(LocusTag) && isEmptyStr(GeneName) && isEmptyStr(GeneID)){
    alert('Please type Gene ID or Locus Tag or Gene Name.');
  }else{
    newwin = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=520,height=400');
    newwin.moveTo(1,1);   
  }
}
function isNumber(str) {
  for(var position=0; position<str.length; position++){
        var chr = str.charAt(position)
        if ( ( (chr < "0") || (chr > "9") ) && chr != ".")
              return false;
  }
  return true;
}
function isEmptyStr(str){
  var str = this != window? this : str;
  var temstr =  str.replace(/^\s+/g, '').replace(/\s+$/g, '');
  if(temstr == 0 || temstr == ''){
     return true;
  } else {
    return false;
  }
}
function resetfields(theForm){
  theForm.frm_LocusTag.readOnly = false;
  theForm.frm_GeneID.readOnly = false;  
  theForm.frm_GeneName.readOnly = false;
  theForm.add_filter.disabled = true;
}
function trimString (str) {
	var str = this != window? this : str;
	return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
</script>
<form name="del_form" method=post action="<?php echo $PHP_SELF;?>">
  <input type=hidden name=theaction value=>
  <input type=hidden name=Alias value="<?php echo $filterArr['Alias'];?>">
  <input type=hidden name=filterID value="<?php echo $filterID;?>">
  <input type=hidden name=Cyt_GeneID value="<?php echo $Cyt_GeneID;?>">
  <input type=hidden name=order_by value='<?php echo $order_by;?>'>
  <input type=hidden name=new_species value="">
  <tr> 
    <td align="left"><br>     
    <?php 
    $species = get_TaxID_name($mainDB,$_SESSION["workingProjectTaxID"]);
    if($AccessProjectName){
        echo "<font color='red' face='helvetica,arial,futura' size='3'><b>Project: &nbsp;&nbsp;$AccessProjectName<br>Species: $species</b></font>";
    }
    ?>        
    </td>
  </tr>
  <tr> 
    <td align="right">
<?php if(_is_file($file_full_name)){?>
      <a href="<?php echo $PHP_SELF;?>?theaction=export&file_name=<?php echo $file_full_name?>" class=button>[Export]</a>
<?php }?>      
<?php if($permission['Insert'] && $theaction != 'addnew' && 1) {?>
      <a href="<?php echo $PHP_SELF;?>?filterID=<?php echo $filterID;?>&theaction=addnew&order_by=<?php echo $order_by;?>" class=button>[Add New]</a>
<?php }?> <a href="javascript: window.close();" class=button>[Close Window]</a>&nbsp;
    </td>
  </tr>
 <?php 
  if($theaction == 'addnew'){ ?>
  <tr>
    <td>
      <table border="0" cellpadding="0" cellspacing="1" width="100%">
        <tr bgcolor="<?php echo $bgcolor;?>">
      	  <td align="right">
               <div class=maintext><b>GeneID:&nbsp;</div>
      	  </td>
      	  <td>&nbsp;<input type="text" name="frm_GeneID" size="15" maxlength=15 value="">
      	  <input type="button" value="Get Protein Info" onClick="javascript: getProteinInfo(this.form);">
      	  </td>
      	</tr>
      	<!--tr bgcolor="<?php echo $bgcolor;?>">
      	  <td align="right">
               <div class=maintext><b>LocusTag:&nbsp;</div>
      	  </td>
      	  <td>&nbsp;<input type="text" name="frm_LocusTag" size="15" maxlength=15 value=""><br>
      		<font face=Arail size=2>This field is ignored if a Gien ID is specified,when you click Get Protein Info button</font>	  
      	  </td>
      	</tr-->
        <input type="hidden" name="frm_LocusTag" size="15" maxlength=15 value="">
      	<tr bgcolor="<?php echo $bgcolor;?>">
      	  <td align="right" nowrap>
      	    <div class=maintext><b>Gene Name:</b>&nbsp;</div>
      	  </td>
      	  <td nowrap>&nbsp;<input type="text" name="frm_GeneName" size="15" maxlength=30 value=""><br>
      		<font face=Arail size=2>This field is ignored if a Gien ID or a Locus Tag is specified,when you click </br>Get Protein Info button</font>
      		</td>
      	</tr>	
      	<tr bgcolor="<?php echo $bgcolor;?>">
      	  <td align="right" valign=top nowrap>
      	    <div class=maintext><b>Species</b>:&nbsp;</div>
      	  </td>
          <?php  $frm_TaxID = (!$frm_TaxID)? $AccessProjectTaxID : $frm_TaxID; ?>
      	  <td>&nbsp;<select name="frm_TaxID">
          <option value="">--Choose a TaxID--<br>
          <?php  
            TaxID_list_($mainDB, $frm_TaxID);
      			//TaxID_list($mainDB, $frm_TaxID, $HITS_DB["prohits"]);
          ?>
      		</select>
      	  </td>
      	</tr>
        <tr bgcolor="<?php echo $bgcolor;?>">      	  
      	  <td colspan=2 align=center>&nbsp;
            <input type="button" name="add_filter" value="Add Filter" disabled onclick="checkform(this.form)">
            <input type="reset" name="reset" value="Reset" onclick="javascript: resetfields(this.form);"><br>
      		</td>
      	</tr>	
      </table>
    </td>
  </tr>
<?php }?>
 <tr>
    <td align="center" valign=top>
    <font color=red><?php echo $msg;?></font>
  <table border="0" cellpadding="0" cellspacing="1" width="100%">   
	<tr bgcolor="">
	  <td width="" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center><div class=tableheader>
      <a href="<?php echo $PHP_SELF;?>?filterID=<?php echo $filterID;?>&order_by=<?php echo  ($order_by == "EntrezGeneID")? 'EntrezGeneID%20desc':'EntrezGeneID';?>">
		GeneID</a>
		<?php if($order_by == "EntrezGeneID") echo "<img src='images/icon_order_up.gif'>";
			if($order_by == "EntrezGeneID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
		?></div>
	  </td>
    <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center onwrap><div class=tableheader>
	    <a href="<?php echo $PHP_SELF;?>?filterID=<?php echo $filterID;?>&order_by=<?php echo  ($order_by == "GeneName")? 'GeneName%20desc':'GeneName';?>">
		 GeneName</a>
		<?php if($order_by == "GeneName") echo "<img src='images/icon_order_up.gif'>";
			if($order_by == "GeneName desc") echo "<img src='images/icon_order_down.gif'>";
		?> </div>
	  </td>
    <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center nowrap>
    <div class=tableheader>Gene Aliase</div>
	  </td>
	  <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center>
	    <div class=tableheader>Links</div> 
	  </td>
	  </td>
	  <td width="" height="25" bgcolor="<?php echo $bgcolordark;?>" align="center">
	    <div class=tableheader>Options</div>
	  </td>
	</tr>
<?php 
$geneArr = array();
$ENSgeneArr = array();
$indexArr = array();
sort_print_filter_table($genePropertyArr,$ENSgenePropertyArr,$order_by,'y');
?>
 </form>
      </table>
 </td>
 </tr>
<?php 
}else{
?>
  <tr>
  <td align="right"><br><a href='javascript: window.close();' class=button>[Close Window]</a>&nbsp;
  </td>
  </tr>
<?php 
}
?> 
 
</table>    
</td></tr></table>
<?php //require("site_no_right_click.inc.php");?>
</body>
</html>
<?php 
function get_enumORset($DB, $field="",$table=""){
  $result=mysqli_query($DB->link, "SHOW COLUMNS FROM `$table` LIKE '$field'");
  if(mysqli_num_rows($result)>0){
    $row=mysqli_fetch_row($result);
    $options=explode("','", preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $row[1]));
  } else {
    $options=array();
  }  
  return $options;
}
function get_single_url($mainDB, $URLname){
  $oldDBname = to_defaultDB($mainDB);
  $SQL = "SELECT URL, Lable,ProteinTag FROM WebLink WHERE Name='$URLname'";
  $webLinkArr = $mainDB->fetch($SQL);
  back_to_oldDB($mainDB, $oldDBname);
  return $webLinkArr;
}
?>

