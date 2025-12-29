-- Migration: 005_modify_turnos_facturas_credito.sql
-- Description: Simplify facturas_credito to numero_factura, monto, id_clientes
-- Date: 2025-12-29

-- Add client foreign key
ALTER TABLE `turnos_facturas_credito`
  ADD COLUMN `id_clientes` INT(11) DEFAULT NULL AFTER `id_turnos`;

-- Add index for cliente
ALTER TABLE `turnos_facturas_credito` ADD INDEX `idx_facturas_cliente` (`id_clientes`);

-- Remove unused columns
ALTER TABLE `turnos_facturas_credito` DROP COLUMN IF EXISTS `rut_cliente`;
ALTER TABLE `turnos_facturas_credito` DROP COLUMN IF EXISTS `nombre_cliente`;
ALTER TABLE `turnos_facturas_credito` DROP COLUMN IF EXISTS `fecha_vencimiento`;
ALTER TABLE `turnos_facturas_credito` DROP COLUMN IF EXISTS `descripcion`;
ALTER TABLE `turnos_facturas_credito` DROP COLUMN IF EXISTS `observaciones`;
ALTER TABLE `turnos_facturas_credito` DROP COLUMN IF EXISTS `pagada_fecha`;

-- Drop old indexes if exist
ALTER TABLE `turnos_facturas_credito` DROP INDEX IF EXISTS `idx_facturas_vencimiento`;
ALTER TABLE `turnos_facturas_credito` DROP INDEX IF EXISTS `idx_facturas_rut`;
