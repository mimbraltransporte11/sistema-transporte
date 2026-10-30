<?php
include 'db.php';

$id = intval($_GET['id']);

$q = $conn->query("
    SELECT equipo, cantidad
    FROM solicitud_detalle
    WHERE solicitud_id = $id
");

if(!$q || $q->num_rows == 0){
    echo "<p>No hay detalle registrado</p>";
    exit;
}

echo "<table style='width:100%; border-collapse:collapse'>";
echo "<tr>
        <th style='padding:10px'>Equipo</th>
        <th style='padding:10px'>Cantidad</th>
      </tr>";

while($r = $q->fetch_assoc()){

    $equipo   = htmlspecialchars($r['equipo']);
    $cantidad = intval($r['cantidad']);

    echo "<tr>
            <td style='padding:10px; border-top:1px solid #e5e7eb'>$equipo</td>
            <td style='padding:10px; border-top:1px solid #e5e7eb'><strong>$cantidad</strong></td>
          </tr>";
}

echo "</table>";