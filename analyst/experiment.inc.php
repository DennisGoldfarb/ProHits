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
  $frm_Name = ($frm_Name)?$frm_Name:$Bait->GeneName;
  $today = @date("Y-m-d");
  $SQL = "SELECT `Name` FROM `Experiment` WHERE `BaitID`='".$Bait->ID."'";
  $tmp_exp_name_arr = $HITSDB->fetchAll($SQL);
  
  $Collaborator_arr = array();
  $SQL = "SELECT `ID`, `FirstName`, `LastName`, `Email`, `Institute`, `UserID`, `Date` FROM `Collaborator`
          ORDER BY LastName";
  $Collaborator_arr = $PROHITSDB->fetchAll($SQL);
?>
<script language="javascript">
var exp_name_arr = new Array();
<?php foreach($tmp_exp_name_arr as $exp_name){?>
    exp_name_arr.push("<?php echo $exp_name['Name']?>"); 
<?php }?>
function ExpEditor(Exp_ID){
  var brower_v = 'IE';
  if(isNav) brower_v = 'Nav';
  var theForm = document.add_modify_form;
  var Selected_option_str = "&Selected_option_str=" + theForm.Selected_option_str.value;
  var file = 'experiment_detail_pop.php?Exp_ID=' + Exp_ID + '&send_from=experiment' + '&browser_v=' + brower_v + Selected_option_str;
  popwin(file,800,500);
}
function protocolEditor(Exp_ID){
  var file = "protocol_detail_pop.php?modal=this_project";
  popwin(file,650,600,newPop);
}
function cooperEditor(){
  var x = document.getElementById("Collaborator").selectedIndex;
  var y = document.getElementById("Collaborator").options;
  var frm_ID = y[x].value;
  file = "collaborator_pop.php?theaction=&frm_ID=" + frm_ID;
  popwin(file, 500, 300)
}
</script>
	<tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" nowrap>
	    <div class=maintext><b>Experiment Name</b>:&nbsp;</div>
	  </td>
	  <td>&nbsp;&nbsp;<input type="text" name="frm_Name" size="35" maxlength=50 value="<?php echo $frm_Name;?>">
      <input type="hidden" name="current_Name" value="<?php echo (($theaction=='modify')?$frm_Name:'');?>">
    </td>
	</tr>
  <?php if($theaction == "addnew"){
      $frm_Date = $today;
    }
  ?>
    <input type="hidden" name="frm_Date" value="<?php echo $frm_Date;?>">	
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" valign=top nowrap height=23>
	    <div class=maintext>Biological Material:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;&nbsp;
<?php 
$tmpNameDateArr = array('Date'=>'', 'ID'=>'');
$tmpNameDateArr = is_protocol_exist($frm_GrowProtocol);
if(is_array($tmpNameDateArr) and $change_protocol !='Growing' ){
?>
    <?php echo $tmpNameDateArr['Name']?>&nbsp;&nbsp;<?php echo $tmpNameDateArr['Date']?>
    <?php if($frm_OwnerID == $USER->ID){?>
    &nbsp;&nbsp;<a href="javascript:change_protocol('Growing');" class=button>[change]</a>
    
    <?php }?>
    &nbsp;&nbsp;<a href="javascript:show_protocol_static(1,0,'<?php echo $tmpNameDateArr['ID']?>');" class=button>[view]</a>
    <input type=hidden name=GrowProtocolFlag value=1>
    <?php if($AUTH->Insert){?>
    &nbsp;&nbsp;</div>
    <?php }?>
<?php }else{?>    
      <select name="frm_GrowProtocol">
      <option value="">-----Choose one-----<br>
      <?php  
  			Protocol_list($mainDB, $frm_GrowProtocol, "GrowProtocol");
      ?>
  		</select>&nbsp;&nbsp;<?php  $DateSelector = new DateSelector();  echo $DateSelector->setDate('frm_Grow_',$tmpNameDateArr['Date']); ?><br>
      &nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:show_protocol_detail(1,0);" class=button>[view]</a>
      <?php if($AUTH->Insert){?>
      &nbsp;&nbsp;<!--a href="javascript:show_protocol_detail(1,1);" class=button>[new]</a--></div>
      <?php }?>
<?php }?>	  
    </td>
  </tr>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" valign=top nowrap height=23>
	    <div class=maintext>Affinity Purification:&nbsp;</div>
	  </td>
	  <td ><div class=maintext>&nbsp;&nbsp;
