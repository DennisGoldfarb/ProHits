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

/***********************************************************************
  Author: Frank Liu
    Date:  2003-02-12 15:03:16
    Desc: 
        1. pars mascot search reults file.
        2. Referer is ./add_checkbox.php. It will pass 
           $SID, search engine $host, data $file, total of hits in this results file $pCounter
          
          $band_id -- prohits band id for this search results file
          $band_code -- user inputed band(sample) code
          $instrument -- mass spec instrument
          $file -- results file insearch inchine
          $SID -- user log in session id
          $pcounter -- number of checkboxes in privious page 
          $targetDB -- tartget database to save hits.
        3. This file will pars $file and save record into mysql database
          
        4. before insert into database it will check permission if the user
        5. same protein GI cann't be resubmitted to same band.
        6. Modify search engine file 192.197.250.115/Mascot/cgi/master_results.pl
           line 1942. Added peptide position for each peptide.
	     
	  7. has been mofidied to surpport Mascot 2.1 (2005-11-25) without 6.
************************************************************************/
require("../../db/dbstart.php");
require("../../classes/user_class.php");
require("../../classes/auth_class.php");
require("../../classes/session_class.php"); 

//connect database msManager and check login ---------------------------------------------
$this_page = "autosearch insert $targetDB";
connect_msManager();  //this functoin is in ./db/dbstart.php

if(!$SID) {
  echo "<font color=red><h2>Please login</h2></font>";
  exit;
} else {
  $SESSION = new Session("fetchall", $SID);
  //expired in $hours, the cookie is setup 4 hours expire.
  if(!$SESSION->check_SID($SID,8)) {
    echo "<center>\n
        <font color=red face='helvetica,arial,futura'><h2>Your login has expired.</h2>
        <br>Please logout ProHits / MS Data Manager. You may refresh this page after you re-login.</font>\n
        </center>\n";
    exit;
  }
}

$USER = new User("",$SESSION->value["UID"]);
$username = $USER->username;
$AUTH = new Auth($SESSION->value[UID],"", $this_page);
if(!$AUTH->insert)  {
  header ("Location: ../../noaccess.html");
  exit;
}

//end of login check----------------------------------------------------------------------

//================ start to read file =========================================
//all array counter start from [1]
$HitsIDS = array(); //to store all new inserted hits IDs
$pNames = array(); //protein names
$pGIs = array();   //protein GI number
$pDatabases = array(); //protein database
$pExpects = array(); //protein Expects
$pMasses = array();
$pScores = array();
$pRedundantGIs = array(); //mach the same set of peptides
$pMatchedPeptides = array();

$pepMiss      = array();
$pepMSExpects   = array();  // $pepExpects[0][0], $pepExpects[0][1], $pepExpects[0][2] for protein 1 peptid 1,2,3 expects
                        // $pepExpects[1][0], $pepExpects[1][1], $pepExpects[1][2] for protein 2 peptid 1,2,3 expects
$pepCharges   = array();  // $pepCharges[0][0], $pepCharges[0][1], $pepCharges[0][2] for protein 1 peptid charges
$pepMass      = array();      // $pepMass[0][0],$pepMass[0][1], $pepMass[0][2] for protein 1 peptid 1,2,3 masses
$pepSequences = array(); // $pepSequence[0][0],$pepSequence[0][1],$pepSequence[0][2], for protein 1 peptid 1,2,3 sequences

//$proteinRP = "&REPTYPE=protein";
$MascotVersion = '';

$fd = @fopen("http://$host/mascot/cgi/master_results.pl?file=$file$proteinRP","r");

$i =0;

if(!$fd){
  echo "The file dose not exsist.<br>$file";
  exit;
}

