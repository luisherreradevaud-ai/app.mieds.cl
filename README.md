# Barril.cl - Brewery Management System

Sistema integral de gestión para cervecería artesanal (ERP) desarrollado para Cerveza Cocholgue, Chile.

## Descripción

**Barril.cl** es una aplicación web PHP que gestiona el ciclo completo de producción y distribución de cerveza artesanal, desde la elaboración del batch hasta la entrega al cliente final. El sistema proporciona trazabilidad completa de productos en barriles, latas y botellas.

## Características Principales

### Producción
- **Gestión de Batches**: Control de producción desde fermentación hasta maduración
- **Recetas**: Base de datos de recetas con ingredientes y procesos
- **Fermentadores**: Tracking de activos y transferencias entre tanques
- **Insumos**: Control de inventario de materias primas

### Envasado y Empaque
- **Barriles**: Llenado, despacho y devolución con trazabilidad completa
- **Envases (Latas/Botellas)**: Sistema de envasado individual con trazabilidad
- **Cajas**: Creación de cajas simples o mixtas (múltiples recetas)
- **Formatos Configurables**: 473ml, 330ml, etc.

### Distribución
- **Despachos**: Gestión de envíos con asignación de repartidor
- **Entregas**: Registro de entregas con generación de documentos tributarios
- **Devoluciones**: Control de retorno de barriles

### Facturación
- **LibreDTE**: Integración con SII para facturación electrónica
- **Transbank**: Integración con WebPay para pagos

### Gestión
- **Clientes**: CRM con historial de compras y barriles en terreno
- **Usuarios**: 8 niveles de permisos (Administrador, Jefe de Planta, etc.)
- **Gastos**: Control de gastos fijos y variables
- **Calendario**: Vista unificada de tareas, gastos y batches
- **Kanban**: Sistema de gestión de tareas

## Stack Tecnológico

| Componente | Tecnología |
|------------|------------|
| Backend | PHP 7.x |
| Base de Datos | MySQL 5.7+ |
| Frontend | Bootstrap 5, jQuery, jQuery UI |
| Servidor | Apache (XAMPP) |
| Calendario | FullCalendar 6.1.10 |
| Tablas | DataTables 2.1.4 |
| Pagos | Transbank WebPay Plus |
| Facturación | LibreDTE |

## Estructura del Proyecto

```
/
├── ajax/                 # 101 endpoints AJAX
├── css/                  # Estilos Bootstrap 5
├── js/                   # JavaScript (jQuery, plugins)
├── media/                # Archivos subidos
├── php/
│   ├── app.php           # Bootstrap y configuración
│   └── classes/          # 102 clases (Active Record ORM)
├── templates/            # 177 vistas PHP/HTML
│   └── components/       # Componentes reutilizables
├── vendor_php/           # Dependencias (Transbank, LibreDTE)
├── index.php             # Punto de entrada principal
└── login.php             # Autenticación
```

## Flujo de Producción

### Barriles
```
Batch → Fermentador → Llenado Barril → Despacho → Entrega → Devolución
```

### Envases
```
Fermentador/Barril → Envasado → Caja → Despacho → Entrega
```

## Instalación

### Requisitos
- XAMPP (Apache + MySQL + PHP)
- PHP 7.4+
- MySQL 5.7+

### Pasos
1. Clonar repositorio en `/Applications/XAMPP/htdocs/app.barril.cl/`
2. Importar base de datos desde `/context/barrcl_cocholg.sql`
3. Configurar credenciales en `/php/app.php`
4. Acceder a `http://localhost/app.barril.cl/`

## Documentación

| Archivo | Descripción |
|---------|-------------|
| `CLAUDE.md` | Guía de desarrollo para Claude Code |
| `QA_FLUJO_TRAZABILIDAD.md` | Análisis QA del flujo de producción |
| `TRAZABILIDAD_SISTEMA.md` | Documentación de trazabilidad |
| `PLAN_CAJAS_MIXTAS.md` | Plan de implementación de cajas mixtas |

## Roles de Usuario

| Nivel | Descripción |
|-------|-------------|
| Administrador | Acceso total al sistema |
| Jefe de Planta | Gestión de producción |
| Cliente | Portal de cliente (pedidos, facturas) |
| Jefe de Cocina | Ejecución de recetas y batches |
| Operario | Entrada de datos, movimientos |
| Repartidor | Gestión de entregas |
| Vendedor | Ventas y clientes |
| Visita | Solo lectura |

## Contexto Chileno

- **Moneda**: Peso Chileno (CLP)
- **RUT**: Identificación tributaria chilena
- **SII**: Servicio de Impuestos Internos
- **DTE**: Documentos Tributarios Electrónicos

## Licencia

Proyecto privado - Cerveza Cocholgue

---

*Última actualización: 2025-11-29*
