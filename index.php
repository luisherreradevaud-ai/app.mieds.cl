<?php


include("./php/app.php");


$usuario = new Usuario;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario->checkSession($_SESSION);
$_SESSION['login'] = "mieds";

if(!validaIdExists($_SESSION,'id')) {
    if($_SERVER['REQUEST_URI'] != '/') {  
        $return_url = new ReturnUrl;
		$return_url->hash = rand(1,999999);
		$return_url->return_url = $_SERVER['REQUEST_URI'];
		$return_url->save();
		header("Location: ./login.php?hash=".$return_url->hash);
    } else {
        header("Location: ./login.php");
    }
}


?>
<!DOCTYPE html>
<!--
  HOW TO USE: 
  data-layout: fluid (default), boxed
  data-sidebar-theme: dark (default), colored, light
  data-sidebar-position: left (default), right
  data-sidebar-behavior: sticky (default), fixed, compact
-->
<html lang="es" data-bs-theme="light" data-layout="fluid" data-sidebar-theme="dark" data-sidebar-position="left" data-sidebar-behavior="sticky">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="MiEDS - Software para Estaciones de Servicio">
	<meta name="author" content="MiEDS">
	<title>MiEDS</title>

	<link rel="canonical" href="https://appstack.bootlab.io/dashboard-default.html" />
	<link rel="shortcut icon" href="img/favicon.ico">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
	<link href="https://cdn.datatables.net/2.1.4/css/dataTables.bootstrap5.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="https://cdn.datatables.net/2.1.4/js/dataTables.js"></script>
	<script src="https://cdn.datatables.net/2.1.4/js/dataTables.bootstrap5.js"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	
	<script>

	document.addEventListener('DOMContentLoaded', (event) => {
        const htmlElement = document.documentElement;
        const switchElement = document.getElementById('darkModeSwitch');

        // Set the default theme to dark if no setting is found in local storage
        const currentTheme = localStorage.getItem('bsTheme') || 'dark';
        htmlElement.setAttribute('data-bs-theme', currentTheme);
        switchElement.checked = currentTheme === 'dark';

        switchElement.addEventListener('change', function () {
            if (this.checked) {
                htmlElement.setAttribute('data-bs-theme', 'dark');
                localStorage.setItem('bsTheme', 'dark');
            } else {
                htmlElement.setAttribute('data-bs-theme', 'light');
                localStorage.setItem('bsTheme', 'light');
            }
        });
    });

    function getDataForm(entity) {
      var data = {};
      $("form#" + entity + "-form :input").each(function(){
       var input = $(this);
       if(input.attr("name")!=undefined) {
         data[input.attr("name")] = input.val();
         if($(this).attr('type') == 'checkbox') {
            data[input.attr("name")] = input.is(":checked");
        }
       };
      });
      return data;
    }

    </script>
	<link href="css/app.css" rel="stylesheet">

	<!-- BEGIN SETTINGS -->
	<!-- Remove this after purchasing 
	<script src="js/settings.js"></script>
	END SETTINGS -->
</head>

