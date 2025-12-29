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

  $donacion = new TurnoDonacion($_POST['id'] ?? null);
  $donacion->setPropertiesNoId($_POST);
  $donacion->id_turnos = $_POST['id_turnos'];

  if($donacion->id == "") {
    $donacion->creado_por = $GLOBALS['usuario']->id;
  }

  $donacion->save();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Donación guardada exitosamente',
    'id' => $donacion->id
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
