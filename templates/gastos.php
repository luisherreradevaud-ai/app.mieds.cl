<?php


function getSemanas($date) {

    $semanas = array();

    $time = strtotime($date);
    $year = date('Y', $time);
    $month = date('m', $time);

    for($day = 1; $day <= 31; $day++)
    {
        $time = mktime(0, 0, 0, $month, $day, $year);
        if (date('N', $time) == 1)
        {
          $semana = new stdClass;
          $semana->lunes = date('Y-m-d', $time);
          $semana->domingo = date('Y-m-d',strtotime($semana->lunes.' +6 days'));
          $semanas[] = $semana;

        }
    }

    return $semanas;

}



$msg = 0;
if(isset($_GET['msg'])) {
$msg = $_GET['msg'];
}

$mes = date('m');
if(validaIdExists($_GET,'mes')) {
  $mes = $_GET['mes'];
}

$ano = date('Y');
if(validaIdExists($_GET,'ano')) {
  $ano = $_GET['ano'];
}

$modo = "Mensual";
if(isset($_GET['modo'])) {
  if(in_array($_GET['modo'],['Mensual','Semanal'])) {
    $modo = $_GET['modo'];
  }
}

$date = date($ano."-".$mes.'-d');

if(isset($_GET['lunes'])) {
  $date = $_GET['lunes'];
}

if(validaIdExists($_GET,'trimestre')) {
  $trimestre = $_GET['trimestre'];
} else {
  $trimestre = ceil($mes/3);
}

if(validaIdExists($_GET,'semestre')) {
  $semestre = $_GET['semestre'];
} else {
  $semestre = ceil($mes/6);
}

$datetime = new DateTime($date);
$ano = $datetime->format('Y');
$mes = $datetime->format('m');

$esta_semana = new stdClass;

if($datetime->format('N') == 1){
  $esta_semana->lunes = $datetime->format('Y-m-d');
} else {
  $esta_semana->lunes = $datetime->modify('last monday')->format('Y-m-d');
}

$esta_semana->domingo = date('Y-m-d',strtotime($esta_semana->lunes.' +6 days'));


$semanas = getSemanas($date);

if($modo == "Semanal") {
  $start_date = $esta_semana->lunes;
  $end_date = $esta_semana->domingo;
} else 
if($modo == "Mensual") {
  $start_date = $ano."-".$mes."-1";
  $end_date = date($ano."-".$mes."-t");
}



$order_by = "date_vencimiento";
if(isset($_GET['order_by'])) {
  if($_GET['order_by'] == "id") {
    $order_by = "id";
  }
  if($_GET['order_by'] == "item") {
    $order_by = "item";
  }
  if($_GET['order_by'] == "tipo_de_gasto") {
    $order_by = "tipo_de_gasto";
  }
  if($_GET['order_by'] == "fecha") {
    $order_by = "date_vencimiento";
  }
  if($_GET['order_by'] == "monto") {
    $order_by = "monto";
  }
}

$order = "desc";
if(isset($_GET['order'])) {
  if($_GET['order'] == "asc") {
    $order = "asc";
  }
}



$usuario = $GLOBALS['usuario'];
if($usuario->nivel == "Administrador") {
  $gastos_atrasados = Gasto::getAll("WHERE date_vencimiento < NOW() AND date_vencimiento < '".$end_date."' AND estado='Por Pagar' AND aprobado='1'");
  $gastos = Gasto::getAll("WHERE date_vencimiento BETWEEN '".$start_date."' AND '".$end_date."' AND (date_vencimiento >= NOW() OR estado='Pagado') AND aprobado='1' ORDER BY ".$order_by." ".$order);
  $gastos_por_aprobar = Gasto::getAll("WHERE aprobado='0'");
} else {
  $gastos_atrasados = Gasto::getAll("WHERE (tipo_de_gasto='Gas' OR tipo_de_gasto='Caja Chica' OR tipo_de_gasto='Combustible' OR tipo_de_gasto='Envios') AND date_vencimiento < NOW() AND estado='Por Pagar' AND aprobado='1'");
  $gastos = Gasto::getAll("WHERE (tipo_de_gasto='Gas' OR tipo_de_gasto='Caja Chica' OR tipo_de_gasto='Combustible' OR tipo_de_gasto='Envios') AND date_vencimiento BETWEEN '".$start_date."' AND '".$end_date."' AND (date_vencimiento >= NOW() OR estado='Pagado') AND aprobado='1' ORDER BY ".$order_by." ".$order);
    $gastos_por_aprobar = Gasto::getAll("WHERE aprobado='0' and (tipo_de_gasto='Gas' OR tipo_de_gasto='Caja Chica' OR tipo_de_gasto='Combustible' OR tipo_de_gasto='Envios')");
}

$gastos_todos = array_merge($gastos_atrasados,$gastos);



