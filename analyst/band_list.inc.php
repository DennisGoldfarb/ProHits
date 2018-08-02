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


$RawFileColor = "#2080df";
$HasHitsColor = "#5b52ad";
$EmptyColor = "#d9e8f0";
?>
 
   <script langue='javascript'>
   <?php 
   if($theaction == "addnewband"){ ?>
       function insert_band(){
        var theForm = document.action_form;
        <?php if($Lane_ID){?>
        if(isEmptyStr(theForm.frm_Location.value) || !radio_checked(theForm.frm_Intensity) || isEmptyStr(theForm.frm_BandMW.value)){
          alert("The new band has to have band code, intensity and observed MW!");
          return false;
        }
        if(!isNumber(theForm.frm_BandMW.value)){
          alert("Observed MW has to be a number!");
          return false;
        } 
        <?php }else{?>
        if(isEmptyStr(theForm.frm_Location.value) ){
          alert("Sample code should not be empty!");
          return false;
        }
        <?php }?>
        theForm.theaction.value = "insertnewband";
        theForm.submit();
     }
  <?php }
  if($theaction == "modifyband"){ ?>
     function modify_band(Band_ID){
       var theForm = document.action_form;
        <?php if($Lane_ID){?>
        if(isEmptyStr(theForm.frm_Location.value) || !radio_checked(theForm.frm_Intensity) || isEmptyStr(theForm.frm_BandMW.value)){
          alert("The new band has to have band code, intensity and observed MW!");
          return false;
        }
        if(!isNumber(theForm.frm_BandMW.value)){
          alert("Observed MW has to be a number!");
          return false;
        } 
        <?php }else{?>
        if(isEmptyStr(theForm.frm_Location.value) ){
          alert("Sample code should not be empty!");
          return false;
        }
        <?php }?>
        theForm.theaction.value = "updateband";
        theForm.Band_ID.value = Band_ID;
        theForm.submit();
    }
  <?php }?>
   </script><br>
<table border="0" cellpadding="0" cellspacing="1" width="100%">
  <tr bgcolor="">
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" height="20" align=center><div class=tableheader>&nbsp;#&nbsp;</div>
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center height="20"><div class=tableheader>Band(Sample)<br>ID</div> 
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center height="20"><div class=tableheader>Band(Sample)<br>Code</div> 
    </td>
 <?php if($Gel_ID){?>   
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center height="20"><div class=tableheader>Intensity</div> 
    </td>
 <?php }?>   
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center height="20"><div class=tableheader>Observed MW</div> 
    </td>
      <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center height="20"><div class=tableheader>Modification</div> 
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align="center" height="20"><div class=tableheader>Options</div></font>
 
    </td>
  </tr>
<?php 
echo  "<input type=hidden name=band_counter value=$band_counter>";
// start of old band list ==============================================
$location_value = "";
$intensity_value = "";
$bandMW_value = "";  
$bandMod_value = "";


