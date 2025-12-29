<?php
/**
 * TipoDeGasto (Expense Type) Class
 *
 * Self-administrable expense types for gastos caja chica.
 */
class TipoDeGasto extends Base {

  public $id = "";
  public $nombre = "";
  public $descripcion = "";
  public $estado = "activo";
  public $creada = "";
  public $actualizada = "";

  function __construct($id = null) {
    $this->tableName("tipos_de_gasto");
    if($id) {
      $this->id = $id;
      $this->getFromDB();
    }
  }

  /**
   * Get from database
   */
  public function getFromDB() {
    $info = $this->getInfoDatabase('id');
    if($info) {
      $this->setProperties($info);
    }
  }

  /**
   * Override save to update timestamps
   */
  public function save() {
    $this->actualizada = date('Y-m-d H:i:s');
    if($this->id == "") {
      $this->creada = date('Y-m-d H:i:s');
    }
    return parent::save();
  }

  /**
   * Soft delete
   */
  public function softDelete() {
    $this->estado = "inactivo";
    $this->save();
  }

  /**
   * Check if deleted
   */
  public function isDeleted() {
    return $this->estado == "inactivo";
  }

  /**
   * Get all active tipos
   */
  public static function getActivos() {
    return self::getAll("WHERE estado='activo' ORDER BY nombre ASC");
  }

  /**
   * Get as options for dropdown
   */
  public static function getOptions() {
    $tipos = self::getActivos();
    $options = array();
    foreach($tipos as $tipo) {
      $options[$tipo->id] = $tipo->nombre;
    }
    return $options;
  }
}
