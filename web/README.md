# Taller Automotriz — Next.js (fase 3)

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

Abrir `http://127.0.0.1:3000/login`. Inicia sesión con `Asadmin` / `123456` (recepcionista) o `AsaCorredor` / `789456` (mecánico). Se comprueba el perfil elegido y se redirige a `/inicio`. Inicio lee sus cuatro contadores de PostgreSQL; Diagnóstico, Cotización y Pagos consultan órdenes y cuentas reales, pero sus acciones de registro siguen deshabilitadas hasta la fase 4. La autenticación usa `usuario`, no correo: es una diferencia intencional respecto a RNF-01 para conservar los hashes y usuarios existentes. `AUTH_SECRET` y las credenciales de Docker son solo para desarrollo; no publicar ni versionar `.env`. `npm run db:seed` conserva los IDs y hashes bcrypt del dump de desarrollo y puede repetirse. Para inspeccionar la base sin instalar `psql` en el host: `docker compose exec postgres psql -U taller -d taller_automotriz`. Para detener el servicio: `docker compose down` (no elimina el volumen).

Para obtener las cuatro cifras del dashboard desde la misma instantánea SQL: `docker compose exec -T postgres psql -U taller -d taller_automotriz < prisma/counts.sql`. El seed original debe producir `2 / 0 / 0 / 0`.

## Sincronización del CSS original

`web/app/globals.css` es una copia byte a byte de `CSS/AUTOMOTRIZ.CSS`, no una fuente independiente. Cualquier ajuste futuro debe hacerse primero en el archivo original, después copiarse íntegro a `web/app/globals.css` y comprobarse desde la raíz con `cmp CSS/AUTOMOTRIZ.CSS web/app/globals.css`. Los estilos que en PHP están incrustados en la pantalla de cotización viven en `web/app/cotizacion/cotizacion.module.css`; no forman parte del CSS global.

## Autorización

Las páginas comprueban la sesión y el rol con `auth()` en el servidor, además del middleware. Los route handlers de escritura de la fase 4 deben repetir esa comprobación con `auth()` y responder 403; el middleware es defensa adicional, no la única autorización.
