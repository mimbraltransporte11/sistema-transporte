<?php
include 'db.php';

/* ===== SUBIR IMAGEN ===== */
if(isset($_POST['subir_imagen'])){

    $id = intval($_POST['id_infraccion']);

    if(isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] == 0){

        $permitidos = ['jpg','jpeg','png','webp','pdf'];
        $ext = strtolower(pathinfo($_FILES['nueva_imagen']['name'], PATHINFO_EXTENSION));

        if(in_array($ext,$permitidos)){

            if($_FILES['nueva_imagen']['size'] <= 5*1024*1024){

                $nombreArchivo = "inf_" . $id . "_" . time() . "." . $ext;
                $rutaDestino = "uploads/" . $nombreArchivo;

                if(move_uploaded_file($_FILES['nueva_imagen']['tmp_name'], $rutaDestino)){

                    // Eliminar imagen anterior si existe
                    $imgVieja = $conn->query("SELECT imagen FROM infraccion WHERE id=$id")->fetch_assoc()['imagen'];
                    if($imgVieja && file_exists("uploads/".$imgVieja)){
                        unlink("uploads/".$imgVieja);
                    }

                    $conn->query("UPDATE infraccion SET imagen='$nombreArchivo' WHERE id=$id");

                    header("Location: historial.php?rut=".$_GET['rut']);
                    exit;
                }
            }
        }
    }
}

/* ===== VALIDACIÓN RUT ===== */
if (!isset($_GET['rut'])) {
    header('Location: index.php');
    exit;
}

$rut = $_GET['rut'];
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

$whereFecha = '';
if ($desde && $hasta) {
    $whereFecha = "AND fecha BETWEEN '$desde' AND '$hasta'";
}

/* ===== CONDUCTOR ===== */
$cond = $conn->query("SELECT * FROM conductor WHERE rut='$rut'");
if ($cond->num_rows === 0) {
    header('Location: index.php');
    exit;
}
$conductor = $cond->fetch_assoc();

