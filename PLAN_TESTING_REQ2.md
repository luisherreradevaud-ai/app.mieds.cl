# Plan de Testing Manual - REQ2 (Sistema Halal, ML y Limpiezas)

## Información General

| Campo | Valor |
|-------|-------|
| Fecha | 2025-12-06 |
| Versión | 1.0 |
| Autor | Claude Code |
| Módulos | Productos, Insumos, Batches, Activos, Limpiezas, PDF Trazabilidad |

---

## Pre-requisitos

### 1. Ejecutar Migraciones
```bash
mysql -u barrcl_cocholg -p barrcl_cocholg < db/migrations/009_productos_linea_productiva.sql
mysql -u barrcl_cocholg -p barrcl_cocholg < db/migrations/010_batches_ml_fields.sql
mysql -u barrcl_cocholg -p barrcl_cocholg < db/migrations/011_insumos_fichas_certificados.sql
mysql -u barrcl_cocholg -p barrcl_cocholg < db/migrations/012_sistema_limpiezas.sql
```

### 2. Verificar Migraciones
```sql
-- Verificar campos en productos
DESCRIBE productos;
-- Debe mostrar: linea_productiva ENUM('alcoholica','analcoholica','general')

-- Verificar campos ML en batches
DESCRIBE batches;
-- Debe mostrar: abv_final, ibu_final, color_ebc, rendimiento_litros_final, etc.

-- Verificar campos Halal en insumos
DESCRIBE insumos;
-- Debe mostrar: url_ficha_tecnica, url_certificado_halal, es_halal_certificado, etc.

-- Verificar campos limpieza en activos
DESCRIBE activos;
-- Debe mostrar: fecha_ultima_limpieza, fecha_ultima_limpieza_halal, uso_exclusivo_halal, etc.

-- Verificar nuevas tablas
SHOW TABLES LIKE '%limpieza%';
-- Debe mostrar: registros_limpiezas, procedimientos_limpieza

-- Verificar procedimientos iniciales
SELECT * FROM procedimientos_limpieza;
-- Debe mostrar 5 procedimientos predefinidos
```

### 3. Usuarios de Prueba
| Rol | Usuario | Permisos Esperados |
|-----|---------|-------------------|
| Administrador | admin | Acceso total |
| Jefe de Planta | jplanta | Gestión limpiezas |
| Jefe de Cocina | jcocina | Registrar limpiezas |
| Operario | operario | Registrar limpiezas |

---

## PARTE 1: Testing de Nuevas Funcionalidades

### 1.1 Módulo: Productos - Línea Productiva

#### TC-PROD-001: Visualizar campo línea productiva
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |
| Precondición | Producto existente |

**Pasos:**
1. Navegar a `?s=nuevo-productos&id={id_producto}`
2. Verificar que existe el campo "Línea Productiva"
3. Verificar opciones del select

**Resultado Esperado:**
- [x] Campo visible con opciones: "Línea Alcohólica", "Línea Sin Alcohol", "General"
- [x] Valor actual del producto cargado correctamente

#### TC-PROD-002: Guardar línea productiva
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |
| Precondición | Producto existente |

**Pasos:**
1. Navegar a detalle de producto
2. Cambiar línea productiva a "Línea Sin Alcohol"
3. Guardar
4. Recargar página

**Resultado Esperado:**
- [x] Mensaje de éxito
- [x] Valor persiste después de recargar
- [x] BD muestra `linea_productiva = 'analcoholica'`

---

### 1.2 Módulo: Insumos - Certificación Halal

#### TC-INS-001: Visualizar campos Halal en detalle insumo
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |
| Precondición | Insumo existente |

**Pasos:**
1. Navegar a `?s=detalle-insumos&id={id}`
2. Buscar sección de documentación/certificación

**Resultado Esperado:**
- [x] Campo "URL Ficha Técnica" visible
- [x] Campo "URL Certificado Halal" visible
- [x] Checkbox "Es Halal Certificado" visible
- [x] Campos de número, vencimiento y emisor de certificado

#### TC-INS-002: Guardar certificación Halal
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |
| Precondición | Insumo existente |

**Pasos:**
1. Abrir detalle de insumo
2. Marcar "Es Halal Certificado"
3. Ingresar número de certificado: "HALAL-2025-001"
4. Ingresar fecha vencimiento: fecha futura
5. Ingresar emisor: "Certificadora Halal Chile"
6. Guardar

**Resultado Esperado:**
- [x] Datos guardados correctamente
- [x] Badge de estado muestra "Vigente" (verde)

