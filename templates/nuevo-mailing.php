<?php
    $usuario = $GLOBALS['usuario'];
?>
<style>
.cke_notification {
  display: none;
}
</style>
<script src="./js/vendor/ckeditor/ckeditor.js"></script>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Nuevo Mailing
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
<form id="mailing-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="mailing">
    <div class="card">
        <div class="card-body p-9">
            <div class="row mb-8">
                <div class="col-xl-3">
                    <div class="fs-6 fw-semibold mt-2 mb-3">Categoría</div>
                </div>
                <div class="col-xl-9 fv-row">
                    <select class="form-control" name="categoria">
                        <option>Leads</option>
                        <option>Ventas</option>
                        <option>Cliente</option>
                    </select>
                </div>
            </div>
            <div class="row mb-8">
                <div class="col-xl-3">
                    <div class="fs-6 fw-semibold mt-2 mb-3">Nombre</div>
                </div>
                <div class="col-xl-9 fv-row">
                    <input type="text" class="form-control form-control-solid" name="nombre">
                </div>
            </div>
            <div class="row mb-8">
                <div class="col-xl-3">
                    <div class="fs-6 fw-semibold mt-2 mb-3">Asunto</div>
                </div>
                <div class="col-xl-9 fv-row">
                    <input type="text" class="form-control form-control-solid" name="asunto">
                </div>
            </div>
            <div class="row mb-8">
                <div class="col-12">
                    <div class="fs-6 fw-semibold mt-2 mb-3">Mensaje</div>
                </div>
                <div class="col-12 fv-row">
                    <textarea class="form-control" id="mensajetextarea" name="mensaje"></textarea>
                </div>
            </div>
        </div>
    </div>
</form>
<div class="card mt-3">
    <div class="card-footer d-flex justify-content-end py-6 px-9">
        <button type="submit" class="btn btn-primary shadow" id="guardar-btn">
            <i class="ki-duotone ki-tablet-ok fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
                <span class="path5"></span>
            </i>
            Guardar
        </button>
    </div>
</div>

<script>


    $(document).ready(function() {

        CKEDITOR.replace( 'mensajetextarea', {
            height: 560
        });
    });

    $(document).on('click','#guardar-btn',function(e){

        e.preventDefault();

        var url = "./ajax/ajax_guardarEntidad.php";
        var data = getDataForm("mailing");
        data['mensaje'] = CKEDITOR.instances.mensajetextarea.getData();

        console.log(data);

        $.post(url,data,function(raw){
            console.log(raw);
            //return false;
            var response = JSON.parse(raw);
            if(response.mensaje!="OK") {
            alert("Algo fallo");
            return false;
            } else {
            window.location.href = "./?s=detalle-mailing&id=" + response.obj.id + "&msg=1";
            }
        }).fail(function(){
            alert("No funciono");
        });
    });

</script>