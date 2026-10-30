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
    SELECT *
    FROM equipos
    WHERE patente = ?
    LIMIT 1
");

$stmt->bind_param("s", $patente);
$stmt->execute();

$equipo = $stmt->get_result()->fetch_assoc();

if (!$equipo) {
    header("Location: index.php");
    exit;
}


/* =========================================================
   FILTRO
========================================================= */

$filtroEstado = trim($_GET['estado'] ?? '');


/* =========================================================
   CONSULTA OT
========================================================= */

$sql = "
    SELECT
        ot.*,
        c.rut AS conductor_rut,
        c.nombre AS conductor_nombre,
        c.area AS conductor_area
    FROM ordenes_trabajo ot
    LEFT JOIN conductores c
        ON c.id = ot.conductor_id
    WHERE ot.patente = ?
";

if (
    $filtroEstado !== '' &&
    in_array(
        $filtroEstado,
        [
            'ABIERTA',
            'EN PROCESO',
            'CERRADA',
            'ANULADA'
        ],
        true
    )
) {

    $sql .= "
        AND ot.estado = ?
    ";

    $sql .= "
        ORDER BY
            ot.fecha_ingreso DESC,
            ot.id DESC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ss",
        $patente,
        $filtroEstado
    );

} else {

    $sql .= "
        ORDER BY
            ot.fecha_ingreso DESC,
            ot.id DESC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "s",
        $patente
    );
}

$stmt->execute();

$resultado = $stmt->get_result();

$ordenes = [];

while ($ot = $resultado->fetch_assoc()) {
    $ordenes[] = $ot;
}


/* =========================================================
   CONTADORES
========================================================= */

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(
            CASE
                WHEN estado = 'ABIERTA'
                THEN 1 ELSE 0
            END
        ) AS abiertas,
        SUM(
            CASE
                WHEN estado = 'EN PROCESO'
                THEN 1 ELSE 0
            END
        ) AS proceso,
        SUM(
            CASE
                WHEN estado = 'CERRADA'
                THEN 1 ELSE 0
            END
        ) AS cerradas,
        SUM(
            CASE
                WHEN estado = 'ANULADA'
                THEN 1 ELSE 0
            END
        ) AS anuladas
    FROM ordenes_trabajo
    WHERE patente = ?
");

$stmt->bind_param("s", $patente);
$stmt->execute();

$contadores = $stmt->get_result()->fetch_assoc();


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

function estadoClase($estado)
{
    switch ($estado) {

        case 'ABIERTA':
            return 'estado-abierta';

        case 'EN PROCESO':
            return 'estado-proceso';

        case 'CERRADA':
            return 'estado-cerrada';

        case 'ANULADA':
            return 'estado-anulada';

        default:
            return '';
    }
}

function estadoIcono($estado)
{
    switch ($estado) {

        case 'ABIERTA':
            return '🟢';

        case 'EN PROCESO':
            return '🟡';

        case 'CERRADA':
            return '🔵';

        case 'ANULADA':
            return '🔴';

        default:
            return '⚪';
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Historial OT · <?=escapar($patente)?></title>

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

    --green:#16a34a;
    --orange:#f59e0b;
    --red:#dc2626;

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
   BOTONES
========================================================= */

.btn,
button{

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

    transition:.2s;

}

.btn:hover,
button:hover{

    transform:translateY(-1px);

    box-shadow:
    0 5px 15px rgba(37,99,235,.25);

}

.btn-secondary{

    background:#64748b;

}

.acciones{

    display:flex;

    gap:12px;

    flex-wrap:wrap;

    margin-bottom:25px;

}

.acciones a{

    text-decoration:none;

}


/* =========================================================
   RESUMEN
========================================================= */

.summary{

    display:grid;

    grid-template-columns:
        repeat(5,1fr);

    gap:15px;

    margin-bottom:25px;

}

.summary-card{

    background:white;

    padding:20px;

    border-radius:17px;

    box-shadow:
    0 8px 25px rgba(0,0,0,.07);

}

.summary-card small{

    display:block;

    color:#64748b;

    font-size:12px;

    font-weight:800;

    text-transform:uppercase;

    margin-bottom:8px;

}

.summary-card strong{

    font-size:28px;

}

.summary-card.blue strong{
    color:#2563eb;
}

.summary-card.yellow strong{
    color:#d97706;
}

.summary-card.green strong{
    color:#15803d;
}

.summary-card.red strong{
    color:#dc2626;
}


/* =========================================================
   PANEL
========================================================= */

.panel{

    background:white;

    padding:25px;

    border-radius:20px;

    box-shadow:
    0 10px 30px rgba(0,0,0,.08);

}

.panel-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:15px;

    flex-wrap:wrap;

    margin-bottom:20px;

}

.panel-header h2{

    margin:0;

}


/* =========================================================
   FILTROS
========================================================= */

.filtros{

    display:flex;

    gap:10px;

    flex-wrap:wrap;

}

.filtro{

    text-decoration:none;

    padding:9px 14px;

    border-radius:10px;

    background:#f1f5f9;

    color:#475569;

    font-size:13px;

    font-weight:700;

    border:1px solid #e2e8f0;

}

.filtro:hover{

    background:#e2e8f0;

}

.filtro.active{

    background:#2563eb;

    color:white;

    border-color:#2563eb;

}


/* =========================================================
   OT
========================================================= */

.ot-card{

    background:#f8fafc;

    border:1px solid #e2e8f0;

    border-left:6px solid #2563eb;

    padding:22px;

    border-radius:16px;

    margin-bottom:16px;

    transition:.2s;

}

.ot-card:hover{

    transform:translateY(-2px);

    box-shadow:
    0 8px 25px rgba(15,23,42,.08);

}

.ot-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:15px;

    flex-wrap:wrap;

}

