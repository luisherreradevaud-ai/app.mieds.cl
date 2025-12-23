# Plan de Implementación - Requerimiento 2 (Actualizado)

**Fecha:** 2025-12-13
**Sistema:** Barril.cl ERP

---

## Resumen de Estado Actual

| Item | Estado | Notas |
|------|--------|-------|
| ID de Productos por Línea | **NO EXISTE** | IDs son INT auto-increment |
| Inventario insumos con fichas | **PARCIAL** | Campos Halal existen, falta UI upload |
| PDF Instrucciones de Receta | **NO EXISTE** | Receta no tiene pasos ni tiempos |
| Título certificado con normas | **YA EXISTE** | TrazabilidadPDF.php línea 639 |
| ID Batch con fecha en PDF | **YA EXISTE** | Línea 754 |
| Código de barril en PDF | **YA EXISTE** | Línea 678 (dinámico) |
| Granos → Insumos en PDF | **YA EXISTE** | Cambiado a "Insumos" + links fichas |
| Línea productiva en timeline | **PARCIAL** | Falta en traspasos |
| Empaque-entrega mal calculado | **REVISAR** | Posible bug en fechas |
| Registros limpieza Halal | **YA EXISTE** | Sistema completo |
| Series temporales proceso | **NO EXISTE** | Requerido para ML/PLC |
| Datos específicos levadura | **NO EXISTE** | BatchInsumo no captura generación/viabilidad |
| Analíticas QC multi-etapa | **PARCIAL** | Solo BatchEnfriado, faltan otras etapas |
| Campos MateriaPrima en insumos | **NO EXISTE** | Falta marca, origen, cosecha, presentación |
| Campos cocción en batches | **PARCIAL** | Falta tiempo hervido, densidades pre/post |
| Campos CIP técnicos | **NO EXISTE** | registros_limpiezas no tiene datos técnicos |
| PDF Informe de Batch | **NO EXISTE** | Solo existe TrazabilidadPDF para entregas |

---

## Tareas Pendientes

### 1. IDs de Productos por Línea Productiva
**Prioridad: ALTA** | **Complejidad: ALTA**

**Objetivo:** Códigos con prefijo por línea (PROD-ALC-001, PROD-SAA-001, PROD-GEN-001)

**Archivos a modificar:**
- `php/classes/Producto.php` - Método `generarCodigoPorLinea()`
- `templates/nuevo-productos.php` - Mostrar código
- `templates/detalle-productos.php` - Mostrar código

**Nueva migración:**
```sql
-- db/migrations/013_productos_codigo_linea.sql
ALTER TABLE productos ADD COLUMN codigo_producto VARCHAR(20) AFTER id;
ALTER TABLE productos ADD UNIQUE INDEX idx_productos_codigo (codigo_producto);

CREATE TABLE productos_secuencias (
  linea_productiva ENUM('alcoholica','analcoholica','general') PRIMARY KEY,
  siguiente_numero INT DEFAULT 1
);

INSERT INTO productos_secuencias VALUES
  ('alcoholica', 1),
  ('analcoholica', 1),
  ('general', 1);
```

---

### 2. Sistema de Inventario de Insumos con Fichas Técnicas
**Prioridad: ALTA** | **Complejidad: MEDIA**

**Estado actual:**
- ✅ Campos Halal existen (url_certificado, numero, emisor, vencimiento)
- ✅ Método `tieneCertificadoHalalVigente()` existe
- ⚠️ No hay tabla `media_insumos` para uploads

**Archivos a modificar:**
- `php/classes/Insumo.php` - Métodos para media
- `templates/nuevo-insumos.php` - UI uploads
- `templates/detalle-insumos.php` - UI uploads

**Nueva migración:**
```sql
-- db/migrations/014_insumos_media.sql
CREATE TABLE media_insumos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_insumos INT NOT NULL,
  id_media INT NOT NULL,
  tipo ENUM('ficha_tecnica','certificado_halal','otro') DEFAULT 'otro',
  FOREIGN KEY (id_insumos) REFERENCES insumos(id)
);

ALTER TABLE insumos ADD COLUMN proveedor VARCHAR(200);
ALTER TABLE insumos ADD COLUMN codigo_proveedor VARCHAR(100);
ALTER TABLE insumos ADD COLUMN pais_origen VARCHAR(100);
```

---

### 3. Mostrar Línea Productiva en Traspasos del PDF
**Prioridad: BAJA** | **Complejidad: BAJA**

