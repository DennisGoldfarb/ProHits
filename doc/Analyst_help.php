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
?>
<HTML>
<HEAD>
<link rel="stylesheet" type="text/css" href="../analyst/site_style.css">
<link rel="stylesheet" type="text/css" href="../msManager/ms_style.css">
</HEAD>
<BODY bgcolor=white text=black link=blue vlink=purple alink=fushia >
<!--table border='1' cellpadding='0' cellspacing='0' width='800'>
<TR><TD ALIGN='center'-->
<DIV STYLE='display: block;border: #6a5acd solid 1px;width: 770px; TEXT-ALIGN: center'>
<DIV  STYLE='display: block;border: white solid 1px;width: 720px; TEXT-ALIGN: left'>
<DIV class="Part">
<pre>
<table border='0' cellpadding='0' cellspacing='0' width='95%'>
  <tr>
    <td><br><font face='Arial Black' size='+2' color='#055698'>
    <b><div align='center'><img src=../msManager/images/prohits_logo.gif border=0 align=middle> &nbsp; ProHits Analyst User Manual </div></b>
    </font><br></td>
  </tr>
  <tr height='1'>
    <td bgcolor='#006699' height='1'>
       <img src='./images/pixel.gif' width='1' height='1' border='0'></td>
  </tr>
  <tr height='1'>
    <td height='1'>&nbsp;<BR>
       <FONT size=+1 face=arial>Contents</FONT>
    </td>
  </tr>
  <tr height='1'>
  <td><BR>
<ul>
</pre><li><a href="#faq1" class=help>Overview</a>
<ul>
<li><a href="#faq2" class=help>Access to projects</a>
<li><a href="#faq3" class=help>Analyst main page</a>
<ul>
<li><a href="#faq4" class=help>Description of the navigator bar options</a>
</ul>
</ul>
<li><a href="#faq5" class=help>Creating samples and viewing individual reports</a>
<li><a href="#faq6" class=help>Adding a &ldquo;Gel-Free&rdquo; sample</a>
<ul>
<li><a href="#faq7" class=help>Creating a bait</a>
<li><a href="#faq8" class=help>Creating an experiment</a>
<ul>
<li><a href="#faq9" class=help>User-defined free-text protocols</a>
<li><a href="#faq10" class=help>Controlled vocabulary</a>
<li><a href="#faq11" class=help>Additional annotation</a>
</ul>
<li><a href="#faq12" class=help>Creating a new sample</a>
<ul>
<li><a href="#faq13" class=help>Linking raw files from the Data Management module</a>
<li><a href="#faq14" class=help>Linking raw files directly from the Analyst module (alternative)</a>
</ul>
<li><a href="#faq15" class=help>Navigating through the results</a>
<ul>
<li><a href="#faq16" class=help>Search results</a>
<li><a href="#faq17" class=help>Sorting options</a>
<li><a href="#faq18" class=help>Links details</a>
<li><a href="#faq19" class=help>Option details</a>
<li><a href="#faq20" class=help>Using filters</a>
<li><a href="#faq21" class=help>Comparing your data to literature interactions</a>
<li><a href="#faq22" class=help>View and navigate hits from the TransProteomics Pipeline</a>
<li><a href="#faq23" class=help>Viewing results using Cytoscape</a>
<li><a href="#faq24" class=help>Export Sample report</a>
<li><a href="#faq25" class=help>Using the Notes option</a>
</ul>
</ul>
<li><a href="#faq26" class=help>Creating gel-based samples</a>
<ul>
<li><a href="#faq27" class=help>Adding a &ldquo;Gel-based&rdquo; sample</a>
</ul>
<li><a href="#faq28" class=help>The Comparison tool</a>
<ul>
<li><a href="#faq29" class=help>Merging files prior to Comparison</a>
<li><a href="#faq30" class=help>Comparison page</a>
<ul>
<li><a href="#faq31" class=help>Using Cytoscape directly from ProHits comparison</a>
<li><a href="#faq32" class=help>Other export options</a>
<li><a href="#faq33" class=help>Comparing larger numbers of baits</a>
</ul>
<li><a href="#faq34" class=help>Automatically adding baits for comparison from the baits or sample report list pages</a>
<li><a href="#faq35" class=help>Simple Search (Gene name)</a>
<li><a href="#faq50" class=help>Advanced Search (Gene name)</a>
<ul>
<li><a href="#faq36" class=help>Other keywords that can be searched</a>
</ul>
</ul>
<li><a href="#faq37" class=help>Manage Protocols and Lists</a>
<ul>
<li><a href="#faq38" class=help>Text-based protocols</a>
<li><a href="#faq39" class=help>Experimental Editor</a>
<li><a href="#faq40" class=help>Background Lists</a>
<li><a href="#faq41" class=help>Group Lists</a>
<ul>
<li><a href="#faq42" class=help>Export version</a>
</ul>
<li><a href="#faq43" class=help>Epitope Tag Lists</a>
<pre>
                    </ul>   
                  </td>
                </tr>
              </table></pre></P>
</DIV>
<DIV class="Part">
<H3 align="justify">
<FONT size=2 face=arial>
<B><a name="faq1"></a><b>Overview&nbsp;</b></H3>
<P>
<FONT size=2 face=arial>
</B>
The Analyst module allows you to visualise, analyze, compare, search and export your MS results. </P>
<P align="">
<FONT size=2 color="#0000CC" face=arial>specify project, provide analyze results bait, experimental compare <U>species </U>
details <U>export </U>
</P>
<IMG width="451" height="283" src="images/Analyst_help_img_1.jpg" >
<P>
<FONT size=2 color="#000000" face=arial>In order to analyze and compare data, each MS file in the MS data management module must be linked to a sample created in the Analyst module. For example, to create a sample for a gel-free experiment, you must first specify a project, create an entry for the protein of interest (bait), and define experimental conditions. Typical gel-free samples are eluates from an affinity purification. </P>
<IMG width="520" height="233" src="images/Analyst_help_img_2.jpg" >
<P>Sample entry for gel-based projects is similar, with the exception that a gel is specified prior to the selection of a bait. Typically, samples are gel bands, and all bands from the same lane are entered under the same &ldquo;Experiment&rdquo;. </P>
<IMG width="527" height="301" src="images/Analyst_help_img_3.jpg" >
<P>
<B><a name="faq2"></a><b>Access to projects&nbsp;</b></P>
<P>
</B>
Projects are created by your administrator in the &ldquo;Admin Office&rdquo; ProHits module, and access is granted to users.  Projects can be specific to a research group or an individual, to a given organism or specific methodology, etc.  The creation of a new project is defined in the &ldquo;Admin Office&rdquo; manual. </P>
<P>When you log into ProHits with your user name, you can see the list of all of the projects that you have access to. You may have different privileges for each project. </P>
<P>
<FONT color="#003163" face=arial><b>&rArr; Highlight the desired project, then hit &ldquo;Select&rdquo; </b>
</P>
<IMG align="" width="612" height="279" src="images/Analyst_help_img_4.jpg" >
<P align="justify">
</B>
<P>
<FONT color="#000000" face=arial><a name="faq3"></a><b>Analyst main page&nbsp;</b></P>
When you enter a project within the Analyst module, you will see the data workflow and a summary of the icons used in this module.  The navigator bar on the left lists various visualization and analysis options. </P>
<IMG align="" width="624" height="433" src="images/Analyst_help_img_5.jpg" >
<P>
<FONT size=2 face=arial>
<I><a name="faq4"></a><b>Description of the navigator bar options&nbsp;</b></I>
</P>




<!--modify-->

<TABLE align="center" border=0 cellspacing=0 cellpadding=2 width="100%">
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>1-</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr><b>Create New Entry</b> allows you to define a bait, experiment, sample, and to link mass spectrometry
 data to this entry. These entries can then be linked to specific files in the MS Data Management
 module. Alternatively, you can upload search results created by external software. </TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="15"><div class=maintext_extra_wr>2-</TD>
<TD align="left" valign="top" height="15"><div class=maintext_extra_wr><b>Individual Reports</b> allows you to explore your mass spectrometry results.<I> Report by Bait</I>
: providesa list of all baits entered in the database for this project. <I>Report by Samples</I>
: lists all samples entered for this project (a bait may be linked to multiple samples, especially in gel-based projects; 
we also use this nomenclature for technical replicates). <I>Report by Plate</I>
: sample tracking for high-
throughput projects, typically gel-based. <I>Report by Gel</I>
: allows you to visualize results for each gel 
(gel-based projects only). </TD>
</TR>
<TR>
<TD align="right" valign="top" height="16"><div class=maintext_extra_wr>3-</TD>
<TD align="left" valign="top" height="16"><div class=maintext_extra_wr><b>Multiple Sample Analysis</b> (Comparison): allows you to simultaneously visualize multiple result pages. 
</TD>
</TR>
<TR>
<TD align="right" valign="top" height="15"><div class=maintext_extra_wr>4-</TD>
<TD align="left" valign="top" height="15"><div class=maintext_extra_wr><b>Manage Protocols and Lists</b> allows you to create and maintain experimental protocols, controlled 
vocabularies, background lists, group lists and epitope tag lists. Access to these pages is defined by the ProHits Administrator. 
</TD>
</TR>
<TR>
<TD align="right" valign="middle" height="16"><div class=maintext_extra_wr>5-</TD>
<TD align="left"  valign="middle" height="16"><div class=maintext_extra_wr><b>Other Tools</b> provides additional functionality. <I>Co</I>
<I>-</I>
<I>IP Report</I>
: allows you to input results from follow-up experiments aimed at confirming interaction pairs by immunoprecipitation/immunoblotting. 
<I>Export Functions</I>
: allows you to export filtered or unfiltered lists of mass spectrometry results. Note 
that export functions are also available within each of the Individual Report or Comparison pages. 
</TD>
</TR>
<TR>
<TD align="right"  valign="top" height="15"><div class=maintext_extra_wr>6-</TD>
<TD align="left" valign="top" height="15"><div class=maintext_extra_wr><b>Advanced search</b> allows you to query your project for genes, keywords and/or controlled 
vocabularies. 
</TD>
</TR>
</TABLE>
<!--modify-->








