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
$mod_HitNote = '';
$mod_HitDis = '';
$HitDisID = '';
$HitNoteID = '';
$message = '';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
require("analyst/classes/bait_class.php");
require("analyst/classes/band_class.php"); 
require("analyst/classes/hits_class.php");
require("analyst/classes/lane_class.php");
require("analyst/classes/gel_class.php");
require("analyst/classes/hitDiscussion_class.php");

$Hit = new Hits($Hit_ID); 
$Bait = new Bait($Hit->BaitID);
$Band = new Band($Hit->BandID);
$Lane = new Lane($Band->LaneID);
$Gel = new Gel($Lane->GelID);
$Log = new Log();

$userNamesArr = get_users_ID_Name($mainDB);

//check if the user can access the band 
$band_permission = $AUTH->isOwner("Band",$Hit->BandID,$AccessUserID);
$GI_array = explode('gi|',str_replace(";","", $Hit->RedundantGI));
//check user if he has permission to read this band

if($theaction == "insert"){
  if($HitNoteAlias == "discussion"){  
    $tmpHitDis = new HitDiscussion();
    $tmpHitDis->insert($Hit_ID,$theHitNote, $AccessUserID);
    $Desc = "HitID=$Hit_ID,HitNoteType=discussion";
    $Log->insert($AccessUserID,'HitDiscussion',$tmpHitDis->ID,'insert',$Desc,$AccessProjectID);
  }else if($AUTH->Insert){
    $SQL ="INSERT INTO HitNote SET 
          HitID='$Hit_ID', 
          FilterAlias='$HitNoteAlias', 
          Note='".mysqli_real_escape_string($mainDB->link, $theHitNote)."', 
          UserID='$AccessUserID',
					Date=now()";
    $ret = $mainDB->insert($SQL);
    if($ret){      
      $Desc = "HitID=$Hit_ID,FilterAlias=$HitNoteAlias";
      $Log->insert($AccessUserID,'HitNote',$Hit_ID,'insert',$Desc,$AccessProjectID);
    }
  }
}

if($theaction == "delete" and $HitNoteAlias and $AUTH->Delete){
  $SQL = "DELETE FROM HitNote WHERE HitID='$Hit_ID' and FilterAlias='$HitNoteAlias' and  UserID='$AccessUserID'";
  $ret = $mainDB->execute($SQL);
  if($ret){
    // the delete function will check the note owner. The note owner has permission to delete
    //add record into Log table
    $Desc = "HitID=$Hit_ID,HitNoteType=$HitNoteAlias";
    $Log->insert($AccessUserID,'HitNote',$Hit_ID,'delete',$Desc,$AccessProjectID);
    //end of Log table
  }  
  
}else if($theaction == "delete_dis" and $HitDisID){
  $tmpHitDis = new HitDiscussion();
  $tmpHitDis->delete($HitDisID, $AccessUserID);
  $Desc = "HitID=$Hit_ID,HitNoteType=Discussion";
  $Log->insert($AccessUserID,'HitDiscussion',$HitDisID,'delete',$Desc,$AccessProjectID);
}

if($theaction == "update" and $HitNoteAlias and $AUTH->Modify){  
  $SQL ="UPDATE HitNote SET 
        HitID='$Hit_ID',       
        FilterAlias='$HitNoteAlias', 
        Note='".mysqli_real_escape_string($mainDB->link, $theHitNote)."',        
			  Date=now()
        WHERE HitID='$Hit_ID' and FilterAlias='$oldHitNoteAlias'";
    $ret = $mainDB->execute($SQL);
  if($ret){
    $Desc = "HitID=$Hit_ID,FilterAlias=$HitNoteAlias, FilterAlias=$oldHitNoteAlias";
    $Log->insert($AccessUserID,'HitNote',$Hit_ID,'update',$Desc,$AccessProjectID);
  }    
}else if($theaction == "update_dis"){
  $tmpHitDis = new HitDiscussion();
  $tmpHitDis->update($HitDisID, $theHitNote, $AccessUserID);
  $Desc = "HitID=$Hit_ID,HitNoteType=discussion";
  $Log->insert($AccessUserID, 'HitDiscussion', $HitDisID,'update',$Desc,$AccessProjectID);
}

$bgcolordark = '#858585';
$bodycolor = '#ffffff';
$bgHitcolor="#e1e1e1";

