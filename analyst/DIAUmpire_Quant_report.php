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
$theProjectName = '';
$order_by = '';
$start_point ='';
$error_msg = '';
$img_msg = '';
$title_lable = '';
$query_string = '';

$frm_user_id = '';
$DIAUmpireQuant_ID = '';
$result_file = '';

//-------------------------------------------

require("../common/site_permission.inc.php");
require("common/page_counter_class.php");
include("analyst/common_functions.inc.php");
require("common/common_fun.inc.php");
require_once("msManager/is_dir_file.inc.php");

define ("RESULTS_PER_PAGE", 100);
define ("MAX_PAGES", 15); //this is max page link to display

$Log = new Log($PROHITSDB->link);
$bgcolor = $TB_CELL_COLOR;
$bgcolordark = "#5c8ca3";

$DIAUmpireQuant_folder = STORAGE_FOLDER."Prohits_Data/DIAUmpireQuant_results/";

if($theaction == 'export' and $DIAUmpireQuant_ID){
  $DIAUmpireQuant_task_folder = $DIAUmpireQuant_folder."task_".$DIAUmpireQuant_ID."/";
  $DIAUmpireQuant_zip = $DIAUmpireQuant_task_folder ."Results.zip";
  if(!_is_file($DIAUmpireQuant_zip)){
    if(_is_dir($DIAUmpireQuant_task_folder ."Results")){
      $cmd = "cd $DIAUmpireQuant_task_folder; zip $DIAUmpireQuant_zip -r Results/*";
      $result = @exec($cmd);
      if(!$result){
        echo  "Can not create a zip file now in $DIAUmpireQuant_task_folder.";
        exit;
      }
    }else{
      echo  "no DIA-Umpire Quantitation results found in $DIAUmpireQuant_task_folder";
      exit;
    }
  }
  header("Cache-Control: public, must-revalidate");
  header("Content-Type: application/zip");  //download-to-disk dialog
  //header("Content-Disposition: attachment; filename=".basename($DIAUmpireQuant_zip).";" );
  header("Content-Disposition: attachment; filename=DIAUmpireQuant_results_ID_".$DIAUmpireQuant_ID.".zip" );
  header("Content-Transfer-Encoding: binary");
  header("Content-Length:"._filesize($DIAUmpireQuant_zip));
  header("Expires: 0");
  ob_clean();
  readfile("$DIAUmpireQuant_zip");
  exit();
}else if($theaction == 'delete' and $DIAUmpireQuant_ID){
  $SQL = "update DIAUmpireQuant_log set Status='deleted' where ID='$DIAUmpireQuant_ID' and UserID='".$USER->ID."'";
  $PROHITSDB->update($SQL);
  $Desc = "";
  $Log->insert($AccessUserID,'DIAUmpireQuant_log',$DIAUmpireQuant_ID,'deleted',$Desc,$AccessProjectID);
}elseif($theaction == 'export_log' && $DIAUmpireQuant_ID){
  $DIAUmpireQuant_task_folder = $DIAUmpireQuant_folder."task_".$DIAUmpireQuant_ID."/";
  $log_file = $DIAUmpireQuant_task_folder."Results/task.log";
  echo "<pre>";
  echo file_get_contents($log_file);
  echo "</pre>";
  exit;
}


//page counter start here----------------------------------------------
$SQL = "SELECT COUNT(ID) AS Total_records FROM DIAUmpireQuant_log 
        WHERE ProjectID='$AccessProjectID' and Status<>'deleted' ";
if($frm_user_id){
  $SQL2 = $SQL . " AND UserID = $frm_user_id ";
  $tmp_arr_m = $PROHITSDB->fetch($SQL2);
  if($tmp_arr_m['Total_records']){
    $SQL = $SQL2;
  }else{
    if(isset($first_show)){
      $frm_user_id = '';
    }else{
      $SQL = $SQL2;
    }
  }
}

if($SQL){
  $tmp_arr_m = $PROHITSDB->fetch($SQL);
  $total_records = $tmp_arr_m['Total_records'];
}else{
  $total_records = 0;
}
$PAGE_COUNTER = new PageCounter('Exp_Status');
$caption = "SAINTs";
if($order_by) { 
  $query_string .= "&order_by=".$order_by;
}

