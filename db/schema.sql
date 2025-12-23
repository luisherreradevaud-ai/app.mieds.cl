-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 05, 2025 at 09:39 AM
-- Server version: 10.4.34-MariaDB
-- PHP Version: 7.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `barrcl_cocholg`
--

-- --------------------------------------------------------

--
-- Table structure for table `accesorios`
--

CREATE TABLE `accesorios` (
  `id` int(11) NOT NULL,
  `id_activos` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `creada` datetime NOT NULL DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL,
  `ultimo_cambio` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activos`
--

CREATE TABLE `activos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `modelo` mediumtext NOT NULL,
  `codigo` varchar(100) NOT NULL,
  `capacidad` varchar(100) NOT NULL,
  `clasificacion` varchar(100) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `propietario` varchar(100) NOT NULL,
  `adquisicion_date` date DEFAULT NULL,
  `valorizacion` varchar(100) NOT NULL,
  `ultima_inspeccion` date DEFAULT NULL,
  `proxima_inspeccion` date DEFAULT NULL,
  `inspeccion_procedimiento` mediumtext NOT NULL,
  `inspeccion_periodicidad` varchar(100) NOT NULL,
  `ultima_mantencion` date DEFAULT NULL,
  `proxima_mantencion` date DEFAULT NULL,
  `mantencion_procedimiento` mediumtext NOT NULL,
  `mantencion_periodicidad` varchar(100) NOT NULL,
  `creada` date DEFAULT NULL,
  `id_media_header` int(11) NOT NULL,
  `id_usuarios_control` int(11) NOT NULL DEFAULT 0,
  `ubicacion` varchar(100) NOT NULL,
  `id_clientes_ubicacion` int(11) NOT NULL,
  `clase` varchar(255) NOT NULL,
  `linea_productiva` enum('alcoholica','analcoholica','general') DEFAULT 'general',
  `id_batches` int(11) NOT NULL DEFAULT 0,
  `litraje` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alertas`
--

CREATE TABLE `alertas` (
  `id` int(11) NOT NULL,
  `alerta` varchar(300) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `estado` varchar(20) NOT NULL,
  `id_usuarios` int(11) NOT NULL,
  `usuarios_nivel` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barriles`
--

CREATE TABLE `barriles` (
  `id` int(11) NOT NULL,
  `tipo_barril` varchar(30) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `estado` varchar(50) NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `id_clientes` int(11) NOT NULL,
  `id_batches` int(11) NOT NULL DEFAULT 0,
  `clasificacion` varchar(100) NOT NULL,
  `litraje` int(11) NOT NULL DEFAULT 0,
  `litros_cargados` int(11) NOT NULL DEFAULT 0,
  `fecha_llenado` datetime DEFAULT NULL,
  `id_activos` int(11) NOT NULL DEFAULT 0,
  `id_batches_activos` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barriles_estados`
--

CREATE TABLE `barriles_estados` (
  `id` int(11) NOT NULL,
  `id_barriles` int(11) NOT NULL,
  `id_clientes` int(11) NOT NULL,
  `inicio_date` datetime DEFAULT NULL,
  `finalizacion_date` datetime DEFAULT NULL,
  `tiempo_transcurrido` varchar(255) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `id_usuarios` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barriles_reemplazos`
--

CREATE TABLE `barriles_reemplazos` (
  `id` int(11) NOT NULL,
  `id_barriles_devuelto` int(11) NOT NULL,
  `id_barriles_reemplazo` int(11) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `motivo` varchar(500) NOT NULL,
  `id_entregas_productos` int(11) NOT NULL,
  `id_entregas` int(11) NOT NULL,
  `id_clientes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `id` int(11) NOT NULL,
  `batch_date` date DEFAULT NULL,
  `batch_id_usuarios_cocinero` int(11) DEFAULT 0,
  `id_recetas` int(11) DEFAULT 0,
  `batch_nombre` varchar(255) NOT NULL,
  `batch_litros` int(11) NOT NULL DEFAULT 0,
  `licor_temperatura` decimal(5,2) DEFAULT 0.00,
  `licor_ph` decimal(4,2) DEFAULT 0.00,
  `licor_litros` decimal(10,2) DEFAULT 0.00,
  `maceracion_hora_inicio` varchar(10) DEFAULT NULL,
  `maceracion_temperatura` decimal(5,2) DEFAULT 0.00,
  `maceracion_litros` decimal(10,2) DEFAULT 0.00,
  `maceracion_ph` decimal(4,2) DEFAULT 0.00,
  `maceracion_hora_finalizacion` varchar(10) DEFAULT NULL,
  `lavado_de_granos_hora_inicio` varchar(10) DEFAULT NULL,
  `lavado_de_granos_mosto` decimal(10,2) DEFAULT 0.00,
  `lavado_de_granos_densidad` decimal(5,3) DEFAULT 0.000,
  `lavado_de_granos_tipo_de_densidad` varchar(50) DEFAULT NULL,
  `lavado_de_granos_hora_termino` varchar(10) DEFAULT NULL,
  `coccion_ph_inicial` decimal(4,2) DEFAULT 0.00,
  `coccion_ph_final` decimal(4,2) DEFAULT 0.00,
  `coccion_recilar` decimal(10,2) DEFAULT 0.00,
  `combustible_gas` decimal(10,2) DEFAULT 0.00,
  `inoculacion_temperatura` decimal(5,2) DEFAULT 0.00,
  `fermentacion_date` date DEFAULT NULL,
  `fermentacion_hora_inicio` varchar(10) DEFAULT NULL,
  `inoculacion_temperatura_inicio` decimal(5,2) DEFAULT 0.00,
  `fermentacion_id_activos` text DEFAULT '0',
  `fermentacion_temperatura` decimal(5,2) DEFAULT 0.00,
  `fermentacion_hora_finalizacion` varchar(10) DEFAULT NULL,
  `fermentacion_ph` decimal(4,2) DEFAULT 0.00,
  `fermentacion_densidad` decimal(5,3) DEFAULT 0.000,
  `fermentacion_tipo_de_densidad` varchar(50) DEFAULT NULL,
  `fermentacion_finalizada` int(11) NOT NULL DEFAULT 0,
  `fermentacion_finalizada_datetime` datetime DEFAULT NULL,
  `traspaso_datetime` datetime DEFAULT NULL,
  `maduracion_date` date DEFAULT NULL,
  `maduracion_temperatura_inicio` decimal(5,2) DEFAULT 0.00,
  `maduracion_hora_inicio` varchar(10) DEFAULT NULL,
  `maduracion_temperatura_finalizacion` decimal(5,2) DEFAULT 0.00,
  `maduracion_hora_finalizacion` varchar(10) DEFAULT NULL,
  `datetime_finalizacion` datetime DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `creada` timestamp NOT NULL DEFAULT current_timestamp(),
  `etapa_seleccionada` varchar(30) NOT NULL DEFAULT 'batch',
  `tipo` varchar(255) NOT NULL,
  `finalizacion_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batches_2`
--

CREATE TABLE `batches_2` (
  `id` int(11) NOT NULL,
  `id_recetas` int(11) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_termino` date DEFAULT NULL,
  `id_usuarios_ejecutor` int(11) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `id_activos` int(11) NOT NULL,
  `observaciones` mediumtext NOT NULL,
  `creada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batches_activos`
--

CREATE TABLE `batches_activos` (
  `id` int(11) NOT NULL,
  `id_batches` int(11) NOT NULL DEFAULT 0,
  `id_activos` int(11) NOT NULL DEFAULT 0,
  `estado` varchar(100) NOT NULL,
  `litraje` int(11) NOT NULL DEFAULT 0,
  `creada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batches_barriles`
--

CREATE TABLE `batches_barriles` (
  `id` int(11) NOT NULL,
  `id_barriles` int(11) NOT NULL,
  `id_batches` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batches_cajas`
--

CREATE TABLE `batches_cajas` (
  `id` int(11) NOT NULL,
  `id_batches` int(11) NOT NULL,
  `cantidad` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batches_de_envases`
--

CREATE TABLE `batches_de_envases` (
  `id` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL DEFAULT 'Lata' COMMENT 'Tipo de envase: Lata, Botella',
  `id_batches` int(11) NOT NULL DEFAULT 0 COMMENT 'Batch de cerveza origen',
  `id_activos` int(11) NOT NULL DEFAULT 0 COMMENT 'Fermentador origen (si aplica)',
  `id_barriles` int(11) NOT NULL DEFAULT 0 COMMENT 'Barril origen (si aplica)',
  `id_batches_activos` int(11) NOT NULL DEFAULT 0 COMMENT 'BatchActivo origen (si aplica)',
  `id_formatos_de_envases` int(11) NOT NULL COMMENT 'Formato de envase utilizado',
  `id_recetas` int(11) NOT NULL DEFAULT 0 COMMENT 'Receta del batch',
  `cantidad_de_envases` int(11) NOT NULL COMMENT 'Cantidad total de envases creados',
  `volumen_origen_ml` int(11) NOT NULL DEFAULT 0 COMMENT 'Volumen disponible antes de enlatar (ml)',
  `rendimiento_ml` int(11) NOT NULL DEFAULT 0 COMMENT 'Volumen efectivamente enlatado (ml)',
  `merma_ml` int(11) NOT NULL DEFAULT 0 COMMENT 'Volumen perdido (origen - rendimiento)',
  `id_usuarios` int(11) NOT NULL COMMENT 'Usuario que realizó el enlatado',
  `estado` varchar(50) NOT NULL DEFAULT 'Cargado en planta' COMMENT 'Cargado en planta, Sin latas',
  `creada` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizada` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batches_enfriado`
--

CREATE TABLE `batches_enfriado` (
  `id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `temperatura_inicio` int(11) NOT NULL DEFAULT 0,
  `hora_inicio` varchar(10) NOT NULL,
  `ph` int(11) NOT NULL DEFAULT 0,
  `densidad` varchar(50) NOT NULL,
  `ph_enfriado` int(11) NOT NULL DEFAULT 0,
  `seq_index` int(11) NOT NULL DEFAULT 0,
  `creada` datetime DEFAULT NULL,
  `id_batches` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batches_historial`
--

CREATE TABLE `batches_historial` (
  `id` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `creada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batches_insumos`
--

CREATE TABLE `batches_insumos` (
  `id` int(11) NOT NULL,
  `id_batches` int(11) NOT NULL,
  `id_insumos` int(11) NOT NULL,
  `cantidad` float NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `date` date NOT NULL DEFAULT current_timestamp(),
  `etapa` varchar(100) NOT NULL,
  `etapa_index` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batches_lupulizacion`
--

CREATE TABLE `batches_lupulizacion` (
  `id` int(11) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `seq_index` int(11) NOT NULL DEFAULT 0,
  `creada` datetime DEFAULT NULL,
  `hora` varchar(5) NOT NULL,
  `date` date DEFAULT NULL,
  `id_batches` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batches_traspasos`
--

CREATE TABLE `batches_traspasos` (
  `id` int(11) NOT NULL,
  `id_batches` int(11) DEFAULT 0,
  `cantidad` int(11) DEFAULT 0,
  `merma_litros` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Litros perdidos durante el traspaso',
  `id_fermentadores_inicio` int(11) DEFAULT 0,
  `id_fermentadores_final` int(11) DEFAULT 0,
  `creada` timestamp NOT NULL DEFAULT current_timestamp(),
  `date` date DEFAULT NULL,
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cajas`
--

CREATE TABLE `cajas` (
  `id` int(11) NOT NULL,
  `id_productos` int(11) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `estado` varchar(50) NOT NULL,
  `codigo` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cajas_de_envases`
--

CREATE TABLE `cajas_de_envases` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL COMMENT 'Código único de la caja (ej: CL-2025-0001)',
  `id_productos` int(11) NOT NULL COMMENT 'Producto asociado',
  `cantidad_envases` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad de envases en la caja',
  `id_usuarios` int(11) NOT NULL DEFAULT 0 COMMENT 'Usuario que creó la caja',
  `estado` varchar(50) NOT NULL DEFAULT 'En planta' COMMENT 'En planta, En despacho, Entregada',
  `creada` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizada` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `estado` varchar(50) NOT NULL,
  `criterio` varchar(50) NOT NULL,
  `precio_tripack` int(11) NOT NULL,
  `precio_24` int(11) NOT NULL,
  `emite_factura` int(11) NOT NULL,
  `RUT` varchar(50) NOT NULL,
  `RznSoc` varchar(150) NOT NULL,
  `Giro` varchar(150) NOT NULL,
  `Dir` varchar(150) NOT NULL,
  `Cmna` varchar(150) NOT NULL,
  `meta_barriles_mensuales` int(11) NOT NULL,
  `meta_cajas_mensuales` int(11) NOT NULL,
  `id_usuarios_vendedor` int(11) NOT NULL DEFAULT 0,
  `salidas_habilitadas` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clientes_productos_precios`
--

CREATE TABLE `clientes_productos_precios` (
  `id` int(11) NOT NULL,
  `id_clientes` int(11) NOT NULL,
  `id_productos` int(11) NOT NULL,
  `precio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compras_de_insumos`
--

CREATE TABLE `compras_de_insumos` (
  `id` int(11) NOT NULL,
  `id_usuarios` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `creada` datetime DEFAULT NULL,
  `monto` int(11) NOT NULL,
  `factura` varchar(100) NOT NULL,
  `estado` varchar(30) NOT NULL,
  `comentarios` varchar(500) NOT NULL,
  `id_proveedores` int(11) NOT NULL,
  `id_gastos` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compras_de_insumos_insumos`
--

CREATE TABLE `compras_de_insumos_insumos` (
  `id` int(11) NOT NULL,
  `id_compras_de_insumos` int(11) NOT NULL,
  `monto` int(11) NOT NULL,
  `id_insumos` int(11) NOT NULL,
  `id_proveedores` int(11) NOT NULL,
  `id_tipos_de_insumos` int(11) NOT NULL,
  `cantidad` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `configuraciones`
--

CREATE TABLE `configuraciones` (
  `id` int(11) NOT NULL,
  `nombre_empresa` varchar(300) NOT NULL,
  `duracion_historial` varchar(20) NOT NULL DEFAULT '1 MONTHS',
  `duracion_notificaciones` varchar(20) NOT NULL DEFAULT '7 DAYS',
  `id_media_header` int(11) NOT NULL,
  `login_color_fondo` varchar(10) NOT NULL,
  `login_color_texto` varchar(10) NOT NULL,
  `email_header` varchar(1000) NOT NULL,
  `email_footer` varchar(1000) NOT NULL,
  `email_empresa` varchar(200) NOT NULL,
  `telefono_empresa` varchar(50) NOT NULL,
  `direccion_empresa` varchar(300) NOT NULL,
  `representante_empresa` varchar(300) NOT NULL,
  `rut_empresa` varchar(50) NOT NULL,
  `giro_empresa` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversaciones_internas`
--

CREATE TABLE `conversaciones_internas` (
  `id` int(11) NOT NULL,
  `nombre_vista` varchar(100) NOT NULL COMMENT 'Nombre de la vista/entidad (ej: batch, pedido, cliente)',
  `id_entidad` varchar(36) NOT NULL COMMENT 'ID de la entidad relacionada',
  `estado` varchar(50) DEFAULT 'abierto',
  `id_propietario` varchar(36) DEFAULT NULL,
  `id_creado_por` varchar(36) DEFAULT NULL,
  `id_actualizado_por` varchar(36) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversaciones_internas_archivos`
--

CREATE TABLE `conversaciones_internas_archivos` (
  `id` int(11) NOT NULL,
  `id_comentario` int(11) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL COMMENT 'Ruta relativa del archivo en /media/',
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'activo',
  `metadata` text DEFAULT NULL COMMENT 'JSON con info adicional (mimetype, size, etc)',
  `id_subido_por` varchar(36) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversaciones_internas_comentarios`
--

CREATE TABLE `conversaciones_internas_comentarios` (
  `id` int(11) NOT NULL,
  `id_conversacion_interna` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `id_autor` varchar(36) NOT NULL,
  `estado` varchar(50) DEFAULT 'activo',
  `likes` text DEFAULT NULL COMMENT 'JSON array de IDs de usuarios que dieron like',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversaciones_internas_tags`
--

CREATE TABLE `conversaciones_internas_tags` (
  `id` int(11) NOT NULL,
  `id_comentario` int(11) NOT NULL,
  `id_usuario` varchar(36) NOT NULL COMMENT 'Usuario mencionado',
  `id_creado_por` varchar(36) NOT NULL COMMENT 'Usuario que hizo la mención',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `despachos`
--

CREATE TABLE `despachos` (
  `id` int(11) NOT NULL,
  `id_usuarios_repartidor` int(11) NOT NULL,
  `id_clientes` int(11) NOT NULL DEFAULT 0 COMMENT 'Cliente destino del despacho',
  `estado` varchar(30) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `id_pedidos` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `despachos_productos`
--

CREATE TABLE `despachos_productos` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `cantidad` varchar(50) NOT NULL,
  `tipos_cerveza` varchar(50) NOT NULL,
  `id_despachos` int(11) NOT NULL,
  `codigo` varchar(70) NOT NULL,
  `id_barriles` int(11) NOT NULL,
  `estado` varchar(60) NOT NULL,
  `id_productos` int(11) NOT NULL DEFAULT 0,
  `id_cajas_de_envases` int(11) NOT NULL DEFAULT 0 COMMENT 'Caja de envases asociada (si tipo=CajaEnvases)',
  `clasificacion` varchar(100) NOT NULL,
  `id_pedidos` int(11) NOT NULL DEFAULT 0,
  `id_pedidos_productos` int(11) NOT NULL DEFAULT 0,
  `creada` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documentos`
--

CREATE TABLE `documentos` (
  `id` int(11) NOT NULL,
  `id_usuarios` int(11) NOT NULL DEFAULT 0,
  `monto` int(11) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `folio` varchar(100) NOT NULL,
  `id_clientes` int(11) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `datetime_aprobado` datetime DEFAULT NULL,
  `id_pagos` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dte`
--

CREATE TABLE `dte` (
  `id` int(11) NOT NULL,
  `folio` int(11) NOT NULL,
  `emisor` int(11) NOT NULL,
  `receptor` int(11) NOT NULL,
  `dte` int(11) NOT NULL,
  `certificacion` int(11) NOT NULL,
  `tasa` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `neto` int(11) NOT NULL,
  `iva` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `track_id` int(11) NOT NULL,
  `fecha_hora_creacion` datetime DEFAULT NULL,
  `id_entregas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entidades`
--

CREATE TABLE `entidades` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `table_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entregas`
--

CREATE TABLE `entregas` (
  `id` int(11) NOT NULL,
  `id_usuarios_repartidor` int(11) NOT NULL,
  `id_clientes` int(11) NOT NULL,
  `tipo_de_entrega` varchar(50) NOT NULL,
  `estado` varchar(30) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `monto` int(11) NOT NULL,
  `factura` varchar(60) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `abonado` int(11) NOT NULL DEFAULT 0,
  `datetime_abonado` datetime DEFAULT NULL,
  `rand_int` int(11) NOT NULL,
  `receptor_nombre` varchar(100) NOT NULL,
  `id_usuarios_vendedor` int(11) NOT NULL DEFAULT 0,
  `observaciones` mediumtext NOT NULL,
  `id_despachos` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entregas_productos`
--

CREATE TABLE `entregas_productos` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `cantidad` varchar(50) NOT NULL,
  `tipos_cerveza` varchar(50) NOT NULL,
  `id_entregas` int(11) NOT NULL,
  `codigo` varchar(70) NOT NULL,
  `monto` int(11) NOT NULL,
  `id_barriles` int(11) NOT NULL,
  `id_cajas_de_envases` int(11) NOT NULL DEFAULT 0 COMMENT 'Caja de envases entregada',
  `QtyItem` int(11) NOT NULL DEFAULT 1,
  `id_productos` int(11) NOT NULL DEFAULT 0,
  `id_pedidos_productos` int(11) NOT NULL DEFAULT 0,
  `id_despachos_productos` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `envases`
--

CREATE TABLE `envases` (
  `id` int(11) NOT NULL,
  `id_formatos_de_envases` int(11) NOT NULL COMMENT 'Formato del envase',
  `volumen_ml` int(11) NOT NULL COMMENT 'Volumen de la lata en ml',
  `id_batches_de_envases` int(11) NOT NULL COMMENT 'Batch de envases al que pertenece',
  `id_batches` int(11) NOT NULL DEFAULT 0 COMMENT 'Batch de cerveza origen',
  `id_barriles` int(11) NOT NULL DEFAULT 0 COMMENT 'Barril origen (si aplica)',
  `id_activos` int(11) NOT NULL DEFAULT 0 COMMENT 'Fermentador origen (si aplica)',
  `id_cajas_de_envases` int(11) NOT NULL DEFAULT 0 COMMENT 'Caja a la que pertenece (0 si no está en caja)',
  `estado` varchar(50) NOT NULL DEFAULT 'Enlatada' COMMENT 'Enlatada, En caja en planta, En despacho, Entregada',
  `creada` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizada` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `errores`
--

CREATE TABLE `errores` (
  `id` int(11) NOT NULL,
  `descripcion` mediumtext NOT NULL,
  `url` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fermentadores`
--

CREATE TABLE `fermentadores` (
  `id` int(11) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `codigo` varchar(100) NOT NULL,
  `id_batches` int(11) NOT NULL DEFAULT 0,
  `clasificacion` varchar(100) NOT NULL,
  `activo` int(11) NOT NULL DEFAULT 1,
  `creada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formatos`
--

CREATE TABLE `formatos` (
  `id` int(11) NOT NULL,
  `nombre` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `formatos_de_envases`
--

CREATE TABLE `formatos_de_envases` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre descriptivo (ej: Lata 350ml)',
  `tipo` varchar(20) NOT NULL DEFAULT 'Lata' COMMENT 'Tipo de envase: Lata, Botella',
  `volumen_ml` int(11) NOT NULL COMMENT 'Volumen en mililitros',
  `estado` varchar(50) NOT NULL DEFAULT 'activo',
  `creada` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizada` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gastos`
--

CREATE TABLE `gastos` (
  `id` int(11) NOT NULL,
  `id_usuarios` int(11) NOT NULL,
  `monto` int(11) NOT NULL,
  `estado` varchar(30) NOT NULL,
  `creada` date DEFAULT NULL,
  `tipo_de_gasto` varchar(100) NOT NULL,
  `item` varchar(100) NOT NULL,
  `comentarios` varchar(300) NOT NULL,
  `id_media_header` int(11) NOT NULL DEFAULT 0,
  `date_vencimiento` date NOT NULL DEFAULT current_timestamp(),
  `aprobado` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gastos_fijos`
--

CREATE TABLE `gastos_fijos` (
  `id` int(11) NOT NULL,
  `tipo_de_gasto` varchar(300) NOT NULL,
  `item` varchar(300) NOT NULL,
  `comentarios` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gastos_fijos_mes`
--

CREATE TABLE `gastos_fijos_mes` (
  `id` int(11) NOT NULL,
  `id_gastos_fijos` int(11) NOT NULL DEFAULT 0,
  `mes` int(11) NOT NULL DEFAULT 0,
  `ano` int(11) NOT NULL DEFAULT 0,
  `proyectado_neto` int(11) NOT NULL DEFAULT 0,
  `proyectado_impuesto` int(11) NOT NULL DEFAULT 0,
  `proyectado_bruto` int(11) NOT NULL DEFAULT 0,
  `real_neto` int(11) NOT NULL DEFAULT 0,
  `real_impuesto` int(11) NOT NULL DEFAULT 0,
  `real_bruto` int(11) NOT NULL DEFAULT 0,
  `creada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gastos_lineas_de_negocio`
--

CREATE TABLE `gastos_lineas_de_negocio` (
  `id` int(11) NOT NULL,
  `id_gastos` int(11) NOT NULL DEFAULT 0,
  `id_gastos_fijos` int(11) NOT NULL DEFAULT 0,
  `id_lineas_de_negocio` int(11) NOT NULL DEFAULT 0,
  `porcentaje` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historial`
--

CREATE TABLE `historial` (
  `id` int(11) NOT NULL,
  `accion` varchar(300) NOT NULL,
  `id_usuarios` int(11) NOT NULL,
  `creada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `insumos`
--

CREATE TABLE `insumos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `id_tipos_de_insumos` int(11) NOT NULL,
  `comentarios` varchar(300) NOT NULL,
  `unidad_de_medida` varchar(30) NOT NULL,
  `despacho` float NOT NULL,
  `bodega` float NOT NULL,
  `last_modified` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_modified_mensaje` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kanban_columnas`
--

CREATE TABLE `kanban_columnas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `id_kanban_tableros` int(11) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `color` varchar(7) DEFAULT '#6A1693',
  `creada` datetime DEFAULT NULL,
  `actualizada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kanban_etiquetas`
--

CREATE TABLE `kanban_etiquetas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `codigo_hex` varchar(7) DEFAULT '#6A1693'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kanban_tableros`
--

CREATE TABLE `kanban_tableros` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_entidad` varchar(100) NOT NULL,
  `id_usuario_creador` int(11) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `creada` datetime DEFAULT NULL,
  `actualizada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kanban_tableros_usuarios`
--

CREATE TABLE `kanban_tableros_usuarios` (
  `id` int(11) NOT NULL,
  `id_kanban_tableros` int(11) NOT NULL,
  `id_usuarios` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kanban_tareas`
--

CREATE TABLE `kanban_tareas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_kanban_columnas` int(11) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `recordatorio_vencimiento` varchar(50) DEFAULT NULL,
  `checklist` text DEFAULT NULL,
  `links` text DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'Pendiente',
  `time_elapsed` int(11) DEFAULT 0,
  `creada` datetime DEFAULT NULL,
  `actualizada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kanban_tareas_etiquetas`
--

CREATE TABLE `kanban_tareas_etiquetas` (
  `id` int(11) NOT NULL,
  `id_kanban_tareas` int(11) NOT NULL,
  `id_kanban_etiquetas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kanban_tareas_usuarios`
--

CREATE TABLE `kanban_tareas_usuarios` (
  `id` int(11) NOT NULL,
  `id_kanban_tareas` int(11) NOT NULL,
  `id_usuarios` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lineas_de_negocio`
--

CREATE TABLE `lineas_de_negocio` (
  `id` int(11) NOT NULL,
  `nombre` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locaciones`
--

CREATE TABLE `locaciones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `creada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mailing`
--

CREATE TABLE `mailing` (
  `id` int(11) NOT NULL,
  `nombre` varchar(300) NOT NULL,
  `asunto` varchar(300) NOT NULL,
  `mensaje` mediumtext NOT NULL,
  `creada` datetime DEFAULT NULL,
  `categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mantenciones`
--

CREATE TABLE `mantenciones` (
  `id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `ejecutor` varchar(100) NOT NULL,
  `observaciones` mediumtext NOT NULL,
  `tarea` varchar(100) NOT NULL,
  `id_activos` int(11) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_termino` time NOT NULL,
  `ubicacion` varchar(100) NOT NULL,
  `id_clientes_ubicacion` int(11) NOT NULL DEFAULT 0,
  `accesorios_renovados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `descripcion` mediumtext NOT NULL,
  `url` mediumtext NOT NULL,
  `tipo` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_activos`
--

CREATE TABLE `media_activos` (
  `id` int(11) NOT NULL,
  `id_activos` int(11) NOT NULL,
  `id_media` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_configuraciones`
--

CREATE TABLE `media_configuraciones` (
  `id` int(11) NOT NULL,
  `id_configuraciones` int(11) NOT NULL,
  `id_media` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_documentos`
--

CREATE TABLE `media_documentos` (
  `id` int(11) NOT NULL,
  `id_media` int(11) NOT NULL,
  `id_documentos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_gastos`
--

CREATE TABLE `media_gastos` (
  `id` int(11) NOT NULL,
  `id_media` int(11) NOT NULL,
  `id_gastos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_gastos_fijos_mes`
--

CREATE TABLE `media_gastos_fijos_mes` (
  `id` int(11) NOT NULL,
  `id_media` int(11) NOT NULL DEFAULT 0,
  `id_gastos_fijos_mes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_kanban_tareas`
--

CREATE TABLE `media_kanban_tareas` (
  `id` int(11) NOT NULL,
  `id_media` int(11) NOT NULL,
  `id_kanban_tareas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_mantenciones`
--

CREATE TABLE `media_mantenciones` (
  `id` int(11) NOT NULL,
  `id_media` int(11) NOT NULL,
  `id_mantenciones` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `icon` varchar(100) NOT NULL,
  `link` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `id_usuarios` int(11) NOT NULL,
  `texto` varchar(500) NOT NULL,
  `link` varchar(200) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `vista` int(11) NOT NULL DEFAULT 0,
  `vista_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notificaciones_usuarios_niveles`
--

CREATE TABLE `notificaciones_usuarios_niveles` (
  `id` int(11) NOT NULL,
  `id_usuarios_niveles` int(11) NOT NULL,
  `id_tipos_de_notificaciones` int(11) NOT NULL,
  `app` int(11) NOT NULL,
  `email` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `ids_entregas` varchar(200) NOT NULL,
  `id_clientes` int(11) NOT NULL,
  `codigo_transaccion` varchar(100) NOT NULL,
  `facturas` varchar(100) NOT NULL,
  `forma_de_pago` varchar(50) NOT NULL,
  `id_usuarios` int(11) NOT NULL DEFAULT 0,
  `restante` int(11) NOT NULL DEFAULT 0,
  `id_documentos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `id_clientes` int(11) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pedidos_productos`
--

CREATE TABLE `pedidos_productos` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `cantidad` varchar(50) NOT NULL,
  `tipos_cerveza` varchar(50) NOT NULL,
  `id_pedidos` int(11) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `id_productos` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
  `id_secciones` int(11) NOT NULL,
  `id_usuarios_niveles` int(11) NOT NULL,
  `acceso` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `precios`
--

CREATE TABLE `precios` (
  `id` int(11) NOT NULL,
  `id_clientes` int(11) NOT NULL,
  `tipo_barril` varchar(30) NOT NULL,
  `tipo_cerveza` varchar(30) NOT NULL,
  `precio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prevlinks`
--

CREATE TABLE `prevlinks` (
  `id` int(11) NOT NULL,
  `id_usuarios` int(11) NOT NULL,
  `url` varchar(300) NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `count` int(11) NOT NULL,
  `id_secciones` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(300) NOT NULL,
  `tipo` varchar(300) NOT NULL,
  `id_recetas` int(11) NOT NULL DEFAULT 0,
  `clasificacion` varchar(100) NOT NULL,
  `cantidad` varchar(30) NOT NULL,
  `monto` int(11) NOT NULL DEFAULT 0,
  `codigo_de_barra` varchar(100) NOT NULL,
  `id_formatos_de_envases` int(11) NOT NULL DEFAULT 0 COMMENT 'Formato de envase para este producto',
  `cantidad_de_envases` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad de envases por caja/pack',
  `tipo_envase` varchar(20) NOT NULL DEFAULT 'Lata' COMMENT 'Tipo de envase: Lata, Botella',
  `es_mixto` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = producto mixto que acepta multiples recetas (mismo formato)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productos_items`
--

CREATE TABLE `productos_items` (
  `id` int(11) NOT NULL,
  `nombre` varchar(300) NOT NULL,
  `impuesto` varchar(100) NOT NULL DEFAULT 'IVA',
  `monto_bruto` int(11) NOT NULL DEFAULT 0,
  `id_productos` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `id_usuarios` int(11) NOT NULL,
  `nombre` varchar(300) NOT NULL,
  `email` varchar(200) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `comentarios` varchar(300) NOT NULL,
  `numero_cuenta` varchar(100) NOT NULL,
  `rut_empresa` varchar(50) NOT NULL,
  `ids_tipos_de_insumos` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proyectos`
--

CREATE TABLE `proyectos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `clasificacion` varchar(100) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `date_inicio` date DEFAULT NULL,
  `date_finalizacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proyectos_gastos`
--

CREATE TABLE `proyectos_gastos` (
  `id` int(11) NOT NULL,
  `id_proyectos` int(11) NOT NULL,
  `id_gastos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proyectos_ingresos`
--

CREATE TABLE `proyectos_ingresos` (
  `id` int(11) NOT NULL,
  `id_proyectos` int(11) NOT NULL,
  `monto` int(11) NOT NULL,
  `forma_de_pago` varchar(100) NOT NULL,
  `impuestos` varchar(100) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `item` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proyectos_productos`
--

CREATE TABLE `proyectos_productos` (
  `id` int(11) NOT NULL,
  `id_proyectos` int(11) NOT NULL,
  `id_relation` int(11) NOT NULL,
  `id_productos` int(11) NOT NULL,
  `monto` int(11) NOT NULL,
  `formato` varchar(40) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `id_gastos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recetas`
--

CREATE TABLE `recetas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(300) NOT NULL,
  `codigo` varchar(100) NOT NULL,
  `clasificacion` varchar(100) NOT NULL,
  `litros` float NOT NULL,
  `observaciones` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recetas_insumos`
--

CREATE TABLE `recetas_insumos` (
  `id` int(11) NOT NULL,
  `id_recetas` int(11) NOT NULL,
  `id_insumos` int(11) NOT NULL,
  `cantidad` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registro_asistencia`
--

CREATE TABLE `registro_asistencia` (
  `id` int(11) NOT NULL,
  `id_usuarios` int(11) NOT NULL DEFAULT 0,
  `date` date DEFAULT NULL,
  `entrada` varchar(10) NOT NULL,
  `salida` varchar(10) NOT NULL,
  `creada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reportes_diarios`
--

CREATE TABLE `reportes_diarios` (
  `id` int(11) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `json_reporte` text NOT NULL,
  `json_discrepancias` text NOT NULL,
  `id_usuarios` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_urls`
--

CREATE TABLE `return_urls` (
  `id` int(11) NOT NULL,
  `hash` int(11) NOT NULL,
  `return_url` varchar(500) NOT NULL,
  `creada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `secciones`
--

CREATE TABLE `secciones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `template_file` varchar(200) NOT NULL,
  `visible` int(11) NOT NULL DEFAULT 1,
  `permisos_editables` int(11) NOT NULL DEFAULT 1,
  `clasificacion` varchar(100) NOT NULL,
  `id_menus` int(11) NOT NULL DEFAULT 0,
  `create_path` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sugerencias`
--

CREATE TABLE `sugerencias` (
  `id` int(11) NOT NULL,
  `id_clientes` int(11) NOT NULL,
  `id_usuarios` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `contenido` mediumtext NOT NULL,
  `creada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tareas`
--

CREATE TABLE `tareas` (
  `id` int(11) NOT NULL,
  `id_usuarios_emisor` int(11) NOT NULL,
  `tipo_envio` varchar(100) NOT NULL,
  `destinatario` varchar(100) NOT NULL,
  `importancia` varchar(100) NOT NULL,
  `tarea` varchar(300) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `plazo_maximo` date DEFAULT NULL,
  `random_int` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tareas_comentarios`
--

CREATE TABLE `tareas_comentarios` (
  `id` int(11) NOT NULL,
  `id_tareas` int(11) NOT NULL,
  `id_usuarios` int(11) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `comentario` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipos_de_gastos`
--

CREATE TABLE `tipos_de_gastos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipos_de_insumos`
--

CREATE TABLE `tipos_de_insumos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `comentarios` varchar(300) NOT NULL,
  `visible` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipos_de_notificaciones`
--

CREATE TABLE `tipos_de_notificaciones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `texto` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipos_de_pago`
--

CREATE TABLE `tipos_de_pago` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transacciones`
--

CREATE TABLE `transacciones` (
  `id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `creada` datetime DEFAULT NULL,
  `ids_entregas` varchar(200) NOT NULL,
  `id_clientes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` mediumtext DEFAULT NULL,
  `apellido` mediumtext DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` mediumtext DEFAULT NULL,
  `password` mediumtext DEFAULT NULL,
  `fecha_creacion` date DEFAULT NULL,
  `nivel` varchar(30) DEFAULT NULL,
  `verificacion` mediumtext DEFAULT NULL,
  `social_network` varchar(20) DEFAULT NULL,
  `app_id` varchar(30) DEFAULT NULL,
  `recuperacion` varchar(30) DEFAULT NULL,
  `id_clientes` int(11) DEFAULT 0,
  `invitacion` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `vendedor_meta_barriles` int(11) DEFAULT 0,
  `vendedor_meta_cajas` int(11) DEFAULT 0,
  `prevlink_direction` varchar(10) DEFAULT NULL,
  `prevlink_count` int(11) DEFAULT 0,
  `id_secciones` int(11) DEFAULT 0,
  `registro_asistencia` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuarios_clientes`
--

CREATE TABLE `usuarios_clientes` (
  `id` int(11) NOT NULL,
  `id_usuarios` int(11) NOT NULL DEFAULT 0,
  `id_clientes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuarios_niveles`
--

CREATE TABLE `usuarios_niveles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `editable` int(11) NOT NULL DEFAULT 1,
  `comentarios` varchar(300) NOT NULL,
  `creada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendedores`
--

CREATE TABLE `vendedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(100) NOT NULL,
  `estado` varchar(30) NOT NULL,
  `meta_barriles_mensuales` int(11) NOT NULL,
  `meta_cajas_mensuales` int(11) NOT NULL,
  `creada` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visitas`
--

CREATE TABLE `visitas` (
  `id` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accesorios`
--
ALTER TABLE `accesorios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activos`
--
ALTER TABLE `activos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activos_linea_productiva` (`linea_productiva`);

--
-- Indexes for table `alertas`
--
ALTER TABLE `alertas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `barriles`
--
ALTER TABLE `barriles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_barriles_fecha_llenado` (`fecha_llenado`);

--
-- Indexes for table `barriles_estados`
--
ALTER TABLE `barriles_estados`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `barriles_reemplazos`
--
ALTER TABLE `barriles_reemplazos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batches_2`
--
ALTER TABLE `batches_2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batches_activos`
--
ALTER TABLE `batches_activos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batches_barriles`
--
ALTER TABLE `batches_barriles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batches_cajas`
--
ALTER TABLE `batches_cajas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batches_de_envases`
--
ALTER TABLE `batches_de_envases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_batches` (`id_batches`),
  ADD KEY `idx_id_activos` (`id_activos`),
  ADD KEY `idx_id_barriles` (`id_barriles`),
  ADD KEY `idx_id_formatos_de_latas` (`id_formatos_de_envases`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_id_recetas` (`id_recetas`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Indexes for table `batches_enfriado`
--
ALTER TABLE `batches_enfriado`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batches_historial`
--
ALTER TABLE `batches_historial`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batches_insumos`
--
ALTER TABLE `batches_insumos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batches_lupulizacion`
--
ALTER TABLE `batches_lupulizacion`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batches_traspasos`
--
ALTER TABLE `batches_traspasos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cajas`
--
ALTER TABLE `cajas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cajas_de_envases`
--
ALTER TABLE `cajas_de_envases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_codigo` (`codigo`),
  ADD KEY `idx_id_productos` (`id_productos`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clientes_productos_precios`
--
ALTER TABLE `clientes_productos_precios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `compras_de_insumos`
--
ALTER TABLE `compras_de_insumos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `compras_de_insumos_insumos`
--
ALTER TABLE `compras_de_insumos_insumos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `configuraciones`
--
ALTER TABLE `configuraciones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversaciones_internas`
--
ALTER TABLE `conversaciones_internas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vista_entidad` (`nombre_vista`,`id_entidad`),
  ADD KEY `idx_propietario` (`id_propietario`),
  ADD KEY `idx_creado_por` (`id_creado_por`);

--
-- Indexes for table `conversaciones_internas_archivos`
--
ALTER TABLE `conversaciones_internas_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comentario` (`id_comentario`),
  ADD KEY `idx_subido_por` (`id_subido_por`);

--
-- Indexes for table `conversaciones_internas_comentarios`
--
ALTER TABLE `conversaciones_internas_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversacion` (`id_conversacion_interna`),
  ADD KEY `idx_autor` (`id_autor`),
  ADD KEY `idx_fecha_creacion` (`fecha_creacion`);

--
-- Indexes for table `conversaciones_internas_tags`
--
ALTER TABLE `conversaciones_internas_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_comment_user` (`id_comentario`,`id_usuario`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_creado_por` (`id_creado_por`);

--
-- Indexes for table `despachos`
--
ALTER TABLE `despachos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_clientes` (`id_clientes`);

--
-- Indexes for table `despachos_productos`
--
ALTER TABLE `despachos_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_cajas_de_envases` (`id_cajas_de_envases`);

--
-- Indexes for table `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dte`
--
ALTER TABLE `dte`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `entidades`
--
ALTER TABLE `entidades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `entregas_productos`
--
ALTER TABLE `entregas_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_cajas_de_envases` (`id_cajas_de_envases`);

--
-- Indexes for table `envases`
--
ALTER TABLE `envases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_batches_de_latas` (`id_batches_de_envases`),
  ADD KEY `idx_id_cajas_de_latas` (`id_cajas_de_envases`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_id_batches` (`id_batches`),
  ADD KEY `idx_id_formatos_de_latas` (`id_formatos_de_envases`);

--
-- Indexes for table `fermentadores`
--
ALTER TABLE `fermentadores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `formatos`
--
ALTER TABLE `formatos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `formatos_de_envases`
--
ALTER TABLE `formatos_de_envases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_volumen_ml` (`volumen_ml`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Indexes for table `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gastos_fijos`
--
ALTER TABLE `gastos_fijos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gastos_fijos_mes`
--
ALTER TABLE `gastos_fijos_mes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gastos_lineas_de_negocio`
--
ALTER TABLE `gastos_lineas_de_negocio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `insumos`
--
ALTER TABLE `insumos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kanban_columnas`
--
ALTER TABLE `kanban_columnas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kanban_tableros` (`id_kanban_tableros`),
  ADD KEY `orden` (`orden`);

--
-- Indexes for table `kanban_etiquetas`
--
ALTER TABLE `kanban_etiquetas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kanban_tableros`
--
ALTER TABLE `kanban_tableros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_entidad` (`id_entidad`);

--
-- Indexes for table `kanban_tableros_usuarios`
--
ALTER TABLE `kanban_tableros_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kanban_tableros` (`id_kanban_tableros`),
  ADD KEY `id_usuarios` (`id_usuarios`);

--
-- Indexes for table `kanban_tareas`
--
ALTER TABLE `kanban_tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kanban_columnas` (`id_kanban_columnas`),
  ADD KEY `orden` (`orden`),
  ADD KEY `estado` (`estado`),
  ADD KEY `fecha_vencimiento` (`fecha_vencimiento`);

--
-- Indexes for table `kanban_tareas_etiquetas`
--
ALTER TABLE `kanban_tareas_etiquetas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kanban_tareas` (`id_kanban_tareas`),
  ADD KEY `id_kanban_etiquetas` (`id_kanban_etiquetas`);

--
-- Indexes for table `kanban_tareas_usuarios`
--
ALTER TABLE `kanban_tareas_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kanban_tareas` (`id_kanban_tareas`),
  ADD KEY `id_usuarios` (`id_usuarios`);

--
-- Indexes for table `lineas_de_negocio`
--
ALTER TABLE `lineas_de_negocio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locaciones`
--
ALTER TABLE `locaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mailing`
--
ALTER TABLE `mailing`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mantenciones`
--
ALTER TABLE `mantenciones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media_activos`
--
ALTER TABLE `media_activos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media_configuraciones`
--
ALTER TABLE `media_configuraciones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media_documentos`
--
ALTER TABLE `media_documentos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media_gastos`
--
ALTER TABLE `media_gastos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media_gastos_fijos_mes`
--
ALTER TABLE `media_gastos_fijos_mes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media_kanban_tareas`
--
ALTER TABLE `media_kanban_tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_media` (`id_media`),
  ADD KEY `id_kanban_tareas` (`id_kanban_tareas`);

--
-- Indexes for table `media_mantenciones`
--
ALTER TABLE `media_mantenciones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notificaciones_usuarios_niveles`
--
ALTER TABLE `notificaciones_usuarios_niveles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pedidos_productos`
--
ALTER TABLE `pedidos_productos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_secciones` (`id_secciones`),
  ADD KEY `id_usuarios_niveles` (`id_usuarios_niveles`),
  ADD KEY `acceso` (`acceso`);

--
-- Indexes for table `precios`
--
ALTER TABLE `precios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prevlinks`
--
ALTER TABLE `prevlinks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prev` (`count`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_formatos_de_latas` (`id_formatos_de_envases`),
  ADD KEY `idx_tipo_envase` (`tipo_envase`),
  ADD KEY `idx_es_mixto` (`es_mixto`);

--
-- Indexes for table `productos_items`
--
ALTER TABLE `productos_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `proyectos_gastos`
--
ALTER TABLE `proyectos_gastos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `proyectos_ingresos`
--
ALTER TABLE `proyectos_ingresos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `proyectos_productos`
--
ALTER TABLE `proyectos_productos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recetas`
--
ALTER TABLE `recetas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recetas_insumos`
--
ALTER TABLE `recetas_insumos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registro_asistencia`
--
ALTER TABLE `registro_asistencia`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reportes_diarios`
--
ALTER TABLE `reportes_diarios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `return_urls`
--
ALTER TABLE `return_urls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `secciones`
--
ALTER TABLE `secciones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sugerencias`
--
ALTER TABLE `sugerencias`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tareas_comentarios`
--
ALTER TABLE `tareas_comentarios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tipos_de_gastos`
--
ALTER TABLE `tipos_de_gastos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tipos_de_insumos`
--
ALTER TABLE `tipos_de_insumos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tipos_de_notificaciones`
--
ALTER TABLE `tipos_de_notificaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tipos_de_pago`
--
ALTER TABLE `tipos_de_pago`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transacciones`
--
ALTER TABLE `transacciones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usuarios_clientes`
--
ALTER TABLE `usuarios_clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usuarios_niveles`
--
ALTER TABLE `usuarios_niveles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendedores`
--
ALTER TABLE `vendedores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `visitas`
--
ALTER TABLE `visitas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accesorios`
--
ALTER TABLE `accesorios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activos`
--
ALTER TABLE `activos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alertas`
--
ALTER TABLE `alertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `barriles`
--
ALTER TABLE `barriles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `barriles_estados`
--
ALTER TABLE `barriles_estados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `barriles_reemplazos`
--
ALTER TABLE `barriles_reemplazos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batches_2`
--
ALTER TABLE `batches_2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batches_activos`
--
ALTER TABLE `batches_activos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batches_barriles`
--
ALTER TABLE `batches_barriles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batches_cajas`
--
ALTER TABLE `batches_cajas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batches_de_envases`
--
ALTER TABLE `batches_de_envases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batches_enfriado`
--
ALTER TABLE `batches_enfriado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batches_historial`
--
ALTER TABLE `batches_historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batches_insumos`
--
ALTER TABLE `batches_insumos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batches_lupulizacion`
--
ALTER TABLE `batches_lupulizacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batches_traspasos`
--
ALTER TABLE `batches_traspasos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cajas`
--
ALTER TABLE `cajas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cajas_de_envases`
--
ALTER TABLE `cajas_de_envases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clientes_productos_precios`
--
ALTER TABLE `clientes_productos_precios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compras_de_insumos`
--
ALTER TABLE `compras_de_insumos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compras_de_insumos_insumos`
--
ALTER TABLE `compras_de_insumos_insumos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `configuraciones`
--
ALTER TABLE `configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversaciones_internas`
--
ALTER TABLE `conversaciones_internas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversaciones_internas_archivos`
--
ALTER TABLE `conversaciones_internas_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversaciones_internas_comentarios`
--
ALTER TABLE `conversaciones_internas_comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversaciones_internas_tags`
--
ALTER TABLE `conversaciones_internas_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `despachos`
--
ALTER TABLE `despachos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `despachos_productos`
--
ALTER TABLE `despachos_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dte`
--
ALTER TABLE `dte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entidades`
--
ALTER TABLE `entidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entregas`
--
ALTER TABLE `entregas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entregas_productos`
--
ALTER TABLE `entregas_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `envases`
--
ALTER TABLE `envases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fermentadores`
--
ALTER TABLE `fermentadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `formatos`
--
ALTER TABLE `formatos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `formatos_de_envases`
--
ALTER TABLE `formatos_de_envases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gastos_fijos`
--
ALTER TABLE `gastos_fijos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gastos_fijos_mes`
--
ALTER TABLE `gastos_fijos_mes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gastos_lineas_de_negocio`
--
ALTER TABLE `gastos_lineas_de_negocio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historial`
--
ALTER TABLE `historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `insumos`
--
ALTER TABLE `insumos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kanban_columnas`
--
ALTER TABLE `kanban_columnas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kanban_etiquetas`
--
ALTER TABLE `kanban_etiquetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kanban_tableros`
--
ALTER TABLE `kanban_tableros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kanban_tableros_usuarios`
--
ALTER TABLE `kanban_tableros_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kanban_tareas`
--
ALTER TABLE `kanban_tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kanban_tareas_etiquetas`
--
ALTER TABLE `kanban_tareas_etiquetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kanban_tareas_usuarios`
--
ALTER TABLE `kanban_tareas_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lineas_de_negocio`
--
ALTER TABLE `lineas_de_negocio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locaciones`
--
ALTER TABLE `locaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mailing`
--
ALTER TABLE `mailing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mantenciones`
--
ALTER TABLE `mantenciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_activos`
--
ALTER TABLE `media_activos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_configuraciones`
--
ALTER TABLE `media_configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_documentos`
--
ALTER TABLE `media_documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_gastos`
--
ALTER TABLE `media_gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_gastos_fijos_mes`
--
ALTER TABLE `media_gastos_fijos_mes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_kanban_tareas`
--
ALTER TABLE `media_kanban_tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_mantenciones`
--
ALTER TABLE `media_mantenciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notificaciones_usuarios_niveles`
--
ALTER TABLE `notificaciones_usuarios_niveles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pedidos_productos`
--
ALTER TABLE `pedidos_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `precios`
--
ALTER TABLE `precios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prevlinks`
--
ALTER TABLE `prevlinks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productos_items`
--
ALTER TABLE `productos_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proyectos_gastos`
--
ALTER TABLE `proyectos_gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proyectos_ingresos`
--
ALTER TABLE `proyectos_ingresos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proyectos_productos`
--
ALTER TABLE `proyectos_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recetas`
--
ALTER TABLE `recetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recetas_insumos`
--
ALTER TABLE `recetas_insumos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registro_asistencia`
--
ALTER TABLE `registro_asistencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reportes_diarios`
--
ALTER TABLE `reportes_diarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_urls`
--
ALTER TABLE `return_urls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `secciones`
--
ALTER TABLE `secciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sugerencias`
--
ALTER TABLE `sugerencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tareas_comentarios`
--
ALTER TABLE `tareas_comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipos_de_gastos`
--
ALTER TABLE `tipos_de_gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipos_de_insumos`
--
ALTER TABLE `tipos_de_insumos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipos_de_notificaciones`
--
ALTER TABLE `tipos_de_notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipos_de_pago`
--
ALTER TABLE `tipos_de_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transacciones`
--
ALTER TABLE `transacciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios_clientes`
--
ALTER TABLE `usuarios_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios_niveles`
--
ALTER TABLE `usuarios_niveles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendedores`
--
ALTER TABLE `vendedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visitas`
--
ALTER TABLE `visitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `conversaciones_internas_archivos`
--
ALTER TABLE `conversaciones_internas_archivos`
  ADD CONSTRAINT `fk_archivo_comentario` FOREIGN KEY (`id_comentario`) REFERENCES `conversaciones_internas_comentarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversaciones_internas_comentarios`
--
ALTER TABLE `conversaciones_internas_comentarios`
  ADD CONSTRAINT `fk_comentario_conversacion` FOREIGN KEY (`id_conversacion_interna`) REFERENCES `conversaciones_internas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversaciones_internas_tags`
--
ALTER TABLE `conversaciones_internas_tags`
  ADD CONSTRAINT `fk_tag_comentario` FOREIGN KEY (`id_comentario`) REFERENCES `conversaciones_internas_comentarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kanban_columnas`
--
ALTER TABLE `kanban_columnas`
  ADD CONSTRAINT `fk_kanban_columnas_tableros` FOREIGN KEY (`id_kanban_tableros`) REFERENCES `kanban_tableros` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kanban_tareas`
--
ALTER TABLE `kanban_tareas`
  ADD CONSTRAINT `fk_kanban_tareas_columnas` FOREIGN KEY (`id_kanban_columnas`) REFERENCES `kanban_columnas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kanban_tareas_etiquetas`
--
ALTER TABLE `kanban_tareas_etiquetas`
  ADD CONSTRAINT `fk_kanban_tareas_etiquetas_etiquetas` FOREIGN KEY (`id_kanban_etiquetas`) REFERENCES `kanban_etiquetas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_kanban_tareas_etiquetas_tareas` FOREIGN KEY (`id_kanban_tareas`) REFERENCES `kanban_tareas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kanban_tareas_usuarios`
--
ALTER TABLE `kanban_tareas_usuarios`
  ADD CONSTRAINT `fk_kanban_tareas_usuarios_tareas` FOREIGN KEY (`id_kanban_tareas`) REFERENCES `kanban_tareas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_kanban_tareas_usuarios_usuarios` FOREIGN KEY (`id_usuarios`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_kanban_tareas`
--
ALTER TABLE `media_kanban_tareas`
  ADD CONSTRAINT `fk_media_kanban_tareas_media` FOREIGN KEY (`id_media`) REFERENCES `media` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_media_kanban_tareas_tareas` FOREIGN KEY (`id_kanban_tareas`) REFERENCES `kanban_tareas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
