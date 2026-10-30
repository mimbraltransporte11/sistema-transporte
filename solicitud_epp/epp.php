<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Sistema EPP</title>

<link rel="icon" type="image/png" href="favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
body{font-family:Inter;background:#f5f7fb;padding:30px}
.card{background:#fff;border-radius:20px;padding:30px;box-shadow:0 20px 40px rgba(0,0,0,.1)}
table{width:100%;border-collapse:collapse;margin-top:20px}
th,td{padding:14px;text-align:center}
th{background:#0f4c81;color:#fff}
</style>
</head>
<body>

<h2>Inventario EPP</h2>

<div class="card">
<form method="post" action="guardar_epp.php">
  <input type="text" name="epp" placeholder="Elemento EPP" required>
  <input type="number" name="cantidad" placeholder="Cantidad" required>
  <button>Agregar</button>
</form>

<table>
<tr><th>EPP</th><th>Cantidad</th></tr>
<?php
$q=$conn->query("SELECT * FROM elementos_epp ORDER BY epp");
while($r=$q->fetch_assoc()){
  echo "<tr><td>{$r['epp']}</td><td>{$r['cantidad']}</td></tr>";
}
?>
</table>
</div>

</body>
</html>
