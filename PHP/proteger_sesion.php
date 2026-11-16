<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario haya iniciado sesión

if (!isset($_SESSION["id_usuario"]) || !isset($_SESSION["rol"])) {

    header("Location: ../VISTAS/LOGIN.HTML");
    exit;

}

?>