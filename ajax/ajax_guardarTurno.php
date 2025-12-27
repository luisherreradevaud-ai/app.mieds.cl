<?php
require_once("../php/app.php");

header('Content-Type: application/json');

try {
  $turno = new Turno($_POST['id'] ?? null);

  // Check if turno can be edited
  if($turno->id != "" && !$turno->canEdit()) {
    echo json_encode(array(
      'status' => 'ERROR',
      'mensaje' => 'El turno no puede ser modificado en su estado actual'
    ));
    exit;
  }

  // Set properties from POST
  $turno->setPropertiesNoId($_POST);

  // Set creator if new
  if($turno->id == "") {
    $turno->creado_por = $GLOBALS['usuario']->id;
    $turno->estado = 'Abierto';
  }

  // Save
  $turno->save();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Turno guardado exitosamente',
    'id' => $turno->id,
    'obj' => $turno
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
