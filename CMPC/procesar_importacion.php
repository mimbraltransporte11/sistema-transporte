<?php

include "conexion.php";


if(isset($_FILES['archivo'])){


$archivo=$_FILES['archivo']['tmp_name'];



$csv=fopen($archivo,"r");



/*
 Saltar encabezado
*/

fgetcsv($csv);



while(($fila=fgetcsv($csv,1000,";"))!==FALSE){



$patente=$fila[0];
$tipo=$fila[1];
$marca=$fila[2];
$modelo=$fila[3];
$numero_chasis=$fila[4];
$color=$fila[5];
$hp=$fila[6];
$anio=$fila[7];
$propietario=$fila[8];
$configuracion=$fila[9];
$sub_configuracion=$fila[10];




/*
 Evitar duplicados
*/


$existe=$conn->prepare("
SELECT patente
FROM equipos
WHERE patente=?
");


$existe->bind_param(
"s",
$patente
);


$existe->execute();


$result=$existe->get_result();



if($result->num_rows==0){



$sql="
INSERT INTO equipos
(
patente,
tipo,
marca,
modelo,
numero_chasis,
color,
hp,
anio,
propietario,
configuracion,
sub_configuracion
)

VALUES
(?,?,?,?,?,?,?,?,?,?,?)

";



$stmt=$conn->prepare($sql);



$stmt->bind_param(

"sssssssssss",

$patente,
$tipo,
$marca,
$modelo,
$numero_chasis,
$color,
$hp,
$anio,
$propietario,
$configuracion,
$sub_configuracion

);



$stmt->execute();



$stmt->close();


}


$existe->close();



}



fclose($csv);



}



echo "

<script>

alert('Importación finalizada');

window.location='index.php';

</script>

";


?>