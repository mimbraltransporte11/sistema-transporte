<?php

include "conexion.php";


$patente=$_GET['patente'] ?? '';


if($patente==""){
    header("Location:index.php");
    exit;
}



$stmt=$conn->prepare("
SELECT *
FROM equipos
WHERE patente=?
");


$stmt->bind_param(
"s",
$patente
);


$stmt->execute();


$equipo=$stmt->get_result()->fetch_assoc();



if(!$equipo){

    echo "Equipo no encontrado";
    exit;

}



?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>Editar Equipo <?=$patente?></title>


<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">


<style>


body{

margin:0;

font-family:Inter;

background:
linear-gradient(135deg,#0f172a,#2563eb);

padding:30px;

}



.contenedor{

max-width:900px;

margin:auto;

background:white;

padding:35px;

border-radius:20px;

box-shadow:0 10px 30px #0005;

}



h1{

color:#2563eb;

}



.formulario{

display:grid;

grid-template-columns:repeat(2,1fr);

gap:15px;

}



input{

padding:12px;

border-radius:10px;

border:1px solid #cbd5e1;

width:100%;

}



button{

grid-column:1/-1;

background:#2563eb;

color:white;

border:0;

padding:15px;

border-radius:12px;

cursor:pointer;

font-size:16px;

}



.volver{

color:white;

text-decoration:none;

display:block;

margin-bottom:20px;

}



@media(max-width:700px){

.formulario{

grid-template-columns:1fr;

}

}


</style>


</head>



<body>



<a class="volver" href="index.php">
⬅ Volver
</a>



<div class="contenedor">


<h1>
✏ Editar Equipo <?=$patente?>
</h1>



<form 
class="formulario"
method="POST"
action="actualizar_equipo.php">



<input 
type="hidden"
name="patente_original"
value="<?=$equipo['patente']?>"
>



<input 
name="patente"
value="<?=$equipo['patente']?>"
placeholder="Patente"
required
>



<input 
name="tipo"
value="<?=$equipo['tipo']?>"
placeholder="Tipo"
>



<input 
name="marca"
value="<?=$equipo['marca']?>"
placeholder="Marca"
>



<input 
name="modelo"
value="<?=$equipo['modelo']?>"
placeholder="Modelo"
>



<input 
name="numero_chasis"
value="<?=$equipo['numero_chasis']?>"
placeholder="Número Chasis"
>



<input 
name="color"
value="<?=$equipo['color']?>"
placeholder="Color"
>



<input 
name="hp"
value="<?=$equipo['hp']?>"
placeholder="HP"
>



<input 
type="number"
name="anio"
value="<?=$equipo['anio']?>"
placeholder="Año"
>



<input 
name="propietario"
value="<?=$equipo['propietario']?>"
placeholder="Propietario"
>



<input 
name="configuracion"
value="<?=$equipo['configuracion']?>"
placeholder="Configuración"
>



<input 
name="sub_configuracion"
value="<?=$equipo['sub_configuracion']?>"
placeholder="Sub Configuración"
>



<button>

💾 Actualizar Equipo

</button>



</form>



</div>



</body>

</html>