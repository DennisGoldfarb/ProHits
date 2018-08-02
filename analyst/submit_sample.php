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

require("../common/site_permission.inc.php");
$bgcolordark = "#0d3d05";
require("analyst/site_header.php");
?>
 <script language='javascript'>
 function subFirstStep(toRul){
  var theForm = document.theForm;
  if(theForm.theaction[0].checked){
    var theaction = theForm.theaction[0].value;
  }else{
    var theaction = theForm.theaction[1].value;
  }
  var theaction_str = "theaction=" + theaction + "&frm_user_id=<?php echo $AccessUserID?>";
  document.location=toRul+"&"+theaction_str;
 }
 </script>
 <br>
 <table border="0" cellpadding="0" cellspacing="0" width="85%">
  <tr>
    <td align="left">
		&nbsp; <font color="<?php echo $bgcolordark;?>" face="helvetica,arial,futura" size="3"><b>
    <?php 
    if($sub==1){
      echo "Add Gel-based Sample";
    }else if($sub==3){
      echo "Add Gel-free Sample";
    }
    if($AccessProjectName){
      echo " <font color='red' face='helvetica,arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font>";
    }
    ?>
    </b> 
		</font> 
	</td>
    <td align="right">&nbsp;
    </td>
  </tr>
  <tr>
  	<td colspan=2 height=1 bgcolor="black"><IMG src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="" colspan=2><br><br><br>
    <form name=theForm action=''>    
		<table align="" cellspacing="0" cellpadding="0" border="0" width=500>
     <tr>
     <td colspan=6><img src="./images/arrow<?php echo ($sub==1)?"_gel":"";?>_callout.gif" border=0></td>
     </tr>
	   <tr>
     <?php if($sub==1){
        $radio_lable = "gel";
     ?>
      <td valign=top><a href="javascript: subFirstStep('./gel.php?sub=1')"><img src="./images/arrow_gel_b.gif" border=0></a>
      </td>
      <td valign=top><img src="./images/arrow_bait.gif" border=0></td>
     <?php }else{
        $radio_lable = "bait"; 
     ?>
		  <td valign=top><a href="javascript: subFirstStep('./bait.php?sub=3')"><img src="./images/arrow_bait_b.gif" border=0></a>
      </td>
     <?php }?>
	    <td valign=top><img src="./images/arrow_exp.gif" border=0></td>
      <td valign=top><img src="./images/arrow_sample.gif" border=0></td>
       <?php if($sub==1){?>
       <td valign=top><img src="./images/arrow_plate.gif" border=0></td>
       <?php }?>
	    <td valign=top rowspan="2"><img src="./images/arrow_light_green.gif" border=0></td>
     </tr>
     <tr>
     <td valign=top colspan="5">
      <font face='helvetica,arial,futura'>
       Start from:
       <br><input type=radio name='theaction'value='addnew' checked>new <?php echo $radio_lable;?><br>
       <input type=radio name='theaction'value='viewall'>existing <?php echo $radio_lable;?>
       <br>&nbsp;&nbsp;
       </font>
     </td>
     </tr>
	  </table>
    </form>
    <font face='helvetica,arial,futura'>
    <br><br>
    </font>
    </td>
  </tr>
</table>
<?php 
require("site_footer.php");
?>