<?php
/*
/   Database connection details environment specific
/
*/
$db = null;
logger("connect and open");
function _dbConnectAndOpen() {
  global $db;

  $db = mysqli_connect( DB_HOST , DB_USERNAME, DB_PASSWORD, DB_SCHEMA ); 
  if($db->connect_error){
      exit("Database connection failed");
  }
  
  /* change character set to utf8 */
  if (!$db->set_charset("utf8")) {
    exit("Error loading character set utf8: {$db->error}\n");
  }
  error_log("DB is ready");
}
?>