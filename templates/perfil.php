<?php

  $msg = 0;

  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $obj = $GLOBALS['usuario'];

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
        Perfil de Usuario
      </b>
    </h1>
  </div>
</div>
<hr />
<?php
if($msg == 1) {
?>
<div class="alert alert-info" role="alert" >Datos modificados con &eacute;xito.</div>
<?php
} else
if($msg == 2) {
?>
<div class="alert alert-info" role="alert" >Contrase&ntilde;a cambiada con &eacute;xito.</div>
<?php
}
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


      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <a href="#" id="cambiar-password-btn" style="font-size: 0.8em" data-bs-toggle="modal" data-bs-target="#cambiar-password-modal">Cambiar Contrase&ntilde;a</a>
          <button class="btn btn-primary" id="guardar-btn">Guardar</button>
      </div>
    </div>
  </div>
</form>


<div class="modal" tabindex="-1" role="dialog" id="cambiar-password-modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cambiar Contrase&ntilde;a</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-6 mb-3">
            Contrase&ntilde;a Anterior
          </div>
          <div class="col-6 mb-3">
            <input type="password" name="cambiar-password-anterior" id="cambiar-password-anterior" class="form-control">
          </div>
          <div class="col-6 mb-3">
            Nueva Contrase&ntilde;a
          </div>
          <div class="col-6 mb-3">
            <input type="password" name="cambiar-password" id="cambiar-password" class="form-control">
          </div>
          <div class="col-6 mb-3">
            Repetir Nuevo Contrase&ntilde;a
          </div>
          <div class="col-6 mb-3">
            <input type="password" name="cambiar-repetir-password" id="cambiar-repetir-password" class="form-control">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="cambiar-password-guardar-btn">Guardar</button>
      </div>
    </div>
  </div>
</div>


<!-- ///////////////////////////////////////////////////////////////////// -->



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
    if(response.mensaje!="OK") {
      alert("Algo fallo: " + response.mensaje);
      alert();
      return false;
    } else {
      window.location.href = "./?s=perfil&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
$(document).ready(function(){
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

$(document).on('click','#cambiar-password-guardar-btn',function(e) {

  e.preventDefault();

  if($('#cambiar-password-anterior').val().length < 6) {
    alert("Password deberia tener al menos 6 caracteres.");
    return false;
  }

  if($('#cambiar-password').val().length < 6) {
    alert("Password nuevo debe tener al menos 6 caracteres.");
    return false;
  }
  if($('#cambiar-password').val() != $('#cambiar-repetir-password').val()) {
    alert("Passwords deben coincidir.");
    return false;
  }

  var data = {
    'id_usuarios': <?= $obj->id; ?>,
    'password': $('#cambiar-password').val(),
    'password-anterior': $('#cambiar-password-anterior').val()
  };
  console.log(data);

  var url = "./ajax/ajax_cambiarPassword.php";

  $.post(url,data,function(response){
    if(response.mensaje!="OK") {
      alert("Algo fallo: " + response.mensaje);
      return false;
    } else {
      window.location.href = "./?s=perfil&msg=2";
    }
  },'json').fail(function(){
    alert("No funciono");
  });

});

</script>
