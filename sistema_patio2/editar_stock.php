<?php
include 'db.php';

$id = $_POST['id'] ?? null;
$cantidad = $_POST['cantidad'] ?? null;

if(!$id || $cantidad === null || !is_numeric($cantidad)){
  header("Location: inventario.php");
  exit;
}

$cantidad = (int)$cantidad;

$conn->query("
  UPDATE inventario 
  SET cantidad = $cantidad 
  WHERE id = $id
");

header("Location: inventario.php");
exit;
