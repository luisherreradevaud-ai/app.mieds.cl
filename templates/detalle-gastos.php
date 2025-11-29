<?php


$id = "";

if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
}

$obj = new Gasto($id);
$media_arr = $obj->getMedia();

$usuario = $GLOBALS['usuario'];
if($usuario->nivel == "Administrador")  {
  $tipos_de_gasto = TipoDeGasto::getAll("ORDER BY nombre asc");
} else {
  $tipos_de_gasto = TipoDeGasto::getAll("WHERE nombre='Gas' OR nombre='Caja Chica' OR nombre='Combustible' OR nombre='Envios' ORDER BY nombre asc");
}

?>
<style>
.tr-gasto {
  cursor: pointer;
}
.media-activo {
  border: 2px solid red;
}

.obj-archivo-img {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Detalle Gasto
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
  Msg::show(1,'Gasto guardado con &eacute;xito','primary');
  Msg::show(2,'Media eliminada con &eacute;xito','danger');
  Msg::show(6,'Media cambiada con &eacute;xito','danger');

  if($obj->aprobado == 0 ) {
    if($usuario->nivel == "Administrador") {
?>

<div class="alert alert-warning" role="alert">
  <div class="alert-icon">
    <i class="far fa-fw fa-bell"></i>
  </div>
  <div class="alert-message d-flex justify-content-between">
      <div>
        <b>Este gasto está pendiente de aprobación. ¿Que acción desea tomar?</b>
      </div>
      <div>
        <button class="btn btn-sm btn-info" id="gastos-aprobar-btn"><i class="fas fa-fw fa-check"></i> Aprobar</button>
        <button class="btn btn-sm btn-warning" id="gastos-rechazar-btn" ><b>x</b> Rechazar</button>
      </div>
  </div>
</div>
<?php
    } else {
?>
<div class="alert alert-warning alert-dismissible" role="alert">
	<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  <div class="alert-icon">
    <i class="far fa-fw fa-bell"></i>
  </div>
  <div class="alert-message d-flex justify-content-between">
      <div>
        <b>Este gasto está pendiente de aprobación.</b>
      </div>
  </div>
</div>

<?php
    }
  } else
  if($obj->aprobado == -1 ) {
?>
<div class='alert alert-danger d-flex justify-content-between'>
  <div>
    <b>Este gasto se encuentra rechazado y no puede ser revertido.</b>
  </div>
</div>
<?php
  }
?>
<form id="gastos-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="gastos">
  <input type="hidden" name="id_usuarios" value="">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-3">
        Ingresado por:
      </div>
      <div class="col-6 mb-3">
       <b> <?php
          $usuario_ingreso = new Usuario($obj->id_usuarios);
          print $usuario_ingreso->nombre;
        ?>
       </b>
      </div>
      <div class="col-6 mb-1">
        Fecha:
      </div>
      <div class="col-6 mb-1">
        <input type="date" value="" class="form-control" name="creada">
      </div>
      <div class="col-6 mb-1">
        &Iacute;tem:
      </div>
      <div class="col-6 mb-1">
        <input type="text" name="item" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Tipo de Gasto:
      </div>
      <div class="col-6 mb-1">
        <select name="tipo_de_gasto" class="form-control">
        <?php
                foreach($tipos_de_gasto as $tipo) {
                    print "<option>".$tipo->nombre."</option>";
                }
            ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Monto:
      </div>
      <div class="col-6 mb-1">
        <div class="input-group">
          <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
          <input type="text" class="form-control acero" name="monto">
        </div>
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select name="estado" class="form-control">
            <option>Pagado</option>
            <option>Por Pagar</option>
        </select>
      </div>
      <div class="col-6 mb-1 date_vencimiento">
        Vencimiento:
      </div>
      <div class="col-6 mb-1 date_vencimiento">
        <input type="date" name="date_vencimiento" class="form-control" value="">
      </div>
      <div class="col-12 mb-1">
        Comentarios:
      </div>
      <div class="col-12 mb-1">
        <textarea name="comentarios" class="form-control"></textarea>
      </div>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
      <?php
        if($usuario->nivel == "Administrador") {
          ?>
          <div>
            <button class="btn btn-sm btn-danger eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
          </div>
          <?php
        } else {
          print "&nbsp;";
        }
        ?>
        <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
      </div>
    </div>
  </div>
</form>

      <div class="col-12 row mt-5" id="media">
        <div class="col-xl-12 col-lg-12">
          <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-images"></i> Imagenes</h6>
            </div>
            <div class="card-body">
                <div class="row text-center text-lg-left">
                <?php
                foreach($media_arr as $key=>$media) {
                  if($media['id'] == 0 || $media['url'] == '') {
                    continue;
                  }
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


<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Gasto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este gasto?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal">Eliminar</button>
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
              <input type="text" class="form-control" name="media_nombre" value="<?= $obj->item." ".(count($media_arr)+1); ?>">
            </div>
            <div class="form-group">
              <label for="descripcion-text" class="control-label">Descripci&oacute;n:</label>
              <textarea type="text" class="form-control" name="media_descripcion">Imagen de <?= $obj->item; ?></textarea>
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
              <div class="col-12">
                <img src="" id="eliminar-media-img" width=100%">
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


<div class="modal fade" tabindex="-1" role="dialog" id="gastos-rechazar-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Rechazar Gasto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="rechazoDate">Fecha</label>
            <input type="date" class="form-control" id="gastos-rechazo-date-input" name="rechazoDate" value="<?= date('Y-m-d'); ?>" required>
          </div>
          <div class="form-group">
            <label for="motivoRechazo">Motivo de Rechazo</label>
            <textarea class="form-control" id="gastos-rechazo-motivo-input" name="motivo_rechazo" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-sm btn-warning" id="gastos-rechazar-aceptar-btn" ><b>x</b> Rechazar</button>
        </div>
      </div>
    </div>
  </div>


<script>

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
var usuario_nivel = '<?= $usuario->nivel; ?>';
$(document).ready(function(){
  
  if(obj.id!="") {
    $.each(obj,function(key,value){
      console.log(key);
      if(key!="table_name"&&key!="table_fields"){
        $('#gastos-form input[name="'+key+'"]').val(value);
        $('#gastos-form textarea[name="'+key+'"]').val(value);
        $('#gastos-form select[name="'+key+'"]').val(value);
      }
      if(usuario_nivel!='Administrador') {
        $('#gastos-form select[name="aprobado"]').attr('disabled',true);
        $('#gastos-form select[name="estado"]').attr('disabled',true);
      }
    });
    if( obj.aprobado == '-1' ) {
      $('#gastos-form input').attr('disabled',true);
      $('#gastos-form textarea').attr('disabled',true);
      $('#gastos-form select').attr('disabled',true);
      $('#guardar-btn').attr('disabled',true);
    }
  }
  if($('select[name="estado"]').val() == 'Por Pagar') {
    $('.date_vencimiento').show();
  } else {
    $('.date_vencimiento').hide();
  }
});

$(document).on('change','select[name="estado"]',function(e) {
  if($(e.currentTarget).val() == 'Por Pagar') {
    $('.date_vencimiento').show();
  } else {
    $('.date_vencimiento').hide();
  }
})

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="monto"]').val() == "") {
    $('input[name="monto"]').val(0);
  }

  if($('input[name="monto"]').val() < 1) {
    alert("El monto debe ser mayor a 0.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("gastos");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-gastos&id=" + response.obj.id + "&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});


$(document).on('keyup','.acero',function(){
  $(this).val($(this).val().replace(/\D/g,''));
  if($(this).val() == "") {
    $(this).val(0);
  }
  $(this).val(parseInt($(this).val()));
});

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-obj-modal').modal('toggle');
})

