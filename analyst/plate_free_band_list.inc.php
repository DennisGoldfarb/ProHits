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
<script langue='javascript'>
 
var sampleCodes = new Array();
<?php 
for($i=0; $i < $Bands->count; $i++){
  echo "sampleCodes[$i] = '".$Bands->Location[$i]."';\n";
}
$machine_name_icon_arr = array();
$bgcolor = '#e1e1e1';
?> 
 
function insert_band(){
  var theForm = document.action_form;
  var name = trimString(theForm.frm_Location.value);
  var match_flag;
  if(isEmptyStr(name)){
    alert("Sample code should not be empty!");
    return false;
  }
  if(/[^-.\w\(\)]/.test(name)){
    alert("Sample code should be made up with characters 'A-Z', 'a-z', '0-9', '-', '()', and '_'.");
    return false;
  }  
  for(k=0; k<sampleCodes.length; k++){
    if(sampleCodes[k] == name){
      alert("Sample code " + name + " has been used.");
      return false;
    }
  }
  theForm.theaction.value = "insertband";
  theForm.submit();
}
function modify_band(Band_ID){
  var theForm = document.action_form;
  var name = trimString(theForm.frm_Location.value);
  var oldName = trimString(theForm.old_Location.value);
  var match_flag;
  if(isEmptyStr(name)){
    alert("Sample code should not be empty!");
    return false;
  }
  if(/[^-.\w\(\)]/.test(name)){
    alert("Sample code should be made up with characters 'A-Z', 'a-z', '0-9', '-', '()' and '_'.");
    return false;
  }  
  if(name != oldName){
    for(k=0; k<sampleCodes.length; k++){
      if(sampleCodes[k] == name){
        alert("Sample code " + name + " has been used.");
        return false;
      }
    }
  }  
  theForm.theaction.value = "updateband";
  theForm.Band_ID.value = Band_ID;
  theForm.submit();
}
</script><br>

<table border="0" cellpadding="0" cellspacing="1" width="100%">
  <tr bgcolor="">
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" height="20" align=center><div class=tableheader>&nbsp;#&nbsp;</div>
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center height="20"><div class=tableheader><?php echo ($Gel_ID)?"Band":"Sample";?> ID</div> 
    </td>
    <td width="35%" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center height="20"><div class=tableheader><?php echo ($Gel_ID)?"Band":"Sample";?> Name</div> 
    </td>    
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align="center" height="10"><div class=tableheader>Options</div></font>
    </td>
  </tr>
<?php 
echo  "<input type=hidden name=band_counter value=$band_counter>";
// start of old band list ==============================================
$status_detail_url = "./status_fun.php";
$location_value = "";
$intensity_value = "";
$bandMW_value = "";  
$bandMod_value = "";

$sample_id_arr = array();

