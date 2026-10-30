<?php
include 'db.php';

$rut      = $_POST['rut'];
$nombre   = $_POST['nombre'];
$fecha    = $_POST['fecha'];

$epps      = $_POST['epp'];        // ARRAY
$cantidades = $_POST['cantidad'];  // ARRAY

$imagen = null;

/* ===== SUBIR IMAGEN ===== */
if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0){

  $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
  $nombreArchivo = uniqid('epp_') . '.' . $ext;
  $ruta = 'uploads/epp/' . $nombreArchivo;

  if(move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)){
    $imagen = $ruta;
  }
}

$conn->begin_transaction();

try{

  /* ===== INSERTAR CABECERA ===== */
  $stmt = $conn->prepare("
    INSERT INTO solicitud (rut,nombre,fecha,imagen)
    VALUES (?,?,?,?)
  ");
  $stmt->bind_param("ssss", $rut, $nombre, $fecha, $imagen);
  $stmt->execute();

  $solicitud_id = $conn->insert_id;

  /* ===== INSERTAR DETALLE ===== */
  $detalle = $conn->prepare("
    INSERT INTO solicitud_detalle (solicitud_id,epp,cantidad)
    VALUES (?,?,?)
  ");

  $descontar = $conn->prepare("
    UPDATE elementos_epp 
    SET cantidad = cantidad - ?
    WHERE epp = ?
  ");

  foreach($epps as $i => $epp){

    $cantidad = (int)$cantidades[$i];

    if(!$epp || $cantidad <= 0) continue;

    /* VALIDAR STOCK */
    $stockQ = $conn->prepare("SELECT cantidad FROM elementos_epp WHERE epp = ?");
    $stockQ->bind_param("s", $epp);
    $stockQ->execute();
    $res = $stockQ->get_result()->fetch_assoc();

    if(!$res || $res['cantidad'] < $cantidad){
      throw new Exception($epp);

    }

    /* INSERT DETALLE */
    $detalle->bind_param("isi", $solicitud_id, $epp, $cantidad);
    $detalle->execute();

    /* DESCONTAR STOCK */
    $descontar->bind_param("is", $cantidad, $epp);
    $descontar->execute();
  }

  $conn->commit();
  header("Location: index.php?ok=1");
  exit;

}catch(Exception $e){

  $conn->rollback();
  header("Location: index.php?error=stock&epp=" . urlencode($e->getMessage()));

  exit;
}
