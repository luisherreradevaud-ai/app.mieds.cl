<?php

abstract class Base {

  public $id = "";
  public $table_name = "";
  public $table_fields = array();

  protected function id($id = null) {
    if($id == null) { return $this->id; }
    else { $this->id = $id; }
  }

  protected function fetch_query($query) {
    $mysqli = $GLOBALS['mysqli'];
    $data = $mysqli->query($query);
    if(!$data){
      return array();
    }
    return mysqli_fetch_all($data,MYSQLI_ASSOC);
  }

  protected function fetchQuery($query) {
    $mysqli = $GLOBALS['mysqli'];
    $data = $mysqli->query($query);
    if(!$data){
      return array();
    }
    return mysqli_fetch_all($data,MYSQLI_ASSOC);
  }

  public function tableFields() {

    $database_name = $GLOBALS['mysqli_db'];

    $query = "SELECT
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_MAXIMUM_LENGTH
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = '".$this->table_name."'
    AND TABLE_SCHEMA = '".$database_name."'";
    $fetch_table_columns = $this->fetch_query($query);

    foreach($fetch_table_columns as $table_column) {
      $info_column = array();
      $info_column['name'] = $table_column['COLUMN_NAME'];
      $info_column['data_type'] = $table_column['DATA_TYPE'];
      $info_column['length'] = $table_column['CHARACTER_MAXIMUM_LENGTH'];
      $this->table_fields[$table_column['COLUMN_NAME']] = $info_column;
    }
  }

  public function tableName($value = null) {
    if($value==null) {
      return $this->table_name;
    } else {
      $this->table_name = $value;
    }
  }

  public function update() {

    $insert_cols = [];

    foreach($this->table_fields as $fields) {

      if($fields['name']=="id") { continue; }

      $value = "''";

      if(property_exists($this,$fields['name'])) {
        if( $fields['data_type']=="int" || $fields['data_type']=="tinyint" ) {
          if($this->{$fields['name']} === '' || $this->{$fields['name']} === null) {
            $this->{$fields['name']} = NULL;
          } else if(filter_var($this->{$fields['name']}, FILTER_VALIDATE_INT) === false && $this->{$fields['name']} !== 0 && $this->{$fields['name']} !== '0') {
            $this->{$fields['name']} = 0;
          } else {
            $this->{$fields['name']} = intval($this->{$fields['name']});
          }
        } else
        if($fields['data_type']=="decimal" || $fields['data_type']=="float" || $fields['data_type']=="double") {
          if($this->{$fields['name']} === '' || $this->{$fields['name']} === null) {
            $this->{$fields['name']} = NULL;
          } else if(!is_numeric($this->{$fields['name']})) {
            $this->{$fields['name']} = NULL;
          }
        } else
        if($fields['data_type']=="text"||$fields['data_type']=="varchar") {
          if($this->{$fields['name']} !== null) {
            $this->{$fields['name']} = addslashes($this->{$fields['name']});
            if($fields['length'] !== null && strlen($this->{$fields['name']})>intval($fields['length'])) {
              $this->{$fields['name']} = substr($this->{$fields['name']},0,$fields['length']);
            }
          }
        } else
        if($fields['data_type']=="date" && $this->{$fields['name']} == '') {
          $this->{$fields['name']} = NULL;
        } else
        if($fields['data_type']=="datetime" && $this->{$fields['name']} == '') {
          $this->{$fields['name']} = NULL;
        }
        $value = $this->{$fields['name']};
      }
      if($value === NULL) {
        $insert_cols[] = $fields['name']."=NULL";
      } else {
        $insert_cols[] = $fields['name']."='".$value."'";
      }
    }

    $query = "UPDATE "
              .$this->table_name
              ." SET "
              .implode(',',$insert_cols)
              ." WHERE id='"
              .$this->id
              ."'";

    $mysqli = $GLOBALS['mysqli'];
    $result = $mysqli->query($query);

  }

  public function setProperties($fields) {
    if(!is_object($fields)) {
      if(!is_array($fields)) {
        return false;
      }
    }
    foreach($fields as $key=>$value) {
      if(is_array($value)){ continue; }
      if(property_exists($this,$key)) {
        //$value = str_replace("\\");
        //$value = addslashes($value);
        //$value = htmlentities($value);
        $value = stripslashes($value);
        $this->{$key} = $value;
      }
    }
  }

