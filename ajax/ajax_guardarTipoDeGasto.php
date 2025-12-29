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
  if(!isset($_POST['nombre']) || trim($_POST['nombre']) == '') {
    throw new Exception('El nombre es requerido');
  }

  $tipo = new TipoDeGasto($_POST['id'] ?? null);
  $tipo->setPropertiesNoId($_POST);

  $tipo->save();

  echo json_encode(array(
    'status' => 'OK',
    'mensaje' => 'Tipo de gasto guardado exitosamente',
    'obj' => $tipo
  ));

} catch (Exception $e) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => $e->getMessage()
  ));
}
