<?php

$usuario = $GLOBALS['usuario'];
$tipos_de_gasto = TipoDeGasto::getAll("ORDER BY nombre asc");

?>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/es.global.min.js'></script>

<style>
.fc-event {
  cursor: pointer;
}
.fc-event:hover {
  opacity: 0.8;
}
.calendar-legend {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}
.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
}
.legend-color {
  width: 20px;
  height: 20px;
  border-radius: 4px;
}
@media (max-width: 768px) {
  .fc-toolbar-title {
    font-size: 1.2rem !important;
  }
  .fc-button {
    padding: 0.3rem 0.5rem !important;
    font-size: 0.8rem !important;
  }
}
</style>

<div class="container-fluid p-0">

  <div class="card">
    <div class="card-body">

      <!-- FullCalendar Container -->
      <div id="fullcalendar"></div>

      <!-- Legend -->
      <div class="calendar-legend mt-3">
        <?php if($usuario->nivel == "Administrador" || $usuario->nivel == "Jefe de Planta") { ?>
        <div class="legend-item">
          <div class="legend-color" style="background-color: #dc3545;"></div>
          <span>Gastos por Pagar</span>
        </div>
        <?php } ?>

        <div class="legend-item">
          <div class="legend-color" style="background-color: #0dcaf0;"></div>
          <span>Tareas Pendientes</span>
        </div>

        <div class="legend-item">
          <div class="legend-color" style="background-color: #ffc107;"></div>
          <span>Tareas Vencidas</span>
        </div>

        <?php if($usuario->nivel == "Administrador" || $usuario->nivel == "Jefe de Planta" || $usuario->nivel == "Jefe de Cocina") { ?>
        <div class="legend-item">
          <div class="legend-color" style="background-color: #198754;"></div>
          <span>Inicio de Batch</span>
        </div>
        <div class="legend-item">
          <div class="legend-color" style="background-color: #6c757d;"></div>
          <span>Término de Batch</span>
        </div>
        <?php } ?>
      </div>

    </div>
  </div>

</div>

<script>
// Handle event drop (drag and drop)
function handleEventDrop(info) {
  var event = info.event;
  var newDate = event.startStr.split('T')[0]; // Get YYYY-MM-DD format

  console.log('Event dropped:', event.id, 'New date:', newDate);

  // Remove any open tooltips
  var tooltip = document.getElementById('fc-tooltip-' + event.id);
  if(tooltip) {
    tooltip.remove();
  }

  // Parse event ID to get type and actual ID
  var eventId = event.id;
  var eventType = null;
  var entityId = null;

  if(eventId.startsWith('gasto_')) {
    eventType = 'gastos';
    entityId = eventId.replace('gasto_', '');
  } else if(eventId.startsWith('tarea_')) {
    eventType = 'kanban_tareas';
    entityId = eventId.replace('tarea_', '');
  } else if(eventId.startsWith('batch_inicio_')) {
    eventType = 'batches';
    entityId = eventId.replace('batch_inicio_', '');
    // Update fecha_inicio
    updateEntity(eventType, entityId, { fecha_inicio: newDate }, info);
    return;
  } else if(eventId.startsWith('batch_termino_')) {
    eventType = 'batches';
    entityId = eventId.replace('batch_termino_', '');
    // Update fecha_termino
    updateEntity(eventType, entityId, { fecha_termino: newDate }, info);
    return;
  }

  if(!eventType || !entityId) {
    console.error('Invalid event ID:', eventId);
    info.revert();
    return;
  }

  // Determine which field to update based on event type
  var updateData = {};
  if(eventType === 'gastos') {
    updateData.date_vencimiento = newDate;
  } else if(eventType === 'kanban_tareas') {
    updateData.fecha_vencimiento = newDate;
  }

  updateEntity(eventType, entityId, updateData, info);
}

