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
$sub = '';
$whichGel = '';
$theaction = '';
$theProjectName = '';
$order_by = '';
$start_point ='';
$error_msg = '';
$Bait_ID = 0;
$frm_Name = '';
$img_msg = '';
$searched_gel_str = '';
$searched_id_str = '';
$title_lable = '';
//--------for gel.inc.php-------------------
$Gel_ID = '';
$frm_Stain = '';
$frm_GelType ='';
$frm_Notes ='';
$frm_Image ='';
$frm_user_id = '';
//-------------------------------------------
require("../common/site_permission.inc.php");
require("analyst/classes/gel_class.php");
require("analyst/classes/plate_class.php");
require("common/page_counter_class.php");

include("analyst/common_functions.inc.php");
require("common/common_fun.inc.php");

define ("RESULTS_PER_PAGE", 30);
define ("MAX_PAGES", 15); //this is max page link to display

require("site_header.php");

$Log = new Log();
$Gels = new Gel();

$imageLocation = "./gel_images/";
if($searched_id_str) $searched_gel_str = $searched_id_str;
//processing move bait
//$Gel = new Gel();
if($whichGel == 'last'){
  $Gel_ID = $Gels->move_Gel('last');
}else if($whichGel == 'first'){
  $Gel_ID = $Gels->move_Gel('first');
}else if($whichGel == 'next' and $Gel_ID){
  $Gel_ID = $Gels->move_Gel('next',$Gel_ID);
}else if($whichGel == 'previous' and $Gel_ID){
  $Gel_ID = $Gels->move_Gel('previous', $Gel_ID);
}

if($theaction == "delete" AND $Gel_ID AND $AUTH->Delete ) {
  if($Gels->isOwner($Gel_ID, $AccessUserID) ){
     $error_msg = $Gels->delete($Gel_ID);
     $theaction = "viewall";
     if(!$error_msg){
       //add record into Log table
       $Desc = "";
       $Log->insert($AccessUserID,'Gel',$Gel_ID,'delete',$Desc,$AccessProjectID);
       //end of Log table
    }
  }
}
//print_r($AUTH);
$bgcolor = $TB_CELL_COLOR;
$bgcolordark = "#c5b781";
//echo $frm_Image."####";