?>
<html>
<head>
 <title>Prohits</title>
 <link rel="stylesheet" type="text/css" href="./site_style.css"> 
 <script language="Javascript" src="site_no_right_click.inc.js"></script>
 <script language="javascript">
 function confirm_delete(HitNoteAlias){
	if(confirm("Are you sure that you want to delete this hit note?")){
    document.del_form.HitNoteAlias.value = HitNoteAlias;  
    document.del_form.theaction.value='delete';
		document.del_form.submit();
	}
 }
 function confirm_delete_dis(HitDisID){
	if(confirm("Are you sure that you want to delete this hit note?")){ 
    document.del_form.HitDisID.value = HitDisID;
    document.del_form.theaction.value='delete_dis';
		document.del_form.submit();
	}
 }
 function modify_note(HitNoteAlias){
	 document.del_form.HitNoteAlias.value = HitNoteAlias;
	 document.del_form.theaction.value = "modify";
	 document.del_form.submit();
 }
 function modify_dis(HitDis_ID){
	 document.del_form.HitDisID.value = HitDis_ID;
	 document.del_form.theaction.value = "modify_dis";   
	 document.del_form.submit();
 }
 function update_note(){
 	 var theNoteAlias=document.del_form.theHitNoteAlias.value;
   var theNote=document.del_form.theHitNote.value;  
   if(theNoteAlias == '' || isEmptyStr(theNote)){
    alert("Note Type and Note are requiered.");
		return false;
   } else {
	 	 document.del_form.HitNoteAlias.value = theNoteAlias;
     document.del_form.theaction.value = 'update'; 
     document.del_form.submit();
   } 
 }
 function update_dis(){
   var theNote=document.del_form.theHitNote.value;  
   if(isEmptyStr(theNote)){
    alert("Note Type and Note are requiered.");
		return false;
   } else {
     document.del_form.HitDisID.value = <?php echo ($HitDisID)?$HitDisID:'0';?>;
     document.del_form.theaction.value = 'update_dis'; 
     document.del_form.submit();
   } 
 }
 function add_new_note(){
  var theNoteAlias=document.del_form.theHitNoteAlias.value;
  var theNote=document.del_form.theHitNote.value;  
  if(theNoteAlias == '' || isEmptyStr(theNote)){
    alert("Note Type and Note are requiered.");
		return false;
   } else {
	 	 document.del_form.HitNoteAlias.value = theNoteAlias;
     document.del_form.theaction.value = 'insert'; 
     document.del_form.submit();
   } 
 }
 function isEmptyStr(str){
    var str = this != window? this : str;
	var temstr =  str.replace(/^\s+/g, '').replace(/\s+$/g, '');
	if(temstr == 0 || temstr == ''){
	   return true;
	} else {
		return false;
	}
}
 function note_detail(HitDisID){
   document.del_form.theaction.value = "notedetail";
   document.del_form.HitDisID.value = HitDisID;
   document.del_form.submit();
 }
 </script>
 <script language="Javascript" src="site_javascript.js"></script>
 </head>
 <body bgcolor=<?php echo $bodycolor;?>>
 <form name=del_form method=post action="<?php echo $PHP_SELF;?>">
 <input type=hidden name=Hit_ID value="<?php echo $Hit_ID;?>">
 <input type=hidden name=theaction value="">
 <input type=hidden name=HitNoteAlias value="">
 <input type=hidden name=HitNoteID value="<?php echo $HitNoteID;?>">
 <input type=hidden name=HitDisID value="">
 <input type=hidden name=oldHitNoteAlias value="<?php echo $HitNoteAlias;?>">
 
 <table border="0" cellpadding="1" cellspacing="1" width="100%">  
 <tr bgcolor="">
	  <td align="center" colspan="4" bgcolor="#0080c0" >
		<font face="Arial" size="3" color="#FFFFFF">&nbsp;<b>Hit Notes <font face="Arial" size="-2" color=#ffffff>(ProHits hit id:<?php  echo "$Hit_ID";?>)</font></b></font>
    </td>
  </tr>
	<tr bgcolor="">
	  <td align="left" colspan="4" bgcolor="<?php echo $bgcolordark;?>">
		<span class=tableheader>&nbsp;<b>Bait Information (<?php  echo "$Hit->BaitID";?>)</b> 
	</span></td>
  </tr>
  <tr>
   <td bgcolor="<?php echo $bgHitcolor;?>" width="15%"><span class=maintext><b>Bait Gene ID</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>" width="20%"><span class=maintext><?php echo $Bait->GeneID;?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>" width="15%"><span class=maintext><b>Bait Locus Tag</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Bait->LocusTag;?></span></td>
  </tr>
  <tr> 
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Bait Gene Name</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Bait->GeneName;?></span></td>  
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Bait MW (kDa)</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Bait->BaitMW;?></span></td>
  </tr>
  <tr>  
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Bait Clone</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Bait->Clone;?></span></td>  
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Bait Description</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Bait->Description;?></span></td> 
  </tr>
