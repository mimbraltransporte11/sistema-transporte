<?php
include 'db.php';
date_default_timezone_set('America/Santiago');

$id = $_GET['id'] ?? 0;

// FILTROS
$conductor_filtro = $_GET['conductor'] ?? '';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

// 🔹 DATOS DEL EQUIPO
$equipo = $conn->query("
SELECT ppu, tipo, configuracion 
FROM equipos 
WHERE id = $id
")->fetch_assoc();

// 🔹 QUERY BASE
$sql = "
SELECT 
a.fecha_inicio,
a.fecha_fin,
c.nombre AS conductor
FROM asignaciones a
LEFT JOIN conductores c ON a.conductor_id = c.id
WHERE a.equipo_id = $id
";

// FILTRO CONDUCTOR
if($conductor_filtro){
  $sql .= " AND c.nombre LIKE '%$conductor_filtro%' ";
}

// FILTRO FECHAS
if($desde){
  $sql .= " AND a.fecha_inicio >= '$desde 00:00:00' ";
}

if($hasta){
  $sql .= " AND a.fecha_inicio <= '$hasta 23:59:59' ";
}

$sql .= " ORDER BY a.fecha_inicio DESC";

$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" href="favicon.png">
<meta charset="UTF-8">
<title>Historial Detalle</title>

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
.banner-wrap{position:relative;margin-bottom:35px}

.banner{
width:100%;
max-height:220px;
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
opacity:.85;
}

#banner-time{
font-size:26px;
font-weight:800;
}

/* CARD */
.card{
background:white;
padding:25px;
border-radius:18px;
box-shadow:0 10px 30px rgba(0,0,0,.1);
margin-bottom:20px;
}

/* INFO */
.info{
display:flex;
gap:15px;
margin-bottom:20px;
flex-wrap:wrap;
}

.badge{
background:#2563eb;
color:white;
padding:8px 14px;
border-radius:10px;
}

/* FILTROS */
input{
padding:10px;
border-radius:10px;
border:1px solid #d1d5db;
margin-right:10px;
}

/* TABLA */
table{
width:100%;
border-collapse:separate;
border-spacing:0;
}

th,td{
padding:14px;
border-bottom:1px solid #e5e7eb;
text-align:center;
}

th{
font-size:12px;
text-transform:uppercase;
color:#64748b;
}

tr:hover{
background:#f8fafc;
}

/* ESTADOS */
.estado{
padding:6px 10px;
border-radius:8px;
font-weight:600;
font-size:13px;
}

.enruta{background:#dc2626;color:white;}
.disponible{background:#16a34a;color:white;}
.sin{background:#64748b;color:white;}

button{
background:linear-gradient(135deg,#2563eb,#1e40af);
color:white;
border:none;
padding:10px 14px;
border-radius:10px;
cursor:pointer;
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
<h1>Historial del Equipo</h1>
<p>Detalle de uso y asignaciones</p>
</div>

<div class="banner-datetime">
<div id="banner-date"></div>
<div id="banner-time"></div>
</div>
</div>

<!-- INFO -->
<div class="card">
<div class="info">
<div class="badge">PPU: <?= $equipo['ppu'] ?></div>
<div class="badge">Tipo: <?= $equipo['tipo'] ?></div>
<div class="badge">Config: <?= $equipo['configuracion'] ?></div>
</div>
</div>

<!-- FILTROS -->
<div class="card">

<form method="GET">
<input type="hidden" name="id" value="<?= $id ?>">

<input type="text" id="buscarConductor" name="conductor" placeholder="Buscar conductor"
value="<?= $conductor_filtro ?>">

<label>Desde</label>
<input type="date" name="desde" value="<?= $desde ?>">

<label>Hasta</label>
<input type="date" name="hasta" value="<?= $hasta ?>">

<button type="submit">Filtrar</button>
</form>

</div>

<!-- TABLA -->
<div class="card">

<table id="tablaHistorial">
<thead>
<tr>
<th>Conductor</th>
<th>Estado</th>
<th>Fecha Inicio</th>
<th>Fecha Fin</th>
</tr>
</thead>

<tbody>

<?php if($res->num_rows == 0): ?>
<tr>
<td colspan="4">
<span class="estado sin">Sin registros</span>
</td>
</tr>

<?php else: ?>

<?php while($row=$res->fetch_assoc()): 

if(!$row['fecha_inicio']){
  $estado="Sin registro"; $clase="sin";
}elseif($row['fecha_fin']===null){
  $estado="En ruta"; $clase="enruta";
}else{
  $estado="Disponible"; $clase="disponible";
}
?>

<tr>
<td><?= $row['conductor'] ?? '-' ?></td>

<td><span class="estado <?= $clase ?>"><?= $estado ?></span></td>

<td>
<?= $row['fecha_inicio'] 
? date('d/m/Y H:i', strtotime($row['fecha_inicio'])) 
: 'Sin registro' ?>
</td>

<td>
<?php if($row['fecha_fin']): ?>
<?= date('d/m/Y H:i', strtotime($row['fecha_fin'])) ?>
<?php else: ?>
<strong style="color:#16a34a;">ACTUAL</strong>
<?php endif; ?>
</td>

</tr>

<?php endwhile; ?>
<?php endif; ?>

</tbody>
</table>

</div>

<div style="text-align:right">
<button onclick="window.location='historial_equipos.php'">⬅ Volver</button>
</div>

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

const inputConductor = document.getElementById("buscarConductor");

inputConductor.addEventListener("keyup", function(){

  let filtro = this.value.toLowerCase();

  document.querySelectorAll("#tablaHistorial tbody tr").forEach(tr => {

    let nombre = tr.children[0].innerText.toLowerCase(); // columna conductor

    tr.style.display = nombre.includes(filtro) ? "" : "none";

  });

});

</script>

</body>
</html>