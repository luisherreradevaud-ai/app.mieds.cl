<?php
/**
 * TurnoFaltante (Shortage) Class
 *
 * Tracks shortages (faltantes) reported during a shift.
 * Each shortage is linked to a specific shift and atendedor.
 *
 * Fields: id_atendedores, monto, motivo, descripcion
 */
class TurnoFaltante extends Base {

  public $id = "";
  public $id_turnos = "";
  public $id_atendedores = "";
  public $monto = 0;
  public $motivo = "";
  public $descripcion = "";
  public $observaciones = "";
  public $estado = "activo";  // activo, eliminado
  public $creada = "";
  public $actualizada = "";
  public $creado_por = "";

  function __construct($id = null) {
    $this->tableName("turnos_faltantes");
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
    if($this->id_atendedores === '' || $this->id_atendedores === '0') $this->id_atendedores = null;

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
   * Get the Atendedor
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
      $turno->recalculateTotalFaltantes();
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
   * Get all faltantes for a turno
   */
  public static function getByTurno($id_turno) {
    return self::getAll("WHERE id_turnos='".$id_turno."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Get total amount for a turno
   */
  public static function getTotalByTurno($id_turno) {
    $faltantes = self::getByTurno($id_turno);
    $total = 0;
    foreach($faltantes as $f) {
      $total += floatval($f->monto);
    }
    return $total;
  }

  /**
   * Get all faltantes for an atendedor
   */
  public static function getByAtendedor($id_atendedor) {
    return self::getAll("WHERE id_atendedores='".$id_atendedor."' AND estado!='eliminado' ORDER BY creada DESC");
  }
}
