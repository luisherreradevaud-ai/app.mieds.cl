# Análisis Ultrathink: Sistema de Trazabilidad Barril.cl
## Cerveza Cocholgue - ERP Cervecería Artesanal

**Fecha:** 27 de Noviembre, 2025
**Autor:** Claude (Análisis Técnico)
**Versión:** 1.0
**Alcance:** Análisis completo del flujo de trazabilidad desde producción hasta entrega al cliente

---

## Tabla de Contenidos

1. [Introducción y Contexto](#1-introducción-y-contexto)
2. [Análisis del Flujo de Trazabilidad Actual](#2-análisis-del-flujo-de-trazabilidad-actual)
3. [Análisis de Trazabilidad por Entidad](#3-análisis-de-trazabilidad-por-entidad)
4. [Problemas Identificados](#4-problemas-identificados)
5. [Soluciones Propuestas](#5-soluciones-propuestas)
6. [Expansión a Formato Latas](#6-expansión-a-formato-latas)
7. [Certificación Halal para Medio Oriente](#7-certificación-halal-para-medio-oriente)
8. [Recomendaciones Finales](#8-recomendaciones-finales)

---

## 1. Introducción y Contexto

### 1.1 Visión General del Sistema

Barril.cl es un sistema ERP completo para la gestión de una cervecería artesanal chilena (Cerveza Cocholgue). El sistema implementa un **flujo de trazabilidad end-to-end** que permite rastrear cada litro de cerveza desde su inicio en la producción hasta su entrega final al cliente.

### 1.2 Objetivos del Análisis

Este documento analiza:
- El flujo completo de trazabilidad actual
- Puntos críticos donde puede perderse la trazabilidad
- Oportunidades de mejora
- Viabilidad de expansión a formato latas
- Requisitos para certificación Halal (Medio Oriente)

### 1.3 Arquitectura del Sistema de Trazabilidad

El sistema actual implementa una arquitectura de trazabilidad basada en **relaciones de entidades** donde cada etapa del proceso está vinculada mediante claves foráneas:

```
Batch (Producción)
  ↓ id_batches
BatchActivo (Fermentación)
  ↓ id_batches, id_activos
Activo (Fermentadores)
  ↓ id_batches
Barril (Envasado)
  ↓ id_batches, id_activos, id_batches_activos
Despacho → DespachoProducto (Logística)
  ↓ id_barriles
Entrega → EntregaProducto (Cliente Final)
  ↓ id_barriles, id_entregas
Cliente (Destino Final)
```

---

## 2. Análisis del Flujo de Trazabilidad Actual

### 2.1 FASE 1: Producción (Batches → Activos/Fermentadores)

#### Vista Principal: `nuevo-batches.php`, `inventario-de-productos.php`
#### Clases Involucradas: `Batch`, `BatchActivo`, `Activo`

**Flujo Detallado:**

1. **Creación del Batch**
   - Se crea un `Batch` con:
     - `batch_nombre`: Identificador único del batch
     - `id_recetas`: Receta a seguir
     - `batch_date`: Fecha de inicio
     - `batch_litros`: Volumen a producir

2. **Asignación a Fermentadores (Activos)**
   - Se crea una relación `BatchActivo` que vincula:
     - `id_batches`: El batch en producción
     - `id_activos`: El fermentador utilizado
     - `estado`: 'Fermentación' → 'Maduración'
     - `litraje`: Litros actuales en el fermentador

3. **Control de Estado en Fermentadores**
   - El `Activo` (fermentador) actualiza su campo:
     - `id_batches`: Referencia al batch actual
     - `litraje`: Capacidad del fermentador

**Trazabilidad en esta fase:**
✅ **FORTALEZA:** Triple vinculación (Batch ← BatchActivo → Activo)
✅ **FORTALEZA:** Registro de litraje por fermentador
✅ **FORTALEZA:** Historial de estados (Fermentación/Maduración)

**Observaciones:**
- En `inventario-de-productos.php:5-21` se distingue entre:
  - Batches en Fermentación (línea 5)
  - Batches en Maduración (línea 11)
  - Batches en Maduración en tanques Inox (línea 17)
- Sistema permite traspasos entre fermentadores (`BatchTraspaso`)

---

### 2.2 FASE 2: Envasado (Activos → Barriles)

#### Vista Principal: `inventario-de-productos.php`, `detalle-barriles.php`
#### Clases Involucradas: `Barril`, `BatchActivo`, `BarrilEstado`

**Flujo Detallado:**

1. **Llenado de Barriles**
   - Proceso iniciado en modal "Llenar Barril" (`inventario-de-productos.php:341-416`)
   - Inputs:
     - `id_batches_activos`: Fermentador origen
     - `id_barriles`: Barril a llenar
     - `cantidad_a_cargar`: Litros a transferir

2. **Actualización del Barril**
   - El `Barril` actualiza sus campos:
     - `id_batches`: Batch de origen
     - `id_activos`: Fermentador de origen
     - `id_batches_activos`: Registro BatchActivo específico
     - `litros_cargados`: Cantidad cargada
     - `estado`: 'En planta'

3. **Registro de Estado**
   - Se crea un `BarrilEstado` automáticamente (ver `Barril.php:52-73`)
   - Registra:
     - `id_barriles`: Barril afectado
     - `estado`: Nuevo estado
     - `inicio_date`: Timestamp del cambio
     - `id_usuarios`: Quien ejecutó el cambio

**Trazabilidad en esta fase:**
✅ **FORTALEZA:** Relación directa Barril → Batch → Activo
✅ **FORTALEZA:** Historial completo de estados con timestamps
✅ **FORTALEZA:** Trazabilidad de usuario ejecutor
⚠️ **DEBILIDAD:** No se registra el litraje específico traspasado si es carga parcial

**Análisis del Modal de Llenado:**
```javascript
// inventario-de-productos.php:742-761
var data = {
    'id_batches_activos': $('#llenar-barriles_id_batches_activos-select').val(),
    'id_barriles': $('#llenar-barriles_id_barriles-select').val(),
    'cantidad_a_cargar': $('#llenar-barriles-cantidad-a-cargar').val()
};
```
El sistema permite cargas parciales, pero **NO registra el litraje específico** en la entidad Barril. Solo actualiza `litros_cargados`.

---

### 2.3 FASE 3: Distribución (Barriles → Despachos)

#### Vista Principal: `central-despacho.php`
#### Clases Involucradas: `Despacho`, `DespachoProducto`, `Barril`

**Flujo Detallado:**

1. **Creación de Despacho**
   - Se crea un `Despacho` con:
     - `id_usuarios_repartidor`: Repartidor asignado
     - `estado`: 'En despacho'
     - `creada`: Timestamp

2. **Asignación de Productos al Despacho**
   - Se crean registros `DespachoProducto` con:
     - `id_despachos`: Despacho padre
     - `tipo`: 'Barril' | 'Caja' | 'Vasos'
     - `cantidad`: Cantidad de productos
     - `tipos_cerveza`: Tipo de cerveza
     - `codigo`: Código del barril
     - `id_barriles`: Referencia al barril específico
     - `id_productos`: Referencia al producto (si aplica)
     - `clasificacion`: Cerveza/Kombucha/etc

3. **Cambio de Estado del Barril**
   - El `Barril` cambia su estado:
     - De: 'En planta'
     - A: 'En despacho'
   - Se registra automáticamente en `BarrilEstado`

**Trazabilidad en esta fase:**
✅ **FORTALEZA:** Vinculación Despacho → DespachoProducto → Barril
✅ **FORTALEZA:** Registro de repartidor responsable
✅ **FORTALEZA:** Timestamp de creación del despacho
⚠️ **DEBILIDAD:** No hay campo `id_clientes` en Despacho (no se sabe el destino)
⚠️ **DEBILIDAD:** No hay relación directa Despacho → Cliente

**Observación Crítica:**
En `central-despacho.php:61-114`, los despachos se listan pero **NO muestran el cliente de destino**. Esto indica una debilidad en el modelo: el despacho no tiene información de hacia dónde va.

---

### 2.4 FASE 4: Entrega (Despachos → Entregas → Clientes)

#### Vista Principal: `repartidor.php`
#### Clases Involucradas: `Entrega`, `EntregaProducto`, `Barril`, `Cliente`

**Flujo Detallado:**

1. **Selección de Cliente**
   - Repartidor selecciona cliente destino (`repartidor.php:23-33`)
   - Se muestran barriles actuales del cliente

2. **Actualización de Estado de Barriles del Cliente**
   - Para cada barril en terreno del cliente, se actualiza:
     - 'En terreno': Barril activo en el cliente
     - 'Pinchado': Barril consumido
     - 'Perdido': Barril extraviado
     - 'Devuelto a planta': Barril retornado

3. **Creación de Entrega**
   - Se crea una `Entrega` con:
     - `id_clientes`: Cliente destino
     - `id_usuarios_repartidor`: Repartidor
     - `receptor_nombre`: Quien recibe
     - `rand_int`: Identificador único de sesión
     - `creada`: Timestamp

4. **Registro de Productos Entregados**
   - Se crean registros `EntregaProducto` con:
     - `id_entregas`: Entrega padre
     - `id_barriles`: Barril entregado
     - `id_despachos_productos`: Producto del despacho origen
     - `tipo`: Tipo de producto
     - `cantidad`: Cantidad entregada
     - `codigo`: Código del barril
     - `monto`: Monto facturado

5. **Actualización Final del Barril**
   - El `Barril` actualiza:
     - `estado`: 'En terreno'
     - `id_clientes`: Cliente actual
   - Se registra en `BarrilEstado`

**Trazabilidad en esta fase:**
✅ **FORTALEZA:** Vinculación completa Entrega → EntregaProducto → Barril → Cliente
✅ **FORTALEZA:** Registro del receptor físico
✅ **FORTALEZA:** Actualización de estados de barriles previos del cliente
✅ **FORTALEZA:** Conexión con DespachoProducto origen
⚠️ **DEBILIDAD:** Campo `rand_int` no documentado claramente (¿sesión de entrega?)
⚠️ **OBSERVACIÓN:** No hay validación de que los barriles del despacho coincidan con la entrega

**Análisis del Flujo de Entrega:**
```javascript
// repartidor.php:308-331
var data = {
    'ids_despachos_productos': ids_despachos_productos,
    'id_clientes': $('#id_clientes-select').val(),
    'id_usuarios_repartidor': <?= $usuario->id; ?>,
    'cantidad_vasos': $('#cantidad-vasos-select').val(),
    'receptor_nombre': $('#receptor-input').val(),
    'rand_int': <?= $rand_int; ?>,
    'barriles_estado': getDataForm('barriles')
};
```

El sistema actualiza **primero** los estados de los barriles existentes del cliente, **luego** entrega los nuevos. Esto garantiza trazabilidad completa del ciclo de vida de cada barril.

---

### 2.5 Diagrama de Flujo Completo

```
┌─────────────────────────────────────────────────────────────────┐
│                     FLUJO DE TRAZABILIDAD                        │
└─────────────────────────────────────────────────────────────────┘

[PRODUCCIÓN]
    Batch #123 (500L)
        ↓ crea
    BatchActivo (Fermentación)
        ↓ vincula
    Activo "Fermentador BD-01" (500L)
        ↓ tiempo
    BatchActivo (Maduración)
        ↓

[ENVASADO]
    Modal "Llenar Barril"
        ↓ selecciona
    BatchActivo + Barril disponible
        ↓ transfiere
    Barril "BC-001"
        - id_batches: 123
        - id_activos: BD-01
        - litros_cargados: 50L
        - estado: 'En planta'
        ↓ registra
    BarrilEstado (historial)
        ↓

[DISTRIBUCIÓN]
    Despacho #45
        ↓ crea
    DespachoProducto
        - id_despachos: 45
        - id_barriles: BC-001
        - tipo: 'Barril'
        - codigo: 'BC-001'
        ↓ cambia estado
    Barril "BC-001"
        - estado: 'En despacho'
        ↓

[ENTREGA]
    Repartidor selecciona Cliente "Restaurant XYZ"
        ↓ actualiza barriles previos
    Barriles en Cliente
        - BC-000: 'Pinchado'
        - BC-002: 'En terreno'
        ↓ crea
    Entrega #78
        - id_clientes: XYZ
        - receptor_nombre: "Juan Pérez"
        ↓ crea
    EntregaProducto
        - id_entregas: 78
        - id_barriles: BC-001
        - id_despachos_productos: 152
        ↓ actualiza
    Barril "BC-001"
        - estado: 'En terreno'
        - id_clientes: XYZ
        ↓ registra
    BarrilEstado (historial)

[TRAZABILIDAD COMPLETA]
Barril "BC-001" →
    Entrega #78 (Cliente XYZ, 15:30 hrs) →
    Despacho #45 (Repartidor: Pedro) →
    BatchActivo (Fermentador BD-01, Maduración) →
    Batch #123 (Receta: IPA, 500L, 01/11/2025)
```

---

## 3. Análisis de Trazabilidad por Entidad

### 3.1 Entidad: Batch

**Campos de Trazabilidad:**
```php
public $batch_nombre;          // Identificador único
public $batch_date;            // Fecha de producción
public $id_recetas;            // Receta utilizada
public $batch_litros;          // Litros producidos
public $creada;                // Timestamp de creación
```

**Relaciones de Trazabilidad:**
- → `BatchInsumo`: Insumos utilizados (trazabilidad hacia atrás)
- → `BatchActivo`: Fermentadores utilizados
- → `Barril`: Barriles llenados (via id_batches)

**Capacidad de Rastreo:**
✅ Batch → Receta: SÍ
✅ Batch → Insumos: SÍ
✅ Batch → Fermentadores: SÍ
✅ Batch → Barriles: SÍ
✅ Batch → Clientes: SÍ (via Barril → Entrega → Cliente)

**Gaps Identificados:**
⚠️ No hay campo para rastrear pérdidas durante el proceso
⚠️ No hay campo para rastrear merma o desperdicio

---

### 3.2 Entidad: Activo (Fermentador)

**Campos de Trazabilidad:**
```php
public $codigo;                // Identificador único (ej: "BD-01")
public $id_batches;            // Batch actual en el fermentador
public $litraje;               // Capacidad del fermentador
public $clase;                 // Tipo de activo
public $ubicacion;             // Ubicación física
```

**Relaciones de Trazabilidad:**
- ← `BatchActivo`: Batches que han pasado por este fermentador
- → `Barril`: Barriles llenados desde este fermentador

**Capacidad de Rastreo:**
✅ Activo → Batch actual: SÍ
✅ Activo → Historial de batches: SÍ (via BatchActivo)
✅ Activo → Barriles llenados: SÍ
⚠️ Activo → Litraje actual disponible: PARCIAL (solo en BatchActivo)

**Gaps Identificados:**
⚠️ No hay registro de limpieza/sanitización del fermentador
⚠️ No hay registro de temperatura en tiempo real (solo en Batch)

---

### 3.3 Entidad: Barril

**Campos de Trazabilidad:**
```php
public $codigo;                // Código único del barril
public $id_batches;            // Batch de origen
public $id_activos;            // Fermentador de origen
public $id_batches_activos;    // BatchActivo específico
public $id_clientes;           // Cliente actual
public $estado;                // Estado actual
public $litros_cargados;       // Litros cargados
public $litraje;               // Capacidad del barril
public $tipo_barril;           // 20L, 30L, 50L
public $clasificacion;         // Cerveza/Kombucha/etc
```

**Relaciones de Trazabilidad:**
- → `Batch`: Batch de origen
- → `Activo`: Fermentador de origen
- → `BarrilEstado`: Historial completo de estados
- → `EntregaProducto`: Entregas realizadas
- → `Cliente`: Cliente actual

**Capacidad de Rastreo:**
✅ Barril → Batch: SÍ
✅ Barril → Fermentador: SÍ
✅ Barril → Historial de estados: SÍ
✅ Barril → Historial de clientes: SÍ (via BarrilEstado)
✅ Barril → Historial de entregas: SÍ
✅ Barril → Ubicación actual: SÍ

**Fortalezas:**
🌟 **EXCELENTE:** Triple vinculación (Batch, Activo, BatchActivo)
🌟 **EXCELENTE:** Historial completo con `BarrilEstado`
🌟 **EXCELENTE:** Método `registrarCambioDeEstado()` automático

**Gaps Identificados:**
⚠️ `litros_cargados` no se actualiza cuando se consume parcialmente
⚠️ No hay fecha de caducidad o fecha de envasado explícita
⚠️ No hay campo para número de usos del barril (desgaste)

---

### 3.4 Entidad: BarrilEstado

**Campos de Trazabilidad:**
```php
public $id_barriles;           // Barril referenciado
public $estado;                // Estado registrado
public $id_clientes;           // Cliente asociado (si aplica)
public $inicio_date;           // Inicio del estado
public $finalizacion_date;     // Fin del estado
public $tiempo_transcurrido;   // Duración en el estado
public $id_usuarios;           // Usuario que ejecutó el cambio
```

**Capacidad de Rastreo:**
✅ Historial completo de todos los estados de un barril
✅ Duración en cada estado
✅ Quién ejecutó cada cambio
✅ Cliente asociado en cada momento

**Fortalezas:**
🌟 **EXCELENTE:** Auditoría completa de cambios de estado
🌟 **EXCELENTE:** Cálculo automático de `tiempo_transcurrido`

**Observación:**
Este sistema de historial permite responder preguntas como:
- ¿Cuánto tiempo estuvo un barril en terreno?
- ¿Quién movió un barril específico?
- ¿Cuándo llegó un barril a un cliente?
- ¿Cuántos días estuvo un barril en estado "Pinchado" antes de ser devuelto?

---

### 3.5 Entidad: Despacho

**Campos de Trazabilidad:**
```php
public $id_usuarios_repartidor; // Repartidor asignado
public $estado;                 // Estado del despacho
public $creada;                 // Timestamp
public $id_pedidos;             // Pedido asociado (opcional)
```

**Relaciones de Trazabilidad:**
- → `DespachoProducto`: Productos en el despacho
- → `Usuario` (repartidor): Responsable del despacho

**Capacidad de Rastreo:**
✅ Despacho → Repartidor: SÍ
✅ Despacho → Productos: SÍ
✅ Despacho → Barriles: SÍ (via DespachoProducto)
⚠️ Despacho → Cliente destino: NO (campo faltante)

**Gaps Identificados:**
❌ **CRÍTICO:** No hay campo `id_clientes` en Despacho
❌ **CRÍTICO:** No se puede saber el destino del despacho sin revisar las entregas
⚠️ No hay campo para ruta del despacho
⚠️ No hay campo para fecha/hora estimada de entrega

---

### 3.6 Entidad: Entrega

**Campos de Trazabilidad:**
```php
public $id_clientes;            // Cliente destino
public $id_usuarios_repartidor; // Repartidor
public $id_despachos;           // Despacho origen
public $receptor_nombre;        // Quien recibió físicamente
public $creada;                 // Timestamp
public $rand_int;               // Identificador de sesión
```

**Relaciones de Trazabilidad:**
- → `Cliente`: Cliente destino
- → `EntregaProducto`: Productos entregados
- → `Despacho`: Despacho origen
- → `Usuario` (repartidor): Responsable

**Capacidad de Rastreo:**
✅ Entrega → Cliente: SÍ
✅ Entrega → Despacho: SÍ
✅ Entrega → Repartidor: SÍ
✅ Entrega → Receptor físico: SÍ
✅ Entrega → Productos: SÍ
✅ Entrega → Barriles: SÍ (via EntregaProducto)

**Fortalezas:**
🌟 **EXCELENTE:** Registro del receptor físico (importante para auditoría)
🌟 **EXCELENTE:** Conexión con DespachoProducto (trazabilidad hacia atrás)

**Observación:**
Campo `rand_int` parece ser un identificador único de sesión de entrega. Permite agrupar múltiples entregas realizadas en la misma salida del repartidor.

---

## 4. Problemas Identificados

### 4.1 Problemas Críticos (Afectan Trazabilidad)

#### ❌ P1: Despacho sin Cliente Destino

**Descripción:**
La entidad `Despacho` no tiene campo `id_clientes`, lo que significa que un despacho no sabe hacia dónde va hasta que se crea la `Entrega`.

**Impacto:**
- No se puede planificar rutas por cliente
- No se puede rastrear qué barriles están en camino a qué cliente
- Si el despacho se pierde, no se sabe el destino

**Evidencia:**
- `Despacho.php:3-39` - No existe campo id_clientes
- `central-despacho.php:61-114` - Los despachos no muestran cliente destino

**Riesgo:** ALTO

**Solución Propuesta:** Ver sección 5.1

---

#### ⚠️ P2: Pérdida de Trazabilidad en Cargas Parciales

**Descripción:**
Cuando se carga un barril parcialmente desde un fermentador, se registra `litros_cargados` pero no se descuenta del `BatchActivo.litraje`.

**Impacto:**
- No se puede saber cuántos litros quedan disponibles en el fermentador
- Puede generar inconsistencias en inventario
- Dificulta la planificación de llenado

**Evidencia:**
- `inventario-de-productos.php:742-761` - Modal de llenado
- `Barril.php:1-92` - No actualiza BatchActivo

**Riesgo:** MEDIO

**Solución Propuesta:** Ver sección 5.2

---

#### ⚠️ P3: Sin Registro de Consumo Parcial de Barriles

**Descripción:**
Un barril de 50L que se entrega lleno no registra cuando se consume parcialmente en el cliente.

**Impacto:**
- No se puede saber si un barril está "casi vacío" vs "recién entregado"
- Dificulta la planificación de recambio
- No hay visibilidad del consumo real del cliente

**Evidencia:**
- `Barril.php:1-92` - Campo `litros_cargados` no se actualiza después de la entrega

**Riesgo:** BAJO-MEDIO

**Solución Propuesta:** Ver sección 5.3

---

### 4.2 Problemas de Integridad de Datos

#### ⚠️ P4: Validación Insuficiente entre Despacho y Entrega

**Descripción:**
No hay validación de que los barriles de un despacho coincidan con los entregados en la entrega.

**Impacto:**
- Puede crearse una entrega sin relación con el despacho
- Barriles pueden "desaparecer" del sistema
- Dificulta auditorías

**Evidencia:**
- `repartidor.php:300-333` - No valida coincidencia con despacho

**Riesgo:** MEDIO

---

#### ⚠️ P5: Barriles Sin Fecha de Envasado Explícita

**Descripción:**
No hay campo `fecha_envasado` en `Barril`, solo se puede inferir del `creada` del `BarrilEstado`.

**Impacto:**
- Dificulta cálculo de caducidad
- Complicado para cumplir regulaciones sanitarias
- No se puede implementar FIFO automático

**Evidencia:**
- `Barril.php:1-92` - No existe campo fecha_envasado

**Riesgo:** MEDIO

---

### 4.3 Problemas de Usabilidad y Eficiencia

#### ℹ️ P6: No Hay Vista Consolidada de Trazabilidad

**Descripción:**
Para rastrear un barril completo hay que navegar por múltiples vistas.

**Impacto:**
- Tiempo excesivo para auditorías
- Difícil presentar trazabilidad a clientes
- Complicado para resolver reclamaciones

**Solución Propuesta:** Ver sección 5.4

---

#### ℹ️ P7: Estados de Barril No Estandarizados

**Descripción:**
Los estados posibles de un barril están hardcodeados en diferentes lugares:
- `repartidor.php:202-208`: 'En terreno', 'Pinchado', 'Perdido', 'Devuelto a planta'
- `detalle-barriles.php:95,102-105`: 'En planta', 'Perdido'

**Impacto:**
- Posibles inconsistencias
- Dificulta reportes
- Complicado agregar nuevos estados

**Riesgo:** BAJO

---

### 4.4 Problemas de Escalabilidad

#### ℹ️ P8: Sin Índices Documentados para Queries de Trazabilidad

**Descripción:**
Las consultas de trazabilidad pueden volverse lentas con muchos registros.

**Ejemplo de Query Problemático:**
```sql
-- En detalle-barriles.php se buscan todas las entregas de un barril
SELECT * FROM entregas_productos WHERE id_barriles='BC-001' ORDER BY id desc;
```

**Impacto:**
- Queries lentas con muchos registros históricos
- Posibles timeouts en producción

**Solución:** Índices en:
- `entregas_productos.id_barriles`
- `barriles_estados.id_barriles`
- `despachos_productos.id_barriles`
- `barriles.id_batches`

---

## 5. Soluciones Propuestas

### 5.1 Solución a P1: Agregar Cliente Destino a Despacho

#### Implementación

**1. Modificación de Base de Datos:**
```sql
ALTER TABLE despachos
ADD COLUMN id_clientes INT DEFAULT 0 AFTER id_usuarios_repartidor,
ADD INDEX idx_id_clientes (id_clientes);
```

**2. Modificación de Clase `Despacho`:**
```php
// php/classes/Despacho.php
class Despacho extends Base {
    public $id_usuarios_repartidor;
    public $id_clientes = 0;  // NUEVO CAMPO
    public $tipo_de_entrega;
    public $estado = "En despacho";
    // ... resto de campos
}
```

**3. Modificación de Vista `central-despacho.php`:**
```php
// Agregar selector de cliente en el formulario de nuevo despacho
<div class="col-6 mb-1">
    Cliente Destino:
</div>
<div class="col-6 mb-1">
    <select name="id_clientes" class="form-control" required>
        <?php foreach($clientes as $cliente) { ?>
            <option value="<?= $cliente->id; ?>"><?= $cliente->nombre; ?></option>
        <?php } ?>
    </select>
</div>
```

**4. Actualización de Listado:**
```php
// central-despacho.php:61-114
foreach($despachos as $despacho) {
    $repartidor = new Usuario($despacho->id_usuarios_repartidor);
    $cliente = new Cliente($despacho->id_clientes); // NUEVO
    $productos = DespachoProducto::getAll("WHERE id_despachos='".$despacho->id."'");
?>
<div class="card w-100 shadow mb-5">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3 mb-1">
                <h5><i class="fas fa-fw fa-truck"></i> DESPACHO #<?= $despacho->id; ?></h5>
            </div>
            <div class="col-md-3 mb-1">
                Cliente: <?= $cliente->nombre; ?> <!-- NUEVO -->
            </div>
            <div class="col-md-3 mb-1">
                Repartidor: <?= $repartidor->nombre; ?>
            </div>
            <div class="col-md-3 mb-1">
                Creado: <?= datetime2fechayhora($despacho->creada); ?>
            </div>
        </div>
        <!-- resto del código -->
    </div>
</div>
<?php } ?>
```

#### Beneficios

✅ Trazabilidad completa desde creación del despacho
✅ Permite planificación de rutas por cliente
✅ Facilita reportes de despachos por cliente
✅ Mejora auditoría de entregas

#### Impacto en el Sistema

**Componentes afectados:**
- ✏️ `php/classes/Despacho.php` - Agregar campo
- ✏️ `templates/central-despacho.php` - Agregar selector y columna
- ✏️ `templates/nuevo-despachos.php` (si existe) - Agregar campo
- ✏️ `ajax/ajax_guardarDespacho.php` - Manejar nuevo campo

**Riesgo:** BAJO
**Esfuerzo:** 2-4 horas
**Prioridad:** ALTA

---

### 5.2 Solución a P2: Actualizar BatchActivo al Llenar Barriles

#### Implementación

**1. Modificación de AJAX `ajax_llenarBarriles.php`:**
```php
<?php
require_once("../php/app.php");

$id_batches_activos = $_POST['id_batches_activos'];
$id_barriles = $_POST['id_barriles'];
$cantidad_a_cargar = floatval($_POST['cantidad_a_cargar']);

// Obtener BatchActivo
$batch_activo = new BatchActivo($id_batches_activos);
$barril = new Barril($id_barriles);

// Validar que hay suficiente líquido
if($batch_activo->litraje < $cantidad_a_cargar) {
    $response['status'] = 'ERROR';
    $response['mensaje'] = 'No hay suficiente líquido en el fermentador';
    echo json_encode($response);
    exit;
}

// ACTUALIZAR BATCH ACTIVO (NUEVO)
$batch_activo->litraje -= $cantidad_a_cargar;
$batch_activo->save();

// Actualizar Barril
$barril->id_batches = $batch_activo->id_batches;
$barril->id_activos = $batch_activo->id_activos;
$barril->id_batches_activos = $batch_activo->id;
$barril->litros_cargados += $cantidad_a_cargar;

// Si el barril se llenó completamente, cambiar estado
if($barril->litros_cargados >= $barril->litraje) {
    $barril->estado = 'En planta';
}

$barril->save();

// Si el fermentador quedó vacío, liberar el activo
if($batch_activo->litraje <= 0) {
    $activo = new Activo($batch_activo->id_activos);
    $activo->id_batches = 0;
    $activo->save();
}

$response['status'] = 'OK';
$response['mensaje'] = 'Barril llenado correctamente';
$response['batch_activo_litraje_restante'] = $batch_activo->litraje;
echo json_encode($response);
?>
```

**2. Actualización de Vista para Mostrar Disponible:**
```javascript
// inventario-de-productos.php:736-740
function renderLlenarBarrilesFermentadores() {
    const bam = batches_activos_maduracion.find((b) => b.id == $('#llenar-barriles_id_batches_activos-select').val());
    $('#llenar-barriles-fermentador-cantidad-disponible').val(bam.litraje); // Ya actualizado por AJAX
}
```

#### Beneficios

✅ Inventario preciso en tiempo real
✅ Previene sobrellenado de barriles
✅ Permite planificación exacta de envasado
✅ Mejora trazabilidad de volumen

#### Impacto en el Sistema

**Componentes afectados:**
- ✏️ `ajax/ajax_llenarBarriles.php` - Lógica de descuento
- ✏️ `templates/inventario-de-productos.php` - Actualización en tiempo real

**Riesgo:** MEDIO (requiere pruebas exhaustivas)
**Esfuerzo:** 3-5 horas
**Prioridad:** ALTA

---

### 5.3 Solución a P3: Registro de Consumo Parcial

#### Implementación

**1. Nueva Tabla `barriles_consumos`:**
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

**2. Nueva Clase `BarrilConsumo`:**
```php
<?php
class BarrilConsumo extends Base {
    public $id_barriles = 0;
    public $id_clientes = 0;
    public $litros_consumidos = 0;
    public $litros_restantes = 0;
    public $fecha_consumo;
    public $observaciones = '';

    public function __construct($id = null) {
        $this->tableName("barriles_consumos");
        if($id) {
            $this->id = $id;
            $info = $this->getInfoDatabase('id');
            $this->setProperties($info);
        } else {
            $this->fecha_consumo = date('Y-m-d H:i:s');
        }
    }
}
?>
```

**3. Vista para Registrar Consumo (en perfil Cliente):**
```php
// Nuevo modal en detalle-clientes.php o repartidor.php
<div class="modal fade" id="registrar-consumo-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Registrar Consumo de Barril</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-2">Barril:</div>
                    <div class="col-6 mb-2">
                        <input type="text" id="consumo-barril-codigo" readonly>
                    </div>
                    <div class="col-6 mb-2">Litros Actuales:</div>
                    <div class="col-6 mb-2">
                        <input type="number" id="consumo-litros-actuales" readonly>
                    </div>
                    <div class="col-6 mb-2">Litros Consumidos:</div>
                    <div class="col-6 mb-2">
                        <input type="number" id="consumo-litros-consumidos" step="0.1">
                    </div>
                    <div class="col-6 mb-2">Litros Restantes:</div>
                    <div class="col-6 mb-2">
                        <input type="number" id="consumo-litros-restantes" readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="guardar-consumo-btn">Guardar</button>
            </div>
        </div>
    </div>
</div>
```

**4. Integración con Vista de Cliente:**
```javascript
// Calcular automáticamente litros restantes
$('#consumo-litros-consumidos').on('input', function() {
    var actuales = parseFloat($('#consumo-litros-actuales').val());
    var consumidos = parseFloat($(this).val());
    var restantes = actuales - consumidos;
    $('#consumo-litros-restantes').val(restantes.toFixed(2));
});

// Guardar consumo
$('#guardar-consumo-btn').on('click', function() {
    var data = {
        'entidad': 'barriles_consumos',
        'id_barriles': idBarrilSeleccionado,
        'id_clientes': idClienteActual,
        'litros_consumidos': $('#consumo-litros-consumidos').val(),
        'litros_restantes': $('#consumo-litros-restantes').val()
    };

    $.post('./ajax/ajax_guardarEntidad.php', data, function(response) {
        // Actualizar vista
        location.reload();
    });
});
```

#### Beneficios

✅ Visibilidad del consumo real
✅ Planificación proactiva de recambio
✅ Análisis de patrones de consumo por cliente
✅ Base para sistema de predicción

#### Impacto en el Sistema

**Componentes afectados:**
- 🆕 Nueva tabla `barriles_consumos`
- 🆕 Nueva clase `BarrilConsumo`
- ✏️ `templates/detalle-clientes.php` - Agregar modal
- ✏️ `templates/repartidor.php` - Integración

**Riesgo:** BAJO (feature independiente)
**Esfuerzo:** 5-8 horas
**Prioridad:** MEDIA

---

### 5.4 Solución a P6: Vista Consolidada de Trazabilidad

#### Implementación

**1. Nueva Vista `detalle-trazabilidad-barril.php`:**
```php
<?php
if(!validaIdExists($_GET,'id')) {
    die('ID de barril requerido');
}

$barril = new Barril($_GET['id']);
$batch = new Batch($barril->id_batches);
$receta = new Receta($batch->id_recetas);
$activo = new Activo($barril->id_activos);
$batch_activo = new BatchActivo($barril->id_batches_activos);

// Historial de estados
$historial_estados = BarrilEstado::getAll("WHERE id_barriles='".$barril->id."' ORDER BY inicio_date desc");

// Historial de entregas
$entregas_productos = EntregaProducto::getAll("WHERE id_barriles='".$barril->id."' ORDER BY creada desc");

// Insumos del batch
$batch_insumos = BatchInsumo::getAll("WHERE id_batches='".$batch->id."'");
?>

<div class="container-fluid">
    <h1><i class="fas fa-search"></i> Trazabilidad Completa: Barril <?= $barril->codigo; ?></h1>
    <hr>

    <!-- TIMELINE DE TRAZABILIDAD -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h3>Línea de Tiempo</h3>
        </div>
        <div class="card-body">
            <div class="timeline">

                <!-- PASO 1: PRODUCCIÓN -->
                <div class="timeline-item">
                    <div class="timeline-marker bg-success"></div>
                    <div class="timeline-content">
                        <h4><i class="fas fa-beer"></i> Producción</h4>
                        <p><strong>Batch:</strong> #<?= $batch->batch_nombre; ?> (<?= $batch->batch_litros; ?>L)</p>
                        <p><strong>Receta:</strong> <?= $receta->nombre; ?> (<?= $receta->clasificacion; ?>)</p>
                        <p><strong>Fecha:</strong> <?= datetime2fechayhora($batch->creada); ?></p>

                        <details>
                            <summary>Ver Insumos Utilizados</summary>
                            <table class="table table-sm">
                                <thead><tr><th>Insumo</th><th>Cantidad</th><th>Etapa</th></tr></thead>
                                <tbody>
                                    <?php foreach($batch_insumos as $bi) {
                                        $insumo = new Insumo($bi->id_insumos);
                                    ?>
                                    <tr>
                                        <td><?= $insumo->nombre; ?></td>
                                        <td><?= $bi->cantidad; ?> <?= $insumo->unidad_de_medida; ?></td>
                                        <td><?= $bi->etapa; ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </details>
                    </div>
                </div>

                <!-- PASO 2: FERMENTACIÓN -->
                <div class="timeline-item">
                    <div class="timeline-marker bg-info"></div>
                    <div class="timeline-content">
                        <h4><i class="fas fa-flask"></i> Fermentación</h4>
                        <p><strong>Fermentador:</strong> <?= $activo->codigo; ?> (<?= $activo->nombre; ?>)</p>
                        <p><strong>Estado:</strong> <?= $batch_activo->estado; ?></p>
                        <p><strong>Litraje procesado:</strong> <?= $batch_activo->litraje; ?>L</p>
                        <p><strong>Fecha:</strong> <?= datetime2fechayhora($batch_activo->creada); ?></p>
                    </div>
                </div>

                <!-- PASO 3: ENVASADO -->
                <div class="timeline-item">
                    <div class="timeline-marker bg-warning"></div>
                    <div class="timeline-content">
                        <h4><i class="fas fa-keg"></i> Envasado</h4>
                        <p><strong>Barril:</strong> <?= $barril->codigo; ?> (<?= $barril->tipo_barril; ?>)</p>
                        <p><strong>Litros cargados:</strong> <?= $barril->litros_cargados; ?>L de <?= $barril->litraje; ?>L</p>
                        <p><strong>Fecha:</strong> <?= datetime2fechayhora($barril->creada); ?></p>
                    </div>
                </div>

                <!-- PASO 4: ENTREGAS -->
                <?php foreach($entregas_productos as $ep) {
                    $entrega = new Entrega($ep->id_entregas);
                    $cliente = new Cliente($entrega->id_clientes);
                    $repartidor = new Usuario($entrega->id_usuarios_repartidor);
                ?>
                <div class="timeline-item">
                    <div class="timeline-marker bg-danger"></div>
                    <div class="timeline-content">
                        <h4><i class="fas fa-truck"></i> Entrega #<?= $entrega->id; ?></h4>
                        <p><strong>Cliente:</strong> <?= $cliente->nombre; ?></p>
                        <p><strong>Repartidor:</strong> <?= $repartidor->nombre; ?></p>
                        <p><strong>Receptor:</strong> <?= $entrega->receptor_nombre; ?></p>
                        <p><strong>Fecha:</strong> <?= datetime2fechayhora($entrega->creada); ?></p>
                    </div>
                </div>
                <?php } ?>

                <!-- ESTADO ACTUAL -->
                <div class="timeline-item">
                    <div class="timeline-marker bg-dark"></div>
                    <div class="timeline-content">
                        <h4><i class="fas fa-info-circle"></i> Estado Actual</h4>
                        <p><strong>Estado:</strong> <span class="badge bg-<?= $barril->estado == 'En planta' ? 'success' : 'warning'; ?>"><?= $barril->estado; ?></span></p>
                        <?php if($barril->id_clientes != 0) {
                            $cliente_actual = new Cliente($barril->id_clientes);
                        ?>
                        <p><strong>Cliente actual:</strong> <?= $cliente_actual->nombre; ?></p>
                        <?php } ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- HISTORIAL DE ESTADOS DETALLADO -->
    <div class="card">
        <div class="card-header">
            <h3>Historial Completo de Estados</h3>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Estado</th>
                        <th>Cliente</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Duración</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($historial_estados as $estado) {
                        $usuario_ejecutor = new Usuario($estado->id_usuarios);
                        $cliente = $estado->id_clientes != 0 ? new Cliente($estado->id_clientes) : null;
                    ?>
                    <tr>
                        <td><?= $estado->estado; ?></td>
                        <td><?= $cliente ? $cliente->nombre : '-'; ?></td>
                        <td><?= datetime2fechayhora($estado->inicio_date); ?></td>
                        <td><?= $estado->finalizacion_date != '0000-00-00 00:00:00' ? datetime2fechayhora($estado->finalizacion_date) : 'Actualidad'; ?></td>
                        <td><?= $estado->tiempo_transcurrido ? $estado->tiempo_transcurrido : 'En curso'; ?></td>
                        <td><?= $usuario_ejecutor->nombre; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 40px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}
.timeline-item {
    position: relative;
    margin-bottom: 30px;
}
.timeline-marker {
    position: absolute;
    left: -28px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
}
.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}
</style>
```

**2. Agregar Enlace desde `detalle-barriles.php`:**
```php
// En el header de detalle-barriles.php
<div>
    <a href="./?s=detalle-trazabilidad-barril&id=<?= $obj->id; ?>"
       class="btn btn-info btn-sm">
        <i class="fas fa-search"></i> Ver Trazabilidad Completa
    </a>
    <?php $usuario->printReturnBtn(); ?>
</div>
```

**3. Actualizar Router en `index.php`:**
```php
// En la función switch_templates()
case 'detalle-trazabilidad-barril':
    incluir_template('detalle-trazabilidad-barril');
    break;
```

#### Beneficios

✅ Vista unificada de toda la trazabilidad
✅ Fácil presentación a clientes
✅ Rápida resolución de reclamaciones
✅ Auditorías simplificadas
✅ Exportable a PDF para certificaciones

#### Impacto en el Sistema

**Componentes afectados:**
- 🆕 `templates/detalle-trazabilidad-barril.php` - Nueva vista
- ✏️ `templates/detalle-barriles.php` - Agregar enlace
- ✏️ `index.php` - Agregar ruta

**Riesgo:** BAJO
**Esfuerzo:** 6-10 horas
**Prioridad:** MEDIA-ALTA

---

### 5.5 Implementación de Códigos QR para Trazabilidad Rápida

#### Concepto

Generar códigos QR únicos para cada barril que permitan acceso instantáneo a la vista de trazabilidad completa.

#### Implementación

**1. Librería PHP para QR:**
```bash
# Instalación vía Composer
composer require endroid/qr-code
```

**2. Generar QR al crear/editar Barril:**
```php
// php/classes/Barril.php - Agregar método
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

public function generarQR() {
    $url = "https://app.barril.cl/?s=detalle-trazabilidad-barril&id=".$this->id;

    $qr_code = QrCode::create($url)
        ->setSize(300)
        ->setMargin(10);

    $writer = new PngWriter();
    $result = $writer->write($qr_code);

    // Guardar en media
    $path = $GLOBALS['base_dir']."/media/qr/barril_".$this->codigo.".png";
    $result->saveToFile($path);

    return $path;
}

public function setSpecifics($post) {
    $this->registrarCambioDeEstado();

    // Generar QR si es nuevo barril
    if($this->id != '') {
        $this->generarQR();
    }
}
```

**3. Mostrar QR en Detalle de Barril:**
```php
// templates/detalle-barriles.php - Agregar sección
<div class="col-md-6 mb-3">
    <div class="card">
        <div class="card-header">
            <h5>Código QR de Trazabilidad</h5>
        </div>
        <div class="card-body text-center">
            <?php if(file_exists($GLOBALS['base_dir']."/media/qr/barril_".$obj->codigo.".png")) { ?>
                <img src="./media/qr/barril_<?= $obj->codigo; ?>.png" width="200">
                <p class="mt-2">Escanea para ver trazabilidad completa</p>
                <a href="./media/qr/barril_<?= $obj->codigo; ?>.png"
                   download="QR_Barril_<?= $obj->codigo; ?>.png"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-download"></i> Descargar QR
                </a>
            <?php } else { ?>
                <button class="btn btn-success" onclick="generarQR(<?= $obj->id; ?>)">
                    <i class="fas fa-qrcode"></i> Generar Código QR
                </button>
            <?php } ?>
        </div>
    </div>
</div>
```

**4. AJAX para generar QR on-demand:**
```php
// ajax/ajax_generarQRBarril.php
<?php
require_once("../php/app.php");

$id_barriles = $_POST['id_barriles'];
$barril = new Barril($id_barriles);

$path = $barril->generarQR();

$response['status'] = 'OK';
$response['qr_path'] = str_replace($GLOBALS['base_dir'], '.', $path);
echo json_encode($response);
?>
```

#### Beneficios

✅ Acceso instantáneo a trazabilidad desde smartphone
✅ Útil para auditorías in-situ
✅ Presentación profesional a clientes
✅ Automatización de verificaciones

#### Caso de Uso

Un inspector de calidad escanea el QR de un barril en el cliente y ve inmediatamente:
- Receta y batch de origen
- Fecha de envasado
- Insumos utilizados
- Historial completo de movimientos
- Cliente actual

---

## 6. Expansión a Formato Latas

### 6.1 Análisis de Viabilidad

La expansión del sistema para soportar envasado en **latas** (además de barriles) es **COMPLETAMENTE VIABLE** con el sistema actual, pero requiere adaptaciones significativas en el flujo de trazabilidad.

### 6.2 Diferencias Clave: Barriles vs Latas

| Aspecto | Barriles | Latas |
|---------|----------|-------|
| **Unidad de trazabilidad** | Individual (cada barril tiene ID único) | Lote/Batch (latas se rastrean por lote, no individual) |
| **Reutilización** | Sí (retornable) | No (desechable) |
| **Volumen** | 20L, 30L, 50L | 350ml, 473ml, 500ml |
| **Cantidad por batch** | 10-20 barriles | 500-2000 latas |
| **Trazabilidad requerida** | Individual hasta cliente | Por lote hasta distribución |
| **Control de inventario** | Por unidad | Por cantidad |
| **Estados posibles** | En planta, En despacho, En terreno, Pinchado, Perdido, Devuelto | Producidas, En inventario, Despachadas, Vendidas |
| **Fecha de caducidad** | Implícita (por fecha envasado) | Explícita (impresa en lata) |

### 6.3 Modelo Propuesto para Latas

#### Arquitectura de Dos Niveles

```
NIVEL 1: Lote de Latas (Batch → LoteEnvasado)
    ↓
NIVEL 2: Inventario de Latas (Cantidad, no individual)
```

**Filosofía:**
Las latas **NO se rastrean individualmente**, sino por **lotes de envasado**. Un lote agrupa todas las latas producidas de un mismo batch en una sesión de envasado.

### 6.4 Implementación Técnica

#### 6.4.1 Nueva Tabla: `lotes_envasados`

```sql
CREATE TABLE lotes_envasados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,           -- Ej: "LE-2025-001"
    id_batches INT NOT NULL,                      -- Batch origen
    id_activos INT NOT NULL,                      -- Fermentador origen
    id_batches_activos INT NOT NULL,              -- BatchActivo específico

    -- Información del envasado
    tipo_envase VARCHAR(20) NOT NULL,             -- 'Lata 350ml', 'Lata 473ml', 'Botella 330ml'
    cantidad_envasada INT NOT NULL,               -- Cantidad de latas producidas
    litros_utilizados DECIMAL(10,2) NOT NULL,     -- Litros del fermentador utilizados

    -- Fechas importantes
    fecha_envasado DATETIME NOT NULL,
    fecha_caducidad DATE NOT NULL,                -- Calculada automáticamente
    lote_produccion VARCHAR(50),                  -- Para etiqueta (ej: "LOT25A001")

    -- Control de inventario
    cantidad_disponible INT NOT NULL,             -- Cantidad actual en inventario
    cantidad_despachada INT DEFAULT 0,            -- Cantidad despachada
    cantidad_vendida INT DEFAULT 0,               -- Cantidad vendida (facturada)
    cantidad_merma INT DEFAULT 0,                 -- Latas dañadas/perdidas

    -- Ubicación
    ubicacion VARCHAR(100) DEFAULT 'Bodega',      -- Donde está el lote

    creada DATETIME NOT NULL,
    actualizada DATETIME,

    INDEX idx_id_batches (id_batches),
    INDEX idx_codigo (codigo),
    INDEX idx_fecha_envasado (fecha_envasado),
    INDEX idx_fecha_caducidad (fecha_caducidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### 6.4.2 Nueva Clase: `LoteEnvasado`

```php
<?php
class LoteEnvasado extends Base {

    public $codigo = '';
    public $id_batches = 0;
    public $id_activos = 0;
    public $id_batches_activos = 0;

    public $tipo_envase = '';
    public $cantidad_envasada = 0;
    public $litros_utilizados = 0;

    public $fecha_envasado;
    public $fecha_caducidad;
    public $lote_produccion = '';

    public $cantidad_disponible = 0;
    public $cantidad_despachada = 0;
    public $cantidad_vendida = 0;
    public $cantidad_merma = 0;

    public $ubicacion = 'Bodega';

    public $creada;
    public $actualizada;

    public function __construct($id = null) {
        $this->tableName("lotes_envasados");
        if($id) {
            $this->id = $id;
            $info = $this->getInfoDatabase('id');
            $this->setProperties($info);
        } else {
            $this->creada = date('Y-m-d H:i:s');
            $this->fecha_envasado = date('Y-m-d H:i:s');
            $this->codigo = $this->generarCodigo();
            $this->lote_produccion = $this->generarLoteProduccion();
        }
    }

    /**
     * Genera código único para el lote
     * Formato: LE-YYYY-NNN (LE = Lote Envasado)
     */
    private function generarCodigo() {
        $anio = date('Y');

        // Obtener último lote del año
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
     * Genera código de lote para imprimir en etiqueta
     * Formato: LOTYYMDD# (LOT + Año + Mes + Día + Secuencia)
     */
    private function generarLoteProduccion() {
        $fecha = date('ymd'); // Ej: 251127 para 27 de Noviembre 2025

        // Obtener secuencia del día
        $lotes_hoy = self::getAll("WHERE DATE(fecha_envasado) = '".date('Y-m-d')."'");
        $secuencia = count($lotes_hoy) + 1;

        return "LOT".$fecha.chr(64 + $secuencia); // A, B, C...
    }

    /**
     * Calcula fecha de caducidad automáticamente
     * Cervezas artesanales: 6 meses desde envasado
     */
    public function calcularFechaCaducidad() {
        $fecha_envasado = new DateTime($this->fecha_envasado);
        $fecha_envasado->modify('+6 months');
        $this->fecha_caducidad = $fecha_envasado->format('Y-m-d');
    }

    /**
     * Registra el consumo de latas del lote
     */
    public function consumir($cantidad, $tipo = 'despacho') {
        if($cantidad > $this->cantidad_disponible) {
            throw new Exception("No hay suficientes latas disponibles en el lote");
        }

        $this->cantidad_disponible -= $cantidad;

        if($tipo == 'despacho') {
            $this->cantidad_despachada += $cantidad;
        } else if($tipo == 'venta') {
            $this->cantidad_vendida += $cantidad;
        } else if($tipo == 'merma') {
            $this->cantidad_merma += $cantidad;
        }

        $this->actualizada = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * Devuelve latas al inventario (ej: devolución)
     */
    public function devolver($cantidad) {
        $this->cantidad_disponible += $cantidad;
        $this->cantidad_despachada -= $cantidad;
        $this->actualizada = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * Verifica si el lote está próximo a caducar
     */
    public function proximoACaducar($dias = 30) {
        $hoy = new DateTime();
        $caducidad = new DateTime($this->fecha_caducidad);
        $diferencia = $hoy->diff($caducidad);

        return $diferencia->days <= $dias && $diferencia->invert == 0;
    }

    /**
     * Verifica si el lote está caducado
     */
    public function estaCaducado() {
        $hoy = new DateTime();
        $caducidad = new DateTime($this->fecha_caducidad);
        return $hoy > $caducidad;
    }
}
?>
```

#### 6.4.3 Nueva Vista: `nuevo-lote-envasado.php`

```php
<?php
$usuario = $GLOBALS['usuario'];

// Obtener fermentadores disponibles para envasar
$batches_activos_disponibles = BatchActivo::getAll("
    JOIN activos ON activos.id = batches_activos.id_activos
    WHERE batches_activos.litraje > 0
    AND (batches_activos.estado = 'Maduración' OR activos.codigo LIKE 'BD%')
    ORDER BY batches_activos.id_batches ASC
");

$tipos_envase = [
    'Lata 350ml' => 0.35,  // litros por unidad
    'Lata 473ml' => 0.473,
    'Lata 500ml' => 0.50,
    'Botella 330ml' => 0.33,
    'Botella 500ml' => 0.50
];
?>

<div class="container-fluid">
    <h1><i class="fas fa-can-food"></i> Nuevo Lote de Envasado</h1>
    <hr>

    <form id="lote-envasado-form">
        <input type="hidden" name="entidad" value="lotes_envasados">
        <input type="hidden" name="id" value="">

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Origen del Producto</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-2">Fermentador:</div>
                            <div class="col-6 mb-2">
                                <select name="id_batches_activos" class="form-control" id="fermentador-select" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach($batches_activos_disponibles as $ba) {
                                        $activo = new Activo($ba->id_activos);
                                        $batch = new Batch($ba->id_batches);
                                        $receta = new Receta($batch->id_recetas);
                                    ?>
                                    <option value="<?= $ba->id; ?>"
                                            data-litraje="<?= $ba->litraje; ?>"
                                            data-batch="<?= $batch->batch_nombre; ?>"
                                            data-receta="<?= $receta->nombre; ?>">
                                        <?= $activo->codigo; ?> - <?= $receta->nombre; ?> (<?= $ba->litraje; ?>L disponibles)
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-6 mb-2">Batch:</div>
                            <div class="col-6 mb-2">
                                <input type="text" id="batch-info" class="form-control" readonly>
                            </div>

                            <div class="col-6 mb-2">Litros Disponibles:</div>
                            <div class="col-6 mb-2">
                                <input type="number" id="litros-disponibles" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Información del Envasado</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-2">Tipo de Envase:</div>
                            <div class="col-6 mb-2">
                                <select name="tipo_envase" class="form-control" id="tipo-envase-select" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach($tipos_envase as $tipo => $litros) { ?>
                                    <option value="<?= $tipo; ?>" data-litros="<?= $litros; ?>">
                                        <?= $tipo; ?> (<?= $litros; ?>L)
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-6 mb-2">Litros a Utilizar:</div>
                            <div class="col-6 mb-2">
                                <input type="number" name="litros_utilizados"
                                       class="form-control" id="litros-utilizar"
                                       step="0.1" min="0" required>
                            </div>

                            <div class="col-6 mb-2">Cantidad Estimada:</div>
                            <div class="col-6 mb-2">
                                <div class="input-group">
                                    <input type="number" id="cantidad-estimada" class="form-control" readonly>
                                    <span class="input-group-text">unidades</span>
                                </div>
                            </div>

                            <div class="col-6 mb-2">Cantidad Real Envasada:</div>
                            <div class="col-6 mb-2">
                                <input type="number" name="cantidad_envasada"
                                       class="form-control" id="cantidad-real"
                                       min="1" required>
                            </div>

                            <div class="col-6 mb-2">Fecha de Envasado:</div>
                            <div class="col-6 mb-2">
                                <input type="datetime-local" name="fecha_envasado"
                                       class="form-control" value="<?= date('Y-m-d\TH:i'); ?>" required>
                            </div>

                            <div class="col-6 mb-2">Ubicación:</div>
                            <div class="col-6 mb-2">
                                <select name="ubicacion" class="form-control">
                                    <option>Bodega</option>
                                    <option>Cámara de Frío</option>
                                    <option>Área de Despacho</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5>Información Generada Automáticamente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Código de Lote:</strong><br>
                                <span id="codigo-lote" class="badge bg-primary fs-6">Se generará automáticamente</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Lote de Producción (Etiqueta):</strong><br>
                                <span id="lote-produccion" class="badge bg-secondary fs-6">Se generará automáticamente</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Fecha de Caducidad:</strong><br>
                                <span id="fecha-caducidad" class="badge bg-warning fs-6">+6 meses desde envasado</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> Registrar Lote de Envasado
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Actualizar información del fermentador seleccionado
$('#fermentador-select').on('change', function() {
    const option = $(this).find('option:selected');
    const litraje = option.data('litraje');
    const batch = option.data('batch');
    const receta = option.data('receta');

    $('#litros-disponibles').val(litraje);
    $('#batch-info').val(batch + ' - ' + receta);

    calcularCantidadEstimada();
});

// Calcular cantidad estimada de latas
function calcularCantidadEstimada() {
    const litrosUtilizar = parseFloat($('#litros-utilizar').val()) || 0;
    const tipoEnvase = $('#tipo-envase-select').find('option:selected');
    const litrosPorUnidad = parseFloat(tipoEnvase.data('litros')) || 0;

    if(litrosUtilizar > 0 && litrosPorUnidad > 0) {
        const cantidad = Math.floor(litrosUtilizar / litrosPorUnidad);
        $('#cantidad-estimada').val(cantidad);
        $('#cantidad-real').val(cantidad); // Pre-llenar con estimado
    }
}

$('#litros-utilizar, #tipo-envase-select').on('change', calcularCantidadEstimada);

// Validar que no se excedan litros disponibles
$('#litros-utilizar').on('change', function() {
    const litrosUtilizar = parseFloat($(this).val());
    const litrosDisponibles = parseFloat($('#litros-disponibles').val());

    if(litrosUtilizar > litrosDisponibles) {
        alert('No hay suficientes litros disponibles en el fermentador');
        $(this).val(litrosDisponibles);
        calcularCantidadEstimada();
    }
});

// Guardar lote
$('#lote-envasado-form').on('submit', function(e) {
    e.preventDefault();

    const data = getDataForm('lote-envasado');

    // Extraer ids de BatchActivo
    const batchActivo = JSON.parse($('#fermentador-select').find('option:selected').val());
    data.id_batches_activos = batchActivo.id;
    data.id_batches = batchActivo.id_batches;
    data.id_activos = batchActivo.id_activos;

    // Cantidad disponible = cantidad envasada al inicio
    data.cantidad_disponible = data.cantidad_envasada;

    $.post('./ajax/ajax_guardarLoteEnvasado.php', data, function(response) {
        if(response.status == 'OK') {
            alert('Lote registrado correctamente: ' + response.lote.codigo);
            window.location.href = './?s=inventario-latas';
        } else {
            alert('Error: ' + response.mensaje);
        }
    }, 'json');
});
</script>
```

#### 6.4.4 Integración con Despachos

**Modificación de `DespachoProducto`:**
```php
// Agregar campo id_lotes_envasados
public $id_lotes_envasados = 0;
```

**Modificación de vista de Nuevo Despacho:**
```php
// Además de seleccionar barriles, permitir seleccionar lotes de latas

<div class="row">
    <div class="col-12">
        <h5>Agregar Latas al Despacho</h5>
        <table class="table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all-lotes"></th>
                    <th>Código Lote</th>
                    <th>Tipo</th>
                    <th>Receta</th>
                    <th>Disponibles</th>
                    <th>Caducidad</th>
                    <th>Cantidad a Despachar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $lotes_disponibles = LoteEnvasado::getAll("WHERE cantidad_disponible > 0 ORDER BY fecha_caducidad ASC");
                foreach($lotes_disponibles as $lote) {
                    $batch = new Batch($lote->id_batches);
                    $receta = new Receta($batch->id_recetas);
                ?>
                <tr>
                    <td>
                        <input type="checkbox" class="lote-checkbox"
                               data-id="<?= $lote->id; ?>"
                               data-disponible="<?= $lote->cantidad_disponible; ?>">
                    </td>
                    <td><?= $lote->codigo; ?></td>
                    <td><?= $lote->tipo_envase; ?></td>
                    <td><?= $receta->nombre; ?></td>
                    <td><?= $lote->cantidad_disponible; ?></td>
                    <td><?= date2fecha($lote->fecha_caducidad); ?></td>
                    <td>
                        <input type="number" class="form-control cantidad-lote-input"
                               data-id="<?= $lote->id; ?>"
                               max="<?= $lote->cantidad_disponible; ?>"
                               min="1" disabled>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
```

### 6.5 Reportes y Analíticas para Latas

#### Dashboard de Inventario de Latas

```php
// Nuevo dashboard: inventario-latas.php
<?php
$lotes_activos = LoteEnvasado::getAll("WHERE cantidad_disponible > 0");
$lotes_proximos_caducar = [];
$lotes_caducados = [];

foreach($lotes_activos as $lote) {
    if($lote->estaCaducado()) {
        $lotes_caducados[] = $lote;
    } elseif($lote->proximoACaducar(30)) {
        $lotes_proximos_caducar[] = $lote;
    }
}

// Agrupar por receta
$inventario_por_receta = [];
foreach($lotes_activos as $lote) {
    $batch = new Batch($lote->id_batches);
    $receta_id = $batch->id_recetas;

    if(!isset($inventario_por_receta[$receta_id])) {
        $inventario_por_receta[$receta_id] = [
            'receta' => new Receta($receta_id),
            'lotes' => [],
            'cantidad_total' => 0
        ];
    }

    $inventario_por_receta[$receta_id]['lotes'][] = $lote;
    $inventario_por_receta[$receta_id]['cantidad_total'] += $lote->cantidad_disponible;
}
?>

<div class="container-fluid">
    <h1><i class="fas fa-warehouse"></i> Inventario de Latas</h1>

    <!-- KPIs -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Latas Disponibles</h5>
                    <h2><?= number_format(array_sum(array_column($lotes_activos, 'cantidad_disponible'))); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Próximos a Caducar (30 días)</h5>
                    <h2><?= count($lotes_proximos_caducar); ?> lotes</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Lotes Caducados</h5>
                    <h2><?= count($lotes_caducados); ?> lotes</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Lotes Activos</h5>
                    <h2><?= count($lotes_activos); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventario por Receta -->
    <div class="card">
        <div class="card-header">
            <h3>Inventario por Receta</h3>
        </div>
        <div class="card-body">
            <?php foreach($inventario_por_receta as $inv) { ?>
            <div class="accordion mb-3">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#receta-<?= $inv['receta']->id; ?>">
                            <?= $inv['receta']->nombre; ?>
                            <span class="badge bg-primary ms-3"><?= number_format($inv['cantidad_total']); ?> latas</span>
                        </button>
                    </h2>
                    <div id="receta-<?= $inv['receta']->id; ?>" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Código Lote</th>
                                        <th>Tipo Envase</th>
                                        <th>Fecha Envasado</th>
                                        <th>Fecha Caducidad</th>
                                        <th>Disponibles</th>
                                        <th>Ubicación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($inv['lotes'] as $lote) { ?>
                                    <tr class="<?= $lote->proximoACaducar(30) ? 'table-warning' : ''; ?>">
                                        <td><?= $lote->codigo; ?></td>
                                        <td><?= $lote->tipo_envase; ?></td>
                                        <td><?= date2fecha($lote->fecha_envasado); ?></td>
                                        <td><?= date2fecha($lote->fecha_caducidad); ?></td>
                                        <td><?= $lote->cantidad_disponible; ?></td>
                                        <td><?= $lote->ubicacion; ?></td>
                                        <td>
                                            <a href="./?s=detalle-lote-envasado&id=<?= $lote->id; ?>"
                                               class="btn btn-sm btn-info">Ver</a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
```

### 6.6 Beneficios de la Implementación

✅ **Trazabilidad por lote** (no requiere trackeo individual de 10,000+ latas)
✅ **Control de caducidad** automatizado
✅ **Gestión FIFO** (First In, First Out) natural
✅ **Integración con sistema de barriles** existente
✅ **Reportes de producción** detallados
✅ **Base para expansión a botellas** (misma arquitectura)

### 6.7 Resumen de Componentes Necesarios

| Componente | Tipo | Esfuerzo Estimado |
|------------|------|-------------------|
| Tabla `lotes_envasados` | Database | 1 hora |
| Clase `LoteEnvasado` | PHP | 4-6 horas |
| Vista `nuevo-lote-envasado.php` | Frontend | 6-8 horas |
| Vista `inventario-latas.php` | Frontend | 8-10 horas |
| Vista `detalle-lote-envasado.php` | Frontend | 4-6 horas |
| Integración con Despachos | Backend/Frontend | 6-8 horas |
| Reportes y Analytics | Frontend | 8-12 horas |
| Testing completo | QA | 8-10 horas |

**TOTAL ESTIMADO:** 45-61 horas (6-8 días de desarrollo)

---

## 7. Certificación Halal para Medio Oriente

### 7.1 Contexto Regulatorio

La certificación **Halal** (حلال‎, "permitido" en árabe) es un requisito fundamental para comercializar productos alimentarios en países de Medio Oriente y cualquier mercado con población musulmana significativa.

### 7.2 Restricción Fundamental: Bebidas Alcohólicas

#### ❌ Cerveza Alcohólica: NO CERTIFICABLE

**Veredicto Islámico Claro:**
Las bebidas alcohólicas (incluida la cerveza) están **EXPLÍCITAMENTE PROHIBIDAS** en la ley islámica (Sharia).

**Base Legal:**
- **Corán 5:90-91:** "Oh, creyentes, ciertamente el vino, los juegos de azar, los altares de sacrificio y las flechas de adivinación son una abominación y obra de Satanás. Absteneos de ello."
- **Hadith:** El Profeta Muhammad dijo: "Lo que embriaga en gran cantidad está prohibido incluso en pequeña cantidad."

**Conclusión:**
> **Las cervezas artesanales de Cerveza Cocholgue (con contenido alcohólico) NO PUEDEN obtener certificación Halal.**

### 7.3 Alternativa Viable: Cerveza Sin Alcohol

#### ✅ Cerveza 0.0% ALC: CERTIFICABLE

Si Cerveza Cocholgue desea ingresar al mercado de Medio Oriente, la **ÚNICA opción viable** es desarrollar una línea de **cerveza sin alcohol (0.0% ABV)**.

#### Requisitos para Cerveza Sin Alcohol Halal

**1. Contenido Alcohólico:**
- **Máximo permitido:** 0.0% ABV (cero alcohol)
- Algunos organismos toleran hasta 0.5% ABV, pero la mayoría exige 0.0%
- Debe certificarse mediante análisis de laboratorio

**2. Ingredientes Permitidos:**

✅ **Permitidos:**
- Malta de cebada, trigo, centeno (sin alcohol)
- Lúpulo
- Levadura (si no genera alcohol)
- Agua
- Especias naturales (cardamomo, cilantro, jengibre)
- Azúcares naturales

❌ **Prohibidos:**
- Cualquier ingrediente con alcohol
- Gelatina de cerdo (usada en algunas clarificaciones)
- Colorantes artificiales de origen animal no halal
- Enzimas de origen porcino

**3. Proceso de Producción:**

**Métodos permitidos para eliminar alcohol:**
- **Destilación al vacío** (método preferido)
- **Ósmosis inversa**
- **Evaporación térmica**
- **Fermentación detenida** (control de levadura)

**Separación de línea de producción:**
- ❗ **CRÍTICO:** La cerveza sin alcohol debe producirse en una línea COMPLETAMENTE SEPARADA de la cerveza alcohólica
- Los fermentadores, barriles, y equipos NO PUEDEN compartirse
- Debe haber protocolos de limpieza y sanitización certificados

**4. Trazabilidad Requerida:**

Para certificación Halal, el sistema de trazabilidad debe registrar:

✅ Origen de todos los ingredientes (certificados Halal)
✅ Proceso completo de producción
✅ Análisis de laboratorio de contenido alcohólico
✅ Separación de línea de producción
✅ Cadena de custodia hasta el consumidor

### 7.4 Organismos Certificadores Reconocidos

Para exportar a Medio Oriente, la certificación debe ser emitida por un organismo reconocido:

#### Organismos Internacionales:
1. **Islamic Food and Nutrition Council of America (IFANCA)**
2. **Halal Food Council of Europe (HFCE)**
3. **Halal Development Corporation (Malaysia)**
4. **Emirates Authority for Standardization and Metrology (ESMA)** - UAE

#### Organismos en Chile:
- **Centro Islámico de Chile** - Puede emitir certificaciones básicas
- Requiere validación adicional del país destino

### 7.5 Proceso de Certificación

#### Fase 1: Preparación (3-6 meses)

1. **Desarrollo de Receta Sin Alcohol**
   - Crear cerveza 0.0% con sabor aceptable
   - Pruebas de laboratorio confirmando 0.0% ABV

2. **Separación de Línea de Producción**
   - Adquirir fermentadores dedicados
   - Establecer área de producción separada
   - Implementar protocolos de limpieza

3. **Documentación de Procesos**
   - Manual de procedimientos Halal
   - Registros de trazabilidad
   - Certificados de ingredientes

#### Fase 2: Auditoría (2-4 semanas)

1. **Auditoría In-Situ**
   - Inspector Halal visita la planta
   - Verifica separación de líneas
   - Revisa ingredientes y proveedores
   - Inspecciona procesos de producción

2. **Análisis de Laboratorio**
   - Muestras enviadas a laboratorio certificado
   - Confirmación de 0.0% ABV
   - Análisis de ingredientes

#### Fase 3: Certificación (2-4 semanas)

1. **Emisión de Certificado**
   - Válido por 1 año (renovable)
   - Permite uso del sello Halal en etiquetas

2. **Auditorías de Seguimiento**
   - Anuales o semestrales
   - Sin aviso previo

### 7.6 Adaptaciones al Sistema Barril.cl

Para soportar una línea de producción Halal, el sistema necesitaría:

#### Nuevos Campos en Base de Datos:

**Tabla `batches`:**
```sql
ALTER TABLE batches
ADD COLUMN es_halal BOOLEAN DEFAULT FALSE,
ADD COLUMN certificado_halal VARCHAR(100),
ADD COLUMN fecha_certificacion_halal DATE,
ADD COLUMN contenido_alcoholico DECIMAL(5,3) DEFAULT 0.000; -- % ABV
```

**Tabla `activos`:**
```sql
ALTER TABLE activos
ADD COLUMN uso_exclusivo_halal BOOLEAN DEFAULT FALSE,
ADD COLUMN fecha_ultima_limpieza_halal DATETIME,
ADD COLUMN certificado_limpieza_halal VARCHAR(100);
```

**Tabla `insumos`:**
```sql
ALTER TABLE insumos
ADD COLUMN es_halal_certificado BOOLEAN DEFAULT FALSE,
ADD COLUMN organismo_certificador_halal VARCHAR(100),
ADD COLUMN numero_certificado_halal VARCHAR(100),
ADD COLUMN fecha_vencimiento_certificado_halal DATE;
```

#### Nuevas Validaciones:

**Al crear Batch Halal:**
```php
// php/classes/Batch.php
public function validarHalal() {
    if(!$this->es_halal) {
        return true; // No requiere validación
    }

    // Validar que todos los insumos tengan certificación Halal
    $insumos = BatchInsumo::getAll("WHERE id_batches='".$this->id."'");
    foreach($insumos as $bi) {
        $insumo = new Insumo($bi->id_insumos);
        if(!$insumo->es_halal_certificado) {
            throw new Exception("Insumo ".$insumo->nombre." no tiene certificación Halal");
        }
        if($insumo->fecha_vencimiento_certificado_halal < date('Y-m-d')) {
            throw new Exception("Certificación Halal de ".$insumo->nombre." está vencida");
        }
    }

    // Validar que el fermentador sea de uso exclusivo Halal
    $activo = new Activo($this->fermentacion_id_activos);
    if(!$activo->uso_exclusivo_halal) {
        throw new Exception("Fermentador ".$activo->codigo." no está designado para uso exclusivo Halal");
    }

    return true;
}
```

#### Nueva Vista: `reporte-halal.php`

```php
<?php
// Vista para generar reporte de certificación Halal

if(!validaIdExists($_GET,'id')) {
    die('ID de batch requerido');
}

$batch = new Batch($_GET['id']);

if(!$batch->es_halal) {
    die('Este batch no está certificado como Halal');
}

$receta = new Receta($batch->id_recetas);
$activos = BatchActivo::getAll("WHERE id_batches='".$batch->id."'");
$insumos = BatchInsumo::getAll("WHERE id_batches='".$batch->id."'");
?>

<div class="container-fluid">
    <div class="text-center mb-4">
        <h1>CERTIFICADO DE TRAZABILIDAD HALAL</h1>
        <p>Batch #<?= $batch->batch_nombre; ?> - <?= $receta->nombre; ?></p>
        <img src="./media/logo-halal.png" width="150">
    </div>

    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h3>Información del Batch</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <tr>
                    <td><strong>Batch:</strong></td>
                    <td>#<?= $batch->batch_nombre; ?></td>
                </tr>
                <tr>
                    <td><strong>Receta:</strong></td>
                    <td><?= $receta->nombre; ?></td>
                </tr>
                <tr>
                    <td><strong>Fecha de Producción:</strong></td>
                    <td><?= datetime2fechayhora($batch->creada); ?></td>
                </tr>
                <tr>
                    <td><strong>Contenido Alcohólico:</strong></td>
                    <td><?= $batch->contenido_alcoholico; ?>% ABV</td>
                </tr>
                <tr>
                    <td><strong>Certificado Halal:</strong></td>
                    <td><?= $batch->certificado_halal; ?></td>
                </tr>
                <tr>
                    <td><strong>Fecha de Certificación:</strong></td>
                    <td><?= date2fecha($batch->fecha_certificacion_halal); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3>Insumos Utilizados (Certificados Halal)</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Insumo</th>
                        <th>Cantidad</th>
                        <th>Certificado Halal</th>
                        <th>Organismo Certificador</th>
                        <th>Vigencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($insumos as $bi) {
                        $insumo = new Insumo($bi->id_insumos);
                    ?>
                    <tr>
                        <td><?= $insumo->nombre; ?></td>
                        <td><?= $bi->cantidad; ?> <?= $insumo->unidad_de_medida; ?></td>
                        <td><?= $insumo->numero_certificado_halal; ?></td>
                        <td><?= $insumo->organismo_certificador_halal; ?></td>
                        <td><?= date2fecha($insumo->fecha_vencimiento_certificado_halal); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Equipos Utilizados (Uso Exclusivo Halal)</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fermentador</th>
                        <th>Código</th>
                        <th>Última Limpieza Halal</th>
                        <th>Certificado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($activos as $ba) {
                        $activo = new Activo($ba->id_activos);
                    ?>
                    <tr>
                        <td><?= $activo->nombre; ?></td>
                        <td><?= $activo->codigo; ?></td>
                        <td><?= datetime2fechayhora($activo->fecha_ultima_limpieza_halal); ?></td>
                        <td><?= $activo->certificado_limpieza_halal; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 text-center">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Imprimir Certificado
        </button>
        <button onclick="exportarPDF()" class="btn btn-success">
            <i class="fas fa-file-pdf"></i> Exportar PDF
        </button>
    </div>
</div>
```

### 7.7 Costos Estimados de Certificación Halal

| Concepto | Costo Estimado (USD) |
|----------|----------------------|
| Desarrollo de receta sin alcohol | $5,000 - $10,000 |
| Equipos dedicados (fermentadores) | $15,000 - $30,000 |
| Certificación inicial (IFANCA) | $1,500 - $3,000 |
| Auditoría anual | $800 - $1,500 |
| Análisis de laboratorio (por batch) | $100 - $200 |
| Modificaciones al sistema Barril.cl | $3,000 - $5,000 |
| **TOTAL INVERSIÓN INICIAL** | **$25,400 - $49,700** |

### 7.8 Mercados Objetivo

Una vez certificado Halal, los mercados accesibles serían:

#### Medio Oriente:
- 🇦🇪 Emiratos Árabes Unidos (Dubai - hub logístico)
- 🇸🇦 Arabia Saudita
- 🇶🇦 Qatar
- 🇴🇲 Omán
- 🇰🇼 Kuwait
- 🇧🇭 Bahréin

#### Otros Mercados:
- 🇲🇾 Malasia
- 🇮🇩 Indonesia
- 🇹🇷 Turquía
- 🇪🇬 Egipto
- 🇲🇦 Marruecos

**Población musulmana mundial:** 1,800 millones
**Mercado Halal global:** $2.3 trillones USD (2024)

### 7.9 Recomendación Final sobre Halal

#### Opción 1: NO PROCEDER (Recomendado a corto plazo)

**Razones:**
- Requiere inversión significativa ($25K-50K USD)
- Implica desarrollar producto completamente nuevo (cerveza 0.0%)
- Necesita línea de producción separada
- Mercado de cerveza sin alcohol es nicho
- Chile no es reconocido como productor de bebidas Halal

**Mejor alternativa:** Enfocarse en mercados donde la cerveza artesanal alcohólica es apreciada (EEUU, Europa, Brasil, Argentina)

#### Opción 2: EXPLORAR (Recomendado a largo plazo)

Si Cerveza Cocholgue tiene interés en diversificación:

**1. Fase Piloto (6-12 meses):**
- Desarrollar 1-2 recetas de cerveza 0.0% de calidad
- Probar mercado local chileno
- Evaluar aceptación del producto

**2. Fase de Certificación (12-18 meses):**
- Si hay demanda local, proceder con certificación
- Adquirir equipos dedicados
- Obtener certificación Halal

**3. Fase de Exportación (18-24 meses):**
- Identificar distribuidor en UAE (Dubai)
- Exportar contenedor de prueba
- Evaluar respuesta del mercado

---

## 8. Recomendaciones Finales

### 8.1 Priorización de Soluciones

Basado en el análisis, las soluciones propuestas se priorizan de la siguiente manera:

#### 🔴 PRIORIDAD CRÍTICA (Implementar en 0-3 meses)

1. **Solución 5.1: Agregar Cliente a Despacho**
   - Impacto: ALTO en trazabilidad
   - Esfuerzo: BAJO (2-4 horas)
   - ROI: Inmediato

2. **Solución 5.2: Actualizar BatchActivo al Llenar Barriles**
   - Impacto: ALTO en inventario
   - Esfuerzo: MEDIO (3-5 horas)
   - ROI: Previene pérdidas y errores

#### 🟠 PRIORIDAD ALTA (Implementar en 3-6 meses)

3. **Solución 5.4: Vista Consolidada de Trazabilidad**
   - Impacto: ALTO en usabilidad
   - Esfuerzo: MEDIO (6-10 horas)
   - ROI: Mejora satisfacción de clientes y auditorías

4. **Solución 5.5: Códigos QR para Trazabilidad**
   - Impacto: MEDIO-ALTO en eficiencia
   - Esfuerzo: MEDIO (6-8 horas)
   - ROI: Diferenciador competitivo

#### 🟡 PRIORIDAD MEDIA (Implementar en 6-12 meses)

5. **Solución 5.3: Registro de Consumo Parcial**
   - Impacto: MEDIO en planificación
   - Esfuerzo: MEDIO (5-8 horas)
   - ROI: Mejora servicio al cliente

6. **Expansión a Formato Latas (Sección 6)**
   - Impacto: ALTO en diversificación
   - Esfuerzo: ALTO (45-61 horas)
   - ROI: Nuevo mercado y revenue stream

#### 🟢 PRIORIDAD BAJA (Evaluar en 12+ meses)

7. **Certificación Halal (Sección 7)**
   - Impacto: Potencial ALTO en nuevos mercados
   - Esfuerzo: MUY ALTO ($25K-50K USD + 18-24 meses)
   - ROI: Incierto, requiere validación de mercado

### 8.2 Roadmap de Implementación Sugerido

```
Q1 2026:
- ✅ Agregar id_clientes a Despacho
- ✅ Actualizar lógica de llenado de barriles
- ✅ Agregar índices a BD para performance

Q2 2026:
- ✅ Vista consolidada de trazabilidad
- ✅ Sistema de códigos QR
- ✅ Dashboard de alertas de trazabilidad

Q3 2026:
- ✅ Registro de consumo parcial
- ✅ Reportes avanzados de trazabilidad
- 📋 Evaluar viabilidad de expansión a latas

Q4 2026:
- 🚀 Inicio desarrollo de sistema de latas (si aprobado)
- 📋 Investigación de mercado para cerveza 0.0%

2027:
- 🚀 Lanzamiento de línea de latas
- 📋 Evaluación de certificación Halal (si hay demanda)
```

### 8.3 Métricas de Éxito

Para medir el éxito de las mejoras implementadas:

#### Métricas de Trazabilidad:

- **Tiempo promedio de rastreo completo de un barril:** < 2 minutos
- **Porcentaje de barriles con historial completo:** > 98%
- **Auditorías exitosas sin hallazgos:** 100%

#### Métricas de Inventario:

- **Diferencia entre inventario físico y sistema:** < 2%
- **Barriles "perdidos" al año:** < 1%
- **Tiempo de detección de discrepancias:** < 24 horas

#### Métricas de Eficiencia:

- **Tiempo de creación de despacho:** < 5 minutos
- **Tiempo de registro de entrega:** < 3 minutos
- **Errores de despacho:** < 0.5%

### 8.4 Consideraciones Finales

#### Fortalezas del Sistema Actual:

🌟 **Excelente fundación de trazabilidad:** El sistema actual tiene todos los componentes necesarios para trazabilidad end-to-end.

🌟 **Historial de estados robusto:** El sistema `BarrilEstado` es una implementación ejemplar de auditoría.

🌟 **Vinculaciones múltiples:** La triple vinculación Barril → Batch → Activo → BatchActivo garantiza trazabilidad completa.

#### Áreas de Mejora Identificadas:

⚠️ **Gaps en modelo de datos:** Falta campo `id_clientes` en Despacho.

⚠️ **Inventario en tiempo real:** Falta actualización de `BatchActivo.litraje` al llenar barriles.

⚠️ **Visibilidad para usuarios:** No hay vista consolidada de trazabilidad completa.

#### Visión a Futuro:

El sistema Barril.cl tiene potencial para convertirse en un **ERP de referencia para cervecerías artesanales** en Latinoamérica. Con las mejoras propuestas:

- Cumplirá con estándares internacionales de trazabilidad (ISO 22000, FSSC 22000)
- Permitirá expansión a nuevos formatos de envasado
- Facilitará certificaciones (Organic, Halal, Fair Trade)
- Soportará exportación a mercados regulados

---

## Apéndices

### Apéndice A: Glosario de Términos

- **Batch:** Lote de producción de cerveza
- **BatchActivo:** Relación entre un batch y un fermentador
- **Activo:** Equipo de producción (fermentador, enfriador, etc.)
- **Barril:** Contenedor retornable para cerveza (20L, 30L, 50L)
- **BarrilEstado:** Registro histórico de estados de un barril
- **Despacho:** Conjunto de productos asignados a un repartidor
- **DespachoProducto:** Producto específico dentro de un despacho
- **Entrega:** Acto de entregar productos a un cliente
- **EntregaProducto:** Producto específico entregado
- **Trazabilidad:** Capacidad de rastrear el historial de un producto

### Apéndice B: Estados Posibles de Barril

| Estado | Descripción | Trigger |
|--------|-------------|---------|
| En planta | Barril en la cervecería | Llenado completo |
| En despacho | Barril asignado a repartidor | Creación de despacho |
| En terreno | Barril en cliente activo | Entrega realizada |
| Pinchado | Barril consumido/vacío | Actualización del repartidor |
| Perdido | Barril extraviado | Marcado manualmente |
| Devuelto a planta | Barril retornado | Recepción física |

### Apéndice C: Queries Útiles para Auditoría

**1. Rastrear todos los barriles de un batch:**
```sql
SELECT b.codigo, b.estado, b.id_clientes, be.estado AS estado_actual, be.inicio_date
FROM barriles b
LEFT JOIN barriles_estados be ON be.id_barriles = b.id AND be.finalizacion_date = '0000-00-00 00:00:00'
WHERE b.id_batches = 123
ORDER BY b.codigo;
```

**2. Ver historial completo de un barril:**
```sql
SELECT be.estado, be.inicio_date, be.finalizacion_date, be.tiempo_transcurrido,
       c.nombre AS cliente, u.nombre AS usuario
FROM barriles_estados be
LEFT JOIN clientes c ON c.id = be.id_clientes
LEFT JOIN usuarios u ON u.id = be.id_usuarios
WHERE be.id_barriles = 456
ORDER BY be.inicio_date DESC;
```

**3. Barriles por estado actual:**
```sql
SELECT estado, COUNT(*) AS cantidad
FROM barriles
GROUP BY estado
ORDER BY cantidad DESC;
```

**4. Barriles en cliente específico:**
```sql
SELECT b.codigo, ba.batch_nombre, r.nombre AS receta,
       be.inicio_date AS fecha_entrega, be.tiempo_transcurrido
FROM barriles b
JOIN batches ba ON ba.id = b.id_batches
JOIN recetas r ON r.id = ba.id_recetas
LEFT JOIN barriles_estados be ON be.id_barriles = b.id
    AND be.estado = 'En terreno'
    AND be.finalizacion_date = '0000-00-00 00:00:00'
WHERE b.id_clientes = 789 AND b.estado IN ('En terreno', 'Pinchado')
ORDER BY be.inicio_date DESC;
```

### Apéndice D: Contactos para Certificación Halal

**Organismos Internacionales:**
- IFANCA (USA): https://www.ifanca.org | info@ifanca.org
- HFCE (Europa): https://www.halaleurope.eu
- HDC (Malasia): https://www.hdcglobal.com

**Organismos en Chile:**
- Centro Islámico de Chile: +56 2 2633 7373
- Mezquita As-Salam, Santiago

**Laboratorios para Análisis ABV:**
- SGS Chile: https://www.sgs.cl
- Bureau Veritas Chile: https://www.bureauveritas.cl

---

## Conclusión

El sistema de trazabilidad de Barril.cl es **robusto y funcional**, con una arquitectura bien diseñada que permite rastrear cada barril desde su producción hasta la entrega al cliente. Las mejoras propuestas fortalecerán aún más el sistema, permitiendo:

1. **Trazabilidad 100% confiable** para auditorías y certificaciones
2. **Expansión a nuevos formatos** (latas, botellas)
3. **Acceso a mercados internacionales** con requisitos regulatorios estrictos
4. **Eficiencia operativa** mediante automatización y reportes

Con una inversión moderada en desarrollo (50-100 horas) y siguiendo el roadmap propuesto, Cerveza Cocholgue estará posicionada como una cervecería artesanal de **clase mundial** en términos de trazabilidad y gestión operativa.

---

**Fin del Documento**

*Fecha de generación: 27 de Noviembre, 2025*
*Versión: 1.0*
*Próxima revisión: Trimestral*
