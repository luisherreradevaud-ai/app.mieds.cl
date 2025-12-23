<?php

  if(!validaIdExists($_GET,'id')) {
      die();
  }

  $obj = new Batch($_GET['id']);

  $recetas['Cerveza'] = Receta::getAll('WHERE clasificacion="Cerveza"');
  $recetas['Kombucha'] = Receta::getAll('WHERE clasificacion="Kombucha"');
  $recetas['Agua saborizada'] = Receta::getAll('WHERE clasificacion="Agua saborizada"');
  $recetas['Agua fermentada'] = Receta::getAll('WHERE clasificacion="Agua fermentada"');

  
  $activos = Activo::getAll();
  $barriles = Barril::getAll("WHERE estado='En planta' AND (tipo_barril='20L' OR tipo_barril='30L' OR tipo_barril='50L')");
  $tipos_caja = $GLOBALS['tipos_caja'];
  $tipos_caja_cerveza = $GLOBALS['tipos_caja_cerveza'];
  $usuarios_ejecutores = Usuario::getAll("WHERE nivel='Jefe de Cocina' AND estado='Activo' ORDER BY nombre asc");


  $batches_barriles = $obj->getRelations("barriles");
  $batches_cajas = BatchCaja::getAll("WHERE id_batches='".$obj->id."'");
  $productos = array();
  foreach($batches_barriles as $id_barriles) {
      $barril = new Barril($id_barriles);
      $productos[] = array(
          "tipo" => "Barril",
          "cantidad" => $barril->tipo_barril,
          "tipos_cerveza" => "",
          "codigo" => $barril->codigo,
          "id_barriles" => $barril->id
      );
  }
  foreach($batches_cajas as $bc) {
      $productos[] = array(
          "tipo" => "Caja",
          "cantidad" => $bc->cantidad,
          "tipos_cerveza" => "",
          "codigo" => "",
          "id_barriles" => ""
      );
  }

  $cantidad_botellas_max = 1000;
  $usuario = $GLOBALS['usuario'];

  $tipos_de_insumos = TipoDeInsumo::getAll();
  $batch_insumos = BatchInsumo::getAll("WHERE id_batches='".$obj->id."' AND tipo='Dryhop'");

  $lista = array();
  foreach($batch_insumos as $bi) {
      $insumo = new Insumo($bi->id_insumos);
      $item = array();
      $item['id'] = $insumo->id;
      $item['nombre'] = $insumo->nombre;
      $item['cantidad'] = $bi->cantidad;
      $item['date'] = $bi->date;
      $item['id_tipos_de_insumos'] = $insumo->id_tipos_de_insumos;
      $item['comentarios'] = $insumo->comentarios;
      $item['unidad_de_medida'] = $insumo->unidad_de_medida;
      $item['despacho'] = $insumo->despacho;
      $item['bodega'] = $insumo->bodega;
      $lista[] = $item;
  }

  $usuario = $GLOBALS['usuario'];
