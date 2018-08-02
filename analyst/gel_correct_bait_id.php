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

$Plate_ID = '';
$Lane_ID = '';
$Bait_LocusTag = '';
$LaneCode = '';

require("../common/site_permission.inc.php");
require("analyst/classes/gel_class.php");

if($theaction == 'submit' and $Lane_ID and $Exp_ID){
   echo "<//--Land ID=$Lane_ID;Bait ID=$Bait_ID;Exp ID=$Exp_ID;LaneCode=$LaneCode-->";
  //echo "<br>";
  //update Lane
  $SQL = "update Lane set LaneCode='$LaneCode', ExpID='$Exp_ID' where ID='$Lane_ID'";
  //echo $SQL."</br>";
  mysqli_query($HITSDB->link, $SQL);
  //get bait MW
  $row = mysqli_fetch_array(mysqli_query($HITSDB->link, "select BaitMW from Bait where ID='$Bait_ID'"));
  $BaitMW = $row[0];
  //update Band
  $SQL = "update Band set BaitID='$Bait_ID', BaitMW='$BaitMW' where LaneID='$Lane_ID'";
   //echo $SQL."</br>";
  mysqli_query($HITSDB->link, $SQL);
  //update hits
  $SQL = "select B.ID, W.ID from Band B, PlateWell W where B.ID=W.BandID and B.LaneID='$Lane_ID'";
  $results = mysqli_query($HITSDB->link, $SQL);
  while($row = mysqli_fetch_array($results) ){
    $SQL = "update Hits set BaitID='$Bait_ID', BandID='$row[0]' where WellID='$row[1]'";
    // echo $SQL."</br>";
    mysqli_query($HITSDB->link, $SQL);
  }
}

//processing move bait
$Gel = new Gel();
$Gel->fetch($Gel_ID);
$frm_Name = $Gel->Name;
$frm_Image = $Gel->Image;
$frm_Stain = $Gel->Stain;
$frm_GelType = $Gel->GelType;
$frm_Notes = $Gel->Notes;
$frm_ProjectID = $Gel->ProjectID;

?>
<script language="javascript">

