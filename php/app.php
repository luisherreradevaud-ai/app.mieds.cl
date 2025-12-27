<?php

///// ERRORS

$debug = 1;

if($debug || isset($_GET['debug'])) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  $debug = 1;
}

////// Sesion

/////

date_default_timezone_set('America/Santiago');


  $mysqli_user = "miedscl_prod"; 
  $mysqli_pass = "ybcqjEwzueXQ";
  $mysqli_db = "miedscl_prod";


// Definir base_dir antes del autoloader
if($_SERVER['HTTP_HOST']=="localhost") {
  $base_dir = realpath($_SERVER["DOCUMENT_ROOT"])."/app.mieds.cl";
  $mysqli_user = "barrcl_cocholg"; 
  $mysqli_pass = "rglgd8ZdWWiP";
  $mysqli_db = "barrcl_cocholg";
} else {
  $base_dir = realpath($_SERVER["DOCUMENT_ROOT"]);
}

spl_autoload_register(function($clase){
  // Ignorar clases con namespace (como Dompdf\*, FontLib\*, Svg\*)
  if(strpos($clase, '\\') !== false) {
    return;
  }
  $ruta = $GLOBALS['base_dir']."/php/classes/".$clase.".php";
  if(is_readable($ruta)){
    require_once($ruta);
  }
});

// Initialize security system
//Security::init();

/*
session_start();
$usuario = new Usuario;
$usuario->checkSession();*/

///// CONSTANTES

$passwordHash = "mister420";
$transbank['codigo_comercio'] = "597047933778";
$transbank['api_secret_key'] = "b520a2a3-59e9-476e-8c01-9df2a231658b";


$niveles_usuario = array(
  "Administrador",
  "Concesionario",
  "Visita"
);

$tipos_de_gasto = array(
  "General"
);


$secciones_clasificacion = [
  'Administraci&oacute;n',
  'Central Cliente',
  'Usuarios',
  'Tareas',
  'Supervisi&oacute;n',
  'Configuraci&oacute;n',
  "Sistema"
];

$ajax_route = $base_dir."/ajax/";

///// MYSQLI

$mysqli = new mysqli("localhost",$mysqli_user,$mysqli_pass,$mysqli_db);
$mysqli->set_charset('utf8mb4');

///// SECTIONS

///////////////////////////////////////////////////////////////////////////////

function sanitize_input($input) {
  //$input = mysql_real_escape_string($input);
  //$input = strip_tags($input);
  $input = htmlspecialchars($input);
  return $input;
}

///////////////////////////////////////////////////////////////////////////////

function incluir_template( $template ) {
  $PATH_TEMPLATES = "templates/";
  $file_path = $PATH_TEMPLATES.$template.".php";
  if(file_exists($file_path)) {
    include($file_path);
  } else {
    include($PATH_TEMPLATES."404.php");
  }
  
}

function switch_templates( $argv, $ambiente = NULL ) {

  if(!$ambiente) {
    $ambiente = "web";
  }

  $section = sanitize_input($argv);
  if($GLOBALS['usuario']->checkAutorizacion($section)) {
    incluir_template($section);
  } else {
    print "Usuario no autorizado";
  }
  

}

///////////////////////////////////////////////////////////////////////////////

$cuenta_media = 0;

function mediaIteration($cantidad_media) {
  if($GLOBALS['cuenta_media']==$cantidad_media) {
    $GLOBALS['cuenta_media'] = 0;
    return 0;
  } else {
    $GLOBALS['cuenta_media']++;
    return $GLOBALS['cuenta_media']-1;
  }
}

function replaceTildes($input) {
  $input = str_replace("á","&aacute;",$input);
  $input = str_replace("é","&eacute;",$input);
  $input = str_replace("í","&iacute;",$input);
  $input = str_replace("ó","&oacute;",$input);
  $input = str_replace("ú","&uacute;",$input);
  $input = str_replace("Á","&Aacute;",$input);
  $input = str_replace("É","&Eacute;",$input);
  $input = str_replace("Í","&Iacute;",$input);
  $input = str_replace("Ó","&Oacute;",$input);
  $input = str_replace("Ú","&Uacute;",$input);
  return $input;
}

function quitaTildes($input) {
  $input = str_replace("á","a",$input);
  $input = str_replace("é","e",$input);
  $input = str_replace("í","i",$input);
  $input = str_replace("ó","o",$input);
  $input = str_replace("ú","u",$input);
  $input = str_replace("Á","A",$input);
  $input = str_replace("É","E",$input);
  $input = str_replace("Í","I",$input);
  $input = str_replace("Ó","O",$input);
  $input = str_replace("Ú","U",$input);
  $input = str_replace("&acute;","a",$input);
  $input = str_replace("&ecute;","e",$input);
  $input = str_replace("&icute;","i",$input);
  $input = str_replace("&ocute;","o",$input);
  $input = str_replace("&ucute;","u",$input);
  $input = str_replace("&Acute;","A",$input);
  $input = str_replace("&Ecute;","E",$input);
  $input = str_replace("&Icute;","I",$input);
  $input = str_replace("&Ocute;","O",$input);
  $input = str_replace("&Ucute;","U",$input);
  return $input;
}

