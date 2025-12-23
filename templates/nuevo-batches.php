<?php


    $usuario = $GLOBALS['usuario'];
    $recetas = Receta::getALL("ORDER BY nombre asc");
    $cocineros = Usuario::getAll("WHERE nivel='Jefe de Cocina'");
    $activos = Activo::getAll("WHERE clase='Fermentador' order by nombre asc");
    $activos_disponibles = Activo::getAll("WHERE clase='Fermentador' AND id_batches='0' order by nombre asc");
    $proveedores = Proveedor::getAll();
    $tipos_de_insumos = TipoDeInsumo::getAll();
    $fermentadores = Activo::getAll("WHERE clase='Fermentador'");

    $insumos_arr = array(
        'licor' => array(),
        'maceracion' => array(),
        'coccion' => array(),
        'inoculacion' => array(),
        'lupulizacion' => array(),
        'enfriado' => array()
    );

    $fermentacion_fermentadores = [];

    if(validaIdExists($_GET,'id')) {
        $batch = new Batch($_GET['id']);
        $batch_insumos = BatchInsumo::getAll("WHERE id_batches='".$batch->id."'");
        foreach($batch_insumos as $bi) {
            if(isset($insumos_arr[$bi->etapa])) {
                $insumo = new Insumo($bi->id_insumos);
                $insumo->cantidad = $bi->cantidad;
                $insumo->etapa_index = $bi->etapa_index;
                $insumos_arr[$bi->etapa][] = $insumo;
            }
        }
        $batch_activos = BatchActivo::getAll("WHERE id_batches='".$batch->id."'");
        foreach($batch_activos as $fa) {
            //$fa->activo = new Activo($fa->id_activos);
            $fermentacion_fermentadores[] = $fa;
        }
    } else {
        $batch = new Batch;
    }

    $lupulizaciones = BatchLupulizacion::getAll("WHERE id_batches='".$batch->id."'");
    $enfriados = BatchEnfriado::getAll("WHERE id_batches='".$batch->id."'");
    $traspasos = BatchTraspaso::getAll("WHERE id_batches='".$batch->id."'");

    // Fermentadores para traspasos (solo los que tienen cerveza de este batch)
    $batch_activos_para_traspaso = BatchActivo::getAll("WHERE id_batches='".$batch->id."' AND litraje>0 AND estado='Fermentación'");
    $activos_disponibles_traspaso = Activo::getAll("WHERE clase='Fermentador' AND id_batches='0' ORDER BY codigo asc");

    // Cargar traspasos para poder revertirlos
    $traspasos_por_destino = array();
    foreach($traspasos as $tr) {
        if(!isset($traspasos_por_destino[$tr->id_fermentadores_final])) {
            $traspasos_por_destino[$tr->id_fermentadores_final] = $tr;
        }
    }

    $ultimo_batch = Batch::getAll("ORDER BY id desc LIMIT 1");

?>

