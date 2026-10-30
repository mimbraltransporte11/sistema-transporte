<?php
include 'db.php';
date_default_timezone_set('America/Santiago');


/* AUTOMATIZACION DE RETORNOS */

$hoy = date('Y-m-d');

$conn->query("
UPDATE retiros
SET estado='NO RETIRADO', fecha_retorno=NULL
WHERE estado IN ('LICENCIA','VACACIONES')
AND fecha_retorno IS NOT NULL
AND fecha_retorno <= '$hoy'
");

$mesActual = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');
$anioActual = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$soloPendientes = isset($_GET['soloPendientes']) && $_GET['soloPendientes'] == 1;
$soloNoRetirado = isset($_GET['soloNoRetirado']) && $_GET['soloNoRetirado'] == 1;
$soloLicencia = isset($_GET['soloLicencia']) && $_GET['soloLicencia'] == 1;
$soloVacaciones = isset($_GET['soloVacaciones']) && $_GET['soloVacaciones'] == 1;


$meses = [
  1 => 'Enero',
  2 => 'Febrero',
  3 => 'Marzo',
  4 => 'Abril',
  5 => 'Mayo',
  6 => 'Junio',
  7 => 'Julio',
  8 => 'Agosto',
  9 => 'Septiembre',
  10 => 'Octubre',
  11 => 'Noviembre',
  12 => 'Diciembre'
];


function semanasDelMes($mes,$anio){
  return 5;
}

$totalSemanas = semanasDelMes($mesActual,$anioActual);

/* ===== SEMANAS QUE YA DEBERÍAN ESTAR ENTREGADAS ===== */

$mesHoy = date('n');
$anioHoy = date('Y');

if($mesActual == $mesHoy && $anioActual == $anioHoy){

  $diaMes = date('j');
  $semanasEsperadas = floor(($diaMes - 1) / 6);

  if($semanasEsperadas > $totalSemanas){
    $semanasEsperadas = $totalSemanas;
  }

}else{

  // si es un mes pasado → revisar todas las semanas
  $semanasEsperadas = $totalSemanas;

}

/* ===== ALERTAS DE MESES ANTERIORES ===== */
$alertas = [];
$mesesARevisar = [];

if ($mesActual > 1) {
  // Meses anteriores del mismo año
  for ($m = 1; $m < $mesActual; $m++) {
    $mesesARevisar[] = ['mes' => $m, 'anio' => $anioActual];
  }
} else {
  // Enero → revisar diciembre del año anterior
  $mesesARevisar[] = ['mes' => 12, 'anio' => $anioActual - 1];
}

foreach ($mesesARevisar as $ref) {
  $mes = $ref['mes'];
  $anio = $ref['anio'];

  // ⚠️ Ignorar años anteriores al actual - 1
  if ($anio < $anioActual - 1) continue;

  $semanasMes = semanasDelMes($mes, $anio);

  $sql = $conn->query("
  SELECT c.rut
  FROM conductores c
  LEFT JOIN entregas e
    ON e.rut = c.rut
    AND e.mes = $mes
    AND e.anio = $anio
    AND e.estado IN ('entregado','licencia','vacaciones','no_corresponde')
    AND e.semana <= $semanasMes
  WHERE c.estado != 'desvinculado'
  GROUP BY c.rut
  HAVING COUNT(e.id) < $semanasMes
");

  if ($sql && $sql->num_rows > 0) {
    $alertas[] = [
      'mes' => $mes,
      'anio' => $anio,
      'cantidad' => $sql->num_rows
    ];
  }
}



/* ===== CONDUCTORES ===== */

if($soloPendientes){

  $conductores = $conn->query("
  SELECT c.*
  FROM conductores c
  LEFT JOIN entregas e
    ON e.rut = c.rut
    AND e.mes = $mesActual
    AND e.anio = $anioActual
    AND e.estado IN ('entregado','licencia','vacaciones','no_corresponde')
  WHERE c.estado != 'desvinculado'
  GROUP BY c.rut
  HAVING COUNT(e.id) < $totalSemanas
  ORDER BY c.nombre
  ");

}else if($soloNoRetirado){

  $conductores = $conn->query("
  SELECT c.*
  FROM conductores c
  LEFT JOIN retiros r
    ON r.rut = c.rut
    AND r.mes = $mesActual
    AND r.anio = $anioActual
  WHERE c.estado != 'desvinculado'
  AND (r.estado IS NULL OR r.estado = 'NO RETIRADO')
  ORDER BY c.nombre
  ");

}else if($soloLicencia){

  $conductores = $conn->query("
  SELECT c.*
  FROM conductores c
  JOIN retiros r
    ON r.rut = c.rut
    AND r.mes = $mesActual
    AND r.anio = $anioActual
  WHERE r.estado = 'LICENCIA'
  ORDER BY c.nombre
  ");

}else if($soloVacaciones){

  $conductores = $conn->query("
  SELECT c.*
  FROM conductores c
  JOIN retiros r
    ON r.rut = c.rut
    AND r.mes = $mesActual
    AND r.anio = $anioActual
  WHERE r.estado = 'VACACIONES'
  ORDER BY c.nombre
  ");

}else{

  $conductores = $conn->query("
  SELECT *
  FROM conductores
  ORDER BY nombre
  ");

} 

/* ===== ENTREGAS ===== */
$entregas = [];
$res = $conn->query("SELECT * FROM entregas WHERE mes=$mesActual AND anio=$anioActual");
while($r = $res->fetch_assoc()){
  $entregas[$r['rut']][$r['semana']] = $r['estado'];
}


$retiros = [];
$resR = $conn->query("SELECT * FROM retiros WHERE mes=$mesActual AND anio=$anioActual");

while($r = $resR->fetch_assoc()){
    $retiros[$r['rut']] = [
        'estado'=>$r['estado'],
        'retorno'=>$r['fecha_retorno']
    ];
}


/* ===== DETECTAR ATRASOS DEL MES ACTUAL ===== */

$atrasados = [];
$detalleAtrasos = [];

if($semanasEsperadas > 0){

  $resConductores = $conn->query("
    SELECT rut,nombre
    FROM conductores
    WHERE estado != 'desvinculado'
  ");

  while($c = $resConductores->fetch_assoc()){

    $rut = $c['rut'];
    $nombre = $c['nombre'];

    $semanasPendientes = [];

    for($s = 1; $s <= $semanasEsperadas; $s++){

      $estado = $entregas[$rut][$s] ?? 'pendiente';

      if(!in_array($estado,['entregado','licencia','vacaciones','no_corresponde'])){
        $semanasPendientes[] = $s;
      }

    }

    if(count($semanasPendientes) > 0){

      $atrasados[$rut] = true;

      $detalleAtrasos[] = [
        'nombre'=>$nombre,
        'semanas'=>$semanasPendientes
      ];

    }

  }

}

$cantidadAtrasados = count($atrasados);


$licencias = $conn->query("
SELECT COUNT(*) total 
FROM retiros 
WHERE estado='LICENCIA'
AND mes=$mesActual 
AND anio=$anioActual
")->fetch_assoc()['total'];

$vacaciones = $conn->query("
SELECT COUNT(*) total 
FROM retiros 
WHERE estado='VACACIONES'
AND mes=$mesActual 
AND anio=$anioActual
")->fetch_assoc()['total'];

/* ===== ALERTA NO RETIRADO MES ACTUAL ===== */

$sqlNoRetirados = $conn->query("
  SELECT c.rut
  FROM conductores c
  LEFT JOIN retiros r
    ON r.rut = c.rut
    AND r.mes = $mesActual
    AND r.anio = $anioActual
  WHERE c.estado != 'desvinculado'
  AND (r.estado IS NULL OR r.estado = 'NO RETIRADO')
");

$cantidadNoRetirados = $sqlNoRetirados ? $sqlNoRetirados->num_rows : 0;

/* ===== DASHBOARD ===== */

/* Conductores activos */
$totalConductores = $conn->query("
SELECT COUNT(*)
FROM conductores
WHERE estado != 'desvinculado'
")->fetch_row()[0];

/* Conductores que retiraron libreta */
$totalRetiraron = $conn->query("
SELECT COUNT(DISTINCT rut)
FROM retiros
WHERE mes=$mesActual
AND anio=$anioActual
AND estado='RETIRADO'
")->fetch_row()[0];


$totalRRHH = $conn->query("
SELECT COUNT(DISTINCT rut)
FROM retiros
WHERE mes=$mesActual
AND anio=$anioActual
AND estado='RETIRADO RR HH'
")->fetch_row()[0];

/* Conductores que NO retiraron */
$totalNoRetiraron = $conn->query("
SELECT COUNT(c.rut)
FROM conductores c
LEFT JOIN retiros r
ON r.rut = c.rut
AND r.mes=$mesActual
AND r.anio=$anioActual
WHERE c.estado != 'desvinculado'
AND (r.estado IS NULL OR r.estado='NO RETIRADO')
")->fetch_row()[0];



?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Sistema de Libretas</title>

<link rel="icon" type="image/png" href="favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root{
  --primary:#2563eb;
  --primary-dark:#1e40af;
  --bg1:#f1f5f9;
  --bg2:#e0e7ff;
  --card:#ffffff;
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
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  color:white;
  border:none;
  padding:10px 14px;
  border-radius:12px;
  cursor:pointer;
  font-weight:600;
}
.btn-success{
  background:linear-gradient(135deg,#16a34a,#15803d);
}

/* ESTADOS */
.entregado{background:#bbf7d0}
.pendiente{background:#fde68a}
.no_corresponde{background:#fecaca}

/* MODAL */
.modal{
  display:none;
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.4);
}
.modal-content{
  background:#fff;
  width:420px;
  margin:10% auto;
  padding:25px;
  border-radius:18px;
  max-height:90vh;          /* 🔥 evita que se salga de la pantalla */
  overflow-y:auto;          /* 🔥 scroll interno */
  margin:5vh auto; 
}
.modal-content select,
.modal-content input{
  width:100%;
  padding:10px;
  margin-bottom:12px;
}
footer{
  margin-top:60px;
  padding:30px;
  border-radius:20px;
  background:linear-gradient(135deg,#1e3a8a,#2563eb);
  color:white;
  text-align:center;
}

/* ===== ALERTA LIBRETAS PENDIENTES ===== */
.alerta-wrap{
  max-width:1400px;
  margin:-10px auto 30px;
}

.alerta{
  display:flex;
  align-items:center;
  gap:14px;
  background:#fff7ed;
  border-left:6px solid #f59e0b;
  padding:18px 22px;
  border-radius:16px;
  box-shadow:0 10px 25px rgba(0,0,0,.08);
  color:#92400e;
  font-weight:600;
}

.alerta span{
  font-size:22px;
}

.clickable{
  cursor:pointer;
  transition:.2s;
}
.clickable:hover{
  background:#eff6ff !important;
  transform:scale(1.01);
}

.licencia{
  background:#dbeafe;
}

.vacaciones{
  background:#ede9fe;
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

.estado-retiro{
  font-weight:700;
}

.estado-retiro[data-estado="NO RETIRADO"]{
  background:#fecaca;
  color:#7f1d1d;
}

.estado-retiro[data-estado="RETIRADO"]{
  background:#bbf7d0;
  color:#065f46;
}

.estado-retiro[data-estado="RETIRADO RR HH"]{
  background:#dbeafe;
  color:#1e3a8a;
}

.badge-semana{
  display:inline-block;
  background:#dc2626;
  color:white;
  padding:4px 8px;
  border-radius:6px;
  font-size:12px;
  margin:2px;
}

.alerta.clickable{
cursor:pointer;
transition:.2s;
}

.alerta.clickable:hover{
transform:scale(1.02);
}

@keyframes alertaPulse{
0%{box-shadow:0 0 0 0 rgba(220,38,38,.4);}
70%{box-shadow:0 0 0 10px rgba(220,38,38,0);}
100%{box-shadow:0 0 0 0 rgba(220,38,38,0);}
}

.alerta.clickable{
animation:alertaPulse 2s infinite;
}

.dashboard{
display:flex;
gap:20px;
margin:25px auto;
max-width:1400px;
flex-wrap:wrap;
}

.card-dashboard{
flex:1;
min-width:220px;
padding:22px;
border-radius:16px;
color:white;
font-weight:600;
box-shadow:0 10px 25px rgba(0,0,0,.15);
}

.card-dashboard h2{
margin:0;
font-size:34px;
}

.card-dashboard p{
margin:4px 0 0;
font-size:14px;
opacity:.9;
}

.card-total{background:linear-gradient(135deg,#2563eb,#1e3a8a);}
.card-retirado{background:linear-gradient(135deg,#16a34a,#166534);}
.card-noretirado{background:linear-gradient(135deg,#dc2626,#7f1d1d);}


#contenedorAlertas{
transition:.3s;
}

.estado-retiro[data-estado="RETIRADO RR HH"]{
  background:#dbeafe;
  color:#1e3a8a;
}

.badge-rrhh{
  display:inline-block;
  background:#2563eb;
  color:white;
  padding:3px 8px;
  border-radius:8px;
  font-size:11px;
  margin-top:4px;
}

</style>
</head>

<body>

<?php include '../shared/menu.php'; ?>


<!-- BANNER -->
<div class="banner-wrap">
  <img src="banner1.jpg" class="banner">
  <div class="banner-overlay"></div>

  <div class="banner-text">
    <h1>Libretas de Conducción</h1>
    <p><?= $meses[$mesActual].' '.$anioActual ?></p>

    <?php if($soloPendientes): ?>
      <p style="font-size:14px;opacity:.9">
        Mostrando solo conductores con libretas pendientes
      </p>
    <?php endif; ?>
    <?php if($soloNoRetirado): ?>
  <p style="font-size:14px;opacity:.9">
    Mostrando solo conductores que aun NO retiran libreta
  </p>
<?php endif; ?>
<?php if($soloLicencia): ?>
  <p style="font-size:14px;opacity:.9">
   Mostrando conductores con LICENCIA
  </p>
<?php endif; ?>
<?php if($soloVacaciones): ?>
  <p style="font-size:14px;opacity:.9">
   Mostrando conductores con VACACIONES
  </p>
<?php endif; ?>
  </div>

  <div class="banner-datetime">
    <div id="banner-date"></div>
    <div id="banner-time"></div>
  </div>
</div>

<div class="dashboard">

<div class="card-dashboard card-total">
<h2><?= $totalConductores ?></h2>
<p>Total conductores</p>
</div>

<div class="card-dashboard card-retirado">
<h2><?= $totalRetiraron ?></h2>
<p>Libretas retiradas</p>
</div>

<div class="card-dashboard" style="background:linear-gradient(135deg,#3b82f6,#1e40af)">
<h2><?= $totalRRHH ?></h2>
<p>Retirado RRHH</p>
</div>

<div class="card-dashboard card-noretirado">
<h2><?= $totalNoRetiraron ?></h2>
<p>Aún no retiran libreta</p>
</div>

</div>

<div class="alerta-wrap">

<div class="alerta clickable"
onclick="toggleRecordatorios()"
style="border-left:6px solid #2563eb;background:#eff6ff;color:#1e40af">

<span>🔔</span>
<strong>Recordatorios</strong> — Ver alertas del sistema

</div>

<div id="contenedorAlertas" style="display:none;margin-top:15px">

<?php if(!empty($alertas)): ?>
<div class="alerta-wrap">
  <?php foreach($alertas as $a): ?>
    <!-- NUEVO → alerta clickeable -->
    <a class="alerta"
       href="?mes=<?= $a['mes'] ?>&anio=<?= $a['anio'] ?>&soloPendientes=1"
       style="text-decoration:none;">
      <span>⚠️</span>
      Hay <strong><?= $a['cantidad'] ?></strong> conductores con libretas pendientes en
      <strong><?= $meses[$a['mes']].' '.$a['anio'] ?></strong>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>


<?php if($cantidadNoRetirados > 0): ?>
<div class="alerta-wrap">
  <a class="alerta"
   href="?mes=<?= $mesActual ?>&anio=<?= $anioActual ?>&soloNoRetirado=1"
   style="border-left:6px solid #ef4444;background:#fef2f2;color:#7f1d1d;text-decoration:none;">
    <span>📕</span>
    Hay <strong><?= $cantidadNoRetirados ?></strong> conductores que aun
    <strong>NO RETIRAN LIBRETA</strong> en <?= $meses[$mesActual].' '.$anioActual ?>
  </a>
</div>
<?php endif; ?>

<?php if($cantidadAtrasados > 0): ?>
<div class="alerta-wrap">

  <div class="alerta clickable"
       onclick="abrirModalPendientes()"
       style="border-left:6px solid #dc2626;background:#fef2f2;color:#7f1d1d">

    <span>⏰</span>

     Hay <strong><?= $cantidadAtrasados ?></strong> conductores con semanas pendientes en
    <strong><?= $meses[$mesActual].' '.$anioActual ?></strong>

  </div>

</div>
<?php endif; ?>


<?php if($licencias > 0): ?>
<div class="alerta-wrap">
<a class="alerta licencia"
href="?mes=<?=$mesActual?>&anio=<?=$anioActual?>&soloLicencia=1"
style="text-decoration:none;">
<span>🟡</span>
Hay <strong><?=$licencias?></strong> conductores con <strong>LICENCIA</strong>
</a>
</div>
<?php endif; ?>

<?php if($vacaciones > 0): ?>
<div class="alerta-wrap">
<a class="alerta vacaciones"
href="?mes=<?=$mesActual?>&anio=<?=$anioActual?>&soloVacaciones=1"
style="text-decoration:none;">
<span>🏖</span>
Hay <strong><?=$vacaciones?></strong> conductores con <strong>VACACIONES</strong>
</a>
</div>
<?php endif; ?>
</div>
</div>

<div class="card">

<form method="get" style="display:flex;gap:12px;align-items:center;margin-bottom:20px">
  <select name="mes">
   <?php for($m=1;$m<=12;$m++): ?>
  <option value="<?= $m ?>" <?= $m==$mesActual?'selected':'' ?>>
    <?= $meses[$m] ?>
  </option>
<?php endfor; ?>
    
  </select>

  <input type="number" name="anio" value="<?= $anioActual ?>" style="width:100px">
 <input
  type="text"
  id="buscador"
  placeholder="Buscar por RUT o nombre"
  value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>"
  style="padding:10px;border-radius:12px;border:1px solid #cbd5f5"
>


  <button class="btn">Filtrar</button>

 <!-- 📄 EXPORTAR PDF -->
  <a
    class="btn btn-success"
    href="exportar_pdf.php?<?= http_build_query($_GET) ?>"
    target="_blank"
  >
    📄 Exportar PDF
  </a>

<a
  class="btn btn-success"
  href="exportar_pdf_categorias.php?<?= http_build_query($_GET) ?>"
  target="_blank"
>
  📊 Exportar por Categorías
</a>

</form>


  <button class="btn btn-success" onclick="abrirModalConductor()">Agregar Conductor</button>

  <table>
<thead>
<tr>
  <th>RUT</th>
  <th>NOMBRE</th>
  <th>ÁREA</th>
  <th>ESTADO MES</th>
  <?php for($s=1; $s <= $totalSemanas; $s++): ?>
    <th>Semana <?= $s ?></th>
  <?php endfor; ?>
</tr>
</thead>

<tbody>
<?php while($c = $conductores->fetch_assoc()): ?>
<tr class="fila-conductor clickable"
    data-rut="<?= $c['rut'] ?>"
    data-nombre="<?= htmlspecialchars($c['nombre']) ?>"
    data-area="<?= htmlspecialchars($c['area']) ?>"
    data-estado="<?= $c['estado'] ?>">

  <td><?= $c['rut'] ?></td>
  <td><?= $c['nombre'] ?></td>
  <td><?= $c['area'] ?? '-' ?></td>
  
  <?php
$estadoRetiro = $retiros[$c['rut']]['estado'] ?? 'NO RETIRADO';
$retorno = $retiros[$c['rut']]['retorno'] ?? null;
?>

<td class="estado-retiro clickable"
    data-rut="<?= $c['rut'] ?>"
    data-nombre="<?= htmlspecialchars($c['nombre']) ?>"
    data-estado="<?= $estadoRetiro ?>">

<?php
if(in_array($estadoRetiro,['LICENCIA','VACACIONES']) && $retorno){

    $retornoFormateado = date('d-m-y', strtotime($retorno));

    echo $estadoRetiro."<br><small>vuelve ".$retornoFormateado."</small>";

}else{

    if($estadoRetiro == 'RETIRADO RR HH'){

  echo "👔 RETIRADO<br><span class='badge-rrhh'>RR HH</span>";

}
else{

  echo $estadoRetiro;

}
}
?>

</td>

  <?php for($s=1; $s <= $totalSemanas; $s++):

 if ($estadoRetiro == 'LICENCIA') {
    $estado = 'licencia';
}
elseif ($estadoRetiro == 'VACACIONES') {
    $estado = 'vacaciones';
}
else {
    $estado = $entregas[$c['rut']][$s] ?? 'pendiente';
}

  ?>
    <td class="<?= $estado ?> semana" data-semana="<?= $s ?>">
      <?= strtoupper(str_replace('_',' ',$estado)) ?>
    </td>
  <?php endfor; ?>

</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

<!-- ===== MODAL ENTREGA ===== -->
<div class="modal" id="modalEntrega">
  <div class="modal-content">
    <h3>Recepción de Libreta</h3>

    <form method="post" action="guardar_entrega.php">

      <input type="hidden" name="rut" id="modalRut">
      <input type="hidden" name="mes" value="<?= $mesActual ?>">
      <input type="hidden" name="anio" value="<?= $anioActual ?>">

      <input type="hidden" name="busqueda" id="modalBusqueda">
      <input type="hidden" name="soloPendientes" value="<?= $soloPendientes ? 1 : 0 ?>">


      <input type="text" id="modalNombre" disabled>

      <select name="semana" required>
        <?php for($s=1; $s <= $totalSemanas; $s++): ?>

          <option value="<?= $s ?>">Semana <?= $s ?></option>
        <?php endfor; ?>
      </select>

      <select name="estado" required>
        <option value="entregado">Entregado</option>
        <option value="pendiente">Pendiente</option>
        <option value="no_corresponde">No corresponde</option>
        <option value="licencia">Licencia</option>
        <option value="vacaciones">Vacaciones</option>
      </select>

      <button class="btn">Guardar</button>
      <button type="button" class="btn" onclick="cerrar()">Cancelar</button>

    </form>
  </div>
</div>

<!-- ===== MODAL CONDUCTOR ===== -->
<div class="modal" id="modalConductor">
  <div class="modal-content">
    <h3>Agregar Conductor</h3>
    <form method="post" action="guardar_conductor.php">
      <input type="text" name="rut" placeholder="RUT" required>
      <input type="text" name="nombre" placeholder="Nombre" required>
      <select name="area" required>
  <option value="">Seleccione área</option>
  <option value="CORTINA">CORTINA</option>
  <option value="CARRO">CARRO</option>
  <option value="RAMPLA">RAMPLA</option>
  <option value="PORTEADOR">PORTEADOR</option>
</select>
      <button class="btn btn-success">Guardar</button>
      <button type="button" class="btn" onclick="cerrar()">Cancelar</button>
    </form>
  </div>
</div>


<!-- ===== MODAL ESTADO RETIRO ===== -->
<div class="modal" id="modalRetiro">
  <div class="modal-content">
    <h3>Estado del Mes</h3>

    <form method="post" action="guardar_retiro.php">

      <input type="hidden" name="rut" id="retiroRut">
      <input type="hidden" name="mes" value="<?= $mesActual ?>">
      <input type="hidden" name="anio" value="<?= $anioActual ?>">

      <input type="text" id="retiroNombre" disabled>

    <select name="estado_retiro" id="estadoMes" required>

<option value="NO RETIRADO">NO RETIRADO</option>
<option value="RETIRADO">RETIRADO</option>
<option value="RETIRADO RR HH">RETIRADO RR HH</option>
<option value="LICENCIA">LICENCIA</option>
<option value="VACACIONES">VACACIONES</option>
<option value="DESVINCULADO">DESVINCULADO</option>

</select>
<div id="campoRetorno" style="display:none">

<label>Fecha de retorno</label>

<input type="date" name="fecha_retorno">

</div>

      <button class="btn btn-success">Guardar</button>
      <button type="button" class="btn" onclick="cerrar()">Cancelar</button>

    </form>
  </div>
</div>



<!-- ===== MODAL PENDIENTES ===== -->
<div class="modal" id="modalPendientes">

  <div class="modal-content" style="width:600px">

    <h3>Conductores con semanas pendientes</h3>

    <div style="max-height:400px;overflow:auto">

      <table style="width:100%">
        <thead>
          <tr>
            <th>Conductor</th>
            <th>Semanas pendientes</th>
          </tr>
        </thead>

        <tbody>

        <?php foreach($detalleAtrasos as $d): ?>

        <tr>
          <td><?= $d['nombre'] ?></td>
          <td>
          <?php foreach($d['semanas'] as $s): ?>
            <span class="badge-semana">
              Semana <?= $s ?>
            </span>
          <?php endforeach; ?>
          </td>
        </tr>

        <?php endforeach; ?>

        </tbody>
      </table>

    </div>

    <br>

    <a class="btn btn-success"
    href="exportar_pendientes_pdf.php?mes=<?= $mesActual ?>&anio=<?= $anioActual ?>"
    target="_blank">
    📄 Exportar PDF
    </a>

    <button class="btn" onclick="cerrar()">Cerrar</button>

  </div>

</div>


<div class="modal" id="modalEditarConductor">
  <div class="modal-content">
    <h3>Editar Conductor</h3>

    <form method="post" action="editar_conductor.php">

      <input type="hidden" name="rut" id="editRut">

      <label>RUT</label>
      <input type="text" id="editRutTexto" disabled>

      <label>Nombre</label>
      <input type="text" id="editNombre" disabled>

      <label>Área</label>
      <select name="area" id="editArea" required>
        <option value="CORTINA">CORTINA</option>
        <option value="CARRO">CARRO</option>
        <option value="RAMPLA">RAMPLA</option>
        <option value="PORTEADOR">PORTEADOR</option>
      </select>

      <button class="btn btn-success">Guardar cambios</button>
      <button type="button" class="btn" onclick="cerrar()">Cancelar</button>

    </form>
  </div>
</div>


<footer>
<strong>Transportes Mimbral LTDA</strong><br>
Gestión empresarial · © <?= date('Y') ?>
</footer>

<script>
function abrirModalEntrega(){
  document.getElementById('modalEntrega').style.display='block';
}
function abrirModalConductor(){
  document.getElementById('modalConductor').style.display='block';
}
function cerrar(){
  document.querySelectorAll('.modal').forEach(m=>m.style.display='none');
}
</script>

<script>
const buscador = document.getElementById('buscador');
const filas = document.querySelectorAll('.fila-conductor');

function aplicarFiltro(){

  const texto = buscador.value.toLowerCase().trim();

  filas.forEach(fila => {

    const rut = fila.dataset.rut.toLowerCase();
    const nombre = fila.dataset.nombre.toLowerCase();
    const area = (fila.dataset.area || '').toLowerCase();
    const estado = fila.dataset.estado.toLowerCase();

    if(texto === ''){

      // 🔒 Oculta desvinculados si no se busca nada
      fila.style.display = (estado === 'desvinculado') ? 'none' : '';

    } else {

     if(estado === 'desvinculado'){

  // 👇 solo mostrar si buscan "desvinculado"
  if(texto === 'desvinculado'){
    fila.style.display = '';
  }else{
    fila.style.display = 'none';
  }

}else{

  if(
    rut.includes(texto) ||
    nombre.includes(texto) ||
    area.includes(texto)
  ){
    fila.style.display = '';
  }else{
    fila.style.display = 'none';
  }

}

    }

  });

  /* actualizar URL */
  const url = new URL(window.location);

  if(texto){
    url.searchParams.set('busqueda', buscador.value);
  } else {
    url.searchParams.delete('busqueda');
  }

  window.history.replaceState({}, '', url);
}

buscador.addEventListener('keyup', aplicarFiltro);
aplicarFiltro(); // ocultar desvinculados al cargar


document.querySelectorAll('.semana').forEach(celda => {

  celda.addEventListener('click', (e) => {

    e.stopPropagation(); // evita doble evento

    const fila = celda.closest('tr');

    const rut = fila.dataset.rut;
    const nombre = fila.dataset.nombre;
    const semana = celda.dataset.semana;

    document.getElementById('modalRut').value = rut;
    document.getElementById('modalNombre').value = nombre;

    document.querySelector('select[name="semana"]').value = semana;

    document.getElementById('modalBusqueda').value = buscador.value;


    document.getElementById('modalEntrega').style.display = 'block';

  });

});


</script>

<script>
const inputBusqueda = document.getElementById('buscador');
const botonesPDF = document.querySelectorAll('a.btn-success');

function actualizarPDF(){

  const busqueda = inputBusqueda.value.trim();
  const params = new URLSearchParams(window.location.search);

  if(busqueda){
    params.set('busqueda', busqueda);
  } else {
    params.delete('busqueda');
  }

  botonesPDF.forEach(boton => {

    if(boton.href.includes('exportar_pdf.php')){
      boton.href = 'exportar_pdf.php?' + params.toString();
    }

    if(boton.href.includes('exportar_pdf_categorias.php')){
      boton.href = 'exportar_pdf_categorias.php?' + params.toString();
    }

  });

}
inputBusqueda.addEventListener('keyup', actualizarPDF);
actualizarPDF();
</script>


<script>
/* ✅ GUARDAR POSICIÓN SCROLL */
window.addEventListener('beforeunload', () => {
  sessionStorage.setItem('scrollPos', window.scrollY);
});

/* ✅ RESTAURAR POSICIÓN */
window.addEventListener('load', () => {
  const scrollPos = sessionStorage.getItem('scrollPos');

  if(scrollPos !== null){
    window.scrollTo(0, parseInt(scrollPos));
  }
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
document.querySelectorAll('.estado-retiro').forEach(celda => {

  celda.addEventListener('click', (e) => {

    e.stopPropagation();

    const rut = celda.dataset.rut;
    const nombre = celda.dataset.nombre;
    const estado = celda.dataset.estado;

    document.getElementById('retiroRut').value = rut;
    document.getElementById('retiroNombre').value = nombre;

    document.querySelector('#modalRetiro select[name="estado_retiro"]').value = estado;

    document.getElementById('modalRetiro').style.display = 'block';

  });

});

function abrirModalPendientes(){
  document.getElementById('modalPendientes').style.display = 'block';
}

const estadoMes = document.getElementById('estadoMes');
const campoRetorno = document.getElementById('campoRetorno');

estadoMes.addEventListener('change',()=>{

  if(
    estadoMes.value === 'LICENCIA' ||
    estadoMes.value === 'VACACIONES'
  ){
    campoRetorno.style.display='block';
  }else{
    campoRetorno.style.display='none';
  }

});

</script>

<script>

function toggleRecordatorios(){

  const contenedor = document.getElementById("contenedorAlertas");

  if(contenedor.style.display === "none"){
    contenedor.style.display = "block";
  }else{
    contenedor.style.display = "none";
  }

}

document.querySelectorAll('.fila-conductor').forEach(fila => {

  fila.addEventListener('click', () => {

    const rut = fila.dataset.rut;
    const nombre = fila.dataset.nombre;
    const area = fila.dataset.area;

    document.getElementById('editRut').value = rut;
    document.getElementById('editRutTexto').value = rut;
    document.getElementById('editNombre').value = nombre;
    document.getElementById('editArea').value = area;

    document.getElementById('modalEditarConductor').style.display = 'block';

  });

});

</script>

</body>
</html>
