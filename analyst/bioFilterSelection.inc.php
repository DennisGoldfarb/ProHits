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
  <tr>
    <td valign=bottom colspan=2>
      <table border=1 cellspacing="0" cellpadding="0" width=100%>
<?php if($typeBioArr && $hitType != 'TPPpep'){?> 
          <tr>
            <td colspan=4><div class=maintext><b>Bio Exclusion Filters</b></div></td>
          </tr>             
          <tr>
    <?php 
    $filterCount = 0;              
    foreach($typeBioArr as $Value) {
      $frmName = 'frm_' . $Value['Alias'];
    ?>              
            <td width='116' bgcolor=<?php echo $Value['Color']?> nowrap><div class=maintext nowrap>&nbsp;
              <input type=checkbox name='<?php echo $frmName?>' value='1' <?php echo (($theaction and $$frmName) or $$frmName)?"checked":"";?>>                  
              <a href="javascript: pop_filter_set('<?php echo $Value['ID']?>');"><?php echo $Value['Name']?></a>                  
              </div>
            </td>
    <?php 
      $filterCount++;
      if(!($filterCount % 4)){
        echo "</tr><tr>";
      }  
    }
    ?>                
          </tr>
<?php }?>                   
      </table>
    </td>
  </tr>        
      
