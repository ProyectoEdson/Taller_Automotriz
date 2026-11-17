"use client";

import { useState } from "react";

type Cuenta = {
  id: number;
  orden: string;
  cliente: string;
  vehiculo: string;
  placa: string;
  total: string;
  pagado: string;
  pendiente: string;
  estado: string;
};

const dinero = (valor: string) => `$${valor}`;

export default function PagosCliente({ cuentas: iniciales }: { cuentas: Cuenta[] }) {
  const [cuentas, setCuentas] = useState(iniciales);
  const [seleccion, setSeleccion] = useState<number | null>(
    cuentas.find((cuenta) => cuenta.estado !== "Pagado")?.id ?? cuentas[0]?.id ?? null,
  );
  const [monto, setMonto] = useState(cuentas.find((item) => item.id === seleccion)?.pendiente ?? "");
  const [metodo, setMetodo] = useState("Efectivo");
  const [mensaje, setMensaje] = useState("");
  const [enviando, setEnviando] = useState(false);
  const cuenta = cuentas.find((item) => item.id === seleccion);
  const pagada = cuenta?.estado === "Pagado";

  async function registrar() {
    if (!cuenta) return;
    setEnviando(true);
    try {
      const response = await fetch("/api/pagos", {
        method: "POST", headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ idCotizacion: cuenta.id, monto, metodo }),
      });
      const result = await response.json();
      setMensaje(response.ok ? `Pago N.º ${result.idPago} registrado. Saldo: $${result.saldo} (${result.estado}).` : result.mensaje);
      if (response.ok) {
        setCuentas(cuentas.map((item) => item.id === cuenta.id ? {
          ...item, pagado: (Number(item.total) - Number(result.saldo)).toFixed(2),
          pendiente: result.saldo, estado: result.estado,
        } : item));
        setMonto(result.saldo);
      }
    } catch {
      setMensaje("No se pudo registrar el pago.");
    } finally {
      setEnviando(false);
    }
  }

  return (
    <>
      <h1>REGISTRAR PAGO DE CUENTA</h1>
      <section className="informacion">
        <h3>SELECCIONAR ORDEN DE TRABAJO</h3>
        <select id="seleccionarOrden" value={seleccion ?? ""} onChange={(evento) => { const id = Number(evento.target.value); setSeleccion(id || null); setMonto(cuentas.find((item) => item.id === id)?.pendiente ?? ""); setMensaje(""); }}>
          <option value="">Seleccione una orden</option>
          {cuentas.map((item) => (
            <option key={item.id} value={item.id}>
              {item.orden} - {item.cliente}{item.estado === "Pagado" ? " [Pagada]" : ""}
            </option>
          ))}
        </select>
      </section>
      <section className="informacion orden-pago">
        <h3>INFORMACIÓN DE LA ORDEN</h3>
        <div className="datos-pago">
          <span>ORDEN DE TRABAJO:</span><strong id="ordenTrabajo">{cuenta?.orden ?? "-"}</strong>
          <span>CLIENTE:</span><strong id="cliente">{cuenta?.cliente ?? "-"}</strong>
          <span>VEHÍCULO:</span><strong id="vehiculo">{cuenta?.vehiculo ?? "-"}</strong>
          <span>PLACA:</span><strong id="placa">{cuenta?.placa ?? "-"}</strong>
        </div>
      </section>
      <div className="zona-pago">
        <div className="columna-izquierda">
          <section className="tarjeta-total">
            <h3>Total a Pagar:</h3>
            <strong id="totalPagar">{dinero(cuenta?.pendiente ?? "0.00")}</strong>
          </section>
          <section className="estado-cuenta">
            <h3>Estado De Cuenta</h3>
            <div className={`estado ${pagada ? "registrado" : "pendiente"}`} id="estadoPendiente">{pagada ? "Registrado" : "Pendiente"}</div>
            <div className="flecha" id="flechaEstado">↓</div>
            <div className={`estado ${pagada ? "pagado" : "pendiente"}`} id="estadoPagado">Pagado</div>
            <p className="detalle-estado" id="detalleEstado">{cuenta ? `Pagado: ${dinero(cuenta.pagado)} de ${dinero(cuenta.total)}` : ""}</p>
          </section>
        </div>
        <section className="formulario-pago" key={seleccion ?? "sin-orden"}>
          <h3>Método De Pago</h3>
          <div className="metodos">
            <label><input type="radio" name="metodo" value="Efectivo" checked={metodo === "Efectivo"} onChange={() => setMetodo("Efectivo")} disabled={!cuenta || pagada} />Efectivo</label>
            <label><input type="radio" name="metodo" value="Tarjeta" checked={metodo === "Tarjeta"} onChange={() => setMetodo("Tarjeta")} disabled={!cuenta || pagada} />Tarjeta</label>
            <label><input type="radio" name="metodo" value="Transferencia" checked={metodo === "Transferencia"} onChange={() => setMetodo("Transferencia")} disabled={!cuenta || pagada} />Yappy/Transferencia</label>
          </div>
          <label htmlFor="monto">Monto Recibido</label>
          <input type="number" id="monto" min="0.01" step="0.01" max={cuenta?.pendiente} value={monto} onChange={(evento) => setMonto(evento.target.value)} disabled={!cuenta || pagada} />
          <label htmlFor="fecha">Fecha De Pago</label>
          <input type="date" id="fecha" defaultValue={new Date().toLocaleDateString("en-CA")} disabled title="Fecha asignada por el servidor" />
        </section>
      </div>
      <div className="acciones"><button type="button" id="btnRegistrarPago" disabled={!cuenta || pagada || enviando || !monto} onClick={registrar}>Registrar Pago</button></div>
      <p id="mensajePago" role="status">{mensaje || (cuentas.length === 0 ? "No hay cotizaciones registradas." : pagada ? "Esta cotización ya está pagada." : "")}</p>
    </>
  );
}
