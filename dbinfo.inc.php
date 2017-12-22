<?
//include("dbinfo.inc.php");

$username="";
$password="";
$database="";

mysql_connect('sql204.getweb.cf:3306',$username,$password);
@mysql_select_db($database) or die( "Unable to select database");

// close the connection in each file or you may have problems with your MySQL server.
?>
