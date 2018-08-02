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

$selectedSetID = 0;
$selectedSetName = '';
$theaction = '';
$section = 0;
$setID = 0;
$nameID = 0;
$linkID = 0;
require_once("../common/site_permission.inc.php");
include("admin_log_class.php");
include("./admin_header.php");


$AdminLog = new AdminLog();

switch ($section) {
  case '1':
    if($theaction == "delete" && $setID AND $AUTH->Delete ){
      $SQL = "SELECT FilterSetID FROM Projects WHERE FilterSetID=$setID";       
      $setArr = $mainDB->fetch($SQL);
      if(!count($setArr)){
        $SQL = "DELETE FROM Filter WHERE FilterSetID=$setID";        
        $re = $mainDB->execute($SQL);
        if($re){
          $SQL = "DELETE FROM SetLink WHERE FilterSetID=$setID";        
          $re = $mainDB->execute($SQL);
          if($re){
            $re = $mainDB->delete("FilterSet", $setID);
            $Desc = "";
            $AdminLog->insert($AccessUserID,'FilterSet',$setID,'delete',$Desc);
          }
        }  
      }
    }else if($theaction == "update" && $frm_FilteSetName && $AUTH->Modify){        
      $SQL ="UPDATE FilterSet SET Name='$frm_FilteSetName' WHERE ID='$setID'";
      //echo $SQL;
      $re = $mainDB->update($SQL);
      $Desc = "Name=" . $frm_FilteSetName;
      $AdminLog->insert($AccessUserID,'FilterSet',$setID,'modify',$Desc);      
    }else if($theaction == "insert" && $frm_FilteSetName && $AUTH->Insert){
      $SQL ="INSERT INTO FilterSet SET Name='$frm_FilteSetName'"; 
      $selectedSetID = $mainDB->insert($SQL);
      echo $selectedSetID;
      $Desc = "Name=" . $frm_FilteSetName;
      $AdminLog->insert($AccessUserID,'FilterSet',$selectedSetID,'insert',$Desc);
    }          
    break;
  case '2':
    if(isset($frm_FilteDescription)){
      $frm_FilteDescription = mysqli_real_escape_string($AdminLog->link, $frm_FilteDescription);
    }   
    if($theaction == "delete" && $nameID AND $AUTH->Delete ){
      $SQL = "delete from Filter where FilterNameID=$nameID";
      $re = $mainDB->execute($SQL);
      if($re){         
        $re = $mainDB->delete("FilterName", $nameID);
        $Desc = "";
        $AdminLog->insert($AccessUserID,'FilterName',$nameID,'delete',$Desc);
      }
    }else if($theaction == "update" && $nameID && $AUTH->Modify){        
      $SQL ="UPDATE FilterName SET 
        Name='$frm_FilteName', 
        Alias='$frm_FilteAlias', 
        Color='$frm_FilteColor', 
        Type='$frm_FilteType', 
        Description='$frm_FilteDescription',
        KeyWord='$frm_KeyWord', 
        Init='$frm_FilteInit' 
        WHERE ID='$nameID'";
      //echo $SQL;
      $re = $mainDB->update($SQL);
      $Desc = "Name=$frm_FilteName,Alias=$frm_FilteAlias,Color=$frm_FilteColor,Type=$frm_FilteType,KeyWord=$frm_KeyWord,Init=$frm_FilteInit";
      $AdminLog->insert($AccessUserID,'FilterName',$nameID,'modify',$Desc);      
    }else if($theaction == "insert" && $frm_FilteName && $AUTH->Insert){
      $SQL ="INSERT INTO FilterName SET 
        Name='$frm_FilteName', 
        Alias='$frm_FilteAlias', 
        Color='$frm_FilteColor', 
        Type='$frm_FilteType', 
        Description='$frm_FilteDescription',
        KeyWord='$frm_KeyWord', 
        Init='$frm_FilteInit'";         
      $FilterNameID = $mainDB->insert($SQL);
      $Desc = "Name=$frm_FilteName,Alias=$frm_FilteAlias,Color=$frm_FilteColor,Type=$frm_FilteType,KeyWord=$frm_KeyWord,Init=$frm_FilteInit";
      $AdminLog->insert($AccessUserID,'FilterName',$FilterNameID,'insert',$Desc);
    }else if($theaction == "selected" && $nameID && $AUTH->Insert){    
      $SQL ="INSERT INTO Filter SET 
        FilterNameID='$nameID', 
        FilterSetID='$selectedSetID'"; 
      $mainDB->insert($SQL);
    }else if($theaction == "unSelected" && $nameID AND $AUTH->Delete ){
      $SQL = "delete from Filter where FilterNameID=$nameID and FilterSetID='$selectedSetID'";
      $re = $mainDB->execute($SQL);     
    }            
    break;
  case '3': 
    if($theaction == "delete" && $linkID AND $AUTH->Delete ){
      $SQL = "delete from SetLink where URLLinkID=$linkID";
      $re = $mainDB->execute($SQL);
      if($re){        
        $re = $mainDB->delete("WebLink", $linkID);
        $Desc = "";
        $AdminLog->insert($AccessUserID,'WebLink',$linkID,'delete',$Desc);
      }
    }else if($theaction == "update" && $linkID && $AUTH->Modify){        
      $SQL ="UPDATE WebLink SET 
        Name='$frm_LinkName', 
        URL='$frm_LinkURL', 
        Lable='$frm_LinkLable', 
        ProteinTag='$frm_LinkProteinTag' 
        WHERE ID='$linkID'";
      //echo $SQL;
      $re = $mainDB->update($SQL);
      $Desc = "Name=$frm_LinkName,URL=$frm_LinkURL,Lable=$frm_LinkLable,ProteinTag=$frm_LinkProteinTag";
      $AdminLog->insert($AccessUserID,'WebLink',$linkID,'modify',$Desc);
    }else if($theaction == "insert" && $frm_LinkURL && $AUTH->Insert){    
      $SQL ="INSERT INTO WebLink SET 
        Name='$frm_LinkName', 
        URL='$frm_LinkURL', 
        Lable='$frm_LinkLable', 
        ProteinTag='$frm_LinkProteinTag'"; 
      $WebLinkID = $mainDB->insert($SQL);
      $Desc = "Name=$frm_LinkName,URL=$frm_LinkURL,Lable=$frm_LinkLable,ProteinTag=$frm_LinkProteinTag";
      $AdminLog->insert($AccessUserID,'WebLink',$WebLinkID,'insert',$Desc);
    }else if($theaction == "selected" && $linkID && $AUTH->Insert){    
      $SQL ="INSERT INTO SetLink SET 
        URLLinkID='$linkID', 
        FilterSetID='$selectedSetID'"; 
      $mainDB->insert($SQL);
    }else if($theaction == "unSelected" && $linkID AND $AUTH->Delete ){
      $SQL = "delete from SetLink where URLLinkID=$linkID and FilterSetID='$selectedSetID'";
      $re = $mainDB->execute($SQL);
    }  
    break;
  default:
}

