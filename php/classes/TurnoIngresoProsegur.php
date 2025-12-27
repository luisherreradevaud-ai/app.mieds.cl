<?php
/**
 * TurnoIngresoProsegur (PROSEGUR MAE Income) Class
 *
 * Tracks PROSEGUR MAE income during a shift.
 * PROSEGUR is a cash handling and security company.
 */
class TurnoIngresoProsegur extends Base {

  public $id = "";
  public $id_turnos = "";
  public $numero_boleta = "";  // PROSEGUR receipt number
  public $monto = 0;
  public $fecha_ingreso = "";
  public $hora_ingreso = "";
  public $descripcion = "";
  public $observaciones = "";
  public $estado = "activo";  // activo, eliminado
  public $creada = "";
  public $actualizada = "";
  public $creado_por = "";

  function __construct($id = null) {
    $this->tableName("turnos_ingresos_prosegur");
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
      $turno->recalculateTotalIngresosProsegur();
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
   * Get formatted ingreso date
   */
  public function getFormattedFechaIngreso() {
    if($this->fecha_ingreso == "" || $this->fecha_ingreso == "0000-00-00") return "";
    return date('d-m-Y', strtotime($this->fecha_ingreso));
  }

  /**
   * Get all ingresos for a turno
   */
  public static function getByTurno($id_turno) {
    return self::getAll("WHERE id_turnos='".$id_turno."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Get total amount for a turno
   */
  public static function getTotalByTurno($id_turno) {
    $ingresos = self::getByTurno($id_turno);
    $total = 0;
    foreach($ingresos as $i) {
      $total += floatval($i->monto);
    }
    return $total;
  }

  /**
   * Get ingresos by date range
   */
  public static function getByDateRange($startDate, $endDate) {
    return self::getAll("WHERE estado!='eliminado' AND fecha_ingreso >= '".$startDate."' AND fecha_ingreso <= '".$endDate."' ORDER BY fecha_ingreso DESC");
  }

  /**
   * Get total by date range
   */
  public static function getTotalByDateRange($startDate, $endDate) {
    $ingresos = self::getByDateRange($startDate, $endDate);
    $total = 0;
    foreach($ingresos as $i) {
      $total += floatval($i->monto);
    }
    return $total;
  }
}
