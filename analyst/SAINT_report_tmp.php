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

set_time_limit(0);

$theaction = '';
$theProjectName = '';
$order_by = '';
$start_point ='';
$error_msg = '';
$img_msg = '';
$title_lable = '';
$query_string = '';

$frm_user_id = '';
$saint_ID = '';

//-------------------------------------------
require("../common/site_permission.inc.php");
require("common/page_counter_class.php");
include("analyst/common_functions.inc.php");
require("common/common_fun.inc.php");
require_once("msManager/is_dir_file.inc.php");


echo "<pre>";
print_r($request_arr);
echo "</pre>";


define ("RESULTS_PER_PAGE", 100);
define ("MAX_PAGES", 15); //this is max page link to display


$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);

$Log = new Log($PROHITSDB->link);
$bgcolor = $TB_CELL_COLOR;
$bgcolordark = "#c5b781";
$saint_folder = STORAGE_FOLDER."Prohits_Data/SAINT_results/saint_".$saint_ID."/";


//echo $USER->ID;

if($theaction == 'export' and $saint_ID){
  $list = $saint_folder."RESULT/list.txt";  
  $list_tmp = $saint_folder."RESULT/list_tmp.txt";
  $sait_resuts_zip = $saint_folder ."RESULT.zip";
//--------------------------------------------------------------------------  
  $log_file = $saint_folder."log.dat";
echo "\$log_file=$log_file<br>";
  $SearchEngine = '';
  if(_is_file($log_file)){
    if($log_handle = fopen($log_file, 'r')){
      while(($buffer = fgets($log_handle)) !== false){
        if(preg_match('/^SearchEngine(.+)/', trim($buffer), $matches)){
          if(stristr($matches[1], 'GeneLevel')){
            $SearchEngine = 'GeneLevel';
            break;
          }
        }
      }
      fclose($log_handle);
    }
  } 
  
echo "\$SearchEngine=$SearchEngine<br>";  
   
  
  if(is_file($list_tmp)){
    unlink($list_tmp);
  }
  
  if(_is_file($list) && !_is_file($list_tmp)){
    $gene_UniProt_arr = array();
    $NEW_gene_UniProt_arr = array();
    //----------------------------------------------------------------------
    $create_new_gene_UniProt_file_flag = 0;
    $SAINT_comparison_dir = "../TMP/SAINT_comparison/";
    if(!_is_dir($SAINT_comparison_dir)){
      _mkdir_path($SAINT_comparison_dir);
    }
    if($SearchEngine == 'GeneLevel'){ 
      $gene_UniProt_arr_file = $SAINT_comparison_dir."gene_UniProt_arr.txt";
    }else{
      $gene_UniProt_arr_file = $SAINT_comparison_dir."acc_UniProt_arr.txt";
    }
    if(!_is_file($gene_UniProt_arr_file)){
      $create_new_gene_UniProt_file_flag = 1;
    }else{
      if($status_handle = fopen($download_status_file, 'r')){
        while(($buffer = fgets($status_handle)) !== false){
          if(preg_match('/^processed_date_HUMAN_9606_idmapping_selected.tab=(.+)/', trim($buffer), $matches)){
            $uniProt_file_processed_time = $matches[1];
            $gene_UniProt_arr_file_mtime = $date("Y-m-d H:i:s", filemtime($gene_UniProt_arr_file));
            if(strcmp($uniProt_file_processed_time, $gene_UniProt_arr_file_mtime) > 0){
              $create_new_gene_UniProt_file_flag = 1;
              break;
            }
          }
        }
        fclose($status_handle);
      }
    }
    if(!$create_new_gene_UniProt_file_flag){
      $gene_UniProt_handle = fopen($gene_UniProt_arr_file, 'r');
      if($gene_UniProt_handle){
        while(($buffer = fgets($gene_UniProt_handle)) !== false){
          $buffer = trim($buffer);
          $tmp_arr = explode(',',$buffer);
          $gene_UniProt_arr[$tmp_arr[0]] = $tmp_arr[1];
        }
      }
    }else{
      if(_is_file($gene_UniProt_arr_file)){
        unlink($gene_UniProt_arr_file);
      }
    }
echo "\$create_new_gene_UniProt_file_flag=$create_new_gene_UniProt_file_flag<br>";    
    

  //------------------------------------------------------------------------
  
  
    add_uniq_pep();
    
echo "<pre>";    
print_r($gene_UniProt_arr); 
print_r($NEW_gene_UniProt_arr);   
echo "</pre>";
exit;



    
    if(_is_file($sait_resuts_zip)){
      unlink($sait_resuts_zip);
    }
  }
  if(!_is_file($sait_resuts_zip)){
   if(_is_dir($saint_folder ."RESULT")){
    //chdir($saint_folder);
    $cmd = "cd $saint_folder; zip RESULT.zip RESULT/* 2>&1;";
    $result = @exec($cmd);
	
    if(!is_file($sait_resuts_zip)){
	    echo "$result<br>";
      echo  "Can not create a zip file now in $saint_folder.";
      exit;
    }
   }else{
     echo  "no SAINT results found in $saint_folder";
     exit;
   }
  }

  header("Cache-Control: public, must-revalidate");
  header("Content-Type: application/zip");  //download-to-disk dialog
  //header("Content-Disposition: attachment; filename=".basename($sait_resuts_zip).";" );
  header("Content-Disposition: attachment; filename=saint_results_ID_".$saint_ID.".zip" );
  header("Content-Transfer-Encoding: binary");
  header("Content-Length:"._filesize($sait_resuts_zip));
  header("Expires: 0");
  ob_clean();
  readfile("$sait_resuts_zip");
  exit();
}else if($theaction == 'delete' and $saint_ID){
  $SQL = "update SAINT_log set Status='deleted' where ID='$saint_ID' and UserID='".$USER->ID."'";
  $PROHITSDB->update($SQL);
  $Desc = "";
  $Log->insert($AccessUserID,'SAINT_log',$saint_ID,'deleted',$Desc,$AccessProjectID);
}
//page counter start here----------------------------------------------
$SQL = "SELECT COUNT(ID) AS Total_records FROM SAINT_log 
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
$SQL = "SELECT ID, `Name`,`UserID`, `Date` , `Description`, `Status` , `ProjectID`, `ParentSaintID`, `UserOptions`
  FROM SAINT_log WHERE  ProjectID=$AccessProjectID and Status<>'deleted' ";
