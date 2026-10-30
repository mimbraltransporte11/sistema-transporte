<?php
include 'db.php';
require 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('America/Santiago');

$mes = $_GET['mes'];
$anio = $_GET['anio'];

$meses = [
1=>"Enero","Febrero","Marzo","Abril","Mayo","Junio",
"Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"
];

$mesNombre = $meses[$mes];

/* FECHA */
setlocale(LC_TIME, 'es_ES.UTF-8','spanish');
$fechaGeneracion = strftime("%A %d de %B del %Y");

/* SEMANAS */
function semanasDelMes($mes,$anio){ return 5; }

$totalSemanas = semanasDelMes($mes,$anio);

$mesActual = date('n');
$anioActual = date('Y');

if($mes == $mesActual && $anio == $anioActual){
    $diaMes = date('j');
    $semanasEsperadas = floor(($diaMes - 1) / 6);
} else {
    $semanasEsperadas = $totalSemanas;
}

if($semanasEsperadas > $totalSemanas){
  $semanasEsperadas = $totalSemanas;
}

/* ENTREGAS */
$entregas = [];
$res = $conn->query("SELECT * FROM entregas WHERE mes=$mes AND anio=$anio");

while($r = $res->fetch_assoc()){
 $entregas[$r['rut']][$r['semana']] = $r['estado'];
}

/* CONDUCTORES */
$resConductores = $conn->query("
SELECT rut,nombre,area
FROM conductores
WHERE estado!='desvinculado'
ORDER BY nombre
");

$areas = [
  'RAMPLA'=>[],
  'CORTINA'=>[],
  'CARRO'=>[],
  'PORTEADOR'=>[]
];

while($c = $resConductores->fetch_assoc()){

  $rut = $c['rut'];
  $nombre = $c['nombre'];
  $area = strtoupper($c['area'] ?? 'SIN AREA');

  $pendientes = [];

  for($s=1;$s<=$semanasEsperadas;$s++){
    $estado = $entregas[$rut][$s] ?? 'pendiente';

    if(!in_array($estado,['entregado','licencia','vacaciones','no_corresponde'])){
      $pendientes[]=$s;
    }
  }

  if(count($pendientes)>0){

    if(!isset($areas[$area])){
      $areas[$area] = [];
    }

    $areas[$area][] = [
      'nombre'=>$nombre,
      'area'=>$area,
      'semanas'=>$pendientes,
      'total'=>count($pendientes)
    ];
  }
}

/* ORDENAR CADA AREA */
foreach($areas as &$lista){
  usort($lista,function($a,$b){
    return $b['total'] - $a['total'];
  });
}
unset($lista);

/* ================= HTML ================= */

$html = "
<style>
@page { margin: 100px 40px 60px 40px; }

body{ font-family: DejaVu Sans; font-size:12px; }

.header{
  position: fixed;
  top:-80px;
  left:0;
  right:0;
  height:70px;
}

.logo{ position:absolute; left:0; top:0; }

.fecha{
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
  bottom:-40px;
  left:0;
  right:0;
  text-align:center;
  font-size:10px;
  color:#64748b;
}

.pagenum:before{ content: counter(page); }

h1{ margin-bottom:5px; }

h3{
  background:#f1f5f9;
  padding:6px 10px;
  border-radius:6px;
}

.subtitle{
  color:#555;
  margin-bottom:15px;
}

table{
  width:100%;
  border-collapse:collapse;
  margin-top:10px;
}

th{
  background:#f1f5f9;
  padding:8px;
  border:1px solid #ddd;
  text-align:left;
}

td{
  padding:8px;
  border:1px solid #ddd;
}

.badge{
  background:#fde68a;
  padding:4px 8px;
  border-radius:6px;
  font-weight:bold;
}

.area-badge{
  background:#2563eb;
  color:#fff;
  padding:2px 8px;
  border-radius:6px;
  font-size:12px;
  display:inline-block;
  vertical-align:middle;
}
</style>

<div class='header'>
<img src='logo.png' width='110' class='logo'>
<div class='fecha'>Generado el ".ucfirst($fechaGeneracion)."</div>
<div class='separator'></div>
</div>

<div class='footer'>
Página <span class='pagenum'></span>
</div>
";

/* ===== CONTENIDO DINÁMICO ===== */

$primera = true;

foreach($areas as $area => $lista){

  if(count($lista) == 0) continue;

  // SOLO PRIMER BLOQUE
  if($primera){

    $html .= "
    <h1>Conductores con semanas pendientes</h1>
    <div class='subtitle'>$mesNombre $anio</div>
    ";

    $primera = false;

  } else {
    // salto de página desde la segunda área
    $html .= "<div style='page-break-before:always'></div>";
  }

  $html .= "
  <h3>
    $area 
    <span class='area-badge'>".count($lista)."</span>
  </h3>

  <table>
  <tr>
    <th>Conductor</th>
    <th>Área</th>
    <th>Semanas pendientes</th>
    <th>Total</th>
  </tr>
  ";

  foreach($lista as $f){

    $sem = implode(", ",$f['semanas']);

    $html .= "
    <tr>
      <td>{$f['nombre']}</td>
      <td>{$f['area']}</td>
      <td>Semana $sem</td>
      <td><span class='badge'>{$f['total']}</span></td>
    </tr>
    ";
  }

  $html .= "</table>";
}

/* ================= PDF ================= */

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('chroot', 'C:/xampp/htdocs/');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream("reporte_pendientes.pdf",["Attachment"=>0]);