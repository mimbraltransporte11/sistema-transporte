<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "antiguedad";

$conn = new mysqli($host,$user,$pass,$db);

if($conn->connect_error){
die("Error de conexión");
}

$conn->set_charset("utf8");
?>