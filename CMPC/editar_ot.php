<?php

include "conexion.php";


/* =========================================================
   VALIDAR ID
========================================================= */

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location:index.php");
    exit;
}


/* =========================================================
   FUNCIONES
========================================================= */

function escapar($texto)
{
    return htmlspecialchars(
        $texto ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


function fechaInput($fecha)
{
    if (!$fecha) {
        return '';
    }

    return date(
        'Y-m-d',
        strtotime($fecha)
    );
}


/* =========================================================
   OT
========================================================= */

$stmt = $conn->prepare("
    SELECT
        ot.*,

        c.rut AS conductor_rut,
        c.nombre AS conductor_nombre,
        c.area AS conductor_area

    FROM ordenes_trabajo ot

    LEFT JOIN conductores c
        ON c.id = ot.conductor_id

    WHERE ot.id = ?

    LIMIT 1
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$ot = $stmt->get_result()->fetch_assoc();

if (!$ot) {
    header("Location:index.php");
    exit;
}


$patente = trim(
    $ot['patente'] ?? ''
);


/* =========================================================
   EQUIPO
========================================================= */

$stmt = $conn->prepare("
    SELECT *
    FROM equipos
    WHERE patente = ?
    LIMIT 1
");

$stmt->bind_param(
    "s",
    $patente
);

$stmt->execute();

$equipo = $stmt->get_result()->fetch_assoc();


/* =========================================================
   MAESTROS
========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        maestro

    FROM ordenes_trabajo_maestros

    WHERE orden_trabajo_id = ?

    ORDER BY id ASC
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$resultadoMaestros = $stmt->get_result();

$maestros = [];

while ($fila = $resultadoMaestros->fetch_assoc()) {
    $maestros[] = $fila['maestro'];
}


/* =========================================================
   CONDUCTORES
========================================================= */

$resultadoConductores = $conn->query("
    SELECT
        id,
        rut,
        nombre,
        area

    FROM conductores

    WHERE estado <> 'DESVINCULADO'

    ORDER BY nombre ASC
");


/* =========================================================
   DOCUMENTO ACTUAL
========================================================= */

$documentoActual =
    trim(
        $ot['documento_ot'] ?? ''
    );

$existeDocumentoActual = false;

if ($documentoActual !== '') {

    $rutaFisicaActual =
        __DIR__ .
        DIRECTORY_SEPARATOR .
        'documentos_ot' .
        DIRECTORY_SEPARATOR .
        $documentoActual;

    $existeDocumentoActual =
        is_file($rutaFisicaActual);
}

?>
<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>
    Editar OT #
    <?=str_pad(
        $ot['numero_ot'],
        4,
        '0',
        STR_PAD_LEFT
    )?>
</title>

<link
    rel="icon"
    type="image/png"
    href="/CMPC/favicon.png"
>

<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<style>

:root{

    --primary:#2563eb;
    --primary-dark:#1e40af;

    --bg1:#f1f5f9;
    --bg2:#e0e7ff;

}

*{

    box-sizing:border-box;
    font-family:Inter,sans-serif;

}

body{

    margin:0;
    padding:30px;

    min-height:100vh;

    background:
    linear-gradient(
        180deg,
        var(--bg1),
        var(--bg2)
    );

    color:#0f172a;

}


/* =========================================================
   BANNER
========================================================= */

.banner-wrap{

    position:relative;
    margin-bottom:30px;

}

.banner{

    width:100%;
    max-height:220px;

    object-fit:cover;

    border-radius:20px;

    display:block;

}

.banner-overlay{

    position:absolute;
    inset:0;

    border-radius:20px;

    background:
    linear-gradient(
        90deg,
        rgba(15,23,42,.82),
        rgba(15,23,42,.2),
        transparent
    );

}

.banner-text{

    position:absolute;

    left:30px;
    bottom:20px;

    color:white;

}

.banner-text h1{

    margin:0;
    font-size:34px;

}

.banner-text p{

    margin:7px 0 0;
    opacity:.9;

}

.banner-datetime{

    position:absolute;

    right:25px;
    bottom:20px;

    background:rgba(255,255,255,.18);

    backdrop-filter:blur(10px);

    padding:12px 18px;

    border-radius:16px;

    color:white;

    text-align:right;

    border:1px solid rgba(255,255,255,.3);

}

#banner-time{

    font-size:26px;
    font-weight:800;

}


/* =========================================================
   ACCIONES
========================================================= */

.acciones{

    display:flex;
    gap:12px;

    margin-bottom:25px;

    flex-wrap:wrap;

}

.btn{

    display:inline-block;

    border:0;

    padding:12px 18px;

    border-radius:12px;

    cursor:pointer;

    font-weight:700;

    color:white;

    background:
    linear-gradient(
        135deg,
        var(--primary),
        var(--primary-dark)
    );

    text-decoration:none;

}

.btn-secondary{

    background:#64748b;

}


/* =========================================================
   PANEL
========================================================= */

.panel{

    background:white;

    padding:28px;

    border-radius:20px;

    box-shadow:
    0 10px 30px rgba(0,0,0,.08);

}

.panel h2{

    margin-top:0;

    margin-bottom:25px;

}


/* =========================================================
   FORMULARIO
========================================================= */

.form-grid{

    display:grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:18px;

}

.form-group{

    display:flex;

    flex-direction:column;

    gap:7px;

}

.form-group.full{

    grid-column:1 / -1;

}

label{

    font-size:12px;

    font-weight:800;

    color:#475569;

    text-transform:uppercase;

}

input,
select,
textarea{

    width:100%;

    border:1px solid #cbd5e1;

    border-radius:12px;

    padding:13px 14px;

    font-size:14px;

    outline:none;

    background:white;

}

input:focus,
select:focus,
textarea:focus{

    border-color:#2563eb;

    box-shadow:
    0 0 0 3px rgba(37,99,235,.1);

}

textarea{

    min-height:130px;

    resize:vertical;

}


/* =========================================================
   EQUIPO INFO
========================================================= */

.equipo-info{

    display:grid;

    grid-template-columns:
        repeat(4,1fr);

    gap:12px;

    margin-bottom:25px;

}

.equipo-item{

    padding:15px;

    border-radius:12px;

    background:#f8fafc;

    border:1px solid #e2e8f0;

}

.equipo-item small{

    display:block;

    color:#64748b;

    font-size:11px;

    font-weight:800;

    margin-bottom:5px;

}

.equipo-item strong{

    font-size:14px;

}


/* =========================================================
   MAESTROS
========================================================= */

.maestros-wrap{

    grid-column:1 / -1;

    padding:20px;

    background:#f8fafc;

    border:1px solid #e2e8f0;

    border-radius:15px;

}

.maestros-list{

    display:flex;

    flex-direction:column;

    gap:10px;

}

.maestro-row{

    display:flex;

    gap:10px;

}

.maestro-row input{

    flex:1;

}

.btn-maestro{

    width:48px;

    padding:0;

    border-radius:10px;

}

.btn-add{

    margin-top:12px;

    background:#0f766e;

}


/* =========================================================
   DOCUMENTO
========================================================= */

.documento-wrap{

    grid-column:1 / -1;

    padding:20px;

    border-radius:15px;

    background:#f8fafc;

    border:1px solid #dbe3ef;

}

.documento-actual{

    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:15px;

    flex-wrap:wrap;

    padding:15px;

    background:white;

    border:1px solid #e2e8f0;

    border-radius:12px;

    margin-bottom:15px;

}

.documento-nombre{

    display:flex;

    align-items:center;

    gap:10px;

    font-weight:700;

    word-break:break-all;

}

.documento-acciones{

    display:flex;

    gap:8px;

    flex-wrap:wrap;

}

.btn-documento{

    padding:9px 13px;

    border-radius:10px;

    font-size:12px;

}

.sin-documento{

    color:#64748b;

    padding:10px 0;

}

.archivo-ayuda{

    margin-top:8px;

    color:#64748b;

    font-size:12px;

}


/* =========================================================
   BOTONES FORM
========================================================= */

.form-actions{

    display:flex;

    justify-content:flex-end;

    gap:12px;

    margin-top:25px;

    padding-top:20px;

    border-top:1px solid #e2e8f0;

}


/* =========================================================
   FOOTER
========================================================= */

footer{

    margin-top:40px;

    padding:30px;

    border-radius:20px;

    background:
    linear-gradient(
        135deg,
        #1e3a8a,
        #2563eb
    );

    color:white;

    text-align:center;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:900px){

    .form-grid{

        grid-template-columns:1fr;

    }

    .equipo-info{

        grid-template-columns:
            repeat(2,1fr);

    }

}

@media(max-width:650px){

    body{

        padding:15px;

    }

    .equipo-info{

        grid-template-columns:1fr;

    }

    .banner-text h1{

        font-size:24px;

    }

    .banner-datetime{

        display:none;

    }

    .form-actions{

        flex-direction:column;

    }

}

</style>

</head>

<body>


<?php include '../shared/menu.php'; ?>


<!-- =======================================================
     BANNER
======================================================= -->

<div class="banner-wrap">

    <img
        src="banner1.jpg"
        class="banner"
        alt="Editar Orden de Trabajo"
    >

    <div class="banner-overlay"></div>

    <div class="banner-text">

        <h1>
            Editar Orden de Trabajo
        </h1>

        <p>
            OT #

            <?=str_pad(
                $ot['numero_ot'],
                4,
                '0',
                STR_PAD_LEFT
            )?>

            · Equipo <?=escapar($patente)?>

        </p>

    </div>

    <div class="banner-datetime">

        <div id="banner-date"></div>
        <div id="banner-time"></div>

    </div>

</div>


<!-- =======================================================
     ACCIONES
======================================================= -->

<div class="acciones">

    <a
        href="detalle_ot.php?id=<?=$id?>"
        class="btn btn-secondary"
    >
        ← Cancelar
    </a>

</div>


<!-- =======================================================
     PANEL
======================================================= -->

<div class="panel">

    <h2>
        ✏️ Editar información de la OT
    </h2>


    <!-- =====================================================
         EQUIPO
    ===================================================== -->

    <div class="equipo-info">

        <div class="equipo-item">

            <small>Patente</small>

            <strong>
                <?=escapar($patente)?>
            </strong>

        </div>


        <div class="equipo-item">

            <small>Tipo</small>

            <strong>
                <?=escapar($equipo['tipo'] ?? '-')?>
            </strong>

        </div>


        <div class="equipo-item">

            <small>Marca</small>

            <strong>
                <?=escapar($equipo['marca'] ?? '-')?>
            </strong>

        </div>


        <div class="equipo-item">

            <small>Modelo</small>

            <strong>
                <?=escapar($equipo['modelo'] ?? '-')?>
            </strong>

        </div>

    </div>


    <!-- =====================================================
         FORMULARIO
    ===================================================== -->

    <form
        action="actualizar_ot.php"
        method="POST"
        enctype="multipart/form-data"
        id="formEditarOT"
    >

        <input
            type="hidden"
            name="id"
            value="<?=$id?>"
        >


        <div class="form-grid">


            <!-- FECHA INGRESO -->

            <div class="form-group">

                <label>
                    Fecha de ingreso
                </label>

                <input
                    type="date"
                    name="fecha_ingreso"
                    value="<?=escapar(
                        fechaInput(
                            $ot['fecha_ingreso']
                        )
                    )?>"
                    required
                >

            </div>


            <!-- CONDUCTOR -->

            <div class="form-group">

                <label>
                    Conductor
                </label>

                <select name="conductor_id">

                    <option value="">
                        Sin conductor
                    </option>

                    <?php while(
                        $conductor =
                        $resultadoConductores->fetch_assoc()
                    ){ ?>

                        <option
                            value="<?=$conductor['id']?>"
                            <?=(
                                (int)$ot['conductor_id']
                                ===
                                (int)$conductor['id']
                            )
                            ? 'selected'
                            : ''
                            ?>
                        >

                            <?=escapar(
                                $conductor['nombre']
                            )?>

                            ·

                            <?=escapar(
                                $conductor['rut']
                            )?>

                        </option>

                    <?php } ?>

                </select>

            </div>


            <!-- ESTADO -->

            <div class="form-group">

                <label>
                    Estado
                </label>

                <select
                    name="estado"
                    required
                >

                    <?php
                    $estados = [
                        'ABIERTA',
                        'EN PROCESO',
                        'CERRADA',
                        'ANULADA'
                    ];

                    foreach($estados as $estado){
                    ?>

                        <option
                            value="<?=escapar($estado)?>"
                            <?=(
                                $ot['estado'] === $estado
                            )
                            ? 'selected'
                            : ''
                            ?>
                        >

                            <?=escapar($estado)?>

                        </option>

                    <?php } ?>

                </select>

            </div>


            <!-- FECHA SALIDA -->

            <div class="form-group">

                <label>
                    Fecha de salida
                </label>

                <input
                    type="date"
                    name="fecha_salida"
                    value="<?=escapar(
                        fechaInput(
                            $ot['fecha_salida']
                        )
                    )?>"
                >

            </div>


            <!-- ODOMETRO -->

            <div class="form-group">

                <label>
                    Odómetro
                </label>

                <input
                    type="number"
                    name="odometro"
                    min="0"
                    step="1"
                    value="<?=escapar(
                        $ot['odometro']
                    )?>"
                    placeholder="Ej: 185000"
                >

            </div>


            <!-- =================================================
                 MOTIVO
            ================================================= -->

            <div class="form-group full">

                <label>
                    Motivo de ingreso
                </label>

                <textarea
    name="motivo_ingreso"
    placeholder="Indique el motivo de ingreso del equipo..."
><?=escapar(
    $ot['motivo_ingreso']
)?></textarea>

            </div>


            <!-- =================================================
                 MAESTROS
            ================================================= -->

            <div class="maestros-wrap">

                <label>
                    Maestros encargados
                </label>

                <br><br>

                <div
                    class="maestros-list"
                    id="maestrosList"
                >

                    <?php if (!empty($maestros)) { ?>

                        <?php foreach($maestros as $maestro){ ?>

                            <div class="maestro-row">

                                <input
                                    type="text"
                                    name="maestros[]"
                                    value="<?=escapar(
                                        $maestro
                                    )?>"
                                    placeholder="Nombre del maestro"
                                >

                                <button
                                    type="button"
                                    class="btn-maestro"
                                    onclick="eliminarMaestro(this)"
                                >
                                    ×
                                </button>

                            </div>

                        <?php } ?>

                    <?php } else { ?>

                        <div class="maestro-row">

                            <input
                                type="text"
                                name="maestros[]"
                                placeholder="Nombre del maestro"
                            >

                            <button
                                type="button"
                                class="btn-maestro"
                                onclick="eliminarMaestro(this)"
                            >
                                ×
                            </button>

                        </div>

                    <?php } ?>

                </div>


                <button
                    type="button"
                    class="btn btn-add"
                    onclick="agregarMaestro()"
                >
                    + Agregar maestro
                </button>

            </div>


            <!-- =================================================
                 DOCUMENTO
            ================================================= -->

            <div class="documento-wrap">

                <label>
                    Documento / SCAN de la OT
                </label>

                <br><br>


                <?php if ($documentoActual !== '') { ?>

                    <div class="documento-actual">

                        <div class="documento-nombre">

                            📎

                            <?=escapar(
                                $documentoActual
                            )?>

                        </div>


                        <?php if (
                            $existeDocumentoActual
                        ) { ?>

                            <div class="documento-acciones">

                                <a
                                    href="documentos_ot/<?=rawurlencode($documentoActual)?>"
                                    target="_blank"
                                    class="btn btn-documento"
                                >
                                    👁️ Ver actual
                                </a>

                            </div>

                        <?php } ?>

                    </div>

                <?php } else { ?>

                    <div class="sin-documento">

                        📎 No existe un documento
                        asociado actualmente.

                    </div>

                <?php } ?>


                <input
                    type="file"
                    name="documento_ot"
                    id="documento_ot"
                    accept=".pdf,.jpg,.jpeg,.png"
                >

                <div class="archivo-ayuda">

                    Solo selecciona un archivo si deseas
                    reemplazar el documento actual.

                    <br>

                    Formatos permitidos:
                    PDF, JPG, JPEG y PNG.
                    Tamaño máximo: 10 MB.

                </div>

            </div>


        </div>


        <!-- =================================================
             BOTONES
        ================================================= -->

        <div class="form-actions">

            <a
                href="detalle_ot.php?id=<?=$id?>"
                class="btn btn-secondary"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn"
            >
                💾 Guardar cambios
            </button>

        </div>

    </form>

