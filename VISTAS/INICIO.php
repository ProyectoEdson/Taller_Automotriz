
<?php

require_once "../PHP/proteger_sesion.php";

$rol = $_SESSION["rol"];
$usuario = $_SESSION["usuario"];

// Normalizar el rol para mostrarlo
$rolMostrar = strtoupper($rol);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../CSS/AUTOMOTRIZ.CSS">

    <meta name="description" content="Panel principal del Taller Automotriz">

    <title>Taller Automotriz - Inicio</title>

</head>

<body class="inicio-body">

<div class="app-frame">

    <header>

        <div class="brand">

            <svg viewBox="0 0 80 65" class="auto">

                <path d="M15 42 L20 17 Q22 7 32 7 H49 Q59 7 62 17 L67 42 V55 H56 V48 H24 V55 H13 V42 Z"/>

                <path d="M21 26 H61 M14 37 H24 M57 37 H67 M27 15 H54"/>

            </svg>

            <h1>TALLER AUTOMOTRIZ</h1>

        </div>


        <div class="user-actions">

            <div class="user-role">

                <svg width="18" height="18" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">

                    <circle cx="12" cy="8" r="4"/>

                    <path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>

                </svg>

                <span id="rolLabel">
                    <?php echo htmlspecialchars($rolMostrar); ?>
                </span>

            </div>

            <a href="../PHP/cerrar_sesion.php" class="logout">
                CERRAR SESIÓN
            </a>

        </div>

    </header>


    <div class="layout">

        <aside class="sidebar">

            <nav>

                <!-- INICIO -->

                <a href="INICIO.php" class="nav-item active">

                    <span>

                        <svg width="22" height="22" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">

                            <path d="M3 10.5L12 3L21 10.5"/>

                            <path d="M5 9.5V21H19V9.5"/>

                            <path d="M9 21V14H15V21"/>

                        </svg>

                    </span>

                    <span>INICIO</span>

                </a>


                <!-- DIAGNÓSTICO Y REPARACIÓN -->

                <?php if ($rol === "mecanico"): ?>

                <a href="../INDEX.php" class="nav-item">

                    <span>

                        <svg width="22" height="22" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">

                            <path d="M14.7 6.3a5 5 0 0 0-6.4 6.4L3 18l3 3 5.3-5.3a5 5 0 0 0 6.4-6.4L14 13l-3-3 3.7-3.7Z"/>

                        </svg>

                    </span>

                    <span>DIAGNÓSTICO Y REPARACIÓN</span>

                </a>

                <?php endif; ?>


                <!-- COTIZACIÓN -->

                <?php if ($rol === "recepcionista"): ?>

                <a href="COTIZACIÓN.php" class="nav-item">

                    <span>

                        <svg width="22" height="22" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">

                            <path d="M6 2H14L20 8V22H6Z"/>

                            <path d="M14 2V8H20"/>

                            <path d="M9 13H17M9 17H17"/>

                        </svg>

                    </span>

                    <span>COTIZACIÓN</span>

                </a>


                <!-- PAGOS -->

                <a href="PAGOS.php" class="nav-item">

                    <span>

                        <svg width="22" height="22" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">

                            <rect x="2" y="5" width="20" height="14" rx="2"/>

                            <path d="M2 10H22"/>

                            <path d="M6 15H10"/>

                        </svg>

                    </span>

                    <span>PAGOS</span>

                </a>

                <?php endif; ?>

            </nav>

        </aside>


        <!-- CONTENIDO PRINCIPAL -->

        <main class="dashboard-main">


            <!-- BIENVENIDA -->

            <div class="dashboard-welcome">

                <div>

                    <p class="dashboard-subtitle">PANEL DE CONTROL</p>

                    <h2>Bienvenido al sistema</h2>

                    <p>
                        Administra las actividades y operaciones del taller
                        desde un solo lugar.
                    </p>

                </div>

                <div class="welcome-icon">🚘</div>

            </div>


            <!-- RESUMEN GENERAL -->

            <section class="dashboard-summary">

                <h3>RESUMEN GENERAL</h3>

                <div class="dashboard-grid">


                    <div class="dash-card">

                        <div class="dash-icon">📋</div>

                        <div>

                            <p>Órdenes registradas</p>

                            <h3 id="totalOrdenes">0</h3>

                        </div>

                    </div>


                    <div class="dash-card">

                        <div class="dash-icon">📄</div>

                        <div>

                            <p>Cotizaciones pendientes</p>

                            <h3 id="totalCotizaciones">0</h3>

                        </div>

                    </div>


                    <div class="dash-card">

                        <div class="dash-icon">🔧</div>

                        <div>

                            <p>Reparaciones activas</p>

                            <h3 id="totalReparaciones">0</h3>

                        </div>

                    </div>


                    <div class="dash-card">

                        <div class="dash-icon">💳</div>

                        <div>

                            <p>Pagos pendientes</p>

                            <h3 id="totalPagos">0</h3>

                        </div>

                    </div>

                </div>

            </section>


            <!-- ACCESOS DEL SISTEMA -->

            <section class="dashboard-access">

                <h3>ACCESOS DEL SISTEMA</h3>

                <p class="access-description">
                    Selecciona el módulo que deseas gestionar.
                </p>

                <div class="access-grid">


                    <!-- DIAGNÓSTICO Y REPARACIÓN -->

                    <?php if ($rol === "mecanico"): ?>

                    <a href="../INDEX.php" class="access-card">

                        <div class="access-icon">⚙️</div>

                        <div>

                            <h4>Diagnóstico y reparación</h4>

                            <p>
                                Consulta órdenes de trabajo y registra
                                diagnósticos y reparaciones.
                            </p>

                        </div>

                        <span class="access-arrow">→</span>

                    </a>

                    <?php endif; ?>


                    <!-- COTIZACIONES -->

                    <?php if ($rol === "recepcionista"): ?>

                    <a href="COTIZACIÓN.php" class="access-card">

                        <div class="access-icon">📑</div>

                        <div>

                            <h4>Cotizaciones</h4>

                            <p>
                                Consulta cotizaciones anteriores y genera
                                nuevos presupuestos.
                            </p>

                        </div>

                        <span class="access-arrow">→</span>

                    </a>


                    <!-- PAGOS -->

                    <a href="PAGOS.php" class="access-card">

                        <div class="access-icon">💳</div>

                        <div>

                            <h4>Pagos</h4>

                            <p>
                                Revisa cuentas pendientes y registra
                                los pagos de los clientes.
                            </p>

                        </div>

                        <span class="access-arrow">→</span>

                    </a>

                    <?php endif; ?>

                </div>

            </section>

        </main>

    </div>

</div>


<script>

// ACTUALIZAR RESUMEN GENERAL

function actualizarResumen() {

    const diagnosticos = JSON.parse(
        localStorage.getItem('diagnosticosTaller') || '[]'
    );

    const cotizaciones = JSON.parse(
        localStorage.getItem('cotizacionesTaller') || '[]'
    );

    const pagos = JSON.parse(
        localStorage.getItem('pagosTaller') || '[]'
    );


    // Órdenes registradas

    document.getElementById('totalOrdenes').textContent =
        diagnosticos.length;


    // Cotizaciones generadas

    document.getElementById('totalCotizaciones').textContent =
        cotizaciones.length;


    // Reparaciones activas

    document.getElementById('totalReparaciones').textContent =
        diagnosticos.length;


    // Calcular pagos pendientes

    const pagosPendientes = cotizaciones.filter(cotizacion => {

        const pago = pagos.find(p =>
            p.orden === cotizacion.orden
        );

        return !pago || pago.estado !== 'Pagado';

    });


    document.getElementById('totalPagos').textContent =
        pagosPendientes.length;

}


// Ejecutar actualización al cargar Inicio

actualizarResumen();

</script>


</body>

</html>
