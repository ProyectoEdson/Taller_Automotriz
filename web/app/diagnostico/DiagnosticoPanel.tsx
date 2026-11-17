"use client";

import { useState } from "react";

type Orden = {
  id: number;
  codigo: string;
  placa: string;
  vehiculo: string;
  cliente: string;
  diagnostico: string;
  reparaciones: string;
  repuestos: { id: number; nombre: string; cantidad: number; precio: string; total: string }[];
};

function Campo({ id, etiqueta, placeholder, type = "text", maxLength, min, max, step }: {
  id: string;
  etiqueta: string;
  placeholder: string;
  type?: string;
  maxLength?: number;
  min?: string;
  max?: string;
  step?: string;
}) {
  return <><span>{etiqueta}</span><input id={id} type={type} maxLength={maxLength} min={min} max={max} step={step} placeholder={placeholder} /></>;
}

export default function DiagnosticoPanel({ ordenes }: { ordenes: Orden[] }) {
  const [seleccion, setSeleccion] = useState(ordenes[0]?.id.toString() ?? "");
  const orden = ordenes.find((item) => item.id.toString() === seleccion);

  return (
    <>
      <h1>REGISTRAR DIAGNÓSTICO Y REPARACIÓN</h1>
      <label htmlFor="ordenTrabajo">ORDEN DE TRABAJO</label>
      <select id="ordenTrabajo" value={seleccion} onChange={(event) => setSeleccion(event.target.value)}>
        <option value="">Nueva orden (código automático)</option>
        {ordenes.map((item) => <option key={item.id} value={item.id}>{item.codigo} — {item.placa}</option>)}
      </select>
      {ordenes.length === 0 && <p id="avisoOrdenes" className="aviso-ordenes">No hay órdenes registradas.</p>}

      {orden ? (
        <section className="informacion gris" id="panelOrdenExistente">
          <h3>INFORMACIÓN DEL VEHÍCULO</h3>
          <div className="datos-vehiculo">
            <span>CLIENTE:</span><strong id="lecturaCliente">{orden.cliente}</strong>
            <span>VEHÍCULO:</span><strong id="lecturaVehiculo">{orden.vehiculo}</strong>
            <span>PLACA:</span><strong id="lecturaPlaca">{orden.placa}</strong>
          </div>
        </section>
      ) : (
        <div id="panelNuevaOrden">
          <section className="informacion gris">
            <h3>INFORMACIÓN DEL CLIENTE</h3>
            <div className="datos-vehiculo">
              <Campo id="nombre" etiqueta="NOMBRE:" placeholder="Nombre del cliente" maxLength={100} />
              <Campo id="primerApellido" etiqueta="PRIMER APELLIDO:" placeholder="Primer apellido" maxLength={100} />
              <Campo id="segundoApellido" etiqueta="SEGUNDO APELLIDO:" placeholder="Segundo apellido" maxLength={100} />
              <Campo id="telefono" etiqueta="TELÉFONO:" placeholder="Teléfono del cliente" type="tel" maxLength={20} />
              <Campo id="email" etiqueta="EMAIL:" placeholder="Correo electrónico" type="email" maxLength={100} />
            </div>
          </section>
          <section className="informacion gris">
            <h3>INFORMACIÓN DEL VEHÍCULO</h3>
            <div className="datos-vehiculo">
              <Campo id="marca" etiqueta="MARCA:" placeholder="Ejemplo: TOYOTA" maxLength={50} />
              <Campo id="modelo" etiqueta="MODELO:" placeholder="Ejemplo: COROLLA" maxLength={50} />
              <Campo id="placa" etiqueta="PLACA:" placeholder="Placa del vehículo" maxLength={15} />
              <Campo id="anio" etiqueta="AÑO:" placeholder="Ejemplo: 2020" type="number" min="1900" max="2100" />
              <Campo id="kilometraje" etiqueta="KILOMETRAJE:" placeholder="Kilometraje actual" type="number" min="0" step="1" />
            </div>
          </section>
        </div>
      )}

      <label htmlFor="diagnostico">DIAGNÓSTICO</label>
      <textarea key={`diagnostico-${seleccion}`} id="diagnostico" maxLength={5000} placeholder="Describe el problema encontrado..." defaultValue={orden?.diagnostico ?? ""} readOnly={Boolean(orden)} />
      <label htmlFor="reparaciones">REPARACIONES REALIZADAS</label>
      <textarea key={`reparaciones-${seleccion}`} id="reparaciones" maxLength={5000} placeholder="Describe las reparaciones realizadas..." defaultValue={orden?.reparaciones ?? ""} readOnly={Boolean(orden)} />

      <section className="repuestos">
        <label>REPUESTO UTILIZADO</label>
        <table>
          <thead><tr><th>Repuesto</th><th>Cantidad</th><th>Precio unitario</th><th>Total</th></tr></thead>
          <tbody id="tablaRepuestos">
            {orden?.repuestos.map((repuesto) => (
              <tr key={repuesto.id}>
                <td>{repuesto.nombre}</td><td>{repuesto.cantidad}</td>
                <td>$ {repuesto.precio}</td><td>$ {repuesto.total}</td>
              </tr>
            ))}
          </tbody>
        </table>
        <div className="nuevo-repuesto" id="nuevoRepuestoForm" hidden>
          <input type="text" id="nuevoRepuestoNombre" maxLength={150} placeholder="Nombre del repuesto" aria-label="Nombre del repuesto" />
          <input type="number" id="nuevoRepuestoCantidad" defaultValue="1" min="1" step="1" placeholder="Cantidad" aria-label="Cantidad" />
          <input type="number" id="nuevoRepuestoPrecio" defaultValue="0" min="0" step="0.01" placeholder="Precio" aria-label="Precio" />
          <button type="button" id="btnConfirmarRepuesto" disabled title="Disponible en fase 4">Añadir</button>
        </div>
        <div className="acciones"><button type="button" id="btnAgregarRepuesto" disabled title="Disponible en fase 4">Agregar Repuesto</button></div>
      </section>
      <p className="aviso-ordenes">Guardar diagnósticos, crear órdenes y agregar repuestos estarán disponibles en la fase 4. Los cambios escritos aquí no se guardan.</p>
      <div className="acciones"><button type="button" id="btnGuardarDiagnostico" disabled title="Disponible en fase 4">Guardar Diagnóstico</button></div>
    </>
  );
}
