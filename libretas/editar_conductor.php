<?php
include 'db.php';

$rut = $_POST['rut'];
$area = $_POST['area'];

$conn->query("UPDATE conductores SET area='$area' WHERE rut='$rut'");

header("Location: index.php");
exit;