<?php

/**
 * Clase para gestionar procedimientos de limpieza
 * Catálogo de procedimientos estándar para activos
 */
class ProcedimientoLimpieza extends Base {

    public $codigo = "";
    public $nombre = "";
    public $tipo = "General";
    public $descripcion = "";
    public $pasos = "";
    public $productos_requeridos = "";
    public $tiempo_estimado_minutos = 0;
    public $frecuencia_recomendada = "";
    public $aplica_a_clases = "";
    public $es_halal_certificado = 0;
    public $version = "1.0";
    public $creada = "";
    public $actualizada = "";
    public $estado = "activo";

    public $table_name = "procedimientos_limpieza";
    public $table_fields = array();

    public function __construct($id = null) {
        $this->tableName("procedimientos_limpieza");
        if($id) {
            $this->id = $id;
            $info = $this->getInfoDatabase('id');
            $this->setProperties($info);
        } else {
            $this->creada = date('Y-m-d H:i:s');
        }
    }

    /**
     * Obtener tipos de procedimiento disponibles
     * @return array
     */
    public static function getTipos() {
        return [
            'General' => 'Limpieza General',
            'Profunda' => 'Limpieza Profunda',
            'Halal' => 'Limpieza Halal Certificada',
            'Sanitizacion' => 'Sanitización',
            'CIP' => 'Clean In Place (CIP)'
        ];
    }

    /**
     * Obtener pasos como array
     * @return array
     */
    public function getPasosArray() {
        if(empty($this->pasos)) {
            return [];
        }
        $decoded = json_decode($this->pasos, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Establecer pasos desde array
     * @param array $pasos
     */
    public function setPasosArray($pasos) {
        $this->pasos = json_encode($pasos, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtener productos requeridos como array
     * @return array
     */
    public function getProductosRequeridosArray() {
        if(empty($this->productos_requeridos)) {
            return [];
        }
        $decoded = json_decode($this->productos_requeridos, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Establecer productos requeridos desde array
     * @param array $productos
     */
    public function setProductosRequeridosArray($productos) {
        $this->productos_requeridos = json_encode($productos, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtener clases de activo aplicables como array
     * @return array
     */
    public function getAplicaAClasesArray() {
        if(empty($this->aplica_a_clases)) {
            return [];
        }
        $decoded = json_decode($this->aplica_a_clases, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Establecer clases aplicables desde array
     * @param array $clases
     */
    public function setAplicaAClasesArray($clases) {
        $this->aplica_a_clases = json_encode($clases, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtener tiempo estimado formateado
     * @return string
     */
    public function getTiempoEstimadoFormateado() {
        if($this->tiempo_estimado_minutos <= 0) {
            return 'No especificado';
        }
        if($this->tiempo_estimado_minutos < 60) {
            return $this->tiempo_estimado_minutos . ' minutos';
        }
        $horas = floor($this->tiempo_estimado_minutos / 60);
        $minutos = $this->tiempo_estimado_minutos % 60;
        if($minutos > 0) {
            return $horas . 'h ' . $minutos . 'min';
        }
        return $horas . ' hora' . ($horas > 1 ? 's' : '');
    }

    /**
     * Obtener procedimientos por tipo
     * @param string $tipo
     * @return array
     */
    public static function getByTipo($tipo) {
        return self::getAll("WHERE tipo='" . addslashes($tipo) . "' AND estado='activo' ORDER BY nombre ASC");
    }

    /**
     * Obtener procedimientos Halal
     * @return array
     */
    public static function getProcedimientosHalal() {
        return self::getAll("WHERE es_halal_certificado=1 AND estado='activo' ORDER BY nombre ASC");
    }

    /**
     * Obtener procedimientos aplicables a una clase de activo
     * @param string $clase
     * @return array
     */
    public static function getByClaseActivo($clase) {
        return self::getAll(
            "WHERE estado='activo'
             AND (aplica_a_clases IS NULL
                  OR aplica_a_clases = ''
                  OR aplica_a_clases LIKE '%" . addslashes($clase) . "%')
             ORDER BY nombre ASC"
        );
    }

    /**
     * Obtener procedimiento por código
     * @param string $codigo
     * @return ProcedimientoLimpieza|null
     */
    public static function getByCodigo($codigo) {
        $procedimientos = self::getAll("WHERE codigo='" . addslashes($codigo) . "' AND estado='activo' LIMIT 1");
        return count($procedimientos) > 0 ? $procedimientos[0] : null;
    }

    /**
     * Generar siguiente código automático
     * @return string
     */
    public static function generarSiguienteCodigo() {
        $mysqli = $GLOBALS['mysqli'];
        $result = $mysqli->query("SELECT codigo FROM procedimientos_limpieza ORDER BY id DESC LIMIT 1");

        if($result && $row = mysqli_fetch_assoc($result)) {
            // Extraer número del código (PROC-LIM-XXX)
            preg_match('/PROC-LIM-(\d+)/', $row['codigo'], $matches);
            if(isset($matches[1])) {
                $siguiente = intval($matches[1]) + 1;
                return 'PROC-LIM-' . str_pad($siguiente, 3, '0', STR_PAD_LEFT);
            }
        }

        return 'PROC-LIM-001';
    }
}

?>
