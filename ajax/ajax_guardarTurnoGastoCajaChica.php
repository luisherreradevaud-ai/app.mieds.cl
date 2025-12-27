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

  $gasto = new TurnoGastoCajaChica($_POST['id'] ?? null);
  $gasto->setPropertiesNoId($_POST);
  $gasto->id_turnos = $_POST['id_turnos'];

  if($gasto->id == "") {
    $gasto->creado_por = $GLOBALS['usuario']->id;
  }

  $gasto->save();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Gasto guardado exitosamente',
    'id' => $gasto->id
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
