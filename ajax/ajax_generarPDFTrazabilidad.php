<?php
/**
 * Endpoint para generar PDF de Trazabilidad
 *
 * Parámetros GET:
 * - id: ID del EntregaProducto
 *
 * Respuesta: Descarga directa del PDF
 */

require_once("./../php/app.php");

// Verificar sesión
$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

// Validar parámetro
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    die('Parámetro id requerido');
}

$id_entregas_productos = $_GET['id'];

// Verificar que existe el registro
$ep = new EntregaProducto($id_entregas_productos);
if(empty($ep->id)) {
    header('HTTP/1.1 404 Not Found');
    die('Producto de entrega no encontrado');
}

// Verificar permisos (solo admin, jefe de planta, jefe de cocina pueden generar)
$niveles_permitidos = array('Administrador', 'Jefe de Planta', 'Jefe de Cocina', 'Operario', 'Repartidor');
if(!in_array($usuario->nivel, $niveles_permitidos)) {
    header('HTTP/1.1 403 Forbidden');
    die('No tiene permisos para generar este documento');
}

try {
    // Generar PDF
    $pdf = new TrazabilidadPDF($id_entregas_productos);
    $pdf->generar('D'); // D = Download
} catch(Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    die('Error al generar PDF: ' . $e->getMessage());
}