?>
<style>
.tr-gastos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-2">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-handshake"></i> <b>Gastos</b></h1>
  </div>
  <div>
    <a href="./?s=nuevo-gastos" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Gasto</a>
  </div>
</div>
<div class="d-sm-flex justify-content-between mb-3">
  <div class="d-flex justify-content-between">
    <div>
      <select class="form-control vista-select me-1" data-select="modo">
        <option>Semanal</option>
        <option>Mensual</option>
      </select>
    </div>
    <div>
      <select class="form-control me-1 vista-select"  data-select="lunes">
        <?php
        foreach($semanas as $semana) {
          print "<option value='".$semana->lunes."'";
          if($esta_semana->lunes == $semana->lunes) {
            print " SELECTED";
          }
          print ">".date2fecha($semana->lunes)." al ".date2fecha($semana->domingo)."</option>";
        }
        ?>
      </select>
    </div>
    <div>
      <select class="form-control vista-select me-1" data-select="mes">
        <?php
        for($i = 1; $i<=12; $i++) {
          print "<option value='".$i."'>".int2mes($i)."</option>";
        }
        ?>
      </select>
    </div>
    <div>
      <select class="form-control vista-select" data-select="ano">
        <?php
        for($i = 2023; $i<=date('Y'); $i++) {
          print "<option>".$i."</option>";
        }
        ?>
      </select>
    </div>
  </div>
  <div class="mb-3">
    <a href="./?s=gastos&mes=<?= $mes."&ano=".$ano; ?>" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-list"></i> Ver por &Iacute;tem</a>
    <a href="./?s=gastos-por-tipo&mes=<?= $mes."&ano=".$ano; ?>" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2"><i class="fas fa-fw fa-dollar-sign"></i> Ver por Tipo</a>
  </div>
</div>
<?php

  Msg::show(2,'Gasto eliminado.','danger');
  Msg::show(3,'Tipo de Gasto eliminado.','danger');
  Msg::show(6,'Gastos modificados exitosamente.','info');
  Msg::show(7,'Gastos eliminados exitosamente.','danger');

