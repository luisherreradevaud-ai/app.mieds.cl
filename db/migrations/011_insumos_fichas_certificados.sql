-- =====================================================
-- Migración 011: Fichas técnicas y certificados Halal
-- Fecha: 2025-12-04
-- Descripción: Agrega soporte para documentación de
--              insumos incluyendo certificación Halal
-- =====================================================

-- URLs de documentación
ALTER TABLE insumos
ADD COLUMN url_ficha_tecnica VARCHAR(500) DEFAULT NULL
  COMMENT 'URL a PDF de ficha técnica',
ADD COLUMN url_certificado_halal VARCHAR(500) DEFAULT NULL
  COMMENT 'URL a certificado Halal';

-- Datos del certificado Halal
ALTER TABLE insumos
ADD COLUMN certificado_halal_numero VARCHAR(100) DEFAULT NULL
  COMMENT 'Número de certificado Halal',
ADD COLUMN certificado_halal_vencimiento DATE DEFAULT NULL
  COMMENT 'Fecha de vencimiento del certificado',
ADD COLUMN certificado_halal_emisor VARCHAR(200) DEFAULT NULL
  COMMENT 'Entidad certificadora Halal',
ADD COLUMN es_halal_certificado TINYINT(1) NOT NULL DEFAULT 0
  COMMENT 'Flag: insumo tiene certificación Halal vigente';

-- Índice para filtrar insumos Halal
CREATE INDEX idx_insumos_halal ON insumos(es_halal_certificado);
CREATE INDEX idx_insumos_halal_vencimiento ON insumos(certificado_halal_vencimiento);