</table>
<table border="0" cellpadding="1" cellspacing="1" width="100%">  
	<tr bgcolor="">
	  <td align="left" colspan="4" bgcolor="<?php echo $bgcolordark;?>">
		<span class=tableheader><b>&nbsp;Band Information (<?php  echo "$Band->ID";?>)</b> 
	</span></td>
  </tr>
  <tr>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><b>Band in Gel(<?php echo $Gel->ID;?>)</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Gel->Name;?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Lane Num/ Lane Code</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo "$Lane->LaneNum / $Lane->LaneCode";?></span></td>
  </tr>
  <tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Band Code</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Band->Location;?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Band Observed MW(kDa)</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Band->BandMW;?></span></td>
  </tr>
</table>
<table border="0" cellpadding="1" cellspacing="1" width="100%">
<tr>
  <td align="left" colspan="4" bgcolor="<?php echo $bgcolordark;?>"> 
		<span class=tableheader><b>&nbsp;Hit Information (<?php  echo $Hit->HitGI . get_URL_str($Hit->HitGI);?>) </b> 
	</span></td>
</tr>
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Instrument</b></span></td><td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Hit->Instrument;?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Score</b></span></td><td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Hit->Expect;?></span></td>
</tr> 
<tr>
   <td  bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Redundant</b> </span></td>
   
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext>
   <?php  
    for($i=0;$i<count($GI_array);$i++){
      if($GI_array[$i]){
        echo $GI_array[$i] . get_URL_str($GI_array[$i])."<br>";
      }  
    } 
   ?></span></td>
   
   <td bgcolor="<?php echo $bgHitcolor;?>">
   <span class=maintext><b>Results File</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext>
     <?php 
if($Hit->ResultFile){
  if(strstr($Hit->SearchEngine, 'Uploaded')){
    $tmp_file_name = $Hit->ResultFile;
    $tmp_SearchEngine = '';
    if(strstr($Hit->SearchEngine, 'Mascot')){
      $theFile = "./ProhitsMascotParserHTML.php?userID=$AccessUserID&File=$tmp_file_name";
      $tmp_SearchEngine = 'Mascot';
    }elseif(strstr($Hit->SearchEngine, 'GPM')){
      $theFile = "./ProhitsGPM_ParserHTML.php?userID=$AccessUserID&File=$tmp_file_name";
      $tmp_SearchEngine = 'GPM';
    }
?>
    <a href="javascript:popwin('<?php echo str_replace("\\","/",$theFile)?>',800,800)">Click to view <b><?php echo $tmp_SearchEngine;?></b> search results</a>
<?php }else{
    if($Hit->SearchEngine == "Mascot"){
      if(MASCOT_USER){
        $tmp_url = "http://".MASCOT_IP. MASCOT_CGI_DIR."/login.pl";
        $tmp_url .= "?action=login&username=".MASCOT_USER."&password=".MASCOT_PASSWD;
        $tmp_url .= "&display=nothing&savecookie=1&referer=master_results.pl?file=".$Hit->ResultFile;
      }else{
        $tmp_url = "http://".MASCOT_IP. MASCOT_CGI_DIR."/master_results.pl?file=".$Hit->ResultFile;
      }
      echo "<a href='$tmp_url' target=mascot_win>Click to view <b>Mascot</b> search results</a>";
    }else if($Hit->SearchEngine == "GPM"){
      echo "<a href=/thegpm-cgi/plist.pl?path=".$Hit->ResultFile."' target=mascot_win>Click to view <b>GPM</b> search results</a>";
    }else{
      //echo "<a href='".$Hit->ResultFile."'>Click to view <b>Sonar</b> search results</a>";
    }
  }  
}
     ?></span>
   </td>
