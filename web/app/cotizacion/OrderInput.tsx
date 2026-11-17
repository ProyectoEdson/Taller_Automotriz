"use client";

import { useRouter } from "next/navigation";
import { useState, type KeyboardEvent } from "react";

export default function OrderInput({ codigo }: { codigo: string }) {
  const router = useRouter();
  const [valor, setValor] = useState(codigo);

  function buscar() {
    const limpio = valor.trim();
    router.push(limpio ? `/cotizacion?orden=${encodeURIComponent(limpio)}` : "/cotizacion");
  }

  return (
    <input
      type="text"
      id="codigoOrden"
      name="orden"
      placeholder="Ej. AT-261116-1586"
      required
      value={valor}
      onChange={(evento) => setValor(evento.target.value)}
      onBlur={buscar}
      onKeyDown={(evento: KeyboardEvent<HTMLInputElement>) => {
        if (evento.key === "Enter") {
          evento.preventDefault();
          buscar();
        }
      }}
    />
  );
}
