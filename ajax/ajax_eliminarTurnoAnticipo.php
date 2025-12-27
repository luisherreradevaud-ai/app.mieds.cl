<?php
require_once("../php/app.php");

header('Content-Type: application/json');

try {
  if(!isset($_POST['id']) || $_POST['id'] == '') {
    throw new Exception('ID no proporcionado');
  }

  $anticipo = new TurnoAnticipo($_POST['id']);

  if($anticipo->id == "") {
    throw new Exception('Registro no encontrado');
  }

  if(!$anticipo->canEdit()) {
    throw new Exception('El registro no puede ser eliminado');
  }

  $anticipo->softDelete();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Anticipo eliminado exitosamente'
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
