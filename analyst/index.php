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

$frm_Projects = '';
$project_Name = '';
$proj_changed = 0;
$frm_show = '';
require("../common/site_permission.inc.php");
require("analyst/site_header.php");
?>
<SCRIPT language=JavaScript>
<!--
function Is_ProjectSelected(theForm){  
  selObj = theForm.frm_Projects;
  for (i=0; i < selObj.options.length; i++) {
      if (selObj.options[i].selected){ 
         return true;
      }
  }
  alert("Please select a project.");
  return false; 
}

function projectSeleced(theForm){
  theForm.frm_show.value = "Y";
  theForm.submit();
}
-->
</SCRIPT>
<br>
<?php 
if(!$proj_changed){
  if($frm_Projects and !$frm_show){
    if(!isset($_SESSION["workingProjectID"]) || $_SESSION["workingProjectID"] != $frm_Projects){
      $current_Project = new Projects($frm_Projects);  
      $_SESSION["workingProjectID"] = $current_Project->ID;
      $_SESSION["workingProjectName"] = $current_Project->Name;
      $_SESSION["workingProjectTaxID"] = $current_Project->TaxID;
      $_SESSION["workingFilterSetID"] = $current_Project->FilterSetID;      
      $_SESSION["workingDBname"] = $HITS_DB[$current_Project->DBname];      
      //$HITSDB->change_db($HITS_DB[$current_Project->DBname]);
      if($current_Project->Frequency){
        $_SESSION["workingProjectFrequency"] = $current_Project->Frequency;
      }else{
        $_SESSION["workingProjectFrequency"] = 0;
      }
      $SQL = "SELECT ID FROM User WHERE Type='MSTech' OR (Type!='user' AND LabID='".$current_Project->LabID."') ORDER BY Type";
      $UserArr = $mainDB->fetchAll($SQL);
      $superUsersArr = array();
      for($i=0; $i<count($UserArr); $i++){
        array_push($superUsersArr, $UserArr[$i]['ID']);
      }
      if(in_array($AccessUserID, $superUsersArr)){
        $_SESSION["superUsers"] = 1;
      }else{
        $_SESSION["superUsers"] = 0;
      }  
    }  
    echo "<script language=javascript>document.location.href='./index.php?proj_changed=1';</script>"; 
    exit;   
  }else{   
  ?>
<table border="0" cellpadding="0" cellspacing="0" width="95%">
  <tr>
    <td align="left">
		&nbsp; <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="3"><b>Select Project
		</b>    
		</font> 
	</td>
    <td align="right">
      &nbsp;
    </td>
  </tr>
  <tr>
  	<td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="center" colspan=2><br>
      
      <form name="select_user" method=post action=<?php echo $_SERVER['PHP_SELF'];?>>
      <input type=hidden name=theaction value="selected">      
	    <input type=hidden name=frm_show value="">      
      <table border="0" cellpadding="0" cellspacing="1" width="650">
      
       <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
		      <td colspan="2" align="center" height=25>
		      <font color="white" face="helvetica,arial,futura" size="3"><b>High-throughput Projects</b></font>
		      </td>
	     </tr>
		   <tr>          
	        <td bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top align=center width=50% nowrap>&nbsp;<br>&nbsp; &nbsp; 
          <?php 
          $Projects = new Projects(); 
          $Projects->getAccessProjects($_SESSION["USER"]->ID);
          if($frm_show){
            $AccessProjectID = $frm_Projects;
          }
          ?> 
				 <select name="frm_Projects" id="mySelect" size=25 onChange="projectSeleced(this.form)">                 
				 	<?php 
          if($Projects->count == 1){
            echo  "<option  value='".$Projects->ID[0]."'";
            echo " selected";   
            echo ">".$Projects->Name[0]."\n";
          }else if($Projects->count){
            for ($i= 0;$i < $Projects->count; $i++) {            
						  echo  "<option  value='".$Projects->ID[$i]."'";
              echo ($Projects->ID[$i]==$AccessProjectID)?" selected":"";   
              echo ">"."(".$Projects->ID[$i].") ".$Projects->Name[$i]."\n";
            }
          }   
					?>
				 </select>&nbsp;&nbsp;<br>&nbsp; 
         <input type=hidden name="project_Name" value="">        
	        </td>          
          <td bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top align=center>
            <table border="0" cellpadding="0" cellspacing="1" width=80% >
              <tr>
                <td valign=top align=center><div class=maintext><br>
                  Select the project to analyze. 
                  </div>
                </td>
              </tr>  
              <tr valign=bottom>                            
                <td valign=bottom><br>
                  &nbsp; &nbsp;<input type="submit" value="Select" Onclick="return Is_ProjectSelected(this.form);" class=green_but>&nbsp; &nbsp;
                  <input type="reset" value="Reset" class=green_but>
                  <div class=maintext>
                  <br><br>
                  <?php                                 
                  if($AccessProjectID){
                    $db = new mysqlDB();
                    $SQL = "select P.ID, 
                                   P.Name, 
                                   P.TaxID, 
                                   P.Description, 
                                   L.Name as LabName, 
                                   P.Frequency,
                                   A.Insert,
                                   A.Modify,
                                   A.Delete 
                                   from Projects P, Lab L, ProPermission A where P.LabID=L.ID and A.ProjectID=P.ID and A.UserID=$AccessUserID
                                   and P.ID='$AccessProjectID'";
                    $record = $db->fetch($SQL);                    
                    if(count($record) > 0){
                      echo "&nbsp;Lab Name: <b>". $record['LabName']."</b><br>";
                      echo "&nbsp;Frequency filter : <b>". $record['Frequency']."%</b><br>";
                      echo "&nbsp;Description : <b>". $record['Description']."</b><br>";
                      echo "&nbsp;Species: ";
                      $SQL = "select Name from ProteinSpecies where TaxID='". $record['TaxID'] . "'";
                      $recordTaxID = $db->fetch($SQL);
                      if(count($recordTaxID) > 0){
                        echo  "<b>" . $recordTaxID['Name'] . "</b>";
                      }
                      $Space = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                                                   
                      echo "<br>&nbsp;Permissions: ";
                      echo "<b>Read</b><br>".$Space;
                      if($record['Insert']){
                        echo "<b>Insert</b><br>".$Space;
                      }
                      if($record['Modify']){
                        echo "<b>Modify</b><br>".$Space;
                      }
                      if($record['Delete']){
                        echo "<b>Delete</b><br>";
                      }
                    }
                  }
                  ?>
                </div>
                </td>
              </tr>
            </table>
          </td>
        </tr>	        
      </table>
  </form>
  </td>
  </tr>
</table>
<?php 
  }
}else{
  $_SESSION['com_BaitIDs'] = ''; 
  $_SESSION['com_SampleIDs'] = ''; 
  $_SESSION['com_ExperimentIDs'] = '';
?>
<table border="0" cellpadding="0" cellspacing="0" width="95%">
  <tr>
    <td align="left">
        &nbsp; <font color="<?php echo $bg_tb_header;?>" face="helvetica,arial,futura" size="3"><b>Database Information
        <?php 
        if($AccessProjectName){
          echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font>";
        }
        ?>
        </b>
        </font>
    </td>
  </tr>
  <tr>
        <td height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="" colspan=1 >
      <br><div class=large>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
      <b>Database structure</b></div>
      <table border=0 width=450 align=center>
        <tr>
        <td>
        <IMG SRC="./images/data_structure.png" >
        </td>
        </tr>
        <tr>
	        <td><div class=maintext><b>Icons</b></div></td>
        </tr>
        <tr>
          <td><div class=maintext>
              Modify -- Click <img src='./images/icon_view.gif'>  to modify a record.<br>
              Delete -- Click <img src='./images/icon_purge.gif'>  to delete a record. You can only delete your own records.<br>
              Next Level -- Click <img src='./images/icon_tree.gif'> to go to next level of a record.<br>
              Picture -- Click <img src='./images/icon_picture.gif'> to have a pop-up window to show up and  display a gel image.<br>
              Plate -- Click <img src='./images/icon_plate.gif'> to view the current plate.<br>
              Next -- Click <img src='./images/arrow_small.gif'> to go to next step when submitting samples.<br>
        		  Co-IP -- Co-IP results: Yes <img src='./images/icon_coip_green.gif'> 
        		  No<img src='./images/icon_coip_red.gif'> 
        		  Possible<img src='./images/icon_coip_yellow.gif'>
              In Progress<img src='./images/icon_coip_blue.gif'>
              </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php 
}
require("site_footer.php");
?>
