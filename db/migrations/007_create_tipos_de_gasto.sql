-- Migration: 007_create_tipos_de_gasto.sql
-- Description: Create tipos_de_gasto table for self-administrable expense types
-- Date: 2025-12-29

CREATE TABLE IF NOT EXISTS `tipos_de_gasto` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  `estado` VARCHAR(50) DEFAULT 'activo',
  `creada` DATETIME DEFAULT NULL,
  `actualizada` DATETIME DEFAULT NULL,

  PRIMARY KEY (`id`),
  INDEX `idx_tipos_gasto_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default expense types
INSERT INTO `tipos_de_gasto` (`nombre`, `estado`, `creada`) VALUES
('Limpieza', 'activo', NOW()),
('Oficina', 'activo', NOW()),
('Mantenimiento', 'activo', NOW()),
('Transporte', 'activo', NOW()),
('Alimentacion', 'activo', NOW()),
('Otros', 'activo', NOW());
