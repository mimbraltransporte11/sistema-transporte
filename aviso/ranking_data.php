<?php

include 'db.php';

$anio = isset($_GET['anio'])
? intval($_GET['anio'])
: date('Y');

$mesActual = date('n');

/* ===========================
   RANKING ANUAL
=========================== */

echo "<h3 style='margin-bottom:15px'>🏆 Ranking Año $anio</h3>";

$query = $conn->query("

SELECT
rut,
nombre,
COUNT(*) as total

FROM avisos

WHERE YEAR(fecha) = $anio

GROUP BY rut,nombre

HAVING total > 0

ORDER BY total DESC, nombre ASC

LIMIT 20

");

$posicion = 1;

while($row = $query->fetch_assoc()):

$medalla = "";

if($posicion == 1){
    $medalla = "🥇";
}
elseif($posicion == 2){
    $medalla = "🥈";
}
elseif($posicion == 3){
    $medalla = "🥉";
}

?>

<a
href="historial.php?rut=<?= $row['rut'] ?>"
class="ranking-link"
>

<div class="ranking-card">

<div class="ranking-top">

<div>

<strong>
<?= $medalla ?> <?= $row['nombre'] ?>
</strong>

</div>

<div class="ranking-total">

total de avisos: <?= $row['total'] ?>

</div>

</div>

<?php

$detalle = $conn->query("
SELECT 
MONTH(fecha) mes,
COUNT(*) cantidad
FROM avisos
WHERE rut='{$row['rut']}'
AND YEAR(fecha) = $anio
GROUP BY MONTH(fecha)
ORDER BY MONTH(fecha)
");

echo "<div class='ranking-detalle'>";

$meses = [
1=>"enero",
2=>"febrero",
3=>"marzo",
4=>"abril",
5=>"mayo",
6=>"junio",
7=>"julio",
8=>"agosto",
9=>"septiembre",
10=>"octubre",
11=>"noviembre",
12=>"diciembre"
];

while($d = $detalle->fetch_assoc()){

$mesNombre = $meses[$d['mes']];

echo "
<span style='
display:inline-block;
background:#e0e7ff;
padding:6px 12px;
border-radius:999px;
margin-right:8px;
margin-top:8px;
font-size:13px;
'>
{$d['cantidad']} en $mesNombre
</span>
";

}

echo "</div>";

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

SELECT
rut,
nombre,
COUNT(*) as total

FROM avisos

WHERE YEAR(fecha) = $anio
AND MONTH(fecha) = $mesActual

GROUP BY rut,nombre

HAVING total > 0

ORDER BY total DESC, nombre ASC

LIMIT 20

");

$posicionMes = 1;

while($m = $rankingMes->fetch_assoc()):

$medallaMes = "";

if($posicionMes == 1){
    $medallaMes = "🥇";
}
elseif($posicionMes == 2){
    $medallaMes = "🥈";
}
elseif($posicionMes == 3){
    $medallaMes = "🥉";
}

?>

<a
href="historial.php?rut=<?= $m['rut'] ?>"
class="ranking-link"
>

<div class="ranking-card">

<div class="ranking-top">

<div>

<strong>
<?= $medallaMes ?> <?= $m['nombre'] ?>
</strong>

</div>

<div class="ranking-total">

avisos este mes: <?= $m['total'] ?>

</div>

</div>

</div>

</a>

<?php

$posicionMes++;

endwhile;

?>