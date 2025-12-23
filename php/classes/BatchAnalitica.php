<?php
/**
 * BatchAnalitica - Mediciones puntuales de control de calidad
 * Registra análisis QC en múltiples etapas del proceso
 */
class BatchAnalitica extends Base {

  public $id_batches = "";
  public $momento = "";
  public $densidad = null;
  public $densidad_unidad = "SG";
  public $ph = null;
  public $co2_disuelto = null;
  public $do_ppm = null;
  public $turbidez_ntu = null;
  public $color_ebc = null;
  public $amargor_ibu = null;
  public $timestamp = "";
  public $analista = "";
  public $observaciones = "";

  // Momentos de medición
  public static $momentos = [
    'PreMaceracion' => 'Pre-Maceración',
    'PreBoil' => 'Pre-Hervido',
    'PostBoil' => 'Post-Hervido',
    'PreFermentacion' => 'Pre-Fermentación',
    'MidFermentacion' => 'Mitad Fermentación',
    'PreEnvasado' => 'Pre-Envasado',
    'PostEnvasado' => 'Post-Envasado'
  ];

  public function __construct($id = null) {
    $this->tableName("batch_analiticas");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    } else {
      $this->timestamp = date('Y-m-d H:i:s');
    }
  }

  /**
   * Obtiene analíticas de un batch
   * @param string $id_batches
   * @return array
   */
  public static function getByBatch($id_batches) {
    return self::getAll("WHERE id_batches='" . addslashes($id_batches) . "'
                         ORDER BY timestamp ASC");
  }

  /**
   * Obtiene analítica por momento específico
   * @param string $id_batches
   * @param string $momento
   * @return BatchAnalitica|null
   */
  public static function getByMomento($id_batches, $momento) {
    $resultados = self::getAll("WHERE id_batches='" . addslashes($id_batches) . "'
                                AND momento='" . addslashes($momento) . "'
                                ORDER BY timestamp DESC LIMIT 1");
    return !empty($resultados) ? $resultados[0] : null;
  }

  /**
   * Obtiene el label del momento
   * @return string
   */
  public function getMomentoLabel() {
    return isset(self::$momentos[$this->momento])
      ? self::$momentos[$this->momento]
      : $this->momento;
  }

  /**
   * Convierte densidad entre unidades
   * @param string $unidad_destino SG, Plato, Brix
   * @return float|null
   */
  public function getDensidadEn($unidad_destino) {
    if(empty($this->densidad)) return null;

    // Si ya está en la unidad correcta
    if($this->densidad_unidad == $unidad_destino) {
      return $this->densidad;
    }

    // Convertir a SG primero
    $sg = $this->densidad;
    if($this->densidad_unidad == 'Plato') {
      $sg = 1 + ($this->densidad / (258.6 - (($this->densidad / 258.2) * 227.1)));
    } elseif($this->densidad_unidad == 'Brix') {
      $sg = 1 + ($this->densidad / (258.6 - (($this->densidad / 258.2) * 227.1)));
    }

    // Convertir a destino
    if($unidad_destino == 'SG') {
      return round($sg, 4);
    } elseif($unidad_destino == 'Plato') {
      return round((-1 * 616.868) + (1111.14 * $sg) - (630.272 * pow($sg, 2)) + (135.997 * pow($sg, 3)), 1);
    } elseif($unidad_destino == 'Brix') {
      return round((($sg - 1) / 0.004) * 0.95, 1);
    }

    return $this->densidad;
  }

  /**
   * Registra una medición rápidamente
   * @param string $id_batches
   * @param string $momento
   * @param array $datos [densidad, ph, etc.]
   * @return BatchAnalitica
   */
  public static function registrar($id_batches, $momento, $datos) {
    $analitica = new BatchAnalitica();
    $analitica->id_batches = $id_batches;
    $analitica->momento = $momento;
    $analitica->timestamp = date('Y-m-d H:i:s');

    if(isset($datos['densidad'])) $analitica->densidad = $datos['densidad'];
    if(isset($datos['densidad_unidad'])) $analitica->densidad_unidad = $datos['densidad_unidad'];
    if(isset($datos['ph'])) $analitica->ph = $datos['ph'];
    if(isset($datos['co2_disuelto'])) $analitica->co2_disuelto = $datos['co2_disuelto'];
    if(isset($datos['do_ppm'])) $analitica->do_ppm = $datos['do_ppm'];
    if(isset($datos['turbidez_ntu'])) $analitica->turbidez_ntu = $datos['turbidez_ntu'];
    if(isset($datos['color_ebc'])) $analitica->color_ebc = $datos['color_ebc'];
    if(isset($datos['amargor_ibu'])) $analitica->amargor_ibu = $datos['amargor_ibu'];
    if(isset($datos['analista'])) $analitica->analista = $datos['analista'];
    if(isset($datos['observaciones'])) $analitica->observaciones = $datos['observaciones'];

    $analitica->save();
    return $analitica;
  }

  /**
   * Obtiene resumen de todas las analíticas de un batch
   * @param string $id_batches
   * @return array
   */
  public static function getResumenBatch($id_batches) {
    $analiticas = self::getByBatch($id_batches);
    $resumen = [];

    foreach($analiticas as $a) {
      $resumen[$a->momento] = [
        'timestamp' => $a->timestamp,
        'densidad' => $a->densidad . ' ' . $a->densidad_unidad,
        'ph' => $a->ph,
        'co2' => $a->co2_disuelto,
        'do' => $a->do_ppm,
        'color' => $a->color_ebc,
        'ibu' => $a->amargor_ibu
      ];
    }

    return $resumen;
  }

  /**
   * Calcula atenuación entre dos momentos
   * @param string $id_batches
   * @param string $momento_inicial
   * @param string $momento_final
   * @return float|null Porcentaje de atenuación
   */
  public static function calcularAtenuacion($id_batches, $momento_inicial, $momento_final) {
    $og = self::getByMomento($id_batches, $momento_inicial);
    $fg = self::getByMomento($id_batches, $momento_final);

    if(!$og || !$fg || !$og->densidad || !$fg->densidad) {
      return null;
    }

    $og_sg = $og->getDensidadEn('SG');
    $fg_sg = $fg->getDensidadEn('SG');

    if($og_sg && $fg_sg && $og_sg > 1) {
      return round((($og_sg - $fg_sg) / ($og_sg - 1)) * 100, 1);
    }

    return null;
  }

  /**
   * Verifica si los valores están dentro de rangos aceptables
   * @param array $rangos [variable => [min, max]]
   * @return array Alertas encontradas
   */
  public function verificarRangos($rangos = []) {
    $alertas = [];

    $defaults = [
      'ph' => [3.0, 5.5],
      'do_ppm' => [0, 8],
      'turbidez_ntu' => [0, 1000]
    ];

    $rangos = array_merge($defaults, $rangos);

    foreach($rangos as $var => $rango) {
      if(!empty($this->$var)) {
        if($this->$var < $rango[0]) {
          $alertas[] = ucfirst($var) . " bajo el mínimo ({$this->$var} < {$rango[0]})";
        } elseif($this->$var > $rango[1]) {
          $alertas[] = ucfirst($var) . " sobre el máximo ({$this->$var} > {$rango[1]})";
        }
      }
    }

    return $alertas;
  }
}

?>
