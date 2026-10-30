<?php
include 'db.php';

/* GUARDAR CONDUCTOR */

if(isset($_POST['guardar_conductor'])){

$rut=$_POST['rut'];
$nombre=$_POST['nombre'];
$area=$_POST['area'];

$conn->query("
INSERT INTO conductores (rut,nombre,area)
VALUES ('$rut','$nombre','$area')
");

header("Location:index.php");
exit;
}

/* GUARDAR MULTA */

if(isset($_POST['guardar'])){

$rut=$_POST['rut'];
$nombre=$_POST['nombre'];
$area=$_POST['area'];
$observacion=$_POST['observacion'];
$valor=$_POST['valor'];
$estado=$_POST['estado'];
$fecha=$_POST['fecha'];
$fecha_recepcion = $_POST['fecha_recepcion'];


$imagen=NULL;

if(!empty($_FILES['imagen']['name'])){

$ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

$permitidos = ['jpg','jpeg','png','webp','pdf'];

if(in_array($ext,$permitidos)){

$imagen="parte_".time().".".$ext;

move_uploaded_file(
$_FILES['imagen']['tmp_name'],
"uploads/".$imagen
);

}

}

$conn->query("
INSERT INTO partes
(rut,nombre,area,observacion,valor,estado,fecha,fecha_recepcion,imagen)
VALUES
('$rut','$nombre','$area','$observacion','$valor','$estado','$fecha','$fecha_recepcion','$imagen')
");

header("Location:index.php");
exit;

}

$res=$conn->query("SELECT * FROM conductores ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<title>Sistema de Multas</title>

<link rel="icon" type="image/png" href="favicon.png">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>

:root{
--primary:#2563eb;
--danger:#dc2626;
--bg1:#f1f5f9;
--bg2:#e0e7ff;
}

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

.btn-ranking{
background:linear-gradient(135deg,#64748b,#475569);
color:white;
}


/* ===== TABLE ===== */ 
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

tbody tr:hover{
 background:#f8fafc
}

/* BOTONES */

.btn{
background:linear-gradient(135deg,#2563eb,#1e40af);
color:white;
border:none;
padding:10px 14px;
border-radius:10px;
cursor:pointer;
}


.btn-danger{
background:#dc2626;
}

/* MODAL */

.modal {
  display:none;
  position:fixed;
  inset:0;
  background:rgba(15,23,42,.55);
  justify-content:center;
  align-items:center;
  padding:20px; /* para que no toque bordes en pantallas pequeñas */
}

.modal-content{
background:white;
width:520px;
padding:25px;
border-radius:18px;
}

input,select,textarea{
width:100%;
padding:10px;
margin-bottom:10px;
border-radius:8px;
border:1px solid #ddd;
}

#resultados{
background:white;
max-height:160px;
overflow-y:auto;
box-shadow:0 10px 20px rgba(0,0,0,.2);
}

.item-resultado{
padding:8px;
cursor:pointer;
}

.item-resultado:hover{
background:#f1f5f9;
}

/* MODAL RANKING - tamaño igual al index 1 */
.modal-ranking {
  width:900px;         /* ancho fijo principal */
  max-width:95%;       /* no sobrepasar el 95% de la pantalla */
  border-radius:18px;
  padding:25px;
  box-shadow:0 15px 40px rgba(0,0,0,.25);
  background:white;
}

.modal-ranking-header {
  background: linear-gradient(135deg,#64748b,#475569);
  color:white;
  padding:20px 25px;
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.modal-ranking-header h2 {
  margin:0;
  font-size:20px;
}

.modal-ranking-header .close {
  font-size:28px;
  cursor:pointer;
}

.modal-ranking-body {
  padding:20px 25px;
  max-height:500px;
  overflow-y:auto;
}

.modal-ranking-body label {
  display:block;
  margin-bottom:10px;
  font-weight:600;
}

.modal-ranking-body select {
  padding:8px;
  border-radius:10px;
  border:1px solid #cbd5e1;
  margin-bottom:15px;
  width:100%;
}

#rankingContenido {
  max-height:500px;    /* altura máxima, scroll si hay más contenido */
  overflow-y:auto;
  padding-right:5px;
}

/* BOTÓN RANKING */
.btn-ranking{
  background:linear-gradient(135deg,#64748b,#475569);
  color:white;
}

/* MODAL RANKING */
.modal-ranking{
  width:900px;
  max-width:95%;
}

#rankingContenido{
  max-height:500px;
  overflow-y:auto;
  padding-right:5px;
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

.close{
  font-size:28px;
  cursor:pointer;
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
<h1>Gestión de Multas</h1>
<p>Registro de partes e infracciones</p>
</div>

<div class="banner-datetime">
<div id="banner-date"></div>
<div id="banner-time"></div>
</div>

</div>


<div class="card">

<h2>Conductores</h2>

<div class="top-bar">

<input type="text" id="buscarTabla" placeholder="Buscar por RUT o nombre...">

</div>

<div class="acciones">

<button class="btn" onclick="abrirModalConductor()">
➕ Nuevo Conductor
</button>

<button class="btn btn-danger" onclick="abrirModal()">
⚠ Registrar Multa
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
<th>AREA</th>
<th>ACCIONES</th>
</tr>
</thead>

<tbody>

<?php while($c=$res->fetch_assoc()): ?>

<tr>

<td><?= $c['rut'] ?></td>
<td><?= $c['nombre'] ?></td>
<td><?= $c['area'] ?></td>

<td>
<a href="historial.php?rut=<?= $c['rut'] ?>" class="btn">📄 Historial</a>
</td>

</tr>

<?php endwhile; ?>

</tbody>
</table>

</div>


<!-- MODAL MULTA -->

<div class="modal" id="modal">

<div class="modal-content">

<h2>Nueva Multa</h2>

<form method="POST" enctype="multipart/form-data">

<input type="text" id="busqueda" placeholder="Buscar conductor">

<div id="resultados"></div>

<input type="hidden" name="rut" id="rut">

<label>Nombre</label>
<input type="text" name="nombre" id="nombre" >

<label>Area</label>
<input type="text" name="area" id="area">

<label>Observacion</label>
<textarea name="observacion" ></textarea>

<label>Valor de la Multa</label>
<input type="number" name="valor">

<label>Estado de la multa</label>
<select name="estado">
<option>Pendiente</option>
<option>Pagado</option>
<option>Apelado</option>
</select>

<label>Fecha de emision de la multa</label>
<input type="date" name="fecha">

<label>Fecha de recepción de la multa</label>
<input type="date" name="fecha_recepcion" value="<?=date('Y-m-d')?>">

<label>subir imagen o pdf (opcional)</label>
<input type="file" name="imagen" accept="image/*,.pdf">

<button name="guardar" class="btn btn-danger">Guardar</button>

<button type="button" onclick="cerrarModal()" class="btn">Cancelar</button>

</form>

</div>

</div>


<!-- MODAL CONDUCTOR -->

<div class="modal" id="modalConductor">

<div class="modal-content">

<h2>Nuevo Conductor</h2>

<form method="POST">

<input type="text" name="rut" placeholder="RUT">

<input type="text" name="nombre" placeholder="Nombre">

<input type="text" name="area" placeholder="Área">

<button name="guardar_conductor" class="btn">Guardar</button>

<button type="button" onclick="cerrarModalConductor()" class="btn">Cancelar</button>

</form>

</div>

</div>


<!-- MODAL RANKING -->
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

// Cerrar modal si se hace click fuera
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

const busqueda=document.getElementById('busqueda');
const resultados=document.getElementById('resultados');

busqueda.addEventListener('keyup',()=>{

let valor=busqueda.value;

if(valor.length<2){
resultados.innerHTML='';
return;
}

fetch("buscar_conductor.php?q="+valor)
.then(res=>res.text())
.then(data=>resultados.innerHTML=data);

});

resultados.addEventListener('click',e=>{

let item=e.target.closest('.item-resultado');

if(!item)return;

document.getElementById('rut').value=item.dataset.rut;
document.getElementById('nombre').value=item.dataset.nombre;
document.getElementById('area').value=item.dataset.area;

busqueda.value=item.dataset.nombre;
resultados.innerHTML='';

});


function actualizarBannerHora(){

const now = new Date();

const dias = [
"Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"
];

const meses = [
"Enero","Febrero","Marzo","Abril","Mayo","Junio",
"Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"
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



const buscador = document.getElementById("buscarTabla");

buscador.addEventListener("keyup", function(){

let filtro = this.value.toLowerCase();
let filas = document.querySelectorAll("tbody tr");

filas.forEach(fila=>{

let texto = fila.innerText.toLowerCase();

fila.style.display = texto.includes(filtro) ? "" : "none";

});

});

</script>

</body>
</html>