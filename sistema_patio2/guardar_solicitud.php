<?php
include 'db.php';

$fecha = $_POST['fecha'];
$nombre = $_POST['nombre'];
$area = $_POST['area'];
$accion = $_POST['accion'];
$observacion = $_POST['observacion'];

$equipos = $_POST['equipo'];
$cantidades = $_POST['cantidad'];

$conn->begin_transaction();

try {

    /* ✅ INSERT CABECERA */
    $conn->query("
        INSERT INTO solicitud (fecha, nombre, area, accion, observacion)
        VALUES ('$fecha','$nombre','$area','$accion','$observacion')
    ");

    $solicitud_id = $conn->insert_id;

    for($i = 0; $i < count($equipos); $i++){

        $equipo = $conn->real_escape_string($equipos[$i]);
        $cantidad = (int)$cantidades[$i];

        if($equipo && $cantidad > 0){

            /* ✅ VALIDAR STOCK SOLO SALIDA */
            if($accion === 'salida'){

                $result = $conn->query("
                    SELECT cantidad 
                    FROM inventario 
                    WHERE equipo = '$equipo'
                ");

                if(!$result || $result->num_rows === 0){
                    throw new Exception("Equipo no encontrado: $equipo");
                }

                $row = $result->fetch_assoc();

                if($row['cantidad'] < $cantidad){
                    throw new Exception("Stock insuficiente para $equipo");
                }
            }

            /* ✅ INSERT DETALLE */
            $conn->query("
                INSERT INTO solicitud_detalle (solicitud_id, equipo, cantidad)
                VALUES ($solicitud_id, '$equipo', $cantidad)
            ");

            /* ✅ ACTUALIZAR INVENTARIO */
            if($accion === 'salida'){

                $conn->query("
                    UPDATE inventario 
                    SET cantidad = cantidad - $cantidad
                    WHERE equipo = '$equipo'
                ");

            } else {

                $conn->query("
                    UPDATE inventario 
                    SET cantidad = cantidad + $cantidad
                    WHERE equipo = '$equipo'
                ");
            }
        }
    }

    $conn->commit();
    header("Location: index.php");
    exit;

} catch (Exception $e) {

    $conn->rollback();
    die("Error: " . $e->getMessage());
}