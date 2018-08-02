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

class cytoscape_export{
  var $BaitGeneNameArr;
  var $HitGeneNameArr;
  var $AttrArr;
  var $hit_is_bait_arr;
  var $PROTEINDB;
  var $FrequencyArr;
  var $outer_circle_hit_arr;
  var $all_hits_id_color_arr;
  var $inner_circle_hit_arr;
  var $matchedEdgeArr_1;
  var $matchedGeneIDArr_1;
  var $unMatchedEdgeArr_1;
  var $Bait_info_arr;
  var $bait_hitOverlap_note_color_arr;
  var $EdgeArr_2;
  var $single_hit_count_arr;
  var $bait_name_index_arr;
  var $bait_position_arr;
  var $outer_start_radians_arr;
  var $nodeIDarr;
  var $node_color_arr;
  var $attTypeArr;
  var $titleArr;
  var $outer_circle_counter;
  var $inner_r;
  var $outer_r;
  var $GRAPH_VIEW_ZOOM;
  var $GRAPH_VIEW_CENTER_X;
  var $GRAPH_VIEW_CENTER_Y;
  var $single_radians;
  var $outer_single_radians;
  var $current_radians;
  var $handle_write;
  var $lineEnd;
  var $nodeH;
  var $nodeW;
  var $nodeType;
  var $counter;
  var $unMatchedNodeColor;
  
  function cytoscape_export($Protein_db){
    $this->PROTEINDB = $Protein_db;
    $this->BaitGeneNameArr = array();
    $this->HitGeneNameArr = array();
    $this->AttrArr = array();
    $this->hit_is_bait_arr = array();
    $this->FrequencyArr = array();
    $this->outer_circle_hit_arr = array();
    $this->all_hits_id_color_arr = array();
    $this->inner_circle_hit_arr = array();
    $this->matchedEdgeArr_1 = array();
    $this->matchedGeneIDArr_1 = array();
    $this->unMatchedEdgeArr_1 = array();
    $this->Bait_info_arr = array();
    $this->bait_hitOverlap_note_color_arr = array();
    $this->EdgeArr_2 = array();
    $this->single_hit_count_arr = array();
    $this->bait_name_index_arr = array();
    $this->bait_position_arr = array();
    $this->outer_start_radians_arr = array();
    $this->nodeIDarr = array();
    $this->node_color_arr = array('r'=>"#ff0000",'w'=>"#ffffff",'g'=>"#e4e4e4");
    $this->attTypeArr = array('real','real','real','real','real','real');
    
    $this->titleArr = array();
    $this->outer_circle_counter = 0;
    $this->current_radians = -10000;
    $this->lineEnd = "\n";
    $this->nodeH = 35.0;
    $this->nodeW = 35.0;
    $this->nodeType = "ELLIPSE";
    $this->counter = 1;
    $this->unMatchedNodeColor = '#e4e4e4';
    $this->hits_bait_arr = array();
    $this->hits_geneid_exchange_arr = array();
  }
  
