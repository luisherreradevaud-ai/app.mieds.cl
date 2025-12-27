<?php
    $objs = Seccion::getAll("ORDER BY nombre ASC");
    $menus = Menu::getAll("ORDER BY nombre ASC");
    $menus_map = array();
    if(is_array($menus)) {
      foreach($menus as $menu) {
        $menus_map[$menu->id] = $menu;
      }
    }
?>
<style>
.tr-obj {
  cursor: pointer;
}
.badge-visible {
  background-color: #28a745;
}
.badge-hidden {
  background-color: #dc3545;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-file"></i> <b>Secciones</b></h1>
  </div>
  <a href="./?s=detalle-secciones&id=0" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-fw fa-plus"></i> Nueva Sección</a>
</div>
<hr />
<?php
Msg::show(2,'Sección eliminada con &eacute;xito','danger');
?>
<table class="table table-hover table-striped table-sm" id="objs-table">
  <thead>
    <tr>
      <th class="fw-bold">
        Nombre
      </th>
      <th>
        Template
      </th>
      <th>
        Menú
      </th>
      <th>
        Clasificación
      </th>
      <th>
        Visible
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($objs as $obj) {
        $menu_nombre = $obj->id_menus && isset($menus_map[$obj->id_menus]) ? $menus_map[$obj->id_menus]->nombre : 'Sin menú';
    ?>
    <tr class="tr-obj" data-idobj="<?= $obj->id; ?>">
      <td>
        <?= $obj->nombre; ?>
      </td>
      <td>
        <code><?= $obj->template_file; ?></code>
      </td>
      <td>
        <?= $menu_nombre; ?>
      </td>
      <td>
        <?= $obj->clasificacion; ?>
      </td>
      <td>
        <span class="badge <?= $obj->visible ? 'badge-visible' : 'badge-hidden'; ?>">
          <?= $obj->visible ? 'Visible' : 'Oculta'; ?>
        </span>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<script>

new DataTable('#objs-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true
});

$(document).on('click','.tr-obj',function(e) {
    window.location.href = "./?s=detalle-secciones&id=" + $(e.currentTarget).data('idobj');
});

</script>
