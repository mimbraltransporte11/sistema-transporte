<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Facturas EPP</title>


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
.banner-text h1{margin:0;font-size:28px;font-weight:700}

/* ===== CARD ===== */
.card{
  background:var(--card);
  border-radius:22px;
  padding:30px;
  box-shadow:0 25px 50px rgba(0,0,0,.1);
}

/* ===== ACTIONS ===== */
.actions{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:20px;
}

.actions a{
  text-decoration:none;
  padding:12px 20px;
  border-radius:14px;
  background:linear-gradient(135deg,#22c55e,#16a34a);
  color:#fff;
  font-weight:700;
}

.back{
  background:#e5e7eb !important;
  color:#0f172a !important;
}
.back:hover{
  background:#d1d5db !important;
}

/* ===== TABLE ===== */
table{
  width:100%;
  border-collapse:collapse;
  border-radius:18px;
  overflow:hidden;
}
th,td{padding:14px;text-align:center}
th{
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  color:white;
}
tr:nth-child(even){background:#f8fafc}

/* ===== MODAL ===== */
.modal{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.45);
  display:none;
  justify-content:center;
  align-items:center;
  z-index:999;
}
.modal-content{
  background:white;
  padding:28px;
  border-radius:24px;
  width:760px;
  max-width:95%;
  max-height:90vh;
  overflow-y:auto;
  box-shadow:0 40px 80px rgba(0,0,0,.25);
}

/* ===== FORM ===== */
.form{display:grid;gap:16px}

.form-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:14px;
}
.form-grid .full{grid-column:1 / -1}

.form input,
.form select{
  padding:12px;
  border-radius:12px;
  border:1px solid #cbd5e1;
  width:100%;
}

.items{display:grid;gap:12px}

.item-row{
  display:grid;
  grid-template-columns:2fr 1fr 1fr 42px;
  gap:10px;
  align-items:center;
}

.remove{
  width:42px;
  height:42px;
  border:none;
  border-radius:10px;
  background:#fee2e2;
  font-size:18px;
  cursor:pointer;
}

.add{
  background:#e0f2fe;
  border:none;
  padding:12px;
  border-radius:12px;
  font-weight:700;
  cursor:pointer;
}

.form-actions{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:12px;
  margin-top:20px;
}

.cancel,
.save{
  padding:14px;
  border:none;
  border-radius:14px;
  font-weight:700;
  cursor:pointer;
}

