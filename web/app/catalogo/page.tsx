import { redirect } from "next/navigation";
import { auth } from "@/auth";
import WorkshopShell from "@/components/WorkshopShell";
import { prisma } from "@/lib/prisma";
import styles from "./catalogo.module.css";

export const dynamic = "force-dynamic";

function dinero(valor: { toFixed: (decimales: number) => string }) {
  return `B/. ${valor.toFixed(2)}`;
}

export default async function Catalogo() {
  const session = await auth();
  if (!session?.user) redirect("/login");

  const [repuestos, servicios] = await Promise.all([
    prisma.inventarioRepuesto.findMany({ orderBy: { nombre: "asc" } }),
    prisma.servicio.findMany({ orderBy: { nombre: "asc" } }),
  ]);

  return (
    <WorkshopShell role={session.user.rol} active="catalogo">
      <div className={styles.catalogo}>
        <div className={styles.encabezado}>
          <div>
            <h1>CATÁLOGO</h1>
            <p>Repuestos y servicios disponibles en el taller.</p>
          </div>
          <div className={styles.resumen}>
            <span>{repuestos.length} repuestos</span>
            <span>{servicios.length} servicios</span>
          </div>
        </div>

        <section className={styles.seccion}>
          <div className={styles.tituloSeccion}>
            <h3>REPUESTOS Y PRODUCTOS</h3>
            <span className={styles.conteo}>{repuestos.length} registros</span>
          </div>
          <div className={styles.tablaWrap}>
            <table className={styles.tabla}>
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Descripción</th>
                  <th>Stock</th>
                  <th>Stock mínimo</th>
                  <th>Precio</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                {repuestos.map((repuesto) => {
                  const bajo = repuesto.stockActual <= repuesto.stockMinimo;
                  return (
                    <tr key={repuesto.idRepuesto}>
                      <td>{repuesto.nombre}</td>
                      <td className={styles.descripcion}>{repuesto.descripcion ?? "—"}</td>
                      <td>{repuesto.stockActual}</td>
                      <td>{repuesto.stockMinimo}</td>
                      <td className={styles.precio}>{dinero(repuesto.precioBase)}</td>
                      <td>
                        <span className={bajo ? styles.bajo : styles.disponible}>
                          {bajo ? "Stock bajo" : "Disponible"}
                        </span>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </section>

        <section className={styles.seccion}>
          <div className={styles.tituloSeccion}>
            <h3>SERVICIOS DE TALLER</h3>
            <span className={styles.conteo}>{servicios.length} registros</span>
          </div>
          <div className={styles.tablaWrap}>
            <table className={styles.tabla}>
              <thead>
                <tr>
                  <th>Servicio</th>
                  <th>Descripción</th>
                  <th>Precio</th>
                </tr>
              </thead>
              <tbody>
                {servicios.map((servicio) => (
                  <tr key={servicio.idServicio}>
                    <td>{servicio.nombre}</td>
                    <td className={styles.descripcion}>{servicio.descripcion ?? "—"}</td>
                    <td className={styles.precio}>{dinero(servicio.precio)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </WorkshopShell>
  );
}
