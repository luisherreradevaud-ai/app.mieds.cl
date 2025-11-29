# Plan de Implementación Completo: Barril.cl
## Cerveza Cocholgue - Roadmap de Mejoras y Expansión

**Fecha:** 27 de Noviembre, 2025
**Versión:** 1.0
**Basado en:** ANALISIS_TRAZABILIDAD_BARRIL.md

---

## Tabla de Contenidos

1. [Resumen Ejecutivo](#1-resumen-ejecutivo)
2. [Tareas de Mejora de Trazabilidad](#2-tareas-de-mejora-de-trazabilidad)
3. [Expansión a Formato Latas](#3-expansión-a-formato-latas)
4. [Certificación Halal para Medio Oriente](#4-certificación-halal-para-medio-oriente)
5. [Roadmap de Implementación](#5-roadmap-de-implementación)
6. [Estimación de Recursos](#6-estimación-de-recursos)
7. [Análisis de Riesgos](#7-análisis-de-riesgos)
8. [Indicadores de Éxito](#8-indicadores-de-éxito)

---

## 1. Resumen Ejecutivo

### 1.1 Visión General

Este documento presenta un **plan de implementación completo** para las mejoras del sistema Barril.cl, dividido en tres grandes categorías:

1. **Mejoras de Trazabilidad** (5 soluciones - 22-40 horas)
2. **Expansión a Latas** (8 tareas - 45-61 horas)
3. **Certificación Halal** (10 tareas - 200+ horas + $25K-50K USD)

### 1.2 Priorización Estratégica

```
🔴 CRÍTICO (0-3 meses)
   ├─ Solución 5.1: Cliente en Despacho (2-4 hrs)
   └─ Solución 5.2: BatchActivo en Llenado (3-5 hrs)

🟠 ALTO (3-6 meses)
   ├─ Solución 5.4: Vista de Trazabilidad (6-10 hrs)
   └─ Solución 5.5: Códigos QR (6-8 hrs)

🟡 MEDIO (6-12 meses)
   ├─ Solución 5.3: Consumo Parcial (5-8 hrs)
   └─ Expansión Latas: Sistema Completo (45-61 hrs)

🟢 BAJO (12+ meses)
   └─ Certificación Halal: Proceso Completo (200+ hrs + $25K-50K)
```

### 1.3 Inversión Total Estimada

| Categoría | Horas | Inversión USD | ROI Esperado |
|-----------|-------|---------------|--------------|
| **Mejoras Trazabilidad** | 22-40 | $2,200-4,000 | Eficiencia operacional |
| **Expansión Latas** | 45-61 | $4,500-6,100 | Nuevo canal de venta |
| **Certificación Halal** | 200+ | $25,400-49,700 | Mercado internacional (0.0% ABV) |
| **TOTAL** | **267-301+** | **$32,100-59,800** | Variable |

*Costo hora estimado: $100 USD*

---

## 2. Tareas de Mejora de Trazabilidad

### 2.1 Solución 5.1: Agregar Cliente a Despacho

**ID:** TRAZ-001
**Prioridad:** 🔴 CRÍTICA
**Estado:** Pendiente

#### Descripción

Agregar campo `id_clientes` a la entidad `Despacho` para rastrear el cliente destino desde el momento de creación del despacho.

#### Problema que Resuelve

**P1 (CRÍTICO):** El despacho actual no tiene información de hacia dónde va, solo se sabe el destino cuando se crea la entrega.

#### Componentes Afectados

| Archivo | Tipo | Cambio |
|---------|------|--------|
| `php/classes/Despacho.php` | Clase | Agregar campo `public $id_clientes = 0;` |
| `templates/central-despacho.php` | Vista | Agregar selector y columna cliente |
| `templates/nuevo-despachos.php` | Vista | Agregar campo cliente (required) |
| `ajax/ajax_guardarDespacho.php` | AJAX | Manejar nuevo campo |
| Database: `despachos` | SQL | `ALTER TABLE` agregar columna |

#### Desglose de Tareas

1. **Modificación de Base de Datos** (30 min)
   ```sql
   ALTER TABLE despachos
   ADD COLUMN id_clientes INT DEFAULT 0 AFTER id_usuarios_repartidor,
   ADD INDEX idx_id_clientes (id_clientes);
   ```

2. **Modificación de Clase** (15 min)
   ```php
   // php/classes/Despacho.php
   public $id_clientes = 0;  // NUEVO CAMPO
   ```

3. **Vista Central Despacho - Selector** (45 min)
   - Agregar `<select>` de clientes en formulario nuevo despacho
   - Marcar como `required`
   - Obtener lista de clientes activos

4. **Vista Central Despacho - Listado** (30 min)
   - Agregar columna "Cliente" en tabla de despachos
   - Mostrar nombre del cliente desde `id_clientes`
   - Actualizar diseño responsive

5. **AJAX Guardar Despacho** (30 min)
   - Validar que `id_clientes` no sea 0
   - Guardar campo en base de datos

6. **Testing** (30 min)
   - Crear despacho con cliente
   - Verificar que se guarda correctamente
   - Verificar listado muestra cliente
   - Testing de validación

#### Estimación

- **Tiempo:** 2-4 horas
- **Riesgo:** BAJO
- **Dependencias:** Ninguna
- **Recursos:** 1 desarrollador

#### Beneficios

✅ Trazabilidad completa desde creación del despacho
✅ Permite planificación de rutas por cliente
✅ Facilita reportes de despachos por cliente
✅ Mejora auditoría de entregas

#### Criterios de Aceptación

- [ ] Despacho tiene campo `id_clientes` en base de datos
- [ ] Formulario nuevo despacho requiere selección de cliente
- [ ] Listado de despachos muestra nombre del cliente
- [ ] Reportes pueden filtrar por cliente
- [ ] Tests pasan correctamente

---

### 2.2 Solución 5.2: Actualizar BatchActivo al Llenar Barriles

**ID:** TRAZ-002
**Prioridad:** 🔴 CRÍTICA
**Estado:** Pendiente

#### Descripción

Cuando se llena un barril desde un fermentador, descontar automáticamente la cantidad del campo `BatchActivo.litraje` para mantener inventario preciso en tiempo real.

#### Problema que Resuelve

**P2 (MEDIO):** Pérdida de trazabilidad en cargas parciales. No se sabe cuántos litros quedan disponibles en el fermentador.

#### Componentes Afectados

| Archivo | Tipo | Cambio |
|---------|------|--------|
| `ajax/ajax_llenarBarriles.php` | AJAX | Lógica de descuento y validación |
| `templates/inventario-de-productos.php` | Vista | Actualización en tiempo real |

#### Desglose de Tareas

1. **Modificación AJAX LlenarBarriles** (2 hrs)
   - Obtener BatchActivo antes de llenar
   - Validar que `BatchActivo.litraje >= cantidad_a_cargar`
   - Descontar: `BatchActivo.litraje -= cantidad_a_cargar`
   - Si `BatchActivo.litraje <= 0`: liberar Activo (`id_batches = 0`)
   - Retornar litraje restante en respuesta

2. **Actualización Vista Inventario** (30 min)
   - Actualizar display de litros disponibles en modal
   - Actualizar lista de fermentadores disponibles
   - Deshabilitar fermentadores vacíos

3. **Validaciones** (1 hr)
   - Prevenir llenado si no hay suficiente líquido
   - Prevenir valores negativos
   - Mensaje de error claro si falla validación

4. **Testing Exhaustivo** (1.5 hrs)
   - Llenado parcial (50% del barril)
   - Llenado completo (100% del barril)
   - Múltiples barriles desde mismo fermentador
   - Vaciado completo de fermentador
   - Edge cases (0.1L restante, etc.)

#### Estimación

- **Tiempo:** 3-5 horas
- **Riesgo:** MEDIO (lógica crítica de inventario)
- **Dependencias:** Ninguna
- **Recursos:** 1 desarrollador + 1 QA

#### Código de Referencia

```php
// ajax/ajax_llenarBarriles.php - NUEVO CÓDIGO

$batch_activo = new BatchActivo($id_batches_activos);
$barril = new Barril($id_barriles);

// Validar disponibilidad
if($batch_activo->litraje < $cantidad_a_cargar) {
    echo json_encode([
        'status' => 'ERROR',
        'mensaje' => 'No hay suficiente líquido en el fermentador. Disponible: '.$batch_activo->litraje.'L'
    ]);
    exit;
}

// Actualizar BatchActivo
$batch_activo->litraje -= $cantidad_a_cargar;
$batch_activo->save();

// Actualizar Barril
$barril->litros_cargados += $cantidad_a_cargar;
$barril->id_batches = $batch_activo->id_batches;
$barril->id_activos = $batch_activo->id_activos;
$barril->id_batches_activos = $batch_activo->id;
$barril->save();

// Si fermentador vacío, liberar
if($batch_activo->litraje <= 0) {
    $activo = new Activo($batch_activo->id_activos);
    $activo->id_batches = 0;
    $activo->save();
}

echo json_encode([
    'status' => 'OK',
    'litraje_restante' => $batch_activo->litraje
]);
```

#### Beneficios

✅ Inventario preciso en tiempo real
✅ Previene sobrellenado de barriles
✅ Permite planificación exacta de envasado
✅ Mejora trazabilidad de volumen

#### Criterios de Aceptación

- [ ] BatchActivo.litraje se descuenta al llenar barril
- [ ] No permite llenar si no hay suficiente líquido
- [ ] Fermentador se libera cuando queda vacío
- [ ] Vista muestra litraje actualizado en tiempo real
- [ ] Tests de edge cases pasan correctamente

---

### 2.3 Solución 5.3: Registro de Consumo Parcial

**ID:** TRAZ-003
**Prioridad:** 🟡 MEDIA
**Estado:** Pendiente

#### Descripción

Crear sistema para registrar el consumo parcial de barriles en el cliente, permitiendo planificación proactiva de recambio.

#### Problema que Resuelve

**P3 (BAJO-MEDIO):** No se puede saber si un barril está "casi vacío" vs "recién entregado" en el cliente.

#### Componentes Afectados

| Archivo | Tipo | Cambio |
|---------|------|--------|
| Database: `barriles_consumos` | SQL | Nueva tabla |
| `php/classes/BarrilConsumo.php` | Clase | Nueva clase |
| `templates/detalle-clientes.php` | Vista | Modal de consumo |
| `templates/repartidor.php` | Vista | Integración |
| `ajax/ajax_guardarEntidad.php` | AJAX | Manejo de entidad |

#### Desglose de Tareas

1. **Crear Tabla** (30 min)
   ```sql
   CREATE TABLE barriles_consumos (
       id INT AUTO_INCREMENT PRIMARY KEY,
       id_barriles INT NOT NULL,
       id_clientes INT NOT NULL,
       litros_consumidos DECIMAL(10,2) NOT NULL,
       litros_restantes DECIMAL(10,2) NOT NULL,
       fecha_consumo DATETIME NOT NULL,
       observaciones TEXT,
       INDEX idx_id_barriles (id_barriles),
       INDEX idx_id_clientes (id_clientes)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   ```

2. **Crear Clase BarrilConsumo** (1 hr)
   - Constructor estándar
   - Método `validarConsumo()`
   - Método `calcularRestante()`

3. **Vista Modal en Detalle Clientes** (2 hrs)
   - Modal con formulario
   - Selector de barril del cliente
   - Input litros consumidos
   - Auto-cálculo de litros restantes
   - Observaciones (textarea)

4. **JavaScript Auto-cálculo** (1 hr)
   - Listener en input consumidos
   - Calcular: `restantes = actuales - consumidos`
   - Validar que consumidos <= actuales
   - Mensaje de advertencia si barril casi vacío

5. **Integración Repartidor** (1 hr)
   - Mostrar litros restantes en vista barriles cliente
   - Indicador visual (barra de progreso)
   - Filtro para barriles casi vacíos

6. **Testing** (1.5 hrs)
   - Registrar consumo parcial
   - Validación de cantidades
   - Vista en historial
   - Indicadores visuales

#### Estimación

- **Tiempo:** 5-8 horas
- **Riesgo:** BAJO (feature independiente)
- **Dependencias:** Ninguna
- **Recursos:** 1 desarrollador

#### Beneficios

✅ Visibilidad del consumo real
✅ Planificación proactiva de recambio
✅ Análisis de patrones de consumo por cliente
✅ Base para sistema de predicción

#### Criterios de Aceptación

- [ ] Tabla `barriles_consumos` creada
- [ ] Clase BarrilConsumo funcional
- [ ] Modal permite registrar consumo
- [ ] Cálculo automático de restantes correcto
- [ ] Vista cliente muestra litros restantes
- [ ] Indicador visual funciona correctamente

---

### 2.4 Solución 5.4: Vista Consolidada de Trazabilidad

**ID:** TRAZ-004
**Prioridad:** 🟠 ALTA
**Estado:** Pendiente

#### Descripción

Crear vista única que muestra la trazabilidad completa de un barril desde producción hasta ubicación actual, con línea de tiempo visual.

#### Problema que Resuelve

**P6:** No hay vista consolidada, hay que navegar por múltiples páginas para rastrear un barril completo.

#### Componentes Afectados

| Archivo | Tipo | Cambio |
|---------|------|--------|
| `templates/detalle-trazabilidad-barril.php` | Vista | Nueva vista completa |
| `templates/detalle-barriles.php` | Vista | Agregar enlace |
| `index.php` | Router | Agregar ruta |

#### Desglose de Tareas

1. **Crear Vista Trazabilidad** (4 hrs)
   - Layout con timeline vertical
   - Sección 1: Producción (Batch, Receta, Insumos)
   - Sección 2: Fermentación (Activo, Estado)
   - Sección 3: Envasado (Barril, Litros)
   - Sección 4: Entregas (Todas las entregas)
   - Sección 5: Estado Actual
   - Tabla historial de estados completo

2. **Estilos CSS Timeline** (1.5 hrs)
   - Timeline vertical con línea conectora
   - Marcadores de color por etapa
   - Cards con información detallada
   - Responsive design
   - Colapsables para detalles (accordions)

3. **Integración con Detalle Barril** (30 min)
   - Botón "Ver Trazabilidad Completa"
   - Enlace con id del barril
   - Return button desde trazabilidad

4. **Router** (15 min)
   - Agregar case en `switch_templates()`
   - Validar id de barril existe

5. **Funcionalidad Imprimir/PDF** (2 hrs)
   - Botón imprimir (print-friendly CSS)
   - Opción exportar a PDF
   - Logo empresa en header

6. **Testing** (1.5 hrs)
   - Visualización de todos los elementos
   - Timeline se muestra correctamente
   - Responsive en móvil/tablet
   - Impresión se ve correcta

#### Estimación

- **Tiempo:** 6-10 horas
- **Riesgo:** BAJO
- **Dependencias:** Ninguna
- **Recursos:** 1 desarrollador frontend

#### Wireframe Conceptual

```
┌─────────────────────────────────────────┐
│  TRAZABILIDAD: BARRIL BC-001            │
│  [Imprimir] [PDF]           [← Volver]  │
├─────────────────────────────────────────┤
│                                         │
│  ●  PRODUCCIÓN                         │
│  │  Batch #123 - IPA (500L)           │
│  │  Fecha: 01/11/2025                 │
│  │  [Ver Insumos ▼]                   │
│  │                                     │
│  ●  FERMENTACIÓN                       │
│  │  Fermentador BD-01                 │
│  │  Estado: Maduración                │
│  │  Litraje: 500L                     │
│  │                                     │
│  ●  ENVASADO                           │
│  │  Barril BC-001 (50L)               │
│  │  Cargado: 50L                      │
│  │  Fecha: 15/11/2025                 │
│  │                                     │
│  ●  ENTREGA #78                        │
│  │  Cliente: Restaurant XYZ           │
│  │  Repartidor: Pedro                 │
│  │  Fecha: 20/11/2025 15:30          │
│  │                                     │
│  ●  ESTADO ACTUAL                      │
│     En terreno - Cliente XYZ          │
│                                         │
├─────────────────────────────────────────┤
│  HISTORIAL COMPLETO DE ESTADOS         │
│  [Tabla con todos los cambios]         │
└─────────────────────────────────────────┘
```

#### Beneficios

✅ Vista unificada de toda la trazabilidad
✅ Fácil presentación a clientes
✅ Rápida resolución de reclamaciones
✅ Auditorías simplificadas
✅ Exportable a PDF para certificaciones

#### Criterios de Aceptación

- [ ] Vista muestra timeline completo
- [ ] Todas las etapas se visualizan correctamente
- [ ] Insumos se muestran en detalle
- [ ] Historial de entregas completo
- [ ] Tabla de estados con duraciones
- [ ] Diseño responsive funciona
- [ ] Impresión se ve profesional

---

### 2.5 Solución 5.5: Códigos QR para Trazabilidad

**ID:** TRAZ-005
**Prioridad:** 🟠 ALTA
**Estado:** Pendiente

#### Descripción

Generar códigos QR únicos para cada barril que permitan acceso instantáneo a la vista de trazabilidad completa desde smartphone.

#### Problema que Resuelve

Acceso rápido a trazabilidad sin navegar el sistema, útil para auditorías in-situ y presentación a clientes.

#### Componentes Afectados

| Archivo | Tipo | Cambio |
|---------|------|--------|
| `php/classes/Barril.php` | Clase | Método `generarQR()` |
| `templates/detalle-barriles.php` | Vista | Sección QR |
| `ajax/ajax_generarQRBarril.php` | AJAX | Generación on-demand |
| `/media/qr/` | Directorio | Almacenamiento de QR |
| `composer.json` | Config | Librería endroid/qr-code |

#### Desglose de Tareas

1. **Instalar Librería QR** (30 min)
   ```bash
   composer require endroid/qr-code
   ```

2. **Método generarQR en Barril** (2 hrs)
   ```php
   use Endroid\QrCode\QrCode;
   use Endroid\QrCode\Writer\PngWriter;

   public function generarQR() {
       $url = "https://app.barril.cl/?s=detalle-trazabilidad-barril&id=".$this->id;
       $qr_code = QrCode::create($url)->setSize(300)->setMargin(10);
       $writer = new PngWriter();
       $result = $writer->write($qr_code);
       $path = $GLOBALS['base_dir']."/media/qr/barril_".$this->codigo.".png";
       $result->saveToFile($path);
       return $path;
   }
   ```

3. **Integración en setSpecifics** (30 min)
   - Generar QR automáticamente al crear barril
   - Regenerar si se cambia código

4. **Vista Detalle Barril** (1.5 hrs)
   - Card con QR code
   - Botón descargar QR
   - Botón regenerar QR
   - Texto explicativo

5. **AJAX Generar QR** (1 hr)
   - Endpoint para generación on-demand
   - Validación de barril existe
   - Retornar path del QR

6. **Testing** (1.5 hrs)
   - Generar QR para barril nuevo
   - Regenerar QR para barril existente
   - Escanear QR con smartphone
   - Verificar enlace correcto
   - Testing en iOS y Android

#### Estimación

- **Tiempo:** 6-8 horas
- **Riesgo:** BAJO
- **Dependencias:** TRAZ-004 (Vista Trazabilidad)
- **Recursos:** 1 desarrollador + dispositivos móviles

#### Beneficios

✅ Acceso instantáneo desde smartphone
✅ Útil para auditorías in-situ
✅ Presentación profesional a clientes
✅ Automatización de verificaciones

#### Caso de Uso

> Un inspector de calidad visita el restaurante XYZ y escanea el QR del barril BC-001. Instantáneamente ve en su celular:
> - Receta: IPA Cocholgue
> - Batch: #123 (01/11/2025)
> - Insumos utilizados (malta, lúpulo, levadura)
> - Fermentador: BD-01
> - Fecha envasado: 15/11/2025
> - Historial completo de movimientos

#### Criterios de Aceptación

- [ ] Librería QR instalada vía Composer
- [ ] Método generarQR() funciona
- [ ] QR se genera al crear barril
- [ ] QR se muestra en detalle barril
- [ ] Escanear QR abre vista trazabilidad
- [ ] Botón descargar funciona
- [ ] QR es legible en móvil

---

## 3. Expansión a Formato Latas

### 3.1 Visión General

La expansión del sistema para soportar **envasado en latas** (además de barriles) requiere un enfoque de **trazabilidad por lote**, no por unidad individual.

**Diferencia clave:** Mientras los barriles se rastrean individualmente (cada barril tiene ID único y historial), las latas se rastrean por **lotes de envasado** (grupos de latas producidas de un mismo batch).

### 3.2 Arquitectura Propuesta

```
Batch #123 (500L) → BatchActivo (Fermentador BD-01)
                         ↓
                   [ENVASADO]
                    ↙       ↘
           BARRILES         LATAS
       (Individual)      (Por Lote)
              ↓              ↓
     Barril BC-001    LoteEnvasado LE-2025-001
     Barril BC-002    - Tipo: Lata 350ml
     Barril BC-003    - Cantidad: 1,000 latas
                      - Litros: 350L
                      - Caducidad: 15/05/2026
```

### 3.3 Tipos de Envase Soportados

| Tipo | Volumen | Latas por Litro | Uso Típico |
|------|---------|-----------------|------------|
| Lata 350ml | 0.35L | 2.86 | Retail, supermercados |
| Lata 473ml | 0.473L | 2.11 | Retail, importado USA |
| Lata 500ml | 0.50L | 2.00 | Retail, mercado chileno |
| Botella 330ml | 0.33L | 3.03 | Retail, restaurantes |
| Botella 500ml | 0.50L | 2.00 | Retail, formato premium |

---

### 3.4 Tarea 6.1: Crear Tabla lotes_envasados

**ID:** LATAS-001
**Prioridad:** 🟠 ALTA* (si expansión aprobada)
**Estado:** Pendiente

#### Descripción

Crear tabla de base de datos para almacenar lotes de envasado (batches de latas/botellas).

#### SQL Schema

```sql
CREATE TABLE lotes_envasados (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Identificación
    codigo VARCHAR(50) UNIQUE NOT NULL,           -- LE-2025-001
    lote_produccion VARCHAR(50),                  -- LOT251127A

    -- Origen (trazabilidad)
    id_batches INT NOT NULL,
    id_activos INT NOT NULL,
    id_batches_activos INT NOT NULL,

    -- Información del envasado
    tipo_envase VARCHAR(20) NOT NULL,             -- 'Lata 350ml', etc
    cantidad_envasada INT NOT NULL,
    litros_utilizados DECIMAL(10,2) NOT NULL,

    -- Fechas
    fecha_envasado DATETIME NOT NULL,
    fecha_caducidad DATE NOT NULL,

    -- Control de inventario
    cantidad_disponible INT NOT NULL,
    cantidad_despachada INT DEFAULT 0,
    cantidad_vendida INT DEFAULT 0,
    cantidad_merma INT DEFAULT 0,

    -- Ubicación
    ubicacion VARCHAR(100) DEFAULT 'Bodega',

    -- Metadata
    creada DATETIME NOT NULL,
    actualizada DATETIME,

    -- Índices
    INDEX idx_id_batches (id_batches),
    INDEX idx_codigo (codigo),
    INDEX idx_fecha_envasado (fecha_envasado),
    INDEX idx_fecha_caducidad (fecha_caducidad),
    INDEX idx_tipo_envase (tipo_envase)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Estimación

- **Tiempo:** 1 hora
- **Riesgo:** BAJO
- **Dependencias:** Ninguna

---

### 3.5 Tarea 6.2: Crear Clase LoteEnvasado

**ID:** LATAS-002
**Prioridad:** 🟠 ALTA*
**Estado:** Pendiente

#### Descripción

Crear clase PHP para gestionar lotes de envasado con generación automática de códigos, cálculo de caducidad, y manejo de inventario.

#### Métodos Clave

```php
<?php
class LoteEnvasado extends Base {

    // Campos...

    /**
     * Genera código único: LE-YYYY-NNN
     * LE-2025-001, LE-2025-002, etc.
     */
    private function generarCodigo() {
        $anio = date('Y');
        $ultimo_lote = self::getAll("WHERE codigo LIKE 'LE-".$anio."-%' ORDER BY id desc LIMIT 1");

        if(count($ultimo_lote) > 0) {
            $codigo_anterior = $ultimo_lote[0]->codigo;
            $numero = intval(substr($codigo_anterior, -3)) + 1;
        } else {
            $numero = 1;
        }

        return "LE-".$anio."-".str_pad($numero, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Genera código de lote para etiqueta: LOTYYMDD#
     * LOT251127A, LOT251127B, etc.
     */
    private function generarLoteProduccion() {
        $fecha = date('ymd');
        $lotes_hoy = self::getAll("WHERE DATE(fecha_envasado) = '".date('Y-m-d')."'");
        $secuencia = count($lotes_hoy) + 1;
        return "LOT".$fecha.chr(64 + $secuencia); // A, B, C...
    }

    /**
     * Calcula fecha de caducidad (+6 meses)
     */
    public function calcularFechaCaducidad() {
        $fecha = new DateTime($this->fecha_envasado);
        $fecha->modify('+6 months');
        $this->fecha_caducidad = $fecha->format('Y-m-d');
    }

    /**
     * Registra consumo de latas
     */
    public function consumir($cantidad, $tipo = 'despachada') {
        if($this->cantidad_disponible < $cantidad) {
            return false;
        }

        $this->cantidad_disponible -= $cantidad;

        if($tipo == 'despachada') {
            $this->cantidad_despachada += $cantidad;
        } elseif($tipo == 'vendida') {
            $this->cantidad_vendida += $cantidad;
        } elseif($tipo == 'merma') {
            $this->cantidad_merma += $cantidad;
        }

        $this->save();
        return true;
    }

    /**
     * Verifica si está próximo a caducar
     */
    public function proximoACaducar($dias = 30) {
        $hoy = new DateTime();
        $caducidad = new DateTime($this->fecha_caducidad);
        $diferencia = $hoy->diff($caducidad);
        return $diferencia->days <= $dias;
    }

    /**
     * Verifica si está caducado
     */
    public function estaCaducado() {
        return strtotime($this->fecha_caducidad) < time();
    }
}
?>
```

#### Estimación

- **Tiempo:** 4-6 horas
- **Riesgo:** BAJO
- **Dependencias:** LATAS-001

---

### 3.6 Tarea 6.3: Vista Nuevo Lote Envasado

**ID:** LATAS-003
**Prioridad:** 🟠 ALTA*
**Estado:** Pendiente

#### Descripción

Crear interfaz para registrar nuevos lotes de envasado con cálculos automáticos y validaciones.

#### Componentes

- `templates/nuevo-lote-envasado.php`
- `ajax/ajax_guardarLoteEnvasado.php`

#### Secciones del Formulario

**1. Origen del Producto**
- Selector de fermentador (solo en maduración)
- Muestra: Batch, Receta, Litros disponibles

**2. Información de Envasado**
- Tipo de envase (dropdown)
- Litros a utilizar (validado contra disponible)
- Cantidad estimada (auto-calculada)
- Cantidad real envasada (input)
- Ubicación (dropdown: Bodega, Cámara fría, etc.)

**3. Información Generada Automáticamente**
- Código de lote (LE-2025-XXX)
- Lote de producción (LOTYYMDD#)
- Fecha de caducidad (+6 meses)

#### JavaScript Auto-cálculo

```javascript
// Calcular cantidad estimada
$('#tipo-envase-select, #litros-utilizar-input').on('change input', function() {
    var tipo_envase = $('#tipo-envase-select').val();
    var litros = parseFloat($('#litros-utilizar-input').val());

    var volumen_unidad = {
        'Lata 350ml': 0.35,
        'Lata 473ml': 0.473,
        'Lata 500ml': 0.50,
        'Botella 330ml': 0.33,
        'Botella 500ml': 0.50
    };

    var cantidad_estimada = Math.floor(litros / volumen_unidad[tipo_envase]);
    $('#cantidad-estimada-display').text(cantidad_estimada + ' unidades');
});
```

#### Estimación

- **Tiempo:** 6-8 horas
- **Riesgo:** MEDIO
- **Dependencias:** LATAS-001, LATAS-002

---

### 3.7 Tarea 6.4: Dashboard Inventario de Latas

**ID:** LATAS-004
**Prioridad:** 🟠 ALTA*
**Estado:** Pendiente

#### Descripción

Crear dashboard completo para gestión de inventario de latas con KPIs, alertas de caducidad, y agrupación por receta.

#### Componentes

- `templates/inventario-latas.php`

#### Secciones del Dashboard

**1. KPIs (Cards superiores)**
```
┌──────────────────┬──────────────────┬──────────────────┬──────────────────┐
│  Total Latas     │  Próximas a      │  Lotes Caducados │  Lotes Activos   │
│  15,420 unidades │  Caducar: 3      │  2 lotes         │  12 lotes        │
└──────────────────┴──────────────────┴──────────────────┴──────────────────┘
```

**2. Inventario por Receta (Accordion)**

```php
foreach($recetas as $receta) {
    $lotes = LoteEnvasado::getAll("
        JOIN batches ON batches.id = lotes_envasados.id_batches
        WHERE batches.id_recetas = '".$receta->id."'
          AND lotes_envasados.cantidad_disponible > 0
        ORDER BY lotes_envasados.fecha_caducidad ASC
    ");
    ?>
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed">
                <?= $receta->nombre; ?>
                <span class="badge"><?= array_sum($lotes, 'cantidad_disponible'); ?> latas</span>
            </button>
        </h2>
        <div class="accordion-collapse collapse">
            <div class="accordion-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Envasado</th>
                            <th>Caducidad</th>
                            <th>Disponible</th>
                            <th>Ubicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($lotes as $lote) { ?>
                        <tr class="<?= $lote->proximoACaducar() ? 'table-warning' : ''; ?>">
                            <td><?= $lote->codigo; ?></td>
                            <td><?= $lote->tipo_envase; ?></td>
                            <td><?= date2fecha($lote->fecha_envasado); ?></td>
                            <td><?= date2fecha($lote->fecha_caducidad); ?></td>
                            <td><?= $lote->cantidad_disponible; ?></td>
                            <td><?= $lote->ubicacion; ?></td>
                            <td><a href="./?s=detalle-lote-envasado&id=<?= $lote->id; ?>">Ver</a></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}
```

**3. Alertas Visuales**
- 🟡 Amarillo: Lotes próximos a caducar (30 días)
- 🔴 Rojo: Lotes caducados
- 🟢 Verde: Lotes normales

#### Estimación

- **Tiempo:** 8-10 horas
- **Riesgo:** BAJO
- **Dependencias:** LATAS-001, LATAS-002, LATAS-003

---

### 3.8 Tarea 6.5: Vista Detalle Lote Envasado

**ID:** LATAS-005
**Prioridad:** 🟡 MEDIA*
**Estado:** Pendiente

#### Descripción

Vista detallada de un lote específico con trazabilidad completa y historial de consumo.

#### Componentes

- `templates/detalle-lote-envasado.php`

#### Secciones

**1. Información del Lote**
- Código, Tipo de envase, Lote de producción
- Fechas (envasado, caducidad)
- Estado (Disponible / Próximo a caducar / Caducado)

**2. Origen (Trazabilidad)**
- Batch origen
- Receta
- Fermentador utilizado

**3. Estado de Inventario**
- Cantidad envasada
- Cantidad disponible
- Cantidad despachada
- Cantidad vendida
- Merma

**4. Historial de Despachos**
- Tabla con todos los despachos que incluyeron este lote
- Fecha, Cliente, Cantidad

#### Estimación

- **Tiempo:** 4-6 horas
- **Riesgo:** BAJO
- **Dependencias:** LATAS-001, LATAS-002

---

### 3.9 Tarea 6.6: Integración con Sistema de Despachos

**ID:** LATAS-006
**Prioridad:** 🟠 ALTA*
**Estado:** Pendiente

#### Descripción

Modificar sistema de despachos para soportar tanto barriles como lotes de latas.

#### Componentes Afectados

| Archivo | Cambio |
|---------|--------|
| `php/classes/DespachoProducto.php` | Agregar campo `id_lotes_envasados` |
| `templates/central-despacho.php` | Agregar selector de lotes |
| `templates/nuevo-despachos.php` | Interfaz selección lotes |
| `ajax/ajax_guardarDespacho.php` | Manejar lotes |

#### Modificaciones

**1. Database**
```sql
ALTER TABLE despachos_productos
ADD COLUMN id_lotes_envasados INT DEFAULT 0 AFTER id_barriles,
ADD INDEX idx_id_lotes_envasados (id_lotes_envasados);
```

**2. Clase DespachoProducto**
```php
public $id_lotes_envasados = 0;
```

**3. Interfaz de Selección (FIFO)**

```php
// nuevo-despachos.php
<h4>Seleccionar Latas/Botellas</h4>
<table class="table">
    <thead>
        <tr>
            <th><input type="checkbox" id="select-all-lotes"></th>
            <th>Código Lote</th>
            <th>Tipo</th>
            <th>Receta</th>
            <th>Disponible</th>
            <th>Caducidad</th>
            <th>Cantidad</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // FIFO: Ordenar por fecha de caducidad ASC
        $lotes_disponibles = LoteEnvasado::getAll("
            WHERE cantidad_disponible > 0
            ORDER BY fecha_caducidad ASC
        ");

        foreach($lotes_disponibles as $lote) {
            $batch = new Batch($lote->id_batches);
            $receta = new Receta($batch->id_recetas);

            $clase_fila = '';
            if($lote->estaCaducado()) {
                $clase_fila = 'table-danger';
            } elseif($lote->proximoACaducar()) {
                $clase_fila = 'table-warning';
            }
        ?>
        <tr class="<?= $clase_fila; ?>">
            <td><input type="checkbox" name="lotes[]" value="<?= $lote->id; ?>"></td>
            <td><?= $lote->codigo; ?></td>
            <td><?= $lote->tipo_envase; ?></td>
            <td><?= $receta->nombre; ?></td>
            <td><?= $lote->cantidad_disponible; ?></td>
            <td><?= date2fecha($lote->fecha_caducidad); ?></td>
            <td>
                <input type="number"
                       name="cantidad_lote_<?= $lote->id; ?>"
                       class="form-control form-control-sm"
                       min="1"
                       max="<?= $lote->cantidad_disponible; ?>"
                       value="0">
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
```

**4. AJAX Guardar Despacho con Lotes**

```php
// ajax/ajax_guardarDespacho.php

// ... crear Despacho ...

// Guardar lotes
if(isset($_POST['lotes']) && is_array($_POST['lotes'])) {
    foreach($_POST['lotes'] as $id_lote) {
        $cantidad = intval($_POST['cantidad_lote_'.$id_lote]);

        if($cantidad <= 0) continue;

        $lote = new LoteEnvasado($id_lote);

        // Validar disponibilidad
        if($lote->cantidad_disponible < $cantidad) {
            echo json_encode([
                'status' => 'ERROR',
                'mensaje' => 'Lote '.$lote->codigo.' no tiene suficiente cantidad disponible'
            ]);
            exit;
        }

        // Crear DespachoProducto
        $dp = new DespachoProducto();
        $dp->id_despachos = $despacho->id;
        $dp->id_lotes_envasados = $lote->id;
        $dp->tipo = $lote->tipo_envase;
        $dp->cantidad = $cantidad;
        $dp->clasificacion = 'Cerveza'; // O desde batch
        $dp->save();

        // Descontar de lote
        $lote->consumir($cantidad, 'despachada');
    }
}
```

#### Estimación

- **Tiempo:** 6-8 horas
- **Riesgo:** MEDIO
- **Dependencias:** LATAS-001, LATAS-002, LATAS-003

---

### 3.10 Tarea 6.7: Reportes y Analíticas

**ID:** LATAS-007
**Prioridad:** 🟡 MEDIA*
**Estado:** Pendiente

#### Descripción

Crear sistema de reportes para producción, inventario, y análisis de latas.

#### Reportes a Crear

**1. Producción por Período**
- Lotes envasados por mes
- Cantidad total por tipo de envase
- Litros utilizados
- Gráfico de tendencia

**2. Inventario Turnover por Receta**
- Cantidad producida vs vendida
- Días promedio en inventario
- Rotación de inventario

**3. Alertas de Caducidad**
- Lotes próximos a caducar (30/60/90 días)
- Valor económico en riesgo
- Recomendaciones de despacho FIFO

**4. Análisis de Merma**
- Latas dañadas/perdidas por lote
- Porcentaje de merma por tipo de envase
- Tendencia de merma en el tiempo

**5. Ventas por Formato**
- Barriles vs Latas (cantidad y $)
- Tipo de envase más vendido
- Clientes por formato preferido

**6. Reporte FIFO Compliance**
- Lotes despachados en orden FIFO
- Alertas de FIFO no cumplido
- Edad promedio de inventario

#### Estimación

- **Tiempo:** 8-12 horas
- **Riesgo:** BAJO
- **Dependencias:** LATAS-001 a LATAS-006

---

### 3.11 Tarea 6.8: Testing Completo

**ID:** LATAS-008
**Prioridad:** 🟠 ALTA*
**Estado:** Pendiente

#### Descripción

Testing exhaustivo de todo el sistema de latas.

#### Test Cases

**Funcionales:**
1. Crear lote con Lata 350ml
2. Crear lote con Botella 500ml
3. Validar código único de lote
4. Validar cálculo de caducidad correcto
5. Consumir parcialmente un lote
6. Intentar consumir más de lo disponible (debe fallar)
7. Devolver latas a inventario
8. Despachar lotes con FIFO
9. Verificar integración con barriles

**Edge Cases:**
10. Lote con 0 disponibles
11. Lote caducado no debe poder despacharse
12. Fermentador con 0.1L restante
13. Cantidad envasada != cantidad estimada

**Performance:**
14. Dashboard con 100+ lotes
15. Queries de reportes con 1 año de data

**Integration:**
16. Crear despacho mixto (barriles + latas)
17. Trazabilidad completa lote → batch → insumos

#### Estimación

- **Tiempo:** 8-10 horas
- **Riesgo:** N/A
- **Dependencias:** LATAS-001 a LATAS-007

---

### 3.12 Resumen Expansión Latas

**Total de Tareas:** 8
**Tiempo Total:** 45-61 horas (6-8 días de desarrollo)
**Inversión Estimada:** $4,500-6,100 USD

**Componentes Nuevos:**
- 1 Tabla nueva (`lotes_envasados`)
- 1 Clase nueva (`LoteEnvasado`)
- 3 Vistas nuevas
- Modificaciones en 2 módulos existentes

**ROI Esperado:**
- Nuevo canal de venta (retail)
- Mayor volumen de producción
- Acceso a supermercados
- Diversificación de producto

---

## 4. Certificación Halal para Medio Oriente

### 4.1 Hallazgo Crítico

⚠️ **IMPORTANTE:** Cerveza alcohólica **NO puede ser certificada Halal**.

Solo productos con **0.0% ABV** (alcohol por volumen) pueden obtener certificación Halal.

**Fundamento Islámico:**
- **Corán 5:90-91:** Prohíbe el vino y sustancias embriagantes
- **Hadith:** "Lo que embriaga en gran cantidad está prohibido incluso en pequeña cantidad"

### 4.2 Alternativa: Cerveza 0.0% ABV

Para acceder al mercado de Medio Oriente, se requiere:
1. Desarrollar línea de **cerveza sin alcohol (0.0% ABV)**
2. Línea de producción **completamente separada** (nunca usada para alcohol)
3. Insumos **100% certificados Halal**
4. Auditoría y certificación por organismo reconocido

### 4.3 Inversión Requerida

| Concepto | Costo (USD) |
|----------|-------------|
| Desarrollo receta 0.0% | $5,000-10,000 |
| Equipos dedicados Halal | $15,000-30,000 |
| Certificación inicial | $1,500-3,000 |
| Auditoría anual | $800-1,500/año |
| Lab testing por batch | $100-200 |
| Mods de sistema | $3,000-5,000 |
| **TOTAL INICIAL** | **$25,400-49,700** |

### 4.4 Mercados Objetivo (si certificado)

- 🇦🇪 Emiratos Árabes Unidos (Dubai) - Punto de entrada
- 🇸🇦 Arabia Saudita
- 🇶🇦 Qatar, Omán, Kuwait, Bahrein
- 🇲🇾 Malasia, Indonesia
- 🇹🇷 Turquía, Egipto, Marruecos

**Población musulmana global:** 1.8 mil millones

### 4.5 Recomendación Estratégica

🟢 **NO PROCEDER a corto plazo**

**Razones:**
1. Requiere cambio completo de producto (0.0% ABV)
2. Inversión significativa ($25K-50K USD)
3. Mercado no validado para cerveza sin alcohol artesanal
4. Requiere línea de producción separada

**Considerar solo si:**
- Cerveza 0.0% muestra demanda fuerte en Chile
- Decisión estratégica de diversificar línea de producto
- Distribuidor identificado en Medio Oriente
- Presupuesto asignado para inversión

---

### 4.6 Tareas Halal (Si se decide proceder)

#### Fase 1: Validación de Mercado (3 meses)

**Tarea 7.1: Investigación de Mercado**
- **Tiempo:** 40-60 horas
- **Inversión:** $2,000-5,000
- **Objetivo:** Validar demanda para 0.0% ABV

**Actividades:**
1. Encuesta mercado chileno
2. Investigación mercado Medio Oriente
3. Análisis competencia 0.0%
4. Identificar distribuidores UAE/Dubai
5. Proyecciones ROI

---

#### Fase 2: Desarrollo de Producto (6 meses)

**Tarea 7.2: Desarrollar Receta 0.0% ABV**
- **Tiempo:** 3-6 meses (R&D)
- **Inversión:** $5,000-10,000
- **Objetivo:** Receta de calidad sin alcohol

**Métodos de Remoción de Alcohol:**
1. Destilación al vacío
2. Ósmosis inversa
3. Fermentación controlada (levaduras especiales)

**Requisitos:**
- 0.0% ABV (verificado en laboratorio)
- Perfil de sabor aceptable
- Vida útil 6+ meses
- Proceso escalable

---

**Tarea 7.3: Adquirir Equipos Dedicados**
- **Tiempo:** 2-3 meses
- **Inversión:** $15,000-30,000
- **Objetivo:** Línea producción Halal exclusiva

**Equipos Necesarios:**
- Fermentadores dedicados (2-3 unidades)
- Línea de envasado separada
- Tanques de almacenamiento
- Equipo de limpieza/sanitización

**Requisito Crítico:** 100% separado de producción alcohólica

---

#### Fase 3: Ingredientes y Certificación (3 meses)

**Tarea 7.4: Obtener Insumos Certificados Halal**
- **Tiempo:** 60-80 horas
- **Inversión:** Premium en costo ingredientes

**Insumos a Certificar:**
- Maltas (cebada, trigo, centeno)
- Lúpulos
- Levadura (no productora de alcohol)
- Especias y adjuntos
- Clarificantes (NO gelatina de cerdo)
- Colores y sabores naturales

---

#### Fase 4: Modificaciones del Sistema (2 meses)

**Tarea 7.5: Modificaciones de Base de Datos**
- **Tiempo:** 2-3 horas
- **Riesgo:** BAJO

```sql
-- Tabla batches
ALTER TABLE batches
ADD COLUMN es_halal BOOLEAN DEFAULT FALSE,
ADD COLUMN certificado_halal VARCHAR(100),
ADD COLUMN fecha_certificacion_halal DATE,
ADD COLUMN contenido_alcoholico DECIMAL(5,3) DEFAULT 0.000;

-- Tabla activos
ALTER TABLE activos
ADD COLUMN uso_exclusivo_halal BOOLEAN DEFAULT FALSE,
ADD COLUMN fecha_ultima_limpieza_halal DATETIME,
ADD COLUMN certificado_limpieza_halal VARCHAR(100);

-- Tabla insumos
ALTER TABLE insumos
ADD COLUMN es_halal_certificado BOOLEAN DEFAULT FALSE,
ADD COLUMN organismo_certificador_halal VARCHAR(100),
ADD COLUMN numero_certificado_halal VARCHAR(100),
ADD COLUMN fecha_vencimiento_certificado_halal DATE;
```

---

**Tarea 7.6: Implementar Validaciones Halal**
- **Tiempo:** 4-6 horas
- **Componentes:** `Batch.php`, `Insumo.php`, `Activo.php`

```php
// Batch.php
public function validarHalal() {
    if(!$this->es_halal) return true;

    // Validar ABV = 0.0%
    if($this->contenido_alcoholico > 0.000) {
        throw new Exception("Batch Halal debe tener 0.0% ABV");
    }

    // Validar insumos certificados
    $insumos = BatchInsumo::getAll("WHERE id_batches='".$this->id."'");
    foreach($insumos as $bi) {
        $insumo = new Insumo($bi->id_insumos);
        if(!$insumo->es_halal_certificado) {
            throw new Exception("Insumo ".$insumo->nombre." no está certificado Halal");
        }

        // Validar certificación vigente
        if(strtotime($insumo->fecha_vencimiento_certificado_halal) < time()) {
            throw new Exception("Certificación Halal de ".$insumo->nombre." está vencida");
        }
    }

    // Validar fermentador exclusivo Halal
    $batch_activo = BatchActivo::getAll("WHERE id_batches='".$this->id."' LIMIT 1")[0];
    $activo = new Activo($batch_activo->id_activos);

    if(!$activo->uso_exclusivo_halal) {
        throw new Exception("Fermentador debe ser de uso exclusivo Halal");
    }

    return true;
}
```

---

**Tarea 7.7: Vista Reporte Halal**
- **Tiempo:** 6-8 horas
- **Componente:** `templates/reporte-halal.php`

**Secciones:**
1. Información del Batch
   - Número, Receta, Fecha
   - ABV: 0.0%
   - Certificado Halal #

2. Insumos Utilizados
   - Lista completa con cantidades
   - Certificado Halal por insumo
   - Organismo certificador
   - Fecha vencimiento certificado

3. Equipos Utilizados
   - Fermentadores
   - Designación Halal exclusivo
   - Fecha última limpieza Halal
   - Certificado de limpieza

4. Export
   - Print
   - PDF para auditores

---

#### Fase 5: Certificación (4 meses)

**Tarea 7.8: Documentar Procedimientos**
- **Tiempo:** 60-80 horas
- **Inversión:** $2,000-3,000

**Documentos:**
1. Manual de Producción Halal
2. Procedimientos de Abastecimiento
3. Protocolos de Limpieza
4. Procedimientos de Separación
5. Checklists de Control de Calidad
6. Procedimientos de Trazabilidad
7. Planes de Respuesta a Emergencias
8. Procedimientos de No Conformidad

---

**Tarea 7.9: Aplicar a Certificación**
- **Tiempo:** 40-60 horas (coordinación)
- **Inversión:** $1,500-3,000
- **Duración:** 2-4 meses

**Organismos Certificadores Recomendados:**
1. **IFANCA** (Islamic Food and Nutrition Council of America) - Primario
2. **HFCE** (Halal Food Council of Europe)
3. **ESMA** (Emirates Authority) - para mercado UAE

**Proceso:**
1. Aplicación con documentación
2. Pre-auditoría documental
3. Auditoría in-situ (2-3 días)
4. Análisis de laboratorio de muestras
5. Reporte de auditoría y recomendaciones
6. Emisión de certificado (si aprobado)

---

**Tarea 7.10: Auditorías Anuales**
- **Tiempo:** 20-30 horas/año
- **Inversión:** $800-1,500/año

**Mantenimiento:**
- Re-certificación anual
- Auditorías semestrales o anuales (pueden ser sin aviso)
- Testing de laboratorio por batch ($100-200/batch)
- Mantener documentación actualizada

---

### 4.7 Elementos Prohibidos en Halal

❌ **Prohibido:**
- Cualquier contenido alcohólico (>0.0%)
- Ingredientes derivados de cerdo (gelatina, etc.)
- Equipos compartidos con producción alcohólica
- Insumos sin certificación Halal

✅ **Permitido:**
- Cerveza 0.0% ABV
- Ingredientes naturales certificados
- Equipos dedicados exclusivamente
- Proceso completamente rastreable

---

## 5. Roadmap de Implementación

### 5.1 Roadmap Visual

```
2026
════════════════════════════════════════════════════════════

Q1 (Ene-Mar)
┌──────────────────────────────────────┐
│ 🔴 CRÍTICO                           │
│ ├─ TRAZ-001: Cliente en Despacho    │ ✓ Semana 1-2
│ └─ TRAZ-002: BatchActivo Llenado    │ ✓ Semana 2-3
└──────────────────────────────────────┘

Q2 (Abr-Jun)
┌──────────────────────────────────────┐
│ 🟠 ALTO                              │
│ ├─ TRAZ-004: Vista Trazabilidad     │ ✓ Semana 14-16
│ └─ TRAZ-005: Códigos QR             │ ✓ Semana 17-19
├──────────────────────────────────────┤
│ 🟡 MEDIO                             │
│ └─ TRAZ-003: Consumo Parcial        │ ✓ Semana 20-21
└──────────────────────────────────────┘

Q3 (Jul-Sep) - Si aprobada expansión latas
┌──────────────────────────────────────┐
│ 🔵 EXPANSIÓN LATAS                   │
│ ├─ LATAS-001: Tabla BD              │ ✓ Semana 27
│ ├─ LATAS-002: Clase LoteEnvasado    │ ✓ Semana 27-28
│ ├─ LATAS-003: Vista Nuevo Lote      │ ✓ Semana 29-30
│ ├─ LATAS-004: Dashboard Inventario  │ ✓ Semana 31-33
│ ├─ LATAS-005: Detalle Lote          │ ✓ Semana 34-35
│ └─ LATAS-006: Integración Despacho  │ ✓ Semana 36-37
└──────────────────────────────────────┘

Q4 (Oct-Dic)
┌──────────────────────────────────────┐
│ 🔵 EXPANSIÓN LATAS (cont.)           │
│ ├─ LATAS-007: Reportes              │ ✓ Semana 40-42
│ └─ LATAS-008: Testing Completo      │ ✓ Semana 43-45
└──────────────────────────────────────┘

2027+ - Solo si validado mercado 0.0%
┌──────────────────────────────────────┐
│ 🟢 CERTIFICACIÓN HALAL (OPCIONAL)    │
│ Q1: Investigación Mercado            │
│ Q2-Q3: Desarrollo Producto 0.0%      │
│ Q3-Q4: Adquisición Equipos           │
│ 2028: Certificación                  │
└──────────────────────────────────────┘
```

---

### 5.2 Sprints Detallados (Q1-Q2 2026)

#### Sprint 1 (Semana 1-2): Despacho con Cliente

**Objetivo:** Resolver problema crítico P1

| Día | Tarea | Responsable | Horas |
|-----|-------|-------------|-------|
| L1 | Reunión planning + análisis | Tech Lead | 2 |
| L1 | Modificar BD (ALTER TABLE) | Dev Backend | 1 |
| M2 | Modificar clase Despacho | Dev Backend | 1 |
| M2-X3 | Vista central-despacho (selector) | Dev Frontend | 4 |
| X3-J4 | Vista central-despacho (listado) | Dev Frontend | 3 |
| J4 | AJAX guardarDespacho | Dev Backend | 2 |
| V5 | Testing completo | QA | 3 |
| **Total** | | | **16 hrs** |

**Entregables:**
- [ ] BD actualizada
- [ ] Clase Despacho con id_clientes
- [ ] Formulario con selector cliente
- [ ] Listado muestra cliente
- [ ] Tests pasando

---

#### Sprint 2 (Semana 2-3): BatchActivo en Llenado

**Objetivo:** Resolver problema crítico P2

| Día | Tarea | Responsable | Horas |
|-----|-------|-------------|-------|
| L1 | Análisis lógica actual | Tech Lead | 2 |
| M2-X3 | Modificar ajax_llenarBarriles | Dev Backend | 8 |
| X3 | Actualizar vista inventario | Dev Frontend | 2 |
| J4 | Validaciones completas | Dev Backend | 4 |
| V5-L8 | Testing exhaustivo | QA | 8 |
| **Total** | | | **24 hrs** |

**Entregables:**
- [ ] AJAX descuenta BatchActivo
- [ ] Validación disponibilidad
- [ ] Fermentador se libera al vaciarse
- [ ] Vista actualizada en tiempo real
- [ ] Tests edge cases pasando

---

#### Sprint 3 (Semana 14-16): Vista Trazabilidad

**Objetivo:** Solución 5.4

| Día | Tarea | Responsable | Horas |
|-----|-------|-------------|-------|
| L1 | Diseño UX/UI wireframes | UX Designer | 4 |
| M2-J4 | Crear vista detalle-trazabilidad-barril | Dev Full Stack | 16 |
| V5-L8 | CSS Timeline responsive | Dev Frontend | 6 |
| M9 | Integración con detalle-barriles | Dev Backend | 2 |
| X10 | Router index.php | Dev Backend | 1 |
| J11-V12 | Funcionalidad imprimir/PDF | Dev Full Stack | 8 |
| L15-M16 | Testing completo | QA | 8 |
| **Total** | | | **45 hrs** |

**Entregables:**
- [ ] Vista trazabilidad completa
- [ ] Timeline visual funcionando
- [ ] Secciones colapsables
- [ ] Print/PDF operativo
- [ ] Responsive mobile

---

### 5.3 Hitos Clave (Milestones)

| Milestone | Fecha Objetivo | Entregables | Criterio Éxito |
|-----------|----------------|-------------|----------------|
| **M1: Trazabilidad Crítica** | Marzo 2026 | TRAZ-001, TRAZ-002 | Despachos con cliente, inventario preciso |
| **M2: Trazabilidad Mejorada** | Junio 2026 | TRAZ-003, TRAZ-004, TRAZ-005 | Vista consolidada + QR funcionales |
| **M3: Sistema Latas Core** | Septiembre 2026 | LATAS-001 a LATAS-006 | Envasar y despachar latas |
| **M4: Sistema Latas Completo** | Diciembre 2026 | LATAS-007, LATAS-008 | Reportes + testing completo |
| **M5: Halal (Opcional)** | 2027-2028 | HALAL-001 a HALAL-010 | Certificación obtenida |

---

## 6. Estimación de Recursos

### 6.1 Recursos Humanos

#### Equipo Requerido

**Para Mejoras de Trazabilidad (Q1-Q2)**

| Rol | Dedicación | Duración | Costo/hr | Total |
|-----|------------|----------|----------|-------|
| Tech Lead | 20% | 6 meses | $150 | $3,600 |
| Dev Backend | 50% | 3 meses | $100 | $6,000 |
| Dev Frontend | 40% | 3 meses | $100 | $4,800 |
| QA Tester | 30% | 3 meses | $80 | $2,880 |
| **SUBTOTAL** | | | | **$17,280** |

---

**Para Expansión Latas (Q3-Q4)**

| Rol | Dedicación | Duración | Costo/hr | Total |
|-----|------------|----------|----------|-------|
| Tech Lead | 20% | 6 meses | $150 | $3,600 |
| Dev Backend | 60% | 4 meses | $100 | $9,600 |
| Dev Frontend | 50% | 4 meses | $100 | $8,000 |
| QA Tester | 40% | 2 meses | $80 | $2,560 |
| **SUBTOTAL** | | | | **$23,760** |

---

**Para Certificación Halal (2027-2028)**

| Rol | Dedicación | Duración | Costo |
|-----|------------|----------|-------|
| Maestro Cervecero | 80% | 6 meses | $36,000 |
| Dev Backend | 20% | 2 meses | $3,200 |
| Documentador Técnico | 100% | 3 meses | $18,000 |
| Consultor Halal | External | - | $5,000 |
| **SUBTOTAL** | | | **$62,200** |

---

### 6.2 Recursos de Infraestructura

#### Trazabilidad (Mínimo)
- Servidor actual (suficiente)
- Storage adicional para QR: ~1GB
- Librería Composer: endroid/qr-code (gratis)

**Costo:** $0 (infraestructura existente)

---

#### Latas (Moderado)
- Storage adicional para reportes: ~5GB
- Sin cambios de hardware requeridos

**Costo:** $0

---

#### Halal (Significativo)
- **Fermentadores dedicados:** $15,000-30,000
- **Línea envasado separada:** Incluido arriba
- **Equipos de laboratorio:** $3,000-5,000
- **Signage y separadores:** $500-1,000

**Costo Total:** $18,500-36,000

---

### 6.3 Resumen Financiero

| Categoría | Desarrollo | Infraestructura | Certificación | **TOTAL** |
|-----------|------------|-----------------|---------------|-----------|
| **Trazabilidad** | $17,280 | $0 | - | **$17,280** |
| **Latas** | $23,760 | $0 | - | **$23,760** |
| **Halal** | $62,200 | $36,000 | $1,500-3,000 | **$99,700-101,200** |
| **TOTAL GENERAL** | **$103,240** | **$36,000** | **$1,500-3,000** | **$140,740-142,240** |

---

## 7. Análisis de Riesgos

### 7.1 Matriz de Riesgos

| ID | Riesgo | Probabilidad | Impacto | Severidad | Mitigación |
|----|--------|--------------|---------|-----------|------------|
| R1 | Cambio en BatchActivo rompe inventario | Media | Alto | 🟠 ALTO | Testing exhaustivo, rollback plan |
| R2 | QR codes no escanean en todos dispositivos | Baja | Medio | 🟡 MEDIO | Testing multi-dispositivo |
| R3 | Sistema latas causa confusión usuarios | Media | Medio | 🟡 MEDIO | Capacitación, UX claro |
| R4 | FIFO no se cumple en despachos latas | Media | Alto | 🟠 ALTO | Validación automática, alertas |
| R5 | Certificación Halal rechazada | Alta | Muy Alto | 🔴 CRÍTICO | Pre-auditoría, consultor experto |
| R6 | Mercado 0.0% no tiene demanda | Alta | Muy Alto | 🔴 CRÍTICO | Investigación mercado ANTES de invertir |
| R7 | Equipos Halal contaminados accidentalmente | Media | Muy Alto | 🔴 CRÍTICO | Separación física, procedimientos estrictos |
| R8 | Performance degradado con muchos lotes | Baja | Medio | 🟡 MEDIO | Índices BD, paginación, caching |

---

### 7.2 Plan de Contingencia

#### Riesgo R1: Problema con BatchActivo

**Si ocurre:**
1. Rollback inmediato a versión anterior
2. Restaurar backup de BD
3. Analizar logs de error
4. Fix y re-deploy

**Prevención:**
- Backup completo antes de deploy
- Feature flag para activar/desactivar
- Testing en ambiente staging primero
- Deploy gradual (10% usuarios → 100%)

---

#### Riesgo R4: FIFO no cumplido

**Si ocurre:**
1. Reporte de lotes despachados fuera de orden
2. Identificar lotes en riesgo de caducidad
3. Promoción urgente de lotes antiguos

**Prevención:**
- Ordenamiento automático por fecha caducidad
- Bloqueo de lotes nuevos si hay antiguos
- Dashboard de alertas visible
- Capacitación equipo despacho

---

#### Riesgo R5-R7: Problemas Halal

**Si certificación rechazada:**
1. Revisar hallazgos de auditoría
2. Implementar correcciones
3. Re-aplicar (costo adicional $1,500)

**Si contaminación:**
1. Detener producción Halal inmediatamente
2. Limpieza exhaustiva certificada
3. Análisis de laboratorio
4. Notificar organismo certificador
5. Puede perder certificación

**Prevención:**
- Separación física completa
- Señalización clara
- SOPs estrictos
- Auditorías internas mensuales
- Training constante

---

## 8. Indicadores de Éxito (KPIs)

### 8.1 KPIs de Trazabilidad

#### Eficiencia Operacional

| KPI | Baseline | Target | Medición |
|-----|----------|--------|----------|
| Tiempo promedio rastrear barril | 15 min | 2 min | Reducción 85% |
| Auditorías completadas/día | 2 | 8 | Aumento 300% |
| Reclamos resueltos <24hrs | 40% | 90% | Aumento 125% |
| Errores de inventario | 5%/mes | <1%/mes | Reducción 80% |

#### Adopción del Sistema

| KPI | Target | Fecha |
|-----|--------|-------|
| 100% despachos con cliente | 95%+ | Abril 2026 |
| QR generados para barriles activos | 100% | Julio 2026 |
| Uso vista trazabilidad consolidada | 20+ visitas/semana | Julio 2026 |

---

### 8.2 KPIs de Expansión Latas

#### Producción y Ventas

| KPI | Target Año 1 | Medición |
|-----|--------------|----------|
| Lotes envasados/mes | 10-15 | Promedio móvil |
| Latas producidas/mes | 8,000-12,000 | Total mensual |
| % ventas en latas vs barriles | 20-30% | Proporción ingresos |
| Clientes nuevos (retail) | 15-25 | Acumulado |

#### Eficiencia y Calidad

| KPI | Target | Medición |
|-----|--------|----------|
| % merma latas | <3% | Cantidad dañada / producida |
| FIFO compliance | >95% | Despachos en orden correcto |
| Lotes caducados | 0 | Eventos por trimestre |
| Rotación inventario (días) | <45 días | Promedio edad lote vendido |

---

### 8.3 KPIs de Certificación Halal

#### Certificación

| KPI | Target | Fecha |
|-----|--------|-------|
| Receta 0.0% desarrollada | 2 recetas | Q2 2027 |
| Equipos dedicados instalados | 100% | Q4 2027 |
| Certificación Halal obtenida | Sí | Q2 2028 |
| Auditorías pasadas sin hallazgos críticos | 100% | Anual |

#### Mercado

| KPI | Target Año 1 | Medición |
|-----|--------------|----------|
| Batches 0.0% producidos/mes | 2-4 | Post-certificación |
| Distribuidores Medio Oriente | 1-2 | Contratos firmados |
| Ventas exportación (USD) | $50K-100K | Año 1 post-cert |

---

## 9. Conclusiones y Recomendaciones

### 9.1 Priorización Recomendada

**🔴 IMPLEMENTAR INMEDIATAMENTE (Q1 2026)**
1. **TRAZ-001:** Cliente en Despacho
2. **TRAZ-002:** BatchActivo en Llenado

**Beneficio:** Resolver problemas críticos de trazabilidad e inventario

---

**🟠 IMPLEMENTAR EN Q2 2026**
3. **TRAZ-004:** Vista Consolidada de Trazabilidad
4. **TRAZ-005:** Códigos QR
5. **TRAZ-003:** Consumo Parcial (opcional)

**Beneficio:** Mejorar experiencia usuario y eficiencia operacional

---

**🟡 EVALUAR PARA Q3-Q4 2026**
6. **Expansión Latas (LATAS-001 a LATAS-008)**

**Condiciones para aprobar:**
- Validar demanda de mercado para latas
- Identificar canales de distribución retail
- Evaluar inversión en equipamiento envasado
- Confirmar presupuesto disponible

**Beneficio:** Nuevo canal de ventas, diversificación producto

---

**🟢 POSPONER (2027+)**
7. **Certificación Halal (HALAL-001 a HALAL-010)**

**Condiciones para reconsiderar:**
- Demanda validada para cerveza 0.0% en Chile
- Distribuidor identificado en Medio Oriente
- Presupuesto $100K+ USD disponible
- Decisión estratégica de diversificar a sin alcohol

**Advertencia:** NO proceder con alcoholic beer para Halal

---

### 9.2 Roadmap Recomendado Final

```
2026
════
Q1: Trazabilidad Crítica (TRAZ-001, TRAZ-002)
Q2: Trazabilidad Mejorada (TRAZ-003, TRAZ-004, TRAZ-005)
Q3: Decisión GO/NO-GO Latas
Q4: Si GO → Implementación Latas

2027+
═════
Solo si validado: Investigación Halal + 0.0% ABV
```

---

### 9.3 Impactos Esperados

**Mejoras de Trazabilidad:**
- ✅ Reducción 85% en tiempo de rastreo
- ✅ Inventario 99%+ preciso
- ✅ Auditorías 4x más rápidas
- ✅ Mejor servicio al cliente

**Expansión Latas:**
- ✅ Nuevo canal retail (supermercados, tiendas)
- ✅ 20-30% ingresos adicionales (proyección)
- ✅ Mayor volumen producción
- ✅ Diversificación riesgo

**Certificación Halal:**
- ⚠️ Requiere producto nuevo (0.0% ABV)
- ⚠️ Inversión $100K+
- ⚠️ Alto riesgo mercado
- ✅ Acceso mercado 1.8B musulmanes (si exitoso)

---

## Anexos

### A. Glosario de Términos

| Término | Definición |
|---------|------------|
| **BatchActivo** | Relación entre un Batch y un Activo (fermentador) con estado y litraje |
| **FIFO** | First In, First Out - método inventario donde lo más antiguo sale primero |
| **Halal** | Permitido según ley islámica. Para alimentos: ingredientes, proceso, trazabilidad |
| **Lote Envasado** | Grupo de latas/botellas producidas de un mismo batch en una sesión |
| **0.0% ABV** | Cero por ciento alcohol por volumen - requerido para Halal |
| **Trazabilidad** | Capacidad de rastrear un producto desde origen hasta destino final |

---

### B. Referencias

1. ANALISIS_TRAZABILIDAD_BARRIL.md - Análisis técnico completo
2. CLAUDE.md - Documentación del sistema Barril.cl
3. Islamic Food and Nutrition Council (IFANCA) - www.ifanca.org
4. Halal Food Council Europe (HFCE) - www.halalfoodcouncil.eu

---

### C. Contactos Clave

**Certificación Halal:**
- IFANCA: +1-847-703-9200 / info@ifanca.org
- HFCE: info@halalfoodcouncil.eu

**Consultoría Técnica:**
- Brewers Association (0.0% beer): www.brewersassociation.org

---

**Fin del Documento**

---

*Este plan de implementación es un documento vivo y debe actualizarse conforme se completen tareas y cambien prioridades.*

**Última actualización:** 27 de Noviembre, 2025
**Próxima revisión:** Marzo 2026 (post-implementación críticas)