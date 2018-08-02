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

class mysqlDB {
  var $link;
  var $selected_db_name;
  var $db_selected;
  var $Host;
  var $User;
  var $Pswd;
  
  function mysqlDB($db = "", $host="", $user="",$pswd=""){
     $this->db_connect($db, $host, $user, $pswd);
  }
  function db_connect($db = "", $host="", $user="",$pswd=""){
    if(!$db){
      $db = DEFAULT_DB;
    }
    if(!$this->link or $this->selected_db_name != $db) {
        $this->selected_db_name = $db;
        $this->Host = ($host)?$host : HOSTNAME;
        $this->User = ($user)?$user : USERNAME;
        $this->Pswd = ($pswd)?$pswd : DBPASSWORD;
        $this->link  = mysqli_connect($this->Host, $this->User, $this->Pswd, $db ) or die("Unable to connect to database...$db" . mysqli_error($this->link));
    }
    mysqli_query($this->link, "SET SESSION sql_mode = ''");
  }
  
  function change_db($db){
    if($this->selected_db_name !=$db){
      $this->selected_db_name = $db;
      $this->db_selected = mysqli_select_db($this->link, $this->selected_db_name) or die("Can not use '". $this->selected_db_name . "' database ". mysqli_error($this->link));
    }
    mysqli_query($this->link, "SET SESSION sql_mode = ''");
  }
  function check_connection(){
    if (!mysqli_ping ($this->link)) {
       //here is the major trick, you have to close the connection (even though its not currently working) for it to recreate properly.
       mysqli_close($this->link);
       $this->db_connect($this->selected_db_name, $this->Host, $this->User,$this->Pswd); 
    }
  }
  function fetchAll($sql='', $col_arr=''){
    //$records = array();
    //SET GLOBAL max_allowed_packet=4048576
    $sql_m = 'SELECT @@global.max_allowed_packet';
    $results = mysqli_query($this->link, $sql_m);
    $row = mysqli_fetch_row($results);
    $old_max_allowed = $row[0];
    $sql_len = strlen($sql);
    if($sql_len >= $old_max_allowed){
      $new_max_allowed = strlen( $sql ) + 1024;
      $sql_m = 'SET @@global.max_allowed_packet = ' . $new_max_allowed;
      mysqli_query($this->link, $sql_m);
      $this->close();
      $this->link  = mysqli_connect($this->Host, $this->User, $this->Pswd, $this->selected_db_name );
    }
    //end reset max_allowed_packet
    

    $records = array();
    if(!$sql) die("No query passed");
    $results = mysqli_query($this->link, $sql);
    if(!$results) $this->error_handle("<b>fetchAll error</b>: <br>***$sql***");
    $field_names = $this->_get_result_field_names($results);
    $num_fields = count($field_names);
    $k = 0;
    while($row = mysqli_fetch_array($results)){
      for ($i = 0; $i < $num_fields; $i++) {
        $this_field_name = $field_names[$i];
        $field_tmp = mysqli_fetch_field_direct($results, $i);
        if($col_arr){
          $records[$this_field_name][$k] = $row[$this_field_name];
        }else{
          $records[$k][$this_field_name] = $row[$this_field_name];
        }
      }
      $k++;
    }
    @mysqli_free_result($results);
    return $records;
  }
  
  function get_total($sql=''){
    $num = 0;
    if(!$sql) die("No query passed");
    $sqlResult = mysqli_query($this->link, $sql);
    return mysqli_num_rows($sqlResult);
  }
  function fetch($sql=''){
    $record = array();
    if(!$sql) die("No query passed");
    $results = mysqli_query($this->link, $sql);
    if(!$results) $this->error_handle("<b>fetch error</b>: <br>***$sql***");
    $field_names = $this->_get_result_field_names($results);
    $num_fields = count($field_names);
    if( $row = mysqli_fetch_array($results) ){
      for ($i = 0; $i < $num_fields; $i++) {
         $this_field_name = $field_names[$i];
         $field_tmp = mysqli_fetch_field_direct($results, $i);
         $record[$this_field_name] = $row[$this_field_name];
      }
    }
    @mysqli_free_result($results);
    return $record;
  }
  function fetch_ID($table, $ID){
    $record = array();
    if(!$table or !$ID) die("No table name or ID passed");
    $sql = "select * from $table where ID='$ID'";
    $record = $this->fetch($sql);
    return $record;
  }
  function _get_result_field_names($results){
     $names = array();
     $num_fields = mysqli_field_count($this->link);
     for ($i = 0; $i < $num_fields; $i++) {
       $field_tmp = mysqli_fetch_field_direct($results, $i);
       $names[] = $field_tmp->name;
    }
    return $names;
  }
  function exist($sql=''){ 
    $record = $this->fetch($sql);
    if( count($record) ){
       return true;
    }else{
       return false;
    }
  }
  
