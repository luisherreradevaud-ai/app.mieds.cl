<?php
/**
 * TurnoFacturaCredito (Credit Invoice) Class
 *
 * Tracks credit invoices (facturas a crédito) during a shift.
 * These are invoices issued on credit, not paid immediately.
 */
class TurnoFacturaCredito extends Base {

  public $id = "";
  public $id_turnos = "";
  public $numero_factura = "";
  public $rut_cliente = "";
  public $nombre_cliente = "";
  public $monto = 0;
  public $fecha_vencimiento = "";
  public $descripcion = "";
  public $observaciones = "";
  public $estado = "activo";  // activo, pagada, vencida, eliminado
  public $pagada_fecha = "";
  public $creada = "";
  public $actualizada = "";
  public $creado_por = "";

  function __construct($id = null) {
    $this->tableName("turnos_facturas_credito");
    if($id) {
      $this->id = $id;
      $this->getFromDB();
    }
  }

  /**
   * Get from database
   */
  public function getFromDB() {
    $info = $this->getInfoDatabase('id');
    if($info) {
      $this->setProperties($info);
    }
  }

  /**
   * Override save to update timestamps
   */
  public function save() {
    $this->actualizada = date('Y-m-d H:i:s');
    if($this->id == "") {
      $this->creada = date('Y-m-d H:i:s');
    }
    $result = parent::save();

    // Update parent turno totals
    $this->updateTurnoTotal();

    return $result;
  }

  /**
   * Get the parent Turno
   */
  public function getTurno() {
    if($this->id_turnos == "" || $this->id_turnos == 0) {
      return null;
    }
    return new Turno($this->id_turnos);
  }

  /**
   * Update parent turno total
   */
  public function updateTurnoTotal() {
    $turno = $this->getTurno();
    if($turno) {
      $turno->recalculateTotalFacturasCredito();
    }
  }

  /**
   * Mark invoice as paid
   */
  public function markAsPaid() {
    $this->estado = "pagada";
    $this->pagada_fecha = date('Y-m-d H:i:s');
    $this->save();
    return array('status' => 'OK', 'mensaje' => 'Factura marcada como pagada');
  }

  /**
   * Check if invoice is overdue
   */
  public function isOverdue() {
    if($this->estado == "pagada" || $this->estado == "eliminado") return false;
    if($this->fecha_vencimiento == "" || $this->fecha_vencimiento == "0000-00-00") return false;
    return strtotime($this->fecha_vencimiento) < strtotime(date('Y-m-d'));
  }

  /**
   * Update overdue status
   */
  public function updateOverdueStatus() {
    if($this->isOverdue() && $this->estado == "activo") {
      $this->estado = "vencida";
      parent::save();  // Save without triggering recalculation
    }
  }

  /**
   * Soft delete
   */
  public function softDelete() {
    $this->estado = "eliminado";
    $this->save();
  }

  /**
   * Check if deleted
   */
  public function isDeleted() {
    return $this->estado == "eliminado";
  }

  /**
   * Check if can edit (only if parent turno is open)
   */
  public function canEdit() {
    $turno = $this->getTurno();
    return $turno && $turno->canEdit();
  }

  /**
   * Format amount as Chilean peso
   */
  public function getFormattedMonto() {
    return '$' . number_format($this->monto, 0, ',', '.');
  }

  /**
   * Get formatted creation date
   */
  public function getFormattedDate() {
    if($this->creada == "" || $this->creada == "0000-00-00 00:00:00") return "";
    return date('d-m-Y H:i', strtotime($this->creada));
  }

  /**
   * Get formatted due date
   */
  public function getFormattedVencimiento() {
    if($this->fecha_vencimiento == "" || $this->fecha_vencimiento == "0000-00-00") return "";
    return date('d-m-Y', strtotime($this->fecha_vencimiento));
  }

  /**
   * Get status badge class
   */
  public function getStatusBadgeClass() {
    switch($this->estado) {
      case 'activo':
        return 'bg-warning text-dark';
      case 'pagada':
        return 'bg-success';
      case 'vencida':
        return 'bg-danger';
      default:
        return 'bg-secondary';
    }
  }

  /**
   * Get all facturas for a turno
   */
  public static function getByTurno($id_turno) {
    return self::getAll("WHERE id_turnos='".$id_turno."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Get total amount for a turno
   */
  public static function getTotalByTurno($id_turno) {
    $facturas = self::getByTurno($id_turno);
    $total = 0;
    foreach($facturas as $f) {
      $total += floatval($f->monto);
    }
    return $total;
  }

  /**
   * Get all overdue invoices
   */
  public static function getOverdue() {
    return self::getAll("WHERE estado='vencida' OR (estado='activo' AND fecha_vencimiento < '".date('Y-m-d')."' AND fecha_vencimiento != '0000-00-00') ORDER BY fecha_vencimiento ASC");
  }

  /**
   * Get all pending invoices (active, not paid)
   */
  public static function getPending() {
    return self::getAll("WHERE estado IN ('activo', 'vencida') ORDER BY fecha_vencimiento ASC");
  }

  /**
   * Update all overdue statuses
   */
  public static function updateAllOverdueStatuses() {
    $pending = self::getAll("WHERE estado='activo' AND fecha_vencimiento < '".date('Y-m-d')."' AND fecha_vencimiento != '0000-00-00'");
    foreach($pending as $factura) {
      $factura->updateOverdueStatus();
    }
  }
}
