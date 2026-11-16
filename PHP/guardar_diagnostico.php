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
        "mensaje" => "No tienes permiso para realizar esta operación."
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


// =====================================
// RECIBIR INFORMACIÓN
// =====================================

$idOrden = filter_var($_POST["id_orden"] ?? "", FILTER_VALIDATE_INT);

$diagnostico = trim($_POST["diagnostico"] ?? "");

$reparaciones = trim($_POST["reparaciones"] ?? "");

$repuestos = $_POST["repuestos"] ?? [];

$nombre = trim($_POST["nombre"] ?? "");

$primerApellido = trim($_POST["primerapellido"] ?? "");

$segundoApellido = trim($_POST["segundoapellido"] ?? "");

$telefono = trim($_POST["telefono"] ?? "");

$email = trim($_POST["email"] ?? "");

$marca = trim($_POST["marca"] ?? "");

$modelo = trim($_POST["modelo"] ?? "");

$placa = strtoupper(trim($_POST["placa"] ?? ""));

$anioTexto = trim($_POST["anio"] ?? "");

$kilometrajeTexto = trim($_POST["kilometraje"] ?? "");

$orden = trim($_POST["orden"] ?? "");


// =====================================
// VALIDAR INFORMACIÓN COMÚN
// =====================================

if ($diagnostico === "" || $reparaciones === "") {

    echo json_encode([
        "exito" => false,
        "mensaje" => "Completa el diagnóstico y las reparaciones."
    ]);

    exit;
}


if (!is_array($repuestos)) {

    echo json_encode([
        "exito" => false,
        "mensaje" => "Los repuestos tienen un formato incorrecto."
    ]);

    exit;
}


// =====================================
// VALIDAR INFORMACIÓN DE UNA ORDEN NUEVA
// =====================================

$anio = null;

$kilometraje = null;

if (!$idOrden) {

    if (
        $nombre === "" ||
        $primerApellido === "" ||
        $marca === "" ||
        $modelo === "" ||
        $placa === ""
    ) {

        echo json_encode([
            "exito" => false,
            "mensaje" => "Completa los datos del cliente y del vehículo."
        ]);

        exit;
    }


    if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        echo json_encode([
            "exito" => false,
            "mensaje" => "El correo electrónico no es válido."
        ]);

        exit;
    }


    if ($anioTexto !== "") {

        if (
            !ctype_digit($anioTexto) ||
            (int)$anioTexto < 1900 ||
            (int)$anioTexto > 2100
        ) {

            echo json_encode([
                "exito" => false,
                "mensaje" => "El año del vehículo no es válido."
            ]);

            exit;
        }

        $anio = (int)$anioTexto;

    }


    if ($kilometrajeTexto !== "") {

        if (!is_numeric($kilometrajeTexto) || (float)$kilometrajeTexto < 0) {

            echo json_encode([
                "exito" => false,
                "mensaje" => "El kilometraje no es válido."
            ]);

            exit;
        }

        $kilometraje = (float)$kilometrajeTexto;

    }

}


// =====================================
// FUNCIONES DE APOYO
// =====================================

function campoDescripcionRepuestos($conexion) {

    $columnas = $conexion->query(
        "SHOW COLUMNS FROM repuestos_utilizados"
    )->fetchAll(PDO::FETCH_COLUMN);

    if (in_array("descripcion", $columnas, true)) {

        return "descripcion";

    }

    if (in_array("descripción", $columnas, true)) {

        return "descripción";

    }

    throw new Exception(
        "No se encontró la columna de descripción en repuestos_utilizados."
    );

}


