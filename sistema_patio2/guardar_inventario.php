<?php
include 'db.php';

$equipo   = $_POST['equipo'];
$cantidad = $_POST['cantidad'];

// Verificar si el equipo ya existe
$check = $conn->prepare("SELECT cantidad FROM inventario WHERE equipo = ?");
$check->bind_param("s", $equipo);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Si existe, suma la cantidad
    $row = $result->fetch_assoc();
    $nuevaCantidad = $row['cantidad'] + $cantidad;

    $update = $conn->prepare("UPDATE inventario SET cantidad = ? WHERE equipo = ?");
    $update->bind_param("is", $nuevaCantidad, $equipo);
    $update->execute();
} else {
    // Si no existe, lo inserta
    $insert = $conn->prepare("INSERT INTO inventario (equipo, cantidad) VALUES (?, ?)");
    $insert->bind_param("si", $equipo, $cantidad);
    $insert->execute();
}

// Volver a inventario
header("Location: inventario.php");
exit;