<DIV class="Sect">
<H5>
<FONT size=2 face=arial>
<B><a name="faq5"></a><b>Creating samples and viewing individual reports&nbsp;</b></H5>
<P>
<FONT size=2 face=arial>
</B>
To learn more about the different functions of ProHits Analyst, we will navigate through the Analyst module by creating new baits and linking them to entries from the Data management system.  We will then explore the functions available in the Analyst module.  We will go through the process of adding a gel-free sample and explore the results for this type of project. We will then briefly review the differences between submitting gel-free and gel-based samples. </P>
</DIV>
<DIV class="Sect">
<H5>
<FONT size=2 face=arial>
<B><a name="faq6"></a><b>Adding a &ldquo;Gel-Free&rdquo; sample&nbsp;</b></H5>
<P>
<FONT size=2 face=arial>
</B>
To create a new sample to be linked to a search result file, you will first specify a bait, then an experiment, and then a sample.  To submit a sample, you have two options: 1) create a new sample from an existing bait; or 2) create a new bait. Here we will start by creating 5 new baits for this project. </P>
<P>
<B><a name="faq7"></a><b>Creating a bait&nbsp;</b></P>
<P>
<FONT color="#003163" face=arial>
</B><b>
&rArr;<B> Select the &ldquo;Add Gel-free Sample&rdquo; link under &ldquo;Create New Entry&rdquo;. Select &ldquo;new bait&rdquo; from the dropdown menu, then click on the &ldquo;Bait&rdquo; Blue arrow.   </b></P>
<IMG align="" width="600" height="284" src="images/Analyst_help_img_6.jpg" >
<P>
<FONT color="#000000" face=arial>
</B>
This will open a new page.  Note at the top of the page the data structure; the Bait is highlighted, indicating that you are adding bait level entries.  Note that each of the baits is automatically assigned a unique numeric identifier. The fields highlighted in bold indicate that the information is mandatory, but many of these can be filled automatically. </P>
<P>The easiest way to enter a new bait is to simply 1) select the desired species (here we have selected <I>Homo sapiens</I>
); 2) enter an official Gene Name (HUGO for human; here we selected MEPCE); 3) click the &ldquo;Get Protein Info&rdquo; green button. Clicking &ldquo;Get Protein Info&rdquo; automatically retrieves the protein information which is displayed in a new window.  Verify this information and hit [Pass Value] if correct &ndash; the information will automatically be transferred.  <FONT color="#800000" face=arial>Note that if there is more than one entry mapped to a given gene, the user can select which one is to be parsed into ProHits. </P>
<P>
<FONT color="#000000" face=arial>ProHits also allows you to indicate which epitope-tag you are using, by selecting from options in the &ldquo;Epitope Tag&rdquo; menu; you can also add new tags using the &ldquo;Manage Protocols and Lists&rdquo; option.  If the sequence of the bait is mutated relative to the HUGO sequence, you can also enter this in the &ldquo;Bait mutation&rdquo; box. </P>
<IMG width="393" height="523" src="images/Analyst_help_img_7.jpg" >
<P>
<FONT color="#003163" face=arial><b>&rArr;<B> Press [Save] to complete bait entry </b></P>
<P>
<FONT color="#000000" face=arial>
</B>
After saving, you still have the option to modify the information (a new window appears with two options at the bottom, &ldquo;Modify&rdquo; and &ldquo;Next&rdquo;). You can add additional information, e.g. in the &ldquo;Description&rdquo; field, or modify existing information. Hitting [Next] would bring up the Experimental detail page (for this demonstration, we will not do this yet). </P>
<P>
<FONT color="#800000" face=arial>Also note that you can create baits for sequences that are not in the database by manually filling in all bold fields (species, gene name, locus tag, protein ID, protein ID type).  ProHits does not check for accuracy in these entries.  You may wish to use this option, for example, for recombinant or chimeric proteins not corresponding to any of the entries in the database. </P>

<FONT color="#003163" face=arial>
<B>&rArr; Use the [Add New Bait] button at the top of the page, and continue defining baits in the same manner as for MEPCE<FONT color="#000000" face=arial>
</B>
. <FONT color="#800000" face=arial>Note that in the bait entry page, you can also define an experiment in which no gene/protein was tagged.  To do so, simply select the &ldquo;No gene (control) or non IP experiment&rdquo; button at the top of the page, and manually enter information.  Here we are adding a &ldquo;FLAG alone&rdquo; bait. </P>
<IMG align="" width="491" height="195" src="images/Analyst_help_img_8.jpg" ><br><br>


<FONT color="#003163" face=arial>
<B>&rArr; To visualize the entry of your new baits in the database, go back to the left bar menu and select [Report by Bait] </P>


<IMG align="" width="648" height="284" src="images/Analyst_help_img_9.jpg" >
<P>
<FONT color="#000000" face=arial>
</B>
The Bait Report now lists the baits we have created (MEPCE, FLAG alone, and 3 additional baits that we will use for the demonstration of the functions of ProHits), along with some relevant information. The &ldquo;ID&rdquo; column lists a unique identifier for the bait that is automatically assigned by ProHits.  The Gene Name and Tag are indicated, and the Protein ID is the accession number from the selected database (e.g. NCBI-GI). The &ldquo;User&rdquo; column is automatically assigned to the user who created the sample (i.e. the person who has signed up in ProHits). </P>
<P>
<FONT color="#612221" face=arial>Note that, on many of the ProHits pages, you will find standard icons (as seen at the top of the Bait Report page). </P>
<IMG width="360" height="23" src="images/Analyst_help_img_10.jpg" >






<TABLE align="center" border=0 cellspacing=0 cellpadding=2 width="100%">
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>1-</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>To remove unused material, press the &ldquo;delete&rdquo; icon. The &ldquo;delete&rdquo; function may be used to remove 
baits, experiments or samples, but <I>only if no information has been entered</I>
. If you wish to delete a bait, experiment or sample for which information has been entered, start by deleting the information 
at a lower level, and work your way up.  (<FONT color="#612221" face=arial>Note that there is an Admin control for the permissions to 
insert, modify and delete entries, and you can only delete your own entries<FONT color="#000000" face=arial>). </TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="15"><div class=maintext_extra_wr>2-</TD>
<TD align="left" valign="top" height="15"><div class=maintext_extra_wr>The next level (tree) icon allows you to navigate down in the data structure (i.e. from bait to experiment to sample).</TD>
</TR>
<TR>
<TD align="right" valign="top" height="16"><div class=maintext_extra_wr>3-</TD>
<TD align="left" valign="top" height="16"><div class=maintext_extra_wr>The Modify icon allows you to change the information you entered for a bait, experiment or sample.
</TD>
</TR>
<TR>
<TD align="right" valign="top" height="15"><div class=maintext_extra_wr>4-</TD>
<TD align="left" valign="top" height="15"><div class=maintext_extra_wr>The green arrow (Next) icon allows you to submit information and/or exit a page after data has been entered.
</TD>
</TR>
<TR>
<TD align="right" valign="middle" height="16"><div class=maintext_extra_wr>5-</TD>
<TD align="left"  valign="middle" height="16"><div class=maintext_extra_wr>The &ldquo;Bait report&rdquo; (graph) icon shows you the mass spectrometry results for the selected bait. We will review this in detail later. 
</TD>
</TR>
<TR>
<TD align="right"  valign="top" height="15"><div class=maintext_extra_wr>6-</TD>
<TD align="left" valign="top" height="15"><div class=maintext_extra_wr>Finally, the &ldquo;Bait Notes&rdquo; (callout) icon allows you to enter specific notes/information for baits or samples.  Such notes can be a manually entered discussion point.  Other types of notes include assignment of a project to a user-defined &ldquo;bait group&rdquo;. 
</TD>
</TR>
</TABLE>
<!--modify-->

<br>Now that you have created baits, you are ready to define your experiments.  <FONT color="#612221" face=arial>Note that in many cases, you will be seamlessly going from bait to experiment to sample when entering real samples.  Here, we have simply separated these modules for ease of teaching. 

<br><br><FONT color="#000000" face=arial>
<B><a name="faq8"></a><b>Creating an experiment&nbsp;</b></P>
<P>
<FONT color="#003163" face=arial>
</B><b>
&rArr;<B> Return to [Add Gel-free sample], and select [start from existing bait]. </b></P>
<P>
<FONT color="#000000" face=arial>
</B>
This will bring up essentially the same page as shown above, but with an additional option (green arrow) at the extreme right of each row. </P>
<P>
<FONT color="#003163" face=arial><b>&rArr;<B> Select this green arrow to enter the experimental details for a given bait </b></P>
<P>
<FONT color="#000000" face=arial>
</B>
The experimental detail page allows you to specify experimental conditions and protocols used for the experiment. The top of the page states the bait information: below, the definition of an experiment can be separated into three sections. </P>
<IMG align="" width="636" height="448" src="images/Analyst_help_img_11.jpg" >
<P>
<FONT size=2 face=arial>
<I><a name="faq9"></a><b>User-defined free-text protocols&nbsp;</b></I>
</P>
<P>
<FONT size=2 face=arial>In section 1, drop-down menus allow for the selection of user-specified protocols for each experiment. We suggest describing generic protocols in detail (in a manner similar to the Methods section of an article).  The protocols can be entered and managed using the &ldquo;Manage Protocols and Lists&rdquo; option (more on this later).  </P>
<P>
<FONT size=2 face=arial>
<I><a name="faq10"></a><b>Controlled vocabulary&nbsp;</b></I>
</P>
<P>
<FONT size=2 face=arial>Section 2 offers (via the Experimental Detail Editor) the possibility to specify controlled vocabulary to describe the experiment.  The controlled vocabulary is specified for each project by using the &ldquo;Experimental Editor&rdquo; option within &ldquo;Manage Protocols and Lists&rdquo;.  Note that this vocabulary can facilitate compliance to community guidelines, such as HUPO Proteomics Standard Initiative (e.g. PSI-MI 2.5).  This controlled vocabulary (drop-down keywords) can be used for searching and structuring the data using the &ldquo;Advanced Search&rdquo; option. </P>
<P>
<FONT size=2 face=arial>
<I><a name="faq11"></a><b>Additional annotation&nbsp;</b></I>
</P>
<P>
<FONT size=2 face=arial>Section 3 allows for additional free-text annotation in the form of notes. Here you can cross-reference to notebook page numbers, add specifics of the experiment not captured in sections 1 and 2, or describe any problem or deviation from the reference protocols. It also allows you to link image files (e.g. Western blots or silver stained gels). </P>
<P>
<FONT color="#003163" face=arial><b>&rArr;<B> Navigate through the dropdown menus to select appropriate protocols associated with the experiment.  </b></P>
<P>
<FONT color="#800000" face=arial>
</B>
Note that selecting the option &ldquo;Edit&rdquo; within Section 2: Controlled Vocabularies of Experimental Details will open up a new window with dropdown menus.  </P>
<IMG align="" width="588" height="144" src="images/Analyst_help_img_12.jpg" >
<P align="justify">
<FONT color="#003163" face=arial><b>&rArr;<B> Select all desired fields to capture using the dropdown menus.   </b></P>
<P align="justify">
<FONT color="#000000" face=arial>
</B>
The selected options will be displayed on the right hand side in the order that they were selected. Use the Up/Down green arrows to change the order, or click on the <FONT color="#003163" face=arial>
<B>x<FONT color="#000000" face=arial>
</B>
 to remove the entry. </P>
