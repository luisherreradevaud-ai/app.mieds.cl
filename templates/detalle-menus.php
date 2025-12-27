<?php

    if(!isset($_GET['id'])) {
        die();
    }

    $id = is_numeric($_GET['id']) && $_GET['id'] > 0 ? $_GET['id'] : null;
    $obj = new Menu($id);

?>
<style>
.icon-preview {
  font-size: 32px;
  margin: 10px 0;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-bars"></i> <b><?= $obj->id ? 'Detalle Menú' : 'Nuevo Menú'; ?></b></h1>
  </div>
  <div>
    <div>
      <?php $GLOBALS['usuario']->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr />
<?php
  Msg::show(1,'Menú guardado con &eacute;xito','primary');
?>
<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <form id="menus-form">
          <input type="hidden" name="id" value="">
          <input type="hidden" name="entidad" value="menus">
          <div class="row">
            <div class="col-6 mb-1">
              Nombre:
            </div>
            <div class="col-6 mb-1">
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col-6 mb-1">
              Icono (lucide):
            </div>
            <div class="col-6 mb-1">
              <input type="text" name="icon" class="form-control" id="icon-input" placeholder="menu">
              <small class="text-muted">Ver iconos en: <a href="https://lucide.dev/icons/" target="_blank">lucide.dev</a></small>
            </div>
            <div class="col-12 mb-3">
              <div class="icon-preview text-center">
                <i class="align-middle" data-lucide="menu" id="icon-preview"></i>
              </div>
            </div>
            <div class="col-6 mb-1">
              Link:
            </div>
            <div class="col-6 mb-3">
              <input type="text" name="link" class="form-control" placeholder="#" value="#">
            </div>
            <div class="col-12 mb-1 mt-3 d-flex justify-content-between">
              <?php if($obj->id) { ?>
              <button class="btn btn-danger btn-sm eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
              <?php } ?>
              <button class="btn btn-primary btn-sm ms-auto" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar Menú</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <center><h5>¿Desea eliminar este Menú?<br/>Este paso no es reversible.</h5></center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<script>

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;

$(function() {
  $.each(obj,function(key,value){
    if(key!="table_name"&&key!="table_fields"){
      $('#menus-form input[name="'+key+'"]').val(value);
      $('#menus-form textarea[name="'+key+'"]').val(value);
      $('#menus-form select[name="'+key+'"]').val(value);
    }
  });

  // Update icon preview
  updateIconPreview();
});

// Update icon preview on input change
$('#icon-input').on('input', function() {
  updateIconPreview();
});

function updateIconPreview() {
  var iconName = $('#icon-input').val() || 'menu';
  $('#icon-preview').attr('data-lucide', iconName);

  // Reload lucide icons if available
  if(typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
}

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("menus");

  $.post(url,data,function(response){
    if(response.mensaje !== "OK") {
      alert("Error al guardar: " + (response.mensaje || "Error desconocido"));
      return false;
    } else {
      window.location.href = "./?s=detalle-menus&id=" + response.obj.id + "&msg=1";
    }
  },"json").fail(function(xhr, status, error){
    alert("No funcionó la conexión");
  });
});

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-obj-modal').modal('show');
})

$(document).on('click','#eliminar-obj-aceptar',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=" + response.table_name + "&msg=2";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

</script>
