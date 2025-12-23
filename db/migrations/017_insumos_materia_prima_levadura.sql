-- =====================================================
-- Migración 017: Campos MateriaPrima y Levadura para Insumos
-- Fecha: 2025-12-13
-- Descripción: Agrega campos de identificación de materia prima
--              y campos específicos para levaduras
-- =====================================================

-- Campos de Materia Prima (identificación y logística)
ALTER TABLE insumos
ADD COLUMN nombre_comercial VARCHAR(200) DEFAULT ''
  COMMENT 'Nombre comercial del producto'
  AFTER url_certificado_halal;

ALTER TABLE insumos
ADD COLUMN marca VARCHAR(100) DEFAULT ''
  COMMENT 'Marca del producto'
  AFTER nombre_comercial;

ALTER TABLE insumos
ADD COLUMN materia_prima_basica VARCHAR(200) DEFAULT ''
  COMMENT 'Materia prima base (ej: cebada, lúpulo, agua)'
  AFTER marca;

ALTER TABLE insumos
ADD COLUMN cosecha_anio YEAR DEFAULT NULL
  COMMENT 'Año de cosecha (para maltas, lúpulos)'
  AFTER materia_prima_basica;

ALTER TABLE insumos
ADD COLUMN presentacion VARCHAR(100) DEFAULT ''
  COMMENT 'Presentación del producto (ej: Saco 25kg, Bolsa 1kg)'
  AFTER cosecha_anio;

ALTER TABLE insumos
ADD COLUMN vida_util_meses INT DEFAULT NULL
  COMMENT 'Vida útil en meses desde fecha de producción'
  AFTER presentacion;

-- Campos específicos de Levadura
ALTER TABLE insumos
ADD COLUMN es_levadura TINYINT(1) DEFAULT 0
  COMMENT 'Indica si el insumo es una levadura'
  AFTER vida_util_meses;

ALTER TABLE insumos
ADD COLUMN cepa VARCHAR(100) DEFAULT ''
  COMMENT 'Cepa de la levadura (ej: Saccharomyces cerevisiae)'
  AFTER es_levadura;

ALTER TABLE insumos
ADD COLUMN tipo_levadura ENUM('ale_seca', 'ale_liquida', 'lager_seca', 'lager_liquida', 'wild', 'otro') DEFAULT NULL
  COMMENT 'Tipo de levadura'
  AFTER cepa;

ALTER TABLE insumos
ADD COLUMN atenuacion_min DECIMAL(5,2) DEFAULT NULL
  COMMENT 'Atenuación mínima (%)'
  AFTER tipo_levadura;

ALTER TABLE insumos
ADD COLUMN atenuacion_max DECIMAL(5,2) DEFAULT NULL
  COMMENT 'Atenuación máxima (%)'
  AFTER atenuacion_min;

ALTER TABLE insumos
ADD COLUMN floculacion ENUM('baja', 'media', 'alta', 'muy_alta') DEFAULT NULL
  COMMENT 'Nivel de floculación'
  AFTER atenuacion_max;

ALTER TABLE insumos
ADD COLUMN temp_fermentacion_min DECIMAL(4,1) DEFAULT NULL
  COMMENT 'Temperatura mínima de fermentación (°C)'
  AFTER floculacion;

ALTER TABLE insumos
ADD COLUMN temp_fermentacion_max DECIMAL(4,1) DEFAULT NULL
  COMMENT 'Temperatura máxima de fermentación (°C)'
  AFTER temp_fermentacion_min;

ALTER TABLE insumos
ADD COLUMN tolerancia_alcohol DECIMAL(4,1) DEFAULT NULL
  COMMENT 'Tolerancia máxima al alcohol (%)'
  AFTER temp_fermentacion_max;

-- Índice para búsqueda de levaduras
CREATE INDEX idx_insumos_es_levadura ON insumos(es_levadura);

-- Índice para búsqueda por marca
CREATE INDEX idx_insumos_marca ON insumos(marca);

