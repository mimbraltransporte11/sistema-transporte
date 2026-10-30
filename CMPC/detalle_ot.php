```php
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


/* =========================================================
   OT
========================================================= */

$stmt = $conn->prepare("
    SELECT
        ot.*,

        c.rut AS conductor_rut,
        c.nombre AS conductor_nombre,
        c.area AS conductor_area,
        c.estado AS conductor_estado

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


/* =========================================================
   EQUIPO
========================================================= */

$patente = trim(
    $ot['patente'] ?? ''
);

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

    $maestros[] = $fila;

}


/* =========================================================
   DOCUMENTO
========================================================= */

$documento = trim(
    $ot['documento_ot'] ?? ''
);

$rutaDocumento = '';

$rutaFisica = '';

$extensionDocumento = '';

$existeDocumento = false;


if ($documento !== '') {

    $rutaDocumento =
        'documentos_ot/' .
        rawurlencode($documento);

    $rutaFisica =
        __DIR__ .
        DIRECTORY_SEPARATOR .
        'documentos_ot' .
        DIRECTORY_SEPARATOR .
        $documento;


    if (is_file($rutaFisica)) {

        $existeDocumento = true;

        $extensionDocumento =
            strtolower(
                pathinfo(
                    $documento,
                    PATHINFO_EXTENSION
                )
            );

    }

}


$esImagen =
    in_array(
        $extensionDocumento,
        [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp'
        ],
        true
    );


$esPdf =
    $extensionDocumento === 'pdf';

?>
<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>
    Detalle OT #<?=str_pad(
        $ot['numero_ot'],
        4,
        '0',
        STR_PAD_LEFT
    )?>
    · <?=escapar($patente)?>
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

.acciones{

    display:flex;

    gap:12px;

    flex-wrap:wrap;

    margin-bottom:25px;

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

    transition:.2s;

}


.btn:hover{

    transform:translateY(-1px);

    box-shadow:
    0 5px 15px rgba(37,99,235,.25);

}


.btn-secondary{

    background:#64748b;

}


/* =========================================================
   HEADER OT
========================================================= */

.ot-header-card{

    background:white;

    padding:25px;

    border-radius:20px;

    box-shadow:
    0 10px 30px rgba(0,0,0,.08);

    margin-bottom:20px;

}


.ot-header-flex{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:20px;

    flex-wrap:wrap;

}


.ot-numero{

    font-size:30px;

    font-weight:800;

    color:#1e40af;

}


.ot-fecha{

    color:#64748b;

    margin-top:6px;

}


.estado{

    display:inline-flex;

    align-items:center;

    gap:6px;

    padding:8px 14px;

    border-radius:20px;

    font-size:13px;

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


/* =========================================================
   EQUIPO
========================================================= */

.equipo-grid{

    display:grid;

    grid-template-columns:
        repeat(4,1fr);

    gap:15px;

    margin-top:20px;

}


.info{

    background:#f8fafc;

    border:1px solid #e2e8f0;

    padding:16px;

    border-radius:13px;

}


.info small{

    display:block;

    color:#64748b;

    font-size:11px;

    font-weight:800;

    text-transform:uppercase;

    margin-bottom:6px;

}


.info strong{

    font-size:15px;

}


/* =========================================================
   GRID PRINCIPAL
========================================================= */

.main-grid{

    display:grid;

    grid-template-columns:
        1fr 1fr;

    gap:20px;

    margin-bottom:20px;

}


.panel{

    background:white;

    padding:25px;

    border-radius:20px;

    box-shadow:
    0 10px 30px rgba(0,0,0,.08);

}


.panel h2{

    margin-top:0;

    margin-bottom:20px;

    font-size:19px;

}


/* =========================================================
   MOTIVO
========================================================= */

.motivo{

    background:#f8fafc;

    border:1px solid #e2e8f0;

    border-radius:14px;

    padding:18px;

    line-height:1.6;

}


.motivo small{

    display:block;

    color:#64748b;

    font-size:11px;

    font-weight:800;

    margin-bottom:8px;

    text-transform:uppercase;

}


/* =========================================================
   MAESTROS
========================================================= */

.maestros{

    display:flex;

    flex-direction:column;

    gap:10px;

}


.maestro{

    display:flex;

    align-items:center;

    gap:10px;

    padding:13px 15px;

    border-radius:12px;

    background:#f8fafc;

    border:1px solid #e2e8f0;

    font-weight:600;

}


.maestro-icon{

    font-size:20px;

}


/* =========================================================
   DOCUMENTO
========================================================= */

.documento-panel{

    margin-top:20px;

}


.documento-box{

    border:1px solid #dbe3ef;

    background:#f8fafc;

    border-radius:16px;

    padding:20px;

    text-align:center;

}


.documento-preview{

    max-width:100%;

    max-height:500px;

    border-radius:12px;

    cursor:pointer;

    box-shadow:
    0 8px 25px rgba(15,23,42,.12);

    transition:.2s;

}


.documento-preview:hover{

    transform:scale(1.01);

}


.documento-nombre{

    margin-top:12px;

    color:#64748b;

    font-size:13px;

    word-break:break-all;

}


.documento-botones{

    margin-top:15px;

    display:flex;

    justify-content:center;

    gap:10px;

    flex-wrap:wrap;

}


.documento-vacio{

    padding:25px;

    color:#64748b;

}


/* =========================================================
   MODAL IMAGEN
========================================================= */

.modal{

    display:none;

    position:fixed;

    z-index:9999;

    inset:0;

    background:rgba(15,23,42,.85);

    align-items:center;

    justify-content:center;

    padding:30px;

}


.modal img{

    max-width:95vw;

    max-height:90vh;

    border-radius:15px;

    box-shadow:
    0 20px 60px rgba(0,0,0,.4);

}


.modal-close{

    position:absolute;

    top:20px;

    right:25px;

    color:white;

    font-size:38px;

    cursor:pointer;

    font-weight:300;

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

@media(max-width:1000px){

    .equipo-grid{

        grid-template-columns:
            repeat(2,1fr);

    }

}


@media(max-width:750px){

    body{

        padding:15px;

    }


    .main-grid{

        grid-template-columns:1fr;

    }


    .equipo-grid{

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
        alt="Detalle Orden de Trabajo"
    >


    <div class="banner-overlay"></div>


    <div class="banner-text">

        <h1>
            Detalle de Orden de Trabajo
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
        href="historial_ot.php?patente=<?=urlencode($patente)?>"
        class="btn btn-secondary"
    >
        ← Volver al historial OT
    </a>


    <a
        href="editar_ot.php?id=<?=$id?>"
        class="btn"
    >
        ✏️ Editar OT
    </a>

</div>


<!-- =======================================================
     CABECERA OT
======================================================= -->

<div class="ot-header-card">

    <div class="ot-header-flex">

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

                📅 Fecha de ingreso:

                <?=fechaChile(
                    $ot['fecha_ingreso']
                )?>

            </div>

        </div>


        <span
            class="estado <?=estadoClase($ot['estado'])?>"
        >

            <?=estadoIcono($ot['estado'])?>

            <?=escapar($ot['estado'])?>

        </span>

    </div>


    <!-- =====================================================
         EQUIPO
    ===================================================== -->

    <div class="equipo-grid">

        <div class="info">

            <small>Patente</small>

            <strong>
                <?=escapar($patente)?>
            </strong>

        </div>


        <div class="info">

            <small>Tipo</small>

            <strong>
                <?=escapar($equipo['tipo'] ?? '-')?>
            </strong>

        </div>


        <div class="info">

            <small>Marca</small>

            <strong>
                <?=escapar($equipo['marca'] ?? '-')?>
            </strong>

        </div>


        <div class="info">

            <small>Modelo</small>

            <strong>
                <?=escapar($equipo['modelo'] ?? '-')?>
            </strong>

        </div>

    </div>

</div>


<!-- =======================================================
     INFORMACIÓN PRINCIPAL
======================================================= -->

<div class="main-grid">


    <!-- =====================================================
         INFORMACIÓN OT
    ===================================================== -->

    <div class="panel">

        <h2>
            📋 Información de la OT
        </h2>


        <div class="equipo-grid">

            <div class="info">

                <small>Número OT</small>

                <strong>

                    #

                    <?=str_pad(
                        $ot['numero_ot'],
                        4,
                        '0',
                        STR_PAD_LEFT
                    )?>

                </strong>

            </div>


            <div class="info">

                <small>Fecha ingreso</small>

                <strong>

                    <?=fechaChile(
                        $ot['fecha_ingreso']
                    )?>

                </strong>

            </div>


            <div class="info">

                <small>Fecha salida</small>

                <strong>

                    <?=fechaChile(
                        $ot['fecha_salida']
                    )?>

                </strong>

            </div>


            <div class="info">

                <small>Odómetro</small>

                <strong>

                    <?php

                    if (
                        $ot['odometro'] !== null &&
                        $ot['odometro'] !== ''
                    ) {

                        echo number_format(
                            $ot['odometro'],
                            0,
                            ',',
                            '.'
                        ) . ' km';

                    } else {

                        echo 'No informado';

                    }

                    ?>

                </strong>

            </div>

        </div>

    </div>


    <!-- =====================================================
         CONDUCTOR
    ===================================================== -->

    <div class="panel">

        <h2>
            👤 Conductor asociado a la OT
        </h2>


        <?php

        if (
            !empty($ot['conductor_nombre'])
        ) {

        ?>

            <div class="info">

                <small>Nombre</small>

                <strong>
                    <?=escapar(
                        $ot['conductor_nombre']
                    )?>
                </strong>

            </div>


            <br>


            <div class="info">

                <small>RUT</small>

                <strong>

                    <?=escapar(
                        $ot['conductor_rut']
                        ?: 'No informado'
                    )?>

                </strong>

            </div>


            <br>


            <div class="info">

                <small>Área</small>

                <strong>

                    <?=escapar(
                        $ot['conductor_area']
                        ?: 'No informada'
                    )?>

                </strong>

            </div>


        <?php

        } else {

        ?>

            <div class="documento-vacio">

                👤

                <br><br>

                No existe conductor asociado
                a esta OT.

            </div>

        <?php

        }

        ?>

    </div>

</div>


<!-- =======================================================
     MOTIVO + MAESTROS
======================================================= -->

<div class="main-grid">


    <div class="panel">

        <h2>
            📝 Motivo de ingreso
        </h2>


        <div class="motivo">

            <small>
                Solicitud / motivo
            </small>


            <?php

            if (
                trim(
                    $ot['motivo_ingreso'] ?? ''
                ) !== ''
            ) {

            ?>

                <?=nl2br(
                    escapar(
                        $ot['motivo_ingreso']
                    )
                )?>


            <?php

            } else {

            ?>

                <span style="color:#94a3b8;">

                    No se informó motivo de ingreso.

                </span>


            <?php

            }

            ?>

        </div>

    </div>


    <div class="panel">

        <h2>
            🔧 Maestros encargados
        </h2>


        <?php

        if (!empty($maestros)) {

        ?>

            <div class="maestros">

                <?php

                foreach (
                    $maestros as $maestro
                ) {

                ?>

                    <div class="maestro">

                        <span class="maestro-icon">
                            👨‍🔧
                        </span>


                        <?=escapar(
                            $maestro['maestro']
                        )?>

                    </div>

                <?php

                }

                ?>

            </div>


        <?php

        } else {

        ?>

            <div class="documento-vacio">

                👨‍🔧

                <br><br>

                No hay maestros registrados
                para esta OT.

            </div>


        <?php

        }

        ?>

    </div>

</div>


<!-- =======================================================
     DOCUMENTO / SCAN
======================================================= -->

<div class="panel documento-panel">

    <h2>
        📎 Documento / SCAN de la OT
    </h2>


    <div class="documento-box">


        <?php

        if (
            $existeDocumento &&
            $esImagen
        ) {

        ?>

            <!-- =============================================
                 IMAGEN
            ============================================== -->

            <img
                src="<?=escapar($rutaDocumento)?>"
                class="documento-preview"
                alt="Documento OT"
                onclick="abrirImagen()"
            >


            <div class="documento-nombre">

                <?=escapar($documento)?>

            </div>


            <div class="documento-botones">

                <a
                    href="<?=escapar($rutaDocumento)?>"
                    target="_blank"
                    class="btn"
                >
                    🔍 Abrir imagen
                </a>

            </div>


        <?php

        } else {

            if (
                $existeDocumento &&
                $esPdf
            ) {

        ?>

                <!-- =========================================
                     PDF
                ========================================== -->

                <div style="font-size:60px;">
                    📄
                </div>


                <div
                    style="
                        margin-top:10px;
                        font-weight:800;
                        font-size:17px;
                    "
                >
                    Documento PDF
                </div>


                <div class="documento-nombre">

                    <?=escapar($documento)?>

                </div>


                <div class="documento-botones">

                    <a
                        href="<?=escapar($rutaDocumento)?>"
                        target="_blank"
                        class="btn"
                    >
                        📄 Abrir PDF
                    </a>

                </div>


            <?php

            } else {

                if (
                    $documento !== ''
                ) {

            ?>

                    <!-- =====================================
                         DOCUMENTO NO ENCONTRADO
                    ====================================== -->

                    <div style="font-size:50px;">
                        ⚠️
                    </div>


                    <p>

                        El documento está registrado,
                        pero el archivo físico no fue encontrado.

                    </p>


                    <div class="documento-nombre">

                        <?=escapar($documento)?>

                    </div>


                <?php

                } else {

                ?>

                    <!-- =====================================
                         SIN DOCUMENTO
                    ====================================== -->

                    <div class="documento-vacio">

                        📎

                        <br><br>

                        Esta OT no tiene documento asociado.

                    </div>


                <?php

                }

            }

        }

        ?>

    </div>

</div>


<!-- =======================================================
     MODAL IMAGEN
======================================================= -->

<?php

if (
    $existeDocumento &&
    $esImagen
) {

?>

    <div
        class="modal"
        id="modalImagen"
        onclick="cerrarImagen()"
    >

        <span
            class="modal-close"
            onclick="cerrarImagen()"
        >
            ×
        </span>


        <img
            src="<?=escapar($rutaDocumento)?>"
            alt="Documento OT ampliado"
            onclick="event.stopPropagation()"
        >

    </div>

<?php

}

?>


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


<!-- =======================================================
     JAVASCRIPT
======================================================= -->

<script>


function actualizarHora(){

    const now = new Date();


    const fecha =
        document.getElementById(
            'banner-date'
        );


    const hora =
        document.getElementById(
            'banner-time'
        );


    if (fecha) {

        fecha.innerText =
            now.toLocaleDateString(
                'es-CL',
                {
                    weekday:'long',
                    day:'2-digit',
                    month:'long',
                    year:'numeric'
                }
            );

    }


    if (hora) {

        hora.innerText =
            now.toLocaleTimeString(
                'es-CL'
            );

    }

}


setInterval(
    actualizarHora,
    1000
);


actualizarHora();



/* =========================================================
   ABRIR IMAGEN
========================================================= */

function abrirImagen(){

    const modal =
        document.getElementById(
            'modalImagen'
        );


    if (modal) {

        modal.style.display = 'flex';

    }

}


/* =========================================================
   CERRAR IMAGEN
========================================================= */

function cerrarImagen(){

    const modal =
        document.getElementById(
            'modalImagen'
        );


    if (modal) {

        modal.style.display = 'none';

    }

}


/* =========================================================
   ESC PARA CERRAR
========================================================= */

document.addEventListener(
    'keydown',
    function(event){

        if (
            event.key === 'Escape'
        ) {

            cerrarImagen();

        }

    }
);

</script>


</body>

</html>
```

