# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**app.mieds.cl** is a PHP-based management system (ERP) built with XAMPP. It uses an Active Record ORM pattern where all entity classes extend `Base.php`.

## Development Commands

```bash
# Local development - access via Apache (XAMPP)
http://localhost/app.mieds.cl/

# Database access
mysql -u barrcl_cocholg -p barrcl_cocholg

# View PHP errors
tail -f /Applications/XAMPP/xamppfiles/logs/error_log

# Import database schema
mysql -u barrcl_cocholg -p barrcl_cocholg < context/barrcl_cocholg.sql

# Test AJAX endpoints
curl -s "http://localhost/ajax/ajax_getNotificaciones.php" -d "id=123"
```

## Architecture

### Active Record ORM
All entity classes in `/php/classes/` extend `Base.php` which provides:
- `save()` - Auto INSERT or UPDATE based on ID
- `delete()` - Hard delete
- `getAll($conditions)` - Fetch with optional WHERE
- `tableFields()` - Auto-inspect table schema
- `setProperties($array)` - Bulk property setter
- `getRelations($relation)` / `createRelation()` / `deleteRelation()` - Many-to-many relationships
- `getMedia()` - Attached files

**Auto-loading:** Classes in `/php/classes/` auto-load via `spl_autoload_register()` in `/php/app.php`

### Template Routing
- URL pattern: `?s=template-name` loads `/templates/{section}.php`
- Function: `switch_templates($section)` with authorization check
- Auth: `Usuario::checkAutorizacion($section)` validates permissions

### AJAX Endpoints
All in `/ajax/ajax_*.php` with standard response:
```php
$response = ['status' => 'OK'|'ERROR', 'mensaje' => 'text', 'data' => $payload];
echo json_encode($response);
```

## Templates (`/templates/`)

### Template Types

| Type | Naming | Purpose |
|------|--------|---------|
| List | `{entity}.php` | DataTable listing with row click navigation |
| Detail | `detalle-{entity}.php` | View/Edit form with AJAX save |
| Create | `nuevo-{entity}.php` | Create form (sometimes with file upload) |
| Component | `components/*.php` | Reusable UI pieces |

### List Template Structure (`usuarios.php`, `clientes.php`)

```php
<?php
  $objs = Entity::getAll();  // Query data
?>
<style>.tr-entity { cursor: pointer; }</style>

<!-- Header with title + "New" button -->
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <h1 class="h3"><b>Entities</b></h1>
  <a href="./?s=nuevo-entity" class="btn btn-sm btn-primary">
    <i class="fas fa-fw fa-plus"></i> Nuevo
  </a>
</div>
<hr />

<?php Widget::printWidget('widget-name'); ?>

<!-- DataTable -->
<table class="table table-hover table-striped" id="objs-table">
  <thead><tr><th>Column</th></tr></thead>
  <tbody>
    <?php foreach($objs as $obj) { ?>
    <tr class="tr-entity" data-identity="<?= $obj->id; ?>">
      <td><?= $obj->field; ?></td>
    </tr>
    <?php } ?>
  </tbody>
</table>

<script>
new DataTable('#objs-table', {
  language: { url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json' },
  pageLength: 50, stateSave: true
});
$(document).on('click','.tr-entity',function(e){
  window.location.href = "./?s=detalle-entity&id=" + $(e.currentTarget).data('identity');
});
</script>
```

### Detail Template Structure (`detalle-usuarios.php`)

