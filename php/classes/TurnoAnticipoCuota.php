<?php
/**
 * TurnoAnticipoCuota (Advance Payment Installment) Class
 *
 * Tracks monthly installments for advance payments.
 * Each anticipo can be divided into multiple cuotas.
 */
class TurnoAnticipoCuota extends Base {

  public $id = "";
  public $id_anticipos = "";
  public $mes = "";  // Format: YYYY-MM
  public $monto = 0;
  public $estado = "pendiente";  // pendiente, pagado, cancelado
  public $fecha_pago = "";
  public $observaciones = "";
  public $creada = "";
  public $actualizada = "";

  function __construct($id = null) {
    $this->tableName("turnos_anticipos_cuotas");
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
    return parent::save();
  }

  /**
   * Get the parent Anticipo
   */
  public function getAnticipo() {
    if($this->id_anticipos == "" || $this->id_anticipos == 0) {
      return null;
    }
    return new TurnoAnticipo($this->id_anticipos);
  }

  /**
   * Mark as paid
   */
  public function markAsPaid() {
    $this->estado = "pagado";
    $this->fecha_pago = date('Y-m-d H:i:s');
    $this->save();
  }

  /**
   * Mark as cancelled
   */
  public function cancel() {
    $this->estado = "cancelado";
    $this->save();
  }

  /**
   * Check if paid
   */
  public function isPaid() {
    return $this->estado == "pagado";
  }

  /**
   * Format amount as Chilean peso
   */
  public function getFormattedMonto() {
    return '$' . number_format($this->monto, 0, ',', '.');
  }

  /**
   * Get formatted month name
   */
  public function getFormattedMonth() {
    if(!$this->mes) return "";
    $months = array(
      '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
      '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
      '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
      '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
    );
    $parts = explode('-', $this->mes);
    if(count($parts) == 2) {
      return $months[$parts[1]] . ' ' . $parts[0];
    }
    return $this->mes;
  }

  /**
   * Get status badge class
   */
  public function getStatusBadgeClass() {
    switch($this->estado) {
      case 'pendiente':
        return 'bg-warning text-dark';
      case 'pagado':
        return 'bg-success';
      case 'cancelado':
        return 'bg-secondary';
      default:
        return 'bg-secondary';
    }
  }

  /**
   * Get all cuotas for an anticipo
   */
  public static function getByAnticipo($id_anticipo) {
    return self::getAll("WHERE id_anticipos='".$id_anticipo."' ORDER BY mes ASC");
  }

  /**
   * Get pending cuotas for an anticipo
   */
  public static function getPendingByAnticipo($id_anticipo) {
    return self::getAll("WHERE id_anticipos='".$id_anticipo."' AND estado='pendiente' ORDER BY mes ASC");
  }

  /**
   * Get all cuotas for a specific month
   */
  public static function getByMonth($mes) {
    return self::getAll("WHERE mes='".$mes."' AND estado!='cancelado' ORDER BY id_anticipos ASC");
  }

  /**
   * Get pending cuotas for a specific month
   */
  public static function getPendingByMonth($mes) {
    return self::getAll("WHERE mes='".$mes."' AND estado='pendiente' ORDER BY id_anticipos ASC");
  }

  /**
   * Get total pending for a month
   */
  public static function getTotalPendingByMonth($mes) {
    $cuotas = self::getPendingByMonth($mes);
    $total = 0;
    foreach($cuotas as $c) {
      $total += floatval($c->monto);
    }
    return $total;
  }

  /**
   * Get all cuotas for an atendedor in a specific month
   */
  public static function getByAtendedorMonth($id_atendedor, $mes) {
    $cuotas = self::getAll("WHERE mes='".$mes."' AND estado!='cancelado'");
    $result = array();
    foreach($cuotas as $cuota) {
      $anticipo = $cuota->getAnticipo();
      if($anticipo && $anticipo->id_atendedores == $id_atendedor) {
        $result[] = $cuota;
      }
    }
    return $result;
  }
}
