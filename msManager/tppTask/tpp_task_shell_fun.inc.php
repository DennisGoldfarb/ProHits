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

function saveTppTask($task_arr, $frm_tppSetID, $frm_tppTaskName, $frm_tppID_str='', $frm_merge_str='', $tppTaskID=0){
  global $managerDB;
  global $tableTppTasks;
  global $USER;
    
  $param_default_str = "xinter_pppfilter=0.05\nxinter_ppiter=20\n";  
  
  if($tppTaskID){
    $SQL = "`".$tableTppTasks."` set 
     `StartTime`=now(), 
     `Status`='Waiting', 
     `UserID`='".$USER->ID."' ";
    $SQL = "UPDATE " . $SQL . " WHERE ID='".$tppTaskID."'";
    $managerDB->update($SQL);
  }else{
    if(!$frm_tppSetID){
      $SQL = "SELECT `ID`, `Name`, `Type`, `Parameters` FROM `SearchParameter` Where Type='TPP' and Name='default'";
      $tpp_para_arr =  $managerDB->fetch($SQL);
      if($tpp_para_arr){
        $frm_tppSetName = $tpp_para_arr['Name'];
        $param_str = $tpp_para_arr['Parameters'];
      }else{
        $param_str = $param_default_str;
        $frm_tppSetName = 'default';
        $SQL = "insert into `SearchParameter` set Name='$frm_tppSetName', Type='TPP', Parameters='$param_str'";
        $frm_tppSetID = $managerDB->insert($SQL);
      }
    }else{
      $theTPPSet_arr = get_search_parameters('TPP', $frm_tppSetID);
      $frm_tppSetName = $theTPPSet_arr['Name'];
      $param_str = $theTPPSet_arr['Parameters'];
    }
    $SQL = "`".$tableTppTasks."` set 
     `SearchTaskID`='".$task_arr['ID']."', 
     `ParamSetName`='".$frm_tppSetName."',
     `Parameters`='".$param_str."', 
     `TaskName`='".$frm_tppTaskName."', 
     `StartTime`=now(), 
     `Status`='Waiting', 
     `UserID`='".$USER->ID."', 
     `ProjectID`='".$task_arr['ProjectID']."'";
    $SQL = "INSERT INTO " . $SQL;
    $tppTaskID = $managerDB->insert($SQL);
  }
  
  if($tppTaskID){
    if($frm_merge_str){
      $tmp_arr = explode(";", $frm_merge_str);
      foreach($tmp_arr as $row){
        insertTppResults($tppTaskID, $row);
      }
    }
    if($frm_tppID_str){
      
      $tmp_arr = explode(",", $frm_tppID_str);
      foreach($tmp_arr as $row){
        if(trim($row))
        insertTppResults($tppTaskID, $row);
      }
    }
  }
  return $tppTaskID;
}
function updateTppTask($tppTaskID=0, $frm_tppID_str='', $frm_merge_str=''){
  global $managerDB;
  global $tableTppTasks;
  global $USER;
  if(!$tppTaskID) return "No TPP ID passed";
  $SQL = "select ID from ".$tableTppTasks." where ID='".$tppTaskID."'";
  if($USER->Type != 'Admin'){
    $SQL .= " and UserID='".$USER->ID."'";
  }
  if($managerDB->exist($SQL)){
    if($frm_merge_str){
      $tmp_arr = explode(";", $frm_merge_str);
      foreach($tmp_arr as $row){
        insertTppResults($tppTaskID, $row);
      }
    }
    if($frm_tppID_str){
      $tmp_arr = explode(",", $frm_tppID_str);
      foreach($tmp_arr as $row){
        if(trim($row))
        insertTppResults($tppTaskID, $row);
      }
    }
    $SQL = "UPDATE  `".$tableTppTasks."` set `StartTime`=now(), `Status`='Waiting' WHERE ID='".$tppTaskID."'";
    $managerDB->update($SQL);
  }else{
    return "the TPP '$tppTaskID' doesn't exist, or you can not modify the TPP task.";
  }
  return;
}

function removeEmptyTppResults($SearchTaskID=0, $empty_ID_engine_str = ''){
  global $managerDB;
  global $tableTppTasks;
  global $tableTppResults;
  if(!$SearchTaskID) return;
  $SQL = "select ID from `".$tableTppTasks."` where SearchTaskID='".$SearchTaskID."'";
  $tppTask_arr = $managerDB->fetchAll($SQL);
  if($tppTask_arr){
    $id_str = '';
    foreach($tppTask_arr as $tmp_rd){
      if($id_str) $id_str .= ",";
      $id_str .= $tmp_rd['ID'];
    }
    if($id_str){
      if($empty_ID_engine_str){
         
        $empty_ID_engine_str = trim($empty_ID_engine_str);
        $tmp_arr = explode(",", $empty_ID_engine_str);
        foreach($tmp_arr as $ID_en_str){
          $ID_en_arr = explode(":", $ID_en_str);
          if(count($ID_en_arr) == 2){
            $SQL = "delete from `".$tableTppResults."` where TppTaskID in(".$id_str.") and WellID=". $ID_en_arr[0] ." and (SearchEngine='" . $ID_en_arr[1]. "' or SearchEngine='iProphet')"; 
            
            $managerDB->execute($SQL);
          } 
        }
      }else{
        $SQL = "delete from `".$tableTppResults."` where TppTaskID in(".$id_str.") and (pepXML='' or protXML='' or protXML='NoProtXML' or pepXML='NoPepXML')"; 
        $managerDB->execute($SQL);
      }
    }
  }
}

function getTppTask($tppTasktable, $tppTaskID=0, $schTaskID=0, $status=''){
  global $managerDB; 
  $tppTasks = array();
  if(!$tppTaskID and !$schTaskID and !$status) return $tppTasks;
  $SQL = "SELECT 
        `ID`, 
        `SearchTaskID`,
        `ParamSetName`,
        `Parameters`, `TaskName`, 
        `StartTime`, `Status`, 
        `ProcessID`, `UserID`, 
        `ProjectID` 
        FROM `$tppTasktable` ";
  if($tppTaskID){
    $SQL .= "where `ID`='$tppTaskID'";
  }else if($schTaskID){
    $SQL .= "where `SearchTaskID`='$schTaskID' order by ID";
  }else if($status){
    $SQL .= "where `Status`='$status' order by ID limit 1";
  }else{
    return $tppTasks;
  }
   
  if($schTaskID){
    $tppTasks = $managerDB->fetchAll($SQL);
  }else{
    $tppTasks = $managerDB->fetch($SQL);
  }
  return $tppTasks;
}


function fetchAllTppResult($tppResulttable, $tppTaskID, $WellID=0){
  global $managerDB;
  $tppResults = array();
  if(!$tppResulttable or !$tppTaskID){
    echo "no enough info was passed for fetchAllTppResult()";
    exit;
  }
  $SQL = "SELECT 
        `WellID`, `TppTaskID`, `SearchEngine`, 
        `pepXML`, `protXML`,
        `ProhitsID`,`ProjectID`,
        `Date`, `SavedBy`, `User` 
         FROM `$tppResulttable` where TppTaskID='$tppTaskID'";
  if($WellID){
    $SQL .= " and WellID='$WellID'";
  }
  $SQL .= " order by SearchEngine, WellID";
  //echo $SQL;
  $tppResults = $managerDB->fetchAll($SQL);
  return $tppResults;
}
function updateTppResults($tppTaskID, $SearchEngine, $WellID, $pepXML, $protXML){
  global $managerDB; 
  global $tableTppResults;
  if(!$tableTppResults or !$tppTaskID or !$tppTaskID or !$SearchEngine or !$WellID){
    echo "no enough info was passed for updateTppResults()";
    exit;
  }
  $pepXML = str_replace("\\", "/", $pepXML);
  $protXML = str_replace("\\", "/", $protXML);
  $pepXML = mysqli_real_escape_string($managerDB->link, $pepXML);
  $protXML = mysqli_real_escape_string ($managerDB->link, $protXML);
  
  
  $SQL = "update `$tableTppResults` set ";
  $SQL .= "`pepXML`='$pepXML', ";
  $SQL .= "`protXML`='$protXML', ";
  $SQL .= "`Date`=now() ";
  $SQL .= " where TppTaskID='$tppTaskID' and SearchEngine='$SearchEngine' and WellID='$WellID'";
  //echo $SQL;
  $managerDB->update($SQL);
  writeLog($SQL);
}

