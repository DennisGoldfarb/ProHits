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
$img_msg = '';

require("../common/site_permission.inc.php");
require("analyst/classes/coip_wstimages_class.php"); 

$Coip_WSTimages = new Coip_WSTimages();
$Coip_WSTimages->fetch($image_ID);


$bgcolor = "#e9e1c9";
$bgcolordark = "#a58a5a";

if($theaction == "overwrite"){
  $uploaded_file_name = '';
	$uploaded_file_type = '';
  if(isset($_FILES['frm_Image']['name'])){  
    $uploaded_file_name = $_FILES['frm_Image']['name'];
    $uploaded_file_type = $_FILES['frm_Image']['type'];
  }  
  if(strstr($uploaded_file_type,"jpeg") or strstr($uploaded_file_type,"gif")){
    //$new_pic_name = $image_ID . "_" . $uploaded_file_name;
    $new_pic_name = "P".$AccessProjectID."C".$coip_ID . "_" . $uploaded_file_name;
    if($new_pic_name != $Coip_WSTimages->Image){
	    $Coip_WSTimages->update($image_ID,$new_pic_name);
		  $Coip_WSTimages->fetch($image_ID);
		}
	  if (move_uploaded_file($_FILES['frm_Image']['tmp_name'], "./coip_images/" . $new_pic_name)) {
	    $img_msg = "image was successfully uploaded";
		}else{
	  	$img_msg = "<font color=#FF0000>Record has been added, but image didn't be uploaded. 
		  		possible file upload attack! Please try again</font>";
	  }
  }else{
	  if($uploaded_file_name){
	    $img_msg = "<font color=red>The image is not replaced since uploaded file is not gif or jpeg image.</font>";
		  $image_error = true;
	  }else if($image_ID){
	    $img_msg = "<font color=0000ff>previous image is used</font>";
	  }
  }
}
?>
<html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
	<title>Gel Image</title>
	<link rel="stylesheet" type="text/css" href="./site_style.css">
 <script language="Javascript" src="site_javascript.js"></script>
</head>
<BODY>
<center>
<script language="javascript">
function printit(){  
	if (window.print) {
	    window.print() ;  
	} else {
	    var WebBrowser = '<OBJECT ID="WebBrowser1" WIDTH=0 HEIGHT=0 CLASSID="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2"></OBJECT>';
	    document.body.insertAdjacentHTML('beforeEnd', WebBrowser);
	    WebBrowser1.ExecWB(6, 2);//Use a 1 vs. a 2 for a prompting dialog box    WebBrowser1.outerHTML = "";  
	}
}
<?php 
if($AUTH->Delete){?>
function overwriteImage(theForm){
  theForm.theaction.value = 'chang_image';
  theForm.submit();
}
function submitFile(theForm){
  theForm.theaction.value = 'overwrite';
  theForm.submit();
}
<?php }?>
</script>
<form name=coipImage method=post action=<?php echo $PHP_SELF;?> enctype="multipart/form-data">
<input type=hidden name=theaction value=<?php echo $theaction;?>>
<input type=hidden name=image_ID value="<?php echo $image_ID;?>">
<input type=hidden name=coip_ID value="<?php echo $coip_ID;?>">
<table border="0" cellpadding="0" cellspacing="0" width="97%">
  <tr>
    <td align="left" bgcolor=<?php echo $bgcolordark;?>>
		&nbsp; <font  face="helvetica,arial,futura" size="3"><b>CO-IP Western Image</b> 
		</font> 
	</td>
	<td align=right bgcolor=<?php echo $bgcolordark;?>> 
	  <a href="javascript: printit();" class=button><font color=white>[ Print ]</font></a>
	 <a href="javascript: window.close();" class=button><font color=white>[ Close the Window ]</font></a>&nbsp; &nbsp; 
	</td>
  </tr>
  <tr>
  	<td   colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="center" colspan=2>
    <table border="0" cellpadding="0" cellspacing="1" width="100%">
	  <tr bgcolor="">
	  <td width="20%" bgcolor="<?php echo $bgcolor;?>">&nbsp;
	    <font face="helvetica,arial,futura" size="2"><b>ID:</b></font> 
	  </td>
	  <td width="80%" bgcolor="<?php echo $bgcolor;?>">&nbsp;
	    <font  face="helvetica,arial,futura" size="2"><?php echo $Coip_WSTimages->ID;?></font>
	  </td> 
	  <tr>
	  <td width="" bgcolor="<?php echo $bgcolor;?>" nowrap>&nbsp;
	    <font  face="helvetica,arial,futura" size="2"><b>Name:</b></font> 
	  </td>
	  <td width="" bgcolor="<?php echo $bgcolor;?>">&nbsp;
	    <font  face="helvetica,arial,futura" size="2"><?php echo $Coip_WSTimages->Image;?></font> 
    </td>
    </tr>
    <tr>
    <td width="" bgcolor="<?php echo $bgcolor;?>" nowrap>&nbsp;
	  </td>
    <td width="" bgcolor="<?php echo $bgcolor;?>">&nbsp;  
	    <?php    
	    echo "$img_msg  &nbsp; &nbsp;";
	    if($AUTH->Delete and $theaction == "chang_image"){
	      echo "<input type='file' name='frm_Image' size='30'>";
		    echo " <input type=button value='Replace Image' onClick='submitFile(coipImage)'>";
	    }else if($AUTH->Delete ){
	      echo "<a href='javascript: overwriteImage(coipImage);'>Over Write the Image</a>";
	    }      
	    ?>
	  </td>
	  </tr>
     </table>
    </td>
	<tr>
	 <td align=center bgcolor="<?php echo $bgcolor;?>" colspan=2>
	    <img src="./coip_images/<?php echo $Coip_WSTimages->Image;?>" border=0>
	 </td>
	</tr>
  </tr>
</table>
</form>
</center>
</body>
</html>