<FONT color="#003163" face=arial>
<B>&rArr; Select [Pass Data] to transfer selection to the Experimental Detail page or [Close] to exit without saving the data.  </P></B>
<B>&rArr; S Continue filling experimental details, link any desired image, and press [Save]. </P><br>
</B>
<IMG align="" width="540" height="389" src="images/Analyst_help_img_13.jpg" >
<P>
<FONT color="#000000" face=arial>

Upon saving, you will be given the option to &ldquo;Modify&rdquo; the entry or follow the green arrow to the next page to enter specific samples.  Additionally, you can continue creating experiments by toggling between the [New Experiment], [Experiment List] and [Back to Bait] buttons at the top of the page to enter biological replicates for each of the baits. </P>
<P>
<FONT color="#003163" face=arial><b>&rArr;<B> Return periodically to the [Back to Bait] list to monitor your progress.    </b></P>
<P>
<FONT color="#000000" face=arial>
</B>
Note the colour-coded experimental status bars in the table.  This view shows our five baits, with experiments defined for four of them (MEPCE, EIF4A2, WASL and RAF1). The status column displays experimental details, experimental status and bait groups (see below). The colour-coding in the &ldquo;Status&rdquo; column indicates that information has been entered for each of the specified fields. </P>
<P>
<FONT color="#003163" face=arial><b>&rArr;<B> Click on the colour-coded status bar to obtain additional experimental details </b></P>
<P>
<FONT color="#000000" face=arial>
</B>
In the Bait view, experiments (and samples) defined under the same bait will be combined in the same row; multiple experiments will be shown by stacked colour bars.  <FONT color="#612221" face=arial>Note that you cannot delete baits for which experiments have been defined (note in the picture below that the FLAG_alone bait can still be deleted, since no experimental details have been entered yet).  Start by deleting the Experimental Details, and work your way up as previously described. </P>
<IMG align="" width="540" height="371" src="images/Analyst_help_img_14.jpg" >
<P>
<FONT color="#000000" face=arial>Once your baits and experiments are entered, you can create one or multiple samples to be linked to the bait and experiment.  The number of samples you create for a given experiment depends on your experimental set-up.  We tend to use different samples from a single experiment to represent technical replicates (i.e. different MS runs from the same biological sample), where all conditions are the same. Alternatively, multiple samples from one experiment may be created when the sample has been fractionated
(e.g. by strong cation exchange) prior to the analysis.  Each of the fractions is then assigned a different sample name within the same experiment.  The &ldquo;Notes&rdquo; sections from the Experimental Details page should explain the sample-naming scheme.  Note that we enter biological replicates as different experiments from the same bait. </P>
<P align="justify">
<B><a name="faq12"></a><b>Creating a new sample&nbsp;</b></P>
<P align="justify">
</B>
Following the green arrow on any of the Experimental Details pages will open a new window, allowing you to create one or many samples for a given experiment.  </P>
<P align="justify">
<FONT color="#003163" face=arial><b>&rArr;<B> In the Sample page, select the [Add New Sample] button to create a sample entry for this bait and set of experimental conditions. </b></P>
<IMG align="" width="564" height="180" src="images/Analyst_help_img_15.jpg" >
<P>
<FONT color="#000000" face=arial>
</B>
By default, ProHits will use the experiment name to name the first sample created from the relevant experiment.  ProHits will also assign a unique Sample ID. The sample name can be modified if necessary (in this case, just type the desired sample name in the text box).  In our group, we reserve the creation of duplicate samples from the same bait/experiment for technical replicates (e.g. if we split the final sample in half, and run each half separately).  Note that creating multiple samples from a single bait/experiment results in an automatic appending of _A, _B, etc. at the end of the sample name.  As long as a sample is not linked to any RAW file, it can be deleted by the owner. </P>
<IMG width="516" height="196" src="images/Analyst_help_img_16.jpg" >
<P>Now that you have created a new sample entry, you are ready to link it to a mass spectrometry raw data file from the Data Management module.  Links can be created automatically if the nomenclature indicated in the notice below for file naming is respected, and ProHits Data Management module is connected to the acquisition computers.  Alternatively, links can be created manually either from the Data Management or the Analyst modules. </P>
<IMG width="432" height="93" src="images/Analyst_help_img_17.jpg" >
</DIV>
<DIV class="Sect">
<H5>
<FONT size=2 face=arial>
<B>Linking raw files to a created sample </H5>
<P>
</B>
<I><a name="faq13"></a><b>Linking raw files from the Data Management module&nbsp;</b></I>
</P>
<P>
<FONT size=2 color="#003163" face=arial><b>&rArr;<B> From any page in the Analyst module, click &ldquo;Data Management&rdquo; on the left menu bar (shown by orange arrow on the right), link the desired file (as described in the Data Management section), parse the hits and return to the Analyst module.  </b></P>
<IMG width="143" height="405" src="images/Analyst_help_img_18.jpg" >
<P align="justify">
<FONT size=2 color="#000000" face=arial>
</B>
<I><a name="faq14"></a><b>Linking raw files directly from the Analyst module (alternative)&nbsp;</b> </I>
</P>
<P align="justify">
<FONT size=2 face=arial>For this alternative example, we are linking files from the Demo Yeast Gel free project, which you can access by going back to the home page of the Analyst module. </P>
<P align="justify">
<FONT color="#003163" face=arial><b>&rArr;<B> Go to the &ldquo;Report by Bait&rdquo; or &ldquo;Report by Sample&rdquo; page of the Analyst module and click on the Status column of the desired file to display experimental details.  Select [Link raw file]. </b></P>
<IMG align="" width="660" height="349" src="images/Analyst_help_img_19.jpg" >
<P>
<FONT color="#000000" face=arial>
</B>
This brings up a new page that allows you to select the file to be linked to the given entry. </P>
<P>
<FONT color="#800000" face=arial>Note that when you link files from the Analyst module, only those files not previously linked to another entry will be displayed.  To modify an existing link, you need to go back to the Data Management module, remove the link to the initial file, so that it can be made available to link to an entry either through the Analyst or Data Management modules. </P>
<IMG width="461" height="283" src="images/Analyst_help_img_20.jpg" >
<P align="justify">
<FONT color="#000000" face=arial>Once a raw file has been linked, the status bar will display an additional blue icon; the number indicates the number of files linked to that entry.  </P>
<IMG align="" width="612" height="327" src="images/Analyst_help_img_21.jpg" >
<P>
<FONT color="#003163" face=arial><b>&rArr;<B> Click the &ldquo;Data Management&rdquo; tab from any page of the Analyst module, parse the hits (as described in the Data Management section) and return to the Analyst module. </b></P>
<P>
<FONT color="#000000" face=arial>
</B>
Once hits are parsed (either from the Data Management or the Analyst module), a new purple coloured tab will appear in the status bar (in either Bait Report or Sample Report pages), indicating the total number of hits identified (sum of hits if more than one search engine was used).  In the &ldquo;Options&rdquo; column, a new graph icon appears; clicking this link brings up the search results for each sample.  Here we are showing MEPCE_pellet A in the sample report view. </P>
<IMG align="" width="564" height="237" src="images/Analyst_help_img_22.jpg" >
<P>You are now ready to explore your results. Use the left-hand side of the ProHits Analyst main page to view &ldquo;Report by Bait&rdquo; and &ldquo;Report by Sample&rdquo;.  The interface for the Bait and Sample reports is very similar. Here we provide an example for the Sample Report. <FONT color="#800000" face=arial>
<I>Bait versus Sample view</I>
: For some projects you may have a one-to-one correspondence between bait and sample. For other projects, you will have multiple samples linked to the same bait. Opening the Bait Report when two or more samples are linked to the bait will generate sequential protein hit lists for each of the samples linked to the bait.  ProHits does not recalculate scores or peptide numbers, but indicates (in bold) proteins detected in more than one sample (mousing over bolded names activates a pop-up window that provides details about the samples and hit scores). If you wish to explore each sample individually, use the &ldquo;Report by Sample&rdquo; link instead. </P>
<P align="justify">
<FONT color="#000000" face=arial>
<B><a name="faq15"></a><b>Navigating through the results&nbsp;</b></P>
<P align="justify">
</B>
Now that we have entered baits, linked and parsed search results, it is time to look at search results.  In this example, we will start from the &ldquo;Report by Sample&rdquo; page for MEPCE_pelletA. </P>
<P align="justify">
<FONT color="#003163" face=arial><b>&rArr;<B> From the sample list page, under &ldquo;Options&rdquo;, select the graph icon from one of the samples to see the results. </b></P>
<IMG width="105" height="51" src="images/Analyst_help_img_23.jpg" >
<P>
<FONT color="#000000" face=arial>
</B>
The following page appears, displaying the results from your search engine (Mascot in this example), alongside links to initial search results and biological databases.  Additional export and viewing functions, as well as options to filter the hits are also available from this page.  Over the next several pages, we will explore the Results page. </P>
<IMG align="" width="624" height="377" src="images/Analyst_help_img_24.jpg" >
<P>
<FONT size=2 face=arial>
<I><a name="faq16"></a><b>Search results&nbsp;</b></I>
</P>
<P>
<FONT size=2 face=arial>Towards the bottom of the page are the search results &ndash; by default, these are not filtered. The red colour in the ID field indicates the bait (as defined by the user when entering the experimental description). There are several tabs at the top of the search results table available for navigation.  The exact tabs displayed depend on the search engines used.  For the demonstration project, we have used the search engines Mascot and X!Tandem (GPM), and have analysed the results using the TransProteomics Pipeline. We will first explore the &ldquo;Mascot Hits&rdquo; tab. </P>
<P>
<B>The columns list the following parameters: </P>


