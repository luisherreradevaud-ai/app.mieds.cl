<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  try {
    // Determine if creating or updating
    $accion = "creada";
    if(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) {
      $tarea = new KanbanTarea($_POST['id']);
      $accion = "modificada";
    } else {
      $tarea = new KanbanTarea();
    }

    // Set basic properties
    $tarea->nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $tarea->descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    $tarea->id_kanban_columnas = isset($_POST['id_kanban_columnas']) ? $_POST['id_kanban_columnas'] : 0;
    $tarea->orden = isset($_POST['orden']) ? $_POST['orden'] : 0;
    $tarea->fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
    $tarea->fecha_vencimiento = isset($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : null;
    $tarea->recordatorio_vencimiento = isset($_POST['recordatorio_vencimiento']) ? $_POST['recordatorio_vencimiento'] : null;
    $tarea->estado = isset($_POST['estado']) ? $_POST['estado'] : 'Pendiente';

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

    // Save the task
    $tarea->save();

    // Handle users and labels through setSpecifics
    $tarea->setSpecifics($_POST);

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