</tr> 
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Search Database</b></span></td><td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Hit->SearchDatabase;?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Search Date</b></span></td><td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Hit->DateTime;?></span></td>
</tr>  
</table>  
<br>

<?php 

$HitDis = new HitDiscussion();
$HitDis->fetchall($Hit_ID);

$SQL = "SELECT
        HitID, 
        FilterAlias, 
        Note, 
        UserID , 
        Note, 
        Date 
        from HitNote  
        where HitID='$Hit_ID' ORDER BY FilterAlias";
$HitNotes = $mainDB->fetchAll($SQL);
//print_r($HitNotes);
$HitNotesCount = count($HitNotes);

if(!$HitNotesCount and !$HitDis->count) {
  echo "<span class=maintest><font color=red></font></span></br>";
}else{
?>
<table border="0" cellpadding="1" cellspacing="1" width="100%">
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>" width=100><span class=maintext><b>Notes Type</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>" width=460><span class=maintext><b>Notes</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>" width=80><span class=maintext><b>Added By</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>" width=60><span class=maintext><b>Added On</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>" width=50><span class=maintext><b>Action</b></span></td>
</tr>
<?php 
}
 
for($i=0; $i<$HitNotesCount; $i++){
  $userFullName = get_userName($mainDB, $HitNotes[$i]['UserID']); 
	//only the hote owner can modify and delete
	//only team member, owner, super user and user id = 1 can add notes
  
?>
<tr>
   <td bgcolor="#ffff00"><span class=maintext><?php echo get_filterName($HitNotes[$i]['FilterAlias']);?></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo nl2br(htmlspecialchars($HitNotes[$i]['Note']));?></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo "$userFullName";?></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $HitNotes[$i]['Date'];?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>">
    <?php if($AUTH->Delete and $HitNotes[$i]['UserID'] == $AccessUserID) {?>
            <a href="javascript:confirm_delete('<?php echo $HitNotes[$i]['FilterAlias'];?>');">
    	      <img border="0" src="images/icon_purge.gif" alt="Delete"></a>
    <?php }else{
    		echo "<img src=\"images/icon_empty.gif\">";
      } 
      if($AUTH->Modify and ($HitNotes[$i]['UserID'] == $AccessUserID or $SuperUsers)){?>      
    	    <a href="javascript:modify_note('<?php echo $HitNotes[$i]['FilterAlias'];?>');">
    	    <img border="0" src="images/icon_view.gif" alt="Modify"></a>&nbsp;
    <?php }?>
   </td>
</tr>
<?php 
}//end for
//////////////////////////// discussion ///////////////

for($i=0; $i<$HitDis->count; $i++){
  if($HitDis->UserID[$i]){
    $userFullName = $userNamesArr[$HitDis->UserID[$i]];
  }  
  $tmpNote = str_replace("\n", "<br>", htmlspecialchars($HitDis->Note[$i]));
  if(!($theaction == "notedetail" and $HitDisID == $HitDis->ID[$i]) and strlen($tmpNote) > 400 ) {
    if($tmpPos = strpos($tmpNote, " ", 400)){ //end with space
      $tmpNote = substr($tmpNote, 0, $tmpPos);
      $tmpNote .="... <a href=\"javascript:note_detail(".$HitDis->ID[$i].");\">[More]</a>";
    }
  }
  //exit;
	//only the hote owner can modify and delete
	//only team member, owner, super user and user id = 1 can add notes 
?>
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext>Discussion</span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $tmpNote;?></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo "$userFullName";?></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $HitDis->DateTime[$i];?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>">
    <?php if($AUTH->Delete and $HitDis->UserID[$i] == $AccessUserID) {?>
            <a href="javascript:confirm_delete_dis(<?php echo $HitDis->ID[$i];?>);">
    	      <img border="0" src="images/icon_purge.gif" alt="Delete"></a>
    <?php }else{
    		echo "<img src=\"images/icon_empty.gif\">";
      } 
      if($AUTH->Modify and ($HitDis->UserID[$i] == $AccessUserID or $SuperUsers)) {?>
    	    <a href="javascript:modify_dis(<?php echo $HitDis->ID[$i];?>);">
    	    <img border="0" src="images/icon_view.gif" alt="Modify"></a>&nbsp;
    <?php }?>
   </td>
</tr>
<?php 
}//end for
////////////////////// end discussion /////////////////
?> 
</table>  
<?php 
//check if the user has permission to add not to this band
//only group onwer, group members , super user, and sueprvisor has permission

