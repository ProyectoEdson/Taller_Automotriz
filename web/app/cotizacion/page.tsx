import { redirect } from "next/navigation";
import { auth } from "@/auth";
import WorkshopShell from "@/components/WorkshopShell";
import { prisma } from "@/lib/prisma";
import OrderInput from "./OrderInput";
import CotizacionEditor from "./CotizacionEditor";
import styles from "./cotizacion.module.css";

export const dynamic = "force-dynamic";

export default async function Cotizacion({
  searchParams,
}: {
  searchParams: Promise<{ orden?: string | string[] }>;
}) {
  const session = await auth();
  if (!session?.user) redirect("/login");
  if (session.user.rol !== "recepcionista") redirect("/inicio");

  const requested = (await searchParams).orden;
  const codigo = typeof requested === "string" ? requested.trim() : "";
  const orden = codigo
    ? await prisma.ordenServicio.findUnique({
        where: { codigoOrden: codigo },
        select: {
          codigoOrden: true,
          vehiculo: {
            select: {
              marca: true,
              modelo: true,
              placa: true,
              cliente: {
                select: { nombre: true, primerApellido: true, segundoApellido: true },
              },
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
          cotizaciones: {
            take: 1,
            select: {
              idCotizacion: true,
              subtotal: true,
              impuesto: true,
              total: true,
              detalles: {
                orderBy: { idDetalle: "asc" },
                select: {
                  idDetalle: true,
                  descripcion: true,
                  tipo: true,
                  cantidad: true,
                  precioUnitario: true,
                  totalItem: true,
                },
              },
            },
          },
        },
      })
    : null;
  const cotizacion = orden?.cotizaciones[0];
  const diagnostico = orden?.diagnostico;
  const filas = cotizacion
    ? cotizacion.detalles.map((detalle) => ({
        id: `cotizacion-${detalle.idDetalle}`,
        descripcion: detalle.descripcion,
        tipo: detalle.tipo,
        cantidad: detalle.cantidad,
        precio: detalle.precioUnitario.toFixed(2),
        total: detalle.totalItem.toFixed(2),
      }))
    : [
        ...(diagnostico?.descripcionFall?.trim()
          ? [{
              id: "diagnostico",
              descripcion: `Diagnóstico: ${diagnostico.descripcionFall.trim()}`.slice(0, 150),
              tipo: "Servicio",
              cantidad: 1,
              precio: "",
              total: "",
            }]
          : []),
        ...(diagnostico?.tareaRealiz?.trim()
          ? [{
              id: "reparacion",
              descripcion: `Reparación: ${diagnostico.tareaRealiz.trim()}`.slice(0, 150),
              tipo: "Servicio",
              cantidad: 1,
              precio: "",
              total: "",
            }]
          : []),
        ...(diagnostico?.repuestos.map((repuesto) => ({
          id: `repuesto-${repuesto.idRepuestoUtil}`,
          descripcion: repuesto.descripcion?.trim() || repuesto.inventario?.nombre || "Repuesto",
          tipo: "Repuesto",
          cantidad: repuesto.cantidad,
          precio: repuesto.precioUnitario.toFixed(2),
          total: repuesto.totalItem.toFixed(2),
        })) ?? []),
      ];

  return (
    <WorkshopShell role="recepcionista" active="cotizacion">
      <div className={styles.cotizacion}>
        <h1>GENERAR COTIZACIÓN</h1>
        <div id="formCotizacionDatos">
          <section className="info-orden-cotizacion">
            <h3>INFORMACIÓN DE LA ORDEN</h3>
            <div className="datos-orden-cotizacion">
              <label htmlFor="codigoOrden">ORDEN DE TRABAJO:</label>
              <OrderInput codigo={codigo} />
              <label htmlFor="cliente">CLIENTE:</label>
              <input
                type="text"
                id="cliente"
                value={orden ? [orden.vehiculo.cliente.nombre, orden.vehiculo.cliente.primerApellido, orden.vehiculo.cliente.segundoApellido].filter(Boolean).join(" ") : ""}
                placeholder="Se completará automáticamente"
                readOnly
              />
              <label htmlFor="vehiculo">VEHÍCULO:</label>
              <input
                type="text"
                id="vehiculo"
                value={orden ? `${orden.vehiculo.marca} ${orden.vehiculo.modelo}`.trim() : ""}
                placeholder="Se completará automáticamente"
                readOnly
              />
              <label htmlFor="placa">PLACA:</label>
              <input
                type="text"
                id="placa"
                value={orden?.vehiculo.placa ?? ""}
                placeholder="Se completará automáticamente"
                readOnly
              />
            </div>
          </section>

        </div>
        <CotizacionEditor
          key={`${codigo}-${cotizacion?.idCotizacion ?? "nueva"}`}
          codigo={orden?.codigoOrden ?? ""}
          iniciales={filas}
          guardada={Boolean(cotizacion)}
          totales={cotizacion ? {
            subtotal: cotizacion.subtotal.toFixed(2),
            impuesto: cotizacion.impuesto.toFixed(2),
            total: cotizacion.total.toFixed(2),
          } : undefined}
        />
      </div>
    </WorkshopShell>
  );
}