// Update entity via AJAX
function updateEntity(entidad, id, data, dropInfo) {
  $.ajax({
    url: './ajax/ajax_guardarEntidad.php',
    type: 'POST',
    data: {
      entidad: entidad,
      id: id,
      ...data
    },
    dataType: 'json',
    success: function(response) {
      console.log('Entity updated:', response);
      if(response.status === 'OK') {
        // Success - event stays in new position
        console.log('Successfully updated ' + entidad + ' #' + id);

        // Refresh calendar to show updated data
        if(window.barrilCalendar) {
          window.barrilCalendar.refetchEvents();
        }
      } else {
        console.error('Error updating entity:', response.mensaje);
        alert('Error al actualizar: ' + response.mensaje);
        dropInfo.revert();
      }
    },
    error: function(xhr, status, error) {
      console.error('AJAX error:', error);
      alert('Error de conexión al actualizar la fecha');
      dropInfo.revert();
    }
  });
}

document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('fullcalendar');

  var calendar = new FullCalendar.Calendar(calendarEl, {
    // Theme and localization
    locale: 'es',
    timeZone: 'America/Santiago',

    // Initial view
    initialView: window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth',

    // Header toolbar
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,listMonth'
    },

    // Button text (Spanish)
    buttonText: {
      today: 'Hoy',
      month: 'Mes',
      week: 'Semana',
      list: 'Lista'
    },

    // Height
    height: 'auto',

    // Enable drag and drop
    editable: true,
    eventDrop: handleEventDrop,

    // Event sources
    events: function(info, successCallback, failureCallback) {
      $.ajax({
        url: './ajax/ajax_getCalendarEvents.php',
        type: 'GET',
        data: {
          start: info.startStr,
          end: info.endStr
        },
        success: function(response) {
          // Check if response is the new format (object with events and usuarios)
          if(response && typeof response === 'object' && response.events) {
            // Store users globally for task assignment
            if(response.usuarios) {
              window.calendarAvailableUsers = response.usuarios;
            }
            successCallback(response.events);
          } else {
            // Legacy format: response is directly the events array
            successCallback(response);
          }
        },
        error: function(xhr, status, error) {
          console.error('Error loading calendar events:', error);
          failureCallback(error);
        }
      });
    },

    // Event rendering
    eventDisplay: 'block',

    // Event click handler
    eventClick: function(info) {
      info.jsEvent.preventDefault(); // Prevent default browser behavior

      if (info.event.url) {
        window.location.href = info.event.url;
      }
    },

    // Event mouse enter
    eventMouseEnter: function(info) {
      // Show tooltip with more details
      var tooltip = document.createElement('div');
      tooltip.className = 'fc-tooltip';
      tooltip.style.position = 'fixed';
      tooltip.style.zIndex = '10000';
      tooltip.style.backgroundColor = 'rgba(0,0,0,0.9)';
      tooltip.style.color = 'white';
      tooltip.style.padding = '8px 12px';
      tooltip.style.borderRadius = '6px';
      tooltip.style.fontSize = '12px';
      tooltip.style.maxWidth = '250px';
      tooltip.style.pointerEvents = 'none'; // Allow clicking through tooltip
      tooltip.style.boxShadow = '0 2px 8px rgba(0,0,0,0.3)';

      var props = info.event.extendedProps;
      var content = '<strong>' + info.event.title + '</strong><br>';

      if(props.tipo === 'gasto' && props.monto) {
        content += 'Monto: $' + parseInt(props.monto).toLocaleString('es-CL') + '<br>';
        content += 'Tipo: ' + (props.tipo_gasto || '-');
      } else if(props.tipo === 'tarea') {
        content += 'Estado: ' + (props.estado || '-');

        // Show assigned users
        if(props.usuarios_nombres && props.usuarios_nombres.length > 0) {
          content += '<br>Asignado a: ' + props.usuarios_nombres.join(', ');
        }

        if(props.vencida) {
          content += '<br><span style="color: #ffc107;">⚠ Vencida</span>';
        }
      }

      tooltip.innerHTML = content;
      tooltip.id = 'fc-tooltip-' + info.event.id;
      document.body.appendChild(tooltip);

      // Position tooltip next to cursor, not over the event
      var rect = info.el.getBoundingClientRect();
      var tooltipHeight = tooltip.offsetHeight;

      // Try to position below the event
      var topPos = rect.bottom + 5;

      // If tooltip would go off bottom of screen, position above instead
      if(topPos + tooltipHeight > window.innerHeight) {
        topPos = rect.top - tooltipHeight - 5;
      }

      tooltip.style.left = rect.left + 'px';
      tooltip.style.top = topPos + 'px';
    },

    // Event mouse leave
    eventMouseLeave: function(info) {
      var tooltip = document.getElementById('fc-tooltip-' + info.event.id);
      if(tooltip) {
        tooltip.remove();
      }
    },

    // Date click handler (optional - for future feature to add events)
    dateClick: function(info) {
      console.log('Clicked on: ' + info.dateStr);
      // Future: Open modal to create new event
    },

    // Loading handler
    loading: function(isLoading) {
      if(isLoading) {
        console.log('Loading calendar events...');
      } else {
        console.log('Calendar events loaded');
      }
    },

    // Responsive behavior
    windowResize: function(view) {
      if(window.innerWidth < 768) {
        calendar.changeView('listMonth');
      }
    }
  });

  // Render calendar
  calendar.render();

  // Make calendar accessible globally for debugging
  window.barrilCalendar = calendar;
});
</script>

