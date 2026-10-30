<?php

/* =========================================================
   VER INCIDENTE
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
   OBTENER ID
========================================================= */

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {

    header(
        'Location: index.php?error=' .
        urlencode('Incidente no válido.')
    );

    exit;
}


/* =========================================================
   CONSULTAR INCIDENTE
========================================================= */

$sql = "
    SELECT
        i.*,
        c.rut AS conductor_rut,
        c.nombre AS conductor_nombre,
        c.area AS conductor_area
    FROM incidentes i
    LEFT JOIN conductores c
        ON c.id = i.conductor_id
    WHERE i.id = ?
    LIMIT 1
";

$stmt = $db->prepare($sql);

if (!$stmt) {

    die(
        'Error al preparar la consulta: ' .
        htmlspecialchars($db->error)
    );
}

$stmt->bind_param('i', $id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    $stmt->close();

    header(
        'Location: index.php?error=' .
        urlencode('El incidente solicitado no existe.')
    );

    exit;
}

$incidente = $resultado->fetch_assoc();

$stmt->close();


/* =========================================================
   FOTOGRAFÍAS / EVIDENCIAS
========================================================= */

$fotos = [];

$sqlFotos = "
    SELECT
        id,
        incidente_id,
        archivo,
        nombre_original,
        fecha_subida
    FROM incidentes_fotos
    WHERE incidente_id = ?
    ORDER BY id ASC
";

$stmtFotos = $db->prepare($sqlFotos);

if ($stmtFotos) {

    $stmtFotos->bind_param('i', $id);
    $stmtFotos->execute();

    $resultadoFotos = $stmtFotos->get_result();

    while ($foto = $resultadoFotos->fetch_assoc()) {
        $fotos[] = $foto;
    }

    $stmtFotos->close();
}


/* =========================================================
   FUNCIONES
========================================================= */

