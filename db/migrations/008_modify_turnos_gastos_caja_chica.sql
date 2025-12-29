-- Migration: 008_modify_turnos_gastos_caja_chica.sql
-- Description: Simplify gastos_caja_chica to id_tipos_de_gasto, monto, descripcion
-- Date: 2025-12-29

-- Add tipo de gasto foreign key
ALTER TABLE `turnos_gastos_caja_chica`
  ADD COLUMN `id_tipos_de_gasto` INT(11) DEFAULT NULL AFTER `id_turnos`;

-- Add index for tipo de gasto
ALTER TABLE `turnos_gastos_caja_chica` ADD INDEX `idx_gastos_tipo_gasto` (`id_tipos_de_gasto`);

-- Remove unused columns
ALTER TABLE `turnos_gastos_caja_chica` DROP COLUMN IF EXISTS `tipo`;
ALTER TABLE `turnos_gastos_caja_chica` DROP COLUMN IF EXISTS `categoria`;
ALTER TABLE `turnos_gastos_caja_chica` DROP COLUMN IF EXISTS `numero_documento`;
ALTER TABLE `turnos_gastos_caja_chica` DROP COLUMN IF EXISTS `fecha_documento`;
ALTER TABLE `turnos_gastos_caja_chica` DROP COLUMN IF EXISTS `proveedor`;
ALTER TABLE `turnos_gastos_caja_chica` DROP COLUMN IF EXISTS `observaciones`;

-- Drop old indexes if exist
ALTER TABLE `turnos_gastos_caja_chica` DROP INDEX IF EXISTS `idx_gastos_tipo`;
ALTER TABLE `turnos_gastos_caja_chica` DROP INDEX IF EXISTS `idx_gastos_categoria`;
