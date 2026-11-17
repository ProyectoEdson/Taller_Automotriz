-- CreateTable
CREATE TABLE "clientes" (
    "id_cliente" SERIAL NOT NULL,
    "nombre" VARCHAR(100) NOT NULL,
    "primerapellido" VARCHAR(100) NOT NULL,
    "segundoapellido" VARCHAR(100),
    "telefono" VARCHAR(20),
    "email" VARCHAR(100),

    CONSTRAINT "clientes_pkey" PRIMARY KEY ("id_cliente")
);

-- CreateTable
CREATE TABLE "vehiculos" (
    "id_vehiculo" SERIAL NOT NULL,
    "id_cliente" INTEGER NOT NULL,
    "placa" VARCHAR(15) NOT NULL,
    "modelo" VARCHAR(50) NOT NULL,
    "marca" VARCHAR(50) NOT NULL,
    "anio" INTEGER,
    "kilometraje" DOUBLE PRECISION,

    CONSTRAINT "vehiculos_pkey" PRIMARY KEY ("id_vehiculo")
);

-- CreateTable
CREATE TABLE "ordenes_servicio" (
    "id_orden" SERIAL NOT NULL,
    "codigo_orden" VARCHAR(30) NOT NULL,
    "id_vehiculo" INTEGER NOT NULL,
    "fecha_ingreso" TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "estado_orden" VARCHAR(30) NOT NULL DEFAULT 'Abierta',

    CONSTRAINT "ordenes_servicio_pkey" PRIMARY KEY ("id_orden")
);

-- CreateTable
CREATE TABLE "diagnostico_reparacion" (
    "id_diagnostico" SERIAL NOT NULL,
    "id_orden" INTEGER NOT NULL,
    "descripcion_fall" TEXT,
    "tarea_realiz" TEXT,
    "prioridad" VARCHAR(30),

    CONSTRAINT "diagnostico_reparacion_pkey" PRIMARY KEY ("id_diagnostico")
);

-- CreateTable
CREATE TABLE "repuestos_utilizados" (
    "id_repuesto_util" SERIAL NOT NULL,
    "id_diagnostico" INTEGER NOT NULL,
    "id_repuesto" INTEGER,
    "cantidad" INTEGER NOT NULL,
    "precio_unitario" DECIMAL(10,2) NOT NULL,
    "total_item" DECIMAL(10,2) NOT NULL,
    "descripcion" VARCHAR(150),

    CONSTRAINT "repuestos_utilizados_pkey" PRIMARY KEY ("id_repuesto_util")
);

-- CreateTable
CREATE TABLE "inventario_repuesto" (
    "id_repuesto" SERIAL NOT NULL,
    "nombre" VARCHAR(100) NOT NULL,
    "descripcion" TEXT,
    "stock_actual" INTEGER NOT NULL DEFAULT 0,
    "stock_minimo" INTEGER NOT NULL DEFAULT 0,
    "precio_base" DECIMAL(10,2) NOT NULL,

    CONSTRAINT "inventario_repuesto_pkey" PRIMARY KEY ("id_repuesto")
);

-- CreateTable
CREATE TABLE "cotizaciones" (
    "id_cotizacion" SERIAL NOT NULL,
    "id_orden" INTEGER NOT NULL,
    "subtotal" DECIMAL(10,2) NOT NULL,
    "impuesto" DECIMAL(10,2) NOT NULL,
    "total" DECIMAL(10,2) NOT NULL,
    "fecha_emision" TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "cotizaciones_pkey" PRIMARY KEY ("id_cotizacion")
);

-- CreateTable
CREATE TABLE "detalle_cotizacion" (
    "id_detalle" SERIAL NOT NULL,
    "id_cotizacion" INTEGER NOT NULL,
    "descripcion" VARCHAR(150) NOT NULL,
    "tipo" VARCHAR(20) NOT NULL,
    "cantidad" INTEGER NOT NULL,
    "precio_unitario" DECIMAL(10,2) NOT NULL,
    "total_item" DECIMAL(10,2) NOT NULL,

    CONSTRAINT "detalle_cotizacion_pkey" PRIMARY KEY ("id_detalle")
);

-- CreateTable
CREATE TABLE "pago_cuenta" (
    "id_pago" SERIAL NOT NULL,
    "id_orden" INTEGER NOT NULL,
    "id_cotizacion" INTEGER NOT NULL,
    "monto_pagar" DECIMAL(10,2) NOT NULL,
    "monto_recibido" DECIMAL(10,2) NOT NULL,
    "metodo_pago" VARCHAR(30) NOT NULL,
    "fecha_pago" TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "pago_cuenta_pkey" PRIMARY KEY ("id_pago")
);

