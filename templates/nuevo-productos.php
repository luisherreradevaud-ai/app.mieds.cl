<?php

  $recetas['Cerveza'] = Receta::getAll('WHERE clasificacion="Cerveza"');
  $recetas['Kombucha'] = Receta::getAll('WHERE clasificacion="Kombucha"');
  $recetas['Agua saborizada'] = Receta::getAll('WHERE clasificacion="Agua saborizada"');
  $recetas['Agua fermentada'] = Receta::getAll('WHERE clasificacion="Agua fermentada"');
  $usuario = $GLOBALS['usuario'];

  // Formatos de envases para configuracion de cajas
  $formatos_latas = FormatoDeEnvases::getAllByTipo('Lata');
  $formatos_botellas = FormatoDeEnvases::getAllByTipo('Botella');

 ?>
<style>
.tr-productos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-plus"></i> <b>Detalle Producto</b></h1>
  </div>
  <div>
    <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr />
<?php 
  Msg::show(1,'Producto guardado con &eacute;xito','primary');
?>
<form id="productos-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="productos">
  <div class="row">
    <div class="col-md-6">
    <div class="row">
    <div class="col-6 mb-1">
        C&oacute;digo:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control bg-light" value="Se generar&aacute; al guardar" readonly>
        <small class="text-muted">Basado en la l&iacute;nea productiva (ALC/SNA/GEN-XXX)</small>
      </div>
    <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" name="nombre" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Tipo:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="tipo">
            <option>Barril</option>
            <option>Caja</option>
        </select>
      </div>
      <!-- Cantidad Barril (solo visible cuando tipo=Barril) -->
      <div id="config-cantidad-barril">
        <div class="row">
          <div class="col-6 mb-1">
            Cantidad:
          </div>
          <div class="col-6 mb-1">
            <select class="form-control" name="cantidad">
            </select>
          </div>
        </div>
      </div>
      <!-- Configuracion de Envases (solo visible cuando tipo=Caja) -->
      <div id="config-envases" style="display:none;">
        <div class="row">
          <div class="col-6 mb-1">
            Tipo de Envase:
          </div>
          <div class="col-6 mb-1">
            <select name="tipo_envase" class="form-control">
              <option value="Lata">Lata</option>
              <option value="Botella">Botella</option>
            </select>
          </div>
          <div class="col-6 mb-1">
            Formato:
          </div>
          <div class="col-6 mb-1">
            <select name="id_formatos_de_envases" class="form-control">
            </select>
          </div>
          <div class="col-6 mb-1">
            Envases por Caja:
          </div>
          <div class="col-6 mb-1">
            <input type="number" name="cantidad_de_envases" class="form-control" min="1" value="0">
          </div>
          <div class="col-12 mb-1 mt-2">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" name="es_mixto" id="es_mixto_check" value="1">
              <label class="form-check-label" for="es_mixto_check">
                <strong>Es Producto Mixto</strong> <small class="text-muted">(acepta m&uacute;ltiples tipos de cerveza)</small>
              </label>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 mb-1">
        Clasificaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="clasificacion">
            <option>Cerveza</option>
            <option>Kombucha</option>
            <option>Agua saborizada</option>
            <option>Agua fermentada</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        L&iacute;nea Productiva:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="linea_productiva">
            <option value="general">General</option>
            <option value="alcoholica">L&iacute;nea Alcoh&oacute;lica</option>
            <option value="analcoholica">L&iacute;nea Sin Alcohol</option>
        </select>
      </div>
      <div id="config-receta">
        <div class="row">
          <div class="col-6 mb-1">
            Receta:
          </div>
          <div class="col-6 mb-1">
            <select class="form-control" name="id_recetas">
            </select>
          </div>
        </div>
      </div>
      <div id="config-receta-mixto" style="display:none;">
        <div class="row">
          <div class="col-6 mb-1">
            Receta:
          </div>
          <div class="col-6 mb-1">
            <input type="text" class="form-control" value="M&uacute;ltiples (Producto Mixto)" disabled>
          </div>
        </div>
      </div>
      <div class="col-6 mb-1">
        Monto:
      </div>
      <div class="col-6 mb-1">
        <input type="number" class="form-control" name="monto" READONLY>
      </div>
      <div class="col-6 mb-1">
        C&oacute;digo de Barra:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="codigo_de_barra">
      </div>
      <div class="col-6 mb-1">
        Visible:
      </div>
      <div class="col-6 mb-1">
        <select name="visible" class="form-control">
          <option value="0">
            No
          </option>
          <option value="1" SELECTED>
            Si
          </option>
        </select>
      </div>
      </div>
    </div>
    <div class="col-md-6" style="vertical-align: top">
    <div class="row">
      <div class="col-12 mb-1">
        <h1 class="h5 mb-0 text-gray-800">Factura</h1>
      </div>
      <div class="col-12 mt-3 mb-1">
        <table class="table table-sm" style="border: 1px solid #5a5c68">
          <thead class="table-dark">
            <th>
              &Iacute;tem
            </th>
            <th>
              P Unitario
            </th>
            <th>
              Impuesto
            </th>
            <th style="width: 10px">
            </th>
          </thead>
          <tbody id="items-table">
          </tbody>
        </table>
        <table class="mt-3 table table-sm table-striped" style="border: 1px solid #5a5c68">
          <tbody>
          <tr>
          <td>Neto $</td>
          <td id="totales-neto">0</td>
          </tr>
          <tr>
          <td>IVA (19%) $</td>
          <td id="totales-iva">0</td>
          </tr>
          <tr>
          <td>Cervezas y Bebidas Alcoh. (20.5%) $</td>
          <td id="totales-ila">0</td>
          </tr>
          <tr>
          <td>Total $</td>
          <td id="totales-total">0</td>
          </tr>
          </tbody>
        </table>
        
      </div>
      <div class="col-12 mb-1 text-left">
        <button class="btn btn-primary btn-sm" id="items-agregar-btn"><i class="fas fa-fw fa-plus"></i> &Iacute;tem</button>
      </div>
      
      </div>
    </div>
    <div class="col-12 mb-1">
      <hr/>
    </div>
    <div class="col-12 mb-1 d-flex justify-content-between">
      <button class="btn btn-primary btn-sm" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
      <button class="btn btn-primary btn-sm" id="guardar-y-agregar-btn"><i class="fas fa-fw fa-save"></i> Guardar y agregar Nuevo</button>
    </div>
  </div>
