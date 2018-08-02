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


$aIons = array();
$a2Ions = array();
$asIons = array();
$as2Ions = array();
$a0Ions = array();
$a02Ions = array();
$bIons = array();
$b2Ions = array();
$bsIons = array();
$bs2Ions = array();
$b0Ions = array();
$b02Ions = array();
$cIons = array();
$c2Ions = array();
$immIons = array();
$intyaIons = array();
$intybIons = array();
$xIons = array();
$x2Ions = array();
$yIons = array();
$y2Ions = array();
$ysIons = array();
$ys2Ions = array();
$y0Ions = array();
$y02Ions = array();
$zIons = array();
$z2Ions = array();
$configFile = array();    // holds mascot.dat
$delta_masses = array();
$exp_masses = array();
$fields = array();        // holds entire result file
$ignoreMass = array();
$intensityList = array();
$intyaIonsByColumn = array();
$intybIonsByColumn = array();
$intybLabels = array();
$labelsByColumn = array();
$massList = array();
$pepFields = array();
$pepMatches = array();
$protTemp = array();
$residues = array();
$runningSum = array();
$summer = array();
$temp_masses = array();
$typeList = array();
//------------------------------------------------------------------
$include = array();     //******ok
$indexArr = array();    //******ok     // keys and values from index block
$labelList = array();   //******ok
$labels = array();
$masses = array();      //******ok  // keys and values from masses block
$neutralLoss = array(); //******ok
$parameters = array();  //******ok # keys and values from parameters block
$peptides = array();    //******ok
$queryArr = array();    //******ok
$seriesSig = array();   //******ok
$summary = array();     //******ok  // keys and values from summary block
$vmMass = array();      //******ok
$vmString = array();    //******ok  // mass deltas for variable mods 
//-----------------------------------------------------------------
$debug = '';
$displayRange = '';
$encoded = '';
$fieldCode = '';
$fileIn = '';        // result file path passed as URL argument 'file'
$firstTick = ''; 
$frameNum = '';
$hitNum = '';        // hit number
$i = '';             // general purpose loop variable 
$indexNum = '';
$indexSave = '';
$ionsData = '';      // flag set if ions data present
$ionsDP = '';
$j = '';
$lastTick = '';
$leftMargin = '';
$massDP = '';        // number of decimal places in displayed mass values
$massMax = '';
$massMin = '';
$matchList = '';
$newMass = '';
$numCalcVals = '';
$numRes = '';
$overallHeight = '';
$overallWidth = '';
$parseRule = '';
$parseString = '';
$peaks = '';
$peptide = '';
$px = '';
$queryNum = '';      // query number
$realMatch = '';
$rightMargin = '';
$scoop = '';
$seqReport = '';
$shipper = '';
$temp = '';
$tempString = '';
$thisScript = '';    // CGI object
$tickInterval = '';   
$title = '';
$tmpLeft = '';
$tmpMatch = '';
$tmpRight = '';
$topMargin = ''; 
$xClick = '';
$xScale = '';
$accession = '';
$argString = '';
$blockName = '';     // name of next block to be unpacked from result file
$bottomMargin = '';
$boundary = '';      // MIME boundary string
$charge = '';
//------------------------------------------------
$tick1 = '';  
$tick_int = '';
$range = '';   
$from = '';
$to = '';
$gif_x = ''; 
//------------------------------------------------
require("../common/site_permission.inc.php");

$queryNum = 12;
$hitNum = 1;
$start_time = @date("F j, Y, g:i a");                 
//$file?$fileIn=$file:fatalError("no file name", __LINE__);
//$query?$queryNum=$query:fatalError("no query number", __LINE__);
//$hit?$hitNum=$hit:fatalError("no hit number", __LINE__);

if(!($firstTick = $tick1)) $firstTick = 1;  
if(!($tickInterval = $tick_int)) $tickInterval = 1;    
if(!($displayRange = $range)) $displayRange = 10 ;    
if(!($massMin = $from)) $massMin = -1 ;
if(!($massMax = $to)) $massMax = 1e99 ;
if(!($xClick = $gif_x)) $xClick = -1;
if(!$scoop) $scoop = 2;                
$overallWidth = 550;    
$overallHeight = 300;
$leftMargin = 20;          
$rightMargin = 20;      
$topMargin = 100;          
$bottomMargin = 10;     
$debug = 'FALSE';

//$massDP and $ionsDP come from mascot.dat-------
$massDP = 4;
$ionsDP = 3;
if($massDP < 1 || $massDP > 5) {
  $massDP = 2;
}
if($ionsDP < 1 || $ionsDP > 5) {
  $ionsDP = 1;
}

$parameters['iatol'] = '';
$parameters['iastol'] = '';
$parameters['ibtol'] = '';
$parameters['ibstol'] = ''; 
$parameters['iytol'] = ''; 
$parameters['ia2tol'] = ''; 
$parameters['ib2tol'] = ''; 
$parameters['iy2tol'] = ''; 
$parameters['peak'] = 'AUTO';
$parameters['db'] = 'SwissProt';
$parameters['file'] = 'C:\Auto MSMS output\Sample 1.pkl';
$parameters['mass'] = 'Monoisotopic';
$parameters['mods'] = ''; 
$parameters['itolu'] = 'Da';
$parameters['rules'] = '1,2,8,9,10,13,14,15';
$parameters['itol'] = '0.2';

// whole $masses array
$masses['a'] = 71.037114;
$masses['b'] = 114.534940;
$masses['c'] = 103.009185;
$masses['d'] = 115.026943;
$masses['e'] = 129.042593;
$masses['f'] = 147.068414;
$masses['g'] = 57.021464;
$masses['h'] = 137.058912;
$masses['i'] = 113.084064;
$masses['j'] = 0.000000;
$masses['k'] = 128.094963;
$masses['l'] = 113.084064;
$masses['m'] = 131.040485;
$masses['n'] = 114.042927;
$masses['o'] = 0.000000;
$masses['p'] = 97.052764;
$masses['q'] = 128.058578;
$masses['r'] = 156.101111;
$masses['s'] = 87.032028;
$masses['t'] = 101.047679;
$masses['u'] = 150.953630;
$masses['v'] = 99.068414;
$masses['w'] = 186.079313;
$masses['x'] = 111.000000;
$masses['y'] = 163.063329;
$masses['z'] = 128.550590;
$masses['hydrogen'] = 1.007825;
$masses['carbon'] = 12.000000;
$masses['nitrogen'] = 14.003074;
$masses['oxygen'] = 15.994915;
$masses['electron'] = 0.000549;
$masses['c_term'] = 17.002740;
$masses['n_term'] = 1.007825;
$masses['delta1'] = "15.994919,Oxidation (M)";
$masses['neutralloss1'] = 0.000000;
$masses['neutralloss1_master'] = 63.998291;

