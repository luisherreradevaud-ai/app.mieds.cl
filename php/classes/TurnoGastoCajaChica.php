<?php
/**
 * TurnoGastoCajaChica (Petty Cash Expense) Class
 *
 * Tracks petty cash expenses (gastos caja chica) during a shift.
 * Also includes donations (donaciones) as a special type.
 */
class TurnoGastoCajaChica extends Base {

  public $id = "";
  public $id_turnos = "";
  public $descripcion = "";
  public $monto = 0;
  public $tipo = "";  // Gasto, Donación
  public $categoria = "";  // Category of expense
  public $numero_documento = "";  // Receipt/invoice number
  public $fecha_documento = "";
  public $proveedor = "";
  public $observaciones = "";
  public $estado = "activo";  // activo, eliminado
  public $creada = "";
  public $actualizada = "";
  public $creado_por = "";

  function __construct($id = null) {
    $this->tableName("turnos_gastos_caja_chica");
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
      $turno->recalculateTotalGastosCajaChica();
      if($this->tipo == 'Donación') {
        $turno->recalculateTotalDonaciones();
      }
    }
  }

  /**
   * Check if this is a donation
   */
  public function isDonation() {
    return $this->tipo == 'Donación';
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
   * Get formatted document date
   */
  public function getFormattedFechaDocumento() {
    if($this->fecha_documento == "" || $this->fecha_documento == "0000-00-00") return "";
    return date('d-m-Y', strtotime($this->fecha_documento));
  }

  /**
   * Get tipo badge class
   */
  public function getTipoBadgeClass() {
    switch($this->tipo) {
      case 'Donación':
        return 'bg-info';
      case 'Gasto':
        return 'bg-warning text-dark';
      default:
        return 'bg-secondary';
    }
  }

  /**
   * Get all gastos for a turno
   */
  public static function getByTurno($id_turno) {
    return self::getAll("WHERE id_turnos='".$id_turno."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Get all donaciones for a turno
   */
  public static function getDonacionesByTurno($id_turno) {
    return self::getAll("WHERE id_turnos='".$id_turno."' AND tipo='Donación' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Get all gastos (excluding donations) for a turno
   */
  public static function getGastosByTurno($id_turno) {
    return self::getAll("WHERE id_turnos='".$id_turno."' AND (tipo!='Donación' OR tipo IS NULL) AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Get total amount for a turno
   */
  public static function getTotalByTurno($id_turno) {
    $gastos = self::getByTurno($id_turno);
    $total = 0;
    foreach($gastos as $g) {
      $total += floatval($g->monto);
    }
    return $total;
  }

  /**
   * Get total donations for a turno
   */
  public static function getTotalDonacionesByTurno($id_turno) {
    $donaciones = self::getDonacionesByTurno($id_turno);
    $total = 0;
    foreach($donaciones as $d) {
      $total += floatval($d->monto);
    }
    return $total;
  }

  /**
   * Get tipo options for dropdowns
   */
  public static function getTipos() {
    return array(
      'Gasto' => 'Gasto',
      'Donación' => 'Donación'
    );
  }

  /**
   * Get categoria options for dropdowns
   */
  public static function getCategorias() {
    return array(
      'Limpieza' => 'Limpieza',
      'Oficina' => 'Oficina',
      'Mantenimiento' => 'Mantenimiento',
      'Transporte' => 'Transporte',
      'Alimentación' => 'Alimentación',
      'Otros' => 'Otros'
    );
  }
}
