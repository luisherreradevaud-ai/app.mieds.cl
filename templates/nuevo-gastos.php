<?php

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
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Nuevo Gasto
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
  Msg::show(1,'Gasto ingresado con &eacute;xito','info');
?>
<form id="gastos-form" action="./php/procesar.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="gastos">
  <input type="hidden" name="modo" value="nuevo-entidad-con-media">
  <input type="hidden" name="id_usuarios" value="<?= $GLOBALS['usuario']->id; ?>">
  <input type="hidden" name="redirect" value="detalle-gastos">
    <?php
    if($usuario->nivel == "Administrador") {
      ?>
      <input type="hidden" name="aprobado" value="1">
      <?php
    } else {
      ?>
      <input type="hidden" name="aprobado" value="0">
      <?php
    }
  ?>
  <div class="row">
    <div class="col-md-6 row">
    <div class="col-6 mb-1">
        Fecha:
      </div>
      <div class="col-6 mb-1">
        <input type="date" value="<?= date('Y-m-d'); ?>" class="form-control" name="creada">
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
      </div>
      <div class="col-6 mb-1" style="text-align: right">
        <a href="./?s=detalle-tipos_de_gastos" style="font-size: 0.8em"><i class="fas fa-fw fa-plus"></i> Crear nuevo Tipo de Gasto</a>
      </div>
      <div class="col-6 mb-1">
        &Iacute;tem:
      </div>
      <div class="col-6 mb-1">
        <input type="text" name="item" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Monto:
      </div>
      <div class="col-6 mb-1">
        <div class="input-group">
          <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
          <input type="text" class="form-control acero" name="monto" value="0">
        </div>
      </div>
      <?php
        if($usuario->nivel=="Administrador") {
      ?>
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
        <input type="date" name="date_vencimiento" class="form-control" value="<?= date('Y-m-d'); ?>">
      </div>
      <div class="col-6 mb-1 date_vencimiento">
        Repetir:
      </div>
      <div class="col-6 mb-1 date_vencimiento">
        <select name="repetir" class="form-control">
          <option>No</option>
          <option>Cada semana</option>
          <option>Cada mes</option>
        </select>
      </div>
        <div class="col-6 repetir">
          <div class="date_vencimiento mb-1">
            Hasta:
          </div>
        </div>
        <div class="col-6 repetir">
          <div class="date_vencimiento mb-1">
            <input type="date" name="hasta" class="form-control">
          </div>
        </div>
      <?php
      } else 
      {
      ?>
      <input type="hidden" name="estado" value="Por Pagar">
      <input type="hidden" name="repetir" value="No">
      <input type="hidden" name="hasta" value="<?= date('Y-m-d H:i:s'); ?>">
      <div class="col-6 mb-1">
        Vencimiento:
      </div>
      <div class="col-6 mb-1">
        <input type="date" name="date_vencimiento" class="form-control" value="<?= date('Y-m-d'); ?>">
      </div>
      <?php
      }
      ?>
      <div class="col-12 mb-1">
        Imagen:
      </div>
      <div class="col-12 mb-1">
        <input type="file" name="file" class="form-control">
      </div>
      <div class="col-12 mb-1">
        Comentarios:
      </div>
      <div class="col-12 mb-1">
        <textarea name="comentarios" class="form-control"></textarea>
      </div>

      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        &nbsp;
        <div>
          <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
          <button class="btn btn-sm btn-primary" id="guardar-btn-2"><i class="fas fa-fw fa-save"></i> Guardar y crear Nuevo Gasto</button>
        </div>
      </div>
    </div>
  </div>
</form>

<script>

$(document).ready(function(){
  $('.date_vencimiento').hide();
  $('.repetir').hide();
});

$(document).on('change','select[name="estado"]',function(e) {
  if($(e.currentTarget).val() == 'Por Pagar') {
    $('.date_vencimiento').show(200);
  } else {
    $('.date_vencimiento').hide(200);
  }
})


$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="item"]').val() == "") {
    alert("Debe ingresar el Item");
    return false;
  }

  if($('input[name="monto"]').val() == "") {
    $('input[name="monto"]').val(0);
  }

  if($('input[name="monto"]').val() < 1) {
    alert("El monto debe ser mayor a 0.");
    return false;
  }

  $('#gastos-form').submit();

});

$(document).on('click','#guardar-btn-2',function(e){

  e.preventDefault();

  if($('input[name="item"]').val() == "") {
    alert("Debe ingresar el Item");
    return false;
  }

  if($('input[name="monto"]').val() == "") {
    $('input[name="monto"]').val(0);
  }

  if($('input[name="monto"]').val() < 1) {
    alert("El monto debe ser mayor a 0.");
    return false;
  }

  $('input[name="redirect"]').val('nuevo-gastos');
  $('#gastos-form').submit();

});

$(document).on('keyup','.acero',function(){
  $(this).val($(this).val().replace(/\D/g,''));
  if($(this).val() == "") {
    $(this).val(0);
  }
  $(this).val(parseInt($(this).val()));
});

$(document).on('change','select[name="repetir"]',function(e) {
  if($(e.currentTarget).val() != "No") {
    $('.repetir').show(200);
  } else {
    $('.repetir').hide(200);
  }
})

</script>
