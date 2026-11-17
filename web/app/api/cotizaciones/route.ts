import { Prisma } from "@prisma/client";
import { NextResponse } from "next/server";
import { z } from "zod";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";

const schema = z.object({
  codigoOrden: z.string().trim().min(1).max(30),
  detalles: z.array(z.object({
    descripcion: z.string().trim().min(1).max(150), tipo: z.enum(["Servicio", "Repuesto"]),
    cantidad: z.number().int().positive(), precio: z.string().regex(/^\d{1,8}(\.\d{1,2})?$/),
  })).min(1),
});

export async function POST(request: Request) {
  const session = await auth();
  if (!session?.user) return NextResponse.json({ mensaje: "Sesión requerida" }, { status: 401 });
  if (session.user.rol !== "recepcionista") return NextResponse.json({ mensaje: "Acceso denegado" }, { status: 403 });
  let body: unknown;
  try { body = await request.json(); } catch { return NextResponse.json({ mensaje: "JSON inválido" }, { status: 400 }); }
  const parsed = schema.safeParse(body);
  if (!parsed.success) return NextResponse.json({ mensaje: "Datos inválidos", errores: parsed.error.flatten() }, { status: 400 });
  try {
    const result = await prisma.$transaction(async (tx) => {
      const orden = await tx.ordenServicio.findUnique({ where: { codigoOrden: parsed.data.codigoOrden }, select: { idOrden: true } });
      if (!orden) return null;
      const detalles = parsed.data.detalles.map((item) => {
        const precioUnitario = new Prisma.Decimal(item.precio);
        return { descripcion: item.descripcion, tipo: item.tipo, cantidad: item.cantidad, precioUnitario, totalItem: precioUnitario.mul(item.cantidad) };
      });
      const subtotal = detalles.reduce((sum, item) => sum.plus(item.totalItem), new Prisma.Decimal(0));
      const impuesto = subtotal.mul("0.07").toDecimalPlaces(2, Prisma.Decimal.ROUND_HALF_UP);
      return tx.cotizacion.create({ data: {
        idOrden: orden.idOrden, subtotal, impuesto, total: subtotal.plus(impuesto),
        detalles: { create: detalles },
      }, select: { idCotizacion: true, subtotal: true, impuesto: true, total: true } });
    });
    if (!result) return NextResponse.json({ mensaje: "Orden no encontrada" }, { status: 409 });
    return NextResponse.json({ idCotizacion: result.idCotizacion, subtotal: result.subtotal.toFixed(2), impuesto: result.impuesto.toFixed(2), total: result.total.toFixed(2) });
  } catch (error) {
    if (error instanceof Prisma.PrismaClientKnownRequestError && error.code === "P2002")
      return NextResponse.json({ mensaje: "Esta orden ya tiene una cotización" }, { status: 409 });
    console.error("POST /api/cotizaciones", error);
    return NextResponse.json({ mensaje: "Error interno" }, { status: 500 });
  }
}
