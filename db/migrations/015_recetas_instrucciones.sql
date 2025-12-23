-- =====================================================
-- Migración 015: Sistema de instrucciones de receta
-- Fecha: 2025-12-13
-- Descripción: Tabla de pasos de receta, campos objetivo
--              y campos de etapa en insumos de receta
-- =====================================================

-- Campos objetivo en recetas
ALTER TABLE recetas
ADD COLUMN abv_objetivo DECIMAL(4,2) DEFAULT NULL
  COMMENT 'ABV objetivo (%)',
ADD COLUMN ibu_objetivo INT DEFAULT NULL
  COMMENT 'IBU objetivo',
ADD COLUMN color_ebc_objetivo INT DEFAULT NULL
  COMMENT 'Color EBC objetivo',
ADD COLUMN og_objetivo DECIMAL(5,3) DEFAULT NULL
  COMMENT 'Densidad original objetivo',
ADD COLUMN fg_objetivo DECIMAL(5,3) DEFAULT NULL
  COMMENT 'Densidad final objetivo',
ADD COLUMN tiempo_fermentacion_dias INT DEFAULT NULL
  COMMENT 'Tiempo de fermentación en días',
ADD COLUMN tiempo_maduracion_dias INT DEFAULT NULL
  COMMENT 'Tiempo de maduración en días',
ADD COLUMN instrucciones_generales TEXT DEFAULT NULL
  COMMENT 'Instrucciones generales de la receta';

-- Tabla de pasos de receta (instrucciones)
CREATE TABLE IF NOT EXISTS recetas_pasos (
  id VARCHAR(36) PRIMARY KEY,
  id_recetas VARCHAR(36) NOT NULL COMMENT 'FK a recetas.id',
  etapa ENUM(
    'preparacion',
    'licor',
    'maceracion',
    'lavado',
    'coccion',
    'lupulizacion',
    'enfriado',
    'inoculacion',
    'fermentacion',
    'maduracion',
    'traspasos',
    'envasado'
  ) NOT NULL DEFAULT 'preparacion',
  orden INT NOT NULL DEFAULT 1 COMMENT 'Orden del paso dentro de la etapa',
  titulo VARCHAR(200) NOT NULL COMMENT 'Título corto del paso',
  instruccion TEXT NOT NULL COMMENT 'Instrucciones detalladas',
  duracion_minutos INT DEFAULT NULL COMMENT 'Duración estimada en minutos',
  temperatura_objetivo DECIMAL(5,2) DEFAULT NULL COMMENT 'Temperatura objetivo en °C',
  ph_objetivo DECIMAL(3,2) DEFAULT NULL COMMENT 'pH objetivo',
  densidad_objetivo DECIMAL(5,3) DEFAULT NULL COMMENT 'Densidad objetivo',
  notas TEXT DEFAULT NULL COMMENT 'Notas adicionales',
  creada DATETIME DEFAULT CURRENT_TIMESTAMP,
  estado VARCHAR(20) DEFAULT 'activo',
  INDEX idx_recetas_pasos_receta (id_recetas),
  INDEX idx_recetas_pasos_etapa (etapa),
  INDEX idx_recetas_pasos_orden (id_recetas, etapa, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Pasos de instrucciones para cada receta';

-- Campos adicionales en recetas_insumos para asignar a etapas
ALTER TABLE recetas_insumos
ADD COLUMN etapa ENUM(
    'preparacion',
    'licor',
    'maceracion',
    'lavado',
    'coccion',
    'lupulizacion',
    'enfriado',
    'inoculacion',
    'fermentacion',
    'maduracion',
    'traspasos',
    'envasado',
    'general'
  ) DEFAULT 'general' COMMENT 'Etapa donde se usa el insumo',
ADD COLUMN orden INT DEFAULT 1 COMMENT 'Orden de uso en la etapa',
ADD COLUMN momento VARCHAR(100) DEFAULT NULL COMMENT 'Momento específico (ej: minuto 60)',
ADD COLUMN instruccion_uso TEXT DEFAULT NULL COMMENT 'Instrucciones de cómo usar el insumo';

-- Índices para consultas
CREATE INDEX idx_recetas_insumos_etapa ON recetas_insumos(etapa);
