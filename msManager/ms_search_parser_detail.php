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
 
include("./ms_permission.inc.php");
require("./common_functions.inc.php");
require("classes/Storage_class.php");
 
$SQL = "SELECT 
         ID, 
         TaskID, 
         Mascot_SaveScore, 
         Mascot_SaveValidation, 
         Status, 
         SaveBy, 
         SetDate, 
         Mascot_SaveWell_str, 
         GPM_SaveWell_str, 
         Mascot_Other_Value, 
         GPM_Value, 
         Tpp_SaveWell_str 
         FROM $tableSaveConf where TaskID='$task_ID' order by ID";
         
         
$SQL = "SELECT `ID`, 
               `TaskID`, 
               `Mascot_SaveScore`, 
               `Mascot_SaveValidation`, 
               `Status`, 
               `SaveBy`, 
               `SetDate`, 
               `Mascot_SaveWell_str`, 
               `GPM_SaveWell_str`, 
               `Mascot_Other_Value`, 
               `GPM_Value`, 
               `TppTaskID`, 
               `Tpp_SaveWell_str`, 
               `Tpp_Value`, 
               `SEQUEST_SaveWell_str`, 
               `SEQUEST_Value`, 
               `DECOY_prefix` 
          FROM $tableSaveConf where TaskID='$task_ID' order by ID";
 
$SaveConf_records = $managerDB->fetchAll($SQL);

/*echo "<pre>";
print_r($SaveConf_records[0]);
echo "</pre>";*/

include("./ms_header_simple.php");
?>
<br>
<table WIDTH="90%" border="0" cellpadding="0" cellspacing="2">
  <tr>
   <td><b><font color='red' face='helvetica,arial,futura' size='3'>Hits Parsed Detail</font></b><br>
  </td>
 </tr>
 <tr>
    <td height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
 </tr>
 <tr>
 	<td><br>
<?php 
	//print_r($SaveConf_records);
	foreach($SaveConf_records as $tmp_rd){
?>
		<TABLE WIDTH="100%" BORDER="0" cellpadding="1" cellspacing="1" bgcolor="#9b9b9b" align=center>
		<tr>
			<td align=right bgcolor='#bcbcbc' width=30%><b>Seach Task ID</b> &nbsp;</td>
			<td bgcolor="white" width=70%><?php echo $task_ID;?></td>
		</tr>
		<tr>
			<td align=right bgcolor='#bcbcbc'><b>Date Time</b> &nbsp;</td>
			<td bgcolor="white" ><?php echo $tmp_rd['SetDate'];?></td>
		</tr>
	 <tr>
			<td align=right bgcolor='#bcbcbc'><b>Parsed By</b> &nbsp;</td>
			<td bgcolor="white" ><?php echo $tmp_rd['SaveBy'];?></td>
		</tr>
    
	<?php if($tmp_rd['Mascot_SaveWell_str']){?>
		<tr>
			<td align=right bgcolor='#bcbcbc'><b>Mascot Result File ID </b> &nbsp;</td>
			<td bgcolor="white" >
      <?php $Mascot_SaveWell_str = str_replace(';', '; ', $tmp_rd['Mascot_SaveWell_str']);
        echo $Mascot_SaveWell_str;
      ?>
			</td>
		</tr>
		<tr>
			<td  valign=top align=right bgcolor='#bcbcbc'><b>Mascot Filter</b> &nbsp;</td>
			<td bgcolor="white" >
			<?php 
			echo "Protein Score:".$tmp_rd['Mascot_SaveScore']."<br>";
			$mascot_values = explode(";", $tmp_rd['Mascot_Other_Value']);
			foreach($mascot_values as $theValue){
				if($theValue) echo $theValue."<br>";
			}
			?>
			</td>
		</tr>
	<?php }?>
	<?php if($tmp_rd['GPM_SaveWell_str']){?>
		<tr>
			<td align=right bgcolor='#bcbcbc'><b>GPM Result File ID </b> &nbsp;</td>
			<td bgcolor="white" >
      <?php $GPM_SaveWell_str = str_replace(';', '; ', $tmp_rd['GPM_SaveWell_str']);
        echo $GPM_SaveWell_str;
      ?>
			</td>
		</tr>
		<tr>
			<td  valign=top align=right bgcolor='#bcbcbc'><b>GPM Filter</b> &nbsp;</td>
			<td bgcolor="white" >
			<?php 
			$mascot_values = explode(",", $tmp_rd['GPM_Value']);
			foreach($mascot_values as $theValue){
				if($theValue) echo $theValue."<br>";
			}
			?>
			</td>
		</tr>
	<?php }?>
  
	<?php if($tmp_rd['TppTaskID']){?>
    <tr>
			<td align=right bgcolor='#bcbcbc'><b>Tpp Task ID</b> &nbsp;</td>
			<td bgcolor="white" ><?php echo $tmp_rd['TppTaskID'];?>
			</td>
		</tr>
		<tr>
			<td align=right bgcolor='#bcbcbc'><b>TPP Result File ID </b> &nbsp;</td>
			<td bgcolor="white">
      
      
      
      <?php
      $pattern = '/(\d+)(\w+:)/i';
      $replacement = '${1}<br>$2';
      $Tpp_SaveWell_str = preg_replace($pattern, $replacement, $tmp_rd['Tpp_SaveWell_str']);
      $Tpp_SaveWell_str = str_replace(";", "; ", $Tpp_SaveWell_str);
      echo $Tpp_SaveWell_str;
			?>
			</td>
		</tr>
		<tr>
			<td  valign=top align=right bgcolor='#bcbcbc'><b>TPP Filter</b> &nbsp;</td>
			<td bgcolor="white" >
      <?php 
      $TPP_values =  str_replace(";", "<br>", $tmp_rd['Tpp_Value']);
      echo $TPP_values;
			?>
			</td>
		</tr>
	<?php }?>
  
  <?php if($tmp_rd['SEQUEST_SaveWell_str']){?>
		<tr>
			<td align=right bgcolor='#bcbcbc'><b>SEQUEST Result File ID</b> &nbsp;</td>
			<td bgcolor="white" >
      <?php 
        $SEQUEST_SaveWell_str = str_replace(';', '; ', $tmp_rd['SEQUEST_SaveWell_str']);
        echo $SEQUEST_SaveWell_str;
      ?>
			</td>
		</tr>
		<tr>
			<td  valign=top align=right bgcolor='#bcbcbc'><b>SEQUEST Filter</b> &nbsp;</td>
			<td bgcolor="white" >
      <?php 
			$mascot_values = explode(":", $tmp_rd['SEQUEST_Value']);
			foreach($mascot_values as $theValue){
				if($theValue) echo $theValue."<br>";
			}
			?>
			</td>
		</tr>
	<?php }?>
  <?php if($tmp_rd['DECOY_prefix']){?>
		<tr>
			<td align=right bgcolor='#bcbcbc'><b>DECOY prefix</b> &nbsp;</td>
			<td bgcolor="white" ><?php echo $tmp_rd['DECOY_prefix'];?>
			</td>
		</tr>
	<?php }?>  
  
		</TABLE>
		<br>
<?php }?>
	</td>
 </tr>
</table>
<?php 
include("./ms_footer_simple.php");
?>