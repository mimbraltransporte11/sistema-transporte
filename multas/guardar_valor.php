<?php
include 'db.php';

$id = intval($_POST['id']);
$valor = intval($_POST['valor']);

$res = $conn->query("SELECT valor FROM partes WHERE id=$id");
$row = $res->fetch_assoc();

if($row['valor'] > 0){
    echo "bloqueado";
    exit;
}

$stmt = $conn->prepare("UPDATE partes SET valor=? WHERE id=?");
$stmt->bind_param("ii", $valor, $id);

if($stmt->execute()){
    echo "ok";
}else{
    echo "error";
}