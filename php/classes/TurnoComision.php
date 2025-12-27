<?php
/**
 * TurnoComision (Commission) Class
 *
 * Tracks commissions for attendants.
 * Commissions are pooled monthly and distributed among all attendants.
 *
 * Workflow:
 * 1. Individual commissions are registered per shift
 * 2. At month end, all commissions are pooled
 * 3. Pool is distributed among eligible attendants
 */
class TurnoComision extends Base {

  public $id = "";
  public $id_turnos = "";
  public $id_atendedores = "";  // Attendant who generated the commission
  public $descripcion = "";
  public $monto = 0;
  public $tipo = "";  // Venta, Servicio, Otro
  public $porcentaje = 0;  // Commission percentage
  public $base_calculo = 0;  // Base amount for calculation
  public $mes = "";  // Format: YYYY-MM
  public $anio = 0;
  public $estado = "pendiente";  // pendiente, distribuido, pagado, eliminado
  public $observaciones = "";
  public $creada = "";
  public $actualizada = "";
  public $creado_por = "";

  function __construct($id = null) {
    $this->tableName("turnos_comisiones");
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
   * Override save to update timestamps and set month/year
   */
  public function save() {
    $this->actualizada = date('Y-m-d H:i:s');
    if($this->id == "") {
      $this->creada = date('Y-m-d H:i:s');
      // Set month/year from turno date or current date
      if($this->id_turnos) {
        $turno = $this->getTurno();
        if($turno && $turno->fecha) {
          $this->mes = date('Y-m', strtotime($turno->fecha));
          $this->anio = date('Y', strtotime($turno->fecha));
        }
      }
      if(!$this->mes) {
        $this->mes = date('Y-m');
        $this->anio = date('Y');
      }
    }
    return parent::save();
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
   * Get the Atendedor who generated the commission
   */
  public function getAtendedor() {
    if($this->id_atendedores == "" || $this->id_atendedores == 0) {
      return null;
    }
    return new Atendedor($this->id_atendedores);
  }

  /**
   * Calculate commission from base and percentage
   */
  public function calculateFromPercentage() {
    if($this->base_calculo > 0 && $this->porcentaje > 0) {
      $this->monto = ($this->base_calculo * $this->porcentaje) / 100;
    }
    return $this->monto;
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
   * Check if can edit
   */
  public function canEdit() {
    return $this->estado == "pendiente";
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
      case 'distribuido':
        return 'bg-info';
      case 'pagado':
        return 'bg-success';
      default:
        return 'bg-secondary';
    }
  }

  /**
   * Get all comisiones for a turno
   */
  public static function getByTurno($id_turno) {
    return self::getAll("WHERE id_turnos='".$id_turno."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Get all comisiones for an atendedor
   */
  public static function getByAtendedor($id_atendedor) {
    return self::getAll("WHERE id_atendedores='".$id_atendedor."' AND estado!='eliminado' ORDER BY creada DESC");
  }

  /**
   * Get all comisiones for a specific month
   */
  public static function getByMonth($year, $month) {
    $mes = sprintf('%04d-%02d', $year, $month);
    return self::getAll("WHERE mes='".$mes."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Get pending comisiones for a specific month
   */
  public static function getPendingByMonth($year, $month) {
    $mes = sprintf('%04d-%02d', $year, $month);
    return self::getAll("WHERE mes='".$mes."' AND estado='pendiente' ORDER BY id ASC");
  }

  /**
   * Get total commission pool for a month
   */
  public static function getMonthlyPool($year, $month) {
    $comisiones = self::getByMonth($year, $month);
    $total = 0;
    foreach($comisiones as $c) {
      $total += floatval($c->monto);
    }
    return $total;
  }

  /**
   * Get total for an atendedor in a month
   */
  public static function getAtendedorMonthlyTotal($id_atendedor, $year, $month) {
    $mes = sprintf('%04d-%02d', $year, $month);
    $comisiones = self::getAll("WHERE id_atendedores='".$id_atendedor."' AND mes='".$mes."' AND estado!='eliminado'");
    $total = 0;
    foreach($comisiones as $c) {
      $total += floatval($c->monto);
    }
    return $total;
  }

  /**
   * Get tipo options for dropdowns
   */
  public static function getTipos() {
    return array(
      'Venta' => 'Venta',
      'Servicio' => 'Servicio',
      'Otro' => 'Otro'
    );
  }

  // ==========================================
  // MONTHLY DISTRIBUTION METHODS
  // ==========================================

  /**
   * Get all eligible attendants for commission distribution
   * (attendants who worked during the month)
   */
  public static function getEligibleAtendedores($year, $month) {
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));

    // Get unique attendants from turnos in this month
    $turnos = Turno::getByDateRange($startDate, $endDate);
    $atendedores = array();
    $atendedorIds = array();

    foreach($turnos as $turno) {
      if($turno->id_atendedores && !in_array($turno->id_atendedores, $atendedorIds)) {
        $atendedorIds[] = $turno->id_atendedores;
        $atendedores[] = new Atendedor($turno->id_atendedores);
      }
    }

    return $atendedores;
  }

  /**
   * Calculate distribution for a month
   * Returns array with each attendant's share
   */
  public static function calculateDistribution($year, $month) {
    $pool = self::getMonthlyPool($year, $month);
    $atendedores = self::getEligibleAtendedores($year, $month);

    if(count($atendedores) == 0 || $pool == 0) {
      return array(
        'pool' => $pool,
        'atendedores_count' => 0,
        'share_per_atendedor' => 0,
        'distribution' => array()
      );
    }

    $sharePerAtendedor = $pool / count($atendedores);
    $distribution = array();

    foreach($atendedores as $atendedor) {
      $distribution[] = array(
        'id_atendedores' => $atendedor->id,
        'nombre' => $atendedor->nombre_completo,
        'share' => $sharePerAtendedor
      );
    }

    return array(
      'pool' => $pool,
      'atendedores_count' => count($atendedores),
      'share_per_atendedor' => $sharePerAtendedor,
      'distribution' => $distribution
    );
  }

  /**
   * Mark all pending commissions for a month as distributed
   */
  public static function markMonthAsDistributed($year, $month) {
    $pendientes = self::getPendingByMonth($year, $month);
    foreach($pendientes as $comision) {
      $comision->estado = 'distribuido';
      $comision->save();
    }
    return count($pendientes);
  }

  /**
   * Mark all distributed commissions for a month as paid
   */
  public static function markMonthAsPaid($year, $month) {
    $mes = sprintf('%04d-%02d', $year, $month);
    $distribuidos = self::getAll("WHERE mes='".$mes."' AND estado='distribuido'");
    foreach($distribuidos as $comision) {
      $comision->estado = 'pagado';
      $comision->save();
    }
    return count($distribuidos);
  }

  /**
   * Get monthly summary report
   */
  public static function getMonthlySummary($year, $month) {
    $pool = self::getMonthlyPool($year, $month);
    $distribution = self::calculateDistribution($year, $month);
    $pendientes = self::getPendingByMonth($year, $month);

    return array(
      'year' => $year,
      'month' => $month,
      'total_pool' => $pool,
      'total_comisiones' => count(self::getByMonth($year, $month)),
      'pendientes_count' => count($pendientes),
      'distribution' => $distribution
    );
  }
}
