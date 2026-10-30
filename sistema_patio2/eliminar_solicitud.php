<?php
include 'db.php';

$id = intval($_POST['id']);

$conn->begin_transaction();

try {

    /* ✅ OBTENER CABECERA */
    $q = $conn->query("SELECT accion FROM solicitud WHERE id=$id");

    if(!$q || $q->num_rows == 0){
        throw new Exception('Solicitud no encontrada');
    }

    $solicitud = $q->fetch_assoc();
    $accion = $solicitud['accion'];

    /* ✅ OBTENER DETALLE */
    $detalle = $conn->query("
        SELECT equipo, cantidad 
        FROM solicitud_detalle 
        WHERE solicitud_id = $id
    ");

    if(!$detalle){
        throw new Exception('Error al obtener detalle');
    }

    while($row = $detalle->fetch_assoc()){

        $equipo   = $conn->real_escape_string($row['equipo']);
        $cantidad = intval($row['cantidad']);

        if($accion === 'salida'){

            /* 🔁 DEVOLVER STOCK */
            $conn->query("
                UPDATE inventario 
                SET cantidad = cantidad + $cantidad 
                WHERE equipo = '$equipo'
            ");

        } else if($accion === 'entrada'){

            /* 🔁 RESTAR STOCK */
            $conn->query("
                UPDATE inventario 
                SET cantidad = cantidad - $cantidad 
                WHERE equipo = '$equipo'
            ");
        }
    }

    /* ✅ ELIMINAR DETALLE */
    $conn->query("DELETE FROM solicitud_detalle WHERE solicitud_id = $id");

    /* ✅ ELIMINAR CABECERA */
    $conn->query("DELETE FROM solicitud WHERE id = $id");

    $conn->commit();

    echo 'ok';

} catch (Exception $e){

    $conn->rollback();
    echo 'error';
}