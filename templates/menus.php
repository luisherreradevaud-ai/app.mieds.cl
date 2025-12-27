<?php
    $objs = Menu::getAll("ORDER BY nombre ASC");
?>
<style>
.tr-obj {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-bars"></i> <b>Menús</b></h1>
  </div>
  <a href="./?s=detalle-menus&id=0" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-fw fa-plus"></i> Nuevo Menú</a>
</div>
<hr />
<?php
Msg::show(2,'Menú eliminado con &eacute;xito','danger');
?>
<table class="table table-hover table-striped table-sm" id="objs-table">
  <thead>
    <tr>
      <th class="fw-bold">
        Nombre
      </th>
      <th>
        Icono
      </th>
      <th>
        Link
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($objs as $obj) {
    ?>
    <tr class="tr-obj" data-idobj="<?= $obj->id; ?>">
      <td>
        <?= $obj->nombre; ?>
      </td>
      <td>
        <i class="align-middle" data-lucide="<?= $obj->icon; ?>"></i> <?= $obj->icon; ?>
      </td>
      <td>
        <?= $obj->link; ?>
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
    window.location.href = "./?s=detalle-menus&id=" + $(e.currentTarget).data('idobj');
});

</script>