$queryArr['charge'] = "2+";
$queryArr['mass_min'] = 70.250000;
$queryArr['mass_max'] = 1916.610000;
$queryArr['int_min'] = 1.078;
$queryArr['int_max'] = 613.3;
$queryArr['num_vals'] = 594;
$queryArr['num_used1'] = -1;
$queryArr['ions1'] =  "129.100000:144.3,248.150000:280.5,286.130000:613.3,399.220000:180.1,559.380000:39.47,571.379074:8.389,745.450000:110.9,777.370000:1.103,157.090000:121,187.071941:207.5,347.220000:230,460.310000:102.9,470.290000:32.2,599.330000:7.202,746.460000:49.6,836.020000:1.103,147.110000:115.2,213.146708:134,287.129616:106.5,371.220000:86.49,498.280000:28.61,581.320000:6.412,688.420000:20.57,836.320000:1.103,130.090765:57.66,258.138286:106.7,342.187921:60.07,385.210000:76.8,560.382227:27.12,600.370000:5.142,727.450000:16.63,102.064662:40.21,185.162622:100.9,357.200000:42.27,400.215757:47.32,500.270000:18.61,572.350000:4.363,747.440000:12.12,86.103699:36.75,229.110000:97.94,348.220000:41.33,387.190000:36.34,473.224400:15.83,635.342535:4.268,689.428043:7.526,159.080000:33.86,201.120000:95.16,340.180000:37.81,461.293539:29.88,471.300000:15.5,634.360000:4.228,728.440000:7.239,141.080000:17.59,230.140000:72.27,314.200000:33.98,453.270000:24.41,486.273449:14.46,590.330000:3.276,671.430000:6.387,158.102586:17.57,249.139464:38.07,312.213463:33.86,372.230000:19.61,482.270000:13.37,666.380000:3.23,670.420000:6.241,148.136428:10.74,183.100000:35.05,329.218427:30.1,386.215033:17.57,480.290000:11.44,582.270000:2.209,744.410000:4.273,70.250000:1.103,72.100000:6.522,72.480000:1.103,73.100000:2.174,74.060000:1.103,75.070000:1.103,81.540000:1.103,84.083660:5.288,86.790000:1.103,87.110000:1.103,95.100000:1.103,98.060000:1.103,100.070000:3.138,101.110000:6.506,101.740000:1.103,103.160000:2.159,110.070000:4.273,111.090000:2.174,112.090000:1.103,113.070000:1.103,114.120000:1.103,115.980000:1.103,121.090000:1.103,123.050000:1.103,124.040000:2.197,127.110000:3.296,128.100000:3.261,129.655000:2.205,130.635000:2.103,131.090000:7.562,132.090000:2.103,139.090000:2.205,140.105000:2.19,140.440000:1.103,142.036382:4.341,143.120000:2.197,144.120000:3.276,146.090000:1.103,147.655000:2.205,149.110000:1.103,152.090000:1.103,154.030000:1.103,155.110000:6.264,156.390000:1.103,157.440000:1.103,157.820000:1.103,158.410000:1.103,160.051711:5.457,160.455000:2.205,161.650000:1.103,162.100000:1.103,163.950000:1.103,166.075000:2.103,166.600000:1.103,167.116455:4.165,168.130000:1.103,169.100000:9.387,170.040000:3.143,171.130000:2.103,171.550000:1.103,173.120000:27.56,174.165253:4.379,175.132820:5.376,175.608236:3.205,176.080000:3.125,176.480000:1.103,177.115000:2.217,178.815000:2.103,179.990000:1.103,184.060000:2.217,184.400000:2.205,184.790000:1.103,185.740293:2.211,186.142588:11.58,186.590000:1.103,188.049766:16.04,188.370000:2.136,189.090742:5.406,189.910000:2.205,191.290000:1.103,192.090000:2.102,195.085347:5.453,196.100000:4.209,196.670000:1.103,197.120000:3.132,197.590000:1.103,198.115000:2.22,199.104604:4.206,200.130000:4.316,200.530000:1.103,202.130000:14.87,203.097086:7.382,203.810000:1.103,204.130000:10.57,205.060000:1.103,206.090000:1.103,207.130000:2.103,208.110000:1.103,208.640000:2.205,209.983005:3.292,210.680000:1.103,211.100000:23.84,211.640000:1.103,212.120000:16.66,212.790000:1.103,213.790000:1.103,214.151905:18.23,214.690000:1.103,215.118452:4.166,216.130000:1.103,217.080000:1.103,218.130000:4.379,219.043997:3.205,220.100000:1.103,221.070000:1.103,222.130000:1.103,223.065000:2.103,224.100000:1.103,225.132531:5.261,226.114971:3.18,227.110000:1.103,229.810000:2.205,230.640000:1.103,231.124687:24.69,232.112569:7.393,232.626701:4.364,234.100000:1.103,235.100000:3.261,236.100000:2.205,237.140000:1.103,238.120000:2.174,239.172980:3.271,239.530000:1.103,240.111682:25.32,240.460000:1.103,241.118664:20.26,241.490000:2.209,242.092088:4.314,242.530000:1.103,243.098360:8.219,245.120000:1.103,246.130000:3.126,247.230000:2.205,247.640000:1.103,248.820000:1.089,249.623906:2.18,250.028047:9.444,250.400000:2.205,251.056710:6.386,251.470000:1.103,252.630000:1.103,253.080000:8.303,253.500000:1.103,254.130000:1.103,255.130000:10.64,256.140000:13.55,256.830000:1.103,258.510000:1.103,259.140000:15.47,259.720000:2.102,260.205365:3.205,261.740000:1.103,262.280000:2.205,263.580000:1.103,264.890000:1.103,265.620000:1.103,266.170000:2.197,267.210000:6.261,268.130000:18.5,268.560000:1.103,269.159264:12.36,270.157680:17.99,271.130000:2.136,271.800000:1.103,272.149221:3.279,273.150000:2.174,274.170000:1.103,276.140000:1.103,277.130000:4.15,278.140000:1.103,278.800000:1.103,279.160000:5.198,279.450000:1.103,279.820000:1.103,280.140000:1.103,281.090000:1.103,282.130000:2.205,283.200484:3.276,284.175869:3.255,284.550000:1.103,285.117937:5.365,285.700000:2.205,286.545691:3.258,287.529345:4.309,287.818208:6.479,288.097846:27.66,288.555662:4.308,289.180000:1.103,289.500000:1.103,289.989161:3.221,290.655000:2.103,291.870000:1.103,292.220000:1.103,292.600000:1.103,293.460000:1.103,294.196200:4.174,296.176905:26.72,297.134492:10.39,298.217955:5.423,299.220000:1.103,300.150000:11.37,301.150000:3.214,301.570000:1.103,302.170000:1.103,303.190000:4.332,303.670000:1.103,304.130000:6.211,304.570000:1.103,305.065000:2.205,306.150000:3.114,308.150000:1.103,309.810000:1.103,310.570000:1.103,311.193578:7.364,312.590000:1.103,313.194271:4.272,315.210000:12.22,315.790000:1.103,316.140000:2.136,316.920000:1.103,317.200000:1.103,318.660000:1.103,319.020000:1.103,320.180000:2.102,321.120000:2.205,322.165000:2.22,323.140000:2.091,323.620000:2.205,324.173957:30.04,325.170000:5.201,325.630000:1.103,326.150000:4.108,327.095000:2.103,328.140000:1.103,328.715000:2.205,330.223396:8.788,330.790000:2.174,331.180000:2.159,332.190000:1.103,332.600000:1.103,333.140000:2.197,333.750000:2.205,334.125000:2.22,334.650000:1.103,335.114927:3.268,335.750000:1.103,336.180000:3.276,337.180000:1.103,338.190000:1.103,338.820000:1.103,339.158329:6.277,341.194541:11.46,342.520000:2.217,343.180000:10.52,344.134401:4.363,345.630000:1.103,346.060000:2.205,347.890000:2.22,348.767915:3.205,349.203601:12.63,350.037768:4.253,351.130000:2.103,352.070000:2.205,352.360000:1.103,352.630000:1.103,353.220000:16.39,354.200000:28.9,355.200000:10.5,355.520000:1.103,356.120000:2.205,356.780000:1.103,358.225870:8.322,358.880000:1.103,359.190000:8.283,360.180000:1.103,361.180000:1.103,362.580000:1.103,363.040000:1.103,364.120000:2.217,365.102312:4.261,366.170000:2.091,366.700000:2.205,367.160240:6.191,368.200000:4.126,369.189282:21.01,369.790000:2.174,370.150000:2.197,371.570000:2.103,371.900000:1.103,372.730000:1.103,373.171467:7.437,373.490000:1.103,375.149697:3.263,376.210000:1.103,377.215000:2.103,378.130000:2.19,378.640000:1.103,379.130000:2.103,380.190000:3.143,381.183428:12.18,381.630000:1.103,382.210000:5.364,383.150000:5.16,384.220000:1.103,386.745000:2.205,387.650000:1.103,388.152706:7.338,389.160000:2.197,391.250000:1.103,392.070000:1.103,392.610000:1.103,393.095000:2.217,394.610000:1.103,395.260000:6.192,396.270000:8.172,397.250000:4.318,398.267017:5.281,398.540000:1.079,398.860000:1.103,399.950000:1.079,400.600000:1.103,400.860000:1.103,401.220000:14.21,401.490000:1.103,402.099801:3.3,402.620000:1.103,403.140000:1.103,404.110000:1.103,405.130344:3.189,406.170000:1.103,407.250000:3.125,409.160000:4.167,410.200000:1.103,411.200000:1.103,411.650000:1.103,412.230000:1.103,413.260000:16.64,414.270000:3.132,415.162954:3.296,416.131544:5.328,417.225091:3.279,427.190000:3.15,428.880000:1.103,429.230000:2.103,430.210000:1.103,432.217500:4.411,433.270000:1.103,435.250000:5.343,437.260000:4.171,438.230000:1.103,439.240000:1.103,440.250000:2.103,441.260000:10.28,441.790000:1.103,442.290000:12.34,443.270000:12.19,444.230000:1.103,445.250000:1.103,446.940000:1.103,447.300000:2.159,449.510000:1.103,450.210000:4.147,450.895000:2.155,452.280000:7.275,454.260000:17.46,455.250000:10.28,456.250000:3.138,456.910000:1.103,457.225000:2.159,458.270000:3.214,458.910000:1.103,459.223220:3.294,461.000000:2.203,462.262420:8.407,463.190000:2.174,464.220000:2.091,465.270000:1.103,466.250000:1.103,467.136180:3.284,468.270000:4.301,469.269693:3.268,472.260000:10.19,472.800240:2.211,474.145000:2.205,475.770000:1.103,477.060000:1.103,479.200000:2.205,481.290000:3.214,483.280000:1.103,484.230000:3.132,485.260000:1.103,487.260000:1.103,487.590000:1.103,488.250000:2.136,490.230000:1.103,492.190000:1.103,492.950000:1.103,496.240000:1.103,497.190000:2.205,497.520000:1.103,499.300000:10.38,499.970000:1.103,500.800000:1.103,501.270000:5.236,502.240000:1.103,504.240000:1.103,508.264722:3.285,508.770000:1.103,510.290000:1.103,514.280000:2.103,515.280000:3.132,516.250000:1.103,518.300000:1.103,521.215000:2.111,523.200000:1.103,524.310000:6.521,527.340000:1.103,529.530000:1.103,533.350000:1.103,536.290000:2.174,537.200000:1.103,538.310000:2.136,540.350000:1.103,541.323683:4.292,542.350000:4.161,543.950000:1.103,545.155000:2.103,545.590000:1.103,547.890000:1.103,551.090000:1.103,553.330000:2.174,554.340000:2.205,555.330000:4.363,556.040000:1.103,557.320000:1.103,559.660000:1.078,561.340000:4.178,563.300000:6.209,564.320000:3.126,568.275000:2.209,569.340000:1.103,573.360000:2.136,574.340000:1.103,576.160000:1.103,576.920000:1.103,579.330000:1.103,580.150000:1.103,585.060000:1.103,591.290000:1.103,593.360000:1.103,596.300000:1.103,598.370000:1.103,603.395000:2.155,609.570000:1.103,612.230000:1.103,614.130000:1.103,615.360000:1.103,617.330000:1.103,621.200000:1.103,624.320000:1.103,625.740000:1.103,629.280000:1.103,630.310000:1.103,632.360000:1.103,637.420000:1.103,643.300000:1.103,646.430000:1.103,652.380000:1.103,661.330000:2.136,662.340000:2.174,663.030000:1.103,669.430000:1.103,672.340000:1.103,673.370000:4.143,674.390000:1.103,679.700000:1.103,680.130000:1.103,680.410000:1.103,682.420000:1.103,683.410000:3.143,684.380000:2.136,690.440000:1.103,691.080000:1.103,693.410000:1.103,700.390000:1.103,701.420000:2.103,702.320000:2.217,704.590000:1.103,708.330000:2.174,710.400000:2.136,711.410000:2.174,714.210000:1.103,726.450000:2.197,728.920000:1.103,731.463328:3.131,742.420000:1.103,743.460000:1.103,744.120000:1.103,745.920000:1.103,747.100000:1.103,748.760000:1.103,750.680000:1.103,756.290000:1.103,757.200000:1.103,759.920000:1.103,981.760000:1.103,1070.390000:1.103,1082.500000:1.103,1247.110000:1.103,1818.610000:1.103,1916.610000:1.103";

$blockName= "query" . $queryNum;
unBlock($queryArr, $massList, $intensityList, $typeList) || fatalError("could not unpack $blockName from $fileIn", __LINE__);

$summary['h1'] = 'CH60_HUMAN,1.40e+03,0.48,61016.38';
$summary['h1_text'] = '60 kDa heat shock protein, mitochondrial precursor (Hsp60) (60 kDa chaperonin) (CPN60) (Heat shock';
$summary['h1_frame'] = '';
$summary['h1_q12'] = '0,843.506577,-0.034557,345,352,7.00,VGEVIVTK,24,0000000000,45.74,2,0001002000000000000,0,0,1662.450000';
$summary['qexp12'] = '422.743286,2+';
$summary['h1_q12_et_mods'] = '';

//$fieldsStr come from infile 'proteins' section-----------------------
$fieldsStr = '"CH60_BOVIN"=61069.43,"60 kDa heat shock protein, mitochondrial precursor (Hsp60) (60 kDa chaperonin) (CPN60) (Heat shock "';
 
$peptides['q12_p1'] = '0,843.506577,-0.034557,7,VGEVIVTK,24,0000000000,45.74,0001002000000000000,0,0;"CH60_BOVIN":0:345:352:2,"CH60_CHICK":0:345:352:2,"CH60_CRIGR":0:345:352:2,"CH60_HUMAN":0:345:352:2,"CH60_MOUSE":0:345:352:2,"CH60_PONPY":0:345:352:2,"CH60_RAT":0:345:352:2';
$peptides['q12_p1_et_mods'] = '';
  
if(isset($massList[0]) && $massList[0]){
  $ionsData = 1;
} else {
  $ionsData = 0;
}
/*
echo "<pre>\$parameters ";
print_r($parameters); 
echo "</pre>";
echo "<pre>\$masses ";
print_r($masses);
echo "</pre>";
echo "<pre>\$queryArr ";
print_r($queryArr);
echo "</pre>";
echo "<pre>\$summary ";
print_r($summary);
echo "</pre>";
*/
/*
echo "<pre>\$peptides ";
print_r($peptides);
echo "</pre>";
*/
/* 
echo "<br>";
echo $fieldsStr;
echo "</br>";
*/

