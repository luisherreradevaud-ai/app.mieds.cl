<?php
/**
 * BatchSignal - Señales de proceso para ML/PLC
 * Captura datos de series temporales desde sensores
 */
class BatchSignal extends Base {

  public $id_batches = "";
  public $etapa = "";
  public $variable = "";
  public $timestamp = "";
  public $valor = 0;
  public $unidad = "";
  public $sensor_id = "";

  // Etapas disponibles
  public static $etapas = [
    'Maceracion' => 'Maceración',
    'Lavado' => 'Lavado',
    'Coccion' => 'Cocción',
    'Enfriado' => 'Enfriado',
    'Fermentacion' => 'Fermentación',
    'Maduracion' => 'Maduración',
    'Envasado' => 'Envasado',
    'CIP' => 'Limpieza CIP'
  ];

  // Variables comunes
  public static $variables = [
    'temperatura' => '°C',
    'presion' => 'bar',
    'ph' => '',
    'densidad' => 'SG',
    'caudal' => 'L/min',
    'nivel' => '%',
    'do_ppm' => 'ppm',
    'co2' => 'g/L',
    'conductividad' => 'mS/cm'
  ];

  public function __construct($id = null) {
    $this->tableName("batch_signals");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    } else {
      $this->timestamp = date('Y-m-d H:i:s');
    }
  }

  /**
   * Obtiene señales de un batch
   * @param string $id_batches
   * @param string $etapa Opcional
   * @param string $variable Opcional
   * @return array
   */
  public static function getByBatch($id_batches, $etapa = null, $variable = null) {
    $where = "WHERE id_batches='" . addslashes($id_batches) . "'";
    if($etapa) {
      $where .= " AND etapa='" . addslashes($etapa) . "'";
    }
    if($variable) {
      $where .= " AND variable='" . addslashes($variable) . "'";
    }
    $where .= " ORDER BY timestamp ASC";
    return self::getAll($where);
  }

  /**
   * Obtiene señales en un rango de tiempo
   * @param string $id_batches
   * @param string $desde Fecha inicio (Y-m-d H:i:s)
   * @param string $hasta Fecha fin
   * @return array
   */
  public static function getByRango($id_batches, $desde, $hasta) {
    return self::getAll("WHERE id_batches='" . addslashes($id_batches) . "'
                         AND timestamp BETWEEN '" . addslashes($desde) . "'
                         AND '" . addslashes($hasta) . "'
                         ORDER BY timestamp ASC");
  }

  /**
   * Registra una señal rápidamente
   * @param string $id_batches
   * @param string $etapa
   * @param string $variable
   * @param float $valor
   * @param string $unidad
   * @param string $sensor_id
   * @return BatchSignal
   */
  public static function registrar($id_batches, $etapa, $variable, $valor, $unidad = '', $sensor_id = '') {
    $signal = new BatchSignal();
    $signal->id_batches = $id_batches;
    $signal->etapa = $etapa;
    $signal->variable = $variable;
    $signal->valor = $valor;
    $signal->unidad = $unidad;
    $signal->sensor_id = $sensor_id;
    $signal->timestamp = date('Y-m-d H:i:s');
    $signal->save();
    return $signal;
  }

  /**
   * Obtiene estadísticas de una variable
   * @param string $id_batches
   * @param string $variable
   * @return array [min, max, avg, count]
   */
  public static function getEstadisticas($id_batches, $variable) {
    global $mysqli;
    $sql = "SELECT
              MIN(valor) as min_valor,
              MAX(valor) as max_valor,
              AVG(valor) as avg_valor,
              COUNT(*) as total
            FROM batch_signals
            WHERE id_batches='" . addslashes($id_batches) . "'
            AND variable='" . addslashes($variable) . "'";
    $result = $mysqli->query($sql);
    if($result && $row = $result->fetch_assoc()) {
      return $row;
    }
    return ['min_valor' => null, 'max_valor' => null, 'avg_valor' => null, 'total' => 0];
  }

  /**
   * Obtiene datos para gráfico (serie temporal)
   * @param string $id_batches
   * @param string $variable
   * @return array [[timestamp, valor], ...]
   */
  public static function getSerieParaGrafico($id_batches, $variable) {
    global $mysqli;
    $sql = "SELECT timestamp, valor
            FROM batch_signals
            WHERE id_batches='" . addslashes($id_batches) . "'
            AND variable='" . addslashes($variable) . "'
            ORDER BY timestamp ASC";
    $result = $mysqli->query($sql);
    $data = [];
    if($result) {
      while($row = $result->fetch_assoc()) {
        $data[] = [
          'x' => strtotime($row['timestamp']) * 1000,
          'y' => floatval($row['valor'])
        ];
      }
    }
    return $data;
  }
}

?>