<!--modify-->
<TABLE align="center" border=0 cellspacing=0 cellpadding=2 width="100%">
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>A)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  ID: Unique identifier assigned by ProHits (for database purposes)
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>B)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  Protein: Protein accession number from original database used by the search engine 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>C)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  Gene: NCBI Gene ID/ Gene Symbol, mapped by ProHits from Protein accession 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>D)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  Score: Mascot score (if applicable)
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>E)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  Expect value: GPM / X!Tandem Expect value (if applicable)
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>F)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  Frequency: The frequency that this protein hit is detected across all samples analyzed for this project   
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>G)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  Redundant: Other protein accession numbers matching the same set of peptides  
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>H)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  MW kDa: Calculated MW for the protein 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>I)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  Description: Definition field from the NCBI protein entry 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>J)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  # Peptide: Spectral counts (or total peptides), as calculated by the search engine 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>K)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  # Unique Peptide: Number of unique peptides, as calculated by the search engine 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>L)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  Coverage: Percentage of the indicated amino acid sequence identified by your search engine
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>M)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  Links: External links to the NCBI Entrez Protein page [GI], the NCBI Gene Page [Gene] and the BioGRID [BioGRID].
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>N)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  Filter: provides a colour-coded view of the Experimental Filters or Bio Filters that could be applied to remove each hit 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>O)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
   Option: Provides the list of peptides belonging to this hit (green M icon), opens up the original search engine search results (here Matrix Science icon for Mascot search results), and allows for the addition of Notes (call-out icons; includes manual exclusion) 
</div></TD>
</TR>
</TABLE>
<!--modify-->

<BR><FONT size=2 face=arial>
<I><a name="faq17"></a><b>Sorting options&nbsp;</b></I></B>
</P>
<P>
<FONT size=2 face=arial>You can sort the results from any of the black underlined columns (Score, #Peptide, #UniquePeptide and Coverage); sorting can be in ascending or descending value. </P>
<P>
<FONT size=2 face=arial>
<I><a name="faq18"></a><b>Links details&nbsp;</b></I>
</P>
<P>
<FONT size=2 face=arial>The following pages can be obtained from each of the items in the &ldquo;Links&rdquo; column. </P>
<IMG width="432" height="255" src="images/Analyst_help_img_25.jpg" >
<P align="justify">
<FONT size=2 face=arial>
<I><a name="faq19"></a><b>Option details&nbsp;</b></I>
</P>
<P align="justify">
<FONT size=2 face=arial>Pressing the following icons in the Option column will retrieve the peptide list (from the search engine) for each hit, or the entire search results file. </P>
<IMG width="365" height="77" src="images/Analyst_help_img_26.jpg" >
<IMG align="" width="492" height="204" src="images/Analyst_help_img_27.jpg" >
<P>We have now navigated through the table listing the search results.  However, the initial list is not filtered; that is, all hits, including likely contaminants, are listed.  ProHits has a built-in filter set that can be applied to the data to help identify <I>bona fide</I>
 interactors. </P>
<P>
<FONT size=2 face=arial>
<I><a name="faq20"></a><b>Using filters&nbsp;</b></I>
</P>
<P>
<FONT size=2 face=arial>Click on the [Show Filters] button within the results page to display the administrator-defined Bio and Experimental filters (see admin office for details of the filtering options) and background lists (see Manage Protocols and Lists) that can be applied to the data in this project.  On the left is the filter list and the graph on the right indicates the number of proteins that would be removed by activating each of the filters. Filters are activated or de-activated by clicking their associated checkbox. Once the desired filters are selected, press &ldquo;Apply exclusion&rdquo; to remove associated proteins from the search results list. <FONT color="#800000" face=arial>Note that the default frequency filter is set in the admin office module when creating the project, and that this value is listed when you select the project from the home page (see page 3).  In the case of the &ldquo;Demo Human Gel Free&rdquo; project shown here, the frequency filter was set at 3%, meaning that a protein detected in &gt;3% of samples within the project is flagged (as shown by the dark green icon in the results table). You do not need to use the default filter, and can modify this frequency cut-off as needed.  Also note that the frequency is not automatically recalculated every time you add a search result to ProHits: to recalculate the frequency, use the &ldquo;Update Frequency&rdquo; button on any &ldquo;Report&rdquo; page. </P>
<IMG align="" width="661" height="341" src="images/Analyst_help_img_28.jpg" >
<P>
<FONT color="#000000" face=arial>In this example, we will filter the data shown above by applying the following filters: </P>




<TABLE align="center" border=0 cellspacing=0 cellpadding=2 width="100%">
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>1)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  click the "background" button, and select the "FLAG_top_contaminants" list from the dropdown menu. The background lists are user-defined, and controlled via the “Manage Lists and Protocols” option. 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>2)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  proteins detected with a Mascot score <60 will be removed 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>3)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  proteins with <20% sequence coverage will be removed 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>4)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  proteins detected with a single unique peptide will be removed
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>5)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  proteins detected in >25% of the samples in this project will be removed
</div></TD>
</TR>
</TABLE>

<P>After applying filters, the list of hits is reduced (see the disappearance of C1QBP, NCL and NPM1 &ndash; which are common contaminants - while SART3, LARP7 and LSM8 remain). The filters can be modified and sorting repeated: ProHits does not remove any data from the dataset, but only displays filtered lists. </P>
<IMG align="" width="661" height="299" src="images/Analyst_help_img_29.jpg" >
<P>
<FONT color="#800000" face=arial>Note that the graph on the right indicates the number of hits that have not been filtered out, but belong to the different categories that could be filtered out.  In this example, after filtering, only 1 RP (Ribosomal Protein) remains, as compared to 64 in the unfiltered example. </P>
<P>
<FONT size=2 color="#000000" face=arial>
<I><a name="faq21"></a><b>Comparing your data to literature interactions&nbsp;</b></I>
</P>
<P>
<FONT size=2 face=arial>Prohits allows you to automatically query the BioGRID interaction database for previously-reported interactions specific to your bait.  To do so, select the type of interactions desired (physical interactions from high-throughput (HTP) studies, physical interactions not from HTP studies (non-HTP), genetic interactions of both types), and press &ldquo;Apply exclusion&rdquo;.  The interactions that overlap with the literature will be highlighted in the &ldquo;filter&rdquo; column. (the next few figures will be replaced by MEPCE as soon as the new version of BioGRID comes online).  <FONT color="#800000" face=arial>Note that the definition of HTP and non-HTP is from BioGRID: high-throughput papers are identified as such by BioGRID curators; as a default, publications reporting &gt;100 interactions are also identified as HTP. </P>
<IMG align="" width="657" height="207" src="images/Analyst_help_img_30.jpg" >
<P>
<FONT color="#000000" face=arial>Selecting [BioGRID interactions not found here] opens a new window with the details of the &ldquo;missed interactions&rdquo;, as shown below. </P>
<P>
<FONT color="#800000" face=arial>Note that the overlap is performed after data filtering is applied, thus care should be taken when analyzing apparent lack of overlap. The example bellow shows the effect of the application of a stringent filter on &ldquo;missed interactions&rdquo;. </P>
<IMG width="492" height="179" src="images/Analyst_help_img_31.jpg" >
<P>
<FONT size=2 color="#000000" face=arial>
<I><a name="faq22"></a><b>View and navigate hits from the TransProteomics Pipeline&nbsp;</b></I>
</P>
<P>
<FONT size=2 face=arial>The tabs located immediately above the search results table allow you to explore search results that have been parsed from the PeptideProphet and ProteinProphet components of the TPP. </P>
<P>In the page &ldquo;Mascot TPP hits&rdquo;, different filtering options based on the number of unique or total peptides, as well as the probability values for the TPP have been implemented.  A link to the TPP search result viewer is provided in the Option column of the table (orange Institute for Systems Biology icon): this opens up the standard ProteinProphet view, allowing further exploration of the data. </P>
<IMG align="" width="661" height="256" src="images/Analyst_help_img_32.jpg" >
<P align="justify">The &ldquo;Mascot TPP Peptides&rdquo; tab lists all of the parsed parameters at the peptide level, and provides some basic filtering options, as well as a link to the PepXML viewer. </P>
<IMG align="" width="540" height="243" src="images/Analyst_help_img_33.jpg" >
<P>
<FONT size=2 face=arial>
<I><a name="faq23"></a><b>Viewing results using Cytoscape&nbsp;</b></I>
</P>
<P>
<FONT size=2 face=arial>At the top right corner on the Report page is a link to the molecular interaction visualization program Cytoscape.  Clicking this link will upload the filtered data (with BioGRID interactions if this option is selected). Note that all mass spectrometry data will also be uploaded (you can use these parameters as attributes of the &ldquo;edges&rdquo; in Cytoscape). We will review Cytoscape requirements and basic information in the discussion of the &ldquo;Comparison&rdquo; function. </P>
<P>
<FONT size=2 face=arial>
<I><a name="faq24"></a><b>Export Sample report&nbsp;</b></I>
</P>
<P>
<FONT size=2 face=arial>Selecting the &ldquo;Export Sample Report&rdquo; on the top right corner allows the user to export text (comma-separated values (CSV) or tab separated values (TSV)) files.  Fields to be exported are user-defined and will be exported in the order selected.  The user can also create pre-defined export formats that can be further modified. Note that this exports NON-FILTERED hits (filtered hits can be exported via the comparison tool). </P>
<IMG width="376" height="435" src="images/Analyst_help_img_34.jpg" >
<P>The exported file can be opened with Excel or similar software. </P>
<P align="justify">
<FONT size=2 face=arial>
<I><a name="faq25"></a><b>Using the Notes option&nbsp;</b></I>
</P>
<P align="">
<FONT size=2 color="#003163" face=arial><b>&rArr;<B> Click on the &ldquo;callout&rdquo; icon at the end of any bait row </b></P>
<P align="justify">
<FONT color="#000000" face=arial>
</B>
This brings up the following window: </P>
<IMG align="" width="381" height="143" src="images/Analyst_help_img_35.jpg" >
<P>
<FONT color="#003163" face=arial><b>&rArr;<B> Add desired text, and press [Save New Notes]   </b></P>
<P align="justify">
<FONT color="#000000" face=arial>
</B>
The following screen can then be seen: </P>
<IMG width="384" height="172" src="images/Analyst_help_img_36.jpg" >
<P>Only the person who entered the note is allowed to modify or delete it.  Additional users can create additional comments on the same bait or sample. </P>
<P>In addition to adding free text annotation (default &ldquo;Discussion&rdquo; note type), &ldquo;Bait groups&rdquo;, &ldquo;Experiment groups&rdquo; or &ldquo;Sample Groups&rdquo; can be created for each project and are managed via the &ldquo;Manage Protocols and Lists&rdquo; option. Use the dropdown box to select the desired &ldquo;Notes Types&rdquo;. </P>
</DIV>
<DIV class="Sect">
<H5>
<FONT size=2 face=arial>
<B><a name="faq26"></a><b>Creating gel-based samples&nbsp;</b></H5>
<P>
<FONT size=2 face=arial>
</B>
ProHits has functionality designed to track samples analyzed in a high-throughput manner from gel-based proteomics. Several of the steps are identical to the steps required to create samples for gel-free projects. Here we will briefly outline the major differences when entering gel-based samples. <FONT color="#800000" face=arial>Note that you can add samples from in-gel digestion as &ldquo;gel-free&rdquo; &ndash; especially if you are only analyzing a few samples without the use of an autosampler. </P>
<P>
<FONT color="#000000" face=arial>
<a name="faq27"></a><b>Adding a &ldquo;Gel-based&rdquo; sample&nbsp;</b></P>

