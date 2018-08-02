
1. Auto search require PEAR/HTTP_Request. But it doesn't support multple select.
Modify Pear Request.php file in /usr/share/pear/HTTP/Request.php (version 1.3.0)
------------------------------
212,217d211
<     
<    /**
<     * to handle prohits submited array
<     * @var array  [0]='QUE_Prohits'; [1]=array();
<     */
<     var $_prohits = null;
471,474d464
<         if(is_array($value) and strstr($name, 'Prohits')){
<           $this->_prohits[$name] = $this->_arrayMapRecursive('urlencode', $value);
<           return;
< 	}
811a802
> 
817,826d807
<                     if($this->_prohits){
<                        foreach($this->_prohits as $k => $v){
< 		        	          //print_r($this->_prohits);exit;
<                          $tmp_name = preg_replace("/_Prohits/","", $k);
<                          foreach($v as $newV){
< 		                        array_push($flatData, array($tmp_name,$newV));
<                          }
<                       }
<   
-------------------------------

Usage:
$req = new HTTP_Request($formaction);
$req->setMethod(HTTP_REQUEST_METHOD_POST);
$req->addHeader('Content-Type', 'multipart/form-data');
$req->addPostData('MODS_Prohits', $MODS);  //$MODS is array.
$result = $req->addFile("FILE", $raw_file_path);
if (PEAR::isError($result)) {
  fatalError($result->getMessage() . " in inclued function: auto_search_mascot.inc.php", __LINE__);
}
$response1 = $req->getResponseBody();
  
2. make log files in ../logs/. make sour that those files are writable for the user who runs the shell sceript and apache user(root, nobody or apache).
  raw_back.log
  auto_run.log
  search.log

3. ./auto_run_shell.php
  It is shell script.
  It should be set in cronjob to run at least once a day.
  It will trigger backup and auto-search shell scripts.
  Usage:
  http://en.wikipedia.org/wiki/Crontab
  login as root or a user who has permission to raad php scripts and write log files
  edit cronjobs.
  shell>crontab -e
  # at 20:01 and 03:01 every day
  1 3,20 * * *  path_to/auto_run_shell.php > /dev/null 2>&1  
  
  
4. ./autoBackup/raw_backup_shell.php.
   It is shell script.
   It is triggered by auto_run_shell.php
   auto_run_shell.php will read raw_back.log file to get last backup process ID. If the process doesn't exists, the script will send to system run.
   Sleep time based on BACKUP_RUNNING_TIME in ../config/conf.inc.php.
   $tmp_PID =  system("php $backup_script ".$sleep_sec." > /dev/null & echo \$!");
  
 5. ./autoSearch/auto_search_table_shell.php
   It is shell script.
   It contans 2 included files: auto_search_gmp.inc.php, auto_search_mascot.inc.php
   It is triggered by auto_run_shell.php
   $tmp_PID =  system("php " . $search_script." ".$tableName."  " .$sleep_sec." > /dev/null & echo \$!");
   $tableName -- is a raw file table which has searchTask and searchResults tables;
   $sleep_sec -- is sleep time for a running task in the $tableName, if the running task is set to run everyday.
   A process ID will be get from the running task record. If the process is running the script will not be triggered.
  
  6. If a storete computer is used (the raw files are in the different computer from Prohits server), following file tree should be in the storae computer.
  
  ProhitsFolder
    |__config
    |__logs
    |__msManager
      |__autoBackup
      |    |__raw_backup_shell.php
      |__autoSearch
           |__auto_search_gpm.inc.php
           |__auto_search_mascot.inc.php
           |__auto_search_table_shell.php
           
    Datebase can be acessed from the remote storage computer.
    auotSearh folder should be writable by the apache user. (the user name is in apache conf file)