**Archivo:** `php/classes/TrazabilidadPDF.php` líneas 780-788

**Cambio:**
```php
// Antes:
$html .= htmlspecialchars($t['origen']) . ' → ' . htmlspecialchars($t['destino']);

// Después:
$html .= htmlspecialchars($t['origen']) . ' (' . $t['linea_origen'] . ')' .
         ' → ' . htmlspecialchars($t['destino']) . ' (' . $t['linea_destino'] . ')';
```

---

### 4. Revisar Cálculo Tiempo Empaque → Entrega
**Prioridad: MEDIA** | **Complejidad: MEDIA**

**Archivo:** `php/classes/TrazabilidadPDF.php` líneas 117-158

**Problema:** El tiempo puede estar calculando incorrectamente debido a:
- `$fecha_llenado` no es la fecha real de empaque
- Fechas inválidas ('0000-00-00') no filtradas

**Solución:** Revisar lógica de obtención de fechas y usar fecha de estado "En despacho" del barril.

---

### 5. PDF de Instrucciones de Receta (Paso a Paso)
**Prioridad: ALTA** | **Complejidad: ALTA**

**Objetivo:** Generar PDF con instrucciones de producción paso a paso, tiempos y parámetros objetivo para que un operario pueda ejecutar la receta.

#### Estado Actual del Sistema

**Receta (muy básica):**
- Solo tiene: nombre, codigo, clasificacion, observaciones, litros
- Insumos sin etapa asignada (lista plana)
- NO tiene: pasos, tiempos, temperaturas objetivo, instrucciones

**Batch (tiene todo el detalle):**
- 13 etapas: General → Licor → Maceración → Lavado → Cocción → Combustible → Lupulización → Enfriado → Inoculación → Fermentación → Traspasos → Maduración → Finalización
- Cada etapa tiene tiempos, temperaturas, pH, insumos por etapa
- Pero estos son DATOS REALES, no INSTRUCCIONES

#### Solución Propuesta

**A. Nueva tabla `recetas_pasos`:**
```sql
CREATE TABLE recetas_pasos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_recetas INT NOT NULL,
  etapa ENUM('licor','maceracion','lavado','coccion','lupulizacion',
             'enfriado','inoculacion','fermentacion','maduracion') NOT NULL,
  orden INT DEFAULT 0,
  instruccion TEXT,
  duracion_minutos INT DEFAULT 0,
  temperatura_objetivo DECIMAL(4,1),
  ph_objetivo DECIMAL(3,1),
  densidad_objetivo VARCHAR(20),
  notas TEXT,
  FOREIGN KEY (id_recetas) REFERENCES recetas(id) ON DELETE CASCADE
);
CREATE INDEX idx_recetas_pasos_receta ON recetas_pasos(id_recetas);
```

**B. Modificar `recetas_insumos` para incluir etapa:**
```sql
ALTER TABLE recetas_insumos ADD COLUMN etapa VARCHAR(50) DEFAULT 'maceracion';
ALTER TABLE recetas_insumos ADD COLUMN orden INT DEFAULT 0;
ALTER TABLE recetas_insumos ADD COLUMN momento VARCHAR(100);
```

**C. Agregar campos objetivo a `recetas`:**
```sql
ALTER TABLE recetas ADD COLUMN abv_objetivo DECIMAL(4,2);
ALTER TABLE recetas ADD COLUMN ibu_objetivo INT;
ALTER TABLE recetas ADD COLUMN color_ebc_objetivo INT;
ALTER TABLE recetas ADD COLUMN og_objetivo DECIMAL(5,3);
ALTER TABLE recetas ADD COLUMN fg_objetivo DECIMAL(5,3);
ALTER TABLE recetas ADD COLUMN tiempo_fermentacion_dias INT;
ALTER TABLE recetas ADD COLUMN tiempo_maduracion_dias INT;
ALTER TABLE recetas ADD COLUMN instrucciones_generales TEXT;
```

#### Archivos a Crear

| Archivo | Descripción |
|---------|-------------|
| `php/classes/RecetaPaso.php` | Nueva clase para pasos de receta |
| `php/classes/RecetaPDF.php` | Generador de PDF de instrucciones |
| `ajax/ajax_generarRecetaPDF.php` | Endpoint para generar PDF |
| `db/migrations/015_recetas_instrucciones.sql` | Migración completa |

#### Archivos a Modificar

