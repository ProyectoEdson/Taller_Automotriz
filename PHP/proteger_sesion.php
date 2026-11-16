<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario haya iniciado sesión

if (!isset($_SESSION["id_usuario"]) || !isset($_SESSION["rol"])) {

    // Construir la ruta al login sin depender de la profundidad
    // del archivo que incluyó este guard (raíz, PHP/ o VISTAS/).

    $script = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");

    $directorio = dirname($script);

    $base = in_array(basename($directorio), ["PHP", "VISTAS"], true)
        ? dirname($directorio)
        : $directorio;

    $login = rtrim($base, "/") . "/VISTAS/LOGIN.HTML";

    header("Location: " . $login);
    exit;

}

?>