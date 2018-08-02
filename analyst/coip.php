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

define ("RESULTS_PER_PAGE", 20);
define ("MAX_PAGES", 15); //this is max page link to display
$order_by = '';
$start_point = '';
$error_msg = '';
$Coip_ID = '';
$WID = 0;
$theaction = '';

$frm_Clone = '';
$frm_BaitGeneID = '';
$frm_BaitORF = '';
$frm_BaitGene = ''; 
$frm_TargetGeneID = '';
$frm_TargetORF = ''; 
$frm_TargetGene = '';
$frm_Interaction = ''; 
$frm_BaitExpression = '';
$frm_TargetExpression = ''; 
$frm_Description = '';
$frm_ImageName = '';
$frm_TargetNegControl = '';
$frm_ImageID = '';
$frm_Image = '';
$img_msg = '';
$bait_TaxID = '';
$target_TaxID = '';
$new_wImage_ID ='';

require("../common/site_permission.inc.php");
require("common/page_counter_class.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");

require("site_header.php");

$imageLocation = "./coip_images/";
$Log = new Log();

if($theaction == "delete" AND $Coip_ID AND $AUTH->Delete ) {
  $SQL = "DELETE FROM Coip WHERE ID = '$Coip_ID'"; 
  $mainDB->execute($SQL);
  $theaction = "viewall";
  $Desc = "$Coip_ID";
  $Log->insert($AccessUserID,'Coip',$Coip_ID,'delete',$Desc,$AccessProjectID);   
}

