# Taller Automotriz — Next.js

Migración funcional de CU-08 (diagnóstico), CU-04 (cotización) y CU-05 (pagos). La versión PHP anterior vive en la rama `php-legacy` y no forma parte de `main`; se conserva como referencia. Requiere **Node.js 22.x**, npm y Docker Compose. Trabajar desde `web/`; el PostgreSQL local es `postgres:16-alpine`, enlazado solo a `127.0.0.1:5432` con volumen persistente (`compose.yaml`).

## Instalación local

```bash
cd web
cp .env.example .env
```

Editar **solo** `.env` (ignorado por Git): sustituir `USER:PASSWORD` de `DATABASE_URL` por `taller:taller_dev_only` del `compose.yaml` local y generar un secreto propio con `node -e 'console.log(require("node:crypto").randomBytes(32).toString("hex"))'`; pegarlo en `AUTH_SECRET`. Mantener `AUTH_URL=http://127.0.0.1:3000` si se usa el puerto predeterminado. No usar estas credenciales ni el seed demo en un servicio público.

| Variable | Uso local |
|---|---|
| `DATABASE_URL` | PostgreSQL del contenedor (`127.0.0.1:5432/taller_automotriz?schema=public`); `schema.prisma` y Prisma Client la leen. |
| `AUTH_SECRET` | Clave aleatoria privada para JWT/Auth.js; obligatoria y distinta por entorno. |
| `AUTH_URL` | Origen de la app; localmente `http://127.0.0.1:3000`. |

```bash
docker compose up -d --wait postgres
npm ci
npx prisma migrate deploy
npx prisma db seed
npm run dev
```

Abrir `http://127.0.0.1:3000/login`. Cuentas **solo de desarrollo**: `Asadmin` / `123456` (recepcionista) y `AsaCorredor` / `789456` (mecánico). El perfil elegido debe coincidir con el rol. Login por `usuario`, no email, para conservar los hashes del dump. Inicio consulta cuatro contadores de PostgreSQL. `npx prisma migrate deploy` aplica migraciones existentes sin crear otra; `npx prisma db seed` es repetible, pero no limpia datos de trabajo. Para empezar desde cero **solo en la base local descartable**, `npx prisma migrate reset --force` (incluye seed) y después `npx prisma db seed` si se desea repetirlo.

Verificar los cuatro contadores en una instantánea SQL desde `web/`: `docker compose exec -T postgres psql -U taller -d taller_automotriz < prisma/counts.sql`. El seed da `2/0/0/0`. Inspeccionar tablas: `docker compose exec -T postgres psql -U taller -d taller_automotriz`. No versionar `.env`; `.env.example` contiene únicamente marcadores de posición.

## Sincronización del CSS original

`web/app/globals.css` es la hoja de estilos de la aplicación actual. La versión PHP y su `CSS/AUTOMOTRIZ.CSS` original quedaron en la rama `php-legacy`; no se sincronizan desde `main`. Los estilos que en PHP están incrustados en la pantalla de cotización viven en `web/app/cotizacion/cotizacion.module.css`; no forman parte del CSS global.

## Cotización con cotización existente

Por la unicidad de `cotizaciones.id_orden`, una orden admite una sola cotización. Si ya existe, Cotización la muestra en solo lectura con sus importes guardados y no ofrece generar otra; si no existe, muestra la vista previa del diagnóstico y permite editar líneas antes de generar el total con IVA 7 % en el servidor. El PHP siempre muestra la vista previa; esta decisión mantiene coherente el modelo de datos y evita duplicados.

La tabla de Cotización mantiene el `min-height` original en su contenedor, no en la tabla, para que una cotización de una sola fila no estire la fila a 435 px. El CSS global sigue intacto.

## Autorización

Las páginas comprueban la sesión y el rol con `auth()` en el servidor, además del middleware. Los route handlers de escritura repiten la comprobación con `auth()` y responden 401 sin sesión o 403 por rol; el middleware es defensa adicional, no la única autorización.

