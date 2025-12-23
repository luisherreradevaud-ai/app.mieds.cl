<?php
/**
 * Endpoint para validar si un activo tiene limpieza Halal válida
 * Usado antes de iniciar producción en línea sin alcohol
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
$horas_maximas = isset($_GET['horas']) ? intval($_GET['horas']) : 24;

// Obtener activo
$activo = new Activo($id_activos);

if(empty($activo->id)) {
    echo json_encode(['status' => 'ERROR', 'mensaje' => 'Activo no encontrado']);
    exit;
}

// Si es uso exclusivo Halal o línea sin alcohol, siempre válido
if($activo->uso_exclusivo_halal) {
    echo json_encode([
        'status' => 'OK',
        'valido' => true,
        'mensaje' => 'Activo de uso exclusivo Halal',
        'requiere_limpieza' => false,
        'activo' => [
            'id' => $activo->id,
            'nombre' => $activo->nombre,
            'linea_productiva' => $activo->linea_productiva,
            'uso_exclusivo_halal' => true
        ]
    ]);
    exit;
}

if($activo->linea_productiva == 'analcoholica') {
    echo json_encode([
        'status' => 'OK',
        'valido' => true,
        'mensaje' => 'Activo de línea sin alcohol',
        'requiere_limpieza' => false,
        'activo' => [
            'id' => $activo->id,
            'nombre' => $activo->nombre,
            'linea_productiva' => $activo->linea_productiva
        ]
    ]);
    exit;
}

// Validar limpieza Halal para activos generales o alcohólicos
$validacion = RegistroLimpieza::validarLimpiezaHalalParaProduccion($id_activos, $horas_maximas);

$response = [
    'status' => 'OK',
    'valido' => $validacion['valido'],
    'mensaje' => $validacion['mensaje'],
    'requiere_limpieza' => !$validacion['valido'],
    'activo' => [
        'id' => $activo->id,
        'nombre' => $activo->nombre,
        'linea_productiva' => $activo->linea_productiva,
        'fecha_ultima_limpieza_halal' => $activo->fecha_ultima_limpieza_halal
    ]
];

if(isset($validacion['ultima_limpieza'])) {
    $ultima = $validacion['ultima_limpieza'];
    $response['ultima_limpieza'] = [
        'id' => $ultima->id,
        'fecha' => $ultima->fecha,
        'fecha_formato' => date('d/m/Y H:i', strtotime($ultima->fecha)),
        'certificado' => $ultima->certificado_numero,
        'procedimiento' => $ultima->procedimiento_utilizado
    ];
}

echo json_encode($response);
?>
