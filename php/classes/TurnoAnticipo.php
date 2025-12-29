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
  public $numero_cuotas = 1;  // Number of installments
  public $monto_cuota = 0;  // Amount per installment
  public $mes_inicio = "";  // Starting month (YYYY-MM)
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

  // ==========================================
  // INSTALLMENT (CUOTA) METHODS
  // ==========================================

  /**
   * Get all cuotas for this anticipo
   */
  public function getCuotas() {
    if($this->id == "") return array();
    return TurnoAnticipoCuota::getByAnticipo($this->id);
  }

  /**
   * Get pending cuotas for this anticipo
   */
  public function getPendingCuotas() {
    if($this->id == "") return array();
    return TurnoAnticipoCuota::getPendingByAnticipo($this->id);
  }

  /**
   * Calculate and set monto_cuota based on monto and numero_cuotas
   */
  public function calculateMontoCuota() {
    if($this->numero_cuotas > 0) {
      $this->monto_cuota = round($this->monto / $this->numero_cuotas, 0);
    }
    return $this->monto_cuota;
  }

  /**
   * Generate cuotas based on numero_cuotas and mes_inicio
   * Should be called after save when creating a new anticipo
   */
  public function generateCuotas() {
    if($this->id == "" || $this->numero_cuotas < 1 || $this->mes_inicio == "") {
      return false;
    }

    // Delete existing cuotas first
    $existingCuotas = $this->getCuotas();
    foreach($existingCuotas as $cuota) {
      $cuota->delete();
    }

    // Calculate monto per cuota
    $montoPorCuota = round($this->monto / $this->numero_cuotas, 0);
    $montoRestante = $this->monto;

    // Generate cuotas for each month
    $mesActual = $this->mes_inicio;
    for($i = 0; $i < $this->numero_cuotas; $i++) {
      $cuota = new TurnoAnticipoCuota();
      $cuota->id_anticipos = $this->id;
      $cuota->mes = $mesActual;

      // Last cuota gets the remaining amount to handle rounding
      if($i == $this->numero_cuotas - 1) {
        $cuota->monto = $montoRestante;
      } else {
        $cuota->monto = $montoPorCuota;
        $montoRestante -= $montoPorCuota;
      }

      $cuota->estado = "pendiente";
      $cuota->save();

      // Move to next month
      $mesActual = date('Y-m', strtotime($mesActual . '-01 +1 month'));
    }

    // Update monto_cuota field
    $this->monto_cuota = $montoPorCuota;
    parent::save();

    return true;
  }

  /**
   * Get total paid amount from cuotas
   */
  public function getTotalPaid() {
    $cuotas = $this->getCuotas();
    $total = 0;
    foreach($cuotas as $cuota) {
      if($cuota->estado == "pagado") {
        $total += floatval($cuota->monto);
      }
    }
    return $total;
  }

  /**
   * Get total pending amount from cuotas
   */
  public function getTotalPending() {
    return $this->monto - $this->getTotalPaid();
  }

  /**
   * Get progress percentage
   */
  public function getProgressPercentage() {
    if($this->monto <= 0) return 0;
    return round(($this->getTotalPaid() / $this->monto) * 100, 0);
  }

  /**
   * Check if fully paid
   */
  public function isFullyPaid() {
    return $this->getTotalPending() <= 0;
  }

  /**
   * Get formatted monto_cuota
   */
  public function getFormattedMontoCuota() {
    return '$' . number_format($this->monto_cuota, 0, ',', '.');
  }
}
