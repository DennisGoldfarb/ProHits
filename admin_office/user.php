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

$action = "";
$SelectName = "";
$subaction = "";
$labID = "";

require("../analyst/classes/lab_class.php");
require_once("../common/site_permission.inc.php");
require_once("../common/common_fun.inc.php");
include("admin_log_class.php");
include("./admin_header.php");

$AdminLog = new AdminLog();

static $Title;
static $CurrentSelected;
if (!$CurrentSelected)
   $CurrentSelected = 0;
   
$User = "";
$msg= '';

if ($action) {
    switch ($action) {
        case 'detactive':
          if($uid and $AUTH->Delete) {
            $User = new User("",$uid);
            $User->deactivate($uid);
            $Desc = "";
            $AdminLog->insert($AccessUserID,'User',$uid,'deactivate',$Desc);
            $User->fetch($uid);            
            $SelectName = $uid;
            $Title = 'Modify current user';
            //$action = 'Modify';
          }
        break;
        case 'active':
          if($uid) {
            $User = new User("",$uid);
            $User->activate($uid);
            $Desc = "";
            $AdminLog->insert($AccessUserID,'User',$uid,'activate',$Desc);
            $User->fetch($uid);            
            $SelectName = $uid;
            $Title = 'Modify current user';
            //$action = 'Modify';
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
            $tmpUser = "";
            if ($subaction != "Add" and $AUTH->Modify) {           
                $tmpUser = new User($UserID);
                $tmpUser->update($UserID, $UserName, $password1, $FirstName, $LastName, $ContactPhone, $ContactEmail, $type, $labID);
                $Desc = "ID=$UserID,Fname=$FirstName,Lname=$LastName,LabID=$labID";
                $AdminLog->insert($AccessUserID,'User',$tmpUser->ID,'modify',$Desc);
            }else if($AUTH->Insert){
                $tmpUser = new User($UserID);
                $tmpUser->fetch_ID($UserName);
                if($tmpUser->ID){
                  $msg = "<font color=red>The user name has been used. Please change the user name</font>.";
                  $User = new User ();
                  $User->init();
                  $User->Username = $UserName;
                  $Title = 'Add a new user';
                  break;
                  exit;
                }else{
                  $tmpUser->insert($UserName, $password1, $FirstName, $LastName, $ContactPhone, $ContactEmail, $type, $labID);
                  $Desc = "ID=$tmpUser->ID,Fname=$FirstName,Lname=$LastName,LabID=$labID";
                  $AdminLog->insert($AccessUserID,'User',$tmpUser->ID,'insert',$Desc);
                }
            } 
            $UserID = $tmpUser->ID;            
            $CurrentSelected = $SelectName = $UserID;
            // update permissions
            
          
            // first delete all permissions belong to this user
            $SQL = 'delete from ProPermission '.
                   'where UserID = "'.$UserID.'"';
            mysqli_query($PROHITSDB->link, $SQL);
            
            // then insert new permissions
            $SQL = 'select ID, Name from Projects';
            $sqlResult = mysqli_query($PROHITSDB->link, $SQL);
            
            while ($row = mysqli_fetch_array($sqlResult)) {
                $aVar1 ='accessProject'.$row['ID'];
                $aVar2 ='insertProject'.$row['ID'];
                $aVar3 ='modifyProject'.$row['ID'];
                $aVar4 ='deleteProject'.$row['ID'];
               
                // if access permission was set
                if ( isset($$aVar1) ) {
                    $Desc = '';
                    $SQL = 'insert into ProPermission values("'.
                         $row['ID'].'", "'.
                         $UserID.'" ';
                      $Desc .="ProjectID=".$row['ID'].",UserID=$UserID,";     
                    if ( isset($$aVar2) ) {
                        $SQL .= ', "1"';
                        $Desc .="Insert=1";
                    } else {
                        $SQL .= ', "0"';
                        $Desc .="Insert=0";
                    }
                    if ( isset($$aVar3) ) {
                        $SQL .= ', "1"';
                        $Desc .="Modify=1";
                    } else {
                        $SQL .= ', "0"';
                        $Desc .="Modify=0";
                    }
                    if ( isset($$aVar4) ) {
                        $SQL .= ', "1"';
                        $Desc .="Delete=1";
                    } else {
                        $SQL .= ', "0"';
                        $Desc .="Delete=0";
                    }
                    $SQL .= ')';
                    mysqli_query($PROHITSDB->link, $SQL);
                    $AdminLog->insert($AccessUserID,'ProPermission',$row['ID'],'insert',$Desc);
                }       
            }
     
            // first delete all permissions belong to this user
            $SQL = 'delete from PagePermission '.
                   'where UserID = "'.$UserID.'"';
            mysqli_query($PROHITSDB->link, $SQL);
            
            // then insert new permissions
            $SQL = 'select * from Page';
            $sqlResult = mysqli_query($PROHITSDB->link, $SQL);
            
            while ($row = mysqli_fetch_array($sqlResult)) {
                $aVar1 ='accessPage'.$row['ID'];
                $aVar2 ='insertPage'.$row['ID'];
                $aVar3 ='modifyPage'.$row['ID'];
                $aVar4 ='deletePage'.$row['ID'];
                
                // if access permission was set
                if ( isset($$aVar1) ) {
                    $Desc = '';
                    $SQL = 'insert into PagePermission values("'.
                           $row['ID'].'", "'.
                           $UserID.'" ';
                      $Desc .="PageID=".$row['ID'].",UserID=$UserID,";     
                    if ( isset($$aVar2) ) {
                        $SQL .= ', "1"';
                        $Desc .="Insert=1";
                    } else {
                        $SQL .= ', "0"';
                        $Desc .="Insert=0";
                    }
                    if ( isset($$aVar3) ) {
                        $SQL .= ', "1"';
                        $Desc .="Modify=1";
                    } else {
                        $SQL .= ', "0"';
                        $Desc .="Modify=0";
                    }
                    if ( isset($$aVar4) ) {
                        $SQL .= ', "1"';
                        $Desc .="Delete=1";
                    } else {
                        $SQL .= ', "0"';
                        $Desc .="Delete=0";
                    }
                    $SQL .= ')';
                    mysqli_query($PROHITSDB->link, $SQL);
                    
                    $AdminLog->insert($AccessUserID,'PagePermission',$row['ID'],'insert',$Desc);
                }       
            }
         
            $Title = 'Add/Update successfully';
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
        theForm.password1.focus();
        <?php if($User->Password){?>
          if (theForm.password1.value.length > 0){
            alert("The password you select must be between 4 and 14 characters.");
            return (false);
          }
        <?php }else{?>
            alert("The password you select must be between 4 and 14 characters.");
            return (false);
        <?php }?>
    }
     
    var email = theForm.ContactEmail.value;
    theForm.submit();
}

function Check_Modify(theForm)
{
  var x=document.getElementById("userSelect");
  if(x.value == "")
  {
    alert("Please select a user.");
    return false;
  }
  return true;
}  
-->
</SCRIPT>
<br>
<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td align="left">
      &nbsp; <font color="black" face="helvetica,arial,futura" size="3"><b>User Management</b></font>   
    </td>    
  </tr>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td colspan=2 height=1 >&nbsp;</td>
  </tr>   
  <tr>
    <td valign="top" align=center>
      <form method=post>      
      <input type="hidden" name="action" value="Modify">  

      <table border="0" width=250>
      <TBODY>
        <tr> 
          <td align="middle">
            <font color="" face="helvetica,arial,futura" size="4"><b>User Admin</b></font>
          </td>
        </tr>
        <tr>
          <td align="middle" valign="top" bgcolor="#e5e5e5">
        
<?php
           $AllUsers = new User();
           $AllUsers->fetchall();          
          // print_r($AllUsers);
?>
            <select id="userSelect" name="SelectName" size="35" length="20">   
        <?php
          $Lab = new Lab();
          $started_inactive_user = 0;
          $pre_labID = 0;
          for ($i= 0;$i < $AllUsers->count;$i++) {           
	          if(!$started_inactive_user and !$AllUsers->Active[$i]){
              $started_inactive_user = 1;
              $pre_labID = 0;
              echo '<option value=""><option value="">==Following users are inactive =='; 
            }
                
	          if($AllUsers->LabID[$i] != $pre_labID ){
		          $pre_labID = $AllUsers->LabID[$i];
              $Lab->fetch($pre_labID);
              echo '<option value="">';
			        echo '<option value="">-------Lab ' .$pre_labID. ' ('.$Lab->Name.') ------';              
		        }
            
            if($AllUsers->ID[$i]==$CurrentSelected) {
              echo '<option selected value="'.$AllUsers->ID[$i].'">'.$AllUsers->Fname[$i]." ". $AllUsers->Lname[$i]."\n"; 
            } else {
              echo '<option value="'.$AllUsers->ID[$i].'">'.$AllUsers->Fname[$i]." ". $AllUsers->Lname[$i]."\n"; 
            } 
          } 
      ?>
              </option>
            </select>
          </td>
        </tr>
  
        <tr>
          <td align="middle" bgcolor="#e5e5e5">
<?php 
  if ( $AUTH->Modify ) echo '<input type="submit" value="Modify" onclick="return Check_Modify(Form)">';//===========================
        if ( $AUTH->Modify && $AUTH->Insert ) echo ' or ';
  if ( $AUTH->Insert ) echo '<a href="user.php?&action=Add">Add User</a>';
?>  
      
          </td>
        </tr>
      </tbody>
      </table>
      </form>
    </td>
    
    <td valign="top">
   
    <table border="0">
    <TBODY>
      <tr>
        
      <?php 
      if($Title){
        if($Title != 'Add a new user'){
      ?>
        <td align="middle">
          <table border="0" width="100%">
            <tr>
              <td align="right" width="70%">
              <font color="" face="helvetica,arial,futura" size="4"><b><?php echo $Title;?></b></font>
              </td>
              <td align="right"><a href="javascript:pop_win('email_out.php?theaction=fillform&UserID=<?php echo $User->ID;?>&sendto=<?php echo $User->Email?>');">[Email]</a></td>
            </tr>
          </table>
        </td>   
      <?php }else{?>
        <td align="middle">
          <font color="" face="helvetica,arial,futura" size="4"><b><?php echo $Title."</b></font><br>$msg";?>
        </td>
      <?php       
        }
      }else{
      ?>
        <td align="middle">
          <font color="" face="helvetica,arial,futura" size="4"><b><?php echo "User Admin";?></b></font>
        </td>
    <?php }?> 
      </tr>
      <tr>
        <td rowspan=3 valign=top align=middle>
          <font color="black" face="helvetica,arial,futura" size="6"><b>
          <TABLE align=center border=0 cellPadding=2 cellSpacing=2>
          <FORM action="<?php echo $PHP_SELF; ?>" method=post name=Form>
          <?php 
          if ( $action == 'Add' ) {
             echo '<input name=subaction type=hidden value="Add">'."\n";
          }
           ?>
          <INPUT name=action type=hidden value=AddUpdateNow>            
          <INPUT name=UserID type=hidden value="<?php echo $CurrentSelected;?>"> 
          <input type="hidden" name="projectString" value=''>
          <TBODY>
            <?php if($SelectName != 0 AND $AUTH->Delete) {?>
            <TR>
              <TD bgColor=#999999 colspan=2 align=center>
                <FONT color=black face=helvetica,arial,futura size=2>
              <?php if($User->Active){ ?>
                To deactivate this user, click here ->&nbsp
                <a href="user.php?action=detactive&uid=<?php echo $User->ID;?>">
                <img src="./images/icon_active.gif" border=0></a>
              <?php }else{?>
                To activate this user, click here ->&nbsp
                <a href="user.php?action=active&uid=<?php echo $User->ID;?>">
                <img src="./images/icon_notactive.gif" border=0></a>
              <?php }?>
                </FONT>
              </TD>
            </TR>
            <?php }?>
            <TR>
              <TD align=right bgColor=#e5e5e5><FONT color=black 
                face=helvetica,arial,futura size=2>User ID.:</FONT> </TD>
              <TD bgColor=#e5e5e5><FONT color=black face=helvetica,arial,futura 
                size=2><?php echo $User->ID; ?>&nbsp;</FONT> </TD>
            </TR>
            <TR>
              <TD align=right bgColor=#e5e5e5><FONT color=black 
                face=helvetica,arial,futura size=2><B>User Name:</B></FONT> </TD>
              <TD bgColor=#e5e5e5><INPUT name=UserName size=24 
                value="<?php echo $User->Username; ?>"></TD>
            </TR>
            <TR>
              <TD align=right bgColor=#e5e5e5><FONT color=black 
                face=helvetica,arial,futura size=2><B>Password:</B></FONT> </TD>
              <TD bgColor=#e5e5e5>
                <INPUT name=password1 size=24 value="" type=password>
                <?php  
                if($User->Password){
                   echo "<br><font color=#800000 size=2>If you want to change the password, type the new one. <br>Otherwise keep it empty.</font>";
                }
                ?>
              </TD>
            </TR>
             
            <TR>
              <TD align=right bgColor=#e5e5e5><FONT color=black 
                face=helvetica,arial,futura size=2>First Name:</FONT> </TD>
              <TD bgColor=#e5e5e5><INPUT name=FirstName size=24 
                value="<?php echo $User->Fname; ?>"></TD>
            </TR>
            <TR>
              <TD align=right bgColor=#e5e5e5><FONT color=black 
                face=helvetica,arial,futura size=2>Last Name:</FONT> </TD>
              <TD bgColor=#e5e5e5><INPUT name=LastName size=24 
                value="<?php echo $User->Lname; ?>"></TD>
            </TR>
            <TR>
              <TD align=right bgColor=#e5e5e5><FONT color=black 
                face=helvetica,arial,futura size=2>Contact Phone #:</FONT> </TD>
              <TD bgColor=#e5e5e5><INPUT name=ContactPhone size=15 maxlength=15 
                value="<?php echo $User->Phone; ?>"></TD>
            </TR>
            <TR>
              <TD align=right bgColor=#e5e5e5><FONT color=black 
                face=helvetica,arial,futura size=2><b>Contact E-mail:</b></FONT> </TD>
              <TD bgColor=#e5e5e5><INPUT name=ContactEmail size=24 value="<?php echo $User->Email; ?>"></TD>
            </TR>
            <TR>
              <TD align=right bgColor=#e5e5e5><FONT color=black 
                face=helvetica,arial,futura size=2><b>User Type:</b></FONT> </TD>
              <TD bgColor=#e5e5e5>
                <select name="type">
                  <option value=""> -- select user type --
                  <option <?php  echo ($User->Type == "user")? "selected ":""; ?> value="user">User
                  <option <?php  echo ($User->Type == "supervisor")? "selected ":""; ?> value="supervisor">Supervisor
                  <option <?php  echo ($User->Type == "MSTech")? "selected ":""; ?> value="MSTech">MS Specialist
                  <option <?php  echo ($User->Type == "Admin")? "selected ":""; ?> value="Admin">Admin
                </select>
              </TD>
            </TR>
            <TR>
              <TD align=right bgColor=#e5e5e5><FONT color=black 
                face=helvetica,arial,futura size=2>Lab Name:</FONT> </TD>
              <TD bgColor=#e5e5e5> 
                <select name="labID">
                <option <?php  echo (!$User->LabID)? "selected ":""; ?> value=""> -- Not in any Lab --
                <?php
                $Labs = new Lab();
                $Labs->fetchall();
                for ($i= 0;$i < $Labs->count;$i++) { 
                  if($User->LabID == $Labs->ID[$i]){
                    echo '<option selected value="'.$Labs->ID[$i].'">'.$Labs->Name[$i]."\n"; 
                  }else{
                     echo '<option value="'.$Labs->ID[$i].'">'.$Labs->Name[$i]."\n"; 
                  }
                } 
                ?>
                </select> &nbsp; &nbsp; [<A HREF="javascript: popwin('pop_lab.php', 400, 400);" class=button>Add Lab</A>]
              </TD>
            </TR>
            
            <TR>
              <TD align=right bgColor=#e5e5e5><FONT color=black 
                face=helvetica,arial,futura size=2>Last On:</FONT> </TD>
              <TD bgColor=#e5e5e5>
                <?php 
                if($action != "Add" or $action == ""){
                  $User->LastLogin = substr($User->LastLogin, 0, 4)."/".substr($User->LastLogin, 5, 2)."/".substr($User->LastLogin, 8, 2).
                    "-".substr($User->LastLogin, 11, 2).":".substr($User->LastLogin, 14, 2).":".substr($User->LastLogin, 17, 2);
                  echo $User->LastLogin;
                }  
                ?>
              </TD>
            </TR>

            <TR>
              <TD align=middle bgColor=#999999 colSpan=2><FONT color=black 
              face=helvetica,arial,futura size=2><B>Project Permissions</B></FONT>
			  &nbsp;&nbsp;&nbsp;&nbsp;
			  <a href='./project.php?theaction=addnew' class=button>[new project]</a>
			  </TD>
            </TR>
            
              <?php   
              // dislpay permissions for all Projects 
              $SQL = 'select ID, Name, LabID from Projects order by LabID, ID';
              $sqlResult = mysqli_query($PROHITSDB->link, $SQL);
              $pre_lab_ID = "";
              while ($row = mysqli_fetch_array($sqlResult)) {
                  // display project name
                  if($row['LabID'] != $pre_lab_ID){
                    if($pre_lab_ID) echo "<TR><TD height=8></td><font size='-2'>&nbsp;</font><TD></TD></TD></TR>\n";
                    $pre_lab_ID = $row['LabID'];
                    
                  }
                  echo  '<TR><TD align=right bgColor=#e5e5e5><FONT color=black'. 
                        ' face=helvetica,arial,futura size=2>'.$row['Name'].' ('.$row['ID'].
                        ')</font></td>';
      
                  // use auth class to check the permission
                  
                  $aAuth = new Auth($User->ID, "project", $row['ID']);
                  echo  '<TD bgColor=#e5e5e5><FONT color=black face=helvetica,arial,futura size=2>';
                  if ( $aAuth->Access ) {
                      echo '<INPUT name=accessProject'.$row['ID'].' type=checkbox value=1 checked>Access ';
                  } else {
                      echo '<INPUT name=accessProject'.$row['ID'].'  type=checkbox value=1>Access ';
                  }            
                  if ( $aAuth->Insert ) {
                      echo '<INPUT name=insertProject'.$row['ID'].'  type=checkbox value=1 checked>Insert ';
                  } else {
                      echo '<INPUT name=insertProject'.$row['ID'].'  type=checkbox value=1>Insert ';
                  }
                  if ( $aAuth->Modify ) {
                      echo '<INPUT name=modifyProject'.$row['ID'].'  type=checkbox value=1 checked>Modify ';
                  } else {
                      echo '<INPUT name=modifyProject'.$row['ID'].'  type=checkbox value=1>Modify ';
                  }            
                  if ( $aAuth->Delete ) {
                      echo '<INPUT name=deleteProject'.$row['ID'].'  type=checkbox value=1 checked>Delete ';
                  } else {
                      echo '<INPUT name=deleteProject'.$row['ID'].'  type=checkbox value=1>Delete ';
                  }   
                  echo '</FONT></TD></TR>';
              }
              ?>
        
            <TR>
              <TD align=middle bgColor=#999999 colSpan=2><FONT color=black 
                face=helvetica,arial,futura size=2><B>Page Permissions</B></FONT> </TD>
            </TR>
            
              <?php   
              // dislpay permissions for all pages 
              $SQL = 'select * from Page order by `ID`';
              $sqlResult = mysqli_query($PROHITSDB->link, $SQL);
                      
              while ($row = mysqli_fetch_array($sqlResult)) {
                  // display page name
                  echo  '<TR><TD align=right bgColor=#e5e5e5><FONT color=black'. 
                        ' face=helvetica,arial,futura size=2>'.$row['PageName'].
                        '</font></td>';
      
                  // use auth class to check the permission
                  
                  $aAuth = new Auth($User->ID, "page", $row['ID']);
                  echo  '<TD bgColor=#e5e5e5><FONT color=black face=helvetica,arial,futura size=2>';
                  if ( $aAuth->Access ) {
                      echo '<INPUT name=accessPage'.$row['ID'].' type=checkbox value=1 checked>Access ';
                  } else {
                      echo '<INPUT name=accessPage'.$row['ID'].'  type=checkbox value=1>Access ';
                  }
                  
                  if ( $aAuth->Insert ) {
                      echo '<INPUT name=insertPage'.$row['ID'].'  type=checkbox value=1 checked>Insert ';
                  } else {
                      echo '<INPUT name=insertPage'.$row['ID'].'  type=checkbox value=1>Insert ';
                  }   
      
                  if ( $aAuth->Modify ) {
                      echo '<INPUT name=modifyPage'.$row['ID'].'  type=checkbox value=1 checked>Modify ';
                  } else {
                      echo '<INPUT name=modifyPage'.$row['ID'].'  type=checkbox value=1>Modify ';
                  }   
                  
                  if ( $aAuth->Delete ) {
                      echo '<INPUT name=deletePage'.$row['ID'].'  type=checkbox value=1 checked>Delete ';
                  } else {
                      echo '<INPUT name=deletePage'.$row['ID'].'  type=checkbox value=1>Delete ';
                  }   
                  echo '</FONT></TD></TR>';
              }
              ?>        
        
            <TR>
              <TD align=mIDdle bgColor=#e5e5e5 colSpan=2>
                <?php if($action == 'Add' && $AUTH->Insert){?>
                  <INPUT ID=button1 name=button1 onclick="return Check_Password(Form)" type=button value=<?php echo $action;?>>
                <?php }else if($action == 'Modify' && $AUTH->Modify){?>
                  <INPUT ID=button1 name=button1 onclick="return Check_Password(Form)" type=button value=<?php echo $action;?>>
                <?php }else{?>
                  &nbsp;&nbsp;
                <?php }?>   
              </TD>
            </TR>
            </FORM>
          </table></FONT></A></B>
        </TD>
      </tr>
    </table>
    </TD>    
  </tr>
</table>        
<br>
<?php 
include("./admin_footer.php");
?>


