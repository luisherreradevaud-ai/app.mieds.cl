<?php
class Atendedor extends Base {
  public $id = "";
  public $rut = "";
  public $nombre_completo = "";
  public $genero = "";
  public $estado_civil = "";
  public $direccion = "";
  public $telefono = "";
  public $correo = "";
  public $nacionalidad = "";
  public $jornada_trabajo = "";
  public $cargo_copec = "";
  public $rol_mae = "";
  public $id_tarjeta_copec = "";  // 14 caracteres alfanuméricos
  public $id_mae = "";            // 4 dígitos
  public $clave_mae = "";         // 4 dígitos
  public $estado = "";
  public $creada = "";
  public $actualizada = "";


  function __construct($id = null) {
    $this->tableName("atendedores");
    if($id) {
      $this->id = $id;
      $this->getFromDatabase('id', $id);
    }
  }
}
