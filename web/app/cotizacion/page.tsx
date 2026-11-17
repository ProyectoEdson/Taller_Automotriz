import { redirect } from "next/navigation";
import { auth } from "@/auth";
import WorkshopShell from "@/components/WorkshopShell";
import { prisma } from "@/lib/prisma";
import OrderInput from "./OrderInput";
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
        <form id="formCotizacion">
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

          <section className="detalle-cotizacion">
            <h3>SERVICIOS Y REPUESTOS</h3>
            <div className="tabla-contenedor">
              <table className="tabla-cotizacion">
                <thead>
                  <tr>
                    <th>Descripción</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Precio unitario</th>
                    <th>Total</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody id="detalleCotizacion">
                  {filas.map((fila) => (
                    <tr key={fila.id}>
                      <td><input type="text" className="descripcion" defaultValue={fila.descripcion} placeholder="Descripción" maxLength={150} readOnly={Boolean(cotizacion)} /></td>
                      <td>
                        <select className="tipo" defaultValue={fila.tipo} disabled={Boolean(cotizacion)}>
                          <option value="Servicio">Servicio</option>
                          <option value="Repuesto">Repuesto</option>
                        </select>
                      </td>
                      <td><input type="number" className="cantidad" min="1" step="1" defaultValue={fila.cantidad} readOnly={Boolean(cotizacion)} /></td>
                      <td><input type="number" className="precio" min="0" step="0.01" defaultValue={fila.precio} placeholder="Sin precio" readOnly={Boolean(cotizacion)} /></td>
                      <td><input type="number" className="total-item" value={fila.total} placeholder="—" readOnly /></td>
                      <td><button type="button" className="btn-eliminar-detalle" disabled title="Disponible en fase 4">Eliminar</button></td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            <div className="acciones-cotizacion">
              <button type="button" id="agregarDetalle" disabled title="Disponible en fase 4">+ Agregar detalle</button>
            </div>
            <div className="resumen-cotizacion">
              <div className="fila-resumen"><span>Subtotal:</span><strong id="subtotalTexto">{cotizacion ? `$${cotizacion.subtotal.toFixed(2)}` : "—"}</strong></div>
              <div className="fila-resumen"><span>Impuesto (7 %):</span><strong id="impuestoTexto">{cotizacion ? `$${cotizacion.impuesto.toFixed(2)}` : "—"}</strong></div>
              <div className="fila-resumen total-final"><span>Total:</span><strong id="totalTexto">{cotizacion ? `$${cotizacion.total.toFixed(2)}` : "—"}</strong></div>
            </div>
          </section>
          <div className="acciones-cotizacion">
            <button type="submit" id="btnGenerar" disabled title="Disponible en fase 4">GENERAR COTIZACIÓN</button>
          </div>
        </form>
        <p id="mensaje">
          {cotizacion
            ? `Cotización N.º ${cotizacion.idCotizacion} registrada. Los importes mostrados son los guardados; los cambios en las líneas no se guardan en esta fase.`
            : orden
              ? "Orden encontrada. Los detalles del diagnóstico son una vista previa; la cotización aún no está registrada y sus totales no están disponibles. Guardar, agregar y eliminar estarán disponibles en fase 4."
              : codigo
                ? "Orden no encontrada. Verifica el código e inténtalo de nuevo."
                : "Escribe el código de la orden de trabajo. Guardar, agregar y eliminar estarán disponibles en fase 4."}
        </p>
      </div>
    </WorkshopShell>
  );
}
