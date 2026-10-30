<?php

/* =========================================================
   CONEXIÓN
   ========================================================= */

require_once __DIR__ . '/../errores/db.php';

date_default_timezone_set('America/Santiago');


/* =========================================================
   VALIDAR CONEXIÓN
   ========================================================= */

if (!isset($conn) || !($conn instanceof mysqli)) {

    if (
        isset($conexion) &&
        $conexion instanceof mysqli
    ) {

        $conn = $conexion;

    }

}


if (!isset($conn) || !($conn instanceof mysqli)) {

    die(
        'No se pudo establecer una conexión válida con la base de datos.'
    );

}


/* =========================================================
   FUNCIONES
   ========================================================= */

function e($valor)
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}


function fechaChile($fecha)
{
    if (
        empty($fecha) ||
        $fecha === '0000-00-00 00:00:00'
    ) {
        return '—';
    }

    $timestamp = strtotime($fecha);

    if (!$timestamp) {
        return '—';
    }

    return date(
        'd-m-Y H:i',
        $timestamp
    );
}


function fechaSolo($fecha)
{
    if (
        empty($fecha) ||
        $fecha === '0000-00-00 00:00:00'
    ) {
        return '—';
    }

    $timestamp = strtotime($fecha);

    if (!$timestamp) {
        return '—';
    }

    return date(
        'd-m-Y',
        $timestamp
    );
}


function numeroIncidente($numero)
{
    return str_pad(
        (string)$numero,
        4,
        '0',
        STR_PAD_LEFT
    );
}


function badgeClasificacion($clasificacion)
{
    if (empty($clasificacion)) {

        return '
            <span class="badge badge-neutral">
                Sin clasificar
            </span>
        ';

    }

    return '
        <span class="badge badge-blue">
            ' . e($clasificacion) . '
        </span>
    ';
}


/* =========================================================
   DATOS BÁSICOS
   ========================================================= */

$anioActual = (int)date('Y');


/* =========================================================
   FILTROS
   ========================================================= */

$busqueda =
    trim($_GET['buscar'] ?? '');

$anioFiltro =
    trim($_GET['anio'] ?? '');

$clasificacionFiltro =
    trim($_GET['clasificacion'] ?? '');

$conductorFiltro =
    (int)($_GET['conductor_id'] ?? 0);


/* =========================================================
   PRÓXIMO NÚMERO DE INCIDENTE
   ========================================================= */

$proximoNumero = 1;


$stmtNumero = $conn->prepare("
    SELECT
        COALESCE(
            MAX(numero_incidente),
            0
        ) + 1 AS siguiente
    FROM incidentes
    WHERE anio_incidente = ?
");


if ($stmtNumero) {

    $stmtNumero->bind_param(
        'i',
        $anioActual
    );

    $stmtNumero->execute();

    $resNumero =
        $stmtNumero->get_result();

    if ($filaNumero =
        $resNumero->fetch_assoc()
    ) {

        $proximoNumero =
            (int)$filaNumero['siguiente'];

    }

    $stmtNumero->close();

}


/* =========================================================
   CONSULTA PRINCIPAL
   ========================================================= */

$sql = "

    SELECT

        i.id,
        i.numero_incidente,
        i.anio_incidente,

        i.conductor_id,

        i.fecha_hora_ocurrencia,
        i.fecha_hora_evento,
        i.fecha_hora_aviso,

        i.antecedentes,
        i.division,
        i.subdivision,
        i.planta,
        i.sucursal,

        i.empresa_evento,
        i.area,

        i.equipo,
        i.lugar_incidente,

        i.clasificacion_incidente,
        i.exposicion_lgf,
        i.riesgo_asociado,

        i.dano_propiedad,
        i.dano_personas,

        i.emergencia,
        i.accidente_grave_fatal,

        i.afectado_nombre,
        i.afectado_rut,
        i.afectado_sexo,
        i.afectado_edad,

        i.empresa_involucrada,
        i.cargo,

        i.partes_cuerpo,

        i.detalle_incidente,
        i.descripcion_preliminar,

        i.avisado_por,
        i.realizado_por,

        i.fecha_registro,

        c.rut AS conductor_rut,
        c.nombre AS conductor_nombre,
        c.area AS conductor_area,

        (
            SELECT COUNT(*)
            FROM incidentes_fotos f
            WHERE f.incidente_id = i.id
        ) AS cantidad_fotos

    FROM incidentes i

    LEFT JOIN conductores c
        ON c.id = i.conductor_id

    WHERE 1 = 1

";


$params = [];
$types = '';


/* =========================================================
   BÚSQUEDA
   ========================================================= */

if ($busqueda !== '') {

    $sql .= "

        AND (

            CAST(
                i.numero_incidente
                AS CHAR
            ) LIKE ?

            OR CAST(
                i.anio_incidente
                AS CHAR
            ) LIKE ?

            OR c.rut LIKE ?

            OR c.nombre LIKE ?

            OR i.lugar_incidente LIKE ?

            OR i.equipo LIKE ?

            OR i.clasificacion_incidente LIKE ?

            OR i.antecedentes LIKE ?

        )

    ";

    $like =
        '%' . $busqueda . '%';


    for ($x = 0; $x < 8; $x++) {

        $params[] =
            $like;

        $types .= 's';

    }

}


/* =========================================================
   FILTRO AÑO
   ========================================================= */

if ($anioFiltro !== '') {

    $sql .= "
        AND i.anio_incidente = ?
    ";

    $params[] =
        (int)$anioFiltro;

    $types .= 'i';

}


/* =========================================================
   FILTRO CLASIFICACIÓN
   ========================================================= */

if ($clasificacionFiltro !== '') {

    $sql .= "
        AND i.clasificacion_incidente = ?
    ";

    $params[] =
        $clasificacionFiltro;

    $types .= 's';

}


/* =========================================================
   FILTRO CONDUCTOR
   ========================================================= */

if ($conductorFiltro > 0) {

    $sql .= "
        AND i.conductor_id = ?
    ";

    $params[] =
        $conductorFiltro;

    $types .= 'i';

}


/* =========================================================
   ORDEN
   ========================================================= */

$sql .= "

    ORDER BY
        i.fecha_hora_ocurrencia DESC,
        i.id DESC

";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die(
        'Error preparando la consulta: ' .
        e($conn->error)
    );

}


if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );

}


if (!$stmt->execute()) {

    die(
        'Error ejecutando la consulta: ' .
        e($stmt->error)
    );

}


$resultado =
    $stmt->get_result();


/* =========================================================
   ESTADÍSTICAS
   ========================================================= */

$totalIncidentes = 0;
$incidentesAnio = 0;
$totalEmergencias = 0;
$totalGraves = 0;
$totalPersonas = 0;
$totalPropiedad = 0;


/* TOTAL */

