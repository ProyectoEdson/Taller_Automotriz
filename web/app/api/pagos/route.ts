import { Prisma } from "@prisma/client";
import { NextResponse } from "next/server";
import { z } from "zod";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";

const schema = z.object({
  idCotizacion: z.number().int().positive(),
  monto: z.string().regex(/^\d{1,8}(\.\d{1,2})?$/),
  metodo: z.enum(["Efectivo", "Tarjeta", "Transferencia", "Yappy"]),
});
class Conflict extends Error {}

export async function POST(request: Request) {
  const session = await auth();
  if (!session?.user) return NextResponse.json({ mensaje: "Sesión requerida" }, { status: 401 });
  if (session.user.rol !== "recepcionista") return NextResponse.json({ mensaje: "Acceso denegado" }, { status: 403 });
  let body: unknown;
  try { body = await request.json(); } catch { return NextResponse.json({ mensaje: "JSON inválido" }, { status: 400 }); }
  const parsed = schema.safeParse(body);
  if (!parsed.success || (parsed.success && new Prisma.Decimal(parsed.data.monto).lte(0)))
    return NextResponse.json({ mensaje: "Datos inválidos" }, { status: 400 });

  try {
    const result = await prisma.$transaction(async (tx) => {
      // El bloqueo de la cabecera serializa los pagos concurrentes a la misma cotización.
      // Se lee SUM después de adquirirlo (READ COMMITTED): el segundo pago ve el primero.
      const rows = await tx.$queryRaw<{ id_orden: number; total: Prisma.Decimal }[]>`SELECT id_orden, total FROM cotizaciones WHERE id_cotizacion = ${parsed.data.idCotizacion} FOR UPDATE`;
      if (!rows.length) throw new Conflict("Cotización no encontrada");
      const cotizacion = rows[0];
      const pagado = await tx.pagoCuenta.aggregate({ where: { idCotizacion: parsed.data.idCotizacion }, _sum: { montoRecibido: true } });
      const saldo = cotizacion.total.minus(pagado._sum.montoRecibido ?? 0);
      const monto = new Prisma.Decimal(parsed.data.monto);
      if (saldo.lte(0)) throw new Conflict("Esta cotización ya está pagada");
      if (monto.gt(saldo)) throw new Conflict("El monto supera el saldo pendiente");
      const pago = await tx.pagoCuenta.create({ data: {
        idOrden: cotizacion.id_orden, idCotizacion: parsed.data.idCotizacion,
        montoPagar: cotizacion.total, montoRecibido: monto, metodoPago: parsed.data.metodo,
      } });
      const restante = saldo.minus(monto);
      return { idPago: pago.idPago, saldo: restante.toFixed(2), estado: restante.isZero() ? "Pagado" : "Pendiente", total: cotizacion.total.toFixed(2) };
    });
    return NextResponse.json(result);
  } catch (error) {
    if (error instanceof Conflict) return NextResponse.json({ mensaje: error.message }, { status: 409 });
    console.error("POST /api/pagos", error);
    return NextResponse.json({ mensaje: "Error interno" }, { status: 500 });
  }
}
