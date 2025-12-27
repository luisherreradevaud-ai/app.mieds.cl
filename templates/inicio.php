<?php
/**
 * CONTEXT: Classes used in this file
 *
 * Menu (extends Base) - /php/classes/Menu.php
 * - Properties: nombre, icon, link, secciones (array), estado
 * - Table: menus
 *
 * Seccion (extends Base) - /php/classes/Seccion.php
 * - Properties: nombre, template_file, visible, permisos_editables, id_menus, create_path, estado
 * - Table: secciones
 *
 * UsuarioNivel (extends Base) - /php/classes/UsuarioNivel.php
 * - Properties: nombre, editable, comentarios, creada
 * - Table: usuarios_niveles
 *
 * Permiso (extends Base) - /php/classes/Permiso.php
 * - Properties: id_secciones, id_usuarios_niveles, acceso
 * - Table: permisos
 */

$usuario = $GLOBALS['usuario'];

// Get all menus
$menus = Menu::getAll("ORDER BY nombre asc");
$menus_arr = array();
foreach($menus as $menu) {
    $menus_arr[$menu->id] = $menu;
    $menus_arr[$menu->id]->secciones = array();
}

// Get user level
$usuario_nivel = new UsuarioNivel;
$usuario_nivel->getFromDatabase('nombre', $usuario->nivel);

// Get permissions for this user level
$permisos = Permiso::getAll("INNER JOIN secciones ON permisos.id_secciones = secciones.id WHERE permisos.id_usuarios_niveles='".$usuario_nivel->id."' AND permisos.acceso='1' ORDER BY secciones.nombre asc");

// Group sections by menu
foreach($permisos as $permiso) {
    $seccion = new Seccion($permiso->id_secciones);
    if(!isset($menus_arr[$seccion->id_menus])) {
        continue;
    }
    $menus_arr[$seccion->id_menus]->secciones[] = $seccion;
}

?>

<div class="container-fluid p-0">

    <div class="mb-3">
        <h1 class="h3 d-inline align-middle">Inicio</h1>
    </div>

    <?php
    foreach($menus_arr as $menu) {
        // Skip menus without sections
        if(count($menu->secciones) == 0) {
            continue;
        }
        ?>

        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-3">
                    <i class="align-middle" data-lucide="<?= $menu->icon; ?>"></i>
                    <span class="align-middle ms-2"><?= $menu->nombre; ?></span>
                </h3>
            </div>

            <?php
            foreach($menu->secciones as $key => $seccion) {
                if($key == 3) break;
                ?>
                <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                    <a href="./?s=<?= $seccion->template_file; ?>" class="text-decoration-none">
                        <div class="card h-100 card-hover">
                            <div class="card-body">
                                <h5 class="card-title mb-0"><?= $seccion->nombre; ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
                <?php
            }
            ?>
        </div>

        <?php
    }
    ?>

</div>

<style>
.card-hover {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

a.text-decoration-none:hover {
    text-decoration: none;
}
</style>
