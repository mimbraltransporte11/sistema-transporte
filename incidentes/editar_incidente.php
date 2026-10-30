<?php

/* =========================================================
   EDITAR INCIDENTE
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
   FUNCIONES
========================================================= */

function escapar($valor)
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}


function valor($valor)
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}


function datetime_local($valor)
{
    if (
        !$valor ||
        $valor === '0000-00-00 00:00:00'
    ) {

        return '';

    }

    $timestamp = strtotime($valor);

    if (!$timestamp) {

        return '';

    }

    return date(
        'Y-m-d\TH:i',
        $timestamp
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
        escapar($db->error)
    );

}


$stmt->bind_param(
    'i',
    $id
);


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


$incidente =
    $resultado->fetch_assoc();


$stmt->close();


/* =========================================================
   OBTENER FOTOS DEL INCIDENTE
========================================================= */

$fotos = [];


$sql_fotos = "
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


$stmt_fotos = $db->prepare($sql_fotos);


if ($stmt_fotos) {

    $stmt_fotos->bind_param(
        'i',
        $id
    );

    $stmt_fotos->execute();

    $resultado_fotos =
        $stmt_fotos->get_result();

    while (
        $foto =
        $resultado_fotos->fetch_assoc()
    ) {

        $fotos[] = $foto;

    }

    $stmt_fotos->close();

}


/* =========================================================
   OBTENER CONDUCTORES
========================================================= */

$sql_conductores = "
    SELECT
        id,
        rut,
        nombre,
        area
    FROM conductores
    ORDER BY nombre ASC
";


$resultado_conductores =
    $db->query($sql_conductores);


if (!$resultado_conductores) {

    die(
        'No fue posible cargar los conductores: ' .
        escapar($db->error)
    );

}


$conductores = [];


while (
    $fila =
    $resultado_conductores->fetch_assoc()
) {

    $conductores[] = $fila;

}


/* =========================================================
   DATOS
========================================================= */

$numero_incidente =
    $incidente['numero_incidente'] ?? '';

$anio_incidente =
    $incidente['anio_incidente'] ?? '';

$conductor_id =
    $incidente['conductor_id'] ?? '';

$fecha_hora_ocurrencia =
    datetime_local(
        $incidente['fecha_hora_ocurrencia'] ?? ''
    );

$fecha_hora_evento =
    datetime_local(
        $incidente['fecha_hora_evento'] ?? ''
    );

$fecha_hora_aviso =
    datetime_local(
        $incidente['fecha_hora_aviso'] ?? ''
    );


$antecedentes =
    $incidente['antecedentes'] ?? '';

$division =
    $incidente['division'] ?? '';

$subdivision =
    $incidente['subdivision'] ?? '';

$planta =
    $incidente['planta'] ?? '';

$sucursal =
    $incidente['sucursal'] ?? '';

$empresa_evento =
    $incidente['empresa_evento'] ?? '';

$area =
    $incidente['area'] ?? '';


$equipo =
    $incidente['equipo'] ?? '';

$lugar_incidente =
    $incidente['lugar_incidente'] ?? '';


$clasificacion_incidente =
    $incidente['clasificacion_incidente'] ?? '';

$exposicion_lgf =
    $incidente['exposicion_lgf'] ?? '';

$riesgo_asociado =
    $incidente['riesgo_asociado'] ?? '';


$dano_propiedad =
    (int) ($incidente['dano_propiedad'] ?? 0);

$dano_personas =
    (int) ($incidente['dano_personas'] ?? 0);

$emergencia =
    (int) ($incidente['emergencia'] ?? 0);

$accidente_grave_fatal =
    (int) ($incidente['accidente_grave_fatal'] ?? 0);


$afectado_nombre =
    $incidente['afectado_nombre'] ?? '';

$afectado_rut =
    $incidente['afectado_rut'] ?? '';

$afectado_sexo =
    $incidente['afectado_sexo'] ?? '';

$afectado_edad =
    $incidente['afectado_edad'] ?? '';

$empresa_involucrada =
    $incidente['empresa_involucrada'] ?? '';

$cargo =
    $incidente['cargo'] ?? '';

$partes_cuerpo =
    $incidente['partes_cuerpo'] ?? '';


$detalle_incidente =
    $incidente['detalle_incidente'] ?? '';

$descripcion_preliminar =
    $incidente['descripcion_preliminar'] ?? '';


$avisado_por =
    $incidente['avisado_por'] ?? '';

$realizado_por =
    $incidente['realizado_por'] ?? '';


/* =========================================================
   IDENTIFICADOR VISUAL
========================================================= */

