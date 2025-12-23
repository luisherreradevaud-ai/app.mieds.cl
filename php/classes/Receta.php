<?php

  class Receta extends Base {

    public $nombre = "";
    public $codigo = "";
    public $clasificacion = "";
    public $observaciones = "";
    public $litros = 0;
    public $creada = "";
    public $insumos_arr;

    // Campos objetivo
    public $abv_objetivo = null;
    public $ibu_objetivo = null;
    public $color_ebc_objetivo = null;
    public $og_objetivo = null;
    public $fg_objetivo = null;
    public $tiempo_fermentacion_dias = null;
    public $tiempo_maduracion_dias = null;
    public $instrucciones_generales = "";

    public function __construct($id = null) {
      $this->tableName("recetas");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
        $this->insumos_arr = RecetaInsumo::getAll("WHERE id_recetas='".$this->id."'");
      }
    }

    public function setSpecifics($post) {

      $this->save();

      $recetas_insumos = RecetaInsumo::getAll("WHERE id_recetas='".$this->id."'");
      foreach($recetas_insumos as $ri) {
        $ri->delete();
      }

      if(isset($post['insumos'])) {

        foreach($post['insumos'] as $insumo) {

          $ri = new RecetaInsumo;
          $ri->setPropertiesNoId($insumo);
          $ri->id_insumos = $insumo['id'];
          $ri->id_recetas = $this->id;
          $ri->save();

        }

      }
    }

    /**
     * Obtiene todos los pasos de la receta
     * @return array
     */
    public function getPasos() {
      return RecetaPaso::getByReceta($this->id);
    }

    /**
     * Obtiene los pasos agrupados por etapa
     * @return array [etapa => [pasos]]
     */
    public function getPasosAgrupados() {
      $pasos = $this->getPasos();
      $agrupados = [];
      foreach($pasos as $paso) {
        if(!isset($agrupados[$paso->etapa])) {
          $agrupados[$paso->etapa] = [];
        }
        $agrupados[$paso->etapa][] = $paso;
      }
      return $agrupados;
    }

    /**
     * Obtiene los insumos agrupados por etapa
     * @return array [etapa => [insumos]]
     */
    public function getInsumosPorEtapa() {
      global $mysqli;
      $sql = "SELECT ri.*, i.nombre as insumo_nombre, i.unidad_de_medida
              FROM recetas_insumos ri
              INNER JOIN insumos i ON ri.id_insumos = i.id
              WHERE ri.id_recetas = '" . addslashes($this->id) . "'
              ORDER BY ri.etapa ASC, ri.orden ASC";
      $result = $mysqli->query($sql);
      $agrupados = [];
      if($result) {
        while($row = $result->fetch_assoc()) {
          $etapa = $row['etapa'] ?? 'general';
          if(!isset($agrupados[$etapa])) {
            $agrupados[$etapa] = [];
          }
          $agrupados[$etapa][] = $row;
        }
      }
      return $agrupados;
    }

    /**
     * Verifica si la receta tiene instrucciones (pasos)
     * @return bool
     */
    public function tieneInstrucciones() {
      $pasos = $this->getPasos();
      return count($pasos) > 0;
    }

    /**
     * Obtiene los parámetros objetivo como array
     * @return array
     */
    public function getParametrosObjetivo() {
      $params = [];
      if(!empty($this->abv_objetivo)) {
        $params['ABV'] = $this->abv_objetivo . '%';
      }
      if(!empty($this->ibu_objetivo)) {
        $params['IBU'] = $this->ibu_objetivo;
      }
      if(!empty($this->color_ebc_objetivo)) {
        $params['Color EBC'] = $this->color_ebc_objetivo;
      }
      if(!empty($this->og_objetivo)) {
        $params['OG'] = $this->og_objetivo;
      }
      if(!empty($this->fg_objetivo)) {
        $params['FG'] = $this->fg_objetivo;
      }
      return $params;
    }

    /**
     * Obtiene el tiempo total estimado de producción (en días)
     * @return int|null
     */
    public function getTiempoTotalDias() {
      $total = 0;
      if(!empty($this->tiempo_fermentacion_dias)) {
        $total += $this->tiempo_fermentacion_dias;
      }
      if(!empty($this->tiempo_maduracion_dias)) {
        $total += $this->tiempo_maduracion_dias;
      }
      return $total > 0 ? $total : null;
    }
  }

 ?>
