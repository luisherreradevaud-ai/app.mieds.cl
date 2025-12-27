<?php
require_once("../php/app.php");

header('Content-Type: application/json');

try {
  if(!isset($_POST['id']) || $_POST['id'] == '') {
    throw new Exception('ID no proporcionado');
  }

  $ingreso = new TurnoIngresoProsegur($_POST['id']);

  if($ingreso->id == "") {
    throw new Exception('Registro no encontrado');
  }

  if(!$ingreso->canEdit()) {
    throw new Exception('El registro no puede ser eliminado');
  }

  $ingreso->softDelete();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Ingreso PROSEGUR eliminado exitosamente'
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
