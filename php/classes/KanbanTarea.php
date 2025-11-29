<?php

class KanbanTarea extends Base {

  public $nombre;
  public $descripcion;
  public $id_kanban_columnas;
  public $orden = 0;
  public $fecha_inicio;
  public $fecha_vencimiento;
  public $recordatorio_vencimiento;
  public $checklist;
  public $links;
  public $estado = "Pendiente";
  public $time_elapsed = 0;
  public $creada;
  public $actualizada;

  public function __construct($id = null) {
    $this->tableName("kanban_tareas");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);

      // Parse JSON fields
      if($this->checklist) {
        $this->checklist = json_decode($this->checklist, true);
      }
      if($this->links) {
        $this->links = json_decode($this->links, true);
      }
    } else {
      $this->creada = date('Y-m-d H:i:s');
      $this->actualizada = date('Y-m-d H:i:s');
    }
  }

  public function save() {
    // Convert arrays to JSON before saving (with UTF-8 support for accents)
    if(is_array($this->checklist)) {
      $checklist_backup = $this->checklist;
      $this->checklist = json_encode($this->checklist, JSON_UNESCAPED_UNICODE);
    }
    if(is_array($this->links)) {
      $links_backup = $this->links;
      $this->links = json_encode($this->links, JSON_UNESCAPED_UNICODE);
    }

    $this->actualizada = date('Y-m-d H:i:s');

    $result = parent::save();

    // Restore arrays after saving
    if(isset($checklist_backup)) {
      $this->checklist = $checklist_backup;
    }
    if(isset($links_backup)) {
      $this->links = $links_backup;
    }

    return $result;
  }

  public function setSpecifics($post) {
    // Handle users (many-to-many relationship)
    if(isset($post['usuarios'])) {
      // Delete existing user assignments
      $query = "DELETE FROM kanban_tareas_usuarios WHERE id_kanban_tareas='".$this->id."'";
      $this->runQuery($query);

      // Add new user assignments
      if(is_array($post['usuarios'])) {
        foreach($post['usuarios'] as $id_usuario) {
          if(is_numeric($id_usuario) && $id_usuario > 0) {
            $tarea_usuario = new KanbanTareaUsuario();
            $tarea_usuario->id_kanban_tareas = $this->id;
            $tarea_usuario->id_usuarios = $id_usuario;
            $tarea_usuario->save();
          }
        }
      }
    }

    // Handle labels (many-to-many relationship)
    if(isset($post['etiquetas'])) {
      // Delete existing label assignments
      $query = "DELETE FROM kanban_tareas_etiquetas WHERE id_kanban_tareas='".$this->id."'";
      $this->runQuery($query);

      // Add new label assignments
      if(is_array($post['etiquetas'])) {
        foreach($post['etiquetas'] as $id_etiqueta) {
          if(is_numeric($id_etiqueta) && $id_etiqueta > 0) {
            $tarea_etiqueta = new KanbanTareaEtiqueta();
            $tarea_etiqueta->id_kanban_tareas = $this->id;
            $tarea_etiqueta->id_kanban_etiquetas = $id_etiqueta;
            $tarea_etiqueta->save();
          }
        }
      }
    }
  }

  public function getColumna() {
    if(!$this->id_kanban_columnas) {
      return null;
    }
    return new KanbanColumna($this->id_kanban_columnas);
  }

  public function getTablero() {
    $columna = $this->getColumna();
    if(!$columna) {
      return null;
    }
    return $columna->getTablero();
  }

  public function getUsuarios() {
    $query = "SELECT id_usuarios FROM kanban_tareas_usuarios WHERE id_kanban_tareas='".$this->id."'";
    $result = $this->fetchQuery($query);
    $usuarios = array();
    foreach($result as $row) {
      $usuarios[] = (int)$row['id_usuarios'];
    }
    return $usuarios;
  }

  public function getEtiquetas() {
    $query = "SELECT id_kanban_etiquetas FROM kanban_tareas_etiquetas WHERE id_kanban_tareas='".$this->id."'";
    $result = $this->fetchQuery($query);
    $etiquetas = array();
    foreach($result as $row) {
      $etiquetas[] = (int)$row['id_kanban_etiquetas'];
    }
    return $etiquetas;
  }

  public function addUsuario($id_usuario) {
    if(!$this->id || !$id_usuario) {
      return false;
    }
    $query = "INSERT INTO kanban_tareas_usuarios (id_kanban_tareas, id_usuarios) VALUES ('".$this->id."', '".$id_usuario."')";
    return $this->runQuery($query);
  }

  public function addEtiqueta($id_etiqueta) {
    if(!$this->id || !$id_etiqueta) {
      return false;
    }
    $query = "INSERT INTO kanban_tareas_etiquetas (id_kanban_tareas, id_kanban_etiquetas) VALUES ('".$this->id."', '".$id_etiqueta."')";
    return $this->runQuery($query);
  }

  public function getCantidadArchivos() {
    $query = "SELECT COUNT(*) as total FROM media_kanban_tareas WHERE id_kanban_tareas='".$this->id."'";
    $result = $this->getRow($query);
    return isset($result['total']) ? (int)$result['total'] : 0;
  }

  public function isVencida() {
    if(!$this->fecha_vencimiento || $this->fecha_vencimiento == '0000-00-00') {
      return false;
    }
    $hoy = date('Y-m-d');
    return $this->fecha_vencimiento < $hoy && $this->estado != 'Completada';
  }

  public function getProgresoChecklist() {
    if(!$this->checklist || !is_array($this->checklist)) {
      return array('completados' => 0, 'total' => 0);
    }

    $total = 0;
    $completados = 0;

    foreach($this->checklist as $checklist) {
      if(isset($checklist['items']) && is_array($checklist['items'])) {
        foreach($checklist['items'] as $item) {
          $total++;
          if(isset($item['completed']) && $item['completed']) {
            $completados++;
          }
        }
      }
    }

    return array('completados' => $completados, 'total' => $total);
  }

  public function toArray() {
    return array(
      'id' => $this->id,
      'nombre' => $this->nombre,
      'descripcion' => $this->descripcion,
      'id_kanban_columnas' => $this->id_kanban_columnas,
      'orden' => $this->orden,
      'fecha_inicio' => $this->fecha_inicio,
      'fecha_vencimiento' => $this->fecha_vencimiento,
      'recordatorio_vencimiento' => $this->recordatorio_vencimiento,
      'checklist' => $this->checklist,
      'links' => $this->links,
      'estado' => $this->estado,
      'time_elapsed' => $this->time_elapsed,
      'usuarios' => $this->getUsuarios(),
      'etiquetas' => $this->getEtiquetas(),
      'cantidad_archivos' => $this->getCantidadArchivos(),
      'creada' => $this->creada,
      'actualizada' => $this->actualizada
    );
  }
}

?>
