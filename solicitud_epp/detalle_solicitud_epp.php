<?php
include 'db.php';

if(!isset($_GET['id'])) exit;

$id = (int)$_GET['id'];


// ===== CABECERA =====
$stmt = $conn->prepare("
    SELECT rut, nombre, fecha
    FROM solicitud
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$base = $stmt->get_result()->fetch_assoc();

if(!$base){
    echo "Solicitud no encontrada";
    exit;
}

$rut    = $base['rut'];
$nombre = $base['nombre'];
$fecha  = $base['fecha'];


// ===== DETALLE (AQUÍ ESTABA EL ERROR) =====
$stmt2 = $conn->prepare("
    SELECT epp, cantidad
    FROM solicitud_detalle
    WHERE solicitud_id = ?
");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$res = $stmt2->get_result();

$fechaFormateada = date('d-m-Y', strtotime($fecha));
?>

<div style="display:flex; flex-direction:column; gap:10px;">

   

    <div style="margin-top:10px;">
        <strong>EPP Entregados:</strong>
    </div>

    <?php while($r = $res->fetch_assoc()): ?>

        <div style="
            background:#f8fafc;
            padding:10px;
            border-radius:12px;
        ">
            <?= htmlspecialchars($r['epp']) ?>
            — Cantidad: <?= $r['cantidad'] ?>
        </div>

    <?php endwhile; ?>

</div>
