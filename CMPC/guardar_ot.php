<?php

include "conexion.php";


/* =========================================================
   VALIDAR MÉTODO
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");
    exit;

}


/* =========================================================
   DATOS OT
========================================================= */

$numero_ot = (int)($_POST['numero_ot'] ?? 0);

$patente = strtoupper(
    trim($_POST['patente'] ?? '')
);

$conductor_id = !empty($_POST['conductor_id'])
    ? (int)$_POST['conductor_id']
    : null;

$fecha_ingreso = trim(
    $_POST['fecha_ingreso'] ?? ''
);

$motivo_ingreso = trim(
    $_POST['motivo_ingreso'] ?? ''
);

$odometro = (
    isset($_POST['odometro']) &&
    $_POST['odometro'] !== ''
)
    ? (int)$_POST['odometro']
    : null;

$estado = trim(
    $_POST['estado'] ?? ''
);

if ($estado === '') {
    $estado = 'ABIERTA';
}


/* =========================================================
   MAESTROS
========================================================= */

$maestros = $_POST['maestros'] ?? [];


/* =========================================================
   USUARIO
========================================================= */

$usuario_registro = trim(
    $_POST['usuario_registro'] ?? ''
);


/* =========================================================
   VALIDACIONES BÁSICAS
========================================================= */

if ($numero_ot <= 0) {

    die("Número de OT inválido.");

}


if ($patente === '') {

    die("No se recibió la patente del equipo.");

}


if ($fecha_ingreso === '') {

    die("Debe existir una fecha de ingreso.");

}


/* =========================================================
   VALIDAR DOCUMENTO
========================================================= */

if (
    !isset($_FILES['documento_ot']) ||
    $_FILES['documento_ot']['error'] !== UPLOAD_ERR_OK
) {

    die(
        "Debe adjuntar el documento o SCAN correspondiente a la OT."
    );

}

$archivo = $_FILES['documento_ot'];


/* =========================================================
   VALIDAR TAMAÑO
   MÁXIMO 10 MB
========================================================= */

$maximo_bytes = 10 * 1024 * 1024;

if ($archivo['size'] > $maximo_bytes) {

    die(
        "El documento es demasiado grande. " .
        "El tamaño máximo permitido es de 10 MB."
    );

}


/* =========================================================
   EXTENSIONES PERMITIDAS
========================================================= */

$extensiones_permitidas = [
    'pdf',
    'jpg',
    'jpeg',
    'png'
];

$nombre_original = $archivo['name'];

$extension = strtolower(
    pathinfo(
        $nombre_original,
        PATHINFO_EXTENSION
    )
);

if (
    !in_array(
        $extension,
        $extensiones_permitidas,
        true
    )
) {

    die(
        "Formato no permitido. " .
        "Solo se aceptan PDF, JPG, JPEG y PNG."
    );

}


/* =========================================================
   VALIDAR MIME REAL
========================================================= */

$finfo = new finfo(FILEINFO_MIME_TYPE);

$mime = $finfo->file(
    $archivo['tmp_name']
);

$mimes_permitidos = [

    'pdf' => [
        'application/pdf'
    ],

    'jpg' => [
        'image/jpeg'
    ],

    'jpeg' => [
        'image/jpeg'
    ],

    'png' => [
        'image/png'
    ]

];

if (
    !isset($mimes_permitidos[$extension]) ||
    !in_array(
        $mime,
        $mimes_permitidos[$extension],
        true
    )
) {

    die(
        "El tipo real del archivo no coincide " .
        "con su extensión."
    );

}


/* =========================================================
   CARPETA DOCUMENTOS OT
========================================================= */

$carpeta = __DIR__ .
    DIRECTORY_SEPARATOR .
    'documentos_ot';


if (!is_dir($carpeta)) {

    if (
        !mkdir(
            $carpeta,
            0755,
            true
        )
    ) {

        die(
            "No fue posible crear la carpeta documentos_ot."
        );

    }

}


/* =========================================================
   GENERAR NOMBRE ÚNICO
========================================================= */

try {

    $token = bin2hex(
        random_bytes(4)
    );

} catch (Exception $e) {

    $token = uniqid();

}