$mod_HitDisID = ''; 
$mod_HitDisNote = ''; 
$mod_HitNoteCount = '';
$mod_HitNoteAlias = '';         
$mod_HitNoteText = ''; 

if($theaction == "modify" and $HitNoteAlias){
  $SQL = "SELECT
          HitID, 
          FilterAlias, 
          Note, 
          UserID,
					Date
          FROM HitNote where HitID='$Hit_ID' AND FilterAlias='$HitNoteAlias'";          
  $mod_HitNote = $mainDB->fetch($SQL);
  if(count($mod_HitNote)){
    $mod_HitNoteCount = count($mod_HitNote);
    $mod_HitNoteAlias = $mod_HitNote['FilterAlias'];         
    $mod_HitNoteText = $mod_HitNote['Note'];
  }  
}else if($theaction == "modify_dis" and $HitDisID){
  $mod_HitDis = new HitDiscussion($HitDisID);
  $mod_HitDisID = $mod_HitDis->ID; 
  $mod_HitDisNote = $mod_HitDis->Note;
}
?>
   <table border="0" cellpadding="1" cellspacing="1" width="100%">
    <tr>
      <td colspan=2 align=center><span class=maintext><b><?php  echo ($mod_HitNoteCount or $mod_HitDisID)?"Modify":"New";?> Hit Note</b></span></td>
    <tr>
<?php if($message){?>
    <tr>
      <td colspan=2 align=center><span class=maintext><?php  echo $message;?></span></td>
    <tr>
<?php }?>    
     <td bgcolor="<?php echo $bgHitcolor;?>" ><span class=maintext><b>Notes Type</b></span></td>
     <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext>
      <select name=theHitNoteAlias>
        <option value=''>--Select a Note Type--
        <option value='discussion' <?php echo ($mod_HitDisID)?"selected":"";?>>Discussion
        <?php        
        if($AUTH->Modify){
          note_dropdown_list($mainDB,$mod_HitNoteAlias);
        }  
        ?>
      </select> <font color=red> Discussion notes are not account for data filtering.</font>
     </span></td>
    </tr>
    <tr>
     <td bgcolor="<?php echo $bgHitcolor;?>" valign=top><span class=maintext><b>Notes</b></span></td>
     <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext>
      <textarea cols=60 rows=5 name=theHitNote><?php echo $mod_HitNoteText.$mod_HitDisNote;?></textarea></td>
    </tr>
   </table>
<center>
<?php if($mod_HitNoteAlias){?>
	<input type=button value=' Update ' onClick='javascript: update_note()'; class=black_but>
<?php }elseif($mod_HitDisID){?>
	<input type=button value=' Update ' onClick='javascript: update_dis()'; class=black_but>
<?php }else{?>
	<input type=button value='Save New Notes' onClick='javascript: add_new_note()'; class=black_but>
<?php }?>
<input type=button value=' Close ' onClick='javascript: window.close();' class=black_but>
</center>
</form>
 </body>
 </html>
 <?php 
 function note_dropdown_list($mainDB, $focus_value=''){  
  $oldDBname = to_defaultDB($mainDB);
  $SQL = "SELECT Name, Alias FROM FilterName WHERE Note = 1 GROUP BY Alias order by ID";  
  $NoteArr2 = $mainDB->fetchall($SQL);
  back_to_oldDB($mainDB, $oldDBname);
  for($i=0; $i<count($NoteArr2); $i++){
?>
  <option value="<?php echo $NoteArr2[$i]['Alias'];?>"<?php echo ($NoteArr2[$i]['Alias']==$focus_value)?" selected":"";?>><?php echo $NoteArr2[$i]['Name'];?><br>
<?php   
  }
}

function get_filterName($alias){
  global $mainDB;
  $oldDBName = to_defaultDB($mainDB);
  $filterName = "";
  $SQL = "SELECT Name FROM FilterName WHERE Alias='$alias' GROUP BY Alias";
  $filterNameArr = $mainDB->fetch($SQL);
  if($filterNameArr){
    $filterName = $filterNameArr['Name'];
  }
  back_to_oldDB($mainDB, $oldDBName);
  return $filterName;
}
 ?>
