<?php
/**
 * ajax_guardarInsumo.php
 * Guarda un insumo con soporte para upload de archivos
 */

require_once("./../php/app.php");

// Inicializar sesión y usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuario = new Usuario;
$usuario->checkSession($_SESSION);
$GLOBALS['usuario'] = $usuario;

header('Content-Type: application/json');

try {
    $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null;
    $accion = $id ? "modificado" : "creado";

    // Crear o cargar insumo
    $insumo = new Insumo($id);

    // Campos base
    $insumo->nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $insumo->id_tipos_de_insumos = isset($_POST['id_tipos_de_insumos']) ? $_POST['id_tipos_de_insumos'] : 0;
    $insumo->unidad_de_medida = isset($_POST['unidad_de_medida']) ? $_POST['unidad_de_medida'] : '';
    $insumo->bodega = isset($_POST['bodega']) ? intval($_POST['bodega']) : 0;
    $insumo->despacho = isset($_POST['despacho']) ? intval($_POST['despacho']) : 0;

    // Campos proveedor
    $insumo->id_proveedores = isset($_POST['id_proveedores']) && !empty($_POST['id_proveedores'])
        ? $_POST['id_proveedores'] : null;
    $insumo->codigo_proveedor = isset($_POST['codigo_proveedor']) ? $_POST['codigo_proveedor'] : '';
    $insumo->pais_origen = isset($_POST['pais_origen']) ? $_POST['pais_origen'] : '';

    // Campos Materia Prima
    $insumo->nombre_comercial = isset($_POST['nombre_comercial']) ? $_POST['nombre_comercial'] : '';
    $insumo->marca = isset($_POST['marca']) ? $_POST['marca'] : '';
    $insumo->materia_prima_basica = isset($_POST['materia_prima_basica']) ? $_POST['materia_prima_basica'] : '';
    $insumo->cosecha_anio = isset($_POST['cosecha_anio']) && !empty($_POST['cosecha_anio'])
        ? intval($_POST['cosecha_anio']) : null;
    $insumo->presentacion = isset($_POST['presentacion']) ? $_POST['presentacion'] : '';
    $insumo->vida_util_meses = isset($_POST['vida_util_meses']) && !empty($_POST['vida_util_meses'])
        ? intval($_POST['vida_util_meses']) : null;

    // Campos Levadura
    $insumo->es_levadura = isset($_POST['es_levadura']) ? intval($_POST['es_levadura']) : 0;
    $insumo->cepa = isset($_POST['cepa']) ? $_POST['cepa'] : '';
    $insumo->tipo_levadura = isset($_POST['tipo_levadura']) && !empty($_POST['tipo_levadura'])
        ? $_POST['tipo_levadura'] : null;
    $insumo->atenuacion_min = isset($_POST['atenuacion_min']) && $_POST['atenuacion_min'] !== ''
        ? floatval($_POST['atenuacion_min']) : null;
    $insumo->atenuacion_max = isset($_POST['atenuacion_max']) && $_POST['atenuacion_max'] !== ''
        ? floatval($_POST['atenuacion_max']) : null;
    $insumo->floculacion = isset($_POST['floculacion']) && !empty($_POST['floculacion'])
        ? $_POST['floculacion'] : null;
    $insumo->temp_fermentacion_min = isset($_POST['temp_fermentacion_min']) && $_POST['temp_fermentacion_min'] !== ''
        ? floatval($_POST['temp_fermentacion_min']) : null;
    $insumo->temp_fermentacion_max = isset($_POST['temp_fermentacion_max']) && $_POST['temp_fermentacion_max'] !== ''
        ? floatval($_POST['temp_fermentacion_max']) : null;
    $insumo->tolerancia_alcohol = isset($_POST['tolerancia_alcohol']) && $_POST['tolerancia_alcohol'] !== ''
        ? floatval($_POST['tolerancia_alcohol']) : null;

    // Campos Halal
    $insumo->es_halal_certificado = isset($_POST['es_halal_certificado']) ? intval($_POST['es_halal_certificado']) : 0;
    $insumo->certificado_halal_numero = isset($_POST['certificado_halal_numero']) ? $_POST['certificado_halal_numero'] : '';
    $insumo->certificado_halal_vencimiento = isset($_POST['certificado_halal_vencimiento']) && !empty($_POST['certificado_halal_vencimiento'])
        ? $_POST['certificado_halal_vencimiento'] : '0000-00-00';
    $insumo->certificado_halal_emisor = isset($_POST['certificado_halal_emisor']) ? $_POST['certificado_halal_emisor'] : '';
    $insumo->url_ficha_tecnica = isset($_POST['url_ficha_tecnica']) ? $_POST['url_ficha_tecnica'] : '';
    $insumo->url_certificado_halal = isset($_POST['url_certificado_halal']) ? $_POST['url_certificado_halal'] : '';

    // Guardar insumo
    $insumo->save();

    // Procesar archivos adjuntos
    $base_dir = $GLOBALS['base_dir'];
    $upload_dir = $base_dir . "/media/insumos/";

    // Crear directorio si no existe
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Ficha técnica
    if (isset($_FILES['ficha_tecnica_file']) && $_FILES['ficha_tecnica_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['ficha_tecnica_file'];
        $media_id = guardarArchivoInsumo($file, $upload_dir, $insumo->id, 'ficha_tecnica');
        if ($media_id) {
            $insumo->attachMedia($media_id, 'ficha_tecnica', 'Ficha técnica');
        }
    }

    // Certificado Halal
    if (isset($_FILES['certificado_halal_file']) && $_FILES['certificado_halal_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['certificado_halal_file'];
        $media_id = guardarArchivoInsumo($file, $upload_dir, $insumo->id, 'certificado_halal');
        if ($media_id) {
            $insumo->attachMedia($media_id, 'certificado_halal', 'Certificado Halal');
        }
    }

    // Registrar en historial
    if ($usuario && $usuario->id) {
        Historial::guardarAccion(
            "Insumo #" . $insumo->id . " (" . $insumo->nombre . ") " . $accion . ".",
            $usuario
        );
    }

    echo json_encode([
        'status' => 'OK',
        'mensaje' => 'OK',
        'id' => $insumo->id
    ]);

} catch (Exception $e) {
    error_log("Error en ajax_guardarInsumo.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'ERROR',
        'mensaje' => 'Error al guardar: ' . $e->getMessage()
    ]);
}

/**
 * Guarda un archivo de insumo y crea la entrada en media
 * @param array $file $_FILES array element
 * @param string $upload_dir Directorio destino
 * @param string $insumo_id ID del insumo
 * @param string $tipo Tipo de archivo
 * @return string|null ID del media creado o null si falla
 */
function guardarArchivoInsumo($file, $upload_dir, $insumo_id, $tipo) {
    global $mysqli;

    // Validar tipo de archivo
    $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $file_name_arr = explode(".", basename($file['name']));
    $extension = strtolower(end($file_name_arr));

    if (!in_array($extension, $allowed_types)) {
        return null;
    }

    // Generar nombre único
    $new_filename = $insumo_id . '_' . $tipo . '_' . time() . '.' . $extension;
    $dest_path = $upload_dir . $new_filename;

    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $dest_path)) {
        chmod($dest_path, 0644);

        // Crear entrada en media
        $media = new Media();
        $media->url = 'insumos/' . $new_filename;
        $media->nombre = basename($file['name']);
        $media->descripcion = ucfirst(str_replace('_', ' ', $tipo));
        $media->tipo = $extension;
        $media->save();

        return $media->id;
    }

    return null;
}

?>