?>
<script language="javascript">
function sortList(order_by){
  var theForm = document.del_form;
  theForm.order_by.value = order_by;
  theForm.submit();
}
function confirm_delete(Gel_ID){
  if(confirm("Are you sure that you want to delete the Gel?")){
    var theForm = document.del_form;
    theForm.Gel_ID.value = Gel_ID;
    theForm.theaction.value = 'delete';
    theForm.submit();
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
function move_Gel(whichGel){
  var theForm =  document.gel_form
  if(whichGel == 'last') { 
    theForm.whichGel.value = 'last';
  } else if(whichGel == 'first') {
    theForm.whichGel.value = 'first';
  } else if(whichGel == 'next') {
    theForm.whichGel.value = 'next';
  } else if(whichGel == 'previous') {
    theForm.whichGel.value = 'previous';
  }
  theForm.theaction.value = 'modify';
  theForm.frm_Name.value = '';
  theForm.submit();
}
function has_spe_character(str){
  if(!(/[-\w]/.test(str))){
    alert("Please enter characters '0-9,A-Z,a-z'and '_' only.");
  }
}

function change_user(theForm){
  theForm.start_point.value = 0;
  theForm.theaction.value = 'viewall';
  theForm.submit();
}
function sortList(order_by){
  var theForm = document.del_form;
  theForm.order_by.value = order_by;
  theForm.submit();
}
function Exp_Status(temp_point){
  var theForm = document.del_form;
  theForm.start_point.value = temp_point;
  theForm.submit();
}

</script>
<?php if($sub){?>
<table cellspacing="1" cellpadding="0" border="0" align=center>
<tr><td>
<?php if($sub != 3){?>
    <img src="./images/arrow_red_gel.gif" border=0>
<?php }?>      
    <img src="./images/arrow_green_bait.gif" border=0> 
    <img src="./images/arrow_green_exp.gif" border=0>
    <img src="./images/arrow_green_band.gif" border=0>   
    <img src="./images/arrow_green_well.gif" border=0>
</tr>
</table>
<?php }?>
<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td colspan=5><div class=maintext>
      <img src="images/icon_picture.gif"> View Gel Image 
      <img src="images/icon_purge.gif"> Delete
      <img src="images/icon_view.gif"> Modify 
      <img src="images/arrow_small.gif"> Next
      <img src='./images/icon_plate.gif' border=0> MS Not Completed
      <img src='./images/icon_plate_check.gif' border=0> MS Completed 
      <br><br>
      </div>
    </td>
  </tr>
  <tr>
    <td align="left" NOWRAP>
    &nbsp; <font color="<?php echo  $bgcolordark;?>" face="helvetica,arial,futura" size="5"><b><?php echo ($title_lable)?$title_lable:"Gels";?> </b></font>
<?php 
    if($AccessProjectName){
      echo "<font color='red' face='helvetica,arial,futura' size='3'><b>(Project $AccessProjectID: $AccessProjectName)</b></font>";
    }
    if($sub){
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color='green' face='helvetica,arial,futura' size='3'><b>(Submit Gel Sample)</b></font>";
    }
    ?>
   </td>
   
<?php if($theaction == "viewall" OR $theaction == "search" OR  !$theaction){
    if($sub == "3" || $sub == "4"){
      $total_records = $Gels->get_total(1);
    }else{
      $total_records = $Gels->get_total();
    }
?>
    <form name="del_form" method=post action="<?php echo $PHP_SELF;?>">
   <td align="left" valign="bottom" width="90%"> 
      <input type=hidden name=start_point value='<?php echo $start_point?>'>
   <?php if($theaction == 'viewall'){?>
      &nbsp;&nbsp;<font face="helvetica,arial,futura" size="2"><b>User</b></font>
      <?php $users_list_arr = show_project_users_list();?>
      <select id="frm_user_id" name="frm_user_id" onchange="change_user(this.form)">
        <option value="">All Users		            
      <?php foreach($users_list_arr as $key => $val){?>              
        <option value="<?php echo $key?>"<?php echo ($frm_user_id==$key)?" selected":"";?>>(<?php echo $key?>)<?php echo $val?>			
      <?php }?>
      </select> 
    <?php }else{
        echo "&nbsp;";
      }
?>
    </td>
<?php 
  }
?>         
  
    <td align="right" NOWRAP>
<?php if($AUTH->Insert) {?>
      <a href="gel.php?theaction=addnew<?php echo ($sub)?"&sub=$sub":"";?><?php echo ($Bait_ID==0)?"&Bait_ID=0":"";?>" class=button>[Add New]</a>&nbsp;
<?php }?>
<?php if($theaction != "viewall" && $theaction != "search" &&  $theaction) {?>
      <a href="gel.php?theaction=viewall<?php echo ($sub)?"&sub=$sub":"";?>" class=button>[Gel List]</a>&nbsp;
<?php }?>      
    </td>
  </tr>
  <tr>
    <td colspan=3 height=1 bgcolor="black"><img src="images/pixel.gif"></td>
  </tr>
<?php if($sub == "3" || $sub == "4"){?>  
  <tr>
    <td colspan=3 height=1 bgcolor="white">Please select a dummy Gel or create a new dummy Gel to submit.</td>
  </tr>
<?php }?>  
<tr>
    <td align="center" colspan=5 valign=top>
<?php 

if($theaction == "viewall" OR $theaction == "search" OR  !$theaction) {
  //page counter start here---------------------------------------------------------  
  $PAGE_COUNTER = new PageCounter('Exp_Status');
  $query_string = "sub=$sub";
  $caption = "Gels";
  if($order_by) { 
    $query_string .= "&order_by=".$order_by;
  }
  $page_output = $PAGE_COUNTER->page_links($start_point, $total_records, RESULTS_PER_PAGE, MAX_PAGES, str_replace(' ','%20',$query_string)); 
//end of page counter-----------------------------------------------------------------
  
  if($order_by == "") $order_by = "ID desc";
  if(!$start_point) $start_point = 0;
  if($sub == "3" || $sub == "4"){
    $Gels->fetchall($order_by, $start_point,RESULTS_PER_PAGE,1);
  }else if($theaction == 'search' and $searched_gel_str){
    $Gels->fetch_IDs($searched_gel_str);
    $page_output = '';
  }else{  
    $Gels->fetchall($order_by, $start_point,RESULTS_PER_PAGE);
  }  
  
  //adding or update $Gelstring into session
    echo "<font color=red face=\"helvetica,arial,futura\">".$error_msg."</font>";
?> 
  <table border="0" cellpadding="0" cellspacing="1" width="800">
  
  <input type=hidden name=theaction value="<?php echo $theaction?>">  
  <input type=hidden name=Gel_ID value="">
  <input type=hidden name=Bait_ID value='<?php echo $Bait_ID;?>'>
  <input type=hidden name=sub value=<?php echo $sub;?>>
  <input type=hidden name=order_by value='<?php echo $order_by;?>'>
  
  <input type=hidden name=searched_gel_str value='<?php echo $searched_gel_str;?>'>
  <input type=hidden name=title_lable value='<?php echo $title_lable;?>'>

  <tr>
    <td colspan=7 align=right><?php echo $page_output;?></td></tr>
  <tr bgcolor="">
    <td width="40" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
      <div class=tableheader>
    <a href="javascript: sortList('<?php echo ($order_by == "ID")? 'ID%20desc':'ID';?>');">ID</a>&nbsp;
    <?php if($order_by == "ID") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "ID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
    ?></div>
    </td>
    <td width="120" bgcolor="<?php echo $bgcolordark;?>" align=center nowrap><div class=tableheader>
      <div class=tableheader>
     <a href="javascript: sortList('<?php echo ($order_by == "Name")? 'Name%20desc':'Name';?>');">Gel Name</a>&nbsp;
    <?php if($order_by == "Name") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "Name desc") echo "<img src='images/icon_order_down.gif'>";
    ?> </div>
    </td>
    <td width="180" bgcolor="<?php echo $bgcolordark;?>" align=center>
      <div class=tableheader>Gel Image</div> 
    </td>
     
    </td>
    <td width="60" bgcolor="<?php echo $bgcolordark;?>" align=center>
      <div class=tableheader>Gel Type</div> 
    </td>
    <td width="100" bgcolor="<?php echo $bgcolordark;?>" align="center"><div class=tableheader>
      <div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "OwnerID")? 'OwnerID%20desc':'OwnerID';?>');">Uploaded By</a>&nbsp;
      <?php if($order_by == "OwnerID") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "OwnerID desc") echo "<img src='images/icon_order_down.gif'>";
    ?> </div>
    </td>
    <td width="100" bgcolor="<?php echo $bgcolordark;?>" align="center"><div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "DateTime")? 'DateTime%20desc':'DateTime';?>');">Created On</a>&nbsp;
    <?php if($order_by == "DateTime") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "DateTime desc") echo "<img src='images/icon_order_down.gif'>";
    ?> 
    </td>
    <td width="100" height="25" bgcolor="<?php echo $bgcolordark;?>" align="center">
      <div class=tableheader>Options</div>
    </td>
  </tr>
