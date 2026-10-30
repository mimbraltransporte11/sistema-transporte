<?php
include 'db.php';

date_default_timezone_set('America/Santiago');

/* =========================
   GUARDAR TRABAJADOR
========================= */

if(isset($_POST['guardar_trabajador'])){

$rut = $_POST['rut'];
$nombre = $_POST['nombre'];

$conn->query("
INSERT INTO trabajadores
(rut,nombre)
VALUES
('$rut','$nombre')
");

header("Location:index.php");
exit;

}

/* =========================
   GUARDAR AVISO
========================= */

if(isset($_POST['guardar'])){

$rut = $_POST['rut'];
$nombre = $_POST['nombre'];
$aviso = $_POST['aviso'];
$fecha = $_POST['fecha'];

$imagen = NULL;

/* SUBIR ARCHIVO */

if(!empty($_FILES['imagen']['name'])){

$ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

$permitidos = ['jpg','jpeg','png','webp','pdf'];

if(in_array($ext,$permitidos)){

$imagen = "aviso_" . time() . "." . $ext;

move_uploaded_file(
$_FILES['imagen']['tmp_name'],
"uploads/" . $imagen
);

}

}

$conn->query("
INSERT INTO avisos
(rut,nombre,aviso,fecha,imagen)
VALUES
('$rut','$nombre','$aviso','$fecha','$imagen')
");

header("Location:index.php");
exit;

}

/* =========================
   TRABAJADORES
========================= */

$res = $conn->query("
SELECT *
FROM trabajadores
ORDER BY nombre
");
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Sistema de Avisos</title>

<link rel="icon" type="image/png" href="favicon.png">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>

:root{
--primary:#2563eb;
--danger:#dc2626;
--bg1:#f1f5f9;
--bg2:#e0e7ff;
}

*{
box-sizing:border-box;
}

body{
margin:0;
padding:30px;
min-height:100vh;
background:linear-gradient(180deg,var(--bg1),var(--bg2));
font-family:Inter,sans-serif;
}

/* BANNER */

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
background:linear-gradient(90deg,rgba(15,23,42,.75),transparent);
border-radius:20px;
}

.banner-text{
position:absolute;
bottom:20px;
left:30px;
color:white;
}

/* RELOJ */

.banner-datetime{
position:absolute;
right:25px;
bottom:20px;
background:rgba(255,255,255,.2);
padding:14px 22px;
border-radius:15px;
color:white;
text-align:right;
backdrop-filter:blur(6px);
}

#banner-date{
font-size:13px;
opacity:.9;
}

#banner-time{
font-size:26px;
font-weight:700;
letter-spacing:1px;
}

/* CARD */

.card{
background:white;
padding:25px;
border-radius:18px;
box-shadow:0 10px 30px rgba(0,0,0,.1);
}

.top-bar{
margin-bottom:15px;
}

.top-bar input{
width:320px;
padding:10px;
border-radius:10px;
border:1px solid #d1d5db;
}

.acciones{
display:flex;
gap:12px;
justify-content:flex-end;
margin-bottom:15px;
}

/* TABLA */

table{
width:100%;
border-collapse:separate;
border-spacing:0;
border-radius:14px;
overflow:hidden;
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
background:#f8fafc;
}

tbody tr:hover{
background:#f8fafc;
}

/* BOTONES */

.btn{
background:linear-gradient(135deg,#2563eb,#1e40af);
color:white;
border:none;
padding:10px 14px;
border-radius:10px;
cursor:pointer;
text-decoration:none;
font-weight:600;
transition:.2s;
}

.btn:hover{
transform:translateY(-2px);
}

.btn-danger{
background:#dc2626;
}

/* MODAL */

.modal{
display:none;
position:fixed;
inset:0;
background:rgba(15,23,42,.55);
justify-content:center;
align-items:center;
padding:20px;
z-index:999;
}

.modal-content{
background:white;
width:520px;
padding:25px;
border-radius:18px;
position:relative;
}

input,textarea{
width:100%;
padding:10px;
margin-bottom:10px;
border-radius:8px;
border:1px solid #ddd;
box-sizing:border-box;
font-family:inherit;
}

textarea{
height:140px;
resize:none;
}

/* RESULTADOS */

#resultados{
background:white;
max-height:220px;
overflow-y:auto;
box-shadow:0 10px 20px rgba(0,0,0,.15);
border-radius:12px;
margin-bottom:10px;
border:1px solid #e5e7eb;
}

.item-resultado{
padding:12px;
cursor:pointer;
transition:.15s;
border-bottom:1px solid #f1f5f9;
}