$nombre_archivo =
    'OT_' .
    str_pad(
        $numero_ot,
        6,
        '0',
        STR_PAD_LEFT
    ) .
    '_' .
    date('Ymd_His') .
    '_' .
    $token .
    '.' .
    $extension;


$ruta_archivo =
    $carpeta .
    DIRECTORY_SEPARATOR .
    $nombre_archivo;


/* =========================================================
   MOVER ARCHIVO
========================================================= */

if (
    !move_uploaded_file(
        $archivo['tmp_name'],
        $ruta_archivo
    )
) {

    die(
        "No fue posible guardar el documento de la OT."
    );

}


/* =========================================================
   TRANSACCIÓN
========================================================= */

$conn->begin_transaction();


try {


    /* =====================================================
       INSERTAR ORDEN DE TRABAJO
    ===================================================== */

    $stmt = $conn->prepare("

        INSERT INTO ordenes_trabajo
        (
            numero_ot,
            patente,
            conductor_id,
            documento_ot,
            fecha_ingreso,
            motivo_ingreso,
            odometro,
            estado,
            usuario_registro
        )

        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )

    ");


    if (!$stmt) {

        throw new Exception(
            "Error preparando la creación de la OT: " .
            $conn->error
        );

    }


    /* =====================================================
       GUARDAR OT
       
       i = numero_ot
       s = patente
       i = conductor_id
       s = documento_ot
       s = fecha_ingreso
       s = motivo_ingreso
       i = odometro
       s = estado
       s = usuario_registro
    ===================================================== */

    $stmt->bind_param(
        "isisssiss",
        $numero_ot,
        $patente,
        $conductor_id,
        $nombre_archivo,
        $fecha_ingreso,
        $motivo_ingreso,
        $odometro,
        $estado,
        $usuario_registro
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Error al crear la OT: " .
            $stmt->error
        );

    }


    /* =====================================================
       OBTENER ID DE LA OT
    ===================================================== */

    $orden_trabajo_id = $conn->insert_id;


    /* =====================================================
       INSERTAR MAESTROS
    ===================================================== */

    if (
        is_array($maestros) &&
        !empty($maestros)
    ) {


        $stmtMaestro = $conn->prepare("

            INSERT INTO ordenes_trabajo_maestros
            (
                orden_trabajo_id,
                maestro
            )

            VALUES
            (
                ?,
                ?
            )

        ");


        if (!$stmtMaestro) {

            throw new Exception(
                "Error preparando maestros: " .
                $conn->error
            );

        }


        foreach (
            $maestros as $maestro
        ) {


            $maestro = trim(
                $maestro
            );


            /* =============================================
               IGNORAR CAMPOS VACÍOS
            ============================================= */

            if ($maestro === '') {
                continue;
            }


            $stmtMaestro->bind_param(
                "is",
                $orden_trabajo_id,
                $maestro
            );


            if (
                !$stmtMaestro->execute()
            ) {

                throw new Exception(
                    "Error al guardar maestro: " .
                    $stmtMaestro->error
                );

            }

        }


        $stmtMaestro->close();

    }


    /* =====================================================
       CERRAR STATEMENT OT
    ===================================================== */

    $stmt->close();


    /* =====================================================
       CONFIRMAR TRANSACCIÓN
    ===================================================== */

    $conn->commit();


    /* =====================================================
       VOLVER AL HISTORIAL DEL EQUIPO
    ===================================================== */

    header(
        "Location: historial.php?patente=" .
        urlencode($patente)
    );

    exit;


} catch (Exception $e) {


    /* =====================================================
       DESHACER TRANSACCIÓN
    ===================================================== */

    $conn->rollback();


    /* =====================================================
       ELIMINAR ARCHIVO SI LA BD FALLÓ
    ===================================================== */

    if (
        file_exists($ruta_archivo)
    ) {

        @unlink(
            $ruta_archivo
        );

    }


    /* =====================================================
       MOSTRAR ERROR
    ===================================================== */

    die(
        "Error al guardar la Orden de Trabajo:<br><br>" .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );

}