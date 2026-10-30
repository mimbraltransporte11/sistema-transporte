<?php

$conn = new mysqli(
"localhost",
"root",
"",
"multa"
);

if($conn->connect_error){
die("Error de conexión");
}
?>