| Archivo | Cambios |
|---------|---------|
| `php/classes/Receta.php` | Agregar campos objetivo y métodos getPasos(), getInsumosPorEtapa() |
| `php/classes/RecetaInsumo.php` | Agregar campos etapa, orden, momento |
| `templates/nuevo-recetas.php` | UI para agregar pasos e insumos por etapa |
| `templates/detalle-recetas.php` | Botón "Generar PDF Instrucciones" + visualización de pasos |

#### Estructura del PDF

```
┌─────────────────────────────────────────────────────────────┐
│           INSTRUCCIONES DE PRODUCCIÓN                       │
│           Receta: [Nombre] ([Código])                       │
│           Volumen objetivo: [X] L                           │
├─────────────────────────────────────────────────────────────┤
│ PARÁMETROS OBJETIVO                                         │
│ ABV: 5.5% | IBU: 45 | OG: 1.052 | FG: 1.012                │
├─────────────────────────────────────────────────────────────┤
│ RESUMEN DE INSUMOS                                          │
│ ┌─────────────────┬──────────┬─────────────┐               │
│ │ Insumo          │ Cantidad │ Etapa       │               │
│ ├─────────────────┼──────────┼─────────────┤               │
│ │ Malta Pilsen    │ 5 kg     │ Maceración  │               │
│ │ Lúpulo Cascade  │ 50 g     │ Cocción     │               │
│ │ Levadura US-05  │ 1 sobre  │ Inoculación │               │
│ └─────────────────┴──────────┴─────────────┘               │
├─────────────────────────────────────────────────────────────┤
│ PASO 1: LICOR                                    ⏱️ 30 min  │
│ ────────────────────────────────────────────────────────────│
│ □ Calentar agua a 72°C                                     │
│ □ Verificar pH del agua (objetivo: 6.5-7.0)                │
│                                                             │
│ 🌡️ Temp: 72°C  |  📊 pH: 6.5-7.0  |  💧 Litros: 25L       │
├─────────────────────────────────────────────────────────────┤
│ PASO 2: MACERACIÓN                               ⏱️ 60 min  │
│ ────────────────────────────────────────────────────────────│
│ INSUMOS A AGREGAR:                                         │
│ • Malta Pilsen: 5 kg                                       │
│                                                             │
│ □ Agregar maltas al agua a 67°C                            │
│ □ Revolver cada 15 minutos                                 │
│                                                             │
│ 🌡️ Temp: 67°C  |  📊 pH: 5.2-5.4                          │
├─────────────────────────────────────────────────────────────┤
│ ... más pasos ...                                          │
├─────────────────────────────────────────────────────────────┤
│ NOTAS FINALES                                               │
│ - Fermentación: 7-10 días a 18-20°C                        │
│ - Maduración: 14 días a 2-4°C                              │
└─────────────────────────────────────────────────────────────┘
```

---

### 6. Ampliación Modelo de Datos para ML y PLC
**Prioridad: MEDIA** | **Complejidad: ALTA**

**Objetivo:** Preparar el sistema para captura de datos de proceso orientada a Machine Learning y futura integración con sistema de control automático (PLC + sensores).

#### 6.1 Ampliar tabla `batches` (campos de cocción y resúmenes)

**Campos existentes relevantes:**
- Ya tiene: licor_*, maceracion_*, lavado_*, coccion_ph_*, fermentacion_*, maduracion_*
- Ya tiene campos ML: abv_final, ibu_final, color_ebc, rendimiento_litros_final, etc.

**Campos nuevos a agregar:**
```sql
ALTER TABLE batches
  ADD COLUMN tiempo_hervido_total_min INT,
  ADD COLUMN densidad_pre_hervor FLOAT,
  ADD COLUMN densidad_pre_hervor_unidad ENUM('SG','Plato','Brix') DEFAULT 'SG',
  ADD COLUMN densidad_post_hervor FLOAT,
  ADD COLUMN densidad_post_hervor_unidad ENUM('SG','Plato','Brix') DEFAULT 'SG',
  ADD COLUMN fermentacion_tiempo_total_h FLOAT,
  ADD COLUMN fermentacion_temp_media FLOAT,
  ADD COLUMN fermentacion_caida_densidad_dia_max FLOAT,
  ADD COLUMN maduracion_tiempo_total_h FLOAT,
  ADD COLUMN maduracion_temp_media FLOAT;
```

#### 6.2 Ampliar tabla `batches_enfriado`

**Campos existentes:** temperatura_inicio, ph, densidad, ph_enfriado

