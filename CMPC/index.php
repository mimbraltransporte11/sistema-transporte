<?php
include "conexion.php";

/* =========================================================
   DATOS GENERALES
========================================================= */

$total = $conn->query("
    SELECT COUNT(*) AS total
    FROM equipos
")->fetch_assoc()['total'];

$totalTracto = $conn->query("
    SELECT COUNT(*) AS total
    FROM equipos
    WHERE tipo = 'TRACTOCAMION'
")->fetch_assoc()['total'];

$totalRampla = $conn->query("
    SELECT COUNT(*) AS total
    FROM equipos
    WHERE configuracion = 'RAMPLA'
")->fetch_assoc()['total'];

$totalCortina = $conn->query("
    SELECT COUNT(*) AS total
    FROM equipos
    WHERE configuracion = 'CORTINA'
")->fetch_assoc()['total'];


/* =========================================================
   LISTA EQUIPOS
========================================================= */

$equipos = $conn->query("
    SELECT 
        e.*,
        c.rut AS conductor_rut,
        c.nombre AS conductor_nombre,
        c.area AS conductor_area,
        c.estado AS conductor_estado
    FROM equipos e
    LEFT JOIN conductores c 
        ON e.conductor_id = c.id
    ORDER BY e.patente ASC
");


/* =========================================================
   LISTAS FILTROS
========================================================= */

$tipos = $conn->query("
    SELECT DISTINCT tipo
    FROM equipos
    WHERE tipo <> ''
    ORDER BY tipo
");

$marcas = $conn->query("
    SELECT DISTINCT marca
    FROM equipos
    WHERE marca <> ''
    ORDER BY marca
");

$configs = $conn->query("
    SELECT DISTINCT configuracion
    FROM equipos
    WHERE configuracion <> ''
    ORDER BY configuracion
");

$subs = $conn->query("
    SELECT DISTINCT sub_configuracion
    FROM equipos
    WHERE sub_configuracion <> ''
    ORDER BY sub_configuracion
");
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Sistema Equipos CMPC</title>

<link rel="icon" href="favicon.png">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<style>

/* =========================================================
   VARIABLES
========================================================= */

:root{

    --primary:#2563eb;
    --primary-dark:#1e40af;
    --primary-light:#dbeafe;

    --success:#16a34a;
    --success-dark:#15803d;

    --danger:#dc2626;
    --danger-dark:#b91c1c;

    --warning:#f59e0b;
    --warning-dark:#d97706;

    --bg1:#f1f5f9;
    --bg2:#e0e7ff;

    --card:#ffffff;

    --text:#0f172a;
    --muted:#64748b;

    --border:#e2e8f0;

    --shadow:
        0 10px 30px rgba(15,23,42,.08);

}


/* =========================================================
   RESET
========================================================= */

*{

    box-sizing:border-box;

    font-family:
        Inter,
        Arial,
        sans-serif;

}


html{

    scroll-behavior:smooth;

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

    color:var(--text);

}


/* =========================================================
   CONTAINER
========================================================= */

.container{

    width:100%;

    max-width:1600px;

    margin:auto;

    padding-top:5px;

}


/* =========================================================
   BANNER
========================================================= */

.banner-wrap{

    position:relative;

    width:100%;

    max-width:1600px;

    margin:0 auto 30px auto;

    overflow:hidden;

}


.banner{

    display:block;

    width:100%;

    height:220px;

    object-fit:cover;

    border-radius:20px;

    box-shadow:
        0 15px 40px rgba(15,23,42,.15);

}


.banner-overlay{

    position:absolute;

    inset:0;

    background:
        linear-gradient(
            90deg,
            rgba(15,23,42,.80),
            rgba(15,23,42,.15),
            transparent
        );

    border-radius:20px;

}


.banner-text{

    position:absolute;

    left:30px;

    bottom:25px;

    color:white;

}


.banner-text h1{

    margin:0 0 7px 0;

    font-size:34px;

    font-weight:800;

}


.banner-text p{

    margin:0;

    font-size:14px;

    opacity:.9;

}


.banner-datetime{

    position:absolute;

    right:25px;

    bottom:20px;

    background:
        rgba(255,255,255,.18);

    backdrop-filter:blur(12px);

    -webkit-backdrop-filter:blur(12px);

    padding:12px 18px;

    border-radius:16px;

    color:white;

    text-align:right;

    border:
        1px solid rgba(255,255,255,.30);

}


#banner-date{

    font-size:12px;

    text-transform:capitalize;

    opacity:.9;

}


#banner-time{

    font-size:26px;

    font-weight:800;

    line-height:1.2;

}


/* =========================================================
   BOTONES
========================================================= */

button,
.btn{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    gap:6px;

    background:
        linear-gradient(
            135deg,
            var(--primary),
            var(--primary-dark)
        );

    color:white;

    border:0;

    padding:11px 17px;

    border-radius:11px;

    cursor:pointer;

    font-weight:600;

    font-size:13px;

    transition:
        transform .15s ease,
        box-shadow .15s ease,
        opacity .15s ease;

}


button:hover,
.btn:hover{

    transform:translateY(-1px);

    box-shadow:
        0 6px 15px rgba(37,99,235,.25);

}


button:active,
.btn:active{

    transform:translateY(0);

}


.btn-success{

    background:
        linear-gradient(
            135deg,
            var(--success),
            var(--success-dark)
        );

}


.btn-success:hover{

    box-shadow:
        0 6px 15px rgba(22,163,74,.25);

}


.btn-danger{

    background:
        linear-gradient(
            135deg,
            var(--danger),
            var(--danger-dark)
        );

}


.btn-danger:hover{

    box-shadow:
        0 6px 15px rgba(220,38,38,.25);

}


.btn-warning{

    background:
        linear-gradient(
            135deg,
            var(--warning),
            var(--warning-dark)
        );

}


.btn-secondary{

    background:#64748b;

}


.btn-secondary:hover{

    box-shadow:
        0 6px 15px rgba(100,116,139,.25);

}


/* =========================================================
   ACCIONES
========================================================= */

.acciones{

    max-width:1600px;

    margin:0 auto 20px auto;

    display:flex;

    gap:12px;

    flex-wrap:wrap;

}


/* =========================================================
   CARDS
========================================================= */

.cards{

    display:grid;

    grid-template-columns:
        repeat(4,1fr);

    gap:20px;

    margin-bottom:25px;

}


.card{

    background:
        rgba(255,255,255,.96);

    padding:22px;

    border-radius:18px;

    box-shadow:var(--shadow);

    border:
        1px solid rgba(255,255,255,.8);

}


.cards .card{

    position:relative;

    overflow:hidden;

}


.cards .card::after{

    content:"";

    position:absolute;

    width:80px;

    height:80px;

    right:-25px;

    bottom:-25px;

    border-radius:50%;

    background:
        rgba(37,99,235,.08);

}


.card h3{

    margin:0 0 10px 0;

    color:var(--muted);

    font-size:14px;

    font-weight:600;

}


.numero{

    font-size:38px;

    line-height:1;

    font-weight:800;

    color:var(--primary);

}


/* =========================================================
   FILTROS
========================================================= */

.filtros{

    background:white;

    padding:18px;

    border-radius:18px;

    display:flex;

    align-items:center;

    gap:12px;

    flex-wrap:wrap;

    margin-bottom:15px;

    box-shadow:var(--shadow);

}


.filtros select{

    min-width:210px;

}


input,
select{

    width:auto;

    padding:11px 13px;

    border-radius:11px;

    border:
        1px solid #cbd5e1;

    background:white;

    color:var(--text);

    outline:none;

    font-size:13px;

    transition:
        border-color .2s,
        box-shadow .2s;

}


input:focus,
select:focus{

    border-color:var(--primary);

    box-shadow:
        0 0 0 3px rgba(37,99,235,.10);

}


#buscar{

    display:block;

    width:100%;

    max-width:400px;

    margin-bottom:15px;

    background:white;

    box-shadow:
        0 4px 15px rgba(15,23,42,.05);

}


/* =========================================================
   TABLA
========================================================= */

.table-container{

    background:white;

    border-radius:18px;

    box-shadow:var(--shadow);

    overflow:auto;

}


#tabla{

    width:100%;

    border-collapse:collapse;

    min-width:1050px;

}


#tabla thead{

    background:
        linear-gradient(
            135deg,
            #eff6ff,
            #dbeafe
        );

}


#tabla th{

    padding:15px 14px;

    text-align:left;

    font-size:12px;

    font-weight:800;

    color:#1e3a8a;

    text-transform:uppercase;

    letter-spacing:.3px;

    border-bottom:
        1px solid #bfdbfe;

    white-space:nowrap;

}


#tabla td{

    padding:14px;

    font-size:13px;

    border-bottom:
        1px solid #eef2f7;

    color:#334155;

    vertical-align:middle;

}


#tabla tbody tr{

    cursor:pointer;

    transition:
        background .15s ease;

}


#tabla tbody tr:hover{

    background:#f8fafc;

}


#tabla tbody tr:last-child td{

    border-bottom:0;

}


#tabla td:first-child{

    font-weight:800;

    color:#1e40af;

}


#tabla td:last-child{

    white-space:nowrap;

}


#tabla td button{

    margin-right:5px;

}


/* =========================================================
   BADGES
========================================================= */

.badge{

    display:inline-flex;

    align-items:center;

    padding:5px 9px;

    border-radius:8px;

    font-size:11px;

    font-weight:700;

    background:#eff6ff;

    color:#1d4ed8;

}


/* =========================================================
   MODALES
========================================================= */