?>

<script language="javascript">
function confirm_delete(ID, selectedSetID, section){
  if(section == '1'){  
    if(confirm("Are you sure that you want to delete this Set?")){
      document.location = "filter.php?theaction=delete&setID=" + ID + "&selectedSetID=0&section=1";
    }
  }else if(section == '2'){
    if(confirm("Are you sure that you want to delete this Name?")){
      document.location = "filter.php?theaction=delete&nameID=" + ID + "&selectedSetID=" + selectedSetID + "&section=2";
    } 
  }else if(section == '3'){
    if(confirm("Are you sure that you want to delete this Link?")){
      document.location = "filter.php?theaction=delete&linkID=" + ID + "&selectedSetID=" + selectedSetID + "&section=3";
    } 
  }
}
function pop_testWindow(link_ID){   
  var file = 'pop_testURL.php?linkID=' + link_ID; 
  newNote = window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=750,height=200');
  newNote.moveTo(400,0);
}
function validate_filter(){
  var theForm = document.name_form;
  if(theForm.frm_FilteName.value == ''){
    alert("Please enter Filter Name.");
  }else if(theForm.frm_FilteAlias.value == ''){
    alert("Please enter Filter Alias.");
  }else if(theForm.frm_FilteType.value == ''){
    alert("Please select a Filter Type.");
  }else if(theForm.frm_FilteColor.value == ''){
    alert("Please enter a Filter Color.");
  }else{
    theForm.submit();
  }
}
function validate_set(){
  var theForm = document.set_form;
  if(theForm.frm_FilteSetName.value == ''){
    alert("Please enter Set Name.");
  }else{
    theForm.submit();
  }
}          
function validate_url_link(){
  var theForm = document.link_form;
  if(theForm.frm_LinkName.value == ''){
    alert("Please enter URL Link Name.");
  }else if(theForm.frm_LinkURL.value == ''){
    alert("Please enter URL.");
  }else if(theForm.frm_LinkLable.value == ''){
    alert("Please enter a Link Lable.");
  }else if(theForm.frm_LinkProteinTag.value == ''){
    alert("Please select a Link ProteinTag.");
  }else{
    theForm.submit();
  }
}                   
                   
