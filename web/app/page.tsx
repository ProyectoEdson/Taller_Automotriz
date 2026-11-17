import { PrismaClient } from "@prisma/client";

export const dynamic = "force-dynamic";

export default async function Home() {
  const prisma = new PrismaClient();
  try {
    const orders = await prisma.ordenServicio.count();
    return <main><h1>Taller Automotriz</h1><p>Órdenes registradas: {orders}</p></main>;
  } finally {
    await prisma.$disconnect();
  }
}
