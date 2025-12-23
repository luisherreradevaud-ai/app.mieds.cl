-- =====================================================
-- Migración 018: Vincular Insumos con Proveedores
-- Fecha: 2025-12-13
-- Descripción: Agrega FK a proveedores y migra datos existentes
-- =====================================================

-- Agregar columna id_proveedores si no existe
SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'insumos'
    AND COLUMN_NAME = 'id_proveedores'
);

SET @sql = IF(@col_exists = 0,
    "ALTER TABLE insumos ADD COLUMN id_proveedores VARCHAR(36) DEFAULT NULL COMMENT 'FK a proveedores' AFTER id_tipos_de_insumos",
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear índice si no existe
SET @idx_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'insumos'
    AND INDEX_NAME = 'idx_insumos_proveedor'
);

SET @sql_idx = IF(@idx_exists = 0,
    'CREATE INDEX idx_insumos_proveedor ON insumos(id_proveedores)',
    'SELECT 1'
);
PREPARE stmt_idx FROM @sql_idx;
EXECUTE stmt_idx;
DEALLOCATE PREPARE stmt_idx;

-- Migrar datos: crear proveedores desde los campos existentes
-- Solo para insumos que tienen proveedor definido y no tienen id_proveedores
-- Nota: campos vacíos para proveedores migrados automáticamente
INSERT INTO proveedores (id, id_usuarios, nombre, email, telefono, creada, comentarios, numero_cuenta, rut_empresa, ids_tipos_de_insumos)
SELECT DISTINCT
    CONCAT('prov_', MD5(CONCAT(proveedor, IFNULL(pais_origen, '')))),
    0,
    proveedor,
    '',
    '',
    NOW(),
    CONCAT('País: ', IFNULL(pais_origen, 'No especificado'), '. Migrado automáticamente.'),
    '',
    '',
    ''
FROM insumos
WHERE proveedor IS NOT NULL
  AND proveedor != ''
  AND (id_proveedores IS NULL OR id_proveedores = '')
  AND NOT EXISTS (
    SELECT 1 FROM proveedores p WHERE p.nombre = insumos.proveedor
  )
GROUP BY proveedor, pais_origen;

-- Actualizar insumos con el id del proveedor migrado
UPDATE insumos i
INNER JOIN proveedores p ON p.nombre = i.proveedor
SET i.id_proveedores = p.id
WHERE i.proveedor IS NOT NULL
  AND i.proveedor != ''
  AND (i.id_proveedores IS NULL OR i.id_proveedores = '');

-- Verificar resultado
SELECT 'Migración completada' as mensaje;
SELECT COUNT(*) as insumos_con_proveedor FROM insumos WHERE id_proveedores IS NOT NULL AND id_proveedores != '';
SELECT COUNT(*) as proveedores_creados FROM proveedores WHERE comentarios LIKE '%Migrado automáticamente%';

