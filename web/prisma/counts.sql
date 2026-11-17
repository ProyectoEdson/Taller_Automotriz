-- Orden: órdenes / cotizaciones pendientes / reparaciones activas / pagos pendientes.
-- Misma instantánea para las cuatro cifras, equivalente a PHP/resumen.php.
SELECT
  (SELECT COUNT(*) FROM ordenes_servicio) AS ordenes,
  (SELECT COUNT(*) FROM ordenes_servicio o
   JOIN diagnostico_reparacion d ON d.id_orden = o.id_orden
   WHERE NOT EXISTS (SELECT 1 FROM cotizaciones c WHERE c.id_orden = o.id_orden)) AS cotizaciones_pendientes,
  (SELECT COUNT(*) FROM ordenes_servicio o
   JOIN diagnostico_reparacion d ON d.id_orden = o.id_orden
   WHERE NOT EXISTS (
     SELECT 1 FROM cotizaciones c WHERE c.id_orden = o.id_orden
       AND c.total <= COALESCE((SELECT SUM(p.monto_recibido)
         FROM pago_cuenta p WHERE p.id_cotizacion = c.id_cotizacion), 0)
   )) AS reparaciones_activas,
  (SELECT COUNT(*) FROM cotizaciones c WHERE c.total > COALESCE(
    (SELECT SUM(p.monto_recibido) FROM pago_cuenta p
     WHERE p.id_cotizacion = c.id_cotizacion), 0)) AS pagos_pendientes;
