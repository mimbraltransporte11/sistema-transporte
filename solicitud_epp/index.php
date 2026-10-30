<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Sistema EPP</title>

<link rel="icon" type="image/png" href="favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
:root{
  --bg:#f5f7fb;
  --card:#ffffff;
  --primary:#0f4c81;
  --primary-dark:#0a365c;
  --success:#22c55e;
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

/* ===== CARD ===== */
.card{
  background:var(--card);
  border-radius:22px;
  padding:30px;
  box-shadow:0 25px 50px rgba(0,0,0,.1);
  width:100%;
  margin:0;
}

/* ===== SECTION TITLE ===== */
.section-title{
  font-size:22px;
  font-weight:700;
  margin-bottom:18px;
  display:flex;
  align-items:center;
  gap:10px;
}

.section-title span{
  width:6px;
  height:28px;
  border-radius:999px;
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
}

/* ===== ACTION BAR ===== */
.actions{
  display:flex;
  justify-content:flex-end;
  margin-bottom:20px;
  gap:10px;
}
.actions a{
  text-decoration:none;
  padding:12px 20px;
  border-radius:14px;
  background:linear-gradient(135deg,#22c55e,#16a34a);
  color:#fff;
  font-weight:700;
}

/* ===== FORM ===== */
.form{
  display:grid;
  grid-template-columns: repeat(2,1fr);
  gap:16px;
  margin-bottom:30px;
}
.full{
  grid-column: span 2;
}

.form input,
.form select{
  padding:14px;
  border-radius:14px;
  border:1px solid #cbd5e1;
  font-size:14px;
}

.form button{
  grid-column:span 3;
  padding:16px;
  border:none;
  border-radius:16px;
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  color:#fff;
  font-weight:700;
  font-size:15px;
  cursor:pointer;
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
  color:#fff;
}

tr:nth-child(even){background:#f8fafc}

.btn-img{
  padding:6px 14px;
  border-radius:999px;
  background:#e0f2fe;
  color:#0369a1;
  border:none;
  font-weight:700;
  cursor:pointer;
}

.btn-delete{
  padding:6px 14px;
  border-radius:999px;
  background:#fee2e2;
  color:#b91c1c;
  font-weight:700;
  text-decoration:none;
  border:none;
  cursor:pointer;
}

/* ALERTAS */
.alert{
  display:flex;
  gap:14px;
  padding:18px 22px;
  border-radius:16px;
  margin-bottom:20px;
  font-weight:600;
}

.alert-error{
  background:linear-gradient(135deg,#fee2e2,#fecaca);
  color:#7f1d1d;
}

.alert-success{
  background:linear-gradient(135deg,#dcfce7,#bbf7d0);
  color:#14532d;
}

/* MODAL */
#modal{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.4);
  display:none;
  align-items:center;
  justify-content:center;
  z-index:999;
}

.modal-content{
  background:white;
  padding:25px;
  border-radius:18px;
  min-width:400px;
  animation:fadeIn .2s ease;
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

.alert{
  display:flex;
  gap:14px;
  padding:18px 22px;
  border-radius:16px;
  margin-bottom:20px;
  font-weight:600;

  opacity:1;
  transition:opacity .5s ease, transform .5s ease;
}

.alert.hide{
  opacity:0;
  transform:translateY(-5px);
}


#modal-confirm{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.4);
  display:none;
  align-items:center;
  justify-content:center;
  z-index:1000;
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

.lista-autocomplete{
  position:absolute;
  top:100%;
  left:0;
  width:100%;
  background:white;
  border-radius:12px;
  box-shadow:0 10px 25px rgba(0,0,0,.15);
  z-index:999;
  display:none;
  max-height:200px;
  overflow-y:auto;
}
.item-autocomplete{
  padding:10px;
  cursor:pointer;
  border-bottom:1px solid #eee;
}
.item-autocomplete:hover{
  background:#f1f5f9;
}

.form input,
.form select{
  width:100%;
}

/* Inputs más alineados */
.form input[type="file"]{
  padding:10px;
  background:#f8fafc;
}

/* EPP items más bonitos */
.epp-item{
  background:#f8fafc;
  padding:10px;
  border-radius:12px;
  border:1px solid #e2e8f0;
}

</style>
</head>

<body>
<?php include '../shared/menu.php'; ?>

<div class="banner-wrap">
  <img src="banner.jpg" class="banner">
  <div class="banner-overlay"></div>

  <div class="banner-text">
    <h1>Sistema de Entrega EPP</h1>
  </div>

  <!-- RELOJ PREMIUM -->
  <div class="banner-datetime">
    <div id="banner-date"></div>
    <div id="banner-time"></div>
  </div>
</div>

<div class="card">

<div class="section-title">
  <span></span>
  📄 Formulario Entrega EPP
</div>

<div class="actions">
  <a href="ficha_epp.php">📦 Ver Ficha EPP</a>
  <a href="facturas_epp.php">📑 Ingresar Factura</a>
  <a href="stock_epp.php">📦 Ver Stock EPP</a>
</div>

<form method="post" action="guardar_solicitud_epp.php" class="form" enctype="multipart/form-data">

<!-- RUT -->
<div style="position:relative;">
  <input type="text" id="rut" name="rut" placeholder="RUT" required autocomplete="off">
  <div id="error-rut"></div>
  <div id="lista-rut" class="lista-autocomplete"></div>
</div>

<!-- NOMBRE -->
<div style="position:relative;">
  <input type="text" id="nombre" name="nombre" placeholder="Nombre trabajador" required autocomplete="off">
  <div id="lista-nombre" class="lista-autocomplete"></div>
</div>

<!-- FECHA -->
<input type="date" name="fecha" required>

<!-- IMAGEN -->
<input type="file" name="imagen" accept="image/*">

<!-- EPP -->
<div id="contenedor-epp" class="full" style="display:flex; flex-direction:column; gap:10px;">
  
  <div class="epp-item" style="display:flex; gap:10px;">
    <select name="epp[]" required>
      <option value="">Seleccione EPP</option>
      <?php
      $e=$conn->query("SELECT epp FROM elementos_epp ORDER BY epp");
      while($r=$e->fetch_assoc()){
        echo "<option>{$r['epp']}</option>";
      }
      ?>
    </select>

    <input type="number" name="cantidad[]" placeholder="Cantidad" required>

    <button type="button" class="btn-delete" onclick="eliminarEPP(this)">✖</button>
  </div>

</div>

<!-- BOTÓN AGREGAR -->
<button type="button" onclick="agregarEPP()" class="btn-img full">
  ➕ Agregar otro EPP
</button>

<!-- BOTÓN SUBMIT -->
<button class="full">Registrar Entrega</button>

</form>

  <?php if(isset($_GET['error']) && $_GET['error']=='stock'): ?>
  <div class="alert alert-error">
    <div class="alert-icon">🚫</div>
  <div>
    <strong>No se puede registrar la solicitud</strong>
    <br>
    El EPP <strong><?= htmlspecialchars($_GET['epp']) ?>
    </strong> no tiene stock disponible.
    </div>
    </div>
  <?php endif; ?>

  <?php if(isset($_GET['ok'])): ?>
  <div class="alert alert-success">
    <div class="alert-icon">✅</div>
    <div>
      Solicitud registrada correctamente.
  </div>
  </div>
  <?php endif; ?>

</form>

<div style="margin-bottom:20px; display:flex; justify-content:flex-end;">
  <input type="text" id="buscador" placeholder="🔍 Buscar..." style="padding:14px;">
</div>

<table id="tabla-epp">
<tr>
  <th>Fecha</th>
  <th>RUT</th>
  <th>Nombre</th>
  <th>Detalle</th>
  <th>Imagen</th>
  <th>Acción</th>
</tr>

<?php
$q=$conn->query("SELECT * FROM solicitud ORDER BY id DESC");
while($r=$q->fetch_assoc()){

  $fechaFormateada = date('d-m-y', strtotime($r['fecha']));

  if($r['imagen']){
    $imgBtn = "<a href='{$r['imagen']}' target='_blank' class='btn-img'>Ver</a>";
  }else{
    $imgBtn = "
      <form action='subir_imagen_epp.php' method='post' enctype='multipart/form-data' style='display:flex; gap:6px; justify-content:center;'>
        <input type='hidden' name='id' value='{$r['id']}'>
        <input type='file' name='imagen' accept='image/*' required style='font-size:12px;'>
        <button type='submit' class='btn-img'>Subir</button>
      </form>
    ";
  }

  echo '<tr>
  <td>'.$fechaFormateada.'</td>
  <td>'.$r['rut'].'</td>
  <td><strong>'.$r['nombre'].'</strong></td>
  <td>
    <button class="btn-img" onclick="verDetalle('.$r['id'].')">
      Ver
    </button>
  </td>
  <td>'.$imgBtn.'</td>
  <td>
    <button class="btn-delete"
      onclick="abrirConfirm('.$r['id'].', \''.$r['nombre'].'\')">
      Eliminar
    </button>
  </td>
</tr>';
}

?>
</table>

</div>

<footer>
<strong>Transportes Mimbral LTDA</strong><br>
Gestión empresarial · © <?= date('Y') ?>
</footer>

<!-- MODAL -->
<div id="modal">
  <div class="modal-content">
    <h3>Detalle Solicitud</h3>
    <div id="detalle-contenido"></div>
    <br>
    <button onclick="cerrarModal()" class="btn-delete">Cerrar</button>
  </div>
</div>


<!-- MODAL CONFIRMAR ELIMINAR -->
<div id="modal-confirm">
  <div class="modal-content">
    <h3>⚠ Confirmar eliminación</h3>

    <div id="confirm-text" style="margin:15px 0; font-size:15px;"></div>

    <div style="display:flex; gap:10px; justify-content:flex-end;">
      <button onclick="cerrarConfirm()" class="btn-img">Cancelar</button>
      <button onclick="eliminarConfirmado()" class="btn-delete">Eliminar</button>
    </div>
  </div>
</div>

<script>
function agregarEPP(){
  const contenedor = document.getElementById('contenedor-epp');
  const item = document.querySelector('.epp-item').cloneNode(true);

  item.querySelector('select').value='';
  item.querySelector('input').value='';

  contenedor.appendChild(item);
}

function eliminarEPP(btn){
  const items=document.querySelectorAll('.epp-item');
  if(items.length>1){
    btn.parentElement.remove();
  }
}

function verDetalle(id){
  fetch('detalle_solicitud_epp.php?id=' + id)
    .then(r => r.text())
    .then(html => {
      document.getElementById('detalle-contenido').innerHTML = html;
      document.getElementById('modal').style.display='flex';
    });
}

function cerrarModal(){
  document.getElementById('modal').style.display='none';
}

document.getElementById('buscador').addEventListener('keyup', function(){
  const filtro=this.value.toLowerCase();
  const filas=document.querySelectorAll('#tabla-epp tr:not(:first-child)');

  filas.forEach(fila=>{
    const rut=fila.children[1].textContent.toLowerCase();
    const nombre=fila.children[2].textContent.toLowerCase();
    fila.style.display=(rut.includes(filtro)||nombre.includes(filtro))?'':'none';
  });
});

/* ===== AUTO OCULTAR ALERTAS ===== */

const alerta = document.querySelector('.alert');

if(alerta){
  setTimeout(() => {
    alerta.classList.add('hide');

    setTimeout(() => {
      alerta.remove();
    }, 500); // espera la animación
  }, 3000); // 3 segundos
}


let solicitudAEliminar = null;

function abrirConfirm(id, nombre){

  solicitudAEliminar = id;

  document.getElementById('confirm-text').innerHTML =
    `¿Estás seguro de eliminar la solicitud de EPP del trabajador <strong>${nombre}</strong>?`;

  document.getElementById('modal-confirm').style.display = 'flex';
}

function cerrarConfirm(){
  document.getElementById('modal-confirm').style.display = 'none';
  solicitudAEliminar = null;
}

function eliminarConfirmado(){
  if(solicitudAEliminar){
    window.location.href = 'eliminar_solicitud_epp.php?id=' + solicitudAEliminar;
  }
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


const inputRut = document.getElementById('rut');
const inputNombre = document.getElementById('nombre');

const listaRut = document.getElementById('lista-rut');
const listaNombre = document.getElementById('lista-nombre');

/* AUTOCOMPLETE */
function buscar(valor, listaDestino){

  if(valor.length < 3){
    listaDestino.style.display = 'none';
    return;
  }

  fetch('buscar_trabajador.php?q=' + valor)
    .then(res => res.json())
    .then(data => {

      listaDestino.innerHTML = '';

      if(data.length === 0){
        listaDestino.style.display = 'none';
        return;
      }

      data.forEach(item => {

        const div = document.createElement('div');
        div.className = 'item-autocomplete';

        div.innerHTML = `
          <strong>${item.rut}</strong><br>
          <small>${item.nombre}</small>
        `;

     div.addEventListener('click', () => {

  inputRut.value = formatearRut(item.rut);
  inputNombre.value = item.nombre;

  limpiarErrorRut(); // 🔥 importante

  listaRut.style.display = 'none';
  listaNombre.style.display = 'none';
});

        listaDestino.appendChild(div);
      });

      listaDestino.style.display = 'block';

    });
}

inputRut.addEventListener('keyup', () => buscar(inputRut.value, listaRut));
inputNombre.addEventListener('keyup', () => buscar(inputNombre.value, listaNombre));

document.addEventListener('click', function(e){
  if(!inputRut.contains(e.target)) listaRut.style.display = 'none';
  if(!inputNombre.contains(e.target)) listaNombre.style.display = 'none';
});

/* ===== VALIDACIÓN RUT (SIN PUNTOS) ===== */

function calcularDV(rut){
  let suma = 0;
  let multiplo = 2;

  for(let i = rut.length - 1; i >= 0; i--){
    suma += parseInt(rut[i]) * multiplo;
    multiplo = multiplo < 7 ? multiplo + 1 : 2;
  }

  let dv = 11 - (suma % 11);

  if(dv === 11) return '0';
  if(dv === 10) return 'K';
  return dv.toString();
}

function validarRutFormato(rut){
  return /^[0-9]{7,8}-[0-9Kk]{1}$/.test(rut);
}

function validarRutCompleto(rut){
  if(!validarRutFormato(rut)) return false;

  const [cuerpo, dv] = rut.split('-');
  return calcularDV(cuerpo) === dv.toUpperCase();
}



/* VALIDAR AL ENVIAR */
document.querySelector('form').addEventListener('submit', function(e){

  const rut = inputRut.value.trim().toUpperCase();

  if(!validarRutFormato(rut)){
    e.preventDefault();
    mostrarErrorRut("⚠ Formato inválido. Usa: 12345678-9");
    inputRut.focus();
    return;
  }

  if(!validarRutCompleto(rut)){
    e.preventDefault();
    mostrarErrorRut("❌ RUT inválido");
    inputRut.focus();
    return;
  }

  limpiarErrorRut();
});

/* ERRORES */
function mostrarErrorRut(msg){
  const error = document.getElementById('error-rut');
  error.innerText = msg;
  error.style.display = 'block';
  inputRut.style.border = '2px solid #ef4444';
}

function limpiarErrorRut(){
  const error = document.getElementById('error-rut');
  error.style.display = 'none';
  inputRut.style.border = '1px solid #cbd5e1';
}


function formatearRut(rut){
  rut = rut.replace(/[^0-9kK]/g, '').toUpperCase();

  if(rut.length < 2) return rut;

  let cuerpo = rut.slice(0, -1);
  let dv = rut.slice(-1);

  return cuerpo + '-' + dv;
}

</script>


</body>
</html>