  function get_prohits_data($handle_read,$sendBy,$baitGeneIDarr,$allBaitgeneID_str,$exist_bait_geneID_name_arr){
    global $proteinDB;
    global $is_upload;
    if($allBaitgeneID_str){
      $allBaitgeneID_arr = explode(",",$allBaitgeneID_str);
    }    
    //$this->create_hits_bait_exchange_arr();
    $this->hits_geneid_exchange_arr = $exist_bait_geneID_name_arr;
    $bait_name_arr = array();
    $buffer = fgets($handle_read);
    $buffer = trim($buffer);
    $this->titleArr = explode(",",$buffer);
    
    for($k = 0; $k<5; $k++) array_shift($this->titleArr);
    if($sendBy == 'item_repot') array_pop($this->titleArr);
//print_r($this->titleArr);exit;
  /*$buffer_ = "[0]=Bait___BaitID
                [1]=Bait___GeneID 
             [2]=level3___GeneName
             [3]=level3___GeneID
             [4]=level3___HitGI
             [5]=level3___Expect
             [6]=level3___Pep_num
             [7]=level3___Pep_num_uniqe
             [8]=level3___Coverage
             [9]=level3___Frequency
             [10]=Bait___BaitID";*/
             
    while(!feof($handle_read)){
      $buffer = fgets($handle_read);
      $buffer = trim($buffer); 
      if(preg_match('/^bioGrid_only:/', $buffer)) break;
      if(!$buffer) continue;
      $tmpAttrArr = explode(',',$buffer);
      if($sendBy == "item_repot"){
        $tmpAttrArr[0] = $tmpAttrArr[10]. " " .$tmpAttrArr[0];
        if(strstr($tmpAttrArr[9], "%")){
          $tmpAttrArr[9] = substr($tmpAttrArr[9], 0, -1);  
        }
        if($tmpAttrArr[1] == $tmpAttrArr[3]) continue; //======================
      }else{ 
        $tmp_arr = explode("|",$tmpAttrArr[1]);
        if(in_array($tmpAttrArr[3], $tmp_arr)) continue; //==================== 
        
        if(!$is_upload){
          $ttemp_arr = explode("|",$tmpAttrArr[0]);
          $tmp_str = '';
          $pattern = '/(.+)?\s(\S+)$/';;
          foreach($ttemp_arr as $ttemp_val){
            if(preg_match($pattern, $ttemp_val, $matches)){
              if($tmp_str) $tmp_str .= "|";
              $tmp_str .= $matches[1];
            }
          }       
          $tmpAttrArr[0] = $tmp_str;
        }
      }
      $tmpAttrArr[0] = trim($tmpAttrArr[0]);     
      if(!in_array($tmpAttrArr[0], $this->BaitGeneNameArr)){
        array_push($this->BaitGeneNameArr, $tmpAttrArr[0]);
      }
      
      if(!$tmpAttrArr[2]) $tmpAttrArr[2] = $tmpAttrArr[4];
      if(!$tmpAttrArr[3]) $tmpAttrArr[3] = $tmpAttrArr[4];
     
      if(array_key_exists($tmpAttrArr[3], $this->hits_geneid_exchange_arr)){
        $tmp_array = $this->hits_geneid_exchange_arr[$tmpAttrArr[3]];
        foreach($tmp_array as $tmp_value){
          $tmpAttrArr[2] = $tmp_value;
          $tmpAttrArr[2] = str_replace(",", ";",$tmpAttrArr[2]);
          if(!array_key_exists($tmpAttrArr[2], $this->HitGeneNameArr)){
            $this->HitGeneNameArr[$tmpAttrArr[2]] = $tmpAttrArr[3];
            if(!$tmpAttrArr[9]) $tmpAttrArr[9] = 0;
            $this->FrequencyArr[$tmpAttrArr[3]] = $tmpAttrArr[9];
            array_push($this->hit_is_bait_arr, $tmpAttrArr[2]);
          }
          $tmpBaitHit = $tmpAttrArr[0]."??".$tmpAttrArr[2];
          $is_du = $this->add_att($tmpBaitHit,$tmpAttrArr);
          if($is_du) continue;
        	if(!array_key_exists($tmpAttrArr[0], $this->outer_circle_hit_arr)){
          	$this->outer_circle_hit_arr[$tmpAttrArr[0]] = array();
        		$this->outer_circle_hit_arr[$tmpAttrArr[0]][$tmpAttrArr[2]] = $tmpAttrArr[3];
        	}else{
        		$this->outer_circle_hit_arr[$tmpAttrArr[0]][$tmpAttrArr[2]] = $tmpAttrArr[3];
        	}          
        	if(!array_key_exists($tmpAttrArr[3], $this->all_hits_id_color_arr)){
        		$this->all_hits_id_color_arr[$tmpAttrArr[3]] = "r";
        	}else{
            if(!in_array($tmpAttrArr[3], $this->inner_circle_hit_arr)){
        			$this->inner_circle_hit_arr[$tmpAttrArr[2]] = $tmpAttrArr[3];
        		}		
        	}         
        }
      }else{
        $tmpAttrArr[2] .= "(".$tmpAttrArr[3].")";
        $tmpAttrArr[2] = str_replace(",", ";",$tmpAttrArr[2]);
        if(!array_key_exists($tmpAttrArr[2], $this->HitGeneNameArr)){
          $this->HitGeneNameArr[$tmpAttrArr[2]] = $tmpAttrArr[3];
          if(!$tmpAttrArr[9]) $tmpAttrArr[9] = 0;
          $this->FrequencyArr[$tmpAttrArr[3]] = $tmpAttrArr[9];
        } 
        $tmpBaitHit = $tmpAttrArr[0]."??".$tmpAttrArr[2];
        $is_du = $this->add_att($tmpBaitHit,$tmpAttrArr);
        if($is_du) continue;
      	if(!array_key_exists($tmpAttrArr[0], $this->outer_circle_hit_arr)){
        	$this->outer_circle_hit_arr[$tmpAttrArr[0]] = array();
      		$this->outer_circle_hit_arr[$tmpAttrArr[0]][$tmpAttrArr[2]] = $tmpAttrArr[3];
      	}else{
      		$this->outer_circle_hit_arr[$tmpAttrArr[0]][$tmpAttrArr[2]] = $tmpAttrArr[3];
      	}
      	if(!array_key_exists($tmpAttrArr[3], $this->all_hits_id_color_arr)){
      		$this->all_hits_id_color_arr[$tmpAttrArr[3]] = "w";
      	}else{
          if(!in_array($tmpAttrArr[3], $this->inner_circle_hit_arr)){
      			$this->inner_circle_hit_arr[$tmpAttrArr[2]] = $tmpAttrArr[3];
      		}		
      	}
      }
    }
/*echo "<pre>";
echo "\$this->BaitGeneNameArr<br>";
print_r($this->BaitGeneNameArr); 
echo "\$this->HitGeneNameArr<br>";
print_r($this->HitGeneNameArr); 
echo "\$this->inner_circle_hit_arr<br>";    
print_r($this->inner_circle_hit_arr);
echo "\$this->outer_circle_hit_arr<br>"; 
print_r($this->outer_circle_hit_arr);
echo "\$this->all_hits_id_color_arr<br>";
print_r($this->all_hits_id_color_arr);      
echo "<pre>";    
//exit;*/
  }
  
