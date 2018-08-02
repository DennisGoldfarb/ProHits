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


class Bait2Hits{
  var $BaitORF;
  var $HitORF;
  var $link;
  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function Bait2Hits(){
     global $HITSDB;
     $this->link = $HITSDB->link;
  }//function end
 
  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall() {
    $SQL = "SELECT 
         BaitORF, 
         HitORF
         FROM Bait2Hits";
    $SQL .= " ORDER BY BaitORF";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->BaitORF[$i], 
         $this->HitORF[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_BaitORF='', 
       $frm_HitORF=''){
    if(!$frm_BaitORF or !$frm_HitORF ){
      //echo "missing info: ... insert aborted.";
      //exit;
    }else{
      $SQL ="INSERT INTO Bait2Hits SET 
          BaitORF='$frm_BaitORF', 
          HitORF='$frm_HitORF'";
      mysqli_query($this->link, $SQL);
    }
  }//end insert

  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID) {
    if($ID) {
      $SQL = "DELETE FROM Bait2Hits WHERE ID = '$ID'";
      mysqli_query($this->link, $SQL);
   }else{
      echo "Need id to delete!!!";
   }
  }//end of delete function
  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update(
         $frm_BaitORF='', 
         $frm_HitORF=''){
      $SQL ="UPDATE Bait2Hits SET 
         BaitORF='$frm_BaitORF', 
         HitORF='$frm_HitORF'
         WHERE ID =$frm_ID ";
      mysqli_query($this->link, $SQL);
  }//end of function update
  function get_total_baits(){
     $SQL = "select count(distinct BaitORF) from Bait2Hits";
    //echo $SQL;
    $row = mysqli_fetch_array(mysqli_query($this->link, $SQL));
    return $row[0];
  }
  //----------------------------------------------
  //   get num of baits that can pull the protein out. 
  //----------------------------------------------
  function fetchall_HitORF_num() {
    $SQL = "select HitORF, count(HitORF) from Bait2Hits group by HitORF"; 
    $i = 0;
    //echo "<br> $SQL";
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->HitORF[$i],
         $this->BaitNum[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall
  //----------------------------------------------
  //  look for new record from hits table
  //----------------------------------------------
  function check_new_record(){
    $SQL = "select B.ORFName ,H.ORFName from Hits H, Bait B where B.ID=H.BaitID";
    $results = mysqli_query($this->link, $SQL);
    while($row = mysqli_fetch_array($results) ){
      if($row[0] and $row[1]){
        @mysqli_query($this->link, "insert into Bait2Hits set BaitORF='$row[0]', HItORF='$row[1]'");
      }
      //echo "insert into Bait2Hits set BaitORF='$row[0]', HItORF='$row[1]'". "<br>";
      //exit;
    }
  }
}//end of class
?>
