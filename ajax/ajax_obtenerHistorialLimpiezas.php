<?php
/**
 * Endpoint para obtener historial de limpiezas de un activo
 */

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

header('Content-Type: application/json');

if(!isset($_GET['id_activos'])) {
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'ID de activo requerido']);
    exit;
}

$id_activos = $_GET['id_activos'];
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$solo_halal = isset($_GET['solo_halal']) && $_GET['solo_halal'] == '1';

// Construir consulta
$where = "WHERE id_activos='" . addslashes($id_activos) . "'";
if($solo_halal) {
    $where .= " AND es_limpieza_halal=1";
}
$where .= " ORDER BY fecha DESC LIMIT " . $limit;

$historial = RegistroLimpieza::getAll($where);

$resultado = [];
foreach($historial as $limpieza) {
    $limpieza->cargarRelaciones();
    $resultado[] = [
        'id' => $limpieza->id,
        'fecha' => $limpieza->fecha,
        'fecha_formato' => date('d/m/Y H:i', strtotime($limpieza->fecha)),
        'tipo_limpieza' => $limpieza->tipo_limpieza,
        'procedimiento' => $limpieza->procedimiento_utilizado,
        'productos_utilizados' => $limpieza->productos_utilizados,
        'usuario' => $limpieza->usuario ? $limpieza->usuario->nombre : 'N/A',
        'usuario_id' => $limpieza->id_usuarios,
        'supervisor' => $limpieza->supervisor ? $limpieza->supervisor->nombre : null,
        'supervisor_id' => $limpieza->id_usuarios_supervisor,
        'es_halal' => $limpieza->es_limpieza_halal,
        'certificado' => $limpieza->certificado_numero,
        'certificado_emisor' => $limpieza->certificado_emisor,
        'observaciones' => $limpieza->observaciones,
        'estado' => $limpieza->estado
    ];
}

echo json_encode([
    'status' => 'OK',
    'data' => $resultado,
    'total' => count($resultado)
]);
?>
