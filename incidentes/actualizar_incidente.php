<?php

/* =========================================================
   ACTUALIZAR INCIDENTE
   Módulo de Incidentes
========================================================= */

date_default_timezone_set('America/Santiago');


/* =========================================================
   CONEXIÓN
========================================================= */

require_once __DIR__ . '/../errores/db.php';


/* =========================================================
   DETECTAR CONEXIÓN MYSQLI
========================================================= */

$db = null;

$posibles_conexiones = [
    'conn',
    'conexion',
    'mysqli',
    'db',
    'link'
];

foreach ($posibles_conexiones as $variable) {

    if (
        isset($$variable) &&
        $$variable instanceof mysqli
    ) {

        $db = $$variable;

        break;
    }
}


if (!$db) {

    die(
        'No se pudo encontrar una conexión MySQL válida. ' .
        'Revisa el archivo ../errores/db.php'
    );

}


/* =========================================================
   VALIDAR MÉTODO
========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');

    exit;

}


/* =========================================================
   FUNCIONES AUXILIARES
========================================================= */

function valor_post($campo)
{
    if (!isset($_POST[$campo])) {

        return null;

    }

    $valor = trim((string) $_POST[$campo]);

    return $valor === ''
        ? null
        : $valor;
}


function entero_post($campo)
{
    if (!isset($_POST[$campo])) {

        return null;

    }

    $valor = trim((string) $_POST[$campo]);

    if ($valor === '') {

        return null;

    }

    return (int) $valor;
}


function fecha_mysql($valor)
{
    if (!$valor) {

        return null;

    }

    $valor = trim($valor);


    /* =====================================================
       FORMATO DATETIME-LOCAL
       2026-10-02T08:30
    ===================================================== */

    $fecha = DateTime::createFromFormat(
        'Y-m-d\TH:i',
        $valor
    );


    if ($fecha instanceof DateTime) {

        return $fecha->format(
            'Y-m-d H:i:s'
        );

    }


    /* =====================================================
       FORMATO MYSQL
       2026-10-02 08:30:00
    ===================================================== */

    $fecha = DateTime::createFromFormat(
        'Y-m-d H:i:s',
        $valor
    );


    if ($fecha instanceof DateTime) {

        return $fecha->format(
            'Y-m-d H:i:s'
        );

    }


    return null;
}


/* =========================================================
   ID DEL INCIDENTE
========================================================= */

$id = entero_post('id');


if (
    $id === null ||
    $id <= 0
) {

    header(
        'Location: index.php?error=' .
        urlencode(
            'El incidente seleccionado no es válido.'
        )
    );

    exit;

}


/* =========================================================
   DATOS PRINCIPALES
========================================================= */

$numero_incidente =
    entero_post(
        'numero_incidente'
    );


$anio_incidente =
    entero_post(
        'anio_incidente'
    );


$conductor_id =
    entero_post(
        'conductor_id'
    );


$fecha_hora_ocurrencia =
    fecha_mysql(
        valor_post(
            'fecha_hora_ocurrencia'
        )
    );


$fecha_hora_evento =
    fecha_mysql(
        valor_post(
            'fecha_hora_evento'
        )
    );


$fecha_hora_aviso =
    fecha_mysql(
        valor_post(
            'fecha_hora_aviso'
        )
    );


/* =========================================================
   DATOS ORGANIZACIONALES
========================================================= */

$antecedentes =
    valor_post(
        'antecedentes'
    );


$division =
    valor_post(
        'division'
    );


$subdivision =
    valor_post(
        'subdivision'
    );


$planta =
    valor_post(
        'planta'
    );


$sucursal =
    valor_post(
        'sucursal'
    );


$empresa_evento =
    valor_post(
        'empresa_evento'
    );


$area =
    valor_post(
        'area'
    );


/* =========================================================
   EQUIPO / UBICACIÓN
========================================================= */

$equipo =
    valor_post(
        'equipo'
    );


$lugar_incidente =
    valor_post(
        'lugar_incidente'
    );


/* =========================================================
   CLASIFICACIÓN
========================================================= */

$clasificacion_incidente =
    valor_post(
        'clasificacion_incidente'
    );


$exposicion_lgf =
    valor_post(
        'exposicion_lgf'
    );


$riesgo_asociado =
    valor_post(
        'riesgo_asociado'
    );


/* =========================================================
   INDICADORES
========================================================= */

$dano_propiedad =
    isset(
        $_POST['dano_propiedad']
    )
        ? 1
        : 0;


$dano_personas =
    isset(
        $_POST['dano_personas']
    )
        ? 1
        : 0;


