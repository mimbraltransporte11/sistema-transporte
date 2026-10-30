<?php
?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>Importar Equipos</title>


<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">


<style>


body{

margin:0;

font-family:Inter;

background:
linear-gradient(135deg,#0f172a,#2563eb);

padding:30px;

}


.card{

max-width:600px;

margin:auto;

background:white;

padding:35px;

border-radius:20px;

box-shadow:0 10px 30px #0005;

}



h1{

color:#2563eb;

}



input{

width:100%;

padding:15px;

margin:15px 0;

border-radius:10px;

border:1px solid #cbd5e1;

}



button{

background:#2563eb;

color:white;

border:0;

padding:15px 25px;

border-radius:12px;

cursor:pointer;

}


a{

color:white;

text-decoration:none;

}


</style>


</head>


<body>



<a href="index.php">
⬅ Volver
</a>



<div class="card">


<h1>
📥 Importar Equipos Flota
</h1>



<p>

Seleccione archivo CSV exportado desde la flota antigua.

</p>



<form 
method="POST"
action="procesar_importacion.php"
enctype="multipart/form-data">


<input 
type="file"
name="archivo"
accept=".csv"
required>


<button>

Importar

</button>



</form>


</div>


</body>

</html>