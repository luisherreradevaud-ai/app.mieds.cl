<?php


  $obj = new Configuracion(1);
  $media_arr = $obj->getMedia();
  $media_header = $obj->getMediaHeader();


?>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-wrench"></i> <b>Configuraci&oacute;n: Datos de la empresa</b></h1>
  </div>
</div>
<?php Widget::printWidget("configuraciones-menu"); ?>
<?php 
  Msg::show(1,'Configuraciones guardadas con &eacute;xito','info');
  Msg::show(2,'Media cargada con &eacute;xito','danger');
  Msg::show(3,'Media eliminada con &eacute;xito','info');
  Msg::show(4,'Imagen principal cambiada con &eacute;xito','info');
?>
<hr />
<form id="configuraciones-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="configuraciones">
  <input type="hidden" name="id_usuarios" value="<?= $GLOBALS['usuario']->id; ?>">
  <div class="row mb-5">
    <div class="col-md-7">
      <div class="row">
        <div class="col-6 mb-1">
          Nombre empresa:
        </div>
        <div class="col-6 mb-1">
          <input type="text" class="form-control" name="nombre_empresa">
        </div>
        <div class="col-6 mb-1">
          Email:
        </div>
        <div class="col-6 mb-1">
          <input type="text" class="form-control" name="email_empresa">
        </div>
        <div class="col-6 mb-1">
          Tel&eacute;fono:
        </div>
        <div class="col-6 mb-1">
          <input type="text" class="form-control" name="telefono_empresa">
        </div>
        <div class="col-6 mb-1">
          Direcci&oacute;n:
        </div>
        <div class="col-6 mb-1">
          <input type="text" class="form-control" name="direccion_empresa">
        </div>
        <div class="col-6 mb-1">
          Representante:
        </div>
        <div class="col-6 mb-1">
          <input type="text" class="form-control" name="representante_empresa">
        </div>
        <div class="col-6 mb-1">
          RUT:
        </div>
        <div class="col-6 mb-1">
          <input type="text" class="form-control" name="rut_empresa">
        </div>
        <div class="col-6 mb-1">
          Giro:
        </div>
        <div class="col-6 mb-1">
          <input type="text" class="form-control" name="giro_empresa">
        </div>
      </div>
      </form>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        &nbsp;
        <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
    </div>
    </div>
    <div class="col-md-5">

    </div>
</div>
</form>




<script>

var id_media_eliminar = 0;
var editar = true;
var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;

$(document).ready(function(){

    $.each(obj,function(key,value){
        if(key!="table_name"&&key!="table_fields"){
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
        }
    });
    
});





$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre_empresa"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("configuraciones");

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=configuracion-datos&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});



</script>