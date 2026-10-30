<?php

include 'db.php';

header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=flota_vehiculos.csv");

echo "\xEF\xBB\xBF";

$salida = fopen("php://output", "w");

/* ==========================
   CABECERAS
========================== */

fputcsv($salida, [

    "Patente",
    "Tipo",
    "Marca",
    "Modelo",
    "Número Chasis",
    "Color",
    "HP",
    "Configuración",
    "Sub Configuración",
    "Año",
    "Propietario",

    "Fecha Permiso",
    "Valor Permiso",
    "Municipalidad",

    "Fecha SOAP",
    "Valor SOAP",
    "Aseguradora",

    "Fecha Revisión Técnica",
    "Valor Revisión Técnica"

], ";");


/* ==========================
   DATOS
========================== */

$res = $conn->query("SELECT * FROM vehiculos");

while ($v = $res->fetch_assoc()) {

    fputcsv($salida, [

        $v['patente'],
        $v['tipo'],
        $v['marca'],
        $v['modelo'],
        $v['numero_chasis'],
        $v['color'],
        $v['hp'],
        $v['configuracion'],
        $v['sub_configuracion'],
        $v['anio'],
        $v['propietario'],

        $v['fecha_permiso'],
        $v['valor_permiso'],
        $v['municipalidad'],

        $v['fecha_soap'],
        $v['valor_soap'],
        $v['aseguradora'],

        $v['fecha_revision_tecnica'],
        $v['valor_revision_tecnica']

    ], ";");
}

fclose($salida);

exit;
?>