$resTotal =
    $conn->query("
        SELECT COUNT(*) AS total
        FROM incidentes
    ");

if ($resTotal) {

    $fila =
        $resTotal->fetch_assoc();

    $totalIncidentes =
        (int)($fila['total'] ?? 0);

}


/* AÑO ACTUAL */

$stmtAnio =
    $conn->prepare("
        SELECT COUNT(*) AS total
        FROM incidentes
        WHERE anio_incidente = ?
    ");

if ($stmtAnio) {

    $stmtAnio->bind_param(
        'i',
        $anioActual
    );

    $stmtAnio->execute();

    $res =
        $stmtAnio->get_result();

    $fila =
        $res->fetch_assoc();

    $incidentesAnio =
        (int)($fila['total'] ?? 0);

    $stmtAnio->close();

}


/* EMERGENCIAS */

$res =
    $conn->query("
        SELECT COUNT(*) AS total
        FROM incidentes
        WHERE emergencia = 1
    ");

if ($res) {

    $fila =
        $res->fetch_assoc();

    $totalEmergencias =
        (int)($fila['total'] ?? 0);

}


/* GRAVES / FATALES */

$res =
    $conn->query("
        SELECT COUNT(*) AS total
        FROM incidentes
        WHERE accidente_grave_fatal = 1
    ");

if ($res) {

    $fila =
        $res->fetch_assoc();

    $totalGraves =
        (int)($fila['total'] ?? 0);

}


/* DAÑO PERSONAS */

$res =
    $conn->query("
        SELECT COUNT(*) AS total
        FROM incidentes
        WHERE dano_personas = 1
    ");

if ($res) {

    $fila =
        $res->fetch_assoc();

    $totalPersonas =
        (int)($fila['total'] ?? 0);

}


/* DAÑO PROPIEDAD */

$res =
    $conn->query("
        SELECT COUNT(*) AS total
        FROM incidentes
        WHERE dano_propiedad = 1
    ");

if ($res) {

    $fila =
        $res->fetch_assoc();

    $totalPropiedad =
        (int)($fila['total'] ?? 0);

}


/* =========================================================
   CLASIFICACIONES
   ========================================================= */

$resClasificaciones =
    $conn->query("

        SELECT DISTINCT
            clasificacion_incidente

        FROM incidentes

        WHERE
            clasificacion_incidente IS NOT NULL
            AND clasificacion_incidente <> ''

        ORDER BY
            clasificacion_incidente ASC

    ");


/* =========================================================
   CONDUCTORES CON INCIDENTES
   ========================================================= */

$resConductores =
    $conn->query("

        SELECT DISTINCT

            c.id,
            c.rut,
            c.nombre

        FROM conductores c

        INNER JOIN incidentes i
            ON i.conductor_id = c.id

        ORDER BY
            c.nombre ASC

    ");


/* =========================================================
   AÑOS
   ========================================================= */

$resAnios =
    $conn->query("

        SELECT DISTINCT
            anio_incidente

        FROM incidentes

        ORDER BY
            anio_incidente DESC

    ");


/* =========================================================
   MENSAJES
   ========================================================= */

$guardado =
    isset($_GET['guardado']) &&
    $_GET['guardado'] === '1';

$actualizado =
    isset($_GET['actualizado']) &&
    $_GET['actualizado'] === '1';

$eliminado =
    isset($_GET['eliminado']) &&
    $_GET['eliminado'] === '1';

$error =
    trim($_GET['error'] ?? '');

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
    Gestión de Incidentes
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


<script
    src="https://cdn.jsdelivr.net/npm/sweetalert2@11"
></script>


<style>

/* =========================================================
   VARIABLES
========================================================= */

:root {

    --primary: #2563eb;
    --primary-dark: #1e40af;

    --danger: #dc2626;
    --danger-dark: #b91c1c;

    --warning: #d97706;

    --success: #16a34a;

    --bg1: #f1f5f9;
    --bg2: #e0e7ff;

    --card: #ffffff;

    --text: #0f172a;

    --muted: #64748b;

    --border: #e2e8f0;

}


/* =========================================================
   GENERAL
========================================================= */

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    padding: 30px;

    min-height: 100vh;

    background:
        linear-gradient(
            180deg,
            var(--bg1),
            var(--bg2)
        );

    font-family:
        'Inter',
        sans-serif;

    color: var(--text);

}


.container {

    max-width: 1500px;

    margin: 0 auto;

}


/* =========================================================
   BANNER
========================================================= */

.banner-wrap {

    position: relative;

    margin-bottom: 25px;

}


.banner {

    width: 100%;

    height: 220px;

    object-fit: cover;

    display: block;

    border-radius: 20px;

}


.banner-overlay {

    position: absolute;

    inset: 0;

    border-radius: 20px;

    background:
        linear-gradient(
            90deg,
            rgba(15,23,42,.82),
            rgba(15,23,42,.12)
        );

}


.banner-text {

    position: absolute;

    left: 32px;

    bottom: 28px;

    color: white;

}


.banner-text h1 {

    margin: 0;

    font-size: 32px;

    font-weight: 800;

    letter-spacing: -.7px;

}


.banner-text p {

    margin: 7px 0 0;

    font-size: 14px;

    opacity: .9;

}


.banner-datetime {

    position: absolute;

    right: 25px;

    bottom: 22px;

    padding: 11px 16px;

    min-width: 175px;

    border-radius: 14px;

    background:
        rgba(255,255,255,.15);

    backdrop-filter: blur(10px);

    border:
        1px solid
        rgba(255,255,255,.25);

    color: white;

    text-align: right;

}


#banner-date {

    font-size: 11px;

    opacity: .85;

    text-transform: capitalize;

}


#banner-time {

    margin-top: 2px;

    font-size: 24px;

    font-weight: 800;

}


/* =========================================================
   KPI
========================================================= */

.kpi-grid {

    display: grid;

    grid-template-columns:
        repeat(6, minmax(0, 1fr));

    gap: 14px;

    margin-bottom: 22px;

}


.kpi-card {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 17px;

    min-width: 0;

    background: white;

    border:
        1px solid
        rgba(226,232,240,.9);

    border-radius: 16px;

    box-shadow:
        0 8px 25px
        rgba(15,23,42,.055);

    transition: .2s ease;

}


.kpi-card:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 12px 30px
        rgba(15,23,42,.09);

}


.kpi-icon {

    width: 45px;

    height: 45px;

    min-width: 45px;

    border-radius: 13px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #eff6ff;

    color: var(--primary);

    font-size: 19px;

}


.kpi-card.warning
.kpi-icon {

    background: #fff7ed;

    color: var(--warning);

}


.kpi-card.danger
.kpi-icon {

    background: #fef2f2;

    color: var(--danger);

}


.kpi-card.success
.kpi-icon {

    background: #f0fdf4;

    color: var(--success);

}


.kpi-label {

    color: var(--muted);

    font-size: 10px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .35px;

}


.kpi-value {

    margin-top: 3px;

    font-size: 25px;

    font-weight: 800;

    line-height: 1;

}


/* =========================================================
   CARD
========================================================= */

.card {

    background: white;

    padding: 23px;

    border-radius: 18px;

    border:
        1px solid
        rgba(226,232,240,.9);

    box-shadow:
        0 10px 30px
        rgba(15,23,42,.065);

    margin-bottom: 25px;

}


.card-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    flex-wrap: wrap;

    margin-bottom: 20px;

}


.card-header h2 {

    margin: 0;

    font-size: 19px;

    font-weight: 800;

}


.card-subtitle {

    margin-top: 5px;

    color: var(--muted);

    font-size: 11px;

}


/* =========================================================
   BOTONES
========================================================= */

.btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    min-height: 40px;

    padding: 10px 14px;

    border: none;

    border-radius: 10px;

    background:
        linear-gradient(
            135deg,
            var(--primary),
            var(--primary-dark)
        );

    color: white;

    text-decoration: none;

    font-family: inherit;

    font-size: 12px;

    font-weight: 700;

    cursor: pointer;

    transition: .2s ease;

}


.btn:hover {

    transform:
        translateY(-1px);

    box-shadow:
        0 7px 18px
        rgba(37,99,235,.22);

}


.btn-secondary {

    background: #64748b;

}


.btn-light {

    background: white;

    color: #334155;

    border:
        1px solid
        #dbe2ea;

}


.btn-light:hover {

    background: #f8fafc;

    box-shadow: none;

}


.btn-danger {

    background:
        linear-gradient(
            135deg,
            #dc2626,
            #b91c1c
        );

}


/* =========================================================
   FILTROS
========================================================= */

.filter-box {

    padding: 17px;

    margin-bottom: 20px;

    background: #f8fafc;

    border:
        1px solid
        #e2e8f0;

    border-radius: 14px;

}


.filter-title {

    display: flex;

    align-items: center;

    gap: 8px;

    margin-bottom: 13px;

    color: #334155;

    font-size: 12px;

    font-weight: 800;

}


.filter-title i {

    color: var(--primary);

}


.filtros {

    display: grid;

    grid-template-columns:
        2fr
        1fr
        1.25fr
        1.5fr
        auto;

    gap: 10px;

    align-items: end;

}


.campo-filtro label {

    display: block;

    margin-bottom: 6px;

    color: var(--muted);

    font-size: 10px;

    font-weight: 800;

    text-transform: uppercase;

}


.campo-filtro input,
.campo-filtro select {

    width: 100%;

    height: 40px;

    padding:
        0 11px;

    border:
        1px solid
        #dbe2ea;

    border-radius: 9px;

    background: white;

    color: var(--text);

    font-family: inherit;

    font-size: 12px;

    outline: none;

}


.campo-filtro input:focus,
.campo-filtro select:focus {

    border-color:
        #60a5fa;

    box-shadow:
        0 0 0 3px
        rgba(37,99,235,.08);

}


/* =========================================================
   TABLA
========================================================= */

.table-wrapper {

    overflow-x: auto;

}


table {

    width: 100%;

    min-width: 1150px;

    border-collapse:
        separate;

    border-spacing: 0;

}


th,
td {

    padding:
        13px 12px;

    border-bottom:
        1px solid
        #eef2f7;

    text-align: left;

    vertical-align: middle;

}


th {

    background: #f8fafc;

    color: var(--muted);

    font-size: 10px;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .4px;

    white-space: nowrap;

}


td {

    font-size: 12px;

}


tbody tr {

    transition: .15s ease;

}


tbody tr:hover {

    background: #f8fbff;

}


/* =========================================================
   INCIDENTE
========================================================= */

.numero-incidente {

    color:
        var(--primary-dark);

    font-size: 14px;

    font-weight: 800;

}


.numero-sub {

    display: block;

    margin-top: 3px;

    color: #94a3b8;

    font-size: 10px;

}


/* =========================================================
   CONDUCTOR
========================================================= */

.conductor {

    display: flex;

    flex-direction: column;

    gap: 3px;

}


.conductor strong {

    color: #1e293b;

    font-size: 12px;

}


.conductor span {

    color: var(--muted);

    font-size: 10px;

}


.sin-conductor {

    color: #94a3b8;

    font-size: 11px;

    font-style: italic;

}


/* =========================================================
   LUGAR / EQUIPO
========================================================= */

.lugar {

    max-width: 200px;

    color: #334155;

    font-weight: 600;

}


.equipo {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    margin-top: 5px;

    color: #64748b;

    font-size: 10px;

}


/* =========================================================
   BADGES
========================================================= */

.badge {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding: 5px 8px;

    border-radius: 7px;

    font-size: 9px;

    font-weight: 800;

    white-space: nowrap;

}


.badge-blue {

    background: #dbeafe;

    color: #1d4ed8;

}


.badge-danger {

    background: #fee2e2;

    color: #b91c1c;

}


.badge-warning {

    background: #fef3c7;

    color: #92400e;

}


.badge-success {

    background: #dcfce7;

    color: #166534;

}


.badge-neutral {

    background: #f1f5f9;

    color: #64748b;

}


.indicadores {

    display: flex;

    flex-direction: column;

    align-items: flex-start;

    gap: 4px;

}


/* =========================================================
   FOTOS
========================================================= */

.fotos {

    display: inline-flex;

    align-items: center;

    gap: 5px;

}


/* =========================================================
   ACCIONES
========================================================= */

.acciones {

    display: flex;

    align-items: center;

    gap: 5px;

}


.action {

    width: 34px;

    height: 34px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border:
        1px solid
        #e2e8f0;

    border-radius: 8px;

    background: white;

    text-decoration: none;

    transition: .18s ease;

}


.action:hover {

    transform:
        translateY(-1px);

}


.action-view {

    color: var(--primary);

}


.action-view:hover {

    background: #eff6ff;

    border-color: #bfdbfe;

}


.action-edit {

    color: #475569;

}


.action-edit:hover {

    background: #f8fafc;

}


.action-delete {

    color: var(--danger);

    border: none;

    cursor: pointer;

}


.action-delete:hover {

    background: #fef2f2;

}


/* =========================================================
   SIN RESULTADOS
========================================================= */

.sin-resultados {

    padding: 70px 20px;

    text-align: center;

    color: var(--muted);

}


.sin-resultados-icon {

    width: 64px;

    height: 64px;

    margin:
        0 auto 15px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 17px;

    background: #eff6ff;

    color: var(--primary);

    font-size: 25px;

}


.sin-resultados h3 {

    margin:
        0 0 7px;

    color: var(--text);

    font-size: 17px;

}


.sin-resultados p {

    margin:
        0 0 18px;

    font-size: 12px;

}


/* =========================================================
   MODAL
========================================================= */

.modal {

    display: none;

    position: fixed;

    inset: 0;

    z-index: 10000;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background:
        rgba(15,23,42,.68);

    backdrop-filter:
        blur(3px);

}


.modal-content {

    width: 900px;

    max-width: 100%;

    max-height: 94vh;

    overflow-y: auto;

    padding: 27px;

    border-radius: 20px;

    background: white;

    box-shadow:
        0 30px 80px
        rgba(0,0,0,.28);

    animation:
        modalEntrada .18s ease;

}


@keyframes modalEntrada {

    from {

        opacity: 0;

        transform:
            translateY(15px)
            scale(.98);

    }

    to {

        opacity: 1;

        transform:
            translateY(0)
            scale(1);

    }

}


.modal-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 22px;

}


.modal-header h2 {

    margin: 0;

    font-size: 20px;

    font-weight: 800;

}


.modal-header p {

    margin:
        5px 0 0;

    color: var(--muted);

    font-size: 11px;

}


.modal-close {

    width: 36px;

    height: 36px;

    border: none;

    border-radius: 9px;

    background: #f1f5f9;

    color: #64748b;

    font-size: 22px;

    cursor: pointer;

}


.modal-close:hover {

    background: #e2e8f0;

}


/* =========================================================
   FORMULARIO MODAL
========================================================= */

.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 14px;

}


.form-section {

    grid-column:
        1 / -1;

    padding:
        10px 0 5px;

    border-bottom:
        1px solid
        #e5e7eb;

}


.form-section h3 {

    margin: 0;

    color: #1e293b;

    font-size: 13px;

    font-weight: 800;

}


.form-section p {

    margin:
        4px 0 0;

    color: var(--muted);

    font-size: 10px;

}


.form-group {

    display: flex;

    flex-direction: column;

}


.form-group.full {

    grid-column:
        1 / -1;

}


.form-group label {

    margin-bottom: 6px;

    color: #475569;

    font-size: 11px;

    font-weight: 700;

}


.form-group input,
.form-group select,
.form-group textarea {

    width: 100%;

    padding:
        10px 11px;

    border:
        1px solid
        #dbe2ea;

    border-radius: 9px;

    background: white;

    color: #0f172a;

    font-family: inherit;

    font-size: 12px;

    outline: none;

}


.form-group input,
.form-group select {

    height: 40px;

}


.form-group textarea {

    min-height: 90px;

    resize: vertical;

}


.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {

    border-color:
        #60a5fa;

    box-shadow:
        0 0 0 3px
        rgba(37,99,235,.08);

}


.required {

    color: var(--danger);

}


.check-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 9px;

}


