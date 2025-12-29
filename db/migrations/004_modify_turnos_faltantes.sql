-- Migration: 004_modify_turnos_faltantes.sql
-- Description: Modify turnos_faltantes to have atendedor, monto, motivo
-- Date: 2025-12-29

-- Add new columns
ALTER TABLE `turnos_faltantes`
  ADD COLUMN `id_atendedores` INT(11) DEFAULT NULL AFTER `id_turnos`,
  ADD COLUMN `motivo` VARCHAR(255) DEFAULT NULL AFTER `monto`;

-- Add index for atendedor
ALTER TABLE `turnos_faltantes` ADD INDEX `idx_faltantes_atendedor` (`id_atendedores`);

-- Remove unused columns
ALTER TABLE `turnos_faltantes` DROP COLUMN IF EXISTS `tipo`;
ALTER TABLE `turnos_faltantes` DROP COLUMN IF EXISTS `codigo_producto`;
ALTER TABLE `turnos_faltantes` DROP COLUMN IF EXISTS `cantidad`;
