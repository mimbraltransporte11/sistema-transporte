<?php

include 'db.php';

if (!isset($_POST['patente'])) {
    die('Patente no recibida');
}

/* ==========================
   PREPARAR INSERT
========================== */

$stmt = $conn->prepare("
    INSERT INTO vehiculos (
        patente,
        tipo,
        marca,
        modelo,
        numero_chasis,
        color,
        hp,
        anio,
        propietario,
        fecha_permiso,
        valor_permiso,
        municipalidad,
        fecha_soap,
        valor_soap,
        aseguradora,
        fecha_revision_tecnica,
        valor_revision_tecnica,
        configuracion,
        sub_configuracion
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

/* ==========================
   BIND PARAM
========================== */

$stmt->bind_param(
    "ssssssiississississ",
    $_POST['patente'],
    $_POST['tipo'],
    $_POST['marca'],
    $_POST['modelo'],
    $_POST['numero_chasis'],
    $_POST['color'],
    $_POST['hp'],
    $_POST['anio'],
    $_POST['propietario'],
    $_POST['fecha_permiso'],
    $_POST['valor_permiso'],
    $_POST['municipalidad'],
    $_POST['fecha_soap'],
    $_POST['valor_soap'],
    $_POST['aseguradora'],
    $_POST['fecha_revision_tecnica'],
    $_POST['valor_revision_tecnica'],
    $_POST['configuracion'],
    $_POST['sub_configuracion']
);

/* ==========================
   EJECUTAR
========================== */

if (!$stmt->execute()) {
    die("Error al guardar: " . $stmt->error);
}

$stmt->close();
$conn->close();

header("Location: index.php");
exit;
?>