.check-item {

    display: flex;

    align-items: center;

    gap: 9px;

    padding: 11px;

    border-radius: 10px;

    background: #f8fafc;

    border:
        1px solid
        #e2e8f0;

}


.check-item input {

    width: auto;

    height: auto;

    margin: 0;

}


.check-item label {

    margin: 0;

    cursor: pointer;

}


/* =========================================================
   CONDUCTOR
========================================================= */

.conductor-search {

    position: relative;

}


#resultadosConductores {

    position: absolute;

    left: 0;

    right: 0;

    top: 100%;

    z-index: 100;

    max-height: 230px;

    overflow-y: auto;

    background: white;

    border-radius: 10px;

    box-shadow:
        0 12px 30px
        rgba(15,23,42,.16);

}


.item-conductor {

    padding:
        11px 13px;

    border-bottom:
        1px solid
        #eef2f7;

    cursor: pointer;

}


.item-conductor:hover {

    background: #f8fafc;

}


.item-conductor strong {

    display: block;

    color: #1e293b;

    font-size: 12px;

}


.item-conductor span {

    display: block;

    margin-top: 3px;

    color: var(--muted);

    font-size: 10px;

}


#conductorSeleccionado {

    margin-top: 5px;

    color: var(--muted);

    font-size: 10px;

}


