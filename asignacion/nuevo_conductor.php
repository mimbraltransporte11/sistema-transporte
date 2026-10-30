<?php include 'db.php'; ?>

<form method="POST">
<input name="rut" placeholder="RUT">
<input name="nombre" placeholder="Nombre">
<input name="area" placeholder="Área">
<button name="guardar">Guardar</button>
</form>

<?php
if(isset($_POST['guardar'])){
$conn->query("INSERT INTO conductores (rut,nombre,area)
VALUES ('$_POST[rut]','$_POST[nombre]','$_POST[area]')");

header("Location:index.php");
}
?>