if($px){  // called from peptide summary&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  if(!isset($index)) $index = '';
  $indexSave = $index;
  $indexNum = $indexSave;
  // accession string may contain meta characters
  $indexNum = preg_replace('/(\W)/', '\\\\\1', $indexNum);
  
  //$peptides['q12_p1'] = '0,843.506577,-0.034557,7,VGEVIVTK,24,0000000000,45.74,0001002000000000000,0,0;"CH60_BOVIN":0:345:352:2,"CH60_CHICK":0:345:352:2,"CH60_CRIGR":0:345:352:2,"CH60_HUMAN":0:345:352:2,"CH60_MOUSE":0:345:352:2,"CH60_PONPY":0:345:352:2,"CH60_RAT":0:345:352:2';
  //$peptides['q12_p1_et_mods'] = '';
  
  if(isset($peptides["q".$queryNum."_p".$hitNum]) && $peptides["q".$queryNum."_p".$hitNum]){
    $realMatch = 1;
    $tmpRight = trim($peptides{"q".$queryNum."_p".$hitNum});
  }else{
    $realMatch = 0;
  }
    
  if(!$realMatch || $tmpRight == "-1"){
  // no match, just echo picture
    $realMatch = 0;
    $accession = "zilch";
  }else{    
    if(!$indexNum){
      // if no index supplied, use the first accession string listed
      if(preg_match('/;\"?(.+?)\"?:/', $tmpRight, $matches)){
        $indexSave = $matches[1];
        $indexNum = $indexSave;
        $indexNum = preg_replace('/(\W)/', '\\\\\1', $indexNum);
      }  
    }
    
    if(preg_match('/\"?'.$indexNum.'\"?:(\d+?):(\d+?):(\d+?):(\d+?)/', $tmpRight, $matches)){    
      $frameNum = $matches[1];
      $summer[3] = $matches[2];
      $summer[4] = $matches[3];
      $summer[10] = $matches[4];
      $tmpArr = explode(';', $tmpRight);
      $tmpRight = $tmpArr[0];
      $pepFields = explode(',',$tmpRight);
      
      //$fieldsStr come from infile 'proteins' section-----------------------
      //$fieldsStr = '"CH60_BOVIN"=61069.43,"60 kDa heat shock protein, mitochondrial precursor (Hsp60) (60 kDa chaperonin) (CPN60) (Heat shock "';
      
      list($tmpLeft,$tmpRight) = explode('=', $fieldsStr, 2);
      $protTemp = explode(',' ,$tmpRight,2);
      $protTemp[1] = noDoubleQoute($protTemp[1]);
      $accession=$tmpLeft;
      $title=$protTemp[1];
      $summer[0] = $pepFields[0];
      $summer[1] = $pepFields[1];
      $summer[2] = $pepFields[2];
      $summer[5] = $pepFields[3];
      $summer[6] = $pepFields[4];
      $summer[7] = $pepFields[5];
      $summer[8] = $pepFields[6];
      $summer[9] = $pepFields[7];
      if(isset($pepFields[8]) && strlen($pepFields[8]) > 8){
        $summer[11]=$pepFields[8];
        if(isset($pepFields[9])){
          $summer[12]=$pepFields[9];
        }
        if (isset($pepFields[10])){
          $summer[13]=$pepFields[10];
        }
      }
    ksort($summer);
    } else {
      fatalError("cannot find $indexSave in results file", __LINE__);
    }
  }
}else{
  // called from protein summary&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  // assume a real match
  $realMatch = 1;
  if(preg_match('/^(.*),(.*?),(.*?),(.*?)$/', $summary["h"."$hitNum"], $matches)){
    $accession = $matches[1];
  }else{
    fatalError("cannot find accession in array \$summary", __LINE__);
  }
  if(isset($summary["h".$hitNum."_frame"]) && $summary["h".$hitNum."_frame"]){
    $frameNum = $summary["h".$hitNum."_frame"];
  }else{
    $frameNum = 0;
  }
  $summer = explode(',', $summary["h".$hitNum."_q".$queryNum]);
}

$tmpArr = explode(',',$summary["qexp".$queryNum]);
if(preg_match('/Mr/', $tmpArr[1])){
  $charge = 0;
}elseif(preg_match('/(\d)\+/', $tmpArr[1], $matches)){
  $charge = $matches[1];
}

/**********************************************************************************************
 version X scoring scheme required ion series to be specified explicitly
 version Y scoring scheme iterated 6 ion series: a, a*, b, b*, y, y* 
 plus  a++, b++, y++ if precursor charge was 2 or more
 version Z scoring scheme iterates ion series specified by INSTRUMENT entry from fragmentation_rules file
 both versions Y and Z record scoring by ion series as "bit pattern" in $summer[11]
   0 means matches not significant
   1 means matches not chosen to contribute to score
   2 means matches contribute to score
*********************************************************************************************/
if(isset($summer[11]) && $summer[11]){
  if(strlen($summer[11]) > 9) {
    $tmpArr = str_split($summer[11]);
    list($seriesSig['iatol'],
        $seriesSig['iastol'],
        $seriesSig['ia2tol'],
        $seriesSig['ibtol'],
        $seriesSig['ibstol'],
        $seriesSig['ib2tol'],
        $seriesSig['iytol'],
        $seriesSig['iystol'],
        $seriesSig['iy2tol'],
        $seriesSig['ictol'],
        $seriesSig['ic2tol'],
        $seriesSig['ixtol'],
        $seriesSig['ix2tol'],
        $seriesSig['iztol'],
        $seriesSig['iz2tol']) = $tmpArr;
        
    $seriesSig['iastol'] = '';
    $seriesSig['ibstol'] = '';
    $seriesSig['iystol'] = '';
    
    getRules();
  }elseif(strlen($summer[11]) == 9) {
  // version Y
    $tmpArr = str_split($summer[11]);
    list( $seriesSig{'iatol'},
      $seriesSig['iastol'],
      $seriesSig['ia2tol'],
      $seriesSig['ibtol'],
      $seriesSig['ibstol'],
      $seriesSig['ib2tol'],
      $seriesSig['iytol'],
      $seriesSig['iystol'],
      $seriesSig['iy2tol'] ) = $tmpArr;
    $include['aIons'] = 1;
    $include['asIons'] = 1;
    $include['bIons'] = 1;
    $include['bsIons'] = 1;
    $include['yIons'] = 1;
    $include['ysIons'] = 1;
    if ($charge > 1){
      $include['a2Ions'] = 1;
      $include['b2Ions'] = 1;
      $include['y2Ions'] = 1;
    }
    $include['immIons'] = 1;
  }else{
  // version X
    if ($parameters['iatol'] > 0) $include['aIons'] = 1;
    if ($parameters['iastol'] > 0) $include['asIons'] = 1;
    if ($parameters['ibtol'] > 0) $include['bIons'] = 1;
    if ($parameters['ibstol'] > 0) $include['bsIons'] = 1;
    if ($parameters['iytol'] > 0) $include['yIons'] = 1;
    if ($parameters['iystol'] > 0) $include['ysIons'] = 1;
    if ($charge > 1) {
      if ($parameters['ia2tol'] > 0) $include['a2Ions'] = 1;
      if ($parameters['ib2tol'] > 0) $include['b2Ions'] = 1;
      if ($parameters['iy2tol'] > 0) $include['y2Ions'] = 1;
    }
  }
}
/*
echo "<pre>\$include ";
print_r($include);
echo "</pre>";
*/
//------------------------------------------

if($realMatch){
  $peaks = $parameters['peak'];
  if($summer[7]){
    $peaks = $summer[7];
  }
  if($summer[12]){
    $peaks += $summer[12];
  }
  if(defined($summer[13])){
    $peaks += $summer[13];
  }
  $peptide = strtolower($summer[6]);
  $numRes = strlen($peptide);
  $residues = str_split($peptide);   // one residue letter per array element

// Create hash for neutral losses, and lookup arrays for variable mods and masses to be ignored

  foreach($masses as $keys => $value){
  
    if(preg_match('/^delta(\d+)/i', $keys, $matches)){
      $value = trim($value);
      list($vmMass[$matches[1]], $vmString[$matches[1]]) = explode(',', $value);
    }elseif(preg_match('/^NeutralLoss[_]*(.*)/i', $keys, $matches)){
      $value = trim($value);
      $neutralLoss[$matches[1]] = $value;
    }elseif(preg_match('/^Ignore(\d+)/i', $keys, $matches)){
      $value = trim($value);
      $ignoreMass[$matches[1]] = $value;
    }
  }

  // Add on any mod found in error tolerant search
  if($px && isset($peptides["q".$queryNum."_p".$hitNum."_et_mods"]) && $peptides["q".$queryNum."_p".$hitNum."_et_mods"]){
    list($vmMass['X'], $neutralLoss['X'], $vmString['X']) = explode(',', $peptides["q".$queryNum."_p".$hitNum."_et_mods"], 3);
  }elseif(isset($summary["h".$hitNum."_q".$queryNum."_et_mods"]) && $summary["h".$hitNum."_q".$queryNum."_et_mods"]){
    list($vmMass['X'], $neutralLoss['X'], $vmString['X']) = explode(',', $summary["h".$hitNum."_q".$queryNum."_et_mods"], 3);
  } 
  
  $_SESSION["massList"] = $massList;
  $_SESSION["intensityList"] = $intensityList;
  //calculate fragment ion masses
  // if there are variable mods, they will be included in the running sum
  calcIons();
  // and matches to experimental data
  findMatches();
}

