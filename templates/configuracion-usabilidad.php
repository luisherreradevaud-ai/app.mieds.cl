<?php


  $obj = new Configuracion(1);
  $media_arr = $obj->getMedia();
  $media_header = $obj->getMediaHeader();


?>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-wrench"></i> <b>Configuraci&oacute;n: Usabilidad</b></h1>
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
          Duraci&oacute;n Historial:
        </div>
        <div class="col-6 mb-1">
          <select class="form-control" name="estado">
            <option value="1 MONTH">1 mes</option>
            <option>3 meses</option>
            <option>6 meses</option>
            <option>12 meses</option>
          </select>
        </div>
        <div class="col-6 mb-1">
          Duraci&oacute;n Notificaciones:
        </div>
        <div class="col-6 mb-1">
          <select class="form-control" name="ubicacion">
            <option value="7 DAYS">1 semana</option>
            <option>1 mes</option>
            <option>3 meses</option>
          </select>
        </div>
      </div>
      </form>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        &nbsp;
        <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
    </div>
    </div>
    <div class="col-md-5">
        <div class="card">
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

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("configuraciones");

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=configuracion-usabilidad&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});



</script>