  function get_biogrid_data($matchGred_handle,$bio_checked_str){
    $bio_checked_arr = explode(",",$bio_checked_str);
    $matchedGeneNameArr_1 = array();
    $flag = "";
    while(!feof($matchGred_handle)){
      $buffer = fgets($matchGred_handle, 4096);
      $buffer = trim($buffer);
      if(!$buffer) continue;
      if($buffer == "edge_info"){
        $flag = "1";
        continue;
      }elseif($buffer == "bait_info"){
        $flag = "2";
        continue;
      }elseif($buffer == "matched_hits_node"){
        break;
      }      
      if($flag == "1"){     
        $tmpArr1 = explode(",",$buffer);
        if(!isset($tmpArr1[1])){
          echo $buffer;
          continue;
        }
        
        $tmpArr1[1] .= "(".$tmpArr1[3].")";
        $tmpArr1[1] = str_replace(",", ";",$tmpArr1[1]);
  			$tmpArr2 = explode('??',$tmpArr1[1]);
//===============================================================================        
        if(array_key_exists($tmpArr1[3], $this->hits_geneid_exchange_arr)){
          $tmp_array = $this->hits_geneid_exchange_arr[$tmpArr1[3]];
          foreach($tmp_array as $tmp_value){
            $tmpArr2[1] = $tmp_value;
//=====================================================================================         
            if($tmpArr1[0]){
              $this->matchedEdgeArr_1[$tmpArr1[1]] = $tmpArr1[2];
      				if(!in_array($tmpArr2[1], $matchedGeneNameArr_1)){
      					$this->matchedGeneIDArr_1[$tmpArr2[1]] = $tmpArr1[3];
      	      	$matchedGeneNameArr_1[$tmpArr1[3]] = $tmpArr2[1];
      				}	
            }else{
              $this->unMatchedEdgeArr_1[$tmpArr1[1]] = $tmpArr1[2];
      				$this->outer_circle_hit_arr[$tmpArr2[0]][$tmpArr2[1]] = $tmpArr1[3];
      				if(!array_key_exists($tmpArr1[3], $this->all_hits_id_color_arr)){
      					$this->all_hits_id_color_arr[$tmpArr1[3]] = "r";
      				}else{
      					if(!in_array($tmpArr1[3], $this->inner_circle_hit_arr)){
      						$this->inner_circle_hit_arr[$tmpArr2[1]] = $tmpArr1[3];
      					}
      				}
            }
          }  
        }else{
          if($tmpArr1[0]){
            $this->matchedEdgeArr_1[$tmpArr1[1]] = $tmpArr1[2];
    				if(!in_array($tmpArr2[1], $matchedGeneNameArr_1)){
    					$this->matchedGeneIDArr_1[$tmpArr2[1]] = $tmpArr1[3];
    	      	$matchedGeneNameArr_1[$tmpArr1[3]] = $tmpArr2[1];
    				}	
          }else{
            $this->unMatchedEdgeArr_1[$tmpArr1[1]] = $tmpArr1[2];
    				$this->outer_circle_hit_arr[$tmpArr2[0]][$tmpArr2[1]] = $tmpArr1[3];
    				if(!array_key_exists($tmpArr1[3], $this->all_hits_id_color_arr)){
    					$this->all_hits_id_color_arr[$tmpArr1[3]] = "g";
    				}else{
    					if(!in_array($tmpArr1[3], $this->inner_circle_hit_arr)){
    						$this->inner_circle_hit_arr[$tmpArr2[1]] = $tmpArr1[3];
    					}
    				}
          }
        }
      }elseif($flag == "2"){
        $tmpArr1 = explode(",",$buffer);
        if(count($tmpArr1) == 2){
          $this->Bait_info_arr[$tmpArr1[0]] = $tmpArr1[1];
        }  
      }
    }
        
    foreach($this->outer_circle_hit_arr as $key => $value){
      $tmpGeneID = '';
      if(array_key_exists($key, $this->Bait_info_arr)){
        $tmpGeneID = $this->Bait_info_arr[$key];
      }  
      $this->bait_hitOverlap_note_color_arr[$key] = "r,$tmpGeneID";
    }
    foreach($matchedGeneNameArr_1 as $key => $value){
      $this->outer_circle_hit_arr[$value] = array();
      $this->bait_hitOverlap_note_color_arr[$value] = "w,$key";
    }
   
    if($bio_checked_arr){
      if($this->matchedGeneIDArr_1){
        $baitGeneIDstr_2 = implode("|", $this->matchedGeneIDArr_1);
        $gride_reponse_arr_2 = array();
        //echo @date("D M j G:i:s T Y")."<br>";                     
        get_bioGride_response($baitGeneIDstr_2, $gride_reponse_arr_2);
        //echo @date("D M j G:i:s T Y")."<br>";
        foreach($gride_reponse_arr_2 as $buffer){
          $buffer = trim($buffer);
          if(!$buffer) continue;  
          $tmpGeneArr = explode("\t",$buffer);
          if(count($tmpGeneArr) != 4){
            echo $buffer."<br>";
            continue;
          }
          if($tmpGeneArr[1] == $tmpGeneArr[0]) continue; 
          $gridType = substr($tmpGeneArr[2], 0, 1).substr($tmpGeneArr[3], 0, 1);
          if(!in_array($gridType, $bio_checked_arr)) continue;
          if(in_array($tmpGeneArr[0], $this->matchedGeneIDArr_1)){
            $level1GeneID = $tmpGeneArr[0];
            $level2GeneID = $tmpGeneArr[1];
          }elseif(in_array($tmpGeneArr[1], $this->matchedGeneIDArr_1)){
            $level1GeneID = $tmpGeneArr[1];
            $level2GeneID = $tmpGeneArr[0];
          }else{
            continue;
          }
          if(array_key_exists($level2GeneID, $matchedGeneNameArr_1)) continue;
          $SQL = "SELECT `GeneName` FROM `Protein_Class` WHERE `EntrezGeneID`='$level2GeneID'";
          if($tmpProteinArr = $this->PROTEINDB->fetch($SQL)){
            if($tmpProteinArr['GeneName']){
              $level2GeneName = $tmpProteinArr['GeneName'];
            }else{
              $level2GeneName = $level2GeneID;
            }  
          }else{
            $level2GeneName = $level2GeneID;
          }
          $level2GeneName .= "(".$level2GeneID.")";
          $level2GeneName = str_replace(",", ";",$level2GeneName);
          $level1GeneName = $matchedGeneNameArr_1[$level1GeneID];
  				$this->outer_circle_hit_arr[$level1GeneName][$level2GeneName] = $level2GeneID;
  				if(!array_key_exists($level2GeneID, $this->all_hits_id_color_arr)){
            $this->all_hits_id_color_arr[$level2GeneID] = "g";
  				}else{
  					if(!in_array($level2GeneID, $this->inner_circle_hit_arr)){
  						$this->inner_circle_hit_arr[$level2GeneName] = $level2GeneID;
  					}		
  				}
          $EdgeIndex = $level1GeneName."??".$level2GeneName;
          if(!array_key_exists($EdgeIndex, $this->EdgeArr_2)){
            $this->EdgeArr_2[$EdgeIndex] = $gridType;
          }else{
            if(!stristr($this->EdgeArr_2[$EdgeIndex], $gridType)){
              $this->EdgeArr_2[$EdgeIndex] .= ",".$gridType;
            }
          }
        }
      }
    }
    $this->uniquick_data();
    return;
  }