<!-- Modal Nuevo Tarea -->
<div class="modal fade" tabindex="-1" role="dialog" id="calendario-nuevo-tarea-modal">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nueva Tarea</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12 mb-3">
            <label class="form-label">Tablero:</label>
            <select class="form-control" id="calendar-tarea-tablero">
              <option value="">Seleccione un tablero...</option>
            </select>
          </div>
          <div class="col-12 mb-3">
            <label class="form-label">Columna:</label>
            <select class="form-control" id="calendar-tarea-columna" disabled>
              <option value="">Primero seleccione un tablero</option>
            </select>
          </div>
          <div class="col-12 mb-3">
            <label class="form-label">Nombre de la tarea:</label>
            <input type="text" class="form-control" id="calendar-tarea-nombre" placeholder="Ej: Completar documentación">
          </div>
          <div class="col-12 mb-3">
            <label class="form-label">Fecha de vencimiento:</label>
            <input type="date" class="form-control" id="calendar-tarea-fecha">
          </div>
          <div class="col-12 mb-3">
            <label class="form-label">Asignar usuarios:</label>
            <div class="dropdown">
              <button
                class="btn btn-outline-secondary dropdown-toggle w-100 text-start"
                type="button"
                id="calendar-tarea-usuarios-btn"
                data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="bi bi-people"></i> Seleccionar usuarios
                <span id="calendar-selected-users-count" class="badge bg-primary ms-2" style="display: none;">0</span>
              </button>
              <div
                class="dropdown-menu p-3"
                id="calendar-tarea-usuarios-dropdown"
                style="min-width: 300px; max-width: 400px; max-height: 300px; overflow-y: auto;"
                onclick="event.stopPropagation();">
                <h6 class="dropdown-header px-0 mb-2">Seleccionar Usuarios</h6>
                <div id="calendar-usuarios-list">
                  <div class="text-center text-muted">
                    <small>Cargando usuarios...</small>
                  </div>
                </div>
              </div>
            </div>
            <div id="calendar-selected-users-display" class="mt-2">
              <!-- Selected users will appear here as badges -->
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary btn-sm" id="crear-tarea-calendar-btn" disabled>Crear Tarea</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Nuevo Gasto -->
<div class="modal fade" tabindex="-1" role="dialog" id="calendario-nuevo-gastos-modal">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nuevo Gasto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="gastos-form-calendar" action="./php/procesar.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="id" value="">
          <input type="hidden" name="entidad" value="gastos">
          <input type="hidden" name="modo" value="nuevo-entidad-con-media">
          <input type="hidden" name="id_usuarios" value="<?= $usuario->id; ?>">
          <input type="hidden" name="redirect" value="calendar">
          <div class="row">
            <div class="col-6 mb-2">
              Fecha:
            </div>
            <div class="col-6 mb-2">
              <input type="date" value="<?= date('Y-m-d'); ?>" class="form-control" name="creada">
            </div>
            <div class="col-6 mb-2">
              Tipo de Gasto:
            </div>
            <div class="col-6 mb-2">
              <select name="tipo_de_gasto" class="form-control calendar-gasto-required">
                <option value="">Seleccione...</option>
                <?php
                if(is_array($tipos_de_gasto)) {
                  foreach($tipos_de_gasto as $tipo) {
                    if(is_object($tipo) && isset($tipo->nombre)) {
                      print "<option>".$tipo->nombre."</option>";
                    }
                  }
                }
                ?>
              </select>
            </div>
            <div class="col-6 mb-2">
              Ítem:
            </div>
            <div class="col-6 mb-2">
              <input type="text" name="item" class="form-control calendar-gasto-required">
            </div>
            <div class="col-6 mb-2">
              Monto:
            </div>
            <div class="col-6 mb-2">
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="text" class="form-control acero" name="monto" value="0">
              </div>
            </div>
            <?php if($usuario->nivel == "Administrador") { ?>
            <div class="col-6 mb-2">
              Estado:
            </div>
            <div class="col-6 mb-2">
              <select name="estado" class="form-control" id="calendar-gasto-estado">
                <option>Por Pagar</option>
                <option>Pagado</option>
              </select>
            </div>
            <div class="col-6 mb-2 calendar-date-vencimiento">
              Vencimiento:
            </div>
            <div class="col-6 mb-2 calendar-date-vencimiento">
              <input type="date" name="date_vencimiento" class="form-control" value="<?= date('Y-m-d'); ?>" id="calendar-date-vencimiento-input">
            </div>
            <div class="col-6 mb-2 calendar-date-vencimiento">
              Repetir:
            </div>
            <div class="col-6 mb-2 calendar-date-vencimiento">
              <select name="repetir" class="form-control" id="calendar-gasto-repetir">
                <option>No</option>
                <option>Cada semana</option>
                <option>Cada mes</option>
              </select>
            </div>
            <div class="col-6 calendar-repetir">
              <div class="calendar-date-vencimiento mb-2">
                Hasta:
              </div>
            </div>
            <div class="col-6 calendar-repetir">
              <div class="calendar-date-vencimiento mb-2">
                <input type="date" name="hasta" class="form-control">
              </div>
            </div>
            <?php } else { ?>
            <input type="hidden" name="estado" value="Por Pagar">
            <div class="col-6 mb-2">
              Vencimiento:
            </div>
            <div class="col-6 mb-2">
              <input type="date" name="date_vencimiento" class="form-control" value="<?= date('Y-m-d'); ?>">
            </div>
            <?php } ?>
            <div class="col-12 mb-2">
              Imagen:
            </div>
            <div class="col-12 mb-2">
              <input type="file" name="file" class="form-control">
            </div>
            <div class="col-12 mb-2">
              Comentarios:
            </div>
            <div class="col-12 mb-2">
              <textarea name="comentarios" class="form-control" rows="3"></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary btn-sm" id="nuevo-gastos-aceptar-calendar" disabled>Agregar</button>
      </div>
    </div>
  </div>
