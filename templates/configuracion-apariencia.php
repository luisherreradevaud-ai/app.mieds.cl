<?php


  $obj = new Configuracion(1);
  $media_arr = $obj->getMedia();
  $media_header = $obj->getMediaHeader();


?>
<script src="./js/ckeditor/ckeditor.js"></script>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-wrench"></i> <b>Configuraci&oacute;n: Apariencia</b></h1>
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
        <div class="col-12 mb-3">
            <h1 class="h4 mb-0 text-gray-800"><b>Pantalla de Login</b></h1>
        </div>
        <div class="col-6 mb-1">
          Color de fondo:
        </div>
        <div class="col-6 mb-1">
          <input type="color" class="form-control" name="login_color_fondo">
        </div>
        <div class="col-6 mb-1">
          Color de texto:
        </div>
        <div class="col-6 mb-1">
          <input type="color" class="form-control" name="login_color_texto">
        </div>
        <div class="col-12 mt-3 mb-3">
            <h1 class="h4 mb-0 text-gray-800"><b>Emails</b></h1>
        </div>
        <div class="col-12 mb-1">
            Encabezado:
        </div>
        <div class="col-12 mb-1">
            <textarea class="form-control" name="email_header" id="email_header"></textarea>
        </div>
        <div class="col-12 mb-1">
            Pie:
        </div>
        <div class="col-12 mb-1">
            <textarea class="form-control" name="email_footer" id="email_footer"></textarea>
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
            <img src="./media/thumbnails/640/<?= $media_header->url; ?>" class="w-100">
            <?php
            if($media_header->id == 0) {
            ?>
            <div class="d-sm-flex text-center mb-4">
            &nbsp;&nbsp;<button class="btn btn-sm btn-primary shadow-sm subir-media-btn">+ Subir imagen</button>
            </div>
            <?php } ?>
        </div>
    </div>
    

    <div class="col-12 row mt-5" id="media">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-images"></i> Imagenes</h6>
            </div>
            <div class="card-body">
                <div class="row text-center text-lg-left">
                <?php
                foreach($media_arr as $key=>$media) {
                    $media = new Media($media['id']);
                    $activo = "";
                    if(( $obj->id_media_header == 0 && $key == 0 ) || $obj->id_media_header == $media->id ) {
                    $activo = " media-activo";
                    }
                    ?>
                    <div class="col-lg-3 col-md-4 col-6">
                    <img class="img-fluid img-thumbnail obj-media-img<?= $activo; ?>" src="../media/images/<?= $media->url; ?>" width="300" alt="<?= $media->nombre; ?>" data-idmedia="<?= $media->id; ?>" data-url="<?= $media->url; ?>">
                    </div>
                    <?php } ?>
                </div>
                <br />
                <div class="d-sm-flex align-items-center mb-4">
                    <button class="d-sm-inline-block btn btn-sm btn-primary shadow-sm subir-media-btn">+ Subir imagen</button>
                </div>
            </div>
            </div>
        </div>
      </div>




<div class="modal fade" id="agregar-media-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-fw fa-images"></i> Subir media</h5>
                <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="font-weight: bold">
            <form id="agregar-media-form" action="./php/procesar.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="modo" value="subir-media">
              <input type="hidden" name="entidad" value="<?= $obj->table_name; ?>">
              <input type="hidden" name="id_<?= $obj->table_name; ?>" value="<?= $obj->id; ?>">
            <div class="form-group">
              <label for="nombre-name" class="control-label">Nombre:</label>
              <input type="text" class="form-control" name="media_nombre" value="Logo">
            </div>
            <div class="form-group">
              <label for="descripcion-text" class="control-label">Descripci&oacute;n:</label>
              <textarea type="text" class="form-control" name="media_descripcion">Logotipo de la empresa</textarea>
            </div>
            <div class="form-group">
              <label for="archivo-text" class="control-label">Archivo:</label>
              <input type="file" class="form-control" name="file" accept="image/jpeg image/jpg">
            </div>
          </form></div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-bs-dismiss="modal">Cancelar</button>
                <a class="btn btn-primary btn-sm shadow-sm" href="#" onclick="document.getElementById('agregar-media-form').submit()">Subir</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="eliminar-media-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-fw fa-images"></i> Media</h5>
                <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
            <div class="row">
              <div class="col-6">
                <img src="" id="eliminar-media-img" width="70%">
              </div>
              <div class="col-6">
                <b>URL:</b>
                <br/>
                <textarea class="form-control" id="url-txt" READONLY></textarea>
                <br/>
                <br/>
                <button class="btn btn-success" id="elegir-header-btn">Elegir como Imagen Principal</button>
              </div>
            </div>
              
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger" id="obj-media-eliminar-btn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

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

    CKEDITOR.replace( 'email_header', {
        uiColor: '#f8f9fc',
        height: 260
    });
    CKEDITOR.replace( 'email_footer', {
        uiColor: '#f8f9fc',
        height: 260
    });
    
});


$(document).on('click','.obj-media-img',function(e) {
  e.preventDefault();
  id_media_eliminar = $(e.currentTarget).data('idmedia');
  var imagen = $('#eliminar-media-img')[0];
  imagen.src = "../media/images/" + $(e.currentTarget).data('url');
  $('#url-txt').val("https://app.mieds.cl/media/images/" + $(e.currentTarget).data('url'));
  $('#eliminar-media-modal').modal('toggle');
});

$(document).on('click','#obj-media-eliminar-btn',function(e){

    e.preventDefault();

var data = {
  'id': obj.id,
  'entidad': obj.table_name,
  'id_media': id_media_eliminar
}

var url = './ajax/ajax_eliminarMedia.php';
$.post(url,data,function(raw){
  console.log(raw);
  var response = JSON.parse(raw);
  if(response.status!="OK") {
    alert("Algo fallo");
    return false;
  } else {
    window.location.href = "./?s=configuracion-apariencia&msg=3&id=" + obj.id;
  }
}).fail(function(){
  alert("No funciono");
});
});

$(document).on('click','#elegir-header-btn',function(e){

e.preventDefault();

var data = {
  'id': obj.id,
  'entidad': obj.table_name,
  'id_media': id_media_eliminar
}

var url = './ajax/ajax_cambiarMediaHeader.php';
$.post(url,data,function(raw){
  console.log(raw);
  var response = JSON.parse(raw);
  if(response.status!="OK") {
    alert("Algo fallo");
    return false;
  } else {
    window.location.href = "./?s=configuracion-apariencia&msg=4&id=" + obj.id;
  }
}).fail(function(){
  alert("No funciono");
});
});

$("#url-txt").focus(function() { $(this).select(); } );

$('.subir-media-btn').click(function(e) {
    e.preventDefault();
    $('#agregar-media-modal').modal('toggle');
});


$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("configuraciones");
  data['email_header'] = CKEDITOR.instances.email_header.getData();
  data['email_footer'] = CKEDITOR.instances.email_footer.getData();

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=configuracion-apariencia&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});



</script>