// Ejecutar desde web/: node --env-file=.env scripts/phase5-matrix.mjs
// Reinicia y restaura exclusivamente la base PostgreSQL local de desarrollo.
import assert from "node:assert/strict";
import { spawn, spawnSync } from "node:child_process";
import { setTimeout as delay } from "node:timers/promises";
import { PrismaClient } from "@prisma/client";
import { readFileSync } from "node:fs";

assert.match(process.env.DATABASE_URL ?? "", /127\.0\.0\.1:5432\/taller_automotriz/);
assert.ok(process.env.AUTH_SECRET);
const db = new PrismaClient();
const countsSql = readFileSync("prisma/counts.sql", "utf8");
let server;
let output = "";

function command(program, args) {
  const result = spawnSync(program, args, { stdio: "inherit", env: process.env });
  assert.equal(result.status, 0, `${program} ${args.join(" ")} falló`);
}

function reset() {
  command("npx", ["prisma", "migrate", "reset", "--force"]);
  command("npx", ["prisma", "db", "seed"]);
}

async function counts(expected) {
  const [row] = await db.$queryRawUnsafe(countsSql);
  const actual = [row.ordenes, row.cotizaciones_pendientes, row.reparaciones_activas, row.pagos_pendientes].map(Number);
  assert.deepEqual(actual, expected);
  console.log(`Panel: ${actual.join("/")}`);
}

async function rows(expected) {
  const actual = await Promise.all([
    db.ordenServicio.count(), db.diagnosticoReparacion.count(),
    db.cotizacion.count(), db.pagoCuenta.count(),
  ]);
  assert.deepEqual(actual, expected);
  console.log(`Filas órdenes/diagnósticos/cotizaciones/pagos: ${actual.join("/")}`);
}

async function start(url, badDatabase = false) {
  output = "";
  server = spawn(process.execPath, ["node_modules/next/dist/bin/next", "dev", "--hostname", "127.0.0.1", "--port", "3100"], {
    detached: true, stdio: ["ignore", "pipe", "pipe"],
    env: { ...process.env, AUTH_URL: url,
      ...(badDatabase ? { DATABASE_URL: process.env.DATABASE_URL.replace(":5432/", ":65432/") } : {}),
    },
  });
  for (const stream of [server.stdout, server.stderr]) stream.on("data", (chunk) => { output += chunk.toString(); });
  for (let i = 0; i < 300; i++) {
    if (server.exitCode !== null) throw new Error(`Next terminó antes de responder:\n${output}`);
    try {
      const response = await fetch(`${url}/login`);
      if (response.status === 200) return;
    } catch { /* El servidor todavía arranca. */ }
    await delay(100);
  }
  throw new Error(`Next no inició:\n${output}`);
}

async function stop() {
  if (!server) return;
  const child = server;
  server = undefined;
  if (child.exitCode === null) {
    try { process.kill(-child.pid, "SIGTERM"); } catch (error) { if (error.code !== "ESRCH") throw error; }
    for (let i = 0; i < 50 && child.exitCode === null; i++) await delay(100);
    if (child.exitCode === null) process.kill(-child.pid, "SIGKILL");
  }
}

function storeCookies(jar, response) {
  for (const line of response.headers.getSetCookie()) {
    const [name, value] = line.split(";", 1)[0].split("=");
    jar.set(name, value);
  }
}

function cookie(jar) {
  return [...jar].map(([name, value]) => `${name}=${value}`).join("; ");
}

async function login(url, user, password, role) {
  const jar = new Map();
  const csrf = await fetch(`${url}/api/auth/csrf`);
  storeCookies(jar, csrf);
  const { csrfToken } = await csrf.json();
  const response = await fetch(`${url}/api/auth/callback/credentials`, {
    method: "POST", redirect: "manual",
    headers: { "Content-Type": "application/x-www-form-urlencoded", Cookie: cookie(jar) },
    body: new URLSearchParams({ usuario: user, password, rol: role, csrfToken }),
  });
  storeCookies(jar, response);
  assert.equal(response.status, 302);
  const session = await fetch(`${url}/api/auth/session`, { headers: { Cookie: cookie(jar) } });
  assert.equal((await session.json()).user.rol, role);
  return cookie(jar);
}

async function request(url, route, body, credentials, expected) {
  const response = await fetch(`${url}/api/${route}`, {
    method: "POST", headers: { "Content-Type": "application/json", ...(credentials ? { Cookie: credentials } : {}) },
    body: JSON.stringify(body),
  });
  const raw = await response.text();
  let result;
  try {
    result = JSON.parse(raw);
  } catch {
    // El middleware responde texto plano para el rol incorrecto.
    assert.equal(response.status, 403);
    assert.equal(raw, "Acceso denegado");
    result = { mensaje: raw };
  }
  if (expected !== undefined) assert.equal(response.status, expected, `${route}: ${JSON.stringify(result)}`);
  console.log(`${route} HTTP ${response.status} ${JSON.stringify(result)}`);
  Object.defineProperty(result, "__status", { value: response.status });
  return result;
}

