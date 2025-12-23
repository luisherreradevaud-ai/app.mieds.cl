<?php

  //checkAutorizacion(["Jefe de Planta","Administrador","Repartidor"]);

  $barriles_obj = new Barril;
  $clientes_barriles = $barriles_obj->getClientesBarriles();
  $barriles_en_planta = Barril::getAll("WHERE clasificacion!='CO2' AND estado='En planta'");
  $barriles_en_despacho = Barril::getAll("WHERE clasificacion!='CO2' AND estado='En despacho'");
  $barriles_perdidos = Barril::getAll("WHERE clasificacion!='CO2' AND estado='Perdido'");
  $usuario = $GLOBALS['usuario'];

?>
<style>
.tr-barriles {
  cursor: pointer;
}
.barriles-table-container {
  max-height: 300px;
  overflow: scroll;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h2 mb-0 text-gray-800"><b>Barriles</b></h1>
  </div>
  <div>
    <a href="./?s=devolucion-barriles" class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mb-2">Devolver Barril</a>
    <?php
    if($usuario->nivel!= "Repartidor") {
    ?>
    <a href="./?s=detalle-barriles" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Barril</a>
    <?php
    }
    ?>
  </div>
</div>
<?php 
  Msg::show(2,'Barril eliminado con &eacute;xito','danger');
?>
<div class="row mb-5">

  <div class="col-12 mb-3">
    <h3>En Cervecería Cocholgüe</32>
  </div>



  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <div class="d-sm-flex align-items-center justify-content-between mb-3">
          <div class="mb-2">
            <h1 class="h4 mb-0 text-gray-800"><b>En Planta</b> (<?= count($barriles_en_planta); ?>)</h1>
          </div>
        </div>
        <div class="barriles-table-container">
        <table class="table table-hover table-striped table-sm mb-5">
          <thead class="thead-dark">
            <tr>
              <th>
                  C&oacute;digo
              </th>
              <th>
                  Tipo
              </th>
            </tr>
          </thead>
          <tbody>
            <?php
              foreach($barriles_en_planta as $barril) {
            ?>
            <tr class="tr-barriles" data-idbarriles="<?= $barril->id; ?>">
              <td>
                <?= $barril->codigo; ?>
              </td>
              <td>
                <?= $barril->tipo_barril; ?>
              </td>
            </tr>
            <?php
              }
            ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <div class="d-sm-flex align-items-center justify-content-between mb-3">
          <div class="mb-2">
            <h1 class="h4 mb-0 text-gray-800"><b>En Despacho</b> (<?= count($barriles_en_despacho); ?>)</h1>
          </div>
        </div>
        <div class="barriles-table-container">
        <table class="table table-hover table-striped table-sm mb-5">
          <thead class="thead-dark">
            <tr>
              <th>
                  C&oacute;digo
              </th>
              <th>
                  Tipo
              </th>
            </tr>
          </thead>
          <tbody>
            <?php
              foreach($barriles_en_despacho as $barril) {
            ?>
            <tr class="tr-barriles" data-idbarriles="<?= $barril->id; ?>">
              <td>
                <?= $barril->codigo; ?>
              </td>
              <td>
                <?= $barril->tipo_barril; ?>
              </td>
            </tr>
            <?php
              }
            ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <div class="d-sm-flex align-items-center justify-content-between mb-3">
          <div class="mb-2">
            <h1 class="h4 mb-0 text-gray-800"><b>Perdidos</b> (<?= count($barriles_perdidos); ?>)</h1>
          </div>
        </div>
        <div class="barriles-table-container">
        <table class="table table-hover table-striped table-sm mb-2">
          <thead class="thead-dark">
            <tr>
              <th>
                  C&oacute;digo
              </th>
              <th>
                  Tipo
              </th>
              <th>
                  Ubicación
              </th>
            </tr>
          </thead>
          <tbody>
            <?php
              foreach($barriles_perdidos as $barril) {
                if($barril->id_clientes != 0) {
                  $cliente_perdido = new Cliente($barril->id_clientes);
                  $cliente_perdido_nombre = $cliente_perdido->nombre;
                } else {
                  $entregas_productos = EntregaProducto::getAll("WHERE id_barriles='".$barril->id."' ORDER BY id desc LIMIT 1");
                  if(count($entregas_productos) > 0) {
                    $entrega = new Entrega($entregas_productos[0]->id_entregas);
                    $cliente_perdido = new Cliente($entrega->id_clientes);
                    $cliente_perdido_nombre = $cliente_perdido->nombre;
                  } else {
                    $cliente_perdido_nombre = '-';
                  }
                }
                
            ?>
            <tr class="tr-barriles" data-idbarriles="<?= $barril->id; ?>">
              <td>
                <?= $barril->codigo; ?>
              </td>
              <td>
                <?= $barril->tipo_barril; ?>
              </td>
              <td>
                <?= $cliente_perdido_nombre; ?>
              </td>
            </tr>
            <?php
              }
            ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 mt-3 mb-4">
    <hr/>
    <h3>En terreno</h3>
  </div>

  <?php
    foreach($clientes_barriles as $cb) {
      $cliente = $cb['obj'];
      $barriles = $cb['barriles'];
  ?>
  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-sm-flex align-items-center justify-content-between mb-3">
          <div class="mb-2">
            <h1 class="h4 mb-0 text-gray-800"><b><?= $cliente->nombre; ?></b> (<?= count($barriles); ?>)</h1>
          </div>
        </div>
        <div class="barriles-table-container">
        <table class="table table-hover table-striped table-sm mb-1">
          <thead class="thead-dark">
            <tr>
              <th>
                C&oacute;digo
              </th>
              <th>
                Tipo
              </th>
              <th>
                Fecha Entrega
              </th>
              <th>
                Batch
              </th>
              <?php
              if($usuario->nivel == 'Administrador') {
              ?>
              <th>
                Estado
              </th>
              <?php
              }
              ?>
            </tr>
          </thead>
          <tbody>
            <?php
              foreach($barriles as $barril) {
                $ep = EntregaProducto::getAll("WHERE id_barriles='".$barril->id."' ORDER BY id desc LIMIT 1");
                if(count($ep)>0) {
                  $entrega = new Entrega($ep[0]->id_entregas);
                  $datetime_entrega = datetime2fecha($entrega->creada);
                } else {
                  $datetime_entrega = "-";
                }
                
            ?>
            <tr class="tr-barriles" data-idbarriles="<?= $barril->id; ?>">
              <td>
                <?= $barril->codigo; ?>
              </td>
              <td>
                <?= $barril->tipo_barril; ?>
              </td>
              <td>
                <?= $datetime_entrega; ?>
              </td>
              <td>
                <?= ($barril->id_batches != 0) ? "#".$barril->id_batches : "-"; ?>
              </td>
              <?php
              if($usuario->nivel == 'Administrador') {
              ?>
              <td>
                <?= $barril->estado; ?>
              </td>
              <?php
              }
              ?>
            </tr>
            <?php
              }
            ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>
  <?php
    }
  ?>




</div>
<script>
var usuario = <?= json_encode($usuario,JSON_PRETTY_PRINT); ?>;

$(document).on('click','.tr-barriles',function(e){
  if(usuario.nivel == "Repartidor") {
    return false;
  }
  window.location.href = "./?s=detalle-barriles&id=" + $(e.currentTarget).data('idbarriles');
});
</script>