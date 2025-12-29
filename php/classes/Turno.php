<?php
/**
 * Turno (Cierre de Turno) Class
 *
 * Main class for managing shift closings with cash counting,
 * workflow states, and related financial records.
 *
 * Workflow: Abierto → Cerrado (Admin) → Aprobado (Concesionario)
 */
class Turno extends Base {

  // Basic info
  public $id = "";
  public $fecha = "";

  // Cash counting - Bills (Billetes)
  public $billetes_20000 = 0;
  public $billetes_10000 = 0;
  public $billetes_5000 = 0;
  public $billetes_2000 = 0;
  public $billetes_1000 = 0;

  // Cash counting - Coins (Monedas)
  public $monedas_500 = 0;
  public $monedas_100 = 0;
  public $monedas_50 = 0;
  public $monedas_10 = 0;

  // Calculated totals (stored for quick access)
  public $total_billetes = 0;
  public $total_monedas = 0;
  public $total_efectivo = 0;

  // Related totals (calculated from related records)
  public $total_faltantes = 0;
  public $total_anticipos = 0;
  public $total_facturas_credito = 0;
  public $total_ingresos_prosegur = 0;
  public $total_gastos_caja_chica = 0;
  public $total_donaciones = 0;

  // Workflow state
  public $estado = "Abierto";  // Abierto, Cerrado, Aprobado
  public $cerrado_por = "";     // id_usuarios who closed
  public $cerrado_fecha = "";   // datetime when closed
  public $aprobado_por = "";    // id_usuarios who approved
  public $aprobado_fecha = "";  // datetime when approved

  // Notes and observations
  public $observaciones = "";
  public $observaciones_cierre = "";
  public $observaciones_aprobacion = "";

  // Audit fields
  public $creada = "";
  public $actualizada = "";
  public $creado_por = "";

  function __construct($id = null) {
    $this->tableName("turnos");
    if($id) {
      $this->id = $id;
      $this->getFromDB();
    }
  }

  /**
   * Get shift from database and load it
   */
  public function getFromDB() {
    $info = $this->getInfoDatabase('id');
    if($info) {
      $this->setProperties($info);
    }
  }

  /**
   * Override save to calculate totals before saving
   */
  public function save() {
    // Convertir strings vacíos a NULL para campos INT que no aceptan ''
    if($this->cerrado_por === '') $this->cerrado_por = null;
    if($this->aprobado_por === '') $this->aprobado_por = null;

    $this->calculateTotals();
    $this->actualizada = date('Y-m-d H:i:s');
    if($this->id == "") {
      $this->creada = date('Y-m-d H:i:s');
    }
    return parent::save();
  }

  // ==========================================
  // CASH COUNTING METHODS
  // ==========================================

  /**
   * Calculate total from bills
   */
  public function calculateBilletes() {
    $this->total_billetes =
      ($this->billetes_20000 * 20000) +
      ($this->billetes_10000 * 10000) +
      ($this->billetes_5000 * 5000) +
      ($this->billetes_2000 * 2000) +
      ($this->billetes_1000 * 1000);
    return $this->total_billetes;
  }

  /**
   * Calculate total from coins
   */
  public function calculateMonedas() {
    $this->total_monedas =
      ($this->monedas_500 * 500) +
      ($this->monedas_100 * 100) +
      ($this->monedas_50 * 50) +
      ($this->monedas_10 * 10);
    return $this->total_monedas;
  }

  /**
   * Calculate total cash (bills + coins)
   */
  public function calculateEfectivo() {
    $this->calculateBilletes();
    $this->calculateMonedas();
    $this->total_efectivo = $this->total_billetes + $this->total_monedas;
    return $this->total_efectivo;
  }

