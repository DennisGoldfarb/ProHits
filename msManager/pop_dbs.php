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
$frm_myaction = '';
$frm_db = '';
$frm_hide='';
$setID = '';
$hide_db_arr = array();
$db_str = '';
 
include("./ms_permission.inc.php");
require("./common_functions.inc.php");
include("./autoSearch/auto_search_mascot.inc.php");
require("./is_dir_file.inc.php");


$SQL = "select ID, Name, User, ProjectID, Parameters from SearchParameter where Type='Database'";
$Paras_arr = $managerDB->fetch($SQL);
if($Paras_arr){
  $setID = $Paras_arr['ID'];
  $db_str=$Paras_arr['Parameters'];
  $hide_db_arr = explode("\n", $db_str);
   
}
if($USER->Type == 'Admin' and $frm_myaction){
  
    if($setID){
      if($frm_hide){
        $db_str .= "\n". $frm_db;
        $hide_db_arr[] = $frm_db;
      }else{
        $key = array_search($frm_db,$hide_db_arr);
        if($key!==false){
          unset($hide_db_arr[$key]);
          $db_str = implode("\n", $hide_db_arr);
        }
      }
      $SQL = "update SearchParameter set Parameters='$db_str', Date=now() where ID=$setID";
      $managerDB->update($SQL);
    }else{
      if($frm_hide){
        $SQL = "insert into SearchParameter set Name='hidden dbs', Type='Database', User='".$USER->ID."', Parameters='$frm_db', Date=now()";
        $hide_db_arr[] = $frm_db;
        $managerDB->insert($SQL);
      }
    }
   
}
/*
echo "<pre>";
print_r($request_arr);
echo "</pre>";
*/

$gpm_dbs = get_gpm_db_arr();
include("./ms_header_simple.php");
$thegpm_path = dirname(GPM_CGI_PATH);
?>
 
<script language="Javascript" src="../common/javascript/site_javascript.js"></script> 
<script language=javascript>
 
function hideThis(theCheckBox){
  var theForm = theCheckBox.form;
  theForm.frm_db.value = theCheckBox.value;
  if(theCheckBox.checked){
     theForm.frm_hide.value = '1';
  }
  theForm.submit();
}

 
 
</script>

<form name=listform method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=frm_myaction value='yes'>
<input type=hidden name=frm_db value=''>
<input type=hidden name=frm_hide value='0'> 
<table border="0" cellpadding="0" cellspacing="2" width=95%>
  <tr>
   
   <td clspan=2><span class="pop_header_text">List of databases
    
    </span>
    <br>
    <hr width="100%" size="1" noshade>
    
    <li>MASCOT_IP and GPM_IP are defined in Prohits conf file.
    <li>If MASCOT_IP is set, the database list is from both Mascot and GPM (species.js) shared databases. Database names should be the same in both Mascot and GPM.
    <li>If MASCOT_IP is not set, the database list is only from GPM databases.
    <li><?php echo "<a href='http://".$gpm_ip."/tandem/species.js'>".$thegpm_path."/tandem/species.js</a>";?>
    <li><?php echo "<a href='http://".$gpm_ip."/tandem/taxonomy.xml'>".$thegpm_path."/tandem/taxonomy.xml</a>";?>
    <li><?php echo $thegpm_path."/gpm/fasta/</a>";?>
    <li>Manully modify file ./pop_dbs_info.txt after adding new database.
   </td>
   </tr>
  
   <TR>
    <TD align=center bgcolor=#50c5a5 colspan=2><br>
        <table cellspacing="2" cellpadding="2" border="0" width=95%>
      <tr bgcolor="#dfe2f7" >
        
            <td><b>Database</b></td>
            <td><b>Check to hide the db</b></td>
        </tr>
        <?php 
              for($i=0; $i < count($gpm_dbs['label']); $i++){
                $isChecked = '';
                $co = '#ffffff';
                $db_label = $gpm_dbs['label'][$i];
                if(in_array($db_label, $hide_db_arr)){
                  $isChecked = " checked";
                  $co = '#cccccc';
                }
                
                echo "\n<tr bgcolor='$co'><td>$db_label</td>\n";
                echo "<td><input name=$i type=checkbox value='$db_label' onClick='hideThis(this)'$isChecked></td>\n</tr>\n";
              }
        
        ?>
        </table>
    </TD> 
   <tr>
</table>
<input type="button" onclick="window.close()" value=" Save " name="frm_save">   
</form>
<?php
include("./ms_footer_simple.php");

?>
