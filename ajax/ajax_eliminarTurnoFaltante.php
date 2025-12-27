<?php
require_once("../php/app.php");

header('Content-Type: application/json');

try {
  if(!isset($_POST['id']) || $_POST['id'] == '') {
    throw new Exception('ID no proporcionado');
  }

  $faltante = new TurnoFaltante($_POST['id']);

  if($faltante->id == "") {
    throw new Exception('Registro no encontrado');
  }

  if(!$faltante->canEdit()) {
    throw new Exception('El registro no puede ser eliminado');
  }

  $faltante->softDelete();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Faltante eliminado exitosamente'
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