//get all hits into arrays
$pNum = 0; //temp checkbox counter
$hitStart = false;
while (!feof ($fd) and !$endFile) {
    $buffer = fgets($fd, 40960);
    //get searched database
    if(strstr($buffer,"<B>Database        :")){
      $searchedDB = substr($buffer, 21, strpos($buffer, '(') - 22);
    }
    if(!$MascotVersion){
    	if(strstr($buffer, '<B>Expect&nbsp;</B></TT></TD><TD ALIGN=RIGHT><TT><B>Rank&nbsp;</B></TT></TD><TD><TT><B>Peptide</B>')){
		$MascotVersion = "2.1";
	}
    }
    //find hit start position
    //$buffer = '<TR><TD><TT><B><A NAME="Hit3">3.</A>&nbsp;&nbsp;&nbsp;&nbsp;</B></';
    if(strstr($buffer, "<A NAME=\"Hit") and strstr($buffer,"protein_view.pl")){
      $buffer = preg_replace("/<[\/\!]*?[^<>]*?>|[ ]/e", "",$buffer);
	$buffer = str_replace("&nbsp;",'',$buffer);
	
      //this is the first line or a hit
      $pNum++;  //tmp checkbox counter start from 1
      $hitStart = true;
      $peptide_num = 0; //peptids num of the hit
      
	if(preg_match("/gi\|([0-9]*)/", $buffer, $matched_arr)){
          $pGIs[$pNum] = $matched_arr[1];			     //-----------------------$pGIs
	}
	if(preg_match("/Mass:([0-9]*)/i", $buffer, $matched_arr)){
          $pMasses[$pNum] = $matched_arr[1]/1000;          //-----------------------$pMasses
	}
	if(preg_match("/Score:([0-9]*)/i", $buffer, $matched_arr)){
          $pScores[$pNum] = $matched_arr[1];               //----------------------$pScores
	     
	}
      if(preg_match("/Queriesmatched:([0-9]*)|Peptidesmatched:([0-9]*) /i", $buffer, $matched_arr)){
          $pMatchedPeptides[$pNum] = $matched_arr[1];      //----------------------$pMatchedPeptides
	}
      $buffer = fgets($fd, 40960);
      //get next line
	$buffer = preg_replace("/<[\/\!]*?[^<>]*?>/e", "",$buffer);
	$buffer = str_replace("&nbsp;",'',$buffer);
      $pNames[$pNum] = trim($buffer);	                //-----------------------$pNames
	 
    } 
     
    //non-bolded peptides
    //and bolded peptides
    //version 2.1
    //<TR><TD ALIGN=RIGHT><INPUT TYPE="checkbox" NAME="QUE" VALUE="779.392724 from(780.400000,1+)  title(A03%2e264%2e264%2e1%2edta)  query(67)" CHECKED></TD><TD ALIGN=RIGHT><TT><A HREF="peptide_view.pl?file=../data/20051115/F001254.dat&query=67&hit=1&index=gi%7c6325142&px=1" TARGET="_blank" onMouseOver="statusString = h1_q67; if (!browser_EXCLUDE) activateEl('Q67', event)" onMouseOut="clearEl()">67</A>&nbsp;&nbsp;</TT></TD><TD ALIGN=RIGHT><TT><FONT COLOR=#FF0000><B>   780.40&nbsp;&nbsp;</B></FONT></TT></TD><TD ALIGN=RIGHT><TT><FONT COLOR=#FF0000><B>   779.39&nbsp;&nbsp;</B></FONT></TT></TD><TD ALIGN=RIGHT><TT><FONT COLOR=#FF0000><B>   779.40&nbsp;&nbsp;</B></FONT></TT></TD><TD ALIGN=RIGHT NOWRAP><TT><FONT COLOR=#FF0000><B>    -0.01&nbsp;</B></FONT></TT></TD><TD ALIGN=RIGHT><TT><FONT COLOR=#FF0000><B>0&nbsp;&nbsp;</B></FONT></TT></TD><TD ALIGN=RIGHT NOWRAP><TT><FONT COLOR=#FF0000><B>14&nbsp;&nbsp;</B></FONT></TT></TD><TD ALIGN=RIGHT NOWRAP><TT><FONT COLOR=#FF0000><B>0.59&nbsp;</B></FONT></TT></TD><TD ALIGN=RIGHT><TT><FONT COLOR=#FF0000><B>1&nbsp;&nbsp;</B></FONT></TT></TD><TD NOWRAP><TT><FONT COLOR=#FF0000><B>K.SGLSTSTK.Y</B></FONT></TT></TD></TR>
    //<TR><TD ALIGN=RIGHT>&nbsp;</TD><TD ALIGN=RIGHT><TT><A HREF="peptide_view.pl?file=../data/20051115/F001254.dat&query=132&hit=8&index=gi%7c6323913&px=1" TARGET="_blank" onMouseOver="statusString = h2_q132; if (!browser_EXCLUDE) activateEl('Q132', event)" onMouseOut="clearEl()">132</A>&nbsp;&nbsp;</TT></TD><TD ALIGN=RIGHT><TT>   959.56&nbsp;&nbsp;</TT></TD><TD ALIGN=RIGHT><TT>   958.55&nbsp;&nbsp;</TT></TD><TD ALIGN=RIGHT><TT>   958.53&nbsp;&nbsp;</TT></TD><TD ALIGN=RIGHT NOWRAP><TT>     0.02&nbsp;</TT></TD><TD ALIGN=RIGHT><TT>0&nbsp;&nbsp;</TT></TD><TD ALIGN=RIGHT NOWRAP><TT>(9)&nbsp;</TT></TD><TD ALIGN=RIGHT NOWRAP><TT>2.3&nbsp;</TT></TD><TD ALIGN=RIGHT><TT>8&nbsp;&nbsp;</TT></TD><TD NOWRAP><TT>K.SPSSLDILK.N</TT></TD></TR>
    
    //version 1.9
    //<TR><TD><INPUT TYPE="checkbox" NAME="QUE" VALUE="1264.352000 from(633.184000,2+) title(A01_12928%2e572%2e577%2e2%2edta) query(152)" CHECKED></TD><TD NOWRAP><TT>&nbsp;<A HREF="peptide_view.pl?file=../data/20051011/F013234.dat&query=152&hit=1&index=gi%7c6322583&px=1" TARGET="_blank" onMouseOver="statusString = h1_q152; if (NS4 || IE4) activateEl('Q152', event)" onMouseOut="clearEl()">152</A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<FONT COLOR=#FF0000><B>633.18&nbsp;&nbsp;&nbsp;&nbsp;1264.35&nbsp;&nbsp;&nbsp;&nbsp;1263.65&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;0.71&nbsp;&nbsp;&nbsp;&nbsp;0&nbsp;&nbsp;&nbsp;&nbsp;58&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;&nbsp;<!--"gi|6322583":0:55:65:1,-->DASLVDYVQVR</B></FONT></TT></TD></TR>
    //<TR><TD>&nbsp;</TD><TD NOWRAP><TT>&nbsp;<A HREF="peptide_view.pl?file=../data/20030822/F007472.dat&query=175&hit=1&index=gi%7c229552&px=1" TARGET="_blank" onMouseOver="statusString = h2_q175; if (NS4 || IE4) activateEl('Q175', event)" onMouseOut="clearEl()">175</A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<FONT COLOR=#FF0000>488.18&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;974.34&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;973.45&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;0.89&nbsp;&nbsp;&nbsp;&nbsp;0&nbsp;&nbsp;&nbsp;&nbsp;31&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;&nbsp;<!--"gi|30794280":0:37:44:2,"gi|229552":0:13:20:2,"gi|1351907":0:37:44:2,-->DLGEEHFK</FONT></TT></TD></TR>
    //<TR><TD><INPUT TYPE="checkbox" NAME="QUE" VALUE="913.102000 from(457.559000,2+) title(A04_7391%2e0317%2e0317%2e2%2edta) query(70)" CHECKED></TD><TD NOWRAP><TT>&nbsp;&nbsp;<A HREF="peptide_view.pl?file=../data/20030206/F002582.dat&query=70&hit=1&index=gi%7c6321477&px=1" TARGET="_blank" onMouseOver="statusString = h1_q70; if (NS4 || IE4) activateEl('Q70', event)" onMouseOut="clearEl()">70</A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<FONT COLOR=#FF0000><B>457.56&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;913.10&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;912.54&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;0.56&nbsp;&nbsp;&nbsp;&nbsp;0&nbsp;&nbsp;&nbsp;&nbsp;41&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;&nbsp;("gi|6321477":0:79:86:1,)VRPVSIDK</B></FONT></TT></TD></TR>
       
    if( strstr($buffer, 'peptide_view.pl?file=') and $hitStart){
	  
        $buffer_org = $buffer;
	  /*
        //before remove html tags, get peptide location
        if(preg_match("/<!--\"gi\|(.*)-->/", $buffer, $matched_arr)){
          echo $matched_arr[1];
          $position_arr = explode(':',$matched_arr[1]);
          $pepLocatoins[$pNum][$peptide_num] = $position_arr[2]."-".$position_arr[3];
        }
	  */
        //remove html tags
        $buffer = preg_replace("/<[\/\!]*?[^<>]*?>/e", "",$buffer);
	  //$buffer = preg_replace("/&nbsp;(&nbsp;)*/", "", $buffer);
        $buffer = preg_replace("/(&nbsp;)++/", "prohits", $buffer);
	  $buffer = preg_replace("/[ ]+/", "", $buffer);
        $buffer = preg_replace("/^(prohits)++/", "", $buffer);
        $tmpArr = explode('prohits',$buffer);
	  
         
	  
        $pepMZ[$pNum][$peptide_num] = $tmpArr[1]/1000;  //$pepMZ[$pNum][$peptide_num]
        $pepMSExpects[$pNum][$peptide_num] = $tmpArr[2]/1000;   
        $pepMass[$pNum][$peptide_num] = $tmpArr[3]/1000;
	  $pepCharges[$pNum][$peptide_num] = round($pepMass[$pNum][$peptide_num]/$pepMZ[$pNum][$peptide_num]); // $pepCharges[$pNum][$peptide_num]
        $pepMiss[$pNum][$peptide_num] = $tmpArr[5];                                        //$pepMiss[$pNum][$peptide_num]  
        $pepScores[$pNum][$peptide_num] = preg_replace("/[()]/","", $tmpArr[6]);                                //$pepMass[$pNum][$peptide_num]
        if($MascotVersion == "2.1"){
	  	$modify_num = 9;
	  }else{
	  	$modify_num = 8;
	  }
	  
        for($modify_num; $modify_num < count($tmpArr); $modify_num++){
          $pepSequences[$pNum][$peptide_num] .= $tmpArr[$modify_num];
        }
	  
        $peptide_num++;
        
    }
    if(!strstr($buffer, "<A NAME=\"Hit") and strstr($buffer,"protein_view.pl")){
    //if(0 === strpos($buffer, '<A HREF="protein_view.pl?file=') and $hitStart){
        if(preg_match ('/gi\|[0-9][0-9][0-9]*/', $buffer, $tmp_gi)){
          $pRedundantGIs[$pNum] .= $tmp_gi[0].";";
        }
    }
    if(strstr($buffer, 'Peptide matches not assigned to protein hits') or strstr($buffer,"<H3>Search Parameters</H3>")){
      $endFile = 1;
    }
}//======================end of file reading================================
fclose($fd);


