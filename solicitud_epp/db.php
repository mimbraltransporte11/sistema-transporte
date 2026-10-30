<?php
$host="localhost";
$user="root";
$pass="";
$db="solicitud_epp";
$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error){ die("Error BD"); }
?>