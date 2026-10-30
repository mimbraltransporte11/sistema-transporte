```php
<?php

include "conexion.php";

$patente = trim($_GET['patente'] ?? '');

if ($patente === '') {
    header("Location: index.php");
    exit;
}

/* =========================================================
   EQUIPO
========================================================= */

$stmt = $conn->prepare("
    SELECT 
        e.*,
        c.id AS conductor_actual_id,
        c.rut AS conductor_actual_rut,
        c.nombre AS conductor_actual_nombre,
        c.area AS conductor_actual_area
    FROM equipos e
    LEFT JOIN conductores c
        ON c.id = e.conductor_id
    WHERE e.patente = ?
");

$stmt->bind_param("s", $patente);
$stmt->execute();

$equipo = $stmt->get_result()->fetch_assoc();

if (!$equipo) {
    header("Location: index.php");
    exit;
}


/* =========================================================
   RESUMEN EVENTOS
========================================================= */

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total_eventos,
        MAX(fecha) AS ultimo_evento
    FROM historial
    WHERE patente = ?
");

$stmt->bind_param("s", $patente);
$stmt->execute();

$resumenEventos = $stmt->get_result()->fetch_assoc();

$totalEventos = (int)($resumenEventos['total_eventos'] ?? 0);
$ultimoEvento = $resumenEventos['ultimo_evento'] ?? null;


/* =========================================================
   RESUMEN OT
========================================================= */

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total_ot,
        SUM(CASE WHEN estado = 'ABIERTA' THEN 1 ELSE 0 END) AS abiertas,
        SUM(CASE WHEN estado = 'EN PROCESO' THEN 1 ELSE 0 END) AS proceso,
        SUM(CASE WHEN estado = 'CERRADA' THEN 1 ELSE 0 END) AS cerradas,
        SUM(CASE WHEN estado = 'ANULADA' THEN 1 ELSE 0 END) AS anuladas,
        MAX(fecha_ingreso) AS ultima_ot
    FROM ordenes_trabajo
    WHERE patente = ?
");

$stmt->bind_param("s", $patente);
$stmt->execute();

$resumenOT = $stmt->get_result()->fetch_assoc();

$totalOT = (int)($resumenOT['total_ot'] ?? 0);
$otAbiertas = (int)($resumenOT['abiertas'] ?? 0);
$otProceso = (int)($resumenOT['proceso'] ?? 0);
$otCerradas = (int)($resumenOT['cerradas'] ?? 0);
$otAnuladas = (int)($resumenOT['anuladas'] ?? 0);
$ultimaOT = $resumenOT['ultima_ot'] ?? null;


/* =========================================================
   PROXIMO NUMERO OT
========================================================= */

$resultadoOT = $conn->query("
    SELECT COALESCE(MAX(numero_ot), 0) + 1 AS siguiente
    FROM ordenes_trabajo
");

$filaOT = $resultadoOT->fetch_assoc();

$siguienteOT = (int)($filaOT['siguiente'] ?? 1);


/* =========================================================
   CONDUCTORES
========================================================= */

$stmtConductores = $conn->query("
    SELECT
        id,
        rut,
        nombre,
        area,
        estado
    FROM conductores
    WHERE estado <> 'DESVINCULADO'
    ORDER BY nombre ASC
");


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


function fechaChile($fecha)
{
    if (!$fecha) {
        return '-';
    }

    return date(
        'd-m-Y',
        strtotime($fecha)
    );
}

?>
<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Historial <?=escapar($patente)?></title>

<link
    rel="icon"
    type="image/png"
    href="/CMPC/favicon.png"
>

<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

/* =========================================================
   VARIABLES
========================================================= */

:root{

    --primary:#2563eb;
    --primary-dark:#1e40af;

    --green:#16a34a;
    --green-dark:#15803d;

    --orange:#f59e0b;
    --orange-dark:#d97706;

    --red:#dc2626;
    --red-dark:#b91c1c;

    --gray:#64748b;

    --bg1:#f1f5f9;
    --bg2:#e0e7ff;

    --border:#e2e8f0;

}


/* =========================================================
   GENERAL
========================================================= */

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

    margin-bottom:35px;

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

    background:
    linear-gradient(
        90deg,
        rgba(15,23,42,.82),
        rgba(15,23,42,.18),
        transparent
    );

    border-radius:20px;

}

.banner-text{

    position:absolute;

    bottom:20px;

    left:30px;

    color:white;

}

.banner-text h1{

    margin:0;

    font-size:35px;

}

.banner-text p{

    margin:6px 0 0;

    opacity:.9;

}

.banner-datetime{

    position:absolute;

    right:25px;

    bottom:20px;

    background:
    rgba(255,255,255,.18);

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
   BOTONES
========================================================= */

.btn,
button{

    background:
    linear-gradient(
        135deg,
        var(--primary),
        var(--primary-dark)
    );

    color:white;

    border:0;

    padding:12px 18px;

    border-radius:12px;

    cursor:pointer;

    font-weight:600;

    transition:.2s;

}

.btn:hover,
button:hover{

    transform:translateY(-1px);

    box-shadow:
    0 5px 15px rgba(37,99,235,.25);

}

.btn-success{

    background:
    linear-gradient(
        135deg,
        var(--green),
        var(--green-dark)
    );

}

.btn-secondary{

    background:#64748b;

}

.btn-warning{

    background:
    linear-gradient(
        135deg,
        var(--orange),
        var(--orange-dark)
    );

}

.acciones{

    display:flex;

    gap:15px;

    margin-bottom:25px;

    flex-wrap:wrap;

}

.acciones a{

    text-decoration:none;

}


/* =========================================================
   CARDS EQUIPO
========================================================= */

.cards{

    display:grid;

    grid-template-columns:
        repeat(5,1fr);

    gap:20px;

    margin-bottom:30px;

}

.card{

    background:white;

    padding:25px;

    border-radius:18px;

    box-shadow:
    0 10px 30px rgba(0,0,0,.08);

}

.card h3{

    margin:0;

    font-size:14px;

    color:#64748b;

}

.numero{

    font-size:25px;

    font-weight:800;

    color:#2563eb;

    margin-top:8px;

    word-break:break-word;

}


/* =========================================================
   DASHBOARD
========================================================= */

.dashboard-grid{

    display:grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:25px;

    margin-bottom:25px;

}

.dashboard-card{

    background:white;

    border-radius:22px;

    padding:28px;

    box-shadow:
    0 10px 30px rgba(0,0,0,.08);

    position:relative;

    overflow:hidden;

}

.dashboard-card::before{

    content:"";

    position:absolute;

    top:0;
    left:0;
    right:0;

    height:5px;

    background:
    linear-gradient(
        90deg,
        var(--primary),
        #60a5fa
    );

}

.dashboard-card.eventos::before{

    background:
    linear-gradient(
        90deg,
        #64748b,
        #94a3b8
    );

}

.dashboard-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:15px;

    margin-bottom:25px;

}

.dashboard-title{

    display:flex;

    align-items:center;

    gap:12px;

}

.dashboard-icon{

    width:48px;

    height:48px;

    display:flex;

    align-items:center;

    justify-content:center;

    border-radius:14px;

    background:#eff6ff;

    font-size:24px;

}

.eventos .dashboard-icon{

    background:#f1f5f9;

}

.dashboard-title h2{

    margin:0;

    font-size:21px;

}

.dashboard-title span{

    display:block;

    color:#64748b;

    font-size:13px;

    margin-top:4px;

}

.dashboard-stats{

    display:grid;

    grid-template-columns:
        repeat(4,1fr);

    gap:12px;

    margin-bottom:22px;

}

.stat{

    background:#f8fafc;

    border:1px solid var(--border);

    padding:15px;

    border-radius:14px;

}

.stat small{

    display:block;

    color:#64748b;

    font-size:11px;

    font-weight:700;

    text-transform:uppercase;

    margin-bottom:6px;

}

.stat strong{

    font-size:24px;

    font-weight:800;

    color:#0f172a;

}

.stat.blue strong{

    color:#2563eb;

}

.stat.yellow strong{

    color:#d97706;

}

.stat.green strong{

    color:#15803d;

}

.stat.red strong{

    color:#dc2626;

}

.dashboard-footer{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:15px;

    flex-wrap:wrap;

    border-top:1px solid #e2e8f0;

    padding-top:18px;

}

.ultimo{

    color:#64748b;

    font-size:13px;

}

.ultimo strong{

    color:#0f172a;

}

.dashboard-button{

    text-decoration:none;

}

.dashboard-button button{

    min-width:210px;

}


/* =========================================================
   MODAL
========================================================= */

.modal-form{

    display:none;

    position:fixed;

    inset:0;

    background:rgba(15,23,42,.65);

    backdrop-filter:blur(5px);

    align-items:center;

    justify-content:center;

    z-index:9999;

}

.modal-form.active{

    display:flex;

}

.modal-box{

    width:800px;

    max-width:95%;

    max-height:90vh;

    overflow-y:auto;

    background:white;

    border-radius:22px;

    padding:30px;

    box-shadow:
    0 25px 60px rgba(0,0,0,.25);

    animation:subir .25s ease;

}

.modal-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:25px;

}

.modal-header h2{

    margin:0;

    color:#2563eb;

}

.cerrar{

    font-size:34px;

    cursor:pointer;

    font-weight:bold;

    color:#64748b;

    transition:.2s;

}

.cerrar:hover{

    color:#dc2626;

    transform:rotate(90deg);

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

    margin-bottom:5px;

}

.form-group.full{

    grid-column:1 / -1;

}

label{

    font-weight:700;

    display:block;

    margin-bottom:7px;

    color:#334155;

}

input,
textarea,
select{

    width:100%;

    padding:12px 14px;

    border-radius:12px;

    border:1px solid #cbd5e1;

    background:white;

    color:#0f172a;

    font-size:14px;

    outline:none;

}

input:focus,
textarea:focus,
select:focus{

    border-color:#2563eb;

    box-shadow:
    0 0 0 3px rgba(37,99,235,.1);

}

textarea{

    resize:vertical;

    min-height:100px;

}

.maestros-box{

    background:#f8fafc;

    border:1px solid #e2e8f0;

    padding:18px;

    border-radius:14px;

}

.maestro-row{

    display:flex;

    align-items:center;

    gap:10px;

    margin-bottom:10px;

}

.maestro-row:last-child{

    margin-bottom:0;

}

.maestro-row input{

    width:auto;
    flex:1;

}

.maestro-row label{

    margin:0;

    font-weight:500;

}


/* =========================================================
   FOOTER
========================================================= */

footer{

    margin-top:50px;

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
   ANIMACION
========================================================= */

.swal-confirm{
    margin-left:8px;
    padding:12px 18px;
    border-radius:12px;
    border:0;
    color:white;
    cursor:pointer;
    font-weight:700;
    background:linear-gradient(135deg,#2563eb,#1e40af);
}

.swal-cancel{
    padding:12px 18px;
    border-radius:12px;
    border:0;
    color:white;
    cursor:pointer;
    font-weight:700;
    background:#64748b;
}

.swal-confirm:hover,
.swal-cancel:hover{
    transform:translateY(-1px);
}

@keyframes subir{

    from{

        opacity:0;

        transform:translateY(30px);

    }

    to{

        opacity:1;

        transform:translateY(0);

    }

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:1100px){

    .cards{

        grid-template-columns:
            repeat(3,1fr);

    }

    .dashboard-stats{

        grid-template-columns:
            repeat(2,1fr);

    }

}

@media(max-width:800px){

    .dashboard-grid{

        grid-template-columns:1fr;

    }

}

@media(max-width:700px){

    body{

        padding:15px;

    }

    .cards{

        grid-template-columns:1fr;

    }

    .dashboard-stats{

        grid-template-columns:1fr 1fr;

    }

    .form-grid{

        grid-template-columns:1fr;

    }

    .form-group.full{

        grid-column:auto;

    }

    .banner-text h1{

        font-size:24px;

    }

    .banner-datetime{

        display:none;

    }

    .dashboard-footer{

        align-items:stretch;

        flex-direction:column;

    }

    .dashboard-button button{

        width:100%;

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
        alt="Gestión Equipos CMPC"
    >

    <div class="banner-overlay"></div>

    <div class="banner-text">

        <h1>
            Gestión Equipos CMPC
        </h1>

        <p>
            Historial y trazabilidad operacional · <?=escapar($patente)?>
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

    <a href="index.php">

        <button
            type="button"
            class="btn-secondary"
        >
            ← Volver
        </button>

    </a>


    <button
        type="button"
        class="btn btn-success"
        onclick="abrirModalOT()"
    >
        🔧 Nueva Orden de Trabajo
    </button>


    <button
        type="button"
        class="btn"
        onclick="abrirModalEvento()"
    >
        📝 Registrar Evento
    </button>

</div>


<!-- =======================================================
     DATOS EQUIPO
======================================================= -->

<div class="cards">

    <div class="card">

        <h3>Patente</h3>

        <div class="numero">
            <?=escapar($patente)?>
        </div>

    </div>


    <div class="card">

        <h3>Tipo</h3>

        <div class="numero">
            <?=escapar($equipo['tipo'] ?? '-')?>
        </div>

    </div>


    <div class="card">

        <h3>Marca</h3>

        <div class="numero">
            <?=escapar($equipo['marca'] ?? '-')?>
        </div>

    </div>


    <div class="card">

        <h3>Configuración</h3>

        <div class="numero">
            <?=escapar($equipo['configuracion'] ?? '-')?>
        </div>

    </div>


    <div class="card">

        <h3>Sub configuración</h3>

        <div class="numero">
            <?=escapar($equipo['sub_configuracion'] ?? '-')?>
        </div>

    </div>

</div>


<!-- =======================================================
     DASHBOARDS
======================================================= -->

<div class="dashboard-grid">


    <!-- ===================================================
         OT
    ==================================================== -->

    <div class="dashboard-card">

        <div class="dashboard-header">

            <div class="dashboard-title">

                <div class="dashboard-icon">
                    🔧
                </div>

                <div>

                    <h2>
                        Órdenes de Trabajo
                    </h2>

                    <span>
                        Resumen de mantenimiento del equipo
                    </span>

                </div>

            </div>

        </div>


        <div class="dashboard-stats">

            <div class="stat blue">

                <small>
                    Total OT
                </small>

                <strong>
                    <?=$totalOT?>
                </strong>

            </div>


            <div class="stat blue">

                <small>
                    Abiertas
                </small>

                <strong>
                    <?=$otAbiertas?>
                </strong>

            </div>


            <div class="stat yellow">

                <small>
                    En proceso
                </small>

                <strong>
                    <?=$otProceso?>
                </strong>

            </div>


            <div class="stat green">

                <small>
                    Cerradas
                </small>

                <strong>
                    <?=$otCerradas?>
                </strong>

            </div>

        </div>


        <div class="dashboard-footer">

            <div class="ultimo">

                Última OT:

                <strong>
                    <?=fechaChile($ultimaOT)?>
                </strong>

            </div>


            <a
                href="historial_ot.php?patente=<?=urlencode($patente)?>"
                class="dashboard-button"
            >

                <button
                    type="button"
                    class="btn"
                >
                    🔎 Ver historial OT →
                </button>

            </a>

        </div>

    </div>



    <!-- ===================================================
         EVENTOS
    ==================================================== -->

    <div class="dashboard-card eventos">

        <div class="dashboard-header">

            <div class="dashboard-title">

                <div class="dashboard-icon">
                    📝
                </div>

                <div>

                    <h2>
                        Historial de Eventos
                    </h2>

                    <span>
                        Registros y trazabilidad del equipo
                    </span>

                </div>

            </div>

        </div>


        <div class="dashboard-stats">

            <div class="stat blue">

                <small>
                    Eventos registrados
                </small>

                <strong>
                    <?=$totalEventos?>
                </strong>

            </div>


            

        </div>


        <div class="dashboard-footer">

            <div class="ultimo">

                Último evento

                <strong>
                    <?=fechaChile($ultimoEvento)?>
                </strong>

            </div>


            <a
                href="historial_eventos.php?patente=<?=urlencode($patente)?>"
                class="dashboard-button"
            >

                <button
                    type="button"
                    class="btn"
                >
                    🔎 Ver historial de eventos →
                </button>

            </a>

        </div>

    </div>

</div>


<!-- =======================================================
     MODAL NUEVA OT
======================================================= -->

<div
    class="modal-form"
    id="modalOT"
>

    <div class="modal-box">

        <div class="modal-header">

            <h2>
                🔧 Nueva Orden de Trabajo
            </h2>

            <span
                class="cerrar"
                onclick="cerrarModalOT()"
            >
                &times;
            </span>

        </div>


        <form
            action="guardar_ot.php"
            method="POST"
            enctype="multipart/form-data"
            id="formNuevaOT"
        >

            <input
                type="hidden"
                name="patente"
                value="<?=escapar($patente)?>"
            >


            <div
                style="
                    background:#eff6ff;
                    border:1px solid #bfdbfe;
                    padding:15px;
                    border-radius:14px;
                    margin-bottom:20px;
                "
            >

                <strong style="color:#1e40af;">

                    🚛 Equipo:
                    <?=escapar($patente)?>

                </strong>

                <div
                    style="
                        color:#64748b;
                        font-size:13px;
                        margin-top:5px;
                    "
                >

                    <?=escapar($equipo['tipo'] ?? '')?>
                    ·
                    <?=escapar($equipo['marca'] ?? '')?>
                    ·
                    <?=escapar($equipo['modelo'] ?? '')?>

                </div>

            </div>


            <div
                class="form-group full"
                style="
                    margin-bottom:20px;
                    background:#f0fdf4;
                    border:1px solid #bbf7d0;
                    padding:18px;
                    border-radius:14px;
                "
            >
                <label style="color:#166534;">
                    📎 Documento / SCAN de la OT <span style="color:#dc2626;">*</span>
                </label>

                <input
                    type="file"
                    name="documento_ot"
                    id="documento_ot"
                    accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                    required
                    style="background:white;"
                >

                <div
                    style="
                        margin-top:8px;
                        color:#64748b;
                        font-size:12px;
                    "
                >
                    Este es el único antecedente obligatorio para crear la OT.
                    Formatos permitidos: PDF, JPG, JPEG o PNG · Máximo 10 MB.
                </div>
            </div>

            <div class="form-grid">


                <div class="form-group">

                    <label>
                        Número OT
                    </label>

                    <input
                        type="text"
                        value="<?=str_pad(
                            $siguienteOT,
                            4,
                            '0',
                            STR_PAD_LEFT
                        )?>"
                        readonly
                    >

                    <input
                        type="hidden"
                        name="numero_ot"
                        value="<?=$siguienteOT?>"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Fecha de ingreso a taller
                    </label>

                    <input
                        type="date"
                        name="fecha_ingreso"
                        value="<?=date('Y-m-d')?>"
                        readonly
                        title="La fecha de ingreso se registra automáticamente"
                    >

                </div>


                <!-- =====================================================
     CONDUCTOR
===================================================== -->

<div class="form-group full">

    <label>
        👤 Conductor del equipo
    </label>

    <select
        name="conductor_id"
        id="conductor_id"
    >

        <?php if (!empty($equipo['conductor_actual_id'])) { ?>

            <!-- CONDUCTOR ACTUAL DEL EQUIPO -->

            <option
                value="<?=escapar($equipo['conductor_actual_id'])?>"
                selected
            >

                <?=escapar(
                    $equipo['conductor_actual_nombre']
                )?>

                —
                <?=escapar(
                    $equipo['conductor_actual_rut']
                )?>

                <?php if (!empty($equipo['conductor_actual_area'])) { ?>

                    · <?=escapar(
                        $equipo['conductor_actual_area']
                    )?>

                <?php } ?>

                · Conductor actual

            </option>

        <?php } else { ?>

            <!-- EQUIPO SIN CONDUCTOR -->

            <option value="">
                Seleccione conductor...
            </option>

        <?php } ?>


        <?php

        $stmtConductores = $conn->query("
            SELECT
                id,
                rut,
                nombre,
                area,
                estado
            FROM conductores
            WHERE estado <> 'DESVINCULADO'
            ORDER BY nombre ASC
        ");

        while (
            $conductor =
            $stmtConductores->fetch_assoc()
        ) {

            /*
             * Si es el conductor actual
             * no lo mostramos nuevamente.
             */

            if (
                !empty($equipo['conductor_actual_id']) &&
                $conductor['id'] == $equipo['conductor_actual_id']
            ) {
                continue;
            }

        ?>

            <option
                value="<?=escapar($conductor['id'])?>"
            >

                <?=escapar(
                    $conductor['nombre']
                )?>

                —
                <?=escapar(
                    $conductor['rut']
                )?>

                <?php if (!empty($conductor['area'])) { ?>

                    · <?=escapar(
                        $conductor['area']
                    )?>

                <?php } ?>

            </option>

        <?php } ?>

    </select>

</div>


                <div class="form-group full">

                    <label>
                        📝 Motivo de ingreso
                    </label>

                    <textarea
                        name="motivo_ingreso"
                        placeholder="Opcional. Puede completarlo posteriormente desde el detalle de la OT."
                    ></textarea>

                </div>


                <div class="form-group">

                    <label>
                        📊 Odómetro
                    </label>

                    <input
                        type="number"
                        name="odometro"
                        min="0"
                        placeholder="Opcional"
                    >

                </div>


                <div class="form-group">

                    <label>
                        Estado inicial
                    </label>

                    <select name="estado">

                        <option value="ABIERTA">
                            🟢 ABIERTA
                        </option>

                        <option value="EN PROCESO">
                            🟡 EN PROCESO
                        </option>

                    </select>

                </div>


                <div class="form-group full">

                    <label>
                        👷 Maestros encargados
                    </label>

                    <div class="maestros-box">

                        <div class="maestro-row">

                            <input
                                type="text"
                                name="maestros[]"
                                placeholder="Nombre del maestro"
                            >

                        </div>

                        <div
                            id="contenedorMaestros"
                        ></div>

                        <button
                            type="button"
                            class="btn-secondary"
                            onclick="agregarMaestro()"
                        >
                            ➕ Agregar maestro
                        </button>

                    </div>

                </div>

            </div>


            <div
                style="
                    display:flex;
                    justify-content:flex-end;
                    gap:10px;
                    margin-top:25px;
                "
            >

                <button
                    type="button"
                    class="btn-secondary"
                    onclick="cerrarModalOT()"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="btn-success"
                >
                    🔧 Crear Orden de Trabajo
                </button>

            </div>

        </form>

    </div>

</div>


<!-- =======================================================
     MODAL EVENTO
======================================================= -->

<div
    class="modal-form"
    id="modalEvento"
>

    <div class="modal-box">

        <div class="modal-header">

            <h2>
                📝 Registrar Evento
            </h2>

            <span
                class="cerrar"
                onclick="cerrarModalEvento()"
            >
                &times;
            </span>

        </div>


        <form
            action="guardar_historial.php"
            method="POST"
            enctype="multipart/form-data"
        >

            <input
                type="hidden"
                name="patente"
                value="<?=escapar($patente)?>"
            >

            <input
                type="hidden"
                name="tipo"
                value="<?=escapar($equipo['tipo'] ?? '')?>"
            >

            <input
                type="hidden"
                name="configuracion"
                value="<?=escapar($equipo['configuracion'] ?? '')?>"
            >

            <input
                type="hidden"
                name="sub_configuracion"
                value="<?=escapar($equipo['sub_configuracion'] ?? '')?>"
            >


            <label>
                Detalle
            </label>

            <textarea
                name="detalle"
                rows="5"
                required
            ></textarea>


            <label>
                Fecha
            </label>

            <input
                type="date"
                name="fecha"
                value="<?=date('Y-m-d')?>"
                required
            >


            <label>
                Imagen respaldo
            </label>

            <input
                type="file"
                name="imagen"
            >


            <label>
                Usuario responsable
            </label>

            <input
                type="text"
                name="usuario"
                required
            >


            <div
                style="
                    display:flex;
                    justify-content:flex-end;
                    gap:10px;
                    margin-top:20px;
                "
            >

                <button
                    type="button"
                    class="btn-secondary"
                    onclick="cerrarModalEvento()"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="btn-success"
                >
                    Guardar Registro
                </button>

            </div>

        </form>

    </div>

</div>


<!-- =======================================================
     FOOTER
======================================================= -->

<footer>

    <strong>
        Transportes Mimbral LTDA
    </strong>

    <br>

    Gestión empresarial · © <?=date('Y')?>

</footer>


<script>

/* =========================================================
   RELOJ
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
   MODAL OT
========================================================= */

function abrirModalOT(){

    Swal.fire({
        icon: 'info',
        title: 'Nueva Orden de Trabajo',
        html: `
            <div style="text-align:left;font-size:14px;line-height:1.65;color:#475569;">
                <p style="margin-top:0;">Para crear esta OT <strong>no es necesario completar todos los datos ahora</strong>.</p>

                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:14px;margin:14px 0;">
                    <strong style="color:#1e40af;">📎 Lo más importante</strong><br>
                    Debes adjuntar el <strong>documento o SCAN de la OT</strong>.
                </div>

                <p style="margin-bottom:8px;">El sistema registrará automáticamente:</p>
                <ul style="margin-top:0;padding-left:22px;">
                    <li>Equipo y patente</li>
                    <li>Número de OT</li>
                    <li>Fecha de ingreso</li>
                    <li>Conductor actualmente asociado al equipo, si existe</li>
                </ul>

                <p style="margin-bottom:0;">El resto de la información puede completarse o corregirse posteriormente desde la OT.</p>
            </div>
        `,
        confirmButtonText: 'Continuar con la OT',
        cancelButtonText: 'Cancelar',
        showCancelButton: true,
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn swal-confirm',
            cancelButton: 'btn-secondary swal-cancel'
        }
    }).then((result) => {

        if (result.isConfirmed) {
            document
                .getElementById("modalOT")
                .classList.add("active");
        }

    });

}

function cerrarModalOT(){

    document
        .getElementById("modalOT")
        .classList.remove("active");

}


/* =========================================================
   MODAL EVENTO
========================================================= */

function abrirModalEvento(){

    document
        .getElementById("modalEvento")
        .classList.add("active");

}

function cerrarModalEvento(){

    document
        .getElementById("modalEvento")
        .classList.remove("active");

}


/* =========================================================
   AGREGAR MAESTRO
========================================================= */

function agregarMaestro(){

    const contenedor =
        document.getElementById(
            "contenedorMaestros"
        );

    const div =
        document.createElement("div");

    div.className =
        "maestro-row";

    div.innerHTML = `

        <input
            type="text"
            name="maestros[]"
            placeholder="Nombre del maestro"
        >

        <button
            type="button"
            class="btn-secondary"
            onclick="this.parentElement.remove()"
            style="
                padding:8px 12px;
                background:#dc2626;
            "
        >
            ✕
        </button>

    `;

    contenedor.appendChild(div);

}


/* =========================================================
   VALIDAR DOCUMENTO OT
========================================================= */

document
    .getElementById("formNuevaOT")
    .addEventListener("submit", function(e){

        const archivo =
            document.getElementById("documento_ot").files[0];

        if (!archivo) {
            e.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Falta el documento de la OT',
                text: 'Debes adjuntar el documento o SCAN de la Orden de Trabajo para poder crearla.',
                confirmButtonText: 'Entendido'
            });

            return;
        }

        const extensiones = ['pdf','jpg','jpeg','png'];
        const extension = archivo.name.split('.').pop().toLowerCase();

        if (!extensiones.includes(extension)) {
            e.preventDefault();

            Swal.fire({
                icon: 'error',
                title: 'Formato no permitido',
                text: 'El documento debe ser PDF, JPG, JPEG o PNG.',
                confirmButtonText: 'Entendido'
            });

            return;
        }

        if (archivo.size > 10 * 1024 * 1024) {
            e.preventDefault();

            Swal.fire({
                icon: 'error',
                title: 'Archivo demasiado grande',
                text: 'El documento no puede superar los 10 MB.',
                confirmButtonText: 'Entendido'
            });
        }

    });


/* =========================================================
   CLICK FUERA
========================================================= */

window.addEventListener(
    "click",
    function(e){

        const modalOT =
            document.getElementById("modalOT");

        const modalEvento =
            document.getElementById("modalEvento");

        if(e.target === modalOT){
            cerrarModalOT();
        }

        if(e.target === modalEvento){
            cerrarModalEvento();
        }

    }
);


/* =========================================================
   ESCAPE
========================================================= */

document.addEventListener(
    "keydown",
    function(e){

        if(e.key === "Escape"){

            cerrarModalOT();
            cerrarModalEvento();

        }

    }
);

</script>

</body>
</html>
```