function insertTppResults($tppTaskID, $row){
  global $managerDB;
  global $tableTppResults;
  $tmp_engine = '';
  $tmp_ID = '';
  preg_match("/^([a-zA-Z]+)(.+)/", $row, $matches);
  if(count($matches) == 3){
    $tmp_engine = $matches[1];
    $tmp_ID = $matches[2];
  }
   
  if($tmp_ID){
    $SQL = "INSERT INTO `$tableTppResults` set 
      `WellID`='$tmp_ID',
      `TppTaskID`='$tppTaskID', 
      `SearchEngine`='$tmp_engine'";
    $managerDB->insert($SQL);
    if($managerDB->affected_rows() == -1){
      $SQL = "UPDATE `$tableTppResults` set pepXML='', protXML='' WHERE 
      `WellID`='$tmp_ID' and 
      `TppTaskID`='$tppTaskID' and
      `SearchEngine`='$tmp_engine'";
      $managerDB->update($SQL);
    }
  }
}

function savedTask($task_ID, $frm_tppTaskName){
  global $managerDB;
  global $tableTppTasks;
  global $USER;
  $SQL = "select ID from $tableTppTasks where SearchTaskID='$task_ID' and TaskName='$frm_tppTaskName' and UserID='".$USER->ID."'";
   
  return $managerDB->exist($SQL);

}
function updateTppTaskStatus($tppTasktable, $tppTaskID=0,  $newStatus = '', $ProcessID=0){
  global $managerDB;
  global $USER;
  if(!$tppTasktable or !$tppTaskID or !$newStatus){
    echo "no enough info was passed for stopTppTask()";
    exit;
  }
  $SQL = "UPDATE `$tppTasktable` SET `Status` = '$newStatus'";
  if($newStatus == 'Stopped'){
    if(isset($USER->ID))  $SQL .= ", UserID='".$USER->ID."'";
  }else if($newStatus == 'Running'){
    $SQL .= ", ProcessID='".$ProcessID."'";
  }
  $SQL .=" WHERE `ID` = '$tppTaskID'";
  $managerDB->update($SQL);
  if($ProcessID and $newStatus == 'stopped'){
    system("kill $ProcessID > /dev/null &");
  }
}
function getUserName($userID){
  global $PROHITSDB;
  $SQL = "select Fname, Lname from User where ID='".$userID."'";
  $user_rd = $PROHITSDB->fetch($SQL);
  if($user_rd) {
    return $user_rd['Fname'] . " " . $user_rd['Lname']; 
  }else{
    return '';
  }
}
function getTppResultLink($WellID, $engine){
  global $managerDB;
  global $tableTppResults;
  global $tppResults;
  global $USER;
  global $perm_delete;
  global $has_parser_permit;
  global $frm_parser_type;
  global $parser_checkbox_arr;
  global $demo_search_results;
  global $tpp_ip;
  
  global $PROHITS_IP;
  //$tpp_ip = TPP_IP;
  if($tpp_ip=='localhost') $tpp_ip = $PROHITS_IP;
  
  $rt = '';
  $add_icons = '';
  $tpp_cgi = "http://" . $tpp_ip . TPP_CGI_DIR;
  if(!$tppResults or !$WellID) return "";  
  foreach($tppResults as $key => $tppRow){
    if($WellID == $tppRow['WellID'] and $engine == $tppRow['SearchEngine'] ){
      if($tppRow['pepXML'] or $tppRow['protXML']){
        if($tppRow['pepXML'] != 'NoPepXML' ){
          if($demo_search_results){
             $rt = "<a  title='open PepTPP' href='$demo_search_results' target=new>pepXML</a>";
             $rt .= " <a  title='download PepTPP xml file' href='$demo_search_results' target=new><img src='./images/icon_download.gif' border=0></a>";
          }else{
             $rt = "<a  title='open PepTPP' href='".$tpp_cgi."/PepXMLViewer.cgi?FmPprobability=".TPP_DISPLAY_MIN_PROBABILITY."&xmlFileName=".$tppRow['pepXML']."' target=new>pepXML</a>";
             $rt .= " <a  title='download PepTPP xml file' href=\"javascript: download('$WellID', '".$tppRow['TppTaskID']."', 'TPPpep_".$tppRow['SearchEngine']."');\"><img src='./images/icon_download.gif' border=0></a>";
          }
          $linked = array();
          if($tppRow['ProhitsID']){
            $linked = $tppRow;
          }else{
            $linked = getLinkedID($tppRow['WellID']);
            
            //$linked = array('ProhitsID'=>'', 'ProjectID'=>'');
          }
          
          if($tppRow['WellID'] == '1966'){
            //print_r($linked);exit;
          }
          if($linked){
            
            if($tppRow['SavedBy']){
              $add_icons = "&nbsp; <a class=sTitle title='parsed'><img src='images/icon_checked2.gif'></a>";
            }else if($has_parser_permit and ($frm_parser_type == 'TPP' or $frm_parser_type == 'both')){
              $tpp_parsed_wellID_TPPID = getParsedTPP($linked, $engine);
              if($tpp_parsed_wellID_TPPID){
                $add_icons = "&nbsp; &nbsp;<a class=sTitle title='TPP hits have been parsed to the linked sample from TPP task: ".$tpp_parsed_wellID_TPPID['TppTaskID']." WellID: ".$tpp_parsed_wellID_TPPID['WellID']."'><img src='images/icon_checkbox_disabled.gif'></a>";
              }else{
                $add_icons .=  "&nbsp; <input type=checkbox value='".$tppRow['WellID']."' name='frm_tpp_".$tppRow['SearchEngine']."'>";
                if(!isset($parser_checkbox_arr['frm_tpp_'.$tppRow['SearchEngine']])){
                  $parser_checkbox_arr['frm_tpp_'.$tppRow['SearchEngine']] = 1;
                }else{
                  $parser_checkbox_arr['frm_tpp_'.$tppRow['SearchEngine']]++;
                }
              }
            } 
            if($tppRow['SavedBy'] and $perm_delete and ($tppRow['SavedBy']==$USER->ID or $USER->Type=='Admin')){
              $tpp_div_id = $tppRow['WellID'].'@@'.$tppRow['SearchEngine'].'@@'.$tppRow['TppTaskID'];
              //$add_icons .= "&nbsp; <a id=\"$tpp_div_id\" href=\"javascript: removeTPPhits('".$tppRow['TppTaskID']."', '".$tppRow['WellID']."', '".$tppRow['SearchEngine']."')\" class=sTitle title='add to removed list'><img src=./images/icon_delete.gif border=0 alt='delete hits' alt='delete'></a>";
              $add_icons .= "&nbsp; <a id=\"$tpp_div_id\" href=\"javascript: removeTPPhits('".$tppRow['TppTaskID']."', '".$tppRow['WellID']."', '".$tppRow['SearchEngine']."')\" title='to-be-deleted'><img src=./images/icon_delete.gif border=0 alt='delete hits' alt='delete'></a>";            
            }
          }
        }else{
          $rt = "NoPepXML";
        }
        if($tppRow['protXML'] != 'NoProtXML'){
          if($demo_search_results){
            $rt .= "<br><a  title='open ProTPP' href='$demo_search_results' target=new>protXML</a>";
            $rt .= " <a  title='download ProTPP xml file' href='$demo_search_results' target=new><img src='./images/icon_download.gif' border=0></a>";
          }else{
            $rt .= "<br><a  title='open ProTPP' href='".$tpp_cgi."/protxml2html.pl?min_prob=".TPP_DISPLAY_MIN_PROBABILITY."&xmlfile=".$tppRow['protXML']."' target=new>protXML</a>";
            $rt .= " <a  title='download ProTPP xml file' href=\"javascript: download('$WellID', '".$tppRow['TppTaskID']."', 'TPPprot_".$tppRow['SearchEngine']."');\"><img src='./images/icon_download.gif' border=0></a>";
          }
        }else{
          $rt .="<br>NoProtXML";
        }
      }else{
        $rt = "selected";
      }
      
      $rt .= "<br>".$add_icons;
      unset($tppResults[$key]);
      break;
    }
  }
  return $rt;
}

