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
$frm_lastlgin = '';
$frm_selected_user_IDs = '';
$frm_selected_user_names = '';
$frm_order_by = 'Fname ASC';
$Selectedusers = array();
$theaction = '';
$bg_tb_header = '#7eb48e';
$bg_tb = '#cee3da';

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}
 
$PHP_SELF = $_SERVER['PHP_SELF'];

require("../config/conf.inc.php");
include("../common/mysqlDB_class.php");
$mainDB = new mysqlDB(PROHITS_DB);


if($theaction == 'adduser' and $frm_userList){
  //if($frm_selected_user_IDs) $frm_selected_user_IDs .= ',';
  //$frm_selected_user_IDs .= $frm_userList;
}

$frm_selected_user_names = '';
$frm_selected_user_address = '';

if($frm_selected_user_IDs){
  $SQL = "SELECT ID,
                 Fname,
                 Lname,
                 Email
          FROM User WHERE ID IN($frm_selected_user_IDs)
          ORDER BY $frm_order_by";
  $Selectedusers = $mainDB->fetchAll($SQL);
  for($i=0; $i<count($Selectedusers); $i++){
    if($frm_selected_user_names){
      $frm_selected_user_names .= ';';
      $frm_selected_user_address  .= '; ';
    }
    $frm_selected_user_names .= $Selectedusers[$i]['Fname'].' '.$Selectedusers[$i]['Lname'];
    if(!strstr($Selectedusers[$i]['Email'], "@")){
       $Selectedusers[$i]['Email'] .= "@mshri.on.ca";
    }
    $frm_selected_user_address .= $Selectedusers[$i]['Email'];
  }
}
$SQL = "SELECT User.ID,
               Fname,
               Lname,
               Name
        FROM User, Lab where User.LabID=Lab.ID and User.Active=1";
if($frm_lastlgin){
  $SQL .= " and User.LastLogin > '$frm_lastlgin'";
}
if($frm_selected_user_IDs){
  $SQL .= " and User.ID NOT IN($frm_selected_user_IDs)";
}
$SQL .= " ORDER BY LabID, $frm_order_by";
$users = $mainDB->fetchAll($SQL);

//echo $frm_selected_user_names;
?>
<script language="javascript">
function changeOrderBy(){
  theForm = document.form_selectUsers;  
  //alert(theForm.frm_order_by.value);
  theForm.submit();
}

function adduser(){
  str = '';
  theForm = document.form_selectUsers;
  selObj = theForm.frm_userList;
  selected_IDs = theForm.frm_selected_user_IDs;
  //alert(selObj.value);
  if(selObj.selectedIndex < 1){
    alert('Please select a user to add from user list box!');
    return 0;
  }
  for (i=0; i < selObj.options.length; i++) {
    if(selObj.options[i].selected && selObj.options[i].value.length > 0){
       
      if(str.length > 0){
        str = str + ',';
      }
      str = str + selObj.options[i].value;
    }
  }
  if(str.length == 0) return 0;
  if(selected_IDs.value.length > 0){
    selected_IDs.value = selected_IDs.value  + ',';
  }
  selected_IDs.value = selected_IDs.value + str;
   
  theForm.theaction.value = 'adduser';
  theForm.submit();
}
function removeuser(){
  theForm = document.form_selectUsers;
  selObj = theForm.frm_selected_user;
  //alert(selObj.value);
  if(selObj.selectedIndex < 1){
    alert('Please select a user to remove from the selected user box!');
    return 0;
  }
  createSelecteduserStr(theForm);
  theForm.theaction.value = 'removeuser';
  theForm.submit();
}
function createSelecteduserStr(theForm){
  //don't include the selected one
  var str = '';
  var selObj;
  selObj = theForm.frm_selected_user;
  for (i=0; i < selObj.options.length; i++) {
    if(!selObj.options[i].selected){
      if(str.length > 0){
        str = str + ',';
      }
      str = str + selObj.options[i].value;
    }
  }
  theForm.frm_selected_user_IDs.value = str;
  //alert(theForm.frm_selected_user_IDs.value);
}
function passUsers(){ 
    opener.document.forms[0].frm_UserStr.value = "<?php echo $frm_selected_user_address?>";
    opener.document.forms[0].frm_UserIDs.value = "<?php echo $frm_selected_user_IDs;?>"; 
    //window.close(); 
} 