for($i=0; $i < $Bands->count; $i++){
  $location_value = $Bands->Location[$i];
  $intensity_value = $Bands->Intensity[$i];
  $bandMW_value = $Bands->BandMW[$i];  
  $bandMod_value = $Bands->Modification[$i];
  $Description_value = $Bands->ResultsFile[$i];  
  $frm_swath = $Bands->Analysis[$i];  
  $statusArr = get_Progress_status($Bands->ID[$i], "Band");  
  array_push($sample_id_arr, $Bands->ID[$i]);
  
  $modify_intensity = false;
  //modify this band  
  if($AUTH->Modify and $theaction == "modifyband" and $Bands->ID[$i] == $Band_ID){
    $modify_intensity = true;
?>
    <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
      <td align=center rowspan="2"><div class=maintext><?php echo $i+1;?></div></td>
      <td rowspan="2"><div class=maintext> &nbsp;<?php echo $Bands->ID[$i];?></div></td>
      <td width="" align="left" colspan=2>
        <table align="left" bgcolor="" cellspacing="1" cellpadding="0" border="0" width="100%">
          <tr>
            <td align=left width=35%>
             <input type="text" size="35" maxlength="60" name='frm_Location' value="<?php echo $location_value;?>">&nbsp;
             <input type="hidden" name='old_Location' value="<?php echo $location_value;?>">
            </td>
            <?php Description_div_for_sample($Description_value,$Band_ID);?>
            <td>Is SWATH
           <input type="checkbox" name="frm_swath" value="SWATH"<?php echo ($frm_swath)?" checked":""?>>
           </td>
          <?php //if(is_empty_sample_protocol()){?>
          <?php //if(num_protocols_for_this_project($AccessProjectID) && !is_all_sample_protocls_used($Band_ID)){?>
          <?php if(num_protocols_for_this_project($AccessProjectID)){?>
          
            <td align=left width="100%" colspan="3" bgcolor='white'>
              <div style="width:100%;border: #969696 solid 1px;">
              <div style="font-size:small;color: white; white-space:nowrap;text-align:left;height:22;margin-top:0px;border: red solid 0px; background-color:#a8a8a8;">
              Sample protocols
              </div>
              <?php sample_protocols_select_update_block($Band_ID);?>
              </div>
            </td>
     
        <?php }?>
         </tr>
       </table>
      </td>
    </tr>
    <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
      <td colspan="2" align="center">
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
    <td width="" align="left" whdth=80%>
      <table align="left" bgcolor="" cellspacing="0" cellpadding="0" border="0" width=100%>
        <tr>
        <td>
        <div class=maintext>&nbsp;<?php echo $location_value;?>&nbsp;</div>
        
        </td>
          <?php Description_div_for_sample($Description_value,$Bands->ID[$i],'','readonly');?>
        <td>
        &nbsp; <?php echo $frm_swath;?>
        </td>
        </tr>
      </table>
    </td>
    <td width="" align="left">
    <table bgcolor="" cellspacing="0" cellpadding="0" border="0">
    <tr>
    <td align="left">
    <DIV id="R_<?php echo $Bands->ID[$i]?>">
    <?php 
    $statueArr = get_status($Bands->ID[$i],'Band');
    ?>
    </DIV>  
    </td>
    <td>
  <?php if(!$statueArr['num_files'] && !$statueArr['num_hits'] && !$statueArr['num_hitsTppProt'] && $AUTH->Delete && $Bands->OwnerID[$i] == $AccessUserID && $SCRIPT_NAME != 'submit.php'){?>
        <a href="javascript:confirm_delete_band(<?php echo $Bands->ID[$i];?>);">
        <img border="0" src="images/icon_purge.gif" alt="Delete sample"></a>&nbsp;
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
      <img border="0" src="images/icon_view.gif" alt="Modify Sample"></a>&nbsp;
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
if($Exp->Name){
  $IdGeneName = str_replace($Bait->ID."_","", $Exp->Name);
}else if($Bait->GeneName){
  $IdGeneName = $Bait->ID."_".$Bait->GeneName;
}else{
  $IdGeneName = $Bait->ID."_".$Bait->LocusTag;
}

if($theaction == 'addband'){
  $lettersArr = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','BA','CA','DA','EA','FA','GA','HA','IA','JA','KA','LA','MA','NA','OA','PA','QA','RA','SA','TA','UA','VA','WA','XA','YA','ZA');
  $usedLetterArr = array();
  $nextLetter = '';
  if($Bands->count){
    foreach($Bands->Location as $value){
      $tmpBanaArr = preg_split("/_/", $value);
      if(in_array($tmpBanaArr[count($tmpBanaArr)-1], $lettersArr)){
        array_push($usedLetterArr, $tmpBanaArr[count($tmpBanaArr)-1]);
      }
    }
    $unUsedLetterArr = array_diff($lettersArr, $usedLetterArr);
    if($unUsedLetterArr){
      usort($unUsedLetterArr, "cmp");
      $nextLetter = $unUsedLetterArr[0];
    }
    $frm_Location = $IdGeneName."_".$nextLetter;
  }else{
    $frm_Location = $IdGeneName;
  }
  array_push($sample_id_arr, 'new');
  $pro_type_counter = 0;  
?> 
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
    <td align=center bgcolor="<?php echo  $TB_CELL_COLOR;?>"><div class=maintext><?php echo $Bands->count+1;?></div></td>
    <td align=center bgcolor="<?php echo $TB_CELL_COLOR;?>"><div class=maintext>&nbsp;&nbsp;</div></td>
    <td width="" align="left" colspan=2>
      <table align="left" bgcolor="" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
          <td align=left width="35%" bgcolor="<?php echo $TB_CELL_COLOR;?>">
          <input type="text" size="35" maxlength="49" name='frm_Location' value=<?php echo $frm_Location;?>>
           
          <?php Description_div_for_sample('','new');?>&nbsp;
          <input type="hidden"  name='old_Location' value=''>&nbsp;&nbsp;&nbsp;&nbsp;
           </td>
           <td>Is SWATH
           <input type="checkbox" name="frm_swath" value="SWATH"<?php echo ($frm_swath)?" checked":""?>>
           </td>
          <td bgcolor=white align="right">
      <?php if(num_protocols_for_this_project($AccessProjectID) && !is_all_sample_protocls_used($Band_ID)){?>
          <div style="width:100%;border: #969696 solid 1px;">
          <div style="color: white; white-space:nowrap;text-align:left;height:22;margin-top:0px;border: red solid 0px; background-color:#a8a8a8;">
          Sample protocols
          </div>
          <?php sample_protocols_select_update_block();?>
          </div>
      <?php }?>   
           </td>
           
        </tr>
      </table>
    </td>
  </tr>
  <tr bgcolor=<?php echo $TB_CELL_COLOR;?> align='center'>
  <td colspan=7>
    <input type='button' value='Save' onclick='javascript: insert_band();' class='green_but'>
  </td>
  </tr>
<?php 
} //end new band list=========================================================

if($Bands->count){
?>
    <tr>
    <td colspan=7><br><br>
    <b>Notice:</b>
    </td>
    </tr>
    <td colspan=7>
    In order to link a raw file to a gel free sample automatically, 
    name the folder and raw file as follows.
    <ul>
    <li>Folder ends with "_P" and Project ID.
    <li>Raw file starts with "sampleID", "_", and "first 4 characters of sample name".
      <ul>
        <br><img src='./images/folder_open.gif' border=0><font color="#0000FF"> AnyNameNoSpace<b>_P<?php echo $AccessProjectID;?></b></font> ( project ID is <?php echo $AccessProjectID;?>) 
        <?php for($i=0; $i < $Bands->count; $i++){?>
        <br>&nbsp; &nbsp; <img src='./images/file.gif' border=0><font color="#0000FF"> <b><?php echo $Bands->ID[$i]."_".substr($Bands->Location[$i], 0, 4)."</b>_AnyOtherWordNoSpace.RAW";?></font>
        <?php }?>
      </ul>
    </ul>
    </td>
    </tr>
<?php 
}
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

<script type="text/javascript">
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
<?php }
function get_next_letter($currentLetter){
  if(!$currentLetter) return "A";
  $lettersArr = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
  foreach($lettersArr as $key => $value){
    if($value == $currentLetter){
      if(isset($lettersArr[$key+1])){
        return($lettersArr[$key+1]);
      }
    }
  }
  return false;
}

//------------------------------------
function cmp($a, $b){
  if(strlen($a) == strlen($b)){
   return strcmp($a, $b);
  }
  return (strlen($a) < strlen($b)) ? -1 : 1;
}
//--------------------------------------
?>  