function createParameterString($paraRawStr, $SearchEngine=''){
  global $condition_file;
  global $philosopher_cmd;
  $para_arr = array();
  $rt = '';
  $para_arr = array('peptideprophet'=>'', 'proteinprophet'=>'', 'iprophet'=>'');
   
  
  $para_arr['proteinprophet'] = '';
  if(strpos($paraRawStr, "frm_") === false and strpos($paraRawStr, "_") === false){
    return createParameterString_old($paraRawStr);
  }else if(strpos($paraRawStr, "plsp_") !== false){
    /////for philosopher parameter string///////
    return createPhilosopherParameterArr($paraRawStr, $SearchEngine);
  }
  ////for old parameter string (TPP)/////////
  $tmp_arr = explode("\n", $paraRawStr);
  foreach($tmp_arr as $line){
    if(!trim($line)) continue;
    $tmp_pare = explode(":", trim($line));
    if(count($tmp_pare)<1) continue;
    $$tmp_pare[0] = $tmp_pare[1];
  }
  if(isset($frm_general) and $frm_general){
   $tmp_arr = explode("proteinprophet=", $frm_general);
    
   $frm_general = trim($tmp_arr[0]);
   if(isset($tmp_arr[1])){
     if(trim($tmp_arr[1])){
       $para_arr['proteinprophet'] = trim($tmp_arr[1]);
     }
   }
   $rt .= $frm_general;
  }else{
    $rt .= "-p0.05 -x20";
  }
  //if(isset($frm_iProphet) and $frm_iProphet) $rt .= " -i".$frm_iProphet;
  if($SearchEngine == 'iProphet'){
    if(isset($frm_iProphet) and $frm_iProphet){
      $rt = '';
      $op_arr = str_split($frm_iProphet);
      $aa_arr = array('P'=>'NONSP', 'R'=>'NONRS', 'I'=>'NONSI', 'M'=>'NONSM', 'S'=>'NONSS', 'E'=>'NONSE');
      foreach($op_arr as $op){
        if(!$op)continue;
        if(preg_match("/[PRIMSE]/", $op, $matches)){
          if(isset($aa_arr[$op])) $rt .= " " . $aa_arr[$op];
        }
      }
    }else{
      $rt = " NONSP NONRS NONSI NONSM NONSE NONSS";
    }
    //return $rt;
  }else if(!$SearchEngine){
    if(isset($frm_peptideProphet) and $frm_peptideProphet){
      $rt .= " -O".$frm_peptideProphet;
    }else{
      $rt .= " -Op";
    }
  }else{
    if($SearchEngine == 'COMET') $SearchEngine = 'Comet';
    $var = "frm_peptideProphet_".$SearchEngine;
    if(isset($$var) and $$var){
      $rt .= " -O".$$var;
    }else if(isset($frm_peptideProphet) and $frm_peptideProphet){
      if($SearchEngine != 'GPM'){
        $frm_peptideProphet = str_replace("E", '', $frm_peptideProphet);
      }
      $rt .= " -O".$frm_peptideProphet;
    }else{
      $rt .= " -Op";
    }
  }
  
  
  if(isset($frm_xpress) and $frm_xpress){
    $rt .= " -X".$frm_xpress;
  }
  if(isset($frm_asap) and $frm_asap){
    $rt .= " -A".$frm_asap;
  }
  if(isset($frm_libra) and $frm_libra){
    $rt .= " -L".$frm_libra;
    if(preg_match("/(.+\.xml)/", $frm_libra, $matchs)){
      $condition_file = $matchs[1];
    }
  }
  if($philosopher_cmd){
    $rt = convert_TPP_para_to_philosopher($rt, $SearchEngine);
  }
  if($SearchEngine == 'iProphet'){
    $para_arr['iprophet'] = $rt;
  }else{
    $para_arr['peptideprophet'] = $rt;
  }
  return $para_arr;
}
function createPhilosopherParameterArr($paraRawStr, $SearchEngine){
  $tmp_arr = explode("\n", $paraRawStr);
  $para_arr = array('peptideprophet'=>'', 'proteinprophet'=>'', 'iprophet'=>'');
  foreach($tmp_arr as $line){
    if(!trim($line)) continue;
    list($key, $value) = explode(":", trim($line));
    $key = trim($key);
    $value = trim($value);
    if(!$key or !$value) continue;
    $key = str_replace("plsp_","", $key);
    if($SearchEngine != 'GPM' and $key == 'peptideprophet'){
      $value = preg_replace("/[ ]?--expectscore/", '', $value);
    }
    $para_arr[$key] = $value;
  }
  return $para_arr;
}
function convert_TPP_para_to_philosopher($para_str, $SearchEngine){
  $rt = '';
  //echo "Convert TPP parameter: '$para_str' to ";
  $TPP_to_philosopher_arr = array(
  '-p'=>'--minprob',
  //'-x'=>'--extraitrs',
  '-P'=>'--ppm',
  '-d'=>'--decoy',
  '-O'=>array('P'=>'--nonparam', 
              'A'=>'--accmass', 
              'E'=>'--expectscore', 
              'd'=>'--decoyprobs', 
              'g'=>'--glyc',
              'm'=>'--maldi',
              'N'=>'--nontt',
              'M'=>'--nonmc',
              'k'=>'--nomass',
              'o'=>'--optimizefval',
              'f'=>'--noicat',
              'i'=>'--icat',
              'H'=>'--phospho',
              'I'=>'--pi',
              'R'=>'--rt',
              'G'=>'--neggamma'
             ),
  'NONSP'=>'--nonsp',
  'NONRS'=>'--nonrs',
  'NONSI'=>'--nonsi',
  'NONSM'=>'--nonsm',
  'NONSE'=>'--nonse',
  'NONSS'=>'--nonss',
  
  );
 
  
  if($SearchEngine == 'iProphet'){
    $tmp_arr = explode(" ", $para_str);
    foreach ($tmp_arr as $p_v){
      $p_v = trim($p_v);
      if(!$p_v)continue;
      if(isset($TPP_to_philosopher_arr[$p_v])){
        $rt .= " ". $TPP_to_philosopher_arr[$p_v];
      }
    }
  }else{
    $tmp_arr = explode(" ", $para_str);
    foreach ($tmp_arr as $p_v){
      $p_v = trim($p_v);
      if(!$p_v)continue;
      if(preg_match("/(^[-]\w)(.+)/", $p_v, $matches)){
        if(count($matches)== 3){
           
          if($matches[1]== '-O'){
            $o_arr = str_split($matches[2]);
            foreach($o_arr as $o_v){
              if(isset($TPP_to_philosopher_arr['-O'][$o_v])){
                $rt .= " ". $TPP_to_philosopher_arr['-O'][$o_v];
              }
            }
          }else if(isset($TPP_to_philosopher_arr[$matches[1]])){
            $rt .= " ". $TPP_to_philosopher_arr[$matches[1]];
            if($matches[1] != '-P'){
              $rt .= " ". $matches[2];
            }
          }
        }
      }
    }
    
  }
  //echo "$rt\n";
  return trim($rt);
}
function createParameterString_old($paraRawStr){
  global $tpp_condition_file;
  global $condition_file;
  
  $part_1 ="";
  $part_2 =" -O";
  $part_3 = '';
  $part_4 = '';
  $part_5 = '';
  $part_6 = '';
  
  $tmp_arr = explode("\n", $paraRawStr);
  
  foreach($tmp_arr as $line){
    if(!trim($line)) continue;
    $tmp_pare = explode("=", trim($line));
    if(count($tmp_pare)<1) continue;
    $$tmp_pare[0] = $tmp_pare[1];
  }
  
   
  if(isset($xinter_pppfilter) and $xinter_pppfilter)  $part_1 .= "-p".$xinter_pppfilter;
  if(isset($xinter_ppiter) and $xinter_ppiter)     $part_1 .= " -x".$xinter_ppiter;
  //$part_1 .= " -nR";
  
  if(isset($pep_accmass))       $part_2 .= $pep_accmass;
  if(isset($pep_icat))          $part_2 .= $pep_icat;
  if(isset($pep_noicat))        $part_2 .= $pep_noicat;
  if(isset($pep_nglyc))         $part_2 .= $pep_nglyc;
  if(isset($pep_maldi))         $part_2 .= $pep_maldi;
  if(isset($pep_pI))            $part_2 .= $pep_pI;
  if(isset($pep_hydro))         $part_2 .= $pep_hydro;
  if(isset($pep_phospho))       $part_2 .= $pep_phospho;
  if(isset($pep_xclaster))      $part_2 .= $pep_xclaster;
  if(isset($pep_nclaster))      $part_2 .= $pep_nclaster;
  if(isset($pep_useExpect))     $part_2 .= $pep_useExpect;
  if(isset($pep_nontt))         $part_2 .= $pep_nontt;
                                $part_2 .= "p";
  if(isset($pep_ngrps))         $part_2 .= $pep_ngrps;
  if(isset($pep_occ))           $part_2 .= $pep_occ;
  
  if(isset($pep_usedecoy) and isset($pep_decoystr)) $part_3 .= " ". $pep_usedecoy . '"'.$pep_decoystr.'"';
  
  if(isset($xp_run)){
    $part_4 = " ". $xp_run;
    if(isset($xp_mass)) $part_4 .= "-m". $xp_mass;
    if(isset($xp_heavy)) $part_4 .= $xp_heavy;
    if(isset($xp_res1) and isset($xp_res1md) and $xp_res1 !='--') $part_4 .= "-n". $xp_res1 ."," . $xp_res1md;
    if(isset($xp_res2) and isset($xp_res2md) and $xp_res2 !='--') $part_4 .= "-n". $xp_res2 ."," . $xp_res2md;
    if(isset($xp_res3) and isset($xp_res3md) and $xp_res3 !='--') $part_4 .= "-n". $xp_res3 ."," . $xp_res3md;
    if(isset($xp_fix)) $part_4 .= $xp_fix;
  }
  if(isset($as_run) and isset($as_labres1) and $as_labres1 != "--"){
    $part_5 = " -A-l". $as_labres1;
    if(isset($as_labres2) and $as_labres2 != "--") $part_5 .= $as_labres2;
    if(isset($as_labres3) and $as_labres3 != "--") $part_5 .= $as_labres3;
    if(isset($as_labres4) and $as_labres4 != "--") $part_5 .= $as_labres4;
    if(isset($as_heavy)) $part_5 .= $as_heavy;
    if(isset($as_fixedscan)) $part_5 .= $as_fixedscan;
    if(isset($as_cidonly)) $part_5 .= $as_cidonly;
    if(isset($as_area) and $as_area) $part_5 .= "-f" . $as_area;
    if(isset($as_zerobg)) $part_5 .= $as_zerobg;
    if(isset($as_highbgok)) $part_5 .= $as_highbgok;
    if(isset($as_mzpeak) and $as_mzpeak) $part_5 .= "-r". $as_mzpeak;
    if(isset($as_static)) $part_5 .= $as_static;
    
    $tmp_str = '';
    if(isset($as_res1) and $as_res1mass and $as_res1 !='--') $tmp_str .= $as_res1 . $as_res1mass;
    if(isset($as_res2) and $as_res2mass and $as_res2 !='--') $tmp_str .= $as_res2 . $as_res2mass;
    if(isset($as_res3) and $as_res3mass and $as_res3 !='--') $tmp_str .= $as_res3 . $as_res3mass;
    if($tmp_str) $part_5 .= "-m". $tmp_str;
  }
  if(isset($lb_run)){
   $part_6 = " " . $lb_run . $lb_condition . "-" . $lb_channel;
   if(preg_match("/ -L(.+\.xml)/", $param_str, $matchs)){
      $condition_file = $matchs[1];
    }
  }
  return trim($part_1 . $part_2 . $part_3 . $part_4 . $part_5 . $part_6);
}