```php
<?php
  $id = validaIdExists($_GET,'id') ? $_GET['id'] : "";
  $obj = new Entity($id);
?>

<!-- Header with return button -->
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <h1 class="h3"><b><?= $obj->id ? 'Detalle' : 'Nuevo' ?> Entity</b></h1>
  <?php $GLOBALS['usuario']->printReturnBtn(); ?>
</div>
<hr />

<?php Msg::show(1,'Guardado con éxito','info'); ?>

<form id="entity-form">
  <input type="hidden" name="id" value="<?= $obj->id; ?>">
  <input type="hidden" name="entidad" value="entities">

  <div class="row">
    <div class="col-6 mb-1">Campo:</div>
    <div class="col-6 mb-1">
      <input type="text" class="form-control" name="field">
    </div>
  </div>

  <button class="btn btn-danger eliminar-obj-btn">Eliminar</button>
  <button class="btn btn-primary" id="guardar-btn">Guardar</button>
</form>

<!-- Delete Modal -->
<div class="modal fade" id="eliminar-modal">...</div>

<script>
var obj = <?= json_encode($obj); ?>;

// Populate form on load
$(document).ready(function(){
  if(obj.id!="") {
    $.each(obj,function(key,value){
      $('input[name="'+key+'"]').val(value);
      $('select[name="'+key+'"]').val(value);
    });
  }
});

// Save via AJAX
$(document).on('click','#guardar-btn',function(e){
  e.preventDefault();
  var data = getDataForm("entity");
  $.post("./ajax/ajax_guardarEntity.php", data, function(response){
    response = JSON.parse(response);
    if(response.status=="OK") {
      window.location.href = "./?s=detalle-entity&id=" + response.obj.id + "&msg=1";
    }
  });
});

// Delete
$(document).on('click','#eliminar-aceptar',function(e){
  $.post('./ajax/ajax_eliminarEntidad.php', {id: obj.id, modo: obj.table_name}, function(response){
    window.location.href = "./?s=entities&msg=2";
  },'json');
});
</script>
```

### Component Structure (`components/conversacion-interna.php`)

```php
<?php
/**
 * Component description
 *
 * Usage:
 * $variable = "value";
 * include($GLOBALS['base_dir']."/templates/components/component.php");
 *
 * Required variables: $variable
 */

// Validate required variables
if(!isset($variable)) {
  echo "<div class='alert alert-danger'>Error: Missing param</div>";
  return;
}

$unique_id = 'comp_'.md5($variable);
?>

<div id="<?= $unique_id; ?>" data-var="<?= htmlspecialchars($variable); ?>">
  <!-- Component HTML -->
</div>

<!-- Templates for JS -->
<template id="item-template">...</template>

<style>/* Scoped styles */</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  ComponentName.init('<?= $unique_id; ?>');
});
</script>
```

### JavaScript Patterns

```javascript
// Event delegation (preferred)
$(document).on('click', '.selector', function(e) { });

// Form data collection
var data = getDataForm("form-id");

// AJAX pattern
$.post(url, data, function(response){
  response = JSON.parse(response);
  if(response.status == "OK") { /* success */ }
}, 'json');

// DataTable with Spanish locale
new DataTable('#table', {
  language: { url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json' }
});
```

### CSS Patterns

- Bootstrap 5 classes: `form-control`, `btn`, `row`, `col-*`, `mb-*`
- Two-column forms: `col-6` label + `col-6` input
- Clickable rows: `.tr-{entity} { cursor: pointer; }`

## Key Systems

### Kanban Task Management
- `KanbanTablero` → `KanbanColumna` → `KanbanTarea`
- Tasks support: checklists (JSON), links (JSON), user assignments (M2M), labels (M2M)
- Drag & drop via jQuery UI Sortable

### Internal Conversations
- Comments attached to any entity via `ConversacionInterna*` classes
- Integration: Include `/templates/components/conversacion-interna.php`
- Variables: `$conversation_view_name`, `$conversation_entity_id`

### Authentication
- Session validation: `Usuario::checkSession($_SESSION)`
- Password hash: `crypt($password, "mister420")`
- User roles: Administrador, Concesionario, Visita

## Global Variables (`/php/app.php`)

```php
$GLOBALS['base_dir']    // App root directory
$GLOBALS['usuario']     // Current User object
$GLOBALS['mysqli']      // MySQL connection
$niveles_usuario        // User roles array
$tipos_de_gasto         // Expense categories
```

## Utility Functions (`/php/app.php`)

```php
incluir_template($name)              // Load template
switch_templates($section)           // Load with auth check
date2fecha($date)                    // YYYY-MM-DD → DD/MM/YYYY
fecha2date($fecha)                   // DD/MM/YYYY → YYYY-MM-DD
createObjFromTableName($table, $id)  // Generic object factory
validaIdExists($array, $key)         // Array key validation
```

## Conventions

| Item | Convention |
|------|------------|
| IDs | INT auto-increment (some use `uniqid()`) |
| Dates | MySQL: YYYY-MM-DD, Display: DD/MM/YYYY |
| Booleans | VARCHAR "1" or "0" |
| Classes | PascalCase singular (`Usuario.php`) |
| AJAX files | `ajax_camelCase.php` |
| Templates | kebab-case (`template-name.php`) |