$bgcolor = "#bbd7ce";
$bgcolordark = "#94b4aa";
?>
<script language="javascript">
function getProteinInfo(fieldOjt1,fieldOjt2,fieldOjt3,fieldOjt4,fieldOjt5){  
  var GeneID=trimString(fieldOjt1.value);
  var LocusTag=trimString(fieldOjt2.value);  
  var GeneName=trimString(fieldOjt3.value);
  var TaxID = trimString(fieldOjt4.value);
  var new_species = trimString(fieldOjt5.value);
  var file = 'pop_proteinInfo.php?GeneID=' + GeneID + '&LocusTag=' + LocusTag + '&GeneName=' + GeneName + '&TaxID=' + TaxID + '&new_species=' + new_species + '&return_geneID=' + fieldOjt1.name + '&return_orf=' + fieldOjt2.name + '&return_gene=' + fieldOjt3.name + '&return_tax=' + fieldOjt4.name + '&return_species=' + fieldOjt5.name;  
  if(isEmptyStr(TaxID)){
    alert('Please Choose a species.');
  }else if(!isNumber(GeneID)){
    alert('Please type only numbers in GineID field.');  
  }else if(isEmptyStr(LocusTag) && isEmptyStr(GeneName) && isEmptyStr(GeneID)){
    alert('Please type Gene ID or Locus Tag or Gene Name.');
  }else{
    newwin = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=520,height=400');
    newwin.moveTo(1,1);   
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
function confirm_delete(Coip_ID){
  if(confirm("Are you sure that you want to delete the Coip?")){
    document.del_form.Coip_ID.value = Coip_ID;
    document.del_form.submit();
  }
}
function checkform(theForm){

  var the_clone = theForm.frm_Clone.value;
  var the_baitORF = theForm.frm_BaitGeneID.value;
  var the_targetORF = theForm.frm_TargetGeneID.value;
  var sel = theForm.frm_Interaction;
  
  var imageFullName = theForm.frm_Image.value;  
  var regEx = new RegExp('\\\\', 'gi');
  imageFullName = imageFullName.replace(regEx, '/');
  var imageNameArr = imageFullName.split('/');    
  var imageName = imageNameArr[imageNameArr.length-1];
  
  if(trimString(the_clone).length == 0 
  || trimString(the_baitORF).length == 0 
  || trimString(the_targetORF).length == 0
  || sel.options[sel.selectedIndex].value.length == 0){
    alert("Bold field names are required to make the insert.");
  }else if(imageName != "" && !(/(\.jpe?g|\.gif)$/i.test(imageName))){
    alert('uploaded file is not gif or jpeg image, please upload a gif or gpeg file');
  }else if(/[^-\w\.]/.test(imageName)){
    alert("File name should be made up with characters 'A-Z', 'a-z', '0-9', '-' and '.'.");
  }else{
    theForm.submit();
  }
}
function select_Coip(theForm){
      theForm.theaction.value = "";
      theForm.action = "bait.php";
      theForm.submit();
}
function trimString (str) {
  var str = this != window? this : str;
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
function view_image(coip_ID, image_ID)  {
  file = 'pop_coip_image.php?image_ID=' + image_ID + '&coip_ID=' + coip_ID;
  newwin = window.open(file,"Coip_image",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=750,height=600');
  newwin.moveTo(10,10);
}
function go_back(){
  document.location = "./coip.php?theaction=viewall";
}
function pop_select_image(){
  var file = 'pop_western_images.php';
  newwin = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=520,height=400');
  newwin.moveTo(1,1);
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
function remove_image(theForm){
  theForm.theaction.value = 'removeImage';
  theForm.submit();
}
</script>
 
<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td colspan=2><div class=maintext>
      <img src="images/icon_picture.gif"> View Western Image 
      <img src="images/icon_purge.gif"> Delete
      <img src="images/icon_view.gif"> Modify 
	<img src="images/icon_report.gif"> Bait report
      <br><br>
      </div>
    </td>
  </tr>
  <tr>
    <td align="left">
      &nbsp; <font color="<?php echo $bgcolordark;?>" face="helvetica,arial,futura" size="3"><b>Co-IP
<?php 
    if($AccessProjectName){
      echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font>";
    }
?>
      </b></font> 
    </td>
    <td align="right">
<?php if($AUTH->Insert) {?>
      <a href="coip.php?theaction=addnew<?php echo ($sub)?"&sub=1":"";?>" class=button>[Add New]</a>&nbsp;
<?php }?>
      <a href="coip.php?theaction=viewall<?php echo ($sub)?"&sub=1":"";?>" class=button>[Co-IP List]</a>&nbsp;
    </td>
  </tr>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="center" colspan=2 valign=top>
<?php 

if($theaction == "viewall" OR !$theaction) {
  //page counter start here---------------------------------------------------------  
  $SQL = "SELECT COUNT(ID) FROM Coip WHERE ProjectID=$AccessProjectID";  
  $row = mysqli_fetch_row(mysqli_query($mainDB->link, $SQL));        
  $total_records = $row[0];  
  $PAGE_COUNTER = new PageCounter();
  $query_string = "";
  $caption = "Co-IP";
  if($order_by){ 
    $query_string .= "&order_by=".$order_by;
  }
  $page_output = $PAGE_COUNTER->page_links($start_point, $total_records, RESULTS_PER_PAGE, MAX_PAGES, str_replace(' ','%20',$query_string)); 
//end of page counter-----------------------------------------------------------------
  
  if($order_by == ""){
    $order_by = "ID desc";
  }  
  if(!$start_point){
    $start_point = 0;
  }  
  $SQL = "SELECT 
      C.ID, 
      C.Clone,
      C.BaitGeneID, 
      C.BaitORF,
      C.TargetGeneID, 
      C.TargetORF, 
      C.Description, 
      C.Interaction, 
      C.BaitExpression, 
      C.TargetExpression, 
      C.TargetNegControl,
      C.WSTimgID,
      C.ProjectID,
      C.DateTime,
      W.Image
      FROM Coip C LEFT JOIN Coip_WSTimages W ON (C.WSTimgID=W.ID)";
  $Where = " WHERE C.ProjectID=$AccessProjectID";  
  $SQL .= $Where;
  if($order_by){
    $SQL .= " ORDER BY C.$order_by";
  }else{
    $SQL .= " ORDER BY C.ID DESC";
  }
  $SQL .= " LIMIT $start_point, ".RESULTS_PER_PAGE;  
  //echo $SQL;
    
  $coipResults = mysqli_query($mainDB->link, $SQL);
  if(!$coipResults){
    echo "false";
  }
  $num_rows = mysqli_num_rows($coipResults);
  //echo $num_rows;
  //adding or update $Coipstring into session
    echo "<font color=red face=\"helvetica,arial,futura\">".$error_msg."</font>";
?> 
  <table border="0" cellpadding="0" cellspacing="1" width="100%">
  <form name="del_form" method=post action="<?php echo $PHP_SELF;?>">
  <input type=hidden name=theaction value=delete>
  <input type=hidden name=Coip_ID value="">
  <tr>
    <td colspan=10 align=right><?php echo $page_output;?></td></tr>
  <tr bgcolor="">
    <td width="5%" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
      <div class=tableheader><a href="coip.php?theaction=viewall&order_by=<?php echo ($order_by == "ID")? 'ID%20desc':'ID';?>">
    ID</a>
    <?php if($order_by == "ID") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "ID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
    ?></div>
    </td>
    <td width="10%" bgcolor="<?php echo $bgcolordark;?>" align=center nowrap><div class=tableheader>
      <div class=tableheader><a href="coip.php?theaction=viewall&order_by=<?php echo ($order_by == "Clone")? 'Clone%20desc':'Clone';?>">
     Clone Number</a>
    <?php if($order_by == "Clone") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "Clone desc") echo "<img src='images/icon_order_down.gif'>";
    ?> </div>
    </td>
    <td width="10%" bgcolor="<?php echo $bgcolordark;?>" align=center onwrap><div class=tableheader>
      <div class=tableheader><a href="coip.php?theaction=viewall&order_by=<?php echo ($order_by == "BaitGeneID")? 'BaitGeneID%20desc':'BaitGeneID';?>">
     Bait GeneID</a>
    <?php if($order_by == "BaitGeneID") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "BaitGeneID desc") echo "<img src='images/icon_order_down.gif'>";
    ?> </div>
    </td>
    <td width="10%" bgcolor="<?php echo $bgcolordark;?>" align=center onwrap><div class=tableheader>
      <div class=tableheader><a href="coip.php?theaction=viewall&order_by=<?php echo ($order_by == "BaitORF")? 'BaitORF%20desc':'BaitORF';?>">
     Bait LocusTag </a>
    <?php if($order_by == "BaitORF") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "BaitORF desc") echo "<img src='images/icon_order_down.gif'>";
    ?> </div>
    </td>
    <td width="10%" bgcolor="<?php echo $bgcolordark;?>" align=center onwrap><div class=tableheader>
      <div class=tableheader><a href="coip.php?theaction=viewall&order_by=<?php echo ($order_by == "TargetGeneID")? 'TargetGeneID%20desc':'TargetGeneID';?>">
     Target GeneID</a>
    <?php if($order_by == "TargetGeneID") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "TargetGeneID desc") echo "<img src='images/icon_order_down.gif'>";
    ?> </div>
    </td>
    <td width="10%" bgcolor="<?php echo $bgcolordark;?>" align=center onwrap><div class=tableheader>
      <div class=tableheader><a href="coip.php?theaction=viewall&order_by=<?php echo ($order_by == "TargetORF")? 'Clone%20desc':'TargetORF';?>">
     Target LocusTag </a>
    <?php if($order_by == "TargetORF") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "TargetORF desc") echo "<img src='images/icon_order_down.gif'>";
    ?> </div>
    </td>
     <td width="10%" bgcolor="<?php echo $bgcolordark;?>" align=center onwrap>
        <div class=tableheader>Western Image</div>
     </td>
    <td width="7%" bgcolor="<?php echo $bgcolordark;?>" align=center>
      <div class=tableheader>Co-IP</div> 
    </td>
    <td width="8%" bgcolor="<?php echo $bgcolordark;?>" align="center"><div class=tableheader>
      <div class=tableheader><a href="coip.php?theaction=viewall&order_by=<?php echo ($order_by == "DateTime")? 'DateTime%20desc':'DateTime';?>">
      Date</a>
    <?php if($order_by == "DateTime") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "DateTime desc") echo "<img src='images/icon_order_down.gif'>";
    ?></div> 
    </td>
    <td width="12%" height="25" bgcolor="<?php echo $bgcolordark;?>" align="center">
      <div class=tableheader>Options</div>
    </td>
  </tr>
