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
if(!is_file("./config/conf.inc.php")){
  echo "<h1>Please install Prohits!</h1>";
  exit;
}

//include("./common/include_path.inc.php");
 
$current_URL = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
 
include("config/conf.inc.php");
include("main_menu_header.php");
?>
                                    <table BORDER=0 CELLPADDING=0 CELLSPACING=10>
                                    <?php if(!DISABLE_RAW_DATA_MANAGEMENT){?>
                                    <tr>
                                    	<td><A HREF="./login.php?RTPAGE=./msManager/" onMouseOver="images_2wap('msManager','arr_on')" onMouseOut="images_2wap('msManager','arr_off')">
                                        <IMG SRC="images/back_arr_off.gif" WIDTH=27 HEIGHT=25 name=msManager border=0></a>
                                        </td>
                                    	<td>&nbsp; &nbsp;<A HREF="./login.php?RTPAGE=./msManager/" onMouseOver="images_2wap('msManager','arr_on')" onMouseOut="images_2wap('msManager','arr_off')" class=Main_menu>MS Data Management</a></td>
                                    </tr>
                                    <?php }?>
                                    <tr>
                                    	<td><A HREF="./login.php?RTPAGE=./analyst/" onMouseOver="images_2wap('analyst','arr_on')" onMouseOut="images_2wap('analyst','arr_off')">
                                        <IMG SRC="images/back_arr_off.gif" WIDTH=27 HEIGHT=25 name=analyst border=0></a>
                                        </td>
                                    	<td>&nbsp; &nbsp;<A HREF="./login.php?RTPAGE=./analyst/" onMouseOver="images_2wap('analyst','arr_on')" onMouseOut="images_2wap('analyst','arr_off')" class=Main_menu>Analyst</a></td>
                                    </tr>
                                    <tr>
                                    	<td><A HREF="./login.php?RTPAGE=./admin_office/" onMouseOver="images_2wap('adminOffice','arr_on')" onMouseOut="images_2wap('adminOffice','arr_off')">
                                        <IMG SRC="images/back_arr_off.gif" WIDTH=27 HEIGHT=25 name=adminOffice border=0></a>
                                        </td>
                                    	<td>&nbsp; &nbsp;<A HREF="./login.php?RTPAGE=./admin_office/" onMouseOver="images_2wap('adminOffice','arr_on')" onMouseOut="images_2wap('adminOffice','arr_off')" class=Main_menu>Admin Office</a></td>
                                    </tr>
                                    
                                    <tr>
                                    	<td><A HREF="http://prohitsms.com/Prohits/doc/doc.php?home=<?php echo $current_URL;?>" onMouseOver="images_2wap('doc','arr_on')" onMouseOut="images_2wap('doc','arr_off')">
                                        <IMG SRC="images/back_arr_off.gif" WIDTH=27 HEIGHT=25 name=doc border=0></a>
                                        </td>
                                    	<td>&nbsp; &nbsp;<A HREF="http://prohitsms.com/Prohits/doc/doc.php?home=<?php echo $current_URL?>" onMouseOver="images_2wap('doc','arr_on')" onMouseOut="images_2wap('doc','arr_off')" class=Main_menu>Documents</a></td>
                                    </tr>
                                     
                                    </table>
<?php include("./main_menu_footer.php");?>