<?php
include 'db.php';

$rut = $_POST['rut'];
$mes = $_POST['mes'];
$anio = $_POST['anio'];
$semana = $_POST['semana'];
$estado = $_POST['estado'];

$res = $conn->query("SELECT nombre FROM conductores WHERE rut='$rut'");
$nombre = $res->fetch_assoc()['nombre'];

/* ✅ SI ES DESVINCULADO → ACTUALIZA CONDUCTOR */
if($estado == 'desvinculado'){
    
    $conn->query("
        UPDATE conductores 
        SET estado='desvinculado'
        WHERE rut='$rut'
    ");

}else{

    /* ✅ SI CAMBIAN A OTRO ESTADO → REACTIVA */
    $conn->query("
        UPDATE conductores 
        SET estado='activo'
        WHERE rut='$rut'
    ");
}

/* ✅ GUARDA ENTREGA NORMAL */
$conn->query("
INSERT INTO entregas (rut,nombre,mes,anio,semana,estado)
VALUES ('$rut','$nombre',$mes,$anio,$semana,'$estado')
ON DUPLICATE KEY UPDATE estado='$estado'
");

$busqueda = $_POST['busqueda'] ?? '';
$soloPendientes = $_POST['soloPendientes'] ?? '';

header("Location: index.php?mes=$mes&anio=$anio"
  . ($busqueda ? "&busqueda=" . urlencode($busqueda) : '')
  . ($soloPendientes ? "&soloPendientes=1" : '')
);
exit;