**Campos nuevos:**
```sql
ALTER TABLE batches_enfriado
  ADD COLUMN do_ppm FLOAT,
  ADD COLUMN temperatura_salida FLOAT,
  ADD COLUMN caudal_mosto FLOAT,
  ADD COLUMN caudal_agua FLOAT;
```

#### 6.3 Ampliar tabla `registros_limpiezas` (CIP técnico)

**Campos nuevos para datos técnicos de CIP:**
```sql
ALTER TABLE registros_limpiezas
  ADD COLUMN programa_cip VARCHAR(100),
  ADD COLUMN temperatura_max_cip FLOAT,
  ADD COLUMN tiempo_total_cip_min INT,
  ADD COLUMN conductividad_promedio FLOAT,
  ADD COLUMN cip_timestamp_inicio DATETIME,
  ADD COLUMN cip_timestamp_fin DATETIME,
  ADD COLUMN id_batches_posterior VARCHAR(36);
```

#### 6.4 Ampliar tabla `insumos` (MateriaPrima + Levadura)

**Campos generales MateriaPrima:**
```sql
ALTER TABLE insumos
  ADD COLUMN marca VARCHAR(100),
  ADD COLUMN materia_prima_basica VARCHAR(200),
  ADD COLUMN cosecha_anio INT,
  ADD COLUMN presentacion VARCHAR(200),
  ADD COLUMN vida_util_meses INT;
```

**Campos específicos para levaduras (nullable):**
```sql
ALTER TABLE insumos
  ADD COLUMN cepa VARCHAR(100),
  ADD COLUMN tipo_levadura ENUM('ale_seca','ale_liquida','lager_seca','lager_liquida','wild'),
  ADD COLUMN atenuacion_min FLOAT,
  ADD COLUMN atenuacion_max FLOAT,
  ADD COLUMN floculacion ENUM('baja','media','alta','muy_alta'),
  ADD COLUMN temp_fermentacion_min FLOAT,
  ADD COLUMN temp_fermentacion_max FLOAT,
  ADD COLUMN tolerancia_alcohol FLOAT;
```

#### 6.5 Nueva tabla `batch_signals` (Series Temporales)

**Propósito:** Capturar señales de proceso en tiempo real desde sensores/PLC.

```sql
CREATE TABLE batch_signals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_batches VARCHAR(36) NOT NULL,
  etapa ENUM('Maceracion','Lavado','Coccion','Enfriado','Fermentacion','Maduracion','Envasado','CIP') NOT NULL,
  variable VARCHAR(50) NOT NULL,
  timestamp DATETIME NOT NULL,
  valor FLOAT NOT NULL,
  unidad VARCHAR(20),
  INDEX idx_batch_signals_batch (id_batches),
  INDEX idx_batch_signals_etapa (id_batches, etapa),
  INDEX idx_batch_signals_timestamp (timestamp)
);
```

**Variables a registrar:**
| Etapa | Variable | Frecuencia |
|-------|----------|------------|
| Maceración | temperatura | cada 1 min |
| Maceración | caudal_recirculacion | si existe sensor |
| Lavado | caudal_agua_lavado | continuo |
| Lavado | deltaP_lecho | continuo |
| Enfriado | temperatura_salida | continuo |
| Enfriado | caudal_mosto, caudal_agua | si existe sensor |
| Fermentación | temperatura | cada 5-15 min |
| Fermentación | densidad | 1-2x día |
| Fermentación | presion | si aplica |
| Maduración | temperatura | cada 15 min |
| Maduración | presion | si aplica |

#### 6.6 Nueva tabla `batch_levaduras` (Extensión BatchInsumo)

**Propósito:** Capturar datos específicos de uso de levadura que BatchInsumo no maneja.

```sql
CREATE TABLE batch_levaduras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_batches VARCHAR(36) NOT NULL,
  id_batches_insumos INT,
  generacion INT DEFAULT 1,
  origen_batch VARCHAR(36),
  cantidad_gramos FLOAT,
  tasa_inoculacion FLOAT,
  viabilidad_medida FLOAT,
  vitalidad_medida FLOAT,
  uso_starter TINYINT(1) DEFAULT 0,
  volumen_starter_ml INT,
  atenuacion_real FLOAT,
  tiempo_lag_h FLOAT,
  observaciones TEXT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_batch_levaduras_batch (id_batches),
  FOREIGN KEY (id_batches_insumos) REFERENCES batches_insumos(id) ON DELETE SET NULL
);
```