<?php 
//echo $Gels->count;
  for($i=0; $i < $Gels->count; $i++) {
  //highlightTR(theRow,  theAction,  highlightColor, defaultColor)
?>
    <tr  bgcolor='<?php echo $bgcolor;?>' onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $bgcolor;?>');">
      <td width="" align="left"><div class=maintext>&nbsp;
          <?php echo $Gels->ID[$i];?>&nbsp;
        </div>
      </td>
      <td width="" align="left"><div class=maintext>&nbsp;
          <?php echo $Gels->Name[$i];?>&nbsp;
        </div>
      </td>
      <td width="" align="left"><div class=maintext>&nbsp;
          <?php  echo $Gels->Image[$i];?>&nbsp;
        </div>
      </td>
      <td width="" align="center"><div class=maintext>&nbsp;
          <?php  echo $Gels->GelType[$i];?>&nbsp;
        </div>
      </td>
      <td width="" align="center"><div class=maintext>&nbsp;          
          <?php echo get_userName($mainDB, $Gels->OwnerID[$i])?>&nbsp;
        </div>
      </td>
      <td width="" align="center"><div class=maintext>&nbsp;
          <?php echo substr($Gels->DateTime[$i],0,10);?>&nbsp;
        </div>
      </td>
      <td width="" align="left"><div class=maintext>&nbsp; &nbsp;
  <?php 
    if($AUTH->Delete and $Gels->OwnerID[$i] == $AccessUserID){
      $SQL = "select ID from Lane where GelID='".$Gels->ID[$i]."'"; 
      if(!mysqli_num_rows(mysqli_query($HITSDB->link, $SQL))){
  ?>
          <a href="javascript:confirm_delete(<?php echo $Gels->ID[$i];?>);">
          <img border="0" src="images/icon_purge.gif" alt="Delete"></a>&nbsp;
  <?php   }
    }
    if($AUTH->Access){
  ?>
        <a href="gel.php?theaction=modify&Gel_ID=<?php echo $Gels->ID[$i];?><?php echo ($sub)?"&sub=$sub":"";?><?php echo ($Bait_ID==0)?"&Bait_ID=0":"";?>">
        <img border="0" src="images/icon_view.gif" alt="Modify"></a>&nbsp;
  <?php 
    }else{
      echo "<img src=\"images/icon_empty.gif\">";
    }
    
    if($Gels->Image[$i]){
       echo "<a href=\"javascript: view_image('" . $Gels->ID[$i] . "');\">";
       echo "<img src='./images/icon_picture.gif' border=0 alt='view gel image'>";
       echo "</a>";
    }else{
       echo "\n<img src='./images/icon_empty.gif'>";
    }
    if($hitType = get_hit_type($Gels->ID[$i],'Gel')){
   ?>
      <a href="./item_report.php?type=Gel&item_ID=<?php echo  $Gels->ID[$i];?>&hitType=<?php echo $hitType;?>" class=button>
      <img src="./images/icon_report.gif" border=0 alt="Gel Report">
      </a>   
   <?php  
    }else{
       echo "\n<img src='./images/icon_empty.gif'>";
    }
    if($sub){
   ?>
         <a href="bait.php?Gel_ID=<?php echo  $Gels->ID[$i];?>&theaction=viewall&frm_user_id=<?php echo $frm_user_id?><?php echo  ($sub)?"&sub=$sub":"";?>">    
         <img border="0" src="./images/arrow_small.gif" alt="submit sample step 2"></a>
  <?php }?> </div>
      </td>
    </tr>
  <?php 
  } //end for
  ?>    </form>
      </table>