<FONT color="#003163" face=arial><b>&rArr; Select &ldquo;Add Gel-based&rdquo; sample from the left menu, and choose whether you will be starting from an existing gel, or create a new gel.</B></P>






<IMG align="" width="575" height="235" src="images/Analyst_help_img_37.jpg" ><BR><BR>

<FONT color="#003163" face=arial><b>&rArr; To create a new gel, add information required in bold, and upload the image of the gel.</B></P>
<IMG width="329" height="215" src="images/Analyst_help_img_38.jpg" >
<P align="center">
<FONT color="#800000" face=arial>
</B>
While the image is not mandatory, it is highly recommended to link a well-annotated image of the gel. </P>
&nbsp;&nbsp;<IMG width="257" height="273" src="images/Analyst_help_img_39.jpg" >
<P align="center">
<FONT color="#000000" face=arial>After a gel is created, you can see the information via the &ldquo;Report by Gel&rdquo; function on the left menu. </P>
<IMG align="" width="575" height="97" src="images/Analyst_help_img_40.jpg" >

<BR><BR><FONT color="#003163" face=arial><B>&rArr; Use the green arrows in the &ldquo;Options&rdquo; field to enter baits from that gel (as shown in the gel-free sample section). </B>

<BR><BR><FONT color="#003163" face=arial><B>&rArr; From each bait, define the Experimental Details, as shown in the gel-free section. </B>



<P>
<FONT color="#000000" face=arial>
</B>
Clicking on the green arrow in the experimental details section will by default prompt you to define a lane on the gel, and guide you through the entry of individual band samples in the autosampler plate that you will use for data acquisition. Simply clicking on a plate well will create an associated sample &ndash; you can add the intensity of each band on the stained gel, as well as the approximate molecular weight.  </P>
<IMG align="" width="528" height="393" src="images/Analyst_help_img_41.jpg" >
<P>Continue entering all desired bands from the selected lane, or use the navigation options at the top of the page to upload samples from the next lane, return to the list of all lanes, or return to the experimental description.  </P>
<P>Opening the &ldquo;Report by plate&rdquo; and clicking the plate icon in the &ldquo;Options&rdquo; field, allows you to view your plate layout. </P>
<IMG align="" width="575" height="257" src="images/Analyst_help_img_42.jpg" >
<P align="justify">If you wish to use the &ldquo;Auto-link&rdquo; option to link your raw files from the Data Management module to the samples in Analyst, select [Print Preview].  </P>
<IMG align="" width="623" height="376" src="images/Analyst_help_img_43.jpg" >
<P>When setting up the acquisition on the mass spectrometer, the folder name (here <FONT color="#FF0000" face=arial>
<B>20080715_YDP00155_A1_P1<FONT color="#000000" face=arial>
</B>
) as well as the Raw file names (e.g. <FONT color="#FF0000" face=arial>
<B>B06_35<FONT color="#000000" face=arial>
</B>
) need to match these above. </P>
</DIV>
<DIV class="Sect">
<H5>
<FONT size=2 face=arial>
<B><a name="faq28"></a><b>The Comparison tool&nbsp;</b></H5>
<P>
<FONT size=2 face=arial>
</B>
ProHits has a built-in comparison tool that allows you to look at the results of several experiments side-byside.  You can perform comparisons at the bait level or at the sample level, and compare the results from the search engines (e.g. Mascot or X!Tandem) or the TPP. For this demonstration, we will perform a comparison at the sample level, using the Mascot search engine. </P>
<IMG align="" width="551" height="389" src="images/Analyst_help_img_44.jpg" >
<P align="justify">
<FONT color="#003163" face=arial><b>&rArr;<B> Select the desired baits to be compared </b></P>
<P align="justify">
<FONT color="#000000" face=arial>
</B>
You can sort by Bait ID, Gene name, Protein ID, or by any of the user-defined flags that were used for the project. </P>
<P align="justify">
<FONT color="#003163" face=arial><b>&rArr;<B> Press the &gt;&gt; arrow button to transfer the baits to the &ldquo;Selected Baits&rdquo; window </b></P>
<P align="justify">
<FONT color="#000000" face=arial>
</B>
You can transfer files one at the time, or by large groups.  The files are added to the list in the order selected.  This will also be the order of the columns in the Comparison View. </P>
<P align="justify">Use the green up/down arrows on the right hand side to reorganize the sort order.  Individual Baits or Groups of Baits can be reorganized. </P>
<IMG align="" width="575" height="363" src="images/Analyst_help_img_45.jpg" >
<P>
<B><a name="faq29"></a><b>Merging files prior to Comparison&nbsp;</b></P>
<P>
</B>
Additional options are available that provide merging options for two or more files.  <FONT color="#800000" face=arial>
<I>Please note that the merging is a very simple process that simply reports the best hits for the item but does not do any recalculation.  If the selected display option in the report is the Mascot score, the best scoring hit will be listed; if the selected display option is based on spectral counts, the hit with the highest spectral counts will be reported</I>
. <FONT color="#000000" face=arial>The merging function allows you to group two or more control runs (click on the &ldquo;Control&rdquo; button before transferring the selected files).  </P>
<IMG align="" width="575" height="151" src="images/Analyst_help_img_46.jpg" >
<P>You can group any set of additional files by first clicking on the multicolour icon to select a new group, then transferring the given files to the right side.  The listing order will be as follows: The control group will be listed first, followed by all other groups in the order selected by the user, followed by all individual entries in the order selected by the user.  Note that within the same group, hits will be combined, and only the maximal value for each of the properties will be reported. </P>
<IMG align="" width="636" height="251" src="images/Analyst_help_img_47.jpg" >
<IMG align="" width="576" height="385" src="images/Analyst_help_img_48.jpg" >
<P>
<FONT color="#003163" face=arial><b>&rArr;<B> When you are done adding all desired baits and/or bait groups, press [Generate Report] </b></P>
<P align="justify">
<FONT color="#000000" face=arial>
</B>
This will open a new window, the Comparison page. </P>
<P>
<B><a name="faq30"></a><b>Comparison page&nbsp;</b></P>
<P>
</B>
When you open the Bait Comparison page, you will see an unfiltered view of the hits. Each column represents a different sample or bait (or group of samples or baits if the &ldquo;merge&rdquo; function was used). The rows represent each of the hits detected across the <I>n</I>
 samples or baits.  Clicking on the Gene Name will take you to NCBI Gene; selecting [BioGRID] will open the BioGRID entry for the given protein; clicking on the number in the Protein ID field will bring you to the Entrez Protein page.  The last column allows you to compare the peptides identified across the bait purifications. </P>
<IMG align="" width="635" height="368" src="images/Analyst_help_img_49.jpg" >
<P>The default display is with Total Peptide Numbers (spectral counts), and the default sorting option is by descending number of spectra, starting by the left-most bait or group.  Note that these sorting options can be modified. In particular, ProHits recalculates and sorts using the following parameters: </P>
<IMG width="217" height="259" src="images/Analyst_help_img_50.jpg" >
<P align="justify">In addition to the sorting options, ProHits Comparison allows you to filter your data in a manner similar to the filtering options in the Bait Report page.   </P>
<P align="justify">
<FONT color="#003163" face=arial><b>&rArr;<B> To access the filtering option, select [Click to apply filters]. </b></P>
<P align="justify">
<FONT color="#000000" face=arial>
</B>
An expanded menu allows you to select criteria for removal of proteins from the Comparison list. </P>
<BR><FONT color="#003163" face=arial><b>&rArr;Select desired parameters </B></P>
<BR><FONT color="#003163" face=arial><b>&rArr;Select to highlight the BioGRID overlap if desired </B></P>

<IMG width="472" height="255" src="images/Analyst_help_img_51.jpg" >
<BR><BR><FONT color="#003163" face=arial><b>&rArr;To apply filters, press [Go] </B></P>