  function uniquick_data(){
    $this->inner_circle_hit_arr = array_diff($this->inner_circle_hit_arr, $this->matchedGeneIDArr_1);
    foreach($this->outer_circle_hit_arr as $key => $value){
      array_push($this->bait_name_index_arr, $key);
      $tmpArr = $value;
      $newArr = array_diff($tmpArr, $this->inner_circle_hit_arr);
      $newArr = array_diff($newArr, $this->matchedGeneIDArr_1);      
      $this->outer_circle_hit_arr[$key] = $newArr;
      $tmpCounter = count($newArr);
      $this->outer_circle_counter += $tmpCounter;
      $this->single_hit_count_arr[$key] = $tmpCounter;
    }
  }

  function Layout_design(){
    $this->inner_circle_hit_arr_counter = count($this->inner_circle_hit_arr);
    foreach($this->single_hit_count_arr as $key => $value){
      array_push($this->bait_position_arr, floor($value*$this->inner_circle_hit_arr_counter/$this->outer_circle_counter));
    }
    $this->bait_position_arr_counter = count($this->bait_position_arr);
    for($i=0; $i<$this->bait_position_arr_counter; $i++){
      if($i !== 0){
        $this->bait_position_arr[$i] = $this->bait_position_arr[$i] + $this->bait_position_arr[$i-1];
      }
    }
    for($i=$this->bait_position_arr_counter-1; $i>=0; $i--){
      if($i !== 0){
        $this->bait_position_arr[$i] = round(($this->bait_position_arr[$i] - $this->bait_position_arr[$i-1])/2 + $this->bait_position_arr[$i-1] - $this->bait_position_arr[0]/2);
      }else{
        $this->bait_position_arr[$i] = 0;
      }
    }
    $inner_circle_counter = count($this->outer_circle_hit_arr) + count($this->inner_circle_hit_arr);
    if($inner_circle_counter == 1){
      $this->inner_r = 0;
    }else{
      $inner_circle_length = 1.5 * $inner_circle_counter;
      $this->inner_r = $inner_circle_length /(2 * M_PI);
      if($this->inner_r < 2) $this->inner_r = 2;
    }
    $this->outer_r = $this->inner_r + round($this->inner_r/3);
    $outer_circle_length = (2 * M_PI) * $this->outer_r;
    $diff_1 = $outer_circle_length - (1.3 * $this->outer_circle_counter);
    if($diff_1 < 0){
      $outer_circle_length = 1.3 * $this->outer_circle_counter;
      $this->outer_r = $outer_circle_length/(2 * M_PI);
    }
    $this->inner_r = 35 * $this->inner_r;
    $this->outer_r = 35 * $this->outer_r;

    $this->GRAPH_VIEW_ZOOM = (0.9 * 400)/ $this->outer_r;
    $this->GRAPH_VIEW_CENTER_X = $this->outer_r;
    $this->GRAPH_VIEW_CENTER_Y = $this->outer_r;

    $this->single_radians = (2 * M_PI) / $inner_circle_counter;
    $this->outer_single_radians = (2 * M_PI) / $outer_circle_length;
  }
  
