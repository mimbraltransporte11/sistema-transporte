<?php

include "conexion.php";


/* =========================================================
   CONFIGURACIÓN EXCEL
========================================================= */

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");

header(
    "Content-Disposition: attachment; filename=equipos_cmpc_" .
    date("Y-m-d") .
    ".xls"
);

header("Pragma: no-cache");
header("Expires: 0");


/* =========================================================
   FUNCIÓN FORMATO MONETARIO
========================================================= */

function formatoPeso($valor){

    if($valor === null || $valor === ''){
        return '$0';
    }

    return '$' . number_format(
        (int)$valor,
        0,
        ',',
        '.'
    );

}


/* =========================================================
   RECIBIR FILTROS
========================================================= */

$buscar = trim($_GET['buscar'] ?? '');

$tipo = trim($_GET['tipo'] ?? '');

$marca = trim($_GET['marca'] ?? '');

$configuracion = trim(
    $_GET['configuracion'] ?? ''
);

$sub_configuracion = trim(
    $_GET['sub_configuracion'] ?? ''
);

$fechaDesde = trim(
    $_GET['fechaDesde'] ?? ''
);

$fechaHasta = trim(
    $_GET['fechaHasta'] ?? ''
);


/* =========================================================
   CONSULTA BASE
========================================================= */

$sql = "

SELECT

    patente,
    tipo,
    marca,
    modelo,
    numero_chasis,
    color,
    hp,
    anio,
    propietario,
    configuracion,
    sub_configuracion,
    tipo_eje,

    capacidad_estanque_1,
    capacidad_estanque_2,

    fecha_permiso,
    valor_permiso,
    municipalidad,

    fecha_soap,
    valor_soap,
    aseguradora,

    fecha_revision_tecnica,
    valor_revision_tecnica

FROM equipos

WHERE 1=1

";


/* =========================================================
   FILTRO BUSCAR PATENTE
========================================================= */

if($buscar !== ''){

    $buscarSQL = $conn->real_escape_string(
        strtoupper($buscar)
    );

    $sql .= "

        AND UPPER(patente)
        LIKE '%$buscarSQL%'

    ";

}


/* =========================================================
   FILTRO TIPO
========================================================= */

if($tipo !== ''){

    $tipoSQL = $conn->real_escape_string($tipo);

    $sql .= "

        AND tipo = '$tipoSQL'

    ";

}


/* =========================================================
   FILTRO MARCA
========================================================= */

if($marca !== ''){

    $marcaSQL = $conn->real_escape_string($marca);

    $sql .= "

        AND marca = '$marcaSQL'

    ";

}


/* =========================================================
   FILTRO CONFIGURACIÓN
========================================================= */

if($configuracion !== ''){

    $configSQL = $conn->real_escape_string(
        $configuracion
    );

    $sql .= "

        AND configuracion = '$configSQL'

    ";

}


/* =========================================================
   FILTRO SUB CONFIGURACIÓN
========================================================= */

if($sub_configuracion !== ''){

    $subSQL = $conn->real_escape_string(
        $sub_configuracion
    );

    $sql .= "

        AND sub_configuracion = '$subSQL'

    ";

}


/* =========================================================
   FILTRO AÑO DESDE
========================================================= */

if($fechaDesde !== ''){

    $desdeSQL = (int)$fechaDesde;

    $sql .= "

        AND anio >= $desdeSQL

    ";

}


/* =========================================================
   FILTRO AÑO HASTA
========================================================= */

if($fechaHasta !== ''){

    $hastaSQL = (int)$fechaHasta;

    $sql .= "

        AND anio <= $hastaSQL

    ";

}


/* =========================================================
   ORDEN
========================================================= */

$sql .= "

ORDER BY patente ASC

";


/* =========================================================
   EJECUTAR CONSULTA
========================================================= */

$resultado = $conn->query($sql);


/* =========================================================
   EXCEL
========================================================= */

echo "\xEF\xBB\xBF";

?>

<table border="1">

