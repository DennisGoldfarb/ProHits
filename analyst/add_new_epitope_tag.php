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
$frm_tag = '';
require("../common/site_permission.inc.php");
if($theaction == "addnew"){
  $SQL = "INSERT INTO EpitopeTag SET
          `Name`='$frm_tag',
          `Description`='". mysqli_escape_string($HITSDB->link, $frm_description)."'";
  $insertID = $PROHITSDB->insert($SQL);
  $theaction = 'pass_value';
}
?>
<html>
<body <?php echo ($theaction=='pass_value')?'onload="passvalue()"':''?>>
<script language="Javascript" src="site_javascript.js"></script>
<script language="javascript">
function validation(theForm){
  if(trim(theForm.frm_tag.value)== ''){
    alert("Please enter a tag name.")
    return false;
  }else{
    theForm.theaction.value = 'addnew';
    theForm.submit();
  }  
}
function passvalue(){
  var theForm = document.add_tag_form;
  var opener_form = opener.document.forms[1];
  opener_form.virtual_Tag.value = theForm.frm_tag.value;
  opener_form.submit();
  window.close();
}

</script>
<form name="add_tag_form" method=post action="<?php echo $PHP_SELF;?>">
<input type=hidden name=theaction value='<?php echo $theaction?>'> 
<table border="0" cellpadding="1" cellspacing="0" width="100%">
  <tr>
    <td width=120 bgcolor=#7483c7>
    <font face="'MS Sans Serif',Geneva,sans-serif" size="2" color="#ffffff"><b>Tag</b>:</font>
    </td>
    <td valign=top bgcolor=white> 
    <input type="text" name="frm_tag" size="40" maxlength=50 value="<?php echo $frm_tag?>">
    </td>
  </tr>
  <tr>
    <td width=120 bgcolor=#7483c7 valign=top>
    <font face="'MS Sans Serif',Geneva,sans-serif" size="2" color="#ffffff"><b>Description</b>:</font>
    </td>
    <td bgcolor=white> <div class=maintext>
    <textarea name=frm_description cols=30 rows=10></textarea>    
    </td>
   </tr>
   <tr>
    <td width=120 bgcolor=white align=center valign=center colspan=2>    
    <input type=button name=frm_save value=' Save ' onclick="validation(this.form);">    
    </td>
   </tr>
</table>
<center>
</form>
</BODY>
</html>

