<?php

$id = "";

if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
}

$msg = 0;
if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}

$obj = new TipoDeGasto($id);
$usuario = $GLOBALS['usuario'];

?>
<style>
.tr-tipos-de-gasto {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        <?php
        if($obj->id=="") {
          print '<i class="fas fa-fw fa-plus"></i> Nuevo';
        } else {
          print '<i class="fas fa-fw fa-tags"></i> Detalle';
        }
        ?> Tipo de Gasto
      </b>
    </h1>
  </div>
  <div>
    <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr />
<?php
Msg::show(1,'Tipo de gasto ingresado con &eacute;xito.','info');
Msg::show(2,'Tipo de gasto guardado con &eacute;xito.','info');
?>
<form id="tipos-de-gasto-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="tipos_de_gasto">

  <div class="row">
    <div class="col-md-6">
      <div class="row">
        <div class="col-4 mb-2">Nombre:</div>
        <div class="col-8 mb-2">
          <input type="text" class="form-control" name="nombre" required>
        </div>
        <div class="col-4 mb-2">Descripci&oacute;n:</div>
        <div class="col-8 mb-2">
          <textarea class="form-control" name="descripcion" rows="3"></textarea>
        </div>
        <div class="col-4 mb-2">Estado:</div>
        <div class="col-8 mb-2">
          <select name="estado" class="form-control">
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 mt-4 mb-1 d-flex justify-content-between">
    <button class="btn btn-danger eliminar-obj-btn btn-sm <?= $obj->id == '' ? 'd-none' : '' ?>"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
    <div>
      <button class="btn btn-primary btn-sm" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
    </div>
  </div>
</form>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-tipo-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar Tipo de Gasto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <center><h5>&iquest;Desea eliminar este tipo de gasto?<br/>Este paso no es reversible.</h5></center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" id="eliminar-tipo-aceptar" data-bs-dismiss="modal">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<script>
var obj = <?= json_encode($obj, JSON_PRETTY_PRINT); ?>;

$(document).ready(function(){
  if(obj.id != "") {
    $.each(obj, function(key, value){
      if(key != "table_name" && key != "table_fields") {
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
      }
    });
  }
});

$(document).on('click','#guardar-btn',function(e){
  e.preventDefault();

  // Validaciones
  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener al menos 2 caracteres.");
    return false;
  }

  var url = "./ajax/ajax_guardarTipoDeGasto.php";
  var data = getDataForm("tipos-de-gasto");

  $.post(url, data, function(response){
    if(response.status != "OK") {
      alert("Algo fall&oacute;: " + response.mensaje);
      return false;
    } else {
      var msgNum = obj.id == "" ? 1 : 2;
      window.location.href = "./?s=detalle-tipos-de-gasto&id=" + response.obj.id + "&msg=" + msgNum;
    }
  }).fail(function(){
    alert("Error al guardar");
  });
});

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-tipo-modal').modal('toggle');
});

$(document).on('click','#eliminar-tipo-aceptar',function(e){
  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': 'tipos_de_gasto'
  }

  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url, data, function(response){
    if(response.status != "OK") {
      alert("Algo fall&oacute;");
      return false;
    } else {
      window.location.href = "./?s=tipos-de-gasto&msg=2";
    }
  },'json').fail(function(){
    alert("Error al eliminar");
  });
});

</script>