function getSearchResultFile($tableName, $schTaskID, $WellIDs, $Engine){
  global $managerDB;
  $results = array();
  $SQL = "SELECT DataFiles from ". $tableName . "SearchResults where (DataFiles is not null or DataFiles<>'') and TaskID='".$schTaskID."' and SearchEngines='".$Engine."'";
  if(strstr($WellIDs, ",")){
    $SQL .= " and WellID in(".$WellIDs.")";
  }else{
    $SQL .= " and WellID='".$WellIDs."'";
  }
  $SQL .= " order by WellID";

  $results = $managerDB->fetchAll($SQL);
  return $results;
}
function getFileName($tableName, $WellIDs){
  global $managerDB;
  $rds = array();
  $SQL = "SELECT ID, FileName,FileType, FolderID,ConvertParameter,RAW_ID  from ".$tableName." where ID in($WellIDs) order by ID" ;
  $rds = $managerDB->fetchAll($SQL);
  return $rds;
}
////////////////////////////////////////////////////////////////////////
function processTppTask($theTppTask, $phpscript, $theURL){
   
  global $tableName;
  global $tableTppTasks;
  $tppTaskID = $theTppTask['ID'];
  $tmp_ProcessID = $theTppTask['ProcessID'];
  if($tmp_ProcessID) system("kill $tmp_ProcessID > /dev/null &"); 
  
  $cmd = PHP_PATH . " " . $phpscript ." ".$tableName. " ".$tppTaskID . " " . $theURL;
   
  if(defined('DEBUG_TPP') and DEBUG_TPP){
    echo "This function is stopped. It is on debug mode. If you are Prohtis administrator, copy following line and run it on the server shell.<br>\n";
    echo $cmd."\n";
    return;
  }
  echo "<font color=black>TPP shell Process ID:";
   
  
  
  $tmp_PID =  system($cmd." > /dev/null & echo \$!");
  //$tmp_PID =  system($cmd." > ./debug.log 2>&1 & echo \$!");
  echo "</font>";
  updateTppTaskStatus($tableTppTasks, $tppTaskID, 'Running', $tmp_PID);
  echo "--the TPP task ($tppTaskID) is running in the background. Click the 'Reload' button for results.";
}
///////////////////////////////////////////////////////////////////////
function runTPP($tableName, $schTaskID, $tppTaskID, $WellIDs, $Engine, $condition_file='', $searhEngine_pepXML_str=''){
  global $debug;
  global $managerDB;
  global $param_str;
  global $param_arr;
  global $tpp_formaction;
  global $http_mascot_cgi_dir;
  global $http_sequest_cgi_dir;
  global $task_infor;
  global $search_task_arr;
  
  
  $TPP_server_mzML_file_name = '';
  $is_SWATH_file = false;
  if(strpos($search_task_arr['SearchEngines'], 'DIAUmpire')){
    $is_SWATH_file = true;
  }
  
  $parameter_file_folder = "../../TMP/search_paramters";
  writeLog("wellID=$WellIDs searchTask=$schTaskID $Engine.");
  $sch_files = '';
  $shFileNameStr = '';
  
  if($Engine == 'iProphet'){
    $sch_files = $searhEngine_pepXML_str;
    $passed_WellIDs = $WellIDs;
    $rawFiles = getFileName($tableName, $WellIDs);
    $shFileNameStr = $rawFiles[0]['FileName'];
    $task_infor = prepare_run_search_on_local($tableName, '', $schTaskID, '', 'TPP');
    
  }else{
    $tmp_schfiles = getSearchResultFile($tableName, $schTaskID, $WellIDs, $Engine);
     
    if(!$tmp_schfiles) {
      writeLog("No search results file found for wellID=$WellIDs searchTask=$schTaskID $Engine.");
      return;
    }
    foreach($tmp_schfiles as $tmpFile){
      $sch_files .= ($sch_files)? ";".$tmpFile['DataFiles']:$tmpFile['DataFiles'];
    }
    echo "search results file:  $sch_files\n";
   
    $rawFiles = getFileName($tableName, $WellIDs);
    
    
    print_r($rawFiles); 
    //ID, FileName, FolderID
    $mzFileName = '';
    $passed_WellIDs = $WellIDs;
    $WellIDs = '';
    
    
    foreach($rawFiles as $rawFile_arr){
      $WellIDs .= ($WellIDs)? ",".$rawFile_arr['ID']: $rawFile_arr['ID'];
      $shFileNameStr .= ($shFileNameStr)? ";".$rawFile_arr['FileName']:$rawFile_arr['FileName']; 
      //if($Engine == 'Mascot' or $Engine == 'SEQUEST'){
      if(!$is_SWATH_file){
        if(defined("PREFERRED_FILE_TYPE") and PREFERRED_FILE_TYPE){
          $TPP_server_mzML_file_name = mzmlExistTPPserver($tableName,$rawFile_arr, $schTaskID, PREFERRED_FILE_TYPE);
        }else{
          $TPP_server_mzML_file_name = mzmlExistTPPserver($tableName,$rawFile_arr, $schTaskID);
        }
        if(!$TPP_server_mzML_file_name){
          print "--mzML not in TPP server--\n";
        }
        
      }else{
        $task_infor = prepare_run_search_on_local($tableName, $rawFile_arr['ID'], $schTaskID, '', 'TPP');
        break;
      }
      //}
    }
  }
   
  //print_r($TPP_server_mzML_file_name); print_r($rawFiles);exit;
  $inter_file = 'NoPepXML';
  $inter_prot_file = 'NoProtXML';
  $returned_from_tpp = 0;
 
  
  
  $task_infor['tpp_machine'] = $tableName;
  $task_infor['tpp_fileID'] = $WellIDs;
  $task_infor['tpp_taskID'] = $schTaskID;
  $task_infor['tpp_TPPtaskID'] = $tppTaskID;
  $task_infor['tpp_engine'] = $Engine;
  $task_infor['tpp_schFiles'] = $sch_files;
  $task_infor['tpp_shFileNameStr'] = $shFileNameStr;
  $task_infor['tpp_parameter'] = $param_arr;
  $task_infor['tpp_mascot_cgi_dir'] = $http_mascot_cgi_dir;
  $task_infor['tpp_sequest_cgi_dir'] = $http_sequest_cgi_dir;
  $task_infor['tpp_server_mzML_file_name'] = $TPP_server_mzML_file_name;
  $task_infor['tpp_condition_file'] = $parameter_file_folder."/".$condition_file;
  $task_infor['is_SWATH_file'] = $is_SWATH_file;
   
  
  
  //*************************************************
  $tpp_results_files = run_TPP_on_local($task_infor);
  //*************************************************
   
  
  
  $inter_file = $tpp_results_files['inter_file'];
  $inter_prot_file = $tpp_results_files['inter_prot_file']; 
  if($inter_file) $returned_from_tpp = 1;

  
  if($returned_from_tpp){
    updateTppResults($tppTaskID, $Engine, $passed_WellIDs, $inter_file, $inter_prot_file);
  }
  return true;
}
function mzmlExistTPPserver($tableName,$rawFile_arr, $schTaskID='', $type='mzML', $searchEngine='TPP'){
  global $debug;
  global $managerDB;
  global $msManager_link;
  global $tpp_formaction;
  global $search_task_arr;
  global $tpp_formaction;
  global $tpp_in_prohits;
  global $task_infor;
  global $frm_theURL;
  
  
  
  $TPP_server_mzML_file_name = '';
  
  $mzFilePath = '';
  $mzFileName = '';
  
  $msManager_link = $managerDB->link;
  
  $mzFileName = '';
  $mzFileExist = false;
  $mzFileUploaded = false;
   
  $RAW_ID = ($rawFile_arr['RAW_ID'])?$rawFile_arr['RAW_ID']: $rawFile_arr['ID'];
  $fileType = $type;
  $parameter = ($rawFile_arr['ConvertParameter'])?$rawFile_arr['ConvertParameter']: $search_task_arr['LCQfilter'];
   
  $rawConvert_arr['Parameter'] = $parameter;
  $rawConvert_arr['Format'] = $fileType;
  if(strpos($search_task_arr['SearchEngines'], "iProphet")===0){
    $rawConvert_arr['is_iProphet'] = 1;
  }
  
  $new_converted_file_arr = get_new_converted_file_array('', 0, '', $RAW_ID, $rawConvert_arr);
  print_r($new_converted_file_arr); 
  echo "$tpp_in_prohits and $searchEngine\n";
  //print_r($new_converted_file_arr);exit;
  if($new_converted_file_arr['status'] == 'existed' and _is_file($new_converted_file_arr['path']) ){
    //check if in TPP server
    $mzML_file_name = preg_replace("/[.]gz$/", '', $new_converted_file_arr['FileName']);
    //$link_to = preg_replace("/".$rawFile_arr['FileType']."/i","", $rawFile_arr['FileName']) . $fileType;
    //if($tpp_in_prohits and $searchEngine != 'DIAUmpireQuant'){
    if($tpp_in_prohits){  
      $task_infor = prepare_run_search_on_local($tableName, $rawFile_arr['ID'], $schTaskID, $new_converted_file_arr['path'], $searchEngine);
      //print_r($task_infor);exit;
      
      if(_is_file($task_infor['linked_raw_file_path']) and $task_infor['prohits_mzML_file_type'] == $fileType){
        $TPP_server_mzML_file_name = $task_infor['prohits_mzML_fileName'];
        return $TPP_server_mzML_file_name;
      }
    }else{
      writeLog("Error: tpp is not in Prohits");
      exit;
    }
  }else{
    //create mzML file
    $tmp_dir_path = getFileDirPath($tableName, $rawFile_arr['ID'], $rawFile_arr['FolderID']);
    
    $type = '';
    $rawFileName = '';
    if($rawFile_arr['RAW_ID']){
      $SQL = "SELECT ID, FileName,FileType, FolderID,ConvertParameter,RAW_ID  from ".$tableName." where ID='".$RAW_ID."'";
      $rds = $managerDB->fetch($SQL);
      $type = $rds['FileType'];
      $rawFileName = $rds['FileName'];
    }else{
      $type = $rawFile_arr['FileType'];
      $rawFileName = $rawFile_arr['FileName'];
    }
    
    if(strtoupper($type) == 'RAW' or strtoupper($type) == 'WIFF'){
      $converted_file = convertLargeRawFile($tableName, $RAW_ID, $rawFileName, $type, $tmp_dir_path . $rawFileName, $rawConvert_arr, $new_converted_file_arr);
     
      if(!$converted_file){
         $msg = "Warning: cannot convert ".$rawFileName." to $type";
         writeLog($msg);
         return false;
      }else{
        saveConvertedFile2db($converted_file, '', $RAW_ID); 
        $new_converted_file_arr['path'] = $converted_file['Path'];
        $new_converted_file_arr['FileName'] = $converted_file['Name'];
      }
    }else{
      //mgf file or other file.
      return false;
    }
  }
   
  
  if(_is_file($new_converted_file_arr['path'])){
    $mzFilePath = $new_converted_file_arr['path'];
    $mzFileName = $new_converted_file_arr['FileName'];
  }
  
  
  //upload mzML file
   
  if($mzFilePath){
    if($tpp_in_prohits){
      $task_infor = prepare_run_search_on_local($tableName, $RAW_ID, $schTaskID, $new_converted_file_arr['path'], $searchEngine);
      if(_is_file($task_infor['linked_raw_file_path']) and $task_infor['prohits_mzML_file_type'] == 'mzML'){
        $TPP_server_mzML_file_name = $task_infor['prohits_mzML_fileName'];
        return $TPP_server_mzML_file_name;
      }
    
    }else{
      //upload2TTPserver
      //@require_once "HTTP/Request_Prohits.php";
      if(!$frm_theURL){
        global $PROHITS_IP;
        $storage_ip = STORAGE_IP;
        if(STORAGE_IP=='localhost') $storage_ip = $PROHITS_IP; 
        if(!$frm_theURL){
          $theURL = "http://".$storage_ip.":".$_SERVER["SERVER_PORT"].$_SERVER["PHP_SELF"];
          $frm_theURL = preg_replace("/(analyst|msManager).+$/","",$theURL) . "msManager/autoBackup/download_raw_file.php";
        }
      }
       
      $req = new HTTP_Request($tpp_formaction, array('timeout' => 1000,'readTimeout' => array(1000,0)));
      $req->setMethod(HTTP_REQUEST_METHOD_POST);
      $req->addHeader('Content-Type', 'multipart/form-data');
      $req->addPostData('tpp_myaction', 'uploadmzML');
      $req->addPostData('tpp_machine', $tableName);
      $req->addPostData('tpp_fileID', $RAW_ID);
      $req->addPostData('mzXMLfileName', $mzFileName); 
      
      $req->addPostData('prohits_mz_file_path', $mzFilePath);
      $req->addPostData('SID', 'rawDataConverter');
      $req->addPostData('tpp_searchEngine', $searchEngine); //TPP/DIAUmpireQuant
      $req->addPostData('download_from', $frm_theURL);
    
      
      if(defined('DEBUG_TPP') and DEBUG_TPP){
        echo "\nupload  $mzFilePath to Prohits_TPP.pl\n";
      }
      if (!PEAR::isError($req->sendRequest())) {
        $response1 = $req->getResponseBody();
        
        echo $response1;
         
        writeLog($mzFileName . " ". $response1);
        if(preg_match('/File uploaded/', $response1, $matchs)){
          $TPP_server_mzML_file_name = $RAW_ID."_". preg_replace("/[.]gz$/", '', $mzFileName);
          return $TPP_server_mzML_file_name;
        }else{
          fatalError($mzFilePath ." cannot upload to TPP server", __LINE__);
          return false;
        }
      } else { 
        fatalError($req->getMessage() . " in inclued file: tpp_task_shell_fun.inc.php", __LINE__);
        return false;
      }
    }
  }
  return false;
}
function getLinkedID($WellID){
  global $linked_arr;
  global $managerDB;
  global $table;
  $resul = array();
  
  if(!$WellID or strpos($WellID, ",")) return '';
  if(isset($linked_arr)){
    if($linked_arr['ProhitsID']){
      return $linked_arr;
    }else{
      return array();
    }
  }else{
    $SQL = "Select ID, ProhitsID, ProjectID from $table where ID='$WellID'";
    $result = $managerDB->fetch($SQL);
     
    if(isset($result['ProhitsID']) and $result['ProhitsID']){
      return $result;
    }else{
      return array();
    }
  }
}
function getParsedTPP($ProhitsID_Project, $searchEngine){
  global $managerDB;
  global $table;
  global $tableTppResults;
  if(!$ProhitsID_Project or !$searchEngine) return '';
  $SQL = "SELECT ID FROM $table WHERE ProhitsID='".$ProhitsID_Project['ProhitsID']."' and ProjectID='".$ProhitsID_Project['ProjectID']."'";
  $result= $managerDB->fetch($SQL);
  $wellID = ($result)?$result['ID']:'';
  $where_str = "(ProhitsID='".$ProhitsID_Project['ProhitsID']."' and ProjectID='".$ProhitsID_Project['ProjectID']."')";
  if($wellID){
    $where_str = "(".$where_str." OR WellID='".$wellID."')";
  }
  $where_str .= " and SearchEngine='".$searchEngine."' and SavedBy>0";
  $SQL = "SELECT WellID, TppTaskID FROM $tableTppResults WHERE ". $where_str;
  //echo $SQL;
  $result= $managerDB->fetch($SQL);
  //print_r($result);exit;
  return $result;
}

