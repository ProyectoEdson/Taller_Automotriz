
<?php

$servidor = "localhost";
$nombreBD = "taller_automotriz";
$usuario = "root";
$contrasena = "";

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
