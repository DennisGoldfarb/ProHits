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

$frm_Project = '';
$frm_Page = '';
$frm_User = '';
$frm_allUsers = '';
$theaction ='';
$order_by = 'ID desc';
$start_point = 0;
$UserIDstr = '';

define ("RESULTS_PER_PAGE", 200);
define ("MAX_PAGES", 15); //this is max page link to display

require("../common/site_permission.inc.php");
include("admin_log_class.php");
include("common_functions.inc.php");
require("../common/page_counter_class.php");
include("./admin_header.php");

if(isset($first)){
  $AdminLog = new AdminLog();
  $Desc = "";       
  $AdminLog->insert($AccessUserID,'Log','','browser',$Desc);
}  
?>

<SCRIPT language=JavaScript>
<!--
function Is_ProjectSelected(theForm){  
  selObj = theForm.frm_Project;
  for (i=0; i < selObj.options.length; i++) {
      if (selObj.options[i].selected){ 
         return true;
      }
  }
  alert("Please select a project.");
  return false; 
}
function item_selected(theForm){
  theForm.theaction.value = "select";     
  theForm.submit();   
}

function check_form(theForm){
  if(!theForm.frm_Project.value){
    alert("Please select a project");
  }else if(!theForm.frm_Page.value){
    alert("Please select a page or all pages");
  }else if(theForm.frm_allUsers.checked == false && !theForm.frm_User.value){
    alert("Please select users");
  }else{
    if(theForm.frm_allUsers.checked == false){
      theForm.UserIDstr.value = ''
      for(var i=0; i<theForm.frm_User.length; i++){
        if(theForm.frm_User[i].selected == true){
          if(theForm.UserIDstr.value){
            theForm.UserIDstr.value += "','";
          }else{
            theForm.UserIDstr.value += "'";
          }
          theForm.UserIDstr.value += theForm.frm_User[i].value;
        }  
      }
      if(theForm.UserIDstr.value){
        theForm.UserIDstr.value += "'";
      }
    }
    theForm.theaction.value = "view_report";
    theForm.submit();
  }
}
function sortList(order_by){
  var theForm = document.log_report;
  theForm.order_by.value = order_by;
  theForm.theaction.value = "view_report";
  theForm.submit();
}

