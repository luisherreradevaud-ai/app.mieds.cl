<?php

  class Usuario extends Base {

    public $email = "";
    public $password = "";
    public $nombre = "";
    public $apellido = "";
    public $nivel;
    public $telefono;
    public $recuperacion;
    public $fecha_creacion;
    public $invitacion;
    public $id_clientes = 0;
    public $vendedor_meta_barriles = 0;
    public $vendedor_meta_cajas = 0;
    public $estado;
    public $prevlink_direction = "in";
    public $prevlink_count = 0;
    public $id_secciones = 0;
    public $registro_asistencia = 0;

    public function __construct($id = null) {
      $this->tableName("usuarios");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->id = "";
        $nombre = "Invitado";
        $this->fecha_creacion = date('Y-m-d');
      }
    }

    public function checkSession() {
      if(!isset($_SESSION['email'])) {
        $this->init_invitado();
      }
      if($_SESSION['email']==""||$_SESSION['nombre']=="Invitado") {
        
        $this->init_invitado();
      } else {
        $info = $this->getInfoDatabase("email",$_SESSION['email']);
        if($_SESSION['password']!=$info['password']) {
          $this->init_invitado();
        } else {
          $this->init_user($info);
          if($this->invitacion == "" && $this->estado != "Bloqueado") {
            $this->init_user($info);
          } else {
            $this->init_invitado();
          }

        }
      }
      $_SESSION = $this->sessionArray();
    }

    public function sessionArray() {
      return array(
        "id"=>$this->id,
        "email"=>$this->email,
        "password"=>$this->password,
        "nombre"=>$this->nombre,
        "apellido"=>$this->apellido,
        "nivel"=>$this->nivel,
        "telefono"=>$this->telefono
      );
    }

    public function logout() {
      $this->init_invitado();
      return $this->sessionArray();
    }

    public function toIndex() {
      header("Location: ./");
    }

    public function toLoginPage() {
    header("Location: ./login.php");
    }

    public function init_invitado() {
      $this->id = "0";
      $this->email = "";
      $this->user = "Invitado";
      $this->password = "";
      $this->nombre = "Invitado";
      $this->apellido = "";
      $this->telefono = "";
      $this->nivel = "";
      $_SESSION = $this->sessionArray();
      //$this->toLoginPage();
    }

    public function init_user($info_usuario) {
      $this->setProperties($info_usuario);
      $_SESSION = $this->sessionArray();
      
    }

    public function passwordHash($pass) {
      $hash = $GLOBALS['passwordHash'];
      return crypt($pass,$hash);
    }

    public function setRecuperacion() {
      if($this->id=="") {
        return "";
      }
      $pre_hash = "recuperacion".$this->id;
      $this->recuperacion = $this->passwordHash($pre_hash);
      $email = new Email;
      $email->destinatario = $this->email;
      $email->setMailRecuparacion($this->nombre,$this->recuperacion);
      $email->enviarMail();
    }

    public function login($datos) {
      $info_usuario = $this->getInfoDatabase('email',$datos['email']);
      if(is_array($info_usuario)) {
        if($this->passwordHash($datos['password'])==$info_usuario['password']) {
          $this->init_user($info_usuario);
        }
        else {
          header("Location: https://app.mieds.cl/login.php?msg=1");
          die();
        }
      } else {
        header("Location: https://app.mieds.cl/login.php?msg=3");
        die();
      }
    }

    public function registroGoogle($datos) {
      $email = new Email;
      $this->email = $datos['email'];
      $this->password = $this->passwordHash($datos['password']);
      $this->nombre = $datos['email'];
      $this->social_network = "google";
      $this->app_id = $datos['password'];
      $this->nivel = 3;
      $this->fecha_creacion = date('Y-m-d');
      $this->save();
      $email->destinatario = $this->email;
      $email->mailBienvenida($this->nombre);
      $email->enviarMail();
    }

    public function isInvitado() {
      if($this->id == 0 || $this->id == '') {
        return true;
      }
      return false;
    }

    public function nuevoUsuario() {

    }

    public function setInvitacion() {
      if($this->id=="") {
        return false;
      }
      $this->invitacion = $this->passwordHash($this->id);
      $this->password = "";
      $this->save();
      $email = new Email;
      $email->destinatario = $this->email;
      $email->setMailInvitacion($this);
      $email->enviarMail();
    }

    public function checkAutorizacion($section,$ambiente = 'web') {

      $seccion = new Seccion;
      $seccion->getFromDatabase('template_file',$section);

      if($seccion->id == '') {
        return false;
      }

      $this->id_secciones = $seccion->id;
      $this->save();

      $usuario_nivel = new UsuarioNivel;
      $usuario_nivel->getFromDatabase('nombre',$this->nivel);

      if($usuario_nivel->id == '') {
        return false;
      }


      $permisos = Permiso::getAll("WHERE id_secciones='".$seccion->id."' AND id_usuarios_niveles='".$usuario_nivel->id."' AND acceso='1' LIMIT 1");
      if(count($permisos) == 0) {
        return false;
      }

      if($seccion->id_menus != 0) {
        $this->createPath();
      } else {
        $this->followPath();
      }

      return true;
    }
    public function renderMenu() {


      $menus = Menu::getAll("ORDER BY nombre asc");
      $menus_arr = array();
      foreach($menus as $menu) {
        $menus_arr[$menu->id] = $menu;
      }

      $usuario_nivel = new UsuarioNivel;
      $usuario_nivel->getFromDatabase('nombre',$this->nivel);
      $permisos = Permiso::getAll("INNER JOIN secciones ON permisos.id_secciones = secciones.id WHERE permisos.id_usuarios_niveles='".$usuario_nivel->id."' AND permisos.acceso='1' ORDER BY secciones.nombre asc");

      foreach($permisos as $permiso) {
        $seccion = new Seccion($permiso->id_secciones);
        if(!isset($menus_arr[$seccion->id_menus])) {
          continue;
        }
        $menus_arr[$seccion->id_menus]->secciones[] = $seccion;
        if($GLOBALS['section'] == $seccion->template_file) {
          $menus_arr[$seccion->id_menus]->estado = "active";
          $seccion->estado = "active";
        }
      }

      foreach($menus_arr as $menu) {
        if(count($menu->secciones) == 0) {
          continue;
        }
        ?>
          <li class="sidebar-item <?= $menu->estado; ?>">
						<a data-bs-target="#menu-<?= $menu->id; ?>" data-bs-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-lucide="<?= $menu->icon; ?>"></i> <span class="align-middle"><?= $menu->nombre; ?></span>
						</a>
						<ul id="menu-<?= $menu->id; ?>" class="sidebar-dropdown list-unstyled collapse <?= ($menu->estado == "active") ? "show" : ""; ?>" data-bs-parent="#sidebar">
              <?php
                foreach($menu->secciones as $s) {
                  ?>
                  <li class="sidebar-item <?= $s->estado; ?>"><a class="sidebar-link" href="./?s=<?= $s->template_file; ?>"><?= $s->nombre; ?></a></li>
                  <?php
                }
              ?>
						</ul>
					</li>

        <?php
      }

    }

    public function createPath() {

      $prevlinks_prev = PrevLink::getAll("WHERE id_usuarios='".$this->id."'");
      foreach($prevlinks_prev as $p) {
        $p->delete();
      }

      $this->prevlink_direction = "in";
      $this->prevlink_count = 0;
      $this->save();

      $prevlink = new PrevLink;
      $prevlink->id_usuarios = $this->id;
      $prevlink->id_secciones = $this->id_secciones;
      $prevlink->count = $this->prevlink_count;
      $prevlink->datetime = date('Y-m-d H:i:s');
      $prevlink->url = "https://app.mieds.cl".$_SERVER['REQUEST_URI'];
      $prevlink->save();


    }

    public function followPath() {

      $p_prev = PrevLink::getAll("WHERE id_usuarios='".$this->id."' AND id_secciones='".$this->id_secciones."' AND count='".$this->prevlink_count."' ORDER BY count desc LIMIT 1");

      if(count($p_prev)) {
        return false;
      } else {

        $p_direction = PrevLink::getAll("WHERE id_usuarios='".$this->id."' AND id_secciones='".$this->id_secciones."' ORDER BY count desc LIMIT 1");

        if(count($p_direction)>0) {
          $this->prevlink_direction = "out";
          $p_borrar = PrevLink::getAll("WHERE id_usuarios='".$this->id."' AND count='".$this->prevlink_count."' LIMIT 1");
          $p_borrar[0]->delete();
          if($this->prevlink_count > 0) {
            $this->prevlink_count -= 1;
          }
        } else {
          $this->prevlink_direction = "in";
          $this->prevlink_count += 1;
          $prevlink = new PrevLink;
          $prevlink->id_usuarios = $this->id;
          $prevlink->id_secciones = $this->id_secciones;
          $prevlink->count = $this->prevlink_count;
          $prevlink->datetime = date('Y-m-d H:i:s');
          $prevlink->url = "https://app.mieds.cl".$_SERVER['REQUEST_URI'];
          $prevlink->save();
        }

        $this->save();

      }

    }

    public function printReturnBtn() {

      if($this->prevlink_count == 0) {
        return false;
      }

      $prevlink_arr = PrevLink::getAll("WHERE id_usuarios='".$this->id."' AND count='".($this->prevlink_count - 1)."' LIMIT 1");
      $prevlink = $prevlink_arr[0];
      $seccion = new Seccion($prevlink->id_secciones);
      print "<a href='".$prevlink->url."' class='d-sm-inline-block btn btn-sm btn-outline-secondary shadow-sm my-auto'><i class='fas fa-fw fa-backward'></i> Volver a ".$seccion->nombre."</a>";

    }

    public function getReturnLink() {

      if($this->prevlink_count == 0) {
        return false;
      }

      $prevlink_arr = PrevLink::getAll("WHERE id_usuarios='".$this->id."' AND count='".($this->prevlink_count - 1)."' LIMIT 1");
      $prevlink = $prevlink_arr[0];
      $seccion = new Seccion($prevlink->id_secciones);
      return $prevlink->url;

    }

    public function getUsuariosClientes() {
      return $this->getRelations('clientes',true);
    }

    public function getUsuariosNiveles() {
      return $this->getRelations('niveles',true);
    }

  }
?>