<tr>

    <th>Patente</th>
    <th>Tipo</th>
    <th>Marca</th>
    <th>Modelo</th>
    <th>N° Chasis</th>
    <th>Color</th>
    <th>HP</th>
    <th>Año</th>
    <th>Propietario</th>
    <th>Configuración</th>
    <th>Sub Configuración</th>
    <th>Tipo de Eje</th>

    <th>Estanque 1 (L)</th>
    <th>Estanque 2 (L)</th>
    <th>Capacidad Total Estanques (L)</th>

    <th>Fecha Permiso</th>
    <th>Valor Permiso</th>
    <th>Municipalidad</th>

    <th>Fecha SOAP</th>
    <th>Valor SOAP</th>
    <th>Aseguradora</th>

    <th>Fecha Revisión Técnica</th>
    <th>Valor Revisión Técnica</th>

</tr>


<?php while($e = $resultado->fetch_assoc()){ ?>


<?php

/* =========================================================
   ESTANQUES
========================================================= */

$estanque1 = !empty(
    $e['capacidad_estanque_1']
)
    ? (int)$e['capacidad_estanque_1']
    : 0;


$estanque2 = !empty(
    $e['capacidad_estanque_2']
)
    ? (int)$e['capacidad_estanque_2']
    : 0;


$totalEstanques =
    $estanque1 +
    $estanque2;


/* =========================================================
   VALORES MONETARIOS
========================================================= */

$valorPermiso = formatoPeso(
    $e['valor_permiso'] ?? 0
);


$valorSoap = formatoPeso(
    $e['valor_soap'] ?? 0
);


$valorRevision = formatoPeso(
    $e['valor_revision_tecnica'] ?? 0
);

?>


<tr>

    <!-- IDENTIFICACIÓN -->

    <td>
        <?=htmlspecialchars(
            $e['patente'] ?? ''
        )?>
    </td>


    <td>
        <?=htmlspecialchars(
            $e['tipo'] ?? ''
        )?>
    </td>


    <td>
        <?=htmlspecialchars(
            $e['marca'] ?? ''
        )?>
    </td>


    <td>
        <?=htmlspecialchars(
            $e['modelo'] ?? ''
        )?>
    </td>


    <td>
        <?=htmlspecialchars(
            $e['numero_chasis'] ?? ''
        )?>
    </td>


    <td>
        <?=htmlspecialchars(
            $e['color'] ?? ''
        )?>
    </td>


    <td>
        <?=htmlspecialchars(
            $e['hp'] ?? ''
        )?>
    </td>


    <td>
        <?=htmlspecialchars(
            $e['anio'] ?? ''
        )?>
    </td>


    <td>
        <?=htmlspecialchars(
            $e['propietario'] ?? ''
        )?>
    </td>


    <td>
        <?=htmlspecialchars(
            $e['configuracion'] ?? ''
        )?>
    </td>


    <td>
        <?=htmlspecialchars(
            $e['sub_configuracion'] ?? ''
        )?>
    </td>


    <td>
        <?=htmlspecialchars(
            $e['tipo_eje'] ?? ''
        )?>
    </td>


    <!-- ESTANQUES -->

    <td>
        <?=$estanque1?>
    </td>


    <td>
        <?=$estanque2?>
    </td>


    <td>
        <strong>
            <?=$totalEstanques?>
        </strong>
    </td>


    <!-- PERMISO -->

    <td>
        <?=htmlspecialchars(
            $e['fecha_permiso'] ?? ''
        )?>
    </td>


    <td>
        <?=$valorPermiso?>
    </td>


    <td>
        <?=htmlspecialchars(
            $e['municipalidad'] ?? ''
        )?>
    </td>


    <!-- SOAP -->

    <td>
        <?=htmlspecialchars(
            $e['fecha_soap'] ?? ''
        )?>
    </td>


    <td>
        <?=$valorSoap?>
    </td>


    <td>
        <?=htmlspecialchars(
            $e['aseguradora'] ?? ''
        )?>
    </td>


    <!-- REVISIÓN TÉCNICA -->

    <td>
        <?=htmlspecialchars(
            $e['fecha_revision_tecnica'] ?? ''
        )?>
    </td>


    <td>
        <?=$valorRevision?>
    </td>

</tr>


<?php } ?>

</table>

<?php

$conn->close();

exit;

?>