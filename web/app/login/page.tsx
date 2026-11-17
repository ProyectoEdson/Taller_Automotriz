"use client";

import { useState, type FormEvent } from "react";
import { useRouter } from "next/navigation";

export default function Login() {
  const router = useRouter();
  const [rol, setRol] = useState<"mecanico" | "recepcionista">("mecanico");
  const [mensaje, setMensaje] = useState("");
  const [enviando, setEnviando] = useState(false);

  async function enviar(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setMensaje("");
    setEnviando(true);
    const data = new FormData(event.currentTarget);
    try {
      const response = await fetch("/api/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ usuario: data.get("usuario"), password: data.get("password"), rol }),
      });
      const result = await response.json();
      if (!response.ok) {
        setMensaje(result.mensaje);
        return;
      }
      router.push(result.destino);
      router.refresh();
    } catch {
      setMensaje("No se pudo conectar con el servidor.");
    } finally {
      setEnviando(false);
    }
  }

  return (
    <main className="login-frame">
        <header className="login-header">
          <svg viewBox="0 0 80 65" className="auto" aria-hidden="true">
            <path d="M15 42 L20 17 Q22 7 32 7 H49 Q59 7 62 17 L67 42 V55 H56 V48 H24 V55 H13 V42 Z" />
            <path d="M21 26 H61 M14 37 H24 M57 37 H67 M27 15 H54" />
          </svg>
          <h2>TALLER AUTOMOTRIZ</h2>
          <p>Acceso al Sistema de Gestión</p>
        </header>
        <section className="login-content">
          <form onSubmit={enviar}>
            <p className="label-title"><strong>Seleccione su Perfil de Agente</strong></p>
            <div className="role-selector">
              <label className="role-card">
                <input type="radio" name="rol" value="mecanico" checked={rol === "mecanico"} onChange={() => setRol("mecanico")} />
                <div className="role-box">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="3" />
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06-.06a2 2 0 1 1-2.83-2.83l-.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0-1.82-.33H9a1.65 1.65 0 0 0-1 1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06-.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
                  </svg>
                  <span>MECÁNICO</span>
                </div>
              </label>
              <label className="role-card">
                <input type="radio" name="rol" value="recepcionista" checked={rol === "recepcionista"} onChange={() => setRol("recepcionista")} />
                <div className="role-box">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
                  </svg>
                  <span>RECEPCIONISTA</span>
                </div>
              </label>
            </div>
            <div className="role-notice">
              <strong>{rol === "mecanico" ? "Acceso Compartido de Taller:" : "Acceso de Recepción:"}</strong>{" "}
              {rol === "mecanico"
                ? "Cualquier mecánico autenticado puede consultar y dar seguimiento a las órdenes de trabajo abiertas por otros compañeros."
                : "El personal de recepción puede trabajar con las cotizaciones y registrar los pagos de las órdenes de servicio."}
            </div>
            <article>
              <label htmlFor="usuario" className="label-title">Usuario o Código de Agente</label>
              <input id="usuario" name="usuario" type="text" className="login-input" placeholder="Ej. mec.carlos o rec.ana" required autoComplete="username" />
            </article>
            <article>
              <label htmlFor="password" className="label-title">Contraseña</label>
              <input id="password" name="password" type="password" className="login-input" placeholder="••••••••" required autoComplete="current-password" />
            </article>
            <button type="submit" className="btn-login" disabled={enviando}>{enviando ? "VERIFICANDO..." : "INICIAR SESIÓN"}</button>
            <p role="alert">{mensaje}</p>
          </form>
        </section>
    </main>
  );
}