for($i=0; $i < $Bands->count; $i++) {
  $location_value = $Bands->Location[$i];
  $intensity_value = $Bands->Intensity[$i];
  $bandMW_value = $Bands->BandMW[$i];  
  $bandMod_value = $Bands->Modification[$i];
  $Description_value = $Bands->ResultsFile[$i];
  $statusArr = get_Progress_status($Bands->ID[$i], "Band");
  array_push($sample_id_arr, $Bands->ID[$i]);
  $modify_intensity = false;
  //modify this band  
  if($AUTH->Modify and $theaction == "modifyband" and $Bands->ID[$i] == $Band_ID){
    $modify_intensity = true;
?>
    <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
      <td align=center><div class=maintext><?php echo $i+1;?></div></td>
      <td><div class=maintext> &nbsp;<?php echo $Bands->ID[$i];?></div></td>
      <td width="" align="left">&nbsp;
        <table align="left" bgcolor="" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td align=right>
            <input type="text" size="10" maxlength="29" name='frm_Location' value="<?php echo $location_value;?>">&nbsp;
            </td>
            <?php Description_div_for_sample($Description_value,$Band_ID);?>
          </tr>
        </table>
      </td>
  <?php if($Gel_ID){      
      echo "<td width='' align='center'>";
      include("./band_color.inc.php");
      echo "</td>";
    }?>    
      <td width="" align="center"> &nbsp;
        <input type="text" size="5" maxlength="20" name='frm_BandMW' value="<?php echo $bandMW_value;?>">
        <font color="black" face="helvetica,arial,futura" size="1"> kDa</font>
      </td>
        <td width="" align="center">
        <select name='frm_Modification'>
          <?php  dis_species_options($bandMod_value); ?>
        </select>
      </td>
      <td width="" align="center">
          <input type="button" value="Update" onclick="javascript: modify_band(<?php echo $Band_ID;?>);" class="black_but">
      </td>
    </tr>
<?php 
  }else{
  //not modify the band 
?>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
    <td align=center><div class=maintext><?php echo $i+1;?></div> </td>
     <td><div class=maintext> &nbsp;<?php echo $Bands->ID[$i];?></div></td>
    <td width="" align="center">
      <table align="left" bgcolor="" cellspacing="0" cellpadding="0" border="0">
        <tr>
        <td align=right>
          <div class=maintext>&nbsp;<?php echo $location_value;?>&nbsp;</div>
        </td>
          <?php Description_div_for_sample($Description_value,$Bands->ID[$i],'' ,'readonly');?>
        </tr>
      </table>
    </td>
    <?php 
    if($Gel_ID){      
      echo "<td width='' align='center'>";
      include("./band_color.inc.php");
      echo "</td>";
    }?>  
    <td width="" align="center">
      <div class=maintext>&nbsp;
      <?php echo $bandMW_value;?> kDa&nbsp;
      </div>
    </td>
      <td width="" align="center">
      <div class=maintext>&nbsp;
      <?php echo $bandMod_value;?> &nbsp;
      </div>
    </td>
    <td width="" align="center">
    <table><tr><td>
  <?php 
    //$statueArr = get_status($Bands->ID[$i],$HITSDB,$Bait->GelFree,'Band');
    $statueArr = get_status($Bands->ID[$i],'Band');
  ?>  
    </td><td>
  <?php     
    if(!$statueArr['num_files'] && !$statueArr['num_hits'] && !$statueArr['num_hitsTppProt'] && $AUTH->Delete && $Bands->OwnerID[$i] == $AccessUserID && $SCRIPT_NAME != 'submit.php'){?>
        <a href="javascript:confirm_delete_band(<?php echo $Bands->ID[$i];?>);">
        <img border="0" src="images/icon_purge.gif" alt="Delete Band"></a>&nbsp;
  <?php }else{
      echo "\n<img src=\"images/icon_empty.gif\">&nbsp;";
    }
    if($AUTH->Modify){
      if($SCRIPT_NAME == 'submit.php'){
        $band_list_inc_submit_url = $SCRIPT_NAME."?ProjectID=$ProjectID&gelMode=$gelMode&addNewType=$addNewType&DBname=$DBname&";
      }else{
        $band_list_inc_submit_url = $SCRIPT_NAME.'?';
      }
  ?>
      <a href="./<?php echo $band_list_inc_submit_url?>theaction=modifyband&Band_ID=<?php echo $Bands->ID[$i];?>&Plate_ID=<?php echo $Plate_ID;?><?php echo  ($sub)?"&sub=$sub":"";?>">
      <img border="0" src="images/icon_view.gif" alt="Modify Band"></a>&nbsp;
  <?php }else{
      echo "\n<img src=\"images/icon_empty.gif\">&nbsp;";
    }?>
    </td>
    </tr></table>
    </td>
  </tr> 
<?php 
   } //end if modify
} //end for loop


//end band old band list=====================================================
$location_value = "";
$intensity_value = "";
$bandMW_value = "";  
$bandMod_value = "";