.modal{

    position:fixed;

    inset:0;

    z-index:9999;

    display:none;

    align-items:center;

    justify-content:center;

    padding:25px;

    background:
        rgba(15,23,42,.65);

    backdrop-filter:blur(5px);

    -webkit-backdrop-filter:blur(5px);

}


.modal.active{

    display:flex;

}


.modal-box{

    width:100%;

    max-width:900px;

    max-height:92vh;

    background:white;

    border-radius:20px;

    box-shadow:
        0 25px 70px rgba(15,23,42,.30);

    overflow:hidden;

    animation:
        aparecerModal .18s ease;

}


@keyframes aparecerModal{

    from{

        opacity:0;

        transform:translateY(10px) scale(.98);

    }

    to{

        opacity:1;

        transform:translateY(0) scale(1);

    }

}


.modal-header{

    display:flex;

    align-items:center;

    justify-content:space-between;

    padding:18px 22px;

    border-bottom:
        1px solid var(--border);

    background:#f8fafc;

}


.modal-header h3{

    margin:0;

    font-size:18px;

    color:#0f172a;

}


.modal-header small{

    display:block;

    margin-top:4px;

    color:var(--muted);

}


.modal-header > span{

    cursor:pointer;

    font-size:20px;

    color:#64748b;

    transition:.2s;

}


.modal-header > span:hover{

    color:var(--danger);

    transform:scale(1.1);

}


.modal-content{

    padding:22px;

    max-height:68vh;

    overflow-y:auto;

}


.modal-footer{

    display:flex;

    justify-content:flex-end;

    gap:10px;

    padding:15px 22px;

    border-top:
        1px solid var(--border);

    background:#f8fafc;

}


/* =========================================================
   FICHA EQUIPO
========================================================= */

.ficha-equipo{

    max-width:1000px;

}


.ficha-equipo .modal-content{

    max-height:70vh;

    overflow-y:auto;

}


.cerrar-modal{

    cursor:pointer;

    font-size:20px;

    color:#64748b;

    transition:.2s;

}


.cerrar-modal:hover{

    color:#dc2626;

    transform:scale(1.1);

}


.ficha-seccion{

    background:#f8fafc;

    border:
        1px solid #e2e8f0;

    border-radius:16px;

    padding:18px;

    margin-bottom:18px;

}


.ficha-titulo{

    font-size:15px;

    font-weight:800;

    color:#1e40af;

    margin-bottom:15px;

    padding-bottom:10px;

    border-bottom:
        1px solid #dbeafe;

}


.ficha-grid{

    display:grid;

    grid-template-columns:
        repeat(3,1fr);

    gap:12px;

}


.dato{

    background:white;

    border:
        1px solid #e2e8f0;

    border-radius:12px;

    padding:12px 14px;

    min-height:62px;

}


.dato span{

    display:block;

    font-size:10px;

    color:#64748b;

    text-transform:uppercase;

    font-weight:800;

    margin-bottom:5px;

}


.dato strong{

    display:block;

    font-size:14px;

    color:#0f172a;

    word-break:break-word;

}


.historial-equipo{

    background:#eff6ff;

    border:
        1px solid #bfdbfe;

    border-radius:16px;

    padding:20px;

    margin-top:10px;

}


.historial-equipo h3{

    margin:0 0 5px 0;

    color:#1e40af;

}


.historial-equipo p{

    margin:0 0 15px 0;

    color:#64748b;

    font-size:14px;

}


/* =========================================================
   FORMULARIO
========================================================= */

.grid-form{

    display:grid;

    grid-template-columns:
        repeat(3,1fr);

    gap:16px;

}


.grid-form > div{

    display:flex;

    flex-direction:column;

    gap:6px;

}


.grid-form label{

    font-size:12px;

    font-weight:700;

    color:#475569;

}


.grid-form input,
.grid-form select{

    width:100%;

}


.grid-form > div:last-child{

    grid-column:auto;

}

.form-seccion-titulo{

    grid-column:1 / -1;

    margin-top:10px;

    padding:12px 14px;

    border-radius:12px;

    background:#eff6ff;

    border:1px solid #bfdbfe;

    color:#1e40af;

    font-size:13px;

    font-weight:800;

}


/* =========================================================
   DASHBOARD
========================================================= */

.dashboard-grid{

    display:grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:20px;

}


.dashboard-card{

    background:white;

    border:
        1px solid var(--border);

    border-radius:16px;

    padding:18px;

}


.dashboard-card h3{

    margin:0 0 15px 0;

    color:#334155;

    font-size:14px;

}


.dashboard-card canvas{

    max-height:300px;

}


/* =========================================================
   FOOTER
========================================================= */

footer{
    margin-top:60px;
    padding:30px;
    border-radius:20px;
    background:linear-gradient(135deg,#1e3a8a,#2563eb);
    color:white;
    text-align:center;
    box-shadow:0 20px 40px rgba(37,99,235,.35);
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:1100px){

    .cards{

        grid-template-columns:
            repeat(2,1fr);

    }

    .ficha-grid{

        grid-template-columns:
            repeat(2,1fr);

    }

    .grid-form{

        grid-template-columns:
            repeat(2,1fr);

    }

}


@media(max-width:800px){

    body{

        padding:15px;

    }


    .banner{

        height:180px;

    }


    .banner-text h1{

        font-size:24px;

    }


    .banner-datetime{

        display:none;

    }


    .cards{

        grid-template-columns:1fr;

    }


    .ficha-grid{

        grid-template-columns:
            repeat(2,1fr);

    }


    .dashboard-grid{

        grid-template-columns:1fr;

    }


    .grid-form{

        grid-template-columns:1fr;

    }


    .modal{

        padding:10px;

    }


    .modal-content{

        max-height:72vh;

    }

}


@media(max-width:550px){

    .ficha-grid{

        grid-template-columns:1fr;

    }


    .filtros{

        align-items:stretch;

    }


    .filtros select,
    .filtros button{

        width:100%;

    }


    #buscar{

        max-width:none;

    }

}


/* =========================================================
   BUSCADOR DE CONDUCTORES
========================================================= */

.campo-conductor{
    position:relative;
}


.resultados-conductores{

    position:absolute;

    top:100%;

    left:0;

    right:0;

    background:#ffffff;

    border:1px solid #dbe2ea;

    border-radius:10px;

    margin-top:5px;

    box-shadow:
        0 10px 25px rgba(0,0,0,.12);

    max-height:260px;

    overflow-y:auto;

    z-index:9999;

    display:none;

}


.resultado-conductor{

    padding:12px 14px;

    cursor:pointer;

    border-bottom:1px solid #eef1f5;

    display:flex;

    flex-direction:column;

    gap:3px;

}


.resultado-conductor:last-child{
    border-bottom:none;
}


.resultado-conductor:hover{
    background:#f1f5f9;
}


.resultado-conductor strong{

    color:#1e293b;

    font-size:14px;

}


.resultado-conductor span{

    color:#64748b;

    font-size:13px;

}


.resultado-conductor small{

    color:#94a3b8;

    font-size:11px;

}


.conductor-seleccionado{

    margin-top:8px;

    padding:10px 12px;

    background:#f0fdf4;

    border:1px solid #bbf7d0;

    border-radius:9px;

    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:10px;

}


.conductor-seleccionado span{

    color:#166534;

    font-size:13px;

}


.conductor-seleccionado button{

    border:none;

    background:#fee2e2;

    color:#dc2626;

    width:28px;

    height:28px;

    border-radius:50%;

    cursor:pointer;

    font-weight:bold;

}


.conductor-seleccionado button:hover{

    background:#fecaca;

}


.sin-conductores{

    padding:15px;

    text-align:center;

    color:#64748b;

    font-size:13px;

}

</style>

</head>


<body>


<?php include '../shared/menu.php'; ?>

<!-- ======================================================
     BANNER
====================================================== -->

<div class="banner-wrap">

    <img
        src="banner1.jpg"
        class="banner"
        alt="Banner CMPC"
    >

    <div class="banner-overlay"></div>

    <div class="banner-text">

        <h1>
            Gestión Equipos CMPC
        </h1>

        <p>
            Control de activos y trazabilidad operacional
        </p>

    </div>

    <div class="banner-datetime">

        <div id="banner-date"></div>

        <div id="banner-time"></div>

    </div>

</div>


<div class="container">


<!-- ======================================================
     CARDS
====================================================== -->

<div class="cards">

    <div class="card">

        <h3>
            Total Equipos
        </h3>

        <div
            class="numero"
            id="cardTotal"
        >
            <?=$total?>
        </div>

    </div>


    <div class="card">

        <h3>
            Tractocamiones
        </h3>

        <div
            class="numero"
            id="cardTracto"
        >
            <?=$totalTracto?>
        </div>

    </div>


    <div class="card">

        <h3>
            Ramplas
        </h3>

        <div
            class="numero"
            id="cardRampla"
        >
            <?=$totalRampla?>
        </div>

    </div>


    <div class="card">

        <h3>
            Cortinas
        </h3>

        <div
            class="numero"
            id="cardCortina"
        >
            <?=$totalCortina?>
        </div>

    </div>

</div>


<!-- ======================================================
     FILTROS
====================================================== -->

<form
    class="filtros"
    onsubmit="return false;"
