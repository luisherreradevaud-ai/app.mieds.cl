<?php

  if($_GET == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!isset($_GET['id'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "ID de tarea requerido";
    print json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    die();
  }

  try {
    $tarea = new KanbanTarea($_GET['id']);

    if(!$tarea->id) {
      $response["status"] = "ERROR";
      $response["mensaje"] = "Tarea no encontrada";
    } else {
      // Get all users
      $usuarios_arr = Usuario::getAll("ORDER BY nombre ASC");
      $usuarios = array();
      foreach($usuarios_arr as $user) {
        $usuarios[] = array(
          'id' => $user->id,
          'nombre' => $user->nombre,
          'email' => $user->email
        );
      }

      // Get all labels
      $etiquetas_arr = KanbanEtiqueta::getTodasLasEtiquetas();
      $etiquetas = array();
      foreach($etiquetas_arr as $etiqueta) {
        $etiquetas[] = array(
          'id' => $etiqueta->id,
          'nombre' => $etiqueta->nombre,
          'codigo_hex' => $etiqueta->codigo_hex
        );
      }

      $response["status"] = "OK";
      $response["mensaje"] = "OK";
      $response["tarea"] = $tarea->toArray();
      $response["usuarios"] = $usuarios;
      $response["etiquetas"] = $etiquetas;
    }

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al obtener tarea: " . $e->getMessage();
    error_log("Error en ajax_getTarea.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

?>
