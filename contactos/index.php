<?php
include 'db.php';

date_default_timezone_set('America/Santiago');

/* ✅ FUNCIONES NUEVAS */

function diasHabilesRestantesDesdeInicio($fechaInicio, $totalDias){

  if(!$fechaInicio || !$totalDias) return 0;

  $hoy = strtotime(date('Y-m-d'));
  $inicio = strtotime($fechaInicio);

  $usados = 0;
  $cursor = $inicio;

  while($cursor <= $hoy){

    $diaSemana = date('N', $cursor);

    if($diaSemana < 6){
      $usados++;
    }

    $cursor = strtotime("+1 day", $cursor);
  }

  $restantes = $totalDias - $usados;

  return max(0, $restantes);
}



function diasCorridosRestantes($fechaInicio, $totalDias){

  if(!$fechaInicio || !$totalDias) return 0;

  $hoy = strtotime(date('Y-m-d'));
  $inicio = strtotime($fechaInicio);

  if($hoy < $inicio) return $totalDias;

  /* ✅ +1 para contar el día de inicio */
  $diasUsados = floor(($hoy - $inicio) / 86400) + 1;

  $restantes = $totalDias - $diasUsados;

  return max(0, $restantes);
}



/* ✅ CAMBIO AUTOMÁTICO DE ESTADO */

$resultadoEstados = $conn->query("SELECT id, estado, fin_vacaciones, fin_licencia FROM informacion");

