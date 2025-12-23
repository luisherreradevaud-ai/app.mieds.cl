<?php

  class Producto extends Base {

    public $codigo_producto = "";
    public $nombre = "";
    public $id_recetas = 0;
    public $productos_items;
    public $tipo = "";
    public $clasificacion = "";
    public $cantidad = "";
    public $monto = 0;
    public $codigo_de_barra = "";
    public $total_bruto = 0;
    public $id_formatos_de_envases = 0;
    public $cantidad_de_envases = 0;
    public $tipo_envase = "Lata";
    public $es_mixto = 0;
    public $linea_productiva = "general";

    public function __construct($id = null) {
      $this->tableName("productos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
        $this->productos_items = ProductoItem::getAll("WHERE id_productos='".$this->id."'");
        foreach($this->productos_items as $pi) {
          $this->total_bruto += $pi->monto_bruto;
        }
      } else {
        $this->productos_items = [];
      }
    }

    public function setSpecifics($post) {
        if($this->id == "" || $this->id == 0) {
            $this->save();
        }
        foreach($this->productos_items as $pi) {
            $pi->delete();
        }
        if(isset($post['items'])) {
            if(is_array($post['items'])) {
                foreach($post['items'] as $item) {
                    $pi = new ProductoItem;
                    $pi->setPropertiesNoId($item);
                    $pi->id_productos = $this->id;
                    $pi->save();
                }
            }
        }
    }

    public function deleteSpecifics($post) {
        foreach($this->productos_items as $pi) {
            $pi->delete();
        }
    }

    public function getClienteProductoPrecio($id_clientes) {

      $query = "WHERE id_productos='".$this->id."' AND id_clientes='".$id_clientes."'";
      $cpp = ClienteProductoPrecio::getAll($query);

      if(count($cpp)>0) {
        return $cpp[0]->precio;
      } else {
        return $this->monto;
      }

    }

    /**
     * Obtiene el formato de envases asociado
     * @return FormatoDeEnvases|null
     */
    public function getFormatoDeEnvases() {
      if($this->id_formatos_de_envases > 0) {
        return new FormatoDeEnvases($this->id_formatos_de_envases);
      }
      return null;
    }

    /**
     * Verifica si este producto es de tipo envases (latas o botellas)
     * @return bool
     */
    public function esProductoDeEnvases() {
      return $this->id_formatos_de_envases > 0 && $this->cantidad_de_envases > 0;
    }

    /**
     * Obtiene productos configurados para envases
     * @param string|null $tipo 'Lata', 'Botella' o null para todos
     * @return array
     */
    public static function getProductosDeEnvases($tipo = null) {
      $where = "WHERE id_formatos_de_envases > 0 AND cantidad_de_envases > 0";
      if($tipo) {
        $where .= " AND tipo_envase='" . addslashes($tipo) . "'";
      }
      $where .= " ORDER BY nombre ASC";
      return self::getAll($where);
    }

    /**
     * Obtiene el label del tipo de envase para UI
     * @return string
     */
    public function getTipoEnvaseLabel() {
      $labels = array(
        'Lata' => 'Lata',
        'Botella' => 'Botella'
      );
      return isset($labels[$this->tipo_envase]) ? $labels[$this->tipo_envase] : $this->tipo_envase;
    }

    /**
     * Verifica si este producto es mixto (acepta múltiples recetas)
     * @return bool
     */
    public function esMixto() {
      return $this->es_mixto == 1;
    }

    /**
     * Obtiene productos mixtos configurados para envases
     * @param string|null $tipo 'Lata', 'Botella' o null para todos
     * @return array
     */
    public static function getProductosMixtos($tipo = null) {
      $where = "WHERE es_mixto = 1 AND id_formatos_de_envases > 0 AND cantidad_de_envases > 0";
      if($tipo) {
        $where .= " AND tipo_envase='" . addslashes($tipo) . "'";
      }
      $where .= " ORDER BY nombre ASC";
      return self::getAll($where);
    }

    /**
     * Obtiene el label legible de la línea productiva
     * @return string
     */
    public function getLineaProductivaLabel() {
      $lineas = [
        'alcoholica' => 'Línea Alcohólica',
        'analcoholica' => 'Línea Sin Alcohol',
        'general' => 'General'
      ];
      return isset($lineas[$this->linea_productiva])
        ? $lineas[$this->linea_productiva]
        : 'General';
    }

    /**
     * Obtiene opciones de líneas productivas para selectores
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
     * Obtiene productos por línea productiva
     * @param string $linea
     * @return array
     */
    public static function getByLineaProductiva($linea) {
      return self::getAll("WHERE linea_productiva='" . addslashes($linea) . "' ORDER BY nombre ASC");
    }

    /**
     * Genera el código de producto para la línea productiva
     * @param string $linea Línea productiva
     * @return string Código generado (ej: ALC-001)
     */
    public static function generarCodigoProducto($linea) {
      global $mysqli;

      $prefijos = [
        'alcoholica' => 'ALC',
        'analcoholica' => 'SNA',
        'general' => 'GEN'
      ];

      $prefijo = isset($prefijos[$linea]) ? $prefijos[$linea] : 'GEN';

      // Obtener y actualizar secuencia atómicamente
      $sql = "UPDATE productos_secuencias
              SET ultimo_numero = ultimo_numero + 1
              WHERE linea_productiva = '" . addslashes($linea) . "'";
      $mysqli->query($sql);

      // Obtener el número actual
      $sql = "SELECT ultimo_numero FROM productos_secuencias
              WHERE linea_productiva = '" . addslashes($linea) . "'";
      $result = $mysqli->query($sql);

      if($result && $row = $result->fetch_assoc()) {
        $numero = $row['ultimo_numero'];
      } else {
        // Fallback: contar productos existentes
        $sql = "SELECT COUNT(*) as total FROM productos
                WHERE linea_productiva = '" . addslashes($linea) . "'";
        $result = $mysqli->query($sql);
        $numero = $result->fetch_assoc()['total'] + 1;
      }

      return $prefijo . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Override del save para generar código automáticamente
     */
    public function save() {
      // Si es nuevo y no tiene código, generar uno
      if(empty($this->id) && empty($this->codigo_producto)) {
        $this->codigo_producto = self::generarCodigoProducto($this->linea_productiva);
      }
      // Si cambió la línea productiva y ya tenía código, regenerar
      // (solo si es producto existente que cambió de línea)

      return parent::save();
    }

    /**
     * Obtiene el código formateado para mostrar
     * @return string
     */
    public function getCodigoFormateado() {
      if(!empty($this->codigo_producto)) {
        return $this->codigo_producto;
      }
      return '-';
    }

  }

 ?>