#### 6.7 Nueva tabla `batch_analiticas` (QC Multi-etapa)

**Propósito:** Mediciones puntuales de control de calidad en múltiples momentos del proceso.

```sql
CREATE TABLE batch_analiticas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_batches VARCHAR(36) NOT NULL,
  momento ENUM('PreMaceracion','PreBoil','PostBoil','PreFermentacion',
               'MidFermentacion','PreEnvasado','PostEnvasado') NOT NULL,
  densidad FLOAT,
  densidad_unidad ENUM('SG','Plato','Brix') DEFAULT 'SG',
  ph FLOAT,
  co2_disuelto FLOAT,
  do_ppm FLOAT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  observaciones TEXT,
  INDEX idx_batch_analiticas_batch (id_batches),
  INDEX idx_batch_analiticas_momento (id_batches, momento)
);
```

#### Archivos a Crear

| Archivo | Descripción |
|---------|-------------|
| `php/classes/BatchSignal.php` | Clase para series temporales |
| `php/classes/BatchLevadura.php` | Clase para datos de levadura |
| `php/classes/BatchAnalitica.php` | Clase para analíticas QC |
| `db/migrations/016_ampliacion_modelo_ml.sql` | Migración consolidada |

#### Archivos a Modificar

| Archivo | Cambios |
|---------|---------|
| `php/classes/Batch.php` | Agregar propiedades de cocción y resúmenes |
| `php/classes/BatchEnfriado.php` | Agregar propiedades DO, caudales |
| `php/classes/Insumo.php` | Agregar propiedades MateriaPrima + Levadura |
| `php/classes/RegistroLimpieza.php` | Agregar propiedades CIP técnico |
| `templates/nuevo-batches.php` | UI para campos de cocción |
| `templates/nuevo-insumos.php` | UI para campos MateriaPrima/Levadura |
| `templates/detalle-activos.php` | UI para datos CIP en limpiezas |

#### Endpoints API para Integración PLC

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `ajax/ajax_registrarBatchSignal.php` | POST | Registrar señal desde PLC |
| `ajax/ajax_registrarBatchLevadura.php` | POST | Registrar datos de levadura |
| `ajax/ajax_registrarBatchAnalitica.php` | POST | Registrar analítica QC |
| `ajax/ajax_getBatchSignals.php` | GET | Obtener series temporales |

#### Validaciones Mínimas por Batch

Para ML efectivo, cada batch debería tener:
- [ ] Al menos señales de temperatura de fermentación
- [ ] Al menos 2 mediciones de densidad (inicio y fin fermentación)
- [ ] Al menos una medición de DO a la salida del enfriador
- [ ] Registro de levadura con generación y cantidad

---

### 7. PDF de Informe Completo de Batch
**Prioridad: MEDIA** | **Complejidad: MEDIA**

**Objetivo:** Generar un PDF con toda la información del batch para documentación, auditoría y trazabilidad interna.

**Referencia:** Basado en `TrazabilidadPDF.php` y `ajax/ajax_generarPDFTrazabilidad.php`

#### Estructura del PDF

