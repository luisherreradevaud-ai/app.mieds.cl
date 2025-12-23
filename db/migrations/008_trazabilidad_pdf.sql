-- =====================================================
-- Migración 008: Campos para PDF de Trazabilidad
-- Fecha: 2025-12-01
-- Descripción: Agrega campos necesarios para generar
--              certificados de trazabilidad por entrega
-- =====================================================

-- -----------------------------------------------------
-- 1. Agregar campo linea_productiva a activos
-- Para indicar si el activo pertenece a línea
-- alcohólica, sin alcohol o general
-- -----------------------------------------------------
ALTER TABLE activos
ADD COLUMN linea_productiva ENUM('alcoholica', 'analcoholica', 'general')
DEFAULT 'general' AFTER clase;

-- Índice para consultas por línea productiva
CREATE INDEX idx_activos_linea_productiva ON activos(linea_productiva);

-- -----------------------------------------------------
-- 2. Agregar campo fecha_llenado a barriles
-- Para registrar fecha exacta de embarrilado
-- -----------------------------------------------------
ALTER TABLE barriles
ADD COLUMN fecha_llenado DATETIME DEFAULT NULL AFTER litros_cargados;

-- Índice para consultas por fecha de llenado
CREATE INDEX idx_barriles_fecha_llenado ON barriles(fecha_llenado);

-- -----------------------------------------------------
-- 3. (Opcional) Inferir fecha_llenado de barriles existentes
-- desde la tabla barriles_estados
-- Descomenta si quieres actualizar datos históricos
-- -----------------------------------------------------
/*
UPDATE barriles b
SET fecha_llenado = (
    SELECT MIN(be.inicio_date)
    FROM barriles_estados be
    WHERE be.id_barriles = b.id
    AND be.estado IN ('En planta', 'En sala de frio')
    AND be.inicio_date != '0000-00-00 00:00:00'
)
WHERE b.fecha_llenado IS NULL
AND b.litros_cargados > 0;
*/

-- -----------------------------------------------------
-- 4. Verificación
-- -----------------------------------------------------
-- Verificar que los campos se agregaron correctamente:
-- DESCRIBE activos;
-- DESCRIBE barriles;
