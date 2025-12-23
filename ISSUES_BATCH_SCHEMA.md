# Problemas Identificados: Schema vs Clases PHP (Batch)

Fecha: 2025-12-05

---

## 1. BatchTraspaso - Falta propiedad `seq_index`

**Archivos afectados:**
- `db/schema.sql` (tabla `batches_traspasos`)
- `php/classes/BatchTraspaso.php`
- `php/classes/Batch.php:183`

**Problema:**
En `Batch.php` línea 183 se asigna:
```php
$batch_traspaso->seq_index = $l_key;
```

Pero `seq_index` no existe ni en la clase `BatchTraspaso` ni en la tabla `batches_traspasos`.

**Solución:**
Agregar columna `seq_index` a la tabla y propiedad a la clase, o eliminar la asignación en `Batch.php`.

---

## 2. BatchEnfriado - Propiedad `tipo` no existe en schema

**Archivos afectados:**
- `db/schema.sql` (tabla `batches_enfriado`)
- `php/classes/BatchEnfriado.php:13`

**Problema:**
La clase tiene:
```php
public $tipo = '';
```

Pero la tabla `batches_enfriado` no tiene columna `tipo`. La propiedad se ignora al guardar.

**Solución:**
Agregar columna `tipo VARCHAR(100)` a la tabla `batches_enfriado`, o eliminar la propiedad de la clase si no se usa.

---

## 3. Batch - Tipos de datos (menor prioridad)

**Archivos afectados:**
- `db/schema.sql` (tabla `batches`)
- `php/classes/Batch.php`

**Problema:**
El schema define campos como `decimal`:
- `licor_temperatura` decimal(5,2)
- `licor_ph` decimal(4,2)
- `maceracion_temperatura` decimal(5,2)
- etc.

La clase los inicializa como `int`:
```php
public $licor_temperatura = 0;
public $licor_ph = 0;
```

Esto puede causar pérdida de precisión con valores decimales (ej: 65.5 → 65).

**Solución:**
Inicializar como `0.0` o simplemente no inicializar (PHP manejará el tipo dinámicamente).

---

## 4. BatchActivo - Falta asignar `id_batches` al guardar

**Archivos afectados:**
- `php/classes/Batch.php:196-204`

**Problema:**
```php
if(isset($post['fermentacion_fermentadores']) && is_array($post['fermentacion_fermentadores'])) {
  foreach($post['fermentacion_fermentadores'] as $l_key => $traspaso) {
    $batch_traspaso = new BatchActivo;
    $batch_traspaso->setPropertiesNoId($traspaso);
    $batch_traspaso->save();  // ← Se guarda SIN id_batches si no viene en $traspaso
    $fermentador = new Activo($batch_traspaso->id_activos);
    $fermentador->id_batches = $this->id;
    $fermentador->save();
  }
}
```

Si `$traspaso` no incluye `id_batches`, el registro se guarda con `id_batches = 0`.

**Solución:**
Agregar antes del `save()`:
```php
$batch_traspaso->id_batches = $this->id;
```

---

## Resumen de Acciones

| # | Severidad | Problema | Acción Requerida |
|---|-----------|----------|------------------|
| 1 | Alta | `seq_index` no existe en BatchTraspaso | Agregar columna/propiedad o eliminar asignación |
| 2 | Media | `tipo` no existe en batches_enfriado | Agregar columna o eliminar propiedad |
| 3 | Baja | Tipos int vs decimal | Opcional: cambiar inicialización |
| 4 | **Crítica** | `id_batches` no se asigna en BatchActivo | Agregar asignación antes de save() |

---

## SQL para corregir schema (si se decide agregar columnas)

```sql
-- Issue 1: Agregar seq_index a batches_traspasos
ALTER TABLE `batches_traspasos` ADD COLUMN `seq_index` int(11) NOT NULL DEFAULT 0 AFTER `hora`;

-- Issue 2: Agregar tipo a batches_enfriado
ALTER TABLE `batches_enfriado` ADD COLUMN `tipo` varchar(100) NOT NULL DEFAULT '' AFTER `seq_index`;
```