/* =========================================================
   FOOTER
========================================================= */

footer {

    max-width: 1500px;

    margin:
        25px auto 0;

    padding: 22px;

    border-radius: 18px;

    background:
        linear-gradient(
            135deg,
            #1e3a8a,
            #2563eb
        );

    color: white;

    text-align: center;

    font-size: 11px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:1250px) {

    .kpi-grid {

        grid-template-columns:
            repeat(3, 1fr);

    }

    .filtros {

        grid-template-columns:
            repeat(3, 1fr);

    }

}


@media(max-width:850px) {

    body {

        padding: 15px;

    }


    .kpi-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }


    .filtros {

        grid-template-columns:
            1fr 1fr;

    }


    .form-grid {

        grid-template-columns:
            1fr;

    }


    .form-group.full,
    .form-section {

        grid-column: auto;

    }


    .check-grid {

        grid-template-columns:
            1fr;

    }

}


@media(max-width:600px) {

    .banner {

        height: 190px;

    }


    .banner-text {

        left: 20px;

        bottom: 18px;

    }


    .banner-text h1 {

        font-size: 22px;

    }


    .banner-text p {

        font-size: 11px;

    }


    .banner-datetime {

        display: none;

    }


    .kpi-grid {

        grid-template-columns:
            1fr;

    }


    .filtros {

        grid-template-columns:
            1fr;

    }


    .card {

        padding: 17px;

    }


    .modal-content {

        padding: 18px;

    }

}

</style>

</head>


<body>


<?php include '../shared/menu.php'; ?>


<div class="container">


<!-- =========================================================
     BANNER
========================================================= -->

<div class="banner-wrap">

    <img
        src="banner1.jpg"
        class="banner"
        alt="Gestión de Incidentes"
    >


    <div class="banner-overlay"></div>


    <div class="banner-text">

        <h1>
            Gestión de Incidentes
        </h1>

        <p>
            Registro, seguimiento y control de incidentes
        </p>

    </div>


    <div class="banner-datetime">

        <div id="banner-date"></div>

        <div id="banner-time"></div>

    </div>

</div>


<!-- =========================================================
     KPIs
========================================================= -->