<?php 
  $tmpNameDateArr = array('Date'=>'', 'ID'=>'');
  $tmpNameDateArr = is_protocol_exist($frm_IpProtocol);
  if(is_array($tmpNameDateArr) and $change_protocol !='Ip' ){
?>
    <?php echo $tmpNameDateArr['Name']?>&nbsp;&nbsp;<?php echo $tmpNameDateArr['Date']?>
    <?php if($frm_OwnerID == $USER->ID){?>
    &nbsp;&nbsp;<a href="javascript:change_protocol('Ip');" class=button>[change]</a>
    <?php }?>
    &nbsp;&nbsp;<a href="javascript:show_protocol_static(2,0,'<?php echo $tmpNameDateArr['ID']?>');" class=button>[view]</a>
    <input type=hidden name=IpProtocolFlag value=1>
    <?php if($AUTH->Insert){?>
    &nbsp;&nbsp;<!--a href="javascript:show_protocol_static(2,1,'');" class=button>[new]</a--></div>  
    <?php }?>  
<?php }else{?>     
      <select name="frm_IpProtocol">
      <option value="">------Choose one-----<br>
      <?php  
  			Protocol_list($mainDB, $frm_IpProtocol, "IpProtocol");
      ?>
  		</select>&nbsp;&nbsp;<?php  $DateSelector = new DateSelector();  echo $DateSelector->setDate('frm_Ip_'); ?><br>
      &nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:show_protocol_detail(2,0);" class=button>[view]</a>
      <?php if($AUTH->Insert){?>
      &nbsp;&nbsp;<!--a href="javascript:show_protocol_detail(2,1);" class=button>[new]</a--></div>
      <?php }?>
<?php }?>      
	  </td>
  </tr>
  
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" valign=top nowrap height=23>
	    <div class=maintext>Peptide Preparation:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;&nbsp;
<?php 
  $tmpNameDateArr = array('Date'=>'', 'ID'=>'');
  $tmpNameDateArr = is_protocol_exist($frm_DigestProtocol);
  if(is_array($tmpNameDateArr) and $change_protocol !='Digest' ){
?>
    <?php echo $tmpNameDateArr['Name']?>&nbsp;&nbsp;<?php echo $tmpNameDateArr['Date']?>
    <?php if($frm_OwnerID == $USER->ID){?>
    &nbsp;&nbsp;<a href="javascript:change_protocol('Digest');" class=button>[change]</a>
    <?php }?>
    &nbsp;&nbsp;<a href="javascript:show_protocol_static(3,0,'<?php echo $tmpNameDateArr['ID']?>');" class=button>[view]</a>
    <input type=hidden name=DigestProtocolFlag value=1>
    <?php if($AUTH->Insert){?>
    &nbsp;&nbsp;<!--a href="javascript:show_protocol_static(3,1,'');" class=button>[new]</a--></div> 
    <?php }?>   
<?php }else{?>    
      <select name="frm_DigestProtocol">
      <option value="">-----Choose one-----<br>
      <?php  
  			Protocol_list($mainDB, $frm_DigestProtocol, "DigestProtocol");
      ?>
  		</select>&nbsp;&nbsp;<?php  $DateSelector = new DateSelector();  echo $DateSelector->setDate('frm_Dig_'); ?><br>
      &nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:show_protocol_detail(3,0);" class=button>[view]</a>
      <?php if($AUTH->Insert){?>
      &nbsp;&nbsp;<!--a href="javascript:show_protocol_detail(3,1);" class=button>[new]</a--></div>
      <?php }?>
<?php }?>      
	  </td>
  </tr>
  
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" valign=top nowrap height=23>
	    <div class=maintext>LC-MS:&nbsp;</div>
	  </td>
	  <td><div class=maintext>&nbsp;&nbsp;
