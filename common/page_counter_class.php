<?php
/***********************************************************************
    Prohits version 1.00
    Copyright (C) 2001, Mike Tyers, All Rights Reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
*************************************************************************/

class PageCounter {
  var $StartPoint; 
  var $NumRows;         //total records
  var $ResultsPerPage; //records will be displaied in each page
  var $MaxPages;        //number of page links will be show on the page
  var $Querystring;      //variables will be passed with a page link
  var $javascript;

  function PageCounter($jscript = "") {
    $this->StartPoint = 0;
    $this->NumRows = 0;
    $this->ResultsPerPage = 0;
    $this->MaxPages = 0;
    $this->Querystring = "";
		$this->javascript = $jscript;
    
  }
  function page_links($StartPoint = 0, $NumRows = 0, $results_per_page = 10, $MaxPages = 10, $Querystring = "") {
    global $PHP_SELF;
    global $caption;
    $output ='';
    $num_pages = intval(($NumRows-1) / $results_per_page) + 1;
     
    $tmpstr = ($num_pages > 1)?" Pages":" Page";
    $output .= "<font FACE='verdana,Arial, Helvetica'  size=2> Total $caption : $NumRows (" . $num_pages . $tmpstr.")";
  	
    //print("Page:");
  	$temp_point=0;
  	$row_count=0;

  	$current_page = intval($StartPoint / $results_per_page) + 1;
  	// Cap the number of pages displayed per result page. 
  	if ($num_pages > $MaxPages)
  	{
  		$page_start = ((intval($current_page / $MaxPages)) * $MaxPages) + 1;
  		if (intval($current_page / $MaxPages) != intval($num_pages/$MaxPages))
  		{
  			$page_end = $page_start + $MaxPages - 1;
  		}
  		else
  		{
  			$page_end = $num_pages;
  		}
  	}
  	else
  	{
  		$page_start = 1;
  		$page_end = $num_pages;
  	}
    if($current_page == $num_pages and $num_pages >= $MaxPages){
	  	$temp_point = (intval($current_page/$MaxPages) - 1) * $MaxPages * $results_per_page;
		}else{
  		$temp_point = (intval($current_page/$MaxPages)) * $MaxPages * $results_per_page;
	  }
		$j = 0;
  	for ($j=$page_start; $j<=$page_end; $j++)
  	{
  		$row_count++;
  		if ($current_page == $j)
  		{
  			$output .= " <font FACE='verdana,Arial, Helvetica' color=\"red\">$j</font> ";
  		}
  		else
  		{
			  if($this->javascript){
					$output .= " <A href=\"javascript: ".$this->javascript."($temp_point)\">";
				}else{
  				$output .= " <A href=\"$PHP_SELF?start_point=$temp_point&$Querystring\">";
			  }
  			$output .= "<font FACE='verdana,Arial, Helvetica'  size=2>$j</FONT></A> ";
  		}
  		$temp_point = $temp_point + $results_per_page;
  	}
  
  	// Give the option of viewing the next 10 pages if the result set is big.
  	if ($j< $num_pages and ($num_pages > $MaxPages) && (intval($current_page / $MaxPages) != intval($num_pages/$MaxPages)))
  	{
  	  $temp_point = (intval($current_page / $MaxPages)+1) * $results_per_page * $MaxPages;
			if($this->javascript){
				$output .= " (<A href=\"javascript: ".$this->javascript."($temp_point)\">";
		  }else{
  			$output .= " (<A href=\"$PHP_SELF?start_point=$temp_point&$Querystring\">";
  		}
  		$output .="<font FACE='verdana,Arial, Helvetica'  size=2>Next $MaxPages pages</font></A>)";
  	}
  
  	// Give the option of viewing the previous 10 pages if the result set is big.
  	if (($num_pages > $MaxPages) && (intval($current_page / $MaxPages) != 0))
  	{
  		$temp_point = ((intval($current_page / $MaxPages)) * $results_per_page * $MaxPages) - ($results_per_page * $MaxPages);
  		if($this->javascript){
					$output .= " (<A href=\"javascript: ".$this->javascript."($temp_point)\">";
		  }else{
				$output .= ' (<A href="'. $PHP_SELF. '?start_point='.$temp_point. '&' .$Querystring. '">';
  		} 
  		$output .= "<font FACE='verdana,Arial, Helvetica'  size=2>Previous $MaxPages pages</font></A>)";
  	}
    return $output;
  }//end of page_links function ===========================
}
?>
