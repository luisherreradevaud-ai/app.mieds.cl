<?php

error_reporting(E_ALL & ~E_WARNING);
ini_set('display_errors', 0);

  class Email {

    public $headers;
    public $remitente;
    public $destinatario;
    public $asunto;
    public $mensaje;

    public function __construct() {
      $this->remitente = "contacto@cervezacocholgue.cl";
      $this->headers = "From: Cerveza Cocholgue <" . strip_tags($this->remitente) . ">\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    }

    public function enviarMail() {
      mail($this->destinatario, $this->asunto, $this->mensaje, $this->headers);
    }

    public function mailVerificacion($verificacion) {

      $this->asunto = 'Verifica tu cuenta en Cerveza Cocholgue';
      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
          <center>
          <img src='https://www.cervezacocholgue.cl/img/navbar-logo.png' width='200'>
          <br /><br />
          Estimado(a):<br /><br />
          Has creado una cuenta en <a href='https://cervezacocholgue.cl/'>cervezacocholgue.cl</a>.
          Solo falta que la verifiques haciendo click en el siguiente enlace:
            <br /><br />
            <a href='https://www.cervezacocholgue.cl/verificacioncuenta/?key=".$verificacion."'>
            https://www.cervezacocholgue.cl/verificacioncuenta/?key=".$verificacion."</a>
            <br />
            <br />Si no has creado una cuenta en Cerveza Cocholgue, ignora este correo.
            </center>
          </body>
        </html>";


    }

    public function mailBienvenida($nombre) {

      $this->asunto =  'Bienvenido a Cerveza Cocholgue';
      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
          <center>
            <img src='https://www.cervezacocholgue.cl/img/navbar-logo.png' width='200'>
            <br /><br />
            Estimado(a) ".$nombre.":<br /><br />
            Tu cuenta en Cerveza Cocholgue se ha creado.
          </center>
        </body>
      </html>";

    }

    public function enviarCorreoRecuperacion($usuario) {

      $this->destinatario = $usuario->email;
      $this->asunto =  'Cerveza Cocholgue // Recuperacion de cuenta';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
          <center>
            <img src='https://app.mieds.cl/img/img/cocholgue.png' width='200'>
            <br /><br />
            Estimado(a) ".$usuario->nombre.":<br /><br />
            Has solicitado recuperar tu cuenta. Sigue el link que esta
            <a href='https://app.mieds.cl/recuperar.php?recuperacion=".$usuario->recuperacion."'>ac&aacute;</a>.
          </center>
        </body>
      </html>";

      $this->enviarMail();

    }

    public function setMailInvitacion($usuario) {

      $this->asunto =  'Invitacion a Cerveza Cocholgue';
      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>

            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado Cliente:<br />
            Esperamos que est&eacute; bien. Nos complace invitarle a registrarse como usuario en nuestra
            WebApp de Cerveza Cocholgue. Aqu&iacute; le explicamos como hacerlo:<br/>
            Ingrese al siguiente <a href='https://app.mieds.cl/crear.php?h=".$usuario->invitacion."'>
            LINK</a>.<br/>
            Genere su clave personal e intransferible.<br />
            Acceda a la WebApp Cocholgue para ver su estado de cuenta, facturaci&oacute;n, gestionar pedidos y realizar pagos.<br />
            Nos entusiasma que se una a nuestra plataforma y esperamos que disfrute la experiencia.<br /><br />
            Si tiene alguna pregunta, por favor, no dude en ponerse en contacto con nosotros.<br /><br />
            Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

    }

    public function enviarCorreosPedido($pedido) {

      $cliente = new Cliente($pedido->id_clientes);
      $pedidos_productos = PedidoProducto::getAll("WHERE id_pedidos='".$pedido->id."'");

      $this->asunto =  'Cerveza Cocholgue // Recepcion de Pedido';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado Cliente:<br /><br />
            Nos complace informarle que hemos recibido su pedido de Cerveza Cocholgue con &eacute;xito. Aqu&iacute; estan los detalles de su pedido:<br /><br />
            <table style='width: 100%;' border=1>";
              foreach($pedidos_productos as $pp) {
                $this->mensaje .=
                "<tr>
                  <td>
                    ".$pp->tipo."
                  </td>
                  <td>
                    ".$pp->cantidad."
                  </td>
                  <td>
                    ".$pp->tipos_cerveza."
                  </td>";
              }
      $this->mensaje .=  "</table><br/><br/>
        Estamos preparando su pedido para enviarlo lo m&aacute;s pronto posible. Una vez que su
        pedido est&eacute; en camino, recibir&aacute; una notificaci&oacute;n con los detalles del
        env&iacute;o.<br/>
        Si tiene alguna duda o necesita informaci&oacute;n adicional, no dude en ponerse en contacto con nosotros.<br />
        Gracias por elegir Cerveza Cocholgue. Esperamos que disfrute nuestros productos.<br/><br/>
        Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $correos = explode(',',$cliente->email);
      foreach($correos as $correo) {
        $this->destinatario = $correo;
        $this->enviarMail();
      }

    }


    public function enviarCorreosPedidoRespaldo($pedido) {

      $cliente = new Cliente($pedido->id_clientes);
      $pedidos_productos = PedidoProducto::getAll("WHERE id_pedidos='".$pedido->id."'");

      $this->asunto =  'Cerveza Cocholgue // Recepcion de Pedido';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Nuestro cliente <b>".$cliente->nombre."</b> ha realizado un <b>pedido</b> por medio de nuestra plataforma,
            dirijase a revisarlo en la <a href='https://app.mieds.cl' target='_BLANK'>WebApp</a>.
            <br /><br/>
            <table style='width: 100%;' border=1>";
              foreach($pedidos_productos as $pp) {
                $this->mensaje .=
                "<tr>
                  <td>
                    ".$pp->tipo."
                  </td>
                  <td>
                    ".$pp->cantidad."
                  </td>
                  <td>
                    ".$pp->tipos_cerveza."
                  </td>";
              }
      $this->mensaje .=  "</table>
        </body>
      </html>";

      $this->destinatario = "matiashmecanico@gmail.com";
      $this->enviarMail();
      $this->destinatario = "rodriarriagadah@gmail.com";
      $this->enviarMail();

    }

    public function enviarCorreoSugerencia($sugerencia) {

      $cliente = new Cliente($sugerencia->id_clientes);
      $usuario = new Usuario($sugerencia->id_usuarios);

      $this->asunto =  'Cerveza Cocholgue // Recepcion de Reclamo o Solicitud';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado Cliente:<br />
            Hemos recibido su reclamo o solicitud y queremos agradecerle por tomarse el tiempo para
            comunicarse con nosotros. Aqu&iacute; est&aacute;n los detalles de su comunicaci&oacute;n:<br /><br/>
            <b>".$sugerencia->contenido."</b><br/><br/>
            Estamos revisando la informaci&oacute;n proporcionada y trabajaremos para resolver el problema
            o atender su solicitud lo m&aacute;s pronto posible. Nuestro compromiso es responder a todas
            las consultas dentro de un plazo de 3 d&iacute;as h&aacute;biles.<br/>
            Si tiene alguna pregunta adicional mientras trabajamos en su reclamo o solicitud, no dude en
            ponerse en contacto con nosotros.<br/>
            Apreciamos su paciencia y comprensi&oacute;n.<br/><br/>
            Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $correos = explode(',',$cliente->email);
      foreach($correos as $correo) {
        $this->destinatario = $correo;
        $this->enviarMail();
      }

      $this->destinatario = $usuario->email;
      $this->enviarMail();

    }


    public function enviarCorreoSugerenciaRespaldo($sugerencia) {

      $cliente = new Cliente($sugerencia->id_clientes);

      $this->asunto =  'Cerveza Cocholgue // Recepcion de Reclamo o Solicitud';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Nuestro cliente <b>".$cliente->nombre."</b> ha realizado una <b>sugerencia o reclamo</b> por medio de nuestra plataforma,
            dirijase a revisarlo en la <a href='https://app.mieds.cl' target='_BLANK'>WebApp</a>.
            <br /><br/>
            <b>".$sugerencia->contenido."</b><br/><br/>
        </body>
      </html>";

      $this->destinatario = "matiashmecanico@gmail.com";
      $this->enviarMail();
      $this->destinatario = "rodriarriagadah@gmail.com";
      $this->enviarMail();

    }

    public function enviarCorreosEntrega($entrega) {

      $cliente = new Cliente($entrega->id_clientes);
      $entregas_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");

      $this->asunto =  'Cerveza Cocholgue // Confirmacion de Entrega';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado Cliente:<br /><br />
            Nos alegra informarle que su pedido de Cerveza Cocholgue ha sido entregado con &eacute;xito. Aqu&iacute; estan los detalles de su entrega:<br /><br />
            <table style='width: 100%;' border=1>";
              foreach($entregas_productos as $ep) {
                $this->mensaje .=
                "<tr>
                  <td>
                    ".$ep->tipo."
                  </td>
                  <td>
                    ".$ep->cantidad."
                  </td>
                  <td>
                    ".$ep->tipos_cerveza."
                  </td>";
              }
      $this->mensaje .=  "</table><br/><br/>
      Esperamos que disfrute nuestros productos y esperamos tener la oportunidad de servirle nuevamente en el futuro.
      Si tiene alg&uacute;n comentario o sugerencia sobre nuestros productos o nuestro servicio, no dude en compartirlo con nosotros.
      Su opini&oacute;n es muy valiosa para nosotros.<br />
      Si tiene alguna duda o necesita asistencia despu&eacute;s de la entrega, estamos a su disposici&oacute;n para ayudarlo.<br />
      Gracias por elegir Cerveza Cocholgue. Apreciamos su negocio.<br/><br/>
        Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $correos = explode(',',$cliente->email);
      foreach($correos as $correo) {
        $this->destinatario = $correo;
        $this->enviarMail();
      }

    }


    public function enviarCorreosEntregaRespaldo($entrega) {

      $cliente = new Cliente($entrega->id_clientes);
      $entregas_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");

      $this->asunto =  'Cerveza Cocholgue // Confirmacion de Entrega';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Se ha realizado una entrega al cliente <b>".$cliente->nombre."</b>:<br /><br />
            <table style='width: 100%;' border=1>";
              foreach($entregas_productos as $ep) {
                $this->mensaje .=
                "<tr>
                  <td>
                    ".$ep->tipo."
                  </td>
                  <td>
                    ".$ep->cantidad."
                  </td>
                  <td>
                    ".$ep->tipos_cerveza."
                  </td>";
              }
      $this->mensaje .=  "</table><br/><br/>
        </body>
      </html>";

      $this->destinatario = "matiashmecanico@gmail.com";
      $this->enviarMail();
      $this->destinatario = "rodriarriagadah@gmail.com";
      $this->enviarMail();

    }


    public function enviarCorreoPago($pago) {

      $cliente = new Cliente($pago->id_clientes);

      $this->asunto =  'Cerveza Cocholgue // Recepcion de Pago Exitoso';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado Cliente:<br />
            Nos complace informarle que hemos recibido su pago con &eacute;xito. Aqu&iacute; están los detalles:<br /><br/>
            <table style='width:100%' border=1>
              <tr>
                <td>
                  N&uacte;mero de factura
                </td>
                <td>
                  Fecha de vencimiento
                </td>
                <td>
                  Monto pagado
                </td>
              </tr>
              <tr>
                <td>
                  <b>".$pago->facturas."</b>
                </td>
                <td>
                  <b>".datetime2fecha($pago->creada)."</b>
                </td>
                <td>
                  <b>$".number_format($pago->amount)."</b>
                </td>
              </tr>
            </table>
            <br/><br/>
            Agradecemos su puntualidad en el cumplimiento de los compromisos de pago. Si tiene alguna pregunta o necesita
            informaci&oacute;n adicional, no dude en ponerse en contacto con nosotros.<br/>
            Gracias por elegir Cerveza Cocholgue. Esperamos seguir sirvi&eacute;ndole en el futuro.<br/><br/>
            Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $correos = explode(',',$cliente->email);
      foreach($correos as $correo) {
        $this->destinatario = $correo;
        $this->enviarMail();
      }

    }

    public function enviarCorreoPagoRespaldo($pago) {

      $cliente = new Cliente($pago->id_clientes);

      $this->asunto =  'Cerveza Cocholgue // Confirmacion de Pago';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Nuestro cliente <b>".$cliente->nombre."</b> ha realizado el siguiente <b>pago</b> por medio de nuestra plataforma,
            dirijase a revisarlo en la <a href='https://app.mieds.cl' target='_BLANK'>WebApp</a>.
            <br /><br/>
            <table style='width:100%' border=1>
              <tr>
                <td>
                  N&uacute;mero de factura
                </td>
                <td>
                  Fecha de pago
                </td>
                <td>
                  Monto pagado
                </td>
              </tr>
              <tr>
                <td>
                  <b>".$pago->facturas."</b>
                </td>
                <td>
                  <b>".datetime2fecha($pago->creada)."</b>
                </td>
                <td>
                  <b>$".number_format($pago->amount)."</b>
                </td>
              </tr>
            </table>
            <br/><br/>
        </body>
      </html>";

      $this->destinatario = "rodriarriagadah@gmail.com";
      $this->enviarMail();
      $this->destinatario = "pagos@cervezacocholgue.cl";
      $this->enviarMail();

    }

    public function enviarCorreoEstadoCuenta($cliente) {

      $cliente = new Cliente($pago->id_clientes);
      $entregas = Entrega::getAll("WHERE id_clientes='".$cliente->id."' AND estado!='Pagada'");

      if(count($entregas) == 0) {
        return false;
      }

      $this->asunto =  'Cerveza Cocholgue // Recepcion de Reclamo o Solicitud';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado cliente:<br/><br/>
            Espero que se encuentre bien. Quisieramos informarle acerca de su estado de cuenta y
            requerirle una fecha de pago para las facturas vencidas y por vencer. Aqu&iacute; le proporciono el detalle:
            <br /><br/>
            <table style='width:100%' border=1>
              <tr>
                <td>
                  N&uacte;mero de factura
                </td>
                <td>
                  Fecha de vencimiento
                </td>
                <td>
                  Monto a pagar
                </td>
              </tr>";

      foreach($entregas as $entrega) {
        $this->mensaje .= "<tr>
                  <td>
                    <b>".$entrega->facturas."</b>
                  </td>
                  <td>
                    <b>".datetime2fecha($entrega->fecha_vencimiento)."</b>
                  </td>
                  <td>
                    <b>$".number_format($entrega->monto)."</b>
                  </td>
                </tr>";
      }


      $this->mensaje .= "</table>
            <br/><br/>
            Solicito amablemente que nos informe la fecha de pago de las facturas.<br/>
            Por favor, no dude en ponerse en contacto con nosotros si tiene alguna pregunta
            o necesita asistencia adicional. Estamos aqu&iacute; para ayudarlo.<br/><br/>
            Agradeciendo su atenci&oacute;n y esperando su pronta respuesta.
            <br/><br/>
            Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
        </body>
      </html>";

      $this->destinatario = "contacto@cervezacocholgue.com";
      $this->enviarMail();

    }

    public function enviarCorreoInformeMorosidad($cliente) {

      $cliente = new Cliente($pago->id_clientes);
      $entregas = Entrega::getAll("WHERE id_clientes='".$cliente->id."' AND estado=='Vencida'");

      if(count($entregas) == 0) {
        return false;
      }

      $this->asunto =  'Cerveza Cocholgue // Estado de Morosidad';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado cliente:<br/><br/>
            Espero que se encuentre bien. Quisieramos informarle acerca de su estado de cuenta y
            requerirle una fecha de pago para las facturas vencidas y por vencer. Aqu&iacute; le proporciono el detalle:
            <br /><br/>
            <table style='width:100%' border=1>
              <tr>
                <td>
                  N&uacte;mero de factura
                </td>
                <td>
                  Fecha de vencimiento
                </td>
                <td>
                  Monto a pagar
                </td>
              </tr>";

      foreach($entregas as $entrega) {
        $this->mensaje .= "<tr>
                  <td>
                    <b>".$entrega->facturas."</b>
                  </td>
                  <td>
                    <b>".datetime2fecha($entrega->fecha_vencimiento)."</b>
                  </td>
                  <td>
                    <b>$".number_format($entrega->monto)."</b>
                  </td>
                </tr>";
      }

      $this->mensaje .= "</table>
            <br/><br/>
            Solicito amablemente que nos informe la fecha de pago de las facturas.<br/>
            Por favor, no dude en ponerse en contacto con nosotros si tiene alguna pregunta
            o necesita asistencia adicional. Estamos aqu&iacute; para ayudarlo.<br/><br/>
            Agradeciendo su atenci&oacute;n y esperando su pronta respuesta.
            <br/><br/>
            Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
        </body>
      </html>";

      $this->destinatario = "contacto@cervezacocholgue.com";
      $this->enviarMail();

    }

    public function enviarCorreoTareas($tarea) {

      $usuario_emisor = new Usuario($tarea->id_usuarios_emisor);

      $this->asunto =  'Cerveza Cocholgue // Nueva Tarea Asignada';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado:<br/><br/>
            Se le ha asignado una tarea en la Cocholgue WebApp.
            <br /><br/>
            Tarea: <b>".$tarea->tarea."</b>
            <br/><br/>
            Para m&aacute;s detalles, dirijase a nuestra <a href='https://app.mieds.cl/?s=tareas'>WebApp</a>
        </body>
      </html>";

      if($tarea->tipo_envio == 'Nivel') {

        if($tarea->destinatario == "Usuarios Internos") {
          $usuarios = Usuario::getAll("WHERE nivel!='Cliente' AND estado='Activo'");
        } else {
          $usuarios = Usuario::getAll("WHERE nivel='".$tarea->destinatario."' AND estado='Activo'");
        }

        foreach($usuarios as $usuario_2) {
          $this->destinatario = $usuario_2->email;
          $this->enviarMail();
        }
      } else {
        $usuario_2 = new Usuario($tarea->destinatario);
        $this->destinatario = $usuario_2->email;
          $this->enviarMail();
      }

    }

    public function enviarCorreoTareasMultiple($random_int) {

      $tareas = Tarea::getAll("WHERE random_int='".$random_int."'");

      if(count($tareas) == 0) {
        return false;
      }

      $usuario_emisor = new Usuario($tareas[0]->id_usuarios_emisor);

      $this->asunto =  'Cerveza Cocholgue // Nuevas Tareas Asignadas ('.count($tareas).')';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado:<br/><br/>
            Se le ha asignado una tarea en la Cocholgue WebApp.
            <br /><br/>";

      foreach($tareas as $key => $tarea) {
        $this->mensaje .= "Tarea #".($key+1).": <b>".$tarea->tarea."</b><br/>";
      }
        
      $this->mensaje .=      "<br/><br/>
            Para m&aacute;s detalles, dirijase a nuestra <a href='https://app.mieds.cl/?s=tareas'>WebApp</a>
        </body>
      </html>";

      if($tareas[0]->tipo_envio == 'Nivel') {

        if($tareas[0]->destinatario == "Usuarios Internos") {
          $usuarios = Usuario::getAll("WHERE nivel!='Cliente' AND estado='Activo'");
        } else {
          $usuarios = Usuario::getAll("WHERE nivel='".$tareas[0]->destinatario."' AND estado='Activo'");
        }
        
        foreach($usuarios as $usuario_2) {
          $this->destinatario = $usuario_2->email;
          $this->enviarMail();
        }


      } else {
        $usuario_2 = new Usuario($tareas[0]->destinatario);
        $this->destinatario = $usuario_2->email;
          $this->enviarMail();
      }

    }




    ////////////////////////////////////////////////////////////////////////


    public function enviarCorreosEntrega_NotificacionControl($dir,$entrega) {

      $cliente = new Cliente($entrega->id_clientes);
      $entregas_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");

      $this->asunto =  'Cerveza Cocholgue // Confirmacion de Entrega';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado Cliente:<br /><br />
            Nos alegra informarle que su pedido de Cerveza Cocholgue ha sido entregado con &eacute;xito. Aqu&iacute; estan los detalles de su entrega:<br /><br />
            <table style='width: 100%;' border=1>";
              foreach($entregas_productos as $ep) {
                $this->mensaje .=
                "<tr>
                  <td>
                    ".$ep->tipo."
                  </td>
                  <td>
                    ".$ep->cantidad."
                  </td>
                  <td>
                    ".$ep->tipos_cerveza."
                  </td>";
              }
      $this->mensaje .=  "</table><br/><br/>
      Esperamos que disfrute nuestros productos y esperamos tener la oportunidad de servirle nuevamente en el futuro.
      Si tiene alg&uacute;n comentario o sugerencia sobre nuestros productos o nuestro servicio, no dude en compartirlo con nosotros.
      Su opini&oacute;n es muy valiosa para nosotros.<br />
      Si tiene alguna duda o necesita asistencia despu&eacute;s de la entrega, estamos a su disposici&oacute;n para ayudarlo.<br />
      Gracias por elegir Cerveza Cocholgue. Apreciamos su negocio.<br/><br/>
        Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }


    public function enviarCorreosEntregaRespaldo_NotificacionControl($dir,$entrega) {

      $cliente = new Cliente($entrega->id_clientes);
      $entregas_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");

      $this->asunto =  'Cerveza Cocholgue // Confirmacion de Entrega';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Se ha realizado una entrega al cliente <b>".$cliente->nombre."</b>:<br /><br />
            <table style='width: 100%;' border=1>";
              foreach($entregas_productos as $ep) {
                $this->mensaje .=
                "<tr>
                  <td>
                    ".$ep->tipo."
                  </td>
                  <td>
                    ".$ep->cantidad."
                  </td>
                  <td>
                    ".$ep->tipos_cerveza."
                  </td>";
              }
      $this->mensaje .=  "</table><br/><br/>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoMantencion_NotificacionControl($dir,$obj) {

      $activo = new Activo($obj->id_activos);
      $ejecutor = new Usuario($obj->ejecutor);

      $this->asunto =  'Cerveza Cocholgue // Mantencion realizada';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado Cliente:<br /><br />
            Se ha realizado una nueva mantenci&oacute;n a un activo asignado a su empresa. Aqu&iacute; los detalles:<br /><br />
            <table style='width: 100%;' border=1>
              <tr>
                <td>
                  ".$activo->marca." ".$activo->modelo."
                </td>
                <td>
                  ".date2fecha($obj->date)." ".$obj->hora_inicio."
                </td>
                <td>
                  ".$ejecutor->nombre."
                </td>
              </tr>
            </table><br/><br/>
        Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoMantencionRespaldo_NotificacionControl($dir,$obj) {

      $activo = new Activo($obj->id_activos);
      $ejecutor = new Usuario($obj->ejecutor);

      $this->asunto =  'Cerveza Cocholgue // Mantencion realizada';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado:<br /><br />
            Se ha realizado una nueva mantenci&oacute;n a un activo. Aqu&iacute; los detalles:<br /><br />
            <table style='width: 100%;' border=1>
              <tr>
                <td>
                  ".$activo->marca." ".$activo->modelo."
                </td>
                <td>
                  ".date2fecha($obj->date)." ".$obj->hora_inicio."
                </td>
                <td>
                  ".$ejecutor->nombre."
                </td>
              </tr>
            </table><br/><br/>
        Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoBarrilPerdido_NotificacionControl($dir,$obj) {

      $this->asunto =  'Cerveza Cocholgue // Barril marcado como Perdido';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado:<br /><br />
            El siguiente barril ha sido marcado como Perdido:<br /><br />
            <table style='width: 100%;' border=1>
              <tr>
                <td>
                  ".$obj->codigo."
                </td>
                <td>
                  ".$obj->clasificacion."
                </td>
                <td>
                  ".$obj->tipo_barril."
                </td>
              </tr>
            </table><br/><br/>
        Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoNuevaCompraDeInsumos_NotificacionControl($dir,$obj) {

      $proveedor = new Proveedor($obj->id_proveedores);
      $usuario_creador = new Usuario($obj->id_usuarios);

      $this->asunto =  'Cerveza Cocholgue // Nueva Compra de Insumos';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado:<br /><br />
            Se ha registrado una nueva compra de insumos. Aqu&iacute; los detalles:<br /><br />
            <table style='width: 100%;' border=1>
              <tr>
                <td>
                  ".$proveedor->nombre."
                </td>
                <td>
                  $".number_format($obj->monto)."
                </td>
                <td>
                  ".$usuario_creador->nombre."
                </td>
              </tr>
            </table><br/><br/>
            Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoSugerencia_NotificacionControl($dir,$sugerencia) {

      $cliente = new Cliente($sugerencia->id_clientes);
      $usuario = new Usuario($sugerencia->id_usuarios);

      $this->asunto =  'Cerveza Cocholgue // Recepcion de Reclamo o Solicitud';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado Cliente:<br />
            Hemos recibido su reclamo o solicitud y queremos agradecerle por tomarse el tiempo para
            comunicarse con nosotros. Aqu&iacute; est&aacute;n los detalles de su comunicaci&oacute;n:<br /><br/>
            <b>".$sugerencia->contenido."</b><br/><br/>
            Estamos revisando la informaci&oacute;n proporcionada y trabajaremos para resolver el problema
            o atender su solicitud lo m&aacute;s pronto posible. Nuestro compromiso es responder a todas
            las consultas dentro de un plazo de 3 d&iacute;as h&aacute;biles.<br/>
            Si tiene alguna pregunta adicional mientras trabajamos en su reclamo o solicitud, no dude en
            ponerse en contacto con nosotros.<br/>
            Apreciamos su paciencia y comprensi&oacute;n.<br/><br/>
            Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoSugerenciaRespaldo_NotificacionControl($dir,$sugerencia) {

      $cliente = new Cliente($sugerencia->id_clientes);

      $this->asunto =  'Cerveza Cocholgue // Recepcion de Reclamo o Solicitud';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Nuestro cliente <b>".$cliente->nombre."</b> ha realizado una <b>sugerencia o reclamo</b> por medio de nuestra plataforma,
            dirijase a revisarlo en la <a href='https://app.mieds.cl' target='_BLANK'>WebApp</a>.
            <br /><br/>
            <b>".$sugerencia->contenido."</b><br/><br/>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoNuevoBatch_NotificacionControl($dir,$obj) {

      $ejecutor = new Usuario($obj->id_usuarios_ejecutor);
      $receta = new Receta($obj->id_recetas);

      $this->asunto =  'Cerveza Cocholgue // Nuevo Batch creado';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado:<br /><br />
            Se ha registrado la creaci&oacute;n de un nuevo Batch. Aqu&iacute; los detalles:<br /><br />
            <table style='width: 100%;' border=1>
              <tr>
                <td>
                  ".$receta->nombre."
                </td>
                <td>
                  ".date2fecha($obj->fecha_inicio)."
                </td>
                <td>
                  ".$ejecutor->nombre."
                </td>
              </tr>
            </table><br/><br/>
            Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoNuevoDespacho_NotificacionControl($dir,$obj) {

      $usuario_repartidor = new Usuario($obj->id_usuarios_repartidor);

      $this->asunto =  'Cerveza Cocholgue // Nuevo Despacho';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado:<br /><br />
            Se ha registrado la creaci&oacute;n de un nuevo Despacho. Aqu&iacute; los detalles:<br /><br />
            <table style='width: 100%;' border=1>
              <tr>
                <td>
                  ".$usuario_repartidor->nombre."
                </td>
                <td>
                  ".datetime2fechayhora($obj->creada)."
                </td>
              </tr>
            </table><br/><br/>
            Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoNuevoGasto_NotificacionControl($dir,$obj) {

      $usuario = new Usuario($obj->id_usuarios);

      $this->asunto =  'Cerveza Cocholgue // Nuevo Gasto';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado:<br /><br />
            Se ha registrado la creaci&oacute;n de un nuevo Gasto. Aqu&iacute; los detalles:<br /><br />
            <table style='width: 100%;' border=1>
              <tr>
                <td>
                  ".$obj->item."
                </td>
                <td>
                  ".$obj->tipo_de_gasto."
                </td>
                <td>
                  ".date2fecha($obj->creada)."
                </td>
                <td>
                  $".number_format($obj->monto)."
                </td>
                <td>
                  ".$usuario->nombre."
                </td>
              </tr>
            </table><br/><br/>
            Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoPedido_NotificacionControl($dir,$pedido) {

      $cliente = new Cliente($pedido->id_clientes);
      $pedidos_productos = PedidoProducto::getAll("WHERE id_pedidos='".$pedido->id."'");

      $this->asunto =  'Cerveza Cocholgue // Recepcion de Pedido';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado Cliente:<br /><br />
            Nos complace informarle que hemos recibido su pedido de Cerveza Cocholgue con &eacute;xito. Aqu&iacute; estan los detalles de su pedido:<br /><br />
            <table style='width: 100%;' border=1>";
              foreach($pedidos_productos as $pp) {
                $this->mensaje .=
                "<tr>
                  <td>
                    ".$pp->tipo."
                  </td>
                  <td>
                    ".$pp->cantidad."
                  </td>
                  <td>
                    ".$pp->tipos_cerveza."
                  </td>";
              }
      $this->mensaje .=  "</table><br/><br/>
        Estamos preparando su pedido para enviarlo lo m&aacute;s pronto posible. Una vez que su
        pedido est&eacute; en camino, recibir&aacute; una notificaci&oacute;n con los detalles del
        env&iacute;o.<br/>
        Si tiene alguna duda o necesita informaci&oacute;n adicional, no dude en ponerse en contacto con nosotros.<br />
        Gracias por elegir Cerveza Cocholgue. Esperamos que disfrute nuestros productos.<br/><br/>
        Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoPedidoRespaldo_NotificacionControl($dir,$pedido) {

      $cliente = new Cliente($pedido->id_clientes);
      $pedidos_productos = PedidoProducto::getAll("WHERE id_pedidos='".$pedido->id."'");

      $this->asunto =  'Cerveza Cocholgue // Recepcion de Pedido';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Nuestro cliente <b>".$cliente->nombre."</b> ha realizado un <b>pedido</b> por medio de nuestra plataforma,
            dirijase a revisarlo en la <a href='https://app.mieds.cl' target='_BLANK'>WebApp</a>.
            <br /><br/>
            <table style='width: 100%;' border=1>";
              foreach($pedidos_productos as $pp) {
                $this->mensaje .=
                "<tr>
                  <td>
                    ".$pp->tipo."
                  </td>
                  <td>
                    ".$pp->cantidad."
                  </td>
                  <td>
                    ".$pp->tipos_cerveza."
                  </td>";
              }
      $this->mensaje .=  "</table>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoPago_NotificacionControl($dir,$pago) {

      $cliente = new Cliente($pago->id_clientes);

      $this->asunto =  'Cerveza Cocholgue // Recepcion de Pago Exitoso';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado Cliente:<br />
            Nos complace informarle que hemos recibido su pago con &eacute;xito. Aqu&iacute; están los detalles:<br /><br/>
            <table style='width:100%' border=1>
              <tr>
                <td>
                  N&uacute;mero de factura
                </td>
                <td>
                  Fecha de vencimiento
                </td>
                <td>
                  Monto pagado
                </td>
              </tr>
              <tr>
                <td>
                  <b>".$pago->facturas."</b>
                </td>
                <td>
                  <b>".datetime2fecha($pago->creada)."</b>
                </td>
                <td>
                  <b>$".number_format($pago->amount)."</b>
                </td>
              </tr>
            </table>
            <br/><br/>
            Agradecemos su puntualidad en el cumplimiento de los compromisos de pago. Si tiene alguna pregunta o necesita
            informaci&oacute;n adicional, no dude en ponerse en contacto con nosotros.<br/>
            Gracias por elegir Cerveza Cocholgue. Esperamos seguir sirvi&eacute;ndole en el futuro.<br/><br/>
            Saludos cordiales,<br/>
            <b>Matias Herrera Vallejos<br/>
            Jefe de Ventas de Cerveza Cocholgue</b>
          </center>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoPagoRespaldo_NotificacionControl($dir,$pago) {

      $cliente = new Cliente($pago->id_clientes);

      $this->asunto =  'Cerveza Cocholgue // Confirmacion de Pago';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Nuestro cliente <b>".$cliente->nombre."</b> ha realizado el siguiente <b>pago</b> por medio de nuestra plataforma,
            dirijase a revisarlo en la <a href='https://app.mieds.cl' target='_BLANK'>WebApp</a>.
            <br /><br/>
            <table style='width:100%' border=1>
              <tr>
                <td>
                  N&uacute;mero de factura
                </td>
                <td>
                  Fecha de pago
                </td>
                <td>
                  Monto pagado
                </td>
              </tr>
              <tr>
                <td>
                  <b>".$pago->facturas."</b>
                </td>
                <td>
                  <b>".datetime2fecha($pago->creada)."</b>
                </td>
                <td>
                  <b>$".number_format($pago->amount)."</b>
                </td>
              </tr>
            </table>
            <br/><br/>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoTareaRealizada_NotificacionControl($dir,$obj) {

      $this->asunto =  'Cerveza Cocholgue // Tarea Realizada';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado:<br/><br/>
            La Tarea asignada por Ud ha sido realizada.
            <br /><br/>
            Tarea: <b>".$obj->tarea."</b>
            <br/><br/>
            Para m&aacute;s detalles, dirijase a nuestra <a href='https://app.mieds.cl/?s=tareas'>WebApp</a>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoInsumosInsuficientes_NotificacionControl($dir,$obj) {

      $this->asunto =  'Cerveza Cocholgue // Insumos insuficientes';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado:<br/><br/>
            No se dispone de insumos para la realizaci&oacute;n de algunas recetas. Por favor, revisar Inventario de Insumos.
            <br /><br/>
            Para m&aacute;s detalles, dirijase a nuestra <a href='https://app.mieds.cl/?s=tareas'>WebApp</a>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoGastoVencido_NotificacionControl($dir,$obj) {

      $this->asunto =  'Cerveza Cocholgue // Gasto vencido';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado:<br/><br/>
            El siguiente gasto ha vencido:
            <table style='width: 100%;' border=1>
              <tr>
                <td>
                  ".$obj->item."
                </td>
                <td>
                  ".$obj->tipo_de_gasto."
                </td>
                <td>
                  ".date2fecha($obj->creada)."
                </td>
                <td>
                  $".number_format($obj->monto)."
                </td>
                <td>
                  ".$usuario->nombre."
                </td>
              </tr>
            </table><br/><br/>
            Para m&aacute;s detalles, dirijase a nuestra <a href='https://app.mieds.cl/?s=tareas'>WebApp</a>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

    public function enviarCorreoNuevoComentarioDeTarea_NotificacionControl($dir,$obj) {

      $tarea = new Tarea($obj->id_tareas);
      $usuario_emisor = new Usuario($obj->id_usuarios);

      $this->asunto =  'Cerveza Cocholgue // Nuevo comentario en Tarea';
      $this->remitente = "contacto@cervezacocholgue.cl";

      $this->headers = "From: " . strip_tags($this->remitente) . "\r\n";
      $this->headers .= "Reply-To: ". strip_tags($this->remitente) . "\r\n";
      $this->headers .= "MIME-Version: 1.0\r\n";
      $this->headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      $this->mensaje = "
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              font-size: 14px;
            }
          </style>
        </head>
        <body>
            <center>
            <img src='https://app.mieds.cl/img/cocholgue.png' width='200'>
            </center>
            <br /><br />
            Estimado:<br/><br/>
            ".$usuario_emisor->nombre." ha realizado un nuevo comentario en Tarea #".$tarea->id.".
            <br/><br/>
            Para m&aacute;s detalles, dirijase a nuestra <a href='https://app.mieds.cl/?s=detalle-tareas&id=".$tarea->id."'>WebApp</a>
        </body>
      </html>";

      $this->destinatario = $dir;
      $this->enviarMail();

    }

  }
  

  ?>
