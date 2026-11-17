"use client";

import { useEffect, useId, useRef, useState } from "react";
import type { CatalogoRepuesto } from "./types";

function normalizar(texto: string) {
  return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
}

export default function RepuestoPicker({
  catalogo,
  value,
  onChange,
  onSelect,
  selectedId,
}: {
  catalogo: CatalogoRepuesto[];
  value: string;
  onChange: (value: string) => void;
  onSelect: (repuesto: CatalogoRepuesto) => void;
  selectedId: number | null;
}) {
  const [abierto, setAbierto] = useState(false);
  const [activo, setActivo] = useState(-1);
  const contenedor = useRef<HTMLDivElement>(null);
  const listaId = useId();
  const consulta = normalizar(value.trim());
  const filtrados = consulta
    ? catalogo.filter((repuesto) => normalizar(`${repuesto.nombre} ${repuesto.descripcion}`).includes(consulta))
    : catalogo;

  useEffect(() => {
    function alHacerClic(event: MouseEvent) {
      if (contenedor.current && !contenedor.current.contains(event.target as Node)) setAbierto(false);
    }
    document.addEventListener("mousedown", alHacerClic);
    return () => document.removeEventListener("mousedown", alHacerClic);
  }, []);

  function seleccionar(repuesto: CatalogoRepuesto) {
    if (repuesto.stock <= 0) return;
    onSelect(repuesto);
    setAbierto(false);
    setActivo(-1);
  }

  function abrirLista() {
    setAbierto(true);
    setActivo(filtrados.length ? 0 : -1);
  }

  return (
    <div className="repuesto-picker" ref={contenedor}>
      <input
        id="nuevoRepuestoNombre"
        type="text"
        maxLength={150}
        placeholder="Nombre del repuesto"
        aria-label="Nombre del repuesto"
        role="combobox"
        aria-autocomplete="list"
        aria-expanded={abierto}
        aria-controls={listaId}
        aria-activedescendant={abierto && activo >= 0 && filtrados[activo] ? `${listaId}-${filtrados[activo].id}` : undefined}
        autoComplete="off"
        value={value}
        onFocus={abrirLista}
        onClick={abrirLista}
        onChange={(event) => {
          onChange(event.target.value);
          setAbierto(true);
          setActivo(0);
        }}
        onKeyDown={(event) => {
          if (event.key === "ArrowDown") {
            event.preventDefault();
            if (!abierto) return abrirLista();
            setActivo((posicion) => (filtrados.length ? (posicion + 1) % filtrados.length : -1));
          } else if (event.key === "ArrowUp") {
            event.preventDefault();
            if (!abierto) return abrirLista();
            setActivo((posicion) => (filtrados.length ? (posicion - 1 + filtrados.length) % filtrados.length : -1));
          } else if (event.key === "Enter") {
            if (abierto && activo >= 0 && filtrados[activo]) {
              event.preventDefault();
              seleccionar(filtrados[activo]);
            }
          } else if (event.key === "Escape" || event.key === "Tab") {
            setAbierto(false);
            setActivo(-1);
          }
        }}
      />
      {abierto && (
        <ul className="repuesto-lista" id={listaId} role="listbox" aria-label="Repuestos del inventario">
          {filtrados.length === 0 ? (
            <li className="repuesto-vacio">
              {consulta ? "Sin coincidencias. Se guardará como repuesto manual." : "No hay repuestos registrados."}
            </li>
          ) : (
            filtrados.map((repuesto, indice) => {
              const sinStock = repuesto.stock <= 0;
              return (
                <li
                  key={repuesto.id}
                  id={`${listaId}-${repuesto.id}`}
                  role="option"
                  aria-selected={selectedId === repuesto.id}
                  aria-disabled={sinStock}
                  className={`repuesto-opcion${indice === activo ? " activa" : ""}${sinStock ? " sin-stock" : ""}`}
                  onMouseDown={(event) => event.preventDefault()}
                  onMouseEnter={() => setActivo(indice)}
                  onClick={() => seleccionar(repuesto)}
                >
                  <span className="repuesto-opcion-datos">
                    <strong>{repuesto.nombre}</strong>
                    <span>{repuesto.descripcion || "Sin descripción"}</span>
                  </span>
                  <span className="repuesto-opcion-meta">
                    <span>{sinStock ? "Sin stock" : `Stock: ${repuesto.stock}`}</span>
                    <strong>B/. {repuesto.precio}</strong>
                  </span>
                </li>
              );
            })
          )}
        </ul>
      )}
    </div>
  );
}
