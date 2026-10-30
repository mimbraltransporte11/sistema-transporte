<?php
include 'db.php';

if(isset($_GET['q'])){

    $q = $conn->real_escape_string($_GET['q']);

    $sql = "
        SELECT rut, nombre 
        FROM solicitud 
        WHERE rut LIKE '%$q%' OR nombre LIKE '%$q%'
        GROUP BY rut, nombre
        LIMIT 10
    ";

    $res = $conn->query($sql);

    $datos = [];

    while($row = $res->fetch_assoc()){
        $datos[] = $row;
    }

    echo json_encode($datos);
}