$emergencia =
    isset(
        $_POST['emergencia']
    )
        ? 1
        : 0;


$accidente_grave_fatal =
    isset(
        $_POST['accidente_grave_fatal']
    )
        ? 1
        : 0;


/* =========================================================
   PERSONA AFECTADA
========================================================= */

$afectado_nombre =
    valor_post(
        'afectado_nombre'
    );


$afectado_rut =
    valor_post(
        'afectado_rut'
    );


$afectado_sexo =
    valor_post(
        'afectado_sexo'
    );


$afectado_edad =
    entero_post(
        'afectado_edad'
    );


$empresa_involucrada =
    valor_post(
        'empresa_involucrada'
    );


$cargo =
    valor_post(
        'cargo'
    );


$partes_cuerpo =
    valor_post(
        'partes_cuerpo'
    );


/* =========================================================
   DESCRIPCIÓN
========================================================= */

$detalle_incidente =
    valor_post(
        'detalle_incidente'
    );


$descripcion_preliminar =
    valor_post(
        'descripcion_preliminar'
    );


/* =========================================================
   RESPONSABLES / AVISO
========================================================= */

$avisado_por =
    valor_post(
        'avisado_por'
    );


$realizado_por =
    valor_post(
        'realizado_por'
    );


/* =========================================================
   VALIDACIONES
========================================================= */

$errores = [];


/* =========================================================
   VALIDAR NÚMERO
========================================================= */

if (
    $numero_incidente === null ||
    $numero_incidente <= 0
) {

    $errores[] =
        'Debe ingresar un número de incidente válido.';

}


/* =========================================================
   VALIDAR AÑO
========================================================= */

if (
    $anio_incidente === null ||
    $anio_incidente < 2000 ||
    $anio_incidente > 2100
) {

    $errores[] =
        'Debe ingresar un año de incidente válido.';

}


/* =========================================================
   VALIDAR FECHA DE OCURRENCIA
========================================================= */

if (!$fecha_hora_ocurrencia) {

    $errores[] =
        'Debe indicar la fecha y hora de ocurrencia del incidente.';

}


/* =========================================================
   VALIDAR EXISTENCIA DEL INCIDENTE
========================================================= */

$sql_actual = "
    SELECT id
    FROM incidentes
    WHERE id = ?
    LIMIT 1
";


$stmt_actual =
    $db->prepare(
        $sql_actual
    );


if (!$stmt_actual) {

    $errores[] =
        'No fue posible validar el incidente seleccionado.';

} else {

    $stmt_actual->bind_param(
        'i',
        $id
    );


    $stmt_actual->execute();


    $resultado_actual =
        $stmt_actual->get_result();


    if (
        $resultado_actual->num_rows === 0
    ) {

        $errores[] =
            'El incidente que intenta modificar no existe.';

    }


    $stmt_actual->close();

}


/* =========================================================
   VALIDAR CONDUCTOR
========================================================= */

if ($conductor_id !== null) {

    $sql_conductor = "
        SELECT id
        FROM conductores
        WHERE id = ?
        LIMIT 1
    ";


    $stmt_conductor =
        $db->prepare(
            $sql_conductor
        );


    if (!$stmt_conductor) {

        $errores[] =
            'No fue posible validar el conductor.';

    } else {

        $stmt_conductor->bind_param(
            'i',
            $conductor_id
        );


        $stmt_conductor->execute();


        $resultado_conductor =
            $stmt_conductor->get_result();


        if (
            $resultado_conductor->num_rows === 0
        ) {

            $errores[] =
                'El conductor seleccionado no existe.';

        }


        $stmt_conductor->close();

    }

}


/* =========================================================
   VALIDAR NÚMERO DUPLICADO
========================================================= */

if (
    $numero_incidente !== null &&
    $anio_incidente !== null
) {

    $sql_existe = "
        SELECT id
        FROM incidentes
        WHERE numero_incidente = ?
        AND anio_incidente = ?
        AND id <> ?
        LIMIT 1
    ";


    $stmt_existe =
        $db->prepare(
            $sql_existe
        );


    if ($stmt_existe) {

        $stmt_existe->bind_param(
            'iii',
            $numero_incidente,
            $anio_incidente,
            $id
        );


        $stmt_existe->execute();


        $resultado_existe =
            $stmt_existe->get_result();


        if (
            $resultado_existe->num_rows > 0
        ) {

            $errores[] =
                'Ya existe otro incidente con el número ' .
                $numero_incidente .
                ' correspondiente al año ' .
                $anio_incidente .
                '.';

        }


        $stmt_existe->close();

    } else {

        $errores[] =
            'No fue posible validar el número del incidente.';

    }

}


