-- Migration: 009_create_turnos_donaciones.sql
-- Description: Create separate table for donations (separate from gastos caja chica)
-- Date: 2025-12-29

CREATE TABLE IF NOT EXISTS `turnos_donaciones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_turnos` INT(11) NOT NULL,
  `id_atendedores` INT(11) DEFAULT NULL,
  `monto` DECIMAL(15,2) DEFAULT 0,
  `descripcion` VARCHAR(500) DEFAULT NULL,
  `estado` VARCHAR(50) DEFAULT 'activo',
  `creada` DATETIME DEFAULT NULL,
  `actualizada` DATETIME DEFAULT NULL,
  `creado_por` INT(11) DEFAULT NULL,

  PRIMARY KEY (`id`),
  INDEX `idx_donaciones_turno` (`id_turnos`),
  INDEX `idx_donaciones_atendedor` (`id_atendedores`),
  INDEX `idx_donaciones_estado` (`estado`),
  CONSTRAINT `fk_donaciones_turno` FOREIGN KEY (`id_turnos`)
    REFERENCES `turnos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add total_donaciones to turnos if not exists
ALTER TABLE `turnos` ADD COLUMN IF NOT EXISTS `total_donaciones` DECIMAL(15,2) DEFAULT 0 AFTER `total_gastos_caja_chica`;
