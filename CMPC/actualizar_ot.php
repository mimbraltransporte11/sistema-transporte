<?php

include "conexion.php";


/* =========================================================
   VALIDAR MÉTODO
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    header("Location:index.php");
    exit;

}


/* =========================================================
   DATOS
========================================================= */

$id =
    (int)(
        $_POST['id'] ?? 0
    );


$fecha_ingreso =
    trim(
        $_POST['fecha_ingreso'] ?? ''
    );


$fecha_salida =
    trim(
        $_POST['fecha_salida'] ?? ''
    );


$conductor_id =
    !empty($_POST['conductor_id'])
        ? (int)$_POST['conductor_id']
        : null;


$motivo_ingreso =
    trim(
        $_POST['motivo_ingreso'] ?? ''
    );


$odometro =
    isset($_POST['odometro']) &&
    $_POST['odometro'] !== ''
        ? (int)$_POST['odometro']
        : null;


$estado =
    trim(
        $_POST['estado'] ?? ''
    );


$maestros =
    $_POST['maestros'] ?? [];


/* =========================================================
   VALIDACIÓN ID
========================================================= */

if ($id <= 0) {

    die(
        "OT inválida."
    );

}


/* =========================================================
   VALIDACIÓN FECHA INGRESO
========================================================= */

if ($fecha_ingreso === '') {

    die(
        "Debe ingresar una fecha de ingreso."
    );

}


/* =========================================================
   VALIDACIÓN ESTADO
========================================================= */

$estadosPermitidos = [

    'ABIERTA',
    'EN PROCESO',
    'CERRADA',
    'ANULADA'

];


if (
    !in_array(
        $estado,
        $estadosPermitidos,
        true
    )
) {

    die(
        "Estado de OT inválido."
    );

}


/* =========================================================
   OBTENER OT ACTUAL
========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        patente,
        numero_ot,
        documento_ot

    FROM ordenes_trabajo

    WHERE id = ?

    LIMIT 1
");


if (!$stmt) {

    die(
        "Error preparando consulta OT: " .
        $conn->error
    );

}


$stmt->bind_param(
    "i",
    $id
);


if (!$stmt->execute()) {

    die(
        "Error consultando la OT: " .
        $stmt->error
    );

}


$otActual =
    $stmt
        ->get_result()
        ->fetch_assoc();


if (!$otActual) {

    die(
        "La OT no existe."
    );

}


$patente =
    trim(
        $otActual['patente'] ?? ''
    );


$documentoAnterior =
    trim(
        $otActual['documento_ot'] ?? ''
    );


/* =========================================================
   DOCUMENTO EXISTENTE
========================================================= */

$carpetaDocumentos =
    __DIR__ .
    DIRECTORY_SEPARATOR .
    'documentos_ot';


$existeDocumentoAnterior = false;


if (
    $documentoAnterior !== ''
) {

    $rutaDocumentoAnterior =
        $carpetaDocumentos .
        DIRECTORY_SEPARATOR .
        $documentoAnterior;


    $existeDocumentoAnterior =
        is_file(
            $rutaDocumentoAnterior
        );

} else {

    $rutaDocumentoAnterior = '';

}


/* =========================================================
   DOCUMENTO NUEVO
========================================================= */

$nuevoArchivo = false;

$nombreNuevoArchivo = '';

$rutaNuevoArchivo = '';


/* =========================================================
   VALIDACIÓN MOTIVO
=========================================================

   REGLA DEL SISTEMA:

   1. Si existe un documento actual:
      -> el motivo puede estar vacío.

   2. Si se está cargando un documento nuevo:
      -> el motivo puede estar vacío.

   3. Si no existe documento:
      -> el motivo es obligatorio.

========================================================= */

$seEstaCargandoDocumentoNuevo = false;


if (
    isset($_FILES['documento_ot']) &&
    $_FILES['documento_ot']['error'] !== UPLOAD_ERR_NO_FILE
) {

    $seEstaCargandoDocumentoNuevo = true;

}


