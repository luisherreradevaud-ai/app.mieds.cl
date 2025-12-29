<?php

$id = "";
if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
}

$msg = 0;
if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}

$obj = new Turno($id);
$usuario = $GLOBALS['usuario'];

// Get related data
$atendedores = Atendedor::getAll("WHERE estado='Activo' ORDER BY nombre_completo ASC");
$faltantes = $obj->id ? $obj->getFaltantes() : array();
$anticipos = $obj->id ? $obj->getAnticipos() : array();
$facturas_credito = $obj->id ? $obj->getFacturasCredito() : array();
$ingresos_prosegur = $obj->id ? $obj->getIngresosProsegur() : array();
$gastos_caja_chica = $obj->id ? $obj->getGastosCajaChica() : array();

$isEditable = $obj->id == "" || $obj->canEdit();

?>
<style>
.cash-input { max-width: 80px; text-align: center; }
.cash-total { font-weight: bold; }
.section-card { margin-bottom: 1rem; }
.item-row:hover { background-color: #f8f9fc; }
.readonly-section { opacity: 0.7; }
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        <?php
        if($obj->id=="") {
          print '<i class="fas fa-fw fa-plus"></i> Nuevo';
        } else {
          print '<i class="fas fa-fw fa-clock"></i> Detalle';
        }
        ?> Turno
        <?php if($obj->id != "") { ?>
          <span class="badge <?= $obj->getStateBadgeClass(); ?>">
            <i class="fas <?= $obj->getStateIcon(); ?>"></i> <?= $obj->estado; ?>
          </span>
        <?php } ?>
      </b>
    </h1>
  </div>
  <div>
    <?php $usuario->printReturnBtn(); ?>
    <a href="./?s=turnos" class="btn btn-outline-secondary btn-sm"><i class="fas fa-fw fa-list"></i> Ver Todos</a>
  </div>
</div>
<hr />

<?php
Msg::show(1,'Turno guardado con &eacute;xito.','info');
Msg::show(2,'Turno cerrado con &eacute;xito.','success');
Msg::show(3,'Turno aprobado con &eacute;xito.','success');
Msg::show(4,'Turno reabierto con &eacute;xito.','warning');
Msg::show(10,'Faltante agregado.','info');
Msg::show(11,'Anticipo agregado.','info');
Msg::show(12,'Factura a cr&eacute;dito agregada.','info');
Msg::show(13,'Ingreso PROSEGUR agregado.','info');
Msg::show(14,'Gasto caja chica agregado.','info');
Msg::show(20,'Registro eliminado.','warning');
?>

<?php if(!$isEditable) { ?>
<div class="alert alert-warning">
  <i class="fas fa-lock"></i> Este turno esta <?= strtolower($obj->estado); ?> y no puede ser modificado.
</div>
<?php } ?>

<div class="row">
  <!-- Left Column: Basic Info + Cash Counting -->
  <div class="col-md-6">

    <!-- Basic Info Card -->
    <div class="card shadow-sm section-card <?= !$isEditable ? 'readonly-section' : '' ?>">
      <div class="card-header py-2">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-info-circle"></i> Informacion del Turno</h6>
      </div>
      <div class="card-body">
        <form id="turnos-form">
          <input type="hidden" name="id" value="<?= $obj->id; ?>">
          <input type="hidden" name="entidad" value="turnos">

          <div class="row">
            <div class="col-6 mb-2">Fecha:</div>
            <div class="col-6 mb-2">
              <input type="date" class="form-control form-control-sm" name="fecha" value="<?= $obj->fecha ?: date('Y-m-d'); ?>" <?= !$isEditable ? 'readonly' : '' ?>>
            </div>
            <div class="col-6 mb-2">Hora Inicio:</div>
            <div class="col-6 mb-2">
              <input type="time" class="form-control form-control-sm" name="hora_inicio" value="<?= $obj->hora_inicio; ?>" <?= !$isEditable ? 'readonly' : '' ?>>
            </div>
            <div class="col-6 mb-2">Hora Fin:</div>
            <div class="col-6 mb-2">
              <input type="time" class="form-control form-control-sm" name="hora_fin" value="<?= $obj->hora_fin; ?>" <?= !$isEditable ? 'readonly' : '' ?>>
            </div>
            <div class="col-6 mb-2">Atendedor:</div>
            <div class="col-6 mb-2">
              <select name="id_atendedores" class="form-control form-control-sm" <?= !$isEditable ? 'disabled' : '' ?>>
                <option value="">Seleccionar...</option>
                <?php foreach($atendedores as $a) { ?>
                <option value="<?= $a->id; ?>" <?= ($obj->id_atendedores == $a->id) ? 'selected' : ''; ?>><?= htmlspecialchars($a->nombre_completo); ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-12 mb-2">Observaciones:</div>
            <div class="col-12 mb-2">
              <textarea class="form-control form-control-sm" name="observaciones" rows="2" <?= !$isEditable ? 'readonly' : '' ?>><?= htmlspecialchars($obj->observaciones); ?></textarea>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Cash Counting Card -->
    <div class="card shadow-sm section-card <?= !$isEditable ? 'readonly-section' : '' ?>">
      <div class="card-header py-2">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-money-bill-wave"></i> Conteo de Efectivo</h6>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Billetes -->
          <div class="col-6">
            <h6 class="text-muted">Billetes</h6>
            <table class="table table-sm">
              <tr>
                <td>$20.000</td>
                <td><input type="number" class="form-control form-control-sm cash-input cash-billete" name="billetes_20000" value="<?= $obj->billetes_20000; ?>" min="0" data-valor="20000" <?= !$isEditable ? 'readonly' : '' ?>></td>
                <td class="cash-total" id="total-20000">$0</td>
              </tr>
              <tr>
                <td>$10.000</td>
                <td><input type="number" class="form-control form-control-sm cash-input cash-billete" name="billetes_10000" value="<?= $obj->billetes_10000; ?>" min="0" data-valor="10000" <?= !$isEditable ? 'readonly' : '' ?>></td>
                <td class="cash-total" id="total-10000">$0</td>
              </tr>
              <tr>
                <td>$5.000</td>
                <td><input type="number" class="form-control form-control-sm cash-input cash-billete" name="billetes_5000" value="<?= $obj->billetes_5000; ?>" min="0" data-valor="5000" <?= !$isEditable ? 'readonly' : '' ?>></td>
                <td class="cash-total" id="total-5000">$0</td>
              </tr>
              <tr>
                <td>$2.000</td>
                <td><input type="number" class="form-control form-control-sm cash-input cash-billete" name="billetes_2000" value="<?= $obj->billetes_2000; ?>" min="0" data-valor="2000" <?= !$isEditable ? 'readonly' : '' ?>></td>
                <td class="cash-total" id="total-2000">$0</td>
              </tr>
              <tr>
                <td>$1.000</td>
                <td><input type="number" class="form-control form-control-sm cash-input cash-billete" name="billetes_1000" value="<?= $obj->billetes_1000; ?>" min="0" data-valor="1000" <?= !$isEditable ? 'readonly' : '' ?>></td>
                <td class="cash-total" id="total-1000">$0</td>
              </tr>
              <tr class="table-secondary">
                <td colspan="2"><strong>Total Billetes</strong></td>
                <td class="cash-total" id="total-billetes">$0</td>
              </tr>
            </table>
          </div>
          <!-- Monedas -->
          <div class="col-6">
            <h6 class="text-muted">Monedas</h6>
            <table class="table table-sm">
              <tr>
                <td>$500</td>
                <td><input type="number" class="form-control form-control-sm cash-input cash-moneda" name="monedas_500" value="<?= $obj->monedas_500; ?>" min="0" data-valor="500" <?= !$isEditable ? 'readonly' : '' ?>></td>
                <td class="cash-total" id="total-500">$0</td>
              </tr>
              <tr>
                <td>$100</td>
                <td><input type="number" class="form-control form-control-sm cash-input cash-moneda" name="monedas_100" value="<?= $obj->monedas_100; ?>" min="0" data-valor="100" <?= !$isEditable ? 'readonly' : '' ?>></td>
                <td class="cash-total" id="total-100">$0</td>
              </tr>
              <tr>
                <td>$50</td>
                <td><input type="number" class="form-control form-control-sm cash-input cash-moneda" name="monedas_50" value="<?= $obj->monedas_50; ?>" min="0" data-valor="50" <?= !$isEditable ? 'readonly' : '' ?>></td>
                <td class="cash-total" id="total-50">$0</td>
              </tr>
              <tr>
                <td>$10</td>
                <td><input type="number" class="form-control form-control-sm cash-input cash-moneda" name="monedas_10" value="<?= $obj->monedas_10; ?>" min="0" data-valor="10" <?= !$isEditable ? 'readonly' : '' ?>></td>
                <td class="cash-total" id="total-10">$0</td>
              </tr>
              <tr class="table-secondary">
                <td colspan="2"><strong>Total Monedas</strong></td>
                <td class="cash-total" id="total-monedas">$0</td>
              </tr>
              <tr class="table-primary">
                <td colspan="2"><strong>TOTAL EFECTIVO</strong></td>
                <td class="cash-total" id="total-efectivo">$0</td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="card shadow-sm section-card">
      <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <?php if($obj->id != "" && $isEditable) { ?>
            <button class="btn btn-danger btn-sm eliminar-turno-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
            <?php } ?>
          </div>
          <div>
            <?php if($isEditable) { ?>
            <button class="btn btn-primary btn-sm" id="guardar-turno-btn"><i class="fas fa-fw fa-save"></i> Guardar Turno</button>
            <?php } ?>
            <?php if($obj->id != "" && $obj->canClose()) { ?>
            <button class="btn btn-info btn-sm" id="cerrar-turno-btn"><i class="fas fa-fw fa-lock"></i> Cerrar Turno</button>
            <?php } ?>
            <?php if($obj->id != "" && $obj->canApprove()) { ?>
            <button class="btn btn-success btn-sm" id="aprobar-turno-btn"><i class="fas fa-fw fa-check"></i> Aprobar Turno</button>
            <?php } ?>
            <?php if($obj->id != "" && $obj->canReopen()) { ?>
            <button class="btn btn-warning btn-sm" id="reabrir-turno-btn"><i class="fas fa-fw fa-unlock"></i> Reabrir Turno</button>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Right Column: Related Items -->
  <div class="col-md-6">

    <?php if($obj->id != "") { ?>

    <!-- Faltantes -->
    <div class="card shadow-sm section-card">
      <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-fw fa-exclamation-triangle"></i> Faltantes</h6>
        <?php if($isEditable) { ?>
        <button class="btn btn-sm btn-outline-danger add-faltante-btn"><i class="fas fa-plus"></i></button>
        <?php } ?>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr>
              <th>Descripcion</th>
              <th>Tipo</th>
              <th class="text-end">Monto</th>
              <?php if($isEditable) { ?><th></th><?php } ?>
            </tr>
          </thead>
          <tbody id="faltantes-list">
            <?php foreach($faltantes as $f) { ?>
            <tr class="item-row">
              <td><?= htmlspecialchars($f->descripcion); ?></td>
              <td><span class="badge bg-secondary"><?= $f->tipo; ?></span></td>
              <td class="text-end text-danger"><?= $f->getFormattedMonto(); ?></td>
              <?php if($isEditable) { ?>
              <td><button class="btn btn-sm btn-link text-danger delete-item-btn" data-tipo="faltante" data-id="<?= $f->id; ?>"><i class="fas fa-times"></i></button></td>
              <?php } ?>
            </tr>
            <?php } ?>
            <?php if(count($faltantes) == 0) { ?>
            <tr><td colspan="4" class="text-center text-muted">Sin faltantes</td></tr>
            <?php } ?>
          </tbody>
          <tfoot class="table-secondary">
            <tr>
              <td colspan="2"><strong>Total</strong></td>
              <td class="text-end text-danger"><strong><?= Turno::formatMoney($obj->total_faltantes); ?></strong></td>
              <?php if($isEditable) { ?><td></td><?php } ?>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Anticipos -->
    <div class="card shadow-sm section-card">
      <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-fw fa-hand-holding-usd"></i> Anticipos</h6>
        <?php if($isEditable) { ?>
        <button class="btn btn-sm btn-outline-warning add-anticipo-btn"><i class="fas fa-plus"></i></button>
        <?php } ?>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr>
              <th>Atendedor</th>
              <th>Motivo</th>
              <th class="text-end">Monto</th>
              <?php if($isEditable) { ?><th></th><?php } ?>
            </tr>
          </thead>
          <tbody id="anticipos-list">
            <?php foreach($anticipos as $a) {
              $atendedor_anticipo = $a->getAtendedor();
            ?>
            <tr class="item-row">
              <td><?= $atendedor_anticipo ? htmlspecialchars($atendedor_anticipo->nombre_completo) : '-'; ?></td>
              <td><?= htmlspecialchars($a->motivo); ?></td>
              <td class="text-end text-warning"><?= $a->getFormattedMonto(); ?></td>
              <?php if($isEditable) { ?>
              <td><button class="btn btn-sm btn-link text-danger delete-item-btn" data-tipo="anticipo" data-id="<?= $a->id; ?>"><i class="fas fa-times"></i></button></td>
              <?php } ?>
            </tr>
            <?php } ?>
            <?php if(count($anticipos) == 0) { ?>
            <tr><td colspan="4" class="text-center text-muted">Sin anticipos</td></tr>
            <?php } ?>
          </tbody>
          <tfoot class="table-secondary">
            <tr>
              <td colspan="2"><strong>Total</strong></td>
              <td class="text-end text-warning"><strong><?= Turno::formatMoney($obj->total_anticipos); ?></strong></td>
              <?php if($isEditable) { ?><td></td><?php } ?>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Facturas a Credito -->
    <div class="card shadow-sm section-card">
      <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-fw fa-file-invoice-dollar"></i> Facturas a Credito</h6>
        <?php if($isEditable) { ?>
        <button class="btn btn-sm btn-outline-info add-factura-btn"><i class="fas fa-plus"></i></button>
        <?php } ?>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr>
              <th>N Factura</th>
              <th>Cliente</th>
              <th class="text-end">Monto</th>
              <?php if($isEditable) { ?><th></th><?php } ?>
            </tr>
          </thead>
          <tbody id="facturas-list">
            <?php foreach($facturas_credito as $f) { ?>
            <tr class="item-row">
              <td><?= htmlspecialchars($f->numero_factura); ?></td>
              <td><?= htmlspecialchars($f->nombre_cliente); ?></td>
              <td class="text-end"><?= $f->getFormattedMonto(); ?></td>
              <?php if($isEditable) { ?>
              <td><button class="btn btn-sm btn-link text-danger delete-item-btn" data-tipo="factura" data-id="<?= $f->id; ?>"><i class="fas fa-times"></i></button></td>
              <?php } ?>
            </tr>
            <?php } ?>
            <?php if(count($facturas_credito) == 0) { ?>
            <tr><td colspan="4" class="text-center text-muted">Sin facturas</td></tr>
            <?php } ?>
          </tbody>
          <tfoot class="table-secondary">
            <tr>
              <td colspan="2"><strong>Total</strong></td>
              <td class="text-end"><strong><?= Turno::formatMoney($obj->total_facturas_credito); ?></strong></td>
              <?php if($isEditable) { ?><td></td><?php } ?>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Ingresos PROSEGUR -->
    <div class="card shadow-sm section-card">
      <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-fw fa-truck"></i> Ingresos PROSEGUR MAE</h6>
        <?php if($isEditable) { ?>
        <button class="btn btn-sm btn-outline-success add-prosegur-btn"><i class="fas fa-plus"></i></button>
        <?php } ?>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr>
              <th>N Boleta</th>
              <th>Descripcion</th>
              <th class="text-end">Monto</th>
              <?php if($isEditable) { ?><th></th><?php } ?>
            </tr>
          </thead>
          <tbody id="prosegur-list">
            <?php foreach($ingresos_prosegur as $i) { ?>
            <tr class="item-row">
              <td><?= htmlspecialchars($i->numero_boleta); ?></td>
              <td><?= htmlspecialchars($i->descripcion); ?></td>
              <td class="text-end text-success"><?= $i->getFormattedMonto(); ?></td>
              <?php if($isEditable) { ?>
              <td><button class="btn btn-sm btn-link text-danger delete-item-btn" data-tipo="prosegur" data-id="<?= $i->id; ?>"><i class="fas fa-times"></i></button></td>
              <?php } ?>
            </tr>
            <?php } ?>
            <?php if(count($ingresos_prosegur) == 0) { ?>
            <tr><td colspan="4" class="text-center text-muted">Sin ingresos</td></tr>
            <?php } ?>
          </tbody>
          <tfoot class="table-secondary">
            <tr>
              <td colspan="2"><strong>Total</strong></td>
              <td class="text-end text-success"><strong><?= Turno::formatMoney($obj->total_ingresos_prosegur); ?></strong></td>
              <?php if($isEditable) { ?><td></td><?php } ?>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Gastos Caja Chica -->
    <div class="card shadow-sm section-card">
      <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-fw fa-receipt"></i> Gastos Caja Chica / Donaciones</h6>
        <?php if($isEditable) { ?>
        <button class="btn btn-sm btn-outline-secondary add-gasto-btn"><i class="fas fa-plus"></i></button>
        <?php } ?>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr>
              <th>Descripcion</th>
              <th>Tipo</th>
              <th class="text-end">Monto</th>
              <?php if($isEditable) { ?><th></th><?php } ?>
            </tr>
          </thead>
          <tbody id="gastos-list">
            <?php foreach($gastos_caja_chica as $g) { ?>
            <tr class="item-row">
              <td><?= htmlspecialchars($g->descripcion); ?></td>
              <td><span class="badge <?= $g->getTipoBadgeClass(); ?>"><?= $g->tipo; ?></span></td>
              <td class="text-end"><?= $g->getFormattedMonto(); ?></td>
              <?php if($isEditable) { ?>
              <td><button class="btn btn-sm btn-link text-danger delete-item-btn" data-tipo="gasto" data-id="<?= $g->id; ?>"><i class="fas fa-times"></i></button></td>
              <?php } ?>
            </tr>
            <?php } ?>
            <?php if(count($gastos_caja_chica) == 0) { ?>
            <tr><td colspan="4" class="text-center text-muted">Sin gastos</td></tr>
            <?php } ?>
          </tbody>
          <tfoot class="table-secondary">
            <tr>
              <td colspan="2"><strong>Total</strong></td>
              <td class="text-end"><strong><?= Turno::formatMoney($obj->total_gastos_caja_chica); ?></strong></td>
              <?php if($isEditable) { ?><td></td><?php } ?>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <?php } else { ?>
    <div class="alert alert-info">
      <i class="fas fa-info-circle"></i> Guarde el turno primero para agregar faltantes, anticipos y otros registros.
    </div>
    <?php } ?>

  </div>
</div>

<!-- MODALS -->

<!-- Modal: Eliminar Turno -->
<div class="modal fade" id="eliminar-turno-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar Turno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-center">Desea eliminar este turno?<br/>Este paso no es reversible.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmar-eliminar-turno">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Agregar Faltante -->
<div class="modal fade" id="add-faltante-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger"></i> Agregar Faltante</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="faltante-form">
          <input type="hidden" name="id_turnos" value="<?= $obj->id; ?>">
          <div class="mb-3">
            <label class="form-label">Descripcion</label>
            <input type="text" class="form-control" name="descripcion" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Tipo</label>
            <select class="form-control" name="tipo">
              <option value="Efectivo">Efectivo</option>
              <option value="Producto">Producto</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" class="form-control" name="monto" min="0" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea class="form-control" name="observaciones" rows="2"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="guardar-faltante-btn">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Agregar Anticipo -->
<div class="modal fade" id="add-anticipo-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-hand-holding-usd text-warning"></i> Agregar Anticipo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="anticipo-form">
          <input type="hidden" name="id_turnos" value="<?= $obj->id; ?>">
          <div class="mb-3">
            <label class="form-label">Atendedor que recibe</label>
            <select class="form-control" name="id_atendedores" required>
              <option value="">Seleccionar...</option>
              <?php foreach($atendedores as $a) { ?>
              <option value="<?= $a->id; ?>"><?= htmlspecialchars($a->nombre_completo); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Motivo</label>
            <input type="text" class="form-control" name="motivo" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" class="form-control" name="monto" min="0" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea class="form-control" name="observaciones" rows="2"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" id="guardar-anticipo-btn">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Agregar Factura a Credito -->
<div class="modal fade" id="add-factura-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-file-invoice-dollar text-info"></i> Agregar Factura a Credito</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="factura-form">
          <input type="hidden" name="id_turnos" value="<?= $obj->id; ?>">
          <div class="mb-3">
            <label class="form-label">N Factura</label>
            <input type="text" class="form-control" name="numero_factura" required>
          </div>
          <div class="mb-3">
            <label class="form-label">RUT Cliente</label>
            <input type="text" class="form-control" name="rut_cliente" placeholder="12.345.678-9">
          </div>
          <div class="mb-3">
            <label class="form-label">Nombre Cliente</label>
            <input type="text" class="form-control" name="nombre_cliente" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" class="form-control" name="monto" min="0" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Fecha Vencimiento</label>
            <input type="date" class="form-control" name="fecha_vencimiento">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-info" id="guardar-factura-btn">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Agregar Ingreso PROSEGUR -->
<div class="modal fade" id="add-prosegur-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-truck text-success"></i> Agregar Ingreso PROSEGUR MAE</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="prosegur-form">
          <input type="hidden" name="id_turnos" value="<?= $obj->id; ?>">
          <div class="mb-3">
            <label class="form-label">N Boleta</label>
            <input type="text" class="form-control" name="numero_boleta" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" class="form-control" name="monto" min="0" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Fecha Ingreso</label>
            <input type="date" class="form-control" name="fecha_ingreso" value="<?= date('Y-m-d'); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Hora Ingreso</label>
            <input type="time" class="form-control" name="hora_ingreso">
          </div>
          <div class="mb-3">
            <label class="form-label">Descripcion</label>
            <textarea class="form-control" name="descripcion" rows="2"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="guardar-prosegur-btn">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Agregar Gasto Caja Chica -->
<div class="modal fade" id="add-gasto-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-receipt"></i> Agregar Gasto Caja Chica</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="gasto-form">
          <input type="hidden" name="id_turnos" value="<?= $obj->id; ?>">
          <div class="mb-3">
            <label class="form-label">Tipo</label>
            <select class="form-control" name="tipo">
              <option value="Gasto">Gasto</option>
              <option value="Donacion">Donacion</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Categoria</label>
            <select class="form-control" name="categoria">
              <option value="Limpieza">Limpieza</option>
              <option value="Oficina">Oficina</option>
              <option value="Mantenimiento">Mantenimiento</option>
              <option value="Transporte">Transporte</option>
              <option value="Alimentacion">Alimentacion</option>
              <option value="Otros">Otros</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripcion</label>
            <input type="text" class="form-control" name="descripcion" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" class="form-control" name="monto" min="0" required>
          </div>
          <div class="mb-3">
            <label class="form-label">N Documento</label>
            <input type="text" class="form-control" name="numero_documento">
          </div>
          <div class="mb-3">
            <label class="form-label">Proveedor</label>
            <input type="text" class="form-control" name="proveedor">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar-gasto-btn">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Cerrar/Aprobar/Reabrir -->
<div class="modal fade" id="workflow-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="workflow-modal-title">Accion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="workflow-modal-message"></p>
        <div class="mb-3">
          <label class="form-label">Observaciones (opcional):</label>
          <textarea class="form-control" id="workflow-observaciones" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn" id="workflow-confirm-btn">Confirmar</button>
      </div>
    </div>
  </div>
</div>

<script>
var obj = <?= json_encode($obj, JSON_PRETTY_PRINT); ?>;
var isEditable = <?= $isEditable ? 'true' : 'false'; ?>;

// Format money helper
function formatMoney(amount) {
  return '$' + amount.toLocaleString('es-CL');
}

// Calculate and display cash totals
function calculateCashTotals() {
  var totalBilletes = 0;
  var totalMonedas = 0;

  // Billetes
  $('.cash-billete').each(function(){
    var cantidad = parseInt($(this).val()) || 0;
    var valor = parseInt($(this).data('valor'));
    var total = cantidad * valor;
    totalBilletes += total;
    $('#total-' + valor).text(formatMoney(total));
  });

  // Monedas
  $('.cash-moneda').each(function(){
    var cantidad = parseInt($(this).val()) || 0;
    var valor = parseInt($(this).data('valor'));
    var total = cantidad * valor;
    totalMonedas += total;
    $('#total-' + valor).text(formatMoney(total));
  });

  $('#total-billetes').text(formatMoney(totalBilletes));
  $('#total-monedas').text(formatMoney(totalMonedas));
  $('#total-efectivo').text(formatMoney(totalBilletes + totalMonedas));
}

$(document).ready(function(){
  calculateCashTotals();
});

// Recalculate on input change
$('.cash-input').on('input', function(){
  calculateCashTotals();
});

// Save Turno
$('#guardar-turno-btn').click(function(){
  // Helper: convertir vacío a null para campos que no aceptan string vacío
  function emptyToNull(val) {
    return val === '' ? null : val;
  }

  var data = {
    id: $('input[name="id"]').val(),
    entidad: 'turnos',
    fecha: emptyToNull($('input[name="fecha"]').val()),
    hora_inicio: emptyToNull($('input[name="hora_inicio"]').val()),
    hora_fin: emptyToNull($('input[name="hora_fin"]').val()),
    id_atendedores: emptyToNull($('select[name="id_atendedores"]').val()),
    observaciones: $('textarea[name="observaciones"]').val(),
    billetes_20000: $('input[name="billetes_20000"]').val() || 0,
    billetes_10000: $('input[name="billetes_10000"]').val() || 0,
    billetes_5000: $('input[name="billetes_5000"]').val() || 0,
    billetes_2000: $('input[name="billetes_2000"]').val() || 0,
    billetes_1000: $('input[name="billetes_1000"]').val() || 0,
    monedas_500: $('input[name="monedas_500"]').val() || 0,
    monedas_100: $('input[name="monedas_100"]').val() || 0,
    monedas_50: $('input[name="monedas_50"]').val() || 0,
    monedas_10: $('input[name="monedas_10"]').val() || 0
  };

  $.ajax({
    url: './ajax/ajax_guardarTurno.php',
    method: 'POST',
    data: data,
    dataType: 'json',
    success: function(response){
      if(response.status == 'OK') {
        window.location.href = './?s=detalle-turnos&id=' + response.id + '&msg=1';
      } else {
        alert(response.mensaje || 'Error al guardar');
      }
    },
    error: function(){
      alert('Error de conexion');
    }
  });
});

// Delete turno
$('.eliminar-turno-btn').click(function(){
  $('#eliminar-turno-modal').modal('show');
});

$('#confirmar-eliminar-turno').click(function(){
  $.ajax({
    url: './ajax/ajax_eliminarTurno.php',
    method: 'POST',
    data: { id: obj.id },
    success: function(response){
      if(response.status == 'OK') {
        window.location.href = './?s=turnos&msg=2';
      } else {
        alert(response.mensaje || 'Error al eliminar');
      }
    },
    error: function(){
      alert('Error de conexion');
    }
  });
});

// Workflow actions
var workflowAction = '';

$('#cerrar-turno-btn').click(function(){
  workflowAction = 'cerrar';
  $('#workflow-modal-title').text('Cerrar Turno');
  $('#workflow-modal-message').text('Esta accion cerrara el turno y no se podran agregar mas registros.');
  $('#workflow-confirm-btn').removeClass().addClass('btn btn-info').text('Cerrar Turno');
  $('#workflow-observaciones').val('');
  $('#workflow-modal').modal('show');
});

$('#aprobar-turno-btn').click(function(){
  workflowAction = 'aprobar';
  $('#workflow-modal-title').text('Aprobar Turno');
  $('#workflow-modal-message').text('Una vez aprobado, el turno quedara marcado como finalizado.');
  $('#workflow-confirm-btn').removeClass().addClass('btn btn-success').text('Aprobar Turno');
  $('#workflow-observaciones').val('');
  $('#workflow-modal').modal('show');
});

$('#reabrir-turno-btn').click(function(){
  workflowAction = 'reabrir';
  $('#workflow-modal-title').text('Reabrir Turno');
  $('#workflow-modal-message').text('El turno sera reabierto y se podran modificar los registros.');
  $('#workflow-confirm-btn').removeClass().addClass('btn btn-warning').text('Reabrir Turno');
  $('#workflow-observaciones').val('');
  $('#workflow-modal').modal('show');
});

$('#workflow-confirm-btn').click(function(){
  var url = './ajax/ajax_' + workflowAction + 'Turno.php';
  $.ajax({
    url: url,
    method: 'POST',
    data: {
      id: obj.id,
      observaciones: $('#workflow-observaciones').val()
    },
    success: function(response){
      if(response.status == 'OK') {
        var msgNum = workflowAction == 'cerrar' ? 2 : (workflowAction == 'aprobar' ? 3 : 4);
        window.location.href = './?s=detalle-turnos&id=' + obj.id + '&msg=' + msgNum;
      } else {
        alert(response.mensaje || 'Error');
      }
    },
    error: function(){
      alert('Error de conexion');
    }
  });
});

// Add item modals
$('.add-faltante-btn').click(function(){ $('#add-faltante-modal').modal('show'); });
$('.add-anticipo-btn').click(function(){ $('#add-anticipo-modal').modal('show'); });
$('.add-factura-btn').click(function(){ $('#add-factura-modal').modal('show'); });
$('.add-prosegur-btn').click(function(){ $('#add-prosegur-modal').modal('show'); });
$('.add-gasto-btn').click(function(){ $('#add-gasto-modal').modal('show'); });

// Save items
function saveItem(formId, endpoint, msgNum) {
  var form = $('#' + formId)[0];
  var formData = new FormData(form);
  var data = {};
  formData.forEach(function(value, key){ data[key] = value; });

  $.ajax({
    url: './ajax/' + endpoint,
    method: 'POST',
    data: data,
    success: function(response){
      if(response.status == 'OK') {
        window.location.href = './?s=detalle-turnos&id=' + obj.id + '&msg=' + msgNum;
      } else {
        alert(response.mensaje || 'Error al guardar');
      }
    },
    error: function(){
      alert('Error de conexion');
    }
  });
}

$('#guardar-faltante-btn').click(function(){ saveItem('faltante-form', 'ajax_guardarTurnoFaltante.php', 10); });
$('#guardar-anticipo-btn').click(function(){ saveItem('anticipo-form', 'ajax_guardarTurnoAnticipo.php', 11); });
$('#guardar-factura-btn').click(function(){ saveItem('factura-form', 'ajax_guardarTurnoFacturaCredito.php', 12); });
$('#guardar-prosegur-btn').click(function(){ saveItem('prosegur-form', 'ajax_guardarTurnoIngresoProsegur.php', 13); });
$('#guardar-gasto-btn').click(function(){ saveItem('gasto-form', 'ajax_guardarTurnoGastoCajaChica.php', 14); });

// Delete items
$('.delete-item-btn').click(function(e){
  e.preventDefault();
  if(!confirm('Eliminar este registro?')) return;

  var tipo = $(this).data('tipo');
  var id = $(this).data('id');
  var endpoint = '';

  switch(tipo) {
    case 'faltante': endpoint = 'ajax_eliminarTurnoFaltante.php'; break;
    case 'anticipo': endpoint = 'ajax_eliminarTurnoAnticipo.php'; break;
    case 'factura': endpoint = 'ajax_eliminarTurnoFacturaCredito.php'; break;
    case 'prosegur': endpoint = 'ajax_eliminarTurnoIngresoProsegur.php'; break;
    case 'gasto': endpoint = 'ajax_eliminarTurnoGastoCajaChica.php'; break;
  }

  $.ajax({
    url: './ajax/' + endpoint,
    method: 'POST',
    data: { id: id },
    success: function(response){
      if(response.status == 'OK') {
        window.location.href = './?s=detalle-turnos&id=' + obj.id + '&msg=20';
      } else {
        alert(response.mensaje || 'Error al eliminar');
      }
    },
    error: function(){
      alert('Error de conexion');
    }
  });
});

</script>
