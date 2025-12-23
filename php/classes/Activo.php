<?php

  class Activo extends Base {

    public $nombre = "";
    public $marca = "";
    public $modelo = "";
    public $codigo = "";
    public $capacidad = "";
    public $clasificacion = "";
    public $estado = "";

    public $propietario = "";
    public $adquisicion_date = "0000-00-00";
    public $valorizacion = "";

    public $id_usuarios_control = 0;

    public $ultima_inspeccion = "0000-00-00";
    public $proxima_inspeccion = "0000-00-00";
    public $inspeccion_procedimiento = "";
    public $inspeccion_periodicidad = "";

    public $ultima_mantencion = "0000-00-00";
    public $proxima_mantencion = "0000-00-00";
    public $mantencion_procedimiento = "";
    public $mantencion_periodicidad = "";
    
    public $creada = "";
    public $id_media_header = 0;

    public $ubicacion = "En planta";
    public $id_clientes_ubicacion = 0;

    public $clase = '';
    public $id_locaciones = 0;

    public $accesorios = array();

    public $id_batches = 0;
    public $litraje = 0;
    public $linea_productiva = "general";

    // Campos de limpieza general
    public $fecha_ultima_limpieza = null;
    public $proxima_limpieza = null;
    public $limpieza_procedimiento = "";
    public $limpieza_periodicidad = "Semanal";

    // Campos de limpieza Halal
    public $fecha_ultima_limpieza_halal = null;
    public $certificado_limpieza_halal = "";
    public $uso_exclusivo_halal = 0;

    public function __construct($id = null) {
      $this->tableName("activos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public static function getClases() {

      $clases = [
        'Agitador',
        'Equipo de Cocción',
        'Equipo de Refrigeración',
        'EPP y Equipo de Seguridad',
        'Fermentador',
        'Maquinaria General',
        'Máquina Schopera',
        'Mueble',
        'Vehículo'
      ];

      return $clases;

    }

    public function getAccesorios() {
      $this->accesorios = Accesorio::getAll("WHERE id_activos='".$this->id."'");
      return $this->accesorios;
    }

    /**
     * Verifica si el activo requiere limpieza
     * @return bool
     */
    public function requiereLimpieza() {
      if(empty($this->proxima_limpieza) || $this->proxima_limpieza == '0000-00-00') {
        return true;
      }
      return strtotime($this->proxima_limpieza) <= strtotime('today');
    }

    /**
     * Verifica si puede usarse para producción Halal
     * @return bool
     */
    public function puedeUsarseParaHalal() {
      // Si es uso exclusivo Halal, siempre puede
      if($this->uso_exclusivo_halal) {
        return true;
      }
      // Si es línea sin alcohol, siempre puede
      if($this->linea_productiva == 'analcoholica') {
        return true;
      }
      // Si es general o alcohólica, verificar limpieza Halal reciente
      return $this->tieneLimpiezaHalalReciente();
    }

    /**
     * Verifica si tiene limpieza Halal reciente
     * @param int $horas Horas máximas desde la última limpieza
     * @return bool
     */
    public function tieneLimpiezaHalalReciente($horas = 24) {
      if(empty($this->fecha_ultima_limpieza_halal)) {
        return false;
      }
      $limite = strtotime("-{$horas} hours");
      return strtotime($this->fecha_ultima_limpieza_halal) >= $limite;
    }

    /**
     * Obtiene la última limpieza Halal registrada
     * @return RegistroLimpieza|null
     */
    public function getUltimaLimpiezaHalal() {
      $limpiezas = RegistroLimpieza::getAll(
        "WHERE id_activos='" . $this->id . "'
         AND es_limpieza_halal=1
         ORDER BY fecha DESC LIMIT 1"
      );
      return count($limpiezas) > 0 ? $limpiezas[0] : null;
    }

    /**
     * Obtiene el historial de limpiezas
     * @param int $limit
     * @return array
     */
    public function getHistorialLimpiezas($limit = 10) {
      return RegistroLimpieza::getAll(
        "WHERE id_activos='" . $this->id . "'
         ORDER BY fecha DESC
         LIMIT " . intval($limit)
      );
    }

    /**
     * Obtiene periodicidades de limpieza disponibles
     * @return array
     */
    public static function getPeriodicidadesLimpieza() {
      return [
        'Diaria' => 'Diaria',
        'Cada 2 días' => 'Cada 2 días',
        'Semanal' => 'Semanal',
        'Quincenal' => 'Quincenal',
        'Mensual' => 'Mensual',
        'Después de cada uso' => 'Después de cada uso'
      ];
    }

    /**
     * Obtiene líneas productivas disponibles
     * @return array
     */
    public static function getLineasProductivas() {
      return [
        'alcoholica' => 'Línea Alcohólica',
        'analcoholica' => 'Línea Sin Alcohol',
        'general' => 'General'
      ];
    }

    /**
     * Obtiene el label de la línea productiva
     * @return string
     */
    public function getLineaProductivaLabel() {
      $lineas = self::getLineasProductivas();
      return isset($lineas[$this->linea_productiva])
        ? $lineas[$this->linea_productiva]
        : 'General';
    }
  }

 ?>