if(isset($queryArr['mass_min']) && $queryArr['mass_min'] && isset($queryArr['mass_max']) && $queryArr['mass_max']){
//work out zoom state for gif
  if($xClick == -1){
  //first time through or user clicked on submit button
    if($massMin < $queryArr['mass_min']){
      $massMin = $queryArr['mass_min'];
    }
    if($massMax > $queryArr['mass_max']){
      $massMax = $queryArr['mass_max'];
    }
    if ($massMax <= $massMin){
      $massMin = $queryArr['mass_min'];
      $massMax = $queryArr['mass_max'];
    }
    calcRange();
  }else{
  //user clicked on gif to zoom in factor of 2
  //work out the mass value of the mouse click
    $xScale = ($overallWidth-$leftMargin - $rightMargin) / $displayRange;
    $newMass = (($xClick-$leftMargin) / $xScale) + $firstTick;
  //want to zoom in about this mass, while staying within the mass range of the data
    if(($newMass-$displayRange/4) < $queryArr['mass_min']){
      $massMin = $queryArr['mass_min'];
    }else{
      $massMin = $newMass - $displayRange/4;
    }      
    if(($massMin + $displayRange/2) > $queryArr['mass_max']){
      $massMax=$queryArr['mass_max'];
    }else{
      $massMax = $massMin + $displayRange/2;
    }
    if(($massMax - $displayRange/2) < $massMin){
      if(($massMax - $displayRange/2) >= $queryArr['mass_min']){
        $massMin = ($massMax - $displayRange/2);
      }else{
        $massMin =$queryArr['mass_min'];
      }
    }
    calcRange();
  }
  $lastTick = $firstTick + $displayRange;
}
?>
<HTML>
<HEAD>
<TITLE>Mascot Search Results: Peptide View</TITLE>
</HEAD>
<script language='javascript'>
function popTest(){
  file = "./msms_gif.php?tick1=<?php echo $firstTick?>&tick_int=<?php echo $tickInterval?>&range=<?php echo $displayRange?>&matches=<?php echo $argString?>";
  nWin = window.open(file,"image",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=800');
  nWin.moveTo(4,0);
}


function popImage(ppp){
  file = './mass_error.php?' + ppp;
  nWin = window.open(file,"image",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=800');
  nWin.moveTo(4,0);
}
</script>
<BODY BGCOLOR="#ffffff" ALINK="#0000ff" VLINK="#0000ff">
<!--H1><IMG SRC="../images/88x31_logo_white.gif" WIDTH="88" HEIGHT="31"
ALIGN="TOP" BORDER="0" NATURALSIZEFLAG="3"> Mascot Search Results</H1-->
<?php 
$massMin = floor($massMin*100)/100;
$massMax = floor($massMax*100)/100;
// output form containing MS/MS gif
if($realMatch){
  $peptide = strtoupper($peptide);
  $accession = noDoubleQoute($accession);
?> 
<H3>Peptide View</H3>
MS/MS Fragmentation of <B><FONT COLOR=#FF0000><?php echo $peptide?></FONT></B><br>
Found in <B><FONT COLOR=#FF0000><?php echo noDoubleQoute($accession)?></FONT></B>, <?php echo noDoubleQoute($title)?>
<?php 
  
  if($frameNum){
    echo "<br>Translated in frame $frameNum ";  
    $encoded = $accession;
    $encoded = preg_replace('/(\W)/', '%20', $accession);
    echo "(<A HREF=\"../cgi/getseq.pl?".$parameters['db']."+$encoded+seq+$frameNum+$summer[3]+$summer[4]\" TARGET=\"_blank\">nucleic acid sequence</A>)<BR>\n";
  }
  echo "<BR>\n";
  $fieldCode = '%.'.$massDP.'f';
  echo "<P>Match to Query ".$queryNum." (" . vsprintf("$fieldCode,%s", split(",",$summary["qexp"."$queryNum"])).")&nbsp;";
  if(isset($queryArr['title']) && $queryArr['title']){
    //------$queryArr['title'] = preg_replace('/%([\dA-Fa-f][\dA-Fa-f])/', pack("C", hexdec(\1)), $queryArr['title']);
    echo noTag($queryArr['title']);
  }
  echo "<BR>";
  if(isset($parameters['file']) &&  $parameters['file']){
    echo "From data file ".noTag($parameters['file'])."<BR>\n";
  }
}else{
  $peptide = "";
  echo "<H3>Peptide View</H3>\n";
  echo "Query $queryNum: No match found<BR>\n";
}


//----------------------------------------------------------------------------------------------------------------
// don't display gif if no mass data (e.g. sequence query)
if($ionsData){
  display_gif();
}
//---------------------------------------------------------------------------------------------------------------------
?>
<table><tr><td></td></tr></table>
<a href='javascript: popTest();'>[Test Image]</a>
</td></tr></table>
<?php 
if($realMatch){
  display_table();
  mass_error_distribution_graph();
  others();
?> 
<P><TABLE WIDTH="100%" BORDER="2" CELLSPACING="2" CELLPADDING="1">
<TR><TD ALIGN="CENTER" NOWRAP><B>Mascot:</B>&nbsp;
<A HREF="http://www.matrixscience.com/index.html">http://www.matrixscience.com/</A>
</TD></TR>
</TABLE>
<?php 
all_matches_to_this_query();
}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//display information functions
//***********************************************************************************************************
//display all matches to this query
//***********************************************************************************************************
function all_matches_to_this_query(){
  global $queryNum,$fileIn,$px;
  $inOneQuerry = in_one_query($queryNum);
  if($inOneQuerry){
?>  
    <p><b>All matches to this query</b><br>
    <p><TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2>
      <TR BGCOLOR=#cccccc>
        <TH>Hit</TH>
        <TH>Score</TH>
        <TH>Mr(calc):</TH>
        <TH>Delta</TH>
        <TH>Sequence</TH>
      </TR>
<?php     
    foreach($inOneQuerry as $value){
?>    
        <TR>
          <TD><?php echo $value['hit'];?></TD>
          <TD><?php echo $value['score'];?></TD>
          <TD><?php echo $value['mr'];?></TD>
          <TD><?php echo $value['delta'];?></TD>
          <TD><a href="<?php echo $PHP_SELF;?>?file=<?php echo $fileIn;?>&hit=<?php echo $value['hit']?>&px=<?php echo $px?>&query=<?php echo $queryNum;?>&section=5"><?php echo $value['sequence'];?></a></TD>
        </TR>
<?php       
    }
    echo "</table>\n<p>\n";
  } 
}  

//***************************************************************************************************************
//display others information
//***************************************************************************************************************
function others(){
  global $peptide;  
  echo "<P>NCBI <B>BLAST</B> search of <A HREF=\"";
  echo "http://www.ncbi.nlm.nih.gov/blast/Blast.cgi?ALIGNMENTS=50&ALIGNMENT_VIEW=Pairwise";
  echo "&AUTO_FORMAT=Semiauto&CLIENT=web&DATABASE=nr&DESCRIPTIONS=100&ENTREZ_QUERY=(none)";
  echo "&EXPECT=20000&FORMAT_BLOCK_ON_RESPAGE=None&FORMAT_OBJECT=Alignment&FORMAT_TYPE=HTML";
  echo "&GAPCOSTS=9+1&I_THRESH=0.001&LAYOUT=TwoWindows&MATRIX_NAME=PAM30&NCBI_GI=on";
  echo "&PAGE=Proteins&PROGRAM=blastp&QUERY=";
  echo $peptide;
  echo "&SERVICE=plain&SET_DEFAULTS.x=32&SET_DEFAULTS.y=7&SHOW_OVERVIEW=on&WORD_SIZE=2";
  echo "&END_OF_HTTPGET=Yes\" TARGET=\"_blank\">";
  echo "$peptide</A><BR>\n";
?>
(Parameters: blastp, nr protein database, expect=20000, no filter, PAM30)<BR>
Other BLAST <A HREF="../help/blast_help.html#web">web gateways</A><BR>
<?php 
}

//*********************************************************************************************
//display mass error distribution graph
//*********************************************************************************************
function mass_error_distribution_graph(){
  global $parameters, $exp_masses, $delta_masses;
  // graph of mass error distribution
  echo "<P><IMG SRC=\"mass_error.php?units=".$parameters['itolu']."&file=massList:";
  $tmpStr = "units=".$parameters['itolu']."&file=massList:";
  if(count($exp_masses)){
    echo sprintf("%.2f",$exp_masses[0]);
    $tmpStr .= sprintf("%.2f",$exp_masses[0]);
    for($i=1; $i<count($exp_masses); $i++){
      echo "," . sprintf("%.2f",$exp_masses[$i]);
      $tmpStr .= "," . sprintf("%.2f",$exp_masses[$i]);
    }
  }
  echo "&hit=errorList:";
  $tmpStr .= "&hit=errorList:";
  //print_r($delta_masses);
  if(count($delta_masses)){
    echo sprintf("%.6f",$delta_masses[0]);
    $tmpStr .= sprintf("%.6f",$delta_masses[0]);
    for($i=1; $i<count($delta_masses); $i++){
      echo ",".sprintf("%.6f",$delta_masses[$i]);
      $tmpStr .= ",".sprintf("%.6f",$delta_masses[$i]);
    }
  }
  echo "\" WIDTH=450 HEIGHT=150 ALT=\"Error Distribution\">\n";
}  

//*******************************************************************************
//display masses table
//*******************************************************************************
function display_table(){
  global $runningSum, $masses, $parameters, $massDP, $summer, $vmString, $residues, $numRes, $exp_masses, $massList, $numCalcVals,
         $peaks, $ionsDP, $peptide, $include, $seriesSig, $fieldCode, $intensityList, $ionMasses, $queryNum;
  global $aIons,$a2Ions,$asIons,$as2Ions,$a0Ions,$a02Ions,$bIons,$b2Ions,$bsIons,$bs2Ions,$b0Ions,$b02Ions,
          $cIons,$c2Ions,$immIons,$intyaIons,$intybIons,$xIons,$x2Ions,$yIons,$y2Ions,$ysIons,$ys2Ions,$y0Ions,
          $y02Ions,$zIons,$z2Ions;       
//*************************************************************************************  
  //print_r($include);exit;
  //print_r($seriesSig);exit;
  echo "<FONT FACE='Courier New,Courier,monospace'>\n";
  echo "<PRE>\n";
  $temp = $runningSum[$numRes-1] + $masses['n_term'] + $masses['c_term'];
  echo "<B>".$parameters['mass']." mass of neutral peptide (Mr):</B>". sprintf(" %.".$massDP."f",$temp)."\n";
  if($parameters['mods']){
    echo "<B>Fixed modifications: </B>".$parameters['mods']."\n";
  }
  if(preg_match('/[1-9A-FX]/', $summer[8])){
    echo "<B>Variable modifications: </B>\n";
    $temp = substr($summer[8],0,1);
    if(preg_match('/[1-9A-FX]/', $temp)){    
      echo "<B>N-term : </B>".$vmString[$temp]."\n";
    }
    for ($i=1; $i<strlen($summer[8])-1; $i++){
      $temp = substr($summer[8],$i,1);
      if(preg_match('/[1-9A-FX]/', $temp)){
        echo "<B>".strtoupper($residues[$i-1]).sprintf('%-2d',$i)."    : </B>".$vmString[$temp]."\n";
      }
    }
    $temp = substr($summer[8],-1,1);
    if(preg_match('/[1-9A-FX]/', $temp)){
      echo "<B>C-term : </B>".$vmString[$temp]."\n";
    }
  }
  printf("<B>Ions Score:</B> %-.f  ",$summer[9]);
  $i = count($exp_masses);
  if($massList[0]){
    echo "<B>Matches (<FONT COLOR=#FF0000>Bold Red</FONT>):</B> $i/$numCalcVals";
    echo " fragment ions using $peaks most intense peaks\n";
  } else {
    echo "\n";
  }
  echo "</PRE>\n";
  echo "</FONT>\n";
//echo table of fragment ion masses
  
  echo "<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2>\n";

  echo "  <TR BGCOLOR=#cccccc>\n";
  echo "    <TH>#</TH>\n";
  if (isset($include['immIons']) && $include['immIons']>0)echo "    <TH>Immon.</TH>\n";
  if (isset($include['aIons']) && $include['aIons']>0)  echo "    <TH>a</TH>\n";
  if (isset($include['a2Ions']) && $include['a2Ions']>0) echo "    <TH>a<SUP>++</SUP></TH>\n";
  if (isset($include['asIons']) && $include['asIons']>0) echo "    <TH>a*</TH>\n";
  if (isset($include['as2Ions']) && $include['as2Ions']>0)echo "    <TH>a*<SUP>++</SUP></TH>\n";
  if (isset($include['a0Ions']) && $include['a0Ions']>0) echo "    <TH>a<SUP>0</SUP></TH>\n";
  if (isset($include['a02Ions']) && $include['a02Ions']>0)echo "    <TH>a<SUP>0++</SUP></TH>\n";
  if (isset($include['bIons']) && $include['bIons']>0)  echo "    <TH>b</TH>\n";
  if (isset($include['b2Ions']) && $include['b2Ions']>0) echo "    <TH>b<SUP>++</SUP></TH>\n";
  if (isset($include['bsIons']) && $include['bsIons']>0) echo "    <TH>b*</TH>\n";
  if (isset($include['bs2Ions']) && $include['bs2Ions']>0)echo "    <TH>b*<SUP>++</SUP></TH>\n";
  if (isset($include['b0Ions']) && $include['b0Ions']>0) echo "    <TH>b<SUP>0</SUP></TH>\n";
  if (isset($include['b02Ions']) && $include['b02Ions']>0)echo "    <TH>b<SUP>0++</SUP></TH>\n";
  if (isset($include['cIons']) && $include['cIons']>0)  echo "    <TH>c</TH>\n";
  if (isset($include['c2Ions']) && $include['c2Ions']>0) echo "    <TH>c<SUP>++</SUP></TH>\n";
  echo "    <TH>Seq.</TH>\n";
  if (isset($include['xIons']) && $include['xIons']>0)  echo "    <TH>x</TH>\n";
  if (isset($include['x2Ions']) && $include['x2Ions']>0) echo "    <TH>x<SUP>++</SUP></TH>\n";
  if (isset($include['yIons']) && $include['yIons']>0)  echo "    <TH>y</TH>\n";
  if (isset($include['y2Ions']) && $include['y2Ions']>0) echo "    <TH>y<SUP>++</SUP></TH>\n";
  if (isset($include['ysIons']) && $include['ysIons']>0) echo "    <TH>y*</TH>\n";
  if (isset($include['ys2Ions']) && $include['ys2Ions']>0)echo "    <TH>y*<SUP>++</SUP></TH>\n";
  if (isset($include['y0Ions']) && $include['y0Ions']>0) echo "    <TH>y<SUP>0</SUP></TH>\n";
  if (isset($include['y02Ions']) && $include['y02Ions']>0)echo "    <TH>y<SUP>0++</SUP></TH>\n";
  if (isset($include['zIons']) && $include['zIons']>0)  echo "    <TH>z</TH>\n";
  if (isset($include['z2Ions']) && $include['z2Ions']>0) echo "    <TH>z<SUP>++</SUP></TH>\n";
  echo "    <TH>#</TH>\n";
  echo "  </TR>\n";
  
  $fieldCode = $ionsDP + 5;
  $fieldCode = '%'.$fieldCode.'.'.$ionsDP.'f';
  
  for ($i = 1; $i <= $numRes; $i++) {
    $j = $numRes - $i + 1;
    echo "  <TR ALIGN=\"RIGHT\">\n";
    echo "    <TD><B><FONT COLOR=#0000FF>$i</FONT></B></TD>\n";
    if (isset($include['immIons']) && $include['immIons']>0)  printMasses($i-1, $fieldCode, $immIons, 0);
    if (isset($include['aIons']) && $include['aIons']>0)   printMasses($i-1, $fieldCode, $aIons, $seriesSig['iatol']);
    if (isset($include['a2Ions']) && $include['a2Ions']>0)  printMasses($i-1, $fieldCode, $a2Ions, $seriesSig['ia2tol']);
    if (isset($include['asIons']) && $include['asIons']>0)  printMasses($i-1, $fieldCode, $asIons, $seriesSig['iastol']);
    if (isset($include['as2Ions']) && $include['as2Ions']>0) printMasses($i-1, $fieldCode, $as2Ions, 0);
    if (isset($include['a0Ions']) && $include['a0Ions']>0)  printMasses($i-1, $fieldCode, $a0Ions, 0);
    if (isset($include['a02Ions']) && $include['a02Ions']>0) printMasses($i-1, $fieldCode, $a02Ions, 0);
    if (isset($include['bIons']) && $include['bIons']>0)   printMasses($i-1, $fieldCode, $bIons, $seriesSig['ibtol']);
    if (isset($include['b2Ions']) && $include['b2Ions']>0)  printMasses($i-1, $fieldCode, $b2Ions, $seriesSig['ib2tol']);
    if (isset($include['bsIons']) && $include['bsIons']>0)  printMasses($i-1, $fieldCode, $bsIons, $seriesSig['ibstol']);
    if (isset($include['bs2Ions']) && $include['bs2Ions']>0) printMasses($i-1, $fieldCode, $bs2Ions, 0);
    if (isset($include['b0Ions']) && $include['b0Ions']>0)  printMasses($i-1, $fieldCode, $b0Ions, 0);
    if (isset($include['b02Ions']) && $include['b02Ions']>0) printMasses($i-1, $fieldCode, $b02Ions, 0);
    if (isset($include['cIons']) && $include['cIons']>0)   printMasses($i-1, $fieldCode, $cIons, $seriesSig['ictol']);
    if (isset($include['c2Ions']) && $include['c2Ions']>0)  printMasses($i-1, $fieldCode, $c2Ions, $seriesSig['ic2tol']);
    echo "    <TD ALIGN=\"CENTER\"><B><FONT COLOR=#0000FF>".strtoupper($residues[$i-1])."</FONT></B></TD>\n";
    if (isset($include['xIons']) && $include['xIons']>0)   printMasses($j-1, $fieldCode, $xIons, $seriesSig['ixtol']);
    if (isset($include['x2Ions']) && $include['x2Ions']>0)  printMasses($j-1, $fieldCode, $x2Ions, $seriesSig['ix2tol']);
    if (isset($include['yIons']) && $include['yIons']>0)   printMasses($j-1, $fieldCode, $yIons, $seriesSig['iytol']);
    if (isset($include['y2Ions']) && $include['y2Ions']>0)  printMasses($j-1, $fieldCode, $y2Ions, $seriesSig['iy2tol']);
    if (isset($include['ysIons']) && $include['ysIons']>0)  printMasses($j-1, $fieldCode, $ysIons, $seriesSig['iystol']);
    if (isset($include['ys2Ions']) && $include['ys2Ions']>0) printMasses($j-1, $fieldCode, $ys2Ions, 0);
    if (isset($include['y0Ions']) && $include['y0Ions']>0)  printMasses($j-1, $fieldCode, $y0Ions, 0);
    if (isset($include['y02Ions']) && $include['y02Ions']>0) printMasses($j-1, $fieldCode, $y02Ions, 0);
    if (isset($include['zIons']) && $include['zIons']>0)   printMasses($j-1, $fieldCode, $zIons, $seriesSig['iztol']);
    if (isset($include['z2Ions']) && $include['z2Ions']>0)  printMasses($j-1, $fieldCode, $z2Ions, $seriesSig['iz2tol']);
    echo "    <TD><B><FONT COLOR=#0000FF>$j</FONT></B></TD>\n";
    echo "  </TR>\n";
  }
  echo "</TABLE>\n";
  
  if((isset($include['intyaIons']) && $include['intyaIons']>0) || (isset($include['intybIons']) && $include['intybIons']>0)){
  //split list into 3 columns
    $j = 0;
    for($m=0; $m<3; $m++){
      $labelsByColumn[$m] = array();
      $intyaIonsByColumn[$m] = array();
      $intybIonsByColumn[$m] = array();
    }
    for($i = 0; $i < count($intyaIons); $i++){
      if($intyaIons[$i] < 700){
        array_push($labelsByColumn[$j % 3], $intybLabels[$i]);
        array_push($intyaIonsByColumn[$j % 3], $intyaIons[$i]);
        array_push($intybIonsByColumn[$j % 3], $intybIons[$i]);
        $j++;
      }
    }
    echo "<P><TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2>\n";
    echo "  <TR BGCOLOR=#cccccc>\n";
    echo "    <TH>Seq</TH>\n";
    if($include['intyaIons']>0) echo  "    <TH>ya</TH>\n";
    if($include['intybIons']>0) echo  "    <TH>yb</TH>\n";
    if(count($labelsByColumn[1])){
      echo "    <TH>Seq</TH>\n";
      if($include['intyaIons']>0) echo "    <TH>ya</TH>\n";
      if($include['intybIons']>0) echo "    <TH>yb</TH>\n";
    }
    if(count($labelsByColumn[2])){
      echo "    <TH>Seq</TH>\n";
      if($include['intyaIons']>0) echo "    <TH>ya</TH>\n";
      if($include['intybIons']>0) echo "    <TH>yb</TH>\n";
    }
    echo "  </TR>\n";
    $i = 0;
    while(isset($labelsByColumn[0][$i])){
      echo "  <TR ALIGN=\"LEFT\">\n";
      echo "    <TD><B><FONT COLOR=#0000FF>$" . $labelsByColumn[0][$i] . "</FONT></B></TD>\n";
      if($include['intyaIons']>0) printMasses($i, $fieldCode, $intyaIonsByColumn[0], 0);
      if($include['intybIons']>0) printMasses($i, $fieldCode, $intybIonsByColumn[0], 0);
      if(count($labelsByColumn[1]) && isset($labelsByColumn[1][$i]) && $labelsByColumn[1][$i]){
        echo "    <TD><B><FONT COLOR=#0000FF>" . $labelsByColumn[1][$i] . "</FONT></B></TD>\n";
        if($include['intyaIons']>0) printMasses($i, $fieldCode, $intyaIonsByColumn[1], 0);
        if($include['intybIons']>0) printMasses($i, $fieldCode, $intybIonsByColumn[1], 0);
      }elseif(count($labelsByColumn[1])){
        echo "    <TD>&nbsp;</TD>\n";
        if($include['intyaIons']>0) echo "    <TD>&nbsp;</TD>\n";
        if($include['intybIons']>0) echo "    <TD>&nbsp;</TD>\n";
      }
      if(count($labelsByColumn[2]) && isset($labelsByColumn[2][$i]) && $labelsByColumn[2][$i]){
        echo "    <TD><B><FONT COLOR=#0000FF>" . $labelsByColumn[2][$i] . "</FONT></B></TD>\n";
        if($include['intyaIons']>0) printMasses($i, $fieldCode, $intyaIonsByColumn[2], 0);
        if($include['intybIons']>0) printMasses($i, $fieldCode, $intybIonsByColumn[2], 0);
      }elseif(count($labelsByColumn[2])){
        echo "    <TD>&nbsp;</TD>\n";
        if($include['intyaIons']>0) echo "    <TD>&nbsp;</TD>\n";
        if($include{'intybIons'}>0) echo "    <TD>&nbsp;</TD>\n";
      }
      echo "  </TR>\n";
      $i++;
    }
    echo "</TABLE>\n";
  }
}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


/*****************************************************************************
//printMasses()
//$index index into ions array
//$fieldCode $fieldCode
//$ionMasses \@ionMasses
//$seriesSig $seriesSig{i*tol}
//globals:
//my(%labels, $debug);
//prints cell in ions table
*****************************************************************************/

function printMasses($index, $fieldCode, &$ionMasses, $seriesSig){
  /*echo $index."<br>";
  echo $fieldCode."<br>";
  echo $seriesSig."<br>";
  print_r($ionMasses);exit;*/

  global $labels, $debug;
  if(isset($ionMasses[$index]) && isset($labels["$ionMasses[$index]"]) && $labels["$ionMasses[$index]"]){
    if(preg_match('/^true/i', $debug)){
      if (isset($seriesSig) && $seriesSig == 2) {
        echo sprintf("    <TD><B><I><FONT COLOR=#FF0000>$fieldCode</FONT></I></B></TD>\n", $ionMasses[$index]);
      }elseif(isset($seriesSig) && $seriesSig == 1) {
        echo sprintf("    <TD><B><FONT COLOR=#FF0000>$fieldCode</FONT></B></TD>\n", $ionMasses[$index]);
      }else{
        echo sprintf("    <TD><FONT COLOR=#FF0000>$fieldCode</FONT></TD>\n", $ionMasses[$index]);
      }
    }else{
      echo sprintf("    <TD><B><FONT COLOR=#FF0000>$fieldCode</FONT></B></TD>\n", $ionMasses[$index]);
    }
  }elseif(isset($ionMasses[$index]) && $ionMasses[$index] > 0) {
    echo sprintf("    <TD>$fieldCode</TD>\n", $ionMasses[$index]);
  }else{
    echo "    <TD>&nbsp;</TD>\n";
  }
  return 1;
}


/************************************************************************
 &calcRange()
 no parameters
 globals:
 my($massMax, $massMin, $tickInterval, $firstTick, $displayRange);
 have to duplicate some of the code from msms_gif.pl here because
 there is no mechanism for it to return the actual mass range plotted
 find least power of 10 which is >= total mass range
**************************************************************************/

function calcRange(){
  global $massMax, $massMin, $tickInterval, $firstTick, $displayRange;
  $massRange = $massMax - $massMin;
  $i = 0;
  while(pow(10, $i) < $massRange) $i++;
// drop a power of 10 & find ceiling
  $j = 10;
  $i = pow(10, ($i-1));
  while ($j*$i > $massRange) $j--;
// drop another power of 10 & find ceiling
  $j = ($j+1)*10;
  $i = $i/10;
  while ($j*$i > $massRange) $j--;
// increase $tickInterval to get between 10 and 25 ticks
  $tickInterval = $i;
  $numTicks = $j + 2;
  if($numTicks > 50){
    $tickInterval *= 5;
  }elseif($numTicks>20){
    $tickInterval *= 2;
  }  
  $firstTick = intval($massMin / $tickInterval) * $tickInterval;
  $displayRange = intval(($massMax-$firstTick)/$tickInterval)*$tickInterval;
  while(($firstTick+$displayRange) < $massMax){
    $displayRange += $tickInterval;    
  }
  if($displayRange < 10){
    $displayRange = 10;
    $tickInterval = 1;
  }  
return 1;
}

/******************************************************************************
 getRules()
 no parameters
 globals:
 $charge, $include, $parameters;
********************************************************************************

 1  //singly charged 
      (required)
 2  //doubly charged if precursor 2+ or higher
      (not internal or immonium) 
 3  //doubly charged if precursor 3+ or higher
      (not internal or immonium) 
 4  //immonium
 5  //a series
 6  //a - NH3 if a significant and fragment includes RKNQ
 7  //a - H2O if a significant and fragment includes STED
 8  //b series
 9  //b - NH3 if b significant and fragment includes RKNQ
 10 //b - H2O if b significant and fragment includes STED
 11 //c series
 12 //x series
 13 //y series
 14 //y - NH3 if y significant and fragment includes RKNQ
 15 //y - H2O if y significant and fragment includes STED
 16 //z series
 17 //internal yb < 700 Da
 18 //internal ya < 700 Da
 19 //y or y++ must be significant
 20 //y or y++ must be highest scoring series
********************************************************************************/
function getRules(){
  global $charge, $include, $parameters;
  $chosen = array();
  $specified = array();
  
  if(!isset($parameters['rules']) || !$parameters['rules']){
    $parameters['rules'] = "1,2,5,6,8,9,13,14";
  }
  $specified = explode(',', $parameters['rules']);
  foreach($specified as $value){
    $chosen[$value] = 1;
  }
  // always include 1+ series, even though rules imply they can be omitted
  if(isset($chosen['4'])) $include['immIons'] = 1;
  if(isset($chosen['5'])) $include['aIons'] = 1;
  if(isset($chosen['5']) && isset($chosen['2']) && $charge > 1) $include['a2Ions'] = 1;
  if(isset($chosen['5']) && isset($chosen['3']) && $charge > 2) $include['a2Ions'] = 1;
  if(isset($chosen['6'])) $include['asIons'] = 1;
  if(isset($chosen['6']) && isset($chosen['2']) && $charge > 1) $include['as2Ions'] = 1;
  if(isset($chosen['6']) && isset($chosen['3']) && $charge > 2) $include['as2Ions'] = 1;
  if(isset($chosen['7'])) $include['a0Ions'] = 1;
  if(isset($chosen['7']) && isset($chosen['2']) && $charge > 1) $include['a02Ions'] = 1;
  if(isset($chosen['7']) && isset($chosen['3']) && $charge > 2) $include['a02Ions'] = 1;
  if(isset($chosen['8'])) $include['bIons'] = 1;
  if(isset($chosen['8']) && isset($chosen['2']) && $charge > 1) $include['b2Ions'] = 1;
  if(isset($chosen['8']) && isset($chosen['3']) && $charge > 2) $include['b2Ions'] = 1;
  if(isset($chosen['9'])) $include['bsIons'] = 1;
  if(isset($chosen['9']) && isset($chosen['2']) && $charge > 1) $include['bs2Ions'] = 1;
  if(isset($chosen['9']) && isset($chosen['3']) && $charge > 2) $include['bs2Ions'] = 1;
  if(isset($chosen['10'])) $include['b0Ions'] = 1;
  if(isset($chosen['10']) && isset($chosen['2']) && $charge > 1) $include['b02Ions'] = 1;
  if(isset($chosen['10']) && isset($chosen['3']) && $charge > 2) $include['b02Ions'] = 1;
  if(isset($chosen['11'])) $include['cIons'] = 1;
  if(isset($chosen['11']) && isset($chosen['2']) && $charge > 1) $include['c2Ions'] = 1;
  if(isset($chosen['11']) && isset($chosen['3']) && $charge > 2) $include['c2Ions'] = 1;
  if(isset($chosen['12'])) $include['xIons'] = 1;
  if(isset($chosen['12']) && isset($chosen['2']) && $charge > 1) $include['x2Ions'] = 1;
  if(isset($chosen['12']) && isset($chosen['3']) && $charge > 2) $include['x2Ions'] = 1;
  if(isset($chosen['13'])) $include['yIons'] = 1;
  if(isset($chosen['13']) && isset($chosen['2']) && $charge > 1) $include['y2Ions'] = 1;
  if(isset($chosen['13']) && isset($chosen['3']) && $charge > 2) $include['y2Ions'] = 1;
  if(isset($chosen['14']) && !isset($chosen['16'])) $include['ysIons'] = 1;
  if(isset($chosen['14']) && !isset($chosen['16']) && isset($chosen['2']) && $charge > 1) $include['ys2Ions'] = 1;
  if(isset($chosen['14']) && !isset($chosen['16']) && isset($chosen['3']) && $charge > 2) $include['ys2Ions'] = 1;
  if(isset($chosen['15'])) $include['y0Ions'] = 1;
  if(isset($chosen['15']) && isset($chosen['2']) && $charge > 1) $include['y02Ions'] = 1;
  if(isset($chosen['15']) && isset($chosen['3']) && $charge > 2) $include['y02Ions'] = 1;
  if(isset($chosen['16'])) $include['zIons'] = 1;
  if(isset($chosen['16']) && isset($chosen['2']) && $charge > 1) $include['z2Ions'] = 1;
  if(isset($chosen['16']) && isset($chosen['3']) && $charge > 2) $include['z2Ions'] = 1;
  if(isset($chosen['17'])) $include['intybIons'] = 1;
  if(isset($chosen['18'])) $include['intyaIons'] = 1;
}

/*****************************************************************************
 &calcIons()
 no parameters
 globals:
 my(@temp_masses, @runningSum, %masses, @residues, @summer, %vmMass,
   $numRes, @aIons, %parameters, %labelList, @asIons, @a2Ions, @bIons,
   @bsIons, @b2Ions, @yIons, @ysIons, @y2Ions, $numCalcVals, );
******************************************************************************/

function calcIons(){
  global $temp_masses, $runningSum, $masses, $residues, $summer, $vmMass,
         $parameters, $labelList, $queryArr, $numRes, $numCalcVals, $include;
  global $aIons,$a2Ions,$asIons,$as2Ions,$a0Ions,$a02Ions,$bIons,$b2Ions,$bsIons,$bs2Ions,$b0Ions,$b02Ions,
          $cIons,$c2Ions,$immIons,$intyaIons,$intybIons,$xIons,$x2Ions,$yIons,$y2Ions,$ysIons,$ys2Ions,$y0Ions,
          $y02Ions,$zIons,$z2Ions; 
// calculated masses for selected series are consolidated in array $temp_masses
  $temp_masses[0] = 0;
// and label text goes into array $labelList using calculated mass as key
// array $neutralLossList contains any neutral loss masses for the corresponding elements of array $runningSum
// calculate the running sum of the residue masses, including any variable mods
  $runningSum[0] = $masses["$residues[0]"];
  $temp = substr($summer[8],1,1);
  if(preg_match('/[1-9A-FX]/', $temp)){
    $runningSum[0] += $vmMass[$temp];
    $neutralLossList[0] = $neutralLoss[$temp];
  }elseif(isset($neutralLoss[$residues[0]]) && $neutralLoss[$residues[0]]){
    $neutralLossList[0] = $neutralLoss[$residues[0]];
  }else{
    $neutralLossList[0] = 0;
  }
  for ($i=1; $i<$numRes; $i++){
    $runningSum[$i] = $runningSum[$i-1] + $masses[$residues[$i]];
    $temp = substr($summer[8], $i+1, 1);
    if(preg_match('/[1-9A-FX]/', $temp)){
      $runningSum[$i] += $vmMass[$temp];
      $neutralLossList[$i] = $neutralLossList[$i-1] + $neutralLoss[$temp];
    }elseif(isset($neutralLoss[$residues[$i]]) && $neutralLoss[$residues[$i]]){
      $neutralLossList[$i] = $neutralLossList[$i-1] + $neutralLoss[$residues[$i]];
    }else{
      $neutralLossList[$i] = $neutralLossList[$i-1] + 0;
    }
  }
  
// If there is a variable mod at either terminus, add it to masses{}
  $temp = substr($summer[8],0,1);
  if(preg_match('/[1-9A-FX]/', $temp)){
    $masses['n_term'] += $vmMass[$temp];
    $neutralLossList_N_Term = $neutralLoss[$temp];
  }elseif(isset($neutralLoss['n_term']) && $neutralLoss['n_term']){
    $neutralLossList_N_Term = $neutralLoss['n_term'];
  }else{
    $neutralLossList_N_Term = 0;
  }  
  $temp = substr($summer[8],-1,1);
  if(preg_match('/[1-9A-FX]/', $temp)){
    $masses['c_term'] += $vmMass[$temp];
    $neutralLossList_C_Term = $neutralLoss[$temp];
  }elseif(isset($neutralLoss['c_term']) && $neutralLoss['c_term']){
    $neutralLossList_C_Term = $neutralLoss['c_term'];
  } else {
    $neutralLossList_C_Term = 0;
  }  

// calculate fragment ion masses for each series
    $CO = $masses['carbon'] + $masses['oxygen'];
    $NH3 = $masses['nitrogen'] + 3*$masses['hydrogen'];
    $H2O = 2*$masses['hydrogen'] + $masses['oxygen'];
//first n-term
  for($i=0; $i<($numRes-1); $i++){
    $j = $i + 1;
    $aIons[$i]=$runningSum[$i] + $masses['n_term'] - $CO - $neutralLossList[$i] - $neutralLossList_N_Term;
    if(isset($include['aIons']) && $include['aIons'] > 0){
      array_push($temp_masses, $aIons[$i]);
      $labelList["$aIons[$i]"] = "a($j)";
    } 
    if(isset($include['a2Ions']) && $include['a2Ions'] >0 ){
      $a2Ions[$i]=($aIons[$i] + $masses['hydrogen'])/2;
      array_push($temp_masses, $a2Ions[$i]);
      $labelList["$a2Ions[$i]"] = "a($j)++";
    }
    if(preg_match('/[RKNQ]/', substr($summer[6],0,$i+1))){
      $asIons[$i] = $aIons[$i] - $NH3; 
      if(isset($include['asIons']) && $include['asIons'] > 0){
        array_push($temp_masses, $asIons[$i]);
        $labelList["$asIons[$i]"] = "a*($j)";
      }  
      if(isset($include['as2Ions']) && $include['as2Ions'] > 0 ){
        $as2Ions[$i]=($asIons[$i] + $masses['hydrogen'])/2;
        array_push($temp_masses, $as2Ions[$i]);
        $labelList["$as2Ions[$i]"] = "a*($j)++";
      }  
    }
    if(preg_match('/[STED]/', substr($summer[6],0,$i+1))){
      $a0Ions[$i] = $aIons[$i] - $H2O; 
      if(isset($include['a0Ions']) && $include['a0Ions'] > 0){
        array_push($temp_masses, $a0Ions[$i]);
        $labelList["$a0Ions[$i]"] = "a0($j)";
      }  
      if(isset($include['a02Ions']) && $include['a02Ions'] > 0){
        $a02Ions[$i]=($a0Ions[$i] + $masses['hydrogen'])/2;
        array_push($temp_masses, $a02Ions[$i]);
        $labelList["$a02Ions[$i]"] = "a0($j)++";
      }  
    }
    $bIons[$i]=$runningSum[$i] + $masses['n_term'] - $neutralLossList[$i] - $neutralLossList_N_Term; 
    if(isset($include['bIons']) && $include['bIons'] > 0){
      array_push($temp_masses, $bIons[$i]);
      $labelList["$bIons[$i]"] = "b($j)";
    }  
    if(isset($include['b2Ions']) && $include['b2Ions'] > 0){
      $b2Ions[$i]=($bIons[$i] + $masses['hydrogen'])/2;
      array_push($temp_masses, $b2Ions[$i]);
      $labelList["$b2Ions[$i]"] = "b($j)++";
    }
    if(preg_match('/[RKNQ]/', substr($summer[6],0,$i+1))){
      $bsIons[$i] = $bIons[$i] - $NH3; 
      if(isset($include['bsIons']) && $include['bsIons'] > 0){
        array_push($temp_masses, $bsIons[$i]);
        $labelList["$bsIons[$i]"] = "b*($j)";
      }  
      if(isset($include['bs2Ions']) && $include['bs2Ions'] > 0){
        $bs2Ions[$i] = ($bsIons[$i] + $masses['hydrogen'])/2;
        array_push($temp_masses, $bs2Ions[$i]);
        $labelList["$bs2Ions[$i]"] = "b*($j)++";
      }  
    }
    if(preg_match('/[STED]/', substr($summer[6],0,$i+1))){
      $b0Ions[$i]=$bIons[$i] - $H2O; 
      if(isset($include['b0Ions']) && $include['b0Ions']>0){
        array_push($temp_masses, $b0Ions[$i]);
        $labelList["$b0Ions[$i]"] = "b0($j)";
      }  
      if(isset($include['b02Ions']) && $include['b02Ions'] > 0){
        $b02Ions[$i]=($b0Ions[$i] + $masses['hydrogen'])/2;
        array_push($temp_masses, $b02Ions[$i]);
        $labelList["$b02Ions[$i]"] = "b0($j)++";
      }  
    }
    $cIons[$i] = $bIons[$i] + $NH3; 
    if(isset($include['cIons']) && $include['cIons'] > 0){
      array_push($temp_masses, $cIons[$i]);
      $labelList["$cIons[$i]"] = "c($j)";
    }  
    if(isset($include['c2Ions']) && $include['c2Ions'] > 0){
      $c2Ions[$i] = ($cIons[$i] + $masses['hydrogen'])/2;
      array_push($temp_masses, $c2Ions[$i]);
      $labelList["$c2Ions[$i]"] = "c($j)++";
    }  
  }
//then c-term
  for($i=0; $i<($numRes-1); $i++){
    $j = $i + 1;
    $yIons[$i] = $runningSum[$numRes-1] - $runningSum[$numRes-2-$i] + $masses['c_term'] + 2*$masses['hydrogen']
      - $neutralLossList[$numRes-1] + $neutralLossList[$numRes-2-$i] - $neutralLossList_C_Term; 
    if(isset($include['yIons']) && $include['yIons'] > 0){
      array_push($temp_masses, $yIons[$i]);
      $labelList["$yIons[$i]"] = "y($j)";
    }  
    if(isset($include['y2Ions']) && $include['y2Ions'] > 0){
      $y2Ions[$i] = ($yIons[$i] + $masses['hydrogen'])/2;
      array_push($temp_masses, $y2Ions[$i]);
      $labelList["$y2Ions[$i]"] = "y($j)++";
    }
    if(preg_match('/[RKNQ]/', substr($summer[6],-$i-1))){
      $ysIons[$i] = $yIons[$i] - $NH3; 
      if(isset($include['ysIons']) && $include['ysIons'] > 0){
        array_push($temp_masses, $ysIons[$i]);
        $labelList["$ysIons[$i]"] = "y*($j)";
      }  
      if(isset($include['ys2Ions']) && $include['ys2Ions'] > 0){
        $ys2Ions[$i]=($ysIons[$i] + $masses['hydrogen'])/2;
        array_push($temp_masses, $ys2Ions[$i]);
        $labelList["$ys2Ions[$i]"] = "y*($j)++";
      }  
    }
    if(preg_match('/[STED]/', substr($summer[6],-$i-1))){
      $y0Ions[$i] = $yIons[$i] - $H2O; 
      if(isset($include['y0Ions']) && $include['y0Ions'] > 0){
        array_push($temp_masses, $y0Ions[$i]);
        $labelList["$y0Ions[$i]"] = "y0($j)";
      }  
      if(isset($include['y02Ions']) && $include['y02Ions'] > 0){
        $y02Ions[$i] = ($y0Ions[$i] + $masses['hydrogen'])/2;
        array_push($temp_masses, $y02Ions[$i]);
        $labelList["$y02Ions[$i]"] = "y0($j)++";
      }  
    }
    $xIons[$i] = $yIons[$i] - 2*$masses['hydrogen'] + $CO; 
    if(isset($include['xIons']) && $include['xIons'] > 0){
      array_push($temp_masses, $xIons[$i]);
      $labelList["$xIons[$i]"] = "x($j)";
    }  
    if(isset($include['x2Ions']) && $include['x2Ions'] > 0){
      $x2Ions[$i]=($xIons[$i] + $masses['hydrogen'])/2;
      array_push($temp_masses, $x2Ions[$i]);
      $labelList["$x2Ions[$i]"] = "x($j)++";
    }  
    $zIons[$i]=$yIons[$i] - $NH3; 
    if(isset($include['zIons']) && $include['zIons'] > 0){
      array_push($temp_masses, $zIons[$i]);
      $labelList["$zIons[$i]"] = "z($j)";
    }  
    if(isset($include['z2Ions']) && $include['z2Ions'] > 0){
      $z2Ions[$i]=($zIons[$i] + $masses['hydrogen'])/2;
      array_push($temp_masses, $z2Ions[$i]);
      $labelList["$z2Ions[$i]"] = "z($j)++";
    }  
  }
  $numCalcVals = count($temp_masses) - 1;
  
  if(isset($include['immIons']) && $include['immIons'] > 0){
    for($i=0; $i < count($residues); $i++){
      $immIons[$i] = $masses["$residues[$i]"] - $CO + $masses['hydrogen'];
      if(isset($neutralLoss[$residues[$i]]) && $neutralLoss[$residues[$i]]) {
        $immIons[$i] -= $neutralLoss[$residues[$i]];
      }
      $temp = substr($summer[8],$i+1,1);
      if(preg_match('/[1-9A-FX]/', $temp)){
        $immIons[$i] += $vmMass[$temp];
        $immIons[$i] -= $neutralLoss[$temp];
      }
      array_push($temp_masses, $immIons[$i]);
      $labelList["$immIons[$i]"] = strtoupper($residues[$i]);
    }
  }

  // internals
  // unlike other series, we may encounter duplicate mass values
  if((isset($include['intyaIons']) && $include['intyaIons'] > 0) || (isset($include['intybIons']) && $include['intybIons'] > 0)){
    for($i=0; $i < count($residues) - 3; $i++){
      for($j=$i+2; $j < count($residues) - 1; $j++){
        array_push($intybIons, $runningSum[$j] - $runningSum[$i]  + $masses['hydrogen'] - $neutralLossList[$j] + $neutralLossList[$i]);
        array_push($intybLabels, substr($summer[6],$i+1,$j-$i));
        $intybIonsLastIndex = count($intybIons) - 1;
        $intybIonsLastValue = $intybIons[$intybIonsLastIndex];
        if($include['intybIons'] > 0 && $intybIonsLastValue < 700){
          array_push($temp_masses, $intybIonsLastValue);
          $labelList["$intybIonsLastValue"] = substr($summer[6],$i+1,$j-$i);
        }
        array_push($intyaIons, $intybIonsLastValue - $CO);
        $intyaIonsLastIndex = count($intyaIons) - 1;
        $intyaIonsLastValue = $intyaIons[$intyaIonsLastIndex];
        if($include['intyaIons'] > 0 && $intyaIonsLastValue < 700){
          array_push($temp_masses, $intyaIonsLastValue);
          $labelList["$intyaIonsLastValue"] = substr($summer[6],$i+1,$j-$i) . "-28";
        }
      }
    }
  }

  // if there are no values for the NH3 and H2O neutral losses, drop the column
  
  if(!isset($asIons) || count($asIons) == 0){
    $include['asIons'] = 0;
  }
  if(!isset($as2Ions) || count($as2Ions) == 0){
    $include['as2Ions'] = 0;
  }
  if(!isset($a0Ions) || count($a0Ions) == 0){
    $include['a0Ions'] = 0;
  }
  if(!isset($a02Ions) || count($a02Ions) == 0){
    $include['a02Ions'] = 0;
  }
  if(!isset($bsIons) || count($bsIons) == 0){
    $include['bsIons'] = 0;
  }
  if(!isset($bs2Ions) || count($bs2Ions) == 0){
    $include['bs2Ions'] = 0;
  }
  if(!isset($b0Ions) || count($b0Ions) == 0){
    $include['b0Ions'] = 0;
  }
  if(!isset($b02Ions) || count($b02Ions) == 0){
    $include['b02Ions'] = 0;
  }
  if(!isset($ysIons) || count($ysIons) == 0){
    $include['ysIons'] = 0;
  }
  if(!isset($ys2Ions) || count($ys2Ions) == 0){
    $include['ys2Ions'] = 0;
  }
  if(!isset($y0Ions) || count($y0Ions) == 0){
    $include['y0Ions'] = 0;
  }
  if(!isset($y02Ions) || count($y02Ions) == 0){
    $include['y02Ions'] = 0;
  }
  return 1;
}

/******************************************************************************
 &tolVal()
 $_[0] tolerance to be converted
 globals:
 my(%parameters);
 returns tolerance in Da
*******************************************************************************/

function tolVal($tolerance){
  global $parameters;
  $scaleMass = $tolerance;
  //echo "<br>".$parameters['itolu'];exit;
  if($parameters['itolu'] == "%"){
    return $scaleMass * $parameters['itol'] / 100;
  }elseif($parameters['itolu'] == "Da"){
    return $parameters['itol'];
  }elseif($parameters['itolu'] == "ppm"){
    return $scaleMass * $parameters['itol'] / 1000000;
  }elseif($parameters['itolu'] == "mmu"){
    return $parameters['itol'] / 1000;
  } else {
    fatalError("Unrecognised fragment mass tolerance unit", __LINE__);
  }
}

/******************************************************************************
 findMatches()
 no parameters
 globals:
 my (@massList, @temp_masses, @intensityList, %query,
   @summer, @typeList, @exp_masses, %labelList, %labels);
*******************************************************************************/

function cmp($a, $b){
  if($a == $b)  return 0;
  return ($a < $b) ? -1 : 1;
}

function cmp2($a, $b){
  global $int_mass;
  if($int_mass[$a] == $int_mass[$b]){
    if($a == $b)  return 0;
    return ($a < $b) ? -1 : 1;
  }
  return ($int_mass[$b] < $int_mass[$a]) ? -1 : 1;
}

function findMatches(){
  global $matchList, $debug, $peaks,  $numRes, $ignoreMass, $delta_masses; 
  global $massList, $temp_masses, $intensityList, $queryArr, $summer, $typeList, $exp_masses, $labelList, $labels;

  $matched_calc = array();
  $matched_exp = array();
  $matched_int = array();  
  
//return if no mass data (e.g. from sequence query)
  if(!$massList[0]){
    $matchList = "";
    return 0;
  }

//sort calc vals by mass
  $calc_masses = $temp_masses;
  usort($calc_masses, "cmp");
  

  if($debug == 'FALSE'){
  //make a copy of the full experimental mass list, sorted by mass
  //and put the corresponding intensities in a hash
    for ($i=0; $i<count($massList); $i++){
      $int_mass[$massList[$i]]= $intensityList[$i];
    }
    $fullMassList = $massList;
    usort($fullMassList, "cmp");
  }
  
  if(isset($queryArr['num_used1']) && $queryArr['num_used1']){
    //new scoring scheme; matches are the first num_used values listed
    if($queryArr['num_used1'] == -1){
      $queryArr['num_used1'] = $summer[7];
      $queryArr['num_used2'] = $summer[12];
      $queryArr['num_used3'] = $summer[13];
    }
    $massList = array();
    $intensityList = array();
    $typeList = array();
  
    
    for($j=1; $j<=3; $j++){
      if(isset($queryArr["ions"."$j"]) && $queryArr["ions"."$j"]){
        $tmpString = $queryArr["ions"."$j"];
        if(preg_match('/^([by])-/i', $tmpString, $matches)){
          preg_replace('/^([by])-/i', '', $tmpString);
        }
        if(isset($matches[1]) && $matches[1]){
          $type = $matches[1];
        }else{
          $type = "";
        }
        $mass = explode(',', $tmpString);

        for($i=0; $i < $queryArr["num_used"."$j"]; $i++){
          list($tmpLeft,$tmpRight) = explode(':',$mass[$i]);
          array_push($massList, $tmpLeft);
          if (isset($tmpRight) && $tmpRight){
            array_push($intensityList, $tmpRight);
          } else {
            array_push($intensityList, 0);
          }
          array_push($typeList, $type);
        }
      }
    }
    
    if ($debug == 'FALSE' && count($massList) > 0){
    //add experimental peaks to the list of potential matches if they are of greater
    //intensity than the smaller adjacent matched peak and not on the ignore list
      $mass = $massList;
      usort($mass, "cmp");
      array_unshift($mass, 0);
      array_push($mass, 999999);
      
      $int_mass[$mass[0]] = $int_mass[$mass[1]];
      $tmplastIndex = count($mass)-1;      
      $int_mass[$mass[$tmplastIndex]] = $int_mass[$mass[$tmplastIndex-1]];
      $j = 0;
      for($i=1; $i<count($mass); $i++){
        $intThresh = $int_mass[$mass[$i]];
        if($int_mass[$mass[$i-1]] < $int_mass[$mass[$i]]){
          $intThresh = $int_mass[$mass[$i-1]];
        }
        while(isset($fullMassList[$j]) && $fullMassList[$j] && $fullMassList[$j] < $mass[$i]){
          if($int_mass[$fullMassList[$j]] >= $intThresh){
            $ignoreMe = 0;
            $this_tol = tolVal($fullMassList[$j]);            
            for ($k=0; $k<count($ignoreMass); $k++){
              if(abs($fullMassList[$j] - $ignoreMass[$k]) <=  $this_tol){
                $ignoreMe = 1;
                break;
              }
            }
            if(!$ignoreMe) {
              array_push($massList, $fullMassList[$j]);
              array_push($intensityList, $int_mass[$fullMassList[$j]]);
              array_push($typeList, "");
            }
          }
          $j++;
        }
        $j++;
      }
    }
    $peaks = count($massList);

    for ($i=1; $i<count($calc_masses); $i++){
      $matchCount[$i] = -1;
    }
    for ($j=0; $j<count($massList); $j++){
      $this_tol=&tolVal($massList[$j]);
      
      for ($i=1; $i<count($calc_masses); $i++){
        if(abs($massList[$j]-$calc_masses[$i]) <=  $this_tol){
          if($matchCount[$i] > -1){
            if($matched_int[$matchCount[$i]] < $intensityList[$j]){
              $matched_exp[$matchCount[$i]] = $massList[$j];
              $matched_int[$matchCount[$i]] = $intensityList[$j];
            }
            continue;
          }
          array_push($matched_calc, $calc_masses[$i]);
          array_push($matched_exp, $massList[$j]);
          array_push($matched_int, $intensityList[$j]);
          $matchCount[$i] = count($matched_int) - 1;
        }
      }
    }

    $exp_masses = $matched_exp;
    usort($exp_masses, "cmp");    
    $calc_masses = $matched_calc;
    usort($calc_masses, "cmp");

    //create error list to be passed to mass_error.pl
    for ($i=0; $i<count($exp_masses); $i++){
      $delta_masses[$i] = $exp_masses[$i] - $calc_masses[$i];
    }

    //concatenate string  of "$label, exp_mass, ..." to be passed to msms_gif.pl
    //and select %labels{calc_mass} from %labelList for highlighting the printed table
    $matchList="";
    $i = 0;
    while(isset($calc_masses[$i]) && $calc_masses[$i]){
    //commented out lines suppress matching to complete peptide
    // if ($labelList{$calc_masses[$i]} =~ /\($numRes\)/) {
    //   splice @exp_masses, $i, 1;
    //   splice @calc_masses, $i, 1;
    // } else {
    
        $matchList .= $labelList["$calc_masses[$i]"].",".sprintf("%.2f",$exp_masses[$i]).",";
        $labels["$calc_masses[$i]"] = $labelList["$calc_masses[$i]"];
        $i++;
    // }
    }
    if($matchList){
      $len = strlen($matchList);
      $matchList = substr($matchList, 0, $len-1);
    }
    return 1;
  } 
    
//peak matching for old scoring scheme
//sort exp vals by descending intensity (problem if any exact duplicate masses)
  for ($i=0; $i<count($massList); $i++){
    $int_mass[$massList[$i]] = $intensityList[$i];
  }
  usort($calc_masses, "cmp2");
    
  $matched_masses[0] = 0;
  for ($i=0; $i<$peaks; $i++){
    $this_tol = tolVal($exp_masses[$i]);
    $j=1;
    $matches_by_exp[$exp_masses[$i]] = array();
    while($calc_masses[$j]){
      if(abs($exp_masses[$i]-$calc_masses[$j]) <=  $this_tol){
        array_push($matches_by_exp[$exp_masses[$i]], $calc_masses[$j]);
        $matches_by_calc[$calc_masses[$j]] = $exp_masses[$i];
        array_push($matched_masses, $calc_masses[$j]);
        array_splice($calc_masses, $j, 1);
      }else{
        $j++;
      }
    }
    if(!$matches_by_exp[$exp_masses[$i]] || !is_array($matches_by_exp[$exp_masses[$i]])){
      for ($k=1; $k<count($matched_masses); $k++){
        if(abs($exp_masses[$i]-$matched_masses[$k]) <=  $this_tol){
          $other_exp_mass = $matches_by_calc[$matched_masses[$k]];
          if(isset($matches_by_exp[$other_exp_mass][1]) && $matches_by_exp[$other_exp_mass][1]){
            array_push($matches_by_exp[$exp_masses[$i]], $matched_masses[$k]);
            for ($m=0; $m<count($matches_by_exp[$other_exp_mass]); $m++){
              if($matches_by_exp[$other_exp_mass][$m] == $matched_masses[$k]){
                array_splice($matches_by_exp[$other_exp_mass], $m, 1);
                break;
              } 
            }
            $matches_by_calc[$matched_masses[$k]] = $exp_masses[$i];
            break;
          }
        }
      }
    }
  }
    
//sort match list to eliminate any crossed matches
  $calc_masses = '';
  $exp_masses = '';
  $temp_masses = '';
  $intensityList = '';
  
  foreach($matches_by_exp as $matchKey => $matchValue){
    array_push($temp_masses, $matchKey);
    array_push($intensityList, $matchValue[0]);
  }
  $exp_masses = $temp_masses;
  usort($exp_masses, "cmp");
  $calc_masses = $intensityList;
  usort($calc_masses, "cmp");

  //create error list to be passed to mass_error.pl
  for ($i=0; $i<count($exp_masses); $i++){
    $delta_masses[$i] = $exp_masses[$i] - $calc_masses[$i];
  }

//concatenate string  of "$label, exp_mass, ..." to be passed to msms_gif.pl
//and select %labels{calc_mass} from %labelList for highlighting the printed table
  $matchList = "";
  for ($i=0; $i<count($exp_masses); $i++){
    $matchList .= $labelList["$calc_masses[$i]"].",".sprintf("%.2f",$exp_masses[$i]).",";
    $labels["$calc_masses[$i]"] = $labelList["$calc_masses[$i]"];
  }
  if($matchList){
    $len = strlen($matchList);
    $matchList = substr($matchList, 0, $len-1);
  }
  return 1;
}

/***************************************************************************
 &unBlock()
 $_[0] $blockName
 $_[1] \%query
 $_[2] \@massList
 $_[3] \@intensityList
 $_[4] \@typeList
 globals:
 my(%index, @fields, $boundary);
 can call with first 2 arguments only or all 5
 nb labels are all set to lower case
****************************************************************************/
function unBlock(&$blockArr, &$massList='', &$intensityList='', &$typeList=''){
  if($typeList !== ''){
    for($j = 1; $j <= 3; $j++){ 
      if(isset($blockArr["ions".$j]) && $blockArr["ions".$j]){
      //if(defined(${$_[1]}{"ions"."$j"}) && ${$_[1]}{"ions"."$j"} gt ""){
        $tmpString = $blockArr["ions".$j];
        if(preg_match('/^([by])-/i', $tmpString, $matches)){
          preg_replace('/^([by])-/i', '', $tmpString);
        }
        if(isset($matches[1]) && $matches[1]){
          $type = $matches[1];
        }else{
          $type = "";
        }
        $mass = explode(',', $tmpString);
        foreach($mass as $value){
          if(!$value) continue;
          list($tmpLeft,$tmpRight) = explode(':', $value, 2);
          array_push($massList, $tmpLeft);
          if(isset($tmpRight) && $tmpRight){
            array_push($intensityList, $tmpRight);
          }else{
            array_push($intensityList, '0');
          }
          array_push($typeList, $type);
        }
      }
    }
  }
  return 1;
}

//----------------------------------------------
function fatalError($msg='', $line=0){
//----------------------------------------------
  global $start_time;
  $msg  = "Fatal Error--$msg;";
  $msg .=  " Script Name: " . $_SERVER['PHP_SELF']. ";";
  $msg .= " Start time: ". $start_time . ";";
  if($line){
    $msg .= " Line number: $line;";
  }
  echo $msg;
  //writeLog($msg);
  exit;
}

/*****************************************************************************
 &noTag()
 $_[0] string which may contain HTML tags
 returns de-tagged string
*****************************************************************************/

function noTag($htmlStr){
  $pattens = array('/</', '/>/');
  $replacement = array('&lt;','&gt;');
  $temp = preg_replace($pattens, $replacement, $htmlStr);
  return $temp;
}

function noDoubleQoute($str){
  $temp = preg_replace('/"/', '', $str);
  return $temp;
}

function in_one_query($queryNum){
  global $peptides;
  $oneQuery = array();
  foreach($peptides as $key => $value){
    $tmpValue = trim($value);
    if(preg_match('/^q'.$queryNum.'_p(\d+)$/', $key, $matches) && ($tmpValue != -1 || $tmpValue != "")){
      $tmpArr = explode(',', $tmpValue);
      $tmpArr2['hit'] = $matches[1];
      $tmpArr2['score'] = round($tmpArr[7],1);
      $tmpArr2['mr'] = round($tmpArr[1],2);
      $tmpArr2['delta'] = round($tmpArr[2],2);
      $tmpArr2['sequence'] = $tmpArr[4];
      array_push($oneQuery, $tmpArr2);
    }
  }
  return $oneQuery;
}

function display_gif(){
  global $matchList, $firstTick, $lastTick, $overallHeight, $overallWidth, $peptide, $fileIn;
  global $queryNum, $hitNum, $tickInterval, $displayRange, $accession, $px;
  
  if($argString = $matchList){
    //-----$argString = preg_replace('/([& +])/', sprintf("%%%02x", ord(\1)), $argString);
  } else {
    $argString = "";
  }
  echo "<FORM METHOD=\"GET\" ENCTYPE=\"application/x-www-form-urlencoded\"";
  echo " ACTION=\"peptide_view.php\">\n";
  echo "Click mouse within plot area to zoom in by factor of two about that point<BR>\n";
  echo "Or,&nbsp;<INPUT TYPE=\"submit\" NAME=\"zoomOut\" VALUE=\"Plot from\" >&nbsp;";
  echo "<INPUT TYPE=\"text\" SIZE=8 NAME=\"from\" VALUE=\"$firstTick\">&nbsp;to&nbsp;";
  echo "<INPUT TYPE=\"text\" SIZE=8 NAME=\"to\" VALUE=\"$lastTick\">&nbsp;Da";
  echo "<P>\n<INPUT TYPE=\"image\" NAME=\"gif\" HEIGHT=$overallHeight WIDTH=$overallWidth";
  echo " ALT=\"MS/MS spectrum of $peptide\" BORDER=2\n";
  echo "SRC=\"./msms_gif.php?tick1=$firstTick&tick_int=$tickInterval&range=$displayRange&matches=$argString\">\n";
  echo "<INPUT TYPE=\"hidden\" NAME=\"tick1\" VALUE=$firstTick>\n";
  echo "<INPUT TYPE=\"hidden\" NAME=\"tick_int\" VALUE=$tickInterval>\n";
  echo "<INPUT TYPE=\"hidden\" NAME=\"range\" VALUE=$displayRange>\n";
  if($px){
    echo "<INPUT TYPE=\"hidden\" NAME=\"index\" VALUE=\"$accession\">\n";
    echo "<INPUT TYPE=\"hidden\" NAME=\"px\" VALUE=$px>\n";
  }
  echo "</FORM>\n";
}
?>
