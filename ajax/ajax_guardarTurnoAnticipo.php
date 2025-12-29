<?php
require_once("../php/app.php");

// Inicializar sesión y usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuario = new Usuario;
$usuario->checkSession($_SESSION);
$GLOBALS['usuario'] = $usuario;

header('Content-Type: application/json');

try {
  if(!isset($_POST['id_turnos']) || $_POST['id_turnos'] == '') {
    throw new Exception('ID de turno no proporcionado');
  }

  $turno = new Turno($_POST['id_turnos']);
  if($turno->id == "" || !$turno->canEdit()) {
    throw new Exception('El turno no puede ser modificado');
  }

  $anticipo = new TurnoAnticipo($_POST['id'] ?? null);
  $anticipo->setPropertiesNoId($_POST);
  $anticipo->id_turnos = $_POST['id_turnos'];

  if($anticipo->id == "") {
    $anticipo->creado_por = $GLOBALS['usuario']->id;
    $anticipo->autorizado_por = $GLOBALS['usuario']->id;
  }

  $anticipo->save();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Anticipo guardado exitosamente',
    'id' => $anticipo->id
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