function escapar($valor)
{
    return htmlspecialchars(
        (string)($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}


function mostrar($valor, $vacio = 'No informado')
{
    if (
        $valor === null ||
        trim((string)$valor) === ''
    ) {

        return '<span class="dato-vacio">' .
            escapar($vacio) .
            '</span>';
    }

    return escapar($valor);
}


function fecha_hora($valor)
{
    if (
        !$valor ||
        $valor === '0000-00-00 00:00:00'
    ) {

        return '<span class="dato-vacio">
                    No informado
                </span>';
    }

    $timestamp = strtotime($valor);

    if (!$timestamp) {
        return escapar($valor);
    }

    return date('d/m/Y H:i', $timestamp);
}


function si_no($valor)
{
    return ((int)$valor === 1)

        ? '<span class="indicador si">SÍ</span>'

        : '<span class="indicador no">NO</span>';
}


/* =========================================================
   CONSTRUIR RUTA DE FOTOGRAFÍA
========================================================= */

/*
 * Esta función es importante.
 *
 * La tabla incidentes_fotos puede contener:
 *
 *   foto.jpg
 *
 * o:
 *
 *   fotos/foto.jpg
 *
 * o:
 *
 *   /incidentes/fotos/foto.jpg
 *
 * o incluso:
 *
 *   incidentes/fotos/foto.jpg
 *
 * La función normaliza todos esos casos para que
 * el navegador pueda encontrar correctamente la imagen.
 */

/* =========================================================
   CONSTRUIR RUTA DE FOTOGRAFÍA
========================================================= */

function ruta_fotografia($archivo)
{
    $archivo = trim((string)$archivo);

    if ($archivo === '') {
        return '';
    }


    /*
     * Normalizar separadores de Windows.
     */

    $archivo = str_replace('\\', '/', $archivo);


    /*
     * Si ya es una URL completa.
     */

    if (
        preg_match(
            '#^(https?:)?//#i',
            $archivo
        )
    ) {
        return $archivo;
    }


    /*
     * Eliminar ./ inicial.
     */

    $archivo = preg_replace(
        '#^\./+#',
        '',
        $archivo
    );


    /*
     * =====================================================
     * CASO PRINCIPAL
     * =====================================================
     *
     * actualizar_incidente.php guarda solamente:
     *
     * incidente_15_xxxxx.jpg
     *
     * y físicamente el archivo está en:
     *
     * C:\xampp\htdocs\incidentes\incidentes_fotos\
     *
     * Por lo tanto la URL correcta es:
     *
     * /incidentes/incidentes_fotos/archivo.jpg
     */


    /*
     * Si viene una ruta completa que contiene
     * incidentes_fotos, nos quedamos solamente
     * con el nombre del archivo.
     */

    $posicion = stripos(
        $archivo,
        'incidentes_fotos/'
    );


    if ($posicion !== false) {

        $archivo = substr(
            $archivo,
            $posicion + strlen('incidentes_fotos/')
        );
    }


    /*
     * Si por alguna razón viene una ruta absoluta
     * de Windows, basename() evita que se intente
     * utilizar esa ruta directamente desde el navegador.
     */

    $nombreArchivo = basename($archivo);


    if (
        $nombreArchivo === '' ||
        $nombreArchivo === '.' ||
        $nombreArchivo === '..'
    ) {
        return '';
    }


    /*
     * =====================================================
     * RUTA WEB CORRECTA
     * =====================================================
     */

    return '/incidentes/incidentes_fotos/' .
        rawurlencode($nombreArchivo);
}


/* =========================================================
   DATOS PRINCIPALES
========================================================= */

$numero = (int)(
    $incidente['numero_incidente'] ?? 0
);

$anio = (int)(
    $incidente['anio_incidente'] ?? 0
);

$identificador = str_pad(
    (string)$numero,
    4,
    '0',
    STR_PAD_LEFT
);

$conductor_nombre = trim(
    (string)(
        $incidente['conductor_nombre'] ?? ''
    )
);

$conductor_rut = trim(
    (string)(
        $incidente['conductor_rut'] ?? ''
    )
);

$conductor_area = trim(
    (string)(
        $incidente['conductor_area'] ?? ''
    )
);

$titulo =
    'Incidente #' .
    $identificador .
    ' · ' .
    $anio;


/* =========================================================
   PREPARAR RUTAS DE FOTOGRAFÍAS
========================================================= */

/* =========================================================
   PREPARAR RUTAS DE FOTOGRAFÍAS
========================================================= */

foreach ($fotos as $indice => $foto) {

    $archivoFoto = trim(
        (string)($foto['archivo'] ?? '')
    );


    $fotos[$indice]['ruta_web'] =
        ruta_fotografia(
            $archivoFoto
        );


    /*
     * Ruta física real del archivo.
     */

    $nombreArchivoFisico =
        basename(
            str_replace(
                '\\',
                '/',
                $archivoFoto
            )
        );


    $rutaFisica =
        __DIR__ .
        DIRECTORY_SEPARATOR .
        'incidentes_fotos' .
        DIRECTORY_SEPARATOR .
        $nombreArchivoFisico;


    $fotos[$indice]['ruta_fisica'] =
        $rutaFisica;


    $fotos[$indice]['existe'] =
        is_file($rutaFisica);
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0"

>

<title>
    <?= escapar($titulo) ?> | Sistema de Incidentes
</title>

<link
    rel="icon"
    type="image/png"
    href="/errores/favicon.png?v=2"
>

<link
    rel="shortcut icon"
    type="image/png"
    href="/errores/favicon.png?v=2"
>

<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.gstatic.com"
    crossorigin
>

<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>

<style>

/* =========================================================
   VARIABLES
========================================================= */

:root {

    --azul: #2563eb;
    --azul-oscuro: #1e40af;
    --azul-suave: #eff6ff;

    --verde: #16a34a;
    --rojo: #dc2626;
    --naranja: #ea580c;
    --amarillo: #ca8a04;

    --texto: #0f172a;
    --texto-secundario: #64748b;

    --borde: #dbe3ef;

    --fondo: #f1f5f9;

    --blanco: #ffffff;
}


/* =========================================================
   GENERAL
========================================================= */

* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {

    margin: 0;

    background:
        linear-gradient(
            135deg,
            #f8fafc 0%,
            #eef2ff 100%
        );

    color: var(--texto);

    font-family:
        'Inter',
        Arial,
        sans-serif;
}


/* =========================================================
   CONTENEDOR
========================================================= */

.contenedor {

    width: min(
        1400px,
        calc(100% - 40px)
    );

    margin: 0 auto;

    padding: 30px 0 60px;
}


/* =========================================================
   CABECERA WEB
========================================================= */

.cabecera {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 20px;

    margin-bottom: 20px;
}

.titulo-area h1 {

    margin: 0;

    font-size: 29px;

    font-weight: 800;

    letter-spacing: -.7px;
}

.titulo-area p {

    margin: 7px 0 0;

    color: var(--texto-secundario);

    font-size: 13px;
}

.badge-incidente {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 7px 12px;

    margin-bottom: 10px;

    border-radius: 999px;

    background: var(--azul-suave);

    color: var(--azul-oscuro);

    font-size: 11px;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .5px;
}


/* =========================================================
   ACCIONES
========================================================= */

.acciones {

    display: flex;

    flex-wrap: wrap;

    gap: 9px;
}

.btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding: 10px 14px;

    border-radius: 9px;

    border: 0;

    text-decoration: none;

    font-family: inherit;

    font-size: 12px;

    font-weight: 700;

    cursor: pointer;

    transition: .2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-volver {

    color: #334155;

    background: #fff;

    border: 1px solid var(--borde);
}

.btn-editar {

    color: #fff;

    background: var(--azul);
}

.btn-imprimir {

    color: #fff;

    background: #0f172a;
}


/* =========================================================
   FORMULARIO
========================================================= */

.formulario {

    background: rgba(
        255,
        255,
        255,
        .97
    );

    border: 1px solid var(--borde);

    border-radius: 16px;

    box-shadow:
        0 10px 30px rgba(
            15,
            23,
            42,
            .07
        );

    overflow: hidden;

    margin-bottom: 15px;
}


/* =========================================================
   CABECERA FORMULARIO
========================================================= */

.formulario-header {

    padding: 17px 22px;

    background:
        linear-gradient(
            135deg,
            #ffffff,
            #f8fafc
        );

    border-bottom: 2px solid var(--azul);

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;
}

.logo-web {

    height: 55px;

    max-width: 210px;

    width: auto;

    object-fit: contain;
}

.header-derecha {
    text-align: right;
}

.header-derecha .titulo {

    font-size: 18px;

    font-weight: 800;

    color: #0f172a;
}

.header-derecha .subtitulo {

    margin-top: 4px;

    color: #64748b;

    font-size: 11px;
}

.numero-documento {

    margin-top: 8px;

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 7px 12px;

    border-radius: 7px;

    background: var(--azul-suave);

    color: var(--azul-oscuro);

    font-size: 12px;

    font-weight: 800;
}


/* =========================================================
   SECCIONES
========================================================= */

.seccion {
    border-bottom: 1px solid #e2e8f0;
}

.seccion:last-child {
    border-bottom: 0;
}

.seccion-titulo {

    padding: 9px 18px;

    background: #f8fafc;

    border-bottom: 1px solid #e2e8f0;

    color: #334155;

    font-size: 10px;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .6px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 7px;
}

.seccion-titulo i {

    color: var(--azul);

    font-size: 11px;
}


/* =========================================================
   GRID
========================================================= */

.grid {

    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 0;
}

.campo {

    min-height: 54px;

    padding: 9px 14px;

    border-right: 1px solid #e2e8f0;

    border-bottom: 1px solid #e2e8f0;
}

.campo:nth-child(4n) {
    border-right: 0;
}

.campo-label {

    margin-bottom: 4px;

    color: #64748b;

    font-size: 8px;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .35px;
}

.campo-valor {

    color: #0f172a;

    font-size: 10.5px;

    font-weight: 600;

    line-height: 1.35;

    word-break: break-word;
}

.campo-completo {

    grid-column: 1 / -1;

    border-right: 0;
}


/* =========================================================
   CONDUCTOR
========================================================= */

.conductor {

    display: grid;

    grid-template-columns:
        55px
        1fr
        1fr
        1fr;

    align-items: center;

    min-height: 62px;

    padding: 8px 14px;

    gap: 12px;
}

.conductor-avatar {

    width: 42px;

    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: var(--azul);

    color: white;

    font-size: 17px;
}

.conductor-dato {
    min-width: 0;
}

.conductor-dato label {

    display: block;

    margin-bottom: 3px;

    color: #64748b;

    font-size: 8px;

    font-weight: 800;

    text-transform: uppercase;
}

.conductor-dato strong {

    font-size: 11px;

    font-weight: 700;
}


/* =========================================================
   INDICADORES
========================================================= */

.indicadores {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);
}

.indicador-box {

    padding: 10px 14px;

    border-right: 1px solid #e2e8f0;

    min-height: 55px;
}

.indicador-box:last-child {
    border-right: 0;
}

.indicador-label {

    display: block;

    margin-bottom: 5px;

    color: #64748b;

    font-size: 8px;

    font-weight: 800;

    text-transform: uppercase;
}

.indicador {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 38px;

    padding: 4px 9px;

    border-radius: 999px;

    font-size: 9px;

    font-weight: 800;
}

.indicador.si {

    color: #b91c1c;

    background: #fee2e2;
}

.indicador.no {

    color: #475569;

    background: #f1f5f9;
}


/* =========================================================
   TEXTOS
========================================================= */

.texto-grid {

    display: grid;

    grid-template-columns:
        1fr 1fr;
}

.texto-box {

    padding: 11px 14px;

    border-right: 1px solid #e2e8f0;
}

.texto-box:last-child {
    border-right: 0;
}

.texto-label {

    margin-bottom: 6px;

    color: #64748b;

    font-size: 8px;

    font-weight: 800;

    text-transform: uppercase;
}

.texto {

    min-height: 58px;

    padding: 8px 10px;

    background: #f8fafc;

    border: 1px solid #e2e8f0;

    border-radius: 6px;

    color: #334155;

    font-size: 9.5px;

    line-height: 1.45;

    white-space: pre-line;
}

.dato-vacio {

    color: #94a3b8;

    font-weight: 500;

    font-style: italic;
}


/* =========================================================
   ETIQUETAS
========================================================= */

.etiqueta {

    display: inline-flex;

    align-items: center;

    padding: 4px 8px;

    border-radius: 6px;

    font-size: 9px;

    font-weight: 800;
}

.etiqueta.azul {

    background: var(--azul-suave);

    color: var(--azul-oscuro);
}


/* =========================================================
   RESPONSABLES
========================================================= */

.responsables {

    display: grid;

    grid-template-columns:
        1fr 1fr;
}

.responsable {

    padding: 10px 14px;

    border-right: 1px solid #e2e8f0;
}

.responsable:last-child {
    border-right: 0;
}

.responsable label {

    display: block;

    margin-bottom: 4px;

    color: #64748b;

    font-size: 8px;

    font-weight: 800;

    text-transform: uppercase;
}

.responsable strong {
    font-size: 10px;
}


/* =========================================================
   PIE FORMULARIO
========================================================= */

.formulario-footer {

    padding: 10px 15px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    background: #f8fafc;

    color: #64748b;

    font-size: 8px;
}

.firma {

    width: 220px;

    padding-top: 18px;

    border-top: 1px solid #64748b;

    text-align: center;

    color: #475569;

    font-size: 8px;
}


/* =========================================================
   FOOTER WEB
========================================================= */

.footer {

    text-align: center;

    margin-top: 18px;

    color: #94a3b8;

    font-size: 10px;
}


/* =========================================================
   EVIDENCIA FOTOGRÁFICA
========================================================= */

.evidencia-contenedor {
    width: 100%;
}

.evidencia-vacia {

    padding: 28px;

    border: 1px dashed #cbd5e1;

    border-radius: 14px;

    background: #f8fafc;

    text-align: center;

    color: #64748b;
}

.evidencia-vacia i {

    display: block;

    font-size: 30px;

    margin-bottom: 10px;

    color: #94a3b8;
}

.evidencia-vacia strong {

    display: block;

    color: #475569;

    margin-bottom: 5px;
}

.evidencia-grid {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fill,
            minmax(220px, 1fr)
        );

    gap: 18px;

    padding: 18px;
}

.evidencia-card {

    position: relative;

    overflow: hidden;

    border: 1px solid #e2e8f0;

    border-radius: 16px;

    background: #ffffff;

    box-shadow:
        0 8px 22px
        rgba(
            15,
            23,
            42,
            .07
        );

    transition:
        transform .25s ease,
        box-shadow .25s ease;
}

.evidencia-card:hover {

    transform: translateY(-3px);

    box-shadow:
        0 14px 30px
        rgba(
            15,
            23,
            42,
            .12
        );
}

.evidencia-imagen {

    width: 100%;

    height: 210px;

    object-fit: cover;

    display: block;

    cursor: pointer;

    background: #f1f5f9;

    transition: opacity .2s ease;
}

.evidencia-imagen:hover {
    opacity: .94;
}

.evidencia-info {
    padding: 13px 15px;
}

.evidencia-nombre {

    display: block;

    font-size: 13px;

    font-weight: 700;

    color: #334155;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.evidencia-fecha {

    display: block;

    margin-top: 4px;

    font-size: 11px;

    color: #94a3b8;
}

.evidencia-fecha i {
    margin-right: 4px;
}

.evidencia-ver {

    position: absolute;

    top: 12px;

    right: 12px;

    width: 38px;

    height: 38px;

    border: none;

    border-radius: 50%;

    background:
        rgba(
            15,
            23,
            42,
            .78
        );

    color: #ffffff;

    display: flex;

    align-items: center;

    justify-content: center;

    cursor: pointer;

    transition: all .2s ease;

    z-index: 5;
}

.evidencia-ver:hover {

    background: #2563eb;

    transform: scale(1.08);
}


/* =========================================================
   MODAL VISOR DE IMAGEN
========================================================= */

.modal-imagen {

    display: none;

    position: fixed;

    z-index: 9999;

    inset: 0;

    background:
        rgba(
            15,
            23,
            42,
            .94
        );

    align-items: center;

    justify-content: center;

    padding: 30px;
}

.modal-imagen.activo {
    display: flex;
}

.modal-imagen-contenido {

    position: relative;

    width: auto;

    max-width: 95vw;

    max-height: 92vh;

    display: flex;

    align-items: center;

    justify-content: center;
}

.modal-imagen-contenido img {

    display: none;

    max-width: 92vw;

    max-height: 85vh;

    width: auto;

    height: auto;

    object-fit: contain;

    border-radius: 12px;

    background: #ffffff;

    box-shadow:
        0 20px 60px
        rgba(
            0,
            0,
            0,
            .45
        );
}

.modal-cerrar {

    position: fixed;

    top: 20px;

    right: 25px;

    width: 46px;

    height: 46px;

    border: none;

    border-radius: 50%;

    background:
        rgba(
            255,
            255,
            255,
            .12
        );

    color: #ffffff;

    font-size: 20px;

    cursor: pointer;

    z-index: 10000;

    transition: all .2s ease;
}

.modal-cerrar:hover {

    background: #ef4444;

    transform: rotate(90deg);
}

.modal-titulo {

    position: fixed;

    left: 25px;

    bottom: 20px;

    color: #ffffff;

    background:
        rgba(
            15,
            23,
            42,
            .78
        );

    padding: 10px 15px;

    border-radius: 10px;

    font-size: 13px;

    max-width:
        calc(
            100vw - 100px
        );

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;
}


/* =========================================================
   CARGANDO IMAGEN
========================================================= */

.cargando-imagen {

    display: none;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    gap: 12px;

    color: #ffffff;

    font-size: 14px;

    min-width: 220px;

    min-height: 120px;
}

.cargando-imagen i {
    font-size: 30px;
}


/* =========================================================
   ERROR IMAGEN
========================================================= */

.error-imagen {

    display: none;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    gap: 9px;

    min-width: 280px;

    padding: 35px;

    color: #ffffff;

    text-align: center;
}

.error-imagen i {

    font-size: 52px;

    opacity: .75;
}

.error-imagen strong {
    font-size: 16px;
}

.error-imagen span {

    color: #cbd5e1;

    font-size: 12px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .campo:nth-child(4n) {
        border-right:
            1px solid #e2e8f0;
    }

    .campo:nth-child(2n) {
        border-right: 0;
    }

    .conductor {
        grid-template-columns:
            50px 1fr 1fr;
    }

    .indicadores {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .indicador-box:nth-child(2) {
        border-right: 0;
    }

    .texto-grid {
        grid-template-columns: 1fr;
    }

    .texto-box {

        border-right: 0;

        border-bottom:
            1px solid #e2e8f0;
    }
}


@media (max-width: 700px) {

    .evidencia-grid {
        grid-template-columns: 1fr;
        padding: 12px;
    }

    .evidencia-imagen {
        height: 240px;
    }

    .modal-imagen {
        padding: 15px;
    }

    .modal-imagen-contenido {
        max-width: 100%;
        max-height: 90vh;
    }

    .modal-imagen-contenido img {

        max-width: 96vw;

        max-height: 80vh;
    }

    .modal-cerrar {

        top: 12px;

        right: 12px;

        width: 42px;

        height: 42px;
    }

    .modal-titulo {

        left: 12px;

        right: 65px;

        bottom: 12px;

        max-width: none;
    }
}


@media (max-width: 650px) {

    .contenedor {

        width: calc(100% - 20px);

        padding-top: 15px;
    }

    .cabecera {
        flex-direction: column;
    }

    .acciones {
        width: 100%;
    }

    .btn {
        flex: 1;
    }

    .formulario-header {

        flex-direction: column;

        align-items: flex-start;
    }

    .header-derecha {
        text-align: left;
    }

    .grid {
        grid-template-columns: 1fr;
    }

    .campo,
    .campo:nth-child(2n),
    .campo:nth-child(4n) {
        border-right: 0;
    }

    .conductor {
        grid-template-columns:
            48px 1fr;
    }

    .conductor-dato {
        grid-column: 2;
    }

    .indicadores {
        grid-template-columns: 1fr;
    }

    .indicador-box {

        border-right: 0;

        border-bottom:
            1px solid #e2e8f0;
    }

    .responsables {
        grid-template-columns: 1fr;
    }

    .responsable {

        border-right: 0;

        border-bottom:
            1px solid #e2e8f0;
    }
}


/* =========================================================
   IMPRESIÓN
========================================================= */

@page {
    size: A4 portrait;
    margin: 4mm;
}

@media print {

    html,
    body {

        width: 210mm !important;

        min-height: 297mm !important;

        margin: 0 !important;

        padding: 0 !important;

        background: #fff !important;

        color: #000 !important;
    }


    /* =====================================================
       OCULTAR ELEMENTOS WEB
    ===================================================== */

    body * {
        visibility: hidden !important;
    }

    .formulario,
    .formulario * {
        visibility: visible !important;
    }


    /* =====================================================
       FORMULARIO PRINCIPAL
    ===================================================== */

    .formulario {

        position: absolute !important;

        left: 0 !important;

        top: 0 !important;

        width: 100% !important;

        margin: 0 !important;

        padding: 0 !important;

        border: 1px solid #9ca3af !important;

        border-radius: 0 !important;

        box-shadow: none !important;

        background: #fff !important;

        overflow: visible !important;

        /*
         * IMPORTANTE:
         * No impedir que el formulario continúe
         * naturalmente hacia la siguiente página.
         */
        page-break-inside: auto !important;

        break-inside: auto !important;
    }


    /* =====================================================
       OCULTAR CABECERA Y FOOTER WEB
    ===================================================== */

    .cabecera,
    .footer {
        display: none !important;
    }


    /* =====================================================
       CABECERA DEL DOCUMENTO
    ===================================================== */

    .formulario-header {

        display: flex !important;

        align-items: center !important;

        padding: 7px 10px !important;

        min-height: 62px !important;

        border-bottom:
            2px solid #1e40af !important;

        background: #fff !important;

        page-break-inside: avoid !important;

        break-inside: avoid !important;
    }

    .logo-web {

        height: 45px !important;

        width: auto !important;

        max-width: 175px !important;
    }

    .header-derecha {

        margin-left: auto !important;
    }

    .header-derecha .titulo {

        font-size: 15px !important;

        line-height: 1.15 !important;

        font-weight: 800 !important;
    }

    .header-derecha .subtitulo {

        font-size: 8.5px !important;

        line-height: 1.2 !important;
    }

    .numero-documento {

        margin-top: 5px !important;

        padding: 4px 8px !important;

        font-size: 9px !important;

        border: 1px solid #93c5fd !important;

        background: #eff6ff !important;
    }


    /* =====================================================
       SECCIONES
    ===================================================== */

    .seccion {

        margin: 0 !important;

        /*
         * MUY IMPORTANTE:
         * Permitimos que una sección pueda continuar
         * naturalmente entre páginas.
         */
        page-break-inside: auto !important;

        break-inside: auto !important;
    }

    .seccion-titulo {

        padding: 5px 9px !important;

        font-size: 8.5px !important;

        line-height: 1.1 !important;

        font-weight: 800 !important;

        border-bottom:
            1px solid #cbd5e1 !important;

        background: #f1f5f9 !important;

        /*
         * Mantener el título junto al contenido.
         */
        page-break-after: avoid !important;

        break-after: avoid !important;
    }


    /* =====================================================
       GRID
    ===================================================== */

    .grid {

        grid-template-columns:
            repeat(4, 1fr) !important;

        width: 100% !important;

        page-break-inside: auto !important;

        break-inside: auto !important;
    }

    .campo {

        min-height: 39px !important;

        padding: 5px 7px !important;

        border-color: #d1d5db !important;
    }

    .campo-label {

        margin-bottom: 3px !important;

        font-size: 6.8px !important;

        line-height: 1.1 !important;

        font-weight: 700 !important;
    }

    .campo-valor {

        font-size: 9.2px !important;

        line-height: 1.2 !important;

        font-weight: 600 !important;
    }


    /* =====================================================
       CONDUCTOR
    ===================================================== */

    .conductor {

        display: grid !important;

        grid-template-columns:
            42px
            1.4fr
            1fr
            1fr !important;

        min-height: 49px !important;

        padding: 6px 9px !important;

        gap: 8px !important;

        page-break-inside: avoid !important;

        break-inside: avoid !important;
    }

    .conductor-avatar {

        width: 34px !important;

        height: 34px !important;

        font-size: 14px !important;
    }

    .conductor-dato label {

        margin-bottom: 3px !important;

        font-size: 6.8px !important;

        line-height: 1.1 !important;
    }

    .conductor-dato strong {

        font-size: 9.2px !important;

        line-height: 1.2 !important;
    }


    /* =====================================================
       INDICADORES
    ===================================================== */

    .indicadores {

        grid-template-columns:
            repeat(4, 1fr) !important;

        page-break-inside: avoid !important;

        break-inside: avoid !important;
    }

    .indicador-box {

        min-height: 45px !important;

        padding: 6px 8px !important;

        border-color: #d1d5db !important;
    }

    .indicador-label {

        margin-bottom: 4px !important;

        font-size: 6.8px !important;

        line-height: 1.1 !important;

        font-weight: 700 !important;
    }

    .indicador {

        min-width: 34px !important;

        padding: 4px 7px !important;

        font-size: 8px !important;

        line-height: 1 !important;
    }


    /* =====================================================
       TEXTOS
    ===================================================== */

    .texto-grid {

        grid-template-columns:
            1fr 1fr !important;

        width: 100% !important;

        page-break-inside: auto !important;

        break-inside: auto !important;
    }

    .texto-box {

        padding: 6px 8px !important;

        border-color: #d1d5db !important;

        page-break-inside: avoid !important;

        break-inside: avoid !important;
    }

    .texto-label {

        margin-bottom: 4px !important;

        font-size: 6.8px !important;

        line-height: 1.1 !important;

        font-weight: 700 !important;
    }

    .texto {

        min-height: 45px !important;

        padding: 6px 7px !important;

        border-radius: 3px !important;

        font-size: 8.6px !important;

        line-height: 1.25 !important;

        background: #fafafa !important;
    }


    /* =====================================================
       RESPONSABLES
    ===================================================== */

    .responsables {

        grid-template-columns:
            1fr 1fr !important;

        page-break-inside: avoid !important;

        break-inside: avoid !important;
    }

    .responsable {

        padding: 6px 8px !important;

        min-height: 42px !important;

        border-color: #d1d5db !important;
    }

    .responsable label {

        margin-bottom: 3px !important;

        font-size: 6.8px !important;

        line-height: 1.1 !important;
    }

    .responsable strong {

        font-size: 9px !important;

        line-height: 1.2 !important;
    }


    /* =====================================================
       FOOTER DEL FORMULARIO
    ===================================================== */

    .formulario-footer {

        display: flex !important;

        align-items: center !important;

        padding: 7px 9px !important;

        min-height: 45px !important;

        background: #fff !important;

        font-size: 7px !important;

        line-height: 1.2 !important;

        page-break-inside: avoid !important;

        break-inside: avoid !important;
    }

    .firma {

        width: 190px !important;

        padding-top: 14px !important;

        font-size: 7px !important;
    }


    /* =====================================================
       ETIQUETAS
    ===================================================== */

    .etiqueta {

        padding: 3px 6px !important;

        font-size: 8px !important;

        line-height: 1 !important;
    }

    .dato-vacio {
        color: #555 !important;
    }


    /* =====================================================
       EVIDENCIA FOTOGRÁFICA
    ===================================================== */

    .evidencia-contenedor {

        width: 100% !important;

        margin: 0 !important;

        padding: 0 !important;
    }


    /*
     * IMPORTANTE:
     *
     * Dejamos una sola columna en impresión.
     *
     * Así la fotografía puede utilizar prácticamente
     * todo el ancho de la hoja y no queda pequeña.
     */
    .evidencia-grid {

        display: block !important;

        width: 100% !important;

        padding: 8px !important;

        margin: 0 !important;

        page-break-inside: auto !important;

        break-inside: auto !important;
    }


    /*
     * Cada fotografía se mantiene completa.
     * Nunca se divide entre dos páginas.
     */
    .evidencia-card {

        width: 100% !important;

        margin: 0 0 12px 0 !important;

        padding: 0 !important;

        border: 1px solid #d1d5db !important;

        border-radius: 4px !important;

        box-shadow: none !important;

        background: #fff !important;

        overflow: visible !important;

        page-break-inside: avoid !important;

        break-inside: avoid !important;
    }


    /*
     * LA PARTE MÁS IMPORTANTE
     *
     * La imagen mantiene su proporción original.
     * No usamos height:150px.
     * No usamos object-fit:cover.
     *
     * De esta forma no se corta.
     */
    .evidencia-imagen {

        display: block !important;

        width: 100% !important;

        height: auto !important;

        max-width: 100% !important;

        max-height: 235mm !important;

        margin: 0 auto !important;

        object-fit: contain !important;

        background: #fff !important;

        page-break-inside: avoid !important;

        break-inside: avoid !important;
    }


    /*
     * Información de la fotografía
     */
    .evidencia-info {

        padding: 5px 8px !important;

        background: #fff !important;

        font-size: 8px !important;
    }

    .evidencia-nombre {

        font-size: 8px !important;

        font-weight: 700 !important;

        white-space: normal !important;

        overflow: visible !important;

        text-overflow: clip !important;
    }

    .evidencia-fecha {

        margin-top: 2px !important;

        font-size: 7px !important;
    }


    /*
     * Ocultar botón de ampliar.
     */
    .evidencia-ver,
    .modal-imagen {

        display: none !important;
    }


    /* =====================================================
       EVITAR SALTOS INNECESARIOS
    ===================================================== */

    .evidencia-card img {

        page-break-inside: avoid !important;

        break-inside: avoid !important;
    }


    /*
     * El título "Evidencia fotográfica" debe quedar
     * junto a la primera fotografía cuando sea posible.
     */
    .seccion:has(.evidencia-grid) {

        page-break-inside: auto !important;

        break-inside: auto !important;
    }


    /* =====================================================
       CALIDAD DE IMPRESIÓN
    ===================================================== */

    * {

        -webkit-print-color-adjust:
            exact !important;

        print-color-adjust:
            exact !important;
    }
}

</style>

</head>

<body>

<div class="contenedor">


<!-- =================================================
     CABECERA WEB
================================================== -->

<div class="cabecera">

    <div class="titulo-area">

        <div class="badge-incidente">

            <i class="fa-solid fa-triangle-exclamation"></i>

            Registro de incidente

        </div>

        <h1>
            <?= escapar($titulo) ?>
        </h1>

        <p>
            Detalle completo del incidente registrado
        </p>

    </div>


    <div class="acciones">

        <a
            href="index.php"
            class="btn btn-volver"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Volver

        </a>


        <a
            href="editar_incidente.php?id=<?= $id ?>"
            class="btn btn-editar"
        >

            <i class="fa-solid fa-pen"></i>

            Editar

        </a>


        <button
            type="button"
            class="btn btn-imprimir"
            onclick="window.print()"
        >

            <i class="fa-solid fa-print"></i>

            Imprimir formulario

        </button>

    </div>

</div>


<!-- =================================================
     FORMULARIO
================================================== -->

<div class="formulario">


    <!-- =================================================
         ENCABEZADO CORPORATIVO
    ================================================== -->

    <div class="formulario-header">

        <div>

            <img
                src="/errores/logo.png"
                alt="Logo empresa"
                class="logo-web"
            >

        </div>


        <div class="header-derecha">

            <div class="titulo">
                REGISTRO DE INCIDENTE
            </div>

            <div class="subtitulo">
                Sistema de Gestión de Incidentes
            </div>

            <div class="numero-documento">

                <i class="fa-solid fa-file-circle-exclamation"></i>

                INCIDENTE
                #<?= escapar($identificador) ?>
                /
                <?= escapar($anio) ?>

            </div>

        </div>

    </div>


    <!-- =================================================
         IDENTIFICACIÓN
    ================================================== -->

    <div class="seccion">

        <div class="seccion-titulo">

            <span>
                <i class="fa-solid fa-circle-info"></i>
                Identificación del incidente
            </span>

        </div>


        <div class="grid">

            <div class="campo">

                <div class="campo-label">
                    N° Incidente
                </div>

                <div class="campo-valor">

                    <span class="etiqueta azul">
                        #<?= escapar($identificador) ?>
                    </span>

                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Año
                </div>

                <div class="campo-valor">
                    <?= mostrar($anio) ?>
                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Fecha ocurrencia
                </div>

                <div class="campo-valor">

                    <?= fecha_hora(
                        $incidente['fecha_hora_ocurrencia']
                    ) ?>

                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Fecha / hora evento
                </div>

                <div class="campo-valor">

                    <?= fecha_hora(
                        $incidente['fecha_hora_evento']
                    ) ?>

                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Fecha / hora aviso
                </div>

                <div class="campo-valor">

                    <?= fecha_hora(
                        $incidente['fecha_hora_aviso']
                    ) ?>

                </div>

            </div>


            <div class="campo campo-completo">

                <div class="campo-label">
                    Antecedentes
                </div>

                <div class="campo-valor">

                    <?= mostrar(
                        $incidente['antecedentes']
                    ) ?>

                </div>

            </div>

        </div>

    </div>


    <!-- =================================================
         CONDUCTOR
    ================================================== -->

    <div class="seccion">

        <div class="seccion-titulo">

            <span>
                <i class="fa-solid fa-user"></i>
                Conductor involucrado
            </span>

        </div>


        <?php if ($conductor_nombre !== ''): ?>

            <div class="conductor">

                <div class="conductor-avatar">

                    <i class="fa-solid fa-user"></i>

                </div>


                <div class="conductor-dato">

                    <label>
                        Nombre
                    </label>

                    <strong>
                        <?= escapar($conductor_nombre) ?>
                    </strong>

                </div>


                <div class="conductor-dato">

                    <label>
                        RUT
                    </label>

                    <strong>
                        <?= mostrar($conductor_rut) ?>
                    </strong>

                </div>


                <div class="conductor-dato">

                    <label>
                        Área
                    </label>

                    <strong>
                        <?= mostrar($conductor_area) ?>
                    </strong>

                </div>

            </div>

        <?php else: ?>

            <div class="conductor">

                <div class="conductor-avatar">

                    <i class="fa-solid fa-user-slash"></i>

                </div>


                <div class="conductor-dato">

                    <label>
                        Conductor
                    </label>

                    <strong class="dato-vacio">
                        No existe un conductor asociado
                    </strong>

                </div>

            </div>

        <?php endif; ?>

    </div>


    <!-- =================================================
         INFORMACIÓN ORGANIZACIONAL
    ================================================== -->

    <div class="seccion">

        <div class="seccion-titulo">

            <span>
                <i class="fa-solid fa-building"></i>
                Información organizacional
            </span>

        </div>


        <div class="grid">

            <div class="campo">

                <div class="campo-label">
                    División
                </div>

                <div class="campo-valor">
                    <?= mostrar($incidente['division']) ?>
                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Subdivisión
                </div>

                <div class="campo-valor">
                    <?= mostrar($incidente['subdivision']) ?>
                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Planta
                </div>

                <div class="campo-valor">
                    <?= mostrar($incidente['planta']) ?>
                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Sucursal
                </div>

                <div class="campo-valor">
                    <?= mostrar($incidente['sucursal']) ?>
                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Empresa del evento
                </div>

                <div class="campo-valor">
                    <?= mostrar($incidente['empresa_evento']) ?>
                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Área
                </div>

                <div class="campo-valor">
                    <?= mostrar($incidente['area']) ?>
                </div>

            </div>

        </div>

    </div>


    <!-- =================================================
         EQUIPO Y UBICACIÓN
    ================================================== -->

    <div class="seccion">

        <div class="seccion-titulo">

            <span>
                <i class="fa-solid fa-truck"></i>
                Equipo y ubicación
            </span>

        </div>


        <div class="grid">

            <div class="campo">

                <div class="campo-label">
                    Equipo
                </div>

                <div class="campo-valor">
                    <?= mostrar($incidente['equipo']) ?>
                </div>

            </div>


            <div class="campo campo-completo">

                <div class="campo-label">
                    Lugar del incidente
                </div>

                <div class="campo-valor">
                    <?= mostrar($incidente['lugar_incidente']) ?>
                </div>

            </div>

        </div>

    </div>


    <!-- =================================================
         CLASIFICACIÓN
    ================================================== -->

    <div class="seccion">

        <div class="seccion-titulo">

            <span>
                <i class="fa-solid fa-tags"></i>
                Clasificación del incidente
            </span>

        </div>


        <div class="grid">

            <div class="campo">

                <div class="campo-label">
                    Clasificación
                </div>

                <div class="campo-valor">

                    <?= mostrar(
                        $incidente['clasificacion_incidente']
                    ) ?>

                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Exposición LGF
                </div>

                <div class="campo-valor">

                    <?= mostrar(
                        $incidente['exposicion_lgf']
                    ) ?>

                </div>

            </div>


            <div class="campo campo-completo">

                <div class="campo-label">
                    Riesgo asociado
                </div>

                <div class="campo-valor">

                    <?= mostrar(
                        $incidente['riesgo_asociado']
                    ) ?>

                </div>

            </div>

        </div>

    </div>


    <!-- =================================================
         INDICADORES
    ================================================== -->

    <div class="seccion">

        <div class="seccion-titulo">

            <span>
                <i class="fa-solid fa-chart-simple"></i>
                Indicadores del incidente
            </span>

        </div>


        <div class="indicadores">

            <div class="indicador-box">

                <span class="indicador-label">
                    Daño a propiedad
                </span>

                <?= si_no(
                    $incidente['dano_propiedad']
                ) ?>

            </div>


            <div class="indicador-box">

                <span class="indicador-label">
                    Daño a personas
                </span>

                <?= si_no(
                    $incidente['dano_personas']
                ) ?>

            </div>


            <div class="indicador-box">

                <span class="indicador-label">
                    Emergencia
                </span>

                <?= si_no(
                    $incidente['emergencia']
                ) ?>

            </div>


            <div class="indicador-box">

                <span class="indicador-label">
                    Accidente grave / fatal
                </span>

                <?= si_no(
                    $incidente['accidente_grave_fatal']
                ) ?>

            </div>

        </div>

    </div>


    <!-- =================================================
         PERSONA AFECTADA
    ================================================== -->

    <div class="seccion">

        <div class="seccion-titulo">

            <span>
                <i class="fa-solid fa-person"></i>
                Persona afectada
            </span>

        </div>


        <div class="grid">

            <div class="campo">

                <div class="campo-label">
                    Nombre
                </div>

                <div class="campo-valor">

                    <?= mostrar(
                        $incidente['afectado_nombre']
                    ) ?>

                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    RUT
                </div>

                <div class="campo-valor">

                    <?= mostrar(
                        $incidente['afectado_rut']
                    ) ?>

                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Sexo
                </div>

                <div class="campo-valor">

                    <?= mostrar(
                        $incidente['afectado_sexo']
                    ) ?>

                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Edad
                </div>

                <div class="campo-valor">

                    <?php if (
                        $incidente['afectado_edad'] !== null &&
                        $incidente['afectado_edad'] !== ''
                    ): ?>

                        <?= escapar(
                            $incidente['afectado_edad']
                        ) ?>

                        años

                    <?php else: ?>

                        <span class="dato-vacio">
                            No informado
                        </span>

                    <?php endif; ?>

                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Empresa involucrada
                </div>

                <div class="campo-valor">

                    <?= mostrar(
                        $incidente['empresa_involucrada']
                    ) ?>

                </div>

            </div>


            <div class="campo">

                <div class="campo-label">
                    Cargo
                </div>

                <div class="campo-valor">

                    <?= mostrar(
                        $incidente['cargo']
                    ) ?>

                </div>

            </div>


            <div class="campo campo-completo">

                <div class="campo-label">
                    Partes del cuerpo afectadas
                </div>

                <div class="campo-valor">

                    <?= mostrar(
                        $incidente['partes_cuerpo']
                    ) ?>

                </div>

            </div>

        </div>

    </div>


    <!-- =================================================
         DESCRIPCIÓN
    ================================================== -->

    <div class="seccion">

        <div class="seccion-titulo">

            <span>
                <i class="fa-solid fa-file-lines"></i>
                Descripción del incidente
            </span>

        </div>


        <div class="texto-grid">

            <div class="texto-box">

                <div class="texto-label">
                    Detalle del incidente
                </div>

                <div class="texto">

                    <?= nl2br(
                        mostrar(
                            $incidente['detalle_incidente']
                        )
                    ) ?>

                </div>

            </div>


            <div class="texto-box">

                <div class="texto-label">
                    Descripción preliminar
                </div>

                <div class="texto">

                    <?= nl2br(
                        mostrar(
                            $incidente['descripcion_preliminar']
                        )
                    ) ?>

                </div>

            </div>

        </div>

    </div>


    <!-- =================================================
     EVIDENCIA FOTOGRÁFICA
================================================== -->

<section class="seccion">

    <div class="seccion-titulo">

        <span>
            <i class="fa-solid fa-camera"></i>
            Evidencia fotográfica
        </span>


        <?php if (count($fotos) > 0): ?>

            <span class="badge-incidente">

                <?= count($fotos) ?>

                <?= count($fotos) === 1
                    ? 'fotografía'
                    : 'fotografías'
                ?>

            </span>

        <?php endif; ?>

    </div>


    <div class="evidencia-contenedor">

        <?php if (empty($fotos)): ?>

            <div class="evidencia-vacia">

                <i class="fa-regular fa-image"></i>

                <strong>
                    No hay fotografías asociadas
                </strong>

                <span>
                    Este incidente no tiene evidencia fotográfica registrada.
                </span>

            </div>

        <?php else: ?>

            <div class="evidencia-grid">

                <?php foreach ($fotos as $foto): ?>

                    <?php

                    /*
                     * ==========================================
                     * DATOS DE LA FOTOGRAFÍA
                     * ==========================================
                     */

                    $rutaFoto = trim(
                        (string)(
                            $foto['ruta_web'] ?? ''
                        )
                    );


                    $nombreFoto = trim(
                        (string)(
                            $foto['nombre_original'] ?? ''
                        )
                    );


                    /*
                     * Si no existe nombre original,
                     * usamos el nombre almacenado.
                     */

                    if ($nombreFoto === '') {

                        $nombreFoto = trim(
                            (string)(
                                $foto['archivo'] ?? ''
                            )
                        );
                    }


                    if ($nombreFoto === '') {

                        $nombreFoto =
                            'Evidencia fotográfica';
                    }


                    /*
                     * ==========================================
                     * FECHA
                     * ==========================================
                     */

                    $fechaFoto = '';

                    if (
                        !empty(
                            $foto['fecha_subida']
                        )
                    ) {

                        $timestampFoto =
                            strtotime(
                                $foto['fecha_subida']
                            );

                        if (
                            $timestampFoto !== false
                        ) {

                            $fechaFoto =
                                date(
                                    'd/m/Y H:i',
                                    $timestampFoto
                                );
                        }
                    }


                    /*
                     * ==========================================
                     * EXISTENCIA FÍSICA
                     * ==========================================
                     */

                    $existeFisicamente =
                        !empty(
                            $foto['existe']
                        );

                    ?>


                    <?php if (
                        $rutaFoto !== '' &&
                        $existeFisicamente
                    ): ?>

                        <div class="evidencia-card">

                            <img
                                src="<?= escapar($rutaFoto) ?>"
                                alt="<?= escapar($nombreFoto) ?>"
                                class="evidencia-imagen"
                                loading="eager"
                                onclick="abrirImagenDesdeTarjeta(this)"
                            >


                            <button
                                type="button"
                                class="evidencia-ver"
                                title="Ver imagen"
                                onclick="abrirImagenDesdeBoton(this)"
                            >

                                <i class="fa-solid fa-expand"></i>

                            </button>


                            <div class="evidencia-info">

                                <span class="evidencia-nombre">

                                    <?= escapar(
                                        $nombreFoto
                                    ) ?>

                                </span>


                                <?php if (
                                    $fechaFoto !== ''
                                ): ?>

                                    <span class="evidencia-fecha">

                                        <i class="fa-regular fa-clock"></i>

                                        <?= escapar(
                                            $fechaFoto
                                        ) ?>

                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>


                    <?php else: ?>

                        <div class="evidencia-card">

                            <div
                                style="
                                    min-height:210px;
                                    display:flex;
                                    flex-direction:column;
                                    align-items:center;
                                    justify-content:center;
                                    padding:25px;
                                    text-align:center;
                                    background:#f8fafc;
                                    color:#64748b;
                                "
                            >

                                <i
                                    class="fa-regular fa-image"
                                    style="
                                        font-size:35px;
                                        margin-bottom:10px;
                                        color:#94a3b8;
                                    "
                                ></i>


                                <strong>
                                    No se pudo cargar la fotografía
                                </strong>


                                <small
                                    style="
                                        margin-top:8px;
                                        word-break:break-all;
                                    "
                                >
                                    <?= escapar(
                                        $foto['archivo'] ?? ''
                                    ) ?>
                                </small>

                            </div>


                            <div class="evidencia-info">

                                <span class="evidencia-nombre">

                                    <?= escapar(
                                        $nombreFoto
                                    ) ?>

                                </span>

                            </div>

                        </div>

                    <?php endif; ?>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</section>


    <!-- =================================================
         RESPONSABLES
    ================================================== -->

    <div class="seccion">

        <div class="seccion-titulo">

            <span>
                <i class="fa-solid fa-user-check"></i>
                Responsables y aviso
            </span>

        </div>


        <div class="responsables">

            <div class="responsable">

                <label>
                    Avisado por
                </label>

                <strong>

                    <?= mostrar(
                        $incidente['avisado_por']
                    ) ?>

                </strong>

            </div>


            <div class="responsable">

                <label>
                    Realizado por
                </label>

                <strong>

                    <?= mostrar(
                        $incidente['realizado_por']
                    ) ?>

                </strong>

            </div>

        </div>

    </div>


    <!-- =================================================
         PIE FORMULARIO
    ================================================== -->

    <div class="formulario-footer">

        <div>

            Sistema de Gestión de Incidentes
            ·
            Registro #<?= escapar($identificador) ?>
            /
            <?= escapar($anio) ?>

        </div>


        <div class="firma">

            Responsable del registro

        </div>

    </div>


</div>


<!-- =================================================
     FOOTER WEB
================================================== -->

<div class="footer">

    Sistema de Incidentes ·
    Registro #<?= escapar($identificador) ?>
    /
    <?= escapar($anio) ?>

</div>


</div>

<!-- =========================================================
     MODAL VISOR DE EVIDENCIA
========================================================= -->

<div
    id="modalImagenIncidente"
    class="modal-imagen"
    onclick="cerrarImagenIncidente(event)"
>


<button
    type="button"
    class="modal-cerrar"
    onclick="cerrarModalImagen()"
    title="Cerrar"
>

    <i class="fa-solid fa-xmark"></i>

</button>


<div class="modal-imagen-contenido">


    <div
        id="cargandoImagenIncidente"
        class="cargando-imagen"
    >

        <i class="fa-solid fa-spinner fa-spin"></i>

        <span>
            Cargando imagen...
        </span>

    </div>


    <img
        id="imagenIncidenteGrande"
        src=""
        alt="Evidencia del incidente"
    >


    <div
        id="errorImagenIncidente"
        class="error-imagen"
    >

        <i class="fa-regular fa-image"></i>

        <strong>
            No se pudo cargar la imagen
        </strong>

        <span>
            Verifique que el archivo exista en el servidor.
        </span>

    </div>

</div>


<div
    id="tituloImagenIncidente"
    class="modal-titulo"
></div>


</div>

<script>

/* =========================================================
   OBTENER RUTA DESDE LA MINIATURA
========================================================= */

function obtenerRutaImagen(imagen) {

    if (!imagen) {
        return '';
    }

    /*
     * Tomamos directamente la URL que utiliza
     * el navegador.
     */

    return (
        imagen.currentSrc ||
        imagen.src ||
        ''
    );
}


/* =========================================================
   ABRIR DESDE LA MINIATURA
========================================================= */

function abrirImagenDesdeTarjeta(imagenMiniatura) {

    if (!imagenMiniatura) {
        return;
    }

    const ruta =
        obtenerRutaImagen(
            imagenMiniatura
        );

    const nombre =
        imagenMiniatura.alt ||
        'Evidencia del incidente';


    if (!ruta) {

        console.error(
            'No se pudo obtener la ruta de la imagen.'
        );

        return;
    }


    abrirImagenIncidente(
        ruta,
        nombre
    );
}


/* =========================================================
   ABRIR DESDE EL BOTÓN
========================================================= */

function abrirImagenDesdeBoton(boton) {

    if (!boton) {
        return;
    }


    const tarjeta =
        boton.closest(
            '.evidencia-card'
        );


    if (!tarjeta) {

        console.error(
            'No se encontró la tarjeta de evidencia.'
        );

        return;
    }


    const imagenMiniatura =
        tarjeta.querySelector(
            '.evidencia-imagen'
        );


    if (!imagenMiniatura) {

        console.error(
            'No se encontró la imagen de evidencia.'
        );

        return;
    }


    const ruta =
        obtenerRutaImagen(
            imagenMiniatura
        );


    const nombre =
        imagenMiniatura.alt ||
        'Evidencia del incidente';


    if (!ruta) {

        console.error(
            'No se pudo obtener la ruta de la imagen.'
        );

        return;
    }


    abrirImagenIncidente(
        ruta,
        nombre
    );
}


/* =========================================================
   ABRIR MODAL
========================================================= */

function abrirImagenIncidente(ruta, nombre) {

    const modal =
        document.getElementById(
            'modalImagenIncidente'
        );

    const imagen =
        document.getElementById(
            'imagenIncidenteGrande'
        );

    const titulo =
        document.getElementById(
            'tituloImagenIncidente'
        );

    const cargando =
        document.getElementById(
            'cargandoImagenIncidente'
        );

    const error =
        document.getElementById(
            'errorImagenIncidente'
        );


    if (
        !modal ||
        !imagen ||
        !titulo ||
        !cargando ||
        !error
    ) {

        console.error(
            'No se encontraron los elementos del visor.'
        );

        return;
    }


    if (!ruta) {

        console.error(
            'No se recibió una ruta válida.'
        );

        return;
    }


    console.log(
        'ABRIENDO IMAGEN:',
        ruta
    );


    imagen.onload = null;
    imagen.onerror = null;

    imagen.style.display = 'none';

    cargando.style.display = 'flex';

    error.style.display = 'none';


    titulo.textContent =
        nombre ||
        'Evidencia del incidente';


    modal.classList.add('activo');

    document.body.style.overflow = 'hidden';


    imagen.removeAttribute('src');


    /* =====================================================
       CARGA CORRECTA
    ===================================================== */

    imagen.onload = function() {

        console.log(
            'IMAGEN CARGADA CORRECTAMENTE:',
            imagen.currentSrc ||
            imagen.src
        );


        cargando.style.display =
            'none';

        error.style.display =
            'none';

        imagen.style.display =
            'block';
    };


    /* =====================================================
       ERROR
    ===================================================== */

    imagen.onerror = function() {

        console.error(
            'ERROR AL CARGAR IMAGEN:',
            ruta
        );


        cargando.style.display =
            'none';

        imagen.style.display =
            'none';

        error.style.display =
            'flex';
    };


    /*
     * Cache-busting leve para evitar que el navegador
     * mantenga una imagen antigua después de reemplazarla.
     */

    const separador =
        ruta.includes('?')
            ? '&'
            : '?';

    imagen.src =
        ruta +
        separador +
        'v=' +
        Date.now();
}


/* =========================================================
   CERRAR MODAL
========================================================= */

function cerrarModalImagen() {

    const modal =
        document.getElementById(
            'modalImagenIncidente'
        );

    const imagen =
        document.getElementById(
            'imagenIncidenteGrande'
        );

    const cargando =
        document.getElementById(
            'cargandoImagenIncidente'
        );

    const error =
        document.getElementById(
            'errorImagenIncidente'
        );


    if (modal) {

        modal.classList.remove(
            'activo'
        );
    }


    if (imagen) {

        imagen.onload = null;
        imagen.onerror = null;

        imagen.removeAttribute(
            'src'
        );

        imagen.style.display =
            'none';
    }


    if (cargando) {

        cargando.style.display =
            'none';
    }


    if (error) {

        error.style.display =
            'none';
    }


    document.body.style.overflow =
        '';
}


/* =========================================================
   CERRAR AL HACER CLICK EN EL FONDO
========================================================= */

function cerrarImagenIncidente(event) {

    if (
        event &&
        event.target &&
        event.target.id ===
        'modalImagenIncidente'
    ) {

        cerrarModalImagen();
    }
}


/* =========================================================
   ESC PARA CERRAR
========================================================= */

document.addEventListener(
    'keydown',
    function(event) {

        if (
            event.key === 'Escape'
        ) {

            cerrarModalImagen();
        }

    }
);


/* =========================================================
   EVITAR CIERRE AL HACER CLICK EN CONTENIDO
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function() {

        const contenido =
            document.querySelector(
                '.modal-imagen-contenido'
            );


        if (contenido) {

            contenido.addEventListener(
                'click',
                function(event) {

                    event.stopPropagation();

                }
            );
        }

    }
);

</script>

</body>

</html>
