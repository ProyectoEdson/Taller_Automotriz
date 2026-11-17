import type { Metadata } from "next";
import type { ReactNode } from "react";
import BodyClass from "@/components/BodyClass";
import "./globals.css";

export const metadata: Metadata = { title: "Taller Automotriz" };

export default function RootLayout({ children }: { children: ReactNode }) {
  return <html lang="es"><body><BodyClass />{children}</body></html>;
}
