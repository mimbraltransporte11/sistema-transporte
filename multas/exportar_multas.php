<?php
date_default_timezone_set('America/Santiago');

include 'db.php';
require __DIR__ . '/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* ===== CONFIG ===== */
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

/* ===== DATOS ===== */
$rut = $_GET['rut'] ?? '';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

if(!$rut){
    die("RUT no especificado");
}

/* ===== FILTRO ===== */
$whereFecha = '';
if ($desde && $hasta) {
    $whereFecha = "AND fecha BETWEEN '$desde' AND '$hasta'";
}

/* ===== CONDUCTOR ===== */
$cond = $conn->query("SELECT * FROM conductores WHERE rut='$rut'");
if ($cond->num_rows === 0) {
    die("Conductor no encontrado");
}
$conductor = $cond->fetch_assoc();

/* ===== HISTORIAL MULTAS ===== */
$historial = $conn->query("
    SELECT * 
    FROM partes 
    WHERE rut='$rut' $whereFecha
    ORDER BY fecha DESC
");

/* ===== TOTAL ===== */
$total = $conn->query("
    SELECT COUNT(*) total 
    FROM partes 
    WHERE rut='$rut' $whereFecha
")->fetch_assoc()['total'];

/* ===== ID DOC ===== */
$docID = strtoupper(substr(md5($rut . time()), 0, 10));

/* ===== FECHA LEGAL ===== */
$meses = [
"enero","febrero","marzo","abril","mayo","junio",
"julio","agosto","septiembre","octubre","noviembre","diciembre"
];

$fechaLegal = date('d')." de ".$meses[(int)date('m')-1]." de ".date('Y').", ".date('H:i')." hrs";

/* ===== URL QR (MISMO PDF) ===== */
$urlSistema = "http://192.168.5.207/multas/exportar_multas.php?rut=".$rut;

/* ===== QR BASE64 ===== */
$qrImage = file_get_contents("https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($urlSistema));
$qr = $qrImage ? 'data:image/png;base64,' . base64_encode($qrImage) : '';

/* ===== LOGO ===== */
$logoPath = __DIR__ . '/logo.png';
$logo = file_exists($logoPath)
    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
    : '';

/* ===== HTML ===== */
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>

body{
  font-family: DejaVu Sans, sans-serif;
  font-size:10px;
  color:#0f172a;
}

/* HEADER */
.header{
  border-bottom:2px solid #2563eb;
  margin-bottom:12px;
}

.header table{width:100%;}

.logo{width:80px;}

.titulo{
  font-size:16px;
  font-weight:bold;
  color:#2563eb;
  text-align:right;
}

/* INFO */
.info{
  margin-bottom:8px;
  line-height:1.4;
}

.badge{
  display:inline-block;
  padding:4px 8px;
  background:#2563eb;
  color:white;
  border-radius:6px;
  font-size:9px;
}

/* TABLA */
table{
  width:100%;
  border-collapse:collapse;
  margin-top:8px;
}

th{
  background:#2563eb;
  color:white;
  padding:5px;
  font-size:9px;
}

td{
  padding:5px;
  border-bottom:1px solid #e5e7eb;
  text-align:center;
  font-size:9px;
}

/* columnas específicas */
.col-obs{
  text-align:left;
  word-wrap:break-word;
}

.col-valor{
  white-space:nowrap;
}

tr:nth-child(even){
  background:#f8fafc;
}

/* FOOTER */
.footer{
  margin-top:25px;
  font-size:10px;
  color:#64748b;
  text-align:left; /* 🔥 esto asegura alineación */
}
.qr{
  width:130px;
}

</style>
</head>

<body>

<div class="header">
<table>
<tr>
<td width="30%">';

if($logo){
    $html .= '<img src="'.$logo.'" class="logo">';
}

$html .= '
</td>
<td width="70%" class="titulo">
Historial de Multas
</td>
</tr>
</table>
</div>

<div class="info">
<strong>RUT:</strong> '.$conductor['rut'].'<br>
<strong>Nombre:</strong> '.$conductor['nombre'].'<br>
<strong>Área:</strong> '.$conductor['area'].'<br>

<span class="badge">Total de multas:'.$total.' </span>
</div>
';

/* ===== TABLA ===== */
$html .= '
<table>
<thead>
<tr>
<th width="12%">Emisión</th>
<th width="12%">Recepción</th>
<th width="12%">Pago</th>
<th width="34%">Observación</th>
<th width="15%">Valor</th>
<th width="15%">Estado</th>
</tr>
</thead>
<tbody>
';

if($historial->num_rows > 0){

while($h = $historial->fetch_assoc()){

$html .= '
<tr>
<td>'.date('d-m-Y', strtotime($h['fecha'])).'</td>
<td>'.($h['fecha_recepcion'] ? date('d-m-Y', strtotime($h['fecha_recepcion'])) : '-').'</td>
<td>'.($h['fecha_pago'] ? date('d-m-Y', strtotime($h['fecha_pago'])) : 'Pendiente').'</td>
<td class="col-obs">'.$h['observacion'].'</td>
<td class="col-valor">$'.number_format($h['valor']).'</td>
<td>'.$h['estado'].'</td>
</tr>
';

}

}else{

$html .= '
<tr>
<td colspan="6">Sin multas</td>
</tr>
';

}

$html .= '
</tbody>
</table>

<br><br>

<table width="100%">
<tr>
<td width="70%" class="footer" style="text-align:left; vertical-align:top;">
<strong>ID Documento:</strong> '.$docID.'<br>
<strong>Fecha emisión:</strong> '.$fechaLegal.'<br>
Transportes Mimbral LTDA
</td>

<td width="30%" align="right" style="vertical-align:top;">
<img src="'.$qr.'" class="qr"><br>
<small>Verificación</small>
</td>
</tr>
</table>

</body>
</html>
';

/* ===== GENERAR PDF ===== */
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait'); // mejor para tablas grandes
$dompdf->render();

/* ===== MOSTRAR ===== */
$dompdf->stream("multas_".$conductor['nombre'].".pdf", ["Attachment" => false]);