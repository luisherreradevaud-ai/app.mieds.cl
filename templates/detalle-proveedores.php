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

$obj = new Proveedor($id);

$tipos_barril = $GLOBALS['tipos_barril'];
$tipos_barril_cerveza = $GLOBALS['tipos_barril_cerveza'];
$tipos_de_insumos = TipoDeInsumo::getAll();

$usuario = $GLOBALS['usuario'];

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
        <?php
        if($obj->id=="") {
          print '<i class="fas fa-fw fa-plus"></i> Nuevo';
        } else {
          print '<i class="fas fa-fw fa-handshake"></i> Detalle';
        }
        ?> Proveedor
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
  Msg::show(1,'Proveedor guardado con &eacute;xito','primary');
?>

<form id="proveedores-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="proveedores">
  <input type="hidden" name="id_usuarios" value="">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Tipos de Insumos:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="ids_tipos_de_insumos[]" multiple>
          <?php
            foreach($tipos_de_insumos as $tdi) {
              print "<option value='".$tdi->id."'";
              if(in_array($tdi,$obj->tipos_de_insumos)) {
                print " SELECTED";
              }
              print ">".$tdi->nombre."</option>";
            }
          ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="nombre">
      </div>
      <div class="col-6 mb-1">
        Email:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="email">
      </div>
      <div class="col-6 mb-1">
        Telefono:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="telefono">
      </div>
      <div class="col-6 mb-1">
        RUT Empresa:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="rut_empresa">
      </div>
      <div class="col-6 mb-1">
        N&uacute;mero de cuenta:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="numero_cuenta">
      </div>
      
      <div class="col-12 mb-1">
        Comentarios:
      </div>
      <div class="col-12 mb-1">
        <textarea name="comentarios" class="form-control"></textarea>
      </div>

      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <?php
        if($obj->id != "") {
          ?>
          <button class="btn btn-sm btn-danger eliminar-obj-btn">Eliminar</button>
          <?php
        } else {
          print "&nbsp;";
        }
        ?>
        <button class="btn btn-sm btn-primary" id="guardar-btn">Guardar</button>
      </div>
    </div>
  </div>
</form>


<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-proveedor-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Proveedor</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este proveedor?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-proveedor-aceptar" data-bs-dismiss="modal">Eliminar</button>
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

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("proveedores");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-proveedores&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
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

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-proveedor-modal').modal('toggle');
})

$(document).on('click','#eliminar-proveedor-aceptar',function(e){

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
