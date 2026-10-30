```php
<?php

/* =========================================================
   ELIMINAR INCIDENTE
========================================================= */

date_default_timezone_set('America/Santiago');


/* =========================================================
   CONEXIÓN
========================================================= */

require_once __DIR__ . '/../errores/db.php';


/*
   Normalizar conexión.
*/

if (
    !isset($conn) ||
    !($conn instanceof mysqli)
) {

    if (
        isset($conexion) &&
        $conexion instanceof mysqli
    ) {

        $conn = $conexion;

    }

}


/* =========================================================
   VALIDAR CONEXIÓN
========================================================= */

if (
    !isset($conn) ||
    !($conn instanceof mysqli)
) {

    die(
        'No se pudo establecer conexión con la base de datos.'
    );

}


/* =========================================================
   OBTENER ID
========================================================= */

$id = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;


if ($id <= 0) {

    header(
        'Location: index.php?error=id_invalido'
    );

    exit;

}


/* =========================================================
   ELIMINAR FOTOGRAFÍAS ASOCIADAS
========================================================= */

$stmtFotos =
    $conn->prepare(
        'DELETE FROM incidentes_fotos WHERE incidente_id = ?'
    );


if (!$stmtFotos) {

    header(
        'Location: index.php?error=eliminar'
    );

    exit;

}


$stmtFotos->bind_param(
    'i',
    $id
);


if (!$stmtFotos->execute()) {

    $stmtFotos->close();

    header(
        'Location: index.php?error=eliminar'
    );

    exit;

}


$stmtFotos->close();


/* =========================================================
   ELIMINAR INCIDENTE
========================================================= */

$stmt =
    $conn->prepare(
        'DELETE FROM incidentes WHERE id = ?'
    );


if (!$stmt) {

    header(
        'Location: index.php?error=eliminar'
    );

    exit;

}


$stmt->bind_param(
    'i',
    $id
);


if (!$stmt->execute()) {

    $stmt->close();

    header(
        'Location: index.php?error=eliminar'
    );

    exit;

}


$stmt->close();


/* =========================================================
   REDIRECCIÓN
========================================================= */

header(
    'Location: index.php?eliminado=1'
);

exit;

?>
```
