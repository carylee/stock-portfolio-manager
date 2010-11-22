<?php
PutEnv("ORACLE_SID=CS339");
PutEnv("LD_LIBRARY_PATH=/opt/oracle/product/11.2.0/db_1/lib");
PutEnv("ORACLE_HOME=/opt/oracle/product/11.2.0/db_1");
PutEnv("ORACLE_BASE=/opt/oracle/product/11.2.0");
$dbuser = "cel294";
$dbpassword = "o66abbfd4";
$ORACLE=oci_connect($dbuser, $dbpassword);
if (!$ORACLE) {
  die("Failed to connect to Oracle database");
 }

$msqluser = "cs339";
$msqlpass = "cs339";
mysql_connect("localhost", $msqluser, $msqlpass);
mysql_select_db("StocksDaily") or die (mysql_error());
?>
