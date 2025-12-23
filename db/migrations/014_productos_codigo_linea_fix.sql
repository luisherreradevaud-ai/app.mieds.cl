-- =====================================================
-- Migración 014 FIX: Completar códigos de producto
-- (La columna codigo_producto ya existe)
-- =====================================================

-- Tabla de secuencias por línea productiva (si no existe)
CREATE TABLE IF NOT EXISTS productos_secuencias (
  linea_productiva ENUM('alcoholica','analcoholica','general') PRIMARY KEY,
  ultimo_numero INT NOT NULL DEFAULT 0
    COMMENT 'Último número asignado en esta línea',
  prefijo VARCHAR(5) NOT NULL
    COMMENT 'Prefijo para el código (ALC, SNA, GEN)',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Secuencias de códigos por línea productiva';

-- Inicializar secuencias (ignorar si ya existen)
INSERT IGNORE INTO productos_secuencias (linea_productiva, ultimo_numero, prefijo) VALUES
  ('alcoholica', 0, 'ALC'),
  ('analcoholica', 0, 'SNA'),
  ('general', 0, 'GEN');

-- Generar códigos para productos existentes (migración de datos)
-- Alcohólicos
SET @counter_alc = 0;
UPDATE productos
SET codigo_producto = CONCAT('ALC-', LPAD((@counter_alc := @counter_alc + 1), 3, '0'))
WHERE linea_productiva = 'alcoholica'
  AND (codigo_producto IS NULL OR codigo_producto = '')
ORDER BY id;

UPDATE productos_secuencias
SET ultimo_numero = (
  SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(codigo_producto, '-', -1) AS UNSIGNED)), 0)
  FROM productos
  WHERE linea_productiva = 'alcoholica' AND codigo_producto IS NOT NULL AND codigo_producto != ''
)
WHERE linea_productiva = 'alcoholica';

-- Analcohólicos
SET @counter_sna = 0;
UPDATE productos
SET codigo_producto = CONCAT('SNA-', LPAD((@counter_sna := @counter_sna + 1), 3, '0'))
WHERE linea_productiva = 'analcoholica'
  AND (codigo_producto IS NULL OR codigo_producto = '')
ORDER BY id;

UPDATE productos_secuencias
SET ultimo_numero = (
  SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(codigo_producto, '-', -1) AS UNSIGNED)), 0)
  FROM productos
  WHERE linea_productiva = 'analcoholica' AND codigo_producto IS NOT NULL AND codigo_producto != ''
)
WHERE linea_productiva = 'analcoholica';

-- General
SET @counter_gen = 0;
UPDATE productos
SET codigo_producto = CONCAT('GEN-', LPAD((@counter_gen := @counter_gen + 1), 3, '0'))
WHERE linea_productiva = 'general'
  AND (codigo_producto IS NULL OR codigo_producto = '')
ORDER BY id;

UPDATE productos_secuencias
SET ultimo_numero = (
  SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(codigo_producto, '-', -1) AS UNSIGNED)), 0)
  FROM productos
  WHERE linea_productiva = 'general' AND codigo_producto IS NOT NULL AND codigo_producto != ''
)
WHERE linea_productiva = 'general';

-- Verificar resultado
SELECT 'Códigos generados:' as mensaje;
SELECT linea_productiva, COUNT(*) as productos, MAX(codigo_producto) as ultimo_codigo
FROM productos
WHERE codigo_producto IS NOT NULL AND codigo_producto != ''
GROUP BY linea_productiva;

SELECT 'Secuencias:' as mensaje;
SELECT * FROM productos_secuencias;
