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

include("./ms_search_header.php");
?>
<script language="javascript">
function trimString(str) {
    var str = this != window? this : str;
    return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
function isNumber(str) {
  for(var position=0; position<str.length; position++){
        var chr = str.charAt(position)
        if ( ( (chr < "0") || (chr > "9") ) && chr != ".")
              return false;
  }
  return true;
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
function submit_form(){
  var theForm = document.form_search;
  var input_id = trimString(theForm.search_ID.value);
  var input_name = trimString(theForm.search_Name.value);
  if(isEmptyStr(input_id) && isEmptyStr(input_name)){
    alert("Both ID and Name can not be empty!");
    return;
  }else if(!isNumber(input_id)){
    alert("The ID should be number(s)!");
    return;
  }
  theForm.submit();
}
</script>
  <form action="./ms_search_task_list.php" method="post" name="form_search" id="form_search">
  <input type="hidden" name="table" value="<?php echo $table;?>">
  <input type="hidden" name="theaction" value="search">
  <table cellspacing="5" cellpadding="1" border="0" width=90%>
    <tr>
      <td align=center colspan=2><br>
       <font face="Arial" size="+1" color="<?php echo $menu_color;?>"><b><?php echo $table;?> Task Finder</b></font>
       <hr width="100%" size="1" noshade>
      </td>
    </tr>
    <tr>
      <td valign=top bgcolor=#cccccc width=45%>
      <table cellspacing="1" cellpadding="0" border="0" width=100%>
        <tr>
          <td colspan="2"><br>
          <b><font color="#FFFFFF">&nbsp;&nbsp;Input information</font></b>
          <hr width="95%" size="1" noshade color=#660066 height=1 align=center>
          </td>
        </tr> 
        <tr>
          <td valign=top colspan="2">&nbsp;<input type="radio" name="search_Whate" value="folder" checked><b>Folder</b></td> 
        </tr>
        <tr>
          <td valign=top colspan="2">&nbsp;<input type="radio" name="search_Whate" value="task"><b>Task</b></td> 
        </tr>
        <tr>
          <td valign=top colspan="2">&nbsp;<input type="radio" name="search_Whate" value="plate"><b>Analyst Plate</b><br>&nbsp;&nbsp;</td> 
        </tr>
        <tr>
            <td><b>&nbsp;&nbsp;ID:</b></td>
            <td><input type="text" name="search_ID" size=18 value=""></td>
        </tr>
        <tr>
            <td><b>&nbsp;&nbsp;Name:</b>&nbsp;&nbsp;</td>
            <td><input type="text" name="search_Name" size=18 value=""></td>
        </tr>
        <tr>
          <td colspan="2"><br>
          <b><font color="#FFFFFF">&nbsp;&nbsp;Notes</font></b>
          <hr width="95%" size="1" noshade color=#660066 height=1 align=center>
          </td>
        </tr>
        <tr>
          <td colspan="2">
          <Ol>
           <li> Select an input source (radio button) then enter source ID and name.<br><br>
           For example: If the Folder button is selected the ID should be the file folder's ID 
           and Name should be the file folder's name.<br>
           You can enter either ID or Name, or both. The Name can be partial name. 
           <li> Select a project or all projects. 
           <li>Click the Find button. 
         </ol>
          </td>
        </tr>  
        </table>
        </td>
        <td align=top bgcolor=#cccccc valign=top><br>
        &nbsp; <b><font color="#FFFFFF">Select a Project</font></b><br>
        <hr width="95%" size="1" noshade color="#660066" align=center>
        <center>
        <?php 
            $SQL = "SELECT `ProjectID` FROM $tableSearchTasks GROUP BY `ProjectID`";
            $projectsForTasks = $managerDB->fetchAll($SQL);
            ?>
  				  <select name="search_Project" size=23">
              <option  value='-5' selected>All Projects                 
  				 	<?php 
            foreach($projectsForTasks as $value){ 
              if(!array_key_exists($value['ProjectID'], $pro_access_ID_Names)) continue;
						  echo  "<option  value='".$value['ProjectID']."'";  
              echo ">(".$value['ProjectID'].') '.$pro_access_ID_Names[$value['ProjectID']]."\n";
            }
  					?>
  				  </select>&nbsp;&nbsp;<br>&nbsp;  
        </center><br>
        </td>
    </tr>
    <tr>
        <td colspan=2 align=center><br>        
        <input type="button" name="frm_lastTask" value="  Find  " onclick="submit_form()">
        </td>
    </tr>
    </table>
    </form>
<?php 
include("./ms_search_footer.php");
?>