// update msManager/msWell set the well has been saved by the user
$SQL = "update MsWell set Saved='". $USER->id . "' where MascotResults='". substr($file, 2)."'";
echo $SQL;
mysql_query($SQL);
echo "<h2>Frank is working on this page !!!</h2><p>";
flush();
//====================== insert into database ==============================
if(count($pGIs)){

  //connect database
  if($targetDB == "yeast"){
    change_db("yeast");
  }else if($targetDB == "general"){
    change_db("general");
  }else if($targetDB == "mammalian"){
    connect_prohits_mml();
    //get band species
  }
  
  //check band accession permission
  $record_permission = 0;
  $sql = " select B.ID from Band B, User U, ProPermissions P  where U.id=P.UserID 
           and P.ProID=B.ProjectID 
           and U.username='".$username."' 
           and B.ID='$band_id'";
  if(mysqli_num_rows(mysql_query($sql))){
    $record_permission = 1;
  }else{
    $sql = "select B.ID from Band B, User U where U.id=B.OwnerID 
            and U.username='".$username."' 
            and B.ID='$band_id'";
    if(mysqli_num_rows(mysql_query($sql))){
      $record_permission = 1;
    }
  }
  if(!$record_permission){
    echo "<h2><font color=red>You have no permission to access this record (Band ID: $band_id)
          .</font></h2>";
    exit;
  }
  
  //band information
  $sql = "select W.ID, W.PlateID, W.BandID, B.BaitID from Band B, PlateWell W
           where B.ID = W.BandID and B.ID = '$band_id'";
  $row = mysqli_fetch_array(mysql_query($sql));
  $well_id = $row['ID'];
  $bait_id = $row['BaitID'];
  $plate_id = $row['PlateID'];
  
  //get user ID in the target database. there is a same username in msManager and prohits
  $row = mysqli_fetch_array(mysql_query("select ID from User where username='$username'"));
  $user_id = $row['ID'];
  $sql = "SELECT E.Species FROM Experiment E, Band B WHERE E.ID = B.ExpID and B.ID='$band_id'";
  $row = mysqli_fetch_array(mysql_query($sql));
  $pSpecies = $row['Species'];
  
  
  //echo $pcounter;  
  //number of hits should $pcounter - 1
  for($num=1; $num<$pcounter; $num++){
    $checkbox_name = "protein_".$num; 
    $MW = 0;
    $Hits->ID = 0;
    if($$checkbox_name and $pGIs[$num]){ 
      //this hit has been checked to save to ProHits
      //if($targetDB == "yeast" and !is_exsist_hit($band_id,$pGIs[$num])){
      if($pSpecies == 'Homo sapiens'){
          //make the protein gi as refseq gi
          //if found return locus id
          //$pRedundantGIs[$num] = 'gi|12345234;gi|20127479;gi|235345;';
          //$pGIs[$num] = '1469167';
          //get_refseq(&$pGIs[$num], &$pRedundantGIs[$num],&$pNames[$num],&$pMasses[$num]);
	    get_refseq($num);
      }
      //echo $pGIs[$num] . '|'.$pRedundantGIs[$num].'|'.$pNames[$num].'|'.$pMasses[$num];
      //exit;
      
      if( !is_exsist_hit($band_id,$pGIs[$num])){
        //get the protein ORFName from local database $pGIs[$num]);
        if($targetDB == "yeast"){
          $sql = "SELECT ORFName FROM YeastORF2GI where GI='".$pGIs[$num]."'";
          $row = mysqli_fetch_array(mysql_query($sql));
          $pORFName = $row['ORFName'];
        }else if($pSpecies == 'Homo sapiens'){
          
        }else if($pSpecies == 'Neurospora crassa'){
	  $sql = "SELECT LocusID FROM NEUROSPORA_ref where GI='".$pGIs[$num]."'";
          $row = mysqli_fetch_array(mysql_query($sql));
          $pORFName = $row['LocusID'];
	}
        $MW = round($pMasses[$num],2);
        if(!$MW) {
          $pSequence[$num] = get_seqence_from_NCBI($pGIs[$num]);
          $MW = calcProteinMass($pSequence[$num]);
        }//end of MW check
         
        if($frm_ORFName){
           //add this hit into Bait2Hits table
           //get Bait ORF if the record already in Bait2Hits table, it will not be added.
           $SQL = "select ORFName from Bait where ID=$bait_id";
           $row = mysqli_fetch_row(mysql_query($SQL));
           $baitORF = $row[0];
           $SQL = "INSERT INTO Bait2Hits SET BaitORF='$baitORF', HitORF='$pORFName'";
           mysql_query($SQL);
        }
        
        $SQL ="INSERT INTO Hits SET 
          WellID='$well_id', 
          BaitID='$bait_id', 
          BandID='$band_id', 
          Instrument='$instrument', 
          ORFName='$pORFName', 
          HitGI='" .$pGIs[$num]."', 
          HitName='".addslashes($pNames[$num])."', 
          Expect='".$pScores[$num]."',
          MW='$MW',
          RedundantGI='".$pRedundantGIs[$num]."',
          ResultFile='$file', 
          SearchDatabase='$searchedDB', 
          DateTime=now(),
          SearchEngine='Mascot', 
          OwnerID='$user_id'";
        //echo $SQL;
         
        mysql_query($SQL);
        if($hit_id = mysql_insert_id()){
          $newHitsStr .= ','.$hit_id;
          
        }
        //print_r ($pepSequences[$num]);
        //exit;
        for($pepNum=0; $pepNum < count($pepSequences[$num]); $pepNum++){
           if($hit_id){
             $SQL ="INSERT INTO Peptide SET 
                  HitID='$hit_id', 
                  Charge='".$pepCharges[$num][$pepNum]."', 
                  MASS='".$pepMass[$num][$pepNum]."', 
                  Location='".$pepLocatoins[$num][$pepNum]."', 
                  Expect='".$pepScores[$num][$pepNum]."',
                  Miss='".$pepMiss[$num][$pepNum]."',
                  Sequence='".trim($pepSequences[$num][$pepNum])."'";
              //echo $SQL;
              //echo "<br>";
              mysql_query($SQL);
           }
        }//end for loop
        
      } //end if target is yeast.
    }//end if -- checkbox has been checked, only checked hits will be saved
  }//end for -- all hits
  //display information  let user check saved records or back to sarch enchine
  //pass objects to function.
  //**************************************
  if($targetDB == 'prohitsDev'){
    $docDir = "/usr/local/apache/htdocs/yeastDev";
  }else if($targetDB == 'yeast' or $targetDB == 'prohits'){
    $docDir = "/usr/local/apache/htdocs/yeast";
  }else if($targetDB == 'mammalian'){
    $docDir = "/usr/local/apache/htdocs/mammalian";
  }
  if(docDir){
  //add hit auto notes
    exec("php $docDir/checkCarryOver.inc.php ". $plate_id);
  }
   get_output($newHitsStr, $band_id);
   exit;
  //**************************************
}//=====================end of inserting ===================================

