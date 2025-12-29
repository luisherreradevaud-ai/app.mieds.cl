<?php
require_once("../php/app.php");

header('Content-Type: application/json');

try {
  if(!isset($_POST['id']) || $_POST['id'] == '') {
    throw new Exception('ID no proporcionado');
  }

  $comision = new TurnoComision($_POST['id']);

  if($comision->id == "") {
    throw new Exception('Registro no encontrado');
  }

  // Check if the parent turno can be edited
  $turno = $comision->getTurno();
  if(!$turno || !$turno->canEdit()) {
    throw new Exception('El registro no puede ser eliminado');
  }

  $comision->softDelete();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Comisión eliminada exitosamente'
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