if(isset($frm_user_id) && $frm_user_id){
  $SQL .= " AND UserID = $frm_user_id";
}
$SQL .= " ORDER BY $order_by";

$SQL .= " LIMIT $start_point, ".RESULTS_PER_PAGE;
$saint_records = $PROHITSDB->fetchAll($SQL);

$running_saint_arr = get_running_saint();
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
  theForm.action = 'SAINT_report.php';
  theForm.target = '_self';
  theForm.submit();
}

/*function generate_report(result_id,is_uploaded,saint_v){
  var theForm = document.del_form; 
  var currentType = theForm.currentType.value;
  var SearchEngine = theForm.SearchEngine.value;
  var is_uploaded = is_uploaded;
  var start_point = theForm.start_point.value;
  var frm_user_id = theForm.frm_user_id.value;
  var theaction = theForm.theaction.value;
  var saint_ID = result_id;
  var order_by = theForm.order_by.value;
  var title_lable = theForm.title_lable.value;
  file = 'SAINT_comparison_results_table.php'+'?currentType='+currentType+'&SearchEngine='+SearchEngine+'&is_uploaded='+is_uploaded+'&saint_ID='+saint_ID
         +'&is_uploaded='+is_uploaded+'&frm_user_id='+frm_user_id+'&order_by='+order_by+'&title_lable='+title_lable+'&saint_v='+saint_v;
  popwin(file,1100,800);
}*/

function generate_report(result_id,is_uploaded,saint_v){
  var theForm = document.del_form; 
  var currentType = theForm.currentType.value;
  var SearchEngine = theForm.SearchEngine.value;
  var is_uploaded = is_uploaded;
  var start_point = theForm.start_point.value;
  var frm_user_id = theForm.frm_user_id.value;
  var theaction = theForm.theaction.value;
  var saint_ID = result_id;
  var order_by = theForm.order_by.value;
  var title_lable = theForm.title_lable.value;
  <?php if($USER->ID == '348'){?>
    file = 'SAINT_comparison_results_table_newVERSION.php';
  <?php }else{?>
    file = 'SAINT_comparison_results_table.php';
  <?php }?>
  file = file+'?currentType='+currentType+'&SearchEngine='+SearchEngine+'&is_uploaded='+is_uploaded+'&saint_ID='+saint_ID
         +'&is_uploaded='+is_uploaded+'&frm_user_id='+frm_user_id+'&order_by='+order_by+'&title_lable='+title_lable+'&saint_v='+saint_v;
  popwin(file,1100,800);
}