  /**
   * Set cash counts from array (from form submission)
   */
  public function setCashCounts($data) {
    // Bills
    if(isset($data['billetes_20000'])) $this->billetes_20000 = intval($data['billetes_20000']);
    if(isset($data['billetes_10000'])) $this->billetes_10000 = intval($data['billetes_10000']);
    if(isset($data['billetes_5000'])) $this->billetes_5000 = intval($data['billetes_5000']);
    if(isset($data['billetes_2000'])) $this->billetes_2000 = intval($data['billetes_2000']);
    if(isset($data['billetes_1000'])) $this->billetes_1000 = intval($data['billetes_1000']);

    // Coins
    if(isset($data['monedas_500'])) $this->monedas_500 = intval($data['monedas_500']);
    if(isset($data['monedas_100'])) $this->monedas_100 = intval($data['monedas_100']);
    if(isset($data['monedas_50'])) $this->monedas_50 = intval($data['monedas_50']);
    if(isset($data['monedas_10'])) $this->monedas_10 = intval($data['monedas_10']);

    $this->calculateEfectivo();
  }

  /**
   * Get cash breakdown as array
   */
  public function getCashBreakdown() {
    return array(
      'billetes' => array(
        '20000' => array('cantidad' => $this->billetes_20000, 'total' => $this->billetes_20000 * 20000),
        '10000' => array('cantidad' => $this->billetes_10000, 'total' => $this->billetes_10000 * 10000),
        '5000' => array('cantidad' => $this->billetes_5000, 'total' => $this->billetes_5000 * 5000),
        '2000' => array('cantidad' => $this->billetes_2000, 'total' => $this->billetes_2000 * 2000),
        '1000' => array('cantidad' => $this->billetes_1000, 'total' => $this->billetes_1000 * 1000),
      ),
      'monedas' => array(
        '500' => array('cantidad' => $this->monedas_500, 'total' => $this->monedas_500 * 500),
        '100' => array('cantidad' => $this->monedas_100, 'total' => $this->monedas_100 * 100),
        '50' => array('cantidad' => $this->monedas_50, 'total' => $this->monedas_50 * 50),
        '10' => array('cantidad' => $this->monedas_10, 'total' => $this->monedas_10 * 10),
      ),
      'total_billetes' => $this->total_billetes,
      'total_monedas' => $this->total_monedas,
      'total_efectivo' => $this->total_efectivo
    );
  }

  // ==========================================
  // RELATED RECORDS METHODS
  // ==========================================

