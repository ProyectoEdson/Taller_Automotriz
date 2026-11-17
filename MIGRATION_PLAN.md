# Plan de migración — fase 0 (auditoría; sin implementación)

Rama de trabajo: `migration/nextjs-typescript`, rebasada sobre `main` **9d77a8a**; referencia PHP: `main` en otra carpeta para ejecutar `dev/linux-dev.sh`. No modificar `main` ni `v1-windows`. La aprobación del baseline corregido habilita la fase 1, no las fases posteriores. **Ubicación:** conservar PHP en esta rama como referencia y crear Next.js en `web/`, sin borrar ni mover archivos existentes; `main` permanece intacta.

## 1. Tecnologías reales y fuentes

- Código actual: PHP sin framework, PDO/MySQL, sesiones PHP, HTML5, JavaScript nativo y `CSS/AUTOMOTRIZ.CSS`; MariaDB local por `dev/linux-dev.sh`. `PHP/conexion.php` consume `TALLER_DB_HOST`, `TALLER_DB_NAME`, `TALLER_DB_USER`, `TALLER_DB_PASS`. El dump declara servidor MariaDB **10.11.16** y PHP **8.3.36**; el README también indica PHP 8.3/MariaDB 10.11. La afirmación de PHP 8.5/MariaDB 11.8 no se puede confirmar desde el código ni el dump; son versiones de entorno posibles, no requisito verificado.
- Referencia funcional: `INDEX.php`, `VISTAS/LOGIN.HTML`, `VISTAS/INICIO.php`, `VISTAS/COTIZACIÓN.php`, `VISTAS/PAGOS.php`, `PHP/*.php` y el dump. No migrar `INDEX.HTML`, `VISTAS/INICIO.HTML`, `VISTAS/COTIZACIÓN.HTML`, `VISTAS/PAGOS.HTML` (prototipos localStorage). `VISTAS/LOGIN.HTML` sí es el login vivo.
- Documentos locales leídos: `Entregable No1 IngSII.pdf` (CU-08/04/05, RF/RNF y maquetas) y `FASE2. DESEÑO DEL SOFTWARE. ENTREGABLE II_3814102cc8cc490371e3aca0f4600ee8.pdf` (arquitectura, trazabilidad y diagramas). Son diseño aspiracional: sus componentes, asignación de mecánicos, estados y respaldos no prueban implementación. Figma devuelve metadatos HTML, no contenido de diseño inspeccionable; usar HTML/CSS implementado como fuente visual y declarar esta limitación en comparación de capturas.
- **Oráculo PHP actualizado (`main` 9d77a8a):** siete correcciones verificadas en `README.md` («Correcciones de implementación (fase 3)»): permisos recepcionista en consultas/pago (403), id real del pago antes del commit, estado/saldo y abonos UI, única cotización por orden (409 y UNIQUE), contadores BD en `PHP/resumen.php`, validación de longitudes y errores de negocio 400/409 o interno 500 genérico, trazabilidad RF/CU en README. La migración replica este baseline, no las deficiencias anteriores.

## 2. Matriz de equivalencia y alcance

