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

require_once("../common/site_permission.inc.php");
require_once("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

if(isset($status_detail_show) && $status_detail_show == 'y'){
  if(!isset($item_ID) || !$item_ID || !isset($itemType) || !$itemType) return;
  $SQL = "SELECT `ID`,`Name` FROM `ExpDetailName`";
  $tmpExpDetail_arr = $PROHITSDB->fetchAll($SQL);
  $ExpDetail_id_name_arr = array();
  foreach($tmpExpDetail_arr as $tmpExpDetail_val){
    $ExpDetail_id_name_arr[$tmpExpDetail_val['ID']] = $tmpExpDetail_val['Name'];
  }
  if($itemType == "Bait"){
    $tmp_head = 'B';
  }elseif($itemType == "Experiment"){
    $tmp_head = 'E';
  }elseif($itemType == "Band"){
    $tmp_head = 'S';
  }
  $detail_div_id = $tmp_head.$item_ID."_a";  
  //-------------------------------------------------------------------------  
  $GelFreeColor = "#737373";
  $unGelFreeColor = "#000000";
  $GrowColor = "#d2691e";
  $IpColor = "#ffa500";
  $DigestColor = "#ffc0cb";
  $LC_MSColor = "#63b1b1";
  $RawFileColor = "#2080df";
  $RawFileUnLinkedColor = "#bcbc7a";
  $HasHitsColor = "#5b52ad";
  $EmptyColor = "#d9e8f0";
  $letterFlag = '';
  $SampleMess = '';  
  
  if($itemType == "Bait"){
     
    $SQL = "SELECT `ID`, `GelFree` 
            FROM `Bait` 
            WHERE ID='$item_ID'";
    $SQL2 ="SELECT `ID`,
            `Name`,
            `GrowProtocol`,
            `IpProtocol`, 
            `DigestProtocol`,
            `PeptideFrag`,
            `Notes`
            FROM `Experiment` 
            WHERE `BaitID`='$item_ID' order by ID";
  }elseif($itemType == "Experiment"){ 
     
    $SQL = "SELECT B.ID, B.GelFree 
            FROM Experiment E 
            LEFT JOIN Bait B ON E.BaitID=B.ID 
            WHERE E.ID='$item_ID'";        
    $SQL2 ="SELECT `ID`,
            `Name`,
            `GrowProtocol`,
            `IpProtocol`, 
            `DigestProtocol`,
            `PeptideFrag`,
            `Notes`
            FROM `Experiment` 
            WHERE `ID`='$item_ID' order by ID";                
  }elseif($itemType == "Band"){
      
    $SQL = "SELECT B.ID, B.GelFree, N.ResultsFile
            FROM Band N 
            LEFT JOIN Bait B ON N.BaitID=B.ID 
            WHERE N.ID='$item_ID'";
    $SQL2 ="SELECT E.ID,
            E.Name,
            E.GrowProtocol,
            E.IpProtocol, 
            E.DigestProtocol,
            E.PeptideFrag,
            E.Notes 
            FROM Band B LEFT JOIN Experiment E ON E.ID=B.ExpID 
            WHERE B.ID='$item_ID'";
  }
  //echo $SQL2;exit;
  $itemInfoArr = $HITSDB->fetch($SQL);
  $Bait_ID = $itemInfoArr['ID'];
  $GelFree =$itemInfoArr['GelFree'];
  if($ExperimentArr = $HITSDB->fetchAll($SQL2)){
    $detailStr_all = $detail_div_id."@@**@@";
    foreach($ExperimentArr as $ExperimentValue){
      $SQL = "SELECT 
              `SelectionID`,
              `OptionID` 
              FROM `ExpDetail` 
              WHERE `ExpID`='".$ExperimentValue['ID']."'
              ORDER BY `IndexNum`";
      $expConditionArr = $HITSDB->fetchAll($SQL);
      $expConditionStr = '';
      foreach($expConditionArr as $expConditionVal){
        $tmpSelection = $ExpDetail_id_name_arr[$expConditionVal['SelectionID']];
        $tmpOption = $ExpDetail_id_name_arr[$expConditionVal['OptionID']];
        if($expConditionStr) $expConditionStr .= "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $expConditionStr .= $tmpSelection.": ".$tmpOption;
      }
       
      if($expConditionStr){
        if(count($expConditionArr) == 1){
          $expConditionStr = "(".$expConditionStr.")";
        }else{
          $expConditionStr = "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(".$expConditionStr.")";
        }  
      }  
      $detailStr = '';
  		$sub_sql = '';
      
      if($itemType == "Bait" || $itemType == "Experiment"){
				$typeID = $ExperimentValue['ID'];
    		$typeID_name = 'ExpID';
  		}else{
				$typeID = $item_ID;
    		$typeID_name = 'ID';
  		}
      $SQL = "SELECT
             `ID`,
             `Location`,
             `RawFile` 
             FROM `Band` 
             WHERE $typeID_name='".$typeID."'";
      $BandArr = $HITSDB->fetchAll($SQL);
  			
      if($GelFree){
        $GelFreeColorlocal = $GelFreeColor;
        $gelFreeMess = '<font color=#008040>'.$ExperimentValue['Name'].$expConditionStr.'</font>';
      }else{
        $GelFreeColorlocal = $unGelFreeColor;
        $gelFreeMess = '<font color=#008040>'.$ExperimentValue['Name'].$expConditionStr.'</font>';
      }
      $detailStr .= "<table border=0 cellpadding='0' cellspacing='0'>\r\n";
      $ret_arr = get_link_for_note($ExperimentValue['ID'],'Experiment');
      $detailStr .= "<tr><td align=left>" . display_step(0,$GelFreeColorlocal,$gelFreeMess) . "</td><td align=left nowrap>" . $ret_arr[0]."</td></tr>";
              
      $ProtocolMess = '';
      $bgColor = $EmptyColor;
      if($ExperimentValue['GrowProtocol']){
        $ProtocolMess = get_Protocol($ExperimentValue['GrowProtocol'],1);
        $bgColor = $GrowColor;
      }    
      $detailStr .= "<tr><td align=left colspan=2>" . display_step(1,$bgColor,$ProtocolMess)."</td></tr>";
        
      $ProtocolMess = '';
      $bgColor = $EmptyColor;
      if($ExperimentValue['IpProtocol']){
        $ProtocolMess = get_Protocol($ExperimentValue['IpProtocol'],2);
        $bgColor = $IpColor;
      }
      $detailStr .= "<tr><td align=left colspan=2>" . display_step(2,$bgColor,$ProtocolMess)."</td></tr>";
        
      $ProtocolMess = '';
      $bgColor = $EmptyColor;
      if($ExperimentValue['DigestProtocol']){
        $ProtocolMess = get_Protocol($ExperimentValue['DigestProtocol'],3);
        $bgColor = $DigestColor;
      }
      $detailStr .= "<tr><td align=left colspan=2>" . display_step(3,$bgColor,$ProtocolMess)."</td></tr>";
      
      $ProtocolMess = '';
      $bgColor = $EmptyColor;
      if($ExperimentValue['PeptideFrag']){
        $ProtocolMess = get_Protocol($ExperimentValue['PeptideFrag'],4);
        $bgColor = $LC_MSColor;
      }
      $detailStr .= "<tr><td align=left colspan=2>" . display_step(4,$bgColor,$ProtocolMess)."</td></tr>";
      
      $HasHitsColor = "#5b52ad";
      $msManagerDB = new mysqlDB(MANAGER_DB);
      if($BandArr){
  		  foreach($BandArr as $BandValue){
          $BandHits_passed = 0;
       	  $sample = $BandValue['Location'];
          $task_id_arr = array();
          $is_merged = '';
       	  if($BandValue['RawFile']){
         	  $rawFileInfoArr = explode(";",$BandValue['RawFile']);//rawfiles belong to a single band--
  				  $SampleMess = '';
            foreach($rawFileInfoArr as $rawValue){
           	  $fileTableIDarr = explode(":",$rawValue);
           	  $thetppResultsTable = '';
              if(count($fileTableIDarr) != 2) continue;
              
              if(preg_match("/^(\w+)tppResults$/", $fileTableIDarr[0], $matches)){
                $fileTableIDarr[0] = $matches[1];
                $is_merged = "tppResults";
              }
              
              $theTableName = $fileTableIDarr[0];
              
              if(!array_key_exists($theTableName, $task_id_arr)){
                $task_id_arr[$theTableName] = array();
              }              
              $is_hits_parsed = 0;
           	  $table = '';
           	  $file = ''; 
           	  $thePos = strpos($fileTableIDarr[0], "tpp");
           	  $thetppResultsTable = $fileTableIDarr[0];
             	if($thePos){
               	$table = substr($fileTableIDarr[0], 0,$thePos);
             	}else{
               	$table = $fileTableIDarr[0];
             	}
             	$fileTableIDarr[1] = preg_replace("/[,]$/",'', $fileTableIDarr[1]);
              if(!$fileTableIDarr[1]) continue;
             	$SQL = "SELECT `ID`,`FileName`,`FolderID`,`Size` FROM ".$table." WHERE `ID` in(".$fileTableIDarr[1].")";
             	if($mBaseArr1 = $msManagerDB->fetchAll($SQL)){
                $subFlag = '';
                if($is_merged){
  								if($SampleMess) $SampleMess .= "\n".str_repeat("&nbsp;",6);
  								$SampleMess .= "Merged Files:";
                  $subFlag = '-';
  							}
               
                if($is_merged){
                  $key = "TppTaskID";
                  $results_table_name = $theTableName.$is_merged;
                  $SQL = "SELECT ".$key." as TaskID,
                          `Date` 
                          FROM $results_table_name 
                          WHERE `WellID`='".$fileTableIDarr[1]."' AND SavedBy>0
                          ORDER BY `Date` DESC,$key DESC";
                  $tmp_arr = $msManagerDB->fetchAll($SQL);                  
                  foreach($tmp_arr as $tmp_val){
                    if(!array_key_exists($key, $task_id_arr[$theTableName])){
                      $task_id_arr[$theTableName][$key] = array();
                      array_push($task_id_arr[$theTableName][$key], $tmp_val['TaskID']);
                    }else{
                      if(!in_array($tmp_val['TaskID'], $task_id_arr[$theTableName][$key])){
                        array_push($task_id_arr[$theTableName][$key], $tmp_val['TaskID']);
                      }
                    }
                  } 
                }               
                                          
               	foreach($mBaseArr1 as $mTableArr){
                  $raw_path = get_rawfile_path($mTableArr['FolderID'],$table,$msManagerDB);
                  $file = "<a class='title' title='$raw_path'>".$subFlag.$mTableArr['FileName']."</a>";
                 	if($SampleMess) $SampleMess .= "\n<font face='Courier' size=1>".str_repeat("&nbsp;",6)."</font>";
                 	$SampleMess .= $file;
                 	$tmp_Size = '';
                 	if( $mTableArr['Size']){
                   	$tmp_Size = number_format(ceil( $mTableArr['Size']/1024));
                   	$SampleMess .= "&nbsp;(".$tmp_Size."KB)";
                 	}
                  $pop_file_name = "./pop_rawFile_detail.php?tableName=".$table."&raw_file_ID=".$mTableArr['ID']."&Band_ID=".$BandValue['ID']."&GelFree=$GelFree";
                  $SampleMess .= "&nbsp;<a  title='raw file detail' href=\"javascript: popwin('$pop_file_name',550,380)\">[detail]</a>";
               	  $search_time = '';
                  $task_id = '';
                  
                  $results_table_name_arr['TaskID'] = $theTableName."SearchResults";
                  $results_table_name_arr['TppTaskID'] = $theTableName."tppResults ";
                  foreach($results_table_name_arr as $key => $results_table_name){
                    $SQL = "SELECT ".$key." as TaskID, SavedBy,
                            `Date` 
                            FROM $results_table_name 
                            WHERE `WellID`='".$mTableArr['ID']."'
                            ORDER BY `Date` DESC,$key DESC";
                    $tmp_arr = $msManagerDB->fetchAll($SQL);
                    foreach($tmp_arr as $tmp_val){
                      if(!$is_merged && $tmp_val['SavedBy'] > 0){
                        if(!array_key_exists($key, $task_id_arr[$theTableName])){
                          $task_id_arr[$theTableName][$key] = array();
                          array_push($task_id_arr[$theTableName][$key], $tmp_val['TaskID']);
                        }else{
                          if(!in_array($tmp_val['TaskID'], $task_id_arr[$theTableName][$key])){
                            array_push($task_id_arr[$theTableName][$key], $tmp_val['TaskID']);
                          }
                        }
                      }
                      if(!$tmp_val['SavedBy']){                              
                        if(!$search_time && $tmp_val['Date']){
                          $search_time = $tmp_val['Date'];
                          $task_id = $tmp_val['TaskID'];
                        }else{
                          if($tmp_val['Date'] > $search_time){
                            $search_time = $tmp_val['Date'];
                            $task_id = $tmp_val['TaskID'];
                          }
                        }
                        if(!$search_time) $task_id = $tmp_val['TaskID'];
                      }  
                    } 
                  }
                  if(!is_hits_parsed($mTableArr['ID'], $theTableName)){
                    if(!$is_merged){
                      if($task_id && $mTableArr['FolderID'] && $theTableName){
                        $ms_url = "../msManager/ms_search_results_detail.php?table=$theTableName&frm_PlateID=".$mTableArr['FolderID']."&iniTaskID=$task_id";
                        $SampleMess .= "&nbsp;<a  title='pass hits' href=\"$ms_url\">[pass hits]</a>";
                      }
                    }  
                  }else{
                    $is_hits_parsed = 1;
                  }
                }
             	}
              $BandHits_passed += $is_hits_parsed;
         	  }
            if($SampleMess){
              $ret_arr = get_link_for_note($BandValue['ID'],'Band');
    				  $detailStr .= "<tr><td align=left>" . display_step(5,$RawFileColor,$SampleMess) . "</td><td align=left nowrap>" . $ret_arr[0]."</td></tr>";
              
            }else{
              $pop_file_name = "./pop_link_rawFile.php?Band_ID=".$BandValue['ID']."&GelFree=$GelFree&itemType=$itemType&item_ID=$item_ID";
              if($AUTH->Modify){
                $SampleMess = "&nbsp;<a  title='link raw file' href=\"javascript: popwin('$pop_file_name',550,380)\">[link raw file]</a>";
              }
              
              $ret_arr = get_link_for_note($BandValue['ID'],'Band');
              $detailStr .= "<tr><td align=left>".display_step(5,$RawFileUnLinkedColor,$SampleMess) . "</td><td align=left nowrap>" . $ret_arr[0]."</td></tr>";
              
            }    
         	}else{
            $pop_file_name = "./pop_link_rawFile.php?Band_ID=".$BandValue['ID']."&GelFree=$GelFree&itemType=$itemType&item_ID=$item_ID";
            if($AUTH->Modify){
              $SampleMess = "&nbsp;<a  title='link raw file' href=\"javascript: popwin('$pop_file_name',550,380)\">[link raw file]</a>";
            }else{
              $SampleMess = "";
            }
            $ret_arr = get_link_for_note($BandValue['ID'],'Band');
            $detailStr .= "<tr><td align=left>".display_step(5,$RawFileUnLinkedColor,$SampleMess) . "</td><td align=left nowrap>" . $ret_arr[0]."</td></tr>";
          }
          
          $task_id_str = '';
          $TPPtask_id_str = '';
          get_machine_taskID_str();
          
       	  $tmp_num_arr = get_hit_num($BandValue['ID']);
          
       	  $hitsMess = '';
         	if($tmp_num_arr['hits'] or $tmp_num_arr['hitsTppProt'] or $tmp_num_arr['hitsGeneLevel']){
           	$tppHits = ($tmp_num_arr['hitsTppProt'])?" <font color=#008040>TPP hits:</font><font color=red>".$tmp_num_arr['hitsTppProt'].$TPPtask_id_str."</font>":"";
           	if($tmp_num_arr['hitsGeneLevel']){
              $geneLevelHits = "</font>&nbsp;&nbsp;<font color=#008040># of Hits_GeneLevel:</font>&nbsp;&nbsp;<font color=red>".$tmp_num_arr['hitsGeneLevel']."</font>";
            }else{
              $geneLevelHits = '';
            }
            $hitsMess = "<font color=#008040># of Hits:</font>&nbsp;&nbsp;<font color=red>".$tmp_num_arr['hits'].$geneLevelHits."<font color=red>$task_id_str</font>".$tppHits;
          }elseif($BandHits_passed){
            $tppHits = ($tmp_num_arr['hitsTppProt'])?" <font color=#008040>TPP hits:</font><font color=red>0</font>":"";
           	$hitsMess = "<font color=#008040># of Hits:</font>&nbsp;&nbsp;" . "<font color=red>0</font>".$task_id_str.$tppHits.$TPPtask_id_str;
    			}
          
         	if($hitsMess){
           	$bgColor = $HasHitsColor;
         	}else{
           	$bgColor = $EmptyColor;
         	}
         	$detailStr .= "<tr><td align=left colspan=2>".display_step(6,$bgColor,$hitsMess)."</td></tr>";
       	}
     	}else{
       	$detailStr .= "<tr><td align=left>".display_step(5,$EmptyColor) . "</td></tr>";
       	$detailStr .= "<tr><td align=left colspan=2>".display_step(6,$EmptyColor)."</td></tr>";
     	}
      if($ExperimentValue['Notes']){
        $detailStr .= "<tr><td align=left colspan=2>".display_note($ExperimentValue['Notes'])."</td></tr>";
      }
      if($itemType == "Band" and isset($itemInfoArr['ResultsFile']) and $itemInfoArr['ResultsFile']){
        $detailStr .= "<tr><td align=left colspan=2>".display_note($itemInfoArr['ResultsFile'], "Sample")."</td></tr>";
      }
      $detailStr .= "</table>\r\n";
      $detailStr_all .= $detailStr;      
    }
    echo $detailStr_all;
  }
  exit;
}

function get_status($item_ID, $itemType, $toggle=0){
  global $HITSDB,$AccessProjectID, $machine_name_icon_arr; 
  $itemAtrArr = array ('num_files' => 0,'num_Exp' => 0, 'num_hits' => 0, 'num_hitsGeneLevel' => 0, 'num_Band' => 0, 'num_hitsTppProt' => 0, 'num_hitsTppPep' => 0, 'total_hits'=>0 ,'hitType' => '','has_report' => 0,); 
  
  $type_color_pair = protocol_type_color_pair();  
  $is_exp_flag = 0;
  if(!is_array($toggle)){
    $agl_itemType = $itemType;
    $passed_type = '';
  }else{
    $agl_itemType = '';
    $passed_type = $itemType;
    //$agl_itemType = $toggle[0];
  }
  
  $GelFreeColor = "#737373";
  $unGelFreeColor = "#000000";
  $GrowColor = "#d2691e";
  $IpColor = "#ffa500";
  $DigestColor = "#ffc0cb";
  $LC_MSColor = "#63b1b1";
  $RawFileColor = "#2080df";
  $HasHitsColor = "#5b52ad";
  $EmptyColor = "#d9e8f0";
  $letterFlag = '';
  $SampleMess = '';
  if($itemType == "Bait"){
    $SQL = "SELECT `ID`, `GelFree` 
            FROM `Bait` 
            WHERE ID='$item_ID'";
    $SQL2 ="SELECT `ID`,
            `Name`,
            `GrowProtocol`,
            `IpProtocol`, 
            `DigestProtocol`,
            `PeptideFrag`,
            `Notes`
            FROM `Experiment` 
            WHERE `BaitID`='$item_ID' order by ID";
  }elseif($itemType == "Experiment"){          
    $SQL = "SELECT B.ID, B.GelFree 
            FROM Experiment E 
            LEFT JOIN Bait B ON E.BaitID=B.ID 
            WHERE E.ID='$item_ID'";        
    $SQL2 ="SELECT `ID`,
            `Name`,
            `GrowProtocol`,
            `IpProtocol`, 
            `DigestProtocol`,
            `PeptideFrag`,
            `Notes`
            FROM `Experiment` 
            WHERE `ID`='$item_ID' order by ID";                
  }elseif($itemType == "Band"){
    $SQL = "SELECT B.ID, B.GelFree 
            FROM Band N 
            LEFT JOIN Bait B ON N.BaitID=B.ID 
            WHERE N.ID='$item_ID'";
    $SQL2 ="SELECT E.ID,
            E.Name,
            E.GrowProtocol,
            E.IpProtocol, 
            E.DigestProtocol,
            E.PeptideFrag,
            E.Notes
            FROM Band B LEFT JOIN Experiment E ON E.ID=B.ExpID 
            WHERE B.ID='$item_ID'";
  }
  $itemInfoArr = $HITSDB->fetch($SQL);
  $Bait_ID = $itemInfoArr['ID'];
  $GelFree =$itemInfoArr['GelFree'];
  $ret_tmp = "&nbsp;";
  
  if($ExperimentArr = $HITSDB->fetchAll($SQL2)){
    if($itemType == "Bait"){
      $tmp_head = 'B';
    }elseif($itemType == "Experiment"){
      $tmp_head = 'E';
    }elseif($itemType == "Band"){
      $tmp_head = 'S';
    }
    $colorBar_div_id = $tmp_head.$item_ID;
    $detail_div_id = $colorBar_div_id."_a";
    $colorBarStr_all = "\r\n<DIV ID='$colorBar_div_id'>\r\n";   
    $colorBarStr_all .= "<table border='0' cellpadding='1' cellspacing='0'><tr><td>\r\n";
    $itemAtrArr['num_Exp'] = count($ExperimentArr);
    
    $colorBarStr_all .= "<a class='tipButton' title='status detail' href=\"javascript: status_detail('$item_ID','$itemType')\">\r\n";
    
    foreach($ExperimentArr as $ExperimentValue){    
      $colorBarStr = "";
			$sub_sql = '';
			if($itemType == "Bait" || $itemType == "Experiment"){
				$typeID = $ExperimentValue['ID'];
    		$typeID_name = 'ExpID';
        //$sub_sql = " AND BaitID='$item_ID'";
  		}else{
				$typeID = $item_ID;
    		$typeID_name = 'ID';
  		}
		  $SQL = "SELECT
		         `ID`,
		         `Location`,
		         `RawFile` 
		         FROM `Band` 
		         WHERE $typeID_name='".$typeID."'";
		  $BandArr = $HITSDB->fetchAll($SQL);
			
      if($GelFree){
        $GelFreeColorlocal = $GelFreeColor;
      }else{
        $GelFreeColorlocal = $unGelFreeColor;
      }

      $colorBarStr .= display_color_bar($GelFreeColorlocal);
      
      $ProtocolMess = '';
      $bgColor = $EmptyColor;
      if($ExperimentValue['GrowProtocol']){
        $ProtocolMess = get_Protocol($ExperimentValue['GrowProtocol'],1);
        $bgColor = $GrowColor;
      }
      
      $colorBarStr .= display_color_bar($bgColor);
      
      $ProtocolMess = '';
      $bgColor = $EmptyColor;
      if($ExperimentValue['IpProtocol']){
        $ProtocolMess = get_Protocol($ExperimentValue['IpProtocol'],2);
        $bgColor = $IpColor;
      }
      $colorBarStr .= display_color_bar($bgColor);
      
      $ProtocolMess = '';
      $bgColor = $EmptyColor;
      if($ExperimentValue['DigestProtocol']){
        $ProtocolMess = get_Protocol($ExperimentValue['DigestProtocol'],3);
        $bgColor = $DigestColor;
      }
      $colorBarStr .= display_color_bar($bgColor);
      
      $ProtocolMess = '';
      $bgColor = $EmptyColor;
      if($ExperimentValue['PeptideFrag']){
        $ProtocolMess = get_Protocol($ExperimentValue['PeptideFrag'],4);
        $bgColor = $LC_MSColor;
      }
      
			$colorBarStr .= display_color_bar($bgColor);
      $statusArr = return_bands_str($BandArr);

      if($statusArr['num_files']){
        $colorBarStr .= display_color_bar($RawFileColor,$statusArr['num_files']);
      }else{
        $colorBarStr .= display_color_bar($EmptyColor);
      }
      
      $total_hits_number = $statusArr['num_hitsTppProt'] + $statusArr['num_hits'] + $statusArr['num_hitsGeneLevel'];
      if($total_hits_number){
        $colorBarStr .= display_color_bar($statusArr['hitsColor'],$total_hits_number);
      }else{
        if($statusArr['is_hits_parsed']){
          $colorBarStr .= display_color_bar($statusArr['hitsColor'],'0');
        }else{
          $colorBarStr .= display_color_bar($EmptyColor);
        }  
      }
      if($itemType == "Band"){
        $machine_name_arr = $statusArr['machine_name'];
        $icom_str = '';
        foreach($machine_name_arr as $machine_name_val){
          if(isset($machine_name_icon_arr) && array_key_exists($machine_name_val, $machine_name_icon_arr)){
            $icon_file_name = $machine_name_icon_arr[$machine_name_val];
          }else{
            $icon_file_name = get_machine_icon($machine_name_val);
            if(isset($machine_name_icon_arr)){
              $machine_name_icon_arr[$machine_name_val] = $icon_file_name;
            }
          }
          $icom_str .= "</a><span style='position:relative; top:4px;'>
                          <img src='$icon_file_name' height='17' width='17' border='1'/>
                        </span>";
        }
        $colorBarStr .= $icom_str;
      
        $tmp_str = '';
         
        foreach($BandArr as $BandVal){
          if($BandVal['ID']){    
            $SQL = "SELECT P.ID,
                       P.Name,
                       P.Type,
                       P.Detail,
                       B.ID AS BandGroupID 
                       FROM BandGroup B
                       LEFT JOIN Protocol P
                       ON (P.ID=B.NoteTypeID) 
                       WHERE B.RecordID='".$BandVal['ID']."'
                       AND B.Note COLLATE latin1_general_cs LIKE 'SAM_%'";
                       //AND B.Note LIKE 'SAM\_%'";
            $tmp_pro_arr = $HITSDB->fetchAll($SQL);
            foreach($tmp_pro_arr as $tmp_pro_val){
              if($tmp_pro_val['Name']){
                $selected_prot_div_id = $AccessProjectID."_".$tmp_pro_val['ID'];
                $tmp_arr = explode('_', $tmp_pro_val['Type'], 2);
                $protocol_type = $tmp_pro_val['Type'];
                $file_name = "./protocol_detail_pop.php?modal=this_project&outsite_script=1&selected_type_div_id=$protocol_type&selected_prot_div_id=$selected_prot_div_id";
                $tmp_str .= "<a href=\"javascript: popwin('$file_name','720','700');\" class=button>
                                <span style='position:relative;border: blue solid 0px;color:white;margin:0px 1px 0px 0px;top:1px;padding:0px 0px 0px 0px;height:19px;".((isset($type_color_pair[$tmp_pro_val['Type']]))?'background-color:'.$type_color_pair[$tmp_pro_val['Type']]:'')."'>
                                  SP".$tmp_pro_val['ID']."
                                </span>
                             </a>";
              }
            }
          }
        }
        $colorBarStr .= $tmp_str;
      }
      
      $colorBarStr .= "\r\n";
      $colorBarStr_all .= $colorBarStr;
      $itemAtrArr['num_hits'] += $statusArr['num_hits'];
      $itemAtrArr['num_hitsGeneLevel'] += $statusArr['num_hitsGeneLevel'];
      $itemAtrArr['num_hitsTppProt'] += $statusArr['num_hitsTppProt'];
      $itemAtrArr['num_hitsTppPep'] += $statusArr['num_hitsTppPep'];
      $itemAtrArr['num_files'] += $statusArr['num_files'];
      $itemAtrArr['total_hits'] += $statusArr['total_hits'];
    }
    $colorBarStr_all .= "</a>\r\n";
    if($itemAtrArr['num_hits'] || $itemAtrArr['num_hitsTppProt'] || $itemAtrArr['num_hitsTppPep'] || $itemAtrArr['num_hitsGeneLevel']){
      $itemAtrArr['has_report'] = 1;
    }
    $agl_item_ID = $item_ID;
    $ret_arr = get_link_for_note($agl_item_ID,$agl_itemType,$toggle,$passed_type);
    $ret_tmp = $ret_arr[0];
    $colorBarStr_all .= "</td><td>". $ret_tmp ."</td></tr></table>\r\n";
    $colorBarStr_all .= "</DIV>\r\n";
    $colorBarStr_all .= "<DIV id='$detail_div_id' CLASS='status_show_1'></DIV>\r\n";
    echo $colorBarStr_all;
  }else{
    $agl_item_ID = $item_ID;
    $ret_arr = get_link_for_note($agl_item_ID,$agl_itemType,$toggle,$passed_type);
    $ret_tmp = $ret_arr[0];
    $colorBarStr_all = "<DIV><table><tr><td>". $ret_tmp ."</td></tr></table></DIV>\r\n";
    echo $colorBarStr_all;
  }
  
  if($itemAtrArr['num_hits']){
    $itemAtrArr['hitType'] = 'normal';
  }elseif($itemAtrArr['num_hitsTppProt']){
    $itemAtrArr['hitType'] = 'TPP';
  }elseif($itemAtrArr['num_hitsTppPep']){
    $itemAtrArr['hitType'] = 'TPPpep';
  }else{
    $itemAtrArr['hitType'] = 'normal';
  }
  if(isset($ret_arr[1]) && $ret_arr[1]){
    $itemAtrArr['has_note'] = $ret_arr[1];
  }else{
    $itemAtrArr['has_note'] = 0;
  }
  return $itemAtrArr;
}

function return_bands_str($BandArr){
	global $EmptyColor;
  $HasHitsColor = "#5b52ad";
	$expDataArr = array ('num_files' => 0, 'num_hits' => 0, 'num_hitsTppProt' => 0, 'num_hitsTppPep' => 0,'num_hitsGeneLevel' => 0, 'is_hits_parsed' => 0, 'has_report' => 0); 
  $machine_name_arr = array();
    
  foreach($BandArr as $BandValue){
   	if($BandValue['RawFile']){    
     	$rawFileInfoArr = explode(";",$BandValue['RawFile']);
      $expDataArr['is_hits_parsed'] = 0;
      foreach($rawFileInfoArr as $rawValue){
       	$fileTableIDarr = explode(":",$rawValue);
       	if(count($fileTableIDarr) != 2) continue;
        if(!in_array($fileTableIDarr[0], $machine_name_arr)){
          array_push($machine_name_arr, $fileTableIDarr[0]);
        }
       	$fileTableIDarr[1] = preg_replace("/[,]$/",'', $fileTableIDarr[1]);
       	$expDataArr['num_files']++;
        $theTableName = $fileTableIDarr[0];
       	$theFileID = $fileTableIDarr[1];
        $is_hits_parsed = is_hits_parsed($theFileID, $theTableName);
		    $expDataArr['is_hits_parsed'] += $is_hits_parsed;
     	}        
    }
   	$tmp_num_arr = get_hit_num($BandValue['ID']);
  	$expDataArr['num_hits']	+= $tmp_num_arr['hits'];
    $expDataArr['num_hitsGeneLevel']	+= $tmp_num_arr['hitsGeneLevel'];
  	$expDataArr['num_hitsTppProt'] += $tmp_num_arr['hitsTppProt'];
    $expDataArr['num_hitsTppPep'] += $tmp_num_arr['hitsTppPep'];
 	}
  $total_hits = $expDataArr['num_hits'] + $expDataArr['num_hitsTppProt'] + $expDataArr['num_hitsGeneLevel']; 
  $expDataArr['total_hits'] = $total_hits;
  
	if($expDataArr['num_hits'] || $expDataArr['num_hitsTppProt'] || $expDataArr['num_hitsTppPep'] || $expDataArr['num_hitsGeneLevel']){
    $expDataArr['is_hits_parsed'] = 1;
    $expDataArr['has_report'] = 1;
  }
  if($expDataArr['is_hits_parsed']){
    $expDataArr['hitsColor'] = $HasHitsColor;
  }else{
    $expDataArr['hitsColor'] = $EmptyColor;
  }
  $expDataArr['machine_name'] = $machine_name_arr;
	return $expDataArr;
}

function get_machine_taskID_str(){
  global $task_id_arr,$msManagerDB;
  global $task_id_str,$TPPtask_id_str;
  
  foreach($task_id_arr as $key => $val){
    $str = $TPP_str = '';
    $task_id_arr = $val;
    foreach($task_id_arr as $task_id_key => $task_id_val){
      $id_str = implode(",", $task_id_val);
      if($task_id_key == 'TaskID'){
        $str = "&nbsp;&nbsp;[" . $key . ": Task " . $id_str . "]";        
      }elseif($task_id_key == 'TppTaskID'){
        $DB_table_name = $key."tppTasks";
        $SQL = "SELECT `ID`,`SearchTaskID` FROM $DB_table_name WHERE `ID` IN($id_str) ORDER BY SearchTaskID DESC,ID DESC";
        $tpp_task_arr = $msManagerDB->fetchAll($SQL);
        $id_str = '';
        foreach($tpp_task_arr as $tpp_task_val){
          if($id_str) $id_str .= ',';
          $id_str .= $tpp_task_val['SearchTaskID'].'_'.$tpp_task_val['ID'];
        }
        $TPP_str =  "&nbsp;&nbsp;[" . $key . ": Task " . $id_str . "]";;
      }
    }
    if($str) $task_id_str .= $str;
    if($TPP_str) $TPPtask_id_str .= $TPP_str;
  }
}
?>