### Creating a New Entity Class

1. Create class in `/php/classes/EntityName.php` extending `Base`
2. Create migration in `/db/migrations/NNN_create_tablename.sql`
3. **Add to `createObjFromTableName()` in `/php/app.php`**

## Database

- Connection: MySQLi direct queries
- Escaping: `addslashes()` in Base class
- Local: `barrcl_cocholg` / Production: `miedscl_prod`
- Charset: utf8mb4
- Schema: `/db/schema.sql`
- **Migrations:** All database migrations must be stored in `/db/migrations/`

### Tables (40 tables)

**Users & Permissions:**
- `usuarios` - Users (nombre, email, password, nivel, estado)
- `usuarios_niveles` - Role definitions (Administrador, Jefe de Planta, Cliente, etc.)
- `usuarios_clientes` - User-customer M2M
- `permisos` - Section permissions per user level
- `secciones` - App sections/pages
- `menus` - Navigation menu items

**Kanban System:**
- `kanban_tableros` - Boards (nombre, id_entidad, id_usuario_creador)
- `kanban_columnas` - Columns (nombre, orden, color, id_kanban_tableros)
- `kanban_tareas` - Tasks (nombre, descripcion, checklist JSON, links JSON, fecha_vencimiento)
- `kanban_tareas_usuarios` - Task-user M2M assignments
- `kanban_tareas_etiquetas` - Task-label M2M
- `kanban_etiquetas` - Labels (nombre, codigo_hex)
- `kanban_tableros_usuarios` - Board access control M2M

**Internal Conversations:**
- `conversaciones_internas` - Threads (nombre_vista, id_entidad)
- `conversaciones_internas_comentarios` - Comments (contenido, likes JSON)
- `conversaciones_internas_archivos` - File attachments
- `conversaciones_internas_tags` - User @mentions

**Business:**
- `clientes` - Customers (nombre, RUT, RznSoc, Giro - Chilean business fields)
- `gastos` - Expenses (monto, tipo_de_gasto, date_vencimiento)
- `pagos` - Payments (Transbank integration)
- `transacciones` - Financial transactions
- `documentos` - Invoices/documents

**Media:**
- `media` - File records (nombre, url, tipo)
- `media_gastos`, `media_kanban_tareas`, `media_configuraciones` - Junction tables

**System:**
- `configuraciones` - System settings (nombre_empresa, email_empresa, etc.)
- `notificaciones` - User notifications
- `notificaciones_usuarios_niveles` - Notification settings per role
- `historial` - Audit log
- `alertas` - System alerts
- `tareas` / `tareas_comentarios` - Legacy task system
- `locaciones`, `mailing`, `sugerencias`, `visitas`, `errores`

### User Roles (from `usuarios_niveles`)

| ID | Role | Description |
|----|------|-------------|
| 10 | Administrador | Full system access |
| 1 | Jefe de Planta | Production management |
| 5 | Jefe de Cocina | Kitchen/recipe management |
| 7 | Cliente | Customer portal |
| 6 | Repartidor | Delivery management |
| 9 | Vendedor | Sales |
| 2 | Operario | Operations |
| 8 | Visita | Read-only visitor |

### Key Relationships

```
kanban_tableros (1) → (N) kanban_columnas (1) → (N) kanban_tareas
kanban_tareas (N) ↔ (M) usuarios (via kanban_tareas_usuarios)
kanban_tareas (N) ↔ (M) kanban_etiquetas (via kanban_tareas_etiquetas)
kanban_tableros (N) ↔ (M) usuarios (via kanban_tableros_usuarios)

conversaciones_internas (1) → (N) comentarios (1) → (N) archivos
                                            ↓
                                      (N) tags → usuarios

permisos links secciones ↔ usuarios_niveles
```

## Frontend Stack

- Bootstrap 5, jQuery 1.12.4, jQuery UI 1.12.1
- DataTables 2.1.4, CKEditor
- Dark mode: localStorage `bsTheme`, toggle `#darkModeSwitch`

## Third-Party Integrations

- **Transbank**: Payment gateway (`/vendor_php/transbank/`)
- **LibreDTE**: Chilean electronic invoicing (`/vendor_php/libredte-lib-master/`)
- **DOMPDF**: PDF generation (`/vendor_php/dompdf/`)
