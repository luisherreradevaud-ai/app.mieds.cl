<?php

    $objs = Mailing::getAll();

?>
<div class="mb-5 d-flex justify-content-end">
    <a href="./?s=nuevo-mailing" class="btn btn-primary btn-sm shadow">
        + Nuevo Mailing
    </a>
</div>
<table class="table table-striped table-hover" id="objs-table">
    <thead>
        <tr>
            <th class="fw-bold">
                Nombre
            </th>
            <th class="fw-bold">
                Asunto
            </th>
            <th class="fw-bold">
                Creado
            </th>
        </tr>
    </thead>
    <tbody>
        <?php
            foreach($objs as $obj) {
                ?>
                <tr class="tr-objs" data-idobjs="<?= $obj->id; ?>">
                    <td>
                        <?= $obj->nombre; ?>
                    </td>
                    <td>
                        <?= $obj->asunto; ?>
                    </td>
                    <td>
                        <?= datetime2fechayhora($obj->creada); ?>
                    </td>
                </tr>
                <?php
            }
        ?>
    </tbody>
</table>

<script>

    $(document).ready(function(){
    new DataTable('#objs-table', {
        paging: false,
        stateSave: true,
        scrollY: 600
        });
    });

    $(document).on('click','.tr-objs',function(e){
        window.location.href = './?s=detalle-mailing&id=' + $(e.currentTarget).data('idobjs');
    })

</script>