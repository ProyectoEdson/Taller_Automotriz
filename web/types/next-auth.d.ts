import type { DefaultSession } from "next-auth";

type TallerRole = "mecanico" | "recepcionista";

declare module "next-auth" {
  interface User {
    rol: TallerRole;
    idRol: number;
  }

  interface Session {
    user: DefaultSession["user"] & {
      id: string;
      rol: TallerRole;
      idRol: number;
    };
  }
}

declare module "@auth/core/jwt" {
  interface JWT {
    rol: TallerRole;
    idRol: number;
  }
}
