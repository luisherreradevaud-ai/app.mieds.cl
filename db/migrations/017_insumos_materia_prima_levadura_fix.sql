-- =====================================================
-- Migración 017 FIX: Campos MateriaPrima y Levadura para Insumos
-- (Maneja columnas que ya existen)
-- =====================================================

-- Procedimiento para agregar columnas de forma segura
DELIMITER //

DROP PROCEDURE IF EXISTS add_column_if_not_exists//

CREATE PROCEDURE add_column_if_not_exists(
    IN table_name VARCHAR(64),
    IN column_name VARCHAR(64),
    IN column_definition VARCHAR(500)
)
BEGIN
    SET @column_exists = (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = table_name
        AND COLUMN_NAME = column_name
    );

    IF @column_exists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', table_name, ' ADD COLUMN ', column_name, ' ', column_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END//

DELIMITER ;

-- Campos de Materia Prima
CALL add_column_if_not_exists('insumos', 'nombre_comercial', "VARCHAR(200) DEFAULT '' COMMENT 'Nombre comercial del producto'");
CALL add_column_if_not_exists('insumos', 'marca', "VARCHAR(100) DEFAULT '' COMMENT 'Marca del producto'");
CALL add_column_if_not_exists('insumos', 'materia_prima_basica', "VARCHAR(200) DEFAULT '' COMMENT 'Materia prima base'");
CALL add_column_if_not_exists('insumos', 'cosecha_anio', "YEAR DEFAULT NULL COMMENT 'Año de cosecha'");
CALL add_column_if_not_exists('insumos', 'presentacion', "VARCHAR(100) DEFAULT '' COMMENT 'Presentación del producto'");
CALL add_column_if_not_exists('insumos', 'vida_util_meses', "INT DEFAULT NULL COMMENT 'Vida útil en meses'");

-- Campos de Levadura
CALL add_column_if_not_exists('insumos', 'es_levadura', "TINYINT(1) DEFAULT 0 COMMENT 'Indica si es levadura'");
CALL add_column_if_not_exists('insumos', 'cepa', "VARCHAR(100) DEFAULT '' COMMENT 'Cepa de la levadura'");
CALL add_column_if_not_exists('insumos', 'tipo_levadura', "ENUM('ale_seca', 'ale_liquida', 'lager_seca', 'lager_liquida', 'wild', 'otro') DEFAULT NULL COMMENT 'Tipo de levadura'");
CALL add_column_if_not_exists('insumos', 'atenuacion_min', "DECIMAL(5,2) DEFAULT NULL COMMENT 'Atenuación mínima (%)'");
CALL add_column_if_not_exists('insumos', 'atenuacion_max', "DECIMAL(5,2) DEFAULT NULL COMMENT 'Atenuación máxima (%)'");
CALL add_column_if_not_exists('insumos', 'floculacion', "ENUM('baja', 'media', 'alta', 'muy_alta') DEFAULT NULL COMMENT 'Nivel de floculación'");
CALL add_column_if_not_exists('insumos', 'temp_fermentacion_min', "DECIMAL(4,1) DEFAULT NULL COMMENT 'Temp mínima fermentación (°C)'");
CALL add_column_if_not_exists('insumos', 'temp_fermentacion_max', "DECIMAL(4,1) DEFAULT NULL COMMENT 'Temp máxima fermentación (°C)'");
CALL add_column_if_not_exists('insumos', 'tolerancia_alcohol', "DECIMAL(4,1) DEFAULT NULL COMMENT 'Tolerancia máxima al alcohol (%)'");

-- Limpiar procedimiento
DROP PROCEDURE IF EXISTS add_column_if_not_exists;

-- Índices (ignorar errores si ya existen)
-- Si falla por duplicado, ignorar
SET @idx1 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'insumos'
             AND INDEX_NAME = 'idx_insumos_es_levadura');
SET @sql1 = IF(@idx1 = 0, 'CREATE INDEX idx_insumos_es_levadura ON insumos(es_levadura)', 'SELECT 1');
PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @idx2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'insumos'
             AND INDEX_NAME = 'idx_insumos_marca');
SET @sql2 = IF(@idx2 = 0, 'CREATE INDEX idx_insumos_marca ON insumos(marca)', 'SELECT 1');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Verificar resultado
SELECT 'Columnas de Materia Prima y Levadura agregadas' as mensaje;
DESCRIBE insumos;

