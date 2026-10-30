<?php include 'db.php'; ?>

<form method="POST">

<input name="ppu" placeholder="PPU">

<select name="tipo">
<option>TRACTO</option>
<option>CAMION</option>
<option>RAMPLA</option>
<option>CAMA BAJA</option>
<option>BATEA</option>
<option>CORTINA</option>
<option>ESTANQUE</option>
<option>CHASIS</option>
<option>CARRO</option>
</select>

<select name="configuracion">
<option>DOBLE PUENTE</option>
<option>SENCILLO</option>
<option>NW</option>
<option>TRIPLE</option>
<option>1+1</option>
<option>3 DIV</option>
<option>2 DIV</option>
<option>1 DIV</option>
<option>SIN DIV</option>
<option>CHASIS</option>
<option>CARRO</option>
</select>

<input type="date" name="revision_tecnica">

<button name="guardar">Guardar</button>
</form>

<?php
if(isset($_POST['guardar'])){
$conn->query("INSERT INTO equipos (ppu,tipo,configuracion,revision_tecnica)
VALUES ('$_POST[ppu]','$_POST[tipo]','$_POST[configuracion]','$_POST[revision_tecnica]')");

header("Location:index.php");
}
?>