<?php 
  $tmpNameDateArr = array('Date'=>'', 'ID'=>'');
  $tmpNameDateArr = is_protocol_exist($frm_PeptideFrag);
  if(is_array($tmpNameDateArr) and $change_protocol !='Peptide' ){
?>
    <?php echo $tmpNameDateArr['Name']?>&nbsp;&nbsp;<?php echo $tmpNameDateArr['Date']?>
    <?php if($frm_OwnerID == $USER->ID){?>
    &nbsp;&nbsp;<a href="javascript:change_protocol('Peptide');" class=button>[change]</a>
    <?php }?>
    &nbsp;&nbsp;<a href="javascript:show_protocol_static(4,0,'<?php echo $tmpNameDateArr['ID']?>');" class=button>[view]</a>
    <input type=hidden name=PeptideFragFlag value=1>
    <?php if($AUTH->Insert){?>
    &nbsp;&nbsp;<!--a href="javascript:show_protocol_static(4,1,'');" class=button>[new]</a--></div>  
    <?php }?>  
<?php }else{?>    
      <select name="frm_PeptideFrag">
      <option value="" selected>-----Choose one-----<br>
      <?php  
  			Protocol_list($mainDB, $frm_PeptideFrag, "PeptideFrag");
      ?>
  		</select>&nbsp;&nbsp;<?php  $DateSelector = new DateSelector();  echo $DateSelector->setDate('frm_Pep_'); ?><br>
      &nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:show_protocol_detail(4,0);" class=button>[view]</a>
      <?php if($AUTH->Insert){?>
      &nbsp;&nbsp;<!--a href="javascript:show_protocol_detail(4,1);" class=button>[new]</a--></div>
      <?php }?>
<?php }?>      
	  </td>
  </tr>
  
  <tr bgcolor="#878787">
	  <td valign=top height=23 colspan=2>
      <div class=maintext><b><font color=white>Controlled Vocabularies of Experimental Details</font></b> &nbsp; &nbsp; 
      <a href="javascript:ExpEditor('<?php echo $Exp_ID;?>');" class=button>[Edit]</a>
      </div>
	   </td> 
	</tr> 
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
  <td colspan=2 align="center" width="100%">
  <DIV id='condition_data' STYLE="
                        display: block;
                        width: 700px;
                        /*height: 100px;*/
                        -moz-opacity: 0.8;
                        /*opacity: 0.3; /* these 2 lines control opacity: they work  in IE, NN, Firefox */
                        /*filter: alpha(opacity=30); /* make sure the numbers agree, e.g. .7 corresponds to 70% */
                        color: black;
                        background-color: white";>
  <table cellspacing="0" cellpadding="1" border="0" align=center width="100%">
  <tbody>          
<?php 

if($theaction == 'modify'){
    $SQL = "SELECT `ID`,
            `Name` 
            FROM `ExpDetailName`";
    $tmp_id_name_arr = $PROHITSDB->fetchALL($SQL);
    $id_name_arr = array();
    foreach($tmp_id_name_arr as $tmp_id_name_val){
      $id_name_arr[$tmp_id_name_val['ID']] = $tmp_id_name_val['Name'];
    }
    $SQL = "SELECT SelectionID 
          FROM ExpDetail           
          WHERE ExpID='$Exp_ID'
          GROUP BY SelectionID ORDER BY IndexNum";
    $select_arr = $HITSDB->fetchAll($SQL);
    $Selected_option_arr = array();
    
    foreach($select_arr as $select_val){
      $SQL = "SELECT OptionID 
              FROM ExpDetail
              WHERE SelectionID='".$select_val['SelectionID']."'
              AND ExpID='$Exp_ID'
              ORDER BY IndexNum";
      if($option_arr = $HITSDB->fetchAll($SQL)){
        foreach($option_arr as $option_val){
          $tmp_key = $select_val['SelectionID']."_".$option_val['OptionID'];
          $Selected_option_arr[$tmp_key] = $id_name_arr[$select_val['SelectionID']].";;".$id_name_arr[$option_val['OptionID']];
    ?>
        <tr bgcolor="#d3d3d3">
          <td align="right" width="29%"><div class=maintext><?php echo $id_name_arr[$select_val['SelectionID']]?> :&nbsp;</div></td>
          <td vlign=top bgcolor="#e3e3e3"><div class=maintext>&nbsp;&nbsp;<?php echo $id_name_arr[$option_val['OptionID']]?>&nbsp;</div></td>
        </tr>
    <?php     
        }
      }  
    }
    $Selected_option_str = '';
    foreach($Selected_option_arr as $Selected_option_key => $Selected_option_val){
      if($Selected_option_str) $Selected_option_str .= "@@";
      $tmp_str = $Selected_option_key.",,".$Selected_option_val;
      $Selected_option_str .= $tmp_str;
    }
  }  