if (
    $motivo_ingreso === '' &&
    !$existeDocumentoAnterior &&
    !$seEstaCargandoDocumentoNuevo
) {

    die(
        "Debe ingresar el motivo de ingreso " .
        "o adjuntar un documento de la OT."
    );

}


/* =========================================================
   PREPARAR DOCUMENTO NUEVO
========================================================= */

if (
    $seEstaCargandoDocumentoNuevo
) {


    /* =====================================================
       ERROR DE SUBIDA
    ===================================================== */

    if (
        $_FILES['documento_ot']['error']
        !== UPLOAD_ERR_OK
    ) {

        die(
            "Ocurrió un error al cargar " .
            "el nuevo documento."
        );

    }


    $archivo =
        $_FILES['documento_ot'];


    /* =====================================================
       TAMAÑO
    ===================================================== */

    $maximoBytes =
        10 * 1024 * 1024;


    if (
        $archivo['size']
        > $maximoBytes
    ) {

        die(
            "El documento es demasiado grande. " .
            "El máximo permitido es de 10 MB."
        );

    }


    /* =====================================================
       EXTENSIÓN
    ===================================================== */

    $extensionesPermitidas = [

        'pdf',
        'jpg',
        'jpeg',
        'png'

    ];


    $extension =
        strtolower(
            pathinfo(
                $archivo['name'],
                PATHINFO_EXTENSION
            )
        );


    if (
        !in_array(
            $extension,
            $extensionesPermitidas,
            true
        )
    ) {

        die(
            "Formato no permitido. " .
            "Solo PDF, JPG, JPEG y PNG."
        );

    }


    /* =====================================================
       MIME REAL
    ===================================================== */

    $finfo =
        new finfo(
            FILEINFO_MIME_TYPE
        );


    $mime =
        $finfo->file(
            $archivo['tmp_name']
        );


    $mimesPermitidos = [

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
        !isset(
            $mimesPermitidos[$extension]
        ) ||
        !in_array(
            $mime,
            $mimesPermitidos[$extension],
            true
        )
    ) {

        die(
            "El tipo real del archivo " .
            "no coincide con su extensión."
        );

    }


    /* =====================================================
       CREAR CARPETA
    ===================================================== */

    if (
        !is_dir(
            $carpetaDocumentos
        )
    ) {

        if (
            !mkdir(
                $carpetaDocumentos,
                0755,
                true
            )
        ) {

            die(
                "No fue posible crear " .
                "la carpeta documentos_ot."
            );

        }

    }


    /* =====================================================
       NOMBRE ÚNICO
    ===================================================== */

    $numeroOT =
        (int)(
            $otActual['numero_ot']
        );


    $nombreNuevoArchivo =
        'OT_' .
        str_pad(
            $numeroOT,
            6,
            '0',
            STR_PAD_LEFT
        ) .
        '_' .
        date('Ymd_His') .
        '_' .
        bin2hex(
            random_bytes(4)
        ) .
        '.' .
        $extension;


    $rutaNuevoArchivo =
        $carpetaDocumentos .
        DIRECTORY_SEPARATOR .
        $nombreNuevoArchivo;


    /* =====================================================
       MOVER ARCHIVO
    ===================================================== */

    if (
        !move_uploaded_file(
            $archivo['tmp_name'],
            $rutaNuevoArchivo
        )
    ) {

        die(
            "No fue posible guardar " .
            "el nuevo documento."
        );

    }


    $nuevoArchivo = true;

}


/* =========================================================
   TRANSACCIÓN
========================================================= */

$conn->begin_transaction();


