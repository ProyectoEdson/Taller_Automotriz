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

            <input
                type="text"
                id="ordenTrabajo"
                placeholder="Se genera automáticamente si lo dejas vacío"
            >


            <section class="informacion gris">

                <h3>INFORMACIÓN DEL CLIENTE</h3>

                <div class="datos-vehiculo">

                    <span>NOMBRE:</span>

                    <input
                        type="text"
                        id="nombre"
                        placeholder="Nombre del cliente"
                    >


                    <span>PRIMER APELLIDO:</span>

                    <input
                        type="text"
                        id="primerApellido"
                        placeholder="Primer apellido"
                    >


                    <span>SEGUNDO APELLIDO:</span>

                    <input
                        type="text"
                        id="segundoApellido"
                        placeholder="Segundo apellido"
                    >


                    <span>TELÉFONO:</span>

                    <input
                        type="tel"
                        id="telefono"
                        placeholder="Teléfono del cliente"
                    >


                    <span>EMAIL:</span>

                    <input
                        type="email"
                        id="email"
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
                        placeholder="Ejemplo: TOYOTA"
                    >


                    <span>MODELO:</span>

                    <input
                        type="text"
                        id="modelo"
                        placeholder="Ejemplo: COROLLA"
                    >


                    <span>PLACA:</span>

                    <input
                        type="text"
                        id="placa"
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


            <label for="diagnostico">DIAGNÓSTICO</label>

            <textarea
                id="diagnostico"
                placeholder="Describe el problema encontrado..."
            ></textarea>


            <label for="reparaciones">REPARACIONES REALIZADAS</label>

            <textarea
                id="reparaciones"
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

                    <tbody id="tablaRepuestos">

                        <tr>

                            <td>
                                <input
                                    type="text"
                                    class="nombre-repuesto"
                                    placeholder="Nombre del repuesto"
                                >
                            </td>

                            <td>
                                <input
                                    type="number"
                                    class="cantidad-repuesto"
                                    value="1"
                                    min="1"
                                >
                            </td>

                            <td>
                                <input
                                    type="number"
                                    class="precio-repuesto"
                                    value="0"
                                    min="0"
                                    step="0.01"
                                >
                            </td>

                            <td class="total-repuesto">$ 0.00</td>

                        </tr>

                    </tbody>

                </table>


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

const tablaRepuestos = document.getElementById("tablaRepuestos");

const btnAgregarRepuesto = document.getElementById("btnAgregarRepuesto");

const btnGuardarDiagnostico = document.getElementById("btnGuardarDiagnostico");


// =================================
// CALCULAR TOTAL
// =================================

function calcularTotal(fila) {

    const cantidad = Number(
        fila.querySelector(".cantidad-repuesto").value
    ) || 0;

    const precio = Number(
        fila.querySelector(".precio-repuesto").value
    ) || 0;

    const total = cantidad * precio;

    fila.querySelector(".total-repuesto").textContent =
        "$ " + total.toFixed(2);

}


// =================================
// AGREGAR REPUESTO
// =================================

function agregarRepuesto() {

    const fila = document.createElement("tr");

    fila.innerHTML = `
        <td>
            <input
                type="text"
                class="nombre-repuesto"
                placeholder="Nombre del repuesto"
            >
        </td>

        <td>
            <input
                type="number"
                class="cantidad-repuesto"
                value="1"
                min="1"
            >
        </td>

        <td>
            <input
                type="number"
                class="precio-repuesto"
                value="0"
                min="0"
                step="0.01"
            >
        </td>

        <td class="total-repuesto">$ 0.00</td>
    `;

    tablaRepuestos.appendChild(fila);

    calcularTotal(fila);

}


// =================================
// EVENTO AGREGAR REPUESTO
// =================================

btnAgregarRepuesto.addEventListener("click", agregarRepuesto);


// =================================
// ACTUALIZAR TOTALES
// =================================

tablaRepuestos.addEventListener("input", function(event) {

    if (
        event.target.classList.contains("cantidad-repuesto") ||
        event.target.classList.contains("precio-repuesto")
    ) {

        calcularTotal(event.target.closest("tr"));

    }

});


// =================================
// OBTENER REPUESTOS
// =================================

