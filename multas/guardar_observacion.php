<?php
include 'db.php';

$id = intval($_POST['id']);
$obs = trim($_POST['observacion']);

if($id && $obs != ""){

$stmt = $conn->prepare("UPDATE partes SET observacion=? WHERE id=? AND (observacion IS NULL OR observacion='')");
$stmt->bind_param("si", $obs, $id);

if($stmt->execute()){
    echo "ok";
}else{
    echo "error";
}

}