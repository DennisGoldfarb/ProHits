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

$A2H_array = array("A","B","C","D","E","F","G","H");
if($Plate_ID and !$newplate){
  //get all wells in this plate
  $thePlateWells = new PlateWell();
  $thePlateWells->fetchall_this_plate($Plate_ID);
  //put records in well array. e.g. $wll_array["A01"] = 3 (Band_ID);
  //Wellcode value are A01 to A12, B01 to B12, C01 to C12 ...
  for($i=0; $i< $thePlateWells->count; $i++){
    if(strlen($thePlateWells->WellCode[$i])!=3){
      $tmpCode = $thePlateWells->WellCode[$i];
      $thePlateWells->WellCode[$i] = $tmpCode{0} . "0" . $tmpCode{1};
    }    
    $well_array[$thePlateWells->WellCode[$i]] = $thePlateWells->BandID[$i];
    $projectID_array[$thePlateWells->WellCode[$i]] = $thePlateWells->ProjectID[$i];
    if(!isset($thePlateWells->GroupID[$i])){
      $thePlateWells->GroupID[$i] = '';
    }
    $group_array[$thePlateWells->WellCode[$i]] = $thePlateWells->GroupID[$i];
  }  
}
?>
                 <table border="0" cellpadding="0" cellspacing="1" width="460">
                  <tr>
                    <td align="center"><div class=maintext><b>&nbsp;Plate Layout</b></div></td>
                  </tr>
                  <tr>
                    <td align="center" valign="top">
                      <table border="0" cellpadding="1" cellspacing="0" bgcolor="#9f9f9f">
                        <tr>
                          <td bgcolor="#707070">
                            <table border="0" cellpadding="0" cellspacing="1" bgcolor="white">
                              <tr height="18">
                              <td height="18">&nbsp;</td>
                              <td height="18"><img src="./images/1.gif" border="0"></td>
                              <td height="18"><img src="./images/2.gif" border="0"></td>
                              <td height="18"><img src="./images/3.gif" border="0"></td>
                              <td height="18"><img src="./images/4.gif" border="0"></td>
                              <td height="18"><img src="./images/5.gif" border="0"></td>
                              <td height="18"><img src="./images/6.gif" border="0"></td>
                              <td height="18"><img src="./images/7.gif" border="0"></td>
                              <td height="18"><img src="./images/8.gif" border="0"></td>
                              <td height="18"><img src="./images/9.gif" border="0"></td>
                              <td height="18"><img src="./images/10.gif" border="0"></td>
                              <td height="18"><img src="./images/11.gif" border="0"></td>
                              <td height="18"><img src="./images/12.gif" border="0"></td>
                              <td height="18" rowspan=9><font size="1">&nbsp; &nbsp;</font></td>
                              </tr>
                              <?php  
                              //create all rows of a plate layout 
                              //first for A->H second for 1->12
                              $tabs = "\n                             ";               
                              for($row=0; $row < count($A2H_array); $row++){
                                 echo $tabs;
                                 echo "<tr height='18'>";
                                 echo $tabs;
                                 echo "<td height='18'><div class=maintext>&nbsp;". $A2H_array[$row] ."&nbsp;</div></td>";
                                 for($col=1; $col <= 12; $col++){
                                   $font_color = '';
                                   $font_color2 = 'white';
                                   if($col < 10) $col = "0".$col;
                                   $theKey = $A2H_array[$row].$col;                                
                                   //echo $well_array[A4];exit;
                                   if(isset($well_array[$theKey]) && $well_array[$theKey]){
                                      if(is_array($selectedWellCode_arr)){
                                        if(in_array($theKey,$selectedWellCode_arr)){
                                          $font_color='red';
                                        }  
                                      }
                                      echo $tabs;
                                      //if it is current band in this plate                                      
                                      if($well_array[$theKey] == $Band_ID){
                                        echo "<td bgcolor='#5a7552' height='18' align=center><div class=platetext>".$well_array[$theKey]."</div></td>";
                                      }else{ 
                                        echo "<td bgcolor='#bebebe' height='18' align=center><div class=platetext>";
                                        if($projectID_array[$theKey] == $AccessProjectID and ($SCRIPT_NAME != 'band.php' and $SCRIPT_NAME != 'submit.php')){
                                          echo  "<a href=\"javascript: show_band_info('$well_array[$theKey]');\"><font color=white>".$well_array[$theKey]."</font></a>";
                                        }else{
                                          echo "<font color=$font_color>".$well_array[$theKey]."</font>";
                                        }
                                        echo "</div></td>";
                                      }
                                   }else{
                                      //available well
                                      echo $tabs;
                                      echo "<td bgcolor='#d1d1d1' height='18'>";
                                      //do not allowed other user add to plate
                                      if(($SCRIPT_NAME == 'band.php' || $SCRIPT_NAME == 'submit.php') and $AUTH->Modify){
                                        if(in_array($theKey,$selectedWellCode_arr)){
                                          echo "<a href=\"javascript:remove_well('" . $theKey . "');\">";
                                          echo "<img src='./images/icon_but_red.gif' border=0 alt='available well (".$theKey.")'></a>";
                                        }else{
                                          echo "<a href=\"javascript:select_well('" . $theKey . "');\">";
                                          echo "<img src='./images/icon_but.gif' border=0 alt='available well (".$theKey.")'></a>";
                                        }
                                      }else{
                                       echo "<img src='./images/icon_but.gif' border=0 alt='available well (".$theKey.")'>";
                                      }
                                      echo "</td>\n";
                                   }   
                                 }
                                 echo $tab."</tr>";
                              
                              }
                              ?>
                              <tr>
                                <td colspan=14 height="5"> <font size="1">&nbsp;</font></td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td align="center" valign="top">
										<?php if(($SCRIPT_NAME == 'band.php' || $SCRIPT_NAME == 'submit.php') and $AUTH->Modify){?>
                    <a href="javascript:change_plate('first');">
                     <img src="images/icon_first.gif" width="31" height="18" border="0"></a>&nbsp;
                     <a href="javascript:change_plate('previous');">
                     <img src="images/icon_previous.gif" width="30" height="18" border="0"></a>&nbsp;
                     <a href="javascript:change_plate('next');">
                     <img src="images/icon_next.gif" width="30" height="18" border="0"></a>&nbsp;
                      <a href="javascript:change_plate('last');">
                     <img src="images/icon_last.gif" width="30" height="18" border="0"></a>&nbsp; 
                     <a href="javascript:change_plate('new');">
                     <img src="images/icon_new_plate.gif" width="60" height="18" border="0"></a>
                     <?php }?>
                     </td>
                  </tr>
                </table>