<?php 
//----start addnew or insert----------------------
}elseif($theaction == "addnew" OR $theaction == "insert" ){ 
  if(($theaction == "insert") and $frm_Name and $AUTH->Insert) {
    $Gels->insert( $frm_Name, $frm_Stain, $frm_Notes,$frm_GelType,$AccessUserID);
    if($sub != "3" && $sub != "4"){ 
      $uploaded_file_name = $_FILES['frm_Image']['name'];
      $uploaded_file_type = $_FILES['frm_Image']['type'];
      if(strstr($uploaded_file_type,"jpeg") or strstr($uploaded_file_type,"gif")){
        $uploaded_file_name = preg_replace ( '/[^-+\w+\.]/', '', $uploaded_file_name );
        $new_pic_name = "P".$_SESSION["workingProjectID"]."G".$Gels->ID . "_" . $uploaded_file_name;
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
    if($theaction == "insert" && $AUTH->Insert) {
      echo "<center><font color='red' face='helvetica,arial,futura' size=3>";
      echo "Missing info. <b>Bold</b> field names are required to make the insert.";
      echo "</font></center>";
    }elseif($theaction == "addnew" && !$AUTH->Insert){
?>
    <tr>
      <td colspan=2 height=1 bgcolor="white">You don't have the permission to add a new Gel.</td>
    </tr> 
<?php   
      exit;
    }
?>
    <form name=gel_form method=post action=<?php echo $PHP_SELF;?>  enctype="multipart/form-data">
    <input type=hidden name=theaction value="insert">
    <input type=hidden name=Gel_ID value="<?php echo $Gel_ID;?>">
    <input type=hidden name=sub value=<?php echo $sub;?>><br> 
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
    <input type=hidden name=whichGel value=''>
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
		
<?php if($AUTH->Modify && ($Gels->OwnerID == $AccessUserID || $SuperUsers)){?>
           <input type="button" value="Modify" onClick="javascript: checkform(document.gel_form);" class=green_but>   
<?php }?>
           <input type="button" value="Back"  onClick="javascript: go_back();" class=green_but>
<?php if($sub){?> 
           <input type="button" value=" Next " class="green_but" onClick="javascript: select_gel(document.gel_form);">
<?php }?>
    </td>
  </tr>
 <?php 
 //print_r($Gels);
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
<br>
<?php 
require("site_footer.php");
?>

