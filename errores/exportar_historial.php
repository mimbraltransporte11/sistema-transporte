<?php
date_default_timezone_set('America/Santiago');

include 'db.php';
require __DIR__ . '/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* ===== CONFIGURACIÓN ===== */
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

/* ===== HISTORIAL ===== */
$historial = $conn->query("
    SELECT * 
    FROM infracciones 
    WHERE rut='$rut' $whereFecha
    ORDER BY fecha DESC
");

/* ===== TOTAL ===== */
$total = $conn->query("
    SELECT COUNT(*) total 
    FROM infracciones 
    WHERE rut='$rut' $whereFecha
")->fetch_assoc()['total'];

/* ===== ID DOCUMENTO ===== */
$docID = strtoupper(substr(md5($rut . time()), 0, 10));

/* ===== FECHA LEGAL CHILE ===== */
$meses = [
    "enero","febrero","marzo","abril","mayo","junio",
    "julio","agosto","septiembre","octubre","noviembre","diciembre"
];

$dia = date('d');
$mes = $meses[(int)date('m') - 1];
$anio = date('Y');
$hora = date('H:i');

$fechaLegal = "$dia de $mes de $anio, $hora hrs";

/* ===== QR LOCAL BASE64 ===== */
$urlSistema = "http://192.168.5.207/errores/exportar_historial.php?rut=".$rut;

// generar QR usando API externa pero convertirlo a base64
$qrImage = file_get_contents("https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($urlSistema));

if($qrImage){
    $qr = 'data:image/png;base64,' . base64_encode($qrImage);
}else{
    $qr = '';
}

/* ===== LOGO BASE64 ===== */
$logoPath = __DIR__ . '/logo.png';

if(file_exists($logoPath)){
    $logoData = base64_encode(file_get_contents($logoPath));
    $logo = 'data:image/png;base64,' . $logoData;
}else{
    $logo = '';
}

/* ===== HTML ===== */
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>

body{
  font-family: DejaVu Sans, sans-serif;
  font-size:12px;
  color:#0f172a;
}

/* HEADER */
.header{
  width:100%;
  border-bottom:2px solid #2563eb;
  margin-bottom:20px;
}

.header table{
  width:100%;
}

.logo{
  width:100px;
}

.titulo{
  font-size:20px;
  font-weight:bold;
  color:#2563eb;
  text-align:right;
}

/* INFO */
.info{
  margin-bottom:15px;
  line-height:1.6;
}

.badge{
  display:inline-block;
  padding:6px 10px;
  background:#2563eb;
  color:white;
  border-radius:8px;
  font-size:11px;
  margin-top:5px;
}

/* TABLA */
table{
  width:100%;
  border-collapse:collapse;
  margin-top:10px;
}

th{
  background:#2563eb;
  color:white;
  padding:8px;
  font-size:11px;
}

td{
  padding:8px;
  border-bottom:1px solid #e5e7eb;
}

tr:nth-child(even){
  background:#f8fafc;
}

/* FOOTER */
.footer{
  margin-top:25px;
  font-size:10px;
  color:#64748b;
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
Historial de Faltas
</td>
</tr>
</table>
</div>

<div class="info">
<strong>RUT:</strong> '.$conductor['rut'].'<br>
<strong>Nombre:</strong> '.$conductor['nombre'].'<br>
<strong>Área:</strong> '.$conductor['area'].'<br>

<span class="badge">Total de faltas : '.$total.' </span>
</div>
';

/* ===== FILTRO ===== */
if($desde && $hasta){
    $html .= '
    <div class="info">
    <strong>Periodo:</strong> Desde '.date('d-m-Y', strtotime($desde)).'
    hasta '.date('d-m-Y', strtotime($hasta)).'
    </div>
    ';
}

/* ===== TABLA ===== */
$html .= '
<table>
<thead>
<tr>
  <th width="20%">Fecha</th>
   <th width="27%">Valor Estimado</th>
  <th width="80%">Observación</th>
</tr>
</thead>
<tbody>
';

if($historial->num_rows > 0){

    while($h = $historial->fetch_assoc()){

        /*
         * FORMATEAR VALOR ESTIMADO COMO PESO CHILENO
         */

        if(
            $h['valor_estimado'] !== null &&
            $h['valor_estimado'] !== ''
        ){

            $valorEstimado = '$' . number_format(
                (float)$h['valor_estimado'],
                0,
                ',',
                '.'
            );

        }else{

            $valorEstimado = 'Sin valor';

        }


        $html .= '
        <tr>
          <td>'.date('d-m-Y', strtotime($h['fecha'])).'</td>

          <td>'
            .htmlspecialchars(
                $valorEstimado,
                ENT_QUOTES,
                'UTF-8'
            ).
          '</td>

          <td>'.$h['observacion'].'</td>
        </tr>
        ';
    }

}else{

    $html .= '
    <tr>
      <td colspan="2" style="text-align:center;">Sin infracciones</td>
    </tr>
    ';
}

$html .= '
</tbody>
</table>

<br><br>

<table width="100%">
<tr>
<td width="70%" class="footer">
<strong>ID Documento:</strong> '.$docID.'<br>
<strong>Fecha emisión:</strong> '.$fechaLegal.'<br>
Transportes Mimbral LTDA
</td>

<td width="30%" align="right">
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
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/* ===== MOSTRAR ===== */
$dompdf->stream("historial_".$conductor['nombre'].".pdf", ["Attachment" => false]);