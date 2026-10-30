<?php

/* =========================================================
   GUARDAR INCIDENTE
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

    $valor = trim(
        (string) $_POST[$campo]
    );

    return $valor === ''
        ? null
        : $valor;
}


function entero_post($campo)
{
    if (!isset($_POST[$campo])) {
        return null;
    }

    $valor = trim(
        (string) $_POST[$campo]
    );

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


    /*
     * Formato datetime-local:
     *
     * 2026-10-02T08:30
     */

    $fecha = DateTime::createFromFormat(
        'Y-m-d\TH:i',
        $valor
    );


    if ($fecha instanceof DateTime) {

        return $fecha->format(
            'Y-m-d H:i:s'
        );
    }


    /*
     * También aceptamos:
     *
     * 2026-10-02 08:30:00
     */

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
   DESCRIPCIÓN DEL INCIDENTE
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


/* ---------------------------------------------------------
   NÚMERO DE INCIDENTE
--------------------------------------------------------- */

if (
    $numero_incidente === null ||
    $numero_incidente <= 0
) {

    $errores[] =
        'Debe ingresar un número de incidente válido.';
}


/* ---------------------------------------------------------
   AÑO
--------------------------------------------------------- */

if (
    $anio_incidente === null ||
    $anio_incidente < 2000 ||
    $anio_incidente > 2100
) {

    $errores[] =
        'Debe ingresar un año de incidente válido.';
}


/* ---------------------------------------------------------
   FECHA DE OCURRENCIA
--------------------------------------------------------- */

if (!$fecha_hora_ocurrencia) {

    $errores[] =
        'Debe indicar la fecha y hora de ocurrencia del incidente.';
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
   VERIFICAR NÚMERO DE INCIDENTE POR AÑO
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
        LIMIT 1
    ";


    $stmt_existe =
        $db->prepare(
            $sql_existe
        );


    if ($stmt_existe) {

        $stmt_existe->bind_param(
            'ii',
            $numero_incidente,
            $anio_incidente
        );


        $stmt_existe->execute();


        $resultado_existe =
            $stmt_existe->get_result();


        if (
            $resultado_existe->num_rows > 0
        ) {

            $errores[] =
                'Ya existe un incidente con el número ' .
                $numero_incidente .
                ' correspondiente al año ' .
                $anio_incidente .
                '.';
        }


        $stmt_existe->close();
    }
}


/* =========================================================
   VALIDAR FOTOGRAFÍAS
========================================================= */

$fotos_validas = [];


$tipos_permitidos = [
    'image/jpeg',
    'image/png',
    'image/webp'
];


$extensiones_permitidas = [
    'jpg',
    'jpeg',
    'png',
    'webp'
];


/*
 * Máximo 10 fotografías.
 *
 * Máximo 10 MB por fotografía.
 */

$max_fotos = 10;

$max_tamano = 10 * 1024 * 1024;


