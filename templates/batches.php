<?php

    $mes = date('m');
    if(validaIdExists($_GET,'mes')) {
    $mes = $_GET['mes'];
    }

    $ano = date('Y');
    if(validaIdExists($_GET,'ano')) {
    $ano = $_GET['ano'];
    }

    $batches = Batch::getAll();
    //$batches = Batch::getAll();

?>
<style>
.tr-batches {
  cursor: pointer;
}
</style>
<div class="mb-4">
  <h1 class="h2 mb-0 text-gray-800"><b>Batches en Proceso</b></h1>
</div>
<?php Widget::printWidget("batches-menu"); ?>
<?php 
Msg::show(2,'Batch eliminado con &eacute;xito','danger');
?>
<table class="table table-hover table-striped table-sm" id="batches-table">
  <thead class="thead-dark">
    <tr>
      <th class="text-left">
        Número de Batch
      </th>
      <th>
        Receta
      </th>
      <th>
        Etapa
      </th>
      <th>
        Creado
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($batches as $batch) {
        $receta = new Receta($batch->id_recetas);
    ?>
    <tr class="tr-batches" data-idbatches="<?= $batch->id; ?>">
      <td class="text-left">
        <?= $batch->batch_nombre; ?>
      </td>
      <td>
        <?= $receta->nombre; ?>
      </td>
      <td>
        <?= ucfirst($batch->etapa_seleccionada); ?>
      </td>
      <td>
        <?= datetime2fechayhora($batch->creada); ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<div class="modal modal-fade" tabindex="-1" role="dialog" id="agregar-lupulizacion-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Agregar Lupulización
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-1">
                        Batch:
                    </div>
                    <div class="col-6 mb-1">
                        <select name="id_batches" id="agregar-lupulizacion-id_batches-select" class="form-control">
                          <?php
                            foreach($batches as $batch) {
                              ?>
                              <option value="<?= $batch->id; ?>">#<?= $batch->id; ?></option>
                              <?php
                            }
                          ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="agregar-lupulizacion-aceptar-btn" data-bs-dismiss="modal">Agregar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-fade" tabindex="-1" role="dialog" id="agregar-traspasos-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Agregar Traspasos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-1">
                        Batch:
                    </div>
                    <div class="col-6 mb-1">
                        <select name="id_batches" id="agregar-traspasos-id_batches-select" class="form-control">
                          <?php
                            foreach($batches as $batch) {
                              ?>
                              <option value="<?= $batch->id; ?>">#<?= $batch->id; ?></option>
                              <?php
                            }
                          ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="agregar-traspasos-aceptar-btn" data-bs-dismiss="modal">Agregar</button>
            </div>
        </div>
    </div>
</div>

<script>

new DataTable('#batches-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true
});

$(document).on('click','.tr-batches',function(e) {
    window.location.href = "./?s=nuevo-batches&id=" + $(e.currentTarget).data('idbatches');
});

$(document).on('click','#agregar-lupulizacion-aceptar-btn',function(e) {
    window.location.href = "./?s=agregar-lupulizacion&id=" + $('#agregar-lupulizacion-id_batches-select').val();
});

$(document).on('click','#agregar-traspasos-aceptar-btn',function(e) {
    window.location.href = "./?s=agregar-traspasos&id=" + $('#agregar-traspasos-id_batches-select').val();
});

</script>
