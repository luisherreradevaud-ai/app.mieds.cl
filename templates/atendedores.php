<?php

  $msg = 0;

  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $atendedores = Atendedor::getAll("ORDER BY nombre_completo ASC");

?>
<style>
.tr-atendedores {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-id-card"></i> <b>Atendedores</b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=detalle-atendedores" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Atendedor</a>
    </div>
  </div>
</div>
<hr />
<?php
if($msg == 2) {
?>
<div class="alert alert-danger" role="alert">Atendedor eliminado.</div>
<?php
}
?>
<table class="table table-hover table-striped table-sm" id="objs-table">
  <thead>
    <tr>
      <th class="fw-bold">RUT</th>
      <th>Nombre Completo</th>
      <th>Cargo Copec</th>
      <th>ID Tarjeta</th>
      <th>Estado</th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($atendedores as $atendedor) {
    ?>
    <tr class="tr-atendedores" data-idatendedores="<?= $atendedor->id; ?>">
      <td><?= $atendedor->rut; ?></td>
      <td><?= $atendedor->nombre_completo; ?></td>
      <td><?= $atendedor->cargo_copec; ?></td>
      <td><?= $atendedor->id_tarjeta_copec; ?></td>
      <td>
        <?php if($atendedor->estado == 'Activo') { ?>
          <span class="badge bg-success"><?= $atendedor->estado; ?></span>
        <?php } else { ?>
          <span class="badge bg-secondary"><?= $atendedor->estado; ?></span>
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

$(document).on('click','.tr-atendedores',function(e){
  window.location.href = "./?s=detalle-atendedores&id=" + $(e.currentTarget).data('idatendedores');
});

</script>
