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
   HISTORIAL
========================================================= */

$stmt = $conn->prepare("
    SELECT *
    FROM historial
    WHERE patente = ?
    ORDER BY fecha DESC, id DESC
");

$stmt->bind_param("s", $patente);
$stmt->execute();

$resultado = $stmt->get_result();

$eventos = [];

while($evento = $resultado->fetch_assoc()){

    $eventos[] = $evento;

}


/* =========================================================
   CONTADORES
========================================================= */

$totalEventos = count($eventos);

$ultimoEvento =
    $totalEventos > 0
        ? $eventos[0]['fecha']
        : null;


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

<title>Historial Eventos · <?=escapar($patente)?></title>

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
        repeat(2,1fr);

    gap:20px;

    margin-bottom:25px;

}

.summary-card{

    background:white;

    padding:25px;

    border-radius:18px;

    box-shadow:
    0 10px 30px rgba(0,0,0,.08);

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

    font-size:30px;

    color:#2563eb;

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
   EVENTO
========================================================= */

.evento{

    background:#f8fafc;

    border:1px solid #e2e8f0;

    border-left:6px solid #64748b;

    padding:22px;

    border-radius:16px;

    margin-bottom:16px;

    transition:.2s;

}

.evento:hover{

    transform:translateY(-2px);

    box-shadow:
    0 8px 25px rgba(15,23,42,.08);

}

.evento-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:15px;

    flex-wrap:wrap;

    margin-bottom:15px;

}

.evento-fecha{

    font-size:19px;

    font-weight:800;

    color:#2563eb;

}

.evento-id{

    font-size:12px;

    color:#64748b;

    background:#e2e8f0;

    padding:6px 10px;

    border-radius:10px;

}

.evento-detalle{

    background:white;

    border:1px solid #e2e8f0;

    padding:17px;

    border-radius:12px;

    line-height:1.6;

}

.evento-footer{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:15px;

    flex-wrap:wrap;

    margin-top:15px;

}

.usuario{

    color:#64748b;

    font-size:13px;

}

.usuario strong{

    color:#0f172a;

}

.btn-archivo{

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

@media(max-width:700px){

    body{

        padding:15px;

    }

    .summary{

        grid-template-columns:1fr;

    }

    .banner-text h1{

        font-size:24px;

    }

    .banner-datetime{

        display:none;

    }

    .evento-footer{

        align-items:stretch;

        flex-direction:column;

    }

    .btn-archivo button{

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
        alt="Historial de eventos"
    >

    <div class="banner-overlay"></div>

    <div class="banner-text">

        <h1>
            Historial de Eventos
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

    <div class="summary-card">

        <small>
            Eventos registrados
        </small>

        <strong>
            <?=$totalEventos?>
        </strong>

    </div>


    <div class="summary-card">

        <small>
            Último evento
        </small>

        <strong
            style="
                font-size:24px;
                color:#0f172a;
            "
        >
            <?=fechaChile($ultimoEvento)?>
        </strong>

    </div>

</div>


<!-- =======================================================
     LISTADO
======================================================= -->

<div class="panel">

    <div class="panel-header">

        <h2>
            📝 Registros del equipo
        </h2>

        <span
            style="
                color:#64748b;
                font-size:14px;
            "
        >
            <?=$totalEventos?> registro(s)
        </span>

    </div>


    <?php if($totalEventos === 0){ ?>

        <div class="vacio">

            <div class="icono">
                📝
            </div>

            <strong>
                No existen eventos registrados
            </strong>

            <p>
                Este equipo todavía no tiene eventos registrados.
            </p>

        </div>

    <?php } else { ?>


        <?php foreach($eventos as $evento){ ?>

            <div class="evento">


                <div class="evento-header">

                    <div class="evento-fecha">

                        📅
                        <?=fechaChile(
                            $evento['fecha']
                        )?>

                    </div>


                    <div class="evento-id">

                        Registro #
                        <?=intval($evento['id'])?>

                    </div>

                </div>


                <div class="evento-detalle">

                    <?=nl2br(
                        escapar(
                            $evento['detalle']
                        )
                    )?>

                </div>


                <div class="evento-footer">

                    <div class="usuario">

                        👤 Usuario responsable:

                        <strong>

                            <?=escapar(
                                $evento['usuario']
                            )?>

                        </strong>

                    </div>


                    <?php if(!empty($evento['imagen'])){ ?>

                        <a
                            href="imagenes/<?=rawurlencode($evento['imagen'])?>"
                            target="_blank"
                            class="btn-archivo"
                        >

                            <button
                                type="button"
                            >
                                📎 Abrir archivo
                            </button>

                        </a>

                    <?php } ?>

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
   MODAL
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


window.addEventListener(
    "click",
    function(e){

        const modal =
            document.getElementById(
                "modalEvento"
            );

        if(e.target === modal){

            cerrarModalEvento();

        }

    }
);


document.addEventListener(
    "keydown",
    function(e){

        if(e.key === "Escape"){

            cerrarModalEvento();

        }

    }
);

</script>

</body>
</html>
```
