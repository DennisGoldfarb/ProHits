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
?>
<script language="javascript">
function getProteinInfo(theForm){
  var LocusTag=theForm.frm_LocusTag.value;
  var GeneID=theForm.frm_GeneID.value;
  var TaxID = theForm.frm_TaxID.value; 
  var GeneName=theForm.frm_GeneName.value;
  var file = 'pop_proteinInfo.php?GeneID=' + GeneID + '&LocusTag=' + LocusTag + '&TaxID=' + TaxID + '&GeneName=' + GeneName;
  if(TaxID == ""){
    alert('Please Choose a species.');
  }else if(!isNumber(GeneID)){
    alert('Please type only numbers in GineID field.');  
  }else if(isEmptyStr(LocusTag) && isEmptyStr(GeneName) && isEmptyStr(GeneID)){
    alert('Please type Gene ID or Locus Tag or Gene Name.');
  }else{
    newwin = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=520,height=400');
    newwin.moveTo(1,1);   
  }
}

function switch_bait_type(obj){
  var theForm = obj.form;
  theForm.reset();
  obj.checked = true;
  theForm.theaction.value = 'addnew';
  theForm.frm_GeneID.value = '';
  theForm.submit();
}

function pop_add_new_tag_window(){
  var theFile = "add_new_epitope_tag.php";
  popwin(theFile,400,300,'w_name');
}

