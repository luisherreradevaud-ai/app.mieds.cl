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
      // Verificar permisos
      $tablero = $tarea->getTablero();
      if($tablero && !$tablero->usuarioTieneAcceso($usuario->id)) {
        throw new Exception("No tienes permisos para ver esta tarea");
      }

      // Get all users
      $usuarios_arr = Usuario::getAll("WHERE nivel!='Cliente' ORDER BY nombre ASC");
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

      // Get task files
      $media_arr = $tarea->getMedia();
      $archivos = array();
      if(is_array($media_arr)) {
        foreach($media_arr as $media) {
          if($media['id'] != 0) {
            $archivos[] = array(
              'id' => $media['id'],
              'nombre' => $media['nombre'],
              'url' => './media/images/' . $media['url'],
              'tipo' => $media['tipo'],
              'descripcion' => $media['descripcion']
            );
          }
        }
      }

      $response["status"] = "OK";
      $response["mensaje"] = "OK";
      $response["tarea"] = $tarea->toArray();
      $response["usuarios"] = $usuarios;
      $response["etiquetas"] = $etiquetas;
      $response["archivos"] = $archivos;
    }

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al obtener tarea: " . $e->getMessage();
    error_log("Error en ajax_getTarea.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

?>
