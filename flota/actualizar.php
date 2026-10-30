<?php

include 'db.php';

$stmt = $conn->prepare("
    UPDATE vehiculos SET

        tipo=?,
        marca=?,
        modelo=?,
        numero_chasis=?,
        color=?,
        hp=?,
        anio=?,
        propietario=?,

        fecha_permiso=?,
        valor_permiso=?,
        municipalidad=?,

        fecha_soap=?,
        valor_soap=?,
        aseguradora=?,

        fecha_revision_tecnica=?,
        valor_revision_tecnica=?,

        configuracion=?,
        sub_configuracion=?

    WHERE patente=?
");

if (!$stmt) {
    die("Error al preparar la actualización: " . $conn->error);
}

$stmt->bind_param(
    "sssssssssssssssssss",

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
    $_POST['sub_configuracion'],

    $_POST['patente']
);

if (!$stmt->execute()) {
    die("Error al actualizar: " . $stmt->error);
}

$stmt->close();
$conn->close();

header("Location: index.php");
exit;
?>