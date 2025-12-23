<?php

require_once("./../php/app.php");

// Inicializar sesión y usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuario = new Usuario;
$usuario->checkSession($_SESSION);
$GLOBALS['usuario'] = $usuario;

// Initialize AJAX security
$ajax = AjaxSecurity::init([
  'methods' => ['POST'],
  'csrf' => false,
  'auth' => false,
  'rate_limit' => false,
  'required_params' => ['entidad'],
  'input_rules' => [
    'entidad' => 'string',
    'id' => 'string'
  ]
]);

try {
  // Habilitar SQL logging para debug (solo si se pasa debug_sql=1)
  $entidad = $ajax->input('entidad');
  $debug_sql = isset($_POST['debug_sql']) && $_POST['debug_sql'] == '1';
  if ($debug_sql) {
    Base::enableSqlLogging();
  }

  $id = $ajax->input('id', '');

  // Determine action (create or update)
  $accion = !empty($id) ? "modificado" : "creado";

  // Create object and set properties
  $obj = createObjFromTableName($entidad, $id);

  // Set properties from POST directly (not using $ajax->input())
  $properties = $_POST;
  unset($properties['entidad']);
  unset($properties['csrf_token']);

  $obj->setPropertiesNoId($properties);
  $obj->save();

  // Handle entity-specific logic
  if (method_exists($obj, 'setSpecifics')) {
    $obj->setSpecifics($properties);
    $obj->save(); // Save again to persist changes from setSpecifics
  }

  // Log action to history
  $usuario = isset($GLOBALS['usuario']) ? $GLOBALS['usuario'] : null;
  if($usuario && $usuario->id) {
    Historial::guardarAccion(
      get_class($obj) . " #" . $obj->id . " " . $accion . ".",
      $usuario
    );
  }

  // Preparar respuesta
  $response = [
    'status' => 'OK',
    'mensaje' => 'OK',
    'obj' => $obj
  ];

  // Agregar SQL log si está habilitado
  if ($debug_sql) {
    $response['sql_log'] = Base::getSqlLog();
    Base::disableSqlLogging();
  }

  // Send success response
  header('Content-Type: application/json');
  echo json_encode($response);

} catch (Exception $e) {
  error_log("Error en ajax_guardarEntidad.php: " . $e->getMessage());

  $response = [
    'status' => 'ERROR',
    'mensaje' => 'Error al guardar: ' . $e->getMessage()
  ];

  // Agregar SQL log incluso en error
  if (isset($debug_sql) && $debug_sql) {
    $response['sql_log'] = Base::getSqlLog();
  }

  header('Content-Type: application/json');
  echo json_encode($response);
}

?>
