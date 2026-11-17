"use client";

import { useEffect } from "react";
import { usePathname } from "next/navigation";

export default function BodyClass() {
  const pathname = usePathname();

  useEffect(() => {
    document.body.classList.toggle("login-body-wrapper", pathname === "/login");
    document.body.classList.toggle("inicio-body", pathname === "/inicio");
  }, [pathname]);

  return null;
}
