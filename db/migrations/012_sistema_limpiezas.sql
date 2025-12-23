-- =====================================================
-- Migración 012: Sistema de Registro de Limpiezas
-- Fecha: 2025-12-04
-- Descripción: Crea sistema completo para registrar
--              limpiezas de activos, especialmente
--              para certificación Halal
-- =====================================================

-- -----------------------------------------------------
-- 1. Campos de limpieza en tabla activos
-- -----------------------------------------------------
ALTER TABLE activos
ADD COLUMN fecha_ultima_limpieza DATETIME DEFAULT NULL
  COMMENT 'Fecha/hora de última limpieza general',
ADD COLUMN proxima_limpieza DATE DEFAULT NULL
  COMMENT 'Fecha programada próxima limpieza',
ADD COLUMN limpieza_procedimiento MEDIUMTEXT DEFAULT NULL
  COMMENT 'Procedimiento de limpieza estándar',
ADD COLUMN limpieza_periodicidad VARCHAR(100) DEFAULT 'Semanal'
  COMMENT 'Frecuencia de limpieza requerida';

-- Campos específicos Halal
ALTER TABLE activos
ADD COLUMN fecha_ultima_limpieza_halal DATETIME DEFAULT NULL
  COMMENT 'Fecha/hora de última limpieza certificada Halal',
ADD COLUMN certificado_limpieza_halal VARCHAR(100) DEFAULT NULL
  COMMENT 'Número de certificado de limpieza Halal',
ADD COLUMN uso_exclusivo_halal TINYINT(1) NOT NULL DEFAULT 0
  COMMENT 'Activo de uso exclusivo para producción Halal';

-- Índices
CREATE INDEX idx_activos_limpieza ON activos(fecha_ultima_limpieza);
CREATE INDEX idx_activos_limpieza_halal ON activos(fecha_ultima_limpieza_halal);

-- -----------------------------------------------------
-- 2. Tabla de registros de limpiezas (historial)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS registros_limpiezas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_activos INT NOT NULL
      COMMENT 'FK al activo limpiado',
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
      COMMENT 'Fecha y hora de la limpieza',
    tipo_limpieza ENUM('General', 'Profunda', 'Halal', 'Sanitizacion', 'CIP') NOT NULL DEFAULT 'General'
      COMMENT 'Tipo de limpieza realizada',
    procedimiento_utilizado VARCHAR(255) DEFAULT NULL
      COMMENT 'Referencia al procedimiento usado',
    productos_utilizados TEXT DEFAULT NULL
      COMMENT 'Lista de productos de limpieza usados',
    id_usuarios INT NOT NULL
      COMMENT 'Usuario que realizó la limpieza',
    id_usuarios_supervisor INT DEFAULT NULL
      COMMENT 'Supervisor que verificó (para Halal)',
    observaciones TEXT DEFAULT NULL
      COMMENT 'Notas adicionales',

    -- Campos específicos Halal
    es_limpieza_halal TINYINT(1) NOT NULL DEFAULT 0
      COMMENT 'Flag: limpieza certificada Halal',
    certificado_numero VARCHAR(100) DEFAULT NULL
      COMMENT 'Número de certificado Halal',
    certificado_emisor VARCHAR(200) DEFAULT NULL
      COMMENT 'Entidad certificadora',

    -- Evidencia
    id_media INT DEFAULT NULL
      COMMENT 'Foto/documento de evidencia',

    -- Metadatos
    creada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(50) NOT NULL DEFAULT 'activo',

    -- Índices
    INDEX idx_registros_limpiezas_activo (id_activos),
    INDEX idx_registros_limpiezas_fecha (fecha),
    INDEX idx_registros_limpiezas_tipo (tipo_limpieza),
    INDEX idx_registros_limpiezas_halal (es_limpieza_halal),
    INDEX idx_registros_limpiezas_usuario (id_usuarios)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de limpiezas de activos';

-- -----------------------------------------------------
-- 3. Tabla de procedimientos de limpieza
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS procedimientos_limpieza (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE
      COMMENT 'Código del procedimiento (ej: PROC-LIM-001)',
    nombre VARCHAR(200) NOT NULL
      COMMENT 'Nombre del procedimiento',
    tipo ENUM('General', 'Profunda', 'Halal', 'Sanitizacion', 'CIP') NOT NULL
      COMMENT 'Tipo de limpieza',
    descripcion TEXT
      COMMENT 'Descripción detallada',
    pasos TEXT
      COMMENT 'Pasos del procedimiento (JSON)',
    productos_requeridos TEXT
      COMMENT 'Lista de productos necesarios (JSON)',
    tiempo_estimado_minutos INT DEFAULT NULL
      COMMENT 'Duración estimada en minutos',
    frecuencia_recomendada VARCHAR(100) DEFAULT NULL
      COMMENT 'Frecuencia recomendada de aplicación',
    aplica_a_clases TEXT DEFAULT NULL
      COMMENT 'Clases de activos donde aplica (JSON)',
    es_halal_certificado TINYINT(1) NOT NULL DEFAULT 0
      COMMENT 'Procedimiento certificado para Halal',
    version VARCHAR(20) DEFAULT '1.0'
      COMMENT 'Versión del procedimiento',
    creada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizada DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado VARCHAR(50) NOT NULL DEFAULT 'activo',

    INDEX idx_procedimientos_tipo (tipo),
    INDEX idx_procedimientos_halal (es_halal_certificado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de procedimientos de limpieza';

-- -----------------------------------------------------
-- 4. Datos iniciales: Procedimientos básicos
-- -----------------------------------------------------
INSERT INTO procedimientos_limpieza (codigo, nombre, tipo, descripcion, tiempo_estimado_minutos, es_halal_certificado) VALUES
('PROC-LIM-001', 'Limpieza General de Fermentador', 'General', 'Limpieza estándar de fermentadores con agua y detergente neutro', 30, 0),
('PROC-LIM-002', 'Limpieza Profunda de Fermentador', 'Profunda', 'Limpieza profunda con sanitizante y enjuague múltiple', 60, 0),
('PROC-LIM-003', 'Sanitización CIP', 'CIP', 'Clean In Place - Limpieza automatizada sin desarmar', 45, 0),
('PROC-LIM-004', 'Limpieza Halal Certificada', 'Halal', 'Limpieza según protocolo Halal con productos certificados y supervisor', 90, 1),
('PROC-LIM-005', 'Limpieza Halal Post-Alcohol', 'Halal', 'Limpieza Halal específica después de producción alcohólica', 120, 1);