</script>
<FORM ACTION="<?php echo $PHP_SELF;?>" NAME="form_selectUsers" METHOD="POST">
<INPUT TYPE="hidden" NAME="theaction" VALUE="">
<INPUT TYPE="hidden" NAME="frm_selected_user_IDs" VALUE="<?php echo $frm_selected_user_IDs?>">
<INPUT TYPE="hidden" NAME="frm_selected_user_names" VALUE="<?php echo $frm_selected_user_names?>">
<center>
<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td colspan=2>&nbsp;
    </td>
  </tr>
  <tr>
    <td align="left" colspan=2>
    &nbsp; <font color="navy" face="helvetica,arial,futura" size="4"><b>Select Prohits Users</b></font> 
    </td>
  </tr>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align=center colspan=2><br>   
   
    <table border="0" width="600" height="50" cellspacing="1" cellpadding=3 >
    <tr>
      <td width="41%" BGCOLOR="<?php echo $TB_CELL_COLOR;?>" align=center>
      <font size="2" face="Arial"><b>user List</b><br>
       <select name="frm_userList" size=20 multiple="multiple">>
         <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
        <?php 
         $previousLabName = '';
         for($i=0; $i<count($users); $i++){
           if($previousLabName != $users[$i]['Name']){
            $previousLabName = $users[$i]['Name'];
            echo "<option value=''>----------".$previousLabName."---------\n";
           }
           echo "<option value='".$users[$i]['ID']."'>".$users[$i]['Fname']."&nbsp; &nbsp;".$users[$i]['Lname']."\n";
           
         }
        ?>
       </select><br><br>
       
       <b>Loged in Prohits after</b><br><input type=text name='frm_lastlgin' value='<?php echo $frm_lastlgin;?>' size=12 onChange="changeOrderBy()">yyyy-mm-dd
      <br>
       <b>Sort by:</b>
       <input type=radio name='frm_order_by' value='Fname ASC' <?php echo ($frm_order_by=='Fname ASC')?'checked':'';?> onClick="changeOrderBy()">ASC&nbsp;&nbsp;
       <input type=radio name='frm_order_by' value='Fname DESC' <?php echo ($frm_order_by=='Fname DESC')?'checked':'';?> onClick="changeOrderBy()">DESC       
       <br>
       <br>
      </td>
      <td width="18%"  BGCOLOR="<?php echo $TB_CELL_COLOR;?>" valign=center>
      <font size="2" face="Arial">
      <center>
      
      <input type=button value='&nbsp;&nbsp;   > >  &nbsp;&nbsp;' onClick="adduser()">
      <br><br>
      <input type=button value='&nbsp;&nbsp;   < <  &nbsp;&nbsp;' onClick="removeuser()">
      </center>
      </font> 
      </td>
      <td width="41%" BGCOLOR="<?php echo $TB_CELL_COLOR;?>" align=center valign=top>
      <font size="2" face="Arial"><b>Selected user</b><br>
      <select name="frm_selected_user" size=20 multiple="multiple">
         <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
        <?php 
         for($i=0; $i<count($Selectedusers); $i++){
           echo "<option value='".$Selectedusers[$i]['ID']."'>".$Selectedusers[$i]['Fname']."&nbsp; &nbsp;".$Selectedusers[$i]['Lname']."\n";
         }
        ?>
       </select>
      </td>
    </tr>
    </table><br>
    <input type=button value=' Pass Users ' onClick="passUsers()" class=green_but>
    <input type=button value=' Close ' onClick="window.close();" class=green_but>
    </td>
  </tr>
</table>
</form>