$page_output = $PAGE_COUNTER->page_links($start_point, $total_records, RESULTS_PER_PAGE, MAX_PAGES, str_replace(' ','%20',$query_string));

//end of page counter-----------------------------------------------------------------

if(!$order_by) $order_by = "ID desc";
if(!$start_point) $start_point = 0;
$SQL = "SELECT ID, `Name`, `Machine`, `SearchEngine`, `UserID`, `Date` , `Description`, `Status` , `ProjectID`, `UserOptions`,`ParentQuantID`,`ProcessID` 
  FROM DIAUmpireQuant_log WHERE  ProjectID=$AccessProjectID and Status<>'deleted' ";
if(isset($frm_user_id) && $frm_user_id){
  $SQL .= " AND UserID = $frm_user_id";
}
$SQL .= " ORDER BY $order_by";

$SQL .= " LIMIT $start_point, ".RESULTS_PER_PAGE;
$UmpireQuant_records = $PROHITSDB->fetchAll($SQL);
//$running_saint_arr = get_running_saint();

require("site_header.php");
echo "<font color=red face=\"helvetica,arial,futura\">".$error_msg."</font>";
?>
<script language="javascript">
function sortList(order_by){
  var theForm = document.del_form;
  theForm.order_by.value = order_by;
  theForm.submit();
}  

function change_user(theForm){
  theForm.start_point.value = 0;
  theForm.theaction.value = 'viewall';
  theForm.action = 'DIAUmpire_Quant_report.php';
  theForm.target = '_self';
  theForm.submit();
}

function generate_report(result_id,is_uploaded,file_single_name){
  var theForm = document.del_form; 
  var currentType = theForm.currentType.value;
  var SearchEngine = theForm.SearchEngine.value;
  var is_uploaded = is_uploaded;
  var start_point = theForm.start_point.value;
  var frm_user_id = theForm.frm_user_id.value;
  var theaction = theForm.theaction.value;
  var DIAUmpireQuant_ID = result_id;
  var order_by = theForm.order_by.value;
  var title_lable = theForm.title_lable.value;
  var result_files = theForm.result_files;
  var checked_flag = false;
  
  if(file_single_name){
    var result_file_name = file_single_name;
  }else{
    for(var i=0; i<result_files.length; i++){
      if(result_files[i].checked){
        var result_file_name = result_files[i].value;
        checked_flag = true;
        break;
      }
    }
    if(!checked_flag){
      alert("Please select a result file to display.");
      return;
    }
  }
   
  file = 'DIAUmpire_Quant_comparison_results_table.php'+'?currentType='+currentType+'&SearchEngine='+SearchEngine+'&is_uploaded='+is_uploaded+'&DIAUmpireQuant_ID='+DIAUmpireQuant_ID
         +'&frm_user_id='+frm_user_id+'&order_by='+order_by+'&title_lable='+title_lable+'&result_file_name='+result_file_name;
  popwin(file,1100,800);
}

function check_status(theID, processID, machineName){
  file = 'DIAUmpire_Quant_run.php?umpireQuant_ID=' + theID + '&theaction=checkStatus&ProcessID='+processID + '&machine='+machineName;
  popwin(file,600,400);
}
function saint_input_files(theID){
  file = 'DIAUmpireQuant_input_files.pop.php?DIAUmpireQuant_ID=' + theID;
  popwin(file,800,600);
}
function run_saint(theID, theOptions){
  file = 'export_SAINT_file.php?theaction=re_run_saint&DIAUmpireQuant_ID=' + theID + '&other_option=' + theOptions;
  popwin(file,800,600);
}
function export_saint_results(theID){
  document.location = '<?php echo $PHP_SELF;?>?theaction=export&DIAUmpireQuant_ID=' + theID;
}
function saint_delete(theID){
  var theForm = document.del_form;
  theForm.action = '<?php echo $PHP_SELF;?>';
  theForm.target = '_self';
  theForm.DIAUmpireQuant_ID.value = theID;
  theForm.theaction.value = 'delete';
  if(confirm("Are you sure that you want to delete the DIA-Umpire Quantitatino results?")){
    theForm.submit(); 
  }
}
function Exp_Status(temp_point){
  var theForm = document.del_form;
  theForm.action="<?php echo $PHP_SELF;?>";
  theForm.start_point.value = temp_point;
  //set_group_id_list(theForm);
  theForm.submit();
}
function comfirm_showTip(event,block_div){
  showTip(event,block_div);
}
function export_log(DIAUmpireQuant_ID){
  var theForm = document.del_form;
  theForm.theaction.value = 'export_log';
  theForm.DIAUmpireQuant_ID.value = DIAUmpireQuant_ID;
  theForm.submit();
}
function open_log_win(log_str){
  var myWindow = window.open("", "MsgWindow", "width=800, height=300");
  myWindow.document.write(log_str);
}
function pop_log(UmpireID){
  file = '<?php echo $PHP_SELF;?>?DIAUmpireQuant_ID=' + UmpireID + '&theaction=export_log';
  popwin(file,600,600);
}
function reRun_Quant(theID){
  file = 'DIAUmpire_Quant_reRun_prepare.php?theaction=re_run_Quant&parentID=' + theID;
  newWin = window.open(file,"subWin",'toolbar=1,location=0,directories=0,status=0,menubar=1,scrollbars=1,resizable=1,width=800,height=800');
  newWin.focus();
}