//------------------------
//if the specie is human it will 
// use protein gi to compare and make redundant gis
// the refseq gi as the protein gi
//------------------------
//function get_refseq(&$gi, &$redundantgis,&$name, &$MW){
function get_refseq($num){
  global $pGIs, $pRedundantGIs, $pNames, $pMasses;
  //if found record in HOMO_ref return LocusID
  $rt = '';
  //$redundantgis = 'gi|12345234;gi|20127479;gi|235345;';
  $sql = "select LocusID, GI from HOMO_ref where GI='". $pGIs[$num]."'";
  if($row = mysqli_fetch_row(mysql_query($sql))){
    //return LocusID
    $rt = $row[0];
  }else{
    //echo "process redundant gis";
    $tmp_arr = explode(';', $pRedundantGIs[$num]);
    $found = 0;
    for($i = 0; $i < count($tmp_arr) - 1; $i++){
      $tmp_arr[$i] = str_replace('gi|',"", $tmp_arr[$i]);
      if(!$found){
        $sql = "select LocusID, GI, Definition, Sequence from HOMO_ref where GI='".$tmp_arr[$i]."'";
        if($row = mysqli_fetch_row(mysql_query($sql))){
          $gi_tmp = $gi;
          $pGIs[$num] = $tmp_arr[$i];
          $pNames[$num] = $row[2];
          $pMasses[$num] = calcProteinMass($row[3]);
          $tmp_arr[$i] = $gi_tmp;
          $found = 1;
          $rt = $row[0];
	    
        }
      }
      $redundant_str .= "gi|".$tmp_arr[$i].";";
    }//end for
    $pRedundantGIs[$num] = $redundant_str;
  }
  return $rt;
}//end function