</div>

<script>
// Open Nuevo Gasto Modal
function openNuevoGastoModal(fecha) {
  console.log('Opening nuevo gasto modal for date:', fecha);

  // Reset form
  $('#gastos-form-calendar')[0].reset();
  $('#gastos-form-calendar input[name="creada"]').val(fecha || '<?= date('Y-m-d'); ?>');
  $('#gastos-form-calendar input[name="date_vencimiento"]').val(fecha || '<?= date('Y-m-d'); ?>');
  $('#gastos-form-calendar input[name="monto"]').val('0');
  $('#nuevo-gastos-aceptar-calendar').attr('disabled', true);

  <?php if($usuario->nivel == "Administrador") { ?>
  $('.calendar-date-vencimiento').hide();
  $('.calendar-repetir').hide();
  <?php } ?>

  // Show modal
  $('#calendario-nuevo-gastos-modal').modal('show');
}

// Handle date click in calendar - show context menu
var calendarContextMenu = null;
var selectedDate = null;

document.addEventListener('DOMContentLoaded', function() {
  if(window.barrilCalendar) {
    // Update calendar to handle date clicks
    window.barrilCalendar.setOption('dateClick', function(info) {
      console.log('Date clicked:', info.dateStr);
      selectedDate = info.dateStr;

      // Remove existing context menu if any
      if(calendarContextMenu) {
        calendarContextMenu.remove();
      }

      // Create context menu
      calendarContextMenu = document.createElement('div');
      calendarContextMenu.className = 'calendar-context-menu';
      calendarContextMenu.style.position = 'absolute';
      calendarContextMenu.style.backgroundColor = 'white';
      calendarContextMenu.style.border = '1px solid #ddd';
      calendarContextMenu.style.borderRadius = '4px';
      calendarContextMenu.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
      calendarContextMenu.style.zIndex = '9999';
      calendarContextMenu.style.minWidth = '180px';

      var menuHTML = '<div style="padding: 8px 0;">';

      <?php if($usuario->nivel == "Administrador" || $usuario->nivel == "Jefe de Planta") { ?>
      menuHTML += '<div class="calendar-menu-item" data-action="gasto" style="padding: 8px 16px; cursor: pointer; transition: background-color 0.2s;">';
      menuHTML += '<i class="align-middle" data-lucide="dollar-sign" style="width: 16px; height: 16px;"></i> ';
      menuHTML += 'Nuevo Gasto';
      menuHTML += '</div>';
      <?php } ?>

      menuHTML += '<div class="calendar-menu-item" data-action="tarea" style="padding: 8px 16px; cursor: pointer; transition: background-color 0.2s;">';
      menuHTML += '<i class="align-middle" data-lucide="check-square" style="width: 16px; height: 16px;"></i> ';
      menuHTML += 'Nueva Tarea';
      menuHTML += '</div>';

      menuHTML += '</div>';

      calendarContextMenu.innerHTML = menuHTML;

      // Position the menu near the click
      var rect = info.dayEl.getBoundingClientRect();
      calendarContextMenu.style.left = (rect.left + window.scrollX) + 'px';
      calendarContextMenu.style.top = (rect.bottom + window.scrollY) + 'px';

      document.body.appendChild(calendarContextMenu);

      // Initialize Lucide icons in the menu
      if(typeof lucide !== 'undefined') {
        lucide.createIcons();
      }

      // Add hover effects
      document.querySelectorAll('.calendar-menu-item').forEach(function(item) {
        item.addEventListener('mouseenter', function() {
          this.style.backgroundColor = '#f0f0f0';
        });
        item.addEventListener('mouseleave', function() {
          this.style.backgroundColor = 'transparent';
        });
      });

      // Handle menu item clicks
      document.querySelectorAll('.calendar-menu-item').forEach(function(item) {
        item.addEventListener('click', function() {
          var action = this.getAttribute('data-action');

          if(action === 'gasto') {
            // Open gasto modal
            openNuevoGastoModal(selectedDate);
          } else if(action === 'tarea') {
            // Open tarea modal
            openNuevaTareaModal(selectedDate);
          }

          // Remove context menu
          if(calendarContextMenu) {
            calendarContextMenu.remove();
            calendarContextMenu = null;
          }
        });
      });
    });
  }

  // Close context menu when clicking outside
  document.addEventListener('click', function(e) {
    if(calendarContextMenu && !calendarContextMenu.contains(e.target)) {
      calendarContextMenu.remove();
      calendarContextMenu = null;
    }
  });
});

