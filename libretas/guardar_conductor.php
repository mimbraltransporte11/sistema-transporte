<?php
include 'db.php';

$rut = $_POST['rut'];
$nombre = $_POST['nombre'];
$area = $_POST['area'];

$conn->query("INSERT IGNORE INTO conductores (rut,nombre,area) VALUES ('$rut','$nombre','$area')");

header("Location: index.php");
