<?php

/**
 * Clase para gestionar registros de limpieza de activos
 * Soporta limpiezas generales y certificadas Halal
 */
class RegistroLimpieza extends Base {

    public $id_activos = 0;
    public $fecha = "";
    public $tipo_limpieza = "General";
    public $procedimiento_utilizado = "";
    public $productos_utilizados = "";
    public $id_usuarios = 0;
    public $id_usuarios_supervisor = 0;
    public $observaciones = "";

    // Campos Halal
    public $es_limpieza_halal = 0;
    public $certificado_numero = "";
    public $certificado_emisor = "";

    // Campos CIP (Clean-In-Place)
    public $programa_cip = "";
    public $temperatura_max_cip = null;
    public $tiempo_total_cip_min = null;
    public $conductividad_promedio = null;
    public $cip_timestamp_inicio = null;
    public $cip_timestamp_fin = null;
    public $id_batches_posterior = null;

    // Evidencia
    public $id_media = 0;

    // Metadatos
    public $creada = "";
    public $estado = "activo";

    public $table_name = "registros_limpiezas";
    public $table_fields = array();

    // Objetos relacionados
    public $activo;
    public $usuario;
    public $supervisor;

    public function __construct($id = null) {
        $this->tableName("registros_limpiezas");
        if($id) {
            $this->id = $id;
            $info = $this->getInfoDatabase('id');
            $this->setProperties($info);
        } else {
            $this->creada = date('Y-m-d H:i:s');
            $this->fecha = date('Y-m-d H:i:s');
        }
    }

    /**
     * Obtener tipos de limpieza disponibles
     * @return array
     */
    public static function getTiposLimpieza() {
        return [
            'General' => 'Limpieza General',
            'Profunda' => 'Limpieza Profunda',
            'Halal' => 'Limpieza Halal Certificada',
            'Sanitizacion' => 'Sanitización',
            'CIP' => 'Clean In Place (CIP)'
        ];
    }

    /**
     * Obtener programas CIP disponibles
     * @return array
     */
    public static function getProgramasCIP() {
        return [
            'CIP-BASICO' => 'CIP Básico (Enjuague + Soda)',
            'CIP-COMPLETO' => 'CIP Completo (Enjuague + Soda + Ácido)',
            'CIP-SANITIZADO' => 'CIP con Sanitizado',
            'CIP-HALAL' => 'CIP Halal Certificado',
            'CIP-PERSONALIZADO' => 'CIP Personalizado'
        ];
    }

    /**
     * Verifica si es una limpieza CIP
     * @return bool
     */
    public function esCIP() {
        return $this->tipo_limpieza == 'CIP';
    }

    /**
     * Calcula la duración del CIP en minutos
     * @return int|null
     */
    public function getDuracionCIP() {
        if(!empty($this->cip_timestamp_inicio) && !empty($this->cip_timestamp_fin)) {
            $inicio = strtotime($this->cip_timestamp_inicio);
            $fin = strtotime($this->cip_timestamp_fin);
            return round(($fin - $inicio) / 60);
        }
        return $this->tiempo_total_cip_min;
    }

    /**
     * Verifica si el CIP fue exitoso
     * @return bool
     */
    public function cipExitoso() {
        if(!$this->esCIP()) {
            return false;
        }
        if(empty($this->cip_timestamp_fin)) {
            return false;
        }
        // Conductividad < 50 µS/cm se considera agua limpia
        if($this->conductividad_promedio !== null && $this->conductividad_promedio > 50) {
            return false;
        }
        return true;
    }

    /**
     * Obtiene el batch posterior asociado
     * @return Batch|null
     */
    public function getBatchPosterior() {
        if(!empty($this->id_batches_posterior)) {
            return new Batch($this->id_batches_posterior);
        }
        return null;
    }

    /**
     * Obtiene el estado del CIP como badge HTML
     * @return string
     */
    public function getEstadoCIPBadge() {
        if(!$this->esCIP()) {
            return '';
        }
        if(empty($this->cip_timestamp_inicio)) {
            return '<span class="badge bg-secondary">Sin iniciar</span>';
        }
        if(empty($this->cip_timestamp_fin)) {
            return '<span class="badge bg-warning text-dark">En progreso</span>';
        }
        if($this->cipExitoso()) {
            return '<span class="badge bg-success">Completado</span>';
        }
        return '<span class="badge bg-danger">Con observaciones</span>';
    }

    /**
     * Obtiene el último registro CIP de un activo
     * @param string $id_activos
     * @return RegistroLimpieza|null
     */
    public static function getUltimoCIPPorActivo($id_activos) {
        $registros = self::getAll(
            "WHERE id_activos='" . addslashes($id_activos) . "'
             AND tipo_limpieza='CIP'
             ORDER BY fecha DESC LIMIT 1"
        );
        return count($registros) > 0 ? $registros[0] : null;
    }

    /**
     * Cargar objetos relacionados
     */
    public function cargarRelaciones() {
        if($this->id_activos > 0) {
            $this->activo = new Activo($this->id_activos);
        }
        if($this->id_usuarios > 0) {
            $this->usuario = new Usuario($this->id_usuarios);
        }
        if($this->id_usuarios_supervisor > 0) {
            $this->supervisor = new Usuario($this->id_usuarios_supervisor);
        }
    }

