<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Inventario - Sistema Patio</title>


<link rel="icon" type="image/png" href="favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root{
  --bg:#f5f7fb;
  --card:#ffffff;
  --primary:#0f4c81;
  --primary-dark:#0a365c;
  --success:#22c55e;
  --danger:#ef4444;
  --text:#0f172a;
  --muted:#64748b;
}

*{box-sizing:border-box}

body{
  font-family:'Inter', sans-serif;
  background:linear-gradient(135deg,#f5f7fb,#e8eef5);
  padding:30px;
  color:var(--text);
}

h2{
  font-size:28px;
  font-weight:700;
  margin-bottom:20px;
}

/* CARD */
.card{
  background:var(--card);
  border-radius:20px;
  padding:25px;
  box-shadow:0 20px 40px rgba(15,23,42,.08);
}


/* TABLE */
/* ===== TABLE PREMIUM ===== */

table{
  width:100%;
  border-collapse:collapse;
  margin-top:20px;
  background:white;
  border-radius:18px;
  overflow:hidden;
  box-shadow:0 20px 40px rgba(15,23,42,.08);
}

th,td{
  padding:16px;
  text-align:center;
  font-size:14px;
}

th{
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  color:white;
  font-weight:600;
}

tr:nth-child(even){
  background:#f8fafc;
}

tr:hover{
  background:#f1f5f9;
}

/* FORM */
.form{
  display:flex;
  gap:12px;
  margin-bottom:25px;
  background:white;
  padding:18px;
  border-radius:18px;
  box-shadow:0 10px 25px rgba(15,23,42,.06);
}

.form input{
  padding:12px 14px;
  border-radius:14px;
  border:1px solid #cbd5e1;
  flex:1;
}

button{
  padding:12px 18px;
  border:none;
  border-radius:14px;
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  color:#fff;
  font-weight:600;
  cursor:pointer;
}

button:hover{opacity:.9}

.back{
  margin-top:20px;
  display:inline-block;
  text-decoration:none;
  color:var(--primary);
  font-weight:600;
}

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


/* ===== CARD MEJORA ===== */
.card{
  background:var(--card);
  border-radius:22px;
  padding:30px;
  box-shadow:0 25px 50px rgba(15,23,42,.1);
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
    <h1>Inventario de Equipos</h1>
    <p>Gestión de stock y existencias</p>
  </div>

  <div class="banner-datetime">
    <div id="banner-date"></div>
    <div id="banner-time"></div>
  </div>
</div>

<form method="post" class="form" action="guardar_inventario.php">
  <input type="text" name="equipo" placeholder="Nombre del equipo" required>
  <input type="number" name="cantidad" placeholder="Cantidad" required>
  <button>Agregar</button>
</form>

<table>
<tr>
  <th>Equipo</th>
  <th>Cantidad</th>
  <th>Acción</th>
</tr>

<?php
$q=$conn->query("SELECT * FROM inventario ORDER BY equipo ASC");
while($r=$q->fetch_assoc()){
  echo "<tr>
    <td>{$r['equipo']}</td>
    <td><strong>{$r['cantidad']}</strong></td>
    <td>
      <button 
        class='btn btn-primary'
        onclick=\"editarStock({$r['id']}, {$r['cantidad']})\"
      >
        ✏️ Actualizar
      </button>
    </td>
  </tr>";
}
?>

</table>

</div>

<div class="form-actions" style="margin-top:15px">
  <a href="index.php" class="btn">⬅ Volver</a>
</div>


<!-- MODAL EDITAR STOCK -->
<div id="modal-stock" style="
  position:fixed;
  inset:0;
  background:rgba(15,23,42,.6);
  display:none;
  align-items:center;
  justify-content:center;
  z-index:999;
">

  <div style="
    background:white;
    border-radius:18px;
    padding:30px;
    width:100%;
    max-width:380px;
    box-shadow:0 30px 60px rgba(0,0,0,.25);
  ">

    <h3 style="margin:0 0 15px;font-size:20px">✏️ Actualizar stock</h3>

    <form method="post" action="editar_stock.php">
      <input type="hidden" name="id" id="stock-id">

      <label style="font-weight:600">Cantidad</label>
      <input 
        type="number"
        name="cantidad"
        id="stock-cantidad"
        min="0"
        required
        style="
          width:100%;
          padding:14px;
          margin-top:8px;
          border-radius:12px;
          border:1px solid #cbd5e1;
          font-size:15px;
        "
      >

      <div style="
        display:flex;
        gap:10px;
        justify-content:flex-end;
        margin-top:25px;
      ">
        <button 
          type="button"
          class="btn"
          onclick="cerrarModal()"
        >
          Cancelar
        </button>

        <button 
          type="submit"
          class="btn btn-primary"
        >
          Guardar
        </button>
      </div>
    </form>

  </div>
</div>


<footer>
<strong>Transportes Mimbral LTDA</strong><br>
Gestión empresarial · © <?= date('Y') ?>
</footer>

</body>
<script>
function editarStock(id, actual){
  document.getElementById('stock-id').value = id;
  document.getElementById('stock-cantidad').value = actual;
  document.getElementById('modal-stock').style.display = 'flex';
}

function cerrarModal(){
  document.getElementById('modal-stock').style.display = 'none';
}
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

</html>
