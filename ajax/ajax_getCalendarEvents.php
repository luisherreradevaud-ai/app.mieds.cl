<?php

require_once("../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession();

// Get date range from FullCalendar
$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-t');

$events = array();

// ====================
// GASTOS (Expenses)
// ====================
if($usuario->nivel == "Administrador" || $usuario->nivel == "Jefe de Planta") {
  $gastos = Gasto::getAll("WHERE estado!='Pagado' AND date_vencimiento BETWEEN '".$start."' AND '".$end."' ORDER BY date_vencimiento");

  if(is_array($gastos)) {
    foreach($gastos as $gasto) {
      if(is_object($gasto) && $gasto->date_vencimiento && $gasto->date_vencimiento != '0000-00-00') {

        // Truncate item name if too long
        $item_name = $gasto->item;
        if(strlen($item_name) > 30) {
          $item_name = substr($item_name, 0, 27) . '...';
        }

        $events[] = array(
          'id' => 'gasto_' . $gasto->id,
          'title' => '💰 ' . $item_name,
          'start' => $gasto->date_vencimiento,
          'backgroundColor' => '#dc3545',
          'borderColor' => '#dc3545',
          'textColor' => '#ffffff',
          'url' => './?s=detalle-gastos&id=' . $gasto->id,
          'extendedProps' => array(
            'tipo' => 'gasto',
            'monto' => $gasto->monto,
            'tipo_gasto' => $gasto->tipo_de_gasto,
            'descripcion' => $gasto->item
          )
        );
      }
    }
  }
}

// ====================
// TAREAS KANBAN (Tasks)
// ====================

// Get all Kanban boards where user has access
// Users (including Admins) have access if they are:
// 1. Creator (id_usuario_creador)
// 2. Assigned (kanban_tableros_usuarios table)

// All users (including Admins) only see tasks from tableros they created or are assigned to
$query = "SELECT DISTINCT kt.* FROM kanban_tareas kt
          INNER JOIN kanban_columnas kc ON kt.id_kanban_columnas = kc.id
          INNER JOIN kanban_tableros ktb ON kc.id_kanban_tableros = ktb.id
          LEFT JOIN kanban_tableros_usuarios ktu ON ktb.id = ktu.id_kanban_tableros
          WHERE (ktb.id_usuario_creador = '".$usuario->id."' OR ktu.id_usuarios = '".$usuario->id."')
          AND kt.fecha_vencimiento BETWEEN '".$start."' AND '".$end."'
          AND kt.fecha_vencimiento != '0000-00-00'
          AND kt.estado != 'Completada'
          ORDER BY kt.fecha_vencimiento";

$result = $GLOBALS['mysqli']->query($query);

if($result) {
  while($row = $result->fetch_assoc()) {
    $tarea = new KanbanTarea($row['id']);

    // Get assigned users
    $usuarios_asignados = $tarea->getUsuarios(); // Returns array of user IDs
    $usuarios_nombres = array();
    foreach($usuarios_asignados as $user_id) {
      $user = new Usuario($user_id);
      if($user->id) {
        $usuarios_nombres[] = trim($user->nombre . ' ' . $user->apellido);
      }
    }

    // Get the tablero ID from the columna
    $columna = new KanbanColumna($tarea->id_kanban_columnas);
    $tablero_id = $columna->id_kanban_tableros;

    // Truncate task name if too long
    $task_name = $tarea->nombre;
    if(strlen($task_name) > 35) {
      $task_name = substr($task_name, 0, 32) . '...';
    }

    // Check if task is overdue
    $is_overdue = $tarea->isVencida();
    $color = $is_overdue ? '#ffc107' : '#0dcaf0'; // Yellow if overdue, cyan if not

    $events[] = array(
      'id' => 'tarea_' . $tarea->id,
      'title' => '✓ ' . $task_name,
      'start' => $tarea->fecha_vencimiento,
      'backgroundColor' => $color,
      'borderColor' => $color,
      'textColor' => '#000000',
      'url' => './?s=tablero-kanban&id=' . $tablero_id . '&id_tarea=' . $tarea->id,
      'extendedProps' => array(
        'tipo' => 'tarea',
        'estado' => $tarea->estado,
        'vencida' => $is_overdue,
        'descripcion' => $tarea->nombre,
        'tarea_id' => $tarea->id,
        'tablero_id' => $tablero_id,
        'usuarios_ids' => $usuarios_asignados,
        'usuarios_nombres' => $usuarios_nombres
      )
    );
  }
}

// ====================
// BATCHES (Optional - for Admin/Jefe de Cocina)
// ====================
if($usuario->nivel == "Jefe de Cocina" || $usuario->nivel == "Administrador") {
  $batches = Batch::getAll("WHERE (fecha_inicio BETWEEN '".$start."' AND '".$end."' OR fecha_termino BETWEEN '".$start."' AND '".$end."') AND (fecha_inicio != '0000-00-00' OR fecha_termino != '0000-00-00')");

  if(is_array($batches)) {
    foreach($batches as $batch) {
      if(is_object($batch)) {

        // Add batch start event
        if($batch->fecha_inicio && $batch->fecha_inicio != '0000-00-00') {
          $events[] = array(
            'id' => 'batch_inicio_' . $batch->id,
            'title' => '🍺 Inicio Batch #' . $batch->id,
            'start' => $batch->fecha_inicio,
            'backgroundColor' => '#198754',
            'borderColor' => '#198754',
            'textColor' => '#ffffff',
            'url' => './?s=detalle-batch&id=' . $batch->id,
            'extendedProps' => array(
              'tipo' => 'batch_inicio',
              'batch_id' => $batch->id
            )
          );
        }

        // Add batch end event
        if($batch->fecha_termino && $batch->fecha_termino != '0000-00-00') {
          $events[] = array(
            'id' => 'batch_termino_' . $batch->id,
            'title' => '🍺 Término Batch #' . $batch->id,
            'start' => $batch->fecha_termino,
            'backgroundColor' => '#6c757d',
            'borderColor' => '#6c757d',
            'textColor' => '#ffffff',
            'url' => './?s=detalle-batch&id=' . $batch->id,
            'extendedProps' => array(
              'tipo' => 'batch_termino',
              'batch_id' => $batch->id
            )
          );
        }
      }
    }
  }
}

// ====================
// GET AVAILABLE USERS (for task assignment)
// ====================
$usuarios_arr = Usuario::getAll("WHERE nivel != 'Cliente' AND estado != 'Bloqueado' ORDER BY nombre ASC");
$usuarios = array();

foreach($usuarios_arr as $user) {
  if(is_object($user)) {
    $usuarios[] = array(
      'id' => $user->id,
      'nombre' => $user->nombre,
      'apellido' => $user->apellido,
      'email' => $user->email,
      'nivel' => $user->nivel
    );
  }
}

// Build response
$response = array(
  'events' => $events,
  'usuarios' => $usuarios
);

// Return JSON for FullCalendar
header('Content-Type: application/json');
echo json_encode($response);

?>