| Caso / función | Estado PHP comprobado | Referencia | Objetivo Next.js |
|---|---|---|---|
| Login, selección de rol, sesión y cierre | Implementado; usuario+contraseña bcrypt; comparación del rol elegido | `VISTAS/LOGIN.HTML`, `PHP/validar_login.php`, `PHP/proteger_sesion.php`, `PHP/cerrar_sesion.php` | Auth.js Credentials+JWT; rol de sesión minúsculo; acceso denegado 403 por rol incorrecto |
| Inicio y accesos por rol | Implementado: menú por rol y contadores BD (`PHP/resumen.php`) | `VISTAS/INICIO.php`, `PHP/resumen.php` | Mismos contadores y accesos en PostgreSQL |
| CU-08: listar, seleccionar y actualizar orden | Implementado: cliente/vehículo de la orden seleccionada en solo lectura; actualiza diagnóstico 1:1 y reemplaza repuestos sin duplicar orden | `INDEX.php`, `PHP/listar_ordenes.php`, `PHP/consultar_diagnostico.php`, `PHP/guardar_diagnostico.php` | Mismo flujo transaccional y permisos de mecánico |
| CU-08: nueva orden | Implementado dentro de módulo de mecánico: busca cliente por nombre+apellidos, placa única, genera código y crea orden+diagnóstico | `INDEX.php`, `PHP/guardar_diagnostico.php` | Preservar comportamiento, sin crear módulo separado de recepción |
| CU-04: precarga y edición de líneas | Implementado: consulta por código, precarga diagnóstico/repuestos y permite editar líneas antes de guardar; **no** edición posterior de cotización persistida | `VISTAS/COTIZACIÓN.php`, `PHP/consultar_orden.php`, `PHP/guardar_cotizacion.php` | Misma edición antes de guardar; no inventar edición posterior |
| CU-04: cálculo y persistencia | Implementado: subtotal+7 % redondeado en servidor; una cotización por orden (UNIQUE y segundo intento 409) | `PHP/guardar_cotizacion.php`, `cotizaciones` | Cálculo decimal y unicidad con 409 |
| CU-05: cobro y estado de cuenta | Implementado: UI permite abonos y muestra saldo/estado reales; backend limita saldo, bloquea cuentas pagadas y devuelve ID capturado antes de commit | `VISTAS/PAGOS.php`, `PHP/registrar_pago.php`, `PHP/consultar_pagos.php` | Replicar abonos, estado derivado e ID real |
| Inventario / stock | Parcial: endpoint puede descontar/restituir stock si se referencia repuesto de inventario, pero no hay CRUD ni stock real en seed | `PHP/guardar_diagnostico.php`, `inventario_repuesto` vacío | Modelar tabla y repuestos utilizados; no implementar CRUD ni descuento real de stock sin aprobación |
| Asignación por gerente, cliente, reportes, estados de orden, factura/impresión | Solo documentados o incompletos; sin flujo de usuario operativo | PDFs (RF-04/07/08/11, CU-06/07/09); `estado_orden` solo columna | Pendientes fuera de alcance |

**Nota de alcance:** RF-01/02/03 de documentos describen altas por recepcionista, pero el código agrupa alta de cliente/vehículo/orden en CU-08 para mecánico. Migrar el código vigente, no los flujos aspiracionales. Login por `usuario`, no por email: discrepancia deliberada con RNF-01.

## 3. Inventario visual

| Pantalla funcional | Marcado/estilo a conservar |
|---|---|
| Login | `VISTAS/LOGIN.HTML`: encabezado con auto SVG, selección de perfil, usuario y contraseña, panel centrado |
| Inicio | `VISTAS/INICIO.php`: encabezado rojo, menú lateral, resumen de cuatro contadores y tarjetas de acceso según rol |
| Diagnóstico | `INDEX.php`: selector de orden existente/nueva, campos de cliente y vehículo, diagnóstico, reparaciones, tabla de repuestos |
| Cotización | `VISTAS/COTIZACIÓN.php`: consulta por código, información de orden, filas editables, subtotal/impuesto/total |
| Pagos | `VISTAS/PAGOS.php`: selector de cotización, datos de orden, saldo/estado, método y monto |

Conservar clases y jerarquía del marcado; importar `CSS/AUTOMOTRIZ.CSS` globalmente sin Tailwind/shadcn. Identidad: fondo gris claro, cabecera rojo oscuro (`#a00000`), bordes granate, panel blanco, sidebar gris, tipografía Arial, responsive a 900/550 px y login a 500 px. La UI funcional usa SVG inline y emojis: los archivos de `IMG/` no tienen referencias en los PHP/HTML/CSS de la app, son recursos no usados y **no se migran**. `AUDIO/`, `VIDEO/`, `JS/` sin recursos versionados útiles. Capturas PHP y Next a 1360×950 durante fase 3; Figma no comprobable como referencia de píxeles.

### Definiciones exactas de las cuatro tarjetas de Inicio

Consultas independientes, globales para ambos roles y ejecutadas con la misma instantánea de lectura. «Cotizaciones pendientes» significa órdenes diagnosticadas sin cotización; «Reparaciones activas» significa órdenes diagnosticadas sin cotización totalmente pagada (sin cotización o con saldo positivo); «Pagos pendientes» significa cotizaciones emitidas con saldo positivo. No usar `estado_orden = 'Abierta'`: no se actualiza con el cobro. Contar entidades, no filas de detalle ni pagos:

```sql
-- Órdenes registradas
SELECT COUNT(*) FROM ordenes_servicio;

-- Cotizaciones pendientes de generar (diagnóstico listo, sin cotización)
SELECT COUNT(*) FROM ordenes_servicio o
JOIN diagnostico_reparacion d ON d.id_orden = o.id_orden
WHERE NOT EXISTS (
  SELECT 1 FROM cotizaciones c WHERE c.id_orden = o.id_orden
);

-- Reparaciones activas (diagnóstico sin cotización totalmente pagada)
SELECT COUNT(*) FROM ordenes_servicio o
JOIN diagnostico_reparacion d ON d.id_orden = o.id_orden
WHERE NOT EXISTS (
  SELECT 1 FROM cotizaciones c
  WHERE c.id_orden = o.id_orden
    AND c.total <= COALESCE((
      SELECT SUM(p.monto_recibido) FROM pago_cuenta p
      WHERE p.id_cotizacion = c.id_cotizacion
    ), 0)
);

-- Pagos pendientes (cotizaciones emitidas con saldo positivo, incluye abonos)
SELECT COUNT(*) FROM cotizaciones c
WHERE c.total > COALESCE((
  SELECT SUM(p.monto_recibido) FROM pago_cuenta p
  WHERE p.id_cotizacion = c.id_cotizacion
), 0);
```


## 4. Esquema y datos

Origen `BASE DE DATO/taller_automotriz.sql`: **11 tablas**; seed equivalente con IDs, fechas y 2 clientes, 2 vehículos, 2 órdenes, 2 diagnósticos, 5 repuestos utilizados, 2 cotizaciones, 5 detalles, 2 pagos, 2 roles, 6 usuarios; `inventario_repuesto` sin filas. Dump sin `CREATE DATABASE`. Demo `Asadmin`/`123456` (rol 2, recepcionista), `AsaCorredor`/`789456` (rol 1, mecánico); conservar hashes bcrypt `$2y$` y comprobar interoperabilidad con bcryptjs.

Relaciones: `clientes` 1:N `vehiculos` 1:N `ordenes_servicio`; orden 1:1 `diagnostico_reparacion` 1:N `repuestos_utilizados` N:1 opcional `inventario_repuesto`; orden 1:N `cotizaciones` 1:N `detalle_cotizacion` y 1:N `pago_cuenta`; pago también N:1 orden; `roles` 1:N `usuarios`. En PostgreSQL nueva unicidad `cotizaciones.id_orden` para impedir duplicados, además de existentes `vehiculos.placa`, `ordenes_servicio.codigo_orden`, `usuarios.usuario`, `diagnostico_reparacion.id_orden`. Índices restantes en FKs como dump. FKs `onDelete: Restrict`; campo `vehiculos.año` pasa a `anio` en modelo y UI (sin carácter especial en columna PostgreSQL). PK `Int @id @default(autoincrement())`, dinero `Decimal @db.Decimal(10,2)`, fecha `DateTime`, `longtext` → `String`, estados/tipos `String`, kilometraje `Float?` (origen `double`); respetar longitudes varchar mediante validación y, donde corresponda, `@db.VarChar(n)`. Preservar nulos/defaults y secuencias posteriores al seed.

**Decisiones confirmadas en el baseline PHP:** una cotización por orden mediante `@unique` y segundo intento 409; el dump no contiene duplicados, por lo que seed compatible. **Abonos habilitados en la UI:** el monto puede ser cualquier valor positivo hasta el saldo pendiente, incluido el pago total. No permitir actualización posterior de una cotización pagada; edición de líneas se refiere al formulario **antes** del guardado. Dedupe de clientes por nombre+apellidos es débil (homónimos); no sustituir por email ni ID sin solicitud.

## 5. Reglas, diferencias y riesgos

