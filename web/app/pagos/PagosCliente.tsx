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

export default function PagosCliente({ cuentas }: { cuentas: Cuenta[] }) {
  const [seleccion, setSeleccion] = useState<number | null>(
    cuentas.find((cuenta) => cuenta.estado !== "Pagado")?.id ?? cuentas[0]?.id ?? null,
  );
  const cuenta = cuentas.find((item) => item.id === seleccion);
  const pagada = cuenta?.estado === "Pagado";

  return (
    <>
      <h1>REGISTRAR PAGO DE CUENTA</h1>
      <section className="informacion">
        <h3>SELECCIONAR ORDEN DE TRABAJO</h3>
        <select id="seleccionarOrden" value={seleccion ?? ""} onChange={(evento) => setSeleccion(evento.target.value ? Number(evento.target.value) : null)}>
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
            <label><input type="radio" name="metodo" value="Efectivo" defaultChecked disabled={!cuenta || pagada} />Efectivo</label>
            <label><input type="radio" name="metodo" value="Tarjeta" disabled={!cuenta || pagada} />Tarjeta</label>
            <label><input type="radio" name="metodo" value="Transferencia" disabled={!cuenta || pagada} />Yappy/Transferencia</label>
          </div>
          <label htmlFor="monto">Monto Recibido</label>
          <input type="number" id="monto" min="0" step="0.01" max={cuenta?.pendiente} defaultValue={cuenta?.pendiente ?? ""} disabled={!cuenta || pagada} />
          <label htmlFor="fecha">Fecha De Pago</label>
          <input type="date" id="fecha" defaultValue={new Date().toLocaleDateString("en-CA")} disabled={!cuenta || pagada} />
        </section>
      </div>
      <div className="acciones"><button type="button" id="btnRegistrarPago" disabled>Registrar Pago</button></div>
      <p id="mensajePago">{cuentas.length === 0 ? "No hay cotizaciones registradas." : "Registro de pagos disponible en fase 4."}</p>
    </>
  );
}
