
<?php

session_start();

header("Content-Type: application/json; charset=utf-8");

// Verificar que el usuario haya iniciado sesión.

if (!isset($_SESSION["id_usuario"]) || !isset($_SESSION["rol"])) {

    http_response_code(401);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Debe iniciar sesión para consultar las cotizaciones."
    ]);

    exit;
}

// Conexión con la base de datos.

require_once "conexion.php";

try {

    // Consultar cotizaciones y sus detalles.

    $sql = "SELECT
                c.id_cotizacion,
                c.id_orden,
                os.codigo_orden,

                CONCAT_WS(
                    ' ',
                    NULLIF(cl.nombre, ''),
                    NULLIF(cl.primerapellido, ''),
                    NULLIF(cl.segundoapellido, '')
                ) AS cliente,

                CONCAT_WS(
                    ' ',
                    NULLIF(v.marca, ''),
                    NULLIF(v.modelo, '')
                ) AS vehiculo,

                v.placa,
                c.subtotal,
                c.impuesto,
                c.total,
                c.fecha_emision,

                dc.id_detalle,
                dc.descripcion,
                dc.tipo,
                dc.cantidad,
                dc.precio_unitario,
                dc.total_item

            FROM cotizaciones c

            INNER JOIN ordenes_servicio os
                ON c.id_orden = os.id_orden

            INNER JOIN vehiculos v
                ON os.id_vehiculo = v.id_vehiculo

            INNER JOIN clientes cl
                ON v.id_cliente = cl.id_cliente

            LEFT JOIN detalle_cotizacion dc
                ON c.id_cotizacion = dc.id_cotizacion

            ORDER BY c.id_cotizacion DESC, dc.id_detalle ASC";

    $consulta = $conexion->prepare($sql);

    $consulta->execute();

    $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

    // Organizar cotizaciones y sus detalles.

    $cotizaciones = [];

    foreach ($resultados as $fila) {

        $idCotizacion = $fila["id_cotizacion"];

        // Crear la cotización si todavía no existe.

        if (!isset($cotizaciones[$idCotizacion])) {

            $cotizaciones[$idCotizacion] = [

                "id_cotizacion" => $idCotizacion,
                "id_orden" => $fila["id_orden"],
                "codigo_orden" => $fila["codigo_orden"],
                "cliente" => $fila["cliente"],
                "vehiculo" => $fila["vehiculo"],
                "placa" => $fila["placa"],
                "subtotal" => $fila["subtotal"],
                "impuesto" => $fila["impuesto"],
                "total" => $fila["total"],
                "fecha_emision" => $fila["fecha_emision"],
                "detalles" => []

            ];

        }

        // Agregar los detalles de la cotización.

        if ($fila["id_detalle"] !== null) {

            $cotizaciones[$idCotizacion]["detalles"][] = [

                "id_detalle" => $fila["id_detalle"],
                "descripcion" => $fila["descripcion"],
                "tipo" => $fila["tipo"],
                "cantidad" => $fila["cantidad"],
                "precio_unitario" => $fila["precio_unitario"],
                "total_item" => $fila["total_item"]

            ];

        }

    }

    // Enviar resultado en formato JSON.

    echo json_encode([
        "exito" => true,
        "cotizaciones" => array_values($cotizaciones)
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $error) {

    http_response_code(500);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Error al consultar las cotizaciones."
    ]);

}

?>