$numero_visual =
    str_pad(
        (string) $numero_incidente,
        4,
        '0',
        STR_PAD_LEFT
    );

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
    Editar Incidente #<?= escapar($numero_visual) ?>
    |
    Sistema de Incidentes
</title>


<link
    rel="icon"
    type="image/png"
    href="/errores/favicon.png?v=2"
>


<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
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

    --texto: #0f172a;
    --secundario: #64748b;

    --borde: #dbe3ee;

    --fondo: #f1f5f9;

    --blanco: #ffffff;

}


/* =========================================================
   GENERAL
========================================================= */

* {

    box-sizing: border-box;

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

    margin: auto;

    padding: 35px 0 60px;

}


/* =========================================================
   CABECERA
========================================================= */

.cabecera {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 20px;

    margin-bottom: 25px;

}


.badge {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 8px 13px;

    margin-bottom: 10px;

    border-radius: 999px;

    background: var(--azul-suave);

    color: var(--azul-oscuro);

    font-size: 11px;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .5px;

}


h1 {

    margin: 0;

    font-size: 30px;

    font-weight: 800;

    letter-spacing: -.7px;

}


.subtitulo {

    margin: 8px 0 0;

    color: var(--secundario);

    font-size: 14px;

}


/* =========================================================
   BOTONES
========================================================= */

.acciones {

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

}


.btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    min-height: 42px;

    padding: 0 16px;

    border-radius: 10px;

    border: 1px solid transparent;

    text-decoration: none;

    font-family: inherit;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;

    transition: .2s ease;

}


.btn:hover {

    transform: translateY(-1px);

}


.btn-volver {

    background: #fff;

    border-color: var(--borde);

    color: #334155;

}


.btn-guardar {

    background: var(--azul);

    color: #fff;

    box-shadow:
        0 5px 15px
        rgba(37, 99, 235, .22);

}


.btn-guardar:hover {

    background: var(--azul-oscuro);

}


/* =========================================================
   TARJETAS
========================================================= */

.tarjeta {

    margin-bottom: 20px;

    background: rgba(
        255,
        255,
        255,
        .97
    );

    border: 1px solid var(--borde);

    border-radius: 18px;

    box-shadow:
        0 10px 30px
        rgba(15, 23, 42, .06);

    overflow: hidden;

}


.tarjeta-cabecera {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 21px 25px;

    border-bottom: 1px solid var(--borde);

}


.icono {

    width: 42px;

    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    background: var(--azul-suave);

    color: var(--azul);

}


.tarjeta-cabecera h2 {

    margin: 0;

    font-size: 17px;

    font-weight: 800;

}


.tarjeta-cabecera p {

    margin: 4px 0 0;

    color: var(--secundario);

    font-size: 12px;

}


/* =========================================================
   FORMULARIO
========================================================= */

.formulario {

    padding: 25px;

}


.grid {

    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 20px;

}


.campo {

    min-width: 0;

}


.campo.completo {

    grid-column: 1 / -1;

}


.campo-label {

    display: block;

    margin-bottom: 7px;

    color: #334155;

    font-size: 12px;

    font-weight: 800;

}


.campo-label .opcional {

    color: #94a3b8;

    font-weight: 500;

}


input,
select,
textarea {

    width: 100%;

    border: 1px solid #cbd5e1;

    border-radius: 10px;

    padding: 12px 13px;

    background: #fff;

    color: #0f172a;

    font-family: inherit;

    font-size: 13px;

    outline: none;

    transition: .2s ease;

}


input:focus,
select:focus,
textarea:focus {

    border-color: var(--azul);

    box-shadow:
        0 0 0 3px
        rgba(37, 99, 235, .10);

}


textarea {

    min-height: 130px;

    resize: vertical;

    line-height: 1.6;

}


textarea.pequena {

    min-height: 90px;

}


select {

    cursor: pointer;

}


.ayuda {

    margin-top: 6px;

    color: #94a3b8;

    font-size: 11px;

}


/* =========================================================
   CONDUCTOR
========================================================= */

.conductor-seleccionado {

    margin-top: 8px;

    padding: 11px 13px;

    border-radius: 9px;

    background: var(--azul-suave);

    border: 1px solid #dbeafe;

    color: var(--azul-oscuro);

    font-size: 12px;

    font-weight: 600;

}


/* =========================================================
   CHECKBOXES
========================================================= */

.check-grid {

    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 14px;

}


.check {

    position: relative;

}


.check input {

    position: absolute;

    opacity: 0;

    pointer-events: none;

}


.check label {

    min-height: 76px;

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 14px;

    border: 1px solid #dbe3ee;

    border-radius: 12px;

    background: #fff;

    cursor: pointer;

    transition: .2s ease;

}


