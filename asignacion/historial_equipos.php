<?php
include 'db.php';
date_default_timezone_set('America/Santiago');

$mes = $_GET['mes'] ?? '';
$anio = $_GET['anio'] ?? '';
$ppu_buscar = $_GET['ppu'] ?? '';
$tipo = $_GET['tipo'] ?? '';

// Consulta base
$sql = "SELECT id, ppu, tipo, configuracion, revision_tecnica FROM equipos WHERE 1";

if($ppu_buscar){
  $sql .= " AND ppu LIKE '%$ppu_buscar%' ";
}

if($tipo){
  $sql .= " AND tipo = '$tipo' ";
}

$sql .= " ORDER BY id DESC";

$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" href="favicon.png">
<meta charset="UTF-8">
<title>Historial de Equipos</title>
<style>
:root{
--primary:#2563eb;
--bg1:#f8fafc;
--bg2:#eef2ff;
}

body{
margin:0;
padding:30px;
min-height:100vh;
background:linear-gradient(180deg,var(--bg1),var(--bg2));
font-family:'Inter',Arial;
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
}

/* ===== FILTROS ===== */
.filtros{
margin-bottom:20px;
}

input, select{
padding:10px;
border-radius:10px;
border:1px solid #d1d5db;
margin-right:10px;
}