```
┌─────────────────────────────────────────────────────────────┐
│ [LOGO]           INFORME DE PRODUCCIÓN                      │
│                  Batch: [Nombre] #[ID]                      │
│                  Receta: [Nombre Receta]                    │
├─────────────────────────────────────────────────────────────┤
│ INFORMACIÓN GENERAL                                          │
│ ─────────────────────                                        │
│ Fecha de Cocción: [DD/MM/YYYY]                              │
│ Cocinero: [Nombre Usuario]                                  │
│ Volumen Objetivo: [X] L                                     │
│ Línea Productiva: [Alcohólica/Sin Alcohol/General]          │
│ Estado: [Etapa actual]                                      │
├─────────────────────────────────────────────────────────────┤
│ ETAPAS DEL PROCESO                                          │
│ ─────────────────────                                        │
│                                                              │
│ ● LICOR                                                      │
│   Temperatura: [X]°C | pH: [X] | Litros: [X]L               │
│                                                              │
│ ● MACERACIÓN                                                 │
│   Inicio: [HH:MM] | Fin: [HH:MM]                            │
│   Temperatura: [X]°C | pH: [X] | Litros: [X]L               │
│                                                              │
│ ● LAVADO DE GRANOS                                          │
│   Inicio: [HH:MM] | Fin: [HH:MM]                            │
│   Mosto: [X]L | Densidad: [X] [SG/Plato]                    │
│                                                              │
│ ● COCCIÓN                                                    │
│   Tiempo Hervido: [X] min                                   │
│   pH Inicial: [X] | pH Final: [X]                           │
│   Densidad Pre: [X] | Densidad Post: [X]                    │
│                                                              │
│ ● ENFRIADO                                                   │
│   [Fecha] Temp: [X]°C | pH: [X] | Densidad: [X]             │
│   DO: [X] ppm (si existe)                                   │
│                                                              │
│ ● FERMENTACIÓN                                               │
│   Inicio: [DD/MM/YYYY HH:MM]                                │
│   Fermentador: [Código] ([Capacidad]L)                      │
│   Temperatura: [X]°C | pH: [X] | Densidad: [X]              │
│   Fin: [DD/MM/YYYY HH:MM]                                   │
│                                                              │
│ ● MADURACIÓN                                                 │
│   Inicio: [DD/MM/YYYY HH:MM]                                │
│   Temp Inicio: [X]°C | Temp Fin: [X]°C                      │
├─────────────────────────────────────────────────────────────┤
│ INSUMOS UTILIZADOS                                          │
│ ─────────────────────                                        │
│ ┌──────────────────┬──────────┬────────────┬───────────┐    │
│ │ Insumo           │ Cantidad │ Etapa      │ Halal     │    │
│ ├──────────────────┼──────────┼────────────┼───────────┤    │
│ │ Malta Pilsen     │ 5 kg     │ Maceración │ ✓         │    │
│ │ Lúpulo Cascade   │ 50 g     │ Cocción    │ ✓         │    │
│ │ Levadura US-05   │ 11.5 g   │ Inoculación│ ✓         │    │
│ └──────────────────┴──────────┴────────────┴───────────┘    │
├─────────────────────────────────────────────────────────────┤
│ LUPULIZACIONES                                              │
│ ─────────────────────                                        │
│ [Fecha HH:MM] - [Tipo: Dry Hop / Whirlpool / etc.]          │
├─────────────────────────────────────────────────────────────┤
│ TRASPASOS                                                    │
│ ─────────────────────                                        │
│ [Fecha HH:MM] [Origen] → [Destino] ([X]L)                   │
├─────────────────────────────────────────────────────────────┤
│ FERMENTADORES ASIGNADOS                                     │
│ ─────────────────────                                        │
│ ┌──────────────────┬──────────┬────────────┐                │
│ │ Activo           │ Litraje  │ Estado     │                │
│ ├──────────────────┼──────────┼────────────┤                │
│ │ FER-001          │ 60 L     │ Maduración │                │
│ └──────────────────┴──────────┴────────────┘                │
├─────────────────────────────────────────────────────────────┤
│ MÉTRICAS FINALES (si existen)                               │
│ ─────────────────────                                        │
│ ABV: [X]% | IBU: [X] | Color EBC: [X]                       │
│ Rendimiento: [X]L | Merma: [X]L ([X]%)                      │
│ Densidad Final: [X]                                         │
│ Calificación Sensorial: [X]/10                              │
├─────────────────────────────────────────────────────────────┤
│ OBSERVACIONES                                                │
│ ─────────────────────                                        │
│ [Texto de observaciones del batch]                          │
├─────────────────────────────────────────────────────────────┤
│ Documento generado el [fecha] - Sistema Barril.cl           │
└─────────────────────────────────────────────────────────────┘
```

#### Archivos a Crear

| Archivo | Descripción |
|---------|-------------|
| `php/classes/BatchPDF.php` | Clase generadora del PDF de batch |
| `ajax/ajax_generarBatchPDF.php` | Endpoint para descargar el PDF |

#### Archivos a Modificar

| Archivo | Cambios |
|---------|---------|
| `templates/detalle-batches.php` | Agregar botón "Generar PDF Informe" |
| `templates/batches.php` | Agregar botón PDF en listado (opcional) |

#### Clase BatchPDF - Estructura

```php
class BatchPDF {
  private $batch;
  private $receta;
  private $datos = array();

  public function __construct($id_batch);
  private function recopilarDatos();
  private function obtenerInsumos();
  private function obtenerLupulizaciones();
  private function obtenerEnfriados();
  private function obtenerTraspasos();
  private function obtenerFermentadores();
  public function generarHTML();
  public function generar($output = 'D');
}
```

#### Endpoint ajax_generarBatchPDF.php

