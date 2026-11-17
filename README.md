# Taller Automotriz

Aplicación web para la operación diaria de un taller automotriz: registro de clientes y vehículos, órdenes de servicio, diagnóstico y reparación, cotización con impuesto y cobro de pagos parciales o totales. Incluye autenticación con roles de **mecánico** y **recepción**, panel con indicadores y una API interna consumida por la interfaz.

**Demo:** https://taller-automotriz-fase-3.vercel.app

| Perfil | Usuario | Contraseña |
| --- | --- | --- |
| Recepción | `Asadmin` | `123456` |
| Mecánico | `AsaCorredor` | `789456` |

> Las credenciales son de demostración. No usar datos reales sin reemplazarlas y configurar un `AUTH_SECRET` propio.

## Funcionalidades

- **Inicio de sesión** con credenciales y validación del perfil seleccionado; sesión JWT y cierre de sesión.
- **Control de acceso por rol** en páginas, middleware y endpoints: `401` sin sesión, `403` con rol incorrecto.
- **Panel de inicio** con cuatro indicadores calculados en una sola instantánea de la base: órdenes, cotizaciones pendientes, reparaciones activas y pagos pendientes.
- **Diagnóstico y reparación** (mecánico):
  - Crear una orden nueva con cliente, vehículo, diagnóstico, reparaciones y repuestos.
  - Actualizar una orden existente sin duplicar cliente ni vehículo.
  - Reutilizar clientes por nombre y apellidos, y vehículos por placa.
  - Seleccionar repuestos del inventario con autocompletado, stock disponible y precio sugerido.
  - Descontar stock de repuestos y restituirlo al editar el diagnóstico.
  - Código de orden autogenerado (`AT-AAAAMMDD-NNNN`).
- **Cotización** (recepción):
  - Buscar la orden por código y mostrar una vista previa del diagnóstico.
  - Editar líneas de servicio o repuesto, con cantidades y precios.
  - Calcular subtotal, IVA del **7 %** y total en el servidor con `Decimal`.
  - Una sola cotización por orden; si ya existe, se muestra en modo lectura con sus importes guardados.
- **Pagos** (recepción):
  - Registrar abonos por efectivo, tarjeta, transferencia o Yappy.
  - Calcular saldo pendiente y cambiar el estado a **Pendiente** o **Pagado**.
  - Bloquear sobrepagos, pagos posteriores al saldo cero y cobros duplicados.
  - Serializar pagos concurrentes a la misma cotización con bloqueo de fila.
- **Validación** de todas las entradas con Zod y respuestas JSON consistentes.
- **Interfaz responsive** con estilos propios en CSS, sin framework de UI.

## Tecnologías

| Área | Tecnología | Versión |
| --- | --- | --- |
| Framework | Next.js (App Router) | 15.5.29 |
| UI | React / React DOM | 19.0.0 |
| Lenguaje principal | TypeScript | 5.9.3 |
| ORM | Prisma Client / Prisma CLI | 6.0.0 |
| Base de datos | PostgreSQL | 16 (`postgres:16-alpine` en local) / Neon en producción |
| Autenticación | Auth.js (NextAuth) | 5.0.0-beta.30 |
| Hash de contraseñas | bcryptjs | 3.0.2 |
| Validación | Zod | 3.24.2 |
| Estilos | CSS global + CSS Modules | — |
| Calidad | ESLint 9 / eslint-config-next | 9.39.1 / 15.5.29 |
| Ejecución | Node.js | 22.x |
| Despliegue | Vercel | — |
| Contenedores (local) | Docker Compose | — |

## Lenguajes del repositorio

| Lenguaje | Dónde se usa |
| --- | --- |
| **TypeScript** | Páginas, componentes, API, autenticación, middleware, seed y configuración. Es el lenguaje principal del proyecto. |
| **CSS** | Hoja global y módulos de la pantalla de cotización. |
| **JavaScript** | Script de verificación de escenarios y configuración de ESLint. |
| **SQL** | Migraciones de Prisma y consulta de indicadores. |
| **JSON** | `package.json`, `package-lock.json` y `tsconfig.json`. |
| **Markdown** | Este README y la documentación de despliegue. |

## Arquitectura

- **Next.js App Router** con páginas de servidor y componentes cliente para los formularios interactivos.
- **API interna** en `app/api/*` para login, órdenes, cotizaciones y pagos.
- **Auth.js** con proveedor de credenciales, sesión JWT y el rol incluido en el token.
- **Middleware** que protege las rutas por prefijo y rol antes de renderizar.
- **Prisma** como única capa de acceso a datos, con transacciones interactivas para operaciones que afectan varias tablas.
- **PostgreSQL** con claves foráneas, índices únicos y restricciones de integridad.
- **Zod** valida cuerpos de solicitud; `Prisma.Decimal` evita errores de coma flotante en importes.

