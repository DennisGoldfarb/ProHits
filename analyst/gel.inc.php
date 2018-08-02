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


if($theaction == "addnew"){
  $frm_OwnerName = get_userName($mainDB, $AccessUserID);
}else if($theaction == "modify"){
  $frm_OwnerName = get_userName($mainDB, $frm_OwnerID);
}
?>
  <input type="hidden" name="frm_OwnerName" value="<?php echo $frm_OwnerName;?>">
<?php 
if($sub == "3" || $sub == "4" || $frm_GelType == "dummy"){
  $frm_Name = (!$frm_Name)? strtoupper(substr(trim($USER->Fname), 0, 1)).strtoupper(substr(trim($USER->Lname), 0, 1))."_dummy" : $frm_Name;
?>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" height=22>
	   <div class=maintext>&nbsp;&nbsp;Gel ID:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;&nbsp; <?php echo $Gel_ID;?></div></td>
	</tr>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" nowrap>
	    <div class=maintext>&nbsp;&nbsp;Gel Name:&nbsp;</div>
	  </td>
	  <td>&nbsp;&nbsp;<input type="text" name="frm_Name" size="25" maxlength=15 value="<?php echo $frm_Name;?>"></td>
	</tr>  
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" nowrap>
	    <div class=maintext>&nbsp;&nbsp;For Project:&nbsp;</div>
	  </td>
	  <td height=23>
      <div class=maintext>&nbsp;&nbsp;<?php echo $AccessProjectName;?></div>
    </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" nowrap>
	    <div class=maintext>Uploaded by:&nbsp;</div>
	  </td>
	  <td height=23>
      <div class=maintext>&nbsp;&nbsp;<?php echo $frm_OwnerName;?></div>
    </td>
	</tr>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" nowrap>
	    <div class=maintext>Gel Type:&nbsp;</div>
	  </td>
	  <td height=22><div class=maintext>&nbsp;&nbsp;Dummy</div>
	  		<input type=hidden name=frm_GelType value="dummy">
	  </td>
	</tr>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" valign=top>
	    <div class=maintext>Notes:&nbsp;</div>
	  </td>
	  <td valign=top>&nbsp;&nbsp;<textarea name=frm_Notes cols=50 rows=4><?php echo $frm_Notes;?></textarea>
	  </td>
	</tr>
	
<?php }else{?>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right">
	   <div class=maintext>Gel ID:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;&nbsp; <?php echo $Gel_ID;?></div></td>
	</tr>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" nowrap>
	    <div class=maintext><b>Gel Name</b>:&nbsp;</div>
	  </td>
	  <td>&nbsp;&nbsp;<input type="text" name="frm_Name" size="25" maxlength=15 value="<?php echo $frm_Name;?>"></td>
	</tr>
  
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" valign=top nowrap>
	    <div class=maintext><b>Method of Staining</b>:&nbsp;</div>
	  </td>
	  <td>&nbsp;
			<select name="frm_Stain">
			<option selected value="">--Choose a Method--
			<option value="Collodial"<?php echo ($frm_Stain=='Collodial')?" selected":"";?>>Collodial
			<option value="Coomassie"<?php echo ($frm_Stain=='Coomassie')?" selected":"";?>>Coomassie
			<option value="Silver"<?php echo ($frm_Stain=='Silver')?" selected":"";?>>Silver
			<option value="Sypro-Ruby"<?php echo ($frm_Stain=='Sypro-Ruby')?" selected":"";?>>Sypro-Ruby
			</select> 		
	  </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" nowrap>
	    <div class=maintext>For Project:&nbsp;</div>
	  </td>
	  <td height=23>
      <div class=maintext>&nbsp;&nbsp;<?php echo $AccessProjectName;?></div>
    </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" nowrap>
	    <div class=maintext>Uploaded by:&nbsp;</div>
	  </td>
	  <td height=23>
      <div class=maintext>&nbsp;&nbsp;<?php echo $frm_OwnerName;?></div>
    </td>
	</tr>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" valign=top nowrap>
	    <div class=maintext>Gel Type:&nbsp;</div>
	  </td>
	  <td>&nbsp;
	  		<select name="frm_GelType">
	  		<option value='1-D' <?php echo ($frm_GelType=='1-D')?"selected":"";?>>1-D Gel
			<option value='2-D' <?php echo ($frm_GelType=='2-D')?" selected":"";?>>2-D Gel
			</select> 
	  </td>
	</tr>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" valign=top>
	    <div class=maintext>Notes:&nbsp;</div>
	  </td>
	  <td valign=top>&nbsp;&nbsp;<textarea name=frm_Notes cols=50 rows=4><?php echo $frm_Notes;?></textarea>
	  </td>
	</tr>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" valign=top nowrap>
	    <div class=maintext>Gel Image:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;
<?php 
  if($theaction == "modify") {
    echo "&nbsp;<input type=text name='frm_Image' value='".$frm_Image."' readonly>"; 
    if($AUTH->Modify && ($Gels->OwnerID == $AccessUserID || $SuperUsers)){    
      echo "&nbsp;&nbsp;&nbsp;<input type='button' value='Replace Image' onClick=\"javascript: remove_image(document.gel_form);\" >"; 
    }  
  } else {
    echo "&nbsp;<input type='file' name='frm_Image' size='30'>";
    echo "<br>&nbsp; please only upload JPG and GIF less than 5 MG formatted image. </div>";
  }
?> 
    </td>
	</tr>
<?php }?>