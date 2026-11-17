# Taller Automotriz — Next.js (fase 4)

Requiere Node.js 22 y Docker con Compose. La aplicación PHP permanece en la raíz como referencia; este directorio contiene la migración. PostgreSQL local usa solo el contenedor `postgres:16-alpine`; no se usa Neon.

```bash
cd web
# Crear .env local (no se versiona):
printf '%s\n' 'DATABASE_URL="postgresql://taller:taller_dev_only@127.0.0.1:5432/taller_automotriz?schema=public"' > .env
node -e 'console.log("AUTH_SECRET=" + require("node:crypto").randomBytes(32).toString("hex"))' >> .env
printf '%s\n' 'AUTH_URL="http://127.0.0.1:3000"' >> .env
docker compose up -d --wait postgres
npm ci
npm run db:migrate -- --name init
npm run db:seed
npm run dev
```

Abrir `http://127.0.0.1:3000/login`. Inicia sesión con `Asadmin` / `123456` (recepcionista) o `AsaCorredor` / `789456` (mecánico). Se comprueba el perfil elegido y se redirige a `/inicio`. Inicio lee sus cuatro contadores de PostgreSQL; Diagnóstico permite crear/actualizar órdenes y repuestos, Cotización genera una sola cotización por orden y Pagos registra abonos o liquidaciones. La autenticación usa `usuario`, no correo: es una diferencia intencional respecto a RNF-01 para conservar los hashes y usuarios existentes. `AUTH_SECRET` y las credenciales de Docker son solo para desarrollo; no publicar ni versionar `.env`. `npm run db:seed` conserva los IDs y hashes bcrypt del dump de desarrollo y puede repetirse. Para inspeccionar la base sin instalar `psql` en el host: `docker compose exec postgres psql -U taller -d taller_automotriz`. Para detener el servicio: detener solo el proceso de la aplicación cuando se necesite; no usar `docker compose down` durante esta fase.

Para obtener las cuatro cifras del dashboard desde la misma instantánea SQL: `docker compose exec -T postgres psql -U taller -d taller_automotriz < prisma/counts.sql`. El seed original debe producir `2 / 0 / 0 / 0`.

## Sincronización del CSS original

`web/app/globals.css` es una copia byte a byte de `CSS/AUTOMOTRIZ.CSS`, no una fuente independiente. Cualquier ajuste futuro debe hacerse primero en el archivo original, después copiarse íntegro a `web/app/globals.css` y comprobarse desde la raíz con `cmp CSS/AUTOMOTRIZ.CSS web/app/globals.css`. Los estilos que en PHP están incrustados en la pantalla de cotización viven en `web/app/cotizacion/cotizacion.module.css`; no forman parte del CSS global.

## Cotización con cotización existente

Por la unicidad de `cotizaciones.id_orden`, una orden admite una sola cotización. Si ya existe, Cotización la muestra en solo lectura con sus importes guardados y no ofrece generar otra; si no existe, muestra la vista previa del diagnóstico y permite editar líneas antes de generar el total con IVA 7 % en el servidor. El PHP siempre muestra la vista previa; esta decisión mantiene coherente el modelo de datos y evita duplicados.

La tabla de Cotización mantiene el `min-height` original en su contenedor, no en la tabla, para que una cotización de una sola fila no estire la fila a 435 px. El CSS global sigue intacto.

## Autorización

Las páginas comprueban la sesión y el rol con `auth()` en el servidor, además del middleware. Los route handlers de escritura de la fase 4 deben repetir esa comprobación con `auth()` y responder 403; el middleware es defensa adicional, no la única autorización.

## Fase 4 — API y verificación

- `POST /api/ordenes`: mecánico; `idOrden` para actualizar diagnóstico 1:1 y reemplazar repuestos, sin ID para crear cliente/vehículo/orden/diagnóstico. El inventario se descuenta por nombre y se restituye al actualizar en la misma transacción; el seed no incluye inventario. Una prueba con `Filtro fixture` (stock 3) observó stock 1 tras usar 2, stock 2 tras reemplazarlos por 1, y 409 al pedir 5.
- `POST /api/cotizaciones`: recepción; `codigoOrden` y `detalles` con descripción, tipo, cantidad y precio decimal string. La transacción calcula subtotal, impuesto 7 % redondeado a centavos y total; UNIQUE impide segunda cotización (409).
- `POST /api/pagos`: recepción; `idCotizacion`, `monto` decimal string y `metodo`. Dentro de la transacción se bloquea `cotizaciones` con `SELECT ... FOR UPDATE` y después se consulta la suma de pagos bajo READ COMMITTED; solicitudes simultáneas por la misma cotización esperan el bloqueo y la segunda lee el saldo actualizado. Devuelve `idPago`, saldo y estado derivados. La fecha la asigna la base, no el formulario.

Escenarios ejecutados sobre PostgreSQL: alta y dos actualizaciones sin duplicados, validación 400, permisos 401/403, placa y stock 409; cotización 27.70 + 1.94 = 29.64, duplicado 409; abono 5.00 → saldo 24.64 Pendiente, liquidación 24.64 → saldo 0 Pagado, sobrepago y cobro posterior 409; dos cobros paralelos de 75.00 sobre 107.00 dieron 200 y 409 (pagado 75.00). El oráculo PHP en 8080 confirmó los mismos resultados 403/409, abono y liquidación; su `guardar_diagnostico.php` sin sesión redirige 302, mientras la API Next responde 401. No se provocó una caída de base para observar el 500; los handlers devuelven un mensaje genérico sin SQL en esa rama.

Antes de las capturas se ejecutó `npx prisma migrate reset --force` y `npx prisma db seed`: contadores del panel `2/0/0/0`; filas físicas órdenes/diagnósticos/cotizaciones/pagos `2/2/2/2`. Tras probar la UI y capturar las pantallas: panel `3/0/1/1` y filas `3/3/3/3` (orden visual, cotización y abono de 5.00). Repetir el reset para volver al seed cuando se desee, no durante una sesión con datos de trabajo.

Capturas a 1360×950: `docs/screenshots/{login,inicio,diagnostico,cotizacion,pagos}-fase4.*`. Diagnóstico muestra acciones activas; Cotización usa una orden nueva sin cotización y Pagos una cuenta sin liquidar. El inicio refleja las escrituras observadas.
