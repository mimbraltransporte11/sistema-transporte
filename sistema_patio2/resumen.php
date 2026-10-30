<?php
include 'db.php';

$nombre = $_GET['nombre'] ?? '';
$desde  = $_GET['desde']  ?? '';
$hasta  = $_GET['hasta']  ?? '';

$whereFecha = "";

/* =======================
   FILTRO POR RANGO
======================= */
if ($desde && $hasta) {
  $whereFecha = "AND s.fecha BETWEEN '$desde' AND '$hasta'";
}
elseif ($desde) {
  $whereFecha = "AND s.fecha >= '$desde'";
}
elseif ($hasta) {
  $whereFecha = "AND s.fecha <= '$hasta'";
}

/* =======================
   RESUMEN GENERAL
======================= */
$sqlGeneral = "
SELECT 
  SUM(CASE WHEN s.accion='entrada' THEN d.cantidad ELSE 0 END) AS entradas,
  SUM(CASE WHEN s.accion='salida' THEN d.cantidad ELSE 0 END) AS salidas
FROM solicitud s
JOIN solicitud_detalle d 
  ON s.id = d.solicitud_id
WHERE s.nombre='$nombre' $whereFecha
";

$general = $conn->query($sqlGeneral)->fetch_assoc();

/* =======================
   HISTORIAL AGRUPADO
======================= */
$sqlHistorial = "
SELECT 
  s.accion,
  d.equipo,
  SUM(d.cantidad) AS cantidad
FROM solicitud s
JOIN solicitud_detalle d 
  ON s.id = d.solicitud_id
WHERE s.nombre='$nombre' $whereFecha
GROUP BY s.accion, d.equipo
ORDER BY s.accion, d.equipo
";

$historial = [];
$q = $conn->query($sqlHistorial);
while ($row = $q->fetch_assoc()) {
  $historial[] = $row;
}

/* =======================
   RESPUESTA JSON
======================= */
echo json_encode([
  'general'   => $general,
  'historial' => $historial
]);