.ot-numero{

    font-size:22px;

    font-weight:800;

    color:#1e40af;

}

.ot-fecha{

    color:#64748b;

    font-size:13px;

    margin-top:5px;

}

.estado{

    display:inline-flex;

    align-items:center;

    gap:6px;

    padding:7px 12px;

    border-radius:20px;

    font-size:12px;

    font-weight:800;

}

.estado-abierta{

    background:#dbeafe;

    color:#1d4ed8;

}

.estado-proceso{

    background:#fef3c7;

    color:#b45309;

}

.estado-cerrada{

    background:#dcfce7;

    color:#15803d;

}

.estado-anulada{

    background:#fee2e2;

    color:#b91c1c;

}

.ot-grid{

    display:grid;

    grid-template-columns:
        repeat(3,1fr);

    gap:15px;

    margin-top:18px;

}

.ot-info{

    background:white;

    padding:14px;

    border-radius:12px;

    border:1px solid #e2e8f0;

}

.ot-info small{

    display:block;

    color:#64748b;

    font-size:11px;

    font-weight:800;

    text-transform:uppercase;

    margin-bottom:5px;

}

.ot-info strong{

    font-size:14px;

}

.ot-motivo{

    margin-top:15px;

    padding:15px;

    border-radius:12px;

    background:white;

    border:1px solid #e2e8f0;

}

.ot-motivo small{

    color:#64748b;

    font-weight:800;

    font-size:11px;

}

.ot-motivo p{

    margin:8px 0 0;

    line-height:1.5;

}

.ot-footer{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:15px;

    flex-wrap:wrap;

    margin-top:18px;

}

.ot-footer a{

    text-decoration:none;

}


/* =========================================================
   VACIO
========================================================= */

.vacio{

    padding:50px 20px;

    text-align:center;

    color:#64748b;

}

