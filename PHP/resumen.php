<?php

header("Content-Type: application/json; charset=utf-8");

require_once "proteger_sesion.php";
require_once "conexion.php";

// =====================================
// RESUMEN DEL PANEL DE INICIO
// =====================================
//
// Contadores globales para ambos roles:
// - ordenes: total de órdenes registradas.
// - cotizaciones_pendientes: órdenes diagnosticadas sin cotización.
// - reparaciones_activas: órdenes diagnosticadas sin cotización
//   totalmente pagada (trabajo que sigue abierto).
// - pagos_pendientes: cotizaciones emitidas con saldo positivo.

try {

    $consulta = $conexion->query("
        SELECT
            (
                SELECT COUNT(*)
                FROM ordenes_servicio
            ) AS ordenes,

            (
                SELECT COUNT(*)
                FROM ordenes_servicio o

                INNER JOIN diagnostico_reparacion d
                    ON d.id_orden = o.id_orden

                WHERE NOT EXISTS (
                    SELECT 1
                    FROM cotizaciones c
                    WHERE c.id_orden = o.id_orden
                )
            ) AS cotizaciones_pendientes,

            (
                SELECT COUNT(*)
                FROM ordenes_servicio o

                INNER JOIN diagnostico_reparacion d
                    ON d.id_orden = o.id_orden

                WHERE NOT EXISTS (
                    SELECT 1
                    FROM cotizaciones c
                    WHERE c.id_orden = o.id_orden
                    AND c.total <= COALESCE((
                        SELECT SUM(p.monto_recibido)
                        FROM pago_cuenta p
                        WHERE p.id_cotizacion = c.id_cotizacion
                    ), 0)
                )
            ) AS reparaciones_activas,

            (
                SELECT COUNT(*)
                FROM cotizaciones c
                WHERE c.total > COALESCE((
                    SELECT SUM(p.monto_recibido)
                    FROM pago_cuenta p
                    WHERE p.id_cotizacion = c.id_cotizacion
                ), 0)
            ) AS pagos_pendientes
    ");

    $fila = $consulta->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "exito" => true,
        "resumen" => [
            "ordenes" => (int)$fila["ordenes"],
            "cotizaciones_pendientes" => (int)$fila["cotizaciones_pendientes"],
            "reparaciones_activas" => (int)$fila["reparaciones_activas"],
            "pagos_pendientes" => (int)$fila["pagos_pendientes"]
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $error) {

    error_log("resumen: " . $error->getMessage());

    http_response_code(500);

    echo json_encode([
        "exito" => false,
        "mensaje" => "No se pudo calcular el resumen del panel."
    ], JSON_UNESCAPED_UNICODE);

}

?>