// Validate required fields
$(document).on('change', '.calendar-gasto-required', function() {
  var tipo = $('select[name="tipo_de_gasto"]').val();
  var item = $('input[name="item"]').val();

  if(tipo && item) {
    $('#nuevo-gastos-aceptar-calendar').attr('disabled', false);
  } else {
    $('#nuevo-gastos-aceptar-calendar').attr('disabled', true);
  }
});

// Submit form via AJAX
$(document).on('click', '#nuevo-gastos-aceptar-calendar', function(e) {
  e.preventDefault();

  var $btn = $(this);
  $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...');

  var formData = new FormData($('#gastos-form-calendar')[0]);

  $.ajax({
    url: './php/procesar.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      console.log('Gasto saved:', response);

      // Close modal
      $('#calendario-nuevo-gastos-modal').modal('hide');

      // Refresh calendar
      if(window.barrilCalendar) {
        window.barrilCalendar.refetchEvents();
      }

      // Reset button
      $btn.prop('disabled', false).html('Agregar');
    },
    error: function(xhr, status, error) {
      console.error('Error saving gasto:', error);
      alert('Error al guardar gasto: ' + error);

      // Reset button
      $btn.prop('disabled', false).html('Agregar');
    }
  });
});

<?php if($usuario->nivel == "Administrador") { ?>
// Show/hide date_vencimiento based on estado
$(document).on('change', '#calendar-gasto-estado', function() {
  if($(this).val() == 'Por Pagar') {
    $('.calendar-date-vencimiento').show(200);
  } else {
    $('.calendar-date-vencimiento').hide(200);
  }
});

// Show/hide repetir fields
$(document).on('change', '#calendar-gasto-repetir', function() {
  if($(this).val() != 'No') {
    $('.calendar-repetir').show(200);
  } else {
    $('.calendar-repetir').hide(200);
  }
});
<?php } ?>

