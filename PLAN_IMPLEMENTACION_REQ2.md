# Plan de Implementación - Requerimiento 2
## Sistema de Trazabilidad Halal y Mejoras ML

**Fecha:** 2025-12-04
**Versión:** 1.0
**Sistema:** Barril.cl ERP
**Autor:** Claude Code

---

## Índice

1. [Resumen Ejecutivo](#1-resumen-ejecutivo)
2. [Análisis de Estado Actual](#2-análisis-de-estado-actual)
3. [Requerimientos Detallados](#3-requerimientos-detallados)
4. [Arquitectura de Cambios](#4-arquitectura-de-cambios)
5. [Migraciones de Base de Datos](#5-migraciones-de-base-de-datos)
6. [Modificaciones a Clases PHP](#6-modificaciones-a-clases-php)
7. [Nuevas Clases a Crear](#7-nuevas-clases-a-crear)
8. [Modificaciones a Templates](#8-modificaciones-a-templates)
9. [Endpoints AJAX](#9-endpoints-ajax)
10. [Modificaciones al PDF de Trazabilidad](#10-modificaciones-al-pdf-de-trazabilidad)
11. [Sistema de Limpiezas](#11-sistema-de-limpiezas)
12. [Plan de Ejecución](#12-plan-de-ejecución)
13. [Dependencias y Orden de Implementación](#13-dependencias-y-orden-de-implementación)
14. [Plan de Pruebas](#14-plan-de-pruebas)
15. [Riesgos y Mitigaciones](#15-riesgos-y-mitigaciones)

---

## 1. Resumen Ejecutivo

### 1.1 Objetivo

Implementar mejoras al sistema de trazabilidad de Barril.cl para:
- Soportar certificación **Halal** (mercados de Medio Oriente)
- Preparar datos para **Machine Learning** (ML ready)
- Mejorar la documentación de **insumos** con fichas técnicas y certificados
- Registrar **limpiezas** de activos para líneas de producción mixtas

### 1.2 Alcance

| Módulo | Impacto | Prioridad |
|--------|---------|-----------|
| PDF de Trazabilidad | Alto | Alta |
| Sistema de Insumos | Alto | Alta |
| Sistema de Limpiezas | Alto | Alta |
| Sistema de Batches (ML) | Medio | Media |
| Sistema de Productos | Bajo | Baja |

### 1.3 Normas a Cumplir

**Línea Alcohólica:**
- ISO 22005 / ISO 22000 / FSSC 22000 / BRCGS / IFS

**Línea Sin Alcohol (Halal):**
- OIC/SMIIC 1 / GSO 2055-1 / ISO 22005 / ISO 22000 / FSSC 22000 / BRCGS / IFS

---

## 2. Análisis de Estado Actual

### 2.1 Sistema de Productos

**Clase:** `php/classes/Producto.php`

| Campo Actual | Existe | Observación |
|--------------|--------|-------------|
| `id` | ✅ | AUTO_INCREMENT |
| `nombre` | ✅ | VARCHAR(300) |
| `tipo` | ✅ | 'Barril' / 'Caja' |
| `id_recetas` | ✅ | FK a recetas |
| `clasificacion` | ✅ | Cerveza, Kombucha, etc. |
| `linea_productiva` | ❌ | **FALTA** |

### 2.2 Sistema de Insumos

**Clase:** `php/classes/Insumo.php`

| Campo Actual | Existe | Observación |
|--------------|--------|-------------|
| `id` | ✅ | - |
| `nombre` | ✅ | - |
| `id_tipos_de_insumos` | ✅ | FK a tipos |
| `unidad_de_medida` | ✅ | - |
| `bodega` | ✅ | Stock en bodega |
| `despacho` | ✅ | Stock en despacho |
| `url_ficha_tecnica` | ❌ | **FALTA** |
| `url_certificado_halal` | ❌ | **FALTA** |
| `es_halal_certificado` | ❌ | **FALTA** |

### 2.3 Sistema de Batches

**Clase:** `php/classes/Batch.php` (63 campos actuales)

| Campos ML Sugeridos | Existe | Observación |
|---------------------|--------|-------------|
| `abv_final` | ❌ | Alcohol by volume final |
| `ibu_final` | ❌ | IBU medido |
| `color_ebc` | ❌ | Color EBC |
| `rendimiento_litros_final` | ❌ | Volumen final real |
| `merma_total_litros` | ❌ | Pérdida total |
| `calificacion_sensorial` | ❌ | 1-10 |

### 2.4 Sistema de Activos (Fermentadores)

**Clase:** `php/classes/Activo.php`

| Campo Actual | Existe | Observación |
|--------------|--------|-------------|
| `linea_productiva` | ✅ | ENUM (alcoholica, analcoholica, general) |
| `ultima_inspeccion` | ✅ | DATE |
| `ultima_mantencion` | ✅ | DATE |
| `fecha_ultima_limpieza` | ❌ | **FALTA** |
| `fecha_ultima_limpieza_halal` | ❌ | **FALTA** |

### 2.5 Sistema de Limpiezas

**Estado:** NO IMPLEMENTADO

No existe:
- Tabla `registros_limpiezas`
- Clase `RegistroLimpieza.php`
- Templates para gestionar limpiezas
- Historial de limpiezas por activo

### 2.6 PDF de Trazabilidad

**Clase:** `php/classes/TrazabilidadPDF.php`

| Elemento | Estado Actual | Requerido |
|----------|---------------|-----------|
| Título | Fijo | Dinámico según línea |
| Normas ISO | No muestra | Mostrar según línea |
| ID Batch | No muestra | Mostrar con fecha |
| Código | "Código" | "Código de barril" |
| Insumos | Solo granos | Todos los insumos |
| Links fichas | No tiene | Agregar URLs |
| Línea en activos | Con título | Sin título "Línea:" |
| Cálculo tiempos | Error reportado | Corregir |
| Registro limpieza | No muestra | Mostrar para Halal |

---

## 3. Requerimientos Detallados

### REQ-2.1: Ajustar ID de Productos por Línea

**Descripción:** Agregar campo `linea_productiva` a productos para categorizar por línea de producción.

**Criterios de Aceptación:**
- [ ] Campo `linea_productiva` en tabla `productos`
- [ ] Selector en formulario de nuevo producto
- [ ] Selector en formulario de edición de producto
- [ ] Filtro por línea en listado de productos

### REQ-2.2: Mejorar Proceso de Batches (ML Ready)

**Descripción:** Agregar campos adicionales para capturar métricas que permitan análisis de Machine Learning.

**Campos Nuevos:**

| Campo | Tipo | Descripción | Uso ML |
|-------|------|-------------|--------|
| `abv_final` | DECIMAL(4,2) | % alcohol final | Predicción de fermentación |
| `ibu_final` | INT | IBU medido | Correlación con lúpulos |
| `color_ebc` | INT | Color EBC | Predicción por maltas |
| `rendimiento_litros_final` | FLOAT | Volumen final | Eficiencia de proceso |
| `merma_total_litros` | FLOAT | Pérdida total | Detección de anomalías |
| `densidad_final_verificada` | DECIMAL(5,3) | OG final | Validación de fermentación |
| `calificacion_sensorial` | TINYINT | 1-10 | Target para ML |
| `notas_cata` | TEXT | Descripción sensorial | NLP features |

### REQ-2.3: Mejorar Sistema de Insumos

**Descripción:** Agregar soporte para fichas técnicas y certificados Halal.

**Campos Nuevos en `insumos`:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `url_ficha_tecnica` | VARCHAR(500) | Link a PDF de ficha técnica |
| `url_certificado_halal` | VARCHAR(500) | Link a certificado Halal |
| `certificado_halal_numero` | VARCHAR(100) | Número de certificado |
| `certificado_halal_vencimiento` | DATE | Fecha de vencimiento |
| `certificado_halal_emisor` | VARCHAR(200) | Entidad certificadora |
| `es_halal_certificado` | BOOLEAN | Flag de certificación |

### REQ-2.4: Título del Certificado según Línea

**Descripción:** El título del PDF debe incluir las normas correspondientes según la línea productiva.

**Línea Alcohólica:**
```
CERTIFICADO DE TRAZABILIDAD DE PRODUCTO
ISO 22005 / ISO 22000 / FSSC 22000 / BRCGS / IFS
Cerveza Cocholgue
```

**Línea Sin Alcohol (Halal):**
```
CERTIFICADO DE TRAZABILIDAD DE PRODUCTO
OIC/SMIIC 1 / GSO 2055-1 / ISO 22005 / ISO 22000 / FSSC 22000 / BRCGS / IFS
Cerveza Cocholgue
```

### REQ-2.5: Agregar ID Batch con Fecha

**Descripción:** En la sección "DATOS DEL PRODUCTO" del PDF, agregar fila con ID del Batch y su fecha.

**Ejemplo:**
```
Batch: #045 (15/11/2025)
```

### REQ-2.6: Cambiar "Código" a "Código de barril"

**Descripción:** Renombrar la etiqueta según el tipo de producto.

- Para barriles: "Código de barril"
- Para cajas: "Código de caja"

### REQ-2.7: Cambiar "Granos" por "Insumos"

**Descripción:** Mostrar todos los insumos utilizados (no solo granos) con links a fichas técnicas.

**Formato:**
```
Insumos:
- Malta Pilsen (5kg) [📄]
- Malta Caramelo (0.5kg) [📄]
- Lúpulo Cascade (100g) [📄] [☪️]
- Levadura US-05 (50g) [📄] [☪️]
```

Donde:
- [📄] = Link a ficha técnica
- [☪️] = Link a certificado Halal (si aplica)

### REQ-2.8: Línea Productiva sin Título

**Descripción:** Mostrar la línea productiva junto al nombre del activo, sin el texto "Línea:".

**Antes:**
```
Fermentador: FER-001 (60L)
Línea: Alcohólica
```

**Después:**
```
Fermentador: FER-001 (60L) [Alcohólica]
```

### REQ-2.9: Corregir Cálculo de Tiempos

**Descripción:** El cálculo de "Empaque → Entrega" está reportando valores incorrectos.

**Investigar:**
1. Formato de fechas (MySQL vs DateTime PHP)
2. Fechas nulas o `0000-00-00`
3. Orden de parámetros en `calcularDiferenciaTiempo()`
4. Zona horaria

### REQ-2.10: Registros de Limpieza para Halal

**Descripción:** Cuando un activo es de línea "General" y se usa para producción Halal, debe existir un registro de limpieza certificada previo.

**Flujo:**
1. Activo "General" requiere limpieza Halal antes de usarse para producción sin alcohol
2. Registrar limpieza con:
   - Fecha y hora
   - Tipo de limpieza (General / Halal)
   - Usuario que realizó
   - Procedimiento utilizado
   - Número de certificado (si aplica)
3. En el PDF de trazabilidad, mostrar:
   - Última limpieza Halal antes de la producción
   - Procedimiento utilizado

---

## 4. Arquitectura de Cambios

### 4.1 Diagrama de Entidades Modificadas

```
┌─────────────────────────────────────────────────────────────────┐
│                     ENTIDADES MODIFICADAS                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐      ┌──────────────┐      ┌──────────────┐  │
│  │   Producto   │      │    Insumo    │      │    Batch     │  │
│  │ + linea_prod │      │ + url_ficha  │      │ + abv_final  │  │
│  └──────────────┘      │ + url_halal  │      │ + ibu_final  │  │
│                        │ + es_halal   │      │ + color_ebc  │  │
│                        └──────────────┘      │ + merma      │  │
│                                              └──────────────┘  │
│                                                                  │
│  ┌──────────────┐      ┌──────────────────────────────────────┐ │
│  │    Activo    │      │         NUEVAS ENTIDADES             │ │
│  │ + limpieza   │      ├──────────────────────────────────────┤ │
│  │ + limpieza_  │      │  ┌────────────────────┐              │ │
│  │   halal      │      │  │  RegistroLimpieza  │              │ │
│  └──────────────┘      │  │  - id_activos      │              │ │
│                        │  │  - fecha           │              │ │
│                        │  │  - tipo_limpieza   │              │ │
│                        │  │  - es_halal        │              │ │
│                        │  │  - certificado     │              │ │
│                        │  └────────────────────┘              │ │
│                        └──────────────────────────────────────┘ │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                    TrazabilidadPDF                        │   │
│  │  + Título dinámico con normas ISO/OIC                    │   │
│  │  + ID Batch con fecha                                    │   │
│  │  + Todos los insumos con links                           │   │
│  │  + Línea productiva inline                               │   │
│  │  + Registro de limpieza Halal                            │   │
│  │  + Corrección de cálculo de tiempos                      │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 4.2 Flujo de Datos para PDF Halal

```
                    ┌─────────────────┐
                    │ EntregaProducto │
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              ▼              ▼              ▼
        ┌─────────┐    ┌──────────┐    ┌─────────┐
        │ Barril  │    │   Caja   │    │Producto │
        └────┬────┘    │ Envases  │    │(precio) │
             │         └────┬─────┘    └─────────┘
             │              │
             └──────┬───────┘
                    ▼
              ┌───────────┐
              │   Batch   │──────────┐
              └─────┬─────┘          │
                    │                │
         ┌──────────┼──────────┐     │
         ▼          ▼          ▼     ▼
   ┌───────────┐ ┌───────┐ ┌────────────────┐
   │BatchInsumo│ │Receta │ │   BatchActivo  │
   └─────┬─────┘ └───────┘ └───────┬────────┘
         │                         │
         ▼                         ▼
   ┌───────────┐             ┌───────────┐
   │  Insumo   │             │  Activo   │
   │ +url_ficha│             │ +linea_   │
   │ +url_halal│             │  productiva│
   └───────────┘             └─────┬─────┘
                                   │
                                   ▼
                          ┌─────────────────┐
                          │RegistroLimpieza │
                          │ (si es General  │
                          │  usado p/ Halal)│
                          └─────────────────┘
```

---

## 5. Migraciones de Base de Datos

### 5.1 Migración 009: Productos - Línea Productiva

**Archivo:** `db/migrations/009_productos_linea_productiva.sql`

```sql
-- =====================================================
-- Migración 009: Agregar línea productiva a productos
-- Fecha: 2025-12-04
-- Descripción: Permite categorizar productos por línea
--              de producción (alcohólica/sin alcohol)
-- =====================================================

-- Agregar campo linea_productiva
ALTER TABLE productos
ADD COLUMN linea_productiva ENUM('alcoholica', 'analcoholica', 'general')
NOT NULL DEFAULT 'general'
AFTER es_mixto;

-- Índice para consultas por línea
CREATE INDEX idx_productos_linea_productiva ON productos(linea_productiva);

-- Actualizar productos existentes según clasificación
UPDATE productos
SET linea_productiva = 'alcoholica'
WHERE clasificacion IN ('Cerveza', 'Cerveza Artesanal');

UPDATE productos
SET linea_productiva = 'analcoholica'
WHERE clasificacion IN ('Kombucha', 'Agua saborizada', 'Agua fermentada');
```

### 5.2 Migración 010: Batches - Campos ML

**Archivo:** `db/migrations/010_batches_ml_fields.sql`

```sql
-- =====================================================
-- Migración 010: Campos ML para Batches
-- Fecha: 2025-12-04
-- Descripción: Agrega campos para análisis de Machine
--              Learning y métricas de calidad
-- =====================================================

-- Métricas de producto final
ALTER TABLE batches
ADD COLUMN abv_final DECIMAL(4,2) DEFAULT NULL
  COMMENT 'Alcohol by volume final (%)',
ADD COLUMN ibu_final INT DEFAULT NULL
  COMMENT 'IBU medido del producto final',
ADD COLUMN color_ebc INT DEFAULT NULL
  COMMENT 'Color en escala EBC';

-- Métricas de rendimiento
ALTER TABLE batches
ADD COLUMN rendimiento_litros_final FLOAT DEFAULT NULL
  COMMENT 'Volumen final real producido (L)',
ADD COLUMN merma_total_litros FLOAT DEFAULT NULL
  COMMENT 'Total de pérdida en proceso (L)',
ADD COLUMN densidad_final_verificada DECIMAL(5,3) DEFAULT NULL
  COMMENT 'Gravedad final verificada';

-- Métricas de calidad sensorial
ALTER TABLE batches
ADD COLUMN calificacion_sensorial TINYINT DEFAULT NULL
  COMMENT 'Calificación sensorial 1-10',
ADD COLUMN notas_cata TEXT DEFAULT NULL
  COMMENT 'Notas de cata descriptivas';

-- Condiciones ambientales
ALTER TABLE batches
ADD COLUMN temperatura_ambiente_promedio DECIMAL(4,1) DEFAULT NULL
  COMMENT 'Temperatura ambiente durante fermentación (°C)',
ADD COLUMN humedad_relativa_promedio DECIMAL(4,1) DEFAULT NULL
  COMMENT 'Humedad relativa promedio (%)';

-- Índices para consultas analíticas
CREATE INDEX idx_batches_abv ON batches(abv_final);
CREATE INDEX idx_batches_calificacion ON batches(calificacion_sensorial);
CREATE INDEX idx_batches_rendimiento ON batches(rendimiento_litros_final);
```

### 5.3 Migración 011: Insumos - Fichas y Certificados

**Archivo:** `db/migrations/011_insumos_fichas_certificados.sql`

```sql
-- =====================================================
-- Migración 011: Fichas técnicas y certificados Halal
-- Fecha: 2025-12-04
-- Descripción: Agrega soporte para documentación de
--              insumos incluyendo certificación Halal
-- =====================================================

-- URLs de documentación
ALTER TABLE insumos
ADD COLUMN url_ficha_tecnica VARCHAR(500) DEFAULT NULL
  COMMENT 'URL a PDF de ficha técnica',
ADD COLUMN url_certificado_halal VARCHAR(500) DEFAULT NULL
  COMMENT 'URL a certificado Halal';

-- Datos del certificado Halal
ALTER TABLE insumos
ADD COLUMN certificado_halal_numero VARCHAR(100) DEFAULT NULL
  COMMENT 'Número de certificado Halal',
ADD COLUMN certificado_halal_vencimiento DATE DEFAULT NULL
  COMMENT 'Fecha de vencimiento del certificado',
ADD COLUMN certificado_halal_emisor VARCHAR(200) DEFAULT NULL
  COMMENT 'Entidad certificadora Halal',
ADD COLUMN es_halal_certificado BOOLEAN NOT NULL DEFAULT FALSE
  COMMENT 'Flag: insumo tiene certificación Halal vigente';

-- Índice para filtrar insumos Halal
CREATE INDEX idx_insumos_halal ON insumos(es_halal_certificado);
CREATE INDEX idx_insumos_halal_vencimiento ON insumos(certificado_halal_vencimiento);
```

### 5.4 Migración 012: Sistema de Limpiezas

**Archivo:** `db/migrations/012_sistema_limpiezas.sql`

```sql
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
ADD COLUMN uso_exclusivo_halal BOOLEAN NOT NULL DEFAULT FALSE
  COMMENT 'Activo de uso exclusivo para producción Halal';

-- Índices
CREATE INDEX idx_activos_limpieza ON activos(fecha_ultima_limpieza);
CREATE INDEX idx_activos_limpieza_halal ON activos(fecha_ultima_limpieza_halal);

-- -----------------------------------------------------
-- 2. Tabla de registros de limpiezas (historial)
-- -----------------------------------------------------
CREATE TABLE registros_limpiezas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_activos INT NOT NULL
      COMMENT 'FK al activo limpiado',
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
      COMMENT 'Fecha y hora de la limpieza',
    tipo_limpieza ENUM('General', 'Profunda', 'Halal', 'Sanitización', 'CIP') NOT NULL DEFAULT 'General'
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
    es_limpieza_halal BOOLEAN NOT NULL DEFAULT FALSE
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
    INDEX idx_registros_limpiezas_usuario (id_usuarios),

    -- Foreign Key
    CONSTRAINT fk_registros_limpiezas_activos
      FOREIGN KEY (id_activos) REFERENCES activos(id)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de limpiezas de activos';

-- -----------------------------------------------------
-- 3. Tabla de procedimientos de limpieza
-- -----------------------------------------------------
CREATE TABLE procedimientos_limpieza (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE
      COMMENT 'Código del procedimiento (ej: PROC-LIM-001)',
    nombre VARCHAR(200) NOT NULL
      COMMENT 'Nombre del procedimiento',
    tipo ENUM('General', 'Profunda', 'Halal', 'Sanitización', 'CIP') NOT NULL
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
    es_halal_certificado BOOLEAN NOT NULL DEFAULT FALSE
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
('PROC-LIM-001', 'Limpieza General de Fermentador', 'General', 'Limpieza estándar de fermentadores con agua y detergente neutro', 30, FALSE),
('PROC-LIM-002', 'Limpieza Profunda de Fermentador', 'Profunda', 'Limpieza profunda con sanitizante y enjuague múltiple', 60, FALSE),
('PROC-LIM-003', 'Sanitización CIP', 'CIP', 'Clean In Place - Limpieza automatizada sin desarmar', 45, FALSE),
('PROC-LIM-004', 'Limpieza Halal Certificada', 'Halal', 'Limpieza según protocolo Halal con productos certificados y supervisor', 90, TRUE),
('PROC-LIM-005', 'Limpieza Halal Post-Alcohol', 'Halal', 'Limpieza Halal específica después de producción alcohólica', 120, TRUE);
```

---

## 6. Modificaciones a Clases PHP

### 6.1 Producto.php

**Archivo:** `php/classes/Producto.php`

**Cambios:**

```php
<?php
class Producto extends Base {
    // ... campos existentes ...

    // NUEVO: Campo de línea productiva
    public $linea_productiva = "general";

    // ... constructor existente ...

    // NUEVO: Método para obtener label de línea
    public function getLineaProductivaLabel() {
        $lineas = [
            'alcoholica' => 'Línea Alcohólica',
            'analcoholica' => 'Línea Sin Alcohol',
            'general' => 'General'
        ];
        return isset($lineas[$this->linea_productiva])
            ? $lineas[$this->linea_productiva]
            : 'General';
    }

    // NUEVO: Método estático para obtener opciones
    public static function getLineasProductivas() {
        return [
            'alcoholica' => 'Línea Alcohólica',
            'analcoholica' => 'Línea Sin Alcohol',
            'general' => 'General'
        ];
    }

    // NUEVO: Obtener productos por línea
    public static function getByLineaProductiva($linea) {
        return self::getAll("WHERE linea_productiva='" . addslashes($linea) . "' AND estado!='eliminado'");
    }
}
```

### 6.2 Insumo.php

**Archivo:** `php/classes/Insumo.php`

**Cambios:**

```php
<?php
class Insumo extends Base {
    // ... campos existentes ...

    // NUEVOS: Campos de documentación
    public $url_ficha_tecnica = "";
    public $url_certificado_halal = "";
    public $certificado_halal_numero = "";
    public $certificado_halal_vencimiento = "0000-00-00";
    public $certificado_halal_emisor = "";
    public $es_halal_certificado = 0;

    // ... constructor existente ...

    // NUEVO: Verificar si certificado Halal está vigente
    public function tieneCertificadoHalalVigente() {
        if(!$this->es_halal_certificado) {
            return false;
        }
        if(empty($this->certificado_halal_vencimiento) ||
           $this->certificado_halal_vencimiento == '0000-00-00') {
            return false;
        }
        return strtotime($this->certificado_halal_vencimiento) >= strtotime('today');
    }

    // NUEVO: Obtener insumos con certificación Halal vigente
    public static function getInsumosHalalVigentes() {
        return self::getAll("WHERE es_halal_certificado=1
            AND certificado_halal_vencimiento >= CURDATE()
            ORDER BY nombre ASC");
    }

    // NUEVO: Obtener insumos con certificados por vencer (próximos 30 días)
    public static function getInsumosHalalPorVencer($dias = 30) {
        return self::getAll("WHERE es_halal_certificado=1
            AND certificado_halal_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL " . intval($dias) . " DAY)
            ORDER BY certificado_halal_vencimiento ASC");
    }
}
```

### 6.3 Batch.php

**Archivo:** `php/classes/Batch.php`

**Cambios (agregar propiedades):**

```php
<?php
class Batch extends Base {
    // ... campos existentes (63 campos) ...

    // NUEVOS: Campos ML - Métricas de producto final
    public $abv_final = null;
    public $ibu_final = null;
    public $color_ebc = null;

    // NUEVOS: Campos ML - Rendimiento
    public $rendimiento_litros_final = null;
    public $merma_total_litros = null;
    public $densidad_final_verificada = null;

    // NUEVOS: Campos ML - Calidad sensorial
    public $calificacion_sensorial = null;
    public $notas_cata = "";

    // NUEVOS: Campos ML - Condiciones ambientales
    public $temperatura_ambiente_promedio = null;
    public $humedad_relativa_promedio = null;

    // ... constructor y métodos existentes ...

    // NUEVO: Calcular eficiencia del batch
    public function calcularEficiencia() {
        if($this->batch_litros > 0 && $this->rendimiento_litros_final > 0) {
            return round(($this->rendimiento_litros_final / $this->batch_litros) * 100, 2);
        }
        return null;
    }

    // NUEVO: Calcular merma porcentual
    public function calcularMermaPorcentual() {
        if($this->batch_litros > 0 && $this->merma_total_litros !== null) {
            return round(($this->merma_total_litros / $this->batch_litros) * 100, 2);
        }
        return null;
    }

    // NUEVO: Determinar línea productiva del batch
    public function getLineaProductiva() {
        // Obtener del primer activo asignado
        $batches_activos = BatchActivo::getAll("WHERE id_batches='" . $this->id . "' LIMIT 1");
        if(count($batches_activos) > 0) {
            $activo = new Activo($batches_activos[0]->id_activos);
            return $activo->linea_productiva;
        }
        // Fallback: determinar por clasificación de receta
        if($this->id_recetas > 0) {
            $receta = new Receta($this->id_recetas);
            if(in_array($receta->clasificacion, ['Cerveza', 'Cerveza Artesanal'])) {
                return 'alcoholica';
            }
            if(in_array($receta->clasificacion, ['Kombucha', 'Agua saborizada', 'Agua fermentada'])) {
                return 'analcoholica';
            }
        }
        return 'general';
    }
}
```

### 6.4 Activo.php

**Archivo:** `php/classes/Activo.php`

**Cambios (agregar propiedades y métodos):**

```php
<?php
class Activo extends Base {
    // ... campos existentes ...

    // NUEVOS: Campos de limpieza general
    public $fecha_ultima_limpieza = null;
    public $proxima_limpieza = null;
    public $limpieza_procedimiento = "";
    public $limpieza_periodicidad = "Semanal";

    // NUEVOS: Campos de limpieza Halal
    public $fecha_ultima_limpieza_halal = null;
    public $certificado_limpieza_halal = "";
    public $uso_exclusivo_halal = 0;

    // ... constructor y métodos existentes ...

    // NUEVO: Verificar si requiere limpieza
    public function requiereLimpieza() {
        if(empty($this->proxima_limpieza) || $this->proxima_limpieza == '0000-00-00') {
            return true;
        }
        return strtotime($this->proxima_limpieza) <= strtotime('today');
    }

    // NUEVO: Verificar si puede usarse para producción Halal
    public function puedeUsarseParaHalal() {
        // Si es uso exclusivo Halal, siempre puede
        if($this->uso_exclusivo_halal) {
            return true;
        }
        // Si es línea sin alcohol, siempre puede
        if($this->linea_productiva == 'analcoholica') {
            return true;
        }
        // Si es general o alcohólica, verificar limpieza Halal reciente
        return $this->tieneLimpiezaHalalReciente();
    }

    // NUEVO: Verificar limpieza Halal reciente (últimas 24 horas)
    public function tieneLimpiezaHalalReciente($horas = 24) {
        if(empty($this->fecha_ultima_limpieza_halal)) {
            return false;
        }
        $limite = strtotime("-{$horas} hours");
        return strtotime($this->fecha_ultima_limpieza_halal) >= $limite;
    }

    // NUEVO: Obtener última limpieza Halal registrada
    public function getUltimaLimpiezaHalal() {
        $limpiezas = RegistroLimpieza::getAll(
            "WHERE id_activos='" . $this->id . "'
             AND es_limpieza_halal=1
             ORDER BY fecha DESC LIMIT 1"
        );
        return count($limpiezas) > 0 ? $limpiezas[0] : null;
    }

    // NUEVO: Obtener historial de limpiezas
    public function getHistorialLimpiezas($limit = 10) {
        return RegistroLimpieza::getAll(
            "WHERE id_activos='" . $this->id . "'
             ORDER BY fecha DESC
             LIMIT " . intval($limit)
        );
    }

    // NUEVO: Obtener periodicidades disponibles
    public static function getPeriodicidadesLimpieza() {
        return [
            'Diaria' => 'Diaria',
            'Cada 2 días' => 'Cada 2 días',
            'Semanal' => 'Semanal',
            'Quincenal' => 'Quincenal',
            'Mensual' => 'Mensual',
            'Después de cada uso' => 'Después de cada uso'
        ];
    }
}
```

---

## 7. Nuevas Clases a Crear

### 7.1 RegistroLimpieza.php

**Archivo:** `php/classes/RegistroLimpieza.php`

```php
<?php

/**
 * Clase para gestionar registros de limpieza de activos
 * Soporta limpiezas generales y certificadas Halal
 */
class RegistroLimpieza extends Base {

    public $id_activos = 0;
    public $fecha = "";
    public $tipo_limpieza = "General";
    public $procedimiento_utilizado = "";
    public $productos_utilizados = "";
    public $id_usuarios = 0;
    public $id_usuarios_supervisor = 0;
    public $observaciones = "";

    // Campos Halal
    public $es_limpieza_halal = 0;
    public $certificado_numero = "";
    public $certificado_emisor = "";

    // Evidencia
    public $id_media = 0;

    // Metadatos
    public $creada = "";
    public $estado = "activo";

    public $table_name = "registros_limpiezas";
    public $table_fields = array();

    // Objetos relacionados
    public $activo;
    public $usuario;
    public $supervisor;

    public function __construct($id = null) {
        $this->tableName("registros_limpiezas");
        if($id) {
            $this->id = $id;
            $info = $this->getInfoDatabase('id');
            $this->setProperties($info);
        } else {
            $this->creada = date('Y-m-d H:i:s');
            $this->fecha = date('Y-m-d H:i:s');
        }
    }

    /**
     * Obtener tipos de limpieza disponibles
     */
    public static function getTiposLimpieza() {
        return [
            'General' => 'Limpieza General',
            'Profunda' => 'Limpieza Profunda',
            'Halal' => 'Limpieza Halal Certificada',
            'Sanitización' => 'Sanitización',
            'CIP' => 'Clean In Place (CIP)'
        ];
    }

    /**
     * Cargar objetos relacionados
     */
    public function cargarRelaciones() {
        if($this->id_activos > 0) {
            $this->activo = new Activo($this->id_activos);
        }
        if($this->id_usuarios > 0) {
            $this->usuario = new Usuario($this->id_usuarios);
        }
        if($this->id_usuarios_supervisor > 0) {
            $this->supervisor = new Usuario($this->id_usuarios_supervisor);
        }
    }

    /**
     * Registrar nueva limpieza y actualizar activo
     */
    public function registrar() {
        // Guardar registro
        $this->save();

        // Actualizar activo con última limpieza
        if($this->id_activos > 0) {
            $activo = new Activo($this->id_activos);
            $activo->fecha_ultima_limpieza = $this->fecha;

            if($this->es_limpieza_halal) {
                $activo->fecha_ultima_limpieza_halal = $this->fecha;
                $activo->certificado_limpieza_halal = $this->certificado_numero;
            }

            // Calcular próxima limpieza según periodicidad
            $activo->proxima_limpieza = $this->calcularProximaLimpieza(
                $this->fecha,
                $activo->limpieza_periodicidad
            );

            $activo->save();
        }

        return $this->id;
    }

    /**
     * Calcular fecha de próxima limpieza según periodicidad
     */
    private function calcularProximaLimpieza($fecha_actual, $periodicidad) {
        $fecha = new DateTime($fecha_actual);

        switch($periodicidad) {
            case 'Diaria':
                $fecha->modify('+1 day');
                break;
            case 'Cada 2 días':
                $fecha->modify('+2 days');
                break;
            case 'Semanal':
                $fecha->modify('+1 week');
                break;
            case 'Quincenal':
                $fecha->modify('+2 weeks');
                break;
            case 'Mensual':
                $fecha->modify('+1 month');
                break;
            default:
                $fecha->modify('+1 week');
        }

        return $fecha->format('Y-m-d');
    }

    /**
     * Obtener última limpieza de un activo
     */
    public static function getUltimaPorActivo($id_activos, $tipo = null) {
        $where = "WHERE id_activos='" . addslashes($id_activos) . "'";
        if($tipo) {
            $where .= " AND tipo_limpieza='" . addslashes($tipo) . "'";
        }
        $where .= " ORDER BY fecha DESC LIMIT 1";

        $registros = self::getAll($where);
        return count($registros) > 0 ? $registros[0] : null;
    }

    /**
     * Obtener última limpieza Halal de un activo
     */
    public static function getUltimaHalalPorActivo($id_activos) {
        $registros = self::getAll(
            "WHERE id_activos='" . addslashes($id_activos) . "'
             AND es_limpieza_halal=1
             ORDER BY fecha DESC LIMIT 1"
        );
        return count($registros) > 0 ? $registros[0] : null;
    }

    /**
     * Validar si un activo tiene limpieza Halal válida
     * para poder usarse en producción sin alcohol
     */
    public static function validarLimpiezaHalalParaProduccion($id_activos, $horas_maximas = 24) {
        $ultima = self::getUltimaHalalPorActivo($id_activos);

        if(!$ultima) {
            return [
                'valido' => false,
                'mensaje' => 'No hay registro de limpieza Halal para este activo'
            ];
        }

        $limite = strtotime("-{$horas_maximas} hours");
        $fecha_limpieza = strtotime($ultima->fecha);

        if($fecha_limpieza < $limite) {
            $horas_transcurridas = round((time() - $fecha_limpieza) / 3600, 1);
            return [
                'valido' => false,
                'mensaje' => "La última limpieza Halal fue hace {$horas_transcurridas} horas (máximo permitido: {$horas_maximas}h)",
                'ultima_limpieza' => $ultima
            ];
        }

        return [
            'valido' => true,
            'mensaje' => 'Limpieza Halal válida',
            'ultima_limpieza' => $ultima
        ];
    }

    /**
     * Obtener activos que requieren limpieza Halal
     * antes de poder usarse para producción sin alcohol
     */
    public static function getActivosRequierenLimpiezaHalal() {
        // Activos de línea "general" que no tienen limpieza Halal reciente
        $query = "SELECT a.* FROM activos a
                  WHERE a.linea_productiva = 'general'
                  AND a.clase = 'Fermentador'
                  AND a.estado = 'Activo'
                  AND (
                      a.fecha_ultima_limpieza_halal IS NULL
                      OR a.fecha_ultima_limpieza_halal < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                  )
                  ORDER BY a.nombre ASC";

        $mysqli = $GLOBALS['mysqli'];
        $result = $mysqli->query($query);
        $activos = [];

        while($row = mysqli_fetch_assoc($result)) {
            $activo = new Activo();
            $activo->setProperties($row);
            $activos[] = $activo;
        }

        return $activos;
    }
}
```

### 7.2 ProcedimientoLimpieza.php

**Archivo:** `php/classes/ProcedimientoLimpieza.php`

```php
<?php

/**
 * Clase para gestionar procedimientos de limpieza
 */
class ProcedimientoLimpieza extends Base {

    public $codigo = "";
    public $nombre = "";
    public $tipo = "General";
    public $descripcion = "";
    public $pasos = "";
    public $productos_requeridos = "";
    public $tiempo_estimado_minutos = 0;
    public $frecuencia_recomendada = "";
    public $aplica_a_clases = "";
    public $es_halal_certificado = 0;
    public $version = "1.0";
    public $creada = "";
    public $actualizada = "";
    public $estado = "activo";

    public $table_name = "procedimientos_limpieza";
    public $table_fields = array();

    public function __construct($id = null) {
        $this->tableName("procedimientos_limpieza");
        if($id) {
            $this->id = $id;
            $info = $this->getInfoDatabase('id');
            $this->setProperties($info);
        } else {
            $this->creada = date('Y-m-d H:i:s');
        }
    }

    /**
     * Obtener pasos como array
     */
    public function getPasosArray() {
        if(empty($this->pasos)) {
            return [];
        }
        $decoded = json_decode($this->pasos, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Establecer pasos desde array
     */
    public function setPasosArray($pasos) {
        $this->pasos = json_encode($pasos, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtener procedimientos por tipo
     */
    public static function getByTipo($tipo) {
        return self::getAll("WHERE tipo='" . addslashes($tipo) . "' AND estado='activo' ORDER BY nombre ASC");
    }

    /**
     * Obtener procedimientos Halal
     */
    public static function getProcedimientosHalal() {
        return self::getAll("WHERE es_halal_certificado=1 AND estado='activo' ORDER BY nombre ASC");
    }

    /**
     * Obtener procedimientos aplicables a una clase de activo
     */
    public static function getByClaseActivo($clase) {
        return self::getAll(
            "WHERE estado='activo'
             AND (aplica_a_clases IS NULL
                  OR aplica_a_clases = ''
                  OR aplica_a_clases LIKE '%" . addslashes($clase) . "%')
             ORDER BY nombre ASC"
        );
    }
}
```

---

## 8. Modificaciones a Templates

### 8.1 detalle-insumos.php

**Archivo:** `templates/detalle-insumos.php`

**Agregar sección de documentación después de los campos existentes:**

```php
<!-- SECCIÓN: Documentación y Certificaciones -->
<div class="card mt-4">
  <div class="card-body">
    <h5 class="card-title mb-4">
      <i class="fas fa-file-alt"></i> Documentación y Certificaciones
    </h5>

    <div class="row">
      <!-- Ficha Técnica -->
      <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">URL Ficha Técnica:</label>
        <div class="input-group">
          <input type="url" class="form-control" name="url_ficha_tecnica"
                 placeholder="https://ejemplo.com/ficha.pdf">
          <button class="btn btn-outline-secondary" type="button" id="ver-ficha-btn"
                  title="Ver ficha técnica" disabled>
            <i class="fas fa-external-link-alt"></i>
          </button>
        </div>
        <small class="text-muted">Link al PDF de la ficha técnica del proveedor</small>
      </div>

      <!-- Subir Ficha (alternativa) -->
      <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">O subir archivo:</label>
        <input type="file" class="form-control" name="ficha_tecnica_file"
               accept=".pdf,.doc,.docx">
      </div>
    </div>

    <hr class="my-4">

    <!-- Certificación Halal -->
    <h6 class="mb-3">
      <i class="fas fa-certificate"></i> Certificación Halal
    </h6>

    <div class="row">
      <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="es_halal_certificado"
                 id="es_halal_certificado" value="1">
          <label class="form-check-label" for="es_halal_certificado">
            <strong>Insumo Halal Certificado</strong>
          </label>
        </div>
      </div>
    </div>

    <div class="row halal-fields" style="display: none;">
      <div class="col-md-4 mb-3">
        <label class="form-label">Número de Certificado:</label>
        <input type="text" class="form-control" name="certificado_halal_numero"
               placeholder="HALAL-2025-001">
      </div>

      <div class="col-md-4 mb-3">
        <label class="form-label">Entidad Certificadora:</label>
        <input type="text" class="form-control" name="certificado_halal_emisor"
               placeholder="Ej: Islamic Food Council">
      </div>

      <div class="col-md-4 mb-3">
        <label class="form-label">Fecha de Vencimiento:</label>
        <input type="date" class="form-control" name="certificado_halal_vencimiento">
      </div>

      <div class="col-md-12 mb-3">
        <label class="form-label">URL Certificado Halal:</label>
        <div class="input-group">
          <input type="url" class="form-control" name="url_certificado_halal"
                 placeholder="https://ejemplo.com/certificado-halal.pdf">
          <button class="btn btn-outline-success" type="button" id="ver-halal-btn"
                  title="Ver certificado" disabled>
            <i class="fas fa-external-link-alt"></i>
          </button>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
// Toggle campos Halal
$('#es_halal_certificado').change(function() {
  if($(this).is(':checked')) {
    $('.halal-fields').slideDown();
  } else {
    $('.halal-fields').slideUp();
  }
});

// Habilitar botones de ver documento si hay URL
$('input[name="url_ficha_tecnica"]').on('input', function() {
  $('#ver-ficha-btn').prop('disabled', !$(this).val());
});

$('input[name="url_certificado_halal"]').on('input', function() {
  $('#ver-halal-btn').prop('disabled', !$(this).val());
});

// Abrir URLs en nueva pestaña
$('#ver-ficha-btn').click(function() {
  var url = $('input[name="url_ficha_tecnica"]').val();
  if(url) window.open(url, '_blank');
});

$('#ver-halal-btn').click(function() {
  var url = $('input[name="url_certificado_halal"]').val();
  if(url) window.open(url, '_blank');
});

// Cargar estado inicial
$(document).ready(function() {
  if(obj.es_halal_certificado == 1) {
    $('#es_halal_certificado').prop('checked', true);
    $('.halal-fields').show();
  }
  if(obj.url_ficha_tecnica) {
    $('#ver-ficha-btn').prop('disabled', false);
  }
  if(obj.url_certificado_halal) {
    $('#ver-halal-btn').prop('disabled', false);
  }
});
</script>
```

### 8.2 detalle-activos.php

**Archivo:** `templates/detalle-activos.php`

**Agregar sección de limpiezas después de la sección de mantención:**

```php
<!-- SECCIÓN: Limpiezas -->
<div class="col-12 mb-4 mt-5">
  <h1 class="h4 mb-0 text-gray-800">
    <b><i class="fas fa-broom"></i> Limpiezas</b>
  </h1>
</div>

<div class="col-12 row">
  <div class="col-md-3 mb-1">Periodicidad:</div>
  <div class="col-md-3 mb-1">
    <select class="form-control" name="limpieza_periodicidad">
      <?php
      $periodicidades = Activo::getPeriodicidadesLimpieza();
      foreach($periodicidades as $key => $label) {
        echo "<option value='{$key}'>{$label}</option>";
      }
      ?>
    </select>
  </div>
</div>

<div class="col-12 row">
  <div class="col-md-3 mb-1">Última limpieza:</div>
  <div class="col-md-3 mb-1">
    <input type="datetime-local" class="form-control" name="fecha_ultima_limpieza" readonly>
  </div>
  <div class="col-md-3 mb-1">Próxima limpieza:</div>
  <div class="col-md-3 mb-1">
    <input type="date" class="form-control" name="proxima_limpieza">
  </div>
</div>

<div class="col-12 mt-1 mb-1">
  Procedimiento de limpieza:
  <br/><br/>
  <textarea class="form-control" name="limpieza_procedimiento" rows="3"
            placeholder="Describir el procedimiento estándar de limpieza para este activo"></textarea>
</div>

<!-- Campos específicos Halal -->
<?php if($obj->linea_productiva == 'general' || $obj->linea_productiva == 'analcoholica'): ?>
<div class="col-12 row mt-4">
  <div class="col-12 mb-2">
    <h6><i class="fas fa-certificate text-success"></i> Limpieza Halal</h6>
  </div>

  <div class="col-md-4 mb-1">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="uso_exclusivo_halal"
             id="uso_exclusivo_halal" value="1" <?= $obj->uso_exclusivo_halal ? 'checked' : ''; ?>>
      <label class="form-check-label" for="uso_exclusivo_halal">
        Uso exclusivo para producción Halal
      </label>
    </div>
  </div>

  <div class="col-md-4 mb-1">
    <label>Última limpieza Halal:</label>
    <input type="datetime-local" class="form-control" name="fecha_ultima_limpieza_halal" readonly>
  </div>

  <div class="col-md-4 mb-1">
    <label>Certificado limpieza Halal:</label>
    <input type="text" class="form-control" name="certificado_limpieza_halal"
           placeholder="Número de certificado" readonly>
  </div>
</div>
<?php endif; ?>

<!-- Historial de Limpiezas -->
<div class="col-12 mt-4">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h6 class="mb-0"><i class="fas fa-history"></i> Historial de Limpiezas</h6>
      <button class="btn btn-primary btn-sm" id="registrar-limpieza-btn">
        <i class="fas fa-plus"></i> Registrar Limpieza
      </button>
    </div>
    <div class="card-body">
      <table class="table table-sm table-hover" id="tabla-historial-limpiezas">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Usuario</th>
            <th>Halal</th>
            <th>Certificado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $historial = $obj->getHistorialLimpiezas(10);
          foreach($historial as $limpieza):
            $usuario = new Usuario($limpieza->id_usuarios);
          ?>
          <tr>
            <td><?= date('d/m/Y H:i', strtotime($limpieza->fecha)); ?></td>
            <td>
              <span class="badge bg-<?= $limpieza->tipo_limpieza == 'Halal' ? 'success' : 'secondary'; ?>">
                <?= $limpieza->tipo_limpieza; ?>
              </span>
            </td>
            <td><?= $usuario->nombre; ?></td>
            <td>
              <?php if($limpieza->es_limpieza_halal): ?>
                <i class="fas fa-check-circle text-success"></i>
              <?php else: ?>
                <i class="fas fa-minus text-muted"></i>
              <?php endif; ?>
            </td>
            <td><?= $limpieza->certificado_numero ?: '-'; ?></td>
            <td>
              <button class="btn btn-xs btn-outline-info ver-limpieza-btn"
                      data-id="<?= $limpieza->id; ?>">
                <i class="fas fa-eye"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(count($historial) == 0): ?>
          <tr>
            <td colspan="6" class="text-center text-muted">
              No hay registros de limpieza
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: Registrar Limpieza -->
<div class="modal fade" id="registrar-limpieza-modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-broom"></i> Registrar Limpieza
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="form-registrar-limpieza">
          <input type="hidden" name="id_activos" value="<?= $obj->id; ?>">

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Fecha y Hora:</label>
              <input type="datetime-local" class="form-control" name="fecha"
                     value="<?= date('Y-m-d\TH:i'); ?>" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Tipo de Limpieza:</label>
              <select class="form-control" name="tipo_limpieza" id="tipo_limpieza_select" required>
                <?php
                $tipos = RegistroLimpieza::getTiposLimpieza();
                foreach($tipos as $key => $label) {
                  echo "<option value='{$key}'>{$label}</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Procedimiento Utilizado:</label>
              <select class="form-control" name="procedimiento_utilizado">
                <option value="">-- Seleccionar procedimiento --</option>
                <?php
                $procedimientos = ProcedimientoLimpieza::getAll("WHERE estado='activo' ORDER BY nombre");
                foreach($procedimientos as $proc) {
                  echo "<option value='{$proc->codigo}'>{$proc->codigo} - {$proc->nombre}</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Productos Utilizados:</label>
              <textarea class="form-control" name="productos_utilizados" rows="2"
                        placeholder="Lista de productos de limpieza utilizados"></textarea>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Observaciones:</label>
              <textarea class="form-control" name="observaciones" rows="2"></textarea>
            </div>
          </div>

          <!-- Campos Halal (ocultos por defecto) -->
          <div id="campos-halal" style="display: none;">
            <hr>
            <h6 class="text-success"><i class="fas fa-certificate"></i> Certificación Halal</h6>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Número de Certificado:</label>
                <input type="text" class="form-control" name="certificado_numero"
                       placeholder="HALAL-LIM-2025-001">
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Entidad Certificadora:</label>
                <input type="text" class="form-control" name="certificado_emisor">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Supervisor:</label>
                <select class="form-control" name="id_usuarios_supervisor">
                  <option value="">-- Seleccionar supervisor --</option>
                  <?php
                  $supervisores = Usuario::getAll("WHERE nivel IN ('Administrador', 'Jefe de Planta') ORDER BY nombre");
                  foreach($supervisores as $sup) {
                    echo "<option value='{$sup->id}'>{$sup->nombre}</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar-limpieza-btn">
          <i class="fas fa-save"></i> Registrar Limpieza
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Mostrar/ocultar campos Halal según tipo seleccionado
$('#tipo_limpieza_select').change(function() {
  if($(this).val() == 'Halal') {
    $('#campos-halal').slideDown();
    $('input[name="certificado_numero"]').prop('required', true);
  } else {
    $('#campos-halal').slideUp();
    $('input[name="certificado_numero"]').prop('required', false);
  }
});

// Abrir modal
$('#registrar-limpieza-btn').click(function() {
  $('#registrar-limpieza-modal').modal('show');
});

// Guardar limpieza
$('#guardar-limpieza-btn').click(function() {
  var $btn = $(this);
  $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

  var data = {
    id_activos: $('input[name="id_activos"]').val(),
    fecha: $('input[name="fecha"]').val(),
    tipo_limpieza: $('#tipo_limpieza_select').val(),
    procedimiento_utilizado: $('select[name="procedimiento_utilizado"]').val(),
    productos_utilizados: $('textarea[name="productos_utilizados"]').val(),
    observaciones: $('textarea[name="observaciones"]').val(),
    es_limpieza_halal: $('#tipo_limpieza_select').val() == 'Halal' ? 1 : 0,
    certificado_numero: $('input[name="certificado_numero"]').val(),
    certificado_emisor: $('input[name="certificado_emisor"]').val(),
    id_usuarios_supervisor: $('select[name="id_usuarios_supervisor"]').val()
  };

  $.post('./ajax/ajax_registrarLimpieza.php', data, function(response) {
    if(response.status == 'OK') {
      window.location.reload();
    } else {
      alert('Error: ' + response.mensaje);
      $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Registrar Limpieza');
    }
  }, 'json').fail(function() {
    alert('Error de conexión');
    $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Registrar Limpieza');
  });
});
</script>
```

### 8.3 nuevo-productos.php y detalle-productos.php

**Agregar selector de línea productiva:**

```php
<div class="col-6 mb-1">
  Línea Productiva:
</div>
<div class="col-6 mb-1">
  <select class="form-control" name="linea_productiva">
    <?php
    $lineas = Producto::getLineasProductivas();
    foreach($lineas as $key => $label) {
      echo "<option value='{$key}'>{$label}</option>";
    }
    ?>
  </select>
</div>
```

### 8.4 nuevo-batches.php

**Agregar sección de métricas de calidad (ML) al final del formulario:**

```php
<!-- SECCIÓN: Métricas de Calidad (ML) -->
<div class="card mt-4">
  <div class="card-header">
    <h6 class="mb-0"><i class="fas fa-chart-line"></i> Métricas de Calidad (Opcional)</h6>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-3 mb-3">
        <label class="form-label">ABV Final (%):</label>
        <input type="number" class="form-control" name="abv_final" step="0.01" min="0" max="20"
               placeholder="Ej: 5.5">
      </div>

      <div class="col-md-3 mb-3">
        <label class="form-label">IBU Final:</label>
        <input type="number" class="form-control" name="ibu_final" min="0" max="150"
               placeholder="Ej: 45">
      </div>

      <div class="col-md-3 mb-3">
        <label class="form-label">Color EBC:</label>
        <input type="number" class="form-control" name="color_ebc" min="0" max="100"
               placeholder="Ej: 12">
      </div>

      <div class="col-md-3 mb-3">
        <label class="form-label">Calificación Sensorial (1-10):</label>
        <input type="number" class="form-control" name="calificacion_sensorial" min="1" max="10"
               placeholder="1-10">
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Rendimiento Final (L):</label>
        <input type="number" class="form-control" name="rendimiento_litros_final" step="0.1" min="0"
               placeholder="Volumen final producido">
      </div>

      <div class="col-md-4 mb-3">
        <label class="form-label">Merma Total (L):</label>
        <input type="number" class="form-control" name="merma_total_litros" step="0.1" min="0"
               placeholder="Pérdida total">
      </div>

      <div class="col-md-4 mb-3">
        <label class="form-label">Densidad Final Verificada:</label>
        <input type="number" class="form-control" name="densidad_final_verificada" step="0.001" min="0.990" max="1.200"
               placeholder="Ej: 1.012">
      </div>
    </div>

    <div class="row">
      <div class="col-md-12 mb-3">
        <label class="form-label">Notas de Cata:</label>
        <textarea class="form-control" name="notas_cata" rows="2"
                  placeholder="Describir características sensoriales: aroma, sabor, cuerpo, etc."></textarea>
      </div>
    </div>
  </div>
</div>
```

---

## 9. Endpoints AJAX

### 9.1 ajax_registrarLimpieza.php

**Archivo:** `ajax/ajax_registrarLimpieza.php`

```php
<?php
/**
 * Endpoint para registrar limpieza de un activo
 */

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

// Validar permisos
$niveles_permitidos = ['Administrador', 'Jefe de Planta', 'Jefe de Cocina', 'Operario'];
if(!in_array($usuario->nivel, $niveles_permitidos)) {
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'Sin permisos']);
    exit;
}

// Validar datos requeridos
if(!isset($_POST['id_activos']) || empty($_POST['id_activos'])) {
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'ID de activo requerido']);
    exit;
}

if(!isset($_POST['tipo_limpieza']) || empty($_POST['tipo_limpieza'])) {
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'Tipo de limpieza requerido']);
    exit;
}

// Crear registro
$limpieza = new RegistroLimpieza();
$limpieza->id_activos = $_POST['id_activos'];
$limpieza->fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d H:i:s');
$limpieza->tipo_limpieza = $_POST['tipo_limpieza'];
$limpieza->procedimiento_utilizado = $_POST['procedimiento_utilizado'] ?? '';
$limpieza->productos_utilizados = $_POST['productos_utilizados'] ?? '';
$limpieza->id_usuarios = $usuario->id;
$limpieza->observaciones = $_POST['observaciones'] ?? '';

// Campos Halal
$limpieza->es_limpieza_halal = isset($_POST['es_limpieza_halal']) && $_POST['es_limpieza_halal'] == 1 ? 1 : 0;
$limpieza->certificado_numero = $_POST['certificado_numero'] ?? '';
$limpieza->certificado_emisor = $_POST['certificado_emisor'] ?? '';
$limpieza->id_usuarios_supervisor = $_POST['id_usuarios_supervisor'] ?? 0;

// Registrar (guarda y actualiza activo)
$id = $limpieza->registrar();

if($id) {
    // Registrar en historial
    Historial::guardarAccion(
        "Limpieza registrada para activo #" . $_POST['id_activos'] . " - Tipo: " . $_POST['tipo_limpieza'],
        $usuario
    );

    echo json_encode([
        'status' => 'OK',
        'mensaje' => 'Limpieza registrada correctamente',
        'id' => $id
    ]);
} else {
    echo json_encode([
        'status' => 'ERROR',
        'mensaje' => 'Error al guardar el registro'
    ]);
}
```

### 9.2 ajax_obtenerHistorialLimpiezas.php

**Archivo:** `ajax/ajax_obtenerHistorialLimpiezas.php`

```php
<?php
/**
 * Endpoint para obtener historial de limpiezas de un activo
 */

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

if(!isset($_GET['id_activos'])) {
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'ID requerido']);
    exit;
}

$id_activos = $_GET['id_activos'];
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;

$historial = RegistroLimpieza::getAll(
    "WHERE id_activos='" . addslashes($id_activos) . "'
     ORDER BY fecha DESC
     LIMIT " . $limit
);

$resultado = [];
foreach($historial as $limpieza) {
    $limpieza->cargarRelaciones();
    $resultado[] = [
        'id' => $limpieza->id,
        'fecha' => $limpieza->fecha,
        'fecha_formato' => date('d/m/Y H:i', strtotime($limpieza->fecha)),
        'tipo_limpieza' => $limpieza->tipo_limpieza,
        'procedimiento' => $limpieza->procedimiento_utilizado,
        'usuario' => $limpieza->usuario ? $limpieza->usuario->nombre : 'N/A',
        'es_halal' => $limpieza->es_limpieza_halal,
        'certificado' => $limpieza->certificado_numero,
        'observaciones' => $limpieza->observaciones
    ];
}

echo json_encode([
    'status' => 'OK',
    'data' => $resultado,
    'total' => count($resultado)
]);
```

### 9.3 ajax_validarLimpiezaHalal.php

**Archivo:** `ajax/ajax_validarLimpiezaHalal.php`

```php
<?php
/**
 * Endpoint para validar si un activo tiene limpieza Halal válida
 * Usado antes de iniciar producción en línea sin alcohol
 */

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

if(!isset($_GET['id_activos'])) {
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'ID requerido']);
    exit;
}

$id_activos = $_GET['id_activos'];
$horas_maximas = isset($_GET['horas']) ? intval($_GET['horas']) : 24;

// Obtener activo
$activo = new Activo($id_activos);

// Si es uso exclusivo Halal o línea sin alcohol, siempre válido
if($activo->uso_exclusivo_halal || $activo->linea_productiva == 'analcoholica') {
    echo json_encode([
        'status' => 'OK',
        'valido' => true,
        'mensaje' => 'Activo de uso exclusivo para producción sin alcohol',
        'requiere_limpieza' => false
    ]);
    exit;
}

// Validar limpieza Halal
$validacion = RegistroLimpieza::validarLimpiezaHalalParaProduccion($id_activos, $horas_maximas);

echo json_encode([
    'status' => 'OK',
    'valido' => $validacion['valido'],
    'mensaje' => $validacion['mensaje'],
    'requiere_limpieza' => !$validacion['valido'],
    'ultima_limpieza' => isset($validacion['ultima_limpieza']) ? [
        'fecha' => $validacion['ultima_limpieza']->fecha,
        'certificado' => $validacion['ultima_limpieza']->certificado_numero
    ] : null
]);
```

---

## 10. Modificaciones al PDF de Trazabilidad

### 10.1 Resumen de Cambios en TrazabilidadPDF.php

**Archivo:** `php/classes/TrazabilidadPDF.php`

Los cambios principales son:

1. **Título dinámico con normas ISO/OIC**
2. **Agregar ID Batch con fecha**
3. **Cambiar "Código" a "Código de barril/caja"**
4. **Cambiar "granos" por "insumos" con links**
5. **Línea productiva inline (sin título)**
6. **Corregir cálculo de tiempos**
7. **Agregar registro de limpieza Halal**

### 10.2 Código de Implementación

```php
<?php
// Agregar al inicio de la clase

/**
 * Determinar línea productiva del producto
 * @return string 'alcoholica' | 'analcoholica' | 'general'
 */
private function determinarLineaProductiva() {
    // Prioridad 1: Desde el producto
    if($this->entrega_producto->id_productos > 0) {
        $producto = new Producto($this->entrega_producto->id_productos);
        if(!empty($producto->linea_productiva)) {
            return $producto->linea_productiva;
        }
    }

    // Prioridad 2: Desde el activo de fermentación
    if(isset($this->datos['fermentacion']['linea_productiva_raw'])) {
        return $this->datos['fermentacion']['linea_productiva_raw'];
    }

    // Prioridad 3: Inferir de la receta
    if(isset($this->datos['producto']['receta_clasificacion'])) {
        $clasificacion = $this->datos['producto']['receta_clasificacion'];
        if(in_array($clasificacion, ['Cerveza', 'Cerveza Artesanal'])) {
            return 'alcoholica';
        }
        if(in_array($clasificacion, ['Kombucha', 'Agua saborizada', 'Agua fermentada'])) {
            return 'analcoholica';
        }
    }

    return 'general';
}

/**
 * Obtener normas ISO según línea productiva
 */
private function getNormasISO() {
    $linea = $this->determinarLineaProductiva();

    if($linea == 'analcoholica') {
        return 'OIC/SMIIC 1 / GSO 2055-1 / ISO 22005 / ISO 22000 / FSSC 22000 / BRCGS / IFS';
    }

    return 'ISO 22005 / ISO 22000 / FSSC 22000 / BRCGS / IFS';
}

/**
 * Obtener TODOS los insumos (no solo granos)
 */
private function obtenerTodosInsumos($batch_id) {
    $insumos_resultado = array();
    $batch_insumos = BatchInsumo::getAll("WHERE id_batches='" . $batch_id . "'");

    foreach($batch_insumos as $bi) {
        $insumo = new Insumo($bi->id_insumos);
        $tipo_insumo = new TipoDeInsumo($insumo->id_tipos_de_insumos);

        $insumos_resultado[] = array(
            'nombre' => $insumo->nombre,
            'tipo' => $tipo_insumo->nombre,
            'cantidad' => $bi->cantidad,
            'unidad' => $insumo->unidad_de_medida,
            'etapa' => $bi->etapa,
            'url_ficha' => $insumo->url_ficha_tecnica,
            'url_halal' => $insumo->url_certificado_halal,
            'es_halal' => $insumo->es_halal_certificado
        );
    }

    return $insumos_resultado;
}

/**
 * Obtener última limpieza Halal relevante para el batch
 */
private function obtenerLimpiezaHalal($id_activos, $fecha_batch) {
    if($id_activos <= 0) return null;

    // Buscar limpieza Halal anterior a la fecha del batch
    $limpiezas = RegistroLimpieza::getAll(
        "WHERE id_activos='" . $id_activos . "'
         AND es_limpieza_halal=1
         AND fecha <= '" . $fecha_batch . "'
         ORDER BY fecha DESC LIMIT 1"
    );

    return count($limpiezas) > 0 ? $limpiezas[0] : null;
}
```

### 10.3 Modificación del método generarHTML()

```php
public function generarHTML() {
    $d = $this->datos;
    $normas = $this->getNormasISO();
    $linea_productiva = $this->determinarLineaProductiva();

    // Logo
    $logo_path = $GLOBALS['base_dir'] . '/media/images/logo.png';
    $logo_html = file_exists($logo_path)
        ? '<img src="' . $logo_path . '" style="max-height: 60px;">'
        : '';

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <style>
        /* ... estilos existentes ... */

        /* NUEVO: Estilos para normas */
        .normas-iso {
          font-size: 9px;
          color: #666;
          margin-top: 5px;
          letter-spacing: 0.5px;
        }

        /* NUEVO: Estilos para links de documentos */
        .doc-link {
          display: inline-block;
          width: 16px;
          height: 16px;
          text-align: center;
          font-size: 10px;
          text-decoration: none;
          margin-left: 3px;
        }
        .doc-link.ficha { color: #3498db; }
        .doc-link.halal { color: #27ae60; }

        /* NUEVO: Badge inline */
        .badge-inline {
          display: inline-block;
          padding: 1px 5px;
          border-radius: 3px;
          font-size: 8px;
          font-weight: bold;
          margin-left: 5px;
          vertical-align: middle;
        }

        /* NUEVO: Sección de limpieza */
        .limpieza-halal {
          background: #e8f5e9;
          border-left: 3px solid #27ae60;
          padding: 8px;
          margin-top: 10px;
          font-size: 10px;
        }
      </style>
    </head>
    <body>

    <!-- HEADER CON NORMAS -->
    <div class="header">
      ' . $logo_html . '
      <h1>CERTIFICADO DE TRAZABILIDAD DE PRODUCTO</h1>
      <div class="normas-iso">' . $normas . '</div>
      <div class="subtitulo">Cerveza Cocholgue</div>
    </div>

    <!-- DATOS DE LA ENTREGA (sin cambios) -->
    ...

    <!-- DATOS DEL PRODUCTO (modificado) -->
    <div class="seccion">
      <div class="seccion-titulo">DATOS DEL PRODUCTO</div>
      <div class="seccion-contenido">
        <table>
          <tr>
            <td>Tipo:</td>
            <td>' . htmlspecialchars($d['producto']['tipo']) . '</td>
          </tr>

          <!-- NUEVO: ID Batch con fecha -->
          <tr>
            <td>Batch:</td>
            <td><strong>#' . htmlspecialchars($d['producto']['batch_nombre'] ?? $d['coccion']['batch_nombre']) . '</strong>
                (' . $this->formatearSoloFecha($d['coccion']['fecha']) . ')</td>
          </tr>

          <!-- MODIFICADO: Código de barril/caja -->
          <tr>
            <td>' . ($d['producto']['tipo'] == 'Barril' ? 'Código de barril:' : 'Código de caja:') . '</td>
            <td><strong>' . htmlspecialchars($d['producto']['codigo']) . '</strong></td>
          </tr>

          <!-- ... resto de campos ... -->
        </table>
      </div>
    </div>

    <!-- LÍNEA DE TIEMPO (modificado) -->
    <div class="seccion">
      <div class="seccion-titulo">LÍNEA DE TIEMPO DEL PROCESO</div>
      <div class="seccion-contenido">
        <div class="timeline">';

    // COCCIÓN - con todos los insumos
    if(isset($d['coccion'])) {
        $html .= '
          <div class="timeline-item">
            <div class="timeline-titulo">COCCIÓN</div>
            <div class="timeline-detalle">
              <strong>Fecha:</strong> ' . $this->formatearSoloFecha($d['coccion']['fecha']) . '<br>
              <strong>Batch:</strong> ' . htmlspecialchars($d['coccion']['batch_nombre']) . '<br>
              <strong>Insumos:</strong><br>';

        // MODIFICADO: Mostrar todos los insumos con links
        if(!empty($d['coccion']['insumos'])) {
            $html .= '<ul style="margin: 5px 0; padding-left: 15px; font-size: 10px;">';
            foreach($d['coccion']['insumos'] as $insumo) {
                $html .= '<li>' . htmlspecialchars($insumo['nombre']);
                if(!empty($insumo['cantidad'])) {
                    $html .= ' (' . $insumo['cantidad'] . ' ' . $insumo['unidad'] . ')';
                }
                // Links a documentos
                if(!empty($insumo['url_ficha'])) {
                    $html .= ' <a href="' . htmlspecialchars($insumo['url_ficha']) . '" class="doc-link ficha" title="Ficha técnica">📄</a>';
                }
                if($insumo['es_halal'] && !empty($insumo['url_halal'])) {
                    $html .= ' <a href="' . htmlspecialchars($insumo['url_halal']) . '" class="doc-link halal" title="Certificado Halal">☪️</a>';
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '</div></div>';
    }

    // FERMENTACIÓN - con línea productiva inline
    if(isset($d['fermentacion'])) {
        $linea_badge = $this->getBadgeLineaInline($d['fermentacion']['linea_productiva']);

        $html .= '
          <div class="timeline-item">
            <div class="timeline-titulo">FERMENTACIÓN</div>
            <div class="timeline-detalle">
              <strong>Inicio:</strong> ' . $this->formatearFecha($d['fermentacion']['fecha']) . '<br>
              <strong>Fermentador:</strong> ' . htmlspecialchars($d['fermentacion']['activo_codigo'] ?: $d['fermentacion']['activo_nombre']) .
              ' (' . $d['fermentacion']['activo_capacidad'] . ') ' . $linea_badge . '
            </div>
          </div>';
    }

    // ... resto de secciones con el mismo patrón ...

    // SECCIÓN LIMPIEZA HALAL (si aplica)
    if($linea_productiva == 'analcoholica' && isset($d['limpieza_halal'])) {
        $html .= '
        <div class="seccion">
          <div class="seccion-titulo" style="background: #27ae60;">
            <i class="fas fa-certificate"></i> LIMPIEZA HALAL CERTIFICADA
          </div>
          <div class="seccion-contenido">
            <div class="limpieza-halal">
              <table>
                <tr>
                  <td>Fecha de limpieza:</td>
                  <td>' . $this->formatearFecha($d['limpieza_halal']['fecha']) . '</td>
                </tr>
                <tr>
                  <td>Certificado:</td>
                  <td>' . htmlspecialchars($d['limpieza_halal']['certificado']) . '</td>
                </tr>
                <tr>
                  <td>Procedimiento:</td>
                  <td>' . htmlspecialchars($d['limpieza_halal']['procedimiento']) . '</td>
                </tr>
                <tr>
                  <td>Verificado por:</td>
                  <td>' . htmlspecialchars($d['limpieza_halal']['supervisor']) . '</td>
                </tr>
              </table>
            </div>
          </div>
        </div>';
    }

    // ... resto del HTML ...

    return $html;
}

/**
 * Generar badge de línea productiva inline
 */
private function getBadgeLineaInline($linea_label) {
    $color = '#3498db'; // general
    if(strpos(strtolower($linea_label), 'alcoh') !== false && strpos(strtolower($linea_label), 'sin') === false) {
        $color = '#e74c3c';
    } elseif(strpos(strtolower($linea_label), 'sin') !== false) {
        $color = '#27ae60';
    }

    return '<span class="badge-inline" style="background: ' . $color . '; color: #fff;">' .
           htmlspecialchars($linea_label) . '</span>';
}
```

### 10.4 Corrección del Cálculo de Tiempos

```php
/**
 * Calcula la diferencia entre dos fechas (CORREGIDO)
 * @return string
 */
private function calcularDiferenciaTiempo($fecha_inicio, $fecha_fin) {
    // Validar fechas vacías o inválidas
    if(empty($fecha_inicio) || empty($fecha_fin)) {
        return 'N/A';
    }

    // Normalizar fechas (pueden venir como DATE o DATETIME)
    $fecha_inicio = trim($fecha_inicio);
    $fecha_fin = trim($fecha_fin);

    // Validar fechas nulas de MySQL
    $fechas_invalidas = ['0000-00-00', '0000-00-00 00:00:00', ''];
    if(in_array($fecha_inicio, $fechas_invalidas) || in_array($fecha_fin, $fechas_invalidas)) {
        return 'N/A';
    }

    // Validar que las fechas tengan un año razonable (después de 2020)
    $year_inicio = (int)substr($fecha_inicio, 0, 4);
    $year_fin = (int)substr($fecha_fin, 0, 4);
    if($year_inicio < 2020 || $year_fin < 2020 || $year_inicio > 2100 || $year_fin > 2100) {
        return 'N/A';
    }

    try {
        // CORREGIDO: Asegurar formato consistente
        // Si solo tiene fecha (YYYY-MM-DD), agregar hora
        if(strlen($fecha_inicio) == 10) {
            $fecha_inicio .= ' 00:00:00';
        }
        if(strlen($fecha_fin) == 10) {
            $fecha_fin .= ' 23:59:59';
        }

        $inicio = new DateTime($fecha_inicio);
        $fin = new DateTime($fecha_fin);

        // CORREGIDO: Verificar que la fecha fin sea posterior a inicio
        if($fin < $inicio) {
            // Si las fechas están invertidas, invertirlas
            $temp = $inicio;
            $inicio = $fin;
            $fin = $temp;
        }

        $diff = $inicio->diff($fin);

        // CORREGIDO: Usar días totales, no solo la propiedad days
        $total_dias = $diff->days;
        $horas = $diff->h;
        $minutos = $diff->i;

        $partes = array();

        if($total_dias > 0) {
            $partes[] = $total_dias . ' día' . ($total_dias > 1 ? 's' : '');
        }
        if($horas > 0) {
            $partes[] = $horas . ' hora' . ($horas > 1 ? 's' : '');
        }
        if(count($partes) == 0 && $minutos > 0) {
            $partes[] = $minutos . ' minuto' . ($minutos > 1 ? 's' : '');
        }

        return count($partes) > 0 ? implode(', ', $partes) : 'Menos de 1 hora';

    } catch(Exception $e) {
        error_log("Error calculando diferencia de tiempo: " . $e->getMessage());
        return 'N/A';
    }
}
```

---

## 11. Sistema de Limpiezas

### 11.1 Diagrama de Flujo

```
┌─────────────────────────────────────────────────────────────────┐
│                    FLUJO DE LIMPIEZA HALAL                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐                                               │
│  │   ACTIVO     │                                               │
│  │   GENERAL    │                                               │
│  └──────┬───────┘                                               │
│         │                                                        │
│         ▼                                                        │
│  ┌─────────────────────────────────────┐                        │
│  │ ¿Última producción fue alcohólica?  │                        │
│  └──────────────┬──────────────────────┘                        │
│                 │                                                │
│        ┌───────┴───────┐                                        │
│        ▼               ▼                                        │
│      [SÍ]            [NO]                                       │
│        │               │                                        │
│        ▼               │                                        │
│  ┌─────────────┐       │                                        │
│  │  REQUIERE   │       │                                        │
│  │  LIMPIEZA   │       │                                        │
│  │   HALAL     │       │                                        │
│  └──────┬──────┘       │                                        │
│         │               │                                        │
│         ▼               │                                        │
│  ┌─────────────────┐    │                                       │
│  │ 1. Ejecutar     │    │                                       │
│  │    limpieza     │    │                                       │
│  │ 2. Registrar    │    │                                       │
│  │ 3. Certificar   │    │                                       │
│  │ 4. Supervisor   │    │                                       │
│  │    verifica     │    │                                       │
│  └────────┬────────┘    │                                       │
│           │              │                                       │
│           └──────┬───────┘                                      │
│                  ▼                                               │
│         ┌────────────────┐                                      │
│         │    ACTIVO      │                                      │
│         │ HABILITADO     │                                      │
│         │ PARA HALAL     │                                      │
│         └────────┬───────┘                                      │
│                  │                                               │
│                  ▼                                               │
│         ┌────────────────┐                                      │
│         │   PRODUCCIÓN   │                                      │
│         │   SIN ALCOHOL  │                                      │
│         └────────────────┘                                      │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 11.2 Validación Antes de Producción

**Implementar en:** `ajax/ajax_llenarBarriles.php` y templates de producción

```php
// Al iniciar producción para línea sin alcohol
if($receta->clasificacion == 'Kombucha' || $producto->linea_productiva == 'analcoholica') {
    $activo = new Activo($batch_activo->id_activos);

    // Si el activo es de uso general, verificar limpieza Halal
    if($activo->linea_productiva == 'general') {
        $validacion = RegistroLimpieza::validarLimpiezaHalalParaProduccion($activo->id, 24);

        if(!$validacion['valido']) {
            $response['status'] = 'ERROR';
            $response['mensaje'] = $validacion['mensaje'];
            $response['requiere_limpieza'] = true;
            $response['activo_id'] = $activo->id;
            echo json_encode($response);
            exit;
        }
    }
}
```

---

## 12. Plan de Ejecución

### 12.1 Fases de Implementación

```
┌─────────────────────────────────────────────────────────────────┐
│                     FASES DE IMPLEMENTACIÓN                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  FASE 1: BASE DE DATOS                                          │
│  ├── Migración 009: Productos línea productiva                  │
│  ├── Migración 010: Batches campos ML                           │
│  ├── Migración 011: Insumos fichas/certificados                 │
│  └── Migración 012: Sistema de limpiezas                        │
│                                                                  │
│  FASE 2: CLASES PHP                                             │
│  ├── Modificar Producto.php                                     │
│  ├── Modificar Insumo.php                                       │
│  ├── Modificar Batch.php                                        │
│  ├── Modificar Activo.php                                       │
│  ├── Crear RegistroLimpieza.php                                 │
│  └── Crear ProcedimientoLimpieza.php                            │
│                                                                  │
│  FASE 3: ENDPOINTS AJAX                                         │
│  ├── ajax_registrarLimpieza.php                                 │
│  ├── ajax_obtenerHistorialLimpiezas.php                         │
│  └── ajax_validarLimpiezaHalal.php                              │
│                                                                  │
│  FASE 4: TEMPLATES                                              │
│  ├── Modificar detalle-insumos.php                              │
│  ├── Modificar detalle-activos.php                              │
│  ├── Modificar nuevo-productos.php                              │
│  ├── Modificar detalle-productos.php                            │
│  └── Modificar nuevo-batches.php                                │
│                                                                  │
│  FASE 5: PDF TRAZABILIDAD                                       │
│  ├── Título dinámico con normas                                 │
│  ├── ID Batch con fecha                                         │
│  ├── Código de barril/caja                                      │
│  ├── Todos los insumos con links                                │
│  ├── Línea productiva inline                                    │
│  ├── Corregir cálculo tiempos                                   │
│  └── Sección limpieza Halal                                     │
│                                                                  │
│  FASE 6: TESTING                                                │
│  ├── Pruebas unitarias                                          │
│  ├── Pruebas de integración                                     │
│  └── Pruebas de PDF                                             │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 12.2 Cronograma Sugerido

| Fase | Descripción | Dependencias | Complejidad |
|------|-------------|--------------|-------------|
| 1 | Base de Datos | Ninguna | Baja |
| 2 | Clases PHP | Fase 1 | Media |
| 3 | Endpoints AJAX | Fase 2 | Baja |
| 4 | Templates | Fases 2, 3 | Media |
| 5 | PDF Trazabilidad | Fases 1, 2 | Alta |
| 6 | Testing | Todas | Media |

---

## 13. Dependencias y Orden de Implementación

### 13.1 Grafo de Dependencias

```
Migración 009 ──┐
Migración 010 ──┼──► Clases PHP ──► Templates ──► Testing
Migración 011 ──┤                      │
Migración 012 ──┘                      │
                                       │
                     AJAX Endpoints ───┘
                           │
                           ▼
                    PDF Trazabilidad
```

### 13.2 Orden de Ejecución de Migraciones

```bash
# 1. Ejecutar en orden:
mysql -u barrcl_cocholg -p barrcl_cocholg < db/migrations/009_productos_linea_productiva.sql
mysql -u barrcl_cocholg -p barrcl_cocholg < db/migrations/010_batches_ml_fields.sql
mysql -u barrcl_cocholg -p barrcl_cocholg < db/migrations/011_insumos_fichas_certificados.sql
mysql -u barrcl_cocholg -p barrcl_cocholg < db/migrations/012_sistema_limpiezas.sql

# 2. Verificar:
mysql -u barrcl_cocholg -p barrcl_cocholg -e "DESCRIBE productos;"
mysql -u barrcl_cocholg -p barrcl_cocholg -e "DESCRIBE batches;"
mysql -u barrcl_cocholg -p barrcl_cocholg -e "DESCRIBE insumos;"
mysql -u barrcl_cocholg -p barrcl_cocholg -e "DESCRIBE activos;"
mysql -u barrcl_cocholg -p barrcl_cocholg -e "SHOW TABLES LIKE '%limpieza%';"
```

---

## 14. Plan de Pruebas

### 14.1 Pruebas de Base de Datos

```sql
-- Test 1: Verificar campo linea_productiva en productos
INSERT INTO productos (nombre, tipo, linea_productiva) VALUES ('Test', 'Barril', 'alcoholica');
SELECT * FROM productos WHERE nombre = 'Test';
DELETE FROM productos WHERE nombre = 'Test';

-- Test 2: Verificar campos ML en batches
UPDATE batches SET abv_final = 5.5, ibu_final = 45 WHERE id = (SELECT MIN(id) FROM batches);
SELECT abv_final, ibu_final FROM batches WHERE abv_final IS NOT NULL;

-- Test 3: Verificar sistema de limpiezas
INSERT INTO registros_limpiezas (id_activos, tipo_limpieza, id_usuarios)
VALUES (1, 'General', 1);
SELECT * FROM registros_limpiezas ORDER BY id DESC LIMIT 1;
```

### 14.2 Pruebas de Clases PHP

```php
// Test RegistroLimpieza
$limpieza = new RegistroLimpieza();
$limpieza->id_activos = 1;
$limpieza->tipo_limpieza = 'Halal';
$limpieza->es_limpieza_halal = 1;
$limpieza->id_usuarios = $GLOBALS['usuario']->id;
$id = $limpieza->registrar();
assert($id > 0, "Fallo: No se pudo registrar limpieza");

// Test validación Halal
$validacion = RegistroLimpieza::validarLimpiezaHalalParaProduccion(1, 24);
assert(isset($validacion['valido']), "Fallo: Validación no retorna 'valido'");

// Test Insumo Halal
$insumo = new Insumo();
$insumo->es_halal_certificado = 1;
$insumo->certificado_halal_vencimiento = date('Y-m-d', strtotime('+30 days'));
assert($insumo->tieneCertificadoHalalVigente() === true, "Fallo: Certificado debería estar vigente");
```

### 14.3 Pruebas de PDF

```php
// Test generación de PDF con línea alcohólica
$pdf = new TrazabilidadPDF($id_entrega_producto_alcoholica);
$normas = $pdf->getNormasISO();
assert(strpos($normas, 'OIC') === false, "No debería incluir OIC para alcohólica");

// Test generación de PDF con línea sin alcohol
$pdf = new TrazabilidadPDF($id_entrega_producto_halal);
$normas = $pdf->getNormasISO();
assert(strpos($normas, 'OIC') !== false, "Debería incluir OIC para sin alcohol");

// Test cálculo de tiempos
$diff = $pdf->calcularDiferenciaTiempo('2025-01-01', '2025-01-03');
assert($diff == '2 días', "Cálculo de días incorrecto: " . $diff);
```

### 14.4 Checklist de Pruebas Manuales

**PDF de Trazabilidad:**
- [ ] Título muestra normas correctas según línea
- [ ] ID Batch con fecha visible
- [ ] "Código de barril" / "Código de caja" según tipo
- [ ] Lista todos los insumos (no solo granos)
- [ ] Links a fichas técnicas funcionan
- [ ] Links a certificados Halal funcionan
- [ ] Badge de línea junto a cada activo (sin texto "Línea:")
- [ ] Cálculo Empaque→Entrega correcto
- [ ] Sección de limpieza Halal visible cuando aplica

**Sistema de Limpiezas:**
- [ ] Registrar limpieza general
- [ ] Registrar limpieza Halal con certificado
- [ ] Ver historial de limpiezas
- [ ] Validación antes de producción Halal
- [ ] Campos de limpieza actualizados en activo

**Insumos:**
- [ ] Agregar URL de ficha técnica
- [ ] Agregar certificado Halal
- [ ] Indicador de Halal vigente
- [ ] Alerta de certificados por vencer

---

## 15. Riesgos y Mitigaciones

### 15.1 Riesgos Identificados

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| Migración falla en producción | Media | Alto | Backup antes de migrar, scripts de rollback |
| URLs de fichas inaccesibles | Alta | Bajo | Validar URLs, caché local opcional |
| Cálculo tiempos sigue fallando | Media | Medio | Logs detallados, casos de prueba exhaustivos |
| Certificados Halal vencidos | Alta | Alto | Alertas automáticas, dashboard de vencimientos |
| Performance con muchos insumos | Baja | Medio | Lazy loading, paginación |

### 15.2 Scripts de Rollback

```sql
-- Rollback Migración 009
ALTER TABLE productos DROP COLUMN linea_productiva;

-- Rollback Migración 010
ALTER TABLE batches
  DROP COLUMN abv_final,
  DROP COLUMN ibu_final,
  DROP COLUMN color_ebc,
  DROP COLUMN rendimiento_litros_final,
  DROP COLUMN merma_total_litros,
  DROP COLUMN densidad_final_verificada,
  DROP COLUMN calificacion_sensorial,
  DROP COLUMN notas_cata,
  DROP COLUMN temperatura_ambiente_promedio,
  DROP COLUMN humedad_relativa_promedio;

-- Rollback Migración 011
ALTER TABLE insumos
  DROP COLUMN url_ficha_tecnica,
  DROP COLUMN url_certificado_halal,
  DROP COLUMN certificado_halal_numero,
  DROP COLUMN certificado_halal_vencimiento,
  DROP COLUMN certificado_halal_emisor,
  DROP COLUMN es_halal_certificado;

-- Rollback Migración 012
DROP TABLE IF EXISTS registros_limpiezas;
DROP TABLE IF EXISTS procedimientos_limpieza;
ALTER TABLE activos
  DROP COLUMN fecha_ultima_limpieza,
  DROP COLUMN proxima_limpieza,
  DROP COLUMN limpieza_procedimiento,
  DROP COLUMN limpieza_periodicidad,
  DROP COLUMN fecha_ultima_limpieza_halal,
  DROP COLUMN certificado_limpieza_halal,
  DROP COLUMN uso_exclusivo_halal;
```

---

## Anexos

### A. Lista de Archivos a Crear

1. `db/migrations/009_productos_linea_productiva.sql`
2. `db/migrations/010_batches_ml_fields.sql`
3. `db/migrations/011_insumos_fichas_certificados.sql`
4. `db/migrations/012_sistema_limpiezas.sql`
5. `php/classes/RegistroLimpieza.php`
6. `php/classes/ProcedimientoLimpieza.php`
7. `ajax/ajax_registrarLimpieza.php`
8. `ajax/ajax_obtenerHistorialLimpiezas.php`
9. `ajax/ajax_validarLimpiezaHalal.php`

### B. Lista de Archivos a Modificar

1. `php/classes/Producto.php`
2. `php/classes/Insumo.php`
3. `php/classes/Batch.php`
4. `php/classes/Activo.php`
5. `php/classes/TrazabilidadPDF.php`
6. `templates/detalle-insumos.php`
7. `templates/detalle-activos.php`
8. `templates/nuevo-productos.php`
9. `templates/detalle-productos.php`
10. `templates/nuevo-batches.php`

### C. Normas de Referencia

**Línea Alcohólica:**
- ISO 22005:2007 - Trazabilidad en la cadena alimentaria
- ISO 22000:2018 - Sistemas de gestión de inocuidad alimentaria
- FSSC 22000 - Certificación de sistemas de seguridad alimentaria
- BRCGS - British Retail Consortium Global Standards
- IFS - International Featured Standards

**Línea Sin Alcohol (Halal):**
- OIC/SMIIC 1 - Guía general para Halal
- GSO 2055-1 - Requisitos generales para alimentos Halal
- (más las normas de línea alcohólica)

---

*Documento generado: 2025-12-04*
*Sistema: Barril.cl ERP v1.1*
*Autor: Claude Code*