function selectLane(Lane_ID){
  var theForm = document.forms[0];
  theForm.Lane_ID.value =Lane_ID;
  theForm.theaction.value = 'selectLane';
  theForm.submit();
}
function searchBait(){
  var theForm = document.forms[0];
  theForm.theaction.value = 'searchBait';
  theForm.submit();
}
function setLaneCode(Bait_ID,LaneCode){
  var theForm = document.forms[0];
  theForm.LaneCode.value = LaneCode;
  theForm.Bait_ID.value = Bait_ID;
}
function updatePlate(){
  var theForm = document.forms[0];
  var Plate_ID = '<?php echo $Plate_ID;?>';
  var file = 'plate_show.php?Plate_ID=' + Plate_ID + '&theaction=modifyplate';
  newNote = window.open(file,"BitsNote",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=850,height=600');
  newNote.moveTo(400,0);
}
function submitIT(){
   var theForm = document.forms[0];
   theForm.theaction.value = 'submit';
   var ischecked;
   if(theForm.Exp_num.value == '1'){
     if(!theForm.Exp_ID.checked){
       ischecked = false;
     }else{
       ischecked = true;
     }
   }else{
     for (var i=0; i < theForm.Exp_ID.length; i++) {
       if(theForm.Exp_ID[i].checked){
         ischecked = theForm.Exp_ID[i].checked;
       }  
     }
   }
   if(ischecked != true){
     alert("Please select a Experiment!");
     return 0;
   }
   if( confirm("Are sure you want to make the change?")){
     theForm.submit();
     //alert("I am going to sumbit");
   }
}
</script>
<center><h1>Bait ID Correction</h1></center>

<form name=gel_form method=post action='<?php echo $PHP_SELF;?>'>
<input type=hidden name=theaction value=update>
<input type=hidden name=Gel_ID value=<?php echo $Gel_ID?>>
<input type=hidden name=Lane_ID value='<?php echo $Lane_ID;?>'>
<input type=hidden name=Bait_ID value=''>
<?php 
if($theaction == 'submit'){
 echo '<center><font size="+2" color="#FF0000">The checked Gel Line has been changed. <br> If you want to change it back,
       you can click the lane radio button to refresh the window.</font><br>
       Please update the plate to add new tags to hits before close the window<br>
       <input type=button value="Update Plate" onClick="javascript: updatePlate()"> <br><br>
       <input type=button value="Close the Window" onClick="window.close()">
       
       </center>
       
       ';
}
?>
<font size="+1" color="">Gel information:</font>
<table border=1 bgcolor=#66ffcc>
<tr>
  <td><b>Gel ID</b></td>
  <td><?php echo $Gel_ID?></td>
</tr>
<tr>
  <td><b>Gel Name</b></td>
  <td><?php echo $frm_Name;?></td>
</tr>
<tr>
  <td><b>Image</b></td>
  <td><?php echo $frm_Image;?></td>
</tr>
<tr>
  <td><b>Project ID</b></td>
  <td><?php echo $frm_ProjectID;?></td>
</tr>
</table>
<br>
<font size="+2" color="#FF0000"><b>Step 1:</b> Select Lane to modify:</font>
<table border=1 bgcolor=#ffff66>
<tr>
	<th>Lane ID</th>
	<th>Lane Num.</th>
	<th>Lane Code</th>
	<th>Exp ID</th>
	<th>Exp Name</th>
	<th>Bait ID</th>
	<th>Bait Locus Tag</th>
	<th>Gene Name</th>
	<th>Modify Lane</th>
</tr>
<?php 
$SQL = "select L.ID, L.LaneNum, L.LaneCode, E.ID, E.Name, B.ID, B.LocusTag, B.GeneName from 
             Bait B, Experiment E, Lane L where L.ExpID=E.ID and E.BaitID=B.ID and 
	     L.GelID='$Gel_ID' order by L.LaneNum";

$results = mysqli_query($HITSDB->link, $SQL);
while($row = mysqli_fetch_array($results) ){
 echo "<tr>\n";
  echo "<td>$row[0]</td>
	<td>$row[1]</td>
	<td>$row[2]</td>
	<td>$row[3]</td>
	<td>$row[4]</td>
	<td>$row[5]</td>
	<td>$row[6]</td>
	<td>$row[7]</td>
	<td><input type=radio value='$row[0]' name=Lane_ID onClick=\"javascript: selectLane('$row[0]');\"";
  if($row[0] == $Lane_ID){ echo 'checked';}
  echo 	"></td>
   </tr> 
   ";
}
?>
</table>
<br>
<b>Change the lane code to</b> <input name=LaneCode value='<?php echo $LaneCode;?>'> <<- auto insert bait Locus Tag
<br>
<?php 
if($Lane_ID and $theaction !='submit'){
  
?> <br>
   <font size="+1" color="">Band information:</font>
    <table border=1 bgcolor=#ffff66>
     <tr>
	<th>Band ID</th>
	<th>Lane ID</th>
	<th>Band Location</th>
	<th>Well Code</th>
	<th>Plate ID</th>
	<th>Plate Name</th>
	<th>has hits</th>
     </tr>
<?php 
  $SQL = "select B.ID, B.LaneID, B.Location, W.WellCode, P.ID, P.Name
         from Band B, PlateWell W, Plate P 
	 where B.ID=W.BandID and W.PlateID=P.ID and
	 B.LaneID='$Lane_ID' order by B.Location";
  $results = mysqli_query($HITSDB->link, $SQL);
  while($row = mysqli_fetch_array($results) ){
   if(!$Plate_ID) $Plate_ID = $row[4];
   echo "<tr>\n";
   echo "<td>$row[0]</td>
	<td>$row[1]</td>
	<td>$row[2]</td>
	<td>$row[3]</td>
	<td>$row[4]</td>
	<td>$row[5]</td>";
    $num = 0;
    $num = mysqli_num_rows(mysqli_query($HITSDB->link, "select ID from Hits where BandID='$row[0]'"));
    echo "<td>$num</td>
	</tr>"; 
  }//end while
  echo "</table>";
?>
  <input type=hidden name=Plate_ID value='<?php echo $Plate_ID;?>'>
  <br>
  <font size="+2" color="#FF0000"><b>Step 2</b>: Search a Bait to Link</font><br>
  <b>Bait LocusTag/Gene Name</b>: <input type="text" name="Bait_LocusTag" value="<?php echo $Bait_LocusTag;?>" size="11">
  <input type=button value='Search Bait' onClick="javascript: searchBait()">
  <br>
  if you can not find a experiment, you should create a new one then click Search Bait button.
<?php   
}//end if

if($Bait_LocusTag and $theaction !='submit'){
  ?>
  <br><br>
  <font size="+2" color="red"><b>Step 3</b>: Select Experiment:</font>
    <table border=1 bgcolor=#cccccc>
     <tr>
	<th>Bait ID</th>
	<th>Bait LocusTag</th>
	<th>Bait Gene</th>
	<th>Exp ID</th>
	<th>Exp Name</th>
	<th>Select Exp.</th>
     </tr>
  <?php 
  $SQL = "select B.ID, B.LocusTag, B.GeneName, E.ID, E.Name from Bait B left join Experiment E on B.ID=E.BaitID 
          where  B.ProjectID=1 and (B.LocusTag='$Bait_LocusTag' or B.GeneName='$Bait_LocusTag')";
  $results = mysqli_query($HITSDB->link, $SQL);
  echo "<input type=hidden name=Exp_num value='".mysqli_num_rows($results)."'>";
  while($row = mysqli_fetch_array($results) ){
     echo "<tr>\n";
     echo "<td>$row[0]</td>
	<td>$row[1]</td>
	<td>$row[2]&nbsp;</td>
	<td>$row[3]&nbsp;</td>
	<td>$row[4]&nbsp;</td><td>";
     if($row[4] == ''){
	echo "the bait has no experiment.";
      }else{
        echo "<input type=radio name=Exp_ID value='$row[3]' onClick=\"javascript: setLaneCode('$row[0]','$row[1]');\">";
      }
     echo "</td></tr>";
  }
  echo "</table>
  
  <br>";
  echo "<input type=button value=' Submit ' onClick=\"javascript: submitIT();\">";
}//end if

?>
</form>