/* ===== HISTORIAL ===== */
$historial = $conn->query("
    SELECT * 
    FROM infraccion 
    WHERE rut='$rut' $whereFecha
    ORDER BY fecha DESC
");

$total = $conn->query("
    SELECT COUNT(*) total 
    FROM infraccion 
    WHERE rut='$rut'
")->fetch_assoc()['total'];

$rangoTotal = $conn->query("
    SELECT COUNT(*) cantidad 
    FROM infraccion 
    WHERE rut='$rut' $whereFecha
")->fetch_assoc()['cantidad'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Historial de Amonestaciones</title>
<link rel="icon" type="image/png" href="favicon.png">
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

/* BANNER */
.banner-wrap{position:relative;margin-bottom:40px}
.banner{width:100%;max-height:220px;object-fit:cover;border-radius:20px}
.banner-overlay{position:absolute;inset:0;background:linear-gradient(90deg,rgba(15,23,42,.75),transparent);border-radius:20px}
.banner-text{position:absolute;bottom:20px;left:30px;color:white;font-size:28px;font-weight:700}
.banner-datetime{position:absolute;right:25px;bottom:20px;background:rgba(255,255,255,0.18);backdrop-filter: blur(10px);padding:12px 18px;border-radius:16px;color:white;text-align:right;box-shadow:0 10px 25px rgba(0,0,0,.35)}
#banner-date{font-size:12px}
#banner-time{font-size:26px;font-weight:800}

/* GRID */
.dashboard{display:grid;grid-template-columns:1fr 3fr;gap:30px}

/* CARD PERFIL */
.profile-card{background:var(--card);padding:25px;border-radius:18px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
.profile-info{margin-bottom:20px;line-height:1.6}

/* ESTADISTICAS */
.stat{background:#f8fafc;padding:20px;border-radius:14px;text-align:center;margin-bottom:15px;transition:.3s}
.stat:hover{transform:translateY(-5px);box-shadow:0 6px 18px rgba(0,0,0,.08)}
.stat h3{font-size:32px;color:var(--danger)}

/* CONTENIDO */
.main-content{display:flex;flex-direction:column;gap:25px}

/* FILTRO */
.filter-card{background:var(--card);padding:20px;border-radius:18px;display:flex;align-items:end;gap:15px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
.filter-card input{padding:10px;border-radius:10px;border:1px solid #e5e7eb}

/* BOTONES */
.btn{background:var(--primary);color:white;border:none;padding:10px 18px;border-radius:12px;font-weight:600;cursor:pointer;text-decoration:none;transition:.3s}
.btn:hover{background:var(--primary-dark)}
.btn-mini{background:#2563eb;color:white;border:none;padding:6px 10px;border-radius:8px;font-size:12px;cursor:pointer;margin-top:5px}
.btn-mini:hover{background:#1e40af}

/* TABLA */
.table-card{background:var(--card);padding:20px;border-radius:18px;box-shadow:0 10px 30px rgba(0,0,0,.08);overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:900px}
th,td{padding:15px;text-align:center;vertical-align:middle}
th{font-size:12px;text-transform:uppercase;color:var(--muted);border-bottom:1px solid #e5e7eb}
tbody tr{transition: all .2s ease;border-bottom:1px solid #e5e7eb;}
tbody tr:hover{background:#eef2ff;cursor:pointer;transform:scale(1.002);box-shadow:0 2px 6px rgba(0,0,0,0.05)}

/* MINIATURA */
.thumb{width:70px;height:70px;object-fit:cover;border-radius:10px;cursor:pointer;transition:.3s}
.thumb:hover{transform:scale(1.1);box-shadow:0 8px 18px rgba(0,0,0,.25)}

/* BOTON VOLVER */
.back-container{margin-top:20px;display:flex;justify-content:flex-end}

/* FOOTER */
footer{margin-top:60px;padding:30px;border-radius:20px;background:linear-gradient(135deg,#1e3a8a,#2563eb);color:white;text-align:center;font-weight:500}

/* VISOR DOCUMENTOS */
.visor{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.92);justify-content:center;align-items:center;z-index:9999}
.visor-contenido{position:relative;width:95%;height:95%;background:#111;border-radius:14px;display:flex;flex-direction:column;overflow:hidden}
.visor-barra{background:#1e293b;color:white;padding:10px 15px;display:flex;justify-content:space-between;align-items:center}
.visor-controles button{background:#2563eb;border:none;color:white;padding:6px 10px;margin-left:5px;border-radius:6px;cursor:pointer;font-size:14px}
.visor-controles button:hover{background:#1e40af}
#contenidoVisor{flex:1;display:flex;justify-content:center;align-items:center;overflow:auto;background:#0f172a}
#contenidoVisor img{max-width:100%;max-height:100%;transition:transform .2s}
#contenidoVisor iframe{width:100%;height:100%;border:none;background:white}

@media(max-width:1000px){
  .dashboard{grid-template-columns:1fr;}
}
.pdf-icon{
  font-size:32px;
  color:#dc2626;
}

.col-fecha{width:120px; white-space:nowrap; font-weight:500;}
.col-obs{width:50%; max-width:450px; word-wrap:break-word;}
.col-img{width:120px; white-space:nowrap;}


</style>
</head>

<body>

<?php include '../shared/menu.php'; ?>

<div class="banner-wrap">
  <img src="banner.jpg" class="banner">
  <div class="banner-overlay"></div>
  <div class="banner-text">Historial de Amonestaciones</div>
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
      <p>Total Amonestaciones</p>
    </div>

    <?php if($desde && $hasta): ?>
    <div class="stat">
      <h3><?= $rangoTotal ?></h3>
      <p>
        Entre <?= date('d-m-y', strtotime($desde)) ?>
        y <?= date('d-m-y', strtotime($hasta)) ?>
      </p>
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

<a href="exportar_amonestaciones.php?rut=<?= $rut ?>" target="_blank" class="btn">
📄 Exportar PDF
</a>

    <div class="table-card">
      <table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Observación</th>
            <th>Imagen</th>
          </tr>
        </thead>
        <tbody>
        <?php if($historial->num_rows > 0): ?>
          <?php while($h = $historial->fetch_assoc()): ?>
           <tr>
            <td class="col-fecha"><?= date('d-m-y', strtotime($h['fecha'])) ?></td>
            <td class="col-obs"><?= $h['observacion'] ?></td>
            <td class="col-img">
              <?php if(!empty($h['imagen'])): 
                $ext = strtolower(pathinfo($h['imagen'], PATHINFO_EXTENSION));
              ?>
                <a href="#" onclick="abrirVisor('uploads/<?= $h['imagen'] ?>');return false;">
                  <?php if($ext=='pdf'): ?>
                    <div class="pdf-icon">📄</div>
                  <?php else: ?>
                    <img src="uploads/<?= $h['imagen'] ?>" class="thumb">
                  <?php endif; ?>
                </a>
              <?php else: ?>
                <span style="color:#64748b;">Sin archivo</span>
              <?php endif; ?>
              <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_infraccion" value="<?= $h['id'] ?>">
                <input type="file" name="nueva_imagen" accept="image/*,.pdf">
                <button type="submit" name="subir_imagen" class="btn-mini"><?= !empty($h['imagen']) ? 'Reemplazar' : 'Subir' ?></button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="3">Sin amonestaciones registradas</td></tr>
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
Sistema de Amonestaciones · <?= date('Y') ?>
</footer>

<script>
function actualizarBannerHora(){
  const now = new Date();
  document.getElementById('banner-date').innerText =
    now.toLocaleDateString('es-CL',{weekday:'long',day:'2-digit',month:'long',year:'numeric'});
  document.getElementById('banner-time').innerText =
    now.toLocaleTimeString('es-CL',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
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
    contenido.innerHTML = '<iframe src="'+archivo+'#zoom=page-width"></iframe>';
  }else{
    contenido.innerHTML = '<img id="imagenVisor" src="'+archivo+'">';
    zoomNivel = 1;
  }
}
function cerrarVisor(){
  document.getElementById("visor").style.display="none";
  document.getElementById("contenidoVisor").innerHTML="";
}
function zoomIn(){
  let img = document.getElementById("imagenVisor");
  if(img){zoomNivel+=0.2;img.style.transform="scale("+zoomNivel+")";}
}
function zoomOut(){
  let img = document.getElementById("imagenVisor");
  if(img){zoomNivel-=0.2;if(zoomNivel<0.5) zoomNivel=0.5;img.style.transform="scale("+zoomNivel+")";}
}
</script>

<!-- VISOR DOCUMENTOS -->
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