.check label:hover {

    border-color: #93c5fd;

    background: #f8fbff;

}


.check input:checked + label {

    border-color: #93c5fd;

    background: var(--azul-suave);

}


.check-icon {

    width: 38px;

    height: 38px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    background: #f1f5f9;

    color: #64748b;

}


.check input:checked + label .check-icon {

    background: var(--azul);

    color: #fff;

}


.check-text strong {

    display: block;

    font-size: 12px;

}


.check-text span {

    display: block;

    margin-top: 3px;

    color: #94a3b8;

    font-size: 10px;

}


/* =========================================================
   FOTOS EXISTENTES
========================================================= */

.fotos-grid {

    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 15px;

}


.foto-card {

    overflow: hidden;

    border: 1px solid var(--borde);

    border-radius: 13px;

    background: #fff;

}


.foto-preview {

    width: 100%;

    height: 180px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f1f5f9;

    overflow: hidden;

}


.foto-preview img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    cursor: pointer;

    transition: .2s ease;

}


.foto-preview img:hover {

    transform: scale(1.03);

}


.foto-no-disponible {

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    gap: 8px;

    color: #94a3b8;

    font-size: 12px;

    text-align: center;

    padding: 15px;

}


.foto-no-disponible i {

    font-size: 28px;

}


.foto-info {

    padding: 11px 12px;

}


.foto-nombre {

    display: block;

    overflow: hidden;

    color: #334155;

    font-size: 11px;

    font-weight: 700;

    text-overflow: ellipsis;

    white-space: nowrap;

}


.foto-fecha {

    display: block;

    margin-top: 4px;

    color: #94a3b8;

    font-size: 10px;

}


.sin-fotos {

    padding: 22px;

    border: 1px dashed #cbd5e1;

    border-radius: 12px;

    background: #f8fafc;

    color: #64748b;

    text-align: center;

    font-size: 12px;

}


.sin-fotos i {

    display: block;

    margin-bottom: 8px;

    color: #94a3b8;

    font-size: 25px;

}


/* =========================================================
   CARGA DE FOTOS
========================================================= */

.carga-fotos {

    margin-top: 22px;

    padding: 18px;

    border: 1px dashed #93c5fd;

    border-radius: 13px;

    background: #f8fbff;

}


.carga-fotos-titulo {

    display: flex;

    align-items: center;

    gap: 9px;

    margin-bottom: 10px;

    color: var(--azul-oscuro);

    font-size: 13px;

    font-weight: 800;

}


.carga-fotos input[type="file"] {

    padding: 10px;

    background: #fff;

}


.lista-nuevas-fotos {

    display: none;

    margin-top: 12px;

    padding: 10px 12px;

    border-radius: 9px;

    background: #eff6ff;

    color: #1e40af;

    font-size: 11px;

}


/* =========================================================
   BARRA INFERIOR
========================================================= */

.barra-final {

    position: sticky;

    bottom: 15px;

    z-index: 20;

    display: flex;

    justify-content: flex-end;

    gap: 10px;

    padding: 14px;

    margin-top: 25px;

    border: 1px solid var(--borde);

    border-radius: 14px;

    background: rgba(
        255,
        255,
        255,
        .94
    );

    backdrop-filter: blur(12px);

    box-shadow:
        0 10px 30px
        rgba(15, 23, 42, .10);

}


/* =========================================================
   FOOTER
========================================================= */

.footer {

    margin-top: 30px;

    text-align: center;

    color: #94a3b8;

    font-size: 11px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {

    .grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

    }


    .check-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

    }


    .fotos-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

    }

}


@media (max-width: 700px) {

    .contenedor {

        width:
            calc(100% - 24px);

        padding-top: 20px;

    }


    .cabecera {

        flex-direction: column;

    }


    .acciones {

        width: 100%;

    }


    .acciones .btn {

        flex: 1;

    }


    .grid {

        grid-template-columns: 1fr;

    }


    .check-grid {

        grid-template-columns: 1fr;

    }


    .fotos-grid {

        grid-template-columns: 1fr;

    }


    .formulario {

        padding: 18px;

    }


    .tarjeta-cabecera {

        padding: 18px;

    }


    .barra-final {

        flex-direction: column;

    }


    .barra-final .btn {

        width: 100%;

    }

}


/* =========================================================
   IMPRESIÓN
========================================================= */

@media print {

    body {

        background: white;

    }


    .acciones,
    .barra-final,
    .footer,
    .carga-fotos {

        display: none !important;

    }


    .contenedor {

        width: 100%;

        padding: 0;

    }


    .tarjeta {

        box-shadow: none;

        break-inside: avoid;

    }

}

