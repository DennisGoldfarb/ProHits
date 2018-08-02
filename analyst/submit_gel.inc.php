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

require("classes/gel_class.php");
require("classes/plate_class.php");
$Gels = new Gel();

$imageLocation = "./gel_images/";
$bgcolor = $TB_CELL_COLOR;
$bgcolordark = "#c5b781";

?>
<script language="javascript">
function confirm_delete(Gel_ID){
  if(confirm("Are you sure that you want to delete the Gel?")){
    document.del_form.Gel_ID.value = Gel_ID;
    document.del_form.submit();
  }
}
function checkform(theForm){
  if(theForm.frm_GelType.value != "dummy"){
    var the_name = theForm.frm_Name.value;
    var the_stain = theForm.frm_Stain.value;
    var imageFullName = theForm.frm_Image.value;  
    var regEx = new RegExp('\\\\', 'gi');
    imageFullName = imageFullName.replace(regEx, '/');
    var imageNameArr = imageFullName.split('/')
    var imageName = imageNameArr[imageNameArr.length-1];
    if(imageName != "" && !(/(\.jpe?g|\.gif)$/i.test(imageName))){
      alert('uploaded file is not gif or jpeg image, please upload a gif or gpeg file');
    }else if(/[^-\w\.]/.test(imageName)){
      alert("File name should be made up with characters 'A-Z', 'a-z', '0-9', '-' and '.'.");
    }else if(the_name == '' || trimString(the_name) == 0 || the_stain == ''){
      alert("Bold field names are required to make the insert.");
    }else{
      theForm.submit();
    }
  }else{
    theForm.submit();
  }    
}

function remove_image(theForm){
  theForm.theaction.value = 'removeImage';
  theForm.submit();
}

