<?php
$host="localhost";
$user="root";
$pass="";
$db="sistema_patio2";
$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error){ die("Error BD"); }
?>