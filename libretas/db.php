<?php
$host = "localhost";
$user = "root";          // tu usuario MySQL
$pass = "";              // tu contraseña MySQL
$db   = "libretas";      // base de datos LIBRETAS

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