  /**
   * Get all Faltantes (shortages) for this shift
   */
  public function getFaltantes() {
    if($this->id == "") return array();
    return TurnoFaltante::getAll("WHERE id_turnos='".$this->id."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Add a Faltante to this shift
   */
  public function addFaltante($data) {
    if($this->id == "") return false;

    $faltante = new TurnoFaltante();
    $faltante->id_turnos = $this->id;
    $faltante->setProperties($data);
    $faltante->save();

    $this->recalculateTotalFaltantes();
    return $faltante;
  }

  /**
   * Recalculate total faltantes
   */
  public function recalculateTotalFaltantes() {
    $faltantes = $this->getFaltantes();
    $total = 0;
    foreach($faltantes as $f) {
      $total += floatval($f->monto);
    }
    $this->total_faltantes = $total;
    $this->save();
    return $total;
  }

  /**
   * Get all Anticipos (advances) for this shift
   */
  public function getAnticipos() {
    if($this->id == "") return array();
    return TurnoAnticipo::getAll("WHERE id_turnos='".$this->id."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Add an Anticipo to this shift
   */
  public function addAnticipo($data) {
    if($this->id == "") return false;

    $anticipo = new TurnoAnticipo();
    $anticipo->id_turnos = $this->id;
    $anticipo->setProperties($data);
    $anticipo->save();

    $this->recalculateTotalAnticipos();
    return $anticipo;
  }

  /**
   * Recalculate total anticipos
   */
  public function recalculateTotalAnticipos() {
    $anticipos = $this->getAnticipos();
    $total = 0;
    foreach($anticipos as $a) {
      $total += floatval($a->monto);
    }
    $this->total_anticipos = $total;
    $this->save();
    return $total;
  }

  /**
   * Get all Facturas a Crédito for this shift
   */
  public function getFacturasCredito() {
    if($this->id == "") return array();
    return TurnoFacturaCredito::getAll("WHERE id_turnos='".$this->id."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Add a Factura a Crédito to this shift
   */
  public function addFacturaCredito($data) {
    if($this->id == "") return false;

    $factura = new TurnoFacturaCredito();
    $factura->id_turnos = $this->id;
    $factura->setProperties($data);
    $factura->save();

    $this->recalculateTotalFacturasCredito();
    return $factura;
  }

  /**
   * Recalculate total facturas a credito
   */
  public function recalculateTotalFacturasCredito() {
    $facturas = $this->getFacturasCredito();
    $total = 0;
    foreach($facturas as $f) {
      $total += floatval($f->monto);
    }
    $this->total_facturas_credito = $total;
    $this->save();
    return $total;
  }

  /**
   * Get all Ingresos PROSEGUR MAE for this shift
   */
  public function getIngresosProsegur() {
    if($this->id == "") return array();
    return TurnoIngresoProsegur::getAll("WHERE id_turnos='".$this->id."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Add an Ingreso PROSEGUR to this shift
   */
  public function addIngresoProsegur($data) {
    if($this->id == "") return false;

    $ingreso = new TurnoIngresoProsegur();
    $ingreso->id_turnos = $this->id;
    $ingreso->setProperties($data);
    $ingreso->save();

    $this->recalculateTotalIngresosProsegur();
    return $ingreso;
  }

  /**
   * Recalculate total ingresos prosegur
   */
  public function recalculateTotalIngresosProsegur() {
    $ingresos = $this->getIngresosProsegur();
    $total = 0;
    foreach($ingresos as $i) {
      $total += floatval($i->monto);
    }
    $this->total_ingresos_prosegur = $total;
    $this->save();
    return $total;
  }

  /**
   * Get all Gastos Caja Chica for this shift
   */
  public function getGastosCajaChica() {
    if($this->id == "") return array();
    return TurnoGastoCajaChica::getAll("WHERE id_turnos='".$this->id."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Add a Gasto Caja Chica to this shift
   */
  public function addGastoCajaChica($data) {
    if($this->id == "") return false;

    $gasto = new TurnoGastoCajaChica();
    $gasto->id_turnos = $this->id;
    $gasto->setProperties($data);
    $gasto->save();

    $this->recalculateTotalGastosCajaChica();
    return $gasto;
  }

  /**
   * Recalculate total gastos caja chica
   */
  public function recalculateTotalGastosCajaChica() {
    $gastos = $this->getGastosCajaChica();
    $total = 0;
    foreach($gastos as $g) {
      $total += floatval($g->monto);
    }
    $this->total_gastos_caja_chica = $total;
    $this->save();
    return $total;
  }

  /**
   * Get all Donaciones for this shift
   */
  public function getDonaciones() {
    if($this->id == "") return array();
    return TurnoDonacion::getAll("WHERE id_turnos='".$this->id."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Add a Donacion to this shift
   */
  public function addDonacion($data) {
    if($this->id == "") return false;

    $donacion = new TurnoDonacion();
    $donacion->id_turnos = $this->id;
    $donacion->setProperties($data);
    $donacion->save();

    $this->recalculateTotalDonaciones();
    return $donacion;
  }

  /**
   * Recalculate total donaciones
   */
  public function recalculateTotalDonaciones() {
    $donaciones = $this->getDonaciones();
    $total = 0;
    foreach($donaciones as $d) {
      $total += floatval($d->monto);
    }
    $this->total_donaciones = $total;
    $this->save();
    return $total;
  }

  /**
   * Get all Comisiones for this shift
   */
  public function getComisiones() {
    if($this->id == "") return array();
    return TurnoComision::getAll("WHERE id_turnos='".$this->id."' AND estado!='eliminado' ORDER BY id ASC");
  }

  /**
   * Calculate all totals from related records
   */
  public function calculateTotals() {
    $this->calculateEfectivo();

    // Calculate from related records without triggering recursive saves
    $faltantes = $this->getFaltantes();
    $this->total_faltantes = 0;
    foreach($faltantes as $f) {
      $this->total_faltantes += floatval($f->monto);
    }

    $anticipos = $this->getAnticipos();
    $this->total_anticipos = 0;
    foreach($anticipos as $a) {
      $this->total_anticipos += floatval($a->monto);
    }

    $facturas = $this->getFacturasCredito();
    $this->total_facturas_credito = 0;
    foreach($facturas as $f) {
      $this->total_facturas_credito += floatval($f->monto);
    }

    $ingresos = $this->getIngresosProsegur();
    $this->total_ingresos_prosegur = 0;
    foreach($ingresos as $i) {
      $this->total_ingresos_prosegur += floatval($i->monto);
    }

    $gastos = $this->getGastosCajaChica();
    $this->total_gastos_caja_chica = 0;
    foreach($gastos as $g) {
      $this->total_gastos_caja_chica += floatval($g->monto);
    }

    $donaciones = $this->getDonaciones();
    $this->total_donaciones = 0;
    foreach($donaciones as $d) {
      $this->total_donaciones += floatval($d->monto);
    }
  }

  /**
   * Get complete summary of the shift
   */
  public function getSummary() {
    $this->calculateTotals();

    return array(
      'turno' => array(
        'id' => $this->id,
        'fecha' => $this->fecha,
        'estado' => $this->estado
      ),
      'efectivo' => $this->getCashBreakdown(),
      'totales' => array(
        'total_efectivo' => $this->total_efectivo,
        'total_faltantes' => $this->total_faltantes,
        'total_anticipos' => $this->total_anticipos,
        'total_facturas_credito' => $this->total_facturas_credito,
        'total_ingresos_prosegur' => $this->total_ingresos_prosegur,
        'total_gastos_caja_chica' => $this->total_gastos_caja_chica,
        'total_donaciones' => $this->total_donaciones
      ),
      'items' => array(
        'faltantes' => $this->getFaltantes(),
        'anticipos' => $this->getAnticipos(),
        'facturas_credito' => $this->getFacturasCredito(),
        'ingresos_prosegur' => $this->getIngresosProsegur(),
        'gastos_caja_chica' => $this->getGastosCajaChica(),
        'donaciones' => $this->getDonaciones(),
        'comisiones' => $this->getComisiones()
      )
    );
  }

  // ==========================================
  // WORKFLOW STATE METHODS
  // ==========================================

  /**
   * Check if shift can be edited
   */
  public function canEdit() {
    return $this->estado == "Abierto";
  }

  /**
   * Check if shift can be closed
   */
  public function canClose() {
    return $this->estado == "Abierto";
  }

  /**
   * Check if shift can be approved
   */
  public function canApprove() {
    return $this->estado == "Cerrado";
  }

  /**
   * Check if shift can be reopened
   */
  public function canReopen() {
    return $this->estado == "Cerrado";
  }

  /**
   * Close the shift (Admin action)
   */
  public function close($userId, $observaciones = "") {
    if(!$this->canClose()) {
      return array('status' => 'ERROR', 'mensaje' => 'El turno no puede ser cerrado en su estado actual');
    }

    $this->estado = "Cerrado";
    $this->cerrado_por = $userId;
    $this->cerrado_fecha = date('Y-m-d H:i:s');
    $this->observaciones_cierre = $observaciones;
    $this->save();

    return array('status' => 'OK', 'mensaje' => 'Turno cerrado exitosamente');
  }

  /**
   * Approve the shift (Concesionario action)
   */
  public function approve($userId, $observaciones = "") {
    if(!$this->canApprove()) {
      return array('status' => 'ERROR', 'mensaje' => 'El turno no puede ser aprobado en su estado actual');
    }

    $this->estado = "Aprobado";
    $this->aprobado_por = $userId;
    $this->aprobado_fecha = date('Y-m-d H:i:s');
    $this->observaciones_aprobacion = $observaciones;
    $this->save();

    return array('status' => 'OK', 'mensaje' => 'Turno aprobado exitosamente');
  }

  /**
   * Reopen a closed shift (Admin action)
   */
  public function reopen($userId, $observaciones = "") {
    if(!$this->canReopen()) {
      return array('status' => 'ERROR', 'mensaje' => 'El turno no puede ser reabierto en su estado actual');
    }

    $this->estado = "Abierto";
    $this->cerrado_por = "";
    $this->cerrado_fecha = "";
    $this->observaciones_cierre = "";
    $this->observaciones = $this->observaciones . "\n[Reabierto por usuario " . $userId . " el " . date('Y-m-d H:i:s') . "] " . $observaciones;
    $this->save();

    return array('status' => 'OK', 'mensaje' => 'Turno reabierto exitosamente');
  }

  /**
   * Get state badge class for UI
   */
  public function getStateBadgeClass() {
    switch($this->estado) {
      case 'Abierto':
        return 'bg-warning text-dark';
      case 'Cerrado':
        return 'bg-info';
      case 'Aprobado':
        return 'bg-success';
      default:
        return 'bg-secondary';
    }
  }

  /**
   * Get state icon for UI
   */
  public function getStateIcon() {
    switch($this->estado) {
      case 'Abierto':
        return 'fa-clock';
      case 'Cerrado':
        return 'fa-lock';
      case 'Aprobado':
        return 'fa-check-circle';
      default:
        return 'fa-question-circle';
    }
  }

  // ==========================================
  // STATIC QUERY METHODS
  // ==========================================

  /**
   * Get shifts by date range
   */
  public static function getByDateRange($startDate, $endDate) {
    return self::getAll("WHERE fecha >= '".$startDate."' AND fecha <= '".$endDate."' ORDER BY fecha DESC");
  }

  /**
   * Get shifts by state
   */
  public static function getByEstado($estado) {
    return self::getAll("WHERE estado='".$estado."' ORDER BY fecha DESC");
  }

  /**
   * Get open shifts
   */
  public static function getAbiertos() {
    return self::getByEstado('Abierto');
  }

  /**
   * Get closed (pending approval) shifts
   */
  public static function getCerrados() {
    return self::getByEstado('Cerrado');
  }

  /**
   * Get approved shifts
   */
  public static function getAprobados() {
    return self::getByEstado('Aprobado');
  }

  /**
   * Get shifts for current month
   */
  public static function getCurrentMonth() {
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-t');
    return self::getByDateRange($startDate, $endDate);
  }

  /**
   * Get shifts for specific month/year
   */
  public static function getByMonth($year, $month) {
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));
    return self::getByDateRange($startDate, $endDate);
  }

  // ==========================================
  // SOFT DELETE
  // ==========================================

  /**
   * Soft delete the shift
   */
  public function softDelete() {
    $this->estado = "eliminado";
    $this->save();
  }

  /**
   * Check if shift is deleted
   */
  public function isDeleted() {
    return $this->estado == "eliminado";
  }

  // ==========================================
  // FORMATTING HELPERS
  // ==========================================

  /**
   * Format amount as Chilean peso
   */
  public static function formatMoney($amount) {
    return '$' . number_format($amount, 0, ',', '.');
  }

  /**
   * Get formatted date
   */
  public function getFormattedDate() {
    if($this->fecha == "" || $this->fecha == "0000-00-00") return "";
    return date('d-m-Y', strtotime($this->fecha));
  }
}
