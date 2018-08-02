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
    <td align="left" colspan=2 valign=bottom>    
    <table border=1 cellspacing="0" cellpadding="2" width=100%> 
    <tr>
    <td valign=bottom>
      <table border=1 cellspacing="6" cellpadding="0" width=580>
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
          <td width='116' bgcolor=<?php echo $Value['Color']?> nowrap><div class=maintext_nowrap>&nbsp;
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
        <tr>
          <td colspan=4><div class=maintext><b>Exp Exclusion Filters</b></div></td>
        </tr>             
        <tr>
<?php 
  $filterCount = 0;
  $tmpCounter = 0;
  if($hitType == 'TPP'){
      foreach($typeExpArr as $Value ) {
        $frmName = 'frm_' . $Value['Alias'];
    ?>              
          <td width='116' bgcolor=<?php echo $Value['Color']?> nowrap>
            <div class=maintext_nowrap>
            &nbsp;<?php echo $Value['Name']?>  < <input type=text name='<?php echo $frmName?>' value='<?php echo $$frmName?>' size=2><?php echo (($Value['Alias']=='COV')?'%':'')?>
            </div>
          </td>
    <?php }?>
        </tr>
        <tr>
          <td bgcolor=<?php echo $typeFrequencyArr['Color']?> width='116' nowrap><div class=maintext_nowrap>&nbsp;
            <input type=checkbox name='frm_Frequency' value='1' <?php echo ($frm_Frequency)?"checked":"";?>>
            <a href="javascript: pop_Frequency_set();"><?php echo $typeFrequencyArr['Name']?></a>>
            <input type=text name='frequencyLimit' value="<?php echo $frequencyLimit?>" size=2>%
             </div>
          </td>
        </tr>
      <?php 
  }else if($hitType == 'TPPpep'){
    foreach($typeExpArr as $Value){
      $frmName = 'frm_' . $Value['Alias'];
      if(!strstr($Value['Alias'], 'CH')){
        echo "<td width='116' bgcolor='".$Value['Color']."' nowrap>";
        echo "<div class=maintext_nowrap>";
        echo "&nbsp;".$Value['Name']." < <input type=text name='$frmName' value='".$$frmName."' size=2>";
        echo ($Value['Alias']=='ION')?'%':'';
        echo " </div>";
        echo "</td>";
      }elseif($Value['Alias'] == 'CH1'){
        echo "<td width='116' bgcolor='".$Value['Color']."' nowrap>";
        echo "<div class=maintext_nowrap>";
        echo "&nbsp;".$Value['Name']."<input type=checkbox name='$frmName' value='".substr($Value['Alias'], -1)."' size=2 ".(($$frmName)?'checked':'').">";
      }else{
        echo "&nbsp;".$Value['Name']."<input type=checkbox name='$frmName' value='".substr($Value['Alias'], -1)."' size=2 ".(($$frmName)?'checked':'').">";  
      }
      if($Value['Alias'] == 'CH3'){
        echo " </div>";
        echo "</td>";
      }
      $subQueryString .= '&'.$frmName.'='.$$frmName;
      if($$frmName){
        if($subWhere2) $subWhere2 .= '#';
        if($Value['DBfieldName'] == 'TP.Ions'){
          $subWhere2 .= $Value['DBfieldName'].'='.$$frmName;
          continue; 
        }else{
          $subWhere2 .= $Value['DBfieldName'].'='.$Value['DBfieldName'].$Value['Operator2'].$$frmName;
        }
        if($subWhere) $subWhere .= ' AND ';
        $subWhere .= $Value['DBfieldName'].$Value['Operator'].$$frmName;
      } 
    }
    if($subWhere) $subWhere = ' AND ('.$subWhere.')';
  }else{
    foreach($typeExpArr as $Value ){
      $frmName = 'frm_' . $Value['Alias'];
  ?>              
        <td width='116' bgcolor=<?php echo $Value['Color']?> ><div class=maintext_nowrap>&nbsp;
          <input type=checkbox name='<?php echo $frmName?>' value='1' <?php echo (($theaction and $$frmName)or $$frmName)?"checked":"";?>>
  <?php   if($Value['Alias'] != 'NS'){?>        
          <a href="javascript: pop_exp_filter_set('<?php echo $Value['ID']?>');"><?php echo $Value['Name']?></a>
  <?php   }else{
        if(!isset($frm_NS_group_id) || !$frm_NS_group_id){
          $frm_NS_group_id = '';
        }
        get_NS_geneID($NSfilteIDarr,$frm_NS_group_id);
        $SQL = "SELECT `ID`,`Name` FROM `ExpBackGroundSet` WHERE `ProjectID`='$AccessProjectID'";
        $NSarr = $HITSDB->fetchAll($SQL);
  ?>
          <select name='frm_NS_group_id' class=maintext>
            <option value='' selected>select a non-specific</option>
  <?php   
        foreach($NSarr as $NSvalue){
          echo "<option value='".$NSvalue['ID']."' ".(($frm_NS_group_id==$NSvalue['ID'])?'selected':'').">".$NSvalue['Name']."<br>";
        }
  ?>
          </select>
            <a href="javascript: pop_exp_filter_set('<?php echo $Value['ID']?>');"><img src=./images/icon_view.gif border=0></a>                               .
          </div>
        </td>
  <?php 
      }
      $filterCount++;
      if(!($filterCount % 4)){
        $tmpCounter = 0;
        echo "</tr><tr>";
      }else{
        $tmpCounter++;
      }
    }
?>              
    <td bgcolor=<?php echo $expect_exclusion_color;?>><div class=maintext_nowrap>&nbsp;
    <input type=checkbox name='frm_Expect_check' value='1' <?php echo ($frm_Expect_check)?"checked":"";?>>
          Score < 
     <select name='frm_Expect' class=maintext>
       <option value='-2' selected>0</option>
<?php 
    if($theaction != 'exclusion' or !$frm_Expect) $frm_Expect = '-1';
    $theValue = 20;
    if( $frm_Expect == -1) $frm_Expect = DEFAULT_EXPECT_EXCLUSION;
    while($theValue <= 510){
      if($theValue == $frm_Expect){
        echo "<option value='$theValue' selected>$theValue</option>\n";
      }else{
        echo "<option value='$theValue'>$theValue</option>\n";
      }
      $theValue = $theValue+20;
    }
?>
       </select>
      </div></td>
  <?php if($tmpCounter == 3) echo "</tr><tr>";?>     
      <td bgcolor=<?php echo $expect_exclusion_color;?>><div class=maintext_nowrap>&nbsp;
      <input type=checkbox name='frm_Expect2_check' value='1' <?php echo ($frm_Expect2_check)?"checked":"";?>>
          Expect > 
     <select name='frm_Expect2' class=maintext>
       <option value='1' selected>1</option>
<?php 
    if($theaction != 'exclusion' or !$frm_Expect2) $frm_Expect2 = '1';
    $theValue = -1;
    while($theValue >= -300){
      if($theValue == $frm_Expect2){
        echo "<option value='$theValue' selected>$theValue</option>\n";
      }else{
        echo "<option value='$theValue'>$theValue</option>\n";
      }
      $theValue = $theValue-5;
    }
?>
       </select>
      </div>
      </td>
<?php   if($typeFrequencyArr && $frequencyLimit <= 100){
      if($tmpCounter == 2) echo "</tr><tr>"; 
?>
      <td bgcolor=<?php echo $typeFrequencyArr['Color']?> nowrap><div class=maintext_nowrap>&nbsp;
      <input type=checkbox name='frm_Frequency' value='1' <?php echo ($frm_Frequency)?"checked":"";?>>
      <a href="javascript: pop_Frequency_set();"><?php echo $typeFrequencyArr['Name']?></a>>
      <input type=text name='frequencyLimit' value="<?php echo $frequencyLimit?>" size=2>%
       </div>
      </td>
<?php   }
  } 
?>
    </tr>
<?php if($hitType != 'TPPpep'){?>   
    <tr>
      <td colspan=4>
<?php 
      include('./filter_biogrid.inc.php');?>
      </td>
    </tr>
<?php }?>                
    </table>
    </td>
    <td valign=top align=right width="40%">
      <img src='./images/pixel.gif' border=0 name='reportgif'>
    </td> 
    </tr>        
    </table>
    </td>
  </tr>