    /**
     * Registrar nueva limpieza y actualizar activo
     * @return int ID del registro creado
     */
    public function registrar() {
        // Guardar registro
        $this->save();

        // Actualizar activo con última limpieza
        if($this->id_activos > 0) {
            $activo = new Activo($this->id_activos);
            $activo->fecha_ultima_limpieza = $this->fecha;

            if($this->es_limpieza_halal) {
                $activo->fecha_ultima_limpieza_halal = $this->fecha;
                $activo->certificado_limpieza_halal = $this->certificado_numero;
            }

            // Calcular próxima limpieza según periodicidad
            $activo->proxima_limpieza = $this->calcularProximaLimpieza(
                $this->fecha,
                $activo->limpieza_periodicidad
            );

            $activo->save();
        }

        return $this->id;
    }

    /**
     * Calcular fecha de próxima limpieza según periodicidad
     * @param string $fecha_actual
     * @param string $periodicidad
     * @return string
     */
    private function calcularProximaLimpieza($fecha_actual, $periodicidad) {
        $fecha = new DateTime($fecha_actual);

        switch($periodicidad) {
            case 'Diaria':
                $fecha->modify('+1 day');
                break;
            case 'Cada 2 días':
                $fecha->modify('+2 days');
                break;
            case 'Semanal':
                $fecha->modify('+1 week');
                break;
            case 'Quincenal':
                $fecha->modify('+2 weeks');
                break;
            case 'Mensual':
                $fecha->modify('+1 month');
                break;
            default:
                $fecha->modify('+1 week');
        }

        return $fecha->format('Y-m-d');
    }

    /**
     * Obtener última limpieza de un activo
     * @param int $id_activos
     * @param string|null $tipo
     * @return RegistroLimpieza|null
     */
    public static function getUltimaPorActivo($id_activos, $tipo = null) {
        $where = "WHERE id_activos='" . addslashes($id_activos) . "'";
        if($tipo) {
            $where .= " AND tipo_limpieza='" . addslashes($tipo) . "'";
        }
        $where .= " ORDER BY fecha DESC LIMIT 1";

        $registros = self::getAll($where);
        return count($registros) > 0 ? $registros[0] : null;
    }

    /**
     * Obtener última limpieza Halal de un activo
     * @param int $id_activos
     * @return RegistroLimpieza|null
     */
    public static function getUltimaHalalPorActivo($id_activos) {
        $registros = self::getAll(
            "WHERE id_activos='" . addslashes($id_activos) . "'
             AND es_limpieza_halal=1
             ORDER BY fecha DESC LIMIT 1"
        );
        return count($registros) > 0 ? $registros[0] : null;
    }

    /**
     * Validar si un activo tiene limpieza Halal válida
     * para poder usarse en producción sin alcohol
     * @param int $id_activos
     * @param int $horas_maximas
     * @return array
     */
    public static function validarLimpiezaHalalParaProduccion($id_activos, $horas_maximas = 24) {
        $ultima = self::getUltimaHalalPorActivo($id_activos);

        if(!$ultima) {
            return [
                'valido' => false,
                'mensaje' => 'No hay registro de limpieza Halal para este activo'
            ];
        }

        $limite = strtotime("-{$horas_maximas} hours");
        $fecha_limpieza = strtotime($ultima->fecha);

        if($fecha_limpieza < $limite) {
            $horas_transcurridas = round((time() - $fecha_limpieza) / 3600, 1);
            return [
                'valido' => false,
                'mensaje' => "La última limpieza Halal fue hace {$horas_transcurridas} horas (máximo permitido: {$horas_maximas}h)",
                'ultima_limpieza' => $ultima
            ];
        }

        return [
            'valido' => true,
            'mensaje' => 'Limpieza Halal válida',
            'ultima_limpieza' => $ultima
        ];
    }

    /**
     * Obtener activos que requieren limpieza Halal
     * antes de poder usarse para producción sin alcohol
     * @return array
     */
    public static function getActivosRequierenLimpiezaHalal() {
        $query = "SELECT a.* FROM activos a
                  WHERE a.linea_productiva = 'general'
                  AND a.clase = 'Fermentador'
                  AND a.estado = 'Activo'
                  AND (
                      a.fecha_ultima_limpieza_halal IS NULL
                      OR a.fecha_ultima_limpieza_halal < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                  )
                  ORDER BY a.nombre ASC";

        $mysqli = $GLOBALS['mysqli'];
        $result = $mysqli->query($query);
        $activos = [];

        if($result) {
            while($row = mysqli_fetch_assoc($result)) {
                $activo = new Activo();
                $activo->setProperties($row);
                $activos[] = $activo;
            }
        }

        return $activos;
    }

    /**
     * Obtener historial de limpiezas de un activo para exportar
     * @param int $id_activos
     * @param int $limit
     * @return array
     */
    public static function getHistorialParaExportar($id_activos, $limit = 50) {
        $registros = self::getAll(
            "WHERE id_activos='" . addslashes($id_activos) . "'
             ORDER BY fecha DESC
             LIMIT " . intval($limit)
        );

        $resultado = [];
        foreach($registros as $reg) {
            $reg->cargarRelaciones();
            $resultado[] = [
                'fecha' => date('d/m/Y H:i', strtotime($reg->fecha)),
                'tipo' => $reg->tipo_limpieza,
                'procedimiento' => $reg->procedimiento_utilizado,
                'productos' => $reg->productos_utilizados,
                'usuario' => $reg->usuario ? $reg->usuario->nombre : 'N/A',
                'supervisor' => $reg->supervisor ? $reg->supervisor->nombre : '-',
                'es_halal' => $reg->es_limpieza_halal ? 'Sí' : 'No',
                'certificado' => $reg->certificado_numero ?: '-',
                'observaciones' => $reg->observaciones
            ];
        }

        return $resultado;
    }
}

?>