<form id="batch-form">
<input type="hidden" name="id" value="<?= $batch->id; ?>">
<input type="hidden" name="entidad" value="batches">
<div class="row">
    <div class="col-md-2 mb-4">
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100 etapa-btn <?= ($batch->etapa_seleccionada == 'batch') ? 'active' : ''; ?>" id="pills-batch-tab" data-bs-toggle="pill" data-bs-target="#pills-batch" type="button" role="tab" aria-controls="pills-batch" aria-selected="true" data-etapa="batch"><i class="bi bi-check"></i> General</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100 etapa-btn <?= ($batch->etapa_seleccionada == 'licor') ? 'active' : ''; ?>" id="pills-licor-tab" data-bs-toggle="pill" data-bs-target="#pills-licor" type="button" role="tab" aria-controls="pills-licor" aria-selected="true" data-etapa="licor"><i class="bi bi-dash-circle" data-etapa="licor"></i> Licor</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100 etapa-btn <?= ($batch->etapa_seleccionada == 'maceracion') ? 'active' : ''; ?>" id="pills-maceracion-tab" data-bs-toggle="pill" data-bs-target="#pills-maceracion" type="button" role="tab" aria-controls="pills-maceracion" aria-selected="false" data-etapa="maceracion"><i class="bi bi-dash-circle"></i> Maceración</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100 etapa-btn <?= ($batch->etapa_seleccionada == 'lavado-de-granos') ? 'active' : ''; ?>" id="pills-lavado-de-granos-tab" data-bs-toggle="pill" data-bs-target="#pills-lavado-de-granos" type="button" role="tab" aria-controls="pills-lavado-de-granos" aria-selected="false" data-etapa="lavado-de-granos"><i class="bi bi-dash-circle"></i> Lavado</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100 etapa-btn <?= ($batch->etapa_seleccionada == 'coccion') ? 'active' : ''; ?>" id="pills-coccion-tab" data-bs-toggle="pill" data-bs-target="#pills-coccion" type="button" role="tab" aria-controls="pills-coccion" aria-selected="false" data-etapa="coccion"><i class="bi bi-dash-circle"></i> Cocción</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100 etapa-btn <?= ($batch->etapa_seleccionada == 'combustible') ? 'active' : ''; ?>" id="pills-combustible-tab" data-bs-toggle="pill" data-bs-target="#pills-combustible" type="button" role="tab" aria-controls="pills-combustible" aria-selected="false" data-etapa="combustible"><i class="bi bi-dash-circle"></i> Combustible</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100 etapa-btn <?= ($batch->etapa_seleccionada == 'lupulizacion') ? 'active' : ''; ?>" id="pills-lupulizacion-tab" data-bs-toggle="pill" data-bs-target="#pills-lupulizacion" type="button" role="tab" aria-controls="pills-lupulizacion" aria-selected="false" data-etapa="lupulizacion"><i class="bi bi-dash-circle"></i> Lupulización</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100 etapa-btn <?= ($batch->etapa_seleccionada == 'enfriado') ? 'active' : ''; ?>" id="pills-enfriado-tab" data-bs-toggle="pill" data-bs-target="#pills-enfriado" type="button" role="tab" aria-controls="pills-enfriado" aria-selected="false" data-etapa="enfriado"><i class="bi bi-dash-circle"></i> Enfriado</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100 etapa-btn <?= ($batch->etapa_seleccionada == 'inoculacion') ? 'active' : ''; ?>" id="pills-inoculacion-tab" data-bs-toggle="pill" data-bs-target="#pills-inoculacion" type="button" role="tab" aria-controls="pills-inoculacion" aria-selected="false" data-etapa="inoculacion"><i class="bi bi-dash-circle"></i> Inoculación</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100 etapa-btn <?= ($batch->etapa_seleccionada == 'fermentacion') ? 'active' : ''; ?>" id="pills-fermentacion-tab" data-bs-toggle="pill" data-bs-target="#pills-fermentacion" type="button" role="tab" aria-controls="pills-fermentacion" aria-selected="false" data-etapa="fermentacion"><i class="bi bi-dash-circle"></i> Fermentación</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100 etapa-btn <?= ($batch->etapa_seleccionada == 'traspasos') ? 'active' : ''; ?>" id="pills-traspasos-tab" data-bs-toggle="pill" data-bs-target="#pills-traspasos" type="button" role="tab" aria-controls="pills-traspasos" aria-selected="false" data-etapa="traspasos"><i class="bi bi-dash-circle"></i> Traspasos</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100 etapa-btn <?= ($batch->etapa_seleccionada == 'maduracion') ? 'active' : ''; ?>" id="pills-maduracion-tab" data-bs-toggle="pill" data-bs-target="#pills-maduracion" type="button" role="tab" aria-controls="pills-maduracion" aria-selected="false" data-etapa="maduracion"><i class="bi bi-dash-circle"></i> Maduración</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100 etapa-btn <?= ($batch->etapa_seleccionada == 'finalizacion') ? 'active' : ''; ?>" id="pills-finalizacion-tab" data-bs-toggle="pill" data-bs-target="#pills-finalizacion" type="button" role="tab" aria-controls="pills-finalizacion" aria-selected="false" data-etapa="finalizacion"><i class="bi bi-dash-circle"></i> Finalización</button>
            </li>
        </ul>
    </div>
    <div class="col-md-10">
        <div class="card shadow">
            <div class="card-body d-flex justify-content-between">
                <h1>Batch <?= ($batch->id != '') ? ''.$batch->batch_nombre : ''; ?></h1>
                <?php $usuario->printReturnBtn(); ?>
            </div>
        </div>
        <div class="tab-content" id="pills-tabContent">
            <!-- BATCH -->
            <div class="tab-pane fade show <?= ($batch->etapa_seleccionada == 'batch') ? 'show active' : ''; ?>" id="pills-batch" role="tabpanel" aria-labelledby="pills-batch-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Información General</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Número de Batch:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" name="batch_nombre" value="<?= $batch->batch_nombre; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                Fecha de Creación:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="date" class="form-control" name="batch_date" value="<?= $batch->batch_date; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                Receta:
                            </div>
                            <div class="col-md-6 mb-2">
                                <select class="form-control" name="id_recetas">
                                    <option value="0">-</option>
                                    <?php
                                    foreach($recetas as $receta) {
                                        ?>
                                        <option value="<?= $receta->id; ?>" <?= ($batch->id_recetas == $receta->id) ? 'selected' : ''; ?>><?= $receta->nombre; ?>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                Cocinero:
                            </div>
                            <div class="col-md-6 mb-2">
                                <select class="form-control" name="batch_id_usuarios_cocinero">
                                    <option value="0">-</option>
                                    <?php
                                    foreach($cocineros as $cocinero) {
                                        ?>
                                        <option value="<?= $cocinero->id; ?>" <?= ($batch->batch_id_usuarios_cocinero == $cocinero->id) ? 'selected' : ''; ?>><?= $cocinero->nombre; ?>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                Litros:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="batch_litros" value="<?= $batch->batch_litros; ?>" step="0.1" min="0">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">&nbsp;&nbsp;L</span>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            <!-- /BATCH -->

            <!-- LICOR -->
            <div class="tab-pane fade <?= ($batch->etapa_seleccionada == 'licor') ? 'show active' : ''; ?>" id="pills-licor" role="tabpanel" aria-labelledby="pills-licor-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Licor</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Temperatura:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control acero-float" name="licor_temperatura" value="<?= $batch->licor_temperatura; ?>" step="0.1" min="0">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                PH:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="number" class="form-control acero-float" name="licor_ph" value="<?= $batch->licor_ph; ?>" step="0.1" min="0" max="10">
                            </div>
                            <div class="col-md-6 mb-2">
                                Litros:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control acero-float" name="licor_litros" value="<?= $batch->licor_litros; ?>" step="0.1" min="0">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">&nbsp;&nbsp;L</span>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>
                                                Insumos
                                            </th>
                                            <th>
                                                Cantidad
                                            </th>
                                    </thead>
                                    <tbody id="licor-0-insumos-table">
                                    </tbody>
                                </table>
                                <button class="btn btn-primary btn-sm agregar-insumos-btn" data-etapa="licor" data-index="0">+ Agregar Insumo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /LICOR -->

            <!-- MACERACION -->

            <div class="tab-pane fade <?= ($batch->etapa_seleccionada == 'maceracion') ? 'show active' : ''; ?>" id="pills-maceracion" role="tabpanel" aria-labelledby="pills-maceracion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Maceración</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Hora de inicio:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="maceracion_hora_inicio" value="<?= $batch->maceracion_hora_inicio; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                Temperatura:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control acero-float" name="maceracion_temperatura" value="<?= $batch->maceracion_temperatura; ?>">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                Litros:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control acero-float" name="maceracion_litros" value="<?= $batch->maceracion_litros; ?>">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">&nbsp;&nbsp;L</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                PH macerado:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="number" class="form-control acero-float" name="maceracion_ph" value="<?= $batch->maceracion_ph; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                Hora de finalización:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="maceracion_hora_finalizacion" value="<?= $batch->maceracion_hora_finalizacion; ?>">
                            </div>
                            <div class="col-12 mb-2">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>
                                                Insumos
                                            </th>
                                            <th>
                                                Cantidad
                                            </th>
                                    </thead>
                                    <tbody id="maceracion-0-insumos-table">
                                    </tbody>
                                </table>
                                <button class="btn btn-primary btn-sm agregar-insumos-btn" data-etapa="maceracion" data-index="0">+ Agregar Insumo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- /MACERACION -->
                                <input type="hidden" name="lavado_de_granos_tipo_de_densidad" value="<?= $batch->lavado_de_granos_tipo_de_densidad; ?>">
                                <input type="hidden"  name="lavado_de_granos_densidad" value="<?= $batch->lavado_de_granos_densidad; ?>">

            <!-- LAVADO DE GRANOS -->

            <div class="tab-pane fade <?= ($batch->etapa_seleccionada == 'lavado-de-granos') ? 'show active' : ''; ?>" id="pills-lavado-de-granos" role="tabpanel" aria-labelledby="pills-lavado-de-granos-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Lavado de Granos</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Hora de inicio:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="lavado_de_granos_hora_inicio" value="<?= $batch->lavado_de_granos_hora_inicio; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                Mosto:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control acero-float" name="lavado_de_granos_mosto" value="<?= $batch->lavado_de_granos_mosto; ?>">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">&nbsp;&nbsp;L</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                Hora de termino:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="lavado_de_granos_hora_termino" value="<?= $batch->lavado_de_granos_hora_termino; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade <?= ($batch->etapa_seleccionada == 'coccion') ? 'show active' : ''; ?>" id="pills-coccion" role="tabpanel" aria-labelledby="pills-coccion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Cocción</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                PH inicial:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="number" class="form-control acero-float" name="coccion_ph_inicial" value="<?= $batch->coccion_ph_inicial; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                PH final:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="number" class="form-control acero-float" name="coccion_ph_final" value="<?= $batch->coccion_ph_final; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                Reciclar Bagazo:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control acero-float" name="coccion_recilar" value="<?= $batch->coccion_recilar; ?>">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">Kg</span>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>
                                                Insumos
                                            </th>
                                            <th>
                                                Cantidad
                                            </th>
                                    </thead>
                                    <tbody id="coccion-0-insumos-table">
                                    </tbody>
                                </table>
                                <button class="btn btn-primary btn-sm agregar-insumos-btn" data-etapa="coccion" data-index="0">+ Agregar Insumo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade <?= ($batch->etapa_seleccionada == 'combustible') ? 'show active' : ''; ?>" id="pills-combustible" role="tabpanel" aria-labelledby="pills-combustible-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Combustible</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Gas:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control acero-float" name="combustible_gas" value="<?= $batch->combustible_gas; ?>">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">Kg</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade <?= ($batch->etapa_seleccionada == 'lupulizacion') ? 'show active' : ''; ?>" id="pills-lupulizacion" role="tabpanel" aria-labelledby="pills-lupulizacion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Lupulización</h3>
                        </div>
                        <div class="mt-5 w-100" id="lupulizaciones-div"></div>
                        <div class="row">
                            <div class="col-12 mb-1">
                                <button class="btn btn-primary btn-sm" id="nuevo-lupulizacion-btn">+ Agregar Lupulización</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade <?= ($batch->etapa_seleccionada == 'enfriado') ? 'show active' : ''; ?>" id="pills-enfriado" role="tabpanel" aria-labelledby="pills-enfriado-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Enfriado</h3>
                        </div>
                        <div class="mt-5 w-100" id="enfriados-div"></div>
                        <div class="row">
                            <div class="col-12 mb-1">
                                <button class="btn btn-primary btn-sm" id="nuevo-enfriado-btn">+ Agregar Enfriado</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade <?= ($batch->etapa_seleccionada == 'inoculacion') ? 'show active' : ''; ?>" id="pills-inoculacion" role="tabpanel" aria-labelledby="pills-inoculacion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Inoculación</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Temperatura de mosto:
                            </div>
                            <div class="col-md-6 mb-2">
                                
                                <div class="input-group">
                                    <input type="number" class="form-control acero-float" name="inoculacion_temperatura" value="<?= $batch->inoculacion_temperatura; ?>">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>
                                                Insumos
                                            </th>
                                            <th>
                                                Cantidad
                                            </th>
                                    </thead>
                                    <tbody id="inoculacion-0-insumos-table">
                                    </tbody>
                                </table>
                                <button class="btn btn-primary btn-sm agregar-insumos-btn" data-etapa="inoculacion" data-index="0">+ Agregar Insumo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade <?= ($batch->etapa_seleccionada == 'fermentacion') ? 'show active' : ''; ?>" id="pills-fermentacion" role="tabpanel" aria-labelledby="pills-fermentacion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Fermentación</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Fecha:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="date" class="form-control" name="fermentacion_date" value="<?= $batch->fermentacion_date; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                Hora de inicio:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="fermentacion_hora_inicio" value="<?= $batch->fermentacion_hora_inicio; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                Temperatura Inicio:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control acero-float" name="inoculacion_temperatura_inicio" value="<?= $batch->inoculacion_temperatura_inicio; ?>">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                Temperatura fermentación:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control acero-float" name="fermentacion_temperatura" value="<?= $batch->fermentacion_temperatura; ?>">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                                
                            </div>
                            <div class="col-md-6 mb-2">
                                Hora de finalización:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="fermentacion_hora_finalizacion" value="<?= $batch->fermentacion_hora_finalizacion; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                PH:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="number" class="form-control acero-float" name="fermentacion_ph" value="<?= $batch->fermentacion_ph; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                Densidad:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="number" class="form-control acero-float" name="fermentacion_densidad" value="<?= $batch->fermentacion_densidad; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                Tipo de densidad:
                            </div>
                            <div class="col-md-6 mb-2">
                                <select class="form-control" name="fermentacion_tipo_de_densidad">
                                    <option <?= ($batch->fermentacion_tipo_de_densidad == 'Pre-hervor') ? 'selected' : ''; ?>>Pre-hervor</option>
                                    <option <?= ($batch->fermentacion_tipo_de_densidad == 'Inicial') ? 'selected' : ''; ?>>Inicial</option>
                                    <option <?= ($batch->fermentacion_tipo_de_densidad == 'Final') ? 'selected' : ''; ?>>Final</option>
                                </select>
                            </div>
                            <div class="col-12 mt-3">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>
                                                Fermentador
                                            </th>
                                            <th>
                                                Cantidad
                                            </th>
                                            <th>
                                                Estado
                                            </th>
                                            <th>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="fermentacion-fermentadores-tbody">
                                    </tbody>
                                    <tfooter>
                                        <tr>
                                            <td>
                                                <b>
                                                    Total:
                                                </b>
                                            </td>
                                            <td>
                                                <b>
                                                    <span id="fermentacion-fermentadores-litraje-total-span">0</span> Litros
                                                </b>
                                            </td>
                                            <td>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4">
                                                <div id="fermentacion-capacidad-info" class="mt-2"></div>
                                            </td>
                                        </tr>
                                    </tfooter>
                                </table>
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-default shadow" data-bs-target="#agregar-fermentadores-modal" data-bs-toggle="modal" id="agregar-fermentadores-btn">
                                        + Agregar Fermentador
                                    </button>
                                </div>                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade <?= ($batch->etapa_seleccionada == 'traspasos') ? 'show active' : ''; ?>" id="pills-traspasos" role="tabpanel" aria-labelledby="pills-traspasos-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Traspaso</h3>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-2">
                                <div id="traspasos-div" class="mb-5">
                                </div>
                                <button class="btn btn-primary btn-sm" id="nuevo-traspasos-btn">+ Agregar Traspaso</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade <?= ($batch->etapa_seleccionada == 'maduracion') ? 'show active' : ''; ?>" id="pills-maduracion" role="tabpanel" aria-labelledby="pills-maduracion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Maduración</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Fecha:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="date" class="form-control" name="maduracion_date" value="<?= $batch->maduracion_date; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                Temperatura inicio:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control acero-float" name="maduracion_temperatura_inicio" value="<?= $batch->maduracion_temperatura_inicio; ?>">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                Hora inicio:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="maduracion_hora_inicio" value="<?= $batch->maduracion_hora_inicio; ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                Temperatura finalización:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control acero-float" name="maduracion_temperatura_finalizacion" value="<?= $batch->maduracion_temperatura_finalizacion; ?>">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                Hora finalización:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="maduracion_hora_finalizacion" value="<?= $batch->maduracion_hora_finalizacion; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade <?= ($batch->etapa_seleccionada == 'finalizacion') ? 'show active' : ''; ?>" id="pills-finalizacion" role="tabpanel" aria-labelledby="pills-finalizacion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Finalización</h3>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-2">
                                <button class="btn btn-primary btn-sm" id="finalizar-btn" data-bs-toggle="modal" data-bs-target="#finalizar-modal"><i class="bi bi-check"></i> Finalizar Batch</button>
                                <?php if(!empty($batch->id)): ?>
                                <a href="./ajax/ajax_generarBatchPDF.php?id=<?= htmlspecialchars($batch->id); ?>" target="_blank" class="btn btn-outline-secondary btn-sm ms-2">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF Informe
                                </a>
                                <a href="./ajax/ajax_generarBatchInstruccionesPDF.php?id=<?= htmlspecialchars($batch->id); ?>" target="_blank" class="btn btn-outline-info btn-sm ms-2">
                                    <i class="bi bi-file-earmark-text"></i> PDF Instrucciones
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN: Métricas de Calidad (ML) -->
                <div class="card shadow mt-3">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3><i class="bi bi-graph-up"></i> Métricas de Calidad (ML)</h3>
                            <small class="text-muted">Datos opcionales para análisis y Machine Learning</small>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">ABV Final (%):</label>
                                <input type="number" class="form-control ml-field" name="abv_final" step="0.01" min="0" max="99.99"
                                       placeholder="Ej: 5.5" data-max="99.99" data-label="ABV Final">
                                <small class="text-muted">M&aacute;x: 99.99%</small>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">IBU Final:</label>
                                <input type="number" class="form-control ml-field" name="ibu_final" min="0" max="2147483647"
                                       placeholder="Ej: 45" data-label="IBU Final">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Color EBC:</label>
                                <input type="number" class="form-control ml-field" name="color_ebc" min="0" max="2147483647"
                                       placeholder="Ej: 12" data-label="Color EBC">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Calificaci&oacute;n Sensorial (1-10):</label>
                                <input type="number" class="form-control ml-field" name="calificacion_sensorial" min="1" max="127"
                                       placeholder="1-10" data-max="127" data-label="Calificaci&oacute;n Sensorial">
                                <small class="text-muted">Rango: 1-10 recomendado</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Rendimiento Final (L):</label>
                                <input type="number" class="form-control ml-field" name="rendimiento_litros_final" step="0.1" min="0"
                                       placeholder="Volumen final producido" data-label="Rendimiento Final">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Merma Total (L):</label>
                                <input type="number" class="form-control ml-field" name="merma_total_litros" step="0.1" min="0"
                                       placeholder="P&eacute;rdida total" data-label="Merma Total">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Densidad Final Verificada:</label>
                                <input type="number" class="form-control ml-field" name="densidad_final_verificada" step="0.001" min="0" max="99.999"
                                       placeholder="Ej: 1.012" data-max="99.999" data-label="Densidad Final">
                                <small class="text-muted">Formato: X.XXX (ej: 1.012)</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notas de Cata:</label>
                                <textarea class="form-control" name="notas_cata" rows="2"
                                          placeholder="Describir características sensoriales: aroma, sabor, cuerpo, etc."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- FIN SECCIÓN Métricas ML -->
            </div>

        </div>

        <div class="card shadow">
            <div class="card-body d-flex justify-content-between">
                <?php
                if($batch->id != '') {
                    ?>
                    <button class="btn btn-danger ms-3 eliminar-obj-btn"><i class="bi bi-trash"></i> Eliminar Batch</button>
                    <?php
                } else {
                    print "&nbsp;";
                }
                ?>
                <button class="btn btn-primary ms-3 guardar-btn" data-tipo="Batch"><i class="bi bi-save"></i> Guardar Batch</button>
            </div>
        </div>
    </div>
</div>
</form>

<!-- // MODALS -->
  

<div class="modal modal-fade" tabindex="-1" role="dialog" id="agregar-insumos-modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Agregar Insumo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-1">
                        Tipo de Insumo:
                    </div>
                    <div class="col-6 mb-1">
                        <select name="id_tipos_de_insumos" class="form-control">
                        </select>
                    </div>
                    <div class="col-6 mb-1">
                        Insumo:
                    </div>
                    <div class="col-6 mb-1">
                        <select name="id_insumos" class="form-control">
                        </select>
                    </div>
                    <div class="col-6 mb-1">
                        Cantidad:
                    </div>
                    <div class="col-6 mb-1">
                        <div class="input-group">
                            <input type="number" class="form-control acero-float" name="cantidad"  value="0" step="0.1" min="0">
                            <span class="input-group-text" style="border-radius: 0px 10px 10px 0px" id="agregar-insumos-unidad-de-medida">ml</span>
                        </div>
                    </div>
                </div>

                <!-- Campos específicos de Levadura (ocultos por defecto) -->
                <div id="campos-levadura-modal" style="display: none;">
                    <hr>
                    <h6 class="text-info"><i class="fas fa-flask"></i> Datos Espec&iacute;ficos de Levadura</h6>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Generaci&oacute;n:</label>
                            <input type="number" class="form-control form-control-sm" name="lev_generacion" min="1" max="20" value="1">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Tasa Inoculaci&oacute;n:</label>
                            <input type="number" class="form-control form-control-sm" name="lev_tasa_inoculacion" step="0.01" min="0" placeholder="M c&eacute;l/ml/&deg;P">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Viabilidad (%):</label>
                            <input type="number" class="form-control form-control-sm" name="lev_viabilidad" step="0.1" min="0" max="100">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Vitalidad (%):</label>
                            <input type="number" class="form-control form-control-sm" name="lev_vitalidad" step="0.1" min="0" max="100">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Tiempo Lag (h):</label>
                            <input type="number" class="form-control form-control-sm" name="lev_tiempo_lag" step="0.5" min="0">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label small">Atenuaci&oacute;n Real (%):</label>
                            <input type="number" class="form-control form-control-sm" name="lev_atenuacion" step="0.1" min="0" max="100">
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="lev_uso_starter" id="lev_uso_starter_modal" value="1">
                                <label class="form-check-label small" for="lev_uso_starter_modal">
                                    &iquest;Us&oacute; Starter?
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2" id="lev-starter-volumen-modal" style="display:none;">
                            <label class="form-label small">Volumen Starter (ml):</label>
                            <input type="number" class="form-control form-control-sm" name="lev_volumen_starter" min="0">
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label small">Batch de Origen (si reutilizada):</label>
                            <select class="form-control form-control-sm" name="lev_origen_batch">
                                <option value="">-- Nueva / No reutilizada --</option>
                                <?php
                                $batches_anteriores = Batch::getAll("ORDER BY batch_date DESC LIMIT 50");
                                foreach($batches_anteriores as $ba) {
                                    if($ba->id == $batch->id) continue;
                                    $receta_ba = new Receta($ba->id_recetas);
                                    echo "<option value='{$ba->id}'>#{$ba->batch_nombre} - {$receta_ba->nombre} ({$ba->batch_date})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-12 mb-2">
                            <label class="form-label small">Observaciones:</label>
                            <textarea class="form-control form-control-sm" name="lev_observaciones" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <!-- Fin campos levadura -->

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="agregar-insumos-aceptar" data-bs-dismiss="modal">Agregar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal modal-fade" tabindex="-1" role="dialog" id="nuevo-lupulizacion-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Agregar Lupulización
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-1">
                        Fecha:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="date" name="nuevo-lupulizacion-date" id="nuevo-lupulizacion-date" class="form-control">
                    </div>
                    <div class="col-6 mb-1">
                        Hora:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="time" name="nuevo-lupulizacion-hora" id="nuevo-lupulizacion-hora" class="form-control">
                    </div>
                    <div class="col-6 mb-1">
                        Tipo:
                    </div>
                    <div class="col-6 mb-1">
                        <select class="form-control" name="nuevo-lupulizacion-tipo">
                            <option>Hervor</option>
                            <option>Hopstand</option>
                            <option>Dry-hop</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="nuevo-lupulizacion-aceptar-btn" data-bs-dismiss="modal">Agregar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal modal-fade" tabindex="-1" role="dialog" id="nuevo-enfriado-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Agregar Enfriado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-1">
                        Fecha:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="date" name="nuevo-enfriado-date" id="nuevo-enfriado-date" class="form-control">
                    </div>
                    <div class="col-6 mb-1">
                        Hora inicio:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="time" name="nuevo-enfriado-hora" id="nuevo-enfriado-hora" class="form-control">
                    </div>
                    <div class="col-6 mb-1">
                        Temperatura de enfriado:
                    </div>
                    <div class="col-6 mb-1">
                        <div class="input-group">
                            <input type="number" class="form-control" name="nuevo-enfriado-temperatura-inicio" id="nuevo-enfriado-temperatura-inicio">
                            <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                        </div>
                    </div>
                    <div class="col-6 mb-1">
                        pH:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="number" class="form-control acero-float" name="nuevo-enfriado-ph" id="nuevo-enfriado-ph" step="0.01" min="0" max="14">
                    </div>
                    <div class="col-6 mb-1">
                        Densidad:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="number" class="form-control" name="nuevo-enfriado-densidad" id="nuevo-enfriado-densidad">
                    </div>
                    <div class="col-6 mb-1">
                        pH enfriado:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="number" class="form-control acero-float" name="nuevo-enfriado-ph-enfriado" id="nuevo-enfriado-ph-enfriado" step="0.01" min="0" max="14">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="nuevo-enfriado-aceptar-btn" data-bs-dismiss="modal">Agregar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal modal-fade" tabindex="-1" role="dialog" id="nuevo-traspasos-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Traspaso
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-1">
                        Desde:
                    </div>
                    <div class="col-6 mb-1">
                        <select class="form-control" id="nuevo-traspasos-desde-select">
                            <?php
                            foreach($batch_activos_para_traspaso as $ba) {
                                $activo_traspaso = new Activo($ba->id_activos);
                            ?>
                            <option value="<?= $activo_traspaso->id; ?>" data-litraje="<?= $activo_traspaso->litraje; ?>" data-litros-disponibles="<?= $ba->litraje; ?>"><?= $activo_traspaso->codigo; ?> (<?= $ba->litraje; ?>L)</option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-6 mb-1">
                        Hasta:
                    </div>
                    <div class="col-6 mb-1">
                        <select class="form-control" id="nuevo-traspasos-hasta-select">
                            <?php
                            foreach($activos_disponibles_traspaso as $atd) {
                            ?>
                            <option value="<?= $atd->id; ?>" data-litraje="<?= $atd->litraje; ?>"><?= $atd->codigo; ?> (<?= $atd->litraje; ?>L)</option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-12 my-2 text-center text-danger" id="traspaso-warning-label" style="display: none;">
                        Los fermentadores deben tener el mismo litraje.
                    </div>
                    <div class="col-12 my-2">
                        <hr/>
                    </div>
                    <div class="col-6 mb-1">
                        Batch:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="text" class="form-control" id="nuevo-traspasos-batch" value="<?= $batch->batch_nombre; ?>" readonly>
                    </div>
                    <div class="col-6 mb-1">
                        Fecha:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="date" name="date" id="nuevo-traspasos-date-input" class="form-control" readonly>
                    </div>
                    <div class="col-6 mb-1">
                        Hora:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="time" name="hora" id="nuevo-traspasos-hora-input" class="form-control" readonly>
                    </div>
                    <div class="col-12 my-2">
                        <hr/>
                    </div>
                    <div class="col-6 mb-1">
                        Merma (opcional):
                    </div>
                    <div class="col-6 mb-1">
                        <div class="input-group">
                            <input type="number" step="0.1" min="0" class="form-control" id="nuevo-traspasos-merma-input" value="0">
                            <span class="input-group-text">L</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="agregar-traspasos-agregar-btn">
                    Traspasar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Revertir Traspaso -->
<div class="modal modal-fade" tabindex="-1" role="dialog" id="revertir-traspaso-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-undo me-2"></i>Revertir Traspaso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de revertir este traspaso?</p>
                <p>La cerveza será devuelta de <strong id="revertir-traspaso-destino"></strong> a <strong id="revertir-traspaso-origen"></strong>.</p>
                <input type="hidden" id="revertir-traspaso-id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="revertir-traspaso-confirmar-btn">
                    <i class="fas fa-undo me-1"></i> Revertir
                </button>
            </div>
        </div>
    </div>
</div>

  <div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Batch</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>¿Desea <b>eliminar</b> este Batch?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="finalizar-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirmación de Finalización</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>¿Confirma la <b>finalización</b> este Batch?<br/>Este paso no es reversible y posteriormente no podrá realizar cambios.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="finalizar-aceptar-btn" data-bs-dismiss="modal">
            <i class="bi bi-check"></i>
            Finalizar
        </button>
        </div>
      </div>
    </div>
  </div>


  <div class="modal modal-fade" tabindex="-1" role="dialog" id="agregar-fermentadores-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Agregar Fermentador
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-2">
                        Fermentador:
                    </div>
                    <div class="col-6 mb-2">
                        <select class="form-control" id="agregar-fermentadores_id_activos-select">
                            <?php
                            foreach($activos_disponibles as $activo) {
                            ?>
                            <option value="<?= $activo->id; ?>" data-litraje="<?= $activo->litraje; ?>"><?= $activo->codigo; ?> (<?= $activo->litraje; ?>L)</option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-6 mb-2">
                        Capacidad:
                    </div>
                    <div class="col-6 mb-2">
                        <div class="input-group">
                            <input type="number" class="form-control" id="agregar-fermentadores-cantidad-input" value="0" readonly>
                            <span class="input-group-text">L</span>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="alert alert-info mb-0" id="agregar-fermentadores-info">
                            <small>El fermentador se agregará con estado <strong>Fermentación</strong> y su capacidad completa.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="agregar-fermentadores-aceptar-btn">
                    Agregar Fermentador
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar Fermentador -->
<div class="modal modal-fade" tabindex="-1" role="dialog" id="eliminar-fermentador-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Eliminar Fermentador</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de quitar este fermentador del batch?</p>
                <p>Fermentador: <strong id="eliminar-fermentador-codigo"></strong></p>
                <p class="text-muted"><small>El fermentador quedará disponible para otros batches.</small></p>
                <input type="hidden" id="eliminar-fermentador-id-batches-activos" value="">
                <input type="hidden" id="eliminar-fermentador-id-batches" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="eliminar-fermentador-confirmar-btn">
                    <i class="fas fa-trash me-1"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.field-error {
  border-color: #dc3545 !important;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.error-message {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
  display: block;
}

.field-error-container {
  position: relative;
}

.etapa-btn.has-errors {
  border-left: 4px solid #dc3545 !important;
  background-color: rgba(220, 53, 69, 0.1) !important;
}

.etapa-btn.has-errors i {
  color: #dc3545 !important;
}
</style>

<script>

var obj = <?= json_encode($batch,JSON_PRETTY_PRINT); ?>;
var tipos_de_insumos = <?= json_encode($tipos_de_insumos,JSON_PRETTY_PRINT); ?>;
var tipo_de_insumo = {};
var insumos = [];
var insumo = {};
var etapa_seleccionada = '<?= $batch->etapa_seleccionada; ?>';
var index_seleccionado = 0;
var lupulizaciones = <?= json_encode($lupulizaciones,JSON_PRETTY_PRINT); ?>;
var enfriados = <?= json_encode($enfriados,JSON_PRETTY_PRINT); ?>;
var traspasos = <?= json_encode($traspasos,JSON_PRETTY_PRINT); ?>;
var lista = <?= json_encode($insumos_arr,JSON_PRETTY_PRINT); ?>;

var fermentadores = <?= json_encode($activos,JSON_PRETTY_PRINT); ?>;
var fermentadores_disponibles = <?= json_encode($activos_disponibles,JSON_PRETTY_PRINT); ?>;
var fermentadores_usados = [];

var fermentacion_fermentadores = <?= json_encode($fermentacion_fermentadores,JSON_PRETTY_PRINT); ?>;

// Variables para traspasos asíncronos
var batch_activos_para_traspaso = <?= json_encode($batch_activos_para_traspaso, JSON_PRETTY_PRINT); ?>;
var activos_disponibles_traspaso = <?= json_encode($activos_disponibles_traspaso, JSON_PRETTY_PRINT); ?>;
var id_batches_actual = '<?= $batch->id; ?>';

var lista_selected = false;

var etapas = ['licor','maceracion','coccion','inoculacion','lupulizacion','enfriado'];


// Validación en tiempo real para campos numéricos
$(document).on('blur', 'input[type="number"]', function() {
  var $input = $(this);
  var nombre = $input.attr('name');
  var valor = $input.val();

  // Limpiar errores previos de este campo
  $input.removeClass('field-error');
  $input.closest('.col-md-6, .col-12').find('.error-message').remove();

  // Validar según el tipo de campo
  if (nombre && nombre.includes('ph')) {
    if (valor && (parseFloat(valor) < 0 || parseFloat(valor) > 14)) {
      $input.addClass('field-error');
      var $errorMsg = $('<span class="error-message">El pH debe estar entre 0 y 14</span>');
      $input.closest('.col-md-6, .col-12').append($errorMsg);
    }
  } else if (nombre && nombre.includes('temperatura')) {
    if (valor && parseFloat(valor) < 0) {
      $input.addClass('field-error');
      var $errorMsg = $('<span class="error-message">La temperatura no puede ser negativa</span>');
      $input.closest('.col-md-6, .col-12').append($errorMsg);
    }
  } else if (nombre && nombre.includes('litros')) {
    if (valor && parseFloat(valor) < 0) {
      $input.addClass('field-error');
      var $errorMsg = $('<span class="error-message">Los litros no pueden ser negativos</span>');
      $input.closest('.col-md-6, .col-12').append($errorMsg);
    }
  }

  // Si se modificó batch_litros, actualizar vista de fermentadores
  if (nombre === 'batch_litros') {
    renderFermentacionFermentadores();
  }

  // Actualizar indicadores de tabs
  setTimeout(function() {
    actualizarIndicadoresTabErrores();
  }, 50);
});

$(document).ready(function(){

    $.each(obj,function(key,value){
        if(key!="table_name"&&key!="table_fields"){
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
        }
    });

    renderListaLupulizacion();
    renderListaEnfriado();
    renderListaTraspasos();
    renderFermentacionFermentadores();
    renderLista();
    armarTiposDeInsumosSelect();
    changeTiposDeInsumosSelect();
    changeInsumosSelect();

});

$(document).on('change','select[name="id_tipos_de_insumos"]',function(){
    changeTiposDeInsumosSelect();
    changeInsumosSelect();
});

function armarTiposDeInsumosSelect(tipos_de_insumos_array = false) {

    var tipos_de_insumos_filtrada = [];

    if(!tipos_de_insumos_array) {
        tipos_de_insumos_filtrada = tipos_de_insumos;
    } else
    if(Array.isArray(tipos_de_insumos_array)) {
        tipos_de_insumos_filtrada = tipos_de_insumos.filter( function(ti) {
            return tipos_de_insumos_array.includes(ti.nombre);
        });
    }
    $('select[name="id_tipos_de_insumos"]').empty();
    tipos_de_insumos_filtrada.forEach(function(tdi) {
        $('select[name="id_tipos_de_insumos"]').append("<option value='" + tdi.id + "'>" + tdi.nombre + "</option>");
    });
    changeTiposDeInsumosSelect();
    
}

function changeTiposDeInsumosSelect() {

  $('select[name="id_insumos"]').empty();

  var id_tipos_de_insumos = $('select[name="id_tipos_de_insumos"]').val();
  if(id_tipos_de_insumos == null) {
    return false;
  }

  tipo_de_insumo = tipos_de_insumos.find((tdi) => tdi.id == id_tipos_de_insumos);
  insumos = tipo_de_insumo.insumos;
  insumos.forEach(function(insumo) {
    $('select[name="id_insumos"]').append("<option value='" + insumo.id + "'>" + insumo.nombre + "</option>");
  });
  
}

function changeInsumosSelect() {

  var id_insumos = $('select[name="id_insumos"]').val();
  if(id_insumos == null) {
    return false;
  }

  var insumo = insumos.find((i) => i.id == id_insumos);
  $('#agregar-insumos-unidad-de-medida').html(insumo.unidad_de_medida);
  $('input[name="cantidad"]').val('0');
  $('input[name="monto"]').val('0');

}


$(document).on('change','select[name="id_tipos_de_insumos"]',function(){
    changeTiposDeInsumosSelect();
    changeInsumosSelect();

    // Mostrar/ocultar campos de levadura según tipo
    var tipoSeleccionado = $('select[name="id_tipos_de_insumos"] option:selected').text().trim();
    if(tipoSeleccionado.toLowerCase() === 'levadura' || tipoSeleccionado.toLowerCase() === 'levaduras') {
        $('#campos-levadura-modal').slideDown();
    } else {
        $('#campos-levadura-modal').slideUp();
        // Limpiar campos de levadura
        resetCamposLevaduraModal();
    }
});

// Toggle volumen starter en modal de insumos
$('#lev_uso_starter_modal').change(function() {
    if($(this).is(':checked')) {
        $('#lev-starter-volumen-modal').slideDown();
    } else {
        $('#lev-starter-volumen-modal').slideUp();
        $('input[name="lev_volumen_starter"]').val('');
    }
});

// Función para resetear campos de levadura en el modal
function resetCamposLevaduraModal() {
    $('input[name="lev_generacion"]').val('1');
    $('input[name="lev_tasa_inoculacion"]').val('');
    $('input[name="lev_viabilidad"]').val('');
    $('input[name="lev_vitalidad"]').val('');
    $('input[name="lev_tiempo_lag"]').val('');
    $('input[name="lev_atenuacion"]').val('');
    $('#lev_uso_starter_modal').prop('checked', false);
    $('input[name="lev_volumen_starter"]').val('');
    $('#lev-starter-volumen-modal').hide();
    $('select[name="lev_origen_batch"]').val('');
    $('textarea[name="lev_observaciones"]').val('');
}

// Función para obtener datos de levadura del modal
function getLevaduraDataFromModal() {
    return {
        generacion: $('input[name="lev_generacion"]').val() || 1,
        tasa_inoculacion: $('input[name="lev_tasa_inoculacion"]').val() || 0,
        viabilidad_medida: $('input[name="lev_viabilidad"]').val() || 0,
        vitalidad_medida: $('input[name="lev_vitalidad"]').val() || 0,
        tiempo_lag_h: $('input[name="lev_tiempo_lag"]').val() || 0,
        atenuacion_real: $('input[name="lev_atenuacion"]').val() || 0,
        uso_starter: $('#lev_uso_starter_modal').is(':checked') ? 1 : 0,
        volumen_starter_ml: $('input[name="lev_volumen_starter"]').val() || 0,
        origen_batch: $('select[name="lev_origen_batch"]').val() || '',
        observaciones: $('textarea[name="lev_observaciones"]').val() || ''
    };
}

$(document).on('change','select[name="id_insumos"]',function(){
    changeInsumosSelect();
});


// Función para validar campos
function validarCampos() {
  // Limpiar errores previos
  $('.field-error').removeClass('field-error');
  $('.error-message').remove();

  var errores = [];
  var validaciones = [
    // Validaciones obligatorias de la sección General
    {
      campo: 'batch_nombre',
      nombre: 'Número de Batch',
      etapa: 'batch',
      tab: '#pills-batch-tab',
      validacion: function(val) { return val && val.trim() !== ''; },
      mensaje: 'El número de batch es obligatorio'
    },
    {
      campo: 'batch_date',
      nombre: 'Fecha de Creación',
      etapa: 'batch',
      tab: '#pills-batch-tab',
      validacion: function(val) { return val && val !== ''; },
      mensaje: 'La fecha de creación es obligatoria'
    },
    {
      campo: 'id_recetas',
      nombre: 'Receta',
      etapa: 'batch',
      tab: '#pills-batch-tab',
      validacion: function(val) { return val && val != '0'; },
      mensaje: 'Debe seleccionar una receta'
    },
    {
      campo: 'batch_id_usuarios_cocinero',
      nombre: 'Cocinero',
      etapa: 'batch',
      tab: '#pills-batch-tab',
      validacion: function(val) { return val && val != '0'; },
      mensaje: 'Debe seleccionar un cocinero'
    },
    {
      campo: 'batch_litros',
      nombre: 'Litros',
      etapa: 'batch',
      tab: '#pills-batch-tab',
      validacion: function(val) { return val && parseFloat(val) > 0; },
      mensaje: 'Los litros deben ser mayor a 0'
    },
    // Validaciones condicionales - Licor
    {
      campo: 'licor_temperatura',
      nombre: 'Temperatura (Licor)',
      etapa: 'licor',
      tab: '#pills-licor-tab',
      validacion: function(val) {
        var licorLitros = parseFloat($('[name="licor_litros"]').val());
        if (licorLitros > 0 && (!val || parseFloat(val) < 0)) return false;
        return true;
      },
      mensaje: 'La temperatura no puede ser negativa'
    },
    {
      campo: 'licor_ph',
      nombre: 'pH (Licor)',
      etapa: 'licor',
      tab: '#pills-licor-tab',
      validacion: function(val) {
        var licorLitros = parseFloat($('[name="licor_litros"]').val());
        if (licorLitros > 0 && val && (parseFloat(val) < 0 || parseFloat(val) > 14)) return false;
        return true;
      },
      mensaje: 'El pH debe estar entre 0 y 14'
    },
    // Validaciones condicionales - Maceración
    {
      campo: 'maceracion_temperatura',
      nombre: 'Temperatura (Maceración)',
      etapa: 'maceracion',
      tab: '#pills-maceracion-tab',
      validacion: function(val) {
        var maceracionLitros = parseFloat($('[name="maceracion_litros"]').val());
        if (maceracionLitros > 0 && val && parseFloat(val) < 0) return false;
        return true;
      },
      mensaje: 'La temperatura no puede ser negativa'
    },
    {
      campo: 'maceracion_ph',
      nombre: 'pH (Maceración)',
      etapa: 'maceracion',
      tab: '#pills-maceracion-tab',
      validacion: function(val) {
        var maceracionLitros = parseFloat($('[name="maceracion_litros"]').val());
        if (maceracionLitros > 0 && val && (parseFloat(val) < 0 || parseFloat(val) > 14)) return false;
        return true;
      },
      mensaje: 'El pH debe estar entre 0 y 14'
    },
    // Validaciones condicionales - Cocción
    {
      campo: 'coccion_ph_inicial',
      nombre: 'pH Inicial (Cocción)',
      etapa: 'coccion',
      tab: '#pills-coccion-tab',
      validacion: function(val) {
        if (val && (parseFloat(val) < 0 || parseFloat(val) > 14)) return false;
        return true;
      },
      mensaje: 'El pH debe estar entre 0 y 14'
    },
    {
      campo: 'coccion_ph_final',
      nombre: 'pH Final (Cocción)',
      etapa: 'coccion',
      tab: '#pills-coccion-tab',
      validacion: function(val) {
        if (val && (parseFloat(val) < 0 || parseFloat(val) > 14)) return false;
        return true;
      },
      mensaje: 'El pH debe estar entre 0 y 14'
    },
    // Validaciones condicionales - Fermentación
    {
      campo: 'fermentacion_ph',
      nombre: 'pH (Fermentación)',
      etapa: 'fermentacion',
      tab: '#pills-fermentacion-tab',
      validacion: function(val) {
        if (val && (parseFloat(val) < 0 || parseFloat(val) > 14)) return false;
        return true;
      },
      mensaje: 'El pH debe estar entre 0 y 14'
    },
    // Validaciones campos ML (opcionales pero con límites)
    {
      campo: 'abv_final',
      nombre: 'ABV Final',
      etapa: 'batch',
      tab: '#pills-batch-tab',
      validacion: function(val) {
        if (val === '' || val === null) return true; // Opcional
        var num = parseFloat(val);
        return !isNaN(num) && num >= 0 && num <= 99.99;
      },
      mensaje: 'ABV Final debe estar entre 0 y 99.99%'
    },
    {
      campo: 'ibu_final',
      nombre: 'IBU Final',
      etapa: 'batch',
      tab: '#pills-batch-tab',
      validacion: function(val) {
        if (val === '' || val === null) return true; // Opcional
        var num = parseInt(val);
        return !isNaN(num) && num >= 0;
      },
      mensaje: 'IBU Final debe ser un número positivo'
    },
    {
      campo: 'color_ebc',
      nombre: 'Color EBC',
      etapa: 'batch',
      tab: '#pills-batch-tab',
      validacion: function(val) {
        if (val === '' || val === null) return true; // Opcional
        var num = parseInt(val);
        return !isNaN(num) && num >= 0;
      },
      mensaje: 'Color EBC debe ser un número positivo'
    },
    {
      campo: 'calificacion_sensorial',
      nombre: 'Calificación Sensorial',
      etapa: 'batch',
      tab: '#pills-batch-tab',
      validacion: function(val) {
        if (val === '' || val === null) return true; // Opcional
        var num = parseInt(val);
        return !isNaN(num) && num >= 1 && num <= 127;
      },
      mensaje: 'Calificación Sensorial debe estar entre 1 y 127'
    },
    {
      campo: 'rendimiento_litros_final',
      nombre: 'Rendimiento Final',
      etapa: 'batch',
      tab: '#pills-batch-tab',
      validacion: function(val) {
        if (val === '' || val === null) return true; // Opcional
        var num = parseFloat(val);
        return !isNaN(num) && num >= 0;
      },
      mensaje: 'Rendimiento Final debe ser un número positivo'
    },
    {
      campo: 'merma_total_litros',
      nombre: 'Merma Total',
      etapa: 'batch',
      tab: '#pills-batch-tab',
      validacion: function(val) {
        if (val === '' || val === null) return true; // Opcional
        var num = parseFloat(val);
        return !isNaN(num) && num >= 0;
      },
      mensaje: 'Merma Total debe ser un número positivo'
    },
    {
      campo: 'densidad_final_verificada',
      nombre: 'Densidad Final',
      etapa: 'batch',
      tab: '#pills-batch-tab',
      validacion: function(val) {
        if (val === '' || val === null) return true; // Opcional
        var num = parseFloat(val);
        return !isNaN(num) && num >= 0 && num <= 99.999;
      },
      mensaje: 'Densidad Final debe estar entre 0 y 99.999'
    }
  ];

  validaciones.forEach(function(v) {
    var valor = $('[name="' + v.campo + '"]').val();
    if (!v.validacion(valor)) {
      errores.push({
        campo: v.campo,
        mensaje: v.mensaje,
        nombre: v.nombre,
        etapa: v.etapa,
        tab: v.tab
      });
    }
  });

  return errores;
}

// Función para mostrar errores
function mostrarErrores(errores) {
  if (errores.length === 0) return;

  // Limpiar indicadores de error en tabs
  $('.etapa-btn').removeClass('has-errors');

  // Agrupar errores por tab
  var tabsConErrores = {};
  errores.forEach(function(error) {
    if (!tabsConErrores[error.tab]) {
      tabsConErrores[error.tab] = [];
    }
    tabsConErrores[error.tab].push(error);
  });

  // Marcar tabs con errores
  Object.keys(tabsConErrores).forEach(function(tabId) {
    $(tabId).addClass('has-errors');
  });

  // Cambiar al primer tab con error
  var primerError = errores[0];
  $(primerError.tab).click();

  // Esperar a que el tab se active antes de marcar errores
  setTimeout(function() {
    // Marcar campos con error y mostrar mensajes
    errores.forEach(function(error) {
      var $campo = $('[name="' + error.campo + '"]');
      $campo.addClass('field-error');

      // Agregar mensaje de error si no existe
      if ($campo.closest('.col-md-6, .col-12').find('.error-message').length === 0) {
        var $errorMsg = $('<span class="error-message">' + error.mensaje + '</span>');
        $campo.closest('.col-md-6, .col-12').append($errorMsg);
      }
    });

    // Scroll al primer campo con error
    var $primerCampo = $('[name="' + primerError.campo + '"]');
    if ($primerCampo.length > 0) {
      $('html, body').animate({
        scrollTop: $primerCampo.offset().top - 100
      }, 500);
    }
  }, 100);

  // Mostrar alerta con resumen
  var mensajeResumen = 'Por favor corrija los siguientes errores:\n\n';
  var tabsAfectados = {};
  errores.forEach(function(error, index) {
    mensajeResumen += (index + 1) + '. ' + error.nombre + ': ' + error.mensaje + '\n';
    tabsAfectados[error.tab] = true;
  });

  if (Object.keys(tabsAfectados).length > 1) {
    mensajeResumen += '\n⚠️ Los errores están en ' + Object.keys(tabsAfectados).length + ' secciones diferentes.';
  }

  alert(mensajeResumen);
}

// Limpiar errores al escribir
$(document).on('input change', '.field-error', function() {
  $(this).removeClass('field-error');
  $(this).closest('.col-md-6, .col-12').find('.error-message').remove();

  // Verificar si quedan errores en el tab actual
  setTimeout(function() {
    actualizarIndicadoresTabErrores();
  }, 50);
});

// Función para actualizar indicadores de error en tabs
function actualizarIndicadoresTabErrores() {
  $('.etapa-btn').each(function() {
    var tabId = $(this).attr('id');
    var targetId = $(this).data('bs-target');

    // Verificar si el panel del tab tiene errores
    var tieneErrores = $(targetId).find('.field-error').length > 0;

    if (tieneErrores) {
      $(this).addClass('has-errors');
    } else {
      $(this).removeClass('has-errors');
    }
  });
}

$(document).on('click','.guardar-btn',function(e){

  e.preventDefault();

  // Validar campos antes de guardar
  var errores = validarCampos();
  if (errores.length > 0) {
    mostrarErrores(errores);
    return false;
  }

  // Nota: Los fermentadores ahora se manejan de forma asíncrona
  // La validación de capacidad se hace al agregar cada fermentador

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("batch");
  // Convertir arrays/objetos complejos a JSON strings para que PHP los reciba correctamente
  data['insumos'] = JSON.stringify(lista);
  data['etapa_seleccionada'] = etapa_seleccionada;
  data['lupulizaciones'] = JSON.stringify(lupulizaciones);
  data['enfriados'] = JSON.stringify(enfriados);
  // Traspasos ya no se envían aquí - se manejan de forma asíncrona
  // Fermentadores ya no se envían aquí - se manejan de forma asíncrona
  data['tipo'] = $(e.currentTarget).data('tipo');

  // Agregar campos ML manualmente
  data['abv_final'] = $('input[name="abv_final"]').val();
  data['ibu_final'] = $('input[name="ibu_final"]').val();
  data['color_ebc'] = $('input[name="color_ebc"]').val();
  data['calificacion_sensorial'] = $('input[name="calificacion_sensorial"]').val();
  data['notas_cata'] = $('textarea[name="notas_cata"]').val();
  data['rendimiento_litros_final'] = $('input[name="rendimiento_litros_final"]').val();
  data['merma_total_litros'] = $('input[name="merma_total_litros"]').val();
  data['densidad_final_verificada'] = $('input[name="densidad_final_verificada"]').val();

  data['debug_sql'] = 1

  $.post(url,data,function(response_raw){
    console.log(response_raw)
    var response = (typeof response_raw === 'string') ? JSON.parse(response_raw) : response_raw;

    if(response.status == "ERROR" || response.mensaje != "OK") {
      alert("Error al guardar el batch: " + (response.mensaje || "Error desconocido"));
      return false;
    } else {
      window.location.href = './?s=nuevo-batches&id=' + response.obj.id;
    }
  }).fail(function(xhr, status, error){
    alert("Error de conexión al guardar. Por favor intente nuevamente.");
  });
});


$(document).on('click','.agregar-insumos-btn',function(e){
  e.preventDefault();
  lista_selected = $(e.currentTarget).data('etapa');
  filter_array = false;
  if(lista_selected == 'licor'  || lista_selected == 'coccion') {
    filter_array = ['Quimicos'];
  } else 
  if(lista_selected == 'lupulizacion' ) {
    filter_array = ['Lupulos'];
  } else
  if(lista_selected == 'maceracion') {
    filter_array = ['Grano','Quimicos'];
  }
  armarTiposDeInsumosSelect(filter_array);
  index_seleccionado = $(e.currentTarget).data('index');
  $('#agregar-insumos-modal').modal('toggle');
});

$(document).on('click','#agregar-insumos-aceptar',function(e){

  e.preventDefault();

  var id_insumos = $('select[name="id_insumos"]').val();
  var insumo = insumos.find((ins) => ins.id == id_insumos);
  var insumo_new = JSON.parse(JSON.stringify(insumo));
  insumo_new.cantidad = $('input[name="cantidad"').val();
  insumo_new.etapa_index = index_seleccionado;

  // Si es tipo levadura, agregar datos específicos
  var tipoSeleccionado = $('select[name="id_tipos_de_insumos"] option:selected').text().trim();
  if(tipoSeleccionado.toLowerCase() === 'levadura' || tipoSeleccionado.toLowerCase() === 'levaduras') {
    insumo_new.levadura_data = getLevaduraDataFromModal();
    insumo_new.es_levadura = true;
  }

  lista[lista_selected].push(insumo_new);
  renderLista();

  // Resetear campos de levadura para el próximo uso
  resetCamposLevaduraModal();
  $('#campos-levadura-modal').hide();

});

function renderLista() {

  

  etapas.forEach(function(et,et_index){


    var lista_index = new Array();
    var indexes = new Array();


    lista_index[0] = '';
    indexes[0] = 0;

    if(et == 'lupulizacion') {
        lupulizaciones.forEach((lup,lup_index) => {
            lista_index[lup_index] = '';
            indexes[lup_index] = 0;
        });
    }

    if(et == 'enfriado') {
        enfriados.forEach((lup,lup_index) => {
            lista_index[lup_index] = '';
            indexes[lup_index] = 0;
        });
    }

    
    lista[et].forEach(function(ins,index){
        
        if(lista_index[ins.etapa_index] == undefined) {
            indexes[ins.etapa_index] = 0;
            lista_index[ins.etapa_index] = '';
        }

        lista_index[ins.etapa_index] += '<tr class="insumos-tr" data-index="' + index +'"><td><b>' + ins.nombre;
        lista_index[ins.etapa_index] += '</b></td><td><b>' + ins.cantidad + " " + ins.unidad_de_medida;
        lista_index[ins.etapa_index] += '</b></td><td><b><button class="btn btn-sm item-eliminar-btn" data-index="' + index + '" + data-lista="' + et + '" data-etapaindex="' + ins.etapa_index + '">x</button>';
        lista_index[ins.etapa_index] += '</b></td></tr>';

        indexes[ins.etapa_index] += 1;

    });

    lista_index.forEach(function(html,index) {
        $('#' + et + '-' + index + '-insumos-table').html(html);
    });

    
  });


}

$(document).on('click','.item-eliminar-btn',function(e){

  e.preventDefault();
  var index = $(e.currentTarget).data('index');
  var etapa = $(e.currentTarget).data('lista');
  var etapa_index = $(e.currentTarget).data('etapaindex');

  lista[etapa].splice(index,1);

  renderLista();

});

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-obj-modal').modal('toggle');
})

$(document).on('click','#eliminar-obj-aceptar',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=" + response.table_name + "&msg=2";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','.etapa-btn',function(e){
    var etapa = $(e.currentTarget).data('etapa');
    etapa_seleccionada = etapa;
});

$(document).on('click','#nuevo-lupulizacion-btn',function(e){

    e.preventDefault();

    const fecha = new Date();
    const anio = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0'); // Los meses van de 0 a 11
    const dia = String(fecha.getDate()).padStart(2, '0');
    const horas = String(fecha.getHours()).padStart(2, '0');
    const minutos = String(fecha.getMinutes()).padStart(2, '0');
    const segundos = String(fecha.getSeconds()).padStart(2, '0');

    $('#nuevo-lupulizacion-date').val(anio + '-' + mes + '-' + dia);
    $('#nuevo-lupulizacion-hora').val(horas + ':' + minutos + ':' + segundos);
    $('#nuevo-lupulizacion-modal').modal('toggle');

});


$(document).on('click','#nuevo-lupulizacion-aceptar-btn',function(e){

    e.preventDefault();



    var lupulizacion = {
        'id': '',
        'id_batches': '',
        'seq_index': '',
        'tipo': $('select[name="nuevo-lupulizacion-tipo"]').val(),
        'date': $('#nuevo-lupulizacion-date').val(),
        'hora': $('#nuevo-lupulizacion-hora').val()
    };

    lupulizaciones.push(lupulizacion);
    renderListaLupulizacion();

});


function renderListaLupulizacion() {
  
    var html = '';
    lupulizaciones.forEach(function(lupulizacion,index){
        html += '<div class="p-3 shadow mb-5">';
        html += '<div class="d-flex justify-content-between mb-1">';
        html += '<div><h4>Lupulización #' + (parseInt(index) + 1) + '</h4></div>';
        html += '<button class="btn btn-sm lupulizaciones-item-eliminar-btn" data-index="' + index + '">x</button>';
        html += '</div>';
        html += '<div class="mb-1">' + lupulizacion.date + ' ' + lupulizacion.hora + '</div>';
        html += '<div class="mb-1">Tipo: ' + lupulizacion.tipo + '</div>';
        html += '<table class="table table-striped">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>Insumos</th>';
        html += '<th>Cantidad</th>';
        html += '</thead>';
        html += '<tbody id="lupulizacion-' + index + '-insumos-table">';
        html += '</tbody>';
        html += '</table>';
        html += '<button class="btn btn-primary btn-sm agregar-insumos-btn" data-etapa="lupulizacion" data-index="' + index + '">+ Agregar Insumo</button>';
        html += '</div>';
    });
    $('#lupulizaciones-div').html(html);
    renderLista();


}

$(document).on('click','.lupulizaciones-item-eliminar-btn',function(e){

  e.preventDefault();
  var index = $(e.currentTarget).data('index');
  lupulizaciones.splice(index,1);

  var lista_lupulizacion = JSON.parse(JSON.stringify(lista['lupulizacion'].filter((lup) => lup.etapa_index != index)));


  lista_lupulizacion.forEach((lup,lup_index) => {

    if(lup.etapa_index > index) {
        lup.etapa_index = lup.etapa_index - 1;
    }

  });

  lista['lupulizacion'] = JSON.parse(JSON.stringify(lista_lupulizacion));

  renderListaLupulizacion();

});


$(document).on('click','#nuevo-enfriado-btn',function(e){

    e.preventDefault();

    const fecha = new Date();
    const anio = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0'); // Los meses van de 0 a 11
    const dia = String(fecha.getDate()).padStart(2, '0');
    const horas = String(fecha.getHours()).padStart(2, '0');
    const minutos = String(fecha.getMinutes()).padStart(2, '0');
    const segundos = String(fecha.getSeconds()).padStart(2, '0');

    $('#nuevo-enfriado-date').val(anio + '-' + mes + '-' + dia);
    $('#nuevo-enfriado-hora').val(horas + ':' + minutos + ':' + segundos);
    $('#nuevo-enfriado-modal').modal('toggle');

});


$(document).on('click','#nuevo-enfriado-aceptar-btn',function(e){

    e.preventDefault();

    var enfriado = {
        'id': '',
        'id_batches': '',
        'seq_index': '',
        'temperatura_inicio': $('#nuevo-enfriado-temperatura-inicio').val(),
        'ph': $('#nuevo-enfriado-ph').val(),
        'densidad': $('#nuevo-enfriado-densidad').val(),
        'ph_enfriado': $('#nuevo-enfriado-ph-enfriado').val(),
        'date': $('#nuevo-enfriado-date').val(),
        'hora_inicio': $('#nuevo-enfriado-hora').val()
    };

    enfriados.push(enfriado);
    renderListaEnfriado();

});


function renderListaEnfriado() {
  
    var html = '';
    enfriados.forEach(function(enfriado,index){
        html += '<div class="p-3 shadow mb-5">';
        html += '<div class="d-flex justify-content-between mb-1">';
        html += '<div><h4>Enfriado #' + (parseInt(index) + 1) + '</h4></div>';
        html += '<button class="btn btn-sm enfriados-item-eliminar-btn" data-index="' + index + '">x</button>';
        html += '</div>';
        html += '<div class="mb-1">' + enfriado.date + ' ' + enfriado.hora_inicio + '</div>';
        html += '<div class="mb-1">Temperatura: ' + enfriado.temperatura_inicio + '°C</div>';
        html += '<div class="mb-1">pH: ' + enfriado.ph + '</div>';
        html += '<div class="mb-1">Densidad: ' + enfriado.densidad + '</div>';
        html += '<div class="mb-1">Densidad: ' + enfriado.ph_enfriado + '</div>';
        html += '<table class="table table-striped">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>Insumos</th>';
        html += '<th>Cantidad</th>';
        html += '</thead>';
        html += '<tbody id="enfriado-' + index + '-insumos-table">';
        html += '</tbody>';
        html += '</table>';
        html += '<button class="btn btn-primary btn-sm agregar-insumos-btn" data-etapa="enfriado" data-index="' + index + '">+ Agregar Insumo</button>';
        html += '</div>';
    });
    $('#enfriados-div').html(html);
    renderLista();


}

$(document).on('click','.enfriados-item-eliminar-btn',function(e){

  e.preventDefault();
  var index = $(e.currentTarget).data('index');
  enfriados.splice(index,1);

  var lista_enfriado = JSON.parse(JSON.stringify(lista['enfriado'].filter((lup) => lup.etapa_index != index)));


  lista_enfriado.forEach((lup,lup_index) => {

    if(lup.etapa_index > index) {
        lup.etapa_index = lup.etapa_index - 1;
    }

  });

  lista['enfriado'] = JSON.parse(JSON.stringify(lista_enfriado));

  renderListaEnfriado();

});


// ===========================
// SISTEMA DE TRASPASOS ASÍNCRONOS
// ===========================

// Abrir modal de traspaso
$(document).on('click','#nuevo-traspasos-btn',function(e){
    e.preventDefault();

    // Verificar si hay fermentadores disponibles para traspasar
    if(batch_activos_para_traspaso.length == 0) {
        alert('No hay fermentadores con cerveza disponible para traspasar en este batch.');
        return false;
    }

    if(activos_disponibles_traspaso.length == 0) {
        alert('No hay fermentadores vacíos disponibles para recibir el traspaso.');
        return false;
    }

    // Setear fecha y hora actuales
    const fecha = new Date();
    const anio = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const dia = String(fecha.getDate()).padStart(2, '0');
    const horas = String(fecha.getHours()).padStart(2, '0');
    const minutos = String(fecha.getMinutes()).padStart(2, '0');
    const segundos = String(fecha.getSeconds()).padStart(2, '0');

    $('#nuevo-traspasos-date-input').val(anio + '-' + mes + '-' + dia);
    $('#nuevo-traspasos-hora-input').val(horas + ':' + minutos + ':' + segundos);
    $('#nuevo-traspasos-merma-input').val(0);
    $('#traspaso-warning-label').hide();

    // Validar litrajes al abrir
    validarLitrajesTraspasos();

    $('#nuevo-traspasos-modal').modal('show');
});

// Validar litrajes cuando cambian los selects
$(document).on('change', '#nuevo-traspasos-desde-select, #nuevo-traspasos-hasta-select', function() {
    validarLitrajesTraspasos();
});

function validarLitrajesTraspasos() {
    var $desde = $('#nuevo-traspasos-desde-select option:selected');
    var $hasta = $('#nuevo-traspasos-hasta-select option:selected');

    var litrajeDe = parseInt($desde.data('litraje')) || 0;
    var litrajeHasta = parseInt($hasta.data('litraje')) || 0;

    if(litrajeDe != litrajeHasta) {
        $('#traspaso-warning-label').show();
        $('#agregar-traspasos-agregar-btn').prop('disabled', true);
    } else {
        $('#traspaso-warning-label').hide();
        $('#agregar-traspasos-agregar-btn').prop('disabled', false);
    }
}

// Ejecutar traspaso vía AJAX
$(document).on('click','#agregar-traspasos-agregar-btn',function(e){
    e.preventDefault();

    var $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Traspasando...');

    var data = {
        'id_batches': id_batches_actual,
        'id_fermentadores_inicio': $('#nuevo-traspasos-desde-select').val(),
        'id_fermentadores_final': $('#nuevo-traspasos-hasta-select').val(),
        'date': $('#nuevo-traspasos-date-input').val(),
        'hora': $('#nuevo-traspasos-hora-input').val(),
        'merma_litros': $('#nuevo-traspasos-merma-input').val() || 0
    };

    $.post('./ajax/ajax_agregarTraspasosInventarioProductos.php', data, function(response){
        console.log('Respuesta traspaso:', response);

        if(typeof response === 'string') {
            response = JSON.parse(response);
        }

        if(response.status == 'OK') {
            // Recargar la página para reflejar los cambios
            window.location.href = './?s=nuevo-batches&id=' + id_batches_actual + '&msg=2';
        } else {
            alert('Error: ' + response.mensaje);
            $btn.prop('disabled', false).html('Traspasar');
        }
    }).fail(function(xhr, status, error) {
        console.error('Error AJAX:', error);
        alert('Error al realizar el traspaso. Por favor intente nuevamente.');
        $btn.prop('disabled', false).html('Traspasar');
    });
});

// Renderizar lista de traspasos existentes
function renderListaTraspasos() {
    var html = '';

    if(traspasos.length == 0) {
        html = '<p class="text-muted">No hay traspasos registrados.</p>';
    } else {
        traspasos.forEach(function(traspaso, index){
            var fermentador_inicio = fermentadores.find((f) => f.id == traspaso.id_fermentadores_inicio);
            var fermentador_final = fermentadores.find((f) => f.id == traspaso.id_fermentadores_final);

            html += '<div class="p-3 shadow mb-3 border-start border-4 border-primary">';
            html += '<div class="d-flex justify-content-between align-items-center mb-2">';
            html += '<h5 class="mb-0">Traspaso #' + (parseInt(index) + 1) + '</h5>';
            html += '<button class="btn btn-sm btn-outline-danger revertir-traspaso-btn" data-id="' + traspaso.id + '" data-origen="' + (fermentador_inicio ? fermentador_inicio.codigo : '-') + '" data-destino="' + (fermentador_final ? fermentador_final.codigo : '-') + '"><i class="fas fa-undo"></i></button>';
            html += '</div>';
            html += '<div class="row">';
            html += '<div class="col-6"><small class="text-muted">Fecha:</small><br>' + traspaso.date + ' ' + traspaso.hora + '</div>';
            html += '<div class="col-6"><small class="text-muted">Cantidad:</small><br>' + traspaso.cantidad + ' L</div>';
            html += '</div>';
            html += '<div class="row mt-2">';
            html += '<div class="col-6"><small class="text-muted">Desde:</small><br><strong>' + (fermentador_inicio ? fermentador_inicio.codigo : '-') + '</strong></div>';
            html += '<div class="col-6"><small class="text-muted">Hasta:</small><br><strong>' + (fermentador_final ? fermentador_final.codigo : '-') + '</strong></div>';
            html += '</div>';
            if(traspaso.merma_litros && traspaso.merma_litros > 0) {
                html += '<div class="mt-2"><small class="text-muted">Merma:</small> ' + traspaso.merma_litros + ' L</div>';
            }
            html += '</div>';
        });
    }

    $('#traspasos-div').html(html);
}

// Abrir modal de revertir traspaso
$(document).on('click', '.revertir-traspaso-btn', function(e) {
    e.preventDefault();
    var idTraspaso = $(this).data('id');
    var origen = $(this).data('origen');
    var destino = $(this).data('destino');

    $('#revertir-traspaso-id').val(idTraspaso);
    $('#revertir-traspaso-origen').text(origen);
    $('#revertir-traspaso-destino').text(destino);
    $('#revertir-traspaso-modal').modal('show');
});

// Confirmar revertir traspaso
$(document).on('click', '#revertir-traspaso-confirmar-btn', function(e) {
    e.preventDefault();

    var $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Revirtiendo...');

    var idTraspaso = $('#revertir-traspaso-id').val();
    var url = './ajax/ajax_revertirTraspaso.php';
    var data = {
        'id_traspaso': idTraspaso
    };

    $.post(url, data, function(response) {
        console.log('Respuesta revertir:', response);

        if(typeof response === 'string') {
            response = JSON.parse(response);
        }

        if(response.status == 'OK') {
            window.location.href = './?s=nuevo-batches&id=' + id_batches_actual + '&msg=1&msg_content=' + encodeURIComponent(response.mensaje);
        } else {
            alert('Error: ' + response.mensaje);
            $btn.prop('disabled', false).html('<i class="fas fa-undo me-1"></i> Revertir');
            $('#revertir-traspaso-modal').modal('hide');
        }
    }).fail(function(xhr, status, error) {
        console.error('Error AJAX:', error);
        alert('Error al revertir el traspaso.');
        $btn.prop('disabled', false).html('<i class="fas fa-undo me-1"></i> Revertir');
    });
});

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-obj-modal').modal('toggle');
})

$(document).on('click','#eliminar-obj-aceptar',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=" + response.table_name + "&msg=2";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#finalizar-btn',function(e){
    e.preventDefault();
});

