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
  <input type="hidden" name="new_species" value="">
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right">
      <div class=maintext>CO-IP ID:&nbsp;</div>
    </td>
    <td><div class=maintext>&nbsp;&nbsp; <?php echo ($Coip_ID)?$Coip_ID:"will be auto-generated";?></div></td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" nowrap>
      <div class=maintext><b>Clone Number</b>:&nbsp;</div>
    </td>
    <td>&nbsp;
    <?php if($AUTH->Access){ ?>
    <input type="text" name="frm_Clone" size="20" maxlength=15 value="<?php echo $frm_Clone;?>"></td>
    <?php }?>
  </tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" valign=top nowrap>
      <div class=maintext><b>Bait</b>:&nbsp;</div>
    </td>
    <td><div class=maintext>&nbsp;&nbsp;
<?php if($AUTH->Access){?>
      <input type=hidden name=Bait_new_species value="">
      GeneID: <input type="text" name="frm_BaitGeneID" size="10" maxlength=20 value="<?php echo $frm_BaitGeneID;?>">
      &nbsp;&nbsp;LocusTag: <input type="text" name="frm_BaitORF" size="10" maxlength=20 value="<?php echo $frm_BaitORF;?>">
      &nbsp;&nbsp;GeneName: <input type="text" name="frm_BaitGene" size="10" maxlength=20 value="<?php echo $frm_BaitGene;?>">
      &nbsp;&nbsp;&nbsp;<br>&nbsp;&nbsp;
      <?php $bait_TaxID = (!$bait_TaxID)? $AccessProjectTaxID : $bait_TaxID; ?>
      <select name="bait_TaxID">
        <option value="">--Choose a TaxID--<br>
        <?php TaxID_list_($mainDB, $bait_TaxID);?>
  		</select>&nbsp;&nbsp;&nbsp;
      <input type="button" value="Get Protein Info" class="green_but" onClick="javascript: getProteinInfo(this.form.frm_BaitGeneID,this.form.frm_BaitORF,this.form.frm_BaitGene,this.form.bait_TaxID,this.form.Bait_new_species);">
<?php }?>
    </div></td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" valign=top nowrap>
      <div class=maintext><b>Target</b>:&nbsp;</div>
    </td>
    <td><div class=maintext>&nbsp;&nbsp;
<?php if($AUTH->Access){ ?>
      <input type=hidden name=Target_new_species value="">
      GeneID: <input type="text" name="frm_TargetGeneID" size="10" maxlength=20 value="<?php echo $frm_TargetGeneID;?>">
      &nbsp;&nbsp;LocusTag: <input type="text" name="frm_TargetORF" size="10" maxlength=20 value="<?php echo $frm_TargetORF;?>">
      &nbsp;&nbsp;GeneName: <input type="text" name="frm_TargetGene" size="10" maxlength=20 value="<?php echo $frm_TargetGene;?>">
      &nbsp;&nbsp;&nbsp;<br>&nbsp;&nbsp;
      <?php $target_TaxID = (!$target_TaxID)? $AccessProjectTaxID : $target_TaxID;?>
      <select name="target_TaxID">
        <option value="">--Choose a TaxID--<br>
        <?php TaxID_list_($mainDB, $target_TaxID);?>
  		</select>&nbsp;&nbsp;&nbsp;
      <input type="button" value="Get Protein Info" class="green_but" onClick="javascript: getProteinInfo(this.form.frm_TargetGeneID,this.form.frm_TargetORF,this.form.frm_TargetGene,this.form.target_TaxID,this.form.Target_new_species);">
<?php }?>
    </div></td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" valign=top nowrap>
      <div class=maintext><b>Interaction</b>:&nbsp;</div>
    </td>
    <td>&nbsp;
      <?php if($AUTH->Access){ ?>
      <select name="frm_Interaction">
        <option selected value="">--choose one--
        <option value="Yes"<?php echo ($frm_Interaction=='Yes')?" selected":"";?>>Yes
        <option value="Possible"<?php echo ($frm_Interaction=='Possible')?" selected":"";?>>Possible
        <option value="No"<?php echo ($frm_Interaction=='No')?" selected":"";?>>No 
  	    <option value="In Progress"<?php echo ($frm_Interaction=='In Progress')?" selected":"";?>>In Progress 
      </select>
<?php }?> 
	  </td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" valign=top nowrap>
      <div class=maintext>Bait Expression:&nbsp;</div>
    </td>
    <td><div class=maintext>&nbsp;&nbsp;