// ============================================
// NUEVA TAREA FUNCTIONALITY
// ============================================

var userTableros = [];
var currentTableroData = null;
var calendarSelectedUsers = [];

// Open Nueva Tarea Modal
function openNuevaTareaModal(fecha) {
  console.log('Opening nueva tarea modal for date:', fecha);

  // Reset form
  $('#calendar-tarea-tablero').val('');
  $('#calendar-tarea-columna').val('').prop('disabled', true).html('<option value="">Primero seleccione un tablero</option>');
  $('#calendar-tarea-nombre').val('');
  $('#calendar-tarea-fecha').val(fecha);
  $('#crear-tarea-calendar-btn').prop('disabled', true);

  // Reset user selection
  calendarSelectedUsers = [];
  updateCalendarSelectedUsersDisplay();

  // Populate users dropdown from global variable
  populateCalendarUsersDropdown();

  // Load user's tableros
  loadUserTableros();

  // Show modal
  $('#calendario-nuevo-tarea-modal').modal('show');
}

// ============================================
// USER ASSIGNMENT FUNCTIONALITY
// ============================================

// Populate users dropdown with checkboxes
function populateCalendarUsersDropdown() {
  var container = $('#calendar-usuarios-list');
  container.empty();

  // Check if users are loaded from calendar events endpoint
  if(!window.calendarAvailableUsers || window.calendarAvailableUsers.length === 0) {
    container.html('<p class="text-muted small mb-0">No hay usuarios disponibles</p>');
    return;
  }

  window.calendarAvailableUsers.forEach(function(user) {
    var isChecked = calendarSelectedUsers.includes(parseInt(user.id));

    var div = $('<div>').addClass('form-check mb-2');

    var checkbox = $('<input>')
      .attr('type', 'checkbox')
      .attr('id', 'calendar-user-' + user.id)
      .addClass('form-check-input')
      .val(user.id)
      .prop('checked', isChecked)
      .on('change', function() {
        toggleCalendarUser(parseInt(user.id), $(this).is(':checked'));
      });

    var label = $('<label>')
      .addClass('form-check-label')
      .attr('for', 'calendar-user-' + user.id)
      .text((user.nombre || '') + ' ' + (user.apellido || ''));

    div.append(checkbox).append(label);
    container.append(div);
  });
}

// Toggle user selection
function toggleCalendarUser(userId, isChecked) {
  if(isChecked) {
    if(!calendarSelectedUsers.includes(userId)) {
      calendarSelectedUsers.push(userId);
    }
  } else {
    calendarSelectedUsers = calendarSelectedUsers.filter(id => id !== userId);
  }

  updateCalendarSelectedUsersDisplay();
}

// Update the display of selected users
function updateCalendarSelectedUsersDisplay() {
  var count = calendarSelectedUsers.length;
  var badge = $('#calendar-selected-users-count');
  var display = $('#calendar-selected-users-display');

  // Update badge count
  if(count > 0) {
    badge.text(count).show();
  } else {
    badge.hide();
  }

  // Update display area with user badges
  display.empty();

  if(count === 0) {
    return;
  }

  calendarSelectedUsers.forEach(function(userId) {
    var user = window.calendarAvailableUsers.find(u => u.id == userId);
    if(user) {
      var badgeEl = $('<span>')
        .addClass('badge bg-secondary me-1 mb-1')
        .text((user.nombre || '') + ' ' + (user.apellido || ''))
        .append(
          $('<i>')
            .addClass('bi bi-x-circle ms-1')
            .css('cursor', 'pointer')
            .on('click', function() {
              $('#calendar-user-' + userId).prop('checked', false).trigger('change');
            })
        );

      display.append(badgeEl);
    }
  });
}

