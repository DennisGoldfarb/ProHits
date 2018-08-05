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

define ("RESULTS_PER_PAGE", 40);
define ("MAX_PAGES", 12);

$theaction = '';
$order_by = ''; 
$start_point = ''; 
$Gel_ID = ''; 
$Bait_ID = ''; 
$Exp_ID = ''; 
$Lane_ID = ''; 
$modify_intensity = '';

$bg_tb_header = '#6a5acd';
$bgcolor = "#dcd1ed";

require("../common/site_permission.inc.php");
require("common/page_counter_class.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require("analyst/site_header.php");


/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/


$newBand = ($theaction == "new")? 1:0;
//page counter start here----------
$SQL = "SELECT COUNT(ID) FROM Band 
        WHERE 1
        AND ProjectID='$AccessProjectID'";
$row = mysqli_fetch_row(mysqli_query($HITSDB->link, $SQL));
$total_records = $row[0];

$PAGE_COUNTER = new PageCounter();
$query_string = "";
$caption = "Bands";
if($theaction) $query_string .= "&theaction=".$theaction;
if(!$order_by) $order_by = "B.BaitID desc";
$query_string .= "&order_by=".$order_by;
 
$page_output = $PAGE_COUNTER->page_links($start_point, $total_records, RESULTS_PER_PAGE, MAX_PAGES,$query_string); 
//end of page counter--------------

$users_ID_Name = get_users_ID_Name($HITSDB);
if($theaction == "delete" and $Band_ID){
echo "*************************";
  $SQL = "SELECT ID, File, SearchEngine, Date from UploadSearchResults where BandID='".$Band_ID."' and UploadedBy='".$USER->ID."'";
  $u_rds = $HITSDB->fetchAll($SQL);
  if($u_rds){
    foreach($u_rds as $rd){
      if($rd['SearchEngine'] == 'tppPep'){
        $SQL = "DELETE FROM TppPeptide where BandID='". $Band_ID ."' and XmlFile='Uploaded:".$rd['File']."'";
        $HITSDB->execute($SQL);
      }else if($rd['SearchEngine'] == 'tppProt'){
        $SQL = "SELECT ID FROM TppProtein where BandID='". $Band_ID ."' and XmlFile='Uploaded:".$rd['File']."'";
        $ID_Arr = $HITSDB->fetchAll($SQL);
        foreach($ID_Arr as $tmpRd){
          $SQL = "DELETE FROM TppPeptideGroup where ProteinID='".$tmpRd['ID']."'";
          $HITSDB->execute($SQL);
        }
        $SQL = "DELETE FROM TppProtein where BandID='". $Band_ID ."' and XmlFile='Uploaded:".$rd['File']."'";
        $HITSDB->execute($SQL);
      }
    }
    $SQL = "DELETE FROM UploadSearchResults where BandID='".$Band_ID."'";
    $HITSDB->execute($SQL);
    $Log = new Log();
    $Desc = "BandID: $Band_ID"; 
    $Log->insert($AccessUserID,'UploadSearchResults',$Band_ID,'delete',$Desc,$AccessProjectID);
  }
}

?>
<script language="javascript">
  function sortList(order_by){
  var theForm = document.band_form;
  theForm.order_by.value = order_by;
  theForm.theaction.value = '<?php echo $theaction;?>';
  theForm.submit();
 }
 function view_image(Gel_ID)  {
  file = 'gel_view.php?Gel_ID=' + Gel_ID;
  newwin = window.open(file,"gel_image",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=750,height=600');
  newwin.moveTo(10,10);
}
function confirm_delete(the_ID){
  var theForm = document.band_form;
  if(confirm("Are you sure that you want to delete the uploaded file?")){
    theForm.Band_ID.value = the_ID;
    theForm.theaction.value = 'delete';
    theForm.submit();
  }
}
</script>

<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
  	<td colspan=2>
    <div class=maintext>
      <img src="images/icon_picture.gif"> Gel Image 
      <img src="images/icon_plate.gif"> Band in Plate 
      <img src="images/arrow_small.gif"> Submit sample
      <img src="images/icon_report.gif"> Report 
      <img src="images/icon_upload.gif"> Upload search results
      <img src="./images/icon_purge.gif"> Remove uploaded hits
      </div>
    </td>
  </tr>
  <tr>
    <td align="left"><br>
		&nbsp; <font color="#004080" face="helvetica,arial,futura" size="5"><b> Upload Search Results
    <?php 
      if($AccessProjectName){
        echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font>";
      }
      ?>
    </b></font>     
	</td>
  </td>
  </tr>
  <tr>
  	<td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="center" colspan=2> 
<?php 
  $tmp_order_by = $order_by;
  if(strpos($order_by, 'B.BaitID')===0 or strpos($order_by, 'BT.GeneName')===0){
   $tmp_order_by = $order_by.",B.LaneID, Location";
  }
  if(!$start_point) $start_point = 0;
  $SQL = "SELECT 
          B.ID,
          B.ExpID, 
          B.LaneID,
          B.BaitID,
          B.Location,
          B.OwnerID,
          B.DateTime,
          B.InPlate,
          BT.ID AS BaitID,
          BT.GelFree,
          BT.GeneName,
          BT.Tag,
          BT.Mutation 
          FROM Band B left join Bait BT on (BT.ID = B.BaitID) 
          WHERE B.ProjectID='$AccessProjectID'
          ORDER BY $tmp_order_by 
          LIMIT $start_point,".RESULTS_PER_PAGE;
  $Bands = $HITSDB->fetchAll($SQL);
  $tmpBaitsIDstr = '';
  foreach($Bands as $tmpValue){
    if($tmpValue['GelFree']){
      if($tmpBaitsIDstr) $tmpBaitsIDstr .= ',';
      $tmpBaitsIDstr .= $tmpValue['BaitID'];
    }
  }
  $idedBaitsArr = array();
  if($tmpBaitsIDstr){
    $SQL = "SELECT B.ID FROM Bait B, Hits H 
            WHERE B.ID IN($tmpBaitsIDstr) 
            AND H.BaitID=B.ID AND H.GeneID=B.GeneID 
            GROUP BY B.ID";
    $tmpBaitsArr = $HITSDB->fetchAll($SQL);
    foreach($tmpBaitsArr as $value){
      array_push($idedBaitsArr, $value['ID']);
    }
  }
?>   
	<form name=band_form action=<?php echo $PHP_SELF;?> method=post>
  <input type=hidden name=theaction value=''>
  <input type=hidden name=order_by value=''>
  <input type=hidden name=Band_ID value=''>
  <input type=hidden name=Gel_ID value=<?php echo $Gel_ID;?>>
  <input type=hidden name=Bait_ID value=<?php echo $Bait_ID;?>>
  <input type=hidden name=Exp_ID value=<?php echo $Exp_ID;?>>
  <input type=hidden name=Lane_ID value=<?php echo $Lane_ID;?>>
  <table border="0" cellpadding="0" cellspacing="1" width="98%">
   
  <tr><td colspan=8 align=right><br><?php echo $page_output;?></td></tr>
	<tr bgcolor="">
    <td width="3%" height="" bgcolor="<?php echo $bg_tb_header;?>" align=center>
      <a href="javascript: sortList('<?php echo ($order_by == "B.BaitID")? 'B.BaitID%20desc':'B.BaitID';?>');"><div class=tableheader>Bait ID</div></a>
      <?php if($order_by == "B.BaitID") echo "<img src='images/icon_order_up.gif'>";
			if($order_by == "B.BaitID desc" ) echo "<img src='images/icon_order_down.gif'>";
		  ?> 
	  </td>
    <td width="10%" height="25" bgcolor="<?php echo $bg_tb_header;?>" align=center>
      <a href="javascript: sortList('<?php echo ($order_by == "BT.GeneName")? 'BT.GeneName%20desc':'BT.GeneName';?>');"><div class=tableheader>Bait Gene</div></a>
      <?php if($order_by == "BT.GeneName") echo "<img src='images/icon_order_up.gif'>";
			if($order_by == "BT.GeneName desc" ) echo "<img src='images/icon_order_down.gif'>";
		  ?>
	  </td>
    <td width="15%" height="25" bgcolor="<?php echo $bg_tb_header;?>" align=center nowrap>
      <div class=tableheader>Experiment<br>Exp / Gel / Lane (LaneNum)</div></a>
	  </td>
	  <td width="6%" height="25" bgcolor="<?php echo $bg_tb_header;?>" align=center>
      <a href="javascript: sortList('<?php echo ($order_by == "B.ID")? 'B.ID%20desc':'B.ID';?>');"><div class=tableheader>Sample ID</div></a>
		<?php if($order_by == "B.ID") echo "<img src='images/icon_order_up.gif'>";
			if($order_by == "B.ID desc") echo "<img src='images/icon_order_down.gif'>";
		?>    
	  </td>
    <td width="8%" height="25" bgcolor="<?php echo $bg_tb_header;?>" align=center>
      <a href="javascript: sortList('<?php echo ($order_by == "B.Location")? 'B.Location%20desc':'B.Location';?>');"><div class=tableheader>Sample Name</div></a>
	    <?php if($order_by == "B.Location") echo "<img src='images/icon_order_up.gif'>";
			if($order_by == "B.Location desc" ) echo "<img src='images/icon_order_down.gif'>";
		  ?> 
    </td>
    
    <td width="8%" bgcolor="<?php echo $bg_tb_header;?>" align=center>
      <a href="javascript: sortList('<?php echo ($order_by == "B.OwnerID")? 'B.OwnerID%20desc':'B.OwnerID';?>');"><div class=tableheader>Uploaded By</div></a>
      <?php if($order_by == "B.OwnerID") echo "<img src='images/icon_order_up.gif'>";
			if($order_by == "B.OwnerID desc" ) echo "<img src='images/icon_order_down.gif'>";
		  ?> 
	  </td>
	  <td width="10%" bgcolor="<?php echo $bg_tb_header;?>" align="center" > 
      <a href="javascript: sortList('<?php echo ($order_by == "B.DateTime")? 'B.DateTime%20desc':'B.DateTime';?>');"><div class=tableheader>Uploaded date</div></a>
      <?php if($order_by == "B.DateTime") echo "<img src='images/icon_order_up.gif'>";
			if($order_by == "B.DateTime desc" ) echo "<img src='images/icon_order_down.gif'>";
		  ?> 
	  </td>
    <td width="20%" bgcolor="<?php echo $bg_tb_header;?>" align=center>
      <div class=tableheader>Uploaded File</div>
	  </td>
	  <td width="6%" bgcolor="<?php echo $bg_tb_header;?>" align=center>
	    <div class=tableheader>Options</div>
	  </td>
	</tr>
<?php 
$noBaitArr = array();
$p_Bait_ID = '';
$d_id_arr = array();
foreach($Bands as $BandValue){
  $LaneArr = array();
  if($BandValue['LaneID']){
    $SQL = "SELECT L.GelID, L.LaneCode, L.LaneNum, G.Name, G.Image FROM Lane L, Gel G WHERE L.GelID=G.ID and L.ID='".$BandValue['LaneID']."'";
    $LaneArr = $HITSDB->fetch($SQL);
  }
  //$SQL = "SELECT ID, File, UploadedBy, Date from UploadSearchResults where BandID='".$BandValue['ID']."' and SearchEngine='tppProt'";
  $SQL = "SELECT ID, File, UploadedBy, Date from UploadSearchResults where BandID='".$BandValue['ID']."'";
  $UploaedArr = $HITSDB->fetchAll($SQL);
  $mod_upload_inf_arr['UploadedBy'] = array();
  $mod_upload_inf_arr['Date'] = array();
  $mod_upload_inf_arr['File'] = array();
  foreach($UploaedArr as $UploaedVal){
    array_push($mod_upload_inf_arr['UploadedBy'], $UploaedVal['UploadedBy']);
    array_push($mod_upload_inf_arr['Date'], $UploaedVal['Date']);
    array_push($mod_upload_inf_arr['File'], $UploaedVal['File']);
  }  
  $SQL = "SElECT Name from Experiment where ID='".$BandValue['ExpID']."'";
  $ExpArr = $HITSDB->fetch($SQL);
  $tmp_exp_str = $ExpArr['Name'];
  if($LaneArr){
    $tmp_exp_str .= "<font color='#20b2aa'>/</font><span class=text_bgcolor>".$LaneArr['Name']."</span><font color='#20b2aa'>/</font>".$LaneArr['LaneCode']."(".$LaneArr['LaneNum'].")";
  
  }else{
    $tmp_exp_str .= "<font color='#ffffff'>/ gel free</font>";
  }
  $tmp_bait_geneName = $BandValue['GeneName'];
  if($BandValue['Tag'] or $BandValue['Mutation']){
    $tmp_bait_geneName .= "(". $BandValue['Tag'].",". $BandValue['Mutation'].")";
  }
  $Gel_ID = ($LaneArr)?$LaneArr['GelID']:'';
  $Bait_ID = $BandValue['BaitID'];
  $Exp_ID = $BandValue['ExpID'];
  $Lane_ID = $BandValue['LaneID'];
  $Band_ID = $BandValue['ID'];
  $gelFree = $BandValue['GelFree'];
  $row_count = count($UploaedArr);
  $d_id = $BandValue['ID'] . '_0';
  array_push($d_id_arr, $d_id);
?>  
	<tr id='<?php echo $d_id?>' bgcolor="<?php echo $bgcolor;?>" onmousedown="highlightTR_2('<?php echo $d_id?>', 'click', '#CCFFCC', '<?php echo $bgcolor;?>')"; height="25">
    <td rowspan="<?php echo $row_count?>"><div class=maintext>&nbsp;<?php echo ($p_Bait_ID != $BandValue['BaitID'])?$BandValue['BaitID']:"";?>&nbsp;</div></td>
    <td rowspan="<?php echo $row_count?>"><div class=maintext>&nbsp;<?php echo ($p_Bait_ID != $BandValue['BaitID'])?$tmp_bait_geneName:"";?>&nbsp;</div></td>
    <td nowrap rowspan="<?php echo $row_count?>"><div class=maintext><?php echo $tmp_exp_str;?></div></td>
	  <td rowspan="<?php echo $row_count?>"><div class=maintext>&nbsp;<?php echo $BandValue['ID'];?>&nbsp;</div></td>    
    <td rowspan="<?php echo $row_count?>"><div class=maintext>&nbsp;<?php echo $BandValue['Location'];?>&nbsp;</div></td>
<?php //if($row_count <= 1){?>    
    <td><div class=maintext>&nbsp;<?php echo ($UploaedArr)?$users_ID_Name[$UploaedArr[0]['UploadedBy']]:'';?></div></td>
    <td><div class=maintext>&nbsp;<?php echo ($UploaedArr)?substr($UploaedArr[0]['Date'],0,10):'';?></div></td>
    <td><div class=maintext><div  title='<?php echo ($UploaedArr)?$UploaedArr[0]['File']:'';?>'>&nbsp;<?php echo ($UploaedArr)?substr($UploaedArr[0]['File'],0, 30):'';?></div></div></td>
<?php //}?>    
    <td bgcolor=#ffe4e1 nowrap rowspan="<?php echo $row_count?>"><div class=maintext>&nbsp;
   <?php 
   $hitType = '';
   if($LaneArr){
      echo "<a  title='gel information' href=\"javascript: popwin('./gel_view.php?Gel_ID=".$LaneArr['GelID']."',600,600);\"  alt=gel><img src=./images/icon_picture.gif border=0></a>\n";
   }
	 $hitType = get_hit_type($Band_ID,'Band');
   
   //////////////////////////////////////////////////////////////////////////////////////////////////
   //this part should be change to find out if there is any uploaded files
   //and check uploaded type to show report icon.
   if($AUTH->Insert){
      echo "<a  title='upload search results' href=javascript:popwin('upload_search_results_pop.php?upload_search_results=yes&no_DECOY=1&passed_Band_ID=".$BandValue['ID']."',750,700)><img src=./images/icon_upload.gif border=0 alt='upload TPP'></a>\n";
   }
   if($hitType){
      echo "<img src=./images/icon_empty.gif border=0>\n";
      if($gelFree){
        echo "<a  title='sample report' href='./item_report.php?type=Sample&item_ID=".$Band_ID."&hitType=".$hitType."' style='text-decoration:none'>";
      }else{
        echo "<a  title='sample report' href=\"javascript: popwin('pop_plate_show.php?Gel_ID=".$Gel_ID."&Bait_ID=".$Bait_ID."&Exp_ID=".$Exp_ID."&Lane_ID=".$Lane_ID."&Band_ID=".$Band_ID."&gelFree=".$gelFree."&hitType=".$hitType."&theaction=showone',850,600)\" style='text-decoration:none'>\n";
      }
      if($UploaedArr){
        echo "<img src='./images/icon_report_uploaded.gif' border=0 alt='Uploaded Sample Report'></a>\n";
      }else{
        echo "<img src='./images/icon_report.gif' border=0 alt='Sample Report'></a>\n";
      }  
   }
   /////////////////////////////////////////////////////////////////////////////////////////////////
   //previouse bait ID
   $p_Bait_ID = $BandValue['BaitID'];
   ?>
    </td>
	</tr>
<?php 
  if($row_count > 1){
    for($i=1; $i<count($UploaedArr); $i++){
    $d_id = $BandValue['ID'] . '_' . $i;
    array_push($d_id_arr, $d_id);
?>
      <tr id='<?php echo $d_id?>' bgcolor="<?php echo $bgcolor;?>" onmousedown="highlightTR_2('<?php echo $d_id?>', 'click', '#CCFFCC', '<?php echo $bgcolor;?>')"; height="25">  
        <td><div class=maintext>&nbsp;<?php echo $users_ID_Name[$UploaedArr[$i]['UploadedBy']];?></div></td>
        <td><div class=maintext>&nbsp;<?php echo $UploaedArr[$i]['Date'];?></div></td>
        <td><div class=maintext>&nbsp;<?php echo $UploaedArr[$i]['File'];?></div></td>
      </tr>  
  <?php 
    }
  }  
} //end for
?>
<tr><td colspan=8 align=right><?php echo $page_output;?></td></tr>
      </table>
    </form>
 
    </td>
  </tr>
</table>
<?php 
require("site_footer.php");
?>
<script language="javascript">
var d_id_arr = new Array();
<?php foreach($d_id_arr as $d_id_val){?>
    d_id_arr.push('<?php echo $d_id_val?>');
<?php }?>

function highlightTR_2(d_id,  theAction,  highlightColor, defaultColor)
{
    var in_id_arr = d_id.split('_');
    var in_id = in_id_arr[0];
    for(var j=0; j<d_id_arr.length; j++){
      var out_id_arr = d_id_arr[j].split('_');
      var out_id = out_id_arr[0];
      if(in_id !== out_id) continue;

      var theRow = document.getElementById(d_id_arr[j]);
      //alert(theRow);

      if ((highlightColor == '' && defaultColor == '')
          || typeof(theRow.style) == 'undefined') {
          return false;
      }
      var domDetect    = null;
      var currentColor = null;
      var newColor     = null;
      if (typeof(window.opera) == 'undefined' ) {
          currentColor = theRow.getAttribute('bgcolor');
          domDetect    = true;
      }else {
          currentColor = theRow.style.backgroundColor;
          domDetect    = false;
      }
      if(currentColor.indexOf("rgb") >= 0)  {
          var rgbStr = currentColor.slice(currentColor.indexOf('(') + 1,currentColor.indexOf(')'));
          var rgbValues = rgbStr.split(",");
          currentColor = "#";
          var hexChars = "0123456789ABCDEF";
          for (var i = 0; i < 3; i++)
          {
              var v = rgbValues[i].valueOf();
              currentColor += hexChars.charAt(v/16) + hexChars.charAt(v%16);
          }
      }
      if (currentColor.toLowerCase() == highlightColor.toLowerCase() && theAction == 'click' ) {
         newColor = defaultColor;
      } else {
         newColor = highlightColor;
      }
      if (newColor) {
          if (domDetect) {
                  theRow.setAttribute('bgcolor', newColor, 0);
          }
          else {
                  theRow.style.backgroundColor = newColor;
          }
      }
    }  
    return true;
}  
</script>