>

    <!-- TIPO -->
    <select id="tipo">

        <option value="">
             Todos los tipos
        </option>

        <?php while($t=$tipos->fetch_assoc()){ ?>

            <option value="<?=htmlspecialchars($t['tipo'])?>">

                <?=htmlspecialchars($t['tipo'])?>

            </option>

        <?php } ?>

    </select>


    <!-- MARCA -->
    <select id="marca">

        <option value="">
             Todas las marcas
        </option>

        <?php while($m=$marcas->fetch_assoc()){ ?>

            <option value="<?=htmlspecialchars($m['marca'])?>">

                <?=htmlspecialchars($m['marca'])?>

            </option>

        <?php } ?>

    </select>


    <!-- CONFIGURACION -->
    <select id="configuracion">

        <option value="">
            ⚙️ Todas las configuraciones
        </option>

    </select>


    <!-- SUB CONFIGURACION -->
    <select id="sub_configuracion">

        <option value="">
            🔧 Todas las sub configuraciones
        </option>

    </select>


    <select id="fechaDesde">

    <option value="">
         Año desde
    </option>

</select>


<select id="fechaHasta">

    <option value="">
         Año hasta
    </option>

</select>


    <!-- BOTONES -->
    <button
    type="button"
    class="btn-secondary"
    onclick="limpiarFiltros()"
>
    Limpiar
</button>


<button
    type="button"
    onclick="abrirNuevoEquipo()"
>
    ➕ Nuevo Equipo
</button>


<button
    type="button"
    onclick="abrirDashboard()"
>
    Dashboard
</button>


<button
    type="button"
    class="btn-success"
    onclick="exportarExcel()"
>
    📊 Exportar Excel
</button>

</form>


<!-- ======================================================
     BUSCADOR
====================================================== -->

<input
    type="text"
    id="buscar"
    placeholder="🔍 Buscar patente..."
    autocomplete="off"
>


<!-- ======================================================
     TABLA
====================================================== -->

<div class="table-container">

<table id="tabla">

<thead>

<tr>

    <th>Patente</th>

    <th>Tipo</th>

    <th>Marca</th>

    <th>Modelo</th>

    <th>Año</th>

    <th>Configuración</th>

    <th>Sub Configuración</th>

    <th>Acciones</th>

</tr>

</thead>


<tbody>

<?php while($e=$equipos->fetch_assoc()){ ?>

<?php

$equipoJson = htmlspecialchars(
    json_encode(
        $e,
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ),
    ENT_QUOTES,
    'UTF-8'
);

?>

<tr

    onclick='verEquipo(<?=$equipoJson?>)'

    data-patente="<?=strtoupper(htmlspecialchars($e['patente'] ?? ''))?>"

    data-tipo="<?=strtoupper(htmlspecialchars($e['tipo'] ?? ''))?>"

    data-marca="<?=strtoupper(htmlspecialchars($e['marca'] ?? ''))?>"

    data-config="<?=strtoupper(htmlspecialchars($e['configuracion'] ?? ''))?>"

    data-sub="<?=strtoupper(htmlspecialchars($e['sub_configuracion'] ?? ''))?>"

    data-anio="<?=htmlspecialchars($e['anio'] ?? '')?>"

    data-valor-permiso="<?=htmlspecialchars($e['valor_permiso'] ?? 0)?>"

    data-municipalidad="<?=htmlspecialchars($e['municipalidad'] ?? '')?>"

    data-valor-soap="<?=htmlspecialchars($e['valor_soap'] ?? 0)?>"

    data-aseguradora="<?=htmlspecialchars($e['aseguradora'] ?? '')?>"

    data-valor-revision="<?=htmlspecialchars($e['valor_revision_tecnica'] ?? 0)?>"

>


<td>
    <?=htmlspecialchars($e['patente'] ?? '')?>
</td>


<td>
    <?=htmlspecialchars($e['tipo'] ?? '')?>
</td>


<td>
    <?=htmlspecialchars($e['marca'] ?? '')?>
</td>


<td>
    <?=htmlspecialchars($e['modelo'] ?? '')?>
</td>


<td>
    <?=htmlspecialchars($e['anio'] ?? '')?>
</td>


<td>
    <?=htmlspecialchars($e['configuracion'] ?? '')?>
</td>


<td>
    <?=htmlspecialchars($e['sub_configuracion'] ?? '')?>
</td>


<td>

<button
    type="button"
    class="btn"
    onclick='event.stopPropagation(); editarEquipo(<?=$equipoJson?>);'
>
    ✏️ Editar
</button>


<button
    type="button"
    class="btn-danger"
    onclick='event.stopPropagation(); eliminarEquipo(
        <?=json_encode($e['patente'] ?? '')?>,
        <?=json_encode("eliminar_equipo.php?patente=".urlencode($e['patente'] ?? ''))?>
    );'
>
    🗑️ Eliminar
</button>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>


<!-- ======================================================
     MODAL FICHA EQUIPO
====================================================== -->

<div
    class="modal"
    id="modalEquipo"
>

<div class="modal-box ficha-equipo">


<div class="modal-header">

<div>

<h3>
     Ficha del Equipo
</h3>

<small id="m_subtitulo">
    Información completa del equipo
</small>

</div>


<span
    onclick="cerrarModal()"
    class="cerrar-modal"
>

    ✖

</span>

</div>


<div class="modal-content">


<!-- IDENTIFICACIÓN -->

<div class="ficha-seccion">

<div class="ficha-titulo">
     Identificación del equipo
</div>

<!-- CONDUCTOR -->

<div class="ficha-seccion">

    <div class="ficha-titulo">
        👤 Conductor asignado
    </div>

    <div class="ficha-grid">

        <div class="dato">

            <span>
                RUT
            </span>

            <strong id="m_conductor_rut">
                -
            </strong>

        </div>


        <div class="dato">

            <span>
                Nombre
            </span>

            <strong id="m_conductor_nombre">
                -
            </strong>

        </div>


        <div class="dato">

            <span>
                Área
            </span>

            <strong id="m_conductor_area">
                -
            </strong>

        </div>


        <div class="dato">

            <span>
                Estado
            </span>

            <strong id="m_conductor_estado">
                -
            </strong>

        </div>

    </div>

</div>


<div class="ficha-grid">


<div class="dato">

<span>
    Patente
</span>

<strong id="m_patente">
    -
</strong>

</div>


<div class="dato">

<span>
    Tipo
</span>

<strong id="m_tipo">
    -
</strong>

</div>


<div class="dato">

<span>
    Marca
</span>

<strong id="m_marca">
    -
</strong>

</div>


<div class="dato">

<span>
    Modelo
</span>

<strong id="m_modelo">
    -
</strong>

</div>


<div class="dato">

<span>
    Año
</span>

<strong id="m_anio">
    -
</strong>

</div>


<div class="dato">

<span>
    Propietario
</span>

<strong id="m_propietario">
    -
</strong>

</div>


</div>

</div>


<!-- CARACTERÍSTICAS -->

<div class="ficha-seccion">

<div class="ficha-titulo">
     Características técnicas
</div>


<div class="ficha-grid">


<div class="dato">

<span>
    N° Chasis
</span>

<strong id="m_chasis">
    -
</strong>

</div>


<div class="dato">

<span>
    Color
</span>

<strong id="m_color">
    -
</strong>

</div>


<div class="dato">

<span>
    HP
</span>

<strong id="m_hp">
    -
</strong>

</div>


<div class="dato">

<span>
    Configuración
</span>

<strong id="m_config">
    -
</strong>

</div>


<div class="dato">

<span>
    Sub Configuración
</span>

<strong id="m_sub">
    -
</strong>

</div>

<div class="dato" id="campoTipoEjeFicha">

    <span>
        Tipo de eje
    </span>

    <strong id="m_tipo_eje">
        -
    </strong>

</div>


</div>

</div>


<!-- ESTANQUES -->

<div class="ficha-seccion" id="seccionEstanques">

<div class="ficha-titulo">
     Estanques de petróleo
</div>

<div class="ficha-grid">

<div class="dato">

<span>
    Estanque 1
</span>

<strong id="m_estanque_1">
    -
</strong>

</div>


<div class="dato">

<span>
    Estanque 2
</span>

<strong id="m_estanque_2">
    -
</strong>

</div>


<div class="dato">

<span>
    Capacidad total
</span>

<strong id="m_estanque_total">
    -
</strong>

</div>

</div>

</div>

<!-- PERMISO -->

<div class="ficha-seccion">

<div class="ficha-titulo">
     Permiso de circulación
</div>


<div class="ficha-grid">


<div class="dato">

<span>
    Fecha permiso
</span>

<strong id="m_fecha_permiso">
    -
</strong>

</div>


<div class="dato">

<span>
    Valor permiso
</span>

<strong id="m_valor_permiso">
    -
</strong>

</div>


<div class="dato">

<span>
    Municipalidad
</span>

<strong id="m_municipalidad">
    -
</strong>

</div>


</div>

</div>


<!-- SOAP -->

<div class="ficha-seccion">

<div class="ficha-titulo">
     SOAP
</div>


<div class="ficha-grid">


<div class="dato">

<span>
    Fecha SOAP
</span>

<strong id="m_fecha_soap">
    -
</strong>

</div>


<div class="dato">

<span>
    Valor SOAP
</span>

<strong id="m_valor_soap">
    -
</strong>

</div>


<div class="dato">

<span>
    Aseguradora
</span>

<strong id="m_aseguradora">
    -
</strong>

</div>


</div>

</div>


<!-- REVISION -->

<div class="ficha-seccion">

<div class="ficha-titulo">
     Revisión técnica
</div>


<div class="ficha-grid">


<div class="dato">

<span>
    Fecha revisión técnica
</span>

<strong id="m_fecha_revision">
    -
</strong>

</div>


<div class="dato">

<span>
    Valor revisión técnica
</span>

<strong id="m_valor_revision">
    -
</strong>

</div>