## Fase 4 — API y verificación

- `POST /api/ordenes`: mecánico; `idOrden` para actualizar diagnóstico 1:1 y reemplazar repuestos, sin ID para crear cliente/vehículo/orden/diagnóstico. El inventario se descuenta por nombre y se restituye al actualizar en la misma transacción; el seed no incluye inventario. Una prueba con `Filtro fixture` (stock 3) observó stock 1 tras usar 2, stock 2 tras reemplazarlos por 1, y 409 al pedir 5.
- `POST /api/cotizaciones`: recepción; `codigoOrden` y `detalles` con descripción, tipo, cantidad y precio decimal string. La transacción calcula subtotal, impuesto 7 % redondeado a centavos y total; UNIQUE impide segunda cotización (409).
- `POST /api/pagos`: recepción; `idCotizacion`, `monto` decimal string y `metodo`. Dentro de la transacción se bloquea `cotizaciones` con `SELECT ... FOR UPDATE` y después se consulta la suma de pagos bajo READ COMMITTED; solicitudes simultáneas por la misma cotización esperan el bloqueo y la segunda lee el saldo actualizado. Devuelve `idPago`, saldo y estado derivados. La fecha la asigna la base, no el formulario.

Escenarios ejecutados sobre PostgreSQL: alta y dos actualizaciones sin duplicados, validación 400, permisos 401/403, placa y stock 409; cotización 27.70 + 1.94 = 29.64, duplicado 409; abono 5.00 → saldo 24.64 Pendiente, liquidación 24.64 → saldo 0 Pagado, sobrepago y cobro posterior 409; dos cobros paralelos de 75.00 sobre 107.00 dieron 200 y 409 (pagado 75.00). El oráculo PHP en 8080 confirmó los mismos resultados 403/409, abono y liquidación; su `guardar_diagnostico.php` sin sesión redirige 302, mientras la API Next responde 401.

Antes de las capturas se ejecutó `npx prisma migrate reset --force` y `npx prisma db seed`: contadores del panel `2/0/0/0`; filas físicas órdenes/diagnósticos/cotizaciones/pagos `2/2/2/2`. Tras probar la UI y capturar las pantallas: panel `3/0/1/1` y filas `3/3/3/3` (orden visual, cotización y abono de 5.00). Repetir el reset para volver al seed cuando se desee, no durante una sesión con datos de trabajo.

Capturas a 1360×950: `docs/screenshots/{login,inicio,diagnostico,cotizacion,pagos}-fase4.*`. Diagnóstico muestra acciones activas; Cotización usa una orden nueva sin cotización y Pagos una cuenta sin liquidar. El inicio refleja las escrituras observadas.

## Comprobaciones previas a fase 5

- Antes de persistir, la cotización muestra subtotal/IVA/total reactivos calculados en centavos para evitar errores de coma flotante; el servidor valida y recalcula con `Prisma.Decimal`. Una edición observada en navegador produjo `37.05 + 2.59 = 39.64`, igual a lo persistido y a lo mostrado tras recargar. Las cotizaciones existentes siguen en solo lectura.
- Al crear una nueva orden con una placa del mismo cliente, se actualizan marca, modelo, año y kilometraje del vehículo existente sin transferirlo; una placa de otro cliente devuelve 409. La prueba actualizó el vehículo 2 a Honda/Civic/2020/12345 y creó otra orden sobre `id_vehiculo=2`.
- Para comprobar el 500 sin afectar PostgreSQL, se arrancó una segunda instancia en puerto 3001 con `DATABASE_URL` apuntando a un puerto local cerrado (65432). Los tres endpoints devolvieron `HTTP 500` con cuerpo exacto `{"mensaje":"Error interno"}`; ningún mensaje SQL llegó al cliente.
- Al terminar estas pruebas se repitió `npx prisma migrate reset --force` y `npx prisma db seed`: el panel quedó en `2/0/0/0`; filas clientes/vehículos/órdenes/diagnósticos/repuestos/cotizaciones/detalles/pagos/inventario `2/2/2/2/5/2/5/2/0`. Este es el estado actual de la base; las capturas anteriores reflejan el escenario de prueba, no el seed final.

