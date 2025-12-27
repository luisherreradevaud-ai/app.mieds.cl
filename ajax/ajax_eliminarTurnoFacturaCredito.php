<?php
require_once("../php/app.php");

header('Content-Type: application/json');

try {
  if(!isset($_POST['id']) || $_POST['id'] == '') {
    throw new Exception('ID no proporcionado');
  }

  $factura = new TurnoFacturaCredito($_POST['id']);

  if($factura->id == "") {
    throw new Exception('Registro no encontrado');
  }

  if(!$factura->canEdit()) {
    throw new Exception('El registro no puede ser eliminado');
  }

  $factura->softDelete();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Factura eliminada exitosamente'
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
