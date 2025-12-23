-- =====================================================
-- Migración 014: Códigos de producto por línea productiva
-- Fecha: 2025-12-13
-- Descripción: Agrega identificador único por línea y
--              sistema de secuencias automáticas
-- =====================================================

-- Agregar campo de código único a productos
ALTER TABLE productos
ADD COLUMN codigo_producto VARCHAR(20) DEFAULT NULL
  COMMENT 'Código único por línea (ALC-001, SNA-001, GEN-001)'
  AFTER id;

-- Índice único para el código
CREATE UNIQUE INDEX idx_productos_codigo ON productos(codigo_producto);

-- Tabla de secuencias por línea productiva
CREATE TABLE IF NOT EXISTS productos_secuencias (
  linea_productiva ENUM('alcoholica','analcoholica','general') PRIMARY KEY,
  ultimo_numero INT NOT NULL DEFAULT 0
    COMMENT 'Último número asignado en esta línea',
  prefijo VARCHAR(5) NOT NULL
    COMMENT 'Prefijo para el código (ALC, SNA, GEN)',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Secuencias de códigos por línea productiva';

-- Inicializar secuencias
INSERT INTO productos_secuencias (linea_productiva, ultimo_numero, prefijo) VALUES
  ('alcoholica', 0, 'ALC'),
  ('analcoholica', 0, 'SNA'),
  ('general', 0, 'GEN');

-- Generar códigos para productos existentes (migración de datos)
-- Alcohólicos
SET @counter_alc = 0;
UPDATE productos
SET codigo_producto = CONCAT('ALC-', LPAD((@counter_alc := @counter_alc + 1), 3, '0'))
WHERE linea_productiva = 'alcoholica'
  AND codigo_producto IS NULL
ORDER BY id;

UPDATE productos_secuencias
SET ultimo_numero = (
  SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(codigo_producto, '-', -1) AS UNSIGNED)), 0)
  FROM productos
  WHERE linea_productiva = 'alcoholica' AND codigo_producto IS NOT NULL
)
WHERE linea_productiva = 'alcoholica';

-- Analcohólicos
SET @counter_sna = 0;
UPDATE productos
SET codigo_producto = CONCAT('SNA-', LPAD((@counter_sna := @counter_sna + 1), 3, '0'))
WHERE linea_productiva = 'analcoholica'
  AND codigo_producto IS NULL
ORDER BY id;

UPDATE productos_secuencias
SET ultimo_numero = (
  SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(codigo_producto, '-', -1) AS UNSIGNED)), 0)
  FROM productos
  WHERE linea_productiva = 'analcoholica' AND codigo_producto IS NOT NULL
)
WHERE linea_productiva = 'analcoholica';

-- General
SET @counter_gen = 0;
UPDATE productos
SET codigo_producto = CONCAT('GEN-', LPAD((@counter_gen := @counter_gen + 1), 3, '0'))
WHERE linea_productiva = 'general'
  AND codigo_producto IS NULL
ORDER BY id;

UPDATE productos_secuencias
SET ultimo_numero = (
  SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(codigo_producto, '-', -1) AS UNSIGNED)), 0)
  FROM productos
  WHERE linea_productiva = 'general' AND codigo_producto IS NOT NULL
)
WHERE linea_productiva = 'general';