<div class="kpi-grid">


    <div class="kpi-card">

        <div class="kpi-icon">

            <i class="fa-solid fa-triangle-exclamation"></i>

        </div>

        <div>

            <div class="kpi-label">
                Total incidentes
            </div>

            <div class="kpi-value">

                <?= number_format(
                    $totalIncidentes,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>

        </div>

    </div>


    <div class="kpi-card success">

        <div class="kpi-icon">

            <i class="fa-solid fa-calendar-days"></i>

        </div>

        <div>

            <div class="kpi-label">
                Incidentes <?= $anioActual ?>
            </div>

            <div class="kpi-value">

                <?= number_format(
                    $incidentesAnio,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>

        </div>

    </div>


    <div class="kpi-card warning">

        <div class="kpi-icon">

            <i class="fa-solid fa-bolt"></i>

        </div>

        <div>

            <div class="kpi-label">
                Emergencias
            </div>

            <div class="kpi-value">

                <?= number_format(
                    $totalEmergencias,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>

        </div>

    </div>


    <div class="kpi-card danger">

        <div class="kpi-icon">

            <i class="fa-solid fa-skull-crossbones"></i>

        </div>

        <div>

            <div class="kpi-label">
                Graves / Fatales
            </div>

            <div class="kpi-value">

                <?= number_format(
                    $totalGraves,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>

        </div>

    </div>


    <div class="kpi-card">

        <div class="kpi-icon">

            <i class="fa-solid fa-person"></i>

        </div>

        <div>

            <div class="kpi-label">
                Daño personas
            </div>

            <div class="kpi-value">

                <?= number_format(
                    $totalPersonas,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>

        </div>

    </div>


    <div class="kpi-card">

        <div class="kpi-icon">

            <i class="fa-solid fa-building"></i>

        </div>

        <div>

            <div class="kpi-label">
                Daño propiedad
            </div>

            <div class="kpi-value">

                <?= number_format(
                    $totalPropiedad,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>

        </div>

    </div>


</div>


<!-- =========================================================
     CARD PRINCIPAL
========================================================= -->

<div class="card">


    <div class="card-header">

        <div>

            <h2>
                Registro de incidentes
            </h2>

            <div class="card-subtitle">

                Consulta, seguimiento y administración
                de los incidentes registrados.

            </div>

        </div>


        <button
            type="button"
            class="btn"
            onclick="abrirModalIncidente()"
        >

            <i class="fa-solid fa-plus"></i>

            Nuevo incidente

        </button>

    </div>


    <!-- =====================================================
         FILTROS
    ====================================================== -->

    <div class="filter-box">

        <div class="filter-title">

            <i class="fa-solid fa-filter"></i>

            Filtros de búsqueda

        </div>


        <form
            method="GET"
            action="index.php"
            class="filtros"
        >


            <div class="campo-filtro">

                <label>
                    Buscar
                </label>

                <input
                    type="text"
                    name="buscar"
                    value="<?= e($busqueda) ?>"
                    placeholder="N° incidente, RUT, trabajador, lugar, equipo..."
                >

            </div>


            <div class="campo-filtro">

                <label>
                    Año
                </label>

                <select name="anio">

                    <option value="">
                        Todos
                    </option>

                    <?php if ($resAnios): ?>

                        <?php while (
                            $a =
                            $resAnios->fetch_assoc()
                        ): ?>

                            <option
                                value="<?= e($a['anio_incidente']) ?>"
                                <?= (
                                    $anioFiltro !== ''
                                    &&
                                    (string)$anioFiltro ===
                                    (string)$a['anio_incidente']
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                <?= e(
                                    $a['anio_incidente']
                                ) ?>

                            </option>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </select>

            </div>


            <div class="campo-filtro">

                <label>
                    Clasificación
                </label>

                <select name="clasificacion">

                    <option value="">
                        Todas
                    </option>

                    <?php if ($resClasificaciones): ?>

                        <?php while (
                            $cl =
                            $resClasificaciones->fetch_assoc()
                        ): ?>

                            <option
                                value="<?= e(
                                    $cl[
                                        'clasificacion_incidente'
                                    ]
                                ) ?>"
                                <?= (
                                    $clasificacionFiltro ===
                                    $cl[
                                        'clasificacion_incidente'
                                    ]
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                <?= e(
                                    $cl[
                                        'clasificacion_incidente'
                                    ]
                                ) ?>

                            </option>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </select>

            </div>


            <div class="campo-filtro">

                <label>
                    Trabajador
                </label>

                <select name="conductor_id">

                    <option value="">
                        Todos
                    </option>

                    <?php if ($resConductores): ?>

                        <?php while (
                            $con =
                            $resConductores->fetch_assoc()
                        ): ?>

                            <option
                                value="<?= (int)$con['id'] ?>"
                                <?= (
                                    $conductorFiltro ===
                                    (int)$con['id']
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                <?= e(
                                    $con['nombre']
                                ) ?>

                                <?php if (
                                    !empty($con['rut'])
                                ): ?>

                                    ·
                                    <?= e(
                                        $con['rut']
                                    ) ?>

                                <?php endif; ?>

                            </option>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </select>

            </div>


            <div
                style="
                    display:flex;
                    gap:7px;
                "
            >

                <button
                    type="submit"
                    class="btn"
                    title="Aplicar filtros"
                >

                    <i class="fa-solid fa-magnifying-glass"></i>

                    Buscar

                </button>


                <a
                    href="index.php"
                    class="btn btn-light"
                    title="Limpiar filtros"
                >

                    <i class="fa-solid fa-rotate-left"></i>

                </a>

            </div>


        </form>

    </div>


    <!-- =====================================================
         INFORMACIÓN RESULTADOS
    ====================================================== -->

    <div
        style="
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
            margin-bottom:12px;
        "
    >

        <div
            style="
                color:#64748b;
                font-size:11px;
            "
        >

            <i
                class="fa-solid fa-database"
                style="color:#2563eb;"
            ></i>

            Registros encontrados:

            <strong
                style="color:#0f172a;"
            >

                <?= number_format(
                    $resultado->num_rows,
                    0,
                    ',',
                    '.'
                ) ?>

            </strong>

        </div>


        <?php if (
            $busqueda !== ''
            ||
            $anioFiltro !== ''
            ||
            $clasificacionFiltro !== ''
            ||
            $conductorFiltro > 0
        ): ?>

            <a
                href="index.php"
                style="
                    color:#2563eb;
                    text-decoration:none;
                    font-size:11px;
                    font-weight:700;
                "
            >

                <i class="fa-solid fa-xmark"></i>

                Quitar filtros

            </a>

        <?php endif; ?>

    </div>


    <!-- =====================================================
         TABLA
    ====================================================== -->

    <div class="table-wrapper">


        <?php if ($resultado->num_rows > 0): ?>


            <table>


                <thead>

                    <tr>

                        <th>
                            Incidente
                        </th>

                        <th>
                            Ocurrencia
                        </th>

                        <th>
                            Trabajador
                        </th>

                        <th>
                            Clasificación
                        </th>

                        <th>
                            Lugar / Equipo
                        </th>

                        <th>
                            Indicadores
                        </th>

                        <th>
                            Evidencia
                        </th>

                        <th>
                            Acciones
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php while (
                    $inc =
                    $resultado->fetch_assoc()
                ): ?>


                    <tr>


                        <!-- INCIDENTE -->

                        <td>

                            <div class="numero-incidente">

                                #

                                <?= e(
                                    numeroIncidente(
                                        $inc[
                                            'numero_incidente'
                                        ]
                                    )
                                ) ?>

                            </div>


                            <span class="numero-sub">

                                Año
                                <?= e(
                                    $inc[
                                        'anio_incidente'
                                    ]
                                ) ?>

                            </span>

                        </td>


                        <!-- FECHA -->

                        <td>

                            <div
                                style="
                                    white-space:nowrap;
                                    font-size:11px;
                                    color:#475569;
                                "
                            >

                                <i
                                    class="fa-regular fa-calendar"
                                    style="color:#2563eb;"
                                ></i>

                                <?= e(
                                    fechaChile(
                                        $inc[
                                            'fecha_hora_ocurrencia'
                                        ]
                                    )
                                ) ?>

                            </div>

                        </td>


                        <!-- TRABAJADOR -->

                        <td>

                            <?php if (
                                !empty(
                                    $inc[
                                        'conductor_nombre'
                                    ]
                                )
                            ): ?>

                                <div class="conductor">

                                    <strong>

                                        <?= e(
                                            $inc[
                                                'conductor_nombre'
                                            ]
                                        ) ?>

                                    </strong>


                                    <?php if (
                                        !empty(
                                            $inc[
                                                'conductor_rut'
                                            ]
                                        )
                                    ): ?>

                                        <span>

                                            <?= e(
                                                $inc[
                                                    'conductor_rut'
                                                ]
                                            ) ?>

                                        </span>

                                    <?php endif; ?>


                                    <?php if (
                                        !empty(
                                            $inc[
                                                'conductor_area'
                                            ]
                                        )
                                    ): ?>

                                        <span>

                                            <?= e(
                                                $inc[
                                                    'conductor_area'
                                                ]
                                            ) ?>

                                        </span>

                                    <?php endif; ?>

                                </div>

                            <?php else: ?>

                                <span class="sin-conductor">

                                    Sin trabajador asociado

                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- CLASIFICACIÓN -->

                        <td>

                            <?= badgeClasificacion(
                                $inc[
                                    'clasificacion_incidente'
                                ]
                            ) ?>

                        </td>


                        <!-- LUGAR / EQUIPO -->

                        <td>

                            <div class="lugar">

                                <?= e(
                                    $inc[
                                        'lugar_incidente'
                                    ]
                                    ?: 'Lugar no informado'
                                ) ?>

                            </div>


                            <?php if (
                                !empty(
                                    $inc['equipo']
                                )
                            ): ?>

                                <div class="equipo">

                                    <i
                                        class="fa-solid fa-truck"
                                    ></i>

                                    <?= e(
                                        $inc['equipo']
                                    ) ?>

                                </div>

                            <?php endif; ?>

                        </td>


                        <!-- INDICADORES -->

                        <td>

                            <div class="indicadores">


                                <?php if (
                                    (int)$inc[
                                        'emergencia'
                                    ] === 1
                                ): ?>

                                    <span
                                        class="
                                            badge
                                            badge-warning
                                        "
                                    >

                                        <i
                                            class="
                                                fa-solid
                                                fa-bolt
                                            "
                                        ></i>

                                        Emergencia

                                    </span>

                                <?php endif; ?>


                                <?php if (
                                    (int)$inc[
                                        'accidente_grave_fatal'
                                    ] === 1
                                ): ?>

                                    <span
                                        class="
                                            badge
                                            badge-danger
                                        "
                                    >

                                        <i
                                            class="
                                                fa-solid
                                                fa-triangle-exclamation
                                            "
                                        ></i>

                                        Grave / Fatal

                                    </span>

                                <?php endif; ?>


                                <?php if (
                                    (int)$inc[
                                        'dano_personas'
                                    ] === 1
                                ): ?>

                                    <span
                                        class="
                                            badge
                                            badge-danger
                                        "
                                    >

                                        <i
                                            class="
                                                fa-solid
                                                fa-person
                                            "
                                        ></i>

                                        Personas

                                    </span>

                                <?php endif; ?>


                                <?php if (
                                    (int)$inc[
                                        'dano_propiedad'
                                    ] === 1
                                ): ?>

                                    <span
                                        class="
                                            badge
                                            badge-neutral
                                        "
                                    >

                                        <i
                                            class="
                                                fa-solid
                                                fa-building
                                            "
                                        ></i>

                                        Propiedad

                                    </span>

                                <?php endif; ?>


                                <?php if (

                                    (int)$inc['emergencia'] === 0
                                    &&
                                    (int)$inc[
                                        'accidente_grave_fatal'
                                    ] === 0
                                    &&
                                    (int)$inc[
                                        'dano_personas'
                                    ] === 0
                                    &&
                                    (int)$inc[
                                        'dano_propiedad'
                                    ] === 0

                                ): ?>

                                    <span
                                        class="
                                            badge
                                            badge-success
                                        "
                                    >

                                        <i
                                            class="
                                                fa-solid
                                                fa-check
                                            "
                                        ></i>

                                        Sin indicadores

                                    </span>

                                <?php endif; ?>


                            </div>

                        </td>


                        <!-- EVIDENCIA -->

                        <td>

                            <?php if (
                                (int)$inc[
                                    'cantidad_fotos'
                                ] > 0
                            ): ?>

                                <span
                                    class="
                                        badge
                                        badge-blue
                                    "
                                >

                                    <i
                                        class="
                                            fa-solid
                                            fa-camera
                                        "
                                    ></i>

                                    <?= (int)$inc[
                                        'cantidad_fotos'
                                    ] ?>

                                    foto(s)

                                </span>

                            <?php else: ?>

                                <span
                                    class="
                                        badge
                                        badge-neutral
                                    "
                                >

                                    Sin fotos

                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- ACCIONES -->

                        <td>

                            <div class="acciones">


                                <a
                                    href="
                                        ver_incidente.php?id=
                                        <?= (int)$inc['id'] ?>
                                    "
                                    class="
                                        action
                                        action-view
                                    "
                                    title="Ver incidente"
                                >

                                    <i
                                        class="
                                            fa-solid
                                            fa-eye
                                        "
                                    ></i>

                                </a>


                                <a
                                    href="
                                        editar_incidente.php?id=
                                        <?= (int)$inc['id'] ?>
                                    "
                                    class="
                                        action
                                        action-edit
                                    "
                                    title="Editar incidente"
                                >

                                    <i
                                        class="
                                            fa-solid
                                            fa-pen
                                        "
                                    ></i>

                                </a>


                             <button
    type="button"
    class="btn-action delete"
    title="Eliminar incidente"
    onclick="eliminarIncidenteSimple(<?= (int)$inc['id'] ?>)"
>
    <i class="fa-solid fa-trash"></i>
</button>


                            </div>

                        </td>


                    </tr>


                <?php endwhile; ?>


                </tbody>

            </table>


        <?php else: ?>


            <div class="sin-resultados">

                <div class="sin-resultados-icon">

                    <i
                        class="
                            fa-solid
                            fa-clipboard-list
                        "
                    ></i>

                </div>


                <h3>
                    No se encontraron incidentes
                </h3>


                <p>

                    No existen registros que coincidan
                    con los filtros seleccionados.

                </p>


                <a
                    href="index.php"
                    class="btn btn-light"
                >

                    <i
                        class="
                            fa-solid
                            fa-rotate-left
                        "
                    ></i>

                    Mostrar todos

                </a>

            </div>


        <?php endif; ?>


    </div>


</div>


<!-- =========================================================
     MODAL NUEVO INCIDENTE
========================================================= -->

<div
    class="modal"
    id="modalIncidente"
>


    <div class="modal-content">


        <div class="modal-header">

            <div>

                <h2>

                    <i
                        class="
                            fa-solid
                            fa-triangle-exclamation
                        "
                        style="color:#2563eb;"
                    ></i>

                    Nuevo incidente

                </h2>

                <p>
                    Complete la información disponible del evento.
                </p>

            </div>


            <button
                type="button"
                class="modal-close"
                onclick="cerrarModalIncidente()"
            >

                &times;

            </button>

        </div>


        <form
    method="POST"
    action="guardar_incidente.php"
    id="formIncidente"
    enctype="multipart/form-data"
>


            <div class="form-grid">


                <!-- =================================================
                     IDENTIFICACIÓN
                ================================================== -->

                <div class="form-section">

                    <h3>
                        Identificación
                    </h3>

                    <p>
                        Número, año y trabajador relacionado.
                    </p>

                </div>


                <div class="form-group">

                    <label>

                        Número de incidente
                        <span class="required">*</span>

                    </label>

                    <input
                        type="number"
                        name="numero_incidente"
                        id="numero_incidente"
                        value="<?= $proximoNumero ?>"
                        min="1"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>

                        Año
                        <span class="required">*</span>

                    </label>

                    <input
                        type="number"
                        name="anio_incidente"
                        id="anio_incidente"
                        value="<?= $anioActual ?>"
                        min="2000"
                        max="2100"
                        required
                    >

                </div>


                <!-- =================================================
                     CONDUCTOR
                ================================================== -->

                <div
                    class="
                        form-group
                        full
                        conductor-search
                    "
                >

                    <label>
                        Trabajador asociado
                    </label>


                    <input
                        type="text"
                        id="busquedaConductor"
                        placeholder="Buscar por nombre o RUT..."
                        autocomplete="off"
                    >


                    <input
                        type="hidden"
                        name="conductor_id"
                        id="conductor_id"
                    >


                    <div
                        id="resultadosConductores"
                    ></div>


                    <div
                        id="conductorSeleccionado"
                    >
                        No se ha seleccionado un trabajador.
                    </div>

                </div>


                <!-- =================================================
                     FECHAS
                ================================================== -->

                <div class="form-section">

                    <h3>
                        Fechas del incidente
                    </h3>

                    <p>
                        La fecha de ocurrencia es obligatoria.
                    </p>

                </div>


                <div class="form-group">

                    <label>

                        Fecha y hora de ocurrencia
                        <span class="required">*</span>

                    </label>

                    <input
                        type="datetime-local"
                        name="fecha_hora_ocurrencia"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Fecha y hora del evento
                    </label>

                    <input
                        type="datetime-local"
                        name="fecha_hora_evento"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Fecha y hora de aviso
                    </label>

                    <input
                        type="datetime-local"
                        name="fecha_hora_aviso"
                    >

                </div>


                <!-- =================================================
                     ORGANIZACIÓN
                ================================================== -->

                <div class="form-section">

                    <h3>
                        Información organizacional
                    </h3>

                </div>


                <div class="form-group full">

                    <label>
                        Antecedentes
                    </label>

                    <textarea
                        name="antecedentes"
                        placeholder="Ingrese los antecedentes relevantes..."
                    ></textarea>

                </div>


                <div class="form-group">

                    <label>
                        División
                    </label>

                    <input
                        type="text"
                        name="division"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Subdivisión
                    </label>

                    <input
                        type="text"
                        name="subdivision"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Planta
                    </label>

                    <input
                        type="text"
                        name="planta"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Sucursal
                    </label>

                    <input
                        type="text"
                        name="sucursal"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Empresa del evento
                    </label>

                    <input
                        type="text"
                        name="empresa_evento"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Área
                    </label>

                    <input
                        type="text"
                        name="area"
                    >

                </div>


                <!-- =================================================
                     EQUIPO
                ================================================== -->

                <div class="form-section">

                    <h3>
                        Equipo y ubicación
                    </h3>

                </div>


                <div class="form-group">

                    <label>
                        Equipo
                    </label>

                    <input
                        type="text"
                        name="equipo"
                        placeholder="Patente, camión, semirremolque, etc."
                    >

                </div>


                <div class="form-group">

                    <label>
                        Lugar del incidente
                    </label>

                    <input
                        type="text"
                        name="lugar_incidente"
                    >

                </div>


                <!-- =================================================
                     CLASIFICACIÓN
                ================================================== -->

                <div class="form-section">

                    <h3>
                        Clasificación y riesgo
                    </h3>

                </div>


                <div class="form-group">

                    <label>
                        Clasificación del incidente
                    </label>

                    <input
                        type="text"
                        name="clasificacion_incidente"
                        placeholder="Ej.: Incidente, accidente, cuasi accidente..."
                    >

                </div>


                <div class="form-group">

                    <label>
                        Exposición LGF
                    </label>

                    <input
                        type="text"
                        name="exposicion_lgf"
                    >

                </div>


                <div class="form-group full">

                    <label>
                        Riesgo asociado
                    </label>

                    <input
                        type="text"
                        name="riesgo_asociado"
                    >

                </div>


                <!-- =================================================
                     INDICADORES
                ================================================== -->

                <div class="form-section">

                    <h3>
                        Indicadores del incidente
                    </h3>

                    <p>
                        Seleccione solamente los que correspondan.
                    </p>

                </div>


                <div class="form-group full">

                    <div class="check-grid">


                        <div class="check-item">

                            <input
                                type="checkbox"
                                id="dano_propiedad"
                                name="dano_propiedad"
                                value="1"
                            >

                            <label for="dano_propiedad">

                                <i
                                    class="
                                        fa-solid
                                        fa-building
                                    "
                                    style="color:#d97706;"
                                ></i>

                                Daño a propiedad

                            </label>

                        </div>


                        <div class="check-item">

                            <input
                                type="checkbox"
                                id="dano_personas"
                                name="dano_personas"
                                value="1"
                            >

                            <label for="dano_personas">

                                <i
                                    class="
                                        fa-solid
                                        fa-person
                                    "
                                    style="color:#dc2626;"
                                ></i>

                                Daño a personas

                            </label>

                        </div>


                        <div class="check-item">

                            <input
                                type="checkbox"
                                id="emergencia"
                                name="emergencia"
                                value="1"
                            >

                            <label for="emergencia">

                                <i
                                    class="
                                        fa-solid
                                        fa-bolt
                                    "
                                    style="color:#d97706;"
                                ></i>

                                Emergencia

                            </label>

                        </div>


                        <div class="check-item">

                            <input
                                type="checkbox"
                                id="accidente_grave_fatal"
                                name="accidente_grave_fatal"
                                value="1"
                            >

                            <label for="accidente_grave_fatal">

                                <i
                                    class="
                                        fa-solid
                                        fa-triangle-exclamation
                                    "
                                    style="color:#dc2626;"
                                ></i>

                                Accidente grave / fatal

                            </label>

                        </div>


                    </div>

                </div>


                <!-- =================================================
                     AFECTADO
                ================================================== -->

                <div class="form-section">

                    <h3>
                        Persona afectada
                    </h3>

                    <p>
                        Complete esta sección cuando corresponda.
                    </p>

                </div>


                <div class="form-group">

                    <label>
                        Nombre
                    </label>

                    <input
                        type="text"
                        name="afectado_nombre"
                    >

                </div>


                <div class="form-group">

                    <label>
                        RUT
                    </label>

                    <input
                        type="text"
                        name="afectado_rut"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Sexo
                    </label>

                    <select name="afectado_sexo">

                        <option value="">
                            Seleccionar
                        </option>

                        <option value="Femenino">
                            Femenino
                        </option>

                        <option value="Masculino">
                            Masculino
                        </option>

                        <option value="Otro">
                            Otro
                        </option>

                        <option value="No informado">
                            No informado
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        Edad
                    </label>

                    <input
                        type="number"
                        name="afectado_edad"
                        min="0"
                        max="120"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Empresa involucrada
                    </label>

                    <input
                        type="text"
                        name="empresa_involucrada"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Cargo
                    </label>

                    <input
                        type="text"
                        name="cargo"
                    >

                </div>


                <div class="form-group full">

                    <label>
                        Partes del cuerpo afectadas
                    </label>

                    <textarea
                        name="partes_cuerpo"
                        placeholder="Indique las partes afectadas, si corresponde..."
                    ></textarea>

                </div>


                <!-- =================================================
                     DESCRIPCIÓN
                ================================================== -->

                <div class="form-section">

                    <h3>
                        Descripción
                    </h3>

                </div>


                <div class="form-group full">

                    <label>
                        Detalle del incidente
                    </label>

                    <textarea
                        name="detalle_incidente"
                        placeholder="Describa lo sucedido..."
                    ></textarea>

                </div>


                <div class="form-group full">

                    <label>
                        Descripción preliminar
                    </label>

                    <textarea
                        name="descripcion_preliminar"
                        placeholder="Ingrese una descripción preliminar..."
                    ></textarea>

                </div>




<!-- =================================================
     EVIDENCIA FOTOGRÁFICA
================================================= -->

<div class="form-section">

    <h3>
        Evidencia fotográfica
    </h3>

    <p>
        Adjunte fotografías relacionadas con el incidente.
        Puede seleccionar una o varias imágenes.
    </p>

</div>


<div class="form-group full">

    <label for="fotos">

        <i
            class="fa-solid fa-camera"
            style="color:#2563eb;"
        ></i>

        Fotografías del incidente

    </label>


    <input
        type="file"
        name="fotos[]"
        id="fotos"
        accept="image/jpeg,image/png,image/webp"
        multiple
        style="
            padding:10px;
            background:#f8fafc;
            border:1px solid #cbd5e1;
            border-radius:8px;
            cursor:pointer;
        "
    >


    <div
        id="previewFotos"
        style="
            margin-top:10px;
            display:grid;
            grid-template-columns:
                repeat(auto-fill,minmax(130px,1fr));
            gap:10px;
        "
    ></div>


    <div
        style="
            margin-top:8px;
            color:#64748b;
            font-size:11px;
        "
    >

        <i class="fa-solid fa-circle-info"></i>

        Formatos permitidos:
        JPG, JPEG, PNG y WEBP.

        <br>

        Puede seleccionar varias fotografías.

    </div>

</div>

                <!-- =================================================
                     RESPONSABLES
                ================================================== -->

                <div class="form-section">

                    <h3>
                        Responsables
                    </h3>

                </div>


                <div class="form-group">

                    <label>
                        Avisado por
                    </label>

                    <input
                        type="text"
                        name="avisado_por"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Realizado por
                    </label>

                    <input
                        type="text"
                        name="realizado_por"
                    >

                </div>


                <!-- =================================================
                     BOTONES
                ================================================== -->

                <div
                    class="form-group full"
                    style="
                        display:flex;
                        flex-direction:row;
                        justify-content:flex-end;
                        gap:8px;
                        margin-top:8px;
                        padding-top:15px;
                        border-top:1px solid #e5e7eb;
                    "
                >

                    <button
                        type="button"
                        class="btn btn-light"
                        onclick="cerrarModalIncidente()"
                    >

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="btn"
                    >

                        <i
                            class="
                                fa-solid
                                fa-floppy-disk
                            "
                        ></i>

                        Guardar incidente

                    </button>

                </div>


            </div>

        </form>

    </div>

</div>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer>

    <strong>
        Transportes Mimbral LTDA
    </strong>

    <br>

    <span
        style="
            opacity:.75;
        "
    >
        Sistema de Gestión de Incidentes
        ·
        <?= date('Y') ?>
    </span>

</footer>


</div>


<script>


/* =========================================================
   PREVISUALIZACIÓN DE FOTOGRAFÍAS
========================================================= */

const inputFotos =
    document.getElementById(
        'fotos'
    );


const previewFotos =
    document.getElementById(
        'previewFotos'
    );


if (
    inputFotos &&
    previewFotos
) {

    inputFotos.addEventListener(
        'change',
        function()
        {

            previewFotos.innerHTML = '';


            const archivos =
                Array.from(
                    this.files
                );


            archivos.forEach(
                function(archivo)
                {

                    if (
                        !archivo.type.startsWith(
                            'image/'
                        )
                    ) {

                        return;

                    }


                    const lector =
                        new FileReader();


                    lector.onload =
                        function(event)
                        {

                            const contenedor =
                                document.createElement(
                                    'div'
                                );


                            contenedor.style.cssText = `
                                border:1px solid #e2e8f0;
                                border-radius:8px;
                                padding:5px;
                                background:#fff;
                            `;


                            const imagen =
                                document.createElement(
                                    'img'
                                );


                            imagen.src =
                                event.target.result;


                            imagen.style.cssText = `
                                width:100%;
                                height:100px;
                                object-fit:cover;
                                border-radius:6px;
                                display:block;
                            `;


                            const nombre =
                                document.createElement(
                                    'div'
                                );


                            nombre.textContent =
                                archivo.name;


                            nombre.style.cssText = `
                                margin-top:5px;
                                font-size:10px;
                                color:#64748b;
                                overflow:hidden;
                                text-overflow:ellipsis;
                                white-space:nowrap;
                            `;


                            contenedor.appendChild(
                                imagen
                            );


                            contenedor.appendChild(
                                nombre
                            );


                            previewFotos.appendChild(
                                contenedor
                            );

                        };


                    lector.readAsDataURL(
                        archivo
                    );

                }
            );

        }
    );

}


/* =========================================================
   RELOJ
========================================================= */

function actualizarBannerHora()
{

    const now =
        new Date();


    const fecha =
        document.getElementById(
            'banner-date'
        );


    const hora =
        document.getElementById(
            'banner-time'
        );


    if (!fecha || !hora) {
        return;
    }


    fecha.innerText =
        now.toLocaleDateString(
            'es-CL',
            {
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            }
        );


    hora.innerText =
        now.toLocaleTimeString(
            'es-CL',
            {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            }
        );

}


actualizarBannerHora();

setInterval(
    actualizarBannerHora,
    1000
);


/* =========================================================
   MODAL
========================================================= */

function abrirModalIncidente()
{

    const modal =
        document.getElementById(
            'modalIncidente'
        );


    if (!modal) {
        return;
    }


    modal.style.display =
        'flex';


    document.body.style.overflow =
        'hidden';

}


function cerrarModalIncidente()
{

    const modal =
        document.getElementById(
            'modalIncidente'
        );


    if (!modal) {
        return;
    }


    modal.style.display =
        'none';


    document.body.style.overflow =
        '';

}


/* =========================================================
   CERRAR MODAL CON ESC
========================================================= */

document.addEventListener(
    'keydown',
    function(event)
    {

        if (
            event.key === 'Escape'
        ) {

            cerrarModalIncidente();

        }

    }
);


/* =========================================================
   CERRAR MODAL AL HACER CLICK AFUERA
========================================================= */

window.addEventListener(
    'click',
    function(event)
    {

        const modal =
            document.getElementById(
                'modalIncidente'
            );


        if (
            modal &&
            event.target === modal
        ) {

            cerrarModalIncidente();

        }

    }
);


/* =========================================================
   BUSCADOR DE CONDUCTORES
========================================================= */

const inputConductor =
    document.getElementById(
        'busquedaConductor'
    );


const resultadosConductor =
    document.getElementById(
        'resultadosConductores'
    );


const conductorId =
    document.getElementById(
        'conductor_id'
    );


const conductorSeleccionado =
    document.getElementById(
        'conductorSeleccionado'
    );


let timeoutConductor = null;


if (
    inputConductor &&
    resultadosConductor
) {

    inputConductor.addEventListener(
        'input',
        function()
        {

            const valor =
                this.value.trim();


            conductorId.value =
                '';


            conductorSeleccionado.innerHTML =
                'No se ha seleccionado un trabajador.';


            resultadosConductor.innerHTML =
                '';


            clearTimeout(
                timeoutConductor
            );


            if (
                valor.length < 2
            ) {

                return;

            }


            timeoutConductor =
                setTimeout(
                    function()
                    {

                        fetch(
                            'buscar_conductor.php?q=' +
                            encodeURIComponent(
                                valor
                            )
                        )

                        .then(
                            function(response)
                            {
                                if (
                                    !response.ok
                                ) {

                                    throw new Error(
                                        'Error en la búsqueda.'
                                    );

                                }

                                return response.text();

                            }
                        )

                        .then(
                            function(html)
                            {

                                resultadosConductor.innerHTML =
                                    html;

                            }
                        )

                        .catch(
                            function(error)
                            {

                                console.error(
                                    error
                                );

                                resultadosConductor.innerHTML =
                                    '';

                            }
                        );

                    },
                    250
                );

        }
    );

}


/* =========================================================
   SELECCIONAR CONDUCTOR
========================================================= */

if (
    resultadosConductor
) {

    resultadosConductor.addEventListener(
        'click',
        function(event)
        {

            const item =
                event.target.closest(
                    '.item-conductor'
                );


            if (!item) {
                return;
            }


            const id =
                item.dataset.id || '';


            const nombre =
                item.dataset.nombre || '';


            const rut =
                item.dataset.rut || '';


            conductorId.value =
                id;


            inputConductor.value =
                nombre;


            conductorSeleccionado.innerHTML =

                '<i class="fa-solid fa-circle-check" ' +
                'style="color:#16a34a;"></i> ' +

                '<strong>' +
                escapeHtml(nombre) +
                '</strong>' +

                (
                    rut
                        ? ' · ' + escapeHtml(rut)
                        : ''
                );


            resultadosConductor.innerHTML =
                '';

        }
    );

}


/* =========================================================
   ESCAPE HTML JAVASCRIPT
========================================================= */

function escapeHtml(text)
{

    const div =
        document.createElement(
            'div'
        );


    div.textContent =
        text;


    return div.innerHTML;

}


/* =========================================================
   CAMBIO DE AÑO
   Actualiza sugerencia del número.
========================================================= */

const inputAnio =
    document.getElementById(
        'anio_incidente'
    );


const inputNumero =
    document.getElementById(
        'numero_incidente'
    );


if (
    inputAnio &&
    inputNumero
) {

    inputAnio.addEventListener(
        'change',
        function()
        {

            const anio =
                parseInt(
                    this.value,
                    10
                );


            if (
                !anio ||
                anio < 2000 ||
                anio > 2100
            ) {

                return;

            }


            fetch(
                'siguiente_incidente.php?anio=' +
                encodeURIComponent(anio)
            )

            .then(
                function(response)
                {

                    if (
                        !response.ok
                    ) {

                        throw new Error(
                            'No se pudo obtener el número.'
                        );

                    }

                    return response.text();

                }
            )

            .then(
                function(numero)
                {

                    numero =
                        parseInt(
                            numero,
                            10
                        );


                    if (
                        !isNaN(numero)
                        &&
                        numero > 0
                    ) {

                        inputNumero.value =
                            numero;

                    }

                }
            )

            .catch(
                function(error)
                {

                    console.log(
                        'No fue posible actualizar el número automáticamente.',
                        error
                    );

                }
            );

        }
    );

}



/* =========================================================
   CONFIRMAR ELIMINACIÓN
========================================================= */

function eliminarIncidente(id)
{

    Swal.fire({

        title: '¿Eliminar incidente?',

        text: 'Esta acción no se puede deshacer.',

        icon: 'warning',

        showCancelButton: true,

        confirmButtonText:
            'Sí, eliminar',

        cancelButtonText:
            'Cancelar',

        reverseButtons: true,

        confirmButtonColor:
            '#dc2626',

        cancelButtonColor:
            '#64748b'

    }).then(function(resultado)
    {

        if (resultado.isConfirmed)
        {

            window.location.href =
                'eliminar_incidente.php?id=' +
                encodeURIComponent(id);

        }

    });

}


/* =========================================================
   CERRAR SCRIPT PRINCIPAL
========================================================= */

</script>


<!-- =========================================================
     ALERTAS PHP
========================================================= -->

<script>

<?php if ($guardado): ?>

Swal.fire({

    icon: 'success',

    title: 'Incidente registrado',

    text:
        'El incidente fue guardado correctamente.',

    confirmButtonColor:
        '#2563eb',

    timer:
        2600,

    timerProgressBar:
        true

});

<?php endif; ?>


<?php if ($actualizado): ?>

Swal.fire({

    icon: 'success',

    title: 'Incidente actualizado',

    text:
        'Los cambios fueron guardados correctamente.',

    confirmButtonColor:
        '#2563eb',

    timer:
        2600,

    timerProgressBar:
        true

});

<?php endif; ?>


<?php if ($eliminado): ?>

Swal.fire({

    icon: 'success',

    title: 'Incidente eliminado',

    text:
        'El incidente fue eliminado correctamente.',

    confirmButtonColor:
        '#2563eb',

    timer:
        2600,

    timerProgressBar:
        true

});

<?php endif; ?>


<?php if ($error !== ''): ?>

Swal.fire({

    icon: 'error',

    title: 'No fue posible completar la operación',

    text:
        <?= json_encode(
            $error,
            JSON_UNESCAPED_UNICODE
        ) ?>,

    confirmButtonColor:
        '#2563eb'

});

<?php endif; ?>

</script>


<!-- =========================================================
     ELIMINACIÓN SIMPLE
     Se mantiene porque actualmente funciona correctamente.
========================================================= -->

<script>

function eliminarIncidenteSimple(id)
{

    var confirmar = confirm(

        '¿Está seguro que desea eliminar este incidente?\n\n' +

        'Esta acción no se puede deshacer.'

    );


    if (confirmar === true)
    {

        window.location.href =
            'eliminar_incidente.php?id=' +
            encodeURIComponent(id);

    }

}

</script>


</body>

</html>


</body>

</html>