?>
<style>
.tr-gasto {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
      <i class="fas fa-fw fa-plus"></i> Detalle Batch #<?= $obj->id; ?>
      </b>
    </h1>
  </div>
    <?php $usuario->printReturnBtn(); ?>
</div>
<hr />
<form id="batches-form">
  <input type="hidden" name="entidad" value="batches">
  <input type="hidden" name="id" value="">
  <div class="row">
    <div class="col-md-8 row">
      <div class="col-6 mb-1">
        Clasificaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="clasificacion" DISABLED>
            <option>Cerveza</option>
            <option>Kombucha</option>
            <option>Agua saborizada</option>
            <option>Agua fermentada</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Receta:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="id_recetas" DISABLED>
        </select>
      </div>
      <div class="col-6 mb-1">
        Cantidad total de Litros:
      </div>
      <div class="col-6 mb-1">
        <div class="input-group">
          <input type="text" class="form-control acero" name="litros" DISABLED>
          <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">L</span>
        </div>
      </div>
      <div class="col-6 mb-1">
        Fermentador:
      </div>
      <div class="col-6 mb-1">
        <select name="id_activos" class="form-control">
        </select>
      </div>
      <div class="col-6 mb-1">
        Fecha de Inicio:
      </div>
      <div class="col-6 mb-1">
        <input type="date" name="fecha_inicio" value="<?= date('Y-m-d'); ?>" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Fecha de Termino:
      </div>
      <div class="col-6 mb-1">
        <input type="date" name="fecha_termino" value="<?= date('Y-m-d'); ?>" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Ejecutor:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="id_usuarios_ejecutor">
          <?php
            foreach($usuarios_ejecutores as $ue) {
              print "<option value='".$ue->id."'>".$ue->nombre."</option>";
            }
          ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select name="estado" class="form-control">
            <option>Fermentacion</option>
            <option>Maduracion</option>
            <option>Embarrilado</option>
            <option>Embotellado</option>
        </select>
      </div>
      <div class="col-12 mb-1">
        Observaciones:
      </div>
      <div class="col-12 mb-4">
        <textarea name="observaciones" class="form-control"></textarea>
      </div>
      <div class="col-12 mt-2 mb-5 d-flex justify-content-between">
        <div>
            <button class="btn btn-sm btn-primary" id="agregar-barriles-btn"><i class="fas fa-fw fa-plus"></i> Agregar Barriles</button>
            <button class="btn btn-sm btn-primary" id="agregar-cajas-btn"><i class="fas fa-fw fa-plus"></i> Agregar Botellas</button>
            <button class="btn btn-sm btn-primary" id="traspasar-btn"><i class="fas fa-fw fa-forward"></i> Traspasar Barril a Botellas</button>
            <button class="btn btn-sm btn-primary" id="agregar-insumos-btn"><i class="fas fa-fw fa-plus"></i> Agregar Dryhop</button>
        </div>
        <div>
        </div>
      </div>
      <div class="col-6 mb-1">
        En fermentador:
      </div>
      <div class="col-6 mb-1">
        <div class="input-group">
          <input type="text" class="form-control" id="litros_en_fermentador" disabled>
          <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">L</span>
        </div>
      </div>
      <div class="col-6 mb-1">
        En botellas:
      </div>
      <div class="col-6 mb-1">
        <div class="input-group">
          <input type="text" class="form-control acero" id="litros_en_botellas" disabled>
          <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">L</span>
        </div>
      </div>
      <div class="col-6 mb-1">
        En barriles:
      </div>
      <div class="col-6 mb-1">
        <div class="input-group">
          <input type="text" class="form-control acero" id="litros_en_barriles" disabled>
          <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">L</span>
        </div>
      </div>
      <div class="col-12 mt-1">
        <table class="table table-striped mt-4" id="pedidos-table">
        </table>
      </div>
      <div class="col-12">
        <h4 class="h4">Dryhop</h4>
        <table class="table table-striped mt-4" id="insumos-table">
        </table>
      </div>
      
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <div>
          <a href="./ajax/ajax_generarBatchPDF.php?id=<?= htmlspecialchars($obj->id); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-fw fa-file-pdf"></i> PDF Informe
          </a>
        </div>
        <div>
          <?php
          if($usuario->nivel == "Administrador") {
            ?>
            <button class="btn btn-sm btn-danger eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
            <?php
          }
          ?>
          <button class="btn btn-sm btn-primary" id="guardar-batches-aceptar"><i class="fas fa-fw fa-save"></i> Guardar</button>
        </div>
      </div>
    </div>
  </div>
  </form>
  <br/>
  <br/>

  <div class="modal" tabindex="-1" role="dialog" id="barrilesModal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Barril</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-6 mb-3">
              C&oacute;digo
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="codigo_barril">
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="agregar-barriles-aceptar" data-bs-dismiss="modal">Agregar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" tabindex="-1" role="dialog" id="traspasarModal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Traspasar</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-6 mb-3">
              C&oacute;digo
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="traspasar_codigo_barril">
              </select>
            </div>
          </div>
          <div id="traspasar_alert">
            No hay barriles asignados a este batch para generar un traspaso a botellas.
          </div>
          <div id="traspasar_texto">
            Este traspaso generará <b id="traspasar-cantidad-botellas-generadas"></b> botellas.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="traspasar-aceptar" data-bs-dismiss="modal">Traspasar</button>
        </div>
      </div>
    </div>
  </div>


  <div class="modal" tabindex="-1" role="dialog" id="cajasModal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Caja</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-6 mb-3">
              Cantidad
            </div>
            <div class="col-6 mb-3">
              <input type="number" id="cantidad_botellas" class="form-control" value="0">
            </div>
          </div>
          <div class='alert alert-danger mt-2' id="cantidad-alert"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="agregar-cajas-aceptar" data-bs-dismiss="modal">Agregar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Batch</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este Batch?<br/>Este paso no es reversible, todos los insumos serán repuestos, los barriles devueltos a planta y las cajas eliminadas.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal modal-fade" tabindex="-1" role="dialog" id="agregar-insumos-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Insumo Dryhop</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
          <div class="col-6 mb-1">
            Tipo de Insumo:
          </div>
          <div class="col-6 mb-1">
            <select name="id_tipos_de_insumos" class="form-control">
            </select>
          </div>
          <div class="col-6 mb-1">
            Insumo:
          </div>
          <div class="col-6 mb-1">
            <select name="id_insumos" class="form-control">
            </select>
          </div>
          <div class="col-6 mb-1">
            Cantidad:
          </div>
          <div class="col-6 mb-1">
            <div class="input-group">
              <input type="number" class="form-control acero" name="cantidad" value="0">
              <span class="input-group-text" style="border-radius: 0px 10px 10px 0px" id="agregar-insumos-unidad-de-medida">ml</span>
            </div>
          </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="agregar-insumos-aceptar" data-bs-dismiss="modal">Agregar</button>
        </div>
      </div>
    </div>
  </div>

<script>

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
var recetas = <?= json_encode($recetas,JSON_PRETTY_PRINT); ?>;
var activos = <?= json_encode($activos,JSON_PRETTY_PRINT); ?>;
var productos = <?= json_encode($productos,JSON_PRETTY_PRINT); ?>;
var enplanta_30_l = <?= json_encode($barriles,JSON_PRETTY_PRINT); ?>;
var cantidad_botellas_max = <?= $cantidad_botellas_max; ?>;
var recetas_p = [];
var total_litros = 0;
var total_litros_fermentador = 0;
var total_litros_botellas = 0;
var total_litros_barriles = 0;
var dryhop = [];
var tipos_de_insumos = <?= json_encode($tipos_de_insumos,JSON_PRETTY_PRINT); ?>;
var tipo_de_insumo = {};
var insumos = [];
var lista = <?= json_encode($lista,JSON_PRETTY_PRINT); ?>;
var insumo = {};

$(function() {
  armarRecetasSelect();
  armarActivosSelect();
  renderLista();
  armarTiposDeInsumosSelect();
  changeTiposDeInsumosSelect();
  changeInsumosSelect();
  $.each(obj,function(key,value){
      //(key);
      if(key!="table_name"&&key!="table_fields"){
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
      }
    });
});


$(document).on('change','select[name="clasificacion"]',function(){
    armarRecetasSelect();
});

$(document).on('change','select[name="id_recetas"]',function(){
    armarReceta();
});

function armarRecetasSelect() {

  recetas_p = recetas[$('select[name="clasificacion"]').val()];

  $('select[name="id_recetas"]').empty();
  recetas_p.forEach(function(r) {
    $('select[name="id_recetas"]').append("<option value='" + r.id + "'>" + r.nombre + "</option>");
  });

  armarReceta();

}

function armarActivosSelect() {

    activos.forEach(function(activo) {
    $('select[name="id_activos"]').append("<option value='" + activo.id + "'>" + activo.nombre + "</option>");
    });

}

function armarReceta() {

    $('input[name="litros"]').val('');
    $('input[name="observaciones"]').val('');

    var id_recetas = $('select[name="id_recetas"]').val();
    var receta = recetas_p.find((r) => id_recetas == r.id);
    $('input[name="litros"]').val(receta.litros);
    $('input[name="observaciones"]').val(receta.observaciones);

    total_litros = receta.litros;
    console.log(total_litros);
    renderTable();

}


$(document).on('click','#guardar-batches-aceptar',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("batches");
  data['productos'] = productos;
  data['dryhop'] = lista;

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-batches&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#agregar-barriles-aceptar',function() {
  var barril_agregar = enplanta_30_l.find((b) => b.id == $('#codigo_barril option:selected').val());
  productos.push({
    'tipo': 'Barril',
    'cantidad': barril_agregar.tipo_barril,
    'tipos_cerveza': '',
    'codigo': $('#codigo_barril option:selected').text(),
    'id_barriles': $('#codigo_barril').val()
  });
  renderTable();
});

