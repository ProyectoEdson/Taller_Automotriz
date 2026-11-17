<?php

require_once "PHP/proteger_sesion.php";

if ($_SESSION["rol"] !== "mecanico") {
    header("Location: VISTAS/INICIO.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico y Reparación</title>
    <link rel="stylesheet" href="CSS/AUTOMOTRIZ.CSS">
</head>

<body>

<div class="contenedor">

    <header class="encabezado">

        <div class="logo">

            <svg viewBox="0 0 80 65" class="auto">
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

                MECÁNICO

            </span>

            <a href="PHP/cerrar_sesion.php">
                CERRAR SESIÓN
            </a>

        </div>

    </header>


    <div class="estructura">

        <aside class="menu">

            <a href="VISTAS/INICIO.php" class="opcion">

                <span class="icono">

                    <svg viewBox="0 0 24 24" width="24" height="24"
                        fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">

                        <path d="m3 10 9-7 9 7"/>
                        <path d="M5 9v11h14V9"/>
                        <path d="M9 20v-7h6v7"/>

                    </svg>

                </span>

                INICIO

            </a>


            <a href="INDEX.php" class="opcion seleccionado">

                <span class="icono">

                    <svg viewBox="0 0 24 24" width="24" height="24"
                        fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">

                        <path d="M14.7 6.3a5 5 0 0 0-6.4 6.4L3 18l3 3 5.3-5.3a5 5 0 0 0 6.4-6.4l-3.1 3.1-3-3z"/>

                    </svg>

                </span>

                <span>DIAGNÓSTICO Y<br> REPARACIÓN</span>

            </a>

        </aside>


        <main class="contenido">

            <h1>REGISTRAR DIAGNÓSTICO Y REPARACIÓN</h1>


            <label for="ordenTrabajo">ORDEN DE TRABAJO</label>

            <select id="ordenTrabajo">
                <option value="">Nueva orden (código automático)</option>
            </select>

            <p id="avisoOrdenes" class="aviso-ordenes" hidden></p>


            <!-- ORDEN EXISTENTE: DATOS EN SOLO LECTURA -->

            <section class="informacion gris" id="panelOrdenExistente" hidden>

                <h3>INFORMACIÓN DEL VEHÍCULO</h3>

                <div class="datos-vehiculo">

                    <span>CLIENTE:</span>
                    <strong id="lecturaCliente">—</strong>

                    <span>VEHÍCULO:</span>
                    <strong id="lecturaVehiculo">—</strong>

                    <span>PLACA:</span>
                    <strong id="lecturaPlaca">—</strong>

                </div>

            </section>


            <!-- ORDEN NUEVA: DATOS EDITABLES -->

            <div id="panelNuevaOrden">

                <section class="informacion gris">

                    <h3>INFORMACIÓN DEL CLIENTE</h3>

                    <div class="datos-vehiculo">

                        <span>NOMBRE:</span>

                        <input
                            type="text"
                            id="nombre"
                            maxlength="100"
                            placeholder="Nombre del cliente"
                        >


                        <span>PRIMER APELLIDO:</span>

                        <input
                            type="text"
                            id="primerApellido"
                            maxlength="100"
                            placeholder="Primer apellido"
                        >


                        <span>SEGUNDO APELLIDO:</span>

                        <input
                            type="text"
                            id="segundoApellido"
                            maxlength="100"
                            placeholder="Segundo apellido"
                        >


                        <span>TELÉFONO:</span>

                        <input
                            type="tel"
                            id="telefono"
                            maxlength="20"
                            placeholder="Teléfono del cliente"
                        >


                        <span>EMAIL:</span>

                        <input
                            type="email"
                            id="email"
                            maxlength="100"
                            placeholder="Correo electrónico"
                        >

                    </div>

                </section>


                <section class="informacion gris">

                    <h3>INFORMACIÓN DEL VEHÍCULO</h3>

                    <div class="datos-vehiculo">

                        <span>MARCA:</span>

                        <input
                            type="text"
                            id="marca"
                            maxlength="50"
                            placeholder="Ejemplo: TOYOTA"
                        >


                        <span>MODELO:</span>

                        <input
                            type="text"
                            id="modelo"
                            maxlength="50"
                            placeholder="Ejemplo: COROLLA"
                        >


                        <span>PLACA:</span>

                        <input
                            type="text"
                            id="placa"
                            maxlength="15"
                            placeholder="Placa del vehículo"
                        >


                        <span>AÑO:</span>

                        <input
                            type="number"
                            id="anio"
                            min="1900"
                            max="2100"
                            placeholder="Ejemplo: 2020"
                        >


                        <span>KILOMETRAJE:</span>

                        <input
                            type="number"
                            id="kilometraje"
                            min="0"
                            step="1"
                            placeholder="Kilometraje actual"
                        >

                    </div>

                </section>

            </div>


            <label for="diagnostico">DIAGNÓSTICO</label>

            <textarea
                id="diagnostico"
                maxlength="5000"
                placeholder="Describe el problema encontrado..."
            ></textarea>


            <label for="reparaciones">REPARACIONES REALIZADAS</label>

            <textarea
                id="reparaciones"
                maxlength="5000"
                placeholder="Describe las reparaciones realizadas..."
            ></textarea>


            <section class="repuestos">

                <label>REPUESTO UTILIZADO</label>

                <table>

                    <thead>

                        <tr>
                            <th>Repuesto</th>
                            <th>Cantidad</th>
                            <th>Precio unitario</th>
                            <th>Total</th>
                        </tr>

                    </thead>

                    <tbody id="tablaRepuestos"></tbody>

                </table>


                <div class="nuevo-repuesto" id="nuevoRepuestoForm" hidden>

                    <input
                        type="text"
                        id="nuevoRepuestoNombre"
                        maxlength="150"
                        placeholder="Nombre del repuesto"
                    >

                    <input
                        type="number"
                        id="nuevoRepuestoCantidad"
                        value="1"
                        min="1"
                        step="1"
                        placeholder="Cantidad"
                    >

                    <input
                        type="number"
                        id="nuevoRepuestoPrecio"
                        value="0"
                        min="0"
                        step="0.01"
                        placeholder="Precio"
                    >

                    <button type="button" id="btnConfirmarRepuesto">
                        Añadir
                    </button>

                </div>


                <div class="acciones">

                    <button type="button" id="btnAgregarRepuesto">
                        Agregar Repuesto
                    </button>

                </div>

            </section>


            <div class="acciones">

                <button type="button" id="btnGuardarDiagnostico">
                    Guardar Diagnóstico
                </button>

            </div>

        </main>

    </div>

</div>


<script>

// =================================
// REFERENCIAS
// =================================

const selectOrden = document.getElementById("ordenTrabajo");

const avisoOrdenes = document.getElementById("avisoOrdenes");

const panelOrdenExistente =
    document.getElementById("panelOrdenExistente");

const panelNuevaOrden =
    document.getElementById("panelNuevaOrden");

const lecturaCliente = document.getElementById("lecturaCliente");

const lecturaVehiculo = document.getElementById("lecturaVehiculo");

const lecturaPlaca = document.getElementById("lecturaPlaca");

const tablaRepuestos = document.getElementById("tablaRepuestos");

const formRepuesto = document.getElementById("nuevoRepuestoForm");

const btnAgregarRepuesto =
    document.getElementById("btnAgregarRepuesto");

const btnConfirmarRepuesto =
    document.getElementById("btnConfirmarRepuesto");

const btnGuardarDiagnostico =
    document.getElementById("btnGuardarDiagnostico");

let repuestos = [];

let cargaActual = 0;


// =================================
// UTILIDADES
// =================================

function formatoDinero(valor) {

    return "$ " + Number(valor).toFixed(2);

}


function mostrarAviso(mensaje) {

    avisoOrdenes.textContent = mensaje;

    avisoOrdenes.hidden = mensaje === "";

}


// =================================
// REPUESTOS
// =================================

function renderRepuestos() {

    tablaRepuestos.innerHTML = "";

    repuestos.forEach(function (repuesto, indice) {

        const fila = document.createElement("tr");

        const celdaNombre = document.createElement("td");

        const nombre = document.createElement("span");

        nombre.textContent = repuesto.nombre;

        const eliminar = document.createElement("button");

        eliminar.type = "button";

        eliminar.className = "btn-eliminar-repuesto";

        eliminar.title = "Quitar repuesto";

        eliminar.textContent = "×";

        eliminar.addEventListener("click", function () {

            repuestos.splice(indice, 1);

            renderRepuestos();

        });

        celdaNombre.appendChild(nombre);

        celdaNombre.appendChild(eliminar);

        fila.appendChild(celdaNombre);

        const celdaCantidad = document.createElement("td");

        celdaCantidad.textContent = repuesto.cantidad;

        fila.appendChild(celdaCantidad);

        const celdaPrecio = document.createElement("td");

        celdaPrecio.textContent = formatoDinero(repuesto.precio);

        fila.appendChild(celdaPrecio);

        const celdaTotal = document.createElement("td");

        celdaTotal.textContent =
            formatoDinero(repuesto.cantidad * repuesto.precio);

        fila.appendChild(celdaTotal);

        tablaRepuestos.appendChild(fila);

    });

}


function agregarRepuesto() {

    const nombre = document
        .getElementById("nuevoRepuestoNombre")
        .value
        .trim();

    const cantidad = Number(
        document.getElementById("nuevoRepuestoCantidad").value
    );

    const precio = Number(
        document.getElementById("nuevoRepuestoPrecio").value
    );

    if (nombre === "") {

        alert("Escribe el nombre del repuesto.");

        return;

    }

    if (
        !Number.isInteger(cantidad) ||
        cantidad <= 0 ||
        !Number.isFinite(precio) ||
        precio < 0
    ) {

        alert("Verifica la cantidad y el precio del repuesto.");

        return;

    }

    repuestos.push({
        nombre: nombre,
        cantidad: cantidad,
        precio: precio
    });

    renderRepuestos();

    document.getElementById("nuevoRepuestoNombre").value = "";

    document.getElementById("nuevoRepuestoCantidad").value = "1";

    document.getElementById("nuevoRepuestoPrecio").value = "0";

    formRepuesto.hidden = true;

    document.getElementById("nuevoRepuestoNombre").focus();

}


// =================================
// CARGAR ÓRDENES
// =================================

async function cargarOrdenes(idSeleccionar = "") {

    const valorActual = idSeleccionar || selectOrden.value;

    try {

        const respuesta = await fetch("PHP/listar_ordenes.php");

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.exito) {

            throw new Error(
                resultado.mensaje || "No se pudieron cargar las órdenes."
            );

        }

        selectOrden.innerHTML =
            '<option value="">Nueva orden (código automático)</option>';

        resultado.ordenes.forEach(function (orden) {

            const opcion = document.createElement("option");

            opcion.value = orden.id_orden;

            opcion.textContent =
                orden.codigo_orden + " — " + orden.placa;

            selectOrden.appendChild(opcion);

        });

        if (valorActual) {

            selectOrden.value = String(valorActual);

        } else if (selectOrden.options.length > 1) {

            // Abrir en la orden más reciente para mostrar los datos
            // en solo lectura, como en el diseño.

            selectOrden.selectedIndex = 1;

        }

        mostrarAviso("");

    } catch (error) {

        mostrarAviso(
            "No se pudieron cargar las órdenes. " + error.message
        );

    }

}


// =================================
// CARGAR ORDEN SELECCIONADA
// =================================

async function cargarOrden(idOrden) {

    const token = ++cargaActual;

    try {

        const respuesta = await fetch(
            "PHP/consultar_diagnostico.php",
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    id_orden: Number(idOrden)
                })
            }
        );

        const resultado = await respuesta.json();

        if (token !== cargaActual) {
            return;
        }

        if (!respuesta.ok || !resultado.exito) {

            throw new Error(
                resultado.mensaje || "No se pudo cargar la orden."
            );

        }

        lecturaCliente.textContent = resultado.cliente;

        lecturaVehiculo.textContent = resultado.vehiculo;

        lecturaPlaca.textContent = resultado.placa;

        document.getElementById("diagnostico").value =
            resultado.diagnostico || "";

        document.getElementById("reparaciones").value =
            resultado.reparaciones || "";

        repuestos = (resultado.repuestos || []).map(function (repuesto) {

            return {
                nombre: repuesto.descripcion,
                cantidad: Number(repuesto.cantidad),
                precio: Number(repuesto.precio_unitario)
            };

        });

        renderRepuestos();

        mostrarAviso("");

    } catch (error) {

        if (token !== cargaActual) {
            return;
        }

        mostrarAviso("No se pudo cargar la orden. " + error.message);

    }

}