$(document).on('click','#finalizar-aceptar-btn',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'entidad': obj.table_name,
    'finalizar_date': '<?= date('Y-m-d'); ?>'
  };
  var url = './ajax/ajax_guardarEntidad.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=bitacora-batches&id=" + obj.id;
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});




// ===========================
// SISTEMA DE FERMENTADORES ASÍNCRONOS
// ===========================

// Función para calcular litros disponibles (mantenida para visualización)
function calcularLitrosDisponibles() {
    var batchLitros = parseFloat($('[name="batch_litros"]').val()) || 0;
    var litrosAsignados = 0;

    fermentacion_fermentadores.forEach(function(f) {
        litrosAsignados += parseFloat(f.litraje) || 0;
    });

    var litrosDisponibles = batchLitros - litrosAsignados;

    return {
        total: batchLitros,
        asignado: litrosAsignados,
        disponible: litrosDisponibles
    };
}

// Abrir modal de agregar fermentador
$(document).on('click','#agregar-fermentadores-btn',function(e){
    e.preventDefault();

    // Verificar que hay fermentadores disponibles
    var $select = $('#agregar-fermentadores_id_activos-select');
    if($select.find('option').length == 0) {
        alert('No hay fermentadores disponibles para agregar.');
        return false;
    }

    // Actualizar cantidad con el primer fermentador
    var litraje = $select.find('option:first').data('litraje') || 0;
    $('#agregar-fermentadores-cantidad-input').val(litraje);

    $('#agregar-fermentadores-modal').modal('show');
});