- Totales, IVA 7 % y redondeo a centavos solo autorizados por servidor con aritmética decimal. Líneas `Servicio`/`Repuesto`, cantidad entera positiva, precio no negativo. Código `AT-<aammdd>-<4 dígitos>` único, generado si falta, manejar colisión de generación. Placa normalizada a mayúsculas y nunca transferir una placa a otro cliente.
- Pagos positivos ≤ saldo de `cotización.total − SUM(pago_cuenta.monto_recibido)`; guardar con transacción y exclusión de concurrencia sobre cotización para evitar sobrepago. Estado derivado `Pagado` si saldo = 0; si no, `Pendiente`. Verificar consistencia `pago_cuenta.id_orden` con `id_cotizacion`. Capturar id antes del commit. Consultas y escritura de pagos exigen recepcionista, como el baseline PHP actualizado.
- Zod y errores claros para campos y longitudes, no exponer errores SQL. Autorización del lado servidor en todas las rutas/acciones y vistas; un rol incorrecto recibe 403. Hashes y rol `id_rol` son fuente de verdad.
- Riesgos: divergencia PHP/diseño PDF (asignación, stock, facturación, respaldos, rendimiento y SLA no implementados); dos cotizaciones futuras son incompatibles con unicidad nueva; cambio MySQL→PostgreSQL exige revisar precisión, zona horaria y secuencias. El seed incluye cuentas y contraseñas demo: exclusivamente entorno de desarrollo, nunca despliegue público. UI cotización posterior al diagnóstico puede quedar desactualizada si se cambia diagnóstico: no recalcular cotizaciones ya guardadas de manera implícita. Vercel y Neon gratuitos sujetos a cuotas y condiciones actuales; no garantizan RNF-04 99,5 %, respaldo diario RNF-05 ni 50 usuarios simultáneos RNF-07.
- Correcciones del baseline PHP **ya implementadas y a reproducir**, no nuevas propuestas: contadores BD, estado por suma, id de pago, permisos de consultas/pago, unicidad cotización, abonos UI, validación de longitudes y errores, login por usuario documentado. `id_usuario` en órdenes/pagos y CSRF/cabeceras quedan pendientes opcionales, fuera de esta migración. No otras correcciones sin registrar y pedir aprobación.

**Versiones objetivo fijadas:** Node.js 22.x (desarrollo y despliegue; fijar en `engines`), Next.js **15.5.29**, React y React DOM **19.0.0**, Prisma CLI y cliente **6.0.0** (mismo número exacto), PostgreSQL **16** mediante imagen `postgres:16-alpine`. Fijar dependencias npm sin `^`/`~` y commitear lockfile en fase 1; el tag Docker fija la serie mayor, no el digest ni la versión menor de Alpine. Verificar compatibilidad en fase 1 antes de instalar; cualquier cambio de versiones requiere aprobación.

**PostgreSQL local:** `docker compose` con servicio `postgres` basado en `postgres:16-alpine`, puerto local y volumen persistente; credenciales de desarrollo por variables del `.env` local, no versionar secretos. No hay `psql` en el host ni imagen Docker local: Compose descargará la imagen solo tras aprobar fase 1; ejecutar SQL de verificación con `docker compose exec postgres psql ...`, migraciones/seed desde `web/`. Sin Neon ni conexión externa en desarrollo. Verificar `docker compose version` y acceso al daemon al comenzar fase 1.

## 6. Plan de fases y puertas

Cada fase termina con evidencia de comandos/resultados y espera aprobación antes de avanzar.

1. **Fase 0 (esta entrega):** inventario y decisiones en este archivo. Sin código; solicitar aprobación antes de fase 1.
2. **Fase 1:** scaffold Next.js App Router/React/TS estricto en `web/` (PHP original permanece en la rama solo como referencia); Prisma + PostgreSQL local por Docker Compose, 11 modelos, migración, seed fiel y conexión. Verificar migración y consultas/contadores con `docker compose exec postgres psql`; nunca conectar Neon durante desarrollo.
3. **Fase 2:** Auth.js v5 Credentials/JWT, bcryptjs, sesión con rol minúsculo, redirecciones y controles 403. Probar ambos usuarios demo, clave/rol erróneos, cierre y autorización.
4. **Fase 3:** pantallas login, Inicio, Diagnóstico, Cotización, Pagos con marcado/clases/CSS existente y lectura real; interacción conforme se habiliten flujos. Capturas PHP en clon separado de `main` y Next a 1360×950, comparar visualmente. No trasladar prototipos localStorage.
5. **Fase 4:** implementar CU-08 → CU-04 → CU-05 con transacciones Prisma, Zod, checks de permisos y totales/estado del servidor. Pruebas de alta y actualización de orden sin duplicar, cálculo 7 %, pago total, abono y rechazo de sobrepago contra PostgreSQL.
6. **Fase 5:** aplicar y verificar matriz de bugs, casos límite, permisos, pruebas reproducibles, `npm run build`, `tsc --noEmit` y lint; registrar trazabilidad CU → ruta/función en documentación tras implementar.
7. **Fase 6:** README, `.env.example`, guía local PostgreSQL/Prisma y preparación Vercel/Neon (incluido pooling compatible con Prisma). Solo documentación; sin cuentas, tarjetas ni despliegue.

