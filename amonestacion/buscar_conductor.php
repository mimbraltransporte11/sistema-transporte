<?php
include 'db.php';

$q = $_GET['q'] ?? '';

if(strlen($q) < 2) exit;

$q = "%".$q."%";

$stmt = $conn->prepare("
SELECT rut,nombre,area 
FROM conductor
WHERE rut LIKE ? OR nombre LIKE ?
LIMIT 10
");

$stmt->bind_param("ss",$q,$q);
$stmt->execute();

$res = $stmt->get_result();

while($row = $res->fetch_assoc()){
?>

<div class="item-resultado"
     data-rut="<?= $row['rut'] ?>"
     data-nombre="<?= $row['nombre'] ?>"
     data-area="<?= $row['area'] ?>">
     
<strong><?= $row['nombre'] ?></strong><br>
<?= $row['rut'] ?>

</div>

<?php } ?>