while($rowEstado = $resultadoEstados->fetch_assoc()){

  $idEstado = $rowEstado['id'];

  if($rowEstado['estado'] == 'Vacaciones' && $rowEstado['fin_vacaciones']){
    if(date('Y-m-d') > $rowEstado['fin_vacaciones']){
      $conn->query("UPDATE informacion SET estado='Disponible',
      detalle_vacaciones=NULL, inicio_vacaciones=NULL, fin_vacaciones=NULL
      WHERE id=$idEstado");
    }
  }

  if($rowEstado['estado'] == 'Licencia' && $rowEstado['fin_licencia']){
    if(date('Y-m-d') > $rowEstado['fin_licencia']){
      $conn->query("UPDATE informacion SET estado='Disponible',
      detalle_licencia=NULL, inicio_licencia=NULL, fin_licencia=NULL
      WHERE id=$idEstado");
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Sistema de Contactos</title>

<link rel="icon" type="image/png" href="favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root{
  --primary:#2563eb;
  --primary-dark:#1e40af;
  --danger:#dc2626;
  --success:#16a34a;
  --bg1:#f1f5f9;
  --bg2:#e0e7ff;
  --card:#ffffff;
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

.banner-wrap{position:relative;margin-bottom:35px}
.banner{width:100%;max-height:220px;object-fit:cover;border-radius:20px}
.banner-overlay{position:absolute;inset:0;background:linear-gradient(90deg,rgba(15,23,42,.75),transparent);border-radius:20px}
.banner-text{position:absolute;bottom:20px;left:30px;color:white}

.card{
  background:var(--card);
  padding:25px;
  border-radius:18px;
  box-shadow:0 10px 30px rgba(0,0,0,.08);
  margin:0 auto 35px;
  max-width:1400px;
}

table{width:100%;border-collapse:separate;border-spacing:0}
th,td{padding:14px;border-bottom:1px solid #e5e7eb;text-align:center}
th{font-size:12px;color:var(--muted);text-transform:uppercase}
tbody tr:hover{background:#f8fafc}

.btn{
  background:linear-gradient(135deg,var(--primary),var(--primary-dark));
  color:white;
  border:none;
  padding:10px 14px;
  border-radius:12px;
  cursor:pointer;
  font-weight:600;
  font-size:13px;
}

.btn-danger{
  background:linear-gradient(135deg,#dc2626,#b91c1c);
}

.btn-chat{
  background:linear-gradient(135deg,#16a34a,#15803d);
}

.actions{
  display:flex;
  gap:8px;
  justify-content:center;
}

input,select{
  width:100%;
  padding:10px;
  margin-bottom:12px;
  border-radius:10px;
  border:1px solid #e5e7eb;
}

input[readonly]{
  background:#f1f5f9;
  cursor:not-allowed;
  color:#475569;
  border:1px solid #e2e8f0;
}

.search{
  max-width:320px;
  margin-bottom:15px;
}

/* MODAL */

.modal{
  display:none;
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.4);
  animation:fade .2s ease;
}

.modal-content{
  background:#fff;
  width:420px;
  margin:10% auto;
  padding:25px;
  border-radius:18px;
  animation:pop .2s ease;
}

@keyframes fade{from{opacity:0}to{opacity:1}}
@keyframes pop{from{transform:scale(.9)}to{transform:scale(1)}}

footer{
  margin-top:60px;
  padding:30px;
  border-radius:20px;
  background:linear-gradient(135deg,#1e3a8a,#2563eb);
  color:white;
  text-align:center;
}

.estado {
  font-weight: 600;
  padding: 6px 10px;
  border-radius: 10px;
  font-size: 13px;
  display: inline-block;
  min-width: 100px;
}

.estado-disponible{
background:#dbeafe;
color:#1e40af;
}

.estado-licencia{
  background:#fee2e2;
  color:#991b1b;
}

.estado-vacaciones{
  background:#fef9c3;
  color:#854d0e;
}

.confirmar h3{
  margin-top:0;
  color:#dc2626;
}

.confirmar p{
  color:#334155;
  margin:15px 0 25px;
}

.acciones-confirmar{
  display:flex;
  gap:10px;
  justify-content:flex-end;
}

.dias-restantes{
    margin-top:6px;
    font-size:13px;
    font-weight:600;
    padding:4px 8px;
    border-radius:8px;
    display:inline-block;
}

.vacaciones{
    background:#dbeafe;
    color:#1e40af;
}

.licencia{
    background:#fee2e2;
    color:#991b1b;
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

<div class="banner-wrap">
  <img src="banner.jpg" class="banner">
  <div class="banner-overlay"></div>

  <div class="banner-text">
    <h1>Agenda de Contactos</h1>
    <p>Comunicación directa</p>
  </div>

  <div class="banner-datetime">
    <div id="banner-date"></div>
    <div id="banner-time"></div>
  </div>
</div>

<div class="card">

  <input type="text" id="buscar" class="search" placeholder="Buscar contacto...">

  <button class="btn" onclick="abrirModal()">Agregar Contacto</button>

  <table id="tabla">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Área</th>
        <th>Teléfono</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>

    <tbody>

    <?php
    $sql = "SELECT * FROM informacion ORDER BY nombre ASC";
    $result = $conn->query($sql);

    while($row = $result->fetch_assoc()):
    ?>

      <tr>
        <td><?= $row['nombre'] ?></td>
        <td><?= $row['area'] ?></td>
        <td><?= $row['telefono'] ?></td>

        <td>
          <?php
            $claseEstado = '';
            if($row['estado'] == 'Disponible') $claseEstado = 'estado-disponible';
            if($row['estado'] == 'Licencia') $claseEstado = 'estado-licencia';
            if($row['estado'] == 'Vacaciones') $claseEstado = 'estado-vacaciones';
          ?>
    <span class="estado <?= $claseEstado ?>">
  <?= $row['estado'] ?>

  <?php if($row['estado'] == 'Vacaciones'): 
    $restantes = diasHabilesRestantesDesdeInicio(
    $row['inicio_vacaciones'],
    $row['detalle_vacaciones']
);

  ?>
    <br><small><?= $row['detalle_vacaciones'] ?> días</small>
    <br><small>Restan: <?= $restantes ?> días</small>
  <?php endif; ?>

 <?php if($row['estado'] == 'Licencia'): 
  $restantes = diasCorridosRestantes(
    $row['inicio_licencia'],
    $row['detalle_licencia']
);

?>

    <br><small><?= $row['detalle_licencia'] ?> días</small>
    <br><small>Restan: <?= $restantes ?> días</small>
  <?php endif; ?>
</span>
        </td>

        <td>
          <div class="actions">

<button class="btn btn-chat" 
  onclick="confirmarChat(
    '<?= $row['telefono'] ?>',
    '<?= $row['nombre'] ?>',
    '<?= $row['estado'] ?>',

    '<?= $row['detalle_vacaciones'] ?>',
    '<?= $row['inicio_vacaciones'] ?>',
    '<?= $row['fin_vacaciones'] ?>',

    '<?= $row['detalle_licencia'] ?>',
    '<?= $row['inicio_licencia'] ?>',
    '<?= $row['fin_licencia'] ?>'
  )">
  Chatear
</button>


            <button class="btn" onclick='editar(<?= json_encode($row) ?>)'>
              Editar
            </button>

            <button class="btn btn-danger" 
                    onclick="eliminar(<?= $row['id'] ?>, '<?= $row['nombre'] ?>')">
              Eliminar
            </button>

          </div>
        </td>
      </tr>

    <?php endwhile; ?>

    </tbody>
  </table>
</div>

<!-- MODAL FORM -->
<div class="modal" id="modal">
  <div class="modal-content">
    <h3 id="tituloModal">Agregar Contacto</h3>

    <form method="post" action="guardar_contacto.php">

      <input type="hidden" name="id" id="id">
      <input type="text" name="nombre" id="nombre" placeholder="Nombre" required>

      <select name="area" id="area" required>
        <option value="">Seleccione Área</option>
        <option value="Rampla">Rampla</option>
        <option value="Carro">Carro</option>
        <option value="Cortina">Cortina</option>
        <option value="Porteador">Porteador</option>
      </select>

      <select name="estado" id="estado" required>
        <option value="Disponible">Disponible</option>
        <option value="Licencia">Licencia</option>
        <option value="Vacaciones">Vacaciones</option>
      </select>

      <input type="number" 
       name="detalle_vacaciones" 
       id="detalle_vacaciones"
       placeholder="Días de vacaciones"
       style="display:none;">
       
       <input type="date" 
        name="inicio_vacaciones" 
        id="inicio_vacaciones"
        style="display:none;">

       <input type="number" 
       name="detalle_licencia" 
       id="detalle_licencia"
       placeholder="Días de licencia"
       style="display:none;">

       <input type="date" 
        name="inicio_licencia" 
        id="inicio_licencia"
        style="display:none;">


      <input type="text" name="telefono" id="telefono" placeholder="569XXXXXXXX" required>

      <button class="btn">Guardar</button>
      <button type="button" class="btn btn-danger" onclick="cerrar()">Cancelar</button>

    </form>
  </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal" id="modalConfirmar">
  <div class="modal-content confirmar">
    <h3>⚠️ Confirmar Eliminación</h3>
    <p id="textoConfirmacion"></p>

    <div class="acciones-confirmar">
      <button class="btn btn-danger" id="btnConfirmarEliminar">
        Sí, eliminar
      </button>

      <button class="btn" onclick="cerrarConfirmacion()">
        Cancelar
      </button>
    </div>
  </div>
</div>

<!-- ✅ MODAL WHATSAPP -->
<div class="modal" id="modalChat">
  <div class="modal-content confirmar">
    <h3>💬 Abrir WhatsApp</h3>
    <p id="textoChat"></p>

    <div class="acciones-confirmar">
      <button class="btn btn-chat" id="btnAbrirChat">
        Sí, continuar
      </button>

      <button class="btn" onclick="cerrarChat()">
        Cancelar
      </button>
    </div>
  </div>
</div>

<footer>
<strong>Transportes Mimbral LTDA</strong><br>
© <?= date('Y') ?>
</footer>

<script>

/* ===== BUSCADOR ===== */

buscar.addEventListener('keyup', function(){
  let filtro = this.value.toLowerCase();
  let filas = document.querySelectorAll('#tabla tbody tr');

  filas.forEach(fila=>{
    fila.style.display = fila.textContent.toLowerCase().includes(filtro)
      ? ''
      : 'none';
  });
});

/* ===== MODAL FORM ===== */

function abrirModal(){
  limpiar();
  tituloModal.textContent='Agregar Contacto';

  nombre.readOnly = false;   // ✅ editable

  modal.style.display='block';
}

function cerrar(){
  modal.style.display='none';
}

function limpiar(){
  id.value='';
  nombre.value='';
  area.value='';
  telefono.value='';
  estado.value='';

  nombre.readOnly = false;   // ✅ vuelve editable
}
/* ===== EDITAR ===== */

function editar(data){
  tituloModal.textContent='Editar Contacto';
  modal.style.display='block';

  id.value = data.id;
  nombre.value = data.nombre;
  area.value = data.area;
  telefono.value = data.telefono;
  estado.value = data.estado;

  nombre.readOnly = true;   // 🔒 bloqueado
}


/* ===== ELIMINAR ===== */

let idEliminar = null;

function eliminar(id, nombre){
  idEliminar = id;

  textoConfirmacion.textContent = 
    "¿Seguro que deseas eliminar el contacto de " + nombre + "?";

  modalConfirmar.style.display = 'block';
}

function cerrarConfirmacion(){
  modalConfirmar.style.display = 'none';
  idEliminar = null;
}

btnConfirmarEliminar.addEventListener('click', function(){
  if(idEliminar){
    window.location = 'eliminar_contacto.php?id=' + idEliminar;
  }
});



function formatearFecha(fecha){

  if(!fecha) return '';

  let partes = fecha.split('-'); // YYYY-MM-DD

  return partes[2] + '-' + partes[1] + '-' + partes[0].slice(2);
}


/* ===== ✅ CONFIRMAR CHAT ===== */

function confirmarChat(
  tel, nombre, estado,
  diasVac, inicioVac, finVac,
  diasLic, inicioLic, finLic
){

  telefonoChat = tel;

  let mensaje = "";

  if(estado === "Disponible"){

    mensaje = "¿Deseas abrir WhatsApp para chatear con " + nombre + "?";
  } 

  else if(estado === "Vacaciones"){

   mensaje = `
<div class="chat-alert">
    <div class="chat-alert-title">🌴 ${nombre} Está con VACACIONES</div>

    <div class="chat-alert-grid">
        <div><span>Duración</span><b>: ${diasVac} días hábiles</b></div>
        <div><span>Inicio</span><b>: ${formatearFecha(inicioVac)}</b></div>
        <div><span>Retorno</span><b>: ${formatearFecha(finVac)}</b></div>
    </div>

    <div class="chat-alert-footer">
        ¿Deseas abrir WhatsApp para chatear con ${nombre}?

    </div>
</div>`;

  } 

  else if(estado === "Licencia"){

   mensaje = `
<div class="chat-alert">
    <div class="chat-alert-title">⛔ ${nombre} Está con LICENCIA</div>

    <div class="chat-alert-grid">
        <div><span>Duración</span><b>: ${diasLic} días</b></div>
        <div><span>Inicio</span><b>: ${formatearFecha(inicioLic)}</b></div>
        <div><span>Retorno</span><b>: ${formatearFecha(finLic)}</b></div>
    </div>

    <div class="chat-alert-footer">
         ¿Deseas abrir WhatsApp para chatear con ${nombre}?
    </div>
</div>`;

  }

  textoChat.innerHTML = mensaje;
  modalChat.style.display = 'block';
}



function cerrarChat(){
  modalChat.style.display = 'none';
  telefonoChat = null;
}

btnAbrirChat.addEventListener('click', function(){
  if(telefonoChat){
    window.open('https://wa.me/' + telefonoChat, '_blank');
    cerrarChat();
  }
});

/* ===== VALIDACIÓN TELÉFONO ===== */

telefono.addEventListener('input', function(){
  this.value = this.value.replace(/[^0-9]/g,'');
});


/* VACACIONES DINÁMICAS */
estado.addEventListener('change', function(){

  /* VACACIONES */
 if(this.value === "Vacaciones"){

    detalle_vacaciones.style.display = 'block';
    detalle_vacaciones.required = true;

    inicio_vacaciones.style.display = 'block';
    inicio_vacaciones.required = true;

    detalle_licencia.style.display = 'none';
    detalle_licencia.required = false;
    detalle_licencia.value = '';

    inicio_licencia.style.display = 'none';
    inicio_licencia.required = false;
    inicio_licencia.value = '';
}


  /* LICENCIA */
 else if(this.value === "Licencia"){

    detalle_licencia.style.display = 'block';
    detalle_licencia.required = true;

    inicio_licencia.style.display = 'block';
    inicio_licencia.required = true;

    detalle_vacaciones.style.display = 'none';
    detalle_vacaciones.required = false;
    detalle_vacaciones.value = '';

    inicio_vacaciones.style.display = 'none';
    inicio_vacaciones.required = false;
    inicio_vacaciones.value = '';
}


  /* DISPONIBLE */
  else{

    detalle_vacaciones.style.display = 'none';
    detalle_vacaciones.required = false;
    detalle_vacaciones.value = '';

    inicio_vacaciones.style.display = 'none';
    inicio_vacaciones.required = false;
    inicio_vacaciones.value = '';

    detalle_licencia.style.display = 'none';
    detalle_licencia.required = false;
    detalle_licencia.value = '';

    inicio_licencia.style.display = 'none';
    inicio_licencia.required = false;
    inicio_licencia.value = '';
}

});



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
