<?php
include 'db.php';

/* ===== SUBIR IMAGEN ===== */
if(isset($_POST['subir_imagen'])){

$id = intval($_POST['id_multa']);

if(isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] == 0){

$permitidos = ['jpg','jpeg','png','webp','pdf'];
$ext = strtolower(pathinfo($_FILES['nueva_imagen']['name'], PATHINFO_EXTENSION));

if(in_array($ext,$permitidos)){

if($_FILES['nueva_imagen']['size'] <= 5*1024*1024){

$nombreArchivo = "multa_" . $id . "_" . time() . "." . $ext;
$rutaDestino = "uploads/" . $nombreArchivo;

if(move_uploaded_file($_FILES['nueva_imagen']['tmp_name'], $rutaDestino)){

$imgVieja = $conn->query("SELECT imagen FROM partes WHERE id=$id")->fetch_assoc()['imagen'];

if($imgVieja && file_exists("uploads/".$imgVieja)){
unlink("uploads/".$imgVieja);
}

$conn->query("UPDATE partes SET imagen='$nombreArchivo' WHERE id=$id");

header("Location: historial.php?rut=".$_GET['rut']);
exit;

}

}

}

}

}

/* ===== CAMBIAR ESTADO ===== */
if(isset($_POST['cambiar_estado'])){

$id = intval($_POST['id_multa']);
$estado = $_POST['estado'];

// 🔒 validar estado actual
$res = $conn->query("SELECT estado FROM partes WHERE id=$id");
$row = $res->fetch_assoc();

if($row['estado'] == "Pagado"){
    exit; // ya está bloqueado
}

if($estado == 'Pagado'){

$fecha_pago = date('Y-m-d');

$conn->query("
UPDATE partes 
SET estado='$estado', fecha_pago='$fecha_pago'
WHERE id=$id
");

}else{

$conn->query("
UPDATE partes 
SET estado='$estado'
WHERE id=$id
");

}

header("Location: historial.php?rut=".$_GET['rut']);
exit;

}

/* ===== VALIDAR RUT ===== */
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
$cond = $conn->query("SELECT * FROM conductores WHERE rut='$rut'");
if ($cond->num_rows === 0) {
    header('Location: index.php');
    exit;
}
$conductor = $cond->fetch_assoc();

/* ===== HISTORIAL ===== */
$historial = $conn->query("
SELECT * 
FROM partes 
WHERE rut='$rut' $whereFecha
ORDER BY fecha DESC
");

$total = $conn->query("
SELECT COUNT(*) total 
FROM partes 
WHERE rut='$rut'
")->fetch_assoc()['total'];

$rangoTotal = $conn->query("
SELECT COUNT(*) cantidad 
FROM partes 
WHERE rut='$rut' $whereFecha
")->fetch_assoc()['cantidad'];
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<title>Historial de Multas</title>

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

.banner{
width:100%;
max-height:220px;
object-fit:cover;
border-radius:20px
}

.banner-overlay{
position:absolute;
inset:0;
background:linear-gradient(90deg,rgba(15,23,42,.75),transparent);
border-radius:20px
}

.banner-text{
position:absolute;
bottom:20px;
left:30px;
color:white;
font-size:28px;
font-weight:700
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
box-shadow:0 10px 25px rgba(0,0,0,.35)
}

#banner-date{font-size:12px}
#banner-time{font-size:26px;font-weight:800}

/* GRID */

.dashboard{
display:grid;
grid-template-columns:1fr 3fr;
gap:30px
}

/* CARD PERFIL */

.profile-card{
background:var(--card);
padding:28px;
border-radius:18px;
box-shadow:0 10px 30px rgba(0,0,0,.08);
display:flex;
flex-direction:column;
gap:20px;
align-items:center;
text-align:center;
min-width:230px;
}

.profile-info{
margin-bottom:20px;
line-height:1.6
}

/* ESTADISTICAS */

.stat{
background:#f8fafc;
padding:20px;
border-radius:14px;
text-align:center;
margin-bottom:15px;
transition:.3s
}

.stat:hover{
transform:translateY(-5px);
box-shadow:0 6px 18px rgba(0,0,0,.08)
}

.stat h3{
font-size:32px;
color:var(--danger)
}

/* CONTENIDO */

.main-content{
display:flex;
flex-direction:column;
gap:25px
}

/* FILTRO */

.filter-card{
background:var(--card);
padding:20px;
border-radius:18px;
display:flex;
align-items:end;
gap:15px;
box-shadow:0 10px 30px rgba(0,0,0,.08)
}

.filter-card input{
padding:10px;
border-radius:10px;
border:1px solid #e5e7eb
}

/* BOTONES */

.btn{
background:var(--primary);
color:white;
border:none;
padding:10px 18px;
border-radius:12px;
font-weight:600;
cursor:pointer;
text-decoration:none;
transition:.3s
}

.btn:hover{
background:var(--primary-dark)
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

/* TABLA */

.table-card{
background:var(--card);
padding:20px;
border-radius:18px;
box-shadow:0 10px 30px rgba(0,0,0,.08);
overflow-x:auto;
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

.col-fecha{
white-space:nowrap;
font-weight:500;
}

.col-valor{
white-space:nowrap;
font-weight:600;
}

.col-estado{
white-space:nowrap;
}

/* EFECTO HOVER FILAS */

tbody tr{
transition: all .2s ease;
border-bottom:1px solid #e5e7eb;
}

tbody tr:hover{
background:#eef2ff;
cursor:pointer;
transform:scale(1.002);
box-shadow:0 2px 6px rgba(0,0,0,0.05);
}

/* MINIATURA */

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

/* BOTON VOLVER */

.back-container{
margin-top:20px;
display:flex;
justify-content:flex-end
}

/* FOOTER */

footer{
margin-top:60px;
padding:30px;
border-radius:20px;
background:linear-gradient(135deg,#1e3a8a,#2563eb);
color:white;
text-align:center;
font-weight:500
}

@media(max-width:1000px){
.dashboard{grid-template-columns:1fr}
}

.pdf-icon{
font-size:32px;
color:#dc2626;
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

/* BARRA SUPERIOR */

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

/* CONTENIDO */

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

.col-obs{
cursor:pointer;
min-width:200px;
}

.col-obs[contenteditable="true"]{
background:#fff7ed;
outline:2px dashed #fb923c;
}

.col-obs{
position:relative;
cursor:pointer;
text-align:left;
padding-right:30px;
}

.obs-content{
pointer-events:none;
}

.placeholder{
color:#94a3b8;
font-style:italic;
}

/* ICONO EDITAR */
.obs-icon{
position:absolute;
right:8px;
top:50%;
transform:translateY(-50%);
opacity:0;
transition:.2s;
}

.col-obs:hover .obs-icon{
opacity:1;
}

/* BLOQUEADO */
.obs-lock{
position:absolute;
right:8px;
top:50%;
transform:translateY(-50%);
opacity:.6;
}

/* ===== MODAL ===== */

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
border-radius:16px;
padding:20px;
box-shadow:0 10px 30px rgba(0,0,0,.2);
animation:fadeIn .2s ease;
}

@keyframes fadeIn{
from{transform:scale(.9);opacity:0}
to{transform:scale(1);opacity:1}
}

.modal textarea{
width:100%;
height:120px;
border:1px solid #e5e7eb;
border-radius:10px;
padding:10px;
resize:none;
font-family:inherit;
}

.modal-actions{
margin-top:15px;
display:flex;
justify-content:flex-end;
gap:10px;
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

.btn-save:hover{background:#1e40af}

/* ===== TOAST ===== */
.toast{
position:fixed;
top:20px;
right:20px;
background:#16a34a;
color:white;
padding:14px 18px;
border-radius:12px;
box-shadow:0 10px 25px rgba(0,0,0,.2);
font-size:14px;
display:flex;
align-items:center;
gap:10px;
opacity:0;
transform:translateY(-20px);
transition:.4s;
z-index:99999;
}

.toast.show{
opacity:1;
transform:translateY(0);
}

.toast.error{
background:#dc2626;
}

/* ICONO */
.toast i{
font-size:18px;
}

/* ===== ANIMACION CELDA ===== */
.save-success{
animation:flashSuccess .6s ease;
}

@keyframes flashSuccess{
0%{background:#bbf7d0;}
100%{background:transparent;}
}

/* ===== MODAL CONFIRM ===== */
.modal-confirm{
display:none;
position:fixed;
inset:0;
background:rgba(0,0,0,.6);
justify-content:center;
align-items:center;
z-index:99999;
}

.modal-confirm-box{
background:white;
padding:25px;
border-radius:16px;
width:380px;
text-align:center;
box-shadow:0 10px 30px rgba(0,0,0,.25);
animation:fadeIn .2s ease;
}

.modal-confirm h3{
margin-bottom:10px;
}

.modal-confirm p{
color:#64748b;
margin-bottom:20px;
}

.modal-confirm-actions{
display:flex;
justify-content:center;
gap:10px;
}

.btn-danger{
background:#dc2626;
color:white;
border:none;
padding:8px 14px;
border-radius:8px;
cursor:pointer;
}

.btn-danger:hover{
background:#b91c1c;
}

.col-valor-edit{
position:relative;
cursor:pointer;
text-align:center;
}

.col-valor-edit:hover{
background:#f1f5ff;
}

.valor-content{
pointer-events:none;
font-weight:600;
}

/* ===== VALOR EDITABLE ===== */

.editable-valor{
position:relative;
cursor:pointer;
white-space:nowrap;
padding-right:30px; /* 🔥 espacio para el icono */
}

.valor-content{
pointer-events:none;
font-weight:600;
display:inline-block;
padding-right:5px;
}

/* ICONO EDITAR */
.valor-icon{
position:absolute;
right:8px;
top:50%;
transform:translateY(-50%);
opacity:0;
transition:.2s;
}

.editable-valor:hover .valor-icon{
opacity:1;
}

/* BLOQUEADO */
.valor-lock{
position:absolute;
right:8px;
top:50%;
transform:translateY(-50%);
opacity:.6;
pointer-events:none; /* 🔥 evita interferir con clicks */
}

</style>
</head>

<body>

<?php include '../shared/menu.php'; ?>

<div class="banner-wrap">
<img src="banner.jpg" class="banner">
<div class="banner-overlay"></div>
<div class="banner-text">Historial de Multas</div>

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
<p>Total Multas</p>
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

<a href="exportar_multas.php?rut=<?= $rut ?>" target="_blank" class="btn">
📄 Exportar PDF
</a>

<div class="table-card">

<table>

<thead>
<tr>
<th>Emisión</th>
<th>Recepción</th>
<th>Pago</th>
<th>Observación</th>
<th>Valor</th>
<th>Estado</th>
<th>Imagen</th>
</tr>
</thead>

</thead>

<tbody>

<?php if($historial->num_rows > 0): ?>

<?php while($h = $historial->fetch_assoc()): ?>

<tr>

<td class="col-fecha">
<?= date('d-m-y', strtotime($h['fecha'])) ?>
</td>

<td class="col-fecha">
<?= !empty($h['fecha_recepcion']) ? date('d-m-y', strtotime($h['fecha_recepcion'])) : '-' ?>
</td>

<td class="col-fecha">
<?= !empty($h['fecha_pago']) ? date('d-m-y', strtotime($h['fecha_pago'])) : 'Pendiente' ?>
</td>

<td 
class="col-obs"
onclick="abrirModalObs(<?= $h['id'] ?>, this)"
data-editable="<?= empty($h['observacion']) ? '1' : '0' ?>"
>

<div class="obs-content">
<?= !empty($h['observacion']) ? $h['observacion'] : '<span class="placeholder">Sin observación</span>' ?>
</div>

<?php if(empty($h['observacion'])): ?>
<div class="obs-icon">✏️</div>
<?php else: ?>
<div class="obs-lock">🔒</div>
<?php endif; ?>

</td>


<td 
class="col-valor editable-valor"
onclick="abrirModalValor(<?= $h['id'] ?>, this)"
data-editable="<?= ($h['valor'] == 0) ? '1' : '0' ?>"
>

<div class="valor-content">
$<?= number_format($h['valor']) ?>
</div>

<?php if($h['valor'] == 0): ?>
<div class="valor-icon">✏️</div>
<?php else: ?>
<div class="valor-lock">🔒</div>
<?php endif; ?>

</td>

<td class="col-estado">

<?php if($h['estado'] == "Pagado"): ?>

    <div style="
        display:flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        font-weight:600;
        color:#16a34a;
    ">
        Pagado 🔒
    </div>

<?php else: ?>

    <form method="POST">

        <input type="hidden" name="id_multa" value="<?= $h['id'] ?>">

        <select name="estado" onchange="this.form.submit()">

            <option value="Pendiente" <?= $h['estado']=="Pendiente"?'selected':'' ?>>Pendiente</option>

            <option value="Pagado">Pagado</option>

            <option value="Apelado" <?= $h['estado']=="Apelado"?'selected':'' ?>>Apelado</option>

        </select>

        <input type="hidden" name="cambiar_estado">

    </form>

<?php endif; ?>

</td>

<td>

<?php if(!empty($h['imagen'])): ?>

<?php
$ext = strtolower(pathinfo($h['imagen'], PATHINFO_EXTENSION));
?>

<a href="#" onclick="abrirVisor('uploads/<?= $h['imagen'] ?>');return false;">

<?php if($ext == 'pdf'): ?>

<div class="pdf-icon">📄</div>

<?php else: ?>

<img src="uploads/<?= $h['imagen'] ?>" class="thumb">

<?php endif; ?>

</a>

<?php else: ?>

<span style="color:#64748b;">Sin archivo</span>

<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="id_multa" value="<?= $h['id'] ?>">

<input type="file" name="nueva_imagen" accept="image/*,.pdf">

<button type="submit" name="subir_imagen" class="btn-mini">
<?= !empty($h['imagen']) ? 'Reemplazar' : 'Subir' ?>
</button>

</form>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="7">Sin multas registradas</td>
</tr>

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
Sistema de Multas · <?= date('Y') ?>

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



let currentID = null;
let currentTD = null;

function abrirModalObs(id, td){

if(td.dataset.editable === "0"){
mostrarConfirm(
"Observación bloqueada",
"Esta observación ya fue registrada y no puede modificarse",
function(){}
);
return;
}

currentID = id;
currentTD = td;

document.getElementById("inputObs").value = "";
document.getElementById("modalObs").style.display = "flex";
}

function cerrarModal(){
document.getElementById("modalObs").style.display = "none";
}

function guardarObservacion(){

let texto = document.getElementById("inputObs").value.trim();

if(texto === ""){
mostrarToast("La observación no puede estar vacía","error");
return;
}

mostrarConfirm(
"Confirmar Observación",
"Una vez guardada no podrás modificarla",
function(){
guardarObsFinal();
}
);
return;

fetch("guardar_observacion.php", {
method: "POST",
headers: {
"Content-Type": "application/x-www-form-urlencoded"
},
body: `id=${currentID}&observacion=${encodeURIComponent(texto)}`
})
.then(res => res.text())
.then(r => {

if(r === "ok"){

// actualizar contenido
currentTD.querySelector(".obs-content").innerText = texto;

// quitar icono editar
currentTD.querySelector(".obs-icon")?.remove();

// agregar candado
let lock = document.createElement("div");
lock.className = "obs-lock";
lock.innerText = "🔒";
currentTD.appendChild(lock);

// animación visual
currentTD.classList.add("save-success");
setTimeout(()=> currentTD.classList.remove("save-success"),600);

// bloquear edición
currentTD.dataset.editable = "0";

// cerrar modal
cerrarModal();

// toast éxito
mostrarToast("Observación guardada correctamente ✅");

}else{

mostrarToast("No se pudo guardar","error");

}

})
.catch(()=>{
mostrarToast("Error de conexión","error");
});

}


function mostrarToast(msg, tipo="ok"){

let toast = document.getElementById("toast");
let text = document.getElementById("toastMsg");

text.innerText = msg;

toast.classList.remove("error");

if(tipo === "error"){
toast.classList.add("error");
}

toast.classList.add("show");

setTimeout(()=>{
toast.classList.remove("show");
},3000);

}

let confirmCallback = null;

function mostrarConfirm(titulo, mensaje, callback){

document.getElementById("confirmTitle").innerText = titulo;
document.getElementById("confirmMsg").innerText = mensaje;

confirmCallback = callback;

document.getElementById("modalConfirm").style.display = "flex";

}

function cerrarConfirm(){
document.getElementById("modalConfirm").style.display = "none";
}

function confirmarAccion(){
if(confirmCallback) confirmCallback();
cerrarConfirm();
}

function guardarObsFinal(){

fetch("guardar_observacion.php", {
method: "POST",
headers: {
"Content-Type": "application/x-www-form-urlencoded"
},
body: `id=${currentID}&observacion=${encodeURIComponent(document.getElementById("inputObs").value.trim())}`
})
.then(res => res.text())
.then(r => {

if(r === "ok"){

currentTD.querySelector(".obs-content").innerText =
document.getElementById("inputObs").value.trim();

currentTD.querySelector(".obs-icon")?.remove();

let lock = document.createElement("div");
lock.className = "obs-lock";
lock.innerText = "🔒";
currentTD.appendChild(lock);

currentTD.classList.add("save-success");
setTimeout(()=> currentTD.classList.remove("save-success"),600);

currentTD.dataset.editable = "0";

cerrarModal();

mostrarToast("Observación guardada correctamente ✅");

}else{
mostrarToast("No se pudo guardar","error");
}

})
.catch(()=>{
mostrarToast("Error de conexión","error");
});

}


let currentValorID = null;
let currentValorTD = null;

function abrirModalValor(id, td){

if(td.dataset.editable === "0"){
mostrarConfirm(
"Valor bloqueado",
"Este valor ya fue registrado y no puede modificarse",
function(){}
);
return;
}

currentValorID = id;
currentValorTD = td;

document.getElementById("inputValor").value = "";
document.getElementById("modalValor").style.display = "flex";
}

function cerrarModalValor(){
document.getElementById("modalValor").style.display = "none";
}

function guardarValor(){

let valor = document.getElementById("inputValor").value.trim();

if(valor === "" || valor <= 0){
mostrarToast("Ingrese un valor válido","error");
return;
}

mostrarConfirm(
"Confirmar Valor",
"Una vez guardado no podrás modificarlo",
function(){
guardarValorFinal(valor);
}
);

}

function guardarValorFinal(valor){

fetch("guardar_valor.php", {
method: "POST",
headers: {
"Content-Type": "application/x-www-form-urlencoded"
},
body: `id=${currentValorID}&valor=${valor}`
})
.then(res => res.text())
.then(r => {

if(r === "ok"){


// actualizar contenido
currentValorTD.querySelector(".valor-content").innerText =
"$" + Number(valor).toLocaleString();

// quitar lápiz
currentValorTD.querySelector(".valor-icon")?.remove();

// agregar candado
let lock = document.createElement("div");
lock.className = "valor-lock";
lock.innerText = "🔒";
currentValorTD.appendChild(lock);

// animación
currentValorTD.classList.add("save-success");
setTimeout(()=> currentValorTD.classList.remove("save-success"),600);

// bloquear
currentValorTD.dataset.editable = "0";

// cerrar modal
cerrarModalValor();

// toast
mostrarToast("Valor guardado correctamente 💰");

}else{
mostrarToast("No se pudo guardar","error");
}

})
.catch(()=>{
mostrarToast("Error de conexión","error");
});

}

</script>

<!-- VISOR DOCUMENTOS PRO -->

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


<div id="modalObs" class="modal">
  <div class="modal-box">
    
    <h3 style="margin-bottom:10px">Agregar Observación</h3>
    
    <textarea id="inputObs" placeholder="Escribe el detalle de la multa..."></textarea>
    
    <div class="modal-actions">
      <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
      <button class="btn-save" onclick="guardarObservacion()">Guardar</button>
    </div>

  </div>
</div>

<div id="toast" class="toast">
  <span id="toastMsg">Guardado</span>
</div>


<div id="modalConfirm" class="modal-confirm">
  <div class="modal-confirm-box">
    
    <h3 id="confirmTitle">Confirmar acción</h3>
    <p id="confirmMsg">¿Estás seguro?</p>

    <div class="modal-confirm-actions">
      <button class="btn-cancel" onclick="cerrarConfirm()">Cancelar</button>
      <button class="btn-danger" onclick="confirmarAccion()">Confirmar</button>
    </div>

  </div>
</div>

<div id="modalValor" class="modal">
  <div class="modal-box">
    
    <h3 style="margin-bottom:10px">Agregar Valor</h3>
    
    <input 
      type="number" 
      id="inputValor" 
      placeholder="Ingrese monto..." 
      style="width:100%;padding:10px;border-radius:10px;border:1px solid #e5e7eb"
    >
    
    <div class="modal-actions">
      <button class="btn-cancel" onclick="cerrarModalValor()">Cancelar</button>
      <button class="btn-save" onclick="guardarValor()">Guardar</button>
    </div>

  </div>
</div>

</body>
</html>