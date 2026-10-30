<?php
include 'db.php';

$epp = $_POST['epp'];
$cantidad = intval($_POST['cantidad']);

$q = $conn->query("SELECT * FROM elementos_epp WHERE epp='$epp'");

if($q->num_rows>0){
  $conn->query("UPDATE elementos_epp SET cantidad = cantidad + $cantidad WHERE epp='$epp'");
}else{
  $conn->query("INSERT INTO elementos_epp (epp,cantidad) VALUES ('$epp',$cantidad)");
}

header("Location: epp.php");
