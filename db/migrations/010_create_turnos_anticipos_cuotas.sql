-- Migration 010: Create turnos_anticipos_cuotas table
-- Tracks monthly installments for advance payments

CREATE TABLE IF NOT EXISTS turnos_anticipos_cuotas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_anticipos INT NOT NULL,
  mes VARCHAR(7) NOT NULL,  -- Format: YYYY-MM
  monto DECIMAL(12,2) DEFAULT 0,
  estado VARCHAR(20) DEFAULT 'pendiente',  -- pendiente, pagado, cancelado
  fecha_pago DATETIME NULL,
  observaciones TEXT,
  creada DATETIME,
  actualizada DATETIME,
  FOREIGN KEY (id_anticipos) REFERENCES turnos_anticipos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add columns to turnos_anticipos for installment tracking
ALTER TABLE turnos_anticipos
  ADD COLUMN IF NOT EXISTS numero_cuotas INT DEFAULT 1 AFTER motivo,
  ADD COLUMN IF NOT EXISTS monto_cuota DECIMAL(12,2) DEFAULT 0 AFTER numero_cuotas,
  ADD COLUMN IF NOT EXISTS mes_inicio VARCHAR(7) NULL AFTER monto_cuota;

-- Index for faster lookups
CREATE INDEX idx_anticipos_cuotas_mes ON turnos_anticipos_cuotas(mes);
CREATE INDEX idx_anticipos_cuotas_estado ON turnos_anticipos_cuotas(estado);