  public function setPropertiesNoId($fields) {
    if(!is_object($fields)) {
      if(!is_array($fields)) {
        return false;
      }
    }
    foreach($fields as $key=>$value) {
      if(is_array($value)){ continue; }
      $value = stripslashes($value);
      //$value = htmlentities($value);
      if($key=="id") { continue; }
      if($key=="table_name") { continue; }
      if($key=="table_fields") { continue; }
      if(property_exists($this,$key)) {
        $this->{$key} = $value;
      }
    }
  }

  public function insert() {

    $mysqli = $GLOBALS['mysqli'];
    $keys = [];
    $values = [];

    foreach($this->table_fields as $fields) {

      if($fields['name']=="id") { continue; }

      $keys[] = $fields['name'];

      if(property_exists($this,$fields['name'])) {
        if( $fields['data_type']=="int" || $fields['data_type']=="tinyint" ) {
          if($this->{$fields['name']} === '' || $this->{$fields['name']} === null) {
            $this->{$fields['name']} = NULL;
          } else if(filter_var($this->{$fields['name']}, FILTER_VALIDATE_INT) === false && $this->{$fields['name']} !== 0 && $this->{$fields['name']} !== '0') {
            $this->{$fields['name']} = 0;
          } else {
            $this->{$fields['name']} = intval($this->{$fields['name']});
          }
        } else
        if($fields['data_type']=="decimal" || $fields['data_type']=="float" || $fields['data_type']=="double") {
          if($this->{$fields['name']} === '' || $this->{$fields['name']} === null) {
            $this->{$fields['name']} = NULL;
          } else if(!is_numeric($this->{$fields['name']})) {
            $this->{$fields['name']} = NULL;
          }
        } else
        if($fields['data_type']=="text"||$fields['data_type']=="varchar") {
          if($this->{$fields['name']} != null) {
            $this->{$fields['name']} = addslashes($this->{$fields['name']});
          }
          if(strlen($this->{$fields['name']})>intval($fields['length'])) {
            $this->{$fields['name']} = substr($this->{$fields['name']},0,$fields['length']);
          }
        } else
        if($fields['data_type']=="date" && $this->{$fields['name']} == '') {
          $this->{$fields['name']} = NULL;
        } else
        if($fields['data_type']=="datetime" && $this->{$fields['name']} == '') {
          $this->{$fields['name']} = NULL;
        }
        if($this->{$fields['name']} === NULL) {
          $values[] = "NULL";
        } else {
          $values[] = "'".$this->{$fields['name']}."'";
        }
      } else {
        $values[] = "NULL";
      }
    }

    $query =  "INSERT INTO "
    .$this->table_name
    ." ("
    .implode(",",$keys)
    .") VALUES ("
    .implode(",",$values)
    .")";

    if($this->table_name == "usuarios") {
      //print $query;
    }

    $return = $mysqli->query($query);
    if(!$return) {
      return false;
    }
    $this->id($mysqli->insert_id);
    return $this->id;
  }

  /* public function getInfo() {
    $query = "SELECT * FROM "
    .$this->$table_name
    ." WHERE id='"
    .$this->$id
    ."'";

    $info = fetch_query($query);
    foreach($info as $key=>$value) {
      if(property_exists($this,$key)) {
        $this->{$key} = $value;
      }
    }
    return $info;
  } */

  public function armarFields() {
    foreach($this as $key=>$value){
      $fields[$key] = $value;
    }
    return $fields;
  }

  public function save() {
    $this->tableFields($this->table_name);
    if($this->id!="") {
      $this->update();
    } else {
      $this->insert();
    }
    return $this;
  }

  public function getInfoDatabase($field, $data=null) {
    $keyword = ($data ? $data : $this->{$field});
    $query = "SELECT * FROM ".$this->table_name." WHERE ".$field."='".$keyword."'";
    $mysqli = $GLOBALS['mysqli'];
    $data_usuario = $mysqli->query($query);
    //print_r($data_usuario);
    return mysqli_fetch_array($data_usuario,MYSQLI_ASSOC);
  }

  public function getRow($query) {
    $mysqli = $GLOBALS['mysqli'];
    $data_usuario = $mysqli->query($query);
    return mysqli_fetch_array($data_usuario,MYSQLI_BOTH);
  }

  public function runQuery($query) {
    $mysqli = $GLOBALS['mysqli'];
    $mysqli->query($query);
  }

