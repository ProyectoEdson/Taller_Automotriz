-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-11-2026 a las 16:57:27
-- Versión del servidor: 10.11.16-MariaDB
-- Versión de PHP: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `taller_automotriz`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `primerapellido` varchar(100) NOT NULL,
  `segundoapellido` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `primerapellido`, `segundoapellido`, `telefono`, `email`) VALUES
(1, 'Omar', 'Liebharto', NULL, NULL, NULL),
(2, 'Clara', 'Falcón', 'Jimenez', '6871-9169', 'claraisabel@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id_cotizacion` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `impuesto` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_emision` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cotizaciones`
--

INSERT INTO `cotizaciones` (`id_cotizacion`, `id_orden`, `subtotal`, `impuesto`, `total`, `fecha_emision`) VALUES
(1, 1, 70.00, 4.90, 74.90, '2026-11-15 20:54:01'),
(2, 2, 50.00, 3.50, 53.50, '2026-11-15 21:38:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_cotizacion`
--

CREATE TABLE `detalle_cotizacion` (
  `id_detalle` int(11) NOT NULL,
  `id_cotizacion` int(11) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `total_item` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_cotizacion`
--

INSERT INTO `detalle_cotizacion` (`id_detalle`, `id_cotizacion`, `descripcion`, `tipo`, `cantidad`, `precio_unitario`, `total_item`) VALUES
(1, 1, 'Pintura', 'Repuesto', 1, 15.00, 15.00),
(2, 1, 'Liquido de freno', 'Repuesto', 1, 15.00, 15.00),
(3, 1, 'Patilla de freno', 'Repuesto', 1, 25.00, 25.00),
(4, 1, 'Disco de frenos', 'Repuesto', 1, 15.00, 15.00),
(5, 2, 'prueba', 'Repuesto', 1, 50.00, 50.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `diagnostico_reparacion`
--

CREATE TABLE `diagnostico_reparacion` (
  `id_diagnostico` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `descripcion_fall` longtext DEFAULT NULL,
  `tarea_realiz` longtext DEFAULT NULL,
  `prioridad` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `diagnostico_reparacion`
--

INSERT INTO `diagnostico_reparacion` (`id_diagnostico`, `id_orden`, `descripcion_fall`, `tarea_realiz`, `prioridad`) VALUES
(1, 1, 'Mantenimiento en general', 'Pintura y cambio de frenos', NULL),
(2, 2, 'prueba', 'prueba', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_repuesto`
--

CREATE TABLE `inventario_repuesto` (
  `id_repuesto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` longtext DEFAULT NULL,
  `stock_actual` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) NOT NULL DEFAULT 0,
  `precio_base` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_servicio`
--

CREATE TABLE `ordenes_servicio` (
  `id_orden` int(11) NOT NULL,
  `codigo_orden` varchar(30) NOT NULL,
  `id_vehiculo` int(11) NOT NULL,
  `fecha_ingreso` datetime NOT NULL DEFAULT current_timestamp(),
  `estado_orden` varchar(30) NOT NULL DEFAULT 'Abierta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ordenes_servicio`
--

INSERT INTO `ordenes_servicio` (`id_orden`, `codigo_orden`, `id_vehiculo`, `fecha_ingreso`, `estado_orden`) VALUES
(1, 'AT-261116-1586', 1, '2026-11-15 20:28:43', 'Abierta'),
(2, 'AT-261116-2229', 2, '2026-11-15 21:37:18', 'Abierta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_cuenta`
--

CREATE TABLE `pago_cuenta` (
  `id_pago` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `id_cotizacion` int(11) NOT NULL,
  `monto_pagar` decimal(10,2) NOT NULL,
  `monto_recibido` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(30) NOT NULL,
  `fecha_pago` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pago_cuenta`
--

INSERT INTO `pago_cuenta` (`id_pago`, `id_orden`, `id_cotizacion`, `monto_pagar`, `monto_recibido`, `metodo_pago`, `fecha_pago`) VALUES
(1, 1, 1, 74.90, 74.90, 'Tarjeta', '2026-11-15 20:54:27'),
(2, 2, 2, 53.50, 53.50, 'Tarjeta', '2026-11-15 21:38:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repuestos_utilizados`
--

CREATE TABLE `repuestos_utilizados` (
  `id_repuesto_util` int(11) NOT NULL,
  `id_diagnostico` int(11) NOT NULL,
  `id_repuesto` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `total_item` decimal(10,2) NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `repuestos_utilizados`
--

INSERT INTO `repuestos_utilizados` (`id_repuesto_util`, `id_diagnostico`, `id_repuesto`, `cantidad`, `precio_unitario`, `total_item`, `descripcion`) VALUES
(1, 1, NULL, 1, 15.00, 15.00, 'Pintura'),
(2, 1, NULL, 1, 15.00, 15.00, 'Liquido de freno'),
(3, 1, NULL, 1, 25.00, 25.00, 'Patilla de freno'),
(4, 1, NULL, 1, 15.00, 15.00, 'Disco de frenos'),
(5, 2, NULL, 1, 50.00, 50.00, 'prueba');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'Mecanico'),
(2, 'Recepcionista');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `usuario`, `contrasena`, `id_rol`) VALUES
(1, 'Asadmin', '$2y$10$PFLZiMC7aXu7HUUbTEg1U.Vh38cOesrA.z24asidLPQ2d/3hOgZk2', 2),
(2, 'AsaCorredor', '$2y$10$vot.uRwFOpnTBN6.5R3bkeQwCUjduPF7DfXGC1dfJ/wwKK0QMku76', 1),
(3, 'Ebernal', '$2y$10$UiHmDyKQzhfw4KdIr1PL.uHSrj.SoMY0zoGz3LP3gMw/dAQKs2wka', 1),
(4, 'Rosibelpd', '$2y$10$NiARFI0v0NQQpltnTfQqA..z7l742b3mM1z7mK2AnFG1jESOamOOO', 2),
(5, 'TuñonE', '$2y$10$p9T/6yXtAu9tZ1qLzjRKvuy29IBv4H0.RHzBi2dpld5cqZX3t4K3y', 1),
(6, 'Ejimenez', '$2y$10$ZaE2kilsud5P2J8iqZb9WON4JdTD1uFCN5Vr1K8XLfr6pY1AndNGO', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id_vehiculo` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `placa` varchar(15) NOT NULL,
  `modelo` varchar(50) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `año` int(11) DEFAULT NULL,
  `kilometraje` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id_vehiculo`, `id_cliente`, `placa`, `modelo`, `marca`, `año`, `kilometraje`) VALUES
(1, 1, 'LMN-4567', 'Honda', 'Sedán', NULL, NULL),
(2, 2, 'SDM6021', 'Corolla', 'Toyota', 2027, 49998);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id_cotizacion`),
  ADD KEY `id_orden` (`id_orden`);

--
-- Indices de la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_cotizacion` (`id_cotizacion`);

--
-- Indices de la tabla `diagnostico_reparacion`
--
ALTER TABLE `diagnostico_reparacion`
  ADD PRIMARY KEY (`id_diagnostico`),
  ADD UNIQUE KEY `id_orden` (`id_orden`);

--
-- Indices de la tabla `inventario_repuesto`
--
ALTER TABLE `inventario_repuesto`
  ADD PRIMARY KEY (`id_repuesto`);

--
-- Indices de la tabla `ordenes_servicio`
--
ALTER TABLE `ordenes_servicio`
  ADD PRIMARY KEY (`id_orden`),
  ADD UNIQUE KEY `codigo_orden` (`codigo_orden`),
  ADD KEY `id_vehiculo` (`id_vehiculo`);

--
-- Indices de la tabla `pago_cuenta`
--
ALTER TABLE `pago_cuenta`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_cotizacion` (`id_cotizacion`),
  ADD KEY `id_orden` (`id_orden`);

--
-- Indices de la tabla `repuestos_utilizados`
--
ALTER TABLE `repuestos_utilizados`
  ADD PRIMARY KEY (`id_repuesto_util`),
  ADD KEY `id_diagnostico` (`id_diagnostico`),
  ADD KEY `id_repuesto` (`id_repuesto`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `id_rol` (`id_rol`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id_vehiculo`),
  ADD UNIQUE KEY `placa` (`placa`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id_cotizacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `diagnostico_reparacion`
--
ALTER TABLE `diagnostico_reparacion`
  MODIFY `id_diagnostico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `inventario_repuesto`
--
ALTER TABLE `inventario_repuesto`
  MODIFY `id_repuesto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordenes_servicio`
--
ALTER TABLE `ordenes_servicio`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pago_cuenta`
--
ALTER TABLE `pago_cuenta`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `repuestos_utilizados`
--
ALTER TABLE `repuestos_utilizados`
  MODIFY `id_repuesto_util` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id_vehiculo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD CONSTRAINT `cotizaciones_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes_servicio` (`id_orden`);

--
-- Filtros para la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD CONSTRAINT `detalle_cotizacion_ibfk_1` FOREIGN KEY (`id_cotizacion`) REFERENCES `cotizaciones` (`id_cotizacion`);

--
-- Filtros para la tabla `diagnostico_reparacion`
--
ALTER TABLE `diagnostico_reparacion`
  ADD CONSTRAINT `diagnostico_reparacion_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes_servicio` (`id_orden`);

--
-- Filtros para la tabla `ordenes_servicio`
--
ALTER TABLE `ordenes_servicio`
  ADD CONSTRAINT `ordenes_servicio_ibfk_1` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`);

--
-- Filtros para la tabla `pago_cuenta`
--
ALTER TABLE `pago_cuenta`
  ADD CONSTRAINT `pago_cuenta_ibfk_1` FOREIGN KEY (`id_cotizacion`) REFERENCES `cotizaciones` (`id_cotizacion`),
  ADD CONSTRAINT `pago_cuenta_ibfk_2` FOREIGN KEY (`id_orden`) REFERENCES `ordenes_servicio` (`id_orden`);

--
-- Filtros para la tabla `repuestos_utilizados`
--
ALTER TABLE `repuestos_utilizados`
  ADD CONSTRAINT `repuestos_utilizados_ibfk_1` FOREIGN KEY (`id_diagnostico`) REFERENCES `diagnostico_reparacion` (`id_diagnostico`),
  ADD CONSTRAINT `repuestos_utilizados_ibfk_2` FOREIGN KEY (`id_repuesto`) REFERENCES `inventario_repuesto` (`id_repuesto`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `vehiculos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