function back_select(){
  var theForm = document.log_report; 
  theForm.theaction.value = "select";
  theForm.submit();
}
function is_allUsers(){
  var theForm = document.log_report;
  if(!theForm.frm_Page.value){
    alert("Please select a page or all pages");
    if(theForm.frm_allUsers.checked == false){
      theForm.frm_allUsers.checked = true;
    }else{
      theForm.frm_allUsers.checked = false;
    }
  }else{  
    theForm.theaction.value = "select";
    theForm.submit();
  }  
}
function pop_one_record(RecordID,MyTable){
  var theForm = document.log_report;  
  file = 'log_report_pop_one.php?RecordID=' + RecordID + '&MyTable=' + MyTable + '&UserIDstr=' + theForm.UserIDstr.value +'&Project=' + theForm.frm_Project.value;
  newNote = window.open(file,"Record",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=940,height=500');
  newNote.moveTo(40,0);
}
-->
</SCRIPT>
<br>

<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td align="left">
		&nbsp; <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="3"><b>Log Report
		</b>    
		</font> 
	</td>
    <td align="right">
<?php 
  if($theaction != "select"){
?>
      <a href="javascript: back_select();">[Back]</a>
<?php }?>      
      &nbsp;
    </td>
  </tr>
  <tr>
  	<td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="center" colspan=2><br>
      
      <form name="log_report" method=post action=<?php echo $_SERVER['PHP_SELF'];?>>
      <input type=hidden name=theaction value="">
<?php 
if($theaction == "view_report" || !$theaction){
  $UserNameArr = get_users_ID_Name($mainDB);
  $ProjectNameArr = get_project_ID_Name($mainDB);
  $oldDBName = change_DB($mainDB, $tmpDBname);
  if($frm_Project)
  $SQL = "SELECT COUNT(ID) FROM Log 
          WHERE ProjectID='$frm_Project' AND UserID IN ($UserIDstr)";
  if($frm_Page != "allPages"){
    $SQL .= " AND MyTable='$frm_Page'";
  }
  //echo $SQL; exit;         
  $row = mysqli_fetch_row(mysqli_query($mainDB->link, $SQL));        
  $total_records = $row[0]; 
  
  $PAGE_COUNTER = new PageCounter();
  $query_string = "tmpDBname=$tmpDBname&frm_Project=$frm_Project&frm_Page=$frm_Page&UserIDstr=$UserIDstr";
  $caption = "Log Report";
  if($order_by){ 
    $query_string .= "&order_by=".$order_by;
  }
  $page_output = $PAGE_COUNTER->page_links($start_point, $total_records, RESULTS_PER_PAGE, MAX_PAGES, str_replace(' ','%20',$query_string)); 
  
  $SQL = "SELECT
         UserID, 
         MyTable, 
         RecordID,
         MyAction,
         Description,
         TS FROM Log
         WHERE ProjectID='$frm_Project' AND UserID IN($UserIDstr)";
  if($frm_Page != "allPages"){
    $SQL .= " AND MyTable='$frm_Page'";
  }
  if($order_by){
    $SQL .= " ORDER BY $order_by ";
  }   
  if(RESULTS_PER_PAGE){
    $SQL .= " LIMIT $start_point, ".RESULTS_PER_PAGE;
  }
  $result = mysqli_query($mainDB->link, $SQL);
?> 
  <input type='hidden' name='tmpDBname' value="<?php echo $tmpDBname?>">
  <input type='hidden' name='frm_Project' value="<?php echo $frm_Project?>">
  <input type='hidden' name='frm_Page' value="<?php echo $frm_Page?>">
  <input type='hidden' name='UserIDstr' value="<?php echo $UserIDstr?>">
  <input type='hidden' name='frm_allUsers' value="Y">  
  <input type='hidden' name='order_by' value=''> 
  <table border="0" cellpadding="0" cellspacing="1" width="900">
  <tr>
    <td colspan=9 height=25>
      <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="3"><b>Project:</b></font>&nbsp;
      <font color="red" face="helvetica,arial,futura" size="3"><b>
        <?php echo (isset($ProjectNameArr[$frm_Project]))?$ProjectNameArr[$frm_Project]:"";?></b></font>&nbsp;&nbsp;&nbsp;&nbsp;      
    </td>
  </tr>
  <tr><td colspan=10 align=right height=25><?php echo $page_output;?></td></tr>
  <tr bgcolor="">
    <td width="15%" height="25" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center><div class=tableheader>
    Owner</div>
    </td>    
    <td width="10%" height="25" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center><div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "MyTable")? 'MyTable%20desc':'MyTable';?>');">
    Table</a>
    <?php if($order_by == "MyTable") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "MyTable desc") echo "<img src='images/icon_order_down.gif'>";
    ?></div>
    </td>
    <td width="6%" height="25" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center><div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "RecordID")? 'RecordID%20desc':'RecordID';?>');">
    Record ID</a>
    <?php if($order_by == "RecordID") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "RecordID desc") echo "<img src='images/icon_order_down.gif'>";
    ?></div>  
    </div>
    </td>
    <td width="6%" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center> <div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "MyAction")? 'MyAction%20desc':'MyAction';?>');">
      Action</a>
    <?php if($order_by == "MyAction") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "MyAction desc") echo "<img src='images/icon_order_down.gif'>";
    ?></div>
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center> <div class=tableheader>
      Description</a>
    </div>
    </td>
    <td width="13%" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center><div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "TS")? 'TS%20desc':'TS';?>');">
    Time </a>
    <?php if($order_by == "TS") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "TS desc") echo "<img src='images/icon_order_down.gif'>";
    ?></div>
    </td>   
  </tr>
<?php 
  while($row = mysqli_fetch_assoc($result)){
    
?>    
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
    <td width="" align="left" height="20"><div class=maintext>&nbsp; 
        <?php echo (isset($UserNameArr[$row['UserID']]))?$UserNameArr[$row['UserID']]:"";?>&nbsp;
      </div>
    </td>
    <td width="" align="left" height="20"><div class=maintext>&nbsp; 
        <?php echo $row['MyTable'];?>&nbsp;
      </div>
    </td>
    <td width="" align="left" ><div class=maintext>&nbsp; 
        <a href="javascript: pop_one_record('<?php echo $row['RecordID'];?>','<?php echo $row['MyTable'];?>');"><?php echo  $row['RecordID'];?>&nbsp;</a>
      </div>
    </td> 
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php echo $row['MyAction'];?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php echo $row['Description'];?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php echo $row['TS'];?>&nbsp;
      </div>
    </td>    
  </tr>
  <?php 
  } //end foreach
  ?>
  </table> 
