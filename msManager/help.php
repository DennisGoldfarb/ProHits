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
<html>
<head>
<link rel="stylesheet" type="text/css" href="./ms_style.css">
<title>ms data management</title>
</head>
<body>
<center>
<table border="0" cellpadding="0" cellspacing="0" width="95%">
  <tr>
    <td><br><font face="Arial Black" size="+2" color="#055698">
   <b><div align="center"><img src=./images/prohits_logo.gif border=0 align=middle> &nbsp; Welcome to Ask Prohits</div></b>
    </font><br></td>
  </tr>
  <tr height="1">
    <td bgcolor="#006699" height="1">
       <img src="./images/pixel.gif" width="1" height="1" border="0"></td>
  </tr>
  <tr height="1">
    <td><br>
    <ol>
     <li> <a href="#faq1" class=help>How the Prohits/Auto-Backup works?</a>
     <li> <a href="#faq2" class=help>How raw files are linked to Prohits?</a>
     <li> <a href="#faq3" class=help>Why my raw file is not linked? How to manually link raw file?</a>
     <li> <a href="#faq4" class=help>How to access my raw file?</a>
     <li> <a href="#faq5" class=help>How to download raw files?</a>
     <li> <a href="#faq6" class=help>How to upload raw file?</a>
     <li> <a href="#faq66" class=help>How to convert LCQ/LTQ raw file to MGF/mzDate format?</a>
     <li> <a href="#faq7" class=help >Why I can't see my raw file?</a>
     <li> <a href="#faq8" class=help>Why my raw file is not linked?</a>
     
     <li> <a href="#faq9" class=help>How to set search engine parameter set?</a>
     <li> <a href="#faq10" class=help>What raw file format can be set to auto-search?</a>
     <li> <a href="#faq11" class=help>Why I can't see the Mass spec machin from Auto-search list?</a>
     <li> <a href="#faq12" class=help>How to create a new search task?</a>
     <li> <a href="#faq13" class=help>How the search resutls are organized?</a>
     
     <li> <a href="#faq14" class=help>How can I add more raw files in finished task and re-run the task?</a>
     <li> <a href="#faq15" class=help>What is the Prohits Parser program? How does it work?</a>
     <li> <a href="#faq16" class=help>Who can run the Prohits Parser program?</a>
    </ol>   
    </td>
  </tr>
  <tr height="1">
    <td bgcolor="#006699" height="1">
       <img src="./images/pixel.gif" width="1" height="1" border="0"></td>
  </tr>
  <tr height="1">
    <td>
      <ol>
     <li> <a name="faq1"></a><b>How the Prohits/Auto-Backup works?</b>
     <br><br>
      <ol type="A">
      <li>In Prohits configuration file, Prohits administrator defines the Prohits Storage computer (STORAGE_IP) and the folder within (STORAGE_FOLDER) for raw file storgae.
      <li>Each mass spectrometry data folder has been mounted to Prohits. A sub folder in STORAGE_FOLDER has been created as the destination folder. For example, a LCQ mass spectrometry has a destination folder STORAGE_FOLDER / LCQ in Prohits. The raw data source folder and storage folder (destination folder) are defended in Prohits conf file as well.
      <li>Prohits administrator has set a scheduled task to run Prohits / Auto-backup once or multiple times a day. 
      <li>Prohits / Auto-backup will get source folder contents. If the folder does not exist in the destination folder it will copy the entire folder to destination folder and save all content information (file size, file type, last modified date) in the database. If the folder exists in the destination folder, it will check folder size. If the folder size is the same as that of destination folder, it will be ignored. Otherwise, it will get the source folder contents and check each element (file and subfolder). If it is a new file/folder or if the file/folder size is not the same as that of the destination folder, it will be processed. If the element is a folder, Prohits will consider it is a sub-source folder and process the sub-source folder in the same way as source folder. The source file will not be removed after a file has been copied to Prohits. 
      <li>FILE_COPY_DELAY_HOURS has been set in Prohits conf file. Only the file which file-modified-time - current-time > FILE_COPY_DELAY_HOURS will be copied to storage.
      </ol>
     <br>
     
     <li> <a name="faq2"></a><b>How raw files are linked to Prohits</b>?
      <br><br>
      Prohits has two databases handling sample and raw file information.  Prohits / Analyst contains all user-submitted sample information while Prohits / MS data management collects raw files and auto-searches. The raw file information and sample information can be linked. The linker can be automatically created by Prohits or manually by using the linker tool. If you want your raw file to be automatically linked, you have to submit your samples to Prohits / Analyst before the mass spec machine generates raw files, and have to bear the folder and file name format as described below.    
      <br><br>
      Prohits can handle multiple folder trees from a mass spectrometry computer. But it is preferred that the raw file root folder contains only sub-folders. In order for Prohits to link a raw file to sample/band information automatically, you need to create a raw file name with the format stated below. There are two types of raw file name structures: gel based projects and gel-free projects. 
      <br><br>
      <ol type="A">
      <li>Gel based project folder has its plate name and plate ID in Prohits / Analyst. Each plate has one plate folder associated as a sub-folder (plate folder) in the source root data folder. All raw files within the plate will be in the same plate folder. The folder name and file name formats should be: 
      <br><br>
      Folder: <b>20061023_PY0001_A23_P43</b> ( date_plateName_PlateID_projectID) 
      <br>
      File: <b>A01_2341.RAW</b> (sampleLocation_sampleID ) 
      <br>
      A mass spectrometry operator should print the plate layout after samples have been submitted in Prohits / Analyst. The layout will generate the folder name and raw file name that should be.

        <li>b.	Gel free project has no plate. Prohits links a raw file to its corresponding sample according to its project ID and sample name. Each project can have multiple folders. But folder name has to end with "_P#". The "#" is the project number. And the file name is the bait id with bait gene name. If a yeast protein has no gene name assigned, ORF name will be used. <br>
          Folder: <b>AnyNameNoSpace_P34</b> ( project ID is 34) 
          <br>
          File: <b>123_CDC4.RAW</b> ( bait ID is 123. bait gene name is CDC4)
          <br>
      </ol><br>
      <img src=./images/raw_file_structure.gif border=0>
     <li> <a name="faq3"></a><b>Why my raw file is not linked? How to manually link raw file?</b>
     <br><br>
      <ol type="A">
        <li>A green link icon appears by a raw file record only if the raw file name and raw file folder name meet the name format requirement as described above. The green link icon means the mass spectrometry-generated raw file is associated with one of the user-submitted sample/band in Prohits.
        <li>A 'MS specialist' user can modify a link if the link is not correctly connected or no link exists. The user ID will be recorded with the modified link. A yellow link icon means that the link has been manually changed.
        <li>A link can not be removed if its search hits have been parsed.

      </ol>
      <br><br>
     
     
     <li> <a name="faq4"></a><b>How to access my raw file?</b>
     <br><br>
      <ol type="A">
         <li>Raw files and folders are categorized in mass spectrometry computers.  When the source / raw file folder has been copied to Prohits, the folder content tree is kept the same as in the source folder. Click the mass spectrometry name at the left menu, the first level of folder content will be listed. If the element is sub-folder, click the sub-folder and its content will be listed in the next page.
         <li>You can sort the folder content by ID, folder / file name, create date or Project ID.
         <li>You can select a project, date, or month when browsing raw files.
     </ol>
      <br><br>
     <li> <a name="faq5"></a><b>How to download raw files?</b>
     <br><br>
        Raw files and folders can be downloaded to your local computer. Click the download icon and a pop-window will appear, then click the 'Download' button. If you want to download a folder, the folder will be zipped and you can save the zipped file to your local computer. If the folder exceeds certain size, it can't be downloaded. You should ask Prohits administrator to copy it for you. Alternatively, you can download content files one by one.
     <br><br>
      
     <li> <a name="faq6"></a><b>How to upload raw file?</b>
      <br><br>
        Only "MS Specialist" users can upload raw files to Prohits. The maximum size of an uploaded file is 10 MG. The limit is set by Prohits server "post_max_size" and "upload_max_filesize". 
        
      <ol type="A"><li>Select raw file type and Project.
        <li>Select an existing folder or create a new folder. If it is a new folder, please type a folder name.
        <li>Browse the raw file to add to the file list.
        <li>Repeat step c to add more files in the same folder.
        <li>Click the "Upload File(s)" button.
        <li>After files have been uploaded, you can click the folder to view the file list you have uploaded and link the file to the sample in Prohits Analyst by clicking the link-icon.
        
              
      </ol>
      <br><br>
     <li> <a name="faq66"></a><b>How to convert LCQ/LTQ raw file to MGF/mzDate format?</b>
      <br><br>
      <ol type="A">
        <li>Only the "MS Specialist" users and "Admin" can use this function
        <li>A raw file conversion server is required for the function. Please contact your Prohits administrator to make sure that the server is running.
        <li>There are two ways to convert LCQ/LTQ raw files to other format. You may choose to convert raw files automatically when it is being copied to storage. Or you may select raw files and change their format manually. 
        <li>The format of a raw file can be converted to depends on the conversion server.

      </ol>
      <br><br>
     <li> <a name="faq7"></a><b>Why I can't see my raw file?</b>
       <br><br>
       <ol type="A">
        <li>There are three possibilities you can't see your raw file. First, Prohits / Auto-Backup is scheduled to start at later time even if the raw file has been generated by mass spectrometry.
        <li>If Prohits / Auto-Backup has been run after the raw file is created, the raw file will not be processed until the time reaches the FILE_COPY_DELAY_HOURS. The FILE_COPY_DELAY_HOURS is defined in Prohits conf file. It's designed to prevent processing a raw file when the file generation has not finished.
        <li>If other user can see the raw file in Prohits MS Data Management but you can't, It means the raw file belongs to a project which you have no permission to access.  You should ask the Prohits administrator to set up permission for you or move the folder to the correct project folder if it's misplaced.
        <li>If the raw file folder or file name contains characters other than A-Z, a-z, 0-9 or underscores, the folder or file will not be processed. You can see the details by clicking the "Backup Log".

       </ol>
       <br><br>
     <li> <a name="faq8"></a><b>Why my raw file is not linked?</b>
      <br><br>
      <ol type="A">
        <li>A green link icon appears by a raw file record only if the raw file name and raw file folder name 
        meet the name format requirement as described above. The green link icon means the mass 
        spectrometry-generated raw file is associated with one of the user-submitted sample/band in Prohits.
        <li>A 'MS specialist' user can modify a link if the link is not correctly connected or no link exists. 
        The user ID will be recorded with the modified link. A yellow link icon means that the link has been 
        manually changed.
        <li>A link can not be removed if its search hits have been parsed.

      </ol>
      <br><br>
     <li> <a name="faq9"></a><b>How to set search engine perameter set?</b>
      <br><br>
       Only specified users can use auto-search function. The permission is defined in Prohits/Admin Office user 
       account. Prohits supports both Mascot and GPM auto-search. The search parameters can be preset and saved 
       as a parameter set. 
       <ol type="A">
         <li><b>Xcalibur:</b><br>
	      Xcalibur is the data analysis software for the entire family of Thermo Scientific mass spectrometers. 
        Prohits passes the Xcalibur parameters with raw file to search engine and converts LCQ / LTQ raw data to 
        MGF data.
         <li><b>Mascot / the GPM form:</b><br>
         	Click Mascot or the GPM icon, a pop-window will bring up a Mascot or the GPM parameter form. The form 
          is connected with "MASCOT_IP" or "GPM_IP" computer defined in Prohits configuration file.  
          The form will display the first parameter set and a list of the rest. Select any parameter set in the 
          "Set Name" drop-down box, and the form will be refreshed to display the selected parameter set. 
          If you have permission, you can modify an existing parameter set. By clicking the "New Set" radio box, 
          you can create a new parameter set. For a new set name, only characters a-z, A-Z and 0-9 are allowed

       </ol>
       <br><br>
     <li> <a name="faq10"></a><b>What raw file format can be set to auto-search?</b>
       <br><br>
       Any raw file formats can be set to Prohits auto-search as long as your local Mascot and the GPM support. 
       Mascot and the GPM support most common mass spec file formats. If your local Mascot is installed in windows 
       operating system it can support LCQ / LTQ raw file directly. If your local the GPM is installed in windows 
       operating system it can be support LCQ / LTQ raw file as well, but your Prohits administrator has to follow 
       Prohis installation instruction to modify the GPM files.
       <br><br>
     <li> <a name="faq11"></a><b>Why I can't see the Mass spec machin from Auto-search list?</b>
       <br><br>
       When you click the "Auto Search" tab it will display the mass spec machine icons. Those machines are set 
       to auto-search. It's normal that you see a machine icon in storage list, but not in auto-search list. 
       It means the machine is set to auto-backup only, not set to auto-search. A Prohits administrator can 
       change the setting in the Prohits Admin Office.
       <br><br>
     <li> <a name="faq12"></a><b>How to create a new search task?</b>
       <br><br>
       <ol type="A">
         <li>One mass spec machine can only have one running search task. Current running task will be stopped if a new task has bee created.  So make sure a task has completed before creating a new task.
         <li>Type a task name in the "Task Name" box. 
         <li>Select a raw file format type. If it LCQ / LTQ raw file, edit the Xcalibur parameter. Confirm with Prohits administrator if the local Mascot and the GPM support LCQ/LTQ raw file.
         <li>Check Mascot check box if you want to use Mascot. Select Mascot parameter set and click "Edit" button to confirm Mascot parameters.
         <li>Check the GPM check box if want to use the GPM. Select the GPM parameter set and click "Edit" button to confirm the GPM parameter set.
         <li>Select a task schedule. When you check "Start Now", the task will start immediately after you click "Run Task" button. 
         <li>If the "Automatically add files" check box is not checked, you can use "Add files" or "Remove file" function.  Prohits is designed to run a task that raw files are located in same folder. When you click "Add Files" button, a pop-up window will allow you to select a folder and files. The selected file format has to be supported by search engines. Click "Submit" button to close pop-window and add selected files in "Data File List" box.
         <li>If the "Automatically add files" box is checked, all data files will be added into "Data File List" after a folder has been selected.
         <li>You can click the "Running Task Status" at the left menu to show shell task ID and running time. You can also click "View Search Log" to view search progress.

       </ol>
       <br><br>
     <li> <a name="faq13"></a><b>How the search resutls are organized?</b>
       <br><br>
         Search tasks are organized by folders. A folder can have multiple tasks. Click the "Search Results" at 
         the left menu to get a folder list.  The yellow highlighted folder contains a running task. Click the 
         check mark at the folder record, the search results detail page will appear. This page contains all 
         tasks that are completed with the folder. The first task is grayed. A list of the search results for 
         the grayed task shows at the bottom of the screen.  Mouse-over on the task ID will create a pop-window 
         that shows the task parameters. Click the task ID to display the raw file search results for the task at 
         the bottom.
       <br><br>
     
     <li> <a name="faq14"></a><b>How can I add more raw files in finished task and re-run the task?</b>
       <br><br>
       You can modify a completed or stopped auto-search task. Since one mass spec machine can only have one 
       running task, modifying a completed or stopped task will stop the running task and the modified task 
       will become running task again. Click the "Search Task" link at the left menu, the last task will show. 
       Use "Previous Task" and "Next Task" buttons to browse task. Click "Modify Task" button to get the task form. 
       You can add more raw files to the task. But you can only add raw files within the same folder. 
       Click the "Run Task" button to save the task and process the task immediately. The modified task will 
       be the running task for the machine.
       <br><br>
     <li> <a name="faq15"></a><b>What is the Prohits Parser program? How does it work? </b>
      <br><br>
       Prohits Analyst stores all project and sample information. Prohits MS Management collects all raw file 
       information and search results. If a raw file is linked to sample, the raw file search results can be 
       parsed to Prohits Analyst database. Prohits Parser is the program's built-in search results page. 
       It parses hit and peptide information. If the task contains both Mascot and GPM results, user can select 
       one or bother search results to parse. The parsed search results will be automatically marked at the right 
       checkbox. The parsed results can also be removed from Prohits Analyst database by the user who performed 
       the parse. Some simple filters can be set when parse hits: Mascot ions score, Mascot bold red peptide, 
       Mascot hit score, GPM ion expect and GPM hit expect. 
      <br><br>
     
     <li> <a name="faq16"></a><b>Who can run the Prohits Parser program? </b>
       <br><br>
       As for Prohits auto-search, Prohits Parser can be used only by the appointed users defined in Prohits user account.
       <br><br>
    </ol>   
   </td>
  </tr>
  <tr height="1">
    <td bgcolor="#006699" height="1">
       <img src="./images/pixel.gif" width="1" height="1" border="0"></td>
  </tr>
</table> 
<br>
[<a href='javascript: window.close();' class=left_menu>Close Window</a>]
</center>
</body></head></html>