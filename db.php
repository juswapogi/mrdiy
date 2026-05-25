<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "sql213.infinityfree.com";      // from InfinityFree
$user = "if0_42009772";           // your database username  
$pass = "HEHEHE2020";         // from InfinityFree
$db   = "if0_42009772_mysql";                // your database name

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
?>