/* =========================================================
   SI HAY ERRORES
========================================================= */

if (!empty($errores)) {

    $mensaje =
        implode(
            ' ',
            $errores
        );


    header(
        'Location: editar_incidente.php?id=' .
        $id .
        '&error=' .
        urlencode(
            $mensaje
        )
    );

    exit;

}


/* =========================================================
   ACTUALIZAR INCIDENTE
========================================================= */

$sql = "
    UPDATE incidentes
    SET

        numero_incidente = ?,
        anio_incidente = ?,
        conductor_id = ?,

        fecha_hora_ocurrencia = ?,
        fecha_hora_evento = ?,
        fecha_hora_aviso = ?,

        antecedentes = ?,

        division = ?,
        subdivision = ?,
        planta = ?,
        sucursal = ?,
        empresa_evento = ?,
        area = ?,

        equipo = ?,
        lugar_incidente = ?,

        clasificacion_incidente = ?,
        exposicion_lgf = ?,
        riesgo_asociado = ?,

        dano_propiedad = ?,
        dano_personas = ?,
        emergencia = ?,
        accidente_grave_fatal = ?,

        afectado_nombre = ?,
        afectado_rut = ?,
        afectado_sexo = ?,
        afectado_edad = ?,

        empresa_involucrada = ?,
        cargo = ?,
        partes_cuerpo = ?,

        detalle_incidente = ?,
        descripcion_preliminar = ?,

        avisado_por = ?,
        realizado_por = ?

    WHERE id = ?

    LIMIT 1
";


$stmt =
    $db->prepare(
        $sql
    );


if (!$stmt) {

    header(
        'Location: editar_incidente.php?id=' .
        $id .
        '&error=' .
        urlencode(
            'No fue posible preparar la actualización: ' .
            $db->error
        )
    );

    exit;

}


/* =========================================================
   TIPOS
   =========================================================

   34 parámetros:

   1  numero_incidente       i
   2  anio_incidente         i
   3  conductor_id           i

   4  fecha_ocurrencia       s
   5  fecha_evento           s
   6  fecha_aviso            s
   7  antecedentes           s
   8  division               s
   9  subdivision            s
   10 planta                 s
   11 sucursal               s
   12 empresa_evento         s
   13 area                   s
   14 equipo                 s
   15 lugar_incidente        s
   16 clasificacion          s
   17 exposicion_lgf         s
   18 riesgo_asociado        s

   19 dano_propiedad         i
   20 dano_personas          i
   21 emergencia             i
   22 accidente_grave_fatal  i

   23 afectado_nombre        s
   24 afectado_rut           s
   25 afectado_sexo          s
   26 afectado_edad          i

   27 empresa_involucrada    s
   28 cargo                  s
   29 partes_cuerpo          s
   30 detalle_incidente      s
   31 descripcion_preliminar s
   32 avisado_por            s
   33 realizado_por          s

   34 id                     i
========================================================= */

$tipos =
    'iiisssssssssssssssiiiisssisssssssi';


$stmt->bind_param(

    $tipos,

    $numero_incidente,
    $anio_incidente,
    $conductor_id,

    $fecha_hora_ocurrencia,
    $fecha_hora_evento,
    $fecha_hora_aviso,

    $antecedentes,

    $division,
    $subdivision,
    $planta,
    $sucursal,
    $empresa_evento,
    $area,

    $equipo,
    $lugar_incidente,

    $clasificacion_incidente,
    $exposicion_lgf,
    $riesgo_asociado,

    $dano_propiedad,
    $dano_personas,
    $emergencia,
    $accidente_grave_fatal,

    $afectado_nombre,
    $afectado_rut,
    $afectado_sexo,
    $afectado_edad,

    $empresa_involucrada,
    $cargo,
    $partes_cuerpo,

    $detalle_incidente,
    $descripcion_preliminar,

    $avisado_por,
    $realizado_por,

    $id
);


/* =========================================================
   EJECUTAR ACTUALIZACIÓN
========================================================= */

if (!$stmt->execute()) {

    $error_db =
        $stmt->error;


    $stmt->close();


    header(
        'Location: editar_incidente.php?id=' .
        $id .
        '&error=' .
        urlencode(
            'No fue posible actualizar el incidente: ' .
            $error_db
        )
    );

    exit;

}


$stmt->close();