</style>

</head>


<body>


<div class="contenedor">


    <!-- =================================================
         CABECERA
    ================================================== -->

    <div class="cabecera">

        <div>

            <div class="badge">

                <i class="fa-solid fa-pen-to-square"></i>

                Edición de incidente

            </div>


            <h1>

                Incidente
                #<?= escapar($numero_visual) ?>

            </h1>


            <p class="subtitulo">

                Modifique los antecedentes del incidente
                y guarde los cambios.

            </p>

        </div>


        <div class="acciones">

            <a
                href="ver_incidente.php?id=<?= $id ?>"
                class="btn btn-volver"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Cancelar

            </a>

        </div>

    </div>



    <!-- =================================================
         FORMULARIO
    ================================================== -->

    <form
        action="actualizar_incidente.php"
        method="POST"
        id="formIncidente"
        enctype="multipart/form-data"
    >


        <input
            type="hidden"
            name="id"
            value="<?= $id ?>"
        >


        <!-- =============================================
             IDENTIFICACIÓN
        ============================================== -->

        <div class="tarjeta">

            <div class="tarjeta-cabecera">

                <div class="icono">

                    <i class="fa-solid fa-circle-info"></i>

                </div>

                <div>

                    <h2>
                        Identificación
                    </h2>

                    <p>
                        Datos principales del incidente
                    </p>

                </div>

            </div>


            <div class="formulario">

                <div class="grid">


                    <div class="campo">

                        <label class="campo-label">
                            Número de incidente
                        </label>

                        <input
                            type="number"
                            name="numero_incidente"
                            min="1"
                            required
                            value="<?= valor($numero_incidente) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Año del incidente
                        </label>

                        <input
                            type="number"
                            name="anio_incidente"
                            min="2000"
                            max="2100"
                            required
                            value="<?= valor($anio_incidente) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Fecha y hora de ocurrencia
                        </label>

                        <input
                            type="datetime-local"
                            name="fecha_hora_ocurrencia"
                            required
                            value="<?= valor($fecha_hora_ocurrencia) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Fecha y hora del evento
                            <span class="opcional">
                                (opcional)
                            </span>
                        </label>

                        <input
                            type="datetime-local"
                            name="fecha_hora_evento"
                            value="<?= valor($fecha_hora_evento) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Fecha y hora del aviso
                            <span class="opcional">
                                (opcional)
                            </span>
                        </label>

                        <input
                            type="datetime-local"
                            name="fecha_hora_aviso"
                            value="<?= valor($fecha_hora_aviso) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Conductor
                            <span class="opcional">
                                (opcional)
                            </span>
                        </label>

                        <select
                            name="conductor_id"
                            id="conductor_id"
                        >

                            <option value="">
                                — Sin conductor asociado —
                            </option>


                            <?php foreach (
                                $conductores
                                as $conductor
                            ): ?>

                                <option
                                    value="<?= escapar($conductor['id']) ?>"
                                    <?= (
                                        (string) $conductor_id ===
                                        (string) $conductor['id']
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>
                                >

                                    <?= escapar(
                                        $conductor['nombre']
                                    ) ?>

                                    <?php if (
                                        !empty(
                                            $conductor['rut']
                                        )
                                    ): ?>

                                        —
                                        <?= escapar(
                                            $conductor['rut']
                                        ) ?>

                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <div
                            class="conductor-seleccionado"
                            id="infoConductor"
                        >

                            Seleccione un conductor
                            para visualizar su información.

                        </div>

                    </div>


                </div>

            </div>

        </div>



        <!-- =================================================
             ORGANIZACIÓN
        ================================================== -->

        <div class="tarjeta">

            <div class="tarjeta-cabecera">

                <div class="icono">

                    <i class="fa-solid fa-building"></i>

                </div>

                <div>

                    <h2>
                        Información organizacional
                    </h2>

                    <p>
                        Antecedentes y estructura organizacional
                    </p>

                </div>

            </div>


            <div class="formulario">

                <div class="grid">


                    <div class="campo completo">

                        <label class="campo-label">
                            Antecedentes
                            <span class="opcional">
                                (opcional)
                            </span>
                        </label>

                        <textarea
                            name="antecedentes"
                            class="pequena"
                        ><?= valor($antecedentes) ?></textarea>

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            División
                        </label>

                        <input
                            type="text"
                            name="division"
                            value="<?= valor($division) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Subdivisión
                        </label>

                        <input
                            type="text"
                            name="subdivision"
                            value="<?= valor($subdivision) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Planta
                        </label>

                        <input
                            type="text"
                            name="planta"
                            value="<?= valor($planta) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Sucursal
                        </label>

                        <input
                            type="text"
                            name="sucursal"
                            value="<?= valor($sucursal) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Empresa del evento
                        </label>

                        <input
                            type="text"
                            name="empresa_evento"
                            value="<?= valor($empresa_evento) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Área
                        </label>

                        <input
                            type="text"
                            name="area"
                            value="<?= valor($area) ?>"
                        >

                    </div>


                </div>

            </div>

        </div>



        <!-- =================================================
             EQUIPO
        ================================================== -->

        <div class="tarjeta">

            <div class="tarjeta-cabecera">

                <div class="icono">

                    <i class="fa-solid fa-truck"></i>

                </div>

                <div>

                    <h2>
                        Equipo y ubicación
                    </h2>

                    <p>
                        Equipo y lugar donde ocurrió el incidente
                    </p>

                </div>

            </div>


            <div class="formulario">

                <div class="grid">


                    <div class="campo">

                        <label class="campo-label">
                            Equipo
                        </label>

                        <input
                            type="text"
                            name="equipo"
                            value="<?= valor($equipo) ?>"
                        >

                    </div>


                    <div class="campo completo">

                        <label class="campo-label">
                            Lugar del incidente
                        </label>

                        <input
                            type="text"
                            name="lugar_incidente"
                            value="<?= valor($lugar_incidente) ?>"
                        >

                    </div>


                </div>

            </div>

        </div>



        <!-- =================================================
             CLASIFICACIÓN
        ================================================== -->

        <div class="tarjeta">

            <div class="tarjeta-cabecera">

                <div class="icono">

                    <i class="fa-solid fa-tags"></i>

                </div>

                <div>

                    <h2>
                        Clasificación
                    </h2>

                    <p>
                        Categorías y factores relacionados
                    </p>

                </div>

            </div>


            <div class="formulario">

                <div class="grid">


                    <div class="campo">

                        <label class="campo-label">
                            Clasificación del incidente
                        </label>

                        <input
                            type="text"
                            name="clasificacion_incidente"
                            value="<?= valor(
                                $clasificacion_incidente
                            ) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Exposición LGF
                        </label>

                        <input
                            type="text"
                            name="exposicion_lgf"
                            value="<?= valor(
                                $exposicion_lgf
                            ) ?>"
                        >

                    </div>


                    <div class="campo completo">

                        <label class="campo-label">
                            Riesgo asociado
                        </label>

                        <input
                            type="text"
                            name="riesgo_asociado"
                            value="<?= valor(
                                $riesgo_asociado
                            ) ?>"
                        >

                    </div>


                </div>

            </div>

        </div>



        <!-- =================================================
             INDICADORES
        ================================================== -->

        <div class="tarjeta">

            <div class="tarjeta-cabecera">

                <div class="icono">

                    <i class="fa-solid fa-triangle-exclamation"></i>

                </div>

                <div>

                    <h2>
                        Indicadores
                    </h2>

                    <p>
                        Marque las condiciones presentes
                    </p>

                </div>

            </div>


            <div class="formulario">

                <div class="check-grid">


                    <div class="check">

                        <input
                            type="checkbox"
                            id="dano_propiedad"
                            name="dano_propiedad"
                            <?= $dano_propiedad
                                ? 'checked'
                                : ''
                            ?>
                        >

                        <label for="dano_propiedad">

                            <div class="check-icon">

                                <i class="fa-solid fa-house-damage"></i>

                            </div>

                            <div class="check-text">

                                <strong>
                                    Daño a propiedad
                                </strong>

                                <span>
                                    Existió daño material
                                </span>

                            </div>

                        </label>

                    </div>



                    <div class="check">

                        <input
                            type="checkbox"
                            id="dano_personas"
                            name="dano_personas"
                            <?= $dano_personas
                                ? 'checked'
                                : ''
                            ?>
                        >

                        <label for="dano_personas">

                            <div class="check-icon">

                                <i class="fa-solid fa-user-injured"></i>

                            </div>

                            <div class="check-text">

                                <strong>
                                    Daño a personas
                                </strong>

                                <span>
                                    Hubo personas afectadas
                                </span>

                            </div>

                        </label>

                    </div>



                    <div class="check">

                        <input
                            type="checkbox"
                            id="emergencia"
                            name="emergencia"
                            <?= $emergencia
                                ? 'checked'
                                : ''
                            ?>
                        >

                        <label for="emergencia">

                            <div class="check-icon">

                                <i class="fa-solid fa-triangle-exclamation"></i>

                            </div>

                            <div class="check-text">

                                <strong>
                                    Emergencia
                                </strong>

                                <span>
                                    Se declaró emergencia
                                </span>

                            </div>

                        </label>

                    </div>



                    <div class="check">

                        <input
                            type="checkbox"
                            id="accidente_grave_fatal"
                            name="accidente_grave_fatal"
                            <?= $accidente_grave_fatal
                                ? 'checked'
                                : ''
                            ?>
                        >

                        <label for="accidente_grave_fatal">

                            <div class="check-icon">

                                <i class="fa-solid fa-skull-crossbones"></i>

                            </div>

                            <div class="check-text">

                                <strong>
                                    Accidente grave / fatal
                                </strong>

                                <span>
                                    Condición grave o fatal
                                </span>

                            </div>

                        </label>

                    </div>


                </div>

            </div>

        </div>



        <!-- =================================================
             PERSONA AFECTADA
        ================================================== -->

        <div class="tarjeta">

            <div class="tarjeta-cabecera">

                <div class="icono">

                    <i class="fa-solid fa-person"></i>

                </div>

                <div>

                    <h2>
                        Persona afectada
                    </h2>

                    <p>
                        Información de la persona involucrada
                    </p>

                </div>

            </div>


            <div class="formulario">

                <div class="grid">


                    <div class="campo">

                        <label class="campo-label">
                            Nombre
                        </label>

                        <input
                            type="text"
                            name="afectado_nombre"
                            value="<?= valor(
                                $afectado_nombre
                            ) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            RUT
                        </label>

                        <input
                            type="text"
                            name="afectado_rut"
                            value="<?= valor(
                                $afectado_rut
                            ) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Sexo
                        </label>

                        <select name="afectado_sexo">

                            <option value="">
                                — Seleccionar —
                            </option>

                            <option
                                value="MASCULINO"
                                <?= $afectado_sexo === 'MASCULINO'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                Masculino
                            </option>

                            <option
                                value="FEMENINO"
                                <?= $afectado_sexo === 'FEMENINO'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                Femenino
                            </option>

                            <option
                                value="OTRO"
                                <?= $afectado_sexo === 'OTRO'
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                Otro
                            </option>

                        </select>

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Edad
                        </label>

                        <input
                            type="number"
                            name="afectado_edad"
                            min="0"
                            max="120"
                            value="<?= valor(
                                $afectado_edad
                            ) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Empresa involucrada
                        </label>

                        <input
                            type="text"
                            name="empresa_involucrada"
                            value="<?= valor(
                                $empresa_involucrada
                            ) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Cargo
                        </label>

                        <input
                            type="text"
                            name="cargo"
                            value="<?= valor($cargo) ?>"
                        >

                    </div>


                    <div class="campo completo">

                        <label class="campo-label">
                            Partes del cuerpo
                        </label>

                        <input
                            type="text"
                            name="partes_cuerpo"
                            value="<?= valor(
                                $partes_cuerpo
                            ) ?>"
                        >

                    </div>


                </div>

            </div>

        </div>



        <!-- =================================================
             DESCRIPCIÓN
        ================================================== -->

        <div class="tarjeta">

            <div class="tarjeta-cabecera">

                <div class="icono">

                    <i class="fa-solid fa-file-lines"></i>

                </div>

                <div>

                    <h2>
                        Descripción del incidente
                    </h2>

                    <p>
                        Detalle del hecho y descripción preliminar
                    </p>

                </div>

            </div>


            <div class="formulario">

                <div class="grid">


                    <div class="campo completo">

                        <label class="campo-label">
                            Detalle del incidente
                        </label>

                        <textarea
                            name="detalle_incidente"
                        ><?= valor(
                            $detalle_incidente
                        ) ?></textarea>

                    </div>


                    <div class="campo completo">

                        <label class="campo-label">
                            Descripción preliminar
                        </label>

                        <textarea
                            name="descripcion_preliminar"
                        ><?= valor(
                            $descripcion_preliminar
                        ) ?></textarea>

                    </div>


                </div>

            </div>

        </div>



        <!-- =================================================
             FOTOGRAFÍAS / EVIDENCIA
        ================================================== -->

        <div class="tarjeta">

            <div class="tarjeta-cabecera">

                <div class="icono">

                    <i class="fa-solid fa-images"></i>

                </div>

                <div>

                    <h2>
                        Fotografías y evidencia
                    </h2>

                    <p>
                        Evidencia fotográfica asociada al incidente
                    </p>

                </div>

            </div>


            <div class="formulario">


                <?php if (!empty($fotos)): ?>

                    <div class="fotos-grid">

                        <?php foreach (
                            $fotos
                            as $foto
                        ): ?>

                            <?php

                            $archivo =
                                trim(
                                    $foto['archivo'] ?? ''
                                );

                            $nombre_original =
                                trim(
                                    $foto['nombre_original'] ?? ''
                                );

                            $ruta_foto =
                                'incidentes_fotos/' .
                                rawurlencode($archivo);

                            $extension =
                                strtolower(
                                    pathinfo(
                                        $archivo,
                                        PATHINFO_EXTENSION
                                    )
                                );

                            $es_imagen =
                                in_array(
                                    $extension,
                                    [
                                        'jpg',
                                        'jpeg',
                                        'png',
                                        'gif',
                                        'webp'
                                    ],
                                    true
                                );

                            ?>

                            <div class="foto-card">

                                <div class="foto-preview">

                                    <?php if (
                                        $es_imagen &&
                                        $archivo !== ''
                                    ): ?>

                                        <img
                                            src="<?= escapar($ruta_foto) ?>"
                                            alt="<?= escapar(
                                                $nombre_original
                                            ) ?>"
                                            onclick="abrirFoto(
                                                this.src,
                                                '<?= escapar(
                                                    $nombre_original
                                                ) ?>'
                                            )"
                                        >

                                    <?php else: ?>

                                        <div class="foto-no-disponible">

                                            <i class="fa-solid fa-file"></i>

                                            <span>
                                                Archivo de evidencia
                                            </span>

                                        </div>

                                    <?php endif; ?>

                                </div>


                                <div class="foto-info">

                                    <span
                                        class="foto-nombre"
                                        title="<?= escapar(
                                            $nombre_original
                                        ) ?>"
                                    >

                                        <?= escapar(
                                            $nombre_original !== ''
                                                ? $nombre_original
                                                : $archivo
                                        ) ?>

                                    </span>


                                    <?php if (
                                        !empty(
                                            $foto['fecha_subida']
                                        )
                                    ): ?>

                                        <span class="foto-fecha">

                                            <i class="fa-regular fa-clock"></i>

                                            <?= escapar(
                                                date(
                                                    'd/m/Y H:i',
                                                    strtotime(
                                                        $foto['fecha_subida']
                                                    )
                                                )
                                            ) ?>

                                        </span>

                                    <?php endif; ?>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="sin-fotos">

                        <i class="fa-regular fa-images"></i>

                        Este incidente todavía no tiene
                        fotografías o evidencias asociadas.

                    </div>

                <?php endif; ?>


                <div class="carga-fotos">

                    <div class="carga-fotos-titulo">

                        <i class="fa-solid fa-cloud-arrow-up"></i>

                        Agregar nuevas fotografías

                    </div>


                    <input
                        type="file"
                        name="fotos[]"
                        id="fotos"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        multiple
                    >


                    <div class="ayuda">

                        Puede seleccionar una o varias imágenes.
                        Las fotografías existentes se mantienen.

                    </div>


                    <div
                        class="lista-nuevas-fotos"
                        id="listaNuevasFotos"
                    ></div>

                </div>


            </div>

        </div>



        <!-- =================================================
             RESPONSABLES
        ================================================== -->

        <div class="tarjeta">

            <div class="tarjeta-cabecera">

                <div class="icono">

                    <i class="fa-solid fa-user-check"></i>

                </div>

                <div>

                    <h2>
                        Responsables y aviso
                    </h2>

                    <p>
                        Información de comunicación y responsables
                    </p>

                </div>

            </div>


            <div class="formulario">

                <div class="grid">


                    <div class="campo">

                        <label class="campo-label">
                            Avisado por
                        </label>

                        <input
                            type="text"
                            name="avisado_por"
                            value="<?= valor(
                                $avisado_por
                            ) ?>"
                        >

                    </div>


                    <div class="campo">

                        <label class="campo-label">
                            Realizado por
                        </label>

                        <input
                            type="text"
                            name="realizado_por"
                            value="<?= valor(
                                $realizado_por
                            ) ?>"
                        >

                    </div>


                </div>

            </div>

        </div>



        <!-- =================================================
             BARRA FINAL
        ================================================== -->

        <div class="barra-final">

            <a
                href="ver_incidente.php?id=<?= $id ?>"
                class="btn btn-volver"
            >

                <i class="fa-solid fa-xmark"></i>

                Cancelar

            </a>


            <button
                type="submit"
                class="btn btn-guardar"
            >

                <i class="fa-solid fa-floppy-disk"></i>

                Guardar cambios

            </button>

        </div>


    </form>



    <div class="footer">

        Sistema de Incidentes ·
        Edición de registro

    </div>


