"use client";

import { useRouter } from "next/navigation";
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
  const router = useRouter();
  const [seleccion, setSeleccion] = useState(ordenes[0]?.id.toString() ?? "");
  const [repuestos, setRepuestos] = useState(ordenes[0]?.repuestos.map(({ nombre, cantidad, precio }) => ({ nombre, cantidad, precio })) ?? []);
  const [nuevo, setNuevo] = useState(false);
  const [nombreRepuesto, setNombreRepuesto] = useState("");
  const [cantidad, setCantidad] = useState("1");
  const [precio, setPrecio] = useState("0");
  const [mensaje, setMensaje] = useState("");
  const [guardando, setGuardando] = useState(false);
  const orden = ordenes.find((item) => item.id.toString() === seleccion);

  async function guardar() {
    const campo = (id: string) => (document.getElementById(id) as HTMLInputElement | HTMLTextAreaElement).value.trim();
    setGuardando(true);
    try {
      const body = {
        ...(orden ? { idOrden: orden.id } : {
          nombre: campo("nombre"), primerApellido: campo("primerApellido"), segundoApellido: campo("segundoApellido"),
          telefono: campo("telefono"), email: campo("email"), marca: campo("marca"), modelo: campo("modelo"),
          placa: campo("placa"), anio: campo("anio") ? Number(campo("anio")) : null,
          kilometraje: campo("kilometraje") ? Number(campo("kilometraje")) : null,
        }),
        diagnostico: campo("diagnostico"), reparaciones: campo("reparaciones"), repuestos,
      };
      const response = await fetch("/api/ordenes", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(body) });
      const result = await response.json();
      setMensaje(response.ok ? `Diagnóstico guardado: ${result.codigoOrden}` : result.mensaje);
      if (response.ok) {
        setSeleccion(String(result.idOrden));
        router.refresh();
      }
    } catch {
      setMensaje("No se pudo guardar el diagnóstico.");
    } finally {
      setGuardando(false);
    }
  }

  return (
    <>
      <h1>REGISTRAR DIAGNÓSTICO Y REPARACIÓN</h1>
      <label htmlFor="ordenTrabajo">ORDEN DE TRABAJO</label>
      <select id="ordenTrabajo" value={seleccion} onChange={(event) => {
        const id = event.target.value;
        setSeleccion(id);
        setRepuestos(ordenes.find((item) => item.id.toString() === id)?.repuestos.map(({ nombre, cantidad, precio }) => ({ nombre, cantidad, precio })) ?? []);
        setMensaje("");
      }}>
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
      <textarea key={`diagnostico-${seleccion}`} id="diagnostico" maxLength={5000} placeholder="Describe el problema encontrado..." defaultValue={orden?.diagnostico ?? ""} />
      <label htmlFor="reparaciones">REPARACIONES REALIZADAS</label>
      <textarea key={`reparaciones-${seleccion}`} id="reparaciones" maxLength={5000} placeholder="Describe las reparaciones realizadas..." defaultValue={orden?.reparaciones ?? ""} />

      <section className="repuestos">
        <label>REPUESTO UTILIZADO</label>
        <table>
          <thead><tr><th>Repuesto</th><th>Cantidad</th><th>Precio unitario</th><th>Total</th></tr></thead>
          <tbody id="tablaRepuestos">
            {repuestos.map((repuesto, index) => (
              <tr key={index}>
                <td>{repuesto.nombre} <button type="button" className="btn-eliminar-repuesto" title="Quitar repuesto" onClick={() => setRepuestos(repuestos.filter((_, position) => position !== index))}>×</button></td>
                <td>{repuesto.cantidad}</td><td>$ {repuesto.precio}</td>
                <td>$ {(Number(repuesto.precio) * repuesto.cantidad).toFixed(2)}</td>
              </tr>
            ))}
          </tbody>
        </table>
        <div className="nuevo-repuesto" id="nuevoRepuestoForm" hidden={!nuevo}>
          <input type="text" id="nuevoRepuestoNombre" maxLength={150} placeholder="Nombre del repuesto" aria-label="Nombre del repuesto" value={nombreRepuesto} onChange={(event) => setNombreRepuesto(event.target.value)} />
          <input type="number" id="nuevoRepuestoCantidad" value={cantidad} onChange={(event) => setCantidad(event.target.value)} min="1" step="1" placeholder="Cantidad" aria-label="Cantidad" />
          <input type="number" id="nuevoRepuestoPrecio" value={precio} onChange={(event) => setPrecio(event.target.value)} min="0" step="0.01" placeholder="Precio" aria-label="Precio" />
          <button type="button" id="btnConfirmarRepuesto" onClick={() => {
            if (!nombreRepuesto.trim() || !Number.isInteger(Number(cantidad)) || Number(cantidad) <= 0 || !/^\d{1,8}(\.\d{1,2})?$/.test(precio)) { setMensaje("Repuesto inválido."); return; }
            setRepuestos([...repuestos, { nombre: nombreRepuesto.trim(), cantidad: Number(cantidad), precio }]);
            setNombreRepuesto(""); setCantidad("1"); setPrecio("0"); setNuevo(false);
          }}>Añadir</button>
        </div>
        <div className="acciones"><button type="button" id="btnAgregarRepuesto" onClick={() => setNuevo(!nuevo)}>Agregar Repuesto</button></div>
      </section>
      <div className="acciones"><button type="button" id="btnGuardarDiagnostico" disabled={guardando} onClick={guardar}>Guardar Diagnóstico</button></div>
      {mensaje && <p className="aviso-ordenes" role="status">{mensaje}</p>}
    </>
  );
}
