  <?php include 'db.php'; ?>
  <!DOCTYPE html>
  <html lang="es">
  <head>
  <meta charset="UTF-8">
  <title>Sistema de Flota</title>

  <link rel="icon" type="image/png" href="favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
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

  /* ===== BANNER ===== */
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
  .banner-text{
    position:absolute;bottom:20px;left:30px;color:white
  }

  /* ===== CARD ===== */
  .card {
    background: var(--card);
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    margin: 0 auto 35px; /* centrado horizontal */
    max-width: 1400px;   /* ancho máximo */
    overflow-x: auto;    /* scroll horizontal si la tabla es muy ancha */
  }


  /* ===== TABLE ===== */
  table{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
    border-radius:14px;
    overflow:hidden;
  }
  thead{background:#f8fafc}
  th,td{padding:14px;border-bottom:1px solid #e5e7eb}
  th{font-size:12px;color:var(--muted);text-transform:uppercase}
  tbody tr{
      transition:.18s;
  }

  tbody tr{
      cursor:pointer;
      transition:.18s;
  }

  tbody tr:hover{
      background:#dbeafe;
      transform:scale(1.005);
      box-shadow:0 6px 15px rgba(37,99,235,.15);
  }
  .alerta{background:linear-gradient(90deg,#fee2e2,transparent)}
  .acciones a{font-size:18px;cursor:pointer;margin-right:10px}

  /* ===== BUTTONS ===== */
  button,.btn{
    background:linear-gradient(135deg,var(--primary),var(--primary-dark));
    color:white;
    border:none;
    padding:12px 18px;
    border-radius:12px;
    cursor:pointer;
    font-weight:600;
  }
  .btn-danger{background:linear-gradient(135deg,#ef4444,#b91c1c)}
  .btn-secondary{background:#64748b}

  /* ===== MODALS ===== */
  .modal{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.55);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:1000;
  }
  .modal.active{display:flex}

  .modal-box{
    background:white;
    border-radius:18px;
    width:100%;
    max-width:760px;
    max-height:90vh;
    display:flex;
    flex-direction:column;
    animation:pop .2s ease;
  }

  @keyframes pop{
    from{transform:scale(.95);opacity:0}
    to{transform:scale(1)}
  }

  .modal-header{
    padding:20px 25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:1px solid #e5e7eb;
  }

  .modal-content{
    padding:20px 25px;
    overflow-y:auto;
  }

  .modal-footer{
    padding:15px 25px;
    display:flex;
    justify-content:flex-end;
    gap:10px;
    border-top:1px solid #e5e7eb;
    background:#fff;
  }

  /* ===== FORM ORDENADO ===== */
  .modal-form{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:16px 20px;
  }
  .modal-form h4{
    grid-column:1/-1;
    margin:10px 0 5px;
    font-size:15px;
    color:#1e40af;
  }
  .modal-form .full{grid-column:1/-1}
  .modal-form label{
    display:block;
    font-size:13px;
    font-weight:600;
    color:var(--muted);
    margin-bottom:5px;
  }
  .modal-form input{
    padding:11px;
    border-radius:10px;
    border:1px solid #cbd5f5;
    width:100%;
  }
  .modal-form input:focus{
    outline:none;
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(37,99,235,.15);
  }

  /* ===== FOOTER ===== */
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
    <img src="banner1.jpg" class="banner">
    <div class="banner-overlay"></div>

    <div class="banner-text">
      <h1>Gestión de Flota</h1>
      <p>Control documental y operacional</p>
    </div>

    <div class="banner-datetime">
      <div id="banner-date"></div>
      <div id="banner-time"></div>
    </div>
  </div>
  <!-- ===== AGREGADO ===== -->
    <button class="btn-secondary" onclick="verTodos()">📋 Ver todos</button>
    <button class="btn-secondary" onclick="verPorVencer()">🚨 Ver documentos por vencer</button>
    <button class="btn-secondary" onclick="abrirDashboard()">📈 Ver Dashboard</button>
    <button onclick="abrirAgregar()">➕ Agregar Vehículo</button>
    <button class="btn-secondary" onclick="exportarExcel()">📊 Exportar Excel</button>
  </div>

  <div id="contadorResultados" style="font-weight:600;color:#1e40af;margin-bottom:10px"></div>

  <!-- LISTADO -->
  <div class="card">

  <div style="
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:10px;
    margin-bottom:15px;
">

    <input
        id="buscar"
        placeholder="🔍 Patente..."
        style="
            flex:1 1 180px;
            max-width:260px;
            padding:12px;
            border-radius:12px;
            border:1px solid #cbd5f5;
        "
    >

    <select
        id="filtroTipo"
        style="
            flex:1 1 180px;
            max-width:260px;
            padding:12px;
            border-radius:12px;
            border:1px solid #cbd5f5;
        "
    >
        <option value="">🚚 Todos los tipos</option>
    </select>

    <!-- NUEVO FILTRO MARCA -->
    <select
        id="filtroMarca"
        style="
            flex:1 1 180px;
            max-width:260px;
            padding:12px;
            border-radius:12px;
            border:1px solid #cbd5f5;
        "
    >
        <option value="">🏷️ Todas las marcas</option>
    </select>

    <select
        id="filtroConfiguracion"
        style="
            flex:1 1 180px;
            max-width:260px;
            padding:12px;
            border-radius:12px;
            border:1px solid #cbd5f5;
        "
    >
        <option value="">⚙️ Todas las configuraciones</option>
    </select>

    <select
        id="anioDesde"
        style="
            flex:1 1 160px;
            max-width:240px;
            padding:12px;
            border-radius:12px;
            border:1px solid #cbd5f5;
        "
    >
        <option value="">📅 Año desde</option>
    </select>

    <select
        id="anioHasta"
        style="
            flex:1 1 160px;
            max-width:240px;
            padding:12px;
            border-radius:12px;
            border:1px solid #cbd5f5;
        "
    >
        <option value="">📅 Año hasta</option>
    </select>

</div>


  <div 
    id="alertaVencimiento"
    style="
      display:none;
      margin-bottom:15px;
      padding:14px 18px;
      border-radius:14px;
      background:linear-gradient(90deg,#fee2e2,#fecaca);
      color:#7f1d1d;
      font-weight:700;
      border-left:6px solid #dc2626;
    "
  ></div>

  <table id="tabla">
  <thead>
  <tr>
  <th>Patente</th>
  <th>Tipo</th>
  <th>Marca</th>
  <th>Configuración</th>
  <th>Sub Configuración</th>
  <th>Año</th>
  <th>Propietario</th>
  <th>Revisión</th>
  <th></th>
  </tr>
  </thead>
  <tbody>
  <?php
  $limite=date('Y-m-d',strtotime('+30 days'));
  $res=$conn->query("SELECT * FROM vehiculos");
  while($v=$res->fetch_assoc()):
  $alerta=(
    $v['fecha_permiso']<=$limite ||
    $v['fecha_soap']<=$limite ||
    $v['fecha_revision_tecnica']<=$limite
  )?'alerta':'';
  ?>
  <tr
  class="<?= $alerta ?>"
  onclick='verVehiculo(<?= json_encode($v, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'

  data-modelo="<?= htmlspecialchars($v['modelo']) ?>"
  data-chasis="<?= htmlspecialchars($v['numero_chasis']) ?>"
  data-color="<?= htmlspecialchars($v['color']) ?>"
  data-hp="<?= htmlspecialchars($v['hp']) ?>"
  data-propietario="<?= htmlspecialchars($v['propietario']) ?>"
  style="cursor:pointer"


  data-fecha-permiso="<?= $v['fecha_permiso'] ?>"
  data-valor-permiso="<?= $v['valor_permiso'] ?>"
  data-municipalidad="<?= htmlspecialchars($v['municipalidad']) ?>"
  data-fecha-soap="<?= $v['fecha_soap'] ?>"
  data-valor-soap="<?= $v['valor_soap'] ?>"
  data-aseguradora="<?= htmlspecialchars($v['aseguradora']) ?>"
  data-fecha-revision="<?= $v['fecha_revision_tecnica'] ?>"
  data-valor-revision="<?= $v['valor_revision_tecnica'] ?>"
  >

  <td><?= $v['patente'] ?></td>
  <td><?= $v['tipo'] ?></td>
  <td><?= $v['marca'] ?></td>
  <td><?= $v['configuracion'] ?></td>
  <td><?= $v['sub_configuracion'] ?></td>
  <td><?= $v['anio'] ?></td>
  <td><?= $v['propietario'] ?></td>
  <td><?= date('d/m/y', strtotime($v['fecha_revision_tecnica'])) ?></td>
  <td class="acciones">
  <a onclick='event.stopPropagation(); editar(<?= json_encode($v) ?>)'>✏️</a>

  <a onclick="event.stopPropagation(); eliminar('<?= $v['patente'] ?>')">🗑️</a>
  </td>
  </tr>
  <?php endwhile; ?>
  </tbody>
  </table>
  </div>
  <!-- MODAL DASHBOARD -->
<div class="modal" id="modalDashboard">

    <div class="modal-box" style="max-width:1100px">

        <div class="modal-header">
            <h3>📊 Dashboard de Flota</h3>
            <span onclick="cerrar()">✖</span>
        </div>

        <div class="modal-content">

            <div style="
                display:grid;
                grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
                gap:25px;
            ">

                <!-- =========================
                     PRIMERA TANDA
                ========================== -->

                <div class="card">
                    <h4>🚛 Vehículos por Marca</h4>
                    <canvas id="graficoMarcas"></canvas>
                </div>

                <div class="card">
                    <h4>📦 Vehículos por Tipo</h4>
                    <canvas id="graficoTipos"></canvas>
                </div>

                <div class="card">
                    <h4>⚙️ Por Configuración</h4>
                    <canvas id="graficoConfiguracion"></canvas>
                </div>


                <!-- =========================
                     SEGUNDA TANDA
                ========================== -->

                <div class="card">
                    <h4>👤 Vehículos por Propietario</h4>
                    <canvas id="graficoPropietarios"></canvas>
                </div>

                <div class="card">
                    <h4>🧩 Vehículos por Sub Configuración</h4>
                    <canvas id="graficoSubConfiguracion"></canvas>
                </div>

                <div class="card">
                    <h4>📅 Vehículos por Año</h4>
                    <canvas id="graficoAnios"></canvas>
                </div>


                <!-- =========================
                     TERCERA TANDA
                     GASTOS Y VALORES
                ========================== -->

                <div class="card">
                    <h4>💰 Gastos Totales</h4>
                    <canvas id="graficoGastos"></canvas>
                </div>

                <div class="card">
                    <h4>🏛️ Gastos por Comuna (Solo Permisos)</h4>
                    <canvas id="graficoPermisosComuna"></canvas>
                </div>

                <div class="card">
                    <h4>🛡️ Gastos SOAP por Aseguradora</h4>
                    <canvas id="graficoSoapAseguradora"></canvas>
                </div>

            </div>

        </div>

        <div class="modal-footer">
            <button class="btn-secondary" onclick="cerrar()">
                ⬅ Volver
            </button>
        </div>

    </div>

</div>


  <!-- MODAL VER VEHÍCULO -->
  <div class="modal" id="modalVer">

  <div class="modal-box" style="max-width:900px">

  <div class="modal-header">
      <h3>🚛 Ficha del Vehículo</h3>
      <span onclick="cerrar()">✖</span>
  </div>

  <div class="modal-content">

      <h4 style="color:#2563eb">🚛 Datos Generales</h4>

      <table style="width:100%;margin-bottom:20px">
          <tr>
              <td><strong>Patente</strong></td>
              <td id="v_patente"></td>

              <td><strong>Tipo</strong></td>
              <td id="v_tipo"></td>
          </tr>

          <tr>
              <td><strong>Marca</strong></td>
              <td id="v_marca"></td>

              <td><strong>Modelo</strong></td>
              <td id="v_modelo"></td>
          </tr>

          <tr>
              <td><strong>N° Chasis</strong></td>
              <td id="v_chasis"></td>

              <td><strong>Color</strong></td>
              <td id="v_color"></td>
          </tr>

          <tr>
              <td><strong>HP</strong></td>
              <td id="v_hp"></td>

              <td><strong>Año</strong></td>
              <td id="v_anio"></td>
          </tr>

          <tr>
              <td><strong>Configuración</strong></td>
              <td id="v_configuracion"></td>

              <td><strong>Sub Configuración</strong></td>
              <td id="v_subconfiguracion"></td>
          </tr>

          <tr>
              <td><strong>Propietario</strong></td>
              <td colspan="3" id="v_propietario"></td>
          </tr>
      </table>

      <hr>

      <h4 style="color:#2563eb">📄 Permiso de Circulación</h4>

      <table style="width:100%;margin-bottom:20px">

          <tr>
              <td><strong>Fecha Vencimiento</strong></td>
              <td id="v_permiso"></td>

              <td><strong>Valor</strong></td>
              <td id="v_valor_permiso"></td>
          </tr>

          <tr>
              <td><strong>Municipalidad</strong></td>
              <td colspan="3" id="v_municipalidad"></td>
          </tr>

      </table>

      <hr>

      <h4 style="color:#2563eb">🛡 SOAP</h4>

      <table style="width:100%;margin-bottom:20px">

          <tr>
              <td><strong>Fecha Vencimiento</strong></td>
              <td id="v_soap"></td>

              <td><strong>Valor</strong></td>
              <td id="v_valor_soap"></td>
          </tr>

          <tr>
              <td><strong>Aseguradora</strong></td>
              <td colspan="3" id="v_aseguradora"></td>
          </tr>

      </table>

      <hr>

      <h4 style="color:#2563eb">🔧 Revisión Técnica</h4>

      <table style="width:100%;margin-bottom:20px">

        <tr>
        
            <td><strong>Fecha Vencimiento</strong></td>
            <td  id="v_revision"></td>

        <td><strong>Valor</strong></td>
        <td  id="v_valor_revision"></td>
    </tr>

</table>

  </div>

  <div class="modal-footer">
      <button class="btn-secondary" onclick="cerrar()">Cerrar</button>
  </div>

  </div>

  </div>


  <!-- MODAL EDITAR -->
  <div class="modal" id="modalEditar">
  <div class="modal-box">
  <div class="modal-header">
  <h3>✏️ Editar Vehículo</h3>
  <span onclick="cerrar()">✖</span>
  </div>
  <div class="modal-content">
  <form action="actualizar.php" method="POST" class="modal-form" id="formEditar">
  <div class="full">
    <label>Patente</label>
    <input type="text" name="patente" id="e_patente" readonly>
  </div>

  <div class="full"><label>Tipo</label><input name="tipo" id="e_tipo"></div>
  <div><label>Marca</label><input name="marca" id="e_marca"></div>
  <div><label>Modelo</label><input name="modelo" id="e_modelo"></div>
  <div><label>Número de Chasis</label><input name="numero_chasis" id="e_numero_chasis"></div>
  <div><label>Color</label><input name="color" id="e_color"></div>
  <div><label>HP</label><input type="number" name="hp" id="e_hp"></div>
  <div><label>Configuración</label><input name="configuracion" id="e_configuracion"></div>
  <div><label>Sub Configuración</label><input name="sub_configuracion" id="e_sub_configuracion"></div>
  <div><label>Año</label><input name="anio" id="e_anio"></div>
  <div><label>Propietario</label><input name="propietario" id="e_propietario"></div>
  <div class="full"><label>Permiso</label><input type="date" name="fecha_permiso" id="e_fecha_permiso"></div>
  <div><label>Valor Permiso</label>
    <input type="number" name="valor_permiso" id="e_valor_permiso">
  </div>
  <div class="full"><label>Municipalidad</label>
    <input name="municipalidad" id="e_municipalidad">
  </div>
  <div class="full"><label>SOAP</label><input type="date" name="fecha_soap" id="e_fecha_soap"></div>
  <div><label>Valor SOAP</label>
    <input type="number" name="valor_soap" id="e_valor_soap">
  </div>
  <div class="full"><label>Aseguradora</label>
    <input name="aseguradora" id="e_aseguradora">
  </div>
  <div>
    <label>Revisión Técnica</label>
    <input type="date"
           name="fecha_revision_tecnica"
           id="e_fecha_revision_tecnica">
</div>

<div>
    <label>Valor Revisión Técnica</label>
    <input type="number"
           name="valor_revision_tecnica"
           id="e_valor_revision_tecnica"
           min="0">
</div>
  </form>
  </div>
  <div class="modal-footer">
  <button class="btn-secondary" onclick="cerrar()">Cancelar</button>
  <button type="submit" form="formEditar">Actualizar</button>
  </div>
  </div>
  </div>

  <!-- MODAL ELIMINAR -->
  <div class="modal" id="modalEliminar">
  <div class="modal-box" style="max-width:420px">
  <div class="modal-header">
  <h3>🗑️ Eliminar Vehículo</h3>
  <span onclick="cerrar()">✖</span>
  </div>
  <div class="modal-content" style="text-align:center">
  <p>¿Deseas eliminar el vehículo?</p>
  <strong id="patenteEliminar" style="color:#dc2626;font-size:20px"></strong>
  </div>
  <div class="modal-footer">
  <button class="btn-secondary" onclick="cerrar()">Cancelar</button>
  <form action="eliminar.php" method="POST">
  <input type="hidden" name="patente" id="inputEliminar">
  <button type="submit" class="btn-danger">Eliminar</button>
  </form>
  </div>
  </div>
  </div>

  <!-- MODAL AGREGAR -->
  <div class="modal" id="modalAgregar">
  <div class="modal-box">

  <div class="modal-header">
    <h3>➕ Agregar Vehículo</h3>
    <span onclick="cerrar()">✖</span>
  </div>

  <div class="modal-content">
  <form action="guardar.php" method="POST" class="modal-form" id="formAgregar">


  <h4>🚗 Datos del Vehículo</h4>
  <div class="full"><label>Patente</label><input name="patente" required></div>
  <div><label>Tipo</label><input name="tipo" required></div>
  <div><label>Marca</label><input name="marca" required></div>
  <div><label>Modelo</label><input name="modelo" required></div>
  <div><label>Número de Chasis</label><input name="numero_chasis"></div>
  <div><label>Color</label><input name="color"></div>
  <div><label>HP</label><input type="number" name="hp"></div>
  <div><label>Configuración</label><input name="configuracion" required></div>
  <div><label>Sub Configuración</label><input name="sub_configuracion"></div>
  <div><label>Año</label><input type="number" name="anio" required></div>
  <div><label>Propietario</label><input name="propietario"></div>

  <h4>📄 Permiso de Circulación</h4>
  <div><label>Vencimiento</label><input type="date" name="fecha_permiso" required></div>
  <div><label>Valor</label><input type="number" name="valor_permiso"></div>
  <div class="full"><label>Municipalidad</label><input name="municipalidad"></div>

  <h4>🛡️ SOAP</h4>
  <div><label>Vencimiento</label><input type="date" name="fecha_soap" required></div>
  <div><label>Valor</label><input type="number" name="valor_soap"></div>
  <div class="full"><label>Aseguradora</label><input name="aseguradora"></div>

  <h4>🔧 Revisión Técnica</h4>

<div>
    <label>Vencimiento</label>
    <input type="date" name="fecha_revision_tecnica" required>
</div>

<div>
    <label>Valor</label>
    <input type="number" name="valor_revision_tecnica" min="0">
</div>

  </form>
  </div>

  <div class="modal-footer">
    <button type="button" class="btn-secondary" onclick="cerrar()">Cancelar</button>
  <button type="submit" form="formAgregar">Guardar Vehículo</button>

  </div>

  </div>
  </div>

  <footer>
  <strong>Transportes Mimbral LTDA</strong><br>
  Gestión empresarial · © <?= date('Y') ?>
  </footer>

  <script>
  const filas = document.querySelectorAll('#tabla tbody tr');
  const ordenOriginal = Array.from(filas);
  const tiposSet = new Set();
const marcasSet = new Set();
const configuracionesSet = new Set();
let aniosSet = new Set();

/* Relación Tipo -> Configuraciones */
const configuracionesPorTipo = {
    "CAMION": [
        "3/4",
        "SENCILLO",
        "TOLVA"
    ],

    "TRACTOCAMION": [
        "SENCILLO",
        "DOBLE PUENTE"
    ],

    "SEMIREMOLQUE": [
        "RAMPLA",
        "CHASIS",
        "ESTANQUE",
        "CORTINA",
        "BATEA",
        "CAMA BAJA"
    ],

    "REMOLQUE": [
        "CARRO"
    ]
};

  /* ===== OBTENER TIPOS Y AÑOS EXISTENTES ===== */
  filas.forEach(r=>{
    tiposSet.add(r.cells[1].innerText.trim());

    // NUEVO: obtener marcas
    marcasSet.add(r.cells[2].innerText.trim());

    configuracionesSet.add(r.cells[3].innerText.trim());
    aniosSet.add(parseInt(r.cells[5].innerText));
});

  /* ===== CARGAR SELECT TIPOS ===== */
  tiposSet.forEach(t=>{
    filtroTipo.innerHTML += `<option value="${t}">${t}</option>`;
  });

  

/* Al iniciar muestra todas las marcas */
cargarMarcasPorTipo('');

  /* ===== CARGAR TODAS LAS CONFIGURACIONES ===== */
function cargarConfiguraciones(lista){

    filtroConfiguracion.innerHTML =
        '<option value="">⚙️ Todas las configuraciones</option>';

    lista.forEach(c=>{

        filtroConfiguracion.innerHTML +=
            `<option value="${c}">${c}</option>`;

    });

}

/* ===== MARCAS DEPENDIENTES DEL TIPO ===== */

function cargarMarcasPorTipo(tipo){

    filtroMarca.innerHTML =
        '<option value="">🏷️ Todas las marcas</option>';

    const marcas = new Set();

    filas.forEach(r => {

        const tipoFila = r.cells[1].innerText.trim().toUpperCase();
        const marcaFila = r.cells[2].innerText.trim();

        // Si no hay tipo seleccionado, mostrar todas
        // Si hay tipo, solamente sus marcas
        if(
            tipo === "" ||
            tipoFila === tipo
        ){

            if(marcaFila !== ""){
                marcas.add(marcaFila);
            }

        }

    });

    [...marcas]
        .sort((a,b) => a.localeCompare(b, 'es'))
        .forEach(marca => {

            filtroMarca.innerHTML +=
                `<option value="${marca}">${marca}</option>`;

        });
}


/* Al iniciar muestra todas */
cargarConfiguraciones([...configuracionesSet].sort());


  /* ===== CARGAR SELECT AÑOS ===== */
  [...aniosSet].sort((a,b)=>a-b).forEach(a=>{
    anioDesde.innerHTML += `<option value="${a}">${a}</option>`;
    anioHasta.innerHTML += `<option value="${a}">${a}</option>`;
  });

  /* ===== FILTRO AVANZADO ===== */
  function filtrarTabla(){
    const patente = buscar.value.toUpperCase();
    const tipo = filtroTipo.value.toUpperCase();
    const marca = filtroMarca.value.toUpperCase();
    const configuracion = filtroConfiguracion.value.toUpperCase();
    const desde = anioDesde.value ? parseInt(anioDesde.value) : 0;
    const hasta = anioHasta.value ? parseInt(anioHasta.value) : 9999;

    let visibles = 0;

    filas.forEach(r=>{
     const cPatente = r.cells[0].innerText.toUpperCase();
const cTipo = r.cells[1].innerText.toUpperCase();
const cMarca = r.cells[2].innerText.toUpperCase();
const cConfiguracion = r.cells[3].innerText.toUpperCase();
const cAnio = parseInt(r.cells[5].innerText);

      let ok = true;

      if (patente && !cPatente.includes(patente)) ok = false;
if (tipo && cTipo !== tipo) ok = false;
if (marca && cMarca !== marca) ok = false;
if (configuracion && cConfiguracion !== configuracion) ok = false;
      if (cAnio < desde || cAnio > hasta) ok = false;

      r.style.display = ok ? '' : 'none';
      if (ok) visibles++;
    });

    /* ===== CONTADOR ===== */
    if (visibles === 1) {
      contadorResultados.innerText = '🔎 Se encontró 1 vehículo';
    } else {
      contadorResultados.innerText = `🔎 Se encontraron ${visibles} vehículos`;
    }
  }

  /* ===== EVENTOS ===== */
/* ===== CONFIGURACIONES DEPENDIENTES DEL TIPO ===== */

filtroTipo.addEventListener('change', function(){

    const tipo = this.value.toUpperCase();

    /* =========================
       CONFIGURACIONES
    ========================= */

    if(tipo === "" || !configuracionesPorTipo[tipo]){

        cargarConfiguraciones([...configuracionesSet].sort());

    }else{

        cargarConfiguraciones(configuracionesPorTipo[tipo]);

    }

    /* =========================
       MARCAS DEPENDIENTES
       DEL TIPO
    ========================= */

    cargarMarcasPorTipo(tipo);

    /* =========================
       REINICIAR SELECCIONES
    ========================= */

    filtroConfiguracion.value = "";
    filtroMarca.value = "";

    filtrarTabla();

});

  ['buscar','filtroTipo','filtroMarca','filtroConfiguracion','anioDesde','anioHasta'].forEach(id=>{
    document.getElementById(id).addEventListener('input', filtrarTabla);
    document.getElementById(id).addEventListener('change', filtrarTabla);
  });

  /* INICIAL */
  filtrarTabla();

  function formatearFecha(fecha){

    if(!fecha || fecha=="0000-00-00")
        return "-";

    const partes = fecha.split('-');

    return `${partes[2]}-${partes[1]}-${partes[0]}`;
}

  function formatearPeso(valor){

      if(valor==null || valor=="")
          return "-";

      return "$ " + Number(valor).toLocaleString('es-CL');
  }


  function abrirAgregar(){document.getElementById('modalAgregar').classList.add('active');}
  function cerrar(){document.querySelectorAll('.modal').forEach(m=>m.classList.remove('active'));}


  function verVehiculo(v){

      v_patente.innerText = v.patente;
      v_tipo.innerText = v.tipo;
      v_marca.innerText = v.marca;
      v_modelo.innerText = v.modelo;
      v_chasis.innerText = v.numero_chasis;
      v_color.innerText = v.color;
      v_hp.innerText = v.hp;

      v_configuracion.innerText = v.configuracion;
      v_subconfiguracion.innerText = v.sub_configuracion;
      v_anio.innerText = v.anio;
      v_propietario.innerText = v.propietario;

      // Permiso
      v_permiso.innerText = formatearFecha(v.fecha_permiso);
      v_valor_permiso.innerText = formatearPeso(v.valor_permiso);
      v_municipalidad.innerText = v.municipalidad;

      // SOAP
      v_soap.innerText = formatearFecha(v.fecha_soap);
      v_valor_soap.innerText = formatearPeso(v.valor_soap);
      v_aseguradora.innerText = v.aseguradora;

      // Revisión
      // Revisión Técnica
      v_revision.innerText =
      formatearFecha(v.fecha_revision_tecnica);

      v_valor_revision.innerText =
      formatearPeso(v.valor_revision_tecnica);

      modalVer.classList.add('active');
  }


  function editar(v){
    e_patente.value=v.patente;
    e_tipo.value=v.tipo;
    e_marca.value=v.marca;
    e_modelo.value=v.modelo;
    e_numero_chasis.value = v.numero_chasis;
    e_color.value = v.color;
    e_hp.value = v.hp;
    e_configuracion.value = v.configuracion;
    e_sub_configuracion.value = v.sub_configuracion;
    e_anio.value = v.anio;
    e_propietario.value = v.propietario;
    e_fecha_permiso.value=v.fecha_permiso;
    e_valor_permiso.value = v.valor_permiso;
    e_municipalidad.value = v.municipalidad;
    e_fecha_soap.value=v.fecha_soap;
    e_valor_soap.value = v.valor_soap;
    e_aseguradora.value = v.aseguradora;
    e_fecha_revision_tecnica.value = v.fecha_revision_tecnica;
    e_valor_revision_tecnica.value = v.valor_revision_tecnica;
    modalEditar.classList.add('active');
  }

  function eliminar(p){
    patenteEliminar.innerText=p;
    inputEliminar.value=p;
    modalEliminar.classList.add('active');
  }


function formatoMonedaExcel(valor){

    if(valor === null || valor === undefined || valor === ''){
        return '';
    }

    let numero = Number(
        String(valor)
            .replace(/\./g, '')
            .replace(',', '.')
    );

    if(isNaN(numero)){
        return '';
    }

    return numero.toFixed(0);
}

  function exportarExcel(){

    let tabla = document.getElementById("tabla");
    let filas = tabla.querySelectorAll("tbody tr");

    /* BOM UTF-8 para Excel */
    let html = '\ufeff<table border="1"><tr>';

    /* ===== CABECERAS ===== */

    const headers = [
        'PATENTE',
        'TIPO',
        'MARCA',
        'MODELO',
        'NÚMERO CHASIS',
        'COLOR',
        'HP',
        'CONFIGURACIÓN',
        'SUB CONFIGURACIÓN',
        'AÑO',
        'PROPIETARIO',
        'FECHA PERMISO',
        'VALOR PERMISO',
        'MUNICIPALIDAD',
        'FECHA SOAP',
        'VALOR SOAP',
        'ASEGURADORA',
        'REVISIÓN TÉCNICA',
        'VALOR REVISIÓN TÉCNICA'
    ];

    headers.forEach(h=>{
        html += `<th>${h}</th>`;
    });

    html += `</tr>`;

    /* ===== FILAS VISIBLES ===== */

    filas.forEach(fila=>{

        if(fila.style.display !== 'none'){

            html += `<tr>`;

            /* Datos generales */

            html += `<td style="mso-number-format:'\\@';">${fila.cells[0].innerText}</td>`;
            html += `<td>${fila.cells[1].innerText}</td>`;
            html += `<td>${fila.cells[2].innerText}</td>`;

            html += `<td style="mso-number-format:'\\@';">
                        ${fila.dataset.modelo || ''}
                     </td>`;

            html += `<td style="mso-number-format:'\\@';">
                        ${fila.dataset.chasis || ''}
                     </td>`;

            html += `<td>${fila.dataset.color || ''}</td>`;
            html += `<td>${fila.dataset.hp || ''}</td>`;

            html += `<td>${fila.cells[3].innerText}</td>`;
            html += `<td>${fila.cells[4].innerText}</td>`;
            html += `<td>${fila.cells[5].innerText}</td>`;
            html += `<td>${fila.cells[6].innerText}</td>`;

            /* ===== PERMISO ===== */

            html += `<td>${fila.dataset.fechaPermiso || ''}</td>`;

            html += `
                <td style="mso-number-format:'$#,##0';">
                    ${formatoMonedaExcel(fila.dataset.valorPermiso)}
                </td>
            `;

            html += `<td>${fila.dataset.municipalidad || ''}</td>`;

            /* ===== SOAP ===== */

            html += `<td>${fila.dataset.fechaSoap || ''}</td>`;

            html += `
                <td style="mso-number-format:'$#,##0';">
                    ${formatoMonedaExcel(fila.dataset.valorSoap)}
                </td>
            `;

            html += `<td>${fila.dataset.aseguradora || ''}</td>`;

            /* ===== REVISIÓN TÉCNICA ===== */

            html += `<td>${fila.cells[7].innerText}</td>`;

            html += `
                <td style="mso-number-format:'$#,##0';">
                    ${formatoMonedaExcel(fila.dataset.valorRevision)}
                </td>
            `;

            html += `</tr>`;
        }

    });

    html += `</table>`;

    /* ===== DESCARGA ===== */

    let blob = new Blob([html], {
        type: 'application/vnd.ms-excel;charset=utf-8;'
    });

    let link = document.createElement("a");

    link.href = URL.createObjectURL(blob);
    link.download = "flota_vehiculos.xls";

    document.body.appendChild(link);
    link.click();

    document.body.removeChild(link);

    URL.revokeObjectURL(link.href);
}


  </script>
  <!-- ===== AGREGADO: JS DASHBOARD ===== -->
  <script>
  let chartsDashboard=[];

  function abrirDashboard(){
    modalDashboard.classList.add('active');
    chartsDashboard.forEach(c=>c.destroy());
    chartsDashboard=[];

    const filas=document.querySelectorAll('#tabla tbody tr');
    const marcas = {};
const tipos = {};
const config = {};
const comunasPermiso = {};
const propietarios = {};
const subConfiguraciones = {};
const anios = {};
const aseguradorasSoap = {};
    let permiso = 0;
    let soap = 0;
    let revision = 0;

    filas.forEach(f=>{
      if(f.style.display==='none') return;

      marcas[f.cells[2].innerText]=(marcas[f.cells[2].innerText]||0)+1;
      tipos[f.cells[1].innerText]=(tipos[f.cells[1].innerText]||0)+1;
      config[f.cells[3].innerText]=(config[f.cells[3].innerText]||0)+1;
      const propietario = f.dataset.propietario || "Sin propietario";
      propietarios[propietario] = (propietarios[propietario] || 0) + 1;

      /* ==========================
   SUB CONFIGURACIÓN
========================== */

const subConfiguracion =
    f.cells[4].innerText.trim() || "Sin sub configuración";

subConfiguraciones[subConfiguracion] =
    (subConfiguraciones[subConfiguracion] || 0) + 1;


/* ==========================
   AÑO
========================== */

const anio =
    f.cells[5].innerText.trim() || "Sin año";

anios[anio] =
    (anios[anio] || 0) + 1;

      permiso += parseInt(f.dataset.valorPermiso || 0);
      soap += parseInt(f.dataset.valorSoap || 0);
      revision += parseInt(f.dataset.valorRevision || 0);

      const comuna = f.dataset.municipalidad || 'Sin comuna';
      const valorPermiso = parseInt(f.dataset.valorPermiso) || 0;
      const totalGastos = permiso + soap + revision;

      comunasPermiso[comuna] = (comunasPermiso[comuna] || 0) + valorPermiso;

      /* ==========================
   GASTOS SOAP POR ASEGURADORA
========================== */

const aseguradora =
    f.dataset.aseguradora?.trim() || "Sin aseguradora";

const valorSoap =
    parseInt(f.dataset.valorSoap) || 0;

aseguradorasSoap[aseguradora] =
    (aseguradorasSoap[aseguradora] || 0) + valorSoap;

    });

    chartsDashboard.push(crearGrafico('graficoMarcas',marcas,'pie'));
    chartsDashboard.push(crearGrafico('graficoTipos',tipos,'doughnut'));
    chartsDashboard.push(crearGrafico('graficoConfiguracion',config,'pie'));
    chartsDashboard.push(crearGrafico('graficoPropietarios',propietarios,'doughnut'));

    /* ==========================
   SUB CONFIGURACIÓN
========================== */

chartsDashboard.push(
    crearGrafico(
        'graficoSubConfiguracion',
        subConfiguraciones,
        'bar'
    )
);


/* ==========================
   AÑOS
========================== */

const aniosOrdenados = Object.keys(anios)
    .sort((a,b) => {

        const numeroA = parseInt(a);
        const numeroB = parseInt(b);

        if(isNaN(numeroA)) return 1;
        if(isNaN(numeroB)) return -1;

        return numeroA - numeroB;

    })
    .reduce((obj, key) => {

        obj[key] = anios[key];

        return obj;

    }, {});


chartsDashboard.push(
    crearGrafico(
        'graficoAnios',
        aniosOrdenados,
        'bar'
    )
);


    


    chartsDashboard.push(new Chart(graficoGastos,{
      type:'bar',
      data:{
        
        labels:['Permiso','SOAP','Revisión Técnica'],
        datasets:[{
        data:[permiso, soap, revision]
}]
      },
      options:{plugins:{legend:{display:false}}}
    }));
    chartsDashboard.push(new Chart(graficoPermisosComuna,{
    type:'bar',
    data:{
      labels:Object.keys(comunasPermiso),
      datasets:[{
        label:'Total Permisos por Comuna',
        data:Object.values(comunasPermiso)
      }]
    },
    options:{
      plugins:{legend:{display:false}},
      scales:{y:{beginAtZero:true}}
    }
  }));

  chartsDashboard.push(new Chart(graficoSoapAseguradora, {
    type: 'bar',
    data: {
        labels: Object.keys(aseguradorasSoap),
        datasets: [{
            label: 'Total SOAP por Aseguradora',
            data: Object.values(aseguradorasSoap)
        }]
    },
    options: {
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
}));

  }

  function crearGrafico(id,data,tipo){
    return new Chart(document.getElementById(id),{
      type:tipo,
      data:{
        labels:Object.keys(data),
        datasets:[{data:Object.values(data)}]
      },
      options:{plugins:{legend:{position:'bottom'}}}
    });
    
  }
  </script>

  <script>
  function verPorVencer(){
    const tbody = document.querySelector('#tabla tbody');
    const filasArray = Array.from(filas);

    // Filtrar solo las que están por vencer
    const porVencer = filasArray.filter(fila =>
      fila.classList.contains('alerta')
    );

    // Obtener la fecha más próxima de una fila
    function fechaMasProxima(fila){
      const fechas = [
        fila.dataset.fechaPermiso,  // permiso
        fila.dataset.fechaSoap,  // soap
        fila.dataset.fechaRevision  // revisión
      ]
      .filter(f => f) // eliminar vacíos
      .map(f => {
        const [d,m,y] = f.split('/');
        return new Date(`20${y}-${m}-${d}`);
      });

      return new Date(Math.min(...fechas));
    }

    // ORDENAR por fecha más próxima
    porVencer.sort((a,b) => fechaMasProxima(a) - fechaMasProxima(b));

    // Ocultar todas
    filasArray.forEach(f => f.style.display = 'none');

    // Reinsertar ordenadas
    porVencer.forEach(f => {
      f.style.display = '';
      tbody.appendChild(f);
    });

    // Alerta superior
    const alerta = document.getElementById('alertaVencimiento');
    alerta.innerHTML = `🚨 Vehículos por vencer: <strong>${porVencer.length}</strong>`;
    alerta.style.display = 'block';

    contadorResultados.innerText =
      porVencer.length === 1
        ? '🔎 Se encontró 1 vehículo por vencer'
        : `🔎 Se encontraron ${porVencer.length} vehículos por vencer`;
  }

  function verTodos(){ 
    const tbody = document.querySelector('#tabla tbody'); 

    /* ==========================================
       LIMPIAR TODOS LOS FILTROS
    ========================================== */

    buscar.value = '';
    filtroTipo.value = '';
    filtroMarca.value = '';
    filtroConfiguracion.value = '';
    anioDesde.value = '';
    anioHasta.value = '';

    /* ==========================================
       RESTAURAR OPCIONES DEPENDIENTES
       DEL TIPO
    ========================================== */

    // Todas las marcas nuevamente
    cargarMarcasPorTipo('');

    // Todas las configuraciones nuevamente
    cargarConfiguraciones(
        [...configuracionesSet].sort()
    );

    /* ==========================================
       RESTAURAR ORDEN ORIGINAL
    ========================================== */

    ordenOriginal.forEach(fila => {
        fila.style.display = '';
        tbody.appendChild(fila);
    });

    /* ==========================================
       OCULTAR ALERTA DE VENCIMIENTO
    ========================================== */

    const alerta = document.getElementById('alertaVencimiento');

    alerta.style.display = 'none';
    alerta.innerHTML = '';

    /* ==========================================
       ACTUALIZAR CONTADOR
    ========================================== */

    const total = ordenOriginal.length;

    contadorResultados.innerText =
        total === 1
            ? '🔎 Se encontró 1 vehículo'
            : `🔎 Se encontraron ${total} vehículos`;
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

  document.querySelectorAll('.modal').forEach(modal=>{

      modal.addEventListener('click',function(e){

          if(e.target===modal){
              cerrar();
          }

      });

  });


function formatoMonedaExcel(valor){
    if(valor === null || valor === undefined || valor === ''){
        return '';
    }

    valor = Number(String(valor).replace(/\./g, '').replace(',', '.'));

    if(isNaN(valor)){
        return '';
    }

    return valor.toFixed(0);
}

  </script>

  </body>
  </html>

  </body>
  </html>