<?php   
  while(list(
    $ID, 
    $Clone,
    $BaitGeneID, 
    $BaitORF,
    $TargetGeneID, 
    $TargetORF, 
    $Description, 
    $Interaction, 
    $BaitExpression, 
    $TargetExpression, 
    $TargetNegControl,
    $WSTimgID,
    $ProjectID, 
    $DateTime,
    $WImage) = mysqli_fetch_array($coipResults)){      
  
    $color = "#ffffff";
    if($Interaction == 'Yes'){
  	  $color = "limegreen";
    }else if($Interaction == 'No'){
      $color = "red";
    }else if($Interaction == 'Possible'){
      $color = "yellow";
    }else if($Interaction == 'In Progress'){
      $color = "dodgerblue";
    }
    //$proteinSQL = "SELECT GeneName, GeneAliase FROM Protein_Class WHERE EntrezGeneID =$BaitGeneID";// OR LocusTag='$BaitORF'";
    $tmp_BaitGene = get_geneName($mainDB, $BaitGeneID);  
    
    //$proteinSQL = "SELECT GeneName, GeneAliase FROM Protein_Class WHERE EntrezGeneID =$TargetGeneID";// OR LocusTag='$TargetORF'";
    $tmp_TargetGene = get_geneName($mainDB, $TargetGeneID);
?>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php echo $ID;?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php echo $Clone;?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php  echo $BaitGeneID;?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php  echo $BaitORF." / " .$tmp_BaitGene;?>&nbsp;
      </div>
    </td>
    <td width="" align="center"><div class=maintext>&nbsp;
        <?php  echo $TargetGeneID;?>&nbsp;
      </div>
    </td>
    <td width="" align="center"><div class=maintext>&nbsp;
        <?php  echo $TargetORF." / " .$tmp_TargetGene;?>&nbsp;
      </div>
    </td>
    <td width="" align="center" color=<?php echo $color;?>><div class=maintext>&nbsp;
        <?php  echo $WImage;?>&nbsp;
      </div>
    </td>
    <td width="" align="center" bgcolor=<?php echo $color;?>><div class=maintext>
        <?php  echo $Interaction; ?>
      </div>
    </td>
    <td width="" align="center"><div class=maintext>&nbsp;
        <?php echo substr($DateTime,0,10);?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp; &nbsp;
<?php   if($AUTH->Delete) {?>
            <a href="javascript:confirm_delete(<?php echo $ID;?>);">
        <img border="0" src="images/icon_purge.gif" alt="Delete"></a>&nbsp;
<?php   }else{
      echo "<img src=\"images/icon_empty.gif\">";
    }
    if($AUTH->Access){
?>
      <a href="coip.php?theaction=modify&Coip_ID=<?php echo $ID;?>">
      <img border="0" src="images/icon_view.gif" alt="Detail"></a>&nbsp;
<?php   }else{
      echo "<img src=\"images/icon_empty.gif\">";
    } 
    if($WImage){
       echo "<a href=\"javascript: view_image('".$ID."','".$WSTimgID."');\">";
       echo "<img src='./images/icon_picture.gif' border=0 alt='view Coip image'>";
       echo "</a>";
    }else{
       echo "\n<img src='./images/icon_empty.gif'>";
    }
    if(!$BaitORF){
      $BaitORF = $tmp_BaitGene;
    }
    if(!$TargetORF){
      $TargetORF = $tmp_TargetGene;
    }
?>     
  <a href=search.php?ListType=Bait&searchThis=<?php echo $BaitORF;?>&targetORF=<?php echo $TargetORF;?>>
  <img src='./images/icon_report.gif' border=0 alt='bait report'></a>
 </div>
    </td>
  </tr>
<?php 
  }   //end while
  //-----$mainDB->change_db($oldDBname);
