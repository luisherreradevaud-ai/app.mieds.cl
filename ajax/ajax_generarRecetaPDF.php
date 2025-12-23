<?php
/**
 * ajax_generarRecetaPDF.php
 * Genera el PDF de instrucciones de una receta
 */

require_once("./../php/app.php");

// Inicializar sesión y usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuario = new Usuario;
$usuario->checkSession($_SESSION);
$GLOBALS['usuario'] = $usuario;

// Validar parámetro
$id_receta = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : null);

if(empty($id_receta)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'ID de receta requerido']);
    exit;
}

try {
    // Cargar la receta
    $receta = new Receta($id_receta);

    if(empty($receta->id)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ERROR', 'mensaje' => 'Receta no encontrada']);
        exit;
    }

    // Generar PDF
    $pdf = new RecetaPDF($receta);

    // Nombre del archivo
    $filename = 'Instrucciones_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $receta->codigo) . '_' . date('Ymd') . '.pdf';

    // Descargar
    $pdf->descargar($filename);

} catch (Exception $e) {
    error_log("Error generando PDF de receta: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'Error al generar PDF: ' . $e->getMessage()]);
}

?>