<?php  
}else if($theaction == "select"){
?>       
      <table border="0" cellpadding="0" cellspacing="1" width="750">
        <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
		      <td colspan="3" align="center" height=25>
		      <font color="white" face="helvetica,arial,futura" size="3"><b>Log Report</b></font>
		      </td>
	      </tr>
		    <tr>          
	        <td bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top align=center width=33% nowrap>&nbsp;
          <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="2"><b>Projects</b></font><br>&nbsp; &nbsp;         
            <?php 
            $SQL = "SELECT P.ID, P.Name, P.DBname, P.LabID 
                    FROM Projects P, ProPermission M 
                    where P.ID=M.ProjectID and M.UserID='".$_SESSION["USER"]->ID."' order by P.Name";
            $Projects = $mainDB->fetchAll($SQL);
            
            ?>
  				  <select name="frm_Project" size=15 onChange="item_selected(this.form)">                 
  				 	<?php 
            for ($i= 0;$i < count($Projects); $i++){            
						  echo  "<option  value='".$Projects[$i]['ID']."'";
              echo ($Projects[$i]['ID']==$frm_Project)?" selected":"";   
              echo ">".$Projects[$i]['Name']."\n";
            }
  					?>
  				  </select>&nbsp;&nbsp;<br>&nbsp;                   
	        </td>
          <td bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top align=center width=33% nowrap>&nbsp;
            <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="2"><b>Pages</b></font><br>&nbsp; &nbsp; 
            <?php 
            $oldDBName = '';            
            $PageNameArr2 = array();
            if($frm_Project){
              $SQL = "SELECT DBname
                      FROM Projects 
                      WHERE ID=$frm_Project";
              $DBnameArr = $mainDB->fetch($SQL);
              if($DBnameArr && $DBnameArr['DBname']){
                $oldDBName = change_DB($mainDB, $HITS_DB[$DBnameArr['DBname']]);                
                $SQL = "SELECT MyTable 
                      FROM Log WHERE ProjectID=$frm_Project
                      GROUP BY MyTable ORDER BY MyTable";
                $PageNameArr2 = $mainDB->fetchAll($SQL);               
              }             
            }
            ?>
            <input type='hidden' name="tmpDBname" value='<?php echo $HITS_DB[$DBnameArr['DBname']]?>'>    
  				  <select name="frm_Page" size=15 onChange="item_selected(this.form)">                 
  				 	<?php 
            if(count($PageNameArr2)){
              echo "<option  value='allPages'";
              echo ($frm_Page=='allPages')?" selected":"";
              echo ">All Pages\n";
              for ($i= 0;$i < count($PageNameArr2); $i++){            
  						  echo  "<option  value='".$PageNameArr2[$i]['MyTable']."'";
                echo ($PageNameArr2[$i]['MyTable']==$frm_Page)?" selected":"";   
                echo ">".$PageNameArr2[$i]['MyTable']."\n";
              }
            }  
  					?>
  				  </select>&nbsp;&nbsp;<br>&nbsp;                  
	        </td>    
          <td bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top align=center width=50% nowrap>&nbsp;
          <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="2"><b>Users</b></font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          all users<input type="checkbox" name="frm_allUsers" value='Y' <?php echo ($frm_allUsers)?"checked":""?> onclick='is_allUsers();'> 
          <br>&nbsp; &nbsp;
            <?php 
            $FullNameArr2 = array();
            $UserIDstr = "";
            if($frm_Project && $frm_Page){
              $SQL = "SELECT UserID 
                      FROM Log
                      WHERE ProjectID='$frm_Project'";
              if($frm_Page != "allPages"){
                $SQL .= " AND MyTable='$frm_Page'";
              }
              $SQL .= " GROUP BY UserID";
              $UserIDArr2 = $mainDB->fetchAll($SQL);
              
              for($i=0; $i<count($UserIDArr2); $i++){
                if($UserIDstr){
                  $UserIDstr .= "','";
                }else{
                  $UserIDstr .= "'";
                }
                $UserIDstr .= $UserIDArr2[$i]['UserID'];
              }
              if($UserIDstr){
                $UserIDstr .= "'";
              }
            }
            back_to_oldDB($mainDB, $oldDBName);
            
            if($UserIDstr){
              $SQL = "SELECT ID, Fname, Lname FROM User WHERE ID IN ($UserIDstr) GROUP BY ID ORDER BY Fname";
              $FullNameArr2 = $mainDB->fetchAll($SQL);
            }            
            ?>
            <input type='hidden' name='UserIDstr' value="<?php echo $UserIDstr;?>" onClick="check_form(this.form)">
  				  <select name="frm_User" size=15 multiple>                 
  				 	<?php 
            if(!$frm_allUsers){            
              for($i= 0;$i < count($FullNameArr2); $i++){            
  						  echo  "<option  value='".$FullNameArr2[$i]['ID']."'";
                echo ($FullNameArr2[$i]['ID']==$frm_User)?" selected":"";   
                echo ">".$FullNameArr2[$i]['Fname']." ".$FullNameArr2[$i]['Lname']."\n";
              }
            }  
  					?>
  				  </select>&nbsp;&nbsp;<br>&nbsp;        
	        </td>    
        </tr>	
        <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
		      <td colspan="3" align="center" height=25>
		      <input type='button' name='frm_submit' value=' Submit ' onClick="check_form(this.form)">
		      </td>
	     </tr>        
      </table>
<?php }?>      
  </form>
  </td>
  </tr>
</table>
<?php 
include("./admin_footer.php");
?>