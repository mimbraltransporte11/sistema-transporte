<?php
$conn = new mysqli("localhost","root","","asignacion");

if($conn->connect_error){
    die("Error: " . $conn->connect_error);
}
?>