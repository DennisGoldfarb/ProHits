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

 
$thisPage = 'User manager';
require("classes/lab_class.php");
require("classes/project_class.php");
require("../common/site_permission.inc.php");

require("site_header.php");

static $Title;
static $CurrentSelected;
if (!$CurrentSelected)
   $CurrentSelected = 0;

if ($action) {
    switch ($action) {
        case 'detactive':
          if($uid and $AUTH->delete) {
            $USER->deactivate($uid);
            $User = new User("",$uid);
            $SelectName = $uid;
            $Title = $Title = 'Modify current user';
          }
        break;
        case 'active':
          if($uid) {
            $USER->activate($uid);
            $User = new User("",$uid);
            $SelectName = $uid;
            $Title = $Title = 'Modify current user';
          }
        break;
        case 'Add':
            $User = new User ();
            $User->init();
            $Title = 'Add a new user';
            break;
        case 'Modify':
            $User = new User ("",$SelectName);
            $CurrentSelected = $SelectName;
            $Title = 'Modify current user';
            
            break;
        case 'AddUpdateNow':
            // Add or update user info
            if ($subaction != "Add" and $AUTH->modify) {
                $tmpUser = new User($UserID);
                $tmpUser->update($UserID, $UserName, $password1, $FirstName, $LastName, $ContactPhone, $ContactEmail, $type, $labID);
            } else if($AUTH->insert){
        
                $tmpUser = new User($UserID);
                $tmpUser->insert($UserName, $password1, $FirstName, $LastName, $ContactPhone, $ContactEmail, $type, $labID);
            }                    

            $UserID = $tmpUser->id;            
            $CurrentSelected = $SelectName = $UserID;
            // update permissions
            // first delete all permissions belong to this user
            $SQL = 'delete from bo_permissions '.
                   'where bo_user_id = "'.$UserID.'"';
            mysqli_query($PROHITSDB->link, $SQL);
            
            // then insert new permissions
            $SQL = 'select * from bo_pages';
            $sqlResult = mysqli_query($PROHITSDB->link, $SQL);
            //echo $projectString;
            $tmpArr = explode(',',$projectString);
            mysqli_query($PROHITSDB->link, "delete from ProPermissions where UserID='$UserID'");
            for($i=0; $i<count($tmpArr); $i++){
              $SQL = "insert into ProPermissions set ProID='$tmpArr[$i]', UserID='$UserID'\n";
              mysqli_query($PROHITSDB->link, $SQL);
            }
            while ($row = mysqli_fetch_array($sqlResult)) {
                $aVar1 ='access'.$row['id'];
                $aVar2 ='insert'.$row['id'];
                $aVar3 ='modify'.$row['id'];
                $aVar4 ='delete'.$row['id'];
                
                // if access permission was set
                if ( $$aVar1 ) {
                    $SQL = 'insert into bo_permissions values("'.
                           $UserID.'", "'.
                           $row['id'].'" ';
                    if ( $$aVar2 ) {
                        $SQL .= ', "1"';
                    } else {
                        $SQL .= ', "0"';
                    }
                    if ( $$aVar3 ) {
                        $SQL .= ', "1"';
                    } else {
                        $SQL .= ', "0"';
                    }
                    if ( $$aVar4 ) {
                        $SQL .= ', "1"';
                    } else {
                        $SQL .= ', "0"';
                    }
                    $SQL .= ')';
              mysqli_query($PROHITSDB->link, $SQL);
                }       
            }
            
            $Title = 'Add/Update sucessfully';
            $User = new User ("",$CurrentSelected);
            $action = 'Modify';
          break;
        default:
            $User = new User ("",$SelectName);
            $CurrentSelected = $SelectName;
            $Title = 'Modify current user';
        break;
    }
}

?>

