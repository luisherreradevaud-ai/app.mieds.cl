# Plan de Implementación: PDF de Trazabilidad por Entrega

## Resumen Ejecutivo

Este documento detalla los cambios necesarios para implementar la generación de PDFs de trazabilidad para cada producto entregado (barril o caja de envases).

---

## 1. Análisis de Datos Existentes

### 1.1 Flujo de Datos Disponibles

#### Para Barriles:
```
EntregaProducto.id_barriles → Barril
    ├── Barril.id_batches → Batch (cocción, receta)
    ├── Barril.id_batches_activos → BatchActivo (fermentación)
    └── Batch → BatchInsumo → Insumo (ingredientes)
              → BatchTraspaso (traspasos fermentación/maduración)
              → Receta (nombre, código)
```

#### Para Cajas de Envases:
```
EntregaProducto.id_cajas_de_envases → CajaDeEnvases
    └── CajaDeEnvases → Envase → BatchDeEnvases
                            ├── id_batches → Batch (cocción)
                            ├── id_activos → Activo (origen fermentador)
                            ├── id_barriles → Barril (origen barril)
                            └── creada (fecha envasado)
```

### 1.2 Datos YA Disponibles en el Sistema

| Sección PDF | Entidad | Campo | Disponible |
|-------------|---------|-------|------------|
| **Identificación Entrega** | | | |
| Cliente | Entrega → Cliente | nombre, razon_social | ✅ |
| Fecha entrega | Entrega | creada | ✅ |
| Tipo producto | EntregaProducto | tipo | ✅ |
| Código barril/caja | Barril/CajaDeEnvases | codigo | ✅ |
| Folio DTE | Entrega | factura | ✅ |
| **Cocción/Batch** | | | |
| Nombre receta | Batch → Receta | nombre | ✅ |
| Código receta | Receta | codigo | ✅ |
| Fecha cocción | Batch | batch_date | ✅ |
| Granos utilizados | BatchInsumo → Insumo → TipoDeInsumo | nombre | ✅ (filtrar por tipo "Malta/Grano") |
| **Fermentación** | | | |
| Fecha inicio | Batch | fermentacion_date + fermentacion_hora_inicio | ✅ |
| Activo fermentador | BatchActivo → Activo | nombre, codigo | ✅ |
| **Maduración/Traspasos** | | | |
| Fechas traspasos | BatchTraspaso | date, hora | ✅ |
| Activos involucrados | BatchTraspaso → Activo | id_fermentadores_inicio/final | ✅ |
| **Empaque Barril** | | | |
| Fecha embarrilado | Barril.creada o BarrilEstado | creada | ⚠️ Parcial |
| Código barril | Barril | codigo | ✅ |
| Capacidad | Barril | litraje | ✅ |
| **Empaque Envases** | | | |
| Fecha envasado | BatchDeEnvases | creada | ✅ |
| Formato | FormatoDeEnvases | nombre, volumen_ml | ✅ |
| Cantidad | CajaDeEnvases | cantidad_envases | ✅ |

### 1.3 Datos FALTANTES (Requieren Modificación BD)

| Dato Requerido | Entidad | Campo Nuevo | Tipo | Descripción |
|----------------|---------|-------------|------|-------------|
| Línea productiva activo | `activos` | `linea_productiva` | ENUM('alcoholica','analcoholica','general') | Indica si el activo pertenece a línea con/sin alcohol |
| Fecha embarrilado explícita | `barriles` | `fecha_llenado` | DATETIME | Fecha exacta cuando se llenó el barril |

---

## 2. Modificaciones de Base de Datos

### 2.1 Tabla `activos` - Agregar campo línea productiva

```sql
-- Migración: Agregar campo linea_productiva a activos
ALTER TABLE activos
ADD COLUMN linea_productiva ENUM('alcoholica', 'analcoholica', 'general')
DEFAULT 'general' AFTER clase;

-- Índice para consultas
CREATE INDEX idx_activos_linea ON activos(linea_productiva);
```

### 2.2 Tabla `barriles` - Agregar campo fecha_llenado

```sql
-- Migración: Agregar campo fecha_llenado a barriles
ALTER TABLE barriles
ADD COLUMN fecha_llenado DATETIME DEFAULT NULL AFTER litros_cargados;

-- Índice para consultas
CREATE INDEX idx_barriles_fecha_llenado ON barriles(fecha_llenado);
```

### 2.3 Actualizar datos existentes (opcional)

```sql
-- Inferir fecha_llenado de barriles existentes desde barriles_estados
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
```

---

## 3. Archivos a Modificar

### 3.1 Clase Activo.php

**Archivo:** `php/classes/Activo.php`

**Cambios:**
1. Agregar propiedad `$linea_productiva`
2. Agregar método `getLineaProductivaLabel()`

```php
// Agregar propiedad
public $linea_productiva = "general";

// Agregar método
public function getLineaProductivaLabel() {
    $labels = [
        'alcoholica' => 'Línea Alcohólica',
        'analcoholica' => 'Línea Sin Alcohol',
        'general' => 'General'
    ];
    return isset($labels[$this->linea_productiva]) ? $labels[$this->linea_productiva] : 'General';
}

// Agregar a getClases() si se quiere mostrar en UI
```

