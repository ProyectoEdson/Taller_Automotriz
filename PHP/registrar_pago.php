
<?php

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "conexion.php";

// Verificar sesión

if (!isset($_SESSION["id_usuario"]) || !isset($_SESSION["rol"])) {

    http_response_code(401);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Debe iniciar sesión para registrar pagos."
    ]);

    exit;
}

// Solo recepción registra pagos (RNF-01).

if ($_SESSION["rol"] !== "recepcionista") {

    http_response_code(403);

    echo json_encode([
        "exito" => false,
        "mensaje" => "No tienes permiso para registrar pagos."
    ]);

    exit;
}

// Verificar método POST

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Método de solicitud no permitido."
    ]);

    exit;
}

// Recibir datos JSON

$datos = json_decode(file_get_contents("php://input"), true);

// Validar datos recibidos

if (
    !$datos ||
    !isset($datos["id_orden"]) ||
    !isset($datos["id_cotizacion"]) ||
    !isset($datos["monto_recibido"]) ||
    !isset($datos["metodo_pago"])
) {

    http_response_code(400);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Faltan datos para registrar el pago."
    ]);

    exit;
}

// Obtener valores

$idOrden = filter_var($datos["id_orden"], FILTER_VALIDATE_INT);

$idCotizacion = filter_var(
    $datos["id_cotizacion"],
    FILTER_VALIDATE_INT
);

$montoRecibido = $datos["monto_recibido"];

$metodoPago = trim($datos["metodo_pago"]);

// Validaciones básicas

$metodosPermitidos = [
    "Efectivo",
    "Tarjeta",
    "Transferencia",
    "Yappy"
];

if (
    !$idOrden ||
    !$idCotizacion ||
    !is_numeric($montoRecibido) ||
    !is_finite((float)$montoRecibido) ||
    (float)$montoRecibido <= 0 ||
    !in_array($metodoPago, $metodosPermitidos, true)
) {

    http_response_code(400);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Los datos del pago no son válidos."
    ]);

    exit;
}

try {

    $conexion->beginTransaction();

    // Consultar cotización y verificar que pertenezca a la orden

    $sqlCotizacion = "SELECT
                        id_cotizacion,
                        id_orden,
                        total
                      FROM cotizaciones
                      WHERE id_cotizacion = :id_cotizacion
                      AND id_orden = :id_orden
                      FOR UPDATE";

    $consultaCotizacion = $conexion->prepare($sqlCotizacion);

    $consultaCotizacion->execute([
        ":id_cotizacion" => $idCotizacion,
        ":id_orden" => $idOrden
    ]);

    $cotizacion = $consultaCotizacion->fetch(PDO::FETCH_ASSOC);

    if (!$cotizacion) {

        throw new Exception(
            "La cotización no existe o no corresponde a la orden seleccionada."
        );

    }

    // Obtener el total de la cotización

    $montoPagar = (float)$cotizacion["total"];

    // Consultar pagos anteriores

    $sqlPagos = "SELECT
                    COALESCE(SUM(monto_recibido), 0) AS total_recibido
                 FROM pago_cuenta
                 WHERE id_cotizacion = :id_cotizacion";

    $consultaPagos = $conexion->prepare($sqlPagos);

    $consultaPagos->execute([
        ":id_cotizacion" => $idCotizacion
    ]);

    $pagosAnteriores = $consultaPagos->fetch(PDO::FETCH_ASSOC);

    $totalRecibido = (float)$pagosAnteriores["total_recibido"];

    // Calcular saldo pendiente

    $saldoPendiente = round($montoPagar - $totalRecibido, 2);

    $montoRecibido = round((float)$montoRecibido, 2);

    if ($saldoPendiente <= 0) {

        throw new Exception(
            "Esta cotización ya se encuentra pagada."
        );

    }

    if ($montoRecibido > $saldoPendiente) {

        throw new Exception(
            "El monto recibido supera el saldo pendiente."
        );

    }

    // Registrar pago

    $sqlPago = "INSERT INTO pago_cuenta
                (
                    id_orden,
                    id_cotizacion,
                    monto_pagar,
                    monto_recibido,
                    metodo_pago,
                    fecha_pago
                )
                VALUES
                (
                    :id_orden,
                    :id_cotizacion,
                    :monto_pagar,
                    :monto_recibido,
                    :metodo_pago,
                    NOW()
                )";

    $consultaPago = $conexion->prepare($sqlPago);

    $consultaPago->execute([

        ":id_orden" => $idOrden,
        ":id_cotizacion" => $idCotizacion,
        ":monto_pagar" => $montoPagar,
        ":monto_recibido" => $montoRecibido,
        ":metodo_pago" => $metodoPago

    ]);

    // Capturar el id antes del commit: COMMIT reinicia lastInsertId().

    $idPago = (int)$conexion->lastInsertId();

    // Calcular saldo restante

    $nuevoSaldo = round($saldoPendiente - $montoRecibido, 2);

    // Confirmar transacción

    $conexion->commit();

    echo json_encode([

        "exito" => true,
        "mensaje" => $nuevoSaldo <= 0
            ? "Pago completado correctamente."
            : "Abono registrado correctamente.",
        "id_pago" => $idPago,
        "monto_pagar" => $montoPagar,
        "monto_recibido" => $montoRecibido,
        "saldo_pendiente" => $nuevoSaldo,
        "estado_pago" => $nuevoSaldo <= 0
            ? "Pagado"
            : "Pendiente"

    ]);

} catch (PDOException $error) {

    if ($conexion->inTransaction()) {

        $conexion->rollBack();

    }

    error_log("registrar_pago: " . $error->getMessage());

    http_response_code(500);

    echo json_encode([

        "exito" => false,
        "mensaje" => "No se pudo registrar el pago por un error interno."

    ]);

} catch (Exception $error) {

    if ($conexion->inTransaction()) {

        $conexion->rollBack();

    }

    http_response_code(409);

    echo json_encode([

        "exito" => false,
        "mensaje" => $error->getMessage()

    ]);

}

?>
