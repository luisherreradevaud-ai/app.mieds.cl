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

  $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
  $userId = $GLOBALS['usuario']->id;

  $result = $turno->approve($userId, $observaciones);

  echo json_encode($result);

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