## Estructura

```text
.
├── web/
│   ├── app/
│   │   ├── api/
│   │   │   ├── auth/[...nextauth]/route.ts
│   │   │   ├── login/route.ts
│   │   │   ├── ordenes/route.ts
│   │   │   ├── cotizaciones/route.ts
│   │   │   └── pagos/route.ts
│   │   ├── login/page.tsx
│   │   ├── inicio/page.tsx
│   │   ├── diagnostico/page.tsx
│   │   ├── cotizacion/page.tsx
│   │   ├── pagos/page.tsx
│   │   ├── globals.css
│   │   └── layout.tsx
│   ├── components/
│   │   ├── WorkshopShell.tsx
│   │   └── BodyClass.tsx
│   ├── lib/prisma.ts
│   ├── prisma/
│   │   ├── schema.prisma
│   │   ├── migrations/
│   │   ├── seed.ts
│   │   ├── catalog-data.sql
│   │   └── counts.sql
│   ├── scripts/phase5-matrix.mjs
│   ├── docs/
│   │   ├── DEPLOYMENT.md
│   │   └── screenshots/
│   ├── auth.ts
│   ├── auth.config.ts
│   ├── middleware.ts
│   ├── compose.yaml
│   └── package.json
├── .gitattributes
└── README.md
```

## Requisitos

- Node.js **22.x** y npm.
- PostgreSQL **16** local o una base Neon.
- Docker y Docker Compose (opcional, para levantar PostgreSQL local).
- Una cuenta Vercel solo si se va a desplegar.

## Variables de entorno

Crear `web/.env` a partir de `web/.env.example`:

| Variable | Obligatoria | Descripción |
| --- | --- | --- |
| `DATABASE_URL` | Sí | Cadena de conexión PostgreSQL. En local: `postgresql://USER:PASSWORD@127.0.0.1:5432/taller_automotriz?schema=public`. |
| `AUTH_SECRET` | Sí | Secreto aleatorio de Auth.js. Generar uno distinto por entorno. |
| `AUTH_URL` | Sí | Origen de la aplicación, por ejemplo `http://127.0.0.1:3000` o la URL HTTPS de producción. |
| `AUTH_TRUST_HOST` | En Vercel | `true` cuando la app corre detrás del proxy del proveedor. |

Generar un secreto:

```bash
openssl rand -base64 32
```

## Puesta en marcha local

```bash
git clone git@github.com:ProyectoEdson/Taller_Automotriz.git
cd Taller_Automotriz/web

cp .env.example .env
# Completar DATABASE_URL, AUTH_SECRET y AUTH_URL

# Opción A: PostgreSQL con Docker Compose
docker compose up -d

# Opción B: usar una base PostgreSQL propia y ajustar DATABASE_URL

npm ci
npx prisma migrate deploy
npx prisma db seed
npm run dev
```

Abrir http://localhost:3000/login.

## Base de datos

El esquema de Prisma define **12 tablas**:

| Tabla | Propósito |
| --- | --- |
| `clientes` | Datos de contacto del cliente. |
| `vehiculos` | Vehículos asociados a un cliente, con placa única. |
| `ordenes_servicio` | Orden de trabajo con código único. |
| `diagnostico_reparacion` | Diagnóstico, reparaciones y observaciones de una orden (1:1). |
| `inventario_repuesto` | Repuestos, precios y stock disponible. |
| `repuestos_utilizados` | Repuestos aplicados a un diagnóstico. |
| `cotizaciones` | Cabecera de cotización con subtotal, impuesto y total. |
| `detalle_cotizacion` | Líneas de servicio o repuesto de la cotización. |
| `pago_cuenta` | Pagos y abonos aplicados a una cotización. |
| `servicios` | Catálogo de servicios de taller con descripción y precio. |
| `roles` | Catálogo de roles: mecánico y recepcionista. |
| `usuarios` | Usuarios con contraseña hasheada y rol. |

Relaciones principales:

```text
clientes 1 ── N vehiculos 1 ── N ordenes_servicio 1 ── 1 diagnostico_reparacion
                                              │                    │
                                              │                    └── N repuestos_utilizados N ── 1 inventario_repuesto
                                              └── 1 cotizaciones 1 ── N detalle_cotizacion
                                                                  └── N pago_cuenta

servicios        (catálogo independiente)
roles 1 ── N usuarios
```

Comandos de base de datos:

```bash
npx prisma migrate deploy   # aplicar migraciones existentes
npx prisma migrate dev      # crear una migración durante desarrollo
npx prisma db seed          # cargar datos de demostración y catálogos
npx prisma db execute --file prisma/catalog-data.sql --schema prisma/schema.prisma
npx prisma studio           # explorar la base en el navegador
```

