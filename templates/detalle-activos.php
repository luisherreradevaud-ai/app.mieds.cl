<?php

  if(!validaIdExists($_GET,'id')) {
    die();
  }

  $obj = new Activo($_GET['id']);
  $obj->getAccesorios();
  $media_arr = $obj->getMedia();
  $media_header = $obj->getMediaHeader();

  $usuarios_control = Usuario::getAll("WHERE nivel!='Cliente' AND nivel!='Administrador'");
  $clientes_ubicacion = Cliente::getAll();
  $usuario = $GLOBALS['usuario'];

  $locaciones = Locacion::getAll('ORDER BY nombre asc');
  $clases = Activo::getClases();
  $lineas_productivas = Activo::getLineasProductivas();

?>

<style>
.tr-proveedor {
  cursor: pointer;
}
.media-activo {
  border: 2px solid red;
}

.obj-archivo-img {
  cursor: pointer;
}

/* Campos readonly con fondo gris */
input[readonly],
textarea[readonly] {
  background-color: #e9ecef !important;
  color: #6c757d;
}
</style>

<script src="./js/ckeditor/ckeditor.js"></script>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Activo
      </b>
    </h1>
  </div>
  <div>
    <button class="btn btn-warning btn-sm d-none" type="button" id="editar-btn">
        <i class="fas fa-fw fa-lock"></i> Editar
    </button>
      <?php $usuario->printReturnBtn(); ?>

  </div>
</div>

<?php 
  Msg::show(1,'Activo guardado con &eacute;xito','primary');
  Msg::show(2,'Media eliminada con &eacute;xito','danger');
  Msg::show(6,'Imagen principal cambiada con &eacute;xito','primary');
?>

<form id="activos-form">
<input type="hidden" name="id" value="">
<input type="hidden" name="entidad" value="activos">
<input type="hidden" name="id_usuarios" value="<?= $GLOBALS['usuario']->id; ?>">

