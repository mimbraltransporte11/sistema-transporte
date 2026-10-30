<?php

include "conexion.php";

header('Content-Type: application/json; charset=utf-8');

$buscar =
    isset($_GET['buscar'])
        ? trim($_GET['buscar'])
        : "";

if($buscar === ""){

    echo json_encode([]);

    exit;

}

$buscarSQL =
    "%" . $buscar . "%";


$sql = "

SELECT
    id,
    rut,
    nombre,
    area,
    estado

FROM conductores

WHERE
    (
        nombre LIKE ?
        OR rut LIKE ?
    )

    AND estado <> 'DESVINCULADO'

ORDER BY nombre ASC

LIMIT 20

";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    "ss",
    $buscarSQL,
    $buscarSQL
);


$stmt->execute();


$resultado =
    $stmt->get_result();


$conductores = [];


while(
    $fila =
    $resultado->fetch_assoc()
){

    $conductores[] = $fila;

}


$stmt->close();


echo json_encode(
    $conductores,
    JSON_UNESCAPED_UNICODE
);

exit;