<P align="justify">
<FONT color="#000000" face=arial>
</B>
This generates a modified list, similar to the process described in the Bait report section.  If selected, the overlap with BioGRID is indicated by stars or triangles in the list below. </P>
<P align="justify">Note that mousing over any of the entries shown below will pop up a menu box listing the scoring details. </P>
<IMG width="447" height="528" src="images/Analyst_help_img_52.jpg" >
<P>
<FONT size=2 face=arial>
<I><a name="faq31"></a><b>Using Cytoscape directly from ProHits comparison&nbsp;</b></I>
</P>
<P>
<FONT size=2 face=arial>ProHits allows you to visualize your data using Cytoscape. If using the ProHits filters, the data post-filtering will be displayed (changing the filter will modify the display).  If the BioGRID overlap function has been selected, the resulting Cytoscape view will incorporate both your 
mass spectrometry data, the overlap between your mass spectrometry data and data in BioGRID, and data 
detected only in BioGRID (including interactions amongst first neighbours of the hits).  The colour-coding (see 
below) allows you to identify the source of the data.</P>
<P align="justify">Before you can use the Cytoscape plug-in, you need to have the Runtime Environment (JRE) installed on your local computer (you can use the following URL to test whether your computer has a functional JRE:  </P>
<IMG width="437" height="384" src="images/Analyst_help_img_53.jpg" >
<P>
<U>
<FONT color="#0000FF" face=arial>http:///www.java.com/en/download/help/testum.xml</U>
<FONT color="#000000" face=arial>). </P>
<P>The first time that you click the &ldquo;Cytoscape&rdquo; icon, Cytoscape will be installed on your local computer. Press the [Cytoscape] link immediately above the table to open the current interaction file in Cytoscape.  The baits are indicated by red nodes (alongside the unique bait identifier), and the recovery of baits in a purification is indicated by circling the white baits in red.  The colour-coding of the arrows is mapped to the spectral counts, as shown above, and all peptide annotation is encoded as an edge attribute. <FONT color="#800000" face=arial>Note that if the &ldquo;Overlap with BioGRID&rdquo; function has been selected, interactions specific to your dataset will be still shown in blue, interactions that overlap between your dataset and BioGRID will be shown in green, while BioGRID-only interactions will be displayed in white. </P>
<P>
<FONT color="#000000" face=arial>The original image is a circular layout; in the example shown here, this has simply been converted to a spring-embedded layout, with weight on the edge (unique peptide). </P>
<P>Note that all of the standard Cytoscape tools are available. </P>
<P>
<FONT size=2 face=arial>
<I><a name="faq32"></a><b>Other export options&nbsp;</b></I>
</P>
<P>
<FONT size=2 face=arial>You may also wish to launch Cytoscape (or additional network viewers) from an Excel Table, in which you can add annotation or other mapping options. To do so, use the <B>[Export (table)]</B>
 option, also located at the top of the table. This will create a .csv file that can be opened and modified in Excel.  The file will be displayed as a bait&gt;hit list with each subsequent column listing a separate parameter.  These lists are easily opened using a stand-alone Cytoscape version. </P>
<IMG width="540" height="132" src="images/Analyst_help_img_54.jpg" >
<P align="justify">
<B>[Export (matrix)]</B>
 provides a view similar to that displayed in the Comparison page, with the option to export only the parameter currently displayed (e.g. spectral counts), or the option to list all parameters inside each cell. Again, a .csv file that can be opened and modified in Excel will be created.  </P>
<P align="justify">View only the displayed value (here = total peptide counts):   </P>
<IMG width="300" height="160" src="images/Analyst_help_img_55.jpg" >


<P align="left">View all parameters:   </P>
<IMG width="492" height="164" src="images/Analyst_help_img_56.jpg" >
<P>
<B>Zoom of the details inside each cell: </P>
<P>
</B>
PID:SC(PT-PU-C%-F%-SF%)  <br>56790935:500(16-10-20.70-11.88-75) </P>
<P>Legend: <br>PID: Protein ID (NCBI Entrez Protein) <br>SC: Mascot Score <br>PT: Total number of peptides <br>PU: Number of Unique peptides <br>C%: Percentage of the protein sequenced <br>F%: Frequency of occurrence of the protein in the entire dataset <br>SF%: Frequency of occurrence of the protein amongst compared baits/samples </P>
<P align="justify">
<FONT size=2 face=arial>
<I><a name="faq33"></a><b>Comparing larger numbers of baits&nbsp;</b></I>
</P>
<P align="justify">
<FONT size=2 face=arial>ProHits also allows you to visualize larger numbers of experiments. </P>
<P>
<FONT color="#003163" face=arial><b>&rArr;<B> Select the baits or samples to be compared and press [Generate Report] </b></P>
<P align="justify">
<FONT color="#000000" face=arial>
</B>
A heat-map view of the data will be generated. </P>
<IMG width="395" height="351" src="images/Analyst_help_img_57.jpg" >
<P align="left">
<FONT color="#003163" face=arial><b>&rArr;Click anywhere on the map to expand and view names and other details </b></P>
<IMG width="324" height="155" src="images/Analyst_help_img_58.jpg" >
<P>
<FONT color="#800000" face=arial>
Note, however, that due to file size, the [Cytoscape] option is not available with this heat map view.  The [Export(table)] option is still available, however, and can allow you to upload data into a stand-alone Cytoscape session (the [Export(matrix)] function is also available).  Note that due to large file sizes, these export functions may run slowly. </P>
<P>
<FONT color="#000000" face=arial>For additional export functionalities, you can go back to the main Analyst module, and select the &ldquo;Export Hits&rdquo; option from the left-hand menu. </P>
<P>
<B><a name="faq34"></a><b>Automatically adding baits for comparison from the baits or sample report list pages&nbsp;</b></P>
<P>
</B>
ProHits allows you to select baits or samples to be added to the comparison page while working on other pages. To use this option, simply click the box located to the left side of each sample in the sample list or by the bait in the bait list. </P>
<IMG width="293" height="303" src="images/Analyst_help_img_59.jpg" >
<P>The selected sample (or baits) will be automatically added to the &ldquo;Selected Samples&rdquo; and &ldquo;Selected Baits&rdquo; pages of the Comparison view.  Note that if a bait is selected, all samples corresponding to this bait will automatically be added to the comparison view.  </P>
<P>You can keep browsing and adding baits or samples for Comparison as you go.  These will stay selected for the duration of your session, or until you manually remove them from the Comparison page. </P>
</DIV>
<DIV class="Sect">
<H5>
<FONT size=2 face=arial>
<B>Search options </H5>
<P>
<FONT size=2 face=arial>
</B>
ProHits Analyst allows you to perform simple searches (for individual Gene Names) or Advanced searches (for multiple gene names or keywords in the protein description field or controlled vocabulary).  Here, we will briefly review these options: </P>
</DIV>
<DIV class="Sect">
<H5>
<FONT size=2 face=arial>
<a name="faq35"></a><b>Simple Search (Gene name)</b></H5>
<P>
<FONT size=2 face=arial>
</B>
ProHits has a simple search function that is located at the upper corner of the Analyst module main page.  </P>
<P>
<FONT color="#003163" face=arial><b>&rArr;<B> Enter an official Gene Name, then press the right pointing arrow. </b></P>
<IMG align="" width="552" height="361" src="images/Analyst_help_img_60.jpg" >
<P>
<FONT color="#000000" face=arial>
</B>
This lists all instances of this Gene name across your project.  Use the [Browse buttons] to navigate through the data.  Below, we have expanded the &ldquo;Hit (Report by Sample)&rdquo; option. The gene SART3 was identified in both of the MEPCE biological replicates.  Note that the column &ldquo;Score of Probability/ # Peptides&rdquo; refers to the score from the search engines (or TPP) and the total number of peptides identified for SART3 in the MEPCE runs. </P>
<IMG align="" width="623" height="172" src="images/Analyst_help_img_61.jpg" >
<P>
<B><a name="faq50"></a><b>Advanced Search </b></P>
<P>
</B>
The Advanced Search function can be accessed from the menu bar.  This function allows you to search for keywords (or combinations of keywords) and retrieve entries across the following categories: <B>Baits</B>
, <B>Hits</B>
, <B>Samples</B>
, <B>Gels</B>
, <B>Raw Files</B>
 and <B>Auto Search</B>
. </P>
<P>In the simplest sense, you can use the Advanced search in a manner similar to the Simple search, i.e. to retrieve entries associated with a gene name. You can use &ldquo;wildcards&rdquo;, either at the front, at the end, or both at the front and end of your query. Note that using wildcards (especially at the front) decreases search speed. </P>
<IMG align="" width="635" height="213" src="images/Analyst_help_img_62.jpg" >
<P align="justify">This will return a list of results that you can then explore further by selecting the [Browse] option for each of the categories, as for the simple search. </P>
<P>
<FONT size=2 face=arial>
<I><a name="faq36"></a><b>Other keywords that can be searched</b></I>
</P>


<TABLE align="center" border=0 cellspacing=0 cellpadding=2 width="100%">
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>1)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  <b>Bait</b> (the keywords were detected in the entry for a bait – fields searched are "Gene Name", "Gene ID", "Locus Tag", "Protein ID", "Epitope Tag", "Bait Mutation", "Clone Number", "Vector", with optionally, Bait "Description"). The searched fields are indicated by red ovals below: </div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr></TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  <IMG width="488" height="331" src="images/Analyst_help_img_63.jpg" >  
</TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>2)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  <b>Hits</b> (the keywords were detected in the hits list – field searched is "Gene" Name, with, optionally, Protein "Description"). You can similarly see the hits across TPP results. The searched fields are indicated by red ovals below. 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr></TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  <IMG width="440" height="148" src="images/Analyst_help_img_64.jpg" >  
</TD>
</TR>
<TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>3)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  <b>Sample</b> (the keywords were detected in the user-defined "Sample Name") 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>4)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  <b>Gel</b> (the keywords were detected in the fields "Gene Name", "Gene Image", and "Lane Code") 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>5)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  <b>Raw files</b> (the keywords were detected in "File Name" or "Folder Name"). This brings you to the "Data management" module, and lists the folders / files bearing the selected keywords. 
</div></TD>
</TR>
<TR>
<TD align="right" width="5%"  valign="top" height="14"><div class=maintext_extra_wr>6)</TD>
<TD align="left" valign="top" height="14"><div class=maintext_extra_wr>
  <b>Auto Search</b> (the keywords were detected in "Search Task Name"). This brings you to the "Data management" module, and lists the search tasks bearing the selected keywords. 
