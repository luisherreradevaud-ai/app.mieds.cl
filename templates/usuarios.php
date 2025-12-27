<?php

  $msg = 0;

  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $query = "WHERE nivel!='Cliente'";
  $usuarios = Usuario::getAll($query);

?>
<style>
.tr-usuarios {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-user"></i> <b>Usuarios Internos</b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=nuevo-usuarios" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Usuario</a>
    </div>
  </div>
</div>
<hr />
<?php
if($msg == 2) {
?>
<div class="alert alert-danger" role="alert" >Usuario eliminado.</div>
<?php
}

Widget::printWidget('usuarios-menu');

?>
<table class="table table-hover table-striped table-sm" id="objs-table">
  <thead>
    <tr>
      <th class="fw-bold">
          Email
      </th>
      <th>
          Nombre
      </th>
      <th>
          Nivel
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($usuarios as $usuario) {
    ?>
    <tr class="tr-usuarios" data-idusuarios="<?= $usuario->id; ?>">
      <td>
        <?= $usuario->email; ?>
      </td>
      <td>
        <?= $usuario->nombre; ?>
      </td>
      <td>
        <?= $usuario->nivel; ?>
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

$(document).on('click','.tr-usuarios',function(e){
  window.location.href = "./?s=detalle-usuarios&id=" + $(e.currentTarget).data('idusuarios');
});

</script>
