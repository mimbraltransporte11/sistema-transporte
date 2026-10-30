<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db.php';

/* ================= BACKEND ================= */


// CAMBIAR ESTADO
if(isset($_POST['cambiar_estado'])){

$id = $_POST['conductor_id'];
$estado = $_POST['nuevo_estado'];

// actualizar estado
$conn->query("UPDATE conductores SET estado='$estado' WHERE id=$id");

// 🔥 SI NO ESTÁ DISPONIBLE → liberar equipos
if(in_array($estado, ['LICENCIA','VACACIONES','DESVINCULADO','OCUPADO'])){

    $conn->query("
    UPDATE asignaciones 
    SET fecha_fin = NOW()
    WHERE conductor_id = $id 
    AND fecha_fin IS NULL
    ");

}

header("Location:index.php");
exit;
}


// GUARDAR CONDUCTOR
if(isset($_POST['guardar_conductor'])){
$conn->query("INSERT INTO conductores (rut,nombre,area)
VALUES ('$_POST[rut]','$_POST[nombre]','$_POST[area]')");
header("Location:index.php?success=conductor");
exit;
}

// GUARDAR EQUIPO (CON VALIDACIÓN)
if(isset($_POST['guardar_equipo'])){

$ppu = $_POST['ppu'];

$check = $conn->query("SELECT * FROM equipos WHERE ppu='$ppu'");

if($check->num_rows > 0){
header("Location: index.php?error=ppu_existe");
exit;
}

$conn->query("INSERT INTO equipos (ppu,tipo,configuracion,revision_tecnica)
VALUES ('$ppu','$_POST[tipo]','$_POST[configuracion]','$_POST[revision_tecnica]')");

header("Location:index.php?success=equipo");
exit;
}

// ASIGNAR EQUIPO
if(isset($_POST['asignar'])){

$equipo = $_POST['equipo'];
$conductor = $_POST['conductor'];

if(empty($_POST['equipo'])){
    header("Location:index.php?error=sin_equipo");
    exit;
}


// 🔹 OBTENER ESTADO DEL CONDUCTOR (CORRECTO)
$resEstado = $conn->query("SELECT estado FROM conductores WHERE id=$conductor");
$estado = $resEstado->fetch_assoc()['estado'];

// 🔹 VALIDAR DISPONIBILIDAD
if($estado != 'DISPONIBLE'){
    header("Location:index.php?error=no_disponible");
    exit;
}
// 🔹 OBTENER TIPO EQUIPO PRIMERO
$r = $conn->query("SELECT tipo FROM equipos WHERE id=$equipo")->fetch_assoc();
$tipoBD = $r['tipo'];

if(in_array($tipoBD, ['TRACTO','CAMION'])){
    $tipo_equipo = 'TRACTO';
}else{
    $tipo_equipo = 'ACOPLADO';
}


// 🔹 VALIDAR SI EL EQUIPO YA ESTÁ ASIGNADO
$checkEquipo = $conn->query("
SELECT conductor_id FROM asignaciones 
WHERE equipo_id = $equipo 
AND fecha_fin IS NULL
");

if($checkEquipo->num_rows > 0){

    $rowEq = $checkEquipo->fetch_assoc();

    if($rowEq['conductor_id'] != $conductor){
        header("Location:index.php?error=equipo_ocupado");
        exit;
    }

    // ✅ mismo conductor → se permite
}

// 🔹 CERRAR ASIGNACIÓN DEL EQUIPO
$conn->query("
UPDATE asignaciones 
SET fecha_fin = NOW() 
WHERE equipo_id = $equipo 
AND fecha_fin IS NULL
");

// 🔹 CERRAR ASIGNACIÓN DEL CONDUCTOR
$conn->query("
UPDATE asignaciones 
SET fecha_fin=NOW() 
WHERE conductor_id=$conductor 
AND tipo_equipo='$tipo_equipo' 
AND fecha_fin IS NULL
");

// 🔹 NUEVA ASIGNACIÓN
$conn->query("
INSERT INTO asignaciones (conductor_id,equipo_id,tipo_equipo,fecha_inicio)
VALUES ($conductor,$equipo,'$tipo_equipo',NOW())
");



header("Location:index.php");
exit;
}


if(isset($_POST['importar'])){

  if($_FILES['archivo']['error'] == 0){

    // ✅ ABRIR ARCHIVO PRIMERO
    $archivo = fopen($_FILES['archivo']['tmp_name'], "r");

    // ✅ DETECTAR DELIMITADOR
    $linea = fgets($archivo);
    $delimitador = (substr_count($linea, ";") > substr_count($linea, ",")) ? ";" : ",";
    rewind($archivo);

    // ✅ SALTAR ENCABEZADO
    fgetcsv($archivo, 1000, $delimitador);

    // ✅ LEER DATOS
    while(($data = fgetcsv($archivo, 1000, $delimitador)) !== FALSE){

      if(count($data) < 4) continue;

      $ppu = strtoupper(trim($data[0]));
      $tipo = strtoupper(trim($data[1]));
      $config = strtoupper(trim($data[2]));
      $rev = trim($data[3]);

      // 🚨 FILTRO
      if(
        empty($ppu) ||
        strlen($ppu) < 5 ||
        str_contains($ppu, "<") ||
        str_contains($ppu, "meta") ||
        !preg_match('/^[A-Z0-9]+$/', $ppu)
      ){
        continue;
      }

      // evitar duplicados
      $check = $conn->query("SELECT id FROM equipos WHERE ppu='$ppu'");
      if($check->num_rows > 0){
        continue;
      }

      // insertar
      $conn->query("INSERT INTO equipos (ppu,tipo,configuracion,revision_tecnica)
      VALUES ('$ppu','$tipo','$config','$rev')");
    }

    fclose($archivo);

    header("Location:index.php?success=importado");
    exit;
  }
}


// ❌ QUITAR EQUIPO
if(isset($_POST['quitar_equipo'])){

  $conductor = $_POST['conductor_id'];
  $tipo = $_POST['tipo_equipo']; // TRACTO o ACOPLADO

  $conn->query("
    UPDATE asignaciones
    SET fecha_fin = NOW()
    WHERE conductor_id = $conductor
    AND tipo_equipo = '$tipo'
    AND fecha_fin IS NULL
  ");

  header("Location:index.php?success=equipo_quitado");
  exit;
}

/* ================= CONSULTA ================= */

$res = $conn->query("
SELECT 
c.id,
c.rut,
c.nombre,
c.estado,

t.ppu AS tracto,
t.tipo AS tipo_tracto,
t.configuracion AS config_tracto,

a2.ppu AS acoplado,
a2.tipo AS tipo_acoplado,
a2.configuracion AS config_acoplado

FROM conductores c

LEFT JOIN asignaciones at 
  ON c.id = at.conductor_id 
  AND at.tipo_equipo='TRACTO' 
  AND at.fecha_fin IS NULL

LEFT JOIN equipos t ON at.equipo_id = t.id

LEFT JOIN asignaciones aa 
  ON c.id = aa.conductor_id 
  AND aa.tipo_equipo='ACOPLADO' 
  AND aa.fecha_fin IS NULL

LEFT JOIN equipos a2 ON aa.equipo_id = a2.id


");

$verDesvinculados = isset($_GET['ver_desvinculados']);

if($verDesvinculados){
    $where = "WHERE c.estado = 'DESVINCULADO'";
}else{
    $where = "WHERE c.estado != 'DESVINCULADO'";
}

$res = $conn->query("
SELECT 
c.id,
c.rut,
c.nombre,
c.estado,

t.ppu AS tracto,
t.tipo AS tipo_tracto,
t.configuracion AS config_tracto,

a2.ppu AS acoplado,
a2.tipo AS tipo_acoplado,
a2.configuracion AS config_acoplado

FROM conductores c

LEFT JOIN asignaciones at 
  ON c.id = at.conductor_id 
  AND at.tipo_equipo='TRACTO' 
  AND at.fecha_fin IS NULL

LEFT JOIN equipos t ON at.equipo_id = t.id

LEFT JOIN asignaciones aa 
  ON c.id = aa.conductor_id 
  AND aa.tipo_equipo='ACOPLADO' 
  AND aa.fecha_fin IS NULL

LEFT JOIN equipos a2 ON aa.equipo_id = a2.id

$where
ORDER BY c.nombre ASC

");

// ================= REVISIONES TECNICAS =================

$mesSeleccionado = isset($_GET['mes']) 
    ? intval($_GET['mes']) 
    : intval(date('m'));

$anioSeleccionado = isset($_GET['anio']) 
    ? intval($_GET['anio']) 
    : intval(date('Y'));

// inicio del rango
$inicioMes = date(
    'Y-m-01',
    strtotime("$anioSeleccionado-$mesSeleccionado-01")
);

// fin = mes actual
$finMes = date(
    'Y-m-10',
    strtotime('+1 month')
);

$revTecnicas = $conn->query("
SELECT 
e.ppu,
e.tipo,
e.revision_tecnica,
c.nombre AS conductor

FROM equipos e

LEFT JOIN asignaciones a 
  ON e.id = a.equipo_id 
  AND a.fecha_fin IS NULL

LEFT JOIN conductores c 
  ON a.conductor_id = c.id

WHERE e.revision_tecnica BETWEEN '$inicioMes' AND '$finMes'

ORDER BY e.revision_tecnica ASC
");

?>

<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" href="favicon.png">
<meta charset="UTF-8">
<title>Sistema Asignación</title>

<style>
:root{
--primary:#2563eb;
--danger:#dc2626;
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

/* ===== CARD ===== */
.card{
background:white;
padding:25px;
border-radius:18px;
box-shadow:0 10px 30px rgba(0,0,0,.1);
}

/* ===== TOP ===== */
.top{
display:flex;
justify-content:space-between;
margin-bottom:15px;
gap:10px;
}

.top input{
width:320px;
padding:10px;
border-radius:10px;
border:1px solid #d1d5db;
}

/* ===== BOTONES ===== */
button{
background:linear-gradient(135deg,#2563eb,#1e40af);
color:white;
border:none;
padding:10px 14px;
border-radius:10px;
cursor:pointer;
transition:.2s;
}

button:hover{
transform:translateY(-1px);
}

.btn-danger{
background:#dc2626;
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

/* ===== MODAL ===== */
.modal{
display:none;
position:fixed;
inset:0;
background:rgba(15,23,42,.55);
justify-content:center;
align-items:center;
}

/* ===== MODAL MEJORADO ===== */

.modal-content{
  background:white;
  padding:30px 25px;
  border-radius:20px;
  width:420px;
  box-shadow:0 20px 40px rgba(0,0,0,.25);
  animation:fadeIn .25s ease;
}

/* Animación */
@keyframes fadeIn{
  from{
    opacity:0;
    transform:translateY(20px);
  }
  to{
    opacity:1;
    transform:translateY(0);
  }
}

/* Título */
.modal-content h3{
  margin-top:0;
  margin-bottom:20px;
  font-size:20px;
  color:#1e293b;
}

/* Inputs y selects */
.modal-content input,
.modal-content select{
  width:100%;
  padding:12px;
  margin-bottom:14px;
  border-radius:10px;
  border:1px solid #d1d5db;
  font-size:14px;
  transition:.2s;
}

/* Focus bonito */
.modal-content input:focus,
.modal-content select:focus{
  border-color:#2563eb;
  outline:none;
  box-shadow:0 0 0 3px rgba(37,99,235,.2);
}

/* Botón guardar */
.modal-content button{
  width:100%;
  padding:12px;
  font-weight:600;
  border-radius:12px;
  margin-top:10px;
}

/* Etiquetas (opcional pero recomendado) */
.modal-content label{
  font-size:13px;
  color:#64748b;
  margin-bottom:4px;
  display:block;
}

/* ===== AUTOCOMPLETE ===== */
#resultadosConductores,#resultadosEquipos{
background:white;
max-height:160px;
overflow-y:auto;
box-shadow:0 10px 20px rgba(0,0,0,.2);
}

.item,.itemEq{
padding:8px;
cursor:pointer;
}

.item:hover,.itemEq:hover{
background:#f1f5f9;
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

.btn-historial {
  display: inline-block;
  padding: 8px 14px;
  background: linear-gradient(135deg,#2563eb,#1e40af);
  color: white;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 500;
  position: relative; /* ⚡ para el pseudo-elemento */
  transition: transform 0.2s, background 0.2s;
}

.btn-historial::after {
  content: '';
  position: absolute;
  left: 8px; /* mismo padding horizontal */
  right: 8px;
  bottom: 4px; /* un poco dentro del botón */
  height: 2px;
  background: white;
  border-radius: 1px;
}

.btn-historial:hover {
  transform: translateY(-1px);
  background: linear-gradient(135deg,#1e40af,#2563eb);
}

/* ===== TOAST NOTIFICACIÓN ===== */
.toast{
  position:fixed;
  top:20px;
  right:20px;
  background:#dc2626;
  color:white;
  padding:14px 18px;
  border-radius:12px;
  box-shadow:0 10px 25px rgba(0,0,0,.2);
  font-weight:500;
  opacity:0;
  transform:translateY(-20px);
  transition:.3s;
  z-index:9999;
}

.toast.show{
  opacity:1;
  transform:translateY(0);
}

.asignar-link{
  color:#2563eb;
  font-weight:600;
  text-decoration:none;
}
.asignar-link:hover{
  text-decoration:underline;
}


.btn-danger{
  background:#dc2626;
  border:none;
  border-radius:6px;
  padding:4px 6px;
  font-size:11px;
  cursor:pointer;
}


/* Modal grande */
.modal-lg{
  width: 700px;
  max-width: 90%;
}

/* Contenedor con scroll */
.tabla-scroll{
  max-height: 400px;
  overflow-y: auto;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

/* Tabla bonita */
.tabla-rev{
  width: 100%;
  border-collapse: collapse;
}

.tabla-rev th{
  position: sticky;
  top: 0;
  background: #1e40af;
  color: white;
  font-size: 12px;
  padding: 10px;
}

.tabla-rev td{
  padding: 10px;
  border-bottom: 1px solid #e5e7eb;
}

/* Hover */
.tabla-rev tr:hover{
  background: #f1f5f9;
}

/* Diferenciar estado */
.tabla-rev td:last-child{
  font-weight: 600;
}

/* SITIO en gris */
.tabla-rev td:last-child:contains("SITIO"){
  color: #64748b;
}

/* ==========================
   MODAL CONFIRMACIÓN QUITAR
========================== */

.confirm-overlay{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.65);
    backdrop-filter:blur(4px);
    justify-content:center;
    align-items:center;
    z-index:10000;
}

.confirm-box{
    width:420px;
    max-width:90%;
    background:white;
    border-radius:22px;
    padding:30px;
    text-align:center;
    box-shadow:0 25px 60px rgba(0,0,0,.30);
    animation:confirmIn .25s ease;
}

@keyframes confirmIn{
    from{
        opacity:0;
        transform:scale(.90) translateY(10px);
    }
    to{
        opacity:1;
        transform:scale(1) translateY(0);
    }
}

.confirm-icon{
    width:65px;
    height:65px;
    margin:0 auto 18px;
    border-radius:50%;
    background:#fef3c7;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:32px;
}

.confirm-box h3{
    margin:0 0 10px;
    color:#1e293b;
    font-size:21px;
}

.confirm-box p{
    margin:0 0 25px;
    color:#64748b;
    font-size:14px;
    line-height:1.5;
}

.confirm-buttons{
    display:flex;
    gap:10px;
    justify-content:center;
}

.confirm-buttons button{
    flex:1;
    padding:11px 15px;
    border-radius:11px;
    font-weight:600;
    border:none;
    cursor:pointer;
}

.btn-cancelar{
    background:#e5e7eb !important;
    color:#374151 !important;
}

.btn-cancelar:hover{
    background:#d1d5db !important;
}

.btn-confirmar-quitar{
    background:linear-gradient(135deg,#dc2626,#b91c1c) !important;
    color:white !important;
}

.btn-confirmar-quitar:hover{
    background:linear-gradient(135deg,#b91c1c,#991b1b) !important;
}


</style>
</head>

<body>

<div id="toast" class="toast"></div>

<!-- ==========================
     CONFIRMACIÓN QUITAR EQUIPO
========================== -->

<div class="confirm-overlay" id="confirmQuitar">

    <div class="confirm-box">

        <div class="confirm-icon">
            ⚠️
        </div>

        <h3>¿Quitar equipo?</h3>

        <p id="confirmMensaje">
            ¿Estás seguro de que deseas quitar este equipo?
        </p>

        <div class="confirm-buttons">

            <button type="button"
                    class="btn-cancelar"
                    id="btnCancelarQuitar">
                Cancelar
            </button>

            <button type="button"
                    class="btn-confirmar-quitar"
                    id="btnConfirmarQuitar">
                Sí, quitar equipo
            </button>

        </div>

    </div>

</div>

<?php include '../shared/menu.php'; ?>

<div class="banner-wrap">

<img src="banner.jpg" class="banner">

<div class="banner-overlay"></div>

<div class="banner-text">
<h1>Gestión de Asignaciones</h1>
<p>Control de equipos y conductores</p>
</div>

<div class="banner-datetime">
<div id="banner-date"></div>
<div id="banner-time"></div>
</div>

</div>

<!-- SOLO PARA SUBIR ARCHIVOS
 <form method="POST" enctype="multipart/form-data">
  <input type="file" name="archivo">
  <button name="importar">Importar CSV</button>
</form> 
-->

<div class="card">

<h2>Asignación de Equipos</h2>

<div class="top">
<input type="text" id="buscador" placeholder="Buscar...">

<div>
<button onclick="abrirConductor()">Agregar conductor</button>
<button onclick="abrirEquipo()">Agregar equipo</button>
<?php if($verDesvinculados): ?>
  <a href="index.php">
    <button>👁  Ocultar desvinculados</button>
  </a>
<?php else: ?>
  <a href="index.php?ver_desvinculados=1">
    <button>👁 Ver desvinculados</button>
  </a>
<?php endif; ?>
<a href="historial_equipos.php">
  <button>Historial Equipos</button>
</a>
<button onclick="abrirRevTecnica()">Rev. Técnicas</button>
</div>
</div>

<table>
<tr>
<th>RUT</th>
<th>Nombre</th>
<th>Estado</th>
<th>Equipo</th>
<th>Tipo</th>
<th>Config</th>
<th>Acoplado</th>
<th>Tipo</th>
<th>Config</th>
<th>Historial</th>
</tr>

<tbody id="tabla">
<?php while($row=$res->fetch_assoc()): ?>

<tr>
<td><?= $row['rut'] ?></td>
<td><?= $row['nombre'] ?></td>

<td>
<?php
$enRuta = !empty($row['tracto']) || !empty($row['acoplado']);

$estadoMostrar = $row['estado'];

if($row['estado'] == 'DISPONIBLE' && $enRuta){
  $estadoMostrar = 'EN RUTA';
}

$color = match($estadoMostrar){
  'DISPONIBLE' => '#16a34a',
  'EN RUTA' => '#2563eb',
  'LICENCIA' => '#f59e0b',
  'VACACIONES' => '#f59e0b',
  'DESVINCULADO' => '#dc2626',
  default => '#9ca3af'
};
?>

<span class="estado-btn"
      data-id="<?= $row['id'] ?>"
      data-estado="<?= $row['estado'] ?>"
      style="cursor:pointer;color:white; background:<?= $color ?>; padding:5px 10px; border-radius:8px; font-size:12px;">
<?= $estadoMostrar ?>
</span>
</td>

<td>
<?php if(!empty($row['tracto'])): ?>

  <?= $row['tracto'] ?>

 <form method="POST" style="display:inline;">

    <input type="hidden" name="quitar_equipo" value="1">
    <input type="hidden" name="conductor_id" value="<?= $row['id'] ?>">
    <input type="hidden" name="tipo_equipo" value="TRACTO">

    <button type="button"
            class="btn-danger btn-quitar"
            data-equipo="<?= htmlspecialchars($row['tracto']) ?>">
        ❌
    </button>

</form>

<?php else: ?>
  <a href="#" class="ppu-btn asignar-link" data-tipo="TRACTO" data-id="<?= $row['id'] ?>">
    ➕ equipo
  </a>
<?php endif; ?>
</td>

<td><?= $row['tipo_tracto'] ?></td>
<td><?= $row['config_tracto'] ?></td>

<td>

<?php if(!empty($row['acoplado'])): ?>

    <?= $row['acoplado'] ?>

    <form method="POST" style="display:inline;">

        <input type="hidden" name="quitar_equipo" value="1">
        <input type="hidden" name="conductor_id" value="<?= $row['id'] ?>">
        <input type="hidden" name="tipo_equipo" value="ACOPLADO">

        <button type="button"
                class="btn-danger btn-quitar"
                data-equipo="<?= htmlspecialchars($row['acoplado']) ?>">
            ❌
        </button>

    </form>

<?php else: ?>

    <a href="#"
       class="ppu-btn asignar-link"
       data-tipo="ACOPLADO"
       data-id="<?= $row['id'] ?>">
        ➕ equipo
    </a>

<?php endif; ?>
</td>

<td><?= $row['tipo_acoplado'] ?></td>
<td><?= $row['config_acoplado'] ?></td>

<td>
  <a href="historial.php?id=<?= $row['id'] ?>" class="btn-historial">
    📄 Historial
  </a>
</td>

</tr>

<?php endwhile; ?>
</tbody>

</table>
</div>

<!-- MODAL CONDUCTOR -->
<div class="modal" id="modalConductor">
<div class="modal-content">
<form method="POST">
<h3>Nuevo Conductor</h3>
<input name="rut" placeholder="RUT">
<input name="nombre" placeholder="Nombre">
<input name="area" placeholder="Área">
<button name="guardar_conductor">Guardar</button>
</form>
</div>
</div>

<!-- MODAL EQUIPO -->
<div class="modal" id="modalEquipo">
  <div class="modal-content">
    <form method="POST">

      <h3> Nuevo Equipo</h3>

      <label>PPU</label>
      <input name="ppu" placeholder="Ej: AB1234" required>

      <label>Tipo de equipo</label>
      <select name="tipo" required>
        <option value="" disabled selected>Seleccionar tipo</option>
        <option>TRACTO</option>
        <option>CAMION</option>
        <option>RAMPLA</option>
        <option>CORTINA</option>
        <option>CAMA BAJA</option>
         <option>BATEA</option>
        <option>ESTANQUE</option>
        <option>CHASIS</option>
        <option>CARRO</option>
      </select>

      <label>Configuración</label>
      <select name="configuracion" id="configuracion" required>
        <option value="" disabled selected>Seleccionar configuración</option>
      </select>

      <label>Revisión técnica</label>
      <input type="date" name="revision_tecnica">

      <button name="guardar_equipo">💾 Guardar Equipo</button>

    </form>
  </div>
</div>

<!-- MODAL ASIGNAR MEJORADO -->
 <form method="POST" class="form-scroll">
<div class="modal" id="modalAsignar">
<div class="modal-content">

<h3>Asignar Equipo</h3>

<form method="POST">

<!-- BUSCAR CONDUCTOR -->
<input type="text" id="buscarConductor" placeholder="Buscar conductor por nombre o rut">

<div id="resultadosConductores"></div>

<input type="hidden" name="conductor" id="conductor_id">

<!-- BUSCAR EQUIPO -->
<input type="text" id="buscarEquipo" placeholder="Buscar equipo por PPU">

<div id="resultadosEquipos"></div>

<input type="hidden" name="equipo" id="equipo_id">

<button name="asignar" disabled id="btnAsignar">Asignar</button>

</form>

</div>
</div>


<!-- MODAL ASIGNAR POR PPU -->
 <form method="POST" class="form-scroll">
<div class="modal" id="modalAsignarPPU">
  <div class="modal-content">
    <h3>Registrar Equipo</h3>
    <form method="POST">
      
      <!-- Conductor ya determinado por la fila -->
      <input type="hidden" name="conductor" id="conductor_id_modal">

      <!-- Buscar PPU -->
      <input type="text" id="buscarPPU" placeholder="Buscar equipo por PPU">
      <div id="resultadosPPU"></div>
      
      <input type="hidden" name="equipo" id="equipo_id_modal">

      <button name="asignar">Guardar</button>
    </form>
  </div>
</div>



<div class="modal" id="modalEstado">
  <div class="modal-content">
    <h3>Cambiar Estado</h3>

    <form method="POST">
      <input type="hidden" name="conductor_id" id="estado_conductor_id">

      <select name="nuevo_estado" required>
        <option value="">Seleccionar estado</option>
        <option>DISPONIBLE</option>
        <option>LICENCIA</option>
        <option>VACACIONES</option>
        <option>DESVINCULADO</option>
      </select>

      <button name="cambiar_estado">Guardar</button>
    </form>
  </div>
</div>


<div class="modal" id="modalRevTecnica">

  <div class="modal-content modal-lg">

    <h3>Revisiones Técnicas</h3>

    <!-- FILTROS -->
    <div style="display:flex; gap:10px; margin-bottom:15px;">

      <select id="filtroMes">

        <?php
        $meses = [
          1=>"Enero",
          2=>"Febrero",
          3=>"Marzo",
          4=>"Abril",
          5=>"Mayo",
          6=>"Junio",
          7=>"Julio",
          8=>"Agosto",
          9=>"Septiembre",
          10=>"Octubre",
          11=>"Noviembre",
          12=>"Diciembre"
        ];

        foreach($meses as $num=>$nombre):
        ?>

        <option value="<?= $num ?>"
          <?= $mesSeleccionado == $num ? 'selected' : '' ?>>
          <?= $nombre ?>
        </option>

        <?php endforeach; ?>

      </select>

      <select id="filtroAnio">

        <?php for($a = date('Y') - 3; $a <= date('Y') + 1; $a++): ?>

          <option value="<?= $a ?>"
            <?= $anioSeleccionado == $a ? 'selected' : '' ?>>

            <?= $a ?>

          </option>

        <?php endfor; ?>

      </select>

    </div>

    <div class="tabla-scroll">

      <table class="tabla-rev">

        <thead>
          <tr>
            <th>Patente</th>
            <th>Tipo</th>
            <th>Asignado</th>
            <th>Vencimiento</th>
          </tr>
        </thead>

        <tbody id="tablaRevTecnicas">

        <?php while($row = $revTecnicas->fetch_assoc()): ?>

        <tr>

          <td><?= $row['ppu'] ?></td>

          <td><?= $row['tipo'] ?></td>

          <td>
            <?php if($row['conductor']): ?>

              <span style="color:#16a34a">
                <?= $row['conductor'] ?>
              </span>

            <?php else: ?>

              <span style="color:#64748b">
                SITIO
              </span>

            <?php endif; ?>
          </td>

          <td>
            <?= date('d-m-Y', strtotime($row['revision_tecnica'])) ?>
          </td>

        </tr>

        <?php endwhile; ?>

        </tbody>

      </table>

    </div>

  </div>

</div>


<script>

// BUSCADOR
document.getElementById("buscador").addEventListener("keyup", function(){
let f=this.value.toLowerCase();
document.querySelectorAll("#tabla tr").forEach(tr=>{
const texto = tr.innerText.toLowerCase();

  tr.style.display = texto.includes(f) ? "" : "none";
})
});


// MODALES
function abrirConductor(){modalConductor.style.display='flex';}
function abrirEquipo(){modalEquipo.style.display='flex';}
function abrirAsignar(){modalAsignar.style.display='flex';}

window.onclick=e=>{
document.querySelectorAll('.modal').forEach(m=>{
if(e.target===m){m.style.display='none'}
});
}


// BUSCAR CONDUCTOR
const buscarConductor = document.getElementById("buscarConductor");
const resultadosConductores = document.getElementById("resultadosConductores");

buscarConductor.addEventListener("keyup", () => {

let val = buscarConductor.value;

if(val.length < 2){
resultadosConductores.innerHTML = "";
return;
}

fetch("buscar_conductor.php?q=" + val)
.then(res => res.text())
.then(data => resultadosConductores.innerHTML = data);

});

// SELECCIONAR CONDUCTOR
resultadosConductores.addEventListener("click", e => {

let item = e.target.closest(".item");
if(!item) return;

document.getElementById("conductor_id").value = item.dataset.id;
buscarConductor.value = item.dataset.nombre;
resultadosConductores.innerHTML = "";

});


// BUSCAR EQUIPO
const buscarEquipo = document.getElementById("buscarEquipo");
const resultadosEquipos = document.getElementById("resultadosEquipos");

buscarEquipo.addEventListener("keyup", () => {

let val = buscarEquipo.value;

if(val.length < 2){
resultadosEquipos.innerHTML = "";
return;
}

fetch("buscar_equipo.php?q=" + val)
.then(res => res.text())
.then(data => resultadosEquipos.innerHTML = data);

});

// SELECCIONAR EQUIPO
resultadosEquipos.addEventListener("click", e => {

let item = e.target.closest(".itemEq");
if(!item) return;

document.getElementById("equipo_id").value = item.dataset.id;
buscarEquipo.value = item.dataset.ppu;

resultadosEquipos.innerHTML = "";

});

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



// REFERENCIAS
const modalPPU = document.getElementById('modalAsignarPPU');
const buscarPPU = document.getElementById('buscarPPU');
const resultadosPPU = document.getElementById('resultadosPPU');
const conductor_id_modal = document.getElementById('conductor_id_modal');
const equipo_id_modal = document.getElementById('equipo_id_modal');

// Click en PPU de la tabla
document.querySelectorAll('.ppu-btn').forEach(btn => {
  btn.addEventListener('click', e => {

    e.preventDefault();

    const fila = btn.closest("tr");
    const estado = fila.querySelector('.estado-btn').dataset.estado;

    // SOLO bloquear estados reales
    if(["LICENCIA","VACACIONES","DESVINCULADO"].includes(estado)){
      mostrarToast("⚠️ Conductor no disponible", "#f59e0b");
      return;
    }

    // ✅ permitir DISPONIBLE aunque esté EN RUTA
    const tipo = btn.dataset.tipo;
    const conductorId = btn.dataset.id;

    modalPPU.style.display = 'flex';
    conductor_id_modal.value = conductorId;

    buscarPPU.placeholder = `Buscar ${tipo} por PPU`;

    buscarPPU.value = '';
    resultadosPPU.innerHTML = '';

    buscarPPU.dataset.tipo = tipo;
  });
});

// Cerrar modal al click fuera
window.addEventListener('click', e => {
  if(e.target === modalPPU) modalPPU.style.display = 'none';
});


buscarPPU.addEventListener('keyup', () => {
  const val = buscarPPU.value;
  const tipo = buscarPPU.dataset.tipo;

  if(val.length < 2){
    resultadosPPU.innerHTML = '';
    return;
  }

  fetch(`buscar_equipo.php?q=${val}&tipo=${tipo}`)
    .then(res => res.text())
    .then(data => resultadosPPU.innerHTML = data);
});

// Seleccionar equipo del resultado
resultadosPPU.addEventListener('click', e => {
  const item = e.target.closest('.itemEq');
  if(!item) return;

  equipo_id_modal.value = item.dataset.id;
  buscarPPU.value = item.dataset.ppu;
  resultadosPPU.innerHTML = '';
});

const tipoSelect = document.querySelector('select[name="tipo"]');
const configSelect = document.getElementById('configuracion');

const configuraciones = {

  "TRACTO": ["DOBLE PUENTE","SENCILLO"],
  "CAMION": ["DOBLE PUENTE","SENCILLO"],

  "RAMPLA": ["NW","TRIPLE","1+1"],
  "CORTINA": ["NW","1+1","TRIPLE"],
  "CAMA BAJA": ["NW","TRIPLE"],
  "BATEA": ["NW","TRIPLE"],

  "CHASIS": ["CHASIS"],

   "CARRO": ["CARRO"],

  "ESTANQUE": ["3 DIV","2 DIV","1 DIV","SIN DIV"]
};

tipoSelect.addEventListener("change", () => {

  const tipo = tipoSelect.value;

  // limpiar opciones
  configSelect.innerHTML = '<option value="" disabled selected>Seleccionar configuración</option>';

  if(configuraciones[tipo]){
    configuraciones[tipo].forEach(cfg => {

      let option = document.createElement("option");
      option.value = cfg;
      option.textContent = cfg;

      configSelect.appendChild(option);

    });
  }

});

// MOSTRAR NOTIFICACIÓN
const params = new URLSearchParams(window.location.search);
const toast = document.getElementById("toast");

if(params.get("error") === "ppu_existe"){
  mostrarToast("⚠️ La PPU ya está registrada", "#dc2626");
}

if(params.get("success") === "equipo"){
  mostrarToast("✅ Equipo guardado correctamente", "#16a34a");
}

if(params.get("success") === "conductor"){
  mostrarToast("✅ Conductor guardado correctamente", "#16a34a");
}

if(params.get("success") === "equipo_quitado"){
  mostrarToast("✅ Equipo quitado correctamente", "#16a34a");
}


// GUARDAR SCROLL ANTES DE ENVIAR FORMULARIOS
document.querySelectorAll(".form-scroll").forEach(form => {
  form.addEventListener("submit", () => {
    localStorage.setItem("scrollY", window.scrollY);
  });
});

// RESTAURAR SCROLL
window.addEventListener("load", () => {
  const scrollY = localStorage.getItem("scrollY");

  if (scrollY !== null) {
    window.scrollTo({
      top: parseInt(scrollY),
      behavior: "instant" // evita animación rara
    });

    // limpiar para que no afecte navegación futura
    localStorage.removeItem("scrollY");
  }
});



// ABRIR MODAL ESTADO
const modalEstado = document.getElementById("modalEstado");
const estadoInput = document.getElementById("estado_conductor_id");

document.querySelectorAll(".estado-btn").forEach(btn => {
  btn.addEventListener("click", () => {

    estadoInput.value = btn.dataset.id;
    modalEstado.style.display = "flex";

  });
});

// cerrar modal
window.addEventListener("click", e => {
  if(e.target === modalEstado){
    modalEstado.style.display = "none";
  }
});


let toastTimeout; // 🔥 control global

function mostrarToast(mensaje, color = "#2563eb"){

  const toast = document.getElementById("toast");

  // limpiar timeout anterior si existe
  if(toastTimeout){
    clearTimeout(toastTimeout);
  }

  // resetear estado
  toast.classList.remove("show");

  // forzar reflow (reinicia animación)
  void toast.offsetWidth;

  // setear contenido
  toast.innerText = mensaje;
  toast.style.background = color;

  // mostrar
  toast.classList.add("show");

  // ocultar correctamente
  toastTimeout = setTimeout(()=>{
    toast.classList.remove("show");
  }, 3000);
}


if(params.get("error") === "equipo_ocupado"){
  mostrarToast("⚠️ Este equipo ya está asignado a otro conductor", "#dc2626");
}


if(params.get("error") === "sin_equipo"){
  mostrarToast("⚠️ Debes seleccionar un equipo", "#f59e0b");
}

if(params.get("error") === "sin_conductor"){
  mostrarToast("⚠️ Debes seleccionar un conductor", "#f59e0b");
}


function validarAsignacion(){
  const equipo = document.getElementById("equipo_id").value;
  const conductor = document.getElementById("conductor_id").value;

  document.getElementById("btnAsignar").disabled = !(equipo && conductor);
}

// ejecutar al seleccionar
resultadosConductores.addEventListener("click", validarAsignacion);
resultadosEquipos.addEventListener("click", validarAsignacion);



// ==========================
// CONFIRMACIÓN QUITAR EQUIPO
// ==========================

const modalConfirmarQuitar = document.getElementById("confirmQuitar");
const btnCancelarQuitar = document.getElementById("btnCancelarQuitar");
const btnConfirmarQuitar = document.getElementById("btnConfirmarQuitar");
const confirmMensaje = document.getElementById("confirmMensaje");

let formularioQuitar = null;


// ==========================
// ABRIR CONFIRMACIÓN
// ==========================

document.querySelectorAll(".btn-quitar").forEach(btn => {

    btn.addEventListener("click", function(e){

        e.preventDefault();

        formularioQuitar = this.closest("form");

        const patente = this.dataset.equipo || "";

        confirmMensaje.innerHTML =
            `¿Estás seguro de que deseas quitar la patente <strong>${patente}</strong> de esta asignación?`;

        btnConfirmarQuitar.disabled = false;
        btnConfirmarQuitar.innerText = "Sí, quitar equipo";

        modalConfirmarQuitar.style.display = "flex";

    });

});


// ==========================
// CANCELAR
// ==========================

btnCancelarQuitar.addEventListener("click", function(){

    modalConfirmarQuitar.style.display = "none";

    formularioQuitar = null;

});


// ==========================
// CONFIRMAR
// ==========================

btnConfirmarQuitar.addEventListener("click", function(){

    if(!formularioQuitar){
        return;
    }

    // Evitar doble click
    this.disabled = true;
    this.innerText = "Quitando...";

    // Enviar formulario
    formularioQuitar.submit();

});


// ==========================
// CERRAR AL HACER CLICK FUERA
// ==========================

modalConfirmarQuitar.addEventListener("click", function(e){

    if(e.target === modalConfirmarQuitar){

        modalConfirmarQuitar.style.display = "none";

        formularioQuitar = null;

    }

});


function abrirRevTecnica(){
  document.getElementById("modalRevTecnica").style.display = "flex";
}

// ================= FILTRO AJAX REVISIONES =================

const filtroMes = document.getElementById("filtroMes");
const filtroAnio = document.getElementById("filtroAnio");
const tablaRevTecnicas = document.getElementById("tablaRevTecnicas");

if(filtroMes && filtroAnio){

  filtroMes.addEventListener("change", cargarRevTecnicas);
  filtroAnio.addEventListener("change", cargarRevTecnicas);

}

function cargarRevTecnicas(){

  const mes = filtroMes.value;
  const anio = filtroAnio.value;

  fetch(`filtrar_rev_tecnicas.php?mes=${mes}&anio=${anio}`)
    .then(res => res.text())
    .then(data => {

      tablaRevTecnicas.innerHTML = data;

    });

}

</script>

<footer>
<strong>Transportes Mimbral LTDA</strong><br>
© <?= date('Y') ?>
</footer>

</body>
</html>