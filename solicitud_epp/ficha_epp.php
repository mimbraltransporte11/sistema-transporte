<?php
include 'db.php';

/* ===== CONFIG ===== */
$STOCK_CRITICO = 5;
$STOCK_BAJO    = 15;

/* ===== REPONER STOCK ===== */
if(isset($_POST['reponer'])){
  $epp=$_POST['epp'];
  $cantidad=(int)$_POST['cantidad'];
  $conn->query("UPDATE elementos_epp SET cantidad=cantidad+$cantidad WHERE epp='$epp'");
  header("Location: stock_epp.php"); exit;
}

/* ===== NUEVO EPP ===== */
if(isset($_POST['nuevo_epp'])){
  $epp=$_POST['nuevo_nombre'];
  $cantidad=(int)$_POST['nuevo_cantidad'];
  $conn->query("INSERT INTO elementos_epp (epp,cantidad) VALUES ('$epp',$cantidad)");
  header("Location: stock_epp.php"); exit;
}

/* ===== STOCK ===== */
$rows=[]; $criticos=[];
$q=$conn->query("SELECT * FROM elementos_epp ORDER BY epp");
while($r=$q->fetch_assoc()){
  if($r['cantidad'] <= $STOCK_CRITICO) $criticos[]=$r['epp'];
  $rows[]=$r;
}

