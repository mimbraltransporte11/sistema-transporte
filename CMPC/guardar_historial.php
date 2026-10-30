<?php

include "conexion.php";


if(isset($_POST['patente'])){


$patente = $_POST['patente'];
$tipo = $_POST['tipo'];
$configuracion = $_POST['configuracion'];
$sub_configuracion = $_POST['sub_configuracion'];

$detalle = $_POST['detalle'];
$fecha = $_POST['fecha'];
$usuario = $_POST['usuario'];



/* ==========================
   SUBIR ARCHIVO
========================== */


$archivo="";


if(isset($_FILES['imagen']) && $_FILES['imagen']['name']!=""){


    $nombreOriginal = $_FILES['imagen']['name'];

    $tmp = $_FILES['imagen']['tmp_name'];


    /*
       Obtener extensión
    */

    $extension = pathinfo(
        $nombreOriginal,
        PATHINFO_EXTENSION
    );


    /*
       Nuevo nombre seguro
    */

    $nuevoNombre = uniqid("doc_")
    ."."
    .strtolower($extension);



    $ruta = __DIR__ . "/imagenes/" . $nuevoNombre;



    if(move_uploaded_file($tmp,$ruta)){


    $archivo=$nuevoNombre;


}else{


    echo "Error al subir archivo";
    echo "<br>";
    echo "Ruta:";
    echo $ruta;
    exit;


}


}





/* ==========================
   INSERTAR HISTORIAL
========================== */


$sql="

INSERT INTO historial

(
patente,
tipo,
configuracion,
sub_configuracion,
detalle,
fecha,
imagen,
usuario,
fecha_registro
)

VALUES

(
?,
?,
?,
?,
?,
?,
?,
?,
NOW()
)

";



$stmt=$conn->prepare($sql);



$stmt->bind_param(

"ssssssss",

$patente,
$tipo,
$configuracion,
$sub_configuracion,
$detalle,
$fecha,
$archivo,
$usuario

);



$stmt->execute();



$stmt->close();


}




header(
"Location: historial.php?patente=".$patente
);


exit;


?>