</div>

</div>


<!-- HISTORIAL -->

<div class="historial-equipo">

<h3>
     Historial
</h3>


<p>
    Consulta todos los movimientos y registros asociados
    a este equipo.
</p>


<a
    id="linkHistorial"
    href="#"
>

<button
    type="button"
    class="btn-success"
>

    Ver historial completo

</button>

</a>

</div>


</div>


<div class="modal-footer">

<button
    type="button"
    onclick="cerrarModal()"
>

    Cerrar

</button>

</div>


</div>

</div>


<!-- ======================================================
     MODAL NUEVO / EDITAR
====================================================== -->

<div
    class="modal"
    id="modalNuevo"
>

<div
    class="modal-box"
    style="max-width:900px;"
>


<div class="modal-header">

<h3 id="tituloModalEquipo">
    ➕ Nuevo Equipo
</h3>


<span
    onclick="cerrarModal()"
    class="cerrar-modal"
>

    ✖

</span>

</div>


<form
    id="formEquipo"
    action="guardar_equipo.php"
    method="POST"
>


<input
    type="hidden"
    id="modoEquipo"
    name="modo"
    value="nuevo"
>


<input
    type="hidden"
    id="patenteOriginal"
    name="patente_original"
>


<div class="modal-content">


<div class="grid-form">


<div>

<label>
    Patente
</label>

<input
    type="text"
    id="patenteEquipo"
    name="patente"
    required
>

</div>


<div>

<label>
    Tipo
</label>

<select
    name="tipo"
    id="nuevoTipo"
    required
>

<option value="">
    Seleccione...
</option>

<option value="TRACTOCAMION">
    TRACTOCAMION
</option>

<option value="SEMIREMOLQUE">
    SEMIREMOLQUE
</option>

</select>

</div>


<div>

<label>
    Marca
</label>

<input
    type="text"
    id="marcaEquipo"
    name="marca"
>

</div>


<div>

<label>
    Modelo
</label>

<input
    type="text"
    id="modeloEquipo"
    name="modelo"
>

</div>


<div>

<label>
    Año
</label>

<input
    type="number"
    id="anioEquipo"
    name="anio"
>

</div>


<div>

<label>
    Configuración
</label>

<select
    name="configuracion"
    id="nuevoConfig"
>

<option value="">
    Seleccione....
</option>

</select>

</div>


<div>

<label>
    Sub Configuración
</label>

<select
    name="sub_configuracion"
    id="nuevoSub"
>

<option value="">
    Seleccione....
</option>

</select>

</div>

<div class="form-group" id="campoTipoEje">
    <label>Tipo de eje</label>

    <select name="tipo_eje" id="tipoEjeEquipo">
        <option value="">Seleccione tipo de eje</option>
        <option value="Motriz">Motriz</option>
        <option value="De levante">De levante</option>
    </select>
</div>

<div class="campo-conductor">

    <label>
        Conductor
    </label>

    <!-- ID DEL CONDUCTOR QUE SE GUARDARÁ -->
    <input
        type="hidden"
        name="conductor_id"
        id="conductorIdEquipo"
    >

    <!-- BUSCADOR -->
    <input
        type="text"
        id="buscarConductor"
        placeholder="🔎 Escriba nombre o RUT..."
        autocomplete="off"
    >

    <!-- RESULTADOS -->
    <div
        id="resultadosConductores"
        class="resultados-conductores"
    ></div>

    <!-- CONDUCTOR SELECCIONADO -->
    <div
        id="conductorSeleccionado"
        class="conductor-seleccionado"
        style="display:none;"
    >

        <span id="nombreConductorSeleccionado"></span>

        <button
            type="button"
            onclick="quitarConductor()"
        >
            ✕
        </button>

    </div>

</div>

<div>

<label>
    Propietario
</label>

<input
    type="text"
    id="propietarioEquipo"
    name="propietario"
>

</div>


<div>

<label>
    Color
</label>

<input
    type="text"
    id="colorEquipo"
    name="color"
>

</div>


<div>

<label>
    HP
</label>

<input
    type="text"
    id="hpEquipo"
    name="hp"
>

</div>


<div>

<label>
    N° Chasis
</label>

<input
    type="text"
    id="chasisEquipo"
    name="numero_chasis"
>

</div>

<div id="campoEstanque1">

<label>
     Estanque 1
</label>

<input
    type="number"
    id="estanqueEquipo1"
    name="capacidad_estanque_1"
    min="0"
    step="1"
    placeholder="Litros"
>

</div>


<div id="campoEstanque2">

<label>
     Estanque 2
</label>

<input
    type="number"
    id="estanqueEquipo2"
    name="capacidad_estanque_2"
    min="0"
    step="1"
    placeholder="Litros"
>

</div>

<!-- =====================================================
     PERMISO DE CIRCULACIÓN
===================================================== -->

<div class="form-seccion-titulo">

    Permiso de circulación

</div>


<div>

    <label>
        Fecha permiso
    </label>

    <input
        type="date"
        id="fechaPermisoEquipo"
        name="fecha_permiso"
    >

</div>


<div>

    <label>
        Valor permiso
    </label>

    <input
        type="number"
        id="valorPermisoEquipo"
        name="valor_permiso"
        min="0"
        step="1"
        placeholder="$"
    >

</div>


<div>

    <label>
        Municipalidad
    </label>

    <input
        type="text"
        id="municipalidadEquipo"
        name="municipalidad"
        placeholder="Municipalidad"
    >

</div>


<!-- =====================================================
     SOAP
===================================================== -->

<div class="form-seccion-titulo">

    SOAP

</div>


<div>

    <label>
        Fecha SOAP
    </label>

    <input
        type="date"
        id="fechaSoapEquipo"
        name="fecha_soap"
    >

</div>


<div>

    <label>
        Valor SOAP
    </label>

    <input
        type="number"
        id="valorSoapEquipo"
        name="valor_soap"
        min="0"
        step="1"
        placeholder="$"
    >

</div>


<div>

    <label>
        Aseguradora
    </label>

    <input
        type="text"
        id="aseguradoraEquipo"
        name="aseguradora"
        placeholder="Aseguradora"
    >

</div>


<!-- =====================================================
     REVISIÓN TÉCNICA
===================================================== -->

<div class="form-seccion-titulo">

    Revisión técnica

</div>


<div>

    <label>
        Fecha revisión técnica
    </label>

    <input
        type="date"
        id="fechaRevisionEquipo"
        name="fecha_revision_tecnica"
    >

</div>


<div>

    <label>
        Valor revisión técnica
    </label>

    <input
        type="number"
        id="valorRevisionEquipo"
        name="valor_revision_tecnica"
        min="0"
        step="1"
        placeholder="$"
    >

</div>


</div>

</div>


<div class="modal-footer">

<button
    type="button"
    class="btn-secondary"
    onclick="cerrarModal()"
>

    Cancelar

</button>


<button
    type="submit"
    id="btnGuardarEquipo"
>

    Guardar Equipo

</button>

</div>


</form>

</div>

</div>


<!-- ======================================================
     MODAL DASHBOARD
====================================================== -->

<div
    class="modal"
    id="modalDashboard"
>

<div
    class="modal-box"
    style="max-width:1100px;"
>


<div class="modal-header">

<h3>
     Dashboard CMPC
</h3>


<span
    onclick="cerrarModal()"
    class="cerrar-modal"
>

    ✖

</span>

</div>


<div class="modal-content">


<div class="dashboard-grid">


<!-- EQUIPOS POR TIPO -->

<div class="dashboard-card">

<h3>
     Equipos por Tipo
</h3>

<canvas id="graficoTipo"></canvas>

</div>


<!-- CONFIGURACION -->

<div class="dashboard-card">

<h3>
     Configuración
</h3>

<canvas id="graficoConfig"></canvas>

</div>


<!-- SUB CONFIGURACION -->

<div class="dashboard-card">

<h3>
     Sub Configuración
</h3>

<canvas id="graficoSub"></canvas>

</div>


<!-- MARCAS -->

<div class="dashboard-card">

<h3>
     Marcas
</h3>

<canvas id="graficoMarca"></canvas>

</div>


<!-- AÑO -->

<div class="dashboard-card">

<h3>
     Año Fabricación
</h3>

<canvas id="graficoAnio"></canvas>

</div>


<!-- =====================================================
     GASTOS GENERALES
===================================================== -->

<div class="dashboard-card">

<h3>
     Gastos Generales
</h3>

<canvas id="graficoGastos"></canvas>

</div>


<!-- =====================================================
     PERMISOS POR MUNICIPALIDAD
===================================================== -->

<div class="dashboard-card">

<h3>
     Permisos por Municipalidad
</h3>

<canvas id="graficoMunicipalidad"></canvas>

</div>


<!-- =====================================================
     SOAP POR ASEGURADORA
===================================================== -->

<div class="dashboard-card">

<h3>
     SOAP por Aseguradora
</h3>

<canvas id="graficoAseguradora"></canvas>

</div>


<!-- =====================================================
     REVISION TECNICA
===================================================== -->

<div class="dashboard-card">

<h3>
     Gastos Revisión Técnica
</h3>

<canvas id="graficoRevision"></canvas>

</div>


</div>

</div>

</div>

</div>


<!-- ======================================================
     FOOTER
====================================================== -->

<footer>
    <strong>Transportes Mimbral LTDA</strong><br>
    Gestión empresarial · © <?= date('Y') ?>
</footer>


</div>


<script>

/* =========================================================
   ELEMENTOS
========================================================= */

const buscar =
    document.getElementById("buscar");

const tipo =
    document.getElementById("tipo");

