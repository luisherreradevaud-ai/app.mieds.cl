<?php
    $usuario_actual = $GLOBALS['usuario'];

    // Get all tableros and separate them
    $all_tableros = KanbanTablero::getAll("ORDER BY actualizada DESC");
    $mis_tableros = array();
    $tableros_asignados = array();

    foreach($all_tableros as $tablero) {
        if($tablero->id_usuario_creador == $usuario_actual->id) {
            $mis_tableros[] = $tablero;
        } else {
            // Check if user is assigned to this tablero
            $usuarios_asignados = $tablero->getUsuarios();
            foreach($usuarios_asignados as $rel) {
                if($rel->id_usuarios == $usuario_actual->id) {
                    $tableros_asignados[] = $tablero;
                    break;
                }
            }
        }
    }
?>
<style>
.tr-obj {
  cursor: pointer;
  transition: background-color 0.15s ease;
}
.tr-obj:hover {
  background-color: rgba(0, 0, 0, 0.05);
}
</style>
<div class="mb-3">
  <h1 class="h3 mb-0 text-gray-800"><b>Tableros</b></h1>
</div>
<?php
Msg::show(2,'Tablero eliminado con &eacute;xito','danger');
?>

<!-- Mis Tableros -->
<div class="card mb-4">
  <div class="card-body">
    <div class="row mb-3">
      <div class="col-6">
        <h4 class="card-title mb-0">Mis Tableros</h4>
      </div>
      <div class="col-6">
        <div class="text-sm-end">
          <button id="btn-nuevo-tablero" class="btn btn-primary"><i class="fas fa-fw fa-plus"></i> Nuevo Tablero</button>
        </div>
      </div>
    </div>
    <table class="table w-100">
      <tbody>
        <?php
          if(count($mis_tableros) == 0) {
            echo '<tr><td colspan="3" class="text-center text-muted">No has creado ning&uacute;n tablero</td></tr>';
          } else {
            foreach($mis_tableros as $obj) {
              $usuarios_asignados = $obj->getUsuarios();
        ?>
        <tr class="tr-obj" data-idobj="<?= $obj->id; ?>">
          <td><strong><?= $obj->nombre; ?></strong></td>
          <td><?= count($usuarios_asignados); ?> usuario<?= count($usuarios_asignados) != 1 ? 's' : ''; ?></td>
          <td><?= date('d/m/Y H:i', strtotime($obj->actualizada)); ?></td>
          <td class="text-end">
            <button type="button" class="btn btn-light btn-sm">Ver</button>
          </td>
        </tr>
        <?php
            }
          }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Tableros Asignados -->
<div class="card">
  <div class="card-body">
    <div class="row mb-3">
      <div class="col-6">
        <h4 class="card-title mb-0">Tableros Asignados</h4>
      </div>
    </div>
    <table class="table w-100">
      <tbody>
        <?php
          if(count($tableros_asignados) == 0) {
            echo '<tr><td colspan="5" class="text-center text-muted">No est&aacute;s asignado a ning&uacute;n tablero</td></tr>';
          } else {
            foreach($tableros_asignados as $obj) {
              $usuarios_asignados = $obj->getUsuarios();
              $creador = new Usuario($obj->id_usuario_creador);
        ?>
        <tr class="tr-obj" data-idobj="<?= $obj->id; ?>">
          <td><strong><?= $obj->nombre; ?></strong></td>
          <td><?= $creador->nombre; ?></td>
          <td><?= count($usuarios_asignados); ?> usuario<?= count($usuarios_asignados) != 1 ? 's' : ''; ?></td>
          <td><?= date('d/m/Y H:i', strtotime($obj->actualizada)); ?></td>
          <td class="text-end">
            <button type="button" class="btn btn-light btn-sm">Ver</button>
          </td>
        </tr>
        <?php
            }
          }
        ?>
      </tbody>
    </table>
  </div>
</div>
<script>

$(document).on('click','.tr-obj',function(e) {
    window.location.href = "./?s=tablero-kanban&id=" + $(e.currentTarget).data('idobj');
});

$('#btn-nuevo-tablero').on('click', function(e) {
    e.preventDefault();

    // Disable button to prevent double clicks
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando...');

    $.ajax({
        url: './ajax/ajax_crearTablero.php',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if(response.status === 'OK') {
                window.location.href = "./?s=tablero-kanban&id=" + response.tablero_id;
            } else {
                alert('Error al crear tablero: ' + response.mensaje);
                $('#btn-nuevo-tablero').prop('disabled', false).html('<i class="fas fa-fw fa-plus"></i> Nuevo Tablero');
            }
        },
        error: function() {
            alert('Error de conexión al crear tablero');
            $('#btn-nuevo-tablero').prop('disabled', false).html('<i class="fas fa-fw fa-plus"></i> Nuevo Tablero');
        }
    });
});

</script>