<SCRIPT language=JavaScript>
<!--
function Check_Password(theForm)
{
    if (theForm.UserName.value == "") {
        alert("Please input user name.");
        theForm.UserName.focus();
        return (false);
    }
  if (theForm.type.value == "") {
        alert("Please select user type.");
        theForm.type.focus();
        return (false);
    }
    if (theForm.ContactEmail.value == "") {
        alert("Please input email address.");
        theForm.UserName.focus();
        return (false);
    } 
    if (theForm.password1.value.length < 4 || theForm.password1.value.length > 14)  {
        alert("The password you select must be between 4 and 14 characters.");
        theForm.password1.focus();
        return (false);
    } 

    if (theForm.password1.value != theForm.password2.value)  {
  alert("The passwords that you typed in do not agree with one another. Please try again");
  theForm.password1.focus();
  return (false);
  }
    var List = theForm.frm_projectID;
    var str = '';
    var first = true;
    var sel;
    for (i=0;i<List.options.length;i++){
      var current = List.options[i];
      if (current.selected){
          sel = true;
          val = current.value;
		      if(first){
		        str = val;
		        first = false;
		      }else{
			       str = str + ","+val;
		      } 
	     }
    }
    theForm.projectString.value = str;
    theForm.submit();
}

-->
</SCRIPT>
<br>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr><td valign="top" align=center>
  <form method=post>
  <input type="hidden" name="SID" value="<?php echo $SID;?>">
  <input type="hidden" name="action" value="Modify"> 
  

  <table border="0" width=250>
  <TBODY>
  <tr> 
    <td align="middle">
      <font color="" face="helvetica,arial,futura" size="4"><b>User Admin</b></font>
    </td>
   </tr>
   <tr><td align="middle" valign="top" bgcolor="#e5e5e5">
        <select name="SelectName" size="20" length="20">
        <?php
           $AllUsers = new User();
           $AllUsers->fetchall();
           for ($i= 0;$i < $AllUsers->count;$i++) { 
                if(!$started_inactive_user and !$AllUsers->active[$i]){
                    $started_inactive_user = 1;
                     echo '<option value=""><option value="">----Following users are inactive ---';
                }
                if($AllUsers->id[$i]==$CurrentSelected) {
                  echo '<option selected value="'.$AllUsers->id[$i].'">'.$AllUsers->fname[$i]." ". $AllUsers->lname[$i]."\n"; 
                } else {
                  echo '<option value="'.$AllUsers->id[$i].'">'.$AllUsers->fname[$i]." ". $AllUsers->lname[$i]."\n"; ; } 
                } 
      ?>
      </option>
      </select>
    </td></tr>
  
    <tr><td align="middle" bgcolor="#e5e5e5">
<?php 
  if ( $AUTH->modify ) echo '<input type="submit" value="Modify">';
        if ( $AUTH->modify && $AUTH->insert ) echo ' or ';
  if ( $AUTH->insert ) echo '<a href="users.php?SID='.$SID.'&action=Add">Add User</a>';
?>  
      
    </td></tr></tbody></table></form>

    </td>
    <td valign="top">
  <table border="0">
    <TBODY>
    <tr><td align="middle">
        <font color="" face="helvetica,arial,futura" size="4"><b>
        <?php 
          if ($Title) {
             echo $Title;
          } else { echo "User Admin"; }
        ?>    
        </b></font>
        </td></tr>
        <tr><td rowspan=3 valign=top align=middle>
          <font color="black" face="helvetica,arial,futura" size="6"><b>
          <TABLE align=center border=0 cellPadding=2 cellSpacing=2>
          <FORM action="<?php echo $PHP_SELF; ?>" method=post name=Form>
          <?php 
          if ( $action == 'Add' ) {
             echo '<input name=subaction type=hidden value="Add">'."\n";
      }
           //  echo '<input name=SID type=hidden value="'.$SID.'">';
           ?>
          <INPUT name=action type=hidden value=AddUpdateNow> 
          <INPUT name=SID type=hidden value="<?php echo $SID;?>"> 
          <INPUT name=UserID type=hidden value="<?php echo $CurrentSelected;?>"> 
          <input type="hidden" name="projectString" value=''>
          <TBODY>