/* =========================================================
   FOTOGRAFÍAS
   =========================================================

   IMPORTANTE:

   - Si NO se seleccionan fotografías:
       no se modifica ninguna fotografía existente.

   - Si se seleccionan fotografías:
       se agregan a las fotografías existentes.

   - Las fotografías anteriores NO se eliminan.
========================================================= */


/* =========================================================
   CONFIGURACIÓN
========================================================= */

$carpeta_fotos =
    __DIR__ .
    DIRECTORY_SEPARATOR .
    'incidentes_fotos';


$hay_archivos_enviados = false;

$fotos_validas = [];

$errores_fotos = [];


/* =========================================================
   COMPROBAR ARCHIVOS RECIBIDOS
========================================================= */

if (
    isset($_FILES['fotos']) &&
    isset($_FILES['fotos']['name']) &&
    is_array($_FILES['fotos']['name'])
) {

    $cantidad_fotos =
        count(
            $_FILES['fotos']['name']
        );


    for (
        $i = 0;
        $i < $cantidad_fotos;
        $i++
    ) {

        $nombre_original =
            trim(
                (string) (
                    $_FILES['fotos']['name'][$i]
                    ?? ''
                )
            );


        $error_archivo =
            $_FILES['fotos']['error'][$i]
            ?? UPLOAD_ERR_NO_FILE;


        /* =================================================
           SIN ARCHIVO EN ESTA POSICIÓN
        ================================================= */

        if (
            $error_archivo === UPLOAD_ERR_NO_FILE
        ) {

            continue;

        }


        $hay_archivos_enviados = true;


        /* =================================================
           ERROR DE SUBIDA
        ================================================= */

        if (
            $error_archivo !== UPLOAD_ERR_OK
        ) {

            $errores_fotos[] =
                'No fue posible subir la fotografía "' .
                $nombre_original .
                '". Código de error: ' .
                $error_archivo .
                '.';

            continue;

        }


        /* =================================================
           ARCHIVO TEMPORAL
        ================================================= */

        $tmp_name =
            $_FILES['fotos']['tmp_name'][$i]
            ?? '';


        if (
            $tmp_name === '' ||
            !is_uploaded_file($tmp_name)
        ) {

            $errores_fotos[] =
                'El archivo "' .
                $nombre_original .
                '" no fue recibido correctamente.';

            continue;

        }


        /* =================================================
           TAMAÑO
           MÁXIMO: 10 MB
        ================================================= */

        $tamano =
            (int) (
                $_FILES['fotos']['size'][$i]
                ?? 0
            );


        if ($tamano <= 0) {

            $errores_fotos[] =
                'La fotografía "' .
                $nombre_original .
                '" está vacía.';

            continue;

        }


        if (
            $tamano >
            10 * 1024 * 1024
        ) {

            $errores_fotos[] =
                'La fotografía "' .
                $nombre_original .
                '" supera el límite de 10 MB.';

            continue;

        }


        /* =================================================
           VALIDAR IMAGEN REAL
        ================================================= */

        $informacion_imagen =
            @getimagesize(
                $tmp_name
            );


        if (
            $informacion_imagen === false
        ) {

            $errores_fotos[] =
                'El archivo "' .
                $nombre_original .
                '" no corresponde a una imagen válida.';

            continue;

        }


        /* =================================================
           MIME REAL
        ================================================= */

        $mime =
            $informacion_imagen['mime']
            ?? '';


        $mime_permitidos = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif'
        ];


        if (
            !in_array(
                $mime,
                $mime_permitidos,
                true
            )
        ) {

            $errores_fotos[] =
                'La fotografía "' .
                $nombre_original .
                '" tiene un formato no permitido.';

            continue;

        }


        /* =================================================
           EXTENSIÓN SEGÚN MIME
        ================================================= */

        switch ($mime) {

            case 'image/jpeg':

                $extension = 'jpg';

                break;


            case 'image/png':

                $extension = 'png';

                break;


            case 'image/webp':

                $extension = 'webp';

                break;


            case 'image/gif':

                $extension = 'gif';

                break;


            default:

                $errores_fotos[] =
                    'No fue posible determinar el formato de la fotografía "' .
                    $nombre_original .
                    '".';

                continue 2;

        }


        /* =================================================
           CREAR NOMBRE ÚNICO
        ================================================= */

        try {

            $identificador =
                bin2hex(
                    random_bytes(16)
                );

        } catch (Throwable $e) {

            $identificador =
                uniqid(
                    '',
                    true
                );

        }


        $nombre_archivo =
            'incidente_' .
            $id .
            '_' .
            $identificador .
            '.' .
            $extension;


        $ruta_destino =
            $carpeta_fotos .
            DIRECTORY_SEPARATOR .
            $nombre_archivo;


        $fotos_validas[] = [

            'tmp_name' =>
                $tmp_name,

            'nombre_original' =>
                $nombre_original,

            'nombre_archivo' =>
                $nombre_archivo,

            'ruta_destino' =>
                $ruta_destino

        ];

    }

}


