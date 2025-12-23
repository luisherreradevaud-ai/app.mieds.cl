<?php

    class Insumo extends Base {

    public $nombre = "";
    public $id_tipos_de_insumos = 0;
    public $id_proveedores = null;
    public $comentarios = "";
    public $unidad_de_medida = "";
    public $despacho = 0;
    public $bodega = 0;
    public $creada;
    public $last_modified;
    public $last_modified_mensaje = '';

    // Campos de documentación y certificación Halal
    public $url_ficha_tecnica = "";
    public $url_certificado_halal = "";
    public $certificado_halal_numero = "";
    public $certificado_halal_vencimiento = "0000-00-00";
    public $certificado_halal_emisor = "";
    public $es_halal_certificado = 0;

    // Campos de proveedor
    public $proveedor = "";
    public $codigo_proveedor = "";
    public $pais_origen = "";

    // Campos de Materia Prima (identificación y logística)
    public $nombre_comercial = "";
    public $marca = "";
    public $materia_prima_basica = "";
    public $cosecha_anio = null;
    public $presentacion = "";
    public $vida_util_meses = null;

    // Campos específicos de Levadura
    public $es_levadura = 0;
    public $cepa = "";
    public $tipo_levadura = null; // ale_seca, ale_liquida, lager_seca, lager_liquida, wild
    public $atenuacion_min = null;
    public $atenuacion_max = null;
    public $floculacion = null; // baja, media, alta, muy_alta
    public $temp_fermentacion_min = null;
    public $temp_fermentacion_max = null;
    public $tolerancia_alcohol = null;

    public function __construct($id = null) {
      $this->tableName("insumos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
        $this->last_modified = $this->creada;
      }
    }

    /**
     * Verifica si el certificado Halal está vigente
     * @return bool
     */
    public function tieneCertificadoHalalVigente() {
      if(!$this->es_halal_certificado) {
        return false;
      }
      if(empty($this->certificado_halal_vencimiento) ||
         $this->certificado_halal_vencimiento == '0000-00-00') {
        return false;
      }
      return strtotime($this->certificado_halal_vencimiento) >= strtotime('today');
    }

    /**
     * Obtiene insumos con certificación Halal vigente
     * @return array
     */
    public static function getInsumosHalalVigentes() {
      return self::getAll("WHERE es_halal_certificado=1
          AND certificado_halal_vencimiento >= CURDATE()
          ORDER BY nombre ASC");
    }

    /**
     * Obtiene insumos con certificados Halal por vencer
     * @param int $dias Días para el vencimiento
     * @return array
     */
    public static function getInsumosHalalPorVencer($dias = 30) {
      return self::getAll("WHERE es_halal_certificado=1
          AND certificado_halal_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL " . intval($dias) . " DAY)
          ORDER BY certificado_halal_vencimiento ASC");
    }

    /**
     * Obtiene el estado del certificado Halal como badge HTML
     * @return string
     */
    public function getEstadoCertificadoHalalBadge() {
      if(!$this->es_halal_certificado) {
        return '<span class="badge bg-secondary">Sin certificar</span>';
      }
      if($this->tieneCertificadoHalalVigente()) {
        $dias_restantes = floor((strtotime($this->certificado_halal_vencimiento) - time()) / 86400);
        if($dias_restantes <= 30) {
          return '<span class="badge bg-warning text-dark">Por vencer (' . $dias_restantes . ' días)</span>';
        }
        return '<span class="badge bg-success">Vigente</span>';
      }
      return '<span class="badge bg-danger">Vencido</span>';
    }

    /**
     * Obtiene todos los archivos media asociados al insumo
     * @param string $tipo Filtrar por tipo (opcional)
     * @return array
     */
    public function getMedia($tipo = null) {
      global $mysqli;
      $sql = "SELECT m.*, mi.tipo as tipo_relacion, mi.descripcion as descripcion_relacion
              FROM media m
              INNER JOIN media_insumos mi ON m.id = mi.id_media
              WHERE mi.id_insumos = '" . addslashes($this->id) . "'";
      if($tipo) {
        $sql .= " AND mi.tipo = '" . addslashes($tipo) . "'";
      }
      $sql .= " ORDER BY mi.fecha_creacion DESC";
      $result = $mysqli->query($sql);
      $media = array();
      if($result) {
        while($row = $result->fetch_assoc()) {
          $media[] = $row;
        }
      }
      return $media;
    }

    /**
     * Adjunta un archivo media al insumo
     * @param string $id_media ID del archivo media
     * @param string $tipo Tipo de archivo (ficha_tecnica, certificado_halal, etc.)
     * @param string $descripcion Descripción opcional
     * @return bool
     */
    public function attachMedia($id_media, $tipo = 'documento', $descripcion = '') {
      global $mysqli;
      $sql = "INSERT INTO media_insumos (id_insumos, id_media, tipo, descripcion)
              VALUES ('" . addslashes($this->id) . "',
                      '" . addslashes($id_media) . "',
                      '" . addslashes($tipo) . "',
                      '" . addslashes($descripcion) . "')";
      return $mysqli->query($sql);
    }

    /**
     * Desasocia un archivo media del insumo
     * @param string $id_media ID del archivo media
     * @return bool
     */
    public function detachMedia($id_media) {
      global $mysqli;
      $sql = "DELETE FROM media_insumos
              WHERE id_insumos = '" . addslashes($this->id) . "'
              AND id_media = '" . addslashes($id_media) . "'";
      return $mysqli->query($sql);
    }

    /**
     * Obtiene la ficha técnica del insumo (media o URL legacy)
     * @return array|string|null
     */
    public function getFichaTecnica() {
      $media = $this->getMedia('ficha_tecnica');
      if(!empty($media)) {
        return $media[0];
      }
      if(!empty($this->url_ficha_tecnica)) {
        return $this->url_ficha_tecnica;
      }
      return null;
    }

    /**
     * Obtiene el certificado Halal del insumo (media o URL legacy)
     * @return array|string|null
     */
    public function getCertificadoHalal() {
      $media = $this->getMedia('certificado_halal');
      if(!empty($media)) {
        return $media[0];
      }
      if(!empty($this->url_certificado_halal)) {
        return $this->url_certificado_halal;
      }
      return null;
    }

    /**
     * Obtiene el proveedor asociado al insumo
     * @return Proveedor|null
     */
    public function getProveedor() {
      if(!empty($this->id_proveedores)) {
        return new Proveedor($this->id_proveedores);
      }
      return null;
    }

    /**
     * Obtiene el nombre del proveedor (helper para UI)
     * @return string
     */
    public function getProveedorNombre() {
      $proveedor = $this->getProveedor();
      if($proveedor && !empty($proveedor->nombre)) {
        return $proveedor->nombre;
      }
      // Fallback a campo legacy
      if(!empty($this->proveedor)) {
        return $this->proveedor;
      }
      return '-';
    }

    /**
     * Obtiene insumos por proveedor
     * @param string $id_proveedor ID del proveedor
     * @return array
     */
    public static function getByProveedor($id_proveedor) {
      return self::getAll("WHERE id_proveedores='" . addslashes($id_proveedor) . "' ORDER BY nombre ASC");
    }
  }

 ?>
