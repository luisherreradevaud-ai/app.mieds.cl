<?php
/**
 * Componente de Conversación Interna
 *
 * Este componente muestra una conversación completa con comentarios, archivos adjuntos,
 * likes y menciones de usuarios.
 *
 * Uso:
 * <?php
 *   $conversation_view_name = "batch"; // Nombre de la vista/entidad
 *   $conversation_entity_id = $batch->id; // ID de la entidad
 *   include($GLOBALS['base_dir']."/templates/components/conversacion-interna.php");
 * ?>
 *
 * Variables requeridas:
 * - $conversation_view_name: Nombre de la vista (ej: "batch", "pedido", "cliente")
 * - $conversation_entity_id: ID de la entidad relacionada
 */

if(!isset($conversation_view_name) || !isset($conversation_entity_id)) {
  echo "<div class='alert alert-danger'>Error: Faltan parámetros para el componente de conversación (conversation_view_name, conversation_entity_id)</div>";
  return;
}

// Generar ID único para este componente
$conv_unique_id = 'conv_'.md5($conversation_view_name.'_'.$conversation_entity_id);
?>

<div class="conversacion-interna-container" id="<?php echo $conv_unique_id; ?>" data-view-name="<?php echo htmlspecialchars($conversation_view_name); ?>" data-entity-id="<?php echo htmlspecialchars($conversation_entity_id); ?>">

  <!-- Encabezado de la conversación -->
  <div class="conversacion-header">
    <h5 class="mb-0">
      <i data-lucide="message-square" class="me-2"></i>
      Conversación
    </h5>
  </div>

  <!-- Spinner de carga -->
  <div class="conversacion-loading text-center py-5" style="display: none;">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Cargando...</span>
    </div>
  </div>

  <!-- Card única con postbox y comentarios -->
  <div class="card">
    <div class="card-body">
      <!-- Área de nueva publicación -->
      <div class="conversacion-postbox">
        <form class="conversacion-form">
          <!-- Input group estilo chat -->
          <div class="input-group">
            <textarea
              class="form-control conversacion-input-contenido"
              rows="1"
              placeholder="Escribe tu comentario aquí..."
              required
              style="overflow-y: hidden; resize: none; border-bottom-left-radius: 0;"
            ></textarea>
            <button type="button" class="btn btn-primary conversacion-btn-enviar" style="border-bottom-right-radius: 0;">
              Publicar
            </button>
          </div>

          <!-- Preview de archivos seleccionados -->
          <div class="conversacion-archivos-preview" style="display: none;">
            <div class="mb-0 border-start border-end" style="border-color: var(--bs-border-color);">
              <div class="p-2" id="files-list">
                <!-- Files will be listed here -->
              </div>
            </div>
          </div>

          <!-- Dropzone para archivos -->
          <div class="conversacion-dropzone" id="conversacion-dropzone">
            <input
              type="file"
              class="conversacion-input-archivos d-none"
              id="conversacion-file-input"
              multiple
              accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt"
            >
            <div class="dropzone-area text-center p-3 border-start border-end border-bottom">
              <i class="bi bi-cloud-upload" style="font-size: 1.5rem; color: var(--bs-secondary);"></i>
              <p class="mb-0 small">Arrastra archivos aquí o haz clic para seleccionar</p>
            </div>
          </div>
        </form>
      </div>

      <!-- Lista de comentarios -->
      <div class="conversacion-comentarios">
        <!-- Los comentarios se cargarán dinámicamente aquí -->
      </div>

      <!-- Mensaje cuando no hay comentarios -->
      <div class="conversacion-sin-comentarios text-center py-5 text-muted" style="display: none;">
        <i data-lucide="message-circle" class="mb-2" style="width: 48px; height: 48px;"></i>
        <p>Aún no hay comentarios. Sé el primero en comentar.</p>
      </div>
    </div>
  </div>

</div>

