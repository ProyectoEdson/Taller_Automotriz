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
        "mensaje" => "No tienes permiso para consultar las órdenes."
    ]);

    exit;
}


// =====================================
// VERIFICAR MÉTODO GET
// =====================================

if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    http_response_code(405);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Método de solicitud no permitido."
    ]);

    exit;
}


try {

    $consulta = $conexion->query("
        SELECT
            o.id_orden,
            o.codigo_orden,
            o.fecha_ingreso,
            o.estado_orden,

            c.nombre,
            c.primerapellido,
            c.segundoapellido,

            v.marca,
            v.modelo,
            v.placa,
            v.`año` AS anio,
            v.kilometraje,

            d.id_diagnostico

        FROM ordenes_servicio o

        INNER JOIN vehiculos v
            ON v.id_vehiculo = o.id_vehiculo

        INNER JOIN clientes c
            ON c.id_cliente = v.id_cliente

        LEFT JOIN diagnostico_reparacion d
            ON d.id_orden = o.id_orden

        ORDER BY o.fecha_ingreso DESC, o.id_orden DESC
    ");

    $ordenes = [];

    foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $fila) {

        $nombreCliente = trim(implode(" ", array_filter([
            $fila["nombre"],
            $fila["primerapellido"],
            $fila["segundoapellido"]
        ], function ($valor) {
            return $valor !== null && trim($valor) !== "";
        })));

        $ordenes[] = [
            "id_orden" => (int)$fila["id_orden"],
            "codigo_orden" => $fila["codigo_orden"],
            "estado_orden" => $fila["estado_orden"],
            "fecha_ingreso" => $fila["fecha_ingreso"],
            "cliente" => $nombreCliente,
            "vehiculo" => trim($fila["marca"] . " " . $fila["modelo"]),
            "placa" => $fila["placa"],
            "anio" => $fila["anio"],
            "kilometraje" => $fila["kilometraje"],
            "tiene_diagnostico" => $fila["id_diagnostico"] !== null
        ];

    }

    echo json_encode([
        "exito" => true,
        "ordenes" => $ordenes
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $error) {

    http_response_code(500);

    echo json_encode([
        "exito" => false,
        "mensaje" => "No se pudieron consultar las órdenes de trabajo."
    ], JSON_UNESCAPED_UNICODE);

}

?>
