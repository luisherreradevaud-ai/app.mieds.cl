<?php
/**
 * Debug endpoint para BatchPDF
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Debug BatchPDF</h2>";

try {
    echo "<p>1. Cargando app.php...</p>";
    require_once("./../php/app.php");
    echo "<p style='color:green'>OK</p>";

    echo "<p>2. Iniciando sesion...</p>";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $usuario = new Usuario;
    $usuario->checkSession($_SESSION);
    $GLOBALS['usuario'] = $usuario;
    echo "<p style='color:green'>OK - Usuario: " . htmlspecialchars($usuario->nombre) . "</p>";

    $id_batch = isset($_GET['id']) ? $_GET['id'] : null;
    echo "<p>3. ID Batch recibido: " . htmlspecialchars($id_batch ?: 'NINGUNO') . "</p>";

    if(empty($id_batch)) {
        echo "<p style='color:red'>ERROR: No se recibio ID de batch</p>";
        exit;
    }

    echo "<p>4. Cargando Batch...</p>";
    $batch = new Batch($id_batch);
    if(empty($batch->id)) {
        echo "<p style='color:red'>ERROR: Batch no encontrado</p>";
        exit;
    }
    echo "<p style='color:green'>OK - Batch: #" . htmlspecialchars($batch->batch_nombre) . "</p>";

    echo "<p>5. Cargando dompdf...</p>";
    require_once($GLOBALS['base_dir'] . '/vendor_php/dompdf_autoload.php');
    echo "<p style='color:green'>OK</p>";

    echo "<p>6. Creando instancia BatchPDF...</p>";
    $pdf = new BatchPDF($batch);
    echo "<p style='color:green'>OK</p>";

    echo "<p>7. Generando HTML (sin renderizar PDF)...</p>";

    // Usar reflection para acceder al metodo privado
    $reflection = new ReflectionClass($pdf);
    $method = $reflection->getMethod('generarHTML');
    $method->setAccessible(true);
    $html = $method->invoke($pdf);

    echo "<p style='color:green'>OK - HTML generado (" . strlen($html) . " caracteres)</p>";

    echo "<h3>Vista previa del HTML:</h3>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; max-height: 400px; overflow: auto;'>";
    echo $html;
    echo "</div>";

    echo "<h3>Codigo fuente HTML:</h3>";
    echo "<textarea style='width:100%; height: 200px;'>" . htmlspecialchars($html) . "</textarea>";

    echo "<p>8. Probando renderizado PDF...</p>";

    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', false);
    $options->set('isRemoteEnabled', false);

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('Letter', 'portrait');
    $dompdf->render();

    $output = $dompdf->output();
    echo "<p style='color:green'>OK - PDF generado (" . strlen($output) . " bytes)</p>";

    echo "<p><a href='ajax_generarBatchPDF.php?id=" . htmlspecialchars($id_batch) . "' target='_blank'>Descargar PDF</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>EXCEPTION: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Error $e) {
    echo "<p style='color:red'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
