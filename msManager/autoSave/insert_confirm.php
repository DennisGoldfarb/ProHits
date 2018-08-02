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

require("../../db/dbstart.php");
require("../../classes/user_class.php");
require("../../classes/auth_class.php");
require("../../classes/session_class.php"); 

//connect database msManager and check login ---------------------------------------------
$this_page = "autosearch insert $targetDB";
connect_msManager();  //this functoin is in ./db/dbstart.php

if(!$SID) {
  echo "<font color=red><h2>Please login</h2></font>";
  exit;
} else {
  $SESSION = new Session("fetchall", $SID);
  //expired in $hours, the cookie is setup 4 hours expire.
  if(!$SESSION->check_SID($SID,8)) {
    echo "<center>\n
        <font color=red face='helvetica,arial,futura'><h2>Your login has expired.</h2>
        <br>Please logout ProHits / MS Data Manager. You may refresh this page after you re-login.</font>\n
        </center>\n";
    exit;
  }
}

$USER = new User("",$SESSION->value["UID"]);
$username = $USER->username;
$AUTH = new Auth($SESSION->value[UID],"", $this_page);
if(!$AUTH->access or !$newHitsStr)  {
  header ("Location: ../../noaccess.html");
  exit;
}
//end of login check----------------------------------------------------------------------

if($targetDB == "yeast"){
  change_db("yeast");
  
  
   
  //get user ID in the target database. there is a same username in msManager and prohits
  $row = mysqli_fetch_array(mysql_query("select ID from User where username='$username'"));
  $user_id = $row['ID'];
  
}else if($targetDB == "general"){
  change_db("general");
}else if($targetDB == "mammalian"){
  connect_prohits_mml();
}else{
  exit;
}

//band information
$sql = "select P.ID, P.Name, B.BaitID, B.BandMW, W.WellCode from Band B, Plate P,PlateWell W 
       where B.ID = W.BandID and W.PlateID = P.ID and B.ID = '$band_id'";
$row = mysqli_fetch_array(mysql_query($sql));
$plate_id = $row['ID'];
$plate_name = $row['Name'];
$bait_id = $row['BaitID'];
$band_MW = $row['BandMW'];
$well_code = $row['WellCode'];
  
$newHitsStr = preg_replace('/^,/','',$newHitsStr);
 
if($theaction == 'delete' and $hit_id and $AUTH->delete){
  $SQL = "DELETE FROM Hits WHERE ID = '$hit_id'";
  mysql_query($SQL);
  //delete all peptids link to this hit
  $SQL = "DELETE FROM Peptide WHERE HitID = '$hit_id'";
  mysql_query($SQL);
}  
$bgcolordark = '#999900';
$bgcolor = '#e2e083';
?>
<html>
<head>
<script language='javascript'>
 function delete_hit(Hitid){
  if(confirm("Are sure that you want to delete the Hit?")){
    var theForm = document.forms[0];
    theForm.hit_id.value=Hitid;  
    theForm.theaction.value='delete';
    theForm.submit();
   }
 }
 function go_back(){
  document.location = 'index.php?SID=<?php echo $SID;?>&file=<?php echo $file;?>';
 }
 
</script>
 <link rel="stylesheet" type="text/css" href="../../ms_style.css"> 
 </head>
 <body>
   <form name=plate_form action=<?php echo $PHP_SELF;?> method=post>
   <input type=hidden name=SID value=<?php echo $SID;?>>
   <input type=hidden name=band_id value='<?php echo $band_id;?>'>
   <input type=hidden name=newHitsStr value='<?php echo $newHitsStr;?>'>
   <input type=hidden name=file value='<?php echo $file;?>'>
   <input type=hidden name=host value='<?php echo $host;?>'>
   <input type=hidden name=targetDB value='<?php echo $targetDB;?>'>
   <input type=hidden name=hit_id value=''>
   <input type=hidden name=theaction value=''> 