  public function getRelations($relation) {

    if($this->id!=""&&$this->id!=0) {
      $arr = array();
      $table = $this->table_name."_".$relation;
      $query = "SHOW TABLES LIKE '".$table."'";
      $result = $this->getRow($query);

      if(!is_array($result)) {
        $table = $relation."_".$this->table_name;
        $query = "SHOW TABLES LIKE '".$table."'";
        $result = $this->getRow($query);

        if(!is_array($result)) {
          return $arr;
        }

      }

      $query = "SELECT * FROM ".$table." WHERE id_".$this->table_name."='".$this->id."'";

      $fetch_info = $this->fetch_query($query);
      if(!is_array($fetch_info)) {
        return $arr;
      }
      foreach($fetch_info as $info) {
        $arr[] = $info['id_'.$relation];
      }
      return $arr;
    }
  }

  public function createRelation($relation) {
    $mysqli = $GLOBALS['mysqli'];
    if($this->id!=""&&$this->id!=0) {
      $table = $this->table_name."_".$relation->table_name;
      $query = "SHOW TABLES LIKE '".$table."'";
      $result = $this->getRow($query);
      if(!is_array($result)) {
        $table = $relation->table_name."_".$this->table_name;
        $query = "SHOW TABLES LIKE '".$table."'";
        $result = $this->getRow($query);
        if(!is_array($result)) {
          return false;
        }
      }
      $query = "INSERT INTO ".$table
      ." (id_".$this->table_name
      .",id_".$relation->table_name.")
      VALUES ('".$this->id
      ."','".$relation->id."')";
      //print $query;
      $mysqli->query($query);
    }
  }

  public function getFromDatabase($field,$data) {
    $info = $this->getInfoDatabase($field,$data);
    $this->setProperties($info);
  }

  public function getMedia() {

    $arr = array(
      0 => array(
        "id" => 0,
        "nombre" => "No hay imagen disponible",
        "descripcion" => "No hay imagen disponible",
        "url" => "NOT_FOUND.jpg",
        "tipo" => "jpg"
      )
    );

    $query = "SELECT media.*
    FROM media
    INNER JOIN media_".$this->table_name."
    ON media.id=media_".$this->table_name.".id_media
    AND media_".$this->table_name.".id_".$this->table_name."='".$this->id."'";
    $result = $this->fetch_query($query);

    if(!$result) {
      return $arr;
    }
    if(!is_array($result)){
      return $arr;
    }

    return $result;
  }

  public function delete() {
    $mysqli = $GLOBALS['mysqli'];
    $query = "DELETE FROM ".$this->table_name." WHERE id='".$this->id."'";
    $mysqli->query($query);
  }

  public static function getAll($conditions = NULL) {
    $object = 1;
    $objects = array();
    $mysqli = $GLOBALS['mysqli'];
    eval("\$object = new ".get_called_class().";");
    $query = "SELECT ".$object->table_name.".id FROM ".$object->table_name;
    if($conditions) {
      $query = $query." ".$conditions;
    }

    $result = $mysqli->query($query);
    if(!$result) {
      return [];
    }
    $data = mysqli_fetch_all($result,MYSQLI_ASSOC);
    foreach($data as $info) {
      eval("\$objects[] = new ".get_called_class()."(".$info['id'].");");
    }
    return $objects;
  }

  public function deleteRelation($relation) {
    $mysqli = $GLOBALS['mysqli'];
    if($this->id!=""&&$this->id!=0) {
      $table = $this->table_name."_".$relation->table_name;
      $query = "SHOW TABLES LIKE '".$table."'";
      $result = $this->getRow($query);
      if(!is_array($result)) {
        $table = $relation->table_name."_".$this->table_name;
        $query = "SHOW TABLES LIKE '".$table."'";
        $result = $this->getRow($query);
        if(!is_array($result)) {
          return false;
        }
      }
      $query = "DELETE FROM ".$table
      ." WHERE id_".$this->table_name
      ."='".$this->id."' AND "
      ."id_".$relation->table_name
      ."='".$relation->id
      ."'";
      //print $query;
      $mysqli->query($query);
    }
  }

  public function deleteAllMedia() {
    $media_arr = $this->getMedia();
    foreach($media_arr as $media_a) {
      $media = new Media($media_a['id']);
      $media->deleteMedia();
    }
  }

  public function setSpecifics($values) {
    return false;
  }

  public function deleteSpecifics($values) {
    return false;
  }

  public function getMediaHeader() {

    $media = $this->getMedia();
    $media_header = new Media;
    $media_header->setProperties($media[0]);

    if(!property_exists($this,'id_media_header')){
      return $media_header;
    }

    if($this->id_media_header != 0) {
      $media_header = new Media($this->id_media_header);
    }

    return $media_header;

  }

}

?>