// =================================
// CAMBIAR ENTRE ORDEN EXISTENTE Y NUEVA
// =================================

async function cambiarModo() {

    const idOrden = selectOrden.value;

    const esExistente = idOrden !== "";

    panelOrdenExistente.hidden = !esExistente;

    panelNuevaOrden.hidden = esExistente;

    if (esExistente) {

        await cargarOrden(idOrden);

        return;

    }

    cargaActual++;

    limpiarFormulario();

}


function limpiarFormulario() {

    ["nombre", "primerApellido", "segundoApellido", "telefono",
     "email", "marca", "modelo", "placa", "anio", "kilometraje"]
        .forEach(function (id) {

            document.getElementById(id).value = "";

        });

    document.getElementById("diagnostico").value = "";

    document.getElementById("reparaciones").value = "";

    lecturaCliente.textContent = "—";

    lecturaVehiculo.textContent = "—";

    lecturaPlaca.textContent = "—";

    repuestos = [];

    renderRepuestos();

}


// =================================
// OBTENER DATOS DEL CLIENTE (ORDEN NUEVA)
// =================================

function obtenerDatosCliente() {

    return {

        nombre: document.getElementById("nombre").value.trim(),

        primerApellido:
            document.getElementById("primerApellido").value.trim(),

        segundoApellido:
            document.getElementById("segundoApellido").value.trim(),

        telefono: document.getElementById("telefono").value.trim(),

        email: document.getElementById("email").value.trim(),

        marca: document.getElementById("marca").value.trim(),

        modelo: document.getElementById("modelo").value.trim(),

        placa: document.getElementById("placa").value.trim(),

        anio: document.getElementById("anio").value.trim(),

        kilometraje:
            document.getElementById("kilometraje").value.trim()

    };

}


