<?php


$id = "";

if(!validaIdExists($_GET,'id')) {
  die();
}

$id = $_GET['id'];

$obj = new Entrega($id);
$entregas_productos = EntregaProducto::getAll("WHERE id_entregas='".$obj->id."'");
$repartidores = Usuario::getAll("WHERE nivel='repartidor'");
$clientes = Cliente::getAll();
$pagos = Pago::getAll("WHERE ids_entregas='".$obj->id."' ORDER BY id asc");

$usuario = $GLOBALS['usuario'];

if($usuario->nivel == "Cliente") {
  die();
}

?>
<style>
.tr-entregas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        <?php
        if($obj->id=="") {
          print '<i class="fas fa-fw fa-plus"></i> Nueva';
        } else {
          print '<i class="fas fa-fw fa-truck"></i> Detalle';
        }
        ?> Entrega
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
  Msg::show(1,'Entrega guardada con &eacute;xito.','info');
  Msg::show(2,'Pago generado con &eacute;xito.','info');
  Msg::show(3,'Abono generado con &eacute;xito.','info');
?>
<form id="entregas-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="entregas">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Fecha:
      </div>
      <div class="col-6 mb-1">
        <input type="datetime-local" name="creada" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Repartidor:
      </div>
      <div class="col-6 mb-1">
        <select name="id_usuarios_repartidor" class="form-control">
          <?php
          foreach($repartidores as $repartidor) {
            print "<option value='".$repartidor->id."'>".$repartidor->nombre."</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Cliente:
      </div>
      <div class="col-6 mb-1">
        <select name="id_clientes" class="form-control">
          <?php
          foreach($clientes as $cliente) {
            print "<option value='".$cliente->id."'>".$cliente->nombre."</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select name="estado" class="form-control">
          <option>Entregada</option>
          <option>Vencida</option>
          <option>Documentada</option>
          <option>Documento Rechazado</option>
          <?php
          if($obj->estado == "Pagada") {
            print "<option>Pagada</option>";
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
        Factura:
      </div>
      <div class="col-6 mb-1">
        <?php
        if(is_numeric($obj->factura)) {
          ?>
          <div class="input-group">
            <input type="text" class="form-control" name="factura">
            <a href="./php/dte.php?folio=<?= $obj->factura; ?>" class="btn btn-outline-secondary" type="button" style="border-radius: 0px 5px 5px 0px">Ver factura</a>
          </div>
          <?php
        } else {
          ?>
          <div class="input-group">
            <input type="text" class="form-control" name="factura">
            <button class="btn btn-outline-primary" type="button" style="border-radius: 0px 5px 5px 0px" id="generar-factura-btn">Generar factura</button>
          </div>
          <?php
        }
        ?>
      </div>
      <div class="col-6 mb-1">
        Receptor Entrega:
      </div>
      <div class="col-6 mb-1">
        <input name="receptor_nombre" type="text" class="form-control">
      </div>
      <div class="col-12 mt-2 mb-1">
        Observaciones:
      </div>
      <div class="col-12 mb-1">
        <textarea name="observaciones" class="form-control"></textarea>
      </div>
      <?php
      if($usuario->nivel == "Administrador") {
      ?>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <button class="btn btn-danger eliminar-obj-btn btn-sm" style="float: right"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
        <button class="btn btn-primary btn-sm" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
      </div>
      <?php
      }
      ?>
      </form>
    </div>
    <div class="col-md-6">
      <h6 class="mb-3"><i class="fas fa-fw fa-box"></i> Productos Entregados</h6>
      <table class="table table-sm">
        <thead class="table-light">
          <tr>
            <th>Tipo</th>
            <th>Cant.</th>
            <th>Producto</th>
            <th>Código</th>
            <th>Monto</th>
            <th>Trazabilidad</th>
          </tr>
        </thead>
        <tbody>
        <?php
        foreach($entregas_productos as $ep) {
          // Verificar si tiene trazabilidad disponible (barril o caja de envases)
          $tiene_trazabilidad = ($ep->id_barriles > 0 || $ep->id_cajas_de_envases > 0);
          ?>
          <tr>
            <td>
              <?= $ep->tipo; ?>
            </td>
            <td>
              <?= $ep->cantidad; ?>
            </td>
            <td>
              <?= $ep->tipos_cerveza; ?>
            </td>
            <td>
              <b><?= $ep->codigo; ?></b>
            </td>
            <td>
              <b>$<?= number_format($ep->monto); ?></b>
            </td>
            <td>
              <?php if($tiene_trazabilidad): ?>
              <a href="./ajax/ajax_generarPDFTrazabilidad.php?id=<?= $ep->id; ?>"
                 class="btn btn-sm btn-outline-primary"
                 target="_blank"
                 title="Descargar PDF de Trazabilidad">
                <i class="fas fa-file-pdf"></i>
              </a>
              <?php else: ?>
              <span class="text-muted" title="Sin trazabilidad disponible">-</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php
        }
        ?>
        </tbody>
      </table>
    </div>
  </div>

  <!--
  <div class="d-sm-flex align-items-center justify-content-between mb-3 mt-5">
    <div class="mb-2">
      <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-handshake"></i> <b>Abonos</b></h1>
    </div>
    <div>
      <div>

      </div>
    </div>
  </div>
  <hr />
  <table class="table table-hover table-striped table-sm">
    <thead class="thead-dark">
      <tr>
        <th>
          Fecha
        </th>
        <th>
          Forma de pago
        </th>
        <th>
          Monto
        </th>
      </tr>
    </thead>
    <tbody>
      <?php
        foreach($pagos as $pago) {
          $cliente = new Cliente($pago->id_clientes);
      ?>
      <tr class="tr-clientes" data-idclientes="<?= $cliente->id; ?>">
        <td>
          <?= datetime2fechayhora($pago->creada); ?>
        </td>
        <td>
          <?= $pago->forma_de_pago; ?>
        </td>
        <td>
          $<?= number_format($pago->amount); ?>
        </td>
      </tr>
      <?php
        }
      ?>
    </tbody>
  </table>
  <br/>
  <?php
  if($obj->estado != "Pagada") {
    ?>
    <button class="btn btn-primary" id="generar-abono-btn">Generar Abono</button>
    <?php
  }
  ?>
-->


<!-- ///////////////////////////////////////////////////////////////////// -->
<?php
  if($obj->id!="") {
?>

<!--

<button class="btn btn-danger mt-4 mb-4 eliminar-obj-btn" style="float: right">Eliminar</button>-->
<?php
}
?>

<div class="modal fade" id="generar-abono-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generar abono</h5>
                <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="font-weight: bold">
              <input type="number" class="form-control" value="0" id="generar-abono-input">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary btn-sm shadow-sm" id="generar-abono-aceptar-btn">Generar</a>
            </div>
        </div>
    </div>
</div>

<script>

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  /*if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }*/

  $('#guardar-btn').attr('DISABLED',true);

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("entregas");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-entregas&id=" + obj.id + "&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
$(document).ready(function(){
  if(obj.id!="") {
    $.each(obj,function(key,value){
      if(key!="table_name"&&key!="table_fields"){
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
        <?php
        if($usuario->nivel != "Administrador") {
        ?>
        $('input[name="'+key+'"]').attr('disabled',true);
        $('textarea[name="'+key+'"]').attr('disabled',true);
        $('select[name="'+key+'"]').attr('disabled',true);
        <?php
        }
        ?>
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
      window.location.href = "./?s=entregas&msg=2";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#generar-abono-aceptar-btn',function(e){

  e.preventDefault();

  /*if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }*/

  $('#generar-abono-input').attr('DISABLED',true);

  var url = "./ajax/ajax_generarPago.php";
  var data = {
    'id': <?= $obj->id; ?>,
    'tipopago': 'abono',
    'monto': $('#generar-abono-input').val()
  };

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-entregas&id=" + response.obj.id + "&msg=3";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#generar-abono-btn',function(){
  $('#generar-abono-modal').modal('toggle');
});

$(document).on('keyup','.acero',function(){
  $(this).val($(this).val().replace(/\D/g,''));
  if($(this).val() == "") {
    $(this).val(0);
  }
  $(this).val(parseInt($(this).val()));
});

$(document).on('click','#generar-factura-btn',function(e){

  e.preventDefault();


  $('#generar-factura-btn').attr('DISABLED',true);

  var url = "./ajax/ajax_crearFactura.php";
  var data = getDataForm("entregas");

  $.post(url,data,function(response){
    console.log(response);
    response = JSON.parse(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-entregas&id=" + obj.id + "&msg=7";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

</script>
