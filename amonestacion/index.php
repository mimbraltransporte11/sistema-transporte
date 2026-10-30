<?php 
include 'db.php';


/* ============================
   GUARDAR NUEVO CONDUCTOR
============================ */

if(isset($_POST['guardar_conductor'])){

  $rut = $_POST['rut_conductor'];
  $nombre = $_POST['nombre_conductor'];
  $area = $_POST['area_conductor'];

  $check = $conn->query("SELECT rut FROM conductor WHERE rut='$rut'");

  if($check->num_rows == 0){

    $conn->query("
      INSERT INTO conductor (rut,nombre,area)
      VALUES ('$rut','$nombre','$area')
    ");

  }

  header("Location: index.php");
  exit;
}


/* ============================
   GUARDAR AMONESTACION
============================ */

$imagenArchivo = ''; // valor por defecto

if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0){
    $permitidos = ['jpg','jpeg','png','webp','pdf'];
    $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

    if(in_array($ext, $permitidos) && $_FILES['imagen']['size'] <= 5*1024*1024){
        $imagenArchivo = "inf_" . time() . "." . $ext;
        move_uploaded_file($_FILES['imagen']['tmp_name'], "uploads/".$imagenArchivo);
    }
}

if(isset($_POST['guardar']) && !empty($_POST['rut'])){

  $rut = $_POST['rut'];
  $nombre = $_POST['nombre'];
  $area = $_POST['area'];
  $observacion = trim($_POST['observacion']);
  $fecha = $_POST['fecha'];

  // INSERT incluyendo el archivo
  $conn->query("
    INSERT INTO infraccion (rut,nombre,area,observacion,fecha,imagen)
    VALUES ('$rut','$nombre','$area','$observacion','$fecha','$imagenArchivo')
  ");

  header("Location: index.php");
  exit;
}

$buscar = $_GET['buscar'] ?? '';
$whereBuscar = '';

if ($buscar) {
    $buscar = $conn->real_escape_string($buscar);
    $whereBuscar = "WHERE rut LIKE '%$buscar%' OR nombre LIKE '%$buscar%'";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Sistema de Amonestaciones</title>

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
*{box-sizing:border-box}
body{
  margin:0;
  padding:30px;
  min-height:100vh;
  background:linear-gradient(180deg,var(--bg1),var(--bg2));
  font-family:Inter,sans-serif;
}

/* BANNER */
.banner-wrap{position:relative;margin-bottom:35px}
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
  position:absolute;bottom:20px;left:30px;color:white;
}

/* CARD */
.card{
  background:var(--card);
  padding:25px;
  border-radius:18px;
  box-shadow:0 10px 30px rgba(0,0,0,.08);
  margin:0 auto 35px;
  max-width:1400px;
  overflow-x:auto;
}

/* TABLE */
table{
  width:100%;
  border-collapse:separate;
  border-spacing:0;
  border-radius:14px;
}
th,td{
  padding:14px;
  border-bottom:1px solid #e5e7eb;
  text-align:center;
}
th{
  font-size:12px;
  color:var(--muted);
  text-transform:uppercase;
}
tbody tr:hover{background:#f8fafc}

/* BUTTONS */
.btn{
  background:linear-gradient(135deg,#2563eb,#1e40af);
  color:white;
  border:none;
  padding:10px 14px;
  border-radius:12px;
  cursor:pointer;
  font-weight:600;
}

.btn-secondary{
  background:#64748b;
}

.btn-danger{background:#dc2626}

/* FOOTER */
footer{
  margin-top:60px;
  padding:30px;
  border-radius:20px;
  background:linear-gradient(135deg,#1e3a8a,#2563eb);
  color:white;
  text-align:center;
}

/* ===== RELOJ PREMIUM ===== */

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
  border:1px solid rgba(255,255,255,0.25);
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

/* MODAL */
.modal{
  display:none;
  position:fixed;
  inset:0;
  background:rgba(15,23,42,.55);
  justify-content:center;
  align-items:center;
}

.modal-content{
  background:white;
  width:520px;
  border-radius:18px;
  padding:25px;
  box-shadow:0 15px 40px rgba(0,0,0,.25);
}

.modal-header{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:25px;
}

.close{
  font-size:28px;
  cursor:pointer;
}

.ranking-card{
  background:#f8fafc;
  padding:18px;
  border-radius:14px;
  margin-bottom:15px;
  transition:.3s;
}

.ranking-card:hover{
  transform:translateY(-3px);
  box-shadow:0 6px 20px rgba(0,0,0,.08);
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

.ranking-card strong{
  font-size:16px;
}

.ranking-card:nth-child(1){
  border-left:5px solid gold;
}

.ranking-card:nth-child(2){
  border-left:5px solid silver;
}

.ranking-card:nth-child(3){
  border-left:5px solid #cd7f32;
}

input,select,textarea{
  width:100%;
  padding:11px;
  margin-bottom:12px;
  border:1px solid #e5e7eb;
  border-radius:10px;
  font-family:inherit;
  font-size:14px;
}

textarea{
  resize:none;
  height:90px;
}

#resultados{
  background:white;
  border-radius:10px;
  box-shadow:0 10px 25px rgba(0,0,0,.15);
  max-height:180px;
  overflow-y:auto;
  margin-top:-8px;
  margin-bottom:10px;
}

.item-resultado{
  padding:10px;
  cursor:pointer;
  border-bottom:1px solid #e5e7eb;
}

.item-resultado:hover{
  background:#f1f5f9;
}

.modal-ranking{
  width:900px;
  max-width:95%;
}

#rankingContenido{
  max-height:500px;
  overflow-y:auto;
  padding-right:5px;
}

.ranking-link{
  text-decoration:none;
  color:inherit;
  display:block;
}

.ranking-card{
  cursor:pointer;
  transition:0.25s;
}

.ranking-card:hover{
  transform:translateY(-3px);
  box-shadow:0 8px 20px rgba(0,0,0,0.08);
}

</style>
</head>

<body>

<?php include '../shared/menu.php'; ?>

<!-- BANNER -->
<div class="banner-wrap">
  <img src="banner.jpg" class="banner">
  <div class="banner-overlay"></div>

  <div class="banner-text">
    <h1>Gestión de Amonestaciones</h1>
    <p>Control de conductores</p>
  </div>

  <div class="banner-datetime">
    <div id="banner-date"></div>
    <div id="banner-time"></div>
  </div>
</div>

<!-- LISTADO -->
<div class="card">

<h2 style="margin-bottom:20px">Conductores</h2>

<input
  type="text"
  id="buscador"
  placeholder="Buscar por RUT o nombre..."
  style="max-width:420px;width:100%;padding:12px 14px;border-radius:12px;border:1px solid #cbd5e1;margin-bottom:20px"
>

<div style="display:flex;justify-content:flex-end;gap:10px;margin-bottom:20px">
  <button class="btn" onclick="abrirModalConductor()">
➕ Nuevo Conductor
</button>

<button class="btn btn-danger" onclick="abrirModal()">
⚠ Registrar Amonestación
</button>
    <button onclick="abrirRanking()" class="btn btn-secondary">🏆 Ranking</button>
</div>

<table>
<thead>
<tr>
  <th>RUT</th>
  <th>Nombre</th>
  <th>Área</th>
  <th>Acciones</th>
</tr>
</thead>
<tbody id="tabla-resultados">
<?php
$res = $conn->query("
  SELECT * FROM conductor
  $whereBuscar
  ORDER BY nombre
");

while($c = $res->fetch_assoc()):
?>
<tr>
  <td><?= $c['rut'] ?></td>
  <td><?= $c['nombre'] ?></td>
  <td><?= $c['area'] ?></td>
  <td>
    <a href="historial.php?rut=<?= $c['rut'] ?>" class="btn">
  📄 Historial
</a>
  </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>

<div class="modal" id="modal">
  <div class="modal-content">

    <h2>Nueva Amonestación</h2>

    <form method="POST" enctype="multipart/form-data">

      <input 
      type="text" 
      id="busqueda" 
      placeholder="Escriba RUT o Nombre del conductor..."
      autocomplete="off">

      <div id="resultados"></div>

      <input type="hidden" name="rut" id="rut">

      <input type="text" name="nombre" id="nombre" placeholder="Nombre" readonly>
      <input type="text" name="area" id="area" placeholder="Área" readonly>

      <textarea name="observacion" placeholder="Observación" required></textarea>

      <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>

      
      <input type="file" name="imagen" id="imagen" accept="image/*,.pdf">

      <button name="guardar" class="btn">Guardar</button>

      <button type="button" onclick="cerrarModal()" class="btn btn-secondary">
      Cancelar
      </button>

    </form>

  </div>
</div>


<div class="modal" id="modalConductor">
  <div class="modal-content">

    <h2>Nuevo Conductor</h2>

    <form method="POST">

      <input type="text" name="rut_conductor" placeholder="RUT" required>
      <input type="text" name="nombre_conductor" placeholder="Nombre" required>
      <input type="text" name="area_conductor" placeholder="Área" required>

      <button name="guardar_conductor" class="btn">Guardar</button>

      <button type="button" onclick="cerrarModalConductor()" class="btn btn-secondary">
      Cancelar
      </button>

    </form>

  </div>
</div>

<!-- MODAL -->
<div id="modalRanking" class="modal">
  <div class="modal-content modal-ranking">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h2>🏆 Ranking Conductores</h2>
      <span class="close" onclick="cerrarRanking()">&times;</span>
    </div>

    <div style="margin-bottom:20px">
      <label><strong>Año:</strong></label>
      <select id="filtroAnio" style="padding:8px;border-radius:10px;border:1px solid #cbd5e1">
        <?php for($a=date('Y'); $a>=2020; $a--): ?>
          <option value="<?= $a ?>"><?= $a ?></option>
        <?php endfor; ?>
      </select>
    </div>

    <div id="rankingContenido"></div>

  </div>
</div>

<footer>
<strong>Transportes Mimbral LTDA</strong><br>
© <?= date('Y') ?>
</footer>

<script>
const buscador = document.getElementById('buscador');
const tabla = document.getElementById('tabla-resultados');

buscador.addEventListener('keyup', () => {
  const valor = buscador.value;

  fetch(`index.php?buscar=${encodeURIComponent(valor)}`)
    .then(res => res.text())
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      tabla.innerHTML = doc.querySelector('#tabla-resultados').innerHTML;
    });
});
</script>

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
</script>

<script>
function abrirRanking(){
  document.getElementById('modalRanking').style.display = 'flex';
  cargarRanking();
}

function cerrarRanking(){
  document.getElementById('modalRanking').style.display = 'none';
}

function cargarRanking(){
  const anio = document.getElementById('filtroAnio').value;

  fetch(`ranking_data.php?anio=${anio}`)
    .then(res => res.text())
    .then(html => {
      document.getElementById('rankingContenido').innerHTML = html;
    });
}

document.getElementById('filtroAnio').addEventListener('change', cargarRanking);

window.onclick = function(e){
  const modal = document.getElementById('modalRanking');
  if(e.target === modal){
    modal.style.display = 'none';
  }
}


function abrirModal(){
  document.getElementById('modal').style.display='flex';
}

function cerrarModal(){
  document.getElementById('modal').style.display='none';
}

function abrirModalConductor(){
  document.getElementById('modalConductor').style.display='flex';
}

function cerrarModalConductor(){
  document.getElementById('modalConductor').style.display='none';
}

const busqueda = document.getElementById('busqueda');
const resultados = document.getElementById('resultados');

busqueda.addEventListener('keyup', () => {

  const valor = busqueda.value;

  if(valor.length < 2){
    resultados.innerHTML = '';
    return;
  }

  fetch(`buscar_conductor.php?q=${encodeURIComponent(valor)}`)
    .then(res => res.text())
    .then(html => resultados.innerHTML = html);

});

resultados.addEventListener('click', e => {

  const item = e.target.closest('.item-resultado');
  if(!item) return;

  document.getElementById('rut').value = item.dataset.rut;
  document.getElementById('nombre').value = item.dataset.nombre;
  document.getElementById('area').value = item.dataset.area;

  busqueda.value = item.dataset.nombre;
  resultados.innerHTML = '';

});


</script>

</body>
</html>
