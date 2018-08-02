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
require("analyst/classes/bait_class.php");
require("analyst/classes/band_class.php"); 
require("analyst/classes/hits_class.php");
require("analyst/classes/lane_class.php");
require("analyst/classes/gel_class.php");
require("analyst/classes/baitDiscussion_class.php");

$theaction = '';
$bait_group_icon_arr = array();

require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");


$Bait = new Bait($Bait_ID);
$Log = new Log();
//all users can insert note into discussion table

$mod_BaitDiscussion = new BaitDiscussion();
if($theaction == "insert" ){
  $tmpBaitDiscussion = new BaitDiscussion();
  if($AUTH->Modify or !$frm_NoteType){
    $tmpBaitDiscussion->insert($Bait_ID, $frm_NoteType, mysqli_escape_string($mainDB->link, $frm_Note), $AccessUserID);
    $Desc = "BaitID=$Bait_ID,NoteType=$frm_NoteType";
    $Log->insert($AccessUserID,'BaitDiscussion',$tmpBaitDiscussion->ID,'insert',$Desc,$AccessProjectID);
  }
}
//user only delete his own note
if($theaction == "delete" and $frm_disID and $AUTH->Delete){
    $mod_BaitDiscussion->delete($frm_disID, $AccessUserID);
    $Desc = "ID=$frm_disID";
    $Log->insert($AccessUserID,'BaitDiscussion',$frm_disID,'delete',$Desc,$AccessProjectID);
}
if($theaction == "update" and $AUTH->Modify) {
	$tmpBaitDiscussion = new BaitDiscussion();
  $tmpBaitDiscussion->update($frm_disID, mysqli_escape_string($mainDB->link, $frm_Note), $AccessUserID);
  $Desc = "BaitID=$Bait_ID";
  $Log->insert($AccessUserID,'BaitDiscussion',$frm_disID,'update',$Desc,$AccessProjectID);
   
}
 
$bait_group_icon_arr = get_project_noteType_arr($HITSDB);

