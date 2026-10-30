<?php
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

include 'db.php';
date_default_timezone_set('America/Santiago');

/* ===== PARAMETROS ===== */
$mes = intval($_GET['mes'] ?? date('n'));
$anio = intval($_GET['anio'] ?? date('Y'));
$soloPendientes = isset($_GET['soloPendientes']) && $_GET['soloPendientes'] == 1;
$soloNoRetirado = isset($_GET['soloNoRetirado']) && $_GET['soloNoRetirado'] == 1;
$busqueda = trim($_GET['busqueda'] ?? '');

$meses = [
  1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
  7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
];

$totalSemanas = 5;

/* ===== BUSQUEDA ===== */
$whereBusqueda = '';
if($busqueda){
  $b = $conn->real_escape_string($busqueda);
  $whereBusqueda = " AND (c.rut LIKE '%$b%' OR c.nombre LIKE '%$b%' OR c.area LIKE '%$b%')";
}

/* ===== QUERY ORDENADA POR AREA ===== */
$sql = "
SELECT *
FROM conductores c
WHERE c.estado != 'desvinculado'
$whereBusqueda
ORDER BY FIELD(c.area,'RAMPLA','CORTINA','CARRO','PORTEADOR'), c.nombre
";

$conductores = $conn->query($sql);

/* ===== ENTREGAS ===== */
$entregas = [];
$res = $conn->query("SELECT * FROM entregas WHERE mes=$mes AND anio=$anio");
while($r=$res->fetch_assoc()){
  $entregas[$r['rut']][$r['semana']] = $r['estado'];
}

/* ===== RETIROS ===== */
$retiros = [];
$resR = $conn->query("SELECT * FROM retiros WHERE mes=$mes AND anio=$anio");
while($r=$resR->fetch_assoc()){
  $retiros[$r['rut']] = $r['estado'];
}

/* ===== AGRUPAR POR AREA ===== */
$areas = [
  'RAMPLA'=>[],
  'CORTINA'=>[],
  'CARRO'=>[],
  'PORTEADOR'=>[]
];

while($c = $conductores->fetch_assoc()){

  $rut = $c['rut'];

  /* ===== VALIDAR PENDIENTES ===== */
  $tienePendiente = false;

  for($s=1;$s<=5;$s++){
    $estadoSemana = $entregas[$rut][$s] ?? 'pendiente';

    if(!in_array($estadoSemana,['entregado','licencia','vacaciones','no_corresponde'])){
      $tienePendiente = true;
      break;
    }
  }

  if($soloPendientes && !$tienePendiente){
    continue;
  }

  /* ===== ESTADO RETIRO ===== */
  $estadoRetiro = $retiros[$rut] ?? 'NO RETIRADO';

  if($soloNoRetirado){
    $estadoTmp = strtoupper(trim($estadoRetiro));
    if(strpos($estadoTmp,'RETIRADO') === 0){
      continue;
    }
  }

  $area = strtoupper($c['area'] ?? 'SIN AREA');

  if(!isset($areas[$area])){
    $areas[$area] = [];
  }

  $areas[$area][] = $c;
}

/* ===== HTML ===== */
$html = "
<style>
@page { margin: 100px 40px 60px 40px; }
body{ font-family: DejaVu Sans; font-size: 12px; }

.header{
  position: fixed;
  top: -80px;
  left: 0;
  right: 0;
  height: 70px;
}

.logo{ position:absolute; left:0; top:0; }

.generated{
  position:absolute;
  right:0;
  top:10px;
  font-size:10px;
  color:#475569;
}

.separator{
  position:absolute;
  bottom:0;
  left:0;
  right:0;
  border-bottom:2px solid #e2e8f0;
}

.footer{
  position: fixed;
  bottom: -40px;
  left: 0;
  right: 0;
  font-size:10px;
  color:#64748b;
  text-align:center;
}

.pagenum:before{ content: counter(page); }

