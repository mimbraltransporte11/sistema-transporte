<?php

include 'db.php';

$q = trim($_GET['q'] ?? '');

if($q == ''){
    exit;
}

/* =========================
   NORMALIZAR
========================= */

function limpiar($texto){

    $texto = mb_strtolower($texto, 'UTF-8');

    $buscar = ['á','é','í','ó','ú','ä','ë','ï','ö','ü'];
    $reemplazar = ['a','e','i','o','u','a','e','i','o','u'];

    return str_replace($buscar,$reemplazar,$texto);

}

$q = limpiar($q);

$palabras = preg_split('/\s+/', $q);

/* =========================
   OBTENER TRABAJADORES
========================= */

$res = $conn->query("
SELECT rut,nombre
FROM trabajadores
ORDER BY nombre
LIMIT 200
");

/* =========================
   FILTRAR MANUALMENTE
========================= */

while($r = $res->fetch_assoc()){

    $nombre = limpiar($r['nombre']);
    $rut = limpiar($r['rut']);

    $coincide = true;

    foreach($palabras as $palabra){

        if(
            strpos($nombre,$palabra) === false &&
            strpos($rut,$palabra) === false
        ){
            $coincide = false;
            break;
        }

    }

    if($coincide){

        echo '
        <div class="item-resultado"
        data-rut="'.$r['rut'].'"
        data-nombre="'.$r['nombre'].'">

        <strong>'.$r['nombre'].'</strong><br>
        <small>'.$r['rut'].'</small>

        </div>
        ';

    }

}
?>