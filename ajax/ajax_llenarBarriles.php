    <?php

    if($_POST == array()) {
        die();
    }

    require_once("./../php/app.php");

    $usuario = new Usuario;
    session_start();
    $usuario->checkSession($_SESSION);

    if(!validaIdExistsVarios($_POST,['cantidad_a_cargar','id_batches_activos','id_barriles'])) {
        die();
    }

    $batch_activo = new BatchActivo($_POST['id_batches_activos']);
    $activo = new Activo($batch_activo->id_activos);
    $barril = new Barril($_POST['id_barriles']);
    $cantidad_a_cargar = floatval($_POST['cantidad_a_cargar']);

    // Validar que hay suficiente en el fermentador
    if($cantidad_a_cargar > $batch_activo->litraje) {
        $response["status"] = "ERROR";
        $response["mensaje"] = "No hay suficiente líquido en el fermentador. Disponible: " . $batch_activo->litraje . "L";
        print json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    // Validar que no exceda la capacidad del barril
    $capacidad_disponible = $barril->litraje - $barril->litros_cargados;
    if($cantidad_a_cargar > $capacidad_disponible) {
        $response["status"] = "ERROR";
        $response["mensaje"] = "Excede la capacidad del barril. Espacio disponible: " . $capacidad_disponible . "L";
        print json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    $batch_activo->litraje -= $cantidad_a_cargar;
    $barril->litros_cargados += $cantidad_a_cargar;
    $barril->id_batches = $batch_activo->id_batches;
    $barril->id_activos = $batch_activo->id_activos;
    $barril->id_batches_activos = $batch_activo->id;
    $barril->fecha_llenado = date('Y-m-d H:i:s');

    $batch_activo->save();

    // Si el fermentador quedó vacío, liberarlo
    if($batch_activo->litraje <= 0) {
        $activo->id_batches = 0;
        $activo->save();
    }

    $barril->save();

    $batch = new Batch($batch_activo->id_batches);
    Historial::guardarAccion("Barril ".$barril->codigo." cargado con ".$_POST['cantidad_a_cargar']." litros de ".$activo->codigo." para Batch #".$batch->batch_nombre.".",$GLOBALS['usuario']);

    $cdd = new CentralDeDespacho;
    $response["status"] = "OK";
    $response["mensaje"] = "OK";
    $response["obj"] = $cdd->getDataPage();
    $response['msg_content'] = "Barril ".$barril->codigo." cargado con ".$_POST['cantidad_a_cargar']." litros de ".$activo->codigo.".";

    print json_encode($response,JSON_PRETTY_PRINT);

    ?>
