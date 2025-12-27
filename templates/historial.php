<?php

  $query = "ORDER BY id desc";
  $historial = Historial::getAll($query);
  
?>
<style>
.tr-productos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-list"></i> <b>Historial</b></h1>
  </div>
  <div>
  </div>
</div>
<hr />
<table class="table table-striped table-sm mb-4" id="objs-table">
  <thead class="thead-dark">
    <tr>
    <th>
        Acci&oacute;n
    </th>
    <th>
        Usuario
    </th>
    <th>
        Fecha y hora
    </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($historial as $h) {
        $u = new Usuario($h->id_usuarios);
        /*$accion_2 = explode('Barril #',$h->accion)[1];
        $id_barriles = explode(' ',$accion_2)[0];
        if(is_numeric($id_barriles)){
          $barril = new Barril($id_barriles);
          $palabras = explode(' ',$h->accion);
          array_shift($palabras);
          array_shift($palabras);
          $h->accion = "Barril código ".$barril->codigo." ".implode(' ', $palabras);
          $h->save();
        }*/
    ?>
    <tr>
        <td>
            <?= $h->accion; ?>
        </td>
        <td>
            <?= $u->nombre; ?>
        </td>
        <td>
            <?= $h->creada; ?>
        </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
</form>
<script>


new DataTable('#objs-table', {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.1.3/i18n/es-CL.json'
    },
    pageLength: 50,
    stateSave: true,
    "order": [[2, "desc"]]
});

</script>
<?php

?>