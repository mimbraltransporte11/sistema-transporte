<?php
include 'db.php';

if(!isset($_GET['id'])) exit;

$id = (int)$_GET['id'];

$conn->begin_transaction();

try{

    /* ===== OBTENER DETALLE ===== */
    $stmt = $conn->prepare("
        SELECT epp, cantidad
        FROM solicitud_detalle
        WHERE solicitud_id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    /* ===== RESTAURAR STOCK ===== */
    while($row = $res->fetch_assoc()){

        $stmtStock = $conn->prepare("
            UPDATE elementos_epp
            SET cantidad = cantidad + ?
            WHERE epp = ?
        ");
        $stmtStock->bind_param("is", $row['cantidad'], $row['epp']);
        $stmtStock->execute();
    }

    /* ===== ELIMINAR DETALLE ===== */
    $stmtDelDetalle = $conn->prepare("
        DELETE FROM solicitud_detalle
        WHERE solicitud_id = ?
    ");
    $stmtDelDetalle->bind_param("i", $id);
    $stmtDelDetalle->execute();

    /* ===== ELIMINAR CABECERA ===== */
    $stmtDel = $conn->prepare("
        DELETE FROM solicitud
        WHERE id = ?
    ");
    $stmtDel->bind_param("i", $id);
    $stmtDel->execute();

    $conn->commit();

}catch(Exception $e){

    $conn->rollback();
    die("Error al eliminar solicitud");
}

header("Location: index.php");
exit;
