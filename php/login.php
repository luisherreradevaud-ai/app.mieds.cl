<?php

  include("app.php");

  if( !isset($_POST['email']) || !isset($_POST['password'])) {
    header("Location: ../login.php");
  }

  session_start();
  $usuario = new Usuario;
  $usuario->login($_POST);

  $redirect_url = '';
  if(isset($_POST['redirect-url'])) {
    $redirect_url = $_POST['redirect-url'];
  }


  if($redirect_url != '') {
    header("Location: ..".$redirect_url);
  } else
  if($usuario->nivel == "Cliente") {
    header("Location: ../?s=central-clientes-completa");
  } else
  {
    header("Location: ../?s=calendar");
  }

?>
