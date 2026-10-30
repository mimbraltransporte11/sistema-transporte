<?php
include 'db.php';

$q = $_GET['q'] ?? '';
$tipo = $_GET['tipo'] ?? '';

$q = strtoupper($q);

// 🚨 FILTRO POR TIPO
if($tipo === 'TRACTO'){
    $whereTipo = "tipo IN ('TRACTO','CAMION')";
}else{
    $whereTipo = "tipo NOT IN ('TRACTO','CAMION')";
}

// CONSULTA
$sql = "SELECT * FROM equipos 
        WHERE $whereTipo 
        AND ppu LIKE '%$q%' 
        LIMIT 10";

$res = $conn->query($sql);

// RESULTADOS
while($row = $res->fetch_assoc()){
    echo "<div class='itemEq' data-id='{$row['id']}' data-ppu='{$row['ppu']}'>
            {$row['ppu']} ({$row['tipo']})
          </div>";
}