if(count($gastos_por_aprobar)>0) {
?>
<h4 class="h4">Gastos por Aprobar</h1>
<table class="table table-hover table-striped table-sm mt-4" id="gastos-por-aprobar-table">
  <thead class="thead-dark">
    <tr>
      <th>
        &Iacute;tem
      </th>
      <th>
      Tipo de Gasto
      </th>
      <th>
      Fecha
      </th>
      <th>
      Ingresado por
      </th>
      <th>
      Monto
      </th>
      <th>
      Estado
      </th>

    </tr>
  </thead>
  <tbody>
      <?php

      foreach($gastos_por_aprobar as $gasto) {
        $usuario_creador = new Usuario($gasto->id_usuarios);

    ?>
    <tr class="tr-gastos table-warning" data-idgastos="<?= $gasto->id; ?>">
      <td>
        <?= $gasto->item; ?>
      </td>
      <td>
        <?= $gasto->tipo_de_gasto; ?>
      </td>
      <td>
        <?= date2fecha($gasto->date_vencimiento); ?>
      </td>
      <td>
        <?= $usuario_creador->nombre; ?>
      </td>
      <td>
        $<?= number_format($gasto->monto); ?>
      </td>
      <td>
        Por Aprobar
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<br/>
<br/>
<?php
}
if(count($gastos_atrasados)>0) {
   Msg::show('','Tiene '.count($gastos_atrasados).' Gastos atrasados.','danger');

}
?>


<table class="table table-hover table-striped table-sm mt-2" id="gastos-table">
  <thead class="thead-dark">
    <tr>
      <th style="width: 10px">
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="item">&Iacute;tem</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="tipo_de_gasto">Tipo de Gasto</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="fecha">Fecha</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="id_usuarios">Ingresado por</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="por_pagar">Por Vencer</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="vencido">Vencido</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="pagado">Pagado</a>
      </th>
    </tr>
  </thead>
  <tbody>
      <?php

      $total = 0;
      $total_por_pagar = 0;
      $total_pagado = 0;
      $total_vencido = 0;

      foreach($gastos_atrasados as $gasto) {

        $contable = false;

        $fecha_1 = $start_date." 00:00:00";
        $fecha_2 = $end_date." 23:59:59";
        $vencimi = $gasto->date_vencimiento." 00:00:00";
        $hoy =     date('Y-m-d')." 00:00:00";
        $ts_fecha_1 = strtotime($fecha_1);
        $ts_fecha_2 = strtotime($fecha_2);
        $ts_vencimi = strtotime($vencimi);
        $ts_hoy = strtotime($hoy);

        if( $ts_fecha_1 <= $ts_vencimi && $ts_fecha_2 >= $ts_vencimi ) {
          $contable = true;
          $total += $gasto->monto;
        }
        
        $por_pagar = 0;
        $pagado = 0;
        $vencido = $gasto->monto;

        $total_vencido += $gasto->monto;



        $usuario_creador = new Usuario($gasto->id_usuarios);
    ?>
    <tr class="tr-gastos table-danger" data-idgastos="<?= $gasto->id; ?>" style="border: 2px solid red">
      <td style="width: 10px">
        <input class="gastos-checkbox" type="checkbox" data-idgastos="<?= $gasto->id; ?>">
      </td>
      <td>
        <?= $gasto->item; ?>
      </td>
      <td>
        <?= $gasto->tipo_de_gasto; ?>
      </td>
      <td>
        <?= date2fecha($gasto->date_vencimiento); ?>
      </td>
      <td>
        <?= $usuario_creador->nombre; ?>
      </td>
      <td>
        $0
      </td>
      <td>
        $<?= number_format($vencido); ?>
      </td>
      <td>
        $0
      </td>
    </tr>
    <?php
      }
    ?>
    <?php
      
      foreach($gastos as $gasto) {

        $contable = false;

        $fecha_1 = $start_date." 00:00:00";
        $fecha_2 = $end_date." 23:59:59";
        $vencimi = $gasto->date_vencimiento." 00:00:00";
        $ts_fecha_1 = strtotime($fecha_1);
        $ts_fecha_2 = strtotime($fecha_2);
        $ts_vencimi = strtotime($vencimi);

        if( $ts_fecha_1 <= $ts_vencimi && $ts_fecha_2 >= $ts_vencimi ) {
          $contable = true;
          $total += $gasto->monto;
        }
        
        $por_pagar = 0;
        $pagado = 0;
        $estado = $gasto->estado;
        if($gasto->estado == "Por Pagar") {
          $por_pagar = $gasto->monto;
          if($contable) {
            $total_por_pagar += $gasto->monto;
          }
          if($ts_vencimi < $ts_hoy) {
            $estado = "Vencido";
          } else {
            $estado = "Por Vencer";
          }
        }
        if($gasto->estado == "Pagado") {
          $pagado = $gasto->monto;
          if($contable) {
            $total_pagado += $gasto->monto;
          }
        }
        
        $usuario_creador = new Usuario($gasto->id_usuarios);
    ?>
    <tr class="tr-gastos" data-idgastos="<?= $gasto->id; ?>">
      <td style="width: 10px">
        <input class="gastos-checkbox" type="checkbox" data-idgastos="<?= $gasto->id; ?>">
      </td>
      <td>
        <?= $gasto->item; ?>
      </td>
      <td>
        <?= $gasto->tipo_de_gasto; ?>
      </td>
      <td>
        <?= date2fecha($gasto->date_vencimiento); ?>
      </td>
      <td>
        <?= $usuario_creador->nombre; ?>
      </td>
      <td>
        $<?= number_format($por_pagar); ?>
      </td>
      <td>
        $0
      </td>
      <td>
        $<?= number_format($pagado); ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
  <tfooter>
    <tr style="background-color: white; border: 1px solid black">
      <td colspan="4">
       Monto seleccionado: $<span id="gastos_checkbox_total_monto">0</span>
      </td>
      <td>
        Total:
    </td>
    <td><b>$<?= number_format($total_por_pagar); ?></td>
    <td><b>$<?= number_format($total_vencido); ?></td>
    <td><b>$<?= number_format($total_pagado); ?></td>
    </tr>
  </tfooter>
</table>
<?php
if($usuario->nivel == "Administrador") {
?>
<div class="mt-3 mb-3" style="font-size: 0.8em">
  Selecci&oacute;n (<span id="gastos_checkbox_total">0</span>): <a class="btn btn-sm btn-secondary accion-masiva" href="#gastos_checkbox_total" data-estado="Pagado" data-accion="marcar-como">Marcar como Pagado</a> <a class="btn btn-sm btn-danger accion-masiva" href="#gastos_checkbox_total" data-accion="eliminar">Eliminar</a>
</div>
<?php
}
?>
<div class="modal fade" id="accion-masiva-eliminar-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-fw fa-trash"></i> Eliminar</h5>
                <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
            <div class="row">
              <div class="col-12">
                Desea eliminar estos elementos (<span id="accion-masiva-eliminar-modal-total"></span>)? Esta acci&oacute;n no es reversible.
              </div>
            </div>
              
            </div>
            <div class="modal-footer">
                <button class="btn btn-default btn-sm" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger btn-sm" id="accion-masiva-eliminar-btn" data-accion="eliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>
<script>
var mes = '<?= intval($mes); ?>';
var ano = '<?= intval($ano); ?>';
var modo = '<?= $modo; ?>';
var lunes = '<?= $esta_semana->lunes; ?>';
var order_by = '<?= $order_by; ?>';
var order = '<?= $order; ?>';
var change_checkbox = [];
var gastos = <?= json_encode($gastos_todos,JSON_PRETTY_PRINT); ?>;

new DataTable('#gastos-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    paging: false
});

new DataTable('#gastos-por-aprobar-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    paging: false
});

