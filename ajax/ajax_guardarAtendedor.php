<?php
require_once("../php/app.php");

$response = array(
  'status' => 'ERROR',
  'mensaje' => '',
  'obj' => null
);

try {
  // Verificar campos requeridos
  if(!isset($_POST['rut']) || empty($_POST['rut'])) {
    $response['mensaje'] = 'El RUT es requerido';
    echo json_encode($response);
    exit;
  }

  if(!isset($_POST['nombre_completo']) || empty($_POST['nombre_completo'])) {
    $response['mensaje'] = 'El nombre completo es requerido';
    echo json_encode($response);
    exit;
  }

  // Crear o cargar objeto
  $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null;
  $obj = new Atendedor($id);

  // Asignar propiedades
  $obj->rut = $_POST['rut'];
  $obj->nombre_completo = $_POST['nombre_completo'];
  $obj->genero = isset($_POST['genero']) ? $_POST['genero'] : '';
  $obj->estado_civil = isset($_POST['estado_civil']) ? $_POST['estado_civil'] : '';
  $obj->direccion = isset($_POST['direccion']) ? $_POST['direccion'] : '';
  $obj->telefono = isset($_POST['telefono']) ? $_POST['telefono'] : '';
  $obj->correo = isset($_POST['correo']) ? $_POST['correo'] : '';
  $obj->nacionalidad = isset($_POST['nacionalidad']) ? $_POST['nacionalidad'] : '';
  $obj->jornada_trabajo = isset($_POST['jornada_trabajo']) ? $_POST['jornada_trabajo'] : '';
  $obj->cargo_copec = isset($_POST['cargo_copec']) ? $_POST['cargo_copec'] : '';
  $obj->rol_mae = isset($_POST['rol_mae']) ? $_POST['rol_mae'] : '';
  $obj->id_tarjeta_copec = isset($_POST['id_tarjeta_copec']) ? $_POST['id_tarjeta_copec'] : '';
  $obj->id_mae = isset($_POST['id_mae']) ? $_POST['id_mae'] : '';
  $obj->clave_mae = isset($_POST['clave_mae']) ? $_POST['clave_mae'] : '';
  $obj->estado = isset($_POST['estado']) ? $_POST['estado'] : 'Activo';

  // Guardar
  $obj->save();

  $response['status'] = 'OK';
  $response['mensaje'] = 'Atendedor guardado correctamente';
  $response['obj'] = $obj;

} catch (Exception $e) {
  $response['mensaje'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
