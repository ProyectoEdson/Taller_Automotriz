<?php

header("Content-Type: application/json; charset=utf-8");

require_once "proteger_sesion.php";
require_once "conexion.php";

// =====================================
// VERIFICAR ROL
// =====================================

if ($_SESSION["rol"] !== "mecanico") {

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

    $datos = json_decode(file_get_contents("php://input"), true);

    $idOrden = filter_var($datos["id_orden"] ?? null, FILTER_VALIDATE_INT);

    if (!$idOrden) {

        throw new Exception("Debes seleccionar una orden de trabajo.");

    }


    // =====================================
    // CONSULTAR ORDEN, CLIENTE Y VEHÍCULO
    // =====================================

    $consulta = $conexion->prepare("
        SELECT
            o.id_orden,
            o.codigo_orden,
            o.estado_orden,

            c.nombre,
            c.primerapellido,
            c.segundoapellido,

            v.marca,
            v.modelo,
            v.placa,
            v.`año` AS anio,
            v.kilometraje

        FROM ordenes_servicio o

        INNER JOIN vehiculos v
            ON v.id_vehiculo = o.id_vehiculo

        INNER JOIN clientes c
            ON c.id_cliente = v.id_cliente

        WHERE o.id_orden = :id_orden

        LIMIT 1
    ");

    $consulta->execute([
        ":id_orden" => $idOrden
    ]);

    $orden = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$orden) {

        throw new Exception("La orden de trabajo no existe.");

    }


    // =====================================
    // CONSULTAR DIAGNÓSTICO
    // =====================================

    $consulta = $conexion->prepare("
        SELECT
            id_diagnostico,
            descripcion_fall,
            tarea_realiz

        FROM diagnostico_reparacion

        WHERE id_orden = :id_orden

        LIMIT 1
    ");

    $consulta->execute([
        ":id_orden" => $idOrden
    ]);

    $diagnostico = $consulta->fetch(PDO::FETCH_ASSOC);

    $repuestos = [];

    if ($diagnostico) {

        $consulta = $conexion->prepare("
            SELECT
                COALESCE(
                    NULLIF(ru.descripcion, ''),
                    ir.nombre,
                    'Repuesto'
                ) AS descripcion,
                ru.cantidad,
                ru.precio_unitario

            FROM repuestos_utilizados ru

            LEFT JOIN inventario_repuesto ir
                ON ir.id_repuesto = ru.id_repuesto

            WHERE ru.id_diagnostico = :id_diagnostico

            ORDER BY ru.id_repuesto_util
        ");

        $consulta->execute([
            ":id_diagnostico" => $diagnostico["id_diagnostico"]
        ]);

        $repuestos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    }


    // =====================================
    // CONSTRUIR DATOS DE PRESENTACIÓN
    // =====================================

    $cliente = trim(implode(" ", array_filter([
        $orden["nombre"],
        $orden["primerapellido"],
        $orden["segundoapellido"]
    ], function ($valor) {
        return $valor !== null && trim($valor) !== "";
    })));

    echo json_encode([
        "exito" => true,
        "id_orden" => (int)$orden["id_orden"],
        "codigo_orden" => $orden["codigo_orden"],
        "estado_orden" => $orden["estado_orden"],
        "cliente" => $cliente,
        "vehiculo" => trim($orden["marca"] . " " . $orden["modelo"]),
        "placa" => $orden["placa"],
        "anio" => $orden["anio"],
        "kilometraje" => $orden["kilometraje"],
        "diagnostico" => $diagnostico["descripcion_fall"] ?? "",
        "reparaciones" => $diagnostico["tarea_realiz"] ?? "",
        "repuestos" => $repuestos
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $error) {

    http_response_code(500);

    echo json_encode([
        "exito" => false,
        "mensaje" => "No se pudo consultar la información de la orden."
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $error) {

    http_response_code(400);

    echo json_encode([
        "exito" => false,
        "mensaje" => $error->getMessage()
    ], JSON_UNESCAPED_UNICODE);

}

?>
