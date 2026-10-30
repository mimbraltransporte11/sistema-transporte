<?php
include 'db.php';

$equipo = $conn->real_escape_string($_GET['equipo']);

$result = $conn->query("SELECT cantidad FROM inventario WHERE equipo='$equipo'");
$row = $result->fetch_assoc();

echo json_encode([
    "stock" => (int)$row['cantidad']
]);
