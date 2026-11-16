
<?php

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "conexion.php";

// Verificar que el usuario haya iniciado sesión como recepcionista.

if (
    !isset($_SESSION["id_usuario"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "recepcionista"
) {
    http_response_code(403);

    echo json_encode([
        "exito" => false,
        "mensaje" => "No tienes permiso para realizar esta operación."
    ]);

    exit;
}

// Verificar el método de solicitud.

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Método de solicitud no permitido."
    ]);

    exit;
}

// Recibir los datos enviados desde JavaScript.

$datos = json_decode(file_get_contents("php://input"), true);

// Verificar los datos recibidos.

if (
    !$datos ||
    !isset($datos["codigo_orden"]) ||
    !isset($datos["detalles"])
) {

    http_response_code(400);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Faltan datos para registrar la cotización."
    ]);

    exit;
}

$codigoOrden = trim($datos["codigo_orden"]);
$detalles = $datos["detalles"];

// Validar la información.

if (
    $codigoOrden === "" ||
    !is_array($detalles) ||
    count($detalles) === 0
) {

    http_response_code(400);

    echo json_encode([
        "exito" => false,
        "mensaje" => "La orden o los detalles no son válidos."
    ]);

    exit;
}

try {

    // Iniciar transacción.

    $conexion->beginTransaction();

    // Buscar la orden mediante su código.

    $sqlOrden = "SELECT id_orden
                 FROM ORDENES_SERVICIO
                 WHERE codigo_orden = :codigo_orden
                 LIMIT 1";

    $consultaOrden = $conexion->prepare($sqlOrden);

    $consultaOrden->execute([
        ":codigo_orden" => $codigoOrden
    ]);

    $orden = $consultaOrden->fetch(PDO::FETCH_ASSOC);

    if (!$orden) {
        throw new Exception(
            "No se encontró una orden de trabajo con ese código."
        );
    }

    $idOrden = $orden["id_orden"];

    // Validar y calcular los detalles.

    $subtotal = 0;

    foreach ($detalles as $detalle) {

        $descripcion = trim($detalle["descripcion"] ?? "");

        $tipo = $detalle["tipo"] ?? "";

        $cantidad = filter_var(
            $detalle["cantidad"] ?? null,
            FILTER_VALIDATE_INT
        );

        $precio = $detalle["precio_unitario"] ?? null;

        if (
            $descripcion === "" ||
            strlen($descripcion) > 150 ||
            !in_array($tipo, ["Servicio", "Repuesto"], true) ||
            !$cantidad ||
            !is_numeric($precio) ||
            !is_finite((float)$precio) ||
            (float)$precio < 0
        ) {

            throw new Exception(
                "Uno de los detalles contiene datos inválidos."
            );

        }

        $subtotal += $cantidad * (float)$precio;

    }

    // Calcular impuesto y total.

    $subtotal = round($subtotal, 2);

    $impuesto = round($subtotal * 0.07, 2);

    $total = round($subtotal + $impuesto, 2);

    // Registrar la cotización.

    $sqlCotizacion = "INSERT INTO COTIZACIONES
        (id_orden, subtotal, impuesto, total)
        VALUES
        (:id_orden, :subtotal, :impuesto, :total)";

    $consultaCotizacion = $conexion->prepare($sqlCotizacion);

    $consultaCotizacion->execute([

        ":id_orden" => $idOrden,
        ":subtotal" => $subtotal,
        ":impuesto" => $impuesto,
        ":total" => $total

    ]);

    // Obtener el ID de la cotización.

    $idCotizacion = $conexion->lastInsertId();

    // Preparar el registro de los detalles.

    $sqlDetalle = "INSERT INTO DETALLE_COTIZACION
        (
            id_cotizacion,
            descripcion,
            tipo,
            cantidad,
            precio_unitario,
            total_item
        )
        VALUES
        (
            :id_cotizacion,
            :descripcion,
            :tipo,
            :cantidad,
            :precio_unitario,
            :total_item
        )";

    $consultaDetalle = $conexion->prepare($sqlDetalle);

    // Guardar cada detalle.

    foreach ($detalles as $detalle) {

        $cantidad = (int)$detalle["cantidad"];

        $precio = (float)$detalle["precio_unitario"];

        $totalItem = round($cantidad * $precio, 2);

        $consultaDetalle->execute([

            ":id_cotizacion" => $idCotizacion,
            ":descripcion" => trim($detalle["descripcion"]),
            ":tipo" => $detalle["tipo"],
            ":cantidad" => $cantidad,
            ":precio_unitario" => $precio,
            ":total_item" => $totalItem

        ]);

    }

    // Confirmar los registros.

    $conexion->commit();

    echo json_encode([

        "exito" => true,
        "mensaje" => "Cotización registrada correctamente.",
        "id_cotizacion" => $idCotizacion

    ]);

} catch (Exception $error) {

    // Revertir los cambios si ocurre un error.

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(400);

    echo json_encode([

        "exito" => false,
        "mensaje" => $error->getMessage()

    ]);

}

?>