h1{ margin-bottom:5px }
.subtitle{ color:#555; margin-bottom:15px }

h3{
  background:#f1f5f9;
  padding:6px 10px;
  border-radius:6px;
}

.area-title{
  margin-top:25px;
  font-size:16px;
  font-weight:bold;

  display:inline-flex;     /* 🔥 clave */
  align-items:center;      /* 🔥 centra vertical */
  gap:8px;
}

.badge{
  display:inline-block;
  background:#fde68a;
  padding:6px 10px;
  border-radius:8px;
  font-weight:bold;
  margin-right:8px;
  margin-top:10px;
}

.badge-area{
  display:inline-flex;
  align-items:center;
  justify-content:center;

  background:#2563eb;
  color:white;

  height:1.2em;           /* 🔥 MISMA ALTURA QUE TEXTO */
  padding:0 8px;

  font-size:0.9em;        /* ligeramente menor */
  font-weight:700;

  border-radius:6px;

  line-height:1;
}
table{
  width:100%;
  border-collapse:collapse;
  margin-top:10px;
}

th,td{
  border:1px solid #ddd;
  padding:8px;
  text-align:center;
}

th{ background:#f1f5f9 }

.entregado{background:#bbf7d0}
.pendiente{background:#fde68a}
.no_corresponde{background:#fecaca}
.licencia{background:#dbeafe}
.vacaciones{background:#ede9fe}
</style>

<div class='header'>
  <img src='logo.png' width='110' class='logo'>
  <div class='generated'>
    Generado el ".date('d/m/Y H:i')."
  </div>
  <div class='separator'></div>
</div>

<div class='footer'>
  Página <span class='pagenum'></span>
</div>

<h1>Libretas por Categoría</h1>

<div class='subtitle'>
  {$meses[$mes]} {$anio}
</div>
";

/* ===== BADGES ===== */
if($soloPendientes){
  $html .= "<div class='badge'>Solo conductores con pendientes</div>";
}

if($soloNoRetirado){
  $html .= "<div class='badge'>Solo conductores que no han retirado libreta</div>";
}

if($busqueda){
  $html .= "<div style='margin-top:8px'>
    <strong>Filtro búsqueda:</strong> $busqueda
  </div>";
}

/* ===== RESUMEN ===== */
$html .= "<h3>Resumen por área</h3>";

foreach($areas as $area => $lista){
  $html .= "<div><strong>$area:</strong> ".count($lista)."</div>";
}

$primera = true;

/* ===== TABLAS ===== */
foreach($areas as $area => $lista){

  if(count($lista) == 0) continue;

  // 👉 salto de página desde la segunda área
  if(!$primera){
    $html .= "<div style='page-break-before:always'></div>";
  }

  $primera = false;

  $html .= "<div class='area-title'>
    $area <span class='badge-area'>".count($lista)."</span>
  </div>";

  $html .= "
  <table>
  <thead>
  <tr>
    <th>RUT</th>
    <th>Nombre</th>
    <th>Estado Mes</th>";

  for($s=1;$s<=5;$s++){
    $html .= "<th>Semana $s</th>";
  }

  $html .= "</tr></thead><tbody>";

  foreach($lista as $c){

    $rut = $c['rut'];
    $estadoRetiro = $retiros[$rut] ?? 'NO RETIRADO';

    $html .= "<tr>
      <td>{$rut}</td>
      <td>{$c['nombre']}</td>";

    $claseRetiro = $estadoRetiro == 'RETIRADO' ? 'entregado' : 'no_corresponde';
    $html .= "<td class='$claseRetiro'>$estadoRetiro</td>";

    for($s=1;$s<=5;$s++){
      $estado = $entregas[$rut][$s] ?? 'pendiente';
      $texto = strtoupper(str_replace('_',' ',$estado));

      $html .= "<td class='$estado'>$texto</td>";
    }

    $html .= "</tr>";
  }

  $html .= "</tbody></table>";
}

/* ===== PDF ===== */
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('chroot', 'C:/xampp/htdocs/');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','landscape');
$dompdf->render();
$dompdf->stream("libretas_categorias_{$mes}_{$anio}.pdf", ['Attachment'=>false]);