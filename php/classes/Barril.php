<?php

  class Barril extends Base {

    public $tipo_barril;
    public $creada;
    public $estado = "";
    public $codigo = "";
    public $id_clientes = 0;
    public $id_batches = 0;
    public $id_activos = 0;
    public $id_batches_activos = 0;
    public $cliente;
    public $clasificacion = "";
    public $litraje = 0;
    public $litros_cargados = 0;
    public $fecha_llenado = null;

    public function __construct($id = null) {
      $this->tableName("barriles");
      $this->cliente = new Cliente;
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
        if($this->estado == "En terreno" && $this->id_clientes != 0) {
          $this->cliente = new Cliente($this->id_clientes);
        }
      } else {
        $this->creada = date('Y-m-d H:i:s');
        $this->estado = "En planta";
      }
    }

    public function getClientesBarriles() {
      $query = "SELECT DISTINCT clientes.id FROM clientes INNER JOIN barriles ON barriles.id_clientes=clientes.id WHERE barriles.estado='En terreno' ORDER by clientes.nombre asc";
      $result = $this->fetchQuery($query);
      $clientes = array();
      foreach($result as $cliente) {
        $arr['obj'] = new Cliente($cliente['id']);
        $arr['barriles'] = Barril::getAll("WHERE estado!='En planta' AND id_clientes='".$cliente['id']."' ORDER BY codigo asc");
        $clientes[] = $arr;
      }
      return $clientes;
    }

    public function setSpecifics($post) {

      $this->registrarCambioDeEstado();

    }

    public function registrarCambioDeEstado() {

      $barril_ant = new Barril($this->id);

      if($barril_ant->estado != $this->estado) {
        $registros_anteriores = BarrilEstado::getAll("WHERE id_barriles='".$this->id."' AND finalizacion_date='0000-00-00 00:00:00' ORDER BY id desc");
        foreach($registros_anteriores as $registro_anterior) {
          $registro_anterior->finalizacion_date = date('Y-m-d H:i:s');
          $registro_anterior->setTiempoTranscurrido();
          $registro_anterior->save();
        }
        $barril_estado = new BarrilEstado;
        $barril_estado->id_barriles = $this->id;
        $barril_estado->estado = $this->estado;
        $barril_estado->id_clientes = $this->id_clientes;
        $barril_estado->inicio_date = date('Y-m-d H:i:s');
        $barril_estado->id_usuarios = $GLOBALS['usuario']->id;
        $barril_estado->save();

      }

    }

    public static function getBarrilesConProducto() {
      $query = "SELECT b.id AS id_barril, r.nombre, r.id AS id_recetas, p.id AS id_productos FROM barriles AS b JOIN batches AS ba ON ba.id = b.id_batches JOIN recetas AS r ON r.id = ba.id_recetas LEFT JOIN ( SELECT id_recetas, MIN(id) AS id FROM productos GROUP BY id_recetas ) AS pr ON pr.id_recetas = r.id LEFT JOIN productos AS p ON p.id = pr.id WHERE b.id_batches <> 0 AND b.estado = 'En planta';";
      $mysqli = $GLOBALS['mysqli'];
      $mysql_response = $mysqli->query($query);
      $result = [];
      while ($row = mysqli_fetch_assoc($mysql_response)) {
        $barril = new Barril((int)$row['id_barril']);
        $barril->id_productos = isset($row['id_productos']) ? (int)$row['id_productos'] : 0;
        $barril->id_recetas = isset($row['id_recetas']) ? (int)$row['id_recetas'] : 0;
        $barril->nombre = $row['nombre'];
        $result[] = $barril;
      }

      return $result;
    }

    public static function getBarrilesParaDespacho() {
      $query = "SELECT
                  b.id,
                  b.codigo,
                  b.tipo_barril,
                  b.litraje,
                  b.litros_cargados,
                  b.id_batches,
                  r.id AS id_recetas,
                  r.nombre AS nombre_receta
                FROM barriles AS b
                JOIN batches AS ba ON ba.id = b.id_batches
                JOIN recetas AS r ON r.id = ba.id_recetas
                WHERE b.id_batches <> 0
                AND b.estado = 'En planta'
                AND b.clasificacion = 'Cerveza'
                ORDER BY b.codigo ASC";
      $mysqli = $GLOBALS['mysqli'];
      $mysql_response = $mysqli->query($query);
      $result = array();
      while ($row = mysqli_fetch_assoc($mysql_response)) {
        $result[] = array(
          'id' => (int)$row['id'],
          'codigo' => $row['codigo'],
          'tipo_barril' => $row['tipo_barril'],
          'litraje' => (int)$row['litraje'],
          'litros_cargados' => (int)$row['litros_cargados'],
          'id_batches' => (int)$row['id_batches'],
          'id_recetas' => (int)$row['id_recetas'],
          'nombre_receta' => $row['nombre_receta']
        );
      }
      return $result;
    }

  }

 ?>
