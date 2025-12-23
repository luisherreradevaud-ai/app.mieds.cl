-- =====================================================
-- Migración 019: Campos CIP (Clean-In-Place) para Registros de Limpieza
-- Fecha: 2025-12-13
-- Descripción: Agrega campos de limpieza CIP a registros_limpiezas
-- =====================================================

-- Procedimiento para agregar columnas de forma segura
DELIMITER //

DROP PROCEDURE IF EXISTS add_column_if_not_exists//

CREATE PROCEDURE add_column_if_not_exists(
    IN p_table_name VARCHAR(64),
    IN p_column_name VARCHAR(64),
    IN p_column_definition VARCHAR(500)
)
BEGIN
    SET @col_exists = (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = p_table_name
        AND COLUMN_NAME = p_column_name
    );

    IF @col_exists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', p_table_name, ' ADD COLUMN ', p_column_name, ' ', p_column_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END//

DELIMITER ;

-- Campos CIP para registros_limpiezas
CALL add_column_if_not_exists('registros_limpiezas', 'programa_cip', "VARCHAR(100) DEFAULT '' COMMENT 'Programa CIP utilizado'");
CALL add_column_if_not_exists('registros_limpiezas', 'temperatura_max_cip', "FLOAT DEFAULT NULL COMMENT 'Temperatura máxima alcanzada en CIP (°C)'");
CALL add_column_if_not_exists('registros_limpiezas', 'tiempo_total_cip_min', "INT DEFAULT NULL COMMENT 'Tiempo total del ciclo CIP (minutos)'");
CALL add_column_if_not_exists('registros_limpiezas', 'conductividad_promedio', "FLOAT DEFAULT NULL COMMENT 'Conductividad promedio del enjuague final (µS/cm)'");
CALL add_column_if_not_exists('registros_limpiezas', 'cip_timestamp_inicio', "DATETIME DEFAULT NULL COMMENT 'Inicio del ciclo CIP'");
CALL add_column_if_not_exists('registros_limpiezas', 'cip_timestamp_fin', "DATETIME DEFAULT NULL COMMENT 'Fin del ciclo CIP'");
CALL add_column_if_not_exists('registros_limpiezas', 'id_batches_posterior', "VARCHAR(36) DEFAULT NULL COMMENT 'Batch que usará el activo después del CIP'");

-- Limpiar procedimiento
DROP PROCEDURE IF EXISTS add_column_if_not_exists;

-- Índice para búsquedas de CIP
SET @idx_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'registros_limpiezas'
    AND INDEX_NAME = 'idx_registros_limpiezas_cip'
);
SET @sql_idx = IF(@idx_exists = 0,
    'CREATE INDEX idx_registros_limpiezas_cip ON registros_limpiezas(tipo_limpieza, cip_timestamp_inicio)',
    'SELECT 1'
);
PREPARE stmt_idx FROM @sql_idx;
EXECUTE stmt_idx;
DEALLOCATE PREPARE stmt_idx;

-- Índice para batch posterior
SET @idx_exists2 = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'registros_limpiezas'
    AND INDEX_NAME = 'idx_registros_limpiezas_batch_posterior'
);
SET @sql_idx2 = IF(@idx_exists2 = 0,
    'CREATE INDEX idx_registros_limpiezas_batch_posterior ON registros_limpiezas(id_batches_posterior)',
    'SELECT 1'
);
PREPARE stmt_idx2 FROM @sql_idx2;
EXECUTE stmt_idx2;
DEALLOCATE PREPARE stmt_idx2;

-- Verificar resultado
SELECT 'Campos CIP agregados a registros_limpiezas' as mensaje;
DESCRIBE registros_limpiezas;

