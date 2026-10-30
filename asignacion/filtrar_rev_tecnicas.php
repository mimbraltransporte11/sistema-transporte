<?php

include 'db.php';

$mes = intval($_GET['mes']);
$anio = intval($_GET['anio']);

// ================= INICIO FILTRO =================
$inicio = date(
    'Y-m-01',
    strtotime("$anio-$mes-01")
);

// ================= FIN FILTRO =================
// siempre hasta el día 10 del próximo mes
$fin = date(
    'Y-m-10',
    strtotime('+1 month')
);

$query = $conn->query("
SELECT 
e.ppu,
e.tipo,
e.revision_tecnica,
c.nombre AS conductor

FROM equipos e

LEFT JOIN asignaciones a 
  ON e.id = a.equipo_id 
  AND a.fecha_fin IS NULL

LEFT JOIN conductores c 
  ON a.conductor_id = c.id

WHERE e.revision_tecnica BETWEEN '$inicio' AND '$fin'

ORDER BY e.revision_tecnica ASC
");

while($row = $query->fetch_assoc()):
?>

<tr>

  <td><?= $row['ppu'] ?></td>

  <td><?= $row['tipo'] ?></td>

  <td>
    <?php if($row['conductor']): ?>

      <span style="color:#16a34a">
        <?= $row['conductor'] ?>
      </span>

    <?php else: ?>

      <span style="color:#64748b">
        SITIO
      </span>

    <?php endif; ?>
  </td>

  <td>
    <?= date('d-m-Y', strtotime($row['revision_tecnica'])) ?>
  </td>

</tr>

<?php endwhile; ?>