  <!--Mascot-------->    
  <tr bgcolor=white>
      <?php
      $error = '';
      $msg = '';
      $mascot_ok = '';      
      if(MASCOT_IP){
  			$mascot_sessionID = Mascot_session();
  			if($mascot_sessionID === true){
  			  //security is disabled
  			  if(MASCOT_USER){
  					$error .= "Mascot security is not enabled. Please define(\"MASCOT_USER\", \"\") in ../config/conf.inc.php";
  				}else{
            $mascot_ok = "<font color='green'>Yes</font>";
          }
  			}else if($mascot_sessionID === false){
  				$error .= "Cannot connect http://" . MASCOT_IP . MASCOT_CGI_DIR ."/login.pl. Please check the Mascot setting in ../config/conf.inc.php";
  			}else if(!$mascot_sessionID){
  				$error .= "Cannot login http://" . MASCOT_IP . MASCOT_CGI_DIR ."/login.pl. Please check the MASCOT_USER account in ../config/conf.inc.php";
  			}else{
  				$mascot_ok = "<font color='green'>Yes</font>";
  			}
			}else{
        $error .= "Mascot is disabled in Prohits setting. If you don't want to use Mascot, you can ignore the error. 
       Otherwise please put Mascot account and MASCOT_IP, MASCOT_CGI_DIR in Prohits conf file. ";
      }
      if($mascot_ok){
        $tmp_mid_path = dirname(MASCOT_CGI_DIR);        
        $url_tmp1 = "http://".MASCOT_IP.add_folder_backslash($tmp_mid_path)."search_form_select.html";
        $fd_tmp1 = @fopen($url_tmp1, 'r');
        $matches = array();
        if(!$fd_tmp1){
          $error .= "search_form_select.html is not exist.<br>";
        }else{
          $startFlag = 0;
          while (!feof($fd_tmp1)) {
            $buffer = fgets($fd_tmp1, 4096);
            if(preg_match('/MS\/MS Ion Search/', $buffer)){
              $startFlag = 1;
            }
            if($startFlag && preg_match('/cgi\/master_results\.pl(\?file=\.\.\/data\/F\d+\.dat)/', $buffer, $matches)){
              break;
            }
          }
          fclose($fd_tmp1);
          if(!isset($matches[1]) || !$matches[1]){
            $error .= "Data file is not exist.<br>";
            //$error .= "Data file '". basename($matches[1]) ."' is not exist.<br>";
          }else{
            
            $url_tmp2 = "http://".MASCOT_IP.add_folder_backslash(MASCOT_CGI_DIR)."ProhitsMascotParser.pl".$matches[1];
            //print $url_tmp2;
            $fd_tmp2 = @fopen($url_tmp2, 'r');
            if($fd_tmp2){
              fclose($fd_tmp2);
            }else{
              $error .= "Cannot parse data file.<br>";
              $error .= "Please copy ProhitsMascotParser.pl from Prohits/MascotParser/ to Mascot cgi folder.<br>";
            }
          }
        }
      }else{
        $mascot_ok = "<font color=red>No</font>";
      }
      if(!defined("RAW_CONVERTER_SERVER_PATH") || !trim(constant("RAW_CONVERTER_SERVER_PATH"))){
        $error .= "Please define constant RAW_CONVERTER_SERVER_PATH and set a value.<br>";
      } 
       
      ?>
      <td align=center>
      <b>Mascot Setup</b><br>
      <img src=<?php echo $prohits_web_root."/msManager/images/mascot.gif";?> boder=0></td>
      <td><li>Mascot IP: <?php echo MASCOT_IP;?><br>
          <li>Mascot CGI Path: <b><?php echo MASCOT_CGI_DIR;?></b><br>
          <li>Mascot is connected: <?php echo $mascot_ok;?><br>
          
          
          <li>perl: 
          <?php 
          
          $perl_version = '';
          $validPerl = false;
          $cmd = "perl -v";
          $validPerl_path = 'perl';
          $rt = commandCheck($cmd, true);
          if(!$rt){
            $error .= "Perl is not installed on ProHits server.</font>";
          }else if(is_array($rt)){
            foreach($rt as $line){
              if($line and preg_match("/^This is perl.+(v[0-9.]+)/", $line, $matches)){
                $perl_version = $matches[1];
                break;
              }
            }
          }
          if($perl_version){
            echo " Perl version: $perl_version<br>";
            if(!preg_match("/^v5.8|^v5.6|^v5.10/", $perl_version, $matches)){
              $error .= "Perl version is invalid for msparser. Please follow instruction from \"install\" folder to install ActivePerl v5.10.x.<br>";
            }else{
              $rt = commandCheck( "perl -MHTTP::Request::Common -e 1");
              if($rt and $rt != 'OK'){
                $error .= "please install perl-libwww-perl</font>";
              }
              $validPerl = true; 
            }
          }
          if(!$validPerl and defined("PERL_58") and PERL_58){
            $cmd = PERL_58." -v";
             
            $rt = commandCheck($cmd, true);
            if(!$rt){
              $error = "The perl path is not correct:". PERL_58."<br>";
            }else if(is_array($rt)){
              foreach($rt as $line){
                if($line and preg_match("/^This is perl.+(v[0-9.]+)/", $line, $matches)){
                  $perl_version = $matches[1];
                  if(preg_match("/^v5.8|^v5.6|^v5.10/", $perl_version, $matches)){
                    $validPerl = true; 
                    $error = '';
                    echo PERL_58;
                    $validPerl_path = PERL_58;
                  }
                  break;
                }
              }
            }
          }
          if($validPerl){
            $cmd = "cd ../MascotParser/scripts; ". $validPerl_path . " ProhitsMascotParser.pl";
            $rt = commandCheck($cmd, true);
            if(!$rt){
              $error .= "command not found: '$cmd'.<br>";
            }else{
              if(strpos($rt[0], "USAGE:") === 0){
                $msg .= '<br>mascot parser installed. You can unloaded Mascot searched results.';
              }
            } 
          }
          ?>
      <font color=red><?php echo ($error)?"ignore this error, if you don't use Mascot in ProHits<br>$error":"";?></font>
      <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr>
  
  <!--theGPM-------->    
  <tr bgcolor=white>
      <?php
       $error = '';
       $msg = '';
       $gpm_ok = '';
       $EXT_path = '';
       $gpm_version = '';
       $GPM_cgi_url = "http://".$_SERVER["SERVER_NAME"].add_folder_backslash(GPM_CGI_DIR);
       
       $fd = @fopen($GPM_cgi_url."thegpm.pl", 'r');
       
       if($fd){
          $gpm_ok = "<font color='green'>Yes</font>";
          fclose($fd);
          $fd = @fopen($GPM_cgi_url."prohits_parser.pl", 'r');
          if($fd){
              fclose($fd);
          }else{ 
              $error .= "prohits_parser.pl is missing.<br>";
              $error .= "Please follow the Prohits installation instruction to setup X!Tandem and TPP.<br>";
          }
		  $gpm_in_prohits = 1;
		  $gpm_version = '';
		  $error = check_search_engine_url('gpm');
		   
          
       }else{
          $gpm_ok = "<font color='red'>No</font>";
          $error .= "Please follow the installation instruction to setup GPM and TPP.<br>";
       }
       
        
       if(!defined('GPM_CGI_PATH')){
          $error .= "GPM_CGI_PATH is not defined in Prohits conf file.<br>";
       }else if(!is_dir(GPM_CGI_PATH)){
          $error .= "Please check if GPM_CGI_PATH is correct path in Prohits conf file.<br>";
       }else{
          $EXT_path = dirname(dirname(__FILE__))."/EXT";
          //if(preg_replace("/\/$/", '', GPM_CGI_PATH) != $EXT_path."/thegpm/thegpm-cgi"){
          //  $error .= "Please set GPM_CGI_PATH to ".$EXT_path."/thegpm/thegpm-cgi<br>";
          //}
       }
      
      ?>
      <td align=center>
      <b>The GPM Setup</b><br>
      <img src=<?php echo $prohits_web_root."/msManager/images/gpm.gif";?> boder=0></td>
      <td><li>GPM IP: <?php echo $_SERVER["SERVER_NAME"] ;?><br>
          <li>GPM CGI Path: <b><?php echo GPM_CGI_PATH;?></b><br>
          <li>GPM is connected: <?php echo $gpm_ok;?><br>
		  <li>GPM version: <?php echo $gpm_version;?><br>
      <font color=red><?php echo $error;?></font>
      <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr>
  
  <!--DIA-Umpire-------->    
  <tr bgcolor=white>
      <?php
	  
       $error = '';
       $msg = '';
       $diaumpire_version = ''; 
	     $error = check_search_engine_url('diaumpire', 1);
      if(!$diaumpire_version){
        $error = "Please make sure that if DIAUMPIRE_BIN_PATH is correct.";
      }else{
        $msg = "DIA-Umpire installed.";
      }
      ?>
      <td align=center>
      <b>DIA-Umpire Setup</b><br>
      <img src=<?php echo $prohits_web_root."/msManager/images/diaumpire.gif";?> boder=0></td>
      <td><?php
      if($diaumpire_version){
        echo "Version: $diaumpire_version<br>";
        if($EXT_path){
          echo  "location: ".DIAUMPIRE_BIN_PATH;
        }else{
          echo "location: check file header ".$GPM_cgi_url."Prohits_TPP.pl";
        }
      }
      ?>
      <font color=red><?php echo $error;?></font>
      <font color=green><br><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr>
 <!--MSPLIT-DIA-------->    
  <tr bgcolor=white>
      <?php
      $error = '';
      $msg = '';
      
      $output = '';
      $msplit_version = '';
      $error = check_search_engine_url('msplit', 1);
      if(!$error){
        $msg = "MSPLIT-DIA is working.";
      }
      ?>
      <td align=center>
      <b>MSPLIT-DIA Setup</b><br>
      <img src=<?php echo $prohits_web_root."/msManager/images/msplit.gif";?> boder=0></td>
      <td>
      <?php
        if($msplit_version)echo "Version: $msplit_version<br>";
      ?>
      <font color=red><?php echo $error;?></font>
      <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr> 
  <!--MS-GFD+-------->    
  <tr bgcolor=white>
      <?php
      $error = '';
      $msg = '';
      $output = '';
      $msgfpl_version = '';
      $error = check_search_engine_url('msgfpl', 1);
      if(!$msgfpl_version){
        $error = "MS-GF+ doesn't work. ";
        if($EXT_path){
          $error .= "Please set correct MSGFPL_BIN_PATH.";
        }else{
          $error .= "Please check Prohits_TPP.pl file if MS-GF+ points a correct location. <br>$url";
        }
      }else{
        $msg = "MS-GF+ installed";
      }
      ?>
      <td align=center>
      <b>MS-GF+ Setup</b><br>
      <img src=<?php echo $prohits_web_root."/msManager/images/msgfpl.gif";?> boder=0></td>
      <td>
      <?php
        if($msgfpl_version)echo "Version: $msgfpl_version<br>";
      ?>
      <font color=red><?php echo $error;?></font>
      <font color=green><br><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr> 
  <!--MS-GFDDB-------->    
  <tr bgcolor=white>
      <?php
      $error = '';
      $msg = '';
      $output = '';
      $msgfdb_version = '';
       
         
        if(!defined('MSGFDB_BIN_PATH') or !is_dir(MSGFDB_BIN_PATH)){
          $error = "Please set correct location for  MSGFPL_BIN_PATH";
          
        }else{
          $cmd = "java -jar ". preg_replace("/\/$/", "", MSGFDB_BIN_PATH) . '/MSGFDB.jar';
          exec("$cmd 2>&1", $output); 
        }
      
      if($output){
        
        foreach($output as $line){
          $line = trim($line);
          
          if(strpos($line, "MSGFDB ") === 0){
            
            $msgfdb_version = str_replace("MSGFDB ", "", $line);
            
            $msg = "MSGFDB installed"; 
    
            break;
          }
        }
      }
      ?>
      <td align=center>
      <b>MSGFDB Setup</b><br>
      <img src=<?php echo $prohits_web_root."/msManager/images/msgfdb.png";?> boder=0></td>
      <td>
      <?php
        if($msgfdb_version)echo "Version: $msgfdb_version<br>";
      ?>
      <font color=red><?php echo $error;?></font>
      <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr>
  <!--MSFragger-------->
  <tr bgcolor=white>
      <?php
      //the version is no way to be detected at this time.
      $error = '';
      $msg = '';
      $output = '';
      $msfragger_version = '';
      $error = check_search_engine_url('msfragger', 1);
      if($error) $msg = "If you want to install MSFragger, folllowing the instruction from '$prohits_root/EXT/MSFragger/install.txt' to install it.";
      if($msfragger_version){
        $msg = "MSFragger installed"; 
      }
      ?>
      <td align=center>
      <b>MSFragger Setup</b><br>
      <img src=<?php echo $prohits_web_root."/msManager/images/msfragger.gif";?> boder=0></td>
      <td>
      <?php
        if($msfragger_version)echo "Version: $msfragger_version<br>";
      ?>
      <font color=red><?php echo $error;?></font>
      <font color=green><br><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr>
  <!--COMET-------->    
  <tr bgcolor=white>
      <?php
      $error = '';
      $msg = '';
      
      $output = '';
      $comet_version = '';
      $error = check_search_engine_url('comet', 1);
      if(!$comet_version){
        $error = "COMET doesn't work. ";
        if($EXT_path){
          $error .= "Please set COMET_BIN_PATH.";
        }else{
          $error .= "Please check Prohits_TPP.pl file if COMET points a correct location. <br>$url";
        }
      }else{
        $msg = "COMET installed";
      } 
      ?>
      <td align=center>
      <b>COMET Setup</b><br>
      <img src=<?php echo $prohits_web_root."/msManager/images/comet.gif";?> boder=0></td>
      <td>
      <?php
        if($comet_version)echo "Version: $comet_version<br>";
      ?>
      <font color=red><?php echo $error;?></font>
      <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr> 
  
  <!-- SEQUEST ------->
  <tr bgcolor=white>
      <?php
       $error = '';
       $msg = '';
       $SEQUEST_ok = '';
       $sequest_set = 'OK';
       if(defined("SEQUEST_IP") and SEQUEST_IP){
        $url = "http://".SEQUEST_IP.SEQUEST_CGI_DIR."/Prohits_SEQUEST.pl";
        $fd = @fopen($url, 'r');
         
        if($fd){
        	while($line = fgets($fd, 4096)){
        		if(preg_match("/ALL COMMANDS ARE WORKING<br>VERSION:(.+)$/", $line, $matches)){
        		  $SEQUEST_VERSION = $matches[1];
        			$msg .= " $SEQUEST_VERSION";
        			fclose($fd);
              $SEQUEST_ok = "<font color='green'>Yes</font>";
              break;
        		}
        	}
        }else{
          $error = "Cannot connect SEQUEST server";
        }
       }else{
        $msg = "SEQUEST not set";
        $sequest_set = '';
       } 
      ?>
      <td align=center>
      <b>The SEQUEST Setup</b><br>
      <img src=<?php echo $prohits_web_root."/msManager/images/sequest.gif";?> boder=0></td>
      <td>
      <?php if($sequest_set){?>
      
      <li>SEQUEST IP: <?php echo SEQUEST_IP;?><br>
          <li>SEQUEST CGI Path: <b><?php echo SEQUEST_CGI_DIR;?></b><br>
          <li>SEQUEST is connected: <?php echo $SEQUEST_ok;?><br>
      <?php }?>
      <font color=red><?php echo $error;?></font>
      <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr>
  <!-- SAINT ------->
  <tr bgcolor=white>
      <?php
       $error = '';
       $msg = '';
       $SAINT_ok = '';
       $version_exp = ''; 
       $version = '';
       
          if(!defined("SAINT_SERVER_PATH") or !SAINT_SERVER_PATH or !defined('SAINT_SERVER_EXPRESS_PATH') or !SAINT_SERVER_EXPRESS_PATH){
            $error =  "Please set SAINT_SERVER_PATH and SAINT_SERVER_EXPRESS_PATH to correct locations. $EXT_path/Prohits_SAINT/SAINT_vx.x.x/bin";
          }else if(trim(SAINT_SERVER_PATH)){
            exec(SAINT_SERVER_PATH."/saint-reformat -v 2>&1", $output);
            $out_str = implode("\n", $output);
            if(preg_match("/SAINT version (.+)/", $out_str, $matches)){
               $msg .= "SAINT version: ".$matches[1]."<br>";
            }else{
              $error .= "SAINT doesn't work<br>";
            }
            $output = '';
            if(defined("SAINT_SERVER_EXPRESS_PATH") and trim(SAINT_SERVER_EXPRESS_PATH)){ 
              exec(SAINT_SERVER_EXPRESS_PATH."/SAINTexpress-spc -v 2>&1", $output); 
              $out_str = implode("\n", $output);
              if(preg_match("/SAINT express version (.+)/", $out_str, $matches)){
                $msg .= "SAINTexpress version: ".$matches[1];
              }else{
                $error .= "SAINTexpress doesn't work<br>";
              }
            }else{
              $error .= "Please set SAINT_SERVER_EXPRESS_PATH<br>";
            }
          }else{
          	$error =  "Please set SAINT_SERVER_PATH";
          }
       
      ?>
      <td align=center>
      <b>The SAINT Setup</b><br>
      <img src=<?php echo $prohits_web_root."/analyst/images/saint_logo.gif";?> boder=0></td>
      <td>
          <li><?php 
          if($EXT_path){
            echo "SAINT location: ".SAINT_SERVER_PATH ."<br>SAINTexpress location: ". SAINT_SERVER_EXPRESS_PATH;
          }else{
            echo "SAINT locaton: ". SAINT_SERVER_WEB_PATH;
          }
          ?><br>
           
          <font color=red><?php echo $error;?></font>
          <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr>
  <!--PEAR---------->
  <tr bgcolor=white>
      <?php
       $error = '';
       $msg = '';
       $rt = shell_exec("pear -V");
      
       if(empty($rt)){
          $error =  "ERROR: please install php-pear.\n";
       }
      ?>
      <td align=center>
      <b>The PEAR Setup</b><br>
      <img src=./images/pear.gif boder=0 height=40 width=72></td>
      <td> 
       <li>Location: <?php echo "<pre>$rt</pre>";?>
         <font color=red><?php echo $error;?></font> 
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr>
  <!---Converter --->
  <tr bgcolor=white>
      <?php
      $error = '';
      $msg = '';
	  $converter_version = '';
	  $error = check_search_engine_url('converter', 1);
      if(!RAW_CONVERTER_SERVER_PATH){
        $error =  'Converter path is empty';
      }else{
        $error = check_search_engine_url('converter', 1);
      }
	  if(!$converter_version){
		$msg = "ignore the error if raw files have been converted to mzML or mzXML format. Otherwise please follwoing the instructon from $prohits_root/install/RawConverter/install_rawconverter.pdf to insall Prohits RwaConverter.";
	  }
      ?>
      <td align=center>
      <b>Raw file Converter Setup</b><br>
      <img src=<?php echo $prohits_web_root."/msManager/images/proteowizard.png";?> boder=0>
      </td>
      <td>
        <li>Location: <?php echo RAW_CONVERTER_SERVER_PATH;?><br>
        <li>Version : <?php echo $converter_version;?><br>
        <font color=green><?php echo $msg;?></font>
         <font color=red><?php echo $error;?></font> 
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr>
  <!--philosohper--------->
   
  <tr bgcolor=white>
      <?php
      $error = '';
      $msg = '';
      $output = '';
      $philosohper_set = 0;
      $philosopher_version = '';
     
       
      $cmd = preg_replace("/\/$/", "", PHILOSOPHER_BIN_PATH)."/philosopher";
      @exec("$cmd 2>&1", $output);
    
      $error = check_search_engine_url('philosopher', 1);
      if(!$philosopher_version){
         $error = 'Prohits can not connect Philosopher: '. PHILOSOPHER_BIN_PATH;
         $error .=" <br>following the instrction to install Philosopher. $prohits_root/EXT/philosopher/install.txt";
      }else{
        $msg = "Philosopher is working.";
      }
       
      ?>
      <td align=center>
      <b>Philosopher Setup</b><br>
      <img src=<?php echo $prohits_web_root."/msManager/images/philosopher.gif";?> boder=0>
      </td>
      <td> 
          <li>Philosopher: <?php echo ($philosopher_version)? $philosopher_version:'<font color=red>not set</font>';?><br>
      <font color=red><?php echo $error;?></font> 
      <font color=green><br><?php echo $msg;?></font> 
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr>