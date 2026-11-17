-- Catálogo inicial de repuestos y servicios del taller.
-- Requiere la migración que crea la tabla "servicios".
-- Es idempotente: actualiza por nombre si la fila existe y la inserta si no.

BEGIN;

WITH actualizado AS (
  UPDATE inventario_repuesto
  SET descripcion = 'Filtro para lubricación del motor', stock_actual = 20, stock_minimo = 5, precio_base = 8.50
  WHERE lower(nombre) = lower('Filtro de aceite')
  RETURNING 1
)
INSERT INTO inventario_repuesto (nombre, descripcion, stock_actual, stock_minimo, precio_base)
SELECT 'Filtro de aceite', 'Filtro para lubricación del motor', 20, 5, 8.50
WHERE NOT EXISTS (SELECT 1 FROM actualizado);

WITH actualizado AS (
  UPDATE inventario_repuesto
  SET descripcion = 'Filtro de admisión de aire', stock_actual = 15, stock_minimo = 5, precio_base = 10.00
  WHERE lower(nombre) = lower('Filtro de aire')
  RETURNING 1
)
INSERT INTO inventario_repuesto (nombre, descripcion, stock_actual, stock_minimo, precio_base)
SELECT 'Filtro de aire', 'Filtro de admisión de aire', 15, 5, 10.00
WHERE NOT EXISTS (SELECT 1 FROM actualizado);

WITH actualizado AS (
  UPDATE inventario_repuesto
  SET descripcion = 'Aceite para motor, envase de 1 litro', stock_actual = 30, stock_minimo = 10, precio_base = 12.00
  WHERE lower(nombre) = lower('Aceite 10W-30')
  RETURNING 1
)
INSERT INTO inventario_repuesto (nombre, descripcion, stock_actual, stock_minimo, precio_base)
SELECT 'Aceite 10W-30', 'Aceite para motor, envase de 1 litro', 30, 10, 12.00
WHERE NOT EXISTS (SELECT 1 FROM actualizado);

WITH actualizado AS (
  UPDATE inventario_repuesto
  SET descripcion = 'Juego de pastillas delanteras', stock_actual = 12, stock_minimo = 4, precio_base = 28.00
  WHERE lower(nombre) = lower('Pastillas de freno')
  RETURNING 1
)
INSERT INTO inventario_repuesto (nombre, descripcion, stock_actual, stock_minimo, precio_base)
SELECT 'Pastillas de freno', 'Juego de pastillas delanteras', 12, 4, 28.00
WHERE NOT EXISTS (SELECT 1 FROM actualizado);

WITH actualizado AS (
  UPDATE inventario_repuesto
  SET descripcion = 'Bujías de encendido', stock_actual = 25, stock_minimo = 6, precio_base = 6.50
  WHERE lower(nombre) = lower('Bujías')
  RETURNING 1
)
INSERT INTO inventario_repuesto (nombre, descripcion, stock_actual, stock_minimo, precio_base)
SELECT 'Bujías', 'Bujías de encendido', 25, 6, 6.50
WHERE NOT EXISTS (SELECT 1 FROM actualizado);

WITH actualizado AS (
  UPDATE inventario_repuesto
  SET descripcion = 'Batería para automóvil', stock_actual = 8, stock_minimo = 2, precio_base = 75.00
  WHERE lower(nombre) = lower('Batería 12V')
  RETURNING 1
)
INSERT INTO inventario_repuesto (nombre, descripcion, stock_actual, stock_minimo, precio_base)
SELECT 'Batería 12V', 'Batería para automóvil', 8, 2, 75.00
WHERE NOT EXISTS (SELECT 1 FROM actualizado);

WITH actualizado AS (
  UPDATE inventario_repuesto
  SET descripcion = 'Líquido refrigerante para motor', stock_actual = 20, stock_minimo = 5, precio_base = 9.50
  WHERE lower(nombre) = lower('Refrigerante')
  RETURNING 1
)
INSERT INTO inventario_repuesto (nombre, descripcion, stock_actual, stock_minimo, precio_base)
SELECT 'Refrigerante', 'Líquido refrigerante para motor', 20, 5, 9.50
WHERE NOT EXISTS (SELECT 1 FROM actualizado);

WITH actualizado AS (
  UPDATE inventario_repuesto
  SET descripcion = 'Filtro para sistema de combustible', stock_actual = 10, stock_minimo = 3, precio_base = 15.00
  WHERE lower(nombre) = lower('Filtro de combustible')
  RETURNING 1
)
INSERT INTO inventario_repuesto (nombre, descripcion, stock_actual, stock_minimo, precio_base)
SELECT 'Filtro de combustible', 'Filtro para sistema de combustible', 10, 3, 15.00
WHERE NOT EXISTS (SELECT 1 FROM actualizado);

WITH actualizado AS (
  UPDATE inventario_repuesto
  SET descripcion = 'Correa para sincronización del motor', stock_actual = 6, stock_minimo = 2, precio_base = 45.00
  WHERE lower(nombre) = lower('Correa de distribución')
  RETURNING 1
)
INSERT INTO inventario_repuesto (nombre, descripcion, stock_actual, stock_minimo, precio_base)
SELECT 'Correa de distribución', 'Correa para sincronización del motor', 6, 2, 45.00
WHERE NOT EXISTS (SELECT 1 FROM actualizado);

WITH actualizado AS (
  UPDATE inventario_repuesto
  SET descripcion = 'Juego de escobillas', stock_actual = 15, stock_minimo = 4, precio_base = 14.00
  WHERE lower(nombre) = lower('Limpiaparabrisas')
  RETURNING 1
)
INSERT INTO inventario_repuesto (nombre, descripcion, stock_actual, stock_minimo, precio_base)
SELECT 'Limpiaparabrisas', 'Juego de escobillas', 15, 4, 14.00
WHERE NOT EXISTS (SELECT 1 FROM actualizado);

INSERT INTO servicios (nombre, descripcion, precio) VALUES
  ('Cambio de aceite', 'Sustitución del aceite del motor', 15.00),
  ('Alineación', 'Ajuste de dirección y ruedas', 25.00),
  ('Balanceo', 'Equilibrado de neumáticos', 20.00),
  ('Diagnóstico computarizado', 'Revisión mediante escáner', 30.00),
  ('Cambio de pastillas', 'Sustitución de pastillas de freno', 25.00),
  ('Revisión de frenos', 'Inspección del sistema de frenado', 15.00),
  ('Cambio de batería', 'Instalación de batería nueva', 10.00),
  ('Mantenimiento preventivo', 'Revisión general del vehículo', 45.00)
ON CONFLICT (nombre) DO UPDATE
SET descripcion = EXCLUDED.descripcion,
    precio = EXCLUDED.precio;

COMMIT;
