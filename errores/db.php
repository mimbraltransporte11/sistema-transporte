<?php
$host = "localhost";
$user = "root";          // usuario por defecto en phpMyAdmin
$pass = "";              // contraseña (normalmente vacía en local)
$db   = "errores";  // nombre de la base de datos

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Forzar UTF-8
mysqli_set_charset($conn, "utf8mb4");
?>
