-- Migration: 003_modify_turnos_remove_fields.sql
-- Description: Remove hora_inicio, hora_fin, id_atendedores from turnos
-- Date: 2025-12-29

-- Remove unused time fields and atendedor link from main turnos table
ALTER TABLE `turnos` DROP COLUMN IF EXISTS `hora_inicio`;
ALTER TABLE `turnos` DROP COLUMN IF EXISTS `hora_fin`;
ALTER TABLE `turnos` DROP COLUMN IF EXISTS `id_atendedores`;

-- Drop the index if exists
ALTER TABLE `turnos` DROP INDEX IF EXISTS `idx_turnos_atendedor`;
