<?php

/* =========================================================
   BUSCAR CONDUCTOR
   Módulo: Incidentes

   Base de datos:
   errores

   Tabla:
   conductores

   Campos:
   id
   rut
   nombre
   area
========================================================= */

header('Content-Type: text/html; charset=UTF-8');

date_default_timezone_set('America/Santiago');


/* =========================================================
   CONEXIÓN
========================================================= */

require_once __DIR__ . '/../errores/db.php';


/* =========================================================
   DETECTAR CONEXIÓN MYSQLI
========================================================= */

$db = null;


/*
 * La conexión de errores/db.php puede utilizar
 * distintos nombres según tu sistema.
 */

if (isset($conn) && $conn instanceof mysqli) {

    $db = $conn;

} elseif (isset($conexion) && $conexion instanceof mysqli) {

    $db = $conexion;

} elseif (isset($mysqli) && $mysqli instanceof mysqli) {

    $db = $mysqli;

} elseif (isset($link) && $link instanceof mysqli) {

    $db = $link;
}


/* =========================================================
   VALIDAR CONEXIÓN
========================================================= */

if (!$db) {

    http_response_code(500);

    echo '<div class="sin-resultados">
            No fue posible conectar con la base de datos.
          </div>';

    exit;
}


$db->set_charset('utf8mb4');


/* =========================================================
   RECIBIR BÚSQUEDA
========================================================= */

$q = trim(
    $_GET['q'] ?? ''
);


/* =========================================================
   VALIDAR BÚSQUEDA
========================================================= */

if ($q === '') {

    echo '<div class="sin-resultados">
            Escribe el nombre o RUT del conductor.
          </div>';

    exit;
}


if (mb_strlen($q, 'UTF-8') < 2) {

    echo '<div class="sin-resultados">
            Escribe al menos 2 caracteres.
          </div>';

    exit;
}


/* =========================================================
   CONSULTA
========================================================= */

$sql = "
    SELECT
        id,
        rut,
        nombre,
        area
    FROM conductores
    WHERE
        nombre LIKE ?
        OR rut LIKE ?
    ORDER BY
        nombre ASC
    LIMIT 20
";


$stmt = $db->prepare($sql);


/* =========================================================
   VALIDAR CONSULTA
========================================================= */

if (!$stmt) {

    http_response_code(500);

    echo '<div class="sin-resultados">
            No fue posible preparar la búsqueda.
          </div>';

    exit;
}


/* =========================================================
   PARÁMETROS
========================================================= */

$buscar = '%' . $q . '%';


$stmt->bind_param(
    'ss',
    $buscar,
    $buscar
);


/* =========================================================
   EJECUTAR
========================================================= */

if (!$stmt->execute()) {

    http_response_code(500);

    echo '<div class="sin-resultados">
            No fue posible realizar la búsqueda.
          </div>';

    $stmt->close();

    exit;
}


/* =========================================================
   RESULTADOS
========================================================= */

$resultado = $stmt->get_result();


/* =========================================================
   SIN RESULTADOS
========================================================= */

if (!$resultado || $resultado->num_rows === 0) {

    $busquedaHtml = htmlspecialchars(
        $q,
        ENT_QUOTES,
        'UTF-8'
    );

    echo '<div class="sin-resultados">
            <strong>No se encontraron conductores.</strong>
            <br>
            <small>
                Búsqueda: ' . $busquedaHtml . '
            </small>
          </div>';

    $stmt->close();

    exit;
}


/* =========================================================
   MOSTRAR CONDUCTORES
========================================================= */

while ($conductor = $resultado->fetch_assoc()) {


    /* -----------------------------------------------------
       DATOS
    ----------------------------------------------------- */

    $id = (int)(
        $conductor['id'] ?? 0
    );


    $rut = trim(
        $conductor['rut'] ?? ''
    );


    $nombre = trim(
        $conductor['nombre'] ?? ''
    );


    $area = trim(
        $conductor['area'] ?? ''
    );


    /* -----------------------------------------------------
       ESCAPAR HTML
    ----------------------------------------------------- */

    $rutHtml = htmlspecialchars(
        $rut,
        ENT_QUOTES,
        'UTF-8'
    );


    $nombreHtml = htmlspecialchars(
        $nombre,
        ENT_QUOTES,
        'UTF-8'
    );


    $areaHtml = htmlspecialchars(
        $area,
        ENT_QUOTES,
        'UTF-8'
    );


    /* =====================================================
       ITEM CONDUCTOR
    ===================================================== */

    echo '<div
        class="item-conductor"
        data-id="' . $id . '"
        data-nombre="' . $nombreHtml . '"
        data-rut="' . $rutHtml . '"
    >';


    /* -----------------------------------------------------
       ICONO
    ----------------------------------------------------- */

    echo '<div class="conductor-icono">
            <i class="fa-solid fa-user"></i>
          </div>';


    /* -----------------------------------------------------
       INFORMACIÓN
    ----------------------------------------------------- */

    echo '<div class="conductor-info">';


    echo '<div class="nombre-conductor">
            ' . $nombreHtml . '
          </div>';


    echo '<div class="datos-conductor">';


    if ($rut !== '') {

        echo '<span>
                <i class="fa-regular fa-id-card"></i>
                ' . $rutHtml . '
              </span>';
    }


    if ($area !== '') {

        echo '<span>
                <i class="fa-solid fa-building"></i>
                ' . $areaHtml . '
              </span>';
    }


    echo '</div>';


    echo '</div>';


    echo '</div>';
}


/* =========================================================
   CERRAR
========================================================= */

$stmt->close();

?>