.vacio .icono{

    font-size:50px;

    margin-bottom:10px;

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
   RESPONSIVE
========================================================= */

@media(max-width:1100px){

    .summary{

        grid-template-columns:
            repeat(3,1fr);

    }

}

@media(max-width:700px){

    body{

        padding:15px;

    }

    .summary{

        grid-template-columns:1fr 1fr;

    }

    .ot-grid{

        grid-template-columns:1fr;

    }

    .banner-text h1{

        font-size:24px;

    }

    .banner-datetime{

        display:none;

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
        alt="Historial de órdenes de trabajo"
    >

    <div class="banner-overlay"></div>

    <div class="banner-text">

        <h1>
            Historial de Órdenes de Trabajo
        </h1>

        <p>
            Equipo <?=escapar($patente)?>
            ·
            <?=escapar($equipo['marca'] ?? '')?>
            <?=escapar($equipo['modelo'] ?? '')?>
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
        href="historial.php?patente=<?=urlencode($patente)?>"
    >

        <button
            type="button"
            class="btn-secondary"
        >
            ← Volver al dashboard
        </button>

    </a>

</div>


<!-- =======================================================
     RESUMEN
======================================================= -->

<div class="summary">

    <div class="summary-card blue">

        <small>
            Total OT
        </small>

        <strong>
            <?=intval($contadores['total'] ?? 0)?>
        </strong>

    </div>


    <div class="summary-card blue">

        <small>
            Abiertas
        </small>

        <strong>
            <?=intval($contadores['abiertas'] ?? 0)?>
        </strong>

    </div>


    <div class="summary-card yellow">

        <small>
            En proceso
        </small>

        <strong>
            <?=intval($contadores['proceso'] ?? 0)?>
        </strong>

    </div>


    <div class="summary-card green">

        <small>
            Cerradas
        </small>

        <strong>
            <?=intval($contadores['cerradas'] ?? 0)?>
        </strong>

    </div>


    <div class="summary-card red">

        <small>
            Anuladas
        </small>

        <strong>
            <?=intval($contadores['anuladas'] ?? 0)?>
        </strong>

    </div>

</div>


<!-- =======================================================
     LISTADO
======================================================= -->

<div class="panel">

    <div class="panel-header">

        <h2>
            🔧 Órdenes registradas
        </h2>


        <div class="filtros">

            <a
                href="historial_ot.php?patente=<?=urlencode($patente)?>"
                class="filtro <?=($filtroEstado === '' ? 'active' : '')?>"
            >
                Todas
            </a>


            <a
                href="historial_ot.php?patente=<?=urlencode($patente)?>&estado=ABIERTA"
                class="filtro <?=($filtroEstado === 'ABIERTA' ? 'active' : '')?>"
            >
                🟢 Abiertas
            </a>


            <a
                href="historial_ot.php?patente=<?=urlencode($patente)?>&estado=EN%20PROCESO"
                class="filtro <?=($filtroEstado === 'EN PROCESO' ? 'active' : '')?>"
            >
                🟡 En proceso
            </a>


            <a
                href="historial_ot.php?patente=<?=urlencode($patente)?>&estado=CERRADA"
                class="filtro <?=($filtroEstado === 'CERRADA' ? 'active' : '')?>"
            >
                🔵 Cerradas
            </a>


            <a
                href="historial_ot.php?patente=<?=urlencode($patente)?>&estado=ANULADA"
                class="filtro <?=($filtroEstado === 'ANULADA' ? 'active' : '')?>"
            >
                🔴 Anuladas
            </a>

        </div>

    </div>


    <?php if(count($ordenes) === 0){ ?>

        <div class="vacio">

            <div class="icono">
                🔧
            </div>

            <strong>
                No existen órdenes de trabajo
                <?=($filtroEstado !== '' ? ' con este estado' : '')?>
            </strong>

            <p>
                No hay registros para mostrar.
            </p>

        </div>

    <?php } else { ?>


        <?php foreach($ordenes as $ot){ ?>

            <div class="ot-card">


                <div class="ot-header">

                    <div>

                        <div class="ot-numero">

                            🔧 OT #

                            <?=str_pad(
                                $ot['numero_ot'],
                                4,
                                '0',
                                STR_PAD_LEFT
                            )?>

                        </div>

                        <div class="ot-fecha">

                            📅 Ingreso:
                            <?=fechaChile($ot['fecha_ingreso'])?>

                        </div>

                    </div>


                    <span
                        class="estado <?=estadoClase($ot['estado'])?>"
                    >

                        <?=estadoIcono($ot['estado'])?>

                        <?=escapar($ot['estado'])?>

                    </span>

                </div>


                <div class="ot-grid">


                    <div class="ot-info">

                        <small>
                            Conductor
                        </small>

                        <strong>

                            <?=escapar(
                                $ot['conductor_nombre']
                                ?: 'Sin conductor'
                            )?>

                        </strong>

                    </div>


                    <div class="ot-info">

                        <small>
                            RUT
                        </small>

                        <strong>

                            <?=escapar(
                                $ot['conductor_rut']
                                ?: 'No informado'
                            )?>

                        </strong>

                    </div>


                    <div class="ot-info">

                        <small>
                            Odómetro
                        </small>

                        <strong>

                            <?php

                            if(
                                $ot['odometro'] !== null &&
                                $ot['odometro'] !== ''
                            ){

                                echo number_format(
                                    $ot['odometro'],
                                    0,
                                    ',',
                                    '.'
                                ) . ' km';

                            }else{

                                echo 'No informado';

                            }

                            ?>

                        </strong>

                    </div>


                </div>


                <div class="ot-motivo">

                    <small>
                        MOTIVO DE INGRESO
                    </small>

                    <p>

                        <?=nl2br(
                            escapar(
                                $ot['motivo_ingreso']
                            )
                        )?>

                    </p>

                </div>


                <?php if (!empty($ot['documento_ot'])) { ?>

    <div
        style="
            margin-top:15px;
            padding:10px 14px;
            border-radius:10px;
            background:#eff6ff;
            color:#1d4ed8;
            font-size:13px;
            font-weight:700;
        "
    >

        📎 Documento / SCAN adjunto

    </div>

<?php } ?>

                <div class="ot-footer">

                    <div
                        style="
                            color:#64748b;
                            font-size:13px;
                        "
                    >

                        Fecha salida:

                        <strong>

                            <?=fechaChile(
                                $ot['fecha_salida']
                            )?>

                        </strong>

                    </div>


                    <a
                        href="detalle_ot.php?id=<?=$ot['id']?>"
                    >

                        <button
                            type="button"
                        >
                            Ver detalle OT →
                        </button>

                    </a>

                </div>


            </div>

        <?php } ?>


    <?php } ?>

</div>


<footer>

    <strong>
        Transportes Mimbral LTDA
    </strong>

    <br>

    Gestión empresarial · © <?=date('Y')?>

</footer>


<script>

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

</script>

</body>
</html>
```
