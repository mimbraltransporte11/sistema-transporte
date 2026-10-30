<?php
include 'db.php';

$patente = $_GET['patente'];
$res = $conn->query("SELECT * FROM vehiculos WHERE patente='$patente'");
$v = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Vehículo</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h1>✏️ Editar Vehículo</h1>

<form action="actualizar.php" method="POST">
  <input type="hidden" name="patente" value="<?= $v['patente'] ?>">

  <input name="tipo" value="<?= $v['tipo'] ?>" required>
  <input name="marca" value="<?= $v['marca'] ?>" required>
  <input name="modelo" value="<?= $v['modelo'] ?>" required>
  <input type="number" name="anio" value="<?= $v['anio'] ?>" required>

  <label>Fecha Permiso</label>
  <input type="date" name="fecha_permiso" value="<?= $v['fecha_permiso'] ?>" required>
  <input type="number" name="valor_permiso" value="<?= $v['valor_permiso'] ?>" required>
  <input name="municipalidad" value="<?= $v['municipalidad'] ?>" required>

  <label>Fecha SOAP</label>
  <input type="date" name="fecha_soap" value="<?= $v['fecha_soap'] ?>" required>
  <input type="number" name="valor_soap" value="<?= $v['valor_soap'] ?>" required>
  <input name="aseguradora" value="<?= $v['aseguradora'] ?>" required>

  <label>Fecha Revisión Técnica</label>
  <input type="date" name="fecha_revision_tecnica" value="<?= $v['fecha_revision_tecnica'] ?>" required>

  <button>Actualizar</button>
  <a href="index.php">Cancelar</a>
</form>

</body>
</html>
