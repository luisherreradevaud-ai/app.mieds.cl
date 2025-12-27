<?php

$id = "";

$obj = new Usuario($id);
$niveles_usuario = $GLOBALS['niveles_usuario'];

$clientes = Cliente::getAll();
$usuario = $GLOBALS['usuario'];


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
  Msg::show(1,'Usuario guardado con &eacute;xito','primary');
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
        <input type="text" class="form-control" name="email" autocomplete="FALSE">
      </div>
      <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="nombre" autocomplete="FALSE">
      </div>
      <div class="col-6 mb-1">
        Telefono:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="telefono">
      </div>
      <div class="col-6 mb-1 mt-3">
        Nivel:
      </div>
      <div class="col-6 mb-1 mt-3">
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
            >
              <?= htmlspecialchars($cliente->nombre) ?>
            </option>
          <?php } ?>
        </select>
      </div>
      <div class="col-6 mb-1 mt-3">
        <b>Modo:</b>
      </div>
      <div class="col-6 mb-1 mt-3">
        <select name="modo" class="form-control" id="modo-registro">
          <option>Registro</option>
          <option>Invitacion</option>
        </select>
      </div>
      <div class="col-6 mb-1 registro">
        Password:
      </div>
      <div class="col-6 mb-1 registro">
        <div class="input-group">
          <input type="password" class="form-control" name="password_input" id="password_input" autocomplete="FALSE">
          <button class="btn btn-outline-secondary" type="button" id="toggle-password" title="Mostrar/Ocultar contraseña">
            <i class="fas fa-eye"></i>
          </button>
          <button class="btn btn-outline-primary" type="button" id="generar-password" title="Generar contraseña segura">
            <i class="fas fa-key"></i>
          </button>
        </div>
      </div>
      <div class="col-6 mb-1 registro">
        Repetir Password:
      </div>
      <div class="col-6 mb-1 registro">
        <input type="password" class="form-control" name="repetir_password_input" id="repetir_password_input" autocomplete="off">
      </div>

      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <button class="btn btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
        <button class="btn btn-primary" id="guardar-y-agregar-btn"><i class="fas fa-fw fa-save"></i> Guardar y agregar Nuevo</button>
      </div>
    </div>
  </div>
</form>



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

  if($('#modo-registro').val()=="Registro") {
    if($('input[name="password_input"]').val().length < 6) {
      alert("Password debe tener al menos 6 caracteres.");
      return false;
    }
    if($('input[name="password_input"]').val() != $('input[name="repetir_password_input"]').val()) {
      alert("Passwords deben coincidir.");
      return false;
    }
  }

  var url = "./ajax/ajax_nuevoUsuario.php";
  var data = getDataForm("usuarios");

  $.post(url,data,function(response){
    console.log(response)
    response = JSON.parse(response)
    if(response.status!="OK") {
      alert("Hubo un error: " + response.mensaje)
    } else {
      //window.location.href = "./?s=detalle-usuarios&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#guardar-y-agregar-btn',function(e){

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

  if($('#modo-registro').val()=="Registro") {
    if($('input[name="password_input"]').val().length < 6) {
      alert("Password debe tener al menos 6 caracteres.");
      return false;
    }
    if($('input[name="password_input"]').val() != $('input[name="repetir_password_input"]').val()) {
      alert("Passwords deben coincidir.");
      return false;
    }
  }

  var url = "./ajax/ajax_nuevoUsuario.php";
  var data = getDataForm("usuarios");

  $.post(url,data,function(response){
    console.log(response);
    if(response.status!="OK") {
      alert("Hubo un error: " + response.mensaje)
    } else {
      window.location.href = "./?s=nuevo-usuarios&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
$(document).ready(function(){

  if(obj.nivel != "Cliente") {
    $('.cliente').hide();
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

$(document).on('click','.eliminar-obj-btn',function(){
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
});

// Generar contraseña segura
$(document).on('click','#generar-password',function(e){
  e.preventDefault();

  // Generar contraseña de 12 caracteres con mayúsculas, minúsculas, números y símbolos
  const mayusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const minusculas = 'abcdefghijklmnopqrstuvwxyz';
  const numeros = '0123456789';
  const simbolos = '!@#$%^&*';
  const todos = mayusculas + minusculas + numeros + simbolos;

  let password = '';

  // Asegurar al menos uno de cada tipo
  password += mayusculas[Math.floor(Math.random() * mayusculas.length)];
  password += minusculas[Math.floor(Math.random() * minusculas.length)];
  password += numeros[Math.floor(Math.random() * numeros.length)];
  password += simbolos[Math.floor(Math.random() * simbolos.length)];

  // Completar hasta 12 caracteres
  for (let i = password.length; i < 12; i++) {
    password += todos[Math.floor(Math.random() * todos.length)];
  }

  // Mezclar los caracteres
  password = password.split('').sort(() => Math.random() - 0.5).join('');

  // Asignar a ambos campos
  $('#password_input').val(password);
  $('#repetir_password_input').val(password);

  // Mostrar temporalmente
  $('#password_input').attr('type', 'text');
  $('#repetir_password_input').attr('type', 'text');
  $('#toggle-password i').removeClass('fa-eye').addClass('fa-eye-slash');

  // Copiar al portapapeles
  navigator.clipboard.writeText(password).then(function() {
    alert('Contraseña generada y copiada al portapapeles: ' + password);
  }).catch(function() {
    alert('Contraseña generada: ' + password);
  });
});

// Toggle mostrar/ocultar contraseña
$(document).on('click','#toggle-password',function(e){
  e.preventDefault();

  const passwordInput = $('#password_input');
  const repetirInput = $('#repetir_password_input');
  const icon = $(this).find('i');

  if (passwordInput.attr('type') === 'password') {
    passwordInput.attr('type', 'text');
    repetirInput.attr('type', 'text');
    icon.removeClass('fa-eye').addClass('fa-eye-slash');
  } else {
    passwordInput.attr('type', 'password');
    repetirInput.attr('type', 'password');
    icon.removeClass('fa-eye-slash').addClass('fa-eye');
  }
});

</script>