$(document).on('click','#agregar-cajas-aceptar',function() {
  productos.push({
    'tipo': 'Caja',
    'cantidad': $('#cantidad_botellas').val(),
    'tipos_cerveza': '',
    'codigo': '',
    'id_barriles': ''
  });
  renderTable();
});

function renderTable() {
  var html = '';
  total_litros_fermentador = parseInt(total_litros);
  total_litros_botellas = 0;
  total_litros_barriles = 0;
  productos.forEach(function(producto,index){
    html += '<tr class="productos-tr" data-index="' + index +'">';
    html += '</td><td>#' + (index+1);
    html += '<td>' + producto.tipo;
    html += '</td><td>' + producto.cantidad;
        html += '</td><td>' + producto.codigo;
    html += '</td><td><a href="#" class="item-eliminar-btn" data-index="' + index + '">x</a>';
    html += '</td></tr>';
    if(producto.tipo == "Caja") {
        total_litros_botellas += producto.cantidad * 0.33;
    }
    if(producto.tipo == "Barril") {
      console.log(producto.cantidad);
        if(producto.cantidad == "20L") {
            total_litros_barriles += 20;
        }
        if(producto.cantidad == "30L") {
            total_litros_barriles += 25;
        }
        if(producto.cantidad == "50L") {
            total_litros_barriles += 45;
        }
    }
    
    
  });
  console.log("total litros: " + total_litros);
  console.log("total litros barriles: " + total_litros_barriles);
  console.log("total litros botellas: " + total_litros_botellas);
  total_litros_fermentador = total_litros - (total_litros_barriles + total_litros_botellas);
  $('#pedidos-table').html(html);
  $('#litros_en_fermentador').val(total_litros_fermentador.toFixed(1));
  $('#litros_en_botellas').val(total_litros_botellas.toFixed(1));
  $('#litros_en_barriles').val(total_litros_barriles.toFixed(1));
}