///////////////////////////////////////////////////////////////////////
function run_TPP_on_local($task_infor){
  $inter_file_name = ".pep.inter.xml";
  $inter_prot_file_name = ".pep.inter-prot.xml";
  $GPM_path = dirname(GPM_CGI_PATH) ;
  
  $gpmDbFile = $task_infor['fasta_file'];
   
  $tpp_results_files['inter_file'] = 'NoPepXML';
  $tpp_results_files['inter_prot_file'] = 'NoProtXML';
  $taskDir      = $task_infor['taskDir'];
  $tppWorkDir   = $taskDir;
  $tppSchEngine = $task_infor['tpp_engine'];
  $tppSchFiles  = $task_infor['tpp_schFiles'];
  $tppParameter = $task_infor['tpp_parameter'];
  $tppTaskID    = $task_infor['tpp_TPPtaskID'];
  $tppMascotCgi = $task_infor['tpp_mascot_cgi_dir'];
  $tppSequestCgi = $task_infor['tpp_sequest_cgi_dir'];
  $fileID        = $task_infor['tpp_fileID'];
  $is_SWATH_file = $task_infor['is_SWATH_file'];
   
  $shNameStr = $task_infor['tpp_shFileNameStr'];
  $tppConditionFile = $task_infor['tpp_condition_file'];
  $tpp_server_mzML_file_name = $task_infor['tpp_server_mzML_file_name'];
  $GPM_fasta_path = $task_infor['GPM_fasta_path'];
  $tppOutpuDir = $tppWorkDir . "/tpp" . $tppTaskID;
  //philosopher output file name. not path
  $ph_inter_file_name = '';
  $ph_prot_file_name = '';
  
  
  
  umask(0);
  
   
  if(!_is_dir($tppOutpuDir)){
    if(!mkdir($tppOutpuDir, 0775, true)){
      fatalError( "cannot make folder $tppOutpuDir.");
    }else{
      
    }
  }
  
  $schFiles = explode(";", $tppSchFiles);
  $shFileNames = explode(";", $shNameStr);
  $IDs   = explode(",", $fileID);  
  
  $command_arr = array();
 
  
  global $philosopher_cmd;
  
  if($philosopher_cmd){
    $command = escapeshellarg($philosopher_cmd) . " workspace --init";
    $command_arr[] = $command;
  } 
  
   
  $swath_tmp_interact_file_path = '';  
  if($is_SWATH_file && $tppSchEngine != 'iProphet'){
    //DIAUmpire search engine
    $all_inter_file_str = '';
    $prohits_mzML_fileNameBase = cleanFileName($shNameStr);
    $prohits_mzML_fileNameBase = preg_replace( "/\.gz$/i", '', $prohits_mzML_fileNameBase);
    
    $info = pathinfo($prohits_mzML_fileNameBase);
    $prohits_mzML_fileNameBase = basename($prohits_mzML_fileNameBase, '.'.$info['extension']);
     
    $rawDir = dirname($tppWorkDir);
    
    $diaumpire_results_dir_path = $rawDir."/".$fileID."_".$prohits_mzML_fileNameBase;;
    $swath_file_name_base_in_TPP = $fileID."_".$prohits_mzML_fileNameBase;
    $swath_search_dir_name = $swath_file_name_base_in_TPP ."_". strtolower($tppSchEngine);
    $swath_search_dir_path = $taskDir ."/". $swath_search_dir_name;
    $tppWorkDir = $swath_search_dir_path;
    $tppOutpuDir = $swath_search_dir_path;
    
   
    print "1--------------------\n";
    print "tppSchEngine=$tppSchEngine\n";
    print "shNameStr=$shNameStr\n";
    print "tppSchFiles=$tppSchFiles\n";
    print "diaumpire_results_dir_path=$diaumpire_results_dir_path\n";
    print "swath_file_name_base_in_TPP = $swath_file_name_base_in_TPP\n";
    print "swath_search_dir_name=$swath_search_dir_name\n";
    print "swath_search_dir_path=$swath_search_dir_path\n";
  
     
    if($tppSchEngine == 'Mascot'){
      if(!_is_dir($swath_search_dir_path)){
        mkdir("$swath_search_dir_path", 0775, true);
      }else{
        system("chmod -R 775 ".$swath_search_dir_path. " >/dev/null 2>&1"); 
      }
      include_once("../autoSearch/auto_search_umpire.inc.php");
      
      linkDIAUmpireFiles($swath_file_name_base_in_TPP, $diaumpire_results_dir_path, $swath_search_dir_path);
      
    }
    $input_file_tmp;
    $i = 0;
     
    $all_inter_file_str = '';
     
    while(isset($schFiles[$i])){ 
      $schFiles[$i] = trim($schFiles[$i]);
      if(!$schFiles[$i]) {
        $i++;
        continue;
      }
      $num = $i + 1; 
      $interact_file = "interact-".$swath_file_name_base_in_TPP."_Q$num".".pep.xml";
      $swath_tmp_interact_file_path = $swath_search_dir_path."/".$interact_file;
      if($all_inter_file_str) $all_inter_file_str .= " ";
      $all_inter_file_str .= escapeshellarg($swath_tmp_interact_file_path);
      //echo $all_inter_file_str;exit;
      $input_file = $schFiles[$i];
      
      
      
      
      //only GPM and Mascot need to make pep.xml files
      if($tppSchEngine == 'GPM'){
        if(_is_file($schFiles[$i])){
          $input_file_tmp = $schFiles[$i];
        }else{
          $input_file_tmp = $GPM_path . $schFiles[$i];
        }
        $out_file = $input_file_tmp;
        $out_file = preg_replace("/[.][a-z]+$/i", "", $out_file);
        $out_file = preg_replace("/[.]gpm$/i", "", $out_file);
        $out_file = $out_file . ".pep.xml";
        $command = escapeshellarg(TPP_BIN_PATH.'/Tandem2XML') . " " .escapeshellarg($input_file_tmp) . " " . escapeshellarg($out_file);
        $command_arr[] = $command;
        print "\n2--------------------\n"; 
        print $command; 
        $input_file = $out_file;
      }else if($tppSchEngine == 'Mascot'){
        $datFile = $schFiles[$i];
        $theShFile = $tppWorkDir."/". $swath_file_name_base_in_TPP."_Q$num".".dat";
        downloadMascotDat('', $datFile, $theShFile, $tppMascotCgi, $tppWorkDir);
        $out_file = "$tppWorkDir/".$swath_file_name_base_in_TPP."_Q$num".".pep.xml";
        if(!$theShFile){
          return 0;
        }else{
          
          if(!$gpmDbFile and _is_file($theShFile)){
            $gpmDbFile = checkMascotDB($theShFile, $GPM_fasta_path, $tppMascotCgi);
          }
        }
        $command = escapeshellarg(TPP_BIN_PATH.'/Mascot2XML') . " \"" .$theShFile . "\" -D\"" . $gpmDbFile . "\" -xml -notgz -shortid -desc"; 
        $command_arr[] = $command;
        $input_file = $out_file; 
      }  
      if($philosopher_cmd){
        $command = escapeshellarg($philosopher_cmd) . " peptideprophet " . $tppParameter['peptideprophet'] . " --database ".escapeshellarg($gpmDbFile)." $input_file";
      
      }else{
        $command = escapeshellarg(TPP_BIN_PATH.'/xinteract') . " " . $tppParameter['peptideprophet'] . " -N" . escapeshellarg($swath_tmp_interact_file_path) . " $input_file";
      }
      $command_arr[] = $command;
      print "\n3--------------------$command\n"; 
      $i++;
    }
    
    
    
    
    
    if($philosopher_cmd){
      $ph_inter_file_name = "interact-".$tppSchEngine."_combined.pep.xml";
      $ph_prot_file_name = "interact-".$tppSchEngine."_combined.prot.xml";
      $command = escapeshellarg($philosopher_cmd) . " iprophet --nonsp --nonrs --nonsi --nonsm --nonse --nonss ". $all_inter_file_str . " --output $ph_inter_file_name";
      $command_arr[] = $command;
      print "\n4--------------------$command\n"; 
      $command = escapeshellarg($philosopher_cmd) . " proteinprophet " .$tppParameter['proteinprophet']. " " . $swath_search_dir_name."/".$ph_inter_file_name . " --output interact-".$tppSchEngine."_combined";
      $command_arr[] = $command;
    
    }else{
      $interFilePath = $swath_search_dir_path."/".$tppSchEngine."_combined.pep.inter.xml";
      $command = escapeshellarg(TPP_BIN_PATH.'/InterProphetParser') ." NONSP NONRS NONSI NONSM NONSE NONSS ". $all_inter_file_str. " ". escapeshellarg($interFilePath);
      print "command: ". $command . "\n";    
      $command_arr[] = $command;
      print "\n4--------------------$command\n"; 
      $interprotFilePath = $swath_search_dir_path."/".$tppSchEngine."_combined.pep.inter.prot.xml";
      $command = escapeshellarg(TPP_BIN_PATH.'/ProteinProphet') . " ". $all_inter_file_str. " " . escapeshellarg($interprotFilePath);
      $command_arr[] = $command;
    }
    print "\n5--------------------$command\n"; 
  
  }else if($tppSchEngine == 'iProphet'){
    if($is_SWATH_file){
      //DIAUmpire iprophet
      $rsFileNameBase = $fileID."_".$shNameStr;
      //$rsFileNameBase = preg_replace("/\.[^.]+$/", '', $rsFileNameBase);
      $rsFileNameBase = preg_replace( "/[.][a-z]+(\.gz)?$/i", '', $rsFileNameBase);
      echo "tppSchFiles=$tppSchFiles\nrsFileNameBase=$rsFileNameBase\ntppOutpuDir=$tppOutpuDir\n";
      $tppSchFile_arr = explode(";", $tppSchFiles);
      $Q_pep_str_arr = array();
      $Q_pep_dir_1 = '';
       
      foreach($tppSchFile_arr as $SchFile){
        $tmp_dirname = dirname($SchFile);
        //output will be in the first of pep.xml file dir.
        if(!$Q_pep_dir_1) $Q_pep_dir_1 = $tmp_dirname;
        for($i=1; $i<=3; $i++){
          if(isset($Q_pep_str_arr[$i])){
            $Q_pep_str_arr[$i] .= " ";
          }else{
            $Q_pep_str_arr[$i] = '';
          }
          $Q_pep_str_arr[$i] .= escapeshellarg($tmp_dirname) . "/interact-*Q".$i.".pep.xml";
        }
      }
      $interFilePath_all = '';
      for($i=1; $i<=3; $i++){
        $interact_file =  "interact-". $rsFileNameBase . "_Q".$i.".pep.xml";
        $interFilePath = $tppOutpuDir."/". $interact_file;
        $interFilePath_all .= escapeshellarg($interFilePath) . " ";
        if($philosopher_cmd){
          $command = escapeshellarg($philosopher_cmd) . " iprophet " . $tppParameter['iprophet'] . " ". $Q_pep_str_arr[$i] . " --output all-". $rsFileNameBase . "_Q".$i.".pep.xml";
          $command_arr[] = $command;
          $command = "mv ". $Q_pep_dir_1 ."/all-". $rsFileNameBase . "_Q".$i.".pep.xml " . $interFilePath;
          $command_arr[] = $command;
        }else{
          $command = escapeshellarg(TPP_BIN_PATH.'/InterProphetParser') . " ". $tppParameter['peptideprophet'] . " ". $Q_pep_str_arr[$i] . " ". escapeshellarg($interFilePath);
          $command_arr[] = $command;
        }
      }
      if($philosopher_cmd){
        $ph_inter_file_name = "interact-".$rsFileNameBase."_combined.pep.xml";
        $ph_prot_file_name = "interact-".$rsFileNameBase."_combined.prot.xml";
        $command = escapeshellarg($philosopher_cmd) . " iprophet " . $tppParameter['iprophet'] . " ". $interFilePath_all . " --output $ph_inter_file_name";
        $command_arr[] = $command;
        
        $command = escapeshellarg($philosopher_cmd) . " proteinprophet --iprophet " .$tppParameter['proteinprophet']." ". $interFilePath_all . " --output interact-".$rsFileNameBase."_combined";
        $command_arr[] = $command;
      }else{
        $interprotFilePath = $tppOutpuDir . "/" . $rsFileNameBase.".pep.inter.iproph.prot.xml";
        $command = escapeshellarg(TPP_BIN_PATH.'/ProteinProphet') . " ". $interFilePath_all . escapeshellarg($interprotFilePath)." IPROPHET" ;
        $command_arr[] = $command;
      }
    }else{
      if($philosopher_cmd){
        $rsFileNameBase = pathinfo($fileID ."_".  $shNameStr, PATHINFO_FILENAME);
        $ph_inter_file_name = "interact-".$rsFileNameBase."_combined.pep.xml";
        $ph_prot_file_name = "interact-".$rsFileNameBase."_combined.prot.xml";
        $tpp_pep_file_str = '';
        foreach($schFiles as $thePepXML_file){
          $tpp_pep_file_str .= escapeshellarg($thePepXML_file). " ";
        }
        
        $command = escapeshellarg($philosopher_cmd) . " iprophet " . $tppParameter['iprophet'] . " ". $tpp_pep_file_str . " --output $ph_inter_file_name";
        $command_arr[] = $command;
        $command = escapeshellarg($philosopher_cmd) . " proteinprophet --iprophet " . $tppParameter['proteinprophet']. " ". $tppOutpuDir."/".$ph_inter_file_name . " --output interact-".$rsFileNameBase."_combined";
        $command_arr[] = $command;
      }else{
        //old TPP
        $rsFileNameBase =  basename($tppSchFiles);
        $interact_file =  preg_replace("/_[a-z]+$inter_file_name$/i", "_combined".$inter_file_name, $rsFileNameBase);
        
        $interFilePath = $tppOutpuDir."/". $interact_file;
        $tpp_pep_file_str = '';
        foreach($schFiles as $thePepXML_file){
          $tpp_pep_file_str .= escapeshellarg($thePepXML_file). " ";
        }
        $command = $command = escapeshellarg(TPP_BIN_PATH.'/InterProphetParser') . $tppParameter['peptideprophet'] . " ". $tpp_pep_file_str." " .escapeshellarg($interFilePath);
        $command_arr[] = $command;
        $interprotFilePath =  preg_replace("/xml$/", "", $interFilePath)."iproph.prot.xml";
        $command = escapeshellarg(TPP_BIN_PATH.'/ProteinProphet') . " ". escapeshellarg($interFilePath). " ". escapeshellarg($interprotFilePath). " XML IPROPHET" ;
        $command_arr[] = $command;
      }
    }
  //---------------------------
  }else if($tppSchEngine == 'Mascot'){
  //---------------------------
  // Mascot2XML
    $i = 0;
    $gpmDbFile = '';
    $all_pep_file_str = '';
    while(isset($IDs[$i])){  
      $datFile = $schFiles[$i];
      $datFile = trim($datFile);
      if(!$datFile) {
        $i++;
        continue;
      }
      $theShFile = cleanFileName($shFileNames[$i]);
      $theShFile_base = preg_replace( "/[.][a-z]+(\.gz)?$/i", '', $theShFile);
      //download mascot dat file
      if(defined("LINK_MZML_TO_TASK_DIR") and !LINK_MZML_TO_TASK_DIR){
        $newDatFile = $tppWorkDir. "/".$theShFile_base."_mascot.dat";
      }else{
        $newDatFile = $tppWorkDir. "/".$IDs[$i]."_".$theShFile_base."_mascot.dat";
      }
      downloadMascotDat('', $datFile, $newDatFile, $tppMascotCgi, $tppWorkDir);
      if(_is_file($newDatFile)){
        if(!$gpmDbFile and !is_file($gpmDbFile)){
          $gpmDbFile = checkMascotDB($newDatFile,$GPM_fasta_path, $tppMascotCgi);
        }
      }else{
        $i++;
        continue;
      }
      $rsFileNameBase  = basename($newDatFile, '.dat');
      $out_file = $rsFileNameBase . ".pep.xml";
      $all_pep_file_str .= " " . escapeshellarg($out_file); 
      if(_is_file( $gpmDbFile)){
        if(defined("LINK_MZML_TO_TASK_DIR") and !LINK_MZML_TO_TASK_DIR){
          $the_mzXML_base = $tppWorkDir. "/".$theShFile_base;
        }else{
          $the_mzXML_base = $tppWorkDir. "/".$IDs[$i]."_".$theShFile_base;
        }
        $the_mzXML_file = $the_mzXML_base.".".$task_infor['prohits_mzML_file_type'];
        $the_mascot_mzXML_file = $the_mzXML_base."_mascot".".".$task_infor['prohits_mzML_file_type'];
        //echo $the_mascot_mzXML_file;   print_r($task_infor);exit;
        if(!_is_file($the_mascot_mzXML_file)){
          if(_is_file($the_mzXML_file)){
            $OK = link_file($the_mzXML_file, $the_mascot_mzXML_file);
          }else{
            $OK = link_file($task_infor['linked_raw_file_path'], $the_mascot_mzXML_file);
            if(!$OK){
              //link error
              if(preg_match("/Permission denied|failed to create symbolic link/", $output[0], $matches)){
                echo $output[0];
              }
              //try to copy.
              $com = "cp ". escapeshellarg($task_infor['linked_raw_file_path']) ." ". escapeshellarg($the_mascot_mzXML_file);
              echo "$com\n";
              exec("$com 2>&1", $output);
              
            }
          }
        }
        $command = escapeshellarg(TPP_BIN_PATH.'/Mascot2XML') . " " .escapeshellarg($rsFileNameBase.'.dat'). " -D" . escapeshellarg($gpmDbFile) . " -notgz -shortid -desc";
        $command_arr[] = $command;
        if(defined("LINK_MZML_TO_TASK_DIR") and !LINK_MZML_TO_TASK_DIR){
        
        }else{
		      $command = "sed -i 's|spectrum=\"|spectrum=\"".$IDs[$i]."_|g' ".escapeshellarg($out_file);
          $command_arr[] = $command;
        }
		    
      }
      $i++;
    }
  //--------------------------------
  }else if($tppSchEngine == 'GPM'){
  //--------------------------------
    $i = 0;
    $all_pep_file_str = '';
    while(isset($IDs[$i])){
      $theShFile = cleanFileName($shFileNames[$i]);
      $theShFile = trim($theShFile);
      if(!$theShFile) {
        $i++;
        continue;
      }
      $input_file = $schFiles[$i];
      $rsFileNameBase  = basename($input_file, '.xml');
      $out_file = $rsFileNameBase . ".pep.xml";
      $all_pep_file_str .= " " . escapeshellarg($out_file); 
      $command = escapeshellarg(TPP_BIN_PATH.'/Tandem2XML') . " " .escapeshellarg($input_file) . " " . escapeshellarg($out_file);
      $command_arr[] = $command;
      $i++;
    }
  //--------------------------------------------------------------
  }else if($tppSchEngine == 'COMET' || $tppSchEngine == 'MSFragger' || $tppSchEngine == 'MSGFPL'){
  //--------------------------------------------------------------
    $i = 0;
    $all_pep_file_str = '';
    while(isset($IDs[$i])){
      $out_file = $schFiles[$i];
      $out_file = trim($out_file);
      if(!$out_file) {
        $i++;
        continue;
      }
      
      $rsFileNameBase  = basename($out_file, '.pep.xml');
      $all_pep_file_str .= " " . escapeshellarg($out_file); 
      $i++;
      
      $command = "sed -i 's|spectrumNativeID=\"[^\"]*\" ||g' ".escapeshellarg($out_file);
      $command_arr[] = $command;
    }
  }
  
  if(!$is_SWATH_file and $tppSchEngine != 'iProphet'){
    if(count($IDs) > 1){
      $rsFileNameBase = 'merge'.$tppSchEngine; 
    }
    $interact_file = $rsFileNameBase . $inter_file_name;
    $interFilePath = $tppOutpuDir."/".$interact_file;
    
    if($philosopher_cmd){
      //
      if(count($IDs) > 1){
         $tppParameter['peptideprophet'] .= " --combine --output " . "interact-".$rsFileNameBase;
      }
      $ph_inter_file_name = "interact-".$rsFileNameBase.".pep.xml";
      $ph_prot_file_name = "interact-".$rsFileNameBase.".prot.xml";
       
      $command = escapeshellarg($philosopher_cmd) . " peptideprophet " . $tppParameter['peptideprophet'] . " --database ".escapeshellarg($gpmDbFile)." $all_pep_file_str";
      $command_arr[] = $command;
      $command = escapeshellarg($philosopher_cmd) . " proteinprophet " . $tppParameter['proteinprophet'] ." ". $ph_inter_file_name . " --output interact-".$rsFileNameBase;
      $command_arr[] = $command;
      $command = "[ -f $ph_inter_file_name ] && mv $ph_inter_file_name $tppOutpuDir"; 
      $command_arr[] = $command;
      $command = "[ -f $ph_prot_file_name ] && mv $ph_prot_file_name $tppOutpuDir"; 
      $command_arr[] = $command;
     
    }else{
      $command = escapeshellarg(TPP_BIN_PATH.'/xinteract') . " " . $tppParameter['peptideprophet'] . " -N" . escapeshellarg($interFilePath) . $all_pep_file_str;
      $command_arr[] = $command;
    }
  }
  $task_infor['taskComFile'] = $task_infor['taskComFile']."_". $task_infor['tpp_fileID']."_".$task_infor['tpp_engine'];
  $OK = make_command_file($command_arr, $task_infor['taskDir'], $task_infor['taskComFile']);
  echo $task_infor['taskComFile']."\n"; 
  
  
  if($OK){
    //***********************************************************
    run_search_on_local($task_infor['taskComFile'], $task_infor);
    //***********************************************************
    
  }
  if($philosopher_cmd){
    if(_is_file($tppOutpuDir."/". $ph_prot_file_name)){
      $tpp_results_files['inter_file'] = $tppOutpuDir."/". $ph_inter_file_name;
      $tpp_results_files['inter_prot_file'] = $tppOutpuDir."/". $ph_prot_file_name;
    }else{
      $msg = 'no protein prophet file($rsFileNameBase) created. please read log file for detail: '.$task_infor['tasklog'];
      writeLog($msg);
    }
  }else  if(_is_file($interFilePath)){
    $tpp_results_files['inter_file'] = $interFilePath;
    $tmp_interprotFilePath = preg_replace("/[.]xml$/", '', $interFilePath);
    if(_is_file($tmp_interprotFilePath.".iproph.prot.xml")){
      $tpp_results_files['inter_prot_file'] = $tmp_interprotFilePath.".iproph.prot.xml"; 
    }else if(_is_file($tmp_interprotFilePath.".prot.xml")){
      $tpp_results_files['inter_prot_file'] = $tmp_interprotFilePath.".prot.xml"; 
    }else if(_is_file($tmp_interprotFilePath."-prot.xml")){
      $tpp_results_files['inter_prot_file'] = $tmp_interprotFilePath."-prot.xml"; 
    }else if(isset($interprotFilePath) and _is_file($interprotFilePath)){
      $tpp_results_files['inter_prot_file'] = $interprotFilePath;
    }else{
      $msg = 'no protein prophet file($rsFileNameBase) created. please read log file for detail: '.$task_infor['tasklog'];
      writeLog($msg);
    }
  }else{
    $msg = "no inter file created($rsFileNameBase). please read log file for detail: ".$task_infor['tasklog'];
    writeLog($msg);
  }
   
  return $tpp_results_files;
}
/////////////////////////////////////////////////////////////////////////////////

?>