?>    </form>
      </table>
<?php 
}elseif($theaction == "addnew" OR $theaction == "insert" ) {

  if($theaction == "insert" and $frm_Clone and $frm_BaitGeneID and $frm_TargetGeneID and $frm_Interaction and $AUTH->Insert){ 
    $SQL ="INSERT INTO Coip SET 
        Clone='$frm_Clone', 
        BaitGeneID='$frm_BaitGeneID', 
        BaitORF='$frm_BaitORF',
        TargetGeneID='$frm_TargetGeneID', 
        TargetORF='$frm_TargetORF', 
        Description='$frm_Description', 
        Interaction='$frm_Interaction', 
        BaitExpression='$frm_BaitExpression', 
        TargetExpression='$frm_TargetExpression', 
        TargetNegControl='$frm_TargetNegControl',       
        ProjectID='$AccessProjectID',
        DateTime=now()";
    $LastID = $mainDB->insert($SQL);
        
    $uploaded_file_name = $_FILES['frm_Image']['name'];
    $uploaded_file_type = $_FILES['frm_Image']['type'];
    $new_wImage_ID = 0;    
    if(strstr($uploaded_file_type,"jpeg") or strstr($uploaded_file_type,"gif")){
      $new_pic_name = "P".$AccessProjectID."C".$LastID . "_" . $uploaded_file_name;
      $SQL ="INSERT INTO Coip_WSTimages SET 
        Image='$new_pic_name',
        ProjectID = '$AccessProjectID'";
      $new_wImage_ID = $mainDB->insert($SQL);	    
      
      //echo $new_pic_name;
     	if (move_uploaded_file($_FILES['frm_Image']['tmp_name'], $imageLocation . $new_pic_name)){
        $SQL ="UPDATE Coip SET 
          WSTimgID='$new_wImage_ID'
          WHERE ID='$LastID'"; 
        $mainDB->execute($SQL);
        $img_msg = "image was successfully uploaded";
        $frm_Image = $new_pic_name;
        $uploadFlag = 1;       
	    }else{
        $SQL ="DELETE FROM Coip_WSTimages
          WHERE ID='$new_wImage_ID'";
        $mainDB->execute($SQL);
        $new_wImage_ID = 0; 
        $frm_Image = "";
  	    $img_msg = "<font color=#FF0000>Possible file upload attack! Please try again</font>";
     	}
    }else{
      $frm_Image = "";
      if($uploaded_file_name){
        $img_msg = "<font color=red>uploaded file is not gif or jpeg image, please upload a gif or gpeg file</font>";
      }else{
        $img_msg = "<font color=#FF0000>no westen image uploaded</font>";
      }
    }
    
    $Desc = "$frm_Clone,$frm_BaitGeneID,$frm_TargetGeneID,interaction=$frm_Interaction,imageID=$new_wImage_ID";
    $Log->insert($AccessUserID,'Coip',$LastID,'insert',$Desc,$AccessProjectID);
    
    echo "<center><font color='green' face='helvetica,arial,futura' size=3>";
    echo "Insert completed ($img_msg).";
    echo "</font></center>";
    
    $theaction = "modify";
    $Coip_ID = $LastID;     
  }else{
    if($theaction == "insert") {
      echo "<center><font color='red' face='helvetica,arial,futura' size=3>";
      echo "Missing info. <b>Bold</b> field names are required to make the insert.";
      echo "</font></center>";
    }
    
?>  <br>
    <form name=Coip_form method=post action=<?php echo $PHP_SELF;?>  enctype="multipart/form-data">
    <input type=hidden name=theaction value="insert">
    <input type=hidden name=Coip_ID value="<?php echo $Coip_ID;?>">
    <input type=hidden name=new_wImage_ID value="<?php echo $new_wImage_ID;?>">
 <table border="0" cellpadding="0" cellspacing="1" width="550">
  <tr bgcolor="<?php echo $bgcolordark;?>">
    <td colspan="2" align="center" height=20>
     <div class=tableheader>New Co-IP</div>
    </td>
  </tr>
  <?php  
  //---------------------
  include("coip.inc.php"); 
  //---------------------
  ?>
  <tr bgcolor="<?php echo $bgcolor;?>" align="center">
    <td colspan="2"><input type="button" value="Save" onclick="javascript: checkform(document.Coip_form);"></td>
  </tr>
 </table>
     </form>
<?php 
  }
}//end of insert

