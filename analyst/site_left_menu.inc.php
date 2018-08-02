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

$bcolor = "#ffffff";
$bcolorLine = "#6495ed";
if(!isset($public)) $public='';
?>
<table border="0" cellpadding="0" cellspacing="0" align="center" width=100%>
     
  <tr>
    <td height=20 bgcolor=<?php echo ($SCRIPT_NAME=='index.php')?"$bcolor":'';?>>
        &nbsp;<img src='./images/icon_HOME.gif' border=0 >
        <a href="index.php" class="left_menu"><b><font color=black>Home</font></b></a>
    </td>
  </tr> 
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
<?php   
 if($AccessProjectID){
?>
  <tr>
    <td><div class='middle'>
      &nbsp;<img src='./images/icon_ENTRY.gif'><b>Create New Entry</b>
      </div>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr> 
  <tr>
    <td height=20 bgcolor=<?php echo ($sub==3)?"$bcolor":'';?>>
  	&nbsp; &nbsp;<a href="submit_sample.php?sub=3" class="left_menu">
      <b>Add Gel-free Sample</b></a>
    </td>
  </tr>   
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
      <td height=20 bgcolor=<?php echo ($sub==1)?"$bcolor":'';?>>
  	&nbsp; &nbsp;<a href="submit_sample.php?sub=1" class="left_menu">
      <b>Add Gel-based Sample</b></a>
    </td>
  </tr>   
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <?php 
  if(ENABLE_UPLOAD_SEARCH_RESULTS and $AUTH->Insert){
  ?>
  <tr>
    <td height=20 <?php echo ($SCRIPT_NAME=='upload_search_results.php')?"bgcolor=$bcolor":'';?>>
      &nbsp; &nbsp;<a href="./upload_search_results.php" class="left_menu">
        <b>Upload Search Results</b></a><br>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr> 
  <?php 
  }
  ?>
  <tr>
    <td height=20><div class=middle>
	    &nbsp;<img src='./images/icon_REPORTS.gif' border=0><b>Individual Reports</b></div>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <tr>
    <td height=20 <?php echo (($SCRIPT_NAME=='bait.php' or ($SCRIPT_NAME=='item_report.php' and $type=='Bait')) and !$sub)?"bgcolor=$bcolor":'';?>>
	   &nbsp; &nbsp;<a href="bait.php?theaction=viewall&frm_user_id=<?php echo $AccessUserID?>&first_show=y" class="left_menu">
       <b>Report by Bait</b></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr> 
  <tr>
    <td height=20 <?php echo (($SCRIPT_NAME=='experiment.php' or $SCRIPT_NAME=='experiment_show.php' or ($SCRIPT_NAME=='item_report.php' and $type=='Experiment')) and !$sub)?"bgcolor=$bcolor":'';?>>
	   &nbsp; &nbsp;<a href="experiment_show.php?theaction=view&frm_Experiment_groups=Experiment&frm_user_id=<?php echo $AccessUserID?>&first_show=y" class="left_menu">
       <b>Report by Experiment</b></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr> 
  <tr>
    <td height=20 <?php echo (($SCRIPT_NAME=='plate.php' or $SCRIPT_NAME=='band.php' or $SCRIPT_NAME=='lane.php' or $SCRIPT_NAME=='band_show.php') and !$sub)?"bgcolor=$bcolor":'';?>>
		  &nbsp; &nbsp;<a href="band_show.php?theaction=viewall&frm_Band_groups=Band&frm_user_id=<?php echo $AccessUserID?>&first_show=y" class="left_menu">
      <b>Report by Sample</b></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <tr>
    <td height=20 <?php echo (($SCRIPT_NAME=='plate_show.php' or $SCRIPT_NAME=='plate_report.php' or $type=='Plate') and !$sub)?"bgcolor=$bcolor":'';?>>
		   &nbsp; &nbsp;<a href="plate_show.php?frm_user_id=<?php echo $AccessUserID?>&first_show=y" class="left_menu">
       <b>Report by Plate</b></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr> 
 <tr>
    <td height=20 <?php echo (($SCRIPT_NAME=='gel.php' or $type=='Gel') and !$sub)?"bgcolor=$bcolor":'';?>>
  	&nbsp; &nbsp;<a href="gel.php?theaction=viewall&frm_user_id=<?php echo $AccessUserID?>&first_show=y" class="left_menu">
      <b>Report by Gel</b></a>
    </td>
  </tr> 
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr> 
  <tr>
    <td height=20><div class=middle>
	    &nbsp;<img src='./images/icon_ANALYSIS.gif' border=0><b>Multiple Sample Analysis</b></div>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr> 
  <tr>
    <td height=20 <?php echo (($SCRIPT_NAME=='comparison.php') and !$sub)?"bgcolor=$bcolor":'';?>>
  	&nbsp; &nbsp;<a href="comparison.php?firstDisplay=y" class="left_menu">
      <b>Comparison</b></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <tr>
    <td height=20 <?php echo (($SCRIPT_NAME=='export_bait_to_hits.php' and $public=='SAINT') and !$sub)?"bgcolor=$bcolor":'';?>>
  	&nbsp; &nbsp;<a href="export_bait_to_hits.php?firstDisplay=y&public=SAINT" class="left_menu">
      <b>Run SAINT</b></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <tr>
    <td height=20 <?php echo (($SCRIPT_NAME=='SAINT_report.php') and !$sub)?"bgcolor=$bcolor":'';?>>
  	&nbsp; &nbsp;<a href="SAINT_report.php?frm_user_id=<?php echo $AccessUserID?>&first_show=y" class="left_menu">
      <b>SAINT Report</b></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr> 
  <tr>
    <td height=20 <?php echo (($SCRIPT_NAME=='DIAUmpire_Quant_report.php') and !$sub)?"bgcolor=$bcolor":'';?>>
  	&nbsp; &nbsp;<a href="DIAUmpire_Quant_report.php?firstDisplay=y&public=SAINT" class="left_menu">
      <b>DIA-Umpire Quantitation</b></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr> 
  <tr>
    <td height=20><div class=middle>
	    &nbsp;<img src='./images/icon_PROTOCOLS.gif' border=0><b>Manage Protocols and Lists</b></div>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <tr>
    <td height=20>
  	&nbsp; &nbsp;<a href="javascript: popwin('protocol_detail_pop.php?modal=this_project', 770, 600);" class="left_menu">
      <b>Text-based Protocols</b></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <tr>
    <td height=20>
  	&nbsp; &nbsp;<a href="javascript: popwin('experiment_detail_pop.php?edit_only=1', 500, 500);" class="left_menu">
      <b>Experimental Editor</b></a>
    </td>
  </tr> 
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <tr>
    <td height=20 >
  	&nbsp; &nbsp;<a href="javascript: popwin('./mng_set_non_specific.php?filterID=12', 670, 600);" class="left_menu">
      <b>Background Lists</b></a>
    </td>
  </tr> 
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <?php 
  if($SCRIPT_NAME == 'experiment.php'){
    $group_note_type = 'Experiment';
  }elseif($SCRIPT_NAME == 'band_show.php' || $SCRIPT_NAME == 'plate_free.php' || $SCRIPT_NAME == 'band.php'){
    $group_note_type = 'Band';
  }else{
    $group_note_type = 'Bait';
  }
  ?>
  <tr>
    <td height=20>
  	&nbsp; &nbsp;<a href="javascript: popwin('group_detail_pop.php?modal=this_project&selected_type_div_id=<?php echo $group_note_type?>', 600, 600);" class="left_menu">
      <b>Group Lists</b></a>
    </td>
  </tr> 
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <tr>
    <td height=20>
  	&nbsp; &nbsp;<a href="javascript: popwin('epitope_tag_detail_pop.php', 500, 600);" class="left_menu">
      <b>Epitope Tag Lists</b></a>
    </td>
  </tr>  
  
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr> 
  <tr>
    <td height=20 <?php echo (($SCRIPT_NAME=='user_defined_frequency') and !$sub)?"bgcolor=$bcolor":'';?>>
  	&nbsp; &nbsp;<!--a href="user_defined_frequency.php?firstDisplay=y" class="left_menu"-->
    <a href="user_defined_frequency.php?theaction=display_frequency" class="left_menu">
      <b>Frequencies</b></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <tr>
    <td height=20><div class=middle>
	    &nbsp;<img src='./images/icon_TOOL.gif' border=0><b>Other Tools</b></div>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <tr>
    <td height=20 <?php echo ($SCRIPT_NAME=='coip.php' and !$sub)?"bgcolor=$bcolor":'';?>>
  	&nbsp; &nbsp;<a href="coip.php?theaction=viewall" class="left_menu">
      <b>Co-IP Report</b></a>
    </td>
  </tr> 
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <tr>
    <td height=20 <?php echo ($SCRIPT_NAME=='log_report.php' and !$sub)?"bgcolor=$bcolor":'';?>>
  	&nbsp; &nbsp;<a href=log_report.php?theaction=select&frm_allUsers=Y&first=Y class='left_menu'>
      <b>Log Report</b></a>
    </td>
  </tr> 
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr> 
  <tr>
    <td height=20 <?php echo ((($SCRIPT_NAME=='export_bait_to_hits.php' and $public!='SAINT') or $SCRIPT_NAME=='export_menu.php') and !$sub )?"bgcolor=$bcolor":'';?>>
  	&nbsp; &nbsp;<a href="export_menu.php" class="left_menu">
      <b>Export Functions</b></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <tr>
    <td height=20 <?php echo ($SCRIPT_NAME=='advanced_search.php')?"bgcolor=$bcolor":'';?>>
  	&nbsp;<img src='./images/icon_SEARCH.gif' border=0 >
    <a href="advanced_search.php" class="left_menu">
      <b><font color="#000000">Advanced Search</font></b></a>
    </td>
  </tr> 
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>
  <tr>
    <td height=20 >
    <?php 
    
    $anchor = '';
    if($SCRIPT_NAME=='index.php'){
      if(isset($proj_changed) && $proj_changed){
        $anchor = 'faq3';
      }else{
        $anchor = 'faq2';
      }
    }else if($sub=='3'){
      $anchor = 'faq26';
    }else if($sub=='1'){
      $anchor = 'faq6';
    }else if($SCRIPT_NAME=='bait.php'){
      $anchor = 'faq16';
    }else if($SCRIPT_NAME=='experiment.php'){
      $anchor = 'faq8';  
    }else if($SCRIPT_NAME=='plate.php' or $SCRIPT_NAME=='plate_show.php' or $SCRIPT_NAME=='gel.php' or $SCRIPT_NAME=='band.php' or $SCRIPT_NAME=='lane.php' or $SCRIPT_NAME=='band_show.php'){
      $anchor = 'faq25';
    }else if($SCRIPT_NAME=='comparison.php'){
      $anchor = 'faq28';
    }else if($SCRIPT_NAME=='export_menu.php'){
      $anchor = 'faq28';
    }else if($SCRIPT_NAME=='advanced_search.php'){
      $anchor = 'faq50';
    }
    ?>
    &nbsp;<img src='./images/icon_HELP.gif' border=0 >
    <a href="javascript: popwin('../doc/Analyst_help.php#<?php echo $anchor;?>', 800, 600);" class="left_menu">
      <b><font color="#000000">Help</font></b></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr>  
 <tr>
    <td height=20 ><br><br></td>
 </tr>
  <?php 
  }
  if(!DISABLE_RAW_DATA_MANAGEMENT){
  ?>
  <tr>
    <td height=20 >
    <a href="../msManager/" class="left_menu">
      <b>&middot; Data Management</b> </a>
    </td>
 </tr>
 <?php }?>
 <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
 </tr>
 <tr>
    <td height=20>
		<a href="../common/logout.php" class="left_menu">&middot; Log Out</a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor=<?php echo $bcolorLine;?>><img width=1 height=1 alt=""></td>
  </tr> 
</table>
<script language='javascript'>
function helpwindow(page){
  var url='help.php#<?php echo $SCRIPT_NAME;?>';
  newwin = window.open(url,"help",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=650,height=500');
  newwin.moveTo(10,10);
}
</script>
