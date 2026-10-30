<?php
include 'db.php';

$q = $_GET['q'];

$res = $conn->query("
SELECT * FROM conductores 
WHERE nombre LIKE '%$q%' OR rut LIKE '%$q%'
LIMIT 10
");

while($row = $res->fetch_assoc()){
echo "
<div class='item' 
data-id='$row[id]' 
data-nombre='$row[nombre]'>
$row[nombre] - $row[rut]
</div>
";
}
?>