</script>
<form name="del_form" method=post action="<?php echo $PHP_SELF;?>">
<INPUT TYPE="hidden" NAME="currentType" VALUE="DIAUmpireQuant">
<INPUT TYPE="hidden" NAME="SearchEngine" VALUE="">
<INPUT TYPE="hidden" NAME="is_uploaded" VALUE="">
<table border="0" cellpadding="0" cellspacing="0" width="95%">
  <tr>
    <td align="left" NOWRAP><br>
    &nbsp; <font color="navy"  face="helvetica,arial,futura" size="5"><b><?php echo ($title_lable)?$title_lable:"DIA-Umpire Quantitation";?> </b></font>
<?php 
    if($AccessProjectName){
      echo "<font color='red' face='helvetica,arial,futura' size='3'><b>(Project $AccessProjectID: $AccessProjectName)</b></font>";
    }
    if($sub){
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color='green' face='helvetica,arial,futura' size='3'><b>(Submit Gel Sample)</b></font>";
    }
    ?>
   </td>
   <td align="left" valign="bottom" width="90%"> 
      <input type=hidden name=start_point value='<?php echo $start_point?>'>
    
      &nbsp;&nbsp;<font face="helvetica,arial,futura" size="2"><b>User</b></font>
      <?php $users_list_arr = show_project_users_list();?>
      <select id="frm_user_id" name="frm_user_id" onchange="change_user(this.form)">
        <option value="">All Users		            
      <?php foreach($users_list_arr as $key => $val){?>              
        <option value="<?php echo $key?>"<?php echo ($frm_user_id==$key)?" selected":"";?>>(<?php echo $key?>)<?php echo $val?>			
      <?php }?>
      </select>
       &nbsp;
    </td>
    <td align="right" NOWRAP valign="bottom"><!--[<a href="javascript: popwin('../logs/log_view.php?log_file=DIAUmpire_quant.log',700,500)" class=button><b>Log</b></a>]-->
    &nbsp;&nbsp;&nbsp;
    [<a href=./DIAUmpire_Quant.php class=button><b>New Task</b></a>]    
    </td>
  </tr>
  <tr>
    <td colspan=3 height=1 bgcolor="black"><img src="images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="center" colspan=5 valign=top>
  <table border="0" cellpadding="0" cellspacing="1" width="900">
  <input type=hidden name=theaction value="<?php echo $theaction?>">  
  <input type=hidden name=DIAUmpireQuant_ID value=""> 
  <input type=hidden name=order_by value='<?php echo $order_by;?>'>
  <input type=hidden name=title_lable value='<?php echo $title_lable;?>'>

  <tr>
    <td colspan=7 align=right>
    <!--a href="javascript: popwin('../logs/DIAUmpireQuant.log', 800, 700);" class=button>[DIAUmpireQuant Log]</a-->&nbsp; &nbsp; &nbsp; 
    </a><?php echo $page_output;?></td>
  </tr>
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
     <a href="javascript: sortList('<?php echo ($order_by == "Name")? 'Name%20desc':'Name';?>');">DIAUmpireQuant Name</a>&nbsp;
    <?php if($order_by == "Name") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "Name desc") echo "<img src='images/icon_order_down.gif'>";
    ?> </div>
    </td>
    <td width="120" bgcolor="<?php echo $bgcolordark;?>" align=center nowrap><div class=tableheader>
      <div class=tableheader>
     <a href="javascript: sortList('<?php echo ($order_by == "Machine")? 'Machine%20desc':'Machine';?>');">Machine</a>&nbsp;
    <?php if($order_by == "Machine") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "Machine desc") echo "<img src='images/icon_order_down.gif'>";
    ?> </div>
    </td>
    <td width="120" bgcolor="<?php echo $bgcolordark;?>" align=center nowrap><div class=tableheader>
      <div class=tableheader>
     <a href="javascript: sortList('<?php echo ($order_by == "SearchEngine")? 'SearchEngine%20desc':'SearchEngine';?>');">SearchEngine</a>&nbsp;
    <?php if($order_by == "SearchEngine") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "SearchEngine desc") echo "<img src='images/icon_order_down.gif'>";
    ?> </div>
    </td>
    
    
    <td width="100" bgcolor="<?php echo $bgcolordark;?>" align=center>
      <div class=tableheader>Status</div> 
    </td>
    <td width="100" bgcolor="<?php echo $bgcolordark;?>" align="center"><div class=tableheader>
      <div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "UserID")? 'UserID%20desc':'UserID';?>');">User</a>&nbsp;
      <?php if($order_by == "UserID") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "UserID desc") echo "<img src='images/icon_order_down.gif'>";
    ?> </div>
    </td>
    <td width="100" bgcolor="<?php echo $bgcolordark;?>" align="center"><div class=tableheader>
       DIAUmpireQuant<br>version</a>
    </td>
    
    <td width="100" bgcolor="<?php echo $bgcolordark;?>" align="center"><div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "Date")? 'Date%20desc':'Date';?>');">Created On</a>&nbsp;
    <?php if($order_by == "Date") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "Date desc") echo "<img src='images/icon_order_down.gif'>";
    ?> 
    </td>
    <td width="" height="25" bgcolor="<?php echo $bgcolordark;?>" align="center">
      <div class=tableheader>Options</div>
    </td>
  </tr>