//-----------------------
//check if the hit is in 
//database
//-----------------------
function is_exsist_hit($band_id,$hit_gi){
  $rt = 0;
  $SQL = "select ID from Hits where BandID='$band_id' and HitGI='$hit_gi' and SearchEngine='Mascot'";
  
  if( mysqli_num_rows(mysql_query($SQL)) ){
    $rt = 1;
  }
  return $rt;
}//end function

// ---- -----------------
//calExpect(string str)
//retrun float
//-----------------------

function calExpect($str){
  $str = trim($str);
  $expect = str_replace('?0','e',$str);
  return $expect; //retrun 2.3e-25 string
} 
//------------------------
// pass GI number to NCBI websit to get protein sequence
// function return upper caase sequence
//------------------------
function get_seqence_from_NCBI($GI){ 
  if(!$GI) return 0;
  $url = "http://www.ncbi.nih.gov/entrez/eutils/efetch.fcgi?rettype=gp&retmode=text&db=protein&id=$GI";
  //echo $url;exit;
  $fhand = @fopen ($url, "r");
  if(!$fhand) {
    echo "NCBI can't be accessed now. Click back button try again later.<br>$url";
    exit;
  }
  while (!feof ($fhand)) { 
    $ncbiFile = fgets($fhand, 40960);
    if( preg_match('/^ORIGIN/',$ncbiFile)) $seqStart = 1;
    if($seqStart) $seq .= $ncbiFile;
  }
  $seq = str_replace('ORIGIN','',$seq);
  $seq = preg_replace('/[0-9| |\n|\/]/','',$seq);  //remove number and number
  $seq = trim(strtoupper($seq));
  return $seq;
}
//--------------------------
// pass Sequence to calculate MS
// function return MW
//--------------------------
function calcProteinMass($sequence){
  # amino acid monoisotopic residue molecular weights
  $masses = array(
    'A' => 71.079,
    'B' => 0,
    'C' => 103.145,
    'D' => 115.089, 
    'E' => 129.116,
    'F' => 147.177,
    'G' => 57.052,
    'H' => 137.141,
    'I' => 113.160,
    'J' => 0,
    'K' => 128.17,
    'L' => 113.160,
    'M' => 131.199,
    'N' => 114.104,
    'O' => 0,
    'P' => 97.117,
    'Q' => 128.131,
    'R' => 156.188,
    'S' => 87.078,
    'T' => 101.105,
    'U' => 0,
    'V' => 99.133,
    'W' => 186.213,
    'X' => 0,
    'Y' => 163.176,
    'Z' => 0
  );
  $sequence = strtoupper($sequence);
  $chars = preg_split('//', $sequence, -1, PREG_SPLIT_NO_EMPTY);
  $tempMass = 0;
  for ($i=0; $i<count($chars); $i++){
     $tempMass = $tempMass + $masses[$chars[$i]];
  }  
  return  round(($tempMass + 18)/1000, 2);
}//end of function
//------------------------
//go page hits_saved.php
//$newHitsStr = ",321322,323"
//------------------------
function get_output($newHitsStr, $band_id){
 global $SID;
 global $file;
 global $host;
 global $targetDB;
?>
  <html>
  <head>
  </htad>
  <body>
  <script language="javascript">
   <?php if($newHitsStr){?>
      document.location = 'insert_confirm.php?SID=<?php echo  $SID;?>&host=<?php echo $host;?>&targetDB=<?php echo $targetDB;?>&file=<?php echo  $file;?>&newHitsStr=<?php echo  $newHitsStr;?>&band_id=<?php echo  $band_id;?>';
    <?php }else{?>
       alert("The protein GI has been saved to band <?php echo $band_id;?>. You cann't resubmit.");
       //document.location = './add_checkbox.php?file=<?php echo $file;?>&host=<?php echo $host;?>&targetDB=<?php echo $targetDB;?>';
    <?php }?>
  </script>
  </body>
  </html>
<?php 
}
?>
