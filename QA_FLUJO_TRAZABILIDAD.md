# QA: Análisis Completo del Flujo de Trazabilidad

## Índice
1. [Flujo Completo: Batch → Entrega](#flujo-completo-batch--entrega)
2. [Análisis de inventario-de-productos.php](#análisis-de-inventario-de-productosphp)
3. [Puntos Problemáticos del Código](#puntos-problemáticos-del-código)
4. [Casos Edge (Casos Límite)](#casos-edge)
5. [Tests Manuales por Proceso](#tests-manuales-por-proceso)
6. [Checklist de Regresión](#checklist-de-regresión)

---

## Flujo Completo: Batch → Entrega

### Flujo de Barriles (Cerveza en Barriles)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           FLUJO DE BARRILES                                      │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  1. PRODUCCIÓN (nuevo-batches.php)                                               │
│     └── Batch (Fermentación → Maduración)                                        │
│         ├── id_recetas                                                           │
│         ├── batch_nombre                                                         │
│         └── etapa_seleccionada                                                   │
│                    │                                                             │
│                    ▼                                                             │
│  2. FERMENTADOR (inventario-de-productos.php)                                    │
│     └── BatchActivo                                                              │
│         ├── id_batches                                                           │
│         ├── id_activos (fermentador)                                             │
│         ├── litraje                                                              │
│         └── estado: 'Fermentación' → 'Maduración'                                │
│                    │                                                             │
│                    ▼                                                             │
│  3. LLENAR BARRIL (ajax_llenarBarriles.php)                                      │
│     └── Barril                                                                   │
│         ├── litros_cargados += cantidad                                          │
│         ├── id_batches = batch_activo.id_batches                                 │
│         ├── id_activos = batch_activo.id_activos                                 │
│         ├── id_batches_activos = batch_activo.id                                 │
│         └── estado: 'En planta'                                                  │
│                    │                                                             │
│                    ▼                                                             │
│  4. DESPACHO (nuevo-despachos.php → ajax_guardarDespacho.php)                    │
│     ├── Despacho                                                                 │
│     │   └── id_usuarios_repartidor                                               │
│     └── DespachoProducto                                                         │
│         ├── tipo: 'Barril'                                                       │
│         ├── id_barriles                                                          │
│         └── id_productos                                                         │
│     └── Barril.estado = 'En despacho'                                            │
│                    │                                                             │
│                    ▼                                                             │
│  5. ENTREGA (repartidor.php → ajax_guardarEntrega.php)                           │
│     ├── Entrega                                                                  │
│     │   ├── id_clientes                                                          │
│     │   ├── monto (calculado)                                                    │
│     │   └── factura (si aplica)                                                  │
│     ├── EntregaProducto                                                          │
│     │   └── (copia de DespachoProducto)                                          │
│     ├── DespachoProducto.delete()                                                │
│     ├── Barril.estado = 'En terreno'                                             │
│     ├── Barril.id_clientes = entrega.id_clientes                                 │
│     └── DTE (si cliente.emite_factura)                                           │
│                    │                                                             │
│                    ▼                                                             │
│  6. DEVOLUCIÓN (repartidor.php - selección estado)                               │
│     └── Barril                                                                   │
│         ├── estado: 'Devuelto a planta' → 'En planta'                            │
│         ├── id_clientes = 0                                                      │
│         ├── id_batches = 0                                                       │
│         └── litros_cargados = 0                                                  │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Flujo de Envases (Latas y Botellas)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           FLUJO DE ENVASES                                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  1. PRODUCCIÓN (nuevo-batches.php)                                               │
│     └── Batch → BatchActivo (mismo que barriles)                                 │
│                    │                                                             │
│              ┌─────┴─────┐                                                       │
│              ▼           ▼                                                       │
│  2A. DESDE FERMENTADOR   2B. DESDE BARRIL                                        │
│      BatchActivo              Barril lleno                                       │
│                    │                                                             │
│                    ▼                                                             │
│  3. ENVASAR (inventario-de-productos.php → ajax_envasar.php)                     │
│     └── BatchDeEnvases (1 por línea de envasado)                                 │
│         ├── tipo: 'Lata' | 'Botella'                                             │
│         ├── id_batches                                                           │
│         ├── id_activos | id_barriles (origen)                                    │
│         ├── id_formatos_de_envases                                               │
│         ├── cantidad_de_envases                                                  │
│         └── volumen_origen_ml                                                    │
│     └── Envase (N registros individuales)                                        │
│         ├── id_batches_de_envases                                                │
│         ├── id_cajas_de_envases = 0                                              │
│         └── estado: 'Envasado'                                                   │
│     └── Origen vaciado (litraje = 0)                                             │
│                    │                                                             │
│                    ▼                                                             │
│  4. CREAR CAJA (inventario-de-productos.php → ajax_crearCajaDeEnvases.php)       │
│     └── CajaDeEnvases                                                            │
│         ├── codigo: 'CAJA-YYMMDD-XXXX'                                           │
│         ├── id_productos                                                         │
│         ├── cantidad_envases                                                     │
│         └── estado: 'En planta'                                                  │
│     └── Envase (actualizar N envases)                                            │
│         ├── id_cajas_de_envases = caja.id                                        │
│         └── estado: 'En caja'                                                    │
│                    │                                                             │
│                    ▼                                                             │
│  5. DESPACHO (nuevo-despachos.php → ajax_guardarDespacho.php)                    │
│     └── DespachoProducto                                                         │
│         ├── tipo: 'CajaEnvases'                                                  │
│         ├── id_cajas_de_envases                                                  │
│         └── id_productos (⚠️ FIX: obtener de caja)                               │
│     └── CajaDeEnvases.estado = 'En despacho'                                     │
│                    │                                                             │
│                    ▼                                                             │
│  6. ENTREGA (repartidor.php → ajax_guardarEntrega.php)                           │
│     └── EntregaProducto (copia de DespachoProducto)                              │
│     └── CajaDeEnvases.estado = 'Entregada'                                       │
│     └── DTE (descripción: "Lata xN [Producto]")                                  │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Puntos Críticos de Trazabilidad

| Punto | Origen | Destino | Campos Críticos | Validaciones |
|-------|--------|---------|-----------------|--------------|
| Batch → BatchActivo | Batch | BatchActivo | id_batches, id_activos | Activo disponible |
| BatchActivo → Barril | BatchActivo | Barril | id_batches, id_activos, litros | ⚠️ Sin validar capacidad |
| BatchActivo → BatchDeEnvases | BatchActivo | BatchDeEnvases | id_batches, id_activos, id_recetas | ✓ Volumen disponible |
| Barril → BatchDeEnvases | Barril | BatchDeEnvases | id_batches, id_barriles | ✓ Volumen disponible |
| Envase → CajaDeEnvases | Envase | CajaDeEnvases | id_cajas_de_envases | ✓ Formato coincide |
| Barril → DespachoProducto | Barril | DespachoProducto | id_barriles, id_productos | ✓ Estado correcto |
| Caja → DespachoProducto | CajaDeEnvases | DespachoProducto | id_cajas_de_envases, id_productos | ⚠️ id_productos era 0 |
| DespachoProducto → EntregaProducto | DespachoProducto | EntregaProducto | Todos los campos | ✓ Copia completa |

---

## Análisis de inventario-de-productos.php

### Estructura del Archivo

El archivo `inventario-de-productos.php` es el núcleo central de la gestión de inventario. Tiene **2172 líneas** divididas en:

| Sección | Líneas | Funcionalidad |
|---------|--------|---------------|
| PHP Backend | 1-64 | Carga de datos para las cards |
| HTML Cards | 65-665 | 6 cards de visualización |
| Modales | 666-1167 | 8 modales de acción |
| JavaScript | 1168-2172 | Lógica de interacción |

### Cards del Inventario

| Card | Variables PHP | Acciones | Archivos AJAX |
|------|---------------|----------|---------------|
| **Fermentación** | `$ba_fermentacion` | Agregar/Quitar fermentador | `ajax_inventarioProductosBatchActivoAgregar.php`, `ajax_inventarioProductosBatchActivoEliminar.php` |
| **Maduración** | `$ba_maduracion` | Traspasar, Eliminar | `ajax_agregarTraspasosInventarioProductos.php` |
| **Barriles Llenos** | `$barriles_en_planta` | Llenar barril | `ajax_llenarBarriles.php` |
| **Despachos** | `$barriles_en_terreno` | Link a nuevo despacho | - |
| **Envases en Planta** | `$batches_de_latas`, `$batches_de_botellas` | Envasar, Crear cajas, Revertir | `ajax_envasar.php`, `ajax_revertirEnvasado.php` |
| **Cajas en Planta** | `$cajas_de_latas_en_planta`, `$cajas_de_botellas_en_planta` | Eliminar caja | `ajax_eliminarCajaDeEnvases.php` |

### Inconsistencias Detectadas en inventario-de-productos.php

#### INC-1: Bug en Card Despachos (Línea 330)

**Problema**: En la card "Despachos", se usa `$batch->id` de la iteración anterior en lugar de `$ba->id_batches`:

```php
// Línea 330 - INCORRECTO
$batch = new Batch($batch->id);  // ❌ Usa el batch de la iteración anterior

// CORRECTO debería ser:
$batch = new Batch($ba->id_batches);
```

**Impacto**: Muestra información incorrecta del batch para los barriles en despacho.

#### INC-2: Variables JavaScript con const que podrían cambiar

**Problema**: Líneas 1172-1178 declaran como `const` arrays que se modifican dinámicamente:

```javascript
const activos_traspaso = <?= json_encode($activos_traspaso,JSON_PRETTY_PRINT); ?>;
const batches = <?= json_encode($batches, JSON_PRETTY_PRINT); ?>;
```

**Impacto**: Bajo - funciona porque son objetos/arrays, pero semánticamente incorrecto.

#### INC-3: Selector de fermentadores sin validación de disponibilidad

**Problema**: Línea 855 carga TODOS los activos en el select, no solo los disponibles:

```php
foreach($activos as $activo) {
    // Incluye activos ocupados también
?>
<option value="<?= $activo->id; ?>"><?= $activo->nombre; ?></option>
```

**Impacto**: Usuario podría intentar usar un fermentador ya ocupado.

#### INC-4: Falta de validación de volumen negativo

**Problema**: En líneas 1280-1285, la validación de litraje solo verifica igualdad, no negativos:

```javascript
if(activo_1.litraje != activo_2.litraje) {
    // Solo valida igualdad de litraje para traspaso
}
```

**Impacto**: Bajo - traspasos entre fermentadores de diferente tamaño son bloqueados, pero no se valida si hay volumen.

#### INC-5: Envasado sin validación de volumen en backend

**Problema**: En `ajax_envasar.php`, no se valida que el volumen total no exceda el disponible:

```php
// Se reciben los datos pero no se valida
$volumen_origen_ml = isset($_POST['volumen_origen_ml']) ? intval($_POST['volumen_origen_ml']) : 0;
$volumen_total_usado_ml = isset($_POST['volumen_total_usado_ml']) ? intval($_POST['volumen_total_usado_ml']) : 0;
// No hay: if($volumen_total_usado_ml > $volumen_disponible_real)...
```

**Impacto**: Usuario podría enviar request manipulado y envasar más de lo disponible.

#### INC-6: Race condition en creación de cajas

**Problema**: En `ajax_crearCajaDeEnvases.php` líneas 49-76, la verificación y asignación no son atómicas:

```php
// Paso 1: Verificar disponibilidad
$disponibles = Envase::contarDisponiblesPorBatch($id_batch);
if($disponibles < $cantidad) { ... }

// Paso 2: Crear caja y asignar (puede haber pasado tiempo)
$caja->save();
$caja->asignarEnvases($asignaciones);  // Otro usuario pudo haber usado los envases
```

**Impacto**: Dos usuarios creando cajas simultáneamente podrían asignar los mismos envases.

#### INC-7: Doble declaración de variables JavaScript

**Problema**: `formatos_latas` y `formatos_botellas` se declaran dos veces:

```javascript
// Línea 296 (detalle-productos.php patrón)
var formatos_latas = <?= json_encode($formatos_latas,JSON_PRETTY_PRINT); ?>;

// Línea 1497-1498
const formatos_latas = <?= json_encode($formatos_latas, JSON_PRETTY_PRINT); ?>;
const formatos_botellas = <?= json_encode($formatos_botellas, JSON_PRETTY_PRINT); ?>;
```

**Impacto**: No causa error porque están en diferentes contextos (templates diferentes), pero es confuso.

#### INC-8: Falta limpieza de variables globales en modal

**Problema**: Líneas 2044-2048, al cerrar el modal de crear cajas, no se limpian todas las variables:

```javascript
$('#crear-cajas-modal').on('hidden.bs.modal', function() {
    // Se limpia crearCajasEsMixto pero no crearCajasTipoEnvase
    crearCajasEsMixto = false;
    // Falta: crearCajasTipoEnvase = 'Lata';
});
```

**Impacto**: Bajo - el tipo se establece al abrir el modal de nuevo.

### Tabla Resumen de Inconsistencias

| ID | Severidad | Archivo | Línea | Descripción | Estado |
|----|-----------|---------|-------|-------------|--------|
| INC-1 | ALTA | inventario-de-productos.php | 330 | Batch incorrecto en card Despachos | NO CORREGIDO |
| INC-2 | BAJA | inventario-de-productos.php | 1172-1178 | const vs var semántico | IGNORABLE |
| INC-3 | MEDIA | inventario-de-productos.php | 855 | Selector sin filtro de disponibilidad | NO CORREGIDO |
| INC-4 | BAJA | inventario-de-productos.php | 1280 | Sin validación de volumen negativo | MITIGADO (frontend) |
| INC-5 | ALTA | ajax_envasar.php | 14-22 | Sin validación de volumen en backend | NO CORREGIDO |
| INC-6 | MEDIA | ajax_crearCajaDeEnvases.php | 49-76 | Race condition | NO CORREGIDO |
| INC-7 | BAJA | inventario-de-productos.php | 296/1497 | Doble declaración variables | IGNORABLE |
| INC-8 | BAJA | inventario-de-productos.php | 2044 | Limpieza incompleta modal | NO CORREGIDO |

---

## Observaciones del Análisis de Trazabilidad (ANALISIS_TRAZABILIDAD_BARRIL.md)

Las siguientes observaciones fueron identificadas en el análisis de trazabilidad del 27/Nov/2025 y aplican a este documento QA:

### Problemas de Trazabilidad Estructural

#### P1: Despacho sin Cliente Destino (CRÍTICO)

**Descripción**: La entidad `Despacho` no tiene campo `id_clientes`, lo que significa que un despacho no sabe hacia dónde va hasta que se crea la `Entrega`.

**Impacto**:
- No se puede planificar rutas por cliente
- No se puede rastrear qué barriles están en camino a qué cliente
- Si el despacho se pierde, no se sabe el destino

**Evidencia**:
- `php/classes/Despacho.php` - No existe campo id_clientes
- `templates/central-despacho.php` - Los despachos no muestran cliente destino

**Solución Propuesta** (del documento de análisis):
```sql
ALTER TABLE despachos
ADD COLUMN id_clientes INT DEFAULT 0 AFTER id_usuarios_repartidor,
ADD INDEX idx_id_clientes (id_clientes);
```

---

#### P2: BatchActivo.litraje NO se actualiza al llenar barriles

**Descripción**: En `ajax_llenarBarriles.php`, cuando se llena un barril desde un fermentador, se actualiza `barril.litros_cargados` pero NO se descuenta de `BatchActivo.litraje`.

**Código Actual** (ajax_llenarBarriles.php:21-28):
```php
$batch_activo->litraje -= $_POST['cantidad_a_cargar'];  // ✅ SÍ se descuenta
$barril->litros_cargados += $_POST['cantidad_a_cargar'];
```

**Estado**: ✅ YA ESTÁ IMPLEMENTADO - El código actual SÍ descuenta del BatchActivo.

**PERO falta validación**:
- No valida que `cantidad_a_cargar <= batch_activo->litraje`
- No valida que `cantidad_a_cargar <= barril->litraje - barril->litros_cargados`

---

#### P4: Validación Insuficiente entre Despacho y Entrega

**Descripción**: No hay validación de que los barriles de un despacho coincidan con los entregados.

**Impacto**:
- Puede crearse una entrega sin relación con el despacho
- Barriles pueden "desaparecer" del sistema
- Dificulta auditorías

**Evidencia**: `repartidor.php:300-333` - No valida coincidencia con despacho

---

#### P7: Estados de Barril No Estandarizados

**Descripción**: Los estados posibles de un barril están hardcodeados en diferentes lugares:

| Archivo | Estados Definidos |
|---------|-------------------|
| `repartidor.php:202-208` | 'En terreno', 'Pinchado', 'Perdido', 'Devuelto a planta' |
| `detalle-barriles.php:95,102-105` | 'En planta', 'Perdido' |
| `ajax_guardarEntrega.php` | 'En terreno', 'Devuelto a planta' |
| `inventario-de-productos.php` | 'En planta', 'En despacho' |

**Recomendación**: Centralizar en constante o tabla de referencia.

---

### Debilidades de Trazabilidad Documentadas

| ID | Debilidad | Ubicación | Impacto |
|----|-----------|-----------|---------|
| D1 | No hay fecha de envasado explícita en Barril | Barril.php | Dificulta cálculo FIFO y caducidad |
| D2 | No hay campo de número de usos del barril | Barril.php | Sin tracking de desgaste |
| D3 | No hay registro de merma/desperdicio | Batch.php | Sin control de pérdidas |
| D4 | No hay registro de limpieza de fermentadores | Activo.php | Sin control sanitario |
| D5 | No hay ruta de despacho | Despacho.php | Sin planificación logística |
| D6 | Campo `rand_int` no documentado | Entrega.php | Función no clara |

### Recomendaciones de Performance (P8)

Índices sugeridos para queries de trazabilidad:
```sql
CREATE INDEX idx_entregas_productos_barriles ON entregas_productos(id_barriles);
CREATE INDEX idx_barriles_estados_barriles ON barriles_estados(id_barriles);
CREATE INDEX idx_despachos_productos_barriles ON despachos_productos(id_barriles);
CREATE INDEX idx_barriles_batches ON barriles(id_batches);
CREATE INDEX idx_envases_cajas ON envases(id_cajas_de_envases);
CREATE INDEX idx_envases_batch ON envases(id_batches_de_envases);
```

---

## Puntos Problemáticos del Código

### 1. CRÍTICO: `id_productos = 0` en nuevo-despachos.php para CajaEnvases

**Ubicación**: `templates/nuevo-despachos.php:721-730`

**Problema**: Al agregar una CajaEnvases al despacho, se envía `id_productos: '0'` en lugar del ID real del producto.

```javascript
productos_lista.push({
    'tipo': 'CajaEnvases',
    'cantidad': selectedOption.data('cantidad') + ' unid.',
    'tipos_cerveza': selectedOption.data('producto'),
    'codigo': selectedOption.text().split(' - ')[0],
    'id_cajas_de_envases': cajaId,
    'id_productos': '0',  // ❌ PROBLEMA: Debería ser el id_productos de la caja
    'id_barriles': '0',
    'clasificacion': selectedOption.data('tipo')
});
```

**Impacto**:
- La facturación falla porque `id_productos = 0`
- El fix en `ajax_guardarDespacho.php` mitiga esto obteniendo el `id_productos` de la caja

**Estado**: MITIGADO pero no corregido en origen

**Recomendación**: Agregar `data-id-productos` al option y usarlo:
```javascript
'id_productos': selectedOption.data('id-productos') || '0',
```

---

### 2. MEDIO: Validación de array vacío incorrecta

**Ubicación**: `templates/nuevo-despachos.php:561,594`

**Problema**: La comparación `productos_lista == []` siempre es `false` en JavaScript.

```javascript
if(productos_lista == []) {  // ❌ Siempre false
    return false;
}
```

**Corrección**:
```javascript
if(productos_lista.length === 0) {
    return false;
}
```

**Impacto**: Bajo, porque el botón está deshabilitado si no hay productos. Pero es una validación que no funciona.

---

### 3. MEDIO: Sin transacción en procesos multi-tabla

**Ubicación**:
- `ajax/ajax_envasar.php`
- `ajax/ajax_crearCajaDeEnvases.php`
- `ajax/ajax_guardarEntrega.php`

**Problema**: Operaciones que modifican múltiples tablas no usan transacciones. Si falla a mitad del proceso, quedan datos inconsistentes.

**Ejemplo en ajax_envasar.php**:
```php
// Crea BatchDeEnvases
$batchDeEnvases->save();

// Crea N envases en loop
for($i = 0; $i < $cantidad; $i++) {
    $envase->save();  // Si falla aquí, quedan envases parciales
}

// Vacía el origen
$batchActivo->litraje = 0;  // Si falla aquí, se perdió volumen
```

**Impacto**: Datos inconsistentes si hay error de BD a mitad del proceso.

**Recomendación**: Usar transacciones:
```php
$mysqli->begin_transaction();
try {
    // ... operaciones ...
    $mysqli->commit();
} catch (Exception $e) {
    $mysqli->rollback();
    // ... error handling ...
}
```

---

### 4. BAJO: Sin límite de envases en envasado

**Ubicación**: `ajax/ajax_envasar.php:125-136`

**Problema**: Si se envasan 10,000 unidades, se crean 10,000 registros en la tabla `envases`.

```php
for($i = 0; $i < $cantidad; $i++) {
    $envase = new Envase();
    // ...
    $envase->save();  // 10,000 INSERTs individuales
}
```

**Impacto**:
- Performance: miles de INSERTs individuales
- BD: crecimiento rápido de la tabla envases

**Recomendación**: Usar INSERT masivo o limitar cantidad máxima por operación.

---

### 5. MEDIO: Condición de carrera en asignación de envases

**Ubicación**: `ajax/ajax_crearCajaDeEnvases.php:71-75`

**Problema**: Entre la validación de disponibilidad y la asignación puede ocurrir una condición de carrera si dos usuarios crean cajas simultáneamente.

```php
// Usuario A verifica: 24 disponibles ✓
// Usuario B verifica: 24 disponibles ✓
$disponibles = Envase::contarDisponiblesPorBatch($id_batch);

// Usuario A asigna 24 ✓
// Usuario B intenta asignar 24... ¿solo quedan 0?
```

**Impacto**: Posible asignación de envases que ya no existen.

**Recomendación**: SELECT FOR UPDATE o validar nuevamente al asignar.

---

### 6. BAJO: Llenado de barril sin validación de capacidad

**Ubicación**: `ajax/ajax_llenarBarriles.php:21-22`

**Problema**: No valida que la cantidad a cargar no exceda la capacidad del barril.

```php
$batch_activo->litraje -= $_POST['cantidad_a_cargar'];
$barril->litros_cargados += $_POST['cantidad_a_cargar'];
// No valida si litros_cargados > litraje del barril
```

**Impacto**: Se puede "llenar" un barril de 30L con 50L.

---

### 7. MEDIO: Revertir envasado no restaura correctamente si origen fue eliminado

**Ubicación**: `ajax/ajax_revertirEnvasado.php:57-91`

**Problema**: Si el fermentador o barril origen fue eliminado/modificado, la reversión falla silenciosamente.

```php
if($id_batches_activos > 0) {
    $batchActivo = new BatchActivo($id_batches_activos);
    if(!empty($batchActivo->id)) {
        $batchActivo->litraje = $volumen_a_devolver_l;
        $batchActivo->save();
    }
    // Si batchActivo no existe, no hace nada y no informa
}
```

**Impacto**: Volumen "perdido" si el origen ya no existe.

---

### 8. BAJO: Inconsistencia en soft delete vs hard delete

**Ubicación**: Múltiples archivos

**Problema**: Algunos delete son soft (`estado='eliminado'`), otros son hard (`DELETE FROM`).

- `CajaDeEnvases`: soft delete (`estado='eliminado'`)
- `Envase`: hard delete (en revertir envasado)
- `DespachoProducto`: hard delete

**Impacto**: Inconsistencia en auditoría y posibilidad de restauración.

---

### 9. MEDIO: Sin validación CSRF en algunos endpoints

**Ubicación**:
- `ajax/ajax_llenarBarriles.php`
- `ajax/ajax_guardarDespacho.php`
- `ajax/ajax_guardarEntrega.php`

**Problema**: No validan token CSRF aunque el frontend lo envía en algunos casos.

```php
// ajax_crearCajaDeEnvases.php usa CSRF
$csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

// ajax_guardarDespacho.php NO usa CSRF
```

**Impacto**: Vulnerabilidad CSRF en operaciones críticas.

---

### 10. BAJO: Código comentado que filtra barriles no funciona

**Ubicación**: `templates/nuevo-despachos.php:347-351`

**Problema**: Hay código comentado que pretendía filtrar barriles por producto pero no funciona.

```javascript
var barriles_cerveza = barriles_enplanta_cerveza /*.filter((b) => {
    // Este filtro está comentado
    return b.tipo_barril == $('#tipos_barril_select').val() && b.id_productos == $('#tipos_barril_cerveza_select').val()
});*/
```

**Impacto**: Se muestran todos los barriles en lugar de solo los del tipo seleccionado.

---

## Casos Edge

### Batches y Fermentadores

| # | Caso Edge | Comportamiento Esperado | Riesgo |
|---|-----------|------------------------|--------|
| B1 | Agregar fermentador ya ocupado a batch | Error: fermentador ocupado | ❌ No validado (INC-3) |
| B2 | Agregar fermentador sin batch seleccionado | Usa primer batch de lista | ⚠️ Silencioso |
| B3 | Eliminar batch activo con litraje > 0 | Debería advertir pérdida | ⚠️ Sin confirmación |
| B4 | Traspasar a fermentador de diferente tamaño | Error: litrajes diferentes | ✓ Validado |
| B5 | Traspasar sin fermentadores disponibles | Mensaje: no hay disponibles | ✓ Validado |
| B6 | Traspasar desde fermentador vacío (litraje=0) | Se traspasa vacío | ⚠️ Sin validar litraje |
| B7 | Batch con múltiples fermentadores | Se muestran agrupados | ✓ OK |
| B8 | Fermentador INOX en card fermentación | Aparece en ambas listas | ✓ Correcto (línea 17-21) |

### Llenado de Barriles

| # | Caso Edge | Comportamiento Esperado | Riesgo |
|---|-----------|------------------------|--------|
| L1 | Llenar barril más allá de su capacidad | Error: excede capacidad | ❌ No validado |
| L2 | Llenar barril con cantidad = 0 | No debería permitirse | ⚠️ Frontend solo |
| L3 | Llenar barril con cantidad negativa | Error | ⚠️ Solo frontend |
| L4 | Llenar barril desde fermentador vacío | Error: sin litraje | ⚠️ No validado backend |
| L5 | Llenar barril ya lleno | Suma a litros existentes | ⚠️ Puede exceder capacidad |
| L6 | Seleccionar barril que no existe | Error: barril no encontrado | ✓ Validado |
| L7 | Dos usuarios llenan del mismo fermentador | Race condition | ❌ No manejado |

### Envasado

| # | Caso Edge | Comportamiento Esperado | Riesgo |
|---|-----------|------------------------|--------|
| E1 | Envasar con 0 envases | Error: cantidad debe ser > 0 | ✓ Validado |
| E2 | Envasar más volumen del disponible | Error o validación frontend | ⚠️ Solo validación frontend |
| E3 | Envasar desde fermentador vacío | Error: fermentador sin litraje | ⚠️ No validado en backend |
| E4 | Envasar desde barril vacío | Error: barril sin litros | ⚠️ No validado en backend |
| E5 | Formato de envase inválido | Error: formato no encontrado | ✓ Validado |
| E6 | Envasar mientras otro usuario envasa del mismo origen | Condición de carrera | ❌ No manejado |
| E7 | Envasar con formato que no corresponde al tipo | Error: formato no corresponde | ✓ Validado |
| E8 | Envasar cantidad muy grande (>10,000) | Performance degradada | ⚠️ Sin límite |

### Crear Cajas

| # | Caso Edge | Comportamiento Esperado | Riesgo |
|---|-----------|------------------------|--------|
| C1 | Crear caja con cantidad incorrecta | Error: cantidad no coincide | ✓ Validado |
| C2 | Crear caja mixta con envases de formatos diferentes | Error: formato diferente | ✓ Validado |
| C3 | Crear caja con batch que ya no tiene envases disponibles | Error: insuficientes | ✓ Validado |
| C4 | Crear dos cajas simultáneamente del mismo batch | Condición de carrera | ❌ No manejado |
| C5 | Crear caja con producto que no existe | Error: producto no encontrado | ✓ Validado |
| C6 | Crear caja con producto sin configuración de envases | Error: no configurado | ✓ Validado |
| C7 | Crear caja mixta asignando 0 envases de un batch | Se ignora el batch | ✓ OK |
| C8 | Generar código de caja duplicado | Posible colisión de código | ⚠️ Bajo riesgo |

### Despacho

| # | Caso Edge | Comportamiento Esperado | Riesgo |
|---|-----------|------------------------|--------|
| D1 | Despachar barril que ya está en despacho | Error o duplicado | ⚠️ No validado |
| D2 | Despachar caja que ya está en despacho | No aparece en lista | ✓ Filtrado en frontend |
| D3 | Despachar barril de CO2 sin stock | Botón deshabilitado | ✓ OK |
| D4 | Despachar sin repartidor seleccionado | Usa el primero de la lista | ⚠️ Silencioso |
| D5 | Despachar caja eliminada | No aparece en lista | ✓ Filtrado |
| D6 | Eliminar despacho con caja que fue re-despachada | Estado inconsistente | ❌ No manejado |
| D7 | Crear despacho con lista vacía | Botón deshabilitado | ✓ OK |
| D8 | Agregar mismo barril dos veces | Se agrega duplicado | ❌ No validado |

### Entrega

| # | Caso Edge | Comportamiento Esperado | Riesgo |
|---|-----------|------------------------|--------|
| N1 | Entregar sin cliente seleccionado | Botón deshabilitado | ✓ OK |
| N2 | Entregar sin actualizar estado de barriles del cliente | Warning mostrado | ✓ OK |
| N3 | Entregar producto con id_productos=0 | Error en facturación | ⚠️ Mitigado |
| N4 | Doble click en entregar (duplicar entrega) | rand_int previene duplicados | ✓ OK |
| N5 | Cliente sin emite_factura configurado | No genera factura | ✓ OK |
| N6 | Cliente con email inválido | Error en envío de correo | ⚠️ No manejado |
| N7 | Entregar caja sin productos_items configurados | Factura sin detalle | ❌ Bug conocido |
| N8 | Entregar vasos sin cantidad | Modal solicita cantidad | ✓ OK |

### Reversión

| # | Caso Edge | Comportamiento Esperado | Riesgo |
|---|-----------|------------------------|--------|
| R1 | Revertir envasado con envases en cajas | Error: envases asignados | ✓ Validado |
| R2 | Revertir envasado de origen eliminado | Envases eliminados, volumen perdido | ⚠️ Silencioso |
| R3 | Revertir caja en despacho | Error: solo en planta | ✓ Validado |
| R4 | Revertir caja ya entregada | Error: solo en planta | ✓ Validado |
| R5 | Eliminar despacho con caja | Caja vuelve a "En planta" | ✓ OK |
| R6 | Dos usuarios intentan revertir la misma caja | Condición de carrera | ⚠️ No manejado |

---

## Tests Manuales por Proceso

### T0. Flujo de Batches y Fermentadores

#### T0.1 Agregar Fermentador a Batch
```
PRECONDICIONES:
- Existe al menos un Batch en proceso
- Existe al menos un Fermentador disponible (id_batches=0)

PASOS:
1. Ir a Inventario de Productos
2. Click en "Agregar Fermentador" en card Fermentación
3. Seleccionar batch
4. Seleccionar fermentador disponible
5. Verificar que cantidad (litraje) está pre-llenado
6. Click en "Agregar"

RESULTADO ESPERADO:
- BatchActivo creado con estado='Fermentación'
- Activo.id_batches actualizado
- Fermentador aparece en card Fermentación bajo el batch

VERIFICACIÓN BD:
SELECT * FROM batches_activos WHERE id_batches=[ID] AND id_activos=[ID_ACTIVO];
SELECT id_batches FROM activos WHERE id=[ID_ACTIVO];
```

#### T0.2 Traspasar entre Fermentadores
```
PRECONDICIONES:
- Existe BatchActivo en Fermentación/Maduración con litraje > 0
- Existe Fermentador vacío del MISMO litraje

PASOS:
1. Ir a Inventario de Productos
2. Click en "Traspasar" en card Maduración
3. Seleccionar fermentador origen (con cerveza)
4. Seleccionar fermentador destino (vacío, mismo litraje)
5. Verificar que muestra info del batch
6. Click en "Traspasar"

RESULTADO ESPERADO:
- BatchActivo origen actualizado (id_activos cambia)
- Activo origen liberado (id_batches=0)
- Activo destino asignado al batch
- Historial de traspaso registrado

VERIFICACIÓN BD:
SELECT * FROM batches_activos WHERE id_batches=[ID_BATCH];
SELECT id_batches FROM activos WHERE id IN ([ID_ORIGEN], [ID_DESTINO]);
```

#### T0.3 Eliminar Fermentador de Maduración
```
PASOS:
1. En card Maduración, expandir un batch
2. Click en "Eliminar" junto al fermentador
3. Confirmar eliminación

RESULTADO ESPERADO:
- BatchActivo.litraje = 0
- Activo liberado
- Warning: si litraje > 0, se pierde volumen

⚠️ NOTA: Actualmente NO advierte sobre pérdida de volumen
```

#### T0.4 Validación de Litraje Diferente en Traspaso
```
PASOS:
1. Intentar traspasar de fermentador 60L a fermentador 30L

RESULTADO ESPERADO:
- Warning: "Los fermentadores deben tener el mismo litraje"
- Botón "Traspasar" deshabilitado
```

### T1. Flujo Completo de Envasado

#### T1.1 Envasar desde Fermentador
```
PRECONDICIONES:
- Existe un BatchActivo en estado "Maduración" con litraje > 0
- Existen formatos de envases activos

PASOS:
1. Ir a Inventario de Productos
2. Click en "Envasar"
3. Seleccionar origen: Fermentador
4. Seleccionar fermentador con cerveza
5. Click en "+ Latas"
6. Seleccionar formato (ej: 473ml)
7. Ingresar cantidad (ej: 100)
8. Verificar que "Max" se calcula correctamente
9. Click en "Confirmar Envasado"

RESULTADO ESPERADO:
- Mensaje de éxito: "Envasado exitoso: 100 latas"
- BatchDeEnvases creado con cantidad_de_envases = 100
- 100 registros en tabla envases
- Fermentador queda con litraje = 0
- Envases aparecen en card "Envases en Planta"

VERIFICACIÓN BD:
SELECT * FROM batches_de_envases WHERE id = [ID_CREADO];
SELECT COUNT(*) FROM envases WHERE id_batches_de_envases = [ID_CREADO];
SELECT litraje FROM batches_activos WHERE id = [ID_BATCH_ACTIVO];
```

#### T1.2 Envasar desde Barril
```
PRECONDICIONES:
- Existe un Barril en estado "En planta" con litros_cargados > 0

PASOS:
1. Ir a Inventario de Productos
2. Click en "Envasar"
3. Seleccionar origen: Barril
4. Seleccionar barril con cerveza
5. Agregar línea de botellas
6. Confirmar envasado

RESULTADO ESPERADO:
- BatchDeEnvases creado con id_barriles = [ID_BARRIL]
- Barril queda con litros_cargados = 0, id_batches = 0

VERIFICACIÓN BD:
SELECT litros_cargados, id_batches FROM barriles WHERE id = [ID_BARRIL];
```

#### T1.3 Envasar Múltiples Líneas (Latas + Botellas)
```
PASOS:
1. Envasar con línea de 50 latas 473ml
2. Agregar línea de 30 botellas 330ml
3. Verificar cálculo de volumen total
4. Confirmar

RESULTADO ESPERADO:
- 2 BatchDeEnvases creados (uno para latas, uno para botellas)
- Total volumen = (50 * 473) + (30 * 330) = 33,550 ml
```

---

### T2. Flujo Completo de Creación de Cajas

#### T2.1 Crear Caja Simple (Un Solo Batch)
```
PRECONDICIONES:
- Existen envases disponibles de un BatchDeEnvases
- Existe un Producto tipo "Caja" configurado para ese formato

PASOS:
1. Ir a Inventario de Productos
2. En card "Envases en Planta", click en "Crear Cajas de Latas"
3. Seleccionar producto (ej: "Pack 24 Latas IPA")
4. En paso 2, asignar 24 envases de un batch
5. Click en "Crear Caja"

RESULTADO ESPERADO:
- CajaDeEnvases creada con código CAJA-YYMMDD-XXXX
- 24 Envases actualizados: id_cajas_de_envases = [ID_CAJA]
- Envases cambian estado a "En caja"
- Caja aparece en card "Cajas en Planta"

VERIFICACIÓN BD:
SELECT * FROM cajas_de_envases WHERE id = [ID_CAJA];
SELECT COUNT(*) FROM envases WHERE id_cajas_de_envases = [ID_CAJA];
SELECT estado FROM envases WHERE id_cajas_de_envases = [ID_CAJA] LIMIT 1;
```

#### T2.2 Crear Caja Mixta (Múltiples Batches/Recetas)
```
PRECONDICIONES:
- Producto marcado como es_mixto = 1
- Envases disponibles de múltiples BatchDeEnvases con diferentes recetas

PASOS:
1. Seleccionar producto mixto
2. Asignar envases de diferentes batches/recetas
3. Verificar que total = cantidad requerida
4. Crear caja

RESULTADO ESPERADO:
- Caja creada con envases de diferentes orígenes
- getContenidoResumen() muestra las diferentes recetas

VERIFICACIÓN:
SELECT e.id, bde.id_recetas, r.nombre
FROM envases e
JOIN batches_de_envases bde ON e.id_batches_de_envases = bde.id
JOIN recetas r ON bde.id_recetas = r.id
WHERE e.id_cajas_de_envases = [ID_CAJA];
```

#### T2.3 Validación de Cantidad Incorrecta
```
PASOS:
1. Seleccionar producto con cantidad_de_envases = 24
2. Intentar asignar 20 envases
3. Verificar warning "Faltan 4 latas por asignar"
4. Verificar botón "Crear Caja" deshabilitado

RESULTADO ESPERADO:
- No permite crear caja con cantidad incorrecta
```

---

### T3. Flujo Completo de Despacho

#### T3.1 Crear Despacho con Barril de Cerveza
```
PASOS:
1. Ir a Nuevo Despacho
2. Seleccionar repartidor
3. Click en "+ Barril"
4. Seleccionar tipo (30L), producto (IPA), código
5. Click "Agregar"
6. Click "Guardar"

RESULTADO ESPERADO:
- Despacho creado
- DespachoProducto creado con tipo="Barril", id_barriles=[ID]
- Barril cambia estado a "En despacho"

VERIFICACIÓN BD:
SELECT estado FROM barriles WHERE id = [ID_BARRIL];
SELECT * FROM despachos_productos WHERE id_despachos = [ID_DESPACHO];
```

#### T3.2 Crear Despacho con Caja de Envases
```
PASOS:
1. Ir a Nuevo Despacho
2. Click en "+ Caja"
3. Filtrar por tipo (Lata)
4. Seleccionar caja disponible
5. Verificar info mostrada (producto, cantidad, mixto)
6. Click "Agregar"
7. Guardar despacho

RESULTADO ESPERADO:
- DespachoProducto creado con:
  - tipo = "CajaEnvases"
  - id_cajas_de_envases = [ID_CAJA]
  - id_productos = [ID_PRODUCTO] (NO 0)
- CajaDeEnvases cambia estado a "En despacho"

VERIFICACIÓN BD:
SELECT id_productos, id_cajas_de_envases FROM despachos_productos
WHERE id_despachos = [ID_DESPACHO] AND tipo = 'CajaEnvases';
-- id_productos NO debe ser 0

SELECT estado FROM cajas_de_envases WHERE id = [ID_CAJA];
-- estado debe ser "En despacho"
```

#### T3.3 Crear Despacho Mixto (Barril + Caja + CO2)
```
PASOS:
1. Agregar barril de cerveza
2. Agregar caja de envases
3. Agregar barril de CO2
4. Guardar

RESULTADO ESPERADO:
- 3 DespachoProducto creados
- Todos los items cambian estado a "En despacho"
```

#### T3.4 Eliminar Despacho (Reversión)
```
PRECONDICIONES:
- Despacho existente con barril y caja

PASOS:
1. Ir a Detalle de Despacho
2. Eliminar despacho

RESULTADO ESPERADO:
- Barril vuelve a estado "En planta"
- CajaDeEnvases vuelve a estado "En planta"
- DespachoProductos eliminados
- Despacho eliminado

VERIFICACIÓN BD:
SELECT estado FROM barriles WHERE id = [ID_BARRIL];
SELECT estado FROM cajas_de_envases WHERE id = [ID_CAJA];
```

---

### T4. Flujo Completo de Entrega

#### T4.1 Entregar a Cliente (Sin Factura)
```
PRECONDICIONES:
- Despacho existente asignado al repartidor logueado
- Cliente con emite_factura = 0

PASOS:
1. Login como repartidor
2. Ir a sección Repartidor
3. Seleccionar cliente
4. Si cliente tiene barriles, actualizar estados
5. Seleccionar productos a entregar
6. Click "Entregar a Cliente"
7. Ingresar nombre de receptor
8. Confirmar

RESULTADO ESPERADO:
- Entrega creada con monto calculado
- EntregaProducto creados (copia de DespachoProducto)
- DespachoProducto eliminados
- Barril cambia estado a "En terreno", id_clientes = [ID_CLIENTE]
- CajaDeEnvases cambia estado a "Entregada"
- NO se genera DTE

VERIFICACIÓN BD:
SELECT * FROM entregas WHERE id = [ID_ENTREGA];
SELECT * FROM entregas_productos WHERE id_entregas = [ID_ENTREGA];
SELECT estado, id_clientes FROM barriles WHERE id = [ID_BARRIL];
```

#### T4.2 Entregar a Cliente (Con Factura)
```
PRECONDICIONES:
- Cliente con emite_factura = 1
- Producto con productos_items configurados

PASOS:
1. Realizar entrega normal a cliente con factura

RESULTADO ESPERADO:
- DTE generado con folio
- Entrega.factura = [FOLIO]
- Correo enviado al cliente

VERIFICACIÓN BD:
SELECT factura FROM entregas WHERE id = [ID_ENTREGA];
SELECT * FROM dte WHERE id_entregas = [ID_ENTREGA];
```

#### T4.3 Entregar Caja Mixta (Verificar Descripción en Factura)
```
PRECONDICIONES:
- Caja mixta en despacho
- Cliente con factura

PASOS:
1. Entregar caja mixta
2. Verificar descripción en DTE

RESULTADO ESPERADO:
- Descripción: "Lata x24 [Nombre Producto]" (no "Caja 24 [Nombre]")
```

---

### T5. Flujos de Reversión

#### T5.1 Revertir Envasado
```
PRECONDICIONES:
- BatchDeEnvases con envases disponibles (no en cajas)

PASOS:
1. En "Envases en Planta", click en botón revertir (↺)
2. Verificar info en modal
3. Confirmar reversión

RESULTADO ESPERADO:
- Envases eliminados de BD
- BatchDeEnvases eliminado
- Volumen devuelto al origen (fermentador o barril)

VERIFICACIÓN BD:
SELECT COUNT(*) FROM envases WHERE id_batches_de_envases = [ID];
-- Debe ser 0

SELECT litraje FROM batches_activos WHERE id = [ID_BATCH_ACTIVO];
-- Debe tener el volumen restaurado
```

#### T5.2 Revertir Envasado con Envases en Cajas (Error Esperado)
```
PASOS:
1. Intentar revertir BatchDeEnvases que tiene envases asignados a cajas

RESULTADO ESPERADO:
- Error: "No se puede revertir: X envases ya están asignados a cajas"
```

#### T5.3 Revertir Caja
```
PRECONDICIONES:
- CajaDeEnvases en estado "En planta"

PASOS:
1. En "Cajas en Planta", click en botón revertir
2. Confirmar

RESULTADO ESPERADO:
- Envases liberados (id_cajas_de_envases = 0, estado = "Envasado")
- Caja en estado "eliminado"
- Envases vuelven a aparecer disponibles

VERIFICACIÓN BD:
SELECT id_cajas_de_envases, estado FROM envases
WHERE id_batches_de_envases IN (SELECT id_batches_de_envases FROM envases WHERE id_cajas_de_envases = [ID_CAJA] LIMIT 1);
```

#### T5.4 Revertir Caja en Despacho (Error Esperado)
```
PASOS:
1. Intentar eliminar caja que está en estado "En despacho"

RESULTADO ESPERADO:
- Error: "Solo se pueden eliminar cajas que están en planta"
```

---

### T6. Tests de Barriles

#### T6.1 Llenar Barril desde Fermentador
```
PASOS:
1. Ir a Inventario de Productos
2. Click en "Llenar Barril"
3. Seleccionar fermentador con cerveza
4. Seleccionar barril vacío
5. Ingresar cantidad a cargar
6. Confirmar

RESULTADO ESPERADO:
- Barril: litros_cargados incrementado
- Barril: id_batches, id_activos, id_batches_activos asignados
- Fermentador: litraje decrementado

VERIFICACIÓN:
SELECT litros_cargados, id_batches FROM barriles WHERE id = [ID];
SELECT litraje FROM batches_activos WHERE id = [ID_BATCH_ACTIVO];
```

#### T6.2 Devolver Barril a Planta (Desde Entrega)
```
PRECONDICIONES:
- Cliente con barril en estado "En terreno"

PASOS:
1. En vista repartidor, seleccionar cliente
2. En tabla de barriles del cliente, seleccionar "Devuelto a planta"
3. Realizar entrega

RESULTADO ESPERADO:
- Barril cambia estado a "En planta"
- Barril: id_clientes = 0, id_batches = 0, litros_cargados = 0

VERIFICACIÓN BD:
SELECT estado, id_clientes, id_batches, litros_cargados FROM barriles WHERE id = [ID];
```

---

## Checklist de Regresión

### Pre-Deploy (Mínimo)
- [ ] T0.1 Agregar fermentador a batch
- [ ] T0.2 Traspasar entre fermentadores
- [ ] T1.1 Envasar desde fermentador
- [ ] T1.2 Envasar desde barril
- [ ] T2.1 Crear caja simple
- [ ] T2.2 Crear caja mixta
- [ ] T3.2 Despacho con caja (verificar id_productos ≠ 0)
- [ ] T4.2 Entrega con factura
- [ ] T5.1 Revertir envasado
- [ ] T5.3 Revertir caja
- [ ] T5.4 Eliminar despacho (verificar reversión de estados)
- [ ] T6.1 Llenar barril desde fermentador

### Post-Fix de Bug Crítico
- [ ] Verificar id_productos en DespachoProducto para CajaEnvases
- [ ] Verificar descripción en factura para productos tipo Caja
- [ ] Verificar reversión de CajaEnvases al eliminar despacho
- [ ] Verificar que INC-1 (batch incorrecto en despachos) está corregido

### Regresión Completa (Release Mayor)
- [ ] Todos los tests T0.x (Batches y Fermentadores)
- [ ] Todos los tests T1.x (Envasado)
- [ ] Todos los tests T2.x (Cajas)
- [ ] Todos los tests T3.x (Despacho)
- [ ] Todos los tests T4.x (Entrega)
- [ ] Todos los tests T5.x (Reversión)
- [ ] Todos los tests T6.x (Barriles)

### Checklist de Inconsistencias inventario-de-productos.php
- [ ] INC-1: Fix línea 330 - usar $ba->id_batches
- [ ] INC-3: Filtrar solo fermentadores disponibles en select
- [ ] INC-5: Agregar validación de volumen en backend
- [ ] INC-6: Implementar transacciones o SELECT FOR UPDATE

---

## Queries de Verificación Rápida

### Verificar integridad de datos generales
```sql
-- Cajas con envases inconsistentes
SELECT c.id, c.codigo, c.cantidad_envases, COUNT(e.id) as envases_reales
FROM cajas_de_envases c
LEFT JOIN envases e ON e.id_cajas_de_envases = c.id
WHERE c.estado != 'eliminado'
GROUP BY c.id
HAVING c.cantidad_envases != envases_reales;

-- DespachoProducto sin id_productos (problema de facturación)
SELECT dp.*, d.estado
FROM despachos_productos dp
JOIN despachos d ON dp.id_despachos = d.id
WHERE dp.tipo = 'CajaEnvases' AND (dp.id_productos = 0 OR dp.id_productos IS NULL);

-- Envases "huérfanos" (en caja eliminada)
SELECT e.* FROM envases e
JOIN cajas_de_envases c ON e.id_cajas_de_envases = c.id
WHERE c.estado = 'eliminado' AND e.id_cajas_de_envases != 0;

-- Barriles con estado inconsistente
SELECT * FROM barriles
WHERE (estado = 'En terreno' AND id_clientes = 0)
   OR (estado = 'En planta' AND id_clientes != 0);
```

### Verificar inconsistencias de Batches y Fermentadores
```sql
-- Fermentadores ocupados (con id_batches) pero sin BatchActivo
SELECT a.id, a.codigo, a.id_batches
FROM activos a
WHERE a.id_batches != 0
AND NOT EXISTS (
    SELECT 1 FROM batches_activos ba
    WHERE ba.id_activos = a.id AND ba.litraje > 0
);

-- BatchActivo sin batch padre válido
SELECT ba.*
FROM batches_activos ba
LEFT JOIN batches b ON ba.id_batches = b.id
WHERE b.id IS NULL OR b.estado = 'eliminado';

-- Barriles con litros_cargados > litraje (sobre-llenados)
SELECT id, codigo, litros_cargados, litraje
FROM barriles
WHERE litros_cargados > litraje;

-- Barriles llenos pero sin batch asociado
SELECT id, codigo, litros_cargados, estado
FROM barriles
WHERE litros_cargados > 0 AND id_batches = 0;

-- BatchDeEnvases sin envases (registro huérfano)
SELECT bde.id, bde.tipo, bde.cantidad_de_envases
FROM batches_de_envases bde
LEFT JOIN envases e ON e.id_batches_de_envases = bde.id
WHERE e.id IS NULL AND bde.cantidad_de_envases > 0;
```

### Verificar flujo de trazabilidad completo
```sql
-- Entregas sin trazabilidad a batch (para barriles)
SELECT e.id, ep.id_barriles, b.id_batches
FROM entregas e
JOIN entregas_productos ep ON e.id = ep.id_entregas
JOIN barriles b ON ep.id_barriles = b.id
WHERE ep.tipo = 'Barril' AND b.id_batches = 0;

-- Cajas entregadas sin registro de entrega
SELECT c.id, c.codigo, c.estado
FROM cajas_de_envases c
WHERE c.estado = 'Entregada'
AND NOT EXISTS (
    SELECT 1 FROM entregas_productos ep
    WHERE ep.id_cajas_de_envases = c.id
);

-- Despachos con productos de cajas inexistentes
SELECT dp.id, dp.id_cajas_de_envases, c.id as caja_existe
FROM despachos_productos dp
LEFT JOIN cajas_de_envases c ON dp.id_cajas_de_envases = c.id
WHERE dp.tipo = 'CajaEnvases' AND c.id IS NULL;
```

### Verificar estados consistentes en el flujo
```sql
-- Resumen de estados por entidad
SELECT 'Barriles' as entidad, estado, COUNT(*) as cantidad
FROM barriles GROUP BY estado
UNION ALL
SELECT 'Cajas', estado, COUNT(*) FROM cajas_de_envases GROUP BY estado
UNION ALL
SELECT 'Envases', estado, COUNT(*) FROM envases GROUP BY estado
UNION ALL
SELECT 'BatchActivos', estado, COUNT(*) FROM batches_activos GROUP BY estado;

-- Transiciones de estado inválidas (barriles)
SELECT * FROM barriles
WHERE estado NOT IN ('En planta', 'En despacho', 'En terreno', 'Pinchado', 'Perdido', 'En sala de frio');

-- Cajas en estado inválido
SELECT * FROM cajas_de_envases
WHERE estado NOT IN ('En planta', 'En despacho', 'Entregada', 'eliminado');
```

---

## Resumen de Hallazgos Críticos

### Inconsistencias de Código (Nuevas)

| Prioridad | Código | Descripción | Archivo | Acción Requerida |
|-----------|--------|-------------|---------|------------------|
| **ALTA** | INC-1 | Batch incorrecto en card Despachos | inventario-de-productos.php:330 | Cambiar `$batch->id` por `$ba->id_batches` |
| **ALTA** | INC-5 | Sin validación volumen backend | ajax_envasar.php | Agregar validación server-side |
| **ALTA** | PP-1 | id_productos=0 en CajaEnvases | nuevo-despachos.php:727 | Agregar data-id-productos al option |
| **MEDIA** | INC-3 | Selector sin filtro disponibilidad | inventario-de-productos.php:855 | Filtrar $activos_disponibles |
| **MEDIA** | INC-6 | Race condition en cajas | ajax_crearCajaDeEnvases.php | Implementar transacciones |
| **MEDIA** | PP-3 | Sin transacciones multi-tabla | ajax_envasar.php, ajax_guardarEntrega.php | Agregar BEGIN/COMMIT/ROLLBACK |
| **BAJA** | PP-6 | Sin validar capacidad barril | ajax_llenarBarriles.php | Agregar validación de capacidad |

### Problemas de Trazabilidad (Del Análisis 27/Nov/2025)

| Prioridad | Código | Descripción | Archivo | Acción Requerida |
|-----------|--------|-------------|---------|------------------|
| **CRÍTICO** | P1 | Despacho sin id_clientes | Despacho.php, central-despacho.php | ALTER TABLE + modificar vistas |
| **MEDIA** | P4 | Sin validación Despacho↔Entrega | repartidor.php, ajax_guardarEntrega.php | Agregar validación cruzada |
| **MEDIA** | P7 | Estados de barril no estandarizados | Múltiples archivos | Centralizar en constante/tabla |
| **BAJA** | P8 | Sin índices de performance | BD | Crear índices sugeridos |

### Debilidades de Modelo de Datos

| ID | Descripción | Tabla | Impacto |
|----|-------------|-------|---------|
| D1 | Sin fecha de envasado | barriles | FIFO imposible |
| D2 | Sin contador de usos | barriles | Sin tracking desgaste |
| D3 | Sin registro de merma | batches | Sin control pérdidas |
| D4 | Sin registro de limpieza | activos | Sin control sanitario |

---

## Documentos Relacionados

- `ANALISIS_TRAZABILIDAD_BARRIL.md` - Análisis completo de trazabilidad (27/Nov/2025)
- `TRAZABILIDAD_SISTEMA.md` - Documentación del sistema de trazabilidad
- `CLAUDE.md` - Guía de desarrollo del proyecto

---

*Documento actualizado el 2025-11-29*
*Sistema: Barril.cl ERP*
*Versión: 2.1 - Análisis Completo + Observaciones Trazabilidad*
