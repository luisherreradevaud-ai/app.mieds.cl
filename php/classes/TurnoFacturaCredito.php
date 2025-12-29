<?php
/**
 * TurnoFacturaCredito (Credit Invoice) Class
 *
 * Tracks credit invoices (facturas a crédito) during a shift.
 * These are invoices issued on credit, not paid immediately.
 *
 * Fields: numero_factura, monto, id_clientes
 */
class TurnoFacturaCredito extends Base {

  public $id = "";
  public $id_turnos = "";
  public $id_clientes = "";
  public $numero_factura = "";
  public $monto = 0;
  public $estado = "activo";  // activo, eliminado
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
    // Sanitize empty values
    if($this->id_clientes === '' || $this->id_clientes === '0') $this->id_clientes = null;

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
   * Get the Cliente
   */
  public function getCliente() {
    if($this->id_clientes == "" || $this->id_clientes == 0) {
      return null;
    }
    return new Cliente($this->id_clientes);
  }

  /**
   * Get cliente name (helper for display)
   */
  public function getClienteNombre() {
    $cliente = $this->getCliente();
    return $cliente ? $cliente->nombre : '-';
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
   * Get all facturas for a cliente
   */
  public static function getByCliente($id_cliente) {
    return self::getAll("WHERE id_clientes='".$id_cliente."' AND estado!='eliminado' ORDER BY creada DESC");
  }
}