<body>
	<!--
    <script src="https://cdn.botpress.cloud/webchat/v2.2/inject.js"></script>
	<script src="https://files.bpcontent.cloud/2025/01/20/21/20250120212606-CDXVGTUJ.js"></script>

	-->
    
	<div class="wrapper">
		<nav id="sidebar" class="sidebar">
			<div class="sidebar-content js-simplebar">
				<a class="sidebar-brand" href="./">
          <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
            width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve">
            <path d="M19.4,4.1l-9-4C10.1,0,9.9,0,9.6,0.1l-9,4C0.2,4.2,0,4.6,0,5s0.2,0.8,0.6,0.9l9,4C9.7,10,9.9,10,10,10s0.3,0,0.4-0.1l9-4
              C19.8,5.8,20,5.4,20,5S19.8,4.2,19.4,4.1z"/>
            <path d="M10,15c-0.1,0-0.3,0-0.4-0.1l-9-4c-0.5-0.2-0.7-0.8-0.5-1.3c0.2-0.5,0.8-0.7,1.3-0.5l8.6,3.8l8.6-3.8c0.5-0.2,1.1,0,1.3,0.5
              c0.2,0.5,0,1.1-0.5,1.3l-9,4C10.3,15,10.1,15,10,15z"/>
            <path d="M10,20c-0.1,0-0.3,0-0.4-0.1l-9-4c-0.5-0.2-0.7-0.8-0.5-1.3c0.2-0.5,0.8-0.7,1.3-0.5l8.6,3.8l8.6-3.8c0.5-0.2,1.1,0,1.3,0.5
              c0.2,0.5,0,1.1-0.5,1.3l-9,4C10.3,20,10.1,20,10,20z"/>
          </svg>
          <span class="align-middle ms-1 me-3">MiEDS</span>
        		</a>
				<ul class="sidebar-nav">
					<?php $usuario->renderMenu(); ?>
				</ul>
			</div>
		</nav>
		<div class="main">
			<nav class="navbar navbar-expand navbar-bg">
				<a class="sidebar-toggle">
          <i class="hamburger align-self-center"></i>
        </a>

				<form class="d-none d-sm-inline-block">
					<div class="input-group input-group-navbar">
						<input type="text" class="form-control search-bar-input" placeholder="Buscar…" aria-label="Search">
						<button class="btn" type="button">
              <i class="align-middle" data-lucide="search"></i>
            </button>
					</div>
				</form>

				<ul class="navbar-nav">
				</ul>

				<div class="navbar-collapse collapse">
					<ul class="navbar-nav navbar-align">

						<!-- Herramientas - Por implementar
						<li class="nav-item dropdown ms-3">
							<a class="nav-link dropdown-toggle d-inline-block text-info" href="#" data-bs-toggle="dropdown">
								<i class="align-middle" data-lucide="calculator"></i>
							</a>
							<div class="dropdown-menu dropdown-menu-end">
								<a class="dropdown-item" href="./?s=reportes">Reportes</a>
							</div>
						</li>
						-->

						<li class="nav-item">
							<div class="form-check form-switch mt-2" style="cursor: pointer">
								<input class="form-check-input d-none" type="checkbox" id="darkModeSwitch" checked>
								<label class="form-check-label" for="darkModeSwitch">
									<i class="align-middle" data-lucide="sun-moon"></i>
								</label>
							</div>
						</li>
						<!--<li class="nav-item">
							<a class="nav-icon" href="#">
								<div class="position-relative">
									<img src="./img/chat-gpt.png" width="25">
								</div>
							</a>
						</li>-->
						
						
						<li class="nav-item dropdown ms-4">
							<a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown" data-bs-toggle="dropdown">
								<div class="position-relative">
									<i class="align-middle text-body" data-lucide="bell"></i>
									<span class="indicator" id="cuentaNotificaciones"></span>
								</div>
							</a>
							<div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="alertsDropdown">

								<div class="dropdown-menu-header" id="tituloNotificaciones">
									0 Nuevas Notificaciones
								</div>

								<div class="list-group" id="divNotificaciones">


								</div>
								<div class="dropdown-menu-footer">
									<a href="./?s=notificaciones" class="text-muted">Ver todas las notificaciones</a>
								</div>
							</div>
						</li>

					


						<!--<li class="nav-item nav-theme-toggle dropdown">
							<a class="nav-icon js-theme-toggle" href="#">
								<div class="position-relative">
									<i class="align-middle text-body nav-theme-toggle-light" data-lucide="sun"></i>
									<i class="align-middle text-body nav-theme-toggle-dark" data-lucide="moon"></i>
								</div>
							</a>
						</li>-->
						
						<li class="nav-item dropdown">
							<a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
								<i class="align-middle" data-lucide="settings"></i>
							</a>

							<a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
								<span><?= $usuario->nombre; ?></span>
								<?php
								if($usuario->nivel == 'Cliente') {
									$cliente = new Cliente($usuario->id_clientes);
									?>
									<br/><small><?= $cliente->nombre; ?></small>
									<?php
								}
								?>
							</a>
							<div class="dropdown-menu dropdown-menu-end">
								<a class="dropdown-item" href="./?s=perfil"><i class="align-middle me-1" data-lucide="user"></i> Perfil</a>
								<?php
								if($usuario->nivel == "Cliente") {
									$usuario_clientes = $usuario->getRelations('clientes');
									?>
									<div class="dropdown-divider"></div>
									<?php
									foreach($usuario_clientes as $id_clientes) {
										$cliente = new Cliente($id_clientes);
										?>
										<a class="dropdown-item cliente-select" href="#" data-idclientes="<?= $id_clientes; ?>">
										<?= ($usuario->id_clientes == $id_clientes) ? '<b>' : ''; ?>
											<?= $cliente->nombre; ?>
										<?= ($usuario->id_clientes == $id_clientes) ? '</b>' : ''; ?>
										</a>
										<?php
									}
								}
								?>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item" href="https://wa.me/34662499585">Ayuda</a>
								<a class="dropdown-item" href="./php/logout.php">Salir</a>
							</div>
						</li>
					</ul>
				</div>
			</nav>

			<main class="content">

			<?php
				switch_templates($section);
			?>
				
			</main>

			<footer class="footer">
				<div class="container-fluid">
					<div class="row text-muted">
						<div class="col-6 text-start">
							<ul class="list-inline">
								<li class="list-inline-item">
									<a class="text-muted" href="https://wa.me/34662499585">Soporte</a>
								</li>
								<li class="list-inline-item">
									<a class="text-muted" href="./politicas-de-privacidad">Privacidad</a>
								</li>
								<li class="list-inline-item">
									<a class="text-muted" href="./terminos-de-servicio">Terminos de Servicio</a>
								</li>
							</ul>
						</div>
						<div class="col-6 text-end">
							<p class="mb-0">
								&copy; 2025 - <a href="./" class="text-muted">MiEDS</a>
							</p>
						</div>
					</div>
				</div>
			</footer>
		</div>
	</div>

	<script>

	$(document).ready(function(){
		getNotificaciones();
	})


    var intervalId = window.setInterval(function(){
      getNotificaciones();
    }, 10000);

    function getNotificaciones() {
      var url = "./ajax/ajax_getNotificaciones.php";
      $.get(url, function(raw) {
		console.log(raw)
        var response = JSON.parse(raw);
        armarNotificaciones(response.obj);
      });
    }

    function setNotificado() {
      var url = "./ajax/ajax_setNotificado.php";
      $.get(url, function(data) {
        $('#cuentaNotificaciones').html('0');
        document.title = "MiEDS";
      },"json");
    }

    function armarNotificaciones(notificaciones) {

      document.getElementById('cuentaNotificaciones').innerHTML = notificaciones['sinleer'].length;

      if(notificaciones['sinleer'].length > 0) {
        document.title = "(" + notificaciones['sinleer'].length + ") MiEDS";
		document.getElementById('tituloNotificaciones').innerHTML = notificaciones['sinleer'].length + " nuevas notificaciones";
      } else {
        document.title = "MiEDS";
		document.getElementById('tituloNotificaciones').innerHTML = "No hay nuevas notificaciones";
      }

      div_notificaciones = document.getElementById("divNotificaciones");
      div_notificaciones.innerHTML = '';

      var notificaciones_todas = notificaciones['sinleer'].concat(notificaciones['leidas']);


      for ( var i = 0; i < notificaciones_todas.length; i++) {

        a = document.createElement("a");
        a.classList.add("list-group-item");
        a.href = notificaciones_todas[i]['link'];
        div_notificaciones.appendChild(a);

        div1 = document.createElement("div");
        div1.classList.add("row");
		div1.classList.add("g-0");
		div1.classList.add("align-items-center");
        a.appendChild(div1);

        div2 = document.createElement("div");
        div2.classList.add("col-2");
        div1.appendChild(div2);

        ih = document.createElement("i");
        ih.classList.add("text-danger");
		ih.setAttribute('data-lucide','alert-circle');
        div2.appendChild(ih);

		div3 = document.createElement("div");
        div3.classList.add("col-10");
        div1.appendChild(div3);

		div4 = document.createElement("div");
        div4.innerHTML = notificaciones_todas[i]['texto'] ;
        div3.appendChild(div4);

		div5 = document.createElement("div");
        div1.classList.add("text-muted");
		div1.classList.add("small");
		div1.classList.add("mt-1");
        div5.innerHTML = notificaciones_todas[i]['creada'];
        div3.appendChild(div5);

      }

    }

    $('#alertsDropdown').click(()=>{
    	setNotificado();
    });

	$(document).on('keyup','.acero',function(){
		$(this).val($(this).val().replace(/\D/g,''));
	});

	$(document).on('change','.acero',function(){
		if($(this).val() == "") {
			$(this).val(0);
		}
		$(this).val(parseInt($(this).val()));
	});

	$( ".search-bar-input" ).autocomplete({
		minLength: 2,
		source: './ajax/ajax_getSearch.php',
		focus: function( event, ui ) {
			$( ".search-bar-input" ).val( ui.item.nombre );
			return false;
		},
		select: function( event, ui ) {
			var item = ui.item;
			window.location.href = item.link;
		}
		})
		.autocomplete( "instance" )._renderItem = function( ul, response ) {
		console.log(response);
		var item = response['producto'];
		return $( "<div style='z-index: 200;background-color: white; padding: 5px; cursor: pointer; width: 400px'>" )
			.append( "<div style='background-color: white'>" + response['titulo'] + "</div>" )
			.appendTo( ul );
	};

	$(document).on('click','.cliente-select',function(e) {

		e.preventDefault();

		var url = "./ajax/ajax_cambiarUsuarioCliente.php";
		var data = {
			'id_clientes': $(e.currentTarget).data('idclientes')
		};
		console.log(data);

		$.post(url,data,function(raw){
			console.log(raw);
			var response = JSON.parse(raw);
			if(response.status!="OK") {
				alert("Algo fallo");
				return false;
			} else {
				window.location.reload();
			}
		}).fail(function(){
			alert("No funciono");
		});
	})





	</script>
	<script src="js/app.js"></script>
	<!--<script src="js/kanban.js"></script>
	<script src="js/kanban-task-functions.js"></script>-->



</body>

</html>