import type { ReactNode } from "react";
import Link from "next/link";
import { signOut } from "@/auth";

type Role = "mecanico" | "recepcionista";
type Section = "diagnostico" | "cotizacion" | "pagos" | "catalogo";

export default function WorkshopShell({ role, active, children }: { role: Role; active: Section; children: ReactNode }) {
  return (
    <div className="contenedor">
      <header className="encabezado">
        <div className="logo">
          <svg viewBox="0 0 80 65" className="auto" aria-hidden="true">
            <path d="M15 42 L20 17 Q22 7 32 7 H49 Q59 7 62 17 L67 42 V55 H56 V48 H24 V55 H13 V42 Z" />
            <path d="M21 26 H61 M14 37 H24 M57 37 H67 M27 15 H54" />
          </svg>
          <span>TALLER AUTOMOTRIZ</span>
        </div>
        <div className="usuario">
          <span className="user-role">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
              <circle cx="12" cy="8" r="4" /><path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
            </svg>
            {role === "mecanico" ? "MECÁNICO" : "RECEPCIONISTA"}
          </span>
          <form action={async () => { "use server"; await signOut({ redirectTo: "/login" }); }}>
            <button type="submit" style={{ border: 0, background: "transparent", color: "white", cursor: "pointer", font: "inherit" }}>CERRAR SESIÓN</button>
          </form>
        </div>
      </header>
      <div className="estructura">
        <aside className="menu">
          <Link href="/inicio" className="opcion">
            <span className="icono"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="m3 10 9-7 9 7" /><path d="M5 9v11h14V9" /><path d="M9 20v-7h6v7" /></svg></span>
            INICIO
          </Link>
          <Link href="/catalogo" className={`opcion${active === "catalogo" ? " seleccionado" : ""}`}>
            <span className="icono"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /><path d="M2 4h20v16H2z" /></svg></span>
            <span>CATÁLOGO</span>
          </Link>
          {role === "mecanico" ? (
            <Link href="/diagnostico" className={`opcion${active === "diagnostico" ? " seleccionado" : ""}`}>
              <span className="icono"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M14.7 6.3a5 5 0 0 0-6.4 6.4L3 18l3 3 5.3-5.3a5 5 0 0 0 6.4-6.4l-3.1 3.1-3-3z" /></svg></span>
              <span>DIAGNÓSTICO Y<br /> REPARACIÓN</span>
            </Link>
          ) : (
            <>
              <Link href="/cotizacion" className={`opcion${active === "cotizacion" ? " seleccionado" : ""}`}>
                <span className="icono"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M6 3h8l4 4v14H6z" /><path d="M14 3v5h5M9 13h6M9 17h6" /><path d="m9 9 1.5 1.5L13 8" /></svg></span>
                COTIZACIÓN
              </Link>
              <Link href="/pagos" className={`opcion${active === "pagos" ? " seleccionado" : ""}`}>
                <span className="icono"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><rect x="2.5" y="5" width="19" height="14" rx="2" /><path d="M3 10h18M7 15h4" /></svg></span>
                PAGOS
              </Link>
            </>
          )}
        </aside>
        <main className={`contenido${active === "cotizacion" ? " contenido-cotizacion" : ""}`}>{children}</main>
      </div>
    </div>
  );
}
