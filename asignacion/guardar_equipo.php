<?php
include 'db.php';

$id = intval($_POST['id']);
$tipo = $_POST['tipo'];
$config = $_POST['configuracion'];
$revision = $_POST['revision'];

$conn->query("
UPDATE equipos 
SET tipo='$tipo',
    configuracion='$config',
    revision_tecnica='$revision'
WHERE id=$id
");

echo "ok";