El seed restaura el escenario base: 2 órdenes, 0 cotizaciones pendientes, 0 reparaciones activas y 0 pagos pendientes, además de los usuarios de demostración.

El catálogo inicial incluye **10 repuestos** con stock, stock mínimo y precio, y **8 servicios de taller**. Se carga con el seed o, sobre una base existente, con `prisma/catalog-data.sql`; ese archivo es idempotente y actualiza por nombre.

## API

Todas las rutas responden JSON y validan la sesión y el rol.

| Método | Ruta | Rol | Descripción |
| --- | --- | --- | --- |
| `POST` | `/api/login` | Público | Inicia sesión con usuario, contraseña y perfil. Devuelve `403` si el rol no coincide. |
| `GET/POST` | `/api/auth/[...nextauth]` | Auth.js | Endpoints internos del proveedor de credenciales. |
| `POST` | `/api/ordenes` | Mecánico | Crea o actualiza una orden con diagnóstico, reparaciones y repuestos. |
| `POST` | `/api/cotizaciones` | Recepción | Genera la cotización de una orden con IVA del 7 %. |
| `POST` | `/api/pagos` | Recepción | Registra un abono y devuelve saldo y estado. |

Códigos de respuesta usados:

- `400` datos inválidos o importe fuera de rango.
- `401` sesión ausente.
- `403` rol incorrecto.
- `409` conflicto: orden inexistente, placa de otro cliente, cotización duplicada, pago sobre el saldo o cotización ya pagada.
- `500` error interno.

## Scripts

Ejecutar desde `web/`:

| Comando | Descripción |
| --- | --- |
| `npm run dev` | Servidor de desarrollo en `http://localhost:3000`. |
| `npm run build` | Build de producción. |
| `npm run start` | Servir el build de producción. |
| `npm run lint` | Análisis estático con ESLint. |
| `npm run typecheck` | Verificación de tipos con TypeScript. |
| `npm run db:migrate` | `prisma migrate dev` para desarrollo. |
| `npm run db:seed` | Carga los datos de demostración. |
| `node --env-file=.env scripts/phase5-matrix.mjs` | Matriz de escenarios sobre PostgreSQL local (reinicia la base). |

## Verificación

```bash
cd web
npm run lint
npm run typecheck
npm run build
```

La matriz de escenarios comprueba, sobre PostgreSQL local: alta y actualización de órdenes sin duplicados, validaciones `400`, permisos `401/403`, conflictos de placa y stock `409`, cálculo de cotización con IVA, cotización duplicada `409`, abonos parciales, liquidación, sobrepagos `409` y dos cobros concurrentes (uno aceptado y otro rechazado).

> `scripts/phase5-matrix.mjs` reinicia la base indicada en `DATABASE_URL`; solo debe ejecutarse contra la base local de desarrollo.

## Despliegue

La aplicación está desplegada en Vercel con **Root Directory `web/`** y PostgreSQL en Neon.

1. En Vercel, importar el repositorio y seleccionar `web/` como Root Directory.
2. Preset **Next.js**, Node.js **22.x**.
3. Configurar `DATABASE_URL` (URL *pooled* de Neon), `AUTH_SECRET`, `AUTH_URL` (origen HTTPS real) y `AUTH_TRUST_HOST=true`.
4. Aplicar migraciones desde un proceso administrativo con la URL **directa** de Neon:
   ```bash
   npx prisma migrate deploy
   ```
5. No ejecutar el seed de demostración en producción.

Los detalles y límites de los planes gratuitos están en [`web/docs/DEPLOYMENT.md`](web/docs/DEPLOYMENT.md).

## Capturas

| Inicio de sesión | Panel |
| --- | --- |
| ![Inicio de sesión](web/docs/screenshots/login-fase5.webp) | ![Panel](web/docs/screenshots/inicio-fase5.webp) |

| Diagnóstico | Cotización | Pagos |
| --- | --- | --- |
| ![Diagnóstico](web/docs/screenshots/diagnostico-fase5.png) | ![Cotización](web/docs/screenshots/cotizacion-fase5.png) | ![Pagos](web/docs/screenshots/pagos-fase5.png) |

## Seguridad

- Contraseñas almacenadas con bcrypt; nunca en texto plano.
- Sesión JWT firmada con `AUTH_SECRET`.
- Autorización en tres capas: middleware, página y endpoint.
- Consultas parametrizadas mediante Prisma.
- Importes con `Prisma.Decimal` y límites de rango.
- Transacciones y bloqueos de fila para inventario, cotizaciones y pagos concurrentes.

## Alcance actual

Implementado: autenticación, panel, diagnóstico, cotización y pagos.

Fuera del alcance de esta versión: CRUD completo de inventario, reportes exportables, asignación de mecánicos por orden, portal de clientes y notificaciones.
