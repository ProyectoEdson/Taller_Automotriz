# Taller Automotriz

Sistema web de gestión para un taller automotriz: recepción del vehículo, diagnóstico y reparación, cotización y registro de pagos. Desarrollado en PHP puro con MySQL/MariaDB, sin frameworks ni dependencias externas.

## Tecnologías

- PHP 8.3 (PDO, sesiones)
- MySQL / MariaDB 10.11
- HTML5, CSS3 y JavaScript (vanilla)
- Dump de base de datos incluido en `BASE DE DATO/taller_automotriz.sql`

## Roles

| Rol | Acceso |
|---|---|
| Mecánico | Diagnóstico y reparación (`INDEX.php`), consulta y actualización de órdenes de trabajo |
| Recepcionista | Cotizaciones (`VISTAS/COTIZACIÓN.PHP`) y pagos (`VISTAS/PAGOS.PHP`) |

La autenticación se valida contra la tabla `usuarios` (contraseñas con `password_hash`) y el rol elegido en el login debe coincidir con el rol real del usuario. Cada vista y cada endpoint verifica la sesión y el rol.

## Módulos

1. **Login** – `VISTAS/LOGIN.HTML` envía las credenciales a `PHP/validar_login.php` y redirige a `VISTAS/INICIO.php`.
2. **Inicio** – panel con accesos filtrados según el rol.
3. **Diagnóstico y reparación** – el mecánico selecciona una orden existente y ve los datos del cliente y del vehículo en solo lectura, o elige "Nueva orden" para registrar cliente, vehículo y orden. En ambos casos guarda el diagnóstico, las reparaciones y los repuestos utilizados en una única transacción. Genera el código de orden `AT-<aammdd>-<4 dígitos>` cuando es una orden nueva.
4. **Cotizaciones** – busca la orden por código, precarga diagnóstico, reparaciones y repuestos, permite editar líneas y calcula subtotal, impuesto (7 %) y total.
5. **Pagos** – selecciona una cotización, muestra el total y registra el pago o abono con método Efectivo, Tarjeta, Transferencia o Yappy. El backend impide sobrepagos y cotizaciones ya saldadas.

## Estructura

```
├── BASE DE DATO/
│   └── taller_automotriz.sql   # esquema + datos de ejemplo
├── CSS/
│   └── AUTOMOTRIZ.CSS          # estilos de toda la aplicación
├── IMG/                        # logo e iconos
├── PHP/                        # endpoints y lógica de servidor
│   ├── conexion.php            # conexión PDO
│   ├── proteger_sesion.php     # guard de sesión
│   ├── validar_login.php
│   ├── cerrar_sesion.php
│   ├── guardar_diagnostico.php
│   ├── listar_ordenes.php
│   ├── consultar_diagnostico.php
│   ├── consultar_orden.php
│   ├── guardar_cotizacion.php
│   ├── consultar_cotizaciones.php
│   ├── consultar_pagos.php
│   └── registrar_pago.php
├── VISTAS/
│   ├── LOGIN.HTML
│   ├── INICIO.PHP
│   ├── COTIZACIÓN.PHP
│   └── PAGOS.PHP
├── INDEX.PHP                   # módulo de diagnóstico (mecánico)
└── INDEX.HTML                  # prototipo estático (no forma parte de la app)
```

Los archivos `VISTAS/INICIO.HTML`, `VISTAS/COTIZACIÓN.HTML`, `VISTAS/PAGOS.HTML` e `INDEX.HTML` son prototipos estáticos basados en `localStorage`; la aplicación funcional son las versiones `.PHP`.

## Base de datos

Tablas: `clientes`, `vehiculos`, `ordenes_servicio`, `diagnostico_reparacion`, `repuestos_utilizados`, `inventario_repuesto`, `cotizaciones`, `detalle_cotizacion`, `pago_cuenta`, `roles`, `usuarios`.

Relaciones principales:

- `clientes` 1 → N `vehiculos`
- `vehiculos` 1 → N `ordenes_servicio`
- `ordenes_servicio` 1 → 1 `diagnostico_reparacion`
- `diagnostico_reparacion` 1 → N `repuestos_utilizados`
- `ordenes_servicio` 1 → N `cotizaciones` → N `detalle_cotizacion`
- `cotizaciones` 1 → N `pago_cuenta` (permite abonos)
- `roles` 1 → N `usuarios`

## Instalación

1. Copia el proyecto dentro del directorio público del servidor web (por ejemplo `htdocs/` en XAMPP o `/var/www/html/` en Apache).
2. Crea la base de datos e importa el dump:

   ```bash
   mysql -u root -p -e "CREATE DATABASE taller_automotriz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p taller_automotriz < "BASE DE DATO/taller_automotriz.sql"
   ```

   El dump no incluye `CREATE DATABASE`, por eso se crea primero.

3. Ajusta las credenciales en `PHP/conexion.php`:

   ```php
   $servidor = "localhost";
   $nombreBD = "taller_automotriz";
   $usuario  = "root";
   $contrasena = "";
   ```

4. Inicia Apache y MySQL, y abre la aplicación:

   ```
   http://localhost/<carpeta-del-proyecto>/VISTAS/LOGIN.HTML
   ```

5. Inicia sesión con un usuario de la tabla `usuarios` y el perfil que le corresponda. El dump incluye usuarios de ejemplo con contraseñas almacenadas como hash bcrypt; crea o actualiza usuarios con:

   ```php
   echo password_hash("tu-contraseña", PASSWORD_DEFAULT);
   ```

## Endpoints

| Método | Endpoint | Rol | Función |
|---|---|---|---|
| POST | `PHP/validar_login.php` | público | Inicia sesión |
| GET | `PHP/cerrar_sesion.php` | sesión | Cierra sesión |
| POST | `PHP/guardar_diagnostico.php` | mecánico | Alta de cliente/vehículo/orden o actualización del diagnóstico de una orden |
| GET | `PHP/listar_ordenes.php` | mecánico | Lista las órdenes de trabajo con cliente y vehículo |
| POST | `PHP/consultar_diagnostico.php` | mecánico | Devuelve la orden, el diagnóstico y los repuestos registrados |
| POST | `PHP/consultar_orden.php` | recepcionista | Consulta la orden y arma los detalles de cotización |
| POST | `PHP/guardar_cotizacion.php` | recepcionista | Registra cotización y detalle |
| GET | `PHP/consultar_cotizaciones.php` | sesión | Lista cotizaciones con detalle |
| GET | `PHP/consultar_pagos.php` | sesión | Estado de cuenta por cotización |
| POST | `PHP/registrar_pago.php` | sesión | Registra pago o abono |

## Notas

- `PHP/conexion.php` contiene credenciales de desarrollo (`root` sin contraseña). Cámbialas antes de desplegar.
- `PHP/probar_conexion.php` es una prueba manual de conexión.
- El impuesto aplicado a las cotizaciones es del 7 % y está fijado en el código.
- Las carpetas `AUDIO/`, `VIDEO/` y `JS/` están vacías y no se versionan.