.cancel{background:#e5e7eb}
.cancel:hover{background:#d1d5db}

.save{
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  color:white;
}

/* ===== RESPONSIVE ===== */
@media (max-width:768px){
  .form-grid{grid-template-columns:1fr}
  .item-row{grid-template-columns:1fr}
  .form-actions{grid-template-columns:1fr}
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
/* ===== ALERTA ===== */
.alert{
  display:flex;
  align-items:center;
  gap:14px;
  padding:16px 20px;
  border-radius:14px;
  margin-bottom:20px;
  font-weight:600;
  animation:fadeIn .4s ease;
}

.alert-error{
  background:linear-gradient(135deg,#fee2e2,#fecaca);
  color:#7f1d1d;
  border-left:6px solid #dc2626;
}

.alert-icon{
  font-size:26px;
}

@keyframes fadeIn{
  from{opacity:0;transform:translateY(-10px)}
  to{opacity:1;transform:translateY(0)}
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

<div class="banner-wrap">
  <img src="banner.jpg" class="banner">
  <div class="banner-overlay"></div>

  <div class="banner-text">
    <h1>Facturas EPP</h1>
  </div>

  <div class="banner-datetime">
    <div id="banner-date"></div>
    <div id="banner-time"></div>
  </div>
</div>

<div class="card">

<div class="actions">
  <a href="index.php" class="back">⬅ Volver</a>

  <div style="display:flex;gap:12px">
    <a href="#" onclick="abrirDashboard()">📊 Dashboard</a>
    <a href="#" onclick="abrirModal()">➕ Ingresar Factura</a>
  </div>
</div>
<?php if(isset($_GET['error']) && $_GET['error']=='duplicada'): ?>
  <div class="alert alert-error">
    <div class="alert-icon">⚠️</div>
    <div>
      La factura <strong>N° <?= htmlspecialchars($_GET['num']) ?></strong>
      ya existe para este proveedor.<br>
      <small>No se permiten facturas duplicadas con el mismo RUT.</small>
    </div>
  </div>
<?php endif; ?>

<div style="
  margin-bottom:18px;
  display:grid;
  grid-template-columns:2fr 1fr 1fr;
  gap:12px;
  align-items:center;
">
  <input 
    type="text" 
    id="buscador" 
    placeholder="🔍 Proveedor, RUT o N° Factura"
    style="padding:12px 16px;border-radius:14px;border:1px solid #cbd5e1"
  >

  <input 
    type="date" 
    id="fechaInicio"
    style="padding:12px;border-radius:14px;border:1px solid #cbd5e1"
  >

  <input 
    type="date" 
    id="fechaFin"
    style="padding:12px;border-radius:14px;border:1px solid #cbd5e1"
  >
</div>


<table>
<tr>
  <th>Proveedor</th>
  <th>RUT</th>
  <th>Factura</th>
  <th>Fecha</th>
  <th>Total</th>
  <th>Imagen</th>
  <th>Detalle</th>
</tr>

<?php
$q=$conn->query("SELECT * FROM facturas_epp ORDER BY fecha DESC");
while($r=$q->fetch_assoc()){
  echo "<tr>
    <td>{$r['proveedor_nombre']}</td>
    <td>{$r['proveedor_rut']}</td>
    <td>{$r['num_factura']}</td>
    <td>".date('d-m-Y',strtotime($r['fecha']))."</td>
    <td><strong>$ ".number_format($r['total'],0,',','.')."</strong></td>
    <td>".($r['imagen']?"<a href='{$r['imagen']}' target='_blank'>📷 Ver</a>":"—")."</td>
    <td><a href='ver_factura_epp.php?id={$r['id']}'>🔍 Ver</a></td>
  </tr>";
}
?>
</table>
</div>

<!-- MODAL -->
<div class="modal" id="modalFactura">
<div class="modal-content">
<h3>➕ Nueva Factura</h3>

<form class="form" method="post" action="guardar_factura_epp.php" enctype="multipart/form-data">

<div class="form-grid">
  <input name="proveedor_rut" id="rut" placeholder="RUT Proveedor" required>
  <input name="proveedor_nombre" id="proveedor" placeholder="Proveedor" required>
  <input name="num_factura" placeholder="N° de Factura" class="full" required>
</div>

<div class="items" id="items">
  <div class="item-row">
    <select name="epp[]" required>
      <option value="">EPP</option>
      <?php
      $e=$conn->query("SELECT epp FROM elementos_epp ORDER BY epp");
      while($r=$e->fetch_assoc()) echo "<option>{$r['epp']}</option>";
      ?>
    </select>
    <input type="number" name="cantidad[]" placeholder="Cantidad" required>
    <input type="number" name="precio[]" placeholder="Precio" required>
    <button type="button" class="remove" onclick="this.parentNode.remove()">✕</button>
  </div>
</div>

<button type="button" class="add" onclick="agregar()">➕ Agregar EPP</button>
<input type="file" name="imagen">

<div class="form-actions">
  <button type="button" class="cancel" onclick="cerrarModal()">Cancelar</button>
  <button class="save">Guardar Factura</button>
</div>

</form>
</div>
</div>

<!-- MODAL DASHBOARD FACTURAS -->
<div class="modal" id="modalDashboard">
  <div class="modal-content" style="width:1000px">

    <h3 style="margin-bottom:20px">📊 Dashboard Facturas EPP</h3>

    <div style="
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
      gap:25px
    ">

      <div class="card">
        <h4>🏢 Facturas por Proveedor</h4>
        <canvas id="graficoProveedores"></canvas>
      </div>

      <div class="card">
        <h4>📄 Cantidad de Facturas</h4>
        <canvas id="graficoCantidad"></canvas>
      </div>

      <div class="card">
        <h4>💰 Total Gastado</h4>
        <canvas id="graficoTotal"></canvas>
      </div>

    </div>

    <div style="text-align:right;margin-top:20px">
      <button class="cancel" onclick="cerrarDashboard()">⬅ Cerrar</button>
    </div>

  </div>
</div>


<footer>
<strong>Transportes Mimbral LTDA</strong><br>
© <?= date('Y') ?>
</footer>

<script>
const modalFactura=document.getElementById('modalFactura');
function abrirModal(){modalFactura.style.display='flex'}
function cerrarModal(){modalFactura.style.display='none'}
function agregar(){
  const row=document.querySelector('.item-row').cloneNode(true);
  row.querySelectorAll('input').forEach(i=>i.value='');
  document.getElementById('items').appendChild(row);
}

/* AUTOCOMPLETAR PROVEEDOR */
const rutInput=document.getElementById('rut');
const proveedorInput=document.getElementById('proveedor');

rutInput.addEventListener('input',()=>{
  clearTimeout(window._rutDelay);
  window._rutDelay=setTimeout(()=>{
    if(rutInput.value.length<7){proveedorInput.value='';proveedorInput.readOnly=false;return;}
    fetch(`buscar_proveedor.php?rut=${encodeURIComponent(rutInput.value)}`)
    .then(r=>r.json())
    .then(d=>{
      if(d?.proveedor_nombre){
        proveedorInput.value=d.proveedor_nombre;
        proveedorInput.readOnly=true;
      }else{
        proveedorInput.value='';
        proveedorInput.readOnly=false;
      }
    });
  },400);
});

/* BUSCADOR AVANZADO TABLA */
const buscador = document.getElementById('buscador');
const fechaInicio = document.getElementById('fechaInicio');
const fechaFin = document.getElementById('fechaFin');

function filtrarTabla(){
  const texto = buscador.value.toLowerCase();
  const fi = fechaInicio.value;
  const ff = fechaFin.value;

  document.querySelectorAll('table tr').forEach((fila, index) => {
    if(index === 0) return;

    const proveedor = fila.children[0].textContent.toLowerCase();
    const rut = fila.children[1].textContent.toLowerCase();
    const factura = fila.children[2].textContent.toLowerCase();

    // convertir fecha dd-mm-yyyy → yyyy-mm-dd
    const fecha = fila.children[3].textContent
      .split('-')
      .reverse()
      .join('-');

    let visible =
      proveedor.includes(texto) ||
      rut.includes(texto) ||
      factura.includes(texto);

    if(fi && fecha < fi) visible = false;
    if(ff && fecha > ff) visible = false;

    fila.style.display = visible ? '' : 'none';
  });
}

buscador.addEventListener('keyup', filtrarTabla);
fechaInicio.addEventListener('change', filtrarTabla);
fechaFin.addEventListener('change', filtrarTabla);

</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chartsFacturas = [];

function abrirDashboard(){
  document.getElementById('modalDashboard').style.display='flex';

  chartsFacturas.forEach(c=>c.destroy());
  chartsFacturas=[];

  const filas = document.querySelectorAll('table tr');

  const proveedores = {};
  let totalGeneral = 0;
  let cantidadFacturas = 0;

  filas.forEach((fila,index)=>{
    if(index===0 || fila.style.display==='none') return;

    const proveedor = fila.children[0].innerText;
    const total = parseInt(
      fila.children[4].innerText
        .replace('$','')
        .replace(/\./g,'')
    );

    proveedores[proveedor] = (proveedores[proveedor] || 0) + total;
    totalGeneral += total;
    cantidadFacturas++;
  });

const coloresBase = [
  '#ef4444', // rojo
  '#22c55e', // verde
  '#3b82f6', // azul
  '#f59e0b', // amarillo
  '#a855f7', // morado
  '#06b6d4', // celeste
  '#ec4899', // rosado
];

const labels = Object.keys(proveedores);
const valores = Object.values(proveedores);

// asignar colores por proveedor
const colores = labels.map((_, i) => coloresBase[i % coloresBase.length]);

/* GRAFICO PROVEEDORES */
chartsFacturas.push(new Chart(graficoProveedores,{
  type:'bar',
  data:{
    labels,
    datasets:[{
      data: valores,
      backgroundColor: colores
    }]
  },
  options:{
    scales:{y:{beginAtZero:true}},
    plugins:{
      legend:{display:false},
      tooltip:{
        callbacks:{
          label:(ctx)=>`$ ${ctx.raw.toLocaleString('es-CL')}`
        }
      }
    }
  }
}));

  /* GRAFICO CANTIDAD */
  chartsFacturas.push(new Chart(graficoCantidad,{
    type:'doughnut',
    data:{
      labels:['Facturas'],
      datasets:[{data:[cantidadFacturas]}]
    }
  }));

  /* GRAFICO TOTAL */
chartsFacturas.push(new Chart(graficoTotal,{
  type:'bar',
  data:{
    labels:['Total General'],
    datasets:[{
      data:[totalGeneral],
      backgroundColor:'#22c55e'
    }]
  },
  options:{
    plugins:{
      legend:{display:false},
      tooltip:{
        callbacks:{
          label:(ctx)=>`$ ${ctx.raw.toLocaleString('es-CL')}`
        }
      }
    },
    scales:{
      y:{
        beginAtZero:true,
        ticks:{
          callback:(v)=>'$ ' + v.toLocaleString('es-CL')
        }
      }
    }
  }
}));

}

function cerrarDashboard(){
  document.getElementById('modalDashboard').style.display='none';
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

</body>
</html>
