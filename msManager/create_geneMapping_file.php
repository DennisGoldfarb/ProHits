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

require("../common/site_permission.inc.php");
include_once("../common/common_fun.inc.php");
include("common_functions.inc.php");
require_once("is_dir_file.inc.php");

check_geneMapping_file("","HUMAN_Ref57cRapREVg");
check_geneMapping_file("","HUMAN_RefV57cRAPg");
check_geneMapping_file("","YEAST_Ref57cRapREVg");

check_geneMapping_file("HUMAN_RefV57_cRAPandREVgene_20130130.fasta");
check_geneMapping_file("HUMAN_RefV57_cRAPgene_20130129.fasta");
check_geneMapping_file("YEAST_RefV57_cRAPandREVgene_20130129.fasta");

?>