```php
// Parámetros GET:
// - id: ID del Batch
// Permisos: Administrador, Jefe de Planta, Jefe de Cocina, Operario
// Respuesta: Descarga directa del PDF
// Nombre archivo: Batch_[NOMBRE]_[FECHA].pdf
```

---

## Items Ya Implementados ✓

| Item | Ubicación |
|------|-----------|
| Normas en título PDF | TrazabilidadPDF.php:348-373, 639 |
| ID Batch + fecha | TrazabilidadPDF.php:754 |
| "Código de barril" | TrazabilidadPDF.php:678 |
| "Insumos" con links | TrazabilidadPDF.php:755 |
| Sistema limpiezas Halal | RegistroLimpieza.php, ajax_registrarLimpieza.php |
| Validación 24h activos generales | ajax_validarLimpiezaHalal.php |

---

## Orden de Implementación Sugerido

### Fase 1 - Correcciones Rápidas
1. Mostrar línea productiva en traspasos PDF
2. Revisar cálculo tiempo empaque-entrega

### Fase 2 - Mejoras de Insumos
1. Crear tabla `media_insumos`
2. Mejorar UI con uploads de fichas técnicas y certificados

### Fase 3 - Códigos por Línea
1. Crear migración de secuencias
2. Implementar generación de códigos
3. Actualizar UI de productos

### Fase 4 - PDF de Instrucciones de Receta
1. Crear migración `015_recetas_instrucciones.sql`
2. Crear clase `RecetaPaso.php`
3. Modificar `Receta.php` y `RecetaInsumo.php`
4. Crear clase `RecetaPDF.php`
5. Actualizar UI de recetas (nuevo-recetas.php, detalle-recetas.php)
6. Crear endpoint `ajax_generarRecetaPDF.php`

### Fase 5 - Ampliación Modelo ML/PLC
1. Crear migración `016_ampliacion_modelo_ml.sql`
2. Ampliar clases existentes:
   - `Batch.php` - campos cocción y resúmenes
   - `BatchEnfriado.php` - campos DO, caudales
   - `Insumo.php` - campos MateriaPrima + Levadura
   - `RegistroLimpieza.php` - campos CIP técnico
3. Crear nuevas clases:
   - `BatchSignal.php` - series temporales
   - `BatchLevadura.php` - datos específicos levadura
   - `BatchAnalitica.php` - mediciones QC
4. Crear endpoints API:
   - `ajax_registrarBatchSignal.php`
   - `ajax_registrarBatchLevadura.php`
   - `ajax_registrarBatchAnalitica.php`
   - `ajax_getBatchSignals.php`
5. Actualizar UI:
   - `nuevo-batches.php` - campos cocción
   - `nuevo-insumos.php` - campos MateriaPrima/Levadura
   - `detalle-activos.php` - datos CIP

### Fase 6 - PDF de Informe de Batch
1. Crear clase `php/classes/BatchPDF.php`
2. Crear endpoint `ajax/ajax_generarBatchPDF.php`
3. Agregar botón en `templates/detalle-batches.php`
4. (Opcional) Agregar botón en listado `templates/batches.php`

---

## Migraciones Pendientes

```
db/migrations/
├── 013_productos_codigo_linea.sql
├── 014_insumos_media.sql
├── 015_recetas_instrucciones.sql
└── 016_ampliacion_modelo_ml.sql
```

---

## Migración 016: Contenido Completo

