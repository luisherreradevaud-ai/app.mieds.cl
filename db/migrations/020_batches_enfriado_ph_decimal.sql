-- =====================================================
-- Migración 020: Campos pH de enfriado a DECIMAL
-- Fecha: 2025-12-23
-- Descripción: Cambia los campos ph y ph_enfriado de
--              INT a DECIMAL(4,2) para soportar decimales
-- =====================================================

ALTER TABLE `batches_enfriado`
  MODIFY COLUMN `ph` DECIMAL(4,2) DEFAULT NULL COMMENT 'pH inicial',
  MODIFY COLUMN `ph_enfriado` DECIMAL(4,2) DEFAULT NULL COMMENT 'pH después del enfriado';
