<?php
include 'db.php';

$rut = trim($_GET['rut'] ?? '');

if (!$rut) {
  echo json_encode([]);
  exit;
}

$q = $conn->prepare("
  SELECT proveedor_nombre
  FROM facturas_epp
  WHERE proveedor_rut = ?
  LIMIT 1
");
$q->bind_param("s", $rut);
$q->execute();
$res = $q->get_result()->fetch_assoc();

echo json_encode($res ?: []);
