# MIEDS - Sistema de Gestion

Sistema de gestion empresarial desarrollado en PHP para administracion de turnos, atendedores y operaciones de estaciones de servicio.

## Stack Tecnologico

- **Backend**: PHP 8.x
- **Base de Datos**: MySQL/MariaDB
- **Frontend**: Bootstrap 5, jQuery, DataTables
- **Servidor**: Apache (XAMPP)

## Estructura del Proyecto

```
/
├── ajax/                    # Endpoints AJAX
├── css/                     # Estilos CSS
├── db/
│   ├── migrations/          # Migraciones de base de datos
│   └── schema.sql           # Esquema completo
├── js/                      # JavaScript y librerias
├── media/                   # Archivos subidos
├── php/
│   ├── app.php              # Bootstrap, configuracion, funciones utilitarias
│   └── classes/             # Clases del modelo (Active Record)
├── templates/               # Vistas PHP
│   └── components/          # Componentes reutilizables
├── vendor_php/              # Dependencias PHP
├── index.php                # Punto de entrada principal
└── login.php                # Autenticacion
```

## Instalacion

### Requisitos
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache con mod_rewrite
- XAMPP (recomendado para desarrollo)

### Pasos

1. Clonar el repositorio:
```bash
git clone https://github.com/luisherreradevaud-ai/app.mieds.cl.git
```

2. Configurar la base de datos en `/php/app.php`:
```php
$mysqli_host = "localhost";
$mysqli_user = "tu_usuario";
$mysqli_pass = "tu_password";
$mysqli_db = "tu_base_de_datos";
```

3. Ejecutar las migraciones:
```bash
mysql -u usuario -p base_de_datos < db/schema.sql
mysql -u usuario -p base_de_datos < db/migrations/001_create_atendedores.sql
mysql -u usuario -p base_de_datos < db/migrations/002_create_turnos.sql
```

4. Configurar Apache para apuntar al directorio del proyecto.

5. Acceder via `http://localhost/`

## Modulos Principales

### Atendedores
Gestion de personal con datos personales, laborales y credenciales (ID Tarjeta Copec, ID MAE, Clave MAE).

### Turnos
Sistema completo de gestion de turnos con:
- **Conteo de efectivo**: Billetes ($20k, $10k, $5k, $2k, $1k) y monedas ($500, $100, $50, $10)
- **Faltantes**: Registro de faltantes de efectivo o productos
- **Anticipos**: Adelantos de sueldo a atendedores
- **Facturas a Credito**: Facturas pendientes de pago
- **Ingresos PROSEGUR MAE**: Registro de ingresos por recaudacion
- **Gastos Caja Chica**: Gastos menores y donaciones
- **Comisiones**: Sistema de comisiones mensuales pooled

#### Flujo de Estados del Turno
```
Abierto → Cerrado (Admin) → Aprobado (Concesionario)
```

## Arquitectura

### Patron Active Record
Todas las entidades extienden de `Base.php` que proporciona:
- `save()` - Insertar/actualizar
- `delete()` - Eliminar
- `getAll()` - Obtener todos los registros
- `tableFields()` - Introspeccion del esquema
- `setProperties()` - Setter masivo de propiedades
- `getRelations()` - Manejo de relaciones many-to-many

### Convencion de Clases
```php
// Crear nueva instancia
$turno = new Turno($id);

// Guardar
$turno->fecha = date('Y-m-d');
$turno->save();

// Obtener todos
$turnos = Turno::getAll("WHERE estado='Abierto' ORDER BY fecha DESC");
```

### Enrutamiento
URL pattern: `?s=template-name`
- `?s=turnos` → `/templates/turnos.php`
- `?s=detalle-turnos&id=123` → `/templates/detalle-turnos.php`

## Endpoints AJAX

Todos los endpoints retornan JSON con el formato:
```json
{
  "status": "OK",
  "mensaje": "Mensaje descriptivo",
  "data": {}
}
```

### Turnos
| Endpoint | Descripcion |
|----------|-------------|
| `ajax_guardarTurno.php` | Guardar turno |
| `ajax_eliminarTurno.php` | Eliminar turno |
| `ajax_cerrarTurno.php` | Cerrar turno |
| `ajax_aprobarTurno.php` | Aprobar turno |
| `ajax_reabrirTurno.php` | Reabrir turno |

### Items Relacionados
- `ajax_guardarTurnoFaltante.php` / `ajax_eliminarTurnoFaltante.php`
- `ajax_guardarTurnoAnticipo.php` / `ajax_eliminarTurnoAnticipo.php`
- `ajax_guardarTurnoFacturaCredito.php` / `ajax_eliminarTurnoFacturaCredito.php`
- `ajax_guardarTurnoIngresoProsegur.php` / `ajax_eliminarTurnoIngresoProsegur.php`
- `ajax_guardarTurnoGastoCajaChica.php` / `ajax_eliminarTurnoGastoCajaChica.php`

## Roles de Usuario

| Rol | Descripcion |
|-----|-------------|
| Administrador | Acceso completo al sistema |
| Jefe de Planta | Gestion de operaciones |
| Operario | Entrada de datos |
| Visita | Acceso solo lectura |

## Desarrollo

### Crear Nueva Entidad

1. Crear clase en `/php/classes/NuevaEntidad.php`:
```php
<?php
class NuevaEntidad extends Base {
  public $id = "";
  public $nombre = "";
  // ... propiedades

  function __construct($id = null) {
    $this->tableName("nueva_entidad");
    if($id) {
      $this->id = $id;
      $this->getFromDB();
    }
  }
}
```

2. Crear migracion en `/db/migrations/`:
```sql
CREATE TABLE nueva_entidad (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255),
  -- ...
);
```

3. Agregar a `createObjFromTableName()` en `/php/app.php`

4. Crear templates en `/templates/`

5. Crear endpoints AJAX en `/ajax/`

### Validar Sintaxis PHP
```bash
php -l archivo.php
```

## Licencia

Proyecto privado. Todos los derechos reservados.
