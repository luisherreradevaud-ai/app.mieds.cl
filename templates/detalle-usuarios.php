<?php

//checkAutorizacion("Administrador");

$id = "";

if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
}

$msg = 0;
if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}

$obj = new Usuario($id);
$niveles_usuario = $GLOBALS['niveles_usuario'];
$clientes = Cliente::getAll();
$vendedores = Vendedor::getAll();

$link_volver = "usuarios";

if(isset($_GET['tipo_usuario'])) { 
  if($_GET['tipo_usuario'] == "externo") { 
    $link_volver = "externos"; 
  }
}
$usuario = $GLOBALS['usuario'];
$relations = $obj->getRelations('clientes');

?>
<style>
.tr-usuarios {
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
          print '<i class="fas fa-fw fa-user"></i> Detalle';
        }
        ?> Usuario
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
Msg::show(1,'Usuario ingresado con &eacute;xito.','info');
Msg::show(2,'Usuario guardado con &eacute;xito.','info');
Msg::show(3,'Invitación enviada con &eacute;xito.','info');
?>
<form id="usuarios-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="usuarios">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Email:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="email" autocomplete="off">
      </div>
      <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="nombre">
      </div>
      <div class="col-6 mb-1">
        Telefono:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="telefono">
      </div>
      <div class="col-6 mb-1">
        Nivel:
      </div>
      <div class="col-6 mb-1">
        <select name="nivel" class="form-control">
          <?php
          foreach($niveles_usuario as $nivel) {
            print "<option>".$nivel."</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-6 mb-1 cliente">
        Cliente(s):
      </div>
      <div class="col-6 mb-1 cliente">
        <select name="id_clientes[]" class="form-control" value="<?= $clientes_value_str; ?>" multiple>
          <?php foreach($clientes as $cliente) { ?>
            <option 
              value="<?= $cliente->id ?>" 
              <?= in_array($cliente->id, $relations) ? 'selected' : '' ?>
            >
              <?= htmlspecialchars($cliente->nombre) ?>
            </option>
          <?php } ?>
        </select>
      </div>
      <div class="col-6 mb-1 vendedor">
        Meta Barriles:
      </div>
      <div class="col-6 mb-1 vendedor">
        <input type="number" class="form-control acero" name="vendedor_meta_barriles" value="0">
      </div>
      <div class="col-6 mb-1 vendedor">
        Meta Cajas:
      </div>
      <div class="col-6 mb-1 vendedor">
        <input type="number" class="form-control acero" name="vendedor_meta_cajas" value="0">
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select name="estado" class="form-control">
          <option>Activo</option>
          <option>Bloqueado</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Registro de Asistencia:
      </div>
      <div class="col-6 mb-1">
        <select name="registro_asistencia" class="form-control form-control-solid">
          <option value="0">No</option>
          <option value="1">Si</option>
        </select>
      </div>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <button class="btn btn-danger eliminar-obj-btn btn-sm"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
        <div>
          <button class="btn btn-primary btn-sm" id="reenviar-invitacion-btn">Enviar Invitaci&oacute;n</button>
          <button class="btn btn-primary btn-sm" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
        </div>
      </div>
    </div>
  </div>
</form>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-usuario-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este usuario?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" id="eliminar-usuario-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>


  <div class="modal fade" tabindex="-1" role="dialog" id="reenviar-invitacion-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Re enviar invitaci&oacute;n a Usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea re enviar la invitaci&oacute;n?<br/>El usuario quedará desactivado hasta que esta se acepte.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary btn-sm" id="reenviar-invitacion-aceptar" data-bs-dismiss="modal">Enviar</button>
        </div>
      </div>
    </div>
  </div>

<script>
$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }

  if(!$('input[name="email"]').val().includes('@')) {
    alert("Ingrese un correo valido.");
    return false;
  }

  if(!$('input[name="email"]').val().includes('.')) {
    alert("Ingrese un correo valido.");
    return false;
  }

  if($('input[name="email"]').val().length < 5) {
    alert("El email debe tener mas de 5 caracteres.");
    return false;
  }

  if($('input[name="telefono"]').val().length < 5) {
    alert("El telefono debe tener mas de 5 caracteres.");
    return false;
  }


  var url = "./ajax/ajax_editarUsuario.php";
  var data = getDataForm("usuarios");

  $.post(url,data,function(response){
    response = JSON.parse(response)
    if(response.mensaje!="OK") {
      alert("Algo fallo: " + response.mensaje);
      return false;
    } else {
      window.location.href = "./?s=detalle-usuarios&id=" + response.obj.id + "&msg=2";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
$(document).ready(function(){

  if(obj.nivel != "Cliente") {
    $('.cliente').hide();
  }

  if(obj.nivel != "Vendedor") {
    $('.vendedor').hide();
  }

  if(obj.id!="") {
    $.each(obj,function(key,value){
      console.log(key);
      if(key!="table_name"&&key!="table_fields"){
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
      }

    });
  }
});

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-usuario-modal').modal('toggle');
})

$(document).on('click','#eliminar-usuario-aceptar',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }

  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(response){
    console.log(response);
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=" + response.table_name + "&msg=2";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#reenviar-invitacion-btn',function(e){
  e.preventDefault();
  $('#reenviar-invitacion-modal').modal('toggle');
})

$(document).on('click','#reenviar-invitacion-aceptar',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }

  if(!$('input[name="email"]').val().includes('@')) {
    alert("Ingrese un correo valido.");
    return false;
  }

  if(!$('input[name="email"]').val().includes('.')) {
    alert("Ingrese un correo valido.");
    return false;
  }

  if($('input[name="email"]').val().length < 5) {
    alert("El email debe tener mas de 5 caracteres.");
    return false;
  }

  if($('input[name="telefono"]').val().length < 5) {
    alert("El telefono debe tener mas de 5 caracteres.");
    return false;
  }


  var url = "./ajax/ajax_editarUsuario.php";
  var data_usuario = getDataForm("usuarios");

  var data = {
    'id': obj.id,
    'modo': obj.table_name,
    'obj': data_usuario
  }

  var url = './ajax/ajax_postReEnviarInvitacion.php';
  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.status!="OK") {
      alert(response.mensaje);
      return false;
    } else {
      window.location.href = "./?s=detalle-usuarios&id" + response.obj.id + "&msg=3";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('change','select[name="modo"]',function(e){
  if($(e.currentTarget).val()=="Registro") {
    $('.registro').show(200);
  } else {
    $('.registro').hide(200);
  }
});

$(document).on('change','select[name="nivel"]',function(e){
  if($(e.currentTarget).val() == "Cliente") {
    $('.cliente').show(200);
  } else {
    $('.cliente').hide(200);
  }
  if($(e.currentTarget).val() == "Vendedor") {
    $('.vendedor').show(200);
  } else {
    $('.vendedor').hide(200);
  }
});

</script>
