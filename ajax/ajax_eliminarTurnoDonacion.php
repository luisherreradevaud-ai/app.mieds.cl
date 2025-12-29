<?php
require_once("../php/app.php");

header('Content-Type: application/json');

try {
  if(!isset($_POST['id']) || $_POST['id'] == '') {
    throw new Exception('ID no proporcionado');
  }

  $donacion = new TurnoDonacion($_POST['id']);

  if($donacion->id == "") {
    throw new Exception('Registro no encontrado');
  }

  if(!$donacion->canEdit()) {
    throw new Exception('El registro no puede ser eliminado');
  }

  $donacion->softDelete();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Donación eliminada exitosamente'
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
