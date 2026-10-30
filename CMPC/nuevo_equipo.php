<?php
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Nuevo Equipo CMPC</title>


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


input,select{

padding:12px;

border-radius:10px;

border:1px solid #cbd5e1;

width:100%;

}



button{

grid-column:1/-1;

padding:15px;

background:#2563eb;

color:white;

border:0;

border-radius:12px;

font-size:16px;

cursor:pointer;

}


.volver{

text-decoration:none;

color:white;

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
🚛 Nuevo Equipo CMPC
</h1>



<form 
class="formulario"
method="POST"
action="guardar_equipo.php">



<input 
name="patente"
placeholder="Patente"
required>


<input 
name="tipo"
placeholder="Tipo"
required>



<input 
name="marca"
placeholder="Marca">



<input 
name="modelo"
placeholder="Modelo">



<input 
name="numero_chasis"
placeholder="Número Chasis">



<input 
name="color"
placeholder="Color">



<input 
name="hp"
placeholder="HP">



<input 
type="number"
name="anio"
placeholder="Año">



<input 
name="propietario"
placeholder="Propietario">



<input 
name="configuracion"
placeholder="Configuración">



<input 
name="sub_configuracion"
placeholder="Sub Configuración">



<button>

💾 Guardar Equipo

</button>


</form>



</div>


</body>

</html>