#### TC-INS-003: Verificar vencimiento de certificado
| Campo | Valor |
|-------|-------|
| Prioridad | Media |

**Pasos:**
1. Crear/editar insumo con certificado Halal
2. Establecer fecha vencimiento en 15 días
3. Guardar y recargar

**Resultado Esperado:**
- [x] Badge muestra "Por vencer (X días)" (amarillo)

#### TC-INS-004: Certificado vencido
| Campo | Valor |
|-------|-------|
| Prioridad | Media |

**Pasos:**
1. Editar insumo con certificado
2. Establecer fecha vencimiento en el pasado
3. Guardar y recargar

**Resultado Esperado:**
- [x] Badge muestra "Vencido" (rojo)

---

### 1.3 Módulo: Batches - Campos ML

#### TC-BATCH-001: Visualizar sección Métricas ML
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |
| Precondición | Batch existente o nuevo |

**Pasos:**
1. Navegar a `?s=nuevo-batches` o `?s=nuevo-batches&id={id}`
2. Scroll hasta el final del formulario
3. Buscar sección "Métricas de Calidad (ML)"

**Resultado Esperado:**
- [x] Sección visible con título e icono
- [x] Campos: ABV Final, IBU Final, Color EBC, Calificación Sensorial
- [x] Campos: Rendimiento Final, Merma Total, Densidad Final Verificada
- [x] Campo: Notas de Cata (textarea)

#### TC-BATCH-002: Guardar métricas ML
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |
| Precondición | Batch existente |

**Pasos:**
1. Abrir batch existente
2. Completar campos ML:
   - ABV Final: 5.5
   - IBU Final: 45
   - Color EBC: 12
   - Calificación Sensorial: 8
   - Rendimiento Final: 180
   - Merma Total: 20
   - Densidad Final: 1.012
   - Notas de Cata: "Notas cítricas, cuerpo medio"
3. Guardar batch

**Resultado Esperado:**
- [x] Mensaje de éxito
- [x] Valores persisten al recargar
- [x] BD muestra valores correctos en tabla batches

#### TC-BATCH-003: Validación de rangos
| Campo | Valor |
|-------|-------|
| Prioridad | Baja |

**Pasos:**
1. Intentar ingresar ABV = 25 (máximo es 20)
2. Intentar ingresar IBU = 200 (máximo es 150)
3. Intentar ingresar Calificación = 15 (máximo es 10)

**Resultado Esperado:**
- [x] Navegador impide valores fuera de rango (validación HTML5)

---

### 1.4 Módulo: Activos - Sistema de Limpiezas

#### TC-ACT-001: Visualizar sección Limpiezas
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |
| Precondición | Activo tipo Fermentador existente |

**Pasos:**
1. Navegar a `?s=detalle-activos&id={id}`
2. Scroll hasta sección "Limpiezas"

**Resultado Esperado:**
- [x] Título "Limpiezas" con icono de escoba
- [x] Campo "Periodicidad" (select)
- [x] Campos "Última limpieza" y "Próxima limpieza"
- [x] Campo "Procedimiento de limpieza" (textarea)
- [x] Subsección "Limpieza Halal" (si línea productiva es general o analcohólica)
- [x] Tabla "Historial de Limpiezas"
- [x] Botón "+ Registrar Limpieza"

#### TC-ACT-002: Registrar limpieza general
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |
| Precondición | Activo existente, usuario con permisos |

**Pasos:**
1. Abrir detalle de activo
2. Click en "+ Registrar Limpieza"
3. Modal se abre
4. Seleccionar tipo: "Limpieza General"
5. Seleccionar procedimiento (si hay)
6. Ingresar productos utilizados: "Detergente neutro, agua"
7. Ingresar observaciones: "Limpieza rutinaria"
8. Click "Registrar Limpieza"

**Resultado Esperado:**
- [x] Modal se cierra
- [x] Página recarga
- [x] Nueva fila en historial de limpiezas
- [x] "Última limpieza" actualizada
- [x] "Próxima limpieza" calculada según periodicidad

#### TC-ACT-003: Registrar limpieza Halal
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |
| Precondición | Activo con línea_productiva = 'general' |

**Pasos:**
1. Abrir detalle de activo
2. Click en "+ Registrar Limpieza"
3. Seleccionar tipo: "Limpieza Halal Certificada"
4. Verificar que aparecen campos adicionales:
   - Número de Certificado
   - Entidad Certificadora
   - Supervisor