### 3.2 Clase Barril.php

**Archivo:** `php/classes/Barril.php`

**Cambios:**
1. Agregar propiedad `$fecha_llenado`

```php
public $fecha_llenado = null;
```

### 3.3 ajax_llenarBarriles.php

**Archivo:** `ajax/ajax_llenarBarriles.php`

**Cambios:**
1. Registrar fecha_llenado al llenar barril

```php
$barril->fecha_llenado = date('Y-m-d H:i:s');
```

---

## 4. Archivos Nuevos a Crear

### 4.1 Clase TrazabilidadPDF.php

**Archivo:** `php/classes/TrazabilidadPDF.php`

**Descripción:** Clase para generar el PDF de trazabilidad

**Métodos principales:**
- `__construct($entrega_producto_id)`
- `generarParaBarril($barril)`
- `generarParaCajaEnvases($caja)`
- `obtenerDatosProduccion($batch_id)`
- `obtenerInsumos($batch_id, $tipo = 'grano')`
- `obtenerTraspasos($batch_id)`
- `calcularTiempos($fecha_coccion, $fecha_empaque, $fecha_entrega)`
- `render()` - Genera el PDF
- `output($tipo = 'D')` - Descarga o muestra

### 4.2 Endpoint AJAX

**Archivo:** `ajax/ajax_generarPDFTrazabilidad.php`

**Parámetros:**
- `id_entregas_productos` (required)
- `tipo` ('barril' | 'caja')

**Respuesta:** Descarga directa del PDF

### 4.3 Template de configuración de activos (modificar existente)

**Archivo a modificar:** `templates/detalle-activos.php` o `templates/nuevo-activos.php`

**Cambios:**
- Agregar campo select para `linea_productiva`

---

## 5. Modificación de Template de Entregas

### 5.1 detalle-entregas.php

**Archivo:** `templates/detalle-entregas.php`

**Cambios en la tabla de productos (línea ~163-186):**

```php
<?php
foreach($entregas_productos as $ep) {
?>
<tr>
    <td><?= $ep->tipo; ?></td>
    <td><?= $ep->cantidad; ?></td>
    <td><?= $ep->tipos_cerveza; ?></td>
    <td><b><?= $ep->codigo; ?></b></td>
    <td><b>$<?= number_format($ep->monto); ?></b></td>
    <td>
        <!-- NUEVO: Botón PDF Trazabilidad -->
        <a href="./ajax/ajax_generarPDFTrazabilidad.php?id=<?= $ep->id; ?>"
           class="btn btn-sm btn-outline-primary"
           target="_blank"
           title="Descargar PDF de Trazabilidad">
            <i class="fas fa-file-pdf"></i> Trazabilidad
        </a>
    </td>
</tr>
<?php
}
?>
```

---

## 6. Librería PDF

### 6.1 Opción Recomendada: TCPDF

TCPDF ya está referenciado en el composer.json de LibreDTE pero no está instalado localmente.

**Instalación:**
```bash
cd /Users/luisherreradevaud/Documents/Github/app.barril.cl
composer require tecnickcom/tcpdf
```

**Alternativa sin Composer:**
Descargar TCPDF manualmente y colocar en `vendor_php/tcpdf/`

### 6.2 Alternativa: DOMPDF (HTML a PDF)

Más sencillo de usar, convierte HTML a PDF.

```bash
composer require dompdf/dompdf
```

### 6.3 Alternativa: FPDF (Más ligero)

Sin dependencias, pero menos características.

```bash
# Descargar de http://www.fpdf.org/
# Colocar en vendor_php/fpdf/
```

---

## 7. Estructura del PDF

### 7.1 Diseño Propuesto

