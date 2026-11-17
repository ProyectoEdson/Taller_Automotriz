import { Prisma } from "@prisma/client";
import { auth } from "@/auth";
import WorkshopShell from "@/components/WorkshopShell";
import { prisma } from "@/lib/prisma";
import { redirect } from "next/navigation";
import PagosCliente from "./PagosCliente";

export const dynamic = "force-dynamic";

export default async function Pagos() {
  const session = await auth();
  if (!session?.user) redirect("/login");
  if (session.user.rol !== "recepcionista") redirect("/inicio");

  const cotizaciones = await prisma.cotizacion.findMany({
    orderBy: { idCotizacion: "desc" },
    select: {
      idCotizacion: true,
      total: true,
      orden: {
        select: {
          codigoOrden: true,
          vehiculo: {
            select: {
              marca: true,
              modelo: true,
              placa: true,
              cliente: { select: { nombre: true, primerApellido: true, segundoApellido: true } },
            },
          },
        },
      },
      pagos: { select: { montoRecibido: true } },
    },
  });

  const cuentas = cotizaciones.map((cotizacion) => {
    const pagado = cotizacion.pagos.reduce(
      (suma, pago) => suma.plus(pago.montoRecibido),
      new Prisma.Decimal(0),
    );
    const pendiente = Prisma.Decimal.max(new Prisma.Decimal(0), cotizacion.total.minus(pagado));
    const { cliente, marca, modelo, placa } = cotizacion.orden.vehiculo;
    return {
      id: cotizacion.idCotizacion,
      orden: cotizacion.orden.codigoOrden,
      cliente: [cliente.nombre, cliente.primerApellido, cliente.segundoApellido].filter(Boolean).join(" "),
      vehiculo: `${marca} ${modelo}`,
      placa,
      total: cotizacion.total.toFixed(2),
      pagado: pagado.toFixed(2),
      pendiente: pendiente.toFixed(2),
      estado: pendiente.isZero() ? "Pagado" : "Pendiente",
    };
  });

  return (
    <WorkshopShell role="recepcionista" active="pagos">
      <PagosCliente cuentas={cuentas} />
    </WorkshopShell>
  );
}
