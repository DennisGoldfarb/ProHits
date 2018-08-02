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

class xmlParser{
   var $xml_obj = null;
   var $output = array();
   var $attrs;
   var $error_msg;

   function xmlParser(){
       $this->xml_obj = xml_parser_create();
       xml_set_object($this->xml_obj,$this);
       xml_set_character_data_handler($this->xml_obj, 'dataHandler');
       xml_set_element_handler($this->xml_obj, "startHandler", "endHandler");
   }

   function parse($path){
       if (!($fp = fopen($path, "r"))) {
           $this->error_msg = "Cannot open XML file: $path";
           return false;
       }
       while ($data = fread($fp, 4096)) {
           $data = str_replace("&", "", $data);
           if (!xml_parse($this->xml_obj, $data, feof($fp))) {
               $this->error_msg = sprintf("XML File error ($path): %s at line %d", xml_error_string(xml_get_error_code($this->xml_obj)),
               xml_get_current_line_number($this->xml_obj));
               xml_parser_free($this->xml_obj);
               return false;
           }
       }
       return true;
   }

   function startHandler($parser, $name, $attribs){
        $_content = array();
        $_content['name'] = $name;
        if(!empty($attribs))
            $_content['attrs'] = $attribs;
        array_push($this->output, $_content);
   }

   function dataHandler($parser, $data){
        if(!empty($data) && $data!="\n") {
            $_output_idx = count($this->output) - 1;
            if(isset($this->output[$_output_idx]['content'])){
              $this->output[$_output_idx]['content'] .= $data;
            }else{
              $this->output[$_output_idx]['content'] = $data;
            }
        }
   }

   function endHandler($parser, $name){
        if(count($this->output) > 1) {
            $_data = array_pop($this->output);
            $_output_idx = count($this->output) - 1;
            $add = array();
            if(!isset($this->output[$_output_idx]['child'])) $this->output[$_output_idx]['child'] = array();
            array_push($this->output[$_output_idx]['child'], $_data);
        }   
   }
}
?>