  function graph($handle_write){
    global $orderby,$fieldIndexArr,$hitType,$webstartDir,$AccessUserID,$colorArrSet;
    $dir = str_replace("/Prohits", "..", CYTOSCAPE_ROOT_PATH);
    $dir_arr['lib'] = $dir."/lib";
    $dir_arr['plugins'] = $dir."/plugins";
    
    $this->handle_write = $handle_write;
    $backgroundColor = "#ccccff";
    $timeStamp =  @date("Y-m-d G:i:s");
    $title = "Prohits Report";
    $aa = '';
    $powerColorIndex = 'Pep_num';
    get_colorArrSets($powerColorIndex,$colorArrSet,$aa);
    $bait_r = '213';
    $bait_g = '0';
    $bait_b = '0';
    $hit_r = '0';
    $hit_g = '0';
    $hit_b = '255';

    $titleLine = "<?php xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
    <graph label=\"$title\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" xmlns:cy=\"http://www.cytoscape.org\" xmlns=\"http://www.cs.rpi.edu/XGMML\" >
      <att name=\"documentVersion\" value=\"1.1\"/>
      <att name=\"networkMetadata\">
        <rdf:RDF>
          <rdf:Description rdf:about=\"http://www.cytoscape.org/\">
            <dc:type>Protein-Protein Interaction</dc:type>
            <dc:description>N/A</dc:description>
            <dc:identifier>N/A</dc:identifier>
            <dc:date>$timeStamp</dc:date>
            <dc:title>$title</dc:title>
            <dc:source>http://www.cytoscape.org/</dc:source>
            <dc:format>Cytoscape-XGMML</dc:format>
          </rdf:Description>
        </rdf:RDF>
      </att>
      <att type=\"string\" name=\"backgroundColor\" value=\"#ccccff\"/>
      <att type=\"real\" name=\"GRAPH_VIEW_ZOOM\" value=\"$this->GRAPH_VIEW_ZOOM\"/>
      <att type=\"real\" name=\"GRAPH_VIEW_CENTER_X\" value=\"$this->GRAPH_VIEW_CENTER_X\"/>
      <att type=\"real\" name=\"GRAPH_VIEW_CENTER_Y\" value=\"$this->GRAPH_VIEW_CENTER_Y\"/>$this->lineEnd";

    fwrite($this->handle_write, $titleLine);  
      
    $this->bait_name_index_arr_2 = $this->bait_name_index_arr;    
    if($this->inner_r){
      $this->write_node_group($this->inner_circle_hit_arr,$this->single_radians,0,$this->inner_r);
    }else{
      $this->write_bait_node($this->single_radians,0,0,0);
    }  
    
    foreach($this->bait_name_index_arr_2 as $bait_name){
      $hits_arr = $this->outer_circle_hit_arr[$bait_name];
      $this->write_outer_node_group($bait_name,$hits_arr,$this->outer_single_radians,$this->outer_r);
    }
    
    //-----------------------------------------------------------------
    $this->write_edge($this->EdgeArr_2,'#ffffcc',$this->handle_write);
    $this->write_edge($this->unMatchedEdgeArr_1,'#ffffcc',$this->handle_write);
    //------------------------------------------------------------------
    $lineWidth = "1";
    foreach($this->AttrArr as $key => $value){
      $tmpGeneName = explode('??',$key);
      $node1 = $tmpGeneName[0];
      $node2 = $tmpGeneName[1];
      $node2_arr = explode('(',$node2);
      $node2_tmp = $node2_arr[0];
      $sourceArrow = 0;
      $targetArrow = 0;
      if(in_array($node1, $this->BaitGeneNameArr)){
        $targetArrow = 3;
        $tmpIndex = $tmpGeneName[0]."??".$tmpGeneName[1];
      }elseif(in_array($node2, $this->BaitGeneNameArr)){
        $sourceArrow = 3;
        $tmpIndex = $tmpGeneName[1]."??".$tmpGeneName[0];
      }else{
        continue;
      }       
      $hitPropertyVal = $value[$fieldIndexArr[$orderby]];
      
//======================================================
if(!isset($this->nodeIDarr[$node1]) || !$this->nodeIDarr[$node1]) continue; //
if(!isset($this->nodeIDarr[$node2]) || !$this->nodeIDarr[$node2]) continue;
//======================================================       
      
      $edgeLine = "<edge label=\"$node1 (---) $node2_tmp\" source=\"".$this->nodeIDarr[$node1]."\" target=\"".$this->nodeIDarr[$node2]."\">$this->lineEnd";

      foreach($this->titleArr as $titleKey => $titleLable){
        if($titleKey == '6') continue;
        if($titleLable == "Project Frequency" || $titleLable == "Shared Frequency") continue;
        $tmpType = $this->attTypeArr[$titleKey];
        $edgeLine .= "  <att type=\"$tmpType\" name=\"".$titleLable."\" value=\"".(($value[$titleKey])?$value[$titleKey]:'0')."\"/>$this->lineEnd";
      }
      
      $colorIndex = '';
      $hitColorVal = $hitPropertyVal; 
      if($hitType == "GPM"){
        $hitColorVal = -1 * $hitColorVal;
      }
      if(array_key_exists($tmpIndex, $this->matchedEdgeArr_1)){
        $lineColor = $sourceArrowColor = $targetArrowColor = "#00e100";
        $edgeLine .= "  <att type=\"string\" name=\"BioGrid Type\" value=\"".$this->matchedEdgeArr_1[$tmpIndex]."\"/>$this->lineEnd";  
      }else{
        $lineColor = $sourceArrowColor = $targetArrowColor = color_num($hitColorVal, $colorIndex);
      }
      //$edgeLine .= "  <att type=\"string\" name=\"canonicalName\" value=\"$node1 (".$value[$fieldIndexArr[$orderby]].") $node2\"/>$this->lineEnd";
      //$edgeLine .= "  <att type=\"string\" name=\"interaction\" value=\"".$value[$fieldIndexArr[$orderby]]."\"/>$this->lineEnd";
      $edgeLine .= "  <graphics width=\"$lineWidth\" fill=\"$lineColor\" cy:sourceArrow=\"$sourceArrow\" cy:targetArrow=\"$targetArrow\" cy:sourceArrowColor=\"$sourceArrowColor\" cy:targetArrowColor=\"$targetArrowColor\" cy:edgeLabelFont=\"Default-0-10\" cy:edgeLineType=\"SOLID\" cy:curved=\"STRAIGHT_LINES\"/>$this->lineEnd";
      $edgeLine .= "</edge>$this->lineEnd";
      fwrite($this->handle_write, $edgeLine);
    }
    fwrite($this->handle_write, "</graph>$this->lineEnd");
    $cyto_tamplate = "./cyto_jnlp_tamplate.jnlp";
    $jnlp_read = fopen($cyto_tamplate, "r");
    $cyto_jnlp = $webstartDir.$AccessUserID."_cytoscape.jnlp";
    $jnlp_write = fopen($cyto_jnlp, "w");
    while(!feof($jnlp_read)){
      $buffer = fgets($jnlp_read);
      if(!trim($buffer)) continue;
      if(preg_match("/\<jar/", $buffer) && !preg_match("/\<jar href=\"cytoscape.jar\"\>\<\/jar\>/", $buffer)) continue;
      if(preg_match("/\<\/resources\>/", $buffer)){      
        foreach($dir_arr as $key => $val){
          if($handle_dir = opendir($val)){
            while(false !== ($file = readdir($handle_dir))){
              if($file == "." || $file == "..") continue; 
              fwrite($jnlp_write, "<jar href=\"$key/$file\"></jar>\n");
            }
            closedir($handle_dir);
          }  
        }
      }
      if(preg_match('/^update1:/', $buffer)){
        $buffer = "<jnlp codebase=\"http://".PROHITS_SERVER_IP.CYTOSCAPE_ROOT_PATH."\">\r\n";
      }elseif(preg_match('/^update2:/', $buffer)){
        $buffer = "<argument>http://".PROHITS_SERVER_IP."/Prohits/TMP/webstart/".$AccessUserID."_cytoscape.xgmml</argument>\r\n";
      }
      fwrite($jnlp_write, $buffer);
    }
    fclose($jnlp_read);
    fclose($jnlp_write);
		header("Location: cytoscape_web.php?xgmml_file=/TMP/webstart/".$AccessUserID."_cytoscape.xgmml");
  }  