<?php if($AUTH->Access){ ?>
      Yes <input type="radio" name="frm_BaitExpression" value="1" <?php echo ($frm_BaitExpression)?"checked":"";?>>
      &nbsp;&nbsp;No <input type="radio" name="frm_BaitExpression" value="0" <?php echo ($frm_BaitExpression === '0')?"checked":"";?>>
      &nbsp;&nbsp;Not Available <input type="radio" name="frm_BaitExpression" value="-1" <?php echo ($frm_BaitExpression == '-1' or strlen($frm_BaitExpression)===0)?"checked":"";?>>
<?php }?>
    </div>
    </td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" valign=top nowrap>
      <div class=maintext>Target Expression:&nbsp;</div>
    </td>
    <td><div class=maintext>&nbsp;&nbsp;
<?php if($AUTH->Access){ ?>
      Yes <input type="radio" name="frm_TargetExpression" value='1' <?php echo ($frm_TargetExpression)?"checked":"";?>>
      &nbsp;&nbsp;No <input type="radio" name="frm_TargetExpression" value="0" <?php echo ($frm_TargetExpression == '0')?"checked":"";?>>
      &nbsp;&nbsp;Not Available <input type="radio" name="frm_TargetExpression" value="-1" <?php echo ($frm_TargetExpression == '-1' or strlen($frm_TargetExpression) === 0)?"checked":"";?>>
<?php }?>
    </div>
    </td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" valign=top nowrap>
      <div class=maintext>Target Neg. Control:&nbsp;</div>
    </td>
    <td><div class=maintext>&nbsp;&nbsp;
<?php if($AUTH->Access){ ?>
      Yes <input type="radio" name="frm_TargetNegControl" value='1' <?php echo ($frm_TargetNegControl)?"checked":"";?>>
      &nbsp;&nbsp;No <input type="radio" name="frm_TargetNegControl" value="0" <?php echo ($frm_TargetNegControl == '0')?"checked":"";?>>
      &nbsp;&nbsp;Not Available <input type="radio" name="frm_TargetNegControl" value="-1" <?php echo ($frm_TargetNegControl == '-1' or strlen($frm_TargetNegControl) === 0)?"checked":"";?>>
<?php }?>
    </div>
    </td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right">
      <div class=maintext>Project:&nbsp;</div>
    </td>
    <td><div class=maintext>&nbsp;&nbsp; <?php echo $AccessProjectName?></div></td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" valign=top>
      <div class=maintext>Description:&nbsp;</div>
    </td>
    <td valign=top>&nbsp;&nbsp;<textarea name=frm_Description cols=40 rows=4><?php echo $frm_Description;?></textarea>
    </td>
  </tr>
   
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" valign=top nowrap>
      <div class=maintext>Western Image:&nbsp;</div>
    </td>
    <td><div class=maintext>&nbsp;
<?php 
if($theaction == "modify"){
  echo "&nbsp;<input type=text name='frm_Image' value='".$frm_Image."' readonly>";
  if($AUTH->Modify){
    echo "&nbsp;&nbsp;&nbsp;<input type='button' value='Replace Image' onClick=\"javascript: remove_image(document.Coip_form);\" >";
  }   
} else {
  echo "&nbsp;<input type='file' name='frm_Image' size='30'>";
  echo "<br>&nbsp; please only upload JPG or GIF formatted images. </div>";
}
?></div></td>
  </tr>