/* =========================================================
   CREAR CARPETA
========================================================= */

if (
    !empty($fotos_validas)
) {

    if (
        !is_dir(
            $carpeta_fotos
        )
    ) {

        if (
            !mkdir(
                $carpeta_fotos,
                0755,
                true
            )
        ) {

            $errores_fotos[] =
                'No fue posible crear la carpeta de fotografías.';

        }

    }


    if (
        !is_writable(
            $carpeta_fotos
        )
    ) {

        $errores_fotos[] =
            'La carpeta de fotografías no tiene permisos de escritura.';

    }

}


/* =========================================================
   SI EXISTEN ERRORES DE VALIDACIÓN DE FOTOS
========================================================= */

if (!empty($errores_fotos)) {

    $mensaje_fotos =
        implode(
            ' ',
            $errores_fotos
        );


    header(
        'Location: editar_incidente.php?id=' .
        $id .
        '&error=' .
        urlencode(
            'El incidente fue actualizado, pero hubo problemas con las fotografías: ' .
            $mensaje_fotos
        )
    );

    exit;

}


/* =========================================================
   GUARDAR FOTOGRAFÍAS NUEVAS
========================================================= */

$fotos_guardadas = 0;


if (
    !empty($fotos_validas)
) {

    /* =====================================================
       PREPARAR INSERT
    ===================================================== */

    $sql_insertar_foto = "
        INSERT INTO incidentes_fotos
        (
            incidente_id,
            archivo,
            nombre_original,
            fecha_subida
        )
        VALUES
        (
            ?,
            ?,
            ?,
            NOW()
        )
    ";


    $stmt_insertar_foto =
        $db->prepare(
            $sql_insertar_foto
        );


    if (!$stmt_insertar_foto) {

        header(
            'Location: editar_incidente.php?id=' .
            $id .
            '&error=' .
            urlencode(
                'El incidente fue actualizado, pero no fue posible preparar el registro de las fotografías: ' .
                $db->error
            )
        );

        exit;

    }


    /* =====================================================
       PROCESAR CADA FOTOGRAFÍA
    ===================================================== */

    foreach (
        $fotos_validas as $foto
    ) {

        /* =================================================
           MOVER ARCHIVO
        ================================================= */

        if (
            !move_uploaded_file(
                $foto['tmp_name'],
                $foto['ruta_destino']
            )
        ) {

            continue;

        }


        /* =================================================
           INSERTAR EN BASE DE DATOS
        ================================================= */

        $archivo =
            $foto['nombre_archivo'];


        $nombre_original =
            $foto['nombre_original'];


        $stmt_insertar_foto->bind_param(
            'iss',
            $id,
            $archivo,
            $nombre_original
        );


        if (
            $stmt_insertar_foto->execute()
        ) {

            $fotos_guardadas++;

        } else {

            /* =============================================
               SI FALLA EL INSERT,
               ELIMINAR EL ARCHIVO
            ============================================= */

            if (
                is_file(
                    $foto['ruta_destino']
                )
            ) {

                @unlink(
                    $foto['ruta_destino']
                );

            }

        }

    }


    $stmt_insertar_foto->close();


    /* =====================================================
       COMPROBAR RESULTADO
    ===================================================== */

    if (
        $fotos_guardadas === 0
    ) {

        header(
            'Location: editar_incidente.php?id=' .
            $id .
            '&error=' .
            urlencode(
                'El incidente fue actualizado, pero no fue posible guardar ninguna de las nuevas fotografías.'
            )
        );

        exit;

    }

}


/* =========================================================
   MENSAJE FINAL
========================================================= */

if (
    $hay_archivos_enviados &&
    $fotos_guardadas > 0
) {

    $mensaje =
        'Incidente actualizado correctamente. ' .
        $fotos_guardadas .
        ' fotografía(s) nueva(s) fueron agregadas.';


    header(
        'Location: ver_incidente.php?id=' .
        $id .
        '&actualizado=1&fotos=' .
        $fotos_guardadas
    );

    exit;

}


/* =========================================================
   ACTUALIZACIÓN SIN FOTOS NUEVAS
========================================================= */

header(
    'Location: ver_incidente.php?id=' .
    $id .
    '&actualizado=1'
);

exit;