
<?php

header("Content-Type: application/json; charset=utf-8");

// Verificar que exista una sesión iniciada
require_once "proteger_sesion.php";

// Solo recepción consulta estados de cuenta (RNF-01).
if ($_SESSION["rol"] !== "recepcionista") {

    http_response_code(403);

    echo json_encode([
        "exito" => false,
        "mensaje" => "No tienes permiso para consultar los pagos."
    ]);

    exit;
}

// Conectar con la base de datos
require_once "conexion.php";

try {

    // Consultar cotizaciones y sus pagos registrados
    $sql = "
        SELECT
            c.id_cotizacion,
            c.id_orden,
            c.total,

            COALESCE(SUM(p.monto_recibido), 0) AS total_pagado,

            GREATEST(
                c.total - COALESCE(SUM(p.monto_recibido), 0),
                0
            ) AS saldo_pendiente,

            CASE
                WHEN COALESCE(SUM(p.monto_recibido), 0) >= c.total
                THEN 'Pagado'
                ELSE 'Pendiente'
            END AS estado_pago,

            MAX(p.fecha_pago) AS fecha_ultimo_pago

        FROM cotizaciones c

        LEFT JOIN pago_cuenta p
            ON c.id_cotizacion = p.id_cotizacion

        GROUP BY
            c.id_cotizacion,
            c.id_orden,
            c.total

        ORDER BY c.id_cotizacion DESC
    ";

    $consulta = $conexion->prepare($sql);
    $consulta->execute();

    $pagos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los resultados
    echo json_encode([
        "exito" => true,
        "pagos" => $pagos
    ]);

} catch (PDOException $error) {

    http_response_code(500);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Error al consultar los pagos."
    ]);

}

?>
