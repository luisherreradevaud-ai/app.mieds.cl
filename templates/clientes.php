<?php


  //checkAutorizacion("Administrador");

  $msg = 0;

  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }


  $clientes = Cliente::getAll();



?>
<style>
.tr-clientes {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-handshake"></i> <b>Clientes</b></h1>
  </div>
  <div>
    <div>
      <a href="./?s=detalle-clientes" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Cliente</a>
    </div>
  </div>
</div>
<hr />
<?php
if($msg == 2) {
?>
<div class="alert alert-danger" role="alert" >Cliente eliminado.</div>
<?php
}
Widget::printWidget('usuarios-menu');
?>
<table class="table table-hover table-striped table-sm" id="objs-table">
  <thead>
    <tr>
      <th>
          Nombre
        </a>
      </th>
      <th>
          Email
        </a>
      </th>
      <th>
        Telefono
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($clientes as $cliente) {
    ?>
    <tr class="tr-clientes" data-idclientes="<?= $cliente->id; ?>">
      <td>
        <?= $cliente->nombre; ?>
      </td>
      <td>
        <?= $cliente->email; ?>
      </td>
      <td>
        <?= $cliente->telefono; ?>
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

$(document).on('click','.tr-clientes',function(e){
  window.location.href = "./?s=detalle-clientes&id=" + $(e.currentTarget).data('idclientes');
});
</script>