$(document).on('click','.item-eliminar-btn',function(e){
  var index = $(e.currentTarget).data('index');
  productos.splice(index,1);
  renderTable();
});

$(document).on('click','#agregar-barriles-btn',function(e) {
  e.preventDefault();
  $('#barrilesModal').modal('toggle');
  var html = '';
    enplanta_30_l.forEach(function(barril) {
        html += '<option value="' + barril.id + '">' + barril.codigo + ' (' + barril.tipo_barril + ')' + '</option>';
    });
  $('#codigo_barril').html(html);
});

$(document).on('click','#agregar-cajas-btn',function(e) {
  e.preventDefault();
  $('#cajasModal').modal('toggle');
  $('#cantidad-alert').hide();
});

$(document).on('keyup','#cantidad_botellas',function(){
  //console.log($(this).val());
  $(this).val($(this).val().replace(/\D/g,''));
  if($(this).val() == "") {
    $(this).val(0);
  }
  if($(this).val() > cantidad_botellas_max) {
    $(this).val(cantidad_botellas_max);
    $('#cantidad-alert').html("Solo hay " + cantidad_botellas_max + " botellas  disponibles en inventario.");
    $('#cantidad-alert').show();
    $(this).val(parseInt($(this).val()));
    return false;
  }
  
  $('#cantidad-alert').hide();
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
      window.location.href = "./?s=" + response.table_name + "&msg=2";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','#traspasar-btn',function(e) {
  e.preventDefault();
  $('#traspasarModal').modal('toggle');
  var html = '';
  var traspaso = false;
  var cuenta = 0;
  var cantidad_botellas = 0;
  var barril = false;
  $('#traspasar_alert').show();
  $('#traspasar_texto').hide();
  $('#traspasar-aceptar').attr('disabled',true);
  productos.forEach(function(p) {
    if(p.tipo == "Barril") {
      if(cuenta == 0) {
        barril = p;
      }
      html += '<option value="' + p.id_barriles + '">' + p.codigo + ' (' + p.cantidad + ')' + '</option>';
      traspaso = true;
      cuenta++;
    }
  });
  if(traspaso) {
    $('#traspasar_alert').hide();
    $('#traspasar_texto').show();
    $('#traspasar-aceptar').attr('disabled',false);
    if(barril.cantidad == "20L") {
      cantidad_botellas = 60;
    } else 
    if(barril.cantidad == "30L") {
      cantidad_botellas = 75;
    } else 
    if(barril.cantidad == "50L") {
      cantidad_botellas = 135;
    }
  }
  $('#traspasar-cantidad-botellas-generadas').html(cantidad_botellas);
  $('#traspasar_codigo_barril').html(html);

});