$modify_intensity = true;
if($band_counter){
  for($i=0; $i < $band_counter; $i++){
    $new_sample_id = "new_$i";
    array_push($sample_id_arr, $new_sample_id);
?> 
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
    <td align=center><div class=maintext><?php echo $Bands->count+ $i + 1;?></div></td>
    <td>&nbsp;</td>
    <td width="" align="left">&nbsp;
      <table align="left" bgcolor="" cellspacing="0" cellpadding="0" border="0">
        <tr>
        <td align=right>
        <input type="text" size="10" maxlength="29" name='<?php echo $location_name[$i];?>' value=<?php echo ($$location_name[$i])?$$location_name[$i]:$selectedWellCode_arr[$i];?>>&nbsp;
        </td>
          <?php Description_div_for_sample('',$new_sample_id,$bandDescription_name[$i]);?>
        </tr>
      </table>
    </td>
  <?php if($Gel_ID){      
      echo "<td width='' align='center'>";
      include("./band_color.inc.php");
      echo "</td>";
    }?>    
    <td align="center" nowrap>&nbsp;
      <font color="black" face="arial,futura" size="1">
     <input type="text" size="7" maxlength="20" name='<?php echo $bandMW_name[$i];?>' value='<?php echo $$bandMW_name[$i];?>'> kDa
      </font>&nbsp;
    </td>
    <td align="center" colspan=2>
    <select name='<?php echo $bandModification_name[$i];?>'>
     <?php dis_species_options($$bandModification_name[$i]);?>
     </select>
    </td>
  </tr>
<?php 
 } 
?>
   <tr bgcolor=<?php echo $TB_CELL_COLOR;?> align='center'>
   <td colspan=7>
    <input type='button' value='Save' onclick='javascript: checkform(this.form);' class='green_but'>
   </td>
   </tr>
<?php 
} //end new band list=========================================================
?>
</table>
<script language="javascript">
var Sample_id_arr = new Array();
<?php foreach($sample_id_arr as $sample_id_value){?>
    Sample_id_arr.push("<?php echo $sample_id_value?>"); 
<?php }?>

