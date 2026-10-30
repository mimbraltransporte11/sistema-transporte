<?php
include 'db.php';

if (!isset($_POST['patente'])) {
  die('Patente no recibida');
}

$patente = $_POST['patente'];

$stmt = $conn->prepare("DELETE FROM vehiculos WHERE patente = ?");
$stmt->bind_param("s", $patente);
$stmt->execute();

header("Location: index.php");
exit;