5. Completar:
   - Certificado: "HALAL-LIM-2025-001"
   - Emisor: "Certificadora Halal Chile"
   - Supervisor: seleccionar administrador
6. Guardar

**Resultado Esperado:**
- [x] Limpieza registrada con badge "Halal" (verde)
- [x] Columna "Halal" muestra check verde
- [x] Columna "Certificado" muestra número
- [x] Campo "Última limpieza Halal" actualizado en activo

#### TC-ACT-004: Ver detalle de limpieza
| Campo | Valor |
|-------|-------|
| Prioridad | Media |
| Precondición | Limpieza registrada |

**Pasos:**
1. En historial de limpiezas, click en icono ojo
2. Modal de detalle se abre

**Resultado Esperado:**
- [x] Modal muestra todos los datos de la limpieza
- [x] Si es Halal, muestra información de certificado

#### TC-ACT-005: Checkbox uso exclusivo Halal
| Campo | Valor |
|-------|-------|
| Prioridad | Media |

**Pasos:**
1. Marcar checkbox "Uso exclusivo para producción Halal"
2. Guardar activo

**Resultado Esperado:**
- [x] Valor persiste
- [x] Este activo no requiere limpieza Halal previa para producción sin alcohol

---

### 1.5 Módulo: Validación Limpieza Halal (AJAX)

#### TC-AJAX-001: Validar activo con limpieza Halal reciente
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |
| Precondición | Activo con limpieza Halal < 24 horas |

**Pasos:**
```bash
curl "http://localhost/app.barril.cl/ajax/ajax_validarLimpiezaHalal.php?id_activos={id}"
```

**Resultado Esperado:**
```json
{
  "status": "OK",
  "valido": true,
  "mensaje": "Limpieza Halal válida",
  "requiere_limpieza": false
}
```

#### TC-AJAX-002: Validar activo sin limpieza Halal
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |
| Precondición | Activo sin limpieza Halal o > 24 horas |

**Pasos:**
```bash
curl "http://localhost/app.barril.cl/ajax/ajax_validarLimpiezaHalal.php?id_activos={id}"
```

**Resultado Esperado:**
```json
{
  "status": "OK",
  "valido": false,
  "mensaje": "No hay registro de limpieza Halal...",
  "requiere_limpieza": true
}
```

#### TC-AJAX-003: Validar activo uso exclusivo Halal
| Campo | Valor |
|-------|-------|
| Prioridad | Media |
| Precondición | Activo con uso_exclusivo_halal = 1 |

**Pasos:**
```bash
curl "http://localhost/app.barril.cl/ajax/ajax_validarLimpiezaHalal.php?id_activos={id}"
```

**Resultado Esperado:**
```json
{
  "status": "OK",
  "valido": true,
  "mensaje": "Activo de uso exclusivo Halal",
  "requiere_limpieza": false
}
```

---

### 1.6 Módulo: PDF Trazabilidad - Mejoras Halal

#### TC-PDF-001: PDF de producto línea alcohólica
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |
| Precondición | Entrega con barril de cerveza (alcohólica) |

**Pasos:**
1. Navegar a detalle de entrega
2. Click en "Generar PDF Trazabilidad" para un barril

**Resultado Esperado:**
- [x] PDF se genera correctamente
- [x] Título: "CERTIFICADO DE TRAZABILIDAD DE PRODUCTO"
- [x] Subtítulo: "Cerveza Cocholgue" (sin normas adicionales)
- [x] No aparece sección de limpiezas Halal
- [x] Código de producto muestra "Código de barril:"

#### TC-PDF-002: PDF de producto línea sin alcohol (Halal)
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |
| Precondición | Entrega con producto de línea analcohólica (Kombucha) |

**Pasos:**
1. Crear batch de receta tipo "Kombucha" o "Agua fermentada"
2. Llenar barril con ese batch
3. Despachar y entregar
4. Generar PDF de trazabilidad

**Resultado Esperado:**
- [x] PDF se genera correctamente
- [x] Subtítulo incluye: "ISO 22000 / HALAL"
- [x] Aparece texto: "Producto de Línea Sin Alcohol - Apto Halal"
- [x] Sección "INSUMOS UTILIZADOS" muestra columna "Halal"
- [x] Sección "REGISTRO DE LIMPIEZAS HALAL" aparece (si hay limpiezas)
- [x] Footer incluye: "Certificado de Trazabilidad Halal"

