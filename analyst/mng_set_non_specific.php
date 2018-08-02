<?php 
/***********************************************************************
    Prohits version 1.00
    Copyright (C) 2001, Mike Tyers, All Rights Reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
*************************************************************************/

$theaction = '';
$order_by = '';
$GeneID = '';
$msg = '';
$frm_BaitGeneID = '';
$frm_BaitORF = '';
$frm_BaitGene = '';
$frm_TaxID = '';
$frm_NS_group_id = '';
$frm_new_file_name = '';
$option = 0;
$deleted_gene_id = '';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

if($theaction=='export'){
  export_file($file_name);
}

$NS_Dir = STORAGE_FOLDER."Prohits_Data/Non_Specific/";
$NS_data_dir = $NS_Dir."NS_data/";

if($theaction == 'delete'){
  $SQL = "SELECT `FileName` FROM `ExpBackGroundSet` WHERE `ID`='$frm_NS_group_id'";
  $name_arr = $HITSDB->fetch($SQL);
  $dataFileFullName = $NS_data_dir.$name_arr['FileName'];
  $tmpStr = file_get_contents($dataFileFullName);
  
  
  $tmpStr = trim($tmpStr);
  $tmpArr = explode(',',$tmpStr);
  $delete_arr[0] = $deleted_gene_id;
  $tmpArr = array_diff($tmpArr, $delete_arr);
  $tmpStr = implode(",", $tmpArr);
  if(!$NS_data_handle_tmp = fopen($dataFileFullName, "w")){
    echo "Cannot open file $new_full_file_name";
    exit;
  }
  fwrite($NS_data_handle_tmp, $tmpStr);
  $theaction = '';
}elseif($theaction == "insert"){
  if(!$frm_NS_group_id){
    $SQL = "INSERT INTO `ExpBackGroundSet` SET 
            `Name`='$frm_new_file_name',
            `ProjectID`='$AccessProjectID',
            `UserID`='$AccessUserID',
            `Date`='".@date("Y-m-d")."'";
    if(!$frm_NS_group_id = $HITSDB->insert($SQL)){
      echo "db insert problem";
      exit;
    }
    $fileName = "P".$AccessProjectID."_G".$frm_NS_group_id."_".$frm_new_file_name.".txt";
    $SQL = "UPDATE `ExpBackGroundSet` SET
           `FileName`='$fileName'
           WHERE ID= '$frm_NS_group_id'";
    if(!$ret = $HITSDB->execute($SQL)){
      echo "db update problem";
      exit;
    }
  }else{
    $SQL = "SELECT `FileName` FROM `ExpBackGroundSet` WHERE `ID`='$frm_NS_group_id'";
    if(!$tmpArr = $HITSDB->fetch($SQL)){
      echo "db fetch problem";
      exit;
    }
    $fileName = $tmpArr['FileName'];
  }  
  $dataFileFullName = $NS_data_dir.$fileName;
  if(_is_file($dataFileFullName)){
  
    $tmpStr = file_get_contents($dataFileFullName);    
    
    $tmpStr = trim($tmpStr);
    if($tmpStr){
      $tmpArr = explode(',',$tmpStr);
      if(!in_array($frm_GeneID, $tmpArr)){
        array_push($tmpArr, $frm_GeneID);
      }
      $tmpStr = implode(",", $tmpArr);
    }else{
      $tmpStr = $frm_GeneID;
    }
    if(!$NS_data_handle = @fopen($dataFileFullName, "w")){
      echo "Cannot open file $new_full_file_name";
      exit;
    }
    fwrite($NS_data_handle, $tmpStr);
  }else{
    if(!$NS_data_handle = @fopen($dataFileFullName, "w")){
      echo "Cannot open file $dataFileFullName";
      exit;
    }
    fwrite($NS_data_handle, $frm_GeneID);
  }
  $theaction = '';
}

if(!$order_by) $order_by = "GeneName";

