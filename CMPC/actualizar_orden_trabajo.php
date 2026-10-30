<?php

include "conexion.php";


/* =========================================================
   VALIDAR ID
========================================================= */

$id = isset($_POST['id'])
    ? intval($_POST['id'])
    : 0;


if ($id <= 0) {

    header("Location:index.php");
    exit;

}


/* =========================================================
   DATOS
========================================================= */

$fecha_ingreso =
    $_POST['fecha_ingreso'] ?? '';

$conductor_id =
    isset($_POST['conductor_id'])
    ? intval($_POST['conductor_id'])
    : 0;

$motivo_ingreso =
    trim(
        $_POST['motivo_ingreso'] ?? ''
    );

$odometro =
    ($_POST['odometro'] ?? '') !== ''
    ? intval($_POST['odometro'])
    : null;

$fecha_salida =
    ($_POST['fecha_salida'] ?? '') !== ''
    ? $_POST['fecha_salida']
    : null;

$estado =
    $_POST['estado'] ?? 'ABIERTA';

$usuario_registro =
    trim(
        $_POST['usuario_registro'] ?? ''
    );


/* =========================================================
   VALIDACIONES
========================================================= */

if (
    $fecha_ingreso === '' ||
    $conductor_id <= 0 ||
    $motivo_ingreso === ''
) {

    die("
        <h2>Error</h2>
        <p>Faltan datos obligatorios.</p>
        <a href='javascript:history.back()'>
            Volver
        </a>
    ");

}


$estadosPermitidos = [

    'ABIERTA',
    'EN PROCESO',
    'CERRADA',
    'ANULADA'

];


if (
    !in_array(
        $estado,
        $estadosPermitidos,
        true
    )
) {

    die("Estado no válido.");

}


/* =========================================================
   OBTENER PATENTE
========================================================= */

$stmt = $conn->prepare("
    SELECT patente
    FROM ordenes_trabajo
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$resultado =
    $stmt->get_result()
        ->fetch_assoc();


if (!$resultado) {

    die("Orden de Trabajo no encontrada.");

}


$patente =
    $resultado['patente'];


/* =========================================================
   ACTUALIZAR OT
========================================================= */

$stmt = $conn->prepare("
    UPDATE ordenes_trabajo
    SET
        fecha_ingreso = ?,
        conductor_id = ?,
        motivo_ingreso = ?,
        odometro = ?,
        fecha_salida = ?,
        estado = ?,
        usuario_registro = ?

    WHERE id = ?
");


$stmt->bind_param(
    "sisisssi",
    $fecha_ingreso,
    $conductor_id,
    $motivo_ingreso,
    $odometro,
    $fecha_salida,
    $estado,
    $usuario_registro,
    $id
);


if (!$stmt->execute()) {

    die(
        "Error al actualizar la OT: "
        .
        $stmt->error
    );

}


/* =========================================================
   MAESTROS
========================================================= */


/*
   Primero eliminamos los maestros actuales
   para volver a insertar los que quedaron
   en el formulario.
*/

$stmt = $conn->prepare("
    DELETE FROM ordenes_trabajo_maestros
    WHERE orden_trabajo_id = ?
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();


/* =========================================================
   INSERTAR NUEVOS MAESTROS
========================================================= */

if (
    isset($_POST['maestros']) &&
    is_array($_POST['maestros'])
) {


    $stmtMaestro = $conn->prepare("
        INSERT INTO ordenes_trabajo_maestros
        (
            orden_trabajo_id,
            maestro
        )
        VALUES
        (
            ?,
            ?
        )
    ");


    foreach (
        $_POST['maestros']
        as $maestro
    ) {


        $maestro =
            trim($maestro);


        /*
           Evitamos guardar campos
           vacíos.
        */

        if ($maestro === '') {
            continue;
        }


        $stmtMaestro->bind_param(
            "is",
            $id,
            $maestro
        );


        $stmtMaestro->execute();

    }

}


/* =========================================================
   VOLVER A DETALLE
========================================================= */

header(
    "Location: detalle_ot.php?id="
    . $id
);

exit;

?>