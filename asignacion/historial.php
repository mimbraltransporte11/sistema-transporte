<?php
include 'db.php';

$id = $_GET['id'];
$ppu = $_GET['ppu'] ?? '';

// DATOS DEL CONDUCTOR
$conductor = $conn->query("
SELECT rut, nombre, area 
FROM conductores 
WHERE id = $id
")->fetch_assoc();

$wherePPU = "";

if(!empty($ppu)){
    $wherePPU = "AND e.ppu LIKE '%$ppu%'";
}

$res = $conn->query("
SELECT e.ppu, e.tipo, e.configuracion, a.fecha_inicio, a.fecha_fin
FROM asignaciones a
JOIN equipos e ON a.equipo_id = e.id
WHERE a.conductor_id = $id
$wherePPU
ORDER BY a.fecha_inicio DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" href="favicon.png">
<meta charset="UTF-8">
<title>Historial Conductor</title>

<style>
:root{
--primary:#2563eb;
--bg1:#f8fafc;
--bg2:#eef2ff;
}

body{
margin:0;
padding:30px;
background:linear-gradient(180deg,var(--bg1),var(--bg2));
font-family:'Inter',Arial;
min-height: 100vh;
}

/* ===== BANNER ===== */
.banner-wrap{position:relative;margin-bottom:30px}

.banner{
width:100%;
max-height:200px;
object-fit:cover;
border-radius:20px;
}

.banner-overlay{
position:absolute;
inset:0;
background:linear-gradient(90deg,rgba(15,23,42,.8),transparent);
border-radius:20px;
}

.banner-text{
position:absolute;
bottom:20px;
left:30px;
color:white;
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

/* ===== CARD ===== */
.card{
background:white;
padding:25px;
border-radius:18px;
box-shadow:0 10px 30px rgba(0,0,0,.1);
margin-bottom:20px;
}

/* ===== TABLA ===== */
table{
width:100%;
border-collapse:collapse;
}

th,td{
padding:12px;
border-bottom:1px solid #eee;
text-align:center;
}

tr:hover{
background:#f8fafc;
}

/* ===== BOTON ===== */
button{
padding:10px 15px;
border:none;
border-radius:10px;
background:#2563eb;
color:white;
cursor:pointer;
margin-bottom:15px;
}

.dashboard{
display:grid;
grid-template-columns:260px 1fr;
gap:30px;
align-items:start;
}

.main-content{
display:flex;
flex-direction:column;
gap:20px;
}

.filter-card{
background:white;
padding:15px;
border-radius:15px;
box-shadow:0 10px 25px rgba(0,0,0,.08);
display:flex;
gap:10px;
align-items:end;
}

.profile-card{
background:white;
padding:28px;
border-radius:18px;
box-shadow:0 10px 30px rgba(0,0,0,.08);
display:flex;
flex-direction:column;
gap:20px;
align-items:center;
text-align:center;
min-width:230px;
height:fit-content;
}

.profile-info{
line-height:1.6;
}

@media(max-width:1000px){
.dashboard{
grid-template-columns:1fr;
}
}

.filter-card input{
padding:10px;
border-radius:10px;
border:1px solid #e5e7eb;
}

/* ===== FOOTER ===== */
footer{
margin-top:60px;
padding:30px;
border-radius:20px;
background:linear-gradient(135deg,#1e3a8a,#2563eb);
color:white;
text-align:center;
}

.estado-activo{
  color:#16a34a;
  font-weight:700;
}

</style>
</head>

<body>

<?php include '../shared/menu.php'; ?>

<div class="banner-wrap">

<img src="banner.jpg" class="banner">

<div class="banner-overlay"></div>

<div class="banner-text">
<h1>Historial de Asignaciones</h1>

</div>

<div class="banner-datetime">
<div id="banner-date"></div>
<div id="banner-time"></div>
</div>

</div>

<div class="dashboard">

<!-- 🔹 CARD IZQUIERDA -->
<div class="profile-card">

<div class="profile-info">

<p><strong>RUT:</strong><br><?= $conductor['rut'] ?></p>

<p><strong>Nombre:</strong><br><?= $conductor['nombre'] ?></p>

<p><strong>Área:</strong><br><?= $conductor['area'] ?></p>

</div>

</div> 

<!-- 🔹 CONTENIDO DERECHO -->
<div class="main-content">

<!-- 🔍 FILTRO -->
<form method="GET" class="filter-card">

<input type="hidden" name="id" value="<?= $id ?>">

<div>
<label>Buscar PPU</label><br>
<input 
type="text" 
id="buscadorPPU"
name="ppu" 
placeholder="Ej: AB1234" 
value="<?= $ppu ?>"
>
</div>


</form>

<!-- 📋 TABLA -->
<div class="card">

<h3>Historial</h3>

<table id="tablaHistorial">
<thead>
<tr>
<th>PPU</th>
<th>Tipo</th>
<th>Config</th>
<th>Desde</th>
<th>Hasta</th>
</tr>
</thead>

<tbody>
<?php while($r=$res->fetch_assoc()): ?>
<tr>
<td><?= $r['ppu'] ?></td>
<td><?= $r['tipo'] ?></td>
<td><?= $r['configuracion'] ?></td>
<td>
<?= $r['fecha_inicio'] 
    ? date('d/m/y H:i', strtotime($r['fecha_inicio'])) 
    : '-' ?>
</td>

<td>
<?php if($r['fecha_fin']): ?>
  <?= date('d/m/y H:i', strtotime($r['fecha_fin'])) ?>
<?php else: ?>
  <span class="estado-activo">ACTUAL</span>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>

<!-- 🔙 VOLVER -->
<div style="text-align:right">
<button onclick="window.location='index.php'">⬅ Volver</button>
</div>

</div> <!-- main-content -->

</div> <!-- dashboard -->

<script>
function actualizarHora(){

const now = new Date();

const dias = ["Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"];
const meses = ["Enero","Febrero","Marzo","Abril","Mayo","Junio",
"Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];

document.getElementById("banner-date").innerText =
dias[now.getDay()] + ", " + now.getDate() + " de " + meses[now.getMonth()] + " de " + now.getFullYear();

document.getElementById("banner-time").innerText =
now.toLocaleTimeString();
}

setInterval(actualizarHora,1000);
actualizarHora();

document.getElementById("buscadorPPU").addEventListener("keyup", function(){

let filtro = this.value.toLowerCase();

document.querySelectorAll("#tablaHistorial tbody tr").forEach(tr => {

let ppu = tr.children[0].innerText.toLowerCase();

if(ppu.includes(filtro)){
    tr.style.display = "";
}else{
    tr.style.display = "none";
}

});

});

</script>

<footer>
<strong>Transportes Mimbral LTDA</strong><br>
© <?= date('Y') ?>
</footer>

</body>
</html>