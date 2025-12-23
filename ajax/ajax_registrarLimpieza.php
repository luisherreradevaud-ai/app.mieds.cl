<?php
/**
 * Endpoint para registrar limpieza de un activo
 */

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

header('Content-Type: application/json');

// Validar permisos
$niveles_permitidos = ['Administrador', 'Jefe de Planta', 'Jefe de Cocina', 'Operario'];
if(!in_array($usuario->nivel, $niveles_permitidos)) {
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'Sin permisos para registrar limpiezas']);
    exit;
}

// Validar datos requeridos
if(!isset($_POST['id_activos']) || empty($_POST['id_activos'])) {
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'ID de activo requerido']);
    exit;
}

if(!isset($_POST['tipo_limpieza']) || empty($_POST['tipo_limpieza'])) {
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'Tipo de limpieza requerido']);
    exit;
}

// Verificar que el activo existe
$activo = new Activo($_POST['id_activos']);
if(empty($activo->id)) {
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'Activo no encontrado']);
    exit;
}

// Crear registro
$limpieza = new RegistroLimpieza();
$limpieza->id_activos = $_POST['id_activos'];
$limpieza->fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d H:i:s');
$limpieza->tipo_limpieza = $_POST['tipo_limpieza'];
$limpieza->procedimiento_utilizado = isset($_POST['procedimiento_utilizado']) ? $_POST['procedimiento_utilizado'] : '';
$limpieza->productos_utilizados = isset($_POST['productos_utilizados']) ? $_POST['productos_utilizados'] : '';
$limpieza->id_usuarios = $usuario->id;
$limpieza->observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';

// Campos Halal
$es_halal = isset($_POST['es_limpieza_halal']) && $_POST['es_limpieza_halal'] == 1;
$limpieza->es_limpieza_halal = $es_halal ? 1 : 0;

if($es_halal || $_POST['tipo_limpieza'] == 'Halal') {
    $limpieza->es_limpieza_halal = 1;
    $limpieza->certificado_numero = isset($_POST['certificado_numero']) ? $_POST['certificado_numero'] : '';
    $limpieza->certificado_emisor = isset($_POST['certificado_emisor']) ? $_POST['certificado_emisor'] : '';
    $limpieza->id_usuarios_supervisor = isset($_POST['id_usuarios_supervisor']) ? intval($_POST['id_usuarios_supervisor']) : 0;
}

// Campos CIP (Clean-In-Place)
if($_POST['tipo_limpieza'] == 'CIP') {
    $limpieza->programa_cip = isset($_POST['programa_cip']) ? $_POST['programa_cip'] : '';
    $limpieza->temperatura_max_cip = isset($_POST['temperatura_max_cip']) && $_POST['temperatura_max_cip'] !== ''
        ? floatval($_POST['temperatura_max_cip']) : null;
    $limpieza->tiempo_total_cip_min = isset($_POST['tiempo_total_cip_min']) && $_POST['tiempo_total_cip_min'] !== ''
        ? intval($_POST['tiempo_total_cip_min']) : null;
    $limpieza->conductividad_promedio = isset($_POST['conductividad_promedio']) && $_POST['conductividad_promedio'] !== ''
        ? floatval($_POST['conductividad_promedio']) : null;

    // Convertir datetime-local (YYYY-MM-DDTHH:mm) a MySQL DATETIME (YYYY-MM-DD HH:mm:ss)
    if(isset($_POST['cip_timestamp_inicio']) && !empty($_POST['cip_timestamp_inicio'])) {
        $limpieza->cip_timestamp_inicio = str_replace('T', ' ', $_POST['cip_timestamp_inicio']) . ':00';
    }
    if(isset($_POST['cip_timestamp_fin']) && !empty($_POST['cip_timestamp_fin'])) {
        $limpieza->cip_timestamp_fin = str_replace('T', ' ', $_POST['cip_timestamp_fin']) . ':00';
    }

    $limpieza->id_batches_posterior = isset($_POST['id_batches_posterior']) && !empty($_POST['id_batches_posterior'])
        ? $_POST['id_batches_posterior'] : null;
}

// Registrar (guarda y actualiza activo)
$id = $limpieza->registrar();

if($id) {
    // Registrar en historial si existe la clase
    if(class_exists('Historial')) {
        Historial::guardarAccion(
            "Limpieza registrada para activo #" . $_POST['id_activos'] . " (" . $activo->nombre . ") - Tipo: " . $_POST['tipo_limpieza'],
            $usuario
        );
    }

    echo json_encode([
        'status' => 'OK',
        'mensaje' => 'Limpieza registrada correctamente',
        'id' => $id,
        'activo' => [
            'id' => $activo->id,
            'nombre' => $activo->nombre,
            'proxima_limpieza' => $activo->proxima_limpieza
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'ERROR',
        'mensaje' => 'Error al guardar el registro'
    ]);
}
?>
