# Taller Automotriz — Next.js (fase 1)

Requiere Node.js 22 y Docker con Compose. La aplicación PHP permanece en la raíz como referencia; este directorio contiene la migración. PostgreSQL local usa solo el contenedor `postgres:16-alpine`; no se usa Neon.

```bash
cd web
# Crear .env local (no se versiona):
printf '%s\n' 'DATABASE_URL="postgresql://taller:taller_dev_only@127.0.0.1:5432/taller_automotriz?schema=public"' > .env
docker compose up -d --wait postgres
npm ci
npm run db:migrate -- --name init
npm run db:seed
npm run dev
```

Abrir `http://127.0.0.1:3000/`. La pantalla inicial solo comprueba la conexión y cuenta órdenes; las pantallas y flujos se habilitarán tras aprobar las siguientes fases. `npm run db:seed` conserva los IDs y hashes bcrypt del dump de desarrollo y puede repetirse. Para inspeccionar la base sin instalar `psql` en el host: `docker compose exec postgres psql -U taller -d taller_automotriz`. El servicio usa credenciales de **desarrollo únicamente**, no publicarlo en Internet. Para detenerlo: `docker compose down` (no elimina el volumen).

Para obtener las cuatro cifras del dashboard desde la misma instantánea SQL: `docker compose exec -T postgres psql -U taller -d taller_automotriz < prisma/counts.sql`. El seed original debe producir `2 / 0 / 0 / 0`.