```sql
-- db/migrations/016_ampliacion_modelo_ml.sql
-- Ampliación del modelo de datos para ML y PLC
-- Fecha: 2025-12-13

-- =====================================================
-- 1. AMPLIAR TABLA batches
-- =====================================================
ALTER TABLE batches
  ADD COLUMN tiempo_hervido_total_min INT,
  ADD COLUMN densidad_pre_hervor FLOAT,
  ADD COLUMN densidad_pre_hervor_unidad ENUM('SG','Plato','Brix') DEFAULT 'SG',
  ADD COLUMN densidad_post_hervor FLOAT,
  ADD COLUMN densidad_post_hervor_unidad ENUM('SG','Plato','Brix') DEFAULT 'SG',
  ADD COLUMN fermentacion_tiempo_total_h FLOAT,
  ADD COLUMN fermentacion_temp_media FLOAT,
  ADD COLUMN fermentacion_caida_densidad_dia_max FLOAT,
  ADD COLUMN maduracion_tiempo_total_h FLOAT,
  ADD COLUMN maduracion_temp_media FLOAT;

-- =====================================================
-- 2. AMPLIAR TABLA batches_enfriado
-- =====================================================
ALTER TABLE batches_enfriado
  ADD COLUMN do_ppm FLOAT,
  ADD COLUMN temperatura_salida FLOAT,
  ADD COLUMN caudal_mosto FLOAT,
  ADD COLUMN caudal_agua FLOAT;

-- =====================================================
-- 3. AMPLIAR TABLA registros_limpiezas (CIP)
-- =====================================================
ALTER TABLE registros_limpiezas
  ADD COLUMN programa_cip VARCHAR(100),
  ADD COLUMN temperatura_max_cip FLOAT,
  ADD COLUMN tiempo_total_cip_min INT,
  ADD COLUMN conductividad_promedio FLOAT,
  ADD COLUMN cip_timestamp_inicio DATETIME,
  ADD COLUMN cip_timestamp_fin DATETIME,
  ADD COLUMN id_batches_posterior VARCHAR(36);

-- =====================================================
-- 4. AMPLIAR TABLA insumos (MateriaPrima + Levadura)
-- =====================================================
-- Campos generales MateriaPrima
ALTER TABLE insumos
  ADD COLUMN marca VARCHAR(100),
  ADD COLUMN materia_prima_basica VARCHAR(200),
  ADD COLUMN cosecha_anio INT,
  ADD COLUMN presentacion VARCHAR(200),
  ADD COLUMN vida_util_meses INT;

-- Campos específicos para levaduras
ALTER TABLE insumos
  ADD COLUMN cepa VARCHAR(100),
  ADD COLUMN tipo_levadura ENUM('ale_seca','ale_liquida','lager_seca','lager_liquida','wild'),
  ADD COLUMN atenuacion_min FLOAT,
  ADD COLUMN atenuacion_max FLOAT,
  ADD COLUMN floculacion ENUM('baja','media','alta','muy_alta'),
  ADD COLUMN temp_fermentacion_min FLOAT,
  ADD COLUMN temp_fermentacion_max FLOAT,
  ADD COLUMN tolerancia_alcohol FLOAT;

-- =====================================================
-- 5. CREAR TABLA batch_signals (Series Temporales)
-- =====================================================
CREATE TABLE batch_signals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_batches VARCHAR(36) NOT NULL,
  etapa ENUM('Maceracion','Lavado','Coccion','Enfriado','Fermentacion','Maduracion','Envasado','CIP') NOT NULL,
  variable VARCHAR(50) NOT NULL,
  timestamp DATETIME NOT NULL,
  valor FLOAT NOT NULL,
  unidad VARCHAR(20),
  INDEX idx_batch_signals_batch (id_batches),
  INDEX idx_batch_signals_etapa (id_batches, etapa),
  INDEX idx_batch_signals_timestamp (timestamp),
  INDEX idx_batch_signals_variable (id_batches, variable)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 6. CREAR TABLA batch_levaduras
-- =====================================================
CREATE TABLE batch_levaduras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_batches VARCHAR(36) NOT NULL,
  id_batches_insumos INT,
  generacion INT DEFAULT 1,
  origen_batch VARCHAR(36),
  cantidad_gramos FLOAT,
  tasa_inoculacion FLOAT,
  viabilidad_medida FLOAT,
  vitalidad_medida FLOAT,
  uso_starter TINYINT(1) DEFAULT 0,
  volumen_starter_ml INT,
  atenuacion_real FLOAT,
  tiempo_lag_h FLOAT,
  observaciones TEXT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_batch_levaduras_batch (id_batches),
  INDEX idx_batch_levaduras_generacion (generacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 7. CREAR TABLA batch_analiticas
-- =====================================================
CREATE TABLE batch_analiticas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_batches VARCHAR(36) NOT NULL,
  momento ENUM('PreMaceracion','PreBoil','PostBoil','PreFermentacion',
               'MidFermentacion','PreEnvasado','PostEnvasado') NOT NULL,
  densidad FLOAT,
  densidad_unidad ENUM('SG','Plato','Brix') DEFAULT 'SG',
  ph FLOAT,
  co2_disuelto FLOAT,
  do_ppm FLOAT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  observaciones TEXT,
  INDEX idx_batch_analiticas_batch (id_batches),
  INDEX idx_batch_analiticas_momento (id_batches, momento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

*Documento actualizado el 2025-12-13*
*Incluye: REQ2 original + Ampliación Modelo ML/PLC*
