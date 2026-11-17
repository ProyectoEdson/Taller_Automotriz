import { CredentialsSignin } from "next-auth";
import { NextResponse } from "next/server";
import { loginSchema, signIn } from "@/auth";

export async function POST(request: Request) {
  let body: unknown;
  try {
    body = await request.json();
  } catch {
    return NextResponse.json({ mensaje: "Datos de inicio de sesión inválidos." }, { status: 400 });
  }
  const parsed = loginSchema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ mensaje: "Usuario, contraseña y perfil son obligatorios." }, { status: 400 });
  }

  try {
    const destination = await signIn("credentials", {
      ...parsed.data,
      redirect: false,
      redirectTo: "/inicio",
    });
    const url = new URL(destination, request.url);
    const expectedOrigin = new URL(process.env.AUTH_URL ?? request.url).origin;
    if (url.origin !== expectedOrigin || url.pathname !== "/inicio") {
      throw new Error("Destino de autenticación inesperado");
    }
    return NextResponse.json({ destino: "/inicio" });
  } catch (error) {
    if (error instanceof CredentialsSignin) {
      return error.code === "rol_incorrecto"
        ? NextResponse.json({ mensaje: "El usuario no pertenece al perfil seleccionado." }, { status: 403 })
        : NextResponse.json({ mensaje: "Usuario o contraseña incorrectos." }, { status: 401 });
    }
    console.error("Error de inicio de sesión:", error);
    return NextResponse.json({ mensaje: "No se pudo iniciar sesión." }, { status: 500 });
  }
}
