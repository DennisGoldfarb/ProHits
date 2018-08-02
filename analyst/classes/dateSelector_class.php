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

class DateSelector{
 function setDate($inName, $useDate="", $displayDay=true) 
 { 
 	  $output = "";
     //create array so we can name months 
     $monthName = array(1=> "January",  "February",  "March", 
         "April",  "May",  "June",  "July",  "August", 
         "September",  "October",  "November",  "December"); 

     //if date invalid or not supplied, use current time 
     if($useDate == "") 
     { 
         $useDate = Time(); 
     }else if(preg_match("/[0-9]+[-][0-9]+[-]/", $useDate)){
         $useDate =@strtotime($useDate);
     }

     /* 
     ** make month selector 
     */ 
     $output .= "<select name=" . $inName .  "Month>\n"; 
     for($currentMonth = 1; $currentMonth <= 12; $currentMonth++) 
     { 
         $output .= "<option value=\""; 
         $output .= intval($currentMonth); 
         $output .= "\""; 
         if(intval(@date( "m", $useDate))==$currentMonth) 
         { 
             $output .= " selected"; 
         } 
         $output .= ">" . $monthName[$currentMonth] .  "\n"; 
     } 
     $output .= "</select>"; 

    
     /* 
     ** make day selector 
     */ 
     if($displayDay){
       $output .= "<select name=" . $inName .  "Day>\n"; 
       for($currentDay=1; $currentDay <= 31; $currentDay++) 
       { 
           $output .= "<option value=\"$currentDay\""; 
           if(intval(@date( "d", $useDate))==$currentDay) 
           { 
               $output .= " selected"; 
           } 
           $output .= ">$currentDay\n"; 
       } 
       $output .= "</select>"; 
     }

     /* 
     ** make year selector 
     */ 
     $output .= "<select name=" . $inName .  "Year>\n"; 
     $startYear = @date( "Y", $useDate); 
     for($currentYear = $startYear - 10; $currentYear <= $startYear+10;$currentYear++) 
     { 
         $output .= "<option value=\"$currentYear\""; 
         if(@date( "Y", $useDate)==$currentYear) 
         { 
             $output .= " selected"; 
         } 
         $output .= ">$currentYear\n"; 
     } 
     $output .= "</select>"; 
	 return $output;
	}// end of date selector function-------------------------
}//end of class
?>