function saint_input_files(theID){
  file = 'SAINT_input_files.pop.php?saint_ID=' + theID;
  popwin(file,600,600);
}
function run_saint(theID, theOptions){
  file = 'export_SAINT_file.php?theaction=re_run_saint&saint_ID=' + theID + '&other_option=' + theOptions;
  popwin(file,800,600);
}
function export_saint_results(theID){
  document.location = '<?php echo $PHP_SELF;?>?theaction=export&saint_ID=' + theID;
}
function saint_delete(theID){
  var theForm = document.del_form;
  theForm.action = '<?php echo $PHP_SELF;?>';
  theForm.target = '_self';
  theForm.saint_ID.value = theID;
  theForm.theaction.value = 'delete';
  if(confirm("Are you sure that you want to delete the SAINT results?")){
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
</script>
<form name="del_form" method=post action="<?php echo $PHP_SELF;?>">
<INPUT TYPE="hidden" NAME="currentType" VALUE="SAINT">
<INPUT TYPE="hidden" NAME="SearchEngine" VALUE="">
<INPUT TYPE="hidden" NAME="is_uploaded" VALUE="">
<table border="0" cellpadding="0" cellspacing="0" width="95%">
  <tr>
    <td align="left" NOWRAP><br>
    &nbsp; <font color="navy"  face="helvetica,arial,futura" size="5"><b><?php echo ($title_lable)?$title_lable:"SAINT Report";?> </b></font>
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
    <td align="right" NOWRAP>
    </td>
  </tr>
  <tr>
    <td colspan=3 height=1 bgcolor="black"><img src="images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="center" colspan=5 valign=top>
  <table border="0" cellpadding="0" cellspacing="1" width="900">
  <input type=hidden name=theaction value="<?php echo $theaction?>">  
  <input type=hidden name=saint_ID value=""> 
  <input type=hidden name=order_by value='<?php echo $order_by;?>'>
  <input type=hidden name=title_lable value='<?php echo $title_lable;?>'>

  <tr>
    <td colspan=7 align=right>
    <a href="javascript: popwin('../logs/saint.log', 800, 700);" class=button>[SAINT Log]</a>&nbsp; &nbsp; &nbsp; 
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
     <a href="javascript: sortList('<?php echo ($order_by == "Name")? 'Name%20desc':'Name';?>');">SAINT Name</a>&nbsp;
    <?php if($order_by == "Name") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "Name desc") echo "<img src='images/icon_order_down.gif'>";
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
       SAINT<br>version</a>
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
  
  for($i=0; $i < count($saint_records); $i++) {
    $op_div = "sDiv_".$saint_records[$i]['ID'];
?>
    <tr  bgcolor='<?php echo $bgcolor;?>' onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $bgcolor;?>');">
      <td width="" align="left"><div class=maintext>&nbsp;
          <?php echo $saint_records[$i]['ID'];?>&nbsp;
        </div>
      </td>
      <td width="250" align="left">
        <div class=maintext>&nbsp;
          <a href="javascript: showhide('<?php echo $op_div?>','')">
          <?php 
            echo $saint_records[$i]['Name'];
            if($saint_records[$i]['ParentSaintID']){
              echo "(" .$saint_records[$i]['ParentSaintID'].")";
            }
          ?>&nbsp;
          </a>
          
          <DIV id='<?php echo $op_div;?>' STYLE="display: none" class=maintext>
            <table border="0"  cellpadding="0" cellspacing="0" width=90% bgcolor=#cccccc align=center>
              <tr>
              <td>
               <?php 
               echo str_replace(",","<br>", $saint_records[$i]['UserOptions']);
               echo "<br>\n";
               echo nl2br($saint_records[$i]['Description']);
               ?>
              </td>
              </tr>
            </table>
         </DIV>
        </div>
      </td>
          <?php 
          $status_color = '';
          if(isset($running_saint_arr[$saint_records[$i]['ID']])){
            $status_color = ' bgcolor=green';
          }
          ?>
      <td width="" align="left"<?php echo $status_color?>><div class=maintext>&nbsp;
          <?php 
          echo $saint_records[$i]['Status'];
          ?>&nbsp;
        </div>
      </td>
      <td width="" align="center"><div class=maintext>&nbsp;
          <?php  echo get_userName($PROHITSDB, $saint_records[$i]['UserID']);?>&nbsp;
        </div>
      </td>
      <td width="" align="center"><div class=maintext>&nbsp;
          <?php 
          if(preg_match("/Version:([^,]+)/",$saint_records[$i]['UserOptions'], $matches)){
            $SAINT_v = $matches[1];
            echo $matches[1];
          }else{
            $SAINT_v = '';
          }
          ?> 
        </div>
      </td> 
      <td width="" align="center"><div class=maintext>&nbsp;
          <?php echo $saint_records[$i]['Date'];?>&nbsp;
        </div>
      </td>
      <td width="" align="left"><div class=maintext>&nbsp; &nbsp; 
      <?php 
      if($saint_records[$i]['UserID'] == $USER->ID and !isset($running_saint_arr[$saint_records[$i]['ID']])){
        echo "<a href=\"javascript: saint_delete('".$saint_records[$i]['ID']."')\" class=sTitle title='delete'>
        <img src='./images/icon_purge.gif' border=0></a>\n";
        if(!$saint_records[$i]['ParentSaintID'] or $saint_records[$i]['Status'] != 'Finished'){
          echo "<a href=\"javascript: run_saint('".$saint_records[$i]['ID']."','".$saint_records[$i]['UserOptions']."' )\" class=sTitle title='re-run SAINT'>
          <img src=\"./images/icon_process.png\" border=0></a>";
        }else{
          echo "<img src=\"./images/icon_empty.gif\" border=0>";
        }
      }else{
        echo "<img src=\"./images/icon_empty.gif\" border=0>&nbsp;";
        echo "<img src=\"./images/icon_empty.gif\" border=0> "; 
      }
      ?>&nbsp;&nbsp;
      <a href="javascript: saint_input_files('<?php echo $saint_records[$i]['ID'];?>')" class=sTitle title='SAINT input files'>
        <img src="./images/icon_view.gif" border=0></a>
      <?php if($saint_records[$i]['Status'] == 'Finished'){
          if(preg_match('/\((uploaded)\)/', $saint_records[$i]['Name'], $matches)){
            $is_uploaded = 'y';
          }else{
            $is_uploaded = '';
          }
          echo "<a href=\"javascript: generate_report('".$saint_records[$i]['ID']."','$is_uploaded','$SAINT_v')\" class=sTitle title='SAINT results'>";
          echo "<img src=\"./images/icon_report.gif\" border=0>";
          echo "</a>&nbsp;&nbsp;";
          echo "****<a href=\"javascript: export_saint_results('".$saint_records[$i]['ID']."')\" class=sTitle title='download SAINT results'>";
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

function add_uniq_pep(){
  global $saint_folder;
  global $SearchEngine;
  $RESULT_file = $saint_folder."RESULT/list.txt";
  $input_file = $saint_folder."inter.dat";  
  $RESULT_file_tmp = $saint_folder."RESULT/list_tmp.txt";
  $prey_file = $saint_folder."prey.dat";
  
  
    
  $prey_len_arr = array();
  if(_is_file($prey_file)){
    if($prey_handle = fopen($prey_file, 'r')){
      while(($buffer = fgets($prey_handle)) !== false){
        $tmp_arr = explode("\t", $buffer);
        $prey_len_arr[$tmp_arr[0]] = $tmp_arr[1];
      }
    }
  }
  if(!$RESULT_handle = fopen($RESULT_file, 'r')){
    echo "Cannot open file ($RESULT_file)";
    exit;
  }
  $RESULT_arr = array();
  while(($buffer = fgets($RESULT_handle)) !== false){
    $tmp_arr = explode("\t", $buffer);
    $RESULT_key = trim($tmp_arr[0])."|".trim($tmp_arr[1]);
    $T_pep_arr = explode("|", $tmp_arr[3]);
    if(!array_key_exists($RESULT_key, $RESULT_arr)){
      $RESULT_arr[$RESULT_key]['T_pep'] = $T_pep_arr;
    }else{
      //echo $RESULT_key."<br>";
    }
  }

  if(!$input_handle = fopen($input_file, 'r')){
    echo "Cannot open file ($input_file)";
    exit;
  }
  while(($buffer = fgets($input_handle)) !== false){
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
    
  $title = $title."\tUniqueSpec\tUniqueSpecSum\tUniqueAvgSpec\tPreySequenceLength\tUniProtID\r\n";

  fwrite($rewrite_handle, $title);
//$tmp_counter = 0;
  foreach($lines as $line){
    $uniprotID = '';
    $buffer = trim($line);
    $tmp_arr = explode("\t", $buffer);
//-----------------------------------------------------
/*$tmp_counter++;
if($tmp_counter > 100){
  exit;
}*/
    $accID = $tmp_arr[1];
    $geneName = $tmp_arr[2];
    
    
    
    $uniprotID = get_uniProt_ID($accID,$geneName,$SearchEngine);
    
    
    
    
    
    
//----------------------------------------------------- 
    $buffer_count = count($tmp_arr);
    $RESULT_key = trim($tmp_arr[0])."|".trim($tmp_arr[1]);
    
    if(array_key_exists($tmp_arr[1], $prey_len_arr)){
      $Pep_len = $prey_len_arr[$tmp_arr[1]];
    }else{
      $Pep_len = '';
    }
    
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
    $pep_sub_line = implode("|", $pep_arr)."\t".$Sum_pep."\t".$Avg_pep."\t".$Pep_len."\t".$uniprotID;
    $re_times = $tile_count - $buffer_count + 1;
    fwrite($rewrite_handle, $buffer.str_repeat("\t",$re_times).$pep_sub_line."\r\n");
  }
  fclose($rewrite_handle);
}
?>

