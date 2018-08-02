<?php 
$in_file = './Analyst_help.htm';

$handle = @fopen($in_file, "r");
$lable_str = '';
if($handle){
  while(!feof($handle)){
    $buffer = fgets($handle);
    if(preg_match('/FONT.+?B\>(.+?\.{10}.+?)\<\/P/', $buffer, $matches)){
      //echo $buffer."<br>";exit;
      $lable_str = $matches[1];
      $lable_str = preg_replace('/\<BR\>/', '', $lable_str);
      echo $lable_str;
      break;
    }  
  }
  fclose($handle);
}

if(!$lable_str){
  echo "$in_file format had changed. Check the format manually.";
  exit;
}
$patterns = array();
$replacements = array();
$anchors = array();

if(preg_match_all('/([A-Za-z-\s\&;\(\)]+)/', $lable_str, $matches)){
  $counter = 1;
  foreach($matches[1] as $value){
    $val = trim($value);
    if(!$val) continue;
    $pat = "/>$val\s*</i";
    $rep = "><a name=\"faq".$counter."\"></a><b>".$val."&nbsp;</b><";
    $anchor = "<li><a href=\"#faq".$counter."\" class=help>$val</a>";
    array_push($patterns, $pat);
    array_push($replacements, $rep);
    array_push($anchors, $anchor);
    $counter++;
  }
  echo "<pre>";
  print_r($patterns);
  //$replacements = array_reverse($replacements);
  print_r($replacements);
  print_r($anchors);
  echo "</pre>";
}
//exit;
$f_str = file_get_contents('./Analyst_help.htm');
$f_str = preg_replace('/\s*\r+\n+/', '', $f_str);
$f_str = preg_replace('/(\<\/.+?\>)/', "$1\r\n", $f_str);
$f_str = preg_replace('/\>\</', ">\r\n<", $f_str);
$f_str = preg_replace('/(\<FONT size=)("\+1")/', '${1}2', $f_str);
$f_str = preg_replace('/(\>\s*&rArr;.+?\<)/', '><b$1/b><', $f_str);
$f_str = preg_replace('/(\<FONT.+?)\>/', "$1 face=arial>", $f_str);
$f_str = preg_replace($patterns, $replacements, $f_str);
$tmp_file_name = "./formated1.html";
$fp = fopen($tmp_file_name, 'w');
fwrite($fp, $f_str);
fclose($fp);

$fp = fopen($tmp_file_name, 'r');
$out_file_name = "./formated2.html";
$fp_w = fopen($out_file_name, 'w');
if($fp){
  while(!feof($fp)){
    $buffer = fgets($fp);
    if(preg_match('/Overview(.+?\.{10}.+?)\</', $buffer, $matches)){
    //if(preg_match('/FONT.+?\>(.+?\.{10}.+?)\</', $buffer, $matches)){
      $anchors_th = "<pre><table border='0' cellpadding='0' cellspacing='0' width='95%'>\r\n
          <tr>\r\n
            <td><br><font face='Arial Black' size='+2' color='#055698'>\r\n
           <b><div align='center'><img src=./msManager/images/prohits_logo.gif border=0 align=middle> &nbsp; Welcome to Ask Prohits</div></b>\r\n
            </font><br></td>\r\n
          </tr>\r\n
          <tr height='1'>\r\n
            <td bgcolor='#006699' height='1'>\r\n
               <img src='./images/pixel.gif' width='1' height='1' border='0'></td>\r\n
          </tr>\r\n
          <tr height='1'>\r\n
            <td><br>\r\n
            <ol>\r\n</pre>";
      fwrite($fp_w, $anchors_th);
      foreach($anchors as $value){
        fwrite($fp_w, $value."\r\n");
      }
  
      $anchors_te = "<pre>
                    </ol>   
                  </td>
                </tr>
              </table></pre>";
      fwrite($fp_w, $anchors_te);
      continue;
    }    
    
    if(preg_match('/(\<IMG.+?width=")(\d+)(".+?height=")(\d+)(".+?\>)/', $buffer, $matches)){
      $tmp_w = round($matches[2]*1.1);
      $tmp_h = round($matches[4]*1.1);
      //$buffer = preg_replace('/(\<IMG.+?width=")(\d+)(".+?height=")(\d+)(".+?\>)/', '${1}'.$tmp_w.'${3}'.$tmp_h.'${5}', $buffer);
      
      /*echo $buffer;
      exit;
      echo "<pre>";
      print_r($matches);
      echo "</pre>";
      echo count($matches);
      exit;*/
    }
    if(preg_match('/\<BODY/', $buffer, $matches)){
      fwrite($fp_w, $buffer);
      $tmp_line = "<DIV ID='cyto_confirm_div' STYLE='display: block;border: black solid 1px;width: 800px';>\r\n";
      fwrite($fp_w, $tmp_line);
      continue;
    }
    if(preg_match('/\<\/BODY/', $buffer, $matches)){
      $tmp_line = "</DIV>\r\n";
      fwrite($fp_w, $tmp_line);
      fwrite($fp_w, $buffer);
      continue;
    }
    fwrite($fp_w, $buffer);  
  }
  fclose($fp);
  fclose($fp_w);
}

?>
