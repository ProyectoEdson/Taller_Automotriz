import NextAuth from "next-auth";
import { NextResponse } from "next/server";
import authConfig from "./auth.config";

const { auth } = NextAuth(authConfig);

export default auth((request) => {
  const rol = request.auth?.user?.rol;
  if (!rol) {
    if (request.nextUrl.pathname.startsWith("/api/")) return NextResponse.next();
    return NextResponse.redirect(new URL("/login", process.env.AUTH_URL ?? request.url));
  }

  const path = request.nextUrl.pathname;
  if ((path.startsWith("/diagnostico") || path.startsWith("/api/ordenes")) && rol !== "mecanico") {
    return new Response("Acceso denegado", { status: 403 });
  }
  if ((path.startsWith("/cotizacion") || path.startsWith("/pagos") || path.startsWith("/api/cotizaciones") || path.startsWith("/api/pagos")) && rol !== "recepcionista") {
    return new Response("Acceso denegado", { status: 403 });
  }
  return NextResponse.next();
});

export const config = {
  matcher: ["/inicio", "/diagnostico/:path*", "/cotizacion/:path*", "/pagos/:path*", "/api/ordenes/:path*", "/api/cotizaciones/:path*", "/api/pagos/:path*"],
};
