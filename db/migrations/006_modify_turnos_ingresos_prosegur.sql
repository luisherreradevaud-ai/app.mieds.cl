-- Migration: 006_modify_turnos_ingresos_prosegur.sql
-- Description: Simplify ingresos_prosegur to id_atendedores and monto only
-- Date: 2025-12-29

-- Add atendedor column
ALTER TABLE `turnos_ingresos_prosegur`
  ADD COLUMN `id_atendedores` INT(11) DEFAULT NULL AFTER `id_turnos`;

-- Add index for atendedor
ALTER TABLE `turnos_ingresos_prosegur` ADD INDEX `idx_prosegur_atendedor` (`id_atendedores`);

-- Remove unused columns
ALTER TABLE `turnos_ingresos_prosegur` DROP COLUMN IF EXISTS `numero_boleta`;
ALTER TABLE `turnos_ingresos_prosegur` DROP COLUMN IF EXISTS `fecha_ingreso`;
ALTER TABLE `turnos_ingresos_prosegur` DROP COLUMN IF EXISTS `hora_ingreso`;
ALTER TABLE `turnos_ingresos_prosegur` DROP COLUMN IF EXISTS `descripcion`;
ALTER TABLE `turnos_ingresos_prosegur` DROP COLUMN IF EXISTS `observaciones`;

-- Drop old indexes if exist
ALTER TABLE `turnos_ingresos_prosegur` DROP INDEX IF EXISTS `idx_prosegur_fecha`;
