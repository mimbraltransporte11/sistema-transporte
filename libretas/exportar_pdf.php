<?php
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

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

/* ===== QUERY CONDUCTORES ===== */
$whereBusqueda = '';
if($busqueda){
  $b = $conn->real_escape_string($busqueda);
  $whereBusqueda = " AND (c.rut LIKE '%$b%' OR c.nombre LIKE '%$b%')";
}

$sql = "
  SELECT *
  FROM conductores c
  WHERE c.estado != 'desvinculado'
  $whereBusqueda
  ORDER BY nombre
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

/* ===== HTML PDF ===== */
$html = "
<style>

@page {
  margin: 100px 40px 60px 40px;
}

body{
  font-family: DejaVu Sans;
  font-size: 12px;
}

/* ===== HEADER ESTABLE PARA DOMPDF ===== */

.header{
  position: fixed;
  top: -80px;
  left: 0;
  right: 0;
  height: 70px;
}

.logo{
  position: absolute;
  left: 0;
  top: 0;
}

.generated{
  position: absolute;
  right: 0;
  top: 10px;
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

/* ===== FOOTER ===== */

.footer{
  position: fixed;
  bottom: -40px;
  left: 0;
  right: 0;
  font-size:10px;
  color:#64748b;
  text-align:center;
}

.pagenum:before{
  content: counter(page);
}

/* ===== CONTENIDO ===== */

h1{margin-bottom:5px}
.subtitle{color:#555;margin-bottom:15px}

.badge{
  display:inline-block;
  background:#fde68a;
  padding:6px 10px;
  border-radius:8px;
  font-weight:bold;
}

table{
  width:100%;
  border-collapse:collapse;
  margin-top:15px;
}

th,td{
  border:1px solid #ddd;
  padding:8px;
  text-align:center;
}

th{background:#f1f5f9}

.entregado{background:#bbf7d0}
.pendiente{background:#fde68a}
.no_corresponde{background:#fecaca}

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

<h1>Libretas de Conducción</h1>

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
  $html .= "<div style='margin-top:8px'><strong>Filtro búsqueda:</strong> $busqueda</div>";
}

/* ===== TABLA ===== */

$html .= "
<table>
<thead>
<tr>
  <th>RUT</th>
  <th>Nombre</th>
  <th>ÁREA</th>
  <th>Estado Mes</th>";

for($s=1;$s<=5;$s++){
  $html .= "<th>Semana $s</th>";
}


$html .= "</tr>
</thead>
<tbody>";

while($c=$conductores->fetch_assoc()){

  $rut = $c['rut'];

  /* ===== CALCULAR SI TIENE PENDIENTES ===== */
  $tienePendiente = false;

  for($s=1;$s<=5;$s++){
    $estadoSemana = $entregas[$rut][$s] ?? 'pendiente';

    if(!in_array($estadoSemana,['entregado','licencia','vacaciones','no_corresponde'])){
      $tienePendiente = true;
      break;
    }
  }

  /* ===== SI ES SOLO PENDIENTES Y NO TIENE, LO SALTAMOS ===== */
  if($soloPendientes && !$tienePendiente){
    continue;
  }

  /* ===== ESTADO RETIRO (SE MANTIENE IGUAL) ===== */
  $estadoRetiro = $retiros[$rut] ?? 'NO RETIRADO';

  /* ===== FILTRO SOLO NO RETIRADO ===== */
if($soloNoRetirado){

  $estadoTmp = strtoupper(trim($estadoRetiro));
  $estadoTmp = preg_replace('/\s+/', ' ', $estadoTmp);

  if(strpos($estadoTmp, 'RETIRADO') === 0){
    continue;
  }

}

 $html .= "<tr>
  <td>{$rut}</td>
  <td>{$c['nombre']}</td>
  <td>{$c['area']}</td>";

  $claseRetiro = $estadoRetiro == 'RETIRADO' ? 'entregado' : 'no_corresponde';
  $html .= "<td class='$claseRetiro'>$estadoRetiro</td>";

  /* ===== SEMANAS ===== */
  for($s=1;$s<=5;$s++){

    $estado = $entregas[$rut][$s] ?? 'pendiente';
    $texto = strtoupper(str_replace('_',' ',$estado));

    $html .= "<td class='$estado'>$texto</td>";
  }

  $html .= "</tr>";
}

$html .= "
</tbody>
</table>
";

/* ===== PDF ===== */

use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);

/* ESTA LINEA ES LA CLAVE EN XAMPP */
$options->set('chroot', 'C:/xampp/htdocs/');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','landscape');
$dompdf->render();
$dompdf->stream("libretas_{$mes}_{$anio}.pdf", ['Attachment'=>false]);