function select_gel(theForm){
      theForm.theaction.value = "";      
      theForm.action = "bait.php";      
      theForm.submit();
}
function trimString (str) {
  var str = this != window? this : str;
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
function go_back(){
  document.location = "./gel.php?theaction=viewall<?php echo ($sub)?"&sub=$sub":"";?>";
}
function check_spillover(Gel_ID){
  file = "checkspillover.php?Gel_ID="+Gel_ID;
  newwin = window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=550,height=400');
  newwin.moveTo(0,0);
}
function has_spe_character(str){
  if(!(/[-\w]/.test(str))){
    alert("Please enter characters '0-9,A-Z,a-z'and '_' only.");
  }
}
function comfirm_selected_gel(theForm){
  var Gel_ID = theForm.Gel_ID.value;
  if(Gel_ID == ''){
    alert("Please select a gel");
    return;
  }
  var x = theForm.Gel_ID;
  var gel_name = x.options[x.selectedIndex].text;
  if(confirm("Select gel '"+ gel_name +"' and continue?")){
    opener.document.editform.passed_Gel_ID.value = Gel_ID;
    opener.document.editform.submit();
    theForm.addNewType.value = 'Sample';
    theForm.theaction.value = 'addNew';
    theForm.submit();
  }
}
function goto_new_sample(theForm){
  theForm.addNewType.value = 'Sample';
  theForm.theaction.value = 'addnew';
  theForm.submit();
}
function add_new_gel(){
  theForm = document.list_form;
  theForm.submit();
}
</script>
<table border="0" cellpadding="0" cellspacing="0" width="90%">
<tr>
    <td colspan=2 height=1 bgcolor="black"><img src="images/pixel.gif"></td>
</tr> 
<tr>
    <td align="center" colspan=2 valign=top>
<?php 
if($theaction == "list"){
  $SQL = "SELECT `ID`, `Name` FROM `Gel` WHERE `ProjectID`='$ProjectID' ORDER BY `ID` DESC";
  $gel_arr = $HITSDB->fetchAll($SQL);
  $gelTitlle = "Select or Add a Gel";
?>  
<form name=list_form method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=theaction value="addnew">
<input type=hidden name=sub value=<?php echo $sub;?>>
<input type=hidden name=ProjectID value='<?php echo $ProjectID?>'>
<input type=hidden name=addNewType value='<?php echo $addNewType?>'>
<input type=hidden name=gelMode value='<?php echo $gelMode?>'>
<input type=hidden name=Bait_ID value='<?php echo $Bait_ID?>'>
<input type=hidden name=Exp_ID value='<?php echo $Exp_ID?>'>
<input type=hidden name=DBname value=<?php echo $DBname;?>>
<table border="0" cellpadding="0" cellspacing="1" width="500">
  <tr bgcolor="<?php echo $bgcolordark;?>">
    <td colspan="2" align="center" height=20>
     <div class=tableheader><?php echo $gelTitlle;?></div>
    </td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor;?>" align="center">
    <td colspan="2">
    <select name="Gel_ID"  onChange="comfirm_selected_gel(this.form);">
    <option value=''>-- select gel --
    <?php 
    foreach($gel_arr as $gel_info){
      echo "  <option value=".$gel_info['ID'].">(".$gel_info['ID'].")".$gel_info['Name']."\n"; 
    }
    ?>
    </select>
    &nbsp;&nbsp;<a href="javascript: add_new_gel();" class=button>[new]</a>
    </td>
  </tr>
 </table>
    
<?php 
}elseif($theaction == "addnew" OR $theaction == "insert" ){
  if(($theaction == "insert") and $frm_Name and $AUTH->Insert) {
    $Gels->insert( $frm_Name, $frm_Stain, $frm_Notes,$frm_GelType,$AccessUserID);
    if($sub != "3" && $sub != "4"){ 
      $uploaded_file_name = $_FILES['frm_Image']['name'];
      $uploaded_file_type = $_FILES['frm_Image']['type'];
      if(strstr($uploaded_file_type,"jpeg") or strstr($uploaded_file_type,"gif")){
        $uploaded_file_name = preg_replace ( '/[^-+\w+\.]/', '', $uploaded_file_name );
        $new_pic_name = "P".$ProjectID."G".$Gels->ID . "_" . $uploaded_file_name;
        if (move_uploaded_file($_FILES['frm_Image']['tmp_name'], $imageLocation . $new_pic_name)){
          $Gels->update_image($Gels->ID, $new_pic_name);  
          $img_msg = "image was successfully uploaded";
          $frm_Image = $new_pic_name;
  	    }else{
          $frm_Image = "";
  	      $img_msg = "<font color=#FF0000>Possible file upload attack! Please try again</font>";
        }
      }else{
        $frm_Image = "";
        if($uploaded_file_name){
          $img_msg = "<font color=red>uploaded file is not gif or jpeg image, please upload a gif or gpeg file</font>";
        }else{
          $img_msg = "<font color=#FF0000>no gel image uploaded</font>";
        }
      }
    }      
    //add record into Log table
    $Desc = "Name=$frm_Name,Image=$frm_Image,Stain=$frm_Stain";
    $Log->insert($AccessUserID,'Gel',$Gels->ID,'insert',$Desc,$AccessProjectID);
    
    //end of Log table
    echo "<center><font color='green' face='helvetica,arial,futura' size=3>";
    echo "Insert completed ($img_msg).";
    echo "</font></center>";
    //after insert change the action
    $theaction = "modify";
    $Gel_ID = $Gels->ID;
  } else {
    if($theaction == "insert") {
      echo "<center><font color='red' face='helvetica,arial,futura' size=3>";
      echo "Missing info. <b>Bold</b> field names are required to make the insert.";
      echo "</font></center>";
    }
?>
    <form name=gel_form method=post action=<?php echo $PHP_SELF;?>  enctype="multipart/form-data">
    <input type=hidden name=theaction value="insert">
    <input type=hidden name=Gel_ID value="<?php echo $Gel_ID;?>">
    <input type=hidden name=sub value=<?php echo $sub;?>>
    <input type=hidden name=ProjectID value='<?php echo $ProjectID?>'>
    <input type=hidden name=addNewType value='<?php echo $addNewType?>'>
    <input type=hidden name=gelMode value='<?php echo $gelMode?>'>
    <input type=hidden name=Bait_ID value='<?php echo $Bait_ID?>'>
    <input type=hidden name=Exp_ID value='<?php echo $Exp_ID?>'>
    <input type=hidden name=DBname value=<?php echo $DBname;?>>
    <br> 
 <table border="0" cellpadding="0" cellspacing="1" width="500">
  <tr bgcolor="<?php echo $bgcolordark;?>">
    <td colspan="2" align="center" height=20>
<?php 
  if($sub == "3" || $sub == "4"){
    $gelTitlle = "New Dummy Gel";
  }else{
    $gelTitlle = "New Gel";
  }
?>
     <div class=tableheader><?php echo $gelTitlle;?></div>
    </td>
  </tr>
  <?php  
  //---------------------
  include("gel.inc.php"); 
  //---------------------
  ?>  
  <tr bgcolor="<?php echo $bgcolor;?>" align="center">
    <td colspan="2"><input type="button" value="Save" onclick="javascript: checkform(document.gel_form);"></td>
  </tr>
 </table>
     </form>
<?php 
  }//-----------------end of insert
}
if($theaction == "modify" OR $theaction == "update" OR $theaction == "removeImage") {
  if($theaction == "update"){  
    if($frm_Name ){             
      if(isset($_FILES['frm_Image']['name'])){
        $uploaded_file_name = $_FILES['frm_Image']['name'];
        $uploaded_file_type = $_FILES['frm_Image']['type'];
        $frm_Image = '';        
        if(strstr($uploaded_file_type,"jpeg") or strstr($uploaded_file_type,"gif")){
          $uploaded_file_name = preg_replace ( '/[^-+\w+\.]/', '', $uploaded_file_name );
          $new_pic_name = "P".$_SESSION["workingProjectID"]."G". $Gel_ID . "_" . $uploaded_file_name;
          if (move_uploaded_file($_FILES['frm_Image']['tmp_name'], $imageLocation . $new_pic_name)){
            $img_msg = "image was successfully uploaded";
      	    $frm_Image = $new_pic_name;                    
          }else{
      	    $img_msg = "<font color=#FF0000>Possible file upload attack! Please try again</font>";
          }
        }else{             
          if($uploaded_file_name){
            $img_msg = "<font color=red>uploaded file is not gif nor jpeg image, please upload a gif or jpeg file.</font>";
          }else{
            $img_msg = "<font color=#FF0000>no new gel image uploaded</font>";
          }
        }
      }      
      $Gels->update($Gel_ID, $frm_Name, $frm_Image, $frm_Stain, $frm_Notes, $frm_GelType);
      
      echo "<center><font color='green' face='helvetica,arial,futura' size=3>";
      echo "Update completed ";
      if($img_msg){
        echo " ($img_msg).";
      }  
      echo "</font></center>";
     //add record into Log table
      $Desc = "Name=$frm_Name,Image=$frm_Image,Stain=$frm_Stain";
      $Log->insert($AccessUserID,'Gel',$Gel_ID,'modify',$Desc,$AccessProjectID);
      //end of Log table
      $theaction = "modify";  
    }else{
      echo "<center><font color='red' face='helvetica,arial,futura' size=3>";
      echo "Missing info. <b>Bold</b> field names are required to make the insert.";
      echo "</font></center><br>";
    }
  }
  $Gels->fetch($Gel_ID);
  if($theaction == "modify"){
    $frm_Name = $Gels->Name;
    $frm_Image = $Gels->Image;
    $frm_Stain = $Gels->Stain;
    $frm_GelType = $Gels->GelType;
    $frm_OwnerID = $Gels->OwnerID;
    $frm_Notes = $Gels->Notes;    
  }
?> <br>
 <table border="0" cellpadding="0" cellspacing="1" width="500">
    <form name=gel_form method=post action='<?php echo $PHP_SELF;?>' enctype="multipart/form-data">
    <input type=hidden name=theaction value=update>
    <input type=hidden name=Gel_ID value=<?php echo $Gel_ID?>>
    <input type=hidden name=sub value=<?php echo $sub;?>>
    <input type=hidden name=ProjectID value='<?php echo $ProjectID?>'>
    <input type=hidden name=addNewType value='<?php echo $addNewType?>'>
    <input type=hidden name=gelMode value='<?php echo $gelMode?>'>
    <input type=hidden name=Bait_ID value='<?php echo $Bait_ID?>'>
    <input type=hidden name=Exp_ID value='<?php echo $Exp_ID?>'>
    <input type=hidden name=DBname value=<?php echo $DBname;?>>
   <tr bgcolor="<?php echo $bgcolordark;?>">
    <td colspan="2" align="center" height=20>
<?php 
  if($sub == "3" || $sub == "4" || $frm_GelType == "dummy"){
    $gelTitlle = "Modify Dummy Gel";
  }else{
    $gelTitlle = "Modify Gel";
  }
?>    
      <div class=tableheader height=18><?php echo $gelTitlle;?></div>
    </td>
  </tr>
  <?php 
  //-----------------------------------
  include("gel.inc.php");
  //-----------------------------------
  ?>
   <tr bgcolor="<?php echo $bgcolor;?>" align="center">
    <td colspan="2" valign=top>
		   <input type="button" value=" Close Window " class="green_but" onClick="javascript: window.close();">     
<?php if($AUTH->Modify && ($Gels->OwnerID == $AccessUserID || $SuperUsers)){?>
       <input type="button" value="Modify" onClick="javascript: checkform(document.gel_form);" class=green_but>   
<?php }?>
<?php if($sub){?> 
       <input type="button" value=" Next " class="green_but" onClick="javascript: goto_new_sample(this.form);">
<?php }?>
    </td>
  </tr>
 <?php 
$tmp_Gel = new Gel();
//it will return num of lanes in diff plates
  if($tmp_Gel->get_plate_ids($Gel_ID)){  
    $tmp_Plate = new Plate();
  ?>
    <tr bgcolor="<?php echo $bgcolordark;?>">
      <td align="center"><div class=tableheader height=18>Gel Lane</td>
      <td align="center"><div class=tableheader height=18>In Plate</td>
    </tr>
  <?php  
  	$lane_str = '';
    for($i = 0; $i<$tmp_Gel->count;$i++){ 
    
      $lane_str .= "<a href=band.php?Lane_ID=".$tmp_Gel->Lane_ID[$i]."&sub=$sub>".$tmp_Gel->Lane_num[$i]."</a>, ";
      if($tmp_Gel->Plate_ID[$i] != $tmp_Gel->Plate_ID[$i+1]){ //if its the end of the plate id
        $tmp_Plate->fetch($tmp_Gel->Plate_ID[$i]);
  ?>
        <tr bgcolor="<?php echo $bgcolordark;?>">
          <td><font color=white face=Arial size=2>&nbsp; <?php  echo $lane_str;?></font></td>
          <td><font color=white face=Arial size=2>&nbsp;
  <?php  
        echo "Plate ID:<b> $tmp_Plate->ID </b> Plate Name: $tmp_Plate->Name ";
        echo "<a href=plate_show.php?Plate_ID=$tmp_Plate->ID&theaction=showone>";        
        if($tmp_Plate->MSDate){
          echo "<img src='./images/icon_plate_check.gif' border=0>";
        }else{
          echo "<img src='./images/icon_plate.gif' border=0>";
        }
        echo"</a>";
  ?>
         </font></td>
      </tr>
  <?php  
            $lane_str ='';
      }//end if
    }//end for?>    
     <tr bgcolor="<?php echo $bgcolordark;?>">
       <td colspan=2 align=center>
        <input type=button value="Check SpillOver" onClick="javascript: check_spillover(<?php echo $Gel_ID;?>);" class=black_but>
  	 <?php if($USER->Username =='super' and 0){?>
        <a href=gel_correct_bait_id.php?Gel_ID=<?php echo $Gel_ID;?> target=new>correct bait id</a>
  	 <?php }?>
       </td>
     </tr>
  <?php 
  }//end if ?>
  </form>
  </table>
<?php 
} //end if
  ?>  
    </td>
  </tr>
</table>

