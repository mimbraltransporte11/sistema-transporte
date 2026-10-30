<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard Compras EPP</title>

<link rel="icon" type="image/png" href="favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
<?php /* mismo estilo del sistema */ ?>
:root{
  --primary:#0f4c81;
  --primary-dark:#0a365c;
}
body{
  font-family:Inter;
  background:#f5f7fb;
  padding:30px;
}
.card{
  background:white;
  padding:30px;
  border-radius:22px;
  box-shadow:0 25px 50px rgba(0,0,0,.1);
  margin-bottom:30px;
}
.stats{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:20px;
}
.stat{
  padding:25px;
  border-radius:18px;
  background:linear-gradient(135deg,#e0f2fe,#f8fafc);
}
.stat h2{margin:0}
</style>
</head>

<body>
<?php include '../shared/menu.php'; ?>

<div class="card">
<h2>📊 Dashboard Compras EPP</h2>

<div class="stats">
<?php
$t=$conn->query("SELECT SUM(cantidad*precio) total, SUM(cantidad) unidades FROM facturas_epp")->fetch_assoc();
?>
<div class="stat">
  <p>Total Gastado</p>
  <h2>$ <?= number_format($t['total'],0,',','.') ?></h2>
</div>

<div class="stat">
  <p>Unidades Compradas</p>
  <h2><?= $t['unidades'] ?></h2>
</div>

<div class="stat">
  <p>Facturas Registradas</p>
  <h2><?= $conn->query("SELECT COUNT(*) c FROM facturas_epp")->fetch_assoc()['c'] ?></h2>
</div>
</div>
</div>

<div class="card">
<canvas id="grafico"></canvas>
</div>

<?php
$data = $conn->query("
  SELECT epp, SUM(cantidad) total 
  FROM facturas_epp 
  GROUP BY epp
");
$labels=[];
$values=[];
while($r=$data->fetch_assoc()){
  $labels[]=$r['epp'];
  $values[]=$r['total'];
}
?>

<script>
new Chart(document.getElementById('grafico'),{
  type:'bar',
  data:{
    labels:<?= json_encode($labels) ?>,
    datasets:[{
      label:'Unidades Compradas',
      data:<?= json_encode($values) ?>
    }]
  }
});
</script>

</body>
</html>