$SQL = "SELECT Name, Alias, Description FROM FilterName WHERE ID=$filterID";
$oldDBName = to_defaultDB($mainDB);
$filterArr = $mainDB->fetch($SQL);
back_to_oldDB($mainDB, $oldDBName);
if(!$frm_TaxID){
  $frm_TaxID = $_SESSION["workingProjectTaxID"];
}
$NSfilteIDarr = array();
$title = "Background (Non-specific) Lists";
$filterArr['Description'] = str_replace ("\r\n", "<br><br>", $filterArr['Description']);
$bgcolor = "#e1e1e1";
$bgcolordark = "#8a8a8a";

$SQL = "SELECT `ID`,`Name`,`UserID`,`Date` FROM `ExpBackGroundSet` WHERE `ProjectID`='$AccessProjectID'";
$NSarr = $HITSDB->fetchAll($SQL);

//-----------------------------------------------------------------------------------
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
//echo "$NSfilteIDarr,$frm_NS_group_id";
get_NS_geneID($NSfilteIDarr,$frm_NS_group_id);
$NSlistAarr  = array_unique($NSfilteIDarr);

$genePropertyArr = array();
$ENSgenePropertyArr = array();
get_gene_property($genePropertyArr,$ENSgenePropertyArr,$NSlistAarr,'value');

$mng_set_dir = "../TMP/mng_ns_set";
if(!_is_dir($mng_set_dir)) _mkdir_path($mng_set_dir);
$file_full_name = $mng_set_dir."/".$AccessProjectID."_".$filterArr['Alias']."_mng_ns_set.csv";
if($fp = fopen($file_full_name, 'w')){
  fwrite($fp, "Gene ID,Gene Name,Gene Aliase\r\n");
  foreach($genePropertyArr as $val){
    $tmp_line = $val['EntrezGeneID'].",".$val['GeneName'].",".(($val['GeneAliase']=='-')?'':$val['GeneAliase'])."\r\n";
    fwrite($fp, $tmp_line);
  }
  fclose($fp);
}
//------------------------------------------------------------------------------------
?>
<html>
<head>

  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
  <link rel="stylesheet" type="text/css" href="./site_style.css">
  <!--script language="Javascript" src="site_no_right_click.inc.js"></script-->
  <script language="Javascript" src="../common/javascript/site_javascript.js"></script>
  
  <script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
  <script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
  <script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
  <link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
  <link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css"-->

  
</head><basefont face="arial">
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff>
<script language=javascript>

var groupNameArr = new Array();
<?php 
  $j = 0;
  foreach($NSarr as $value){
?>
    groupNameArr[<?php echo $j++?>] = '<?php echo $value['Name']?>';
<?php }?>
function pop_update_win(){
  var theForm = document.del_form;
  var NS_group_id = theForm.frm_NS_group_id;
  var frm_NS_group_id = '';
  for(var i=0; i<NS_group_id.length; i++){
    if(NS_group_id[i].selected == true){
      frm_NS_group_id = NS_group_id[i].value;
      break;
    }
  }
  var pop_file = "./import_NS_data.php?show=first_time&frm_group_name_s=" + frm_NS_group_id;
  //var pop_file = "./import_NS_data_3jp.php?show=first_time&frm_group_name_s=" + frm_NS_group_id;
  popwin(pop_file,650,395,'w_name')
}
function change_group(){
  var theForm = document.del_form;
  theForm.submit();
}

function add_new(){
  var add_lable_div = document.getElementById('add_lable_div');
  var add_new_div = document.getElementById('add_new_div');
  if(add_lable_div.innerHTML == "[Add New]"){
    add_new_div.style.display = "block";
    add_lable_div.innerHTML = "";
  }else{
    add_new_div.style.display = "none";
    add_lable_div.innerHTML = "[Add New]";
    resetfields();
  }
}
function cancel_merging(obj_id){
  hideTip(obj_id);
}

function show_hide_merging_div(event,obj_id){
  add_new_obj = document.getElementById(obj_id);
  if(add_new_obj.style.display == "none"){
    showTip(event,obj_id);
  }else{
    hideTip(obj_id);
    comfirmForm();
  }
}

function checkform(event,obj_id,theForm){
  if(theForm.frm_NS_group_id.value == ''){
    show_hide_merging_div(event,obj_id)
  }else{
    comfirmForm();
  }
}

