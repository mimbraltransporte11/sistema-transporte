<?php
include 'db.php';
$id = (int)$_GET['id'];

$f = $conn->query("
  SELECT * FROM facturas_epp WHERE id=$id
")->fetch_assoc();

$d = $conn->query("
  SELECT * FROM facturas_epp_detalle WHERE factura_id=$id
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Detalle Factura <?= $f['num_factura'] ?></title>

<link rel="icon" type="image/png" href="favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
:root{
  --bg:#f5f7fb;
  --card:#ffffff;
  --primary:#0f4c81;
  --primary-dark:#0a365c;
  --text:#0f172a;
}
*{box-sizing:border-box}
body{
  font-family:'Inter', sans-serif;
  background:linear-gradient(135deg,#f5f7fb,#e8eef5);
  padding:30px;
  color:var(--text);
}

/* ===== BANNER ===== */
.banner-wrap{position:relative;margin-bottom:35px}
.banner{
  width:100%;
  height:220px;
  object-fit:cover;
  border-radius:20px;
}
.banner-overlay{
  position:absolute;
  inset:0;
  background:linear-gradient(90deg,rgba(15,23,42,.75),rgba(15,23,42,.25),transparent);
  border-radius:20px;
}
.banner-text{
  position:absolute;
  bottom:20px;
  left:30px;
  color:white;
}
.banner-text h1{
  margin:0;
  font-size:26px;
  font-weight:800;
}

/* ===== CARD ===== */
.card{
  background:var(--card);
  border-radius:22px;
  padding:30px;
  box-shadow:0 25px 50px rgba(0,0,0,.1);
}

/* ===== TITLES ===== */
.section-title{
  font-size:20px;
  font-weight:700;
  margin-bottom:20px;
  display:flex;
  align-items:center;
  gap:10px;
}
.section-title span{
  width:6px;
  height:26px;
  border-radius:999px;
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
}

/* ===== INFO ===== */
.info{
  display:flex;
  justify-content:space-between;
  margin-bottom:25px;
  flex-wrap:wrap;
  gap:10px;
}
.info div{
  font-weight:600;
}

/* ===== TABLE ===== */
table{
  width:100%;
  border-collapse:collapse;
  border-radius:18px;
  overflow:hidden;
}
th,td{
  padding:14px;
  text-align:center;
}
th{
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  color:white;
}
tr:nth-child(even){background:#f8fafc}

/* ===== TOTAL ===== */
.total{
  text-align:right;
  margin-top:20px;
  font-size:20px;
  font-weight:800;
}

/* ===== ACTIONS ===== */
.actions{
  margin-top:30px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  flex-wrap:wrap;
  gap:12px;
}
.actions a{
  text-decoration:none;
  padding:12px 22px;
  border-radius:14px;
  font-weight:700;
}
.back{
  background:#e5e7eb;
  color:#111827;
}
.image{
  background:linear-gradient(135deg,#22c55e,#16a34a);
  color:white;
}

/* FOOTER */
footer{
  margin-top:60px;
  padding:30px;
  border-radius:20px;
  background:linear-gradient(135deg,#1e3a8a,#2563eb);
  color:white;
  text-align:center;
}

/* ===== RELOJ EN BANNER ===== */

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

<div class="banner-wrap">
  <img src="banner.jpg" class="banner">
  <div class="banner-overlay"></div>

  <div class="banner-text">
    <h1>Detalle Factura Nº <?= $f['num_factura'] ?></h1>
  </div>

  <!-- FECHA / HORA PREMIUM -->
  <div class="banner-datetime">
    <div id="banner-date"></div>
    <div id="banner-time"></div>
  </div>
</div>

<div class="card">

<div class="section-title">
  <span></span> 🧾 Información de la Factura
</div>

<div class="info">
  <div>🏢 Proveedor: <strong><?= $f['proveedor_nombre'] ?></strong></div>
  <div>🆔 RUT: <strong><?= $f['proveedor_rut'] ?></strong></div>
  <div>📑 Factura: <strong><?= $f['num_factura'] ?></strong></div>
  <div>📅 Fecha: <strong><?= date('d-m-Y', strtotime($f['fecha'])) ?></strong></div>
</div>

<table>
<tr>
  <th>EPP</th>
  <th>Cantidad</th>
  <th>Precio unitario</th>
  <th>Subtotal</th>
</tr>

<?php while($r = $d->fetch_assoc()): ?>
<tr>
  <td><?= $r['epp'] ?></td>
  <td><?= $r['cantidad'] ?></td>
  <td>$ <?= number_format($r['precio'],0,',','.') ?></td>
  <td><strong>$ <?= number_format($r['subtotal'],0,',','.') ?></strong></td>
</tr>
<?php endwhile; ?>
</table>

<div class="total">
  Total Factura: $ <?= number_format($f['total'],0,',','.') ?>
</div>

<div class="actions">
  <a href="facturas_epp.php" class="back">⬅ Volver</a>

  <?php if($f['imagen']): ?>
    <a href="<?= $f['imagen'] ?>" target="_blank" class="image">📷 Ver Imagen</a>
  <?php endif; ?>
</div>

</div>

<footer>
<strong>Transportes Mimbral LTDA</strong><br>
© <?= date('Y') ?>
</footer>


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
