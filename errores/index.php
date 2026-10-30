```php
<?php

include 'db.php';

date_default_timezone_set('America/Santiago');


/* =========================================================
   GUARDAR INFRACCION
========================================================= */

if (isset($_POST['guardar'])) {

    $rut = $_POST['rut'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $area = $_POST['area'] ?? '';
    $observacion = $_POST['observacion'] ?? '';
    $fecha = $_POST['fecha'] ?? '';

    /*
     * Valor estimativo:
     * Es opcional.
     * Si no se ingresa, se guarda como NULL.
     */
    $valor_estimado = !empty($_POST['valor_estimado'])
        ? floatval($_POST['valor_estimado'])
        : null;

    $imagen = '';

    /* ===== SUBIR ARCHIVO ===== */

    if (!empty($_FILES['imagen']['name'])) {

        $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

        $ext = strtolower(
            pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION)
        );

        if (in_array($ext, $permitidos)) {

            $imagen = "infraccion_" . time() . "." . $ext;

            move_uploaded_file(
                $_FILES['imagen']['tmp_name'],
                "imagenes/" . $imagen
            );
        }
    }


    /* ===== INSERTAR ===== */

    $stmt = $conn->prepare("
        INSERT INTO infracciones
        (
            rut,
            nombre,
            area,
            observacion,
            valor_estimado,
            fecha,
            imagen
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssdss",
        $rut,
        $nombre,
        $area,
        $observacion,
        $valor_estimado,
        $fecha,
        $imagen
    );

    $stmt->execute();

    header("Location: index.php");
    exit;
}


/* =========================================================
   ELIMINAR INFRACCION
========================================================= */

if (isset($_GET['eliminar'])) {

    $id = intval($_GET['eliminar']);

    $conn->query("
        DELETE FROM infracciones
        WHERE id = $id
    ");

    header("Location: index.php");
    exit;
}


/* =========================================================
   GUARDAR CONDUCTOR
========================================================= */

if (isset($_POST['guardar_conductor'])) {

    $rut = $_POST['rut_conductor'] ?? '';
    $nombre = $_POST['nombre_conductor'] ?? '';
    $area = $_POST['area_conductor'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO conductores
        (
            rut,
            nombre,
            area
        )
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param(
        "sss",
        $rut,
        $nombre,
        $area
    );

    $stmt->execute();

    header("Location: index.php");
    exit;
}


/* =========================================================
   OBTENER CONDUCTORES
========================================================= */

$conductores = $conn->query("
    SELECT *
    FROM conductores
    ORDER BY nombre
");


/* =========================================================
   OBTENER INFRACCIONES
========================================================= */

$infracciones = $conn->query("
    SELECT *
    FROM infracciones
    ORDER BY fecha DESC
");

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Sistema de Errores</title>

<link rel="icon" type="image/png" href="/errores/favicon.png?v=2">
<link rel="shortcut icon" type="image/png" href="/errores/favicon.png?v=2">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">


<style>

/* =========================================================
   VARIABLES
========================================================= */

:root{

    --primary:#2563eb;
    --primary-dark:#1e40af;

    --danger:#dc2626;
    --danger-dark:#b91c1c;

    --bg1:#f1f5f9;
    --bg2:#e0e7ff;

    --card:#ffffff;

    --text:#0f172a;
    --muted:#64748b;

}


/* =========================================================
   GENERAL
========================================================= */

*{
    box-sizing:border-box;
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

    font-family:Inter,sans-serif;

    color:var(--text);

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

}


.banner-overlay{

    position:absolute;

    inset:0;

    background:
        linear-gradient(
            90deg,
            rgba(15,23,42,.75),
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

    font-size:30px;

}


.banner-text p{

    margin:5px 0 0;

    opacity:.9;

}


/* =========================================================
   RELOJ
========================================================= */

.banner-datetime{

    position:absolute;

    right:25px;

    bottom:20px;

    background:
        rgba(255,255,255,0.18);

    backdrop-filter:blur(10px);

    padding:12px 18px;

    border-radius:16px;

    color:white;

    text-align:right;

    box-shadow:
        0 10px 25px rgba(0,0,0,.35);

    border:
        1px solid rgba(255,255,255,0.25);

}


#banner-date{

    font-size:12px;

    letter-spacing:.5px;

    opacity:.85;

    text-transform:capitalize;

}


#banner-time{

    font-size:26px;

    font-weight:800;

    margin-top:2px;

}


/* =========================================================
   CARD
========================================================= */

.card{

    background:var(--card);

    padding:25px;

    border-radius:18px;

    box-shadow:
        0 10px 30px rgba(0,0,0,.08);

    margin:
        0 auto 35px;

    max-width:1400px;

    overflow-x:auto;

}


/* =========================================================
   TABLE
========================================================= */

table{

    width:100%;

    border-collapse:separate;

    border-spacing:0;

    border-radius:14px;

}


th,
td{

    padding:14px;

    border-bottom:
        1px solid #e5e7eb;

    text-align:center;

}


th{

    font-size:12px;

    color:var(--muted);

    text-transform:uppercase;

}


tbody tr:hover{

    background:#f8fafc;

}


/* =========================================================
   BOTONES
========================================================= */

.btn{

    background:
        linear-gradient(
            135deg,
            var(--primary),
            var(--primary-dark)
        );

    color:white;

    border:none;

    padding:10px 14px;

    border-radius:12px;

    cursor:pointer;

    text-decoration:none;

    font-weight:600;

    transition:.2s;

}


.btn:hover{

    transform:translateY(-1px);

    box-shadow:
        0 5px 12px rgba(37,99,235,.25);

}


.btn-danger{

    background:
        linear-gradient(
            135deg,
            var(--danger),
            var(--danger-dark)
        );

}


/* =========================================================
   MODALES
========================================================= */

.modal{

    display:none;

    position:fixed;

    inset:0;

    background:
        rgba(15,23,42,.65);

    justify-content:center;

    align-items:center;

    padding:20px;

    z-index:10000;

}


.modal-content{

    background:white;

    width:520px;

    max-width:100%;

    max-height:90vh;

    overflow-y:auto;

    border-radius:18px;

    padding:25px;

    box-shadow:
        0 20px 60px rgba(0,0,0,.30);

    animation:
        aparecerModal .2s ease;

}


@keyframes aparecerModal{

    from{

        opacity:0;

        transform:
            translateY(15px)
            scale(.98);

    }

    to{

        opacity:1;

        transform:
            translateY(0)
            scale(1);

    }

}


/* =========================================================
   FORMULARIOS
========================================================= */

input,
select,
textarea{

    width:100%;

    padding:11px;

    margin-bottom:12px;

    border:
        1px solid #e5e7eb;

    border-radius:10px;

    font-family:inherit;

    font-size:14px;

}


input:focus,
select:focus,
textarea:focus{

    outline:none;

    border-color:
        var(--primary);

    box-shadow:
        0 0 0 3px
        rgba(37,99,235,.10);

}


textarea{

    resize:vertical;

    min-height:110px;

}


/* =========================================================
   BUSCADOR DE TRABAJADORES
========================================================= */

#resultados{

    background:white;

    border-radius:10px;

    box-shadow:
        0 10px 25px rgba(0,0,0,.15);

    max-height:180px;

    overflow-y:auto;

    margin-top:-8px;

    margin-bottom:12px;

}


.item-resultado{

    padding:10px;

    cursor:pointer;

    border-bottom:
        1px solid #e5e7eb;

}


.item-resultado:hover{

    background:#f1f5f9;

}


/* =========================================================
   RANKING
========================================================= */

.ranking-card{

    background:#f8fafc;

    padding:18px;

    border-radius:14px;

    margin-bottom:15px;

    transition:.3s;

}


.ranking-card:hover{

    transform:
        translateY(-4px)
        scale(1.01);

    box-shadow:
        0 10px 25px rgba(0,0,0,.12);

}


.ranking-top{

    display:flex;

    justify-content:space-between;

    margin-bottom:10px;

}


.ranking-total{

    background:#2563eb;

    color:white;

    padding:6px 12px;

    border-radius:12px;

    font-weight:600;

}


.ranking-detalle span{

    background:#e0e7ff;

    padding:5px 10px;

    border-radius:10px;

    margin-right:6px;

    font-size:13px;

}


.ranking-card:nth-child(1){

    border-left:
        5px solid gold;

}


.ranking-card:nth-child(2){

    border-left:
        5px solid silver;

}


.ranking-card:nth-child(3){

    border-left:
        5px solid #cd7f32;

}


/* =========================================================
   FOOTER
========================================================= */

footer{

    margin-top:60px;

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


    .banner-text{

        left:20px;

        bottom:15px;

    }


    .banner-text h1{

        font-size:22px;

    }


    .banner-datetime{

        right:15px;

        bottom:15px;

        padding:8px 12px;

    }


    #banner-time{

        font-size:20px;

    }


    .modal-content{

        padding:20px;

    }

}

</style>

</head>


<body>


<?php include '../shared/menu.php'; ?>


<!-- =========================================================
     BANNER
========================================================= -->

<div class="banner-wrap">

    <img
        src="banner.jpg"
        class="banner"
        alt="Banner"
    >

    <div class="banner-overlay"></div>


    <div class="banner-text">

        <h1>Gestión de faltas</h1>

        <p>Registro de faltas de trabajadores</p>

    </div>


    <div class="banner-datetime">

        <div id="banner-date"></div>

        <div id="banner-time"></div>

    </div>

</div>


<!-- =========================================================
     LISTADO DE TRABAJADORES
========================================================= -->

<div class="card">

    <h2 style="margin-bottom:20px;">
        Trabajadores
    </h2>


    <!-- BUSCADOR -->

    <input
        type="text"
        id="filtroTabla"
        placeholder="Buscar por RUT o nombre..."
        style="
            max-width:420px;
            width:100%;
            padding:12px 14px;
            border-radius:12px;
            border:1px solid #cbd5e1;
            margin-bottom:20px;
        "
    >


    <!-- BOTONES -->

    <div
        style="
            display:flex;
            justify-content:flex-end;
            gap:10px;
            margin-bottom:20px;
            flex-wrap:wrap;
        "
    >

        <button
            class="btn"
            onclick="abrirModalConductor()"
        >
            ➕ Nuevo Trabajador
        </button>


        <button
            class="btn btn-danger"
            onclick="abrirModal()"
        >
            🚨 Registrar Falta
        </button>


        <button
            class="btn"
            style="background:#64748b;"
            onclick="abrirRanking()"
        >
            🏆 Ranking
        </button>

    </div>


    <!-- TABLA -->

    <table id="tablaConductores">

        <thead>

            <tr>

                <th>RUT</th>

                <th>Nombre</th>

                <th>Área</th>

                <th>Acción</th>

            </tr>

        </thead>


        <tbody>

        <?php while($c = $conductores->fetch_assoc()): ?>

            <tr class="fila-conductor">

                <td>
                    <?= htmlspecialchars($c['rut']) ?>
                </td>


                <td>
                    <?= htmlspecialchars($c['nombre']) ?>
                </td>


                <td>
                    <?= htmlspecialchars($c['area']) ?>
                </td>


                <td>

                    <a
                        href="historial.php?rut=<?= urlencode($c['rut']) ?>"
                        class="btn"
                    >
                        📄 Historial
                    </a>

                </td>

            </tr>

        <?php endwhile; ?>

        </tbody>

    </table>

</div>


<!-- =========================================================
     MODAL AVISO
========================================================= -->

<div
    class="modal"
    id="modalAvisoFalta"
>


    <div
        class="modal-content"
        style="
            max-width:600px;
            text-align:center;
        "
    >


        <!-- ICONO -->

        <div
            style="
                width:70px;
                height:70px;
                margin:0 auto 20px;
                border-radius:50%;
                background:#fef3c7;
                display:flex;
                align-items:center;
                justify-content:center;
                font-size:36px;
            "
        >
            ⚠️
        </div>


        <h2 style="margin-bottom:15px;">
            Antes de registrar la falta
        </h2>


        <p
            style="
                color:#475569;
                line-height:1.7;
                margin-bottom:20px;
            "
        >
            Para mantener un registro claro y útil,
            la falta debe ser
            <strong>
                detallada de la forma más precisa posible.
            </strong>
        </p>


        <!-- INFORMACION -->

        <div
            style="
                background:#f8fafc;
                border-left:4px solid #2563eb;
                border-radius:10px;
                padding:16px;
                text-align:left;
                margin-bottom:20px;
            "
        >

            <strong
                style="
                    display:block;
                    margin-bottom:10px;
                "
            >
                📝 ¿Qué debe incluir la falta?
            </strong>


            <ul
                style="
                    margin:0;
                    padding-left:20px;
                    color:#475569;
                    line-height:1.7;
                "
            >



                

                <li>
                    Indicar daños o situaciones asociadas,
                    si corresponde.
                </li>

                <li>
                    Si existe un daño con costo conocido,
                    ingresar el valor estimativo.
                </li>

                

                <li>
                    Si todavía no se conoce el valor,
                    la falta puede registrarse igualmente
                    y completarse posteriormente desde el historial.
                </li>

            </ul>

        </div>


        <!-- EJEMPLO -->

        <div
            style="
                background:#eff6ff;
                border-radius:12px;
                padding:15px;
                text-align:left;
                margin-bottom:25px;
            "
        >

            <strong
                style="
                    display:block;
                    margin-bottom:8px;
                "
            >
                💡 Ejemplo
            </strong>


            <div
                style="
                    color:#334155;
                    line-height:1.6;
                    font-size:14px;
                "
            >

                <strong>
                    Descripción:
                </strong>

                <br>

                Daño de 1 neumático delantero derecho
                producto de corte durante maniobra.

                <br><br>

                <strong>
                    Valor estimativo:
                </strong>

                $180.000

            </div>

        </div>


        <!-- BOTONES -->

        <div
            style="
                display:flex;
                justify-content:center;
                gap:10px;
            "
        >

            <button
                type="button"
                onclick="cerrarAvisoFalta()"
                class="btn"
                style="background:#64748b;"
            >
                Cancelar
            </button>


            <button
                type="button"
                onclick="continuarRegistroFalta()"
                class="btn"
            >
                ✓ Entendido, continuar
            </button>

        </div>


    </div>

</div>


<!-- =========================================================
     MODAL NUEVA FALTA
========================================================= -->

<div
    class="modal"
    id="modal"
>


    <div class="modal-content">


        <h2 style="margin-bottom:20px;">
             Nueva Falta
        </h2>


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- BUSCADOR TRABAJADOR -->

            <label
                style="
                    font-size:13px;
                    font-weight:600;
                    color:#475569;
                "
            >
                Trabajador
            </label>


            <input
                type="text"
                id="busqueda"
                placeholder="Escriba RUT o nombre del trabajador..."
                autocomplete="off"
            >


            <div id="resultados"></div>


            <!-- RUT -->

            <input
                type="hidden"
                name="rut"
                id="rut"
            >


            <!-- NOMBRE -->

            <label
                style="
                    font-size:13px;
                    font-weight:600;
                    color:#475569;
                "
            >
                Nombre
            </label>


            <input
                type="text"
                name="nombre"
                id="nombre"
                placeholder="Nombre"
                readonly
            >


            <!-- AREA -->

            <label
                style="
                    font-size:13px;
                    font-weight:600;
                    color:#475569;
                "
            >
                Área
            </label>


            <input
                type="text"
                name="area"
                id="area"
                placeholder="Área"
                readonly
            >


            <!-- DESCRIPCION -->

            <label
                style="
                    font-size:13px;
                    font-weight:600;
                    color:#475569;
                "
            >
                Descripción de lo sucedido
            </label>


            <textarea
                name="observacion"
                placeholder="Describa detalladamente lo sucedido..."
                required
            ></textarea>


            <div
                style="
                    margin-top:-5px;
                    margin-bottom:18px;
                    font-size:12px;
                    color:#64748b;
                "
            >
              
            </div>


            <!-- VALOR ESTIMATIVO -->

            <label
                style="
                    font-size:13px;
                    font-weight:600;
                    color:#475569;
                "
            >

                Valor estimativo

                <span
                    style="
                        font-weight:400;
                        color:#94a3b8;
                    "
                >
                    (opcional)
                </span>

            </label>


            <div
                style="
                    position:relative;
                    margin-bottom:8px;
                "
            >

                <span
                    style="
                        position:absolute;
                        left:12px;
                        top:11px;
                        color:#64748b;
                        font-weight:600;
                    "
                >
                    $
                </span>


                <input
                    type="number"
                    name="valor_estimado"
                    min="0"
                    step="100"
                    placeholder="Ingrese valor estimativo"
                    style="padding-left:30px;"
                >

            </div>


            <div
                style="
                    margin-top:-3px;
                    margin-bottom:18px;
                    font-size:12px;
                    color:#64748b;
                "
            >
                 
                Si todavía no conoce el valor,
                puede dejarlo vacío y agregarlo posteriormente
                desde el historial.
            </div>


            <!-- FECHA -->

            <label
                style="
                    font-size:13px;
                    font-weight:600;
                    color:#475569;
                "
            >
                Fecha
            </label>


            <input
                type="date"
                name="fecha"
                value="<?= date('Y-m-d') ?>"
                required
            >


            <!-- ARCHIVO -->

            <label
                style="
                    font-size:13px;
                    font-weight:600;
                    color:#475569;
                "
            >

                Archivo adjunto

                <span
                    style="
                        font-weight:400;
                        color:#94a3b8;
                    "
                >
                    (opcional)
                </span>

            </label>


            <input
                type="file"
                name="imagen"
                accept="image/*,.pdf"
            >


            <div
                style="
                    margin-top:-3px;
                    margin-bottom:18px;
                    font-size:12px;
                    color:#64748b;
                "
            >
                 Puede adjuntar una imagen o documento PDF
                relacionado con la falta.
            </div>


            <!-- BOTONES -->

            <div
                style="
                    display:flex;
                    justify-content:flex-end;
                    gap:10px;
                    margin-top:10px;
                "
            >

                <button
                    type="submit"
                    name="guardar"
                    class="btn"
                >
                     Guardar Falta
                </button>


                <button
                    type="button"
                    onclick="cerrarModal()"
                    class="btn"
                    style="background:#64748b;"
                >
                    Cancelar
                </button>

            </div>


        </form>

    </div>

</div>


<!-- =========================================================
     MODAL NUEVO TRABAJADOR
========================================================= -->

<div
    class="modal"
    id="modalConductor"
>


    <div class="modal-content">


        <h2 style="margin-bottom:20px;">
            ➕ Nuevo Trabajador
        </h2>


        <form method="POST">


            <label
                style="
                    font-size:13px;
                    font-weight:600;
                    color:#475569;
                "
            >
                RUT
            </label>


            <input
                type="text"
                name="rut_conductor"
                placeholder="Ej: 12.345.678-9"
                required
            >


            <label
                style="
                    font-size:13px;
                    font-weight:600;
                    color:#475569;
                "
            >
                Nombre
            </label>


            <input
                type="text"
                name="nombre_conductor"
                placeholder="Nombre completo"
                required
            >


            <label
                style="
                    font-size:13px;
                    font-weight:600;
                    color:#475569;
                "
            >
                Área
            </label>


            <input
                type="text"
                name="area_conductor"
                placeholder="Área"
                required
            >


            <div
                style="
                    display:flex;
                    justify-content:flex-end;
                    gap:10px;
                "
            >

                <button
                    type="submit"
                    name="guardar_conductor"
                    class="btn"
                >
                    💾 Guardar
                </button>


                <button
                    type="button"
                    onclick="cerrarModalConductor()"
                    class="btn"
                    style="background:#64748b;"
                >
                    Cancelar
                </button>

            </div>


        </form>

    </div>

</div>


<!-- =========================================================
     MODAL RANKING
========================================================= -->

<div
    class="modal"
    id="modalRanking"
>


    <div
        class="modal-content"
        style="
            width:900px;
            max-width:95%;
            max-height:85vh;
            overflow-y:auto;
        "
    >


        <div
            style="
                display:flex;
                justify-content:space-between;
                align-items:center;
                margin-bottom:20px;
            "
        >

            <h2>
                🏆 Ranking Trabajadores
            </h2>


            <span
                onclick="cerrarRanking()"
                style="
                    font-size:28px;
                    cursor:pointer;
                "
            >
                &times;
            </span>

        </div>


        <div style="margin-bottom:20px;">

            <label>
                <strong>Año:</strong>
            </label>


            <select
                id="filtroAnio"
                style="
                    padding:8px;
                    border-radius:10px;
                    border:1px solid #e5e7eb;
                    width:auto;
                    min-width:100px;
                "
            >

                <?php
                for(
                    $a = date('Y');
                    $a >= 2020;
                    $a--
                ):
                ?>

                    <option value="<?= $a ?>">
                        <?= $a ?>
                    </option>

                <?php endfor; ?>

            </select>

        </div>


        <div id="rankingContenido"></div>


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

    © <?= date('Y') ?>

</footer>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>


/* =========================================================
   MODAL NUEVO TRABAJADOR
========================================================= */

function abrirModalConductor(){

    document
        .getElementById('modalConductor')
        .style.display = 'flex';

}


function cerrarModalConductor(){

    document
        .getElementById('modalConductor')
        .style.display = 'none';

}


/* =========================================================
   MODAL AVISO FALTA
========================================================= */

function abrirModal(){

    document
        .getElementById('modalAvisoFalta')
        .style.display = 'flex';

}


function cerrarAvisoFalta(){

    document
        .getElementById('modalAvisoFalta')
        .style.display = 'none';

}


/*
 * IMPORTANTE:
 * Primero cerramos el aviso.
 * Luego abrimos el formulario.
 *
 * Se utiliza un pequeño setTimeout para asegurar
 * que ambos cambios de display se ejecuten correctamente.
 */

function continuarRegistroFalta(){

    const aviso =
        document.getElementById('modalAvisoFalta');

    const formulario =
        document.getElementById('modal');


    aviso.style.display = 'none';


    setTimeout(function(){

        formulario.style.display = 'flex';

    }, 50);

}


function cerrarModal(){

    document
        .getElementById('modal')
        .style.display = 'none';

}


/* =========================================================
   BUSCADOR DE TRABAJADORES
========================================================= */

const busqueda =
    document.getElementById('busqueda');

const resultados =
    document.getElementById('resultados');


busqueda.addEventListener(
    'keyup',
    function(){

        const valor =
            busqueda.value.trim();


        if(valor.length < 2){

            resultados.innerHTML = '';

            return;

        }


        fetch(
            `buscar_conductor.php?q=${encodeURIComponent(valor)}`
        )

        .then(
            res => res.text()
        )

        .then(
            html => {

                resultados.innerHTML = html;

            }
        )

        .catch(
            error => {

                console.error(
                    'Error buscando trabajador:',
                    error
                );

            }
        );

    }
);


/* =========================================================
   SELECCIONAR RESULTADO
========================================================= */

resultados.addEventListener(
    'click',
    function(e){

        const item =
            e.target.closest('.item-resultado');


        if(!item){

            return;

        }


        document.getElementById('rut').value =
            item.dataset.rut;


        document.getElementById('nombre').value =
            item.dataset.nombre;


        document.getElementById('area').value =
            item.dataset.area;


        busqueda.value =
            item.dataset.nombre;


        resultados.innerHTML = '';

    }
);


/* =========================================================
   RELOJ
========================================================= */

function actualizarBannerHora(){

    const now =
        new Date();


    document.getElementById('banner-date').innerText =

        now.toLocaleDateString(
            'es-CL',
            {
                weekday:'long',
                day:'2-digit',
                month:'long',
                year:'numeric'
            }
        );


    document.getElementById('banner-time').innerText =

        now.toLocaleTimeString(
            'es-CL',
            {
                hour:'2-digit',
                minute:'2-digit',
                second:'2-digit'
            }
        );

}


setInterval(
    actualizarBannerHora,
    1000
);


actualizarBannerHora();


/* =========================================================
   FILTRO TABLA
========================================================= */

const filtro =
    document.getElementById('filtroTabla');


filtro.addEventListener(
    'keyup',
    function(){

        const texto =
            filtro.value.toLowerCase();


        const filas =
            document.querySelectorAll(
                '.fila-conductor'
            );


        filas.forEach(
            function(fila){

                const rut =
                    fila.children[0]
                        .innerText
                        .toLowerCase();


                const nombre =
                    fila.children[1]
                        .innerText
                        .toLowerCase();


                if(
                    rut.includes(texto) ||
                    nombre.includes(texto)
                ){

                    fila.style.display = '';

                }else{

                    fila.style.display = 'none';

                }

            }
        );

    }
);


/* =========================================================
   RANKING
========================================================= */

function abrirRanking(){

    document
        .getElementById('modalRanking')
        .style.display = 'flex';


    cargarRanking();

}


function cerrarRanking(){

    document
        .getElementById('modalRanking')
        .style.display = 'none';

}


function cargarRanking(){

    const anio =
        document.getElementById('filtroAnio').value;


    fetch(
        `ranking_errores.php?anio=${anio}`
    )

    .then(
        res => res.text()
    )

    .then(
        html => {

            document
                .getElementById('rankingContenido')
                .innerHTML = html;

        }
    )

    .catch(
        error => {

            console.error(
                'Error cargando ranking:',
                error
            );

        }
    );

}


document
    .getElementById('filtroAnio')
    .addEventListener(
        'change',
        cargarRanking
    );


/* =========================================================
   CERRAR MODALES AL HACER CLICK FUERA
========================================================= */

window.addEventListener(
    'click',
    function(e){

        const modalAviso =
            document.getElementById('modalAvisoFalta');

        const modalFalta =
            document.getElementById('modal');

        const modalConductor =
            document.getElementById('modalConductor');

        const modalRanking =
            document.getElementById('modalRanking');


        if(e.target === modalAviso){

            modalAviso.style.display = 'none';

        }


        if(e.target === modalFalta){

            modalFalta.style.display = 'none';

        }


        if(e.target === modalConductor){

            modalConductor.style.display = 'none';

        }


        if(e.target === modalRanking){

            modalRanking.style.display = 'none';

        }

    }
);

</script>


</body>

</html>
```