function restaurarStock($conexion, $idDiagnostico) {

    $consulta = $conexion->prepare("
        SELECT id_repuesto, cantidad
        FROM repuestos_utilizados
        WHERE id_diagnostico = ?
        AND id_repuesto IS NOT NULL
    ");

    $consulta->execute([$idDiagnostico]);

    $actualizar = $conexion->prepare("
        UPDATE inventario_repuesto
        SET stock_actual = stock_actual + ?
        WHERE id_repuesto = ?
    ");

    foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $fila) {

        $actualizar->execute([
            (int)$fila["cantidad"],
            (int)$fila["id_repuesto"]
        ]);

    }

}


function registrarRepuestos($conexion, $idDiagnostico, $repuestos, $campoDescripcion) {

    foreach ($repuestos as $repuesto) {

        $nombreRepuesto = trim($repuesto["nombre"] ?? "");

        $cantidad = filter_var(
            $repuesto["cantidad"] ?? null,
            FILTER_VALIDATE_INT
        );

        $precioTexto = $repuesto["precio"] ?? null;


        if ($nombreRepuesto === "") {
            continue;
        }


        if (
            $cantidad === false ||
            $cantidad <= 0 ||
            !is_numeric($precioTexto) ||
            !is_finite((float)$precioTexto) ||
            (float)$precioTexto < 0
        ) {

            throw new Exception(
                "Verifica las cantidades y los precios de los repuestos."
            );

        }


        $precio = (float)$precioTexto;

        $total = $cantidad * $precio;


        // Buscar el repuesto en inventario.

        $consulta = $conexion->prepare("
            SELECT id_repuesto, stock_actual
            FROM inventario_repuesto
            WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))
            LIMIT 1
            FOR UPDATE
        ");

        $consulta->execute([$nombreRepuesto]);

        $inventario = $consulta->fetch(PDO::FETCH_ASSOC);


        $idRepuesto = null;


        if ($inventario) {

            $idRepuesto = $inventario["id_repuesto"];


            if ((int)$inventario["stock_actual"] < $cantidad) {

                throw new Exception(
                    "Stock insuficiente para el repuesto: " . $nombreRepuesto
                );

            }


            // Descontar unidades del inventario.

            $actualizar = $conexion->prepare("
                UPDATE inventario_repuesto
                SET stock_actual = stock_actual - ?
                WHERE id_repuesto = ?
            ");

            $actualizar->execute([
                $cantidad,
                $idRepuesto
            ]);

        }


        // Registrar repuesto utilizado.

        $sql = "
            INSERT INTO repuestos_utilizados (
                id_diagnostico,
                id_repuesto,
                cantidad,
                precio_unitario,
                total_item,
                `$campoDescripcion`
            ) VALUES (?, ?, ?, ?, ?, ?)
        ";

        $consulta = $conexion->prepare($sql);

        $consulta->execute([
            $idDiagnostico,
            $idRepuesto,
            $cantidad,
            $precio,
            $total,
            $nombreRepuesto
        ]);

    }

}


// =====================================
// GENERAR CÓDIGO DE ORDEN
// =====================================

function generarCodigoOrden($conexion) {

    do {

        $codigo = "AT-" . date("ymd") . "-" . random_int(1000, 9999);

        $consulta = $conexion->prepare(
            "SELECT COUNT(*) FROM ordenes_servicio WHERE codigo_orden = ?"
        );

        $consulta->execute([$codigo]);

    } while ($consulta->fetchColumn() > 0);

    return $codigo;

}


// =====================================
// INICIAR PROCESO
// =====================================

$campoDescripcion = null;

try {

    $campoDescripcion = campoDescripcionRepuestos($conexion);

    $conexion->beginTransaction();


    // =====================================
    // MODO 1: ACTUALIZAR UNA ORDEN EXISTENTE
    // =====================================

    if ($idOrden) {

        $consulta = $conexion->prepare("
            SELECT codigo_orden
            FROM ordenes_servicio
            WHERE id_orden = ?
            LIMIT 1
            FOR UPDATE
        ");

        $consulta->execute([$idOrden]);

        $codigoOrden = $consulta->fetchColumn();

        if (!$codigoOrden) {

            throw new Exception("La orden de trabajo no existe.");

        }


        // Buscar el diagnóstico de la orden.

        $consulta = $conexion->prepare("
            SELECT id_diagnostico
            FROM diagnostico_reparacion
            WHERE id_orden = ?
            LIMIT 1
            FOR UPDATE
        ");

        $consulta->execute([$idOrden]);

        $idDiagnostico = $consulta->fetchColumn();


        if ($idDiagnostico) {

            $consulta = $conexion->prepare("
                UPDATE diagnostico_reparacion
                SET descripcion_fall = ?, tarea_realiz = ?
                WHERE id_diagnostico = ?
            ");

            $consulta->execute([
                $diagnostico,
                $reparaciones,
                $idDiagnostico
            ]);

            $idDiagnostico = (int)$idDiagnostico;


            // Devolver al inventario los repuestos anteriores.

            restaurarStock($conexion, $idDiagnostico);


            // Reemplazar los repuestos utilizados.

            $consulta = $conexion->prepare("
                DELETE FROM repuestos_utilizados
                WHERE id_diagnostico = ?
            ");

            $consulta->execute([$idDiagnostico]);

        } else {

            $consulta = $conexion->prepare("
                INSERT INTO diagnostico_reparacion (
                    id_orden,
                    descripcion_fall,
                    tarea_realiz
                ) VALUES (?, ?, ?)
            ");

            $consulta->execute([
                $idOrden,
                $diagnostico,
                $reparaciones
            ]);

            $idDiagnostico = (int)$conexion->lastInsertId();

        }


        registrarRepuestos(
            $conexion,
            $idDiagnostico,
            $repuestos,
            $campoDescripcion
        );


        $conexion->commit();


        echo json_encode([
            "exito" => true,
            "mensaje" => "El diagnóstico se actualizó correctamente.",
            "codigo_orden" => $codigoOrden,
            "id_orden" => (int)$idOrden
        ], JSON_UNESCAPED_UNICODE);

        exit;

    }


    // =====================================
    // MODO 2: REGISTRAR CLIENTE, VEHÍCULO Y ORDEN NUEVA
    // =====================================

    // 1. BUSCAR O REGISTRAR CLIENTE

    $consulta = $conexion->prepare("
        SELECT id_cliente
        FROM clientes
        WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))
        AND LOWER(TRIM(primerapellido)) = LOWER(TRIM(?))
        AND LOWER(TRIM(COALESCE(segundoapellido, ''))) = LOWER(TRIM(?))
        LIMIT 1
    ");

    $consulta->execute([
        $nombre,
        $primerApellido,
        $segundoApellido
    ]);

    $idCliente = $consulta->fetchColumn();


    if ($idCliente) {

        // Actualizar los datos de contacto si fueron proporcionados.

        $consulta = $conexion->prepare("
            UPDATE clientes
            SET
                telefono = COALESCE(NULLIF(?, ''), telefono),
                email = COALESCE(NULLIF(?, ''), email)
            WHERE id_cliente = ?
        ");

        $consulta->execute([
            $telefono,
            $email,
            $idCliente
        ]);

    } else {

        // Registrar un cliente nuevo.

        $consulta = $conexion->prepare("
            INSERT INTO clientes (
                nombre,
                primerapellido,
                segundoapellido,
                telefono,
                email
            ) VALUES (?, ?, ?, ?, ?)
        ");

        $consulta->execute([
            $nombre,
            $primerApellido,
            $segundoApellido !== "" ? $segundoApellido : null,
            $telefono !== "" ? $telefono : null,
            $email !== "" ? $email : null
        ]);

        $idCliente = $conexion->lastInsertId();

    }


    // 2. BUSCAR O REGISTRAR VEHÍCULO

    $consulta = $conexion->prepare("
        SELECT id_vehiculo, id_cliente
        FROM vehiculos
        WHERE placa = ?
        LIMIT 1
        FOR UPDATE
    ");

    $consulta->execute([$placa]);

    $vehiculoExistente = $consulta->fetch(PDO::FETCH_ASSOC);


    if ($vehiculoExistente) {

        $idVehiculo = $vehiculoExistente["id_vehiculo"];


        // Evitar asignar una placa existente a otro cliente.

        if ((int)$vehiculoExistente["id_cliente"] !== (int)$idCliente) {

            throw new Exception(
                "Esta placa ya está registrada para otro cliente."
            );

        }


        // Actualizar la información del vehículo.
        // Si año o kilometraje vienen vacíos, se conserva el dato anterior.

        $consulta = $conexion->prepare("
            UPDATE vehiculos
            SET
                marca = ?,
                modelo = ?,
                `año` = COALESCE(?, `año`),
                kilometraje = COALESCE(?, kilometraje)
            WHERE id_vehiculo = ?
        ");

        $consulta->execute([
            $marca,
            $modelo,
            $anio,
            $kilometraje,
            $idVehiculo
        ]);

    } else {

        // Registrar un vehículo nuevo.

        $consulta = $conexion->prepare("
            INSERT INTO vehiculos (
                id_cliente,
                placa,
                modelo,
                marca,
                `año`,
                kilometraje
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");

        $consulta->execute([
            $idCliente,
            $placa,
            $modelo,
            $marca,
            $anio,
            $kilometraje
        ]);

        $idVehiculo = $conexion->lastInsertId();

    }


    // 3. CREAR ORDEN DE SERVICIO

    if ($orden === "") {

        $orden = generarCodigoOrden($conexion);

    }


    $consulta = $conexion->prepare("
        SELECT id_orden
        FROM ordenes_servicio
        WHERE codigo_orden = ?
        LIMIT 1
    ");

    $consulta->execute([$orden]);


    if ($consulta->fetchColumn()) {

        throw new Exception(
            "El código de orden ya existe. Utiliza otro código."
        );

    }


    $consulta = $conexion->prepare("
        INSERT INTO ordenes_servicio (
            codigo_orden,
            id_vehiculo
        ) VALUES (?, ?)
    ");

    $consulta->execute([
        $orden,
        $idVehiculo
    ]);

    $idOrden = $conexion->lastInsertId();


    // 4. GUARDAR DIAGNÓSTICO

    $consulta = $conexion->prepare("
        INSERT INTO diagnostico_reparacion (
            id_orden,
            descripcion_fall,
            tarea_realiz
        ) VALUES (?, ?, ?)
    ");

    $consulta->execute([
        $idOrden,
        $diagnostico,
        $reparaciones
    ]);

    $idDiagnostico = (int)$conexion->lastInsertId();


    // 5. GUARDAR REPUESTOS

    registrarRepuestos(
        $conexion,
        $idDiagnostico,
        $repuestos,
        $campoDescripcion
    );


    // 6. CONFIRMAR TRANSACCIÓN

    $conexion->commit();


    echo json_encode([
        "exito" => true,
        "mensaje" => "El diagnóstico se guardó correctamente.",
        "codigo_orden" => $orden,
        "id_orden" => (int)$idOrden
    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $error) {

    if ($conexion->inTransaction()) {

        $conexion->rollBack();

    }

    http_response_code(400);

    echo json_encode([
        "exito" => false,
        "mensaje" => $error->getMessage()
    ], JSON_UNESCAPED_UNICODE);

}

?>
