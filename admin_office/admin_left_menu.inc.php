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

?>
<script language="javascript">
function pop_win(url){ 
  newwin = window.open(url,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=830,height=750');
  newwin.moveTo(1,1);
}  
</script>
<?php 

$AccessPages_arr = $_SESSION['USER']->AccessPages_arr;

foreach($AccessPages_arr as $value){
  if($value == 'index.php'){
    echo "<a href=./index.php class='left_menu'>Admin Office Home</a><br><br>";
  }else if($value == 'project.php'){
    echo "<a href=project.php?theaction=viewall class='left_menu'>Project Manager</a><br><br>";
  }else if($value == 'filter.php'){
    echo "<a href=filter.php?selectedSetID=1 class='left_menu'>Filter Manager</a><br><br>";
  }else if($value == 'user.php'){
    echo "<a href=user.php?action=Add&SelectName=0 class='left_menu'>User Manager</a><br><br>";
  }else if($value == 'protein_db_update.php'){
    echo "<a href=protein_db_update.php class='left_menu'>Protein DB Update</a><br><br>";

  }else if($value == 'email_out.php'){ 
    echo "<a href='javascript:pop_win(\"email_out.php?theaction=fillform&sendto=\");' class='left_menu'>Admin Email</a><br><br>";
  }else if($value == 'backup_setup.php' and !DISABLE_RAW_DATA_MANAGEMENT){
    echo "<a href=backup_setup.php class='left_menu'>Backup Setup</a><br><br>";
  }else if($value == 'log_report.php'){
    echo "<a href=log_report.php?theaction=select&frm_allUsers=Y&first=Y class='left_menu'>Log Report</a><br><br>";
  }else if($value == 'split_project.php'){
    echo "<a href='javascript:pop_win(\"./".$value."\");' class='left_menu'>Split Project</a><br><br>";
  }
}
?>
<br>
<a href='javascript:pop_win("iRefindex.php");' class='left_menu'>iRefindex</a><br><br>
<a href='javascript:popwin("add_gene_to_fasta_file.php","700","390");' class='left_menu'>Add gene and decoy to fasta file</a>
<br><br><br><br>
<a href=../common/logout.php class='left_menu'>Logout</a>