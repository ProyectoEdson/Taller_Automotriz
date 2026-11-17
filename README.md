# Taller Automotriz — Fase 3

Aplicación web para la gestión de un taller automotriz: clientes, vehículos, órdenes de trabajo, diagnóstico, reparaciones, cotizaciones y pagos.

La aplicación actual está en `web/`:

- Next.js 15 + React 19 + TypeScript
- Prisma + PostgreSQL (Neon)
- Auth.js con credenciales
- Zod

La versión PHP anterior ya no se usa en `main`; se conserva en la rama `php-legacy`. La versión original de Windows está en el tag `v1-windows`.

- URL pública: https://taller-automotriz-fase-3.vercel.app
- Documentación: `web/README.md`, `MIGRATION_PLAN.md`, `web/docs/DEPLOYMENT.md`

## Ejecutar en local

```bash
cd web
cp .env.example .env
# completa DATABASE_URL, AUTH_SECRET y AUTH_URL
npm ci
npx prisma migrate deploy
npx prisma db seed
npm run dev
```

Abrir http://localhost:3000/login.

## Despliegue

La app está desplegada en Vercel con Root Directory `web/` y PostgreSQL en Neon. Ver `web/docs/DEPLOYMENT.md`.

## Estructura

- `web/` — aplicación Next.js
- `MIGRATION_PLAN.md` — plan y trazabilidad de la migración
- `web/docs/screenshots/` — evidencia visual de las fases