<?php if($SelectName != "" AND $AUTH->delete) {?>
        <TR>
          <TD bgColor=#e5e5e5 colspan=2 align=center>
      <FONT color=black face=helvetica,arial,futura size=2>
       <?php if($User->active){ ?>
        To deactivate this user, click here ->&nbsp
        <a href="users.php?SID=<?php echo $SID;?>&action=detactive&uid=<?php echo $User->id;?>">
        <img src="images/icon_active.gif" border=0></a>
       <?php }else{?>
         To activate this user, click here ->&nbsp
        <a href="users.php?SID=<?php echo $SID;?>&action=active&uid=<?php echo $User->id;?>">
        <img src="images/icon_notactive.gif" border=0></a>
       <?php }?>
      </FONT>
    </TD>
  </TR>
<?php }?>
        <TR>
        <TR>
          <TD align=right bgColor=#e5e5e5><FONT color=black 
            face=helvetica,arial,futura size=2>User ID.:</FONT> </TD>
          <TD bgColor=#e5e5e5><FONT color=black face=helvetica,arial,futura 
            size=2><?php echo $User->id; ?>&nbsp;</FONT> </TD></TR>
        <TR>
          <TD align=right bgColor=#e5e5e5><FONT color=black 
            face=helvetica,arial,futura size=2><B>User Name:</B></FONT> </TD>
          <TD bgColor=#e5e5e5><INPUT name=UserName size=24 
            value="<?php echo $User->username; ?>"></TD></TR>
        <TR>
          <TD align=right bgColor=#e5e5e5><FONT color=black 
            face=helvetica,arial,futura size=2><B>Password:</B></FONT> </TD>
          <TD bgColor=#e5e5e5><INPUT name=password1 size=24 type=password 
            value="<?php echo $User->password; ?>"></TD></TR>
        <TR>
          <TD align=right bgColor=#e5e5e5><FONT color=black 
            face=helvetica,arial,futura size=2><B>Password(re-type):</B></FONT> 
          </TD>
          <TD bgColor=#e5e5e5><INPUT name=password2 size=24 type=password
            value="<?php echo $User->password; ?>"></TD></TR>
        <TR>
          <TD align=right bgColor=#e5e5e5><FONT color=black 
            face=helvetica,arial,futura size=2>First Name:</FONT> </TD>
          <TD bgColor=#e5e5e5><INPUT name=FirstName size=24 
            value="<?php echo $User->fname; ?>"></TD></TR>
        <TR>
          <TD align=right bgColor=#e5e5e5><FONT color=black 
            face=helvetica,arial,futura size=2>Last Name:</FONT> </TD>
          <TD bgColor=#e5e5e5><INPUT name=LastName size=24 
            value="<?php echo $User->lname; ?>"></TD></TR>
        <TR>
          <TD align=right bgColor=#e5e5e5><FONT color=black 
            face=helvetica,arial,futura size=2>Contact Phone #:</FONT> </TD>
          <TD bgColor=#e5e5e5><INPUT name=ContactPhone size=15 maxlength=15 
            value="<?php echo $User->contact_phone; ?>"></TD></TR>
        <TR>
          <TD align=right bgColor=#e5e5e5><FONT color=black 
            face=helvetica,arial,futura size=2><b>Contact E-mail:</b></FONT> </TD>
          <TD bgColor=#e5e5e5><INPUT name=ContactEmail size=24 value="<?php echo $User->contact_email; ?>"></TD></TR>
        <TR>
          <TD align=right bgColor=#e5e5e5><FONT color=black 
            face=helvetica,arial,futura size=2><b>User Type:</b></FONT> </TD>
          <TD bgColor=#e5e5e5>
        <select name="type">
        
        <option <?php  echo (!$User->type)? "selected ":""; ?> value=""> -- select user type --
        <option <?php  echo ($User->type == "user")? "selected ":""; ?> value="user">User
        <option <?php  echo ($User->type == "supervisor")? "selected ":""; ?> value="supervisor">Supervisor
        <option <?php  echo ($User->type == "MSTech")? "selected ":""; ?> value="MSTech">MS Specialist
        <option <?php  echo ($User->type == "labTech")? "selected ":""; ?> value="labTech">Lab MS Specialist
      </select>
      </TD></TR>
        <TR>
          <TD align=right bgColor=#e5e5e5><FONT color=black 
            face=helvetica,arial,futura size=2>Lab Name:</FONT> </TD>
          <TD bgColor=#e5e5e5> 
        <select name="labID">
        <option <?php  echo (!$User->labID)? "selected ":""; ?> value=""> -- Not in any Lab --
        <?php
        $Labs = new Lab();
        $Labs->fetchall();
        for ($i= 0;$i < $Labs->count;$i++) { 
          if($User->labID == $Labs->id[$i]){
                      echo '<option selected value="'.$Labs->id[$i].'">'.$Labs->name[$i]."\n"; 
                  }else{
             echo '<option value="'.$Labs->id[$i].'">'.$Labs->name[$i]."\n"; 
          }
                } 
      ?>
      </select>
      </TD></TR>
    <TR>
          <TD align=right bgColor=#e5e5e5 valign=top><FONT color=black 
            face=helvetica,arial,futura size=2>Access Projects:</FONT> </TD>
          <TD bgColor=#e5e5e5> 
        <select name="frm_projectID" multiple size=3>
        <option value="" > ----- no project ----
        <?php
        $AllProjects = new Projects();
        $AllProjects->fetchall();
        $AccessProjects = new Projects();
        if($User->id){
         $AccessProjects->getAccessProjects($User->id);
        }
        for($i = 0; $i < $AllProjects->count; $i++){
						echo  "<option  value='".$AllProjects->ID[$i]."'";
            if($User->id)
            if(in_array($AllProjects->ID[$i], $AccessProjects->ID)){
              echo " selected";
            }
            echo ">".$AllProjects->Name[$i]."\n";
			  }
       
      ?>
      </select>
      </TD></TR>
      <TR>
         <TD align=right bgColor=#e5e5e5><FONT color=black 
            face=helvetica,arial,futura size=2>User Alias:</FONT> </TD>
          <TD bgColor=#e5e5e5>
          <INPUT name=alias size=30 value='<?php echo $User->alias;?>'>
           e.g. TYE,WRA,PAW,MET</TD>
      </TR>
        <TR>
          <TD align=right bgColor=#e5e5e5><FONT color=black 
            face=helvetica,arial,futura size=2>Last On:</FONT> </TD>
          <TD bgColor=#e5e5e5>