<?php 
  
  for($i=0; $i < count($UmpireQuant_records); $i++) {
    $op_div = "sDiv_".$UmpireQuant_records[$i]['ID'];
?>
    <tr  bgcolor='<?php echo $bgcolor;?>' onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $bgcolor;?>');">
      <td width="" align="left">
        <div class=maintext>&nbsp;
          <?php echo $UmpireQuant_records[$i]['ID'];?>&nbsp;
        </div>
      </td>
      <td width="250" align="left">
        <div class=maintext>&nbsp;
          <a href="javascript: showhide('<?php echo $op_div?>','')">
          <?php 
            echo $UmpireQuant_records[$i]['Name']."";
            if($UmpireQuant_records[$i]['ParentQuantID']) echo " (".$UmpireQuant_records[$i]['ParentQuantID'].")";
          ?>&nbsp;
          </a>&nbsp;&nbsp;
          <?php 
            $log_file = $DIAUmpireQuant_folder."task_".$UmpireQuant_records[$i]['ID']."/Results/task.log";
            if(is_file($log_file)){?>
            <a  title='view log file' href="javascript: pop_log('<?php echo $UmpireQuant_records[$i]['ID']?>');">[Log]</a>
          <?php }?>     
          <DIV id='<?php echo $op_div;?>' STYLE="display: none" class=maintext>
            <table border="0"  cellpadding="0" cellspacing="0" width=90% bgcolor=#cccccc align=center>
              <tr>
              <td>
               <?php 
               $UserOptions = preg_replace('/,+/', "<br>", nl2br($UmpireQuant_records[$i]['UserOptions']));
               echo str_replace(";","<br>", nl2br($UserOptions));
               echo "<br>\nDescription:<br>";
               echo nl2br($UmpireQuant_records[$i]['Description']);
               ?>
              </td>
              </tr>
            </table>
         </DIV>
        </div>
      </td>
      <td width="" align="left"><div class=maintext>&nbsp;
          <?php echo $UmpireQuant_records[$i]['Machine'];?>&nbsp;
        </div>
      </td>
      <td width="" align="left"><div class=maintext>&nbsp;
          <?php echo $UmpireQuant_records[$i]['SearchEngine'];?>&nbsp;
        </div>
      </td>
          <?php 
          $status_color = '';
          if(isset($running_saint_arr[$UmpireQuant_records[$i]['ID']])){
            $status_color = ' bgcolor=green';
          }
          ?>
      <td width="" align="left"<?php echo $status_color?>><div class=maintext>&nbsp;
          <?php 
          echo $UmpireQuant_records[$i]['Status'];
          ?>&nbsp;
        </div>
      </td>
      <td width="" align="left" nowrap><div class=maintext>&nbsp;
          <?php  echo get_userName($PROHITSDB, $UmpireQuant_records[$i]['UserID']);?>&nbsp;
        </div>
      </td>
      <td width="" align="left" nowrap><div class=maintext>
          <?php 
          
          if(preg_match("/(QUANT=Version:[^,|^;|^\|]+)/",$UmpireQuant_records[$i]['UserOptions'], $matches)){
            $tmp_str = str_replace("="," ",$matches[1]);
            echo str_replace("version:","",$tmp_str);
          }
          if(preg_match("/(SAINT=Version:[^,]+)/",$UmpireQuant_records[$i]['UserOptions'], $matches)){
            echo "<br>".str_replace("="," ",$matches[1]);
          }
          if(preg_match("/(mapDIA=###[ ][^,]+)/",$UmpireQuant_records[$i]['UserOptions'], $matches)){
            echo "<br>".str_replace("=###"," ",$matches[1]);
            //$tmp_matche = str_replace("="," ",$matches[1]);
            //echo "<br>".str_replace("#","",$tmp_matche);
          }
          ?> 
        </div>
      </td> 
      <td width="" align="left"><div class=maintext>&nbsp;
          <?php echo $UmpireQuant_records[$i]['Date'];?>&nbsp;
        </div>
      </td>
      <td width="" align="left" nowrap><div class=maintext>&nbsp;
      <?php 
      if(!isset($running_saint_arr[$UmpireQuant_records[$i]['ID']])){
      //if($UmpireQuant_records[$i]['UserID'] == $USER->ID and !isset($running_saint_arr[$UmpireQuant_records[$i]['ID']])){
        if($UmpireQuant_records[$i]['Status'] == 'Running'){
           echo "<a href=\"javascript: check_status('".$UmpireQuant_records[$i]['ID']."','".$UmpireQuant_records[$i]['ProcessID']."','".$UmpireQuant_records[$i]['Machine']."' )\"  title='click to get task status'><font color=#008080>[TASK STATUS]</font></a>";
        }else{
          echo "<!--a href=\"javascript: saint_delete('".$UmpireQuant_records[$i]['ID']."')\"  title='delete'>
          <img src='./images/icon_purge.gif' border=0></a-->\n";
          
          echo "
          <a href=\"javascript: saint_input_files('".$UmpireQuant_records[$i]['ID']."')\"  title='DIAUmpireQuant input files'>
          <img src=\"./images/icon_view.gif\" border=0></a>
          ";
          
          //if(!$UmpireQuant_records[$i]['ParentQuantID'] or $UmpireQuant_records[$i]['Status'] != 'Finished'){
          if(!$UmpireQuant_records[$i]['ParentQuantID']){
            echo "<a href=\"javascript: reRun_Quant('".$UmpireQuant_records[$i]['ID']."')\"  title='Use DIAUmpire Qaunt results to run SAINAT/mapDIA.'>
            <img src=\"./images/icon_process.png\" border=0></a>";
          }else{
            echo "<img src=\"./images/icon_empty.gif\" border=0>";
          }
          
        }
      }else{
        echo "<img src=\"./images/icon_empty.gif\" border=0>&nbsp;";
        echo "<img src=\"./images/icon_empty.gif\" border=0> "; 
      }
      if($UmpireQuant_records[$i]['Status'] == 'Finished'){
          if(preg_match('/\((uploaded)\)/', $UmpireQuant_records[$i]['Name'], $matches)){
            $is_uploaded = 'y';
          }else{
            $is_uploaded = '';
          }
          $DIAUmpireQuant_folder = STORAGE_FOLDER."Prohits_Data/DIAUmpireQuant_results/";
          $DIAUmpireQuant_task_Results_folder = $DIAUmpireQuant_folder."task_".$UmpireQuant_records[$i]['ID']."/Results";
          $dir_arr = scandir($DIAUmpireQuant_task_Results_folder);
          $result_file_arr = array();
          foreach($dir_arr as $dir_val){
            if($dir_val=='.' || $dir_val=='..') continue;
            if(is_dir($DIAUmpireQuant_task_Results_folder."/mapDIA")){
              if($dir_val == 'mapDIA' && is_file($DIAUmpireQuant_task_Results_folder.'/'.$dir_val.'/analysis_output.txt')){
                $result_file_arr[] = $dir_val.'/analysis_output.txt';
              }
            }else{
              if($dir_val == 'list_MS1.txt' && is_file($DIAUmpireQuant_task_Results_folder.'/'.$dir_val)){
                $result_file_arr[] = $dir_val;
              }elseif($dir_val == 'list_MS2.txt' && is_file($DIAUmpireQuant_task_Results_folder.'/'.$dir_val)){
                $result_file_arr[] = $dir_val;
              }elseif($dir_val == 'RESULT_MS1' && is_file($DIAUmpireQuant_task_Results_folder.'/'.$dir_val.'/unique_interactions')){
                $result_file_arr[] = $dir_val.'/unique_interactions';
              }elseif($dir_val == 'RESULT_MS2' && is_file($DIAUmpireQuant_task_Results_folder.'/'.$dir_val.'/unique_interactions')){
                $result_file_arr[] = $dir_val.'/unique_interactions';
              }
            }  
          }          
          if(count($result_file_arr) == 1){
            echo "<img src=\"./images/icon_empty.gif\" border=0>&nbsp;&nbsp;";
          }elseif($result_file_arr){
            $div_id = 'results_div_'.$UmpireQuant_records[$i]['ID'];
?>
          <a href="javascript: href_show_hand();" onclick="showTip(event,'<?php echo $div_id?>');"><img src="./images/icon_report.gif" border=0></a>
            <DIV ID='<?php echo $div_id?>' STYLE="position: absolute; 
                                  display: none;
                                  border: black solid 1px;
                                  width: 210px";>
              <table align="center" cellspacing="0" cellpadding="1" border="0" width=100% bgcolor="#e6e6cc">
                <tr bgcolor="#c1c184" height=25><td valign="bottem">&nbsp;&nbsp;&nbsp;<font color="white" face="helvetica,arial,futura" size="2"><b>Select results file:</b></font></td></tr>
            <?php 
            foreach($result_file_arr as $result_file_val){
                $tmp_arr = explode("/",$result_file_val);
                if(count($tmp_arr) == 1){
                  $result_file = $tmp_arr[0]."&nbsp;&nbsp;(SAINT exp)";
                }else{
                  $result_file = $tmp_arr[0]."&nbsp;&nbsp;(SAINT)";
                }
            ?>
                <tr bgcolor="#e6e6cc">
                  <td>&nbsp;&nbsp;&nbsp;
                    <input type=radio NAME="result_files" VALUE="<?php echo $result_file_val?>" <?php ($result_file==$result_file_val)?'checked':''?>>&nbsp;<font color="black" face="helvetica,arial,futura" size="2"><?php echo $result_file?></font>
                  </td>
                </tr>
            <?php }?>
                <tr bgcolor="#e6e6cc"><td align="center" height=35><input type=button name='' VALUE=' Confirm ' onclick="generate_report('<?php echo $UmpireQuant_records[$i]['ID']?>','<?php echo $is_uploaded?>')"  title='DIAUmpireQuant results'>&nbsp;&nbsp;
                <input type=button name='hide_div' VALUE=" Close " onclick="javascript: hideTip('<?php echo $div_id?>');">
                </td>
                </tr>
              </table>   
            </DIV>
<?php           
          }else{
            echo "<img src=\"./images/icon_empty.gif\" border=0>&nbsp;&nbsp;";
          }
          echo "<a href=\"javascript: export_saint_results('".$UmpireQuant_records[$i]['ID']."')\"  title='download DIAUmpireQuant results'>";
          echo "<img src=\"../msManager/images/icon_download.gif\" border=0>";
          echo "</a>";
        }else{
          echo "<img src=\"./images/icon_empty.gif\" border=0>";
        }
      ?> 
      </td>
    </tr>
  <?php 
  } //end for
  ?>  
   </table>
  </form>
    </td>
  </tr>
