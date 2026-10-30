<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Sistema Patio</title>

<link rel="icon" type="image/png" href="favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root{
  --bg:#f5f7fb;
  --card:#ffffff;
  --primary:#0f4c81;
  --primary-dark:#0a365c;
  --accent:#38bdf8;
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
  font-size:30px;
  font-weight:700;
  margin-bottom:25px;
}

/* TOP BAR */
.top-bar{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:20px;
  gap:15px;
}
.top-bar input{
  padding:12px 16px;
  width:280px;
  border-radius:14px;
  border:1px solid #cbd5e1;
  font-size:14px;
}

/* BUTTONS */
button{
  padding:12px 18px;
  border:none;
  border-radius:14px;
  cursor:pointer;
  font-weight:600;
  font-size:14px;
  transition:.2s ease;
}
.btn-primary{
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  color:#fff;
}
.btn-primary:hover{opacity:.9}
.btn-secondary{
  background:#475569;
  color:#fff;
}
.btn-secondary:hover{opacity:.9}

/* TABLE */
table{
  width:100%;
  border-collapse:collapse;
  background:var(--card);
  border-radius:18px;
  overflow:hidden;
  box-shadow:0 20px 40px rgba(255, 255, 255, 0.08);
}
th,td{
  padding:16px;
  text-align:center;
  font-size:14px;
}
th{
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  color:#fff;
  font-weight:600;
}
tr{
  transition:.2s ease;
}
tr:hover{
  background:#f1f5f9;
}
tr.entrada{
  background:#ecfdf5;
  cursor:pointer;
}
tr.salida{
  background:#fef2f2;
  cursor:pointer;
}

/* BADGES */
.badge{
  padding:6px 14px;
  border-radius:999px;
  font-size:12px;
  font-weight:700;
}

.badge.entrada{
  background:#dcfce7;
  color:#166534;
}

.badge.salida{
  background:#fee2e2;
  color:#991b1b;
}

/* MODAL BASE */
.modal{
  display:none;
  position:fixed;
  inset:0;
  background:rgba(15,23,42,.75);
  backdrop-filter:blur(5px);
  z-index:10;
}
.modal-content{
  background:#fff;
  width:440px;
  margin:5% auto;
  padding:30px;
  border-radius:22px;
  box-shadow:0 30px 60px rgba(0,0,0,.3);
  animation:fadeUp .3s ease;

  max-height: 90vh;   /* ✅ FIX CLAVE */
  overflow-y: auto;   /* ✅ FIX CLAVE */

}
@keyframes fadeUp{
  from{transform:translateY(20px);opacity:0}
  to{transform:translateY(0);opacity:1}
}
.modal-content h3{
  text-align:center;
  margin-bottom:20px;
  font-size:22px;
}

/* FORM */
.modal-content input,
.modal-content select,
.modal-content textarea{
  width:100%;
  padding:12px 16px;
  margin-bottom:14px;
  border-radius:14px;
  border:1px solid #cbd5e1;
  font-size:14px;
}
.modal-content textarea{
  resize:none;
  min-height:90px;
}