  function add_att($tmpBaitHit,$tmpAttrArr){
    global $sortByIndex;  
    $tmpArr = array();
    for($i=5; $i<count($tmpAttrArr)-1; $i++){
      array_push($tmpArr, $tmpAttrArr[$i]);
    }
    if(!array_key_exists($tmpBaitHit, $this->AttrArr)){ 
      $this->AttrArr[$tmpBaitHit] = $tmpArr;
      return 0;
    }else{
      if($this->AttrArr[$tmpBaitHit][$sortByIndex] < $tmpArr[$sortByIndex]){
        $this->AttrArr[$tmpBaitHit] = $tmpArr;
      }
      return 1;
    }  
  }
  
  function write_edge(&$AttrArr,$lineColor){
    $lineWidth =1;
    foreach($AttrArr as $key => $value){
      $tmpGeneName = explode('??',$key);
      $node1 = $tmpGeneName[0];
      $node2 = $tmpGeneName[1];
      if(!array_key_exists($node1, $this->nodeIDarr)) continue;
      $node2_arr = explode('(',$tmpGeneName[1]);
      $node2_tmp = $node2_arr[0];
      $sourceArrow = 0;
      $targetArrow = 0;
      $sourceArrowColor = $lineColor;
      $targetArrowColor = $lineColor;
      
      //======================================================
      if(!$this->nodeIDarr[$node1]) continue;
      if(!$this->nodeIDarr[$node2]) continue;
      //======================================================
      
      $edgeLine = "<edge label=\"$node1 (-----) $node2_tmp\" source=\"".$this->nodeIDarr[$node1]."\" target=\"".$this->nodeIDarr[$node2]."\">$this->lineEnd";
      //$edgeLine = "<edge label=\"$node1 (-----) $node2\" source=\"".$this->nodeIDarr[$node1]."\" target=\"".$this->nodeIDarr[$node2]."\">$this->lineEnd";    
      
      $edgeLine .= "  <att type=\"string\" name=\"BioGrid Type\" value=\"".$value."\"/>$this->lineEnd";
      $edgeLine .= "  <graphics width=\"$lineWidth\" fill=\"$lineColor\" cy:sourceArrow=\"$sourceArrow\" cy:targetArrow=\"$targetArrow\" cy:sourceArrowColor=\"$sourceArrowColor\" cy:targetArrowColor=\"$targetArrowColor\" cy:edgeLabelFont=\"Default-0-10\" cy:edgeLineType=\"SOLID\" cy:curved=\"STRAIGHT_LINES\"/>$this->lineEnd";
      $edgeLine .= "</edge>$this->lineEnd";
      fwrite($this->handle_write, $edgeLine);
    }
  }
  