async function main() {
  const url = "http://127.0.0.1:3100";
  reset();
  await counts([2, 0, 0, 0]);
  await rows([2, 2, 2, 2]);
  await start(url);
  const mechanic = await login(url, "AsaCorredor", "789456", "mecanico");
  const desk = await login(url, "Asadmin", "123456", "recepcionista");
  const update = { idOrden: 2, diagnostico: "Fase 5", reparaciones: "Verificar", repuestos: [] };
  const quoteInput = { codigoOrden: "AT-261116-1586", detalles: [{ descripcion: "Servicio", tipo: "Servicio", cantidad: 1, precio: "1.00" }] };
  const paymentInput = { idCotizacion: 1, monto: "1.00", metodo: "Efectivo" };

  for (const [route, body, wrongRole] of [
    ["ordenes", update, desk], ["cotizaciones", quoteInput, mechanic], ["pagos", paymentInput, mechanic],
  ]) {
    await request(url, route, body, null, 401);
    await request(url, route, body, wrongRole, 403);
  }
  for (const [path, credentials] of [["/diagnostico", desk], ["/cotizacion", mechanic], ["/pagos", mechanic]]) {
    const response = await fetch(`${url}${path}`, { headers: { Cookie: credentials }, redirect: "manual" });
    assert.equal(response.status, 403);
    console.log(`${path} HTTP 403`);
  }
  assert.equal((await fetch(`${url}/diagnostico`, { redirect: "manual" })).status, 307);
  await request(url, "ordenes", { ...update, diagnostico: "" }, mechanic, 400);
  await request(url, "cotizaciones", { ...quoteInput, detalles: [] }, desk, 400);
  await request(url, "pagos", { ...paymentInput, monto: "0.00" }, desk, 400);
  await request(url, "cotizaciones", quoteInput, desk, 409);
  await request(url, "pagos", paymentInput, desk, 409);

  const fixture = await db.inventarioRepuesto.create({ data: { nombre: "Filtro fixture", stockActual: 3, precioBase: "2.00" } });
  const newOrder = {
    nombre: "Fase", primerApellido: "Cinco", marca: "Ford", modelo: "Focus", placa: "P5-001",
    diagnostico: "Falla", reparaciones: "Cambio", repuestos: [{ nombre: "Filtro fixture", cantidad: 1, precio: "2.00" }],
  };
  await request(url, "ordenes", { ...newOrder, nombre: "X".repeat(101) }, mechanic, 400);
  const order = await request(url, "ordenes", newOrder, mechanic, 200);
  assert.match(order.codigoOrden, /^AT-\d{6}-\d{4}$/);
  await counts([3, 1, 1, 0]);
  assert.equal((await db.inventarioRepuesto.findUniqueOrThrow({ where: { idRepuesto: fixture.idRepuesto } })).stockActual, 2);
  const change = { idOrden: order.idOrden, diagnostico: "Segunda", reparaciones: "Prueba", repuestos: [{ nombre: "Filtro fixture", cantidad: 2, precio: "2.00" }] };
  const first = await request(url, "ordenes", change, mechanic, 200);
  assert.equal(first.idDiagnostico, order.idDiagnostico);
  assert.equal((await db.inventarioRepuesto.findUniqueOrThrow({ where: { idRepuesto: fixture.idRepuesto } })).stockActual, 1);
  const second = await request(url, "ordenes", { ...change, diagnostico: "Tercera", repuestos: [{ ...change.repuestos[0], cantidad: 1 }] }, mechanic, 200);
  assert.equal(second.idDiagnostico, first.idDiagnostico);
  assert.equal((await db.inventarioRepuesto.findUniqueOrThrow({ where: { idRepuesto: fixture.idRepuesto } })).stockActual, 2);
  await request(url, "ordenes", { ...change, repuestos: [{ ...change.repuestos[0], cantidad: 4 }] }, mechanic, 409);
  assert.equal((await db.inventarioRepuesto.findUniqueOrThrow({ where: { idRepuesto: fixture.idRepuesto } })).stockActual, 2);
  assert.equal((await db.diagnosticoReparacion.findUniqueOrThrow({ where: { idOrden: order.idOrden } })).descripcionFall, "Tercera");
  await rows([3, 3, 2, 2]);
  assert.equal(await db.repuestoUtilizado.count({ where: { idDiagnostico: order.idDiagnostico } }), 1);

  const details = { codigoOrden: order.codigoOrden, detalles: [
    { descripcion: "Servicio", tipo: "Servicio", cantidad: 2, precio: "12.35" },
    { descripcion: "Filtro", tipo: "Repuesto", cantidad: 1, precio: "3.00" },
  ] };
  const quote = await request(url, "cotizaciones", details, desk, 200);
  assert.deepEqual([quote.subtotal, quote.impuesto, quote.total], ["27.70", "1.94", "29.64"]);
  await counts([3, 0, 1, 1]);
  await request(url, "cotizaciones", details, desk, 409);
  assert.equal(await db.cotizacion.count({ where: { idOrden: order.idOrden } }), 1);
  assert.equal(await db.detalleCotizacion.count({ where: { idCotizacion: quote.idCotizacion } }), 2);
  const pay = { idCotizacion: quote.idCotizacion, monto: "5.00", metodo: "Transferencia" };
  const partial = await request(url, "pagos", pay, desk, 200);
  assert.deepEqual([partial.saldo, partial.estado], ["24.64", "Pendiente"]);
  assert.equal((await db.pagoCuenta.findUniqueOrThrow({ where: { idPago: partial.idPago } })).montoRecibido.toFixed(2), "5.00");
  await counts([3, 0, 1, 1]);
  await request(url, "pagos", { ...pay, monto: "24.65" }, desk, 409);
  const full = await request(url, "pagos", { ...pay, monto: "24.64" }, desk, 200);
  assert.deepEqual([full.saldo, full.estado], ["0.00", "Pagado"]);
  assert.notEqual(full.idPago, partial.idPago);
  await request(url, "pagos", pay, desk, 409);
  await counts([3, 0, 0, 0]);

  const samePlate = await request(url, "ordenes", {
    nombre: "clara", primerApellido: "FALCÓN", segundoApellido: "jimenez", marca: "Honda", modelo: "Civic",
    placa: "sdm6021", anio: null, kilometraje: null, diagnostico: "Mantenimiento", reparaciones: "Cambio", repuestos: [],
  }, mechanic, 200);
  const vehicle = await db.vehiculo.findUniqueOrThrow({ where: { placa: "SDM6021" } });
  assert.deepEqual([vehicle.idVehiculo, vehicle.idCliente, vehicle.marca, vehicle.modelo, vehicle.anio, vehicle.kilometraje], [2, 2, "Honda", "Civic", 2027, 49998]);
  assert.equal((await db.ordenServicio.findUniqueOrThrow({ where: { idOrden: samePlate.idOrden } })).idVehiculo, 2);
  await request(url, "ordenes", { ...newOrder, nombre: "Ajeno", primerApellido: "Otro", placa: "SDM6021" }, mechanic, 409);
  await rows([4, 4, 3, 4]);
  console.log(`Placa propia: vehículo ${vehicle.idVehiculo}, año ${vehicle.anio}, km ${vehicle.kilometraje}`);

  const concurrentQuote = await request(url, "cotizaciones", { codigoOrden: samePlate.codigoOrden, detalles: [
    { descripcion: "Trabajo", tipo: "Servicio", cantidad: 1, precio: "100.00" },
  ] }, desk, 200);
  const simultaneous = await Promise.all([1, 2].map(() => request(url, "pagos", {
    idCotizacion: concurrentQuote.idCotizacion, monto: "75.00", metodo: "Tarjeta",
  }, desk, undefined).catch((error) => { throw error; })));
  const statuses = simultaneous.map((result) => result.__status);
  assert.deepEqual(statuses.sort(), [200, 409]);
  const sum = await db.pagoCuenta.aggregate({ where: { idCotizacion: concurrentQuote.idCotizacion }, _sum: { montoRecibido: true } });
  assert.equal(sum._sum.montoRecibido.toFixed(2), "75.00");
  console.log("Pagos simultáneos: HTTP 200/409; total recibido 75.00");
  await stop();
  await start(url, true);
  for (const [route, body, credentials] of [
    ["ordenes", update, mechanic], ["cotizaciones", details, desk], ["pagos", pay, desk],
  ]) {
    const result = await request(url, route, body, credentials, 500);
    assert.deepEqual(result, { mensaje: "Error interno" });
  }
  console.log("Fallo aislado: 500 genérico en los tres CU, sin SQL en respuestas");
}

try {
  await main();
} finally {
  await stop();
  await db.$disconnect();
  reset();
  const final = new PrismaClient();
  try {
    const [row] = await final.$queryRawUnsafe(countsSql);
    assert.deepEqual([row.ordenes, row.cotizaciones_pendientes, row.reparaciones_activas, row.pagos_pendientes].map(Number), [2, 0, 0, 0]);
    console.log("Seed final: panel 2/0/0/0");
  } finally {
    await final.$disconnect();
  }
}