const marca =
    document.getElementById("marca");

    const fechaDesde =
    document.getElementById("fechaDesde");

const fechaHasta =
    document.getElementById("fechaHasta");
    

const config =
    document.getElementById("configuracion");

const sub =
    document.getElementById("sub_configuracion");


const nuevoTipo =
    document.getElementById("nuevoTipo");

const nuevoConfig =
    document.getElementById("nuevoConfig");

const nuevoSub =
    document.getElementById("nuevoSub");


const modalEquipo =
    document.getElementById("modalEquipo");

const modalNuevo =
    document.getElementById("modalNuevo");

const modalDashboard =
    document.getElementById("modalDashboard");


const cardTotal =
    document.getElementById("cardTotal");

const cardTracto =
    document.getElementById("cardTracto");

const cardRampla =
    document.getElementById("cardRampla");

const cardCortina =
    document.getElementById("cardCortina");

    const estanque1 =
    document.getElementById("estanqueEquipo1");

const estanque2 =
    document.getElementById("estanqueEquipo2");

const campoEstanque1 =
    document.getElementById("campoEstanque1");

const campoEstanque2 =
    document.getElementById("campoEstanque2");

const seccionEstanques =
    document.getElementById("seccionEstanques");

    const tipoEje =
    document.getElementById("tipoEjeEquipo");

const campoTipoEje =
    document.getElementById("campoTipoEje");

/* =========================================================
   CONDUCTOR
========================================================= */

const buscarConductor =
    document.getElementById("buscarConductor");

const resultadosConductores =
    document.getElementById("resultadosConductores");

const conductorIdEquipo =
    document.getElementById("conductorIdEquipo");

const conductorSeleccionado =
    document.getElementById("conductorSeleccionado");

const nombreConductorSeleccionado =
    document.getElementById("nombreConductorSeleccionado");


let timerConductor;    



/* =========================================================
   BUSCADOR DE CONDUCTORES
========================================================= */

buscarConductor.addEventListener(
    "input",
    function(){

        const texto =
            this.value.trim();

        clearTimeout(
            timerConductor
        );

        resultadosConductores.innerHTML = "";

        if(texto.length < 2){

            resultadosConductores.style.display =
                "none";

            return;

        }

        timerConductor =
            setTimeout(
                function(){

                    buscarConductores(texto);

                },
                250
            );

    }
);


/* =========================================================
   BUSCAR CONDUCTORES EN LA BASE DE DATOS
========================================================= */

function buscarConductores(texto){

    fetch(
        "buscar_conductores.php?buscar=" +
        encodeURIComponent(texto)
    )

    .then(
        response => response.json()
    )

    .then(
        function(conductores){

            resultadosConductores.innerHTML = "";


            if(
                !conductores ||
                conductores.length === 0
            ){

                resultadosConductores.innerHTML =
                    `
                    <div class="sin-conductores">
                        No se encontraron conductores
                    </div>
                    `;

                resultadosConductores.style.display =
                    "block";

                return;

            }


            conductores.forEach(
                function(conductor){

                    const item =
                        document.createElement(
                            "div"
                        );


                    item.className =
                        "resultado-conductor";


                    item.innerHTML =
                        `
                        <strong>
                            ${conductor.nombre}
                        </strong>

                        <span>
                            ${conductor.rut}
                        </span>

                        <small>
                            ${conductor.area || ""}
                        </small>
                        `;


                    item.addEventListener(
                        "click",
                        function(){

                            seleccionarConductor(
                                conductor
                            );

                        }
                    );


                    resultadosConductores.appendChild(
                        item
                    );

                }
            );


            resultadosConductores.style.display =
                "block";

        }
    )

    .catch(
        function(error){

            console.error(
                "Error buscando conductores:",
                error
            );

        }
    );

}


/* =========================================================
   SELECCIONAR CONDUCTOR
========================================================= */

function seleccionarConductor(conductor){

    conductorIdEquipo.value =
        conductor.id;


    buscarConductor.value =
        conductor.nombre;


    nombreConductorSeleccionado.innerHTML =
        `
        👤 <strong>
            ${conductor.nombre}
        </strong>

        <span>
            ${conductor.rut}
        </span>
        `;


    conductorSeleccionado.style.display =
        "flex";


    resultadosConductores.innerHTML = "";

    resultadosConductores.style.display =
        "none";

}


/* =========================================================
   QUITAR CONDUCTOR
========================================================= */

function quitarConductor(){

    conductorIdEquipo.value = "";

    buscarConductor.value = "";

    nombreConductorSeleccionado.innerHTML = "";

    conductorSeleccionado.style.display =
        "none";

    resultadosConductores.innerHTML = "";

    resultadosConductores.style.display =
        "none";

}

/* =========================================================
   RELOJ
========================================================= */

function actualizarHora(){

    const now = new Date();


    document.getElementById(
        "banner-date"
    ).innerText = now.toLocaleDateString(
        "es-CL",
        {
            weekday:"long",
            day:"2-digit",
            month:"long",
            year:"numeric"
        }
    );


    document.getElementById(
        "banner-time"
    ).innerText = now.toLocaleTimeString(
        "es-CL",
        {
            hour:"2-digit",
            minute:"2-digit",
            second:"2-digit"
        }
    );

}


actualizarHora();

setInterval(
    actualizarHora,
    1000
);





function actualizarEstanques(){

    const esTractocamion =
        nuevoTipo.value === "TRACTOCAMION";


    if(esTractocamion){

        /* ESTANQUES */
        campoEstanque1.style.display = "";
        campoEstanque2.style.display = "";

        /* TIPO DE EJE */
        campoTipoEje.style.display = "";

    }
    else{

        /* ESTANQUES */
        campoEstanque1.style.display = "none";
        campoEstanque2.style.display = "none";

        estanque1.value = "";
        estanque2.value = "";

        /* TIPO DE EJE */
        campoTipoEje.style.display = "none";

        tipoEjeEquipo.value = "";

    }

}


function actualizarTipoEje(){

    const esTractocamion =
        nuevoTipo.value === "TRACTOCAMION";


    if(esTractocamion){

        campoTipoEje.style.display = "";

    }
    else{

        campoTipoEje.style.display = "none";

        tipoEje.value = "";

    }

}

/* =========================================================
   MODAL FICHA
========================================================= */

function formatearFecha(fecha){

    if(!fecha){
        return "-";
    }

    const partes = fecha.split("-");

    if(partes.length !== 3){
        return fecha;
    }

    return partes[2] + "-" + partes[1] + "-" + partes[0];
}


function verEquipo(e){

document.getElementById(
    "m_patente"
).innerText =
    e.patente || "-";

    document.getElementById(
    "m_conductor_rut"
).innerText =
    e.conductor_rut || "Sin asignar";


document.getElementById(
    "m_conductor_nombre"
).innerText =
    e.conductor_nombre || "Sin conductor asignado";


document.getElementById(
    "m_conductor_area"
).innerText =
    e.conductor_area || "-";


document.getElementById(
    "m_conductor_estado"
).innerText =
    e.conductor_estado || "-";

const estanqueUno =
    Number(e.capacidad_estanque_1) || 0;

const estanqueDos =
    Number(e.capacidad_estanque_2) || 0;

const capacidadTotal =
    estanqueUno + estanqueDos;

if(e.tipo === "TRACTOCAMION"){

    /* ============================
       ESTANQUES
    ============================ */

    document.getElementById(
        "seccionEstanques"
    ).style.display = "";

    document.getElementById(
        "m_estanque_1"
    ).innerText =
        estanqueUno > 0
            ? estanqueUno + " L"
            : "No informado";

    document.getElementById(
        "m_estanque_2"
    ).innerText =
        estanqueDos > 0
            ? estanqueDos + " L"
            : "No informado";

    document.getElementById(
        "m_estanque_total"
    ).innerText =
        capacidadTotal > 0
            ? capacidadTotal + " L"
            : "No informado";


    /* ============================
       TIPO DE EJE
    ============================ */

    document.getElementById(
        "campoTipoEjeFicha"
    ).style.display = "";

    document.getElementById(
        "m_tipo_eje"
    ).innerText =
        e.tipo_eje || "-";

}
else{

    /* ============================
       OCULTAR ESTANQUES
    ============================ */

    document.getElementById(
        "seccionEstanques"
    ).style.display = "none";


    /* ============================
       OCULTAR TIPO DE EJE
    ============================ */

    document.getElementById(
        "campoTipoEjeFicha"
    ).style.display = "none";

}

    console.log(
        "Equipo seleccionado:",
        e
    );


    document.getElementById(
        "m_patente"
    ).innerText =
        e.patente || "-";


    document.getElementById(
        "m_tipo"
    ).innerText =
        e.tipo || "-";


    document.getElementById(
        "m_marca"
    ).innerText =
        e.marca || "-";


    document.getElementById(
        "m_modelo"
    ).innerText =
        e.modelo || "-";


    document.getElementById(
        "m_anio"
    ).innerText =
        e.anio || "-";


    document.getElementById(
        "m_propietario"
    ).innerText =
        e.propietario || "-";


    document.getElementById(
        "m_chasis"
    ).innerText =
        e.numero_chasis || "-";


    document.getElementById(
        "m_color"
    ).innerText =
        e.color || "-";


    document.getElementById(
        "m_hp"
    ).innerText =
        e.hp || "-";


    document.getElementById(
        "m_config"
    ).innerText =
        e.configuracion || "-";


    document.getElementById(
        "m_sub"
    ).innerText =
        e.sub_configuracion || "-";

    document.getElementById(
    "m_tipo_eje"
).innerText =
    e.tipo_eje || "-";    


    document.getElementById(
        "m_fecha_permiso"
    ).innerText =
        formatearFecha(e.fecha_permiso);


    document.getElementById(
        "m_valor_permiso"
    ).innerText =
        formatearDinero(
            e.valor_permiso
        );


    document.getElementById(
        "m_municipalidad"
    ).innerText =
        e.municipalidad || "-";


    document.getElementById(
        "m_fecha_soap"
    ).innerText =
        formatearFecha(e.fecha_soap);


    document.getElementById(
        "m_valor_soap"
    ).innerText =
        formatearDinero(
            e.valor_soap
        );


    document.getElementById(
        "m_aseguradora"
    ).innerText =
        e.aseguradora || "-";


    document.getElementById(
        "m_fecha_revision"
    ).innerText =
        formatearFecha(e.fecha_revision_tecnica);


    document.getElementById(
        "m_valor_revision"
    ).innerText =
        formatearDinero(
            e.valor_revision_tecnica
        );


    document.getElementById(
        "m_subtitulo"
    ).innerText =
        "Patente " +
        (e.patente || "") +
        " · " +
        (e.marca || "") +
        " " +
        (e.modelo || "");


    document.getElementById(
        "linkHistorial"
    ).href =
        "historial.php?patente=" +
        encodeURIComponent(
            e.patente || ""
        );


    modalEquipo.classList.add(
        "active"
    );

}


