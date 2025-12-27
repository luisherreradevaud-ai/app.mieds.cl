<?php
require_once("../php/app.php");

header('Content-Type: application/json');

try {
  if(!isset($_POST['id']) || $_POST['id'] == '') {
    throw new Exception('ID no proporcionado');
  }

  $gasto = new TurnoGastoCajaChica($_POST['id']);

  if($gasto->id == "") {
    throw new Exception('Registro no encontrado');
  }

  if(!$gasto->canEdit()) {
    throw new Exception('El registro no puede ser eliminado');
  }

  $gasto->softDelete();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Gasto eliminado exitosamente'
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