?>
  </tbody>
  </table>
  </DIV>
  </td>
  </tr>
  <?php 
    if(!$frm_TaxID){
      if(isset($Bait->TaxID)){
        $frm_TaxID = $Bait->TaxID;
      }else{
        $frm_TaxID = $AccessProjectTaxID; 
      }
    }
   ?>
   <input type="hidden" name="frm_TaxID" value="<?php echo $frm_TaxID?>">
   <input type="hidden" name="frm_ProjectID" value="<?php echo $AccessProjectID?>">
   
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" valign="top" nowrap height=23>
	    <div class=maintext>Additional Description:&nbsp;</div>
	  </td>
	  <td>&nbsp;&nbsp;<textarea name="frm_Notes" rows="8" cols="80"><?php echo $frm_Notes;?></textarea></td>
	</tr> 
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
	  <td align="right" nowrap height=23>
	    <div class=maintext>Images:&nbsp;<br> (western blot images)</div>
	  </td>
	  <td><div class=maintext>
    <?php 
    if(isset($tmpImageNameArr) && is_array($tmpImageNameArr)){
      $hasImageFlag = 0;
      foreach($tmpImageNameArr as $value){
        if($value){
          echo "&nbsp;&nbsp;&nbsp;<a href=\"javascript: view_image('".$value."','".$frm_Name."');\">";
          echo "<img src='./images/icon_picture.gif' border=0 alt='view image'>";
          echo "</a>&nbsp;$value<br>";
          $hasImageFlag = 1;
        }
      }
      //if($hasImageFlag) echo "&nbsp;";
    }  
    ?>
    <?php if($theaction == "addnew" || ($theaction == "modify" && $AUTH->Modify)){ ?> 
      &nbsp;&nbsp;<input type='file' name='frm_Image' size='30'>
        &nbsp;&nbsp;<input type="button" value="attach image" onClick="javascript:checkImage();" class="green_but">
      <br>&nbsp; please only upload JPG and GIF formatted less than 5 MG image. </div>
    <?php }
      if($theaction == "modify"){
        $creater = get_userName($mainDB, $Exps['OwnerID']);
    ?>
    </tr> 
    <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
  	  <td align="right" nowrap height=23>
  	    <div class=maintext>Created by:&nbsp;</div>
  	  </td>
  	  <td><div class=maintext>&nbsp;&nbsp;<?php echo $creater;?></div></td>
	  </tr> 
    <?php }?>
    
    
    
    
    
    
    <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
  	  <td align="right" nowrap height=23>
  	    <div class=maintext>Collaborator:&nbsp;</div>
  	  </td>
  	  <td>
      <select id="Collaborator" name="frm_Collaborator">
        <option value="">-----Choose one-----<br>
<?php     foreach($Collaborator_arr as $val){
         $selected = '';
         $coop_name = $val['LastName'].', '. $val['FirstName'];
         if($theaction == "modify" && $val['ID'] == $Exps['CollaboratorID']){
          $selected = 'selected';
         }
?>
        <option value="<?php echo $val['ID']?>" <?php echo $selected?>><?php echo $coop_name?> [<?php echo $val['Institute']?>]
<?php     }?>
  		</select>
  	  &nbsp;
      <a href="javascript: cooperEditor();" class=button>[Edit]</a>
      </td>
	  </tr> 
    
    
    
    
    
    
    
    <tr bgcolor="white">
  	  <td align="right" nowrap valign="top" colspan="2">
  	    <?php 
        note_block($note_action,$Exp_ID,'Experiment',$frm_disID);
        ?>
  	  </td>
  	</tr>