  function write_node_group(&$HitGeneNameArr,$single_radians,$start_radians,$r){
    $i = 0;
    $j = 0;
    $bait_node_position = array_shift($this->bait_position_arr);
    if($HitGeneNameArr){
      foreach($HitGeneNameArr as $geneName => $geneID){
        if(!$geneName) continue;
        if(in_array($geneName, $this->hit_is_bait_arr)) continue; //20012/08/09
        while($i == $bait_node_position){
          $j = $this->write_bait_node($single_radians,$start_radians,$r,$j);
          $bait_node_position = array_shift($this->bait_position_arr);
          if($bait_node_position === null) break;
        }
        $nodeColor = $this->all_hits_id_color_arr[$geneID];
        $radians = $start_radians + $single_radians * $j++;
        $this->write_single_node($geneName,$geneID,$nodeColor,$radians,$r);
        $i++;
      }
      //--------------------------------------------------------------------
      if(count($this->bait_position_arr)){
        while(count($this->bait_position_arr)){
          $j = $this->write_bait_node($single_radians,$start_radians,$r,$j);
          $bait_node_position = array_shift($this->bait_position_arr);
          if($bait_node_position === null) break;
        }
        $j = $this->write_bait_node($single_radians,$start_radians,$r,$j);
      }  
      //--------------------------------------------------------------------
    }else{
      while($i == $bait_node_position){
        $j = $this->write_bait_node($single_radians,$start_radians,$r,$j);
        $bait_node_position = array_shift($this->bait_position_arr);
        if($bait_node_position === null) break;
      }
    }
  }