</form>

<div class="modal" tabindex="-1" role="dialog" id="itemsModal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar &Iacute;tem</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-6 mb-3">
              &Iacute;tem
            </div>
            <div class="col-6 mb-3">
              <input type="text" name="items_nombre" class="form-control">
            </div>
            <div class="col-6 mb-3">
              P Unitario
            </div>
            <div class="col-6 mb-3">
              <div class="input-group">
                <span class="input-group-text" style="border-radius: 10px 0px 0px 10px">$</span>
                <input type="number" class="form-control acero" name="items_monto_bruto">
              </div>
            </div>
            <div class="col-6 mb-3">
              Impuesto
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" name="items_impuesto">
                <option>IVA</option>
                <option>IVA + ILA</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="items-agregar-aceptar-btn" data-bs-dismiss="modal">Agregar</button>
        </div>
      </div>
    </div>
  </div>

<script>

var recetas = <?= json_encode($recetas,JSON_PRETTY_PRINT); ?>;
var items = [];
var tipos_barril = <?= json_encode($GLOBALS['tipos_barril'],JSON_PRETTY_PRINT); ?>;
var tipos_caja = <?= json_encode($GLOBALS['tipos_caja'],JSON_PRETTY_PRINT); ?>;
var formatos_latas = <?= json_encode($formatos_latas,JSON_PRETTY_PRINT); ?>;
var formatos_botellas = <?= json_encode($formatos_botellas,JSON_PRETTY_PRINT); ?>;

$(function() {
  armarRecetasSelect();
  armarCantidadSelect();
});

$(document).on('change','select[name="clasificacion"]',function(){
    armarRecetasSelect();
});

$(document).on('change','select[name="tipo"]',function(){
    armarCantidadSelect();
});

