<?php
/**
 * BatchLevadura - Datos específicos de uso de levadura
 * Extiende la información de BatchInsumo para levaduras
 */
class BatchLevadura extends Base {

  public $id_batches = "";
  public $id_batches_insumos = null;
  public $generacion = 1;
  public $origen_batch = "";
  public $cantidad_gramos = 0;
  public $tasa_inoculacion = 0;
  public $viabilidad_medida = 0;
  public $vitalidad_medida = 0;
  public $uso_starter = 0;
  public $volumen_starter_ml = 0;
  public $atenuacion_real = 0;
  public $tiempo_lag_h = 0;
  public $observaciones = "";
  public $timestamp = "";

  public function __construct($id = null) {
    $this->tableName("batch_levaduras");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    } else {
      $this->timestamp = date('Y-m-d H:i:s');
    }
  }

  /**
   * Obtiene datos de levadura de un batch
   * @param string $id_batches
   * @return array
   */
  public static function getByBatch($id_batches) {
    return self::getAll("WHERE id_batches='" . addslashes($id_batches) . "'
                         ORDER BY timestamp DESC");
  }

  /**
   * Obtiene el primer registro de levadura de un batch
   * @param string $id_batches
   * @return BatchLevadura|null
   */
  public static function getPrincipalByBatch($id_batches) {
    $resultados = self::getAll("WHERE id_batches='" . addslashes($id_batches) . "'
                                ORDER BY timestamp ASC LIMIT 1");
    return !empty($resultados) ? $resultados[0] : null;
  }

  /**
   * Obtiene el historial de generaciones de una levadura
   * @param string $id_origen Batch de origen inicial
   * @return array
   */
  public static function getHistorialGeneraciones($id_origen) {
    $historial = [];
    $current = $id_origen;
    $maxIterations = 20; // Prevenir loops infinitos

    while($current && $maxIterations > 0) {
      $lev = self::getAll("WHERE origen_batch='" . addslashes($current) . "'");
      if(!empty($lev)) {
        $historial[] = $lev[0];
        $current = $lev[0]->id_batches;
      } else {
        break;
      }
      $maxIterations--;
    }

    return $historial;
  }

  /**
   * Verifica si usó starter
   * @return bool
   */
  public function usoStarter() {
    return $this->uso_starter == 1;
  }

  /**
   * Obtiene el batch de origen de la levadura reutilizada
   * @return Batch|null
   */
  public function getBatchOrigen() {
    if(!empty($this->origen_batch)) {
      return new Batch($this->origen_batch);
    }
    return null;
  }

  /**
   * Obtiene el insumo (levadura) asociado
   * @return BatchInsumo|null
   */
  public function getBatchInsumo() {
    if(!empty($this->id_batches_insumos)) {
      return new BatchInsumo($this->id_batches_insumos);
    }
    return null;
  }

  /**
   * Calcula la tasa de inoculación recomendada
   * @param float $volumen_litros
   * @param float $og Densidad original
   * @param string $tipo 'ale' o 'lager'
   * @return float Células necesarias (billones)
   */
  public static function calcularTasaRecomendada($volumen_litros, $og, $tipo = 'ale') {
    // Convertir OG a Plato si es SG
    if($og > 2) { // Es SG
      $plato = ($og - 1) * 1000 / 4;
    } else {
      $plato = $og;
    }

    $tasa = ($tipo == 'lager') ? 1.5 : 0.75; // millones/ml/°P
    $celulas = $volumen_litros * 1000 * $plato * $tasa;
    return $celulas / 1000000000000; // billones
  }

  /**
   * Formatea los datos para resumen
   * @return array
   */
  public function getResumen() {
    return [
      'generacion' => $this->generacion,
      'cantidad' => $this->cantidad_gramos . 'g',
      'tasa_inoculacion' => $this->tasa_inoculacion,
      'viabilidad' => $this->viabilidad_medida . '%',
      'uso_starter' => $this->usoStarter() ? 'Sí' : 'No',
      'atenuacion_real' => $this->atenuacion_real . '%',
      'tiempo_lag' => $this->tiempo_lag_h . 'h'
    ];
  }
}

?>
