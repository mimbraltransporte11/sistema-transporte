<?php
include 'db.php';
date_default_timezone_set('America/Santiago');

/* ===== FUNCIONES ===== */

function sumarDiasHabiles($fechaInicio, $dias){

    if(!$dias || $dias <= 0) return null;

    $fecha = $fechaInicio;

    while($dias > 0){

        $fecha = date('Y-m-d', strtotime("+1 day", strtotime($fecha)));
        $diaSemana = date('N', strtotime($fecha));

        if($diaSemana < 6){
            $dias--;
        }
    }

    return $fecha;
}

/* ✅ NUEVA FUNCIÓN LICENCIA */

function sumarDiasCorridos($fechaInicio, $dias){

    if(!$dias || $dias <= 0) return null;

    return date('Y-m-d', strtotime("+$dias day", strtotime($fechaInicio)));
}

/* ===== DATOS ===== */

$id       = $_POST['id'] ?? null;
$nombre   = $_POST['nombre'];
$area     = $_POST['area'];
$telefono = $_POST['telefono'];
$estado   = $_POST['estado'];
$inicioVacacionesPOST = $_POST['inicio_vacaciones'] ?? null;
$inicioLicenciaPOST   = $_POST['inicio_licencia'] ?? null;


$detalleVacaciones = (!empty($_POST['detalle_vacaciones']))
    ? intval($_POST['detalle_vacaciones'])
    : null;

$detalleLicencia = (!empty($_POST['detalle_licencia']))
    ? intval($_POST['detalle_licencia'])
    : null;

/* ===== VARIABLES ===== */

$inicioVacaciones = "NULL";
$finVacaciones    = "NULL";

$inicioLicencia   = "NULL";
$finLicencia      = "NULL";

/* ===== VACACIONES ===== */

if($estado == 'Vacaciones'){

    $fechaInicio = $inicioVacacionesPOST ?: date('Y-m-d');

    $inicioVacaciones = "'$fechaInicio'";

    $finCalculado = sumarDiasHabiles($fechaInicio, $detalleVacaciones);
    $finVacaciones = $finCalculado ? "'$finCalculado'" : "NULL";

    $detalleLicencia = "NULL";
}


/* ===== LICENCIA ===== */

elseif($estado == 'Licencia'){

    $fechaInicio = $inicioLicenciaPOST ?: date('Y-m-d');

    $inicioLicencia = "'$fechaInicio'";

    $finCalculado = sumarDiasCorridos($fechaInicio, $detalleLicencia);

    $finLicencia = $finCalculado ? "'$finCalculado'" : "NULL";

    $detalleVacaciones = "NULL";
}


/* ===== DISPONIBLE ===== */

else{

    $detalleVacaciones = "NULL";
    $detalleLicencia   = "NULL";
}

/* ===== NORMALIZACIÓN ===== */

$detalleVacacionesSQL = is_numeric($detalleVacaciones) ? $detalleVacaciones : "NULL";
$detalleLicenciaSQL   = is_numeric($detalleLicencia)   ? $detalleLicencia   : "NULL";

/* ===== UPDATE ===== */

if($id){

    $sql = "
    UPDATE informacion SET
      nombre='$nombre',
      area='$area',
      telefono='$telefono',
      estado='$estado',

      detalle_vacaciones=$detalleVacacionesSQL,
      inicio_vacaciones=$inicioVacaciones,
      fin_vacaciones=$finVacaciones,

      detalle_licencia=$detalleLicenciaSQL,
      inicio_licencia=$inicioLicencia,
      fin_licencia=$finLicencia

    WHERE id=$id
    ";
}

/* ===== INSERT ===== */

else{

    $sql = "
    INSERT INTO informacion
    (nombre,area,telefono,estado,
     detalle_vacaciones,inicio_vacaciones,fin_vacaciones,
     detalle_licencia,inicio_licencia,fin_licencia)

    VALUES
    ('$nombre','$area','$telefono','$estado',
     $detalleVacacionesSQL,$inicioVacaciones,$finVacaciones,
     $detalleLicenciaSQL,$inicioLicencia,$finLicencia)
    ";
}

$conn->query($sql);

header("Location: index.php");
