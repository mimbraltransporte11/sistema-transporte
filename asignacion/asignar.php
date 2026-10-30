<?php include 'db.php'; ?>

<form method="POST">

<select name="conductor">
<?php
$c = $conn->query("SELECT * FROM conductores");
while($row=$c->fetch_assoc()){
echo "<option value='$row[id]'>$row[nombre]</option>";
}
?>
</select>

<select name="equipo">
<?php
$e = $conn->query("SELECT * FROM equipos");
while($row=$e->fetch_assoc()){
echo "<option value='$row[id]'>$row[ppu] - $row[tipo]</option>";
}
?>
</select>

<button name="asignar">Asignar</button>

</form>

<?php
if(isset($_POST['asignar'])){

$equipo_id = $_POST['equipo'];
$conductor_id = $_POST['conductor'];

// obtener tipo
$r = $conn->query("SELECT tipo FROM equipos WHERE id=$equipo_id")->fetch_assoc();

$tipo_equipo = ($r['tipo']=='TRACTO') ? 'TRACTO' : 'ACOPLADO';

// cerrar asignación anterior del equipo
$conn->query("
UPDATE asignaciones 
SET fecha_fin=NOW() 
WHERE equipo_id=$equipo_id AND fecha_fin IS NULL
");

// cerrar asignación anterior del conductor para ese tipo
$conn->query("
UPDATE asignaciones 
SET fecha_fin=NOW() 
WHERE conductor_id=$conductor_id 
AND tipo_equipo='$tipo_equipo'
AND fecha_fin IS NULL
");

// crear nueva
$conn->query("
INSERT INTO asignaciones (conductor_id,equipo_id,tipo_equipo,fecha_inicio)
VALUES ($conductor_id,$equipo_id,'$tipo_equipo',NOW())
");

header("Location:index.php");
}
?>