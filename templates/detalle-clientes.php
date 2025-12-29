<?php

$id = "";

if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
}

$msg = 0;

if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}

$obj = new Cliente($id);
$usuario = $GLOBALS['usuario'];

?>
<style>
.tr-clientes {
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
          print '<i class="fas fa-fw fa-building"></i> Detalle';
        }
        ?> Cliente
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
Msg::show(1,'Cliente guardado con &eacute;xito.','info');
?>
<form id="clientes-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="clientes">
  <div class="row">
    <div class="col-md-6">
      <h5 class="mb-3"><i class="fas fa-fw fa-user"></i> Datos del Cliente</h5>
      <div class="row">
        <div class="col-4 mb-2">Nombre:</div>
        <div class="col-8 mb-2">
          <input type="text" class="form-control" name="nombre" required>
        </div>
        <div class="col-4 mb-2">Email:</div>
        <div class="col-8 mb-2">
          <input type="email" class="form-control" name="email">
        </div>
        <div class="col-4 mb-2">Tel&eacute;fono:</div>
        <div class="col-8 mb-2">
          <input type="text" class="form-control" name="telefono">
        </div>
        <div class="col-4 mb-2">Estado:</div>
        <div class="col-8 mb-2">
          <select name="estado" class="form-control">
            <option>Activo</option>
            <option>Inactivo</option>
          </select>
        </div>
      </div>

      <h5 class="mb-3 mt-4"><i class="fas fa-fw fa-file-invoice"></i> Datos de Facturaci&oacute;n</h5>
      <div class="row">
        <div class="col-4 mb-2">RUT:</div>
        <div class="col-8 mb-2">
          <input type="text" name="RUT" class="form-control" placeholder="12.345.678-9">
        </div>
        <div class="col-4 mb-2">Raz&oacute;n Social:</div>
        <div class="col-8 mb-2">
          <input type="text" name="RznSoc" class="form-control">
        </div>
        <div class="col-4 mb-2">Giro:</div>
        <div class="col-8 mb-2">
          <input type="text" name="Giro" class="form-control">
        </div>
        <div class="col-4 mb-2">Direcci&oacute;n:</div>
        <div class="col-8 mb-2">
          <input type="text" name="Dir" class="form-control">
        </div>
        <div class="col-4 mb-2">Comuna:</div>
        <div class="col-8 mb-2">
          <input type="text" name="Cmna" class="form-control">
        </div>
      </div>

      <div class="col-12 mt-4 mb-1 d-flex justify-content-between">
        <?php if($obj->id != "" && $usuario->nivel == 'Administrador') { ?>
        <button class="btn btn-sm btn-danger eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
        <?php } else { ?>
        <span></span>
        <?php } ?>
        <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
      </div>
    </div>
  </div>
</form>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-cliente-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar Cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <center><h5>&iquest;Desea eliminar este cliente?<br/>Este paso no es reversible.</h5></center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" id="eliminar-cliente-aceptar" data-bs-dismiss="modal">Eliminar</button>
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

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener al menos 2 caracteres.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("clientes");

  $.post(url, data, function(response){
    if(response.mensaje != "OK") {
      alert("Algo fall&oacute;: " + (response.error || ''));
      return false;
    } else {
      window.location.href = "./?s=detalle-clientes&id=" + response.obj.id + "&msg=1";
    }
  },'json').fail(function(){
    alert("Error al guardar");
  });
});

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-cliente-modal').modal('toggle');
});

$(document).on('click','#eliminar-cliente-aceptar',function(e){
  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }

  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url, data, function(response){
    if(response.status != "OK") {
      alert("Algo fall&oacute;");
      return false;
    } else {
      window.location.href = "./?s=clientes&msg=2";
    }
  },"json").fail(function(){
    alert("Error al eliminar");
  });
});
</script>
