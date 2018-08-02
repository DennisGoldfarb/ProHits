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

$theaction = '';
$deleted = '';
$msg = '';
$Hyp_ORFName = '';
$OwnerID = '';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require("analyst/classes/hits_class.php");

$theHit = new Hits();
if($theaction == 'update' and $AUTH->Modify and ($OwnerID==$AccessUserID or $SuperUsers) and $Hit_ID){
  $theHit->update(
         $Hit_ID,
         '',
         '',
         '',
         '',
         $frm_GeneID, 
         $frm_LocusTag,        
         $frm_HitGI, 
         $frm_HitName, 
         $frm_MW
         );
  $Desc = "$Hyp_ORFName";
  $msg = "<b>the hit information</b> has been updated.";
}else if($theaction == 'delete' and $AUTH->Delete and $Hit_ID){
  $theHit->delete($Hit_ID);
  $deleted = 'yes';
}
//-------------------------------end action processing -----------------------------------

$bgcolor = "#e1e1e1";
$bgcolordark = "#8a8a8a";
$URL = getURL();

$theHit->fetch($Hit_ID); 
$OwnerID = $theHit->OwnerID;
//echo $OwnerID.'**'.$AccessUserID;
echo "
<html>
<head>
  <meta http-equiv=\"content-type\" content=\"text/html;charset=iso-8859-1\">
<link rel=\"stylesheet\" type=\"text/css\" href=\"./site_style.css\"> 
<title>Prohits</title>
<script language=\"Javascript\" src=\"site_javascript.js\"></script>
</head><basefont face=\"arial\">
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff>
";

?>
<script language="javascript">
function confirm_delete(){
  var theForm = document.hit_form;
	if(confirm("Are you sure that you want to delete the hit?")){
    theForm.theaction.value = 'delete';
		theForm.submit();
	}
}
function checkform(){
  var theForm = document.hit_form;
	var GeneID = theForm.frm_GeneID.value;
  var HitGI = theForm.frm_HitGI.value;
  var MW = theForm.frm_MW.value;
  
	if(GeneID == '' || HitGI == '' ){
	   alert("GeneID and GI are required to update.");
  } else if(!isNumber(GeneID)){
    alert("GeneID should be a number");   
	} else if(!isNumber(HitGI)){
    alert("GI should be a number");
  } else if( MW != '' && !isNumber(MW) ) {
    alert("MW should be a number");
  } else {
	  theForm.theaction.value = 'update';
		theForm.submit();
	}
}
function isNumber(str) {
  for(var position=0; position<str.length; position++){
        var chr = str.charAt(position)
        if ( ( (chr < "0") || (chr > "9") ) && chr != ".")
              return false;
  }
  return true;
}
function trimString (str) {
	var str = this != window? this : str;
	return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
function view_protein(){
  var GeneID = document.hit_form.frm_GeneID.value;
  file = 'pop_proteinInfo.php?GeneID=' + GeneID + '&from=hit_edit';
  newwin = window.open(file,"YeastRibosome_image",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=200');
  newwin.moveTo(10,10);
}
function refreshOpener(refreshOpener){
 if(refreshOpener == 1){
   opener.location.reload();
 }
 window.close();
}
</script>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <form name="hit_form" method=post action="<?php echo $PHP_SELF;?>">
  <input type=hidden name=theaction value=''>
  <input type=hidden name=Hit_ID value="<?php echo $Hit_ID;?>">
  <input type=hidden name=OwnerID value="<?php echo $OwnerID;?>">  
 <tr> 
  <td align=center><font face='Arial' size=4><b>Hit Editor</b></font></td>
 </tr>
<?php 
if($deleted){
 echo "
 <tr> 
  <td align=center><br><br><font color=red>The Hit has been deleted.</font><br><br></td>
 </tr>
 <tr> 
  <td align=center><font face='Arial' size=4>
  <input type=button value=\"Close Window\" onClick=\"javascript: refreshOpener(0);\">
  <br><br>
  <input type=button value=\"Close Window and Refresh Plate Report\" onClick=\"javascript: refreshOpener(1);\">
  </td>
 </tr>";
}else{
?>
 <tr>
   <td align=center>&nbsp;
   <?php if($AUTH->Modify and ($OwnerID==$AccessUserID or $SuperUsers)){?>
      Please very carefully change this hit information if you needed.
   <?php }else{
      echo 'You cannot modify the hit.';
     }
   ?>
     <br><br>
     <font color=red><?php echo $msg;?></front>
   </td>
 </tr>
 <tr> 
  <td align=center>
    <table border="1" cellpadding="0" cellspacing="0" width="100%" bgcolor=<?php echo $bgcolor;?>>
    <tr>
    	<td>Hit ID</td>
    	<td><?php echo $theHit->ID;?></td>
    </tr>
    <tr>
    	<td>Well ID</td>
    	<td><?php echo $theHit->WellID;?></td>
    </tr>
    <tr>
    	<td>Bait ID</td>
    	<td><?php echo $theHit->BaitID;?></td>
    </tr>
    <tr>
    	<td>Band ID</td>
    	<td><?php echo $theHit->BandID;?></td>
    </tr>
    <tr>
    	<td><b>Hit GeneID</b></td>
    	<td><input type=text size=15 name=frm_GeneID value='<?php echo $theHit->GeneID;?>'>      
      <input type=button value='Protein Info' onClick="javascript: view_protein();">
      </td>
    </tr>
    <tr>
    	<td><b>Hit GI</b></td>
    	<td><input type=text size=15 name=frm_HitGI value='<?php echo $theHit->HitGI;?>'>
      <?php   
        $urlGI = $theHit->HitGI;      
        foreach($URL as $Value){
          if($Value['ProteinTag'] == "urlGI"){                      
            echo "<a href='".$Value['URL'].$$Value['ProteinTag']."' target=new class=button>[".$Value['Lable']."]</a>"; 
          }          
        }     
      ?></td>
    </tr>
    <tr>
    	<td><b>Hit Locus Tag</b></td>
    	<td><input type=text size=15 name=frm_LocusTag value='<?php echo $theHit->LocusTag;?>'>
      <?php 
      $urlLocusTag = $theHit->LocusTag;
      foreach($URL as $Value){
        if($Value['ProteinTag'] == "urlLocusTag"){                      
          echo "<a href='".$Value['URL'].$$Value['ProteinTag']."' target=new class=button>[".$Value['Lable']."]</a>"; 
        }          
      }     
      ?></td>
    </tr>
    <tr>
    	<td>Hit Description</td>
    	<td><textarea name=frm_HitName cols=40 rows=3><?php echo $theHit->HitName;?></textarea></td>
    </tr>
    <tr>
    	<td>MW</td>
    	<td><input type=text size=15 name=frm_MW value='<?php echo $theHit->MW;?>'>kDa</td>
    </tr>    
    </table><br>         
    <?php  if($AUTH->Modify and ($OwnerID==$AccessUserID or $SuperUsers)){?>  
       <input type=button value='Delete' onClick="javascript: confirm_delete();">
    <?php }?>
    <?php if($AUTH->Delete and $OwnerID==$AccessUserID){?>
    <input type=button value='Update' onClick="javascript: checkform();">
    <input type=reset>
    <?php }?>
    <input type=button value="Close Window" onClick="javascript: window.close();">
    
  </td>
 </tr>
<?php }?>
 </form>
</table>
</body>
</html>
