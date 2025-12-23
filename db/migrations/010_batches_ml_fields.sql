-- =====================================================
-- Migración 010: Campos ML para Batches
-- Fecha: 2025-12-04
-- Descripción: Agrega campos para análisis de Machine
--              Learning y métricas de calidad
-- =====================================================

-- Métricas de producto final
ALTER TABLE batches
ADD COLUMN abv_final DECIMAL(4,2) DEFAULT NULL
  COMMENT 'Alcohol by volume final (%)',
ADD COLUMN ibu_final INT DEFAULT NULL
  COMMENT 'IBU medido del producto final',
ADD COLUMN color_ebc INT DEFAULT NULL
  COMMENT 'Color en escala EBC';

-- Métricas de rendimiento
ALTER TABLE batches
ADD COLUMN rendimiento_litros_final FLOAT DEFAULT NULL
  COMMENT 'Volumen final real producido (L)',
ADD COLUMN merma_total_litros FLOAT DEFAULT NULL
  COMMENT 'Total de pérdida en proceso (L)',
ADD COLUMN densidad_final_verificada DECIMAL(5,3) DEFAULT NULL
  COMMENT 'Gravedad final verificada';

-- Métricas de calidad sensorial
ALTER TABLE batches
ADD COLUMN calificacion_sensorial TINYINT DEFAULT NULL
  COMMENT 'Calificación sensorial 1-10',
ADD COLUMN notas_cata TEXT DEFAULT NULL
  COMMENT 'Notas de cata descriptivas';

-- Condiciones ambientales
ALTER TABLE batches
ADD COLUMN temperatura_ambiente_promedio DECIMAL(4,1) DEFAULT NULL
  COMMENT 'Temperatura ambiente durante fermentación (°C)',
ADD COLUMN humedad_relativa_promedio DECIMAL(4,1) DEFAULT NULL
  COMMENT 'Humedad relativa promedio (%)';

-- Índices para consultas analíticas
CREATE INDEX idx_batches_abv ON batches(abv_final);
CREATE INDEX idx_batches_calificacion ON batches(calificacion_sensorial);
CREATE INDEX idx_batches_rendimiento ON batches(rendimiento_litros_final);
