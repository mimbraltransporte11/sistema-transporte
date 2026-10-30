<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  die('Acceso no permitido');
}

/* ===== DATOS FACTURA ===== */
$proveedor   = trim($_POST['proveedor_nombre'] ?? '');
$rut         = trim($_POST['proveedor_rut'] ?? '');
$num_factura = trim($_POST['num_factura'] ?? '');
$fecha       = date('Y-m-d');

/* ===== ARRAYS ===== */
$epp      = $_POST['epp'] ?? [];
$cantidad = $_POST['cantidad'] ?? [];
$precio   = $_POST['precio'] ?? [];

/* ===== VALIDACIÓN ===== */
if (!$proveedor || !$rut || !$num_factura || empty($epp)) {
  die('❌ Datos incompletos');
}

/* ===== VALIDAR DUPLICADO (MISMO PROVEEDOR) ===== */
$dup = $conn->prepare("
  SELECT id 
  FROM facturas_epp 
  WHERE proveedor_rut = ? 
    AND num_factura = ?
");
$dup->bind_param("ss", $rut, $num_factura);
$dup->execute();
$dup->store_result();

if ($dup->num_rows > 0) {
  header("Location: facturas_epp.php?error=duplicada&num=$num_factura");
  exit;
}
$dup->close();

/* ===== SUBIR IMAGEN ===== */
$imagen = null;

if (!empty($_FILES['imagen']['name'])) {
  $dir = "uploads/facturas/";
  if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
  }

  $nombre = time() . "_" . basename($_FILES['imagen']['name']);
  $ruta = $dir . $nombre;

  if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {
    $imagen = $ruta; // ← ruta web
  }
}

/* ===== CALCULAR TOTAL ===== */
$total = 0;
for ($i = 0; $i < count($epp); $i++) {
  $total += ((int)$cantidad[$i]) * ((int)$precio[$i]);
}

/* ===== TRANSACCIÓN ===== */
$conn->begin_transaction();

try {

  /* ===== INSERT FACTURA ===== */
  $fact = $conn->prepare("
    INSERT INTO facturas_epp
    (proveedor_nombre, proveedor_rut, num_factura, fecha, total, imagen)
    VALUES (?,?,?,?,?,?)
  ");
  $fact->bind_param(
    "ssssds",
    $proveedor,
    $rut,
    $num_factura,
    $fecha,
    $total,
    $imagen
  );
  $fact->execute();

  $factura_id = $conn->insert_id;

  /* ===== DETALLE ===== */
  $det = $conn->prepare("
    INSERT INTO facturas_epp_detalle
    (factura_id, epp, cantidad, precio, subtotal)
    VALUES (?,?,?,?,?)
  ");

  /* ===== STOCK ===== */
  $stock = $conn->prepare("
    UPDATE elementos_epp
    SET cantidad = cantidad + ?
    WHERE epp = ?
  ");

  for ($i = 0; $i < count($epp); $i++) {

    if (!$epp[$i] || !$cantidad[$i] || !$precio[$i]) continue;

    $sub = ((int)$cantidad[$i]) * ((int)$precio[$i]);

    $det->bind_param(
      "isidd",
      $factura_id,
      $epp[$i],
      $cantidad[$i],
      $precio[$i],
      $sub
    );
    $det->execute();

    $stock->bind_param("is", $cantidad[$i], $epp[$i]);
    $stock->execute();
  }

  $conn->commit();
  header("Location: facturas_epp.php?ok=1");
  exit;

} catch (Exception $e) {
  $conn->rollback();
  die("❌ Error al guardar factura");
}
