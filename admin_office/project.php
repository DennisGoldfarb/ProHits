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


$error_msg = '';
$Project_ID = '';
$frm_Name = '';
$frm_TaxID = '';
$frm_FilterSetID = '';
$frm_DBname = '';
$frm_Frequency = '';
$frm_Description= '';
$frm_LabID = '';
$frm_Date = '';

require_once("../common/site_permission.inc.php");
require_once("./common_functions.inc.php");
include("admin_log_class.php");
include("./admin_header.php");

$AdminLog = new AdminLog();

$Projects = new Projects();
if($theaction == "delete" AND $Project_ID AND $AUTH->Delete ){
  $SQL = "SELECT UserID FROM ProPermission WHERE ProjectID=$Project_ID";
  $UserIDArr2 = $mainDB->fetchAll($SQL);
  if($UserIDArr2){
    $UserIDStr = '';
    for($i=0; $i<count($UserIDArr2); $i++){
      if($UserIDStr){
        $UserIDStr .= "|";
      }  
      $UserIDStr .= $UserIDArr2[$i]['UserID'];
    }    
    //echo $UserIDStr;exit;
    $Desc = "userIDs=" . $UserIDStr;
    $SQL = "DELETE FROM ProPermission WHERE ProjectID=$Project_ID";
    $mainDB->execute($SQL);
    $AdminLog->insert($AccessUserID,'ProPermission',$Project_ID,'delete',$Desc);
  }
  $error_msg = $Projects->delete($Project_ID);
  $Desc = "";    
  $AdminLog->insert($AccessUserID,'Projects',$Project_ID,'delete',$Desc);
  $theaction = "viewall";
}

$SQL="SELECT ID, Name FROM FilterSet ORDER BY ID";
$FilterSetsArr = $mainDB->fetchAll($SQL);
$SQL="SELECT ID, Name FROM Lab ORDER BY ID";
$labArr = $mainDB->fetchAll($SQL);

$bgcolor = "#e9e1c9";
$bgcolordark = "#c5b781";

