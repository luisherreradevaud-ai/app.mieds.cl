<?php
/**
 * Autoload para DOMPDF y sus dependencias
 * Usar: require_once($GLOBALS['base_dir'].'/vendor_php/dompdf_autoload.php');
 */

$vendor_path = dirname(__FILE__);

// Registrar autoloader para Masterminds HTML5
spl_autoload_register(function ($class) use ($vendor_path) {
    $prefix = 'Masterminds\\';
    $base_dir = $vendor_path . '/masterminds-html5/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
}, true, true);

// Registrar autoloader para php-font-lib (DEBE ir primero)
spl_autoload_register(function ($class) use ($vendor_path) {
    $prefix = 'FontLib\\';
    $base_dir = $vendor_path . '/php-font-lib/src/FontLib/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
}, true, true); // prepend = true para que vaya antes del autoloader de la app

// Registrar autoloader para php-svg-lib
spl_autoload_register(function ($class) use ($vendor_path) {
    $prefix = 'Svg\\';
    $base_dir = $vendor_path . '/php-svg-lib/src/Svg/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
}, true, true);

// Registrar autoloader para Dompdf
spl_autoload_register(function ($class) use ($vendor_path) {
    $prefix = 'Dompdf\\';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);

    // Primero buscar en src/
    $file = $vendor_path . '/dompdf/src/' . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
        return;
    }

    // Luego buscar en lib/ (para Cpdf y otras clases especiales)
    $file = $vendor_path . '/dompdf/lib/' . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
        return;
    }
}, true, true);

// Cargar Cpdf directamente ya que no tiene namespace
require_once $vendor_path . '/dompdf/lib/Cpdf.php';
