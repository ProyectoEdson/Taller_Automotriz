
<?php

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "conexion.php";

// Verificar que la solicitud sea POST

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Método de solicitud no permitido."
    ]);

    exit;
}


// Recibir los datos del formulario

$usuario = trim($_POST["usuario"] ?? "");
$contrasena = $_POST["password"] ?? "";
$rolSeleccionado = strtolower(trim($_POST["rol"] ?? ""));


// Validar campos

if (
    $usuario === "" ||
    $contrasena === "" ||
    !in_array($rolSeleccionado, ["mecanico", "recepcionista"], true)
) {

    http_response_code(400);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Complete todos los campos correctamente."
    ]);

    exit;
}


try {

    // Buscar usuario y rol en la base de datos

    $consulta = $conexion->prepare("
        SELECT
            u.id_usuario,
            u.usuario,
            u.contrasena,
            u.id_rol,
            r.nombre_rol
        FROM USUARIOS u
        INNER JOIN ROLES r
            ON u.id_rol = r.id_rol
        WHERE u.usuario = :usuario
        LIMIT 1
    ");

    $consulta->execute([
        ":usuario" => $usuario
    ]);

    $datosUsuario = $consulta->fetch(PDO::FETCH_ASSOC);


    // Verificar que el usuario exista

    if (!$datosUsuario) {

        http_response_code(401);

        echo json_encode([
            "exito" => false,
            "mensaje" => "Usuario o contraseña incorrectos."
        ]);

        exit;
    }


    // Verificar contraseña protegida con password_hash()

    if (!password_verify($contrasena, $datosUsuario["contrasena"])) {

        http_response_code(401);

        echo json_encode([
            "exito" => false,
            "mensaje" => "Usuario o contraseña incorrectos."
        ]);

        exit;
    }


    // Normalizar el nombre del rol para compararlo

    $nombreRol = strtolower(trim($datosUsuario["nombre_rol"]));

    $nombreRol = strtr($nombreRol, [
        "á" => "a",
        "é" => "e",
        "í" => "i",
        "ó" => "o",
        "ú" => "u"
    ]);


    // Comprobar que el perfil seleccionado corresponda al usuario

    if ($nombreRol !== $rolSeleccionado) {

        http_response_code(403);

        echo json_encode([
            "exito" => false,
            "mensaje" => "El usuario no pertenece al perfil seleccionado."
        ]);

        exit;
    }


    // Crear sesión segura

    session_regenerate_id(true);

    $_SESSION["id_usuario"] = $datosUsuario["id_usuario"];
    $_SESSION["usuario"] = $datosUsuario["usuario"];
    $_SESSION["id_rol"] = $datosUsuario["id_rol"];
    $_SESSION["rol"] = $nombreRol;


    // Respuesta correcta

    echo json_encode([
        "exito" => true,
        "mensaje" => "Inicio de sesión correcto."
    ]);

} catch (PDOException $error) {

    http_response_code(500);

    echo json_encode([
        "exito" => false,
        "mensaje" => "Ocurrió un error al verificar el inicio de sesión."
    ]);

}

?>
