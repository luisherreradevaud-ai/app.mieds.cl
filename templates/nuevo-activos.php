<?php

  $usuario = $GLOBALS['usuario'];
  $locaciones = Locacion::getAll('ORDER BY nombre asc');
  $clases = Activo::getClases();
  $lineas_productivas = Activo::getLineasProductivas();
  $clientes = Cliente::getAll("WHERE estado!='Bloqueado' ORDER BY nombre asc");

?>
<style>
.tr-proveedor {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Nuevo Activo
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
  Msg::show(1,'Activo guardado con &eacute;xito','primary');
?>
<form id="activos-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="activos">
  <input type="hidden" name="id_usuarios" value="<?= $GLOBALS['usuario']->id; ?>">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="nombre">
      </div>
      <div class="col-6 mb-1">
        Clase:
      </div>
      <div class="col-6 mb-1">
        <select name="clase" class="form-control">
          <?php
            foreach($clases as $clase) {
              ?>
              <option>
                <?= $clase; ?>
              </option>
              <?php
            }
          ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Marca:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="marca">
      </div>
      <div class="col-6 mb-1">
        Modelo:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="modelo">
      </div>
      <div class="col-6 mb-1">
        C&oacute;digo:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="codigo">
      </div>
      <div class="col-6 mb-1">
        Capacidad:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="capacidad">
      </div>
      <div class="col-6 mb-1">
        Clasificaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="clasificacion">
          <option>Cr&iacute;tico</option>
          <option>Importante</option>
          <option>Poco importante</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="estado">
          <option>Activo</option>
          <option>Con observaciones</option>
          <option>Inactivo</option>
        </select>
      </div>

      <div class="col-6 mb-1">
        L&iacute;nea Productiva:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="linea_productiva">
          <?php
            foreach($lineas_productivas as $key => $label) {
              $selected = ($key == 'general') ? 'selected' : '';
              ?>
              <option value="<?= $key; ?>" <?= $selected; ?>>
                <?= $label; ?>
              </option>
              <?php
            }
          ?>
        </select>
      </div>

      <div class="col-6 mb-1">
        Ubicaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="ubicacion">
          <option>En planta</option>
          <option>En terreno</option>
        </select>
      </div>

      <div class="col-6 mb-1 en-terreno">
        Cliente:
      </div>
      <div class="col-6 mb-1 en-terreno">
        <select class="form-control" name="id_clientes_ubicacion">
          <?php
          foreach($clientes as $cliente) {
            print "<option value='".$cliente->id."'>".$cliente->nombre."</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-6 mb-1 en-planta">
        Locación:
      </div>
      <div class="col-6 mb-1 en-planta">
        <select class="form-control" name="id_locaciones">
          <?php
          foreach($locaciones as $locacion) {
            print "<option value='".$locacion->id."'>".$locacion->nombre."</option>";
          }
          ?>
        </select>
      </div>

      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        &nbsp;
        <div>
          <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
          <button class="btn btn-sm btn-primary" id="guardar-y-agregar-btn"><i class="fas fa-fw fa-save"></i> Guardar y agregar Nuevo</button>
        </div>
      </div>
    </div>
    
  </div>
</form>

<script>

$(document).ready(function(){
  $('.en-terreno').hide();
  $('.en-planta').show();
});

$(document).on('change','select[name="ubicacion"]',function(e) {
  if($(e.currentTarget).val() == "En terreno") {
    $('.en-terreno').show();
    $('.en-planta').hide();
    $('select[name="id_locaciones"]').val('0');
  } else
  if($(e.currentTarget).val() == "En planta") {
    $('.en-terreno').hide();
    $('.en-planta').show();
    $('select[name="id_clientes_ubicacion"]').val('0');
  }
});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    //alert("El nombre debe tener mas de 2 caracteres.");
    //return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("activos");
  console.log(data);

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-activos&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#guardar-y-agregar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    //alert("El nombre debe tener mas de 2 caracteres.");
    //return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("activos");
  console.log(data);

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-activos&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

</script>
