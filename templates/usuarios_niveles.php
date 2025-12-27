<?php

    $objs = UsuarioNivel::getAll("ORDER BY nombre asc");

?>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-user"></i> <b>Niveles de Usuario</b></h1>
  </div>
  <div>
  </div>
</div>
<hr />
<?php 
Msg::show(2,'','danger');
?>
<form id="permisos-form">
<table class="table table-hover table-striped table-sm mb-4">
  <thead class="thead-dark">
    <tr>
      <th>
        Nombre
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
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
</form>
<script>

$(document).on('click','.tr-obj',function(e){
  window.location.href = "./?s=detalle-usuarios_niveles&id=" + $(e.currentTarget).data('idobj');
});


</script>