if($theaction == "modify" OR $theaction == "update"  OR $theaction == "removeImage"){
//------------------------------------------------------------------------------------------------------------
  if($theaction == "update" and  $AUTH->Modify) {  
    if($frm_Clone and $frm_BaitGeneID and $frm_TargetGeneID and $frm_Interaction){
      if(isset($_FILES['frm_Image']['name'])){
        $uploaded_file_name = $_FILES['frm_Image']['name'];
        $uploaded_file_type = $_FILES['frm_Image']['type'];
        $frm_Image = '';        
        if(strstr($uploaded_file_type,"jpeg") or strstr($uploaded_file_type,"gif")){
          $new_pic_name = "P".$AccessProjectID."C".$Coip_ID . "_" . $uploaded_file_name;
          if (move_uploaded_file($_FILES['frm_Image']['tmp_name'], $imageLocation . $new_pic_name)){
            $SQL ="UPDATE Coip_WSTimages SET 
            Image='$new_pic_name'
            WHERE ID='$new_wImage_ID' AND ProjectID='$AccessProjectID'";
            $mainDB->update($SQL); 
            
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
      $SQL ="UPDATE Coip SET 
        Clone='$frm_Clone', 
        BaitGeneID='$frm_BaitGeneID', 
        BaitORF='$frm_BaitORF',
        TargetGeneID='$frm_TargetGeneID', 
        TargetORF='$frm_TargetORF', 
        Description='$frm_Description', 
        Interaction='$frm_Interaction', 
        BaitExpression='$frm_BaitExpression', 
        TargetExpression='$frm_TargetExpression', 
        TargetNegControl='$frm_TargetNegControl',";
       /* if($new_wImage_ID){
          $SQL = $SQL . "WSTimgID='$new_wImage_ID',";
        }*/       
        $SQL = $SQL . "ProjectID='$AccessProjectID',
        DateTime=now()
        WHERE ID='$Coip_ID'";        
      $mainDB->execute($SQL);
      
      
      echo "<center><font color='green' face='helvetica,arial,futura' size=3>";
      echo "Update completed ";
      if($img_msg){
        echo " ($img_msg).";
      }  
      echo "</font></center>";
      $Desc = "$frm_Clone,$frm_BaitGeneID,$frm_TargetGeneID,interaction=$frm_Interaction,imageID=$new_wImage_ID";
	    $Log->insert($AccessUserID,'Coip',$Coip_ID,'modify',$Desc,$AccessProjectID);
      $theaction = "modify";  
    }else{
      echo "<center><font color='red' face='helvetica,arial,futura' size=3>";
      echo "Missing info. <b>Bold</b> field names are required to make the insert.";
      echo "</font></center><br>";
    }
  }else if($theaction == "modify" and $Coip_ID){
    $SQL ="SELECT 
        Clone, 
        BaitGeneID, 
        BaitORF,
        TargetGeneID, 
        TargetORF, 
        Description, 
        Interaction, 
        BaitExpression, 
        TargetExpression, 
        TargetNegControl,       
        WSTimgID,
        ProjectID
        FROM Coip
        WHERE ID=$Coip_ID"; 
    $coipArr = $mainDB->fetch($SQL);     
    
    $frm_Clone = $coipArr['Clone'];
    $frm_BaitGeneID = $coipArr['BaitGeneID'];
    $frm_BaitORF = $coipArr['BaitORF'];
    $frm_TargetGeneID = $coipArr['TargetGeneID'];
    $frm_TargetORF = $coipArr['TargetORF'];
    $frm_Interaction = $coipArr['Interaction'];
    $frm_Description = $coipArr['Description'];
    $frm_BaitExpression = $coipArr['BaitExpression'];
    $frm_TargetExpression = $coipArr['TargetExpression'];
    $frm_TargetNegControl = $coipArr['TargetNegControl'];	   
    $new_wImage_ID = $frm_WSTimgID = $coipArr['WSTimgID'];
	  
    if($frm_WSTimgID){  
  	  $SQL ="SELECT Image FROM Coip_WSTimages 
            WHERE ID=$frm_WSTimgID";
      $Coip_WSTimagesArr = $mainDB->fetch($SQL);
      if(count($Coip_WSTimagesArr) && $Coip_WSTimagesArr['Image']){
  	    $frm_Image = $Coip_WSTimagesArr['Image'];
      }  
    }  
    
    //$proteinSQL = "SELECT GeneName, GeneAliase FROM Protein_Class WHERE EntrezGeneID =$frm_BaitGeneID";// OR LocusTag='$BaitORF'";
    $frm_BaitGene = get_geneName($mainDB, $frm_BaitGeneID);  
    
    //$proteinSQL = "SELECT GeneName, GeneAliase FROM Protein_Class WHERE EntrezGeneID =$frm_TargetGeneID";// OR LocusTag='$TargetORF'";
    $frm_TargetGene = get_geneName($mainDB, $frm_TargetGeneID);
  }

?> <br>
 <table border="0" cellpadding="0" cellspacing="1" width="550">
    <form name=Coip_form method=post action='<?php echo $PHP_SELF;?>' enctype="multipart/form-data">
    <input type=hidden name=theaction value=update>
    <input type=hidden name=Coip_ID value=<?php echo $Coip_ID?>>
    <input type=hidden name=new_wImage_ID value="<?php echo $new_wImage_ID;?>">
    <input type=hidden name=whichCoip value=''>
    
   
   <tr bgcolor="<?php echo $bgcolordark;?>">
    <td colspan="2" align="center" height=20>
      <div class=tableheader height=18>Modify Coip</div>
    </td>
  </tr>
    <?php 
  //-----------------------------------
  include("coip.inc.php");
  //-----------------------------------
  ?>
   <tr bgcolor="<?php echo $bgcolor;?>" align="center">
    <td colspan="2" valign=top>&nbsp;
     <?php  if($AUTH->Modify){?>
             <input type="button" value="Modify" onClick="javascript: checkform(document.Coip_form);" class=green_but> 
     <?php }?> 
             <input type="button" value="Back"  onClick="javascript: go_back();" class=green_but>
     
    </td>
  </tr> 
  </form>
  </table>
<?php }//end if?>
    </td>
  </tr>
</table>
<br>
<?php 
require("site_footer.php");

?>