</div>


<footer>

    <strong>
        Transportes Mimbral LTDA
    </strong>

    <br>

    Gestión empresarial · © <?=date('Y')?>

</footer>


<script>


/* =========================================================
   HORA
========================================================= */

function actualizarHora(){

    const now = new Date();

    document.getElementById(
        'banner-date'
    ).innerText =
        now.toLocaleDateString(
            'es-CL',
            {
                weekday:'long',
                day:'2-digit',
                month:'long',
                year:'numeric'
            }
        );

    document.getElementById(
        'banner-time'
    ).innerText =
        now.toLocaleTimeString(
            'es-CL'
        );

}

setInterval(
    actualizarHora,
    1000
);

actualizarHora();


/* =========================================================
   MAESTROS
========================================================= */

function agregarMaestro(){

    const lista =
        document.getElementById(
            'maestrosList'
        );

    const fila =
        document.createElement('div');

    fila.className =
        'maestro-row';

    fila.innerHTML = `

        <input
            type="text"
            name="maestros[]"
            placeholder="Nombre del maestro"
        >

        <button
            type="button"
            class="btn-maestro"
            onclick="eliminarMaestro(this)"
        >
            ×
        </button>

    `;

    lista.appendChild(fila);

}


function eliminarMaestro(boton){

    const lista =
        document.getElementById(
            'maestrosList'
        );

    const filas =
        lista.querySelectorAll(
            '.maestro-row'
        );

    if(filas.length <= 1){

        filas[0]
            .querySelector('input')
            .value = '';

        return;

    }

    boton
        .closest('.maestro-row')
        .remove();

}


/* =========================================================
   VALIDAR DOCUMENTO
========================================================= */

document
    .getElementById('documento_ot')
    .addEventListener(
        'change',
        function(){

            const archivo =
                this.files[0];

            if(!archivo){
                return;
            }


            const maximo =
                10 * 1024 * 1024;


            const extensiones = [
                'pdf',
                'jpg',
                'jpeg',
                'png'
            ];


            const extension =
                archivo.name
                    .split('.')
                    .pop()
                    .toLowerCase();


            if(
                !extensiones.includes(
                    extension
                )
            ){

                alert(
                    'Formato no permitido. Solo PDF, JPG, JPEG y PNG.'
                );

                this.value = '';

                return;

            }


            if(
                archivo.size > maximo
            ){

                alert(
                    'El archivo supera el máximo permitido de 10 MB.'
                );

                this.value = '';

                return;

            }

        }
    );

</script>

</body>
</html>