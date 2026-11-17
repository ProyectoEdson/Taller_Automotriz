"use client";

import { useRouter } from "next/navigation";
import { useState, type FormEvent } from "react";

type Fila = { id: string; descripcion: string; tipo: string; cantidad: number; precio: string; total: string };
type Totales = { subtotal: string; impuesto: string; total: string };

export default function CotizacionEditor({ codigo, iniciales, guardada, totales }: {
  codigo: string; iniciales: Fila[]; guardada: boolean; totales?: Totales;
}) {
  const router = useRouter();
  const [filas, setFilas] = useState(iniciales);
  const [mensaje, setMensaje] = useState("");
  const [enviando, setEnviando] = useState(false);
  const [registrada, setRegistrada] = useState(guardada);
  const [importes, setImportes] = useState(totales);

  function cambiar(index: number, cambio: Partial<Fila>) {
    setFilas(filas.map((fila, position) => position === index ? { ...fila, ...cambio } : fila));
  }

  async function generar(event: FormEvent) {
    event.preventDefault();
    if (!codigo || registrada) return;
    setEnviando(true);
    try {
      const response = await fetch("/api/cotizaciones", {
        method: "POST", headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ codigoOrden: codigo, detalles: filas.map(({ descripcion, tipo, cantidad, precio }) => ({ descripcion, tipo, cantidad, precio })) }),
      });
      const result = await response.json();
      setMensaje(response.ok ? `Cotización N.º ${result.idCotizacion} registrada.` : result.mensaje);
      if (response.ok) {
        setRegistrada(true);
        setImportes(result);
        router.refresh();
      }
    } catch {
      setMensaje("No se pudo registrar la cotización.");
    } finally {
      setEnviando(false);
    }
  }

  return (
    <form id="formCotizacion" onSubmit={generar}>
      <section className="detalle-cotizacion">
        <h3>SERVICIOS Y REPUESTOS</h3>
        <div className="tabla-contenedor">
          <table className="tabla-cotizacion">
            <thead><tr><th>Descripción</th><th>Tipo</th><th>Cantidad</th><th>Precio unitario</th><th>Total</th><th>Acción</th></tr></thead>
            <tbody id="detalleCotizacion">
              {filas.map((fila, index) => (
                <tr key={fila.id}>
                  <td><input type="text" className="descripcion" value={fila.descripcion} onChange={(event) => cambiar(index, { descripcion: event.target.value })} placeholder="Descripción" maxLength={150} readOnly={registrada} required /></td>
                  <td><select className="tipo" value={fila.tipo} onChange={(event) => cambiar(index, { tipo: event.target.value })} disabled={registrada}><option value="Servicio">Servicio</option><option value="Repuesto">Repuesto</option></select></td>
                  <td><input type="number" className="cantidad" min="1" step="1" value={fila.cantidad} onChange={(event) => cambiar(index, { cantidad: Number(event.target.value) })} readOnly={registrada} required /></td>
                  <td><input type="number" className="precio" min="0" step="0.01" value={fila.precio} onChange={(event) => cambiar(index, { precio: event.target.value })} placeholder="Sin precio" readOnly={registrada} required /></td>
                  <td><input type="number" className="total-item" value={registrada ? fila.total : fila.precio && fila.cantidad > 0 ? (Number(fila.precio) * fila.cantidad).toFixed(2) : ""} placeholder="—" readOnly /></td>
                  <td><button type="button" className="btn-eliminar-detalle" disabled={registrada} onClick={() => setFilas(filas.filter((_, position) => position !== index))}>Eliminar</button></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        <div className="acciones-cotizacion"><button type="button" id="agregarDetalle" disabled={registrada || !codigo} onClick={() => setFilas([...filas, { id: crypto.randomUUID(), descripcion: "", tipo: "Servicio", cantidad: 1, precio: "", total: "" }])}>+ Agregar detalle</button></div>
        <div className="resumen-cotizacion">
          <div className="fila-resumen"><span>Subtotal:</span><strong id="subtotalTexto">{importes ? `$${importes.subtotal}` : "—"}</strong></div>
          <div className="fila-resumen"><span>Impuesto (7 %):</span><strong id="impuestoTexto">{importes ? `$${importes.impuesto}` : "—"}</strong></div>
          <div className="fila-resumen total-final"><span>Total:</span><strong id="totalTexto">{importes ? `$${importes.total}` : "—"}</strong></div>
        </div>
      </section>
      <div className="acciones-cotizacion"><button type="submit" id="btnGenerar" disabled={registrada || enviando || !codigo || filas.length === 0}>GENERAR COTIZACIÓN</button></div>
      <p id="mensaje" role="status">{mensaje || (registrada ? "Cotización registrada. Los importes mostrados son los guardados." : codigo ? "Orden encontrada. Ajusta las líneas antes de generar la cotización." : "Escribe el código de la orden de trabajo.")}</p>
    </form>
  );
}
