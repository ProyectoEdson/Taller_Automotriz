# Taller Automotriz

Sistema web de gestión para un taller automotriz: recepción del vehículo, diagnóstico y reparación, cotización y registro de pagos. Desarrollado en PHP puro con MySQL/MariaDB, sin frameworks ni dependencias externas.

> Rama `linux`: variante portable con archivos `.php` y nombres de tabla en minúsculas. En Windows funciona igual, porque su sistema de archivos y MySQL no distinguen mayúsculas.

## Tecnologías

- PHP 8.3 (PDO, sesiones)
- MySQL / MariaDB 10.11
- HTML5, CSS3 y JavaScript (vanilla)
- Dump de base de datos incluido en `BASE DE DATO/taller_automotriz.sql`

## Roles

| Rol | Acceso |
|---|---|
| Mecánico | Diagnóstico y reparación (`INDEX.php`), consulta y actualización de órdenes de trabajo |
| Recepcionista | Cotizaciones (`VISTAS/COTIZACIÓN.php`) y pagos (`VISTAS/PAGOS.php`) |

La autenticación se valida contra la tabla `usuarios` (contraseñas con `password_hash`) y el rol elegido en el login debe coincidir con el rol real del usuario. Cada vista y cada endpoint verifica la sesión y el rol.

## Módulos

1. **Login** – `VISTAS/LOGIN.HTML` envía las credenciales a `PHP/validar_login.php` y redirige a `VISTAS/INICIO.php`.
2. **Inicio** – panel con accesos filtrados según el rol.
3. **Diagnóstico y reparación** – el mecánico selecciona una orden existente y ve los datos del cliente y del vehículo en solo lectura, o elige "Nueva orden" para registrar cliente, vehículo y orden. En ambos casos guarda el diagnóstico, las reparaciones y los repuestos utilizados en una única transacción. Genera el código de orden `AT-<aammdd>-<4 dígitos>` cuando es una orden nueva.
4. **Cotizaciones** – busca la orden por código, precarga diagnóstico, reparaciones y repuestos, permite editar líneas y calcula subtotal, impuesto (7 %) y total.
5. **Pagos** – selecciona una cotización, muestra el saldo pendiente y el estado de cuenta real, y registra pagos o abonos con método Efectivo, Tarjeta, Transferencia o Yappy. El backend impide sobrepagos y cotizaciones ya saldadas.

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
│   ├── INICIO.php
│   ├── COTIZACIÓN.php
│   └── PAGOS.php
├── dev/
│   └── linux-dev.sh            # arranque local sin sudo
├── INDEX.php                   # módulo de diagnóstico (mecánico)
└── INDEX.HTML                  # prototipo estático (no forma parte de la app)
```

Los archivos `VISTAS/INICIO.HTML`, `VISTAS/COTIZACIÓN.HTML`, `VISTAS/PAGOS.HTML` e `INDEX.HTML` son prototipos estáticos basados en `localStorage`; la aplicación funcional son las versiones `.php`.

## Base de datos

Tablas: `clientes`, `vehiculos`, `ordenes_servicio`, `diagnostico_reparacion`, `repuestos_utilizados`, `inventario_repuesto`, `cotizaciones`, `detalle_cotizacion`, `pago_cuenta`, `roles`, `usuarios`.

Relaciones principales:

- `clientes` 1 → N `vehiculos`
- `vehiculos` 1 → N `ordenes_servicio`
- `ordenes_servicio` 1 → 1 `diagnostico_reparacion`
- `diagnostico_reparacion` 1 → N `repuestos_utilizados`
- `ordenes_servicio` 1 → N `cotizaciones` → N `detalle_cotizacion` (una sola cotización por orden, `id_orden` UNIQUE)
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

## Ejecutar en Linux

### Arranque rápido (desarrollo, sin sudo)

Requiere `php`, `php-mysqlnd`, `php-mbstring` y `mariadb-server` instalados:

```bash
sudo dnf install php php-mysqlnd php-mbstring mariadb-server   # Fedora
# sudo apt install php php-mysql php-mbstring mariadb-server   # Debian/Ubuntu

./dev/linux-dev.sh
```

El script inicializa una base local en `.dev/db`, importa el dump y arranca PHP en `http://127.0.0.1:8080/VISTAS/LOGIN.HTML`. `Ctrl+C` detiene la aplicación y la base. Para reiniciar los datos: `TALLER_DEV_RESET=1 ./dev/linux-dev.sh`.

Usuarios de prueba:

| Usuario | Contraseña | Rol |
|---|---|---|
| `Asadmin` | `123456` | Recepcionista |
| `AsaCorredor` | `789456` | Mecánico |

### Apache y MariaDB del sistema

1. Instala y arranca los servicios:

   ```bash
   sudo dnf install httpd php php-mysqlnd php-mbstring mariadb-server
   sudo systemctl enable --now httpd mariadb
   ```

2. Crea el usuario de base de datos:

   ```sql
   CREATE DATABASE taller_automotriz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'taller'@'localhost' IDENTIFIED BY 'cambia-esta-clave';
   GRANT ALL PRIVILEGES ON taller_automotriz.* TO 'taller'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. Importa el dump:

   ```bash
   sudo mariadb taller_automotriz < "BASE DE DATO/taller_automotriz.sql"
   ```

4. Copia el proyecto y ajusta permisos:

   ```bash
   sudo cp -r . /var/www/html/taller
   sudo chown -R apache:apache /var/www/html/taller
   ```

5. Configura la conexión en `PHP/conexion.php` o define `TALLER_DB_USER` y `TALLER_DB_PASS` (también `TALLER_DB_HOST` y `TALLER_DB_NAME`).

6. Abre `http://localhost/taller/VISTAS/LOGIN.HTML`.

> En Fedora, si SELinux bloquea la lectura: `sudo chcon -R -t httpd_sys_content_t /var/www/html/taller`.
> `BASE DE DATO/.htaccess` bloquea la descarga del dump cuando Apache permite `.htaccess`; en producción muévelo fuera del directorio público.

## Endpoints

| Método | Endpoint | Rol | Función |
|---|---|---|---|
| POST | `PHP/validar_login.php` | público | Inicia sesión |
| GET | `PHP/cerrar_sesion.php` | sesión | Cierra sesión |
| POST | `PHP/guardar_diagnostico.php` | mecánico | Alta de cliente/vehículo/orden o actualización del diagnóstico de una orden |
| GET | `PHP/listar_ordenes.php` | mecánico | Lista las órdenes de trabajo con cliente y vehículo |
| POST | `PHP/consultar_diagnostico.php` | mecánico | Devuelve la orden, el diagnóstico y los repuestos registrados |
| GET | `PHP/resumen.php` | sesión | Contadores del panel de inicio |
| POST | `PHP/consultar_orden.php` | recepcionista | Consulta la orden y arma los detalles de cotización |
| POST | `PHP/guardar_cotizacion.php` | recepcionista | Registra la cotización (una por orden) y su detalle |
| GET | `PHP/consultar_cotizaciones.php` | recepcionista | Lista cotizaciones con detalle |
| GET | `PHP/consultar_pagos.php` | recepcionista | Estado de cuenta por cotización |
| POST | `PHP/registrar_pago.php` | recepcionista | Registra pago o abono |

## Correcciones de implementación (fase 3)

Estas correcciones no cambian el alcance de los casos de uso de la Fase 1 ni las decisiones de arquitectura de la Fase 2; ajustan la implementación a lo ya especificado.

| Corrección | Requisito / CU | Dónde |
|---|---|---|
| Permisos de recepcionista en consultas y pagos | RNF-01 | `PHP/consultar_cotizaciones.php`, `PHP/consultar_pagos.php`, `PHP/registrar_pago.php` |
| `id_pago` real en la respuesta | RF-10 / CU-05 | `PHP/registrar_pago.php` |
| Estado de cuenta y saldo reales; abonos desde la UI | RF-10 / CU-05 | `VISTAS/PAGOS.php`, `PHP/consultar_pagos.php` |
| Una cotización por orden | RF-09 / CU-04 | `PHP/guardar_cotizacion.php`, `BASE DE DATO/taller_automotriz.sql` |
| Contadores del panel desde la BD | Panel de control | `PHP/resumen.php`, `VISTAS/INICIO.php` |
| Validación de longitudes y errores claros | RNF-01 / RNF-02 | `PHP/guardar_diagnostico.php`, `PHP/guardar_cotizacion.php`, `INDEX.php` |

Definiciones de los contadores del panel:

- **Órdenes registradas:** total de órdenes de servicio.
- **Cotizaciones pendientes:** órdenes diagnosticadas sin cotización.
- **Reparaciones activas:** órdenes diagnosticadas sin cotización totalmente pagada (trabajo abierto).
- **Pagos pendientes:** cotizaciones emitidas con saldo mayor que cero.

Límites de alcance, sin cambios respecto a la Fase 1:

- RF-04/CU-06 (asignación de mecánico), RF-07 (portal del cliente), RF-08 (máquina de estados) y RF-11 (reportes) siguen fuera de la implementación.
- El contador "reparaciones activas" usa el estado financiero como proxy; no implementa la máquina de estados de RF-08.
- El login usa `usuario`, no email, para conservar los hashes existentes; es una diferencia documentada con RNF-01.

## Notas

- `PHP/conexion.php` usa las variables `TALLER_DB_HOST`, `TALLER_DB_NAME`, `TALLER_DB_USER` y `TALLER_DB_PASS`, con `root` sin contraseña por defecto. Cámbialas antes de desplegar.
- `PHP/probar_conexion.php` es una prueba manual de conexión.
- El impuesto aplicado a las cotizaciones es del 7 % y está fijado en el código.
- Las carpetas `AUDIO/`, `VIDEO/` y `JS/` están vacías y no se versionan.
- Los nombres de archivo, tablas y enlaces están en minúsculas para que funcionen en Linux; Windows los resuelve igual.
- El dump incluye `UNIQUE(id_orden)` en `cotizaciones`. Si importas una base anterior con duplicados, elimínalos antes de aplicar el índice.