</div>


<!-- =========================================================
     MODAL IMAGEN
========================================================= -->

<div
    id="modalFoto"
    style="
        display:none;
        position:fixed;
        inset:0;
        z-index:9999;
        background:rgba(15,23,42,.88);
        align-items:center;
        justify-content:center;
        padding:25px;
    "
    onclick="cerrarFoto()"
>

    <div
        style="
            position:relative;
            max-width:1100px;
            max-height:90vh;
        "
        onclick="event.stopPropagation()"
    >

        <button
            type="button"
            onclick="cerrarFoto()"
            style="
                position:absolute;
                top:-45px;
                right:0;
                width:38px;
                height:38px;
                border:0;
                border-radius:50%;
                background:#fff;
                color:#0f172a;
                cursor:pointer;
                font-size:18px;
            "
        >

            <i class="fa-solid fa-xmark"></i>

        </button>


        <img
            id="imagenModal"
            src=""
            alt=""
            style="
                display:block;
                max-width:100%;
                max-height:85vh;
                border-radius:12px;
                box-shadow:0 20px 60px rgba(0,0,0,.35);
            "
        >

    </div>

</div>


<script>

/* =========================================================
   INFORMACIÓN DEL CONDUCTOR
========================================================= */

const selectConductor =
    document.getElementById(
        'conductor_id'
    );