</div></TD>
</TR>
</TABLE>



<P>
<B>Searching Bait/Protein Description:</B>
 You can search for a keyword inside the Description field (e.g. &ldquo;squamous&rdquo; in the example above), by allowing wildcards on both sides.  In other words, the entire field is captured (not individual words), and any partial field (e.g. &ldquo;squamous&rdquo; or &ldquo;carcinoma&rdquo;) must be preceded and/or followed by wildcards. Note again that such searches may be very slow. </P>
<P>
<B>Searching in Experimental Details (controlled vocabularies):</B>
 The search function also allows you to search (or limit your searches) based on selected controlled vocabulary.  Simply press [Select] (bottom right corner of the Experimental Detail section).  This will take you to the Experimental Details/controlled vocabulary section where you can select categories/values to be passed to the Advanced search page. </P>
<P>
<B>Restricting searches by date:</B>
 You can restrict search results by date.  Simply press the [select] button in the Date field to open a drop-menu. </P>
<P>
<B>Using logical operations:</B>
 You can combine several keywords (simply separate them by spaces), to search for &ldquo;at least one of the words&rdquo;, &ldquo;all words&rdquo; (in any order), or &ldquo;the exact phrase&rdquo; within a field, such as &ldquo;Description&rdquo;.  Note that the &ldquo;all words&rdquo; and &ldquo;exact phrase&rdquo; operations only apply within a field. Alternatively, you can use the &ldquo;at least one of the words&rdquo; option to search for different keywords even across different fields.  This will generate a list of results that will be the union of the separate lists. </P>
<P>
<B>Hits searches returning too many results:</B>
 Note that there is a limit of 3000 to search results. Try narrowing down your search parameters and try again. </P>
<P>
<B>Example:</B>
 Searching for squamous AND carcinoma in 293 Flp-In T-REx cells and in anti tag coimmunoprecipitation; date restricted to January 2009 &ndash; January 2010. </P>
<IMG width="499" height="283" src="images/Analyst_help_img_65.jpg" >
</DIV>
<DIV class="Sect">
<H5>
<FONT size=2 face=arial>
<B>Uploading search results  </H5>
<P>
<FONT size=2 face=arial>
</B>
The Analyst module allows you to import search results from the TransProteomics Pipeline (TPP), Mascot or GPM/X!Tandem. This function is very useful for laboratories that are not interested in the Data Management module of ProHits, e.g. if they are using a third party analysis solution.  All that is needed for this section are the search results files or both TPP ProteinProphet and TPP PeptideProphet XML files. </P>
<FONT color="#003163" face=arial><B>&rArr; Select the [Upload Search Results] link on the left hand side of the Analyst module. </P>
</B>
<P>
<FONT color="#000000" face=arial>
</B>
This opens up the list of all the baits that you have created in the Analyst module. </P>
<IMG align="" width="623" height="264" src="images/Analyst_help_img_66.jpg" >
<br><FONT color="#003163" face=arial><B>&rArr; Select the upload option  at the end of the desired sample </P>
</B>
<IMG width="17" height="17" src="images/Analyst_help_img_67.jpg" >
<P>This pops up a new page: </P>
<IMG width="508" height="283" src="images/Analyst_help_img_68.jpg" >
<br><P><B><FONT color="#003163" face=arial>&rArr; Select the type of search results files you wish to upload (TPP, Mascot, GPM/X!Tandem), and Browse your local computer for the files in the right format.</b>  </P>
<P><FONT color="#003163" face=arial>&rArr; Press [Submit] to upload search results. </b></p>

<FONT color="#000000" face=arial>
<IMG align="" width="623" height="597" src="images/Analyst_help_img_69.jpg" >
</DIV>
</DIV>
<DIV class="Part">
<H3>
<FONT size=2 face=arial>
<a name="faq37"></a><b>Manage Protocols and Lists&nbsp;</b></H3>
<P>
<FONT size=2 face=arial>
Five types of Protocols and Lists pages are available in ProHits. With the exception of the &ldquo;Epitope Tag Lists&rdquo;, that are applied to all projects on the local ProHits server, the other protocols and lists are only applicable to the current project.  Lists and Protocols defined for a given project may be imported into a different project, so long as the user has access to both projects, and permission to modify individual lists and/or protocols. Access to individual pages of the &ldquo;Manage Protocols and Lists&rdquo; of the Analyst module is restricted via page permissions set in the admin office module. We suggest limiting the number of users having access to these management tools. </P>
<P><FONT size=2 face=arial>
Here, we will briefly review the function of the different protocols and lists, then show a few examples for each category of protocol and/or list. </P>
<P><FONT size=2 face=arial>
<B>Text-based protocols &ndash; pages 42-43 </B></P>
<P><FONT size=2 face=arial>
Text-based protocols provide details on the experimental procedures.  We have separated the protocols into four modules: Biological Material (i.e. what type of cells, expression system, growth conditions, etc.), Affinity Purification (from cell lysis to elution), Peptide Preparation (including separation at the protein/peptide level after elution), and LC-MS conditions.  For our internal use, we attempt in providing very detailed protocols that could be used for publication with only minor modifications. </P>
<P><FONT size=2 face=arial>
<B>Experimental Editor &ndash; pages 44-45 </B></P>
<P><FONT size=2 face=arial>
The Experimental Editor allows you to create and manage the list of controlled vocabularies to be used within the Experimental Details page, in conjunction with the text-based protocols and additional notes. For our internal use, we attempt to capture information that would allow PSI MI 2.5 compliance, as well as other relevant information that would allow us to structure our data.  Note that the terms entered in this section are searchable in the &ldquo;Advanced Search&rdquo; function. </P>
<P><FONT size=2 face=arial>
<B>Background Lists &ndash; pages 46-48 </B></P>
<P><FONT size=2 face=arial>
This function allows you to define and manage one or more lists of contaminants and/or background proteins associated with a given project.  For example, you could maintain individual lists of the proteins found to associate non-specifically with different affinity matrices.  The proteins on a given &ldquo;Background&rdquo; list can be subtracted from the list of identified proteins, in Individual Report, Comparison, or Export views. </P>
<P><FONT size=2 face=arial>
<B>Group Lists &ndash; pages 49-51 </B></P>
<P><FONT size=2 face=arial>
This function allows you to further organize and/or mark certain baits, experiments or samples by adding a colour-coded and user-defined icon that will appear in the Status bar of the Report by Bait or Report by Sample view.  Useful Sample level group could include comments about the quality of the data, while Experiment level group would refer to some property of the experimental prep (e.g. phospho-enrichment), and a Bait level group could be the type of tag used. Additionally, ProHits allows you to mark (at the Sample level), samples that are to be included in publication (and/or to be exported to a third party). </P>
<P><FONT size=2 face=arial>
<B>Epitope Tag Lists &ndash; page 52 </B></P>
<P><FONT size=2 face=arial>
This is the only list that applies to the entire local ProHits database.  The objects in this list are available on the Bait entry page, and define the tag (if applicable) used for tagging of the bait. N or C refer to the position of the tag relative to the bait.  When available, the epitope tags have been mapped back to the standard vocabularies from the Molecular Interaction PSI MI 2.5; an automated link to the Ontology Lookup Service (OLS).  We strongly suggest using this service to enter the PSI MI 2.5 terms when entering new tags. </P>
<P align="justify"><FONT size=2 face=arial>
<a name="faq38"></a><b>Text-based protocols&nbsp;</b></P>

<FONT color="#003163" size=2 face=arial><B>&rArr; Select the &ldquo;Text-based Protocols&rdquo; entry from the Manage Protocols and Lists </B></P>


<IMG align="" width="533" height="148" src="images/Analyst_help_img_70.jpg" ><br><br>

<FONT color="#003163" size=2 face=arial><B>&rArr; Click [add new], and paste or type your protocol.  Then press [Save]. </B></P>
<P>
<FONT color="#800000" size=2 face=arial>
Note 1: because the protocols are displayed as html and exported as a CSV or TSV file, certain characters and symbols will not display properly, and should be spelled out.  Examples are &mu; (u or micro), &deg; (degree), and &lsquo; (apostrophe). </P>
<P>
<FONT color="#800000" size=2 face=arial>
Note 2: to each protocol is assigned a unique identifier. The protocol can be modified or even deleted as long as it has not been used.  Once in use, modifications are no longer allowed and a new protocol (that will be assigned a different protocol number) will need to be created. </P>
<IMG width="533" height="329" src="images/Analyst_help_img_71.jpg" >
<P align="justify">
<FONT color="#000000" size=2 face=arial>Continue entering protocols as above.  Alternatively, if a protocol of interest already exists in another project to which you have access, you can import it directly from that project. </P>


<P align="justify">
<FONT color="#003163" size=2 face=arial><B>&rArr; Click [import from other projects], select desired project by clicking the &gt;&gt; button and pressing [Submit]. </B></P>
<IMG align="" width="621" height="435" src="images/Analyst_help_img_72.jpg" >
<br><br><FONT color="#003163" size=2 face=arial><B>&rArr; Click the green arrow to transfer the protocol from the source project to the destination project, modify if needed, and press [Save]. </B></P>
<P align="justify">
<FONT color="#000000" size=2 face=arial>
You can export protocols linked to a project to a CSV file that can be opened in Excel or similar programs.  The &ldquo;Detail&rdquo; column contains the full text of the protocol. </P>
<IMG align="" width="623" height="79" src="images/Analyst_help_img_73.jpg" >
<P align="justify">
<FONT color="#000000" size=2 face=arial>
<a name="faq39"></a><b>Experimental Editor&nbsp;</b></P>
<P>
<FONT color="#003163" size=2 face=arial><b>&rArr; Select the &ldquo;Experimental Editor&rdquo; entry from the Manage Protocols and Lists </b></P>