/* ===== TRABAJADORES ===== */
$trabajadores=[];
$q=$conn->query("SELECT DISTINCT nombre FROM solicitud ORDER BY nombre");
while($r=$q->fetch_assoc()) $trabajadores[]=$r['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Perfil EPP</title>


<link rel="icon" type="image/png" href="favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root{
  --primary:#0f4c81;--primary-dark:#0a365c;
  --success:#22c55e;--warning:#f59e0b;--danger:#ef4444;
}
*{box-sizing:border-box}
body{font-family:Inter;background:linear-gradient(135deg,#f5f7fb,#e8eef5);padding:30px}
/* ===== BANNER (UNIFICADO GLOBAL) ===== */
.banner-wrap{
  position: relative;
  margin-bottom: 35px;
}

.banner{
  width: 100%;
  height: 220px;
  object-fit: cover;
  border-radius: 20px;
  display: block;
}

.banner-overlay{
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg,
    rgba(15,23,42,0.75),
    rgba(15,23,42,0.25),
    transparent
  );
  border-radius: 20px;
}

.banner-text{
  position: absolute;
  bottom: 20px;
  left: 30px;
  color: #fff;
  z-index: 2;
}

.banner-text h1{
  margin: 0;
  font-size: 28px;
  font-weight: 700;
}

.actions{display:flex;justify-content:space-between;margin-bottom:20px;gap:10px}
.actions a,.actions button{padding:12px 18px;border-radius:14px;font-weight:700;border:none;cursor:pointer;text-decoration:none}
.back{background:#e2e8f0;color:#0f172a}
.new{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff}
.card{background:#fff;border-radius:22px;padding:30px;box-shadow:0 25px 50px rgba(0,0,0,.1);margin-bottom:25px}
table{width:100%;border-collapse:collapse}
th,td{padding:14px;text-align:center}
th{background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff}
tr:nth-child(even){background:#f8fafc}
.badge{padding:6px 14px;border-radius:999px;color:#fff;font-weight:700;font-size:12px}
.ok{background:var(--success)}.bajo{background:var(--warning)}.critico{background:var(--danger)}
.alert{background:#fff7ed;border-left:6px solid var(--danger);padding:18px;border-radius:14px;margin-bottom:25px}
button{padding:10px 16px;border:none;border-radius:14px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;cursor:pointer}
input,select{padding:12px 14px;border-radius:14px;border:1px solid #cbd5e1;width:100%}
.filters{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:15px}
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:10}
.modal-content{background:#fff;width:380px;margin:10% auto;padding:25px;border-radius:18px}
@media(max-width:700px){
  body{padding:15px}
  table{display:block;overflow-x:auto}
  .filters{grid-template-columns:1fr}
}
/* FOOTER */
footer{
  margin-top:60px;
  padding:30px;
  border-radius:20px;
  background:linear-gradient(135deg,#1e3a8a,#2563eb);
  color:white;
  text-align:center;
  box-shadow:0 20px 40px rgba(37,99,235,.35);
}

/* ===== RELOJ PREMIUM EN BANNER ===== */

.banner-datetime{
  position:absolute;
  right:25px;
  bottom:20px;

  background:rgba(255,255,255,0.18);
  backdrop-filter: blur(10px);

  padding:12px 18px;
  border-radius:16px;

  color:white;
  text-align:right;

  box-shadow:0 10px 25px rgba(0,0,0,.35);
  border:1px solid rgba(255,255,255,0.25);
}

#banner-date{
  font-size:12px;
  letter-spacing:.5px;
  opacity:.85;
  text-transform:capitalize;
}

#banner-time{
  font-size:26px;
  font-weight:800;
  margin-top:2px;
}

</style>
</head>

<body>
   <?php include '../shared/menu.php'; ?>
<!-- BANNER -->
<div class="banner-wrap">
  <img src="banner.jpg" class="banner">
  <div class="banner-overlay"></div>

  <div class="banner-text">
    <h1>Ficha Personal EPP</h1>
  </div>

  <div class="banner-datetime">
    <div id="banner-date"></div>
    <div id="banner-time"></div>
  </div>
</div>


<div class="actions">
  <a href="index.php" class="back">⬅ Volver</a>
</div>


<!-- FICHA TRABAJADOR -->
<div class="card">
<h3>🧾 Ficha de consumo por trabajador</h3>

<form method="get" class="filters">
  <select name="trabajador" required>
    <option value="">Trabajador</option>
    <?php foreach($trabajadores as $t): ?>
      <option <?=($_GET['trabajador']??'')==$t?'selected':''?>><?= $t ?></option>
    <?php endforeach; ?>
  </select>

  <input type="date" name="desde">
  <input type="date" name="hasta">
  <button>Buscar</button>
</form>

<?php
if(isset($_GET['trabajador'])){
  $t=$_GET['trabajador'];
  $d=$_GET['desde'];
  $h=$_GET['hasta'];

$t = $_GET['trabajador'];
$d = $_GET['desde'] ?? '';
$h = $_GET['hasta'] ?? '';

if(!empty($d) && !empty($h)){

  // 🔹 CON FILTRO DE FECHA
  $stmt = $conn->prepare("
    SELECT sd.epp, SUM(sd.cantidad) total
    FROM solicitud s
    JOIN solicitud_detalle sd ON sd.solicitud_id = s.id
    WHERE s.nombre = ?
    AND s.fecha BETWEEN ? AND ?
    GROUP BY sd.epp
  ");

  $stmt->bind_param("sss", $t, $d, $h);

}else{

  // 🔹 SIN FECHA → TODO EL HISTORIAL
  $stmt = $conn->prepare("
    SELECT sd.epp, SUM(sd.cantidad) total
    FROM solicitud s
    JOIN solicitud_detalle sd ON sd.solicitud_id = s.id
    WHERE s.nombre = ?
    GROUP BY sd.epp
  ");

  $stmt->bind_param("s", $t);
}

$stmt->execute();
$q = $stmt->get_result();

?>
<br>
<h4><?= $t ?> (<?= $d ?> → <?= $h ?>)</h4>
<table>
<tr><th>EPP</th><th>Total solicitado</th></tr>
<?php
$total=0;
while($r=$q->fetch_assoc()):
$total+=$r['total'];
?>
<tr>
<td><?= $r['epp'] ?></td>
<td><?= $r['total'] ?></td>
</tr>
<?php endwhile; ?>
<tr>
<th>TOTAL</th>
<th><?= $total ?></th>
</tr>
</table>
<?php } ?>
</div>

<footer>
<strong>Transportes Mimbral LTDA</strong><br>
Gestión empresarial · © <?= date('Y') ?>
</footer>

<script>
function abrirReponer(e){document.getElementById('epp').value=e;modalReponer.style.display='block'}
function abrirNuevo(){modalNuevo.style.display='block'}
function cerrar(){modalNuevo.style.display='none';modalReponer.style.display='none'}
</script>

<script>
function actualizarBannerHora(){
  const now = new Date();

  document.getElementById('banner-date').innerText =
    now.toLocaleDateString('es-CL',{
      weekday:'long',
      day:'2-digit',
      month:'long',
      year:'numeric'
    });

  document.getElementById('banner-time').innerText =
    now.toLocaleTimeString('es-CL',{
      hour:'2-digit',
      minute:'2-digit',
      second:'2-digit'
    });
}

setInterval(actualizarBannerHora,1000);
actualizarBannerHora();
</script>

</body>
</html>