const infoConductor =
    document.getElementById(
        'infoConductor'
    );


const conductores =
    <?= json_encode(
        $conductores,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ) ?>;


function actualizarConductor()
{

    const id =
        selectConductor.value;


    if (!id) {

        infoConductor.innerHTML =
            'Seleccione un conductor ' +
            'para visualizar su información.';

        return;

    }


    const conductor =
        conductores.find(
            function(item)
            {
                return String(item.id) ===
                    String(id);
            }
        );


    if (!conductor) {

        infoConductor.innerHTML =
            'No fue posible cargar la información del conductor.';

        return;

    }


    let html =
        '<strong>' +
        escaparJS(conductor.nombre || '') +
        '</strong>';


    if (conductor.rut) {

        html +=
            ' · RUT: ' +
            escaparJS(conductor.rut);

    }


    if (conductor.area) {

        html +=
            ' · Área: ' +
            escaparJS(conductor.area);

    }


    infoConductor.innerHTML =
        html;

}


function escaparJS(valor)
{

    return String(valor)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

}


selectConductor.addEventListener(
    'change',
    actualizarConductor
);


actualizarConductor();


/* =========================================================
   MOSTRAR ARCHIVOS NUEVOS SELECCIONADOS
========================================================= */

const inputFotos =
    document.getElementById(
        'fotos'
    );