function obtenerRepuestos() {

    const filas = tablaRepuestos.querySelectorAll("tr");

    const repuestos = [];

    filas.forEach(function(fila) {

        const nombre = fila.querySelector(
            ".nombre-repuesto"
        ).value.trim();

        const cantidad = Number(
            fila.querySelector(".cantidad-repuesto").value
        );

        const precio = Number(
            fila.querySelector(".precio-repuesto").value
        );

        if (nombre !== "") {

            repuestos.push({
                nombre: nombre,
                cantidad: cantidad,
                precio: precio
            });

        }

    });

    return repuestos;

}


// =================================
// GUARDAR DIAGNÓSTICO
// =================================

async function guardarDiagnostico() {

    const orden = document.getElementById("ordenTrabajo").value.trim();

    const nombre = document.getElementById("nombre").value.trim();

    const primerApellido = document.getElementById("primerApellido").value.trim();

    const segundoApellido = document.getElementById("segundoApellido").value.trim();

    const telefono = document.getElementById("telefono").value.trim();

    const email = document.getElementById("email").value.trim();

    const marca = document.getElementById("marca").value.trim();

    const modelo = document.getElementById("modelo").value.trim();

    const placa = document.getElementById("placa").value.trim();

    const anio = document.getElementById("anio").value.trim();

    const kilometraje = document.getElementById("kilometraje").value.trim();

    const diagnostico = document.getElementById("diagnostico").value.trim();

    const reparaciones = document.getElementById("reparaciones").value.trim();

    const repuestos = obtenerRepuestos();


    // Validar campos obligatorios

    if (
        !nombre ||
        !primerApellido ||
        !marca ||
        !modelo ||
        !placa ||
        !diagnostico ||
        !reparaciones
    ) {

        alert("Completa los campos obligatorios: nombre, primer apellido, marca, modelo, placa, diagnóstico y reparaciones.");

        return;

    }


    // Validar email si fue escrito

    if (
        email !== "" &&
        !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
    ) {

        alert("Escribe un correo electrónico válido.");

        return;

    }


    // Validar año

    if (anio !== "" && (Number(anio) < 1900 || Number(anio) > 2100)) {

        alert("Verifica el año del vehículo.");

        return;

    }


    // Validar kilometraje

    if (kilometraje !== "" && Number(kilometraje) < 0) {

        alert("El kilometraje no puede ser negativo.");

        return;

    }


    // Validar repuestos

    for (const repuesto of repuestos) {

        if (
            !Number.isInteger(repuesto.cantidad) ||
            repuesto.cantidad <= 0 ||
            !Number.isFinite(repuesto.precio) ||
            repuesto.precio < 0
        ) {

            alert("Verifica las cantidades y los precios de los repuestos.");

            return;

        }

    }


    if (repuestos.length === 0) {

        if (!confirm("No agregaste repuestos. ¿Deseas continuar?")) {
            return;
        }

    }


    // Preparar información para PHP

    const datos = new FormData();

    datos.append("orden", orden);

    datos.append("nombre", nombre);
    datos.append("primerapellido", primerApellido);
    datos.append("segundoapellido", segundoApellido);
    datos.append("telefono", telefono);
    datos.append("email", email);

    datos.append("marca", marca);
    datos.append("modelo", modelo);
    datos.append("placa", placa);
    datos.append("anio", anio);
    datos.append("kilometraje", kilometraje);

    datos.append("diagnostico", diagnostico);
    datos.append("reparaciones", reparaciones);


    // Enviar repuestos como arreglo POST

    repuestos.forEach(function(repuesto, indice) {

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


        document.getElementById("ordenTrabajo").value =
            resultado.codigo_orden;


        alert(
            resultado.mensaje +
            "\nCódigo de orden: " +
            resultado.codigo_orden
        );


    } catch (error) {

        alert("Error: " + error.message);

    } finally {

        btnGuardarDiagnostico.disabled = false;

        btnGuardarDiagnostico.textContent = "Guardar Diagnóstico";

    }

}


// =================================
// EVENTO GUARDAR
// =================================

btnGuardarDiagnostico.addEventListener(
    "click",
    guardarDiagnostico
);


// =================================
// CALCULAR TOTALES INICIALES
// =================================

document.querySelectorAll("#tablaRepuestos tr").forEach(function(fila) {

    calcularTotal(fila);

});

</script>

</body>
</html>