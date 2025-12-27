<?php

  $msg = 0;
  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  // Get filter parameters
  $filter_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
  $filter_fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
  $filter_fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');

  // Build query conditions
  $conditions = "WHERE estado != 'eliminado'";
  if($filter_estado) {
    $conditions .= " AND estado='".$filter_estado."'";
  }
  if($filter_fecha_inicio && $filter_fecha_fin) {
    $conditions .= " AND fecha >= '".$filter_fecha_inicio."' AND fecha <= '".$filter_fecha_fin."'";
  }
  $conditions .= " ORDER BY fecha DESC, hora_inicio DESC";

  $turnos = Turno::getAll($conditions);

  // Get all atendedores for filter dropdown
  $atendedores = Atendedor::getAll("ORDER BY nombre_completo ASC");

  // Calculate summary totals
  $summary = array(
    'total_efectivo' => 0,
    'total_faltantes' => 0,
    'total_anticipos' => 0,
    'abiertos' => 0,
    'cerrados' => 0,
    'aprobados' => 0
  );
  foreach($turnos as $t) {
    $summary['total_efectivo'] += $t->total_efectivo;
    $summary['total_faltantes'] += $t->total_faltantes;
    $summary['total_anticipos'] += $t->total_anticipos;
    if($t->estado == 'Abierto') $summary['abiertos']++;
    if($t->estado == 'Cerrado') $summary['cerrados']++;
    if($t->estado == 'Aprobado') $summary['aprobados']++;
  }