**Coste:** código y desarrollo local con herramientas libres y sin servicios de pago. La preparación Vercel/Neon puede aprovechar planes gratuitos, pero límites, disponibilidad y continuidad del precio no están garantizados. No se crearán cuentas ni se desplegará sin permiso.

## 7. Checklist único de aceptación final

- [ ] Login de `Asadmin` y `AsaCorredor`; credenciales/rol incorrectos rechazados y acceso indebido a vista o API devuelve 403.
- [ ] Mecánico lista y selecciona orden, lee cliente/vehículo/placa sin editarlos y actualiza diagnóstico/repuestos dos veces sin duplicar orden ni diagnóstico.
- [ ] Recepcionista crea una sola cotización por orden, edita sus líneas antes de guardar y el servidor calcula subtotal, impuesto 7 % a dos decimales y total.
- [ ] Abono parcial desde UI deja saldo positivo y estado `Pendiente`; datos persisten en PostgreSQL.
- [ ] Pago total deja saldo cero y estado `Pagado`; se devuelve `id_pago` real.
- [ ] Sobrepago y pago posterior a liquidación rechazados; consultas y registro de pagos restringidos a recepcionista (403).
- [ ] Contadores (órdenes/cotizaciones pendientes/reparaciones activas/pagos pendientes): seed `2/0/0/0`; orden diagnosticada sin cotizar `3/1/1/0`; cotizada sin pagar `3/0/1/1`; tras abono `3/0/1/1`; tras pago total `3/0/0/0`.
- [ ] Las cinco pantallas funcionan y capturas PHP/Next a 1360×950 permiten comparar fidelidad visual.
- [ ] `npm run build`, `tsc --noEmit` y lint pasan; pruebas reproducibles de los tres CU y permisos.
- [ ] `main` mantiene historial y árbol intactos; la rama de migración no modifica la versión PHP de `main`.
- [ ] Arranque local en Linux con comando documentado en `web/`, PostgreSQL Docker Compose y seed; preparación Vercel/Neon solo documental.

## Trazabilidad implementada, fase 5

| CU | Página y formulario | Endpoint y función | Transacción y evidencia |
|---|---|---|---|
| CU-08 Diagnóstico | `web/app/diagnostico/page.tsx` (`DiagnosticoPage`), `DiagnosticoPanel.tsx` (`guardar`) | `web/app/api/ordenes/route.ts` (`POST`): autorización, validación, cliente/vehículo, orden, diagnóstico y repuestos | `prisma.$transaction`; bloqueo de orden existente, restitución/descuento condicional de inventario y reemplazo 1:1. `web/scripts/phase5-matrix.mjs`: alta, dos actualizaciones, placa propia/ajena, stock y 400/401/403/409/500. |
| CU-04 Cotización | `web/app/cotizacion/page.tsx` (`Cotizacion`), `CotizacionEditor.tsx` (`generar`, vista previa en centavos) | `web/app/api/cotizaciones/route.ts` (`POST`): líneas, `Prisma.Decimal`, IVA 7 % | `prisma.$transaction`; `id_orden` UNIQUE impide duplicados. `phase5-matrix.mjs`: 27.70/1.94/29.64, duplicado y errores. Cotización existente en solo lectura. |
| CU-05 Pagos | `web/app/pagos/page.tsx` (`Pagos`), `PagosCliente.tsx` (`registrar`) | `web/app/api/pagos/route.ts` (`POST`): saldo, método, ID real y estado | `prisma.$transaction`; `SELECT ... FOR UPDATE` sobre cotización antes de sumar pagos. `phase5-matrix.mjs`: abono, liquidación, sobrepago, cierre, concurrencia 200/409 y errores. |

Las tres páginas comprueban `auth()`; el middleware y cada handler también verifican acceso. El panel conserva las cuatro definiciones de `web/prisma/counts.sql`. La matriz de fase 5 restaura el seed (`2/0/0/0`) y las capturas están en `web/docs/screenshots/*-fase5.*`. La fase 6 agrega únicamente documentación local, [guía de preparación](web/docs/DEPLOYMENT.md) y [enlaces a capturas](web/README.md#fase-5--matriz-reproducible-y-estado-final); no se hizo despliegue.
