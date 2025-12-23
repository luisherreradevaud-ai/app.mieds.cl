-- =====================================================
-- Migración 016: Ampliación Modelo ML/PLC
-- Fecha: 2025-12-13
-- Descripción: Extensión del modelo de datos para
--              integración con Machine Learning y PLC
-- =====================================================

-- =====================================================
-- 1. AMPLIAR TABLA batches (campos de cocción)
-- =====================================================
ALTER TABLE batches
  ADD COLUMN tiempo_hervido_total_min INT DEFAULT NULL
    COMMENT 'Tiempo total de hervido en minutos',
  ADD COLUMN densidad_pre_hervor FLOAT DEFAULT NULL
    COMMENT 'Densidad antes de hervir',
  ADD COLUMN densidad_pre_hervor_unidad ENUM('SG','Plato','Brix') DEFAULT 'SG',
  ADD COLUMN densidad_post_hervor FLOAT DEFAULT NULL
    COMMENT 'Densidad después de hervir',
  ADD COLUMN densidad_post_hervor_unidad ENUM('SG','Plato','Brix') DEFAULT 'SG',
  ADD COLUMN volumen_pre_hervor_l FLOAT DEFAULT NULL
    COMMENT 'Volumen antes de hervir (litros)',
  ADD COLUMN volumen_post_hervor_l FLOAT DEFAULT NULL
    COMMENT 'Volumen después de hervir (litros)',
  ADD COLUMN evaporacion_pct FLOAT DEFAULT NULL
    COMMENT 'Porcentaje de evaporación',
  ADD COLUMN eficiencia_coccion_pct FLOAT DEFAULT NULL
    COMMENT 'Eficiencia de cocción (%)',
  ADD COLUMN energia_coccion_kwh FLOAT DEFAULT NULL
    COMMENT 'Energía consumida en cocción (kWh)',
  ADD COLUMN sensor_temp_id VARCHAR(50) DEFAULT NULL
    COMMENT 'ID del sensor de temperatura asociado',
  ADD COLUMN resumen_json JSON DEFAULT NULL
    COMMENT 'Resumen estructurado del batch para ML';

-- =====================================================
-- 2. AMPLIAR TABLA batches_enfriado
-- =====================================================
ALTER TABLE batches_enfriado
  ADD COLUMN do_ppm_inicial FLOAT DEFAULT NULL
    COMMENT 'Oxígeno disuelto inicial (ppm)',
  ADD COLUMN do_ppm_final FLOAT DEFAULT NULL
    COMMENT 'Oxígeno disuelto final (ppm)',
  ADD COLUMN caudal_mosto_lpm FLOAT DEFAULT NULL
    COMMENT 'Caudal de mosto (litros/min)',
  ADD COLUMN caudal_agua_lpm FLOAT DEFAULT NULL
    COMMENT 'Caudal de agua de enfriamiento (litros/min)',
  ADD COLUMN temperatura_agua_entrada FLOAT DEFAULT NULL
    COMMENT 'Temperatura del agua a la entrada (°C)',
  ADD COLUMN temperatura_agua_salida FLOAT DEFAULT NULL
    COMMENT 'Temperatura del agua a la salida (°C)',
  ADD COLUMN delta_temp FLOAT DEFAULT NULL
    COMMENT 'Diferencia de temperatura (°C)';

-- =====================================================
-- 3. AMPLIAR TABLA registros_limpiezas (CIP técnico)
-- =====================================================
ALTER TABLE registros_limpiezas
  ADD COLUMN volumen_solucion_l FLOAT DEFAULT NULL
    COMMENT 'Volumen de solución utilizado (litros)',
  ADD COLUMN temperatura_solucion FLOAT DEFAULT NULL
    COMMENT 'Temperatura de la solución de limpieza (°C)',
  ADD COLUMN tiempo_contacto_min INT DEFAULT NULL
    COMMENT 'Tiempo de contacto (minutos)',
  ADD COLUMN caudal_lpm FLOAT DEFAULT NULL
    COMMENT 'Caudal de recirculación (litros/min)',
  ADD COLUMN conductividad_inicial FLOAT DEFAULT NULL
    COMMENT 'Conductividad inicial',
  ADD COLUMN conductividad_final FLOAT DEFAULT NULL
    COMMENT 'Conductividad final',
  ADD COLUMN ph_final FLOAT DEFAULT NULL
    COMMENT 'pH de la solución al finalizar',
  ADD COLUMN ciclos_enjuague INT DEFAULT NULL
    COMMENT 'Número de ciclos de enjuague',
  ADD COLUMN validado_por VARCHAR(36) DEFAULT NULL
    COMMENT 'Usuario que validó la limpieza',
  ADD COLUMN validado_fecha DATETIME DEFAULT NULL
    COMMENT 'Fecha de validación';