## Fase 5 — matriz reproducible y estado final

Desde `web/`, con `.env` local y PostgreSQL saludable, ejecutar `node --env-file=.env scripts/phase5-matrix.mjs`. **Destruye los datos de la base local `taller_automotriz`**: aplica `prisma migrate reset --force` y el seed al inicio y al final (también si una aserción falla). Levanta Next temporalmente en `127.0.0.1:3100`, usa sesiones reales de ambos perfiles, endpoints HTTP reales y consultas Prisma a PostgreSQL; no usa mocks ni servicios externos. Para comprobar el 500 detiene la aplicación temporal y la reinicia con un `DATABASE_URL` hacia el puerto cerrado 65432; no detiene PostgreSQL. Ante fallo del script, la aserción mantiene su error y no se ignora.

| Escenario de `phase5-matrix.mjs` | Aserciones sobre HTTP y PostgreSQL |
|---|---|
| Sesión y permisos | API 401 sin sesión, 403 por rol; páginas 403 y redirect sin sesión. |
| CU-08 diagnóstico | 400 por longitud, dos actualizaciones con un solo diagnóstico y un repuesto; stock fixture 3→2→1→2 y 409 insuficiente; placa propia conserva año 2027/km 49998 si llegan `null`, placa ajena 409. |
| CU-04 cotización | 400 sin líneas; `27.70 + 1.94 = 29.64` persistidos; segunda cotización 409. |
| CU-05 pagos | 400 monto cero; abono 5.00 → saldo 24.64 Pendiente, liquidación 24.64 → 0 Pagado; sobrepago/cierre 409, dos pagos simultáneos de 75.00 sobre 107.00 → 200/409 y SUM 75.00; IDs reales. |
| Falla controlada | Base aislada en puerto cerrado 65432: los tres endpoints responden 500 y `{"mensaje":"Error interno"}`, sin SQL en respuesta. |

Comandos de verificación desde `web/`: `npm run lint`, `npx tsc --noEmit`, `npm run build`, `node --env-file=.env scripts/phase5-matrix.mjs` y la consulta SQL anterior. El build ha mostrado advertencias de `jose` sobre `CompressionStream`/`DecompressionStream` en Edge Runtime; [riesgo y verificación pendientes](docs/DEPLOYMENT.md#riesgo-conocido-jose-en-edge-runtime), sin ocultar el aviso. El script restaura el panel `2/0/0/0` y las filas clientes/vehículos/órdenes/diagnósticos/repuestos/cotizaciones/detalles/pagos/inventario `2/2/2/2/5/2/5/2/0`.

Trazabilidad CU → archivos/funciones en [`MIGRATION_PLAN.md`](../MIGRATION_PLAN.md#trazabilidad-implementada-fase-5). [Preparación Vercel/Neon, sin despliegue](docs/DEPLOYMENT.md).

| Pantalla (1360×950) | Fase 4 | Fase 5 |
|---|---|---|
| Login | [captura](docs/screenshots/login-fase4.webp) | [captura](docs/screenshots/login-fase5.webp) |
| Inicio | [captura](docs/screenshots/inicio-fase4.webp) | [captura](docs/screenshots/inicio-fase5.webp) |
| Diagnóstico | [captura](docs/screenshots/diagnostico-fase4.png) | [captura](docs/screenshots/diagnostico-fase5.png) |
| Cotización | [captura](docs/screenshots/cotizacion-fase4.png) | [captura](docs/screenshots/cotizacion-fase5.png) |
| Pagos | [captura](docs/screenshots/pagos-fase4.png) | [captura](docs/screenshots/pagos-fase5.png) |