  function write_bait_node($single_radians,$start_radians,$r,$j){
    $bait_gene_name = array_shift($this->bait_name_index_arr);
    if(!$bait_gene_name) return $j;
    $tmpArr = explode(',',$this->bait_hitOverlap_note_color_arr[$bait_gene_name]);
    $tmp_geneID = '';
    if(count($tmpArr) == 2){
      $tmp_geneID = $tmpArr[1];
    }
    $nodeColor = $tmpArr[0];
    $radians = $start_radians + $single_radians * $j++;
    $this->write_single_node($bait_gene_name,$tmp_geneID,$nodeColor,$radians,$r);
    $this->calculate_start_point($bait_gene_name,$radians);
    return $j;
  } 

  function write_outer_node_group($baitName,&$HitGeneNameArr,$single_radians,$r){
    if(!isset($this->outer_start_radians_arr[$baitName]))return;//--2012/08/09
    $start_radians = $this->outer_start_radians_arr[$baitName];
    if($start_radians <= $this->current_radians) $start_radians = $this->current_radians + $single_radians;
    $sub_total = $this->single_hit_count_arr[$baitName];
    $this->current_radians = $start_radians + $single_radians * $sub_total;
    $j = 0;
    foreach($HitGeneNameArr as $geneName => $geneID){
      if(in_array($geneName, $this->hit_is_bait_arr)) continue; //20012/08/09
      $nodeColor = $this->all_hits_id_color_arr[$geneID];
      $radians = $start_radians + $single_radians * $j++;
      $this->write_single_node($geneName,$geneID,$nodeColor,$radians,$r);
    }  
  }

  function calculate_start_point($bait_gene_name,$radians){
  	$num_hits = $this->single_hit_count_arr[$bait_gene_name];
    $start_radians = $radians - ($num_hits * $this->outer_single_radians / 2);
  	$this->outer_start_radians_arr[$bait_gene_name] =  $start_radians;
  }

  function write_single_node($geneName,$geneID,$nodeColor,$radians,$r){
    global $node_lable;
    $x_y = $this->get_x_y($radians,$r);
    $nodeX = $x_y['x'];
    $nodey = $x_y['y'];
    $width = 1;
    $baitColor = "#ff0000";
    $outlineColor = "#666666";
    
    $tmp_geneName = $geneName;
    if($node_lable != 'long'){
      if(!array_key_exists($geneName, $this->Bait_info_arr)){
        $tmpArr = explode('(',$geneName);
        $tmp_geneName = $tmpArr[0];
      }
    } 
    $nodeLine = "<node label=\"$tmp_geneName\" id=\"$this->counter\">
    <att type=\"string\" name=\"Gene Name\" value=\"$tmp_geneName\"/>$this->lineEnd";
    if($geneID){  
      $nodeLine .= "  <att type=\"string\" name=\"Gene ID\" value=\"$geneID\"/>$this->lineEnd";
    }  
    if($nodeColor != "g" && array_key_exists($geneID, $this->FrequencyArr)){
      $Frequency = '';
      if(array_key_exists($geneID, $this->FrequencyArr)){
        $Frequency = $this->FrequencyArr[$geneID];
      }  
      $nodeLine .= "  <att type=\"real\" name=\"Frequency\" value=\"$Frequency\"/>$this->lineEnd";
    }
    $nodeColor_num = $this->node_color_arr[$nodeColor];
    $nodeLine .= "  <graphics type=\"$this->nodeType\" h=\"$this->nodeH\" w=\"$this->nodeW\" x=\"$nodeX\" y=\"$nodey\" fill=\"$nodeColor_num\" width=\"$width\" outline=\"$outlineColor\" cy:nodeTransparency=\"1.0\" cy:nodeLabelFont=\"SansSerif.bold-0-12\" cy:borderLineType=\"solid\"/>
  </node>$this->lineEnd";
    fwrite($this->handle_write, $nodeLine);
    
    $this->nodeIDarr[$geneName] = $this->counter;
    $this->counter++;
  }
    
  function get_x_y($radians,$r){
    $x_y = array();
    $x_y['x'] = $this->GRAPH_VIEW_CENTER_X + cos($radians) * $r;
    $x_y['y'] = $this->GRAPH_VIEW_CENTER_Y - sin($radians) * $r;
    return $x_y;
  }
  
  function array_diff_key_m($arr_1,$arr_2){
    $result_arr = array();
    foreach($arr_1 as $arr_1_key => $arr_1_val){
      $is_same_flag = 0;
      foreach($arr_2 as $arr_2_key => $arr_2_val){
        if($arr_1_key == $arr_2_key){
          $is_same_flag = 1;
          break;
        }
      }
      if(!$is_same_flag){
        $result_arr[$arr_1_key] = $arr_1_val;
      }
    }
    return $result_arr;
  }
}
?>