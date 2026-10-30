<?php
include 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut = trim($_POST['rut']);
    $nombre = trim($_POST['nombre']);
    $area = trim($_POST['area']);

    $check = $conn->query("SELECT rut FROM conductor WHERE rut='$rut'");
    if ($check->num_rows > 0) {
        $error = '⚠ El RUT ya existe en el sistema';
    } else {
        $conn->query("INSERT INTO conductor (rut,nombre,area) VALUES ('$rut','$nombre','$area')");
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo Conductor</title>

<link rel="icon" type="image/png" href="favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
/* === MISMO DISEÑO SISTEMA FLOTA === */
:root{
  --primary:#2563eb;
  --primary-dark:#1e40af;
  --danger:#dc2626;
  --bg1:#f1f5f9;
  --bg2:#e0e7ff;
  --card:#ffffff;
  --text:#0f172a;
  --muted:#64748b;
}
*{box-sizing:border-box}
body{
  margin:0;
  padding:30px;
  min-height:100vh;
  background:linear-gradient(180deg,var(--bg1),var(--bg2));
  font-family:Inter,sans-serif;
}

/* BANNER */
.banner-wrap{position:relative;margin-bottom:35px}
.banner{width:100%;max-height:220px;object-fit:cover;border-radius:20px}
.banner-overlay{
  position:absolute;inset:0;
  background:linear-gradient(90deg,rgba(15,23,42,.75),transparent);
  border-radius:20px
}
.banner-text{position:absolute;bottom:20px;left:30px;color:white}

/* CARD */
.card{
  background:var(--card);
  padding:30px;
  border-radius:18px;
  box-shadow:0 10px 30px rgba(0,0,0,.08);
  max-width:800px;
  margin:0 auto 40px;
}

/* FORM */
.form-group{margin-bottom:18px}
label{display:block;font-weight:600;margin-bottom:6px}
input{
  width:100%;
  padding:12px;
  border-radius:12px;
  border:1px solid #cbd5f5;
  font-size:15px;
}

/* BUTTONS */
.btn{
  padding:12px 18px;
  border-radius:12px;
  text-decoration:none;
  font-weight:600;
  border:none;
  cursor:pointer;
  background:#64748b;
  color:white;
}
.btn-primary{
  background:linear-gradient(135deg,var(--primary),var(--primary-dark))
}
.form-actions{
  display:flex;
  gap:10px;
  justify-content:flex-end;
  margin-top:25px;
}

/* ALERT */
.alert{
  background:#fee2e2;
  color:#991b1b;
  padding:14px;
  border-radius:12px;
  margin-bottom:20px;
  font-weight:600;
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

/* ===== RELOJ PREMIUM ===== */

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
    <h1>Nuevo Conductor</h1>
    <p>Registro de conductores del sistema</p>
  </div>

  <div class="banner-datetime">
    <div id="banner-date"></div>
    <div id="banner-time"></div>
  </div>
</div>

<div class="card">

<?php if($error): ?>
  <div class="alert"><?= $error ?></div>
<?php endif; ?>

<form method="POST">

  <div class="form-group">
    <label>RUT</label>
    <input type="text" name="rut" required placeholder="12345678-9">
  </div>

  <div class="form-group">
    <label>Nombre</label>
    <input type="text" name="nombre" required>
  </div>

  <div class="form-group">
    <label>Área</label>
    <input type="text" name="area" required>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">💾 Guardar</button>
    <a href="index.php" class="btn">Cancelar</a>
  </div>

</form>
</div>

<footer>
<strong>Transportes Mimbral LTDA</strong><br>
Sistema de Amonestaciones · <?= date('Y') ?>
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