<!-- Template para un comentario -->
<template id="conversacion-comentario-template">
  <div class="conversacion-comentario" data-comentario-id="">
    <div class="d-flex align-items-start">
      <div class="flex-grow-1">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <p class="mb-0"><strong class="conversacion-comentario-autor"></strong></p>
          </div>
          <div class="d-flex align-items-center gap-2">
            <small class="text-muted conversacion-comentario-fecha"></small>
            <div class="dropdown position-relative">
              <button class="btn btn-sm btn-link text-muted conversacion-comentario-menu p-0" type="button" data-bs-toggle="dropdown" data-bs-display="static">
                <i data-lucide="more-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item conversacion-btn-eliminar-comentario" href="#"><i data-lucide="trash-2" class="me-2"></i>Eliminar</a></li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Contenido del comentario -->
        <div class="conversacion-comentario-contenido mb-2"></div>

        <!-- Archivos adjuntos -->
        <div class="conversacion-comentario-archivos mb-2"></div>

        <!-- Timestamp y Acciones -->
        <small class="text-muted conversacion-comentario-timestamp"></small><br />
        <button type="button" class="btn btn-sm btn-danger mt-1 conversacion-btn-like">
          <i class="lucide-sm" data-lucide="heart"></i>
          <span class="conversacion-likes-count"></span>
        </button>
      </div>
    </div>
  </div>
</template>

<!-- Template para archivo adjunto -->
<template id="conversacion-archivo-template">
  <a href="" target="_blank" class="conversacion-archivo d-inline-flex align-items-center p-2 border rounded me-2 mb-2 text-decoration-none">
    <i data-lucide="file" class="me-2"></i>
    <div>
      <div class="conversacion-archivo-nombre small"></div>
      <div class="conversacion-archivo-tamano text-muted" style="font-size: 0.75rem;"></div>
    </div>
  </a>
</template>

<style>
/* Estilos básicos del componente - se pueden sobrescribir */
.conversacion-interna-container {
  background: transparent;
  padding: 0;
}

.conversacion-header {
  border-bottom: 1px solid var(--bs-border-color);
  padding-bottom: 15px;
  margin-bottom: 20px;
}

.conversacion-comentario {
  padding: 20px 0;
  border-bottom: 1px solid var(--bs-border-color);
}

.conversacion-comentario:last-child {
  border-bottom: none;
}

.conversacion-comentario-contenido {
  white-space: pre-wrap;
  word-wrap: break-word;
  line-height: 1.6;
}

.conversacion-mention {
  background-color: #e3f2fd;
  color: #1976d2;
  padding: 2px 6px;
  border-radius: 3px;
  font-weight: 500;
}

.conversacion-archivo {
  transition: all 0.2s;
}

.conversacion-archivo:hover {
  background-color: var(--bs-light);
  border-color: var(--bs-primary) !important;
}

.conversacion-comentario-avatar img {
  object-fit: cover;
}

.conversacion-archivos-preview-item {
  display: inline-flex;
  align-items: center;
  padding: 8px 12px;
  background: var(--bs-light);
  border-radius: 4px;
  margin-right: 8px;
  margin-bottom: 8px;
}

.conversacion-menciones-lista .badge {
  margin-right: 5px;
  margin-bottom: 5px;
}

.conversacion-btn-like {
  border: none;
  background: transparent;
  color: var(--bs-danger);
}

.conversacion-btn-like:hover {
  background: var(--bs-danger);
  color: white;
}

.conversacion-btn-like.liked {
  background: var(--bs-danger);
  color: white;
}

.conversacion-btn-like.liked i[data-lucide="heart"] {
  fill: currentColor;
}

.conversacion-postbox {
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  border-radius: 0.375rem;
}

/* Dropzone */
.conversacion-dropzone .dropzone-area {
  cursor: pointer;
  transition: all 0.2s;
  background: white;
  border-top: none !important;
  border-bottom-right-radius: 0.375rem !important;
  border-bottom-left-radius: 0.375rem !important;
}

.conversacion-dropzone .dropzone-area:hover {
  border-color: var(--bs-primary) !important;
  background: var(--bs-primary-bg-subtle);
}

.conversacion-dropzone .dropzone-area.dragover {
  border-color: var(--bs-primary) !important;
  background: var(--bs-primary-bg-subtle);
}

.conversacion-archivos-preview .file-item {
  padding: 0.5rem;
  border-bottom: 1px solid var(--bs-border-color);
}

.conversacion-archivos-preview .file-item:last-child {
  border-bottom: none;
}

.conversacion-archivos-preview .file-thumbnail {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 4px;
  cursor: pointer;
}
</style>

<script>
// Inicializar el componente cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
  ConversacionInterna.init('<?php echo $conv_unique_id; ?>');
});
</script>
