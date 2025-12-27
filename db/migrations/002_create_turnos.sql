-- Migration: 002_create_turnos.sql
-- Description: Create tables for Turno (Shift) management system
-- Date: 2024-12-27
-- Tables: turnos, turnos_faltantes, turnos_anticipos, turnos_facturas_credito,
--         turnos_ingresos_prosegur, turnos_gastos_caja_chica, turnos_comisiones

-- ================================================================
-- Main Turnos table
-- ================================================================
CREATE TABLE IF NOT EXISTS `turnos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_atendedores` INT(11) DEFAULT NULL,
  `fecha` DATE DEFAULT NULL,
  `hora_inicio` TIME DEFAULT NULL,
  `hora_fin` TIME DEFAULT NULL,

  -- Cash counting - Bills (Billetes)
  `billetes_20000` INT(11) DEFAULT 0,
  `billetes_10000` INT(11) DEFAULT 0,
  `billetes_5000` INT(11) DEFAULT 0,
  `billetes_2000` INT(11) DEFAULT 0,
  `billetes_1000` INT(11) DEFAULT 0,

  -- Cash counting - Coins (Monedas)
  `monedas_500` INT(11) DEFAULT 0,
  `monedas_100` INT(11) DEFAULT 0,
  `monedas_50` INT(11) DEFAULT 0,
  `monedas_10` INT(11) DEFAULT 0,

  -- Calculated totals
  `total_billetes` DECIMAL(15,2) DEFAULT 0,
  `total_monedas` DECIMAL(15,2) DEFAULT 0,
  `total_efectivo` DECIMAL(15,2) DEFAULT 0,

  -- Related totals (denormalized for quick access)
  `total_faltantes` DECIMAL(15,2) DEFAULT 0,
  `total_anticipos` DECIMAL(15,2) DEFAULT 0,
  `total_facturas_credito` DECIMAL(15,2) DEFAULT 0,
  `total_ingresos_prosegur` DECIMAL(15,2) DEFAULT 0,
  `total_gastos_caja_chica` DECIMAL(15,2) DEFAULT 0,
  `total_donaciones` DECIMAL(15,2) DEFAULT 0,

  -- Workflow state
  `estado` VARCHAR(50) DEFAULT 'Abierto',
  `cerrado_por` INT(11) DEFAULT NULL,
  `cerrado_fecha` DATETIME DEFAULT NULL,
  `aprobado_por` INT(11) DEFAULT NULL,
  `aprobado_fecha` DATETIME DEFAULT NULL,

  -- Notes
  `observaciones` TEXT,
  `observaciones_cierre` TEXT,
  `observaciones_aprobacion` TEXT,

  -- Audit fields
  `creada` DATETIME DEFAULT NULL,
  `actualizada` DATETIME DEFAULT NULL,
  `creado_por` INT(11) DEFAULT NULL,

  PRIMARY KEY (`id`),
  INDEX `idx_turnos_atendedor` (`id_atendedores`),
  INDEX `idx_turnos_fecha` (`fecha`),
  INDEX `idx_turnos_estado` (`estado`),
  INDEX `idx_turnos_fecha_estado` (`fecha`, `estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Turnos Faltantes (Shortages)
-- ================================================================
CREATE TABLE IF NOT EXISTS `turnos_faltantes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_turnos` INT(11) NOT NULL,
  `descripcion` VARCHAR(500) DEFAULT NULL,
  `monto` DECIMAL(15,2) DEFAULT 0,
  `tipo` VARCHAR(50) DEFAULT NULL,
  `codigo_producto` VARCHAR(100) DEFAULT NULL,
  `cantidad` INT(11) DEFAULT 0,
  `observaciones` TEXT,
  `estado` VARCHAR(50) DEFAULT 'activo',
  `creada` DATETIME DEFAULT NULL,
  `actualizada` DATETIME DEFAULT NULL,
  `creado_por` INT(11) DEFAULT NULL,

  PRIMARY KEY (`id`),
  INDEX `idx_faltantes_turno` (`id_turnos`),
  INDEX `idx_faltantes_estado` (`estado`),
  CONSTRAINT `fk_faltantes_turno` FOREIGN KEY (`id_turnos`)
    REFERENCES `turnos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Turnos Anticipos (Advances)
-- ================================================================
CREATE TABLE IF NOT EXISTS `turnos_anticipos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_turnos` INT(11) NOT NULL,
  `id_atendedores` INT(11) DEFAULT NULL,
  `descripcion` VARCHAR(500) DEFAULT NULL,
  `monto` DECIMAL(15,2) DEFAULT 0,
  `motivo` VARCHAR(255) DEFAULT NULL,
  `autorizado_por` INT(11) DEFAULT NULL,
  `observaciones` TEXT,
  `estado` VARCHAR(50) DEFAULT 'activo',
  `creada` DATETIME DEFAULT NULL,
  `actualizada` DATETIME DEFAULT NULL,
  `creado_por` INT(11) DEFAULT NULL,

  PRIMARY KEY (`id`),
  INDEX `idx_anticipos_turno` (`id_turnos`),
  INDEX `idx_anticipos_atendedor` (`id_atendedores`),
  INDEX `idx_anticipos_estado` (`estado`),
  CONSTRAINT `fk_anticipos_turno` FOREIGN KEY (`id_turnos`)
    REFERENCES `turnos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Turnos Facturas a Credito (Credit Invoices)
-- ================================================================
CREATE TABLE IF NOT EXISTS `turnos_facturas_credito` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_turnos` INT(11) NOT NULL,
  `numero_factura` VARCHAR(100) DEFAULT NULL,
  `rut_cliente` VARCHAR(20) DEFAULT NULL,
  `nombre_cliente` VARCHAR(255) DEFAULT NULL,
  `monto` DECIMAL(15,2) DEFAULT 0,
  `fecha_vencimiento` DATE DEFAULT NULL,
  `descripcion` VARCHAR(500) DEFAULT NULL,
  `observaciones` TEXT,
  `estado` VARCHAR(50) DEFAULT 'activo',
  `pagada_fecha` DATETIME DEFAULT NULL,
  `creada` DATETIME DEFAULT NULL,
  `actualizada` DATETIME DEFAULT NULL,
  `creado_por` INT(11) DEFAULT NULL,

  PRIMARY KEY (`id`),
  INDEX `idx_facturas_turno` (`id_turnos`),
  INDEX `idx_facturas_estado` (`estado`),
  INDEX `idx_facturas_vencimiento` (`fecha_vencimiento`),
  INDEX `idx_facturas_rut` (`rut_cliente`),
  CONSTRAINT `fk_facturas_turno` FOREIGN KEY (`id_turnos`)
    REFERENCES `turnos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Turnos Ingresos PROSEGUR MAE
-- ================================================================
CREATE TABLE IF NOT EXISTS `turnos_ingresos_prosegur` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_turnos` INT(11) NOT NULL,
  `numero_boleta` VARCHAR(100) DEFAULT NULL,
  `monto` DECIMAL(15,2) DEFAULT 0,
  `fecha_ingreso` DATE DEFAULT NULL,
  `hora_ingreso` TIME DEFAULT NULL,
  `descripcion` VARCHAR(500) DEFAULT NULL,
  `observaciones` TEXT,
  `estado` VARCHAR(50) DEFAULT 'activo',
  `creada` DATETIME DEFAULT NULL,
  `actualizada` DATETIME DEFAULT NULL,
  `creado_por` INT(11) DEFAULT NULL,

  PRIMARY KEY (`id`),
  INDEX `idx_prosegur_turno` (`id_turnos`),
  INDEX `idx_prosegur_estado` (`estado`),
  INDEX `idx_prosegur_fecha` (`fecha_ingreso`),
  CONSTRAINT `fk_prosegur_turno` FOREIGN KEY (`id_turnos`)
    REFERENCES `turnos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Turnos Gastos Caja Chica (Petty Cash Expenses)
-- ================================================================
CREATE TABLE IF NOT EXISTS `turnos_gastos_caja_chica` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_turnos` INT(11) NOT NULL,
  `descripcion` VARCHAR(500) DEFAULT NULL,
  `monto` DECIMAL(15,2) DEFAULT 0,
  `tipo` VARCHAR(50) DEFAULT NULL,
  `categoria` VARCHAR(100) DEFAULT NULL,
  `numero_documento` VARCHAR(100) DEFAULT NULL,
  `fecha_documento` DATE DEFAULT NULL,
  `proveedor` VARCHAR(255) DEFAULT NULL,
  `observaciones` TEXT,
  `estado` VARCHAR(50) DEFAULT 'activo',
  `creada` DATETIME DEFAULT NULL,
  `actualizada` DATETIME DEFAULT NULL,
  `creado_por` INT(11) DEFAULT NULL,

  PRIMARY KEY (`id`),
  INDEX `idx_gastos_turno` (`id_turnos`),
  INDEX `idx_gastos_estado` (`estado`),
  INDEX `idx_gastos_tipo` (`tipo`),
  INDEX `idx_gastos_categoria` (`categoria`),
  CONSTRAINT `fk_gastos_turno` FOREIGN KEY (`id_turnos`)
    REFERENCES `turnos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Turnos Comisiones (Commissions)
-- ================================================================
CREATE TABLE IF NOT EXISTS `turnos_comisiones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_turnos` INT(11) DEFAULT NULL,
  `id_atendedores` INT(11) DEFAULT NULL,
  `descripcion` VARCHAR(500) DEFAULT NULL,
  `monto` DECIMAL(15,2) DEFAULT 0,
  `tipo` VARCHAR(50) DEFAULT NULL,
  `porcentaje` DECIMAL(5,2) DEFAULT 0,
  `base_calculo` DECIMAL(15,2) DEFAULT 0,
  `mes` VARCHAR(7) DEFAULT NULL,
  `anio` INT(4) DEFAULT NULL,
  `estado` VARCHAR(50) DEFAULT 'pendiente',
  `observaciones` TEXT,
  `creada` DATETIME DEFAULT NULL,
  `actualizada` DATETIME DEFAULT NULL,
  `creado_por` INT(11) DEFAULT NULL,

  PRIMARY KEY (`id`),
  INDEX `idx_comisiones_turno` (`id_turnos`),
  INDEX `idx_comisiones_atendedor` (`id_atendedores`),
  INDEX `idx_comisiones_mes` (`mes`),
  INDEX `idx_comisiones_anio` (`anio`),
  INDEX `idx_comisiones_estado` (`estado`),
  INDEX `idx_comisiones_mes_estado` (`mes`, `estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Add foreign key for atendedores in turnos table (if atendedores table exists)
-- ================================================================
-- Note: Run this after atendedores table is created
-- ALTER TABLE `turnos` ADD CONSTRAINT `fk_turnos_atendedor`
--   FOREIGN KEY (`id_atendedores`) REFERENCES `atendedores` (`id`)
--   ON DELETE SET NULL ON UPDATE CASCADE;
