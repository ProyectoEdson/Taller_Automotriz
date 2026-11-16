<?php

header("Content-Type: application/json; charset=utf-8");

require_once "proteger_sesion.php";
require_once "conexion.php";


// =====================================
// VERIFICAR ROL
// =====================================

if ($_SESSION["rol"] !== "recepcionista") {

    http_response_code(403);

    echo json_encode([
        "exito" => false,
        "mensaje" => "No tienes permiso para consultar esta orden."
    ]);

    exit;
}


// =====================================
// VERIFICAR MÉTODO POST
// =====================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Método de solicitud no permitido."
    ]);

    exit;
}


try {

    // =====================================
    // 1. RECIBIR CÓDIGO DE ORDEN
    // =====================================

    $datos = json_decode(file_get_contents("php://input"), true);

    $codigoOrden = trim($datos["codigo_orden"] ?? "");


    if ($codigoOrden === "") {

        throw new Exception(
            "Debes escribir el código de la orden."
        );

    }


    // =====================================
    // 2. CONSULTAR ORDEN, CLIENTE Y VEHÍCULO
    // =====================================

    $consulta = $conexion->prepare("
        SELECT

            o.id_orden,
            o.codigo_orden,

            c.nombre,
            c.primerapellido,
            c.segundoapellido,
            c.telefono,
            c.email,

            v.marca,
            v.modelo,
            v.placa,
            v.`año` AS anio,
            v.kilometraje

        FROM ordenes_servicio o

        INNER JOIN vehiculos v
            ON o.id_vehiculo = v.id_vehiculo

        INNER JOIN clientes c
            ON v.id_cliente = c.id_cliente

        WHERE o.codigo_orden = :codigo

        LIMIT 1
    ");


    $consulta->execute([
        ":codigo" => $codigoOrden
    ]);


    $orden = $consulta->fetch(PDO::FETCH_ASSOC);


    if (!$orden) {

        throw new Exception(
            "No se encontró una orden de trabajo con ese código."
        );

    }


    // =====================================
    // 3. CONSTRUIR NOMBRE DEL CLIENTE
    // =====================================

    $cliente = trim(implode(" ", array_filter([
    $orden["nombre"],
    $orden["primerapellido"],
    $orden["segundoapellido"]
], function ($valor) {
    return $valor !== null && trim($valor) !== "";
})));


    // =====================================
    // 4. CONSTRUIR NOMBRE DEL VEHÍCULO
    // =====================================

    $vehiculo = trim(
        $orden["marca"] . " " . $orden["modelo"]
    );


    // =====================================
    // 5. CONSULTAR DIAGNÓSTICO
    // =====================================

    $consultaDiagnostico = $conexion->prepare("
        SELECT
            id_diagnostico,
            descripcion_fall,
            tarea_realiz

        FROM diagnostico_reparacion

        WHERE id_orden = :id_orden

        LIMIT 1
    ");


    $consultaDiagnostico->execute([
        ":id_orden" => $orden["id_orden"]
    ]);


    $diagnostico = $consultaDiagnostico->fetch(PDO::FETCH_ASSOC);


    // =====================================
    // 6. PREPARAR DETALLES DE COTIZACIÓN
    // =====================================

    $detalles = [];


    // Agregar diagnóstico.

    if (
        $diagnostico &&
        trim($diagnostico["descripcion_fall"] ?? "") !== ""
    ) {

        $descripcion = "Diagnóstico: " .
            trim($diagnostico["descripcion_fall"]);


        $detalles[] = [

            "descripcion" => mb_substr($descripcion, 0, 150),

            "tipo" => "Servicio",

            "cantidad" => 1,

            "precio_unitario" => "0.00"

        ];

    }


    // Agregar reparaciones.

    if (
        $diagnostico &&
        trim($diagnostico["tarea_realiz"] ?? "") !== ""
    ) {

        $descripcion = "Reparación: " .
            trim($diagnostico["tarea_realiz"]);


        $detalles[] = [

            "descripcion" => mb_substr($descripcion, 0, 150),

            "tipo" => "Servicio",

            "cantidad" => 1,

            "precio_unitario" => "0.00"

        ];

    }


    // =====================================
    // 7. CONSULTAR REPUESTOS UTILIZADOS
    // =====================================

    if ($diagnostico) {

        $columnas = $conexion
            ->query("SHOW COLUMNS FROM repuestos_utilizados")
            ->fetchAll(PDO::FETCH_COLUMN);


        if (in_array("descripcion", $columnas, true)) {

            $columnaDescripcion = "descripcion";

        } elseif (in_array("descripción", $columnas, true)) {

            $columnaDescripcion = "descripción";

        } else {

            $columnaDescripcion = null;

        }


        // Determinar cómo obtener el nombre del repuesto.

        if ($columnaDescripcion !== null) {

            $expresionDescripcion = "
                COALESCE(
                    NULLIF(ru.`$columnaDescripcion`, ''),
                    ir.nombre,
                    'Repuesto'
                )
            ";

        } else {

            $expresionDescripcion = "
                COALESCE(ir.nombre, 'Repuesto')
            ";

        }


        // Consultar repuestos.

        $sqlRepuestos = "
            SELECT

                $expresionDescripcion AS descripcion,

                ru.cantidad,

                ru.precio_unitario

            FROM repuestos_utilizados ru

            LEFT JOIN inventario_repuesto ir
                ON ru.id_repuesto = ir.id_repuesto

            WHERE ru.id_diagnostico = :id_diagnostico

            ORDER BY ru.id_repuesto_util
        ";


        $consultaRepuestos = $conexion->prepare($sqlRepuestos);


        $consultaRepuestos->execute([
            ":id_diagnostico" => $diagnostico["id_diagnostico"]
        ]);


        $repuestos = $consultaRepuestos->fetchAll(PDO::FETCH_ASSOC);


        // Incorporar repuestos a la cotización.

        foreach ($repuestos as $repuesto) {

            $detalles[] = [

                "descripcion" => mb_substr(
                    trim($repuesto["descripcion"]),
                    0,
                    150
                ),

                "tipo" => "Repuesto",

                "cantidad" => (int)$repuesto["cantidad"],

                "precio_unitario" => $repuesto["precio_unitario"]

            ];

        }

    }


    // =====================================
    // 8. DEVOLVER INFORMACIÓN
    // =====================================

    echo json_encode([

        "exito" => true,

        "codigo_orden" => $orden["codigo_orden"],

        // Información del cliente.
        "cliente" => $cliente,
        "nombre" => $orden["nombre"],
        "primerapellido" => $orden["primerapellido"],
        "segundoapellido" => $orden["segundoapellido"],
        "telefono" => $orden["telefono"],
        "email" => $orden["email"],

        // Información del vehículo.
        "vehiculo" => $vehiculo,
        "marca" => $orden["marca"],
        "modelo" => $orden["modelo"],
        "placa" => $orden["placa"],
        "anio" => $orden["anio"],
        "kilometraje" => $orden["kilometraje"],

        // Detalles de la cotización.
        "detalles" => $detalles

    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $error) {

    http_response_code(500);

    echo json_encode([

        "exito" => false,

        "mensaje" => "Ocurrió un error al consultar la información de la orden."

    ], JSON_UNESCAPED_UNICODE);


} catch (Exception $error) {

    http_response_code(400);

    echo json_encode([

        "exito" => false,

        "mensaje" => $error->getMessage()

    ], JSON_UNESCAPED_UNICODE);

}

?>