?>
<style>
.tr-turnos {
  cursor: pointer;
}
.summary-card {
  border-left: 4px solid;
}
.summary-card.efectivo { border-color: #28a745; }
.summary-card.faltantes { border-color: #dc3545; }
.summary-card.anticipos { border-color: #ffc107; }
.summary-card.estados { border-color: #17a2b8; }
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-clock"></i> <b>Turnos</b></h1>
  </div>
  <div>
    <a href="./?s=detalle-turnos" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2"><i class="fas fa-fw fa-plus"></i> Nuevo Turno</a>
  </div>
</div>
<hr />

<?php
  Msg::show(1,'Turno guardado con &eacute;xito','info');
  Msg::show(2,'Turno eliminado','danger');
  Msg::show(3,'Turno cerrado con &eacute;xito','success');
  Msg::show(4,'Turno aprobado con &eacute;xito','success');
  Msg::show(5,'Turno reabierto','warning');
?>

<!-- Summary Cards -->
<div class="row mb-4">
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="card shadow-sm summary-card efectivo">
      <div class="card-body py-3">
        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Efectivo</div>
        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= Turno::formatMoney($summary['total_efectivo']); ?></div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="card shadow-sm summary-card faltantes">
      <div class="card-body py-3">
        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Faltantes</div>
        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= Turno::formatMoney($summary['total_faltantes']); ?></div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="card shadow-sm summary-card anticipos">
      <div class="card-body py-3">
        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Anticipos</div>
        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= Turno::formatMoney($summary['total_anticipos']); ?></div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="card shadow-sm summary-card estados">
      <div class="card-body py-3">
        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Estados</div>
        <div class="small">
          <span class="badge bg-warning text-dark"><?= $summary['abiertos']; ?> Abiertos</span>
          <span class="badge bg-info"><?= $summary['cerrados']; ?> Cerrados</span>
          <span class="badge bg-success"><?= $summary['aprobados']; ?> Aprobados</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
  <div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-center" id="filter-form">
      <input type="hidden" name="s" value="turnos">
      <div class="col-auto">
        <label class="visually-hidden">Desde</label>
        <input type="date" class="form-control form-control-sm" name="fecha_inicio" value="<?= $filter_fecha_inicio; ?>">
      </div>
      <div class="col-auto">
        <label class="visually-hidden">Hasta</label>
        <input type="date" class="form-control form-control-sm" name="fecha_fin" value="<?= $filter_fecha_fin; ?>">
      </div>
      <div class="col-auto">
        <select class="form-control form-control-sm" name="estado">
          <option value="">Todos los estados</option>
          <option value="Abierto" <?= ($filter_estado == 'Abierto') ? 'selected' : ''; ?>>Abierto</option>
          <option value="Cerrado" <?= ($filter_estado == 'Cerrado') ? 'selected' : ''; ?>>Cerrado</option>
          <option value="Aprobado" <?= ($filter_estado == 'Aprobado') ? 'selected' : ''; ?>>Aprobado</option>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
        <a href="./?s=turnos" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i> Limpiar</a>
      </div>
    </form>
  </div>
</div>

<!-- Turnos Table -->
<div class="card shadow-sm">
  <div class="card-body">
    <table class="table table-hover table-striped table-sm" id="turnos-table">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Horario</th>
          <th>Atendedor</th>
          <th class="text-end">Total Efectivo</th>
          <th class="text-end">Faltantes</th>
          <th class="text-end">Anticipos</th>
          <th class="text-center">Estado</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($turnos as $turno) {
          $atendedor = $turno->getAtendedor();
          $atendedor_nombre = $atendedor ? $atendedor->nombre_completo : '(Sin asignar)';
        ?>
        <tr class="tr-turnos" data-idturnos="<?= $turno->id; ?>">
          <td><?= $turno->getFormattedDate(); ?></td>
          <td><?= $turno->getTimeRange(); ?></td>
          <td><?= htmlspecialchars($atendedor_nombre); ?></td>
          <td class="text-end"><?= Turno::formatMoney($turno->total_efectivo); ?></td>
          <td class="text-end <?= ($turno->total_faltantes > 0) ? 'text-danger' : ''; ?>">
            <?= Turno::formatMoney($turno->total_faltantes); ?>
          </td>
          <td class="text-end <?= ($turno->total_anticipos > 0) ? 'text-warning' : ''; ?>">
            <?= Turno::formatMoney($turno->total_anticipos); ?>
          </td>
          <td class="text-center">
            <span class="badge <?= $turno->getStateBadgeClass(); ?>">
              <i class="fas <?= $turno->getStateIcon(); ?>"></i> <?= $turno->estado; ?>
            </span>
          </td>
          <td class="text-center">
            <div class="btn-group btn-group-sm" role="group">
              <a href="./?s=detalle-turnos&id=<?= $turno->id; ?>" class="btn btn-outline-primary btn-sm" title="Ver detalle">
                <i class="fas fa-eye"></i>
              </a>
              <?php if($turno->canClose()) { ?>
              <button class="btn btn-outline-info btn-sm btn-cerrar-turno" data-id="<?= $turno->id; ?>" title="Cerrar turno">
                <i class="fas fa-lock"></i>
              </button>
              <?php } ?>
              <?php if($turno->canApprove()) { ?>
              <button class="btn btn-outline-success btn-sm btn-aprobar-turno" data-id="<?= $turno->id; ?>" title="Aprobar turno">
                <i class="fas fa-check"></i>
              </button>
              <?php } ?>
            </div>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Cerrar Turno -->
<div class="modal fade" id="cerrar-turno-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-lock"></i> Cerrar Turno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Esta accion cerrara el turno y no se podran agregar mas registros.</p>
        <div class="mb-3">
          <label class="form-label">Observaciones de cierre (opcional):</label>
          <textarea class="form-control" id="cerrar-observaciones" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-info" id="confirmar-cerrar-btn"><i class="fas fa-lock"></i> Cerrar Turno</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Aprobar Turno -->
<div class="modal fade" id="aprobar-turno-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-check-circle"></i> Aprobar Turno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Una vez aprobado, el turno quedara marcado como finalizado.</p>
        <div class="mb-3">
          <label class="form-label">Observaciones de aprobacion (opcional):</label>
          <textarea class="form-control" id="aprobar-observaciones" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="confirmar-aprobar-btn"><i class="fas fa-check"></i> Aprobar Turno</button>
      </div>
    </div>
  </div>
</div>

<script>

var turnoIdToProcess = null;

new DataTable('#turnos-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    order: [[0, 'desc'], [1, 'desc']]
});

// Click on row to view detail
$(document).on('click','.tr-turnos td:not(:last-child)',function(e){
  var id = $(e.currentTarget).parent().data('idturnos');
  window.location.href = "./?s=detalle-turnos&id=" + id;
});

// Cerrar Turno
$(document).on('click','.btn-cerrar-turno',function(e){
  e.preventDefault();
  e.stopPropagation();
  turnoIdToProcess = $(this).data('id');
  $('#cerrar-observaciones').val('');
  $('#cerrar-turno-modal').modal('show');
});

$('#confirmar-cerrar-btn').click(function(){
  if(!turnoIdToProcess) return;

  $.ajax({
    url: './ajax/ajax_cerrarTurno.php',
    method: 'POST',
    data: {
      id: turnoIdToProcess,
      observaciones: $('#cerrar-observaciones').val()
    },
    success: function(response) {
      if(response.status == 'OK') {
        window.location.href = './?s=turnos&msg=3';
      } else {
        alert(response.mensaje || 'Error al cerrar turno');
      }
    },
    error: function() {
      alert('Error de conexion');
    }
  });
});

// Aprobar Turno
$(document).on('click','.btn-aprobar-turno',function(e){
  e.preventDefault();
  e.stopPropagation();
  turnoIdToProcess = $(this).data('id');
  $('#aprobar-observaciones').val('');
  $('#aprobar-turno-modal').modal('show');
});

$('#confirmar-aprobar-btn').click(function(){
  if(!turnoIdToProcess) return;

  $.ajax({
    url: './ajax/ajax_aprobarTurno.php',
    method: 'POST',
    data: {
      id: turnoIdToProcess,
      observaciones: $('#aprobar-observaciones').val()
    },
    success: function(response) {
      if(response.status == 'OK') {
        window.location.href = './?s=turnos&msg=4';
      } else {
        alert(response.mensaje || 'Error al aprobar turno');
      }
    },
    error: function() {
      alert('Error de conexion');
    }
  });
});

</script>