// Al cambiar fermentador seleccionado
$(document).on('change','#agregar-fermentadores_id_activos-select',function(e){
    var litraje = $(this).find('option:selected').data('litraje') || 0;
    $('#agregar-fermentadores-cantidad-input').val(litraje);
});

// Agregar fermentador vía AJAX
$(document).on('click','#agregar-fermentadores-aceptar-btn',function(e){
    e.preventDefault();

    var $btn = $(this);
    var id_activos = $('#agregar-fermentadores_id_activos-select').val();

    if(!id_activos) {
        alert('Seleccione un fermentador');
        return false;
    }

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Agregando...');

    var data = {
        'id_batches': id_batches_actual,
        'id_activos': id_activos,
        'estado': 'Fermentación'
    };

    $.post('./ajax/ajax_inventarioProductosBatchActivoAgregar.php', data, function(response){
        console.log('Respuesta agregar fermentador:', response);

        if(typeof response === 'string') {
            response = JSON.parse(response);
        }

        if(response.status == 'OK') {
            window.location.href = './?s=nuevo-batches&id=' + id_batches_actual + '&msg=1&msg_content=' + encodeURIComponent(response.msg_content);
        } else {
            alert('Error: ' + (response.mensaje || response.msg_content || 'Error desconocido'));
            $btn.prop('disabled', false).html('Agregar Fermentador');
        }
    }).fail(function(xhr, status, error) {
        console.error('Error AJAX:', error);
        alert('Error al agregar fermentador. Por favor intente nuevamente.');
        $btn.prop('disabled', false).html('Agregar Fermentador');
    });
});

