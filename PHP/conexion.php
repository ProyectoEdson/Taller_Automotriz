
<?php

$servidor = getenv("TALLER_DB_HOST") ?: "localhost";

$nombreBD = getenv("TALLER_DB_NAME") ?: "taller_automotriz";

$usuario = getenv("TALLER_DB_USER") ?: "root";

$contrasena = getenv("TALLER_DB_PASS") ?: "";

try {

    $conexion = new PDO(
        "mysql:host=$servidor;dbname=$nombreBD;charset=utf8mb4",
        $usuario,
        $contrasena
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $error) {

    die("Error de conexión: " . $error->getMessage());

}

?>
