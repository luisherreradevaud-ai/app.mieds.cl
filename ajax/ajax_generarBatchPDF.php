<?php
/**
 * ajax_generarBatchPDF.php
 * Genera el PDF de informe completo de un batch
 */

// Capturar cualquier output previo que pueda corromper el PDF
ob_start();

require_once("./../php/app.php");

// Inicializar sesión y usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuario = new Usuario;
$usuario->checkSession($_SESSION);
$GLOBALS['usuario'] = $usuario;

// Limpiar cualquier output previo
ob_end_clean();

// Validar parámetro
$id_batch = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : null);

if(empty($id_batch)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'ID de batch requerido']);
    exit;
}

try {
    // Cargar el batch
    $batch = new Batch($id_batch);

    if(empty($batch->id)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ERROR', 'mensaje' => 'Batch no encontrado']);
        exit;
    }

    // Generar PDF
    $pdf = new BatchPDF($batch);

    // Nombre del archivo
    $filename = 'Batch_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $batch->batch_nombre) . '_' . date('Ymd') . '.pdf';

    // Descargar
    $pdf->descargar($filename);

} catch (Exception $e) {
    error_log("Error generando PDF de batch: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'Error al generar PDF: ' . $e->getMessage()]);
}

exit; // IMPORTANTE: Terminar ejecución para evitar output adicional que corrompa el PDF