function formatearDinero(valor){

    if(
        valor === null ||
        valor === undefined ||
        valor === ""
    ){

        return "-";

    }


    const numero =
        Number(valor);


    if(
        Number.isNaN(numero)
    ){

        return valor;

    }


    return "$ " +
        numero.toLocaleString(
            "es-CL"
        );

}


/* =========================================================
   CERRAR MODALES
========================================================= */

function cerrarModal(){

    document.querySelectorAll(
        ".modal"
    ).forEach(function(modal){

        modal.classList.remove(
            "active"
        );

    });

}


document.querySelectorAll(
    ".modal"
).forEach(function(modal){

    modal.addEventListener(
        "click",
        function(e){

            if(
                e.target === modal
            ){

                cerrarModal();

            }

        }
    );

});


document.addEventListener(
    "keydown",
    function(e){

        if(
            e.key === "Escape"
        ){

            cerrarModal();

        }

    }
);


/* =========================================================
   CONFIGURACIONES
========================================================= */

const configuraciones = {

    "TRACTOCAMION":[
        "SENCILLO",
        "DOBLE PUENTE"
    ],

    "SEMIREMOLQUE":[
        "RAMPLA",
        "CORTINA"
    ]

};


/* =========================================================
   SUB CONFIGURACIONES
========================================================= */

const subConfiguraciones = {

    "RAMPLA":[
        "NW",
        "TRIPLE"
    ],

    "CORTINA":[
        "SIDER / 1+1",
        "SIDER / NW",
        "SIDER / TRIPLE",
        "TOTAL SIDER / NW",
        "TOTAL SIDER / TRIPLE"
    ],

    "TRACTOCAMION":[
        "4X2",
        "6X2",
        "6X4"
    ]

};


/* =========================================================
   FILTRO TIPO
========================================================= */

tipo.addEventListener(
    "change",
    function(){

        actualizarMarca();

        actualizarConfiguracion();

        actualizarSub();

        actualizarAnios();

        aplicarFiltros();

    }
);


/* =========================================================
   ACTUALIZAR AÑOS SEGUN FILTROS
========================================================= */

function actualizarAnios(){

    const desdeActual =
        fechaDesde.value;

    const hastaActual =
        fechaHasta.value;

    const anios = new Set();

    document.querySelectorAll(
        "#tabla tbody tr"
    ).forEach(
        function(fila){

            const tipoFila =
                (
                    fila.dataset.tipo || ""
                ).toUpperCase();

            const marcaFila =
                (
                    fila.dataset.marca || ""
                ).toUpperCase();

            const configFila =
                (
                    fila.dataset.config || ""
                ).toUpperCase();

            const subFila =
                (
                    fila.dataset.sub || ""
                ).toUpperCase();

            const anio =
                fila.dataset.anio || "";

            if(!anio){
                return;
            }

            /* RESPETAR TIPO */
            if(
                tipo.value &&
                tipoFila !==
                tipo.value.toUpperCase()
            ){
                return;
            }

            /* RESPETAR MARCA */
            if(
                marca.value &&
                marcaFila !==
                marca.value.toUpperCase()
            ){
                return;
            }

            /* RESPETAR CONFIGURACION */
            if(
                config.value &&
                configFila !==
                config.value.toUpperCase()
            ){
                return;
            }

            /* RESPETAR SUB CONFIGURACION */
            if(
                sub.value &&
                subFila !==
                sub.value.toUpperCase()
            ){
                return;
            }

            anios.add(anio);

        }
    );


    /* AÑO DESDE */

    fechaDesde.innerHTML =
        "<option value=''>" +
        "📅 Año desde" +
        "</option>";


    Array.from(anios)
        .sort(
            function(a,b){
                return Number(a) - Number(b);
            }
        )
        .forEach(
            function(anio){

                const option =
                    document.createElement(
                        "option"
                    );

                option.value = anio;
                option.textContent = anio;

                fechaDesde.appendChild(
                    option
                );

            }
        );


    /* AÑO HASTA */

    fechaHasta.innerHTML =
        "<option value=''>" +
        "📅 Año hasta" +
        "</option>";


    Array.from(anios)
        .sort(
            function(a,b){
                return Number(a) - Number(b);
            }
        )
        .forEach(
            function(anio){

                const option =
                    document.createElement(
                        "option"
                    );

                option.value = anio;
                option.textContent = anio;

                fechaHasta.appendChild(
                    option
                );

            }
        );


    /* RECUPERAR VALORES SI SIGUEN EXISTIENDO */

    if(
        desdeActual &&
        anios.has(desdeActual)
    ){
        fechaDesde.value =
            desdeActual;
    }

    if(
        hastaActual &&
        anios.has(hastaActual)
    ){
        fechaHasta.value =
            hastaActual;
    }

}

/* =========================================================
   FILTRO MARCA
========================================================= */

marca.addEventListener(
    "change",
    function(){

        actualizarSub();

        actualizarAnios();

        aplicarFiltros();

    }
);

/* =========================================================
   FILTRO AÑO DESDE
========================================================= */

fechaDesde.addEventListener(
    "change",
    function(){

        aplicarFiltros();

    }
);


/* =========================================================
   FILTRO AÑO HASTA
========================================================= */

fechaHasta.addEventListener(
    "change",
    function(){

        aplicarFiltros();

    }
);


/* =========================================================
   FILTRO CONFIG
========================================================= */

config.addEventListener(
    "change",
    function(){

        actualizarSub();

        actualizarAnios();

        aplicarFiltros();

    }
);


/* =========================================================
   FILTRO SUB
========================================================= */

sub.addEventListener(
    "change",
    function(){

        actualizarAnios();

        aplicarFiltros();

    }
);


/* =========================================================
   BUSCADOR
========================================================= */

buscar.addEventListener(
    "input",
    function(){

        clearTimeout(
            window.timerBusqueda
        );


        window.timerBusqueda =
            setTimeout(
                aplicarFiltros,
                150
            );

    }
);


/* =========================================================
   ACTUALIZAR MARCAS SEGUN TIPO
========================================================= */

function actualizarMarca(){

    marca.innerHTML =
        "<option value=''>" +
        "🏭 Todas las marcas" +
        "</option>";

    const tipoSeleccionado =
        tipo.value.toUpperCase();

    const marcasEncontradas = new Set();

    document.querySelectorAll(
        "#tabla tbody tr"
    ).forEach(
        function(fila){

            const tipoFila =
                (
                    fila.dataset.tipo || ""
                ).toUpperCase();

            const marcaFila =
                (
                    fila.dataset.marca || ""
                ).trim();

            if(
                marcaFila &&
                (
                    !tipoSeleccionado ||
                    tipoFila === tipoSeleccionado
                )
            ){

                marcasEncontradas.add(
                    marcaFila
                );

            }

        }
    );

    Array.from(marcasEncontradas)
        .sort()
        .forEach(
            function(m){

                const option =
                    document.createElement(
                        "option"
                    );

                option.value = m;
                option.textContent = m;

                marca.appendChild(
                    option
                );

            }
        );

}


function exportarExcel(){

    const buscar = document.getElementById('buscar').value;
    const tipo = document.getElementById('tipo').value;
    const marca = document.getElementById('marca').value;
    const configuracion = document.getElementById('configuracion').value;
    const sub_configuracion = document.getElementById('sub_configuracion').value;
    const fechaDesde = document.getElementById('fechaDesde').value;
    const fechaHasta = document.getElementById('fechaHasta').value;


    const parametros = new URLSearchParams({

        buscar: buscar,
        tipo: tipo,
        marca: marca,
        configuracion: configuracion,
        sub_configuracion: sub_configuracion,
        fechaDesde: fechaDesde,
        fechaHasta: fechaHasta

    });


    window.location.href =
        'exportar_excel.php?' +
        parametros.toString();

}

/* =========================================================
   ACTUALIZAR CONFIGURACION FILTRO
========================================================= */