const listaNuevasFotos =
    document.getElementById(
        'listaNuevasFotos'
    );


inputFotos.addEventListener(
    'change',
    function()
    {

        const archivos =
            Array.from(
                inputFotos.files
            );


        if (!archivos.length) {

            listaNuevasFotos.style.display =
                'none';

            listaNuevasFotos.innerHTML =
                '';

            return;

        }


        let html =
            '<strong>' +
            archivos.length +
            '</strong> archivo(s) seleccionado(s):<br>';


        archivos.forEach(
            function(archivo)
            {

                html +=
                    '• ' +
                    escaparJS(
                        archivo.name
                    ) +
                    '<br>';

            }
        );


        listaNuevasFotos.innerHTML =
            html;


        listaNuevasFotos.style.display =
            'block';

    }
);


/* =========================================================
   MODAL DE FOTOGRAFÍA
========================================================= */

function abrirFoto(
    src,
    nombre
)
{

    const modal =
        document.getElementById(
            'modalFoto'
        );


    const imagen =
        document.getElementById(
            'imagenModal'
        );


    imagen.src =
        src;


    imagen.alt =
        nombre || 'Fotografía del incidente';


    modal.style.display =
        'flex';


    document.body.style.overflow =
        'hidden';

}


function cerrarFoto()
{

    const modal =
        document.getElementById(
            'modalFoto'
        );


    const imagen =
        document.getElementById(
            'imagenModal'
        );


    modal.style.display =
        'none';


    imagen.src =
        '';


    document.body.style.overflow =
        '';

}


/* =========================================================
   ESCAPE PARA CERRAR MODAL
========================================================= */

document.addEventListener(
    'keydown',
    function(event)
    {

        if (
            event.key === 'Escape'
        ) {

            cerrarFoto();

        }

    }
);


/* =========================================================
   CONFIRMACIÓN ANTES DE GUARDAR
========================================================= */

document
    .getElementById('formIncidente')
    .addEventListener(
        'submit',
        function(event)
        {

            const confirmar =
                confirm(
                    '¿Desea guardar los cambios realizados en este incidente?'
                );


            if (!confirmar) {

                event.preventDefault();

            }

        }
    );

</script>


</body>

</html>