// Load user's tableros
function loadUserTableros() {
  console.log('Loading user tableros...');

  $.ajax({
    url: './ajax/ajax_getUserTableros.php',
    type: 'GET',
    dataType: 'json',
    success: function(response) {
      console.log('Tableros loaded:', response);

      if(response.status === 'OK' && response.tableros) {
        userTableros = response.tableros;

        var html = '<option value="">Seleccione un tablero...</option>';
        response.tableros.forEach(function(tablero) {
          html += '<option value="' + tablero.id + '">' + tablero.nombre + '</option>';
        });

        $('#calendar-tarea-tablero').html(html);
      } else {
        console.error('Error loading tableros:', response.mensaje);
        alert('Error al cargar tableros: ' + response.mensaje);
      }
    },
    error: function(xhr, status, error) {
      console.error('AJAX error loading tableros:', error);
      alert('Error de conexión al cargar tableros');
    }
  });
}

// When tablero is selected, load columns
$(document).on('change', '#calendar-tarea-tablero', function() {
  var tableroId = $(this).val();

  if(!tableroId) {
    $('#calendar-tarea-columna').val('').prop('disabled', true).html('<option value="">Primero seleccione un tablero</option>');
    validateTareaForm();
    return;
  }

  console.log('Loading columns for tablero:', tableroId);

  $.ajax({
    url: './ajax/ajax_getTablero.php',
    type: 'GET',
    data: { id: tableroId },
    dataType: 'json',
    success: function(response) {
      console.log('Tablero data loaded:', response);

      if(response.status === 'OK' && response.tablero && response.tablero.columnas) {
        currentTableroData = response.tablero;

        var html = '<option value="">Seleccione una columna...</option>';
        response.tablero.columnas.forEach(function(columna) {
          html += '<option value="' + columna.id + '">' + columna.nombre + '</option>';
        });

        $('#calendar-tarea-columna').html(html).prop('disabled', false);
      } else {
        console.error('Error loading tablero:', response.mensaje);
        alert('Error al cargar columnas: ' + response.mensaje);
      }

      validateTareaForm();
    },
    error: function(xhr, status, error) {
      console.error('AJAX error loading tablero:', error);
      alert('Error de conexión al cargar columnas');
    }
  });
});

// Validate tarea form
$(document).on('change keyup', '#calendar-tarea-columna, #calendar-tarea-nombre', function() {
  validateTareaForm();
});

function validateTareaForm() {
  var tablero = $('#calendar-tarea-tablero').val();
  var columna = $('#calendar-tarea-columna').val();
  var nombre = $('#calendar-tarea-nombre').val().trim();

  if(tablero && columna && nombre) {
    $('#crear-tarea-calendar-btn').prop('disabled', false);
  } else {
    $('#crear-tarea-calendar-btn').prop('disabled', true);
  }
}

// Create tarea
$(document).on('click', '#crear-tarea-calendar-btn', function() {
  var tableroId = $('#calendar-tarea-tablero').val();
  var columnaId = $('#calendar-tarea-columna').val();
  var nombre = $('#calendar-tarea-nombre').val().trim();
  var fecha = $('#calendar-tarea-fecha').val();

  if(!tableroId || !columnaId || !nombre) {
    alert('Por favor complete todos los campos requeridos');
    return;
  }

  console.log('Creating tarea:', { tableroId, columnaId, nombre, fecha, usuarios: calendarSelectedUsers });

  // Disable button
  $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Creando...');

  $.ajax({
    url: './ajax/ajax_guardarTarea.php',
    type: 'POST',
    data: {
      id: '',
      nombre: nombre,
      id_kanban_columnas: columnaId,
      fecha_vencimiento: fecha,
      estado: 'Pendiente',
      usuarios: calendarSelectedUsers
    },
    dataType: 'json',
    success: function(response) {
      console.log('Tarea created:', response);

      if(response.status === 'OK') {
        // Close modal
        $('#calendario-nuevo-tarea-modal').modal('hide');

        // Reload calendar
        if(window.barrilCalendar) {
          window.barrilCalendar.refetchEvents();
        }
      } else {
        console.error('Error creating tarea:', response.mensaje);
        alert('Error al crear tarea: ' + response.mensaje);
        $('#crear-tarea-calendar-btn').prop('disabled', false).html('Crear Tarea');
      }
    },
    error: function(xhr, status, error) {
      console.error('AJAX error creating tarea:', error);
      alert('Error de conexión al crear tarea');
      $('#crear-tarea-calendar-btn').prop('disabled', false).html('Crear Tarea');
    }
  });
});
</script>