document.onclick=check;
var div_id = '';
var tmp_flag = 0;
function check(e){
  if(tmp_flag == 1){
    tmp_flag = 0;
    return;
  }
  for(var i=0; i<Sample_id_arr.length; i++){
    var div_id_tmp = "des_" + Sample_id_arr[i];
    var obj_tmp = document.getElementById(div_id_tmp);
    if(obj_tmp.style.display == 'block'){
      div_id = div_id_tmp;
      var target = (e && e.target) || (event && event.srcElement);
      var obj = document.getElementById(div_id);
      checkParent(target)?obj.style.display='none':null;
      break;
    }
  }
}
function checkParent(t){
  while(t.parentNode){
    if(t==document.getElementById(div_id)){
    return false
  }
    t=t.parentNode
  }
  return true
}
</script>   
<?php 
function dis_species_options($bandMod_value){ ?>
    <option value="None">None
    <option value="Acetyl (N-term)"<?php echo ($bandMod_value == 'Acetyl (N-term)')?" selected":'';?>>Acetyl (N-term)
    <option value="Acetyl (K)"<?php echo ($bandMod_value == 'Acetyl (K)')?" selected":'';?>>Acetyl (K)
    <option value="Amide (C-term G)"<?php echo ($bandMod_value == 'Amide (C-term G)')?" selected":'';?>>Amide (C-term G)
    <option value="Biotinylated (N-term)"<?php echo ($bandMod_value == 'Biotinylated (N-term)')?" selected":'';?>>Biotinylated (N-term)
    <option value="Biotinylated (K)"<?php echo ($bandMod_value == 'Biotinylated (K)')?" selected":'';?>>Biotinylated (K)
    <option value="Carbamyl (N-term)"<?php echo ($bandMod_value == 'Carbamyl (N-term)')?" selected":'';?>>Carbamyl (N-term)
    <option value="Carboxymethyl (C)"<?php echo ($bandMod_value == 'Carboxymethyl (C)')?" selected":'';?>>Carboxymethyl (C)
    <option value="Deamidation (NQ)"<?php echo ($bandMod_value == 'Deamidation (NQ)')?" selected":'';?>>Deamidation (NQ)
    <option value="Glycosylation (N-linked)"<?php echo ($bandMod_value == 'Glycosylation (N-linked)')?" selected":'';?>>Glycosylation (N-linked)
    <option value="Glycosylation (O-linked)"<?php echo ($bandMod_value == 'Glycosylation (O-linked)')?" selected":'';?>>Glycosylation (O-linked)
    <option value="ICAT_light"<?php echo ($bandMod_value == 'ICAT_light')?" selected":'';?>>ICAT_light
    <option value="ICAT_heavy"<?php echo ($bandMod_value == 'ICAT_heavy')?" selected":'';?>>ICAT_heavy
    <option value="Methyl ester (C-term)"<?php echo ($bandMod_value == 'Methyl ester (C-term)')?" selected":'';?>>Methyl ester (C-term)
    <option value="Methyl ester (DE)"<?php echo ($bandMod_value == 'Methyl ester (DE)')?" selected":'';?>>Methyl ester (DE)
    <option value="NIPCAM (C)"<?php echo ($bandMod_value == 'NIPCAM (C)')?" selected":'';?>>NIPCAM (C)
    <option value="N-Acetyl (Protein)"<?php echo ($bandMod_value == 'N-Acetyl (Protein)')?" selected":'';?>>N-Acetyl (Protein)
    <option value="N-Formyl (Protein)"<?php echo ($bandMod_value == 'N-Formyl (Protein)')?" selected":'';?>>N-Formyl (Protein)
    <option value="Oxidation (M)"<?php echo ($bandMod_value == 'Oxidation (M)')?" selected":'';?>>Oxidation (M)
    <option value="Oxidation (HW)"<?php echo ($bandMod_value == 'Oxidation (HW)')?" selected":'';?>>Oxidation (HW)
    <option value="O18 (C-term)"<?php echo ($bandMod_value == 'O18 (C-term)')?" selected":'';?>>O18 (C-term)
    <option value="Phospho (Y)"<?php echo ($bandMod_value == 'Phospho (Y)')?" selected":'';?>>Phospho (Y)
    <option value="Phospho (ST)"<?php echo ($bandMod_value == 'Phospho (ST)')?" selected":'';?>>Phospho (ST)
    <option value="Propionamide (C)"<?php echo ($bandMod_value == 'Propionamide (C)')?" selected":'';?>>Propionamide (C)
    <option value="Pyro-glu (N-term Q)"<?php echo ($bandMod_value == 'Pyro-glu (N-term Q)')?" selected":'';?>>Pyro-glu (N-term Q)
    <option value="Pyro-glu (N-term E)"<?php echo ($bandMod_value == 'Pyro-glu (N-term E)')?" selected":'';?>>Pyro-glu (N-term E)
    <option value="S-pyridylethyl (C)"<?php echo ($bandMod_value == 'S-pyridylethyl (C)')?" selected":'';?>>S-pyridylethyl (C)
    <option value="SMA (K)"<?php echo ($bandMod_value == 'SMA (K)')?" selected":'';?>>SMA (K)
    <option value="SMA (N-term)"<?php echo ($bandMod_value == 'SMA (N-term)')?" selected":'';?>>SMA (N-term)
    <option value="Sodiated (DE)"<?php echo ($bandMod_value == 'Sodiated (DE)')?" selected":'';?>>Sodiated (DE)
    <option value="Sodiated (C-term)"<?php echo ($bandMod_value == 'Sodiated (C-term)')?" selected":'';?>>Sodiated (C-term)
    <option value="Sulphone (M)"<?php echo ($bandMod_value == 'Sulphone (M)')?" selected":'';?>>Sulphone (M)
<?php 
}
?>