#### TC-PDF-003: Insumos con links a fichas técnicas
| Campo | Valor |
|-------|-------|
| Prioridad | Media |
| Precondición | Insumo con url_ficha_tecnica configurada |

**Pasos:**
1. Configurar insumo con URL de ficha técnica
2. Usar ese insumo en un batch
3. Generar PDF de trazabilidad

**Resultado Esperado:**
- [x] Nombre del insumo aparece como link clickeable
- [x] Link apunta a la URL configurada

#### TC-PDF-004: Batch ID con fecha
| Campo | Valor |
|-------|-------|
| Prioridad | Media |

**Pasos:**
1. Generar PDF de cualquier producto

**Resultado Esperado:**
- [x] En sección COCCIÓN, línea Batch muestra: "Nombre (ID: xxx - DD/MM/YYYY)"

#### TC-PDF-005: Línea productiva inline
| Campo | Valor |
|-------|-------|
| Prioridad | Media |

**Pasos:**
1. Generar PDF de producto con línea productiva configurada

**Resultado Esperado:**
- [x] En "Tipo:" aparece badge con línea productiva
- [x] Badge color según línea (rojo=alcohólica, verde=sin alcohol, azul=general)

---

## PARTE 2: Testing Regresivo

### 2.1 Módulo: Productos (Funcionalidad Existente)

#### TC-REG-PROD-001: Crear producto nuevo
| Campo | Valor |
|-------|-------|
| Prioridad | Crítica |

**Pasos:**
1. Navegar a `?s=nuevo-productos`
2. Completar datos básicos: nombre, tipo, clasificación
3. Guardar

**Resultado Esperado:**
- [x] Producto creado correctamente
- [x] Redirección a detalle con msg=1
- [x] Campo linea_productiva tiene valor default "general"

#### TC-REG-PROD-002: Editar producto existente
| Campo | Valor |
|-------|-------|
| Prioridad | Crítica |

**Pasos:**
1. Abrir producto existente
2. Modificar nombre
3. Guardar

**Resultado Esperado:**
- [x] Cambios guardados
- [x] Otros campos no afectados
- [x] Sin errores JavaScript en consola

#### TC-REG-PROD-003: Eliminar producto
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |

**Pasos:**
1. Abrir producto existente
2. Click "Eliminar"
3. Confirmar

**Resultado Esperado:**
- [x] Producto eliminado (estado='eliminado')
- [x] Redirección a lista de productos

---

### 2.2 Módulo: Insumos (Funcionalidad Existente)

#### TC-REG-INS-001: Crear insumo nuevo
| Campo | Valor |
|-------|-------|
| Prioridad | Crítica |

**Pasos:**
1. Navegar a crear insumo
2. Completar datos básicos
3. Guardar

**Resultado Esperado:**
- [x] Insumo creado
- [x] Campos Halal vacíos por defecto (es_halal_certificado=0)

#### TC-REG-INS-002: Editar insumo existente
| Campo | Valor |
|-------|-------|
| Prioridad | Crítica |

**Pasos:**
1. Abrir insumo existente (anterior a migración)
2. Modificar nombre
3. Guardar

**Resultado Esperado:**
- [x] Cambios guardados sin errores
- [x] Campos Halal mantienen valores default

#### TC-REG-INS-003: Usar insumo en batch
| Campo | Valor |
|-------|-------|
| Prioridad | Crítica |

**Pasos:**
1. Crear/editar batch
2. Agregar insumo existente
3. Guardar batch

**Resultado Esperado:**
- [x] Insumo agregado correctamente
- [x] Stock de bodega descontado
- [x] BatchInsumo creado en BD

---

### 2.3 Módulo: Batches (Funcionalidad Existente)

#### TC-REG-BATCH-001: Crear batch nuevo
| Campo | Valor |
|-------|-------|
| Prioridad | Crítica |

**Pasos:**
1. Navegar a `?s=nuevo-batches`
2. Completar datos de cocción
3. Agregar insumos
4. Guardar

**Resultado Esperado:**
- [x] Batch creado
- [x] Campos ML vacíos por defecto
- [x] Flujo normal sin errores

#### TC-REG-BATCH-002: Editar batch existente (pre-migración)
| Campo | Valor |
|-------|-------|
| Prioridad | Crítica |

**Pasos:**
1. Abrir batch creado antes de la migración
2. Modificar algún campo existente
3. Guardar

**Resultado Esperado:**
- [x] Batch se guarda sin errores
- [x] Campos ML muestran valores null/vacíos
- [x] Sin errores PHP ni JavaScript