<font color="navy" face="helvetica,arial,futura" size="3">
<br><font size=5 color=#cc6600><b><center>Saved Hits</center></b></font>
<br>
Following hits have been saved to ProHits / <?php echo $targetDb;?> and link to band <b><?php echo $band_id;?></b>
(MW=<?php echo $band_MW;?>)kDa in Plate <b><?php echo $plate_name;?></b>(id=<?php echo $plate_id;?>) well code <b><?php echo $well_code;?></b>.
<br>  

</font>
<table border="0" cellpadding="1" cellspacing="1" width="97%">
    
	<tr bgcolor="">
	  <td width="5" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
    <div class=tableheader>Band ID</div>
	  </td>
	  <td width="50" bgcolor="<?php echo $bgcolordark;?>" align=center> 
   <div class=tableheader>MW(kDa)</div>
	  </td>
	  <td width="50" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
	  <div class=tableheader>Hit GI</div>
	  </td>
    <td width="50" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
	  <div class=tableheader>Expect<br>/Score</div>
	  </td>
    <td width="600" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
	   <div class=tableheader>Hit Name</div>
	  </td>
	 
	  <td width="50" bgcolor="<?php echo $bgcolordark;?>" align="center">
	    <div class=tableheader>Action</div>
	  </td>
	</tr>
<?php 
$SQL = "SELECT 
         ID,  
         Instrument, 
         ORFName, 
         HitGI, 
         HitName, 
         Expect,
         MW 
         FROM Hits where ID in ($newHitsStr)";

    //echo $SQL;
$sqlResult = mysql_query($SQL);
while ( $raw = mysqli_fetch_array($sqlResult) ) {
?>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td width="" align="center"><font face="arial" size="1">
	      <?php echo '<b>'.$band_id."</b>";?>&nbsp;
	    </div>
	  </td>
	  <td width="" align="center"><div class=maintext>
	      <?php echo round($raw['MW'],2);?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo $raw['HitGI'];?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo expectFormat($raw['Expect']);?>&nbsp;
	      </div>
	  </td>
    <td width="" align="left"><div class=maintext>
	      <?php echo $raw['HitName'];?>&nbsp;
	    </div>
	  </td>
	  <td width="" align="center"><div class=maintext>
    <?php if($AUTH->delete){?>
      <a href="javascript:delete_hit(<?php echo $raw['ID'];?>);">[Delete]</a>
    <?php }?>
    </div>
	  </td>
	</tr>
   
<?php 
} //end while
?>
   </table> 
    </td>
  </tr>
</table><br>
<center>
  
  &nbsp;
  <input type=button value='Confirm and Back to Result file' 
  onClick="javascript: document.location='add_checkbox.php?file=<?php echo $file;?>&host=<?php echo $host;?>'">&nbsp; 
  <input type=button value='Confirm and Close Window' onClick='javascript: self.close();'>
  
</center>
</form>
<script language=JavaScript>
<!--
function clickIE() {if (document.all) { return false;}}
function clickNS(e) {if 
(document.layers||(document.getElementById&&!document.all)) {
if (e.which==2||e.which==3) {return false;}}}
if (document.layers) 
{document.captureEvents(Event.MOUSEDOWN);document.onmousedown=clickNS;}
else{document.onmouseup=clickNS;document.oncontextmenu=clickIE;}
document.oncontextmenu=new Function("return false")
// --> 
</script>
 </body>
 </html>
 <?php 
 //**************************************
// this function will return 10 base power 
// string value for displaying by passing
// a float value.
function expectFormat($Value){
  
  $rt='';
  if($Value == 0) return "0";
  $decimals = log10($Value);
  $tmp_int = intval( $decimals );
  if($decimals < 0){
    $tmpPow = $decimals + abs($tmp_int) + 1;
    $rt = sprintf("%0.1f", pow(10,$tmpPow));
    $tmp_int = $tmp_int -1;
    $rt .= "×10<sup>$tmp_int</sup>";
   
  }else if($decimals > 1){
    $tmpPow = $decimals - $tmp_int;
    $rt = sprintf("%0.1f", pow(10,$tmpPow));
    $rt .= "×10<sup>$tmp_int</sup>";
  }else{
    $rt = sprintf("%0.1f", $Value);
  }
  return $rt;
}
//**************************************
?>