function actualizarConfiguracion(){

    config.innerHTML =
        "<option value=''>" +
        "⚙️ Todas las configuraciones" +
        "</option>";


    sub.innerHTML =
        "<option value=''>" +
        "🔧 Todas las sub configuraciones" +
        "</option>";


    if(!tipo.value){

        return;

    }


    const lista =
        configuraciones[
            tipo.value
        ] || [];


    lista.forEach(
        function(c){

            const option =
                document.createElement(
                    "option"
                );

            option.value = c;

            option.textContent = c;

            config.appendChild(
                option
            );

        }
    );

}


/* =========================================================
   ACTUALIZAR SUB FILTRO
========================================================= */

/* =========================================================
   ACTUALIZAR SUB CONFIGURACION SEGUN CONFIGURACION
========================================================= */

function actualizarSub(){

    sub.innerHTML =
        "<option value=''>" +
        "🔧 Todas las sub configuraciones" +
        "</option>";

    /* Si no hay configuración seleccionada,
       no mostramos subconfiguraciones */

    if(!config.value){
        return;
    }

    const configuracionSeleccionada =
        config.value.toUpperCase();

    const subEncontradas = new Set();

    document.querySelectorAll(
        "#tabla tbody tr"
    ).forEach(
        function(fila){

            const tipoFila =
                (
                    fila.dataset.tipo || ""
                ).toUpperCase();

            const marcaFila =
                (
                    fila.dataset.marca || ""
                ).toUpperCase();

            const configFila =
                (
                    fila.dataset.config || ""
                ).toUpperCase();

            const subFila =
                (
                    fila.dataset.sub || ""
                ).trim();

            /* La configuración debe coincidir */
            if(
                configFila !==
                configuracionSeleccionada
            ){
                return;
            }

            /* Si hay Tipo seleccionado,
               respetarlo */

            if(
                tipo.value &&
                tipoFila !==
                tipo.value.toUpperCase()
            ){
                return;
            }

            /* Si hay Marca seleccionada,
               respetarla */

            if(
                marca.value &&
                marcaFila !==
                marca.value.toUpperCase()
            ){
                return;
            }

            if(subFila){
                subEncontradas.add(
                    subFila
                );
            }

        }
    );


    Array.from(subEncontradas)
        .sort()
        .forEach(
            function(s){

                const option =
                    document.createElement(
                        "option"
                    );

                option.value = s;
                option.textContent = s;

                sub.appendChild(
                    option
                );

            }
        );

}


/* =========================================================
   FORMULARIO CONFIGURACION
========================================================= */

nuevoTipo.addEventListener(
    "change",
    function(){

        actualizarConfiguracionNuevo();

        actualizarEstanques();

        actualizarTipoEje();

    }
);


nuevoConfig.addEventListener(
    "change",
    function(){

        actualizarSubNuevo();

    }
);


function actualizarConfiguracionNuevo(){

    nuevoConfig.innerHTML =
        "<option value=''>" +
        "Seleccione...." +
        "</option>";


    nuevoSub.innerHTML =
        "<option value=''>" +
        "Seleccione...." +
        "</option>";


    const tipoNuevo =
        nuevoTipo.value;


    if(!tipoNuevo){

        return;

    }


    const lista =
        configuraciones[
            tipoNuevo
        ] || [];


    lista.forEach(
        function(c){

            const option =
                document.createElement(
                    "option"
                );

            option.value = c;

            option.textContent = c;

            nuevoConfig.appendChild(
                option
            );

        }
    );

}


function actualizarSubNuevo(){

    nuevoSub.innerHTML =
        "<option value=''>" +
        "Seleccione...." +
        "</option>";


    let lista = [];


    if(
        nuevoTipo.value ===
        "TRACTOCAMION"
    ){

        lista =
            subConfiguraciones[
                "TRACTOCAMION"
            ] || [];

    }
    else if(
        nuevoConfig.value
    ){

        lista =
            subConfiguraciones[
                nuevoConfig.value
            ] || [];

    }


    lista.forEach(
        function(s){

            const option =
                document.createElement(
                    "option"
                );

            option.value = s;

            option.textContent = s;

            nuevoSub.appendChild(
                option
            );

        }
    );

}


/* =========================================================
   FILTROS
========================================================= */

function aplicarFiltros(){

    const texto =
        buscar.value
        .toUpperCase()
        .trim();


    const t =
        tipo.value
        .toUpperCase();


    const c =
        config.value
        .toUpperCase();


    const s =
        sub.value
        .toUpperCase();

    const m =
    marca.value
    .toUpperCase();

    const desde =
    fechaDesde.value;

    const hasta =
    fechaHasta.value;    


    document.querySelectorAll(
        "#tabla tbody tr"
    ).forEach(
        function(fila){
            
            const marcaFila =
                (
                    fila.dataset.marca
                    || ""
                ).toUpperCase();

            const anioFila =
    fila.dataset.anio || "";;

            const patente =
                (
                    fila.dataset.patente
                    || ""
                ).toUpperCase();


            const tipoFila =
                (
                    fila.dataset.tipo
                    || ""
                ).toUpperCase();


            const configFila =
                (
                    fila.dataset.config
                    || ""
                ).toUpperCase();


            const subFila =
                (
                    fila.dataset.sub
                    || ""
                ).toUpperCase();


            let mostrar = true;


            

            if(
                texto &&
                !patente.includes(texto)
            ){

                mostrar = false;

            }


            if(
                t &&
                tipoFila !== t
            ){

                mostrar = false;

            }

            if(
    m &&
    marcaFila !== m
){

    mostrar = false;

}


            if(
                c &&
                configFila !== c
            ){

                mostrar = false;

            }


            if(
                s &&
                subFila !== s
            ){

                mostrar = false;

            }

            if(
    desde &&
    (!anioFila ||
     Number(anioFila) < Number(desde))
){
    mostrar = false;
}


if(
    hasta &&
    (!anioFila ||
     Number(anioFila) > Number(hasta))
){
    mostrar = false;
}


            fila.style.display =
                mostrar ? "" : "none";

        }
    );


    actualizarCards();

}


/* =========================================================
   LIMPIAR FILTROS
========================================================= */

function limpiarFiltros(){

    buscar.value = "";

    tipo.value = "";

    marca.value = "";

    fechaDesde.value = "";

    fechaHasta.value = "";

    actualizarMarca();

    config.innerHTML =
        "<option value=''>" +
        "⚙️ Todas las configuraciones" +
        "</option>";

    sub.innerHTML =
        "<option value=''>" +
        "🔧 Todas las sub configuraciones" +
        "</option>";

    aplicarFiltros();

}


/* =========================================================
   CARDS DINAMICAS
========================================================= */

function actualizarCards(){

    let total = 0;

    let tracto = 0;

    let rampla = 0;

    let cortina = 0;


    document.querySelectorAll(
        "#tabla tbody tr"
    ).forEach(
        function(fila){

            if(
                fila.style.display ===
                "none"
            ){

                return;

            }


            total++;


            const tipoFila =
                fila.children[1]
                .innerText
                .trim()
                .toUpperCase();


            const configFila =
                fila.children[5]
                .innerText
                .trim()
                .toUpperCase();


            if(
                tipoFila ===
                "TRACTOCAMION"
            ){

                tracto++;

            }


            if(
                configFila ===
                "RAMPLA"
            ){

                rampla++;

            }


            if(
                configFila ===
                "CORTINA"
            ){

                cortina++;

            }

        }
    );


    cardTotal.innerText =
        total;


    cardTracto.innerText =
        tracto;


    cardRampla.innerText =
        rampla;


    cardCortina.innerText =
        cortina;

}


/* =========================================================
   NUEVO EQUIPO
========================================================= */

function abrirNuevoEquipo(){

    document.getElementById(
        "formEquipo"
    ).reset();


    /* ============================
       LIMPIAR CONDUCTOR
    ============================ */

    document.getElementById(
        "conductorIdEquipo"
    ).value = "";


    document.getElementById(
        "buscarConductor"
    ).value = "";


    document.getElementById(
        "nombreConductorSeleccionado"
    ).innerHTML = "";


    document.getElementById(
        "conductorSeleccionado"
    ).style.display = "none";


    document.getElementById(
        "resultadosConductores"
    ).innerHTML = "";


    document.getElementById(
        "resultadosConductores"
    ).style.display = "none";


    actualizarTipoEje();


    document.getElementById(
        "patenteOriginal"
    ).value = "";


    document.getElementById(
        "tituloModalEquipo"
    ).innerHTML =
        "➕ Nuevo Equipo";


    document.getElementById(
        "formEquipo"
    ).action =
        "guardar_equipo.php";


    document.getElementById(
        "modoEquipo"
    ).value =
        "nuevo";


    document.getElementById(
        "btnGuardarEquipo"
    ).innerHTML =
        "Guardar Equipo";


    document.getElementById(
        "patenteEquipo"
    ).readOnly =
        false;


    nuevoConfig.innerHTML =
        "<option value=''>" +
        "Seleccione...." +
        "</option>";


    nuevoSub.innerHTML =
        "<option value=''>" +
        "Seleccione...." +
        "</option>";


    modalNuevo.classList.add(
        "active"
    );

}


/* =========================================================
   EDITAR EQUIPO
========================================================= */