</table>
<br>
<?php 
require("site_footer.php");

function add_uniq_pep($ID){
  $dir = STORAGE_FOLDER."/Prohits_Data/SAINT_results/saint_$ID";
  $RESULT_file = $dir."/RESULT/list.txt";
  $input_file = $dir."/inter.dat";  
  $RESULT_file_tmp = $dir."/RESULT/list_tmp.txt";
  if(!$RESULT_handle = fopen($RESULT_file, 'r')){
    echo "Cannot open file ($RESULT_file)";
    exit;
  }
  $RESULT_arr = array();
  while(($buffer = fgets($RESULT_handle, 4096)) !== false){
    $tmp_arr = explode("\t", $buffer);
    $RESULT_key = trim($tmp_arr[0])."|".trim($tmp_arr[1]);
    $T_pep_arr = explode("|", $tmp_arr[3]);
    if(!array_key_exists($RESULT_key, $RESULT_arr)){
      $RESULT_arr[$RESULT_key]['T_pep'] = $T_pep_arr;
    }else{
      echo $RESULT_key."<br>";
    }
  }

  if(!$input_handle = fopen($input_file, 'r')){
    echo "Cannot open file ($input_file)";
    exit;
  }
  while(($buffer = fgets($input_handle, 4096)) !== false){
    $tmp_arr = explode("\t", $buffer);
    $RESULT_key = trim($tmp_arr[1])."|".trim($tmp_arr[2]);
    $pep_arr = array();
    if(array_key_exists($RESULT_key, $RESULT_arr)){
      $T_pep_arr = $RESULT_arr[$RESULT_key]['T_pep'];
      if(!array_key_exists('pep', $RESULT_arr[$RESULT_key])){
        $RESULT_arr[$RESULT_key]['pep'] = array();
      }
      for($i=0;$i<count($T_pep_arr);$i++){
        if($T_pep_arr[$i] == $tmp_arr[3] && !array_key_exists($i, $RESULT_arr[$RESULT_key]['pep'])){          
          $RESULT_arr[$RESULT_key]['pep'][$i] = trim($tmp_arr[4]);
          break;
        }
      }
    }else{
      //echo "$RESULT_key##########<br>";
    }
  }

  $lines = file($RESULT_file);
  if(!$rewrite_handle = fopen($RESULT_file_tmp, 'w')){
    echo "Cannot open file ($input_file)";
    exit;
  }
  
  $title = trim(array_shift($lines));
  $tile_arr = explode("\t", $title);
  $tile_count = count($tile_arr);
  $title = $title."\tUniqueSpec\tUniqueSpecSum\tUniqueAvgSpec\r\n";
  fwrite($rewrite_handle, $title);

  foreach($lines as $line){
    $buffer = trim($line);
    $tmp_arr = explode("\t", $buffer);
    $buffer_count = count($tmp_arr);
    $RESULT_key = trim($tmp_arr[0])."|".trim($tmp_arr[1]);
    $Sum_pep = 0;
    $Avg_pep = '';
    if(array_key_exists($RESULT_key, $RESULT_arr)){
      $t_pep_arr = $RESULT_arr[$RESULT_key]['T_pep'];
      $pep_arr = $RESULT_arr[$RESULT_key]['pep'];
      
      for($j=0;$j<count($t_pep_arr);$j++){
        if(!array_key_exists($j, $pep_arr)){
          $pep_arr[$j] = trim($t_pep_arr[$j]);
        }
        $Sum_pep += intval($pep_arr[$j]);
      }
      $Avg_pep = round($Sum_pep / $j, 2);
      ksort($pep_arr);
    }else{
      //echo "$RESULT_key##########<br>";
    }
    $pep_sub_line = implode("|", $pep_arr)."\t".$Sum_pep."\t".$Avg_pep;
    $re_times = $tile_count - $buffer_count + 1;
    fwrite($rewrite_handle, $buffer.str_repeat("\t",$re_times).$pep_sub_line."\r\n");
  }
  fclose($rewrite_handle);
}
?>