// =================================
// GUARDAR DIAGNÓSTICO
// =================================

async function guardarDiagnostico() {

    const idOrden = selectOrden.value;

    const diagnostico =
        document.getElementById("diagnostico").value.trim();

    const reparaciones =
        document.getElementById("reparaciones").value.trim();

    if (diagnostico === "" || reparaciones === "") {

        alert("Completa el diagnóstico y las reparaciones.");

        return;

    }

    const datos = new FormData();

    if (idOrden !== "") {

        datos.append("id_orden", idOrden);

    } else {

        const cliente = obtenerDatosCliente();

        if (
            !cliente.nombre ||
            !cliente.primerApellido ||
            !cliente.marca ||
            !cliente.modelo ||
            !cliente.placa
        ) {

            alert(
                "Completa los datos del cliente y del vehículo: " +
                "nombre, primer apellido, marca, modelo y placa."
            );

            return;

        }

        if (
            cliente.email !== "" &&
            !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cliente.email)
        ) {

            alert("Escribe un correo electrónico válido.");

            return;

        }

        if (
            cliente.anio !== "" &&
            (Number(cliente.anio) < 1900 || Number(cliente.anio) > 2100)
        ) {

            alert("Verifica el año del vehículo.");

            return;

        }

        if (
            cliente.kilometraje !== "" &&
            Number(cliente.kilometraje) < 0
        ) {

            alert("El kilometraje no puede ser negativo.");

            return;

        }

        datos.append("nombre", cliente.nombre);

        datos.append("primerapellido", cliente.primerApellido);

        datos.append("segundoapellido", cliente.segundoApellido);

        datos.append("telefono", cliente.telefono);

        datos.append("email", cliente.email);

        datos.append("marca", cliente.marca);

        datos.append("modelo", cliente.modelo);

        datos.append("placa", cliente.placa);

        datos.append("anio", cliente.anio);

        datos.append("kilometraje", cliente.kilometraje);

    }

    datos.append("diagnostico", diagnostico);

    datos.append("reparaciones", reparaciones);

    repuestos.forEach(function (repuesto, indice) {

        datos.append(`repuestos[${indice}][nombre]`, repuesto.nombre);

        datos.append(`repuestos[${indice}][cantidad]`, repuesto.cantidad);

        datos.append(`repuestos[${indice}][precio]`, repuesto.precio);

    });

    btnGuardarDiagnostico.disabled = true;

    btnGuardarDiagnostico.textContent = "Guardando...";

    try {

        const respuesta = await fetch(
            "PHP/guardar_diagnostico.php",
            {
                method: "POST",
                body: datos
            }
        );

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.exito) {

            throw new Error(
                resultado.mensaje || "No se pudo guardar el diagnóstico."
            );

        }

        alert(
            resultado.mensaje +
            "\nCódigo de orden: " +
            resultado.codigo_orden
        );

        await cargarOrdenes(resultado.id_orden);

        await cambiarModo();

    } catch (error) {

        alert("Error: " + error.message);

    } finally {

        btnGuardarDiagnostico.disabled = false;

        btnGuardarDiagnostico.textContent = "Guardar Diagnóstico";

    }

}


// =================================
// EVENTOS
// =================================

selectOrden.addEventListener("change", cambiarModo);

btnAgregarRepuesto.addEventListener("click", function () {

    formRepuesto.hidden = !formRepuesto.hidden;

    if (!formRepuesto.hidden) {

        document.getElementById("nuevoRepuestoNombre").focus();

    }

});

btnConfirmarRepuesto.addEventListener("click", agregarRepuesto);

["nuevoRepuestoNombre", "nuevoRepuestoCantidad", "nuevoRepuestoPrecio"]
    .forEach(function (id) {

        document.getElementById(id).addEventListener(
            "keydown",
            function (evento) {

                if (evento.key === "Enter") {

                    evento.preventDefault();

                    agregarRepuesto();

                }

            }
        );

    });

btnGuardarDiagnostico.addEventListener("click", guardarDiagnostico);


// =================================
// INICIO
// =================================

renderRepuestos();

cargarOrdenes().then(cambiarModo);

</script>

</body>
</html>
