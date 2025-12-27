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

  $ingreso = new TurnoIngresoProsegur($_POST['id'] ?? null);
  $ingreso->setPropertiesNoId($_POST);
  $ingreso->id_turnos = $_POST['id_turnos'];

  if($ingreso->id == "") {
    $ingreso->creado_por = $GLOBALS['usuario']->id;
  }

  $ingreso->save();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Ingreso PROSEGUR guardado exitosamente',
    'id' => $ingreso->id
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
