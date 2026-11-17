import { PrismaClient } from "@prisma/client";

const db = new PrismaClient();

async function main() {
  // Keep the dump's IDs, timestamps, hashes and monetary values intact.
  await db.rol.createMany({ data: [
    { idRol: 1, nombreRol: "Mecanico" },
    { idRol: 2, nombreRol: "Recepcionista" },
  ], skipDuplicates: true });
  await db.usuario.createMany({ data: [
    { idUsuario: 1, usuario: "Asadmin", contrasena: "$2y$10$PFLZiMC7aXu7HUUbTEg1U.Vh38cOesrA.z24asidLPQ2d/3hOgZk2", idRol: 2 },
    { idUsuario: 2, usuario: "AsaCorredor", contrasena: "$2y$10$vot.uRwFOpnTBN6.5R3bkeQwCUjduPF7DfXGC1dfJ/wwKK0QMku76", idRol: 1 },
    { idUsuario: 3, usuario: "Ebernal", contrasena: "$2y$10$UiHmDyKQzhfw4KdIr1PL.uHSrj.SoMY0zoGz3LP3gMw/dAQKs2wka", idRol: 1 },
    { idUsuario: 4, usuario: "Rosibelpd", contrasena: "$2y$10$NiARFI0v0NQQpltnTfQqA..z7l742b3mM1z7mK2AnFG1jESOamOOO", idRol: 2 },
    { idUsuario: 5, usuario: "TuñonE", contrasena: "$2y$10$p9T/6yXtAu9tZ1qLzjRKvuy29IBv4H0.RHzBi2dpld5cqZX3t4K3y", idRol: 1 },
    { idUsuario: 6, usuario: "Ejimenez", contrasena: "$2y$10$ZaE2kilsud5P2J8iqZb9WON4JdTD1uFCN5Vr1K8XLfr6pY1AndNGO", idRol: 2 },
  ], skipDuplicates: true });
  await db.cliente.createMany({ data: [
    { idCliente: 1, nombre: "Omar", primerApellido: "Liebharto" },
    { idCliente: 2, nombre: "Clara", primerApellido: "Falcón", segundoApellido: "Jimenez", telefono: "6871-9169", email: "claraisabel@gmail.com" },
  ], skipDuplicates: true });
  await db.vehiculo.createMany({ data: [
    { idVehiculo: 1, idCliente: 1, placa: "LMN-4567", modelo: "Honda", marca: "Sedán" },
    { idVehiculo: 2, idCliente: 2, placa: "SDM6021", modelo: "Corolla", marca: "Toyota", anio: 2027, kilometraje: 49998 },
  ], skipDuplicates: true });
  await db.ordenServicio.createMany({ data: [
    { idOrden: 1, codigoOrden: "AT-261116-1586", idVehiculo: 1, fechaIngreso: new Date("2026-11-15T20:28:43Z"), estadoOrden: "Abierta" },
    { idOrden: 2, codigoOrden: "AT-261116-2229", idVehiculo: 2, fechaIngreso: new Date("2026-11-15T21:37:18Z"), estadoOrden: "Abierta" },
  ], skipDuplicates: true });
  await db.diagnosticoReparacion.createMany({ data: [
    { idDiagnostico: 1, idOrden: 1, descripcionFall: "Mantenimiento en general", tareaRealiz: "Pintura y cambio de frenos" },
    { idDiagnostico: 2, idOrden: 2, descripcionFall: "prueba", tareaRealiz: "prueba" },
  ], skipDuplicates: true });
  await db.repuestoUtilizado.createMany({ data: [
    { idRepuestoUtil: 1, idDiagnostico: 1, cantidad: 1, precioUnitario: "15.00", totalItem: "15.00", descripcion: "Pintura" },
    { idRepuestoUtil: 2, idDiagnostico: 1, cantidad: 1, precioUnitario: "15.00", totalItem: "15.00", descripcion: "Liquido de freno" },
    { idRepuestoUtil: 3, idDiagnostico: 1, cantidad: 1, precioUnitario: "25.00", totalItem: "25.00", descripcion: "Patilla de freno" },
    { idRepuestoUtil: 4, idDiagnostico: 1, cantidad: 1, precioUnitario: "15.00", totalItem: "15.00", descripcion: "Disco de frenos" },
    { idRepuestoUtil: 5, idDiagnostico: 2, cantidad: 1, precioUnitario: "50.00", totalItem: "50.00", descripcion: "prueba" },
  ], skipDuplicates: true });
  await db.inventarioRepuesto.createMany({ data: [
    { idRepuesto: 1, nombre: "Filtro de aceite", descripcion: "Filtro para lubricación del motor", stockActual: 20, stockMinimo: 5, precioBase: "8.50" },
    { idRepuesto: 2, nombre: "Filtro de aire", descripcion: "Filtro de admisión de aire", stockActual: 15, stockMinimo: 5, precioBase: "10.00" },
    { idRepuesto: 3, nombre: "Aceite 10W-30", descripcion: "Aceite para motor, envase de 1 litro", stockActual: 30, stockMinimo: 10, precioBase: "12.00" },
    { idRepuesto: 4, nombre: "Pastillas de freno", descripcion: "Juego de pastillas delanteras", stockActual: 12, stockMinimo: 4, precioBase: "28.00" },
    { idRepuesto: 5, nombre: "Bujías", descripcion: "Bujías de encendido", stockActual: 25, stockMinimo: 6, precioBase: "6.50" },
    { idRepuesto: 6, nombre: "Batería 12V", descripcion: "Batería para automóvil", stockActual: 8, stockMinimo: 2, precioBase: "75.00" },
    { idRepuesto: 7, nombre: "Refrigerante", descripcion: "Líquido refrigerante para motor", stockActual: 20, stockMinimo: 5, precioBase: "9.50" },
    { idRepuesto: 8, nombre: "Filtro de combustible", descripcion: "Filtro para sistema de combustible", stockActual: 10, stockMinimo: 3, precioBase: "15.00" },
    { idRepuesto: 9, nombre: "Correa de distribución", descripcion: "Correa para sincronización del motor", stockActual: 6, stockMinimo: 2, precioBase: "45.00" },
    { idRepuesto: 10, nombre: "Limpiaparabrisas", descripcion: "Juego de escobillas", stockActual: 15, stockMinimo: 4, precioBase: "14.00" },
  ], skipDuplicates: true });
  await db.servicio.createMany({ data: [
    { idServicio: 1, nombre: "Cambio de aceite", descripcion: "Sustitución del aceite del motor", precio: "15.00" },
    { idServicio: 2, nombre: "Alineación", descripcion: "Ajuste de dirección y ruedas", precio: "25.00" },
    { idServicio: 3, nombre: "Balanceo", descripcion: "Equilibrado de neumáticos", precio: "20.00" },
    { idServicio: 4, nombre: "Diagnóstico computarizado", descripcion: "Revisión mediante escáner", precio: "30.00" },
    { idServicio: 5, nombre: "Cambio de pastillas", descripcion: "Sustitución de pastillas de freno", precio: "25.00" },
    { idServicio: 6, nombre: "Revisión de frenos", descripcion: "Inspección del sistema de frenado", precio: "15.00" },
    { idServicio: 7, nombre: "Cambio de batería", descripcion: "Instalación de batería nueva", precio: "10.00" },
    { idServicio: 8, nombre: "Mantenimiento preventivo", descripcion: "Revisión general del vehículo", precio: "45.00" },
  ], skipDuplicates: true });
  await db.cotizacion.createMany({ data: [
    { idCotizacion: 1, idOrden: 1, subtotal: "70.00", impuesto: "4.90", total: "74.90", fechaEmision: new Date("2026-11-15T20:54:01Z") },
    { idCotizacion: 2, idOrden: 2, subtotal: "50.00", impuesto: "3.50", total: "53.50", fechaEmision: new Date("2026-11-15T21:38:07Z") },
  ], skipDuplicates: true });
  await db.detalleCotizacion.createMany({ data: [
    { idDetalle: 1, idCotizacion: 1, descripcion: "Pintura", tipo: "Repuesto", cantidad: 1, precioUnitario: "15.00", totalItem: "15.00" },
    { idDetalle: 2, idCotizacion: 1, descripcion: "Liquido de freno", tipo: "Repuesto", cantidad: 1, precioUnitario: "15.00", totalItem: "15.00" },
    { idDetalle: 3, idCotizacion: 1, descripcion: "Patilla de freno", tipo: "Repuesto", cantidad: 1, precioUnitario: "25.00", totalItem: "25.00" },
    { idDetalle: 4, idCotizacion: 1, descripcion: "Disco de frenos", tipo: "Repuesto", cantidad: 1, precioUnitario: "15.00", totalItem: "15.00" },
    { idDetalle: 5, idCotizacion: 2, descripcion: "prueba", tipo: "Repuesto", cantidad: 1, precioUnitario: "50.00", totalItem: "50.00" },
  ], skipDuplicates: true });
  await db.pagoCuenta.createMany({ data: [
    { idPago: 1, idOrden: 1, idCotizacion: 1, montoPagar: "74.90", montoRecibido: "74.90", metodoPago: "Tarjeta", fechaPago: new Date("2026-11-15T20:54:27Z") },
    { idPago: 2, idOrden: 2, idCotizacion: 2, montoPagar: "53.50", montoRecibido: "53.50", metodoPago: "Tarjeta", fechaPago: new Date("2026-11-15T21:38:26Z") },
  ], skipDuplicates: true });

  // Explicit dump IDs do not advance PostgreSQL's identity sequences.
  for (const [table, key] of [
    ["clientes", "id_cliente"], ["vehiculos", "id_vehiculo"],
    ["ordenes_servicio", "id_orden"], ["diagnostico_reparacion", "id_diagnostico"],
    ["repuestos_utilizados", "id_repuesto_util"], ["inventario_repuesto", "id_repuesto"],
    ["cotizaciones", "id_cotizacion"], ["detalle_cotizacion", "id_detalle"],
    ["pago_cuenta", "id_pago"], ["roles", "id_rol"], ["usuarios", "id_usuario"],
    ["servicios", "id_servicio"],
  ] as const) {
    await db.$queryRawUnsafe(
      `SELECT setval(pg_get_serial_sequence('"${table}"', '${key}'), COALESCE((SELECT MAX("${key}") FROM "${table}"), 1), (SELECT COUNT(*) > 0 FROM "${table}"))`,
    );
  }
}

main().finally(async () => db.$disconnect()).catch((error: unknown) => {
  console.error(error);
  process.exitCode = 1;
});