?>
<script language="javascript">
function confirm_delete(Project_ID){
  if(confirm("Are you sure that you want to delete the Project?")){
    document.del_form.Project_ID.value = Project_ID;
    document.del_form.submit();
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

function checkform(theForm){
  if(theForm.frm_Name.value == ''){
    alert('Please enter project name.');
    theForm.frm_Name.focus();
  }else if(theForm.frm_TaxID.value == ''){
    alert('Please select a Species.');
    theForm.frm_TaxID.focus();
  }else if(theForm.frm_FilterSetID.value == ''){
    alert('Please select a FilterSetID.');
    theForm.frm_FilterSetID.focus();
  }else if(theForm.frm_DBname.value == ''){
    alert('Please select a Database name.');
    theForm.frm_DBname.focus();
  }else if(theForm.frm_Frequency.value != '' && !isNumber(theForm.frm_Frequency.value)){
    alert('Please enter numbers for Frequency.');
    theForm.frm_Frequency.value = ''
    theForm.frm_Frequency.focus();  
  }else if(theForm.frm_Description.value == ''){
    alert('Please enter description.');
    theForm.frm_Description.focus();
  }else if(theForm.frm_LabID.value == ''){
    alert('Please select a lab.');
    theForm.frm_LabID.focus();
  }else if(theForm.frm_Date.value == ''){
    alert('Please enter date.'); 
    theForm.frm_Date.focus();   
  }else{   
    theForm.submit();
  }
}

function trimString (str) {
  var str = this != window? this : str;
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
function go_back(){
  document.location = "./project.php?theaction=viewall";
}
function getToday(Field){
  <?php  $today =@time()+5*60;?>
  //var Today = '<?php echo @date("Y-m-d H:i:s",$today);?>';
  var Today = '<?php echo @date("Y-m-d",$today);?>';
  Field.value = Today;
}
</script>

<table border="0" cellpadding="0" cellspacing="0" width="90%">  
  <tr>
    <td align="left">
      &nbsp; <font color="<?php echo $bgcolordark;?>" face="helvetica,arial,futura" size="3"><b>Projects</b></font>   
    </td>
    <td align="right">
<?php if($AUTH->Insert) {?>
      <a href="project.php?theaction=addnew" class=button>[Add New]</a>&nbsp;
<?php }?>
      <a href="project.php?theaction=viewall" class=button>[Project List]</a>&nbsp;
    </td>
  </tr>
  <br>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>  
  <tr>    
    <td align="center" colspan=2 valign=top>
<?php 

if($theaction == "viewall" OR !$theaction){ 
  $Projects->fetchall();  
  echo "<font color=red face=\"helvetica,arial,futura\">".$error_msg."</font>";
?> 
<br>
  <table border="0" cellpadding="0" cellspacing="1" width="900">
  <form name="del_form" method=post action="<?php echo $PHP_SELF;?>">  
    <input type=hidden name=theaction value=delete>
    <input type=hidden name=Project_ID value="">   
  <tr bgcolor="">
    <td width="5%" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center><div class=tableheader>ID</td>
    <td width="15%" bgcolor="<?php echo $bgcolordark;?>" align=center onwrap><div class=tableheader>Project Name</td>
    <td width="18%" bgcolor="<?php echo $bgcolordark;?>" align=center><div class=tableheader>Species</div></td>
    <td width="8%" bgcolor="<?php echo $bgcolordark;?>" align=center><div class=tableheader>Filter Set</div></td>
    <td width="8%" bgcolor="<?php echo $bgcolordark;?>" align=center><div class=tableheader>Hits DB</div></td>
    <td width="8%" bgcolor="<?php echo $bgcolordark;?>" align=center><div class=tableheader>Frequency</div></td>    
    <td  bgcolor="<?php echo $bgcolordark;?>" align=center><div class=tableheader>Description</div></td>
    <td width="8%" bgcolor="<?php echo $bgcolordark;?>" align="center"><div class=tableheader>Lab Name</td>
    <td width="8%" bgcolor="<?php echo $bgcolordark;?>" align="center"><div class=tableheader>Date</td>
    <td width="8%" bgcolor="<?php echo $bgcolordark;?>" align="center"><div class=tableheader>Options</td>    
  </tr>
<?php 
  for($i=0; $i < $Projects->count; $i++){
  $SQL="SELECT Name FROM FilterSet WHERE ID=".$Projects->FilterSetID[$i];
  $FilterSetsName = $mainDB->fetch($SQL);
  $SQL="SELECT Name FROM Lab WHERE ID=".$Projects->LabID[$i];  
  $LabName = $mainDB->fetch($SQL);
  $Species = get_TaxID_name($mainDB, $Projects->TaxID[$i]);
  $frequency = '';
  if($Projects->Frequency[$i] != ''){
    $frequency = $Projects->Frequency[$i]."%";
  }
?>
    <tr bgcolor="<?php echo $bgcolor;?>">
      <td width="" align="left" valign="top"><div class=maintext>&nbsp;
          <?php echo $Projects->ID[$i];?>&nbsp;
        </div>
      </td>
      <td width="" align="left" valign="top"><div class=maintext>
          <?php echo $Projects->Name[$i];?>&nbsp;
        </div>
      </td>
      <td width="" align="left" valign="top"><div class=maintext>
          <?php  echo $Species;?>&nbsp;
        </div>
      </td>         
      <td width="" align="left" valign="top"><div class=maintext>&nbsp;
          <?php  echo $FilterSetsName['Name'];?>&nbsp;
        </div>
      </td>
      <td width="" align="left" valign="top"><div class=maintext>&nbsp;
          <?php echo $Projects->DBname[$i];?>&nbsp;
        </div>
      </td>
      <td width="" align="left" valign="top"><div class=maintext>&nbsp;
          <?php echo $frequency;?>&nbsp;
        </div>
      </td>
      <td width="" align="left" valign="top"><div class=maintext>
          <?php  echo $Projects->Description[$i];?>&nbsp;
        </div>
      </td>
      <td width="" align="left" valign="top"><div class=maintext>&nbsp;
          <?php  echo $LabName['Name'];?>&nbsp;
        </div>
      </td>
      <td width="" align="left" valign="top"><div class=maintext>&nbsp;
          <?php echo $Projects->Date[$i];?>&nbsp;
        </div>
      </td>
      <td width="" align="left"><div class=maintext>&nbsp; &nbsp;
  <?php if($AUTH->Delete) {
    $SQL = "SELECT UserID FROM ProPermission WHERE ProjectID=".$Projects->ID[$i];
    $UserIDArr2 = $mainDB->fetchAll($SQL);
      if(!$UserIDArr2){
  ?>
        <a href="javascript:confirm_delete(<?php echo $Projects->ID[$i];?>);">
        <img border="0" src="./images/icon_purge.gif" alt="Delete"></a>&nbsp;
  <?php 
      }else{
        echo "<img src=\"./images/icon_empty.gif\">";
      }
    }else{
      echo "<img src=\"./images/icon_empty.gif\">";
    }
    //if($AUTH->Modify) {
  ?>
        <a href="project.php?theaction=modify&Project_ID=<?php echo $Projects->ID[$i];?>">
        <img border="0" src="./images/icon_view.gif" alt="Detail"></a>&nbsp;</div>
  <?php /*}else{
      echo "<img src=\"./images/icon_empty.gif\">";
    }*/   
  ?>        
      </td>
    </tr>
  <?php 
  } //end for
  ?>    
  </form>
  </table>
<?php 
}else if($theaction == "addnew" OR $theaction == "insert" ){ 
  if(($theaction == "insert") and $frm_Name and $AUTH->Insert){
    $Projects->insert($frm_Name, $frm_TaxID, $frm_FilterSetID, $frm_DBname, $frm_Frequency, $frm_Description, $frm_LabID, $frm_Date);
    $Project_ID = $Projects->ID;
    $Desc = "Name=$frm_Name,TaxID=$frm_TaxID,FilterSetID=$frm_FilterSetID,DBname=$frm_DBname,Frequency=$frm_Frequency,LabID=$frm_LabID";    
    $AdminLog->insert($AccessUserID,'Projects',$Project_ID,'insert',$Desc);
    echo "<center><font color='green' face='helvetica,arial,futura' size=3>";
    echo "insert completed.";
    echo "</font></center>"; 
    $theaction = "modify";
  }else{
    if($theaction == "insert") {
      echo "<center><font color='red' face='helvetica,arial,futura' size=3>";
      echo "Missing info. <b>Bold</b> field names are required to make the insert.";
      echo "</font></center>";
    }
?>
    <form name=addnew_form method=post action=<?php echo $PHP_SELF;?>  enctype="multipart/form-data">
    <input type=hidden name=theaction value="insert">    
    <input type=hidden name=Project_ID value="<?php echo $Project_ID;?>">
    <br> 
  <table border="0" cellpadding="0" cellspacing="1" width="500">
    <tr bgcolor="<?php echo $bgcolordark;?>">
      <td colspan="2" align="center" height=20>
      <div class=tableheader>New Project</div>
      </td>
    </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right">
	    <div class=maintext>Project ID:&nbsp;</div>
	    </td>
	    <td><div class=maintext>&nbsp;&nbsp; <?php echo $Project_ID;?></div></td>
	  </tr>
	  <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap>
	      <div class=maintext><b>Project Name</b>:&nbsp;</div>
	    </td>
	    <td>&nbsp;&nbsp;<input type="text" name="frm_Name" size="24" value="<?php echo $frm_Name;?>"></td>
	  </tr>  
	  <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" valign=top nowrap>
	      <div class=maintext><b>Species</b>:&nbsp;</div>
	    </td>
		  <td>&nbsp;&nbsp;<select name="frm_TaxID">
        <option value="">--Choose a Genus Species--<br>
        <?php TaxID_list_($mainDB, $frm_TaxID);?>
		    </select>
	    </td>
	  </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
  	  <td align="right" valign=top nowrap>
  	    <div class=maintext><b>Filter Set</b>:&nbsp;</div>
  	  </td>
	  <td>&nbsp;
    <select name="frm_FilterSetID">
			<option value="">--Choose a Filter Set--
      <?php foreach ($FilterSetsArr as $Value){?>      
    	<option value="<?php echo $Value['ID']?>"<?php echo  ($frm_FilterSetID==$Value['ID'])?" selected":"";?>><?php echo $Value['Name']?>			
      <?php }?>      			
		</select>
		<a href='./filter.php?theaction=addnew&selectedSetID=0&section=1' class=button>[New Filter Set]</a>
    </td>    
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap>
	      <div class=maintext><b>Hits DB Name</b>:</div>
	    </td>
	    <td>&nbsp;
      <select name="frm_DBname">
        <option value="">--Choose a DB--
        <?php 
        foreach($HITS_DB as $key => $value){
          if($key != "proteins"){
        ?>
          <option value="<?php echo $key;?>" <?php echo ($frm_DBname==$key)?" selected":"";?>><?php echo $key;?>
        <?php 
          }
        }
        ?>
      </select>      
      </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap>
	      <div class=maintext>Frequency:&nbsp;</div>
	    </td>
	    <td>&nbsp;&nbsp;<input type="text" name="frm_Frequency" size="3" value="<?php echo $frm_Frequency;?>">&nbsp;%</td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" nowrap>
	    <div class=maintext><b>Description</b>:&nbsp;</div>
	  </td>
	  <td valign=top>&nbsp;&nbsp;<textarea name=frm_Description cols=50 rows=4><?php echo $frm_Description;?></textarea></td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" valign=top nowrap>
	    <div class=maintext><b>Lab Name:</b>:&nbsp;</div>
	  </td>
	  <td>&nbsp;
    <select name="frm_LabID">
			<option value="">--Choose a Lab Name--
      <?php foreach ($labArr as $Value){?>      
      			<option value="<?php echo $Value['ID']?>"<?php echo  ($frm_LabID==$Value['ID'])?" selected":"";?>><?php echo $Value['Name']?>			
      <?php }?>      			
		</select>
    &nbsp; &nbsp; [<A HREF="javascript: popwin('pop_lab.php', 400, 400);" class=button>Add Lab</A>]
    </td>
	</tr>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" valign=top>
	    <div class=maintext><b>Date</b>:&nbsp;</div>
	  </td>
	  <td>&nbsp;&nbsp;<input type="text" name="frm_Date" size="24" maxlength=15 value="<?php echo $frm_Date;?>">
      <a href="javascript: getToday(document.addnew_form.frm_Date);" class=button><font size=1>now</font></a>
    </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>" align="center">
    <td colspan="2"><input type="button" value="Save" onclick="javascript: checkform(document.addnew_form);"></td>
  </tr>
 </table>
 </form>
<?php 
  }//end of insert
}

if($theaction == "modify" OR $theaction == "update"){ 
  
  if($frm_Name  AND $theaction == "update" and $AUTH->Modify ){
    $Projects->update($Project_ID, $frm_Name, $frm_TaxID, $frm_FilterSetID, $frm_DBname, $frm_Frequency, $frm_Description, $frm_LabID, $frm_Date);
    $Desc = "Name=$frm_Name,TaxID=$frm_TaxID,FilterSetID=$frm_FilterSetID,DBname=$frm_DBname,Frequency=$frm_Frequency,LabID=$frm_LabID";    
    $AdminLog->insert($AccessUserID,'Projects',$Project_ID,'modify',$Desc);
    echo "<center><font color='green' face='helvetica,arial,futura' size=3>";
    echo "Update completed.";
    echo "</font></center>";  
  } else {
    if($theaction == "update"){
      echo "<center><font color='red' face='helvetica,arial,futura' size=3>";
      echo "Missing info. <b>Bold</b> field names are required to make the insert.";
      echo "</font></center><br>";
    }
  }
  if($theaction == "modify" and $Project_ID and !$frm_Name ){
    $Projects->fetch($Project_ID);
    $frm_Name = $Projects->Name;     
    $frm_TaxID = $Projects->TaxID;
    $frm_FilterSetID = $Projects->FilterSetID;
    $frm_DBname = $Projects->DBname;
    $frm_Frequency = $Projects->Frequency;
    $frm_Description = $Projects->Description;
    $frm_LabID = $Projects->LabID;
    $frm_Date = $Projects->Date;    
  }
?> <br>
 <table border="0" cellpadding="0" cellspacing="1" width="500">
    <form name=modify_form method=post action='<?php echo $PHP_SELF;?>' enctype="multipart/form-data">
    <input type=hidden name=theaction value=update>   
    <input type=hidden name=Project_ID value=<?php echo $Project_ID?>>   
      
   <tr bgcolor="<?php echo $bgcolordark;?>">
    <td colspan="2" align="center" height=20>
      <div class=tableheader height=18>Modify Project</div>
    </td>
  </tr>   
  <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right">
	    <div class=maintext>Project ID:&nbsp;</div>
	    </td>
	    <td><div class=maintext>&nbsp;&nbsp; <?php echo $Project_ID;?></div></td>
	  </tr>
	  <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap>
	      <div class=maintext><b>Project Name</b>:&nbsp;</div>
	    </td>
	    <td>&nbsp;&nbsp;<input type="text" name="frm_Name" size="24" value="<?php echo $frm_Name;?>"></td>
	  </tr>  
	  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" valign=top nowrap>
	    <div class=maintext><b>Species</b>:&nbsp;</div>
	  </td>
	  <td>&nbsp;&nbsp;<?php  $frm_TaxID = (!$frm_TaxID)? $AccessProjectTaxID : $frm_TaxID; ?><select name="frm_TaxID">
        <option value="">--Choose a Genus Species--<br>
        <?php TaxID_list_($mainDB, $frm_TaxID);?>
		    </select>
	  </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" valign=top nowrap>
	    <div class=maintext><b>Filter Set</b>:&nbsp;</div>
	  </td>
	  <td>&nbsp;
    <select name="frm_FilterSetID">
			<option value="">--Choose a Filter Set--
      <?php foreach ($FilterSetsArr as $Value){?>      
        <option value="<?php echo $Value['ID']?>"<?php echo  ($frm_FilterSetID==$Value['ID'])?" selected":"";?>><?php echo $Value['Name']?>			
      <?php }?>      			
		</select>
    </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap>
	      <div class=maintext><b>Hits DB Name</b>:</div>
	    </td>
	    <td height='20'>
      <input type=hidden name="frm_DBname" value="<?php echo $frm_DBname?>">   
      <div class=maintext><font size='2'>&nbsp;&nbsp;<?php echo $frm_DBname?></font>
       (names are in conf.inc.php file)
      </div> 
    </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap>
	      <div class=maintext>Frequency:&nbsp;</div>
	    </td>
	    <td>&nbsp;&nbsp;<input type="text" name="frm_Frequency" size="3" value="<?php echo $frm_Frequency;?>">&nbsp;%</td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" nowrap>
	    <div class=maintext><b>Description</b>:&nbsp;</div>
	  </td>
	  <td valign=top>&nbsp;&nbsp;<textarea name=frm_Description cols=50 rows=4><?php echo $frm_Description;?></textarea></td>
	</tr>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" valign=top nowrap>
	    <div class=maintext><b>Lab Name:</b>:&nbsp;</div>
	  </td>
	  <td>&nbsp;
    <select name="frm_LabID">
			<option value="">--Choose a Lab Name--
      <?php foreach ($labArr as $Value){?>      
        <option value="<?php echo $Value['ID']?>"<?php echo  ($frm_LabID==$Value['ID'])?" selected":"";?>><?php echo $Value['Name']?>			
      <?php }?>      			
		</select>
    &nbsp; &nbsp; [<A HREF="javascript: popwin('pop_lab.php', 400, 400);" class=button>Add Lab</A>]
    </td>
	</tr>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" valign=top>
	    <div class=maintext><b>Date</b>:&nbsp;</div>
	  </td>
	  <td>&nbsp;&nbsp;<input type="text" name="frm_Date" size="24" maxlength=15 value="<?php echo $frm_Date;?>">
      <a href="javascript: getToday(document.modify_form.frm_Date);" class=button><font size=1>now</font></a>
    </td>
	</tr> 
   <tr bgcolor="<?php echo $bgcolor;?>" align="center">
    <td colspan="2" valign=top>
		<?php if($AUTH->Modify){?>
             <input type="button" value="Modify" onClick="javascript: checkform(document.modify_form);" class=green_but> 
             <input type="button" value="Back"  onClick="javascript: go_back();" class=green_but>
		<?php }else{
        echo "&nbsp;&nbsp;";
      }  ?>   
    </td>
  </tr>
  </form>
  </table>
<?php 
} //end if
?>
    </td>
  </tr>
</table>
<?php 
include("./admin_footer.php");
?>