// Renderizar lista de fermentadores
function renderFermentacionFermentadores() {
    var html = '';
    var litraje_total = 0;

    if(fermentacion_fermentadores.length == 0) {
        html = '<tr><td colspan="4" class="text-center text-muted">No hay fermentadores asignados</td></tr>';
    } else {
        fermentacion_fermentadores.forEach(function(batch_activo, index){
            var fermentador = fermentadores.find((f) => f.id == batch_activo.id_activos);
            var codigo = fermentador ? fermentador.codigo : 'N/A';

            html += '<tr>';
            html += '<td>' + codigo + '</td>';
            html += '<td>' + batch_activo.litraje + ' L</td>';
            html += '<td><span class="badge bg-primary">' + batch_activo.estado + '</span></td>';
            html += '<td>';
            html += '<button class="btn btn-sm btn-outline-danger fermentacion-fermentador-eliminar-btn" ';
            html += 'data-id="' + batch_activo.id + '" ';
            html += 'data-codigo="' + codigo + '">';
            html += '<i class="fas fa-times"></i>';
            html += '</button>';
            html += '</td>';
            html += '</tr>';

            litraje_total += parseInt(batch_activo.litraje) || 0;
        });
    }

    $('#fermentacion-fermentadores-tbody').html(html);
    $('#fermentacion-fermentadores-litraje-total-span').html(litraje_total);

    // Actualizar información de capacidad
    var capacidad = calcularLitrosDisponibles();
    var infoHtml = '';

    if (capacidad.total > 0) {
        var porcentajeUsado = (capacidad.asignado / capacidad.total) * 100;
        var colorBarra = '#28a745';
        var mensaje = '';

        if (porcentajeUsado > 100) {
            colorBarra = '#dc3545';
            mensaje = '⚠️ ¡EXCEDE LA CAPACIDAD DEL BATCH!';
        } else if (porcentajeUsado > 95) {
            colorBarra = '#ffc107';
            mensaje = '⚠️ Casi al límite de capacidad';
        } else if (porcentajeUsado >= 80) {
            colorBarra = '#17a2b8';
            mensaje = 'Buen uso de capacidad';
        }

        infoHtml = '<div style="font-size: 0.9rem;">';
        infoHtml += '<div class="d-flex justify-content-between mb-1">';
        infoHtml += '<span><strong>Capacidad del Batch:</strong></span>';
        infoHtml += '<span>' + capacidad.total + ' L</span>';
        infoHtml += '</div>';
        infoHtml += '<div class="d-flex justify-content-between mb-2">';
        infoHtml += '<span><strong>Disponible:</strong></span>';
        infoHtml += '<span style="color: ' + colorBarra + '; font-weight: bold;">' + capacidad.disponible.toFixed(1) + ' L</span>';
        infoHtml += '</div>';
        infoHtml += '<div class="progress" style="height: 20px;">';
        infoHtml += '<div class="progress-bar" role="progressbar" style="width: ' + Math.min(porcentajeUsado, 100) + '%; background-color: ' + colorBarra + ';">';
        infoHtml += porcentajeUsado.toFixed(1) + '%';
        infoHtml += '</div></div>';
        if (mensaje) {
            infoHtml += '<div class="mt-2" style="color: ' + colorBarra + '; font-weight: bold;">' + mensaje + '</div>';
        }
        infoHtml += '</div>';
    }

    $('#fermentacion-capacidad-info').html(infoHtml);
}

