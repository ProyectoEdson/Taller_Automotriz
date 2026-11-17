import { PrismaClient } from "@prisma/client";
import { redirect } from "next/navigation";
import { auth, signOut } from "@/auth";

const prisma = new PrismaClient();

export const dynamic = "force-dynamic";

export default async function Inicio() {
  const session = await auth();
  if (!session?.user) redirect("/login");
  const ordenes = await prisma.ordenServicio.count();

  return (
    <main>
      <h1>TALLER AUTOMOTRIZ</h1>
      <p>{session.user.name} — {session.user.rol}</p>
      <p>Órdenes registradas: {ordenes}</p>
      <form action={async () => {
        "use server";
        await signOut({ redirectTo: "/login" });
      }}>
        <button type="submit">Cerrar sesión</button>
      </form>
    </main>
  );
}