function tag_detail(){
  var s_obj = document.getElementById("frm_Tag");
  var index = s_obj.selectedIndex;
  var selected_id = s_obj.options[index].id;
  if(selected_id == ''){
    alert("Please select a tag to show detail.");
  }else{  
    var detail_url = "./epitope_tag_detail_pop.php?outsite_script=1&selected_id=" + selected_id; 
    popwin(detail_url,500,600,'new');
  }  
}    
</script>
<input type=hidden name=Bait_ID value="<?php echo $Bait_ID;?>">
<input type=hidden name=new_species value="">
<input type=hidden name=OF_session_id value="<?php echo $OF_session_id;?>">
<!--input type=hidden name=GeneID value="">
<input type=hidden name=LocusTag value="">
<input type=hidden name=TaxID value="">
<input type=hidden name=GeneName value=""-->
<tr><td>
<?php 
if($bait_switch == 'new_bait'){
?>
<table border="0" cellpadding="0" cellspacing="1" width="100%">
<?php 
  if($sub == "2" || $sub == "4" || $frm_Clone == "dummy"){
    $frm_GeneName = (!$frm_GeneName)? strtoupper(substr(trim($USER->Fname), 0, 1)).strtoupper(substr(trim($USER->Lname), 0, 1))."_dummy" : $frm_GeneName;
?>  
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" nowrap>
	    <div class=maintext><b>Gene Name:</b>&nbsp;</div>
	  </td>
	  <td nowrap>&nbsp;<input type="text" name="frm_GeneName" size="20" maxlength=50 value="<?php echo $frm_GeneName;?>"></td>
	</tr>
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" height=23 nowrap>
	    <div class=maintext><b>Clone Number</b>:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;dummy&nbsp;</div><input type="hidden" name="frm_Clone" value="dummy">
    <input type="hidden" name="frm_GeneID" value=0>
    </td>
	</tr>
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" valign=top nowrap valign="top">
	    <div class=maintext><b>Description</b>:&nbsp;</div>
	  </td>
	  <td>&nbsp;<textarea cols="40" rows="5" name="frm_Description"><?php echo stripslashes($frm_Description);?></textarea>
	  </td>
	</tr>
<?php }else{?>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" width="120" height=22>
	    <div class=maintext><b>Bait ID:&nbsp;</b></div>
	  </td>
	  <td><div class=maintext>&nbsp; <?php echo $Bait_ID;?></div></td>
	</tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right"  nowrap>
	    <div class=maintext><b>Species:&nbsp;</b></div>
	  </td>
	  <td>&nbsp;<?php  $frm_TaxID = (!$frm_TaxID)? $AccessProjectTaxID : $frm_TaxID; ?>
    <select name="frm_TaxID">
      <option value="">--Choose a Species--<br>
    <?php  
			TaxID_list_($mainDB, $frm_TaxID);
    ?>
		</select>
	  </td>
	</tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" nowrap valign="top">
	    <div class=maintext><b>Gene Name:</b>&nbsp;</div>
	  </td>
	  <td nowrap><div class=maintext>&nbsp;<input type="text" name="frm_GeneName" size="15" maxlength=50 value="<?php echo $frm_GeneName;?>">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Epitope Tag:&nbsp;
	  <select id="frm_Tag" name="frm_Tag">
      <option id="" value="">--Choose a Tag--<br>
    <?php 
      $SQL = "SELECT `ID`,`Name` FROM `EpitopeTag` ORDER BY ID";
      $tagArr = $PROHITSDB->fetchAll($SQL);
      foreach($tagArr as $tagVal){?>
      <option id="<?php echo $tagVal['ID']?>" value="<?php echo $tagVal['Name']?>" <?php echo ($frm_Tag==$tagVal['Name'])?'selected':''?>><?php echo $tagVal['Name']?> 
    <?php }?>
	  </select>
    <a href="javascript: tag_detail();">[Detail]</a>    
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bait Mutation:&nbsp;<input type="text" name="frm_Mutation" size="15" maxlength=50 value="<?php echo $frm_Mutation;?>">
    <?php if($theaction == "addnew" || ($theaction == "modify" && $AUTH->Modify )){?>
    <br>
		<font face=Arail size=2>If the bait sequence has been  modified/altered, you can first enter the wild type gene name to get<br>its protein information, 
    then modify the Gene Name as you wish and write the modification detail<br>in Description field.</font>	 
		<?php }?>
    <?php if($theaction == "addnew" || ($theaction == "modify" && $AUTH->Modify )){?>
	  <br>&nbsp;<input type="button" value="Get Protein Info" class="green_but" onClick="javascript: getProteinInfo(this.form);">
    <?php }?>
    </div></td>
	</tr>  
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right">
         <div class=maintext>GeneID:&nbsp;</div>
	  </td>
	  <td>&nbsp;<input type="text" name="frm_GeneID" size="15" maxlength=15 value="<?php echo $frm_GeneID;?>">
	  </td>
	</tr>
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" valign="top">
         <div class=maintext>LocusTag:&nbsp;</div>
	  </td>
	  <td>&nbsp;<input type="text" name="frm_LocusTag" size="15" maxlength=20 value="<?php echo $frm_LocusTag;?>">
    <?php if($theaction == "addnew" || ($theaction == "modify" && $AUTH->Modify)){?>    
    <br>
		<font face=Arail size=2>This field is ignored if a Gene ID is specified,when you click Get Protein Info button</font>	 
    <?php }?> 
	  </td>
	</tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" >
	    <div class=maintext><B>ProteinID:&nbsp;</B></div>
	  </td>
	  <td>&nbsp;<input type="text" name="frm_BaitAcc" size="15" maxlength=50 value="<?php echo $frm_BaitAcc;?>">	  
		</td>
	</tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" >
	    <div class=maintext><B>ProteinID Type:&nbsp;</B></div>
	  </td>
	  <td>&nbsp;<select name="frm_AccType">
      <option value="REFSEQ"<?php echo ($frm_AccType=='REFSEQ' or !$frm_AccType)?" selected":"";?>>REFSEQ	
			<option value="uniprotkb"<?php echo ($frm_AccType=='uniprotkb' or !$frm_AccType)?" selected":"";?>>uniprotkb	
      <option value="ENS"<?php echo ($frm_AccType=='ENS' or !$frm_AccType)?" selected":"";?>>ENS
      <option value="GI"<?php echo ($frm_AccType=='GI' or !$frm_AccType)?" selected":"";?>>GI			
			</select> 	  
		</td>
	</tr>
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" >
	    <div class=maintext>MW:&nbsp;</div>
	  </td>
	  <td>&nbsp;<input type="text" name="frm_BaitMW" size="15" maxlength=50 value="<?php echo $frm_BaitMW;?>">
	     <font size=1>kDa</font>
		 </td>
	</tr>
	
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" >
	    <div class=maintext>Family:&nbsp;</div>
	  </td>
	  <td>&nbsp;<input type="text" name="frm_Family" size="15" maxlength=50 value="<?php echo $frm_Family;?>"></td>
	</tr>
  
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" >
	    <div class=maintext>Reagent ID:&nbsp;</div>
	  </td>
	  <td>
      <div class=maintext>
      &nbsp;<input type="text" name="frm_Vector" size="20" maxlength=100 value="<?php echo $frm_Vector;?>">&nbsp;&nbsp;
      <input type="hidden" name="frm_CellLine" size="20" value="">
<?php if(defined('OPENFREEZER_SEARCH')){?>
      &nbsp;<input type="button" value="Get from OpenFreezer" class="green_but" onClick="javascript: send_req_to_OF(this.form);">
<?php }?>
      <div style="display: none"><input type="text" name="frm_OFdata_passed" value="<?php echo (($theaction == "modify")?'Y':'')?>"></div>
      </div>
    </td>
	</tr>
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right"  nowrap>
	    <div class=maintext>Clone Number:&nbsp;</div>
	  </td>
	  <td>&nbsp;<input type="text" name="frm_Clone" size="15" maxlength=50 value="<?php echo $frm_Clone;?>"></td>
	</tr>
  <?php if($theaction == "modify"){
      $createdBy = get_userName($mainDB, $frm_OwnerID);
  ?>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" width="120" height=22>
	    <div class=maintext><b>Created by:&nbsp;</b></div>
	  </td>
	  <td><div class=maintext>&nbsp; <?php echo $createdBy;?></div></td>
	</tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" width="120" height=22>
	    <div class=maintext><b>Created time:&nbsp;</b></div>
	  </td>
	  <td><div class=maintext>&nbsp; <?php echo $frm_DateTime;?></div></td>
	</tr>
  <?php }?>  
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" nowrap valign="top">
	    <div class=maintext><b>Description:&nbsp;</b></div>
	  </td>
	  <td>&nbsp;<textarea cols="40" rows="5" name="frm_Description"><?php echo stripslashes($frm_Description);?></textarea>
	  </td>
	</tr>
<?php }?>
  <tr bgcolor="white">
	  <td align="right" nowrap valign="top" colspan="2">
	    <?php 
      note_block($note_action,$Bait_ID,'Bait',$frm_disID);
      ?>
	  </td>
	</tr>
</table>
<!--/DIV-->
<?php 
}else{
?>
<!--DIV ID="no_bait" style="display:none"-->
<table border="0" cellpadding="0" cellspacing="1" width="100%">
<?php 
  if($sub == "2" || $sub == "4" || $frm_Clone == "dummy"){
    $frm_GeneName = (!$frm_GeneName)? strtoupper(substr(trim($USER->Fname), 0, 1)).strtoupper(substr(trim($USER->Lname), 0, 1))."_dummy" : $frm_GeneName;
?>  
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" nowrap>
	    <div class=maintext><b>Name:</b>&nbsp;</div>
	  </td>
	  <td nowrap>&nbsp;<input type="text" name="frm_GeneName" size="20" maxlength=50 value="<?php echo $frm_GeneName;?>"></td>
	</tr>
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" height=23 nowrap>
	    <div class=maintext><b>Clone Number</b>:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;dummy&nbsp;</div><input type="hidden" name="frm_Clone" value="dummy">
    <input type="hidden" name="frm_GeneID" value=0>
    </td>
	</tr>
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" valign=top nowrap valign="top">
	    <div class=maintext><b>Description</b>:&nbsp;</div>
	  </td>
	  <td>&nbsp;<textarea cols="40" rows="5" name="frm_Description"><?php echo stripslashes($frm_Description);?></textarea>
	  </td>
	</tr>
<?php }else{?>  
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right"  nowrap>
	    <div class=maintext><b>Species:&nbsp;</b></div>
	  </td>
	  <td>&nbsp;<?php  $frm_TaxID = (!$frm_TaxID)? $AccessProjectTaxID : $frm_TaxID; ?><select name="frm_TaxID">
    <option value="">--Choose a TaxID--<br>
    <?php  
			TaxID_list_($mainDB, $frm_TaxID);
    ?>
		</select>
	  </td>
	</tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" nowrap >
	    <div class=maintext><b>Name:</b>&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;<input type="text" name="frm_GeneName" size="20" maxlength=50 value="<?php echo $frm_GeneName;?>">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Epitope Tag:&nbsp;
	  <select id="frm_Tag" name="frm_Tag">
      <option value="">--Choose a Tag--<br>
    <?php 
      $SQL = "SELECT `ID`,`Name` FROM `EpitopeTag` ORDER BY ID";
      $tagArr = $PROHITSDB->fetchAll($SQL);
      foreach($tagArr as $tagVal){?>
      <option id="<?php echo $tagVal['ID']?>" value="<?php echo $tagVal['Name']?>" <?php echo ($frm_Tag==$tagVal['Name'])?'selected':''?>><?php echo $tagVal['Name']?> 
<?php }?>
	  </select>
    <a href="javascript: tag_detail();">[Detail]</a> 
    </div>
    </td>
	</tr>
  <input type="hidden" name="frm_Mutation" value="">
  <input type="hidden" name="frm_GeneID" value="-1">
	<input type="hidden" name="frm_LocusTag" value="">
  <input type="hidden" name="frm_BaitAcc" value="">
  <input type="hidden" name="frm_AccType" value="">
	<input type="hidden" name="frm_BaitMW" value="">	
  <input type="hidden" name="frm_Family" value="">
	<input type="hidden" name="frm_Vector" value="">
	<input type="hidden" name="frm_Clone" value="">
<?php if($theaction == "modify"){
    $createdBy = get_userName($mainDB, $frm_OwnerID);
?>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" width="120" height=22>
	    <div class=maintext><b>Created by:&nbsp;</b></div>
	  </td>
	  <td><div class=maintext>&nbsp; <?php echo $createdBy;?></div></td>
	</tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" width="120" height=22 >
	    <div class=maintext><b>Created time:&nbsp;</b></div>
	  </td>
	  <td><div class=maintext>&nbsp; <?php echo $frm_DateTime;?></div></td>
	</tr>
<?php }?>  
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" nowrap valign="top">
	    <div class=maintext><b>Description:&nbsp;</b></div>
	  </td>
	  <td>&nbsp;<textarea cols="40" rows="5" name="frm_Description"><?php echo stripslashes($frm_Description);?></textarea>
	  </td>
	</tr>
  <tr bgcolor="white">
	  <td align="right" nowrap valign="top" colspan="2">
	    <?php 
      note_block($note_action,$Bait_ID,'Bait',$frm_disID);
      ?>
	  </td>
	</tr>
<?php }?>
</table>
<!--/DIV-->
<?php 
}
?>
</td>
</tr>  
