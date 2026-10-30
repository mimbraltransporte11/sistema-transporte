<?php
include 'db.php';

$rut   = $_POST['rut'];
$mes   = $_POST['mes'];
$anio  = $_POST['anio'];
$estado = $_POST['estado_retiro'];
$fecha_retorno = $_POST['fecha_retorno'] ?? null;

// Solo LICENCIA y VACACIONES usan fecha de retorno
if (!in_array($estado, ['LICENCIA', 'VACACIONES'])) {
    $fecha_retorno = null;
}

// Guardar estado del mes
$conn->query("
INSERT INTO retiros (rut, mes, anio, estado, fecha_retorno)
VALUES ('$rut', $mes, $anio, '$estado', " . ($fecha_retorno ? "'$fecha_retorno'" : "NULL") . ")
ON DUPLICATE KEY UPDATE
    estado = '$estado',
    fecha_retorno = " . ($fecha_retorno ? "'$fecha_retorno'" : "NULL")
);

// Actualizar estado del conductor
if ($estado === 'DESVINCULADO') {
    $conn->query("
        UPDATE conductores
        SET estado = 'desvinculado'
        WHERE rut = '$rut'
    ");
} else {
    $conn->query("
        UPDATE conductores
        SET estado = 'activo'
        WHERE rut = '$rut'
    ");
}

header("Location: index.php?mes=$mes&anio=$anio");
exit;