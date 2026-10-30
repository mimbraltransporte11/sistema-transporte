<?php

include "conexion.php";


/* EQUIPOS POR TIPO */

$tipos=$conn->query("
SELECT tipo, COUNT(*) cantidad
FROM equipos
GROUP BY tipo
");


$tipo_nombre=[];
$tipo_cantidad=[];


while($t=$tipos->fetch_assoc()){

$tipo_nombre[]=$t['tipo'];
$tipo_cantidad[]=$t['cantidad'];

}



/* CONFIGURACIONES */

$config=$conn->query("
SELECT configuracion, COUNT(*) cantidad
FROM equipos
GROUP BY configuracion
");


$config_nombre=[];
$config_cantidad=[];


while($c=$config->fetch_assoc()){

$config_nombre[]=$c['configuracion'];
$config_cantidad[]=$c['cantidad'];

}


/* TOTAL */

$total=$conn->query("
SELECT COUNT(*) total
FROM equipos
")->fetch_assoc()['total'];


?>