-- =====================================================
-- Migración 013: Sistema de archivos para insumos
-- Fecha: 2025-12-13
-- Descripción: Tabla media_insumos para archivos adjuntos
--              y campos de proveedor en insumos
-- =====================================================

-- Tabla de relación muchos-a-muchos entre insumos y media
CREATE TABLE IF NOT EXISTS media_insumos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_insumos VARCHAR(36) NOT NULL COMMENT 'FK a insumos.id',
  id_media VARCHAR(36) NOT NULL COMMENT 'FK a media.id',
  tipo ENUM('ficha_tecnica','certificado_halal','certificado_otro','imagen','documento') DEFAULT 'documento'
    COMMENT 'Tipo de archivo adjunto',
  descripcion VARCHAR(255) DEFAULT NULL COMMENT 'Descripción opcional del archivo',
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_media_insumos_insumo (id_insumos),
  INDEX idx_media_insumos_media (id_media),
  INDEX idx_media_insumos_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Archivos adjuntos de insumos (fichas, certificados, etc)';

-- Campos de proveedor en tabla insumos
ALTER TABLE insumos
ADD COLUMN proveedor VARCHAR(200) DEFAULT NULL
  COMMENT 'Nombre del proveedor',
ADD COLUMN codigo_proveedor VARCHAR(100) DEFAULT NULL
  COMMENT 'Código interno del proveedor',
ADD COLUMN pais_origen VARCHAR(100) DEFAULT NULL
  COMMENT 'País de origen del insumo';

-- Índice para búsqueda por proveedor
CREATE INDEX idx_insumos_proveedor ON insumos(proveedor);
