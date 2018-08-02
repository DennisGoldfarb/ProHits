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


$theIntensity = '';
if($modify_intensity){
      if($intensity_value){
        $theIntensity = $intensity_value;
      }else{      
        if(isset($intensity_name[$i]) && isset($$intensity_name[$i])){
          $theIntensity =  $$intensity_name[$i];
        }
      }            
      if(isset($Bands->ID[$i]) and $Band_ID == $Bands->ID[$i] and $Band_ID) $intensity_name[$i] = "frm_Intensity";
?>
<table cellspacing="0" cellpadding="0" border="0">
<tr><td background="sdgfas"></td>
<?php   for($k = 1; $k <= 6; $k++){ ?>
    <td background="./images/gray_<?php echo $k;?>.gif"> 
  <input type=radio name='<?php echo $intensity_name[$i];?>' value='<?php echo $k;?>' <?php echo ($theIntensity==$k)?" checked":"";?>>
  </td>
    <?php }?>
   <td>
   <input type=button value='uncheck' onClick="javascript: uncheckradio(this.form.<?php echo $intensity_name[$i];?>);" >
   </td>
</tr>

</table>

<?php }else{
   $theIntensity = $intensity_value;
?>
<table cellspacing="0" cellpadding="0" border="1">
<tr>
    <td><img src="./images/gray_<?php echo ($theIntensity=="1")?"yellow":"";?>1.gif"></td>
    <td><img src="./images/gray_<?php echo ($theIntensity=="2")?"yellow":"";?>2.gif"></td>
    <td><img src="./images/gray_<?php echo ($theIntensity=="3")?"yellow":"";?>3.gif"></td>
    <td><img src="./images/gray_<?php echo ($theIntensity=="4")?"yellow":"";?>4.gif"></td>
    <td><img src="./images/gray_<?php echo ($theIntensity=="5")?"yellow":"";?>5.gif"></td>
    <td><img src="./images/gray_<?php echo ($theIntensity=="6")?"yellow":"";?>6.gif"></td>
</tr>
</table>
<?php }?>