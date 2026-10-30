<?php
include 'db.php';

$year = $_GET['anio'] ?? date('Y');
$mesActual = date('n');

/* ===========================
   RANKING ANUAL
=========================== */

echo "<h3 style='margin-bottom:15px'>Ranking Año $year</h3>";

$ranking = $conn->query("
  SELECT c.rut, c.nombre, COUNT(i.id) total, SUM(i.valor) total_valor
  FROM conductores c
  INNER JOIN partes i 
    ON c.rut = i.rut 
  WHERE YEAR(i.fecha) = $year
  GROUP BY c.rut, c.nombre
  HAVING total > 0
  ORDER BY total DESC
");

$posicion = 1;

while($r = $ranking->fetch_assoc()):

$medalla = "";
if($posicion == 1) $medalla = "🥇";
elseif($posicion == 2) $medalla = "🥈";
elseif($posicion == 3) $medalla = "🥉";
?>

<a href="historial.php?rut=<?= $r['rut'] ?>" class="ranking-link">
<div class="ranking-card">

  <div class="ranking-top">
    <strong><?= $medalla ?> <?= $r['nombre'] ?></strong>
    <span class="ranking-total">total de multas: <?= $r['total'] ?> </span>
  </div>

<?php
$detalle = $conn->query("
  SELECT MONTH(fecha) mes, COUNT(*) cantidad
  FROM partes
  WHERE rut='{$r['rut']}' 
  AND YEAR(fecha)=$year
  GROUP BY MONTH(fecha)
  ORDER BY MONTH(fecha)
");

echo "<div class='ranking-detalle'>";
while($d = $detalle->fetch_assoc()){
 $meses = [
  1=>"enero", 2=>"febrero", 3=>"marzo", 4=>"abril",
  5=>"mayo", 6=>"junio", 7=>"julio", 8=>"agosto",
  9=>"septiembre", 10=>"octubre", 11=>"noviembre", 12=>"diciembre"
];

$mesNombre = $meses[$d['mes']];
echo "<span>{$d['cantidad']} en $mesNombre</span>";
}
echo "</div>";

$valorTotal = number_format($r['total_valor'],0,',','.');
echo "<div style='margin-top:8px;font-size:14px;color:#334155'>
💰 Total multas: <strong>$$valorTotal</strong>
</div>";
?>

</div>
</a>

<?php 
$posicion++;
endwhile;


/* ===========================
   RANKING MES ACTUAL
=========================== */

echo "<hr style='margin:35px 0'>";
echo "<h3 style='margin-bottom:15px'>🔥 Ranking Mes Actual</h3>";

$rankingMes = $conn->query("
  SELECT c.rut, c.nombre, COUNT(i.id) total, SUM(i.valor) total_valor
  FROM conductores c
  INNER JOIN partes i 
    ON c.rut = i.rut 
  WHERE YEAR(i.fecha_recepcion) = $year
  AND MONTH(i.fecha_recepcion) = $mesActual
  GROUP BY c.rut, c.nombre
  HAVING total > 0
  ORDER BY total DESC
");

$posicionMes = 1;

while($m = $rankingMes->fetch_assoc()):

$medallaMes = "";
if($posicionMes == 1) $medallaMes = "🥇";
elseif($posicionMes == 2) $medallaMes = "🥈";
elseif($posicionMes == 3) $medallaMes = "🥉";
?>

<a href="historial.php?rut=<?= $m['rut'] ?>" class="ranking-link">
<div class="ranking-card">

  <div class="ranking-top">
    <strong><?= $medallaMes ?> <?= $m['nombre'] ?></strong>
    <span class="ranking-total">multas este mes: <?= $m['total'] ?> </span>
  </div>

<?php
/* ===== DETALLE POR MES (opcional, pero consistente con anual) ===== */
$detalleMes = $conn->query("
  SELECT MONTH(fecha_recepcion) mes, COUNT(*) cantidad
  FROM partes
  WHERE rut='{$m['rut']}'
  AND YEAR(fecha_recepcion)=$year
  GROUP BY MONTH(fecha_recepcion)
");

echo "<div class='ranking-detalle'>";
while($d = $detalleMes->fetch_assoc()){
 $meses = [
  1=>"enero", 2=>"febrero", 3=>"marzo", 4=>"abril",
  5=>"mayo", 6=>"junio", 7=>"julio", 8=>"agosto",
  9=>"septiembre", 10=>"octubre", 11=>"noviembre", 12=>"diciembre"
 ];

 $mesNombre = $meses[$d['mes']];
 echo "<span>{$d['cantidad']} en $mesNombre</span>";
}
echo "</div>";

/* ===== TOTAL EN DINERO ===== */
$valorTotalMes = number_format($m['total_valor'],0,',','.');
echo "<div style='margin-top:8px;font-size:14px;color:#334155'>
💰 Total multas: <strong>$$valorTotalMes</strong>
</div>";
?>

</div>
</a>

<?php 
$posicionMes++;
endwhile;

?>