function comfirmForm(){
  var theForm = document.del_form;
  var GeneID = theForm.frm_GeneID.value;  	
  if(GeneID == '' || trimString(GeneID) == 0){
    alert("GeneID is required to add filter.");
    return false;
  }
  if(theForm.frm_NS_group_id.value == ''){
    var group_name = trimString(theForm.frm_new_file_name.value);
    if(isEmptyStr(group_name)){
      alert("Please enter a set name");
      return false;
    }else if(group_name.match(/[^a-zA-Z0-9]/)){
      alert("Please enter charactors 'A-z', 'a-z' and '0-9' for set name");
      return false;  
    }else if(group_name.length > 20){
      alert("The set name should be less than 20");
    }
    for(var i=0; i<groupNameArr.length; i++){
      if(groupNameArr[i] == group_name){
        alert("The set name is already exist. Please give another name");
        return false;
      }
    }
  }
  theForm.theaction.value = "insert";    
  theForm.submit();
}

function getProteinInfo(theForm){
  var LocusTag=theForm.frm_LocusTag.value;
  var GeneID=theForm.frm_GeneID.value;
  var TaxID = theForm.frm_TaxID.value; 
  var GeneName=theForm.frm_GeneName.value;
  
  var file = 'pop_proteinInfo.php?GeneID=' + GeneID + '&LocusTag=' + LocusTag + '&TaxID=' + TaxID + '&GeneName=' + GeneName + '&pageName=mng_set';
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
function resetfields(){
  theForm = document.del_form;
  theForm.frm_LocusTag.readOnly = false;
  theForm.frm_LocusTag.value = ''
  theForm.frm_GeneID.readOnly = false;
  theForm.frm_GeneID.value = '';   
  theForm.frm_GeneName.readOnly = false;
  theForm.frm_GeneName.value = '';
  theForm.add_filter.disabled = false;
  if(typeof theForm.frm_new_file_name != 'undefined'){
    theForm.frm_new_file_name = '';
  }
}
function trimString (str) {
	var str = this != window? this : str;
	return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
function confirm_delete(GeneID){
  theForm = document.del_form;
  if(GeneID == '' || GeneID == 0){
    alert("No gene id.")
  }
  theForm.deleted_gene_id.value = GeneID;
  theForm.theaction.value = "delete";
  theForm.submit();
}
</script>
<center>
<table border=0 width=100% cellspacing="1" cellpadding=0 bgcolor='#a0a7c5' width=100%><tr><td valign=top align=center bgcolor="white" width=100%>
<table border="0" cellpadding="0" cellspacing="0" width="90%">
 <tr> 
  <td><br><span class=pop_header_text><?php echo $title;?></span>&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp;
  <a href="javascript: popwin('../doc/Analyst_help.php#faq40', 800, 600, 'help');"><img src='./images/icon_HELP.gif' border=0 ></a>
  </td>
 </tr>
 <tr>
   <td bgcolor=""><hr size=1><div class=maintext><?php  echo $filterArr['Description'];?></div>
   </td>
 </tr>
<?php 
$bgcolor = "#e1e1e1";
$bgcolordark2 = ""#a4a4ff"";

//$ProjectName = $_SESSION["workingProjectName"];
?>
<form id="del_form" name="del_form" method=post action="<?php echo $PHP_SELF;?>">
  <input type=hidden name=theaction value=''> 
  <input type=hidden name=order_by value='<?php echo $order_by;?>'>
  <input type=hidden name=filterID value='<?php echo $filterID;?>'>
  <input type=hidden name=deleted_gene_id value=''> 
<table border="0" cellpadding="0" cellspacing="0" width="90%">
 <tr> 
    <td align="left" colspan=3><br>     
  <?php 
  $species = get_TaxID_name($mainDB,$_SESSION["workingProjectTaxID"]);
  if($AccessProjectName){
      echo "<font color='$bgcolordark2' face='helvetica,arial,futura' size='3'><b>Project: </b></font>
      <font color='red' face='helvetica,arial,futura' size='3'><b>&nbsp;&nbsp;$AccessProjectName</font><br>
      <font color='$bgcolordark2' face='helvetica,arial,futura' size='3'><b>Species:<b></font>
      <font color='red' face='helvetica,arial,futura' size='3'><b>$species</b></font><br>&nbsp;";
  }
  $modifieder = '';
  $modifiedDate = '';
  $flag = 0;
  foreach($NSarr as $NSvalue){
    if(!$frm_NS_group_id){
      $flag = 0;
    }else{
      if($frm_NS_group_id == $NSvalue['ID']) $flag = 1;
    }
    if($flag){
      $modifieder = get_userName($PROHITSDB, $NSvalue['UserID']);
      $modifiedDate = $NSvalue['Date'];
      break;
    }
  }
  ?>        
    </td>
 </tr>
 <tr>
  <td height='30' width="20%" nowrap>
    <div class=maintext_extra><b>Modified by:</b>&nbsp;&nbsp;&nbsp;<?php echo $modifieder?></div>
  </td>
  <td height='30' colspan=2 nowrap>
    <div class=maintext_extra><b>Modified date:</b>&nbsp;&nbsp;&nbsp;<?php echo $modifiedDate?></div>
  </td>
 </tr>
 <tr><td height='30' width="20%" nowrap>
  <font color='' face='helvetica,arial,futura' size='2'><b>Background Set</b></font>:&nbsp;
  <select id='frm_NS_group_id' name='frm_NS_group_id' onchange="change_group();">
    <option id='0' value=''>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php   
    foreach($NSarr as $NSvalue){
      echo "<option id='".$NSvalue['ID']."' value='".$NSvalue['ID']."' ".(($frm_NS_group_id==$NSvalue['ID'])?'selected':'').">".$NSvalue['Name']."\n";
    }
?>
   </select>
 </td>
 <td align="" valign=bottom>
<?php if($analyst_this_page_permission_arr['Insert']){?>
    &nbsp;&nbsp;<a href="javascript: pop_update_win();" class=sTitle title='add new or modify set'><img src=./images/icon_view.gif border=0></a>&nbsp;&nbsp;
<?php }?>
 </td> 
<td align="right">
<?php if(_is_file($file_full_name)){?>
    <a href="<?php echo $PHP_SELF;?>?theaction=export&file_name=<?php echo $file_full_name?>" class=button>[Export]</a>
<?php }?> 
<?php if($analyst_this_page_permission_arr['Insert']) {?>
      <a id='add_lable_div' href="javascript:add_new();" class=button>[Add New]</a>
<?php }?>  
    <a href="javascript: window.close();" class=button>[Close window]</a>
<?php if($analyst_this_page_permission_arr['Insert']) {?>
    <br><a id='add_lable_div' href="javascript:popwin('./import_NS_data_from_other_project.php?filterID=12',650,395,'w_name');" class=button>[Import from other projects]</a>
<?php }?>     
 </td>
 </tr>
  <tr>
    <td colspan=3>
    <DIV id='add_new_div' STYLE="display: none">
    <br>
		<table bgcolor="#808000" cellspacing="1" cellpadding="0" width="100%" height="100%" border="0">
			<tr bgcolor="">
  		<td bgcolor="">
			<table bgcolor="#ffffff" cellspacing="1" cellpadding="0" width="100%" height="90%" border="0">
        <tr bgcolor="<?php echo $bgcolor;?>">
      	  <td align="right">
               <div class=maintext><b>GeneID:&nbsp;</div>
      	  </td>
      	  <td>&nbsp;<input type="text" name="frm_GeneID" size="15" maxlength=15 value="">
      	  <input type="button" value="Get Protein Info" onClick="javascript: getProteinInfo(this.form);">
      	  </td>
      	</tr>
      	<tr bgcolor="<?php echo $bgcolor;?>">
      	  <td align="right">
               <div class=maintext><b>LocusTag:&nbsp;</div>
      	  </td>
      	  <td>&nbsp;<input type="text" name="frm_LocusTag" size="15" maxlength=15 value=""><br>
      		<div class=maintext>This field is ignored if a Gene ID is specified when you click [Get Protein Info]</div>	  
      	  </td>
      	</tr>
      	<tr bgcolor="<?php echo $bgcolor;?>">
      	  <td align="right" nowrap>
      	    <div class=maintext><b>Gene Name:</b>&nbsp;</div>
      	  </td>
      	  <td nowrap>&nbsp;<input type="text" name="frm_GeneName" size="15" maxlength=30 value=""><br>
      		<div class=maintext>This field is ignored if a Gene ID or a Locus Tag is specified when you click <br>[Get Protein Info]</div>
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
   <?php if(!$frm_NS_group_id){?>      
            <DIV ID='merge_file_div' STYLE="position: absolute; 
                              display: none;
                              font-family : Arial, Helvetica, sans-serif;
                              FONT-SIZE: 10pt;
                              border: black solid 1px;
                              width: 220px";>
          <table align="center" cellspacing="0" cellpadding="1" border="0" width=100% bgcolor="#e6e6cc">
            <tr bgcolor="#c1c184" height=25><td align=center><div class=maintext><b>You didn't select any Non-specific set.<br>Do you want to create a new one?</b></div></td></tr>            
            <tr><td align=center><div class=maintext>Set Name <input type="text" name="frm_new_file_name" size="20" maxlength=30 value=""></div></td></tr>
            <tr height=35><td align="center">
            <input type=button name='hide_div' VALUE=" Confirm " onClick='comfirmForm()';">
            <input type=button name='hide_div' VALUE=" Cancel " onclick="javascript: cancel_merging('merge_file_div');">
            </td></tr>
          </table>   
         </DIV>&nbsp;&nbsp; &nbsp;
   <?php }else{?>
            <input type="hidden" name="frm_new_file_name" value="">
   <?php }?>       
            <input type="button" name="add_filter" value="Add" onclick="checkform(event,'merge_file_div',this.form)">
            <input type="reset" name="reset" value="Reset" onclick="javascript: resetfields();">
      		  <input type="button" name="cancel" value="Cancel" onclick="javascript: add_new();">
          </td>
      	</tr>	
      </table>
	    </td>
			</tr>	
    </table><br>
    </DIV>
    </td>
  </tr>
  <tr>
    <td align="center" valign=top colspan=3>
      <table border="0" cellpadding="0" cellspacing="1" width="100%">
      <tr bgcolor="">
    	  <td width="" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center><div class=tableheader>
          <a href="<?php echo $PHP_SELF;?>?frm_NS_group_id=<?php echo $frm_NS_group_id?>&filterID=<?php echo $filterID;?>&order_by=<?php echo  ($order_by == "EntrezGeneID")? 'EntrezGeneID%20desc':'EntrezGeneID';?>">
    		GeneID</a>
    		<?php if($order_by == "EntrezGeneID") echo "<img src='images/icon_order_up.gif'>";
    			if($order_by == "EntrezGeneID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
    		?></div>
    	  </td>
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center onwrap><div class=tableheader>
    	    <a href="<?php echo $PHP_SELF;?>?frm_NS_group_id=<?php echo $frm_NS_group_id?>&filterID=<?php echo $filterID;?>&order_by=<?php echo  ($order_by == "GeneName")? 'GeneName%20desc':'GeneName';?>">
    		 GeneName</a>
    		<?php if($order_by == "GeneName") echo "<img src='images/icon_order_up.gif'>";
    			if($order_by == "GeneName desc") echo "<img src='images/icon_order_down.gif'>";
    		?> </div>
    	  </td>
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center nowrap>
        <div class=tableheader>Gene Alias</div>
    	  </td>
    	  <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center>
    	    <div class=tableheader>Links</div> 
    	  </td>
    <?php if($analyst_this_page_permission_arr['Delete'] && $theaction != 'addnew'){
        $option = 1;
    ?>    
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center>
    	    <div class=tableheader>Option</div> 
    	  </td>
    <?php }?>    
    	</tr>
<?php 
$geneArr = array();
$ENSgeneArr = array();
$indexArr = array();
sort_print_filter_table($genePropertyArr,$ENSgenePropertyArr,$order_by,$option);
?>    
    </table>
    </form>
 </td>
 </tr>
</table> 
</td></tr></table>
<a href="javascript: window.close();" class=button>[Close window]</a>   
</body>
</html>