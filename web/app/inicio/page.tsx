import Link from "next/link";
import { redirect } from "next/navigation";
import { auth, signOut } from "@/auth";
import { prisma } from "@/lib/prisma";

export const dynamic = "force-dynamic";

type Resumen = {
  ordenes: number;
  cotizaciones_pendientes: number;
  reparaciones_activas: number;
  pagos_pendientes: number;
};

export default async function Inicio() {
  const session = await auth();
  if (!session?.user) redirect("/login");
  const [resumen] = await prisma.$queryRaw<Resumen[]>`
    SELECT
      (SELECT COUNT(*)::int FROM ordenes_servicio) AS ordenes,
      (SELECT COUNT(*)::int FROM ordenes_servicio o
       JOIN diagnostico_reparacion d ON d.id_orden = o.id_orden
       WHERE NOT EXISTS (SELECT 1 FROM cotizaciones c WHERE c.id_orden = o.id_orden))
        AS cotizaciones_pendientes,
      (SELECT COUNT(*)::int FROM ordenes_servicio o
       JOIN diagnostico_reparacion d ON d.id_orden = o.id_orden
       WHERE NOT EXISTS (
         SELECT 1 FROM cotizaciones c WHERE c.id_orden = o.id_orden
           AND c.total <= COALESCE((
             SELECT SUM(p.monto_recibido) FROM pago_cuenta p
             WHERE p.id_cotizacion = c.id_cotizacion
           ), 0)
       )) AS reparaciones_activas,
      (SELECT COUNT(*)::int FROM cotizaciones c
       WHERE c.total > COALESCE((
         SELECT SUM(p.monto_recibido) FROM pago_cuenta p
         WHERE p.id_cotizacion = c.id_cotizacion
       ), 0)) AS pagos_pendientes
  `;
  const mecanico = session.user.rol === "mecanico";

  return (
    <div className="app-frame">
      <header>
        <div className="brand">
          <svg viewBox="0 0 80 65" className="auto" aria-hidden="true">
            <path d="M15 42 L20 17 Q22 7 32 7 H49 Q59 7 62 17 L67 42 V55 H56 V48 H24 V55 H13 V42 Z" />
            <path d="M21 26 H61 M14 37 H24 M57 37 H67 M27 15 H54" />
          </svg>
          <h1>TALLER AUTOMOTRIZ</h1>
        </div>
        <div className="user-actions">
          <div className="user-role">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true"><circle cx="12" cy="8" r="4" /><path d="M4 20c0-4 4-6 8-6s8 2 8 6" /></svg>
            <span id="rolLabel">{mecanico ? "MECANICO" : "RECEPCIONISTA"}</span>
          </div>
          <form action={async () => { "use server"; await signOut({ redirectTo: "/login" }); }}>
            <button type="submit" className="logout" style={{ border: 0, cursor: "pointer", fontFamily: "inherit" }}>CERRAR SESIÓN</button>
          </form>
        </div>
      </header>
      <div className="layout">
        <aside className="sidebar">
          <nav>
            <Link href="/inicio" className="nav-item active">
              <span><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M3 10.5L12 3L21 10.5" /><path d="M5 9.5V21H19V9.5" /><path d="M9 21V14H15V21" /></svg></span>
              <span>INICIO</span>
            </Link>
            {mecanico ? (
              <Link href="/diagnostico" className="nav-item">
                <span><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M14.7 6.3a5 5 0 0 0-6.4 6.4L3 18l3 3 5.3-5.3a5 5 0 0 0 6.4-6.4L14 13l-3-3 3.7-3.7Z" /></svg></span>
                <span>DIAGNÓSTICO Y REPARACIÓN</span>
              </Link>
            ) : (
              <>
                <Link href="/cotizacion" className="nav-item">
                  <span><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M6 2H14L20 8V22H6Z" /><path d="M14 2V8H20" /><path d="M9 13H17M9 17H17" /></svg></span>
                  <span>COTIZACIÓN</span>
                </Link>
                <Link href="/pagos" className="nav-item">
                  <span><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2" /><path d="M2 10H22" /><path d="M6 15H10" /></svg></span>
                  <span>PAGOS</span>
                </Link>
              </>
            )}
          </nav>
        </aside>
        <main className="dashboard-main">
          <div className="dashboard-welcome">
            <div>
              <p className="dashboard-subtitle">PANEL DE CONTROL</p>
              <h2>Bienvenido al sistema</h2>
              <p>Administra las actividades y operaciones del taller desde un solo lugar.</p>
            </div>
            <div className="welcome-icon">🚘</div>
          </div>
          <section className="dashboard-summary">
            <h3>RESUMEN GENERAL</h3>
            <div className="dashboard-grid">
              <div className="dash-card"><div className="dash-icon">📋</div><div><p>Órdenes registradas</p><h3 id="totalOrdenes">{resumen.ordenes}</h3></div></div>
              <div className="dash-card"><div className="dash-icon">📄</div><div><p>Cotizaciones pendientes</p><h3 id="totalCotizaciones">{resumen.cotizaciones_pendientes}</h3></div></div>
              <div className="dash-card"><div className="dash-icon">🔧</div><div><p>Reparaciones activas</p><h3 id="totalReparaciones">{resumen.reparaciones_activas}</h3></div></div>
              <div className="dash-card"><div className="dash-icon">💳</div><div><p>Pagos pendientes</p><h3 id="totalPagos">{resumen.pagos_pendientes}</h3></div></div>
            </div>
          </section>
          <section className="dashboard-access">
            <h3>ACCESOS DEL SISTEMA</h3>
            <p className="access-description">Selecciona el módulo que deseas gestionar.</p>
            <div className="access-grid">
              {mecanico ? (
                <Link href="/diagnostico" className="access-card">
                  <div className="access-icon">⚙️</div>
                  <div><h4>Diagnóstico y reparación</h4><p>Consulta órdenes de trabajo y registra diagnósticos y reparaciones.</p></div>
                  <span className="access-arrow">→</span>
                </Link>
              ) : (
                <>
                  <Link href="/cotizacion" className="access-card">
                    <div className="access-icon">📑</div>
                    <div><h4>Cotizaciones</h4><p>Consulta cotizaciones anteriores y genera nuevos presupuestos.</p></div>
                    <span className="access-arrow">→</span>
                  </Link>
                  <Link href="/pagos" className="access-card">
                    <div className="access-icon">💳</div>
                    <div><h4>Pagos</h4><p>Revisa cuentas pendientes y registra los pagos de los clientes.</p></div>
                    <span className="access-arrow">→</span>
                  </Link>
                </>
              )}
            </div>
          </section>
        </main>
      </div>
    </div>
  );
}
