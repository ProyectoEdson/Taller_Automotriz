<?php

require_once "../PHP/proteger_sesion.php";

// Este módulo corresponde al recepcionista.

if ($_SESSION["rol"] !== "recepcionista") {
    header("Location: ./INICIO.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrar Pago</title>

    <link rel="stylesheet" href="../CSS/AUTOMOTRIZ.CSS">

</head>

<body>

<div class="contenedor">

    <header class="encabezado">

        <div class="logo">

            <svg viewBox="0 0 80 65" class="auto" aria-hidden="true">
                <path d="M15 42 L20 17 Q22 7 32 7 H49 Q59 7 62 17 L67 42 V55 H56 V48 H24 V55 H13 V42 Z"/>
                <path d="M21 26 H61 M14 37 H24 M57 37 H67 M27 15 H54"/>
            </svg>

            <span>TALLER AUTOMOTRIZ</span>

        </div>

        <div class="usuario">

            <span class="user-role">

                <svg width="18" height="18" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">

                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>

                </svg>

                RECEPCIONISTA

            </span>

            <a href="../PHP/cerrar_sesion.php">
                CERRAR SESIÓN
            </a>

        </div>

    </header>

    <div class="estructura">

        <aside class="menu">

            <a href="../VISTAS/INICIO.php" class="opcion">

                <span class="icono">

                    <svg viewBox="0 0 24 24" aria-hidden="true"
                        fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">

                        <path d="M3 10.5L12 3L21 10.5"/>
                        <path d="M5 9.5V21H19V9.5"/>
                        <path d="M9 21V14H15V21"/>

                    </svg>

                </span>

                INICIO

            </a>

            <a href="../VISTAS/COTIZACIÓN.php" class="opcion">

                <span class="icono">

                    <svg viewBox="0 0 24 24" width="22" height="22"
                        fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">

                        <path d="M6 3h8l4 4v14H6z"/>
                        <path d="M14 3v5h5M9 13h6M9 17h6"/>

                    </svg>

                </span>

                COTIZACIÓN

            </a>

            <a href="../VISTAS/PAGOS.php" class="opcion seleccionado">

                <span class="icono">

                    <svg viewBox="0 0 24 24" width="22" height="22"
                        fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">

                        <rect x="2.5" y="5" width="19" height="14" rx="2"/>
                        <path d="M3 10h18M7 15h4"/>

                    </svg>

                </span>

                PAGOS

            </a>

        </aside>

        <main class="contenido">

            <h1>REGISTRAR PAGO DE CUENTA</h1>

            <!-- SELECCIONAR ORDEN -->

            <section class="informacion">

                <h3>SELECCIONAR ORDEN DE TRABAJO</h3>

                <select id="seleccionarOrden">

                    <option value="">Seleccione una orden</option>

                </select>

            </section>

            <!-- INFORMACIÓN DE LA ORDEN -->

            <section class="informacion orden-pago">

                <h3>INFORMACIÓN DE LA ORDEN</h3>

                <div class="datos-pago">

                    <span>ORDEN DE TRABAJO:</span>
                    <strong id="ordenTrabajo">-</strong>

                    <span>CLIENTE:</span>
                    <strong id="cliente">-</strong>

                    <span>VEHÍCULO:</span>
                    <strong id="vehiculo">-</strong>

                    <span>PLACA:</span>
                    <strong id="placa">-</strong>

                </div>

            </section>

            <div class="zona-pago">

                <div class="columna-izquierda">

                    <section class="tarjeta-total">

                        <h3>Total a Pagar:</h3>

                        <strong id="totalPagar">$0.00</strong>

                    </section>

                    <section class="estado-cuenta">

                        <h3>Estado De Cuenta</h3>

                        <div class="estado pendiente" id="estadoPendiente">
                            Pendiente
                        </div>

                        <div class="flecha" id="flechaEstado">↓</div>

                        <div class="estado pendiente" id="estadoPagado">
                            Pagado
                        </div>

                    </section>

                </div>

                <section class="formulario-pago">

                    <h3>Método De Pago</h3>

                    <div class="metodos">

                        <label>

                            <input type="radio" name="metodo"
                                value="Efectivo" checked>

                            Efectivo

                        </label>

                        <label>

                            <input type="radio" name="metodo"
                                value="Tarjeta">

                            Tarjeta

                        </label>

                        <label>

                            <input type="radio" name="metodo"
                                value="Transferencia">

                            Yappy/Transferencia

                        </label>

                    </div>

                    <label for="monto">Monto Recibido</label>

                    <input type="number" id="monto"
                        min="0" step="0.01" value="0">

                    <label for="fecha">Fecha De Pago</label>

                    <input type="date" id="fecha">

                </section>

            </div>

            <div class="acciones">

                <button type="button" id="btnRegistrarPago" disabled>
                    Registrar Pago
                </button>

            </div>

            <p id="mensajePago"></p>

        </main>

    </div>

</div>

<script>

// COTIZACIONES CARGADAS DESDE MYSQL

let cotizaciones = [];

let cotizacionSeleccionada = null;


// FORMATEAR VALORES MONETARIOS

function formatoDinero(valor) {

    return '$' + Number(valor).toFixed(2);

}


// ESTABLECER FECHA ACTUAL

function establecerFechaActual() {

    const hoy = new Date();

    const anio = hoy.getFullYear();

    const mes = String(hoy.getMonth() + 1).padStart(2, '0');

    const dia = String(hoy.getDate()).padStart(2, '0');

    document.getElementById('fecha').value =
        `${anio}-${mes}-${dia}`;

}


// MOSTRAR MENSAJES

function mostrarMensaje(mensaje) {

    document.getElementById('mensajePago').textContent = mensaje;

}


// CARGAR COTIZACIONES DESDE PHP

async function cargarListaCotizaciones() {

    const selector = document.getElementById('seleccionarOrden');

    selector.innerHTML =
        '<option value="">Seleccione una orden</option>';

    try {

        const respuesta = await fetch(
            "../PHP/consultar_cotizaciones.php"
        );

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.exito) {

            throw new Error(
                resultado.mensaje || "No se pudieron consultar las cotizaciones."
            );

        }

        cotizaciones = resultado.cotizaciones || [];

        cotizaciones.forEach(cotizacion => {

            const opcion = document.createElement('option');

            opcion.value = cotizacion.id_cotizacion;

            opcion.textContent =
                cotizacion.codigo_orden + ' - ' + cotizacion.cliente;

            selector.appendChild(opcion);

        });

        if (cotizaciones.length > 0) {

            selector.value = cotizaciones[0].id_cotizacion;

            cargarOrdenSeleccionada();

        } else {

            limpiarInformacion();

            mostrarMensaje("No hay cotizaciones registradas.");

        }

    } catch (error) {

        console.error("Error al cargar cotizaciones:", error);

        limpiarInformacion();

        mostrarMensaje(
            "No se pudieron cargar las cotizaciones. Verifique la conexión."
        );

    }

}


// BUSCAR COTIZACIÓN SELECCIONADA

function obtenerCotizacion(idCotizacion) {

    return cotizaciones.find(cotizacion =>
        String(cotizacion.id_cotizacion) === String(idCotizacion)
    );

}


// LIMPIAR INFORMACIÓN

function limpiarInformacion() {

    document.getElementById('ordenTrabajo').textContent = '-';

    document.getElementById('cliente').textContent = '-';

    document.getElementById('vehiculo').textContent = '-';

    document.getElementById('placa').textContent = '-';

    document.getElementById('totalPagar').textContent = '$0.00';

    document.getElementById('monto').value = '';

    document.getElementById('monto').disabled = true;

    document.getElementById('fecha').value = '';

    document.getElementById('fecha').disabled = true;

    document.querySelectorAll(
        'input[name="metodo"]'
    ).forEach(input => {

        input.checked = input.value === 'Efectivo';

        input.disabled = true;

    });

    document.getElementById('estadoPendiente').textContent = 'Pendiente';

    document.getElementById('estadoPendiente').className =
        'estado pendiente';

    document.getElementById('estadoPagado').textContent = 'Pagado';

    document.getElementById('estadoPagado').className =
        'estado pendiente';

    document.getElementById('flechaEstado').textContent = '↓';

    document.getElementById('btnRegistrarPago').disabled = true;

    cotizacionSeleccionada = null;

}


// CARGAR INFORMACIÓN DE LA ORDEN

function cargarOrdenSeleccionada() {

    const idCotizacion =
        document.getElementById('seleccionarOrden').value;

    const cotizacion = obtenerCotizacion(idCotizacion);

    if (!cotizacion) {

        limpiarInformacion();

        return;

    }

    cotizacionSeleccionada = cotizacion;

    document.getElementById('ordenTrabajo').textContent =
        cotizacion.codigo_orden;

    document.getElementById('cliente').textContent =
        cotizacion.cliente;

    document.getElementById('vehiculo').textContent =
        cotizacion.vehiculo;

    document.getElementById('placa').textContent =
        cotizacion.placa;

    document.getElementById('totalPagar').textContent =
        formatoDinero(cotizacion.total);

    document.getElementById('monto').value =
        Number(cotizacion.total).toFixed(2);

    document.getElementById('monto').disabled = false;

    document.getElementById('fecha').disabled = false;

    document.getElementById('btnRegistrarPago').disabled = false;

    document.querySelectorAll(
        'input[name="metodo"]'
    ).forEach(input => {

        input.disabled = false;

        input.checked = input.value === 'Efectivo';

    });

    establecerFechaActual();

    // Estado inicial mientras se consulta el historial de pagos.

    document.getElementById('estadoPendiente').textContent = 'Pendiente';

    document.getElementById('estadoPendiente').className =
        'estado pendiente';

    document.getElementById('estadoPagado').textContent = 'Pagado';

    document.getElementById('estadoPagado').className =
        'estado pendiente';

    document.getElementById('flechaEstado').textContent = '↓';

    mostrarMensaje('');

}


// MOSTRAR ESTADO PAGADO

function mostrarEstadoPagado() {

    const estadoPendiente =
        document.getElementById('estadoPendiente');

    const estadoPagado =
        document.getElementById('estadoPagado');

    estadoPendiente.textContent = 'Registrado';

    estadoPendiente.className = 'estado registrado';

    estadoPagado.textContent = 'Pagado';

    estadoPagado.className = 'estado pagado';

    document.getElementById('flechaEstado').textContent = '↓';

}


// REGISTRAR PAGO EN MYSQL

async function registrarPago() {

    if (!cotizacionSeleccionada) {

        alert('Primero debe seleccionar una cotización.');

        return;

    }

    const monto = Number(
        document.getElementById('monto').value
    );

    const fecha = document.getElementById('fecha').value;

    const metodo = document.querySelector(
        'input[name="metodo"]:checked'
    );

    const total = Number(cotizacionSeleccionada.total);

    // VALIDAR MONTO

    if (!Number.isFinite(monto) || monto <= 0) {

        alert('Ingrese un monto válido.');

        return;

    }

    if (Math.abs(monto - total) > 0.009) {

        alert(
            'El monto recibido debe coincidir con el total de la cotización: ' +
            formatoDinero(total)
        );

        return;

    }

    if (!fecha) {

        alert('Seleccione la fecha del pago.');

        return;

    }

    if (!metodo) {

        alert('Seleccione un método de pago.');

        return;

    }

    // PREPARAR DATOS PARA PHP

    const datosPago = {

        id_orden: Number(cotizacionSeleccionada.id_orden),

        id_cotizacion: Number(cotizacionSeleccionada.id_cotizacion),

        monto_recibido: monto,

        metodo_pago: metodo.value

    };

    const boton = document.getElementById('btnRegistrarPago');

    boton.disabled = true;

    mostrarMensaje("Registrando pago...");

    try {

        const respuesta = await fetch(
            "../PHP/registrar_pago.php",
            {

                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify(datosPago)

            }
        );

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.exito) {

            throw new Error(
                resultado.mensaje || "No se pudo registrar el pago."
            );

        }

        // ACTUALIZAR INTERFAZ

        mostrarEstadoPagado();

        document.getElementById('monto').disabled = true;

        document.getElementById('fecha').disabled = true;

        document.querySelectorAll(
            'input[name="metodo"]'
        ).forEach(input => input.disabled = true);

        mostrarMensaje(resultado.mensaje);

        alert(resultado.mensaje);

    } catch (error) {

        console.error("Error al registrar el pago:", error);

        mostrarMensaje(error.message);

        alert(error.message);

        boton.disabled = false;

    }

}


// EVENTO DEL BOTÓN

document.getElementById('btnRegistrarPago').addEventListener(
    'click',
    registrarPago
);


// EVENTO DEL SELECTOR

document.getElementById('seleccionarOrden').addEventListener(
    'change',
    cargarOrdenSeleccionada
);


// INICIAR PÁGINA

cargarListaCotizaciones();

</script>

</body>
</html>