// Abrir modal de eliminar fermentador
$(document).on('click', '.fermentacion-fermentador-eliminar-btn', function(e){
    e.preventDefault();
    var idBatchActivo = $(this).data('id');
    var codigo = $(this).data('codigo');

    $('#eliminar-fermentador-id-batches-activos').val(idBatchActivo);
    $('#eliminar-fermentador-id-batches').val(id_batches_actual);
    $('#eliminar-fermentador-codigo').text(codigo);
    $('#eliminar-fermentador-modal').modal('show');
});

// Confirmar eliminar fermentador
$(document).on('click', '#eliminar-fermentador-confirmar-btn', function(e){
    e.preventDefault();

    var $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Eliminando...');

    var data = {
        'id_batches': $('#eliminar-fermentador-id-batches').val(),
        'id_batches_activos': $('#eliminar-fermentador-id-batches-activos').val()
    };

    $.post('./ajax/ajax_inventarioProductosBatchActivoEliminar.php', data, function(response){
        console.log('Respuesta eliminar fermentador:', response);

        if(typeof response === 'string') {
            response = JSON.parse(response);
        }

        if(response.status == 'OK') {
            window.location.href = './?s=nuevo-batches&id=' + id_batches_actual + '&msg=1&msg_content=' + encodeURIComponent(response.msg_content);
        } else {
            alert('Error: ' + (response.mensaje || response.msg_content || 'Error desconocido'));
            $btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Eliminar');
            $('#eliminar-fermentador-modal').modal('hide');
        }
    }).fail(function(xhr, status, error) {
        console.error('Error AJAX:', error);
        alert('Error al eliminar fermentador.');
        $btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Eliminar');
    });
});

// Función legacy para compatibilidad (ya no se usa activamente)
function armarFermentadoresSelect() {
    var fermentadores_select_html = '';
    fermentadores_disponibles.sort();
    fermentadores_disponibles.forEach(function(f){
        fermentadores_select_html += '<option value="' + f.id + '" data-litraje="' + f.litraje + '">' + f.codigo + ' (' + f.litraje + 'L)</option>';
    });
    $('#agregar-fermentadores_id_activos-select').html(fermentadores_select_html);
}

$(document).on('keyup', '.acero-float', function() {
  $(this).val(
    $(this).val()
      .replace(/[^0-9.]/g, '') 
      .replace(/\.(?=.*\.)/g, '') 
  );
});

$(document).on('change', '.acero-float', function() {
  if ($(this).val() === '') {
    $(this).val(0);
  }
  const floatVal = parseFloat($(this).val());
  $(this).val(isNaN(floatVal) ? 0 : floatVal);
});


</script>