if (
    isset($_FILES['fotos']) &&
    is_array($_FILES['fotos']['name'])
) {

    $cantidad_archivos =
        count(
            $_FILES['fotos']['name']
        );


    if (
        $cantidad_archivos >
        $max_fotos
    ) {

        $errores[] =
            'Puede adjuntar como máximo ' .
            $max_fotos .
            ' fotografías.';
    }


    /*
     * Analizamos cada archivo.
     */

    $finfo =
        new finfo(
            FILEINFO_MIME_TYPE
        );


    for (
        $i = 0;
        $i < $cantidad_archivos;
        $i++
    ) {

        $nombre_original =
            $_FILES['fotos']['name'][$i]
            ?? '';


        $tmp_name =
            $_FILES['fotos']['tmp_name'][$i]
            ?? '';


        $error_archivo =
            $_FILES['fotos']['error'][$i]
            ?? UPLOAD_ERR_NO_FILE;


        $tamano =
            (int)(
                $_FILES['fotos']['size'][$i]
                ?? 0
            );


        /*
         * Archivo no seleccionado.
         */

        if (
            $error_archivo ===
            UPLOAD_ERR_NO_FILE
        ) {

            continue;
        }


        /*
         * Error de subida.
         */

        if (
            $error_archivo !==
            UPLOAD_ERR_OK
        ) {

            $errores[] =
                'No fue posible cargar la fotografía "' .
                $nombre_original .
                '".';

            continue;
        }


        /*
         * Validar tamaño.
         */

        if (
            $tamano <= 0 ||
            $tamano > $max_tamano
        ) {

            $errores[] =
                'La fotografía "' .
                $nombre_original .
                '" supera el tamaño máximo de 10 MB.';

            continue;
        }


        /*
         * Obtener MIME real.
         */

        $mime =
            $finfo->file(
                $tmp_name
            );


        /*
         * Obtener extensión.
         */

        $extension =
            strtolower(
                pathinfo(
                    $nombre_original,
                    PATHINFO_EXTENSION
                )
            );


        /*
         * Validar MIME y extensión.
         */

        if (
            !in_array(
                $mime,
                $tipos_permitidos,
                true
            )
            ||
            !in_array(
                $extension,
                $extensiones_permitidas,
                true
            )
        ) {

            $errores[] =
                'La fotografía "' .
                $nombre_original .
                '" no tiene un formato permitido.';

            continue;
        }


        /*
         * Guardamos temporalmente
         * los datos.
         */

        $fotos_validas[] = [

            'tmp_name' =>
                $tmp_name,

            'nombre_original' =>
                $nombre_original,

            'extension' =>
                $extension

        ];
    }
}


/* =========================================================
   SI EXISTEN ERRORES
========================================================= */

if (!empty($errores)) {

    $mensaje =
        implode(
            ' ',
            $errores
        );


    header(
        'Location: index.php?error=' .
        urlencode($mensaje)
    );

    exit;
}


/* =========================================================
   INSERTAR INCIDENTE
========================================================= */

$sql = "
    INSERT INTO incidentes
    (
        numero_incidente,
        anio_incidente,
        conductor_id,

        fecha_hora_ocurrencia,
        fecha_hora_evento,
        fecha_hora_aviso,

        antecedentes,

        division,
        subdivision,
        planta,
        sucursal,
        empresa_evento,
        area,

        equipo,
        lugar_incidente,

        clasificacion_incidente,
        exposicion_lgf,
        riesgo_asociado,

        dano_propiedad,
        dano_personas,
        emergencia,
        accidente_grave_fatal,

        afectado_nombre,
        afectado_rut,
        afectado_sexo,
        afectado_edad,

        empresa_involucrada,
        cargo,
        partes_cuerpo,

        detalle_incidente,
        descripcion_preliminar,

        avisado_por,
        realizado_por
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
        ?,
        ?,
        ?,
        ?,
        ?,

        ?,
        ?,

        ?,
        ?,
        ?,

        ?,
        ?,
        ?,
        ?,

        ?,
        ?,
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
";


$stmt =
    $db->prepare(
        $sql
    );


if (!$stmt) {

    header(
        'Location: index.php?error=' .
        urlencode(
            'No fue posible preparar el registro del incidente: ' .
            $db->error
        )
    );

    exit;
}


/* =========================================================
   TIPOS DE LOS 33 PARÁMETROS
========================================================= */

$tipos =
    'iiisssssssssssssssiiiisssisssssss';


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
    $realizado_por
);


/* =========================================================
   EJECUTAR INSERT
========================================================= */

if (!$stmt->execute()) {

    $error_db =
        $stmt->error;


    $stmt->close();


    header(
        'Location: index.php?error=' .
        urlencode(
            'No fue posible guardar el incidente: ' .
            $error_db
        )
    );

    exit;
}


/* =========================================================
   OBTENER ID GENERADO
========================================================= */

$incidente_id =
    $stmt->insert_id;


$stmt->close();


/* =========================================================
   GUARDAR FOTOGRAFÍAS
========================================================= */