try {


    /* =====================================================
       ACTUALIZAR OT
    ===================================================== */

    if ($nuevoArchivo) {


        /* =================================================
           CON DOCUMENTO NUEVO
        ================================================= */

        $stmt = $conn->prepare("
            UPDATE ordenes_trabajo

            SET
                conductor_id = ?,
                fecha_ingreso = ?,
                motivo_ingreso = ?,
                odometro = ?,
                fecha_salida = ?,
                estado = ?,
                documento_ot = ?

            WHERE id = ?
        ");


        if (!$stmt) {

            throw new Exception(
                "Error preparando actualización: " .
                $conn->error
            );

        }


        $stmt->bind_param(
            "ississsi",
            $conductor_id,
            $fecha_ingreso,
            $motivo_ingreso,
            $odometro,
            $fecha_salida,
            $estado,
            $nombreNuevoArchivo,
            $id
        );


    } else {


        /* =================================================
           CONSERVAR DOCUMENTO ACTUAL
        ================================================= */

        $stmt = $conn->prepare("
            UPDATE ordenes_trabajo

            SET
                conductor_id = ?,
                fecha_ingreso = ?,
                motivo_ingreso = ?,
                odometro = ?,
                fecha_salida = ?,
                estado = ?

            WHERE id = ?
        ");


        if (!$stmt) {

            throw new Exception(
                "Error preparando actualización: " .
                $conn->error
            );

        }


        $stmt->bind_param(
            "ississi",
            $conductor_id,
            $fecha_ingreso,
            $motivo_ingreso,
            $odometro,
            $fecha_salida,
            $estado,
            $id
        );

    }


    /* =====================================================
       EJECUTAR ACTUALIZACIÓN
    ===================================================== */

    if (
        !$stmt->execute()
    ) {

        throw new Exception(
            "Error al actualizar la OT: " .
            $stmt->error
        );

    }


    /* =====================================================
       ELIMINAR MAESTROS ANTERIORES
    ===================================================== */

    $stmtEliminar =
        $conn->prepare("
            DELETE FROM ordenes_trabajo_maestros

            WHERE orden_trabajo_id = ?
        ");


    if (!$stmtEliminar) {

        throw new Exception(
            "Error preparando eliminación " .
            "de maestros: " .
            $conn->error
        );

    }


    $stmtEliminar->bind_param(
        "i",
        $id
    );


    if (
        !$stmtEliminar->execute()
    ) {

        throw new Exception(
            "No fue posible actualizar " .
            "los maestros: " .
            $stmtEliminar->error
        );

    }


    /* =====================================================
       INSERTAR MAESTROS
    ===================================================== */

    if (
        is_array($maestros) &&
        !empty($maestros)
    ) {


        $stmtMaestro =
            $conn->prepare("
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


            $maestro =
                trim(
                    $maestro
                );


            if (
                $maestro === ''
            ) {

                continue;

            }


            $stmtMaestro->bind_param(
                "is",
                $id,
                $maestro
            );


            if (
                !$stmtMaestro->execute()
            ) {

                throw new Exception(
                    "Error guardando maestro: " .
                    $stmtMaestro->error
                );

            }

        }

    }


    /* =====================================================
       CONFIRMAR TRANSACCIÓN
    ===================================================== */

    $conn->commit();


    /* =====================================================
       ELIMINAR DOCUMENTO ANTERIOR
       SOLO SI SE REEMPLAZÓ
    ===================================================== */

    if (
        $nuevoArchivo &&
        $rutaDocumentoAnterior !== '' &&
        is_file(
            $rutaDocumentoAnterior
        )
    ) {

        @unlink(
            $rutaDocumentoAnterior
        );

    }


    /* =====================================================
       VOLVER AL DETALLE
    ===================================================== */

    header(
        "Location:detalle_ot.php?id=" .
        urlencode(
            $id
        )
    );

    exit;


} catch (
    Exception $e
) {


    /* =====================================================
       ROLLBACK
    ===================================================== */

    $conn->rollback();


    /* =====================================================
       ELIMINAR NUEVO ARCHIVO
       SI LA BD FALLÓ
    ===================================================== */

    if (
        $nuevoArchivo &&
        $rutaNuevoArchivo !== '' &&
        is_file(
            $rutaNuevoArchivo
        )
    ) {

        @unlink(
            $rutaNuevoArchivo
        );

    }


    /* =====================================================
       MOSTRAR ERROR
    ===================================================== */

    die(
        "Error al actualizar la Orden de Trabajo:" .
        "<br><br>" .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );

}