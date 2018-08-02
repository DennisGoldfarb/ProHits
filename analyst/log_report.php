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

$frm_Category = '';
$frm_Page = '';
$frm_User = '';
$frm_allUsers = '';
$theaction ='';
$order_by = 'ID desc';
$start_point = 0;

define ("RESULTS_PER_PAGE", 100);
define ("MAX_PAGES", 15); //this is max page link to display

require("../common/site_permission.inc.php");
include("admin_office/admin_log_class.php");
include("admin_office/common_functions.inc.php");
require("common/page_counter_class.php");
require("analyst/site_header.php");

if(isset($first)){
  $AdminLog = new AdminLog();
  $Desc = "";       
  $AdminLog->insert($AccessUserID,'Log','','browser',$Desc);
}

if($frm_Page){
  if(!isset($category_lable_arr[$frm_Page])){
    if($frm_Category == "_pro_link"){
      $category_lable_arr[$frm_Page] = ret_pro_link_lable($frm_Page);
    }elseif($frm_Category == "Results"){
      $category_lable_arr[$frm_Page] = ret_search_lable($frm_Page);
    }  
  }
}  

$category_arr['Bait'] = "Bait,BaitGroup";
  
$category_lable_arr['Bait'] = 'Bait';
$category_lable_arr['BaitGroup'] = 'Bait Notes';
//------------------------------------------------------------------------
$category_arr['Experiment'] = "Experiment,ExperimentGroup,Protocol";

$category_lable_arr['Experiment'] = 'Experiment';
$category_lable_arr['ExperimentGroup'] = 'Experiment Notes';
$category_lable_arr['Protocol'] = 'Experiment Protocol';


//------------------------------------------------------------------------
$category_arr['Band'] = "Plate,PlateWell,Gel,Lane,Band,BandGroup";

$category_lable_arr['Band'] = 'Sample';
$category_lable_arr['BandGroup'] = 'Sample Notes';

$category_lable_arr['Plate'] = 'Plate';
$category_lable_arr['PlateWell'] = 'Plate Well';

$category_lable_arr['Gel'] = 'Gel';
$category_lable_arr['Lane'] = 'Lane';
//------------------------------------------------------------------------

$category_arr['Hit'] = "HitDiscussion,HitNote";

$category_lable_arr['HitDiscussion'] = 'Hit Notes';
$category_lable_arr['HitNote'] = 'Hit Experiment Filters'; 
//------------------------------------------------------------------------
$category_arr['Group Lists'] = "NoteType";

$category_lable_arr['NoteType'] = 'Group Lists';
//------------------------------------------------------------------------
$category_arr['Search Results'] = "Results";
//------------------------------------------------------------------------
$category_arr['Coip'] = "Coip";

$category_lable_arr['Coip'] = 'Coip';
//------------------------------------------------------------------------
$category_arr['Raw File'] = "_pro_link";

/*echo "<pre>";
print_r($category_lable_arr);
echo "</pre>";*/
?>

