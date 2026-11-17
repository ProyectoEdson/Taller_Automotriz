-- CreateTable
CREATE TABLE "servicios" (
    "id_servicio" SERIAL NOT NULL,
    "nombre" VARCHAR(100) NOT NULL,
    "descripcion" TEXT,
    "precio" DECIMAL(10,2) NOT NULL,

    CONSTRAINT "servicios_pkey" PRIMARY KEY ("id_servicio")
);

-- CreateIndex
CREATE UNIQUE INDEX "servicios_nombre_key" ON "servicios"("nombre");
