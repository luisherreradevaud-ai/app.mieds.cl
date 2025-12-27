<?php
/**
 * TurnoAnticipo (Advance Payment) Class
 *
 * Tracks advance payments (anticipos) given during a shift.
 * Each advance is linked to a specific shift and attendant.
 */
class TurnoAnticipo extends Base {

  public $id = "";
  public $id_turnos = "";
  public $id_atendedores = "";  // Who received the advance
  public $descripcion = "";
  public $monto = 0;
  public $motivo = "";  // Reason for advance
  public $autorizado_por = "";  // User ID who authorized
  public $observaciones = "";
  public $estado = "activo";  // activo, eliminado
  public $creada = "";
  public $actualizada = "";
  public $creado_por = "";

  function __construct($id = null) {
    $this->tableName("turnos_anticipos");
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
   * Get the Atendedor who received the advance
   */
  public function getAtendedor() {
    if($this->id_atendedores == "" || $this->id_atendedores == 0) {
      return null;
    }
    return new Atendedor($this->id_atendedores);
  }

  /**
   * Update parent turno total
   */
  public function updateTurnoTotal() {
    $turno = $this->getTurno();
    if($turno) {
      $turno->recalculateTotalAnticipos();
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
   * Get all anticipos for a turno
   */
  public static function getByTurno($id_turno) {
    return self::getAll("WHERE id_turnos='".$id_turno."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Get all anticipos for an atendedor
   */
  public static function getByAtendedor($id_atendedor) {
    return self::getAll("WHERE id_atendedores='".$id_atendedor."' AND estado!='eliminado' ORDER BY creada DESC");
  }

  /**
   * Get total amount for a turno
   */
  public static function getTotalByTurno($id_turno) {
    $anticipos = self::getByTurno($id_turno);
    $total = 0;
    foreach($anticipos as $a) {
      $total += floatval($a->monto);
    }
    return $total;
  }

  /**
   * Get total amount for an atendedor in a date range
   */
  public static function getTotalByAtendedorDateRange($id_atendedor, $startDate, $endDate) {
    $anticipos = self::getAll("WHERE id_atendedores='".$id_atendedor."' AND estado!='eliminado' AND DATE(creada) >= '".$startDate."' AND DATE(creada) <= '".$endDate."'");
    $total = 0;
    foreach($anticipos as $a) {
      $total += floatval($a->monto);
    }
    return $total;
  }
}
