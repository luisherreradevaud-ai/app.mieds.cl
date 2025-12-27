<?php

  $usuario = $GLOBALS['usuario'];

  $vci = array(
    "TSY" => "Autenticación Exitosa",
    "TSN" => "Autenticación Rechazada",
    "NP" => "No Participa, sin autenticación",
    "U3" => "Falla conexión, Autenticación Rechazada",
    "INV" => "Datos Inválidos",
    "A" => "Intentó",
    "CNP1" => "Comercio no participa",
    "EOP" => "Error operacional",
    "BNA" => "BIN no adherido",
    "ENA" => "Emisor no adherido"
  );


  $transbank = $GLOBALS['transbank'];

  require_once('./vendor_php/autoload.php');

  use Transbank\Webpay\WebpayPlus\Transaction;
  Transbank\Webpay\WebpayPlus::configureForProduction($transbank['codigo_comercio'], $transbank['api_secret_key']);


  $resultado = "";

  if(isset($_GET['token_ws'])) {

    $token_ws = $_GET['token_ws'];
    $transaction = new Transaction();
    //require_once("../php/classes/autoload.php");

    try {
      $response = $transaction->commit($token_ws);
    } catch (Exception $e){
      print "ERROR";
      die();
    }

    if( $response->responseCode == 0 ) {

      $transaccion = new Transaccion($response->buyOrder);

      $pago = new Pago;
      $pago->setPropertiesNoId($transaccion);
      $pago->codigo_transaccion = $token_ws;
      $pago->creada = date('Y-m-d H:i:s');
      $pago->total = $response->amount;
      $pago->forma_de_pago = "Webpay";
      $pago->id_usuarios = $usuario->id;
      $pago->save();

      $entregas = array();
      $facturas = array();
      $ids_entregas = explode(",",$pago->ids_entregas);
      foreach($ids_entregas as $id_entregas) {
        $entrega = new Entrega($id_entregas);
        $entrega->abonado = $entrega->monto;
        $entrega->estado = "Pagada";
        $entrega->save();
        $entregas[] = $entrega;
        $facturas[] = $entrega->factura;
      }

      $pago->facturas = implode(" - ",$facturas);

      $transaccion->delete();

      $email = new Email;
      $email->enviarCorreoPago($pago);
      $email->enviarCorreoPagoRespaldo($pago);


      $resultado = "transaccion-exitosa";
      $titulo = "Transaccion exitosa.";
      $descripcion = "Se ha pagado con &eacute;xito.";
      $boton = "app";

      $cliente = new Cliente($pago->id_clientes);
      Historial::guardarAccion("Pago #".$pago->id." realizado por cliente ".$cliente->nombre.".",$GLOBALS['usuario']);
      NotificacionControl::trigger('Pago Webpay',$pago);

    }  else {
      $resultado = "no-response";
      $titulo = "Algo sali&oacute; mal";
      $descripcion = "Hubo un error en la transacci&oacute;n.";
      $boton = "inicio";
    }
  } else {
    // TRANSACCION NO REALIZADA

    $resultado = "no-hay-token";
    $titulo = "Algo sali&oacute; mal";
    $descripcion = "Hubo un error en la transacci&oacute;n.";
    $boton = "inicio";
  }

?>
<style>

td {
  font-family: 'Montserrat-Medium' !important;
}

.asterisco {
  font-family: 'Montserrat-Medium' !important;
  font-size: 0.9em;
  color: red;
}
th {
  font-family: 'Montserrat-SemiBold' !important;
}
td {
  vertical-align: middle;
}

</style>

<div class="container mt-5">
  <div class="row">
    <div class="col-md-6">
      <h1 class="mb-4"><?= $titulo; ?></h1>
      <?= $descripcion; ?>
      <?php
      if($resultado=="transaccion-exitosa") {
        ?>
        <pre class="mt-5">
          Respuesta: <?= $vci[$response->vci]; ?><br/>
          Monto: $<?= number_format($response->amount); ?><br/>
          Tarjeta: xxxxxxxxx<?php
          $cardDetail = $response->cardDetail;
          print $cardDetail['card_number'];
          if($response->installmentsNumber>0) { ?>
          Cuotas: <?= $response->installmentsNumber; ?><br/>
        <?php
          }
          ?>
          </pre>
          <?php
        }
        ?>


    </div>
    <div class="col-md-6">
      <?php
      if($resultado=="transaccion-exitosa") {
      ?>
      <div class="mt-3">
        PAGADO
      </div>
      <table class="table table-striped mt-2">
        <thead>
          <tr>
            <th>
              ID
            </th>
            <th>
              Tipo
            </th>
            <th>
              Monto
            </th>
        </thead>
        <?php
        $total = 0;
        foreach($entregas as $entrega) {
        ?>
        <tr>
          <td>
            #<?= $entrega->id; ?>
          </td>
          <td>
            0
          </td>
          <td>
            <b>$<?= number_format($entrega->monto); ?></b>
          </td>
        </tr>
        <?php
        }
        ?>
      </table>
      <table class="table mt-0" style="margin-top:0px">
        <tr>
          <td colspan="4" class="px-5 py-3">
            <div class="d-flex justify-content-between">
              <div>
                TOTAL:
              </div>
              <div>
                $<?= number_format($pago->total); ?>
              </div>
            </div>
          </td>
        </tr>
      </table>
      <?php
      }
      ?>
    </div>
  </div>
</div>