if (
    $incidente_id > 0 &&
    !empty($fotos_validas)
) {

    /*
     * =====================================================
     * CARPETA FÍSICA
     * =====================================================
     *
     * C:\xampp\htdocs\incidentes\incidentes_fotos\
     */

    $carpeta_fotos =
        __DIR__ .
        DIRECTORY_SEPARATOR .
        'incidentes_fotos';


    /*
     * =====================================================
     * RUTA WEB
     * =====================================================
     *
     * Esta es la ruta que podrá utilizar el navegador.
     *
     * Ejemplo:
     *
     * incidentes_fotos/incidente_1_20261002_xxxxx.jpg
     */

    $carpeta_web =
        'incidentes_fotos/';


    /*
     * Crear carpeta si no existe.
     */

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

            header(
                'Location: index.php?error=' .
                urlencode(
                    'El incidente fue creado, pero no fue posible crear la carpeta para las fotografías.'
                )
            );

            exit;
        }
    }


    /*
     * Verificar que la carpeta sea escribible.
     */

    if (
        !is_writable(
            $carpeta_fotos
        )
    ) {

        header(
            'Location: index.php?error=' .
            urlencode(
                'La carpeta de fotografías no tiene permisos de escritura.'
            )
        );

        exit;
    }


    /*
     * =====================================================
     * PREPARAR INSERT DE FOTOGRAFÍA
     * =====================================================
     */

    $sql_foto = "
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


    $stmt_foto =
        $db->prepare(
            $sql_foto
        );


    if (!$stmt_foto) {

        header(
            'Location: index.php?error=' .
            urlencode(
                'El incidente fue creado, pero no fue posible preparar el registro de las fotografías.'
            )
        );

        exit;
    }


    $archivos_creados = [];


    foreach (
        $fotos_validas
        as $foto
    ) {

        /*
         * =================================================
         * GENERAR NOMBRE FÍSICO ÚNICO
         * =================================================
         */

        $nombre_archivo =
            'incidente_' .
            $incidente_id .
            '_' .
            date('YmdHis') .
            '_' .
            bin2hex(
                random_bytes(5)
            ) .
            '.' .
            $foto['extension'];


        /*
         * =================================================
         * RUTA FÍSICA
         * =================================================
         */

        $ruta_destino =
            $carpeta_fotos .
            DIRECTORY_SEPARATOR .
            $nombre_archivo;


        /*
         * =================================================
         * RUTA WEB QUE SE GUARDA EN MYSQL
         * =================================================
         */

        $ruta_bd =
            $carpeta_web .
            $nombre_archivo;


        /*
         * =================================================
         * MOVER ARCHIVO TEMPORAL
         * =================================================
         */

        if (
            !move_uploaded_file(
                $foto['tmp_name'],
                $ruta_destino
            )
        ) {

            /*
             * Eliminar archivos ya creados.
             */

            foreach (
                $archivos_creados
                as $archivo_creado
            ) {

                if (
                    is_file(
                        $archivo_creado
                    )
                ) {

                    unlink(
                        $archivo_creado
                    );
                }
            }


            $stmt_foto->close();


            header(
                'Location: index.php?error=' .
                urlencode(
                    'No fue posible guardar una de las fotografías del incidente.'
                )
            );

            exit;
        }


        /*
         * Registrar archivo físico creado.
         */

        $archivos_creados[] =
            $ruta_destino;


        /*
         * =================================================
         * GUARDAR RUTA WEB EN BASE DE DATOS
         * =================================================
         *
         * ANTES:
         *
         * incidente_1_20261002_xxxxx.jpg
         *
         * AHORA:
         *
         * incidentes_fotos/incidente_1_20261002_xxxxx.jpg
         */

        $stmt_foto->bind_param(
            'iss',

            $incidente_id,

            $ruta_bd,

            $foto['nombre_original']
        );


        if (
            !$stmt_foto->execute()
        ) {

            /*
             * Si falla la BD,
             * eliminamos el archivo físico.
             */

            if (
                is_file(
                    $ruta_destino
                )
            ) {

                unlink(
                    $ruta_destino
                );
            }


            $stmt_foto->close();


            header(
                'Location: index.php?error=' .
                urlencode(
                    'El incidente fue creado, pero no fue posible registrar una de las fotografías.'
                )
            );

            exit;
        }
    }


    $stmt_foto->close();
}


/* =========================================================
   REDIRECCIÓN FINAL
========================================================= */

header(
    'Location: ver_incidente.php?id=' .
    $incidente_id .
    '&guardado=1'
);

exit;