$(document).on('click','#eliminar-obj-aceptar',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "<?= $usuario->getReturnLink(); ?>";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','.obj-media-img',function(e) {
  e.preventDefault();
  id_media_eliminar = $(e.currentTarget).data('idmedia');
  var imagen = $('#eliminar-media-img')[0];
  imagen.src = "../media/images/" + $(e.currentTarget).data('url');
  $('#url-txt').val("https://app.barril.cl/media/images/" + $(e.currentTarget).data('url'));
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
    window.location.href = "./?s=detalle-gastos&msg=2&id=" + obj.id;
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
    window.location.href = "./?s=detalle-gastos&msg=6&id=" + obj.id;
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


$(document).on('click','#gastos-rechazar-btn',function(e){
  e.preventDefault();
  $('#gastos-rechazar-modal').modal('toggle');
});


$(document).on('click','#gastos-rechazar-aceptar-btn',function(e){

  e.preventDefault();

  if ($('#gastos-rechazo-motivo-input').val().trim() === '') {
      alert('Por favor, ingrese un motivo de rechazo.');
      return;
  }

  var data = {
    'id': obj.id,
    'entidad': obj.table_name,
    'aprobado': ' -1',
    'rechazo_motivo': $('#gastos-rechazo-motivo-input').val(),
    'rechazo_date': $('#gastos-rechazo-date-input').val()
  }

  var url = './ajax/ajax_guardarEntidad.php';
  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-gastos&id=" + obj.id;
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#gastos-aprobar-btn',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'entidad': obj.table_name,
    'aprobado': '1'
  }

  var url = './ajax/ajax_guardarEntidad.php';
  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-gastos&id=" + obj.id;
    }
  }).fail(function(){
    alert("No funciono");
  });
});



function toggleButton() {
  if ($('#gastos-rechazo-motivo-input').val().trim() !== '') {
      $('#gastos-rechazar-aceptar-btn').attr('disabled', false);
  } else {
      $('#gastos-rechazar-aceptar-btn').attr('disabled', true);
      console.log("desacti");
  }
}

$('#gastos-rechazar-modal').on('shown.bs.modal', function () {
  $('#gastos-rechazo-motivo-input').val('');
    toggleButton();
});

$(document).on('change', '#gastos-rechazo-motivo-input', function() {
  toggleButton();
});


</script>