$(document).on('change','#traspasar_codigo_barril',function(e) {
  var barril = productos.find( (p) => p.id_barriles == $(e.currentTarget).val());
  if(barril.cantidad == "20L") {
    cantidad_botellas = 60;
  } else 
  if(barril.cantidad == "30L") {
    cantidad_botellas = 75;
  } else 
  if(barril.cantidad == "50L") {
    cantidad_botellas = 135;
  }
  $('#traspasar-cantidad-botellas-generadas').html(cantidad_botellas);
});

$(document).on('click','#traspasar-aceptar',function(e) {
  e.preventDefault();

  var id_barriles = $('#traspasar_codigo_barril').val();
  console.log(id_barriles);

  var slice_index = -1;
  var cantidad_botellas = 0;

  productos.forEach(function(p,index) {
    if(id_barriles == p.id_barriles) {
      slice_index = index;
      if(p.cantidad == '20L') {
        cantidad_botellas = 60;
      } else
      if(p.cantidad == '30L') {
        cantidad_botellas = 75;
      } else
      if(p.cantidad == '50L') {
        cantidad_botellas = 135;
      }
    }
    
  });

  console.log('slice_index: ' + slice_index);

  if(slice_index != -1) {
      productos.splice(slice_index,1);
      productos.push({
        'tipo': 'Caja',
        'cantidad': cantidad_botellas,
        'tipos_cerveza': '',
        'codigo': '',
        'id_barriles': ''
      });
      renderTable();
    } 


});


$(document).on('change','select[name="id_tipos_de_insumos"]',function(){
  changeTiposDeInsumosSelect();
  changeInsumosSelect();
});

function armarTiposDeInsumosSelect() {

  tipos_de_insumos.forEach(function(tdi) {
    $('select[name="id_tipos_de_insumos"]').append("<option value='" + tdi.id + "'>" + tdi.nombre + "</option>");
  });

}

function changeTiposDeInsumosSelect() {

  $('select[name="id_insumos"]').empty();

  var id_tipos_de_insumos = $('select[name="id_tipos_de_insumos"]').val();
  if(id_tipos_de_insumos == null) {
    return false;
  }

  tipo_de_insumo = tipos_de_insumos.find((tdi) => tdi.id == id_tipos_de_insumos);
  insumos = tipo_de_insumo.insumos;
  insumos.forEach(function(insumo) {
    $('select[name="id_insumos"]').append("<option value='" + insumo.id + "'>" + insumo.nombre + "</option>");
  });
  
}

function changeInsumosSelect() {

  var id_insumos = $('select[name="id_insumos"]').val();
  if(id_insumos == null) {
    return false;
  }

  var insumo = insumos.find((i) => i.id == id_insumos);
  $('#agregar-insumos-unidad-de-medida').html(insumo.unidad_de_medida);
  $('input[name="cantidad"]').val('0');
  $('input[name="monto"]').val('0');

}

$(document).on('change','select[name="id_tipos_de_insumos"]',function(){
    changeTiposDeInsumosSelect();
    changeInsumosSelect();
});

$(document).on('change','select[name="id_insumos"]',function(){
    changeInsumosSelect();
});

$(document).on('click','#agregar-insumos-btn',function(e){
  e.preventDefault();
  $('#agregar-insumos-modal').modal('toggle');
});

$(document).on('click','#agregar-insumos-aceptar',function(e){

  e.preventDefault();

  var id_insumos = $('select[name="id_insumos"]').val();
  var insumo = insumos.find((ins) => ins.id = id_insumos);
  insumo.cantidad = $('input[name="cantidad"').val();
  insumo.date = '<?= date('Y-m-d'); ?>';

  lista.push(insumo);
  console.log(lista);
  renderLista();
});

function renderLista() {
  var html = '';
  lista.forEach(function(ins,index){
    html += '<tr class="insumos-tr" data-index="' + index +'"><td><b>' + ins.nombre;
    html += '</b></td><td><b>' + ins.cantidad + " " + ins.unidad_de_medida;
    html += '</b></td><td><b>' + ins.date;
    html += '</b></td><td><b><button class="btn btn-sm item-eliminar-btn" data-index="' + index + '">x</button>';
    html += '</b></td></tr>';
  });
  $('#insumos-table').html(html);
}

$(document).on('click','.item-eliminar-btn',function(e){
  e.preventDefault();
  var index = $(e.currentTarget).data('index');
  lista.splice(index,1);
  renderLista();
});

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-obj-modal').modal('toggle');
})

</script>
