<?php

include "conexion.php";


$patente=$_GET['patente'] ?? '';



if($patente!=""){



/*
 Primero eliminamos historial asociado
 para evitar información huérfana
*/


$stmt=$conn->prepare("
DELETE FROM historial
WHERE patente=?
");


$stmt->bind_param(
"s",
$patente
);


$stmt->execute();



$stmt->close();



/*
 Eliminamos equipo
*/


$stmt=$conn->prepare("
DELETE FROM equipos
WHERE patente=?
");


$stmt->bind_param(
"s",
$patente
);


$stmt->execute();



$stmt->close();



}



header("Location:index.php");

exit;


?>