$(document).ready(function(){

  $('.vista-select[data-select="mes"]').val(mes);
  $('.vista-select[data-select="ano"]').val(ano);
  $('.vista-select[data-select="modo"]').val(modo);
  $('.vista-select[data-select="lunes"]').val(lunes);
  console.log(lunes);

  $('.vista-select').hide();
  $('.vista-select[data-select="modo"]').show();

  if(modo == "Semanal") {
    $('.vista-select[data-select="lunes"]').show();
    $('.vista-select[data-select="mes"]').show();
    $('.vista-select[data-select="ano"]').show();
  }
  if(modo == "Mensual") {
    $('.vista-select[data-select="mes"]').show();
    $('.vista-select[data-select="ano"]').show();
  }

});

$(document).on('change','.date-select', function(e) {
  window.location.href = "./?s=gastos&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();
});


$(document).on('click','.tr-gastos',function(e) {
    window.location.href = "./?s=detalle-gastos&id=" + $(e.currentTarget).data('idgastos');
});

$(document).on('click','.sort',function(e){
  if(order == "asc") {
    order = "desc";
  } else {
    order = "asc";
  }
  window.location.href = "./?s=gastos&mes=" + mes + "&ano=" + ano + "&order_by=" + $(e.currentTarget).data('sortorderby') + "&order=" + order;
});

$(document).on('click','.gastos-checkbox',function(e){

  e.stopPropagation();

  change_checkbox = [];
  total = 0;
  total_monto = 0;

  $('.gastos-checkbox').each(function(){
    if($(this).is(':checked')){
      total += 1;
      change_checkbox.push($(this).data('idgastos'));
      var gasto_p = gastos.find((g) => g.id == $(this).data('idgastos'));
      total_monto += parseInt(gasto_p.monto);
      console.log(gasto_p);
    }
  })
  $('#gastos_checkbox_total').html(total);
  $('#gastos_checkbox_total_monto').html(total_monto.toLocaleString('en-US'));
  $('#accion-masiva-eliminar-modal-total').html(total);
  

});

$(document).on('click','.accion-masiva',function(e){

  if(change_checkbox.length == 0) {
    return 0;
  }

  if($(e.currentTarget).data('accion') == "marcar-como") {

    var url = "./ajax/ajax_cambiarEstadoGastos.php";
    var data = {
      'table_name': 'gastos',
      'ids_gastos': change_checkbox,
      'estado': $(e.currentTarget).data('estado')
    };

    console.log(data);

    $.post(url,data,function(response_raw){
      console.log(response_raw);
      var response = JSON.parse(response_raw);
      if(response.mensaje!="OK") {
        alert("Algo fallo");
        return false;
      } else {
        window.location.href = "./?s=gastos&msg=6";
      }
    }).fail(function(){
      alert("No funciono");
    });
  } else
  if($(e.currentTarget).data('accion') == "eliminar") {
    $('#accion-masiva-eliminar-modal').modal('toggle');
  }

});



$(document).on('click','#accion-masiva-eliminar-btn',function(e){

  if(change_checkbox.length == 0) {
    return 0;
  }

  var url = "./ajax/ajax_accionMasiva.php";
  var data = {
    'table_name': 'gastos',
    'ids': change_checkbox,
    'accion': $(e.currentTarget).data('accion')
  };

  console.log(data);

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=gastos&msg=7";
    }
  }).fail(function(){
    alert("No funciono");
  });


});

$(document).on('change','.vista-select', function(e) {

  var select = $(e.currentTarget).data('select');
  var value = $(e.currentTarget).val();

  if(select == 'lunes') {
    lunes = value;
  } else
  if(select == 'mes') {
    mes = value;
  } else
  if(select == 'ano') {
    ano = value;
  } else
  if(select == 'trimestre') {
    trimestre = value;
  } else
  if(select == 'semestre') {
    semestre = value;
  } else
  if(select == 'modo') {
    modo = value;
  }

  cambiarVista();

});

function cambiarVista() {

  if(modo == 'Semanal') {
    window.location.href = "./?s=gastos&lunes=" + lunes + "&mes=" + mes + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Mensual') {
    window.location.href = "./?s=gastos&mes=" + mes + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Trimestral') {
    window.location.href = "./?s=gastos&trimestre=" + trimestre + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Semestral') {
    window.location.href = "./?s=gastos&semestre=" + semestre + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Historico') {
    window.location.href = "./?s=gastos&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }
  if(modo == 'Anual') {
    window.location.href = "./?s=gastos&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order;
  }

}

  


</script>