<P align="justify">
<FONT color="#000000" size=2  face=arial>
You will see a list of the categories already defined for your project. </P>
<IMG width="425" height="145" src="images/Analyst_help_img_74.jpg" >
<P>
<FONT color="#003163" size=2 face=arial><b>&rArr; To view the values already entered under the &ldquo;interaction detection method&rdquo; category, click on the [+] button to expand this category.   </b></P>



<P>
<FONT color="#000000" size=2 face=arial>
You can add additional values by typing their description and pressing [Add]. Values that are not yet linked to an entry are followed by a red <FONT color="#FF0000" face=arial>
<B>X</B></FONT><FONT color="#000000" face=arial>
. Pressing <FONT color="#FF0000" face=arial>
<B>X</B></FONT><FONT color="#000000" face=arial>
 deletes the entry.  Note that for this category, we have used PSI MI 2.5 terms, to facilitate later deposition in interaction databases. </P>
<IMG width="425" height="265" src="images/Analyst_help_img_75.jpg" >
<P>
<FONT size=2 color="#003163" face=arial><b>&rArr;<FONT size=2 face=arial>
To define new categories, press the [+] button next to &ldquo;Edit selection&rdquo;. </b></P>
<P align="justify">
<FONT color="#000000" size=2 face=arial>
This allows you to enter a new category.</P>
<P>
<FONT size=2 color="#003163" face=arial><b>&rArr; <FONT size=2 face=arial>
To import a category from another project to which you have access, simply click the checkbox associated to the category under the Edit selection option to transfer the category (and associated values) to current project. </b></P>
<IMG width="551" height="660" src="images/Analyst_help_img_76.jpg" >
<P>
<FONT color="#000000" size=2 face=arial><a name="faq40"></a><b>Background Lists&nbsp;</b></P>
<P>

<FONT color="#000000" size=2 face=arial>
In addition to the Bio Filters and Experimental Filters defined in the Admin Office module, ProHits allows you to define additional filters to remove non-specific (or background) proteins.  These filters are project-specific and created within a bait (or sample) report page in the Analyst module. Several different filters can be associated with the same project (e.g. corresponding to different workflows used in the project).  Creation of these filters requires administrator-level privileges. The filters can be created by adding proteins manually (one-by-one) to an existing list of contaminants.  The filters can also be generated by uploading a list (or table) of hits identified in control run(s), in which case the mapping only requires the Entrez Gene ID field. You can also add multiple proteins at once from any other pre-existing list (e.g. in Excel). The mapping is via the NCBI Entrez Gene ID. </P>

<FONT size=2 color="#003163" face=arial><b>&rArr; Select the &ldquo;Background Lists&rdquo; entry from the Manage Protocols and Lists. </b></P>
<FONT size=2 color="#003163" face=arial><b>&rArr; From the entry page, click on the <IMG align="" width="17" height="17" src="images/Analyst_help_img_77.jpg" > (modify) icon to upload a list of contaminant proteins.</b></P>

<IMG width="461" height="229" src="images/Analyst_help_img_78.jpg" >
<P>
<FONT color="#800000" size=2 face=arial>
Note that an efficient method to generate a non-specific filter set utilizes the ProHits comparison tool. First, select multiple control runs and merge them into a single &ldquo;Control&rdquo; group.  This will open up a Comparison page with a single column called &ldquo;Control Group&rdquo; displayed in yellow.  As before, the maximal value for the parameter visualized is displayed (e.g. spectral count).  Apply filters (e.g. number of unique peptides, protein coverage, etc.) desired, and select [Export(table)] to export a comma-delimited file (*.csv).  Save this file on your hard drive, and go to any Bait report page. (Note that any Excel or text file that lists the NCBI Gene ID may also be used). </P>



<FONT size=2 color="#003163" face=arial><b>&rArr; Browse the file to be uploaded, select delimiter, and press [upload file]. </B></P>

<FONT size=2 color="#003163" face=arial><b>&rArr; Select the &ldquo;add as new&rdquo; radio button and type a name (here: FLAG_top_contaminants). </B></P></B>
</b>
<P align="justify">
<FONT color="#000000" size=2 face=arial>
Alternatively, append to an existing list by using the dropdown menu. </P>
<P align="justify">
<FONT color="#003163" size=2 face=arial><b>&rArr; Select the row to start importing, and check the radio button in the GeneID field.  Then, click [Process File]. </b></P>
<IMG width="461" height="280" src="images/Analyst_help_img_79.jpg" >
<P>
<FONT color="#000000" size=2 face=arial>
Once the file is processed, the contaminant list will be displayed (after selecting the name in the dropdown menu). You can manually remove individual entries (they will not be on the background list) by clicking the &ldquo;delete&rdquo; icon. </P>
<IMG width="460" height="297" src="images/Analyst_help_img_80.jpg" >


<P align="left">
<FONT color="#003163" size=2 face=arial><b>&rArr; To manually add a protein to a background list, press [Add New].   </b></P>
<P align="left">
<FONT color="#000000" size=2 face=arial>
You will then be prompted to enter a new contaminating/background protein. You can simply enter a gene name and species and press [Get Protein Info].  Press [Add] to include this protein on the background list. </P>
<IMG width="413" height="189" src="images/Analyst_help_img_81.jpg" >


<P align="justify">
<FONT color="#000000" size=2 face=arial>
If you do not specify a pre-entered non-specific set, ProHits will allow you to create a new one (press [Confirm] after entering the non-specific set name). </P>
<P align="justify">
<FONT color="#003163" size=2 face=arial><b>&rArr; To import a contaminant list from a different project, press [Import from other projects], and navigate through the menus.   </b></P>
<IMG width="413" height="292" src="images/Analyst_help_img_82.jpg" >
<P>
<FONT color="#000000" size=2 face=arial>
You now have your own background set that can be used for filtering both in the bait/sample report pages and in comparison. <FONT color="#800000" face=arial>We recommend using caution when creating these sets: some proteins that are true interacting partners for a given bait may also be present (usually in lower amounts) on the background list. It may be a good idea to only include on this non-specific (background) list proteins detected across more than one control run with a high number of peptides. </P>
<P>
<FONT color="#000000" size=2 face=arial>
<a name="faq41"></a><b>Group Lists&nbsp;</b></P>
<P>
<FONT color="#000000" size=2 face=arial>
ProHits allows the definition of new &ldquo;groups&rdquo; for any given project.  As described earlier, groups are added to baits/samples by selecting the &ldquo;Notes&rdquo; Option.  Groups act like flags and are displayed in the status bar in the &ldquo;Report by Bait&rdquo; or &ldquo;Report by Sample&rdquo; pages.  These groups can help you organize your data. </P>
<P>
<FONT color="#000000" size=2 face=arial><b>&rArr; Select the &ldquo;Groups&rdquo; entry from the Manage Protocols and Lists. </b></P>
<P>
<FONT color="#000000" size=2 face=arial>
As with the other Protocols and Lists, you can define new groups, or import a new  group from another project. Here we will import sample groups from a different project. </P>
<IMG width="448" height="156" src="images/Analyst_help_img_83.jpg" >
<P align="justify">
<FONT color="#000000" size=2 face=arial><b>&rArr; To import groups from a different project, press [import from other projects], and navigate through the menus. </b></P>
<P align="justify">
<FONT color="#000000" size=2 face=arial>
As with the Text-based protocols, use the green arrows to transfer desired groups to the current project. You can only transfer one group at a time.  </P>
<IMG width="449" height="356" src="images/Analyst_help_img_84.jpg" >
<P align="justify">
<FONT color="#000000" size=2 face=arial>
Upon transfer of a group, it will appear on your group list as shown below (the new group can be modified or deleted, unless it is used for a sample). </P>
<IMG width="317" height="180" src="images/Analyst_help_img_85.jpg" >
<P>
<FONT color="#003163" size=2 face=arial><b>&rArr; To create a new group, press [add new], and navigate through the menus.   </b></P>
<P>
<FONT color="#000000" size=2 face=arial>
Simply enter a short descriptive name for the group as well as a description, an abbreviation (that will be listed alongside the baits or samples), and an icon.  Icons can easily be created in Photoshop as 17 x 17 pixel images, and saved as GIF, PNG or JPEG files. A template can be downloaded from the ProHits group page. </P>
<IMG width="389" height="303" src="images/Analyst_help_img_86.jpg" >
<P>
<FONT size=2 face=arial>
<I><a name="faq42"></a><b>Export version&nbsp;</b></I>
</P>
<P>
<FONT size=2 face=arial>ProHits allows you to flag a group of samples, e.g. for inclusion in a publication or export to a third party.  </P>
<P>
<FONT color="#003163" size=2 face=arial><b>&rArr; To create an Export Version, press [add new].   </b></P>
<P>
<FONT color="#000000" size=2 face=arial>
This will open a new menu with the default abbreviation (Version1, VS1), and Icon (a yellow star with the number 1). Subsequent versions will automatically be numbered VS2, VS3, etc., and the number inside the star will similarly increase. We suggest that you provide a meaningful short name and an accurate description of each &ldquo;Export Version&rdquo;. </P>
<IMG width="449" height="212" src="images/Analyst_help_img_87.jpg" >
<P>
<FONT color="#000000" size=2 face=arial>
<a name="faq43"></a><b>Epitope Tag Lists&nbsp;</b></P>
<P>
<FONT color="#003163" size=2 face=arial><b>&rArr; Select the &ldquo;Epitope Tag Lists&rdquo; entry from the Manage Protocols and Lists. </b></P>
<P>
<FONT color="#000000" size=2 face=arial>
This lists all tags available to the local ProHits projects.  Clicking on the [+] sign expands the details of the epitope tag. We have mapped the current epitope tags in the demo database to PSI MI 2.5, using the Ontology Lookup Service (OLS) at the EBI. A link page is provided that allow retrieval of additional information. </P>
<IMG align="" width="629" height="293" src="images/Analyst_help_img_88.jpg" >
<P>
<FONT color="#000000" size=2 face=arial>
In addition to the epitope tags currently in the system, you can create additional tags by pressing [add new] and navigating through the fields.  Again, we strongly recommend mapping your terms to PSI MI 2.5 whenever possible. </P>
<IMG width="401" height="241" src="images/Analyst_help_img_89.jpg" >
</DIV>
</BODY>
</HTML>
