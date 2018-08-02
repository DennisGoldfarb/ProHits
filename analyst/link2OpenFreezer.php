<?php 
$OF_session_id = '';
$Species = '';
$Description = '';
$Accession = '';
$GeneID = '';
$GeneName = '';
$MW = '';
$Vector = '';
$GI = '';
$CellLine = '';
$LocusTag = '';;
$Description = '';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";
exit;*/

if($OF_session_id){
  $_SESSION["OF_session_id"] = $OF_session_id;
  //echo $_SESSION["OF_session_id"];
}
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
if($TaxID){
  $SQL = "SELECT `TaxID`,`name_txt` FROM `NCBI_tax_names` WHERE `name_txt`='$TaxID'";
  $Tax_arr = $proteinDB->fetch($SQL);
  if($Tax_arr){
    $TaxID = $Tax_arr['TaxID'];
  }else{
    $TaxID = '';
  }
}
if(!$MW && $Sequence){
  $MW = round(calcMass($Sequence)/1000,2);
}
?>
<html>
<head>
  <meta http-equiv='content-type' content='text/html;charset=iso-8859-1'>
<title>Prohits</title>
<link rel='stylesheet' type='text/css' href='./site_style.css'>
</head>
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 topMargin=5 rightMargin=5 marginheight=0 marginwidth=0>
<!--script language="Javascript" src="site_no_right_click.inc.js"></script-->
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script> 
<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language='javascript'>
//opener.document.bait_form.reset();
if(typeof opener.document.bait_form === "undefined"){
  window.close();
  alert("something is undefined");
}
<?php if($OF_session_id){?>
  opener.document.bait_form.OF_session_id.value = "<?php echo $OF_session_id?>";
<?php }?>
//function passvalue(){
<?php if($GeneID){?>
  //opener.document.bait_form.frm_GeneID.value = "<?php echo $GeneID?>";
<?php }?>
<?php if($GeneName){?>  
  opener.document.bait_form.frm_Family.value = "<?php echo $GeneName?>";
<?php }?>
<?php if($GI || $Accession){?>   
  opener.document.bait_form.frm_BaitAcc.value = "<?php echo (($GI)?$GI:$Accession)?>";
<?php }?>
<?php if($MW){?>   
  opener.document.bait_form.frm_BaitMW.value = "<?php echo $MW?>";
<?php }?>
<?php if($Description){?>   
  opener.document.bait_form.frm_Description.value = "<?php echo $Description?>";
<?php }?>
<?php if($Vector){?>   
  opener.document.bait_form.frm_Vector.value = "<?php echo $Vector?>";
<?php }?>
<?php if($CellLine){?>   
  opener.document.bait_form.frm_CellLine.value = "<?php echo $CellLine?>";
<?php }?>
<?php if($LocusTag){?>   
  opener.document.bait_form.frm_LocusTag.value = "<?php echo $LocusTag?>";
<?php }?>  
<?php if($TaxID){?> 
  var x = opener.document.bait_form.frm_TaxID;
  for(var i=0; i<x.length; i++){
    if(x[i].value == '<?php echo $TaxID?>'){
      x[i].selected = true;
      break;
    }
  }
<?php }?>
  opener.document.bait_form.frm_GeneID.readOnly = true;
  opener.document.bait_form.frm_OFdata_passed.value = 'Y';
  window.close();
//}
</script>
</body>
</html>
<?php 
exit;
?>

<form name="protein_info_frm">
<center>
<table border="0" cellpadding="0" cellspacing="1" width="98%">
	<tr bgcolor="#a48b59">
	  <td colspan="2" align="center" height=20>
	   <div class=tableheader>OpenFreezer returned Information</div>
	  </td>
	</tr>
  <tr bgcolor="#e9e1c9">
	  <td align="right" width="20%">
	   <div class=maintext><b>Gene ID:</b>&nbsp;</div>
	  </td>
	  <td width="80%"><div class=maintext>&nbsp;<?php echo $GeneID?></div></td>
	</tr>
  <tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>Gene Name</b>:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;<?php echo $GeneName?></div></td>
	</tr>
  <tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>Species</b>:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;<?php echo $Species?></div></td>
	</tr>
  
	<tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>Description</b>:&nbsp;</div>
	  </td>
    
	  <td><div class=maintext>&nbsp;<?php echo $Description?></div></td>
	</tr>
  <tr bgcolor="#e9e1c9">
	  <td align="right" nowrap><div class=maintext>
	    <b>Accession</b>:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;<?php echo $Accession?></div></td>
	</tr>
  <tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>GI Number</b>:&nbsp;</div>
	  </td>
	  <td><div id='gi_0' class=maintext><?php echo $GI?></div></td>
	</tr>
  <tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>MW</b>:&nbsp;</div>
	  </td>
	  <td><div id='gi_0' class=maintext><?php echo $MW?></div></td>
	</tr>
  <tr bgcolor="#e9e1c9">
	  <td align="right" nowrap>
	    <div class=maintext><b>Vector</b>:&nbsp;</div>
	  </td>
	  <td><div id='gi_0' class=maintext><?php echo $Vector?></div></td>
	</tr>    
	<tr bgcolor="#e9e1c9" align="center">
	  <td colspan="2">
		<input type="button" value="Pass Value" onclick="javascript: passvalue();" class=black_but>&nbsp;
		<input type="button" value=" Close " onclick="javascript: window.close();" class=black_but></td>
	</tr>
</table>
</center>
</form>
</body>
</html>