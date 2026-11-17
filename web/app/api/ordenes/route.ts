import { Prisma } from "@prisma/client";
import { randomInt } from "node:crypto";
import { NextResponse } from "next/server";
import { z } from "zod";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";

const money = z.string().regex(/^\d{1,8}(\.\d{1,2})?$/);
const text = (length: number) => z.string().trim().max(length);
const required = (length: number) => text(length).min(1);
const optional = (length: number) => text(length).optional().default("");
const part = z.object({ nombre: required(150), cantidad: z.number().int().positive().max(2147483647), precio: money });
const common = z.object({
  diagnostico: required(5000), reparaciones: required(5000), repuestos: z.array(part),
});
const input = z.union([
  common.extend({ idOrden: z.number().int().positive() }),
  common.extend({
    nombre: required(100), primerApellido: required(100), segundoApellido: optional(100),
    telefono: optional(20), email: z.union([z.literal(""), z.string().email().max(100)]).optional().default(""),
    marca: required(50), modelo: required(50), placa: required(15),
    anio: z.number().int().min(1900).max(2100).nullable().optional(),
    kilometraje: z.number().nonnegative().finite().nullable().optional(),
    codigoOrden: optional(30),
  }),
]);
class InvalidAmount extends Error {}

class Conflict extends Error {}

export async function POST(request: Request) {
  const session = await auth();
  if (!session?.user) return NextResponse.json({ mensaje: "Sesión requerida" }, { status: 401 });
  if (session.user.rol !== "mecanico") return NextResponse.json({ mensaje: "Acceso denegado" }, { status: 403 });
  let body: unknown;
  try { body = await request.json(); } catch { return NextResponse.json({ mensaje: "JSON inválido" }, { status: 400 }); }
  const parsed = input.safeParse(body);
  if (!parsed.success) return NextResponse.json({ mensaje: "Datos inválidos", errores: parsed.error.flatten() }, { status: 400 });

  try {
    const result = await prisma.$transaction(async (tx) => {
      const data = parsed.data;
      let idOrden: number;
      let codigoOrden: string;
      if ("idOrden" in data) {
        // Serializa actualizaciones de la misma orden, incluida la restitución de inventario.
        const rows = await tx.$queryRaw<{ codigo_orden: string }[]>`SELECT codigo_orden FROM ordenes_servicio WHERE id_orden = ${data.idOrden} FOR UPDATE`;
        if (!rows.length) throw new Conflict("La orden no existe");
        idOrden = data.idOrden;
        codigoOrden = rows[0].codigo_orden;
      } else {
        const existing = await tx.cliente.findFirst({
          where: { nombre: { equals: data.nombre, mode: "insensitive" }, primerApellido: { equals: data.primerApellido, mode: "insensitive" }, segundoApellido: { equals: data.segundoApellido || null, mode: "insensitive" } },
        });
        const cliente = existing ?? await tx.cliente.create({ data: {
          nombre: data.nombre, primerApellido: data.primerApellido, segundoApellido: data.segundoApellido || null,
          telefono: data.telefono || null, email: data.email || null,
        } });
        const placa = data.placa.toUpperCase();
        const found = await tx.vehiculo.findUnique({ where: { placa } });
        if (found && found.idCliente !== cliente.idCliente) throw new Conflict("La placa pertenece a otro cliente");
        const vehiculo = found ?? await tx.vehiculo.create({ data: {
          idCliente: cliente.idCliente, placa, marca: data.marca, modelo: data.modelo,
          anio: data.anio ?? null, kilometraje: data.kilometraje ?? null,
        } });
        codigoOrden = data.codigoOrden || `AT-${new Date().toISOString().slice(2, 10).replaceAll("-", "")}-${randomInt(0, 10000).toString().padStart(4, "0")}`;
        const orden = await tx.ordenServicio.create({ data: { codigoOrden, idVehiculo: vehiculo.idVehiculo } });
        idOrden = orden.idOrden;
      }
      const previous = await tx.diagnosticoReparacion.findUnique({ where: { idOrden }, include: { repuestos: true } });
      if (previous) {
        for (const item of previous.repuestos) {
          if (item.idRepuesto !== null) await tx.inventarioRepuesto.update({ where: { idRepuesto: item.idRepuesto }, data: { stockActual: { increment: item.cantidad } } });
        }
        await tx.repuestoUtilizado.deleteMany({ where: { idDiagnostico: previous.idDiagnostico } });
      }
      const diagnostic = await tx.diagnosticoReparacion.upsert({
        where: { idOrden },
        create: { idOrden, descripcionFall: data.diagnostico, tareaRealiz: data.reparaciones },
        update: { descripcionFall: data.diagnostico, tareaRealiz: data.reparaciones },
      });
      for (const item of data.repuestos) {
        const inventory = await tx.inventarioRepuesto.findFirst({ where: { nombre: { equals: item.nombre, mode: "insensitive" } }, orderBy: { idRepuesto: "asc" } });
        if (inventory) {
          // UPDATE condicional evita stock negativo incluso entre órdenes concurrentes.
          const changed = await tx.inventarioRepuesto.updateMany({ where: { idRepuesto: inventory.idRepuesto, stockActual: { gte: item.cantidad } }, data: { stockActual: { decrement: item.cantidad } } });
          if (!changed.count) throw new Conflict(`Stock insuficiente: ${item.nombre}`);
        }
        const price = new Prisma.Decimal(item.precio);
        const totalItem = price.mul(item.cantidad);
        if (totalItem.gt("99999999.99")) throw new InvalidAmount();
        await tx.repuestoUtilizado.create({ data: {
          idDiagnostico: diagnostic.idDiagnostico, idRepuesto: inventory?.idRepuesto ?? null,
          descripcion: item.nombre, cantidad: item.cantidad, precioUnitario: price, totalItem,
        } });
      }
      return { idOrden, codigoOrden, idDiagnostico: diagnostic.idDiagnostico };
    });
    return NextResponse.json(result);
  } catch (error) {
    if (error instanceof InvalidAmount) return NextResponse.json({ mensaje: "Importe de repuesto fuera de rango" }, { status: 400 });
    if (error instanceof Conflict || (error instanceof Prisma.PrismaClientKnownRequestError && error.code === "P2002"))
      return NextResponse.json({ mensaje: error instanceof Conflict ? error.message : "Código o placa ya registrados" }, { status: 409 });
    console.error("POST /api/ordenes", error);
    return NextResponse.json({ mensaje: "Error interno" }, { status: 500 });
  }
}