<?php 
$User->last_on = substr($User->last_on, 0, 4)."/".substr($User->last_on, 4, 2)."/".substr($User->last_on, 6, 2).
    "-".substr($User->last_on, 8, 2).":".substr($User->last_on, 10, 2).":".substr($User->last_on, 12, 2);
echo $User->last_on;
?>
</TD></TR>
        <TR>
          <TD align=middle bgColor=#e5e5e5 colSpan=2><FONT color=black 
            face=helvetica,arial,futura size=2><B>Permissions</B></FONT> </TD></TR>
            
        <?php   
        // dislpay permissions for all pages 
        $SQL = 'select * from bo_pages order by id';
        $sqlResult = mysqli_query($PROHITSDB->link, $SQL);
                
        while ($row = mysqli_fetch_array($sqlResult)) {
            // display page name
            echo  '<TR><TD align=right bgColor=#e5e5e5><FONT color=black'. 
                  ' face=helvetica,arial,futura size=2>'.$row['page_name'].
                  '</font></td>';

            // use auth class to check the permission
            $aAuth = new Auth($User->id, $row['id']);
            echo  '<TD bgColor=#e5e5e5><FONT color=black face=helvetica,arial,futura size=2>';
            if ( $aAuth->access ) {
                echo '<INPUT name=access'.$row['id'].' type=checkbox value=1 checked>Access ';
            } else {
                echo '<INPUT name=access'.$row['id'].'  type=checkbox value=1>Access ';
            }
            
            if ( $aAuth->insert ) {
                echo '<INPUT name=insert'.$row['id'].'  type=checkbox value=1 checked>Insert ';
            } else {
                echo '<INPUT name=insert'.$row['id'].'  type=checkbox value=1>Insert ';
            }   

            if ( $aAuth->modify ) {
                echo '<INPUT name=modify'.$row['id'].'  type=checkbox value=1 checked>Modify ';
            } else {
                echo '<INPUT name=modify'.$row['id'].'  type=checkbox value=1>Modify ';
            }   
            
            if ( $aAuth->delete ) {
                echo '<INPUT name=delete'.$row['id'].'  type=checkbox value=1 checked>Delete ';
            } else {
                echo '<INPUT name=delete'.$row['id'].'  type=checkbox value=1>Delete ';
            }   
            echo '</FONT></TD></TR>';
        }
        
        ?>
        <TR>
          <TD align=middle bgColor=#e5e5e5 colSpan=2>
          <?php if($action == 'Add' or $action == 'Modify'){?>
          <INPUT id=button1 name=button1 onclick="return Check_Password(Form)" type=button value=<?php echo $action;?>>
          <?php }?>
        </TD></tr></FORM>
        </table></FONT></A></B>
        </TD></tr></table>
        </TD></tr></table>
        
<br>
<?php 
require("site_footer.php");
?>