function armarRecetasSelect() {

  var recetas_p = recetas[$('select[name="clasificacion"]').val()];

  $('select[name="id_recetas"]').empty();
  recetas_p.forEach(function(r) {
    $('select[name="id_recetas"]').append("<option value='" + r.id + "'>" + r.nombre + "</option>");
  });
  $('select[name="id_recetas"]').append("<option value='0'>No Especifica</option>");

}

function armarCantidadSelect() {
  var tipo = $('select[name="tipo"]').val();

  if(tipo == 'Barril') {
    cantidades = tipos_barril;
    $('#config-cantidad-barril').show();
    $('#config-envases').hide();

    $('select[name="cantidad"]').empty();
    cantidades.forEach(function(c) {
      $('select[name="cantidad"]').append("<option>" + c + "</option>");
    });
  } else {
    $('#config-cantidad-barril').hide();
    $('#config-envases').show();
    armarFormatosSelect();
  }
}

function armarFormatosSelect() {
  var tipo_envase = $('select[name="tipo_envase"]').val();
  var formatos = tipo_envase == 'Lata' ? formatos_latas : formatos_botellas;

  $('select[name="id_formatos_de_envases"]').empty();
  formatos.forEach(function(f) {
    $('select[name="id_formatos_de_envases"]').append(
      '<option value="' + f.id + '">' + f.nombre + ' (' + f.volumen_ml + 'ml)</option>'
    );
  });
}

$(document).on('change', 'select[name="tipo_envase"]', function() {
  armarFormatosSelect();
});

// Handler para checkbox es_mixto
$(document).on('change', '#es_mixto_check', function() {
  toggleMixto($(this).is(':checked'));
});

function toggleMixto(esMixto) {
  if(esMixto) {
    $('#config-receta').hide();
    $('#config-receta-mixto').show();
    $('select[name="id_recetas"]').val('0');
  } else {
    $('#config-receta').show();
    $('#config-receta-mixto').hide();
    armarRecetasSelect();
  }
}

$(document).on('click','#items-agregar-aceptar-btn',function() {
  items.push({
    'nombre': $('input[name="items_nombre"]').val(),
    'monto_bruto': $('input[name="items_monto_bruto"]').val(),
    'impuesto': $('select[name="items_impuesto"]').val()
  });
  renderTable();
});

function renderTable() {

  var neto = 0;
  var iva = 0;
  var ila = 0;
  var total = 0;
  var html = '';

  items.forEach(function(item,index){
    neto += parseInt(item.monto_bruto);
    if(item.impuesto == 'IVA + ILA') {
      ila += parseInt(item.monto_bruto) * 0.205;
    }
    html += '<tr class="productos-tr" data-index="' + index +'"><td>' + item.nombre;
    html += '</td><td>' + item.monto_bruto;
    html += '</td><td>' + item.impuesto;
    html += '</td><td style="width:10px"><button class="btn btn-sm item-eliminar-btn" data-index="' + index + '">x</button>';
    html += '</td></tr>';
  });

  iva = neto * 0.19;
  total = Math.round(neto + iva + ila);

  $('#totales-neto').html(neto);
  $('#totales-iva').html(iva);
  $('#totales-ila').html(ila);
  $('#totales-total').html(total);
  $('#items-table').html(html);
  $('input[name="monto"]').val(total);

}

$(document).on('click','.item-eliminar-btn',function(e){
  var index = $(e.currentTarget).data('index');
  items.splice(index,1);
  renderTable();
});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("productos");
  data['items'] = items;
  // Manejar checkbox es_mixto
  data['es_mixto'] = $('#es_mixto_check').is(':checked') ? '1' : '0';

  console.log(data);


  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-productos&id=" + response.obj.id + "&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#guardar-y-agregar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("productos");
  data['items'] = items;
  // Manejar checkbox es_mixto
  data['es_mixto'] = $('#es_mixto_check').is(':checked') ? '1' : '0';

  console.log(data);

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-productos&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','#items-agregar-btn',function(e){
  e.preventDefault();
  $('input[name="items_nombre"]').val('');
  $('input[name="items_monto_bruto"]').val('0');
  $('select[name="items_impuesto"]').val('IVA');
  $('#itemsModal').modal('toggle');
})


</script>
