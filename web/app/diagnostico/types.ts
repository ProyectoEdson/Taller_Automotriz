export type CatalogoRepuesto = {
  id: number;
  nombre: string;
  descripcion: string;
  stock: number;
  stockMinimo: number;
  precio: string;
};

export type Repuesto = {
  idRepuesto?: number;
  nombre: string;
  cantidad: number;
  precio: string;
};
