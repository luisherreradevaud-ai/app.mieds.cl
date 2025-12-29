<?php
/**
 * TurnoDonacion (Donation) Class
 *
 * Tracks donations during a shift.
 * Separate entity from gastos caja chica.
 */
class TurnoDonacion extends Base {

  public $id = "";
  public $id_turnos = "";
  public $id_atendedores = "";
  public $monto = 0;
  public $descripcion = "";
  public $estado = "activo";
  public $creada = "";
  public $actualizada = "";
  public $creado_por = "";

  function __construct($id = null) {
    $this->tableName("turnos_donaciones");
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
      $turno->recalculateTotalDonaciones();
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
   * Get all donaciones for a turno
   */
  public static function getByTurno($id_turno) {
    return self::getAll("WHERE id_turnos='".$id_turno."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Get total amount for a turno
   */
  public static function getTotalByTurno($id_turno) {
    $donaciones = self::getByTurno($id_turno);
    $total = 0;
    foreach($donaciones as $d) {
      $total += floatval($d->monto);
    }
    return $total;
  }

  /**
   * Get all donaciones for an atendedor
   */
  public static function getByAtendedor($id_atendedor) {
    return self::getAll("WHERE id_atendedores='".$id_atendedor."' AND estado!='eliminado' ORDER BY creada DESC");
  }
}
