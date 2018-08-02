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

class Ms_search_parameter{
  var $Element_arr = array();
  var $input_html;
  var $html_lines;
  
  //function Ms_search_parameter($Element_arr=array(),$input_html){
  function __construct($Element_arr=array(),$input_html){
    $this->input_html = $input_html;
    $this->set_elements($Element_arr);
    $this->read_html($input_html);
  }
  function set_elements($Element_arr=array()){
    if($this->Element_arr){
      $this->Element_arr = array_merge($this->Element_arr, $Element_arr);
    }else{
      $this->Element_arr = $Element_arr;
    }
/*
echo "<pre>***********";
print_r($this->Element_arr);  
echo "</pre>";
exit; */   
    
  }
   
  function read_html(){  
    $fd = @fopen($this->input_html, "r");
    if(!$fd) fatalError("Cannot open ". $input_html , __LINE__);
    $find_flag = 0;
    $type_filter_arr = array("BUTTON","HIDDEN");
    
    while($buffer = fgets($fd)){
      $this->html_lines[] = $buffer;
      if(preg_match("/<SELECT\s+NAME='(.+?)'/i", $buffer, $matchs)){
        $find_flag = 1;
        $tmp_name = trim($matchs[1]);
        if(preg_match("/frm_(.+)$/i", $tmp_name, $name_matchs)){
          $e_Name = $name_matchs[1];
        }else{
          $e_Name = $tmp_name;
        }
        continue;
      }elseif(preg_match("/<\/SELECT/i", $buffer, $matchs)){
        $find_flag = 0;
        continue;
      }elseif($find_flag && preg_match("/VALUE='(.+?)'\s*selected/i", $buffer, $matchs)){
        $e_selected_value = $matchs[1];
        $this->Element_arr[$e_Name] = $e_selected_value;
      }elseif(preg_match("/<INPUT(.+?)>/i", $buffer, $matchs)){
        $atr_str_arr = explode(" ", $matchs[1]);
        $atr_arr = array();
        $i_name = '';
        foreach($atr_str_arr as $atr_str_val){
          $atr_str_val = $atr_str_val;
          if(!$atr_str_val) continue;
          $tmp_arr = explode("=", $atr_str_val);
          $upper_KEY = strtoupper(trim($tmp_arr[0]));          
          if(count($tmp_arr) == 2){
            if($upper_KEY == 'TYPE'){
              $atr_arr[$upper_KEY] = str_replace("'", "", strtoupper($tmp_arr[1]));
            }else{
              $atr_arr[$upper_KEY] = str_replace("'", "", $tmp_arr[1]);
            }
          }else{
            $atr_arr[$upper_KEY] = '';
          }
        }
        if(in_array(strtoupper($atr_arr['TYPE']), $type_filter_arr)){
          $atr_arr = array();
          continue;
        }
        if(($atr_arr['TYPE'] == 'RADIO' || $atr_arr['TYPE'] == 'CHECKBOX') && !array_key_exists('CHECKED', $atr_arr)) continue;
        
        $tmp_name = trim($atr_arr['NAME']);
        if(preg_match("/frm_(.+)$/i", $tmp_name, $name_matchs)){
          $i_name = $name_matchs[1];
        }else{
          $i_name = $tmp_name;
        }
        $this->Element_arr[$i_name] = $atr_arr['VALUE'];
      }
    }    
    fclose($fd); 
  }
  
  
  
  
  function display_form($td_label_color='',$td_base_color=''){
    $Element_name_arr = array();
    //$fd = @fopen($this->input_html, "r");
    //if(!$fd) fatalError("Cannot open ". $input_html , __LINE__);
    $find_flag = 0;
    $type_filter_arr = array("BUTTON","HIDDEN");
    
    //while($buffer = fgets($fd)){
    foreach($this->html_lines as $buffer){
      if(preg_match("/<SELECT\s+NAME='(.+?)'/i", $buffer, $matchs)){
        $find_flag = 1;
        
        $tmp_name = trim($matchs[1]);
        if(preg_match("/frm_(.+)$/i", $tmp_name, $name_matchs)){
          $e_Name = $name_matchs[1];
        }else{
          $e_Name = $tmp_name;
        }
        
        if(!in_array($e_Name, $Element_name_arr)){
          $Element_name_arr[] = $e_Name;
        }
        
        if(array_key_exists($e_Name, $this->Element_arr)){
          $e_Value = $this->Element_arr[$e_Name];
        }
        echo $buffer;
      }elseif(preg_match("/<\/SELECT/i", $buffer, $matchs)){
        $find_flag = 0;
        echo $buffer;
      }elseif($find_flag && preg_match("/VALUE='(.+?)'/i", $buffer, $h_matchs)){
        if($find_flag && preg_match("/VALUE='(.+?)'\s*selected/i", $buffer, $matchs)){
          if($matchs[1] != $e_Value){          
            $buffer = str_ireplace("selected", "", $buffer);
          }
        }else{
          if($h_matchs[1] == $e_Value){
            $buffer = str_ireplace(">", " selected>", $buffer);
          }
        }
        echo $buffer;
      }elseif(preg_match("/<INPUT(.+?)>/i", $buffer, $matchs)){
        $atr_str_arr = explode(" ", $matchs[1]);
        $atr_arr = array();
        foreach($atr_str_arr as $atr_str_val){
          $atr_str_val = trim($atr_str_val);
          if(!$atr_str_val) continue;
          $tmp_arr = explode("=", $atr_str_val);
          $upper_KEY = strtoupper(trim($tmp_arr[0]));
          if(count($tmp_arr) == 2){
            if($upper_KEY == 'TYPE'){
              $atr_arr[$upper_KEY] = str_replace("'", "", strtoupper($tmp_arr[1]));
            }else{
              $atr_arr[$upper_KEY] = str_replace("'", "", $tmp_arr[1]);
            }
          }else{
            $atr_arr[$upper_KEY] = '';
          }
        }
        if(in_array($atr_arr['TYPE'], $type_filter_arr)){
          echo $buffer;
          continue;
        }       
        if($atr_arr['TYPE'] == 'TEXT'){
/*echo "<pre>";        
print_r($atr_arr);
//echo "\$i_name=$i_name<br>";        
echo "</pre>";*/
          $tmp_name = trim($atr_arr['NAME']);
          if(preg_match("/frm_(.+)$/i", $tmp_name, $name_matchs)){
            $t_name = $name_matchs[1];
          }else{
            $t_name = $tmp_name;
          }
          if(!in_array($t_name, $Element_name_arr)){
            $Element_name_arr[] = $t_name;
          }
          if(array_key_exists($t_name, $this->Element_arr)){
            
            $e_Value = $this->Element_arr[$t_name];
            $buffer = preg_replace("/VALUE='.+?'/i", "VALUE='$e_Value'", $buffer);
          }
          echo $buffer;
        }elseif($atr_arr['TYPE'] == 'RADIO' || $atr_arr['TYPE'] == 'CHECKBOX'){
        
          $tmp_name = trim($atr_arr['NAME']);
          if(preg_match("/frm_(.+)$/i", $tmp_name, $name_matchs)){
            $i_name = $name_matchs[1];
          }else{
            $i_name = $tmp_name;
          }
          
          if(!in_array($i_name, $Element_name_arr)){
            $Element_name_arr[] = $i_name;
          }
          if(array_key_exists($i_name, $this->Element_arr) && $atr_arr['VALUE'] == $this->Element_arr[$i_name]){
            if(!array_key_exists('CHECKED', $atr_arr)){
              $buffer = str_ireplace(">", " CHECKED>", $buffer);
            }
          }else{
            $buffer = str_ireplace("CHECKED", "", $buffer);
          }
          echo $buffer;
        }else{
          echo $buffer;
        }    
      }else{
        if($td_label_color){
          $buffer = str_replace("@#@td_label_color@#@", "$td_label_color", $buffer);
        }
        if($td_base_color){
          $buffer = str_replace("@#@td_base_color@#@", "$td_base_color", $buffer);
        }
        if(stristr($buffer,'</form>')){
          $Element_name_str = implode(",", $Element_name_arr);
          echo "<INPUT NAME='Element_name_str' TYPE='hidden' VALUE='$Element_name_str'>";
        }
        echo $buffer;
      }
    }  
    //fclose($fd);
  }
}
?>