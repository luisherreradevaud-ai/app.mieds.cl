<?php

$msg = 0;

if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}

$url = '';
if(isset($_GET['url'])) {
  $url = $_GET['url'];
}

?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark" data-layout="fluid" data-sidebar-theme="dark" data-sidebar-position="left" data-sidebar-behavior="sticky">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Mejora la eficacia de tu gestion con MiEDS - Software para Estaciones de Servicio">
	<meta name="author" content="MiEDS">

	<title>Login - MiEDS Software para Estaciones de Servicio</title>

	<link rel="canonical" href="https://appstack.bootlab.io/auth-sign-in-cover.html" />
	<link rel="shortcut icon" href="img/favicon.ico">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<link href="css/app.css" rel="stylesheet">

	<!-- BEGIN SETTINGS -->
	<!-- Remove this after purchasing -->
	<!-- END SETTINGS -->
</head>

<body>
	<div class="container-fluid p-0">
		<div class="row g-0">
			<div class="col-xl-6 d-none d-xl-flex">
				<div class="auth-full-page position-relative">
					<img src="img/gasolineras.jpg" class="auth-bg" alt="Unsplash">
					<div class="auth-quote">
						<i data-lucide="quote"></i>
						<?php
						$testimonios = [
							["texto" => "Llevo casi seis meses utilizando MiEDS en mi estacion de servicio y la diferencia es abismal.", "autor" => "Rodrigo, concesionario Copec"],
							["texto" => "Desde que implementamos MiEDS, el control de turnos y arqueos es mucho mas eficiente y preciso.", "autor" => "Carolina, administradora de estacion"],
							["texto" => "La gestion de faltantes y anticipos se simplifico enormemente, ahora todo esta centralizado.", "autor" => "Andres, jefe de operaciones"],
							["texto" => "El modulo de conteo de efectivo nos permite cerrar turnos en minutos sin errores de calculo.", "autor" => "Martin, supervisor de turno"],
							["texto" => "MiEDS transformo nuestra operacion, ahora tenemos visibilidad total de cada turno y atendedor.", "autor" => "Francisca, gerente de estacion"],
							["texto" => "El registro de ingresos PROSEGUR y facturas a credito es automatico y sin complicaciones.", "autor" => "Pablo, contador"],
							["texto" => "El sistema de comisiones mensuales nos ha ahorrado horas de trabajo manual y errores.", "autor" => "Claudia, jefa de RRHH"],
							["texto" => "Con MiEDS logramos controlar multiples islas de despacho sin perder detalle de las operaciones.", "autor" => "Diego, dueno de estacion"],
							["texto" => "El flujo de aprobacion de turnos nos da tranquilidad y control sobre las operaciones diarias.", "autor" => "Valentina, auditora interna"],
							["texto" => "El seguimiento de gastos caja chica y donaciones mejoro nuestra rendicion de cuentas.", "autor" => "Jose, administrador regional"]
						];
						$testimonioAleatorio = $testimonios[array_rand($testimonios)];
						?>
					<!--<figure>
							<blockquote>
								<p><?= $testimonioAleatorio['texto'] ?></p>
							</blockquote>
							<figcaption>
								— <?= $testimonioAleatorio['autor'] ?>
							</figcaption>
						</figure>-->
					</div>
				</div>
			</div>
			<div class="col-xl-6">
				<div class="auth-full-page d-flex p-4 p-xl-5">
					<div class="d-flex flex-column w-100 h-100">
						<div class="auth-form">

                            <div>
                                <?php
                                if($msg == 1) {
                                ?>
                                <div class="alert alert-danger" role="alert" style="width: 300px">Contrase&ntilde;a incorrecta.</div>
                                <?php
                                } else
                                if($msg == 2) {
                                ?>
                                <div class="alert alert-info" role="alert" style="width: 300px">Se ha enviado un correo para recuperar su cuenta.</div>
                                <?php
                                } else
                                if($msg == 3) {
                                ?>
                                <div class="alert alert-danger" role="alert" style="width: 300px">El email no corresponde.</div>
                                <?php
                                } else
                                if($msg == 4) {
                                ?>
                                <div class="alert alert-info" role="alert" style="width: 300px">Contrase&ntilde;a seteada con &eacute;xito.</div>
                                <?php
                                } else
                                if($msg == 5) {
                                ?>
                                <div class="alert alert-info" role="alert" style="width: 300px">Contrase&ntilde;a seteada con &eacute;xito.</div>
                                <?php
                                }
                                ?>
                            </div>

							<div class="text-center">
								<h1 class="h2">¡Bienvenido!</h1>
								<p class="lead">
									Ingresa a tu cuenta para continuar.
								</p>
							</div>

							<div class="mb-3">

									<div class="mb-3">
										<label class="form-label">Email</label>
										<input id="login-email" class="form-control form-control-lg" type="email" name="email" placeholder="Ingresa tu email" />
									</div>
									<div class="mb-3">
										<label class="form-label">Contraseña</label>
										<input id="login-password" class="form-control form-control-lg" type="password" name="password" placeholder="Ingresa tu password" />
										<small>
                                            <a href="#" id="olvidePasswordBtn">¿Olvidaste tu contraseña?</a>
                                        </small>
									</div>

							</div>
                            <div class="d-grid gap-2 mt-3">
                                <button" id="login-btn" class="btn btn-lg btn-primary">Ingresar</button>
                            </div>

						</div>
						<div class="text-center">
							<p class="mb-0">
								&copy; 2025 - <a href="index.php">MiEDS</a>
							</p>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>

    <form action="./php/login.php" method="POST" id="loginForm">
      <input type="hidden" id="hidden_email" name="email" value="">
      <input type="hidden" id="hidden_password" name="password" value="">
      <input type="hidden" id="hidden_tipo_login" name="tipo_login" value="">
      <input type="hidden" name="redirect-url" value="<?= $url; ?>">
    </form>

    <div class="modal fade bd-example-modal" id="olvidePasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="exampleModalLabel">Recuperar cuenta</h4>

            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="align-center"><center>
                <div class="form-group mt-20 subscribe-form">
                  <input type="text" class="form-control" id="olvidePasswordEmailTxt" name="email" placeholder="Email">
                  <small class="text-muted mt-1">Se enviar&aacute;n instrucciones para recuperar tu cuenta a tu correo.</small>
                </div>
              </div>
              <div class="mt-2 mb-1"><center>
                <button class="btn btn-primary w-100 btn-lg" id="olvide-password-modal-btn">Aceptar</button>
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="modal fade bd-example-modal" id="result-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="exampleModalLabel">Recuperar cuenta</h4>

          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="result-modal-text">
            </div>
          </div>
        </div>
      </div>



    <script>

    $('#olvidePasswordBtn').click(()=>{
      $('#olvidePasswordModal').modal('toggle');
    });
    $('#olvidePasswordModalBtn').click(()=>{
      $('#olvidePasswordForm').submit();
    });
    $('#olvidePasswordEmailTxt').change(()=>{
      if($('#olvidePasswordEmailTxt').val()=="") {
        $('#olvidePasswordModalBtn').attr('DISABLED',true);
      } else {
        $('#olvidePasswordModalBtn').attr('DISABLED',false);
      }
    });

    $('#emailTxt').keyup(()=>{
      if($('#emailTxt').val()!=''&&$('#passwordTxt').val()!='') {
        $('#loginBtn').attr('DISABLED',false);
      } else {
        $('#loginBtn').attr('DISABLED',true);
      }
    });
    $('#passwordTxt').keyup(()=>{
      if($('#emailTxt').val()!=''&&$('#passwordTxt').val()!='') {
        $('#loginBtn').attr('DISABLED',false);
      } else {
        $('#loginBtn').attr('DISABLED',true);
      }
    });

    $('#login-btn').click(()=>{
      $('#hidden_email').val($('#login-email').val());
      $('#hidden_password').val($('#login-password').val());
      $('#hidden_tipo_login').val('');
      $('#loginForm').submit();
    });

    $(document).on('click','#olvide-password-modal-btn',function(e){

      e.preventDefault();

      var email = $('#olvidePasswordEmailTxt').val();

      if(!email.includes('@')) {
        alert("Ingrese un correo valido.");
        return false;
      }

      if(!email.includes('.')) {
        alert("Ingrese un correo valido.");
        return false;
      }

      if(email.length < 5) {
        alert("El email debe tener mas de 5 caracteres.");
        return false;
      }

      var data = {
        'email': email
      };

      var url = "./ajax/ajax_setRecuperacion.php";

      $.post(url,data,function(response){
        console.log(response);
        $('#olvidePasswordModal').modal('toggle');
        $('#result-modal').modal('toggle');
        $('#result-modal-text').html(response.mensaje)
        console.log(response);
        if(response.status!="OK") {

        }
      },'json').fail(function(){
        alert("No funciono");
      });

    });


    </script>
	<script src="js/app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

</body>

</html>