#### TC-REG-BATCH-003: Agregar activo a batch
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |

**Pasos:**
1. Abrir batch
2. Ir a pestaña Fermentación
3. Agregar fermentador

**Resultado Esperado:**
- [x] Fermentador asignado correctamente
- [x] BatchActivo creado en BD
- [x] Fermentador muestra id_batches actualizado

#### TC-REG-BATCH-004: Eliminar batch
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |

**Pasos:**
1. Abrir batch
2. Click "Eliminar Batch"
3. Confirmar

**Resultado Esperado:**
- [x] Batch eliminado
- [x] Insumos devueltos a bodega
- [x] Fermentadores liberados

---

### 2.4 Módulo: Activos (Funcionalidad Existente)

#### TC-REG-ACT-001: Crear activo nuevo
| Campo | Valor |
|-------|-------|
| Prioridad | Crítica |

**Pasos:**
1. Navegar a `?s=nuevo-activos`
2. Completar datos básicos
3. Guardar

**Resultado Esperado:**
- [x] Activo creado
- [x] Campos de limpieza con valores default
- [x] linea_productiva = 'general' por defecto

#### TC-REG-ACT-002: Editar activo existente (pre-migración)
| Campo | Valor |
|-------|-------|
| Prioridad | Crítica |

**Pasos:**
1. Abrir activo creado antes de la migración
2. Modificar nombre
3. Guardar

**Resultado Esperado:**
- [x] Activo guardado sin errores
- [x] Campos de limpieza vacíos/default
- [x] Sección de limpiezas visible pero historial vacío

#### TC-REG-ACT-003: Inspecciones y Mantenciones
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |

**Pasos:**
1. Abrir activo
2. Modificar fechas de inspección/mantención
3. Guardar

**Resultado Esperado:**
- [x] Fechas guardadas correctamente
- [x] CKEditor funciona para procedimientos
- [x] Sin conflicto con nueva sección de limpiezas

#### TC-REG-ACT-004: Accesorios de activo
| Campo | Valor |
|-------|-------|
| Prioridad | Media |

**Pasos:**
1. Abrir activo
2. Agregar accesorio
3. Editar accesorio
4. Eliminar accesorio

**Resultado Esperado:**
- [x] CRUD de accesorios funciona correctamente
- [x] Modales abren/cierran sin problemas

---

### 2.5 Módulo: Entregas y Despachos

#### TC-REG-ENT-001: Crear despacho con barriles
| Campo | Valor |
|-------|-------|
| Prioridad | Crítica |

**Pasos:**
1. Crear despacho
2. Agregar barriles
3. Guardar

**Resultado Esperado:**
- [x] Despacho creado
- [x] Barriles cambian a estado "En despacho"
- [x] Sin errores

#### TC-REG-ENT-002: Crear entrega
| Campo | Valor |
|-------|-------|
| Prioridad | Crítica |

**Pasos:**
1. Marcar despacho como entregado
2. Completar datos de entrega

**Resultado Esperado:**
- [x] Entrega creada
- [x] Productos cambian a estado "En terreno"/entregado

#### TC-REG-ENT-003: Generar PDF trazabilidad (producto existente)
| Campo | Valor |
|-------|-------|
| Prioridad | Crítica |

**Pasos:**
1. Abrir entrega antigua (pre-migración)
2. Generar PDF de trazabilidad

**Resultado Esperado:**
- [x] PDF se genera sin errores
- [x] Secciones estándar visibles
- [x] Sin errores PHP por campos null/nuevos

---

### 2.6 Módulo: Sistema de Envases

#### TC-REG-ENV-001: Envasar desde fermentador
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |

**Pasos:**
1. Navegar a inventario de productos
2. Seleccionar fermentador con batch
3. Envasar a latas/botellas

**Resultado Esperado:**
- [x] Envases creados
- [x] BatchDeEnvases creado
- [x] Litros descontados del fermentador

#### TC-REG-ENV-002: Crear caja de envases
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |

**Pasos:**
1. Seleccionar envases
2. Crear caja

**Resultado Esperado:**
- [x] CajaDeEnvases creada
- [x] Envases asignados a caja

---

## PARTE 3: Testing de Integración

### 3.1 Flujo Completo: Producción Halal

#### TC-INT-001: Flujo completo producción sin alcohol
| Campo | Valor |
|-------|-------|
| Prioridad | Crítica |