.item-resultado:last-child{
border-bottom:none;
}

.item-resultado:hover{
background:#eff6ff;
}

/* FOOTER */

footer{
margin-top:60px;
padding:30px;
border-radius:20px;
background:linear-gradient(135deg,#1e3a8a,#2563eb);
color:white;
text-align:center;
}

.btn-ranking{
background:linear-gradient(135deg,#64748b,#475569);
color:white;
}

/* MODAL RANKING */

/* ================= RANKING ================= */

.modal-ranking{
width:900px;
max-width:95%;
border-radius:22px;
padding:28px;
box-shadow:0 20px 50px rgba(0,0,0,.25);
background:white;
}

/* SELECT AÑO */

#filtroAnio{
width:100%;
padding:14px 18px;
border-radius:14px;
border:1px solid #cbd5e1;
background:white;
font-size:16px;
font-weight:600;
color:#0f172a;
outline:none;
appearance:none;
-webkit-appearance:none;
-moz-appearance:none;
cursor:pointer;
transition:.2s;
background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 8 10 12 14 8'/%3E%3C/svg%3E");
background-repeat:no-repeat;
background-position:right 14px center;
padding-right:45px;
}

#filtroAnio:focus{
border-color:#2563eb;
box-shadow:0 0 0 4px rgba(37,99,235,.15);
}

/* CONTENIDO */

#rankingContenido{
max-height:500px;
overflow-y:auto;
padding-right:5px;
margin-top:25px;
}

/* SCROLL BONITO */

#rankingContenido::-webkit-scrollbar{
width:8px;
}

#rankingContenido::-webkit-scrollbar-thumb{
background:#cbd5e1;
border-radius:20px;
}

#rankingContenido::-webkit-scrollbar-track{
background:transparent;
}

/* RANKING CARDS */

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

.close{
font-size:28px;
cursor:pointer;
}

.ranking-link{
text-decoration:none;
color:inherit;
display:block;
}