function text2html($text) {

  $r_text = explode("\n\n",$text);
  $texto_html = "";

  foreach($r_text as $element) {

    if(strtoupper(quitaTildes($element))==quitaTildes($element)) {

      $titulo_html = "<h4>".ucwords(strtolower(replaceTildes($element)))."</h4>";
      $texto_html = $texto_html.$titulo_html;

    } else if(strlen($element) > 0 && $element[0]=="-"){

      $lista = explode("\n",$element);

      $texto_html = $texto_html."<ul class='list-1'>";

      foreach($lista as $li) {
        $li[0] = " ";
        $texto_html = $texto_html."<li>".$li."</li>";
      }

      $texto_html = $texto_html."</ul>";

    } else {

      $texto_html = $texto_html."<p>".replaceTildes($element)."</p>";

    }

  }

  return $texto_html;
}

function date2fecha($date) {
  if($date == "0000-00-00") {
    return "-";
  }
  $r_date = explode("-",$date);
  if(!is_array($r_date)) {
    print "-";
  }
  if(count($r_date)!=3) {
    return "-";
  }
  return $r_date[2]."/".$r_date[1]."/".$r_date[0];
}

function datetime2fechayhora($datetime) {
  $r_datetime = explode(" ",$datetime);
  if(!is_array($r_datetime)) {
    print "-";
  }
  if(count($r_datetime)!=2) {
    return "-";
  }
  return date2fecha($r_datetime[0])." ".$r_datetime[1];
}

function datetime2fecha($datetime) {
  $r_datetime = explode(" ",$datetime);
  return date2fecha($r_datetime[0]);
}

function fecha2date($fecha) {
  $r_fecha = explode("/",$fecha);
  if(count($r_fecha)==3){
    if( strlen($r_fecha[2]) == 4 && strlen($r_fecha[1]) == 2 && strlen($r_fecha[0]) == 2 ){
      if( is_numeric($r_fecha[2]) && is_numeric($r_fecha[1]) && is_numeric($r_fecha[0]) ) {
        $fecha = $r_fecha[2]."-".$r_fecha[1]."-".$r_fecha[0];
      }
    }
  }
  return $fecha;
}


  function mes2int($mes) {
    $month = "";
    switch($mes) {
      case "Enero":
        $month = "01";
        break;
      case "Febrero":
        $month = "02";
        break;
      case "Marzo":
        $month = "03";
        break;
      case "Abril":
        $month = "04";
        break;
      case "Mayo":
        $month = "05";
        break;
      case "Junio":
        $month = "06";
        break;
      case "Julio":
        $month = "07";
        break;
      case "Agosto":
        $month = "08";
        break;
      case "Septiembre":
        $month = "09";
        break;
      case "Octubre":
        $month = "10";
        break;
      case "Noviembre":
        $month = "11";
        break;
      case "Diciembre":
        $month = "12";
        break;
    }
    return $month;
  }
  function int2mes($mes) {
    $mes = $mes + 0;
    $month = "";
    switch($mes) {
      case 1:
        $month = "Enero";
        break;
      case 2:
        $month = "Febrero";
        break;
      case 3:
        $month = "Marzo";
        break;
      case 4:
        $month = "Abril";
        break;
      case 5:
        $month = "Mayo";
        break;
      case 6:
        $month = "Junio";
        break;
      case 7:
        $month = "Julio";
        break;
      case 8:
        $month = "Agosto";
        break;
      case 9:
        $month = "Septiembre";
        break;
      case 10:
        $month = "Octubre";
        break;
      case 11:
        $month = "Noviembre";
        break;
      case 12:
        $month = "Diciembre";
        break;
    }
    return $month;
  }

  function date2fechaEscrita($date) {

  if($date == "0000-00-00") {
    return "-";
  }

  $r_date = explode("-",$date);
  if(!is_array($r_date)) {
    print "-";
  }

  if(count($r_date)!=3) {
    return "-";
  }

  $str = '';

  switch(intval(date('w',strtotime($date)))){
    case 0:
      $str .= 'Domingo';
      break;
    case 1:
      $str .= 'Lunes';
      break;
    case 2:
      $str .= 'Martes';
      break;
    case 3:
      $str .= 'Miercoles';
      break;
    case 4:
      $str .= 'Jueves';
      break;
    case 5:
      $str .= 'Viernes';
      break;
    case 6:
      $str .= 'Sabado';
      break;
  }

  $str .= " ".$r_date[2]." de ".int2mes($r_date[1]).", ".$r_date[0];
    
  return $str;
}

///////////////////////////////////////////////////////////////////////////////


