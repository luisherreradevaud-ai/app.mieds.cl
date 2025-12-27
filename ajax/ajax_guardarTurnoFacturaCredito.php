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

  $factura = new TurnoFacturaCredito($_POST['id'] ?? null);
  $factura->setPropertiesNoId($_POST);
  $factura->id_turnos = $_POST['id_turnos'];

  if($factura->id == "") {
    $factura->creado_por = $GLOBALS['usuario']->id;
  }

  $factura->save();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Factura guardada exitosamente',
    'id' => $factura->id
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