</script>

<?php 
$SQL = "SELECT ID, Name FROM FilterSet ORDER BY ID";
$filterSetsArr=$mainDB->fetchAll($SQL);
//print_r($filterSetsArr);
?>
<br>
<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td align="left">
      &nbsp; <font color="#c5b781" face="helvetica,arial,futura" size="3"><b>Filter Management</b></font>   
    </td>    
    <td  align=right>
    <a href=create_bio_filter.php>[<b>Add Proteins to Bio Filters</b>]</a>
    </td>
  </tr>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>  
  <tr>
    <td align="middle" valign="top" colspan="2"><br>        
        <table border="0" cellpadding="0" cellspacing="1" width="800"> 
          <tr>
            <td colspan="2">
              <font color="" face="helvetica,arial,futura" size="4"><b>Filter Sets</b></font><br>
              <div class=maintext>
             Once data have been searched, it may be useful to specifically exclude certain classes of proteins from the results list.  This can be accomplished by creating one or more "Filters" to dynamically subtract potential contaminants. ProHits allows you to define two types of filters: Experimental filters and Bio Filters.  Experimental filters (see below) are based on search results and experimental technique.  Such filters include carry-over and spill-over, molecular weight exclusion, frequency of detection across the project, and experimentally-derived background contaminant list.  Bio Filters are aimed at excluding specific classes of proteins that may be contaminants, and are defined via text mining of the protein database (e.g. keratins, ribosomal proteins, cytoskeletal components, etc.).  ProHits allows for the creation of user-defined Bio Filters for each "<b>Project</b>" (see Project Manager). Different filter sets may be created for different projects, and multiple projects may share the same filter set. Note that only Bio Filters may be created from this page. Scripts are required to create new Experimental Filters, with the exception of the user-defined Background list that is managed from the Analyst Bait Report page.  
              </div>
            </td>
            <td align="right" colspan="1" valign="bottom">
<?php if($AUTH->Insert) {?>
              <a href="filter.php?theaction=addnew&selectedSetID=0&section=1" class=button>[Add New]</a>&nbsp;
<?php }?>              
            </td>
          </tr>  
          <tr bgcolor="">
            <td width="10%" height="25" bgcolor="#c5b781" align=center><div class=tableheader>Set ID</td>
            <td bgcolor="#c5b781" align=center onwrap><div class=tableheader>Set Name</td>                            
            <td width="20%" bgcolor="#c5b781" align="center"><div class=tableheader>Options</td>    
          </tr>
              <form name=set_form method=post action=filter.php >
              
<?php 
$setBgcolor = '';
$setSRC = '';
foreach ($filterSetsArr as $setValue) {
  $setBgcolor = "#e9e1c9";
  $setSRC = "./images/gray_yellow6.gif";
  if($selectedSetID == $setValue['ID']){
    $selectedSetName = $setValue['Name'];
    $setBgcolor = "#87cefa";
    $setSRC = "./images/icon_checked.gif";  
  }
  if($theaction == "modify" && $AUTH->Modify && $setValue['ID'] == $setID && $section == 1){
    $theaction = "update";
?>
          <tr bgcolor="<?php echo $setBgcolor?>">
            <td width="" align="left"><div class=maintext>&nbsp;<?php echo $setValue['ID']?>&nbsp;</div></td>
            <td width="" align="left"><div class=maintext>
              <input type="text" name="frm_FilteSetName" size="24" value="<?php echo $setValue['Name']?>"></div>                                                           
            </td> 
            <td width="" align="center"><div class=maintext>&nbsp; &nbsp;
              <input type="button" name="frm_Name" size="24" value="Modify" onclick="validate_set();">
            </td>  
          </tr>
<?php 
  }else{
?>    
          <tr bgcolor="<?php echo $setBgcolor?>">
            <td width="" align="left"><div class=maintext>&nbsp;<?php echo $setValue['ID']?>&nbsp;</div></td>
            <td width="" align="left">
              <a href="filter.php?selectedSetID=<?php echo $setValue['ID']?>"><div class=maintext>&nbsp;<?php echo $setValue['Name']?>&nbsp;</div></a>
            </td>            
            <td width="" align="left"><div class=maintext>&nbsp; &nbsp;
<?php 
    if($AUTH->Delete){
      $SQL = "SELECT FilterSetID FROM Projects WHERE FilterSetID=".$setValue['ID'];       
      $setArr = $mainDB->fetch($SQL);
      if(!count($setArr)){
?>                
              <a href="javascript:confirm_delete('<?php echo $setValue['ID']?>','0', '1');">
                <img border="0" src="./images/icon_purge.gif" alt="Delete"></a>&nbsp;
<?php 
      }else{
        echo "<img src=\"./images/icon_empty.gif\">";
      }
    }else{
      echo "<img src=\"./images/icon_empty.gif\">";
    }
    if($AUTH->Modify){
?>               
              <a href="filter.php?theaction=modify&setID=<?php echo $setValue['ID']?>&selectedSetID=0&section=1">
                <img border="0" src="./images/icon_view.gif" alt="Detail"></a>&nbsp;</div>
<?php 
    }else{
      echo "<img src=\"./images/icon_empty.gif\">";
    }   
?>                
            </td>
          </tr>   
<?php 
  }
}//-end foreach-----

