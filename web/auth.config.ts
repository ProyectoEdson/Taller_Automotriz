import type { NextAuthConfig } from "next-auth";

const authConfig = {
  pages: { signIn: "/login" },
  session: { strategy: "jwt" },
  providers: [],
  callbacks: {
    jwt({ token, user }) {
      if (user) {
        token.rol = user.rol;
        token.idRol = user.idRol;
      }
      return token;
    },
    session({ session, token }) {
      session.user.id = token.sub!;
      session.user.rol = token.rol;
      session.user.idRol = token.idRol;
      return session;
    },
  },
} satisfies NextAuthConfig;

export default authConfig;