button{
background:linear-gradient(135deg,#2563eb,#1e40af);
color:white;
border:none;
padding:10px 14px;
border-radius:10px;
cursor:pointer;
}

/* ===== TABLA ===== */
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

tbody tr:hover{
background:#f8fafc;
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


.modal{
display:none;
position:fixed;
inset:0;
background:rgba(0,0,0,.6);
justify-content:center;
align-items:center;
z-index:9999;
}

.modal-box{
background:white;
width:400px;
padding:20px;
border-radius:16px;
display:flex;
flex-direction:column;
gap:10px;
}

.modal-box input{
padding:10px;
border-radius:8px;
border:1px solid #ccc;
}

.modal-actions{
display:flex;
justify-content:flex-end;
gap:10px;
margin-top:10px;
}

.btn-cancel{
background:#64748b;
color:white;
border:none;
padding:8px 14px;
border-radius:8px;
cursor:pointer;
}

.btn-save{
background:#2563eb;
color:white;
border:none;
padding:8px 14px;
border-radius:8px;
cursor:pointer;
}

tbody tr{
cursor:pointer;
transition:.2s;
}

tbody tr:hover{
background:#eef2ff;
transform:scale(1.002);
}

</style>
</head>
<body>

<?php include '../shared/menu.php'; ?>

<div class="banner-wrap">

<img src="banner.jpg" class="banner">

<div class="banner-overlay"></div>

<div class="banner-text">
<h1>Historial de Equipos</h1>
<p>Registro completo de asignaciones</p>
</div>

<div class="banner-datetime">
  <div id="banner-date"></div>
  <div id="banner-time"></div>
</div>

</div> 

<div class="card">
<h2>Historial de Equipos</h2>

<form method="GET" id="formFiltro">

<select name="tipo" id="filtroTipo">
  <option value="">Todos los tipos</option>
<option value="TRACTO" <?= $tipo=='TRACTO'?'selected':'' ?>>Tracto</option>
<option value="CAMION" <?= $tipo=='CAMION'?'selected':'' ?>>Camión</option>
<option value="RAMPLA" <?= $tipo=='RAMPLA'?'selected':'' ?>>Rampla</option>
<option value="CAMA BAJA" <?= $tipo=='CAMA BAJA'?'selected':'' ?>>CAMA BAJA</option>
<option value="CORTINA" <?= $tipo=='CORTINA'?'selected':'' ?>>Cortina</option>
<option value="CHASIS" <?= $tipo=='CHASIS'?'selected':'' ?>>Chasis</option>
<option value="ESTANQUE" <?= $tipo=='ESTANQUE'?'selected':'' ?>>Estanque</option>
<option value="CARRO" <?= $tipo=='CARRO'?'selected':'' ?>>Carro</option>
</select>

<input type="text" id="buscarPPU" name="ppu" placeholder="Buscar PPU" value="<?= $ppu_buscar ?>">

</form>

<table id="tablaEquipos">

<thead>
<tr>
  <th>PPU</th>
  <th>Tipo</th>
  <th>Config</th>
  <th>Historial</th>
</tr>
</thead>

<tbody>
<?php while($row=$res->fetch_assoc()): ?>
<tr 
onclick="abrirModalEquipo(this)"
data-id="<?= $row['id'] ?>"
data-ppu="<?= $row['ppu'] ?>"
data-tipo="<?= $row['tipo'] ?>"
data-config="<?= $row['configuracion'] ?>"
data-revision="<?= $row['revision_tecnica'] ?? '' ?>"
style="cursor:pointer"
>

<td><?= $row['ppu'] ?></td>
<td><?= $row['tipo'] ?></td>
<td><?= $row['configuracion'] ?></td>

<td>
  <a href="historial_detalle.php?id=<?= $row['id'] ?>" onclick="event.stopPropagation()">
    <button>📄 Ver historial</button>
  </a>
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


<div id="modalEquipo" class="modal">
  <div class="modal-box">

    <h3>Editar Equipo</h3>

    <input type="hidden" id="equipoID">

    <label>PPU</label>
    <input type="text" id="equipoPPU" disabled>

    <label>Tipo</label>
    <input type="text" id="equipoTipo" disabled>

    <label>Configuración</label>
    <input type="text" id="equipoConfig" disabled>

    <label>Revisión Técnica</label>
    <input type="date" id="equipoRevision">

    <div class="modal-actions">
      <button class="btn-cancel" onclick="cerrarModalEquipo()">Cancelar</button>
      <button class="btn-save" onclick="guardarEquipo()">Guardar</button>
    </div>

  </div>
</div>

<footer>
<strong>Transportes Mimbral LTDA</strong><br>
© <?= date('Y') ?>
</footer>

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

// FILTRO EN VIVO POR PPU
document.getElementById("buscarPPU").addEventListener("keyup", function(){

let filtro = this.value.toLowerCase();

document.querySelectorAll("#tablaEquipos tbody tr").forEach(tr => {

let ppu = tr.children[0].innerText.toLowerCase(); // columna PPU

tr.style.display = ppu.includes(filtro) ? "" : "none";

});

});


const filtroTipo = document.getElementById("filtroTipo");
const inputPPU = document.getElementById("buscarPPU");
const form = document.getElementById("formFiltro");

// Cuando cambia el tipo → envía formulario
filtroTipo.addEventListener("change", () => {
  form.submit();
});



function abrirModalEquipo(tr){

document.getElementById("modalEquipo").style.display = "flex";

document.getElementById("equipoID").value = tr.dataset.id;
document.getElementById("equipoPPU").value = tr.dataset.ppu;
document.getElementById("equipoTipo").value = tr.dataset.tipo;
document.getElementById("equipoConfig").value = tr.dataset.config;
document.getElementById("equipoRevision").value = tr.dataset.revision;

}

function cerrarModalEquipo(){
document.getElementById("modalEquipo").style.display = "none";
}

function guardarEquipo(){

let id = document.getElementById("equipoID").value;
let tipo = document.getElementById("equipoTipo").value;
let config = document.getElementById("equipoConfig").value;
let revision = document.getElementById("equipoRevision").value;

fetch("guardar_equipo.php", {
method: "POST",
headers: {
"Content-Type": "application/x-www-form-urlencoded"
},
body: `id=${id}&tipo=${tipo}&configuracion=${config}&revision=${revision}`
})
.then(res => res.text())
.then(r => {

if(r === "ok"){
location.reload(); // puedes hacerlo más pro después
}else{
alert("Error al guardar");
}

})
.catch(()=> alert("Error de conexión"));

}

</script>

</body>
</html>