.ranking-link:hover .ranking-card{
cursor:pointer;
transform:translateY(-3px);
box-shadow:0 8px 25px rgba(0,0,0,.12);
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
<h1>Gestión de Avisos</h1>
<p>Registro y seguimiento de avisos</p>
</div>

<div class="banner-datetime">
<div id="banner-date"></div>
<div id="banner-time"></div>
</div>

</div>

<!-- CARD -->

<div class="card">

<h2>Trabajadores</h2>

<div class="top-bar">

<input
type="text"
id="buscarTabla"
placeholder="Buscar por RUT o nombre..."
>

</div>

<div class="acciones">

<button class="btn" onclick="abrirModalTrabajador()">
➕ Nuevo Trabajador
</button>

<button class="btn btn-danger" onclick="abrirModal()">
⚠ Registrar Aviso
</button>

<button class="btn btn-ranking" onclick="abrirRanking()">
🏆 Ranking
</button>

</div>

<table>

<thead>

<tr>
<th>RUT</th>
<th>NOMBRE</th>
<th>ACCIONES</th>
</tr>

</thead>

<tbody id="tablaTrabajadores">

<?php while($t = $res->fetch_assoc()): ?>

<tr>

<td><?= $t['rut'] ?></td>

<td><?= $t['nombre'] ?></td>

<td>

<a
href="historial.php?rut=<?= $t['rut'] ?>"
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

<!-- MODAL AVISO -->

<div class="modal" id="modal">

<div class="modal-content">

<h2>Nuevo Aviso</h2>

<form method="POST" enctype="multipart/form-data">

<input
type="text"
id="busqueda"
placeholder="Buscar trabajador..."
autocomplete="off"
>

<div id="resultados"></div>

<input type="hidden" name="rut" id="rut">

<label>Nombre</label>

<input
type="text"
name="nombre"
id="nombre"
readonly
>

<label>Fecha del aviso</label>

<input
type="date"
name="fecha"
value="<?= date('Y-m-d') ?>"
>

<label>Aviso</label>

<textarea name="aviso" required></textarea>

<label>Adjuntar imagen o PDF (opcional)</label>

<input
type="file"
name="imagen"
accept="image/*,.pdf"
>

<button
name="guardar"
class="btn btn-danger"
>
Guardar
</button>

<button
type="button"
onclick="cerrarModal()"
class="btn"
>
Cancelar
</button>

</form>

</div>

</div>

<!-- MODAL TRABAJADOR -->

<div class="modal" id="modalTrabajador">

<div class="modal-content">

<h2>Nuevo Trabajador</h2>

<form method="POST">

<input
type="text"
name="rut"
placeholder="RUT"
required
>

<input
type="text"
name="nombre"
placeholder="Nombre"
required
>

<button
name="guardar_trabajador"
class="btn"
>
Guardar
</button>

<button
type="button"
onclick="cerrarModalTrabajador()"
class="btn"
>
Cancelar
</button>

</form>

</div>

</div>

<!-- MODAL RANKING -->
<div id="modalRanking" class="modal">

  <div class="modal-content modal-ranking">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">

      <h2>🏆 Ranking Trabajadores</h2>

      <span class="close" onclick="cerrarRanking()">
        &times;
      </span>

    </div>

    <div style="margin-bottom:20px">

      <label>
        <strong>Año:</strong>
      </label>

      <select id="filtroAnio">

        <?php for($a=date('Y'); $a>=2020; $a--): ?>

          <option value="<?= $a ?>">
            <?= $a ?>
          </option>

        <?php endfor; ?>

      </select>

    </div>

    <div id="rankingContenido"></div>

  </div>

</div>

<footer>

<strong>Transportes Mimbral LTDA</strong><br>

Sistema de Avisos · <?= date('Y') ?>

</footer>

<script>

/* =========================
   MODALES
========================= */

function abrirModal(){

document.getElementById('modal').style.display='flex';

}

function cerrarModal(){

document.getElementById('modal').style.display='none';

}

function abrirModalTrabajador(){

document.getElementById('modalTrabajador').style.display='flex';

}

function cerrarModalTrabajador(){

document.getElementById('modalTrabajador').style.display='none';

}

/* =========================
   BUSCADOR AJAX
========================= */

const busqueda = document.getElementById('busqueda');
const resultados = document.getElementById('resultados');

busqueda.addEventListener('keyup', () => {

let valor = busqueda.value.trim();

if(valor.length < 2){

resultados.innerHTML = '';
return;

}

fetch("buscar_trabajador.php?q=" + encodeURIComponent(valor))

.then(res => res.text())

.then(data => {

resultados.innerHTML = data;

});

});

/* =========================
   CLICK RESULTADO
========================= */

resultados.addEventListener('click', e => {

let item = e.target.closest('.item-resultado');

if(!item)return;

document.getElementById('rut').value =
item.dataset.rut;

document.getElementById('nombre').value =
item.dataset.nombre;

busqueda.value =
item.dataset.nombre;

resultados.innerHTML = '';

});

/* =========================
   RELOJ
========================= */

function actualizarBannerHora(){

const now = new Date();

const dias = [
"Domingo","Lunes","Martes","Miércoles",
"Jueves","Viernes","Sábado"
];

const meses = [
"Enero","Febrero","Marzo","Abril",
"Mayo","Junio","Julio","Agosto",
"Septiembre","Octubre","Noviembre","Diciembre"
];

let dia = dias[now.getDay()];
let numero = now.getDate();
let mes = meses[now.getMonth()];
let anio = now.getFullYear();

document.getElementById("banner-date").innerText =
dia + ", " + numero + " De " + mes + " De " + anio;

document.getElementById("banner-time").innerText =
now.toLocaleTimeString("es-CL",{
hour:'2-digit',
minute:'2-digit',
second:'2-digit'
});

}

setInterval(actualizarBannerHora,1000);

actualizarBannerHora();


/* =========================
   RANKING
========================= */

function abrirRanking(){

document.getElementById('modalRanking').style.display='flex';

cargarRanking();

}

function cerrarRanking(){

document.getElementById('modalRanking').style.display='none';

}

function cargarRanking(){

const anio =
document.getElementById('filtroAnio').value;

fetch(`ranking_data.php?anio=${anio}`)

.then(res => res.text())

.then(html => {

document.getElementById('rankingContenido').innerHTML = html;

});

}

document
.getElementById('filtroAnio')
.addEventListener('change', cargarRanking);

/* cerrar modal */

window.onclick = function(e){

const modal =
document.getElementById('modalRanking');

if(e.target === modal){

modal.style.display = 'none';

}

}

/* =========================
   BUSCADOR TABLA
========================= */

const buscador =
document.getElementById("buscarTabla");

buscador.addEventListener("keyup", function(){

let filtro =
this.value.toLowerCase();

let palabras =
filtro.split(" ");

let filas =
document.querySelectorAll("#tablaTrabajadores tr");

filas.forEach(fila=>{

let texto =
fila.innerText.toLowerCase();

let coincide = true;

palabras.forEach(palabra=>{

if(
palabra != '' &&
!texto.includes(palabra)
){
coincide = false;
}

});

fila.style.display =
coincide ? "" : "none";

});

});

</script>

</body>
</html>