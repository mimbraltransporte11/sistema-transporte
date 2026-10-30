<?php
include 'db.php';

$id = intval($_POST['id']);

if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0){

    $directorio = "uploads/";
    if(!is_dir($directorio)){
        mkdir($directorio, 0777, true);
    }

    $nombreArchivo = time() . "_" . $_FILES['imagen']['name'];
    $ruta = $directorio . $nombreArchivo;

    if(move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)){

        $stmt = $conn->prepare("UPDATE solicitud SET imagen=? WHERE id=?");
        $stmt->bind_param("si", $ruta, $id);
        $stmt->execute();
    }
}

header("Location: index.php?ok=1");
exit;