<div class="card">
  <div class="card-body">
  <h1 class="h4 mb-0 text-gray-800">
    <b>
        Datos generales
    </b>
  </h1>
  <div class="row mt-4">
    <div class="col-md-7 row">
      <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="nombre">
      </div>
      <div class="col-6 mb-1">
        Clase:
      </div>
      <div class="col-6 mb-1">
        <select name="clase" class="form-control">
          <?php
            foreach($clases as $clase) {
              ?>
              <option>
                <?= $clase; ?>
              </option>
              <?php
            }
          ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Marca:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="marca">
      </div>
      <div class="col-6 mb-1">
        Modelo:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="modelo">
      </div>
      <div class="col-6 mb-1">
        C&oacute;digo:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="codigo">
      </div>
      <div class="col-6 mb-1">
        Capacidad:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="capacidad">
      </div>
      <div class="col-6 mb-1">
        Clasificaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="clasificacion">
          <option>Cr&iacute;tico</option>
          <option>Importante</option>
          <option>Poco importante</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Litraje:
      </div>
      <div class="col-6 mb-1">
        <div class="input-group">
            <input type="number" class="form-control acero-float" name="litraje"  value="0" step="0.1" min="0">
            <span class="input-group-text" style="border-radius: 0px 10px 10px 0px">Litros</span>
        </div>
      </div>
      <div class="col-6 mb-1">
        Valorizaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <input type="number" class="form-control" name="valorizacion">
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="estado">
          <option>Activo</option>
          <option>Con observaciones</option>
          <option>Inactivo</option>
        </select>
      </div>

      <div class="col-6 mb-1">
        L&iacute;nea Productiva:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="linea_productiva">
          <?php
            foreach($lineas_productivas as $key => $label) {
              ?>
              <option value="<?= $key; ?>">
                <?= $label; ?>
              </option>
              <?php
            }
          ?>
        </select>
      </div>

      <div class="col-6 mb-1">
        Ubicaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="ubicacion">
          <option>En planta</option>
          <option>En terreno</option>
        </select>
      </div>

      <div class="col-6 mb-1 en-terreno">
        Cliente:
      </div>
      <div class="col-6 mb-1 en-terreno">
        <select class="form-control" name="id_clientes_ubicacion">
          <?php
          foreach($clientes_ubicacion as $cu) {
            print "<option value='".$cu->id."'>".$cu->nombre."</option>";
          }
          ?>
        </select>
      </div>

      <div class="col-6 mb-1 en-planta">
        Locación:
      </div>
      <div class="col-6 mb-1 en-planta">
        <select class="form-control" name="id_locaciones">
          <?php
          foreach($locaciones as $locacion) {
            print "<option value='".$locacion->id."'>".$locacion->nombre."</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-12 mb-2 mt-2">
        <hr/>
      </div>
      <div class="col-6 mb-1">
        Propietario:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="propietario">
      </div>
      <div class="col-6 mb-1">
        Fecha adquisici&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <input type="date" class="form-control" name="adquisicion_date">
      </div>
      <div class="col-6 mb-1">
        Responsabilidad / control:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="id_usuarios_control">
        <?php
          foreach($usuarios_control as $uc) {
            print "<option value='".$uc->id."'>".$uc->nombre."</option>";
          }
        ?>
        </select>
      </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <img src="./media/thumbnails/640/<?= $media_header->url; ?>" class="w-100">
            <?php
            if($media_header->id == 0) {
            ?>
            <div class="d-sm-flex text-center mb-4">
            &nbsp;&nbsp;<button class="btn btn-sm btn-primary shadow-sm subir-media-btn">+ Subir imagen</button>
            </div>
            <?php } ?>
        </div>
    </div>
    </div>
  </div>
</div>
  <div class="row">



    <div class="col-12 mb-2 mt-3">
      <div class="card">
        <div class="card-body">
          <h1 class="h4 mb-0 text-gray-800">
            <b>
                Accesorios
            </b>
          </h1>
          <table class="table table-sm table-stripped mt-4">
            <thead>
              <tr>
                <th>
                  Nombre 
                </th>
                <th>
                  Marca
                </th>
                <th>
                  Último cambio 
                </th>
              </tr>
            </thead>
            <tbody>
              <?php
              foreach($obj->accesorios as $accesorio) {
              ?>
              <tr>
                <td>
                  <?= $accesorio->nombre; ?>
                </td>
                <td>
                  <?= $accesorio->marca; ?>
                </td>
                <td>
                  <?= $accesorio->ultimo_cambio; ?>
                </td>
                <td>
                <td class="text-end">
                <button class="btn btn-sm btn-primary editar-accesorio-btn"
                        data-id="<?= $accesorio->id; ?>"
                        data-nombre="<?= htmlspecialchars($accesorio->nombre); ?>"
                        data-marca="<?= htmlspecialchars($accesorio->marca); ?>"
                        data-observaciones="<?= htmlspecialchars($accesorio->observaciones); ?>"
                        >
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger eliminar-accesorio-btn"
                        data-id="<?= $accesorio->id; ?>">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </td>
              </tr>
              <?php
              }
              ?>
            </tbody>
          </table>
          <button class="btn btn-primary btn-sm mt-3" id="nuevo-accesorio-abrir-modal-btn">
            <i class="fas fa-plus"></i> Nuevo Accesorio
          </button>
        </div>
      </div>


        
    </div>

    



    <div class="col-12 mb-2 mt-3">
        <hr/>
    </div>
    <div class="col-12 mb-4 mt-2">
        <h1 class="h4 mb-0 text-gray-800">
        <b>
            Inspecciones
        </b>
        </h1>
    </div>

    <div class="col-12 row">
      <div class="col-md-3 mb-1">
        Periodicidad:
      </div>
      <div class="col-md-3 mb-1">
        <select class="form-control" name="inspeccion_periodicidad">
            <option>Inmediata Tras Uso</option>
            <option>Semanal</option>
            <option>Quincenal</option>
            <option>Mensual</option>
            <option>Bimestral</option>
            <option>Trimestral</option>
            <option>Semestral</option>
            <option>Anual</option>
        </select>
      </div>
    </div>
    <div class="col-12 row">
      <div class="col-md-3 mb-1">
        &Uacute;ltima:
      </div>
      <div class="col-md-3 mb-1">
        <input type="date" class="form-control" name="ultima_inspeccion">
      </div>
      <div class="col-md-3 mb-1">
        Pr&oacute;xima:
      </div>
      <div class="col-md-3 mb-1">
        <input type="date" class="form-control" name="proxima_inspeccion">
      </div>
    </div>
    <div class="col-12 mt-1 mb-1">
        Procedimiento:
        <br/>
        <br/>
        <textarea class="form-control" name="inspeccion_procedimiento" id="inspeccion_procedimiento"></textarea>
    </div>
    <div class="col-12 mb-4 mt-5">
        <h1 class="h4 mb-0 text-gray-800">
        <b>
            Mantenci&oacute;n
        </b>
        </h1>
    </div>
    
    <div class="col-12 row">
      <div class="col-md-3 mb-1">
        Periodicidad:
      </div>
      <div class="col-md-3 mb-1">
        <select class="form-control" name="mantencion_periodicidad">
            <option>Inmediata Tras Uso</option>
            <option>Semanal</option>
            <option>Quincenal</option>
            <option>Mensual</option>
            <option>Bimestral</option>
            <option>Trimestral</option>
            <option>Semestral</option>
            <option>Anual</option>
        </select>
      </div>
    </div>
    <div class="col-12 row">
      <div class="col-md-3 mb-1">
        &Uacute;ltima:
      </div>
      <div class="col-md-3 mb-1">
        <input type="date" class="form-control" name="ultima_mantencion">
      </div>
      <div class="col-md-3 mb-1">
        Pr&oacute;xima:
      </div>
      <div class="col-md-3 mb-1">
        <input type="date" class="form-control" name="proxima_mantencion">
      </div>
    </div>
    <div class="col-12 mt-1 mb-1">
        Procedimiento:
        <br/>
        <br/>
        <textarea class="form-control" name="mantencion_procedimiento" id="mantencion_procedimiento"></textarea>
    </div>

    <!-- SECCIÓN: Limpiezas -->
    <div class="col-12 mt-3 mb-3">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h6 class="mb-0"><i class="fas fa-broom"></i> Limpiezas</h6>
          <button class="btn btn-primary btn-sm" id="registrar-limpieza-btn" type="button">
            <i class="fas fa-plus"></i> Registrar Limpieza
          </button>
        </div>
        <div class="card-body">
          <!-- Configuración de Limpiezas -->
          <div class="row mb-3">
            <div class="col-md-3 mb-2">
              <label class="form-label">Periodicidad:</label>
              <select class="form-control" name="limpieza_periodicidad">
                <?php
                $periodicidades = Activo::getPeriodicidadesLimpieza();
                foreach($periodicidades as $key => $label) {
                  echo "<option value='{$key}'>{$label}</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <label class="form-label">&Uacute;ltima limpieza:</label>
              <input type="text" class="form-control" name="fecha_ultima_limpieza" readonly
                     value="<?= $obj->fecha_ultima_limpieza ? date('d/m/Y H:i', strtotime($obj->fecha_ultima_limpieza)) : 'Sin registrar'; ?>">
            </div>
            <div class="col-md-3 mb-2">
              <label class="form-label">Pr&oacute;xima limpieza:</label>
              <input type="date" class="form-control" name="proxima_limpieza">
            </div>
            <div class="col-md-3 mb-2">
              <label class="form-label">Procedimiento:</label>
              <textarea class="form-control" name="limpieza_procedimiento" rows="1"
                        placeholder="Procedimiento est&aacute;ndar"></textarea>
            </div>
          </div>

          <!-- Campos específicos Halal -->
          <?php if($obj->linea_productiva == 'general' || $obj->linea_productiva == 'analcoholica'): ?>
          <div class="row mb-3 pt-2 border-top">
            <div class="col-12 mb-2">
              <small class="text-success fw-bold"><i class="fas fa-certificate"></i> Limpieza Halal</small>
            </div>
            <div class="col-md-4 mb-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="uso_exclusivo_halal"
                       id="uso_exclusivo_halal" value="1" <?= $obj->uso_exclusivo_halal ? 'checked' : ''; ?>>
                <label class="form-check-label" for="uso_exclusivo_halal">
                  Uso exclusivo para producci&oacute;n Halal
                </label>
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">&Uacute;ltima limpieza Halal:</label>
              <input type="text" class="form-control" readonly
                     value="<?= $obj->fecha_ultima_limpieza_halal ? date('d/m/Y H:i', strtotime($obj->fecha_ultima_limpieza_halal)) : 'Sin registrar'; ?>">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Certificado:</label>
              <input type="text" class="form-control" name="certificado_limpieza_halal"
                     value="<?= htmlspecialchars($obj->certificado_limpieza_halal); ?>" readonly>
            </div>
          </div>
          <?php endif; ?>

          <!-- Historial de Limpiezas -->
          <div class="pt-2 border-top">
            <h6 class="mb-2"><i class="fas fa-history"></i> Historial</h6>
            <table class="table table-sm table-hover mb-0" id="tabla-historial-limpiezas">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Tipo</th>
                  <th>Usuario</th>
                  <th>Halal</th>
                  <th>Certificado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $historial = $obj->getHistorialLimpiezas(10);
                foreach($historial as $limpieza):
                  $usuario_limpieza = new Usuario($limpieza->id_usuarios);
                ?>
                <tr>
                  <td><?= date('d/m/Y H:i', strtotime($limpieza->fecha)); ?></td>
                  <td>
                    <?php
                    $badge_class = 'secondary';
                    if($limpieza->tipo_limpieza == 'Halal') $badge_class = 'success';
                    elseif($limpieza->tipo_limpieza == 'CIP') $badge_class = 'primary';
                    ?>
                    <span class="badge bg-<?= $badge_class; ?>">
                      <?= $limpieza->tipo_limpieza; ?>
                    </span>
                    <?php if($limpieza->tipo_limpieza == 'CIP'): ?>
                      <?= $limpieza->getEstadoCIPBadge(); ?>
                    <?php endif; ?>
                  </td>
                  <td><?= $usuario_limpieza->nombre; ?></td>
                  <td>
                    <?php if($limpieza->es_limpieza_halal): ?>
                      <i class="fas fa-check-circle text-success"></i>
                    <?php else: ?>
                      <i class="fas fa-minus text-muted"></i>
                    <?php endif; ?>
                  </td>
                  <td><?= $limpieza->certificado_numero ?: '-'; ?></td>
                  <td>
                    <button class="btn btn-xs btn-outline-info ver-limpieza-btn" type="button"
                            data-id="<?= $limpieza->id; ?>"
                            data-fecha="<?= $limpieza->fecha; ?>"
                            data-tipo="<?= $limpieza->tipo_limpieza; ?>"
                            data-procedimiento="<?= htmlspecialchars($limpieza->procedimiento_utilizado); ?>"
                            data-productos="<?= htmlspecialchars($limpieza->productos_utilizados); ?>"
                            data-observaciones="<?= htmlspecialchars($limpieza->observaciones); ?>"
                            data-halal="<?= $limpieza->es_limpieza_halal; ?>"
                            data-certificado="<?= htmlspecialchars($limpieza->certificado_numero); ?>"
                            data-programa-cip="<?= htmlspecialchars($limpieza->programa_cip); ?>"
                            data-temperatura-cip="<?= $limpieza->temperatura_max_cip; ?>"
                            data-tiempo-cip="<?= $limpieza->tiempo_total_cip_min; ?>"
                            data-conductividad="<?= $limpieza->conductividad_promedio; ?>"
                            data-cip-inicio="<?= $limpieza->cip_timestamp_inicio; ?>"
                            data-cip-fin="<?= $limpieza->cip_timestamp_fin; ?>">
                      <i class="fas fa-eye"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($historial) == 0): ?>
                <tr>
                  <td colspan="6" class="text-center text-muted">
                    No hay registros de limpieza
                  </td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- FIN SECCIÓN Limpiezas -->

    <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <button class="btn btn-danger btn-sm eliminar-obj-btn" style="float: right"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
        <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
    </div>

    <div class="col-12 row mt-5" id="media">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-images"></i> Imagenes</h6>
            </div>
            <div class="card-body">
                <div class="row text-center text-lg-left">
                <?php
                foreach($media_arr as $key=>$media) {
                    $media = new Media($media['id']);
                    $activo = "";
                    if(( $obj->id_media_header == 0 && $key == 0 ) || $obj->id_media_header == $media->id ) {
                    $activo = " media-activo";
                    }
                    ?>
                    <div class="col-lg-3 col-md-4 col-6">
                    <img class="img-fluid img-thumbnail obj-media-img<?= $activo; ?>" src="../media/images/<?= $media->url; ?>" width="300" alt="<?= $media->nombre; ?>" data-idmedia="<?= $media->id; ?>" data-url="<?= $media->url; ?>">
                    </div>
                    <?php } ?>
                </div>
                <br />
                <div class="d-sm-flex align-items-center mb-4">
                    <button class="d-sm-inline-block btn btn-sm btn-primary shadow-sm subir-media-btn">+ Subir imagen</button>
                </div>
            </div>
            </div>
        </div>
      </div>

    
      
    
  </div>
</form>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Activo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este Activo?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

<div class="modal fade" id="agregar-media-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-fw fa-images"></i> Subir media</h5>
                <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="font-weight: bold">
            <form id="agregar-media-form" action="./php/procesar.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="modo" value="subir-media">
              <input type="hidden" name="entidad" value="<?= $obj->table_name; ?>">
              <input type="hidden" name="id_<?= $obj->table_name; ?>" value="<?= $obj->id; ?>">
            <div class="form-group">
              <label for="nombre-name" class="control-label">Nombre:</label>
              <input type="text" class="form-control" name="media_nombre" value="<?= $obj->nombre." ".(count($media_arr)+1); ?>">
            </div>
            <div class="form-group">
              <label for="descripcion-text" class="control-label">Descripci&oacute;n:</label>
              <textarea type="text" class="form-control" name="media_descripcion">Imagen de <?= $obj->nombre; ?></textarea>
            </div>
            <div class="form-group">
              <label for="archivo-text" class="control-label">Archivo:</label>
              <input type="file" class="form-control" name="file" accept="image/jpeg image/jpg">
            </div>
          </form></div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-bs-dismiss="modal">Cancelar</button>
                <a class="btn btn-primary btn-sm shadow-sm" href="#" onclick="document.getElementById('agregar-media-form').submit()">Subir</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="eliminar-media-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-fw fa-images"></i> Media</h5>
                <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
            <div class="row">
              <div class="col-6">
                <img src="" id="eliminar-media-img" width="70%">
              </div>
              <div class="col-6">
                <b>URL:</b>
                <br/>
                <textarea class="form-control" id="url-txt" READONLY></textarea>
                <br/>
                <br/>
                <button class="btn btn-success" id="elegir-header-btn">Elegir como Imagen Principal</button>
              </div>
            </div>
              
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger" id="obj-media-eliminar-btn">Eliminar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="nuevo-accesorio-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Crear Accesorio</h5>
        <button type="button" class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
          <i class="ki-duotone ki-cross fs-1"></i>
        </button>
      </div>
      <div class="modal-body">
        <form id="nuevo-accesorio-form">
          <input type="hidden" name="entidad" value="accesorios">
          <input type="hidden" name="id" value="">
          <input type="hidden" name="id_activos" value="<?= $obj->id; ?>">
          <div class="mb-3 fw-semibold">
            <label for="nombre-new" class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" id="nombre-new">
          </div>
          <div class="mb-3 fw-semibold">
            <label for="marca-new" class="form-label">Marca</label>
            <input type="text" class="form-control" name="marca" id="marca-new">
          </div>
          <div class="mb-3 fw-semibold">
            <label for="observaciones-new" class="form-label">Observaciones</label>
            <textarea class="form-control" name="observaciones" id="observaciones-new" rows="2"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" id="nuevo-accesorio-aceptar-btn" data-bs-dismiss="modal">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal fade" id="editar-accesorio-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Accesorio</h5>
        <button type="button" class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
          <i class="ki-duotone ki-cross fs-1"></i>
        </button>
      </div>
      <div class="modal-body">
        <form id="editar-accesorio-form">
          <input type="hidden" name="entidad" value="accesorios">
          <input type="hidden" name="id" value="">
          <input type="hidden" name="id_activos" value="<?= $obj->id; ?>">
          <div class="mb-3 fw-semibold">
            <label for="nombre-edit" class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" id="nombre-edit">
          </div>
          <div class="mb-3 fw-semibold">
            <label for="marca-edit" class="form-label">Marca</label>
            <input type="text" class="form-control" name="marca" id="marca-edit">
          </div>
          <div class="mb-3 fw-semibold">
            <label for="observaciones-edit" class="form-label">Observaciones</label>
            <textarea class="form-control" name="observaciones" id="observaciones-edit" rows="2"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" id="editar-accesorio-aceptar-btn" data-bs-dismiss="modal">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="eliminar-accesorio-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar Accesorio</h5>
        <button type="button" class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
          <i class="ki-duotone ki-cross fs-1"></i>
        </button>
      </div>
      <div class="modal-body">
        <h5 class="fw-semibold text-center">¿Desea eliminar este accesorio?<br/>Este paso no es reversible.</h5>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-danger" id="eliminar-accesorio-aceptar-btn" data-bs-dismiss="modal">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Registrar Limpieza -->
<div class="modal fade" id="registrar-limpieza-modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-broom"></i> Registrar Limpieza
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="form-registrar-limpieza">
          <input type="hidden" name="id_activos" value="<?= $obj->id; ?>">

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Fecha y Hora:</label>
              <input type="datetime-local" class="form-control" name="fecha"
                     value="<?= date('Y-m-d\TH:i'); ?>" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Tipo de Limpieza:</label>
              <select class="form-control" name="tipo_limpieza" id="tipo_limpieza_select" required>
                <?php
                $tipos = RegistroLimpieza::getTiposLimpieza();
                foreach($tipos as $key => $label) {
                  echo "<option value='{$key}'>{$label}</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Procedimiento Utilizado:</label>
              <select class="form-control" name="procedimiento_utilizado">
                <option value="">-- Seleccionar procedimiento --</option>
                <?php
                $procedimientos = ProcedimientoLimpieza::getAll("WHERE estado='activo' ORDER BY nombre");
                foreach($procedimientos as $proc) {
                  echo "<option value='{$proc->codigo}'>{$proc->codigo} - {$proc->nombre}</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Productos Utilizados:</label>
              <textarea class="form-control" name="productos_utilizados" rows="2"
                        placeholder="Lista de productos de limpieza utilizados"></textarea>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Observaciones:</label>
              <textarea class="form-control" name="observaciones" rows="2"></textarea>
            </div>
          </div>

          <!-- Campos Halal (ocultos por defecto) -->
          <div id="campos-halal" style="display: none;">
            <hr>
            <h6 class="text-success"><i class="fas fa-certificate"></i> Certificación Halal</h6>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Número de Certificado:</label>
                <input type="text" class="form-control" name="certificado_numero"
                       placeholder="HALAL-LIM-2025-001">
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Entidad Certificadora:</label>
                <input type="text" class="form-control" name="certificado_emisor">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Supervisor:</label>
                <select class="form-control" name="id_usuarios_supervisor">
                  <option value="">-- Seleccionar supervisor --</option>
                  <?php
                  $supervisores = Usuario::getAll("WHERE nivel IN ('Administrador', 'Jefe de Planta') ORDER BY nombre");
                  foreach($supervisores as $sup) {
                    echo "<option value='{$sup->id}'>{$sup->nombre}</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>

          <!-- Campos CIP (ocultos por defecto) -->
          <div id="campos-cip" style="display: none;">
            <hr>
            <h6 class="text-primary"><i class="fas fa-shower"></i> Par&aacute;metros CIP</h6>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Programa CIP:</label>
                <select class="form-control" name="programa_cip">
                  <option value="">-- Seleccionar programa --</option>
                  <?php
                  $programas_cip = RegistroLimpieza::getProgramasCIP();
                  foreach($programas_cip as $key => $label) {
                    echo "<option value='{$key}'>{$label}</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Batch Posterior:</label>
                <select class="form-control" name="id_batches_posterior">
                  <option value="">-- Sin asignar --</option>
                  <?php
                  $batches_activos = Batch::getAll("WHERE estado NOT IN ('eliminado', 'Finalizado') ORDER BY fecha_coccion DESC LIMIT 20");
                  foreach($batches_activos as $batch) {
                    $receta = new Receta($batch->id_recetas);
                    echo "<option value='{$batch->id}'>#{$batch->numero_batch} - {$receta->nombre}</option>";
                  }
                  ?>
                </select>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label">Temperatura M&aacute;x. (&deg;C):</label>
                <input type="number" class="form-control" name="temperatura_max_cip"
                       step="0.1" min="0" max="100" placeholder="Ej: 85.0">
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Tiempo Total (min):</label>
                <input type="number" class="form-control" name="tiempo_total_cip_min"
                       min="0" placeholder="Ej: 45">
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Conductividad (&micro;S/cm):</label>
                <input type="number" class="form-control" name="conductividad_promedio"
                       step="0.1" min="0" placeholder="< 50 = limpio">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Inicio CIP:</label>
                <input type="datetime-local" class="form-control" name="cip_timestamp_inicio">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Fin CIP:</label>
                <input type="datetime-local" class="form-control" name="cip_timestamp_fin">
              </div>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar-limpieza-btn">
          <i class="fas fa-save"></i> Registrar Limpieza
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Ver Limpieza -->
<div class="modal fade" id="ver-limpieza-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-broom"></i> Detalle de Limpieza</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-sm">
          <tr><td><strong>Fecha:</strong></td><td id="ver-fecha"></td></tr>
          <tr><td><strong>Tipo:</strong></td><td id="ver-tipo"></td></tr>
          <tr><td><strong>Procedimiento:</strong></td><td id="ver-procedimiento"></td></tr>
          <tr><td><strong>Productos:</strong></td><td id="ver-productos"></td></tr>
          <tr><td><strong>Observaciones:</strong></td><td id="ver-observaciones"></td></tr>
          <tr id="ver-halal-row"><td><strong>Halal:</strong></td><td id="ver-halal"></td></tr>
          <tr id="ver-certificado-row"><td><strong>Certificado:</strong></td><td id="ver-certificado"></td></tr>
        </table>

        <!-- Sección CIP -->
        <div id="ver-cip-section" style="display: none;">
          <hr>
          <h6 class="text-primary"><i class="fas fa-shower"></i> Par&aacute;metros CIP</h6>
          <table class="table table-sm">
            <tr><td><strong>Programa:</strong></td><td id="ver-programa-cip"></td></tr>
            <tr><td><strong>Temperatura M&aacute;x.:</strong></td><td id="ver-temperatura-cip"></td></tr>
            <tr><td><strong>Tiempo Total:</strong></td><td id="ver-tiempo-cip"></td></tr>
            <tr><td><strong>Conductividad:</strong></td><td id="ver-conductividad"></td></tr>
            <tr><td><strong>Inicio CIP:</strong></td><td id="ver-cip-inicio"></td></tr>
            <tr><td><strong>Fin CIP:</strong></td><td id="ver-cip-fin"></td></tr>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>

var id_media_eliminar = 0;
var editar = true;


$('#editar-btn').click(function(e){

    e.preventDefault();

    if(editar == false) {

        $.each(obj,function(key,value){
        console.log(key);
        if(key!="table_name"&&key!="table_fields"){
            $('#activos-form input[name="'+key+'"]').attr('disabled',false);
            $('#activos-form textarea[name="'+key+'"]').attr('disabled',false);
            $('#activos-form select[name="'+key+'"]').attr('disabled',false);
        }
        });

        CKEDITOR.instances.inspeccion_procedimiento.setReadOnly(false);
        CKEDITOR.instances.mantencion_procedimiento.setReadOnly(false);

        $('#guardar-btn').attr('disabled',false);
        $('.eliminar-obj-btn').attr('disabled',false);

        $(e.currentTarget).html('<i class="fas fa-fw fa-unlock"></i> Editando');
        $(e.currentTarget).removeClass('btn-warning');
        $(e.currentTarget).addClass('btn-danger');

        editar = true;

    } else {

        $.each(obj,function(key,value){
        console.log(key);
        if(key!="table_name"&&key!="table_fields"){
            $('input[name="'+key+'"]').attr('disabled',true);
            $('textarea[name="'+key+'"]').attr('disabled',true);
            $('select[name="'+key+'"]').attr('disabled',true);
        }
        });

        CKEDITOR.instances.inspeccion_procedimiento.setReadOnly();
        CKEDITOR.instances.mantencion_procedimiento.setReadOnly();

        $('#guardar-btn').attr('disabled',true);
        $('.eliminar-btn').attr('disabled',true);

        $(e.currentTarget).html('<i class="fas fa-fw fa-lock"></i> Editar');
        $(e.currentTarget).addClass('btn-warning');
        $(e.currentTarget).removeClass('btn-danger');

        editar = false;


    }

})

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
$(document).ready(function(){

    //$('#guardar-btn').attr('disabled','1');
    //$('.eliminar-obj-btn').attr('disabled','1');


    $.each(obj,function(key,value){
        if(key!="table_name"&&key!="table_fields"){
        $('#activos-form input[name="'+key+'"]').val(value);
        $('#activos-form textarea[name="'+key+'"]').val(value);
        $('#activos-form select[name="'+key+'"]').val(value);
        }
    });

    if(obj.ubicacion == "En terreno") {
      $('.en-terreno').show();
      $('.en-planta').hide();
    } else
    if(obj.ubicacion == "En planta") {
      $('.en-terreno').hide();
      $('.en-planta').show();
    }


    CKEDITOR.replace( 'mantencion_procedimiento', {
        uiColor: '#f8f9fc',
        height: 260
    });
    CKEDITOR.replace( 'inspeccion_procedimiento', {
        uiColor: '#f8f9fc',
        height: 260
    });
    
});

$(document).on('change','select[name="ubicacion"]',function(e) {
  console.log($(e.currentTarget).val());
  if($(e.currentTarget).val() == "En terreno") {
    $('.en-terreno').show();
    $('.en-planta').hide();
    $('select[name="id_locaciones"]').val('0');
  } else
  if($(e.currentTarget).val() == "En planta") {
    $('.en-terreno').hide();
    $('.en-planta').show();
    $('select[name="id_clientes_ubicacion"]').val('0');
  }
  
});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("activos");

  data['mantencion_procedimiento'] = CKEDITOR.instances.mantencion_procedimiento.getData();
  data['inspeccion_procedimiento'] = CKEDITOR.instances.inspeccion_procedimiento.getData();

  // Agregar checkbox Halal manualmente (los checkboxes no se envían cuando están desmarcados)
  data['uso_exclusivo_halal'] = $('#uso_exclusivo_halal').is(':checked') ? '1' : '0';

  $.post(url,data,function(response){
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-activos&id=" + response.obj.id + "&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','.obj-media-img',function(e) {
  e.preventDefault();
  id_media_eliminar = $(e.currentTarget).data('idmedia');
  var imagen = $('#eliminar-media-img')[0];
  imagen.src = "../media/images/" + $(e.currentTarget).data('url');
  $('#url-txt').val("https://app.barril.cl/media/images/" + $(e.currentTarget).data('url'));
  $('#eliminar-media-modal').modal('toggle');
});

$(document).on('click','#obj-media-eliminar-btn',function(e){

    e.preventDefault();

var data = {
  'id': obj.id,
  'entidad': obj.table_name,
  'id_media': id_media_eliminar
}

var url = './ajax/ajax_eliminarMedia.php';
$.post(url,data,function(raw){
  console.log(raw);
  var response = JSON.parse(raw);
  if(response.status!="OK") {
    alert("Algo fallo");
    return false;
  } else {
    window.location.href = "./?s=detalle-activos&msg=2&id=" + obj.id;
  }
}).fail(function(){
  alert("No funciono");
});
});

$(document).on('click','#elegir-header-btn',function(e){

e.preventDefault();

var data = {
  'id': obj.id,
  'entidad': obj.table_name,
  'id_media': id_media_eliminar
}

var url = './ajax/ajax_cambiarMediaHeader.php';
$.post(url,data,function(raw){
  console.log(raw);
  var response = JSON.parse(raw);
  if(response.status!="OK") {
    alert("Algo fallo");
    return false;
  } else {
    window.location.href = "./?s=detalle-activos&msg=6&id=" + obj.id;
  }
}).fail(function(){
  alert("No funciono");
});
});

$("#url-txt").focus(function() { $(this).select(); } );

$('.subir-media-btn').click(function(e) {
    e.preventDefault();
    $('#agregar-media-modal').modal('toggle');
});


$(document).on('click','.eliminar-obj-btn',function(e){

    e.preventDefault();
    $('#eliminar-obj-modal').modal('toggle');

});

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

$('#nuevo-accesorio-abrir-modal-btn').on('click', function(e){
    e.preventDefault();
    $('#nuevo-accesorio-form')[0].reset();
    $('#nuevo-accesorio-modal').modal('show');
  });

  $('#nuevo-accesorio-aceptar-btn').on('click', function(e){
    e.preventDefault();
    var url = "./ajax/ajax_guardarEntidad.php";
    var data = getDataForm("nuevo-accesorio");
    $.post(url, data, function(raw){
      var response = JSON.parse(raw);
      if(response.mensaje!="OK") {
        alert("Error al crear Accesorio");
        return;
      }
      window.location.reload();
    }).fail(function(){
      alert("No funcionó la creación");
    });
  });

  $(document).on('click','.editar-accesorio-btn', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    var nombre = $(this).data('nombre');
    var marca = $(this).data('marca');
    var obs = $(this).data('observaciones');

    $('#editar-accesorio-form input[name="id"]').val(id);
    $('#editar-accesorio-form input[name="nombre"]').val(nombre);
    $('#editar-accesorio-form input[name="marca"]').val(marca);
    $('#editar-accesorio-form textarea[name="observaciones"]').val(obs);

    $('#editar-accesorio-modal').modal('show');
  });

  $('#editar-accesorio-aceptar-btn').on('click', function(e){
    e.preventDefault();
    var url = "./ajax/ajax_guardarEntidad.php";
    var data = getDataForm("editar-accesorio");
    $.post(url, data, function(raw){
      var response = JSON.parse(raw);
      if(response.mensaje!="OK") {
        alert("Error al editar Accesorio");
        return;
      }
      window.location.reload();
    }).fail(function(){
      alert("No funcionó la edición");
    });
  });

  var accesorioAEliminar = null;
  $(document).on('click','.eliminar-accesorio-btn',function(e){
    e.preventDefault();
    accesorioAEliminar = $(this).data('id');
    $('#eliminar-accesorio-modal').modal('show');
  });

  $('#eliminar-accesorio-aceptar-btn').on('click', function(e){
    e.preventDefault();
    if(!accesorioAEliminar) {
      return;
    }
    var url = "./ajax/ajax_eliminarEntidad.php";
    var data = { id: accesorioAEliminar, modo: "accesorios" };
    $.post(url, data, function(resp){
      if(resp.status!="OK") {
        alert("Error al eliminar Accesorio");
        return;
      }
      window.location.reload();
    },"json").fail(function(){
      alert("No funcionó la eliminación del Accesorio");
    });
  });

  // ============================================
  // LIMPIEZAS
  // ============================================

  // Mostrar/ocultar campos según tipo seleccionado
  $('#tipo_limpieza_select').change(function() {
    var tipo = $(this).val();

    // Campos Halal
    if(tipo == 'Halal') {
      $('#campos-halal').slideDown();
      $('input[name="certificado_numero"]').prop('required', true);
    } else {
      $('#campos-halal').slideUp();
      $('input[name="certificado_numero"]').prop('required', false);
    }

    // Campos CIP
    if(tipo == 'CIP') {
      $('#campos-cip').slideDown();
    } else {
      $('#campos-cip').slideUp();
    }
  });

  // Abrir modal de registrar limpieza
  $('#registrar-limpieza-btn').click(function() {
    $('#form-registrar-limpieza')[0].reset();
    $('#form-registrar-limpieza input[name="fecha"]').val(new Date().toISOString().slice(0,16));
    $('#campos-halal').hide();
    $('#campos-cip').hide();
    $('#registrar-limpieza-modal').modal('show');
  });

  // Guardar limpieza
  $('#guardar-limpieza-btn').click(function() {
    var $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    var tipoLimpieza = $('#tipo_limpieza_select').val();
    var data = {
      id_activos: $('#form-registrar-limpieza input[name="id_activos"]').val(),
      fecha: $('#form-registrar-limpieza input[name="fecha"]').val(),
      tipo_limpieza: tipoLimpieza,
      procedimiento_utilizado: $('#form-registrar-limpieza select[name="procedimiento_utilizado"]').val(),
      productos_utilizados: $('#form-registrar-limpieza textarea[name="productos_utilizados"]').val(),
      observaciones: $('#form-registrar-limpieza textarea[name="observaciones"]').val(),
      es_limpieza_halal: tipoLimpieza == 'Halal' ? 1 : 0,
      certificado_numero: $('#form-registrar-limpieza input[name="certificado_numero"]').val(),
      certificado_emisor: $('#form-registrar-limpieza input[name="certificado_emisor"]').val(),
      id_usuarios_supervisor: $('#form-registrar-limpieza select[name="id_usuarios_supervisor"]').val(),
      // Campos CIP
      programa_cip: tipoLimpieza == 'CIP' ? $('#form-registrar-limpieza select[name="programa_cip"]').val() : '',
      temperatura_max_cip: tipoLimpieza == 'CIP' ? $('#form-registrar-limpieza input[name="temperatura_max_cip"]').val() : '',
      tiempo_total_cip_min: tipoLimpieza == 'CIP' ? $('#form-registrar-limpieza input[name="tiempo_total_cip_min"]').val() : '',
      conductividad_promedio: tipoLimpieza == 'CIP' ? $('#form-registrar-limpieza input[name="conductividad_promedio"]').val() : '',
      cip_timestamp_inicio: tipoLimpieza == 'CIP' ? $('#form-registrar-limpieza input[name="cip_timestamp_inicio"]').val() : '',
      cip_timestamp_fin: tipoLimpieza == 'CIP' ? $('#form-registrar-limpieza input[name="cip_timestamp_fin"]').val() : '',
      id_batches_posterior: tipoLimpieza == 'CIP' ? $('#form-registrar-limpieza select[name="id_batches_posterior"]').val() : ''
    };

    $.post('./ajax/ajax_registrarLimpieza.php', data, function(response) {
      if(response.status == 'OK') {
        window.location.reload();
      } else {
        alert('Error: ' + response.mensaje);
        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Registrar Limpieza');
      }
    }, 'json').fail(function() {
      alert('Error de conexión');
      $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Registrar Limpieza');
    });
  });

  // Ver detalle de limpieza
  $(document).on('click', '.ver-limpieza-btn', function() {
    var $btn = $(this);
    var fecha = new Date($btn.data('fecha'));
    var fechaStr = fecha.toLocaleDateString('es-CL') + ' ' + fecha.toLocaleTimeString('es-CL', {hour: '2-digit', minute:'2-digit'});
    var tipo = $btn.data('tipo');

    $('#ver-fecha').text(fechaStr);

    // Badge de tipo con colores apropiados
    var badgeClass = 'secondary';
    if(tipo == 'Halal') badgeClass = 'success';
    else if(tipo == 'CIP') badgeClass = 'primary';
    $('#ver-tipo').html('<span class="badge bg-' + badgeClass + '">' + tipo + '</span>');

    $('#ver-procedimiento').text($btn.data('procedimiento') || '-');
    $('#ver-productos').text($btn.data('productos') || '-');
    $('#ver-observaciones').text($btn.data('observaciones') || '-');

    // Campos Halal
    if($btn.data('halal') == 1) {
      $('#ver-halal-row').show();
      $('#ver-certificado-row').show();
      $('#ver-halal').html('<i class="fas fa-check-circle text-success"></i> Sí');
      $('#ver-certificado').text($btn.data('certificado') || '-');
    } else {
      $('#ver-halal-row').hide();
      $('#ver-certificado-row').hide();
    }

    // Campos CIP
    if(tipo == 'CIP') {
      $('#ver-cip-section').show();

      // Programa CIP
      var programaCip = $btn.data('programa-cip');
      var programasLabels = {
        'CIP-BASICO': 'CIP Básico (Enjuague + Soda)',
        'CIP-COMPLETO': 'CIP Completo (Enjuague + Soda + Ácido)',
        'CIP-SANITIZADO': 'CIP con Sanitizado',
        'CIP-HALAL': 'CIP Halal Certificado',
        'CIP-PERSONALIZADO': 'CIP Personalizado'
      };
      $('#ver-programa-cip').text(programasLabels[programaCip] || programaCip || '-');

      // Temperatura
      var temp = $btn.data('temperatura-cip');
      $('#ver-temperatura-cip').text(temp ? temp + ' °C' : '-');

      // Tiempo
      var tiempo = $btn.data('tiempo-cip');
      $('#ver-tiempo-cip').text(tiempo ? tiempo + ' minutos' : '-');

      // Conductividad
      var cond = $btn.data('conductividad');
      if(cond) {
        var condClass = cond <= 50 ? 'text-success' : 'text-warning';
        $('#ver-conductividad').html('<span class="' + condClass + '">' + cond + ' µS/cm</span>' +
          (cond <= 50 ? ' <i class="fas fa-check-circle text-success"></i>' : ' <i class="fas fa-exclamation-triangle text-warning"></i>'));
      } else {
        $('#ver-conductividad').text('-');
      }

      // Timestamps CIP
      var cipInicio = $btn.data('cip-inicio');
      var cipFin = $btn.data('cip-fin');
      if(cipInicio) {
        var inicioDate = new Date(cipInicio);
        $('#ver-cip-inicio').text(inicioDate.toLocaleDateString('es-CL') + ' ' + inicioDate.toLocaleTimeString('es-CL', {hour: '2-digit', minute:'2-digit'}));
      } else {
        $('#ver-cip-inicio').text('-');
      }
      if(cipFin) {
        var finDate = new Date(cipFin);
        $('#ver-cip-fin').text(finDate.toLocaleDateString('es-CL') + ' ' + finDate.toLocaleTimeString('es-CL', {hour: '2-digit', minute:'2-digit'}));
      } else {
        $('#ver-cip-fin').text('-');
      }
    } else {
      $('#ver-cip-section').hide();
    }

    $('#ver-limpieza-modal').modal('show');
  });

</script>