/* ===== RESUMEN ===== */
.resumen-header{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:15px;
}
.resumen-header span{
  font-size:22px;
  font-weight:700;
}
.resumen-body{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:20px;
}
.resumen-grafico{
  background:#f8fafc;
  padding:20px;
  border-radius:18px;
}
.resumen-tabla{
  max-height:260px;
  overflow-y:auto;
  border-radius:18px;
  border:1px solid #e5e7eb;
}
.resumen-tabla table{
  box-shadow:none;
}
.resumen-tabla th{
  background:#f1f5f9;
  color:#0f172a;
}
.badge-resumen{
  padding:6px 12px;
  border-radius:999px;
  font-weight:700;
  font-size:12px;
  color:#fff;
}
.badge-resumen.entrada{background:#22c55e}
.badge-resumen.salida{background:#ef4444}

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
    <h1>Control de inventario</h1>
    <p>Gestión de entradas y salidas</p>
  </div>

  <div class="banner-datetime">
    <div id="banner-date"></div>
    <div id="banner-time"></div>
  </div>
</div>


<div class="top-bar">
  <div>
    <button class="btn-primary" onclick="openModal()">➕ Ingresar Solicitud</button>
    <a href="inventario.php"><button class="btn-secondary">📦 Inventario</button></a>
  </div>
  <input type="text" id="buscar" placeholder="🔍 Buscar por nombre...">
</div>

<table id="tabla">
<tr>
  <th>Fecha</th>
  <th>Nombre</th>
  <th>Área</th>
  <th>Acción</th>
  <th>Detalle</th>
  <th>Observación</th>
  <th>Modificar</th>
</tr>

<?php
$q=$conn->query("SELECT * FROM solicitud ORDER BY id DESC");
while($r=$q->fetch_assoc()){
  $accionClase = $r['accion'];
  $nombre = htmlspecialchars($r['nombre']);
  echo "
<tr class='{$accionClase}' onclick=\"abrirResumen('{$nombre}')\">
  <td>{$r['fecha']}</td>
  <td><strong>{$nombre}</strong></td>
  <td>{$r['area']}</td>
  <td><span class='badge {$accionClase}'>{$r['accion']}</span></td>

  <td>
    <button class='btn-primary'
      onclick=\"event.stopPropagation(); verDetalle({$r['id']})\">
      👁 Ver
    </button>
  </td>

  <td>{$r['observacion']}</td>

  <td>
    <button class='btn-secondary'
      onclick=\"event.stopPropagation(); eliminarSolicitud({$r['id']})\">
      🗑 Eliminar
    </button>
  </td>
</tr>";
}
?>
</table>

<!-- ✅ MODAL NUEVA SOLICITUD (MEJORADO) -->
<div class="modal" id="modal">
  <div class="modal-content">
    <h3>📄 Nueva Solicitud</h3>

    <form method="post" action="guardar_solicitud.php">

  <input type="date" name="fecha" required>
  <input type="text" name="nombre" placeholder="Nombre del solicitante" required>
  <input type="text" name="area" placeholder="Área" required>

  <select name="accion" id="accion" required>
  <option value="" disabled selected>Acción</option>
  <option value="entrada">Entrada</option>
  <option value="salida">Salida</option>
</select>

  <!-- ✅ CONTENEDOR SOLO PARA ITEMS -->
  <div id="items">

    <div class="item-row">

      <select name="equipo[]" required onchange="actualizarStock(this)">
        <option value="" disabled selected>Seleccione equipo</option>
        <?php
        $e=$conn->query("SELECT equipo FROM inventario");
        while($eq=$e->fetch_assoc()){
          echo "<option>{$eq['equipo']}</option>";
        }
        ?>
      </select>

      <input type="number" name="cantidad[]" placeholder="Cantidad" required>

      <div class="stock-info"></div>

    </div>

  </div>

  <!-- ✅ BOTÓN FUERA DEL CONTENEDOR -->
  <button type="button"
          class="btn-secondary"
          onclick="agregarItem()"
          style="width:100%">
    ➕ Agregar otro equipo
  </button>

  <textarea name="observacion" placeholder="Observación"></textarea>

  <button class="btn-primary" style="width:100%">Guardar</button>

  <br><br>

  <button type="button"
          class="btn-secondary"
          style="width:100%"
          onclick="closeModal()">
    Cancelar
  </button>

</form>

  </div>
</div>

<!-- MODAL RESUMEN -->
<div class="modal" id="modalResumen">
  <div class="modal-content" style="width:850px">
    <div class="resumen-header">
      <span id="nombreResumen"></span>
      <div style="display:flex; gap:10px;">
        <input type="date" id="fechaDesde" onchange="cargarResumen()">
        <input type="date" id="fechaHasta" onchange="cargarResumen()">
      </div>
    </div>

    <div class="resumen-body">
      <div class="resumen-grafico">
        <canvas id="graficoResumen" height="180"></canvas>
      </div>
      <div class="resumen-tabla">
        <table>
          <thead>
            <tr>
              <th>Acción</th>
              <th>Equipo</th>
              <th>Cantidad</th>
            </tr>
          </thead>
          <tbody id="tablaResumen"></tbody>
        </table>
      </div>
    </div>

    <br>
    <button class="btn-secondary" style="width:100%" onclick="cerrarResumen()">⬅ Volver atrás</button>
  </div>
</div>

<div class="modal" id="modalDetalle">
  <div class="modal-content">
    <h3>Detalle solicitud</h3>
    <div id="contenidoDetalle"></div>

    <br>
    <button class="btn-secondary" style="width:100%" onclick="cerrarDetalle()">
      Cerrar
    </button>
  </div>
</div>


<footer>
<strong>Transportes Mimbral LTDA</strong><br>
Gestión empresarial · © <?= date('Y') ?>
</footer>

<script>
function openModal(){
  document.getElementById('modal').style.display='block';
}
function closeModal(){
  document.getElementById('modal').style.display='none';
}

document.getElementById('buscar').addEventListener('keyup', function(){
  let f=this.value.toLowerCase();
  document.querySelectorAll('#tabla tr').forEach((tr,i)=>{
    if(i===0)return;
    tr.style.display=tr.children[1].innerText.toLowerCase().includes(f)?'':'none';
  });
});

let nombreActual='';
let grafico;

function abrirResumen(nombre){
  nombreActual=nombre;
  document.getElementById('nombreResumen').innerText=nombre;
  document.getElementById('modalResumen').style.display='block';
  cargarResumen();
}
function cerrarResumen(){
  document.getElementById('modalResumen').style.display='none';
}

function cargarResumen(){
  let desde=document.getElementById('fechaDesde').value;
  let hasta=document.getElementById('fechaHasta').value;

  fetch(`resumen.php?nombre=${nombreActual}&desde=${desde}&hasta=${hasta}`)
    .then(r=>r.json())
    .then(data=>{
      if(grafico) grafico.destroy();

      grafico=new Chart(document.getElementById('graficoResumen'),{
        type:'doughnut',
        data:{
          labels:['Entradas','Salidas'],
          datasets:[{
            data:[data.general.entradas||0,data.general.salidas||0],
            backgroundColor:['#22c55e','#ef4444']
          }]
        },
        options:{plugins:{legend:{position:'bottom'}}}
      });

      let html='';
      data.historial.forEach(r=>{
        html+=`
        <tr>
          <td><span class="badge-resumen ${r.accion}">${r.accion}</span></td>
          <td>${r.equipo}</td>
          <td>${r.cantidad}</td>
        </tr>`;
      });
      document.getElementById('tablaResumen').innerHTML=html;
    });
}

function eliminarSolicitud(id){
  if(!confirm('¿Eliminar esta solicitud?')) return;

  fetch('eliminar_solicitud.php',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'id='+id
  })
  .then(r=>r.text())
  .then(resp=>{
    if(resp==='ok') location.reload();
    else alert('Error al eliminar');
  });
}

function agregarItem(){

  let container = document.getElementById('items');

  let firstRow = document.querySelector('.item-row');
  let newRow = firstRow.cloneNode(true);

  newRow.querySelector('input').value = '';
  newRow.querySelector('select').selectedIndex = 0;
  newRow.querySelector('.stock-info').innerHTML = '';

  let removeBtn = document.createElement('button');
  removeBtn.type = 'button';
  removeBtn.innerText = '❌ Quitar';
  removeBtn.style = `
      margin-top:6px;
      width:100%;
      background:#ef4444;
      color:white;
      border:none;
      padding:8px;
      border-radius:10px;
      cursor:pointer;
  `;

  removeBtn.onclick = function(){
      newRow.remove();
  };

  newRow.appendChild(removeBtn);

  container.appendChild(newRow);
}



function actualizarStock(selectElement){

  let equipo = selectElement.value;
  let row = selectElement.parentElement;
  let accion = document.getElementById('accion').value;   // ✅ CLAVE

  if(!equipo) return;

  fetch(`obtener_stock.php?equipo=${equipo}`)
    .then(r => r.json())
    .then(data => {

        let stockDiv = row.querySelector('.stock-info');
        let inputCantidad = row.querySelector('input');

        stockDiv.innerHTML = `Stock disponible: <strong>${data.stock}</strong>`;
        stockDiv.style.color = '#64748b';

        /* ✅ SOLO BLOQUEAR EN SALIDA */
        if(accion === 'salida'){

            inputCantidad.max = data.stock;

            inputCantidad.oninput = function(){

                if(parseInt(this.value) > data.stock){

                    this.value = data.stock;

                    stockDiv.innerHTML = `⚠ Stock máximo: <strong>${data.stock}</strong>`;
                    stockDiv.style.color = '#ef4444';

                } else {

                    stockDiv.innerHTML = `Stock disponible: <strong>${data.stock}</strong>`;
                    stockDiv.style.color = '#64748b';
                }
            };

        } else {

            /* ✅ ENTRADA → SIN LÍMITE */
            inputCantidad.removeAttribute('max');

            inputCantidad.oninput = null;

            stockDiv.innerHTML = `Ingreso de stock`;
            stockDiv.style.color = '#22c55e';
        }
    });
}


document.getElementById('accion').addEventListener('change', () => {

  document.querySelectorAll('.item-row').forEach(row => {

      let input = row.querySelector('input');
      let stockDiv = row.querySelector('.stock-info');

      input.removeAttribute('max');
      input.oninput = null;

      if(document.getElementById('accion').value === 'entrada'){
          stockDiv.innerHTML = 'Ingreso de stock';
          stockDiv.style.color = '#22c55e';
      } else {
          stockDiv.innerHTML = '';
      }
  });
});


function verDetalle(id){

  fetch('detalle_solicitud.php?id=' + id)
    .then(r => r.text())
    .then(html => {
        document.getElementById('contenidoDetalle').innerHTML = html;
        document.getElementById('modalDetalle').style.display = 'block';
    });
}

function cerrarDetalle(){
  document.getElementById('modalDetalle').style.display = 'none';
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
