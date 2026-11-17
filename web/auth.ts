import { compare } from "bcryptjs";
import NextAuth, { CredentialsSignin } from "next-auth";
import Credentials from "next-auth/providers/credentials";
import { PrismaClient } from "@prisma/client";
import { z } from "zod";
import authConfig from "./auth.config";

const prisma = new PrismaClient();

export const loginSchema = z.object({
  usuario: z.string().trim().min(1).max(50),
  password: z.string().min(1),
  rol: z.enum(["mecanico", "recepcionista"]),
});

class IncorrectRole extends CredentialsSignin {
  code = "rol_incorrecto";
}

export const { handlers, auth, signIn, signOut } = NextAuth({
  ...authConfig,
  providers: [
    Credentials({
      credentials: {
        usuario: { label: "Usuario", type: "text" },
        password: { label: "Contraseña", type: "password" },
        rol: { label: "Perfil", type: "text" },
      },
      async authorize(credentials) {
        const parsed = loginSchema.safeParse(credentials);
        if (!parsed.success) return null;

        const usuario = await prisma.usuario.findUnique({
          where: { usuario: parsed.data.usuario },
          select: { idUsuario: true, usuario: true, contrasena: true, idRol: true },
        });
        if (!usuario) return null;

        // PHP password_hash stores $2y$; bcryptjs understands its equivalent $2a$.
        const hash = usuario.contrasena.replace(/^\$2y\$/, "$2a$");
        if (!(await compare(parsed.data.password, hash))) return null;

        const rol = usuario.idRol === 1 ? "mecanico" : usuario.idRol === 2 ? "recepcionista" : null;
        if (rol !== parsed.data.rol) throw new IncorrectRole();

        return { id: String(usuario.idUsuario), name: usuario.usuario, rol, idRol: usuario.idRol };
      },
    }),
  ],
});
