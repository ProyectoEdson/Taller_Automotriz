import { auth } from "@/auth";
import WorkshopShell from "@/components/WorkshopShell";
import { prisma } from "@/lib/prisma";
import { redirect } from "next/navigation";
import DiagnosticoPanel from "./DiagnosticoPanel";

export const dynamic = "force-dynamic";

export default async function DiagnosticoPage() {
  const session = await auth();
  if (!session?.user) redirect("/login");
  if (session.user.rol !== "mecanico") redirect("/inicio");

  const ordenes = await prisma.ordenServicio.findMany({
    orderBy: { idOrden: "desc" },
    select: {
      idOrden: true,
      codigoOrden: true,
      vehiculo: {
        select: {
          placa: true,
          marca: true,
          modelo: true,
          cliente: { select: { nombre: true, primerApellido: true, segundoApellido: true } },
        },
      },
      diagnostico: {
        select: {
          descripcionFall: true,
          tareaRealiz: true,
          repuestos: {
            orderBy: { idRepuestoUtil: "asc" },
            select: {
              idRepuestoUtil: true,
              descripcion: true,
              cantidad: true,
              precioUnitario: true,
              totalItem: true,
              inventario: { select: { nombre: true } },
            },
          },
        },
      },
    },
  });

  const datos = ordenes.map((orden) => ({
    id: orden.idOrden,
    codigo: orden.codigoOrden,
    placa: orden.vehiculo.placa,
    vehiculo: `${orden.vehiculo.marca} ${orden.vehiculo.modelo}`,
    cliente: [orden.vehiculo.cliente.nombre, orden.vehiculo.cliente.primerApellido, orden.vehiculo.cliente.segundoApellido].filter(Boolean).join(" "),
    diagnostico: orden.diagnostico?.descripcionFall ?? "",
    reparaciones: orden.diagnostico?.tareaRealiz ?? "",
    repuestos: orden.diagnostico?.repuestos.map((repuesto) => ({
      id: repuesto.idRepuestoUtil,
      nombre: repuesto.descripcion || repuesto.inventario?.nombre || "",
      cantidad: repuesto.cantidad,
      precio: repuesto.precioUnitario.toFixed(2),
      total: repuesto.totalItem.toFixed(2),
    })) ?? [],
  }));

  return (
    <WorkshopShell role="mecanico" active="diagnostico">
      <DiagnosticoPanel ordenes={datos} />
    </WorkshopShell>
  );
}
