<?php


$id = "";

if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
}

$msg = 0;

if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}

$obj = new Insumo($id);
$tipos_de_insumos = TipoDeInsumo::getAll();
$proveedores = Proveedor::getAll("ORDER BY nombre ASC");

$usuario = $GLOBALS['usuario'];
?>
<style>
.tr-gasto {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Detalle Insumo
      </b>
    </h1>
  </div>
  <div>
    <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr />
<?php
if($msg == 1) {
?>
<div class="alert alert-info" role="alert" >Insumo guardado con &eacute;xito.</div>
<?php
}
?>
<form id="insumos-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="insumos">
  <input type="hidden" name="id_usuarios" value="<?= $GLOBALS['usuario']->id; ?>">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Nombre
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="nombre">
      </div>
      <div class="col-6 mb-1">
        Tipo de Insumo
      </div>
      <div class="col-6 mb-1">
        <select name="id_tipos_de_insumos" class="form-control">
        <?php
                foreach($tipos_de_insumos as $tipo) {
                    print "<option value='".$tipo->id."'>".$tipo->nombre."</option>";
                }
            ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Unidad de medida:
      </div>
      <div class="col-6 mb-1">
      <select name="unidad_de_medida" class="form-control">
        <?php
                foreach($GLOBALS['insumos_unidades_de_medida'] as $udm) {
                    print "<option>".$udm."</option>";
                }
            ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Cantidad en Bodega:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control acero-float" name="bodega">
      </div>
      <div class="col-6 mb-1">
        Cantidad en Despacho:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control acero-float" name="despacho">
      </div>

      <!-- Datos del Proveedor -->
      <div class="col-12 mt-3 mb-2">
        <h6 class="text-muted"><i class="bi bi-truck"></i> Datos del Proveedor</h6>
        <hr class="mt-1 mb-2">
      </div>
      <div class="col-6 mb-1">
        Proveedor:
      </div>
      <div class="col-6 mb-1">
        <select name="id_proveedores" class="form-control">
          <option value="">-- Sin proveedor --</option>
          <?php foreach($proveedores as $prov): ?>
            <option value="<?= $prov->id ?>" <?= ($obj->id_proveedores == $prov->id) ? 'selected' : '' ?>><?= htmlspecialchars($prov->nombre) ?></option>
          <?php endforeach; ?>
        </select>
        <small class="text-muted">
          <a href="./?s=nuevo-proveedores" target="_blank"><i class="bi bi-plus-circle"></i> Crear nuevo proveedor</a>
        </small>
      </div>
      <div class="col-6 mb-1">
        C&oacute;digo Proveedor:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="codigo_proveedor" placeholder="C&oacute;digo interno del insumo">
      </div>
      <div class="col-6 mb-1">
        Pa&iacute;s de Origen:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="pais_origen" placeholder="Ej: Chile, Argentina, Alemania">
      </div>

      <!-- Materia Prima -->
      <div class="col-12 mt-3 mb-2">
        <h6 class="text-muted"><i class="bi bi-box-seam"></i> Identificaci&oacute;n de Materia Prima</h6>
        <hr class="mt-1 mb-2">
      </div>
      <div class="col-6 mb-1">
        Nombre Comercial:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="nombre_comercial" placeholder="Nombre comercial del producto">
      </div>
      <div class="col-6 mb-1">
        Marca:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="marca" placeholder="Marca del fabricante">
      </div>
      <div class="col-6 mb-1">
        Materia Prima B&aacute;sica:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="materia_prima_basica" placeholder="Ej: Cebada, L&uacute;pulo, Agua">
      </div>
      <div class="col-6 mb-1">
        A&ntilde;o de Cosecha:
      </div>
      <div class="col-6 mb-1">
        <input type="number" class="form-control" name="cosecha_anio" min="2000" max="2100" placeholder="Ej: 2024">
      </div>
      <div class="col-6 mb-1">
        Presentaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="presentacion" placeholder="Ej: Saco 25kg, Bolsa 1kg">
      </div>
      <div class="col-6 mb-1">
        Vida &Uacute;til (meses):
      </div>
      <div class="col-6 mb-1">
        <input type="number" class="form-control" name="vida_util_meses" min="0" placeholder="Meses desde producci&oacute;n">
      </div>

      <!-- Levadura -->
      <div class="col-12 mt-3 mb-2">
        <h6 class="text-muted"><i class="bi bi-virus"></i> Datos de Levadura</h6>
        <hr class="mt-1 mb-2">
      </div>
      <div class="col-6 mb-1">
        <div class="form-check">
          <input type="checkbox" class="form-check-input" name="es_levadura" id="es_levadura" value="1">
          <label class="form-check-label" for="es_levadura">
            <strong>Este insumo es una Levadura</strong>
          </label>
        </div>
      </div>
      <div class="col-6 mb-1"></div>
      <div id="levadura-fields" style="display:none;" class="col-12">
        <div class="row">
          <div class="col-6 mb-1">
            Cepa:
          </div>
          <div class="col-6 mb-1">
            <input type="text" class="form-control" name="cepa" placeholder="Ej: Saccharomyces cerevisiae">
          </div>
          <div class="col-6 mb-1">
            Tipo de Levadura:
          </div>
          <div class="col-6 mb-1">
            <select name="tipo_levadura" class="form-control">
              <option value="">-- Seleccionar --</option>
              <option value="ale_seca">Ale Seca</option>
              <option value="ale_liquida">Ale L&iacute;quida</option>
              <option value="lager_seca">Lager Seca</option>
              <option value="lager_liquida">Lager L&iacute;quida</option>
              <option value="wild">Wild / Brett</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          <div class="col-6 mb-1">
            Atenuaci&oacute;n (%):
          </div>
          <div class="col-6 mb-1">
            <div class="row">
              <div class="col-6">
                <input type="number" class="form-control form-control-sm" name="atenuacion_min" placeholder="M&iacute;n" step="0.1">
              </div>
              <div class="col-6">
                <input type="number" class="form-control form-control-sm" name="atenuacion_max" placeholder="M&aacute;x" step="0.1">
              </div>
            </div>
          </div>
          <div class="col-6 mb-1">
            Floculaci&oacute;n:
          </div>
          <div class="col-6 mb-1">
            <select name="floculacion" class="form-control">
              <option value="">-- Seleccionar --</option>
              <option value="baja">Baja</option>
              <option value="media">Media</option>
              <option value="alta">Alta</option>
              <option value="muy_alta">Muy Alta</option>
            </select>
          </div>
          <div class="col-6 mb-1">
            Temp. Fermentaci&oacute;n (&deg;C):
          </div>
          <div class="col-6 mb-1">
            <div class="row">
              <div class="col-6">
                <input type="number" class="form-control form-control-sm" name="temp_fermentacion_min" placeholder="M&iacute;n" step="0.5">
              </div>
              <div class="col-6">
                <input type="number" class="form-control form-control-sm" name="temp_fermentacion_max" placeholder="M&aacute;x" step="0.5">
              </div>
            </div>
          </div>
          <div class="col-6 mb-1">
            Tolerancia Alcohol (%):
          </div>
          <div class="col-6 mb-1">
            <input type="number" class="form-control" name="tolerancia_alcohol" placeholder="% m&aacute;ximo" step="0.1">
          </div>
        </div>
      </div>

      <!-- Certificacion Halal -->
      <div class="col-12 mt-3 mb-2">
        <h6 class="text-muted"><i class="bi bi-patch-check"></i> Certificaci&oacute;n Halal</h6>
        <hr class="mt-1 mb-2">
      </div>
      <div class="col-6 mb-1">
        <div class="form-check">
          <input type="checkbox" class="form-check-input" name="es_halal_certificado" id="es_halal_certificado" value="1">
          <label class="form-check-label" for="es_halal_certificado">
            <strong>Insumo Certificado Halal</strong>
          </label>
        </div>
      </div>
      <div class="col-6 mb-1">
        <?php if($obj->id && $obj->es_halal_certificado) { echo $obj->getEstadoCertificadoHalalBadge(); } ?>
      </div>
      <div id="halal-fields" style="display:none;">
        <div class="row">
          <div class="col-6 mb-1">
            N&uacute;mero de Certificado:
          </div>
          <div class="col-6 mb-1">
            <input type="text" class="form-control" name="certificado_halal_numero" placeholder="Ej: HALAL-2025-001">
          </div>
          <div class="col-6 mb-1">
            Fecha de Vencimiento:
          </div>
          <div class="col-6 mb-1">
            <input type="date" class="form-control" name="certificado_halal_vencimiento">
          </div>
          <div class="col-6 mb-1">
            Entidad Emisora:
          </div>
          <div class="col-6 mb-1">
            <input type="text" class="form-control" name="certificado_halal_emisor" placeholder="Ej: Islamic Food Council">
          </div>
          <div class="col-6 mb-1">
            Ficha T&eacute;cnica (PDF):
          </div>
          <div class="col-6 mb-1">
            <?php
            $ficha = $obj->getFichaTecnica();
            if($ficha) {
              if(is_array($ficha)) {
                echo '<div class="mb-2"><a href="./media/' . htmlspecialchars($ficha['url']) . '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-pdf"></i> Ver ficha actual</a></div>';
              } else {
                echo '<div class="mb-2"><a href="' . htmlspecialchars($ficha) . '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-link-45deg"></i> Ver URL actual</a></div>';
              }
            }
            ?>
            <input type="file" class="form-control form-control-sm" name="ficha_tecnica_file" accept=".pdf,.doc,.docx">
            <small class="text-muted">O URL externa:</small>
            <input type="url" class="form-control form-control-sm mt-1" name="url_ficha_tecnica" placeholder="https://...">
          </div>
          <div class="col-6 mb-1">
            Certificado Halal (PDF):
          </div>
          <div class="col-6 mb-1">
            <?php
            $cert = $obj->getCertificadoHalal();
            if($cert) {
              if(is_array($cert)) {
                echo '<div class="mb-2"><a href="./media/' . htmlspecialchars($cert['url']) . '" target="_blank" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-pdf"></i> Ver certificado actual</a></div>';
              } else {
                echo '<div class="mb-2"><a href="' . htmlspecialchars($cert) . '" target="_blank" class="btn btn-sm btn-outline-success"><i class="bi bi-link-45deg"></i> Ver URL actual</a></div>';
              }
            }
            ?>
            <input type="file" class="form-control form-control-sm" name="certificado_halal_file" accept=".pdf,.doc,.docx,.jpg,.png">
            <small class="text-muted">O URL externa:</small>
            <input type="url" class="form-control form-control-sm mt-1" name="url_certificado_halal" placeholder="https://...">
          </div>
        </div>
      </div>

      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
      <?php
        if($obj->id != "") {
          ?>
          <button class="btn btn-sm btn-danger eliminar-obj-btn">Eliminar</button>
          <?php
        } else {
          print "&nbsp;";
        }
        ?>
        <button class="btn btn-sm btn-primary" id="guardar-btn">Guardar</button>
      </div>
    </div>
  </div>
</form>


<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Insumo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este Insumo?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>


<script>

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
$(document).ready(function(){
  if(obj.id!="") {
    $.each(obj,function(key,value){
      console.log(key);
      if(key!="table_name"&&key!="table_fields"){
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
      }
    });
    // Cargar checkbox Halal
    if(obj.es_halal_certificado == "1") {
      $('#es_halal_certificado').prop('checked', true);
      $('#halal-fields').show();
    }
    // Cargar checkbox Levadura
    if(obj.es_levadura == "1") {
      $('#es_levadura').prop('checked', true);
      $('#levadura-fields').show();
    }
  }
});

// Toggle campos Halal
$(document).on('change', '#es_halal_certificado', function() {
  if($(this).is(':checked')) {
    $('#halal-fields').show();
  } else {
    $('#halal-fields').hide();
  }
});

// Toggle campos Levadura
$(document).on('change', '#es_levadura', function() {
  if($(this).is(':checked')) {
    $('#levadura-fields').show();
  } else {
    $('#levadura-fields').hide();
  }
});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }

  var formData = new FormData();

  // Campos base
  formData.append('id', $('input[name="id"]').val());
  formData.append('entidad', 'insumos');
  formData.append('id_usuarios', $('input[name="id_usuarios"]').val());
  formData.append('nombre', $('input[name="nombre"]').val());
  formData.append('id_tipos_de_insumos', $('select[name="id_tipos_de_insumos"]').val());
  formData.append('unidad_de_medida', $('select[name="unidad_de_medida"]').val());
  formData.append('bodega', $('input[name="bodega"]').val());
  formData.append('despacho', $('input[name="despacho"]').val());

  // Campos proveedor
  formData.append('id_proveedores', $('select[name="id_proveedores"]').val());
  formData.append('codigo_proveedor', $('input[name="codigo_proveedor"]').val());
  formData.append('pais_origen', $('input[name="pais_origen"]').val());

  // Campos Materia Prima
  formData.append('nombre_comercial', $('input[name="nombre_comercial"]').val());
  formData.append('marca', $('input[name="marca"]').val());
  formData.append('materia_prima_basica', $('input[name="materia_prima_basica"]').val());
  formData.append('cosecha_anio', $('input[name="cosecha_anio"]').val());
  formData.append('presentacion', $('input[name="presentacion"]').val());
  formData.append('vida_util_meses', $('input[name="vida_util_meses"]').val());

  // Campos Levadura
  formData.append('es_levadura', $('#es_levadura').is(':checked') ? '1' : '0');
  formData.append('cepa', $('input[name="cepa"]').val());
  formData.append('tipo_levadura', $('select[name="tipo_levadura"]').val());
  formData.append('atenuacion_min', $('input[name="atenuacion_min"]').val());
  formData.append('atenuacion_max', $('input[name="atenuacion_max"]').val());
  formData.append('floculacion', $('select[name="floculacion"]').val());
  formData.append('temp_fermentacion_min', $('input[name="temp_fermentacion_min"]').val());
  formData.append('temp_fermentacion_max', $('input[name="temp_fermentacion_max"]').val());
  formData.append('tolerancia_alcohol', $('input[name="tolerancia_alcohol"]').val());

  // Campos Halal
  formData.append('es_halal_certificado', $('#es_halal_certificado').is(':checked') ? '1' : '0');
  formData.append('certificado_halal_numero', $('input[name="certificado_halal_numero"]').val());
  formData.append('certificado_halal_vencimiento', $('input[name="certificado_halal_vencimiento"]').val());
  formData.append('certificado_halal_emisor', $('input[name="certificado_halal_emisor"]').val());
  formData.append('url_ficha_tecnica', $('input[name="url_ficha_tecnica"]').val());
  formData.append('url_certificado_halal', $('input[name="url_certificado_halal"]').val());

  // Archivos
  var fichaTecnica = $('input[name="ficha_tecnica_file"]')[0].files[0];
  if(fichaTecnica) {
    formData.append('ficha_tecnica_file', fichaTecnica);
  }
  var certHalal = $('input[name="certificado_halal_file"]')[0].files[0];
  if(certHalal) {
    formData.append('certificado_halal_file', certHalal);
  }

  $.ajax({
    url: './ajax/ajax_guardarInsumo.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function(response) {
      console.log(response);
      if(response.mensaje != "OK" && response.status != "OK") {
        alert("Error: " + (response.mensaje || response.error || "Algo falló"));
        return false;
      }
      window.location.href = "./?s=detalle-insumos&id=" + response.id + "&msg=1";
    },
    error: function() {
      alert("No funcionó la conexión");
    }
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

</script>
