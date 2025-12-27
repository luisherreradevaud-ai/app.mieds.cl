<?php
require_once("../php/app.php");

header('Content-Type: application/json');

try {
  if(!isset($_POST['id']) || $_POST['id'] == '') {
    throw new Exception('ID no proporcionado');
  }

  $turno = new Turno($_POST['id']);

  if($turno->id == "") {
    throw new Exception('Turno no encontrado');
  }

  // Only allow delete if turno is open
  if(!$turno->canEdit()) {
    throw new Exception('El turno no puede ser eliminado en su estado actual');
  }

  // Soft delete
  $turno->softDelete();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Turno eliminado exitosamente'
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
