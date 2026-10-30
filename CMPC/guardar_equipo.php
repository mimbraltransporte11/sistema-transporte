<?php

include "conexion.php";


if(isset($_POST['patente'])){


    /* ============================
       DATOS GENERALES
    ============================ */

    $patente =
        strtoupper(
            trim($_POST['patente'])
        );

    $conductor_id =
        !empty($_POST['conductor_id'])
            ? (int)$_POST['conductor_id']
            : null;

    $tipo =
        $_POST['tipo'] ?? '';

    $marca =
        $_POST['marca'] ?? '';

    $modelo =
        $_POST['modelo'] ?? '';

    $numero_chasis =
        $_POST['numero_chasis'] ?? '';

    $color =
        $_POST['color'] ?? '';

    $hp =
        !empty($_POST['hp'])
            ? (int)$_POST['hp']
            : null;

    $anio =
        !empty($_POST['anio'])
            ? (int)$_POST['anio']
            : null;

    $propietario =
        $_POST['propietario'] ?? '';

    $configuracion =
        $_POST['configuracion'] ?? '';

    $sub_configuracion =
        $_POST['sub_configuracion'] ?? '';

   
$tipo_eje =
    !empty($_POST['tipo_eje'])
        ? trim($_POST['tipo_eje'])
        : '';



    /* ============================
       ESTANQUES DE PETRÓLEO
    ============================ */

    $capacidad_estanque_1 =
        !empty($_POST['capacidad_estanque_1'])
            ? (int)$_POST['capacidad_estanque_1']
            : null;

    $capacidad_estanque_2 =
        !empty($_POST['capacidad_estanque_2'])
            ? (int)$_POST['capacidad_estanque_2']
            : null;


    if($tipo !== 'TRACTOCAMION'){

        $capacidad_estanque_1 = null;
        $capacidad_estanque_2 = null;

    }


    /* ============================
       PERMISO DE CIRCULACIÓN
    ============================ */

    $fecha_permiso =
        !empty($_POST['fecha_permiso'])
            ? $_POST['fecha_permiso']
            : null;

    $valor_permiso =
        !empty($_POST['valor_permiso'])
            ? (int)$_POST['valor_permiso']
            : null;

    $municipalidad =
        !empty($_POST['municipalidad'])
            ? trim($_POST['municipalidad'])
            : null;


    /* ============================
       SOAP
    ============================ */

    $fecha_soap =
        !empty($_POST['fecha_soap'])
            ? $_POST['fecha_soap']
            : null;

    $valor_soap =
        !empty($_POST['valor_soap'])
            ? (int)$_POST['valor_soap']
            : null;

    $aseguradora =
        !empty($_POST['aseguradora'])
            ? trim($_POST['aseguradora'])
            : null;


    /* ============================
       REVISIÓN TÉCNICA
    ============================ */

    $fecha_revision_tecnica =
        !empty($_POST['fecha_revision_tecnica'])
            ? $_POST['fecha_revision_tecnica']
            : null;

    $valor_revision_tecnica =
        !empty($_POST['valor_revision_tecnica'])
            ? (int)$_POST['valor_revision_tecnica']
            : null;


    /* ============================
       VALIDAR PATENTE EXISTENTE
    ============================ */

    $buscar = $conn->prepare("
        SELECT patente
        FROM equipos
        WHERE patente=?
    ");

    $buscar->bind_param(
        "s",
        $patente
    );

    $buscar->execute();

    $resultado =
        $buscar->get_result();


    if($resultado->num_rows > 0){

        echo "
        <script>

        alert('La patente ya existe');

        window.location='index.php';

        </script>
        ";

        exit;

    }

    $buscar->close();


    /* ============================
       INSERTAR EQUIPO
    ============================ */

    $sql = "

    INSERT INTO equipos
    (

        patente,
        conductor_id,

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

    )

    VALUES
    (

        ?,
        ?,

        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,

        ?,
        ?,
        ?,

        ?,
        ?,

        ?,
        ?,
        ?,

        ?,
        ?,
        ?,

        ?,
        ?

    )

    ";


    $stmt =
        $conn->prepare($sql);


    /*
    23 VARIABLES

    s = string
    i = integer
    */

    $stmt->bind_param(

        "sisssssiissssii" .
        "sis" .
        "sis" .
        "si",

        $patente,
        $conductor_id,

        $tipo,
        $marca,
        $modelo,
        $numero_chasis,
        $color,

        $hp,
        $anio,

        $propietario,

        $configuracion,
        $sub_configuracion,
        $tipo_eje,

        $capacidad_estanque_1,
        $capacidad_estanque_2,

        $fecha_permiso,
        $valor_permiso,
        $municipalidad,

        $fecha_soap,
        $valor_soap,
        $aseguradora,

        $fecha_revision_tecnica,
        $valor_revision_tecnica

    );


    $stmt->execute();


    $stmt->close();

}


header("Location:index.php");

exit;

?>