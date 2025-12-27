-- Migration: Create atendedores table
-- Date: 2025-12-27

CREATE TABLE `atendedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rut` varchar(12) NOT NULL,
  `nombre_completo` varchar(200) NOT NULL,
  `genero` varchar(20) NOT NULL,
  `estado_civil` varchar(30) NOT NULL,
  `direccion` varchar(300) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `nacionalidad` varchar(50) NOT NULL,
  `jornada_trabajo` varchar(50) NOT NULL,
  `cargo_copec` varchar(100) NOT NULL,
  `rol_mae` varchar(50) NOT NULL,
  `id_tarjeta_copec` varchar(14) NOT NULL COMMENT '14 caracteres alfanuméricos',
  `id_mae` varchar(4) NOT NULL COMMENT '4 dígitos',
  `clave_mae` varchar(4) NOT NULL COMMENT '4 dígitos',
  `estado` varchar(50) NOT NULL DEFAULT 'Activo',
  `creada` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizada` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rut` (`rut`),
  KEY `idx_id_tarjeta_copec` (`id_tarjeta_copec`),
  KEY `idx_id_mae` (`id_mae`),
  KEY `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
