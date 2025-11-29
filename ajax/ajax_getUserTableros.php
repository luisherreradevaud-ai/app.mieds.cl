<?php

require_once("../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession();

try {
  // Get all tableros where user is creator or assigned
  $all_tableros = KanbanTablero::getAll("ORDER BY actualizada DESC");
  $user_tableros = array();

  foreach($all_tableros as $tablero) {
    // Check if user has access to this tablero
    if($tablero->usuarioTieneAcceso($usuario->id)) {
      $user_tableros[] = array(
        'id' => $tablero->id,
        'nombre' => $tablero->nombre,
        'descripcion' => $tablero->descripcion,
        'id_usuario_creador' => $tablero->id_usuario_creador
      );
    }
  }

  $response["status"] = "OK";
  $response["mensaje"] = "Tableros obtenidos correctamente";
  $response["tableros"] = $user_tableros;

} catch (Exception $e) {
  $response["status"] = "ERROR";
  $response["mensaje"] = "Error al obtener tableros: " . $e->getMessage();
  error_log("Error en ajax_getUserTableros.php: " . $e->getMessage());
}

header('Content-Type: application/json');
print json_encode($response, JSON_PRETTY_PRINT);

?>
