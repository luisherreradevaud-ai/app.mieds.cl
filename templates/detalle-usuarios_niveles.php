<?php

$id = "";

if(!validaIdExists($_GET,'id')) {
  die();
}

$obj = new UsuarioNivel($_GET['id']);
$tipos_notificaciones = TipoDeNotificacion::getAll("order by nombre asc");
$nuns = TipoDeNotificacionUsuarioNivel::getAll("WHERE id_usuarios_niveles='".$obj->id."'");
$usuario = $GLOBALS['usuario'];

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
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-user"></i> 
      <b>
        Detalle Nivel de Usuario
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
<form id="usuarios_niveles-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="<?= $obj->table_name; ?>">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-3">
        Nombre:
      </div>
      <div class="col-6 mb-3">
       <input type="text" class="form-control" name="nombre" DISABLED>
      </div>
      <div class="col-12 mb-1 d-none">
        Comentarios:
      </div>
      <div class="col-12 mb-1 d-none">
        <textarea name="comentarios" class="form-control"></textarea>
      </div>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
      <?php /*
        if($obj->id != "") {
          ?>
          <button class="btn btn-sm btn-danger eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
          <?php
        } else {
          print "&nbsp;";
        }
        ?>
        <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
      
      <?php
        */
      ?>
      </div>
    </div>
  </div>
</form>

<div class="d-sm-flex align-items-center justify-content-between mt-1 mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800">
      <b>
        Notificaciones
      </b>
    </h1>
  </div>
</div>
<?php 
  Msg::show(1,'Cambio en notificaciones guardado con &eacute;xito','primary');
?>
<form id="notificaciones-form">
<table class="table table-sm table-striped">
<thead class="thead-dark">
    <th>
        Notificaci&oacute;n
    </th>
    <th>
        <center><i class="fas fa-fw fa-bell"></i> App
    </th>
    <th>
        <center><i class="fas fa-fw fa-envelope"></i> Email 
    </th>
</thead>
<tbody>
<?php
foreach($tipos_notificaciones as $tn) {
    ?>
    <tr>
        <td>
            <?= $tn->nombre; ?>
        </td>
        <td>
            <center><input type="checkbox" name="nun_<?= $tn->id; ?>_app">
        </td>
        <td>
            <center><input type="checkbox" name="nun_<?= $tn->id; ?>_email">
        </td>
    <?php
}
?>
</tbody>
</table>
</form>
<button class="btn btn-sm btn-primary mt-3 mb-3" id="notificaciones-guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar Notificaciones</button>



<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Nivel de Usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este Nivel de Usuario?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

<script>

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
var nuns = <?= json_encode($nuns,JSON_PRETTY_PRINT); ?>;

$(document).ready(function(){
  
  if(obj.id!="") {
    $.each(obj,function(key,value){
      if(key!="table_name"&&key!="table_fields"){
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
      }
    });
  }

  nuns.forEach(function(nun){
    if(nun.app == 1) {
      var nombre = 'input[name="nun_' + nun.id_tipos_de_notificaciones + '_app"]';
      $(nombre).attr('checked',true);
    }
    if(nun.email == 1) {
      var nombre = 'input[name="nun_' + nun.id_tipos_de_notificaciones + '_email"]';
      $(nombre).attr('checked',true);
    }
  });

});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val() == "") {
    alert("El nombre debe tener al menos 1 caracter.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm(obj.table_name);

  $.post(url,data,function(raw){
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-usuarios_niveles&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#notificaciones-guardar-btn',function(e){

  e.preventDefault();


  var url = "./ajax/ajax_guardarNotificaciones.php";
  var data = getDataForm('notificaciones');
  data['id_usuarios_niveles'] = obj.id;


  $.post(url,data,function(raw){
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-usuarios_niveles&id=" + obj.id + "&msg=1";
    }
  }).fail(function(){
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

</script>