  function insert($sql=''){
    if(!$sql) die("No query passed");
    $ret = mysqli_query($this->link, $sql) or $this->error_handle("<b>insert error</b>: <br>***$sql***");
    if($ret){      
      return mysqli_insert_id($this->link);
    }else{
      return $ret;
    }  
  }
  
  function insert_pair($field_pair_arry, $table=''){
    //$field_pair_arry['ID'] = 3, $field_pair_arry['firstName'] = 'Frank';
    if(!is_array($field_pair_arry)) die("should pass a files_pair_arry");
    $sql="INSERT INTO $table SET ";
    $i = 0;
    while (list($key, $val) = each($field_pair_arry)) {
       if($i++) $sql .= ', ';
       if($val == "now()"){
         $field_type = $this->get_field_type($table, $key);
         if( $field_type == "date" or $field_type == "datetime" or $field_type == "timestamp" or $field_type == "time"){
            $sql .= "$key=$val";
            continue;
         }
       }
       $sql .= "$key='$val'";
        
    }
    //echo $sql;
    return $this->insert($sql);
  }
  //used for delete and modify record
  function execute($sql=''){
    if(!$sql) die("No query passed");
    $re = mysqli_query($this->link, $sql ) or $this->error_handle("<b>execute error</b>: <br>***$sql***");
    return $re;
  }
  
  function delete($table='', $id=0){
    if(!$table or !$id) die("delete error: on table name or id");
    $sql = "delete from $table where id='$id'";
    $this->execute($sql);
    return $this->affected_rows();
  }
  
  function update($sql=''){
    $this->execute($sql);
    return $this->affected_rows();
  }
  
  function update_pair($field_pair_arry, $table='', $id=0){
    //$field_pair_arry['ID'] = 3, $field_pair_arry['firstName'] = 'Frank';
    if(!is_array($field_pair_arry)) die("should pass a files_pair_arry");
    if(!$table or !$id) die("should pass a table name and id");
    $sql="UPDATE $table SET ";
    $i = 0;
    while (list($key, $val) = each($field_pair_arry)) {
       if($i++) $sql .= ', ';
       if($val == "now()"){
         $field_type = $this->get_field_type($table, $key);
         if( $field_type == "date" or $field_type == "datetime" or $field_type == "timestamp" or $field_type == "time"){
            $sql .= "$key=$val";
            continue;
         }
       }
       $sql .= "$key='$val'";
    }
    $sql .= " WHERE id='$id'";
    //echo $sql;
    return $this->update($sql);
  }
  function close(){
    @mysqli_close($this->link);
  }
  
  function get_field_type($table, $field_name){
    if(!$table or !$field_name) die("table and file name should be passed");
    $field_types = array();
    $result = $this->execute("SHOW FIELDS FROM $table");
    while ($row = mysqli_fetch_array($result)) {
      if($row['Field'] == $field_name){
        return $row['Type'];
      }
    }
    return "";
  }
  /*---------------------------------
  mysql_list_tables()
  mysql_tablename()
  ----------------------------------*/
  function list_tables($db_name=''){
    if(!$db_name) $db_name = $this->selected_db_name;
    if(!$db_name) die("Database name should be passed");
    $tables_name = array();
    $result = $this->execute("SHOW TABLES FROM $db_name");    
    while ($row = mysqli_fetch_row($result)){
      array_push($tables_name, $row[0]);    
    }
    return $tables_name;
  } 
  function error_handle($msg = ''){
    if(mysqli_errno($this->link) ==  "1062"){
      return false;
    }else{
      echo $msg;
      echo "<br><b>error #</b>" . mysqli_errno($this->link);
      echo "<br><b>mysql error</b>: " . mysqli_error($this->link);
      echo "<br><b>Script Name:</b> " . $_SERVER['PHP_SELF'];
     
      exit;
    }  
  }
  function affected_rows(){
    //INSERT, UPDATE, REPLACE or DELETE
    return mysqli_affected_rows($this->link);
  }
}//end of class
?>