$BaitDiscussions = new BaitDiscussion();
$BaitDiscussions->fetchall($Bait_ID);
$bgcolordark = '#0080c0';
$bodycolor = '#ffffff';
$bgBaitcolor="#e1e1e1";
?>
<html>
<head>
 <link rel="stylesheet" type="text/css" href="./site_style.css"> 
 <script language="javascript">
 function confirm_delete(disID){
	if(confirm("Are you sure that you want to delete this hit notes?")){ 
    document.del_form.frm_disID.value = disID;
    document.del_form.theaction.value='delete';
		document.del_form.submit();
	}
 }
 function modify_note(disID){ 
	 document.del_form.frm_disID.value = disID;
	 document.del_form.theaction.value = "modify";
	 document.del_form.submit();
 }
 function update_note(){
 	 var disID=document.del_form.frm_disID.value;
   var theNote=document.del_form.frm_Note.value;  
   if(disID == '' || isEmptyStr(theNote)){
     alert("Note are required.");
		 return false;
   }else{
     document.del_form.theaction.value = 'update'; 
     document.del_form.submit();
   } 
 }
 function add_new_note(theForm){
  var theNote=theForm.frm_Note.value;
  if(theNote == '' || isEmptyStr(theNote)){
    alert("Note Type and Note are required.");
		return false;
  }else {
     theForm.theaction.value = 'insert';
     theForm.submit();
   } 
 }
 function refresh(){
     document.del_form.theaction.value = '';
     document.del_form.submit();
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
 function note_detail(dis_id){
   document.del_form.frm_disID.value = dis_id;
   document.del_form.theaction.value = 'notedetail'; 
   document.del_form.submit();
 }
 </script>
 <script language="Javascript" src="site_javascript.js"></script>
 </head>
 <body bgcolor=<?php echo $bodycolor;?>>
 <form name=del_form method=post action="<?php echo $PHP_SELF;?>">
 <input type=hidden name=Bait_ID value="<?php echo $Bait_ID;?>">
 <input type=hidden name=frm_disID value='<?php echo $frm_disID;?>'>
 <input type=hidden name=theaction value="">
 
 <table border="0" cellpadding="1" cellspacing="1" width="100%">  
	<tr bgcolor="">
	  <td align="center" colspan="4" bgcolor="<?php echo $bgcolordark;?>" >
		<font face="Arial" size="3" color="#FFFFFF">&nbsp;<b>Bait Notes <font face="Arial" size="-2" color=#ffffff></font></b></font>
    </td>
  </tr>
  <tr>
   <td bgcolor="<?php echo $bgBaitcolor;?>" width="15%"><span class=maintext><b>Bait ID</b></span></td>
   <td bgcolor="<?php echo $bgBaitcolor;?>" width="20%"><span class=maintext><?php echo $Bait->ID;?></span></td>
   <td bgcolor="<?php echo $bgBaitcolor;?>" width="15%"><span class=maintext><b>Bait Gene ID</b></span></td>
   <td bgcolor="<?php echo $bgBaitcolor;?>" ><span class=maintext><?php echo $Bait->GeneID;?></span></td>
  </tr>
  <tr> 
   <td bgcolor="<?php echo $bgBaitcolor;?>"><span class=maintext><b>Bait Gene Name</b></span></td>
   <td bgcolor="<?php echo $bgBaitcolor;?>"><span class=maintext><?php echo $Bait->GeneName;?></span></td>  
   <td bgcolor="<?php echo $bgBaitcolor;?>"><span class=maintext><b>Bait MW (kDa)</b></span></td>
   <td bgcolor="<?php echo $bgBaitcolor;?>"><span class=maintext><?php echo $Bait->BaitMW;?></span></td>
  </tr>
  <tr> 
   <td bgcolor="<?php echo $bgBaitcolor;?>"><span class=maintext><b>Bait Clone</b></span></td>
   <td bgcolor="<?php echo $bgBaitcolor;?>"><span class=maintext><?php echo $Bait->Clone;?></span></td>  
   <td bgcolor="<?php echo $bgBaitcolor;?>"><span class=maintext><b>Bait Description</b></span></td>
   <td bgcolor="<?php echo $bgBaitcolor;?>"><span class=maintext><?php echo $Bait->Description;?></span></td> 
  </tr>
</table>
 <br>

<?php 
if($BaitDiscussions->count) {
?>
<table border="0" cellpadding="1" cellspacing="1" width="100%">
<tr>
   <td width=150 bgcolor="<?php echo $bgBaitcolor;?>"><span class=maintext><b>Notes Type</b></span></td>
   <td width=510 bgcolor="<?php echo $bgBaitcolor;?>" align=center><span class=maintext><b>Notes</b></span></td>
   <td width=60 bgcolor="<?php echo $bgBaitcolor;?>"><span class=maintext><b>Added By</b></span></td>
   <td width=80 nowrap bgcolor="<?php echo $bgBaitcolor;?>"><span class=maintext><b>&nbsp;Added On</b></span></td>
   <td width=60 bgcolor="<?php echo $bgBaitcolor;?>"><span class=maintext>&nbsp;<b>Action</b></span></td>
</tr>
<?php 
}
 
$exist_type_arr = array();
$temp_icon = '';
$tem_initial = '';
for($i=0; $i<$BaitDiscussions->count; $i++){
  $tmpUser = get_userName($mainDB, $BaitDiscussions->UserID[$i]);
	//only the hote owner can modify and delete
	//only team member, owner, super user and user id = 1 can add notes 
  $tmpNote = nl2br(htmlspecialchars($BaitDiscussions->Note[$i]));
  $temp_icon = '';
  if(isset($bait_group_icon_arr[$BaitDiscussions->NoteType[$i]])){
    $tmpNoteType = $bait_group_icon_arr[$BaitDiscussions->NoteType[$i]]['Name'];
    $temp_icon = $bait_group_icon_arr[$BaitDiscussions->NoteType[$i]]['Icon'];
    $tem_initial = $bait_group_icon_arr[$BaitDiscussions->NoteType[$i]]['Initial'];
    array_push( $exist_type_arr, $BaitDiscussions->NoteType[$i]);
  }else{
    $tmpNoteType = "Discussion";
  }
?>

<tr>
<?php if(!is_numeric($tem_initial)){?>
    <td bgcolor="<?php echo $bgBaitcolor;?>" nowrap><div class=maintext>
    <?php 
    if($temp_icon){
     echo "<img src=./gel_images/$temp_icon border=0>\n";
    }else{
    
    }
    ?>
    <?php echo $tmpNoteType;?></div>
    </td>
<?php }else{?>
    <td bgcolor="<?php echo $bgBaitcolor;?>" nowrap><div class=maintext>
      <table border=0 cellpadding="1" cellspacing="1">
        <tr>
          <td class=tdback_star_image><?php echo $tem_initial;?></td>
        </tr>
      </table><?php echo $tmpNoteType;?></div>
    </td>
<?php }?>   
   
   <td bgcolor="<?php echo  $bgBaitcolor;?>"><span class=maintext><?php echo $tmpNote;?></span></td>
   <td bgcolor="<?php echo  $bgBaitcolor;?>"><span class=maintext><?php echo $tmpUser;?></span></td>
   <td bgcolor="<?php echo  $bgBaitcolor;?>"><span class=maintext><?php echo $BaitDiscussions->DateTime[$i];?></span></td>
   <td bgcolor="<?php echo $bgBaitcolor;?>">
    <?php if($AUTH->Delete and $BaitDiscussions->UserID[$i] == $AccessUserID) {?>
      <a href="javascript:confirm_delete(<?php echo $BaitDiscussions->ID[$i];?>);">
      <img border="0" src="images/icon_purge.gif" alt="Delete"></a>
    <?php }else{?> 
      <img src="images/icon_empty.gif" width=17>
    <?php }
      if($AUTH->Modify and ($BaitDiscussions->UserID[$i] == $AccessUserID or $SuperUsers)){
    ?>     
      <a href="javascript:modify_note(<?php echo $BaitDiscussions->ID[$i];?>);">
      <img border="0" src="images/icon_view.gif" alt="Modify"></a>&nbsp;
    <?php }else{ ?>
      <img src="images/icon_empty.gif" width=17>  
    <?php }?>
   </td>
</tr>
<?php 
}//end for

if($theaction == "modify" and $frm_disID){  
		$mod_BaitDiscussion = new BaitDiscussion($frm_disID, $AccessUserID);
}
    
?> 
</table>
 
<table border="0" cellpadding="1" cellspacing="1" width="100%">
  <tr>
    <td colspan=2 align=center><span class=maintext><b><?php  echo isset($mod_BaitDiscussion->ID)?"Modify":"New";?> Bait Note</b></span></td>
  </tr>
   
  <tr>
   <td bgcolor="<?php echo $bgBaitcolor;?>" ><span class=maintext><b>Notes Type</b></span></td>
   <td bgcolor="<?php echo $bgBaitcolor;?>">
   <?php 
   if(isset($mod_BaitDiscussion->ID)){
     if($mod_BaitDiscussion->NoteType >0){
      echo $bait_group_icon_arr[$mod_BaitDiscussion->NoteType]['Name'];
     }else{
      echo "Discussion";
     }
   }else{
   ?>
   <select name=frm_NoteType>
      <?php       
      echo "<option value='0'>Discussion\n";
      if($AUTH->Insert){
        foreach($bait_group_icon_arr as $key =>$rd){
          if(!in_array($key, $exist_type_arr)){
            $tmp_initial = $rd['Initial'];
            if(is_numeric($rd['Initial'])) $tmp_initial = "VS".$rd['Initial'];
            echo "<option value='".$key."'>".$rd['Name']." (".$tmp_initial.")\n";
          }
        }
      }
      ?>
    </select>
   </td>
  </tr>
  <?php }?>
  <tr>
   <td bgcolor="<?php echo $bgBaitcolor;?>" valign=top><span class=maintext><b>Notes</b></span></td>
   <td bgcolor="<?php echo $bgBaitcolor;?>"><span class=maintext>
    <textarea cols=50 rows=4 name=frm_Note><?php  echo isset($mod_BaitDiscussion->ID)?$mod_BaitDiscussion->Note:"";?></textarea></td>
  </tr>
</table>

<center>
<?php if(isset($mod_BaitDiscussion->count) && $mod_BaitDiscussion->count){?>
	<input type=button value=' Update ' onClick='javascript: update_note(this.form)'; class=black_but>
<?php }else{?>
	<input type=button value='Save New Notes' onClick='javascript: add_new_note(this.form)'; class=black_but>
<?php }?>
<input type=button value=' Refresh ' onClick='javascript: refresh()'; class=black_but>
<input type=button value=' Close ' onClick='javascript: window.close();' class=black_but>
</center>
</form>
 </body>
 </html>