**Pasos:**
1. **Preparar Activo:**
   - Abrir fermentador
   - Cambiar línea productiva a "General"
   - Registrar limpieza Halal certificada

2. **Preparar Insumos:**
   - Configurar 2-3 insumos con certificación Halal
   - Verificar fechas de vencimiento futuras

3. **Crear Batch:**
   - Receta tipo "Kombucha" o "Agua fermentada"
   - Usar insumos certificados Halal
   - Asignar fermentador con limpieza Halal reciente
   - Completar campos ML opcionales

4. **Envasar:**
   - Llenar barriles o crear envases

5. **Despachar y Entregar:**
   - Crear despacho
   - Marcar como entregado

6. **Generar PDF:**
   - Generar PDF de trazabilidad

**Resultado Esperado:**
- [x] Todo el flujo completo sin errores
- [x] PDF muestra certificación Halal
- [x] PDF muestra todos los insumos con estado Halal
- [x] PDF muestra limpiezas Halal de activos
- [x] Footer indica "Certificado de Trazabilidad Halal"

---

### 3.2 Flujo de Validación Halal

#### TC-INT-002: Intento usar activo sin limpieza Halal
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |

**Pasos:**
1. Activo sin limpieza Halal reciente
2. Intentar asignar a batch de línea sin alcohol
3. Verificar advertencia (si está implementada)

**Resultado Esperado:**
- [x] Sistema permite la asignación (sin bloqueo hard)
- [x] PDF mostrará ausencia de limpieza Halal
- [x] Endpoint ajax_validarLimpiezaHalal retorna valido=false

---

## PARTE 4: Testing de Permisos

### 4.1 Permisos de Limpiezas

#### TC-PERM-001: Operario puede registrar limpieza
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |

**Pasos:**
1. Login como Operario
2. Abrir activo
3. Registrar limpieza

**Resultado Esperado:**
- [x] Limpieza registrada correctamente

#### TC-PERM-002: Cliente no puede registrar limpieza
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |

**Pasos:**
1. Login como Cliente
2. Intentar acceder a ajax_registrarLimpieza

**Resultado Esperado:**
- [x] Error "Sin permisos para registrar limpiezas"

---

## PARTE 5: Testing de Errores

### 5.1 Manejo de Datos Null

#### TC-ERR-001: Activo sin historial de limpiezas
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |

**Pasos:**
1. Abrir activo nuevo (sin limpiezas)
2. Verificar sección de historial

**Resultado Esperado:**
- [x] Tabla muestra "No hay registros de limpieza"
- [x] Sin errores JavaScript

#### TC-ERR-002: Batch sin campos ML
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |

**Pasos:**
1. Abrir batch antiguo (pre-migración)
2. Verificar sección ML

**Resultado Esperado:**
- [x] Campos muestran vacío/placeholder
- [x] Sin errores PHP

#### TC-ERR-003: PDF con datos incompletos
| Campo | Valor |
|-------|-------|
| Prioridad | Alta |

**Pasos:**
1. Generar PDF de entrega donde:
   - Batch no tiene campos ML
   - Insumos no tienen certificación Halal
   - Activos no tienen limpiezas

**Resultado Esperado:**
- [x] PDF se genera sin errores
- [x] Secciones opcionales no aparecen o muestran "N/A"

---

## Checklist Final

### Pre-Deploy
- [ ] Todas las migraciones ejecutadas exitosamente
- [ ] Backup de base de datos realizado
- [ ] Todos los tests críticos pasados
- [ ] Sin errores en logs de PHP
- [ ] Sin errores en consola JavaScript

### Post-Deploy
- [ ] Verificar acceso a todas las secciones
- [ ] Verificar generación de PDFs
- [ ] Verificar registro de limpiezas
- [ ] Monitorear logs por 24 horas

---

## Notas de Testing

### Datos de Prueba Sugeridos

**Insumo Halal:**
```
Nombre: Malta Base Pilsen (Halal)
Certificado: HALAL-2025-001
Emisor: Certificadora Halal Chile
Vencimiento: 31/12/2025
URL Ficha: https://proveedor.com/ficha-malta-pilsen.pdf
```

**Limpieza Halal:**
```
Tipo: Limpieza Halal Certificada
Certificado: HALAL-LIM-2025-001
Emisor: Certificadora Halal Chile
Procedimiento: PROC-LIM-004
```

---

## Historial de Ejecución

| Fecha | Tester | Ambiente | Resultado | Observaciones |
|-------|--------|----------|-----------|---------------|
|       |        |          |           |               |

