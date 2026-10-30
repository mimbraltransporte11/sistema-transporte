<?php
include 'db.php';

$error = '';
$datos = null;

/* BUSCAR CONDUCTOR */
if (isset($_POST['buscar'])) {
    $busqueda = trim($_POST['busqueda']);
    $res = $conn->query("
        SELECT * FROM conductor 
        WHERE rut = '$busqueda' 
        OR nombre LIKE '%$busqueda%' 
        LIMIT 1
    ");

    if ($res->num_rows > 0) {
        $datos = $res->fetch_assoc();
    } else {
        $error = '❌ Conductor no encontrado';
    }
}

/* GUARDAR AMONESTACIÓN */
if (isset($_POST['guardar'])) {
    $rut = $_POST['rut'];
    $nombre = $_POST['nombre'];
    $area = $_POST['area'];
    $observacion = trim($_POST['observacion']);
    $fecha = $_POST['fecha'];

       /* IMAGEN (OPCIONAL) */
    $imagen = null;

    if (!empty($_FILES['imagen']['name'])) {
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imagen = 'inf_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['imagen']['tmp_name'], 'uploads/' . $imagen);
    }

    $conn->query("
        INSERT INTO infraccion (rut,nombre,area,observacion,fecha,imagen)
        VALUES ('$rut','$nombre','$area','$observacion','$fecha','$imagen')
    ");

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registrar Amonestación</title>

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
  color:var(--text);
}

/* BANNER */
.banner-wrap{position:relative;margin-bottom:35px}
.banner{
  width:100%;
  max-height:220px;
  object-fit:cover;
  border-radius:20px;
}
.banner-overlay{
  position:absolute;inset:0;
  background:linear-gradient(90deg,rgba(15,23,42,.75),transparent);
  border-radius:20px;
}
.banner-text{position:absolute;bottom:20px;left:30px;color:white}

/* CARD */
.card{
  background:var(--card);
  padding:30px;
  border-radius:18px;
  box-shadow:0 10px 30px rgba(0,0,0,.08);
  max-width:900px;
  margin:0 auto 40px;
}

/* FORM */
.form-group{margin-bottom:18px}
label{display:block;font-weight:600;margin-bottom:6px}
input,textarea{
  width:100%;
  padding:12px;
  border-radius:12px;
  border:1px solid #cbd5f5;
  font-size:15px;
}
textarea{min-height:110px;resize:vertical}

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
.btn-danger{
  background:linear-gradient(135deg,#ef4444,#b91c1c)
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
  margin:20px 0;
  font-weight:600;
}

hr{border:none;border-top:1px solid #e5e7eb;margin:30px 0}

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
    <h1>Registrar Amonestación</h1>
  </div>

  <div class="banner-datetime">
    <div id="banner-date"></div>
    <div id="banner-time"></div>
  </div>
</div>

<div class="card">

<!-- BUSCADOR -->
<form method="POST">
  <div class="form-group">
    <label>Buscar por RUT o Nombre</label>
    <input type="text" name="busqueda" required placeholder="12345678-9 o Juan Pérez">
  </div>
  <button type="submit" name="buscar" class="btn btn-primary">🔍 Buscar</button>
</form>

<?php if($error): ?>
  <div class="alert"><?= $error ?></div>
<?php endif; ?>

<?php if($datos): ?>
<hr>

<form method="POST" enctype="multipart/form-data">


  <input type="hidden" name="rut" value="<?= $datos['rut'] ?>">

  <div class="form-group">
    <label>Nombre</label>
    <input type="text" name="nombre" value="<?= $datos['nombre'] ?>" readonly>
  </div>

  <div class="form-group">
    <label>Área</label>
    <input type="text" name="area" value="<?= $datos['area'] ?>" readonly>
  </div>

  <div class="form-group">
    <label>Observación</label>
    <textarea name="observacion" required></textarea>
  </div>

    <div class="form-group">
    <label>Imagen (opcional)</label>
    <input type="file" name="imagen" accept="image/*">
  </div>

  <div class="form-group">
    <label>Fecha</label>
    <input type="date" name="fecha" required value="<?= date('Y-m-d') ?>">
  </div>

  <div class="form-actions">
    <button type="submit" name="guardar" class="btn btn-danger">⚠ Registrar</button>
    <a href="index.php" class="btn">Cancelar</a>
  </div>

</form>
<?php endif; ?>

</div>
<?php if (!$datos): ?>
<div class="form-actions" style="margin-top:15px">
  <a href="index.php" class="btn">⬅ Volver</a>
</div>
<?php endif; ?>

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
