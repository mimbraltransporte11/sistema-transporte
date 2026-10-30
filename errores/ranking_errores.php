<?php
include 'db.php';

$year = $_GET['anio'] ?? date('Y');
$mesActual = date('n');

/* ===========================
   RANKING ANUAL
=========================== */

echo "<h3 style='margin-bottom:15px'>Ranking Año $year</h3>";

$ranking = $conn->query("
  SELECT c.rut, c.nombre, COUNT(i.id) total
  FROM conductores c
  INNER JOIN infracciones i 
    ON c.rut = i.rut 
  WHERE YEAR(i.fecha) = $year
  GROUP BY c.rut
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

<a href="historial.php?rut=<?= $r['rut'] ?>" style="text-decoration:none; color:inherit;">
  <div class="ranking-card" style="cursor:pointer;">

    <div class="ranking-top">
      <strong><?= $medalla ?> <?= $r['nombre'] ?></strong>
      <span class="ranking-total">total de faltas: <?= $r['total'] ?> </span>
    </div>

    <div class="ranking-detalle">
      <?php
      $detalle = $conn->query("
        SELECT MONTH(fecha) mes, COUNT(*) cantidad
        FROM infracciones
        WHERE rut='{$r['rut']}' 
        AND YEAR(fecha)=$year
        GROUP BY MONTH(fecha)
        ORDER BY MONTH(fecha)
      ");

      $meses = [
        1=>"enero", 2=>"febrero", 3=>"marzo", 4=>"abril",
        5=>"mayo", 6=>"junio", 7=>"julio", 8=>"agosto",
        9=>"septiembre", 10=>"octubre", 11=>"noviembre", 12=>"diciembre"
      ];

      while($d = $detalle->fetch_assoc()):
        $mesNombre = $meses[$d['mes']];
      ?>
        <span><?= $d['cantidad'] ?> en <?= $mesNombre ?></span>
      <?php endwhile; ?>
    </div>

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
  SELECT c.rut, c.nombre, COUNT(i.id) total
  FROM conductores c
  INNER JOIN infracciones i 
    ON c.rut = i.rut 
  WHERE YEAR(i.fecha) = $year
  AND MONTH(i.fecha) = $mesActual
  GROUP BY c.rut
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

<a href="historial.php?rut=<?= $m['rut'] ?>" style="text-decoration:none; color:inherit;">
  <div class="ranking-card" style="cursor:pointer;">

    <div class="ranking-top">
      <strong><?= $medallaMes ?> <?= $m['nombre'] ?></strong>
      <span class="ranking-total">faltas este mes: <?= $m['total'] ?> </span>
    </div>

  </div>
</a>

<?php 
$posicionMes++;
endwhile;
?>