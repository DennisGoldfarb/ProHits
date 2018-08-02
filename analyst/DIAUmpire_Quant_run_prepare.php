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

set_time_limit(2400);
$DIAUmpireQuantName = '';
$DIAUmpireQuantDescription = '';
$frm_selected_sample_str = '';
$sample_as_name = '';
$item_name = '';
$saint_bait_name_str = '';
$control_id_str = '';
$run_saint = 'SAINT';

$Quant_InternalLibSearch = "true";
$Quant_PeptideFDR = 0.05;
$Quant_ProteinFDR = 0.05;
$Quant_ProbThreshold  = 0.9;
$Quant_FilterWeight = 'GW';
$Quant_MinWeight = 0.9;
$Quant_TopNFrag = "6";
$Quant_TopNPep = "6";
$Quant_Freq = "0.5";
 
$Quant_TopNFrag_mapDIA = "20";
$Quant_TopNPep_mapDIA = "20";
$Quant_Freq_mapDIA = "0";

require("../common/site_permission.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$item_propty_arr = array();
if($frm_selected_sample_str){
  $item_id_str = $frm_selected_sample_str;
  $item_name = "Sample";
   
    $item_name = "Sample";
    $SQL = "SELECT D.ID,
            D.Location AS Name,
            B.GeneName AS Bait_name 
            FROM Band D
            LEFT JOIN Bait B ON (D.BaitID=B.ID) 
            WHERE D.ID IN ($item_id_str) ORDER BY D.Location";
  $item_propty_arr = $HITSDB->fetchAll($SQL);
}

$sample_propty_arr = array();
foreach($item_propty_arr as $item_propty_val){
  $sample_propty_arr[$item_propty_val['ID']] = $item_propty_val;
}

$sample_arr = explode(",", $frm_selected_sample_str);
$raw_arr = explode(",", $frm_selected_list_str);
if($theaction == 'moveDown' and isset($fileIndex)){
  for($i = 0; $i < count($raw_arr); $i++){
    if($i === intval($fileIndex)){
      
      $raw_fuffer = $raw_arr[$i+1];
      $raw_arr[$i+1] = $raw_arr[$i];
      $raw_arr[$i] = $raw_fuffer;
      
      $sample_fuffer = $sample_arr[$i+1];
      $sample_arr[$i+1] = $sample_arr[$i];
      $sample_arr[$i] = $sample_fuffer;
    }
  }
  $frm_selected_sample_str = implode(",", $sample_arr);
  $frm_selected_list_str = implode(",", $raw_arr);
}

$rawkID_sampleID_arr = array();
foreach($raw_arr as $tmp_val){
  $tmp_arr2 = explode("|", $tmp_val);
  $rawkID_sampleID_arr[$tmp_arr2[1]] = $tmp_arr2[2];
}
$total_files = count($raw_arr);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Export hits DIAUmpireQuant</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
<script language="JavaScript" type="text/javascript">
<!--
function checkempty(theform){
  var has_control = false;
  if(isEmptyStr(theform.DIAUmpireQuantName.value) || isEmptyStr(theform.DIAUmpireQuantDescription.value)){
    alert("DIAUmpireQuant Name and Description are required!");
    return false;
  }
  var saint_bait_name_str = ''; 
  var control_id_str = '';
  for(i=0; i<theform.elements.length; i++){
    if(theform.elements[i].name.match(/TEXT/)){  
      if(trimString(theform.elements[i].value) == '' ){
        alert("The name field cannot be emptly");
        return false; 
      }
      if(!onlyAlphaNumerics(theform.elements[i].value, 2)){
        alert("Only [_A-Za-z0-9] characters are allowed for Bait Name/Label.");
        return false; 
      }
      var tmp_arr = theform.elements[i].name.split('_');
      if(saint_bait_name_str) saint_bait_name_str += ',';
      saint_bait_name_str += tmp_arr[1] + '|' + theform.elements[i].value;
    }else if(theform.elements[i].name.match(/CHECK_/)){ 
      if(theform.elements[i].checked){
        has_control = true;
        if(control_id_str) control_id_str += ',';
        control_id_str += theform.elements[i].value;
      }
    }
  }
  theform.saint_bait_name_str.value = saint_bait_name_str;
  theform.control_id_str.value = control_id_str;  
  var theType = getRadioCheckedValue(theform.run_saint);
 
  if(theType == 'SAINT' || theType == 'mapDIA'){ 
    if(theType == 'SAINT'  && !has_control){
      alert("You can not generate SAINT files without controls.");
      return;
    }
    theform.action = "DIAUmpire_Quant_run.php";
    theform.theaction.value = '';
  }else{
    theform.action = "DIAUmpire_Quant_run.php";
    theform.theaction.value = 'runQuant';
    theform.saint_bait_name_str.value = '';
  }
  theform.target = 'subWin';
  theform.submit();
}

function toggle_item_type_name(theform){
  theform.action = "DIAUmpire_Quant_run_prepare.php";
  theform.target = 'subWin';
  theform.theaction.value = 'changeLabel';
  theform.submit();
}
function moveItem(theIndex, goDown){
  var theform = document.run_DIAUmpireQuant_prepare_form;
  theform.action = "DIAUmpire_Quant_run_prepare.php";
  theform.target = 'subWin';
  
  if(goDown != 'down'){
    theIndex = theIndex - 1;
  }else if(theIndex + 1 >= <?php echo $total_files;?>){
    return;
  }
  
  if(theIndex > -1){
    theform.fileIndex.value = theIndex;
    theform.theaction.value = 'moveDown';
    theform.submit();
  }
}

function checkControl(obj, sampleID){
  var text_obj = document.getElementById('TEXT_'+ sampleID);
  if(obj.checked){
    text_obj.value = sampleID + '_' + text_obj.value;
  }else{
    text_obj.value = text_obj.value.replace(sampleID + '_', '');
  }
}
function is_running_saint(theform){
  var run_obj = document.getElementById('running_saint');
  var not_run_obj = document.getElementById('not_saint');
  var boxObj = theform.run_saint;
  for (var i=0; i < boxObj.length; i++) {
    if(boxObj[i].checked){
      if(boxObj[i].value != ''){
        run_obj.style.display = 'block';
        not_run_obj.style.display = 'none';
        if(boxObj[i].value == 'SAINT'){
          theform.Quant_TopNFrag.value = '<?php echo $Quant_TopNFrag;?>';
          theform.Quant_TopNPep.value = '<?php echo $Quant_TopNPep;?>';
          theform.Quant_Freq.value = '<?php echo $Quant_Freq;?>';
        }else{
          theform.Quant_TopNFrag.value = '<?php echo $Quant_TopNFrag_mapDIA;?>';
          theform.Quant_TopNPep.value = '<?php echo $Quant_TopNPep_mapDIA;?>';
          theform.Quant_Freq.value = '<?php echo $Quant_Freq_mapDIA;?>';
        }
      }else{
        run_obj.style.display = 'none';
        not_run_obj.style.display = 'block';
      }
    }
  }
}

//-->
</script>
</head>
<body>
<FORM ACTION="DIAUmpire_Quant_run_prepare.php" ID="" NAME="run_DIAUmpireQuant_prepare_form" METHOD="POST">
<input type="hidden" name="frm_selected_list_str" value="<?php echo $frm_selected_list_str;?>">
<input type="hidden" name="frm_machine" value="<?php echo $frm_machine;?>">
<input type="hidden" name="frm_SearchEngine" value="<?php echo $frm_SearchEngine;?>">
<input type="hidden" name="frm_selected_sample_str" value="<?php echo $frm_selected_sample_str;?>">
<input type="hidden" name="item_name" value="<?php echo $item_name;?>">
<input type="hidden" name="saint_bait_name_str" value="<?php echo $saint_bait_name_str;?>">  
<input type="hidden" name="control_id_str" value="<?php echo $control_id_str;?>"> 
<input type="hidden" name="theaction" value=""> 
<input type="hidden" name="fileIndex" value=""> 

<table border=0 width=95% cellspacing="1" cellpadding=1 align=center>
  <tr>
    <td align=left nowrap><span class=pop_header_text>DIA-Umpire Quant parameters</span>    <a href="javascript: showhideClass('instruction');"><img src=../msManager/images/help2.gif></a></td>
  </tr>  
  <tr>
    <td height='1'><hr size=1>
    
    <table border=0 width=100% cellspacing="1" cellpadding=3 align=center bgcolor=#5c8ca3>
      <tr>
          <td colspan='4'><font color="#FFFFFF"><b>DIA-Umpire Quant</b></font>   </td>
      </tr>
      <tr bgcolor=white>
          <td align=right ><b><font color="#008080">Task Name</font></b></td>
          <td colspan=3><input type=text size=60 value='<?php echo $DIAUmpireQuantName;?>' name=DIAUmpireQuantName> </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top><b><font color="#008080">Task Description</font></b></td>
          <td colspan=3><textarea cols="60" rows="2" name="DIAUmpireQuantDescription"><?php echo $DIAUmpireQuantDescription;?></textarea></td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top><b>InternalLibSearch</b></td>
          <td colspan=3>true:<input type="radio" name="Quant_InternalLibSearch" value='true'<?php echo ($Quant_InternalLibSearch == 'true')?' checked':'';?>>
              false: <input type="radio" name="Quant_InternalLibSearch" value='false'<?php echo ($Quant_InternalLibSearch == 'false')?' checked':'';?>>
            <DIV class='instruction' STYLE="display: none">
            Whether to process targeted re-extraction across samples and replicates
            </DIV>
          </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top><b>PeptideFDR</b></td>
          <td colspan=3>
          <input type="text" name="Quant_PeptideFDR" value="<?php echo $Quant_PeptideFDR;?>" size=5>              
          <DIV class='instruction' STYLE="display: none">
          DIA-Umpire estimates peptide level FDR by target-decoy approach according to peptide ion's maximum PeptideProphet probability.
          Recommended value: 0.01 or 0.05 are the standard thresholds used in proteomics studies, corresponding to 1% and 5% FDR
          </DIV>
          </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top><b>ProteinFDR</b></td>
          <td colspan=3>
          <input type="text" name="Quant_ProteinFDR" value="<?php echo $Quant_ProteinFDR;?>" size=5>              
          <DIV class='instruction' STYLE="display: none">
          DIA-Umpire fist removes protein identifications with low protein group probability (<0.5) and estimates protein level FDR of the remaining list by target-decoy approach according to the maximum peptide ion's probability.
          Recommended value: 0.01 or 0.05.
          </DIV>
          </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top><b>ProbThreshold</b></td>
          <td colspan=3>
          <input type="text" name="Quant_ProbThreshold" value="<?php echo $Quant_ProbThreshold;?>" size=5>              
          <DIV class='instruction' STYLE="display: none">
          (0.0~0.99) Probability threshold for peptide-centric targeted extraction.
	        This probability is calculated by DIA-Umpire based on LDA analysis of true and decoy targeted identifications.
	        Recommended value: 0.99 corresponds to 99% confidence in an ID. Which means FDR should be less than 1% in that case.
          </DIV>
          </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top><b>FilterWeight</b></td>
          <td colspan=3>
          GW:<input type="radio" name="Quant_FilterWeight" value="GW"<?php echo ($Quant_FilterWeight == 'GW')?' checked':'';?>>
          PepW: <input type="radio" name="Quant_FilterWeight" value="PepW"<?php echo ($Quant_FilterWeight == 'PepW')?' checked':'';?>>
          <DIV class='instruction' STYLE="display: none">
          Option of using peptide group weight or peptide weight (computed by ProteinProphet) to remove shared peptides for protein quantitation.
          </DIV>
          </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top><b>MinWeight</b></td>
          <td colspan=3>
         <input type="text" name="Quant_MinWeight" value="<?php echo $Quant_MinWeight;?>" size=5>     
          <DIV class='instruction' STYLE="display: none">
          (0.0~0.99) Minimum weight (peptide group weight or peptide weight) threshold of peptides to be considered for protein quantitation. Higher weight (closer to 1) of a peptide for a protein is more likely to be a unique peptide for the protein.
          </DIV>
          </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top><b>TopNFrag</b></td>
          <td colspan=3>
         <input type="text" name="Quant_TopNFrag" value="<?php echo $Quant_TopNFrag;?>" size=2>Suggested values SAINT=6; mapDIA=20     
          <DIV class='instruction' STYLE="display: none">
          Top N fragments in terms of fragment score (Pearson correlation x fragment intensity) used for determining peptide ion intensity
          </DIV>
          </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top><b>TopNPep</b></td>
          <td colspan=3>
         <input type="text" name="Quant_TopNPep" value="<?php echo $Quant_TopNPep;?>" size=2>Suggested values SAINT=6; mapDIA=20     
          <DIV class='instruction' STYLE="display: none">
          Top N peptide ions in terms of peptide ion intensity (determined by top fragments) used for determining protein intensity
          </DIV>
          </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top><b>Freq</b></td>
          <td colspan=3>
         <input type="text" name="Quant_Freq" value="<?php echo $Quant_Freq;?>" size=2>Suggested values SAINT=0.5; mapDIA=0     
          <DIV class='instruction' STYLE="display: none">
          Minimum frequency of a peptide ion or fragment across all samples/replicates to be considered for Top N ranking
          </DIV>
          </td>
      </tr>
      
      <tr bgcolor=white>
        <td colspan="4" align=left nowrap> 
          Run SAINT:<input type=radio name='run_saint' value='SAINT' <?php echo ($run_saint=='SAINT'?'checked':'')?> onclick="is_running_saint(this.form)">
          &nbsp; &nbsp; Run mapDIA:<input type=radio name='run_saint' value='mapDIA' <?php echo ($run_saint=='mapDIA'?'checked':'')?> onclick="is_running_saint(this.form)">
          &nbsp; &nbsp; Only Run DIA-Umpire-Quant:<input type=radio name='run_saint' value='' <?php echo (!$run_saint?'checked':'')?> onclick="is_running_saint(this.form)">
          
        </td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td colspan="4">
    <div id='running_saint' style="display: block">
      <table border=0 width=100% cellspacing="1" cellpadding=0 align=center bgcolor=#5c8ca3>
        <tr  bgcolor=#6699cc height="25">
          <td align=left nowrap><font color="#FFFFFF"><b>Raw File ID</b></font></td>
          <td align=left><font color="#FFFFFF"><b><?php echo $item_name?> Name</b></font></td>
          <td align=left>
            <font color="#FFFFFF"><b>Bait Name/Label</b></font>
      <?php if($item_name != 'Bait'){?>
            <br><input id="sample_as_name" type="checkbox" name="sample_as_name" value='y' <?php echo ($sample_as_name?'checked':'')?> onClick="toggle_item_type_name(this.form)">
            Use sample name
      <?php }?>    
          </td>
          <td align=center nowrap><font color="#FFFFFF"><b>Is control</font></td>
        </tr>      
      <?php 
        $index = 0;
        foreach($rawkID_sampleID_arr as $tmp_key => $tmp_val){
        //foreach($item_propty_arr as $val){
            $val = $sample_propty_arr[$tmp_val];
            $tr_bgcolor = "#f8f8fc";
            $tmp_name = 'CHECK_'.$tmp_key;
            $checked = '';
            $target = array(",", "|", " ");
            $val['Name'] = str_replace($target, "", $val['Name']);
            $val['Bait_name'] = str_replace($target, "",$val['Bait_name']);
            if(isset($$tmp_name)){
              $checked = 'checked'; 
            }
            $textName = "TEXT_".$tmp_key;
            
            if(isset($$textName) and $theaction !='changeLabel'){
              $tmp_label = $$textName;
            }else{
              $tmp_label = ($sample_as_name)?$val['Name']:$val['Bait_name'];
            }
      ?>  
        <tr id='t_<?php echo $tmp_key?>' bgcolor='<?php echo $tr_bgcolor?>'>
          <td align=left nowrap><div class=maintext>&nbsp;&nbsp;<?php echo $tmp_key?>&nbsp;&nbsp;</div></td>
          <td align=left nowrap><div class=maintext>&nbsp;&nbsp;<?php echo $val['Name']?>&nbsp;&nbsp;</div></td>
          <td align=><input ID="<?php echo $textName?>" type="text" name="<?php echo $textName?>" size="30"  value='<?php echo $tmp_label?>' maxlength="300">
          <a class="button" title="up" href="javascript: moveItem(<?php echo $index ;?>, '');"><img border="0" src="images/icon_up_blue.gif"></a>
          <a class="button" title="down" href="javascript: moveItem(<?php echo $index ;?>,'down');"><img border="0" src="images/icon_down_blue.gif"></a>
          </td>
          <td align=center><input type="checkbox" name="CHECK_<?php echo $tmp_key?>" value="<?php echo $tmp_key?>" <?php echo $checked?>></td>
    
        </tr>
      <?php  
          $index++;
        }
      ?>
      <tr>
        <td align=center bgcolor='white' colspan="4">
          <input type="button" value=" Next " onClick="checkempty(this.form)" ></td>
        </td>
      </tr>
      </table>
    </div>
    <div id='not_saint' style="display: none ">
      <center>
      <input type="button" value=" Run DIA-Umpire Quant " onClick="checkempty(this.form)" >
      </center>
    </div>
  </tr>
  
</table> 
</FORM>
</body>
</html>
 