-- CreateTable
CREATE TABLE "roles" (
    "id_rol" SERIAL NOT NULL,
    "nombre_rol" VARCHAR(30) NOT NULL,

    CONSTRAINT "roles_pkey" PRIMARY KEY ("id_rol")
);

-- CreateTable
CREATE TABLE "usuarios" (
    "id_usuario" SERIAL NOT NULL,
    "usuario" VARCHAR(50) NOT NULL,
    "contrasena" VARCHAR(255) NOT NULL,
    "id_rol" INTEGER NOT NULL,

    CONSTRAINT "usuarios_pkey" PRIMARY KEY ("id_usuario")
);

-- CreateIndex
CREATE UNIQUE INDEX "vehiculos_placa_key" ON "vehiculos"("placa");

-- CreateIndex
CREATE INDEX "vehiculos_id_cliente_idx" ON "vehiculos"("id_cliente");

-- CreateIndex
CREATE UNIQUE INDEX "ordenes_servicio_codigo_orden_key" ON "ordenes_servicio"("codigo_orden");

-- CreateIndex
CREATE INDEX "ordenes_servicio_id_vehiculo_idx" ON "ordenes_servicio"("id_vehiculo");

-- CreateIndex
CREATE UNIQUE INDEX "diagnostico_reparacion_id_orden_key" ON "diagnostico_reparacion"("id_orden");

-- CreateIndex
CREATE INDEX "repuestos_utilizados_id_diagnostico_idx" ON "repuestos_utilizados"("id_diagnostico");

-- CreateIndex
CREATE INDEX "repuestos_utilizados_id_repuesto_idx" ON "repuestos_utilizados"("id_repuesto");

-- CreateIndex
CREATE UNIQUE INDEX "cotizaciones_id_orden_key" ON "cotizaciones"("id_orden");

-- CreateIndex
CREATE INDEX "detalle_cotizacion_id_cotizacion_idx" ON "detalle_cotizacion"("id_cotizacion");

-- CreateIndex
CREATE INDEX "pago_cuenta_id_orden_idx" ON "pago_cuenta"("id_orden");

-- CreateIndex
CREATE INDEX "pago_cuenta_id_cotizacion_idx" ON "pago_cuenta"("id_cotizacion");

-- CreateIndex
CREATE UNIQUE INDEX "usuarios_usuario_key" ON "usuarios"("usuario");

-- CreateIndex
CREATE INDEX "usuarios_id_rol_idx" ON "usuarios"("id_rol");

-- AddForeignKey
ALTER TABLE "vehiculos" ADD CONSTRAINT "vehiculos_id_cliente_fkey" FOREIGN KEY ("id_cliente") REFERENCES "clientes"("id_cliente") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "ordenes_servicio" ADD CONSTRAINT "ordenes_servicio_id_vehiculo_fkey" FOREIGN KEY ("id_vehiculo") REFERENCES "vehiculos"("id_vehiculo") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "diagnostico_reparacion" ADD CONSTRAINT "diagnostico_reparacion_id_orden_fkey" FOREIGN KEY ("id_orden") REFERENCES "ordenes_servicio"("id_orden") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "repuestos_utilizados" ADD CONSTRAINT "repuestos_utilizados_id_diagnostico_fkey" FOREIGN KEY ("id_diagnostico") REFERENCES "diagnostico_reparacion"("id_diagnostico") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "repuestos_utilizados" ADD CONSTRAINT "repuestos_utilizados_id_repuesto_fkey" FOREIGN KEY ("id_repuesto") REFERENCES "inventario_repuesto"("id_repuesto") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "cotizaciones" ADD CONSTRAINT "cotizaciones_id_orden_fkey" FOREIGN KEY ("id_orden") REFERENCES "ordenes_servicio"("id_orden") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "detalle_cotizacion" ADD CONSTRAINT "detalle_cotizacion_id_cotizacion_fkey" FOREIGN KEY ("id_cotizacion") REFERENCES "cotizaciones"("id_cotizacion") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "pago_cuenta" ADD CONSTRAINT "pago_cuenta_id_orden_fkey" FOREIGN KEY ("id_orden") REFERENCES "ordenes_servicio"("id_orden") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "pago_cuenta" ADD CONSTRAINT "pago_cuenta_id_cotizacion_fkey" FOREIGN KEY ("id_cotizacion") REFERENCES "cotizaciones"("id_cotizacion") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "usuarios" ADD CONSTRAINT "usuarios_id_rol_fkey" FOREIGN KEY ("id_rol") REFERENCES "roles"("id_rol") ON DELETE RESTRICT ON UPDATE CASCADE;