function validaFechas($fechas) {

  $explode_fechas = explode(" - ", $fechas);
  if( count($explode_fechas)==2 ) {
    for( $i=0; $i<2; $i++) {
      $explode_fecha[$i] = explode("/",$explode_fechas[$i]);
      if( count($explode_fecha[$i])==3 ) {
        if( $explode_fecha[$i][1] > 0 && $explode_fecha[$i][1] <= 12 ) {
          if( $explode_fecha[$i][2] >= date("Y") ) {
            $dias_mes = cal_days_in_month(CAL_GREGORIAN, $explode_fecha[$i][1], $explode_fecha[$i][2]);
            if( $explode_fecha[$i][0] > 0 && $explode_fecha[$i][0] <= $dias_mes ) {
              return true;
            }
          }
        }
      }
    }
  }
  return false;
}

function validaFecha($fecha) {

  $explode_fecha = explode("/",$fecha);
  if( count($explode_fecha)==3 ) {
    if( $explode_fecha[1] > 0 && $explode_fecha[1] <= 12 ) {
      if( $explode_fecha[2] >= date("Y") ) {
        $dias_mes = cal_days_in_month(CAL_GREGORIAN, $explode_fecha[1], $explode_fecha[2]);
        if( $explode_fecha[0] > 0 && $explode_fecha[0] <= $dias_mes ) {
          return true;
        }
      }
    }
  }
  return false;
}

function validaNumerico($numerico) {
  if(!is_numeric($numerico)) {
    return false;
  }
  return true;
}

function validaId($id) {
  if(is_numeric($id)) {
    if($id>0) {
      return true;
    }
  }
  return false;
}

function validaEmail($email) {
  $explode_email = explode("@",$email);
  if(count($explode_email)=="2") {
    $explode_domain = explode(".",$explode_email[1]);
    if(count($explode_domain)>1) {
      return true;
    }
  }
  return false;
}

if (isset($_GET['s'])) {
  $section = $_GET['s'];
} else
if (isset($_POST['s'])) {
  $section = $_POST['s'];
} else {
  $section = "inicio";
}

function validaIdExists($array,$index) {
  if(!is_array($array)) {
    return false;
  }
  if(isset($array[$index])) {
    if(is_numeric($array[$index])) {
      if($array[$index]>0) {
        return true;
      }
    }
  }
  return false;
}

function validaIdExistsVarios($array,$indexes) {
  if(!is_array($array)||!is_array($indexes)) {
    return false;
  }
  foreach($indexes as $index) {
    if(validaIdExists($array,$index)) {
      continue;
    } else {
      return false;
    }
  }
  return true;
}

function postData($url,$data) {
  $postvars = http_build_query($data);
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, count($data));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);

  $result = curl_exec($ch);

  curl_close($ch);
}



function include404() {
  $base_dir = $GLOBALS['base_dir'];
  include("./templates/404.php");
  die();
}


function curl_get_file_contents($URL) {
  $c = curl_init();
  curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($c, CURLOPT_URL, $URL);
  $contents = curl_exec($c);
  curl_close($c);
  if ($contents) {
    return $contents;
  } else {
    return false;
  }
}

function createObjFromTableName($table_name,$id){

  if($table_name=="atendedores") {
    $obj = new Atendedor($id);
  } else
  if($table_name=="clientes") {
    $obj = new Cliente($id);
  } else
  if($table_name=="usuarios") {
    $obj = new Usuario($id);
  } else
  if($table_name=="gastos") {
    $obj = new Gasto($id);
  } else
  if($table_name=="tipos_de_gastos") {
    $obj = new TipoDeGasto($id);
  } else
  if($table_name=="tareas") {
    $obj = new Tarea($id);
  } else
  if($table_name=="usuarios_nivel") {
    $obj = new UsuarioNivel($id);
  } else
  if($table_name=="configuraciones") {
    $obj = new Configuracion($id);
  } else 
  if($table_name=="documentos") {
    $obj = new Documento($id);
  } else 
  if($table_name=="tareas_comentarios") {
    $obj = new TareaComentario($id);
  } else 
  if($table_name=="kanban_tableros") {
    $obj = new KanbanTablero($id);
  } else
  if($table_name=="kanban_columnas") {
    $obj = new KanbanColumna($id);
  } else
  if($table_name=="kanban_tareas") {
    $obj = new KanbanTarea($id);
  } else
  if($table_name=="kanban_tareas_usuarios") {
    $obj = new KanbanTareaUsuario($id);
  } else
  if($table_name=="kanban_tareas_etiquetas") {
    $obj = new KanbanTareaEtiqueta($id);
  } else
  if($table_name=="kanban_etiquetas") {
    $obj = new KanbanEtiqueta($id);
  } else
  if($table_name=="menus") {
    $obj = new Menu($id);
  } else
  if($table_name=="secciones") {
    $obj = new Seccion($id);
  } else
  if($table_name=="tableros") {
    $obj = new Tablero($id);
  }

  return $obj;

}

 ?>
