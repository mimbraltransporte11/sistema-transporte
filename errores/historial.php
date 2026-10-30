<?php

include 'db.php';

date_default_timezone_set('America/Santiago');


/* =========================================================
   ACTUALIZAR VALOR ESTIMADO
========================================================= */

/* =========================================================
   ACTUALIZAR VALOR ESTIMADO
========================================================= */

if(isset($_POST['guardar_valor'])){

    $id = intval($_POST['id_infraccion'] ?? 0);

    $valor = trim($_POST['valor_estimado'] ?? '');

    $rut = $_POST['rut'] ?? '';


    /*
     * Si el campo queda vacío,
     * guardamos NULL.
     */
    if($valor === ''){

        $stmt = $conn->prepare("
            UPDATE infracciones
            SET valor_estimado = NULL
            WHERE id = ?
        ");

        $stmt->bind_param("i", $id);

    }else{

        /*
         * Convertir formato chileno:
         *
         * 300.000     -> 300000
         * $300.000    -> 300000
         * 1.500.000   -> 1500000
         */
        $valor = preg_replace('/[^0-9]/', '', $valor);

        $valor = intval($valor);

        /*
         * Evitar valores negativos
         */
        if($valor < 0){
            $valor = 0;
        }

        $stmt = $conn->prepare("
            UPDATE infracciones
            SET valor_estimado = ?
            WHERE id = ?
        ");

        $stmt->bind_param("ii", $valor, $id);
    }


    /*
     * Ejecutar actualización
     */
    if($stmt){

        $stmt->execute();

        $stmt->close();

    }


    /*
     * Volver al historial
     */
    header(
        "Location: historial.php?rut=" . urlencode($rut)
    );

    exit;
}


/* =========================================================
   SUBIR / REEMPLAZAR ARCHIVO
========================================================= */

if(isset($_POST['subir_imagen'])){

    $id = intval($_POST['id_infraccion']);

    $rut = $_POST['rut'] ?? '';

    if(
        isset($_FILES['nueva_imagen']) &&
        $_FILES['nueva_imagen']['error'] === 0
    ){

        $permitidos = [
            'jpg',
            'jpeg',
            'png',
            'webp',
            'pdf'
        ];

        $ext = strtolower(
            pathinfo(
                $_FILES['nueva_imagen']['name'],
                PATHINFO_EXTENSION
            )
        );

        /*
         * Verificar extensión
         */
        if(in_array($ext, $permitidos)){

            /*
             * Máximo 5 MB
             */
            if($_FILES['nueva_imagen']['size'] <= 5 * 1024 * 1024){

                $nombreArchivo =
                    "infraccion_" .
                    $id .
                    "_" .
                    time() .
                    "." .
                    $ext;

                $rutaDestino =
                    "imagenes/" . $nombreArchivo;


                if(
                    move_uploaded_file(
                        $_FILES['nueva_imagen']['tmp_name'],
                        $rutaDestino
                    )
                ){

                    /*
                     * Obtener archivo anterior
                     */
                    $resultadoAnterior = $conn->query("
                        SELECT imagen
                        FROM infracciones
                        WHERE id = $id
                    ");

                    $archivoAnterior = '';

                    if($resultadoAnterior && $resultadoAnterior->num_rows > 0){

                        $filaAnterior =
                            $resultadoAnterior->fetch_assoc();

                        $archivoAnterior =
                            $filaAnterior['imagen'];
                    }


                    /*
                     * Eliminar archivo anterior
                     */
                    if(
                        !empty($archivoAnterior) &&
                        file_exists("imagenes/" . $archivoAnterior)
                    ){

                        unlink(
                            "imagenes/" . $archivoAnterior
                        );
                    }


                    /*
                     * Actualizar nombre del archivo
                     */
                    $stmt = $conn->prepare("
                        UPDATE infracciones
                        SET imagen = ?
                        WHERE id = ?
                    ");

                    $stmt->bind_param(
                        "si",
                        $nombreArchivo,
                        $id
                    );

                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }

    header(
        "Location: historial.php?rut=" . urlencode($rut)
    );

    exit;
}


/* =========================================================
   DATOS DEL HISTORIAL
========================================================= */

$rut = $_GET['rut'] ?? '';

$desde = $_GET['desde'] ?? '';

$hasta = $_GET['hasta'] ?? '';


/* =========================================================
   FILTRO POR FECHA
========================================================= */

$whereFecha = '';

if($desde && $hasta){

    $desdeSQL = $conn->real_escape_string($desde);
    $hastaSQL = $conn->real_escape_string($hasta);

    $whereFecha = "
        AND fecha BETWEEN '$desdeSQL' AND '$hastaSQL'
    ";
}


/* =========================================================
   OBTENER CONDUCTOR
========================================================= */

$rutSQL = $conn->real_escape_string($rut);

$cond = $conn->query("
    SELECT *
    FROM conductores
    WHERE rut='$rutSQL'
");


if(!$cond || $cond->num_rows === 0){

    header('Location: index.php');
    exit;
}


$conductor = $cond->fetch_assoc();


/* =========================================================
   OBTENER HISTORIAL
========================================================= */

$historial = $conn->query("
    SELECT *
    FROM infracciones
    WHERE rut='$rutSQL'
    $whereFecha
    ORDER BY fecha DESC
");


/* =========================================================
   TOTAL GENERAL
========================================================= */

$total = $conn->query("
    SELECT COUNT(*) total
    FROM infracciones
    WHERE rut='$rutSQL'
")->fetch_assoc()['total'];


/* =========================================================
   TOTAL DEL RANGO
========================================================= */

$rangoTotal = $conn->query("
    SELECT COUNT(*) cantidad
    FROM infracciones
    WHERE rut='$rutSQL'
    $whereFecha
")->fetch_assoc()['cantidad'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Historial de Infracciones</title>

<link rel="icon" type="image/png" href="./favicon.png?v=2">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root{
  --primary:#2563eb;
  --primary-dark:#1e40af;
  --danger:#dc2626;
  --bg1:#f1f5f9;
  --bg2:#e0e7ff;
  --card:#ffffff;
  --text:#0f172a;
  --muted:#64748b;
}
*{box-sizing:border-box;margin:0;padding:0}

body{
  font-family:Inter,sans-serif;
  background:linear-gradient(180deg,var(--bg1),var(--bg2));
  padding:30px;
  color:var(--text);
}

/* ===== Banner ===== */
.banner-wrap{position:relative;margin-bottom:40px}
.banner{
  width:100%;
  max-height:220px;
  object-fit:cover;
  border-radius:20px;
}
.banner-overlay{
  position:absolute;inset:0;
  background:linear-gradient(90deg,rgba(15,23,42,.75),transparent);
  border-radius:20px;
}
.banner-text{
  position:absolute;
  bottom:20px;
  left:30px;
  color:white;
  font-size:28px;
  font-weight:700;
}
.banner-datetime{
  position:absolute;
  right:25px;
  bottom:20px;
  background:rgba(255,255,255,0.18);
  backdrop-filter: blur(10px);
  padding:12px 18px;
  border-radius:16px;
  color:white;
  text-align:right;
  box-shadow:0 10px 25px rgba(0,0,0,.35);
}
#banner-date{font-size:12px}
#banner-time{font-size:26px;font-weight:800}

/* ===== Layout ===== */
.dashboard{
  display:grid;
  grid-template-columns:1fr 3fr;
  gap:30px;
}

/* Perfil */
.profile-card{
  background:var(--card);
  padding:25px;
  border-radius:18px;
  box-shadow:0 10px 30px rgba(0,0,0,.08);
}

.profile-info{
  margin-bottom:20px;
  line-height:1.6;
}

.stat{
  background:#f8fafc;
  padding:20px;
  border-radius:14px;
  text-align:center;
  margin-bottom:15px;
  transition:.3s;
}
.stat:hover{
  transform:translateY(-5px);
  box-shadow:0 6px 18px rgba(0,0,0,.08);
}
.stat h3{
  font-size:32px;
  color:var(--danger);
}

/* Main */
.main-content{
  display:flex;
  flex-direction:column;
  gap:25px;
}

.filter-card{
  background:var(--card);
  padding:20px;
  border-radius:18px;
  display:flex;
  align-items:end;
  gap:15px;
  box-shadow:0 10px 30px rgba(0,0,0,.08);
}

.filter-card input{
  padding:10px;
  border-radius:10px;
  border:1px solid #e5e7eb;
}

.btn{
  background:var(--primary);
  color:white;
  border:none;
  padding:10px 18px;
  border-radius:12px;
  font-weight:600;
  cursor:pointer;
  text-decoration:none;
  transition:.3s;
}
.btn:hover{
  background:var(--primary-dark);
}

/* Tabla */
.table-card{
  background:var(--card);
  padding:20px;
  border-radius:18px;
  box-shadow:0 10px 30px rgba(0,0,0,.08);
}

table{
width:100%;
border-collapse:collapse;
min-width:900px;
}

th,td{
padding:15px;
text-align:center;
vertical-align:middle;
}

td{
font-size:14px;
line-height:1.4;
}

/* columnas */

.col-fecha{
white-space:nowrap;
font-weight:500;
}

.col-obs{
max-width:450px;
word-wrap:break-word;
}

.col-img{
white-space:nowrap;
}

th{
  font-size:12px;
  text-transform:uppercase;
  color:var(--muted);
  border-bottom:1px solid #e5e7eb;
}

tbody tr:hover{
  background:#f8fafc;
}

td img{
  width:70px;
  border-radius:8px;
}

/* Botón volver */
.back-container{
  margin-top:20px;
  display:flex;
  justify-content:flex-end;
}

/* Footer */
footer{
  margin-top:60px;
  padding:30px;
  border-radius:20px;
  background:linear-gradient(135deg,#1e3a8a,#2563eb);
  color:white;
  text-align:center;
  font-weight:500;
}

@media(max-width:1000px){
  .dashboard{grid-template-columns:1fr;}
}

.archivo-box{
cursor:pointer;
display:flex;
justify-content:center;
align-items:center;
height:60px;
border-radius:10px;
background:#f1f5f9;
transition:.2s;
font-weight:600;
}

.archivo-box:hover{
background:#e2e8f0;
}

.archivo-box img{
width:60px;
border-radius:6px;
}

/* ===== VISOR PRO ===== */

.visor{
display:none;
position:fixed;
inset:0;
background:rgba(0,0,0,0.92);
justify-content:center;
align-items:center;
z-index:9999;
}

.visor-contenido{
position:relative;
width:95%;
height:95%;
background:#111;
border-radius:14px;
display:flex;
flex-direction:column;
overflow:hidden;
}

.visor-barra{
background:#1e293b;
color:white;
padding:10px 15px;
display:flex;
justify-content:space-between;
align-items:center;
}

.visor-controles button{
background:#2563eb;
border:none;
color:white;
padding:6px 10px;
margin-left:5px;
border-radius:6px;
cursor:pointer;
font-size:14px;
}

.visor-controles button:hover{
background:#1e40af;
}

#contenidoVisor{
flex:1;
display:flex;
justify-content:center;
align-items:center;
overflow:auto;
background:#0f172a;
}

#contenidoVisor img{
max-width:100%;
max-height:100%;
transition:transform .2s;
}

#contenidoVisor iframe{
width:100%;
height:100%;
border:none;
background:white;
}

#cerrar{
position:absolute;
top:10px;
right:15px;
font-size:24px;
cursor:pointer;
}

.thumb{
width:70px;
height:70px;
object-fit:cover;
border-radius:10px;
cursor:pointer;
transition:.3s
}

.thumb:hover{
transform:scale(1.1);
box-shadow:0 8px 18px rgba(0,0,0,.25)
}

.btn-mini{
background:#2563eb;
color:white;
border:none;
padding:6px 10px;
border-radius:8px;
font-size:12px;
cursor:pointer;
margin-top:5px
}

.btn-mini:hover{
background:#1e40af
}

.pdf-icon{
font-size:32px;
color:#dc2626;
}

.col-fecha{width:120px; white-space:nowrap; font-weight:500;}
.col-obs{width:50%; max-width:450px; word-wrap:break-word;}
.col-img{width:120px; white-space:nowrap;}


/* ===== MODAL VALOR ===== */

.modal-valor{
  display:none;
  position:fixed;
  inset:0;
  background:rgba(15,23,42,.60);
  justify-content:center;
  align-items:center;
  z-index:10000;
  padding:20px;
}

.modal-valor-content{
  background:white;
  width:450px;
  max-width:100%;
  border-radius:18px;
  padding:25px;
  box-shadow:0 20px 50px rgba(0,0,0,.25);
}

.col-valor{
  width:160px;
  min-width:160px;
  text-align:center;
}

</style>
</head>

<body>

<?php include '../shared/menu.php'; ?>

<div class="banner-wrap">
  <img src="banner.jpg" class="banner">
  <div class="banner-overlay"></div>
  <div class="banner-text">Historial de faltas</div>
  <div class="banner-datetime">
    <div id="banner-date"></div>
    <div id="banner-time"></div>
  </div>
</div>

<div class="dashboard">

  <div class="profile-card">
    <div class="profile-info">
      <strong>RUT:</strong> <?= $conductor['rut'] ?><br>
      <strong>Nombre:</strong> <?= $conductor['nombre'] ?><br>
      <strong>Área:</strong> <?= $conductor['area'] ?>
    </div>

    <div class="stat">
      <h3><?= $total ?></h3>
      <p>Total faltas</p>
    </div>

    <?php if($desde && $hasta): ?>
    <div class="stat">
      <h3><?= $rangoTotal ?></h3>
      <p>Entre <?= date('d-m-y', strtotime($desde)) ?> y <?= date('d-m-y', strtotime($hasta)) ?></p>
    </div>
    <?php endif; ?>
  </div>

  <div class="main-content">

    <form method="GET" class="filter-card">
      <input type="hidden" name="rut" value="<?= $rut ?>">
      <div>
        <label>Desde</label><br>
        <input type="date" name="desde" value="<?= $desde ?>">
      </div>
      <div>
        <label>Hasta</label><br>
        <input type="date" name="hasta" value="<?= $hasta ?>">
      </div>
      <button class="btn">Filtrar</button>
    </form>
<a href="exportar_historial.php?rut=<?= $rut ?>&desde=<?= $desde ?>&hasta=<?= $hasta ?>" 
   class="btn" target="_blank">
   📄 Exportar PDF
</a>
    <div class="table-card">
      <table>
        <thead>
        <tr>
        <th>Fecha</th>
        <th>Observación</th>
        <th>Valor estimativo</th>
        <th>Archivo</th>
        </tr>
      </thead>
        <tbody>

        <?php if($historial->num_rows > 0): ?>
          <?php while($h = $historial->fetch_assoc()): ?>
         <tr>
  <td class="col-fecha"><?= date('d-m-y', strtotime($h['fecha'])) ?></td>
  <td class="col-obs"><?= $h['observacion'] ?></td>
<td class="col-valor">

  <?php if($h['valor_estimado'] !== null && $h['valor_estimado'] !== ''): ?>

    <div style="
      font-weight:700;
      color:#166534;
      background:#dcfce7;
      padding:8px 10px;
      border-radius:10px;
      margin-bottom:6px;
    ">
      💰 $<?= number_format($h['valor_estimado'], 0, ',', '.') ?>
    </div>

  <?php else: ?>

    <div style="
      font-size:12px;
      color:#92400e;
      background:#fef3c7;
      padding:8px 10px;
      border-radius:10px;
      margin-bottom:6px;
    ">
      ⚠️ Sin valor
    </div>

  <?php endif; ?>


  <button
    type="button"
    class="btn-mini"
    onclick="abrirValor(
      <?= $h['id'] ?>,
      '<?= $h['valor_estimado'] !== null ? $h['valor_estimado'] : '' ?>'
    )"
  >
    
    <?= ($h['valor_estimado'] !== null && $h['valor_estimado'] !== '')
        ? 'Modificar'
        : 'Agregar valor' ?>
  </button>

</td>

  <td class="col-img">
    <?php if(!empty($h['imagen'])): ?>
      <?php $ext = strtolower(pathinfo($h['imagen'], PATHINFO_EXTENSION)); ?>
      <a href="#" onclick="abrirVisor('imagenes/<?= $h['imagen'] ?>');return false;">
        <?php if($ext == 'pdf'): ?>
          <div class="pdf-icon">📄</div>
        <?php else: ?>
          <img src="imagenes/<?= $h['imagen'] ?>" class="thumb">
        <?php endif; ?>
      </a>
    <?php else: ?>
      <span style="color:#64748b;">Sin archivo</span>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" style="margin-top:6px;">
      <input type="hidden" name="id_infraccion" value="<?= $h['id'] ?>">
      <input type="file" name="nueva_imagen" accept="image/*,.pdf">
      <button type="submit" name="subir_imagen" class="btn-mini">
        <?= !empty($h['imagen']) ? 'Reemplazar' : 'Subir' ?>
      </button>
    </form>
  </td>
</tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="3">Sin faltas registradas</td></tr>
        <?php endif; ?>

        </tbody>
      </table>

      <div class="back-container">
        <a href="index.php" class="btn">⬅ Volver</a>
      </div>

    </div>

  </div>
</div>

<footer>
<strong>Transportes Mimbral LTDA</strong><br>
Sistema de faltas · <?= date('Y') ?>
</footer>

<script>
function actualizarBannerHora(){
  const now = new Date();
  document.getElementById('banner-date').innerText =
    now.toLocaleDateString('es-CL',{
      weekday:'long',
      day:'2-digit',
      month:'long',
      year:'numeric'
    });
  document.getElementById('banner-time').innerText =
    now.toLocaleTimeString('es-CL',{
      hour:'2-digit',
      minute:'2-digit',
      second:'2-digit'
    });
}
setInterval(actualizarBannerHora,1000);
actualizarBannerHora();


let zoomNivel = 1;

function abrirVisor(archivo){

let ext = archivo.split('.').pop().toLowerCase();

let visor = document.getElementById("visor");
let contenido = document.getElementById("contenidoVisor");

visor.style.display = "flex";

if(ext === "pdf"){

contenido.innerHTML =
'<iframe src="'+archivo+'#zoom=page-width"></iframe>';

}else{

contenido.innerHTML =
'<img id="imagenVisor" src="'+archivo+'">';

zoomNivel = 1;

}

}

function cerrarVisor(){

document.getElementById("visor").style.display="none";
document.getElementById("contenidoVisor").innerHTML="";

}

function zoomIn(){

let img = document.getElementById("imagenVisor");

if(img){

zoomNivel += 0.2;
img.style.transform = "scale("+zoomNivel+")";

}

}

function zoomOut(){

let img = document.getElementById("imagenVisor");

if(img){

zoomNivel -= 0.2;

if(zoomNivel < 0.5) zoomNivel = 0.5;

img.style.transform = "scale("+zoomNivel+")";

}

}


function abrirValor(id, valor){

    document.getElementById('valorId').value = id;

    if(valor !== ''){

        // Convertir el valor guardado a formato chileno
        document.getElementById('valorInput').value =
            new Intl.NumberFormat('es-CL').format(
                Number(valor)
            );

    }else{

        document.getElementById('valorInput').value = '';

    }

    document.getElementById('modalValor').style.display = 'flex';

    setTimeout(() => {

        document.getElementById('valorInput').focus();

    }, 100);
}


function cerrarValor(){

    document.getElementById('modalValor').style.display = 'none';

}


function cerrarValor(){

    document.getElementById('modalValor').style.display = 'none';

}

function formatearPesoChileno(valor){

    // Eliminar todo lo que no sea número
    valor = valor.replace(/\D/g, '');

    if(valor === ''){
        return '';
    }

    // Formato chileno: 300000 -> 300.000
    return new Intl.NumberFormat('es-CL').format(valor);
}

const valorInput = document.getElementById('valorInput');

valorInput.addEventListener('input', function(){

    this.value = formatearPesoChileno(this.value);

});

</script>

<!-- ===== MODAL VALOR ESTIMADO ===== -->

<div id="modalValor" class="modal-valor">

  <div class="modal-valor-content">

    <div style="
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:20px;
    ">

      <h2 style="margin:0;">
         Valor estimativo
      </h2>

      <span
        onclick="cerrarValor()"
        style="
          font-size:28px;
          cursor:pointer;
          color:#64748b;
        "
      >
        &times;
      </span>

    </div>

    <p style="
      color:#64748b;
      font-size:14px;
      line-height:1.5;
      margin-bottom:20px;
    ">
      Ingrese el valor estimativo asociado a esta falta.
      Si no corresponde o desea eliminar el valor existente,
      puede dejar el campo vacío.
    </p>

    <form method="POST">

    <input
        type="hidden"
        name="id_infraccion"
        id="valorId"
    >

    <input
        type="hidden"
        name="rut"
        value="<?= htmlspecialchars($rut) ?>"
    >

      <label style="
        display:block;
        font-size:13px;
        font-weight:600;
        margin-bottom:7px;
      ">
        Valor estimativo
      </label>

      <div style="position:relative;">

        <span style="
    position:absolute;
    left:13px;
    top:12px;
    font-weight:600;
    color:#64748b;
">
    $
</span>

       <input
    type="text"
    name="valor_estimado"
    id="valorInput"
    inputmode="numeric"
    autocomplete="off"
    placeholder="Ej: 300.000"
    style="
        width:100%;
        padding:12px 15px 12px 32px;
        border:1px solid #e5e7eb;
        border-radius:10px;
        font-size:16px;
        font-weight:600;
    "
>

      </div>

      <div style="
        display:flex;
        justify-content:flex-end;
        gap:10px;
        margin-top:15px;
      ">

        <button
          type="button"
          onclick="cerrarValor()"
          class="btn"
          style="background:#64748b;"
        >
          Cancelar
        </button>

        <button
          type="submit"
          name="guardar_valor"
          class="btn"
        >
           Guardar valor
        </button>

      </div>

    </form>

  </div>

</div>

<div id="visor" class="visor">

<div class="visor-contenido">

<div class="visor-barra">

<div>Visor de Documento</div>

<div class="visor-controles">

<button onclick="zoomOut()">➖</button>
<button onclick="zoomIn()">➕</button>
<button onclick="cerrarVisor()">✖</button>

</div>

</div>

<div id="contenidoVisor"></div>

</div>

</div>

</body>
</html>