<?php
/**
 * RecetaPaso - Paso de instrucción de receta
 * Representa un paso individual en la producción de una receta
 */
class RecetaPaso extends Base {

  public $id_recetas = "";
  public $etapa = "preparacion";
  public $orden = 1;
  public $titulo = "";
  public $instruccion = "";
  public $duracion_minutos = 0;
  public $temperatura_objetivo = null;
  public $ph_objetivo = null;
  public $densidad_objetivo = null;
  public $notas = "";
  public $creada = "";

  // Etapas disponibles
  public static $etapas = [
    'preparacion' => 'Preparación',
    'licor' => 'Licor de Maceración',
    'maceracion' => 'Maceración',
    'lavado' => 'Lavado',
    'coccion' => 'Cocción',
    'lupulizacion' => 'Lupulización',
    'enfriado' => 'Enfriado',
    'inoculacion' => 'Inoculación',
    'fermentacion' => 'Fermentación',
    'maduracion' => 'Maduración',
    'traspasos' => 'Traspasos',
    'envasado' => 'Envasado'
  ];

  public function __construct($id = null) {
    $this->tableName("recetas_pasos");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    } else {
      $this->creada = date('Y-m-d H:i:s');
    }
  }

  /**
   * Obtiene todos los pasos de una receta
   * @param string $id_recetas
   * @return array
   */
  public static function getByReceta($id_recetas) {
    return self::getAll("WHERE id_recetas='" . addslashes($id_recetas) . "'
                         AND estado='activo'
                         ORDER BY etapa ASC, orden ASC");
  }

  /**
   * Obtiene pasos por etapa de una receta
   * @param string $id_recetas
   * @param string $etapa
   * @return array
   */
  public static function getByRecetaEtapa($id_recetas, $etapa) {
    return self::getAll("WHERE id_recetas='" . addslashes($id_recetas) . "'
                         AND etapa='" . addslashes($etapa) . "'
                         AND estado='activo'
                         ORDER BY orden ASC");
  }

  /**
   * Obtiene el nombre legible de la etapa
   * @return string
   */
  public function getEtapaLabel() {
    return isset(self::$etapas[$this->etapa])
      ? self::$etapas[$this->etapa]
      : ucfirst($this->etapa);
  }

  /**
   * Formatea la duración para mostrar
   * @return string
   */
  public function getDuracionFormateada() {
    if(empty($this->duracion_minutos) || $this->duracion_minutos == 0) {
      return '-';
    }
    if($this->duracion_minutos >= 60) {
      $horas = floor($this->duracion_minutos / 60);
      $mins = $this->duracion_minutos % 60;
      if($mins > 0) {
        return $horas . 'h ' . $mins . 'min';
      }
      return $horas . 'h';
    }
    return $this->duracion_minutos . ' min';
  }

  /**
   * Obtiene los parámetros objetivo como array
   * @return array
   */
  public function getParametrosObjetivo() {
    $params = [];
    if(!empty($this->temperatura_objetivo)) {
      $params['Temperatura'] = $this->temperatura_objetivo . '°C';
    }
    if(!empty($this->ph_objetivo)) {
      $params['pH'] = $this->ph_objetivo;
    }
    if(!empty($this->densidad_objetivo)) {
      $params['Densidad'] = $this->densidad_objetivo;
    }
    return $params;
  }

  /**
   * Obtiene el siguiente número de orden para una etapa
   * @param string $id_recetas
   * @param string $etapa
   * @return int
   */
  public static function getSiguienteOrden($id_recetas, $etapa) {
    global $mysqli;
    $sql = "SELECT MAX(orden) as max_orden FROM recetas_pasos
            WHERE id_recetas='" . addslashes($id_recetas) . "'
            AND etapa='" . addslashes($etapa) . "'
            AND estado='activo'";
    $result = $mysqli->query($sql);
    if($result && $row = $result->fetch_assoc()) {
      return ($row['max_orden'] ?? 0) + 1;
    }
    return 1;
  }
}

?>
