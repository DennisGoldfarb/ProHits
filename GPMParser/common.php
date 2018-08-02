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

function set_aa($path){  
  $m_pfAaMass = array();
	$residue = array();
	$mass = array();
	$H2O = 0;
	$NH3 = 0;
	$a = 0;
	$found = 0;
  
	if(!$INPUT = fopen($path,"r")){
    if($INPUT = fopen("$path.gz","r")){ 
      fclose($INPUT);
      system("gzip -d $path.gz");
      $INPUT = fopen("$path","r");
    }else{
			$m_pfAaMass['A'] = 71.037110;
			$m_pfAaMass['B'] = 114.042930;
			$m_pfAaMass['C'] = 103.009190;
			$m_pfAaMass['D'] = 115.026940;
			$m_pfAaMass['E'] = 129.042590;
			$m_pfAaMass['F'] = 147.068410;
			$m_pfAaMass['G'] = 57.021460;
			$m_pfAaMass['H'] = 137.058910;
			$m_pfAaMass['I'] = 113.084060;
			$m_pfAaMass['J'] = 0.0;
			$m_pfAaMass['K'] = 128.094960;
			$m_pfAaMass['L'] = 113.084060;
			$m_pfAaMass['M'] = 131.040490;
			$m_pfAaMass['N'] = 114.042930;
			$m_pfAaMass['O'] = 0.0;
			$m_pfAaMass['P'] = 97.052760;
			$m_pfAaMass['Q'] = 128.058580;
			$m_pfAaMass['R'] = 156.101110;
			$m_pfAaMass['S'] = 87.032030;
			$m_pfAaMass['T'] = 101.047680;
			$m_pfAaMass['U'] = 150.953640;
			$m_pfAaMass['V'] = 99.068410;
			$m_pfAaMass['W'] = 186.079310;
			$m_pfAaMass['X'] = 111.060000;
			$m_pfAaMass['Y'] = 163.063330;
			$m_pfAaMass['Z'] = 128.058580;
			$m_pfAaMass['H2O'] = 18.01056470;
  		$m_pfAaMass['NH3'] = 17.02654911;
			return $m_pfAaMass;
		}
	}
  
  while(!feof($INPUT)) {
    $buffer = fgets($INPUT, 4096);
    if(preg_match("/group label=\"residue mass parameters\" type=\"parameters\"/", $buffer)){
      $found=1;
      $tmp_str = trim($buffer);
      while(!feof($INPUT)){
        $buffer = fgets($INPUT, 4096);
        if(preg_match("/\<\/group\>/", $buffer)){
          preg_match_all("/\<aa type=\"(\w)\" mass=\"\d+\.\d+\" \/>/",$tmp_str,$matches);
          if($matches[1]) $residue = $matches[1];
          preg_match_all("/\<aa type=\"\w\" mass=\"(\d+\.\d+)\" \/>/",$tmp_str,$matches_2);
          if($matches_2[1]) $mass = $matches_2[1];
          preg_match("/\<molecule type=\"NH3\" mass=\"(\d+\.\d+)\" \/>/",$tmp_str,$matches_3);
          if($matches_3[1]) $NH3 = $matches_3[1];
          preg_match("/\<molecule type=\"H2O\" mass=\"(\d+\.\d+)\" \/>/",$tmp_str,$matches_4);
          if($matches_4[1]) $H2O = $matches_4[1];
          $AMASS = array_combine($residue, $mass);
    			$AMASS['H2O'] = $H2O;
    			$AMASS['NH3'] = $NH3;
    			fclose($INPUT);
          $tmp_str = '';
          break;
        }else{
          $tmp_str .= trim($buffer);
        }
      }
      if($found) break;
    }
  }
	if(!$found){
		$m_pfAaMass['A'] = 71.037110;
		$m_pfAaMass['B'] = 114.042930;
		$m_pfAaMass['C'] = 103.009190;
		$m_pfAaMass['D'] = 115.026940;
		$m_pfAaMass['E'] = 129.042590;
		$m_pfAaMass['F'] = 147.068410;
		$m_pfAaMass['G'] = 57.021460;
		$m_pfAaMass['H'] = 137.058910;
		$m_pfAaMass['I'] = 113.084060;
		$m_pfAaMass['J'] = 0.0;
		$m_pfAaMass['K'] = 128.094960;
		$m_pfAaMass['L'] = 113.084060;
		$m_pfAaMass['M'] = 131.040490;
		$m_pfAaMass['N'] = 114.042930;
		$m_pfAaMass['O'] = 0.0;
		$m_pfAaMass['P'] = 97.052760;
		$m_pfAaMass['Q'] = 128.058580;
		$m_pfAaMass['R'] = 156.101110;
		$m_pfAaMass['S'] = 87.032030;
		$m_pfAaMass['T'] = 101.047680;
		$m_pfAaMass['U'] = 150.953640;
		$m_pfAaMass['V'] = 99.068410;
		$m_pfAaMass['W'] = 186.079310;
		$m_pfAaMass['X'] = 111.060000;
		$m_pfAaMass['Y'] = 163.063330;
		$m_pfAaMass['Z'] = 128.058580;
		$m_pfAaMass['H2O'] = 18.01056470;
  	$m_pfAaMass['NH3'] = 17.02654911;
	}
  return $m_pfAaMass;
}

function get_feature($s,$f){
  $return = '';
  if(preg_match("/$f=\"(.+?)\"/",$s,$matches)){
    $return = $matches[1];
  }
	if($return == '0'){
		return 0;
	}
  if($return){
    return $return;
  }else{
    return '_';
  }  
}

function GetCache($cache){
	return file_get_contents($cache);
}
?>
