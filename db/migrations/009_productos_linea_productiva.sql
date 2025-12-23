-- =====================================================
-- Migración 009: Agregar línea productiva a productos
-- Fecha: 2025-12-04
-- Descripción: Permite categorizar productos por línea
--              de producción (alcohólica/sin alcohol)
-- =====================================================

-- Agregar campo linea_productiva
ALTER TABLE productos
ADD COLUMN linea_productiva ENUM('alcoholica', 'analcoholica', 'general')
NOT NULL DEFAULT 'general'
AFTER es_mixto;

-- Índice para consultas por línea
CREATE INDEX idx_productos_linea_productiva ON productos(linea_productiva);

-- Actualizar productos existentes según clasificación
UPDATE productos
SET linea_productiva = 'alcoholica'
WHERE clasificacion IN ('Cerveza', 'Cerveza Artesanal');

UPDATE productos
SET linea_productiva = 'analcoholica'
WHERE clasificacion IN ('Kombucha', 'Agua saborizada', 'Agua fermentada');