```
┌─────────────────────────────────────────────────────────────┐
│ [LOGO]          CERTIFICADO DE TRAZABILIDAD DE PRODUCTO    │
│                 Cerveza Cocholgue                           │
├─────────────────────────────────────────────────────────────┤
│ DATOS DE LA ENTREGA                                         │
│ ─────────────────────                                       │
│ Cliente: [Nombre / Razón Social]                            │
│ Fecha de Entrega: [DD/MM/YYYY HH:MM]                        │
│ Documento: Factura #[Folio] / Sin documento                 │
│ Receptor: [Nombre receptor]                                 │
├─────────────────────────────────────────────────────────────┤
│ DATOS DEL PRODUCTO                                          │
│ ─────────────────                                           │
│ Tipo: [Barril 30L / Caja 24 Latas 473ml]                   │
│ Código: [B-001 / CAJA-241129-ABC1]                         │
│ Receta: [IPA - Código: REC-001]                            │
├─────────────────────────────────────────────────────────────┤
│ LÍNEA DE TIEMPO DEL PROCESO                                 │
│ ─────────────────────────────                               │
│                                                             │
│ ● COCCIÓN                                                   │
│   Fecha: [DD/MM/YYYY]                                       │
│   Granos: Malta Pilsen, Malta Caramelo, ...                │
│   Línea: [Alcohólica / Sin Alcohol / General]              │
│                    ↓                                        │
│ ● FERMENTACIÓN                                              │
│   Inicio: [DD/MM/YYYY HH:MM]                               │
│   Fermentador: [FER-001 (60L)]                             │
│   Línea: [Alcohólica]                                      │
│                    ↓                                        │
│ ● MADURACIÓN / TRASPASOS                                    │
│   [DD/MM/YYYY] FER-001 → MAD-002                           │
│   [DD/MM/YYYY] MAD-002 → MAD-003                           │
│   Línea: [Alcohólica]                                      │
│                    ↓                                        │
│ ● EMPAQUE                                                   │
│   Fecha: [DD/MM/YYYY HH:MM]                                │
│   Tipo: [Barril B-001 (30L) / Caja 24 Latas]              │
│   Línea: [Alcohólica]                                      │
│                    ↓                                        │
│ ● ENTREGA                                                   │
│   Fecha: [DD/MM/YYYY HH:MM]                                │
│   Cliente: [Nombre]                                         │
├─────────────────────────────────────────────────────────────┤
│ RESUMEN DE TIEMPOS                                          │
│ ─────────────────                                           │
│ Cocción → Empaque:    [X] días, [Y] horas                  │
│ Empaque → Entrega:    [X] días, [Y] horas                  │
│ ────────────────────────────────────────                   │
│ TOTAL:                [X] días, [Y] horas                  │
├─────────────────────────────────────────────────────────────┤
│ OBSERVACIONES                                               │
│ [Campo opcional con notas del batch o entrega]             │
└─────────────────────────────────────────────────────────────┘
│ Documento generado el [fecha] - Sistema Barril.cl          │
└─────────────────────────────────────────────────────────────┘
```

---

## 8. Tareas de Implementación

### Fase 1: Preparación de Base de Datos
- [ ] 1.1 Crear migración SQL para `activos.linea_productiva`
- [ ] 1.2 Crear migración SQL para `barriles.fecha_llenado`
- [ ] 1.3 Ejecutar migraciones
- [ ] 1.4 Actualizar datos existentes (opcional)

### Fase 2: Modificación de Clases PHP
- [ ] 2.1 Actualizar `Activo.php` con nuevo campo y método
- [ ] 2.2 Actualizar `Barril.php` con nuevo campo
- [ ] 2.3 Modificar `ajax_llenarBarriles.php` para registrar fecha_llenado

### Fase 3: Instalación de Librería PDF
- [ ] 3.1 Decidir librería (TCPDF, DOMPDF o FPDF)
- [ ] 3.2 Instalar/descargar librería
- [ ] 3.3 Verificar funcionamiento básico

### Fase 4: Desarrollo de Generador PDF
- [ ] 4.1 Crear clase `TrazabilidadPDF.php`
- [ ] 4.2 Implementar método para barriles
- [ ] 4.3 Implementar método para cajas de envases
- [ ] 4.4 Implementar cálculo de tiempos
- [ ] 4.5 Diseñar layout del PDF

### Fase 5: Integración
- [ ] 5.1 Crear endpoint `ajax_generarPDFTrazabilidad.php`
- [ ] 5.2 Modificar `detalle-entregas.php` con botón de descarga
- [ ] 5.3 Modificar formulario de activos para editar línea productiva

### Fase 6: Testing
- [ ] 6.1 Probar generación PDF para barril
- [ ] 6.2 Probar generación PDF para caja de envases
- [ ] 6.3 Verificar cálculos de tiempo correctos
- [ ] 6.4 Verificar trazabilidad completa

---

## 9. Estimación de Esfuerzo

| Fase | Descripción | Complejidad |
|------|-------------|-------------|
| 1 | Base de Datos | Baja |
| 2 | Clases PHP | Baja |
| 3 | Librería PDF | Baja-Media |
| 4 | Generador PDF | Alta |
| 5 | Integración | Media |
| 6 | Testing | Media |

---

## 10. Dependencias

### Requeridas antes de empezar:
1. Acceso a ejecutar migraciones SQL
2. Decisión sobre librería PDF a usar
3. Logo de la cervecería en formato PNG

### Datos de prueba necesarios:
1. Al menos un barril con trazabilidad completa (batch → fermentación → entrega)
2. Al menos una caja de envases con trazabilidad completa
3. Entrega con factura generada

---

## 11. Consideraciones Adicionales

### 11.1 Performance
- Cachear datos de recetas/insumos que no cambian frecuentemente
- Considerar generación asíncrona para PDFs grandes

### 11.2 Seguridad
- Validar que el usuario tenga permiso para ver la entrega
- No exponer información sensible en el PDF

### 11.3 Auditoría
- Registrar cada generación de PDF (quién, cuándo, qué producto)

### 11.4 Internacionalización
- Fechas en formato chileno (DD/MM/YYYY)
- Montos en CLP si aplica

---

*Documento creado: 2025-12-01*
*Sistema: Barril.cl ERP*
