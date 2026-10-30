<?php
include 'db.php';

$q = $_GET['q'] ?? '';

if(!$q) exit;

$q = $conn->real_escape_string($q);

$res = $conn->query("
    SELECT * FROM conductores
    WHERE rut LIKE '%$q%'
    OR nombre LIKE '%$q%'
    LIMIT 8
");

while($c = $res->fetch_assoc()){
    echo "
        <div class='item-resultado'
             data-rut='{$c['rut']}'
             data-nombre='{$c['nombre']}'
             data-area='{$c['area']}'
        >
            {$c['nombre']} — {$c['rut']}
        </div>
    ";
}