<SCRIPT language=JavaScript>
<!--
function Is_ProjectSelected(theForm){  
  selObj = theForm.frm_Category;
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
  if(!theForm.frm_Category.value){
    alert("Please select a category");
  }else if(!theForm.frm_Page.value){
    alert("Please select a page");
  }else if(!theForm.frm_User.value){
    alert("Please select users");
  }else{
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
  file = 'log_report_pop_one.php?RecordID=' + RecordID + '&MyTable=' + MyTable + '&frm_User=' + theForm.frm_User.value;
  newNote = window.open(file,"Record",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=940,height=500');
  newNote.moveTo(40,0);
}
-->
</SCRIPT>
<br>

<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td align="left">
    <font color="navy" face="helvetica,arial,futura" size="5"><b>Log Report</b></font>&nbsp;
		<font color='red' face='helvetica,arial,futura' size='3'><b>(Project <?php echo $AccessProjectID?>: <?php echo $AccessProjectName?>)</b></font>
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
  $UserNameArr = get_users_ID_Name($HITSDB);
  $ProjectNameArr = get_project_ID_Name($HITSDB);
  if(!$frm_User || !$frm_Page) exit;
  $SQL = "SELECT COUNT(ID) FROM Log 
         WHERE ProjectID='$AccessProjectID' 
         AND UserID='$frm_User'
         AND MyTable='$frm_Page'";
  $row = mysqli_fetch_row(mysqli_query($HITSDB->link, $SQL));        
  $total_records = $row[0]; 
  
  $PAGE_COUNTER = new PageCounter();
  
  $query_string = "frm_Page=$frm_Page&frm_User=$frm_User&frm_Category=$frm_Category";
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
         WHERE ProjectID='$AccessProjectID' 
         AND UserID='$frm_User'
         AND MyTable='$frm_Page'";
  if($order_by){
    $SQL .= " ORDER BY $order_by ";
  }   
  if(RESULTS_PER_PAGE){
    $SQL .= " LIMIT $start_point, ".RESULTS_PER_PAGE;
  }
  $result = mysqli_query($HITSDB->link, $SQL);
?>
  <input type='hidden' name='frm_Page' value="<?php echo $frm_Page?>">
  <input type='hidden' name='frm_User' value="<?php echo $frm_User?>">
  <input type='hidden' name='frm_Category' value="<?php echo $frm_Category?>">
  <input type='hidden' name='order_by' value=''> 
  <table border="0" cellpadding="0" cellspacing="1" width="900">
  <tr>
    <td colspan=9 height=25>
      <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="3">Table:</font>&nbsp;
      <font color="red" face="helvetica,arial,futura" size="3">
        <?php echo $category_lable_arr[$frm_Page];?></font>&nbsp;&nbsp;&nbsp;&nbsp;      
    </td>
  </tr>
  <tr>
    <td colspan=9 height=25>
      <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="3">Owner:</font>&nbsp;
      <font color="red" face="helvetica,arial,futura" size="3">
        <?php echo (isset($UserNameArr[$frm_User]))?$UserNameArr[$frm_User]:"";?></font>&nbsp;&nbsp;&nbsp;&nbsp;      
    </td>
  </tr>
  <tr><td colspan=10 align=right height=25><?php echo $page_output;?></td></tr>
  <tr bgcolor="">
    <td width="10%" height="25" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center><div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "RecordID")? 'RecordID%20desc':'RecordID';?>');">
    Record ID</a>
    <?php if($order_by == "RecordID") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "RecordID desc") echo "<img src='images/icon_order_down.gif'>";
    ?></div>  
    </div>
    </td>
    <td width="10%" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center> <div class=tableheader>
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
    <td width="15%" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center><div class=tableheader>
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
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" height=20>
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
          <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="2"><b>Category</b></font><br>&nbsp; &nbsp;  
  				  <select name="frm_Category" size=15 onChange="item_selected(this.form)">                 
  				 	<?php 
            foreach($category_arr as $key => $val){            
						  echo  "<option  value='".$val."'";
              echo ($val==$frm_Category)?" selected":"";   
              echo ">".$key."\n";
            }
  					?>
  				  </select>&nbsp;&nbsp;<br>&nbsp;                   
	        </td>
          
          <td bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top align=center width=33% nowrap>&nbsp;
            <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="2"><b>Pages</b></font><br>&nbsp; &nbsp; 
            <?php        
            $PageNameArr = array();
            if($frm_Category){
              if($frm_Category == "_pro_link"){
                $MyTable = " MyTable LIKE '%_pro_link' ";
              }elseif($frm_Category == "Results"){
                $MyTable = " MyTable LIKE '%Results' ";
              }else{
                $frm_Category = preg_replace("/,/", "','", $frm_Category);
                $frm_Category = "'".$frm_Category."'";
                $MyTable = " MyTable IN($frm_Category) ";
              }            
              
              $SQL = "SELECT MyTable
                      FROM Log
                      WHERE ProjectID='$AccessProjectID'
                      AND $MyTable
                      GROUP BY MyTable";
              $PageNameArr = $HITSDB->fetchAll($SQL);
              if($frm_Category == "_pro_link"){
                foreach($PageNameArr as $PageNameVal){
                  $lable = ret_pro_link_lable($PageNameVal['MyTable']);
                  $category_lable_arr[$PageNameVal['MyTable']] = $lable;
                }
              }elseif($frm_Category == "Results"){
                foreach($PageNameArr as $PageNameVal){
                  $lable = ret_search_lable($PageNameVal['MyTable']);
                  $category_lable_arr[$PageNameVal['MyTable']] = $lable;
                }
              }               
            }            
            ?> 
  				  <select name="frm_Page" size=15" onChange="item_selected(this.form)">                 
  				 	<?php 
            foreach($PageNameArr as $PageNameVal){
						  echo  "<option  value='".$PageNameVal['MyTable']."'";
              echo ($PageNameVal['MyTable']==$frm_Page)?" selected":"";   
              echo ">".$category_lable_arr[$PageNameVal['MyTable']]."\n";
            }  
  					?>
  				  </select>&nbsp;&nbsp;<br>&nbsp;                  
	        </td>
          
          
          
          <td bgcolor="<?php echo $TB_CELL_COLOR;?>" valign=top align=center width=50% nowrap>&nbsp;
          <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="2"><b>Users</b></font><br>&nbsp; &nbsp;
            <?php 
            $FullNameArr = array();
            if($frm_Category && $frm_Page){
              $frm_Category = preg_replace("/,/", "','", $frm_Category);
              $frm_Category = "'".$frm_Category."'"; 
              $SQL = "SELECT UserID 
                      FROM Log
                      WHERE ProjectID='$AccessProjectID'
                      AND MyTable = '$frm_Page'
                      GROUP BY UserID";
              $UserIDArr = $HITSDB->fetchAll($SQL);
              $UserID_str = '';
              foreach($UserIDArr as $UserIDVal){
                if($UserID_str) $UserID_str .= ',';
                $UserID_str .= $UserIDVal['UserID'];
              }             
              if($UserID_str){
                $SQL = "SELECT ID, Fname, Lname FROM User WHERE ID IN($UserID_str) ORDER BY Fname";
                $FullNameArr = $PROHITSDB->fetchAll($SQL);
              }
            }        
            ?>
  				  <select name="frm_User" size=15 multiple>                 
  				 	<?php      
            foreach($FullNameArr as $FullNameVal){            
						  echo  "<option  value='".$FullNameVal['ID']."'";
              echo ($FullNameVal['ID']==$frm_User)?" selected":"";   
              echo ">".$FullNameVal['Fname'].' '.$FullNameVal['Lname']."\n";
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
require("site_footer.php");
function ret_search_lable($MyTable){
  $lable = '';
  $pattern = '/^(\w+)(Search|tpp)(Results)$/';
  if(preg_match($pattern, $MyTable, $matches)){
    $lable = $matches[1].' '.$matches[2].' '.$matches[3];
  }  
  return $lable;
} 
function ret_pro_link_lable($MyTable){
  $lable = '';
  $pattern = '/^(\w+)(_pro_link)$/';
  if(preg_match($pattern, $MyTable, $matches)){
    $lable = $matches[1]." raw file link";
  }  
  return $lable;
}       
?>