function editarEquipo(e){

    document.getElementById("tituloModalEquipo").innerHTML =
        "✏️ Editar Equipo";

    document.getElementById("formEquipo").action =
        "actualizar_equipo.php";

    document.getElementById("modoEquipo").value =
        "editar";

    document.getElementById("btnGuardarEquipo").innerHTML =
        "Guardar Cambios";


    /* ============================
       PATENTE
    ============================ */

    const patente = e.patente || "";

    document.getElementById("patenteEquipo").value =
        patente;

    document.getElementById("patenteEquipo").readOnly =
        true;

    document.getElementById("patenteOriginal").value =
        patente;


    /* ============================
   DATOS GENERALES
============================ */

document.getElementById("marcaEquipo").value =
    e.marca || "";

document.getElementById("modeloEquipo").value =
    e.modelo || "";

document.getElementById("anioEquipo").value =
    e.anio || "";


/* ============================
   CONDUCTOR
============================ */

document.getElementById("conductorIdEquipo").value =
    e.conductor_id || "";

if(e.conductor_id){

    document.getElementById("buscarConductor").value =
        e.conductor_nombre || "";

    document.getElementById("nombreConductorSeleccionado").innerHTML =
        `
        👤 <strong>
            ${e.conductor_nombre || ""}
        </strong>

        <span>
            ${e.conductor_rut || ""}
        </span>
        `;

    document.getElementById("conductorSeleccionado").style.display =
        "flex";

}
else{

    document.getElementById("buscarConductor").value =
        "";

    document.getElementById("nombreConductorSeleccionado").innerHTML =
        "";

    document.getElementById("conductorSeleccionado").style.display =
        "none";

}


/* ============================
   RESTO DE DATOS
============================ */

document.getElementById("propietarioEquipo").value =
    e.propietario || "";

    document.getElementById("colorEquipo").value =
        e.color || "";

    document.getElementById("hpEquipo").value =
        e.hp || "";

    document.getElementById("chasisEquipo").value =
        e.numero_chasis || "";


    /* ============================
       TIPO
    ============================ */

    nuevoTipo.value =
        e.tipo || "";

    actualizarTipoEje();

tipoEje.value =
    e.tipo_eje || "";

    /* ============================
       CONFIGURACIÓN
    ============================ */

    actualizarConfiguracionNuevo();

    nuevoConfig.value =
        e.configuracion || "";


    /* ============================
       SUB CONFIGURACIÓN
    ============================ */

    actualizarSubNuevo();

    nuevoSub.value =
        e.sub_configuracion || "";


    /* ============================
       ESTANQUES
    ============================ */

    actualizarEstanques();

    estanque1.value =
        e.capacidad_estanque_1 || "";

    estanque2.value =
        e.capacidad_estanque_2 || "";


    /* ============================
       PERMISO DE CIRCULACIÓN
    ============================ */

    document.getElementById("fechaPermisoEquipo").value =
        e.fecha_permiso || "";

    document.getElementById("valorPermisoEquipo").value =
        e.valor_permiso || "";

    document.getElementById("municipalidadEquipo").value =
        e.municipalidad || "";


    /* ============================
       SOAP
    ============================ */

    document.getElementById("fechaSoapEquipo").value =
        e.fecha_soap || "";

    document.getElementById("valorSoapEquipo").value =
        e.valor_soap || "";

    document.getElementById("aseguradoraEquipo").value =
        e.aseguradora || "";


    /* ============================
       REVISIÓN TÉCNICA
    ============================ */

    document.getElementById("fechaRevisionEquipo").value =
        e.fecha_revision_tecnica || "";

    document.getElementById("valorRevisionEquipo").value =
        e.valor_revision_tecnica || "";


    /* ============================
       ABRIR MODAL
    ============================ */

    modalNuevo.classList.add("active");

}


/* =========================================================
   ELIMINAR
========================================================= */

function eliminarEquipo(
    patente,
    url
){

    if(
        typeof Swal ===
        "undefined"
    ){

        if(
            confirm(
                "¿Eliminar el equipo " +
                patente +
                "?"
            )
        ){

            window.location.href =
                url;

        }

        return;

    }


    Swal.fire({

        title:
            "¿Eliminar equipo?",

        html:
            "Se eliminará el equipo " +
            "<strong>" +
            patente +
            "</strong>.",

        icon:
            "warning",

        showCancelButton:
            true,

        confirmButtonColor:
            "#dc2626",

        cancelButtonColor:
            "#64748b",

        confirmButtonText:
            "Sí, eliminar",

        cancelButtonText:
            "Cancelar",

        reverseButtons:
            true

    }).then(
        function(result){

            if(
                result.isConfirmed
            ){

                window.location.href =
                    url;

            }

        }
    );

}


/* =========================================================
   DASHBOARD
========================================================= */

let charts = [];


function abrirDashboard(){

    modalDashboard.classList.add("active");


    /* =====================================================
       DESTRUIR GRAFICOS ANTERIORES
    ===================================================== */

    charts.forEach(function(chart){

        try{
            chart.destroy();
        }
        catch(error){}

    });

    charts = [];


    /* =====================================================
       FILAS VISIBLES
    ===================================================== */

    const filas =
        document.querySelectorAll(
            "#tabla tbody tr"
        );


    /* =====================================================
       DATOS
    ===================================================== */

    const tipoData = {};
    const configData = {};
    const subData = {};
    const marcaData = {};
    const anioData = {};

    const municipalidadData = {};
    const aseguradoraData = {};

    let totalPermiso = 0;
    let totalSoap = 0;
    let totalRevision = 0;


    /* =====================================================
       RECORRER EQUIPOS
    ===================================================== */

    filas.forEach(function(fila){

        /* Solo equipos visibles */

        if(
            fila.style.display === "none"
        ){

            return;

        }


        const c = fila.children;


        /* =================================================
           DATOS GENERALES
        ================================================= */

        const tipoValor =
            c[1].innerText.trim()
            || "Sin información";


        const marcaValor =
            c[2].innerText.trim()
            || "Sin información";


        const anioValor =
            c[4].innerText.trim()
            || "Sin información";


        const configValor =
            c[5].innerText.trim()
            || "Sin información";


        const subValor =
            c[6].innerText.trim()
            || "Sin información";


        tipoData[tipoValor] =
            (tipoData[tipoValor] || 0) + 1;


        marcaData[marcaValor] =
            (marcaData[marcaValor] || 0) + 1;


        anioData[anioValor] =
            (anioData[anioValor] || 0) + 1;


        configData[configValor] =
            (configData[configValor] || 0) + 1;


        subData[subValor] =
            (subData[subValor] || 0) + 1;


        /* =================================================
           VALORES ECONOMICOS
        ================================================= */

        const permiso =
            Number(
                fila.dataset.valorPermiso
            ) || 0;


        const soap =
            Number(
                fila.dataset.valorSoap
            ) || 0;


        const revision =
            Number(
                fila.dataset.valorRevision
            ) || 0;


        totalPermiso += permiso;
        totalSoap += soap;
        totalRevision += revision;


        /* =================================================
           MUNICIPALIDAD
        ================================================= */

        const municipalidad =
            (
                fila.dataset.municipalidad
                || "Sin información"
            ).trim();


        if(permiso > 0){

            municipalidadData[municipalidad] =
                (
                    municipalidadData[municipalidad]
                    || 0
                ) + permiso;

        }


        /* =================================================
           ASEGURADORA
        ================================================= */

        const aseguradora =
            (
                fila.dataset.aseguradora
                || "Sin información"
            ).trim();


        if(soap > 0){

            aseguradoraData[aseguradora] =
                (
                    aseguradoraData[aseguradora]
                    || 0
                ) + soap;

        }

    }); // ⭐ IMPORTANTE: CIERRA EL forEach


    /* =====================================================
       GRAFICOS
    ===================================================== */

    charts.push(
        crearGrafico(
            "graficoTipo",
            tipoData,
            "doughnut"
        )
    );


    charts.push(
        crearGrafico(
            "graficoConfig",
            configData,
            "pie"
        )
    );


    charts.push(
        crearGrafico(
            "graficoSub",
            subData,
            "bar"
        )
    );


    charts.push(
        crearGrafico(
            "graficoMarca",
            marcaData,
            "pie"
        )
    );


    charts.push(
        crearGrafico(
            "graficoAnio",
            anioData,
            "bar"
        )
    );


    /* =====================================================
       GASTOS GENERALES
    ===================================================== */

    charts.push(
        crearGrafico(
            "graficoGastos",
            {
                "Permiso de circulación":
                    totalPermiso,

                "SOAP":
                    totalSoap,

                "Revisión técnica":
                    totalRevision
            },
            "bar"
        )
    );


    /* =====================================================
       PERMISOS POR MUNICIPALIDAD
    ===================================================== */

    charts.push(
        crearGrafico(
            "graficoMunicipalidad",
            municipalidadData,
            "bar"
        )
    );


    /* =====================================================
       SOAP POR ASEGURADORA
    ===================================================== */

    charts.push(
        crearGrafico(
            "graficoAseguradora",
            aseguradoraData,
            "doughnut"
        )
    );


    /* =====================================================
       REVISION TECNICA
    ===================================================== */

    charts.push(
        crearGrafico(
            "graficoRevision",
            {
                "Revisión técnica":
                    totalRevision
            },
            "doughnut"
        )
    );

}


/* =========================================================
   CREAR GRAFICO
========================================================= */

function crearGrafico(
    id,
    data,
    tipoGrafico
){

    const canvas =
        document.getElementById(id);


    if(!canvas){

        return null;

    }


    return new Chart(
        canvas,
        {

            type:
                tipoGrafico,

            data:{

                labels:
                    Object.keys(data),

                datasets:[{

                    data:
                        Object.values(data),

                    borderWidth:
                        1

                }]

            },

            options:{

                responsive:true,

                maintainAspectRatio:true,

                plugins:{

                    legend:{

                        position:
                            "bottom"

                    }

                }

            }

        }
    );

}


/* =========================================================
   INICIO
========================================================= */

aplicarFiltros();

actualizarCards();

actualizarEstanques();

actualizarTipoEje();

</script>

</body>

</html>