if($theaction == "addnew" && $AUTH->Insert && $section == 1){   
  $theaction = "insert";
?>      
          <tr bgcolor="#e9e1c9">
            <td align='left'><div class=maintext>&nbsp;&nbsp;</div></td>
            <td width="" align="left" colspan="1">
              <input type="text" name="frm_FilteSetName" size="35" value="">
            </td>  
            <td width="" align="left" colspan="1">  
              <input type="button" name="frm_Name"  value="Add New" onclick="validate_set();">
            </td>              
          </tr>
<?php 
}
?>
          <input type="hidden" name="theaction" value="<?php echo $theaction?>">              
          <input type="hidden" name="section" value="1">
          <input type='hidden' name='setID' value='<?php echo $setID?>'>
          </form>              
        </table>       
      </td>
  </tr>
</table>
  
<?php 
if($selectedSetID){

  $SQL = "SELECT ID, Name, Alias, Color, Type, Description, KeyWord, Init FROM FilterName ORDER BY ID";
  $setsNamesArr=$mainDB->fetchAll($SQL);
  $SQL = "SELECT FilterNameID FROM Filter WHERE FilterSetID=$selectedSetID";
  $setNamesArr=$mainDB->fetchAll($SQL);
  $setNamesArrOne = array();
  foreach ($setNamesArr as $Value) {
    array_push($setNamesArrOne, $Value['FilterNameID']);
  }
?>
<br>
<table border="0" cellpadding="0" cellspacing="0" width="100%">  
  <tr>
    <td align="middle" valign="top" colspan="1">        
        <table border="0" cellpadding="0" cellspacing="1" width="800">
          <tr height="1" >
            <td bgcolor="#4d4d4d" height="0" colspan="10"><img src="./images/pixel.gif" width="1" height="1" border="0"></td>  
          </tr>
          <tr>
            <td colspan="7">
              <font color="" face="helvetica,arial,futura" size="4"><b>Filters:</b></font><br>
              <div class=maintext>
Bio Filters are based on a text search with selected keywords followed by a manual validation of the exclusion list. Additional Bio Filters can be created from this page.
A 'KeyWord' is used to search user-defined keywords in both the GO annotation and gene description pages in the ProHits Protein DB.  Keywords should be separated by ";".  After the keyword search has been performed, the user can manage the list by selecting the link [Add Proteins to Bio Filters] on the top of the page, and manually selecting those proteins to be added to filter set.  These entries will be displayed in grey.
<br><br>
Select desired filters for a given Filter set by clicking the appropriate "<b>Selected</b>" box. Selected filters are highlighted in grey after selection, and can be deselected if desired.
</div>
            </td>
            <td align="right" colspan="3" valign=bottom>
              <?php if($AUTH->Insert) {?>
              <a href="filter.php?theaction=addnew&selectedSetID=<?php echo $selectedSetID?>&section=2" class=button>[Add New]</a>&nbsp;
              <?php }?>              
            </td>
          </tr>  
          <tr bgcolor="">
            <td width="5%" height="25" bgcolor="#c5b781" align=center><div class=tableheader>ID</td>
            <td width="10%" bgcolor="#c5b781" align=center onwrap><div class=tableheader>Name</td>
            <td width="5%" bgcolor="#c5b781" align=center onwrap><div class=tableheader>Alias</td>
            <td width="25%" bgcolor="#c5b781" align="center"><div class=tableheader>Description</td>
            <td width="20%" bgcolor="#c5b781" align="center"><div class=tableheader>KeyWord</td>
            <td width="5%" bgcolor="#c5b781" align="center"><div class=tableheader>Init</td>
            <td width="5%" bgcolor="#c5b781" align="center"><div class=tableheader>Type</td>
            <td width="5%" bgcolor="#c5b781" align="center"><div class=tableheader>Color</td>
            <td width="5%" bgcolor="#c5b781" align="center"><div class=tableheader>Selected</td>                
            <td width="10%" bgcolor="#c5b781" align="center"><div class=tableheader>Modify filter</td>    
          </tr>
              <form name=name_form method=post action=filter.php >
              
<?php 

  foreach ($setsNamesArr as $nameValue) {
    $setBgcolor = "#e9e1c9";
    $setSRC = "./images/gray_yellow6.gif";
    $isSelected = "selected"; 
    $nameValue['Description'] = str_replace ("\r\n", "<br><br>", $nameValue['Description']);
    //  echo $nameValue['Description']; exit;  
    if(in_array($nameValue['ID'], $setNamesArrOne)){
      $setBgcolor = "#a7a7a7";
      $setSRC = "./images/icon_checked.gif";
      $isSelected = "unSelected";  
    }  
    if($theaction == "modify" && $AUTH->Modify && $nameValue['ID'] == $nameID && $section == 2){
      $theaction = "update";
?>
          <tr bgcolor="<?php echo $setBgcolor?>">
            <td width="" align="left"><div class=maintext>&nbsp;<?php echo $nameValue['ID']?>&nbsp;</div></td>
            <td width="" align="left"><div class=maintext>
              <input type="text" name="frm_FilteName" size="10" value="<?php echo $nameValue['Name']?>"></div>
            </td>
            <td width="" align="left"><div class=maintext>
              <input type="text" name="frm_FilteAlias" size="1" value="<?php echo $nameValue['Alias']?>"></div>
            </td>
            <td width="" align="left"><div class=maintext>              
              <textarea name="frm_FilteDescription" rows="10" cols="22"><?php echo $nameValue['Description']?></textarea></div>
            </td>
            <td width="" align="left"><div class=maintext>              
              <textarea name="frm_KeyWord" rows="10" cols="14"><?php echo $nameValue['KeyWord']?></textarea></div>
            </td>
            <td width="" align="left"><div class=maintext>
              <input type="text" name="frm_FilteInit" size="1" value="<?php echo $nameValue['Init']?>"></div>
            </td>
            <td width="" align="left"><div class=maintext>
              <select name="frm_FilteType">
                <option value=''>             
	  		        <option value='Bio'<?php echo ($nameValue['Type']=='Bio')?" selected":"";?>>Bio
			          <option value='Exp'<?php echo ($nameValue['Type']=='Exp')?" selected":"";?>>Exp
                <option value='Fre'<?php echo ($nameValue['Type']=='Fre')?" selected":"";?>>Fre
                <option value='Non'<?php echo ($nameValue['Type']=='Non')?" selected":"";?>>Non
			        </select>             
              </div>
            </td>
            <td width="" align="left"><div class=maintext>
              <input type="text" name="frm_FilteColor" size="3" value="<?php echo $nameValue['Color']?>"></div>
            </td>
            <td width="" align="left"><div class=maintext>&nbsp; &nbsp;                                               
            </td> 
            <td width="" align="left"><div class=maintext>&nbsp; &nbsp;
              <input type="button" name="frm_Name" value="Modify"  onclick="validate_filter()">
            </td>  
          </tr>
 <?php 
    }else{ 
      $keyWordStr = str_replace(";", "; ", $nameValue['KeyWord']); 
 ?>    
          <tr bgcolor="<?php echo $setBgcolor?>">
            <td width="" align="left"><div class=maintext>&nbsp;<?php echo $nameValue['ID']?>&nbsp;</div></td>
            <td width="" align="left"><div class=maintext>&nbsp;<?php echo $nameValue['Name']?>&nbsp;</div></td>
            <td width="" align="left"><div class=maintext>&nbsp;<?php echo $nameValue['Alias']?>&nbsp;</div></td>
            <td width="" align="left"><div class=maintext>&nbsp;<?php echo $nameValue['Description']?>&nbsp;</div></td>
            <td width="" align="left"><div class=maintext>&nbsp;<?php echo $keyWordStr?>&nbsp;</div></td>
            <td width="" align="left"><div class=maintext>&nbsp;<?php echo $nameValue['Init']?>&nbsp;</div></td>
            <td width="" align="left"><div class=maintext>&nbsp;<?php echo $nameValue['Type']?>&nbsp;</div></td>
            <td width="" align="left" bgcolor="<?php echo $nameValue['Color']?>"><div class=maintext>&nbsp;&nbsp;</div></td>
            <td width="" align="center"><div class=maintext>&nbsp;
              <a href="filter.php?theaction=<?php echo $isSelected?>&selectedSetID=<?php echo $selectedSetID?>&nameID=<?php echo $nameValue['ID']?>&section=2">          
                <img border="0" src="<?php echo $setSRC?>"></a>&nbsp;                                 
            </td> 
            <td width="" align="left"><div class=maintext>&nbsp; &nbsp;
            <?php if($AUTH->Delete and $nameValue['Type']!='Exp' and $nameValue['Type']!='Fre'){?>
              <a href="javascript:confirm_delete('<?php echo $nameValue['ID']?>','<?php echo $selectedSetID?>', '2');">
                <img border="0" src="./images/icon_purge.gif" alt="Delete"></a>&nbsp;
            <?php }else{
                echo "\n<img src=\"images/icon_empty.gif\">&nbsp;";
              }
              if($AUTH->Modify and $nameValue['Type']!='Exp' and $nameValue['Type']!='Fre'){
            ?>    
              <a href="filter.php?theaction=modify&selectedSetID=<?php echo $selectedSetID?>&nameID=<?php echo $nameValue['ID']?>&section=2">
                <img border="0" src="./images/icon_view.gif" alt="Modify"></a>&nbsp;</div>
            <?php }else{
                echo "\n<img src=\"images/icon_empty.gif\">&nbsp;";
              }
            ?>    
            </td>
          </tr>   
<?php 
    }
  }//-end foreach-----

  if($theaction == "addnew" && $AUTH->Insert && $section == 2){   
    $theaction = "insert";
?>      
          <tr bgcolor="#e9e1c9">
            <td align='left'><div class=maintext>&nbsp;&nbsp;</div></td>
            <td width="" align="left"><div class=maintext>
              <input type="text" name="frm_FilteName" size="10" value=""></div>
            </td>
            <td width="" align="left"><div class=maintext>
              <input type="text" name="frm_FilteAlias" size="1" value=""></div>
            </td>
            <td width="" align="left"><div class=maintext>
              <textarea name="frm_FilteDescription" rows="10" cols="22"></textarea></div>
            </td>
            <td width="" align="left"><div class=maintext>
              <textarea name="frm_KeyWord" rows="10" cols="14"></textarea></div>
            </td>
            <td width="" align="left"><div class=maintext>
              <input type="text" name="frm_FilteInit" size="1" value=''></div>
            </td>
            <td width="" align="left"><div class=maintext>
              <select name="frm_FilteType">
                <option value=''>
	  		        <option value='Bio'>Bio
			          <option value='Exp'>Exp
                <option value='Fre'>Fre
                <option value='Non'>Non
			        </select>               
            </td>
            <td width="" align="left"><div class=maintext>
              <input type="text" name="frm_FilteColor" size="3" value=""></div>
            </td>
            <td width="" align="left"><div class=maintext>&nbsp; &nbsp;                                               
            </td>           
            <td width="" align="left"><div class=maintext>
              <input type="button" name="frm_Name"  value="Add New"  onclick="validate_filter()">
            </td>               
          </tr>
          
<?php 
  }
?>
          <input type="hidden" name="selectedSetID" value="<?php echo $selectedSetID?>">
          <input type="hidden" name="theaction" value="<?php echo $theaction?>">              
          <input type="hidden" name="section" value="2">
          <input type='hidden' name='nameID' value='<?php echo $nameID?>'>
          </form>              
        </table>
        <br>
      </td>
  </tr>
</table> 
<?php 
}
?>  

<br>
<?php 
include("./admin_footer.php");
?>