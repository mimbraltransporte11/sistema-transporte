<?php

/* =========================================================
   CONEXIÓN BASE DE DATOS
   MÓDULO: INCIDENTES
   BASE DE DATOS: errores
========================================================= */

$host = "localhost";
$usuario = "root";
$password = "";
$base_datos = "errores";

$conn = new mysqli(
    $host,
    $usuario,
    $password,
    $base_datos
);

/* =========================================================
   VALIDAR CONEXIÓN
========================================================= */

if ($conn->connect_error) {

    die(
        "Error de conexión con la base de datos: "
        . $conn->connect_error
    );

}

/* =========================================================
   UTF-8
========================================================= */

$conn->set_charset("utf8mb4");

?>