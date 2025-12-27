<?php

$id = "";

if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
}

$msg = 0;
if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}

$obj = new Atendedor($id);
$usuario = $GLOBALS['usuario'];

?>
<style>
.tr-atendedores {
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
          print '<i class="fas fa-fw fa-id-card"></i> Detalle';
        }
        ?> Atendedor
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
Msg::show(1,'Atendedor ingresado con &eacute;xito.','info');
Msg::show(2,'Atendedor guardado con &eacute;xito.','info');
?>
<form id="atendedores-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="atendedores">

  <div class="row">
    <div class="col-md-6">
      <h5 class="mb-3"><i class="fas fa-fw fa-user"></i> Datos Personales</h5>
      <div class="row">
        <div class="col-6 mb-2">RUT:</div>
        <div class="col-6 mb-2">
          <input type="text" class="form-control" name="rut" placeholder="12.345.678-9" maxlength="12">
        </div>
        <div class="col-6 mb-2">Nombre Completo:</div>
        <div class="col-6 mb-2">
          <input type="text" class="form-control" name="nombre_completo">
        </div>
        <div class="col-6 mb-2">G&eacute;nero:</div>
        <div class="col-6 mb-2">
          <select name="genero" class="form-control">
            <option value="">Seleccionar...</option>
            <option>Masculino</option>
            <option>Femenino</option>
            <option>Otro</option>
          </select>
        </div>
        <div class="col-6 mb-2">Estado Civil:</div>
        <div class="col-6 mb-2">
          <select name="estado_civil" class="form-control">
            <option value="">Seleccionar...</option>
            <option>Soltero/a</option>
            <option>Casado/a</option>
            <option>Divorciado/a</option>
            <option>Viudo/a</option>
            <option>Conviviente</option>
          </select>
        </div>
        <div class="col-6 mb-2">Direcci&oacute;n:</div>
        <div class="col-6 mb-2">
          <input type="text" class="form-control" name="direccion">
        </div>
        <div class="col-6 mb-2">Tel&eacute;fono:</div>
        <div class="col-6 mb-2">
          <input type="text" class="form-control" name="telefono" placeholder="+56 9 1234 5678">
        </div>
        <div class="col-6 mb-2">Correo:</div>
        <div class="col-6 mb-2">
          <input type="email" class="form-control" name="correo">
        </div>
        <div class="col-6 mb-2">Nacionalidad:</div>
        <div class="col-6 mb-2">
          <input type="text" class="form-control" name="nacionalidad" value="Chilena">
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <h5 class="mb-3"><i class="fas fa-fw fa-briefcase"></i> Datos Laborales</h5>
      <div class="row">
        <div class="col-6 mb-2">Jornada de Trabajo:</div>
        <div class="col-6 mb-2">
          <select name="jornada_trabajo" class="form-control">
            <option value="">Seleccionar...</option>
            <option>Completa</option>
            <option>Parcial</option>
            <option>Por turnos</option>
          </select>
        </div>
        <div class="col-6 mb-2">Cargo Copec:</div>
        <div class="col-6 mb-2">
          <input type="text" class="form-control" name="cargo_copec">
        </div>
        <div class="col-6 mb-2">Rol MAE:</div>
        <div class="col-6 mb-2">
          <input type="text" class="form-control" name="rol_mae">
        </div>
      </div>

      <h5 class="mb-3 mt-4"><i class="fas fa-fw fa-key"></i> Credenciales</h5>
      <div class="row">
        <div class="col-6 mb-2">ID Tarjeta Copec:</div>
        <div class="col-6 mb-2">
          <input type="text" class="form-control" name="id_tarjeta_copec" maxlength="14" placeholder="14 caracteres">
        </div>
        <div class="col-6 mb-2">ID MAE:</div>
        <div class="col-6 mb-2">
          <input type="text" class="form-control numerico" name="id_mae" maxlength="4" placeholder="4 d&iacute;gitos">
        </div>
        <div class="col-6 mb-2">Clave MAE:</div>
        <div class="col-6 mb-2">
          <input type="text" class="form-control numerico" name="clave_mae" maxlength="4" placeholder="4 d&iacute;gitos">
        </div>
      </div>

      <h5 class="mb-3 mt-4"><i class="fas fa-fw fa-cog"></i> Estado</h5>
      <div class="row">
        <div class="col-6 mb-2">Estado:</div>
        <div class="col-6 mb-2">
          <select name="estado" class="form-control">
            <option>Activo</option>
            <option>Inactivo</option>
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

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-atendedor-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar Atendedor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <center><h5>¿Desea eliminar este atendedor?<br/>Este paso no es reversible.</h5></center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" id="eliminar-atendedor-aceptar" data-bs-dismiss="modal">Eliminar</button>
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
  if($('input[name="rut"]').val().length < 8) {
    alert("Ingrese un RUT v&aacute;lido.");
    return false;
  }

  if($('input[name="nombre_completo"]').val().length < 3) {
    alert("El nombre debe tener al menos 3 caracteres.");
    return false;
  }

  if($('input[name="id_tarjeta_copec"]').val().length > 0 && $('input[name="id_tarjeta_copec"]').val().length != 14) {
    alert("El ID de tarjeta Copec debe tener 14 caracteres.");
    return false;
  }

  if($('input[name="id_mae"]').val().length > 0 && $('input[name="id_mae"]').val().length != 4) {
    alert("El ID MAE debe tener 4 d&iacute;gitos.");
    return false;
  }

  if($('input[name="clave_mae"]').val().length > 0 && $('input[name="clave_mae"]').val().length != 4) {
    alert("La clave MAE debe tener 4 d&iacute;gitos.");
    return false;
  }

  var url = "./ajax/ajax_guardarAtendedor.php";
  var data = getDataForm("atendedores");

  $.post(url, data, function(response){
    response = JSON.parse(response);
    if(response.status != "OK") {
      alert("Algo fall&oacute;: " + response.mensaje);
      return false;
    } else {
      var msgNum = obj.id == "" ? 1 : 2;
      window.location.href = "./?s=detalle-atendedores&id=" + response.obj.id + "&msg=" + msgNum;
    }
  }).fail(function(){
    alert("Error al guardar");
  });
});

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-atendedor-modal').modal('toggle');
});

$(document).on('click','#eliminar-atendedor-aceptar',function(e){
  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': 'atendedores'
  }

  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url, data, function(response){
    if(response.status != "OK") {
      alert("Algo fall&oacute;");
      return false;
    } else {
      window.location.href = "./?s=atendedores&msg=2";
    }
  },'json').fail(function(){
    alert("Error al eliminar");
  });
});

// Solo permitir n&uacute;meros en campos num&eacute;ricos
$(document).on('keyup','.numerico',function(){
  $(this).val($(this).val().replace(/\D/g,''));
});

</script>
