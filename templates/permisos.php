<?php

    $usuarios_niveles = UsuarioNivel::getAll("ORDER BY nombre asc");
    $secciones_arr = array();
    foreach($GLOBALS['secciones_clasificacion'] as $clasificacion) {
      $secciones_arr[$clasificacion] = Seccion::getAll("WHERE clasificacion='".$clasificacion."' ORDER BY nombre asc");
    }
    $permisos = Permiso::getAll();

?>
<style>
.tr-productos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-check"></i> <b>Permisos</b></h1>
  </div>
  <div>
  </div>
</div>
<hr />
<?php 
Msg::show(2,'Permisos modificados con &eacute;xito','danger');
?>
<form id="permisos-form">
<?php
  foreach($secciones_arr as $clasificacion => $secciones) {
?>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800"><?= $clasificacion; ?></h1>
  </div>
  <div>
  </div>
</div>
<table class="table table-hover table-striped table-sm mb-4">
  <thead class="thead-dark">
    <tr>
    <th>
    </th>
      <?php
        foreach($usuarios_niveles as $un) {
            print "<th>".$un->nombre."</th>";
        }
      ?>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($secciones as $seccion) {
    ?>
    <tr>
        <td>
            <?= $seccion->nombre; ?>
        </td>
        <?php
        foreach($usuarios_niveles as $un) {
        ?>
        <td>
            <input type="checkbox" name="permiso_<?=$seccion->id."_".$un->id; ?>" data-idsecciones="<?= $seccion->id; ?>" data-idusuariosniveles="<?= $un->id; ?>"
            <?php
            if($seccion->permisos_editables == 0) {
              print " DISABLED";
            }
            ?> data-bs-toggle="tooltip" data-bs-placement="top" title="Activa '<?= $seccion->nombre;?>' para '<?= $un->nombre; ?>'">
        </td>
        <?php
        }
        ?>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<?php
  }
?>
</form>
<button class="btn btn-sm btn-primary mt-3 mb-5" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
<script>

var permisos = <?= json_encode($permisos,JSON_PRETTY_PRINT); ?>;

$(document).ready(function(){
  permisos.forEach(function(permiso){
    if(permiso.acceso == 1) {
      var nombre = 'input[name="permiso_' + permiso.id_secciones + '_' + permiso.id_usuarios_niveles + '"]';
      $(nombre).attr('checked',true);
    }
  });
});

$(document).on('click','.tr-productos',function(e){
  window.location.href = "./?s=detalle-productos&id=" + $(e.currentTarget).data('idproductos');
});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarPermisos.php";
  var data = getDataForm("permisos");

  $.post(url,data,function(raw){

    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.reload();
      //window.location.href = "./?s=detalle-clientes&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

</script>
