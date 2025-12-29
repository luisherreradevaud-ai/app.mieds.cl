<?php

  $msg = 0;

  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $tipos = TipoDeGasto::getAll("ORDER BY nombre ASC");

?>
<style>
.tr-tipos-de-gasto {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-tags"></i> <b>Tipos de Gasto</b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=detalle-tipos-de-gasto" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Tipo</a>
    </div>
  </div>
</div>
<hr />
<?php
if($msg == 2) {
?>
<div class="alert alert-danger" role="alert">Tipo de gasto eliminado.</div>
<?php
}
?>
<table class="table table-hover table-striped table-sm" id="objs-table">
  <thead>
    <tr>
      <th class="fw-bold">Nombre</th>
      <th>Descripci&oacute;n</th>
      <th>Estado</th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($tipos as $tipo) {
    ?>
    <tr class="tr-tipos-de-gasto" data-id="<?= $tipo->id; ?>">
      <td><?= $tipo->nombre; ?></td>
      <td><?= $tipo->descripcion; ?></td>
      <td>
        <?php if($tipo->estado == 'activo') { ?>
          <span class="badge bg-success">Activo</span>
        <?php } else { ?>
          <span class="badge bg-secondary">Inactivo</span>
        <?php } ?>
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
        url: 'https://cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true
});

$(document).on('click','.tr-tipos-de-gasto',function(e){
  window.location.href = "./?s=detalle-tipos-de-gasto&id=" + $(e.currentTarget).data('id');
});

</script>
