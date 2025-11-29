<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession();

  try {
    // Determine if creating or updating
    $accion = "creada";
    if(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) {
      $tarea = new KanbanTarea($_POST['id']);
      $accion = "modificada";

      // Verificar permisos al editar
      if($tarea->id) {
        $tablero = $tarea->getTablero();
        if($tablero && !$tablero->usuarioTieneAcceso($usuario->id)) {
          throw new Exception("No tienes permisos para editar esta tarea");
        }
      }
    } else {
      $tarea = new KanbanTarea();
    }

    // Only update fields that are provided
    if(isset($_POST['nombre'])) {
      $tarea->nombre = $_POST['nombre'];
    }
    if(isset($_POST['descripcion'])) {
      $tarea->descripcion = $_POST['descripcion'];
    }
    if(isset($_POST['id_kanban_columnas'])) {
      $tarea->id_kanban_columnas = $_POST['id_kanban_columnas'];
    }
    if(isset($_POST['orden'])) {
      $tarea->orden = $_POST['orden'];
    }
    if(isset($_POST['fecha_inicio'])) {
      $tarea->fecha_inicio = $_POST['fecha_inicio'];
    }
    if(isset($_POST['fecha_vencimiento'])) {
      $tarea->fecha_vencimiento = $_POST['fecha_vencimiento'];
    }
    if(isset($_POST['recordatorio_vencimiento'])) {
      $tarea->recordatorio_vencimiento = $_POST['recordatorio_vencimiento'];
    }
    if(isset($_POST['estado'])) {
      $tarea->estado = $_POST['estado'];
    }
    if(isset($_POST['time_elapsed'])) {
      $tarea->time_elapsed = $_POST['time_elapsed'];
    }

    // Handle checklist (expects JSON string or array)
    if(isset($_POST['checklist'])) {
      if(is_string($_POST['checklist'])) {
        $tarea->checklist = json_decode($_POST['checklist'], true);
      } else {
        $tarea->checklist = $_POST['checklist'];
      }
    }

    // Handle links (expects JSON string or array)
    if(isset($_POST['links'])) {
      if(is_string($_POST['links'])) {
        $tarea->links = json_decode($_POST['links'], true);
      } else {
        $tarea->links = $_POST['links'];
      }
    }

    // Verificar permisos al crear nueva tarea
    if(!$tarea->id && $tarea->id_kanban_columnas) {
      $columna = new KanbanColumna($tarea->id_kanban_columnas);
      if($columna->id) {
        $tablero = $columna->getTablero();
        if($tablero && !$tablero->usuarioTieneAcceso($usuario->id)) {
          throw new Exception("No tienes permisos para crear tareas en este tablero");
        }
      }
    }

    // Save the task
    $tarea->save();

    // Handle users and labels through setSpecifics
    if(isset($_POST['usuarios']) || isset($_POST['etiquetas'])) {
      $tarea->setSpecifics($_POST);
    }

    // Log action
    Historial::guardarAccion("Kanban Tarea #".$tarea->id." ".$accion.".", $usuario);

    $response["status"] = "OK";
    $response["mensaje"] = "Tarea guardada correctamente";
    $response["tarea"] = $tarea->toArray();

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al guardar tarea: " . $e->getMessage();
    error_log("Error en ajax_guardarTarea.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

?>
