
<?php

require_once "../PHP/proteger_sesion.php";

// Este módulo corresponde al recepcionista.
if ($_SESSION["rol"] !== "recepcionista") {
    header("Location: INICIO.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Generar Cotización</title>

    <link rel="stylesheet" href="../CSS/AUTOMOTRIZ.CSS">

    <style>

/* DISTRIBUCIÓN DE COTIZACIÓN */

.contenido-cotizacion {
    width: 100%;
}

.info-orden-cotizacion {
    background: #eeeeee;
    padding: 22px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.info-orden-cotizacion h3,
.detalle-cotizacion h3 {
    margin-top: 0;
    margin-bottom: 18px;
}

.datos-orden-cotizacion {
    display: grid;
    grid-template-columns: 180px 1fr;
    gap: 13px 18px;
    align-items: center;
}

.datos-orden-cotizacion label {
    font-weight: 600;
}

.datos-orden-cotizacion input {
    width: 100%;
    padding: 10px;
    border: 1px solid #cccccc;
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 14px;
}

.datos-orden-cotizacion input[readonly] {
    background: #f8f8f8;
    color: #555555;
}

.detalle-cotizacion {
    background: #ffffff;
    padding: 22px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.tabla-cotizacion {
    width: 100%;
    border-collapse: collapse;
}

.tabla-cotizacion th,
.tabla-cotizacion td {
    padding: 13px 12px;
    text-align: left;
    border-bottom: 1px solid #dddddd;
}

.tabla-cotizacion th {
    background: #eeeeee;
    font-weight: 700;
}

.tabla-cotizacion input,
.tabla-cotizacion select {
    width: 100%;
    min-width: 65px;
    padding: 9px;
    box-sizing: border-box;
}

.resumen-cotizacion {
    width: 300px;
    max-width: 100%;
    margin: 22px 0 0 auto;
}

.fila-resumen {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.fila-resumen.total-final {
    border-top: 2px solid #dddddd;
    margin-top: 8px;
    padding-top: 14px;
    font-size: 19px;
    font-weight: bold;
}

.acciones-cotizacion {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    margin-top: 20px;
}

.acciones-cotizacion button {
    background: #922c48;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 13px 25px;
    font-weight: bold;
    cursor: pointer;
}

.acciones-cotizacion button:hover {
    background: #742139;
}

.acciones-cotizacion button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-eliminar-detalle {
    background: #922c48;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
}

#mensaje {
    margin-top: 15px;
    font-weight: 600;
}

@media (max-width: 800px) {

    .datos-orden-cotizacion {
        grid-template-columns: 1fr;
        gap: 6px;
    }

    .datos-orden-cotizacion input {
        margin-bottom: 10px;
    }

    .tabla-contenedor {
        overflow-x: auto;
    }

    .tabla-cotizacion {
        min-width: 800px;
    }

}

    </style>

</head>

<body>

<div class="contenedor">

    <!-- ENCABEZADO SUPERIOR -->

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
                    stroke-linecap="round" stroke-linejoin="round">

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

        <!-- MENÚ LATERAL -->

        <aside class="menu">

            <a href="INICIO.php" class="opcion">

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

            <a href="COTIZACIÓN.PHP" class="opcion seleccionado">

                <span class="icono">
                    <svg viewBox="0 0 24 24" width="22" height="22"
                        fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 3h8l4 4v14H6z"/>
                        <path d="M14 3v5h5M9 13h6M9 17h6"/>
                        <path d="m9 9 1.5 1.5L13 8"/>
                    </svg>
                </span>

                COTIZACIÓN

            </a>

            <a href="PAGOS.PHP" class="opcion">

                <span class="icono">
                    <svg viewBox="0 0 24 24" width="22" height="22"
                        fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2.5" y="5" width="19" height="14" rx="2"/>
                        <path d="M3 10h18M7 15h4"/>
                    </svg>
                </span>

                PAGOS

            </a>

        </aside>

        <!-- CONTENIDO PRINCIPAL -->

        <main class="contenido contenido-cotizacion">

            <h1>GENERAR COTIZACIÓN</h1>

            <form id="formCotizacion">

                <!-- INFORMACIÓN DE LA ORDEN -->

                <section class="info-orden-cotizacion">

                    <h3>INFORMACIÓN DE LA ORDEN</h3>

                    <div class="datos-orden-cotizacion">

                        <label for="codigoOrden">ORDEN DE TRABAJO:</label>

                        <input type="text" id="codigoOrden"
                            placeholder="Ej. AT-261116-1586" required>

                        <label for="cliente">CLIENTE:</label>

                        <input type="text" id="cliente"
                            placeholder="Se completará automáticamente"
                            readonly required>

                        <label for="vehiculo">VEHÍCULO:</label>

                        <input type="text" id="vehiculo"
                            placeholder="Se completará automáticamente"
                            readonly required>

                        <label for="placa">PLACA:</label>

                        <input type="text" id="placa"
                            placeholder="Se completará automáticamente"
                            readonly required>

                    </div>

                </section>

                <!-- SERVICIOS Y REPUESTOS -->

                <section class="detalle-cotizacion">

                    <h3>SERVICIOS Y REPUESTOS</h3>

                    <div class="tabla-contenedor">

                        <table class="tabla-cotizacion">

                            <thead>

                                <tr>
                                    <th>Descripción</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Precio unitario</th>
                                    <th>Total</th>
                                    <th>Acción</th>
                                </tr>

                            </thead>

                            <tbody id="detalleCotizacion"></tbody>

                        </table>

                    </div>

                    <div class="acciones-cotizacion">

                        <button type="button" id="agregarDetalle">
                            + Agregar detalle
                        </button>

                    </div>

                    <!-- RESUMEN DE TOTALES -->

                    <div class="resumen-cotizacion">

                        <div class="fila-resumen">
                            <span>Subtotal:</span>
                            <strong id="subtotalTexto">$0.00</strong>
                        </div>

                        <div class="fila-resumen">
                            <span>Impuesto (7 %):</span>
                            <strong id="impuestoTexto">$0.00</strong>
                        </div>

                        <div class="fila-resumen total-final">
                            <span>Total:</span>
                            <strong id="totalTexto">$0.00</strong>
                        </div>

                    </div>

                </section>

                <!-- CAMPOS INTERNOS -->

                <input type="hidden" id="subtotal" value="0.00">
                <input type="hidden" id="impuesto" value="0.00">
                <input type="hidden" id="total" value="0.00">

                <!-- BOTÓN GENERAR -->

                <div class="acciones-cotizacion">

                    <button type="submit" id="btnGenerar">
                        GENERAR COTIZACIÓN
                    </button>

                </div>

            </form>

            <p id="mensaje"></p>

        </main>

    </div>

</div>

<script>

// ELEMENTOS

const formulario = document.getElementById("formCotizacion");

const tablaDetalles = document.getElementById("detalleCotizacion");

const subtotalInput = document.getElementById("subtotal");
const impuestoInput = document.getElementById("impuesto");
const totalInput = document.getElementById("total");

const codigoOrdenInput = document.getElementById("codigoOrden");
const clienteInput = document.getElementById("cliente");
const vehiculoInput = document.getElementById("vehiculo");
const placaInput = document.getElementById("placa");

let codigoOrdenConsultada = "";


// FORMATO DE DINERO

function formatoDinero(valor) {

    return "$" + Number(valor).toFixed(2);

}


// MOSTRAR MENSAJE

function mostrarMensaje(mensaje) {

    document.getElementById("mensaje").textContent = mensaje;

}


// AGREGAR UNA FILA A LA TABLA

function agregarDetalle(datos = null) {

    const fila = document.createElement("tr");

    fila.innerHTML = `

        <td>
            <input type="text" class="descripcion"
                placeholder="Descripción"
                maxlength="150"
                required>
        </td>

        <td>
            <select class="tipo" required>

                <option value="Servicio">Servicio</option>
                <option value="Repuesto">Repuesto</option>

            </select>
        </td>

        <td>
            <input type="number" class="cantidad"
                min="1" step="1" value="1" required>
        </td>

        <td>
            <input type="number" class="precio"
                min="0" step="0.01" value="0.00" required>
        </td>

        <td>
            <input type="number" class="total-item"
                value="0.00" readonly>
        </td>

        <td>
            <button type="button" class="btn-eliminar-detalle">
                Eliminar
            </button>
        </td>

    `;

    tablaDetalles.appendChild(fila);

    // Si se reciben datos, completar la fila automáticamente.

    if (datos !== null) {

        fila.querySelector(".descripcion").value =
            datos.descripcion || "";

        fila.querySelector(".tipo").value =
            datos.tipo || "Servicio";

        fila.querySelector(".cantidad").value =
            datos.cantidad || 1;

        fila.querySelector(".precio").value =
            Number(datos.precio_unitario || 0).toFixed(2);

    }

    // Actualizar cálculos cuando cambie la cantidad.

    fila.querySelector(".cantidad").addEventListener(
        "input", calcularTotales
    );

    // Actualizar cálculos cuando cambie el precio.

    fila.querySelector(".precio").addEventListener(
        "input", calcularTotales
    );

    // Eliminar fila.

    fila.querySelector(".btn-eliminar-detalle").addEventListener(
        "click", function() {

            fila.remove();

            calcularTotales();

        }
    );

    calcularTotales();

}


// CONSULTAR ORDEN DE TRABAJO

async function consultarOrden() {

    const codigo = codigoOrdenInput.value.trim();

    // Invalidar los datos anteriores.

    codigoOrdenConsultada = "";

    clienteInput.value = "";
    vehiculoInput.value = "";
    placaInput.value = "";

    tablaDetalles.innerHTML = "";

    calcularTotales();

    if (codigo === "") {

        mostrarMensaje("Escribe el código de la orden de trabajo.");

        return false;

    }

    try {

        mostrarMensaje("Buscando orden de trabajo...");

        const respuesta = await fetch("../PHP/consultar_orden.php", {

            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({
                codigo_orden: codigo
            })

        });

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.exito) {

            mostrarMensaje(
                resultado.mensaje || "No se encontró la orden."
            );

            return false;

        }

        // Completar los datos de la orden.

        codigoOrdenInput.value = resultado.codigo_orden;

        clienteInput.value = resultado.cliente;

        vehiculoInput.value = resultado.vehiculo;

        placaInput.value = resultado.placa;

        // Cargar diagnóstico, reparaciones y repuestos.

        if (
            Array.isArray(resultado.detalles) &&
            resultado.detalles.length > 0
        ) {

            resultado.detalles.forEach(detalle => {

                agregarDetalle(detalle);

            });

            mostrarMensaje(
                "Orden encontrada. Se cargaron " +
                resultado.detalles.length +
                " detalles registrados por el mecánico."
            );

        } else {

            mostrarMensaje(
                "Orden encontrada, pero no tiene diagnóstico, reparaciones o repuestos registrados."
            );

        }

        // Marcar la orden como consultada.

        codigoOrdenConsultada = resultado.codigo_orden;

        calcularTotales();

        return true;

    } catch (error) {

        mostrarMensaje(
            "Ocurrió un error al consultar la orden."
        );

        return false;

    }

}


// CONSULTAR AL SALIR DEL CAMPO

codigoOrdenInput.addEventListener("blur", consultarOrden);


// CONSULTAR AL PRESIONAR ENTER

codigoOrdenInput.addEventListener("keydown", function(evento) {

    if (evento.key === "Enter") {

        evento.preventDefault();

        consultarOrden();

    }

});


// INVALIDAR CONSULTA SI CAMBIA EL CÓDIGO

codigoOrdenInput.addEventListener("input", function() {

    codigoOrdenConsultada = "";

    clienteInput.value = "";
    vehiculoInput.value = "";
    placaInput.value = "";

    tablaDetalles.innerHTML = "";

    calcularTotales();

});


// BOTÓN AGREGAR DETALLE

document.getElementById("agregarDetalle").addEventListener(
    "click", function() {

        agregarDetalle();

    }
);


// CALCULAR TOTALES

function calcularTotales() {

    let subtotal = 0;

    document.querySelectorAll("#detalleCotizacion tr").forEach(fila => {

        const cantidad =
            Number(fila.querySelector(".cantidad").value) || 0;

        const precio =
            Number(fila.querySelector(".precio").value) || 0;

        const totalItem = cantidad * precio;

        fila.querySelector(".total-item").value =
            totalItem.toFixed(2);

        subtotal += totalItem;

    });

    const impuesto = subtotal * 0.07;

    const total = subtotal + impuesto;

    subtotalInput.value = subtotal.toFixed(2);

    impuestoInput.value = impuesto.toFixed(2);

    totalInput.value = total.toFixed(2);

    document.getElementById("subtotalTexto").textContent =
        formatoDinero(subtotal);

    document.getElementById("impuestoTexto").textContent =
        formatoDinero(impuesto);

    document.getElementById("totalTexto").textContent =
        formatoDinero(total);

}


// GUARDAR COTIZACIÓN

formulario.addEventListener("submit", async function(evento) {

    evento.preventDefault();

    const boton = document.getElementById("btnGenerar");

    // Verificar que la orden haya sido consultada.

    if (codigoOrdenConsultada !== codigoOrdenInput.value.trim()) {

        const ordenEncontrada = await consultarOrden();

        if (!ordenEncontrada) {
            return;
        }

    }

    // Obtener los detalles de la cotización.

    const detalles = [];

    document.querySelectorAll("#detalleCotizacion tr").forEach(fila => {

        detalles.push({

            descripcion: fila.querySelector(".descripcion").value.trim(),

            tipo: fila.querySelector(".tipo").value,

            cantidad: fila.querySelector(".cantidad").value,

            precio_unitario: fila.querySelector(".precio").value

        });

    });

    if (detalles.length === 0) {

        mostrarMensaje(
            "Debes agregar al menos un servicio o repuesto."
        );

        return;

    }

    // Validar los detalles antes de enviarlos.

    for (const detalle of detalles) {

        if (
            detalle.descripcion === "" ||
            Number(detalle.cantidad) < 1 ||
            !Number.isInteger(Number(detalle.cantidad)) ||
            Number(detalle.precio_unitario) < 0 ||
            !Number.isFinite(Number(detalle.precio_unitario))
        ) {

            mostrarMensaje(
                "Revisa las descripciones, cantidades y precios de los detalles."
            );

            return;

        }

    }

    // Preparar los datos.

    const datos = {

        codigo_orden: codigoOrdenConsultada,

        detalles: detalles

    };

    try {

        boton.disabled = true;

        mostrarMensaje("Registrando cotización...");

        // Enviar información al servidor.

        const respuesta = await fetch("../PHP/guardar_cotizacion.php", {

            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify(datos)

        });

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.exito) {

            mostrarMensaje(
                resultado.mensaje || "No se pudo registrar la cotización."
            );

            return;

        }

        // Mostrar confirmación.

        mostrarMensaje(
            "¡Cotización N.º " +
            resultado.id_cotizacion +
            " registrada correctamente!"
        );

        // Limpiar el formulario después de guardar.

        formulario.reset();

        tablaDetalles.innerHTML = "";

        codigoOrdenConsultada = "";

        calcularTotales();

    } catch (error) {

        mostrarMensaje(
            "Ocurrió un error al comunicarse con el servidor."
        );

    } finally {

        boton.disabled = false;

    }

});


// INICIAR

calcularTotales();

</script>

</body>
</html>