-- =====================================================
-- 4. AMPLIAR TABLA insumos (MateriaPrima + Levadura)
-- =====================================================
ALTER TABLE insumos
  ADD COLUMN es_levadura TINYINT(1) DEFAULT 0
    COMMENT 'Flag: es insumo tipo levadura',
  ADD COLUMN cepa VARCHAR(100) DEFAULT NULL
    COMMENT 'Cepa de levadura (ej: Safale US-05)',
  ADD COLUMN tipo_levadura ENUM('ale_seca','ale_liquida','lager_seca','lager_liquida','wild') DEFAULT NULL,
  ADD COLUMN atenuacion_min FLOAT DEFAULT NULL
    COMMENT 'Atenuación mínima (%)',
  ADD COLUMN atenuacion_max FLOAT DEFAULT NULL
    COMMENT 'Atenuación máxima (%)',
  ADD COLUMN floculacion ENUM('baja','media','alta','muy_alta') DEFAULT NULL,
  ADD COLUMN temp_fermentacion_min FLOAT DEFAULT NULL
    COMMENT 'Temperatura mínima de fermentación (°C)',
  ADD COLUMN temp_fermentacion_max FLOAT DEFAULT NULL
    COMMENT 'Temperatura máxima de fermentación (°C)',
  ADD COLUMN tolerancia_alcohol FLOAT DEFAULT NULL
    COMMENT 'Tolerancia máxima de alcohol (%)';

-- Índice para levaduras
CREATE INDEX idx_insumos_levadura ON insumos(es_levadura);

-- =====================================================
-- 5. CREAR TABLA batch_signals (Series Temporales)
-- =====================================================
CREATE TABLE IF NOT EXISTS batch_signals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_batches VARCHAR(36) NOT NULL COMMENT 'FK a batches.id',
  etapa ENUM('Maceracion','Lavado','Coccion','Enfriado','Fermentacion','Maduracion','Envasado','CIP') NOT NULL
    COMMENT 'Etapa del proceso',
  variable VARCHAR(50) NOT NULL
    COMMENT 'Nombre de la variable (temperatura, presion, caudal, etc)',
  timestamp DATETIME NOT NULL
    COMMENT 'Momento de la medición',
  valor FLOAT NOT NULL
    COMMENT 'Valor medido',
  unidad VARCHAR(20) DEFAULT NULL
    COMMENT 'Unidad de medida',
  sensor_id VARCHAR(50) DEFAULT NULL
    COMMENT 'Identificador del sensor/PLC',
  INDEX idx_batch_signals_batch (id_batches),
  INDEX idx_batch_signals_etapa (id_batches, etapa),
  INDEX idx_batch_signals_timestamp (timestamp),
  INDEX idx_batch_signals_variable (id_batches, variable)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Series temporales de señales de proceso desde sensores/PLC';

-- =====================================================
-- 6. CREAR TABLA batch_levaduras
-- =====================================================
CREATE TABLE IF NOT EXISTS batch_levaduras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_batches VARCHAR(36) NOT NULL COMMENT 'FK a batches.id',
  id_batches_insumos INT DEFAULT NULL COMMENT 'FK a batches_insumos.id',
  generacion INT DEFAULT 1 COMMENT 'Generación de la levadura',
  origen_batch VARCHAR(36) DEFAULT NULL COMMENT 'Batch de origen si es reutilizada',
  cantidad_gramos FLOAT DEFAULT NULL COMMENT 'Cantidad en gramos',
  tasa_inoculacion FLOAT DEFAULT NULL COMMENT 'Tasa de inoculación (millones/ml/°P)',
  viabilidad_medida FLOAT DEFAULT NULL COMMENT 'Viabilidad medida (%)',
  vitalidad_medida FLOAT DEFAULT NULL COMMENT 'Vitalidad medida',
  uso_starter TINYINT(1) DEFAULT 0 COMMENT 'Se usó starter',
  volumen_starter_ml INT DEFAULT NULL COMMENT 'Volumen del starter (ml)',
  atenuacion_real FLOAT DEFAULT NULL COMMENT 'Atenuación real obtenida (%)',
  tiempo_lag_h FLOAT DEFAULT NULL COMMENT 'Tiempo de lag (horas)',
  observaciones TEXT DEFAULT NULL,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_batch_levaduras_batch (id_batches),
  INDEX idx_batch_levaduras_generacion (generacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Datos específicos de uso de levadura por batch';

-- =====================================================
-- 7. CREAR TABLA batch_analiticas (QC Multi-etapa)
-- =====================================================
CREATE TABLE IF NOT EXISTS batch_analiticas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_batches VARCHAR(36) NOT NULL COMMENT 'FK a batches.id',
  momento ENUM('PreMaceracion','PreBoil','PostBoil','PreFermentacion',
               'MidFermentacion','PreEnvasado','PostEnvasado') NOT NULL
    COMMENT 'Momento de la medición',
  densidad FLOAT DEFAULT NULL,
  densidad_unidad ENUM('SG','Plato','Brix') DEFAULT 'SG',
  ph FLOAT DEFAULT NULL,
  co2_disuelto FLOAT DEFAULT NULL COMMENT 'CO2 disuelto (g/L)',
  do_ppm FLOAT DEFAULT NULL COMMENT 'Oxígeno disuelto (ppm)',
  turbidez_ntu FLOAT DEFAULT NULL COMMENT 'Turbidez (NTU)',
  color_ebc INT DEFAULT NULL COMMENT 'Color (EBC)',
  amargor_ibu INT DEFAULT NULL COMMENT 'Amargor (IBU)',
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  analista VARCHAR(100) DEFAULT NULL COMMENT 'Nombre del analista',
  observaciones TEXT DEFAULT NULL,
  INDEX idx_batch_analiticas_batch (id_batches),
  INDEX idx_batch_analiticas_momento (id_batches, momento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Mediciones puntuales de control de calidad';
