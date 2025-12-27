<?php
require_once("../php/app.php");

header('Content-Type: application/json');

try {
  if(!isset($_POST['id_turnos']) || $_POST['id_turnos'] == '') {
    throw new Exception('ID de turno no proporcionado');
  }

  $turno = new Turno($_POST['id_turnos']);
  if($turno->id == "" || !$turno->canEdit()) {
    throw new Exception('El turno no puede ser modificado');
  }

  $faltante = new TurnoFaltante($_POST['id'] ?? null);
  $faltante->setPropertiesNoId($_POST);
  $faltante->id_turnos = $_POST['id_turnos'];

  if($faltante->id == "") {
    $faltante->creado_por = $GLOBALS['usuario']->id;
  }

  $faltante->save();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Faltante guardado exitosamente',
    'id' => $faltante->id
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
